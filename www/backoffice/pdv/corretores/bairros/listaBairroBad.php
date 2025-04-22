<?php
require_once '../../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";

$ps_query = "SELECT distinct ug_bairro FROM dist_usuarios_games group by ug_bairro ";
//pg_send_query($conex,$ps_query);
//$res0 = pg_get_result($conex);
$res0 = SQLexecuteQuery($ps_query);
$total = pg_num_rows($res0);


$ps_query = "SELECT distinct ug_bairro, count (ug_bairro) as total, ug_cidade, ug_estado  FROM dist_usuarios_games where ug_bairro !~ '^[A-Z][a-z]+ [A-Z][a-z] |[A-Z][a-z]' group by ug_bairro,ug_cidade,ug_estado order by ug_bairro";

//echo "SQL: ".$ps_query."<br>";
//die("Stop lista Bairro Bad<br>");
/// todas as lan que estiverem nesse bairro

//pg_send_query($conex,$ps_query);
//$res1 = pg_get_result($conex);
$res1 = SQLexecuteQuery($ps_query);


$bad = pg_num_rows($res1);
echo "Total: $bad - $total <br>";

while ($info = pg_fetch_array($res1)) {
	echo "<nobr>".(($info['ug_bairro'])?$info['ug_bairro']:"<font color='#FF0000'>NO BAIRRO</font>")." (".$info['total'].") - <font color='#0000CC'>".(($info['ug_cidade'])?$info['ug_cidade']:"<font color='#FF0000'>NO CIDADE</font>")."</font> - <font color='#339900'>".(($info['ug_estado'])?$info['ug_estado']:"<font color='#FF0000'>NO ESTADO</font>")."</font></nobr><br>";
}

?>