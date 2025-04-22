<?php
require_once '../../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";
$estado = str_replace("'","\'",$_GET['estado']);
$ps_query = "SELECT distinct ug_cidade FROM dist_usuarios_games where ug_estado = '$estado';";	//  and ug_ativo = '1'
//echo $ps_query;
/// todas as lan que estiverem nesse bairro
//pg_send_query($conex,$ps_query);
//$res1 = pg_get_result($conex);
$res1 = SQLexecuteQuery($ps_query);


//Variavel V anexa um id a div para proteger o conteúdo
$v = 0;

  while ($info = pg_fetch_array($res1)) {
	?>
	<div id='<?php echo $v?>' style="cursor:pointer;cursor:hand"><?php echo $info[0]?></div>
	
<?php
	$v++;
} 



//sessão de controle //
$ps_query = "SELECT distinct ug_cidade FROM dist_usuarios_games where ug_estado = '$estado';";	//  and ug_ativo = '1'

//pg_send_query($conex,$ps_query);
//$res1 = pg_get_result($conex);
$res1 = SQLexecuteQuery($ps_query);

$v = 0;
while ($info = pg_fetch_array($res1)) { ?>
<script>
$('#<?php echo $v?>').click( function() {

	var ValorSelecionadoCidade = $('#<?php echo $v?>').text();
	
	$("#bairro").load("selectbairros.php","cidade="+ValorSelecionadoCidade+"&estado="+"<?php echo $estado; ?>");
	$("#variacao").load("selectbairros.php","");
	$("#corretor").load("corretor.php","");

});
///////////////Colori Células ///////////////////////////////////////////////////

  $("#<?php echo $v?>").hover( function () {
    $(this).addClass("teste");
  },
  function () {
    $(this).removeClass("teste");
  }
);
////////////////////////////////////////////////////////////////////////////////
		
</script>
<?php 
	
$v++; 
} ?>