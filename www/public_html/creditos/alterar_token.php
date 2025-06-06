<?php require_once __DIR__ . '/../../includes/constantes_url.php'; ?>
<?php
require_once "../../includes/constantes.php";
require_once "../libs/PHPGangsta/GoogleAuthenticator.php";
require_once RAIZ_DO_PROJETO . "class/pdv/controller/OffLineController.class.php";

$controller = new OfflineController;

$https = 'http' . (($_SERVER['HTTPS'] == 'on') ? 's' : '');
$server_url = $https . '://' . (checkIP() ? $_SERVER['SERVER_NAME'] : '' . EPREPAG_URL . '');

$id_do_usuario = $_SESSION['id_do_usuario'] ? $_SESSION['id_do_usuario'] : 0;

if ($_SESSION['usuario_operador']) {
    $sql = "select * from dist_usuarios_games_operador where ugo_id = ? and ugo_ativo = 1";

    $con = ConnectionPDO::getConnection();
    $pdo = $con->getLink();

    $stmt = $pdo->prepare($sql);
    $stmt->execute(array($id_do_usuario));
    $fetch = $stmt->fetch(PDO::FETCH_ASSOC);

    $user = $fetch;

    if (empty($user)) {
        $msg = "Usuario inválido.\n";
        $linha = "4[" . date('Y-m-d H:i:s') . "] [".$_SESSION['login_usuario']."] $msg" . PHP_EOL;
        file_put_contents('/www/log/log_login.txt', $linha, FILE_APPEND);
        //$pag = $server_url . $pag;
        $strRedirect = $server_url .
            "/creditos/login.php?msg=" .
            urlencode($msg) .
            "&login=" .
            urlencode($login);

        header("Location: $strRedirect");
        exit;
    }

    $sql = "SELECT ugo_chave_autenticador FROM dist_usuarios_games_operador WHERE ugo_id = ?";
    $con = ConnectionPDO::getConnection();
    $pdo = $con->getLink();

    $stmt = $pdo->prepare($sql);
    $stmt->execute(array($user['ugo_id']));
    $authData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($authData) {
        if (!empty($authData['ugo_chave_autenticador'])) {
            $msg = "Usuario inválido.\n";
            $linha = "4[" . date('Y-m-d H:i:s') . "] [".$_SESSION['login_usuario']."] $msg" . PHP_EOL;
            file_put_contents('/www/log/log_login.txt', $linha, FILE_APPEND);
            //$pag = $server_url . $pag;
            $strRedirect = $server_url .
                "/creditos/login.php?msg=" .
                urlencode($msg) .
                "&login=" .
                urlencode($login);

            header("Location: $strRedirect");
            exit;
        }
    }

    $token = $_POST['token'];
    $secret = $_SESSION['secret'];

    if ($token && $secret) {
        $ga = new PHPGangsta_GoogleAuthenticator();
        $checkResult = $ga->verifyCode($secret, $token, 2);

        if ($checkResult) {
            $sql = "UPDATE dist_usuarios_games_operador SET ugo_chave_autenticador = ? WHERE ugo_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$secret, $user['ugo_id']]);

            // Verifica se alguma linha foi afetada
            if ($stmt->rowCount() > 0) {
                header("Location: loginEf2.php?token=$token");
                exit;
            }
            $msg = "Erro ao salvar o token!";
            $cor = "text-danger";
        } else {
            // Token is invalid
            $msg = "Token inválido!";
            $cor = "text-danger";

            $secret = $ga->createSecret();
            $qrCodeUrl = $ga->getQRCodeGoogleUrl('E-Prepag', $secret);
            $_SESSION['secret'] = $secret;
        }
    } else {
        $ga = new PHPGangsta_GoogleAuthenticator();

        $secret = $ga->createSecret();
        $qrCodeUrl = $ga->getQRCodeGoogleUrl('E-Prepag', $secret);
        $_SESSION['secret'] = $secret;
    }
} else {
    $sql = "select * from dist_usuarios_games where ug_id = ? and ug_ativo = 1";

    $con = ConnectionPDO::getConnection();
    $pdo = $con->getLink();

    $stmt = $pdo->prepare($sql);
    $stmt->execute(array($id_do_usuario));
    $fetch = $stmt->fetch(PDO::FETCH_ASSOC);

    $user = $fetch;

    if (empty($user)) {
        $msg = "Usuario inválido.\n";
        $linha = "4[" . date('Y-m-d H:i:s') . "] [".$_SESSION['login_usuario']."] $msg" . PHP_EOL;
        file_put_contents('/www/log/log_login.txt', $linha, FILE_APPEND);
        //$pag = $server_url . $pag;
        $strRedirect = $server_url .
            "/creditos/login.php?msg=" .
            urlencode($msg) .
            "&login=" .
            urlencode($login);

        header("Location: $strRedirect");
        exit;
    }

    $sql = "SELECT ug_chave_autenticador FROM dist_usuarios_games WHERE ug_id = ?";
    $con = ConnectionPDO::getConnection();
    $pdo = $con->getLink();

    $stmt = $pdo->prepare($sql);
    $stmt->execute(array($user['ug_id']));
    $authData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($authData) {
        if (!empty($authData['ug_chave_autenticador'])) {
            $msg = "Usuario inválido.\n";
            $linha = "4[" . date('Y-m-d H:i:s') . "] [".$_SESSION['login_usuario']."] $msg" . PHP_EOL;
            file_put_contents('/www/log/log_login.txt', $linha, FILE_APPEND);
            //$pag = $server_url . $pag;
            $strRedirect = $server_url .
                "/creditos/login.php?msg=" .
                urlencode($msg) .
                "&login=" .
                urlencode($login);

            header("Location: $strRedirect");
            exit;
        }
    }

    $token = $_POST['token'];
    $secret = $_SESSION['secret'];

    if ($token && $secret) {
        $ga = new PHPGangsta_GoogleAuthenticator();
        $checkResult = $ga->verifyCode($secret, $token, 2);

        if ($checkResult) {
            $sql = "UPDATE dist_usuarios_games SET ug_chave_autenticador = ? WHERE ug_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$secret, $user['ug_id']]);

            // Verifica se alguma linha foi afetada
            if ($stmt->rowCount() > 0) {
                header("Location: loginEf2.php?token=$token");
                exit;
            }
            $msg = "Erro ao salvar o token!";
            $cor = "text-danger";
        } else {
            // Token is invalid
            $msg = "Token inválido!";
            $cor = "text-danger";

            $secret = $ga->createSecret();
            $qrCodeUrl = $ga->getQRCodeGoogleUrl('E-Prepag', $secret);
            $_SESSION['secret'] = $secret;
        }
    } else {
        $ga = new PHPGangsta_GoogleAuthenticator();

        $secret = $ga->createSecret();
        $qrCodeUrl = $ga->getQRCodeGoogleUrl('E-Prepag', $secret);
        $_SESSION['secret'] = $secret;
    }
}

require_once "includes/header-offline.php";
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
                                Insira o Token gerado pelo autenticador:
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
</div>
<link href="/js/jqueryui/css/custom-theme/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="/js/facebook.js"></script>
<script src="/js/jqueryui/js/jquery-ui-1.9.2.custom.min.js"></script>
<script>
    $(function () {
        $("#div-copiar").click(function () {
            copyAuthCode();
        });
    });

    function copyAuthCode() {
        const authCode = document.getElementById("authCode").innerText;
        navigator.clipboard.writeText(authCode).then(() => {
            const message = document.getElementById("copyMessage");
            message.innerText = "Copiado!";
            setTimeout(() => {
                message.innerText = "Clique para copiar";
            }, 2000);
        }).catch(err => {
            console.error("Erro ao copiar:", err);
        });
    }

</script>
<?php
require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/footer.php";