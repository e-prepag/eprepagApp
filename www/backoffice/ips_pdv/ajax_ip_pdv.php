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
					COALESCE(u.shn_nome, 'Desconhecido') as shn_nome,
					ip.domain
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

			$btnVisualizar = "<a class='btn btn-info' href='usuario.php?ug_id=" . $value["ug_id"] . "'
                    style='border-width: 0px; border-radius: 1px; box-shadow: 1px 1px 5px rgb(0,0,0,0.5); display: flex; justify-content: center; align-items: center; padding: 5px 10px;' title='Visualizar'>
                    Visualizar
                </a>";

			$btnPesquisar = "<form action='/pdv/keys/key.php' target='_blank' class='dados' method='POST' style='margin: 0; display: flex; gap: 5px;'>
                        <input type='hidden' value='" . $value["ug_id"] . "' name='selecaoPdv' class='inpId form-control form-control-sm' placeholder='CÃ³digo PDV' style='width: 100px;'>
                        <button type='submit' name='Pesquisar' class='btenvia btn btn-primary btn-sm' style='border-radius: 1px; box-shadow: 1px 1px 5px rgb(0,0,0,0.5);'>
                            Ver chave
                        </button>
                    </form>";

			$acao = "<div style='display: flex; gap: 10px; align-items: center; justify-content: center;'>
                $btnVisualizar
                $btnPesquisar
             </div>";

			if ($value["ip_range"] == true) {
				$str_ip = "{$value["ip_range_ini"]} - {$value["ip_range_end"]}";
			} else {
				$str_ip = $value["ip_address"];
			}

			$dataLine = [
				"ug_id" => $value["ug_id"],
				"ug_nome" => (isset($value["ug_nome_fantasia"]) ? utf8_encode($value["ug_nome_fantasia"]) : "Não encontrado"),
				"ip_pdv" => $str_ip,
				"dominio_site" => $value["domain"] ?? "Sem site",
				"shn_nome" => utf8_encode($value["shn_nome"]),
				"criado_em" => isset($value["created_date"]) ? $value["created_date"] : "",
				"ativo" => isset($value["active"]) ? ($value["active"] ? "Sim" : "NÃ£o") : "",
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
	$domain = isset($_POST['domain']) ? $_POST['domain'] : "";
	$ipType = isset($_POST['ipType']) ? $_POST['ipType'] : "single";

	if ($ug_id <= 0) {
		echo json_encode(["status" => "error", "msg" => "Usuário inválido."]);
		die;
	}

	$ip_address = null;
	$ip_range_ini = null;
	$ip_range_end = null;
	$ip_range = ($ipType === 'range');

	if ($ip_range) {
		$ip_range_ini = isset($_POST['ip_range_ini']) ? $_POST['ip_range_ini'] : "";
		$ip_range_end = isset($_POST['ip_range_end']) ? $_POST['ip_range_end'] : "";

		if (empty($ip_range_ini) || empty($ip_range_end)) {
			echo json_encode(["status" => "error", "msg" => "IPs inválidos."]);
			die;
		}
	} else {
		$ip_address = isset($_POST['ip_address']) ? $_POST['ip_address'] : "";
		if (empty($ip_address)) {
			echo json_encode(["status" => "error", "msg" => "Endereço IP inválido."]);
			die;
		}
	}

	// 2) Insere a nova observação
	$sql = "INSERT INTO pdv_api_ip (ug_id, ip_address, ip_range_ini, ip_range_end, ip_range, domain, bko_user) VALUES (:ug_id, :ip_address, :ip_range_ini, :ip_range_end, :ip_range, :domain, :bko_user) RETURNING id";
	$stmt = $conexao->prepare($sql);
	$stmt->bindParam(':ug_id', $ug_id);
	$stmt->bindParam(':ip_address', $ip_address, PDO::PARAM_STR);
	$stmt->bindParam(':ip_range_ini', $ip_range_ini, PDO::PARAM_STR);
	$stmt->bindParam(':ip_range_end', $ip_range_end, PDO::PARAM_STR);
	$stmt->bindParam(':ip_range', $ip_range, PDO::PARAM_BOOL);
	$stmt->bindParam(':domain', $domain, PDO::PARAM_STR);
	$stmt->bindParam(':bko_user', $_POST['bko_user'], PDO::PARAM_STR);

	if ($stmt->execute()) {
		$id = $stmt->fetchColumn();
		$msgRetorno = $ip_address ? $ip_address : "$ip_range_ini - $ip_range_end";
		echo json_encode(["status" => "success", "msg" => $msgRetorno, "id" => $id]);
	} else {
		echo json_encode(["status" => "error", "msg" => "Erro ao registrar a nova observação."]);
	}
	die;
} else if (isset($_POST["acao"]) && $_POST["acao"] == "editar") {

	$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
	if ($id <= 0) {
		echo json_encode(["status" => "error", "msg" => "ID do IP inválido."]);
		die;
	}

	$domain = isset($_POST['domain']) ? $_POST['domain'] : "";
	$ipType = isset($_POST['ipType']) ? $_POST['ipType'] : "single";

	$ip_address = null;
	$ip_range_ini = null;
	$ip_range_end = null;
	$ip_range = ($ipType === 'range');

	if ($ip_range) {
		$ip_range_ini = isset($_POST['ip_range_ini']) ? $_POST['ip_range_ini'] : "";
		$ip_range_end = isset($_POST['ip_range_end']) ? $_POST['ip_range_end'] : "";

		if (empty($ip_range_ini) || empty($ip_range_end)) {
			echo json_encode(["status" => "error", "msg" => "IPs inválidos."]);
			die;
		}
	} else {
		$ip_address = isset($_POST['ip_address']) ? $_POST['ip_address'] : "";
		if (empty($ip_address)) {
			echo json_encode(["status" => "error", "msg" => "Endereço IP inválido."]);
			die;
		}
	}

	$sql = "UPDATE pdv_api_ip SET ip_address = :ip_address, ip_range_ini = :ip_range_ini, ip_range_end = :ip_range_end, ip_range = :ip_range, domain = :domain WHERE id = :id";
	$stmt = $conexao->prepare($sql);
	$stmt->bindParam(':id', $id, PDO::PARAM_INT);
	$stmt->bindParam(':ip_address', $ip_address, PDO::PARAM_STR);
	$stmt->bindParam(':ip_range_ini', $ip_range_ini, PDO::PARAM_STR);
	$stmt->bindParam(':ip_range_end', $ip_range_end, PDO::PARAM_STR);
	$stmt->bindParam(':ip_range', $ip_range, PDO::PARAM_BOOL);
	$stmt->bindParam(':domain', $domain, PDO::PARAM_STR);

	if ($stmt->execute()) {
		echo json_encode(["status" => "success", "msg" => "IP atualizado com sucesso."]);
	} else {
		echo json_encode(["status" => "error", "msg" => "Erro ao atualizar IP."]);
	}
	die;
} else if (isset($_POST["acao"]) && $_POST["acao"] == "remover") {
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
} else if (isset($_POST["acao"]) && $_POST["acao"] == "alterar") {

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
