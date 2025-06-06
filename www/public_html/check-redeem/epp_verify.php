<?php
//Verificando se a execução foi acionada a apartir CHECK-REDEEM
$teste = debug_backtrace();
if (strpos($teste[0]["file"], "check-redeem/index.php")) {

	//Include comumm de integração de PINs Publishers
	require_once $raiz_do_projeto . "class/classIntegracaoPinPub.php";

	$pin_code = isset($_POST["pin_code"]) ? $_POST["pin_code"] : null;
	$pin_value = isset($_POST["pin_value"]) ? $_POST["pin_value"] : null;
	$id = isset($_POST["id"]) ? $_POST["id"] : null;
	$action = isset($_POST["action"]) ? $_POST["action"] : null;
	$riot_order_id = isset($_POST["riot_order_id"]) ? $_POST["riot_order_id"] : null;
	$checkout_id = isset($_POST["checkout_id"]) ? $_POST["checkout_id"] : null;

	send_debug_info_by_email_PINCASH('Teste ID [' . $id . ']', 'PIN CODE: [' . $pin_code . ']<br>PIN VALUE: [' . $pin_value . ']<br>ACTION: [' . $action . ']<br>ID: [' . $id . ']<br>POST:<pre>' . print_r($_POST, true) . '</pre>', $partner_dep, $id);
	//gravaLog_IntegracaoPIN('PIN CODE: ['.$pin_code.']'.PHP_EOL.'PIN VALUE: ['.$pin_value.']'.PHP_EOL.'ACTION: ['.$action.']'.PHP_EOL.'ID: ['.$id.']'.PHP_EOL.'POST:'.print_r($_POST,true));
	//Variavel que coloca o sistema em OFF LINE qdo FALSE
	$auxOnLine = true;

	//Convertendo para não considerar CENTAVOS
	$pin_value = $pin_value / 100;

	//echo $pin_value."<br>";

	$params = array(
		'pin_code' => array(
			'0' => $pin_code,
			'1' => 'S',
			'2' => '1'
		),
		'pin_value' => array(
			'0' => $pin_value,
			'1' => 'F',
			'2' => '1'
		),
		'id' => array(
			'0' => $id,
			'1' => 'I',
			'2' => '1'
		),
		'action' => array(
			'0' => $action,
			'1' => 'I',
			'2' => '1'
		),
		'riot_order_id' => array(
			'0' => $riot_order_id,
			'1' => 'S',
			'2' => '1'
		),
	);
	$params = sanitize_input_data_array($params, $err_cod);
	extract($params, EXTR_OVERWRITE);

	//echo $pin_value."<br>";

	$aux_codretepp = '0';
	$aux_pin_value = null;

	if ($auxOnLine) {
		if (empty($pin_code) && empty($id) && empty($action)) {
			$aux_codretepp = $notify_list_values['F4'];
		} elseif (empty($pin_code)) {
			$aux_codretepp = $notify_list_values['FC'];
		} elseif (empty($pin_value) && ($action == '2')) {
			$aux_codretepp = $notify_list_values['FV'];
		} elseif (empty($action)) {
			$aux_codretepp = $notify_list_values['FA'];
		} elseif (empty($id)) {
			$aux_codretepp = $notify_list_values['FI'];
		} elseif (retorna_id_pin($pin_code, $id) <> 0) {

			$aux_opr_ip = retorna_ip_integracao($id);

			//$vetor_IPs = explode(';', $aux_opr_ip);
			$aux_teste_IP = false;
			$controleIP = new ControleIP();
			if ($controleIP->isInOprRange($aux_opr_ip, retorna_ip_acesso())) {
				$aux_teste_IP = true;
				$dominio_check = retorna_dominio($id);
			}

			/*
				  for ($i = 0; $i < count($vetor_IPs); $i++) {
				  if (trim($vetor_IPs[$i]) == retorna_ip_acesso()) {
				  $aux_teste_IP = true;
				  $dominio_check = retorna_dominio($id);
									  }//end if
				  }
							  */
			send_debug_info_by_email_PINCASH('Teste ID [' . $id . ']', 'IPs Permitidos: [' . $aux_opr_ip . ']<br>IP Utilizado: [' . retorna_ip_acesso() . ']<br>Verificação de IP retornou: [' . $aux_teste_IP . ']<br>Dominio capturando no if ( controleIP->isInOprRange(aux_opr_ip, retorna_ip_acesso()) ): [' . $dominio_check . ']<br>', $partner_dep, $id);
			gravaLog_IntegracaoPIN('PIN [' . $pin_code . ']' . PHP_EOL . 'IPs Permitidos: [' . $aux_opr_ip . ']' . PHP_EOL . 'IP Utilizado: [' . retorna_ip_acesso() . ']' . PHP_EOL . 'Verificação de IP retornou: [' . $aux_teste_IP . ']' . PHP_EOL . 'Dominio capturando no if ( controleIP->isInOprRange(aux_opr_ip, retorna_ip_acesso()) ): [' . $dominio_check . ']' . PHP_EOL);

			if ($aux_opr_ip <> 0 && $aux_teste_IP) {
				$aux_status_value = retorna_status($pin_code, $id);
				//echo $aux_status_value."<br>".$PINS_PUBLISHERS_STATUS_VALUES['V']."<br>".$PINS_PUBLISHERS_STATUS_VALUES['L']."<br>".$PINS_PUBLISHERS_STATUS_VALUES['P']."<br>";
				if (($aux_status_value == $PINS_PUBLISHERS_STATUS_VALUES['V']) || ($aux_status_value == $PINS_PUBLISHERS_STATUS_VALUES['L']) || ($aux_status_value == $PINS_PUBLISHERS_STATUS_VALUES['P'])) {
					if ($action == '1') {
						$pin_valido = verifica_validade($pin_code, $id);
						if ($pin_valido === true) {
							$aux_codretepp = $notify_list_values['SV'];
							$aux_pin_value = retorna_pin_valor($pin_code, $id);
						} else {
							$aux_codretepp = $notify_list_values['SD'];
							$aux_pin_value = 0;
						}
					} elseif ($action == '2') {
						$pin_valido = verifica_validade($pin_code, $id);
						if ($pin_valido === true) {
							if (verifica_valor_pin($pin_code, $pin_value, $id)) {
								$sql_opr = "select opr_use_check,opr_partner_check from operadoras where opr_codigo=" . $id;
								$rs_oper = SQLexecuteQuery($sql_opr);
								$rs_oper_row = pg_fetch_array($rs_oper);
								if ($rs_oper_row['opr_use_check'] == 1) {
									if (empty($dominio_check)) {
										$aux_codretepp = $notify_list_values['PO'];
									} //end if(empty($dominio_check))
									else {

										// Build a postback string
										$post_parameters = array(
											'PIN_CODE' => $pin_code,
											'PIN_VALUE' => $pin_value * 100,
											'ID' => $id,
											'ACTION' => $action
										);
										/*
										 * 13 => Ongame
										 * 73 => Publisher Teste
										 * 88 => RIOT Teste removido por conta de testes da RIOT
										 * 90 => RIOT Live
										 * 102=> Ongame Teste
										 * 124=> Garena
										 * 137=> Garena Teste
										 * 142=> Drummond
										 * 143=> Valofe
										 * 147=> IGG Teste
										 * 148=> IGG
										 */
										$ids_https = array(88, 90, 73, 13, 102, 124, 137, 142, 143, 147, 148, 166, 168);
										if (in_array($id, $ids_https)) {
											$url = "https://";
										} else {
											$url = "http://";
										}

										if (($id * 1) == 90 || ($id * 1) == 88) {
											$headers[] = "Content-Type: application/x-www-form-urlencoded";
											$post_parameters['CHECKOUT_ID'] = $checkout_id;
											$post_parameters = http_build_query($post_parameters);
										}
										if (($id * 1) == 124 || ($id * 1) == 137) {
											$post_parameters['PIN_VALUE'] = $pin_value;
										}
										$varAuxIP = retorna_ip_acesso();
										if ((($id * 1) == 13 || ($id * 1) == 166) && ($varAuxIP == "201.77.235.18" || $varAuxIP == "201.77.235.30")) {
											$dominio_check = "loja.ongame.net";
											$rs_oper_row['opr_partner_check'] = "payment/eprepag/pingback/";
											$headers[] = "Host: loja.ongame.net";
										}

										$url .= $dominio_check . "/" . $rs_oper_row['opr_partner_check'];
										$buffer = "";
										$curl_handle = curl_init();
										curl_setopt($curl_handle, CURLOPT_URL, $url);
										//Teste solução headers para caso TLSv1.2
										if (is_array($headers)) {
											curl_setopt($curl_handle, CURLOPT_HTTPHEADER, $headers);
											curl_setopt($curl_handle, CURLOPT_FAILONERROR, true);
											curl_setopt($curl_handle, CURLOPT_VERBOSE, true);
											$errorFileLog = fopen("/www/log/error_epp_verify.log", "a+");
											curl_setopt($curl_handle, CURLOPT_STDERR, $errorFileLog);
											curl_setopt($curl_handle, CURLOPT_HEADER, 0);
										}

										// verify the digital certificate
										curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, 0);
										//  verify digital certificate’s name
										curl_setopt($curl_handle, CURLOPT_SSL_VERIFYHOST, 0);

										curl_setopt($curl_handle, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)");
										curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
										curl_setopt($curl_handle, CURLOPT_POST, true);
										curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $post_parameters);
										// The number of seconds to wait while trying to connect. 
										// Use 0 to wait indefinitely.
										curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 0);
										// The maximum number of seconds to allow cURL functions to execute.
										curl_setopt($curl_handle, CURLOPT_TIMEOUT, 90);
										// send the request and get the response
										$buffer = curl_exec($curl_handle);

										//Verificando erro
										$erros_curl = curl_error($curl_handle);

										$infoCURL = curl_getinfo($curl_handle);

										$file = fopen("/www/log/ongame.txt", "a+");
										$data = date('d-m-Y H:i:s');
										$body = json_encode($post_parameters);
										$dados_CURL = json_encode($infoCURL);
										$dados_buffer = json_encode($buffer);
										fwrite($file, '####### START #######' . PHP_EOL .
											date('d-m-Y H:i:s') . PHP_EOL .
											'IP ' . $_SERVER['REMOTE_ADDR'] . PHP_EOL .
											'Corpo ' . $body . PHP_EOL .
											'CURL: ' . PHP_EOL . $dados_CURL . PHP_EOL .
											'BUFFER ' . PHP_EOL . $dados_buffer . PHP_EOL .
											'ERRORs: ' . PHP_EOL . $erros_curl . PHP_EOL .
											'####### END #######' . PHP_EOL);
										fclose($file);

										if (is_array($headers)) {
											//logEventsONGAME('ERROR: ['.print_r($erros_curl,true).']'.PHP_EOL.'VERSION: '.print_r(curl_version(),true).PHP_EOL);
											//logEventsONGAME('URL: ['.$url.']'.PHP_EOL.'BUFFER Partner_Check = ['.$buffer.']'.PHP_EOL);
										}//end if(is_array($headers))
										send_debug_info_by_email_PINCASH('Teste ID [' . $id . '] CURL ERROR', 'ERROR: [<PRE>' . print_r($erros_curl, true) . '] <br> VERSION: ' . print_r(curl_version(), true) . 'Post parameters: ' . print_r($post_parameters, true) . '</PRE>', $partner_dep, $id);
										gravaLog_IntegracaoPIN('PIN [' . $pin_code . ']' . PHP_EOL . 'ERROR: [' . print_r($erros_curl, true) . ']' . PHP_EOL . 'Post parameters: ' . print_r($post_parameters, true) . PHP_EOL);

										curl_close($curl_handle);
										//	echo $buffer."<br>";
										list($name, $value) = explode('=', $buffer);
										send_debug_info_by_email_PINCASH('Teste ID [' . $id . '] BUFFER Partner_Check', 'URL: [' . $url . ']<br>BUFFER Partner_Check = [' . $buffer . ']<br>', $partner_dep, $id);
										gravaLog_IntegracaoPIN('PIN [' . $pin_code . ']' . PHP_EOL . 'URL: [' . $url . ']' . PHP_EOL . 'BUFFER Partner_Check = [' . $buffer . ']' . PHP_EOL);

										//	echo "Name= ".$name." Value=".$value."<br>";
										if ($value == "1") {
											$sql = "update pins set pin_status='" . $PINS_PUBLISHERS_STATUS_VALUES['U'] . "' where (pin_status= '" . $PINS_PUBLISHERS_STATUS_VALUES['V'] . "' OR pin_status= '" . $PINS_PUBLISHERS_STATUS_VALUES['L'] . "' OR pin_status= '" . $PINS_PUBLISHERS_STATUS_VALUES['P'] . "') AND pin_codinterno=" . retorna_id_pin($pin_code, $id);
											//echo $sql;
											$rs_pin_update = SQLexecuteQuery($sql);
											if (!$rs_pin_update) {
												$aux_codretepp = $notify_list_values['EU'];
											} else {
												$cmdtuples = pg_affected_rows($rs_pin_update);
												//echo $cmdtuples . " tuples are affected.<br>\n";
												if ($cmdtuples === 1) {
													//If somente para o Publisher RIOT
													if ($id == 90) {
														publisherOrderId(retorna_id_pin($pin_code, $id), $riot_order_id, 'L');
													} //end if($id == 90)
													$aux_codretepp = $notify_list_values['SU'];
													gravaLog_IntegracaoPIN('PIN [' . $pin_code . ']' . PHP_EOL . 'Resposta EPP*: CODRETEPP=' . $notify_list_values['SU'] . PHP_EOL);
												} else {
													$aux_codretepp = $notify_list_values['EU'];
													gravaLog_IntegracaoPIN('PIN [' . $pin_code . ']' . PHP_EOL . 'A atualização para PIN Utilizado nãoa afetou N E N H U M registro.' . PHP_EOL . $sql . PHP_EOL . 'Resposta EPP: CODRETEPP=' . $notify_list_values['EU'] . PHP_EOL);
												}

											}
										} elseif ($value == "2") {
											$aux_codretepp = $notify_list_values['EG'];
											if (is_array($headers)) {
												//logEventsONGAME('A consulta Partner_Check retornou 2'.PHP_EOL);
											}//end if(is_array($headers))
											send_debug_info_by_email_PINCASH('Teste ID [' . $id . '] Partner_Check', 'A consulta Partner_Check retornou 2<br>', $partner_dep, $id);
											gravaLog_IntegracaoPIN('PIN [' . $pin_code . ']' . PHP_EOL . 'A consulta Partner_Check retornou 2' . PHP_EOL);

										}
									}//end else do if(empty($dominio_check))

								} elseif ($rs_oper_row['opr_use_check'] == 2) {
									$sql = "update pins set pin_status='" . $PINS_PUBLISHERS_STATUS_VALUES['U'] . "' where (pin_status= '" . $PINS_PUBLISHERS_STATUS_VALUES['V'] . "' OR pin_status= '" . $PINS_PUBLISHERS_STATUS_VALUES['L'] . "' OR pin_status= '" . $PINS_PUBLISHERS_STATUS_VALUES['P'] . "') AND pin_codinterno=" . retorna_id_pin($pin_code, $id);
									//echo $sql;
									$rs_pin_update = SQLexecuteQuery($sql);
									if (!$rs_pin_update) {
										$aux_codretepp = $notify_list_values['EU'];
									} else {
										$cmdtuples = pg_affected_rows($rs_pin_update);
										//echo $cmdtuples . " tuples are affected.<br>\n";
										if ($cmdtuples === 1) {
											//If somente para o Publisher RIOT
											if ($id == 90) {
												publisherOrderId(retorna_id_pin($pin_code, $id), $riot_order_id, 'L');
											} //end if($id == 90)
											$aux_codretepp = $notify_list_values['SU'];
										} else {
											$aux_codretepp = $notify_list_values['EU'];
										}
									}
								} else
									$aux_codretepp = $notify_list_values['PO'];
							} else
								$aux_codretepp = $notify_list_values['VD'];
						} else {
							$aux_codretepp = $notify_list_values['SD'];
						}
					}
				} elseif ($aux_status_value == $PINS_PUBLISHERS_STATUS_VALUES['U']) {
					$aux_codretepp = $notify_list_values['PU'];
				} else
					$aux_codretepp = $notify_list_values['SD'];
			} else {
				$aux_codretepp = $notify_list_values['ID'];
			}
		} else
			$aux_codretepp = $notify_list_values['ND'];
	} else
		$aux_codretepp = $notify_list_values['OL'];

	if ($aux_codretepp == '0') {
		$aux_codretepp = $notify_list_values['EG'];
		send_debug_info_by_email_PINCASH('Teste ID [' . $id . '] ERRO GERAL', 'aux_codretepp é ZERO', $partner_dep, $id);
		gravaLog_IntegracaoPIN('PIN [' . $pin_code . ']' . PHP_EOL . 'aux_codretepp é ZERO' . PHP_EOL);
	}
	log_pin($aux_codretepp, $pin_code, $id);
	if (isset($aux_codreteppTOP)) {

		/*
						  if($_SERVER["REMOTE_ADDR"] == "201.93.162.169"){
							  var_dump($aux_teste_IP);
							  var_dump($aux_codretepp);
							  exit;
						  }
						  */

		$aux_codreteppTOP = $aux_codretepp;
		$aux_pin_valueTOP = $aux_pin_value;
	} else {
		echo "CODRETEPP=" . converte_detalhe_codretepp($aux_codretepp);
		if (!is_null($aux_pin_value)) {
			$pinValueFormatted = number_format($aux_pin_value * 100, 0, '', '');
			echo ";PIN_VALUE=" . $pinValueFormatted;
		}
	}
} //end do if(strpos($teste[0]["file"],"check-redeem"))
else {
	die("Access Denied!");
}

?>