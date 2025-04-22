<?php
     
	require '/www/class/pdv/classLink.php'; 

    session_start();
	unset($_SESSION["sendToken"]);
	$classAcesso = new LinkAcesso(null);
	if($classAcesso->verificaFamiliaIps()){
		if(empty($_POST['token']) || !isset($_POST['token'])){
			$_SESSION["error_login"] = ['(06) Não foi enviado nenhum token.'];
			header("location: https://www.e-prepag.com.br/creditos/login-sms.php");
			exit;
		}
		$inform = $classAcesso->verificaAcesso($_POST['token']);
		if(count($inform) == 0){
			if($_SESSION["AuXdist_usuarioGamesOperador_ser"]){
				$_SESSION["dist_usuarioGamesOperador_ser"] = $_SESSION["AuXdist_usuarioGamesOperador_ser"];
			}
				
			if($_SESSION["AuXdist_usuarioGames_ser"]){
				$_SESSION["dist_usuarioGames_ser"] = $_SESSION["AuXdist_usuarioGames_ser"];
			}
			header("location: https://www.e-prepag.com.br/creditos/");
			exit;
		}else{
			$_SESSION["error_login"] = $inform['code'];
			header("location: https://www.e-prepag.com.br/creditos/login-sms.php");
		}
	}else{
		$_SESSION["error_login"] = ['(05) Acesso invalido.'];
		header("location: https://www.e-prepag.com.br/creditos/login-sms.php");
	}
    exit;
?>