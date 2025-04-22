<?php
require_once '../../includes/constantes.php';
header("Content-Type: text/html; charset=ISO-8859-1",true);

if(!empty($_GET['var'])){
    $file = $raiz_do_projeto."includes/templates/".$_GET['var'];
    if(file_exists($file))
        echo file_get_contents($file);
}
