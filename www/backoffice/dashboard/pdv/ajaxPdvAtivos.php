<?php
header('Content-Type: application/json; charset=utf-8');
require_once "/www/db/connect.php"; 
require_once "/www/db/ConnectionPDO.php"; 
$connection = ConnectionPDO::getConnection()->getLink(); 


$sql = "select count(*) as qtde, case when (select count(*) from tb_dist_venda_games inner join tb_dist_venda_games_modelo on vg_id = vgm_vg_id 
inner join tb_dist_operadora_games_produto on vgm_ogp_id = ogp_id where vg_ultimo_status = '5' and vg_ug_id = ug_id and ogp_opr_codigo
not in(78)) = 0 then 0 else 1 end as tem from dist_usuarios_games where ug_ativo = 1 group by tem;";

$query = $connection->prepare($sql);
$query->execute();
$result = $query->fetchAll(PDO::FETCH_ASSOC);


echo json_encode($result);

?>