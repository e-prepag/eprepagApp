<?php
//ini_set("display_errors", 1);
//ini_set("display_startup_errors", 1);
//error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');
require_once "/www/db/connect.php"; 
require_once "/www/db/ConnectionPDO.php"; 

$connection = ConnectionPDO::getConnection()->getLink();
ini_set('memory_limit', '8192M');
set_time_limit(0);

if(isset($_POST["type"]) && $_POST["type"] == 2) {

	$sql = "select ug_id from usuarios_games where ug_ativo = 1;";
	$query = $connection->prepare($sql);
	$query->execute();
	$result = $query->fetchAll(PDO::FETCH_ASSOC);
	
	echo json_encode($result);
	exit;
}

else if(isset($_POST["datainicial"]) && isset($_POST["datafinal"])) {
	
	//$sql = "select avg(vgm_valor) from tb_dist_venda_games inner join tb_dist_venda_games_modelo on vgm_vg_id = vg_id where vg_ultimo_status = 5 and vg_ug_id = :ID and date(vg_data_inclusao) between :DTINI and :DTFIN;";

	$sql = "select avg(vgm_valor), ug_id, ug_nome from tb_venda_games inner join tb_venda_games_modelo on vgm_vg_id = vg_id
    inner join usuarios_games on vg_ug_id = ug_id where vg_ultimo_status = 5 and ug_ativo = 1 and ug_nome != '' and date(vg_data_inclusao) between :DTINI and :DTFIN group by ug_id, ug_nome order by ug_id;"; 
	$query = $connection->prepare($sql);

	$query->bindValue(":DTINI", $_POST["datainicial"]);
	$query->bindValue(":DTFIN", $_POST["datafinal"]);	
	$query->execute();
	$ticket_medio_usuarios = $query->fetchAll(PDO::FETCH_ASSOC);
	$codigosUsuarios = array_column($ticket_medio_usuarios, "ug_id");
	$mediaUsuarios = array_column($ticket_medio_usuarios, "avg", "ug_id");
	$nomeUsuarios = array_column($ticket_medio_usuarios, "ug_nome");
	 try{
		 $sql2 = "select count(*) as qtde, vg_ug_id from tb_venda_games inner join tb_venda_games_modelo on vgm_vg_id = vg_id where vg_ultimo_status = 5 and vg_ug_id in(".implode(",", $codigosUsuarios).") and date(vg_data_inclusao) BETWEEN :DTINI2 and :DTFIN2 group by vg_ug_id order by vg_ug_id;";
		 $queries = $connection->prepare($sql2);
		 //$queries->bindValue(":ID2", ); //$_POST["id"]
		 $queries->bindValue(":DTINI2", $_POST["datainicial"]);
		 $queries->bindValue(":DTFIN2", $_POST["datafinal"]);	 
		 $queries->execute();
		 $qtdeAnual = $queries->fetchAll(PDO::FETCH_ASSOC);
		 $mediaQuantidadeUsuarios = array_column($qtdeAnual, "qtde", "vg_ug_id");
		 
		 $sql3 = "SELECT date(min(vg_data_inclusao)) as data, vg_ug_id from tb_venda_games inner join tb_venda_games_modelo on vgm_vg_id = vg_id where vg_ultimo_status = 5 and vg_ug_id in(".implode(",", $codigosUsuarios).") group by vg_ug_id order by vg_ug_id;";
		 $queries3 = $connection->prepare($sql3);
		 $queries3->execute();
		 $tempoDeUniao = $queries3->fetchAll(PDO::FETCH_ASSOC);
		 $mediaUniaoUsuarios = array_column($tempoDeUniao, "data", "vg_ug_id");
		 
		 function calculaLtv($ticket, $qtde, $uniao, $nome){
			$data_atual = new DateTime('now');
			$diferenca = $data_atual->diff(new DateTime($uniao));
			//var_dump($ticket." - ".$qtde." - ".$uniao." - ".$nome);
			$ltv = number_format(($ticket * $qtde) * $diferenca->y, 2, ",", ".");
			return ["usuario" => utf8_encode($nome), "ltv" => $ltv]; 
		 }
		 $resultadoLTV = array_map("calculaLtv", $mediaUsuarios, $mediaQuantidadeUsuarios, $mediaUniaoUsuarios, $nomeUsuarios);  //$codigosPdvs
		 function limpaResultado($array){
			 if($array["ltv"] !="0,00"){
				 return $array;
			 }
		 }
		 $resultado = array_filter($resultadoLTV, "limpaResultado");
		 function ordenar($prev, $next) {
              return str_replace([".", ","], ["", "."], $prev['ltv']) < str_replace([".", ","], ["", "."], $next['ltv']);
		 }
		 usort($resultado, 'ordenar');
		 
		 echo json_encode($resultado);
		 
	 }catch(PDOException $e){
		 echo $e->getMessage();
	 }

	
	//echo json_encode($resultadoFinal);
}
