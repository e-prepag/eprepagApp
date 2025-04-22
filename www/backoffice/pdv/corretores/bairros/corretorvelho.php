<?php
require_once '../../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";
$cidade = urldecode($_GET['cidade']);
$bairro = urldecode($_GET['bairro']);
$ps_query = "SELECT distinct ug_bairro FROM dist_usuarios_games where sem_acentos(ug_bairro) ilike sem_acentos('%$bairro%') and ug_cidade = '$cidade';";
//echo $ps_query;
// todas as lan que estiverem nesse bairro
//pg_send_query($conex,$ps_query);
//$res1 = pg_get_result($conex);
$res1 = SQLexecuteQuery($ps_query);
?>
<input type="radio" name="op" id="r1" value="op1" checked>
<select id='lista_corretor' name='lista_cidades'>
  <option value=''>Escolha uma forma de Correção</option>
  <?php
  while ($info = pg_fetch_array($res1)) {
	?>
	<option value='<?=$info[0]?>'><?=$info[0]?></option>
<?php
	} ?>
</select>
<br>
<br>
<input type="radio" name="op" id="r2" value="op2">
<input type='text' name='palavra' id='palavra' disabled>