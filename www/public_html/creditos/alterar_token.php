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
        $linha = "4[" . date('Y-m-d H:i:s') . "] [" . $_SESSION['login_usuario'] . "] $msg" . PHP_EOL;
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
            $linha = "4[" . date('Y-m-d H:i:s') . "] [" . $_SESSION['login_usuario'] . "] $msg" . PHP_EOL;
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
        }
    }
    if (!$secret) {
        $ga = new PHPGangsta_GoogleAuthenticator();

        $secret = $ga->createSecret();
        $qrCodeUrl = $ga->getQRCodeGoogleUrl('E-Prepag', $secret);
        $_SESSION['secret'] = $secret;
    } else if (!isset($qrCodeUrl)) {
        $ga = new PHPGangsta_GoogleAuthenticator();
        $qrCodeUrl = $ga->getQRCodeGoogleUrl('E-Prepag', $secret);
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
        $linha = "4[" . date('Y-m-d H:i:s') . "] [" . $_SESSION['login_usuario'] . "] $msg" . PHP_EOL;
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
            $linha = "4[" . date('Y-m-d H:i:s') . "] [" . $_SESSION['login_usuario'] . "] $msg" . PHP_EOL;
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
        }
    }
    if (!$secret) {
        $ga = new PHPGangsta_GoogleAuthenticator();

        $secret = $ga->createSecret();
        $qrCodeUrl = $ga->getQRCodeGoogleUrl('E-Prepag', $secret);
        $_SESSION['secret'] = $secret;
    } else if (!isset($qrCodeUrl)) {
        $ga = new PHPGangsta_GoogleAuthenticator();
        $qrCodeUrl = $ga->getQRCodeGoogleUrl('E-Prepag', $secret);
    }
}

require_once "includes/header-offline.php";
?>
<style>
    .form-token {
        display: flex;
        justify-content: space-between;
        flex-direction: column;
    }

    .instrucoes {
        font-family: system-ui, sans-serif;
        color: #333;
    }

    ol.lista-instrucoes li {
        line-height: 1.7;
        margin-bottom: 3px;
    }

    .div-principal {
        display: flex;
        flex-direction: row;
        justify-content: stretch;
    }

    .botao-expandir {
        background: none;
        border: none;
        color: #555;
        font-size: 16px;
        font-family: system-ui, sans-serif;
        cursor: pointer;
        padding: 8px 0;
        margin-bottom: 10px;
    }

    .botao-expandir:hover {
        color: #333;
    }


    @media (min-width: 769px) {
        .botao-expandir {
            display: none;
        }

        .instrucoes {
            display: block !important;
        }
    }

    @media (max-width: 768px) {
        .div-principal {
            flex-direction: column;
            /* empilha os itens */
        }

        .instrucoes {
            display: none;
        }

        .instrucoes.expandida {
            display: block;
        }

        .botao-expandir {
            display: block;
            margin-top: 15px;
        }
    }
</style>
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

            <div class="div-principal">
                <div class="form-token">
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
                    <div class="col-md-12 fontsize-p" style="text-align: start;">
                        <p class="decoration-none txt-cinza"><em>Problemas com a autenticação?</em></p>
                        <a class="decoration-none txt-cinza" id="faca-cadastro" target="_blank" href="/game/suporte.php"><em>Entre em
                                contato com o suporte.</em></a>
                    </div>
                </div>
                <button class="botao-expandir btn"
                    onclick="document.querySelector('.instrucoes').classList.toggle('expandida')">
                    Como configurar o autenticador? &#11206;
                </button>
                <div class="col-md-8 form-group col-sm-12 col-xs-12 col-md-offset-4 instrucoes">

                    <h3>Instruções:</h3>
                    <ol class="lista-instrucoes">
                        <li>Abra o aplicativo autenticador instalado no seu celular. Caso não tenha um autenticador,
                            você deve instalar um. O Microsoft Authenticator e o Google Authenticator são os mais
                            populares.</li>

                        <li>Com o aplicativo aberto, leia o QR code gerado pelo nosso site.
                            Se estiver usando celular, copie a chave de segurança gerada e cole no
                            aplicativo autenticador.</li>

                        <li>Aparecerá um código de 6 dígitos no seu aplicativo.</li>

                        <li>Digite esse código no site da E-prepag para confirmar e pronto! O autenticador está
                            associado a seu PDV.</li>

                    </ol>
                    <div style="width: 100%; display: flex; justify-content: center;">
                        <iframe width="300" height="170px" src="https://www.youtube.com/embed/H_19Cv6jSDU"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen>
                        </iframe>
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