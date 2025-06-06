<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
require_once "../../includes/constantes.php";
require_once DIR_CLASS . "pdv/controller/MeuCadastroController.class.php";
header("Content-Type: text/html; charset=ISO-8859-1", true);
$controller = new MeuCadastroController;

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
        return "Erro: URL inválida.";
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
        'cor_primaria' => $corPrimaria,
        'cor_secundaria' => $corSecundaria,
        'email_suporte' => $emailSuporte,
        'link_canal' => $linkCanal,
        'mensagem' => $mensagem,
        'logo_extensao' => $ext
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

if (isset($_POST['telefone_contato'])) {
    $retorno = array();

    $con = ConnectionPDO::getConnection();
    $pdo = $con->getLink();

    $error_msg = salva_estilos($controller->usuarios->getId(), $pdo);

    if ($error_msg) {
        $retorno[] = $error_msg;
        $cor = "txt-vermelho";
    } else {

        if (!$controller->atualizaCadastro($_POST)) {
            $retorno[] = $controller->erros;
            $cor = "txt-vermelho";
        } else {
            $cor = "txt-verde";
            $retorno[] = "Informações alteradas com sucesso";

        }
    }
}

//Conectando com PDO para execução da QUERY
$con = ConnectionPDO::getConnection();
$pdo = $con->getLink();

$dadosEstiloLink = getEstilosUsuarioPDO($controller->usuarios->getId(), $pdo);
if (!filter_var($dadosEstiloLink['email_suporte'], FILTER_VALIDATE_EMAIL)) {
    $dadosEstiloLink['email_suporte'] = "";
}
if (!filter_var($dadosEstiloLink['link_canal'], FILTER_VALIDATE_URL)) {
    $dadosEstiloLink['link_canal'] = "";
}

$sql_socios = "SELECT * FROM dist_usuarios_games_socios WHERE ug_id = :ug_id order by ugs_percentagem DESC";

$stmt = $pdo->prepare($sql_socios);
$user_id = $controller->usuarios->getId();
$stmt->bindParam(':ug_id', $user_id, PDO::PARAM_INT);

$stmt->execute();
$socios = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT ug_chave_autenticador FROM dist_usuarios_games WHERE ug_id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$auth = $stmt->fetch(PDO::FETCH_ASSOC);
$possuiAuth = empty($auth['ug_chave_autenticador']) ? false : true;

$estabelecimentos = $controller->getEstabelecimentos();

$banner = $controller->getBanner();

require_once "includes/header.php";
?>
<div id="modal-edicao" class="modal fade" role="dialog">
    <div class="modal-dialog" role="document">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h5 class="modal-title txt-azul-claro"><strong>Atenção!</strong></h5>
            </div>
            <div class="modal-body txt-verde">
                <p class="txt-cinza">Após inserir os novos dados, clique em "Salvar" no final da página.</p>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
<div class="container bg-branco">
    <div class="row">
        <div class="col-md-10 txt-preto">
            <form method="post" id="formCad" name="formCad" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-12 col-lg-12 col-sm-12 col-xs-12 espacamento txt-azul-claro">
                        <strong>Meu Cadastro</strong>
                    </div>
                </div>
                <?php
                if (isset($retorno)) {
                    ?>
                    <div class="row">
                        <div class="col-md-12 col-lg-12 col-sm-12 col-xs-12 espacamento <?php echo $cor; ?>">
                            <?php
                            foreach ($retorno as $val) {
                                echo "<p><strong>" . $val . "</strong></p>";
                            }
                            ?>

                        </div>
                    </div>
                    <?php
                }
                ?>

                <div class="row">
                    <div class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right">Login: </div>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9 "><?php echo $controller->usuarios->getLogin(); ?>
                    </div>
                </div>
                <div class="row top5">
                    <div class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right">E-mail: </div>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9"><?php echo $controller->usuarios->getEmail(); ?>
                    </div>
                </div>
                <div class="row top5">
                    <div class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right">Senha: </div>
                    <div class="col-md-3 col-lg-9 col-sm-9 col-xs-9">******** <a class="txt-azul-claro"
                            href="/creditos/alterar_senha.php">alterar</a></div>
                </div>
                <div class="row top5">
                    <div class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right">Autenticador: </div>
                    <div class="col-md-3 col-lg-9 col-sm-9 col-xs-9"><?php echo $possuiAuth ? "Possui" : "Não possui" ?>
                        <a class="txt-azul-claro <?php echo $possuiAuth ? "" : "d-none" ?>"
                            href="/creditos/alterar_token_auth.php">alterar</a>
                    </div>
                </div>
                <div class="row top5">
                    <div class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right"><label for="telefone_contato">Telefone:
                        </label></div>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9">
                        <input type="text" name="telefone_contato" id="telefone_contato" maxlength="14" char="14"
                            required <?php if ($controller->usuarios->getTelDDD() != "" && $controller->usuarios->getTel() != "") { ?>value="<?php echo $controller->usuarios->getTelDDD() . " " . $controller->usuarios->getTel(); ?>"
                            <?php } ?> class="telefone form-control w-auto">
                        <span class="txt-vermelho" id="errotelefone_contato" style="display:none;">O telefone está
                            incompleto.</span>
                    </div>
                </div>
                <div class="row top5">
                    <div class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right"><label for="celular_contato">Celular:
                        </label></div>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9">
                        <input type="text" name="celular_contato" id="celular_contato" maxlength="15" char="14" required
                            <?php if ($controller->usuarios->getCelDDD() != "" && $controller->usuarios->getCel() != "") { ?>value="<?php echo $controller->usuarios->getCelDDD() . " " . $controller->usuarios->getCel(); ?>"
                            <?php } ?> class="celular form-control w-auto">
                        <span class="txt-vermelho" id="errocelular_contato" style="display:none;">O telefone está
                            incompleto.</span>
                    </div>
                </div>
                <div class="row top5">
                    <div class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right">Skype: </div>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9"><input type="text" name="skype" id="skype"
                            value="<?php echo ($controller->usuarios->getReprVendaMSN() != "") ? $controller->usuarios->getReprVendaMSN() : $controller->usuarios->getReprLegalMSN(); ?>"
                            class="form-control w-auto"></div>
                </div>
                <div class="row top10">
                    <p class="p-left25"><strong>Representante da empresa:</strong></p>
                </div>
                <div class="row top5">
                    <div class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right">Nome: </div>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9">
                        <?php echo $controller->usuarios->getReprLegalNome(); ?>
                    </div>
                </div>
                <div class="row top5">
                    <div class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right">RG: </div>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9">
                        <?php echo $controller->usuarios->getReprLegalRG(); ?>
                    </div>
                </div>
                <div class="row top5">
                    <div class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right">CPF: </div>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9">
                        <?php echo $controller->usuarios->getReprLegalCPF(); ?>
                    </div>
                </div>
                <div class="row top10">
                    <p class="p-left25"><strong>Sócios:</strong></p>
                </div>
                <?php
                if (count($socios) > 0) {
                    for ($j = 0; $j < count($socios); $j++) {
                        ?>
                        <div class="row top10">
                            <p class="p-left25"><strong>Sócio <?php echo ($j + 1) ?> </strong></p>
                        </div>
                        <div class="row top5">
                            <div class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right">Nome: </div>
                            <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9"><?php echo $socios[$j]['ugs_nome']; ?></div>
                        </div>
                        <div class="row top5">
                            <div class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right">CPF: </div>
                            <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9"><?php echo $socios[$j]['ugs_cpf']; ?></div>
                        </div>
                        <div class="row top5">
                            <div class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right">Data Nascimento: </div>
                            <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9">
                                <?php echo formata_data($socios[$j]['ugs_data_nascimento'], 0); ?>
                            </div>
                        </div>
                        <div class="row top5">
                            <div class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right">Porcentagem na Empresa: </div>
                            <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9"><?php echo $socios[$j]['ugs_percentagem'] . "%"; ?>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    ?>
                    <div class="row top5">
                        <div class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right">*</div>
                        <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9">Sócios ainda não informados. Entre em contato com o
                            <a href="https://<?php echo $_SERVER["SERVER_NAME"] ?>/game/suporte.php"
                                target="_blank">Suporte</a> para atualizar seu cadastro.
                        </div>
                    </div>
                    <?php
                }
                ?>
                <div class="row top10">
                    <p class="p-left25"><strong>Dados do estabelecimento:</strong></p>
                </div>
                <div class="row top5">
                    <div class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right">Nome: </div>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9">
                        <input type="hidden" name="fantasia_empresa" id="fantasia_empresa" char="5" maxlength="100"
                            required value="<?php echo $controller->usuarios->getNomeFantasia(); ?>">
                        <?php echo $controller->usuarios->getNomeFantasia(); ?>
                    </div>
                </div>
                <div class="row top5">
                    <div class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right">Razão Social: </div>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9">
                        <?php echo $controller->usuarios->getRazaoSocial(); ?>
                    </div>
                </div>
                <div class="row top5">
                    <div class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right">
                        <?php echo (($controller->usuarios->getTipoCadastro() == "PF") ? "CPF" : "CNPJ"); ?>:
                    </div>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9">
                        <?php echo (($controller->usuarios->getTipoCadastro() == "PF") ? ($controller->usuarios->getCPF() ? mascara_cnpj_cpf(preg_replace('/[^0-9]/', '', $controller->usuarios->getCPF()), "cpf") : "<div class='txt-vermelho'>Campo faltante entre em contato com o suporte E-Prepag</div>") : ($controller->usuarios->getCNPJ() ? mascara_cnpj_cpf(preg_replace('/[^0-9]/', '', $controller->usuarios->getCNPJ()), "cnpj") : "<div class='txt-vermelho'>Campo faltante entre em contato com o suporte E-Prepag</div>")); ?>
                    </div>
                </div>
                <div class="row top5">
                    <div class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right"><label for="tipo_estabelecimento">Tipo:
                        </label></div>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9">
                        <select name="tipo_estabelecimento_empresa" id="tipo_estabelecimento" char="1"
                            class="form-control w-auto" required>
                            <option value=""> Selecione </option>
                            <?php
                            foreach ($estabelecimentos as $ind => $val) {
                                $selected = "";
                                if ($controller->usuarios->getTipoEstabelecimento() == $ind)
                                    $selected = "selected";
                                echo '<option value="' . $ind . '" ' . $selected . '>' . $val . '</option>';
                            }
                            ?>
                            <!-- option value="Outros" <?php if ($controller->usuarios->getTipoEstabelecimento() == "Outros")
                                echo "selected"; ?>> Outros </option -->
                        </select>
                        <span class="txt-vermelho" id="errotipo_estabelecimento" style="display:none;">Opção
                            inválida.</span>
                    </div>
                </div>
                <!--                <div class="row top5" id="divOutros_tipo_estabelecimento" style="display:none;">
                    <div class="col-md-3 text-right"><label for="outro_estabelecimento">Qual?</label></div>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9">
                        <input type="text" id="outro_estabelecimento" name="outro_estabelecimento" size="9" char="5" class="form-control  w-auto" /><span class="form_obs">(Sem hífen ou espaços)</span>
                    </div>
                </div>-->
                <div class="row top5">
                    <div class="col-md-3 col-lg-3 col-sm-12 col-xs-12 text-left"><label
                            for="faturamento_medio">Faturamento médio mensal: </label></div>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9">
                        <select name="faturamento_medio" char="1" id="faturamento_medio" class="form-control w-auto"
                            required>
                            <option value=""> Selecione </option>
                            <option value="1" <?php if ($controller->usuarios->getFaturaMediaMensal() == "1")
                                echo "selected"; ?>>Menor que R$ 5.000,00</option>
                            <option value="2" <?php if ($controller->usuarios->getFaturaMediaMensal() == "2")
                                echo "selected"; ?>>R$ 5.000,01 - R$ 10.000,00</option>
                            <option value="3" <?php if ($controller->usuarios->getFaturaMediaMensal() == "3")
                                echo "selected"; ?>>R$ 10.000,01 - R$ 20.000,00</option>
                            <option value="4" <?php if ($controller->usuarios->getFaturaMediaMensal() == "4")
                                echo "selected"; ?>>Acima de R$ 20.000,00</option>
                        </select>
                        <span class="txt-vermelho" id="errofaturamento_medio" style="display:none;">Opção
                            inválida.</span>
                    </div>
                </div>
                <div class="row top5">
                    <div class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right">CEP: </div>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9"><?php echo $controller->usuarios->getCEP(); ?>
                    </div>
                </div>
                <div class="row top5">
                    <div class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right">Estado: </div>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9"><?php echo $controller->usuarios->getEstado(); ?>
                    </div>
                </div>
                <div class="row top5">
                    <div class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right">Cidade: </div>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9"><?php echo $controller->usuarios->getCidade(); ?>
                    </div>
                </div>
                <div class="row top5">
                    <div class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right">Bairro: </div>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9"><?php echo $controller->usuarios->getBairro(); ?>
                    </div>
                </div>
                <div class="row top5">
                    <div class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right">Endereço: </div>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9"><?php echo $controller->usuarios->getEndereco(); ?>
                    </div>
                </div>
                <div class="row top5">
                    <div class="col-md-3  col-lg-3 col-sm-3 col-xs-3 text-right">Número: </div>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9"><?php echo $controller->usuarios->getNumero(); ?>
                    </div>
                </div>
                <div class="row top5">
                    <div class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right">Complemento: </div>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9">
                        <?php echo $controller->usuarios->getComplemento(); ?>
                    </div>
                </div>
                <div class="row top5">
                    <div class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right">Site: </div>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9"><input type="text" name="site"
                            value="<?php echo $controller->usuarios->getSite(); ?>" class="form-control w-auto"></div>
                </div>
                <div class="row top10">
                    <p class="p-left25"><strong>Estilo do e-mail de comprovante e do cupom impresso no pedido:</strong>
                    </p>
                </div>
                <div class="row top5">
                    <label style="font-weight: normal;" class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right">Cor
                        primária:
                        <span class="help-icon">?<span class="tooltiptext">Dica: escolha uma cor escura e que represente
                                sua marca</span></span>
                    </label>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9"><input type="color" style="width: 100px;"
                            class="form-control" name="cor_primaria" id="cor_primaria"
                            value="<?php echo !empty($dadosEstiloLink["cor_primaria"]) ? htmlspecialchars($dadosEstiloLink["cor_primaria"]) : ""; ?>">
                    </div>
                </div>
                <div class="row top5">
                    <label style="font-weight: normal;" class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right">Cor
                        secundária:<span class="help-icon">?<span class="tooltiptext">Dica: escolha uma cor escura,
                                essa é a cor utilizada geralmente em detalhes e textos em
                                destaque</span></span></label>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9"><input type="color" style="width: 100px;"
                            class="form-control" name="cor_secundaria" id="cor_secundaria"
                            value="<?php echo !empty($dadosEstiloLink["cor_secundaria"]) ? htmlspecialchars($dadosEstiloLink["cor_secundaria"]) : ""; ?>">
                    </div>
                </div>
                <div class="row top5">
                    <label style="font-weight: normal;" class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right">E-mail de
                        suporte:<span class="help-icon">?<span class="tooltiptext">Esse é o e-mail do seu suporte que
                                vai ao cliente na venda, caso ele precise entrar em contato</span></span></label>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9"><input type="text" style="width: 250px;"
                            class="form-control" name="email_suporte" id="email_suporte" placeholder="exemplo@email.com"
                            value="<?php echo !empty($dadosEstiloLink["email_suporte"]) ? htmlspecialchars($dadosEstiloLink["email_suporte"]) : ""; ?>">
                    </div>
                </div>
                <div class="row top5">
                    <label style="font-weight: normal;"
                        class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right">Link para atendimento:<span class="help-icon">?<span class="tooltiptext">Esse é o link do seu canal de suporte, por favor, coloque o link completo com https://</span></span></label>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9"><input type="text" style="width: 250px;"
                            class="form-control" name="link_canal" id="link_canal" placeholder="https://link.com.br"
                            value="<?php echo !empty($dadosEstiloLink["link_canal"]) ? htmlspecialchars($dadosEstiloLink["link_canal"]) : ""; ?>">
                    </div>
                </div>
                <div class="row top5">
                    <label style="font-weight: normal;"
                        class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right">Mensagem:<span class="help-icon">?<span class="tooltiptext">Essa é a mensagem personalizada do seu PDV</span></span></label>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9"><textarea style="width: 300px; height: 150px;"
                            class="form-control" name="mensagem" id="mensagem"
                            placeholder="A maior loja de games da regiao..."><?php echo !empty($dadosEstiloLink["mensagem"]) ? htmlspecialchars($dadosEstiloLink["mensagem"]) : ""; ?></textarea>
                    </div>
                </div>
                <div class="row top5">
                    <label style="font-weight: normal;"
                        class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right">Logo:<span class="help-icon">?<span class="tooltiptext">Envie sua logo nos formatos PNG ou JPG, preferencialmente com proporção horizontal e até 200 kB</span></span></label>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9">
                        <label id="labelfile" for="logo">Envie sua logo</label>
                        <input type="file" class="form-control w-auto" id="logo" name="logo">
                        <p id="nenhum"
                            style="<?php echo empty($dadosEstiloLink["logo_base64"]) ? "display: block;color: red;" : "display:none;"; ?>">
                            Você ainda não tem logo cadastrado</p>
                        <img style="<?php echo !empty($dadosEstiloLink["logo_base64"]) ? "display: block;object-fit: contain;background: #eeeeee;padding: 10px;border-radius: 10px; max-width: 250px; max-height: 50px; margin-top: 20px; margin-bottom: 15px;" : "display:none;"; ?>"
                            src="<?php echo $dadosEstiloLink["logo_base64"]; ?>" id="preview">
                        <?php
                        if (!empty($dadosEstiloLink["logo_base64"])) {
                            echo '<div>
                                <label for="rm_logo" style="font-weight: normal; margin-right: 5px;" >Remover logo</label>
                                <input type="checkbox" name="rm_logo" id="rm_logo" />
                            </div>';
                        }
                        ?>
                    </div>
                </div>
                <div class="row top10">
                    <div class="col-md-offset-3 col-md-9 fontsize-pp">
                        <p>Precisa alterar algum campo não disponível?</p>
                        <p>Por favor, entre em contato com o <a
                                href="https://<?php echo $_SERVER["SERVER_NAME"] ?>/game/suporte.php"
                                target="_blank">suporte</a>.</p>
                    </div>
                </div>
                <div class="row top10 bottom10">
                    <div class="col-md-offset-3 col-md-9 fontsize-pp">
                        <button type="submit" id="btnSalvar" name="salvar" value="1"
                            class="btn btn-info">Salvar</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-md-2 hidden-sm hidden-xs p-top10">
            <?php
            if ($banner) {
                foreach ($banner as $b) {
                    ?>
                    <div class="row pull-right">
                        <a href="<?php echo $b->link; ?>" class="banner" id="<?php echo $b->id; ?>" target="_blank"><img
                                src="<?php echo $controller->objBanner->urlLink . $b->imagem; ?>" width="186" class="p-3"
                                title="<?php echo $b->titulo; ?>"></a>
                    </div>
                    <?php
                }
            }
            ?>
            <div class="row pull-right facebook">
            </div>
        </div>
    </div>
</div>
<link href="/js/jqueryui/css/custom-theme/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="/js/facebook.js"></script>
<script src="/js/jqueryui/js/jquery-ui-1.9.2.custom.min.js"></script>
<script type="text/javascript" src="/js/jquery.mask.min.js"></script>
<script>

    const estilo = document.createElement('style');
    estilo.textContent = `
        .help-icon {
            margin-left: 5px;
            cursor: pointer;
            background: #007BFF;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            text-align: center;
            line-height: 18px;
            font-size: 12px;
            user-select: none;
            display: inline-block;
        }
        .help-icon .tooltiptext {
            visibility: hidden;
            width: 120px;
            bottom: 100%;
            left: 50%;
            margin-left: -60px;
            background-color: rgba(0, 0, 0, 0.9);
            color: #fff;
            text-align: center;
            border-radius: 6px;
            padding: 5px;
            font-weight: bold;

            /* Position the tooltip */
            position: absolute;
            z-index: 1;
        }

        .help-icon .tooltiptext::after {
            content: " ";
            position: absolute;
            top: 100%; /* At the bottom of the tooltip */
            left: 50%;
            margin-left: -5px;
            border-width: 5px;
            border-style: solid;
            border-color: black transparent transparent transparent;
        }

        .help-icon:hover .tooltiptext,
        .tooltiptext.show {
            visibility: visible;
            pointer-events: auto;
        }
      `;
    document.head.appendChild(estilo);

    document.querySelectorAll('.help-icon').forEach(icon => {
        icon.addEventListener('click', () => {
            const tooltip = icon.querySelector('.tooltiptext');

            // Remove outros tooltips visíveis
            document.querySelectorAll('.tooltiptext.show').forEach(other => {
                if (other !== tooltip) other.classList.remove('show');
            });

            tooltip.classList.add('show');

            // Remove após 3 segundos
            setTimeout(() => {
                tooltip.classList.remove('show');
            }, 3000);
        });
    });

    $(function () {
        $('.telefone').mask('(99) 9999-9999');
        $('.celular').mask('(00) 90000-0000');

        var aviso = 0;

        $(".form-control").focus(function () {
            if (aviso === 0) {
                $("#modal-edicao").modal();
                aviso++;
            }
        });

        $("#formCad").on("submit", function () {
            var id = "";
            var char = null;
            var tamanho = null;
            var erros = [];

            $(".form-control").each(function () {
                if ($(this).attr("required")) {
                    char = $(this).attr("char");
                    tamanho = $(this).val().length;
                    id = $(this).attr("id");

                    if (tamanho < char) {
                        $("label[for='" + id + "']").css("color", "red");
                        erros.push(id);
                    }
                }
            });

            if (erros.length > 0) {
                for (i = 0; i < erros.length; i++) {
                    $("#erro" + erros[i]).show();
                    e.preventDefault();
                }
            }
        });

        //    $("#tipo_estabelecimento").change(function(){
        //        if($(this).val() == "Outros"){
        //            $("#outro_estabelecimento").attr("required","required");
        //            $("#divOutros_tipo_estabelecimento").show();
        //        }else{
        //            $("#outro_estabelecimento").removeAttr("required");
        //            $("#divOutros_tipo_estabelecimento").hide();
        //        }
        //    });

    });

</script>
<?php
require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/footer.php";

