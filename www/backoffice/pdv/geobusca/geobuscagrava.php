<?php 
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";

//echo "<pre>".$_GET."</pre>";
$ug_coord_lat = isset($_GET['ug_coord_lat']) ? $_GET['ug_coord_lat'] : 0;
$ug_coord_lng = isset($_GET['ug_coord_lng']) ? $_GET['ug_coord_lng'] : 0;
$ug_google_maps_string = isset($_GET['ug_google_maps_string']) ? $_GET['ug_google_maps_string'] : '';
$ug_id = isset($_GET['ug_id']) ? $_GET['ug_id'] : 0;
$sql = "UPDATE dist_usuarios_games SET ug_coord_lat = $1, ug_coord_lng = $2, ug_google_maps_string = $3, ug_google_maps_status = Null WHERE ug_id = $4";
$params = array($ug_coord_lat, $ug_coord_lng, $ug_google_maps_string, $ug_id);
//echo $sql."<br>";

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
