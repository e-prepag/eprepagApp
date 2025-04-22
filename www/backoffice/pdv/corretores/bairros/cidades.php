<?php
require_once '../../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";
$estado = $_GET['estado'];
$ps_query = "SELECT distinct ug_cidade FROM dist_usuarios_games where ug_estado = '$estado';";
//echo $ps_query;
// todas as lan que estiverem nesse bairro
//pg_send_query($conex,$ps_query);
//$res1 = pg_get_result($conex);
$res1 = SQLexecuteQuery($ps_query);



?>
<select id='lista_cidades' name='lista_cidades'>
  <option value=''>Cidades</option>
  <?php
  while ($info = pg_fetch_array($res1)) {
	?>
	<option value='<?=$info[0]?>'><?=$info[0]?></option>
<?php
	} ?>
  </select>
