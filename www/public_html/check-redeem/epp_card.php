<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

//Verificando se a execução foi acionada a apartir CHECK-REDEEM
$teste = debug_backtrace();
if(strpos($teste[0]["file"],"check-redeem/index.php")) { 

        require_once $raiz_do_projeto . "class/classIntegracaoPinCard.php";
        
        //Setando o tempo de início
        $time_start_stats 	= getmicrotimePINCard();
//die("CARD"); 
        $operacao_array	= VetorDistribuidorasCard();
	$pin_code 	= isset($_POST["pin_code"])		? $_POST["pin_code"]		: null;
	$pin_value 	= isset($_POST["pin_value"])		? $_POST["pin_value"]		: null;
	$id 		= isset($_POST["id"])			? $_POST["id"]			: null;
	$action		= isset($_POST["action"])		? $_POST["action"]		: null;
	$cpf		= isset($_POST["cpf"])			? $_POST["cpf"]			: null;
	$data_nascimento= isset($_POST["data_nascimento"])	? $_POST["data_nascimento"]	: null;
	$riot_order_id	= isset($_POST["riot_order_id"])	? $_POST["riot_order_id"]	: null;
	$checkout_id	= isset($_POST["checkout_id"])		? $_POST["checkout_id"]		: null;
        
        //Variavel que coloca o sistema em OFF LINE qdo FALSE
	$auxOnLine = true;

	//Convertendo para não considerar CENTAVOS
	$pin_value = $pin_value/100;

	$params		= array('pin_code'	=> array ('0' => $pin_code,
                                                                            '1' => 'S',
                                                                            '2' => '1'
                                                                  ),
                                  'pin_value'	=> array ('0' => $pin_value,
                                                                            '1' => 'F',
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
                                  'cpf'		=> array ('0' => $cpf,
                                                                            '1' => 'S',
                                                                            '2' => '1'
                                                                  ),
                                  'data_nascimento'=> array ('0' => $data_nascimento,
                                                                            '1' => 'S',
                                                                            '2' => '1'
                                                                  ),
                                  'riot_order_id'=> array ('0' => $riot_order_id,
                                                                            '1' => 'S',
                                                                            '2' => '1'
                                                                  ),
                                  'checkout_id'=> array ('0' => $checkout_id,
                                                                            '1' => 'S',
                                                                            '2' => '1'
                                                                  ),
                                  );
	$params		= sanitize_input_data_array($params,$err_cod);
        //echo "<pre>".print_r($params,true)."</pre>";
	extract($params, EXTR_OVERWRITE);
        
	$aux_codretepp = '0';
	$aux_pin_value = null;

	if ($auxOnLine) {
 
            //Variavel contendo o Código do Distribuidor
            $cod_distrib = retornaID_Distibuidora($pin_code);
            
            //Arquivo contendo o Include Dinâmico
            $tmp_arq = $raiz_do_projeto . "/partners_cards/".$operacao_array[$cod_distrib]."/config.inc.".$operacao_array[$cod_distrib].".php";
                    
            //Testando se o PIN pertence a algum distribuidor integrado
            if(array_key_exists($cod_distrib, $operacao_array) && file_exists($tmp_arq)) {
                
                    //incluindo a classe dinamicamente de acordo com o PIN informado
                    require_once ($tmp_arq);
                
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
				elseif (retorna_id_pin_card($pin_code, $id) <> 0) {
                                                
                                                //Buscando a lista de IPs habilitados do Publisher
                                                $aux_opr_ip = retorna_ip_integracao($id);
                                                //Verificando se a lista está vazia
                                                if($aux_opr_ip <> 0) {
                                                        $aux_teste_IP = false;
                                                        $controleIP = new ControleIP();
                                                        if ( $controleIP->isInOprRange($aux_opr_ip, retorna_ip_acesso()) ) {
                                                            $aux_teste_IP = true;
                                                            $dominio_check = retorna_dominio($id);
                                                        }
							/*
							$vetor_IPs = explode(';', $aux_opr_ip);
                                                        $aux_teste_IP = false;
                                                        for ($i = 0; $i < count($vetor_IPs); $i++) {
                                                                if (trim($vetor_IPs[$i]) == retorna_ip_acesso()) { 
                                                                        $aux_teste_IP = true;
                                                                        $dominio_check = retorna_dominio($id);
                                                                }//end if 
                                                        }
                                                        */
                                                        //Verificando se o IP da requisição está cadastrado no registro do Publisher
                                                        if ($aux_teste_IP) {
                                                                if(retorna_status_card($pin_code,$id) == $PINS_STORE_STATUS_VALUES['A']){
                                                                    
                                                                        if ($action == '1') {
                                                                            
                                                                                // Verificar junto ao distribuidor o status 
                                                                                //Instanciando o objeto dinamicamente de acordo com o PIN informado
                                                                                $teste = new $operacao_array[$cod_distrib];
                                                                                $params_distributor = array(
                                                                                                'pin'		=> $pin_code,
                                                                                                );
                                                                                $teste->Req_EfetuaConsulta($params_distributor,$resposta, INQUIRY);
                                                                                // 
                                                                                //  PARA TIRAR CONSULTA DIRETA NA INCOMM: Comentar a linha acima e restornar na função que encaminha para www a resposta [$resposta = nomedafuncao($params_distributor, ACAO)]
                                                                                //  PARA RETORNAR A CONSULTA DIRETA NA INCOMM: Comentar a linha abaixo de descomentar a linha acima
                                                                                //
                                                                                //$resposta = $teste->object_to_array(VerificaIncomm($params_distributor, "INQUIRY"));
                                                                                //echo "<pre>".print_r($resposta,TRUE)."<pre>";
                                                                                $aux_codretepp = $teste->RetornaStatusConsultaInquiry($resposta);
                                                                                //echo "AKI<br>[$aux_codretepp]<br>[".$notify_list_values['SV']."]<br>--".$resposta['TransferredValueTxnResp']['RespCode']."<br>";
                                                                                if($aux_codretepp == $notify_list_values['SV']) {
                                                                                        //Capturando o valor retornado pelo Distribuidor
                                                                                        $auxValorDistribuidor = $teste->RetornaValorConsulta($resposta);
                                                                                        if($auxValorDistribuidor != 0) {
                                                                                                //Capturando o valor na Base de Dados da E-Prepag
                                                                                                $auxValorEprepag = retorna_pin_valor_card($pin_code,$id);
                                                                                                if($auxValorDistribuidor == $auxValorEprepag) {
                                                                                                    $aux_pin_value = $auxValorEprepag;
                                                                                                }//end if($auxValorDistribuidor == $auxValorEprepag
                                                                                                else {
                                                                                                    $aux_codretepp = $notify_list_values['VD'];
                                                                                                }
                                                                                        }//end if($auxValorDistribuidor != 0)
                                                                                        else {
                                                                                            $aux_codretepp = $notify_list_values['VD'];
                                                                                        }
                                                                                }//end if($aux_codretepp == $notify_list_values['SV'])
                                                                        } //end if ($action == '1')
                                                                        
                                                                        elseif($action == '2') {
                                                                                        // Verificando se está bloqueado e bloqueando se não
                                                                                        if(flag_pin_card_test($pin_code,$id)) {
                                                                                            $aux_codretepp = $notify_list_values['BK'];
                                                                                        }
                                                                                        else {
                                                                                            //Bloqueado com Sucesso

                                                                                            if (verifica_valor_pin_card($pin_code,$pin_value,$id)) {
                                                                                                            $sql_opr = "select opr_use_check,opr_partner_check,opr_internacional_alicota, opr_need_cpf_lh from operadoras where opr_codigo=".$id;
                                                                                                            $rs_oper = SQLexecuteQuery($sql_opr);
                                                                                                            $rs_oper_row = pg_fetch_array($rs_oper);
                                                                                                            
                                                                                                            if (empty($cpf)&&($rs_oper_row['opr_need_cpf_lh'] != 0)) {
                                                                                                                            $aux_codretepp = $notify_list_values['CF'];
                                                                                                                    }
                                                                                                            elseif (empty($data_nascimento)&&($rs_oper_row['opr_need_cpf_lh'] != 0)) {
                                                                                                                            $aux_codretepp = $notify_list_values['FD'];
                                                                                                                    }
                                                                                                            elseif ($rs_oper_row['opr_use_check'] == 1) {
                                                                                                                    if(empty($dominio_check)) {
                                                                                                                            $aux_codretepp = $notify_list_values['PO'];
                                                                                                                    } //end if(empty($dominio_check))
                                                                                                                    else { 

                                                                                                                            // Build a postback string
                                                                                                                            $post_parameters = array(
                                                                                                                                            'PIN_CODE'	=> $pin_code,
                                                                                                                                            'PIN_VALUE'	=> $pin_value*100,
                                                                                                                                            'ID'	=> $id,
                                                                                                                                            'ACTION'	=> $action
                                                                                                                                         );
                                                                                                                            /*
                                                                                                                             * 13 => OnGame
                                                                                                                             * 73 => Publisher Teste
                                                                                                                             * 88 => RIOT Teste removido por conta de testes da RIOT
                                                                                                                             * 90 => RIOT Live
                                                                                                                             * 102=> Ongame Teste
                                                                                                                             */
                                                                                                                            $ids_https = array(13,88,90,73,102);
                                                                                                                            if (in_array($id, $ids_https)) {
                                                                                                                                    $url = "https://";
                                                                                                                            }
                                                                                                                            else { 
                                                                                                                                $url = "http://";
                                                                                                                            }
                                                                                            
                                                                                                                            if(($id*1) == 90 || ($id*1) == 88) { 
                                                                                                                                $headers[] = "Content-Type: application/x-www-form-urlencoded";
                                                                                                                                $post_parameters['CHECKOUT_ID'] = $checkout_id;
                                                                                                                                $post_parameters = http_build_query($post_parameters);
                                                                                                                            }
                                                                                                                            $varAuxIP = retorna_ip_acesso();
                                                                                                                            if(($id*1) == 13 && ($varAuxIP == "201.77.235.18" || $varAuxIP == "201.77.235.30")) {
                                                                                                                                $dominio_check = "loja.ongame.net";
                                                                                                                                $rs_oper_row['opr_partner_check'] = "payment/eprepag/pingback/";
                                                                                                                                $headers[] = "Host: loja.ongame.net";
                                                                                                                            }
                                                                                                                            $url .= $dominio_check."/".$rs_oper_row['opr_partner_check'];
                                                                                                                            //$url = "http://".$dominio_check."/".$rs_oper_row['opr_partner_check'];
                                                                                                                            $buffer = "";
                                                                                                                            $curl_handle = curl_init();
                                                                                                                            curl_setopt($curl_handle, CURLOPT_URL, $url);
                                                                                                                            //Teste solução headers para caso TLSv1.2
                                                                                                                            if(is_array($headers)) {
                                                                                                                                curl_setopt($curl_handle, CURLOPT_HTTPHEADER, $headers);
                                                                                                                                curl_setopt($curl_handle, CURLOPT_FAILONERROR, true);
                                                                                                                                curl_setopt($curl_handle, CURLOPT_VERBOSE, true);
                                                                                                                                //$errorFileLog = fopen($raiz_do_projeto . "log/log_ONGAME_DEBUG.log", "a+");
                                                                                                                                //curl_setopt($curl_handle, CURLOPT_STDERR, $errorFileLog);
                                                                                                                                curl_setopt($curl_handle, CURLOPT_HEADER, 0);
                                                                                                                            }

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
                                                                                                                            //echo $buffer."<br>";
                                                                                                                            list($name, $value) = explode('=', $buffer);
                                                                                                                            //echo "Name= ".$name." Value=".$value."<br>";
                                                                                                                            if ($value == "1") {

                                                                                                                                //                                                                                                                                       //
                                                                                                                                //  Verificar junto ao distribuidor o status 
                                                                                                                                //  
                                                                                                                                //Instanciando o objeto dinamicamente de acordo com o PIN informado
                                                                                                                                $teste = new $operacao_array[$cod_distrib];
                                                                                                                                $params_distributor = array(
                                                                                                                                                'pin'		=> $pin_code,
                                                                                                                                                );
                                                                                                                                $teste->Req_EfetuaConsulta($params_distributor,$resposta, INQUIRY);
                                                                                                                                // 
                                                                                                                                //  PARA TIRAR CONSULTA DIRETA NA INCOMM: Comentar a linha acima e restornar na função que encaminha para www a resposta [$resposta = nomedafuncao($params_distributor, ACAO)]
                                                                                                                                //  PARA RETORNAR A CONSULTA DIRETA NA INCOMM: Comentar a linha abaixo de descomentar a linha acima
                                                                                                                                //
                                                                                                                                //$resposta = $teste->object_to_array(VerificaIncomm($params_distributor, "INQUIRY"));
                                                                                                                                $aux_codretepp = $teste->RetornaStatusConsultaInquiry($resposta);
                                                                                                                                //echo "AKI<br>[$aux_codretepp]<br>";
                                                                                                                                if($aux_codretepp == $notify_list_values['SV']) {
                                                                                                                                        //Capturando o valor retornado pelo Distribuidor
                                                                                                                                        $auxValorDistribuidor = $teste->RetornaValorConsulta($resposta);
                                                                                                                                        if($auxValorDistribuidor != 0) {
                                                                                                                                                //Capturando o valor na Base de Dados da E-Prepag
                                                                                                                                                $auxValorEprepag = retorna_pin_valor_card($pin_code,$id);
                                                                                                                                                if($auxValorDistribuidor == $auxValorEprepag) {

                                                                                                                                                    // SE PIN DISPONIVEL PARA UTILIZAÇÂO consulta CPF
                                                                                                                                                    // Verificando se o publisher exige CPF
                                                                                                                                                    if($rs_oper_row['opr_need_cpf_lh'] != 0) {
                                                                                                                                                            //Atribuindo resultado da consulta em variável temporária
                                                                                                                                                            $auxResultadoConsultaCPF = verificaCPFnaReceitaFederal($cpf,$pin_code,$id,$data_nascimento);
                                                                                                                                                    } //end if($rs_oper_row['opr_need_cpf_lh'] != 0)
                                                                                                                                                    else $auxResultadoConsultaCPF = true;
                                                                                                                                                    if($auxResultadoConsultaCPF === true) {
                                                                                                                                                            //
                                                                                                                                                            // Marcando como utilizado
                                                                                                                                                            //
                                                                                                                                                            //Instanciando o objeto dinamicamente de acordo com o PIN informado
                                                                                                                                                            $teste = new $operacao_array[$cod_distrib];
                                                                                                                                                            $params_distributor = array(
                                                                                                                                                                            'pin'		=> $pin_code,
                                                                                                                                                                            );
                                                                                                                                                            $teste->Req_EfetuaConsulta($params_distributor,$resposta, REDEEM);
                                                                                                                                                            // 
                                                                                                                                                            //  PARA TIRAR CONSULTA DIRETA NA INCOMM: Comentar a linha acima e restornar na função que encaminha para www a resposta [$resposta = nomedafuncao($params_distributor, ACAO)]
                                                                                                                                                            //  PARA RETORNAR A CONSULTA DIRETA NA INCOMM: Comentar a linha abaixo de descomentar a linha acima
                                                                                                                                                            //
                                                                                                                                                            //$resposta = $teste->object_to_array(VerificaIncomm($params_distributor, "REDEEM"));
                                                                                                                                                            $aux_codretepp = $teste->RetornaStatusConsultaRedeem($resposta);
                                                                                                                                                            //echo "AKI<br>[$aux_codretepp]<br>";
                                                                                                                                                            if($aux_codretepp == $notify_list_values['SU']) {
                                                                                                                                                                    //Marcando como utilizado em nosso Banco de Dados 
                                                                                                                                                                    $sql = "update pins_card set pin_status=".$PINS_STORE_STATUS_VALUES['U']." where pin_status= ".$PINS_STORE_STATUS_VALUES['A']."  AND pin_codinterno=".retorna_id_pin_card($pin_code,$id)." and opr_codigo = ".addslashes($id).";";
                                                                                                                                                                    //echo $sql;
                                                                                                                                                                    $rs_pin_update = SQLexecuteQuery($sql);
                                                                                                                                                                    if(!$rs_pin_update) {
                                                                                                                                                                             $aux_codretepp = $notify_list_values['EU']; 
                                                                                                                                                                    }
                                                                                                                                                                    else {
                                                                                                                                                                            $cmdtuples = pg_affected_rows($rs_pin_update);
                                                                                                                                                                            //echo $cmdtuples . " tuples are affected.<br>\n";
                                                                                                                                                                            if($cmdtuples===1) {
                                                                                                                                                                                    //If somente para o Publisher RIOT
                                                                                                                                                                                    if($id == 90) {
                                                                                                                                                                                        publisherOrderId(retorna_id_pin_card($pin_code,addslashes($id)),$riot_order_id,'C');
                                                                                                                                                                                    } //end if($id == 90)
                                                                                                                                                                                    $aux_codretepp = $notify_list_values['SU']; 
                                                                                                                                                                            } else {
                                                                                                                                                                                    $aux_codretepp = $notify_list_values['EU']; 
                                                                                                                                                                            }
                                                                                                                                                                    } //end else do if(!$rs_pin_update)
                                                                                                                                                            }//end if($aux_codretepp == $notify_list_values['SU'])

                                                                                                                                                    } //end if($auxResultadoConsultaCPF === true)
                                                                                                                                                    //Verificando se o sistema está offline
                                                                                                                                                    elseif($auxResultadoConsultaCPF == 2) {
                                                                                                                                                        $aux_codretepp = $notify_list_values['OF'];
                                                                                                                                                    }
                                                                                                                                                    //Verificando se a data de nascimento é inconsistente
                                                                                                                                                    elseif($auxResultadoConsultaCPF == 12) {
                                                                                                                                                        $aux_codretepp = $notify_list_values['DE'];
                                                                                                                                                    }
                                                                                                                                                    //Testando se ultrapassou o limite de utilização do mesmo CPF
                                                                                                                                                    elseif($auxResultadoConsultaCPF == 171) {
                                                                                                                                                        $aux_codretepp = $notify_list_values['TD'];
                                                                                                                                                    }
                                                                                                                                                    //Testando idade minima
                                                                                                                                                    elseif($auxResultadoConsultaCPF == 112) {
                                                                                                                                                        $aux_codretepp = $notify_list_values['IM'];
                                                                                                                                                    }
                                                                                                                                                    else {
                                                                                                                                                        $aux_codretepp = $notify_list_values['PF'];
                                                                                                                                                    }
                                                                                                                                                
                                                                                                                                                }//end if($auxValorDistribuidor == $auxValorEprepag
                                                                                                                                                else {
                                                                                                                                                    $aux_codretepp = $notify_list_values['VD'];
                                                                                                                                                }
                                                                                                                                        }//end if($auxValorDistribuidor != 0)
                                                                                                                                        else {
                                                                                                                                            $aux_codretepp = $notify_list_values['VD'];
                                                                                                                                        }
                                                                                                                                }//end if($aux_codretepp == $notify_list_values['SV'])


                                                                                                                            } //end if ($value == "1") 
                                                                                                                            elseif ($value == "2"){
                                                                                                                                    $aux_codretepp = $notify_list_values['EG'];
                                                                                                                            }
                                                                                                                    }//end else do if(empty($dominio_check))

                                                                                                            } //end if ($rs_oper_row['opr_use_check'] == 1) 
                                                                                                            elseif ($rs_oper_row['opr_use_check'] == 2) {
                                                                                                                            //
                                                                                                                            //  Verificar junto ao distribuidor o status 
                                                                                                                            //  
                                                                                                                            //Instanciando o objeto dinamicamente de acordo com o PIN informado
                                                                                                                            $teste = new $operacao_array[$cod_distrib];
                                                                                                                            $params_distributor = array(
                                                                                                                                            'pin'		=> $pin_code,
                                                                                                                                            );
                                                                                                                            $teste->Req_EfetuaConsulta($params_distributor,$resposta, INQUIRY);
                                                                                                                            // 
                                                                                                                            //  PARA TIRAR CONSULTA DIRETA NA INCOMM: Comentar a linha acima e restornar na função que encaminha para www a resposta [$resposta = nomedafuncao($params_distributor, ACAO)]
                                                                                                                            //  PARA RETORNAR A CONSULTA DIRETA NA INCOMM: Comentar a linha abaixo de descomentar a linha acima
                                                                                                                            //
                                                                                                                            //$resposta = $teste->object_to_array(VerificaIncomm($params_distributor, "INQUIRY"));
                                                                                                                            $aux_codretepp = $teste->RetornaStatusConsultaInquiry($resposta);
                                                                                                                            //echo "AKI<br>[$aux_codretepp]<br>";
                                                                                                                            if($aux_codretepp == $notify_list_values['SV']) {
                                                                                                                                    //Capturando o valor retornado pelo Distribuidor
                                                                                                                                    $auxValorDistribuidor = $teste->RetornaValorConsulta($resposta);
                                                                                                                                    if($auxValorDistribuidor != 0) {
                                                                                                                                            //Capturando o valor na Base de Dados da E-Prepag
                                                                                                                                            $auxValorEprepag = retorna_pin_valor_card($pin_code,$id);
                                                                                                                                            if($auxValorDistribuidor == $auxValorEprepag) {
                                                                                                                                                    if($rs_oper_row['opr_need_cpf_lh'] != 0) {
                                                                                                                                                            //Atribuindo resultado da consulta em variável temporária
                                                                                                                                                            $auxResultadoConsultaCPF = verificaCPFnaReceitaFederal($cpf,$pin_code,$id,$data_nascimento);
                                                                                                                                                    }//end if($rs_oper_row['opr_need_cpf_lh'] != 0)
                                                                                                                                                    else $auxResultadoConsultaCPF = true;
                                                                                                                                                    if($auxResultadoConsultaCPF === true) {
                                                                                                                                                            //
                                                                                                                                                            // Marcando como utilizado
                                                                                                                                                            //
                                                                                                                                                            //Instanciando o objeto dinamicamente de acordo com o PIN informado
                                                                                                                                                            $teste = new $operacao_array[$cod_distrib];
                                                                                                                                                            $params_distributor = array(
                                                                                                                                                                            'pin'		=> $pin_code,
                                                                                                                                                                            );
                                                                                                                                                            $teste->Req_EfetuaConsulta($params_distributor,$resposta, REDEEM);
                                                                                                                                                            // 
                                                                                                                                                            //  PARA TIRAR CONSULTA DIRETA NA INCOMM: Comentar a linha acima e restornar na função que encaminha para www a resposta [$resposta = nomedafuncao($params_distributor, ACAO)]
                                                                                                                                                            //  PARA RETORNAR A CONSULTA DIRETA NA INCOMM: Comentar a linha abaixo de descomentar a linha acima
                                                                                                                                                            //
                                                                                                                                                            //$resposta = $teste->object_to_array(VerificaIncomm($params_distributor, "REDEEM"));
                                                                                                                                                            $aux_codretepp = $teste->RetornaStatusConsultaRedeem($resposta);
                                                                                                                                                            //echo "AKI<br>[$aux_codretepp]<br>";
                                                                                                                                                            if($aux_codretepp == $notify_list_values['SU']) {
                                                                                                                                                                    //Marcando como utilizado em nosso Banco de Dados
                                                                                                                                                                    $sql = "update pins_card set pin_status=".$PINS_STORE_STATUS_VALUES['U']." where pin_status= ".$PINS_STORE_STATUS_VALUES['A']."  AND pin_codinterno=".retorna_id_pin_card($pin_code,addslashes($id))." and opr_codigo = ".addslashes($id).";";
                                                                                                                                                                    //echo $sql;
                                                                                                                                                                    $rs_pin_update = SQLexecuteQuery($sql);
                                                                                                                                                                    if(!$rs_pin_update) {
                                                                                                                                                                             $aux_codretepp = $notify_list_values['EU']; 
                                                                                                                                                                    }
                                                                                                                                                                    else {
                                                                                                                                                                            $cmdtuples = pg_affected_rows($rs_pin_update);
                                                                                                                                                                            //echo $cmdtuples . " tuples are affected.<br>\n";
                                                                                                                                                                            if($cmdtuples===1) {
                                                                                                                                                                                    //If somente para o Publisher RIOT
                                                                                                                                                                                    if($id == 90) {
                                                                                                                                                                                        publisherOrderId(retorna_id_pin_card($pin_code,addslashes($id)),$riot_order_id,'C');
                                                                                                                                                                                    } //end if($id == 90)
                                                                                                                                                                                     $aux_codretepp = $notify_list_values['SU']; 
                                                                                                                                                                            } else {
                                                                                                                                                                                    $aux_codretepp = $notify_list_values['EU']; 
                                                                                                                                                                            }
                                                                                                                                                                    }

                                                                                                                                                            }//end if($aux_codretepp == $notify_list_values['SU'])
                                                                                                                                                    } //end if($auxResultadoConsultaCPF === true)
                                                                                                                                                    //Verificando se o sistema está offline
                                                                                                                                                    elseif($auxResultadoConsultaCPF == 2) {
                                                                                                                                                        $aux_codretepp = $notify_list_values['OF'];
                                                                                                                                                    }
                                                                                                                                                    //Verificando se a data de nascimento é inconsistente
                                                                                                                                                    elseif($auxResultadoConsultaCPF == 12) {
                                                                                                                                                        $aux_codretepp = $notify_list_values['DE'];
                                                                                                                                                    }
                                                                                                                                                    //Testando se ultrapassou o limite de utilização do mesmo CPF
                                                                                                                                                    elseif($auxResultadoConsultaCPF == 171) {
                                                                                                                                                        $aux_codretepp = $notify_list_values['TD'];
                                                                                                                                                    }
                                                                                                                                                    //Testando idade minima
                                                                                                                                                    elseif($auxResultadoConsultaCPF == 112) {
                                                                                                                                                        $aux_codretepp = $notify_list_values['IM'];
                                                                                                                                                    }
                                                                                                                                                    else {
                                                                                                                                                        $aux_codretepp = $notify_list_values['PF'];
                                                                                                                                                    }
                                                                                                                                                
                                                                                                                                            }//end if($auxValorDistribuidor == $auxValorEprepag
                                                                                                                                            else {
                                                                                                                                                $aux_codretepp = $notify_list_values['VD'];
                                                                                                                                            }
                                                                                                                                    }//end if($auxValorDistribuidor != 0)
                                                                                                                                    else {
                                                                                                                                        $aux_codretepp = $notify_list_values['VD'];
                                                                                                                                    }
                                                                                                                            }//end if($aux_codretepp == $notify_list_values['SV'])

                                                                                                            }//end elseif ($rs_oper_row['opr_use_check'] == 2)
                                                                                                            else $aux_codretepp = $notify_list_values['PO'];
                                                                                            }//end if (verifica_valor_pin_card($pin_code,$pin_value,$id))
                                                                                            else $aux_codretepp = $notify_list_values['VD'];

                                                                                            //Desbloquendo PIN
                                                                                            flag_pin_card_unblock($pin_code,$id);

                                                                                        }//end else do if(flag_pin_card_test($pin_code,$id))
                                                                                }//end elseif($action == '2')
                                                                }//end if(retorna_status_card($pin_code,$id) == $PINS_STORE_STATUS_VALUES['A'])
                                                                elseif(retorna_status_card($pin_code,$id) == $PINS_STORE_STATUS_VALUES['U']) {
                                                                                $aux_codretepp = $notify_list_values['PU'];
                                                                }
                                                                else $aux_codretepp = $notify_list_values['SD'];
                                                        }//end if ($aux_teste_IP)
                                                        else $aux_codretepp = $notify_list_values['ID'];
                                                }//end if($aux_opr_ip <> 0)
                                                else $aux_codretepp = $notify_list_values['PO'];

					} //end elseif (retorna_id_pin_card($pin_code, $id) <> 0)
					else $aux_codretepp = $notify_list_values['ND'];
                                        
            } //end if(!array_key_exists($cod_distrib, $operacao_array))
            else $aux_codretepp = $notify_list_values['ND'];
                                        
	}//end if ($auxOnLine) 
	else $aux_codretepp = $notify_list_values['OL'];

	if ($aux_codretepp == '0') {
		$aux_codretepp = $notify_list_values['EG'];
	}
	//verificar as definições e implementações do log para liberar a geração
	log_pin_card($aux_codretepp,$pin_code,$id,serialize($params),($pin_value*100),0);
	if (isset($aux_codreteppTOP)) {
		$aux_codreteppTOP = $aux_codretepp;
		$aux_pin_valueTOP = $aux_pin_value;
	} else {
		echo "CODRETEPP=".converte_detalhe_codretepp($aux_codretepp);
		if (!is_null($aux_pin_value)) {
			echo ";PIN_VALUE=".$aux_pin_value."00";
		}
	}
        gravaLog_EPPCARD("Tempo total de execução: ".number_format(getmicrotimePINCard() - $time_start_stats, 2, '.', '.')." segundos");
}	// end do if debug_backtrace
else {
    die("Access Denied!");
}
?>