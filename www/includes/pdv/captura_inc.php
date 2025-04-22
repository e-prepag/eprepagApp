<?php

	require_once RAIZ_DO_PROJETO . "includes/inc_register_globals.php";	
	require_once RAIZ_DO_PROJETO . "includes/pdv/inc_utf.php"; 

/* BEGIN BLOQUEIA REGISTRO (ver abaixo) */
//include "incs/inc_Browser.php";
//	$browser = new Browser("");
/*	END BLOQUEIA REGISTRO	*/	

$CanalId = $cid;
//echo "CanalId: $CanalId <br>";

$country_array = array();
	$country_array['country'] = '??';
	$country_array['city'] = '';
	$country_array['latitude'] = 0;
	$country_array['longitude'] = 0;
$ret = -3;
//$ret = getCountryInfo($country_array);

//gravaLog_Captura("Country Code (PHP): \n\t" . "SCRIPT_NAME: '".$_SERVER['SCRIPT_NAME']."'\n\tREMOTE_ADDR: ".$_SERVER['REMOTE_ADDR']."'\n\tCountryCode: '" . $country_code . "' [".(($country_code=="US ")?"REDIRECT":"don't redirect")."]\n");

// Registro do Parceiro
if ($CanalId == "" ) {
	$CanalId = 0;
}

if (!(isset($_SESSION['epp_origem'])) || ($_SESSION['epp_origem']=="") || ($_SESSION['epp_origem']=="EPP")) {

	//Inicia conexão
	
	//  insert into tb_CanalAcesso (CanalId,Data,IP,LocalID) values(11,current_timestamp,'xxx.xxx.xxx.11', 'HomeTeste')

//	LocalID = 
//		'Home'		- http://xxx.xxx.xxx.61/eprepag/index.asp 
//		'Gamers'	- http://xxx.xxx.xxx.61/eprepag/moedavirtual/index.php
//		'LH'		- http://xxx.xxx.xxx.61/eprepag/revendedores/index.asp
//		etc.

//echo "_SERVER['SCRIPT_NAME']: ".$_SERVER['SCRIPT_NAME']."<br>";
//if( (substr($_SERVER['HTTP_REFERER'],0,26)!="http://www.e-prepag.com.br") || (substr($_SERVER['HTTP_REFERER'],0,25)!="http://www.eprepag.com.br")) {

	if(strpos($_SERVER['SCRIPT_NAME'],"eprepag/index.asp")>0) {	$LocalId = "Home"; } 
	else if (strpos($_SERVER['SCRIPT_NAME'],"moedavirtual")>0) { $LocalId = "Gamers"; } 
	else if (strpos($_SERVER['SCRIPT_NAME'],"revendedores")>0) { $LocalId = "LH";  } 
	else if (strpos($_SERVER['SCRIPT_NAME'],"commerce/jogos")>0) { $LocalId = "Jogos"; } 
	else if (strpos($_SERVER['SCRIPT_NAME'],"commerce")>0) { $LocalId = "Commerce"; } 
	else if (strpos($_SERVER['SCRIPT_NAME'],"newsletter")>0) { $LocalId = "Teste"; } 
	else { $LocalId = "???"; }	

//echo "CanalId: $CanalId <br>";
//echo "LocalId: $LocalId <br>";

//	$SQLQueryNews = "insert into tb_CanalAcesso (CanalId,Data,IP,HTTP_REFERER, HTTP_USER_AGENT, browser_Platform, browser_Browser, browser_Version, LocalID) values($CanalId,current_timestamp,'".$_SERVER['REMOTE_ADDR']."', '".$_SERVER['HTTP_REFERER']."', '".$_SERVER['HTTP_USER_AGENT']."', '".$browser->getPlatform()."', '".$browser->getBrowser()."', '".$browser->getVersion()."', '$LocalId')";

		// Manter idêntico a captura_inc.asp
		if(strpos(strtoupper($_SERVER['HTTP_REFERER']),"GOOGLE.COM")>0) {	$OrigemId = "GOOGLE"; } 
		else if (strpos(strtoupper($_SERVER['HTTP_REFERER']),"WWW.ARENAMMO.COM.BR")>0) {	$OrigemId = "ARENAMMO"; } 
		else if (strpos(strtoupper($_SERVER['HTTP_REFERER']),"WWW.HABBO.COM.BR")>0) { $OrigemId = "HABBO"; } 
		else if (strpos(strtoupper($_SERVER['HTTP_REFERER']),"STARDOLL.UOL.COM.BR")>0) { $OrigemId = "STARDOLL"; } 
		else if (strpos(strtoupper($_SERVER['HTTP_REFERER']),"ONGAME.COM.BR")>0) { $OrigemId = "ONGAME"; } 
		else if (strpos(strtoupper($_SERVER['HTTP_REFERER']),"VOSTU.COM")>0) { $OrigemId = "VOSTU"; } 
		else if (strpos(strtoupper($_SERVER['HTTP_REFERER']),"WWW.VIRTUALKNOWLEDGE.COM.BR")>0) { $OrigemId = "TMP"; } 
		else if (strpos(strtoupper($_SERVER['HTTP_REFERER']),"WWW.E-PREPAG.COM.BR")>0) { $OrigemId = "EPP"; } 
		else if (strpos(strtoupper($_SERVER['HTTP_REFERER']),"SOFTNYX")>0) { $OrigemId = "SOFTNYX"; } 
		else if (strpos(strtoupper($_SERVER['HTTP_REFERER']),"AXESO5.COM.BR")>0) { $OrigemId = "AXESO5"; } 
		else if (strlen($_SERVER['HTTP_REFERER'])==0) { $OrigemId = "==EMPTY=="; } 
//		else if (strpos(strtoupper($_SERVER['HTTP_REFERER']),"DEV.E-PREPAG.COM.BR")>0) { $OrigemId = "STARDOLL"; } 

//echo "(0$OrigemId, R".$_SERVER['HTTP_REFERER'].")";

/* BEGIN BLOQUEIA REGISTRO (ver acima) */
/****
*	$ip_long_unsigned = sprintf("%u\n", ip2long($_SERVER['REMOTE_ADDR']));
*
*	$SQLQueryNews = "insert into tb_CanalAcesso (CanalId,Data,IP, ip_long, HTTP_CLIENT_IP, HTTP_X_FORWARDED_FOR, HTTP_REFERER, HTTP_USER_AGENT, browser_Platform, browser_Browser, browser_Version, LocalID, script_name, OrigemId) values($CanalId,current_timestamp,'".$_SERVER['REMOTE_ADDR']."', $ip_long_unsigned, '".str_replace("'", "''", $_SERVER['HTTP_CLIENT_IP'])."','".$_SERVER['HTTP_X_FORWARDED_FOR']."', '".str_replace("'", "''", $_SERVER['HTTP_REFERER'])."', '', '', '', '', '$LocalId', '".$_SERVER['SCRIPT_NAME']."', '$OrigemId')";
*
*	//echo "<!-- SQL $SQLQueryNews -->";
*
*	//Executa SQLQuery
*	$ret = SQLexecuteQuery($SQLQueryNews);
****/
/*	END BLOQUEIA REGISTRO	*/	

//echo "ret: $ret <br>";

//echo "<!-- HTTP_REFERER: ".$_SERVER['HTTP_REFERER']." -->";		

	// Define a origem da visita 

	$msg_captura = "Captura (".date("Y-m-d H:i:s").") \n\t";
	$msg_captura .= "SCRIPT_NAME: '".$_SERVER['SCRIPT_NAME']."',\n\t";
	$msg_captura .= "HTTP_REFERER: '".$_SERVER['HTTP_REFERER']."', \n\t";
	$msg_captura .= "REMOTE_ADDR: ".$_SERVER['REMOTE_ADDR'].", \n\t";
	$msg_captura .= "HTTP_CLIENT_IP".(($_SERVER['HTTP_CLIENT_IP'])?"(*)":"").": ".$_SERVER['HTTP_CLIENT_IP'].", \n\t";
	$msg_captura .= "HTTP_X_FORWARDED_FOR".(($_SERVER['HTTP_X_FORWARDED_FOR']?"(*)":"")).": ".$_SERVER['HTTP_X_FORWARDED_FOR']."\n\t";
	$msg_captura .= "* epp_origem: '".((isset($_SESSION['epp_origem']))?$_SESSION['epp_origem']:"==empty==")."',\n\t";
	$msg_captura .= "CountryCode: '" . $country_array['country'] . "' [".(($country_array['country']=="US")?"REDIRECT":"don't redirect")."]\n";
//	$msg_captura .= "CountryInfo: '" . $country_array['country'] . "', '" . translate_utf_to_extended_ascii($country_array['city']) . "' (" . $country_array['latitude'] . "," . $country_array['longitude'] . ")\n";
	$msg_captura .= "CountryInfo: '" . $country_array['country'] . "', '" . $country_array['city'] . "' (" . $country_array['latitude'] . "," . $country_array['longitude'] . ")\n";

//	gravaLog_Captura($msg_captura);

//echo "*";

//	} else 
	{
/*
		$OrigemId = "";

		if(strpos(strtoupper($_SERVER['HTTP_REFERER']),"WWW.ARENAMMO.COM.BR")>0) {	$OrigemId = "ARENAMMO"; } 
		else if (strpos(strtoupper($_SERVER['HTTP_REFERER']),"WWW.HABBO.COM.BR")>0) { $OrigemId = "HABBO"; } 
		else if (strpos(strtoupper($_SERVER['HTTP_REFERER']),"STARDOLL")>0) { $OrigemId = "STARDOLL"; } 
		else if (strpos(strtoupper($_SERVER['HTTP_REFERER']),"DEV.E-PREPAG.COM.BR")>0) { $OrigemId = "STARDOLL"; } 
*/

		if (!isset($_SESSION['epp_remote_addr']) || $_SESSION['epp_remote_addr']=="") {
			$_SESSION['epp_remote_addr'] = $_SERVER['REMOTE_ADDR'];
		}

//		if($OrigemId) {
		if (!isset($_SESSION['epp_origem']) || $_SESSION['epp_origem']=="") {
			$_SESSION['epp_origem'] = $OrigemId;
			$_SESSION['epp_origem_referer'] = $_SERVER['HTTP_REFERER'];
		}
//echo "<!-- epp_origem: '".$_SESSION['epp_origem']."' -->\n";
// session_id (*): ".session_id().", 
//gravaLog_Captura("Captura (".date("Y-m-d H:i:s").") epp_origem: '".$_SESSION['epp_origem']."' (HTTP_REFERER: ".$_SESSION['epp_origem_referer'].")\n");
//		}
//	} else {
//		$_SESSION['epp_origem'] .= "*";
	}
//echo "($OrigemId, ".$_SESSION['epp_origem'].")";

//}	

	// Redireciona para landing page
	if($OrigemId && $OrigemId == "AXESO5") {	
		// Axeso5
		$strRedirect = "http://www.e-prepag.com.br/prepag2/commerce/landing_page.php";
		header("Location: " . $strRedirect);
		die("Stop ABCGLanding");
	} 

} else {
//	echo "<span title='(".$_SESSION['epp_origem'].")'>*</span>";
}

/*
// Redireciona por IP
if($_SERVER['REMOTE_ADDR'] == "189.38.238.205") {	
	$strRedirect = "https://www.e-prepag.com";
	header("Location: " . $strRedirect);
	die("Stop ABCG Redirecting");
} 
*/

/*
// Redireciona por Country
if($country_array['country'] == "US") {	
	$strRedirect = "https://www.e-prepag.com";
	header("Location: " . $strRedirect);
	die("Stop ABCG Redirecting");
} 
*/

function getCountryInfo(&$country_array) {
	$country_array = array();
	$ret = 0;
	$remote_addr = $_SERVER['REMOTE_ADDR'];
//	$remote_addr = "189.38.238.205";	// São Paulo
//echo "remote_addr: '" . remote_addr . "'<br>";

	$country_array['country'] = '??';
	$country_array['city'] = '';
	$country_array['latitude'] = 0;
	$country_array['longitude'] = 0;

	if(strlen($remote_addr)>0) {
/*
		$sql = "SELECT country_code FROM ip2c " .
			"WHERE ( " .
			"		SELECT (((elements[1]::bigint * 256) + elements[2]::bigint) * 256 + elements[3]::bigint) * 256 + elements[4]::bigint as ip_long	" .
			"		FROM ( " .
			"			SELECT  string_to_array('" . $remote_addr . "', '.') as elements " .
			"		) t " .
			"	) BETWEEN begin_ip_num AND end_ip_num ";
*/
		$sql = "select * from ip2c_Blocks ipb inner join ip2c_Location ipl on ipl.locId = ipb.locId \n".
"WHERE ( \n".
"	SELECT (((elements[1]::bigint * 256) + elements[2]::bigint) * 256 + elements[3]::bigint) * 256 + elements[4]::bigint as ip_long	 \n".
"	FROM \n".
"		( SELECT string_to_array('" . $remote_addr . "', '.') as elements \n".
"		) t \n".
"	) BETWEEN startipnum AND endipnum \n";

//gravaLog_Captura($sql);

//		$rs = SQLexecuteQuery($sql);
//gravaLog_Captura("Rows: ".pg_numrows($rs));
		if($rs && pg_numrows($rs)>0) {
			$rs_row = pg_fetch_array($rs);
			$country_array['country'] = $rs_row['country'];
			$country_array['city'] = $rs_row['city'];	// $rs_row['city_utf'];
			$country_array['latitude'] = $rs_row['latitude'];
			$country_array['longitude'] = $rs_row['longitude'];
//gravaLog_Captura("Info Found: ".print_r($country_array, true)."\n");
			$ret = 1;
		} else {
//gravaLog_Captura("Info NOT Found: EMPTY\n");
			$ret = -1;
		}
	}
	return $ret;
}
?>