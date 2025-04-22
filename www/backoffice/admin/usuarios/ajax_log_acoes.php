<?php

require_once "/www/class/phpmailer/class.phpmailer.php";
require_once "/www/includes/configIP.php";
require_once "/www/class/phpmailer/class.smtp.php";
require_once "/www/includes/constantes.php";
require_once "/www/includes/gamer/functions.php";
require "/www/db/connect.php";
require "/www/db/ConnectionPDO.php";
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

try {
	# code...

	$conexao = ConnectionPDO::getConnection()->getLink();

	if (isset($_GET["acao"]) && $_GET["acao"] == "listar") {

		$dt_inicial = isset($_GET["dt_inicial"]) ? str_replace('T', ' ', $_GET["dt_inicial"]) . ":00" : null;
		$dt_final = isset($_GET["dt_final"]) ? str_replace('T', ' ', $_GET["dt_final"]) . ":59" : null;
		$data = ["data" => []];

		$sql = "SELECT * FROM usuario_logs_acoes WHERE caminho_arquivo IS NOT NULL";

		// Array para armazenar as condições e os parâmetros
		$conditions = [];
		$params = [];

		// Adiciona condições dinamicamente
		if ($dt_inicial) {
			$conditions[] = "data_hora_registro >= :DT_INICIAL";
			$params[':DT_INICIAL'] = $dt_inicial;
		}
		if ($dt_final) {
			$conditions[] = "data_hora_registro <= :DT_FINAL";
			$params[':DT_FINAL'] = $dt_final;
		}
		if (!empty($_GET["usuario_id"])) {
			$conditions[] = "usuario_id = :usuario_id";
			$params[':usuario_id'] = $_GET["usuario_id"];
		}
		if (!empty($_GET["tipo_usuario"])) {
			if ($_GET["tipo_usuario"] == 3) {
				$conditions[] = "usuario_id = 0";
			} else {
				$conditions[] = "tipo_usuario = :tipo_usuario AND usuario_id <> 0";
				$params[':tipo_usuario'] = $_GET["tipo_usuario"];
			}
		}
		if (!empty($_GET["ip_usuario"])) {
			$conditions[] = "ip_usuario = :ip_usuario";
			$params[':ip_usuario'] = $_GET["ip_usuario"];
		}

		// Adiciona as condições na consulta, se houver
		if (!empty($conditions)) {
			$sql .= " AND " . implode(" AND ", $conditions);
		}

		$sql .= " ORDER BY data_hora_registro DESC";

		// Prepara a consulta
		$selectRows = $conexao->prepare($sql);

		// Associa os parâmetros dinamicamente
		foreach ($params as $key => $value) {
			$selectRows->bindValue($key, $value);
		}

		// Executa a consulta
		$selectRows->execute();

		$resultRows = $selectRows->fetchAll(PDO::FETCH_ASSOC);
		if (count($resultRows) > 0) {
			foreach ($resultRows as $key => $value) {
				$dataKeys = array_keys($value);

				if ($value["usuario_id"] == 0) {
					$tipo_usuario_descricao = "sem login";
				} elseif ($value["tipo_usuario"] == 1) {
					$tipo_usuario_descricao = "usuario PDV";
				} elseif ($value["tipo_usuario"] == 2) {
					$tipo_usuario_descricao = "usuario gamer";
				} else {
					$tipo_usuario_descricao = "tipo desconhecido"; // Caso o valor seja inesperado
				}

				$dataLine = [
					$dataKeys[1] => $value["usuario_id"],
					$dataKeys[2] => $tipo_usuario_descricao,
					$dataKeys[3] => $value["data_hora_registro"],
					$dataKeys[4] => $value["ip_usuario"],
					$dataKeys[5] => $value["caminho_arquivo"]
				];
				array_push($data["data"], $dataLine);
			}

			echo json_encode($data);
			die;
		}
	} else {
		echo "Não foi possivel efetuar sua escolha";
	}

} catch (\Throwable $e) {
	$dataLine = [
		$dataKeys[0] => $e->getMessage(),
		$dataKeys[1] => $e->getTraceAsString(),
		$dataKeys[2] => date('y-m-d'),
		$dataKeys[3] => "",
		$dataKeys[4] => ""
	];
	array_push($data["data"], $dataLine);

	echo json_encode($data);
	die;
}
?>