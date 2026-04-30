<?php
@session_start();
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
include "Captcha.class.php";
include "Functions.php";
/*
:::::::::::::::::::::::::::::::::::::::::::::::::
::                                             ::
::   And turn the http header into image/gif   ::
::                                             ::
:::::::::::::::::::::::::::::::::::::::::::::::::
*/
Header ( 'Content-type: image/gif' );

// Pega as fontes no diretorio
$fontDir = __DIR__ . "/fonts/";
if ( $dh = opendir ( $fontDir ) ) {
	while ( false !== ( $dat = readdir ( $dh ) ) ) {
		if ( $dat != "." && $dat != ".." ) {
			$fonts [ ] = $fontDir . $dat;
		}
	}
	closedir ( $dh );
} else {
	error_log ( "CaptchaImage3: nao foi possivel abrir diretorio de fontes: " . $fontDir );
}

// executa a classe
$font = "";
if ( isset ( $fonts ) && is_array ( $fonts ) && count ( $fonts ) > 0 ) {
	$font = $fonts [ rand ( 0, count ( $fonts ) - 1 ) ];
} else {
	error_log ( "CaptchaImage3: nenhuma fonte encontrada, usando fallback GD" );
}
$IMG = new Captcha ( generateRandomCode() , $font, "ff0000" );

// Gera o grafico
echo $IMG->AnimatedOut ( );


// Gera a palavra com caracteres aleatorios
function generateRandomCode(){
	$primeiraLetra = chr(rand(97, 122));
	$segundaLetra = chr(rand(97, 122));
	$terceiraLetra = chr(rand(97, 122));
	
	$palavraCodigo = $primeiraLetra.$segundaLetra.$terceiraLetra;
	$_SESSION['palavraCodigo'] = $palavraCodigo;
	return $palavraCodigo;

}

?>
