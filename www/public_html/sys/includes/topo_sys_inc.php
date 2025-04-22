<?php
//error_reporting(E_ALL & ~E_NOTICE);

session_start();

require_once $raiz_do_projeto . "includes/inc_register_globals.php";		

if($_SERVER['HTTPS']!="on") {
    Header("Location: https://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']);
    die();
} //end if($_SERVER['HTTPS']!="on") 

$webstring = "http".(($_SERVER['HTTPS']=='on') ? 's' : '')."://".$_SERVER['SERVER_NAME'];// . ":" . $_SERVER['SERVER_PORT'];
$raiz_do_sys = $raiz_do_projeto . "public_html/sys/";

if($_SERVER['SCRIPT_NAME'] <> "/sys/admin/pins/situacao_pin.php") {       
    require_once $raiz_do_sys . "includes/functions.php";
} //end if($_SERVER['SCRIPT_NAME'] <> "/sys/admin/pins/situacao_pin.php")
else {
    require_once $raiz_do_sys . "admin/stats/functions.php";
}//end else do if($_SERVER['SCRIPT_NAME'] <> "/sys/admin/pins/situacao_pin.php")
require_once $raiz_do_projeto.'includes/configIP.php';
require_once $raiz_do_sys . "includes/configuracao.php";
require_once $raiz_do_projeto."db/connect.php";
require_once $raiz_do_projeto."db/ConnectionPDO.php";
require_once $raiz_do_sys . "includes/header.php";
require_once $raiz_do_sys . "includes/security.php";
require_once $raiz_do_sys . "includes/languages.php";
require_once $raiz_do_projeto."class/classDescriptionReport.php";
require_once $raiz_do_projeto."class/business/SistemaBO.class.php";
