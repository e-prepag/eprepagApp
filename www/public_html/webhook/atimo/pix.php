<?php

	require "/www/banco/pix/cielo/config.inc.pix.php";
	  
	$dados = file_get_contents('php://input');
    $infomacoesRecebidas = json_decode($dados, true);
 	
	if($infomacoesRecebidas["metodo"] == "criar"){
		$pix = new Pix(
			$infomacoesRecebidas["info"]["tipo"], 
			$infomacoesRecebidas["info"]["chave"], 
			$infomacoesRecebidas["info"]["nome"],
			$infomacoesRecebidas["info"]["pedido"],
			$infomacoesRecebidas["info"]["valor"]
		);
	    $result = $pix->send("web");
		$return = [
		    "dados" => $result["dados"],
			"qrcode" => json_decode($result["response"], true)
		];
		echo json_encode($return); 
		exit;
	}else if($infomacoesRecebidas["metodo"] == "consultar"){
		$result = Pix::verify($infomacoesRecebidas["info"]["id"]);
		$return = [
			"pagamento" => json_decode($result["response"], true)
		];
		echo json_encode($return); 
		exit;
	}
	
    echo json_encode(["error" => "Não encontrado metodo"]);	
?>