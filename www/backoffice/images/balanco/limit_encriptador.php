<?php include "../includes/classPrincipal.php"; ?>
<?php include "../../../incs/topo_bko.php"; ?>
<?php
echo "<h1> Encriptando Saldo </h1><br><br>";

$objEncryption = new Encryption();

// Seleciona cada lanhouse :
$query = "select ug_id,ug_perfil_limite from dist_usuarios_games where ug_id = '295' ";

echo $query;

$rs_query = SQLexecuteQUERY($query);
$data = date("Y-m-d H:i:s");

while ($balanco_info = pg_fetch_array($rs_query)) {

	$limite  = $objEncryption->encrypt($balanco_info['ug_perfil_limite']);	
	
	$id_lan = $balanco_info['ug_id'];
	
	$query_insert = "update dist_usuarios_games set ug_perfil_limite = '$limite' where ug_id = '$id_lan' ";
	

	SQLexecuteQUERY($query_insert);

	echo $query_insert."<br>";

}
	
?>
