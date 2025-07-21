<?php require_once __DIR__ . '/../../includes/constantes_url.php'; ?>
<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);  // Exibe todos os tipos de erros

require_once "../../includes/constantes.php";
require_once RAIZ_DO_PROJETO . "class/pdv/controller/OffLineController.class.php";

$controller = new OfflineController;

require_once "includes/header-offline.php";

$https = 'http' . (($_SERVER['HTTPS'] == 'on') ? 's' : '');
$server_url = $https . '://' . (checkIP() ? $_SERVER['SERVER_NAME'] : '' . EPREPAG_URL . '');
session_start();

//Id do GoCASH
$id_gocash = 1;

// Vetor que cria o drop drown dos estados
$Resultadoestado = $SIGLA_ESTADOS;

$site_key_v3 = getenv("AMBIENTE") == "HOMOLOGACAO" ? "6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI" : getenv("RECAPTCHA_SITE_KEY_V3");
$site_key = getenv("AMBIENTE") == "HOMOLOGACAO" ? "6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI" : getenv("RECAPTCHA_SITE_KEY");
?>
<div class="container txt-cinza bg-branco  p-bottom40">
    <?php
    if (isset($msg) && $msg != "") {
        ?>
        <div class="col-md-12 top20">
            <div class="alert alert-danger" role="alert">
                <span class="glyphicon glyphicon-exclamation-sign t0" aria-hidden="true"></span>
                <span class="sr-only">Erro:</span>
                <?php echo htmlspecialchars(utf8_decode($msg)); ?>
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
                <h4 class="top20 txt-azul-claro">acesso para pontos de venda</h4>
            </strong>
            <div class="alert-login">
                O acesso para conta principal deverá ser feito somente com o login cadastrado.
            </div>
            <form action="login_auth.php" method="post">
                <div class="form-group top20 col-md-8 col-md-offset-4 col-sm-12 col-xs-12">
                    <div class="col-md-3 col-md-offset-1">
                        <label for="login">Login:</label>
                    </div>
                    <div class="col-md-8">
                        <input class="form-control input-sm" id="login" onpaste="return false;" name="login"
                            autocomplete="off" type="text" value="">
                    </div>
                </div>
                <div class="col-md-8 col-md-offset-4 form-group col-sm-12 col-xs-12">
                    <div class="col-md-3 col-md-offset-1">
                        <label for="senha">Senha:</label>
                    </div>
                    <div class="col-md-8">
                        <input class="form-control input-sm" id="senha" name="senha" type="password" value="">
                    </div>
                    <div class="col-md-12 fontsize-p" style="text-align: end;">
                        <div style="padding: 10px 0 15px 0;">
                            <div class="g-recaptcha" data-sitekey="<?= $site_key ?>"></div>
                        </div>
                        <a class="decoration-none txt-cinza"
                            href="<?= EPREPAG_URL_HTTPS ?>/creditos/esqueci-minha-senha/index.php?redirected=true&origemUsuario=pdv"><em>Esqueci
                                minha senha</em></a>
                    </div>
                    <div class="col-md-8 col-md-offset-6 fontsize-p">
                        <a class="decoration-none txt-cinza" id="faca-cadastro" target="_blank"
                            href="/cadastro-de-ponto-de-venda.php"><em>Faça aqui seu cadastro</em></a>
                    </div>
                </div>
                <div class="col-md-12 top10 form-group col-sm-12 col-xs-12">
                    <div class="col-md-6 col-md-offset-6 dislineblock">
                        <input id="" type="submit" class="pull-right btn btn-success" value="Login" /><br />
                    </div>
                </div>
            </form>
        </div>
        <div class="col-md-6 borda-cinza top10 col-sm-12 col-xs-12">
            <div class="col-md-8 pull-right col-md-offset-2 borda-fina bg-cinza-claro">
                <div id="form_lanHouses_filtros">
                    <div class="form-group col-md-12 col-sm-12 col-xs-12">
                        <strong class="pull-left">
                            <h4 class="top20 txt-azul-claro"><strong>Encontre um ponto de venda</strong></h4>
                        </strong>
                    </div>
                    <div class="form-group col-md-12 col-sm-12 col-xs-12" style="display: flex; align-items: end;">
                        <div class="col-md-5  col-sm-6 col-xs-6" style="display: flex; gap: 10px;">
                            <img src="/imagens/icone_googlemaps_3.png" height="55" />
                            <img src="/imagens/icone_googlemaps_card.png" height="55" />
                        </div>
                        <div class="col-md-5  col-sm-4 col-xs-4">

                        </div>
                        <div class="col-md-2  col-sm-2 col-xs-2">
                            <a href="/busca-pdv.php" class=" btn btn-sm pull-right btn-success">Encontrar!</a>
                        </div>
                    </div>
                    <div class="clearfix"></div>
            </div>
</div>
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
<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 bg-info align-center borda-direita-basica p-bottom10">
        <h3>
            Quer ser um ponto de venda ?
            <a href="/cadastro-de-ponto-de-venda.php" class="txt-branco" target="_blank"><b><span
                        class="link-destaque">Cadastre-se</span></b></a>
            ou
            <a href="https://e-prepagpdv.com.br/" class="txt-branco" target="_blank"><b><span
                        class="link-destaque">saiba mais</span></b></a>.
        </h3>
    </div>
</div>
<script src="https://www.google.com/recaptcha/api.js" async="" defer=""></script>
<script src="/js/valida.js"></script>

<?php
require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/footer.php";