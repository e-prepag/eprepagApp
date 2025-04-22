<?php
header("Content-Type: text/html; charset=ISO-8859-1",true);
require_once "../../../includes/constantes.php";
require_once  DIR_INCS . "main.php";
require_once  DIR_INCS . "pdv/main.php";
require_once  DIR_INCS . "configIP.php";
require_once  DIR_INCS . "functions_captcha.php";
session_start();

//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

// Deixa o drop down nos valores que estavam selecionados antes do reload.
$valorRequestCidade = isset($_POST['cidade']) ? filter_var(trim(str_replace("'", "",$_POST['cidade'])),FILTER_SANITIZE_STRING) : '';
$valorRequestEstado = filter_var($_POST['estado'], FILTER_SANITIZE_STRING);
//Conectando com PDO para execução da QUERY
$con = ConnectionPDO::getConnection();
$pdo = $con->getLink();

$https = 'http' . (($_SERVER['HTTPS']=='on') ? 's' : '');
$need_key_maps = (checkIP())?"sensor=false":"key=AIzaSyA25PAcZMc6toew3UDW1HwG8wve00r8hb4";

if ((isset($_REQUEST['cidade'])) and (isset($_REQUEST['bairro']))){
	$SQLBairro = "
				SELECT ug_bairro
					FROM dist_usuarios_games
					WHERE replace(ug_cidade, '\'', '') = :ug_cidade
						AND ug_estado = :ug_estado
						AND ug_ativo = 1
						AND ug_status = 1
						AND ug_coord_lat != 0
						AND ug_coord_lng != 0
				GROUP BY ug_bairro 
				ORDER BY ug_bairro
				";
    
    $stmt = $pdo->prepare($SQLBairro);
    $stmt->bindParam(":ug_cidade", $valorRequestCidade,PDO::PARAM_STR);
    $stmt->bindParam(":ug_estado", $valorRequestEstado,PDO::PARAM_STR);
    $stmt->execute();
    $ResultadoBairro = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Query que cria o drop drown das cidades
if ((isset($_REQUEST['estado'])) and (isset($_REQUEST['cidade']))){
	$SQLCidade = "
		SELECT 
			ug_cidade
		FROM dist_usuarios_games
		WHERE ug_ativo = 1
			AND ug_status = 1
			AND ug_estado = :ug_estado
			AND ug_coord_lat != 0
			AND ug_coord_lng != 0
	GROUP BY ug_cidade
	ORDER BY ug_cidade
	";
	//echo "SQLCidade: $SQLCidade<br>";
    $valorRequestEstado = trim($valorRequestEstado);   
    $stmt = $pdo->prepare($SQLCidade);
    $stmt->bindParam(":ug_estado", $valorRequestEstado,PDO::PARAM_STR);
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
require_once DIR_INCS . "meta.php";
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
// Variabeis para o banner
$varRoot = "../../eprepag/revendedores";
$Tiposup = " AND ((tiposup=0) OR (tiposup=1)) ";
$Path = "../";

// include com as funçoes do banner
require_once RAIZ_DO_PROJETO. "public_html/eprepag/incs/inc_bannersuperior.php";
?>
<link type="text/css" href="/eprepag/moedavirtual/css/style.css" rel="stylesheet" media="all" />
<script type="text/javascript" src="<?php echo $https; ?>://maps.google.com/maps/api/js?<?php echo $need_key_maps; ?>"></script>
<?php
//include "date.php";
?>

<script type="text/javascript">

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
		document.form_lanHouses_filtros.action = "lan_houses_b2c.php";
		document.form_lanHouses_filtros.submit();		
		return false;
	}
</script>
</head>

<body onLoad="carregaBanner();">  
<div id="main">
	<table width="779" border="0" align="center" cellpadding="0" cellspacing="0">
   	<tr>
      	<td height="98" align="center" valign="bottom">
				<span id="spnBannerSuperior"></span><br>
			</td>
    	</tr>
    	<tr>
      	<td height="98" colspan="3" align="center" valign="bottom">
			<?php
  			include DIR_WEB . "includes/topoN.php";
			?>
			</td>
    	</tr>
  </table>

<!-- inicio :: centro //-->
<div id="conteudo_maps">
	<!-- inicio :: conteudo principal //-->
	<span class="titulo">Encontre um Ponto de Venda</span><br><br>
	<div id="img_help" class="styled">
		<table border="0">
		<tr>
			<td class="font-10px">&nbsp;</td>
			<td width="20%">&nbsp;</td>
			<td class="font-10px" width="30%">&nbsp;</td>
			<td><img src="/imagens/icone_eppLH.png" width="43" height="55" border="0" title="Lanhouses" alt="Lanhouses"></td>
			<td class="font-10px" width="25%">Lan Houses</td>
		</tr>
		</table>
	</div>
	<div id="legendpagto" class="outside">
		<table border="0" class="legendaoutside">
		<tr>
			<td>Você também pode</td>
		</tr>
		<tr>
			<td>comprar utilizando os</td>
		</tr>
		<tr>
			<td>meios de pagamento online</td>
		</tr>
		<tr>
			<td><img src="/imagens/logos_pagamentos.png" width="141" height="54" border="0" alt="Meios de Pagamentos" title="Meios de Pagamentos"></td>
		</tr>
		<tr>
			<td>________________________</td>
		</tr>
		<tr class="legendaoutsidegreen">
			<td><a href="/game/index.php" target="_blank">Acesse a loja online</a></td>
		</tr>
		</table>
	</div>
	<form name="form_lanHouses_filtros" id="form_lanHouses_filtros" action="lan_houses_b2c.php" method="post">
	<input type="hidden" name="escolha" value="Cidade">
	<input name="contactForm" type="hidden" id="contactForm" value="Send" />
	<table width="800" border="0" cellpadding="1" cellspacing="1" bgcolor="#D6D6D6">
	<tr>
		<td>
		<table class="bgcolor-table" width="800" border="0" cellpadding="0" cellspacing="0">
			<tr>
				<td align="left">
					<div id="msg_validacao"></div>
				</td>
			</tr>
			<tr>
				<td class="font-10px">
					<div id="pontos_busca">
						<div id="ajuste">
							<div id="SelEstado" name="SelEstado">
							 Estado:&nbsp;&nbsp;
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
								if ($ResultadoCidade){
									echo '<select name="cidade" id="cidade"  onChange="MostraBairro();">';
									foreach ($ResultadoCidade as $RowCidade){
										echo '<option value="'.$RowCidade['ug_cidade'].'"';
										if ($_REQUEST['cidade'] == $RowCidade['ug_cidade'] && !empty($RowCidade['ug_cidade'])){
											echo " SELECTED ";
										}
										echo '>'.$RowCidade['ug_cidade'].'</option>';
									}
									echo  '</select>';
								}else{
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
							 Bairro:&nbsp;&nbsp;&nbsp;
								<?php
								if ($ResultadoBairro){
									echo '<select name="bairro" id="bairro">';
									foreach ($ResultadoBairro as $RowBairro){
										echo '<option value="'.$RowBairro['ug_bairro'].'"';
										if ($_REQUEST['bairro'] == $RowBairro['ug_bairro'] && !empty($RowBairro['ug_bairro'])){
											echo " SELECTED ";
										}
										echo '>'.$RowBairro['ug_bairro'].'</option>';
									}
									echo  '</select>';
								}else{
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
		<table class="bgcolor-table" width="800" border="0" cellpadding="0" cellspacing="0">
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
					<a onClick="ValidaForm()" id="bt_procurar" href="#" ><img src="/imagens/botao-procurar.png" width="131" height="33" border="0" /></a>
				</td>
			</tr>
		</table>
		</td>
	</tr>
	<tr>
		<td>
		<table class="bgcolor-table" width="800" border="0" cellpadding="0" cellspacing="0">
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
</div>
<!-- fim :: conteudo principal //-->

	<br/>
	<br/>
	<br/>
	<br/>
	<br/>
<?php
include DIR_WEB . "includes/rodape.php";
?>
<script src="http://www.google-analytics.com/urchin.js" type="text/javascript"></script>
<script type="text/javascript">
_uacct = "UA-1903237-3";
urchinTracker();
</script>
</body>
</html>