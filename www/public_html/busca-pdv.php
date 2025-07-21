<?php require_once __DIR__ . '/../includes/constantes_url.php'; ?>
<?php
// error_reporting(E_ALL); 
// ini_set("display_errors", 1); 
$request_uri = $_SERVER['REQUEST_URI'];
// Obtém o script principal chamado
$script_name = $_SERVER['SCRIPT_NAME'];
// Se a URI acessada não for exatamente igual ao script chamado, bloqueia o acesso
if ($request_uri !== $script_name) {
    http_response_code(403);
    die("Acesso negado.");
}
//error_reporting(E_ALL); 
//ini_set("display_errors", 1);
header("Content-Type: text/html; charset=ISO-8859-1", true);
require_once "../includes/constantes.php";
require_once DIR_CLASS . "gamer/controller/HeaderController.class.php";
require_once DIR_INCS . "functions_captcha.php";
require_once "../includes/load_dotenv.php";

$controller = new HeaderController;
$controller->setHeader();

$https = 'http' . (($_SERVER['HTTPS'] == 'on') ? 's' : '');
$need_key_maps = (checkIP()) ? "sensor=false" : "key=AIzaSyA25PAcZMc6toew3UDW1HwG8wve00r8hb4";
$server_url = $https . '://' . (checkIP() ? $_SERVER['SERVER_NAME'] : '' . EPREPAG_URL . '');
session_start();

//Id do GoCASH
$id_gocash = 1;

//Conectando com PDO para execução da QUERY
$con = ConnectionPDO::getConnection();
$pdo = $con->getLink();
$recaptcha = $_POST['g-recaptcha-response'];

$msg_modal = false;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //$recaptcha = $_POST['g-recaptcha-response'];

    if (!empty($_POST["g-recaptcha-response"])) {
        $captcha_secret = getenv("AMBIENTE") == "HOMOLOGACAO" ? "6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe" : getenv("RECAPTCHA_SECRET_KEY_V3");

        $tokenInfo = ["secret" => $captcha_secret, "response" => $_POST["g-recaptcha-response"], "remoteip" => $_SERVER["REMOTE_ADDR"]];

        $recaptcha = curl_init();
        curl_setopt_array($recaptcha, [
            CURLOPT_URL => "https://www.google.com/recaptcha/api/siteverify",
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => http_build_query($tokenInfo)

        ]);
        $retorno = json_decode(curl_exec($recaptcha), true);
        curl_close($recaptcha);

        if ($retorno["success"] != true || (isset($retorno["error-codes"]) && !empty($retorno["error-codes"]))) {
            $erros[] = "<p>Processo invalidado por RECAPTCHA.</p>";
        }
    } else {
        $erros[] = "<p>Você deve realizar a verificação do RECAPTCHA para prosseguir.</p>";
    }
    if (!empty($erros)) {
        $msg_modal = true;
    }

    // Prosseguir com o restante do formulário...
}

// Deixa o drop down nos valores que estavam selecionados antes do reload.
if ((isset($_POST['cidade'])) and (isset($_POST['bairro']) and !empty($recaptcha))) {

    $valorRequestCidade = isset($_POST['cidade']) ? filter_var(trim(str_replace("'", "", $_POST['cidade'])), FILTER_SANITIZE_STRING) : '';
    $_POST['bairro'] = filter_var($_POST['bairro'], FILTER_SANITIZE_STRING);

    $SQLBairro = "
				SELECT 
					ug_bairro
				FROM (

					(SELECT ug_bairro
					FROM dist_usuarios_games
					WHERE 
                        trim(both ' ' from replace(ug_cidade, '''', '')) = :ug_cidade
						AND trim(both ' ' from ug_estado) = :ug_estado
						AND ug_ativo = 1
						AND ug_status = 1
						AND ug_coord_lat != 0
						AND ug_coord_lng != 0
					)
				UNION ALL

					(SELECT us_bairro AS ug_bairro
					FROM dist_usuarios_stores_cartoes
					WHERE trim(both ' ' from replace(us_cidade, '''', '')) = :us_cidade
						AND trim(both ' ' from us_estado) = :us_estado 
						AND us_coord_lat != 0
						AND us_coord_lng != 0
                                                AND us_id IN (
                                                    select us_id from classificacao_mapas cm
                                                            INNER JOIN classificacao_mapas_pdv cmp ON cm.cm_id = cmp.cm_id
                                                    WHERE cm.cm_id = :idGoCash	
                                                            AND cm_status = 1
                                                )
					)
                UNION ALL

					(SELECT trim(both ' ' from us_bairro) AS ug_bairro
					FROM dist_usuarios_stores_qiwi
					WHERE replace(us_cidade, '''', '') = :us_cidade
						AND us_estado = :us_estado
						AND us_coord_lat != 0
						AND us_coord_lng != 0
					)
				) as locais
				GROUP BY ug_bairro 
				ORDER BY ug_bairro
				";

    $stmt = $pdo->prepare($SQLBairro);
    $stmt->bindParam(':ug_cidade', $valorRequestCidade, PDO::PARAM_STR);
    $stmt->bindParam(':ug_estado', $_POST['estado'], PDO::PARAM_STR);
    $stmt->bindParam(':us_cidade', $valorRequestCidade, PDO::PARAM_STR);
    $stmt->bindParam(':us_estado', $_POST['estado'], PDO::PARAM_STR);
    $stmt->bindParam(':idGoCash', $id_gocash, PDO::PARAM_INT);
    $stmt->execute();
    $ResultadoBairro = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Query que cria o drop drown das cidades
if ((isset($_POST['estado'])) and (isset($_POST['cidade']) and !empty($recaptcha))) {

    $_POST['estado'] = filter_var($_POST['estado'], FILTER_SANITIZE_STRING);

    $SQLCidade = "
	SELECT 
		ug_cidade
	FROM (

		(SELECT 
			ug_cidade
		FROM dist_usuarios_games
		WHERE ug_ativo = 1
			AND ug_status = 1
			AND ug_estado = :ug_estado
			AND ug_coord_lat != 0
			AND ug_coord_lng != 0
		)
	UNION ALL
		(SELECT 
			us_cidade AS ug_cidade
		FROM dist_usuarios_stores_cartoes
		WHERE us_coord_lat != 0
			AND us_coord_lng != 0
			AND us_estado = :us_estado
                        AND us_id IN (
                            select us_id from classificacao_mapas cm
                                    INNER JOIN classificacao_mapas_pdv cmp ON cm.cm_id = cmp.cm_id
                            WHERE cm.cm_id = :idGoCash
                                    AND cm_status = 1
                        )
		)
    UNION ALL
		(SELECT 
            trim(both ' ' from us_cidade) AS ug_cidade
            FROM dist_usuarios_stores_qiwi
            WHERE us_coord_lat != 0
                AND us_coord_lng != 0
                AND us_estado = :us_estado
		)
	) as locais
	GROUP BY ug_cidade
	ORDER BY ug_cidade
	";
    //echo "SQLCidade: $SQLCidade<br>";
    //die("Stop");

    $stmt = $pdo->prepare($SQLCidade);
    $stmt->bindParam(':ug_estado', $_POST['estado'], PDO::PARAM_STR);
    $stmt->bindParam(':us_estado', $_POST['estado'], PDO::PARAM_STR);
    $stmt->bindParam(':idGoCash', $id_gocash, PDO::PARAM_INT);

    $stmt->execute();
    $ResultadoCidade = $stmt->fetchAll(PDO::FETCH_ASSOC);

    //    $ResultadoCidade = SQLexecuteQuery($SQLCidade);
}//end if ((isset($_POST['estado'])) and (isset($_POST['cidade'])))

// Vetor que cria o drop drown dos estados
$Resultadoestado = $SIGLA_ESTADOS;
?>
<div class="container txt-azul-claro bg-branco p-bottom40">
    <div class="row top20">
        <div class="col-md-12">
            <span class="glyphicon glyphicon-triangle-right graphycon-big color-blue pull-left"></span>
            <strong class="pull-left top15 color-blue font20">Busca de ponto de venda</strong>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8 ">
            <hr class="border-blue">
        </div>
    </div>
    <div class="col-md-12 top50 txt-cinza">
        <form name="form_lanHouses_filtros" id="form_lanHouses_filtros"
            action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method="post">
            <input type="hidden" name="escolha" value="Cidade">
            <input name="contactForm" type="hidden" id="contactForm" value="Send" />
            <div class="col-md-12">
                <div id="msg_validacao"></div>
            </div>
            <div class="col-md-6">
                <span class="col-md-3 top10">
                    Estado:
                </span>
                <span class="col-md-9 top10">
                    <select name="estado" class="form-control input-sm" id="estado" onChange='MostraCidade();'>
                        <option value="">&nbsp;UF&nbsp;</option>
                        <?php
                        // Gera os dados do drop down estado
                        foreach ($Resultadoestado as $value) {
                            echo '<option value="' . $value . '"';
                            if ($_POST['estado'] == $value) {
                                echo " SELECTED ";
                            }
                            echo ">" . $value . "</option>\n";
                        }
                        ?>
                    </select>
                </span>
                <span class="col-md-3 top10">
                    Cidade:
                </span>
                <span class="col-md-9 top10" id="SelCidade">
                    <?php
                    if ($ResultadoCidade) {
                        echo '<select name="cidade" id="cidade"  class="form-control input-sm" onChange="MostraBairro();">';

                        foreach ($ResultadoCidade as $RowCidade) {
                            echo '<option value="' . $RowCidade['ug_cidade'] . '"';
                            if ($_POST['cidade'] == $RowCidade['ug_cidade'] && !empty($RowCidade['ug_cidade'])) {
                                echo " SELECTED ";
                            }
                            echo '>' . $RowCidade['ug_cidade'] . '</option>';
                        }
                        echo '</select>';
                    } else {
                        ?>
                        <select name="cidade" class="form-control input-sm" id="cidade" DISABLED>
                            <option value="">Selecione um Estado</option>
                        </select>
                        <?php
                    }
                    ?>
                </span>
                <span class="col-md-3 top10">
                    Bairro:
                </span>
                <span class="col-md-9 top10" id="SelBairro">
                    <?php
                    if ($ResultadoBairro) {
                        echo '<select class="form-control input-sm" name="bairro" id="bairro">';
                        foreach ($ResultadoBairro as $RowBairro) {

                            echo '<option value="' . $RowBairro['ug_bairro'] . '"';
                            if ($_POST['bairro'] == $RowBairro['ug_bairro'] && !empty($RowBairro['ug_bairro'])) {
                                echo " SELECTED ";
                            }
                            echo '>' . $RowBairro['ug_bairro'] . '</option>';
                        }
                        echo '</select>';
                    } else {
                        ?>
                        <select class="form-control input-sm" name="bairro" id="bairro" DISABLED>
                            <option value="">Selecione uma Cidade</option>
                        </select>
                        <?php
                    }
                    ?>
                </span>
                <!-- <span class="col-md-4 col-md-offset-3 top10" id="span_captcha">
<?php
$site_key = getenv("AMBIENTE") == "HOMOLOGACAO" ? "6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI" : getenv("RECAPTCHA_SITE_KEY");
?>
                    <img width="110px" height="60px" class="pull-right" src="includes/captcha/CaptchaImage.php?uid=<?php echo $randomcode_translated; ?>" title="Verify Code" vspace="2" />
                </span> -->
                <input readonly name="verificationCode" class="form-control input-sm" type="hidden"
                    id="verificationCode" value="<?php if (isset($_POST['g-recaptcha-response']))
                        echo htmlspecialchars($_POST['g-recaptcha-response'], ENT_QUOTES, 'UTF-8'); ?>" size="5" /><br>
                <!-- <span class="col-md-5 top10">
                    <a class="estiloSpan font-10px" href="javascript:monta_captcha();">Gerar outro código</a>
                </span> -->
                <div class="g-recaptcha col-md-4 col-md-offset-3 text-center" data-sitekey="<?= $site_key ?>"
                    data-callback="onReCaptchaSuccess"></div>

                <div class="clearfix"></div>
                <span class="col-md-4 col-md-offset-3 text-center">
                    <a onClick="ValidaForm()" id="bt_procurar" href="#" class="btn top10 btn-success">Procurar</a>
                </span>
                <div class="col-md-5 top10">
                    <span class="fontsize-pp"><a class="fancybox" id="frameavise">Avise-nos</a> sobre problemas no Ponto
                        de Venda.</span>
                </div>
            </div>
            <div class="col-md-6">
                <img src="imagens/icone_eppLH.png" width="50" height="55" border="0" title="PINs E-Prepag"
                    alt="PINs E-Prepag" class="c-pointer" data-toggle="modal" data-target="#pimepp">
                <img src="imagens/icone_eppcard.png" width="50" height="55" border="0" title="Cards E-Prepag/Go Cash"
                    class="c-pointer espacamento-laterais-pequeno" alt="Cards E-Prepag/Go Cash" data-toggle="modal"
                    data-target="#pimeppcard">
                <img src="imagens/icone_qiwi.png" width="50" height="55" border="0" title="Rede Qiwi" alt="Rede Qiwi"
                    class="c-pointer espacamento-laterais-pequeno" data-toggle="modal" data-target="#pinqiwi">
                <a href="http://blog.e-prepag.com/pdvs-online-oficiais-e-prepag/"><img
                        src="imagens/botaopdvonline.png"></a>
                <div id="pimeppcard" class="modal fade" role="dialog">
                    <div class="modal-dialog">
                        <!-- Modal content-->
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title"><img src="<?php echo DIR_EPREPAG; ?>imagens/icone_eppcard.png"
                                        width="50" height="55" border="0" title="PINs E-Prepag" alt="PINs E-Prepag">
                                </h4>
                            </div>
                            <div class="modal-body">
                                <div class="alert alert-info" role="alert">
                                    <h5><span id="msg-modal">Cards E-Prepag/Go Cash: Você encontra os gift cards em
                                            livrarias, lojas de departamento, lojas de games, supermercados, e outras
                                            redes de varejo.</span></h5>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="pimepp" class="modal fade" role="dialog">
                    <div class="modal-dialog">
                        <!-- Modal content-->
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title"><img src="<?php echo DIR_EPREPAG; ?>imagens/icone_eppLH.png"
                                        width="50" height="55" border="0" title="PINs E-Prepag" alt="PINs E-Prepag">
                                </h4>
                            </div>
                            <div class="modal-body">
                                <div class="alert alert-info" role="alert">
                                    <h5><span id="msg-modal">PINs E-Prepag: São milhares de Lan Houses, lojas de games,
                                            de informáticas e vários outros tipos de comércio em todo o Brasil.</span>
                                    </h5>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="pinqiwi" class="modal fade" role="dialog">
                    <div class="modal-dialog">
                        <!-- Modal content-->
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title"><img src="<?php echo DIR_EPREPAG; ?>/imgs/icone_qiwi.png"
                                        width="50" height="55" border="0" title="PINs E-Prepag" alt="PINs E-Prepag">
                                </h4>
                            </div>
                            <div class="modal-body">
                                <div class="alert alert-info" role="alert">
                                    <h5><span id="msg-modal">Totens com PINs E-Prepag: São centenas de totens
                                            eletrônicos de auto-serviço, onde você pode comprar o E-Prepag Cash
                                            diretamente da máquina, e também alguns estabelecimentos comerciais da rede
                                            credenciada.</span></h5>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="row">
        <div name="resultado" class="col-md-12 top20" id="resultado"></div>
    </div>

</div>
</div>
<script type="text/javascript" src="js/scripts.js"></script>
<script type="text/javascript" src="js/scripts_dropdown.js"></script>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script>
    function onReCaptchaSuccess(token) {
        // Preenche o input com o token para ser enviado no FormData
        document.getElementById('verificationCode').value = token;
    }
</script>
<script type="text/javascript"
    src="<?php echo $https; ?>://maps.google.com/maps/api/js?<?php echo $need_key_maps; ?>"></script>
<script>
    $(document).ready(function () {
        $(document).keypress(function (e) {
            if (e.which == 13) {
                $('#bt_procurar').click();
                e.preventDefault();
                return false;
            }
        });
    }); 
</script>
<?php
/*require_once 'C:\Sites\E-Prepag\www\web\prepag2\incs\functions.php';
echo modal_includes();*/
?>
<link rel="stylesheet" href="js/fancybox/jquery.fancybox.css" type="text/css" />
<script src="js/fancybox/jquery.fancybox.js"></script>
<script type="text/javascript" src="js/buscalans.js"></script>
<script>
    $(".fancybox").fancybox({
        href: '/creditos/ajax/iframe-problemas-pdv.php',
        type: 'iframe',
        width: 500
    });
</script>
<style>
    .fancybox-outer,
    .fancybox-inner,
    .fancybox-image,
    .fancybox-wrap iframe,
    .fancybox-wrap object,
    .fancybox-nav,
    .fancybox-nav span,
    .fancybox-tmp {
        height: 380px !important;
    }
</style>
<script type="text/javascript">
    document.addEventListener("DOMContentLoaded", function () {
        if (<?= $msg_modal ?> == 1) {
            document.getElementById('error-text').innerHTML = "<?= implode($erros) ?>";
            $("#modal-load").modal();
        }
    });

</script>
<?php
if (isset($_POST['g-recaptcha-response']) && isset($_POST['cidade']) && isset($_POST['bairro'])) {
    echo "<script>ValidaForm();</script>";
}

require_once "game/includes/footer.php";