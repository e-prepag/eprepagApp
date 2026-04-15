<?php
	
	require_once "/www/class/phpmailer/class.phpmailer.php";
	require_once "/www/class/phpmailer/class.smtp.php";
        require_once "/www/includes/load_dotenv.php";
	
	function disparaEmail($to, $cc, $bcc, $subject, $body_html, $body_plain, $codigoValidacao) {
                $mail = new PHPMailer();
                $mail->IsSMTP();
                $mail->Host     = getenv("smtp_host");
                $mail->SMTPAuth = true;
                $mail->Mailer   = "smtp";
                $mail->Username = getenv("smtp_username");
                $mail->Password = getenv("smtp_password"); //'985856';
                //$mail->SMTPSecure = "ssl";
                $mail->Port     = getenv("smtp_port"); //587;
                
                $mail->From     = getenv("email_suporte");
                $mail->FromName = "E-Prepag";
                // Reply-to
                $mail->AddReplyTo(getenv("email_suporte"));
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
                $enviado = $mail->Send();
                if(!$enviado) {
                        $mensagemLog = "Pagina: Game->Conta->Esqueci minha senha - Erro: Ao enviar e-mail para: {$to} - Erro: " . $mail->ErrorInfo;
                }

        //Mensagem
		//$mensagemLog = "E-mail encaminhado para: {$to} CoDIGO: {$codigoValidacao}";
		
		$arquivoLog = '/www/arquivos_gerados/logs/envioEmailEsqueciMinhaSenha.log';
		
		geraLogEnvioEmail($arquivoLog, $mensagemLog);
		
        return $mensagemLog;
}