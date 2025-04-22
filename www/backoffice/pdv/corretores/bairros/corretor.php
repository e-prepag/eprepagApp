<?php
require_once '../../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";
$estado = str_replace("'","\'",($_GET['estado']));
$cidade = str_replace("'","\'",urldecode($_GET['cidade']));
$bairro = str_replace("'","\'",urldecode($_GET['bairro']));


$ps_query = "SELECT distinct ug_bairro,ug_cidade, ug_estado, count(*) as n FROM dist_usuarios_games where sem_acentos(ug_bairro) ilike sem_acentos('%$bairro%') and ug_cidade = '$cidade' and ug_estado = '$estado' group by ug_bairro,ug_cidade, ug_estado;";
//echo "SQL 4334:". $ps_query;
/// todas as lan que estiverem nesse bairro
//pg_send_query($conex,$ps_query);
//$res1 = pg_get_result($conex);
$res1 = SQLexecuteQuery($ps_query);

$v = 0;
while ($info = pg_fetch_array($res1)) {
?>
	<nobr><div id='c<?php echo $v?>'><input type="radio" name="op" id="r<?php echo $v;?>" value="<?php echo $info['ug_bairro']?>"><?php echo "'".$info['ug_bairro']."', '".$info['ug_cidade']."' '".$info['ug_estado']."'  (n: ".$info['n'].")" ?></div></nobr>
<?php
	$v++;
} 
?>

<br>
<br>
<input type="radio" name="op" id="r2" value="op2">
<input type='text' name='palavra' id='palavra' disabled>

<?php
$ps_query = "SELECT distinct ug_bairro,ug_cidade, ug_estado, count(*) as n FROM dist_usuarios_games where sem_acentos(ug_bairro) ilike sem_acentos('%$bairro%') and ug_cidade = '$cidade' and ug_estado = '$estado' group by ug_bairro,ug_cidade, ug_estado;";
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
$("#r<?php echo $v;?>").click( function() {

var ValorSelecionadoListaCorretor = "<?php echo $info["ug_bairro"]?>";
var ValorSelecionadoEstado = "<?php echo $info["ug_estado"]?>";
var ValorSelecionadoCidade = "<?php echo $info["ug_cidade"]?>";
	$("#word").attr('value',ValorSelecionadoListaCorretor);
	$("#c_estado").attr('value',ValorSelecionadoEstado);
	$("#c_cidade").attr('value',ValorSelecionadoCidade);
	
			
	 });

	 $("#r2").click( function() {
var ValorSelecionadoCidade = "<?php echo $info["ug_cidade"]?>";
$("#c_cidade").attr('value',ValorSelecionadoCidade);
	 });

</script>
<?php
 
 $v++;
 }

?>