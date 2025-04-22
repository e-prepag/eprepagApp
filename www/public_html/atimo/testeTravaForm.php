<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

	require_once "/www/includes/constantes.php";
	require_once "/www/includes/gamer/constantes.php";
	require_once "/www/includes/main.php";
	require_once "/www/includes/gamer/main.php";
	require_once "/www/includes/gamer/functions.php";
	
	$objEncryption = new Encryption();
            $senha = 'Jefferson@2024TrocarSenha';
			echo $senha . '<br><br><br>';
            $senha = $objEncryption->encrypt($senha);
			
			
			echo $senha;