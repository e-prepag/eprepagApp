<?php
require_once '../../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";

// seleciona o total de bairros diferentes cadastrados atualmente.
$ps_query = "SELECT distinct ug_cidade FROM dist_usuarios_games ";
//pg_send_query($conex,$ps_query);
//$res0 = pg_get_result($conex);
$res0 = SQLexecuteQuery($ps_query);
$total = pg_num_rows($res0);

// seleciona todos os bairros que tenha palavras sem acentos e se pareçam o bairro subselecionado  
//$ps_query = "SELECT distinct ug_cidade, ug_estado FROM dist_usuarios_games where sem_acentos(ug_cidade) ilike in (sem_acentos( SELECT distinct ug_cidade from dist_usuarios_games)) order by ug_cidade";
$ps_query = "SELECT distinct ug_cidade, ug_estado FROM dist_usuarios_games where sem_acentos(ug_cidade)	IN (SELECT distinct ug_cidade from dist_usuarios_games) order by ug_cidade";

//echo $ps_query."<br>";
//pg_send_query($conex,$ps_query);
//$res1 = pg_get_result($conex);
$res1 = SQLexecuteQuery($ps_query);

//recebe o total de bairros que estão ruins ou seja semelhante e desacentuados
$bad = pg_num_rows($res1);
echo "Total: $bad - $total <br>";

// regata o valor e faz a lista
while ($info = pg_fetch_array($res1)) {

/*	
	$ug_cidade = $info['ug_cidade'];
	$ug_estado = $info['ug_estado'];
	
     echo " Cidade :".$ug_cidade."(".$ug_estado.")<br>";
*/
	echo "<nobr>".$info['total']." - ".$info['ug_cidade']." (<font color='#336633'>".$info['ug_estado']."</font>)</nobr><br>";

  
}
echo "<a href='/index.php'>voltar</a>"
?>