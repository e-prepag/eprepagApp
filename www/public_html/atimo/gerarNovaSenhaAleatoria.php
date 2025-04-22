<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set("memory_limit", "1024M");
set_time_limit(0); 
require_once "/www/db/connect.php";
require_once "/www/class/classEncryption.php"; 
require_once "/www/db/ConnectionPDO.php";

$conexao = ConnectionPDO::getConnection()->getLink();

function gerarSenha(){
	$tamanho = 12;
	$posibilidades = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz@*{}";
	$chaveFinal = "";

	for($num = 0; $num < $tamanho; $num++){
		
		$letra = $posibilidades[rand(0, (strlen($posibilidades) - 1))];
		$chaveFinal .= $letra;
		
	}

	return $chaveFinal;
}

//$sql = "select * from dist_usuarios_games where (select count(*) from tb_dist_venda_games where vg_ug_id = ug_id and date(vg_data_inclusao) <= '2023-07-27' and date(vg_data_inclusao) >= '2023-01-27') = 0 and ug_ativo = 1;"; 

$sql = "select * from dist_usuarios_games where ug_id = " . 17851; 

$stmt = $conexao->prepare($sql);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

$objEncryption = new Encryption();
$c = 1;
foreach($result as $key => $value){
	
    $senha = $objEncryption->encrypt(trim(gerarSenha()));
    $sqlUpdate = "update dist_usuarios_games set ug_senha = :SENHA where ug_id = :ID;";
	$query = $conexao->prepare($sqlUpdate);
	$query->bindValue(":SENHA", $senha);
	$query->bindValue(":ID", $value["ug_id"]);
	$query->execute();
	
	if($query->rowCount() > 0){
		 echo "Senha antiga: ". $value["ug_senha"]." - nova senha: ". $senha . " para o pdv: ". $value["ug_id"] ." - num: ".$c."<br>";
		 $c++;
	}
	
}


?> 