<?php
header('Content-Type: application/json; charset=utf-8');
require_once "/www/db/connect.php"; 
require_once "/www/db/ConnectionPDO.php"; 
$connection = ConnectionPDO::getConnection()->getLink();


if(isset($_POST["type"]) && $_POST["type"] == 2 && isset($_POST["id_eprepag"])) {
	$sql = "select id_produto from tb_permissoes_produtos where id_eprepag = :ID_EPREPAG;";
	$query = $connection->prepare($sql);
	$query->bindValue(":ID_EPREPAG", $_POST["id_eprepag"]);
	$query->execute();
	
	$resultado = $query->fetchAll(PDO::FETCH_ASSOC);
	
	echo json_encode($resultado);
	exit;
}

else if(isset($_POST["type"]) && $_POST["type"] == 4 && isset($_POST["id_eprepag"])) {
	$sql = "delete from tb_permissoes_produtos where id_eprepag = :ID_EPREPAG;";
	$query = $connection->prepare($sql);
	$query->bindValue(":ID_EPREPAG", $_POST["id_eprepag"]);
	$query->execute();
	
	echo json_encode(["Todos excluídos"]);
	exit;
}

else if($_POST["type"] && $_POST["type"] == 3 && isset($_POST["id_eprepag"])  && isset($_POST["id_produto"])) {
	
	$sql = "delete from tb_permissoes_produtos where id_eprepag = :ID_EPREPAG and id_produto = :ID_PRODUTO;";
	$query = $connection->prepare($sql);
	$query->bindValue(":ID_EPREPAG", $_POST["id_eprepag"]);
	$query->bindValue(":ID_PRODUTO", $_POST["id_produto"]);
	
	$query->execute();
	
	$result = $query->fetchAll(PDO::FETCH_ASSOC);
	
	echo json_encode($result);
	exit;
}

else if(isset($_POST["id_eprepag"]) && isset($_POST["id_produto"])) {
	
	$sql = "insert into tb_permissoes_produtos (id_eprepag, id_produto) values (:ID_EPREPAG, :ID_PRODUTO);";
	$verify = "select * from tb_permissoes_produtos where id_eprepag = :ID_EPREPAG and id_produto = :ID_PRODUTO;";
	
	$query = $connection->prepare($sql);
	$second_query = $connection->prepare($verify);
	
	$query->bindValue(":ID_EPREPAG", $_POST["id_eprepag"]);
	$query->bindValue(":ID_PRODUTO", $_POST["id_produto"]);
	$second_query->bindValue(":ID_EPREPAG", $_POST["id_eprepag"]);
	$second_query->bindValue(":ID_PRODUTO", $_POST["id_produto"]);
	
	$second_query->execute();
	$check = $second_query->fetch(PDO::FETCH_ASSOC);
	
	if($check == false) {
		$query->execute();
		$resultado = $query->fetch(PDO::FETCH_ASSOC);
		echo json_encode($resultado);
		exit;
	}
	
	echo json_encode(["Já existe"]);
	exit;
} 	
?>