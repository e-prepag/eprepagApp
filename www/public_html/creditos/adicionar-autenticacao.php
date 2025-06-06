<?php require_once __DIR__ . '/../../includes/constantes_url.php'; ?>
<?php

require_once "../../includes/constantes.php";
require_once RAIZ_DO_PROJETO . "class/pdv/controller/OffLineController.class.php";
require '../libs/PHPGangsta/GoogleAuthenticator.php';


$controller = new OfflineController;

require_once "includes/header-offline.php";

$https = 'http' . (($_SERVER['HTTPS'] == 'on') ? 's' : '');
$server_url = $https . '://' . (checkIP() ? $_SERVER['SERVER_NAME'] : '' . EPREPAG_URL . '');

$id_do_usuario = $_SESSION['id_do_usuario'];

if ($_SESSION['usuario_operador']) {
    $sql = "select * from dist_usuarios_games_operador where ugo_id = ? and ugo_ativo = 1";

    $con = ConnectionPDO::getConnection();
    $pdo = $con->getLink();

    $stmt = $pdo->prepare($sql);
    $stmt->execute(array($id_do_usuario));
    $fetch = $stmt->fetch(PDO::FETCH_ASSOC);

    $user = $fetch;

    if (empty($user)) {
        $msg = "Usuario inv�lido.\n";
        $linha = "3[" . date('Y-m-d H:i:s') . "] [".$_SESSION['login_usuario']."] $msg" . PHP_EOL;
        file_put_contents('/www/log/log_login.txt', $linha, FILE_APPEND);
        //$pag = $server_url . $pag;
        $strRedirect = $server_url .
            "/creditos/login.php?msg=" .
            urlencode($msg) .
            "&login=" .
            urlencode($_SESSION['login_usuario']);

        header("Location: $strRedirect");
        exit;
    }

    $sql = "SELECT ugo_acesso_sem_aut FROM dist_usuarios_games_operador WHERE ugo_id = ?";
    $con = ConnectionPDO::getConnection();
    $pdo = $con->getLink();

    $stmt = $pdo->prepare($sql);
    $stmt->execute(array($user['ugo_id']));
    $authData = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    $sql = "select * from dist_usuarios_games where ug_id = ? and ug_ativo = 1";

    $con = ConnectionPDO::getConnection();
    $pdo = $con->getLink();

    $stmt = $pdo->prepare($sql);
    $stmt->execute(array($id_do_usuario));
    $fetch = $stmt->fetch(PDO::FETCH_ASSOC);

    $user = $fetch;

    if (empty($user)) {
        $msg = "Usuario inv�lido.\n";
        $linha = "3[" . date('Y-m-d H:i:s') . "] [".$_SESSION['login_usuario']."] $msg" . PHP_EOL;
        file_put_contents('/www/log/log_login.txt', $linha, FILE_APPEND);
        //$pag = $server_url . $pag;
        $strRedirect = $server_url .
            "/creditos/login.php?msg=" .
            urlencode($msg) .
            "&login=" .
            urlencode($_SESSION['login_usuario']);

        header("Location: $strRedirect");
        exit;
    }

    $sql = "SELECT ug_acesso_sem_aut FROM dist_usuarios_games WHERE ug_id = ?";
    $con = ConnectionPDO::getConnection();
    $pdo = $con->getLink();

    $stmt = $pdo->prepare($sql);
    $stmt->execute(array($user['ug_id']));
    $authData = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($authData) {
    $dataUltimoAcesso = new DateTime($authData['ug_acesso_sem_aut']);
    $dataHoje = new DateTime();

    $prazoMaximo = 28;

    // Calcula a diferen�a de dias
    $diasPassados = $dataUltimoAcesso->diff($dataHoje)->days;
    $diasRestantes = $prazoMaximo - $diasPassados;

    if ($diasRestantes > 0) {
        $mensagemAuth = "Voc� ainda n�o ativou a autentica��o de dois fatores. Deseja configurar agora? 
              Voc� tem <strong>{$diasRestantes} dias</strong> para ativ�-la antes que se torne obrigat�ria.";
    } else {
        $mensagemAuth = "O prazo para ativar a autentica��o de dois fatores expirou. 
              Para continuar acessando sua conta, � necess�rio configur�-la agora.";
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
        <div class="top10 col-sm-12 col-xs-12">
            <span class="glyphicon txt-azul-claro glyphicon-triangle-right graphycon-big pull-left"
                aria-hidden="true"></span>
            <strong class="pull-left">
                <h4 class="top20 txt-azul-claro">Autentica��o de dois fatores</h4>
            </strong>
            <div class="alert-login">
                <?= $mensagemAuth ?>
            </div>
            <form action="loginEf2.php" method="post">
                <div class="col-md-12 top10 form-group col-sm-12 col-xs-12"
                    style="display: flex; flex-direction: row; margin-top: 30px; margin-bottom: 40px;">
                    <div class="dislineblock" style="margin-right: 25px;">
                        <button style="font-weight: bold; font-style: italic;" type="submit"
                            class="pull-right btn btn-success" value="true" name="tem_auth">Sim</button>
                    </div>
                    <div class="dislineblock">
                        <button style="font-weight: bold; font-style: italic;" type="submit"
                            class="pull-right btn btn-info" value="false" name="tem_auth" />N�o</button>
                    </div>
                </div>

                <div class="col-md-8 col-md-offset-4 form-group col-sm-12 col-xs-12">
                    <div class="col-md-12 fontsize-p" style="text-align: end;">
                        <p class="decoration-none txt-cinza"><em>Problemas com a autentica��o?</em></p>
                        <a class="decoration-none txt-cinza" id="faca-cadastro" target="_blank" href="/"><em>Entre em
                                contato com o suporte.</em></a>
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