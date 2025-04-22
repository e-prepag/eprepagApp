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
if ( $dh = opendir ( "fonts/" ) ) {
	while ( false !== ( $dat = readdir ( $dh ) ) ) {
		if ( $dat != "." && $dat != ".." ) {
			$fonts [ ] = "fonts/$dat";
		}
	}
	closedir ( $dh );
}

// executa a classe
$IMG = new Captcha ( generateRandomCode() , $fonts [ rand ( 0, ( count ( $fonts ) ) - 1 ) ], "ff0000" );

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
