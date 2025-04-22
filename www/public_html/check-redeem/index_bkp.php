<?php
header("Content-Type: text/html; charset=ISO-8859-1",true);

if ($_SERVER['HTTPS']=="on") { //descomentar para implementar https

        set_time_limit(300);
        ini_set('max_execution_time', 300);
        ini_set('default_socket_timeout', 240);
        require_once "../../includes/main.php";
        require_once $raiz_do_projeto . "includes/gamer/main.php";
        require_once $raiz_do_projeto . "includes/functionsCheckRedeem.php";
        require_once $raiz_do_projeto . "class/classIntegracaoPin.php";
        require_once $raiz_do_projeto . "class/classIntegracaoPinCash.php";
        require_once $raiz_do_projeto . "class/classControleIP.php";
        
        gravaLog_IntegracaoPIN("IP Tentativa: ".retorna_ip_acesso().PHP_EOL.print_r($_POST,true));
        
		$dataAtual = date('Y-m-d H:i:s');
		$informacoesPOST = $_POST;
		$ipReq = $_SERVER['REMOTE_ADDR'];
		$infoAdicional = json_encode($_SERVER, JSON_UNESCAPED_UNICODE);

		if (!empty($_POST)) {
			// Se $_POST existe e não está vazio
			$informacoesReq = "Via POST: " . json_encode($_POST, JSON_UNESCAPED_UNICODE);
		} else {
			// Se $_POST está vazio ou não está definido
			
			if (!empty($_GET)) {
				$informacoesReq = "Via GET: " . json_encode($_GET, JSON_UNESCAPED_UNICODE);
			} else if (!empty($_COOKIE))
				$informacoesReq = "Via COOKIE: " . json_encode($_COOKIE, JSON_UNESCAPED_UNICODE);
			else {
				$informacoesReq = 'Sem informações de requisição POST, GET ou COOKIE';
			} 
		}


		$mensagemLog = '****#### INÍCIO ####****' . PHP_EOL . 
					   'Data e Hora: ' . $dataAtual . PHP_EOL . 
					   'Informações de REQUEST: ' . $informacoesReq . PHP_EOL . 
					   'IP de Acesso: ' . $ipReq . PHP_EOL . 
					   'Informações Adicionais do Servidor: ' . $infoAdicional . PHP_EOL . 
					   '****#### FIM ####****' . PHP_EOL . PHP_EOL;

		$fileLog = "../../log/logCheckRedeemALL.txt";
		$file = fopen($fileLog, 'a+');
		if ($file) {
			fwrite($file, $mensagemLog);
			fclose($file);
		} else {
			echo "Erro ao abrir o arquivo de log.";
		}
		
		
        //Forçando todos os parametros em minusculo
        $_POST = array_change_key_case($_POST, CASE_LOWER);
	$id 		= isset($_POST["id"])			? $_POST["id"]			: null;

	//Variavel que coloca o sistema em OFF LINE qdo FALSE
	$auxOnLineTop = true;

	//comentar e deixar soimente o POST
	//	descomentar $id 		= isset($_REQUEST["id"])		? $_REQUEST["id"]			: null;

	$params		= array('id'		=> array ('0' => $id,
											  '1' => 'I',
											  '2' => '1'
										),
						);
	$params		= sanitize_input_data_array($params,$err_cod);
	extract($params, EXTR_OVERWRITE);

	$aux_codreteppTOP = '0';
	$aux_pin_valueTOP = null;

	if ($auxOnLineTop) {
		$sql_opr = "select opr_product_type from operadoras where opr_codigo=".$id;
		$rs_oper = SQLexecuteQuery($sql_opr);
		if($rs_oper) {
			if($rs_oper_row = pg_fetch_array($rs_oper)) {
                    			switch($rs_oper_row['opr_product_type'])
                    			{
						case '1':
							include "epp_verify.php";
							break;
						case '2':
							include "epp_cash.php";
							break;
						case '3':
							include "epp_verify.php";
							if ($aux_codreteppTOP <> $notify_list_values['SV'] && $aux_codreteppTOP <> $notify_list_values['SU']&& $aux_codreteppTOP <> $notify_list_values['VD']&& $aux_codreteppTOP <> $notify_list_values['PU']&& $aux_codreteppTOP <> $notify_list_values['SD']&& $aux_codreteppTOP <> $notify_list_values['EU']) {
								include "epp_cash.php";
							}
							break;
						case '4':
							include "epp_go_cash.php";
							break;
						case '5':
							include "epp_go_cash_real_value.php";
							break;
						case '6':
							$pin_code 	= isset($_POST["pin_code"])	? $_POST["pin_code"]	: null;
                                                        $pin_code	= filter_var($pin_code, FILTER_SANITIZE_NUMBER_INT);
                                                        if(RetonaTamanhoPINEPPCARD_SINGLEPAGE($pin_code)) {
                                                            include "epp_card.php";
                                                        }
                                                        else {
                                                            include "epp_verify.php";
                                                        }
							break;
						case '7':
                                                    	require_once $raiz_do_projeto . "banco/gocash/config.inc.php";
                                                        $pin_code 	= isset($_POST["pin_code"])	? $_POST["pin_code"]	: null;
                                                        $pin_code	= filter_var($pin_code, FILTER_SANITIZE_NUMBER_INT);
                                                        if(RetonaTamanhoPINGoCASH($pin_code)) {
                                                            include "epp_go_cash.php";
                                                        }
                                                        else {
                                                            include "epp_verify.php";
                                                        }
							break;
						case '8':
                                                    	require_once $raiz_do_projeto . "banco/gocash/config.inc.php";
                                                        $pin_code 	= isset($_POST["pin_code"])	? $_POST["pin_code"]	: null;
                                                        $pin_code	= filter_var($pin_code, FILTER_SANITIZE_NUMBER_INT);
                                                        if(RetonaTamanhoPINEPPCARD_SINGLEPAGE($pin_code)) {
                                                            include "epp_card.php";
                                                        }
                                                        elseif(RetonaTamanhoPINGoCASH($pin_code)) {
                                                            include "epp_go_cash.php";
                                                        }
                                                        else {
                                                            include "epp_verify.php";
                                                        }
							break;
						default:
							$aux_codreteppTOP = $notify_list_values['PO'];
							break;
					}
			}
			else $aux_codreteppTOP = $notify_list_values['EG'];
		}
		else $aux_codreteppTOP = $notify_list_values['EG'];
	}
	else $aux_codreteppTOP = $notify_list_values['OL'];

	if ($aux_codreteppTOP == '0') {
		$aux_codreteppTOP = $notify_list_values['EG'];
	}



     if($action == '2' && (($id*1) == 124 || ($id*1) == 137)){
	
		if(converte_detalhe_codretepp($aux_codreteppTOP) == 2 || converte_detalhe_codretepp($aux_codreteppTOP) == 1 || converte_detalhe_codretepp($aux_codreteppTOP) == 5 || converte_detalhe_codretepp($aux_codreteppTOP) == 6){
			$sql_id = "select pin_codinterno from pins where pin_codigo ='$pin_code'";
			$rs_id = SQLexecuteQuery($sql_id);
			$rsiD = pg_fetch_array($rs_id);
			
			$sql_venda_user = "select * from tb_venda_games inner join tb_venda_games_modelo on vg_id= vgm_vg_id inner join tb_venda_games_modelo_pins on vgm_id = vgmp_vgm_id where vgmp_pin_codinterno =".$rsiD["pin_codinterno"];
			$rs_venda_user = SQLexecuteQuery($sql_venda_user);
			$rs_user = pg_fetch_array($rs_venda_user);
			
			if(pg_num_rows($rs_venda_user) == 0 || $rs_venda_user == false){
			
				$sql_venda_pdv = "select * from tb_dist_venda_games inner join tb_dist_venda_games_modelo on vg_id= vgm_vg_id inner join tb_dist_venda_games_modelo_pins on vgm_id = vgmp_vgm_id where vgmp_pin_codinterno =".$rsiD["pin_codinterno"];
				$rs_venda_pdv = SQLexecuteQuery($sql_venda_pdv);
				$rs_pdv = pg_fetch_array($rs_venda_pdv);
				
				if(pg_num_rows($rs_venda_pdv) > 0 ){
					echo "CODRETEPP=".converte_detalhe_codretepp($aux_codreteppTOP).";CODCHANNEL=1";
					if (!is_null($aux_pin_valueTOP)) {
						echo ";PIN_VALUE=".$aux_pin_valueTOP."00";
					}
				}
			
			}else{
			
				if($rs_user["vg_pagto_tipo"] == 2 || $rs_user["vg_pagto_tipo"] == 24){
					echo "CODRETEPP=".converte_detalhe_codretepp($aux_codreteppTOP).";CODCHANNEL=0";
					if (!is_null($aux_pin_valueTOP)) {
						echo ";PIN_VALUE=".$aux_pin_valueTOP."00";
					}
				}elseif($rs_user["vg_pagto_tipo"] == 13){
					
					$sql_venda_pincash = "select * from pins_store_pag_epp_pin where tpc_idvenda =".$rs_user["vg_id"];
					$rs_venda_pin_cash = SQLexecuteQuery($sql_venda_pincash);
					$pdv = false;
					while($row_rs_cash = pg_fetch_array($rs_venda_pin_cash)){
						if($row_rs_cash["pspep_canal"] == 'L'){
							$pdv = true;
						}
					}
					
					if($pdv == true){
						echo "CODRETEPP=".converte_detalhe_codretepp($aux_codreteppTOP).";CODCHANNEL=1";
					}else{
						echo "CODRETEPP=".converte_detalhe_codretepp($aux_codreteppTOP).";CODCHANNEL=0";
					} 
					if (!is_null($aux_pin_valueTOP)) {
						echo ";PIN_VALUE=".$aux_pin_valueTOP."00";
					} 
				}
			 
			}

        }else{
		
			echo "CODRETEPP=".converte_detalhe_codretepp($aux_codreteppTOP);
			if (!is_null($aux_pin_valueTOP)) {
				echo ";PIN_VALUE=".$aux_pin_valueTOP."00";
			}
		
		}	
		
	}else{
	
		echo "CODRETEPP=".converte_detalhe_codretepp($aux_codreteppTOP);
		if (!is_null($aux_pin_valueTOP)) {
			echo ";PIN_VALUE=".$aux_pin_valueTOP."00";
		}
	
	}
	

	/* echo "CODRETEPP=".converte_detalhe_codretepp($aux_codreteppTOP);
	if (!is_null($aux_pin_valueTOP)) {
		echo ";PIN_VALUE=".$aux_pin_valueTOP."00";
	} */

        //Fechando Conexão
        pg_close($connid);

} //	end do teste HTTPS //descomentar para implementar https
?>