<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."banco/bexs/config.inc.bexs.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/gamer/main.php";

if($_POST['id_arquivo']){
    
    $id_arq = $_POST['id_arquivo'];
    
    $sql = "UPDATE remessa_bexs set status = ".$GLOBALS['ARRAY_STATUS']['CANCELADA'].", data_atualizacao = NOW() WHERE id_arquivo = '". $id_arq. "';";
    
    $rs = SQLexecuteQuery($sql);

    if($rs){
        echo "A remessa de ID <strong>". $id_arq. "</strong> teve seu status atualizado para CANCELADA";
    } else{
        echo "Problema ao atualizar o status da remessa de ID <strong>". $id_arq."</strong>. Contacte o problema ao setor de T.I";
    }

} else{
    echo "Problema ao capturar a remessa selecionada. Contacte o problema ao setor de T.I";
}
