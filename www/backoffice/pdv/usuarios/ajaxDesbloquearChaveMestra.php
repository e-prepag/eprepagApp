<?php

header('Content-Type: application/json; charset=utf-8');
require_once "/www/db/connect.php"; 
require_once "/www/db/ConnectionPDO.php"; 
$connection = ConnectionPDO::getConnection()->getLink(); 

/*function isAjax() {return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));}
function block_direct_calling() {
    if(!isAjax()) {
           echo "Chamada n".utf8_encode('ã')."o permitida";
           die();
    }
}
block_direct_calling();*/

if(isset($_GET["acao"]) && $_GET["acao"] == "listar") {
	$sql = "SELECT codigo,ip,liberado,ug_nome_fantasia FROM dist_usuarios_games_chave_seguro inner join dist_usuarios_games on usuario = ug_id";
	$query = $connection->prepare($sql);
	$query->execute();
	
	if($query->rowCount() > 0) {
		$result = $query->fetchAll(PDO::FETCH_ASSOC);
		$data = ["data" => []];
		
		foreach($result as $key => $value){
			 $dataKeys = array_keys($value);
			 $acao = '<button class="btn btn-danger btn-liberar" data-codigo="'.$value["codigo"].'">Deletar</button>';
			 $dataLine = [
				  $dataKeys[0] => $value["codigo"],
				  $dataKeys[1] => $value["ip"],
				  $dataKeys[2] => $value["liberado"],
				  $dataKeys[3] => utf8_encode($value["ug_nome_fantasia"]),
				  "acao" => $acao
			 ];
			 array_push($data["data"], $dataLine);
		}
		
		echo json_encode($data["data"]);
	}
}
else if($_GET["acao"] == "apagar") {
	$sql = "delete from dist_usuarios_games_chave_seguro where codigo = :CODIGO;";
	$deleteRow = $connection->prepare($sql);
	$deleteRow->bindValue(":CODIGO", $_POST["codigo"]);
	$deleteRow->execute();
	if($deleteRow->rowCount() > 0){
		echo "Bloqueio liberado com sucesso";
		exit;
	}
	
	echo "Não foi possivel liberar o bloqueio";
}
?> 