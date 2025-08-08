<?php require_once __DIR__ . '/../../includes/constantes_url.php'; ?>
<?php

require_once "../../includes/constantes.php";
require_once RAIZ_DO_PROJETO . "class/pdv/controller/OffLineController.class.php";


$controller = new OfflineController;

require_once "includes/header-offline.php";

$https = 'http' . (($_SERVER['HTTPS'] == 'on') ? 's' : '');
$server_url = $https . '://' . (checkIP() ? $_SERVER['SERVER_NAME'] : '' . EPREPAG_URL . '');

$id_do_usuario = $_SESSION['id_do_usuario'];

$sql = "select * from dist_usuarios_games where ug_id = ? and ug_ativo = 1";

$con = ConnectionPDO::getConnection();
$pdo = $con->getLink();

$stmt = $pdo->prepare($sql);
$stmt->execute(array($id_do_usuario));
$fetch = $stmt->fetch(PDO::FETCH_ASSOC);

$user = $fetch;

if (empty($user)) {
    $msg = "Usuario inválido.\n";
    $linha = "3[" . date('Y-m-d H:i:s') . "] [" . $_SESSION['login_usuario'] . "] $msg" . PHP_EOL;
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
if(!$_SESSION['precisa_termos']) {
    $msg = "Você já aceitou os termos de uso.";
    $linha = "3[" . date('Y-m-d H:i:s') . "] [" . $_SESSION['login_usuario'] . "] $msg" . PHP_EOL;
    file_put_contents('/www/log/log_login.txt', $linha, FILE_APPEND);
    $strRedirect = $server_url .
        "/creditos/login.php?msg=" .
        urlencode($msg) .
        "&login=" .
        urlencode($_SESSION['login_usuario']);

    header("Location: $strRedirect");
    exit;
}

?>
<style>
    .form1 {
        display: flex;
        justify-content: space-between;
        flex-direction: column;
    }

    .instrucoes {
        font-family: system-ui, sans-serif;
        color: #333;
    }

    .div-principal {
        display: flex;
        flex-direction: row;
        justify-content: stretch;
    }

    @media (max-width: 768px) {
        .div-principal {
            flex-direction: column;
            /* empilha os itens */
        }
    }
</style>
<script type="text/javascript">

    const msgLocationError = "Para seguir com a confirmação, precisamos da sua autorização para acessar sua localização. Essa informação nos ajuda a garantir mais segurança no processo. Sua geolocalização será usada somente para esse fim e protegida conforme a Lei Geral de Proteção de Dados (LGPD).";

    $(document).ready(function () {

        new Promise((resolve, reject) =>
            navigator.geolocation.getCurrentPosition(resolve, reject, { timeout: 10000 })
        ).catch((error) => {
            manipulaModal(1, msgLocationError, 'Erro');
            return null;
        });
        // Verifica se o checkbox está marcado
        $('#termos').change(function () {
            if ($(this).is(':checked')) {
                // Se estiver marcado, habilita o botão
                $('button[type="submit"]').prop('disabled', false);
            } else {
                // Se não estiver marcado, desabilita o botão
                $('button[type="submit"]').prop('disabled', true);
            }
        });

        $("#cadastro").submit(async function (e) {

            e.preventDefault();

            if (!$("#termos").is(":checked")) {
                manipulaModal(1, "Você deve concordar com os termos de uso e termos dos responsáveis.", 'Erro');
                return false;
            }

            const dispositivo = navigator.userAgent;
            const linguagem = navigator.language;
            const plataforma = navigator.platform;

            // Tenta obter localização (se o usuário permitir)
            const pos = await new Promise((resolve, reject) =>
                navigator.geolocation.getCurrentPosition(resolve, reject, { timeout: 10000 })
            ).catch((error) => {
                manipulaModal(1, msgLocationError, 'Erro');
                return null;
            });

            if (!pos || !pos.coords || typeof pos.coords.latitude === 'undefined' || typeof pos.coords.longitude === 'undefined') {
                manipulaModal(1, msgLocationError, 'Erro');
                return false;
            }

            const localizacao = `Lat: ${pos.coords.latitude}, Lon: ${pos.coords.longitude}`;

            if (localizacao === "") {
                console.log("Localização não obtida.");
                return false;
            }
            $("#location").val(localizacao);
            $("#device").val(`${dispositivo} | ${plataforma} | ${linguagem}`);

            this.submit();

        });
    });
</script>
<div class="container txt-cinza bg-branco  p-bottom40">
    <div class="row top10">
        <div class="top10 col-sm-12 col-xs-12">
            <span class="glyphicon txt-azul-claro glyphicon-triangle-right graphycon-big pull-left"
                aria-hidden="true"></span>
            <strong class="pull-left">
                <h4 class="top20 txt-azul-claro">Revisamos nossos Termos de Uso</h4>
            </strong>
            <div class="alert-login">
                Queremos continuar com você: Para continuar usando nossos serviços, você precisa ler e aceitar os
                termos!
            </div>
            <div class="div-principal">
                <form action="loginEf2.php" method="post" class="form1" id="cadastro">
                    <input type="hidden" name="location" id="location" value="" />
                    <input type="hidden" name="device" id="device" value="" />
                    <div class="col-md-12 top10 form-group col-sm-12 col-xs-12"
                        style="display: flex; flex-direction: column; margin-top: 30px; margin-bottom: 40px; align-items: start;">
                        <label for="termos" style="font-size: 14px; display: inline;"><span>Eu concordo com os Termos de
                                Uso do sistema
                                E-Prepag</span> <input type="checkbox" name="termos" id="termos"
                                style="height: 14px; width: auto; margin: 0px 0px 0px 2px; padding: 0px;" /></label>

                        <div class="dislineblock">
                            <button style="font-weight: bold; margin-top: 12px;" type="submit" disabled
                                class="pull-right btn btn-success" />Continuar</button>
                        </div>
                    </div>
                    <div class="col-md-12 fontsize-p" style="text-align: start;">
                        <p class="decoration-none txt-preto" style="text-align: justify;" >Precisamos da sua autorização para acessar sua localização. Essa informação nos ajuda a garantir mais segurança no processo. Sua geolocalização será usada somente para esse fim e protegida conforme a Lei Geral de Proteção de Dados (LGPD).</p>
                        <p class="decoration-none txt-cinza"><em>Algum problema?</em></p>
                        <a id="faca-cadastro" target="_blank" href="/game/suporte.php"><em>Entre em
                                contato com o suporte.</em></a>
                    </div>
                </form>

                <div class="col-md-8 form-group col-sm-12 col-xs-12 col-md-offset-1 instrucoes">

                    <h4>Termos:</h4>
                    <?php echo "<textarea class='contrato' rows='18' readonly style='width: 100%; font-size: 13px; background-color: #00000005; text-align: justify; padding: 10px;'>";
                    require_once __DIR__ . "/layout/contrato.php";
                    echo "</textarea>"; ?>
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

<script src="https://www.google.com/recaptcha/api.js" async="" defer=""></script>
<script type="text/javascript" src="/js/buscalans.js"></script>
<script src="/js/valida.js"></script>

<?php
require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/footer.php";