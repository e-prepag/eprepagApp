<?php
require_once '../../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";
$estado =  $_GET['estado'];
$cidade =  $_GET['cidade'];
$ps_query = "SELECT distinct ug_bairro, ug_cidade, ug_estado, count(*) as n  FROM dist_usuarios_games where ug_cidade = '$cidade' and ug_estado ='$estado' group by ug_bairro, ug_cidade, ug_estado;";	//  and ug_ativo = '1' 
//echo $ps_query."<br>";
/// todas as lan que estiverem nesse bairro
//pg_send_query($conex,$ps_query);
//$res1 = pg_get_result($conex);
$res1 = SQLexecuteQuery($ps_query);

$v = 0; 
  while ($info = pg_fetch_array($res1)) {
	?>
	<nobr><div id='<?php echo "b".$v?>' style="cursor:pointer;cursor:hand"><font color='blue'><?php echo $info['ug_bairro']."</font> ('".$info['ug_cidade']."' - '".$info['ug_estado']."') n: ".$info['n'].""?></div></nobr>
	
<?php
	$v++;
} 

//sessão de controle //
$ps_query = "SELECT distinct ug_bairro,ug_cidade, ug_estado FROM dist_usuarios_games where ug_cidade = '$cidade' and ug_estado ='$estado';";	
// and ug_ativo='1'

//pg_send_query($conex,$ps_query);
//$res1 = pg_get_result($conex);
$res1 = SQLexecuteQuery($ps_query);
//echo $ps_query."<br>";

$v = 0;

while ($info = pg_fetch_array($res1)) { ?>

<script>
	///////////////Colori Células ///////////////////////////////////////////////////

  $("#b<?php echo $v?>").hover( function () {
    $(this).addClass("teste");
  },
  function () {
    $(this).removeClass("teste");
  }
);
////////////////////////////////////////////////////////////////////////////////
///////////////////////////CARREGA AS SIMILIARIDADES DAS PALAVRAS //////////////		 
$("#<?php echo 'b'.$v?>").click( function() {
	
	var ValorSelecionadoEstado = '<?php echo urlencode($info['ug_estado']);?>';
	var ValorSelecionadoCidade = '<?php echo urlencode($info['ug_cidade']);?>';
	var ValorSelecionadoBairro = '<?php echo urlencode($info['ug_bairro']);?>';

//alert("bairro="+ValorSelecionadoBairro+"&cidade="+ValorSelecionadoCidade+"&estado="+ValorSelecionadoEstado);

	$("#variacao").load("variacao.php","bairro="+ValorSelecionadoBairro+"&cidade="+ValorSelecionadoCidade+"&estado="+ValorSelecionadoEstado);
	$("#corretor").load("corretor.php","bairro="+ValorSelecionadoBairro+"&cidade="+ValorSelecionadoCidade+"&estado="+ValorSelecionadoEstado);
		 });
///////////////////////////////////////////////////////////////////////////////////
</script>
<?php 
	
$v++; } ?>