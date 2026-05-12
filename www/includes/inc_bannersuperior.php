<?php
    $prod = $prod ?? 0;
    $sTiposup = $sTiposup ?? '';
    $varStatus = $varStatus ?? 0;
    $sPath = $sPath ?? '';
    $sTiposupAllow = array(
        '' => '',
        ' AND ((tiposup=0) OR (tiposup=1)) ' => ' AND ((tiposup=0) OR (tiposup=1)) ',
    );
    $sTiposup = $sTiposupAllow[$sTiposup] ?? '';

	// Mark origem from Habbo
	$bHabbo = false;
	if(($_SERVER['SCRIPT_NAME'] ?? '')=="/prepag2/commerce/modelosEx.php" && $prod==5 && ($_SERVER['HTTP_REFERER'] ?? '')=="http://www.habbo.com.br/credits") {
		$bHabbo = true;		
	}

	$comando = "SELECT * " 
			  ."FROM tbBanner " 
			  ."WHERE tipo = 1 AND ativo=1 ". $sTiposup ." "; 

	if($bHabbo) {
		$comando .= " AND strpos(upper(nome),'HABBO')>0"; 
	} else {
		$comando .= " AND ((extract(epoch from age(datinicio)) >0) AND (extract(epoch from age(dattermino))<=0)) "; 
	}
	$comando .= "ORDER BY random()";

	$idbanners = "";
	$rs_bannersSuperiores = SQLexecuteQuery($comando);
?>

<script language="javascript">
<!--
var tempoAlterarBannerSuperior = 12; //tempo em segundos
var bannersSuperioresID = 0;

var rs_bannersSuperiores = new Array();
var rs_bannersSuperioresURL = new Array();
var ultimoBannerSuperior = 0;

function carregaBanner(){
	<?php
		$linha=0;
        $arquivo = '';
        $url = '';
        $spref = 's';
		if($rs_bannersSuperiores && pg_num_rows($rs_bannersSuperiores) > 0){
			while($rs_bannersSuperiores_row = pg_fetch_array($rs_bannersSuperiores)) {
            if (!is_array($rs_bannersSuperiores_row)) continue;

			$idbanners .= $rs_bannersSuperiores_row['idbanner'].", ";
	
			$arquivo = $sPath.$rs_bannersSuperiores_row['arquivo'];
			$url = $rs_bannersSuperiores_row['urladdress'];
			$spref = "s";
			$url = str_replace("http", "https", strtolower((string)$url));
	?>
			rs_bannersSuperiores[<?php echo $linha?>] = "<?php echo addslashes((string)$arquivo); ?>";
			rs_bannersSuperioresURL[<?php echo $linha?>] = "<?php echo addslashes((string)$url); ?>";
	<?php
			$linha ++;
			}
		}
	?>	

	ultimoBannerSuperior = -1;
	changeBannerSuperior();

	if(rs_bannersSuperiores.length>1) { bannersSuperioresID = window.setInterval(changeBannerSuperior, (tempoAlterarBannerSuperior * 1000)); }

	// Enquete 
	carregaEnquete(<?php echo (int)$varStatus ?>);
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
