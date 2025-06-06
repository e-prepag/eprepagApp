<?php

	$dataAtual = date('Y-m-d H:i:s');
		$informacoesReq = json_encode($_POST, JSON_UNESCAPED_UNICODE);
		$ipReq = $_SERVER['SERVER_ADDR'];
		$infoAdicional = json_encode($_SERVER, JSON_UNESCAPED_UNICODE);


	$mensagemLog = '****#### INÍCIO ####****' . PHP_EOL . 
					   'Data e Hora: ' . $dataAtual . PHP_EOL . 
					   'Informações do POST: ' . $informacoesReq . PHP_EOL . 
					   'IP de Acesso: ' . $ipReq . PHP_EOL . 
					   'Informações Adicionais do Servidor: ' . $infoAdicional . PHP_EOL . 
					   '****#### FIM ####****' . PHP_EOL . PHP_EOL;

		$fileLog = "../../../www/log/logCheckRedeemALL.txt";
		$file = fopen($fileLog, 'a+');
		if ($file) {
			fwrite($file, $mensagemLog);
			fclose($file);
			echo "Foi";
		} else {
			echo "Erro ao abrir o arquivo de log.";
		}