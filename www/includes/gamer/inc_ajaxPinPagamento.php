<?php
/**********************************************************************************************
************** Arquivo contem funções referente  ao Ajax_PIN_Pagamento.php ********************
**********************************************************************************************/

function getSaldoUsuarioFunc() {
	$ug_perfil_saldo_aux = 0;
	$usuarioGames = unserialize($GLOBALS['_SESSION']['usuarioGames_ser']);

	$sql_function ="SELECT ug_perfil_saldo from usuarios_games where ug_id=".intval($usuarioGames->ug_id).";";
	gravaLog_EPPCASH("SQL para buscar o saldo do usuario:\n   $sql_function");
	$rs_saldo_function = SQLexecuteQuery($sql_function);
	if($rs_saldo_function_row = pg_fetch_array($rs_saldo_function)) {
		$ug_perfil_saldo_aux = $rs_saldo_function_row['ug_perfil_saldo'];
	}
	return $ug_perfil_saldo_aux;
}

//Função que verifica se o tamanho do PIN pertence à um PIN EPP CASH
function RetonaTamanhoPINEPPCASH($pin) {
	$tamanho = strlen($pin);
	if($tamanho == $GLOBALS['PIN_STORE_TAMANHO']) {
		//instanciando a classe de cryptografia
		$chave256bits = new Chave();
		$aes = new AES($chave256bits->retornaChave());
		//Teste existencia na tabela de exceção de gocash com tamanho de 16
		$sql = "select * from pins_gocash_lote16 where pgcl_pin_number_encrypt = '".base64_encode($aes->encrypt(addslashes($pin)))."'";
		$rs_pins_gocash = SQLexecuteQuery($sql);
		if($rs_pins_gocash && pg_num_rows($rs_pins_gocash) == 0) {
			return true;
		}
		else {
			return false;
		}
	}
	else {
		return false;
	}
}//end function RetonaTamanhoPINEPPCASH($pin)

//Função que calcula a composição do pagamento EPP
function RetonaComposicaoPagamento($valor_compra,$valor_saldo,$valor_carrinho_pin_epp,$valor_carrinho_pin_gocash) {
	$retorno = array (
					'SALDO_FINAL'				=> 0,
					'TOTAL_SALDO_UTILIZADO'		=> 0,
					'TOTAL_PIN_EPP_UTILIZADO'	=> 0,
					'TOTAL_PIN_GOCASH_UTILIZADO'=> 0,
					'RESTO_PIN_EPP_DEPOSITO'	=> 0,
					'RESTO_PIN_GOCASH_DEPOSITO' => 0,
					);
	if($valor_compra <= $valor_carrinho_pin_epp+$valor_carrinho_pin_gocash+$valor_saldo) {

		//Utilizando os PINs EPP CASH
		$valor_compra_aux = $valor_compra;
		$valor_compra -= $valor_carrinho_pin_epp;
		if($valor_compra <= 0) {				
			$retorno['SALDO_FINAL']					= number_format( ( ($valor_carrinho_pin_epp-$valor_compra_aux) + $valor_carrinho_pin_gocash + $valor_saldo), 2, '.', '');
			$retorno['TOTAL_SALDO_UTILIZADO']		= 0;
			$retorno['TOTAL_PIN_EPP_UTILIZADO']		= number_format( $valor_compra_aux, 2, '.', '');
			$retorno['TOTAL_PIN_GOCASH_UTILIZADO']	= 0;
			$retorno['RESTO_PIN_EPP_DEPOSITO']		= number_format( ($valor_carrinho_pin_epp-$valor_compra_aux), 2, '.', '');
			$retorno['RESTO_PIN_GOCASH_DEPOSITO']	= number_format( $valor_carrinho_pin_gocash, 2, '.', '');
		}//end if($valor_compra <= 0) PINs EPP CASH
		else {

			//Utilizando os PINs GoCASH
			$valor_compra_aux = $valor_compra;
			$valor_compra -= $valor_carrinho_pin_gocash;
			if($valor_compra <= 0) {
				$retorno['SALDO_FINAL']					=  number_format( (($valor_carrinho_pin_gocash-$valor_compra_aux) + $valor_saldo), 2, '.', '');
				$retorno['TOTAL_SALDO_UTILIZADO']		= 0;
				$retorno['TOTAL_PIN_EPP_UTILIZADO']		=  number_format( $valor_carrinho_pin_epp, 2, '.', '');
				$retorno['TOTAL_PIN_GOCASH_UTILIZADO']	=  number_format( $valor_compra_aux, 2, '.', '');
				$retorno['RESTO_PIN_EPP_DEPOSITO']		= 0;
				$retorno['RESTO_PIN_GOCASH_DEPOSITO']	=  number_format(  ($valor_carrinho_pin_gocash-$valor_compra_aux), 2, '.', '');
			}//end if($valor_compra <= 0) PINs GoCASH
			else {

				//Utilizando o Saldo
				$valor_compra_aux = $valor_compra;
				$valor_compra -= $valor_saldo;
				if($valor_compra <= 0) {
					$retorno['SALDO_FINAL']					=  number_format( ($valor_saldo-$valor_compra_aux), 2, '.', '');
					$retorno['TOTAL_SALDO_UTILIZADO']		=  number_format( $valor_compra_aux, 2, '.', '');
					$retorno['TOTAL_PIN_EPP_UTILIZADO']		=  number_format( $valor_carrinho_pin_epp, 2, '.', '');
					$retorno['TOTAL_PIN_GOCASH_UTILIZADO']	=  number_format( $valor_carrinho_pin_gocash, 2, '.', '');
					$retorno['RESTO_PIN_EPP_DEPOSITO']		= 0;
					$retorno['RESTO_PIN_GOCASH_DEPOSITO']	= 0;
				}//end if($valor_compra <= 0) Saldo
				else {
					$usuarioGamesAux = unserialize($_SESSION['usuarioGames_ser']);
					gravaLog_EPPCASH("(ug_id: ".$usuarioGamesAux->getId().") - Abortado: Sobrou valor da Compra COD: [EGS-].\nValor da compra INICIAL: $valor_compra_aux\nValor da compra que SOBROU: $valor_compra\nValor do Saldo: $valor_saldo\nValor PIN EPP: $valor_carrinho_pin_epp\nValor GoCASH: $valor_carrinho_pin_gocash");
					die("Problema no processamento! Execute a compra novamente! COD: [EGS-].");
				}
				
			}//end else if($valor_compra <= 0) PINs GoCASH

		}//end else if($valor_compra <= 0) PINs EPP CASH

	} //end if($valor_compra <= $valor_carrinho_pin_epp+$valor_carrinho_pin_gocash+$valor_saldo) 
	else {
		$usuarioGamesAux = unserialize($_SESSION['usuarioGames_ser']);
		gravaLog_EPPCASH("(ug_id: ".$usuarioGamesAux->getId().") - Abortado: Este usuário tentou efetuar uma compra de valor maior que seu Saldo e PINs.\nValor da compra: $valor_compra\nValor passado por POST: ". $GLOBALS['_POST']['vl_compra'] ." \nVerificar o valor da compra: [".$GLOBALS['_SESSION']['venda']."] = ID da compra na SESSION\nValor do Saldo: $valor_saldo\nValor PIN EPP: $valor_carrinho_pin_epp\nValor GoCASH: $valor_carrinho_pin_gocash");
		die("Problema no processamento! Execute a compra novamente!");
	}
	
	return $retorno;
}//end function RetonaComposicaoPagamento($valor_compra,$valor_saldo,$valor_carrinho_pin_epp,$valor_carrinho_pin_gocash)

//Função que retorna o SQL para depósito em saldo do resto de PIN
function RetonaSQLVendaDeposito($vetor) {
        //Variáveis necessárias para detecção de origem Drupal da prdem
        $varDrupal = $GLOBALS['_SESSION']['drupal_order_id']*1+$GLOBALS['_SESSION']['drupal_deposit']*1;
        //A multiplicação por 100 é apneas um artefato para resolver a divisão inicial do EPP CASH por 100
	$valorEPPCASH = $vetor['VALOR_DEP']*100;
	$valorMoeda = (new ConversionPINsEPP)->get_Valor('E',$valorEPPCASH);

	$sql_credito_venda = "insert into tb_venda_games (" .
		"vg_id, vg_ug_id, vg_data_inclusao, vg_pagto_tipo, " .
		"vg_ultimo_status, vg_ultimo_status_obs, vg_http_referer_origem, vg_http_referer,".
		"vg_concilia, vg_data_concilia, vg_pagto_data, vg_pagto_data_inclusao,". 
		"vg_deposito_em_saldo, vg_valor_eppcash,";
        // Marcando ordem como de origem Drupal
        if($varDrupal > 0) {
            $sql_credito_venda .= " vg_drupal,";
        }//end if($varDrupal > 0)
        
        $sql_credito_venda .= " vg_deposito_em_saldo_valor) values (";
	
	$sql_credito_venda .= SQLaddFields($vetor['NOVO_ID'], "") . ",";
	$sql_credito_venda .= SQLaddFields($vetor['UG_ID'], "") . ",";
	$sql_credito_venda .= SQLaddFields("CURRENT_TIMESTAMP", "") . ",";
	$sql_credito_venda .= SQLaddFields($GLOBALS['PAGAMENTO_PIN_EPREPAG_NUMERIC'], "") . ",";

	$sql_credito_venda .= SQLaddFields($GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'], "") . ",";
	$sql_credito_venda .= SQLaddFields("Depósito em Saldo de resto de pagamento com PINs EPP Cash", "s") . ", ";
	$sql_credito_venda .= SQLaddFields($GLOBALS['_SESSION']['epp_origem'], "s") . ", ";
	$sql_credito_venda .= SQLaddFields($GLOBALS['_SESSION']['epp_origem_referer'], "s") . ", ";

	$sql_credito_venda .= SQLaddFields("1", "") .",";
	$sql_credito_venda .= SQLaddFields("CURRENT_TIMESTAMP", "") . ",";
	$sql_credito_venda .= SQLaddFields("CURRENT_TIMESTAMP", "") . ",";
	$sql_credito_venda .= SQLaddFields("CURRENT_TIMESTAMP", "") . ","; 

	$sql_credito_venda .= SQLaddFields("1", "") .",";
	$sql_credito_venda .= SQLaddFields($valorEPPCASH, "") .",";
        // Marcando ordem como de origem Drupal
        if($varDrupal > 0) {
            $sql_credito_venda .= SQLaddFields("1", "") .",";
        }//end if($varDrupal > 0)
	$sql_credito_venda .= SQLaddFields($valorMoeda, "").")";
	
	//retornando o SQL montado
	return $sql_credito_venda;
}//end function RetonaSQLVendaDeposito($vetor)

//Função que executa o depósito de gocash no saldo
function deposita_em_saldo($vetor, &$msg2) {
	$sql_credito_venda = RetonaSQLVendaDeposito($vetor);
	$ret_venda = SQLexecuteQuery($sql_credito_venda);
	if(!$ret_venda) {
		$msg2 .= "Erro ao inserir venda de crédito no saldo. Por favor, tente novamente atualizando a página. Obrigado 214-EPP.\n";
		gravaLog_EPPCASH($msg2);
		return false;
	}
	else {
		$orderId = get_newOrderID();
		$usuarioGames = unserialize($GLOBALS['_SESSION']['usuarioGames_ser']);
		$sql_credito = "INSERT INTO tb_pag_compras (numcompra, idvenda, cliente_nome, idcliente, tipo_cliente, frete, manuseio, taxas, subtotal, cesta, tipoPagto, prazo, numParcelas, valorParcela, total, dataInicio, status, iforma, banco, tipo_deposito, status_processed, datacompra, dataconfirma, valorpagtogocash, valorpagtopin, idvenda_origem) values ('".$orderId."',".$vetor['NOVO_ID'].", '".$usuarioGames->ug_sNome."', ".$usuarioGames->ug_id.", 'M', 0, 0, 0, 0, 'Depósito em Saldo com Resto de PIN Cash C (13)', 0, 0, 0, 0, ".((new ConversionPINsEPP)->get_Valor('E', number_format( ($vetor['VALOR_DEP']*100), 0, ',', ''))*100).", CURRENT_TIMESTAMP, 3, '".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIN_EPREPAG']."', '".$GLOBALS['PAGAMENTO_PIN_EPP_COD_BANCO']."', ";
		if($vetor['ID_ORIGEM']==0) {
			//$GLOBALS['TIPO_DEPOSITO']['DEPOSITO_DIRETO_COM_PAGAMENTO'] é colocado para identificar como deposito direto por conta do rollback da transação e deposito em saldo somente do gocash
			$sql_credito .= $GLOBALS['TIPO_DEPOSITO']['DEPOSITO_DIRETO_COM_PAGAMENTO'];
		}
		else {
			$sql_credito .= $GLOBALS['TIPO_DEPOSITO']['DEPOSITO_RESTO_PINS'];
		}
		$sql_credito .= ", 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, ".number_format($vetor['VALOR_GOCASH'], 2, '.', '').", ".number_format($vetor['VALOR_EPPCASH'], 2, '.', '').", ".$vetor['ID_ORIGEM'].")";
		$ret = SQLexecuteQuery($sql_credito);
		if(!$ret) {
			$msg2 .= "Erro ao inserir compra de crédito no saldo. Por favor, tente novamente atualizando a página. Obrigado 217.\n";
			gravaLog_EPPCASH($msg2);
			return false;
		}//end if(!$ret)
		else {
			gravaLog_EPPCASH("SUCESSO no depósito em saldo no valor de [".$vetor['VALOR_DEP']."]");
			return true;
		}//end else
	}//end else do if(!$ret_venda)
}//end function deposita_em_saldo($vetor)

function deposita_gocash_rollback($vetor,$a_pins_gocash,&$valor_saldo_rollback,&$msg2){
	//Inicia transacao
	$sql = "BEGIN TRANSACTION ";
	$ret = SQLexecuteQuery($sql);
	if(!$ret) $msg2 = "<font color='#FF0000'><b>Erro ao iniciar transa&cceil;&atilde;o.\n</b></font><br>";
	$valor_saldo_rollback_tmp = $valor_saldo_rollback;
	if($msg2 == "") {
		if(deposita_em_saldo($vetor,$msg2)) {
			$valor_saldo_rollback += $vetor['VALOR_DEP'];
			$usuarioGames = unserialize($GLOBALS['_SESSION']['usuarioGames_ser']);
			foreach($GLOBALS['_SESSION']['PINEPP'] as $key => $value) {
				//Testando se é PIN CASH
				if(RetonaTamanhoPINGoCASH($key)) {

						//Canal definido como Cartão
						$canal		= 'C';
						//Definindo comissão
						$comissao	= $GLOBALS['GOCASH_CUSTO'];		//15;
							// Temporário até garantir que $GOCASH_CUSTO está definido aqui
							if(!$comissao) $comissao = 15;
						
						$valor_composicao	= $value;

						$maior['canal']		= $canal;
						$maior['id']		= 0;
						$maior['comissao']	= $comissao; 
						$maior['financial']	= 0;
						
						if(!insereSaldoComposicaoFifo($valor_composicao,$canal,$comissao,$vetor['NOVO_ID'])) {
							$msg2 .= "Erro ao inserir a composição do saldo. Por favor, tente novamente atualizando a página. Obrigado 218.\n";
						}
					
				}//end elseif(RetonaTamanhoPINGoCASH)
			}//end foreach($GLOBALS['_SESSION']['PINEPP'] as $key => $value)
					
			if($msg2 == "") {
				//inserir na nova tabela o registro de maior comissão
				if(!insereRegistro_tb_venda_games_pinepp_origem($maior,$vetor['NOVO_ID'])) {
					 $msg2 .= "<font color='#FF0000'><b>Erro ao inserir o Registro do Canal Venda PIN Cash (".$maior['canal'].").\n</b></font><br>";
				}
				else {
					$sql ="UPDATE usuarios_games SET ug_perfil_saldo=".$valor_saldo_rollback." where ug_id=".intval($usuarioGames->ug_id);
					gravaLog_EPPCASH("SQL que atualiza registro do usuario na tabela usuarios_games (ROLLBACK):\n$sql");
					$rs_saldo = SQLexecuteQuery($sql);
					if(!$rs_saldo) {
						 $msg2 .= "<font color='#FF0000'><b>Erro ao atualizar o Saldo do Usu&aacute;rio .\n</b></font><br>";
					}//end if(!$rs_saldo)
					else {
						saveTransactionGoCASH($a_pins_gocash, $vetor['NOVO_ID']);
						echo "Ops, ocorreu um ERRO.\n<br>
							Ok: Cartão EPPCash foi ativado com sucesso em Saldo.\n<br>
							ERRO: EPPCash PIN NÃO utilizado. Faça novo pedido utilizando este PIN e seu Saldo para pagar. ";
						gravaLog_EPPCASH("SUCESSO no DEPÓSITO de GOCASH no Saldo dentro do ROLLBACK!.\n");
					}//end else do if(!$rs_saldo)
				}//end else do if(!$rs_tvgpo)
			}//end if($msg2 == "")
		}//end if(deposita_em_saldo($vetor,$msg2))
	}//end if($msg2 == "")
	if($msg2 == ""){
		$sql = "COMMIT TRANSACTION ";
		$ret = SQLexecuteQuery($sql);
		if(!$ret) $msg2 .= "<font color='#FF0000'><b>Erro ao comitar transa&ccedil;&atilde;o do rollback.\n<br></b></font><br>";
		else return true;
	} else {
		$sql = "ROLLBACK TRANSACTION ";
		$ret = SQLexecuteQuery($sql);
		if(!$ret) $msg2 .= "<font color='#FF0000'><b>Erro ao dar rollback no rollback na transa&ccedil;&atilde;o.\n<br></b></font><br>";
		else {
			if ($valor_saldo_rollback_tmp != $valor_saldo_rollback) {
				$valor_saldo_rollback -= $vetor['VALOR_DEP'];
			}
			return false;
		}
	}
}//end function deposita_gocash_rollback


function saveTransactionGoCASH($a_pins_gocash, $venda_id){
	//echo "a_pins_gocash:<pre>".print_r($a_pins_gocash,true)."</pre>ID VENDA[".$venda_id."]<br>";
	$i = 0;
	$usuarioGames = unserialize($GLOBALS['_SESSION']['usuarioGames_ser']);
	//echo "ID USER: [".$usuarioGames->ug_id."]<br>";
	foreach($a_pins_gocash as $pin => $pin_valor_nominal) {
		//echo "DENTRO FOREACH<br>";
		$order_no = str_pad($venda_id , 8, "0", STR_PAD_LEFT) ."_". str_pad($i, 2, "0", STR_PAD_LEFT);

		$params = array();
		$params['PinNumber'] = $pin;
		$params['FaceAmount'] = $pin_valor_nominal;
		$params['Currency'] = "BRL";
		$params['IDVenda'] = $venda_id;
		$params['OrderNo'] = $order_no;
		$params['IDUsuario'] = $usuarioGames->ug_id;
		$params['RespDate'] = date("Y-m-d H:i:s");

		//echo "params:<pre>".print_r($params,true)."</pre>";
	

		if (class_exists('GoCashAPI')) {
			gravaLog_EPPCASH("EXISTE a classe gocash do ROLLBACK!.\n");
			$gc = new GoCashAPI();
		}
		else {
			gravaLog_EPPCASH("NÃO EXISTE a classe gocash do ROLLBACK!.\n");
		}
		if(method_exists($gc,'saveSoapTransaction')) {
			gravaLog_EPPCASH("EXISTE o metodo da classe gocash do ROLLBACK!.\n");
			if($gc->saveSoapTransaction($params)) {
				gravaLog_EPPCASH("SUCESSO na utilização do metodo da classe gocash do ROLLBACK!.\n");
			}
			else {
				gravaLog_EPPCASH("ERRO na utilização do metodo da classe gocash do ROLLBACK!.\n");
			}
		}//end if
		else {
			gravaLog_EPPCASH("NÃO EXISTE o metodo da classe gocash do ROLLBACK!.\n");
		}

		$i++;
	}//end foreach
}//end function saveTransactionGoCASH

function gravaLog_EPPCASH($mensagem){
	
		//Arquivo
		$file = RAIZ_DO_PROJETO . "log/log_sql_EPP_CASH.txt";
	
		//Mensagem
		$mensagem =  str_repeat("-", 80)."\n".date('Y-m-d H:i:s'). " " .$GLOBALS['_SERVER']['SCRIPT_FILENAME'] . "\n" . $mensagem . "\n";
		//Grava mensagem no arquivo
		if ($handle = fopen($file, 'a+')) {
			fwrite($handle, $mensagem);
			fclose($handle);
		} 
	
}//end function gravaLog_EPPCASH

function retorna_remote_addr() {
	if (isset($_SERVER)) {
		$ip = ((isset($_SERVER['REMOTE_ADDR']))?$_SERVER['REMOTE_ADDR']:"");
   } else {
		$ip = getenv('REMOTE_ADDR');
   }
   return $ip;
}

function retorna_http_client_ip() {
	if (isset($_SERVER)) {
		$ip = ((isset($_SERVER['HTTP_CLIENT_IP']))?$_SERVER['HTTP_CLIENT_IP']:"");
   } else {
		$ip = getenv('HTTP_CLIENT_IP');
   }
   return $ip;
}

function retorna_http_x_forwarded_for() {
	if (isset($_SERVER)) {
		$ip = ((isset($_SERVER['HTTP_X_FORWARDED_FOR']))?$_SERVER['HTTP_X_FORWARDED_FOR']:"");
   } else {
		$ip = getenv('HTTP_X_FORWARDED_FOR');
   }
   return $ip;
}

function retorna_id_pin($pin) {
	//instanciando a classe de cryptografia
	$chave256bits = new Chave();
	$aes = new AES($chave256bits->retornaChave());
	$sql = "select pin_codinterno from pins_store where pin_codigo = '".base64_encode($aes->encrypt(addslashes($pin)))."'";
	$rs_log = SQLexecuteQuery($sql);
	if(!$rs_log) {
	}
	else { 
		$rs_log_row = pg_fetch_array($rs_log);
		if ($rs_log_row['pin_codinterno'] != '')
			return $rs_log_row['pin_codinterno'];
		// informa zero (0) quando nao foi encontrado -- ATENCAUN
		else return '0';
	}
}

function retorna_distribuidor_pin($pin) {
	//instanciando a classe de cryptografia
	$chave256bits = new Chave();
	$aes = new AES($chave256bits->retornaChave());
	$sql = "select distributor_codigo from pins_store where pin_codigo = '".base64_encode($aes->encrypt(addslashes($pin)))."'";
	$rs_log = SQLexecuteQuery($sql);
	if(!$rs_log) {
	}
	else { 
		$rs_log_row = pg_fetch_array($rs_log);
		if ($rs_log_row['distributor_codigo'] != '')
			return $rs_log_row['distributor_codigo'];
		// informa zero (0) quando nao foi encontrado -- ATENCAUN
		else return '0';
	}
}

function retorna_status($pin) {
	//instanciando a classe de cryptografia
	$chave256bits = new Chave();
	$aes = new AES($chave256bits->retornaChave());
	$sql = "select pin_status from pins_store where pin_codigo = '".base64_encode($aes->encrypt(addslashes($pin)))."'";
	$rs_log = SQLexecuteQuery($sql);
	if(!$rs_log) {
	}
	else { 
		$rs_log_row = pg_fetch_array($rs_log);
		if ($rs_log_row['pin_status'] != '')
			return $rs_log_row['pin_status'];
		// informa zero (0) quando nao foi encontrado -- ATENCAUN
		else return '0';
	}
}

function log_pin($acao,$query,$pin) {
	$usuarioGames = unserialize($GLOBALS['_SESSION']['usuarioGames_ser']);
	if (retorna_id_pin(addslashes($pin))=='')
		$aux_id_pin = '0';
	else $aux_id_pin = retorna_id_pin(addslashes($pin));
	$sql = "INSERT INTO pins_store_apl_historico VALUES (NOW(),".intval($usuarioGames->ug_id).",'".retorna_ip_acesso_new()."','".addslashes($acao)."','".str_replace("'",'"',$query)."',".retorna_status(addslashes($pin)).",".intval($aux_id_pin).",'".retorna_remote_addr()."','".retorna_http_client_ip()."','".retorna_http_x_forwarded_for()."')";
	//echo $sql." -- ".retorna_id_pin(addslashes($pin))." -- ".retorna_status(addslashes($pin))."<br>";
	$rs_log = SQLexecuteQuery($sql);
	if(!$rs_log) {
		 echo "<font color='#FF0000'><b>Erro na gera&ccedil;&atilde;o de LOG.\n</b></font><br>";
	}
}

function permite_tentativas($quantidade,$tempo,&$msg_ajax) {
	$usuarioGames = unserialize($GLOBALS['_SESSION']['usuarioGames_ser']);
	$sql = "select count(*) as total from pins_store_apl_historico where psah_autor=".intval($usuarioGames->ug_id)." and psah_ip_autor='".retorna_ip_acesso_new()."' and psah_acao='1' and psah_data >= (NOW()-'".intval($tempo)." minutes'::interval)";
	//echo $sql;
	$rs_log = SQLexecuteQuery($sql);
	if($rs_log) {
		$rs_log_row = pg_fetch_array($rs_log);
		if ($rs_log_row['total'] >= $quantidade) {
			$msg_ajax = "Voc&ecirc; excedeu o n&uacute;mero de tentativas em um intervalo de tempo.<br>Por favor, aguarde ".intval($tempo)." minutos antes de tentar inserir o PIN novamente.<br><br>Em caso de d&uacute;vidas entre em contato com o <a href='mailto:suporte@e-prepag.com.br'>suporte@e-prepag.com.br</a>";
			gravaLog_EPPCASH("Excedeu o numero de tentativas:\n IP: ".retorna_ip_acesso_new()."\n Acao: 1\n Data no Instante: ".date('Y-m-d H:i:s')."\n Intevalo em Minutos considerado: $tempo\n(ug_id: ".$usuarioGames->getId().")");
			return false;
		}
		// retorna true quando atende a restrição
		else return true;
	}
}

function addContadorVezCarrinho($cod_pin) {
	//instanciando a classe de cryptografia
	$chave256bits = new Chave();
	$aes = new AES($chave256bits->retornaChave());
	$sql = "UPDATE pins_store SET pin_qtde_carrinho = pin_qtde_carrinho+1  WHERE pin_codigo='".base64_encode($aes->encrypt(addslashes($cod_pin)))."' ";
	gravaLog_EPPCASH("Contabilizando o numero de vezes do PIN no carrinho. \n $sql \n");
	$rs_oper = SQLexecuteQuery($sql);
	if(!$rs_oper) {
		gravaLog_EPPCASH("NAUNN Contabilizado.\n");
	}
	else {
		gravaLog_EPPCASH("Contabilizado.\n");
	}
} //end function addContadorVezCarrinho

function valida_pin($cod_pin, $geralog = null) {
	global $PINS_STORE_STATUS_VALUES,$PINS_STORE_MSG_LOG_STATUS; 
	sleep(1);
	if (!empty($cod_pin)) {
		//instanciando a classe de cryptografia
		$chave256bits = new Chave();
		$aes = new AES($chave256bits->retornaChave());
		$sql = "select pin_codigo,pin_valor from pins_store where pin_codigo='".base64_encode($aes->encrypt(addslashes($cod_pin)))."' and pin_status='".intval($PINS_STORE_STATUS_VALUES['A'])."'";
		
		$ff = fopen("/www/log/erroFile.txt","a+");
		fwrite($ff, $sql.date('Y-m-d H:i:s')."\r");
		fclose($ff);
		
		$rs_oper = SQLexecuteQuery($sql);
		if(!$rs_oper || pg_num_rows($rs_oper) == 0) {
			if (is_null($geralog))
				log_pin($PINS_STORE_MSG_LOG_STATUS['ERRO_VALIDACAO'],$sql,$cod_pin);
			return -1;
		} else {
			$rs_oper_row = pg_fetch_array($rs_oper);
			if (is_null($geralog))
				log_pin($PINS_STORE_MSG_LOG_STATUS['SUCESSO_VALIDACAO'],$sql,$cod_pin);
			return $rs_oper_row['pin_valor'];
		}
	}
	else return -1;
}

function valida_vencimento_pin($cod_pin, $geralog = null) {
	global $PINS_STORE_STATUS_VALUES,$PINS_STORE_MSG_LOG_STATUS; 
	sleep(1);
	if (!empty($cod_pin)) {

		$pdo = ConnectionPDO::getConnection()->getLink();

		$sql = "SELECT 1 
				FROM pins 
				WHERE pin_codigo = :PIN 
				  AND pin_validade >= CURRENT_DATE;";

		$stmt = $pdo->prepare($sql);
		$stmt->bindValue(':PIN', trim($cod_pin), PDO::PARAM_STR);
		$stmt->execute();
		if (!($stmt->fetchColumn())) {

			//instanciando a classe de cryptografia
			$chave256bits = new Chave();
			$aes = new AES($chave256bits->retornaChave());
			$sql = "SELECT pin_codigo, pin_valor 
        				from pins_store 
        				where pin_codigo = '" . base64_encode($aes->encrypt(addslashes($cod_pin))) . "' 
        				  and pin_dataentrada >= CURRENT_DATE - INTERVAL '6 months'";

			
			$ff = fopen("/www/log/erroFile.txt","a+");
			fwrite($ff, $sql.date('Y-m-d H:i:s')."\r");
			fclose($ff);
			
			$rs_oper = SQLexecuteQuery($sql);
			if(!$rs_oper || pg_num_rows($rs_oper) == 0) {
				if (is_null($geralog))
					log_pin($PINS_STORE_MSG_LOG_STATUS['ERRO_VALIDACAO'],$sql,$cod_pin);
				return false;
			} else {
				$rs_oper_row = pg_fetch_array($rs_oper);
				if (is_null($geralog))
					log_pin($PINS_STORE_MSG_LOG_STATUS['SUCESSO_VALIDACAO'],$sql,$cod_pin);
				return true;
			}
		}else{
			if (is_null($geralog))
				log_pin($PINS_STORE_MSG_LOG_STATUS['SUCESSO_VALIDACAO'],$sql,$cod_pin);
			return true;
		}

	}
	else return false;
}

function limpa_session_pin($pagto) {
//$usuarioGames1 = unserialize($GLOBALS['_SESSION']['usuarioGames_ser']);
//if($usuarioGames1->getId()==53916) { 	echo "[$pagto]<br>"; }
	if ($pagto != '1' && $pagto != '0') {
		if (is_array($GLOBALS['_SESSION']['PINEPP'])) {
			foreach($GLOBALS['_SESSION']['PINEPP'] as $key => $value) {
				if (substr($key,-4) == $pagto) {
//if($usuarioGames1->getId()==53916) { echo "[$key]<br>"; }
					unset ($GLOBALS['_SESSION']['PINEPP'][$key]);
					if(isset($GLOBALS['_SESSION']['PIN_NOMINAL'][$key])) {
						unset ($GLOBALS['_SESSION']['PIN_NOMINAL'][$key]);
					}
				}
			}
		}
	}
}//end function limpa_session_pin

function monta_array_somente_gocash(&$aux_valor_PINs_GoCASH) {
	//Foreach para GoCASH
	$a_pins_gocash = array();
	foreach($GLOBALS['_SESSION']['PINEPP'] as $key => $value) {
		//Testando se é PIN GoCASH
		if(RetonaTamanhoPINGoCASH($key)) {
			$aux_valor_PINs_GoCASH += $value;
			
			$a_pins_gocash[$key] = $GLOBALS['_SESSION']['PIN_NOMINAL'][$key];
		}//end if(RetonaTamanhoPINGoCASH)
	}//end foreach
	return $a_pins_gocash;
}//end function monta_array_somente_gocash

function capturaCanalComissaoPINEPP($key,$value,&$maior,&$canal,&$id,&$comissao,&$financial) {
	/*
	Legenda:
		$key		=> Código PIN EPP
		$value		=> Valor do PIN EPP
		$maior		=> Vetor contendo dados da maior comissão
		$canal		=> Canal de venda do PIN EPP
		$id			=> Id de quem vendeu -- Funciona somente para LANs
		$comissao	=> Comissão de custo do PIN EPP
		$financial	=> Contém a informação se deverá ser considerado nos relatórios financeiros ou não. Onde: 0 = Não considera; 1 = Considera
	*/

	$distribuidor_id = retorna_distribuidor_pin($key);
	switch ($distribuidor_id) {
		case 1:
			$canal		= 'P1';
			$id			= 0;
			$comissao	= $GLOBALS['DISTRIBUIDORAS'][$distribuidor_id]['distributor_commiss']; 
			$financial	= 1;
			break;
		case 2:
			$canal		= 'G';
			$id			= 0;
			$comissao	= $GLOBALS['DISTRIBUIDORAS'][$distribuidor_id]['distributor_commiss']; 
			$financial	= 0;
			break;
		case 3:
			$canal		= 'L';
			$sql = "
select vg_ug_id,vg_pagto_tipo,vgm_perc_desconto, vgm_valor from tb_dist_venda_games vg
inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id
inner join tb_dist_venda_games_modelo_pins vgmp on vgm.vgm_id = vgmp.vgmp_vgm_id 
inner join pins p on vgmp.vgmp_pin_codinterno = p.pin_codinterno
inner join tb_pins_store_pins tpsp on p.pin_codinterno = tpsp.pins_pin_codinterno
where tpsp.pins_store_pin_codinterno = ".retorna_id_pin(addslashes($key));
			gravaLog_EPPCASH("SQL que busca comissoes para venda de PINs EPP atraves de LANHOUSES:\n$sql");
			$rs_lh = SQLexecuteQuery($sql);
			if(!$rs_lh) {
				$id			= 0;
				$comissao	= $GLOBALS['DISTRIBUIDORAS'][$distribuidor_id]['distributor_commiss']; 
			}
			else { 
				$rs_lh_row	= pg_fetch_array($rs_lh);
				$id			= $rs_lh_row['vg_ug_id'];
				$comissao	= $rs_lh_row['vgm_perc_desconto'];
			}
			$financial	= 0;
			break;
		case 4:
			$canal		= 'G';
			$id			= 0;
			$comissao	= $GLOBALS['DISTRIBUIDORAS'][$distribuidor_id]['distributor_commiss']; 
			$financial	= 0;
			break;
		case 5:
			$canal		= 'P2';
			$id			= 0;
			$comissao	= $GLOBALS['DISTRIBUIDORAS'][$distribuidor_id]['distributor_commiss']; 
			$financial	= 1;
			break;
		case 6:
			$canal		= 'P3';
			$id			= 0;
			$comissao	= $GLOBALS['DISTRIBUIDORAS'][$distribuidor_id]['distributor_commiss']; 
			$financial	= 1;
			break;
		case 7:
			$canal		= 'P4';
			$id			= 0;
			$comissao	= $GLOBALS['DISTRIBUIDORAS'][$distribuidor_id]['distributor_commiss']; 
			$financial	= 1;
			break;
		case 8:
			$canal		= 'P5';
			$id			= 0;
			$comissao	= $GLOBALS['DISTRIBUIDORAS'][$distribuidor_id]['distributor_commiss']; 
			$financial	= 1;
			break;
		case 9:
			$canal		= 'P6';
			$id			= 0;
			$comissao	= $GLOBALS['DISTRIBUIDORAS'][$distribuidor_id]['distributor_commiss']; 
			$financial	= 1;
			break;
		default:
		   $canal		= '';
	}
	if(empty($maior['comissao'])) {
		$maior['comissao']	= $comissao;
		$maior['valor']		= $value;
		$maior['canal']		= $canal;
		$maior['id']		= $id;
		$maior['financial']	= $financial;
	}
	else if($comissao>=$maior['comissao']){
			if($comissao==$maior['comissao']){
				if($value>$maior['valor']) {
					$maior['valor']		= $value;
					$maior['canal']		= $canal;
					$maior['id']		= $id;
					$maior['financial']	= $financial;
				}
			}
			else {
				$maior['comissao']	= $comissao;
				$maior['valor']		= $value;
				$maior['canal']		= $canal;
				$maior['id']		= $id;
				$maior['financial']	= $financial;
			}
		}
}//end function capturaCanalComissaoPINEPP

function updatePagamentoCompraPrincipal($valores_processados,$venda_id,&$msg){
	$usuarioGames = unserialize($GLOBALS['_SESSION']['usuarioGames_ser']);
	$sql = "UPDATE tb_pag_compras 
			SET status = 3, 
				dataconfirma = CURRENT_TIMESTAMP, 
				datacompra = CURRENT_TIMESTAMP ,
				valorpagtosaldo = ".$valores_processados['TOTAL_SALDO_UTILIZADO'].",
				valorpagtopin = ".$valores_processados['TOTAL_PIN_EPP_UTILIZADO'].",
				valorpagtogocash = ".$valores_processados['TOTAL_PIN_GOCASH_UTILIZADO']." 
			where idvenda = ".intval($venda_id)." 
				and tipo_cliente = 'M' 
				and idcliente = ".intval($usuarioGames->ug_id)." 
				and status = 1";
	gravaLog_EPPCASH("SQL que atualiza o registro na tabela tb_pag_compras:\n$sql");
	$rs_pagamento = SQLexecuteQuery($sql);
	if(!$rs_pagamento) {
		 $msg .= "<font color='#FF0000'><b>Erro ao atualizar o Pagamento (".$venda_id.").\n</b></font><br>";
		 return false;
	}
	else {
		return true;
	}
}//end function updatePagamentoCompraPrincipal($valores_processados,$venda_id,&$msg)

function updateVendaCompraPrincipal($venda_id,&$msg){
	$usuarioGames = unserialize($GLOBALS['_SESSION']['usuarioGames_ser']);
	$sql = "UPDATE tb_venda_games vg SET vg_ultimo_status=".intval($GLOBALS['STATUS_VENDA']['PAGTO_CONFIRMADO'])." where vg.vg_id = ".intval($venda_id)." and vg.vg_ug_id=".intval($usuarioGames->ug_id)." and vg_ultimo_status=".intval($GLOBALS['STATUS_VENDA']['PEDIDO_EFETUADO']);
	gravaLog_EPPCASH("SQL que atualiza o registro na tabela tb_venda_games:\n$sql");
	$rs_compra = SQLexecuteQuery($sql);
	if(!$rs_compra) {
		 $msg .= "<font color='#FF0000'><b>Erro ao atualizar a Compra (".$venda_id.").\n</b></font><br>";
		 return false;
	}
	else {
		return true;
	}
}//end function updateVendaCompraPrincipal($venda_id,&$msg)

function selecionaIDPagto($venda_id,&$msg){
	$usuarioGames = unserialize($GLOBALS['_SESSION']['usuarioGames_ser']);
	$sql = "select idpagto from tb_pag_compras where idvenda = ".intval($venda_id)." and idcliente=".intval($usuarioGames->ug_id)." and status=3 and tipo_cliente = 'M' ";
	gravaLog_EPPCASH("SQL que seleciona idpagto na tabela tb_pag_compras:\n$sql");
	$rs_id_pag = SQLexecuteQuery($sql);
	if($rs_id_pag_row = pg_fetch_array($rs_id_pag)) {
		return $rs_id_pag_row['idpagto'];
	}//end if($rs_id_pag_row = pg_fetch_array($rs_id_pag))
	else {
		$msg .= "<font color='#FF0000'><b>Erro ao Localizar o ID do Pagamento.\n</b></font><br>";
		return null;
	}
}//end function updateVendaCompraPrincipal($venda_id,&$msg)

function insereRegistro_pins_store_pag_epp_pin($key,$venda_id,$idpgto,$canal,$id,$comissao,$financial) {
	$sql = "insert into pins_store_pag_epp_pin (tpc_idpagto,tpc_idvenda,ps_pin_codinterno,pspep_data, pspep_canal, pspep_id, pspep_comissao,pspep_cons_financial) values (".$idpgto.",".intval($venda_id).",".retorna_id_pin(addslashes($key)).",now(),'".$canal."',".$id.",".$comissao.",".$financial.")";
	gravaLog_EPPCASH("SQL que insere registro na tabela pins_store_pag_epp_pin:\n$sql");
	$rs_rastreab = SQLexecuteQuery($sql);
	if(!$rs_rastreab) {
		 return false;
	}
	else {
		return true;
	}
}//end function insereRegistro_pins_store_pag_epp_pin

function insereSaldoComposicaoFifo($valor_composicao,$canal,$comissao,$novo_venda_id) {
	$usuarioGames = unserialize($GLOBALS['_SESSION']['usuarioGames_ser']);
	$sql_composicao_saldo = "insert into saldo_composicao_fifo (ug_id,scf_data_deposito,scf_valor,scf_valor_disponivel,scf_canal,scf_comissao,scf_id_pagamento,vg_id) values (".$usuarioGames->ug_id.",NOW(),".$valor_composicao.",".$valor_composicao.",'".$canal."',".$comissao.",'".$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIN_EPREPAG']."',$novo_venda_id)";
	gravaLog_EPPCASH("SQL que insere registro na tabela saldo_composicao_fifo EPP:\n$sql_composicao_saldo");
	$ret_composicao_saldo = SQLexecuteQuery($sql_composicao_saldo);
	if(!$ret_composicao_saldo) {
		$msg = "Erro ao inserir a composição do saldo. Por favor, tente novamente atualizando a página. Obrigado 215.\n";
		return false;
	}
	else {
		return true;
	}
}//end function insereSaldoComposicaoFifo


function insereRegistro_tb_venda_games_pinepp_origem($maior,$venda_id) {
	$sql = "insert into tb_venda_games_pinepp_origem (vg_id, tvgpo_canal, tvgpo_id, tvgpo_perc_desconto, tvgpo_cons_financial) values (".intval($venda_id).", '".$maior['canal']."', ".$maior['id'].", ".$maior['comissao'].", ".$maior['financial'].")";
	gravaLog_EPPCASH("SQL que insere registro na tabela tb_venda_games_pinepp_origem:\n$sql");
	$rs_tvgpo = SQLexecuteQuery($sql);
	if(!$rs_tvgpo) {
		 return false;
	}
	else {
		return true;
	}
}//end function insereSaldoComposicaoFifo($maior,$venda_id)

function utilizadordeSaldo(&$valor_decrementar, $rs_busca_saldo_row, $venda_id, &$msg) {
	$valor_decrementar -= $rs_busca_saldo_row['scf_valor_disponivel'];
	//Update da tabela de utilização de saldo
	$sql_update_saldo_composicao = "UPDATE saldo_composicao_fifo SET ";
	//Gerando registro da utilização do saldo
	$sql_saldo_utilizado = "INSERT INTO saldo_composicao_fifo_utilizado (scf_id, vg_id, scfu_valor) VALUES (".$rs_busca_saldo_row['scf_id'].",".intval($venda_id).",";
	if($valor_decrementar<0){
		$sql_update_saldo_composicao .= "scf_valor_disponivel=".round(($valor_decrementar*(-1)), 2);
		//Gerando registro da utilização do saldo
		$sql_saldo_utilizado .= round(($rs_busca_saldo_row['scf_valor_disponivel']+$valor_decrementar), 2).")";
	}
	else {
		$sql_update_saldo_composicao .= "scf_valor_disponivel=0, scf_status=0";
		//Gerando registro da utilização do saldo
		$sql_saldo_utilizado .= $rs_busca_saldo_row['scf_valor_disponivel'].")"; 
	}
	$sql_update_saldo_composicao .= " where scf_id=".$rs_busca_saldo_row['scf_id'];
	gravaLog_EPPCASH("SQL que atualiza o registro na tabela saldo_composicao_fifo_utilizado[1]:\n$sql_update_saldo_composicao");
//echo "<br>SQL: ".$sql_update_saldo_composicao."<br>";
	$rs_update_saldo_composicao = SQLexecuteQuery($sql_update_saldo_composicao);
	if(!$rs_update_saldo_composicao) {
		 $msg .= "<font color='#FF0000'><b>Erro ao atualizar a composição do Saldo (".$rs_busca_saldo_row['scf_id'].").\n</b></font><br>";
	}
	else {
		//Gerando registro da utilização do saldo
		gravaLog_EPPCASH("SQL que insere o registro na tabela saldo_composicao_fifo_utilizado[1]:\n$sql_saldo_utilizado");
//echo "<br>SQL: ".$sql_saldo_utilizado."<br>";
		$rs_saldo_utilizado = SQLexecuteQuery($sql_saldo_utilizado);
		if(!$rs_saldo_utilizado) {
			 $msg .= "<font color='#FF0000'><b>Erro ao gerar ratreabilidade de utilização de Saldo (".$rs_busca_saldo_row['scf_id'].").\n</b></font><br>";
		}
		if($valor_decrementar<=0){
			return true;
		}//end if($valor_decrementar<=0)
		else {
			return false;
		}
	}
}//end function utilizadordeSaldo()

function RedeemPIN_EPP_CASH($key,$value,&$msg,&$aux_valor_PINs_EPP,&$aux_valor_PINs) {
	//instanciando a classe de cryptografia
	$chave256bits = new Chave();
	$aes = new AES($chave256bits->retornaChave());
	if(valida_pin($key,1) > 0){

		if(valida_vencimento_pin($key,1) == false) {
			$msg .= "<font color='#FF0000'><b>Erro ao validar a validade do PIN EPP CASH (".$key.").\n</b></font><br>";
			return;
		}

		$sql = "UPDATE pins_store SET pin_status='".intval($GLOBALS['PINS_STORE_STATUS_VALUES']['U'])."' WHERE pin_codigo='".base64_encode($aes->encrypt(addslashes($key)))."' and pin_status='".intval($GLOBALS['PINS_STORE_STATUS_VALUES']['A'])."' and pin_valor = ".intval($value);
		gravaLog_EPPCASH("Atualizando cada PIN CASH como utilizado. \n $sql");
//echo "<br>SQL: ".$sql."<br>";
		$rs_oper = SQLexecuteQuery($sql);
		if(!$rs_oper) {
			 $msg .= "<font color='#FF0000'><b>Erro ao utilizar o PIN (".$key.").\n</b></font><br>";
			 log_pin($GLOBALS['PINS_STORE_MSG_LOG_STATUS']['ERRO_UTILIZACAO'],$sql,$key);
		}
		else {
			log_pin($GLOBALS['PINS_STORE_MSG_LOG_STATUS']['SUCESSO_UTILIZACAO'],$sql,$key);
			$aux_valor_PINs_EPP += $value;
			$aux_valor_PINs += $value;
		}
	}//end if(valida_pin($key,1) > 0)
	else {
		$msg .= "<font color='#FF0000'><b>Erro ao validar PIN EPP CASH no Transaction (".$key.").\n</b></font><br>";
	}
} //end function RedeemPIN_EPP_CASH

/**
 ** Implementa o bloqueio por flags para atualização do saldo
*/
function flag_user_test($ug_id) {
	$sql = "update usuarios_games set ug_flag_usando_saldo = 1 where ug_id = $ug_id and ug_flag_usando_saldo = 0;";
	$ret2 = SQLexecuteQuery($sql);

	$cmdtuples = pg_affected_rows($ret2);
//	echo $cmdtuples . " tuples are affected.<br>\n";
	gravaLog_EPPCASH("Em flag_user_test: $cmdtuples registros afetados.\n$sql");

	if($cmdtuples===1) {
		return 0;
	} else {
		return 1;
	}
}

function flag_user_unblock($ug_id) {
	$sql = "update usuarios_games set ug_flag_usando_saldo = 0 where ug_id = $ug_id;";
	gravaLog_EPPCASH("Em flag_user_unblock($ug_id)\n   $sql");

	$ret2 = SQLexecuteQuery($sql);
//	if(!$ret2) echo "<font color='#FF0000'><b>Erro ao setar flag</b></font>\n<br><br>";
}

//Função que bloqueia e retorna 0 se bloqueou todos os PIN envolvidos(EPP CASH e GoCASH) com sucesso, caso contrário retorna 1
function flag_pin_test() {

	//variavel auxiliar para o retorno da função
	$aux_retorno = 1;

	if (is_array($GLOBALS['_SESSION']['PINEPP'])) { 
		
		//Qtde de PINs considerados
		$qte_pin_epp = 0;
		//Qtde de PINs GoCASH considerados
		$qte_pin_epp_gocash = 0;

		//Lista com ID
		$lista_pin_codinterno = "";
		//Lista com GoCASH para INSERT
		$lista_pin_gocash = "";
	
		foreach($_SESSION['PINEPP'] as $key => $value) {
									
			if(RetonaTamanhoPINEPPCASH($key)) {
				$aux_pin_codinterno = retorna_id_pin(addslashes($key));
				if($aux_pin_codinterno != '0') {
					if($lista_pin_codinterno != "") {
						$lista_pin_codinterno .= ",";
					}//end if($lista_pin_codinterno != "")
					$lista_pin_codinterno .= $aux_pin_codinterno;
					$qte_pin_epp++;
				}//end if($aux_pin_codinterno > 0)

			}//end if(RetonaTamanhoPINEPPCASH) 

			//Testando se é PIN GoCASH
			elseif(RetonaTamanhoPINGoCASH($key)) {
				if($lista_pin_gocash != "") {
					$lista_pin_gocash .= ",";
				}//end if($lista_pin_gocash != "")
				$lista_pin_gocash .= "('".$key."')";
				$qte_pin_epp_gocash++;
			}//end elseif(RetonaTamanhoPINGoCASH)


		}//end foreach
		
		//Trecho abaixo verifica se existe PINs EEP CASH e tenta um bloqueio com transaction por conta de ter a necessidade de bloquieo de todos os PINs, caso seja parcial a execução da SQL é necessário executar rollback
		$sucesso = true;
		if($qte_pin_epp > 0) {
			//Inicia transacao
			$sql = "BEGIN TRANSACTION ";
			//echo $sql."<br>";
			$ret = SQLexecuteQuery($sql);
			if($ret) {
				$sql = "update pins_store set pin_bloqueio = 1 where pin_codinterno IN ($lista_pin_codinterno) and pin_bloqueio = 0;";
				$ret2 = SQLexecuteQuery($sql);

				$cmdtuples = pg_affected_rows($ret2);
				//$cmdtuples = 99;
				//echo "BLOQUEIO EPP CASH: ".$cmdtuples . " tuples are affected.<br>\n$sql<br>\n";
				gravaLog_EPPCASH("Em flag_pin_test: $cmdtuples registros afetados.\n$sql");

				if($cmdtuples===$qte_pin_epp) {
					$sql = "COMMIT TRANSACTION ";
					//echo $sql."<br>";
					$ret3 = SQLexecuteQuery($sql);
					if($ret3) $aux_retorno = 0;
					else $sucesso = false; 
				} else {
					$sql = "ROLLBACK TRANSACTION ";
					//echo $sql."<br>";
					$ret3 = SQLexecuteQuery($sql);
					$sucesso = false;
				}
			}//end if($ret)
			else $sucesso = false;
		}//end if($qte_pin_epp > 0)
		
		//Trecho abaixo verifica se PINs EEP GoCASH está sendo utilizado e bloqueia este através de um insert na tabela pins_gocash_bloqueio, caso seja parcial a execução da SQL o retorno é false
		if(($qte_pin_epp_gocash > 0) &&  $sucesso) {
			
			//Inicia transacao
			$sql = "BEGIN TRANSACTION ";
			//echo $sql."<br>";
			$ret = SQLexecuteQuery($sql);
			if($ret) {
				$sql = "insert into pins_gocash_bloqueio values ".$lista_pin_gocash.";";
				$ret2 = SQLexecuteQuery($sql);

				$cmdtuples_gocash = pg_affected_rows($ret2);
				//$cmdtuples_gocash = 99;
				//echo "BLOQUEIO GOCASH: ".$cmdtuples_gocash . " tuples are affected.<br>\n$sql<br>\n";
				gravaLog_EPPCASH("Em flag_pin_test [GoCASH]: $cmdtuples registros afetados.\n$sql");

				if($cmdtuples_gocash===$qte_pin_epp_gocash) {
					$sql = "COMMIT TRANSACTION ";
					//echo $sql."<br>";
					$ret3 = SQLexecuteQuery($sql);
					if($ret3) $aux_retorno = 0;
					else $aux_retorno = 1;
				} else {
					$sql = "ROLLBACK TRANSACTION ";
					//echo $sql."<br>";
					$ret3 = SQLexecuteQuery($sql);
					//Rollback nos PINs EPP CASH no bloqueio
					if($qte_pin_epp > 0) {
						$sql = "update pins_store set pin_bloqueio = 0 where pin_codinterno IN ($lista_pin_codinterno);";
						gravaLog_EPPCASH("Em flag_pin_test DESBLOQUEIA [GoCASH] ");
						$ret2 = SQLexecuteQuery($sql);
						//echo "DESBLOQUEIO EPP CASH: $sql<br>\n";
					}
					$aux_retorno = 1;
				}
			}//end if($ret)
			else $aux_retorno = 1;

		}//end if($qte_pin_epp_gocash > 0)
		
		if(($qte_pin_epp_gocash == 0) && ($qte_pin_epp == 0)) {
			$aux_retorno = 0;
		}//end if(($qte_pin_epp_gocash == 0) && ($qte_pin_epp == 0))

	}//end if (is_array($_SESSION['PINEPP']))
	else $aux_retorno = 0;

	return $aux_retorno;

}//end function flag_pin_test

//Função que desbloqueia todos os PINs envolvidos(EPP CASH e GoCASH), somente será executada se houve bloqueio com sucesso, ou seja, dentro do if de bloqueio com sucesso
function flag_pin_unblock() {
	//Libera o LOG
	$sDebug = true;

	if (is_array($_SESSION['PINEPP'])) { 

		//Qtde de PINs considerados
		$qte_pin_epp = 0;
		//Qtde de PINs GoCASH considerados
		$qte_pin_epp_gocash = 0;

		//Lista com ID
		$lista_pin_codinterno = "";
		//Lista com GoCASH para INSERT
		$lista_pin_gocash = "";

		foreach($_SESSION['PINEPP'] as $key => $value) {
									
			if(RetonaTamanhoPINEPPCASH($key)) {
				$aux_pin_codinterno = retorna_id_pin(addslashes($key));
				if($aux_pin_codinterno != '0') {
					if($lista_pin_codinterno != "") {
						$lista_pin_codinterno .= ",";
					}//end if($lista_pin_codinterno != "")
					$lista_pin_codinterno .= $aux_pin_codinterno;
					$qte_pin_epp++;
				}//end if($aux_pin_codinterno > 0)

			}//end if(RetonaTamanhoPINEPPCASH) 

			//Testando se é PIN GoCASH
			elseif(RetonaTamanhoPINGoCASH($key)) {
				if($lista_pin_gocash != "") {
					$lista_pin_gocash .= ",";
				}//end if($lista_pin_gocash != "")
				$lista_pin_gocash .= "'".$key."'";
				$qte_pin_epp_gocash++;
			}//end elseif(RetonaTamanhoPINGoCASH)

		}//end foreach
		
		if($qte_pin_epp > 0) {
		
			$sql = "update pins_store set pin_bloqueio = 0 where pin_codinterno IN ($lista_pin_codinterno);";
			$ret2 = SQLexecuteQuery($sql);
			if($sDebug) gravaLog_EPPCASH("DESBLOQUEIO EPP CASH: $sql");
		//	if(!$ret2) echo "<font color='#FF0000'><b>Erro ao setar flag</b></font>\n<br><br>";
	
		}//end if($qte_pin_epp > 0)

		if($qte_pin_epp_gocash > 0) {
		
			$sql = "delete from pins_gocash_bloqueio where pgb_pin_codigo IN ($lista_pin_gocash);";
			$ret2 = SQLexecuteQuery($sql);
			if($sDebug) gravaLog_EPPCASH("DESBLOQUEIO GOCASH: $sql");
		//	if(!$ret2) echo "<font color='#FF0000'><b>Erro ao setar flag</b></font>\n<br><br>";
	
		}//end if($qte_pin_epp > 0)

	}//end if (is_array($_SESSION['PINEPP']))

}//end function flag_pin_unblock()
/**
 ** FIM de Implementa o bloqueio por flags para atualização do saldo
*/

function buscaIdLANHouse($venda_id) {
	$sql = "select pspep_id
			from pins_store_pag_epp_pin 
			where tpc_idvenda IN (select case when idvenda_origem=0 then idvenda else idvenda_origem end as id from tb_pag_compras where idvenda=$venda_id)
			and pspep_canal='L'
			group by pspep_id; ";
	gravaLog_EPPCASH("SQL que busca o ID da LAN House que Vendeu o PIN EPPCASH:\n$sql");
	$rs_busca = SQLexecuteQuery($sql);
	if(!$rs_busca) {
		 return 0;
	}
	else {
		while($rs_busca_row = pg_fetch_array($rs_busca)){
			if($rs_busca_row['pspep_id'] != 0) {
				return $rs_busca_row['pspep_id'];
			}//end if($rs_busca_row['pspep_id'] != 0)
			else return 0;
		}//end while
	}//end else do if(!$rs_busca)
}//end function buscaIdLANHouse($venda_id)


function buscaCanalnaComposicaoSaldo(&$maior,$venda_id) { 
	$sql = "select scf_canal, scf_comissao, vg_id, vg_id_dep  
			from ( 
				select scfu.vg_id, scf.scf_canal, scf.scf_comissao, scf.vg_id as vg_id_dep  
				from saldo_composicao_fifo_utilizado scfu 
				INNER JOIN saldo_composicao_fifo scf ON (scfu.scf_id=scf.scf_id) 
			) scfu_int 
			where vg_id=$venda_id
			group by scf_canal, scf_comissao, vg_id, vg_id_dep; ";
	gravaLog_EPPCASH("SQL que busca a composição do Saldo:\n$sql");
	$rs_busca = SQLexecuteQuery($sql);
	if(!$rs_busca) {
		 return false;
	}
	else {
		gravaLog_EPPCASH("Entrou no else do if(!rs_busca) \n");
		$aux_canais = array('C','P1','P2','P3','P4','P5','P6','L','G');
		if(in_array($maior['canal'],$aux_canais)) {
			$maior['canal'] = 'W';
		}
		if($maior['canal'] != 'C') {
			gravaLog_EPPCASH("Canal maior [".$maior['canal']."] \n");
			while($rs_busca_row = pg_fetch_array($rs_busca)){
				gravaLog_EPPCASH("Canal do pg_fetch_array [".$rs_busca_row['scf_canal']."] \n");
				switch ($rs_busca_row['scf_canal']) {
					case 'C':
						$maior['canal']		= $rs_busca_row['scf_canal'];
						$maior['id']		= 0;
						$maior['comissao']	= $rs_busca_row['scf_comissao']; 
						break;
					case 'P1':
						if($maior['canal'] != 'C') {
							$maior['canal']		= $rs_busca_row['scf_canal'];
							$maior['id']		= 0;
							$maior['comissao']	= $rs_busca_row['scf_comissao']; 
						} //end if($maior['canal'] != 'C') 
						break;
					case 'P2':
						if($maior['canal'] != 'C') {
							$maior['canal']		= $rs_busca_row['scf_canal'];
							$maior['id']		= 0;
							$maior['comissao']	= $rs_busca_row['scf_comissao']; 
						} //end if($maior['canal'] != 'C') 
						break;
					case 'P3':
						if($maior['canal'] != 'C') {
							$maior['canal']		= $rs_busca_row['scf_canal'];
							$maior['id']		= 0;
							$maior['comissao']	= $rs_busca_row['scf_comissao']; 
						} //end if($maior['canal'] != 'C') 
						break;
					case 'P4':
						if($maior['canal'] != 'C') {
							$maior['canal']		= $rs_busca_row['scf_canal'];
							$maior['id']		= 0;
							$maior['comissao']	= $rs_busca_row['scf_comissao']; 
						} //end if($maior['canal'] != 'C') 
						break;
					case 'P5':
						if($maior['canal'] != 'C') {
							$maior['canal']		= $rs_busca_row['scf_canal'];
							$maior['id']		= 0;
							$maior['comissao']	= $rs_busca_row['scf_comissao']; 
						} //end if($maior['canal'] != 'C') 
						break;
					case 'P6':
						if($maior['canal'] != 'C') {
							$maior['canal']		= $rs_busca_row['scf_canal'];
							$maior['id']		= 0;
							$maior['comissao']	= $rs_busca_row['scf_comissao']; 
						} //end if($maior['canal'] != 'C') 
						break;
					case 'L':
						if($maior['canal'] != 'C' && $maior['canal'] != 'P1' && $maior['canal'] != 'P2' && $maior['canal'] != 'P3' && $maior['canal'] != 'P4' && $maior['canal'] != 'P5' && $maior['canal'] != 'P6') {
							$maior['canal']		= $rs_busca_row['scf_canal'];
							//$maior['id']		= 0;
							$maior['id']		= buscaIdLANHouse($rs_busca_row['vg_id_dep']);
							$maior['comissao']	= $rs_busca_row['scf_comissao']; 
						} //end if($maior['canal'] != 'C' && $maior['canal'] != 'P1' && $maior['canal'] != 'P2' && $maior['canal'] != 'P3' &&  && $maior['canal'] != 'P4')
						break;
					case 'G':
						if($maior['canal'] != 'C' && $maior['canal'] != 'P1' && $maior['canal'] != 'P2' && $maior['canal'] != 'P3' && $maior['canal'] != 'P4' && $maior['canal'] != 'P5' && $maior['canal'] != 'P6' && $maior['canal'] != 'L') {
							$maior['canal']		= $rs_busca_row['scf_canal'];
							$maior['id']		= 0;
							$maior['comissao']	= $rs_busca_row['scf_comissao']; 
						} //end if($maior['canal'] != 'C' && $maior['canal'] != 'P1' && $maior['canal'] != 'P2' && $maior['canal'] != 'P3' && $maior['canal'] != 'P4' && $maior['canal'] != 'L')
						break;
				}//end switch
				
			}//end while
		} //end if($maior['canal'] != 'C')
		else {
			gravaLog_EPPCASH("Array passado por referencia <pre>".print_r($maior,true)."</pre> \n");
		}
		return true;
	}//end else do if(!$rs_busca)
}//end function buscaCanalnaComposicaoSaldo($maior,$venda_id)

function utilizar_pin() { 
	global $PINS_STORE_STATUS_VALUES,$STATUS_VENDA,$PINS_STORE_MSG_LOG_STATUS,$venda_id,$DISTRIBUIDORAS,$PAGAMENTO_PIN_EPREPAG_NUMERIC;
	//Libera o LOG
	$sDebug = true;

	if($sDebug) gravaLog_EPPCASH("Dentro da funcao utilizar_pin() no ajax-pin_pagamento.php");

	$msg = "";
	$time_start_stats = getmicrotime();

	$usuarioGames1 = unserialize($_SESSION['usuarioGames_ser']);
	if(flag_user_test($usuarioGames1->getId()) ) {
		$msg = "<font color='red'>Conta com saldo em uso, faça login novamente, por favor.</font><br>";
		if($sDebug) gravaLog_EPPCASH("FLAG Usa - Blocked - go away (ug_id: ".$usuarioGames1->getId().")");
	} else {

		//bloqueia os PINs
		if (flag_pin_test()) {
			$msg = "<font color='red'>PINs já estão sendo utilizados. Aguarde alguns segundos, por favor.</font><br>";
		}
		else {
//comentar esta linha
//if($usuarioGames1->getId()==53916) sleep(20); //$usuarioGames1->getId()==9093 || - reynaldo ///$usuarioGames1->getId()==2745 - fabio
			$time_stop_stats = getmicrotime(); 
			if($sDebug) gravaLog_EPPCASH("FLAG Usa - Free - block it (Delay window (ug_id: ".$usuarioGames1->getId()."): ".number_format($time_stop_stats - $time_start_stats, 12, '.', '.').")");

			// Atualiza valor saldo do BD
			$saldo = getSaldoUsuarioFunc();

			//Inicia transacao
			$sql = "BEGIN TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "<font color='#FF0000'><b>Erro ao iniciar transa&cceil;&atilde;o.\n</b></font><br>";
			//variavel de acumula valores de TODOS PINs utilizados na compra
			$aux_valor_PINs = 0;
			//variavel de acumula valores de PINs EPP CASH utilizados na compra
			$aux_valor_PINs_EPP = 0;
			//variavel de acumula valores de PINs GoCASH utilizados na compra
			$aux_valor_PINs_GoCASH = 0;

			//transacao
			if($msg == ""){
				if($sDebug) gravaLog_EPPCASH("Dentro do transaction (ug_id: ".$usuarioGames1->getId().")");
				$var_origem_ajax_pin_pagamento = true;
				include DIR_INCS . "gamer/venda_e_modelos_logica_epp.php"; 
				if($sDebug) gravaLog_EPPCASH("Apos o include 'includes/venda_e_modelos_logica.php'(ug_id: ".$usuarioGames1->getId().")");
				$rs_venda_row = pg_fetch_array($rs_venda);
				$ultimo_status	= $rs_venda_row['vg_ultimo_status'];
				if($sDebug) gravaLog_EPPCASH("Verificando o ultimo_status: [$ultimo_status] da compra (Compra existente ou NAO)(ug_id: ".$usuarioGames1->getId().")");
				if ($ultimo_status <> 1) {
					$msg .= "<font color='#FF0000'><b>Compra j&aacute; efetuada ou inexistente.\n</b></font><br>";
				}
				else {
					if($sDebug) gravaLog_EPPCASH("Compra nao existe! Entao Continua.(ug_id: ".$usuarioGames1->getId().")");
					include DIR_INCS . "gamer/venda_e_modelos_calculate.php"; 
					if($sDebug) gravaLog_EPPCASH("Apos o include 'includes/venda_e_modelos_calculate.php'(ug_id: ".$usuarioGames1->getId().")");
					//Atribuindo o valor total da compra em REAIS
					//$valor = $total_geral;
					//Atribuindo o valor total da compra em EPP CASH
					$valor = ($total_geral_epp_cash/100+$taxas);
					//echo "GERAL Reais[$total_geral]<br>";
					//echo "GERAL EPP[$total_geral_epp_cash]<br>";
					$usuarioGames = unserialize($_SESSION['usuarioGames_ser']);
					$saldoDB = getSaldoUsuarioFunc();
					if($sDebug) gravaLog_EPPCASH("Antes do if (saldo <> saldoDB)\n Saldo antes do transaction: saldo [$saldo]\n Saldo retornado pelo SQL: saldoDB: [".$saldoDB."](ug_id: ".$usuarioGames1->getId().")");
					//variavel utilizada para rollback da SESSION
					$valor_saldo_rollback = $saldoDB;
					if ($saldo <> $saldoDB) {
						$msg .= "<font color='#FF0000'><b>Saldo n&atilde;o confere com o banco de dados(R$ ".$saldo.",00). <br>Favor, fazer login novamente.\n</b></font><br>";
					}
					else {
						if($sDebug) gravaLog_EPPCASH("Entrou no ELSE do if (saldo <> saldoDB)\n(ug_id: ".$usuarioGames1->getId().")");
						if (is_array($_SESSION['PINEPP'])) { 
							if($sDebug) gravaLog_EPPCASH("Existe carrinho contendo PINs (COD.A) (ug_id: ".$usuarioGames1->getId().")");
		//echo "PIN_NOMINAL:<pre>".print_r($_SESSION['PIN_NOMINAL'],true)."</pre>";
		//echo "PINEPP:<pre>".print_r($_SESSION['PINEPP'],true)."</pre>";
							
							//Captura somente os GoCASH com seus respectivos valores nominais
							$a_pins_gocash = monta_array_somente_gocash($aux_valor_PINs_GoCASH);

							//Foreach para PIN CASH
							if((count($a_pins_gocash)==0)||SaveRedeemPinTransaction($a_pins_gocash,$venda_id)) {

								if($sDebug) gravaLog_EPPCASH("Dentro do if((count(a_pins_gocash)==0)||SaveRedeemPinTransaction(a_pins_gocash,$venda_id))(ug_id: ".$usuarioGames1->getId().")");
								$aux_valor_PINs += $aux_valor_PINs_GoCASH;
								foreach($_SESSION['PINEPP'] as $key => $value) {
									
									if(RetonaTamanhoPINEPPCASH($key)) {
										RedeemPIN_EPP_CASH($key,$value,$msg,$aux_valor_PINs_EPP,$aux_valor_PINs);
									}//end if(RetonaTamanhoPINEPPCASH) 

								}//end foreach

							}// end if((count($a_pins_gocash)==0)||SaveRedeemPinTransaction($a_pins_gocash,$venda_id)
							else {
								$msg .= "<font color='#FF0000'><b>Erro ao validar PIN GoCASH no Transaction A.\n</b></font><br>";
							}//end else if((count($a_pins_gocash)==0)||SaveRedeemPinTransaction($a_pins_gocash,$venda_id)

						}//end if (is_array($_SESSION['PINEPP']))
						else {
							if($sDebug) gravaLog_EPPCASH("Não existe carrinho contendo PINs (ug_id: ".$usuarioGames1->getId().")");
						}

						
						if($msg == ""){
		//$msg .= "AKI nada";
							//Distribuindo os valores para processamento
							if($sDebug) gravaLog_EPPCASH("(ug_id: ".$usuarioGames1->getId().")\nValores antes RetonaComposicaoPagamento:\n valor = ".$valor."\n saldo = ".$saldo."\n aux_valor_PINs_EPP = ".$aux_valor_PINs_EPP."\n aux_valor_PINs_GoCASH: = ".$aux_valor_PINs_GoCASH."\n");
							$valores_processados = RetonaComposicaoPagamento($valor,$saldo,$aux_valor_PINs_EPP,$aux_valor_PINs_GoCASH);
							if($sDebug) gravaLog_EPPCASH("(ug_id: ".$usuarioGames1->getId().")<pre>".print_r($valores_processados,true)."</pre>");
		//echo "<pre>".print_r($valores_processados,true)."</pre>";
							if($sDebug) gravaLog_EPPCASH("Saldo do usuario: [$saldo]\nTotal do carrinho com TODOS os PINs: [$aux_valor_PINs]\nTotal do carrinho de PINs EPP: [$aux_valor_PINs_EPP]\nTotal do carrinho de PINs GoCASH: [$aux_valor_PINs_GoCASH]\nValor total da compra: [$valor](ug_id: ".$usuarioGames1->getId().")");
							if (($saldo+$aux_valor_PINs)>= $valor) {
								//Capturando o Saldo FINAL
								$saldoFinal = $valores_processados['SALDO_FINAL'];
							
								if($sDebug) gravaLog_EPPCASH("Dentro do IF (SALDO+CARRINHO PIN EPP+CARRINHO GoCASH >= valor da compra)(ug_id: ".$usuarioGames1->getId().")");
								if ($usuarioGames->ug_id != "" && $usuarioGames->ug_id) {
									if($sDebug) gravaLog_EPPCASH("Dentro do IF (A SESSION POSSUI UG_ID)(ug_id: ".$usuarioGames1->getId().")");

									//Variável utilizada para identicar a composição do pagamento quanto a formação de PINs e Saldo
									$auxComposicaoPagto = 0;

									// Valor dos PIN maior do que valor da compra, diferencia vai para o saldo
									if ($aux_valor_PINs >= $valor) {
										if($sDebug) gravaLog_EPPCASH("Dentro do IF (CARRINHO PIN EPP >= valor da compra)(ug_id: ".$usuarioGames1->getId().")");
										//Atribuindo composição somente PIN
										$auxComposicaoPagto = 1;
										if($sDebug) gravaLog_EPPCASH("Antes de obter ID de venda para depósito do resto.(ug_id: ".$usuarioGames1->getId().")");
										//Capturando um id para venda
										$novo_venda_id = obterIdVendaValido();
										if($sDebug) gravaLog_EPPCASH("Depois de obter ID de venda para depósito do resto. Id Venda: $novo_venda_id (ug_id: ".$usuarioGames1->getId().")");
										// Cadastra uma venda sem modelo para o que sobrou da utilização de PINs EPP e GoCASH 
										if( ($valores_processados['RESTO_PIN_EPP_DEPOSITO']+$valores_processados['RESTO_PIN_GOCASH_DEPOSITO'])  > 0) {
											if($sDebug) gravaLog_EPPCASH("Dentro do IF (CARRINHO PIN EPP + CARRINHO PIN GoCASH -  valor da compra > 0) - Ou seja, gerar o registro de deposito em saldo da diferença.(ug_id: ".$usuarioGames1->getId().")");
											$info_insert = array(
													'NOVO_ID'		=> $novo_venda_id,
													'UG_ID'			=> $usuarioGames->ug_id,
													'VALOR_DEP'		=> ($valores_processados['RESTO_PIN_EPP_DEPOSITO']+$valores_processados['RESTO_PIN_GOCASH_DEPOSITO']),
													'VALOR_GOCASH'	=> $valores_processados['RESTO_PIN_GOCASH_DEPOSITO'],
													'VALOR_EPPCASH' => $valores_processados['RESTO_PIN_EPP_DEPOSITO'],
													'ID_ORIGEM'		=> intval($venda_id)
													);
											deposita_em_saldo($info_insert, $msg);
										} //end if( ($valores_processados['RESTO_PIN_EPP_DEPOSITO']+$valores_processados['RESTO_PIN_GOCASH_DEPOSITO'])  > 0)
									
									}//end if ($aux_valor_PINs >= $valor)  

									// Sem PINs, usa só o saldo 
									elseif ($aux_valor_PINs == 0){
										if($sDebug) gravaLog_EPPCASH("Dentro do IF(Pagamento com somente saldo)(ug_id: ".$usuarioGames1->getId().")");
										//Atribuindo composição somente SALDO
										$auxComposicaoPagto = 2;
										//Valor utilizado do saldo
										$valor_decrementar = $valor;
									} // end elseif ($aux_valor_PINs == 0)

									// Pagamento composto de PINs + Saldo
									else{
										if($sDebug) gravaLog_EPPCASH("Dentro do ELSE - Para pagamento com saldo mais PIN combinado.(ug_id: ".$usuarioGames1->getId().")");
										//Atribuindo composição SALDO + PIN
										$auxComposicaoPagto = 3;
										//Valor utilizado do saldo
										$valor_decrementar = ($valor-$aux_valor_PINs);
									}//end ELSE - Pagamento composto de PINs + Saldo

									//inicio do bloco se o pagamento foi efetuado UTILIZANDO saldo
									if($auxComposicaoPagto>1) {
										$sql_busca_saldo_composicao = "select * from saldo_composicao_fifo where ug_id=".intval($usuarioGames->ug_id)." and scf_status=1 order by scf_data_deposito";
		//echo "<br>SQL: ".$sql_busca_saldo_composicao."<br>";
										if($sDebug) gravaLog_EPPCASH("SQL que busca o saldo disponivel do usuario:\n$sql_busca_saldo_composicao\n(ug_id: ".$usuarioGames1->getId().")");
										$rs_busca_saldo = SQLexecuteQuery($sql_busca_saldo_composicao);
										while($rs_busca_saldo_row = pg_fetch_array($rs_busca_saldo)){
											if(utilizadordeSaldo($valor_decrementar, $rs_busca_saldo_row, $venda_id, $msg)) {
												break;
											}
										}//end while
									}//end if($auxComposicaoPagto>1)

									//Update do pagamento da compra principal com as informações de composição do pagamento
									if(updatePagamentoCompraPrincipal($valores_processados,$venda_id,$msg)) {

										//Update da venda da compra principal com as informações de composição do pagamento
										if(updateVendaCompraPrincipal($venda_id,$msg)){

											//Se tiver algum PIN EPP informado no carrinho de PINs
											if($valores_processados['RESTO_PIN_EPP_DEPOSITO'] > 0 || $valores_processados['TOTAL_PIN_EPP_UTILIZADO'] > 0) {
												//Capturando o ID do Pagamento
												$idpgto = selecionaIDPagto($venda_id,$msg);
											}//end if($valores_processados['RESTO_PIN_EPP_DEPOSITO'] > 0 || $valores_processados['TOTAL_PIN_EPP_UTILIZADO'] > 0) 

											$maior = array(
														'comissao'	=> 0,
														'valor'		=> 0,
														'canal'		=> 0,
														'id'		=> 0,
														'financial' => 0
														);

											if (is_array($_SESSION['PINEPP'])) {
												//variavel flag para definir que já foi inserido parte do PIN anterior na composição do saldo
												$parte_do_pin	= 0;
												//variavel para testar o valor parcial na composição dos PINs
												$valor_parcial	= 0;
												//variavel flag para definir que já foi inserido parte do PIN GoCASH anterior na composição do saldo
												$parte_do_pin_gocash	= 0;
												//variavel para testar o valor parcial na composição dos PINs GoCASH
												$valor_parcial_gocash	= 0;
												foreach($_SESSION['PINEPP'] as $key => $value) {
		//echo "[$key]<br>";
													
													//Testando se é PIN CASH
													if(RetonaTamanhoPINEPPCASH($key)) {
		//echo "Tamanho EPP[$tamanho_pin]<br>";
														//função que captura dados para o insert na tabela pins_store_pag_epp_pin
														// a função também atualiza o vetor contendo a maior comissão
														capturaCanalComissaoPINEPP($key, $value, $maior, $canal, $id, $comissao, $financial);
		//echo "<pre>".print_r($maior,true)."</pre>";

														if(!empty($canal)) {
															//insere o registrio na tabela pins_store_pag_epp_pin
															if(!insereRegistro_pins_store_pag_epp_pin($key,$venda_id,$idpgto,$canal,$id,$comissao,$financial)) {
																 $msg .= "<font color='#FF0000'><b>Erro ao atualizar a Rastreabilidade do PIN (".$key.").\n</b></font><br>";
															}
															else {
																	$valor_parcial += $value;
																	//if(($valor_parcial - $valor)>0)
																	if(($valor_parcial - $valores_processados['TOTAL_PIN_EPP_UTILIZADO'])>0)
																	{
																		if(empty($parte_do_pin)){
																			$valor_composicao	= ($valor_parcial - $valores_processados['TOTAL_PIN_EPP_UTILIZADO']);//$valor);
																			$parte_do_pin		= 1;
																		}
																		else {
																			$valor_composicao	= $value;
																		}
																		if(!insereSaldoComposicaoFifo($valor_composicao,$canal,$comissao,$novo_venda_id)) {
																			$msg .= "Erro ao inserir a composição do saldo. Por favor, tente novamente atualizando a página. Obrigado 215 EPP CASH.\n";
																			log_pin($GLOBALS['PINS_STORE_MSG_LOG_STATUS']['ERRO_STRANSACAO'],"Sem sql",$key);
																		}else{
																			log_pin($GLOBALS['PINS_STORE_MSG_LOG_STATUS']['SUCESSO_CTRANSACAO'],"Sem sql",$key);
																		}
																	} //end if(($valor_parcial - $valor)>0)
															}//end else if(!$rs_rastreab)
														}//end if(!empty($canal))
														else {
															 $msg .= "<font color='#FF0000'><b>Erro ao capturar o CANAL do PIN (".$key.").\n</b></font><br>"; 
														}
													}//end if(RetonaTamanhoPINEPPCASH) 

													//Testando se é PIN GoCASH
													elseif(RetonaTamanhoPINGoCASH($key)) {
		//echo "Tamanho GoCASH[$tamanho_pin]<br>";
														//A rastreabilidade do depósito esta no id_venda_origem
														$valor_parcial_gocash += $value;
														//if(($valor_parcial_gocash - $valor)>0)
														if(($valor_parcial_gocash - $valores_processados['TOTAL_PIN_GOCASH_UTILIZADO'])>0)
														{
															if(empty($parte_do_pin_gocash)){
																$valor_composicao	= ($valor_parcial_gocash - $valores_processados['TOTAL_PIN_GOCASH_UTILIZADO']);//$valor);
																$parte_do_pin_gocash		= 1;
															}
															else {
																$valor_composicao	= $value;
															}

															//Canal definido como Cartão
															$canal		= 'C';
															//Definindo comissão
															$comissao	= $GLOBALS['GOCASH_CUSTO'];		//15;
													// Temporário até garantir que $GOCASH_CUSTO está definido aqui
													if(!$comissao) $comissao = 15;
															
															$maior['canal']		= $canal;
															$maior['id']		= 0;
															$maior['comissao']	= $comissao; 
															$maior['financial']	= 0;
															
															if(!insereSaldoComposicaoFifo($valor_composicao,$canal,$comissao,$novo_venda_id)) {
																$msg .= "Erro ao inserir a composição do saldo. Por favor, tente novamente atualizando a página. Obrigado 215 EPP CARTÃO.\n";
															}
														} //end if(($valor_parcial_gocash - $valor)>0)

													}//end elseif(RetonaTamanhoPINGoCASH)

												}//end foreach($_SESSION['PINEPP'] as $key => $value) 
											}//end if (is_array($_SESSION['PINEPP']))
											//Verificando a composição do Pagamento
											//echo $auxComposicaoPagto."<br>";

											//Se tiver algum PIN EPP informado no carrinho de PINs
											if(($valores_processados['RESTO_PIN_EPP_DEPOSITO']+$valores_processados['RESTO_PIN_GOCASH_DEPOSITO']) > 0 || $valores_processados['TOTAL_PIN_EPP_UTILIZADO'] > 0 || $valores_processados['TOTAL_SALDO_UTILIZADO'] > 0 || $valores_processados['TOTAL_PIN_GOCASH_UTILIZADO'] > 0) {
												if ($auxComposicaoPagto>1) {
													
													//função que busca o pior caso de comissão para a composição do saldo
													if(!buscaCanalnaComposicaoSaldo($maior,$venda_id)){
														$msg .= "Erro ao buscar o Canal da composição do saldo.\n<br>";
													}
													
													$maior['financial']	= 1;
													$maior['valor']		= $valor;
												}
												//inserir na nova tabela o registro de maior comissão
												if(!insereRegistro_tb_venda_games_pinepp_origem($maior,$venda_id)) {
													 $msg .= "<font color='#FF0000'><b>Erro ao inserir o Registro do Canal Venda PIN Cash (".$maior['canal'].").\n</b></font><br>";
												}
											}//end if($valores_processados['RESTO_PIN_EPP_DEPOSITO'] > 0 || $valores_processados['TOTAL_PIN_EPP_UTILIZADO'] > 0) 

		//ver aqui a necessidade de gerar tb_venda_games_pingocash_origem

											$sql ="UPDATE usuarios_games SET ug_perfil_saldo=".$saldoFinal." where ug_id=".intval($usuarioGames->ug_id);
		//echo "<br>SQL: ".$sql."<br>";
											if($sDebug) gravaLog_EPPCASH("SQL que altera registro na tabela usuarios_games:\n$sql\n(ug_id: ".$usuarioGames1->getId().")");
											$rs_saldo = SQLexecuteQuery($sql);
											if(!$rs_saldo) {
												 $msg .= "<font color='#FF0000'><b>Erro ao atualizar o Saldo do Usu&aacute;rio .\n</b></font><br>";
											}
											else {
												$usuarioGames->setPerfilSaldo(trim($saldoFinal));
												$_SESSION['usuarioGames_ser'] = serialize($usuarioGames);
												if($sDebug) gravaLog_EPPCASH("Atualizando o saldo na session para [$saldoFinal](ug_id: ".$usuarioGames1->getId().")");
											}
										}
									}//end if(updatePagamentoCompraPrincipal($valores_processados,$venda_id,&$msg))
								}//end if ($usuarioGames->ug_id != "" && $usuarioGames->ug_id)
								else {
									$msg .= "<font color='#FF0000'><b>Sistema obteve TimeOut.\n<br>Favor logar novamente.</b></font><br>";
								}
							}//end if (($saldo+$aux_valor_PINs)>= $valor)
							else {
								$msg .= "<font color='#FF0000'><b>Sistema obteve TimeOut.\n<br>Favor logar novamente.</b></font><br>"; //Saldo mais PINs insuficiente para efetuar a compra.
							}
						}//end if($msg == "")
					}//end else do if ($saldo <> $saldoDB)
				}//end else if ($ultimo_status <> 1)
			}//end if($msg == "")
		//$msg .= "FORÇANDO ROLLBACK!!!<br>";
			//Finaliza transacao
			if($msg == ""){
				$sql = "COMMIT TRANSACTION ";
				if($sDebug) gravaLog_EPPCASH($sql."(ug_id: ".$usuarioGames1->getId().")");
				$ret = SQLexecuteQuery($sql);
				if(!$ret) $msg .= "<font color='#FF0000'><b>Erro ao comitar transa&ccedil;&atilde;o.\n<br></b></font><br>";
				else unset($_SESSION['venda']);
			} else {
				$msg2 = "";
				$sql = "ROLLBACK TRANSACTION ";
				if($sDebug) gravaLog_EPPCASH($sql."(ug_id: ".$usuarioGames1->getId().")");
				$ret = SQLexecuteQuery($sql);
				if(!$ret) $msg2 .= "<font color='#FF0000'><b>Erro ao dar rollback na transa&ccedil;&atilde;o.\n<br></b></font><br>";
				elseif(($valores_processados['RESTO_PIN_GOCASH_DEPOSITO']+$valores_processados['TOTAL_PIN_GOCASH_UTILIZADO']) > 0) {
						if($msg2 == "") {
							$info_insert = array(
							'NOVO_ID'		=> $novo_venda_id,
							'UG_ID'			=> $usuarioGames->ug_id,
							'VALOR_DEP'		=> ($valores_processados['RESTO_PIN_GOCASH_DEPOSITO']+$valores_processados['TOTAL_PIN_GOCASH_UTILIZADO']),
							'VALOR_GOCASH'	=> ($valores_processados['RESTO_PIN_GOCASH_DEPOSITO']+$valores_processados['TOTAL_PIN_GOCASH_UTILIZADO']),
							'VALOR_EPPCASH' => 0,
							'ID_ORIGEM'		=> 0
							);
							if(!deposita_gocash_rollback($info_insert,$a_pins_gocash,$valor_saldo_rollback,$msg2)) {
								echo "ERROR ROLLBACK.<BR>";
							}//end if(deposita_gocash_rollback($info_insert,$a_pins_gocash,$valor_saldo_rollback,$msg2))
						}//end if($msg2 == "")
				}//end elseif(($valores_processados['RESTO_PIN_GOCASH_DEPOSITO']+$valores_processados['TOTAL_PIN_GOCASH_UTILIZADO']) > 0)

				$msg .= $msg2;

				gravaLog_EPPCASH("Final: ".$msg."(ug_id: ".$usuarioGames1->getId().")");
				//Voltando o valor do saldo para a SESSION			
				$usuarioGames->setPerfilSaldo(trim($valor_saldo_rollback));
				$_SESSION['usuarioGames_ser'] = serialize($usuarioGames);
			}//end do ROLLBACK

			//desbloqueando os PINs
			flag_pin_unblock();

		}//end else do if (flag_pin_test())

		// Unblock the saldo 
		flag_user_unblock($usuarioGames1->getId());

	}//end do if(flag_user_test($usuarioGames1->getId()) )

	sleep(1);
	echo $msg;
	if($msg == "") {
		return true;
	}
	else {
		return false;
	}
}//end function utilizar_pin() 


function utilizar_pin_carga() { 
	global $PINS_STORE_STATUS_VALUES,$STATUS_VENDA,$PINS_STORE_MSG_LOG_STATUS,$saldo,$DISTRIBUIDORAS,$PAGAMENTO_PIN_EPREPAG_NUMERIC;
	//Libera o LOG
	$sDebug = true;
	$msg = "";

	$time_start_stats = getmicrotime();

	if($sDebug) gravaLog_EPPCASH("Dentro da funcao utilizar_pin_carga() no ajax_pin_carga.php");

	$usuarioGames1 = unserialize($_SESSION['usuarioGames_ser']);
	if(flag_user_test($usuarioGames1->getId()) ) {
		$msg .= "<font color='red'>Conta com saldo em uso, faça login novamente, por favor.</font><br>";
		if($sDebug) gravaLog_EPPCASH("FLAG carga - Blocked - go away");
	} else {

		//bloqueia os PINs
		if (flag_pin_test()) {
			$msg = "<font color='red'>PINs já estão sendo utilizados. Aguarde alguns segundos, por favor.</font><br>";
		}
		else {

			$time_stop_stats = getmicrotime(); 
			if($sDebug) gravaLog_EPPCASH("FLAG Carga - Free - block it (Delay window (ug_id: ".$usuarioGames1->getId()."): ".number_format($time_stop_stats - $time_start_stats, 12, '.', '.').")");

			// Atualiza valor saldo do BD
			$saldo = getSaldoUsuarioFunc();

			//Inicia transacao
			$sql = "BEGIN TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg .= "<font color='#FF0000'><b>Erro ao iniciar transa&cceil;&atilde;o.\n</b></font><br>";
			//variavel de acumula valores de TODOS PINs utilizados na compra
			$aux_valor_PINs = 0;
			//variavel de acumula valores de PINs EPP CASH utilizados na compra
			$aux_valor_PINs_EPP = 0;
			//variavel de acumula valores de PINs GoCASH utilizados na compra
			$aux_valor_PINs_GoCASH = 0;

			//transacao
			if($msg == ""){
				$usuarioGames = unserialize($_SESSION['usuarioGames_ser']);
				$saldoDB = getSaldoUsuarioFunc();
				if($sDebug) gravaLog_EPPCASH("Antes do if (saldo <> saldoDB)\n Saldo antes do transaction: saldo [$saldo]\n Saldo retornado pelo SQL: rs_user_saldo_row['ug_perfil_saldo']: [".$saldoDB."]");
				//variavel utilizada para rollback da SESSION
				$valor_saldo_rollback = $saldoDB;
				if ($saldo <> $saldoDB) {
					$msg .= "<font color='#FF0000'><b>Saldo n&atilde;o confere com o banco de dados(R$ ".$saldo.",00). <br>Favor, fazer login novamente.\n</b></font><br>";
				}
				else {

					if (is_array($_SESSION['PINEPP'])) {
						if($sDebug) gravaLog_EPPCASH("Existe carrinho contendo PINs (COD.B) (ug_id: ".$usuarioGames1->getId().")");
						//instanciando a classe de cryptografia
						$chave256bits = new Chave();
						$aes = new AES($chave256bits->retornaChave());

	//echo "PIN_NOMINAL:<pre>".print_r($_SESSION['PIN_NOMINAL'],true)."</pre>";
	//echo "PINEPP:<pre>".print_r($_SESSION['PINEPP'],true)."</pre>";
							
						//Captura somente os GoCASH com seus respectivos valores nominais
						$a_pins_gocash = monta_array_somente_gocash($aux_valor_PINs_GoCASH);

	// Dummy
						if($sDebug) gravaLog_EPPCASH("a_pins_gocash:".print_r($a_pins_gocash,true)." (ug_id: ".$usuarioGames1->getId().")");
						
	//echo "aux_valor_PINs_GoCASH:$aux_valor_PINs_GoCASH<br>";
	//echo "a_pins_gocash:<pre>".print_r($a_pins_gocash,true)."</pre>";
	//echo "Count a_pins_gocash:".count($a_pins_gocash)."<br>";

						//Foreach para PIN CASH
						$novo_venda_id = obterIdVendaValido();

						if($sDebug) gravaLog_EPPCASH("Novo venda_id [$novo_venda_id] (ug_id: ".$usuarioGames1->getId().")");

	//echo "[$novo_venda_id]<br>";
						if((count($a_pins_gocash)==0)||SaveRedeemPinTransaction($a_pins_gocash,$novo_venda_id)) {

							$aux_valor_PINs += $aux_valor_PINs_GoCASH;
										
							foreach($_SESSION['PINEPP'] as $key => $value) {

								if(RetonaTamanhoPINEPPCASH($key)) {
									RedeemPIN_EPP_CASH($key,$value,$msg,$aux_valor_PINs_EPP,$aux_valor_PINs);
								}//end if(RetonaTamanhoPINEPPCASH) 

							}//end foreach

						}// end if((count($a_pins_gocash)==0)||SaveRedeemPinTransaction($a_pins_gocash,$novo_venda_id) 
						else {
							$msg .= "<font color='#FF0000'><b>Erro ao validar PIN GoCASH no Transaction B.\n</b></font><br>";
						}//end else if((count($a_pins_gocash)==0)||SaveRedeemPinTransaction($a_pins_gocash,$novo_venda_id)
					}//end if (is_array($_SESSION['PINEPP']))
					
					if($msg == ""){ 
	//$msg .= "AKI nada";
						//Distribuindo os valores para processamento
						$valor = 0; //Atribuindo valor da compra copmo zero para a distribuição funcionar corretamente
						$valores_processados = RetonaComposicaoPagamento($valor,$saldo,$aux_valor_PINs_EPP,$aux_valor_PINs_GoCASH);
							
	// Dummy							
	//echo "<pre>".print_r($valores_processados,true)."</pre>";

						 if ($aux_valor_PINs > 0) {
							//Capturando o Saldo FINAL
							$saldoFinal = $valores_processados['SALDO_FINAL'];
														
							if ($usuarioGames->ug_id != "" && $usuarioGames->ug_id) {
								// Cadastra uma venda sem modelo para o que sobrou da utilização de PINs EPP e GoCASH 
								if( ($valores_processados['RESTO_PIN_EPP_DEPOSITO']+$valores_processados['RESTO_PIN_GOCASH_DEPOSITO'])  > 0) {
									if($sDebug) gravaLog_EPPCASH("Dentro do IF (CARRINHO PIN EPP + CARRINHO PIN GoCASH -  valor da compra > 0) - Ou seja, gerar o registro de deposito em saldo da diferença.");
									$info_insert = array(
											'NOVO_ID'		=> $novo_venda_id,
											'UG_ID'			=> $usuarioGames->ug_id,
											'VALOR_DEP'		=> ($valores_processados['RESTO_PIN_EPP_DEPOSITO']+$valores_processados['RESTO_PIN_GOCASH_DEPOSITO']),
											'VALOR_GOCASH'	=> $valores_processados['RESTO_PIN_GOCASH_DEPOSITO'],
											'VALOR_EPPCASH' => $valores_processados['RESTO_PIN_EPP_DEPOSITO'],
											'ID_ORIGEM'		=> 0
											);
									deposita_em_saldo($info_insert, $msg);
									
								} //if( ($valores_processados['RESTO_PIN_EPP_DEPOSITO']+$valores_processados['RESTO_PIN_GOCASH_DEPOSITO'])  > 0)
								//Capturando o ID do Pagamento
								$idpgto = selecionaIDPagto($novo_venda_id,$msg);
								$maior['comissao']	= 0;
								$maior['valor']		= 0;
								$maior['canal']		= 0;
								$maior['id']		= 0;
								$maior['financial']	= 0;
								if (is_array($_SESSION['PINEPP'])) {
									foreach($_SESSION['PINEPP'] as $key => $value) {
												
										if(RetonaTamanhoPINEPPCASH($key)) {
	//$msg .= "AKIII EPP";
											//função que captura dados para o insert na tabela pins_store_pag_epp_pin
											// a função também atualiza o vetor contendo a maior comissão
											capturaCanalComissaoPINEPP($key, $value, $maior, $canal, $id, $comissao, $financial);

											if(!empty($canal)) {
												//insere o registrio na tabela pins_store_pag_epp_pin
												if(!insereRegistro_pins_store_pag_epp_pin($key,$novo_venda_id,$idpgto,$canal,$id,$comissao,$financial)) {
													 $msg .= "<font color='#FF0000'><b>Erro ao atualizar a Rastreabilidade do PIN (".$key.").\n</b></font><br>";
												}
												else {
														if(!insereSaldoComposicaoFifo($value,$canal,$comissao,$novo_venda_id)) {
															$msg .= "Erro ao inserir a composição do saldo. Por favor, tente novamente atualizando a página. Obrigado 215 EPP CASH.\n";
															log_pin($GLOBALS['PINS_STORE_MSG_LOG_STATUS']['ERRO_STRANSACAO'],"Sem sql",$key);
														}else{
															log_pin($GLOBALS['PINS_STORE_MSG_LOG_STATUS']['SUCESSO_CTRANSACAO'],"Sem sql",$key);
														}
												}//end else if(!$rs_rastreab)
											}//end if(!empty($canal))
											else {
												 $msg .= "<font color='#FF0000'><b>Erro ao capturar o CANAL do PIN (".$key.").\n</b></font><br>"; 
											}
										}//end if(RetonaTamanhoPINEPPCASH) 

										//Testando se é PIN GoCASH
										elseif(RetonaTamanhoPINGoCASH($key)) {
	//$msg .= "AKI GOCASH";

											//Canal definido como Cartão
											$canal		= 'C';
											//Definindo comissão
											$comissao	= 15;
											
											$valor_composicao	= $value;

											$maior['canal']		= $canal;
											$maior['id']		= 0;
											$maior['comissao']	= $comissao; 
											$maior['financial']	= 0;
											
											if(!insereSaldoComposicaoFifo($valor_composicao,$canal,$comissao,$novo_venda_id)) {
												$msg .= "Erro ao inserir a composição do saldo. Por favor, tente novamente atualizando a página. Obrigado 215 EPP CARTÃO.\n";
											}
											
										}//end elseif(RetonaTamanhoPINGoCASH)

									}//end foreach($_SESSION['PINEPP'] as $key => $value) 
								}//end if (is_array($_SESSION['PINEPP']))

								//inserir na nova tabela o registro de maior comissão
								if(!insereRegistro_tb_venda_games_pinepp_origem($maior,$novo_venda_id)) {
									 $msg .= "<font color='#FF0000'><b>Erro ao inserir o Registro do Canal Venda PIN Cash (".$maior['canal'].").\n</b></font><br>";
								}
								
								$sql ="UPDATE usuarios_games SET ug_perfil_saldo=".$saldoFinal." where ug_id=".intval($usuarioGames->ug_id);
								if($sDebug) gravaLog_EPPCASH("SQL que atualiza o saldo usuario na tabela usuarios_games:\n$sql");
											
	//echo "<br>SQL: ".$sql."<br>";
								$rs_saldo = SQLexecuteQuery($sql);
								if(!$rs_saldo) {
									 $msg .= "<font color='#FF0000'><b>Erro ao atualizar o Saldo do Usu&aacute;rio .\n</b></font><br>";
								}
								else {
									$usuarioGames->setPerfilSaldo(trim($saldoFinal));
									$_SESSION['usuarioGames_ser'] = serialize($usuarioGames);
								}

							}//end if ($usuarioGames->ug_id != "" && $usuarioGames->ug_id)
							else {
								$msg .= "<font color='#FF0000'><b>Sistema obteve TimeOut.\n<br>Favor logar novamente.</b></font><br>";
							}
						}//end if ($aux_valor_PINs > 0) 
					}//end if($msg == "")
				}//end else if ($saldo <> $saldoDB)
			}//end if($msg == "")
			//$msg = "TESTE ROLLBACK";
			//Finaliza transacao
			if($msg == ""){
				$sql = "COMMIT TRANSACTION ";
				$ret = SQLexecuteQuery($sql);
				if(!$ret) $msg .= "<font color='#FF0000'><b>Erro ao comitar transa&ccedil;&atilde;o.\n<br></b></font><br>";
				else unset($_SESSION['venda']);
			} else {
				$msg2 = "";
				$sql = "ROLLBACK TRANSACTION ";
				$ret = SQLexecuteQuery($sql);
				if(!$ret) $msg2 .= "<font color='#FF0000'><b>Erro ao dar rollback na transa&ccedil;&atilde;o.\n<br></b></font><br>";
				elseif(($valores_processados['RESTO_PIN_GOCASH_DEPOSITO']+$valores_processados['TOTAL_PIN_GOCASH_UTILIZADO']) > 0) {
						if($msg2 == "") {
							$info_insert = array(
							'NOVO_ID'		=> $novo_venda_id,
							'UG_ID'			=> $usuarioGames->ug_id,
							'VALOR_DEP'		=> ($valores_processados['RESTO_PIN_GOCASH_DEPOSITO']+$valores_processados['TOTAL_PIN_GOCASH_UTILIZADO']),
							'VALOR_GOCASH'	=> ($valores_processados['RESTO_PIN_GOCASH_DEPOSITO']+$valores_processados['TOTAL_PIN_GOCASH_UTILIZADO']),
							'VALOR_EPPCASH' => 0,
							'ID_ORIGEM'		=> 0
							);
							if(!deposita_gocash_rollback($info_insert,$a_pins_gocash,$valor_saldo_rollback,$msg2)) {
								echo "ERROR ROLLBACK.<BR>";
							}//end if(deposita_gocash_rollback($info_insert,$a_pins_gocash,$valor_saldo_rollback,$msg2))
						}//end if($msg2 == "")
				}//end elseif(($valores_processados['RESTO_PIN_GOCASH_DEPOSITO']+$valores_processados['TOTAL_PIN_GOCASH_UTILIZADO']) > 0)

				$msg .= $msg2;

				gravaLog_EPPCASH("Final: ".$msg);
				//Voltando o valor do saldo para a SESSION			
				$usuarioGames->setPerfilSaldo(trim($valor_saldo_rollback));
				$_SESSION['usuarioGames_ser'] = serialize($usuarioGames);
			}//end rollback

			//desbloqueando os PINs
			flag_pin_unblock();

		}//end else do if (flag_pin_test())

		// Unblock the saldo 
		flag_user_unblock($usuarioGames1->getId());

	}//end do if(flag_user_test($usuarioGames1->getId()) )
	
	sleep(1);
	echo $msg;
	if($msg == "") {
		return true;
	}
	else {
		return false;
	}
}//end function utilizar_pin_carga() 

?>
