<?php
if(empty($_SESSION["iduser_bko"]))
{
        echo "<script>";
        echo "setTimeout('top.location = \'".$url_session_expires."\'', 0);";
        echo "</script>";
        exit;
}

$sql = "select bko_autoriza, bko_local_acesso from usuarios where id=$1";
$result = pg_query_params($connid, $sql, array($_SESSION['iduser_bko']));
$pgrow = pg_fetch_array($result);  
?>
