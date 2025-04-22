<?php
header('Content-Type: application/json; charset=utf-8');
require_once "/www/db/connect.php"; 
require_once "/www/db/ConnectionPDO.php"; 
$connection = ConnectionPDO::getConnection()->getLink(); 

function isAjax() {return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));}
function block_direct_calling() {
    if(!isAjax()) {
           echo "Chamada n".utf8_encode('ã')."o permitida";
           die();
    }
}
block_direct_calling();

if(isset($_POST["type"]) && $_POST["type"] == 2 && isset($_POST["id"])) {
	
	$sql = "update bloqueios_login_pdv set tentativas = 0 where id = :ID";
	$query = $connection->prepare($sql);
	$query->bindValue(":ID", $_POST["id"]);
	$query->execute();
	
	echo json_encode(["Alterado com sucesso"]);
	exit;
}

$sql = "select id, ug_id, TO_CHAR(created, 'DD-MM-YYYY HH24:MI:SS') as created, ip, login, tentativas from bloqueios_login_pdv where visualizacao = 'S';";
$query = $connection->prepare($sql);
$query->execute();
$result = $query->fetchAll(PDO::FETCH_ASSOC);
$allResults = [];

foreach($result as $key => $value) {
	
	if($value["ug_id"] == "") {
		$sql = "SELECT ug_id from dist_usuarios_games where ug_login = :UG_LOGIN;";
		$query = $connection->prepare($sql);
		$query->bindValue(":UG_LOGIN", strtoupper($value["login"]));
		$query->execute();
		$result = $query->fetch(PDO::FETCH_ASSOC);
		
		$value["ug_id"] = $result["ug_id"];
	}
	
	if($value['tentativas'] == 0){
		$mensagem = 'PDV Livre';
	} else {
		$mensagem = '<button type="button" class="btn btn-aprovar" style="background-color: green; color: white">Desbloquear</button>';			
	}
	
	$value["login"] = utf8_encode($value["login"]);
	$value["msg"] = $mensagem;
	
	array_push($allResults, $value);
}

echo json_encode($allResults);
?> 