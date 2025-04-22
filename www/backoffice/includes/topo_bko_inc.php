<?php 
//error_reporting(E_ALL);
//ini_set("display_errors", 1);

session_start();

header("Content-Type: text/html; charset=ISO-8859-1",true);

require_once $raiz_do_projeto."includes/inc_register_globals.php";	

$url = $_SERVER['HTTPS']=="on" ? "https://" : "http://";
$url .= $_SERVER['SERVER_NAME'];

$webstring = "https://".$_SERVER['SERVER_NAME'] . ":" . $_SERVER['SERVER_PORT'];
require_once $raiz_do_projeto."includes/access_functions.php";
require_once $raiz_do_projeto.'includes/configIP.php';
require_once $raiz_do_projeto.'includes/configuracaoBO.php';
require_once $raiz_do_projeto."db/connect.php";
require_once $raiz_do_projeto."db/ConnectionPDO.php";
require_once $raiz_do_projeto."includes/header.php";
require_once $raiz_do_projeto."includes/security.php";
require_once $raiz_do_projeto."includes/functions.php";
require_once $raiz_do_projeto."includes/constantes.php";

?>