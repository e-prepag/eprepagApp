<?php

require_once "/www/includes/constantes.php";
require_once "/www/includes/gamer/functions.php";
require_once "/www/db/connect.php";
require_once "/www/db/ConnectionPDO.php";
require_once "/www/class/classEmailAutomatico.php";
require_once "/www/class/phpmailer/class.phpmailer.php";
require_once "/www/includes/configIP.php";
require_once "/www/class/phpmailer/class.smtp.php";
require_once "/www/class/pdv/classChaveMestra.php";


$envia_email = new EnvioEmailAutomatico('P', 'ChaveMestra');

$id = $_POST["id"];

$conexao = ConnectionPDO::getConnection()->getLink();

$query = $conexao->prepare("select ug_nome_fantasia, ug_email, chave from dist_usuarios_games inner join dist_usuarios_games_chave on ug_id = usuario where ug_id = :ID;");
$query->bindValue(":ID", $id);
$query->execute();
$retorno = $query->fetch(PDO::FETCH_ASSOC);

if($retorno != false){
	
	$envia_email->setUgNome($retorno["ug_nome_fantasia"]);
	$envia_email->setChaveMestra($retorno["chave"]);

	$to = strtolower($retorno["ug_email"]);
	$cc = ""; 
	$bcc = "";
	$subject = utf8_decode("E-prepag - Código de Segundo Fator de Autenticação");
	$msg = $envia_email->getCorpoEmail();

	$retorno = enviaEmail3($to, $cc, $bcc, $subject, $msg, "");

	if($retorno == true){
	   echo json_encode(["situacao" => "success", "msg" => "Chave enviada com sucesso"]);
	   http_response_code(200);
	   exit;   
	}
}

  http_response_code(400);
  echo json_encode(["situacao" => "error", "msg" => "Chave não enviada, por favor tente novamente"]);
  exit;
?>