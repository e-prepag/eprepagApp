<?php

set_time_limit(0);
ini_set("memory_limit","1024M");


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "/www/db/connect.php";
require_once "/www/db/ConnectionPDO.php";

$conexao = ConnectionPDO::getConnection()->getLink();

$file = fopen("/www/public_html/atimo/p.txt", "a+");
$dados = explode("Senha antiga:", fgets($file));
$c = 1;
foreach($dados as $key => $b){
	$senha = explode("-", $b);
	$pdv = $senha[1];
	$limpar = substr($pdv, (strpos($pdv, "pdv") + 4), (strpos($pdv, ":") - 4));
	$codigo = str_replace(["u", "n", "m", ":", " "], "", $limpar);
	$senhaFinal = trim($senha[0]);
	
	//echo $codigo ." - ". $senhaFinal. "<br>";
	
	if($senhaFinal != "" & $codigo != ""){
		
	  // if($codigo != "76 :" && $codigo != "61 :" && $codigo != "42 :" && $codigo != "6 : "){
		 $sqlUpdate = "update dist_usuarios_games set ug_senha = :SENHA where ug_id = :ID;";
		 $query = $conexao->prepare($sqlUpdate);
		 $query->bindValue(":SENHA", $senhaFinal);
		 $query->bindValue(":ID", $codigo);
		$query->execute();
			
		if($query->rowCount() > 0){
		echo "senha: ". $senhaFinal . " para o pdv ". $codigo ." num: ".$c."<br>";
		$c++;
		}
	   //}else{
		//   echo "senha: ". $senhaFinal . " para o pdv ". $codigo ." num: ".$c."<br>";
	  // }
		
	}
}


?>