<?php

require_once '/www/includes/constantes.php';
//require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/gamer/main.php";
require_once $raiz_do_projeto."includes/gamer/functions_vendaGames.php"; 
require_once $raiz_do_projeto."includes/inc_Pagamentos.php";

function retorna_status($pin) {
	//instanciando a classe de cryptografia
	$chave256bits = new Chave();
	$aes = new AES($chave256bits->retornaChave());
	$sql = "select pin_status from pins_store where pin_codigo = '".base64_encode($aes->encrypt(addslashes($pin)))."'";
	echo $sql; 
	$rs_log = SQLexecuteQuery($sql);

	$rs_log_row = pg_fetch_array($rs_log);
	var_dump($rs_log_row);	
	
}

retorna_status("1253443484745022");


?>