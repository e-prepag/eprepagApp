<?php
require_once '../../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";
$cidade = $_GET['cidade'];
$estado = $_GET['estado'];
$ps_query = "SELECT distinct ug_cidade, count (ug_cidade) as total FROM dist_usuarios_games where sem_acentos(ug_cidade) ilike sem_acentos('%".str_replace("'", "''", $cidade)."%') and ug_estado = '$estado' group by ug_cidade ;";
//echo $ps_query;
/// todas as lan que estiverem nesse cidade
//pg_send_query($conex,$ps_query);
//$res1 = pg_get_result($conex);
$res1 = SQLexecuteQuery($ps_query);

?>

<form id="formfinal" name="formfinal" method="post" action="corrigeCidade.php">
  <?php
  $f = 0;
  while ($info = pg_fetch_array($res1)) {
	?><input type="checkbox" name="varia<?php echo $f?>" value="<?php echo $info[0]?>" ><?php echo $info[0]?>(<?php echo $info[1]?>)<br>
<?php $f++;
} ?>
<input type='hidden' value='<?php echo $f?>' name='f' id='f'>
<input type='texto' value='' id='word' name='word' readonly>
<input type='button' value='ALTERAR !' id='carregar' name='carregar' disabled>
<input type='hidden' value='' name='c_estado' id='c_estado'>
</form>
<script>

	//////Checar se tem palavra para ativar o submit ///
	////////////////////////////////////////////////////
$("input:checkbox").click( function() {

	var qtdPalavras = document.getElementById('word').value;
	var cidade = "<?php echo $cidade?>";

//alert('("input:checked").length: '+$("input:checked").length+'\nqtdPalavras: '+qtdPalavras);

	if ($("input:checked").length > 1 && qtdPalavras != ''  ) {
		$("#carregar").removeAttr('disabled');
		
		} else {
	 $("#carregar").attr('disabled','disabled');
		
		}
	
	
});
////////////////////////////////////////////////////////
////////////////////////////////////////////////////////
</script>