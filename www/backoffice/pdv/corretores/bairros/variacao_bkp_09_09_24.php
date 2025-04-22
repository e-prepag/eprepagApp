<?php
require_once '../../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";
$estado = str_replace("'","\'",urldecode($_GET['estado']));
$cidade = str_replace("'","\'",urldecode($_GET['cidade']));
$bairro = str_replace("'","\'",urldecode($_GET['bairro']));
$ps_query = "SELECT distinct ug_bairro, count (ug_bairro) as total FROM dist_usuarios_games where sem_acentos(ug_bairro) ilike sem_acentos('%$bairro%') and ug_cidade = '$cidade' and ug_estado = '$estado' group by ug_bairro ;";
///echo $ps_query;
/// todas as lan que estiverem nesse bairro
//pg_send_query($conex,$ps_query);
//$res1 = pg_get_result($conex);
$res1 = SQLexecuteQuery($ps_query);

?>

<form id="formfinal" name="formfinal"  method="post" action="corrigeBairro.php">
  <?php
  $f = 0;
  while ($info = pg_fetch_array($res1)) {
	?><input type="checkbox" name="varia<?php echo $f?>" id="varia<?php echo $f?>" value="<?php echo $info[0]?>" ><?php echo $info[0]?>(<?php echo $info[1]?>)<br>
<?php $f++;
} ?>
<input type='hidden' value='<?php echo $f?>' name='f' id='f'>
<input type='text' value='' id='word' name='word' readonly>
<input type='button' value='MODIFICAR !' id='carregar' name='carregar' disabled>
<input type='hidden' name='c_cidade' id='c_cidade' value='<?php echo $cidade?>'> 
<input type='hidden' name='c_estado' id='c_estado' value='<?php echo $estado?>'> 
</form>

<script>

	//////Checar se tem palavra para ativar o submit ///
	////////////////////////////////////////////////////
$("input:checkbox").click( function() {

	var qtdPalavras = document.getElementById('word').value;

	if ($("input:checked").length > 1 && qtdPalavras != ''  ) {
		$("#carregar").removeAttr('disabled');
		
		} else {
	 $("#carregar").attr('disabled','disabled');
		
		}
	
	
});


</script>