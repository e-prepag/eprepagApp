<?php
header('Content-Type: application/json; charset=utf-8');
require_once "/www/db/connect.php"; 
require_once "/www/db/ConnectionPDO.php"; 
$connection = ConnectionPDO::getConnection()->getLink(); 

if($_POST["grafico"] == 2 && !empty($_POST["datainicial"]) && !empty($_POST["datafinal"])){
	
	$dataInicialNew = new DateTime($_POST["datainicial"]);
	$dataInicialNew->sub(new DateInterval('P7D'));
	$dateInicial = $dataInicialNew->format("Y-m-d");
	
	$sql = "select sum(vgm_qtde) as qtde, sum(vgm_valor * vgm_qtde) as total, vgm_nome_produto as nome from tb_dist_venda_games inner join 
	tb_dist_venda_games_modelo on vg_id = vgm_vg_id where date(vg_data_inclusao) between :DTINI and :DTFIN
	and vg_ultimo_status = '5' group by vgm_nome_produto order by qtde desc;";


	$query = $connection->prepare($sql);
	$query->bindValue(":DTINI", $dateInicial);
	$query->bindValue(":DTFIN", $_POST["datafinal"]);
	$query->execute();
	$result = $query->fetchAll(PDO::FETCH_ASSOC);
	
	$newResult = [];
	
	foreach($result as $key => $value){
		$value["nome"] = utf8_encode($value["nome"]);
		array_push($newResult, $value);
	}
	echo json_encode($newResult);
	
}else if($_POST["grafico"] == 3 && !empty($_POST["datainicial"]) && !empty($_POST["datafinal"])){
	
	$dataInicialNew = new DateTime($_POST["datainicial"]);
	$dataInicialNew->sub(new DateInterval('P7D'));
	$dateInicial = $dataInicialNew->format("Y-m-d");
	
	$sql = "select sum(vgm_qtde) as qtde, sum(vgm_valor * vgm_qtde) as total, vgm_nome_produto as nome from tb_dist_venda_games inner join 
	tb_dist_venda_games_modelo on vg_id = vgm_vg_id where date(vg_data_inclusao) between :DTINI and :DTFIN
	and vg_ultimo_status = '5' group by vgm_nome_produto order by qtde desc;";

	$query = $connection->prepare($sql);
	$query->bindValue(":DTINI", $dateInicial);
	$query->bindValue(":DTFIN", $_POST["datafinal"]);
	$query->execute();
	$result = $query->fetchAll(PDO::FETCH_ASSOC);
	
	$newResult = [];
	
	foreach($result as $key => $value){
		$value["nome"] = utf8_encode($value["nome"]);
		array_push($newResult, $value);
	}
	echo json_encode($newResult);
}

?>