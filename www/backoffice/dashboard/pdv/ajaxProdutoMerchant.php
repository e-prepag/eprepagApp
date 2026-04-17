<?php
require_once __DIR__ . "/../../../includes/pdv_encoding.php";
header('Content-Type: application/json; charset=utf-8');
require_once "/www/db/connect.php"; 
require_once "/www/db/ConnectionPDO.php"; 
$connection = ConnectionPDO::getConnection()->getLink(); 


if((isset($_POST["grafico"]) ? $_POST["grafico"] : null) == 2 && !empty($_POST["datainicial"]) && !empty($_POST["datafinal"])){
	
	$dataInicialNew = new DateTime((string)($_POST["datainicial"] ?? "now"));
	$dataInicialNew->sub(new DateInterval('P'.((int)($_POST["periodo"] ?? 0)).'D'));
	$dateInicial = $dataInicialNew->format("Y-m-d");

	$sql = "select count(*) as qtde, sum(vgm_valor * vgm_qtde) as total, extract(day from vg_data_inclusao) as dia, extract(month from vg_data_inclusao) as mes, extract(year from vg_data_inclusao) as ano from tb_dist_venda_games inner join tb_dist_venda_games_modelo on vg_id = vgm_vg_id inner join tb_dist_operadora_games_produto on ogp_id = vgm_ogp_id
	where vgm_ogp_id = :ID and date(vg_data_inclusao) between :DTINI and :DTFIN and vg_ultimo_status = '5' 
	group by dia,mes,ano order by ano,mes,dia;";
	
	/*
	
	select count(*) as qtde, sum(vgm_valor * vgm_qtde) as total, extract(day from vg_data_inclusao) as dia from tb_dist_venda_games 
	inner join tb_dist_venda_games_modelo on vg_id = vgm_vg_id
	where vgm_opr_codigo = :ID and date(vg_data_inclusao) between :DTINI and :DTFIN and vg_ultimo_status = '5' group by dia order by dia;
	
	*/

	$query = $connection->prepare($sql);
	$query->bindValue(":ID", (isset($_POST["id"]) ? $_POST["id"] : null));
	$query->bindValue(":DTINI", $dateInicial);
	$query->bindValue(":DTFIN", (isset($_POST["datafinal"]) ? $_POST["datafinal"] : null));
	$query->execute();
	$result = $query->fetchAll(PDO::FETCH_ASSOC);
	
	echo json_encode($result);
	
}else if((isset($_POST["grafico"]) ? $_POST["grafico"] : null) == 3 && !empty($_POST["datainicial"]) && !empty($_POST["datafinal"])){
	
	$sql = "select count(*) as vendas,sum(vgm_qtde*vgm_valor) as total,ogp_nome,extract(month from vg_data_inclusao) as mes  from tb_dist_venda_games 
	inner join tb_dist_venda_games_modelo on vg_id = vgm_vg_id
	inner join tb_dist_operadora_games_produto on ogp_opr_codigo = vgm_opr_codigo
	where ogp_ativo = 1 and vg_ug_id = :ID and vg_ultimo_status = '5' and date(vg_data_inclusao) BETWEEN :DTINI and :DTFIN group by ogp_nome,mes order by mes;";
	$query = $connection->prepare($sql);
	$query->bindValue(":ID", (isset($_POST["id"]) ? $_POST["id"] : null));
	$query->bindValue(":DTINI", (isset($_POST["datainicial"]) ? $_POST["datainicial"] : null));
	$query->bindValue(":DTFIN", (isset($_POST["datafinal"]) ? $_POST["datafinal"] : null));
	$query->execute();
	$result = $query->fetchAll(PDO::FETCH_ASSOC);
	$newResult = [];
	foreach($result as $key => $value){
		$value["ogp_nome"] = pdv_iso_to_utf8((string)($value["ogp_nome"] ?? ""));
		array_push($newResult, $value);
	}

	echo json_encode($newResult);
	
}

?>