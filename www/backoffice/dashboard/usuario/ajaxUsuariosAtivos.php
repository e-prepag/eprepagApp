<?php
header('Content-Type: application/json; charset=utf-8');
require_once "/www/db/connect.php"; 
require_once "/www/db/ConnectionPDO.php"; 
$connection = ConnectionPDO::getConnection()->getLink(); 


$sql = "select count(*) as qtde, case when (select count(*) from tb_venda_games where vg_ultimo_status = '5' and vg_ug_id = ug_id) = 0 then 0 else 1 end
as tem from usuarios_games where ug_ativo = 1 group by tem;";

$query = $connection->prepare($sql);
$query->execute();
$result = $query->fetchAll(PDO::FETCH_ASSOC);


echo json_encode($result);

?>