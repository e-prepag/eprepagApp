<?php
set_time_limit(120);

require_once RAIZ_DO_PROJETO . "consulta_cpf/config.inc.cpf.php";
require_once DIR_WEB . "includes/functions.php";
require_once "/www/consulta_cpf/trocaAutomatica.php";

$errors = array();

if (isset($_POST['formsubmit'])) {
    if (isset($_REQUEST['skip'])) {
        $GLOBALS['_SESSION']['skip'] = true;
        header('Location: ' . $GLOBALS['_SERVER']['PHP_SELF']);
    }

    if (!verificaCPF_int($_POST['cpf'])) {
        $errors[] = "CPF inválido, por favor revise o número digitado.";
    } else {

        //    ob_clean();
        $_POST['cpf'] = preg_replace('/[^0-9]/', '', $_POST['cpf']);

        $contagemErroDia = verificaContagem();
        if ($contagemErroDia["contagem"] != false && $contagemErroDia["contagem"] >= 5) {
            trocaOrigemAutomatica(3);
        }

        //Novo modelo de Consulta
        $rs_api = new classCPF();
        $resposta = null;
        $parametros = array(
            'cpfcnpj' => $_POST['cpf'],
            'data_nascimento' => (!empty($_POST['data_nascimento']) ? $_POST['data_nascimento'] : null)
        );

        //testando se consulta automatica despresar qtde de contas e quantidade de compras
        if ($_POST['consulta_automatica'] == '1') {
            $rs_api->set_quantidade_contas($rs_api->consultaQuantidadeContas($parametros) + 1);
            $rs_api->set_quantidade_limite($rs_api->consultaQuantidadeUtilizada($parametros) + 1);
        }//end if($_REQUEST['consulta_automatica'] == '1')

        $testeCPF = $rs_api->Req_EfetuaConsulta($parametros, $resposta);

        //Verificação de idade mínima 
        if ($testeCPF == 112) {
            $errors[] = "O produto " . $GLOBALS["produto_idade_minima"] . " é destinado para maiores de " . $GLOBALS["IDADE_MINIMA"] . " anos. Esta compra só poderá ser concluída caso você informe o CPF e data de nascimento dos seus pais ou responsável.";
        }

        //Testando se o CPF consta na BlackList
        elseif ($testeCPF == 299) {
            $errors[] = "Existem pendências de documentos relacionadas ao seu CPF. Por gentileza entre em contato com suporte@e-prepag.com.br para desbloqueio.<br> Como empresa de serviços financeiros, a E-prepag trabalha para manter um ambiente seguro para todos, e conta com a sua colaboração.";
        }

        //Testando se ultrapassou o limite de utilização do mesmo CPF
        elseif ($testeCPF != 171) {

            if (CPF_PARTNER_ENVIRONMET == CPF_PARTNER_CREDIFY) {

                if ($testeCPF == 2) {
                    $errors[] = "Este número de CPF parece não constar na Receita Federal. Por favor, verifique o número digitado e tente novamente.";
                } elseif ($testeCPF == 1) {
                    $errors[] = "Atualização de sistema em andamento. Alguns serviços podem estar indisponíveis. Estamos trabalhando para normalizar tudo o mais rápido possível. Qualquer dúvida, nossa equipe de suporte está à disposição. (erro 9191)";
                    qtdeTrocaAutomatica();
                } elseif (is_null($testeCPF)) {
                    $errors[] = "Erro no sistema (0034). Por favor, entre em contato com suporte@e-prepag.com.br reportando o código do problema. Obrigado.";
                    qtdeTrocaAutomatica();
                } elseif ($testeCPF == 0 && $resposta['resposta']['cpf']['situacao'] != CPF_SITUCAO_REGULAR) {
                    $errors[] = "CPF não está regular junto a Receita Federal. Por favor, verifique o número digitado e tente novamente.";
                } elseif (!isset($resposta['resposta']['cpf']['nome'])) {
                    $errors[] = "Este número de CPF parece não constar na Receita Federal. Por favor, verifique o número digitado e tente novamente.";
                } elseif ($testeCPF == 0 && $resposta['resposta']['cpf']['situacao'] == CPF_SITUCAO_REGULAR) {
                    $name = $resposta['resposta']['cpf']['nome'];
                } else {
                    $errors[] = "Erro no sistema (0407). Por favor, entre em contato com suporte@e-prepag.com.br reportando o código do problema. Obrigado.";
                    qtdeTrocaAutomatica();
                }

            } // end if (CPF_PARTNER_ENVIRONMET == CPF_PARTNER_CREDIFY)
            elseif (CPF_PARTNER_ENVIRONMET == CPF_CONSULTA_HUB) {

                $file = fopen("/www/log/retorno_cpf.txt", "a+");
                fwrite($file, "hud do desenvolvedor \n");
                fwrite($file, "resultado code " . $testeCPF . "\n");
                fwrite($file, "resultado json " . json_encode($resposta) . "\n");
                fwrite($file, "retorno json " . json_encode($retorno) . "\n");
                fwrite($file, str_repeat("*", 50));
                fclose($file);

                if ($testeCPF == 2) {
                    $errors[] = "Este número de CPF parece não constar na Receita Federal. Por favor, verifique o número digitado e tente novamente.";
                } elseif ($testeCPF == 1) {
                    $errors[] = "Atualização de sistema em andamento. Alguns serviços podem estar indisponíveis. Estamos trabalhando para normalizar tudo o mais rápido possível. Qualquer dúvida, nossa equipe de suporte está à disposição. (erro 9191)";
                    qtdeTrocaAutomatica();
                } elseif (is_null($testeCPF)) {
                    $errors[] = "Erro no sistema (0034). Por favor, entre em contato com suporte@e-prepag.com.br reportando o código do problema. Obrigado.";
                    qtdeTrocaAutomatica();
                } elseif ($testeCPF == 0 && $resposta['result']['situacao_cadastral'] != CPF_SITUCAO_REGULAR) {
                    $errors[] = "CPF não está regular junto a Receita Federal. Por favor, verifique o número digitado e tente novamente.";
                } elseif (!isset($resposta['result']['nome_da_pf'])) {
                    $errors[] = "Este número de CPF parece não constar na Receita Federal. Por favor, verifique o número digitado e tente novamente.";
                } elseif ($testeCPF == 0 && $resposta['result']['situacao_cadastral'] == CPF_SITUCAO_REGULAR) {
                    $retorno["nome"] = $resposta['result']['nome_da_pf'];
                    $retorno["data_nascimento"] = $resposta['result']['data_nascimento'];
                    $name = $retorno["nome"];
                    $data_nascimento = $retorno["data_nascimento"];
                } else {
                    $errors[] = "Erro no sistema (0407). Por favor, entre em contato com suporte@e-prepag.com.br reportando o código do problema. Obrigado.";
                    qtdeTrocaAutomatica();
                }

            } elseif (CPF_PARTNER_ENVIRONMET == CPF_CONSULTA_CACHE) {

                if ($testeCPF == 2) {
                    $errors[] = "Estamos momentaneamente com falha na comunição para verificação do CPF informado. Por favor, aguarde alguns minutos e tente novamente.";
                } elseif ($testeCPF == 1) {
                    $name = $resposta['pesquisas']['camposResposta']['nome'];
                    $data_nascimento = $resposta['pesquisas']['camposResposta']['data_nascimento'];
                } else {
                    $errors[] = "Erro no sistema [" . $resposta['pesquisas']['msg'] . "] (0485). Por favor, entre em contato com suporte@e-prepag.com.br reportando o código do problema. Obrigado.";
                }

            } //end  elseif (CPF_PARTNER_ENVIRONMET == CPF_CONSULTA_CACHE) 
            else {

                if ($testeCPF == 2 || $testeCPF == 8) {
                    $errors[] = "Este número de CPF parece não constar na Receita Federal. Por favor, verifique o número digitado e tente novamente.";
                } elseif ($testeCPF == 1) {
                    $errors[] = "Atualização de sistema em andamento. Alguns serviços podem estar indisponíveis. Estamos trabalhando para normalizar tudo o mais rápido possível. Qualquer dúvida, nossa equipe de suporte está à disposição. (erro 9191)";
                    qtdeTrocaAutomatica();
                } elseif ($testeCPF == 9) {
                    $errors[] = "Não foi possível realizar consulta. Erro(9355). Por favor, tente novamente. Se o problema persistir entre em contato com suporte@e-prepag.com.br reportando o código do problema. Obrigado.";
                    qtdeTrocaAutomatica();
                } elseif ($testeCPF == 12) {
                    $errors[] = "A Data de Nascimento informada é diferente do que consta nos dados da Receita. Por favor, insira a data de nascimento do CPF informado.";
                } elseif (is_null($testeCPF)) {
                    $errors[] = "Erro no sistema (0034). Por favor, entre em contato com suporte@e-prepag.com.br reportando o código do problema. Obrigado.";
                    qtdeTrocaAutomatica();
                } elseif ($testeCPF == 3 && $resposta['pesquisas']['camposResposta']['situacao'] != CPF_SITUCAO_REGULAR) {
                    $errors[] = "CPF não está regular junto a Receita Federal. Por favor, verifique o número digitado e tente novamente.";
                } elseif (!isset($resposta['pesquisas']['camposResposta']['nome'])) {
                    $errors[] = "Este número de CPF parece não constar na Receita Federal. Por favor, verifique o número digitado e tente novamente.";
                } elseif ($testeCPF == 3 && $resposta['pesquisas']['camposResposta']['situacao'] == CPF_SITUCAO_REGULAR) {
                    $name = $resposta['pesquisas']['camposResposta']['nome'];
                    $data_nascimento = $resposta['pesquisas']['camposResposta']['data_nascimento'];
                } else {
                    $errors[] = "Erro no sistema [" . $resposta['pesquisas']['msg'] . "] (0407). Por favor, entre em contato com suporte@e-prepag.com.br reportando o código do problema. Obrigado.";
                    qtdeTrocaAutomatica();
                }


            }//end elseif (CPF_PARTNER_ENVIRONMET == CPF_PARTNER_OMNIDATA)   

        }//end elseif ($testeCPF != 171)

        // Atingiu o limite máximo de utilização do mesmo CPF
        else {

            $errors[] = "Para utilizar seu CPF precisamos confirmar alguns dados pessoais. Por favor entre em contato com a E-Prepag.<br><span onclick=\'window.open(\"https://www.e-prepag.com/support\");\' style=\'cursor:pointer; color:#2e5984;\'>https://www.e-prepag.com/support</span>.";

        }//end else do elseif ($testeCPF != 171)

        if (count($errors) == 0 && !empty($usuarioId)) {

            // Vamos certificar que extraimos apenas os numeros do CPF, para depois aplicarmos a mascara
            $matches = array();
            preg_match_all('!\d+!', $_POST['cpf'], $matches);

            $cpf = implode('', $matches[0]);

            $sql = "UPDATE usuarios_games SET ug_cpf='" . mask($cpf, '###.###.###-##') . "', ug_nome='" . fix_name($name) . "', ug_nome_cpf='" . fix_name($name) . "', ug_data_cpf_informado=NOW(), ug_data_nascimento = to_date('" . $data_nascimento . "','DD/MM/YYYY')  WHERE ug_id=" . $usuarioId . ";";
            $res = SQLexecuteQuery($sql);
            if ($res) {
                (new UsuarioGames)->adicionarLoginSession_ByID($usuarioId);
            } else {
                $errors[] = "Problema ao atualizar os dados.<br>Por favor entre com nosso suporte. Obrigado!";
            }


            //Atualizando no Qtde de Contas com o mesmo CPF
            $rs_api->adicionaQtdeContas($cpf, fix_name($name), $data_nascimento);

            header('Location: ' . $GLOBALS['_SERVER']['PHP_SELF']);
        } elseif (empty($usuarioId)) {
            $errors[] = "Sua sessão expirou. Por favor, faça login no sistema novamente. Obrigado!";
        }

    }//end else 
    if (count($errors) > 0 && $_POST['consulta_automatica'] == '1') {
        $msg = "Houve um problema na atualização de seus dados.<br>Por favor, tente mais tarde ou se o problema persistir entre em contato com o suporte da E-Prepag reportando.<br>";
        $msg .= "Problemas encontrados:<br>";
        foreach ($errors as $error) {
            $msg .= $error . "<br>";
        }/*
             $sql = "UPDATE usuarios_games SET ug_data_cpf_informado=NOW() WHERE ug_id=".$usuarioId.";";
             //echo $sql;
             $res = SQLexecuteQuery($sql);
             if($testeCPF != 171) {
                 $msg = "Não houve sucesso na atualização do CPF do usuário de ID[".$usuarioId."]<br>Porém foi permitido efetuar a compra e foi atualizado a data de consulta do seu CPF para ter sucesso.<br>Dados:<br>CPF: ".$_POST['cpf']."<br>Data de Nascimento: ".$_POST['data_nascimento']."<br>";
                 foreach($errors as $key => $error){ 
                     $msg .= str_replace("\n","<br>",  $error); 
                 }
                 enviaEmail("rc@e-prepag.com.br", "tamy@e-prepag.com.br", "wagner@e-prepag.com.br", "Erro na atualização de CPF já informado", $msg);
             }//end if($testeCPF != 171)
             UsuarioGames::adicionarLoginSession_ByID($usuarioId);
             header('Location: ' . $GLOBALS['_SERVER']['PHP_SELF']);      
             die();
             */

    }//end if(count($errors) > 0 && $_POST['consulta_automatica'] == '1')

}

$form_name = isset($_POST['name']) ? $_POST['name'] : $usuarioGames->ug_sNome;
$form_cpf = isset($_POST['cpf']) ? $_POST['cpf'] : $usuarioGames->ug_sCPF;
$form_data_nascimento = isset($_POST['data_nascimento']) ? $_POST['data_nascimento'] : $usuarioGames->ug_dDataNascimento;

if (!isset($_POST['formsubmit'])) {
    $form_name = "";
    $form_cpf = "";
    $form_data_nascimento = "";
}

$server_url = $_SERVER['SERVER_NAME'];


$retorno = "<div id='popup_cpf' align='left' title=''>
                            <script type='text/javascript'>
                                function Trim(str){
                                    return str.replace(/^\\s+|\\s+$/g,'');
                                }
                                function validaform() {
                      			var strDtNasc = document.frmPreCadastro.data_nascimento.value;
                                        if(strDtNasc.length == '10'){
                                            var dtNasc = strDtNasc.split('/');
                                            var objDtNasc = new Date(parseInt(dtNasc[2]),parseInt(dtNasc[1])-1,parseInt(dtNasc[0]));
                                            if(objDtNasc.getTime() > currentDate.getTime()){
                                                document.frmPreCadastro.data_nascimento.focus();
                                                document.frmPreCadastro.data_nascimento.select();
                                                return false;
                                            }
                                        }
                      			if (document.frmPreCadastro.ug_cpf.value == '') {
                                                alert('Informe o CPF');
                                                document.frmPreCadastro.ug_cpf.focus();
                                                document.frmPreCadastro.ug_cpf.select();
                                                return false;
                                        }
                                        else if(!validaRespostaCPF(document.frmPreCadastro.ug_cpf.value)) {
                                                alert('CPF inválido, por favor revise o número digitado.');
                                                document.frmPreCadastro.ug_cpf.focus();
                                                document.frmPreCadastro.ug_cpf.select();
                                                return false;
                                        }
                                        else return true;
                                }//end function validaform()

                                function validaRespostaCPF(cpf) {
                                    cpf = cpf.replace(/[^\d]+/g,'');
                                    if(cpf == '') return false;

                                    // Elimina CPFs invalidos conhecidos
                                    if (cpf.length != 11 ||
                                            cpf == '00000000000' ||
                                            cpf == '11111111111' ||
                                            cpf == '22222222222' ||
                                            cpf == '33333333333' ||
                                            cpf == '44444444444' ||
                                            cpf == '55555555555' ||
                                            cpf == '66666666666' ||
                                            cpf == '77777777777' ||
                                            cpf == '88888888888' ||
                                            cpf == '99999999999')
                                            return false;

                                    // Valida 1o digito
                                    add = 0;
                                    for (i=0; i < 9; i ++)
                                            add += parseInt(cpf.charAt(i)) * (10 - i);
                                    rev = 11 - (add % 11);
                                    if (rev == 10 || rev == 11)
                                            rev = 0;
                                    if (rev != parseInt(cpf.charAt(9)))
                                            return false;

                                    // Valida 2o digito
                                    add = 0;
                                    for (i = 0; i < 10; i ++)
                                            add += parseInt(cpf.charAt(i)) * (11 - i);
                                    rev = 11 - (add % 11);
                                    if (rev == 10 || rev == 11)
                                            rev = 0;
                                    if (rev != parseInt(cpf.charAt(10)))
                                            return false;

                                    return true;
                              }//end validaRespostaCPF()
                             </script>
                        </div>
                ";
?>
<!DOCTYPE html>

<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <!--<link href="/eprepag/incs/styles.css" rel="stylesheet" type="text/css" />-->
    <script type="text/javascript" src="/js/scripts.js"></script>
    <link href="/includes/bootstrap/css/bootstrap.min_new.css" rel="stylesheet" type="text/css" />
    <link href="/css/creditos.css" rel="stylesheet" type="text/css" />
    <link href="/css/game.css" rel="stylesheet" type="text/css" />
    <!-- includes js -->
    <script type="text/javascript" src="/js/jquery.js"></script>
    <script type="text/javascript" src="/includes/bootstrap/js/bootstrap.min.js"></script>
    <link href="/js/jqueryui/css/custom-theme/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
    <script src="/js/jqueryui/js/jquery-ui-1.9.2.custom.min.js"></script>
    <script type="text/javascript" src="/js/modalwaitingfor.js"></script>
    <script type="text/javascript" src="/js/global.js"></script>
    <?php
    $GLOBALS["jquery"] = true;
    $url = 'http' . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "s" : "") . '://' . $_SERVER['SERVER_NAME'];
    echo '<script src="/js/jquery.mask.min.js"></script>';
    ?>
</head>

<body>
    <?php
    $GLOBALS["jquery"] = true;
    echo integracao_layout('css');
    echo modal_includes();
    if (count($errors) > 0 && $_POST['consulta_automatica'] == '1') {
        echo "<script>$(function(){ showMessage('" . $msg . "'); });</script>";
        die();
    }//end if(count($errors) > 0 && $_REQUEST['consulta_automatica'] == '1')
    ?>
    <div class="wrapper txt-preto int-box">
        <div class="col-md-12 col-lg-12 col-sm-12 col-xs-12">
            <h4 class="c1 txt-azul">Por favor, complete o campo abaixo com o seu CPF <a href="#"
                    class="btn-question glyphicon glyphicon-question-sign txt-vermelho c-pointer t0"
                    data-msg="<h2>O que é isso?</h2>Agora todas as transações financeiras de jogos online no Brasil são condicionadas ao fornecimento de um CPF. Esta informação será solicitada em algumas compras, mas não sempre. Agradecemos a sua compreensão."
                    style="position: relative;"></a></h4>
            <p><i>O CPF será solicitado apenas na sua primeira compra no jogo.</i></p>

            <div class="int-form1" style="position: relative;">
                <form action="" id="cpfForm" method="POST">
                    <input type="hidden" name="formsubmit" value="OK" style="display: none;" />
                    <div class="col-md-5 bottom20">
                        <div class="form-group">
                            <!--<label for="cpf">Cpf:</label>-->
                            <input type="text" class="form-control w160" id="cpf" name="cpf" maxlength="14"
                                value="<?php echo $form_cpf; ?>" placeholder="CPF">
                        </div>
                        <div class="form-group bottom0">
                            <!--<label for="cpf">Data de Nascimento (<i>(DD/MM/AAAA)</i>):</label>-->
                            <input type="text" class="form-control datepicker w160"
                                value="<?php echo $form_data_nascimento; ?>" placeholder="Data de Nascimento"
                                name="data_nascimento" id="data_nascimento">
                        </div>
                        <span
                            style="font-style: italic; color: #444; float: left; font-size: 12px; margin-top: 0px;">(DD/MM/AAAA)</span><br>
                        <div class="form-group">
                            <input type="button" class="int-btn1 grad1 btn btn-sm btn-success pull-left" id="btn_submit"
                                value="Confirmar" />
                        </div>
                    </div>
                    <?php
                    echo $retorno;
                    ?>
                </form>

                <?php foreach ($errors as $key => $error) { ?>
                    <script>$(function () { showMessage('<?php echo str_replace("\n", " ", $error); ?>'); });</script>
                    <?php break; ?>
                <?php } ?>
            </div>
        </div>
    </div>
    </div>
    <script>
        $('div#captcha_img, div#captcha_img + a').wrapAll('<div id="captcha-wrapper">');

        $(document).ready(function () {
            //jQuery(function(e){e.datepicker.regional["pt-BR"]={closeText:"Fechar",prevText:"&#x3C;Anterior",nextText:"Próximo&#x3E;",currentText:"Hoje",monthNames:["Janeiro","Fevereiro","Março","Abril","Maio","Junho","Julho","Agosto","Setembro","Outubro","Novembro","Dezembro"],monthNamesShort:["Jan","Fev","Mar","Abr","Mai","Jun","Jul","Ago","Set","Out","Nov","Dez"],dayNames:["Domingo","Segunda-feira","Terça-feira","Quarta-feira","Quinta-feira","Sexta-feira","Sábado"],dayNamesShort:["Dom","Seg","Ter","Qua","Qui","Sex","Sáb"],dayNamesMin:["Dom","Seg","Ter","Qua","Qui","Sex","Sáb"],weekHeader:"Sm",dateFormat:"dd/mm/yy",firstDay:0,isRTL:!1,showMonthAfterYear:!1,yearSuffix:""},e.datepicker.setDefaults(e.datepicker.regional["pt-BR"])});
            var currentDate = new Date();

            $("#data_nascimento").mask("99/99/9999");
            $("#cpf").mask("999.999.999-99");
            $("#data_nascimento").blur(function () {
                if ($(this).val().length == "10") {
                    var dt_nasc = $(this).val().split("/");
                    var objDtNasc = new Date(parseInt(dt_nasc[2]), parseInt(dt_nasc[1]) - 1, parseInt(dt_nasc[0]));
                    if (objDtNasc.getTime() > currentDate.getTime()) {
                        $(this).val("");
                        showMessage("Data inválida");
                    }
                }
            });

            $("#data_nascimento").change(function () {
                if ($(this).val().length == "10") {
                    var dt_nasc = $(this).val().split("/");
                    var objDtNasc = new Date(parseInt(dt_nasc[2]), parseInt(dt_nasc[1]) - 1, parseInt(dt_nasc[0]));
                    if (objDtNasc.getTime() > currentDate.getTime()) {
                        $(this).val("");
                        showMessage("Data inválida");
                    }
                }
            });

            //    $("#data_nascimento").datepicker({
            //        maxDate: currentDate
            //    });
        });

    </script>

</body>

</html>
<?php
//Restaurando Sessão por conta do (1REW)
$GLOBALS['_SESSION']['pagamento.pagto.deposito.em.saldo'] = $aux['pagamento.pagto.deposito.em.saldo'];
$GLOBALS['_SESSION']['pagamento.pagto.deposito.em.saldo.num.docto'] = $aux['pagamento.pagto.deposito.em.saldo.num.docto'];
?>