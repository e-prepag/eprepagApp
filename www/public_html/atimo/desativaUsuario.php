<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

set_time_limit(0);
ini_set("memory_limit","1024M");

include "/www/db/connect.php";
include "/www/db/ConnectionPDO.php";

$conexao = ConnectionPDO::getConnection()->getLink();

$select = "select ug_id, (select count(*) from tb_venda_games where vg_ug_id = ug_id and date(vg_data_inclusao) >= '2022-08-01') as quantidade from usuarios_games
where ug_ativo = 1 and (select count(*) from tb_venda_games where vg_ug_id = ug_id and date(vg_data_inclusao) >= '2022-08-01') = 0 and date(ug_data_inclusao) < '2023-01-01';";
$busca = $conexao->prepare($select);
$busca->execute();
$retorno = $busca->fetchAll(PDO::FETCH_ASSOC);

$ids = implode(",", array_column($retorno, "ug_id"));

$file = fopen("/www/log/id.txt", "a+");
fwrite($file, $ids);
fclose($file);

$updateSql = "update usuarios_games set ug_ativo = 6 where ug_id in(".$ids.");";
$update = $conexao->prepare($updateSql);
$update->execute();

var_dump($ids);
?>