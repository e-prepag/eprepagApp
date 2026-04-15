<?php

require_once "/www/includes/load_dotenv.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '/www/vendor/autoload.php';

function disparaEmail($to, $cc, $bcc, $subject, $body_html, $body_plain, $codigoValidacao)
{
        $mensagemLog = "";
        $mail = new \PHPMailer\PHPMailer\PHPMailer();
        $mail->isSMTP();
        $mail->Host     = getenv("smtp_host");
        $mail->SMTPAuth = true;
        $mail->Mailer   = "smtp";
        $mail->Username = getenv("smtp_username");
        $mail->Password = getenv("smtp_password");
        //$mail->SMTPSecure = "ssl";
        $mail->Port     = getenv("smtp_port");

        $mail->From     = getenv("email_suporte");
        $mail->FromName = "E-Prepag";
        $mail->addReplyTo(getenv("email_suporte"));

        if ($to && trim($to) != "") {
                $toAr = explode(",", $to);
                foreach ($toAr as $recipient) {
                        $mail->addAddress(trim($recipient));
                }
        }

        if ($cc && trim($cc) != "") {
                $ccAr = explode(",", $cc);
                foreach ($ccAr as $ccRecipient) {
                        $mail->addCC(trim($ccRecipient));
                }
        }

        if ($bcc && trim($bcc) != "") {
                $bccAr = explode(",", $bcc);
                foreach ($bccAr as $bccRecipient) {
                        $mail->addBCC(trim($bccRecipient));
                }
        }

        $mail->Subject = $subject;
        $mail->isHTML(true);
        $mail->Body    = $body_html;
        $mail->AltBody = $body_plain;

        $enviado = $mail->send();
        if (!$enviado) {
                $mensagemLog = "Erro: Ao enviar e-mail para: {$to} - Erro: " . $mail->ErrorInfo;
        }

        $arquivoLog = '/www/arquivos_gerados/logs/envioEmailEsqueciMinhaSenha_pdv.log';
        geraLogEnvioEmail($arquivoLog, $mensagemLog);

        return $enviado;
}
