<?php 
// Livrodjx dit it right

require_once "/www/includes/pdv/constantes.php";
require_once "/www/includes/pdv/functions.php";
require_once "/www/class/class2FA.php";
require_once "/www/class/pdv/classGamesUsuario.php";
require_once "/www/includes/pdv/main.php";

if($_SERVER["REMOTE_ADDR"] == "201.93.162.169") {
	
	if(isset($_GET["token"]) && !empty($_GET["token"])) {
		
		$token = filter_var($_GET["token"], FILTER_SANITIZE_STRING);
		
		$two_fa = new TwoFactorAuthenticator();
		
		$is_valid = $two_fa->verify_token($token);
		
		if($is_valid != null) {
			var_dump($is_valid);
			$new_user = new UsuarioGames();
			
			$ret = $new_user->adicionarLoginSession_ByID($is_valid['ug_id']);
			var_dump($ret);
			//header("Location: /game/index.php"); 
			exit;
		}
		else {
			//header("Location: /game/conta/login.php");
		}
	}	
	else {
		header("Location: /game/conta/login.php");
	}
}
else {
	
	echo "Site em construção";
}
?> 