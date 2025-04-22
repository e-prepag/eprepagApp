<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);

require_once "../../../includes/constantes.php";
require_once RAIZ_DO_PROJETO . "class/pdv/controller/IndexController.class.php";
require_once RAIZ_DO_PROJETO . "class/business/ClickBannerBO.class.php";

if(!Util::isAjaxRequest() || empty($_GET['id']))
{
    die("Chamada não permitida ou banner inválido.");
}

$clickBanner = new ClickBannerBO();
$clickBanner->insereClickBanner($_GET['id']);
