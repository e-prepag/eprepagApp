<?php
require_once '../../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";
die("Stop");

//$ps_query = "SELECT distinct count (ug_cidade), ug_cidade, ug_estado as total FROM dist_usuarios_games where ug_cidade !~ '^[A-Z][a-z]+ [A-Z][a-z] |[A-Z][a-z]' group by ug_cidade,ug_estado order by ug_cidade";
$ps_query = "SELECT distinct count (*) as total, ug_cidade, ug_estado, (case when ug_cidade !~ '^[A-Z][a-z]+ [A-Z][a-z] |[A-Z][a-z]' then 'erro' when ug_cidade ~ '^[A-Z][a-z]+ [A-Z][a-z] |[A-Z][a-z]' then 'ok' end) as ug_tipo_erro FROM dist_usuarios_games  group by ug_cidade, ug_estado order by ug_tipo_erro, ug_estado, ug_cidade";

//pg_send_query($conex,$ps_query);
//$res1 = pg_get_result($conex);
$res1 = SQLexecuteQuery($ps_query);

echo "Start: ".date("Y-M-d H:i:s")."<br>";

// loop por cada bairro
while ($info = pg_fetch_array($res1)) {

	$total = $info['total'];
	$ug_cidade = str_replace("'", "''", $info['ug_cidade']);
	$ug_estado = $info['ug_estado'];
	$ug_tipo_erro = $info['ug_tipo_erro'];

	$ug_cidade_nova = ucwords(strtolower($ug_cidade));

	$query = "update dist_usuarios_games set ug_cidade = '$ug_cidade_nova' where ug_cidade = '$ug_cidade';";

	if($ug_cidade) {
		if($ug_tipo_erro=="ok") {
			echo "<font color='#0000CC'>REGISTRO OK: '$ug_cidade', '$ug_estado'</font><br>";
		} else if($ug_tipo_erro=="erro") {
			$query = "update dist_usuarios_games set ug_cidade = '$ug_cidade_nova' where ug_cidade='$ug_cidade' and ug_estado='$ug_estado';";

		//	echo $query.'<br>';
		//	echo 'UPDATE Bloqueado!!! <br>';
		//	pg_send_query($conex,$query);
		//	$res = SQLexecuteQuery($query);
		} else {
			echo "<font color='#FF0000'>REGISTRO tipo desconhecido: '$ug_tipo_erro' ('$ug_cidade', '$ug_estado')</font><br>";
		}
	} else {
		echo "Cidade vazia (cidade: '$ug_cidade', ug_estado: '$ug_estado', total: $total)<br>";
	}

}
echo "End: ".date("Y-M-d H:i:s")."<br>";

?>