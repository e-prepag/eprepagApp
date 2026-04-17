<?php
require_once "/www/backoffice/includes/encoding.php";
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
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
	$dataInicio = validarData($_POST['dt_inicial'] ?? '') ? ($_POST['dt_inicial'] ?? '') : null;
	$dataFim = validarData($_POST['dt_final'] ?? '') ? ($_POST['dt_final'] ?? '') . " 23:59:59" : null;
	$usuario = isset($_POST['usuario_id']) ? trim((string)($_POST['usuario_id'] ?? "")) : null;

	// 2) Monta a parte fixa do SELECT
	$sql = "SELECT uat.versao_termo, uat.aceitou, uat.data_aceite, uat.ip, uat.dispositivo, uat.localizacao, ug.ug_nome_fantasia, ug.ug_id from dist_usuarios_games ug
				left join dist_usuarios_aceito_termos uat on ug.ug_id = uat.ug_id 
				where ";

	if ($usuario != null) {
		if (is_numeric($usuario)) {
			$usuario = intval($usuario);
			$sql .= "ug.ug_id = :usuario_id ";
		} else {
			$usuario = "%$usuario%";
			$sql .= "ug.ug_nome_fantasia ILIKE :usuario_id ";
		}
	} else {
		$sql .= "1=1 ";
	}
	if ($dataInicio && $dataFim) {
		$sql .= " AND uat.data_aceite BETWEEN :data_inicio AND :data_fim ";
	} elseif ($dataInicio) {
		$sql .= " AND uat.data_aceite >= :data_inicio ";
	} elseif ($dataFim) {
		$sql .= " AND uat.data_aceite <= :data_fim ";
	}
	$sql .= "ORDER BY uat.data_aceite DESC NULLS LAST LIMIT 100";

	//echo $sql;
	$stmt = $conexao->prepare($sql);
	//echo $sql;
	// 3) Prepara os parâmetros
	if ($usuario != null) {
		$stmt->bindParam(':usuario_id', $usuario);
	}
	if ($dataInicio) {
		$stmt->bindParam(':data_inicio', $dataInicio, PDO::PARAM_STR);
	}
	if ($dataFim) {
		$stmt->bindParam(':data_fim', $dataFim, PDO::PARAM_STR);
	}

	$stmt->execute();
	$resultRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

	$data = ["data" => []];

	if ((is_countable($resultRows) ? count($resultRows) : 0) > 0) {
		foreach ($resultRows as $key => $value) {
			$dataKeys = array_keys($value);

			$svg = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 576 512">
    					<path d="M572.5 241.4c-1.5-2.3-38.4-57.3-107.2-102.5C407.3 94.1 337.2 80 288 80s-119.3 14.1-177.3 58.9C42.9 184.1 6.1 239.1 4.5 241.4a32.1 32.1 0 0 0 0 29.2c1.5 2.3 38.4 57.3 107.2 102.5C168.7 417.9 238.8 432 288 432s119.3-14.1 177.3-58.9c68.8-45.2 105.6-100.2 107.2-102.5a32.1 32.1 0 0 0 0-29.2zM288 384c-79.5 0-144-64.5-144-144s64.5-144 144-144 144 64.5 144 144-64.5 144-144 144zm0-240a96 96 0 1 0 0 192 96 96 0 0 0 0-192z"/>
  					</svg>';

			$acao = "<a class='btn btn-info' href='usuario.php?usuario_id=" . $value["ug_id"] . "'
							style='border-width: 0px;border-radius: 1px;box-shadow: 1px 1px 5px rgb(0,0,0,0.5); display: flex;width: 100%;justify-content: center;
							data-atual='" . ($_GET["reload"] ?? "") . "'
						>
							$svg
						</a>";

			if (preg_match('/Lat:\s*(-?\d+\.\d+),\s*Lon:\s*(-?\d+\.\d+)/', $value["localizacao"], $matches)) {
				$lat = $matches[1];
				$lon = $matches[2];

				$googleMapsUrl = "https://www.google.com/maps?q=$lat,$lon";

				$localizacao = "<a href='$googleMapsUrl' target='_blank' rel='noopener noreferrer'>{$value['localizacao']}</a>";
			} else {
				$localizacao = ($value["localizacao"] ? $value["localizacao"] : "N/A");
			}

			$dataLine = [
				$dataKeys[0] => ($value["versao_termo"] ? backoffice_iso_to_utf8($value["versao_termo"]) : "N/A"),
				$dataKeys[1] => ($value["aceitou"] ? "Sim" : "Não"),
				$dataKeys[2] => ($value["data_aceite"] ? $value["data_aceite"] : "N/A"),
				$dataKeys[3] => ($value["ip"] ? $value["ip"] : "Não possui"),
				$dataKeys[4] => ($value["dispositivo"] ? backoffice_iso_to_utf8($value["dispositivo"]) : "N/A"),
				$dataKeys[5] => $localizacao,
				$dataKeys[6] => ($value["ug_nome_fantasia"] ? backoffice_iso_to_utf8($value["ug_nome_fantasia"]) : "Não encontrado"),
				$dataKeys[7] => ($value["ug_id"] ? $value["ug_id"] : "Erro"),
				"acao" => $acao
			];
			array_push($data["data"], $dataLine);
		}
	}
	echo json_encode($data, JSON_INVALID_UTF8_SUBSTITUTE | JSON_UNESCAPED_UNICODE);
	die;
} else if (isset($_POST["acao"]) && $_POST["acao"] == "remover") {

	$usuario = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;

	if ($usuario <= 0) {
		echo "Usuário inválido.";
		die;
	}

	// 2) Insere a nova observação
	$sql = "DELETE from dist_usuarios_aceito_termos where ug_id = :user_id";
	$stmt = $conexao->prepare($sql);
	$stmt->bindParam(':user_id', $usuario, PDO::PARAM_INT);

	if ($stmt->execute()) {
		echo 1;
	} else {
		echo "Erro ao excluir.";
	}
	die;

} else {
	echo "Não foi possivel efetuar sua escolha";
}
