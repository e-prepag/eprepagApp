<?php
require_once '../../../../includes/constantes.php';
if(isset($_GET["csv"]) && file_exists($raiz_do_projeto."arquivos_gerados/csv/".$_GET['csv'])){
    //header('Content-Type: application/csv; charset=utf-8');
    header('Content-Type: application/csv; charset=iso-8859-1');
    header('Content-Disposition: attachment; filename=relatorio.csv');
    header('Pragma: no-cache');
    //readfile($_SERVER['DOCUMENT_ROOT']."\bkov2_prepag\dist_commerce\csv\\".$_GET['csv']);
    $str = file_get_contents($raiz_do_projeto."arquivos_gerados/csv/".$_GET['csv']);
    
    echo $str;
//    die;
}elseif(isset($_REQUEST['filename'])){
    $filename = $_REQUEST['filename'];
    
    header("Content-Description: File Transfer");
    header('Content-Type: application/octet-stream;');
    header('Content-Disposition: attachment; filename="'. $filename);
    
    echo base64_decode($_REQUEST['content']);
    
} else{
    echo "Erro na geraчуo do CSV";
}
