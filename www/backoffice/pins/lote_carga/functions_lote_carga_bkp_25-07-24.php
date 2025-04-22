<?php
function buscaArquivos($folder, $ordem = 'nome', $direcao = 'asc', $filtro) {

	if($filtro != ''){
		if(strpos($filtro, ';') != strlen($filtro)) $filtro .= ';';
		$filtro = explode(';', $filtro);
	}
	if(is_dir($folder)){
		if ($handle = opendir($folder)) {
			//Carrega e Filtra os arquivos
			while(false !== ($file = readdir($handle))) {
			   if ($file != '.' && $file != '..') {
					if($filtro != ''){
						for($j = 0; $j < count($filtro) -1; $j++){
							if(strpos(strtolower($file), strtolower($filtro[$j])) !== false){
								if($ordem == 'nome') $arquivoAr[strtolower($file)] = $file;
								if($ordem == 'data') $arquivoAr[date("YmdHis", filemtime($folder.$file))] = $file;
							}
						}
					} else {
						if($ordem == 'nome') $arquivoAr[strtolower($file)] = $file;
						if($ordem == 'data') $arquivoAr[date("YmdHis", filemtime($folder.$file))] = $file;
					}					
				}
			}
			closedir($handle);

			//Ordena os arquivos
			if (count($arquivoAr) != 0) {
				if($direcao == 'asc') ksort($arquivoAr);
				if($direcao == 'desc') krsort($arquivoAr);
			}
			
			return $arquivoAr;
		}
	}
}

function gravaLog($file, $mensagem){

	$msg = "";
	
	if (!file_exists($file)){
		if(!fopen($file, 'w')){
			$msg = "Não foi possível criar arquivo de log.";
			return $msg;
		}
	}

	if (file_exists($file) && (!is_writable($file))) {
		$msg = "Não foi possível gravar log #1.";
		return $msg;

	} else {
		if (!$handle = fopen($file, 'r+')) {
			$msg = "Não foi possível gravar log #2.";
			return $msg;
		} 
		
		//Le conteudo atual do log
		if((file_exists($file)) && (filesize($file)) > 0) {
			$mensagem .= fread($handle, filesize($file));
		}
		
		//grava o log no arquivo
		rewind($handle);
		if (fwrite($handle, $mensagem) === FALSE) {
			$msg = "Não foi possível gravar log #3.";
			return $msg;
		}
	
		fclose($handle);
		return "";
	}
}

function gravaLogFormat($nomeArqUploaded, $mensagem){

	global $logDelimitador, $folder, $logFile;
	if(!isset($_SESSION["loginuser_bko"])) $session_user = "";
        else $session_user = $_SESSION["loginuser_bko"];
	$mensagem = date('Y-m-d H:i:s') . " - " . $session_user . " - " . $nomeArqUploaded . ": " . $mensagem . "\n";
	$mensagem .= $logDelimitador . "\n";
	
	return gravaLog($folder . $logFile, $mensagem);
	
}

function leLog($leLogCompleto){

	global $logDelimitador, $folder, $logFile;
	$buffer = '';
	$file = $folder . $logFile;
	
	if (file_exists($file)) {
		if ($handle = fopen($file, 'r')) {
		   	while (!feof($handle)) {
				$buffer_aux = fgets($handle);
				if($leLogCompleto){
					$buffer .= $buffer_aux;
				} else {
					if(trim($buffer_aux) == trim($logDelimitador)){
						break;
					} else {
						$buffer .= $buffer_aux;
					}
				}
			}
		}
		fclose($handle);
	}

	return $buffer;
}


function Ongame_traduzKValor($k){
	$valor["4"] = "10";
	$valor["5"] = "13";
	$valor["10"] = "25";
	$valor["15"] = "37";
	$valor["20"] = "49";
	$valor["22"] = "49";
	$valor["40"] = "97";
	$valor["60"] = "145";
	
	return $valor[$k];
}

	function processaLote_Ongame($fileSource, $nomeArq, $opr_codigo, $fcanal){
	
		global $folder, $opr_codigo_Ongame;
		
		$msg = "";
		
		//Valida entradas
		$operadoras = array($opr_codigo_Ongame);
		if(is_null($fileSource) || trim($fileSource) == "") $msg = "Caminho do arquivo vázio.\n";
		if(is_null($nomeArq) || trim($nomeArq) == "") $msg = "Nome do arquivo vázio.\n";
		if(is_null($opr_codigo) || trim($opr_codigo) == "" || !is_numeric($opr_codigo) || !in_array($opr_codigo, $operadoras)) $msg = "Código da operadora inválido.\n";

		//local
		$iPinLocal = 19;


		//Abre arquivo e le conteudo
		if($msg == ""){
			$handle = fopen($fileSource, "r");
			$carga = fread($handle, filesize($fileSource));
			fclose($handle);

			$cargaAr = explode("\n", $carga);
			if(count($cargaAr) == 0) $msg = "Arquivo vázio.\n";
		} 

// Debug
//for($i=0; $i < count($cargaAr); $i++){
//	echo "cargaAr[$i]: '".$cargaAr[$i]."'<br>";
//}
//$msg = "";

		//valida tamanho da linha
		if($msg == ""){
			for($i=0; $i < count($cargaAr); $i++){
				$cargaAr[$i] = trim($cargaAr[$i]);
								
				// tira linha em branco
				if(trim($cargaAr[$i]) == ""){
					array_splice($cargaAr, $i, 1); 
					continue;
				}
/*
 				//Valida tamanho da linha
				$num_posicoes = 34;
				if(strlen($cargaAr[$i]) != $num_posicoes){
					$msg = "linha " . ($i+1) . " do arquivo não tem $num_posicoes posições, possui " . strlen($cargaAr[$i]) . ":\n";
					$msg .= "**" . $cargaAr[$i] . "**\n";
					break;
				}
*/
			}
		}
		
		
		//Inicia transacao
		if($msg == ""){
			$sql = "BEGIN TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao iniciar transação.\n";
		}

		//insere lote
		if($msg == ""){
			
			$seq = 0;
			$valor = 0;
			for($i=0; $i < count($cargaAr); $i++){
	
				$linha 		= $cargaAr[$i];

				//dados				
				if (strpos($linha, "unidades") === false) { // corpo

					$linhaAr 	= explode("\t", $linha);
					$sLote 		= $opr_codigo . date('dmy') . "0" . $seq;
					$sPinSerial = $linhaAr[0];
				    $sPinCodigo	= $linhaAr[1];
			    	$sValorFace = $valor;

					//Validacoes - "614791 MGspdKFVS5atw9ER 20000"
					if($sLote == "" || !is_numeric($sLote)) 			$msg = "Lote inválido: " . $sLote . ".\n";
					if($sPinSerial == "" || !is_numeric($sPinSerial)) 	$msg = "Pin Serial inválido: " . $sPinSerial . ".\n";
//					if($sPinCodigo == "" || !is_numeric($sPinCodigo)) 	$msg = "Pin Código inválido: " . $sPinCodigo . ".\n"; // Código antigo: só números
					if($sPinCodigo == "") 	$msg = "Pin Código inválido: " . $sPinCodigo . ".\n";
					if($sValorFace == "" || !is_numeric($sValorFace)) 	$msg = "Valor de face inválido: " . $sValorFace . ".\n";

					//insere pin
					if($msg == ""){
						$sql = "insert into pins (opr_codigo, pin_status, pin_serial, pin_codigo, pin_local, pin_valor, pin_lote_codigo, pin_dataentrada, pin_horaentrada, pin_canal) ";
						$sql .= "values ($opr_codigo, 1, '$sPinSerial', '$sPinCodigo', '$iPinLocal', '$sValorFace', '$sLote', CURRENT_DATE, CURRENT_TIME, '".$fcanal."')";

						$ret = SQLexecuteQuery($sql);
						if(!$ret) $msg = "Erro ao inserir registro: " . $linha . ".\n";
					}
			
				} else {
					$seq++;
					$valor = "";
					
					$sai = array("-", " ", "unidades");
					$entra = array("", "", "");
					$linhaAr = explode("k", str_replace($sai, $entra, strtolower($linha)));
					$lote  = $opr_codigo . date('dmy') . "0" . $seq;
					$valor = Ongame_traduzKValor($linhaAr[0]);
					$qtd = $linhaAr[1];

					//Validacoes
					if($lote == "" || !is_numeric($lote)) 	$msg = "Lote inválido: " . $lote . ".\n";
					if($valor == "" || !is_numeric($valor)) $msg = "Valor de face inválido: " . $valor . ".\n";
		
					if($msg == ""){		    
						$sql  = "select count(*) as qtde from pins where opr_codigo = $opr_codigo and pin_lote_codigo = $lote and pin_valor = $valor and pin_canal='s' ";
//echo "sql: $sql";
						$rs = SQLexecuteQuery($sql);
						$ret = true;
						if($rs && pg_num_rows($rs) > 0){
							$rs_row = pg_fetch_array($rs);
							if($rs_row['qtde'] == 0) $ret = false;
						}			
						if($ret) $msg = "Arquivo já foi importado (opr_codigo=$opr_codigo, pin_lote_codigo=$lote, pin_valor=$valor -> val:".$linhaAr[0].", qtd:".$linhaAr[1].")\n"."linha ($i): '$linha'\n";
					}

				} 

				//Se houve erro, sai do loop
				if($msg != "") break;
				
			}
		}


		//Cria diretorios
		if($msg == ""){
			if(!is_dir($folder)) $msg = "Diretório raiz não existe.\n";
			if($msg == ""){
				if(!is_dir($folder. $opr_codigo)) 
					if(!mkdir($folder. $opr_codigo)) $msg = "Não foi possivel criar diretorio da operadora.\n";
			}
		}				

		//move arquivo
		if($msg == ""){
			$fileDest = $folder . $opr_codigo . "/" . $nomeArq; 
			if(!copy($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.\n"; 
			elseif((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo vazio ou inválido.\n";
		}
		
		//Finaliza transacao
		if($msg == ""){
			$sql = "COMMIT TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao comitar transação.\n";
		} else {
			$sql = "ROLLBACK TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
		}

		return $msg;
		
	}


function BilaGames_traduzKValor($k){
	$valor["10"] = "10";
	$valor["20"] = "20";
	$valor["40"] = "40";
	$valor["50"] = "50";
	$valor["100"] = "100";
	$valor["200"] = "200";
	
	return $valor[$k];
}

	function processaLote_BilaGames($fileSource, $nomeArq, $opr_codigo, $fcanal){
	
		global $folder, $opr_codigo_BilaGames;
		
		$msg = "";
		
		//Valida entradas
		$operadoras = array($opr_codigo_BilaGames);
		if(is_null($fileSource) || trim($fileSource) == "") $msg = "Caminho do arquivo vázio.\n";
		if(is_null($nomeArq) || trim($nomeArq) == "") $msg = "Nome do arquivo vázio.\n";
		if(is_null($opr_codigo) || trim($opr_codigo) == "" || !is_numeric($opr_codigo) || !in_array($opr_codigo, $operadoras)) $msg = "Código da operadora inválido.\n";

		//local
		$iPinLocal = 19;


		//Abre arquivo e le conteudo
		if($msg == ""){
			$handle = fopen($fileSource, "r");
			$carga = fread($handle, filesize($fileSource));
			fclose($handle);

			$cargaAr = explode("\n", $carga);
			if(count($cargaAr) == 0) $msg = "Arquivo vázio.\n";
		} 

// Debug
//for($i=0; $i < count($cargaAr); $i++){
//	echo "cargaAr[$i]: '".$cargaAr[$i]."'<br>";
//}
//$msg = "";

		//valida tamanho da linha
		if($msg == ""){
			for($i=0; $i < count($cargaAr); $i++){
				$cargaAr[$i] = trim($cargaAr[$i]);
								
				// tira linha em branco
				if(trim($cargaAr[$i]) == ""){
					array_splice($cargaAr, $i, 1); 
					continue;
				}
/*
 				//Valida tamanho da linha
				$num_posicoes = 34;
				if(strlen($cargaAr[$i]) != $num_posicoes){
					$msg = "linha " . ($i+1) . " do arquivo não tem $num_posicoes posições, possui " . strlen($cargaAr[$i]) . ":\n";
					$msg .= "**" . $cargaAr[$i] . "**\n";
					break;
				}
*/
			}
		}
		
		
		//Inicia transacao
		if($msg == ""){
			$sql = "BEGIN TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao iniciar transação.\n";
		}

		//insere lote
		if($msg == ""){
			
			$seq = 0;
			$valor = 0;
			for($i=0; $i < count($cargaAr); $i++){
	
				$linha 		= $cargaAr[$i];

				//dados				
				if (strpos($linha, "unidades") === false) { // corpo

					$linhaAr 	= explode("\t", $linha);
					$sLote 		= $opr_codigo . date('dmy') . "0" . $seq;
					$sPinSerial = $linhaAr[0];
				    $sPinCodigo	= $linhaAr[1];
			    	$sValorFace = $valor;

					//Validacoes - "614791 MGspdKFVS5atw9ER 20000"
					if($sLote == "" || !is_numeric($sLote)) 			$msg = "Lote inválido: " . $sLote . ".\n";
//					if($sPinSerial == "" || !is_numeric($sPinSerial)) 	$msg = "Pin Serial inválido: " . $sPinSerial . ".\n";
					if($sPinSerial == "") 	$msg = "Pin Serial inválido: " . $sPinSerial . ".\n";
//					if($sPinCodigo == "" || !is_numeric($sPinCodigo)) 	$msg = "Pin Código inválido: " . $sPinCodigo . ".\n"; // Código antigo: só números
					if($sPinCodigo == "") 	$msg = "Pin Código inválido: " . $sPinCodigo . ".\n";
					if($sValorFace == "" || !is_numeric($sValorFace)) 	$msg = "Valor de face inválido: " . $sValorFace . ".\n";

					//insere pin
					if($msg == ""){
						$sql = "insert into pins (opr_codigo, pin_status, pin_serial, pin_codigo, pin_local, pin_valor, pin_lote_codigo, pin_dataentrada, pin_horaentrada, pin_canal) ";
						$sql .= "values ($opr_codigo, 1, '$sPinSerial', '$sPinCodigo', '$iPinLocal', '$sValorFace', '$sLote', CURRENT_DATE, CURRENT_TIME, '".$fcanal."')";

						$ret = SQLexecuteQuery($sql);
						if(!$ret) $msg = "Erro ao inserir registro: " . $linha . ".\n";
					}
			
				} else {
					$seq++;
					$valor = "";
					
					$sai = array("-", " ", "unidades");
					$entra = array("", "", "");
					$linhaAr = explode("*", str_replace($sai, $entra, strtolower($linha)));
					$lote  = $opr_codigo . date('dmy') . "0" . $seq;
					$valor = BilaGames_traduzKValor($linhaAr[0]);
					$qtd = $linhaAr[1];

					//Validacoes
					if($lote == "" || !is_numeric($lote)) 	$msg = "Lote inválido: " . $lote . ".\n";
					if($valor == "" || !is_numeric($valor)) $msg = "Valor de face inválido: " . $valor . ".\n";
		
					if($msg == ""){		    
						$sql  = "select count(*) as qtde from pins where opr_codigo = $opr_codigo and pin_lote_codigo = $lote and pin_valor = $valor and pin_canal='s' ";
//echo "sql: $sql";
						$rs = SQLexecuteQuery($sql);
						$ret = true;
						if($rs && pg_num_rows($rs) > 0){
							$rs_row = pg_fetch_array($rs);
							if($rs_row['qtde'] == 0) $ret = false;
						}			
						if($ret) $msg = "Arquivo já foi importado (opr_codigo=$opr_codigo, pin_lote_codigo=$lote, pin_valor=$valor -> val:".$linhaAr[0].", qtd:".$linhaAr[1].")\n"."linha ($i): '$linha'\n";
					}

				} 

				//Se houve erro, sai do loop
				if($msg != "") break;
				
			}
		}


		//Cria diretorios
		if($msg == ""){
			if(!is_dir($folder)) $msg = "Diretório raiz não existe.\n";
			if($msg == ""){
				if(!is_dir($folder. $opr_codigo)) 
					if(!mkdir($folder. $opr_codigo)) $msg = "Não foi possivel criar diretorio da operadora.\n";
			}
		}				

		//move arquivo
		if($msg == ""){
			$fileDest = $folder . $opr_codigo . "/" . $nomeArq; 
			if(!copy($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.\n"; 
			elseif((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo vazio ou inválido.\n";
		}
		
		//Finaliza transacao
		if($msg == ""){
			$sql = "COMMIT TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao comitar transação.\n";
		} else {
			$sql = "ROLLBACK TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
		}

		return $msg;
		
	}

function AeriaGames_traduzKValor($k){
	$valor["10"] = "10";
	$valor["20"] = "20";
	$valor["40"] = "40";
	$valor["50"] = "50";
	$valor["100"] = "100";
	$valor["200"] = "200";
	
	return $valor[$k];
}

	function processaLote_AeriaGames($fileSource, $nomeArq, $opr_codigo, $fcanal){
	
		global $folder, $opr_codigo_AeriaGames;
		
		$msg = "";
		
		//Valida entradas
		$operadoras = array($opr_codigo_AeriaGames);
		if(is_null($fileSource) || trim($fileSource) == "") $msg = "Caminho do arquivo vázio.\n";
		if(is_null($nomeArq) || trim($nomeArq) == "") $msg = "Nome do arquivo vázio.\n";
		if(is_null($opr_codigo) || trim($opr_codigo) == "" || !is_numeric($opr_codigo) || !in_array($opr_codigo, $operadoras)) $msg = "Código da operadora inválido.\n";

		//local
		$iPinLocal = 19;


		//Abre arquivo e le conteudo
		if($msg == ""){
			$handle = fopen($fileSource, "r");
			$carga = fread($handle, filesize($fileSource));
			fclose($handle);

			$cargaAr = explode("\n", $carga);
			if(count($cargaAr) == 0) $msg = "Arquivo vázio.\n";
		} 

// Debug
//for($i=0; $i < count($cargaAr); $i++){
//	echo "cargaAr[$i]: '".$cargaAr[$i]."'<br>";
//}
//$msg = "";

		//valida tamanho da linha
		if($msg == ""){
			for($i=0; $i < count($cargaAr); $i++){
				$cargaAr[$i] = trim($cargaAr[$i]);
								
				// tira linha em branco
				if(trim($cargaAr[$i]) == ""){
					array_splice($cargaAr, $i, 1); 
					continue;
				}
/*
 				//Valida tamanho da linha
				$num_posicoes = 34;
				if(strlen($cargaAr[$i]) != $num_posicoes){
					$msg = "linha " . ($i+1) . " do arquivo não tem $num_posicoes posições, possui " . strlen($cargaAr[$i]) . ":\n";
					$msg .= "**" . $cargaAr[$i] . "**\n";
					break;
				}
*/
			}
		}
		
		
		//Inicia transacao
		if($msg == ""){
			$sql = "BEGIN TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao iniciar transação.\n";
		}

		//insere lote
		if($msg == ""){
			
			$seq = 0;
			$valor = 0;
			for($i=0; $i < count($cargaAr); $i++){
	
				$linha 		= $cargaAr[$i];

				//dados				
				if (strpos($linha, "unidades") === false) { // corpo

					$linhaAr 	= explode("\t", $linha);
					$sLote 		= $opr_codigo . date('dmy') . "0" . $seq;
					$sPinSerial = $linhaAr[0];
				    $sPinCodigo	= $linhaAr[1];
			    	$sValorFace = $valor;

					//Validacoes - "614791 MGspdKFVS5atw9ER 20000"
					if($sLote == "" || !is_numeric($sLote)) 			$msg = "Lote inválido: " . $sLote . ".\n";
//					if($sPinSerial == "" || !is_numeric($sPinSerial)) 	$msg = "Pin Serial inválido: " . $sPinSerial . ".\n";
					if($sPinSerial == "") 	$msg = "Pin Serial inválido: " . $sPinSerial . ".\n";
//					if($sPinCodigo == "" || !is_numeric($sPinCodigo)) 	$msg = "Pin Código inválido: " . $sPinCodigo . ".\n"; // Código antigo: só números
					if($sPinCodigo == "") 	$msg = "Pin Código inválido: " . $sPinCodigo . ".\n";
					if($sValorFace == "" || !is_numeric($sValorFace)) 	$msg = "Valor de face inválido: " . $sValorFace . ".\n";

					//insere pin
					if($msg == ""){
						$sql = "insert into pins (opr_codigo, pin_status, pin_serial, pin_codigo, pin_local, pin_valor, pin_lote_codigo, pin_dataentrada, pin_horaentrada, pin_canal) ";
						$sql .= "values ($opr_codigo, 1, '$sPinSerial', '$sPinCodigo', '$iPinLocal', '$sValorFace', '$sLote', CURRENT_DATE, CURRENT_TIME, '".$fcanal."')";

						$ret = SQLexecuteQuery($sql);
						if(!$ret) $msg = "Erro ao inserir registro: " . $linha . ".\n";
					}
			
				} else {
					$seq++;
					$valor = "";
					
					$sai = array("-", " ", "unidades");
					$entra = array("", "", "");
					$linhaAr = explode("*", str_replace($sai, $entra, strtolower($linha)));
					$lote  = $opr_codigo . date('dmy') . "0" . $seq;
					$valor = AeriaGames_traduzKValor($linhaAr[0]);
					$qtd = $linhaAr[1];

					//Validacoes
					if($lote == "" || !is_numeric($lote)) 	$msg = "Lote inválido: " . $lote . ".\n";
					if($valor == "" || !is_numeric($valor)) $msg = "Valor de face inválido: " . $valor . ".\n";
		
					if($msg == ""){		    
						$sql  = "select count(*) as qtde from pins where opr_codigo = $opr_codigo and pin_lote_codigo = $lote and pin_valor = $valor and pin_canal='s' ";
//echo "sql: $sql";
						$rs = SQLexecuteQuery($sql);
						$ret = true;
						if($rs && pg_num_rows($rs) > 0){
							$rs_row = pg_fetch_array($rs);
							if($rs_row['qtde'] == 0) $ret = false;
						}			
						if($ret) $msg = "Arquivo já foi importado (opr_codigo=$opr_codigo, pin_lote_codigo=$lote, pin_valor=$valor -> val:".$linhaAr[0].", qtd:".$linhaAr[1].")\n"."linha ($i): '$linha'\n";
					}

				} 

				//Se houve erro, sai do loop
				if($msg != "") break;
				
			}
		}


		//Cria diretorios
		if($msg == ""){
			if(!is_dir($folder)) $msg = "Diretório raiz não existe.\n";
			if($msg == ""){
				if(!is_dir($folder. $opr_codigo)) 
					if(!mkdir($folder. $opr_codigo)) $msg = "Não foi possivel criar diretorio da operadora.\n";
			}
		}				

		//move arquivo
		if($msg == ""){
			$fileDest = $folder . $opr_codigo . "/" . $nomeArq; 
			if(!copy($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.\n"; 
			elseif((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo vazio ou inválido.\n";
		}
		
		//Finaliza transacao
		if($msg == ""){
			$sql = "COMMIT TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao comitar transação.\n";
		} else {
			$sql = "ROLLBACK TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
		}

		return $msg;
		
	}


function OGPlanet_traduzKValor($k){
	$valor["5"] = "5";
	$valor["10"] = "10";
	$valor["20"] = "20";
	$valor["30"] = "30";
	$valor["50"] = "50";
	
	return $valor[$k];
}

	function processaLote_OGPlanet($fileSource, $nomeArq, $opr_codigo, $fcanal){
	
		global $folder, $opr_codigo_OGPlanet;
		
		$msg = "";
		
		//Valida entradas
		$operadoras = array($opr_codigo_OGPlanet);
		if(is_null($fileSource) || trim($fileSource) == "") $msg = "Caminho do arquivo vázio.\n";
		if(is_null($nomeArq) || trim($nomeArq) == "") $msg = "Nome do arquivo vázio.\n";
		if(is_null($opr_codigo) || trim($opr_codigo) == "" || !is_numeric($opr_codigo) || !in_array($opr_codigo, $operadoras)) $msg = "Código da operadora inválido.\n";

		//local
		$iPinLocal = 19;


		//Abre arquivo e le conteudo
		if($msg == ""){
			$handle = fopen($fileSource, "r");
			$carga = fread($handle, filesize($fileSource));
			fclose($handle);

			$cargaAr = explode("\n", $carga);
			if(count($cargaAr) == 0) $msg = "Arquivo vázio.\n";
		} 

// Debug
//for($i=0; $i < count($cargaAr); $i++){
//	echo "cargaAr[$i]: '".$cargaAr[$i]."'<br>";
//}
//$msg = "";

		//valida tamanho da linha
		if($msg == ""){
			for($i=0; $i < count($cargaAr); $i++){
				$cargaAr[$i] = trim($cargaAr[$i]);
								
				// tira linha em branco
				if(trim($cargaAr[$i]) == ""){
					array_splice($cargaAr, $i, 1); 
					continue;
				}
/*
 				//Valida tamanho da linha
				$num_posicoes = 34;
				if(strlen($cargaAr[$i]) != $num_posicoes){
					$msg = "linha " . ($i+1) . " do arquivo não tem $num_posicoes posições, possui " . strlen($cargaAr[$i]) . ":\n";
					$msg .= "**" . $cargaAr[$i] . "**\n";
					break;
				}
*/
			}
		}
		
		
		//Inicia transacao
		if($msg == ""){
			$sql = "BEGIN TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao iniciar transação.\n";
		}

		//insere lote
		if($msg == ""){
			
			$seq = 0;
			$valor = 0;
			for($i=0; $i < count($cargaAr); $i++){
	
				$linha 		= $cargaAr[$i];

				//dados				
				if (strpos($linha, "unidades") === false) { // corpo

					$linhaAr 	= explode("\t", $linha);
					$sLote 		= $opr_codigo . date('dmy') . "0" . $seq;
					$sPinSerial = $linhaAr[0];
				    $sPinCodigo	= $linhaAr[1];
			    	$sValorFace = $linhaAr[2];

					//Validacoes - "614791 MGspdKFVS5atw9ER 20000"
					if($sLote == "" || !is_numeric($sLote)) 			$msg = "Lote inválido: " . $sLote . ".\n";
					if($sPinSerial == "") 	$msg = "Pin Serial inválido: " . $sPinSerial . ".\n";
//					if($sPinCodigo == "" || !is_numeric($sPinCodigo)) 	$msg = "Pin Código inválido: " . $sPinCodigo . ".\n"; // Código antigo: só números
					if($sPinCodigo == "") 	$msg = "Pin Código inválido: " . $sPinCodigo . ".\n";
					if($sValorFace == "" || !is_numeric($sValorFace)) 	$msg = "Valor de face inválido: " . $sValorFace . ".\n";

// Debug
//(opr_codigo=41, pin_lote_codigo=4116031100, pin_valor=0 -> val:11FEB005100, codigo:780F08AB2AB741179A5F2DB5, valor: 0)
//linha (0): '11FEB005100	780F08AB2AB741179A5F2DB5	5'
//echo "fcanal: ".$fcanal."<br>";
//echo "linha ($i): '$linha'<br>\n";
//echo "(opr_codigo=".$opr_codigo.", pin_lote_codigo=".$sLote.", pin_valor=".$sValorFace." -> val:".$sPinSerial.", codigo:".$sPinCodigo.")<hr>\n";
					//insere pin
					if($msg == ""){
						$sql = "insert into pins (opr_codigo, pin_status, pin_serial, pin_codigo, pin_local, pin_valor, pin_lote_codigo, pin_dataentrada, pin_horaentrada, pin_canal) ";
						$sql .= "values ($opr_codigo, 1, '$sPinSerial', '$sPinCodigo', '$iPinLocal', '$sValorFace', '$sLote', CURRENT_DATE, CURRENT_TIME, '".$fcanal."')";

						$ret = SQLexecuteQuery($sql);
						if(!$ret) $msg = "Erro ao inserir registro: " . $linha . ".\n";
					}
			
				} else {
					$seq++;
					$valor = "";
					
					$sai = array("-", " ", "unidades");
					$entra = array("", "", "");
					$linhaAr = explode("k", str_replace($sai, $entra, strtolower($linha)));
					$lote  = $opr_codigo . date('dmy') . "0" . $seq;
					$valor = OGPlanet_traduzKValor($linhaAr[0]);
					$qtd = $linhaAr[1];

					//Validacoes
					if($lote == "" || !is_numeric($lote)) 	$msg = "Lote inválido: " . $lote . ".\n";
					if($valor == "" || !is_numeric($valor)) $msg = "Valor de face inválido: " . $valor . ".\n";
		
					if($msg == ""){		    
						$sql  = "select count(*) as qtde from pins where opr_codigo = $opr_codigo and pin_lote_codigo = $lote and pin_valor = $valor and pin_canal='s' ";
//echo "sql: $sql";
						$rs = SQLexecuteQuery($sql);
						$ret = true;
						if($rs && pg_num_rows($rs) > 0){
							$rs_row = pg_fetch_array($rs);
							if($rs_row['qtde'] == 0) $ret = false;
						}			
						if($ret) $msg = "Arquivo já foi importado (opr_codigo=$opr_codigo, pin_lote_codigo=$lote, pin_valor=$valor -> val:".$linhaAr[0].", qtd:".$linhaAr[1].")\n"."linha ($i): '$linha'\n";
					}

				} 

				//Se houve erro, sai do loop
				if($msg != "") break;
				
			}
		}


		//Cria diretorios
		if($msg == ""){
			if(!is_dir($folder)) $msg = "Diretório raiz não existe.\n";
			if($msg == ""){
				if(!is_dir($folder. $opr_codigo)) 
					if(!mkdir($folder. $opr_codigo)) $msg = "Não foi possivel criar diretorio da operadora.\n";
			}
		}				

		//move arquivo
		if($msg == ""){
			$fileDest = $folder . $opr_codigo . "/" . $nomeArq; 
			if(!copy($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.\n"; 
			elseif((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo vazio ou inválido.\n";
		}
		
		//Finaliza transacao
		if($msg == ""){
			$sql = "COMMIT TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao comitar transação.\n";
		} else {
			$sql = "ROLLBACK TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
		}

		return $msg;
		
	}

function Axeso5_traduzKValor($k){
	$valor["5"] = "5";
	$valor["7"] = "7";
	$valor["8.5"] = "8.5";
	$valor["9"] = "9";
	$valor["12"] = "12";
	$valor["14"] = "14";
	$valor["17"] = "17";
	$valor["20"] = "20";
	$valor["22"] = "22";
	$valor["26"] = "26";
	$valor["27"] = "27";
	$valor["39"] = "39";
	$valor["40"] = "40";
	$valor["44"] = "44";
	$valor["52"] = "52";
	$valor["60"] = "60";
	$valor["66"] = "66";
	$valor["77"] = "77";
	$valor["78"] = "78";
	$valor["110"] = "110";
	$valor["123"] = "123";
        $valor["145"] = "145";
        
	return $valor[$k];
}

function processaLote_Axeso5($fileSource, $nomeArq, $opr_codigo, $fcanal){
	
		global $folder, $opr_codigo_Axeso5, $opr_codigo_Axeso5_new;
		
		$msg = "";
		
		//Valida entradas
		$operadoras = array($opr_codigo_Axeso5, $opr_codigo_Axeso5_new);
		if(is_null($fileSource) || trim($fileSource) == "") $msg = "Caminho do arquivo vázio.".PHP_EOL;
		if(is_null($nomeArq) || trim($nomeArq) == "") $msg = "Nome do arquivo vázio.".PHP_EOL;
		if(is_null($opr_codigo) || trim($opr_codigo) == "" || !is_numeric($opr_codigo) || !in_array($opr_codigo, $operadoras)) $msg = "Código da operadora inválido.".PHP_EOL;

		//local
		$iPinLocal = 19;


		//Abre arquivo e le conteudo
		if($msg == ""){
			$handle = fopen($fileSource, "r");
			$carga = fread($handle, filesize($fileSource));
			fclose($handle);

			$cargaAr = explode("\n", $carga);
			if(count($cargaAr) == 0) $msg = "Arquivo vázio.".PHP_EOL;
		} 

		//valida tamanho da linha
		if($msg == ""){
			for($i=0; $i < count($cargaAr); $i++){
				$cargaAr[$i] = trim($cargaAr[$i]);
								
				// tira linha em branco
				if(trim($cargaAr[$i]) == ""){
					array_splice($cargaAr, $i, 1); 
					continue;
				}
			}
		}
		
		
		//Inicia transacao
		if($msg == ""){
			$sql = "BEGIN TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao iniciar transação.".PHP_EOL;
		}

		//insere lote
		if($msg == ""){
			
			$seq = 0;
			$valor = 0;
			for($i=0; $i < count($cargaAr); $i++){
	
				$linha 		= $cargaAr[$i];

				//dados				
				if (strpos($linha, "unidades") === false) { // corpo

					$linhaAr 	= explode("\t", $linha);
					$sLote 		= $opr_codigo . date('dmy') . "0" . $seq;
					$sPinSerial = $linhaAr[0];
				    $sPinCodigo	= $linhaAr[1];
			    	$sValorFace = $linhaAr[2];

					//Validacoes - "614791 MGspdKFVS5atw9ER 20000"
					if($sLote == "" || !is_numeric($sLote)) 			$msg = "Lote inválido: " . $sLote . ".".PHP_EOL;
					if($sPinSerial == "") 	$msg = "Pin Serial inválido: " . $sPinSerial . ".".PHP_EOL;
					if($sPinCodigo == "") 	$msg = "Pin Código inválido: " . $sPinCodigo . ".".PHP_EOL;
					if($sValorFace == "" || !is_numeric($sValorFace)) 	$msg = "Valor de face inválido: " . $sValorFace . ".".PHP_EOL;

					//insere pin
					if($msg == ""){
						$sql = "insert into pins (opr_codigo, pin_status, pin_serial, pin_codigo, pin_local, pin_valor, pin_lote_codigo, pin_dataentrada, pin_horaentrada, pin_canal) ";
						$sql .= "values ($opr_codigo, 1, '$sPinSerial', '$sPinCodigo', '$iPinLocal', '$sValorFace', '$sLote', CURRENT_DATE, CURRENT_TIME, '".$fcanal."')";

						$ret = SQLexecuteQuery($sql);
						if(!$ret) $msg = "Erro ao inserir registro: " . $linha . ".".PHP_EOL;
					}
			
				} else {
					$seq++;
					$valor = "";
					
					$sai = array("-", " ", "unidades");
					$entra = array("", "", "");
					$linhaAr = explode("k", str_replace($sai, $entra, strtolower($linha)));
					$lote  = $opr_codigo . date('dmy') . "0" . $seq;
					$valor = Axeso5_traduzKValor($linhaAr[0]);
					$qtd = $linhaAr[1];

					//Validacoes
					if($lote == "" || !is_numeric($lote)) 	$msg = "Lote inválido: " . $lote . ".".PHP_EOL;
					if($valor == "" || !is_numeric($valor)) $msg = "Valor de face inválido: " . $valor . ".".PHP_EOL;
		
					if($msg == ""){		    
						$sql  = "select count(*) as qtde from pins where opr_codigo = $opr_codigo and pin_lote_codigo = $lote and pin_valor = $valor and pin_canal='s' ";
						$rs = SQLexecuteQuery($sql);
						$ret = true;
						if($rs && pg_num_rows($rs) > 0){
							$rs_row = pg_fetch_array($rs);
							if($rs_row['qtde'] == 0) $ret = false;
						}			
						if($ret) $msg = "Arquivo já foi importado (opr_codigo=$opr_codigo, pin_lote_codigo=$lote, pin_valor=$valor -> val:".$linhaAr[0].", qtd:".$linhaAr[1].")".PHP_EOL."linha ($i): '$linha'".PHP_EOL;
					}

				} 

				//Se houve erro, sai do loop
				if($msg != "") break;
				
			}
		}


		//Cria diretorios
		if($msg == ""){
			if(!is_dir($folder)) $msg = "Diretório raiz não existe.".PHP_EOL;
			if($msg == ""){
				if(!is_dir($folder. $opr_codigo)) 
					if(!mkdir($folder. $opr_codigo)) $msg = "Não foi possivel criar diretorio da operadora.".PHP_EOL;
			}
		}				

		//move arquivo
		if($msg == ""){
			$fileDest = $folder . $opr_codigo . "/" . $nomeArq; 
			if(!copy($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.".PHP_EOL; 
			elseif((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo vazio ou inválido.".PHP_EOL;
		}
		
		//Finaliza transacao
		if($msg == ""){
			$sql = "COMMIT TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao comitar transação.".PHP_EOL;
		} else {
			$sql = "ROLLBACK TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao dar rollback na transação.".PHP_EOL;
		}

		return $msg;
		
}

function processaLote_BHN($fileSource, $nomeArq, $opr_codigo, $fcanal){
	
		global $folder, $opr_codigo_Facebook_BHN, $opr_codigo_IMVU_BHN;
		
		$msg = "";
		
		//Valida entradas
		$operadoras = array($opr_codigo_Facebook_BHN,$opr_codigo_IMVU_BHN);
		if(is_null($fileSource) || trim($fileSource) == "") $msg = "Caminho do arquivo vázio.\n";
		if(is_null($nomeArq) || trim($nomeArq) == "") $msg = "Nome do arquivo vázio.\n";
		if(is_null($opr_codigo) || trim($opr_codigo) == "" || !is_numeric($opr_codigo) || !in_array($opr_codigo, $operadoras)) $msg = "Código da operadora inválido.\n";

		//local
		$iPinLocal = 36;

		//Abre arquivo e le conteudo
		if($msg == ""){
			$handle = fopen($fileSource, "r");
			$carga = fread($handle, filesize($fileSource));
			fclose($handle);

			$cargaAr = explode("\n", $carga);
			if(count($cargaAr) == 0) $msg = "Arquivo vázio.\n";
		} 

		//valida tamanho da linha
		if($msg == ""){
			for($i=0; $i < count($cargaAr); $i++){
				$cargaAr[$i] = trim($cargaAr[$i]);
								
				// tira linha em branco
				if(trim($cargaAr[$i]) == ""){
					array_splice($cargaAr, $i, 1); 
					continue;
				}
			}
		}
		
		//Inicia transacao
		if($msg == ""){
			$sql = "BEGIN TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao iniciar transação.\n";
		}

		//insere lote
		if($msg == ""){
			
			$seq = 1;
			$valor = 0;
			for($i=0; $i < count($cargaAr); $i++){
	
				$linha 		= $cargaAr[$i];

				//dados				
				if (strpos($linha, "unidades") === false) { // corpo

					$linhaAr 	= explode("\t", $linha);
					$sLote 		= $opr_codigo . date('dmy') . "0" . $seq;
					$sPinSerial     = $linhaAr[0];
                                        $sPinCodigo	= $linhaAr[1];
                                        $sValorFace     = $linhaAr[2];

					//Validacoes - "XFAK1840325	3086462112437622541	25"
					if($sLote == "" || !is_numeric($sLote)) 			$msg = "Lote inválido: " . $sLote . ".\n";
					if($sPinSerial == "") 	$msg = "Pin Serial inválido: " . $sPinSerial . ".\n";
					if($sPinCodigo == "") 	$msg = "Pin Código inválido: " . $sPinCodigo . ".\n";
					if($sValorFace == "" || !is_numeric($sValorFace)) 	$msg = "Valor de face inválido: " . $sValorFace . ".\n";

					//insere pin
					if($msg == ""){
						$sql = "insert into pins (opr_codigo, pin_status, pin_serial, pin_codigo, pin_local, pin_valor, pin_lote_codigo, pin_dataentrada, pin_horaentrada, pin_canal) ";
						$sql .= "values ($opr_codigo, 1, '$sPinSerial', '$sPinCodigo', '$iPinLocal', '$sValorFace', '$sLote', CURRENT_DATE, CURRENT_TIME, '".$fcanal."')";

						$ret = SQLexecuteQuery($sql);
						if(!$ret) $msg = "Erro ao inserir registro: " . $linha . ".\n";
					}
			
				} else {
					
                                    $msg = "Layout do arquivo incorrreto";

				} 

				//Se houve erro, sai do loop
				if($msg != "") break;
				
			}
		}

		//Cria diretorios
		if($msg == ""){
			if(!is_dir($folder)) $msg = "Diretório raiz não existe.\n";
			if($msg == ""){
				if(!is_dir($folder. $opr_codigo)) 
					if(!mkdir($folder. $opr_codigo)) $msg = "Não foi possivel criar diretorio da operadora.\n";
			}
		}				

		//move arquivo
		if($msg == ""){
			$fileDest = $folder . $opr_codigo . "/" . $nomeArq; 
			if(!copy($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.\n"; 
			elseif((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo vazio ou inválido.\n";
		}
		
		//Finaliza transacao
		if($msg == ""){
			$sql = "COMMIT TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao comitar transação.\n";
		} else {
			$sql = "ROLLBACK TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
		}

		return $msg;
		
} //end function processaLote_BHN


function GlobalGames_traduzKValor($k){
	$valor["5"] = "5";
	$valor["10"] = "10";
	$valor["30"] = "30";
	$valor["100"] = "100";
	$valor["200"] = "200";
	$valor["500"] = "500";
	
	return $valor[$k];
}

	function processaLote_GlobalGames($fileSource, $nomeArq, $opr_codigo, $fcanal){
	
		global $folder, $opr_codigo_GlobalGames, $opr_codigo_GlobalGames3;
		
		$msg = "";
		
		//Valida entradas
		$operadoras = array($opr_codigo_GlobalGames, $opr_codigo_GlobalGames3);
		if(is_null($fileSource) || trim($fileSource) == "") $msg = "Caminho do arquivo vázio.\n";
		if(is_null($nomeArq) || trim($nomeArq) == "") $msg = "Nome do arquivo vázio.\n";
		if(is_null($opr_codigo) || trim($opr_codigo) == "" || !is_numeric($opr_codigo) || !in_array($opr_codigo, $operadoras)) $msg = "Código da operadora inválido.\n";

		//local
		$iPinLocal = 19;


		//Abre arquivo e le conteudo
		if($msg == ""){
			$handle = fopen($fileSource, "r");
			$carga = fread($handle, filesize($fileSource));
			fclose($handle);

			$cargaAr = explode("\n", $carga);
			if(count($cargaAr) == 0) $msg = "Arquivo vázio.\n";
		} 

// Debug
//for($i=0; $i < count($cargaAr); $i++){
//	echo "cargaAr[$i]: '".$cargaAr[$i]."'<br>";
//}
//$msg = "";

		//valida tamanho da linha
		if($msg == ""){
			for($i=0; $i < count($cargaAr); $i++){
				$cargaAr[$i] = trim($cargaAr[$i]);
								
				// tira linha em branco
				if(trim($cargaAr[$i]) == ""){
					array_splice($cargaAr, $i, 1); 
					continue;
				}
/*
 				//Valida tamanho da linha
				$num_posicoes = 34;
				if(strlen($cargaAr[$i]) != $num_posicoes){
					$msg = "linha " . ($i+1) . " do arquivo não tem $num_posicoes posições, possui " . strlen($cargaAr[$i]) . ":\n";
					$msg .= "**" . $cargaAr[$i] . "**\n";
					break;
				}
*/
			}
		}
		
		
		//Inicia transacao
		if($msg == ""){
			$sql = "BEGIN TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao iniciar transação.\n";
		}

		//insere lote
		if($msg == ""){
			
			$seq = 0;
			$valor = 0;
			for($i=0; $i < count($cargaAr); $i++){
	
				$linha 		= $cargaAr[$i];

				//dados				
				if (strpos($linha, "unidades") === false) { // corpo

					$linhaAr 	= explode("\t", $linha);
					$sLote 		= $opr_codigo . date('dmy') . "0" . $seq;
					$sPinSerial = $linhaAr[0];
				    $sPinCodigo	= $linhaAr[1];
			    	$sValorFace = $linhaAr[2];

					//Validacoes - "614791 MGspdKFVS5atw9ER 20000"
					if($sLote == "" || !is_numeric($sLote)) 			$msg = "Lote inválido: " . $sLote . ".\n";
					if($sPinSerial == "") 	$msg = "Pin Serial inválido: " . $sPinSerial . ".\n";
//					if($sPinCodigo == "" || !is_numeric($sPinCodigo)) 	$msg = "Pin Código inválido: " . $sPinCodigo . ".\n"; // Código antigo: só números
					if($sPinCodigo == "") 	$msg = "Pin Código inválido: " . $sPinCodigo . ".\n";
					if($sValorFace == "" || !is_numeric($sValorFace)) 	$msg = "Valor de face inválido: " . $sValorFace . ".\n";

// Debug
//(opr_codigo=41, pin_lote_codigo=4116031100, pin_valor=0 -> val:11FEB005100, codigo:780F08AB2AB741179A5F2DB5, valor: 0)
//linha (0): '11FEB005100	780F08AB2AB741179A5F2DB5	5'
//echo "fcanal: ".$fcanal."<br>";
//echo "linha ($i): '$linha'<br>\n";
//echo "(opr_codigo=".$opr_codigo.", pin_lote_codigo=".$sLote.", pin_valor=".$sValorFace." -> val:".$sPinSerial.", codigo:".$sPinCodigo.")<hr>\n";
					//insere pin
					if($msg == ""){
						$sql = "insert into pins (opr_codigo, pin_status, pin_serial, pin_codigo, pin_local, pin_valor, pin_lote_codigo, pin_dataentrada, pin_horaentrada, pin_canal) ";
						$sql .= "values ($opr_codigo, 1, '$sPinSerial', '$sPinCodigo', '$iPinLocal', '$sValorFace', '$sLote', CURRENT_DATE, CURRENT_TIME, '".$fcanal."')";

						$ret = SQLexecuteQuery($sql);
						if(!$ret) $msg = "Erro ao inserir registro: " . $linha . ".\n";
					}
			
				} else {
					$seq++;
					$valor = "";
					
					$sai = array("-", " ", "unidades");
					$entra = array("", "", "");
					$linhaAr = explode("k", str_replace($sai, $entra, strtolower($linha)));
					$lote  = $opr_codigo . date('dmy') . "0" . $seq;
					$valor = GlobalGames_traduzKValor($linhaAr[0]);
					$qtd = $linhaAr[1];

					//Validacoes
					if($lote == "" || !is_numeric($lote)) 	$msg = "Lote inválido: " . $lote . ".\n";
					if($valor == "" || !is_numeric($valor)) $msg = "Valor de face inválido: " . $valor . ".\n";
		
					if($msg == ""){		    
						$sql  = "select count(*) as qtde from pins where opr_codigo = $opr_codigo and pin_lote_codigo = $lote and pin_valor = $valor and pin_canal='s' ";
//echo "sql: $sql";
						$rs = SQLexecuteQuery($sql);
						$ret = true;
						if($rs && pg_num_rows($rs) > 0){
							$rs_row = pg_fetch_array($rs);
							if($rs_row['qtde'] == 0) $ret = false;
						}			
						if($ret) $msg = "Arquivo já foi importado (opr_codigo=$opr_codigo, pin_lote_codigo=$lote, pin_valor=$valor -> val:".$linhaAr[0].", qtd:".$linhaAr[1].")\n"."linha ($i): '$linha'\n";
					}

				} 

				//Se houve erro, sai do loop
				if($msg != "") break;
				
			}
		}


		//Cria diretorios
		if($msg == ""){
			if(!is_dir($folder)) $msg = "Diretório raiz não existe.\n";
			if($msg == ""){
				if(!is_dir($folder. $opr_codigo)) 
					if(!mkdir($folder. $opr_codigo)) $msg = "Não foi possivel criar diretorio da operadora.\n";
			}
		}				

		//move arquivo
		if($msg == ""){
			$fileDest = $folder . $opr_codigo . "/" . $nomeArq; 
			if(!copy($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.\n"; 
			elseif((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo vazio ou inválido.\n";
		}
		
		//Finaliza transacao
		if($msg == ""){
			$sql = "COMMIT TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao comitar transação.\n";
		} else {
			$sql = "ROLLBACK TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
		}

		return $msg;
		
	}

function GlobalGames2_traduzKValor($k){
	$valor["5"] = "5";
	$valor["15"] = "15";
	$valor["50"] = "50";
	$valor["300"] = "300";
	
	return $valor[$k];
}
   
	function processaLote_GlobalGames2($fileSource, $nomeArq, $opr_codigo, $fcanal){
	
		global $folder, $opr_codigo_GlobalGames2;
		
		$msg = "";
		
		//Valida entradas
		$operadoras = array($opr_codigo_GlobalGames2);
		if(is_null($fileSource) || trim($fileSource) == "") $msg = "Caminho do arquivo vázio.\n";
		if(is_null($nomeArq) || trim($nomeArq) == "") $msg = "Nome do arquivo vázio.\n";
		if(is_null($opr_codigo) || trim($opr_codigo) == "" || !is_numeric($opr_codigo) || !in_array($opr_codigo, $operadoras)) $msg = "Código da operadora inválido.\n";

		//local
		$iPinLocal = 19;


		//Abre arquivo e le conteudo
		if($msg == ""){
			$handle = fopen($fileSource, "r");
			$carga = fread($handle, filesize($fileSource));
			fclose($handle);

			$cargaAr = explode("\n", $carga);
			if(count($cargaAr) == 0) $msg = "Arquivo vázio.\n";
		} 

// Debug
//for($i=0; $i < count($cargaAr); $i++){
//	echo "cargaAr[$i]: '".$cargaAr[$i]."'<br>";
//}
//$msg = "";

		//valida tamanho da linha
		if($msg == ""){
			for($i=0; $i < count($cargaAr); $i++){
				$cargaAr[$i] = trim($cargaAr[$i]);
								
				// tira linha em branco
				if(trim($cargaAr[$i]) == ""){
					array_splice($cargaAr, $i, 1); 
					continue;
				}
/*
 				//Valida tamanho da linha
				$num_posicoes = 34;
				if(strlen($cargaAr[$i]) != $num_posicoes){
					$msg = "linha " . ($i+1) . " do arquivo não tem $num_posicoes posições, possui " . strlen($cargaAr[$i]) . ":\n";
					$msg .= "**" . $cargaAr[$i] . "**\n";
					break;
				}
*/
			}
		}
		
		
		//Inicia transacao
		if($msg == ""){
			$sql = "BEGIN TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao iniciar transação.\n";
		}

		//insere lote
		if($msg == ""){
			
			$seq = 0;
			$valor = 0;
			for($i=0; $i < count($cargaAr); $i++){
	
				$linha 		= $cargaAr[$i];

				//dados				
				if (strpos($linha, "unidades") === false) { // corpo

					$linhaAr 	= explode("\t", $linha);
					$sLote 		= $opr_codigo . date('dmy') . "0" . $seq;
					$sPinSerial = $linhaAr[0];
				    $sPinCodigo	= $linhaAr[1];
			    	$sValorFace = $linhaAr[2];

					//Validacoes - "614791 MGspdKFVS5atw9ER 20000"
					if($sLote == "" || !is_numeric($sLote)) 			$msg = "Lote inválido: " . $sLote . ".\n";
					if($sPinSerial == "") 	$msg = "Pin Serial inválido: " . $sPinSerial . ".\n";
//					if($sPinCodigo == "" || !is_numeric($sPinCodigo)) 	$msg = "Pin Código inválido: " . $sPinCodigo . ".\n"; // Código antigo: só números
					if($sPinCodigo == "") 	$msg = "Pin Código inválido: " . $sPinCodigo . ".\n";
					if($sValorFace == "" || !is_numeric($sValorFace)) 	$msg = "Valor de face inválido: " . $sValorFace . ".\n";

// Debug
//(opr_codigo=41, pin_lote_codigo=4116031100, pin_valor=0 -> val:11FEB005100, codigo:780F08AB2AB741179A5F2DB5, valor: 0)
//linha (0): '11FEB005100	780F08AB2AB741179A5F2DB5	5'
//echo "fcanal: ".$fcanal."<br>";
//echo "linha ($i): '$linha'<br>\n";
//echo "(opr_codigo=".$opr_codigo.", pin_lote_codigo=".$sLote.", pin_valor=".$sValorFace." -> val:".$sPinSerial.", codigo:".$sPinCodigo.")<hr>\n";
					//insere pin
					if($msg == ""){
						$sql = "insert into pins (opr_codigo, pin_status, pin_serial, pin_codigo, pin_local, pin_valor, pin_lote_codigo, pin_dataentrada, pin_horaentrada, pin_canal) ";
						$sql .= "values ($opr_codigo, 1, '$sPinSerial', '$sPinCodigo', '$iPinLocal', '$sValorFace', '$sLote', CURRENT_DATE, CURRENT_TIME, '".$fcanal."')";

						$ret = SQLexecuteQuery($sql);
						if(!$ret) $msg = "Erro ao inserir registro: " . $linha . ".\n";
					}
			
				} else {
					$seq++;
					$valor = "";
					
					$sai = array("-", " ", "unidades");
					$entra = array("", "", "");
					$linhaAr = explode("k", str_replace($sai, $entra, strtolower($linha)));
					$lote  = $opr_codigo . date('dmy') . "0" . $seq;
					$valor = GlobalGames2_traduzKValor($linhaAr[0]);
					$qtd = $linhaAr[1];

					//Validacoes
					if($lote == "" || !is_numeric($lote)) 	$msg = "Lote inválido: " . $lote . ".\n";
					if($valor == "" || !is_numeric($valor)) $msg = "Valor de face inválido: " . $valor . ".\n";
		
					if($msg == ""){		    
						$sql  = "select count(*) as qtde from pins where opr_codigo = $opr_codigo and pin_lote_codigo = $lote and pin_valor = $valor and pin_canal='s' ";
//echo "sql: $sql";
						$rs = SQLexecuteQuery($sql);
						$ret = true;
						if($rs && pg_num_rows($rs) > 0){
							$rs_row = pg_fetch_array($rs);
							if($rs_row['qtde'] == 0) $ret = false;
						}			
						if($ret) $msg = "Arquivo já foi importado (opr_codigo=$opr_codigo, pin_lote_codigo=$lote, pin_valor=$valor -> val:".$linhaAr[0].", qtd:".$linhaAr[1].")\n"."linha ($i): '$linha'\n";
					}

				} 

				//Se houve erro, sai do loop
				if($msg != "") break;
				
			}
		}


		//Cria diretorios
		if($msg == ""){
			if(!is_dir($folder)) $msg = "Diretório raiz não existe.\n";
			if($msg == ""){
				if(!is_dir($folder. $opr_codigo)) 
					if(!mkdir($folder. $opr_codigo)) $msg = "Não foi possivel criar diretorio da operadora.\n";
			}
		}				

		//move arquivo
		if($msg == ""){
			$fileDest = $folder . $opr_codigo . "/" . $nomeArq; 
			if(!copy($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.\n"; 
			elseif((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo vazio ou inválido.\n";
		}
		
		//Finaliza transacao
		if($msg == ""){
			$sql = "COMMIT TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao comitar transação.\n";
		} else {
			$sql = "ROLLBACK TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
		}

		return $msg;
		
	}

function StarDoll_traduzKValor($k){
	$valor["10"] = "10";
	
	return $valor[$k];
}

	function processaLote_StarDoll($fileSource, $nomeArq, $opr_codigo, $fcanal){
	
		global $folder, $opr_codigo_StarDoll;
		
		$msg = "";
		
		//Valida entradas
		$operadoras = array($opr_codigo_StarDoll);
		if(is_null($fileSource) || trim($fileSource) == "") $msg = "Caminho do arquivo vázio.\n";
		if(is_null($nomeArq) || trim($nomeArq) == "") $msg = "Nome do arquivo vázio.\n";
		if(is_null($opr_codigo) || trim($opr_codigo) == "" || !is_numeric($opr_codigo) || !in_array($opr_codigo, $operadoras)) $msg = "Código da operadora inválido.\n";

		//local
		$iPinLocal = 21;

		//Abre arquivo e le conteudo
		if($msg == ""){
			$handle = fopen($fileSource, "r");
			$carga = fread($handle, filesize($fileSource));
			fclose($handle);

			$cargaAr = explode("\n", $carga);
			if(count($cargaAr) == 0) $msg = "Arquivo vázio.\n";
		} 

// Debug
//for($i=0; $i < count($cargaAr); $i++){
//	echo "cargaAr[$i]: '".$cargaAr[$i]."'<br>";
//}
//$msg = "";

		//valida tamanho da linha
		if($msg == ""){
			for($i=0; $i < count($cargaAr); $i++){
				$cargaAr[$i] = trim($cargaAr[$i]);
								
				// tira linha em branco
				if(trim($cargaAr[$i]) == ""){
					array_splice($cargaAr, $i, 1); 
					continue;
				}
/*
 				//Valida tamanho da linha
				$num_posicoes = 34;
				if(strlen($cargaAr[$i]) != $num_posicoes){
					$msg = "linha " . ($i+1) . " do arquivo não tem $num_posicoes posições, possui " . strlen($cargaAr[$i]) . ":\n";
					$msg .= "**" . $cargaAr[$i] . "**\n";
					break;
				}
*/
			}
		}
		

		$msg1 = "";
		// Procura por pins existentes
		for($i=0; $i < count($cargaAr); $i++){

			$linha 		= $cargaAr[$i];

			$linhaAr 	= explode("\t", $linha);
			$sPinCodigo	= $linhaAr[0];
			$valor = StarDoll_traduzKValor($linhaAr[1]);
			$sValorFace = $valor;

			//Validacoes - "MGspdKFVS5atw9ER 10"	
//					if($sPinSerial == "" || !is_numeric($sPinSerial)) 	$msg = "Pin Serial inválido: " . $sPinSerial . ".\n";
//					if($sPinCodigo == "" || !is_numeric($sPinCodigo)) 	$msg = "Pin Código inválido: " . $sPinCodigo . ".\n"; // Código antigo: só números
			if($sPinCodigo == "") 	$msg = "Pin Código inválido: " . $sPinCodigo . ".\n";
			if($sValorFace == "" || !is_numeric($sValorFace)) 	$msg = "Valor de face inválido: " . $sValorFace . ".\n";

			//procura pin
			if($msg == ""){
				$sql = "select * from pins p inner join operadoras o on p.opr_codigo = o.opr_codigo where p.opr_codigo = $opr_codigo and p.pin_codigo = '$sPinCodigo' and pin_canal='".$fcanal."' ";

//echo "sql: $sql<br>";
				$ret = SQLexecuteQuery($sql);
				if($ret && pg_num_rows($ret) > 0){
					$ret_row = pg_fetch_array($ret);
					$opr_nome = $ret_row['opr_nome'];

					$msg1 = "Pin já existe no sistema: opr_nome = $opr_nome, opr_codigo = $opr_codigo, pin_codigo = '$sPinCodigo'. Não foi cadastrado.";
					echo $msg1."<br>";
				} 
			}
		}
		if($msg1) {
			$msg .= $msg1;
			echo "PINs foram encontrados no BD: NÃO vai carregar este arquivo...<br>";
		} else {
			echo "PINs não foram encontrados no BD: vai carregar este arquivo...<br>";
		}


		//Inicia transacao
		if($msg == ""){
			$sql = "BEGIN TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao iniciar transação.\n";
		}

		//insere lote
		if($msg == ""){
			
			$seq = 1;
			$lote  = $opr_codigo . date('dmy') . "0" . $seq;

			$valor = 0;
			for($i=0; $i < count($cargaAr); $i++){
	
				$linha 		= $cargaAr[$i];

				//dados
				if (strpos($linha, "unidades") === false) { // corpo

					$linhaAr 	= explode("\t", $linha);
					$sLote 		= $opr_codigo . date('dmy') . "0" . $seq;
					$sPinSerial = "";	//$linhaAr[0];
				    $sPinCodigo	= $linhaAr[0];
					$valor = StarDoll_traduzKValor($linhaAr[1]);
			    	$sValorFace = $valor;

					//Validacoes - "MGspdKFVS5atw9ER 10"	
					if($sLote == "" || !is_numeric($sLote)) 			$msg = "Lote inválido: " . $sLote . ".\n";
//					if($sPinSerial == "" || !is_numeric($sPinSerial)) 	$msg = "Pin Serial inválido: " . $sPinSerial . ".\n";
//					if($sPinCodigo == "" || !is_numeric($sPinCodigo)) 	$msg = "Pin Código inválido: " . $sPinCodigo . ".\n"; // Código antigo: só números
					if($sPinCodigo == "") 	$msg = "Pin Código inválido: " . $sPinCodigo . ".\n";
					if($sValorFace == "" || !is_numeric($sValorFace)) 	$msg = "Valor de face inválido: " . $sValorFace . ".\n";

					//insere pin
					if($msg == ""){
//echo "Vai inserir<br>";
						$sql = "insert into pins (opr_codigo, pin_status, pin_codigo, pin_local, pin_valor, pin_lote_codigo, pin_dataentrada, pin_horaentrada, pin_canal) ";
						$sql .= "values ($opr_codigo, '1', '$sPinCodigo', '$iPinLocal', '$sValorFace', '$sLote', CURRENT_DATE, CURRENT_TIME, '".$fcanal."')";

						$ret2 = SQLexecuteQuery($sql);
						if(!$ret2) $msg = "Erro ao inserir registro: " . $linha . "<br>\n";
if (strlen ($erro = pg_last_error($GLOBALS['connid']))) {
	$message  = date("Y-m-d H:i:s") . " ";
	$message .= "Erro: " . $erro . "<br>\n";
	$message .= "Query: " . $sql . "<br>\n";
	echo $message;
} else {
//	$message = "Sem Erros<br>";
}
					}			
				} else {
					// Não usa a primeira linha, processa aqui apenas por consistencia com os outros modelos de arquivos.
					$seq++;
					$valor = "";
					
					$sai = array("-", " ", "unidades");
					$entra = array("", "", "");
					$linhaAr = explode("k", str_replace($sai, $entra, strtolower($linha)));
					$lote  = $opr_codigo . date('dmy') . "0" . $seq;
					$valor = StarDoll_traduzKValor($linhaAr[0]);
					$qtd = $linhaAr[1];

					//Validacoes
					if($lote == "" || !is_numeric($lote)) 	$msg = "Lote inválido: " . $lote . ".\n";
					if($valor == "" || !is_numeric($valor)) $msg = "Valor de face inválido: " . $valor . ".\n";
		
					if($msg == ""){		    
						$sql  = "select count(*) as qtde from pins where opr_codigo = $opr_codigo and pin_lote_codigo = $lote and pin_valor = $valor and pin_canal='s' ";
//echo "sql: $sql";
						$rs = SQLexecuteQuery($sql);
						$ret = true;
						if($rs && pg_num_rows($rs) > 0){
							$rs_row = pg_fetch_array($rs);
							if($rs_row['qtde'] == 0) $ret = false;
						}			
						if($ret) $msg = "Arquivo já foi importado (opr_codigo=$opr_codigo, pin_lote_codigo=$lote, pin_valor=$valor -> val:".$linhaAr[0].", qtd:".$linhaAr[1].")\n"."linha ($i): '$linha'\n";
					}

				} 

				//Se houve erro, sai do loop
				if($msg != "") break;
				
			}
		}


		//Cria diretorios
		if($msg == ""){
			if(!is_dir($folder)) $msg = "Diretório raiz não existe.\n";
			if($msg == ""){
				if(!is_dir($folder. $opr_codigo)) 
					if(!mkdir($folder. $opr_codigo)) $msg = "Não foi possivel criar diretorio da operadora.\n";
			}
		}				

		//move arquivo
		if($msg == ""){
			$fileDest = $folder . $opr_codigo . "/" . $nomeArq; 
			if(!copy($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.\n"; 
			elseif((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo vazio ou inválido.\n";
		}
		
		//Finaliza transacao
		if($msg == ""){
			$sql = "COMMIT TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao comitar transação.\n";
		} else {
			$sql = "ROLLBACK TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
		}

		return $msg;
		
	}

function Softnyx_traduzKValor($k){
	$valor["4"] = "10";
	$valor["5"] = "13";
	$valor["10"] = "25";
	$valor["15"] = "37";
	$valor["20"] = "49";
	$valor["22"] = "49";
	$valor["40"] = "97";
	$valor["60"] = "145";
	
	return $valor[$k];
}

	function processaLote_Softnyx($fileSource, $nomeArq, $opr_codigo, $fcanal){
	
		global $folder, $opr_codigo_Softnyx;
		
		$msg = "";
		
		//Valida entradas
		$operadoras = array($opr_codigo_Softnyx);
		if(is_null($fileSource) || trim($fileSource) == "") $msg = "Caminho do arquivo vázio.\n";
		if(is_null($nomeArq) || trim($nomeArq) == "") $msg = "Nome do arquivo vázio.\n";
		if(is_null($opr_codigo) || trim($opr_codigo) == "" || !is_numeric($opr_codigo) || !in_array($opr_codigo, $operadoras)) $msg = "Código da operadora inválido.\n";

		//local
		$iPinLocal = 19;


		//Abre arquivo e le conteudo
		if($msg == ""){
			$handle = fopen($fileSource, "r");
			$carga = fread($handle, filesize($fileSource));
			fclose($handle);

			$cargaAr = explode("\n", $carga);
			if(count($cargaAr) == 0) $msg = "Arquivo vázio.\n";
		} 

// Debug
//for($i=0; $i < count($cargaAr); $i++){
//	echo "cargaAr[$i]: '".$cargaAr[$i]."'<br>";
//}
//$msg = "";

		//valida tamanho da linha
		if($msg == ""){
			for($i=0; $i < count($cargaAr); $i++){
				$cargaAr[$i] = trim($cargaAr[$i]);
								
				// tira linha em branco
				if(trim($cargaAr[$i]) == ""){
					array_splice($cargaAr, $i, 1); 
					continue;
				}
/*
 				//Valida tamanho da linha
				$num_posicoes = 34;
				if(strlen($cargaAr[$i]) != $num_posicoes){
					$msg = "linha " . ($i+1) . " do arquivo não tem $num_posicoes posições, possui " . strlen($cargaAr[$i]) . ":\n";
					$msg .= "**" . $cargaAr[$i] . "**\n";
					break;
				}
*/
			}
		}
		
		
		//Inicia transacao
		if($msg == ""){
			$sql = "BEGIN TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao iniciar transação.\n";
		}

		//insere lote
		if($msg == ""){
			
			$seq = 0;
			$valor = 0;
			for($i=0; $i < count($cargaAr); $i++){
	
				$linha 		= $cargaAr[$i];

				//dados				
				if (strpos($linha, "unidades") === false) { // corpo

					$linhaAr 	= explode("\t", $linha);
					$sLote 		= $opr_codigo . date('dmy') . "0" . $seq;
					$sPinSerial = $linhaAr[0];
				    $sPinCodigo	= $linhaAr[1];
			    	$sValorFace = $valor;

					//Validacoes - "614791 MGspdKFVS5atw9ER 20000"
					// Nenhuma 
/*
					if($sLote == "" || !is_numeric($sLote)) 			$msg = "Lote inválido: " . $sLote . ".\n";
					if($sPinSerial == "" || !is_numeric($sPinSerial)) 	$msg = "Pin Serial inválido: " . $sPinSerial . ".\n";
//					if($sPinCodigo == "" || !is_numeric($sPinCodigo)) 	$msg = "Pin Código inválido: " . $sPinCodigo . ".\n"; // Código antigo: só números
					if($sPinCodigo == "") 	$msg = "Pin Código inválido: " . $sPinCodigo . ".\n";
					if($sValorFace == "" || !is_numeric($sValorFace)) 	$msg = "Valor de face inválido: " . $sValorFace . ".\n";
*/
					//insere pin
					if($msg == ""){
						$sql = "insert into pins (opr_codigo, pin_status, pin_serial, pin_codigo, pin_local, pin_valor, pin_lote_codigo, pin_dataentrada, pin_horaentrada, pin_canal) ";
						$sql .= "values ($opr_codigo, 1, '$sPinSerial', '$sPinCodigo', '$iPinLocal', '$sValorFace', '$sLote', CURRENT_DATE, CURRENT_TIME, '".$fcanal."')";

						$ret = SQLexecuteQuery($sql);
						if(!$ret) $msg = "Erro ao inserir registro: " . $linha . ".\n";
					}
			
				} else {
					$seq++;
					$valor = "";
					
					$sai = array("-", " ", "unidades");
					$entra = array("", "", "");
					$linhaAr = explode("k", str_replace($sai, $entra, strtolower($linha)));
					$lote  = $opr_codigo . date('dmy') . "0" . $seq;
					$valor = Softnyx_traduzKValor($linhaAr[0]);
					$qtd = $linhaAr[1];

					//Validacoes
					if($lote == "" || !is_numeric($lote)) 	$msg = "Lote inválido: " . $lote . ".\n";
					if($valor == "" || !is_numeric($valor)) $msg = "Valor de face inválido: " . $valor . ".\n";
		
					if($msg == ""){		    
						$sql  = "select count(*) as qtde from pins where opr_codigo = $opr_codigo and pin_lote_codigo = $lote and pin_valor = $valor and pin_canal='s' ";
//echo "sql: $sql";
						$rs = SQLexecuteQuery($sql);
						$ret = true;
						if($rs && pg_num_rows($rs) > 0){
							$rs_row = pg_fetch_array($rs);
							if($rs_row['qtde'] == 0) $ret = false;
						}			
						if($ret) $msg = "Arquivo já foi importado (opr_codigo=$opr_codigo, pin_lote_codigo=$lote, pin_valor=$valor -> val:".$linhaAr[0].", qtd:".$linhaAr[1].")\n"."linha ($i): '$linha'\n";
					}

				} 

				//Se houve erro, sai do loop
				if($msg != "") break;
				
			}
		}


		//Cria diretorios
		if($msg == ""){
			if(!is_dir($folder)) $msg = "Diretório raiz não existe.\n";
			if($msg == ""){
				if(!is_dir($folder. $opr_codigo)) 
					if(!mkdir($folder. $opr_codigo)) $msg = "Não foi possivel criar diretorio da operadora.\n";
			}
		}				

		//move arquivo
		if($msg == ""){
			$fileDest = $folder . $opr_codigo . "/" . $nomeArq; 
			if(!copy($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.\n"; 
			elseif((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo vazio ou inválido.\n";
		}
		
		//Finaliza transacao
		if($msg == ""){
			$sql = "COMMIT TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao comitar transação.\n";
		} else {
			$sql = "ROLLBACK TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
		}

		return $msg;
		
	}

function Jolt_traduzKValor($k){
	$valor["5"] = "5";
	$valor["10"] = "10";
	$valor["25"] = "25";
	$valor["50"] = "50";
	
	return $valor[$k];
}

	function processaLote_Jolt($fileSource, $nomeArq, $opr_codigo, $fcanal){
	
		global $folder, $opr_codigo_Jolt;
		
		$msg = "";
		
		//Valida entradas
		$operadoras = array($opr_codigo_Jolt);
		if(is_null($fileSource) || trim($fileSource) == "") $msg = "Caminho do arquivo vázio.\n";
		if(is_null($nomeArq) || trim($nomeArq) == "") $msg = "Nome do arquivo vázio.\n";
		if(is_null($opr_codigo) || trim($opr_codigo) == "" || !is_numeric($opr_codigo) || !in_array($opr_codigo, $operadoras)) $msg = "Código da operadora inválido.\n";

		//local
		$iPinLocal = 19;


		//Abre arquivo e le conteudo
		if($msg == ""){
			$handle = fopen($fileSource, "r");
			$carga = fread($handle, filesize($fileSource));
			fclose($handle);

			$cargaAr = explode("\n", $carga);
			if(count($cargaAr) == 0) $msg = "Arquivo vázio.\n";
		} 

// Debug
//for($i=0; $i < count($cargaAr); $i++){
//	echo "cargaAr[$i]: '".$cargaAr[$i]."'<br>";
//}
//$msg = "";

		//valida tamanho da linha
		if($msg == ""){
			for($i=0; $i < count($cargaAr); $i++){
				$cargaAr[$i] = trim($cargaAr[$i]);
								
				// tira linha em branco
				if(trim($cargaAr[$i]) == ""){
					array_splice($cargaAr, $i, 1); 
					continue;
				}
/*
 				//Valida tamanho da linha
				$num_posicoes = 34;
				if(strlen($cargaAr[$i]) != $num_posicoes){
					$msg = "linha " . ($i+1) . " do arquivo não tem $num_posicoes posições, possui " . strlen($cargaAr[$i]) . ":\n";
					$msg .= "**" . $cargaAr[$i] . "**\n";
					break;
				}
*/
			}
		}
		
		
		//Inicia transacao
		if($msg == ""){
			$sql = "BEGIN TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao iniciar transação.\n";
		}

		//insere lote
		if($msg == ""){
			
			$seq = 0;
			$valor = 0;
			for($i=0; $i < count($cargaAr); $i++){
	
				$linha 		= $cargaAr[$i];

				//dados				
				if (strpos($linha, "unidades") === false) { // corpo

					$linhaAr 	= explode("\t", $linha);
					$sLote 		= $opr_codigo . date('dmy') . "0" . $seq;
					$sPinSerial = $linhaAr[0];
				    $sPinCodigo	= $linhaAr[1];
			    	$sValorFace = $valor;

					//Validacoes - "614791 MGspdKFVS5atw9ER 20000"
					// Nenhuma 
/*
					if($sLote == "" || !is_numeric($sLote)) 			$msg = "Lote inválido: " . $sLote . ".\n";
					if($sPinSerial == "" || !is_numeric($sPinSerial)) 	$msg = "Pin Serial inválido: " . $sPinSerial . ".\n";
//					if($sPinCodigo == "" || !is_numeric($sPinCodigo)) 	$msg = "Pin Código inválido: " . $sPinCodigo . ".\n"; // Código antigo: só números
					if($sPinCodigo == "") 	$msg = "Pin Código inválido: " . $sPinCodigo . ".\n";
					if($sValorFace == "" || !is_numeric($sValorFace)) 	$msg = "Valor de face inválido: " . $sValorFace . ".\n";
*/
					//insere pin
					if($msg == ""){
						$sql = "insert into pins (opr_codigo, pin_status, pin_serial, pin_codigo, pin_local, pin_valor, pin_lote_codigo, pin_dataentrada, pin_horaentrada, pin_canal) ";
						$sql .= "values ($opr_codigo, 1, '$sPinSerial', '$sPinCodigo', '$iPinLocal', '$sValorFace', '$sLote', CURRENT_DATE, CURRENT_TIME, '".$fcanal."')";

						$ret = SQLexecuteQuery($sql);
						if(!$ret) $msg = "Erro ao inserir registro: " . $linha . ".\n";
					}
			
				} else {
					$seq++;
					$valor = "";
					
					$sai = array("-", " ", "unidades");
					$entra = array("", "", "");
					$linhaAr = explode("k", str_replace($sai, $entra, strtolower($linha)));
					$lote  = $opr_codigo . date('dmy') . "0" . $seq;
					$valor = Jolt_traduzKValor($linhaAr[0]);
					$qtd = $linhaAr[1];

					//Validacoes
					if($lote == "" || !is_numeric($lote)) 	$msg = "Lote inválido: " . $lote . ".\n";
					if($valor == "" || !is_numeric($valor)) $msg = "Valor de face inválido: " . $valor . ".\n";
		
					if($msg == ""){		    
						$sql  = "select count(*) as qtde from pins where opr_codigo = $opr_codigo and pin_lote_codigo = $lote and pin_valor = $valor and pin_canal='s' ";
//echo "sql: $sql";
						$rs = SQLexecuteQuery($sql);
						$ret = true;
						if($rs && pg_num_rows($rs) > 0){
							$rs_row = pg_fetch_array($rs);
							if($rs_row['qtde'] == 0) $ret = false;
						}			
						if($ret) $msg = "Arquivo já foi importado (opr_codigo=$opr_codigo, pin_lote_codigo=$lote, pin_valor=$valor -> val:".$linhaAr[0].", qtd:".$linhaAr[1].")\n"."linha ($i): '$linha'\n";
					}

				} 

				//Se houve erro, sai do loop
				if($msg != "") break;
				
			}
		}


		//Cria diretorios
		if($msg == ""){
			if(!is_dir($folder)) $msg = "Diretório raiz não existe.\n";
			if($msg == ""){
				if(!is_dir($folder. $opr_codigo)) 
					if(!mkdir($folder. $opr_codigo)) $msg = "Não foi possivel criar diretorio da operadora.\n";
			}
		}				

		//move arquivo
		if($msg == ""){
			$fileDest = $folder . $opr_codigo . "/" . $nomeArq; 
			if(!copy($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.\n"; 
			elseif((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo vazio ou inválido.\n";
		}
		
		//Finaliza transacao
		if($msg == ""){
			$sql = "COMMIT TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao comitar transação.\n";
		} else {
			$sql = "ROLLBACK TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
		}

		return $msg;
		
	}

function Cosmopax_traduzKValor($k){
	$valor["10"] = "10";
	
	return $valor[$k];
}

	function processaLote_Cosmopax($fileSource, $nomeArq, $opr_codigo, $loteValor, $fcanal){
	
		global $folder, $opr_codigo_Cosmopax;
		
		$msg = "";
		
		//Valida entradas
		$operadoras = array($opr_codigo_Cosmopax);
		if(is_null($fileSource) || trim($fileSource) == "") $msg = "Caminho do arquivo vázio.\n";
		if(is_null($nomeArq) || trim($nomeArq) == "") $msg = "Nome do arquivo vázio.\n";
		if(is_null($opr_codigo) || trim($opr_codigo) == "" || !is_numeric($opr_codigo) || !in_array($opr_codigo, $operadoras)) $msg = "Código da operadora inválido.\n";

		//local
		$iPinLocal = 19;


		//Abre arquivo e le conteudo
		if($msg == ""){
			$handle = fopen($fileSource, "r");
			$carga = fread($handle, filesize($fileSource));
			fclose($handle);

			$cargaAr = explode("\n", $carga);
			if(count($cargaAr) == 0) $msg = "Arquivo vázio.\n";
		} 

// Debug
//for($i=0; $i < count($cargaAr); $i++){
//	echo "cargaAr[$i]: '".$cargaAr[$i]."'<br>";
//}
//$msg = "";

		//valida tamanho da linha
		if($msg == ""){
			for($i=0; $i < count($cargaAr); $i++){
				$cargaAr[$i] = trim($cargaAr[$i]);
								
				// tira linha em branco
				if(trim($cargaAr[$i]) == ""){
					array_splice($cargaAr, $i, 1); 
					continue;
				}
/*
 				//Valida tamanho da linha
				$num_posicoes = 34;
				if(strlen($cargaAr[$i]) != $num_posicoes){
					$msg = "linha " . ($i+1) . " do arquivo não tem $num_posicoes posições, possui " . strlen($cargaAr[$i]) . ":\n";
					$msg .= "**" . $cargaAr[$i] . "**\n";
					break;
				}
*/
			}
		}
		
		
		//Inicia transacao
		if($msg == ""){
			$sql = "BEGIN TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao iniciar transação.\n";
		}

		//insere lote
		if($msg == ""){
			
			$seq = 0;
			$valor = 0;
			for($i=0; $i < count($cargaAr); $i++){
	
				$linha 		= $cargaAr[$i];

				//dados				
				if (strpos($linha, "unidades") === false) { // corpo

					$linhaAr 	= explode("\t", $linha);
					$sLote 		= $opr_codigo . date('ymdHis'); // $opr_codigo . date('dmy') . "0" . $seq;
				    $sPinCodigo	= $linhaAr[0];
			    	$sValorFace = $linhaAr[1];
//echo "sPinCodigo :$sPinCodigo, sValorFace: $sValorFace<br>";
					//Validacoes - "614791 98986f4c5ae5399e 10"
					if($sLote == "" || !is_numeric($sLote)) 			$msg = "Lote inválido: " . $sLote . ".\n";
//					if($sPinSerial == "" || !is_numeric($sPinSerial)) 	$msg = "Pin Serial inválido: " . $sPinSerial . ".\n";
//					if($sPinCodigo == "" || !is_numeric($sPinCodigo)) 	$msg = "Pin Código inválido: " . $sPinCodigo . ".\n"; // Código antigo: só números
					if($sPinCodigo == "") 	$msg = "Pin Código inválido: " . $sPinCodigo . ".\n";
					if($sValorFace == "" || !is_numeric($sValorFace)) 	$msg = "Valor de face inválido (C): " . $sValorFace . ".\n";

					//insere pin
					if($msg == ""){
						$sql = "insert into pins (opr_codigo, pin_status, pin_serial, pin_codigo, pin_local, pin_valor, pin_lote_codigo, pin_dataentrada, pin_horaentrada, pin_canal) ";
						$sql .= "values ($opr_codigo, 1, 0, '$sPinCodigo', '$iPinLocal', '$sValorFace', '$sLote', CURRENT_DATE, CURRENT_TIME, '".$fcanal."')";

						$ret = SQLexecuteQuery($sql);
						if(!$ret) $msg = "Erro ao inserir registro: " . $linha . ".\n";
					}
			
				} else {
					$seq++;
					$valor = "";
					
					$sai = array("-", "unidades");
					$entra = array("", "", "");
//					$linhaAr = explode("-", str_replace($sai, $entra, strtolower($linha)));
					$strtmp = str_replace($sai, $entra, strtolower($linha));
					$linhaAr = explode(" ", str_replace($sai, $entra, strtolower($linha)));
					$lote  = $opr_codigo . date('ymdHis');	//	$opr_codigo . date('dmy') . "0" . $seq;
					$valor = Cosmopax_traduzKValor(trim($linhaAr[0]));
					$qtd = trim($linhaAr[1]);
//echo "linha: $linha<br>";
//echo "linhaAr[0]: ".$linhaAr[0]."<br>";
//echo "linhaAr[1]: ".$linhaAr[1]."<br>";
//echo "lote: $lote, valor: $valor, qtd: $qtd<br>";


					//Validacoes
					if($lote == "" || !is_numeric($lote)) 	$msg = "Lote inválido: " . $lote . ".\n";
					if($valor == "" || !is_numeric($valor)) $msg = "Valor de face inválido (A): " . $valor . ".\n";
		
					if($msg == ""){		    
						$sql  = "select count(*) as qtde from pins where opr_codigo = $opr_codigo and pin_lote_codigo = $lote and pin_valor = $valor and pin_canal='s' ";
//echo "sql: $sql";
						$rs = SQLexecuteQuery($sql);
						$ret = true;
						if($rs && pg_num_rows($rs) > 0){
							$rs_row = pg_fetch_array($rs);
							if($rs_row['qtde'] == 0) $ret = false;
						}			
						if($ret) $msg = "Arquivo já foi importado (opr_codigo=$opr_codigo, pin_lote_codigo=$lote, pin_valor=$valor -> val:".$linhaAr[0].", qtd:".$linhaAr[1].")\n"."linha ($i): '$linha'\n";
					}

				} 

				//Se houve erro, sai do loop
				if($msg != "") break;
				
			}
		}


		//Cria diretorios
		if($msg == ""){
			if(!is_dir($folder)) $msg = "Diretório raiz não existe.\n";
			if($msg == ""){
				if(!is_dir($folder. $opr_codigo)) 
					if(!mkdir($folder. $opr_codigo)) $msg = "Não foi possivel criar diretorio da operadora.\n";
			}
		}				

		//move arquivo
		if($msg == ""){
			$fileDest = $folder . $opr_codigo . "/" . $nomeArq; 
			if(!copy($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.\n"; 
			elseif((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo vazio ou inválido.\n";
		}
		
		//Finaliza transacao
		if($msg == ""){
			$sql = "COMMIT TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao comitar transação.\n";
		} else {
			$sql = "ROLLBACK TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
		}

		return $msg;
		
	}


function Hive_traduzKValor($k){
	$valor["5"] = "13";
	$valor["10"] = "24";
	$valor["15"] = "35";
	$valor["20"] = "45";
	$valor["50"] = "100";
	
	return $valor[$k];
}

	function processaLote_Hive($fileSource, $nomeArq, $opr_codigo, $loteValor, $fcanal){
	
		global $folder, $opr_codigo_Hive;
		
		$msg = "";
		
		//Valida entradas
		$operadoras = array($opr_codigo_Hive);
		if(is_null($fileSource) || trim($fileSource) == "") $msg = "Caminho do arquivo vázio.\n";
		if(is_null($nomeArq) || trim($nomeArq) == "") $msg = "Nome do arquivo vázio.\n";
		if(is_null($opr_codigo) || trim($opr_codigo) == "" || !is_numeric($opr_codigo) || !in_array($opr_codigo, $operadoras)) $msg = "Código da operadora inválido.\n";

		//local
		$iPinLocal = 31;


		//Abre arquivo e le conteudo
		if($msg == ""){
			$handle = fopen($fileSource, "r");
			$carga = fread($handle, filesize($fileSource));
			fclose($handle);

			$cargaAr = explode("\n", $carga);
			if(count($cargaAr) == 0) $msg = "Arquivo vázio.\n";
		} 

// Debug
//for($i=0; $i < count($cargaAr); $i++){
//	echo "cargaAr[$i]: '".$cargaAr[$i]."'<br>";
//}
//$msg = "";

		//valida tamanho da linha
		if($msg == ""){
			for($i=0; $i < count($cargaAr); $i++){
				$cargaAr[$i] = trim($cargaAr[$i]);
								
				// tira linha em branco
				if(trim($cargaAr[$i]) == ""){
					array_splice($cargaAr, $i, 1); 
					continue;
				}
/*
 				//Valida tamanho da linha
				$num_posicoes = 34;
				if(strlen($cargaAr[$i]) != $num_posicoes){
					$msg = "linha " . ($i+1) . " do arquivo não tem $num_posicoes posições, possui " . strlen($cargaAr[$i]) . ":\n";
					$msg .= "**" . $cargaAr[$i] . "**\n";
					break;
				}
*/
			}
		}
		
		
		//Inicia transacao
		if($msg == ""){
			$sql = "BEGIN TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao iniciar transação.\n";
		}

		//insere lote
		if($msg == ""){
			
			$seq = 0;
			$valor = 0;
			for($i=0; $i < count($cargaAr); $i++){
	
				$linha 		= $cargaAr[$i];

				//dados				
				if (strpos($linha, "unidades") === false) { // corpo

					$linhaAr 	= explode("\t", $linha);
					$sLote 		= $opr_codigo . date('dm') . "0" . $seq;
				    $sPinCodigo	= $linhaAr[0];
			    	$sValorFace = $valor;

					//Validacoes - "614791 MGspdKFVS5atw9ER 20000"
					if($sLote == "" || !is_numeric($sLote)) 			$msg = "Lote inválido: " . $sLote . ".\n";
//					if($sPinSerial == "" || !is_numeric($sPinSerial)) 	$msg = "Pin Serial inválido: " . $sPinSerial . ".\n";
//					if($sPinCodigo == "" || !is_numeric($sPinCodigo)) 	$msg = "Pin Código inválido: " . $sPinCodigo . ".\n"; // Código antigo: só números
					if($sPinCodigo == "") 	$msg = "Pin Código inválido: " . $sPinCodigo . ".\n";
					if($sValorFace == "" || !is_numeric($sValorFace)) 	$msg = "Valor de face inválido: " . $sValorFace . ".\n";

					//insere pin
					if($msg == ""){
						$sql = "insert into pins (opr_codigo, pin_status, pin_serial, pin_codigo, pin_local, pin_valor, pin_lote_codigo, pin_dataentrada, pin_horaentrada, pin_canal) ";
						$sql .= "values ($opr_codigo, 1, '', '$sPinCodigo', '$iPinLocal', '$sValorFace', '$sLote', CURRENT_DATE, CURRENT_TIME, '".$fcanal."')";
//echo "sql: $sql<br>";
						$ret = SQLexecuteQuery($sql);
						if(!$ret) $msg = "Erro ao inserir registro: " . $linha . ".\n";
					}
			
				} else {
					$seq++;
					$valor = "";
					
					$sai = array("-", " ", "unidades");
					$entra = array("", "", "");
					$linhaAr = explode("k", str_replace($sai, $entra, strtolower($linha)));
					$lote  = $opr_codigo . date('dmy') . "0" . $seq;
					$valor = Hive_traduzKValor($linhaAr[0]);
					$qtd = $linhaAr[1];

					//Validacoes
					if($lote == "" || !is_numeric($lote)) 	$msg = "Lote inválido: " . $lote . ".\n";
					if($valor == "" || !is_numeric($valor)) $msg = "Valor de face inválido: " . $valor . ".\n";
		
					if($msg == ""){		    
						$sql  = "select count(*) as qtde from pins where opr_codigo = $opr_codigo and pin_lote_codigo = $lote and pin_valor = $valor and pin_canal='s' ";
//echo "sql: $sql";
						$rs = SQLexecuteQuery($sql);
						$ret = true;
						if($rs && pg_num_rows($rs) > 0){
							$rs_row = pg_fetch_array($rs);
							if($rs_row['qtde'] == 0) $ret = false;
						}			
						if($ret) $msg = "Arquivo já foi importado (opr_codigo=$opr_codigo, pin_lote_codigo=$lote, pin_valor=$valor -> val:".$linhaAr[0].", qtd:".$linhaAr[1].")\n"."linha ($i): '$linha'\n";
					}

				} 

				//Se houve erro, sai do loop
				if($msg != "") break;
				
			}
		}


		//Cria diretorios
		if($msg == ""){
			if(!is_dir($folder)) $msg = "Diretório raiz não existe.\n";
			if($msg == ""){
				if(!is_dir($folder. $opr_codigo)) 
					if(!mkdir($folder. $opr_codigo)) $msg = "Não foi possivel criar diretorio da operadora.\n";
			}
		}				

		//move arquivo
		if($msg == ""){
			$fileDest = $folder . $opr_codigo . "/" . $nomeArq; 
			if(!copy($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.\n"; 
			elseif((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo vazio ou inválido.\n";
		}
		
		//Finaliza transacao
		if($msg == ""){
			$sql = "COMMIT TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao comitar transação.\n";
		} else {
			$sql = "ROLLBACK TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
		}

		return $msg;
		
	}


function Escola24h_traduzKValor($k){
	$valor["29"] = "29";
	
	return $valor[$k];
}

	function processaLote_Escola24h($fileSource, $nomeArq, $opr_codigo, $loteValor, $fcanal){
	
		global $folder, $opr_codigo_Escola24h;
		
		$msg = "";
		
		//Valida entradas
		$operadoras = array($opr_codigo_Escola24h);
		if(is_null($fileSource) || trim($fileSource) == "") $msg = "Caminho do arquivo vázio.\n";
		if(is_null($nomeArq) || trim($nomeArq) == "") $msg = "Nome do arquivo vázio.\n";
		if(is_null($opr_codigo) || trim($opr_codigo) == "" || !is_numeric($opr_codigo) || !in_array($opr_codigo, $operadoras)) $msg = "Código da operadora inválido.\n";

		//local
		$iPinLocal = 31;


		//Abre arquivo e le conteudo
		if($msg == ""){
			$handle = fopen($fileSource, "r");
			$carga = fread($handle, filesize($fileSource));
			fclose($handle);

			$cargaAr = explode("\n", $carga);
			if(count($cargaAr) == 0) $msg = "Arquivo vázio.\n";
		} 

// Debug
//for($i=0; $i < count($cargaAr); $i++){
//	echo "cargaAr[$i]: '".$cargaAr[$i]."'<br>";
//}
//$msg = "";

		//valida tamanho da linha
		if($msg == ""){
			for($i=0; $i < count($cargaAr); $i++){
				$cargaAr[$i] = trim($cargaAr[$i]);
								
				// tira linha em branco
				if(trim($cargaAr[$i]) == ""){
					array_splice($cargaAr, $i, 1); 
					continue;
				}
/*
 				//Valida tamanho da linha
				$num_posicoes = 34;
				if(strlen($cargaAr[$i]) != $num_posicoes){
					$msg = "linha " . ($i+1) . " do arquivo não tem $num_posicoes posições, possui " . strlen($cargaAr[$i]) . ":\n";
					$msg .= "**" . $cargaAr[$i] . "**\n";
					break;
				}
*/
			}
		}
		
		
		//Inicia transacao
		if($msg == ""){
			$sql = "BEGIN TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao iniciar transação.\n";
		}

		//insere lote
		if($msg == ""){
			
			$seq = 0;
			$valor = 0;
			for($i=0; $i < count($cargaAr); $i++){
	
				$linha 		= $cargaAr[$i];

				//dados				
				if (strpos($linha, "unidades") === false) { // corpo

					$linhaAr 	= explode("\t", $linha);
					$sLote 		= $opr_codigo . date('dm') . "0" . $seq;
				    $sPinCodigo	= $linhaAr[0];
			    	$sValorFace = $valor;

					//Validacoes - "614791 MGspdKFVS5atw9ER 20000"
					if($sLote == "" || !is_numeric($sLote)) 			$msg = "Lote inválido: " . $sLote . ".\n";
					if($sPinCodigo == "" || !ctype_alnum($sPinCodigo)) 	$msg = "Pin Código inválido: " . $sPinCodigo . ".\n"; 
					if($sValorFace == "" || !is_numeric($sValorFace)) 	$msg = "Valor de face inválido: " . $sValorFace . ".\n";

					//insere pin
					if($msg == ""){
						$sql = "insert into pins (opr_codigo, pin_status, pin_serial, pin_codigo, pin_local, pin_valor, pin_lote_codigo, pin_dataentrada, pin_horaentrada, pin_canal) ";
						$sql .= "values ($opr_codigo, 1, '', '$sPinCodigo', '$iPinLocal', '$sValorFace', '$sLote', CURRENT_DATE, CURRENT_TIME, '".$fcanal."')";
//echo "sql: $sql<br>";
						$ret = SQLexecuteQuery($sql);
						if(!$ret) $msg = "Erro ao inserir registro: " . $linha . ".\n";
					}
			
				} else {
					$seq++;
					$valor = "";
					
					$sai = array("-", " ", "unidades");
					$entra = array("", "", "");
					$linhaAr = explode("k", str_replace($sai, $entra, strtolower($linha)));
					$lote  = $opr_codigo . date('dmy') . "0" . $seq;
					$valor = Escola24h_traduzKValor($linhaAr[0]);
					$qtd = $linhaAr[1];

					//Validacoes
					if($lote == "" || !is_numeric($lote)) 	$msg = "Lote inválido: " . $lote . ".\n";
					if($valor == "" || !is_numeric($valor)) $msg = "Valor de face inválido: " . $valor . ".\n";
		
					if($msg == ""){		    
						$sql  = "select count(*) as qtde from pins where opr_codigo = $opr_codigo and pin_lote_codigo = $lote and pin_valor = $valor and pin_canal='s' ";
//echo "sql: $sql";
						$rs = SQLexecuteQuery($sql);
						$ret = true;
						if($rs && pg_num_rows($rs) > 0){
							$rs_row = pg_fetch_array($rs);
							if($rs_row['qtde'] == 0) $ret = false;
						}			
						if($ret) $msg = "Arquivo já foi importado (opr_codigo=$opr_codigo, pin_lote_codigo=$lote, pin_valor=$valor -> val:".$linhaAr[0].", qtd:".$linhaAr[1].")\n"."linha ($i): '$linha'\n";
					}

				} 

				//Se houve erro, sai do loop
				if($msg != "") break;
				
			}
		}


		//Cria diretorios
		if($msg == ""){
			if(!is_dir($folder)) $msg = "Diretório raiz não existe.\n";
			if($msg == ""){
				if(!is_dir($folder. $opr_codigo)) 
					if(!mkdir($folder. $opr_codigo)) $msg = "Não foi possivel criar diretorio da operadora.\n";
			}
		}				

		//move arquivo
		if($msg == ""){
			$fileDest = $folder . $opr_codigo . "/" . $nomeArq; 
			if(!copy($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.\n"; 
			elseif((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo vazio ou inválido.\n";
		}
		
		//Finaliza transacao
		if($msg == ""){
			$sql = "COMMIT TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao comitar transação.\n";
		} else {
			$sql = "ROLLBACK TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
		}

		return $msg;
		
	}

	function processaLote_HabboHotel($fileSource, $nomeArq, $opr_codigo, $loteValor, $fcanal){

		set_time_limit(0);
		global $folder, $opr_codigo_HabboHotel;
		
		$msg = "";
		
		//Valida entradas
		$operadoras = array($opr_codigo_HabboHotel);
		if(is_null($fileSource) || trim($fileSource) == "") $msg = "Caminho do arquivo vázio.\n";
		if(is_null($nomeArq) || trim($nomeArq) == "") $msg = "Nome do arquivo vázio.\n";
		if(is_null($opr_codigo) || trim($opr_codigo) == "" || !is_numeric($opr_codigo) || !in_array($opr_codigo, $operadoras)) $msg = "Código da operadora inválido.\n";
		if(!$loteValor || trim($loteValor) == "" || !is_numeric($loteValor)) $msg = "Valor do pin inválido.\n";
		elseif(!ctype_digit($loteValor)) $msg = "Valor do pin deve ser número inteiro.\n";

		//Abre arquivo e le conteudo
		if($msg == ""){
			$handle = fopen($fileSource, "r");
			$carga = fread($handle, filesize($fileSource));
			fclose($handle);

			$cargaAr = explode("\n", $carga);
			if(count($cargaAr) == 0) $msg = "Arquivo vázio.\n";
		} 

		//valida tamanho da linha
		if($msg == ""){
			for($i=0; $i < count($cargaAr); $i++){
				$cargaAr[$i] = trim($cargaAr[$i]);
								
				// tira linha em branco
				if(trim($cargaAr[$i]) == ""){
					array_splice($cargaAr, $i, 1); 
					continue;
				}
				$num_posicoes = 8;
				if(strlen($cargaAr[$i]) != $num_posicoes){
					$msg = "linha " . ($i+1) . " do arquivo não tem $num_posicoes posições, possui " . strlen($cargaAr[$i]) . ":\n";
					$msg .= "**" . $cargaAr[$i] . "**\n";
					break;
				}
			}
		}

		//arquivo ja importado?
		if($msg == "") {
			
			$existe = false;
			for($i=0; $i < count($cargaAr); $i++){
				$linha 	= $cargaAr[$i];

				$sql  = "select count(*) as qtde from pins where opr_codigo = $opr_codigo and pin_caracter = '$linha' and pin_canal='".$fcanal."' ";
				$rs = SQLexecuteQuery($sql);
				if(!$rs || pg_num_rows($rs) == 0) $msg = "Erro ao pesquisar se pin já existe.\n";
				else {
					$rs_row = pg_fetch_array($rs);
					if($rs_row['qtde'] != 0){
						$msg = "Pin '$linha' já existe";
						$existe = true;
						break;
					}
				}
			}
			if($existe) $msg = "Arquivo já foi importado ($msg).\n";
		}
		
		//pin_lote_codigo
		if($msg == "") {
			$pin_lote_codigo = 0;
			$sql  = "select pin_lote_codigo from pins where opr_codigo = $opr_codigo and pin_canal='".$fcanal."' order by pin_lote_codigo desc limit 1";
			$rs = SQLexecuteQuery($sql);
			if(!$rs) $msg = "Erro ao pesquisar pin_lote_codigo.\n";
			elseif(pg_num_rows($rs) > 0) {
				$rs_row = pg_fetch_array($rs);
				$pin_lote_codigo = $rs_row['pin_lote_codigo'];
			}
			$pin_lote_codigo++;
		}

		//pin_serial
		if($msg == "") {
			$pin_serial = 0;
			$sql  = "select pin_serial from pins where opr_codigo = $opr_codigo and pin_canal='".$fcanal."' order by pin_serial desc limit 1";
			$rs = SQLexecuteQuery($sql);
			if(!$rs) $msg = "Erro ao pesquisar pin_serial.\n";
			elseif(pg_num_rows($rs) > 0) {
				$rs_row = pg_fetch_array($rs);
				$pin_serial = $rs_row['pin_serial'];
			}
			$pin_serial++;
		}

		//Inicia transacao
		$sql = "BEGIN TRANSACTION ";
		$ret = SQLexecuteQuery($sql);
		if(!$ret) $msg = "Erro ao iniciar transação.\n";

		if($msg == ""){

			$iPinLocal = 22;
			$iPinCodigo = "0000000000000000";

			//insere lote
			for($i=0; $i < count($cargaAr); $i++){
	
				$linha 		= $cargaAr[$i];

				$sql = "insert into pins (opr_codigo, pin_status, pin_serial, pin_codigo, pin_local, pin_valor, pin_lote_codigo, pin_dataentrada, pin_horaentrada, pin_caracter, pin_canal) ";
				$sql .= "values ($opr_codigo, 1, '" .$pin_serial++ ."', '$iPinCodigo', $iPinLocal, $loteValor, $pin_lote_codigo, CURRENT_DATE, CURRENT_TIME, '$linha', '".$fcanal."')";
				$ret = SQLexecuteQuery($sql);
				if(!$ret) $msg = "Erro ao inserir registro: " . $linha . ".\n";
					
				//Se houve erro, sai do loop
				if($msg != "") break;
			}
		}
		
		//Cria diretorios
		if($msg == ""){
			if(!is_dir($folder)) $msg = "Diretório raiz não existe.\n";
			if($msg == ""){
				if(!is_dir($folder. $opr_codigo)) 
					if(!mkdir($folder. $opr_codigo)) $msg = "Não foi possivel criar diretorio da operadora.\n";
			}
		}				

		//move arquivo
		if($msg == ""){
			$fileDest = $folder . $opr_codigo . "/" . $nomeArq; 
			if(!copy($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.\n"; 
			elseif((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo vazio ou inválido.\n";
		}
		
		//Finaliza transacao
		if($msg == ""){
			$sql = "COMMIT TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao comitar transação.\n";
		} else {
			$sql = "ROLLBACK TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
		}

		return $msg;
	}

	function processaLote_Mindset($fileSource, $nomeArq, $opr_codigo, $loteValor, $fcanal){

		set_time_limit(0);
		global $folder, $opr_codigo_Mindset;
		
		$msg = "";
		
		//Valida entradas
		$operadoras = array($opr_codigo_Mindset);
		if(is_null($fileSource) || trim($fileSource) == "") $msg = "Caminho do arquivo vázio.\n";
		if(is_null($nomeArq) || trim($nomeArq) == "") $msg = "Nome do arquivo vázio.\n";
		if(is_null($opr_codigo) || trim($opr_codigo) == "" || !is_numeric($opr_codigo) || !in_array($opr_codigo, $operadoras)) $msg = "Código da operadora inválido.\n";
		if(!$loteValor || trim($loteValor) == "" || !is_numeric($loteValor)) $msg = "Valor do pin inválido.\n";
		elseif(!ctype_digit($loteValor)) $msg = "Valor do pin deve ser número inteiro.\n";

		//Abre arquivo e le conteudo
		if($msg == ""){
			$handle = fopen($fileSource, "r");
			$carga = fread($handle, filesize($fileSource));
			fclose($handle);

			$cargaAr = explode("\n", $carga);
			if(count($cargaAr) == 0) $msg = "Arquivo vázio.\n";
		} 

		//valida tamanho da linha
		if($msg == ""){
			for($i=0; $i < count($cargaAr); $i++){
				$cargaAr[$i] = trim($cargaAr[$i]);
								
				// tira linha em branco
				if(trim($cargaAr[$i]) == ""){
					array_splice($cargaAr, $i, 1); 
					continue;
				}
				$num_posicoes = 17;
				if(strlen($cargaAr[$i]) != $num_posicoes){
					$msg = "linha " . ($i+1) . " do arquivo não tem $num_posicoes posições, possui " . strlen($cargaAr[$i]) . ":\n";
					$msg .= "**" . $cargaAr[$i] . "**\n";
					break;
				}
			}
		}

		//arquivo ja importado?
		if($msg == "") {
			
			$existe = false;
			for($i=0; $i < count($cargaAr); $i++){
				$linha 	= $cargaAr[$i];

				$sql  = "select count(*) as qtde from pins where opr_codigo = $opr_codigo and pin_caracter = '$linha' and pin_canal='".$fcanal."' ";
				$rs = SQLexecuteQuery($sql);
				if(!$rs || pg_num_rows($rs) == 0) $msg = "Erro ao pesquisar se pin já existe.\n";
				else {
					$rs_row = pg_fetch_array($rs);
					if($rs_row['qtde'] != 0){
						$msg = "Pin '$linha' já existe";
						$existe = true;
						break;
					}
				}
			}
			if($existe) $msg = "Arquivo já foi importado ($msg).\n";
		}
		
		//pin_lote_codigo
		if($msg == "") {
			$pin_lote_codigo = 0;
			$sql  = "select pin_lote_codigo from pins where opr_codigo = $opr_codigo and pin_canal='".$fcanal."' order by pin_lote_codigo desc limit 1";
			$rs = SQLexecuteQuery($sql);
			if(!$rs) $msg = "Erro ao pesquisar pin_lote_codigo.\n";
			elseif(pg_num_rows($rs) > 0) {
				$rs_row = pg_fetch_array($rs);
				$pin_lote_codigo = $rs_row['pin_lote_codigo'];
			}
			$pin_lote_codigo++;
		}

		//pin_serial
		if($msg == "") {
			$pin_serial = 0;
			$sql  = "select pin_serial from pins where opr_codigo = $opr_codigo and pin_canal='".$fcanal."' order by pin_serial desc limit 1";
			$rs = SQLexecuteQuery($sql);
			if(!$rs) $msg = "Erro ao pesquisar pin_serial.\n";
			elseif(pg_num_rows($rs) > 0) {
				$rs_row = pg_fetch_array($rs);
				$pin_serial = $rs_row['pin_serial'];
			}
			$pin_serial++;
		}

		//Inicia transacao
		$sql = "BEGIN TRANSACTION ";
		$ret = SQLexecuteQuery($sql);
		if(!$ret) $msg = "Erro ao iniciar transação.\n";

		if($msg == ""){

			$iPinLocal = 32;
			$iPincaracter = "";

			//insere lote
			for($i=0; $i < count($cargaAr); $i++){
	
				$linha 		= $cargaAr[$i];

				$sql = "insert into pins (opr_codigo, pin_status, pin_serial, pin_codigo, pin_local, pin_valor, pin_lote_codigo, pin_dataentrada, pin_horaentrada, pin_caracter, pin_canal) ";
				$sql .= "values ($opr_codigo, 1, '" .$pin_serial++ ."', '$linha', $iPinLocal, $loteValor, $pin_lote_codigo, CURRENT_DATE, CURRENT_TIME, '$iPincaracter', '".$fcanal."')";
				$ret = SQLexecuteQuery($sql);
				if(!$ret) $msg = "Erro ao inserir registro: " . $linha . ".\n";
					
				//Se houve erro, sai do loop
				if($msg != "") break;
			}
		}
		
		//Cria diretorios
		if($msg == ""){
			if(!is_dir($folder)) $msg = "Diretório raiz não existe.\n";
			if($msg == ""){
				if(!is_dir($folder. $opr_codigo)) 
					if(!mkdir($folder. $opr_codigo)) $msg = "Não foi possivel criar diretorio da operadora.\n";
			}
		}				

		//move arquivo
		if($msg == ""){
			$fileDest = $folder . $opr_codigo . "/" . $nomeArq; 
			if(!copy($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.\n"; 
			elseif((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo vazio ou inválido.\n";
		}
		
		//Finaliza transacao
		if($msg == ""){
			$sql = "COMMIT TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao comitar transação.\n";
		} else {
			$sql = "ROLLBACK TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
		}

		return $msg;
	}

	function processaLote_Vostu($fileSource, $nomeArq, $opr_codigo, $loteValor, $fcanal){

		set_time_limit(0);
		global $folder, $opr_codigo_Vostu;
		
		$msg = "";
		
		//Valida entradas
		$operadoras = array($opr_codigo_Vostu);
		if(is_null($fileSource) || trim($fileSource) == "") $msg = "Caminho do arquivo vázio.\n";
		if(is_null($nomeArq) || trim($nomeArq) == "") $msg = "Nome do arquivo vázio.\n";
		if(is_null($opr_codigo) || trim($opr_codigo) == "" || !is_numeric($opr_codigo) || !in_array($opr_codigo, $operadoras)) $msg = "Código da operadora inválido.\n";
		if(!$loteValor || trim($loteValor) == "" || !is_numeric($loteValor)) $msg = "Valor do pin inválido.\n";
		elseif(!ctype_digit($loteValor)) $msg = "Valor do pin deve ser número inteiro.\n";

		//Abre arquivo e le conteudo
		if($msg == ""){
			$handle = fopen($fileSource, "r");
			$carga = fread($handle, filesize($fileSource));
			fclose($handle);

			$cargaAr = explode("\n", $carga);
			if(count($cargaAr) == 0) $msg = "Arquivo vázio.\n";
		} 

		//valida tamanho da linha
		if($msg == ""){
			for($i=0; $i < count($cargaAr); $i++){
				$cargaAr[$i] = trim($cargaAr[$i]);
								
				// tira linha em branco
				if(trim($cargaAr[$i]) == ""){
					array_splice($cargaAr, $i, 1); 
					continue;
				}
				$num_posicoes = 17;
				if(strlen($cargaAr[$i]) != $num_posicoes){
					$msg = "linha " . ($i+1) . " do arquivo não tem $num_posicoes posições, possui " . strlen($cargaAr[$i]) . ":\n";
					$msg .= "**" . $cargaAr[$i] . "**\n";
					break;
				}
			}
		}

		//arquivo ja importado?
		if($msg == "") {
			
			$existe = false;
			for($i=0; $i < count($cargaAr); $i++){
				$linha 	= $cargaAr[$i];

				$sql  = "select count(*) as qtde from pins where opr_codigo = $opr_codigo and pin_caracter = '$linha' and pin_canal='s' ";
				$rs = SQLexecuteQuery($sql);
				if(!$rs || pg_num_rows($rs) == 0) $msg = "Erro ao pesquisar se pin já existe.\n";
				else {
					$rs_row = pg_fetch_array($rs);
					if($rs_row['qtde'] != 0){
						$msg = "Pin '$linha' já existe.\n";
						$existe = true;
						break;
					}
				}
			}
			if($existe) $msg = "Arquivo já foi importado.\n";
		}
		
		//pin_lote_codigo
		if($msg == "") {
			$pin_lote_codigo = 0;
			$sql  = "select pin_lote_codigo from pins where opr_codigo = $opr_codigo and pin_canal='s' order by pin_lote_codigo desc limit 1";
			$rs = SQLexecuteQuery($sql);
			if(!$rs) $msg = "Erro ao pesquisar pin_lote_codigo.\n";
			elseif(pg_num_rows($rs) > 0) {
				$rs_row = pg_fetch_array($rs);
				$pin_lote_codigo = $rs_row['pin_lote_codigo'];
			}
			$pin_lote_codigo++;
		}

		//pin_serial
		if($msg == "") {
			$pin_serial = 0;
			$sql  = "select pin_serial from pins where opr_codigo = $opr_codigo and pin_canal='s' order by pin_serial desc limit 1";
			$rs = SQLexecuteQuery($sql);
			if(!$rs) $msg = "Erro ao pesquisar pin_serial.\n";
			elseif(pg_num_rows($rs) > 0) {
				$rs_row = pg_fetch_array($rs);
				$pin_serial = $rs_row['pin_serial'];
			}
			$pin_serial++;
		}

		//Inicia transacao
		$sql = "BEGIN TRANSACTION ";
		$ret = SQLexecuteQuery($sql);
		if(!$ret) $msg = "Erro ao iniciar transação.\n";

		if($msg == ""){

			$iPinLocal = 35;
//			$iPinCodigo = "0000000000000000";

			//insere lote
			for($i=0; $i < count($cargaAr); $i++){
	
				$linha 		= $cargaAr[$i];

				$sql = "insert into pins (opr_codigo, pin_status, pin_serial, pin_codigo, pin_local, pin_valor, pin_lote_codigo, pin_dataentrada, pin_horaentrada, pin_canal) ";
				$sql .= "values ($opr_codigo, 1, '" .$pin_serial++ ."', '$linha', $iPinLocal, $loteValor, $pin_lote_codigo, CURRENT_DATE, CURRENT_TIME, '".$fcanal."')";
				$ret = SQLexecuteQuery($sql);
				if(!$ret) $msg = "Erro ao inserir registro: " . $linha . " ($sql).\n";
					
				//Se houve erro, sai do loop
				if($msg != "") break;
			}
		}
		
		//Cria diretorios
		if($msg == ""){
			if(!is_dir($folder)) $msg = "Diretório raiz não existe.\n";
			if($msg == ""){
				if(!is_dir($folder. $opr_codigo)) 
					if(!mkdir($folder. $opr_codigo)) $msg = "Não foi possivel criar diretorio da operadora.\n";
			}
		}				

		//move arquivo
		if($msg == ""){
			$fileDest = $folder . $opr_codigo . "/" . $nomeArq; 
			if(!copy($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.\n"; 
			elseif((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo vazio ou inválido.\n";
		}
		
		//Finaliza transacao
		if($msg == ""){
			$sql = "COMMIT TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao comitar transação.\n";
		} else {
			$sql = "ROLLBACK TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
		}

		return $msg;
	}



	function processaLote_Brancaleone($fileSource, $nomeArq, $opr_codigo, $loteValor, $fcanal){

		set_time_limit(0);
		global $folder, $opr_codigo_Brancaleone;
		
		$msg = "";
		
		//Valida entradas
		$operadoras = array($opr_codigo_Brancaleone);
		if(is_null($fileSource) || trim($fileSource) == "") $msg = "Caminho do arquivo vázio.\n";
		if(is_null($nomeArq) || trim($nomeArq) == "") $msg = "Nome do arquivo vázio.\n";
		if(is_null($opr_codigo) || trim($opr_codigo) == "" || !is_numeric($opr_codigo) || !in_array($opr_codigo, $operadoras)) $msg = "Código da operadora inválido.\n";
		if(!$loteValor || trim($loteValor) == "") $msg = "Valor do pin inválido.\n";

		//Abre arquivo e le conteudo
		if($msg == ""){
			$handle = fopen($fileSource, "r");
			$carga = fread($handle, filesize($fileSource));
			fclose($handle);

			$cargaAr = explode("\n", $carga);
			if(count($cargaAr) == 0) $msg = "Arquivo vázio.\n";
		} 

		//valida tamanho da linha
		if($msg == ""){
			for($i=0; $i < count($cargaAr); $i++){
				$cargaAr[$i] = trim($cargaAr[$i]);
								
				// tira linha em branco
				if(trim($cargaAr[$i]) == ""){
					array_splice($cargaAr, $i, 1); 
					continue;
				}
				$num_posicoes =10;
				if(strlen($cargaAr[$i]) != $num_posicoes){
					$msg = "linha " . ($i+1) . " do arquivo não tem $num_posicoes posições, possui " . strlen($cargaAr[$i]) . ":\n";
					$msg .= "**" . $cargaAr[$i] . "**\n";
					break;
				}
			}
		}

		//arquivo ja importado?
		if($msg == "") {
			
			$existe = false;
			for($i=0; $i < count($cargaAr); $i++){
				$linha 	= $cargaAr[$i];

				$sql  = "select count(*) as qtde from pins where opr_codigo = $opr_codigo and pin_caracter = '$linha' and pin_canal='s' ";
				$rs = SQLexecuteQuery($sql);
				if(!$rs || pg_num_rows($rs) == 0) $msg = "Erro ao pesquisar se pin já existe.\n";
				else {
					$rs_row = pg_fetch_array($rs);
					if($rs_row['qtde'] != 0){
						$msg = "Pin '$linha' já existe.\n";
						$existe = true;
						break;
					}
				}
			}
			if($existe) $msg = "Arquivo já foi importado.\n";
		}
		
		//pin_lote_codigo
		if($msg == "") {
			$pin_lote_codigo = 0;
			$sql  = "select pin_lote_codigo from pins where opr_codigo = $opr_codigo and pin_canal='s' order by pin_lote_codigo desc limit 1";
			$rs = SQLexecuteQuery($sql);
			if(!$rs) $msg = "Erro ao pesquisar pin_lote_codigo.\n";
			elseif(pg_num_rows($rs) > 0) {
				$rs_row = pg_fetch_array($rs);
				$pin_lote_codigo = $rs_row['pin_lote_codigo'];
			}
			$pin_lote_codigo++;
		}

		//pin_serial
		if($msg == "") {
			$pin_serial = 0;
			$sql  = "select pin_serial from pins where opr_codigo = $opr_codigo and pin_canal='s' order by pin_serial desc limit 1";
			$rs = SQLexecuteQuery($sql);
			if(!$rs) $msg = "Erro ao pesquisar pin_serial.\n";
			elseif(pg_num_rows($rs) > 0) {
				$rs_row = pg_fetch_array($rs);
				$pin_serial = $rs_row['pin_serial'];
			}
			$pin_serial++;
		}

		//Inicia transacao
		$sql = "BEGIN TRANSACTION ";
		$ret = SQLexecuteQuery($sql);
		if(!$ret) $msg = "Erro ao iniciar transação.\n";

		if($msg == ""){

			$iPinLocal = 22;
			$iPinCodigo = "0000000000000000";

			//insere lote
			for($i=0; $i < count($cargaAr); $i++){
	
				$linha 		= $cargaAr[$i];

				$sql = "insert into pins (opr_codigo, pin_status, pin_serial, pin_codigo, pin_local, pin_valor, pin_lote_codigo, pin_dataentrada, pin_horaentrada, pin_caracter, pin_canal) ";
				$sql .= "values ($opr_codigo, 1, '" .$pin_serial++ ."', '$iPinCodigo', $iPinLocal, $loteValor, $pin_lote_codigo, CURRENT_DATE, CURRENT_TIME, '$linha', '".$fcanal."')";
				$ret = SQLexecuteQuery($sql);
				if(!$ret) $msg = "Erro ao inserir registro: " . $linha . ".\n";
					
				//Se houve erro, sai do loop
				if($msg != "") break;
			}
		}
		
		//Cria diretorios
		if($msg == ""){
			if(!is_dir($folder)) $msg = "Diretório raiz não existe.\n";
			if($msg == ""){
				if(!is_dir($folder. $opr_codigo)) 
					if(!mkdir($folder. $opr_codigo)) $msg = "Não foi possivel criar diretorio da operadora.\n";
			}
		}				

		//move arquivo
		if($msg == ""){
			$fileDest = $folder . $opr_codigo . "/" . $nomeArq; 
			if(!copy($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.\n"; 
			elseif((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo vazio ou inválido.\n";
		}
		
		//Finaliza transacao
		if($msg == ""){
			$sql = "COMMIT TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao comitar transação.\n";
		} else {
			$sql = "ROLLBACK TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
		}

		return $msg;
	}


function MUOnline_traduzKValor($k){
	$valor["10"] = "10";
	$valor["16"] = "16";
	
	return $valor[$k];
}

	function processaLote_MUOnline($fileSource, $nomeArq, $opr_codigo, $loteValor, $fcanal){
	
		global $folder, $opr_codigo_MUOnline;
		
		$msg = "";
		
		//Valida entradas
		$operadoras = array($opr_codigo_MUOnline);
		if(is_null($fileSource) || trim($fileSource) == "") $msg = "Caminho do arquivo vázio.\n";
		if(is_null($nomeArq) || trim($nomeArq) == "") $msg = "Nome do arquivo vázio.\n";
		if(is_null($opr_codigo) || trim($opr_codigo) == "" || !is_numeric($opr_codigo) || !in_array($opr_codigo, $operadoras)) $msg = "Código da operadora inválido.\n";

		//local
		$iPinLocal = 26;


		//Abre arquivo e le conteudo
		if($msg == ""){
			$handle = fopen($fileSource, "r");
			$carga = fread($handle, filesize($fileSource));
			fclose($handle);

			$cargaAr = explode("\n", $carga);
			if(count($cargaAr) == 0) $msg = "Arquivo vázio.\n";
		} 

		// Debug
//		for($i=0; $i < count($cargaAr); $i++){
//			echo "cargaAr[$i]: '".$cargaAr[$i]."'<br>";
//		}
//		$msg = "";

		//valida tamanho da linha
		if($msg == ""){
			for($i=0; $i < count($cargaAr); $i++){
				$cargaAr[$i] = trim($cargaAr[$i]);
								
				// tira linha em branco
				if(trim($cargaAr[$i]) == ""){
					array_splice($cargaAr, $i, 1); 
					continue;
				}
/*
 				//Valida tamanho da linha
				$num_posicoes = 34;
				if(strlen($cargaAr[$i]) != $num_posicoes){
					$msg = "linha " . ($i+1) . " do arquivo não tem $num_posicoes posições, possui " . strlen($cargaAr[$i]) . ":\n";
					$msg .= "**" . $cargaAr[$i] . "**\n";
					break;
				}
*/
			}
		}
		
		
		//Inicia transacao
		if($msg == ""){
			$sql = "BEGIN TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao iniciar transação.\n";
		}

		//insere lote
		if($msg == ""){
			
			$seq = 0;
			$valor = 0;
			for($i=0; $i < count($cargaAr); $i++){
	
				$linha 		= $cargaAr[$i];

				//dados				
				if (strpos($linha, "unidades") === false) { // corpo

//echo "[linha: $linha]<br>";
					$linhaAr 	= explode("\t", $linha);
					$sLote 		= $opr_codigo . date('dmy') . "0" . $seq;
					$sPinSerial = $linhaAr[0];
				    $sPinCodigo	= $linhaAr[1];
			    	$sValorFace = $linhaAr[2]; //$valor;
//echo "[sLote: $sLote] [sPinSerial: $sPinSerial] [sPinCodigo: $sPinCodigo] [sValorFace: $sValorFace]<br>";
					//Validacoes
					if($sLote == "" || !is_numeric($sLote)) 			$msg = "Lote inválido: " . $sLote . ".\n";
//					if($sPinSerial == "" || !is_numeric($sPinSerial)) 	$msg = "Pin Serial inválido: " . $sPinSerial . ".\n";
					if($sPinSerial == "" || (strlen($sPinSerial)!=36) || (substr($sPinSerial,8,1)!="-") || (substr($sPinSerial,13,1)!="-") || (substr($sPinSerial,18,1)!="-") || (substr($sPinSerial,23,1)!="-")) 	
						$msg = "Pin Serial inválido: " . $sPinSerial . ".\n";
//					if($sPinCodigo == "" || !is_numeric($sPinCodigo)) 	$msg = "Pin Código inválido: " . $sPinCodigo . ".\n";
					if($sPinCodigo == "" || (strlen($sPinCodigo)!=9) || (substr($sPinCodigo,4,1)!="-")) 	
							$msg = "Pin Código inválido: " . $sPinCodigo . ".\n";
					if($sValorFace == "" || !is_numeric($sValorFace)) 	$msg = "Valor de face inválido: " . $sValorFace . ".\n";

					//insere pin
					if($msg == ""){
						$sql = "insert into pins (opr_codigo, pin_status, pin_serial, pin_codigo, pin_local, pin_valor, pin_lote_codigo, pin_dataentrada, pin_horaentrada, pin_canal) ";
						$sql .= "values ($opr_codigo, 1, '$sPinSerial', '$sPinCodigo', '$iPinLocal', '$sValorFace', '$sLote', CURRENT_DATE, CURRENT_TIME, '".$fcanal."')";
						$ret = SQLexecuteQuery($sql);
						if(!$ret) $msg = "Erro ao inserir registro: " . $linha . ".\n";
					}
			
				} else {
					$seq++;
					$valor = "";
					
					$sai = array("-", " ", "unidades");
					$entra = array("", "", "");
					$linhaAr = explode("k", str_replace($sai, $entra, strtolower($linha)));
					$lote  = $opr_codigo . date('dmy') . "0" . $seq;
					$valor = MUOnline_traduzKValor($linhaAr[0]);
					$qtd = $linhaAr[1];

					//Validacoes
					if($lote == "" || !is_numeric($lote)) 	$msg = "Lote inválido: " . $lote . ".\n";
					if($valor == "" || !is_numeric($valor)) $msg = "Valor de face inválido: " . $valor . ".\n";
		
					if($msg == ""){		    
						$sql  = "select count(*) as qtde from pins where opr_codigo = $opr_codigo and pin_lote_codigo = $lote and pin_valor = $valor and pin_canal='s' ";
						$rs = SQLexecuteQuery($sql);
						$ret = true;
						if($rs && pg_num_rows($rs) > 0){
							$rs_row = pg_fetch_array($rs);
							if($rs_row['qtde'] == 0) $ret = false;
						}			
						if($ret) $msg = "Arquivo já foi importado.\n";
					}

				} 

				//Se houve erro, sai do loop
				if($msg != "") break;
				
			}
		}


		//Cria diretorios
		if($msg == ""){
			if(!is_dir($folder)) $msg = "Diretório raiz não existe.\n";
			if($msg == ""){
				if(!is_dir($folder. $opr_codigo)) 
					if(!mkdir($folder. $opr_codigo)) $msg = "Não foi possivel criar diretorio da operadora.\n";
			}
		}				

		//move arquivo
		if($msg == ""){
			$fileDest = $folder . $opr_codigo . "/" . $nomeArq; 
			if(!copy($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.\n"; 
			elseif((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo vazio ou inválido.\n";
		}
		
		//Finaliza transacao
		if($msg == ""){
			$sql = "COMMIT TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao comitar transação.\n";
		} else {
			$sql = "ROLLBACK TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
		}

		return $msg;
		
	}
	$valor_GPotato["15"] = "15";
	$valor_GPotato["17"] = "17";
	$valor_GPotato["30"] = "30";
	$valor_GPotato["35"] = "35";
	$valor_GPotato["50"] = "50";
	$valor_GPotato["65"] = "65";
	$valor_GPotato["100"] = "100";
	$valor_GPotato["115"] = "115";

function GPotato_traduzKValor($k){
	global $valor_GPotato;
	
	return $valor_GPotato[$k];
}

	function processaLote_GPotato($fileSource, $nomeArq, $opr_codigo, $loteValor, $fcanal){
		global $folder, $opr_codigo_GPotato;
		global $valor_GPotato;
		
		$msg = "";
		
		//Valida entradas
		$operadoras = array($opr_codigo_GPotato);
		if(is_null($fileSource) || trim($fileSource) == "") $msg = "Caminho do arquivo vázio.\n";
		if(is_null($nomeArq) || trim($nomeArq) == "") $msg = "Nome do arquivo vázio.\n";
		if(is_null($opr_codigo) || trim($opr_codigo) == "" || !is_numeric($opr_codigo) || !in_array($opr_codigo, $operadoras)) $msg = "Código da operadora inválido.\n";

		//local
		$iPinLocal = 41;


		//Abre arquivo e le conteudo
		if($msg == ""){
			$handle = fopen($fileSource, "r");
			$carga = fread($handle, filesize($fileSource));
			fclose($handle);

			$cargaAr = explode("\n", $carga);
			if(count($cargaAr) == 0) $msg = "Arquivo vázio.\n";
		} 

		// Debug
//		for($i=0; $i < count($cargaAr); $i++){
//			echo "cargaAr[$i]: '".$cargaAr[$i]."'<br>";
//		}
//		$msg = "";

		//valida tamanho da linha
		if($msg == ""){
			for($i=0; $i < count($cargaAr); $i++){
				$cargaAr[$i] = trim($cargaAr[$i]);
								
				// tira linha em branco
				if(trim($cargaAr[$i]) == ""){
					array_splice($cargaAr, $i, 1); 
					continue;
				}
/*
 				//Valida tamanho da linha
				$num_posicoes = 34;
				if(strlen($cargaAr[$i]) != $num_posicoes){
					$msg = "linha " . ($i+1) . " do arquivo não tem $num_posicoes posições, possui " . strlen($cargaAr[$i]) . ":\n";
					$msg .= "**" . $cargaAr[$i] . "**\n";
					break;
				}
*/
			}
		}
		
		
		//Inicia transacao
		if($msg == ""){
			$sql = "BEGIN TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao iniciar transação.\n";
		}

		//insere lote
		if($msg == ""){
			
			$seq = 0;
			$valor = 0;
			for($i=0; $i < count($cargaAr); $i++){
	
				$linha 		= $cargaAr[$i];

				//dados				
				if (strpos($linha, "unidades") === false) { // corpo

//echo "[linha: $linha]<br>";
					$linhaAr 	= explode("\t", $linha);
					$sLote 		= $opr_codigo . date('dmy') . "0" . $seq;
					$sPinSerial = $linhaAr[0];
				    $sPinCodigo	= $linhaAr[1];
			    	$sValorFace = $linhaAr[2]; //$valor;
//echo "[sLote: $sLote] [sPinSerial: $sPinSerial] [sPinCodigo: $sPinCodigo] [sValorFace: $sValorFace]<br>";
					//Validacoes
					if($sLote == "" || !is_numeric($sLote)) 			$msg = "Lote inválido: " . $sLote . ".\n";
//					if($sPinSerial == "" || !is_numeric($sPinSerial)) 	$msg = "Pin Serial inválido: " . $sPinSerial . ".\n";
					if($sPinSerial == "" || (strlen($sPinSerial)!=14) || (substr($sPinSerial,4,1)!="-") || (substr($sPinSerial,9,1)!="-") ) 	
						$msg = "Pin Serial inválido: " . $sPinSerial . ".\n";
//					if($sPinCodigo == "" || !is_numeric($sPinCodigo)) 	$msg = "Pin Código inválido: " . $sPinCodigo . ".\n";
					if($sPinCodigo == "" || (strlen($sPinCodigo)!=12) ) 	
							$msg = "Pin Código inválido: " . $sPinCodigo . ".\n";
					if($sValorFace == "" || !is_numeric($sValorFace) || (!in_array($sValorFace, $valor_GPotato)) ) 	$msg = "Valor de face inválido: " . $sValorFace . ".\n";

					//procura pin
					if($msg == ""){
						$sql = "select count(*) as n from pins where opr_codigo=$opr_codigo and pin_serial='$sPinSerial' and pin_codigo='$sPinCodigo' and pin_valor='$sValorFace' and pin_canal='s' ";
//echo "SQL: $sql<br>";
						$ret = SQLexecuteQuery($sql);
						if(!$ret) 
							$msg = "Erro ao procurar pin (opr_codigo=$opr_codigo, pin_serial='$sPinSerial', pin_codigo='$sPinCodigo').\n";
						else {
							$ret_row = pg_fetch_array($ret);
							$n = $ret_row['n'];
//echo "N: $n<br>";
							if($n>0) {
								$msg = "Erro ao inserir pin: Já existe (opr_codigo=$opr_codigo, pin_serial='$sPinSerial', pin_codigo='$sPinCodigo', pin_valor='$sValorFace').\n";
							}
						}
					}

					//insere pin
					if($msg == ""){
						$sql = "insert into pins (opr_codigo, pin_status, pin_serial, pin_codigo, pin_local, pin_valor, pin_lote_codigo, pin_dataentrada, pin_horaentrada, pin_canal) ";
						$sql .= "values ($opr_codigo, 1, '$sPinSerial', '$sPinCodigo', '$iPinLocal', '$sValorFace', '$sLote', CURRENT_DATE, CURRENT_TIME, '".$fcanal."')";
//echo "SQL: $sql<br>";
						$ret = SQLexecuteQuery($sql);
						if(!$ret) $msg = "Erro ao inserir registro: " . $linha . ".\n";
					}
			
				} else {
					$seq++;
					$valor = "";
					
					$sai = array("-", " ", "unidades");
					$entra = array("", "", "");
					$linhaAr = explode("k", str_replace($sai, $entra, strtolower($linha)));
					$lote  = $opr_codigo . date('dmy') . "0" . $seq;
					$valor = GPotato_traduzKValor($linhaAr[0]);
					$qtd = $linhaAr[1];

					//Validacoes
					if($lote == "" || !is_numeric($lote)) 	$msg = "Lote inválido: " . $lote . ".\n";
					if($valor == "" || !is_numeric($valor)) $msg = "Valor de face inválido: " . $valor . ".\n";
		
					if($msg == ""){		    
						$sql  = "select count(*) as qtde from pins where opr_codigo = $opr_codigo and pin_lote_codigo = $lote and pin_valor = $valor and pin_canal='s' ";
						$rs = SQLexecuteQuery($sql);
						$ret = true;
						if($rs && pg_num_rows($rs) > 0){
							$rs_row = pg_fetch_array($rs);
							if($rs_row['qtde'] == 0) $ret = false;
						}			
						if($ret) $msg = "Arquivo já foi importado.\n";
					}

				} 

				//Se houve erro, sai do loop
				if($msg != "") break;
				
			}
		}


		//Cria diretorios
		if($msg == ""){
			if(!is_dir($folder)) $msg = "Diretório raiz não existe.\n";
			if($msg == ""){
				if(!is_dir($folder. $opr_codigo)) 
					if(!mkdir($folder. $opr_codigo)) $msg = "Não foi possivel criar diretorio da operadora.\n";
			}
		}				

		//move arquivo
		if($msg == ""){
			$fileDest = $folder . $opr_codigo . "/" . $nomeArq; 
			if(!copy($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.\n"; 
			elseif((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo vazio ou inválido.\n";
		}
		
		//Finaliza transacao
		if($msg == ""){
			$sql = "COMMIT TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao comitar transação.\n";
		} else {
			$sql = "ROLLBACK TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
		}

		return $msg;
		
	}

	$valor_FHLGames["12"] = "12";
	$valor_FHLGames["25"] = "25";
	$valor_FHLGames["50"] = "50";
	$valor_FHLGames["100"] = "100";

function FHLGames_traduzKValor($k){
	global $valor_FHLGames;
	
	return $valor_FHLGames[$k];
}

	function processaLote_FHLGames($fileSource, $nomeArq, $opr_codigo, $loteValor, $fcanal){
		global $folder, $opr_codigo_FHLGames;
		global $valor_FHLGames;
		
		$msg = "";
		
		//Valida entradas
		$operadoras = array($opr_codigo_FHLGames);
		if(is_null($fileSource) || trim($fileSource) == "") $msg = "Caminho do arquivo vázio.\n";
		if(is_null($nomeArq) || trim($nomeArq) == "") $msg = "Nome do arquivo vázio.\n";
		if(is_null($opr_codigo) || trim($opr_codigo) == "" || !is_numeric($opr_codigo) || !in_array($opr_codigo, $operadoras)) $msg = "Código da operadora inválido.\n";

		//local
		$iPinLocal = 49;


		//Abre arquivo e le conteudo
		if($msg == ""){
			$handle = fopen($fileSource, "r");
			$carga = fread($handle, filesize($fileSource));
			fclose($handle);

			$cargaAr = explode("\n", $carga);
			if(count($cargaAr) == 0) $msg = "Arquivo vázio.\n";
		} 

		// Debug
//		for($i=0; $i < count($cargaAr); $i++){
//			echo "cargaAr[$i]: '".$cargaAr[$i]."'<br>";
//		}
//		$msg = "";

		//valida tamanho da linha
		if($msg == ""){
			for($i=0; $i < count($cargaAr); $i++){
				$cargaAr[$i] = trim($cargaAr[$i]);
								
				// tira linha em branco
				if(trim($cargaAr[$i]) == ""){
					array_splice($cargaAr, $i, 1); 
					continue;
				}
/*
 				//Valida tamanho da linha
				$num_posicoes = 34;
				if(strlen($cargaAr[$i]) != $num_posicoes){
					$msg = "linha " . ($i+1) . " do arquivo não tem $num_posicoes posições, possui " . strlen($cargaAr[$i]) . ":\n";
					$msg .= "**" . $cargaAr[$i] . "**\n";
					break;
				}
*/
			}
		}
		
		
		//Inicia transacao
		if($msg == ""){
			$sql = "BEGIN TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao iniciar transação.\n";
		}

		//insere lote
		if($msg == ""){
			
			$seq = 0;
			$valor = 0;
			for($i=0; $i < count($cargaAr); $i++){
	
				$linha 		= $cargaAr[$i];

				//dados				
				if (strpos($linha, "unidades") === false) { // corpo

//echo "[linha: $linha]<br>";
					$linhaAr 	= explode("\t", $linha);
					$sLote 		= $opr_codigo . date('dmy') . "0" . $seq;
					$sPinSerial = $linhaAr[0];
				    $sPinCodigo	= $linhaAr[1];
			    	$sValorFace = $linhaAr[2]; //$valor;
//echo "[sLote: $sLote] [sPinSerial: $sPinSerial] [sPinCodigo: $sPinCodigo] [sValorFace: $sValorFace]<br>";
					//Validacoes
					if($sLote == "" || !is_numeric($sLote)) 			$msg = "Lote inválido: " . $sLote . ".\n";
//					if($sPinSerial == "" || !is_numeric($sPinSerial)) 	$msg = "Pin Serial inválido: " . $sPinSerial . ".\n";
					if($sPinSerial == "" || (strlen($sPinSerial)!=10)) 	
						$msg = "Pin Serial inválido: " . $sPinSerial . ".\n";
//					if($sPinCodigo == "" || !is_numeric($sPinCodigo)) 	$msg = "Pin Código inválido: " . $sPinCodigo . ".\n";
					if($sPinCodigo == "" || (strlen($sPinCodigo)!=14) || (substr($sPinCodigo,4,1)!="-") || (substr($sPinCodigo,9,1)!="-")  ) 	
							$msg = "Pin Código inválido: " . $sPinCodigo . ".\n";
					if($sValorFace == "" || !is_numeric($sValorFace) || (!in_array($sValorFace, $valor_FHLGames)) ) 	$msg = "Valor de face inválido: " . $sValorFace . ".\n";

					//procura pin
					if($msg == ""){
						$sql = "select count(*) as n from pins where opr_codigo=$opr_codigo and pin_serial='$sPinSerial' and pin_codigo='$sPinCodigo' and pin_valor='$sValorFace' and pin_canal='s' ";
//echo "SQL: $sql<br>";
						$ret = SQLexecuteQuery($sql);
						if(!$ret) 
							$msg = "Erro ao procurar pin (opr_codigo=$opr_codigo, pin_serial='$sPinSerial', pin_codigo='$sPinCodigo').\n";
						else {
							$ret_row = pg_fetch_array($ret);
							$n = $ret_row['n'];
//echo "N: $n<br>";
							if($n>0) {
								$msg = "Erro ao inserir pin: Já existe (opr_codigo=$opr_codigo, pin_serial='$sPinSerial', pin_codigo='$sPinCodigo', pin_valor='$sValorFace').\n";
							}
						}
					}

					//insere pin
					if($msg == ""){
						$sql = "insert into pins (opr_codigo, pin_status, pin_serial, pin_codigo, pin_local, pin_valor, pin_lote_codigo, pin_dataentrada, pin_horaentrada, pin_canal) ";
						$sql .= "values ($opr_codigo, 1, '$sPinSerial', '$sPinCodigo', '$iPinLocal', '$sValorFace', '$sLote', CURRENT_DATE, CURRENT_TIME, '".$fcanal."')";
//echo "SQL: $sql<br>";
						$ret = SQLexecuteQuery($sql);
						if(!$ret) $msg = "Erro ao inserir registro: " . $linha . ".\n";
					}
			
				} else {
					$seq++;
					$valor = "";
					
					$sai = array("-", " ", "unidades");
					$entra = array("", "", "");
					$linhaAr = explode("k", str_replace($sai, $entra, strtolower($linha)));
					$lote  = $opr_codigo . date('dmy') . "0" . $seq;
					$valor = FHLGames_traduzKValor($linhaAr[0]);
					$qtd = $linhaAr[1];

					//Validacoes
					if($lote == "" || !is_numeric($lote)) 	$msg = "Lote inválido: " . $lote . ".\n";
					if($valor == "" || !is_numeric($valor)) $msg = "Valor de face inválido: " . $valor . ".\n";
		
					if($msg == ""){		    
						$sql  = "select count(*) as qtde from pins where opr_codigo = $opr_codigo and pin_lote_codigo = $lote and pin_valor = $valor and pin_canal='s' ";
						$rs = SQLexecuteQuery($sql);
						$ret = true;
						if($rs && pg_num_rows($rs) > 0){
							$rs_row = pg_fetch_array($rs);
							if($rs_row['qtde'] == 0) $ret = false;
						}			
						if($ret) $msg = "Arquivo já foi importado.\n";
					}

				} 

				//Se houve erro, sai do loop
				if($msg != "") break;
				
			}
		}


		//Cria diretorios
		if($msg == ""){
			if(!is_dir($folder)) $msg = "Diretório raiz não existe.\n";
			if($msg == ""){
				if(!is_dir($folder. $opr_codigo)) 
					if(!mkdir($folder. $opr_codigo)) $msg = "Não foi possivel criar diretorio da operadora.\n";
			}
		}				

		//move arquivo
		if($msg == ""){
			$fileDest = $folder . $opr_codigo . "/" . $nomeArq; 
			if(!copy($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.\n"; 
			elseif((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo vazio ou inválido.\n";
		}
		
		//Finaliza transacao
		if($msg == ""){
			$sql = "COMMIT TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao comitar transação.\n";
		} else {
			$sql = "ROLLBACK TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
		}

		return $msg;
		
	}

function YNKinteractive_traduzKValor($k){
	$valor["18"] = "18";
	$valor["36"] = "36";
	$valor["72"] = "72";
	$valor["180"] = "180";
	$valor["360"] = "360";
	
	return $valor[$k];
}

	function processaLote_YNKinteractive($fileSource, $nomeArq, $opr_codigo, $fcanal){
	
		global $folder, $opr_codigo_YNKinteractive;
		
		$msg = "";
		
		//Valida entradas
		$operadoras = array($opr_codigo_YNKinteractive);
		if(is_null($fileSource) || trim($fileSource) == "") $msg = "Caminho do arquivo vázio.\n";
		if(is_null($nomeArq) || trim($nomeArq) == "") $msg = "Nome do arquivo vázio.\n";
		if(is_null($opr_codigo) || trim($opr_codigo) == "" || !is_numeric($opr_codigo) || !in_array($opr_codigo, $operadoras)) $msg = "Código da operadora inválido.\n";

		//local
		$iPinLocal = 19;


		//Abre arquivo e le conteudo
		if($msg == ""){
			$handle = fopen($fileSource, "r");
			$carga = fread($handle, filesize($fileSource));
			fclose($handle);

			$cargaAr = explode("\n", $carga);
			if(count($cargaAr) == 0) $msg = "Arquivo vázio.\n";
		} 

// Debug
//for($i=0; $i < count($cargaAr); $i++){
//	echo "cargaAr[$i]: '".$cargaAr[$i]."'<br>";
//}
//$msg = "";

		//valida tamanho da linha
		if($msg == ""){
			for($i=0; $i < count($cargaAr); $i++){
				$cargaAr[$i] = trim($cargaAr[$i]);
								
				// tira linha em branco
				if(trim($cargaAr[$i]) == ""){
					array_splice($cargaAr, $i, 1); 
					continue;
				}
/*
 				//Valida tamanho da linha
				$num_posicoes = 34;
				if(strlen($cargaAr[$i]) != $num_posicoes){
					$msg = "linha " . ($i+1) . " do arquivo não tem $num_posicoes posições, possui " . strlen($cargaAr[$i]) . ":\n";
					$msg .= "**" . $cargaAr[$i] . "**\n";
					break;
				}
*/
			}
		}
		
		
		//Inicia transacao
		if($msg == ""){
			$sql = "BEGIN TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao iniciar transação.\n";
		}

		//insere lote
		if($msg == ""){
			
			$seq = 0;
			$valor = 0;
			for($i=0; $i < count($cargaAr); $i++){
	
				$linha 		= $cargaAr[$i];

				//dados				
				if (strpos($linha, "unidades") === false) { // corpo

					$linhaAr 	= explode("\t", $linha);
					$sLote 		= $opr_codigo . date('dmy') . "0" . $seq;
					$sPinSerial = $linhaAr[0];
				    $sPinCodigo	= $linhaAr[1];
			    	$sValorFace = $linhaAr[2];

					//Validacoes - "614791 MGspdKFVS5atw9ER 20000"
					if($sLote == "" || !is_numeric($sLote)) 			$msg = "Lote inválido: " . $sLote . ".\n";
					if($sPinSerial == "") 	$msg = "Pin Serial inválido: " . $sPinSerial . ".\n";
//					if($sPinCodigo == "" || !is_numeric($sPinCodigo)) 	$msg = "Pin Código inválido: " . $sPinCodigo . ".\n"; // Código antigo: só números
					if($sPinCodigo == "") 	$msg = "Pin Código inválido: " . $sPinCodigo . ".\n";
					if($sValorFace == "" || !is_numeric($sValorFace)) 	$msg = "Valor de face inválido: " . $sValorFace . ".\n";

					//insere pin
					if($msg == ""){
						$sql = "insert into pins (opr_codigo, pin_status, pin_serial, pin_codigo, pin_local, pin_valor, pin_lote_codigo, pin_dataentrada, pin_horaentrada, pin_canal) ";
						$sql .= "values ($opr_codigo, 1, '$sPinSerial', '$sPinCodigo', '$iPinLocal', '$sValorFace', '$sLote', CURRENT_DATE, CURRENT_TIME, '".$fcanal."')";

						$ret = SQLexecuteQuery($sql);
						if(!$ret) $msg = "Erro ao inserir registro: " . $linha . ".\n";
					}
			
				} else {
					$seq++;
					$valor = "";
					
					$sai = array("-", " ", "unidades");
					$entra = array("", "", "");
					$linhaAr = explode("k", str_replace($sai, $entra, strtolower($linha)));
					$lote  = $opr_codigo . date('dmy') . "0" . $seq;
					$valor = YNKinteractive_traduzKValor($linhaAr[0]);
					$qtd = $linhaAr[1];

					//Validacoes
					if($lote == "" || !is_numeric($lote)) 	$msg = "Lote inválido: " . $lote . ".\n";
					if($valor == "" || !is_numeric($valor)) $msg = "Valor de face inválido: " . $valor . ".\n";
		
					if($msg == ""){		    
						$sql  = "select count(*) as qtde from pins where opr_codigo = $opr_codigo and pin_lote_codigo = $lote and pin_valor = $valor and pin_canal='s' ";
//echo "sql: $sql";
						$rs = SQLexecuteQuery($sql);
						$ret = true;
						if($rs && pg_num_rows($rs) > 0){
							$rs_row = pg_fetch_array($rs);
							if($rs_row['qtde'] == 0) $ret = false;
						}			
						if($ret) $msg = "Arquivo já foi importado (opr_codigo=$opr_codigo, pin_lote_codigo=$lote, pin_valor=$valor -> val:".$linhaAr[0].", qtd:".$linhaAr[1].")\n"."linha ($i): '$linha'\n";
					}

				} 

				//Se houve erro, sai do loop
				if($msg != "") break;
				
			}
		}


		//Cria diretorios
		if($msg == ""){
			if(!is_dir($folder)) $msg = "Diretório raiz não existe.\n";
			if($msg == ""){
				if(!is_dir($folder. $opr_codigo)) 
					if(!mkdir($folder. $opr_codigo)) $msg = "Não foi possivel criar diretorio da operadora.\n";
			}
		}				

		//move arquivo
		if($msg == ""){
			$fileDest = $folder . $opr_codigo . "/" . $nomeArq; 
			if(!copy($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.\n"; 
			elseif((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo vazio ou inválido.\n";
		}
		
		//Finaliza transacao
		if($msg == ""){
			$sql = "COMMIT TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao comitar transação.\n";
		} else {
			$sql = "ROLLBACK TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
		}

		return $msg;
		
	}



function PayByCash_traduzKValor($k){
	$valor["33"] = "33";
	$valor["66"] = "66";
	
	return $valor[$k];
}

	function processaLote_PayByCash($fileSource, $nomeArq, $opr_codigo, $loteValor, $fcanal){
	
		global $folder, $opr_codigo_PayByCash;
		
		$msg = "";
		
		//Valida entradas
		$operadoras = array($opr_codigo_PayByCash);
		if(is_null($fileSource) || trim($fileSource) == "") $msg = "Caminho do arquivo vázio.\n";
		if(is_null($nomeArq) || trim($nomeArq) == "") $msg = "Nome do arquivo vázio.\n";
		if(is_null($opr_codigo) || trim($opr_codigo) == "" || !is_numeric($opr_codigo) || !in_array($opr_codigo, $operadoras)) $msg = "Código da operadora inválido.\n";

		//local
		$iPinLocal = 26;


		//Abre arquivo e le conteudo
		if($msg == ""){
			$handle = fopen($fileSource, "r");
			$carga = fread($handle, filesize($fileSource));
			fclose($handle);

			$cargaAr = explode("\n", $carga);
			if(count($cargaAr) == 0) $msg = "Arquivo vázio.\n";
		} 

		// Debug
//		for($i=0; $i < count($cargaAr); $i++){
//			echo "cargaAr[$i]: '".$cargaAr[$i]."'<br>";
//		}
//		$msg = "";

		//valida tamanho da linha
		if($msg == ""){
			for($i=0; $i < count($cargaAr); $i++){
				$cargaAr[$i] = trim($cargaAr[$i]);
								
				// tira linha em branco
				if(trim($cargaAr[$i]) == ""){
					array_splice($cargaAr, $i, 1); 
					continue;
				}
/*
 				//Valida tamanho da linha
				$num_posicoes = 34;
				if(strlen($cargaAr[$i]) != $num_posicoes){
					$msg = "linha " . ($i+1) . " do arquivo não tem $num_posicoes posições, possui " . strlen($cargaAr[$i]) . ":\n";
					$msg .= "**" . $cargaAr[$i] . "**\n";
					break;
				}
*/
			}
		}
		
		
		//Inicia transacao
		if($msg == ""){
			$sql = "BEGIN TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao iniciar transação.\n";
		}

		//insere lote
		if($msg == ""){
			
			$seq = 0;
			$valor = 0;
			for($i=0; $i < count($cargaAr); $i++){
	
				$linha 		= $cargaAr[$i];

				//dados				
				if (strpos($linha, "unidades") === false) { // corpo

//echo "[linha: $linha]<br>";
					$linhaAr 	= explode("\t", $linha);
					$sLote 		= $opr_codigo . date('dm') . $seq;	//	Too big for pin_lote_codigo (integer) in DB: $opr_codigo.date('dmy')."0".$seq;
					$sPinSerial = $linhaAr[0];
			    	$sValorFace = $linhaAr[1]; //$valor;
//echo "[sLote: $sLote] [sPinSerial: $sPinSerial] [sValorFace: $sValorFace]<br>";
					//Validacoes
					if($sLote == "" || !is_numeric($sLote)) 			$msg = "Lote inválido: " . $sLote . ".\n";
//					if($sPinSerial == "" || !is_numeric($sPinSerial)) 	$msg = "Pin Serial inválido: " . $sPinSerial . ".\n";
					// 012345678901234567890123
					// FIUK-AYCZ-YHXR-EZAF-OEPE
					if($sPinSerial == "" || (strlen($sPinSerial)!=24)) 	
						$msg = "Pin Serial comprimento inválido: " . $sPinSerial . ".\n";
					if((substr($sPinSerial,4,1)!="-") || (substr($sPinSerial,9,1)!="-") || (substr($sPinSerial,14,1)!="-") || (substr($sPinSerial,19,1)!="-")) 
						$msg = "Pin Serial inválido (5x4): " . $sPinSerial . ".\n";
					if($sValorFace == "" || !is_numeric($sValorFace)) 	$msg = "Valor de face inválido: " . $sValorFace . ".\n";

					//insere pin
					if($msg == ""){
						$sql = "insert into pins (opr_codigo, pin_status, pin_serial, pin_codigo, pin_local, pin_valor, pin_lote_codigo, pin_dataentrada, pin_horaentrada, pin_canal) ";
						$sql .= "values ($opr_codigo, 1, '$sPinSerial', '', '$iPinLocal', '$sValorFace', '$sLote', CURRENT_DATE, CURRENT_TIME, '".$fcanal."')";
//echo $sql."<br>";
						$ret = SQLexecuteQuery($sql);
						if(!$ret) $msg = "Erro ao inserir registro: " . $linha . ".\n";
					}
			
				} else {
					$seq++;
					$valor = "";
					
					$sai = array("-", " ", "unidades");
					$entra = array("", "", "");
					$linhaAr = explode("k", str_replace($sai, $entra, strtolower($linha)));
					$lote  = $opr_codigo . date('dmy') . "0" . $seq;
					$valor = PayByCash_traduzKValor($linhaAr[0]);
					$qtd = $linhaAr[1];

					//Validacoes
					if($lote == "" || !is_numeric($lote)) 	$msg = "Lote inválido: " . $lote . ".\n";
					if($valor == "" || !is_numeric($valor)) $msg = "Valor de face inválido: " . $valor . ".\n";
		
					if($msg == ""){		    
						$sql  = "select count(*) as qtde from pins where opr_codigo = $opr_codigo and pin_lote_codigo = $lote and pin_valor = $valor and pin_canal='s' ";
						$rs = SQLexecuteQuery($sql);
						$ret = true;
						if($rs && pg_num_rows($rs) > 0){
							$rs_row = pg_fetch_array($rs);
							if($rs_row['qtde'] == 0) $ret = false;
						}			
						if($ret) $msg = "Arquivo já foi importado.\n";
					}

				} 

				//Se houve erro, sai do loop
				if($msg != "") break;
				
			}
		}

		//Cria diretorios
		if($msg == ""){
			if(!is_dir($folder)) $msg = "Diretório raiz não existe.\n";
			if($msg == ""){
				if(!is_dir($folder. $opr_codigo)) 
					if(!mkdir($folder. $opr_codigo)) $msg = "Não foi possivel criar diretorio da operadora.\n";
			}
		}				

		//move arquivo
		if($msg == ""){
			$fileDest = $folder . $opr_codigo . "/" . $nomeArq; 
			if(!copy($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.\n"; 
			elseif((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo vazio ou inválido.\n";
		}
		
		//Finaliza transacao
		if($msg == ""){
			$sql = "COMMIT TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao comitar transação.\n";
		} else {
			$sql = "ROLLBACK TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
		}

		return $msg;
		
	}

function Webzen_traduzKValor($k){
	$valor["10"] = "10";
	$valor["20"] = "20";
	$valor["30"] = "30";
	$valor["40"] = "40";
	$valor["60"] = "60";
	$valor["80"] = "80";
	$valor["100"] = "100";
	$valor["200"] = "200";
	
	return $valor[$k];
}

	function processaLote_Webzen($fileSource, $nomeArq, $opr_codigo, $loteValor, $fcanal){
	
		global $folder, $opr_codigo_Webzen, $opr_codigo_Webzen_Packs, $opr_codigo_Webzen_2, $opr_codigo_Webzen_3;
		
		$msg = "";
		
		//Valida entradas
		$operadoras = array($opr_codigo_Webzen, $opr_codigo_Webzen_Packs, $opr_codigo_Webzen_2, $opr_codigo_Webzen_3);
		if(is_null($fileSource) || trim($fileSource) == "") $msg = "Caminho do arquivo vázio.\n";
		if(is_null($nomeArq) || trim($nomeArq) == "") $msg = "Nome do arquivo vázio.\n";
		if(is_null($opr_codigo) || trim($opr_codigo) == "" || !is_numeric($opr_codigo) || !in_array($opr_codigo, $operadoras)) $msg = "Código da operadora inválido.\n";

		//local
		$iPinLocal = 26;


		//Abre arquivo e le conteudo
		if($msg == ""){
			$handle = fopen($fileSource, "r");
			$carga = fread($handle, filesize($fileSource));
			fclose($handle);

			$cargaAr = explode("\n", $carga);
			if(count($cargaAr) == 0) $msg = "Arquivo vázio.\n";
		} 

		// Debug
//		for($i=0; $i < count($cargaAr); $i++){
//			echo "cargaAr[$i]: '".$cargaAr[$i]."'<br>";
//		}
//		$msg = "";

		//valida tamanho da linha
		if($msg == ""){
			for($i=0; $i < count($cargaAr); $i++){
				$cargaAr[$i] = trim($cargaAr[$i]);
								
				// tira linha em branco
				if(trim($cargaAr[$i]) == ""){
					array_splice($cargaAr, $i, 1); 
					continue;
				}
/*
 				//Valida tamanho da linha
				$num_posicoes = 34;
				if(strlen($cargaAr[$i]) != $num_posicoes){
					$msg = "linha " . ($i+1) . " do arquivo não tem $num_posicoes posições, possui " . strlen($cargaAr[$i]) . ":\n";
					$msg .= "**" . $cargaAr[$i] . "**\n";
					break;
				}
*/
			}
		}
		
		
		//Inicia transacao
		if($msg == ""){
			$sql = "BEGIN TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao iniciar transação.\n";
		}

		//insere lote
		if($msg == ""){
			
			$seq = 0;
			$valor = 0;
			for($i=0; $i < count($cargaAr); $i++){
	
				$linha 		= $cargaAr[$i];

				//dados				
				if (strpos($linha, "unidades") === false) { // corpo

//echo "[linha: $linha]<br>";
					$linhaAr 	= explode("\t", $linha);
					$sLote 		= $opr_codigo . date('dm') . $seq;	//	Too big for pin_lote_codigo (integer) in DB: $opr_codigo.date('dmy')."0".$seq;
					$sPinSerial = $linhaAr[0];
			    	$sValorFace = $linhaAr[1]; //$valor;
//echo "[sLote: $sLote] [sPinSerial: $sPinSerial] [sValorFace: $sValorFace]<br>";
					//Validacoes
					if($sLote == "" || !is_numeric($sLote)) 			$msg = "Lote inválido: " . $sLote . ".\n";
//					if($sPinSerial == "" || !is_numeric($sPinSerial)) 	$msg = "Pin Serial inválido: " . $sPinSerial . ".\n";
					// 01234567890123456789012345678
					// AAWEC-KALMP-Y21HR-CX7AC-4YVBD
                                        // Alterado por Wagner em 05/07/2013para o novo formato conforme instrução da Tamlyn
                                        // Formato antigo: xxxxx-xxxxx-xxxxx-xxxxx-xxxxx (25 dígitos alfanuméricos separados por traços)
                                        // Formato novo: xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx (32 dígitos alfanuméricos sem traços)

					if($sPinSerial == "" || (strlen($sPinSerial)!=16)) 	
						$msg = "Pin Serial comprimento inválido: " . $sPinSerial . ".\n";
					//if((substr($sPinSerial,5,1)!="-") || (substr($sPinSerial,11,1)!="-") || (substr($sPinSerial,17,1)!="-") || (substr($sPinSerial,23,1)!="-")) 
					//	$msg = "Pin Serial inválido (5x5): " . $sPinSerial . ".\n";
					if($sValorFace == "" || !is_numeric($sValorFace)) 	$msg = "Valor de face inválido: " . $sValorFace . ".\n";

					//insere pin
					if($msg == ""){
						$sql = "insert into pins (opr_codigo, pin_status, pin_serial, pin_codigo, pin_local, pin_valor, pin_lote_codigo, pin_dataentrada, pin_horaentrada, pin_canal) ";
						$sql .= "values ($opr_codigo, 1, '$sPinSerial', '', '$iPinLocal', '$sValorFace', '$sLote', CURRENT_DATE, CURRENT_TIME, '".$fcanal."')";
//echo $sql."<br>";
						$ret = SQLexecuteQuery($sql);
						if(!$ret) $msg = "Erro ao inserir registro: " . $linha . ".\n";
					}
			
				} else {
					$seq++;
					$valor = "";
					
					$sai = array("-", " ", "unidades");
					$entra = array("", "", "");
					$linhaAr = explode("k", str_replace($sai, $entra, strtolower($linha)));
					$lote  = $opr_codigo . date('dmy') . "0" . $seq;
					$valor = Webzen_traduzKValor($linhaAr[0]);
					$qtd = $linhaAr[1];

					//Validacoes
					if($lote == "" || !is_numeric($lote)) 	$msg = "Lote inválido: " . $lote . ".\n";
					if($valor == "" || !is_numeric($valor)) $msg = "Valor de face inválido: " . $valor . ".\n";
		
					if($msg == ""){		    
						$sql  = "select count(*) as qtde from pins where opr_codigo = $opr_codigo and pin_lote_codigo = $lote and pin_valor = $valor and pin_canal='s' ";
						$rs = SQLexecuteQuery($sql);
						$ret = true;
						if($rs && pg_num_rows($rs) > 0){
							$rs_row = pg_fetch_array($rs);
							if($rs_row['qtde'] == 0) $ret = false;
						}			
						if($ret) $msg = "Arquivo já foi importado.\n";
					}

				} 

				//Se houve erro, sai do loop
				if($msg != "") break;
				
			}
		}

		//Cria diretorios
		if($msg == ""){
			if(!is_dir($folder)) $msg = "Diretório raiz não existe.\n";
			if($msg == ""){
				if(!is_dir($folder. $opr_codigo)) 
					if(!mkdir($folder. $opr_codigo)) $msg = "Não foi possivel criar diretorio da operadora.\n";
			}
		}				

		//move arquivo
		if($msg == ""){
			$fileDest = $folder . $opr_codigo . "/" . $nomeArq; 
			if(!copy($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.\n"; 
			elseif((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo vazio ou inválido.\n";
		}
		
		//Finaliza transacao
		if($msg == ""){
			$sql = "COMMIT TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao comitar transação.\n";
		} else {
			$sql = "ROLLBACK TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
		}

		return $msg;
		
	}

function Coolnex_traduzKValor($k){
	$valor["5"] = "5";
	$valor["10"] = "10";
	$valor["15"] = "15";
	$valor["25"] = "25";
	$valor["50"] = "50";
	
	return $valor[$k];
}

	function processaLote_Coolnex($fileSource, $nomeArq, $opr_codigo, $loteValor, $fcanal){
	
		global $folder, $opr_codigo_Coolnex;
		
		$msg = "";
		
		//Valida entradas
		$operadoras = array($opr_codigo_Coolnex);
		if(is_null($fileSource) || trim($fileSource) == "") $msg = "Caminho do arquivo vázio.\n";
		if(is_null($nomeArq) || trim($nomeArq) == "") $msg = "Nome do arquivo vázio.\n";
		if(is_null($opr_codigo) || trim($opr_codigo) == "" || !is_numeric($opr_codigo) || !in_array($opr_codigo, $operadoras)) $msg = "Código da operadora inválido.\n";

		//local
		$iPinLocal = 30;


		//Abre arquivo e le conteudo
		if($msg == ""){
			$handle = fopen($fileSource, "r");
			$carga = fread($handle, filesize($fileSource));
			fclose($handle);

			$cargaAr = explode("\n", $carga);
			if(count($cargaAr) == 0) $msg = "Arquivo vázio.\n";
		} 

		// Debug
//		for($i=0; $i < count($cargaAr); $i++){
//			echo "cargaAr[$i]: '".$cargaAr[$i]."'<br>";
//		}
//		$msg = "";
		//valida tamanho da linha
		if($msg == ""){
			for($i=0; $i < count($cargaAr); $i++){
				$cargaAr[$i] = trim($cargaAr[$i]);
								
				// tira linha em branco
				if(trim($cargaAr[$i]) == ""){
					array_splice($cargaAr, $i, 1); 
					continue;
				}
/*
 				//Valida tamanho da linha
				$num_posicoes = 34;
				if(strlen($cargaAr[$i]) != $num_posicoes){
					$msg = "linha " . ($i+1) . " do arquivo não tem $num_posicoes posições, possui " . strlen($cargaAr[$i]) . ":\n";
					$msg .= "**" . $cargaAr[$i] . "**\n";
					break;
				}
*/
			}
		}
		
		
		//Inicia transacao
		if($msg == ""){
			$sql = "BEGIN TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao iniciar transação.\n";
		}

		//insere lote
		if($msg == ""){
			
			$seq = 0;
			$valor = 0;
			for($i=0; $i < count($cargaAr); $i++){
	
				$linha 		= $cargaAr[$i];

				//dados				
				if (strpos($linha, "unidades") === false) { // corpo

//echo "[linha: $linha]<br>";
					$linhaAr 	= explode("\t", $linha);
					$sLote 		= $opr_codigo . date('dm') . $seq;	//	Too big for pin_lote_codigo (integer) in DB: $opr_codigo.date('dmy')."0".$seq;
					$sPinSerial = $linhaAr[0];
			    	$sValorFace = $linhaAr[1]; //$valor;
//echo "[sLote: $sLote] [sPinSerial: $sPinSerial] [sValorFace: $sValorFace]<br>";

					//Validacoes
					$msg = "";
					if($sLote == "" || !is_numeric($sLote)) 			$msg .= "Lote inválido: " . $sLote . ".\n";
//					if($sPinSerial == "" || !is_numeric($sPinSerial)) 	$msg .= "Pin Serial inválido: " . $sPinSerial . ".\n";
					// 0123456789012345678
					// vwb0 d4a8 d61d 420f
					if($sPinSerial == "" || (strlen($sPinSerial)!=19)) 	
						$msg .= "Pin Serial comprimento inválido: " . $sPinSerial . ".\n";
					$blocks = explode(" ", $sPinSerial);
					if(count($blocks)!=4) 	
						$msg .= "Pin Serial estrutura inválida (4x4): " . $sPinSerial . ".\n";
					for($j=0;$j<count($blocks);$j++) {
						if(!ctype_alnum($blocks[$j])) 
							$msg .= "Pin Serial estrutura inválida (4 bocos alfanuméricos): " . $sPinSerial . " -> ".$blocks[$j].".\n";
					}
					if($sValorFace == "" || !is_numeric($sValorFace)) 	$msg .= "Valor de face inválido: " . $sValorFace . ".\n";

					//insere pin
					if($msg == ""){
						$sql = "insert into pins (opr_codigo, pin_status, pin_serial, pin_codigo, pin_local, pin_valor, pin_lote_codigo, pin_dataentrada, pin_horaentrada, pin_canal) ";
						$sql .= "values ($opr_codigo, 1, '$sPinSerial', '', '$iPinLocal', '$sValorFace', '$sLote', CURRENT_DATE, CURRENT_TIME, '".$fcanal."')";
//echo $sql."<br>";
						$ret = SQLexecuteQuery($sql);
						if(!$ret) $msg = "Erro ao inserir registro: " . $linha . ".\n";
					}
			
				} else {
					$seq++;
					$valor = "";
					
					$sai = array("-", " ", "unidades");
					$entra = array("", "", "");
					$linhaAr = explode("k", str_replace($sai, $entra, strtolower($linha)));
					$lote  = $opr_codigo . date('dmy') . "0" . $seq;
					$valor = Coolnex_traduzKValor($linhaAr[0]);
					$qtd = $linhaAr[1];
//echo "valor: $valor qtd: $qtd<br>";
					//Validacoes
					if($lote == "" || !is_numeric($lote)) 	$msg = "Lote inválido: " . $lote . ".\n";
					if($valor == "" || !is_numeric($valor)) $msg = "Valor de face inválido: " . $valor . ".\n";
		
					if($msg == ""){		    
						$sql  = "select count(*) as qtde from pins where opr_codigo = $opr_codigo and pin_lote_codigo = $lote and pin_valor = $valor and pin_canal='s' ";
						$rs = SQLexecuteQuery($sql);
						$ret = true;
						if($rs && pg_num_rows($rs) > 0){
							$rs_row = pg_fetch_array($rs);
							if($rs_row['qtde'] == 0) $ret = false;
						}			
						if($ret) $msg = "Arquivo já foi importado.\n";
					}

				} 

				//Se houve erro, sai do loop
				if($msg != "") {
//echo "MSG:".$msg."<br>";
					break;
				}
				
			}
		}

		//Cria diretorios
		if($msg == ""){
			if(!is_dir($folder)) $msg = "Diretório raiz não existe.\n";
			if($msg == ""){
				if(!is_dir($folder. $opr_codigo)) 
					if(!mkdir($folder. $opr_codigo)) $msg = "Não foi possivel criar diretorio da operadora.\n";
			}
		}				

		//move arquivo
		if($msg == ""){
			$fileDest = $folder . $opr_codigo . "/" . $nomeArq; 
			if(!copy($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.\n"; 
			elseif((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo vazio ou inválido.\n";
		}
		
		//Finaliza transacao
		if($msg == ""){
			$sql = "COMMIT TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao comitar transação.\n";
		} else {
			$sql = "ROLLBACK TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
		}

		return $msg;
		
	}


//  ========================================================

function KOL_traduzKValor($k){
	$valor["10"] = "10";
	$valor["20"] = "20";
	$valor["30"] = "30";
	$valor["40"] = "40";
	$valor["50"] = "50";
	$valor["60"] = "60";
	$valor["80"] = "80";
	
	return $valor[$k];
}

	function processaLote_KOL($fileSource, $nomeArq, $opr_codigo, $loteValor, $fcanal){
	
		global $folder, $opr_codigo_KOL;
		
		$msg = "";
		
		//Valida entradas
		$operadoras = array($opr_codigo_KOL);
		if(is_null($fileSource) || trim($fileSource) == "") $msg = "Caminho do arquivo vázio.\n";
		if(is_null($nomeArq) || trim($nomeArq) == "") $msg = "Nome do arquivo vázio.\n";
		if(is_null($opr_codigo) || trim($opr_codigo) == "" || !is_numeric($opr_codigo) || !in_array($opr_codigo, $operadoras)) $msg = "Código da operadora inválido.\n";

		//local
		$iPinLocal = 26;


		//Abre arquivo e le conteudo
		if($msg == ""){
			$handle = fopen($fileSource, "r");
			$carga = fread($handle, filesize($fileSource));
			fclose($handle);

			$cargaAr = explode("\n", $carga);
			if(count($cargaAr) == 0) $msg = "Arquivo vázio.\n";
		} 

		// Debug
//		for($i=0; $i < count($cargaAr); $i++){
//			echo "cargaAr[$i]: '".$cargaAr[$i]."'<br>";
//		}
//		$msg = "";

		//valida tamanho da linha
		if($msg == ""){
			for($i=0; $i < count($cargaAr); $i++){
				$cargaAr[$i] = trim($cargaAr[$i]);
								
				// tira linha em branco
				if(trim($cargaAr[$i]) == ""){
					array_splice($cargaAr, $i, 1); 
					continue;
				}
/*
 				//Valida tamanho da linha
				$num_posicoes = 34;
				if(strlen($cargaAr[$i]) != $num_posicoes){
					$msg = "linha " . ($i+1) . " do arquivo não tem $num_posicoes posições, possui " . strlen($cargaAr[$i]) . ":\n";
					$msg .= "**" . $cargaAr[$i] . "**\n";
					break;
				}
*/
			}
		}
		
		
		//Inicia transacao
		if($msg == ""){
			$sql = "BEGIN TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao iniciar transação.\n";
		}

		//insere lote
		if($msg == ""){
			
			$seq = 0;
			$valor = 0;
			for($i=0; $i < count($cargaAr); $i++){
	
				$linha 		= $cargaAr[$i];

				//dados				
				if (strpos($linha, "unidades") === false) { // corpo

//echo "[linha: $linha]<br>";
//			00000000 0111 1111 1222 2222 2333 3333
//			01234567-9012-4567-9012-4567-9012-4567
//	serial: 90304089-gpuv-3jgz-1ns5-b4pm-mlpf-jozi
//	senha:  ismo-iyj5

					$linhaAr 	= explode("\t", $linha);
					$sLote 		= $opr_codigo . date('dmy') . "0" . $seq;
					$sPinSerial = $linhaAr[0];
				    $sPinCodigo	= $linhaAr[1];
			    	$sValorFace = $linhaAr[2]; //$valor;
//echo "[sLote: $sLote] [sPinSerial: $sPinSerial] [sPinCodigo: $sPinCodigo] [sValorFace: $sValorFace]<br>";
					//Validacoes
					if($sLote == "" || !is_numeric($sLote)) 			$msg = "Lote inválido: " . $sLote . ".\n";
//					if($sPinSerial == "" || !is_numeric($sPinSerial)) 	$msg = "Pin Serial inválido: " . $sPinSerial . ".\n";
//					if($sPinSerial == "" || (strlen($sPinSerial)!=38) || (substr($sPinSerial,8,1)!="-") || (substr($sPinSerial,13,1)!="-") || (substr($sPinSerial,18,1)!="-") || (substr($sPinSerial,23,1)!="-") || (substr($sPinSerial,28,1)!="-") || (substr($sPinSerial,33,1)!="-")) 	
					$pinlen = strlen($sPinSerial)-1;
//echo "pinlen: $pinlen<br>";
//echo "-4: ".substr($sPinSerial,$pinlen-4,1).", -9: ".substr($sPinSerial,$pinlen-9,1).", -14: ".substr($sPinSerial,$pinlen-14,1).", -19: ".substr($sPinSerial,$pinlen-19,1).", -24: ".substr($sPinSerial,$pinlen-24,1).", -29: ".substr($sPinSerial,$pinlen-29,1)."<br>";
					if($sPinSerial == "" || (!(strlen($sPinSerial)==38 || strlen($sPinSerial)==39)) || (substr($sPinSerial,$pinlen-4,1)!="-") || (substr($sPinSerial,$pinlen-9,1)!="-") || (substr($sPinSerial,$pinlen-14,1)!="-") || (substr($sPinSerial,$pinlen-19,1)!="-") || (substr($sPinSerial,$pinlen-24,1)!="-") || (substr($sPinSerial,$pinlen-29,1)!="-")) 	
						$msg = "Pin Serial inválido: " . $sPinSerial . ".\n";
//					if($sPinCodigo == "" || !is_numeric($sPinCodigo)) 	$msg = "Pin Código inválido: " . $sPinCodigo . ".\n";
					if($sPinCodigo == "" || (strlen($sPinCodigo)!=9) || (substr($sPinCodigo,4,1)!="-")) 	
							$msg = "Pin Código inválido: " . $sPinCodigo . ".\n";
					if($sValorFace == "" || !is_numeric($sValorFace)) 	$msg = "Valor de face inválido: " . $sValorFace . ".\n";

					//insere pin
					if($msg == ""){
						$sql = "insert into pins (opr_codigo, pin_status, pin_serial, pin_codigo, pin_local, pin_valor, pin_lote_codigo, pin_dataentrada, pin_horaentrada, pin_canal) ";
						$sql .= "values ($opr_codigo, 1, '$sPinSerial', '$sPinCodigo', '$iPinLocal', '$sValorFace', '$sLote', CURRENT_DATE, CURRENT_TIME, '".$fcanal."')";
						$ret = SQLexecuteQuery($sql);
						if(!$ret) $msg = "Erro ao inserir registro: " . $linha . ".\n";
					}
			
				} else {
					$seq++;
					$valor = "";
					
					$sai = array("-", " ", "unidades");
					$entra = array("", "", "");
					$linhaAr = explode("k", str_replace($sai, $entra, strtolower($linha)));
					$lote  = $opr_codigo . date('dmy') . "0" . $seq;
					$valor = KOL_traduzKValor($linhaAr[0]);
					$qtd = $linhaAr[1];

					//Validacoes
					if($lote == "" || !is_numeric($lote)) 	$msg = "Lote inválido: " . $lote . ".\n";
					if($valor == "" || !is_numeric($valor)) $msg = "Valor de face inválido: " . $valor . ".\n";
		
					if($msg == ""){		    
						$sql  = "select count(*) as qtde from pins where opr_codigo = $opr_codigo and pin_lote_codigo = $lote and pin_valor = $valor and pin_canal='s' ";
						$rs = SQLexecuteQuery($sql);
						$ret = true;
						if($rs && pg_num_rows($rs) > 0){
							$rs_row = pg_fetch_array($rs);
							if($rs_row['qtde'] == 0) $ret = false;
						}			
						if($ret) $msg = "Arquivo já foi importado.\n";
					}

				} 

				//Se houve erro, sai do loop
				if($msg != "") break;
				
			}
		}


		//Cria diretorios
		if($msg == ""){
			if(!is_dir($folder)) $msg = "Diretório raiz não existe.\n";
			if($msg == ""){
				if(!is_dir($folder. $opr_codigo)) 
					if(!mkdir($folder. $opr_codigo)) $msg = "Não foi possivel criar diretorio da operadora.\n";
			}
		}				

		//move arquivo
		if($msg == ""){
			$fileDest = $folder . $opr_codigo . "/" . $nomeArq; 
			if(!copy($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.\n"; 
			elseif((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo vazio ou inválido.\n";
		}
		
		//Finaliza transacao
		if($msg == ""){
			$sql = "COMMIT TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao comitar transação.\n";
		} else {
			$sql = "ROLLBACK TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
		}

		return $msg;
		
	}


//  ========================================================
function Acclaim_traduzKValor($k){
	$valor["535"] = "10";
	$valor["1070"] = "20";
	$valor["1605"] = "30";
	$valor["2675"] = "50";
	$valor["5345"] = "100";

	return $valor[$k];
}

	function processaLote_Acclaim($fileSource, $nomeArq, $opr_codigo, $loteValor, $fcanal){
	
		global $folder, $opr_codigo_Acclaim;
		
		$msg = "";
		
		//Valida entradas
		$operadoras = array($opr_codigo_Acclaim);
		if(is_null($fileSource) || trim($fileSource) == "") $msg = "Caminho do arquivo vázio.\n";
		if(is_null($nomeArq) || trim($nomeArq) == "") $msg = "Nome do arquivo vázio.\n";
		if(is_null($opr_codigo) || trim($opr_codigo) == "" || !is_numeric($opr_codigo) || !in_array($opr_codigo, $operadoras)) $msg = "Código da operadora inválido.\n";

		//local
		$iPinLocal = 30;


		//Abre arquivo e le conteudo
		if($msg == ""){
			$handle = fopen($fileSource, "r");
			$carga = fread($handle, filesize($fileSource));
			fclose($handle);

			$cargaAr = explode("\n", $carga);
			if(count($cargaAr) == 0) $msg = "Arquivo vázio.\n";
		} 

		// Debug
//		for($i=0; $i < count($cargaAr); $i++){
//			echo "cargaAr[$i]: '".$cargaAr[$i]."'<br>";
//		}
//		$msg = "";

		//valida tamanho da linha
		if($msg == ""){
			for($i=0; $i < count($cargaAr); $i++){
				$cargaAr[$i] = trim($cargaAr[$i]);
								
				// tira linha em branco
				if(trim($cargaAr[$i]) == ""){
					array_splice($cargaAr, $i, 1); 
					continue;
				}
/*
 				//Valida tamanho da linha
				$num_posicoes = 34;
				if(strlen($cargaAr[$i]) != $num_posicoes){
					$msg = "linha " . ($i+1) . " do arquivo não tem $num_posicoes posições, possui " . strlen($cargaAr[$i]) . ":\n";
					$msg .= "**" . $cargaAr[$i] . "**\n";
					break;
				}
*/
			}
		}
		
		
		//Inicia transacao
		if($msg == ""){
			$sql = "BEGIN TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao iniciar transação.\n";
		}

		//insere lote
		if($msg == ""){
			
			$seq = 0;
			$valor = 0;
			for($i=0; $i < count($cargaAr); $i++){
	
				$linha 		= $cargaAr[$i];

				//dados				
				if (strpos($linha, "unidades") === false) { // corpo

					$linhaAr 	= explode("\t", $linha);
					$sLote 		= $opr_codigo . date('dmy') . "0" . $seq;
					$sPinSerial = trim($linhaAr[0]);
				    $sPinCodigo	= trim($linhaAr[1]);
			    	$sValorFace = $linhaAr[2]; //$valor;

//echo "[sLote: '$sLote'] [sPinSerial: '$sPinSerial'] [sPinCodigo: '$sPinCodigo'] [sValorFace: '$sValorFace']<br>";

					//Validacoes
					if($sLote == "" || !is_numeric($sLote)) 			$msg = "Lote inválido: " . $sLote . ".\n";
//					if($sPinSerial == "" || !is_numeric($sPinSerial)) 	$msg = "Pin Serial inválido: " . $sPinSerial . ".\n";
					if($sPinSerial == "" || (strlen($sPinSerial)!=14)) 	
						$msg = "Pin Serial inválido: " . $sPinSerial . ".\n";
//					if($sPinCodigo == "" || !is_numeric($sPinCodigo)) 	$msg = "Pin Código inválido: " . $sPinCodigo . ".\n";
//					echo "sPinSerial: ";
//					if(!is_numeric($sPinSerial))  	
//						echo "[Not is_numeric] ";
//					if(strlen($sPinSerial)!=14) 	
//						echo " [Not 14 chars:'']";
//					echo "<br>";

//echo "IsEmpty: ".(($sPinCodigo == "")?"Yes":"No")."<br>";  
//echo "IsNumeric: ".(is_numeric($sPinCodigo)?"Yes":"No")."<br>"; 
//echo "Len(14): ".((strlen($sPinCodigo)==14)?"Yes":"No")."<br>";

					if($sPinCodigo == "" || !is_numeric($sPinCodigo) || (strlen($sPinCodigo)!=14)) 	
						$msg = "Pin Código inválido: " . $sPinCodigo . ".\n";
					if($sValorFace == "" || !is_numeric($sValorFace)) 	$msg = "Valor de face inválido: " . $sValorFace . ".\n";

					//insere pin
					if($msg == ""){
						$sql = "insert into pins (opr_codigo, pin_status, pin_serial, pin_codigo, pin_local, pin_valor, pin_lote_codigo, pin_dataentrada, pin_horaentrada, pin_canal) ";
						$sql .= "values ($opr_codigo, 1, '$sPinSerial', '$sPinCodigo', '$iPinLocal', '$sValorFace', '$sLote', CURRENT_DATE, CURRENT_TIME, '".$fcanal."')";
						$ret = SQLexecuteQuery($sql);
						if(!$ret) $msg = "Erro ao inserir registro: " . $linha . ".\n";
					}
			
				} else {
					$seq++;
					$valor = "";
					
					$sai = array("-", " ", "unidades");
					$entra = array("", "", "");
					$linhaAr = explode("k", str_replace($sai, $entra, strtolower($linha)));
					$lote  = $opr_codigo . date('dmy') . "0" . $seq;
					$valor = Acclaim_traduzKValor($linhaAr[0]);
					$qtd = $linhaAr[1];

					//Validacoes
					if($lote == "" || !is_numeric($lote)) 	$msg = "Lote inválido: " . $lote . ".\n";
					if($valor == "" || !is_numeric($valor)) $msg = "Valor de face inválido: " . $valor . ".\n";
		
					if($msg == ""){		    
						$sql  = "select count(*) as qtde from pins where opr_codigo = $opr_codigo and pin_lote_codigo = $lote and pin_valor = $valor and pin_canal='s' ";
						$rs = SQLexecuteQuery($sql);
						$ret = true;
						if($rs && pg_num_rows($rs) > 0){
							$rs_row = pg_fetch_array($rs);
							if($rs_row['qtde'] == 0) $ret = false;
						}			
						if($ret) $msg = "Arquivo já foi importado.\n";
					}

				} 

				//Se houve erro, sai do loop
				if($msg != "") break;
				
			}
		}


		//Cria diretorios
		if($msg == ""){
			if(!is_dir($folder)) $msg = "Diretório raiz não existe.\n";
			if($msg == ""){
				if(!is_dir($folder. $opr_codigo)) 
					if(!mkdir($folder. $opr_codigo)) $msg = "Não foi possivel criar diretorio da operadora.\n";
			}
		}				

		//move arquivo
		if($msg == ""){
			$fileDest = $folder . $opr_codigo . "/" . $nomeArq; 
			if(!copy($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.\n"; 
			elseif((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo vazio ou inválido.\n";
		}
		
		//Finaliza transacao
		if($msg == ""){
			$sql = "COMMIT TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao comitar transação.\n";
		} else {
			$sql = "ROLLBACK TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
		}

		return $msg;
		
	}

//  ========================================================
function NDoors_traduzKValor($k){
	$valor["535"] = "10";
	$valor["1070"] = "20";
	$valor["1605"] = "30";
	$valor["2675"] = "50";
	$valor["5345"] = "100";

	return $valor[$k];
}

	function processaLote_NDoors($fileSource, $nomeArq, $opr_codigo, $loteValor, $fcanal){
	
		global $folder, $opr_codigo_NDoors;
		
		$msg = "";
		
		//Valida entradas
		$operadoras = array($opr_codigo_NDoors);
		if(is_null($fileSource) || trim($fileSource) == "") $msg = "Caminho do arquivo vázio.\n";
		if(is_null($nomeArq) || trim($nomeArq) == "") $msg = "Nome do arquivo vázio.\n";
		if(is_null($opr_codigo) || trim($opr_codigo) == "" || !is_numeric($opr_codigo) || !in_array($opr_codigo, $operadoras)) $msg = "Código da operadora inválido.\n";

		//local
		$iPinLocal = 30;


		//Abre arquivo e le conteudo
		if($msg == ""){
			$handle = fopen($fileSource, "r");
			$carga = fread($handle, filesize($fileSource));
			fclose($handle);

			$cargaAr = explode("\n", $carga);
			if(count($cargaAr) == 0) $msg = "Arquivo vázio.\n";
		} 

		// Debug
//		for($i=0; $i < count($cargaAr); $i++){
//			echo "cargaAr[$i]: '".$cargaAr[$i]."'<br>";
//		}
//		$msg = "";

		//valida tamanho da linha
		if($msg == ""){
			for($i=0; $i < count($cargaAr); $i++){
				$cargaAr[$i] = trim($cargaAr[$i]);
								
				// tira linha em branco
				if(trim($cargaAr[$i]) == ""){
					array_splice($cargaAr, $i, 1); 
					continue;
				}
/*
 				//Valida tamanho da linha
				$num_posicoes = 34;
				if(strlen($cargaAr[$i]) != $num_posicoes){
					$msg = "linha " . ($i+1) . " do arquivo não tem $num_posicoes posições, possui " . strlen($cargaAr[$i]) . ":\n";
					$msg .= "**" . $cargaAr[$i] . "**\n";
					break;
				}
*/
			}
		}
		
		
		//Inicia transacao
		if($msg == ""){
			$sql = "BEGIN TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao iniciar transação.\n";
		}

		//insere lote
		if($msg == ""){
			
			$seq = 0;
			$valor = 0;
			for($i=0; $i < count($cargaAr); $i++){
	
				$linha 		= $cargaAr[$i];

				//dados				
				if (strpos($linha, "unidades") === false) { // corpo

					$linhaAr 	= explode("\t", $linha);
					$sLote 		= $opr_codigo . date('dmy') . "0" . $seq;
					$sPinSerial = trim($linhaAr[0]);
				    $sPinCodigo	= trim($linhaAr[1]);
			    	$sValorFace = $linhaAr[2]; //$valor;

//echo "[sLote: '$sLote'] [sPinSerial: '$sPinSerial'] [sPinCodigo: '$sPinCodigo'] [sValorFace: '$sValorFace']<br>";

					//Validacoes
					if($sLote == "" || !is_numeric($sLote)) 			$msg = "Lote inválido: " . $sLote . ".\n";
//					if($sPinSerial == "" || !is_numeric($sPinSerial)) 	$msg = "Pin Serial inválido: " . $sPinSerial . ".\n";
					if($sPinSerial == "" || (strlen($sPinSerial)!=14)) 	
						$msg = "Pin Serial inválido: " . $sPinSerial . ".\n";
//					if($sPinCodigo == "" || !is_numeric($sPinCodigo)) 	$msg = "Pin Código inválido: " . $sPinCodigo . ".\n";
//					echo "sPinSerial: ";
//					if(!is_numeric($sPinSerial))  	
//						echo "[Not is_numeric] ";
//					if(strlen($sPinSerial)!=14) 	
//						echo " [Not 14 chars:'']";
//					echo "<br>";

//echo "IsEmpty: ".(($sPinCodigo == "")?"Yes":"No")."<br>";  
//echo "IsNumeric: ".(is_numeric($sPinCodigo)?"Yes":"No")."<br>"; 
//echo "Len(14): ".((strlen($sPinCodigo)==14)?"Yes":"No")."<br>";

					if($sPinCodigo == "" || (strlen($sPinCodigo)!=12)) 	
						$msg = "Pin Código inválido: " . $sPinCodigo . ".\n";
					if($sValorFace == "" || !is_numeric($sValorFace)) 	$msg = "Valor de face inválido: " . $sValorFace . ".\n";

					//insere pin
					if($msg == ""){
						$sql = "insert into pins (opr_codigo, pin_status, pin_serial, pin_codigo, pin_local, pin_valor, pin_lote_codigo, pin_dataentrada, pin_horaentrada, pin_canal) ";
						$sql .= "values ($opr_codigo, 1, '$sPinSerial', '$sPinCodigo', '$iPinLocal', '$sValorFace', '$sLote', CURRENT_DATE, CURRENT_TIME, '".$fcanal."')";
						$ret = SQLexecuteQuery($sql);
						if(!$ret) $msg = "Erro ao inserir registro: " . $linha . ".\n";
					}
			
				} else {
					$seq++;
					$valor = "";
					
					$sai = array("-", " ", "unidades");
					$entra = array("", "", "");
					$linhaAr = explode("k", str_replace($sai, $entra, strtolower($linha)));
					$lote  = $opr_codigo . date('dmy') . "0" . $seq;
					$valor = NDoors_traduzKValor($linhaAr[0]);
					$qtd = $linhaAr[1];

					//Validacoes
					if($lote == "" || !is_numeric($lote)) 	$msg = "Lote inválido: " . $lote . ".\n";
					if($valor == "" || !is_numeric($valor)) $msg = "Valor de face inválido: " . $valor . ".\n";
		
					if($msg == ""){		    
						$sql  = "select count(*) as qtde from pins where opr_codigo = $opr_codigo and pin_lote_codigo = $lote and pin_valor = $valor and pin_canal='s' ";
						$rs = SQLexecuteQuery($sql);
						$ret = true;
						if($rs && pg_num_rows($rs) > 0){
							$rs_row = pg_fetch_array($rs);
							if($rs_row['qtde'] == 0) $ret = false;
						}			
						if($ret) $msg = "Arquivo já foi importado.\n";
					}

				} 

				//Se houve erro, sai do loop
				if($msg != "") break;
				
			}
		}


		//Cria diretorios
		if($msg == ""){
			if(!is_dir($folder)) $msg = "Diretório raiz não existe.\n";
			if($msg == ""){
				if(!is_dir($folder. $opr_codigo)) 
					if(!mkdir($folder. $opr_codigo)) $msg = "Não foi possivel criar diretorio da operadora.\n";
			}
		}				

		//move arquivo
		if($msg == ""){
			$fileDest = $folder . $opr_codigo . "/" . $nomeArq; 
			if(!copy($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.\n"; 
			elseif((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo vazio ou inválido.\n";
		}
		
		//Finaliza transacao
		if($msg == ""){
			$sql = "COMMIT TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao comitar transação.\n";
		} else {
			$sql = "ROLLBACK TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
		}

		return $msg;
		
	}

//  ========================================================
function Ignitedgames_traduzKValor($k){
	$valor["535"] = "10";
	$valor["1070"] = "20";
	$valor["1605"] = "30";
	$valor["2675"] = "50";
	$valor["5345"] = "100";

	return $valor[$k];
}

	function processaLote_Ignitedgames($fileSource, $nomeArq, $opr_codigo, $loteValor, $fcanal){
	
		global $folder, $opr_codigo_Ignitedgames;
		
		$msg = "";
		
		//Valida entradas
		$operadoras = array($opr_codigo_Ignitedgames);
		if(is_null($fileSource) || trim($fileSource) == "") $msg = "Caminho do arquivo vázio.\n";
		if(is_null($nomeArq) || trim($nomeArq) == "") $msg = "Nome do arquivo vázio.\n";
		if(is_null($opr_codigo) || trim($opr_codigo) == "" || !is_numeric($opr_codigo) || !in_array($opr_codigo, $operadoras)) $msg = "Código da operadora inválido.\n";

		//local
		$iPinLocal = 30;


		//Abre arquivo e le conteudo
		if($msg == ""){
			$handle = fopen($fileSource, "r");
			$carga = fread($handle, filesize($fileSource));
			fclose($handle);

			$cargaAr = explode("\n", $carga);
			if(count($cargaAr) == 0) $msg = "Arquivo vázio.\n";
		} 

		// Debug
//		for($i=0; $i < count($cargaAr); $i++){
//			echo "cargaAr[$i]: '".$cargaAr[$i]."'<br>";
//		}
//		$msg = "";

		//valida tamanho da linha
		if($msg == ""){
			for($i=0; $i < count($cargaAr); $i++){
				$cargaAr[$i] = trim($cargaAr[$i]);
								
				// tira linha em branco
				if(trim($cargaAr[$i]) == ""){
					array_splice($cargaAr, $i, 1); 
					continue;
				}
/*
 				//Valida tamanho da linha
				$num_posicoes = 34;
				if(strlen($cargaAr[$i]) != $num_posicoes){
					$msg = "linha " . ($i+1) . " do arquivo não tem $num_posicoes posições, possui " . strlen($cargaAr[$i]) . ":\n";
					$msg .= "**" . $cargaAr[$i] . "**\n";
					break;
				}
*/
			}
		}
		
		
		//Inicia transacao
		if($msg == ""){
			$sql = "BEGIN TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao iniciar transação.\n";
		}

		//insere lote
		if($msg == ""){
			
			$seq = 0;
			$valor = 0;
			for($i=0; $i < count($cargaAr); $i++){
	
				$linha 		= $cargaAr[$i];

				//dados				
				if (strpos($linha, "unidades") === false) { // corpo

					$linhaAr 	= explode("\t", $linha);
					$sLote 		= $opr_codigo . date('dmy') . "0" . $seq;
					$sPinSerial = trim($linhaAr[0]);
				    $sPinCodigo	= trim($linhaAr[1]);
			    	$sValorFace = $linhaAr[2]; //$valor;

//echo "[sLote: '$sLote'] [sPinSerial: '$sPinSerial'] [sPinCodigo: '$sPinCodigo'] [sValorFace: '$sValorFace']<br>";

					//Validacoes
					if($sLote == "" || !is_numeric($sLote)) 			$msg = "Lote inválido: " . $sLote . ".\n";
//					if($sPinSerial == "" || !is_numeric($sPinSerial)) 	$msg = "Pin Serial inválido: " . $sPinSerial . ".\n";
					if($sPinSerial == "" || (strlen($sPinSerial)!=14)) 	
						$msg = "Pin Serial inválido: " . $sPinSerial . ".\n";
//					if($sPinCodigo == "" || !is_numeric($sPinCodigo)) 	$msg = "Pin Código inválido: " . $sPinCodigo . ".\n";
//					echo "sPinSerial: ";
//					if(!is_numeric($sPinSerial))  	
//						echo "[Not is_numeric] ";
//					if(strlen($sPinSerial)!=14) 	
//						echo " [Not 14 chars:'']";
//					echo "<br>";

//echo "IsEmpty: ".(($sPinCodigo == "")?"Yes":"No")."<br>";  
//echo "IsNumeric: ".(is_numeric($sPinCodigo)?"Yes":"No")."<br>"; 
//echo "Len(14): ".((strlen($sPinCodigo)==14)?"Yes":"No")."<br>";

					if($sPinCodigo == "" || (strlen($sPinCodigo)!=20)) 	
						$msg = "Pin Código inválido: " . $sPinCodigo . ".\n";
					if($sValorFace == "" || !is_numeric($sValorFace)) 	$msg = "Valor de face inválido: " . $sValorFace . ".\n";

					//insere pin
					if($msg == ""){
						$sql = "insert into pins (opr_codigo, pin_status, pin_serial, pin_codigo, pin_local, pin_valor, pin_lote_codigo, pin_dataentrada, pin_horaentrada, pin_canal) ";
						$sql .= "values ($opr_codigo, 1, '$sPinSerial', '$sPinCodigo', '$iPinLocal', '$sValorFace', '$sLote', CURRENT_DATE, CURRENT_TIME, '".$fcanal."')";
						$ret = SQLexecuteQuery($sql);
						if(!$ret) $msg = "Erro ao inserir registro: " . $linha . ".\n";
					}
			
				} else {
					$seq++;
					$valor = "";
					
					$sai = array("-", " ", "unidades");
					$entra = array("", "", "");
					$linhaAr = explode("k", str_replace($sai, $entra, strtolower($linha)));
					$lote  = $opr_codigo . date('dmy') . "0" . $seq;
					$valor = Ignitedgames_traduzKValor($linhaAr[0]);
					$qtd = $linhaAr[1];

					//Validacoes
					if($lote == "" || !is_numeric($lote)) 	$msg = "Lote inválido: " . $lote . ".\n";
					if($valor == "" || !is_numeric($valor)) $msg = "Valor de face inválido: " . $valor . ".\n";
		
					if($msg == ""){		    
						$sql  = "select count(*) as qtde from pins where opr_codigo = $opr_codigo and pin_lote_codigo = $lote and pin_valor = $valor and pin_canal='s' ";
						$rs = SQLexecuteQuery($sql);
						$ret = true;
						if($rs && pg_num_rows($rs) > 0){
							$rs_row = pg_fetch_array($rs);
							if($rs_row['qtde'] == 0) $ret = false;
						}			
						if($ret) $msg = "Arquivo já foi importado.\n";
					}

				} 

				//Se houve erro, sai do loop
				if($msg != "") break;
				
			}
		}


		//Cria diretorios
		if($msg == ""){
			if(!is_dir($folder)) $msg = "Diretório raiz não existe.\n";
			if($msg == ""){
				if(!is_dir($folder. $opr_codigo)) 
					if(!mkdir($folder. $opr_codigo)) $msg = "Não foi possivel criar diretorio da operadora.\n";
			}
		}				

		//move arquivo
		if($msg == ""){
			$fileDest = $folder . $opr_codigo . "/" . $nomeArq; 
			if(!copy($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.\n"; 
			elseif((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo vazio ou inválido.\n";
		}
		
		//Finaliza transacao
		if($msg == ""){
			$sql = "COMMIT TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao comitar transação.\n";
		} else {
			$sql = "ROLLBACK TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
		}

		return $msg;
		
	}


//  ========================================================
function Ticket_Surf_traduzKValor($k){
	$valor["20"] = "20";

	return $valor[$k];
}

	function processaLote_Ticket_Surf($fileSource, $nomeArq, $opr_codigo, $loteValor, $fcanal){
	
		global $folder, $opr_codigo_Ticket_Surf;
		
		$msg = "";
		
		//Valida entradas
		$operadoras = array($opr_codigo_Ticket_Surf);
		if(is_null($fileSource) || trim($fileSource) == "") $msg = "Caminho do arquivo vázio.\n";
		if(is_null($nomeArq) || trim($nomeArq) == "") $msg = "Nome do arquivo vázio.\n";
		if(is_null($opr_codigo) || trim($opr_codigo) == "" || !is_numeric($opr_codigo) || !in_array($opr_codigo, $operadoras)) $msg = "Código da operadora inválido.\n";

		//local
		$iPinLocal = 30;


		//Abre arquivo e le conteudo
		if($msg == ""){
			$handle = fopen($fileSource, "r");
			$carga = fread($handle, filesize($fileSource));
			fclose($handle);

			$cargaAr = explode("\n", $carga);
			if(count($cargaAr) == 0) $msg = "Arquivo vázio.\n";
		} 

		// Debug
//		for($i=0; $i < count($cargaAr); $i++){
//			echo "cargaAr[$i]: '".$cargaAr[$i]."'<br>";
//		}
//		$msg = "";

		//valida tamanho da linha
		if($msg == ""){
			for($i=0; $i < count($cargaAr); $i++){
				$cargaAr[$i] = trim($cargaAr[$i]);
								
				// tira linha em branco
				if(trim($cargaAr[$i]) == ""){
					array_splice($cargaAr, $i, 1); 
					continue;
				}
/*
 				//Valida tamanho da linha
				$num_posicoes = 34;
				if(strlen($cargaAr[$i]) != $num_posicoes){
					$msg = "linha " . ($i+1) . " do arquivo não tem $num_posicoes posições, possui " . strlen($cargaAr[$i]) . ":\n";
					$msg .= "**" . $cargaAr[$i] . "**\n";
					break;
				}
*/
			}
		}
		
		
		//Inicia transacao
		if($msg == ""){
			$sql = "BEGIN TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao iniciar transação.\n";
		}

		//insere lote
		if($msg == ""){
			
			$seq = 0;
			$valor = 0;
			for($i=0; $i < count($cargaAr); $i++){
	
				$linha 		= $cargaAr[$i];

				//dados				
				if (strpos($linha, "unidades") === false) { // corpo

					$linhaAr 	= explode("\t", $linha);
					$sLote 		= $opr_codigo . date('dmy') . $seq;	//$opr_codigo . date('dmy') . "0" . $seq;
					$sPinSerial = trim($linhaAr[0]);
				    $sPinCodigo	= trim($linhaAr[1]);
			    	$sValorFace = $linhaAr[2]; //$valor;

//echo "[sLote: '$sLote'] [sPinSerial: '$sPinSerial'] [sPinCodigo: '$sPinCodigo'] [sValorFace: '$sValorFace']<br>";

					//Validacoes
					if($sLote == "" || !is_numeric($sLote)) 			$msg = "Lote inválido: " . $sLote . ".\n";
//					if($sPinSerial == "" || !is_numeric($sPinSerial)) 	$msg = "Pin Serial inválido: " . $sPinSerial . ".\n";
					if($sPinSerial == "" || (strlen($sPinSerial)!=14)) 	
						$msg = "Pin Serial inválido: " . $sPinSerial . ".\n";
//					if($sPinCodigo == "" || !is_numeric($sPinCodigo)) 	$msg = "Pin Código inválido: " . $sPinCodigo . ".\n";
//					echo "sPinSerial: ";
//					if(!is_numeric($sPinSerial))  	
//						echo "[Not is_numeric] ";
//					if(strlen($sPinSerial)!=14) 	
//						echo " [Not 14 chars:'']";
//					echo "<br>";

//echo "IsEmpty: ".(($sPinCodigo == "")?"Yes":"No")."<br>";  
//echo "IsNumeric: ".(is_numeric($sPinCodigo)?"Yes":"No")."<br>"; 
//echo "Len(14): ".((strlen($sPinCodigo)==14)?"Yes":"No")."<br>";

					if($sPinCodigo == "" || !is_numeric($sPinCodigo) || (strlen($sPinCodigo)!=14)) 	
						$msg = "Pin Código inválido: " . $sPinCodigo . ".\n";
					if($sValorFace == "" || !is_numeric($sValorFace)) 	$msg = "Valor de face inválido: " . $sValorFace . ".\n";

					//insere pin
					if($msg == ""){
						$sql = "insert into pins (opr_codigo, pin_status, pin_serial, pin_codigo, pin_local, pin_valor, pin_lote_codigo, pin_dataentrada, pin_horaentrada, pin_canal) ";
						$sql .= "values ($opr_codigo, 1, '$sPinSerial', '$sPinCodigo', '$iPinLocal', '$sValorFace', $sLote, CURRENT_DATE, CURRENT_TIME, '".$fcanal."')";
//echo $sql."<br>";
						$ret = SQLexecuteQuery($sql);
						if(!$ret) $msg = "Erro ao inserir registro: " . $linha . ".\n";
					}
			
				} else {
					$seq++;
					$valor = "";
					
					$sai = array("-", " ", "unidades");
					$entra = array("", "", "");
					$linhaAr = explode("k", str_replace($sai, $entra, strtolower($linha)));
					$lote  = $opr_codigo . date('dmy') . "0" . $seq;
					$valor = Ticket_Surf_traduzKValor($linhaAr[0]);
					$qtd = $linhaAr[1];

					//Validacoes
					if($lote == "" || !is_numeric($lote)) 	$msg = "Lote inválido: " . $lote . ".\n";
					if($valor == "" || !is_numeric($valor)) $msg = "Valor de face inválido: " . $valor . ".\n";
		
					if($msg == ""){		    
						$sql  = "select count(*) as qtde from pins where opr_codigo = $opr_codigo and pin_lote_codigo = $lote and pin_valor = $valor and pin_canal='s' ";
						$rs = SQLexecuteQuery($sql);
						$ret = true;
						if($rs && pg_num_rows($rs) > 0){
							$rs_row = pg_fetch_array($rs);
							if($rs_row['qtde'] == 0) $ret = false;
						}			
						if($ret) $msg = "Arquivo já foi importado.\n";
					}

				} 

				//Se houve erro, sai do loop
				if($msg != "") break;
				
			}
		}


		//Cria diretorios
		if($msg == ""){
			if(!is_dir($folder)) $msg = "Diretório raiz não existe.\n";
			if($msg == ""){
				if(!is_dir($folder. $opr_codigo)) 
					if(!mkdir($folder. $opr_codigo)) $msg = "Não foi possivel criar diretorio da operadora.\n";
			}
		}				

		//move arquivo
		if($msg == ""){
			$fileDest = $folder . $opr_codigo . "/" . $nomeArq; 
			if(!copy($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.\n"; 
			elseif((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo vazio ou inválido.\n";
		}
		
		//Finaliza transacao
		if($msg == ""){
			$sql = "COMMIT TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao comitar transação.\n";
		} else {
			$sql = "ROLLBACK TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
		}

		return $msg;
		
	}


function GameGol_traduzKValor($k){
	$valor["10"] = "10";
	$valor["20"] = "20";
	$valor["25"] = "25";
	$valor["30"] = "30";
	$valor["40"] = "40";
	$valor["50"] = "50";
	
	return $valor[$k];
}

	function processaLote_GameGol($fileSource, $nomeArq, $opr_codigo, $loteValor, $fcanal){
	
		global $folder, $opr_codigo_GameGol;
		
		$msg = "";
		
		//Valida entradas
		$operadoras = array($opr_codigo_GameGol);
		if(is_null($fileSource) || trim($fileSource) == "") $msg = "Caminho do arquivo vázio.\n";
		if(is_null($nomeArq) || trim($nomeArq) == "") $msg = "Nome do arquivo vázio.\n";
		if(is_null($opr_codigo) || trim($opr_codigo) == "" || !is_numeric($opr_codigo) || !in_array($opr_codigo, $operadoras)) $msg = "Código da operadora inválido.\n";

		//local
		$iPinLocal = 28;


		//Abre arquivo e le conteudo
		if($msg == ""){
			$handle = fopen($fileSource, "r");
			$carga = fread($handle, filesize($fileSource));
			fclose($handle);

			$cargaAr = explode("\n", $carga);
			if(count($cargaAr) == 0) $msg = "Arquivo vázio.\n";
		} 

		// Debug
//		for($i=0; $i < count($cargaAr); $i++){
//			echo "cargaAr[$i]: '".$cargaAr[$i]."'<br>";
//		}
//		$msg = "";

		//valida tamanho da linha
		if($msg == ""){
			for($i=0; $i < count($cargaAr); $i++){
				$cargaAr[$i] = trim($cargaAr[$i]);
								
				// tira linha em branco
				if(trim($cargaAr[$i]) == ""){
					array_splice($cargaAr, $i, 1); 
					continue;
				}
/*
 				//Valida tamanho da linha
				$num_posicoes = 34;
				if(strlen($cargaAr[$i]) != $num_posicoes){
					$msg = "linha " . ($i+1) . " do arquivo não tem $num_posicoes posições, possui " . strlen($cargaAr[$i]) . ":\n";
					$msg .= "**" . $cargaAr[$i] . "**\n";
					break;
				}
*/
			}
		}
		
		
		//Inicia transacao
		if($msg == ""){
			$sql = "BEGIN TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao iniciar transação.\n";
		}

		//insere lote
		if($msg == ""){
			
			$seq = 0;
			$valor = 0;
			for($i=0; $i < count($cargaAr); $i++){
	
				$linha 		= $cargaAr[$i];

				//dados				
				if (strpos($linha, "unidades") === false) { // corpo

//echo "[linha: $linha]<br>";
					$linhaAr 	= explode("\t", $linha);
					$sLote 		= $opr_codigo . date('dmy') . "0" . $seq;
					$sPinSerial = "0";
				    $sPinCodigo	= trim($linhaAr[0]);
			    	$sValorFace = trim($linhaAr[1]); //$valor;
//echo "[sLote: $sLote] [sPinCodigo: $sPinCodigo] [sValorFace: $sValorFace] [strlen: ".strlen($sPinCodigo)."]<br>";
					//Validacoes
					if($sLote == "" || !is_numeric($sLote)) 			$msg = "Lote inválido: " . $sLote . ".\n";
//					if($sPinCodigo == "" || !is_numeric($sPinCodigo)) 	$msg = "Pin Código inválido: " . $sPinCodigo . ".\n";
					if($sPinCodigo == "" || (strlen($sPinCodigo)!=16)) 	
							$msg = "Pin Código inválido: " . $sPinCodigo . ".\n";
					if($sValorFace == "" || !is_numeric($sValorFace)) 	$msg = "Valor de face inválido: " . $sValorFace . ".\n";

					//insere pin
					if($msg == ""){
						$sql = "insert into pins (opr_codigo, pin_status, pin_serial, pin_codigo, pin_local, pin_valor, pin_lote_codigo, pin_dataentrada, pin_horaentrada, pin_canal) ";
						$sql .= "values ($opr_codigo, 1, '$sPinSerial', '$sPinCodigo', '$iPinLocal', '$sValorFace', '$sLote', CURRENT_DATE, CURRENT_TIME, '".$fcanal."')";
						$ret = SQLexecuteQuery($sql);
						if(!$ret) $msg = "Erro ao inserir registro: " . $linha . ".\n";
					}
			
				} else {
					$seq++;
					$valor = "";
					
					$sai = array("-", " ", "unidades");
					$entra = array("", "", "");
					$linhaAr = explode("k", str_replace($sai, $entra, strtolower($linha)));
					$lote  = $opr_codigo . date('dmy') . "0" . $seq;
					$valor = GameGol_traduzKValor($linhaAr[0]);
					$qtd = $linhaAr[1];

					//Validacoes
					if($lote == "" || !is_numeric($lote)) 	$msg = "Lote inválido: " . $lote . ".\n";
					if($valor == "" || !is_numeric($valor)) $msg = "Valor de face inválido: " . $valor . ".\n";
		
					if($msg == ""){		    
						$sql  = "select count(*) as qtde from pins where opr_codigo = $opr_codigo and pin_lote_codigo = $lote and pin_valor = $valor and pin_canal='s' ";
						$rs = SQLexecuteQuery($sql);
						$ret = true;
						if($rs && pg_num_rows($rs) > 0){
							$rs_row = pg_fetch_array($rs);
							if($rs_row['qtde'] == 0) $ret = false;
						}			
						if($ret) $msg = "Arquivo já foi importado.\n";
					}

				} 

				//Se houve erro, sai do loop
				if($msg != "") break;
				
			}
		}


		//Cria diretorios
		if($msg == ""){
			if(!is_dir($folder)) $msg = "Diretório raiz não existe.\n";
			if($msg == ""){
				if(!is_dir($folder. $opr_codigo)) 
					if(!mkdir($folder. $opr_codigo)) $msg = "Não foi possivel criar diretorio da operadora.\n";
			}
		}				

		//move arquivo
		if($msg == ""){
			$fileDest = $folder . $opr_codigo . "/" . $nomeArq; 
			if(!copy($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.\n"; 
			elseif((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo vazio ou inválido.\n";
		}
		
		//Finaliza transacao
		if($msg == ""){
			$sql = "COMMIT TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao comitar transação.\n";
		} else {
			$sql = "ROLLBACK TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
		}

		return $msg;
		
	}

function GameIS_traduzKValor($k){
	$valor["11"] = "50";
	$valor["33"] = "158";
	$valor["66"] = "330";
	
	return $valor[$k];
}

	function processaLote_GameIS($fileSource, $nomeArq, $opr_codigo, $loteValor, $fcanal){
	
		global $folder, $opr_codigo_GameIS;
		
		$msg = "";
		
		//Valida entradas
		$operadoras = array($opr_codigo_GameIS);
		if(is_null($fileSource) || trim($fileSource) == "") $msg = "Caminho do arquivo vázio.\n";
		if(is_null($nomeArq) || trim($nomeArq) == "") $msg = "Nome do arquivo vázio.\n";
		if(is_null($opr_codigo) || trim($opr_codigo) == "" || !is_numeric($opr_codigo) || !in_array($opr_codigo, $operadoras)) $msg = "Código da operadora inválido.\n";

		//local
		$iPinLocal = 27;


		//Abre arquivo e le conteudo
		if($msg == ""){
			$handle = fopen($fileSource, "r");
			$carga = fread($handle, filesize($fileSource));
			fclose($handle);

			$cargaAr = explode("\n", $carga);
			if(count($cargaAr) == 0) $msg = "Arquivo vázio.\n";
		} 

// Debug
//for($i=0; $i < count($cargaAr); $i++){
//	echo "cargaAr[$i]: '".$cargaAr[$i]."'<br>";
//}
//$msg = "";

		//valida tamanho da linha
		// Exemplo: 	"4E7855D1AC4649HS	66"
		if($msg == ""){
			for($i=0; $i < count($cargaAr); $i++){
				$cargaAr[$i] = trim($cargaAr[$i]);
								
				// tira linha em branco
				if(trim($cargaAr[$i]) == ""){
					array_splice($cargaAr, $i, 1); 
					continue;
				}
/*
 				//Valida tamanho da linha
				$num_posicoes = 34;
				if(strlen($cargaAr[$i]) != $num_posicoes){
					$msg = "linha " . ($i+1) . " do arquivo não tem $num_posicoes posições, possui " . strlen($cargaAr[$i]) . ":\n";
					$msg .= "**" . $cargaAr[$i] . "**\n";
					break;
				}
*/
			}
		}
		
		
		//Inicia transacao
		if($msg == ""){
			$sql = "BEGIN TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao iniciar transação.\n";
		}

		//insere lote
		if($msg == ""){
			
			$seq = 0;
			$valor = 0;
			for($i=0; $i < count($cargaAr); $i++){
	
				$linha 		= $cargaAr[$i];

				//dados				
				if (strpos($linha, "unidades") === false) { // corpo

					$linhaAr 	= explode("\t", $linha);
					$sLote 		= $opr_codigo . date('dmy') . $seq;
					$sPinSerial = $linhaAr[0];
				    $sPinCodigo	= "0";
			    	$sValorFace = $linhaAr[1];

					//Validacoes
					if($sLote == "" || !is_numeric($sLote)) 			$msg = "Lote inválido: " . $sLote . ".\n";
					if($sPinSerial == "") 	$msg = "Pin Serial inválido: " . $sPinSerial . ".\n";
//					if($sPinCodigo == "" || !is_numeric($sPinCodigo)) 	$msg = "Pin Código inválido: " . $sPinCodigo . ".\n";
					if($sValorFace == "" || !is_numeric($sValorFace)) 	$msg = "Valor de face inválido: " . $sValorFace . ".\n";

					//insere pin
					if($msg == ""){
						$sql = "insert into pins (opr_codigo, pin_status, pin_serial, pin_codigo, pin_local, pin_valor, pin_lote_codigo, pin_dataentrada, pin_horaentrada, pin_canal) ";
						$sql .= "values ($opr_codigo, 1, '$sPinSerial', '$sPinCodigo', '$iPinLocal', '$sValorFace', '$sLote', CURRENT_DATE, CURRENT_TIME, '".$fcanal."')";
//echo "sql: $sql<br>";
						$ret = SQLexecuteQuery($sql);
						if(!$ret) $msg = "Erro ao inserir registro: " . $linha . ".\n";
					}
			
				} else {
					$seq++;
					$valor = "";
					
					$sai = array("-", " ", "unidades");
					$entra = array("", "", "");
					$linhaAr = explode("k", str_replace($sai, $entra, strtolower($linha)));
					$lote  = $opr_codigo . date('dmy') . "0" . $seq;
					$valor = GameIS_traduzKValor($linhaAr[0]);
					$qtd = $linhaAr[1];

					//Validacoes
					if($lote == "" || !is_numeric($lote)) 	$msg = "Lote inválido: " . $lote . ".\n";
					if($valor == "" || !is_numeric($valor)) $msg = "Valor de face inválido: " . $valor . ".\n";
		
					if($msg == ""){		    
						$sql  = "select count(*) as qtde from pins where opr_codigo = $opr_codigo and pin_lote_codigo = $lote and pin_valor = $valor and pin_canal='s' ";
//echo "sql: $sql";
						$rs = SQLexecuteQuery($sql);
						$ret = true;
						if($rs && pg_num_rows($rs) > 0){
							$rs_row = pg_fetch_array($rs);
							if($rs_row['qtde'] == 0) $ret = false;
						}			
						if($ret) $msg = "Arquivo já foi importado (opr_codigo=$opr_codigo, pin_lote_codigo=$lote, pin_valor=$valor -> val:".$linhaAr[0].", qtd:".$linhaAr[1].")\n"."linha ($i): '$linha'\n";
					}

				} 

				//Se houve erro, sai do loop
				if($msg != "") break;
				
			}
		}


		//Cria diretorios
		if($msg == ""){
			if(!is_dir($folder)) $msg = "Diretório raiz não existe.\n";
			if($msg == ""){
				if(!is_dir($folder. $opr_codigo)) 
					if(!mkdir($folder. $opr_codigo)) $msg = "Não foi possivel criar diretorio da operadora.\n";
			}
		}				

		//move arquivo
		if($msg == ""){
			$fileDest = $folder . $opr_codigo . "/" . $nomeArq; 
			if(!copy($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.\n"; 
			elseif((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo vazio ou inválido.\n";
		}
		
		//Finaliza transacao
		if($msg == ""){
			$sql = "COMMIT TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao comitar transação.\n";
		} else {
			$sql = "ROLLBACK TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
		}

		return $msg;
		
	}

function Kaizen_traduzKValor($k){
	$valor["14"] = "14";
	$valor["24"] = "24";
	$valor["39"] = "39";
	
	return $valor[$k];
}

	function processaLote_Kaizen($fileSource, $nomeArq, $opr_codigo, $loteValor, $fcanal){
	
		global $folder, $opr_codigo_Kaizen;
		
		$msg = "";
		
		//Valida entradas
		$operadoras = array($opr_codigo_Kaizen);
		if(is_null($fileSource) || trim($fileSource) == "") $msg = "Caminho do arquivo vázio.\n";
		if(is_null($nomeArq) || trim($nomeArq) == "") $msg = "Nome do arquivo vázio.\n";
		if(is_null($opr_codigo) || trim($opr_codigo) == "" || !is_numeric($opr_codigo) || !in_array($opr_codigo, $operadoras)) $msg = "Código da operadora inválido.\n";

		//local
		$iPinLocal = 29;


		//Abre arquivo e le conteudo
		if($msg == ""){
			$handle = fopen($fileSource, "r");
			$carga = fread($handle, filesize($fileSource));
			fclose($handle);

			$cargaAr = explode("\n", $carga);
			if(count($cargaAr) == 0) $msg = "Arquivo vázio.\n";
		} 

// Debug
//for($i=0; $i < count($cargaAr); $i++){
//	echo "cargaAr[$i]: '".$cargaAr[$i]."'<br>";
//}
//$msg = "";

		//valida tamanho da linha
		// Exemplo: 	"44A42C5F	14"
		if($msg == ""){
			for($i=0; $i < count($cargaAr); $i++){
				$cargaAr[$i] = trim($cargaAr[$i]);
								
				// tira linha em branco
				if(trim($cargaAr[$i]) == ""){
					array_splice($cargaAr, $i, 1); 
					continue;
				}
/*
 				//Valida tamanho da linha
				$num_posicoes = 34;
				if(strlen($cargaAr[$i]) != $num_posicoes){
					$msg = "linha " . ($i+1) . " do arquivo não tem $num_posicoes posições, possui " . strlen($cargaAr[$i]) . ":\n";
					$msg .= "**" . $cargaAr[$i] . "**\n";
					break;
				}
*/
			}
		}
		
		
		//Inicia transacao
		if($msg == ""){
			$sql = "BEGIN TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao iniciar transação.\n";
		}

		//insere lote
		if($msg == ""){
			
			$seq = 0;
			$valor = 0;
			for($i=0; $i < count($cargaAr); $i++){
	
				$linha 		= $cargaAr[$i];

				//dados				
				if (strpos($linha, "unidades") === false) { // corpo

					$linhaAr 	= explode("\t", $linha);
					$sLote 		= $opr_codigo . date('dmy') . $seq;
					$sPinSerial = "";
				    $sPinCodigo	= $linhaAr[0];
			    	$sValorFace = $linhaAr[1];

					//Validacoes
					if($sLote == "" || !is_numeric($sLote)) 			$msg = "Lote inválido: " . $sLote . ".\n";
//					if($sPinSerial == "") 	$msg = "Pin Serial inválido: " . $sPinSerial . ".\n";
//					if(strlen($sPinSerial)!=8) 	$msg = "Pin Serial inválido: tamanho!=8 '" . $sPinSerial . "', Len=".strlen($sPinSerial).".\n";
					if($sPinCodigo == "") 	$msg = "Pin Código inválido: " . $sPinCodigo . ".\n";
					if(strlen($sPinCodigo)!=8) 	$msg = "Pin Código inválido: tamanho!=8 '" . $sPinCodigo . "', Len=".strlen($sPinCodigo).".\n";
					if($sValorFace == "" || !is_numeric($sValorFace)) 	$msg = "Valor de face inválido: " . $sValorFace . ".\n";

					//insere pin
					if($msg == ""){
						$sql = "insert into pins (opr_codigo, pin_status, pin_serial, pin_codigo, pin_local, pin_valor, pin_lote_codigo, pin_dataentrada, pin_horaentrada, pin_canal) ";
						$sql .= "values ($opr_codigo, 1, '$sPinSerial', '$sPinCodigo', '$iPinLocal', '$sValorFace', '$sLote', CURRENT_DATE, CURRENT_TIME, '".$fcanal."')";
//echo "sql: $sql<br>";
						$ret = SQLexecuteQuery($sql);
						if(!$ret) $msg = "Erro ao inserir registro: " . $linha . ".\n";
					} else {
						echo "<font color='#FF0000'>Erro de validação: <b>".$msg."</b></font>\n";
					}
			
				} else {
					$seq++;
					$valor = "";
					
					$sai = array("-", " ", "unidades");
					$entra = array("", "", "");
					$linhaAr = explode("k", str_replace($sai, $entra, strtolower($linha)));
					$lote  = $opr_codigo . date('dmy') . "0" . $seq;
					$valor = Kaizen_traduzKValor($linhaAr[0]);
					$qtd = $linhaAr[1];

					//Validacoes
					if($lote == "" || !is_numeric($lote)) 	$msg = "Lote inválido: " . $lote . ".\n";
					if($valor == "" || !is_numeric($valor)) $msg = "Valor de face inválido: " . $valor . ".\n";
		
					if($msg == ""){		    
						$sql  = "select count(*) as qtde from pins where opr_codigo = $opr_codigo and pin_lote_codigo = $lote and pin_valor = $valor and pin_canal='s' ";
//echo "sql: $sql";
						$rs = SQLexecuteQuery($sql);
						$ret = true;
						if($rs && pg_num_rows($rs) > 0){
							$rs_row = pg_fetch_array($rs);
							if($rs_row['qtde'] == 0) $ret = false;
						}			
						if($ret) $msg = "Arquivo já foi importado (opr_codigo=$opr_codigo, pin_lote_codigo=$lote, pin_valor=$valor -> val:".$linhaAr[0].", qtd:".$linhaAr[1].")\n"."linha ($i): '$linha'\n";
					}

				} 

				//Se houve erro, sai do loop
				if($msg != "") break;
				
			}
		}


		//Cria diretorios
		if($msg == ""){
			if(!is_dir($folder)) $msg = "Diretório raiz não existe.\n";
			if($msg == ""){
				if(!is_dir($folder. $opr_codigo)) 
					if(!mkdir($folder. $opr_codigo)) $msg = "Não foi possivel criar diretorio da operadora.\n";
			}
		}				

		//move arquivo
		if($msg == ""){
			$fileDest = $folder . $opr_codigo . "/" . $nomeArq; 
			if(!copy($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.\n"; 
			elseif((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo vazio ou inválido.\n";
		}
		
		//Finaliza transacao
		if($msg == ""){
			$sql = "COMMIT TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao comitar transação.\n";
		} else {
			$sql = "ROLLBACK TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
		}

		return $msg;
		
	}


function Onnet_traduzKValor($k){
	$valor["10"] = "10";
	$valor["20"] = "20";
	$valor["40"] = "40";
	$valor["60"] = "60";
	
	return $valor[$k];
}

	function processaLote_Onnet($fileSource, $nomeArq, $opr_codigo, $loteValor, $fcanal){
	
		global $folder, $opr_codigo_Onnet;
		
		$msg = "";
		
		//Valida entradas
		$operadoras = array($opr_codigo_Onnet);
		if(is_null($fileSource) || trim($fileSource) == "") $msg = "Caminho do arquivo vázio.\n";
		if(is_null($nomeArq) || trim($nomeArq) == "") $msg = "Nome do arquivo vázio.\n";
		if(is_null($opr_codigo) || trim($opr_codigo) == "" || !is_numeric($opr_codigo) || !in_array($opr_codigo, $operadoras)) $msg = "Código da operadora inválido.\n";

		//local
		$iPinLocal = 29;


		//Abre arquivo e le conteudo
		if($msg == ""){
			$handle = fopen($fileSource, "r");
			$carga = fread($handle, filesize($fileSource));
			fclose($handle);

			$cargaAr = explode("\n", $carga);
			if(count($cargaAr) == 0) $msg = "Arquivo vázio.\n";
		} 

// Debug
//for($i=0; $i < count($cargaAr); $i++){
//	echo "cargaAr[$i]: '".$cargaAr[$i]."'<br>";
//}
//$msg = "";

		//valida tamanho da linha
		// Exemplo: 	"44A42C5F	14"
		if($msg == ""){
			for($i=0; $i < count($cargaAr); $i++){
				$cargaAr[$i] = trim($cargaAr[$i]);
								
				// tira linha em branco
				if(trim($cargaAr[$i]) == ""){
					array_splice($cargaAr, $i, 1); 
					continue;
				}
/*
 				//Valida tamanho da linha
				$num_posicoes = 34;
				if(strlen($cargaAr[$i]) != $num_posicoes){
					$msg = "linha " . ($i+1) . " do arquivo não tem $num_posicoes posições, possui " . strlen($cargaAr[$i]) . ":\n";
					$msg .= "**" . $cargaAr[$i] . "**\n";
					break;
				}
*/
			}
		}
		
		
		//Inicia transacao
		if($msg == ""){
			$sql = "BEGIN TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao iniciar transação.\n";
		}

		//insere lote
		if($msg == ""){
			
			$seq = 0;
			$valor = 0;
			for($i=0; $i < count($cargaAr); $i++){
	
				$linha 		= $cargaAr[$i];

				//dados				
				if (strpos($linha, "unidades") === false) { // corpo

					$linhaAr 	= explode("\t", $linha);
					$sLote 		= $opr_codigo . date('dmy') . $seq;
					$sPinSerial = "";
				    $sPinCodigo	= $linhaAr[0];
			    	$sValorFace = $linhaAr[1];

					//Validacoes
					if($sLote == "" || !is_numeric($sLote)) 			$msg = "Lote inválido: " . $sLote . ".\n";
//					if($sPinSerial == "") 	$msg = "Pin Serial inválido: " . $sPinSerial . ".\n";
//					if(strlen($sPinSerial)!=8) 	$msg = "Pin Serial inválido: tamanho!=8 '" . $sPinSerial . "', Len=".strlen($sPinSerial).".\n";
					if($sPinCodigo == "") 	$msg = "Pin Código inválido: " . $sPinCodigo . ".\n";
					if(strlen($sPinCodigo)!=15) 	$msg = "Pin Código inválido: tamanho!=15 '" . $sPinCodigo . "', Len=".strlen($sPinCodigo).".\n";
					if($sValorFace == "" || !is_numeric($sValorFace)) 	$msg = "Valor de face inválido: " . $sValorFace . ".\n";

					//insere pin
					if($msg == ""){
						$sql = "insert into pins (opr_codigo, pin_status, pin_serial, pin_codigo, pin_local, pin_valor, pin_lote_codigo, pin_dataentrada, pin_horaentrada, pin_canal) ";
						$sql .= "values ($opr_codigo, 1, '$sPinSerial', '$sPinCodigo', '$iPinLocal', '$sValorFace', '$sLote', CURRENT_DATE, CURRENT_TIME, '".$fcanal."')";
//echo "sql: $sql<br>";
						$ret = SQLexecuteQuery($sql);
						if(!$ret) $msg = "Erro ao inserir registro: " . $linha . ".\n";
					} else {
						echo "<font color='#FF0000'>Erro de validação: <b>".$msg."</b></font>\n";
					}
			
				} else {
					$seq++;
					$valor = "";
					
					$sai = array("-", " ", "unidades");
					$entra = array("", "", "");
					$linhaAr = explode("k", str_replace($sai, $entra, strtolower($linha)));
					$lote  = $opr_codigo . date('dmy') . "0" . $seq;
					$valor = Onnet_traduzKValor($linhaAr[0]);
					$qtd = $linhaAr[1];

					//Validacoes
					if($lote == "" || !is_numeric($lote)) 	$msg = "Lote inválido: " . $lote . ".\n";
					if($valor == "" || !is_numeric($valor)) $msg = "Valor de face inválido: " . $valor . ".\n";
		
					if($msg == ""){		    
						$sql  = "select count(*) as qtde from pins where opr_codigo = $opr_codigo and pin_lote_codigo = $lote and pin_valor = $valor and pin_canal='s' ";
//echo "sql: $sql";
						$rs = SQLexecuteQuery($sql);
						$ret = true;
						if($rs && pg_num_rows($rs) > 0){
							$rs_row = pg_fetch_array($rs);
							if($rs_row['qtde'] == 0) $ret = false;
						}			
						if($ret) $msg = "Arquivo já foi importado (opr_codigo=$opr_codigo, pin_lote_codigo=$lote, pin_valor=$valor -> val:".$linhaAr[0].", qtd:".$linhaAr[1].")\n"."linha ($i): '$linha'\n";
					}

				} 

				//Se houve erro, sai do loop
				if($msg != "") break;
				
			}
		}


		//Cria diretorios
		if($msg == ""){
			if(!is_dir($folder)) $msg = "Diretório raiz não existe.\n";
			if($msg == ""){
				if(!is_dir($folder. $opr_codigo)) 
					if(!mkdir($folder. $opr_codigo)) $msg = "Não foi possivel criar diretorio da operadora.\n";
			}
		}				

		//move arquivo
		if($msg == ""){
			$fileDest = $folder . $opr_codigo . "/" . $nomeArq; 
			if(!copy($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.\n"; 
			elseif((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo vazio ou inválido.\n";
		}
		
		//Finaliza transacao
		if($msg == ""){
			$sql = "COMMIT TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao comitar transação.\n";
		} else {
			$sql = "ROLLBACK TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
		}

		return $msg;
		
	}


function fun_77PB_traduzKValor($k){
	$valor["30"] = "30";
	
	return $valor[$k];
}

	function processaLote_77PB($fileSource, $nomeArq, $opr_codigo, $loteValor, $fcanal){
	
		global $folder, $opr_codigo_77PB;
		
		$msg = "";
		
		//Valida entradas
		$operadoras = array($opr_codigo_77PB);
		if(is_null($fileSource) || trim($fileSource) == "") $msg = "Caminho do arquivo vázio.\n";
		if(is_null($nomeArq) || trim($nomeArq) == "") $msg = "Nome do arquivo vázio.\n";
		if(is_null($opr_codigo) || trim($opr_codigo) == "" || !is_numeric($opr_codigo) || !in_array($opr_codigo, $operadoras)) $msg = "Código da operadora inválido.\n";

		//local
		$iPinLocal = 29;


		//Abre arquivo e le conteudo
		if($msg == ""){
			$handle = fopen($fileSource, "r");
			$carga = fread($handle, filesize($fileSource));
			fclose($handle);

			$cargaAr = explode("\n", $carga);
			if(count($cargaAr) == 0) $msg = "Arquivo vázio.\n";
		} 

// Debug
//for($i=0; $i < count($cargaAr); $i++){
//	echo "cargaAr[$i]: '".$cargaAr[$i]."'<br>";
//}
//$msg = "";

		//valida tamanho da linha
		// Exemplo: 	"44A42C5F	14"
		if($msg == ""){
			for($i=0; $i < count($cargaAr); $i++){
				$cargaAr[$i] = trim($cargaAr[$i]);
								
				// tira linha em branco
				if(trim($cargaAr[$i]) == ""){
					array_splice($cargaAr, $i, 1); 
					continue;
				}
/*
 				//Valida tamanho da linha
				$num_posicoes = 34;
				if(strlen($cargaAr[$i]) != $num_posicoes){
					$msg = "linha " . ($i+1) . " do arquivo não tem $num_posicoes posições, possui " . strlen($cargaAr[$i]) . ":\n";
					$msg .= "**" . $cargaAr[$i] . "**\n";
					break;
				}
*/
			}
		}
		
		
		//Inicia transacao
		if($msg == ""){
			$sql = "BEGIN TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao iniciar transação.\n";
		}

		//insere lote
		if($msg == ""){
			
			$seq = 0;
			$valor = 0;
			for($i=0; $i < count($cargaAr); $i++){
	
				$linha 		= $cargaAr[$i];

				//dados				
				if (strpos($linha, "unidades") === false) { // corpo

					$linhaAr 	= explode("\t", $linha);
					$sLote 		= $opr_codigo . date('dmy') . $seq;
					$sPinSerial = "";
				    $sPinCodigo	= $linhaAr[0];
			    	$sValorFace = $loteValor;	//$linhaAr[1];

					//Validacoes
					if($sLote == "" || !is_numeric($sLote)) 			$msg = "Lote inválido: " . $sLote . ".\n";
//					if($sPinSerial == "") 	$msg = "Pin Serial inválido: " . $sPinSerial . ".\n";
//					if(strlen($sPinSerial)!=8) 	$msg = "Pin Serial inválido: tamanho!=8 '" . $sPinSerial . "', Len=".strlen($sPinSerial).".\n";
					if($sPinCodigo == "") 	$msg = "Pin Código inválido: " . $sPinCodigo . ".\n";
					if(strlen($sPinCodigo)!=12) 	$msg = "Pin Código inválido: tamanho!=12 '" . $sPinCodigo . "', Len=".strlen($sPinCodigo).".\n";
					if($sValorFace == "" || !is_numeric($sValorFace)) 	$msg = "Valor de face inválido: " . $sValorFace . ".\n";

					//insere pin
					if($msg == ""){
						$sql = "insert into pins (opr_codigo, pin_status, pin_serial, pin_codigo, pin_local, pin_valor, pin_lote_codigo, pin_dataentrada, pin_horaentrada, pin_canal) ";
						$sql .= "values ($opr_codigo, 1, '$sPinSerial', '$sPinCodigo', '$iPinLocal', '$sValorFace', '$sLote', CURRENT_DATE, CURRENT_TIME, '".$fcanal."')";
//echo "sql: $sql<br>";
						$ret = SQLexecuteQuery($sql);
						if(!$ret) $msg = "Erro ao inserir registro: " . $linha . ".\n";
					} else {
						echo "<font color='#FF0000'>Erro de validação: <b>".$msg."</b></font>\n";
					}
			
				} else {
					$seq++;
					$valor = "";
					
					$sai = array("-", " ", "unidades");
					$entra = array("", "", "");
					$linhaAr = explode("k", str_replace($sai, $entra, strtolower($linha)));
					$lote  = $opr_codigo . date('dmy') . "0" . $seq;
					$valor = fun_77PB_traduzKValor($linhaAr[0]);
					$qtd = $linhaAr[1];

					//Validacoes
					if($lote == "" || !is_numeric($lote)) 	$msg = "Lote inválido: " . $lote . ".\n";
					if($valor == "" || !is_numeric($valor)) $msg = "Valor de face inválido: " . $valor . ".\n";
		
					if($msg == ""){		    
						$sql  = "select count(*) as qtde from pins where opr_codigo = $opr_codigo and pin_lote_codigo = $lote and pin_valor = $valor and pin_canal='s' ";
//echo "sql: $sql";
						$rs = SQLexecuteQuery($sql);
						$ret = true;
						if($rs && pg_num_rows($rs) > 0){
							$rs_row = pg_fetch_array($rs);
							if($rs_row['qtde'] == 0) $ret = false;
						}			
						if($ret) $msg = "Arquivo já foi importado (opr_codigo=$opr_codigo, pin_lote_codigo=$lote, pin_valor=$valor -> val:".$linhaAr[0].", qtd:".$linhaAr[1].")\n"."linha ($i): '$linha'\n";
					}

				} 

				//Se houve erro, sai do loop
				if($msg != "") break;
				
			}
		}


		//Cria diretorios
		if($msg == ""){
			if(!is_dir($folder)) $msg = "Diretório raiz não existe.\n";
			if($msg == ""){
				if(!is_dir($folder. $opr_codigo)) 
					if(!mkdir($folder. $opr_codigo)) $msg = "Não foi possivel criar diretorio da operadora.\n";
			}
		}				

		//move arquivo
		if($msg == ""){
			$fileDest = $folder . $opr_codigo . "/" . $nomeArq; 
			if(!copy($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.\n"; 
			elseif((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo vazio ou inválido.\n";
		}
		
		//Finaliza transacao
		if($msg == ""){
			$sql = "COMMIT TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao comitar transação.\n";
		} else {
			$sql = "ROLLBACK TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
		}

		return $msg;
		
	}

function Alawar_traduzKValor($k){
	$valor["10"] = "10";
	
	return $valor[$k];
}

	function processaLote_Alawar($fileSource, $nomeArq, $opr_codigo, $loteValor, $fcanal){
	
		global $folder, $opr_codigo_Alawar;
		
		$msg = "";
		
		//Valida entradas
		$operadoras = array($opr_codigo_Alawar);
		if(is_null($fileSource) || trim($fileSource) == "") $msg = "Caminho do arquivo vázio.\n";
		if(is_null($nomeArq) || trim($nomeArq) == "") $msg = "Nome do arquivo vázio.\n";
		if(is_null($opr_codigo) || trim($opr_codigo) == "" || !is_numeric($opr_codigo) || !in_array($opr_codigo, $operadoras)) $msg = "Código da operadora inválido.\n";

		//local
		$iPinLocal = 29;


		//Abre arquivo e le conteudo
		if($msg == ""){
			$handle = fopen($fileSource, "r");
			$carga = fread($handle, filesize($fileSource));
			fclose($handle);

			$cargaAr = explode("\n", $carga);
			if(count($cargaAr) == 0) $msg = "Arquivo vázio.\n";
		} 

// Debug
//for($i=0; $i < count($cargaAr); $i++){
//	echo "cargaAr[$i]: '".$cargaAr[$i]."'<br>";
//}
//$msg = "";

		//valida tamanho da linha
		// Exemplo: 	"44A42C5F	14"
		if($msg == ""){
			for($i=0; $i < count($cargaAr); $i++){
				$cargaAr[$i] = trim($cargaAr[$i]);
								
				// tira linha em branco
				if(trim($cargaAr[$i]) == ""){
					array_splice($cargaAr, $i, 1); 
					continue;
				}
/*
 				//Valida tamanho da linha
				$num_posicoes = 34;
				if(strlen($cargaAr[$i]) != $num_posicoes){
					$msg = "linha " . ($i+1) . " do arquivo não tem $num_posicoes posições, possui " . strlen($cargaAr[$i]) . ":\n";
					$msg .= "**" . $cargaAr[$i] . "**\n";
					break;
				}
*/
			}
		}
		
		
		//Inicia transacao
		if($msg == ""){
			$sql = "BEGIN TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao iniciar transação.\n";
		}

		//insere lote
		if($msg == ""){
			
			$seq = 0;
			$valor = 0;
			for($i=0; $i < count($cargaAr); $i++){
	
				$linha 		= $cargaAr[$i];

				//dados				
				if (strpos($linha, "unidades") === false) { // corpo

					$linhaAr 	= explode("\t", $linha);
					$sLote 		= $opr_codigo . date('dmy') . $seq;
					$sPinSerial = "";
				    $sPinCodigo	= trim($linhaAr[0]);
			    	$sValorFace = $loteValor;

					//Validacoes
					if($sLote == "" || !is_numeric($sLote)) 			$msg = "Lote inválido: " . $sLote . ".\n";
//					if($sPinSerial == "") 	$msg = "Pin Serial inválido: " . $sPinSerial . ".\n";
//					if(strlen($sPinSerial)!=8) 	$msg = "Pin Serial inválido: tamanho!=8 '" . $sPinSerial . "', Len=".strlen($sPinSerial).".\n";
					if($sPinCodigo == "") 	$msg = "Pin Código inválido: " . $sPinCodigo . ".\n";
					if(strlen($sPinCodigo)>20) 	$msg = "Pin Código inválido: tamanho>20 '" . $sPinCodigo . "', Len=".strlen($sPinCodigo).".\n";
					if($sValorFace == "" || !is_numeric($sValorFace)) 	$msg = "Valor de face inválido: " . $sValorFace . ".\n";

					//insere pin
					if($msg == ""){
						$sql = "insert into pins (opr_codigo, pin_status, pin_serial, pin_codigo, pin_local, pin_valor, pin_lote_codigo, pin_dataentrada, pin_horaentrada, pin_canal) ";
						$sql .= "values ($opr_codigo, 1, '$sPinSerial', '$sPinCodigo', '$iPinLocal', '$sValorFace', '$sLote', CURRENT_DATE, CURRENT_TIME, '".$fcanal."')";
//echo "sql: $sql<br>";
						$ret = SQLexecuteQuery($sql);
						if(!$ret) $msg = "Erro ao inserir registro: " . $linha . ".\n";
					} else {
						echo "<font color='#FF0000'>Erro de validação: <b>".$msg."</b></font>\n";
					}
			
				} else {
					$seq++;
					$valor = "";
					
					$sai = array("-", " ", "unidades");
					$entra = array("", "", "");
					$linhaAr = explode("k", str_replace($sai, $entra, strtolower($linha)));
					$lote  = $opr_codigo . date('dmy') . "0" . $seq;
					$valor = Alawar_traduzKValor($linhaAr[0]);
					$qtd = $linhaAr[1];

					//Validacoes
					if($lote == "" || !is_numeric($lote)) 	$msg = "Lote inválido: " . $lote . ".\n";
					if($valor == "" || !is_numeric($valor)) $msg = "Valor de face inválido: " . $valor . ".\n";
		
					if($msg == ""){		    
						$sql  = "select count(*) as qtde from pins where opr_codigo = $opr_codigo and pin_lote_codigo = $lote and pin_valor = $valor and pin_canal='s' ";
//echo "sql: $sql";
						$rs = SQLexecuteQuery($sql);
						$ret = true;
						if($rs && pg_num_rows($rs) > 0){
							$rs_row = pg_fetch_array($rs);
							if($rs_row['qtde'] == 0) $ret = false;
						}			
						if($ret) $msg = "Arquivo já foi importado (opr_codigo=$opr_codigo, pin_lote_codigo=$lote, pin_valor=$valor -> val:".$linhaAr[0].", qtd:".$linhaAr[1].")\n"."linha ($i): '$linha'\n";
					}

				} 

				//Se houve erro, sai do loop
				if($msg != "") break;
				
			}
		}


		//Cria diretorios
		if($msg == ""){
			if(!is_dir($folder)) $msg = "Diretório raiz não existe.\n";
			if($msg == ""){
				if(!is_dir($folder. $opr_codigo)) 
					if(!mkdir($folder. $opr_codigo)) $msg = "Não foi possivel criar diretorio da operadora.\n";
			}
		}				

		//move arquivo
		if($msg == ""){
			$fileDest = $folder . $opr_codigo . "/" . $nomeArq; 
			if(!copy($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.\n"; 
			elseif((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo vazio ou inválido.\n";
		}
		
		//Finaliza transacao
		if($msg == ""){
			$sql = "COMMIT TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao comitar transação.\n";
		} else {
			$sql = "ROLLBACK TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
		}

		return $msg;
		
	}

function EletronicArts_traduzKValor($k){
	$valor["5"] = "5";
	$valor["9"] = "9";
	$valor["12"] = "12";
	$valor["15"] = "15";
	$valor["19"] = "19";
	$valor["24"] = "24";
	$valor["29"] = "29";
	$valor["38"] = "38";
	$valor["50"] = "50";
	return $valor[$k];
}
        
        
function processaLote_EletronicArts($fileSource, $nomeArq, $opr_codigo, $loteValor, $fcanal){
        global $folder, $opr_codigo_BATTLEFIELD, $opr_codigo_COMMANDANDCONQUER, $opr_codigo_NEEDFORSPEED, $opr_codigo_FIFAWORLD;
        $msg = "";
        //Valida entradas
        $operadoras = array($opr_codigo_BATTLEFIELD, $opr_codigo_COMMANDANDCONQUER, $opr_codigo_NEEDFORSPEED, $opr_codigo_FIFAWORLD);
        if(is_null($fileSource) || trim($fileSource) == "") $msg = "Caminho do arquivo vázio.\n";
        if(is_null($nomeArq) || trim($nomeArq) == "") $msg = "Nome do arquivo vázio.\n";
        if(is_null($opr_codigo) || trim($opr_codigo) == "" || !is_numeric($opr_codigo) || !in_array($opr_codigo, $operadoras)) $msg = "Código da operadora inválido.\n";
        //local
        $iPinLocal = 33;
        //Abre arquivo e le conteudo
        if($msg == ""){
                $handle = fopen($fileSource, "r");
                $carga = fread($handle, filesize($fileSource));
                fclose($handle);
                $cargaAr = explode("\n", $carga);
                if(count($cargaAr) == 0) $msg = "Arquivo vázio.\n";
        } 
        //valida tamanho da linha
        if($msg == ""){
                for($i=0; $i < count($cargaAr); $i++){
                        $cargaAr[$i] = trim($cargaAr[$i]);
                        // tira linha em branco
                        if(trim($cargaAr[$i]) == ""){
                                array_splice($cargaAr, $i, 1); 
                                continue;
                        }
                }
        }
        //Inicia transacao
        if($msg == ""){
                $sql = "BEGIN TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if(!$ret) $msg = "Erro ao iniciar transação.\n";
        }
        //insere lote
        if($msg == ""){
                $seq = 0;
                $valor = 0;
                for($i=0; $i < count($cargaAr); $i++){
                        $linha 		= $cargaAr[$i];
                        //dados				
                        if (strpos($linha, "unidades") === false) { // corpo
                                $linhaAr 	= explode("\t", $linha);
                                $sLote 		= $opr_codigo . date('dm') . $seq;	//	Too big for pin_lote_codigo (integer) in DB: $opr_codigo.date('dmy')."0".$seq;
                                $sPinSerial = $linhaAr[0];
                                $sValorFace = $linhaAr[1]; //$valor;
                                //Validacoes
                                if($sLote == "" || !is_numeric($sLote)) 			$msg = "Lote inválido: " . $sLote . ".\n";
                                if($sPinSerial == "" || (strlen($sPinSerial)!=19)) 	
                                        $msg = "Pin Serial comprimento inválido: " . $sPinSerial . ".\n";
                                if($sValorFace == "" || !is_numeric($sValorFace)) 	$msg = "Valor de face inválido: " . $sValorFace . ".\n";
                                //insere pin
                                if($msg == ""){
                                        $sql = "insert into pins (opr_codigo, pin_status, pin_serial, pin_codigo, pin_local, pin_valor, pin_lote_codigo, pin_dataentrada, pin_horaentrada, pin_canal) ";
                                        $sql .= "values ($opr_codigo, 1, '$sPinSerial', '', '$iPinLocal', '$sValorFace', '$sLote', CURRENT_DATE, CURRENT_TIME, '".$fcanal."')";
                                        $ret = SQLexecuteQuery($sql);
                                        if(!$ret) $msg = "Erro ao inserir registro: " . $linha . ".\n";
                                }
                        } else {
                                $seq++;
                                $valor = "";
                                $sai = array("-", " ", "unidades");
                                $entra = array("", "", "");
                                $linhaAr = explode("k", str_replace($sai, $entra, strtolower($linha)));
                                $lote  = $opr_codigo . date('dmy') . "0" . $seq;
                                $valor = EletronicArts_traduzKValor($linhaAr[0]);
                                $qtd = $linhaAr[1];
                                //Validacoes
                                if($lote == "" || !is_numeric($lote)) 	$msg = "Lote inválido: " . $lote . ".\n";
                                if($valor == "" || !is_numeric($valor)) $msg = "Valor de face inválido: " . $valor . ".\n";
                                if($msg == ""){		    
                                        $sql  = "select count(*) as qtde from pins where opr_codigo = $opr_codigo and pin_lote_codigo = $lote and pin_valor = $valor and pin_canal='s' ";
                                        $rs = SQLexecuteQuery($sql);
                                        $ret = true;
                                        if($rs && pg_num_rows($rs) > 0){
                                                $rs_row = pg_fetch_array($rs);
                                                if($rs_row['qtde'] == 0) $ret = false;
                                        }			
                                        if($ret) $msg = "Arquivo já foi importado.\n";
                                }
                        } 
                        //Se houve erro, sai do loop
                        if($msg != "") break;
                }
        }
        //Cria diretorios
        if($msg == ""){
                if(!is_dir($folder)) $msg = "Diretório raiz não existe.\n";
                if($msg == ""){
                        if(!is_dir($folder. $opr_codigo)) 
                                if(!mkdir($folder. $opr_codigo)) $msg = "Não foi possivel criar diretorio da operadora.\n";
                }
        }				
        //move arquivo
        if($msg == ""){
                $fileDest = $folder . $opr_codigo . "/" . $nomeArq; 
                if(!copy($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.\n"; 
                elseif((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo vazio ou inválido.\n";
        }
        //Finaliza transacao
        if($msg == ""){
                $sql = "COMMIT TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if(!$ret) $msg = "Erro ao comitar transação.\n";
        } else {
                $sql = "ROLLBACK TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
        }
        return $msg;
} //end function processaLote_EletronicArts

function CheckOk_traduzKValor($k){
	$valor["10"] = "10";
	$valor["25"] = "25";
	return $valor[$k];
}
        
        
function processaLote_CheckOk($fileSource, $nomeArq, $opr_codigo, $loteValor, $fcanal){
        global $folder, $opr_codigo_CheckOk;
        $msg = "";
        //Valida entradas
        $operadoras = array($opr_codigo_CheckOk);
        if(is_null($fileSource) || trim($fileSource) == "") $msg = "Caminho do arquivo vázio.\n";
        if(is_null($nomeArq) || trim($nomeArq) == "") $msg = "Nome do arquivo vázio.\n";
        if(is_null($opr_codigo) || trim($opr_codigo) == "" || !is_numeric($opr_codigo) || !in_array($opr_codigo, $operadoras)) $msg = "Código da operadora inválido.\n";
        //local
        $iPinLocal = 34;
        //Abre arquivo e le conteudo
        if($msg == ""){
                $handle = fopen($fileSource, "r");
                $carga = fread($handle, filesize($fileSource));
                fclose($handle);
                $cargaAr = explode("\n", $carga);
                if(count($cargaAr) == 0) $msg = "Arquivo vázio.\n";
        } 
        //valida tamanho da linha
        if($msg == ""){
                for($i=0; $i < count($cargaAr); $i++){
                        $cargaAr[$i] = trim($cargaAr[$i]);
                        // tira linha em branco
                        if(trim($cargaAr[$i]) == ""){
                                array_splice($cargaAr, $i, 1); 
                                continue;
                        }
                }
        }
        //Inicia transacao
        if($msg == ""){
                $sql = "BEGIN TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if(!$ret) $msg = "Erro ao iniciar transação.\n";
        }
        //insere lote
        if($msg == ""){
                $seq = 0;
                $valor = 0;
                $contador_PIN_importados = 0;
                for($i=0; $i < count($cargaAr); $i++){
                        $linha 		= $cargaAr[$i];
                        //dados				
                        if (substr($linha, 0, 1) == '1') { // corpo
                                $linhaAr[0]     = substr($linha, 13, 30);
                                $linhaAr[1]     = substr($linha, 43, 5)*1/100;
                                $sLote 		= $opr_codigo . date('dm') . $seq;	//	Too big for pin_lote_codigo (integer) in DB: $opr_codigo.date('dmy')."0".$seq;
                                $sPinSerial = trim($linhaAr[0]);
                                $sValorFace = $linhaAr[1]; //$valor;
                                //Validacoes
                                if($sLote == "" || !is_numeric($sLote)) 			$msg = "Lote inválido: " . $sLote . ".\n";
                                if($sPinSerial == "") 	
                                        $msg = "Pin Serial comprimento inválido: " . $sPinSerial . ".\n";
                                if($sValorFace == "" || !is_numeric($sValorFace)) 	$msg = "Valor de face inválido: " . $sValorFace . ".\n";
                                //insere pin
                                if($msg == ""){
                                        $sql = "insert into pins (opr_codigo, pin_status, pin_serial, pin_codigo, pin_local, pin_valor, pin_lote_codigo, pin_dataentrada, pin_horaentrada, pin_canal) ";
                                        $sql .= "values ($opr_codigo, 1, '$sPinSerial', '', '$iPinLocal', '$sValorFace', '$sLote', CURRENT_DATE, CURRENT_TIME, '".$fcanal."')";
                                        //echo $sql."<br>";
                                        $ret = SQLexecuteQuery($sql);
                                        if(!$ret) $msg = "Erro ao inserir registro: " . $linha . ".\n";
                                        else $contador_PIN_importados++;
                                }
                        } else {
                                $seq++;
                                if (substr($linha, 0, 1) == '0') {
                                    echo "Linha do cabeçalho.<br>";
                                }
                                elseif (substr($linha, 0, 1) == '9') {
                                    echo "Linha do rodapé.<br>";
                                }
                                elseif (strlen($linha) == 0) {
                                    echo "Linha em branco.<br>";
                                }
                        } 
                        //Se houve erro, sai do loop
                        if($msg != "") break;
                }
        }
        echo "Total de PINs Importados: ".number_format($contador_PIN_importados, 0, ",", ".")."<br>";
        //Cria diretorios
        if($msg == ""){
                if(!is_dir($folder)) $msg = "Diretório raiz não existe.\n";
                if($msg == ""){
                        if(!is_dir($folder. $opr_codigo)) 
                                if(!mkdir($folder. $opr_codigo)) $msg = "Não foi possivel criar diretorio da operadora.\n";
                }
        }				
        //move arquivo
        if($msg == ""){
                $fileDest = $folder . $opr_codigo . "/" . $nomeArq; 
                if(!copy($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.\n"; 
                elseif((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo vazio ou inválido.\n";
        }
        //Finaliza transacao
        if($msg == ""){
                $sql = "COMMIT TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if(!$ret) $msg = "Erro ao comitar transação.\n";
        } else {
                $sql = "ROLLBACK TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
        }
        return $msg;
} //end function processaLote_EletronicArts


function XBox_traduzKValor($k){
	$valor["49"] = "49";
	$valor["69"] = "69";
	$valor["119"] = "119";
	
	return $valor[$k];
}

function processaLote_XBox($fileSource, $nomeArq, $opr_codigo, $loteValor, $fcanal){

        global $folder, $opr_codigo_XBox;

        $msg = "";

        //Valida entradas
        $operadoras = array($opr_codigo_XBox);
        if(is_null($fileSource) || trim($fileSource) == "") $msg = "Caminho do arquivo vázio.\n";
        if(is_null($nomeArq) || trim($nomeArq) == "") $msg = "Nome do arquivo vázio.\n";
        if(is_null($opr_codigo) || trim($opr_codigo) == "" || !is_numeric($opr_codigo) || !in_array($opr_codigo, $operadoras)) $msg = "Código da operadora inválido.\n";

        //local
        $iPinLocal = 19;

        //Abre arquivo e le conteudo
        if($msg == ""){
                $handle = fopen($fileSource, "r");
                $carga = fread($handle, filesize($fileSource));
                fclose($handle);

                $cargaAr = explode("\n", $carga);
                if(count($cargaAr) == 0) $msg = "Arquivo vázio.\n";
        } 

        //valida tamanho da linha
        if($msg == ""){
                for($i=0; $i < count($cargaAr); $i++){
                        $cargaAr[$i] = trim($cargaAr[$i]);

                        // tira linha em branco
                        if(trim($cargaAr[$i]) == ""){
                                array_splice($cargaAr, $i, 1); 
                                continue;
                        }
                }
        }


        //Inicia transacao
        if($msg == ""){
                $sql = "BEGIN TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if(!$ret) $msg = "Erro ao iniciar transação.\n";
        }

        //insere lote
        if($msg == ""){

                $seq = 0;
                $valor = 0;
                for($i=0; $i < count($cargaAr); $i++){

                        $linha 		= $cargaAr[$i];

                        //dados				
                        if (strpos($linha, "unidades") === false) { // corpo

                                $linhaAr 	= explode("\t", $linha);
                                $sLote 	= $opr_codigo . date('dm') . $seq;	//	Too big for pin_lote_codigo (integer) in DB: $opr_codigo.date('dmy')."0".$seq;
                                $sPinSerial = $linhaAr[0];
                                $sValorFace = $linhaAr[1]; //$valor;

                                //Validacoes
                                if($sLote == "" || !is_numeric($sLote))
                                        $msg = "Lote inválido: " . $sLote . ".\n";

                                if($sPinSerial == "" || (strlen($sPinSerial)!=25)) 	
                                        $msg = "Pin Serial comprimento inválido: " . $sPinSerial . ".\n";

                                if($sValorFace == "" || !is_numeric($sValorFace))
                                        $msg = "Valor de face inválido: " . $sValorFace . ".\n";

                                //insere pin
                                if($msg == ""){
                                        $sql = "insert into pins (opr_codigo, pin_status, pin_serial, pin_codigo, pin_local, pin_valor, pin_lote_codigo, pin_dataentrada, pin_horaentrada, pin_canal) ";
                                        $sql .= "values ($opr_codigo, 1, '$sPinSerial', '', '$iPinLocal', '$sValorFace', '$sLote', CURRENT_DATE, CURRENT_TIME, '".$fcanal."')";
                                        $ret = SQLexecuteQuery($sql);
                                        if(!$ret) $msg = "Erro ao inserir registro: " . $linha . ".\n";
                                }

                        } else {
                                $seq++;
                                $valor = "";

                                $sai = array("-", " ", "unidades");
                                $entra = array("", "", "");
                                $linhaAr = explode("k", str_replace($sai, $entra, strtolower($linha)));
                                $lote  = $opr_codigo . date('dmy') . "0" . $seq;
                                $valor = Webzen_traduzKValor($linhaAr[0]);
                                $qtd = $linhaAr[1];

                                //Validacoes
                                if($lote == "" || !is_numeric($lote)) 	$msg = "Lote inválido: " . $lote . ".\n";
                                if($valor == "" || !is_numeric($valor)) $msg = "Valor de face inválido: " . $valor . ".\n";

                                if($msg == ""){		    
                                        $sql  = "select count(*) as qtde from pins where opr_codigo = $opr_codigo and pin_lote_codigo = $lote and pin_valor = $valor and pin_canal='s' ";
                                        $rs = SQLexecuteQuery($sql);
                                        $ret = true;
                                        if($rs && pg_num_rows($rs) > 0){
                                                $rs_row = pg_fetch_array($rs);
                                                if($rs_row['qtde'] == 0) $ret = false;
                                        }			
                                        if($ret) $msg = "Arquivo já foi importado.\n";
                                }

                        } 

                        //Se houve erro, sai do loop
                        if($msg != "") break;

                }
        }

        //Cria diretorios
        if($msg == ""){
                if(!is_dir($folder)) $msg = "Diretório raiz não existe.\n";
                if($msg == ""){
                        if(!is_dir($folder. $opr_codigo)) 
                                if(!mkdir($folder. $opr_codigo)) $msg = "Não foi possivel criar diretorio da operadora.\n";
                }
        }				

        //move arquivo
        if($msg == ""){
                $fileDest = $folder . $opr_codigo . "/" . $nomeArq; 
                if(!copy($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.\n"; 
                elseif((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo vazio ou inválido.\n";
        }

        //Finaliza transacao
        if($msg == ""){
                $sql = "COMMIT TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if(!$ret) $msg = "Erro ao comitar transação.\n";
        } else {
                $sql = "ROLLBACK TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
        }

        return $msg;

} //end function processaLote_XBox

function Encripta_traduzKValor($k){
	$valor["16.9"] = "16.9";
	$valor["17"] = "17";
	$valor["19"] = "19";
	$valor["50"] = "50";
	$valor["100"] = "100";
	$valor["200"] = "200";
	return $valor[$k];
}

function processaLote_Encripta($fileSource, $nomeArq, $opr_codigo, $loteValor, $fcanal){

        global $folder, $opr_codigo_Encripta;

        $msg = "";

        //Valida entradas
        $operadoras = array($opr_codigo_Encripta);
        if(is_null($fileSource) || trim($fileSource) == "") $msg = "Caminho do arquivo vázio.\n";
        if(is_null($nomeArq) || trim($nomeArq) == "") $msg = "Nome do arquivo vázio.\n";
        if(is_null($opr_codigo) || trim($opr_codigo) == "" || !is_numeric($opr_codigo) || !in_array($opr_codigo, $operadoras)) $msg = "Código da operadora inválido.\n";

        //local
        $iPinLocal = 20;

        //Abre arquivo e le conteudo
        if($msg == ""){
                $handle = fopen($fileSource, "r");
                $carga = fread($handle, filesize($fileSource));
                fclose($handle);

                $cargaAr = explode("\n", $carga);
                if(count($cargaAr) == 0) $msg = "Arquivo vázio.\n";
        } 

        //valida tamanho da linha
        if($msg == ""){
                for($i=0; $i < count($cargaAr); $i++){
                        $cargaAr[$i] = trim($cargaAr[$i]);

                        // tira linha em branco
                        if(trim($cargaAr[$i]) == ""){
                                array_splice($cargaAr, $i, 1); 
                                continue;
                        }
                }
        }


        //Inicia transacao
        if($msg == ""){
                $sql = "BEGIN TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if(!$ret) $msg = "Erro ao iniciar transação.\n";
        }

        //insere lote
        if($msg == ""){

                $seq = 0;
                $valor = 0;
                for($i=0; $i < count($cargaAr); $i++){

                        $linha 		= $cargaAr[$i];

                        //dados				
                        if (strpos($linha, "unidades") === false) { // corpo

                                $linhaAr 	= explode("\t", $linha);
                                $sLote 	= $opr_codigo . date('dm') . $seq;	//	Too big for pin_lote_codigo (integer) in DB: $opr_codigo.date('dmy')."0".$seq;
                                $sPinSerial = $linhaAr[0];
                                $sValorFace = $linhaAr[1]; //$valor;

                                //Validacoes
                                if($sLote == "" || !is_numeric($sLote))
                                        $msg = "Lote inválido: " . $sLote . ".\n";

                                if($sPinSerial == "" || (strlen($sPinSerial)!=11)) 	
                                        $msg = "Pin Serial comprimento inválido: " . $sPinSerial . ".\n";

                                if($sValorFace == "" || !is_numeric($sValorFace))
                                        $msg = "Valor de face inválido: " . $sValorFace . ".\n";

                                //insere pin
                                if($msg == ""){
                                        $sql = "insert into pins (opr_codigo, pin_status, pin_serial, pin_codigo, pin_local, pin_valor, pin_lote_codigo, pin_dataentrada, pin_horaentrada, pin_canal) ";
                                        $sql .= "values ($opr_codigo, 1, '$sPinSerial', '', '$iPinLocal', '$sValorFace', '$sLote', CURRENT_DATE, CURRENT_TIME, '".$fcanal."')";
                                        $ret = SQLexecuteQuery($sql);
                                        if(!$ret) $msg = "Erro ao inserir registro: " . $linha . ".\n";
                                }

                        } else {
                                $seq++;
                                $valor = "";

                                $sai = array("-", " ", "unidades");
                                $entra = array("", "", "");
                                $linhaAr = explode("k", str_replace($sai, $entra, strtolower($linha)));
                                $lote  = $opr_codigo . date('dmy') . "0" . $seq;
                                $valor = Encripta_traduzKValor($linhaAr[0]);
                                $qtd = $linhaAr[1];

                                //Validacoes
                                if($lote == "" || !is_numeric($lote)) 	$msg = "Lote inválido: " . $lote . ".\n";
                                if($valor == "" || !is_numeric($valor)) $msg = "Valor de face inválido: " . $valor . ".\n";

                                if($msg == ""){		    
                                        $sql  = "select count(*) as qtde from pins where opr_codigo = $opr_codigo and pin_lote_codigo = $lote and pin_valor = $valor and pin_canal='s' ";
                                        $rs = SQLexecuteQuery($sql);
                                        $ret = true;
                                        if($rs && pg_num_rows($rs) > 0){
                                                $rs_row = pg_fetch_array($rs);
                                                if($rs_row['qtde'] == 0) $ret = false;
                                        }			
                                        if($ret) $msg = "Arquivo já foi importado.\n";
                                }

                        } 

                        //Se houve erro, sai do loop
                        if($msg != "") break;

                }
        }

        //Cria diretorios
        if($msg == ""){
                if(!is_dir($folder)) $msg = "Diretório raiz não existe.\n";
                if($msg == ""){
                        if(!is_dir($folder. $opr_codigo)) 
                                if(!mkdir($folder. $opr_codigo)) $msg = "Não foi possivel criar diretorio da operadora.\n";
                }
        }				

        //move arquivo
        if($msg == ""){
                $fileDest = $folder . $opr_codigo . "/" . $nomeArq; 
                if(!copy($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.\n"; 
                elseif((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo vazio ou inválido.\n";
        }

        //Finaliza transacao
        if($msg == ""){
                $sql = "COMMIT TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if(!$ret) $msg = "Erro ao comitar transação.\n";
        } else {
                $sql = "ROLLBACK TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
        }

        return $msg;

} //end function processaLote_Encripta

function Valvesoftware_traduzKValor($k){
	$valor["23"] = "23";
	$valor["35"] = "35";
	return $valor[$k];
}

function processaLote_Valvesoftware($fileSource, $nomeArq, $opr_codigo, $loteValor, $fcanal){

        global $folder, $opr_codigo_Valvesoftware;

        $msg = "";

        //Valida entradas
        $operadoras = array($opr_codigo_Valvesoftware);
        if(is_null($fileSource) || trim($fileSource) == "") $msg = "Caminho do arquivo vázio.\n";
        if(is_null($nomeArq) || trim($nomeArq) == "") $msg = "Nome do arquivo vázio.\n";
        if(is_null($opr_codigo) || trim($opr_codigo) == "" || !is_numeric($opr_codigo) || !in_array($opr_codigo, $operadoras)) $msg = "Código da operadora inválido.\n";

        //local
        $iPinLocal = 23;

        //Abre arquivo e le conteudo
        if($msg == ""){
                $handle = fopen($fileSource, "r");
                $carga = fread($handle, filesize($fileSource));
                fclose($handle);

                $cargaAr = explode("\n", $carga);
                if(count($cargaAr) == 0) $msg = "Arquivo vázio.\n";
        } 

        //valida tamanho da linha
        if($msg == ""){
                for($i=0; $i < count($cargaAr); $i++){
                        $cargaAr[$i] = trim($cargaAr[$i]);

                        // tira linha em branco
                        if(trim($cargaAr[$i]) == ""){
                                array_splice($cargaAr, $i, 1); 
                                continue;
                        }
                }
        }


        //Inicia transacao
        if($msg == ""){
                $sql = "BEGIN TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if(!$ret) $msg = "Erro ao iniciar transação.\n";
        }

        //insere lote
        if($msg == ""){

                $seq = 0;
                $valor = 0;
                for($i=0; $i < count($cargaAr); $i++){

                        $linha 		= $cargaAr[$i];

                        //dados				
                        if (strpos($linha, "unidades") === false) { // corpo

                                $linhaAr 	= explode("\t", $linha);
                                $sLote 	= $opr_codigo . date('dm') . $seq;	//	Too big for pin_lote_codigo (integer) in DB: $opr_codigo.date('dmy')."0".$seq;
                                $sPinSerial = $linhaAr[0];
                                $sValorFace = $linhaAr[1]; //$valor;

                                //Validacoes
                                if($sLote == "" || !is_numeric($sLote))
                                        $msg = "Lote inválido: " . $sLote . ".\n";

                                if($sPinSerial == "" || (strlen($sPinSerial)!=17)) 	
                                        $msg = "Pin Serial comprimento inválido: " . $sPinSerial . ".\n";

                                if($sValorFace == "" || !is_numeric($sValorFace))
                                        $msg = "Valor de face inválido: " . $sValorFace . ".\n";

                                //insere pin
                                if($msg == ""){
                                        $sql = "insert into pins (opr_codigo, pin_status, pin_serial, pin_codigo, pin_local, pin_valor, pin_lote_codigo, pin_dataentrada, pin_horaentrada, pin_canal) ";
                                        $sql .= "values ($opr_codigo, 1, '$sPinSerial', '', '$iPinLocal', '$sValorFace', '$sLote', CURRENT_DATE, CURRENT_TIME, '".$fcanal."')";
                                        $ret = SQLexecuteQuery($sql);
                                        if(!$ret) $msg = "Erro ao inserir registro: " . $linha . ".\n";
                                }

                        } else {
                                $seq++;
                                $valor = "";

                                $sai = array("-", " ", "unidades");
                                $entra = array("", "", "");
                                $linhaAr = explode("k", str_replace($sai, $entra, strtolower($linha)));
                                $lote  = $opr_codigo . date('dmy') . "0" . $seq;
                                $valor = Valvesoftware_traduzKValor($linhaAr[0]);
                                $qtd = $linhaAr[1];

                                //Validacoes
                                if($lote == "" || !is_numeric($lote)) 	$msg = "Lote inválido: " . $lote . ".\n";
                                if($valor == "" || !is_numeric($valor)) $msg = "Valor de face inválido: " . $valor . ".\n";

                                if($msg == ""){		    
                                        $sql  = "select count(*) as qtde from pins where opr_codigo = $opr_codigo and pin_lote_codigo = $lote and pin_valor = $valor and pin_canal='s' ";
                                        $rs = SQLexecuteQuery($sql);
                                        $ret = true;
                                        if($rs && pg_num_rows($rs) > 0){
                                                $rs_row = pg_fetch_array($rs);
                                                if($rs_row['qtde'] == 0) $ret = false;
                                        }			
                                        if($ret) $msg = "Arquivo já foi importado.\n";
                                }

                        } 

                        //Se houve erro, sai do loop
                        if($msg != "") break;

                }
        }

        //Cria diretorios
        if($msg == ""){
                if(!is_dir($folder)) $msg = "Diretório raiz não existe.\n";
                if($msg == ""){
                        if(!is_dir($folder. $opr_codigo)) 
                                if(!mkdir($folder. $opr_codigo)) $msg = "Não foi possivel criar diretorio da operadora.\n";
                }
        }				

        //move arquivo
        if($msg == ""){
                $fileDest = $folder . $opr_codigo . "/" . $nomeArq; 
                if(!copy($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.\n"; 
                elseif((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo vazio ou inválido.\n";
        }

        //Finaliza transacao
        if($msg == ""){
                $sql = "COMMIT TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if(!$ret) $msg = "Erro ao comitar transação.\n";
        } else {
                $sql = "ROLLBACK TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
        }

        return $msg;

} //end function processaLote_Valvesoftware

function G2A_traduzKValor($k){
	$valor["5.32"] = "5.32";
	$valor["6"] = "6";
	$valor["10.64"] = "10.64";
	$valor["11"] = "11";
	$valor["26.60"] = "26.60";
	$valor["27"] = "27";
	$valor["53.19"] = "53.19";
	$valor["54"] = "54";
	$valor["106.38"] = "106.38";
	$valor["107"] = "107";
	
	return $valor[$k];
}

function processaLote_G2A($fileSource, $nomeArq, $opr_codigo, $loteValor, $fcanal){
	
        global $folder, $opr_codigo_G2A, $opr_codigo_G2A_2;

        $msg = "";

        //Valida entradas
        $operadoras = array($opr_codigo_G2A, $opr_codigo_G2A_2);
        if(is_null($fileSource) || trim($fileSource) == "") $msg = "Caminho do arquivo vázio.\n";
        if(is_null($nomeArq) || trim($nomeArq) == "") $msg = "Nome do arquivo vázio.\n";
        if(is_null($opr_codigo) || trim($opr_codigo) == "" || !is_numeric($opr_codigo) || !in_array($opr_codigo, $operadoras)) $msg = "Código da operadora inválido.\n";

        //local
        $iPinLocal = 37;


        //Abre arquivo e le conteudo
        if($msg == ""){
                $handle = fopen($fileSource, "r");
                $carga = fread($handle, filesize($fileSource));
                fclose($handle);

                $cargaAr = explode("\n", $carga);
                if(count($cargaAr) == 0) $msg = "Arquivo vázio.\n";
        } 


        //valida tamanho da linha
        if($msg == ""){
                for($i=0; $i < count($cargaAr); $i++){
                        $cargaAr[$i] = trim($cargaAr[$i]);

                        // tira linha em branco
                        if(trim($cargaAr[$i]) == ""){
                                array_splice($cargaAr, $i, 1); 
                                continue;
                        }
                }
        }
		

        //Inicia transacao
        if($msg == ""){
                $sql = "BEGIN TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if(!$ret) $msg = "Erro ao iniciar transação.\n";
        }

		//insere lote
        if($msg == ""){

                $seq = 0;
                $valor = 0;
                for($i=0; $i < count($cargaAr); $i++){

                        $linha 		= $cargaAr[$i];

                        //dados				
                        if (strpos($linha, "unidades") === false) { // corpo

                                $linhaAr 	= explode("\t", $linha);
                                $sLote 		= $opr_codigo . date('dm') . $seq;	//	Too big for pin_lote_codigo (integer) in DB: $opr_codigo.date('dmy')."0".$seq;
                                $sPinSerial = $linhaAr[0];
                                $sValorFace = $linhaAr[1]; //$valor;
                                
                                //Validacoes
                                if($sLote == "" || !is_numeric($sLote)) 			$msg = "Lote inválido: " . $sLote . ".\n";

                                if($sPinSerial == "" || (strlen($sPinSerial)!=24)) 	
                                        $msg = "Pin Serial comprimento inválido: " . $sPinSerial . ".\n";
                                
                                if($sValorFace == "" || !is_numeric($sValorFace)) 	$msg = "Valor de face inválido: " . $sValorFace . ".\n";

                                //insere pin
                                if($msg == ""){
                                        $sql = "insert into pins (opr_codigo, pin_status, pin_serial, pin_codigo, pin_local, pin_valor, pin_lote_codigo, pin_dataentrada, pin_horaentrada, pin_canal) ";
                                        $sql .= "values ($opr_codigo, 1, '', '$sPinSerial', '$iPinLocal', '$sValorFace', '$sLote', CURRENT_DATE, CURRENT_TIME, '".$fcanal."')";
                                        $ret = SQLexecuteQuery($sql);
                                        if(!$ret) $msg = "Erro ao inserir registro: " . $linha . ".\n";
                                }

                        } else {
                                $seq++;
                                $valor = "";

                                $sai = array("-", " ", "unidades");
                                $entra = array("", "", "");
                                $linhaAr = explode("k", str_replace($sai, $entra, strtolower($linha)));
                                $lote  = $opr_codigo . date('dmy') . "0" . $seq;
                                $valor = G2A_traduzKValor($linhaAr[0]);
                                $qtd = $linhaAr[1];

                                //Validacoes
                                if($lote == "" || !is_numeric($lote)) 	$msg = "Lote inválido: " . $lote . ".\n";
                                if($valor == "" || !is_numeric($valor)) $msg = "Valor de face inválido: " . $valor . ".\n";

                                if($msg == ""){		    
                                        $sql  = "select count(*) as qtde from pins where opr_codigo = $opr_codigo and pin_lote_codigo = $lote and pin_valor = $valor and pin_canal='s' ";
                                        $rs = SQLexecuteQuery($sql);
                                        $ret = true;
                                        if($rs && pg_num_rows($rs) > 0){
                                                $rs_row = pg_fetch_array($rs);
                                                if($rs_row['qtde'] == 0) $ret = false;
                                        }			
                                        if($ret) $msg = "Arquivo já foi importado.\n";
                                }

                        } 

                        //Se houve erro, sai do loop
                        if($msg != "") break;

                }
        }

        //Cria diretorios
        if($msg == ""){
                if(!is_dir($folder)) $msg = "Diretório raiz não existe.\n";
                if($msg == ""){
                        if(!is_dir($folder. $opr_codigo)) 
                                if(!mkdir($folder. $opr_codigo)) $msg = "Não foi possivel criar diretorio da operadora.\n";
                }
        }				

        //move arquivo
        if($msg == ""){
                $fileDest = $folder . $opr_codigo . "/" . $nomeArq; 
                if(!copy($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.\n"; 
                elseif((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo vazio ou inválido.\n";
        }

        //Finaliza transacao
        if($msg == ""){
                $sql = "COMMIT TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if(!$ret) $msg = "Erro ao comitar transação.\n";
        } else {
                $sql = "ROLLBACK TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
        }

        return $msg;
		
}

function processaLote_NoPing($fileSource, $nomeArq, $opr_codigo, $loteValor, $fcanal){
	
        global $folder, $opr_codigo_NoPing;

        $msg = "";

        //Valida entradas
        $operadoras = array($opr_codigo_NoPing);
        if(is_null($fileSource) || trim($fileSource) == "") $msg = "Caminho do arquivo vázio.\n";
        if(is_null($nomeArq) || trim($nomeArq) == "") $msg = "Nome do arquivo vázio.\n";
        if(is_null($opr_codigo) || trim($opr_codigo) == "" || !is_numeric($opr_codigo) || !in_array($opr_codigo, $operadoras)) $msg = "Código da operadora inválido.\n";

        //local
        $iPinLocal = 37;


        //Abre arquivo e le conteudo
        if($msg == ""){
                $handle = fopen($fileSource, "r");
                $carga = fread($handle, filesize($fileSource));
                fclose($handle);

                $cargaAr = explode("\n", $carga);
                if(count($cargaAr) == 0) $msg = "Arquivo vázio.\n";
        } 


        //valida tamanho da linha
        if($msg == ""){
                for($i=0; $i < count($cargaAr); $i++){
                        $cargaAr[$i] = trim($cargaAr[$i]);

                        // tira linha em branco
                        if(trim($cargaAr[$i]) == ""){
                                array_splice($cargaAr, $i, 1); 
                                continue;
                        }
                }
        }
		

        //Inicia transacao
        if($msg == ""){
                $sql = "BEGIN TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if(!$ret) $msg = "Erro ao iniciar transação.\n";
        }

		//insere lote
        if($msg == ""){

                $seq = 0;
                $valor = 0;
                for($i=0; $i < count($cargaAr); $i++){

                        $linha 		= $cargaAr[$i];

                        //dados				
                        if (strpos($linha, "unidades") === false) { // corpo

                                $linhaAr 	= explode("\t", $linha);
                                $sLote 		= $opr_codigo . date('dm') . $seq;	//	Too big for pin_lote_codigo (integer) in DB: $opr_codigo.date('dmy')."0".$seq;
                                $sPinSerial = $linhaAr[0];
                                $sValorFace = $linhaAr[1]; //$valor;
                                
                                //Validacoes
                                if($sLote == "" || !is_numeric($sLote)) 			$msg = "Lote inválido: " . $sLote . ".\n";

                                if($sPinSerial == "" || (strlen($sPinSerial)!=23)) 	
                                        $msg = "Pin Serial comprimento inválido: " . $sPinSerial . ".\n";
                                
                                if($sValorFace == "" || !is_numeric($sValorFace)) 	$msg = "Valor de face inválido: " . $sValorFace . ".\n";

                                //insere pin
                                if($msg == ""){
                                        $sql = "insert into pins (opr_codigo, pin_status, pin_serial, pin_codigo, pin_local, pin_valor, pin_lote_codigo, pin_dataentrada, pin_horaentrada, pin_canal) ";
                                        $sql .= "values ($opr_codigo, 1, '', '$sPinSerial', '$iPinLocal', '$sValorFace', '$sLote', CURRENT_DATE, CURRENT_TIME, '".$fcanal."')";
                                        $ret = SQLexecuteQuery($sql);
                                        if(!$ret) $msg = "Erro ao inserir registro: " . $linha . ".\n";
                                }

                        } else {
                                $seq++;
                                $valor = "";

                                $sai = array("-", " ", "unidades");
                                $entra = array("", "", "");
                                $linhaAr = explode("k", str_replace($sai, $entra, strtolower($linha)));
                                $lote  = $opr_codigo . date('dmy') . "0" . $seq;
                                $valor = G2A_traduzKValor($linhaAr[0]);
                                $qtd = $linhaAr[1];

                                //Validacoes
                                if($lote == "" || !is_numeric($lote)) 	$msg = "Lote inválido: " . $lote . ".\n";
                                if($valor == "" || !is_numeric($valor)) $msg = "Valor de face inválido: " . $valor . ".\n";

                                if($msg == ""){		    
                                        $sql  = "select count(*) as qtde from pins where opr_codigo = $opr_codigo and pin_lote_codigo = $lote and pin_valor = $valor and pin_canal='s' ";
                                        $rs = SQLexecuteQuery($sql);
                                        $ret = true;
                                        if($rs && pg_num_rows($rs) > 0){
                                                $rs_row = pg_fetch_array($rs);
                                                if($rs_row['qtde'] == 0) $ret = false;
                                        }			
                                        if($ret) $msg = "Arquivo já foi importado.\n";
                                }

                        } 

                        //Se houve erro, sai do loop
                        if($msg != "") break;

                }
        }

        //Cria diretorios
        if($msg == ""){
                if(!is_dir($folder)) $msg = "Diretório raiz não existe.\n";
                if($msg == ""){
                        if(!is_dir($folder. $opr_codigo)) 
                                if(!mkdir($folder. $opr_codigo)) $msg = "Não foi possivel criar diretorio da operadora.\n";
                }
        }				

        //move arquivo
        if($msg == ""){
                $fileDest = $folder . $opr_codigo . "/" . $nomeArq; 
                if(!copy($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.\n"; 
                elseif((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo vazio ou inválido.\n";
        }

        //Finaliza transacao
        if($msg == ""){
                $sql = "COMMIT TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if(!$ret) $msg = "Erro ao comitar transação.\n";
        } else {
                $sql = "ROLLBACK TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
        }

        return $msg;
		
}

function Rimo_traduzKValor($k){
	$valor["50"] = "50";
	$valor["60"] = "60";
	$valor["100"] = "100";
	$valor["160"] = "160";
	
	return $valor[$k];
}

function processaLote_Rimo($fileSource, $nomeArq, $opr_codigo, $loteValor, $fcanal){
	
        global $folder, $opr_codigo_Rimo, $opr_codigo_Rimo1, $opr_codigo_Rimo2;

        $msg = "";

        //Valida entradas
        $operadoras = array($opr_codigo_Rimo, $opr_codigo_Rimo1, $opr_codigo_Rimo2);
        if(is_null($fileSource) || trim($fileSource) == "") $msg = "Caminho do arquivo vázio.\n";
        if(is_null($nomeArq) || trim($nomeArq) == "") $msg = "Nome do arquivo vázio.\n";
        if(is_null($opr_codigo) || trim($opr_codigo) == "" || !is_numeric($opr_codigo) || !in_array($opr_codigo, $operadoras)) $msg = "Código da operadora inválido.\n";

        //local
        $iPinLocal = 38;


        //Abre arquivo e le conteudo
        if($msg == ""){
                $handle = fopen($fileSource, "r");
                $carga = fread($handle, filesize($fileSource));
                fclose($handle);

                $cargaAr = explode("\n", $carga);
                if(count($cargaAr) == 0) $msg = "Arquivo vázio.\n";
        } 


        //valida tamanho da linha
        if($msg == ""){
                for($i=0; $i < count($cargaAr); $i++){
                        $cargaAr[$i] = trim($cargaAr[$i]);

                        // tira linha em branco
                        if(trim($cargaAr[$i]) == ""){
                                array_splice($cargaAr, $i, 1); 
                                continue;
                        }
                }
        }
		

        //Inicia transacao
        if($msg == ""){
                $sql = "BEGIN TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if(!$ret) $msg = "Erro ao iniciar transação.\n";
        }

		//insere lote
        if($msg == ""){

                $seq = 0;
                $valor = 0;
                for($i=0; $i < count($cargaAr); $i++){

                        $linha 		= $cargaAr[$i];

                        //dados				
                        if (strpos($linha, "unidades") === false) { // corpo

                                $linhaAr 	= explode("\t", $linha);
                                $sLote 		= $opr_codigo . date('dm') . $seq;	//	Too big for pin_lote_codigo (integer) in DB: $opr_codigo.date('dmy')."0".$seq;
                                $sPinSerial = $linhaAr[0];
                                $sValorFace = $linhaAr[1]; //$valor;
                                
                                //Validacoes
                                if($sLote == "" || !is_numeric($sLote)) 			$msg = "Lote inválido: " . $sLote . ".\n";

                                if($sPinSerial == "" || (strlen($sPinSerial)!=23)) 	
                                        $msg = "Pin Serial comprimento inválido: " . $sPinSerial . ".\n";
                                
                                if($sValorFace == "" || !is_numeric($sValorFace)) 	$msg = "Valor de face inválido: " . $sValorFace . ".\n";

                                //insere pin
                                if($msg == ""){
                                        $sql = "insert into pins (opr_codigo, pin_status, pin_serial, pin_codigo, pin_local, pin_valor, pin_lote_codigo, pin_dataentrada, pin_horaentrada, pin_canal) ";
                                        $sql .= "values ($opr_codigo, 1, '', '$sPinSerial', '$iPinLocal', '$sValorFace', '$sLote', CURRENT_DATE, CURRENT_TIME, '".$fcanal."')";
                                        $ret = SQLexecuteQuery($sql);
                                        if(!$ret) $msg = "Erro ao inserir registro: " . $linha . ".\n";
                                }

                        } else {
                                $seq++;
                                $valor = "";

                                $sai = array("-", " ", "unidades");
                                $entra = array("", "", "");
                                $linhaAr = explode("k", str_replace($sai, $entra, strtolower($linha)));
                                $lote  = $opr_codigo . date('dmy') . "0" . $seq;
                                $valor = Rimo_traduzKValor($linhaAr[0]);
                                $qtd = $linhaAr[1];

                                //Validacoes
                                if($lote == "" || !is_numeric($lote)) 	$msg = "Lote inválido: " . $lote . ".\n";
                                if($valor == "" || !is_numeric($valor)) $msg = "Valor de face inválido: " . $valor . ".\n";

                                if($msg == ""){		    
                                        $sql  = "select count(*) as qtde from pins where opr_codigo = $opr_codigo and pin_lote_codigo = $lote and pin_valor = $valor and pin_canal='s' ";
                                        $rs = SQLexecuteQuery($sql);
                                        $ret = true;
                                        if($rs && pg_num_rows($rs) > 0){
                                                $rs_row = pg_fetch_array($rs);
                                                if($rs_row['qtde'] == 0) $ret = false;
                                        }			
                                        if($ret) $msg = "Arquivo já foi importado.\n";
                                }

                        } 

                        //Se houve erro, sai do loop
                        if($msg != "") break;

                }
        }

        //Cria diretorios
        if($msg == ""){
                if(!is_dir($folder)) $msg = "Diretório raiz não existe.\n";
                if($msg == ""){
                        if(!is_dir($folder. $opr_codigo)) 
                                if(!mkdir($folder. $opr_codigo)) $msg = "Não foi possivel criar diretorio da operadora.\n";
                }
        }				

        //move arquivo
        if($msg == ""){
                $fileDest = $folder . $opr_codigo . "/" . $nomeArq; 
                if(!copy($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.\n"; 
                elseif((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo vazio ou inválido.\n";
        }

        //Finaliza transacao
        if($msg == ""){
                $sql = "COMMIT TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if(!$ret) $msg = "Erro ao comitar transação.\n";
        } else {
                $sql = "ROLLBACK TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
        }

        return $msg;
		
}

function HabboHotel_2_traduzKValor($k){
	$valor["9.98"] = "9.98";
	$valor["9.99"] = "9.99";
	$valor["12.90"] = "12.90";
	$valor["13"] = "13";
	$valor["14.90"] = "14.90";
	$valor["16.90"] = "16.90";
	$valor["32.90"] = "32.90";
	$valor["33"] = "33";
	$valor["37.90"] = "37.90";
	$valor["72.90"] = "72.90";
	$valor["73"] = "73";
	$valor["89.90"] = "89.90";
	$valor["90"] = "90";
	$valor["129.90"] = "129.90";
	$valor["130"] = "130";
	$valor["134.90"] = "134.90";
	$valor["135"] = "135";
	$valor["149.90"] = "149.90";
        return $valor[$k];
}


function processaLote_HabboHotel_2($fileSource, $nomeArq, $opr_codigo, $loteValor, $fcanal){
	
        global $folder, $opr_codigo_HabboHotel_2;

        $msg = "";

        //Valida entradas
        $operadoras = array($opr_codigo_HabboHotel_2);
        if(is_null($fileSource) || trim($fileSource) == "") $msg = "Caminho do arquivo vázio.\n";
        if(is_null($nomeArq) || trim($nomeArq) == "") $msg = "Nome do arquivo vázio.\n";
        if(is_null($opr_codigo) || trim($opr_codigo) == "" || !is_numeric($opr_codigo) || !in_array($opr_codigo, $operadoras)) $msg = "Código da operadora inválido.\n";

        //local
        $iPinLocal = 39;

        //Abre arquivo e le conteudo
        if($msg == ""){
                $handle = fopen($fileSource, "r");
                $carga = fread($handle, filesize($fileSource));
                fclose($handle);

                $cargaAr = explode("\n", $carga);
                if(count($cargaAr) == 0) $msg = "Arquivo vázio.\n";
        } 


        //valida tamanho da linha
        if($msg == ""){
                for($i=0; $i < count($cargaAr); $i++){
                        $cargaAr[$i] = trim($cargaAr[$i]);

                        // tira linha em branco
                        if(trim($cargaAr[$i]) == ""){
                                array_splice($cargaAr, $i, 1); 
                                continue;
                        }
                }
        }
		

        //Inicia transacao
        if($msg == ""){
                $sql = "BEGIN TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if(!$ret) $msg = "Erro ao iniciar transação.\n";
        }

		//insere lote
        if($msg == ""){

                $seq = 0;
                $valor = 0;
                for($i=0; $i < count($cargaAr); $i++){

                        $linha 		= $cargaAr[$i];

                        //dados				
                        if (strpos($linha, "unidades") === false) { // corpo

                                $linhaAr 	= explode("\t", $linha);
                                $sLote 		= $opr_codigo . date('dm') . $seq;	//	Too big for pin_lote_codigo (integer) in DB: $opr_codigo.date('dmy')."0".$seq;
                                $sPinSerial = $linhaAr[0];
                                $sValorFace = $linhaAr[1]; //$valor;
                                
                                //Validacoes
                                if($sLote == "" || !is_numeric($sLote)) 			$msg = "Lote inválido: " . $sLote . ".\n";

                                if($sPinSerial == "" || (strlen($sPinSerial)!=8)) 	
                                        $msg = "Pin Serial comprimento inválido: " . $sPinSerial . ".\n";
                                
                                if($sValorFace == "" || !is_numeric($sValorFace)) 	$msg = "Valor de face inválido: " . $sValorFace . ".\n";

                                //insere pin
                                if($msg == ""){
                                        $sql = "insert into pins (opr_codigo, pin_status, pin_serial, pin_codigo, pin_local, pin_valor, pin_lote_codigo, pin_dataentrada, pin_horaentrada, pin_canal) ";
                                        $sql .= "values ($opr_codigo, 1, '', '$sPinSerial', '$iPinLocal', '$sValorFace', '$sLote', CURRENT_DATE, CURRENT_TIME, '".$fcanal."')";
                                        $ret = SQLexecuteQuery($sql);
                                        if(!$ret) $msg = "Erro ao inserir registro: " . $linha . ".\n";
                                }

                        } else {
                                $seq++;
                                $valor = "";

                                $sai = array("-", " ", "unidades");
                                $entra = array("", "", "");
                                $linhaAr = explode("k", str_replace($sai, $entra, strtolower($linha)));
                                $lote  = $opr_codigo . date('dmy') . "0" . $seq;
                                $valor = HabboHotel_2_traduzKValor($linhaAr[0]);
                                $qtd = $linhaAr[1];

                                //Validacoes
                                if($lote == "" || !is_numeric($lote)) 	$msg = "Lote inválido: " . $lote . ".\n";
                                if($valor == "" || !is_numeric($valor)) $msg = "Valor de face inválido: " . $valor . ".\n";

                                if($msg == ""){		    
                                        $sql  = "select count(*) as qtde from pins where opr_codigo = $opr_codigo and pin_lote_codigo = $lote and pin_valor = $valor and pin_canal='s' ";
                                        $rs = SQLexecuteQuery($sql);
                                        $ret = true;
                                        if($rs && pg_num_rows($rs) > 0){
                                                $rs_row = pg_fetch_array($rs);
                                                if($rs_row['qtde'] == 0) $ret = false;
                                        }			
                                        if($ret) $msg = "Arquivo já foi importado.\n";
                                }

                        } 

                        //Se houve erro, sai do loop
                        if($msg != "") break;

                }
        }

        //Cria diretorios
        if($msg == ""){
                if(!is_dir($folder)) $msg = "Diretório raiz não existe.\n";
                if($msg == ""){
                        if(!is_dir($folder. $opr_codigo)) 
                                if(!mkdir($folder. $opr_codigo)) $msg = "Não foi possivel criar diretorio da operadora.\n";
                }
        }				

        //move arquivo
        if($msg == ""){
                $fileDest = $folder . $opr_codigo . "/" . $nomeArq; 
                if(!copy($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.\n"; 
                elseif((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo vazio ou inválido.\n";
        }

        //Finaliza transacao
        if($msg == ""){
                $sql = "COMMIT TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if(!$ret) $msg = "Erro ao comitar transação.\n";
        } else {
                $sql = "ROLLBACK TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
        }

        return $msg;
		
}


function processaLote_SurfTelecom($fileSource, $nomeArq, $opr_codigo, $fcanal){
	
        global $folder, $opr_codigo_SurfTelecom;

        $msg = "";

        //Valida entradas
        $operadoras = array($opr_codigo_SurfTelecom);
        if(is_null($fileSource) || trim($fileSource) == "") $msg = "Caminho do arquivo vázio.".PHP_EOL;
        if(is_null($nomeArq) || trim($nomeArq) == "") $msg = "Nome do arquivo vázio.".PHP_EOL;
        if(is_null($opr_codigo) || trim($opr_codigo) == "" || !is_numeric($opr_codigo) || !in_array($opr_codigo, $operadoras)) $msg = "Código da operadora inválido.".PHP_EOL;

        //local
        $iPinLocal = 40;

        //Abre arquivo e le conteudo
        if($msg == ""){
                $handle = fopen($fileSource, "r");
                $carga = fread($handle, filesize($fileSource));
                fclose($handle);

                $cargaAr = explode("\n", $carga);
                if(count($cargaAr) == 0) $msg = "Arquivo vázio.".PHP_EOL;
        } 


        //valida tamanho da linha
        if($msg == ""){
                for($i=0; $i < count($cargaAr); $i++){
                        $cargaAr[$i] = trim($cargaAr[$i]);

                        // tira linha em branco
                        if(trim($cargaAr[$i]) == ""){
                                array_splice($cargaAr, $i, 1); 
                                continue;
                        }
                }
        }

        //Inicia transacao
        if($msg == ""){
                $sql = "BEGIN TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if(!$ret) $msg = "Erro ao iniciar transação.".PHP_EOL;
        }

        //insere lote
        if($msg == ""){

                $seq = 0;
                $valor = 0;
                for($i=0; $i < count($cargaAr); $i++){

                        $linha 		= $cargaAr[$i];

                        //dados				
                        $linhaAr 	= explode(",", $linha);
                        $sLote 		= $opr_codigo . date('dm') . $seq;	//	Too big for pin_lote_codigo (integer) in DB: $opr_codigo.date('dmy')."0".$seq;
                        $sSerial	= $linhaAr[2];
                        $sPinSerial	= $linhaAr[3];
                        $sValorFace	= $linhaAr[4]; //$valor;

                        //Validacoes
                        if($sLote == "" || !is_numeric($sLote)) 			$msg = "Lote inválido: " . $sLote . ".".PHP_EOL;

                        if($sPinSerial == "") 	
                                $msg = "Pin Serial comprimento inválido: " . $sPinSerial . ".".PHP_EOL;

                        if($sValorFace == "" || !is_numeric($sValorFace)) 	$msg = "Valor de face inválido: " . $sValorFace . ".".PHP_EOL;

                        //insere pin
                        if($msg == ""){
                                $sql = "insert into pins (opr_codigo, pin_status, pin_serial, pin_codigo, pin_local, pin_valor, pin_lote_codigo, pin_dataentrada, pin_horaentrada, pin_canal) ";
                                $sql .= "values ($opr_codigo, 1, '$sSerial', '$sPinSerial', '$iPinLocal', '$sValorFace', '$sLote', CURRENT_DATE, CURRENT_TIME, '".$fcanal."')";
                                $ret = SQLexecuteQuery($sql);
                                if(!$ret) $msg = "Erro ao inserir registro: " . $linha . ".".PHP_EOL;
                        }

                        //Se houve erro, sai do loop
                        if($msg != "") break;

                }
        }

        //Cria diretorios
        if($msg == ""){
                if(!is_dir($folder)) $msg = "Diretório raiz não existe.".PHP_EOL;
                if($msg == ""){
                        if(!is_dir($folder. $opr_codigo)) 
                                if(!mkdir($folder. $opr_codigo)) $msg = "Não foi possivel criar diretorio da operadora.".PHP_EOL;
                }
        }				

        //move arquivo
        if($msg == ""){
                $fileDest = $folder . $opr_codigo . "/" . $nomeArq; 
                if(!copy($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.".PHP_EOL; 
                elseif((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo vazio ou inválido.".PHP_EOL;
        }

        //Finaliza transacao
        if($msg == ""){
                $sql = "COMMIT TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if(!$ret) $msg = "Erro ao comitar transação.".PHP_EOL;
        } else {
                $sql = "ROLLBACK TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if(!$ret) $msg = "Erro ao dar rollback na transação.".PHP_EOL;
        }

        return $msg;
		
} //end function processaLote_SurfTelecom(

function ExitLag_traduzKValor($k){
	$valor["1"] = "1";
	return $valor[$k];
}

function processaLote_ExitLag($fileSource, $nomeArq, $opr_codigo, $fcanal){

        global $folder, $opr_codigo_exitlag;
        $msg = "";
        //Valida entradas
        $operadoras = array($opr_codigo_exitlag);
        if(is_null($fileSource) || trim($fileSource) == "") $msg = "Caminho do arquivo vázio.\n";
        if(is_null($nomeArq) || trim($nomeArq) == "") $msg = "Nome do arquivo vázio.\n";
        if(is_null($opr_codigo) || trim($opr_codigo) == "" || !is_numeric($opr_codigo) || !in_array($opr_codigo, $operadoras)) $msg = "Código da operadora inválido.\n";

        //local
        $iPinLocal = 32;

        //Abre arquivo e le conteudo
        if($msg == ""){
                $handle = fopen($fileSource, "r");
                $carga = fread($handle, filesize($fileSource));
                fclose($handle);

                $cargaAr = explode("\n", $carga);
                if(count($cargaAr) == 0) $msg = "Arquivo vázio.\n";
        } 

        // Debug
		//for($i=0; $i < count($cargaAr); $i++){
		//	echo "cargaAr[$i]: '".$cargaAr[$i]."'<br>";
		//}
		//		$msg = "";

        //valida tamanho da linha
        if($msg == ""){
                for($i=0; $i < count($cargaAr); $i++){
                        $cargaAr[$i] = trim($cargaAr[$i]);

                        // tira linha em branco
                        if(trim($cargaAr[$i]) == ""){
                                array_splice($cargaAr, $i, 1); 
                                continue;
                        }
/*
                        //Valida tamanho da linha
                        $num_posicoes = 34;
                        if(strlen($cargaAr[$i]) != $num_posicoes){
                                $msg = "linha " . ($i+1) . " do arquivo não tem $num_posicoes posições, possui " . strlen($cargaAr[$i]) . ":\n";
                                $msg .= "**" . $cargaAr[$i] . "**\n";
                                break;
                        }
*/
                }
        }


        //Inicia transacao
        if($msg == ""){
                $sql = "BEGIN TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if(!$ret) $msg = "Erro ao iniciar transação.\n";
        }

        //insere lote
        if($msg == ""){

                $seq = 0;
                $valor = 0;
                for($i=0; $i < count($cargaAr); $i++){

                        $linha 		= $cargaAr[$i];

                        //dados				
                        if (strpos($linha, "unidades") === false) { // corpo

                                $linhaAr 	= explode("\t", $linha);
                                $sLote 		= $opr_codigo . date('dmy') . "0" . $seq;
                                $sPinSerial = trim($linhaAr[0]);
                                $sValorFace = $linhaAr[1]; //$valor;

                                 //echo "[sLote: '$sLote'] [sPinSerial: '$sPinSerial'] [sPinCodigo: '$sPinCodigo'] [sValorFace: '$sValorFace']<br>";
								 //exit;

                                //Validacoes
                                if($sLote == "" || !is_numeric($sLote)) 			$msg = "Lote inválido: " . $sLote . ".\n";

                                if($sPinSerial == "" || (strlen($sPinSerial)!=16))	
                                        $msg = "Pin Serial comprimento inválido: " . $sPinSerial . ".\n";
                                
                                if($sValorFace == "" || !is_numeric($sValorFace)) 	$msg = "Valor de face inválido: " . $sValorFace . ".\n";

                                //insere pin
                                if($msg == ""){
										$sql = "insert into pins (opr_codigo, pin_status, pin_serial, pin_codigo, pin_local, pin_valor, pin_lote_codigo, pin_dataentrada, pin_horaentrada, pin_canal) ";
                                        $sql .= "values ($opr_codigo, 1, '', '$sPinSerial', '$iPinLocal', '$sValorFace', '$sLote', CURRENT_DATE, CURRENT_TIME, '".$fcanal."')";
                                        $ret = SQLexecuteQuery($sql);
                                        if(!$ret) $msg = "Erro ao inserir registro: " . $linha . ".\n";
                                }

                        } else {
                                $seq++;
                                $valor = "";

                                $sai = array("-", " ", "unidades");
                                $entra = array("", "", "");
                                $linhaAr = explode("k", str_replace($sai, $entra, strtolower($linha)));
                                $lote  = $opr_codigo . date('dmy') . "0" . $seq;
                                $valor = ExitLag_traduzKValor($linhaAr[0]);
                                $qtd = $linhaAr[1];

                                //Validacoes
                                if($lote == "" || !is_numeric($lote)) 	$msg = "Lote inválido: " . $lote . ".\n";
                                if($valor == "" || !is_numeric($valor)) $msg = "Valor de face inválido: " . $valor . ".\n";

                                if($msg == ""){		    
                                        $sql  = "select count(*) as qtde from pins where opr_codigo = $opr_codigo and pin_lote_codigo = $lote and pin_valor = $valor and pin_canal='s' ";
                                        $rs = SQLexecuteQuery($sql);
                                        $ret = true;
                                        if($rs && pg_num_rows($rs) > 0){
                                                $rs_row = pg_fetch_array($rs);
                                                if($rs_row['qtde'] == 0) $ret = false;
                                        }			
                                        if($ret) $msg = "Arquivo já foi importado.\n";
                                }

                        } 

                        //Se houve erro, sai do loop
                        if($msg != "") break;

                }
        }


        //Cria diretorios
        if($msg == ""){
                if(!is_dir($folder)) $msg = "Diretório raiz não existe.\n";
                if($msg == ""){
                        if(!is_dir($folder. $opr_codigo)) 
                                if(!mkdir($folder. $opr_codigo)) $msg = "Não foi possivel criar diretorio da operadora.\n";
                }
        }				

        //move arquivo
        if($msg == ""){
                $fileDest = $folder . $opr_codigo . "/" . $nomeArq; 
                if(!copy($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.\n"; 
                elseif((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo vazio ou inválido.\n";
        }

        //Finaliza transacao
        if($msg == ""){
                $sql = "COMMIT TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if(!$ret) $msg = "Erro ao comitar transação.\n";
        } else {
                $sql = "ROLLBACK TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
        }

        return $msg;

}

function Tinder_traduzKValor($k){
	$valor["19"] = "19";
	$valor["30"] = "30";
	$valor["76"] = "76";
	$valor["115"] = "115";
	$valor["170"] = "170";
	return $valor[$k];
}

function processaLote_Tinder($fileSource, $nomeArq, $opr_codigo, $fcanal){

        global $folder, $opr_codigo_Tinder_1, $opr_codigo_Tinder_2;

        $msg = "";

        //Valida entradas
        $operadoras = array($opr_codigo_Tinder_1, $opr_codigo_Tinder_2);
        if(is_null($fileSource) || trim($fileSource) == "") $msg = "Caminho do arquivo vázio.\n";
        if(is_null($nomeArq) || trim($nomeArq) == "") $msg = "Nome do arquivo vázio.\n";
        if(is_null($opr_codigo) || trim($opr_codigo) == "" || !is_numeric($opr_codigo) || !in_array($opr_codigo, $operadoras)) $msg = "Código da operadora inválido.\n";

        //local
        $iPinLocal = 50;


        //Abre arquivo e le conteudo
        if($msg == ""){
                $handle = fopen($fileSource, "r");
                $carga = fread($handle, filesize($fileSource));
                fclose($handle);

                $cargaAr = explode("\n", $carga);
                if(count($cargaAr) == 0) $msg = "Arquivo vázio.\n";
        } 

        // Debug
//		for($i=0; $i < count($cargaAr); $i++){
//			echo "cargaAr[$i]: '".$cargaAr[$i]."'<br>";
//		}
//		$msg = "";

        //valida tamanho da linha
        if($msg == ""){
                for($i=0; $i < count($cargaAr); $i++){
                        $cargaAr[$i] = trim($cargaAr[$i]);

                        // tira linha em branco
                        if(trim($cargaAr[$i]) == ""){
                                array_splice($cargaAr, $i, 1); 
                                continue;
                        }
/*
                        //Valida tamanho da linha
                        $num_posicoes = 34;
                        if(strlen($cargaAr[$i]) != $num_posicoes){
                                $msg = "linha " . ($i+1) . " do arquivo não tem $num_posicoes posições, possui " . strlen($cargaAr[$i]) . ":\n";
                                $msg .= "**" . $cargaAr[$i] . "**\n";
                                break;
                        }
*/
                }
        }


        //Inicia transacao
        if($msg == ""){
                $sql = "BEGIN TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if(!$ret) $msg = "Erro ao iniciar transação.\n";
        }

        //insere lote
        if($msg == ""){

                $seq = 0;
                $valor = 0;
                for($i=0; $i < count($cargaAr); $i++){

                        $linha 		= $cargaAr[$i];

                        //dados				
                        if (strpos($linha, "unidades") === false) { // corpo

                                $linhaAr 	= explode("\t", $linha);
                                $sLote 		= $opr_codigo . date('dmy') . "0" . $seq;
                                $sPinSerial = trim($linhaAr[0]);
                                $sValorFace = $linhaAr[1]; //$valor;

//echo "[sLote: '$sLote'] [sPinSerial: '$sPinSerial'] [sPinCodigo: '$sPinCodigo'] [sValorFace: '$sValorFace']<br>";

                                //Validacoes
                                if($sLote == "" || !is_numeric($sLote)) 			$msg = "Lote inválido: " . $sLote . ".\n";

                                if($sPinSerial == "" || (strlen($sPinSerial)!=36)) 	
                                        $msg = "Pin Serial comprimento inválido: " . $sPinSerial . ".\n";
                                
                                if($sValorFace == "" || !is_numeric($sValorFace)) 	$msg = "Valor de face inválido: " . $sValorFace . ".\n";

                                //insere pin
                                if($msg == ""){
                                        $sql = "insert into pins (opr_codigo, pin_status, pin_serial, pin_codigo, pin_local, pin_valor, pin_lote_codigo, pin_dataentrada, pin_horaentrada, pin_canal) ";
                                        $sql .= "values ($opr_codigo, 1, '', '$sPinSerial', '$iPinLocal', '$sValorFace', '$sLote', CURRENT_DATE, CURRENT_TIME, '".$fcanal."')";
                                        $ret = SQLexecuteQuery($sql);
                                        if(!$ret) $msg = "Erro ao inserir registro: " . $linha . ".\n";
                                }

                        } else {
                                $seq++;
                                $valor = "";

                                $sai = array("-", " ", "unidades");
                                $entra = array("", "", "");
                                $linhaAr = explode("k", str_replace($sai, $entra, strtolower($linha)));
                                $lote  = $opr_codigo . date('dmy') . "0" . $seq;
                                $valor = Tinder_traduzKValor($linhaAr[0]);
                                $qtd = $linhaAr[1];

                                //Validacoes
                                if($lote == "" || !is_numeric($lote)) 	$msg = "Lote inválido: " . $lote . ".\n";
                                if($valor == "" || !is_numeric($valor)) $msg = "Valor de face inválido: " . $valor . ".\n";

                                if($msg == ""){		    
                                        $sql  = "select count(*) as qtde from pins where opr_codigo = $opr_codigo and pin_lote_codigo = $lote and pin_valor = $valor and pin_canal='s' ";
                                        $rs = SQLexecuteQuery($sql);
                                        $ret = true;
                                        if($rs && pg_num_rows($rs) > 0){
                                                $rs_row = pg_fetch_array($rs);
                                                if($rs_row['qtde'] == 0) $ret = false;
                                        }			
                                        if($ret) $msg = "Arquivo já foi importado.\n";
                                }

                        } 

                        //Se houve erro, sai do loop
                        if($msg != "") break;

                }
        }


        //Cria diretorios
        if($msg == ""){
                if(!is_dir($folder)) $msg = "Diretório raiz não existe.\n";
                if($msg == ""){
                        if(!is_dir($folder. $opr_codigo)) 
                                if(!mkdir($folder. $opr_codigo)) $msg = "Não foi possivel criar diretorio da operadora.\n";
                }
        }				

        //move arquivo
        if($msg == ""){
                $fileDest = $folder . $opr_codigo . "/" . $nomeArq; 
                if(!copy($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.\n"; 
                elseif((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo vazio ou inválido.\n";
        }

        //Finaliza transacao
        if($msg == ""){
                $sql = "COMMIT TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if(!$ret) $msg = "Erro ao comitar transação.\n";
        } else {
                $sql = "ROLLBACK TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
        }

        return $msg;

}

?>
