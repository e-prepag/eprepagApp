<?php
//error_reporting(E_ALL & ~E_NOTICE);

$ipVisitante = $_SERVER['REMOTE_ADDR'];

// IP que você deseja redirecionar para fora do site
$ipsBloqueados = [
'37.120.246.155',
'169.150.198.75',
'143.137.155.249',
'149.102.233.180',
'149.102.233.186',
'149.102.233.196',
'149.102.233.198',
'149.102.233.206',
'149.102.233.224',
'149.102.233.230',
'149.102.233.233',
'169.150.220.146',
'169.150.220.152',
'169.150.220.156',
'169.150.220.159',
'179.102.140.124',
'200.173.196.150',
'200.173.198.129',
'200.173.202.152',
'200.173.203.121',
'200.173.208.169',
'200.217.103.187',
'131.221.99.11',
'131.221.99.114',
'149.78.184.205',
'149.78.184.206',
'169.150.198.77',
'169.150.198.90',
'169.150.198.91',
'177.101.92.6',
'177.51.82.153',
'177.51.84.213',
'179.102.129.22',
'189.127.60.146',
'191.96.5.109',
'191.96.5.113',
'191.96.5.156',
'191.96.5.189',
'191.96.5.193',
'191.96.5.62',
'191.96.5.64',
'191.96.5.80',
'191.96.5.94',
'20.163.125.12',
'85.10.192.143',
'169.150.220.150',
'191.96.5.162',
'149.102.234.144',
'191.177.183.104',
'177.15.0.30',
'200.236.201.216',
'189.38.34.49',
'181.191.1.159',
'181.189.121.48',
'191.96.5.81',
'187.180.250.52',
'169.150.220.157',
'143.208.252.10',
'191.96.4.205',
'177.157.235.127',
'168.196.138.54',
'189.40.91.86',
'152.234.138.197',
'191.96.5.48',
'149.102.233.236',
'177.130.155.221',
'187.85.148.199',
'201.15.79.195',
'191.96.5.131',
'179.109.45.68',
'169.150.220.132',
'189.97.79.208',
'168.205.180.193',
'191.163.167.34',
'187.183.51.38',
'177.185.110.16',
'187.19.231.166',
'200.106.214.138',
'191.96.5.87',
'191.96.4.243',
'187.126.106.71',
'138.117.116.50',
'177.8.132.24',
'177.12.181.138',
'45.174.18.85',
'191.181.56.135',
'168.181.29.213',
'190.89.111.178',
'168.121.25.156',
'189.40.91.2',
'149.102.234.143',
'169.150.220.162',
'191.96.5.67',
'149.102.233.204',
'191.96.5.40',
'191.96.5.51',
'191.96.5.232',
'191.96.5.170',
'149.102.234.129',
'191.96.13.188',
'191.96.4.93',
'169.150.220.140',
'200.173.201.155',
'200.173.200.32',
'200.173.208.147',
'191.245.78.249',
'200.173.206.252',
'191.245.65.18',
'191.245.75.164',
'200.173.209.153',
'200.173.201.27',
'169.150.220.172',
'191.96.5.28',
'149.102.233.187',
'149.102.234.130',
'149.102.233.215',
'149.102.233.193',
'191.96.5.24',
'191.96.5.171'];

// Verifica se o IP do visitante corresponde ao IP bloqueado
if (in_array($ipVisitante, $ipsBloqueados)) {
	
    // Redireciona o visitante para uma página externa
    header('Location: https://www.google.com', true, 302);
    die();
	
}

ob_start();
@session_start();

require_once 'constantes.php';
require_once $raiz_do_projeto.'db/connect.php';
require_once $raiz_do_projeto.'db/ConnectionPDO.php';
require_once $raiz_do_projeto.'includes/functions.php';
require_once $raiz_do_projeto.'class/phpmailer/PHPMailerAutoload.php';
require_once $raiz_do_projeto.'class/util/EmailEnvironment.class.php';
require_once $raiz_do_projeto.'class/classEmailAutomatico.php';
require_once $raiz_do_projeto.'class/classEncryption.php';
require_once $raiz_do_projeto.'includes/configIP.php';
require_once $raiz_do_projeto.'banco/bradesco/config.inc.urls_bradesco.php';
require_once $raiz_do_projeto.'banco/itau/inc_config.php';

	// Strict-Transport-Security (HSTS)
	header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");

	// Remove Exibição da Versão
	header_remove("X-Powered-By");

	// X-Frame-Options
	header("X-Frame-Options: SAMEORIGIN");

	// X-Content-Type-Options
	header("X-Content-Type-Options: nosniff");

	// Referrer-Policy
	header("Referrer-Policy: same-origin");

	// Permissions-Policy
	header("Permissions-Policy: geolocation=(self)");

?>