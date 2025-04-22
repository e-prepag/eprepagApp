<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');
require_once "/www/db/connect.php"; 
require_once "/www/db/ConnectionPDO.php"; 
$connection = ConnectionPDO::getConnection()->getLink(); 

$myPeriodo = "";

switch($_POST["periodo"]) {
	
	case 'week':
		$myPeriodo = "1 week";
		break;
		
	case 'day':
		$myPeriodo = "15 day";
		break;
		
	case 'month':
		$myPeriodo = "1 month";
		break;
	
	default:
		$myPeriodo = "1 week";
		break;
}

$sql = "select sum(vgm_valor * vgm_qtde) as quantidade, vgm_nome_produto as nome from tb_venda_games inner 
join tb_venda_games_modelo on vg_id = vgm_vg_id WHERE vg_data_inclusao >= DATE_TRUNC('week', NOW() - INTERVAL '".$myPeriodo."') 
GROUP BY nome order by quantidade desc;";


$query = $connection->prepare($sql);
$query->execute();
$result = $query->fetchAll(PDO::FETCH_ASSOC);

$newResult = [];

foreach($result as $key => $value){
	$value["nome"] = utf8_encode($value["nome"]);
	array_push($newResult, $value);
}

echo json_encode($newResult);

?>