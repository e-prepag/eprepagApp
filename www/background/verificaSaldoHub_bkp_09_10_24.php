<?php

    //ini_set('display_errors', 1);
    //ini_set('display_startup_errors', 1);
    //error_reporting(E_ALL);

	require_once "/www/includes/constantes.php";
	require_once "/www/includes/gamer/constantes.php";
	require_once "/www/includes/main.php";
	require_once "/www/includes/gamer/main.php";
	require_once "/www/includes/gamer/functions.php";
	
	$enviaEmail = function($email, $titulo, $msg){
	
		$mail = new PHPMailer();
		//-----Altera��o exigida pela BaseNet(11/2017)-------------//
		$mail->Host     = "smtp.basenet.com.br";
		//---------------------------------------------------------//
		$mail->Mailer   = "smtp";
		$mail->From     = "suporte@e-prepag.com.br";
		$mail->SMTPAuth = true;     // turn on SMTP authentication
		$mail->Username = 'suporte@e-prepag.com.br';  // a valid email here
		$mail->Password = '@AnQ1V7hP#E7pQ31'; //'985856'; //'850637'; 
		$mail->FromName = "E-Prepag";	// " (EPP)"

		//-----Altera��o exigida pela BaseNet(11/2017)-------------//
		$mail->IsSMTP();
		$mail->SMTPSecure = "ssl";
		$mail->Port     = 465;
		//---------------------------------------------------------//        
		// Overwrite smt details for dev version cause e-prepag.com.br server reject it
		// When run bat files there is not ip address so we need use COMPUTERNAME to check
		//Comentar aki quando problema no email
		if(checkIP() || (class_exists('EmailEnvironment')  && EmailEnvironment::serverId() == 1)) {
			//  $mail->SMTPDebug  = 1; descomentar para debugar 
			$mail->IsSMTP();
			$mail->SMTPSecure = "ssl";
			$mail->Host     = "email-ssl.com.br";
			$mail->Port     = 465;
			$mail->From     = "send@e-prepag.com";
			$mail->Username = 'send@e-prepag.com';
			$mail->Password = 'sendeprepag2013';
			}

		$mail->AddReplyTo('suporte@e-prepag.com.br');
		
		if($email && trim($email) != ""){
            $toAr = explode(",", $email);
            for($i = 0; $i < count($toAr); $i++) $mail->AddAddress($toAr[$i]);
        }
		
		//$mail->AddAddress($email);
		$mail->Subject = $titulo;
		$mail->isHTML();
		$mail->Body    = $msg;
		$mail->AltBody = $body_plain;

		$sret = $mail->Send();	
		return $sret;

	};

	$url = "http://ws.hubdodesenvolvedor.com.br/v2/saldo?info&token=104048520UeLqsXgHvd187856448";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch,CURLOPT_TIMEOUT,5);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,10);
	$result = curl_exec($ch);
	curl_close($ch); 
	
	$file = fopen("/www/log/hubdodesenvolvedor.txt", "a+");
	fwrite($file, "DATA: ".date("d-m-Y H:i:s")."\n");
	fwrite($file, "CONTEUDO: ".$result."\n");
	fwrite($file, str_repeat("*", 50)."\n");
	fclose($file);

    if(!empty($result)){
        $dados = json_decode($result, true);
		$mensagem = "Data: ".date("d-m-Y H:i:s")."<br><br> O saldo de consulta do hub do desenvolvedor está <b>baixo</b>, faça uma recarga.<br><br> <b>Saldo atual:</b> ".$dados["result"][0][0]["saldo"]."<br><br> <b>Link:</b> https://www.hubdodesenvolvedor.com.br/entrar";
        if($dados["result"][0][0]["saldo"] <= 100 && isset($dados["result"][0][0]["saldo"]) && !empty($dados["result"][0][0]["saldo"])){
            $retorno = $enviaEmail('daniela.oliveira@e-prepag.com.br', 'Saldo Hud do desenvolvedor', utf8_decode($mensagem));
			if(!$retorno){
				$enviaEmail('daniela.oliveira@e-prepag.com.br', 'Saldo Hud do desenvolvedor', utf8_decode($mensagem));
			}
        }
		//$enviaEmail('andresilva@gokeitecnologia.com.br', 'Saldo Hud do desenvolvedor', utf8_decode($mensagem));
    }
exit;

?>