<?php 
// Livrodjx dit it right
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

require_once "/www/class/class2FA.php";
require_once "/www/includes/main.php";
require_once "/www/class/gamer/classGamesUsuario.php";
require_once "/www/includes/functions.php";


if(isset($_GET["token"]) && !empty($_GET["token"])) {
	
	$token = filter_var($_GET["token"], FILTER_SANITIZE_STRING);
	
	$two_fa = new TwoFactorAuthenticator();
	
	$is_valid = $two_fa->verify_token($token);
	if($is_valid) {
		$new_user = new UsuarioGames();
		$ret = $new_user->adicionarLoginSessionByIdDjx($is_valid['ug_id']);
		
		header("Location: /game/index.php"); 
		exit;
	}
	else {
		header("Location: /game/conta/login.php");
	}
}	
else {
	header("Location: /game/conta/login.php");
}
