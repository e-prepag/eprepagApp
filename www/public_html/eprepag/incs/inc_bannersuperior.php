<?php
//include "../../prepag2/dist_commerce/includes/classPrincipal.php";

// $bHabbo = ((strpos(strtoupper($_SERVER['HTTP_REFERER']),"WWW.HABBO.COM.BR")>0) && ((strpos($_SERVER['SCRIPT_NAME'],"eprepag/index.asp")>0)));

//echo "<span title='".$_SERVER['SCRIPT_NAME'].", ".$_SERVER['HTTP_REFERER']."'>*</span>";

$SQL = 'SELECT * 
			FROM tbBanner
			WHERE tipo = 1 and ativo = 1'.$TipoSup.'
			AND ((extract(epoch from age(datinicio)) >0) AND (extract(epoch from age(dattermino))<=0))
			ORDER BY random()';
$Resultado = SQLexecuteQuery($SQL);	
?>

<script language="javascript">
<!--
var tempoAlterarBannerSuperior = 12; //tempo em segundos

var bannersSuperiores = new Array();
var bannersSuperioresURL = new Array();
var ultimoBannerSuperior = 0;

function carregaBanner(){
	<?php
	if ($Resultado){
		$linha = 0;
		while ($Row = pg_fetch_array($Resultado)){
	?>
			bannersSuperiores[<?php echo $linha; ?>] = "<?php echo $Path; echo $Row['arquivo']; ?>";
			bannersSuperioresURL[<?php echo $linha; ?>] = "<?php echo $Row['urladdress']; ?>";
	<?php
			$linha++;
		}
	}
	?>
	ultimoBannerSuperior = -1;
	changeBannerSuperior();

	if(bannersSuperiores.length>1) { window.setInterval(changeBannerSuperior, (tempoAlterarBannerSuperior * 1000)); }
}

function changeBannerSuperior(){
	if(bannersSuperiores.length<=1) {
		return;
	}
	ultimoBannerSuperior = ultimoBannerSuperior + 1;
	if(ultimoBannerSuperior>=bannersSuperiores.length) {ultimoBannerSuperior = 0;	}
	bannernome = "";
	if(bannersSuperiores[ultimoBannerSuperior].indexOf(".swf")>0) {	
		bannernome = getFlash(ultimoBannerSuperior);
		if(bannersSuperioresURL[ultimoBannerSuperior]!="") 
			document.getElementById("spnBannerSuperior").innerHTML = "<a href='" + bannersSuperioresURL[ultimoBannerSuperior] + "' target='_blank'>" + bannernome + "</a>";
		else
			document.getElementById("spnBannerSuperior").innerHTML = bannernome;
	} 
	else {
		bannernome = bannersSuperiores[ultimoBannerSuperior];	
		if(bannersSuperioresURL[ultimoBannerSuperior]!="") 
			document.getElementById("spnBannerSuperior").innerHTML = "<a href='" + bannersSuperioresURL[ultimoBannerSuperior] + "' target='_blank'><img src='" + bannernome + "' border='0'></a>";
		else
			document.getElementById("spnBannerSuperior").innerHTML = "<img src='" + bannernome + "' border='0'>";
	}
}

function getFlash(arquivo){
	var banner = "<object classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' codebase='http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,19,0' width='728' height='90'>" +
				 "	<param name='movie' value='" + bannersSuperiores[arquivo] + "' />" +
				 "	<param name='quality' value='high' />" +
				 "  <embed src='" + bannersSuperiores[arquivo] + "' quality='high' pluginspage='http://www.macromedia.com/go/getflashplayer' type='application/x-shockwave-flash' width='728' height='90'></embed>" +
				 "</object>";
	return banner;
}

-->
</script>