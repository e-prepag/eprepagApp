<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 0); 
session_start();

$file = fopen("/www/public_html/sys/admin/pins/dadosRecebidos.txt", "a+");
fwrite($file, "Data ".date("d-m-Y H:i:s")."\n");
fwrite($file, json_encode($_POST)."\n");
fclose($file);

header('Content-Type: application/json; charset=utf-8');

if(isset($_SESSION["userlogin_bko"])){
	
	require_once "/www/db/connect.php"; 
	require_once "/www/db/ConnectionPDO.php"; 
	$connection = ConnectionPDO::getConnection()->getLink(); 
	
	if(!empty($_POST["codPin"])){
		//
		$sql = "delete from trava_qtde_pin where pin = :CODIGO and qtde > 2 and (select count(*) from pins where pin_codigo = :CODIGO2 and pin_status not in('8', '9')) > 0;";
		$query = $connection->prepare($sql);
		$query->bindValue(":CODIGO", $_POST["codPin"]);
		$query->bindValue(":CODIGO2", $_POST["codPin"]);
		$query->execute();
		
		if($query->rowCount() > 0){
			echo json_encode(["mensagem" => "Pin desbloqueado com sucesso", "type" => "sucesso"]);
		}else{ 
			echo json_encode(["mensagem" => "Nenhum bloqueio foi encontrado", "type" => "erro"]);
		}
		
	}else{
		echo json_encode(["mensagem" => "Código pin vazio", "type" => "erro"]);
	}
	
}else{
	echo json_encode(["mensagem" => "Não foi possivel desbloquear o pin", "type" => "erro"]);
}

?>