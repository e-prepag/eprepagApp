<?php 
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";

//echo "<pre>".print_r($_GET,true)."</pre>";
$us_coord_lat = isset($_GET['us_coord_lat']) ? $_GET['us_coord_lat'] : 0;
$us_coord_lng = isset($_GET['us_coord_lng']) ? $_GET['us_coord_lng'] : 0;
$us_google_maps_string = isset($_GET['us_google_maps_string']) ? $_GET['us_google_maps_string'] : '';
$us_id = isset($_GET['us_id']) ? $_GET['us_id'] : 0;
$sql = "UPDATE dist_usuarios_stores_qiwi SET us_coord_lat = $1, us_coord_lng = $2, us_google_maps_string = $3, us_google_maps_status = Null WHERE us_id = $4";
$params = array($us_coord_lat, $us_coord_lng, $us_google_maps_string, $us_id);

$ret = SQLexecuteQueryParams($sql, $params);
if(!$ret) {
	$msg = "Erro ao atualizar geocoordenadas.";
	$smsgHTML = "<font color='red'>$msg</font><br>";
	$smsgJS = "$msg";
} else {
	$msg = "Geocoordenadas atualizadas com sucesso.";
	$smsgHTML = "<font color='blue'>$msg</font><br>";
	$smsgJS = "$msg";
}
echo $smsgHTML;

//die("Stop");

?>

<script language="javascript">
	alert('<?php echo $smsgJS; ?>');
	window.close();
</script>
