<?php
require_once '../../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";
die("Stop corretor de Upper<br>");

echo "Start: ".date("Y-M-d H:i:s")."<br>";

$ps_query = "SELECT distinct count (*) as total, ug_bairro, ug_cidade, ug_estado, (case when ug_bairro !~ '^[A-Z][a-z]+ [A-Z][a-z] |[A-Z][a-z]' then 'erro' when ug_bairro ~ '^[A-Z][a-z]+ [A-Z][a-z] |[A-Z][a-z]' then 'ok' end) as ug_tipo_erro FROM dist_usuarios_games  group by ug_bairro, ug_cidade, ug_estado order by ug_tipo_erro, ug_estado, ug_cidade, ug_bairro";
	// where ug_bairro !~ '^[A-Z][a-z]+ [A-Z][a-z] |[A-Z][a-z]'

//echo $ps_query."<br>";
//pg_send_query($conex,$ps_query);
//$res1 = pg_get_result($conex);
$res1 = SQLexecuteQuery($ps_query);

// loop por cada bairro
while ($info = pg_fetch_array($res1)) {

	$total = $info['total'];
	$ug_bairro = $info['ug_bairro'];
	$ug_bairro = str_replace("'", "''", $info['ug_bairro']);
	$ug_cidade = $info['ug_cidade'];
	$ug_cidade = str_replace("'", "''", $info['ug_cidade']);
	$ug_estado = $info['ug_estado'];
	$ug_tipo_erro = $info['ug_tipo_erro'];

	$ug_bairro_novo = ucwords(strtolower($ug_bairro));

	//echo $bairrof."<br>";

	if($ug_bairro) {
		if($ug_tipo_erro=="ok") {
			echo "<font color='#0000CC'>REGISTRO OK: '$ug_bairro', '$ug_cidade', '$ug_estado'</font><br>";
		} else if($ug_tipo_erro=="erro") {
			$query = "update dist_usuarios_games set ug_bairro = '$ug_bairro_novo' where ug_bairro = '$ug_bairro' and ug_cidade='$ug_cidade' and ug_estado='$ug_estado';";

			echo $query.'<br>';
		//	echo 'UPDATE Bloqueado!!! <br>';
			//pg_send_query($conex,$query);
			$res = SQLexecuteQuery($query);
		} else {
			echo "<font color='#FF0000'>REGISTRO tipo desconhecido: '$ug_tipo_erro' ('$ug_bairro', '$ug_cidade', '$ug_estado')</font><br>";
		}
	} else {
		echo "Bairro vazio (cidade: '$ug_cidade', ug_estado: '$ug_estado', total: $total)<br>";
	}
}

echo "End: ".date("Y-M-d H:i:s")."<br>";


?>