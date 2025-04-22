<?php
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);

	$curl = curl_init(); 
	curl_setopt_array($curl, array(
	  CURLOPT_URL => "http://10.204.132.17/s5/oauth/seguranca/connect/token",
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => "",
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 10,
	  CURLOPT_FOLLOWLOCATION => true,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => "POST",
	  CURLOPT_POSTFIELDS => "client_id=eprepag_pix&client_secret=456789HJTj3fkhtjdkhn9fgu8d89g89df78ag888&grant_type=client_credentials&scope=cobranca", 
	  CURLOPT_HTTPHEADER => array(
		"Content-Type: application/x-www-form-urlencoded"
	  )
	));

	$resultado = json_decode(curl_exec($curl), true);
	$info = curl_getinfo($curl);
	curl_close($curl);

	/*
		$params = array (
		'cpf_cnpj'  => "03097175000157",
		'nome'      => "testes",
		'valor'     => 10,
		'descricao' => "E-PREPAG",
		'idpedido'  => "2022738231",
	    ); 
	*/
	
	$params = array(
		"cpf_cnpj" => "19037276000172",
		"nome" => "Teste epp",
		"descricao"=> "E-PREPAG",
		"recebedor" => "E-PREPAG",
		"chave" => "87e2284a-a07e-4b2e-bf76-7e6dc50a94e8",
		"idpedido" => "777456789012345678h",
		"valor" => "0.01",
		"estatico" => 1
	);
			 
	$curlw = curl_init();
	
	 curl_setopt_array($curlw, array(
	  CURLOPT_URL => "http://10.204.132.17/s2/oauth/jwtqr.php",  // www.contause.digital.com.br
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => "",
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 10,
	  CURLOPT_FOLLOWLOCATION => true,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => "POST",
	  CURLOPT_HTTPHEADER => array(
		"Content-Type: application/json",
		"Authorization: Bearer ".$resultado["access_token"]
	  ),
	));
	
	$dados = json_encode($params);
	curl_setopt($curlw, CURLOPT_POSTFIELDS,$dados);

	$resultado = curl_exec($curlw);
	
	// Em caso de erro libera aqui
	$info = curl_getinfo($curlw);
	   
	//Setando Resposta
	if($info['http_code'] != 200 && $info['http_code'] != 201 ) {
	
		$resultado = '{"codigo":"ERRO NA COMUNICACAO"}';
	}

	curl_close($curlw);
	
	var_dump($info);
	var_dump($resultado);

?> 