<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

set_time_limit(0);
ini_set('memory_limit', '256M');

require "/www/db/connect.php";
require "/www/db/ConnectionPDO.php";

$conexao = ConnectionPDO::getConnection()->getLink();

$sql = "select ug_id, COALESCE(ug_nome_fantasia, ug_nome) as nome, (select count(*) from tb_dist_venda_games where vg_ug_id = ug_id and date(vg_data_inclusao) >= '2023-02-07' and date(vg_data_inclusao) <= '2023-08-07') as qtde from dist_usuarios_games where ug_ativo = 1 and (select count(*) from tb_dist_venda_games where vg_ug_id = ug_id and date(vg_data_inclusao) >= '2023-02-07' and date(vg_data_inclusao) <= '2023-08-07') = 0;";
$stmt = $conexao->prepare($sql);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$file = fopen("/www/log/pdvDesativados.txt", "a+");
$ids = [];
foreach($rows as $key => $pdv){
	fwrite($file, "nome: ".utf8_encode($pdv["nome"])." - id: ".$pdv["ug_id"]." - quatidade: ".$pdv["qtde"]."\n");
	fwrite($file, str_repeat("*", 50)."\n");
	$ids[] = $pdv["ug_id"];
	//echo "nome: ".utf8_encode($pdv["nome"])." - id: ".$pdv["ug_id"]." - quatidade: ".$pdv["qtde"]."<br>";
}
  $pdvs = implode(",", $ids);
  $update = "update dist_usuarios_games set ug_ativo = 0, ug_substatus = 12 where ug_id in(".$pdvs.");";
  $up = $conexao->prepare($sql);
  //$up->bindValue(":CODIGOS", $pdvs);
  $up->execute(); 
  
  if($up->rowCount() > 0){
	  echo "deu certo: ". $up->rowCount();
  }else{
	  echo "erro no update";
  }
  
fclose($file);
 
?>