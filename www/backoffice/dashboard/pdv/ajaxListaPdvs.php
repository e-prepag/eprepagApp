<?php
header('Content-Type: application/json; charset=utf-8');
require_once "/www/db/connect.php"; 
require_once "/www/db/ConnectionPDO.php"; 
$connection = ConnectionPDO::getConnection()->getLink(); 

$sql = "select ug_id, ug_nome_fantasia from dist_usuarios_games where ug_ativo = 1 and ug_nome_fantasia <> '' order by ug_nome_fantasia;";
$query = $connection->prepare($sql);
$query->execute();
$result = $query->fetchAll(PDO::FETCH_ASSOC);

$newResult = [];
foreach($result as $key => $value){
	$value["ug_nome_fantasia"] = utf8_encode($value["ug_nome_fantasia"]);
	array_push($newResult, $value);
}

echo json_encode($newResult);

?>

