<?php

	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);

	require "/www/banco/pix/cielo/config.inc.pix.php";
	
	$pixClass = new Pix("", "", "", "1020231106172559214", "");
	$retorno = $pixClass->status("04191d3c-3cd6-4181-9add-5994b4fe7dae");
	var_dump($retorno);

    /*
    $transacoes = Pix::buscaTransacoes();
	$fileLog = fopen("/www/log/pix-log-cielo.txt", "a+");
	if(count($transacoes) > 0){
		 foreach($transacoes as $key => $pix){
			 Pix::travaTransacoes($pix["numcompra"]);
			 $pedido = $pix["type"].$pix["numcompra"];
			 $pedido_cielo = json_decode($pix["json_resposta"], true);
			 if(isset($pedido_cielo["Payment"]["PaymentId"])){
				 $pixClass = new Pix("", "", "", $pedido, "");
				 $retorno = $pixClass->status($pedido_cielo["Payment"]["PaymentId"]);
				 fwrite($fileLog, "Data: ".date("d-m-Y H:i:s")."\n");
				 fwrite($fileLog, "Conteudo: ".$retorno."\n");
				 fwrite($fileLog, str_repeat("*", 50)."\n");
			}
			
			if(isset($retorno)){
				$code = json_decode($retorno, true);
				if(isset($code["code"]) && ($code["code"] == "PG002" || $code["code"] == "PG003")){
					Pix::destravaTransacoes($pix["numcompra"]);
				}
			}

		 }
	}
	fclose($fileLog);
	*/
	   
?>