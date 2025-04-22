<?php

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

require "/www/db/connect.php";
require "/www/db/ConnectionPDO.php";
require_once "/www/includes/gamer/chave.php";
require_once "/www/includes/gamer/AES.class.php";

$conexao = ConnectionPDO::getConnection()->getLink();

if(!isset($_POST["check"])){
	$visualiza = 'N';
}else{
	$visualiza = 'S';
}
	
$query = $conexao->prepare("select count(*) from usuarios where shn_mail = :EMAIL;");
$query->bindValue(":EMAIL", $_POST["email"]);	
$query->execute();
$retorno = $query->fetch(PDO::FETCH_ASSOC);
	
if($retorno["count"] > 0){
	echo json_encode(["type" => "error", "msg" => "E-mail já cadastrado"]);
	exit;
}

$query = $conexao->prepare("select max(id) as ultimo from usuarios;");
$query->execute();
$id = $query->fetch(PDO::FETCH_ASSOC)["ultimo"] + 1;

$chave256bits = new Chave();
$aes = new AES($chave256bits->retornaChavePub());
$passw = base64_encode($aes->encrypt(addslashes($_POST["passw"])));
$bko_local = ($_POST["tipo"] == 'AT')? '111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111': '110000000000';

$insertQuery = $conexao->prepare("insert into usuarios(shn_login,shn_password,id,shn_mail,shn_codigovinculo,bko_autoriza,tipo_acesso,visualiza_dados,bko_local_acesso)values(:LOGIN,:PASS,:ID,:EMAIL,:COD,:AUTO,:TIPO,:VISU,:BKO);");
$insertQuery->bindValue(":LOGIN", strtoupper($_POST["login"]));
$insertQuery->bindValue(":PASS", $passw);
$insertQuery->bindValue(":ID", $id);
$insertQuery->bindValue(":EMAIL", $_POST["email"]);
$insertQuery->bindValue(":COD", $id);
$insertQuery->bindValue(":AUTO", "S");
$insertQuery->bindValue(":TIPO", $_POST["tipo"]);
$insertQuery->bindValue(":VISU", $visualiza);
$insertQuery->bindValue(":BKO", $bko_local);
$insertQuery->execute();

if($insertQuery->rowCount() > 0){
	echo json_encode(["type" => "success", "msg" => "Usuário cadastrado com sucesso"]);
	exit;
}else{
	echo json_encode(["type" => "error", "msg" => "O usuário não foi cadastardo"]);
	exit;
}


?>