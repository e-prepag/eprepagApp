<?php require_once __DIR__ . '/../../../includes/constantes_url.php'; ?>
<?php
$request_uri = $_SERVER['REQUEST_URI'];
// Obtém o script principal chamado
$script_name = $_SERVER['SCRIPT_NAME'];
// Se a URI acessada não for exatamente igual ao script chamado, bloqueia o acesso
if ($request_uri !== $script_name) {
    http_response_code(403);
    die("Acesso negado.");
}
require_once "../../../includes/constantes.php";
require_once DIR_CLASS . "gamer/controller/HeaderController.class.php";
require_once RAIZ_DO_PROJETO . 'consulta_cpf/config.inc.cpf.php';
require_once "../../libs/PHPGangsta/GoogleAuthenticator.php";

//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
$https = 'http' . (($_SERVER['HTTPS'] == 'on') ? 's' : '');
$server_url = $https . '://' . (checkIP() ? $_SERVER['SERVER_NAME'] : '' . EPREPAG_URL . '');

$id_do_usuario = $_SESSION['id_do_usuario'] ? $_SESSION['id_do_usuario'] : 0;

$sql = "select * from usuarios_games where ug_id = ? and ug_ativo = 1";

$con = ConnectionPDO::getConnection();
$pdo = $con->getLink();

$stmt = $pdo->prepare($sql);
$stmt->execute(array($id_do_usuario));
$fetch = $stmt->fetch(PDO::FETCH_ASSOC);

$user = $fetch;

if (empty($user)) {
    $msg = "Usuario inválido.\n";
    $linha = "4g[" . date('Y-m-d H:i:s') . "] [" . $_SESSION['id_do_usuario'] . "] $msg" . PHP_EOL;
    file_put_contents('/www/log/log_login.txt', $linha, FILE_APPEND);
    //$pag = $server_url . $pag;
    $strRedirect = $server_url .
        "/game/conta/login_teste.php?msg=" .
        urlencode($msg) .
        "&login=" .
        urlencode($login);

    header("Location: $strRedirect");
    exit;
}

$sql = "SELECT ug_chave_autenticador FROM usuarios_games WHERE ug_id = ?";
$con = ConnectionPDO::getConnection();
$pdo = $con->getLink();

$stmt = $pdo->prepare($sql);
$stmt->execute(array($user['ug_id']));
$authData = $stmt->fetch(PDO::FETCH_ASSOC);

if ($authData) {
    if (!empty($authData['ug_chave_autenticador'])) {
        $msg = "Usuario inválido.\n";
        $linha = "4g[" . date('Y-m-d H:i:s') . "] [" . $_SESSION['id_do_usuario'] . "] $msg" . PHP_EOL;
        file_put_contents('/www/log/log_login.txt', $linha, FILE_APPEND);
        //$pag = $server_url . $pag;
        $strRedirect = $server_url .
            "/game/conta/login_teste.php?msg=" .
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
        $sql = "UPDATE usuarios_games SET ug_chave_autenticador = ? WHERE ug_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$secret, $user['ug_id']]);

        // Verifica se alguma linha foi afetada
        if ($stmt->rowCount() > 0) {
            header("Location: $server_url/game/conta/login_teste.php");
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

$controller = new HeaderController;
$controller->setHeader();

require_once RAIZ_DO_PROJETO . "public_html/game/includes/termos-de-uso.php";
$termosDeUso = strip_tags($termosDeUso);
?>
<script src="/js/valida.js"></script>
<script src="/js/validaSenha.js"></script>
<script>
    <?php
    if (!empty($erros)) {
        print "manipulaModal(1,\"" . implode($erros) . "\",'Atenção');";
    }
    ?>

</script>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script src="https://kit.fontawesome.com/e045fafe2e.js" crossorigin="anonymous"></script>
<div class="container txt-cinza bg-branco  p-bottom40">
    <div class="row top10">
        <div class="col-md-12 txt-verde top10">
            <span class="glyphicon glyphicon-triangle-right graphycon-big pull-left" aria-hidden="true"></span><strong
                class="pull-left">
                <h4 class="top20">Crie seu token de autenticação.<span class="hidden-md hidden-lg"><br></span> Utilize um aplicativo autenticador!</h4>
            </strong>
        </div>
        <div class="clearfix"></div>
        <div class="col-md-10 txt-preto" style="margin: 25px; padding: 15px;">
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
                            <label class="">QR Code (utilize seu app):</label>
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
                                Insira o Token gerado pelo app autenticador:
                            </label>
                            <input type="text" name="token" id="token" class="form-control">
                        </div>

                        <div class="d-grid gap-2 mt-3" style="margin-top: 15px;">
                            <button type="submit" class="btn btn-info">Salvar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 align-center p-bottom10">
            Caso não possua um aplicativo autenticador, baixe o google authenticator ou microsoft authenticator
        </div>
    </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"
    integrity="sha512-pHVGpX7F/27yZ0ISY+VVjyULApbDlD0/X0rgGbTqCE7WFW5MezNTWG/dnhtbBuICzsd0WQPgpE4REBLv+UqChw=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    $('#cpf').mask('000.000.000-00', { reverse: true });
    $('#dtNasc').mask('00/00/0000', { placeholder: "__/__/____" });
</script>
<?php
require_once RAIZ_DO_PROJETO . "public_html/game/includes/footer.php";