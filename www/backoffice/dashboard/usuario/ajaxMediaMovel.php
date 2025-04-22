<?php 
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');
require_once "/www/db/connect.php"; 
require_once "/www/db/ConnectionPDO.php"; 
$connection = ConnectionPDO::getConnection()->getLink(); 

if(isset($_POST["id"]) && isset($_POST["datainicial"]) && isset($_POST["datafinal"]) && isset($_POST["periodo"])) {
	
	$dataInicialNew = new DateTime($_POST["datainicial"]);
	$dataInicialNew->sub(new DateInterval('P'.$_POST["periodo"].'D'));
	$dateInicial = $dataInicialNew->format("Y-m-d");
	
	$sql = "select count(*) as qtde, sum(vgm_valor * vgm_qtde) as total, extract(day from vg_data_inclusao) as dia, extract(month from vg_data_inclusao) as mes, extract(year from vg_data_inclusao) as ano from tb_venda_games inner join tb_venda_games_modelo on vg_id = vgm_vg_id inner join tb_operadora_games_produto on ogp_id = vgm_ogp_id where vgm_ogp_id = :ID and date(vg_data_inclusao) between :DTINI and :DTFIN and vg_ultimo_status = '5' group by dia,mes,ano order by ano,mes,dia;";
	
	$query = $connection->prepare($sql);
	$query->bindValue(":ID", $_POST["id"]);
	$query->bindValue(":DTINI", $dateInicial);
	$query->bindValue(":DTFIN", $_POST["datafinal"]);
	$query->execute();
	$result = $query->fetchAll(PDO::FETCH_ASSOC);
	

	echo json_encode($result);
}
?>