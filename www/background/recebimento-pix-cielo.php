<?php
//esta inativo no momento 
	// require "/www/banco/pix/cielo/config.inc.pix.php";

    // $transacoes = Pix::buscaTransacoes();
	// $fileLog = fopen("/www/log/pix-log-cielo.txt", "a+");
	// if(count($transacoes) > 0){
		 // foreach($transacoes as $key => $pix){
			 // Pix::travaTransacoes($pix["numcompra"]);
			 // $pedido = $pix["type"].$pix["numcompra"];
			 // $pedido_cielo = json_decode($pix["json_resposta"], true);
			 
			 // if(isset($pedido_cielo["Payment"]["PaymentId"])){
				 // $pixClass = new Pix("", "", "", $pedido, "");
				 // $retorno = $pixClass->status($pedido_cielo["Payment"]["PaymentId"]);
				 // fwrite($fileLog, "Data: ".date("d-m-Y H:i:s")."\n");
				 // fwrite($fileLog, "Conteudo: ".$retorno."\n");
				 // fwrite($fileLog, str_repeat("*", 50)."\n");
			 // }
			 
			 // if(isset($retorno)){
				// $code = json_decode($retorno, true);
				// if(isset($code["code"]) && ($code["code"] == "PG002" || $code["code"] == "PG003")){
					// Pix::destravaTransacoes($pix["numcompra"]);
				// }
			 // }

		 // }
	// }
	// fclose($fileLog);
	   
?>