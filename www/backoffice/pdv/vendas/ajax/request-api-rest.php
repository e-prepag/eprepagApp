<?php require_once __DIR__ . '/../../../../includes/constantes_url.php'; ?>
<?php
	require_once "/www/db/connect.php";
	require_once "/www/db/ConnectionPDO.php";
		
	$dados_operador = $_POST["dados_operador"];
	$venda_id = $_POST["venda_id"];

	$dataHoraAtual = new dateTime();

	$dataHoraFormatada = $dataHoraAtual->format('Y-m-d H:i:s');

	$chama_api = curl_init("" . EPREPAG_URL_HTTPS . "/webhook/confirmaPix.php");
	$data = [
		'http_status_code' => 200,
		'http_status_message' => 'OK',
		'date' => $dataHoraFormatada,
		'response' => [
			'message' => [
				'status' => 'TRANSACAO_RECEBIDA',
				'id' => $_POST["id"]
			]
		]
	];
	
	$tratamento_json = json_encode($data);
	
	curl_setopt($chama_api, CURLOPT_CUSTOMREQUEST, 'POST');
	curl_setopt($chama_api, CURLOPT_POSTFIELDS, $tratamento_json);
	
	curl_setopt($chama_api, CURLOPT_RETURNTRANSFER, True);
	
	$resultado = curl_exec($chama_api);
	
	
	
	if (trim($resultado) == 'Pagamento já conciliado') {
		echo 'Conciliação manual não foi realizada!';
	} elseif (strpos($resultado, 'e-mail enviado com sucesso') !== false) {
		
		
		$conexao_pdo = ConnectionPDO::getConnection();
		$conexao_bd = $conexao_pdo->getLink();
		
		try {
			$query_consulta = "select * from tb_dist_venda_games where vg_id = :venda_id and vg_ultimo_status = :vg_ultimo_status;";
		   
			$stmt = $conexao_bd->prepare($query_consulta);
			$stmt->bindValue(":venda_id", $venda_id, PDO::PARAM_INT);
			$stmt->bindValue(":vg_ultimo_status", 5, PDO::PARAM_INT);
			$stmt->execute();
			$resultado_consulta = $stmt->fetchAll(PDO::FETCH_ASSOC);

			if (count($resultado_consulta) > 0) {
				
				$query_atualiza = "update tb_dist_venda_games set vg_usuario_obs = vg_usuario_obs || ' - {$dataHoraFormatada} Conciliado manualmente por ' || :dados_operador WHERE vg_id = :venda_id;";

				$stmt2 = $conexao_bd->prepare($query_atualiza);
				$stmt2->bindValue(":dados_operador", $dados_operador, PDO::PARAM_STR);
				$stmt2->bindValue(":venda_id", $venda_id, PDO::PARAM_INT);
				$stmt2->execute();

			}
		} catch (PDOException $erro) {
			
			$compila_erro = "Operação do PDV " . $dataHoraFormatada . " " . $erro->getMessage();
			
		}
		
		echo 'Concialização realizada com sucesso!';
		
	} else {
		echo 'Erro inesperado. Tente mais tarde!';
	}
	
	curl_close($chama_api);
	
	$arquivo = "/www/log/log-concilia.txt";

	$abre_arquivo = fopen($arquivo, 'a');

	fwrite($abre_arquivo, $compila_erro);
																	
	fclose($abre_arquivo);
	
	exit;