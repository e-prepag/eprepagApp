<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

header("Content-Type: text/html; charset=ISO-8859-1",true);
function isAjax() {return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));}
function block_direct_calling() {
    if(!isAjax()) {
           echo "Chamada não permitida<br>";
           die("Stop");
    }
}
block_direct_calling();


require_once "../../includes/constantes.php";
require_once RAIZ_DO_PROJETO."includes/configuracao.inc";
require_once RAIZ_DO_PROJETO."db/connect.php";
require_once RAIZ_DO_PROJETO."includes/functions.php";

$bds_tipo_usuario	= isset($_POST['bds_tipo_usuario'])	? $_POST['bds_tipo_usuario']	: NULL;
$ug_id				= isset($_POST['ug_id'])			? $_POST['ug_id']				: NULL;
$bds_id_banner		= isset($_POST['bds_id_banner'])	? $_POST['bds_id_banner']		: NULL;

require_once (RAIZ_DO_PROJETO . "class/classBannerDrawShadow.php");
$banner = new BannerDrawShadow($ug_id,$bds_tipo_usuario);
$banner->CapturaBannerEspecifico($bds_id_banner);
$banner->InsereClick();
?>