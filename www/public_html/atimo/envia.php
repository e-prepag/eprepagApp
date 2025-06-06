<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "/www/includes/constantes.php";
require_once "/www/includes/gamer/functions.php";
require_once "/www/db/connect.php";
require_once "/www/db/ConnectionPDO.php";
require_once "/www/class/classEmailAutomatico.php";
require_once "/www/class/phpmailer/class.phpmailer.php";
require_once "/www/includes/configIP.php";
require_once "/www/class/phpmailer/class.smtp.php";
require_once "/www/class/pdv/classChaveMestra.php";

$envia_email = new EnvioEmailAutomatico('P', 'ChaveMestra');

//$gerador = new ChaveMestra();
//$chave = $gerador->inserirChaveMestra(19351);
//echo $chave;
//exit;

$envia_email->setUgNome("STAR VISION");
$envia_email->setChaveMestra("1B8j7SSpYDQTQhf");

$to = strtolower("lojastarvision@gmail.com");
$cc = ""; //andresilva@gokeitecnologia.com.br
$bcc = "";
$subject = "E-prepag - Cуdigo de Segundo Fator de Autenticaзгo";
$msg = $envia_email->getCorpoEmail();
var_dump(enviaEmail3($to, $cc, $bcc, $subject, $msg, ""));
 


?>