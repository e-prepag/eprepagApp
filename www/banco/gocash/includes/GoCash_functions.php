<?php

function StatusInquiryPIN($pinNumber, &$resultGoCashWS) {
	// =========================================================================================
	// Status
//	$time_start_stats = getmicrotime();

	$params = array();
	$params["SerialCode"]	= $pinNumber;
	$params["Currency"]		= GC_CURRENCY_BRL;

	$gc = new GoCashAPI();
	if($gc->get_service_status()) {
		$resultGoCashWS = $gc->SerialCheckAction($params);

//echo "STATUS RESPONSE:<pre>".print_r($resultGoCashWS, true)."</pre>";
gravaLog_GoCash("PIN: ".$pinNumber."\nSTATUS RESPONSE:\n".print_r($resultGoCashWS, true)."\n");
		if($resultGoCashWS['ReturnValue']['RetCode']===0) {
//echo "<p>STATUS SUCESSO: FaceAmt: '".$resultGoCashWS['FaceAmt']."', RemainAmt: '".$resultGoCashWS['RemainAmt']."', Currency: '".$resultGoCashWS['Currency']."'</p>";
		} else {
//echo "<p>STATUS ERRO - Retorno: '".$resultGoCashWS['ReturnValue']['RetCode']."' -> '".$resultGoCashWS['ReturnValue']['RetMsg']."'</p>";
		}
	//	echo "Elapsed time ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.')."s<br>";
	//	echo "<hr>";
		return ($resultGoCashWS['ReturnValue']['RetCode']===0);
	} else {
		return false;
	}
}


function StatusInquiryPIN_value($pinNumber) {
	$ret = StatusInquiryPIN($pinNumber, $resultGoCashWS);
	if($ret) {
		return $resultGoCashWS['FaceAmt']; 
	} else {
		return 0; 
	}
}

// Processa uam lista de PINs GoCash
//
// Retorna true só se todos os Redeem foram com sucesso
// SaveRedeemPinTransaction($a_pin_gocash,$venda_id)
// $a_pin_gocash = (
//					pin_code => pin_value,
//					)
function SaveRedeemPinTransaction($a_pin_gocash, $venda_id, $opr_codigo = 0) {
// Dummy 
//echo "EM SaveRedeemPinTransaction (venda_id: $venda_id):<pre>".print_r($a_pin_gocash, true)."</pre>";
	if(count($a_pin_gocash)>0) {
		$i = 0;
		// processa cada PIN
		foreach($a_pin_gocash as $pin => $pin_valor_nominal) {
//echo "[$i] $pin => $pin_valor_nominal<br>";
			// 1 - Confere Status do PIN
			$pin_value = StatusInquiryPIN_value($pin);
			if($pin_value>0) {
				// 2 - Confere Valor do PIN
				if($pin_value===$pin_valor_nominal) {
					$usuario_id = 0;
					if(isset($GLOBALS['_SESSION']['usuarioGames_ser'])) {
						$usuarioGames = unserialize($_SESSION['usuarioGames_ser']);
						$usuario_id = $usuarioGames->getId();
					}

					// 3 - Redeem 
/*
$b_is_reinaldo = false;
if(isset($GLOBALS['_SESSION']['usuarioGames_ser'])) {
	$usuarioGames = unserialize($_SESSION['usuarioGames_ser']);
	if($usuarioGames->b_IsLogin_reinaldopshotmail()) {
		$b_is_reinaldo = true;
echo "In PayCashPIN()<br>\n";
	}
}
*/

					$order_no = str_pad($venda_id , 8, "0", STR_PAD_LEFT) ."_". str_pad($i, 2, "0", STR_PAD_LEFT);

					$ret = PayCashPIN($pin, $order_no, $pin_valor_nominal, $resultGoCashWS_Pay);
					$msg = "pin: $pin, order_no: $order_no, pin_valor_nominal: $pin_valor_nominal, \nresultGoCashWS_Pay: ".print_r($resultGoCashWS_Pay, true)."\n";

					if($ret) {

						// 4 - Salva o pagamento junto com o venda_id
	
						$params = array();
						$params['PinNumber'] = $pin;
						$params['FaceAmount'] = $pin_valor_nominal;
						$params['Currency'] = "BRL";
						$params['IDVenda'] = $venda_id;
						$params['OrderNo'] = $order_no;
						$params['IDUsuario'] = $usuario_id;
						$params['RespDate'] = date("Y-m-d H:i:s");
						$params['IDOperadora'] = $opr_codigo;

						$gc = new GoCashAPI();

						$gc->saveSoapTransaction($params);

						$msg .= "  == SUCCESS\n";
						gravaLog_GoCash($msg);
//echo "<font color='red'>Sleep(2)</font><br>";
						sleep(2);
					} else {

						$msg .= "  == FAILED\n";
						gravaLog_GoCash($msg);
						return false;
					}


				} else {
					// ERROR Log -> valor do PIN reportado deferente do valor consultado
					$msg = "ERROR Log -> valor do PIN reportado deferente do valor consultado\n";
					gravaLog_GoCash($msg);
					return false;
				}
			} else {
				// ERROR Log -> valor do PIN zero
				$msg = "ERROR Log -> valor do PIN zero\n";
				gravaLog_GoCash($msg);
				return false;
			}
			$i++;
		}
	} else {
		// 
		$msg = "ERROR Log -> carrinho GoCash sem PINs, por que está aqui?\n";
		gravaLog_GoCash($msg);
		return false;
	}
	return true;
}

function PayCashPIN($pinNumber, $order_no, $pin_valor_nominal, &$resultGoCashWS_Pay) {
// Redeem
//	$time_start_stats = getmicrotime();
/*
$b_is_reinaldo = false;
if(isset($GLOBALS['_SESSION']['usuarioGames_ser'])) {
	$usuarioGames = unserialize($_SESSION['usuarioGames_ser']);
	if($usuarioGames->b_IsLogin_reinaldopshotmail()) {
		$b_is_reinaldo = true;
echo "In PayCashPIN()<br>\n";
	}
}
*/
	$params = array();
	$params["ClientID"]		= GC_CLIENT_ID;
	$params["SerialCodes"]	= $pinNumber;
	$params["ServiceName"]	= "ePrepag";
	$params["GameCode"]		= "Null";
	$params["IPAddr"]		= $_SERVER['REMOTE_ADDR'];
	$params["Currency"]		= GC_CURRENCY_BRL;

	// user params - standard EPP user
	$params["PayerName"]	= "E-Prepag";
	$params["PayerEmail"]	= "suporte@e-prepag.com.br";
	$params["PayerID"]		= "8002";

	// product params 
	$params["OrderNo"]		= $order_no;
	$params["ProdName"]		= "EPP Prod";
	$params["ChargeAmt"]	= $pin_valor_nominal;

//if($b_is_reinaldo) {
//	echo "REDEEM PARAMS: <pre>".print_r($params, true)."</pre>\n";
//}

	$gc = new GoCashAPI();
	if($gc->get_service_status()) {
		$resultGoCashWS_Pay = $gc->PayCashAction($params);

//if($b_is_reinaldo) {
//echo "REDEEM RESPONSE: <pre>".print_r($resultGoCashWS_Pay, true)."</pre>\n";
//}

		if($resultGoCashWS_Pay['ReturnValue']['RetCode']===0) {
//if($b_is_reinaldo) {
//echo "<p>REDEEM SUCESSO: FaceAmt: '".$resultGoCashWS_Pay['FaceAmt']."', RemainAmt: '".$resultGoCashWS_Pay['RemainAmt']."', Currency: '".$resultGoCashWS_Pay['Currency']."'</p>";
//}
		} else {
//if($b_is_reinaldo) {
//echo "<p>REDEEM ERRO - Retorno: '".$resultGoCashWS_Pay['ReturnValue']['RetCode']."' -> '".$resultGoCashWS_Pay['ReturnValue']['RetMsg']."'</p>";
//}
		}
	//	echo "Elapsed time ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.')."s<br>";

		return ($resultGoCashWS_Pay['ReturnValue']['RetCode']===0);
	} else {
		return false;
	}
}

//Função que verifica se o tamanho do PIN pertence à um PIN GoCASH
function RetonaTamanhoPINGoCASH($pin) {
	$tamanho = strlen($pin);
	if(in_array($tamanho, $GLOBALS['PIN_GOCASH_TAMANHO'])) {
		if($tamanho == $GLOBALS['PIN_STORE_TAMANHO']) {
			//instanciando a classe de cryptografia
			$chave256bits = new Chave();
			$aes = new AES($chave256bits->retornaChave());
			//Teste existencia na tabela de exceção de gocash com tamanho de 16
			$sql = "select * from pins_gocash_lote16 where pgcl_pin_number_encrypt = '".base64_encode($aes->encrypt(addslashes($pin)))."'";
			$rs_pins_gocash = SQLexecuteQuery($sql);
			if($rs_pins_gocash && pg_num_rows($rs_pins_gocash) > 0) {
				return true;
			}
			else {
				return false;
			}
		} //end if($tamanho == $GLOBALS['PIN_STORE_TAMANHO'])
		else {
			return true;
		}
	}//end if(in_array($tamanho, $GLOBALS['PIN_GOCASH_TAMANHO']))
	else {
		return false;
	}
}//end function RetonaTamanhoPINGoCASH($pin)


?>