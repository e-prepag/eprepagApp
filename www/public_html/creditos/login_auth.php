<?php require_once __DIR__ . '/../../includes/constantes_url.php'; ?>
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);  // Exibe todos os tipos de erros
require_once "../../includes/constantes.php";
require_once RAIZ_DO_PROJETO . "class/pdv/controller/OffLineController.class.php";
require '../libs/PHPGangsta/GoogleAuthenticator.php';
require_once "/www/includes/load_dotenv.php";
require_once __DIR__ . '/includes/funcoes_login.php';


$controller = new OfflineController;

require_once "includes/header-offline.php";

function checkDevice($userId, $pdo, $operador)
{
    if (!isset($_COOKIE['device_token'])) {
        return false; // Sem cookie, exige login
    }

    $tabela = $operador ? 'dist_usuarios_games_operador_dispositivos' : 'dist_usuarios_games_dispositivos';

    $deviceId = $_COOKIE['device_token'];
    $sql = "SELECT * FROM $tabela WHERE user_id = ? AND device_token = ? AND expires_at > NOW()";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId, $deviceId]);

    if ($stmt->fetch()) {
        return true; // Dispositivo válido
    } else {
        return false; // Dispositivo inválido ou expirado
    }
}

$https = 'http' . (($_SERVER['HTTPS'] == 'on') ? 's' : '');
$server_url = $https . '://' . (checkIP() ? $_SERVER['SERVER_NAME'] : '' . EPREPAG_URL . '');
session_start();

//Id do GoCASH
$id_gocash = 1;

$pag = $_REQUEST["pag"];
$login = $_REQUEST["login"];
$senha = $_REQUEST["senha"];
$recaptcha = $_REQUEST["g-recaptcha-response"];

if ($recaptcha != "") {

    $tokenInfo = [
        "secret" => getenv("RECAPTCHA_SECRET_KEY"),
        "response" => $recaptcha,
        "remoteip" => $_SERVER["REMOTE_ADDR"],
    ];

    $recaptcha_curl = curl_init();

    curl_setopt_array($recaptcha_curl, [
        CURLOPT_URL => getenv("RECAPTCHA_URL"),
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => http_build_query($tokenInfo),
    ]);

    $dadosT = curl_exec($recaptcha_curl);

    $inforCurl = curl_getinfo($recaptcha_curl);



    $retorno = json_decode($dadosT, true);


    curl_close($recaptcha_curl);

    if ($retorno["success"] != true || (isset($retorno["error-codes"]) && !empty($retorno["error-codes"]))) {
        $msg = "Captcha inválido.\n";

        $linha = "1[" . date('Y-m-d H:i:s') . "] [$login] $msg" . PHP_EOL;
        file_put_contents('/www/log/log_login.txt', $linha, FILE_APPEND);

        $strRedirect = $server_url .
            "/creditos/login.php?msg=" .
            urlencode($msg) .
            "&login=" .
            urlencode($login);

        header("Location: $strRedirect");
        exit;
    }
} else {
    $msg = "Captcha inválido.\n";

    $linha = "1[" . date('Y-m-d H:i:s') . "] [$login] $msg" . PHP_EOL;
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

$objEncryption = new Encryption();
$original = trim($senha);
$senhaCrip = $objEncryption->encrypt(trim($senha));
$login = strtoupper(trim($login));


$sql = "select * from dist_usuarios_games where ug_login = ? and ug_senha = ? and ug_ativo = 1 and ug_substatus in ('11', '9')";

$con = ConnectionPDO::getConnection();
$pdo = $con->getLink();

$stmt = $pdo->prepare($sql);
$stmt->execute(array($login, $senhaCrip));
$fetch = $stmt->fetch(PDO::FETCH_ASSOC);

$user = $fetch;

$usuario_operador = false;

if (empty($user)) {

    $sql = "
        SELECT 
            o.*, 
            g.ug_id as pdv_id
        FROM 
            dist_usuarios_games_operador o
        JOIN 
            dist_usuarios_games g ON o.ugo_ug_id = g.ug_id
        WHERE 
            o.ugo_login = ?
            AND o.ugo_senha = ?
            AND o.ugo_ativo = 1
            AND g.ug_ativo = 1
            AND g.ug_substatus IN ('11', '9')
        ";


    $con = ConnectionPDO::getConnection();
    $pdo = $con->getLink();

    $stmt = $pdo->prepare($sql);
    $stmt->execute(array($login, $senhaCrip));
    $fetch = $stmt->fetch(PDO::FETCH_ASSOC);

    $user = $fetch;

    if (empty($user)) {

        $msg = "Login ou senha inválidos.\n";

        $linha = "1[" . date('Y-m-d H:i:s') . "] [$login] $msg" . PHP_EOL;
        file_put_contents('/www/log/log_login.txt', $linha, FILE_APPEND);

        //$pag = $server_url . $pag;
        $strRedirect = $server_url .
            "/creditos/login.php?msg=" .
            urlencode($msg) .
            "&login=" .
            urlencode($login);

        header("Location: $strRedirect");
        exit;
    } else {
        $usuario_operador = true;
    }

}

if ($usuario_operador) {
    $verificaBlock = obterUsuarioBloqueado($user['pdv_id']);
    if($verificaBlock != null){
        $msg = utf8_decode($verificaBlock['motivo']) . " Seu PDV está bloqueado.";
        $linha = "1[" . date('Y-m-d H:i:s') . "] [$login] $msg" . PHP_EOL;
        file_put_contents('/www/log/log_login.txt', $linha, FILE_APPEND);

        header("Location: ".EPREPAG_URL_HTTPS."/creditos/pagina_bloqueio.php?msg=" . urlencode($msg) . "&login=" . urlencode($login));
        exit;
    }
    if (temLogInconsistente($user['pdv_id'], $pdo)) {
        $msg = "(BLQ101) Os dados da sua conta estão inconsistentes.";
        $linha = "1[" . date('Y-m-d H:i:s') . "] [$login] $msg" . PHP_EOL;
        file_put_contents('/www/log/log_login.txt', $linha, FILE_APPEND);

        adicionarUsuarioBloqueado($user['pdv_id'], $msg);
        enviaEmailReport($msg, $user);

        $users = lerUsuariosBloqueados();

        header("Location: ".EPREPAG_URL_HTTPS."/creditos/pagina_bloqueio.php?msg=" . urlencode($msg) . "&login=" . urlencode($login));
        exit;
    }
    if(buscarUsuariosSemLog($pdo, $user['pdv_id'])) {
        $msg = "(BLQ102) Este PDV ainda não foi validado para acesso ao sistema.";
        $linha = "1[" . date('Y-m-d H:i:s') . "] [$login] $msg" . PHP_EOL;
        file_put_contents('/www/log/log_login.txt', $linha, FILE_APPEND);

        adicionarUsuarioBloqueado($user['pdv_id'], $msg);
        enviaEmailReport($msg, $user);

        header("Location: ".EPREPAG_URL_HTTPS."/creditos/pagina_bloqueio.php?msg=" . urlencode($msg) . "&login=" . urlencode($login));
        exit;
    }
    $_SESSION['id_do_usuario'] = $user['ugo_id'];
    $_SESSION['usuario_operador'] = $usuario_operador;
    $_SESSION['login_usuario'] = $login;
    $_SESSION['senha_usuario'] = $senha;
    $_SESSION['g-recaptcha-response'] = "valido";
    $_SESSION['pag'] = $pag;

    $sql = "SELECT ugo_chave_autenticador FROM dist_usuarios_games_operador WHERE ugo_id = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user['ugo_id']]);
    $auth = $stmt->fetch(PDO::FETCH_ASSOC);

    if (empty($auth['ugo_chave_autenticador'])) {
        header("Location: " . EPREPAG_URL_HTTPS . "/creditos/adicionar-autenticacao.php");
        exit;
    }
    $msg = "";
    if (checkDevice($user['ugo_id'], $pdo, true)) {
        //$msg = "Dispositivo já autenticado.";
        header("Location: " . EPREPAG_URL_HTTPS . "/creditos/loginEf2.php");
        exit;
    }

} else {
    $verificaBlock = obterUsuarioBloqueado($user['ug_id']);
    if($verificaBlock != null){
        $msg = utf8_decode($verificaBlock['motivo']) . " Seu PDV está bloqueado.";
        $linha = "1[" . date('Y-m-d H:i:s') . "] [$login] $msg" . PHP_EOL;
        file_put_contents('/www/log/log_login.txt', $linha, FILE_APPEND);

        header("Location: ".EPREPAG_URL_HTTPS."/creditos/pagina_bloqueio.php?msg=" . urlencode($msg) . "&login=" . urlencode($login));
        exit;
    }
    if (temLogInconsistente($user['ug_id'], $pdo)) {
        $msg = "(BLQ101) Os dados da sua conta estão inconsistentes.";
        $linha = "1[" . date('Y-m-d H:i:s') . "] [$login] $msg" . PHP_EOL;
        file_put_contents('/www/log/log_login.txt', $linha, FILE_APPEND);

        adicionarUsuarioBloqueado($user['ug_id'], $msg);
        enviaEmailReport($msg, $user);

        $users = lerUsuariosBloqueados();

        header("Location: ".EPREPAG_URL_HTTPS."/creditos/pagina_bloqueio.php?msg=" . urlencode($msg) . "&login=" . urlencode($login));
        exit;
    }
    if(buscarUsuariosSemLog($pdo, $user['ug_id'])) {
        $msg = "(BLQ102) Este PDV ainda não foi validado para acesso ao sistema.";
        $linha = "1[" . date('Y-m-d H:i:s') . "] [$login] $msg" . PHP_EOL;
        file_put_contents('/www/log/log_login.txt', $linha, FILE_APPEND);

        adicionarUsuarioBloqueado($user['ug_id'], $msg);
        enviaEmailReport($msg, $user);

        header("Location: ".EPREPAG_URL_HTTPS."/creditos/pagina_bloqueio.php?msg=" . urlencode($msg) . "&login=" . urlencode($login));
        exit;
    }
    $_SESSION['login_usuario'] = $login;
    $_SESSION['senha_usuario'] = $senha;
    $_SESSION['g-recaptcha-response'] = "valido";
    $_SESSION['pag'] = $pag;
    $_SESSION['id_do_usuario'] = $user['ug_id'];
    unset($_SESSION['usuario_operador']);

    $sql = "SELECT ug_chave_autenticador FROM dist_usuarios_games WHERE ug_id = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user['ug_id']]);
    $auth = $stmt->fetch(PDO::FETCH_ASSOC);

    if (empty($auth['ug_chave_autenticador'])) {
        header("Location: " . EPREPAG_URL_HTTPS . "/creditos/adicionar-autenticacao.php");
        exit;
    }
    $msg = "";
    if (checkDevice($user['ug_id'], $pdo, false)) {
        //$msg = "Dispositivo já autenticado.";
        header("Location: " . EPREPAG_URL_HTTPS . "/creditos/loginEf2.php");
        exit;
    }
}

?>
<div class="container txt-cinza bg-branco  p-bottom40">
    <?php
    if (isset($msg) && $msg != "") {
        ?>
        <div class="col-md-12 top20">
            <div class="alert alert-danger" role="alert">
                <span class="glyphicon glyphicon-exclamation-sign t0" aria-hidden="true"></span>
                <span class="sr-only">Erro:</span>
                <?php echo $msg; ?>
            </div>
        </div>
        <?php
    }
    ?>
    <div class="row top10">
        <div class="col-md-6 top10 col-sm-12 col-xs-12">
            <span class="glyphicon txt-azul-claro glyphicon-triangle-right graphycon-big pull-left"
                aria-hidden="true"></span>
            <strong class="pull-left">
                <h4 class="top20 txt-azul-claro">Autenticação de dois fatores</h4>
            </strong>
            <div class="alert-login">
                Digite o token disponível no seu app de autenticação. <?= $msg ?>
            </div>
            <form action="loginEf2.php" method="post">
                <div class="form-group top20 col-md-8 col-md-offset-4 col-sm-12 col-xs-12">

                    <div class="col-md-12">
                        <label for="token">Token</label>
                        <input class="form-control input-sm" style="max-width: 270px;" id="token" name="token"
                            autocomplete="off" type="text" value="">
                    </div>
                    <div class="col-md-12 top10" style="display: flex; align-items: center;">
                        <label style=" display: flex; align-items: center; margin-bottom: 0px;" for="salvarDispositivo">
                            <span style="font-size: 16px;"> </span>
                            Lembrar desse dispositivo
                        </label>
                        <input class="form-check-input"
                            style="margin-left: 10px; cursor: pointer; height: 16px; width: 16px; margin-top: 0px;"
                            type="checkbox" id="salvarDispositivo" name="salvarDispositivo" value="sim">
                    </div>
                </div>
                <div class="col-md-8 col-md-offset-4 form-group col-sm-12 col-xs-12">
                    <div class="col-md-12 fontsize-p" style="text-align: end;">
                        <p class="decoration-none txt-cinza"><em>Problemas com a autenticação?</em></p>
                        <a class="decoration-none txt-cinza" id="faca-cadastro" target="_blank" href="/"><em>Entre em
                                contato com o suporte.</em></a>
                    </div>
                </div>
                <div class="col-md-12 top10 form-group col-sm-12 col-xs-12">
                    <div class="col-md-6 col-md-offset-6 dislineblock">
                        <input id="" type="submit" class="pull-right btn btn-success" value="Login" /><br />
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php
    require_once RAIZ_DO_PROJETO . "class/business/BannerBO.class.php";

    $categoria = "OffLine";
    $posicao = "Login";

    $objBanner = new BannerBO();
    $banner = $objBanner->getBannersFromJson($posicao, $categoria);
    ?>
    <div id="background_banner" class="top20 hidden-sm hidden-xs">
        <?php
        if ($banner) {
            foreach ($banner as $b) {
                ?>
                <a href="<?php echo $b->link; ?>" class="banner p-8" id="<?php echo $b->id; ?>" target="_blank"><img
                        src="<?php echo $objBanner->urlLink . $b->imagem; ?>" title="<?php echo $b->titulo; ?>"></a>
                <?php
            }
            ?>
            <script>
                $(function () {
                    $(function () {
                        $(".banner").click(function () {
                            $.get("/ajax/pdv/clickBanner.php", { id: $(this).attr("id") });
                        });
                    });
                });
            </script>
            <?php
        }
        ?>
    </div>
</div>
</div>

<script src="https://www.google.com/recaptcha/api.js" async="" defer=""></script>
<script type="text/javascript" src="/js/buscalans.js"></script>
<script src="/js/valida.js"></script>

<?php
require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/footer.php";