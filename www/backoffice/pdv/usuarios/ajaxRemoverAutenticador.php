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

$query = $conexao->prepare("UPDATE dist_usuarios_games SET ug_chave_autenticador = '' WHERE ug_id = :ID;");
$query->bindValue(":ID", $id);
$query->execute();

if ($query->rowCount() > 0) {

	echo json_encode(["situacao" => "success", "msg" => "Autenticador removido com sucesso, usuário foi inativado."]);
	http_response_code(200);
	exit;
}

http_response_code(400);
echo json_encode(["situacao" => "error", "msg" => "Autenticador não encontrado, tente novamente"]);
exit;
?>