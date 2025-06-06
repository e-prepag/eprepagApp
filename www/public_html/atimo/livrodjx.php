<?php
	ini_set('memory_limit', '400M');
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	
	require_once "/www/db/connect.php"; 
	require_once "/www/db/ConnectionPDO.php";
	require_once '/www/includes/gamer/inc_sanitize.php'; 
	require_once '/www/includes/gamer/chave.php';
	require_once '/www/includes/gamer/AES.class.php';
	require_once '/www/includes/main.php';
	require_once '/www/includes/gamer/functions.php';
	require "/www/class/classGeraPin.php";
	require_once "/www/class/pdv/classChaveMestra.php";
	require_once "/www/class/class2FA.php";
	
	$two_factor = new TwoFactorAuthenticator("USER", 1333904);
	$sim =  $two_factor->verify_time();
	var_dump($sim);
	
	/*$senha = new Encryption();
	$teste = $senha->decrypt('AFgPEFRUfQJOYG5B');
	echo $teste;*/
	
	/*$meu_teste = new GeraPinVariavel(14, 53, 3, 1);
	$pin = $meu_teste->gerar();
	
	echo $pin;*/
	/*$str = "4069480005198442";
	$objEncryption = new Encryption();
	echo $objEncryption->encrypt($str);*/
	
	/*$chave_mestra = new ChaveMestra();
	$my_chave = $chave_mestra->inserirChaveMestra(19351);*/
	/*$envia_email = new EnvioEmailAutomatico('L', 'ChaveMestra');
	$envia_email->setUgNome(ucwords(strtolower('GAME ACTION'))); 
	$envia_email->setChaveMestra('EF9shxU0J0eT7nE');
	
	$to = 'gerenciagameaction@mail.com, lucas.alexandre@gokeitecnologia.com.br'; //ipojucan_net@hotmail.com
	$cc = "";
	$bcc = "";
	$subject = "E-prepag - Chave Mestra";
	$msg = $envia_email->getCorpoEmail();
	
	enviaEmail3($to, $cc, $bcc, $subject, $msg, "");
	
	echo $msg;*/
	//require_once "/www/class/class2FA.php";
	/*ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	$raiz_do_projeto = "/www/";
	require_once $raiz_do_projeto."class/classEncryption.php";
	
	
		$str = $_POST["password"];
		$objEncryption = new Encryption();
		echo $objEncryption->encrypt($str);
	
	
	*/
	
	/*$senha = new Encryption();
	$teste = $senha->decrypt('EQ0FJgpQLQ4dTn1B');
	echo $teste;*/
	/*$teste = new TwoFactorAuthenticator('USER', 1333904, 'Livrodjx', "lucas.alexandre@gokeitecnologia.com.br");
	
	$precisaReenviar = $teste->verify_time();
	
	var_dump($precisaReenviar);*/
	//$teste->send_email();
	/*$chaveMestra = new ChaveMestra();
		
	$sql = "SELECT usuario, chave FROM dist_usuarios_games_chave WHERE chave = :CHAVE";
	$query = $connection->prepare($sql);
	$query->bindValue(':CHAVE', "'RxQk'btfm*G@1x");
	$query->execute();
	
	$results = $query->fetch(PDO::FETCH_ASSOC);
	
	$result = $chaveMestra->verificaSenha($results['usuario'], $results['chave']);
	var_dump($results);
	
	/*foreach($results as $id => $pdv) {
		$ug_id = $pdv['ug_id'];
		$ug_nome = ucwords(strtolower(utf8_encode($pdv['ug_nome'])));
		$ug_email = strtolower($pdv['ug_email']);
		
		$chaveMestra = new ChaveMestra();
		$minha_chave = $chaveMestra->inserirChaveMestra($ug_id);
		
		$envia_email = new EnvioEmailAutomatico('L', 'ChaveMestra');
		$envia_email->setUgNome($ug_nome); 
		$envia_email->setChaveMestra($minha_chave);
		
		$to = $ug_email;
		$cc = "";
		$bcc = "";
		$subject = "E-prepag - Chave Mestra";
		$msg = $envia_email->getCorpoEmail();
		
		$sql = "SELECT usuario FROM dist_usuarios_games_chave WHERE usuario = :UG_ID";
		$query = $connection->prepare($sql);
		$query->bindValue(":UG_ID", $ug_id);
		$query->execute();
		
		if($query->rowCount() == 0) {
			enviaEmail3($to, $cc, $bcc, $subject, $msg, "");
		}
	}*/
	
	/*$sql = "SELECT * FROM dist_usuarios_games WHERE ug_id = 7938;";
	$query = $connection->prepare($sql);
	$query->execute();
	$results = $query->fetchAll(PDO::FETCH_ASSOC);
	
	foreach($results as $id => $pdv) {
		$ug_id = $pdv['ug_id'];
		$ug_nome = ucwords(strtolower(utf8_encode($pdv['ug_nome_fantasia'])));
		$ug_email = strtolower($pdv['ug_email']);
		
		$chaveMestra = new ChaveMestra();
		$minha_chave = $chaveMestra->inserirChaveMestra($ug_id);
		
		$envia_email = new EnvioEmailAutomatico('L', 'ChaveMestra');
		$envia_email->setUgNome($ug_nome); 
		$envia_email->setChaveMestra($minha_chave);
		
		$to = "$ug_email, lucas.alexandre@gokeitecnologia.com.br, andresilva@gokeitecnologia.com.br";
		$cc = "";
		$bcc = "";
		$subject = "E-prepag - Chave Mestra";
		$msg = $envia_email->getCorpoEmail();
		
		enviaEmail3($to, $cc, $bcc, $subject, $msg, "");
		
	}*/
?> 