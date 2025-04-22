<?php

	require_once '/www/banco/pix/blupayA/config.inc.pix.php'; 
	//require_once '/www/includes/gamer/main.php';
	//require_once '/www/includes/pdv/main.php';
	require_once '/www/includes/main.php';
	// require_once '/www/banco/pix/casadocreditoA/classPIX.php'; 
 	//require_once '/www/banco/pix/casadocreditoA/classJSONEstruturaPIX.php'; 
    $pix = new classPIX();
	
	//echo 'pix';
    //Bloco que vai para página de checkout
	$params = array (
		'metodo'    => "POST",
		'cpf_cnpj'  => str_replace('-', '', str_replace('.', '', "067.436.111-31")),
		'nome'      => "Andre silva do nascimento",
		'valor'     => "1.0",
		'descricao' => "E-PREPAG ADMINISTRADORA DE CARTOES LTDA",
		'idpedido'  => '10'.'0000000'.'3GG60ZZ706VU7115K87ZZ'
	); 
	
	// idpedido = 1000000003161415779
	
	//'chave' => '9abbfd42-dd8d-4a24-bad4-f0996e7743b6',
	//'idAccount'  => "9b4d7289-7172-e34b-813d-dea5889c7323",
	//'idAccountUser'  => "9b4d7289-7172-e34b-813d-dea5889c7323",
	
	var_dump($pix->callService($params));
    //echo $pix->callService($params); 
	//$retorno = "";
	//$retorno2 = getSondaPIX("100000000345657890654367864678", $retorno);
	//var_dump($retorno2);

?>