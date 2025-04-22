<?php
/*
:::::::::::::::::::::::::::::::::::::::::::::::::
::                                             ::
::         CAPTCHA Validation projects         ::
::                                             ::
::             2007 02. 01. 18.24.             ::
::                                             ::
::                                             ::
::                                             ::
::                                             ::
:::::::::::::::::::::::::::::::::::::::::::::::::

:::::::::::::::::::::::::::::::::::::::::::::::::
::                                             ::
::          Include required classes           ::
::                                             ::
:::::::::::::::::::::::::::::::::::::::::::::::::
*/
require_once "../../../includes/constantes.php";
require_once DIR_CLASS."Captcha.class.php";
require_once "Functions.php";
/*
:::::::::::::::::::::::::::::::::::::::::::::::::
::                                             ::
::   And turn the http header into image/gif   ::
::                                             ::
:::::::::::::::::::::::::::::::::::::::::::::::::
*/
Header ( 'Content-type: image/gif' );

$aforecolors = array("ff0000", "#6600FF", "#00CC00", "#009900", "#CC66FF", "#330099", "#FF3300", "#993300");
$irand_color = rand(0,count($aforecolors)-1);

if ( $dh = opendir ( "fonts/" ) ) {
	while ( false !== ( $dat = readdir ( $dh ) ) ) {
		if ( $dat != "." && $dat != ".." ) {
			$fonts [ ] = "fonts/$dat";
		}
	}
	closedir ( $dh );
}
$irand_font = rand(0,count($fonts)-1);

//echo $_GET [ 'uid' ]." -> ".translateCode($_GET [ 'uid' ])."<br>";
//die();

if ( $_GET [ 'uid' ] ) {
	$UID = explode ( ";", $_GET [ 'uid' ] );
    $IMG = new Captcha ( translateCode($UID [ 0 ]) , $fonts [$irand_font], $aforecolors[$irand_color] );

    echo $IMG->AnimatedOut ( );
}

	function translateCode($sCode) {
                $stmp = "";
		for($i=0;$i<strlen($sCode);$i+=3) {
			$schar = (int)(substr($sCode,$i,3))-($i/3);
			$stmp .= chr($schar);
		}
		return $stmp;
	}

?>
