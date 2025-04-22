<?php
require_once '../../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";

$ps_query = "SELECT distinct ug_cidade FROM dist_usuarios_games group by ug_cidade ";
//echo $ps_query."<br>";
//pg_send_query($conex,$ps_query);
//$res0 = pg_get_result($conex);
$res0 = SQLexecuteQuery($ps_query);

$total = pg_num_rows($res0);

$ps_query = "SELECT distinct count(*) as total, ug_cidade, ug_estado 
				FROM dist_usuarios_games 
				where ug_cidade ~ '[A-z] +[De|Dos|Do|Da|Das]+' 
				group by ug_cidade, ug_estado
				order by ug_cidade";
//echo "SQL: ".$ps_query."<br>";
//die("Stop");
//pg_send_query($conex,$ps_query);
//$res0a = pg_get_result($conex);
$res0a = SQLexecuteQuery($ps_query);

$bad = pg_num_rows($res0a);


$ps_query = "SELECT distinct count(*) as total, ug_cidade, ug_estado 
				FROM dist_usuarios_games 
				where ug_cidade ~ '[A-z] +[De|Dos|Do|Da|Das]+' 
				group by ug_cidade,ug_estado 
				order by ug_cidade";

//echo $ps_query."<br>";
/// todas as lan que estiverem nesse bairro
//pg_send_query($conex,$ps_query);
//$res1 = pg_get_result($conex);
$res1 = SQLexecuteQuery($ps_query);


echo "Total: $bad - $total <br>";

while ($info = pg_fetch_array($res1)) {
	echo "<nobr>".$info['total']." - ".$info['ug_cidade']." (<font color='#336633'>".$info['ug_estado']."</font>)</nobr><br>";
}

