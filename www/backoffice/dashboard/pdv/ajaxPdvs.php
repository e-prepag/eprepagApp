<?php

header('Content-Type: application/json; charset=utf-8');
require_once "/www/db/connect.php"; 
require_once "/www/db/ConnectionPDO.php"; 

$connection = ConnectionPDO::getConnection()->getLink();
ini_set('memory_limit', '8192M');
set_time_limit(0);


$sql = "select * from dist_usuarios_games where ug_ativo = 1;";
$query = $connection->prepare($sql);
$query->execute();
$result = $query->fetchAll(PDO::FETCH_ASSOC);

var_dump($result);
exit;


?>