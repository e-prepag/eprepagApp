<?php 		
$sProtocol = (($_SERVER['HTTPS']=="on")?"HTTPS":"HTTP");
$spref = "";
if(($GLOBALS['_SERVER']['HTTPS']=="on") && ($GLOBALS['_SERVER']['SERVER_PORT']==443)) {
	$spref = "s";
}


?><link href="/css/home2.css" rel="stylesheet" type="text/css">
<div id="topo"><div id="logo"><a href="/" border="0"><img src="/imagens/logo_eprepag.gif" title="E-PREPAG<?php echo (($_SESSION["OrigemId"])?" (" . $_SESSION["OrigemId"] . ")":"") ?>" border="0" /></a></div>
	<?php
		$strRequestURI_Jogos = strstr($_SERVER["REQUEST_URI"], '/prepag2/commerce/jogos/');
		$strRequestURI_Ofertas = strstr($_SERVER["REQUEST_URI"], '/prepag2/commerce/ofertas/');
			
		// Para modificar alinhamento em página de jogos Alawar		
		if(trim($strRequestURI_Jogos) || trim($strRequestURI_Ofertas)) {
			$styleMenu = 'style="margin-right: 70px;"';
		}		

	?>
	<div id="menu" <?php echo $styleMenu; ?>><nobr><table cellpadding='0' cellspacing='1' border='0' bordercolor='#cccccc' style='border-collapse:collapse;'><tr>
		<td><a href='https://www.e-prepag.com' class='tit_menu_arial_bold' onMouseover="this.style.color='#FF0000'" onMouseout="this.style.color='#24297F'" border="0">PORTAL E-PREPAG</a></td>
		<td><img src='/imagens/menu_div.gif' width='7' height='18' border='0'/></td>
		<td><a href='/games-list-coins' class='tit_menu_arial_bold' onMouseover="this.style.color='#FF0000'" onMouseout="this.style.color='#24297F'" border="0">COMPRE AQUI</a></td>
		<td><img src='/imagens/menu_div.gif' width='7' height='18' border='0'/></td>
		<td><a href='/e-prepag' class='tit_menu_arial_bold' onMouseover="this.style.color='#FF0000'" onMouseout="this.style.color='#24297F'" border="0">QUEM SOMOS</a></td>
		<td><img src='/imagens/menu_div.gif' width='7' height='18' border='0'/></td>
		<td><a href='/eprepag/revendedores/index.asp' class='tit_menu_arial_bold' onMouseover="this.style.color='#FF0000'" onMouseout="this.style.color='#24297F'" border="0">REVENDEDORES</a></td>
		<td><img src='/imagens/menu_div.gif' width='7' height='18' border='0'/></td>
		<td><a href='/support' class='tit_menu_arial_bold' onMouseover="this.style.color='#FF0000'" onMouseout="this.style.color='#24297F'" border="0">FALE CONOSCO</a></td>
		</tr></table></a></nobr></div></div>