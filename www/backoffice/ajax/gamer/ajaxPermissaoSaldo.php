<?php

	require "/www/db/connect.php";
	require "/www/db/ConnectionPDO.php";
	
	$conexao = ConnectionPDO::getConnection()->getLink();
	
	if(isset($_GET["acao"]) && $_GET["acao"] == "listar") {
		
		$sql = "SELECT * FROM estorno_usuario;";
		$query = $conexao->prepare($sql);
		$query->execute();
		
		if($query->rowCount() > 0) {
			$result = $query->fetchAll(PDO::FETCH_ASSOC);
			
			$data = [];
			
			foreach($result as $id => $value) {
				// Converting yyyy-mm-dd to d-m-Y
				$date = DateTime::createFromFormat('Y-m-d', $value['data_operacao']);
				$value['data_operacao'] = $date->format('d-m-Y');
				
				$sql = "SELECT ug_nome FROM usuarios_games WHERE ug_id = :UG_ID";
				$query = $conexao->prepare($sql);
				$query->bindValue(':UG_ID', $value['ug_id']);
				$query->execute();
				
				$ret = $query->fetch(PDO::FETCH_ASSOC);
				
				$value["ug_nome"] = $ret["ug_nome"];
				
				$data[$id] = $value;
			}
			
			echo json_encode($data);
		}
		else {
			echo json_encode([]);
		}
	}
	
	else if(isset($_GET["acao"]) && $_GET["acao"] == "alterar") {
		
		$sql = "UPDATE estorno_usuario SET foi_aprovado = 1 WHERE ug_id = :UG_ID";
		$query = $conexao->prepare($sql);
		$query->bindValue(':UG_ID', $_POST["ug_id"]);
		$query->execute();
		
		$sql = "UPDATE usuarios_games SET ug_perfil_saldo = :NOVO_SALDO WHERE ug_id = :UG_ID;";
		$query = $conexao->prepare($sql);
		$query->bindValue(':NOVO_SALDO', $_POST["novo_saldo"]);
		$query->bindValue(':UG_ID', $_POST["ug_id"]);
		$query->execute();
		
		if($query->rowCount() > 0){
			echo "Alteração Liberada com Sucesso";
			exit;
		}
		
		echo "Não foi possivel liberar a alteração";
	}
?>