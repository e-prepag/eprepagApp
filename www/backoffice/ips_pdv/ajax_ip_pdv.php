<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
require_once "/www/includes/configIP.php";
require_once "/www/includes/constantes.php";
require "/www/db/connect.php";
require "/www/db/ConnectionPDO.php";
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
$conexao = ConnectionPDO::getConnection()->getLink();

if (isset($_POST["acao"]) && $_POST["acao"] == "listar") {

	$usuario_id = $_POST['usuario_id'];
	$ip_pdv = $_POST['ip_pdv'];

	// 2) Monta a parte fixa do SELECT
	$sql = "SELECT 
				    COALESCE(ip.ip_address, 'Sem IP') AS ip_address,
				    COALESCE(ip.ip_range, false)      AS ip_range,
				    ip.ip_range_ini,
				    ip.ip_range_end,
					ip.created_date,
					ip.active,
				    ug.ug_nome_fantasia,
				    ug.ug_id,
					COALESCE(u.shn_nome, 'Desconhecido') as shn_nome
				FROM dist_usuarios_games ug
				LEFT JOIN pdv_api_ip ip 
				       ON ug.ug_id = ip.ug_id
				LEFT JOIN usuarios u ON u.id = ip.bko_user
				WHERE ug.ug_ativo = 1
		";

	if ($usuario_id != null && !empty($usuario_id)) {
		if (is_numeric($usuario_id)) {
			$usuario_id = intval($usuario_id);
			$sql .= "AND ug.ug_id = :usuario_id ";
		} else {
			$usuario_id = "%$usuario_id%";
			$sql .= "AND ug.ug_nome_fantasia ILIKE :usuario_id ";
		}
	}

	if (filter_var($ip_pdv, FILTER_VALIDATE_IP) !== false) {
		$sql .= " AND (ip.ip_range IS FALSE AND ip_address = :ip_pdv)
						OR
						(ip.ip_range IS TRUE AND ip_range_ini <= :ip_pdv AND ip_range_end >= :ip_pdv)";
	}

	// Adiciona filtro para "Somente com IP"
	$ip_only = isset($_POST['ip_only']) ? $_POST['ip_only'] : null;
	if ($ip_only !== null && $ip_only !== "") {
		if ($ip_only == "1") {
			$sql .= " AND ip.ip_address IS NOT NULL ";
		}
	}

	// Adiciona filtro para ativos/inativos
	$ativos = isset($_POST['ativos']) ? $_POST['ativos'] : "";
	if ($ativos !== "") {
		if ($ativos == "1") {
			$sql .= " AND ip.active = true ";
		} else if ($ativos == "0") {
			$sql .= " AND (ip.active = false) ";
		}
	}

	$sql .= "ORDER BY ip.id NULLS LAST LIMIT 100;";

	$stmt = $conexao->prepare($sql);
	// 3) Prepara os parâmetros
	if ($usuario_id != null && !empty($usuario_id)) {
		$stmt->bindParam(':usuario_id', $usuario_id);
	}
	if (isset($ip_pdv) && filter_var($ip_pdv, FILTER_VALIDATE_IP) !== false) {
		$stmt->bindParam(':ip_pdv', $ip_pdv, PDO::PARAM_STR);
	}
	$stmt->execute();
	$resultRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

	//echo $sql;
	$data = ["data" => []];
	if (count($resultRows) > 0) {
		foreach ($resultRows as $key => $value) {
			$svg = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 576 512">
    					<path d="M572.5 241.4c-1.5-2.3-38.4-57.3-107.2-102.5C407.3 94.1 337.2 80 288 80s-119.3 14.1-177.3 58.9C42.9 184.1 6.1 239.1 4.5 241.4a32.1 32.1 0 0 0 0 29.2c1.5 2.3 38.4 57.3 107.2 102.5C168.7 417.9 238.8 432 288 432s119.3-14.1 177.3-58.9c68.8-45.2 105.6-100.2 107.2-102.5a32.1 32.1 0 0 0 0-29.2zM288 384c-79.5 0-144-64.5-144-144s64.5-144 144-144 144 64.5 144 144-64.5 144-144 144zm0-240a96 96 0 1 0 0 192 96 96 0 0 0 0-192z"/>
  					</svg>';

			$acao = "<a class='btn btn-info' href='usuario.php?ug_id=" . $value["ug_id"] . "'
							style='border-width: 0px;border-radius: 1px;box-shadow: 1px 1px 5px rgb(0,0,0,0.5); display: flex;width: 100%;justify-content: center;'
						>
							$svg
						</a>";

			if ($value["ip_range"] == true) {
				$str_ip = "{$value["ip_range_ini"]} - {$value["ip_range_end"]}";
			} else {
				$str_ip = $value["ip_address"];
			}

			$dataLine = [
				"ug_id" => $value["ug_id"],
				"ug_nome" => (isset($value["ug_nome_fantasia"]) ? utf8_encode($value["ug_nome_fantasia"]) : "Não encontrado"),
				"ip_pdv" => $str_ip,
				"shn_nome" => utf8_encode($value["shn_nome"]),
				"criado_em" => isset($value["created_date"]) ? $value["created_date"] : "",
				"ativo" => isset($value["active"]) ? ($value["active"] ? "Sim" : "Não") : "",
				"acao" => $acao
			];
			array_push($data["data"], $dataLine);
		}
	}

	echo json_encode($data);
	die;
} else if (isset($_POST["acao"]) && $_POST["acao"] == "novo") {

	// 1) Recebe valores do POST / valida
	$ug_id = isset($_POST['ug_id']) ? (int) $_POST['ug_id'] : 0;
	$ip_address = isset($_POST['ip_address']) ? $_POST['ip_address'] : "";

	if ($ug_id <= 0) {
		echo json_encode(["status" => "error", "msg" => "Usuário inválido."]);
		die;
	}
	$ip_range_ini = "";
	$ip_range_end = "";
	$ip_range = empty($ip_address);
	if ($ip_range) {
		$ip_range_ini = isset($_POST['ip_range_ini']) ? $_POST['ip_range_ini'] : "";
		$ip_range_end = isset($_POST['ip_range_end']) ? $_POST['ip_range_end'] : "";

		if (empty($ip_range_ini) && empty($ip_range_end)) {
			echo json_encode(["status" => "error", "msg" => "IPs inválidos."]);
			die;
		}
	}

	// 2) Insere a nova observação
	$sql = "INSERT INTO pdv_api_ip (ug_id, ip_address, ip_range_ini, ip_range_end, ip_range, bko_user) VALUES (:ug_id, :ip_address, :ip_range_ini, :ip_range_end, :ip_range, :bko_user) RETURNING id";
	$stmt = $conexao->prepare($sql);
	$stmt->bindParam(':ug_id', $ug_id);
	$stmt->bindParam(':ip_address', $ip_address, PDO::PARAM_STR);
	$stmt->bindParam(':ip_range_ini', $ip_range_ini, PDO::PARAM_STR);
	$stmt->bindParam(':ip_range_end', $ip_range_end, PDO::PARAM_STR);
	$stmt->bindParam(':ip_range', $ip_range, PDO::PARAM_BOOL);
	$stmt->bindParam(':bko_user', $_POST['bko_user'], PDO::PARAM_STR);

	if ($stmt->execute()) {
		$id = $stmt->fetchColumn();
		$msgRetorno = $ip_address ? $ip_address : "$ip_range_ini - $ip_range_end";
		echo json_encode(["status" => "success", "msg" => $msgRetorno, "id" => $id]);
	} else {
		echo json_encode(["status" => "error", "msg" => "Erro ao registrar a nova observação."]);
	}
	die;
} else if(isset($_POST["acao"]) && $_POST["acao"] == "remover"){
	$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

	if ($id <= 0) {
		echo json_encode(["status" => "error", "msg" => "ID do IP inválido."]);
		die;
	}

	$sql = "DELETE FROM pdv_api_ip WHERE id = :id";
	$stmt = $conexao->prepare($sql);
	$stmt->bindParam(':id', $id, PDO::PARAM_INT);

	if ($stmt->execute()) {
		echo json_encode(["status" => "success", "msg" => "Endereço IP removido com sucesso."]);
	} else {
		echo json_encode(["status" => "error", "msg" => "Erro ao remover o endereço IP."]);
	}
	die;

} else if(isset($_POST["acao"]) && $_POST["acao"] == "alterar") {

	$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

	$ativo = $_POST["ativo"] == 1;

	if ($id <= 0) {
		echo json_encode(["status" => "error", "msg" => "ID do IP inválido."]);
		die;
	}

	$sql = "UPDATE pdv_api_ip SET active = :ativo WHERE id = :id";
	$stmt = $conexao->prepare($sql);
	$stmt->bindParam(':id', $id, PDO::PARAM_INT);
	$stmt->bindParam(':ativo', $ativo, PDO::PARAM_BOOL);

	if ($stmt->execute()) {
		echo json_encode(["status" => "success", "msg" => "Endereço IP alterado com sucesso."]);
	} else {
		echo json_encode(["status" => "error", "msg" => "Erro ao alterar o endereço IP."]);
	}

} else {
	echo json_encode(["status" => "error", "msg" => "Não foi possivel efetuar sua escolha."]);
}
