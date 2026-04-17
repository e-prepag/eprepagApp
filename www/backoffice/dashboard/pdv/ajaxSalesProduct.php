<?php
header('Content-Type: application/json; charset=utf-8');
require_once "/www/db/connect.php"; 
require_once "/www/db/ConnectionPDO.php"; 
$connection = ConnectionPDO::getConnection()->getLink(); 


if((isset($_POST["grafico"]) ? $_POST["grafico"] : null) == 2 && !empty($_POST["datainicial"]) && !empty($_POST["datafinal"])){
	
	$dataInicialNew = new DateTime((string)($_POST["datainicial"] ?? "now"));
	$dataInicialNew->sub(new DateInterval('P'.((int)($_POST["periodo"] ?? 0)).'D'));
	$dateInicial = $dataInicialNew->format("Y-m-d");

	$sql = "select count(*) as qtde, sum(vgm_valor * vgm_qtde) as total, extract(day from vg_data_inclusao) as dia, extract(month from vg_data_inclusao) as mes, extract(year from vg_data_inclusao) as ano from tb_dist_venda_games 
	inner join tb_dist_venda_games_modelo on vg_id = vgm_vg_id
	where vgm_opr_codigo = :ID and date(vg_data_inclusao) between :DTINI and :DTFIN and vg_ultimo_status = '5' group by dia,mes,ano order by ano,mes,dia;";
	
	$query = $connection->prepare($sql);
	$query->bindValue(":ID", (isset($_POST["id"]) ? $_POST["id"] : null));
	$query->bindValue(":DTINI", $dateInicial);
	$query->bindValue(":DTFIN", (isset($_POST["datafinal"]) ? $_POST["datafinal"] : null));
	$query->execute();
	$result = $query->fetchAll(PDO::FETCH_ASSOC);

	echo json_encode($result);
	exit;
}

?>