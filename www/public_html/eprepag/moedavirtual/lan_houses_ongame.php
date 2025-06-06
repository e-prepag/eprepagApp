<?php require_once __DIR__ . '/../../../includes/constantes_url.php'; ?>
<?php
$withLogo = false;
if(!empty($_SERVER['HTTP_REFERER'])){
    $url_referer = parse_url($_SERVER['HTTP_REFERER']);
    $withLogo = ($url_referer['path'] == '/eprepag/revendedores/index.php');
}
$https = 'http' . (($_SERVER['HTTPS']=='on') ? 's' : '');

//Id do Publisher
$opr_codigo = 13;

//Id do GoCASH
$id_gocash = 1;

require_once "../../../includes/constantes.php";
require_once  DIR_INCS . "main.php";
require_once  DIR_INCS . "pdv/main.php";
require_once  DIR_INCS . "configIP.php";
require_once  DIR_INCS . "functions_captcha.php";

$need_key_maps = (checkIP())?"sensor=false":"key=AIzaSyA25PAcZMc6toew3UDW1HwG8wve00r8hb4";
$server_url = $https . '://' . (checkIP() ? $_SERVER['SERVER_NAME'] : '' . EPREPAG_URL . '');
session_start();
//Conectando com PDO para execução da QUERY
$con = ConnectionPDO::getConnection();
$pdo = $con->getLink();
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
$valorRequestCidade = isset($_POST['cidade']) ? filter_var(trim(str_replace("'", "",$_POST['cidade'])),FILTER_SANITIZE_STRING) : '';
$_POST['estado'] = filter_var($_POST['estado'], FILTER_SANITIZE_STRING);
// Deixa o drop down nos valores que estavam selecionados antes do reload.
if ((isset($_REQUEST['cidade'])) and ( isset($_REQUEST['bairro']))) {
    $SQLBairro = "
				SELECT 
					ug_bairro
				FROM (

					(SELECT ug_bairro
					FROM dist_usuarios_games
					WHERE replace(ug_cidade, '\'', '') = :ug_cidade
						AND ug_estado = :ug_estado
						AND ug_ativo = 1
						AND ug_status = 1
						AND ug_coord_lat != 0
						AND ug_coord_lng != 0
					)
                                        
				UNION ALL

					(SELECT us_bairro AS ug_bairro
					FROM dist_usuarios_stores_cartoes
					WHERE replace(us_cidade, '\'', '') = :us_cidade
						AND us_estado = :us_estado
						AND us_coord_lat != 0
						AND us_coord_lng != 0
                                                AND us_id IN (
                                                    select us_id from classificacao_mapas cm
                                                            INNER JOIN classificacao_mapas_pdv cmp ON cm.cm_id = cmp.cm_id
                                                    WHERE opr_codigo = :opr_codigo
                                                            AND cm_status = 1
                                                )
					)
                                                     
                                UNION ALL

                                        (SELECT us_bairro AS ug_bairro
					FROM dist_usuarios_stores_cartoes
                                        WHERE replace(us_cidade, '\'', '') = :us_cidade2
						AND us_estado = :us_estado2
                                                AND us_coord_lat != 0
                                                AND us_coord_lng != 0
                                                AND us_id IN (
                                                                select us_id from classificacao_mapas cm
                                                                        INNER JOIN classificacao_mapas_pdv cmp ON cm.cm_id = cmp.cm_id
                                                                WHERE cm.cm_id = :idGoCash
                                                                        AND cm_status = 1
                                                             )
                                        )
				) as locais
				GROUP BY ug_bairro 
				ORDER BY ug_bairro
				";
    $Estado = trim($_POST['estado']);
    $stmt = $pdo->prepare($SQLBairro);
    $stmt->bindParam(':ug_cidade', $valorRequestCidade, PDO::PARAM_STR);
    $stmt->bindParam(':ug_estado', $Estado, PDO::PARAM_STR);
    $stmt->bindParam(':us_cidade', $valorRequestCidade, PDO::PARAM_STR);
    $stmt->bindParam(':us_estado', $Estado, PDO::PARAM_STR);
    $stmt->bindParam(':us_cidade2', $valorRequestCidade, PDO::PARAM_STR);
    $stmt->bindParam(':us_estado2', $Estado, PDO::PARAM_STR);
    $stmt->bindParam(':opr_codigo', $opr_codigo, PDO::PARAM_STR);
    $stmt->bindParam(':idGoCash', $id_gocash, PDO::PARAM_INT);
    $stmt->execute();
    $ResultadoBairro = $stmt->fetchAll(PDO::FETCH_ASSOC);    
}

// Query que cria o drop drown das cidades
if ((isset($_REQUEST['estado'])) and ( isset($_REQUEST['cidade']))) {
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
				WHERE opr_codigo = :opr_codigo
					AND cm_status = 1
			     )
		)
                       
        UNION ALL

                (SELECT 
			us_cidade AS ug_cidade
                FROM dist_usuarios_stores_cartoes
                WHERE us_estado = :us_estado2
                        AND  us_coord_lat != 0
                        AND us_coord_lng != 0
                        AND us_id IN (
                                        select us_id from classificacao_mapas cm
                                                INNER JOIN classificacao_mapas_pdv cmp ON cm.cm_id = cmp.cm_id
                                        WHERE cm.cm_id = :idGoCash
                                                AND cm_status = 1
                                     )
                )
	) as locais
	GROUP BY ug_cidade
	ORDER BY ug_cidade
	";
    //echo "SQLCidade: $SQLCidade<br>";
    //die("Stop");
    $Estado = trim($_POST['estado']);
    $stmt = $pdo->prepare($SQLCidade);
    $stmt->bindParam(':ug_estado', $Estado, PDO::PARAM_STR);
    $stmt->bindParam(':us_estado', $Estado, PDO::PARAM_STR);
    $stmt->bindParam(':us_estado2', $Estado, PDO::PARAM_STR);
    $stmt->bindParam(':opr_codigo', $opr_codigo, PDO::PARAM_STR);
    $stmt->bindParam(':idGoCash', $id_gocash, PDO::PARAM_INT);
    $stmt->execute();
    $ResultadoCidade = $stmt->fetchAll(PDO::FETCH_ASSOC);
}//end if ((isset($_REQUEST['estado'])) and (isset($_REQUEST['cidade'])))

// Vetor que cria o drop drown dos estados
$Resultadoestado = $SIGLA_ESTADOS;

?>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
        <?php
            include DIR_INCS . "meta.php";
        ?>
        <title>E-Prepag - Créditos para games online</title>
        <link href="/includes/bootstrap/css/bootstrap.min_new.css" rel="stylesheet" type="text/css" />
        <link href="/eprepag/incs/styles_new.css" rel="stylesheet" type="text/css" />
        <script language="javascript" src="/js/jquery.js"></script>
        <script type="text/javascript" src="/js/jquery.js"></script>
        <script type="text/javascript" src="/includes/bootstrap/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="/js/jqueryui/js/jquery-ui-1.9.2.custom.min.js"></script>
        <script type="text/javascript" src="/js/scripts.js"></script>
        <script type="text/javascript" src="/js/scripts_dropdown.js"></script>
        <script type="text/javascript" src="/js/modalwaitingfor.js"></script>   
        <script type="text/javascript" src="/js/jquery-1.11.3.min.js"></script>
        <script>
            $(document).ready(function(){
                $(document).keypress(function(e) {
                    if(e.which == 13 ) {
                        $('#bt_procurar').click();
                        e.preventDefault();
                        return false;
                    }
                });
            });
        </script>
        <?php 
        require_once DIR_INCS . "functions.php";
        echo modal_includes();
// Variabeis para o banner
        $varRoot = "../../eprepag/revendedores";
        $Tiposup = " AND ((tiposup=0) OR (tiposup=1)) ";
        $Path = "../";

// include com as funçoes do banner
        require_once RAIZ_DO_PROJETO. "public_html/eprepag/incs/inc_bannersuperior.php";
        ?>
        <link type="text/css" href="/eprepag/moedavirtual/css/style.css" rel="stylesheet" media="all" />
        <script type="text/javascript" src="<?php echo $https; ?>://maps.google.com/maps/api/js?<?php echo $need_key_maps; ?>"></script>
        <script type="text/javascript">
            
            $(".fancybox").fancybox({
                href : 'iframe_problemas_lan_houses_cartao.php',
                type: 'iframe',
                width: 300,
                autoScale:false,
                scrolling : 'no'
            });

            function ValidaForm() {
                waitingDialog.show('Por favor, aguarde...',{dialogSize: 'sm'});
                setTimeout(function(){
                    if($("#estado").val() == ""){
                        waitingDialog.hide();
                        $("#msg_validacao").html('<font color="#FF1F00"><b>Selecione um estado.</b></font>');
                    }else if ($("#cidade").val() == ""){
                        waitingDialog.hide();
                        $("#msg_validacao").html('<font color="#FF1F00"><b>Selecione uma cidade.</b></font>');
                    }else if ($("#bairro").val() == ""){
                        waitingDialog.hide();
                        $("#msg_validacao").html('<font color="#FF1F00"><b>Selecione um bairro.</b></font>');
                    }else{
                        $("#msg_validacao").html('');
                        MostraLANs();
                    }
                }, 500);
            }
            
            function monta_captcha(){
                document.form_lanHouses_filtros.verificationCode.value = "";

                $.ajax({
                    type: "POST",
                    url: "/creditos/ajax/ajax_captcha.php",
                    success: function(html){
                        $("#td_captcha").html(html);
                    },
                    error: function(){
                    $("#td_captcha").html("ERRO");
                    }
                });
            }

            function MostraCidade() {
                if($("#estado").val() != "")
                    waitingDialog.show('Buscando cidades...',{dialogSize: 'sm'});
                setTimeout(function(){
                    if ($("#estado").val() != ""){
                        estado = document.form_lanHouses_filtros.estado.value;
                        $.ajax({
                            type: "POST",
                            url: "lan_house_select_cidade.php",
                            data: "estado=" + estado,
                            beforeSend: function(){
                                $("#SelCidade").html("&nbsp;&nbsp;&nbsp;Cidade: <select DISABLED><option>Buscando Cidade</option></select>");
                            },
                            success: function(html){
                                waitingDialog.hide();
                                $("#SelCidade").html(html);
                            },
                            error: function(){
                                waitingDialog.hide();
                                $("#SelCidade").html("ERRO");
                            }
                        });
                    }
                }, 500);
            }

            function MostraBairro() {
                if ($("#cidade").val() != "")
                    waitingDialog.show('Buscando bairros...',{dialogSize: 'sm'});
                setTimeout(function(){
                    if ($("#cidade").val() != ""){
                        estado = document.form_lanHouses_filtros.estado.value;
                        cidade = document.form_lanHouses_filtros.cidade.value;
                        $.ajax({
                            type: "POST",
                            url: "lan_house_select_bairro_only.php",
                            data: "cidade=" + cidade + "&estado=" + estado,
                            beforeSend: function(){
                                $("#SelBairro").html("&nbsp;&nbsp;&nbsp;Bairro: <select DISABLED><option>Buscando Bairro</option></select>");
                            },
                            success: function(html){
                                waitingDialog.hide();
                                $("#SelBairro").html(html);
                            },
                            error: function(){
                                waitingDialog.hide();
                                $("#SelBairro").html("ERRO");
                            }
                        });
                    }
                },500);
            }

            function MostraLANs() {
                if ($("#cidade").val() != ""){
                    estado = document.form_lanHouses_filtros.estado.value;
                    cidade = document.form_lanHouses_filtros.cidade.value;
                    bairro = document.form_lanHouses_filtros.bairro.value;
                    verificationCode = document.form_lanHouses_filtros.verificationCode.value;
                    $.ajax({
                        type: "POST",
                        url: "lan_houses_result_only.php",
                        data: "cidade=" + cidade + "&bairro=" + bairro + "&estado=" + estado + "&verificationCode=" + verificationCode,
                        beforeSend: function(){
                            $("#resultado").html("&nbsp;&nbsp;&nbsp;Fazendo a consulta!");
                        },
                        success: function(txt){
                            waitingDialog.hide();
                            $("#resultado").html(txt);
                            monta_captcha();
                        },
                        error: function(){
                            waitingDialog.hide();
                            $("#resultado").html("ERRO");
                        }
                    });
                }
            }

            function Reload() {
                document.form_lanHouses_filtros.verificationCode.value = "";
                document.form_lanHouses_filtros.action = "<?php echo $_SERVER['PHP_SELF']; ?>";
                document.form_lanHouses_filtros.submit();
                return false;
            }
        </script>
        <?php if ($withLogo) { ?>
            <style>
                #background_banner {
                    margin-top: 30px;
                }
                #background_banner a + a {
                    margin-left: 20px;
                    text-decoration: none;
                }
                #clickable_cadastro {
                    margin-top: 30px;
                    margin-left: 595px;
                    width: 190px;
                    height: 285px;
                    cursor: pointer;
                    cursor: hand;
                }
            </style>
        <?php } ?>
    <style>
    .fancybox-wrap iframe{height: 300px !important;}
    </style>
    </head>
    <body>
        <!-- inicio :: centro //-->
        <div id="" style="height:700px">
            <!-- inicio :: conteudo principal //-->
            <div id="img_help" class="styledCartao">
                <table border="0">
                    <tr>
			<td width="80"><nobr>&nbsp;</nobr></td>
                        <td class="font-10px" width="200"><nobr>&nbsp;</nobr></td>
                        <td align="center" style='cursor:pointer;cursor:hand;'><img src="/imagens/icone_eppLH.png" width="50" height="55" border="0" title="PINs E-Prepag" alt="PINs E-Prepag" onClick="$(function(){ showMessage('PINs E-Prepag: São milhares de Lan Houses, lojas de games, de informáticas e vários outros tipos de comércio em todo o Brasil.'); });"></td>
<!--                        <td class="font-10px" width="30"><nobr>&nbsp;</nobr></td>
                        <td align="center" style='cursor:pointer;cursor:hand;'><img src="../imgs/icone_eppcard.png" width="50" height="55" border="0" title="Cards E-Prepag/Go Cash" alt="Cards E-Prepag/Go Cash" onClick="$(function(){ showMessage('Cards E-Prepag/Go Cash: Você encontra os gift cards em livrarias, lojas de departamento, lojas de games, supermercados, e outras redes de varejo.'); });"></td>-->
                        <td class="font-10px" width="30"><nobr>&nbsp;</nobr></td>
                        <td align="center" style='cursor:pointer;cursor:hand;'><img src="/imagens/icone_cardsongame_i_.png" width="50" height="55" border="0" title="Cards Ongame" alt="Cards Ongame" onClick="$(function(){ showMessage('Cards Ongame: Você encontra os cards da Ongame no valor de R$ 25 e R$49 em livrarias, lojas de games, supermercados, lojas de departamento, etc.'); });"></td>
                    </tr>
                </table>
            </div>
            <form name="form_lanHouses_filtros" id="form_lanHouses_filtros" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                <input type="hidden" name="escolha" value="Cidade">
                    <input name="contactForm" type="hidden" id="contactForm" value="Send" />
                    <table class="bgcolor-table" width="600px" border="0" cellpadding="1" cellspacing="1">
                        <tr>
                            <td>
                                <table width="785px" border="0" cellpadding="0" cellspacing="0" bgcolor="#F4F3F0">
                                    <tr>
                                        <td align="left">
                                            <div id="msg_validacao"></div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="font-10px">
                                            <div id="pontos_buscaWGB">
                                                <div id="ajuste">
                                                    <div id="SelEstado" name="SelEstado">
                                                        Estado:&nbsp;
                                                        <select name="estado" id="estado" onChange='MostraCidade();'>
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
                                                    </div>
                                                    <div class="espaco">&nbsp;</div>
                                                    <div id="SelCidade" name="SelCidade">
                                                        Cidade:&nbsp;
                                                        <?php
                                                        if ($ResultadoCidade) {
                                                            echo '<select name="cidade" id="cidade"  onChange="MostraBairro();">';
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
                                                            <select name="bairro" id="bairro" DISABLED>
                                                                <option>Selecione um Estado</option>		
                                                            </select>
                                                            <?php
                                                        }
                                                        ?>
                                                    </div>
                                                    <div class="espaco">&nbsp;</div>
                                                    <div name="SelBairro" id="SelBairro">
                                                        Bairro:&nbsp;&nbsp;
                                                        <?php
                                                        if ($ResultadoBairro) {
                                                            echo '<select name="bairro" id="bairro">';
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
                                                            <select name="bairro" id="bairro" DISABLED>
                                                                <option>Selecione uma Cidade</option>		
                                                            </select>
                                                            <?php
                                                        }
                                                        ?>
                                                    </div>
                                                    <br>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <table class="bgcolor-table" width="785px" border="0" cellpadding="0" cellspacing="0">
                                    <tr height="75px">
                                        <td align="center" width="15%" id="td_captcha">
                                            <?php
                                            $randomcode = generateRandomCode();
                                            $randomcode_translated = translateCode($randomcode);
                                            ?>
                                            <img src="/includes/captcha/CaptchaImage.php?uid=<?php echo $randomcode_translated; ?>" title="Verify Code" vspace="2" />
                                        </td>
                                        <td width="15%">
                                            <input name="verificationCode" type="text" id="verificationCode" size="5" /><br>
                                                <nobr><a class="estiloSpan font-10px" href="javascript:monta_captcha();">Gerar outro código</a></nobr>
                                        </td>
                                        <td width="70%">
                                            <a onClick="ValidaForm()" id="bt_procurar" href="#" style="padding-top: 21px; position: absolute;"><img src="<?php echo $server_url;?>/eprepag/imgs/botao-procurar.png" width="131" height="33" border="0" /></a>
                                            <span style="float: right;font-size: 10px;padding-top: 60px; margin-right: 10px;"><a class="fancybox"  style="" >Avise-nos</a> sobre problemas no Ponto de Venda.</span>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <table class="bgcolor-table" width="785px" border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td>
                                            <!-- inicio :: resultado //-->	
                                            <div name="resultado" id="resultado"></div>
                                            <!-- fim :: resultado //-->	
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
            </form>	
            <?php if ($withLogo) { ?>
                <div id="background_banner">
                    <a href="http://br.leagueoflegends.com/pt/pagamentos#map" target="__blank"><img src="/eprepag/moedavirtual/imgs/banner_lol_ekko.jpg" /></a>
                    <a href="<?= EPREPAG_URL_HTTPS_COM ?>/Cartao-Xbox-Live-Gold-na-Eprepag" target="__blank"><img src="/eprepag/moedavirtual/imgs/banner_xbox_card.jpg" /></a>
                    <a href="http://blog.e-prepag.com/seja-um-ponto-de-venda/" target="__blank"><img src="/eprepag/moedavirtual/imgs/banner_faca_parte.jpg" /></a>
                </div>

            <?php } ?>
        </div>
        <!-- fim :: conteudo principal //-->

        <br/>
        <br/>
        <br/>
        <br/>
        <br/>
        <script src="<?php echo $https; ?>://www.google-analytics.com/urchin.js" type="text/javascript"></script>
        <script type="text/javascript">
                    _uacct = "UA-1903237-3";
                    urchinTracker();
        </script>
    </body>
</html>