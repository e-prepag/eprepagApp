<?php
if ($_SERVER['HTTPS']=="on") { //descomentar para implementar https

        require_once $raiz_do_projeto . "class/gamer/classConversionPINsEPP.php";
        
	$pin_code 	= isset($_POST["pin_code"])		? $_POST["pin_code"]	: null;
	$pin_value 	= isset($_POST["pin_value"])		? $_POST["pin_value"]	: null;
	$id 		= isset($_POST["id"])			? $_POST["id"]		: null;
	$action		= isset($_POST["action"])		? $_POST["action"]	: null;
	//Variavel que coloca o sistema em OFF LINE qdo FALSE
	$auxOnLine = true;

	//comentar e deixar soimente o POST
	//	descomentar $pin_code 	= isset($_REQUEST["pin_code"])	? $_REQUEST["pin_code"]		: null;
	//	descomentar $pin_value 	= isset($_REQUEST["pin_value"])	? $_REQUEST["pin_value"]	: null;
	//	descomentar $id 		= isset($_REQUEST["id"])		? $_REQUEST["id"]			: null;
	//	descomentar $action		= isset($_REQUEST["action"])	? $_REQUEST["action"]		: null;

	//Convertendo para não considerar CENTAVOS
	$pin_value = $pin_value/100;

	$params		= array('pin_code'	=> array ('0' => $pin_code,
											  '1' => 'S',
											  '2' => '1'
										),
						'pin_value'	=> array ('0' => $pin_value,
											  '1' => 'I',
											  '2' => '1'
										),
						'id'		=> array ('0' => $id,
											  '1' => 'I',
											  '2' => '1'
										),
						'action'	=> array ('0' => $action,
											  '1' => 'I',
											  '2' => '1'
										),
						);
	$params		= sanitize_input_data_array($params,$err_cod);
        extract($params, EXTR_OVERWRITE);

	$aux_codretepp = '0';
	$aux_pin_value = null;
        
        //Convertendo para valor NOMINAL
        $pin_value = ConversionPINs::get_ValorNominal('G', $pin_value);

	//verificar as definições e implementações do log para liberar a geração
	log_pin_cash($aux_codretepp,$pin_code,$id,serialize($params),($pin_value*100),1);

        //Programa descontinuado por conta da desativação do GoCASH
        $aux_codretepp = $notify_list_values['ND'];
        echo "CODRETEPP=".converte_detalhe_codretepp($aux_codretepp);
        die();
        
        if ($auxOnLine) {
		if (empty($pin_code) && empty($id) && empty($action)) {
			$aux_codretepp = $notify_list_values['F4'];
		}
		elseif (empty($pin_code)) {
				$aux_codretepp = $notify_list_values['FC'];
			}
			elseif (empty($pin_value)&&($action == '2')) {
					$aux_codretepp = $notify_list_values['FV'];
				}
			elseif (empty($action)) {
					$aux_codretepp = $notify_list_values['FA'];
				}
			elseif (empty($id)) {
					$aux_codretepp = $notify_list_values['FI'];
				}
				elseif (RetonaTamanhoPINGoCASH($pin_code)) {
						$aux_opr_ip = retorna_ip_integracao($id);
						//echo "aux_opr_ip [".$aux_opr_ip."]<br>";
						if($aux_opr_ip <> 0) {
							//$vetor_IPs = explode(';', $aux_opr_ip);
							$aux_teste_IP = false;
                                                        $controleIP = new ControleIP();
                                                        if ( $controleIP->isInOprRange($aux_opr_ip, retorna_ip_acesso()) ) {
                                                            $aux_teste_IP = true;
                                                            $dominio_check = retorna_dominio($id);
                                                        }
                                                        //send_debug_info_by_email_PINCASH("Teste Email DEBUG", "retorna_ip_integracao ($aux_opr_ip) <br>IP de acesso:".retorna_ip_acesso(), $partner_dep, $id);
							//echo "Retorna IP [".retorna_ip_acesso()."]<br>";
                                                        /*
						        for ($i = 0; $i < count($vetor_IPs); $i++) {
								if (trim($vetor_IPs[$i]) == retorna_ip_acesso()) {
									$aux_teste_IP = true;
									$dominio_check = retorna_dominio($id);
								}//end if 
							}
                                                        */
							//echo "aux_teste_IP [".$aux_teste_IP."]<br>";
						        if ($aux_teste_IP) {
								if ($action == '1') {
									$aux_pin_value = StatusInquiryPIN_Value($pin_code);
                                                                        if($aux_pin_value > 0) {
										$aux_codretepp = $notify_list_values['SV'];
									}
									else {
										$aux_codretepp = $notify_list_values['ND'];
									}
								}
								elseif($action == '2') {
										$aux_debug = "";
										$sql_opr = "select opr_use_check,opr_partner_check from operadoras where opr_codigo=".$id;
										$rs_oper = SQLexecuteQuery($sql_opr);
										$rs_oper_row = pg_fetch_array($rs_oper);
                                                                                //echo "opr_use_check [".$rs_oper_row['opr_use_check']."]<br>";
										if ($rs_oper_row['opr_use_check'] == 1) {
												
											if(empty($dominio_check)) {
												$aux_codretepp = $notify_list_values['PO'];
											}
											else {

												// Build a postback string
												$post_parameters = array(
																		'PIN_CODE'	=> $pin_code,
																		'PIN_VALUE'	=> ConversionPINs::get_ValorReal('G', $pin_value),
																		'ID'		=> $id
																	);
												if($partner_dep[$id]['depurar']) $aux_debug .= "<pre>".print_r($post_parameters,true)."</pre>";
											
											// Colocar LOGS

												$url = "https://".$dominio_check."/".$rs_oper_row['opr_partner_check'];
												if($partner_dep[$id]['depurar']) $aux_debug .= "https://".$dominio_check."/".$rs_oper_row['opr_partner_check']."<br>";
												$buffer = "";
												$curl_handle = curl_init();
												curl_setopt($curl_handle, CURLOPT_URL, $url);
												
												// verify the digital certificate
												curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, 0);
												//  verify digital certificate’s name
												curl_setopt($curl_handle, CURLOPT_SSL_VERIFYHOST, 0);

												curl_setopt($curl_handle, CURLOPT_USERAGENT,"Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)");
												curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
												curl_setopt($curl_handle, CURLOPT_POST, true);
												curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $post_parameters);
												// The number of seconds to wait while trying to connect. 
												// Use 0 to wait indefinitely.
												curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 60);
												// The maximum number of seconds to allow cURL functions to execute.
												curl_setopt($curl_handle, CURLOPT_TIMEOUT, 50);
												// send the request and get the response
												$buffer = curl_exec($curl_handle);
												curl_close($curl_handle);
												if($partner_dep[$id]['depurar']) $aux_debug .= "BUFFER: [".$buffer."]<br>";
												list($name, $value) = explode('=', $buffer);
												if($partner_dep[$id]['depurar']) $aux_debug .= "Name= ".$name." Value=".$value."<br>";
												if ($value == "1") {
													$aux_pin_value = StatusInquiryPIN_Value($pin_code);
													if($aux_pin_value == $pin_value) {
														$a_pins_gocash[$pin_code] = $aux_pin_value;
														$venda_id = obterIdVendaValidoGoCASH();
														if(!SaveRedeemPinTransaction($a_pins_gocash,$venda_id,$id)) {
															 $aux_codretepp = $notify_list_values['EU']; 
														}
														else {
															$aux_codretepp = $notify_list_values['SU']; 
														}
													}//end if($aux_pin_value == $pin_value)
													else $aux_codretepp = $notify_list_values['VD'];
												}
												elseif ($value == "2"){
													$aux_codretepp = $notify_list_values['EG'];
												}
												send_debug_info_by_email_PINCASH("Teste Email DEBUG", $aux_debug, $partner_dep, $id);
											}//end else do if(empty($dominio_check))
										}
										elseif ($rs_oper_row['opr_use_check'] == 2) {
												$aux_pin_value = StatusInquiryPIN_Value($pin_code);
												if($aux_pin_value == $pin_value) {
													$a_pins_gocash[$pin_code] = $aux_pin_value;
													$venda_id = obterIdVendaValidoGoCASH();
													if(!SaveRedeemPinTransaction($a_pins_gocash,$venda_id,$id)) {
														 $aux_codretepp = $notify_list_values['EU']; 
													}
													else {
														$aux_codretepp = $notify_list_values['SU']; 
													}
												}//end if($aux_pin_value == $pin_value)
												else $aux_codretepp = $notify_list_values['VD'];
										}
										else $aux_codretepp = $notify_list_values['PO'];
									} 
							}
							else $aux_codretepp = $notify_list_values['ID'];
						}
						else $aux_codretepp = $notify_list_values['PO'];
					}
					else $aux_codretepp = $notify_list_values['ND'];
	}
	else $aux_codretepp = $notify_list_values['OL'];

	if ($aux_codretepp == '0') {
		$aux_codretepp = $notify_list_values['EG'];
	}
	//verificar as definições e implementações do log para liberar a geração
	log_pin_cash($aux_codretepp,$pin_code,$id,serialize($params),($pin_value*100),1);

//echo "[".$aux_pin_value."]<br>";
       //Convertendo para valor REAL
        if (!empty($aux_pin_value)) {
                $aux_pin_value = ConversionPINs::get_ValorReal('G', $aux_pin_value);
        }

        if (isset($aux_codreteppTOP)) {
		$aux_codreteppTOP = $aux_codretepp;
		$aux_pin_valueTOP = $aux_pin_value;
	} else {
		echo "CODRETEPP=".converte_detalhe_codretepp($aux_codretepp);
		if (!is_null($aux_pin_value)) {
                        echo ";PIN_VALUE=".($aux_pin_value*100);
		}
	}
}	// end do teste HTTPS //descomentar para implementar https
?>