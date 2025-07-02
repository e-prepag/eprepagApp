<?php
	
	require_once "/www/class/phpmailer/class.phpmailer.php";
	require_once "/www/class/phpmailer/class.smtp.php";
	
	function disparaEmail($to, $cc, $bcc, $subject, $body_html, $body_plain, $codigoValidacao) {
                $mail = new PHPMailer();
                $mail->IsSMTP();
                $mail->Host     = "email-smtp.sa-east-1.amazonaws.com";
                $mail->SMTPAuth = true;
                $mail->Mailer   = "smtp";
                $mail->Username = 'AKIAUYOIQI7LSCTC6LUP';
                $mail->Password = 'BIFYsYF5+PhgFer64wPmfalJyRQXhukM3HVDoNO17giB'; //'985856';
                //$mail->SMTPSecure = "ssl";
                $mail->Port     = 587; //587;
                
                $mail->From     = "suporte@e-prepag.com.br";
                $mail->FromName = "E-Prepag";
                // Reply-to
                $mail->AddReplyTo('suporte@e-prepag.com.br');
                // To
                if ($to && trim($to) != "") {
                        $toAr = explode(",", $to);
                        foreach ($toAr as $recipient) {
                        $mail->AddAddress(trim($recipient));
                        }
                }
                
                // Cc
                if ($cc && trim($cc) != "") {
                        $ccAr = explode(",", $cc);
                        foreach ($ccAr as $ccRecipient) {
                        $mail->AddCC(trim($ccRecipient));
                        }
                }
                
                // Bcc
                if ($bcc && trim($bcc) != "") {
                        $bccAr = explode(",", $bcc);
                        foreach ($bccAr as $bccRecipient) {
                        $mail->AddBCC(trim($bccRecipient));
                        }
                }
        
                $mail->Subject = $subject;
                $mail->isHTML(true);
                $mail->Body    = $body_html;
                $mail->AltBody = $body_plain;

                // Enviar e capturar o resultado
                if(!$mail->Send()) {
                        $mensagemLog = "Erro: Ao enviar e-mail para: {$to} - Erro: " . $mail->ErrorInfo;
                } else {
                        $mensagemLog = "Sucesso: E-mail encaminhado para: {$to} CÓDIGO: {$codigoValidacao}";
                }


        //Mensagem
		//$mensagemLog = "E-mail encaminhado para: {$to} C�DIGO: {$codigoValidacao}";
		
		$arquivoLog = 'envioEmailEsqueciMinhaSenha.log';
		
		geraLogEnvioEmail($arquivoLog, $mensagemLog);
		
        return $mail->Send();
}