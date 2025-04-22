<?php
require_once '../../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";

$ps_query = "SELECT distinct ug_cidade FROM dist_usuarios_games group by ug_cidade";
//pg_send_query($conex,$ps_query);
//$res0 = pg_get_result($conex);
$res0 = SQLexecuteQuery($ps_query);
$total_de_cidades = pg_num_rows($res0);


$ps_query = "SELECT distinct count (ug_cidade) as total, ug_cidade FROM dist_usuarios_games where ug_cidade ~ '^[A-Z][a-z]+ [A-Z][a-z] |[A-Z][a-z]' group by ug_cidade order by ug_cidade";

//echo "SQL: ".$ps_query."<br>";
//die("Stop");
/// todas as lan que estiverem nesse bairro

//pg_send_query($conex,$ps_query);
//$res1 = pg_get_result($conex);
$res1 = SQLexecuteQuery($ps_query);

$total_de_cidades_sem_erro = pg_num_rows($res1);
echo "total_de_cidades_sem_erro: $total_de_cidades_sem_erro - total_de_cidades: $total_de_cidades (".number_format((100*$total_de_cidades_sem_erro/ $total_de_cidades), 2, '.', '.').") <br>";

while ($info = pg_fetch_array($res1)) {
	echo "<nobr>".$info['total']." - ".$info['ug_cidade']." </nobr><br>";	
	// (<font color='#336633'>".$info['ug_estado']."</font>)
}

