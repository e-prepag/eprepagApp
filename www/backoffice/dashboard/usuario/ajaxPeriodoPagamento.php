<?php
header('Content-Type: application/json; charset=utf-8');
require_once "/www/db/connect.php"; 
require_once "/www/db/ConnectionPDO.php"; 
$connection = ConnectionPDO::getConnection()->getLink(); 

if(isset($_POST["datainicial"]) && isset($_POST["datafinal"]) && isset($_POST["id"])) {
	
	$sql = "select count(*) as qtde, vg_pagto_tipo as forma_pagamento, ogp_nome from tb_venda_games inner join tb_pag_compras on 
	vg_id = idvenda inner join tb_venda_games_modelo on vgm_vg_id = vg_id inner join tb_operadora_games_produto on 
	vgm_ogp_id = ogp_id where vg_ultimo_status = '5' and vgm_ogp_id = :ID and ogp_ativo = 1 and date(vg_data_inclusao) between :DTINI 
	and :DTFIN group by vg_pagto_tipo, ogp_nome order by ogp_nome, qtde desc;";
	
	$query = $connection->prepare($sql);
	$query->bindValue(":ID", $_POST["id"]);
	$query->bindValue(":DTINI", $_POST["datainicial"]);
	$query->bindValue(":DTFIN", $_POST["datafinal"]);
	
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
?>