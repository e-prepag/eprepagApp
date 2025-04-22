<?php

if(!isset($raiz_do_projeto)){
    require_once '../../includes/constantes.php';
}

$dir = array("cache" => $raiz_do_projeto."public_html/cache/",
             "bkov"  => $raiz_do_projeto."arquivos_gerados/csv/");

if(isset($_GET["downloadCsv"]) && $_GET["downloadCsv"] == 1)
{
    ob_end_clean();
    ob_clean();
    header("Content-type: text/csv");
    header("Content-Disposition: attachment; filename=report_e-prepag.csv");
    header("Pragma: no-cache");
    header("Expires: 0");
//
    echo $objCsv->getCsv();
    die;
}elseif(isset($_GET["csv"]) && array_key_exists($_GET["dir"], $dir) && file_exists($dir[$_GET["dir"]].$_GET['csv']))
{
    //header('Content-Type: application/csv; charset=utf-8');
    header('Content-Type: application/csv; charset=iso-8859-1');
    header('Content-Disposition: attachment; filename=report_e-prepag.csv');
    header('Pragma: no-cache');
    $str = file_get_contents($dir[$_GET["dir"]].$_GET['csv']);
    
    echo $str;
//    die;
}else{
    echo "Erro na geração do CSV";
}
