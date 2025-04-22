<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
require_once "../../includes/constantes.php";
require_once DIR_CLASS . "pdv/controller/MeuCadastroController.class.php";
require_once "../libs/PHPGangsta/GoogleAuthenticator.php";

$controller = new MeuCadastroController;

$banner = $controller->getBanner();

$token = $_POST['token'];
$secret = $_SESSION['secret'];

if ($token && $secret) {

    @$cad_id = $controller->usuarios->getId();

    $cad_senhaAtual = $_POST['cad_senhaAtual'];

    $objEncryption = new Encryption();
    $senhaAtual = $objEncryption->encrypt(trim($cad_senhaAtual));

    $ga = new PHPGangsta_GoogleAuthenticator();
    $checkResult = $ga->verifyCode($secret, $token, 2);

    if ($checkResult) {
        $sql = "UPDATE dist_usuarios_games SET ug_chave_autenticador = ? WHERE ug_id = ? and ug_senha = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$secret, $cad_id, $senhaAtual]);

        // Verifica se alguma linha foi afetada
        if ($stmt->rowCount() > 0) {
            $cor = "txt-verde";
            $msg = "Token alterado com sucesso!";
        } else {
            $msg = "Senha ou Token inválidos! Verifique se o Token atual foi inserido corretamente.";
            $cor = "text-danger";
        }
    } else {
        // Token is invalid
        $msg = "Senha ou Token inválidos! Verifique se o Token atual foi inserido corretamente.";
        $cor = "text-danger";
    }
}

$ga = new PHPGangsta_GoogleAuthenticator();

$secret = $ga->createSecret();
$qrCodeUrl = $ga->getQRCodeGoogleUrl('E-Prepag', $secret);
$_SESSION['secret'] = $secret;

require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/header.php";
?>
<div class="container bg-branco">
    <div class="row">
        <div class="col-md-10 txt-preto" style="margin: 25px; padding: 15px;">
            <div class="row">
                <div class="col-12 mb-3 text-primary">
                    <strong>Token de autenticação</strong>
                </div>
            </div>

            <?php if (isset($msg)) { ?>
                <div class="row">
                    <div class="col-12 mb-3 <?php echo $cor; ?>">
                        <p><?php echo $msg; ?></p>
                    </div>
                </div>
            <?php } ?>

            <div class="row">
                <div class="col-md-6">
                    <form name="form1" action="" method="post">
                        <div class="mb-3">
                            <label style="margin-top: 15px;" for="token" class="<?php if (isset($txtVermelho))
                                echo $txtVermelho; ?>">
                                Insira o Token gerado pelo autenticador atual:
                            </label>
                            <input type="text" name="token" id="token" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label style="margin-top: 15px;" for="cad_senhaAtual" class="<?php if (isset($txtVermelho))
                                echo $txtVermelho; ?>">
                                Insira sua senha atual:
                            </label>
                            <input type="password" name="cad_senhaAtual" id="cad_senhaAtual" class="form-control">
                        </div>
                        <div class="text-left" style="margin: 15px;">
                            <label class="">QR Code:</label>
                            <div>
                                <img src="<?= $qrCodeUrl ?>" class="img-fluid" />
                            </div>
                        </div>

                        <div class="text-left mt-3" style="margin: 15px;">
                            <label class="">Chave de segurança:</label>
                            <div id="div-copiar" style="cursor: pointer;">
                                <p id="authCode" style="font-size: 15px; letter-spacing: 0.5px; margin-bottom: 0px;">
                                    <?= $secret ?>
                                </p>
                                <small style="color: #333;" id="copyMessage">Clique para copiar</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label style="margin-top: 15px;" for="token" class="<?php if (isset($txtVermelho))
                                echo $txtVermelho; ?>">
                                Insira o novo Token gerado:
                            </label>
                            <input type="text" name="token" id="token" class="form-control">
                        </div>

                        <div class="d-grid gap-2 mt-3" style="margin-top: 15px;">
                            <button type="submit" class="btn btn-info">Salvar</button>
                        </div>
                    </form>
                </div>

                <div class="col-md-4 d-md-block">
                    <div class="mt-3 text-danger" style="text-align:justify;">
                        <ol>
                            <li>Escaneie o QR code ou cole a chave no seu aplicativo autenticador.</li>
                            <li>Copie o Token gerado.</li>
                            <li>Cole no campo abaixo e confirme.</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-2 p-top10 hidden-sm hidden-xs">
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
<script src="/js/validaSenha.js"></script>
<script>
    $(function () {
        $(".salvar").click(function () {
            var form = $(this).closest("form");
            var erro = [];

            $(".form-control").each(function () {
                if ($(this).val().length < $(this).attr("char")) {
                    erro.push($(this).attr("label") + " deve ter " + $(this).attr("char") + " caracteres.");
                    $("label[for='" + $(this).attr("id") + "']").addClass("txt-vermelho");
                } else {
                    $("label[for='" + $(this).attr("id") + "']").removeClass("txt-vermelho");
                }
            });

            if (erro.length == 0) {
                erro = validaFormSenha(); //funcao esta em /js/validaSenha.js
            }

            if (erro.length > 0) {
                alert(erro.join("\n"));

            } else {
                $(form).submit();
            }
        });
    });
</script>
<?php
require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/footer.php";