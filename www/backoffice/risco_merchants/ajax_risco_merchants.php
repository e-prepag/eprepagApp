<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
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
$conexao = ConnectionPDO::getConnection()->getLink();

function validarData($data)
{
	if (!is_string($data)) {
		return false;
	}
	$d = DateTime::createFromFormat('Y-m-d', $data);
	return $d && $d->format('Y-m-d') === $data;
}

if (isset($_POST["acao"]) && $_POST["acao"] == "listar") {

	// 1) Recebe valores do POST / valida
	$dataInicio = validarData($_POST['dt_inicial']) ? $_POST['dt_inicial'] : null;
	$dataFim = validarData($_POST['dt_final']) ? $_POST['dt_final'] . " 23:59:59" : null;
	$tipoRisco = isset($_POST['opr_risco']) ? (int) $_POST['opr_risco'] : 4;
	$oprCodigo = isset($_POST['opr_codigo']) ? (int) $_POST['opr_codigo'] : 0;
	$tipoStatus = isset($_POST['opr_status']) ? (int) $_POST['opr_status'] : 2;

	// 2) Monta a parte fixa do SELECT
	$sql = "SELECT *
				FROM (
				  SELECT DISTINCT ON (o.opr_codigo)
				    o.opr_codigo,
				    o.opr_nome,
				    o.opr_cnpj,
				    o.opr_internacional,
				    o.opr_status,
				    COALESCE(oo.tipo_risco, 0) AS tipo_risco,
				    COALESCE(oo.data_observacao, NULL) AS data_observacao,
				    COALESCE(oo.observacao, '') AS observacao
				  FROM operadoras o
				  LEFT JOIN operadoras_obs oo ON o.opr_codigo = oo.opr_codigo
				  WHERE 1=1
		";

	if ($oprCodigo > 0) {
		$sql .= " AND o.opr_codigo = :opr_codigo ";
	}
	if ($tipoStatus < 2) {
		$sql .= " AND o.opr_status = :opr_status ";
	}

	$sql .= " ORDER BY o.opr_codigo, oo.data_observacao DESC NULLS LAST
		) ultimos WHERE 1=1
		";
	if ($tipoRisco < 4) {
		$sql .= " AND ultimos.tipo_risco = :tipo_risco";
	}
	if ($dataInicio && $dataFim) {
		$sql .= " AND ultimos.data_observacao BETWEEN :data_inicio AND :data_fim ";
	} elseif ($dataInicio) {
		$sql .= " AND ultimos.data_observacao >= :data_inicio ";
	} elseif ($dataFim) {
		$sql .= " AND ultimos.data_observacao <= :data_fim ";
	}

	$stmt = $conexao->prepare($sql);
	// 3) Prepara os parâmetros
	if ($oprCodigo > 0) {
		$stmt->bindParam(':opr_codigo', $oprCodigo, PDO::PARAM_INT);
	}
	if ($tipoStatus < 2) {
		$stmt->bindParam(':opr_status', $tipoStatus, PDO::PARAM_INT);
	}
	if ($dataInicio) {
		$stmt->bindParam(':data_inicio', $dataInicio, PDO::PARAM_STR);
	}
	if ($dataFim) {
		$stmt->bindParam(':data_fim', $dataFim, PDO::PARAM_STR);
	}
	if ($tipoRisco < 4) {
		$stmt->bindParam(':tipo_risco', $tipoRisco, PDO::PARAM_INT);
	}
	$stmt->execute();
	$resultRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

	//echo $sql;

	$data = ["data" => []];
	$riscos = [
		0 => "Não possui",
		1 => "Baixo",
		2 => "Médio",
		3 => "Alto"
	];
	if (count($resultRows) > 0) {
		foreach ($resultRows as $key => $value) {
			$dataKeys = array_keys($value);

			$csv = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 576 512">
    					<path d="M572.5 241.4c-1.5-2.3-38.4-57.3-107.2-102.5C407.3 94.1 337.2 80 288 80s-119.3 14.1-177.3 58.9C42.9 184.1 6.1 239.1 4.5 241.4a32.1 32.1 0 0 0 0 29.2c1.5 2.3 38.4 57.3 107.2 102.5C168.7 417.9 238.8 432 288 432s119.3-14.1 177.3-58.9c68.8-45.2 105.6-100.2 107.2-102.5a32.1 32.1 0 0 0 0-29.2zM288 384c-79.5 0-144-64.5-144-144s64.5-144 144-144 144 64.5 144 144-64.5 144-144 144zm0-240a96 96 0 1 0 0 192 96 96 0 0 0 0-192z"/>
  					</svg>';

			$acao = "<a class='btn btn-info' href='/risco_merchants/publisher.php?opr_codigo=" . $value["opr_codigo"] . "'
							style='border-width: 0px;border-radius: 1px;box-shadow: 1px 1px 5px rgb(0,0,0,0.5); display: flex;width: 100%;justify-content: center;
							data-codigo='" . $value["pin_codinterno"] . "' data-atual='" . $_GET["reload"] . "'
						>
							$csv
						</a>";

			$dataLine = [
				$dataKeys[0] => $value["opr_codigo"],
				$dataKeys[1] => utf8_encode($value["opr_nome"]),
				$dataKeys[2] => ($value["opr_cnpj"] != "" ? $value["opr_cnpj"] : "Não possui"),
				$dataKeys[3] => ($value["opr_internacional"] == 1 ? "Sim" : "Não"),
				$dataKeys[4] => ($value["opr_status"] == 1 ? "Ativo" : "Inativo"),
				"tipo_risco" => (isset($value["tipo_risco"]) ? $riscos[$value["tipo_risco"]] : "Não encontrado"),
				"ultima_data" => (isset($value["data_observacao"]) ? $value["data_observacao"] : "Não encontrado"),
				"observacao" => (isset($value["observacao"]) ? utf8_encode($value["observacao"]) : "Não encontrado"),
				"acao" => $acao
			];
			array_push($data["data"], $dataLine);
		}
	}
	echo json_encode($data);
	die;
} else if(isset($_POST["acao"]) && $_POST["acao"] == "novo"){

	// 1) Recebe valores do POST / valida
	$oprCodigo = isset($_POST['opr_codigo']) ? (int) $_POST['opr_codigo'] : 0;
	$tipoRisco = isset($_POST['tipo_risco']) ? (int) $_POST['tipo_risco'] : 0;
	$dataObservacao = date("Y-m-d H:i:s");
	$observacao = isset($_POST['observacao']) ? utf8_decode($_POST['observacao']) : '';
	$usuario = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;

	if ($oprCodigo <= 0 ) {
		echo "Operadora inválida.";
		die;
	}
	if ($tipoRisco < 1 || $tipoRisco > 3) {
		echo "Tipo de risco inválido.";
		die;
	}
	if (empty($observacao)) {
		echo "Observação não pode ser vazia.";
		die;
	}
	if ($usuario <= 0) {
		echo "Você não está logado ou seu usuário é inválido.";
		die;
	}


	// 2) Insere a nova observação
	$sql = "INSERT INTO operadoras_obs (opr_codigo, tipo_risco, observacao, data_observacao, user_id)
			VALUES (:opr_codigo, :tipo_risco, :observacao, :data_observacao, :user_id)";
	$stmt = $conexao->prepare($sql);
	$stmt->bindParam(':opr_codigo', $oprCodigo, PDO::PARAM_INT);
	$stmt->bindParam(':tipo_risco', $tipoRisco, PDO::PARAM_INT);
	$stmt->bindParam(':observacao', $observacao, PDO::PARAM_STR);
	$stmt->bindParam(':data_observacao', $dataObservacao, PDO::PARAM_STR);
	$stmt->bindParam(':user_id', $usuario, PDO::PARAM_INT);

	if ($stmt->execute()) {
		echo 1;
	} else {
		echo "Erro ao registrar a nova observação.";
	}
	die;

} else {
	echo "Não foi possivel efetuar sua escolha";
}
