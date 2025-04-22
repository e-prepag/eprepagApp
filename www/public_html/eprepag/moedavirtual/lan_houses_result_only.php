<?php
header("Content-Type: text/html; charset=ISO-8859-1",true);
function isAjax() {return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));}
function block_direct_calling() {
    if(!isAjax()) {
           echo "Chamada não permitida<br>";
           die("Stop");
    }
}
block_direct_calling();
require_once "../../../includes/constantes.php";
require_once  DIR_INCS . "main.php";
require_once  DIR_INCS . "pdv/main.php";
require_once  RAIZ_DO_PROJETO . "/public_html/includes/captcha/Functions.php";

// Adiciona os valos recebidos por post em uma variavel
$Estado = $_POST['estado'];
$Cidade = utf8_decode($_POST['cidade']);
$Bairro = utf8_decode($_POST['bairro']);

if(empty($ug_ativo)) {
	$ug_ativo = 0;
}

//echo $_SERVER['HTTP_REFERER'];
//echo substr($_SERVER['HTTP_REFERER'],0,strpos($_SERVER['HTTP_REFERER'],"?")?strpos($_SERVER['HTTP_REFERER'],"?"):strlen($_SERVER['HTTP_REFERER']));
?>
<form name="form_lanHouses" id="form_lanHouses" action="lan_houses_result_only.php" method="post">
	<div id="main">
   	<?php
	
		if((strtolower($_POST['verificationCode']) == strtolower($_POST['recaptchaReceived']))) {
		?>
			<br>
			<table border="0" cellspacing="0" cellpadding="0">
			  <tr>
				<td align="center" valign="top" style="font-family:Arial, Helvetica, sans-serif;font-size:12px;font-style:strong;">
				  <div id="legendas"></div>
				</td>
			  </tr>
			  <tr>
                                <td valign="top"><div id="map" style="width: 780px; height: 500px"></div></td>
			<?php
                            require_once "maplanhouse_only.php";
			?>
				</td>
			  </tr>
			</table>
			<script type="text/javascript">
				inicializa();
			</script>

			<?php 
		}
		// Verifica se o codigo foi digitado ou se o codigo digitado é igual ao da imagem
		elseif ((strlen($_POST['verificationCode']) == CHARSLEN) && (strtolower($_POST['verificationCode']) == strtolower($_SESSION['palavraCodigo']))){
                        unset($_SESSION['palavraCodigo']);
			?>
			<br>
			<table border="0" cellspacing="0" cellpadding="0">
			  <tr>
				<td align="center" valign="top" style="font-family:Arial, Helvetica, sans-serif;font-size:12px;font-style:strong;">
				  <div id="legendas"></div>
				</td>
			  </tr>
			  <tr>
                                <td valign="top"><div id="map" style="width: 780px; height: 500px"></div></td>
			<?php
                            require_once "maplanhouse_only.php";
			?>
				</td>
			  </tr>
			</table>
			<script type="text/javascript">
				inicializa();
			</script>
			<?php 
		}elseif($_POST['verificationCode'] == ""){
			echo '<p align="center"><font color="#FF0000">Preencha o campo de verificação.</font></p>';
		}elseif(strtolower($_POST['verificationCode']) != strtolower($_SESSION['palavraCodigo'])){
			echo '<p align="center"><font color="#FF0000">Preencha o campo de verificação corretamente.</font></p>';
		}
		?>
			</form>
			<!-- fim :: conteudo principal //-->
   	</div>
    <!-- fim :: centro //-->
</body>
</html>
<br>