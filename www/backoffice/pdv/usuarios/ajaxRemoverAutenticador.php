<?php

require_once "/www/includes/constantes.php";
require_once "/www/includes/gamer/functions.php";
require_once "/www/db/connect.php";
require_once "/www/db/ConnectionPDO.php";
require_once "/www/includes/configIP.php";

$id = $_POST["id"];
$codigo = $_POST["codigo"];

if ($codigo != 'Gz8#kV2!mP$Xr9@tQw') {
	http_response_code(400);
	echo json_encode(["situacao" => "error", "msg" => "Acesso negado"]);
	exit;
}

if (empty($id)) {
	http_response_code(400);
	echo json_encode(["situacao" => "error", "msg" => "ID inválido do usuário"]);
	exit;
}

$conexao = ConnectionPDO::getConnection()->getLink();

$query = $conexao->prepare("SELECT ug_ativo from dist_usuarios_games WHERE ug_id = :ID;");
$query->bindValue(":ID", $id);
$query->execute();
$resultado = $query->fetch(PDO::FETCH_ASSOC);

if ($resultado) {
	$ativo = $resultado['ug_ativo'];

	$query = $conexao->prepare("UPDATE dist_usuarios_games SET ug_chave_autenticador = '' WHERE ug_id = :ID;");
	$query->bindValue(":ID", $id);
	$query->execute();

	if ($query->rowCount() > 0) {
		$query = $conexao->prepare("UPDATE dist_usuarios_games SET ug_ativo = :ATIVO WHERE ug_id = :ID;");
		$query->bindValue(":ID", $id);
		$query->bindValue(":ATIVO", $ativo);
		$query->execute();
		if ($query->rowCount() > 0) {
			echo json_encode(["situacao" => "success", "msg" => "Autenticador removido com sucesso."]);
			http_response_code(200);
			exit;
		}

		echo json_encode(["situacao" => "error", "msg" => "Autenticador removido com sucesso, usuário não foi reativado."]);
		http_response_code(200);
		exit;
	}
}

http_response_code(400);
echo json_encode(["situacao" => "error", "msg" => "Autenticador não encontrado, tente novamente"]);
exit;
?>