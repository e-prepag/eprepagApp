<?php require_once __DIR__ . '/../../includes/constantes_url.php'; ?>
<?php

require_once "../../includes/constantes.php";
require_once RAIZ_DO_PROJETO . "class/pdv/controller/OffLineController.class.php";
require '../libs/PHPGangsta/GoogleAuthenticator.php';


$controller = new OfflineController;

require_once "includes/header-offline.php";

$https = 'http' . (($_SERVER['HTTPS'] == 'on') ? 's' : '');
$server_url = $https . '://' . (checkIP() ? $_SERVER['SERVER_NAME'] : '' . EPREPAG_URL . '');
session_start();

//Id do GoCASH
$id_gocash = 1;

$pag = $_REQUEST["pag"];
$login = $_REQUEST["login"];
$senha = $_REQUEST["senha"];
$recaptcha = $_REQUEST["g-recaptcha-response"];


$objEncryption = new Encryption();
$original = trim($senha);
$senhaCrip = $objEncryption->encrypt(trim($senha));
$login = strtoupper(trim($login));


$sql = "select * from dist_usuarios_games where ug_login = ? and ug_senha = ?";

$con = ConnectionPDO::getConnection();
$pdo = $con->getLink();

$stmt = $pdo->prepare($sql);
$stmt->execute(array($login, $senhaCrip));
$fetch = $stmt->fetch(PDO::FETCH_ASSOC);

$user = $fetch;

if (empty($user)) {
    $msg = "Login ou senha inválidos.\n";
    //$pag = $server_url . $pag;
    $strRedirect = $server_url .
        "/creditos/login_teste.php?msg=" .
        urlencode($msg) .
        "&login=" .
        urlencode($login);

    header("Location: $strRedirect");
    exit;
}

$sql = "select * from log_erros_saldo where ug_id = ?";

$con = ConnectionPDO::getConnection();
$pdo = $con->getLink();

$stmt = $pdo->prepare($sql);
$stmt->execute(array($user['ug_id']));
$auth = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$auth) {

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
                Digite o token disponível no seu app de autenticação.
            </div>
            <form action="loginEf2.php" method="post">
                <div class="form-group top20 col-md-8 col-md-offset-4 col-sm-12 col-xs-12">
                    <div class="col-md-3 col-md-offset-1">
                        <label for="token">Token:</label>
                    </div>
                    <div class="col-md-8">
                        <input class="form-control input-sm" id="token" onpaste="return false;" name="token"
                            autocomplete="off" type="text" value="">
                        <input id="g-recaptcha-response" name="g-recaptcha-response" type="hidden"
                            value="<?= $recaptcha ?>">
                        <input id="senha" name="senha" type="hidden" value="<?= $senha ?>">
                        <input id="login" name="login" type="hidden" value="<?= $login ?>">
                        <input id="pag" name="pag" type="hidden" value="<?= $pag ?>">
                        <input id="login_id" name="login_id" type="hidden" value="<?= $user['ug_id'] ?>">
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