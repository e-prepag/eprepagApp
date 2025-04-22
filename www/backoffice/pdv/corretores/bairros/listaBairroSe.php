<?php
require_once '../../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";


// seleciona o total de bairros diferentes cadastrados atualmente.
$ps_query = "SELECT distinct ug_bairro FROM dist_usuarios_games ";
//pg_send_query($conex,$ps_query);
//$res0 = pg_get_result($conex);
$res0 = SQLexecuteQuery($ps_query);
$total = pg_num_rows($res0);

// seleciona todos os bairros que tenha palavras sem acentos e se pareçam o bairro subselecionado  
$ps_query = "SELECT distinct ug_bairro, ug_cidade, ug_estado FROM dist_usuarios_games where sem_acentos(ug_bairro) ilike in (sem_acentos( SELECT distinct ug_bairro from dist_usuarios_games  )order by ug_bairro";


//pg_send_query($conex,$ps_query);
//$res1 = pg_get_result($conex);
$res1 = SQLexecuteQuery($ps_query);


//recebe o total de bairros que estão ruins ou seja semelhante e desacentuados
$bad = pg_num_rows($res1);
echo "Total: $bad - $total <br>";

// regata o valor e faz a lista
while ($info = pg_fetch_array($res1)) {
	$ug_bairro = $info['ug_bairro'];
	$ug_cidade = $info['ug_cidade'];
	$ug_estado = $info['ug_estado'];
	
     echo "Bairro :".$ug_bairro." - Cidade :".$ug_cidade."(".$ug_estado.")<br>";
  
}

