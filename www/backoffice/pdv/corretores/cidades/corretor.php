<?php
require_once '../../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";
$estado = $_GET['estado'];
$cidade = $_GET['cidade'];
$ps_query = "SELECT distinct ug_cidade,ug_estado FROM dist_usuarios_games where sem_acentos(ug_cidade) ilike sem_acentos('%".str_replace("'", "''", $cidade)."%') and ug_estado = '$estado';";
//echo "SQL: ".$ps_query."<br>";
/// todas as lan que estiverem nessa cidade
//pg_send_query($conex,$ps_query);
//$res1 = pg_get_result($conex);
$res1 = SQLexecuteQuery($ps_query);

$v = 0;
while ($info = pg_fetch_array($res1)) {
	?>
	
	<div id='c<?=$v?>'><input type="radio" name="op" id="r<?=$v;?>" value="<?=$info[0]?>" ><?=$info[0]?></div>
<?php
	$v++;
	
	} ?>

<br>
<br>
<input type="radio" name="op" id="r2" value="op2">
<input type='text' name='palavra' id='palavra' disabled>

<?php
$ps_query = "SELECT distinct ug_cidade,ug_estado FROM dist_usuarios_games where sem_acentos(ug_cidade) ilike sem_acentos('%".str_replace("'", "''", $cidade)."%') and ug_estado = '$estado';";
//echo $ps_query;
/// todas as lan que estiverem nesse bairro
//pg_send_query($conex,$ps_query);
//$res1 = pg_get_result($conex);
$res1 = SQLexecuteQuery($ps_query);

$v = 0;
while ($info = pg_fetch_array($res1)) {

?>
<script>
//CARREGA O CHANGER A PALAVRA QUE VAI CORRIGIR NA TELA FINAL E O BOTÃO SUBMIT//
$("#r<?=$v;?>").click( function() {

var ValorSelecionadoListaCorretor = "<?=$info["ug_cidade"]?>";
var ValorSelecionadoEstado = '<?=$info["ug_estado"]?>';
	$("#word").attr('value',ValorSelecionadoListaCorretor);
	$("#c_estado").attr('value',ValorSelecionadoEstado);
	
			
	 });

	 $("#r2").click( function() {
var ValorSelecionadoEstado = '<?=$info["ug_estado"]?>';
$("#c_estado").attr('value',ValorSelecionadoEstado);
	 });

</script>
<?php
 
 $v++;
 }

?>