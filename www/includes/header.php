<?php
if(empty($_SESSION["iduser_bko"]))
{
        echo "<script>";
        echo "setTimeout('top.location = \'".$url_session_expires."\'', 0);";
        echo "</script>";
        exit;
}

$sql = "select bko_autoriza, bko_local_acesso from usuarios where id='".$_SESSION['iduser_bko']."'";
$result = pg_exec($connid, $sql);   
$pgrow = pg_fetch_array($result);  
?>
