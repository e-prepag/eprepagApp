<?php

    ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);

	require_once '/www/includes/constantes.php';
	require_once $raiz_do_projeto . "includes/main.php";
	require_once $raiz_do_projeto . "includes/gamer/main.php";
	require_once $raiz_do_projeto . "class/classIntegracaoPin.php";
	require_once $raiz_do_projeto . "class/classIntegracaoPinCash.php";
	require_once $raiz_do_projeto . "includes/inc_functions.php";
	require_once $raiz_do_projeto . "class/classPinsStore.php";       


	$ps = new Pins_Store();

	
	//instanciando a classe de cryptografia
	$chave256bits = new Chave();
	//chave Publisher
	$chavePub = new AES($chave256bits->retornaChavePub());
	//chave Cash
	$eas = new AES($chave256bits->retornaChave());

     //$pin = base64_encode($eas->encrypt('4535171711374847'));
     $pin = $eas->decrypt(base64_decode("lpJFNUbZy8oY792SaFAs4w=="));

    var_dump($pin); 

?>