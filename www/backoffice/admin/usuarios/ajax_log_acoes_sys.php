<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);  // Exibe todos os tipos de erros
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

		$sql = "SELECT * FROM usuario_logs_acoes_admin ul
		JOIN usuarios u ON u.id = ul.usuario_id
		WHERE rota_acessada IS NOT NULL";

		// Array para armazenar as condições e os parâmetros
		$conditions = [];
		$params = [];

		// Adiciona condições dinamicamente
		if ($dt_inicial) {
			$conditions[] = "ul.data_hora_registro >= :DT_INICIAL";
			$params[':DT_INICIAL'] = $dt_inicial;
		}
		if ($dt_final) {
			$conditions[] = "ul.data_hora_registro <= :DT_FINAL";
			$params[':DT_FINAL'] = $dt_final;
		}
		if (!empty($_GET["usuario_id"])) {
			$conditions[] = "ul.usuario_id = :usuario_id";
			$params[':usuario_id'] = $_GET["usuario_id"];
		}
		if (!empty($_GET["ip_usuario"])) {
			$conditions[] = "ul.ip_usuario = :ip_usuario";
			$params[':ip_usuario'] = $_GET["ip_usuario"];
		}

		// Adiciona as condições na consulta, se houver
		if (!empty($conditions)) {
			$sql .= " AND " . implode(" AND ", $conditions);
		}

		$sql .= " ORDER BY ul.data_hora_registro DESC";

		// Prepara a consulta
		$selectRows = $conexao->prepare($sql);

		// Associa os parâmetros dinamicamente
		foreach ($params as $key => $value) {
			$selectRows->bindValue($key, $value);
		}

		// Executa a consulta
		$selectRows->execute();

		$resultRows = $selectRows->fetchAll(PDO::FETCH_ASSOC);
		if ((is_countable($resultRows) ? count($resultRows) : 0) > 0) {
			foreach ($resultRows as $key => $value) {
				$dataKeys = array_keys($value);

				$dataLine = [
					$dataKeys[1] => $value["usuario_id"],
					$dataKeys[2] => $value["tipo_usuario"],
					$dataKeys[3] => $value["data_hora_registro"],
					$dataKeys[4] => $value["ip_usuario"],
					$dataKeys[5] => $value["rota_acessada"],
					$dataKeys[6] => $value["dados_extras"]
				];
				array_push($data["data"], $dataLine);
			}
		}
		echo json_encode($data);
		die;
	} else {
		echo json_encode(["data" => []]);;
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
