<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once $raiz_do_projeto . "includes/main.php";
require_once $raiz_do_projeto . "includes/pdv/main.php";
require_once $raiz_do_projeto . "includes/pdv/corte_constantes.php";

require_once $raiz_do_projeto . "class/pdv/classChaveMestra.php";
require_once $raiz_do_projeto . "includes/pdv/functions.php";
require_once "/www/includes/bourls.php";

$grupos = unserialize($_SESSION["arrIdGrupos"]);

function getEstilosUsuarioPDO($userId, PDO $pdo)
{
    // Configurações recomendadas
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
    $pdo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);

    $sql = "SELECT ug_estilo, ug_logo FROM dist_usuarios_games WHERE ug_id = :userId";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();

    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($resultado && isset($resultado['ug_estilo'])) {
        $dados = json_decode($resultado['ug_estilo'], true);

        if (!empty($resultado['ug_logo'])) {
            // Se for um stream, precisamos ler o conteúdo
            $logoRaw = is_resource($resultado['ug_logo']) 
                ? stream_get_contents($resultado['ug_logo']) 
                : $resultado['ug_logo'];

            // Detectar extensão do logo (opcional)
            $ext = isset($dados['logo_extensao']) ? strtolower($dados['logo_extensao']) : 'png';
            $mime = ($ext === 'jpg') ? 'jpeg' : $ext;

            // Gerar base64 da imagem
            $dados['logo_base64'] = 'data:image/' . $mime . ';base64,' . base64_encode($logoRaw);
        }

        return is_array($dados) ? $dados : array();
    }

    return array();
}
function salva_estilos($idUsuario, $pdo)
{
    $maxFileSize = 200 * 1024;

    // Coleta os dados existentes para manter a extensão atual, caso não envie nova imagem
    $stmtFetch = $pdo->prepare("SELECT ug_estilo FROM dist_usuarios_games WHERE ug_id = ?");
    $stmtFetch->execute([$idUsuario]);
    $registro = $stmtFetch->fetch(PDO::FETCH_ASSOC);

    $estiloExistente = [];
    if ($registro && !empty($registro['ug_estilo'])) {
        $estiloExistente = json_decode($registro['ug_estilo'], true);
    }

    if (!filter_var($_POST['email_suporte'], FILTER_VALIDATE_EMAIL) && $_POST['email_suporte'] != "") {
        return "Erro: E-mail de suporte inválido.";
    }
    if (!filter_var($_POST['link_canal'], FILTER_VALIDATE_URL) && $_POST['link_canal'] != "") {
        return "Erro: URL inválida. Coloque o link completo, incluindo http:// ou https://";
    }

    // Coleta os dados do formulário
    $corPrimaria = htmlspecialchars($_POST['cor_primaria']);
    $corSecundaria = htmlspecialchars($_POST['cor_secundaria']);
    $emailSuporte = $_POST['email_suporte'];
    $linkCanal = $_POST['link_canal'];
    $mensagem = htmlspecialchars($_POST['mensagem']);

    $logoData = null;
    $ext = $estiloExistente['logo_extensao'] ? $estiloExistente['logo_extensao'] : 'png'; // usa extensão anterior por padrão

    // Se imagem foi enviada, processa
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['logo']['tmp_name'];
        $fileName = $_FILES['logo']['name'];
        $fileSize = $_FILES['logo']['size'];

        // Verifica extensão
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (!in_array($ext, ['png', 'jpg', 'jpeg'])) {
            return "Erro: Apenas arquivos PNG ou JPG são permitidos.";
        }

        // Verifica tamanho
        if ($fileSize > $maxFileSize) {
            return "Erro: Tamanho máximo permitido é 200 KB.";
        }

        // Lê o conteúdo do arquivo
        $logoData = file_get_contents($fileTmp);
        if ($logoData === false) {
            return "Erro ao ler o conteúdo do arquivo.";
        }
    }

    // Monta novo JSON de estilos
    $estilos = array(
        'cor_primaria'   => $corPrimaria,
        'cor_secundaria' => $corSecundaria,
        'email_suporte'  => $emailSuporte,
        'link_canal'     => $linkCanal,
        'mensagem'       => $mensagem,
        'logo_extensao'  => $ext
    );

    $jsonEstilos = json_encode($estilos);

    if (isset($_POST['rm_logo'])) {
        // Remove a logo
        $stmt = $pdo->prepare("UPDATE dist_usuarios_games SET ug_estilo = ?, ug_logo = NULL WHERE ug_id = ?");
        $stmt->bindParam(1, $jsonEstilos, PDO::PARAM_STR);
        $stmt->bindParam(2, $idUsuario, PDO::PARAM_INT);
    } elseif ($logoData !== null) {
        // Atualiza com nova imagem
        $stmt = $pdo->prepare("UPDATE dist_usuarios_games SET ug_estilo = ?, ug_logo = ? WHERE ug_id = ?");
        $stmt->bindParam(1, $jsonEstilos, PDO::PARAM_STR);
        $stmt->bindParam(2, $logoData, PDO::PARAM_LOB);
        $stmt->bindParam(3, $idUsuario, PDO::PARAM_INT);
    } else {
        // Atualiza apenas os estilos, mantendo imagem existente
        $stmt = $pdo->prepare("UPDATE dist_usuarios_games SET ug_estilo = ? WHERE ug_id = ?");
        $stmt->bindParam(1, $jsonEstilos, PDO::PARAM_STR);
        $stmt->bindParam(2, $idUsuario, PDO::PARAM_INT);
    }

    if ($stmt->execute()) {
    } else {
        $errorInfo = $stmt->errorInfo();
        return "Erro ao salvar no banco: " . $errorInfo[2];
    }
}

?>
<link href="<?php echo $url; ?>:<?php echo $server_port; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>:<?php echo $server_port; ?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $url; ?>:<?php echo $server_port; ?>/js/global.js"></script>
<script src="<?php echo $url; ?>:<?php echo $server_port; ?>/js/jquery.mask.min.js"></script>
<script src="<?php echo $url; ?>:<?php echo $server_port; ?>/js/formataNome.js"></script>

<script>
    $(function () {
        var optDate = new Object();
        optDate.interval = 1000;

        $('#ug_data_expiracao_senha').datepicker({
            minDate: "dateToday",
            changeMonth: true,
            dateFormat: "dd/mm/yy"
        });

        var searching = false;
        //Função para impedir que o usuário digite além de números no campo CEP.
        $("#novo_ug_cep").keypress(function (event) {

            var varTecla = event.charCode || event.keyCode;

            <?php
            // http://www.aspdotnetfaq.com/Faq/What-is-the-list-of-KeyCodes-for-JavaScript-KeyDown-KeyPress-and-KeyUp-events.aspx
            // Permite números, Backspace, Tab, Enter, End, Home, Left, Right, Del
            ?>
            if (((varTecla > 47) && (varTecla < 58)) || (varTecla == 8) || (varTecla == 9) || (varTecla == 13) || (varTecla == 35) || (varTecla == 36) || (varTecla == 37) || (varTecla == 39) || (varTecla == 46)) {
                return true;
            } else {
                return false;
            }
        });

        $(".data-nasc").datepicker({
            dateFormat: "dd/mm/yy",
            maxDate: "dateToday"
        });

        $(".data-nasc").mask("99/99/9999");

        $('.class_cpf_socios').mask("999.999.999-99");

        var aux_num_socios_bd = parseInt($('.addSocio').attr('data-socios'));
        var indice_socios = aux_num_socios_bd;
        $(document).on('click', '.rmSocio, .addSocio', function (e) {

            thisClass = e.target.className;
            var get_action = thisClass.split(" ");

            //div correspondente do botão 'remover' em que o usuário clicou
            var current_div = get_action[1];

            //Recuperando a classe 'remDiv' ou 'addDiv'
            thisClass = get_action[0];

            if (thisClass == 'rmSocio') {
                if (parseInt($(this).attr('remove-socio')) >= aux_num_socios_bd) {
                    $(this).closest("tr").nextAll(".data-rmv").remove();
                    $(this).closest("tr").find("td").remove();
                    indice_socios--;
                } else {
                    if (confirm('Você confirma a exclusão deste sócio?')) {
                        var index = $(this).attr('remove-socio');
                        window.location = 'com_usuario_detalhe_salva.php?acao=exclui_socio&usuario_id=<?php echo $usuario_id ?>&novo_ug_cpf_socios=' + $("input[name='novo_ug_cpf_socios[" + index + "]']").val();
                    }
                }

            } else {

                $(this).closest('tr').prev().add($(this).closest()).after(
                    '<tr bgcolor="#F5F5FB" class="texto"><td colspan="1"><b>Sócio ' + (indice_socios + 1) + '</b></td><td colspan="3"><button type="button" remove-socio="' + indice_socios + '" class="rmSocio btn btn-danger btn-xs">Excluir</button></td></tr><tr bgcolor="#F5F5FB" class="texto data-rmv"><td><b>Nome</b></td><td><input type="text" name="novo_ug_nome_socios[' + indice_socios + ']" title="Consulte o CPF na Receita para preenchimento do Nome" value="" maxlength="100" size="50" class="texto"></td><td><b>Porcentagem na Empresa</b></td><td><input type="text" name="novo_ug_porcentagem_socios[' + indice_socios + ']" value="" maxlength="20" size="20" class="texto"></td></tr><tr bgcolor="#F5F5FB" class="texto data-rmv"><td><b>CPF</b></td><td><input type="text" name="novo_ug_cpf_socios[' + indice_socios + ']" value="" maxlength="14" size="20" class="texto class_cpf_socios"></td><td><b>Data de Nascimento</b></td><td><input type="text" name="novo_ug_data_nascimento_socios[' + indice_socios + ']" value="" maxlength="10" size="20" class="texto data data-nasc">&nbsp;&nbsp;&nbsp;<button type="button" onclick="pegaNomeSocioRF(' + indice_socios + ');" class="btn btn-info btn-xs consultar">Consultar na Receita</button>&nbsp;&nbsp;&nbsp;<span class="loading"></span></td></tr>'
                );
                indice_socios++;
            }
        });

        //Função para buscar o endereço.
        $("#novo_ug_cep").keyup(function () {
            cep = this.value;
            if (cep.length == 8 && !searching) {
                $.ajax({
                    type: "POST",
                    url: "/includes/cep.php",
                    data: "cep=" + cep,
                    beforeSend: function () {
                        searching = true;
                        $("#info_cep").html("<b>Aguarde... Procurando CEP.</b>");
                    },
                    success: function (txt) {
                        searching = false;
                        $("#info_cep").html("");
                        if (txt.indexOf("NO_ACCESS") < 0) {
                            var msg = 'Você gostaria de trocar o endereço abaixo:\n';
                            msg += 'Endereço: ' + document.getElementById("novo_ug_tipo_end").value + ' ' + document.getElementById("novo_ug_endereco").value + '\n';
                            msg += document.getElementById("novo_ug_bairro").value + ' - ' + document.getElementById("novo_ug_cidade").value + ' - ' + document.getElementById("novo_ug_estado").value + '\n\n';
                            msg += 'por este novo endereço?\n';

                            if (txt.indexOf("ERRO") < 0) {
                                txt = txt.split("&");

                                msg += 'Endereço: ' + txt[0].trim() + ' ' + txt[1] + '\n';
                                msg += txt[2] + ' - ' + txt[3] + ' - ' + txt[4];

                                if (confirm(msg)) {

                                    document.getElementById("novo_ug_tipo_end").value = txt[0].trim();
                                    document.getElementById("novo_ug_endereco").value = txt[1].trim();
                                    document.getElementById("novo_ug_bairro").value = txt[2].trim();
                                    document.getElementById("novo_ug_cidade").value = txt[3].trim();
                                    document.getElementById("novo_ug_estado").value = txt[4].trim();
                                    document.getElementById("novo_ug_numero").focus();

                                } else {
                                    document.getElementById("novo_ug_cep").value = "";
                                }
                            }
                            else {
                                funcZerar();
                                document.getElementById("novo_ug_cep").value = "";
                                alert("CEP Inexistente!");
                            }
                        }
                        else {
                            funcZerar();
                            document.getElementById("novo_ug_cep").value = "";
                            alert("[ERRO 404] - Consulta de CEP indisponível no momento. Tente novamente mais tarde.");
                        }
                    },
                    error: function () {
                        $("#info_cep").html("");
                        funcZerar();
                        alert("Erro no servidor, por favor tente mais tarde.");
                    }
                });
            }
        });

        function funcZerar() {
            document.getElementById("novo_ug_tipo_end").value = "Tipo";
            document.getElementById("novo_ug_endereco").value = "";
            document.getElementById("novo_ug_bairro").value = "";
            document.getElementById("novo_ug_cidade").value = "";
            document.getElementById("novo_ug_estado").value = "Estado";
            document.getElementById("novo_ug_cep").focus();
        }
    });

    var searching = false;
    function pegaNomeSocioRF(index) {

        if ($("input[name='novo_ug_cpf_socios[" + index + "]']").val().trim().length == 14 && $("input[name='novo_ug_data_nascimento_socios[" + index + "]']").val().trim().length == 10 && !searching) {
            $.ajax({
                type: "POST",
                url: "/ajax/ajaxCpf.php",
                dataType: "json",
                data: { cpf: $("input[name='novo_ug_cpf_socios[" + index + "]']").val(), dataNascimento: $("input[name='novo_ug_data_nascimento_socios[" + index + "]']").val() },
                beforeSend: function () {
                    searching = true;
                    $(".loading").html("<img src='/images/ajax-loader.gif' width='30' height='30' title='Consultando...'>");
                },
                success: function (txt) {
                    searching = false;
                    if (txt.erros.length > 0) {
                        $("input[name='novo_ug_cpf_socios[" + index + "]']").val("");
                        $("input[name='novo_ug_data_nascimento_socios[" + index + "]']").val("");
                        alert(txt.erros);
                    } else {
                        var nome = fix_name_js(txt.nome.substr(0, 480));
                        $("input[name='novo_ug_nome_socios[" + index + "]']").val(nome);
                    }
                    $(".loading").addClass("hidden");
                },
                error: function (x, y) {
                    $(".loading").addClass("hidden");
                    return false;
                }
            });
        } else {
            alert("Preencha corretamente os campos CPF e Data de Nascimento");
            return false;
        }
    }


    function fcnSalvarCadastro(cod) {

        form1.action = '?acao=sto&usuario_id=' + cod;
        form1.submit();
    }

    function fcnVoltar(cod) {
        form1.action = 'com_usuario_detalhe.php?usuario_id=' + cod;
        form1.submit();
    }

    function mudarSelect() {
        var x = document.getElementById('novo_ug_substatus');
        var y = '';
        var ativos = new Array(2);
        ativos[0] = 'Selecione o Substatus'; ativos[1] = 'Ainda não fez 1º compra';
        var inativos = new Array(2);
        inativos[0] = 'Selecione o Substatus'; inativos[1] = 'Pendente de Contato e Análise'; inativos[2] = 'Loja não Localizada'; inativos[3] = 'Representante Divergente'; inativos[4] = 'Cadastro não Aprovado'; inativos[5] = 'Sem Interesse'; inativos[6] = 'Não quer mais vender'; inativos[7] = 'Bloqueado por fraude'; inativos[8] = 'Pré-Cadastro/Prospecção';

        for (var i = x.length - 1; i >= 0; i--)
            x.remove(x[i]);

        if (document.getElementById('novo_ug_ativo').selectedIndex == 2) {
            y = document.createElement('option'); y.text = ativos[0]; y.value = '';
            try { x.add(y, null); } catch (ex) { x.add(y); }
            y = document.createElement('option'); y.text = ativos[1]; y.value = '9';
            try { x.add(y, null); } catch (ex) { x.add(y); }
        } else if (document.getElementById('novo_ug_ativo').selectedIndex == 1) {
            for (i = 0; i < inativos.length; i++) {
                y = document.createElement('option'); y.text = inativos[i]; y.value = i;
                try { x.add(y, null); } catch (ex) { x.add(y); }
            }
        } else {
            for (i = 0; i < inativos.length; i++) {
                y = document.createElement('option'); y.text = inativos[i]; y.value = i;
                try { x.add(y, null); } catch (ex) { x.add(y); }
            }
            var y = document.createElement('option'); y.text = ativos[1]; y.value = '9';
            try { x.add(y, null); } catch (ex) { x.add(y); }
        }
    }
</script>

<?php
$msg = "";
$msgAcao = "";

//$usuario_id = 204;    //    'PJ'
//    $usuario_id = 468;    //    'REINALDOLH2'

if (!$usuario_id)
    $msg = "Código do usuário não fornecido.\n";
elseif (!is_numeric($usuario_id))
    $msg = "Código do usuário inválido.\n";

//echo "op: '$op'<br>";

//Processa Acoes
if ($msg == "") {

    //Alterar Dados do Estabelecimento
    if ($op && $op == "sto") {

        if ($msgAcao == "") {
            $cad_usuarioGames = new UsuarioGames($usuario_id);


            //                $cad_usuarioGames->setLogin($novo_ug_login);
//                $cad_usuarioGames->setSenha($novo_ug_senha);

            $cad_usuarioGames->setAtivo($novo_ug_ativo);
            $cad_usuarioGames->setStatusBusca($novo_ug_status_busca);
            $cad_usuarioGames->setSubstatus($novo_ug_substatus);
            $cad_usuarioGames->setNomeFantasia($novo_ug_nome_fantasia);
            $cad_usuarioGames->setRazaoSocial($novo_ug_razao_social);
            $cad_usuarioGames->setCNPJ($novo_ug_cnpj);
            $cad_usuarioGames->setResponsavel($novo_ug_responsavel);
            $cad_usuarioGames->setEmail($novo_ug_email);
            $cad_usuarioGames->setEndereco($novo_ug_endereco);
            $cad_usuarioGames->setTipoEnd($novo_ug_tipo_end);
            $cad_usuarioGames->setNumero($novo_ug_numero);
            $cad_usuarioGames->setComplemento($novo_ug_complemento);
            $cad_usuarioGames->setBairro($novo_ug_bairro);
            $cad_usuarioGames->setCidade($novo_ug_cidade);
            $cad_usuarioGames->setEstado($novo_ug_estado);
            $cad_usuarioGames->setCEP($novo_ug_cep);
            $cad_usuarioGames->setTelDDI($novo_ug_tel_ddi);
            $cad_usuarioGames->setTelDDD($novo_ug_tel_ddd);
            $cad_usuarioGames->setTel($novo_ug_tel);
            $cad_usuarioGames->setCelDDI($novo_ug_cel_ddi);
            $cad_usuarioGames->setCelDDD($novo_ug_cel_ddd);
            $cad_usuarioGames->setCel($novo_ug_cel);
            $cad_usuarioGames->setFaxDDI($novo_ug_fax_ddi);
            $cad_usuarioGames->setFaxDDD($novo_ug_fax_ddd);
            $cad_usuarioGames->setFax($novo_ug_fax);
            $cad_usuarioGames->setRACodigo($novo_ug_ra_codigo);
            $cad_usuarioGames->setRAOutros($novo_ug_ra_outros);
            $cad_usuarioGames->setContato01Nome($novo_ug_contato01_nome);
            $cad_usuarioGames->setContato01Cargo($novo_ug_contato01_cargo);
            $cad_usuarioGames->setContato01TelDDI($novo_ug_contato01_tel_ddi);
            $cad_usuarioGames->setContato01TelDDD($novo_ug_contato01_tel_ddd);
            $cad_usuarioGames->setContato01Tel($novo_ug_contato01_tel);
            $cad_usuarioGames->setObservacoes(substr($novo_ug_observacoes, 0, 2048));
            $cad_usuarioGames->setTipoCadastro($novo_ug_tipo_cadastro);
            $cad_usuarioGames->setNome($novo_ug_nome);
            $cad_usuarioGames->setRG($novo_ug_rg);
            $cad_usuarioGames->setCPF($novo_ug_cpf);
            $cad_usuarioGames->setDataNascimento($novo_ug_data_nascimento);
            $cad_usuarioGames->setSexo($novo_ug_sexo);
            $cad_usuarioGames->setPerfilSenhaReimpressao($novo_ug_perfil_senha_reimpressao);
            $cad_usuarioGames->setPerfilFormaPagto($novo_ug_perfil_forma_pagto);
            $cad_usuarioGames->setPerfilLimite($novo_ug_perfil_limite);
            $cad_usuarioGames->setInscrEstadual($novo_ug_inscr_estadual);
            $cad_usuarioGames->setSite($novo_ug_site);
            $cad_usuarioGames->setAberturaAno($novo_ug_abertura_ano);
            $cad_usuarioGames->setAberturaMes($novo_ug_abertura_mes);
            $cad_usuarioGames->setCartoes($novo_ug_cartoes);
            $cad_usuarioGames->setFaturaMediaMensal($cad_FaturaMediaMensal);
            $cad_usuarioGames->setReprLegalNome($novo_ug_repr_legal_nome);
            $cad_usuarioGames->setReprLegalRG($novo_ug_repr_legal_rg);
            $cad_usuarioGames->setReprLegalCPF($novo_ug_repr_legal_cpf);
            $cad_usuarioGames->setReprLegalDataNascimento($novo_ug_repr_legal_data_nascimento);
            $cad_usuarioGames->setReprLegalTelDDI($novo_ug_repr_legal_tel_ddi);
            $cad_usuarioGames->setReprLegalTelDDD($novo_ug_repr_legal_tel_ddd);
            $cad_usuarioGames->setReprLegalTel($novo_ug_repr_legal_tel);
            $cad_usuarioGames->setReprLegalCelDDI($novo_ug_repr_legal_cel_ddi);
            $cad_usuarioGames->setReprLegalCelDDD($novo_ug_repr_legal_cel_ddd);
            $cad_usuarioGames->setReprLegalCel($novo_ug_repr_legal_cel);
            $cad_usuarioGames->setReprLegalEmail($novo_ug_repr_legal_email);
            $cad_usuarioGames->setReprLegalMSN($novo_ug_repr_legal_msn);
            $cad_usuarioGames->setReprVendaIgualReprLegal($novo_ug_repr_venda_igual_repr_legal);
            $cad_usuarioGames->setReprVendaNome($novo_ug_repr_venda_nome);
            $cad_usuarioGames->setReprVendaRG($novo_ug_repr_venda_rg);
            $cad_usuarioGames->setReprVendaCPF($novo_ug_repr_venda_cpf);
            $cad_usuarioGames->setReprVendaTelDDI($novo_ug_repr_venda_tel_ddi);
            $cad_usuarioGames->setReprVendaTelDDD($novo_ug_repr_venda_tel_ddd);
            $cad_usuarioGames->setReprVendaTel($novo_ug_repr_venda_tel);
            $cad_usuarioGames->setReprVendaCelDDI($novo_ug_repr_venda_cel_ddi);
            $cad_usuarioGames->setReprVendaCelDDD($novo_ug_repr_venda_cel_ddd);
            $cad_usuarioGames->setReprVendaCel($novo_ug_repr_venda_cel);
            $cad_usuarioGames->setReprVendaEmail($novo_ug_repr_venda_email);
            $cad_usuarioGames->setReprVendaMSN($novo_ug_repr_venda_msn);
            $cad_usuarioGames->setDadosBancarios01Banco($novo_ug_dados_bancarios_01_banco);
            $cad_usuarioGames->setDadosBancarios01Agencia($novo_ug_dados_bancarios_01_agencia);
            $cad_usuarioGames->setDadosBancarios01Conta($novo_ug_dados_bancarios_01_conta);
            $cad_usuarioGames->setDadosBancarios01Abertura($novo_ug_dados_bancarios_01_abertura);
            $cad_usuarioGames->setDadosBancarios02Banco($novo_ug_dados_bancarios_02_banco);
            $cad_usuarioGames->setDadosBancarios02Agencia($novo_ug_dados_bancarios_02_agencia);
            $cad_usuarioGames->setDadosBancarios02Conta($novo_ug_dados_bancarios_02_conta);
            $cad_usuarioGames->setDadosBancarios02Abertura($novo_ug_dados_bancarios_02_abertura);
            $cad_usuarioGames->setComputadoresQtde($cad_ComputadoresQtde);
            if (is_array($cad_ComunicacaoVisual))
                $cad_usuarioGames->setComunicacaoVisual(implode(",", $cad_ComunicacaoVisual));
            $cad_usuarioGames->setPerfilCorteDiaSemana($novo_ug_perfil_corte_dia_semana);
            $cad_usuarioGames->setPerfilLimiteSugerido($novo_ug_perfil_limite_sugerido);
            $cad_usuarioGames->setNews($novo_ug_news);
            $cad_usuarioGames->setRiscoClassif($novo_ug_risco_classif);
            $cad_usuarioGames->setPerfilCorteUltimoCorte($novo_ug_perfil_corte_ultimo_corte);
            $cad_usuarioGames->setCreditoPendente($novo_ug_credito_pendente);

            $cad_usuarioGames->setPerfilLimiteRef($novo_ug_perfil_limite_referencia);

            $cad_usuarioGames->setUgOngame($ug_ongame);

            $cad_usuarioGames->setTipoEstabelecimento($ug_te_id);

            $cad_usuarioGames->setTipoVenda($novo_ug_tipo_venda);

            $cad_usuarioGames->setCanaisVenda($novo_ug_canais_venda);

            // NexCafe
            $cad_usuarioGames->setUgIdNexCafe($tf_u_login_nexcafe);
            $cad_usuarioGames->setUgLoginNexCafeAuto($tf_u_login_automatico_nexcafe ? $tf_u_login_automatico_nexcafe : 0);
            // VIP
            $cad_usuarioGames->setVIP($novo_ug_vip);

            //Possui Restrição de Vendas de Produtos
            $cad_usuarioGames->setPossuiRestricaoProdutos($novo_ug_possui_restricao_produtos);

            $cad_usuarioGames->setDataAprovacao($novo_ug_data_aprovacao);

            $cad_usuarioGames->setDataExpiraSenha($ug_data_expiracao_senha);

            $array_nomes_socios = $GLOBALS['_POST']['novo_ug_nome_socios'];
            $array_cpf_socios = $GLOBALS['_POST']['novo_ug_cpf_socios'];
            $array_data_nascimento_socios = $GLOBALS['_POST']['novo_ug_data_nascimento_socios'];
            $array_porcentagem_socios = $GLOBALS['_POST']['novo_ug_porcentagem_socios'];
            $num_registros = $GLOBALS['_POST']['num_registros_bd'];

            if ($msgAcao == "") {
                usuarios_games_log($GLOBALS['USUARIO_GAMES_LOG_TIPOS']['ALTERACAO_DO_CADASTRO'], $usuario_id, null, "Mod. por usuario bko id: " . $_SESSION['iduser_bko']);
            }

            if (count($array_nomes_socios) > 0) {
                $not_empty_nome = verificaValorVazioArray($array_nomes_socios);
                $not_empty_cpf = verificaValorVazioArray($array_cpf_socios);
                $not_empty_data_nascimento = verificaValorVazioArray($array_data_nascimento_socios);
                $not_empty_porcentagem = verificaValorVazioArray($array_porcentagem_socios);

                $total_porcentagem = 0;
                $coeficiente = 0.1;

                if ($not_empty_nome && $not_empty_cpf && $not_empty_data_nascimento && $not_empty_porcentagem) {

                    //Validação para que não tenha sócios duplicados
                    $verifica_repetidos = array_unique($array_cpf_socios);

                    if (count($array_cpf_socios) != count($verifica_repetidos)) {
                        $msgAcao = "Erro: Problema com sócios duplicados";

                    } else {
                        foreach ($array_porcentagem_socios as $valor) {
                            $total_porcentagem += str_replace(",", ".", $valor);
                        }
                        //Cálculo para aceitar uma diferença máxima na soma das porcentagens, nesse caso com coeficiente igual a 0.1
                        $diferenca = abs(100 - $total_porcentagem);

                        if ($diferenca <= $coeficiente) {
                            if ($msgAcao == "") {
                                if ($num_registros > 0) {
                                    $sql_delete = "DELETE FROM dist_usuarios_games_socios WHERE ug_id = " . $usuario_id . " ;";
                                    $ret_del = SQLexecuteQuery($sql_delete);
                                    if (!$ret_del) {
                                        $msgAcao = "Erro 1: ao atualizar/adicionar novos sócios";
                                    }
                                }
                            }

                            if ($msgAcao == "") {
                                for ($i = 0; $i < count($array_cpf_socios); $i++) {
                                    $sql_insert = "INSERT INTO dist_usuarios_games_socios (ug_id, ugs_nome, ugs_cpf, ugs_data_nascimento, ugs_percentagem) VALUES 
                                        (" . $usuario_id . " , '" . $array_nomes_socios[$i] . "' , '" . str_replace('.', '', str_replace('-', '', $array_cpf_socios[$i])) . "' , '" . formata_data($array_data_nascimento_socios[$i], 1) . " 00:00:00', " . str_replace(",", ".", $array_porcentagem_socios[$i]) . ");";
                                    $ret_insert = SQLexecuteQuery($sql_insert);

                                    if (!$ret_insert) {
                                        $msgAcao = "Erro 2: ao atualizar/adicionar novos sócios";
                                    }
                                }
                            }
                        } else {
                            $msgAcao = "Erro: A soma das porcentagens dos Sócios devem totalizar 100%";
                        }
                    }

                } else {
                    $msgAcao = "Erro: Todos os campos de Sócios devem ser preenchidos corretamente!";
                }
            }

            //Pre atualizacao
//                if($msgAcao == ""){
//                    $objUsuarioGames = UsuarioGames::getUsuarioGamesById($usuario_id);
//                    if($objUsuarioGames == null) $msgAcao = "Usuário não encontrado.\n";
//                }
            if ($msgAcao == "") {
                if ($novo_ug_ativo == "1") {
                    if (!$cad_usuarioGames->getPerfilFormaPagto() || trim($cad_usuarioGames->getPerfilFormaPagto()) == "") {
                        $msgAcao = "Não é possivel ativar este usuário, Forma de Pagamento ainda não definida.\n";
                    }
                }
            }

            if ($msgAcao == "") {
                //echo "<pre>".print_r($cad_usuarioGames, true)."</pre>";
                $instUsuarioGames = new UsuarioGames();

                // Livrodjx dit it right
                $is_ativo = $cad_usuarioGames->getAtivo();
                $nome_fantasia = $cad_usuarioGames->getNomeFantasia();
                $email = $cad_usuarioGames->getEmail();
                // Está ativo
                if ($is_ativo == 1) {
                    $chave_mestra = new ChaveMestra();
                    $my_chave = $chave_mestra->inserirChaveMestra($usuario_id);

                    if ($my_chave !== false) {
                        $envia_email = new EnvioEmailAutomatico('L', 'ChaveMestra');
                        $envia_email->setUgNome(ucwords(strtolower($nome_fantasia)));
                        $envia_email->setChaveMestra($my_chave);

                        $to = $email;
                        $cc = "";
                        $bcc = "";
                        $subject = "E-prepag - Chave Mestra";
                        $msg = $envia_email->getCorpoEmail();

                        enviaEmail4($to, $cc, $bcc, $subject, $msg, NULL);
                    }

                }
                $msgAcao = $instUsuarioGames->atualizar_sem_validar($cad_usuarioGames);
                //echo "ret: ".str_replace("\n", "<br>", $msgAcao)."<br>";
            }

            //Pos atualizacao
            if ($msgAcao == "") {
                if ($v_campo == 'ativo' && $v_valor_new == "1") {
                    //$msgAcao = UsuarioGames::enviaEmailAtivacao($usuario_id);
                }
            }

            if($msgAcao == ""){
                $con = ConnectionPDO::getConnection();
                $pdo = $con->getLink();
                $retorno_estilo = salva_estilos($usuario_id, $pdo);
                if($retorno_estilo != ""){
                    $msgAcao .= $retorno_estilo;
                }
            }
        }
    }


}

if ($msg == "") {
    if ($acao && $acao == 'exclui_socio') {
        $sql = "DELETE FROM dist_usuarios_games_socios WHERE ug_id = " . $usuario_id . " AND ugs_cpf ='" . str_replace('.', '', str_replace('-', '', $novo_ug_cpf_socios)) . "';";
        $ret = SQLexecuteQuery($sql);
        if (!$ret)
            $msgAcao = "Erro ao excluir sócio.\n";
    }
}


//Recupera dados do usuario
if ($msg == "") {
    $instUsuarioGames = new UsuarioGames();
    $objUsuarioGames = $instUsuarioGames->getUsuarioGamesById($usuario_id);

    if ($objUsuarioGames == null)
        $msg = "Nenhum usuário encontrado.\n";
    else {

        //RA
        if (is_null($objUsuarioGames->getRACodigo()) || trim($objUsuarioGames->getRACodigo()) == "") {
            $cad_RA = $objUsuarioGames->getRAOutros();
        } else {
            $resatv = SQLexecuteQuery("select ra_codigo, ra_desc from ramo_atividade where ra_codigo = '" . $objUsuarioGames->getRACodigo() . "'");
            if ($resatv)
                $pgatv = pg_fetch_array($resatv);
            $cad_RA = $pgatv['ra_desc'];
        }
    }

}
$msg .= $msgAcao;


$cor1 = "#F5F5FB";
$cor2 = "#F5F5FB";
$cor3 = "#FFFFFF";

$con = ConnectionPDO::getConnection();
$pdo = $con->getLink();
$estilos = getEstilosUsuarioPDO($objUsuarioGames->getId(), $pdo);

if (!filter_var($estilos['email_suporte'], FILTER_VALIDATE_EMAIL)){
    $estilos['email_suporte'] = "";
}
if (!filter_var($estilos['link_canal'], FILTER_VALIDATE_URL)) {
    $estilos['link_canal'] = "";
}

ob_end_flush();
?>
<script language="javascript">
    function GP_popupAlertMsg(msg) { //v1.0
        document.MM_returnValue = alert(msg);
    }
    function GP_popupConfirmMsg(msg) { //v1.0
        document.MM_returnValue = confirm(msg);
    }
</script>

<script language="javascript">

    function trimAll(sString) {
        while (sString.substring(0, 1) == ' ')
            sString = sString.substring(1, sString.length);
        while (sString.substring(sString.length - 1, sString.length) == ' ')
            sString = sString.substring(0, sString.length - 1);

        return sString;
    }

</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice -
                <?php echo $currentAba->getDescricao(); ?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a
                href="<?php echo $sistema->item->getLink(); ?>"><?php echo $sistema->item->getDescricao(); ?></a></li>
    </ol>
</div>
<?php
if (!$usuario_id || !is_numeric($usuario_id)) {
    $msg = "Código do usuário inválido.\n";
    ?>
    <div class="col-md-12 col-lg-12 col-xs-12 col-sm-12 txt-vermelho">
        <p><?= $msg ?></p>
    </div>
    <?php
    require_once $raiz_do_projeto . "backoffice/includes/rodape_bko.php";
    die;
}
?>
<form name="form1" method="post" action="com_usuario_detalhe_salva.php" enctype="multipart/form-data">
    <input type="hidden" name="v_campo" value="">
    <input type="hidden" name="v_valor_old" value="">
    <input type="hidden" name="v_valor_new" value="">
    <input type="hidden" name="op" value="sto">
    <input type="hidden" name="novo_ug_tipo_cadastro" value="<?php echo $objUsuarioGames->getTipoCadastro(); ?>">
    <div class="col-md-12">
        <ol class="breadcrumb top10">
            <li class="active">PDV - Edita Usuário</li>
        </ol>
    </div>
    <div class="col-md-12 col-sm-12 col-xs-12 pull-left">
        <input type="button" value="Voltar" class="btn pull-left btn-info"
            Onclick="fcnVoltar(<?php echo $usuario_id ?>)"></button>
    </div>
    <?php if ($msg != "") { ?>
        <div class="col-md-12 col-sm-12 col-xs-12 pull-left txt-vermelho">
            <?php echo str_replace("\n", "<br>", $msg) ?>
        </div>
    <?php } elseif ($msg == "" && $op == "sto") { ?>
        <div class="col-md-12 col-sm-12 col-xs-12 pull-left txt-azul">
            Atualizado com sucesso!!!
        </div>
    <?php } ?>
    <div class="col-md-12 col-sm-12 col-xs-12 pull-right">
        <input type="button" value="Salvar Cadastro" Onclick="fcnSalvarCadastro(<?php echo $usuario_id ?>)"
            class="btn btn-info pull-right">
    </div>
    <table class="txt-preto fontsize-p">
        <tr>
            <td>
                <table class="table">
                    <tr bgcolor="#FFFFFF" class="texto">
                        <td colspan="4" bgcolor="#ECE9D8">Perfil</font>
                        </td>
                    </tr>
                    <tr bgcolor="#F5F5FB" class="texto">
                        <td><b>Forma de Pagamento</b></td>
                        <td>
                            <input type="hidden" name="novo_ug_perfil_forma_pagto"
                                value="<?php echo $objUsuarioGames->getPerfilFormaPagto(); ?>">
                            <select name="just_diplay_novo_ug_perfil_forma_pagto" class="texto" readonly
                                disabled="disabled">
                                <option value="0">Selecione a Forma de Pagamento</option>
                                <?php foreach ($FORMAS_PAGAMENTO_DESCRICAO as $formaPagtoId => $formaPagtoDesc) { ?>
                                    <option value="<?php echo $formaPagtoId; ?>" <?php if ($objUsuarioGames->getPerfilFormaPagto() == $formaPagtoId)
                                           echo "selected"; ?>>
                                        <?php echo $formaPagtoDesc; ?>
                                    </option>
                                <?php } ?>
                            </select>

                        </td>
                        <td width="140"><b>Senha de Reimpressão</b></td>
                        <td width="307"><input type="text" name="novo_ug_perfil_senha_reimpressao"
                                value="<?php echo $objUsuarioGames->getPerfilSenhaReimpressao() ?>" maxlength="50"
                                size="10" class="texto"></td>
                    </tr>
                    <tr bgcolor="#F5F5FB" class="texto">
                        <td><b>Limite</b></td>
                        <td><input type="text" name="novo_ug_perfil_limite"
                                value="<?php echo number_format($objUsuarioGames->getPerfilLimite(), 2, ',', '.') ?>"
                                maxlength="10" size="10" class="texto"></td>
                        <td><b>Saldo atual</b></td>
                        <td><?php echo number_format($objUsuarioGames->getPerfilSaldo(), 2, ',', '.') ?>
                            <?php if ($objUsuarioGames->getCreditoPendente() > 0 && false) { ?>
                                <font color="#FF0000">(<b>Crédito Pendente:
                                        <?php echo number_format($objUsuarioGames->getCreditoPendente(), 2, ',', '.') ?></b>)
                                </font><?php } ?>
                        </td>
                    </tr>
                    <tr bgcolor="#F5F5FB" class="texto">
                        <td><b>Limite Sugerido</b></td>
                        <td><input type="text" name="novo_ug_perfil_limite_sugerido"
                                value="<?php echo number_format($objUsuarioGames->getPerfilLimiteSugerido(), 2, ',', '.') ?>"
                                maxlength="10" size="10" class="texto"></td>
                        <td><b>Dia de Corte</b></a></td>
                        <td>
                            <select name="novo_ug_perfil_corte_dia_semana" class="texto">
                                <option value="">Selecione o Dia de Corte</option>
                                <?php foreach ($GLOBALS['CORTE_DIAS_DA_SEMANA_DESCRICAO'] as $formaPagtoId => $formaPagtoDesc) { ?>
                                    <option value="<?php echo $formaPagtoId; ?>" <?php if ($objUsuarioGames->getPerfilCorteDiaSemana() == $formaPagtoId)
                                           echo "selected"; ?>>
                                        <?php echo $formaPagtoDesc; ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>
                    <tr bgcolor="#F5F5FB" class="texto">
                        <td><b>Limite de Referência</b></td>
                        <td><input type="text" name="novo_ug_perfil_limite_referencia"
                                value="<?php echo number_format($objUsuarioGames->getPerfilLimiteRef(), 2, ',', '.') ?>"
                                maxlength="10" size="10" class="texto"></td>
                        <td><b>Usuário VIP, Master, Black, etc?</b></td>
                        <td>
                            <select name="novo_ug_vip" class="texto">
                                <option value="" <?php if ($objUsuarioGames->getVIP() == "")
                                    echo "selected" ?>>Selecione
                                    </option>
                                    <option value="0" <?php if (is_numeric($objUsuarioGames->getVIP()) && $objUsuarioGames->getVIP() == 0)
                                    echo "selected"; ?>>Não</option>
                                <option value="1" <?php if ($objUsuarioGames->getVIP() == 1)
                                    echo "selected"; ?>>VIP
                                </option>
                                <option value="2" <?php if ($objUsuarioGames->getVIP() == 2)
                                    echo "selected"; ?>>MASTER
                                </option>
                                <option value="3" <?php if ($objUsuarioGames->getVIP() == 3)
                                    echo "selected"; ?>>BLACK
                                </option>
                                <option value="4" <?php if ($objUsuarioGames->getVIP() == 4)
                                    echo "selected"; ?>>GOLD
                                </option>
                                <option value="5" <?php if ($objUsuarioGames->getVIP() == 5)
                                    echo "selected"; ?>>PLATINUM
                                </option>
                            </select>
                        </td>
                    </tr>
                    <tr bgcolor="#F5F5FB" class="texto">
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td><b>Possui Restrição de Vendas de Produtos?</b></td>
                        <td>
                            <select name="novo_ug_possui_restricao_produtos" class="texto">
                                <option value="" <?php if ($objUsuarioGames->getPossuiRestricaoProdutos() == "")
                                    echo "selected" ?>>Selecione</option>
                                    <option value="0" <?php if (is_numeric($objUsuarioGames->getPossuiRestricaoProdutos()) && $objUsuarioGames->getPossuiRestricaoProdutos() == 0)
                                    echo "selected"; ?>>Não
                                </option>
                                <option value="1" <?php if ($objUsuarioGames->getPossuiRestricaoProdutos() == 1)
                                    echo "selected"; ?>>SIM</option>
                            </select>
                        </td>
                    </tr>
                    <tr bgcolor="#FFFFFF" class="texto">
                        <td colspan="4" bgcolor="#ECE9D8">Dados Administrativos</font>
                        </td>
                    </tr>
                    <tr bgcolor="#F5F5FB" class="texto">
                        <td><b>C&oacute;digo</b></td>
                        <td><?php echo $objUsuarioGames->getId() ?></td>
                        <td><b>Status</b></td>
                        <td>
                            <select name="novo_ug_ativo" class="texto">
                                <?php // onChange="javascript:mudarSelect();"> ?>
                                <option value="">Selecione o Status</option>
                                <option value="0" <?php if ($objUsuarioGames->getAtivo() != 1)
                                    echo "selected"; ?>>Inativo
                                </option>
                                <option value="1" <?php if ($objUsuarioGames->getAtivo() == 1)
                                    echo "selected"; ?>>Ativo
                                </option>
                            </select>
                        </td>
                    </tr>
                    <tr bgcolor="#F5F5FB" class="texto">
                        <td><b>Login</b></td>
                        <td><?php echo $objUsuarioGames->getLogin() ?></td>
                        <?php if (in_array(44, $grupos)) { ?>
                            <td><b>Email</b></td>
                            <td><input type="text" name="novo_ug_email" value="<?php echo $objUsuarioGames->getEmail() ?>"
                                    maxlength="100" size="40" class="texto"></td>
                        <?php } else { ?>
                            <td></td>
                            <td></td>
                        <?php } ?>
                    </tr>
                    <tr bgcolor="#F5F5FB" class="texto">
                        <td><b>Data de Cadastro</b></td>
                        <td><?php echo $objUsuarioGames->getDataInclusao() ?></td>
                        <td><b>Status Busca</b></td>
                        <td>
                            <select name="novo_ug_status_busca" class="texto">
                                <option value="">Selecione o Status de Busca</option>
                                <option value="2" <?php if ($objUsuarioGames->getStatusBusca() != 1)
                                    echo "selected"; ?>>
                                    Inativo</option>
                                <option value="1" <?php if ($objUsuarioGames->getStatusBusca() == 1)
                                    echo "selected"; ?>>
                                    Ativo</option>
                            </select>
                        </td>
                    </tr>
                    <tr bgcolor="#F5F5FB" class="texto">
                        <td><b>Qtde de Acessos</b></td>
                        <td><?php echo $objUsuarioGames->getQtdeAcessos() ?></td>
                        <td><b>Data Último Acesso</b></td>
                        <td><?php echo $objUsuarioGames->getDataUltimoAcesso() ?></td>
                    </tr>
                    <tr bgcolor="#F5F5FB" class="texto">
                        <td><b>Data de expiração de senha</b></td>
                        <td colspan="3">
                            <input name="ug_data_expiracao_senha" type="text" value="<?php if ($objUsuarioGames->getDataExpiraSenha() != "")
                                echo $objUsuarioGames->getDataExpiraSenha(); ?>" class="form"
                                id="ug_data_expiracao_senha" value="" size="9" maxlength="10">
                        </td>
                    </tr>
                    <tr bgcolor="#FFFFFF" class="texto">
                        <td colspan="4" bgcolor="#ECE9D8">
                            <table border="0" cellspacing="0" cellpadding="0" width="0">
                                <tr>
                                    <td align="left" class="texto">Habilitado para ONGAME (PB):</td>
                                    <td align="left" class="texto">&nbsp;&nbsp;&nbsp;&nbsp;<br>
                                        <select name="ug_ongame" class="field_dados">
                                            <option value="s" <?php if (strtolower($objUsuarioGames->getUgOngame()) == "s")
                                                echo " selected" ?>>Sim - Habilitado</option>
                                                <option value="n" <?php if (strtolower($objUsuarioGames->getUgOngame()) != "s")
                                                echo " selected" ?>>Não - Desabilitado</option>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <tr bgcolor="#FFFFFF" class="texto">
                            <td colspan="4" bgcolor="#ECE9D8">Cadastro</font>
                            </td>
                        </tr>
                        <tr bgcolor="#F5F5FB" class="texto">
                            <td><b>Tipo Estab.</b></td>
                            <td>
                                <select name="ug_te_id" class="">
                                    <option value="" <?php if (is_null($objUsuarioGames->getTipoEstabelecimento())) {
                                                echo "selected";
                                            } ?>>N&atilde;o Informado</option>
                                <?php
                                //colocar rotina para criação do combo de seleção do tipo de estabelecimento
                                $sql = "select * from tb_tipo_estabelecimento order by te_ativo DESC,te_descricao";
                                $res_te = SQLexecuteQuery($sql);
                                while ($res_te_row = pg_fetch_array($res_te)) {
                                    $te_codigo = $res_te_row['te_id'];
                                    if ($objUsuarioGames->getTipoEstabelecimento() == $te_codigo) {
                                        $select = "selected = 'selected' ";
                                    } else {
                                        $select = '';
                                    }
                                    ?>
                                    <option value='<?php echo $te_codigo ?>' <?php echo $select ?>>
                                        <?php echo utf8_decode($res_te_row['te_descricao']); ?>
                                        (<?php if ($res_te_row['te_ativo'])
                                            echo "Ativo";
                                        else
                                            echo "Inativo"; ?>)
                                    </option>
                                    <?php
                                }//end while
                                ?>
                            </select>
                        </td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    <?php if ($objUsuarioGames->getTipoCadastro() == "PJ") { ?>
                        <tr bgcolor="#F5F5FB" class="texto">
                            <td><b>Nome Fantasia</b></td>
                            <td><input type="text" name="novo_ug_nome_fantasia"
                                    value="<?php echo $objUsuarioGames->getNomeFantasia() ?>" maxlength="100" size="50"
                                    class="texto"></td>
                            <td><b>Razão Social</b></td>
                            <td><input type="text" name="novo_ug_razao_social"
                                    value="<?php echo $objUsuarioGames->getRazaoSocial() ?>" maxlength="100" size="50"
                                    class="texto"></td>
                        </tr>
                        <tr bgcolor="#F5F5FB" class="texto">
                            <td><b>CNPJ</b></td>
                            <td><input type="text" name="novo_ug_cnpj" value="<?php echo $objUsuarioGames->getCNPJ() ?>"
                                    maxlength="20" size="20" class="texto"></td>
                            <td><b>Inscrição Estadual</b></td>
                            <td><input type="text" name="novo_ug_inscr_estadual"
                                    value="<?php echo $objUsuarioGames->getInscrEstadual() ?>" maxlength="20" size="20"
                                    class="texto"></td>
                        </tr>
                        <tr bgcolor="#F5F5FB" class="texto">
                            <td><b>Responsável</b></td>
                            <td><input type="text" name="novo_ug_responsavel"
                                    value="<?php echo $objUsuarioGames->getResponsavel() ?>" maxlength="20" size="20"
                                    class="texto"></td>
                            <td><b>Abertura da Empresa</b></td>
                            <td><input type="text" name="novo_ug_abertura_mes"
                                    value="<?php echo $objUsuarioGames->getAberturaMes() ?>" maxlength="2" size="2"
                                    class="texto">/<input type="text" name="novo_ug_abertura_ano"
                                    value="<?php echo $objUsuarioGames->getAberturaAno() ?>" maxlength="4" size="4"
                                    class="texto"></td>
                        </tr>
                        <tr bgcolor="#F5F5FB" class="texto">
                            <td><b>Site</b></td>
                            <td><input type="text" name="novo_ug_site" value="<?php echo $objUsuarioGames->getSite() ?>"
                                    maxlength="250" class="texto"></td>
                            <td><b>Substatus</b></td>
                            <td>
                                <select name="novo_ug_substatus" class="form2">
                                    <?php
                                    //<option value="" <_?php  if($tf_u_substatus == "") echo "selected" ?_>>Selecione</option>
                                    if ($tf_u_substatus == "" && $objUsuarioGames->getSubstatus() != '') {
                                        $tf_u_substatus = $objUsuarioGames->getSubstatus();
                                    } elseif (empty($tf_u_substatus)) {
                                        $tf_u_substatus = 0;
                                    }
                                    foreach ($SUBSTATUS_LH as $indice => $dado) {
                                        echo "<option value=\"" . $indice . "\"";
                                        if (strcmp($tf_u_substatus, $indice) == 0)
                                            echo "selected";
                                        echo " >" . $dado . " (" . $indice . ")</option>\n";
                                    }
                                    ?>
                                </select>
                                <?php
                                if ($objUsuarioGames->getDataAprovacao() != "") {
                                    echo "<br>Data de Aprovação do PDV em: <b>" . $objUsuarioGames->getDataAprovacao() . "</b>";
                                }//end if(!empty($objUsuarioGames->getDataAprovacao()))
                                ?>
                                <input type="hidden" name="novo_ug_data_aprovacao" id="novo_ug_data_aprovacao"
                                    value="<?php echo $objUsuarioGames->getDataAprovacao(); ?>">
                            </td>
                        </tr>
                        <!--tr bgcolor="#F5F5FB" class="texto"> 
            <td><b>Ramo de Atividade</b></td>
            <td><?php echo $cad_RA ?></td>
            <td colspan="2"></td>
          </tr-->
                        <tr bgcolor="#F5F5FB" class="texto">
                            <td><b>Fat. Médio Mensal</b></td>
                            <td>
                                <?php foreach ($CADASTRO_FATURAMENTO as $FaturamentoId => $FaturamentoDesc) { ?>
                                    <nobr><input type="radio" name="cad_FaturaMediaMensal" value="<?php echo $FaturamentoId; ?>"
                                            <?php if ($objUsuarioGames->getFaturaMediaMensal() == $FaturamentoId)
                                                echo "checked"; ?>><?php echo $FaturamentoDesc; ?>&nbsp;&nbsp;&nbsp;&nbsp;</nobr>
                                    <?php if (is_null($i))
                                        $i = 0;
                                    $i++;
                                    if ($i % 2 == 1)
                                        echo "<br>"; ?>
                                <?php } ?>
                            </td>
                            <td><b>Qtde Computadores</b></td>
                            <td>
                                <?php foreach ($CADASTRO_COMPUTADORES as $ComputadoresId => $ComputadoresDesc) { ?>
                                    <nobr><input type="radio" name="cad_ComputadoresQtde" value="<?php echo $ComputadoresId; ?>"
                                            <?php if ($objUsuarioGames->getComputadoresQtde() == $ComputadoresId)
                                                echo "checked"; ?>><?php echo $ComputadoresDesc; ?></nobr>
                                <?php } ?>
                            </td>
                        </tr>
                        <tr bgcolor="#F5F5FB" class="texto">
                            <td><b>Cartões</b></td>
                            <td>
                                <?php $cad_Cartoes = preg_split("/;/", $objUsuarioGames->getCartoes()); ?>
                                <?php foreach ($cad_Cartoes as $CartaoId) { ?>
                                    <?php echo $GLOBALS['CADASTRO_CARTOES'][$CartaoId] ?>,
                                <?php } ?>
                            </td>
                            <td><b>Comunicação Visual</b></td>
                            <td>
                                <?php $cad_ComunicacaoVisual = explode(",", $objUsuarioGames->getComunicacaoVisual()); ?>
                                <?php foreach ($CADASTRO_COMUNICACAO as $ComunicacaoId => $ComunicacaoDesc) { ?>
                                    <nobr><input type="checkbox" name="cad_ComunicacaoVisual[]"
                                            value="<?php echo $ComunicacaoId; ?>" <?php if (in_array($ComunicacaoId, $cad_ComunicacaoVisual))
                                                   echo "checked"; ?>><?php echo $ComunicacaoDesc; ?>&nbsp;&nbsp;&nbsp;&nbsp;</nobr>
                                <?php } ?>
                                <?php /* foreach ($cad_ComunicacaoVisual as $ComunicacaoId){ ?>
                  <?php echo $GLOBALS['CADASTRO_COMUNICACAO'][$ComunicacaoId]?>,
              <?php } */ ?>
                            </td>
                        </tr>
                        <tr bgcolor="#F5F5FB" class="texto">
                            <td><strong>Canais de venda</strong></td>
                            <td><textarea name="novo_ug_canais_venda" rows="3" cols="100"
                                    class="texto"><?php echo $objUsuarioGames->getCanaisVenda(); ?></textarea></td>
                            <td><strong>Tipo de Venda</strong></td>
                            <td><select name="novo_ug_tipo_venda" id="novo_ug_tipo_venda" class="form2">
                                    <option value="0" <?php echo ($objUsuarioGames->getTipoVenda() == '') ? 'selected="selected" ' : ''; ?>>-- Selecione --</option>
                                    <option value="1" <?php echo ($objUsuarioGames->getTipoVenda() == '1') ? 'selected="selected" ' : ''; ?>>Online</option>
                                    <option value="2" <?php echo ($objUsuarioGames->getTipoVenda() == '2') ? 'selected="selected" ' : ''; ?>>Offline</option>
                                    <option value="3" <?php echo ($objUsuarioGames->getTipoVenda() == '3') ? 'selected="selected" ' : ''; ?>>Offline e Online</option>
                                </select></td>
                        </tr>

                        <tr bgcolor="#FFFFFF" class="texto">
                            <td colspan="4" bgcolor="#ECE9D8">Cadastro</font>
                            </td>
                        </tr>
                        <tr bgcolor="#F5F5FB" class="texto">
                            <td><b>Cor primária</b></td>
                            <td>
                                <input type="color" name="cor_primaria" id="cor_primaria" value="<?= htmlspecialchars($estilos['cor_primaria']) ?>"/>
                            </td>
                            <td><b>Cor secundária</b></td>
                            <td>
                                <input type="color" name="cor_secundaria" id="cor_secundaria" value="<?= htmlspecialchars($estilos['cor_secundaria']) ?>"/>
                            </td>
                        </tr>
                        <tr bgcolor="#F5F5FB" class="texto">
                            <td><b>E-mail de suporte</b></td>
                            <td>
                                <input type="text" name="email_suporte" id="email_suporte" value="<?= htmlspecialchars($estilos['email_suporte']) ?>"/>
                            </td>
                            <td><b>Canal de atendimento</b></td>
                            <td>
                                <input type="text" name="link_canal" id="link_canal" value="<?= htmlspecialchars($estilos['link_canal']) ?>"/>
                            </td>
                        </tr>
                        <tr bgcolor="#F5F5FB" class="texto">
                            <td><b>Mensagem</b></td>
                            <td>
                                <textarea name="mensagem" id="mensagem" rows="3" cols="100"
                                    class="texto"><?= htmlspecialchars($estilos['mensagem']) ?></textarea></td>
                            </td>
                            <td><b>Logo</b></td>
                            <td>
                                <input type="file" name="logo" id="logo"/>
                                <?php echo "<img src='" . $estilos['logo_base64'] . "' title='logo' alt='Sem logo' border='0' class='imagem_epp' style='max-width: 100px; max-height: 25px;'>"; ?>
                                <?php
                                    if (!empty($estilos["logo_base64"])){
                                        echo '<div>
                                        <label for="rm_logo" style="font-weight: normal; margin-right: 5px;" >Remover logo</label>
                                        <input type="checkbox" name="rm_logo" id="rm_logo" />
                                        </div>';
                        }
                        ?>
                            </td>
                        </tr>

                        <tr bgcolor="#FFFFFF" class="texto">
                            <td colspan="4" bgcolor="#ECE9D8">Representante Legal da Empresa</font>
                            </td>
                        </tr>
                        <tr bgcolor="#F5F5FB" class="texto">
                            <td><b>Nome</b></td>
                            <td><input type="text" name="novo_ug_repr_legal_nome"
                                    value="<?php echo $objUsuarioGames->getReprLegalNome() ?>" maxlength="50" size="50"
                                    class="texto"></td>
                            <td><b>Data Nascimento</b></td>
                            <td><input type="text" name="novo_ug_repr_legal_data_nascimento" value="<?php $data_nasc = ($objUsuarioGames->getReprLegalDataNascimento() != "") ? formata_data($objUsuarioGames->getReprLegalDataNascimento(), 0) : $objUsuarioGames->getReprLegalDataNascimento();
                            echo $data_nasc; ?>" maxlength="10" size="10" class="texto data-nasc"></td>
                        </tr>
                        <tr bgcolor="#F5F5FB" class="texto">
                            <td><b>CPF</b></td>
                            <td><input type="text" name="novo_ug_repr_legal_cpf"
                                    value="<?php echo $objUsuarioGames->getReprLegalCPF() ?>" maxlength="20" size="20"
                                    class="texto"></td>
                            <td><b>RG</b></td>
                            <td><input type="text" name="novo_ug_repr_legal_rg"
                                    value="<?php echo $objUsuarioGames->getReprLegalRG() ?>" maxlength="20" size="20"
                                    class="texto"></td>
                        </tr>
                        <tr bgcolor="#F5F5FB" class="texto">
                            <td><b>Telefone</b></td>
                            <td>(<input type="text" name="novo_ug_repr_legal_tel_ddi"
                                    value="<?php echo $objUsuarioGames->getReprLegalTelDDI() ?>" maxlength="2" size="2"
                                    class="texto">) (<input type="text" name="novo_ug_repr_legal_tel_ddd"
                                    value="<?php echo $objUsuarioGames->getReprLegalTelDDD() ?>" maxlength="2" size="2"
                                    class="texto">) <input type="text" name="novo_ug_repr_legal_tel"
                                    value="<?php echo $objUsuarioGames->getReprLegalTel() ?>" maxlength="9" size="9"
                                    class="texto"></td>
                            <td><b>Celular</b></td>
                            <td>(<input type="text" name="novo_ug_repr_legal_cel_ddi"
                                    value="<?php echo $objUsuarioGames->getReprLegalCelDDI() ?>" maxlength="2" size="2"
                                    class="texto">) (<input type="text" name="novo_ug_repr_legal_del_ddd"
                                    value="<?php echo $objUsuarioGames->getReprLegalCelDDD() ?>" maxlength="2" size="2"
                                    class="texto">) <input type="text" name="novo_ug_repr_legal_cel"
                                    value="<?php echo $objUsuarioGames->getReprLegalCel() ?>" maxlength="9" size="9"
                                    class="texto"></td>
                        </tr>
                        <tr bgcolor="#F5F5FB" class="texto">
                            <td><b>Email</b></td>
                            <td><input type="text" name="novo_ug_repr_legal_email"
                                    value="<?php echo $objUsuarioGames->getReprLegalEmail() ?>" maxlength="100" size="50"
                                    class="texto"></td>
                            <td><b>Skype</b></td>
                            <td><input type="text" name="novo_ug_repr_legal_msn"
                                    value="<?php echo $objUsuarioGames->getReprLegalMSN() ?>" maxlength="100" size="50"
                                    class="texto"></td>
                        </tr>

                        <tr bgcolor="#FFFFFF" class="texto">
                            <td colspan="4" bgcolor="#ECE9D8">Sócios</font>
                            </td>
                        </tr>
                        <?php
                        $sql_socios = "SELECT * FROM dist_usuarios_games_socios WHERE ug_id = " . $objUsuarioGames->getId() . " order by ugs_percentagem DESC;";
                        $res_socios = SQLexecuteQuery($sql_socios);

                        $i_socios = 0;
                        if ($res_socios && pg_num_rows($res_socios) > 0) {

                            while ($res_row = pg_fetch_array($res_socios)) {
                                $novo_ug_nome_socios = $res_row['ugs_nome'];
                                $novo_ug_porcentagem_socios = $res_row['ugs_percentagem'];
                                $novo_ug_cpf_socios = $res_row['ugs_cpf'];
                                $novo_ug_data_nascimento_socios = $res_row['ugs_data_nascimento'];
                                $ug_id_socios = $res_row['ug_id'];
                                ?>

                                <tr bgcolor="#F5F5FB" class="texto">
                                    <td colspan="1"><b>Sócio <?php echo ($i_socios + 1); ?></b></td>
                                    <td colspan="3"><button type="button" remove-socio="<?php echo $i_socios ?>"
                                            class="rmSocio btn btn-danger btn-xs">Excluir</button></td>
                                </tr>
                                <tr bgcolor="#F5F5FB" class="texto">
                                    <td><b>Nome</b></td>
                                    <td><input type="text" name="novo_ug_nome_socios<?php echo "[" . $i_socios . "]" ?>"
                                            readonly="readonly" value="<?php echo $novo_ug_nome_socios ?>" maxlength="100" size="50"
                                            class="texto"></td>
                                    <td><b>Porcentagem na Empresa</b></td>
                                    <td><input type="text" name="novo_ug_porcentagem_socios<?php echo "[" . $i_socios . "]" ?>"
                                            value="<?php echo $novo_ug_porcentagem_socios ?>" maxlength="20" size="20"
                                            class="texto"></td>
                                </tr>
                                <tr bgcolor="#F5F5FB" class="texto">
                                    <td><b>CPF</b></td>
                                    <td><input type="text" class="class_cpf_socios"
                                            name="novo_ug_cpf_socios<?php echo "[" . $i_socios . "]" ?>" readonly="readonly"
                                            value="<?php echo $novo_ug_cpf_socios ?>" maxlength="20" size="20" class="texto"></td>
                                    <td><b>Data de Nascimento</b></td>
                                    <td><input type="text" name="novo_ug_data_nascimento_socios<?php echo "[" . $i_socios . "]" ?>"
                                            readonly="readonly"
                                            value="<?php echo formata_data($novo_ug_data_nascimento_socios, 0) ?>" maxlength="20"
                                            size="20" class="texto data data-nasc"></td>
                                </tr>
                                <?php
                                $i_socios++;
                            }
                        }
                        ?>
                        <tr bgcolor="#F5F5FB" class="texto">
                            <td colspan="1"><input type="hidden" name="num_registros_bd" id="num_registros_bd"
                                    value="<?php echo $i_socios ?>"></td>
                            <td colspan="3"><button type="button" data-socios="<?php echo $i_socios ?>"
                                    class="addSocio btn btn-success btn-xs pull-right">Adicionar sócio</button></td>
                        </tr>

                        <tr bgcolor="#FFFFFF" class="texto">
                            <td colspan="4" bgcolor="#ECE9D8">Principal Contato para assuntos relacionados à venda de
                                crédito digitáis e cartões pré-pagos para games online</font>
                            </td>
                        </tr>
                        <tr bgcolor="#F5F5FB" class="texto">
                            <td><b>Representante de Vendas</b></td>
                            <td colspan="3">
                                <select name="novo_ug_repr_venda_igual_repr_legal" class="texto">
                                    <option value="0" <?php if ($objUsuarioGames->getReprVendaIgualReprLegal() != "1")
                                        echo "selected"; ?>>Outra pessoa</option>
                                    <option value="1" <?php if ($objUsuarioGames->getReprVendaIgualReprLegal() == "1")
                                        echo "selected"; ?>>Representante legal</option>
                                </select>

                            </td>
                        </tr>
                        <?php if ($objUsuarioGames->getReprVendaIgualReprLegal() == "1") { ?>
                            <tr bgcolor="#F5F5FB" class="texto">
                                <td colspan="4">
                                    <font color="#FF0000">Representante Legal da Empresa também é o Principal Contato</font>
                                </td>
                            </tr>
                        <?php } else { ?>
                            <tr bgcolor="#F5F5FB" class="texto">
                                <td><b>Nome</b></td>
                                <td colspan="3"><input type="text" name="novo_ug_repr_venda_nome"
                                        value="<?php echo $objUsuarioGames->getReprVendaNome() ?>" maxlength="50" size="50"
                                        class="texto"></td>
                            </tr>
                            <tr bgcolor="#F5F5FB" class="texto">
                                <td><b>CPF</b></td>
                                <td><input type="text" name="novo_ug_repr_venda_cpf"
                                        value="<?php echo $objUsuarioGames->getReprVendaCPF() ?>" maxlength="20" size="20"
                                        class="texto"></td>
                                <td><b>RG</b></td>
                                <td><input type="text" name="novo_ug_repr_venda_rg"
                                        value="<?php echo $objUsuarioGames->getReprVendaRG() ?>" maxlength="20" size="20"
                                        class="texto"></td>
                            </tr>
                            <tr bgcolor="#F5F5FB" class="texto">
                                <td><b>Telefone</b></td>
                                <td>(<input type="text" name="novo_ug_repr_venda_tel_ddi"
                                        value="<?php echo $objUsuarioGames->getReprVendaTelDDI() ?>" maxlength="2" size="2"
                                        class="texto">) (<input type="text" name="novo_ug_repr_venda_tel_ddd"
                                        value="<?php echo $objUsuarioGames->getReprVendaTelDDD() ?>" maxlength="2" size="2"
                                        class="texto">) <input type="text" name="novo_ug_repr_venda_tel"
                                        value="<?php echo $objUsuarioGames->getReprVendaTel() ?>" maxlength="9" size="9"
                                        class="texto"></td>
                                <td><b>Celular</b></td>
                                <td>(<input type="text" name="novo_ug_repr_venda_cel_ddi"
                                        value="<?php echo $objUsuarioGames->getReprVendaCelDDI() ?>" maxlength="2" size="2"
                                        class="texto">) (<input type="text" name="novo_ug_repr_venda_cel_ddd"
                                        value="<?php echo $objUsuarioGames->getReprVendaCelDDD() ?>" maxlength="2" size="2"
                                        class="texto">) <input type="text" name="novo_ug_repr_venda_cel"
                                        value="<?php echo $objUsuarioGames->getReprVendaCel() ?>" maxlength="9" size="9"
                                        class="texto"></td>
                            </tr>
                            <tr bgcolor="#F5F5FB" class="texto">
                                <td><b>Email</b></td>
                                <td><input type="text" name="novo_ug_repr_venda_email"
                                        value="<?php echo $objUsuarioGames->getReprVendaEmail() ?>" maxlength="100" size="50"
                                        class="texto"></td>
                                <td><b>MSN</b></td>
                                <td><input type="text" name="novo_ug_repr_venda_msn"
                                        value="<?php echo $objUsuarioGames->getReprVendaMSN() ?>" maxlength="100" size="50"
                                        class="texto"></td>
                            </tr>
                        <?php } ?>

                        <tr bgcolor="#FFFFFF" class="texto">
                            <td colspan="4" bgcolor="#ECE9D8">Dados Bancários</font>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="4" width="100%">
                                <table border="0" cellspacing="0" cellpadding="0" width="100%">
                                    <tr bgcolor="#F5F5FB" class="texto">
                                        <td align="center"><b>#</b></td>
                                        <td align="center"><b>Banco</b></td>
                                        <td align="center"><b>Agência</b></td>
                                        <td align="center"><b>Conta</b></td>
                                        <td align="center"><b>Data Abertura</b></td>
                                    </tr>
                                    <tr bgcolor="#F5F5FB" class="texto">
                                        <td align="center">&nbsp;1&nbsp;</td>
                                        <td align="center"><input type="text" name="novo_ug_dados_bancarios_01_banco"
                                                value="<?php echo $objUsuarioGames->getDadosBancarios01Banco() ?>"
                                                maxlength="4" size="4" class="texto"></td>
                                        <td align="center"><input type="text" name="novo_ug_dados_bancarios_01_agencia"
                                                value="<?php echo $objUsuarioGames->getDadosBancarios01Agencia() ?>"
                                                maxlength="10" size="10" class="texto"></td>
                                        <td align="center"><input type="text" name="novo_ug_dados_bancarios_01_conta"
                                                value="<?php echo $objUsuarioGames->getDadosBancarios01Conta() ?>"
                                                maxlength="11" size="11" class="texto"></td>
                                        <td align="center"><input type="text" name="novo_ug_dados_bancarios_01_abertura"
                                                value="<?php echo $objUsuarioGames->getDadosBancarios01Abertura() ?>"
                                                maxlength="7" size="7" class="texto"></td>
                                    </tr>
                                    <tr bgcolor="#F5F5FB" class="texto">
                                        <td align="center">&nbsp;&nbsp;2&nbsp;</td>
                                        <td align="center"><input type="text" name="novo_ug_dados_bancarios_02_banco"
                                                value="<?php echo $objUsuarioGames->getDadosBancarios02Banco() ?>"
                                                maxlength="4" size="4" class="texto"></td>
                                        <td align="center"><input type="text" name="novo_ug_dados_bancarios_02_agencia"
                                                value="<?php echo $objUsuarioGames->getDadosBancarios02Agencia() ?>"
                                                maxlength="10" size="10" class="texto"></td>
                                        <td align="center"><input type="text" name="novo_ug_dados_bancarios_02_conta"
                                                value="<?php echo $objUsuarioGames->getDadosBancarios02Conta() ?>"
                                                maxlength="11" size="11" class="texto"></td>
                                        <td align="center"><input type="text" name="novo_ug_dados_bancarios_02_abertura"
                                                value="<?php echo $objUsuarioGames->getDadosBancarios02Abertura() ?>"
                                                maxlength="7" size="7" class="texto"></td>
                                    </tr>

                                </table>
                            </td>
                        </tr>

                        <tr bgcolor="#FFFFFF" class="texto">
                            <td colspan="4" bgcolor="#ECE9D8">Contato Técnico</font>
                            </td>
                        </tr>
                        <tr bgcolor="#F5F5FB" class="texto">
                            <td><b>Nome</b></td>
                            <td><input type="text" name="novo_ug_contato01_nome"
                                    value="<?php echo $objUsuarioGames->getContato01Nome() ?>" maxlength="20" size="20"
                                    class="texto"></td>
                            <td><b>Cargo</b></td>
                            <td><input type="text" name="novo_ug_contato01_cargo"
                                    value="<?php echo $objUsuarioGames->getContato01Cargo() ?>" maxlength="20" size="20"
                                    class="texto"></td>
                        </tr>
                        <tr bgcolor="#F5F5FB" class="texto">
                            <td><b>Telefone</b></td>
                            <td>(<input type="text" name="novo_ug_contato01_tel_ddi"
                                    value="<?php echo $objUsuarioGames->getContato01TelDDI() ?>" maxlength="2" size="2"
                                    class="texto">) (<input type="text" name="novo_ug_contato01_tel_ddd"
                                    value="<?php echo $objUsuarioGames->getContato01TelDDD() ?>" maxlength="2" size="2"
                                    class="texto">) <input type="text" name="novo_ug_contato01_tel"
                                    value="<?php echo $objUsuarioGames->getContato01Tel() ?>" maxlength="9" size="9"
                                    class="texto"></td>
                            <td colspan="2"></td>
                        </tr>
                    <?php } ?>

                    <?php if ($objUsuarioGames->getTipoCadastro() == "PF") { ?>
                        <tr bgcolor="#F5F5FB" class="texto">
                            <td><b>Nome</b></td>
                            <td><input type="text" name="novo_ug_nome" value="<?php echo $objUsuarioGames->getNome() ?>"
                                    maxlength="50" size="50" class="texto"></td>
                            <td><b>Substatus</b></td>
                            <td>
                                <select name="tf_u_substatus" class="form2">
                                    <?php
                                    //<option value="" <_?php  if($tf_u_substatus == "") echo "selected" ?_>>Selecione</option>
                                    if ($tf_u_substatus == "")
                                        $tf_u_substatus = "0";
                                    foreach ($SUBSTATUS_LH as $indice => $dado) {
                                        echo "<option value=\"" . $indice . "\"";
                                        if (strcmp($tf_u_substatus, $indice) == 0)
                                            echo "selected";
                                        echo " >" . $dado . " (" . $indice . ")</option>\n";
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr bgcolor="#F5F5FB" class="texto">
                            <td><b>CPF</b></td>
                            <td><input type="text" name="novo_ug_cpf" value="<?php echo $objUsuarioGames->getCPF() ?>"
                                    maxlength="250" size="20" class="texto"></td>
                            <td><b>RG</b></td>
                            <td><input type="text" name="novo_ug_rg" value="<?php echo $objUsuarioGames->getRG() ?>"
                                    maxlength="20" size="20" class="texto"></td>
                        </tr>
                        <tr bgcolor="#F5F5FB" class="texto">
                            <td><b>Data de Nascimento</b></td>
                            <td><input name="novo_ug_data_nascimento" type="text" class="form data-nasc"
                                    id="novo_ug_data_nascimento"
                                    value="<?php echo substr($objUsuarioGames->getDataNascimento(), 0, 10) ?>" size="9"
                                    maxlength="10">
                            </td>
                            <td><b>Sexo</b></td>
                            <td>
                                <select name="novo_ug_sexo" class="texto">
                                    <option value="">Selecione o Sexo</option>
                                    <option value="F" <?php if ($objUsuarioGames->getSexo() == "F")
                                        echo "selected"; ?>>
                                        Feminino</option>
                                    <option value="M" <?php if ($objUsuarioGames->getSexo() == "M")
                                        echo "selected"; ?>>
                                        Masculino</option>
                                </select>
                            </td>
                        </tr>
                        <tr bgcolor="#F5F5FB" class="texto">
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td><b>
                                    <nobr>Tipo de Venda</nobr>
                                </b></td>
                            <td>
                                <select name="novo_ug_tipo_venda" id="novo_ug_tipo_venda" class="form2">
                                    <option value="0" <?php echo ($objUsuarioGames->getTipoVenda() == '') ? 'selected="selected" ' : ''; ?>>-- Selecione --</option>
                                    <option value="1" <?php echo ($objUsuarioGames->getTipoVenda() == '1') ? 'selected="selected" ' : ''; ?>>Online</option>
                                    <option value="2" <?php echo ($objUsuarioGames->getTipoVenda() == '2') ? 'selected="selected" ' : ''; ?>>Offline</option>
                                    <option value="3" <?php echo ($objUsuarioGames->getTipoVenda() == '3') ? 'selected="selected" ' : ''; ?>>Offline e Online</option>
                                </select>
                            </td>
                        </tr>


                    <?php } ?>
                    <tr bgcolor="#FFFFFF" class="texto">
                        <td colspan="4" bgcolor="#ECE9D8">Endereço</font>
                        </td>
                    </tr>
                    <tr bgcolor="#F5F5FB" class="texto">
                        <td><b>CEP</b></td>
                        <td><input type="text" id="novo_ug_cep" name="novo_ug_cep"
                                value="<?php echo $objUsuarioGames->getCEP() ?>" maxlength="8" size="8"
                                class="texto">&nbsp;<div id="info_cep"></div>
                        </td>
                        <td><b>Tipo de Endereço</b></td>
                        <td><input type="text" id="novo_ug_tipo_end" name="novo_ug_tipo_end"
                                value="<?php echo $objUsuarioGames->getTipoEnd() ?>" maxlength="30" size="15"
                                class="texto"></td>
                    </tr>
                    <tr bgcolor="#F5F5FB" class="texto">
                        <td><b>Endereço</b></td>
                        <td colspan="3"><input type="text" id="novo_ug_endereco" name="novo_ug_endereco"
                                value="<?php echo $objUsuarioGames->getEndereco() ?>" maxlength="100" size="50"
                                class="texto"></td>
                    </tr>
                    <tr bgcolor="#F5F5FB" class="texto">
                        <td><b>Número</b></td>
                        <td><input type="text" id="novo_ug_numero" name="novo_ug_numero"
                                value="<?php echo $objUsuarioGames->getNumero() ?>" maxlength="10" size="10"
                                class="texto"></td>
                        <td><b>Complemento</b></td>
                        <td><input type="text" id="novo_ug_complemento" name="novo_ug_complemento"
                                value="<?php echo $objUsuarioGames->getComplemento() ?>" maxlength="100" size="50"
                                class="texto"></td>
                    </tr>
                    <tr bgcolor="#F5F5FB" class="texto">
                        <td><b>Bairro</b></td>
                        <td><input type="text" id="novo_ug_bairro" name="novo_ug_bairro"
                                value="<?php echo $objUsuarioGames->getBairro() ?>" maxlength="100" size="50"
                                class="texto"></td>
                        <td><b>Cidade</b></td>
                        <td><input type="text" id="novo_ug_cidade" name="novo_ug_cidade"
                                value="<?php echo $objUsuarioGames->getCidade() ?>" maxlength="100" size="50"
                                class="texto"></td>
                    </tr>
                    <tr bgcolor="#F5F5FB" class="texto">
                        <td><b>Estado</b></td>
                        <td><input type="text" id="novo_ug_estado" name="novo_ug_estado"
                                value="<?php echo $objUsuarioGames->getEstado() ?>" maxlength="2" size="2"
                                class="texto"></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr bgcolor="#F5F5FB" class="texto">
                        <td><b>Telefone</b></td>
                        <td>(<input type="text" name="novo_ug_tel_ddi"
                                value="<?php echo $objUsuarioGames->getTelDDI() ?>" maxlength="2" size="2"
                                class="texto">) (<input type="text" name="novo_ug_tel_ddd"
                                value="<?php echo $objUsuarioGames->getTelDDD() ?>" maxlength="2" size="2"
                                class="texto">) <input type="text" name="novo_ug_tel"
                                value="<?php echo $objUsuarioGames->getTel() ?>" maxlength="9" size="9" class="texto">
                        </td>
                        <td><b>Celular</b></td>
                        <td>(<input type="text" name="novo_ug_cel_ddi"
                                value="<?php echo $objUsuarioGames->getCelDDI() ?>" maxlength="2" size="2"
                                class="texto">) (<input type="text" name="novo_ug_cel_ddd"
                                value="<?php echo $objUsuarioGames->getCelDDD() ?>" maxlength="2" size="2"
                                class="texto">) <input type="text" name="novo_ug_cel"
                                value="<?php echo $objUsuarioGames->getCel() ?>" maxlength="9" size="9" class="texto">
                        </td>
                    </tr>
                    <tr bgcolor="#F5F5FB" class="texto">
                        <td><b>Fax</b></td>
                        <td>(<input type="text" name="novo_ug_fax_ddi"
                                value="<?php echo $objUsuarioGames->getFaxDDI() ?>" maxlength="2" size="2"
                                class="texto">) (<input type="text" name="novo_ug_fax_ddd"
                                value="<?php echo $objUsuarioGames->getFaxDDD() ?>" maxlength="2" size="2"
                                class="texto">) <input type="text" name="novo_ug_fax"
                                value="<?php echo $objUsuarioGames->getFax() ?>" maxlength="9" size="9" class="texto">
                        </td>
                        <td colspan="2"></td>
                    </tr>

                    <!-- [NEXCAFE] -->
                    <tr bgcolor="#FFFFFF" class="texto">
                        <td colspan="4" bgcolor="#ECE9D8">NexCafé</font>
                        </td>
                    </tr>
                    <tr bgcolor="#F5F5FB" class="texto">
                        <td><b>Login NexCafé Plus+</b></td>
                        <td>
                            <input type="text" name="tf_u_login_nexcafe"
                                value="<?php echo $objUsuarioGames->getUgIdNexCafe(); ?>" maxlength="100" size="40"
                                class="texto">
                        </td>
                        <td colspan="2">
                            <input type="checkbox" name="tf_u_login_automatico_nexcafe"
                                id="tf_u_login_automatico_nexcafe" value="1" <?php if ($objUsuarioGames->getUgLoginNexCafeAuto()) {
                                    echo 'checked="checked"';
                                } ?>
                                style="float: left; display: block; text-align: left; width: auto; margin-top:0px;" />
                            <label
                                style="float: left; display: block; text-align: left; width: auto; margin-left:4px;">Habilitar
                                login autom&aacute;tico para venda de PINs via NexCaf&eacute;?</label>
                        </td>
                    </tr>
                    <tr bgcolor="#F5F5FB" class="texto">
                        <td colspan="4"><b>Data Adesão ao
                                NexCafé</b>&nbsp;&nbsp;&nbsp;<?php echo (($objUsuarioGames->getUgDataInclusaoNexCafe()) ? formata_data_ts($objUsuarioGames->getUgDataInclusaoNexCafe() . "", 0, true, true) : "-"); ?>
                        </td>
                    </tr>
                    <tr bgcolor="#F5F5FB" class="texto">
                        <td colspan="4">&nbsp;</td>
                    </tr>
                    <!-- [/NEXCAFE] -->

                    <tr bgcolor="#FFFFFF" class="texto">
                        <td colspan="4" bgcolor="#ECE9D8">Observaçôes</font>
                        </td>
                    </tr>
                    <tr bgcolor="#F5F5FB" class="texto">
                        <td><b>Observações</b> (2000 chars.)</td>
                        <td colspan="3">
                            <?php echo str_replace("\n", "<br>", $objUsuarioGames->getObservacoes()) ?>
                            <textarea name="novo_ug_observacoes" rows="3" cols="100" class="texto"></textarea>
                        </td>
                    </tr>

                    <tr bgcolor="#FFFFFF" class="texto">
                        <td colspan="4" bgcolor="#ECE9D8">Gestão de Risco</font>
                        </td>
                    </tr>
                    <tr bgcolor="#F5F5FB" class="texto">
                        <td><b>Classificação</b></td>
                        <td colspan="3">
                            <select name="novo_ug_risco_classif" class="texto">
                                <option value="">Selecione a Classificação</option>
                                <?php foreach ($GLOBALS['RISCO_CLASSIFICACAO_NOMES'] as $formaPagtoId => $formaPagtoDesc) { ?>
                                    <option value="<?php echo $formaPagtoId; ?>" <?php if ($objUsuarioGames->getRiscoClassif() == $formaPagtoId)
                                           echo "selected"; ?>>
                                        <?php echo $formaPagtoDesc; ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>

                </table>


            </td>
        </tr>
    </table>
</form>
<?php
require_once $raiz_do_projeto . "backoffice/includes/rodape_bko.php";
?>

<style>
    html {
        overflow-x: scroll !important;
    }
</style>

</html>