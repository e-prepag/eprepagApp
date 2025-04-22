<?php


	// Mark origem from Habbo
// $_SERVER['SCRIPT_NAME'] = "/prepag2/commerce/modelosEx.php"
// $_SERVER['HTTP_REFERER']
//echo "<span color='#FFFFFF' title='".$_SERVER['SCRIPT_NAME'].", ".$_SERVER['HTTP_REFERER'].", $prod'>*</span>";
	$bHabbo = false;
	if(($_SERVER['SCRIPT_NAME']=="/prepag2/commerce/modelosEx.php") && ($prod==5) && ($_SERVER['HTTP_REFERER']=="http://www.habbo.com.br/credits")) {
//		echo "<span color='#FFFFFF' title='".$_SERVER['SCRIPT_NAME'].", ".$_SERVER['HTTP_REFERER'].", $prod'>*</span>";
		$bHabbo = true;		
	}

	$comando = "SELECT * " 
			  ."FROM tbBanner " 
			  ."WHERE tipo = 1 AND ativo=1 ".$sTiposup." "; 

	if($bHabbo) {
		$comando .= " AND strpos(upper(nome),'HABBO')>0"; 
	} else {
		$comando .= " AND ((extract(epoch from age(datinicio)) >0) AND (extract(epoch from age(dattermino))<=0)) "; 
	}
	$comando .= "ORDER BY random()";

	$idbanners = "";
//echo "<!-- ".$comando." -->\n";
//echo "<!-- ".$prod." -->\n";
//echo "<!-- ".$_SERVER['SCRIPT_NAME']." -->\n";
//echo "<!-- ".$_SERVER['HTTP_REFERER']." -->\n";
//	echo $comando."<br>";

// Warning: pg_query() [function.pg-query]: Query failed: ERROR: permission denied for relation tbbanner in C:\Sites\E-Prepag\www\web\incs\functions.php on line 56

	$rs_bannersSuperiores = SQLexecuteQuery($comando);

//	if($rs_bannersSuperiores && pg_num_rows($rs_bannersSuperiores) > 0){
//		echo "pg_num_rows: ".pg_num_rows($rs_bannersSuperiores)."<br>";
//	}
//	echo $rs_bannersSuperiores."<br>";
?>

<script language="javascript">
<!--
var tempoAlterarBannerSuperior = 12; //tempo em segundos
var bannersSuperioresID = 0;

var rs_bannersSuperiores = new Array();
var rs_bannersSuperioresURL = new Array();
var ultimoBannerSuperior = 0;

function carregaBanner(){
	<?

//	Example 
//	if($rs_concilia && pg_num_rows($rs_concilia) > 0){
//		$rs_concilia_row = pg_fetch_array($rs_concilia);
//		$vg_id_prox = $rs_concilia_row['vg_id'];
//	}

		$linha=0;
		if($rs_bannersSuperiores && pg_num_rows($rs_bannersSuperiores) > 0){
			while($rs_bannersSuperiores_row = pg_fetch_array($rs_bannersSuperiores)) {

			$idbanners .= $rs_bannersSuperiores_row['idbanner'].", ";
	
			$arquivo = $sPath.$rs_bannersSuperiores_row['arquivo'];
			$url = $rs_bannersSuperiores_row['urladdress'];
			$spref = "";

			if(($GLOBALS['_SERVER']['HTTPS']=="on") && ($GLOBALS['_SERVER']['SERVER_PORT']==443)) {
				$url = str_replace("http", "https", strtolower($url));
				$spref = "s";
			}

	?>
			rs_bannersSuperiores[<?php echo $linha?>] = "<?php echo $arquivo; ?>";
			rs_bannersSuperioresURL[<?php echo $linha?>] = "<?php echo $url; ?>";
	<?
			$linha ++;
			}
		}
	?>	

	ultimoBannerSuperior = -1;
	changeBannerSuperior();

	if(rs_bannersSuperiores.length>1) { bannersSuperioresID = window.setInterval(changeBannerSuperior, (tempoAlterarBannerSuperior * 1000)); }

	// Enquete 
	carregaEnquete(<?php echo $varStatus ?>);
}

function changeBannerSuperior(){
	if(rs_bannersSuperiores.length<1) {
		return;
	}
	ultimoBannerSuperior = ultimoBannerSuperior + 1;
	if(ultimoBannerSuperior>=rs_bannersSuperiores.length) {ultimoBannerSuperior = 0;	}

<? //document.getElementById("spnBannerSuperiorInfo").innerHTML = "<span class='texto'>["+ultimoBannerSuperior+"] : "+rs_bannersSuperiores[ultimoBannerSuperior]+" ("+rs_bannersSuperioresURL[ultimoBannerSuperior]+")</span>"; ?>

	bannernome = "";
	if(rs_bannersSuperiores[ultimoBannerSuperior].indexOf(".swf")>0) {	
		bannernome = getFlash(ultimoBannerSuperior);
		if(rs_bannersSuperioresURL[ultimoBannerSuperior]!="") 
			document.getElementById("spnBannerSuperior").innerHTML = "<a href='" + rs_bannersSuperioresURL[ultimoBannerSuperior] + "' target='_blank'>" + bannernome + "</a>";
		else
			document.getElementById("spnBannerSuperior").innerHTML = bannernome;
	} 
	else {
		bannernome = rs_bannersSuperiores[ultimoBannerSuperior];	
		if(rs_bannersSuperioresURL[ultimoBannerSuperior]!="") 
			document.getElementById("spnBannerSuperior").innerHTML = "<a href='" + rs_bannersSuperioresURL[ultimoBannerSuperior] + "' target='_blank'><img src='" + bannernome + "' border='0'></a>";
		else
			document.getElementById("spnBannerSuperior").innerHTML = "<img src='" + bannernome + "' border='0'>";
	}

}

function getFlash(arquivo){
	var banner = "<object classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' codebase='http<?php echo $spref; ?>://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,19,0' width='728' height='90'>" +
				 "	<param name='movie' value='" + rs_bannersSuperiores[arquivo] + "' />" +
				 "	<param name='quality' value='high' />" +
				 "  <embed src='" + rs_bannersSuperiores[arquivo] + "' quality='high' pluginspage='http://www.macromedia.com/go/getflashplayer' type='application/x-shockwave-flash' width='728' height='90'></embed>" +
				 "</object>";
	return banner;
}


function addLoadEvent(func) {	
	var oldonload = window.onload;	
	if(typeof window.onload != "function") {
		window.onload = func;	
	}
	else {
		window.onload = function() {			
			oldonload();			
			func();		
		}
	}
}


<?
/*
function chain(object, methodName, newMethod){ 
	if(object && typeof object == 'object' && methodName && typeof methodName == 'string' && newMethod && typeof newMethod == 'function'){ 
		var old = object[methodName]; 
		if(old && typeof old == 'function'){ 
			var oldArgs = []; 
			var newArgs = []; 
			for(var i0 = 0; i0 < old.length; i0++){ 
				oldArgs[i] = 'arg' + i0; 
			} 
			for(var i0 = 0; i0 < newMethod.length; i0++){ 
				newArgs[i] = 'arg' + i0; 
			} 
			oldArgs = oldArgs.join(', '); 
			newArgs = newArgs.join(', '); 
			var args = old.length > newMethod.length ? oldArgs : newArgs; 
			object[methodName] = eval('function(' + args + '){\n' + ' old.call(' + oldArgs + ');\n' + ' newMethod.call(' + newArgs + ');\n' + '}\n'); 
		}else { 
			object[methodName] = newMethod; 
		} 
	} 
}
*/
?>

function carregaEnquete(varStatus) {
	if (varStatus == 1)
		document.getElementById('divTitulo').style.display = 'block';
}
-->
</script>

<?php
//echo "<!-- ".$idbanners ." -->\n";

?>
