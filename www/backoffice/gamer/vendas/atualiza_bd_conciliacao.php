<?php

	require_once "/www/db/connect.php";
	require_once "/www/db/ConnectionPDO.php";

	$dados_operador = $_POST["dados_operador"];
	$venda_id = $_POST["venda_id"];

	$conexao_pdo = ConnectionPDO::getConnection();

	$conexao_bd = $conexao_pdo->getLink();

	try {
		$query_consulta = "SELECT * FROM tb_venda_games WHERE vg_id = :venda_id;";
	   
		$stmt = $conexao_bd->prepare($query_consulta);
		$stmt->bindParam(":venda_id", $venda_id, PDO::PARAM_INT);
		$stmt->execute();
		$resultado_consulta = $stmt->fetchAll(PDO::FETCH_ASSOC);

		if (count($resultado_consulta) > 0) {
			
			$query_atualiza = "UPDATE tb_venda_games SET vg_usuario_obs = :vg_usuario_obs || ' - Conciliado manualmente por ' || :dados_operador WHERE vg_id = :venda_id;";

			$stmt2 = $conexao_bd->prepare($query_atualiza);
			$stmt2->bindParam(":vg_usuario_obs", $resultado_consulta["vg_usuario_obs"], PDO::PARAM_STR);
			$stmt2->bindParam(":dados_operador", $dados_operador, PDO::PARAM_STR);
			$stmt2->bindParam(":venda_id", $venda_id, PDO::PARAM_INT);
			$stmt2->execute();
			$resultado_atualizacao = $stmt2->fetchAll(PDO::FETCH_ASSOC);

		}
	} catch (PDOException $erro) {
		$compila_erro = date("l jS \of F Y h:i:s A");
		$compila_erro .= "Erro na consulta ao banco de dados: " . $erro->getMessage();
		$compila_erro .= "/n";
		$compila_erro .= $dbh->errorCode();
		$compila_erro .= "/n";
		
		$arquivo = "/www/log/log-concilia.txt";

		$abre_arquivo = fopen($arquivo, 'a');

		fwrite($abre_arquivo, $compila_erro);
																
		fclose($abre_arquivo);
	}
?>
