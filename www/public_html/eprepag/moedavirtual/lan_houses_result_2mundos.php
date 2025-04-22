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

// Adiciona os valos recebidos por post em uma variavel
$Cidade = utf8_decode($_POST['cidade']);
$Bairro = utf8_decode($_POST['bairro']);

if(empty($ug_ativo)) {
	$ug_ativo = 0;
}
/*
// Query com as lanhouses encontradas.
// query alterada em AND ug_busca = 1 para AND ug_status = 1
$SQLResult = "set client_encoding to latin1;SELECT ug_nome_fantasia, ug_numero, ug_cidade, ug_endereco, ug_tipo_end, ug_bairro, ug_estado, ug_razao_social 
			FROM dist_usuarios_games 
			WHERE ug_cidade = '".$Cidade."' 
			AND ug_bairro = '".$Bairro."' 
			AND ug_ativo = 1 
			AND ug_status = 1
			ORDER BY ug_bairro"; 

//echo $SQLResult;
$ResultadoResult = SQLexecuteQuery($SQLResult);
/*
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta content="text/html; charset=iso-8859-1" http-equiv="Content-Type">
<title>MAPA</title>
<link href="../incs/styles.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../incs/scripts.js"></script>
<link type="text/css" href="css/style.css" rel="stylesheet" media="all" />
<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
<?php
include "date.php";
?>
</head>

<body onLoad="inicializa();">
<?php
*/
?>
<form name="form_lanHouses" id="form_lanHouses" action="lan_houses_result_2mundos.php" method="post">
	<div id="main">
   	<?php
		// Verifica se o codigo foi digitado ou se o codigo digitado é igual ao da imagem
		if (($_POST['verificationCode'] == $_SESSION['palavraCodigo'])){
            unset($_SESSION['palavraCodigo']);
		/*
		?>
  			<!-- inicio :: conteudo principal //-->  
         <div id="conteudo">
			Resultado da busca pela Cidade <strong><?php echo $Cidade; ?></strong> e bairro <strong><?php echo $Bairro; ?></strong>
      		<br/>
				<!--Encontrados <strong>20</strong> estabelecimentos.<br />-->
				<br/>
				<!-- inicio :: carrinho titulos //-->
				<div id="pontos_topo">
					<div id="pontos_topo_estabelecimento">
						<img src="../imgs/pontos_tit_estabelecimento.gif" />
					</div>
					<div id="pontos_topo_endereco">
					  <img src="../imgs/pontos_tit_endereco.gif" />
				   </div>
					<div id="pontos_topo_bairro">
					  <img src="../imgs/pontos_tit_bairro.gif" />
				   </div>
					<div id="pontos_topo_cidadeuf">
					  <img src="../imgs/pontos_tit_cidadeuf.gif" />
				   </div>
				</div>
				<!-- fim :: carrinho titulos //-->
				<!-- inicio :: carrinho itens //-->
      		<?php
			//echo "Antes do While<br>";
      		while ($RowResult = pg_fetch_array($ResultadoResult)){
				//echo "Dentro do While<br>";
      			?>	
					<div id="pontos_item">
        				<div id="pontos_item_estabelecimento">
            			<b>
            			<?php
							if ($RowResult['ug_nome_fantasia'] == ""){
								echo $RowResult['ug_razao_social'];
							}else{
								echo $RowResult['ug_nome_fantasia'];
							}
            			?>
							</b>
						</div>
						<div id="pontos_item_endereco">
							<?php
							if ($RowResult['ug_tipo_end'] == ""){
								echo $RowResult['ug_endereco'];
							}else{
								echo $RowResult['ug_tipo_end'].'&nbsp;'.$RowResult['ug_endereco'];
							}
							if ($RowResult['ug_numero'] != ""){
								echo ',&nbsp;'.$RowResult['ug_numero'];
							}
							if ($RowResult['ug_complemento'] != ""){
								echo ',&nbsp;'.$RowResult['ug_complemento'];
							}
							?>
						</div>
						<div id="pontos_item_bairro">
            			<?php
							echo $RowResult['ug_bairro'];
							?>
						</div>
						<div id="pontos_item_cidadeuf">
            			<?php
							echo $RowResult['ug_cidade'].'/'.$RowResult['ug_estado'];
							?>
	            		</div>
         		</div>
				<?php
      		}*/
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
			include('maplanhouse_2mundos.php');
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
		}elseif($_POST['verificationCode'] != $_SESSION['palavraCodigo']){
			echo '<p align="center"><font color="#FF0000">Preencha o campo de verificação corretamente.</font></p>';
		}
		?>
			</form>
			<!-- fim :: conteudo principal //-->
   	</div>
 		<!-- fim :: centro //-->
</body>
</html>