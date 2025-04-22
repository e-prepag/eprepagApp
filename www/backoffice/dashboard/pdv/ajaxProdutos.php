<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');
require_once "/www/db/connect.php"; 
require_once "/www/db/ConnectionPDO.php"; 
$connection = ConnectionPDO::getConnection()->getLink(); 

if(isset($_POST["type"]) && $_POST["type"] == 2) {
	$sql = "select ogp_id, ogp_nome from tb_operadora_games_produto where ogp_ativo = 1;";
	$query = $connection->prepare($sql);
	$query->execute();
	$result = $query->fetchAll(PDO::FETCH_ASSOC);

	$newResult = [];
	foreach($result as $key => $value){
		$value["ogp_nome"] = utf8_encode($value["ogp_nome"]);
		array_push($newResult, $value);
	}

	echo json_encode($newResult);
	exit;
}

$sql = "select ogp_id, ogp_nome from tb_dist_operadora_games_produto where ogp_ativo = 1;";
$query = $connection->prepare($sql);
$query->execute();
$result = $query->fetchAll(PDO::FETCH_ASSOC);

$newResult = [];
foreach($result as $key => $value){
	$value["ogp_nome"] = utf8_encode($value["ogp_nome"]);
	array_push($newResult, $value);
}

echo json_encode($newResult);
	
?>