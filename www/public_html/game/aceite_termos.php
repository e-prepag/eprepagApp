<?php require_once __DIR__ . '/../../includes/constantes_url.php'; ?>
<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
$request_uri = $_SERVER['REQUEST_URI'];
// Obtém o script principal chamado
$script_name = $_SERVER['SCRIPT_NAME'];
// Se a URI acessada não for exatamente igual ao script chamado, bloqueia o acesso
if ($request_uri !== $script_name) {
    http_response_code(403);
    die("Acesso negado.");
}
require_once "../../includes/constantes.php";
require_once DIR_CLASS . "gamer/controller/HeaderController.class.php";
require_once RAIZ_DO_PROJETO . 'consulta_cpf/config.inc.cpf.php';

//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
$https = 'http' . (($_SERVER['HTTPS'] == 'on') ? 's' : '');
$server_url = $https . '://' . (checkIP() ? $_SERVER['SERVER_NAME'] : EPREPAG_URL);

session_start();

$_SESSION['acessou_pag_termos'] = true;

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
    file_put_contents('/www/arquivos_gerados/logs/log_login.txt', $linha, FILE_APPEND);
    //$pag = $server_url . $pag;
    $strRedirect = $server_url .
        "/game/conta/login.php?msg=" .
        urlencode($msg) .
        "&login=" .
        urlencode($login);

    session_destroy();
    header("Location: $strRedirect");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['termos']) || !isset($_POST['termos_responsaveis'])) {
        $erros[] = "<p>Você deve concordar com os termos de uso e termos dos responsáveis.</p>";
    } else {

        $ipAdress = $_SERVER["HTTP_X_FORWARDED_FOR"] ?: $_SERVER["REMOTE_ADDR"] ?: "Desconhecido";
        
        if (isset($_POST["location"]) && !empty($_POST["location"])) {
            preg_match('/^Lat:\s*(-?\d+(\.\d+)?),\s*Lon:\s*(-?\d+(\.\d+)?)/', $_POST['location'], $matches);

            $lat = floatval($matches[1]);
            $lon = floatval($matches[3]);

            if ($lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) {
                $location_ip = consultarGeoIP($ipAdress);
                if ($location_ip) {
                    $location = "Lat: " . $location_ip['results']['latitude'] . ", " . "Lon: " . $location_ip['results']['longitude'];
                }
            } else {
                $location = $_POST["location"];
            }
        } else {
            $location_ip = consultarGeoIP($ipAdress);
            if ($location_ip) {
                $location = "Lat: " . $location_ip['results']['latitude'] . ", " . "Lon: " . $location_ip['results']['longitude'];
            }
        }

        if (!isset($location) || empty($location)) {
            $erros[] = "<p>Para seguir com a confirmação, precisamos da sua autorização para acessar sua localização. Essa informação nos ajuda a garantir mais segurança no processo. Sua geolocalização será usada somente para esse fim e protegida conforme a Lei Geral de Proteção de Dados (LGPD).</p>";
        } else {

            $device = $_POST["device"] . " | " . $_SERVER['HTTP_USER_AGENT'];
            $version = "v1 Termos Uso | v1 Termo Respons. ou pais";


            $usuarios_func = new UsuarioGames();
            $salvou = $usuarios_func->salvaAceiteTermosGamer($location, $device, $version, $ipAdress, $id_do_usuario);
            if ($salvou) {
                $_SESSION['acessou_pag_termos'] = false;
                Util::redirect("/game/");
            } else {
                $erros[] = "<p>Ocorreu um erro ao salvar os termos. Tente novamente novamente, se o erro persistir, entre em contato com o suporte.</p>";
            }
        }
    }
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

    .container-custom {
        width: 1150px;
    }

    @media (max-width: 1200px) {
        .container-custom {
            width: 100%;
        }
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

    $(document).ready(function() {

        new Promise((resolve, reject) =>
            navigator.geolocation.getCurrentPosition(resolve, reject, {
                timeout: 10000
            })
        ).catch((error) => {
            manipulaModal(1, msgLocationError, 'Erro');
            return null;
        });
        // Verifica se o checkbox está marcado
        $('#termos').change(function() {
            if ($(this).is(':checked')) {
                // Se estiver marcado, habilita o botão
                if ($('#termos_responsaveis').is(':checked')) {
                    $('button[type="submit"]').prop('disabled', false);
                }
            } else {
                // Se não estiver marcado, desabilita o botão
                $('button[type="submit"]').prop('disabled', true);
            }
        });
        $('#termos_responsaveis').change(function() {
            if ($(this).is(':checked')) {
                // Se estiver marcado, habilita o botão
                if ($('#termos').is(':checked')) {
                    $('button[type="submit"]').prop('disabled', false);
                }
            } else {
                // Se não estiver marcado, desabilita o botão
                $('button[type="submit"]').prop('disabled', true);
            }
        });

        $("#cadastro").submit(async function(e) {

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
                navigator.geolocation.getCurrentPosition(resolve, reject, {
                    timeout: 10000
                })
            ).catch((error) => {
            });

            let localizacao;

            if (!pos || !pos.coords || typeof pos.coords.latitude === 'undefined' || typeof pos.coords.longitude === 'undefined') {
                localizacao = "";
            }else{
                localizacao = `Lat: ${pos.coords.latitude}, Lon: ${pos.coords.longitude}`;
            }

            $("#location").val(localizacao);
            $("#device").val(`${dispositivo} | ${plataforma} | ${linguagem}`);

            this.submit();

        });
    });
</script>
<div class="container txt-cinza bg-branco container-custom p-bottom40">
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
                <form action="#" method="post" class="form1" id="cadastro">
                    <input type="hidden" name="location" id="location" value="" />
                    <input type="hidden" name="device" id="device" value="" />
                    <div class="col-md-12 top10 form-group col-sm-12 col-xs-12"
                        style="display: flex; flex-direction: column; margin-top: 30px; margin-bottom: 40px; align-items: start;">
                        <label for="termos" style="font-size: 14px; display: inline;"><span>Eu concordo com os Termos de
                                Uso do sistema
                                E-Prepag</span> <input type="checkbox" name="termos" id="termos"
                                style="height: 14px; width: auto; margin: 0px 0px 0px 2px; padding: 0px;" /></label>
                        <label for="termos_responsaveis" style="font-size: 14px; display: inline;"><span>Eu concordo com
                                os Termos de Responsabilidade para pais e responsáveis
                                E-Prepag</span> <input type="checkbox" name="termos_responsaveis"
                                id="termos_responsaveis"
                                style="height: 14px; width: auto; margin: 0px 0px 0px 2px; padding: 0px;" /></label>

                        <div class="dislineblock">
                            <button style="font-weight: bold; margin-top: 12px;" type="submit" disabled
                                class="pull-right btn btn-success" />Continuar</button>
                        </div>
                    </div>
                    <div class="col-md-12 fontsize-p" style="text-align: start;">
                        <p class="decoration-none txt-preto" style="text-align: justify;">Precisamos da sua autorização para acessar sua localização. Essa informação nos ajuda a garantir mais segurança no processo. Sua geolocalização será usada somente para esse fim e protegida conforme a Lei Geral de Proteção de Dados (LGPD).</p>
                        <p class="decoration-none txt-cinza"><em>Algum problema?</em></p>
                        <a id="faca-cadastro" target="_blank" href="/game/suporte.php"><em>Entre em
                                contato com o suporte.</em></a>
                    </div>
                </form>

                <div class="col-md-8 form-group col-sm-12 col-xs-12 col-md-offset-1 instrucoes">

                    <h5>Termos de uso:</h5>
                    <?php echo "<textarea class='contrato' rows='10' readonly style='width: 100%; font-size: 13px; background-color: #00000005; text-align: justify; padding: 10px;'>";
                    echo $termosDeUso;
                    echo "</textarea>"; ?>
                    <h5>Termo de Responsabilidade para pais e responsáveis:</h5>
                    <textarea class='contrato' rows='4' readonly
                        style='width: 100%; font-size: 13px; background-color: #00000005; text-align: justify; padding: 10px;'>
Os usuários entre 12 e 18 anos devem certificar-se de ter lido o Termos e Condições de uso da Plataforma E-prepag, juntamente com seus pais ou responsáveis e que todo seu conteúdo tenha sido entendido e aprovado.
                    </textarea>

                </div>
            </div>
        </div>
    </div>
</div>
<?php
require_once RAIZ_DO_PROJETO . "public_html/game/includes/footer.php";
