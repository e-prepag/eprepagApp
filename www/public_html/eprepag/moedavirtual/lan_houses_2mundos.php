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

//Conectando com PDO para execução da QUERY
$con = ConnectionPDO::getConnection();
$pdo = $con->getLink();

$https = 'http' . (($_SERVER['HTTPS']=='on') ? 's' : '');
$need_key_maps = (checkIP())?"sensor=false":"key=AIzaSyA25PAcZMc6toew3UDW1HwG8wve00r8hb4";

// Deixa o drop down nos valores que estavam selecionados antes do reload.
$valorRequestCidade = isset($_POST['cidade']) ? filter_var(trim(str_replace("'", "",$_POST['cidade'])),FILTER_SANITIZE_STRING) : '';
if ((isset($_REQUEST['cidade'])) and (isset($_REQUEST['bairro']))){
    
    
	$SQLBairro = "SELECT distinct(ug_bairro) as ug_bairro
				FROM dist_usuarios_games
				WHERE replace(ug_cidade, '\'', '') = :ug_cidade
					AND ug_ativo = 1
					AND ug_status = 1
				ORDER BY ug_bairro";
    
    $stmt = $pdo->prepare($SQLBairro);
    $stmt->bindParam(":ug_cidade", $valorRequestCidade,PDO::PARAM_STR);
    $stmt->execute();
    $ResultadoBairro = $stmt->fetchAll(PDO::FETCH_ASSOC);
}


// Query que cria o drop drown das cidades
$SQLCidade = "SELECT ug_cidade, ug_estado
					FROM dist_usuarios_games
					WHERE ug_ativo = 1
						AND ug_status = 1
					GROUP BY ug_cidade, ug_estado 
					ORDER BY ug_cidade";
$stmt = $pdo->prepare($SQLCidade);
$stmt->execute();
$ResultadoCidade = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
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
<script type="text/javascript" src="<?php echo $https; ?>://maps.google.com/maps/api/js?<?php echo $need_key_maps; ?>"></script>
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
include "../incs/date.php";
?>
<script type="text/javascript">

	function ValidaForm() {
        waitingDialog.show('Por favor, aguarde...',{dialogSize: 'sm'});
        setTimeout(function(){
            if ($("#cidade").val() == ""){
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
	
	function MostraBairro() {
        if ($("#cidade").val() != "")
            waitingDialog.show('Buscando bairros...',{dialogSize: 'sm'});
        setTimeout(function(){
            if ($("#cidade").val() != ""){
                cidade = $("#cidade").val();
                $.ajax({
                    type: "POST",
                    url: "lan_house_select_bairro_2mundos.php",
                    data: "cidade=" + cidade,
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
            cidade = $("#cidade").val();
            bairro = $("#bairro").val();
            verificationCode = document.form_lanHouses_filtros.verificationCode.value;
            $.ajax({
                type: "POST",
                url: "lan_houses_result_2mundos.php",
                data: "cidade=" + cidade + "&bairro=" + bairro + "&verificationCode=" + verificationCode,
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
		document.form_lanHouses_filtros.action = "lan_houses_2mundos.php";
		document.form_lanHouses_filtros.submit();		
		return false;
	}
</script>
</head>
<body>  
<body>  
<div id="main">
<!-- inicio :: centro //-->
<div id="conteudo_maps">
	<!-- inicio :: conteudo principal //-->  
   <div id="msg_validacao" align="center"></div>
	<br/>
   Para localizar o endere&ccedil;o mais pr&oacute;ximo selecione abaixo a "<b>Cidade</b>" e opcionalmente o "<b>Bairro</b>".
   <br/>
   Escreva as letras que aparecem na imagem e clique em "<b>Procurar</b>".
	<br/>
		<div id="pontos_busca">
			<form name="form_lanHouses_filtros" id="form_lanHouses_filtros" action="lan_houses_2mundos.php" method="post">
         <input type="hidden" name="escolha" value="Cidade">
		 	<div id="ajuste">
				<div id="SelCidade" style="float: left;">
         		&nbsp;&nbsp;Cidade:
					<select name="cidade" id="cidade" onChange='MostraBairro();'>
						<option value="">Selecione uma cidade</option>
						<?php
						// Gera os dados do drop down cidade
                        foreach ($ResultadoCidade as $RowCidade){
							if (!empty($RowCidade['ug_cidade'])) {
								echo '<option value="'.$RowCidade['ug_cidade'].'"';
								if ($_POST['cidade'] == $RowCidade['ug_cidade']){
									echo " SELECTED ";
								}
								echo '>'.$RowCidade['ug_cidade'].' - '.$RowCidade['ug_estado'].'</option>';
							}
						}
					?>
					</select>
         	</div>
         <div id="SelBairro" style="float: left;">
         	&nbsp;&nbsp;&nbsp;Bairro:
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
						<option>Selecione um Bairro</option>		
					</select>
         	<?php
				}
				?>
         </div>
		</div>
		<div id="bt">
			<table width="60%" border="0">
				<tr>
					<td width="10%">&nbsp;
               	
               </td>
					<td align="center" id="td_captcha">
						<?php 
							$randomcode = generateRandomCode();	//	"ytoe"	-> translatedcode: '122118114105'	// "atoe";	//
							$randomcode_translated = translateCode($randomcode);
//							$randomcode_translated_rev = translateCode_rev($randomcode_translated);

//echo "randomcode: '$randomcode'<br>";
//echo "randomcode_translated: '$randomcode_translated'<br>";
//echo "randomcode_translated_rev: '$randomcode_translated_rev'<br>";
						?>
			           	<img src="/includes/captcha/CaptchaImage.php?uid=<?php echo $randomcode_translated; ?>" title="Verify Code" vspace="2" />
	               </td>
					<td width="10%">&nbsp;
               	
	               </td>
					<td align="center">
               	<input name="verificationCode" type="text" id="verificationCode" size="5" />&nbsp;&nbsp;
               </td>
					<td width="10%">&nbsp;
               	
               </td>
					<td align="center">
               	<a onClick="ValidaForm()" id="bt_procurar" href="#" ><img src="/imagens/bt_procurar.gif" width="131" height="33" border="0" /></a>
               </td>
					<td>&nbsp;</td>
				</tr>
			</table>
			&nbsp;&nbsp;&nbsp;Se não conseguir visualizar o código de verificação acima, <a href="javascript:monta_captcha();">clique aqui</a>.<br>
			</div>
			</div>
			<input name="contactForm" type="hidden" id="contactForm" value="Send" />
		</div>
		</form>
	<!-- fim :: conteudo principal //-->
	<!-- inicio :: resultado //-->	
	<div name="resultado" id="resultado"></div>
	<!-- fim :: resultado //-->	
	<!-- fim :: conteudo principal //-->
	<br/>
	<br/>
	<br/>
	<br/>
		<?php
		//include "../incs/rodape.php";
		?>
   </div>
  <script src="http://www.google-analytics.com/urchin.js" type="text/javascript">
	</script>
	<script type="text/javascript">
	//_uacct = "UA-1903237-3";
	//urchinTracker();
	</script>
</body>
</html>