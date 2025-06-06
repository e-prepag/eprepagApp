<?php

ini_set('memory_limit', '200M');
ini_set('max_execution_time', '300');


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "/www/db/connect.php";
require_once "/www/db/ConnectionPDO.php";

$sql = "select ug_id, (select count(*) from tb_venda_games where EXTRACT(YEAR FROM vg_data_inclusao) BETWEEN '2012' and '2022' and vg_ug_id = ug_id) as vendas 
from usuarios_games where ug_ativo = 1 and EXTRACT(YEAR FROM ug_data_inclusao) BETWEEN '2000' and '2012' order by vendas;";

//$sql = "select ug_id, (select count(*) from tb_dist_venda_games where EXTRACT(YEAR FROM vg_data_inclusao) BETWEEN '2012' and '2022' and vg_ug_id = ug_id) as vendas 
//from dist_usuarios_games where ug_ativo = 1 and EXTRACT(YEAR FROM ug_data_inclusao) BETWEEN '2000' and '2012' order by vendas;";

$conexao = ConnectionPDO::getConnection()->getLink();

$query = $conexao->prepare($sql);
$query->execute();
$dados = $query->fetchAll(PDO::FETCH_ASSOC);
$numero = 0;

foreach($dados as $index => $value){
	if($value["vendas"] == 0){
		$numero++;
	}
}

echo $numero;

# PDV COM ZERO VENDAS 376
# USUARIO COM ZERO VENDA 131673

?>

