<?php
header('Content-Type: application/json; charset=utf-8');
require_once "/www/db/connect.php"; 
require_once "/www/db/ConnectionPDO.php"; 
$connection = ConnectionPDO::getConnection()->getLink(); 

if(isset($_POST["type"]) && $_POST["type"] == 2 && isset($_POST["codigo_cnae"]) && isset($_POST["aprovado_cnae"])) {
	
	$sql = "update cnae set aprovado_cnae = :APROV_CNAE where codigo_cnae = :COD_CNAE;";
	$query = $connection->prepare($sql);
	$query->bindValue(":APROV_CNAE", $_POST["aprovado_cnae"]);
	$query->bindValue("COD_CNAE", $_POST["codigo_cnae"]);
	$query->execute();
	
	echo json_encode(["Alterado com sucesso"]);
	exit;
}

$sql = "select * from cnae;";
$query = $connection->prepare($sql);
$query->execute();
$result = $query->fetchAll(PDO::FETCH_ASSOC);
$cnaes = [];

foreach($result as $key => $cnae) {
	$cnae["atividade_cnae"] = utf8_encode($cnae["atividade_cnae"]);
	
	array_push($cnaes, $cnae);
}

echo json_encode($cnaes);
?> 