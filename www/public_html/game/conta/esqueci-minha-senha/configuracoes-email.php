<?php

require_once "/www/includes/load_dotenv.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '/www/vendor/autoload.php';

function disparaEmail($to, $cc, $bcc, $subject, $body_html, $body_plain, $codigoValidacao)
{

        $mensagemLog = "";
        $mail = null;

        try {

                $emailSuporte = (string)getenv("email_suporte");

                if (!filter_var($emailSuporte, FILTER_VALIDATE_EMAIL)) {
                        throw new Exception("Remetente email_suporte invalido ou nao configurado.");
                }


                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host     = (string)getenv("smtp_host");
                $mail->SMTPAuth = true;
                $mail->Mailer   = "smtp";
                $mail->Username = (string)getenv("smtp_username");
                $mail->Password = (string)getenv("smtp_password");
                //$mail->SMTPSecure = "ssl";
                $mail->Port     = (int)getenv("smtp_port");
                $mail->CharSet = 'UTF-8';
                $mail->Timeout  = 20;

                $mail->setFrom($emailSuporte, "E-Prepag");
                $mail->addReplyTo($emailSuporte);


                $destinatariosAdicionados = 0;

                $adicionaDestinatarios = function ($emails, callable $callback) use (&$destinatariosAdicionados) {
                        if (!$emails || trim((string)$emails) == "") {
                                return;
                        }

                        $emailsAr = explode(",", (string)$emails);
                        foreach ($emailsAr as $email) {
                                $email = trim($email);
                                if ($email == "") {
                                        continue;
                                }

                                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                        throw new Exception("Destinatario invalido: {$email}");
                                }

                                $callback($email);
                                $destinatariosAdicionados++;
                        }
                };

                $adicionaDestinatarios($to, function ($email) use ($mail) {
                        $mail->addAddress($email);
                });

                $adicionaDestinatarios($cc, function ($email) use ($mail) {
                        $mail->addCC($email);
                });

                $adicionaDestinatarios($bcc, function ($email) use ($mail) {
                        $mail->addBCC($email);
                });

                if ($destinatariosAdicionados == 0) {
                        throw new Exception("Nenhum destinatario valido informado.");
                }


                $mail->Subject = (string)$subject;
                $mail->isHTML(true);
                $mail->Body    = (string)$body_html;
                $mail->AltBody = (string)$body_plain;

                $mail->send();
        } catch (\Throwable $e) {
                //$erroPhpmailer = $mail instanceof PHPMailer && $mail->ErrorInfo ? " - PHPMailer: " . $mail->ErrorInfo : "";
                $mensagemLog = "Pagina: Game->Conta->Esqueci minha senha - Erro: Ao enviar e-mail para: {$to}";
                //$mensagemLog .= " - Erro: " . $e->getMessage() . $erroPhpmailer;
        }

        $arquivoLog = '/www/arquivos_gerados/logs/envioEmailEsqueciMinhaSenha.log';
        if (function_exists("geraLogEnvioEmail")) {
                geraLogEnvioEmail($arquivoLog, $mensagemLog);
        }
        error_log($mensagemLog);
        return $mensagemLog;
}
