<?php
class classB2C {
		
	private $soapClient;
	private $serviceCode;	
	private $service_online;

	public function __construct() {
		$this->set_service_status(false);
		try{
			$soapClient = @new SoapClient(B2C_WSDL_URL, array('location'     => B2C_SERVICE_URL,
															  'uri'          => B2C_SERVICE_URL,
															  'cache_wsdl'   => WSDL_CACHE_NONE,
															  'soap_version' => SOAP_1_1,//SOAP_1_2,
															  //'encoding'     => 'UTF-8',
															  'encoding'	 => 'ISO-8859-1',
															  'trace'        => 1,
															  'exceptions'   => 1));
			$this->set_service_status(true);
			$this->logEvents("Service enable!\n", B2C_MSG_ERROR_LOG);
		} catch (SoapFault $e) {
			$this->logEvents("Caught exception 1 (".$e->faultcode."): ". $e->getMessage()."\n", B2C_MSG_ERROR_LOG);
		}
	}//end function __construct()

	private function set_service_status($status) {
		$this->service_online	=	$status;
	}//end function set_service_status

	public function get_service_status() {
		return $this->service_online;
	}//end function get_service_status


	public function callService($typeOfService = '', $requestParams = array()) {
						
		// Armazena na classe os dados do serviço informado 
		$b2cRequestRecord = $this->getRequestObject($typeOfService, $requestParams);

		try{
			$this->soapClient = @new SoapClient(B2C_WSDL_URL, array('location'		=> B2C_SERVICE_URL,
																	'uri'			=> B2C_SERVICE_URL,
																	'cache_wsdl'	=> WSDL_CACHE_NONE,
																	'soap_version'	=> SOAP_1_1,//SOAP_1_2,
																	//'encoding'		=> 'UTF-8',
																	'encoding'		=> 'ISO-8859-1',
																	'trace'			=> 1,
																	'exceptions'	=> 1,
																	)
												);
		} catch (SoapFault $e) {
			$this->logEvents( "Caught exception 2A (".utf8_decode($e->faultcode)."): ". utf8_decode($e->getMessage())."\n", B2C_MSG_ERROR_LOG, 0);
		}

		
		if($this->soapClient) {

			try {
				$this->logEvents("Antes do metodo __sopCall:".print_r(array($b2cRequestRecord),true), B2C_MSG_ERROR_LOG, 0);
				$resultWS = $this->soapClient->__soapCall($typeOfService, array($b2cRequestRecord));
$this->logEvents("<hr>SUCESSO\n<pre>".htmlentities(str_replace("><", ">\n<", $this->getTransactionMessages()))."</pre>\n<hr>", B2C_MSG_ERROR_LOG, 0);
				if ($resultWS instanceof SoapFault) {								
					$this->logEvents($this->getErrorMessages($resultWS), B2C_MSG_ERROR_LOG, 0);	
					$this->logEventsBD($this->getErrorMessages($resultWS), B2C_MSG_ERROR_LOG, 0);		
				} else {
										
					$b2cResponseRecord = $this->getResponseObject($typeOfService, $resultWS);			
				
					return $b2cResponseRecord;
				}

			} catch (SoapFault $e) {
$this->logEvents("<hr>ERRO\n<pre>".htmlentities(str_replace("><", ">\n<", $this->getTransactionMessages()))."</pre>\n<hr>", B2C_MSG_ERROR_LOG, 0);
				$this->logEvents( "Caught exception 2B (".utf8_decode($e->faultcode)."): ". utf8_decode($e->getMessage())."\n", B2C_MSG_ERROR_LOG, 0);
			}
			
		} else {
			$this->logEvents( "Erro Interno 2C: soapClient não definido\n", B2C_MSG_ERROR_LOG, 0);
		}
	} //end function callService
		
	
	public function saveTransactionBeforeSoap($params) {

		$vb2c_vg_id					= $params['vg_id'];
		$vb2c_coServico				= $params['coServico'];
		$vb2c_precoServico			= $params['precoServico'];
		$vb2c_prazoVigencia			= $params['prazoVigencia'] ? $params['prazoVigencia'] : 0;
		$vb2c_comissao_total		= $params['comissao_total'];
		$vb2c_comissao_para_repasse	= $params['comissao_para_repasse'];
		
		$sql = "INSERT INTO tb_vendas_b2c (vb2c_vg_id, vb2c_coServico, vb2c_precoServico, vb2c_prazoVigencia, vb2c_comissao_total, vb2c_comissao_para_repasse)
								VALUES (";
		$sql .= SQLaddFields($vb2c_vg_id, ""). ",";
		$sql .= SQLaddFields($vb2c_coServico, "s"). ",";
		$sql .= SQLaddFields($vb2c_precoServico, ""). ",";
		$sql .= SQLaddFields($vb2c_prazoVigencia, "s"). ",";
		$sql .= SQLaddFields($vb2c_comissao_total, ""). ",";
		$sql .= SQLaddFields($vb2c_comissao_para_repasse, ""). "";
		$sql .= ");";

		gravaLog_B2C("Em saveSoapTransaction(): \n".$sql."\n");

		$rs   = SQLexecuteQuery($sql);
		if($rs) {
			$ret = true;
		}
		else {
			gravaLog_B2C("Em saveSoapTransaction(): ERROR ao executar o SQL\n");
			$ret = false;
		}
		
		return $ret;
	} //end function saveTransactionBeforeSoap
	
	//Método para captura do XML do SOAP
	public function getTransactionMessages() {

		if($this->soapClient) {
			$requestMsg        = htmlspecialchars_decode($this->soapClient->__getLastRequest());
			$requestHeaderMsg  = htmlspecialchars_decode($this->soapClient->__getLastRequestHeaders());
			$responseMsg       = htmlspecialchars_decode($this->soapClient->__getLastResponse());
			$responseHeaderMsg = htmlspecialchars_decode($this->soapClient->__getLastResponseHeaders());
			
			$msg  = "";
			$msg .= "--------------------------\n";
			$msg .= "Request :\n\n".$requestMsg."\n";
			$msg .= "--------------------------\n";
			$msg .= "RequestHeaders:\n\n".$requestHeaderMsg;
			$msg .= "--------------------------\n";
			$msg .= "Response:\n\n".$responseMsg."\n\n";
			$msg .= "--------------------------\n";
			$msg .= "ResponseHeaders:\n\n".$responseHeaderMsg."\n\n";
		} else {
			$msg = "Erro Interno A: soapClient não definido";
		}
		return $msg;		
	}//end function getTransactionMessages

	
	//Método para exibição da messagem de erro
	public function getErrorMessages($resultWS, $isSoapFault = true) {
		
		if ($isSoapFault) {
			$msg .= "Message : ".$resultWS->getMessage()."\n";
			$msg .= "--------------------------\n";
			$msg .= "TraceString: ".$resultWS->getTraceAsString()."\n";
			$msg .= "--------------------------\n";
			$msg .= "Code: ".$resultWS->getCode()."\n";
			$msg .= "--------------------------\n";
			$msg .= "File: ".$resultWS->getFile()."\n";
			$msg .= "--------------------------\n";
			$msg .= "Line: ".$resultWS->getLine()."\n";
			$msg .= "--------------------------\n";
			$msg .= "FaultCode: ".$resultWS->faultcode."\n";
			$msg .= "--------------------------\n";
			$msg .= "Detail: ".$resultWS->detail."\n\n\n";
			$msg .= $this->getTransactionMessages();
		} else {
			$msg .= $this->getTransactionMessages();				
		}
		
		return $msg;
	} //end function getErrorMessages

	// General methods request
	private function getRequestObject($typeOfService = '', $requestParams = array()) {	
		
		if ($typeOfService == B2C_ACTION_SEARCH_RANGE_PIN) {
			$serialCheck = new buscarFaixasPin();
			$serialCheckRequestObj = $serialCheck->getRequestData($requestParams);
			return $serialCheckRequestObj;
		}
		else if ($typeOfService == B2C_ACTION_SEARCH_RANGE_PIN_FOR_DATE) {
			$serialCheck = new buscarFaixasPinPorData();
			$serialCheckRequestObj = $serialCheck->getRequestData($requestParams);
			return $serialCheckRequestObj;
		}
		else if ($typeOfService == B2C_ACTION_CALCULATE_SERVICE_CODE) {
			$serialCheck = new calcularServicoCodigo();
			$serialCheckRequestObj = $serialCheck->getRequestData($requestParams);
			return $serialCheckRequestObj;
		}
		else if ($typeOfService == B2C_ACTION_CALCULATE_PIN_SERVICE) {
			$serialCheck = new calcularServicoPin();
			$serialCheckRequestObj = $serialCheck->getRequestData($requestParams);
			return $serialCheckRequestObj;
		}
		else if ($typeOfService == B2C_ACTION_CANCEL_SALE) {
			$serialCheck = new cancelarVenda();
			$serialCheckRequestObj = $serialCheck->getRequestData($requestParams);
			return $serialCheckRequestObj;
		}
		else if ($typeOfService == B2C_ACTION_COMPLETE_SALE_DATA) {
			$serialCheck = new complementarDadosVenda();
			$serialCheckRequestObj = $serialCheck->getRequestData($requestParams);
			return $serialCheckRequestObj;
		}
		else if ($typeOfService == B2C_ACTION_CONSULT_STATUS_PIN) {
			$serialCheck = new consultarStatusPin();
			$serialCheckRequestObj = $serialCheck->getRequestData($requestParams);
			return $serialCheckRequestObj;
		}
		else if ($typeOfService == B2C_ACTION_REGISTER_SALE) {
			$serialCheck = new registrarVenda();
			$serialCheckRequestObj = $serialCheck->getRequestData($requestParams);
			//$this->logEvents( "Dados Carregados na Estrutura: ". print_r($serialCheckRequestObj,true)."\n", B2C_MSG_ERROR_LOG, 0);
			return $serialCheckRequestObj;
		}
		else if ($typeOfService == B2C_ACTION_RESERVE_PIN) {
			$serialCheck = new reservarPin();
			$serialCheckRequestObj = $serialCheck->getRequestData($requestParams);
			return $serialCheckRequestObj;
		}
		
	}//end 	function getRequestObject

	// General method Response
	private function getResponseObject($typeOfService = '', $soapResponseData) {			

		if ($typeOfService == B2C_ACTION_SEARCH_RANGE_PIN) {
			$serialCheck = new buscarFaixasPin();
			$serialCheckResponseObj = $serialCheck->getResponseData($soapResponseData);
			return $serialCheckResponseObj;
		}
		else if ($typeOfService == B2C_ACTION_SEARCH_RANGE_PIN_FOR_DATE) {
			$serialCheck = new buscarFaixasPinPorData();
			$serialCheckResponseObj = $serialCheck->getResponseData($soapResponseData);
			return $serialCheckResponseObj;
		}
		else if ($typeOfService == B2C_ACTION_CALCULATE_SERVICE_CODE) {
			$serialCheck = new calcularServicoCodigo();
			$serialCheckResponseObj = $serialCheck->getResponseData($soapResponseData);
			return $serialCheckResponseObj;
		}
		else if ($typeOfService == B2C_ACTION_CALCULATE_PIN_SERVICE) {
			$serialCheck = new calcularServicoPin();
			$serialCheckResponseObj = $serialCheck->getResponseData($soapResponseData);
			return $serialCheckResponseObj;
		}
		else if ($typeOfService == B2C_ACTION_CANCEL_SALE) {
			$serialCheck = new cancelarVenda();
			$serialCheckResponseObj = $serialCheck->getResponseData($soapResponseData);
			return $serialCheckResponseObj;
		}
		else if ($typeOfService == B2C_ACTION_COMPLETE_SALE_DATA) {
			$serialCheck = new complementarDadosVenda();
			$serialCheckResponseObj = $serialCheck->getResponseData($soapResponseData);
			return $serialCheckResponseObj;
		}
		else if ($typeOfService == B2C_ACTION_CONSULT_STATUS_PIN) {
			$serialCheck = new consultarStatusPin();
			$serialCheckResponseObj = $serialCheck->getResponseData($soapResponseData);
			return $serialCheckResponseObj;
		}
		else if ($typeOfService == B2C_ACTION_REGISTER_SALE) {
			$serialCheck = new registrarVenda();
			$serialCheckResponseObj = $serialCheck->getResponseData($soapResponseData);
			//$this->logEvents( "Dados Carregados na Estrutura: ". print_r($serialCheckResponseObj,true)."\n", B2C_MSG_ERROR_LOG, 0);
			return $serialCheckResponseObj;
		}
		else if ($typeOfService == B2C_ACTION_RESERVE_PIN) {
			$serialCheck = new reservarPin();
			$serialCheckResponseObj = $serialCheck->getResponseData($soapResponseData);
			return $serialCheckResponseObj;
		}
		
	}//end function getResponseObject

	function get_select_Produtos($id_selected = null) {
		$sret = "";
		if($this->get_service_status()) {
				
			$aValores = $GLOBALS['B2C_PRODUCT'];
			

		}//end if($dados->get_service_status())
		else {
			$aValores = array();
		}
		$sret .= "<br><select id='product' name='product' class='form-xl form-control' onchange='javascript:carga_valor_Produtos(this.value);'>\n"; 
		$sret .= "<option value=''>Selecione o Produto</option>\n";
		foreach($aValores as $key => $val) {
			$sret .= "<option value='".$key."'";
			if($id_selected == $key) {
				$sret .= " selected";
			}
			$sret .= ">".$val['name']."</option>\n";
		}
		$sret .= "</select> \n";
		$sret .= "<br>\n";

		return $sret;
	}

	function get_select_Valores_Produtos($id) {
		$sret = "";
		if (($id*1) > 0){
			$aValores = null;
			if($this->get_service_status()) {
					
				$aValoresAux = $GLOBALS['B2C_PRODUCT'];
				foreach($aValoresAux as $key => $val) {
					if ($key == $id) {
						$aValores = $val['price'];
					}//end if ($key == $id)
				}//end foreach
				

			}//end if($dados->get_service_status())
			
			$sret .= "<b>R$ ".$aValores."</b><br><br>\n";

		} else {
			$sret .= "Não foi selecionado o Produto<br>\n";
		}

		return $sret;
	}//end function get_select_Valores($id) 

	function get_ValorFixoProduto($id) {
		$sret = "";
		if (($id*1) > 0){
			$aValores = null;
			if($this->get_service_status()) {
					
				$aValoresAux = $GLOBALS['B2C_PRODUCT'];
				foreach($aValoresAux as $key => $val) {
					if ($key == $id) {
						$aValores = $val['price'];
					}//end if ($key == $id)
				}//end foreach
				

			}//end if($dados->get_service_status())
			
			$sret .= $aValores;

		} else {
			$sret .= "Não foi selecionado o Produto<br>\n";
		}

		return $sret;
	}//end function get_ValorFixoProduto($id) 

	function get_Vigencia($id) {
		$sret = "";
		if (($id*1) > 0){
			$aValores = null;
			if($this->get_service_status()) {
					
				$aValoresAux = $GLOBALS['B2C_PRODUCT'];
				foreach($aValoresAux as $key => $val) {
					if ($key == $id) {
						$aValores = $val['validity'];
					}//end if ($key == $id)
				}//end foreach
				

			}//end if($dados->get_service_status())
			
			$sret .= $aValores;

		} else {
			$sret .= "Não foi selecionado o Produto<br>\n";
		}
		
		if($sret == '') {
			$sret = null;
		}

		return $sret;
	}//end function get_Vigencia($id) 

	function get_ComissaoTotal($id) {
		$sret = "";
		if (($id*1) > 0){
			$aValores = null;
			if($this->get_service_status()) {
					
				$aValoresAux = $GLOBALS['B2C_PRODUCT'];
				foreach($aValoresAux as $key => $val) {
					if ($key == $id) {
						$aValores = $val['comiss'];
					}//end if ($key == $id)
				}//end foreach
				

			}//end if($dados->get_service_status())
			
			$sret .= $aValores;

		} else {
			$sret .= "Não foi selecionado o Produto<br>\n";
		}
		
		return $sret;
	}//end function get_ComissaoTotal($id) 

	function get_ComissaoRepasse($id) {
		$sret = "";
		if (($id*1) > 0){
			$aValores = null;
			if($this->get_service_status()) {
					
				$aValoresAux = $GLOBALS['B2C_PRODUCT'];
				foreach($aValoresAux as $key => $val) {
					if ($key == $id) {
						$aValores = $val['comiss_lan'];
					}//end if ($key == $id)
				}//end foreach
				

			}//end if($dados->get_service_status())
			
			$sret .= $aValores;

		} else {
			$sret .= "Não foi selecionado o Produto<br>\n";
		}
		
		return $sret;
	}//end function get_ComissaoRepasse($id) 

	function valida_Produto($id) {
		$aValoresAux = $GLOBALS['B2C_PRODUCT'];
		foreach($aValoresAux as $key => $val){
			if($key == $id)  {
				return true;
			}
		}
		return false;
	}//end function valida_operadora($id) 

	// Carrega dados de Consulta com id_venda vg_id para Produtos
	function get_new_idvenda_Produto() {
		$b_unique = false;
		$iloop = 1;
		do{
			$vg_id = rand(1, 1e7-1);

			$sql = "select * from tb_vendas_b2c where vb2c_vg_id = $vg_id order by \"vb2c_dataVenda\" desc limit 1";
			$rs = SQLexecuteQuery($sql);
			if(!$rs || pg_num_rows($rs) == 0) {
				$b_unique = true;
				break;
			} else {
				$b_unique = false;
			}
		} while((!$b_unique) && (($iloop++)<10));
		return $vg_id;
	}

	//Salva o Pedido Produto
	function salvaPedidoProduto($params) {

		$vg_id = $this->get_new_idvenda_Produto();
		$GLOBALS['_SESSION']['vendaB2C'] = $vg_id;
		if(isset($GLOBALS['_SESSION']['dist_usuarioGames_ser'])) {
			$usuarioGames = unserialize($_SESSION['dist_usuarioGames_ser']);
			$ug_id = $usuarioGames->getId();
		}
		else {
			$ug_id = 0; 
		}
		$valor				= $this->get_ValorFixoProduto($params['product']);
		$vigencia			= $this->get_Vigencia($params['product']);
		$comissao_total		= $this->get_ComissaoTotal($params['product']);
		$comissao_repasse	= $this->get_ComissaoRepasse($params['product']);
		$sql = "INSERT INTO tb_vendas_b2c (vb2c_vg_id, \"vb2c_coServico\", \"vb2c_dataVenda\", \"vb2c_precoServico\", \"vb2c_prazoVigencia\", vb2c_comissao_total, vb2c_comissao_para_repasse, vb2c_ug_id, vb2c_ug_id_lan) ";
		$sql .= "VALUES ($vg_id, '".$params['product']."', NOW(), $valor, '$vigencia', $comissao_total, $comissao_repasse, ".$params['ug_id'].", $ug_id);";
//echo "SQL: ".$sql."<br>";
		$rs = SQLexecuteQuery($sql);
		if(!$rs) {
			echo "Erro ao Salvar Pedido (256).";
			return false;
		} 
		else {
			$sql = "select * from tb_vendas_b2c_ug_to_dist_ug where vb2cud_ug_id_gamer=".$params['ug_id']." and vb2cud_ug_id_lan=$ug_id;";
//echo "SQL: ".$sql."<br>";
			$rs_busca = SQLexecuteQuery($sql);
			if($rs_busca && pg_num_rows($rs_busca) == 0) {
				$sql = "INSERT INTO tb_vendas_b2c_ug_to_dist_ug (vb2cud_ug_id_gamer, vb2cud_ug_id_lan) VALUES (".$params['ug_id'].", $ug_id)";
//echo "SQL: ".$sql."<br>";
				$rs_ids = SQLexecuteQuery($sql);
				if(!$rs_ids) {
					echo "Erro ao Salvar Pedido (257).";
					return false;
				} 
				else {
					return true;
				}
			}//end if($rs && pg_num_rows($rs) == 0)
			else {
				if($rs_busca) return true;
				else return false;
			}
		}//end else do if(!$rs)
	}//end function salvaPedido($params)

	//Salva o Atualiza Produto
	function atualizaProduto($params) {

		$vg_id				= $params['vg_id'];
		$dataCadastroVenda	= str_replace('T',' ',$params['dataCadastroVenda']);
		$pin				= $params['pin'];
		$statusPin			= $params['statusPin'];
		$status				= $params['status'];
																								
		$sql = "UPDATE tb_vendas_b2c SET \"vb2c_dataCadastroVenda\" = '$dataCadastroVenda', vb2c_pin = '$pin', \"vb2c_statusPin\" = '$statusPin', vb2c_status = '$status' ";
		$sql .= "WHERE vb2c_vg_id=$vg_id;";
//echo "SQL: ".$sql."<br>";
		$rs = SQLexecuteQuery($sql);
		if(!$rs) {
			echo "Erro ao Atualizar Pedido (258).";
			return false;
		} 
		else {
			return true;
		}//end else do if(!$rs)
	}//end function atualizaProduto($params)

	function get_dados_da_Lan($ug_id, &$params) {
		$params = array();
		$sql  = "select ug_ativo, ug_tipo_cadastro, ug_perfil_limite, ug_perfil_saldo, ug_risco_classif from dist_usuarios_games where ug_id = " . $ug_id;
//echo "$sql\n";
		$rs_lan = SQLexecuteQuery($sql);
		if(!$rs_lan || pg_num_rows($rs_lan) == 0) {
		} else {
			$rs_lan_row = pg_fetch_array($rs_lan);
			$params['ug_ativo']			= $rs_lan_row['ug_ativo'];
			$params['ug_risco_classif']	= $rs_lan_row['ug_risco_classif'];
			$params['ug_perfil_limite']	= $rs_lan_row['ug_perfil_limite'];
			$params['ug_perfil_saldo']	= $rs_lan_row['ug_perfil_saldo'];
		}
	}//end function get_dados_da_Lan($ug_id, &$params) 

	function Req_EfetuaCompra($vg_id, &$lista_respostaNOK) {
		$sret = false;
		if (($vg_id*1) > 0){
			if($this->get_service_status()) {
				$sql = "select to_char(\"vb2c_dataVenda\",'YYYY-MM-DD HH24:MI:SS') as \"vb2c_dataVenda_aux\",* from tb_vendas_b2c where vb2c_vg_id = $vg_id";
				$rs_b2c = SQLexecuteQuery($sql);
				if(!$rs_b2c || pg_num_rows($rs_b2c) == 0) {
				} else {
					//carregando dados
					$rs_b2c_row = pg_fetch_array($rs_b2c);
			
					//inicio do bloco para a classe reservarPin
					$params = array(
									'cnpj'			=> B2C_CNPJ_EPP,
									'chave'			=> B2C_CLIENT_ID,
									'coServico'		=> $rs_b2c_row['vb2c_coServico'],
									);
					$responsePIN = $this->callService(B2C_ACTION_RESERVE_PIN, $params);
					echo "PIN Reservado<pre>".print_r($responsePIN,true)."</pre>\n";
					//final do bloco para a classe reservarPin

					//inicio do bloco para a classe consultarStatusPin
					$params = array(
									'cnpj'			=> B2C_CNPJ_EPP,
									'chave'			=> B2C_CLIENT_ID,
									'pin'			=> $responsePIN["pin"],
									);
					$responsePINstatus = $this->callService(B2C_ACTION_CONSULT_STATUS_PIN, $params); 

					echo "Status do PIN Reservado<pre>".print_r($responsePINstatus,true)."</pre>\n";
					//final do bloco para a classe consultarStatusPin

					//inicio do bloco para a classe registrarVenda
					$dataCadastroVenda = str_replace(' ','T',date("Y-m-d H:i:s"));

					if($responsePINstatus['vendaStatus']['status'] == B2C_PIN_STATUS_DISPONIVEL) {

						//carregando dados do GAMER
						$rs_gamer_row  = $this->get_dados_gamer($rs_b2c_row['vb2c_ug_id']);
						if(is_null($rs_gamer_row)) {
							echo "Problema na captura dos dados do Gamer!\n";
							return false;
						}

						$params = array(
										'cnpj'		=> B2C_CNPJ_EPP,
										'chave'		=> B2C_CLIENT_ID,
										'venda'		=> array( 
															'dataCadastroVenda'				=> $dataCadastroVenda,
															'cnpjLoja'						=> B2C_CNPJ_EPP,											//obrigatório
															'numeroNotaFiscal'				=> $vg_id,		 											//obrigatório
															'siglaMoedaVenda'				=> B2C_CURRENCY_BRL,										//obrigatório
															'dataVenda'						=> str_replace(' ','T',$rs_b2c_row['vb2c_dataVenda_aux']),	//obrigatório
															'servicoVenda'					=> array(
																									'codigo'			=> $rs_b2c_row['vb2c_coServico'],				
																									'pin'				=> $responsePINstatus['vendaStatus']['pin'],	//obrigatório			
																									'statusPin'			=> B2C_PIN_STATUS_FATURADO,						//obrigatório
																									'precoServico'		=> $rs_b2c_row['vb2c_precoServico'],			//obrigatório	
																									),
															'cliente'						=> array(
																									//'tipoPessoa'			=> 'PF',
																									//'cpfCnpj'				=> $rs_gamer_row['ug_cpf'],
																									//'nome'					=> $rs_gamer_row['ug_nome'],
																									//'telefoneCelular'		=> $rs_gamer_row['ug_cel_ddd'].$rs_gamer_row['ug_cel'],
																									//'telefoneResidencial'	=> $rs_gamer_row['ug_tel_ddd'].$rs_gamer_row['ug_tel'],
																									'email'					=> $rs_gamer_row['ug_email'],
																									/*
																									'endereco'				=> array(
																																	//'logradouro'	=> 'Av. Sem Fim',
																																	'logradouro'	=> $rs_gamer_row['ug_endereco'],
																																	'numero'		=> $rs_gamer_row['ug_numero'],
																																	'bairro'		=> $rs_gamer_row['ug_bairro'],
																																	//'cidade'		=> 'São Paulo',
																																	'cidade'		=> $rs_gamer_row['ug_cidade'],
																																	'cep'			=> $rs_gamer_row['ug_cep'],
																																	'complemento'	=> $rs_gamer_row['ug_complemento'],
																																	'uf'			=> $rs_gamer_row['ug_estado'],
																																	),
																																	*/
																									),
															),		
										); 
						//registra venda
						$responseRegistroVenda = $this->callService(B2C_ACTION_REGISTER_SALE, $params); 
						echo "Resposta do Registro da Venda<pre>".print_r($responseRegistroVenda,true)."</pre>\n";
						
						//testando o retorno do registro de venda
						if($responseRegistroVenda['critica']['codigo'] == '1') {
							$params = array(
											'vg_id'				=> $vg_id,
											'dataCadastroVenda'	=> $dataCadastroVenda,
											'pin'				=> $responsePINstatus['vendaStatus']['pin'],
											'statusPin'			=> B2C_PIN_STATUS_FATURADO,
											'status'			=> 1,
											);
							if($this->atualizaProduto($params)) {
								$sret = true;	
							}
						}//end if($responseRegistroVenda['critica']['codigo'] == '1')
						//testando o retorno do registro de venda
						//regra de retorno válido somente para Antivirus Kaspersky
						else if($responseRegistroVenda['critica']['codigo'] == '2' && ($rs_b2c_row['vb2c_coServico'] == B2C_PRODUCT_SERVICE_ANTIVIRUS || $rs_b2c_row['vb2c_coServico'] == B2C_PRODUCT_SERVICE_INTERNETSECURITY)) {
							$params = array(
											'vg_id'				=> $vg_id,
											'dataCadastroVenda'	=> $dataCadastroVenda,
											'pin'				=> $responsePINstatus['vendaStatus']['pin'],
											'statusPin'			=> B2C_PIN_STATUS_FATURADO,
											'status'			=> 1,
											);
							if($this->atualizaProduto($params)) {
								$sret = true;	
							}
						}//end if($responseRegistroVenda['critica']['codigo'] == '2')
						else {
							/*
							$params = array(
											'vg_id'				=> $vg_id,
											'dataCadastroVenda'	=> $dataCadastroVenda,
											'pin'				=> $responsePINstatus['vendaStatus']['pin'],
											'statusPin'			=> $responsePINstatus['vendaStatus']['status'],
											'status'			=> 'N',
											);
							$this->atualizaProduto($params);
							*/
							$lista_respostaNOK[$vg_id]['PIN']	= $responsePINstatus['vendaStatus']['pin'];
							$lista_respostaNOK[$vg_id]['STATUS']= $responsePINstatus['vendaStatus']['status'];
							$lista_respostaNOK[$vg_id]['DATA']	= str_replace('T',' ',$dataCadastroVenda);
						}//end else do if($responseRegistroVenda['critica']['codigo'] == '1')


					} //end if($responsePINstatus['vendaStatus']['status'] == B2C_PIN_STATUS_DISPONIVEL)
					//final do bloco para a classe registrarVenda
					else {
						/*
						$params = array(
										'vg_id'				=> $vg_id,
										'dataCadastroVenda'	=> '',
										'pin'				=> $responsePINstatus['vendaStatus']['pin'],
										'statusPin'			=> $responsePINstatus['vendaStatus']['status'],
										'status'			=> 'N',
										);
						$this->atualizaProduto($params);
						*/
						$lista_respostaNOK[$vg_id]['PIN']	= $responsePIN["pin"];
						$lista_respostaNOK[$vg_id]['STATUS']= $responsePINstatus['vendaStatus']['status'];
						$lista_respostaNOK[$vg_id]['DATA']	= str_replace('T',' ',$dataCadastroVenda);
					}//end else do if($responsePINstatus['vendaStatus']['status'] == B2C_PIN_STATUS_DISPONIVEL)

				}//end else do if(!$rs_b2c || pg_num_rows($rs_b2c) == 0)
			}//end if($dados->get_service_status())
		}//end if (($vg_id*1) > 0)	
		return $sret;
	}//end function Req_EfetuaCompra($vg_id) 

	function get_status_pedido_produto($vg_id,&$recibo) {

		if(!$vg_id) {
			return -1;
		}
		$sql = "select * from tb_vendas_b2c where vb2c_vg_id = $vg_id order by \"vb2c_dataVenda\" desc limit 1";
		$rs = SQLexecuteQuery($sql);
		if(!$rs || pg_num_rows($rs) == 0) {
			echo "Nenhum produto encontrado.\n";
			return -2;
		} else {
			$rs_row = pg_fetch_array($rs);
			$status = $rs_row['vb2c_status'];
			if($status=="1") {
				// Pedido processado
				$recibo = $rs_row['vb2c_pin'];
				return "1";
			} elseif($status=="N") {
				// Pedido recusado
				return "N";
			} elseif($status=="0") {
				// Pedido pendente de procesamento
				return "0";
			} else {
				// Status desconhecido
				return $status;
			}
		}
	}//end function get_status_pedido_produto($vg_id)

	function get_dados_pedido_concilado($vg_id) {

		if(!$vg_id) {
			return null;
		}
		$sql = "select * from tb_vendas_b2c where vb2c_vg_id = $vg_id and vb2c_status = '1' order by \"vb2c_dataVenda\" desc limit 1";
		$rs = SQLexecuteQuery($sql);
		if(!$rs || pg_num_rows($rs) == 0) {
			echo "Nenhum produto encontrado.\n";
			return null;
		} else {
			$rs_row = pg_fetch_array($rs);
			return $rs_row;
			
		}
	}//end function get_dados_pedido_concilado($vg_id) 

	function get_dados_gamer($ug_id) {

		if(!$ug_id) {
			return null;
		}
		$sql = "select * from usuarios_games where ug_id = $ug_id ";
		$rs = SQLexecuteQuery($sql);
		if(!$rs || pg_num_rows($rs) == 0) {
			echo "Nenhum produto encontrado.\n";
			return null;
		} else {
			$rs_row = pg_fetch_array($rs);
			return $rs_row;
			
		}
	}//end function get_dados_gamer($ug_id) 

	function envia_email_produto($vg_id, $mensagem = null) {

		//buscando os dados do pedido
		$dados_pedido = $this->get_dados_pedido_concilado($vg_id);
		if(is_null($dados_pedido)) {
			return -1;
		}

		//buscando os dados do gamer
		$dados_gamer = $this->get_dados_gamer($dados_pedido['vb2c_ug_id']);
		if(is_null($dados_gamer)) {
			return -1;
		}

		$aux_lista_prods = "
			<table cellspacing='0' cellpadding='0' width='100%' style='font: normal 14px arial, sans-serif;'>
				<tr>
					<td><font face='arial' color='#165293'>Produto</font></td>
					<td width='15'><font face='arial' color='#165293'>:</font></td>
					<td><font face='arial' color='#165293'><b>". $GLOBALS['B2C_PRODUCT'][$dados_pedido['vb2c_coServico']]['name'] ."</b></font></td>
				</tr>
				<tr>
					<td><font face='arial' color='#165293'>Valor</font></td>
					<td width='15'><font face='arial' color='#165293'>:</font></td>
					<td><font face='arial' color='#165293'>R$ ". number_format($GLOBALS['B2C_PRODUCT'][$dados_pedido['vb2c_coServico']]['price'], 2, ',', '.') ."</font></td>
				</tr>
				<tr>
					<td><font face='arial' color='#165293' size='4'><b>PIN</b></font></td>
					<td width='15'><font face='arial' color='#165293'>:</font></td>
					<td><font face='arial' color='#165293' size='4'><b>". $dados_pedido['vb2c_pin'] ."</b></font></td>
				</tr>
			</table>";
		$objEnvioEmailAutomatico = new EnvioEmailAutomatico(TIPO_USUARIO_GAMER,'CompraB2C');		
		$objEnvioEmailAutomatico->setListaProduto($aux_lista_prods);
		$objEnvioEmailAutomatico->setUgID($dados_pedido['vb2c_ug_id']);
		$objEnvioEmailAutomatico->setProduct($GLOBALS['B2C_PRODUCT'][$dados_pedido['vb2c_coServico']]['name']);
		$objEnvioEmailAutomatico->setPromocoes("<img src='".$GLOBALS['B2C_PRODUCT'][$dados_pedido['vb2c_coServico']]['image']."' align='left' border='0'></img>");
		$objEnvioEmailAutomatico->setInstrucoesUso($GLOBALS['B2C_PRODUCT'][$dados_pedido['vb2c_coServico']]['instrucoes']);
		echo $objEnvioEmailAutomatico->MontaEmailEspecifico();

		if(empty($mensagem)) {
			echo " = Enviado Email (".date("Y-m-d H:i:s").") para ".$dados_gamer['ug_email']." (id da Lan: ".$dados_pedido['vb2c_ug_id_lan'].") (id do Gamer: ".$dados_pedido['vb2c_ug_id'].")\n";
		}//end	if(empty($mensagem))

	}//end function envia_email_produto($params)

	private function logEvents($msg, $tipoLog = 'ERROR_LOG') {
			
		if($tipoLog == B2C_MSG_ERROR_LOG) 
			$fileLog = LOG_FILE_B2C_WS_ERRORS;		
		else if($tipoLog == B2C_MSG_TRANSACTION_LOG) 
			$fileLog = LOG_FILE_B2C_WS_TRANSACTIONS;
		
		$log  = "=================================================================================================\n";
		$log .= "DATA -> ".date("d/m/Y - H:i:s")."\n";
		$log .= "SERVICE STATUS  -> ".$this->get_service_status()."\n";
		$log .= "---------------------------------\n";
		$log .= htmlspecialchars_decode($msg);			
						
		$fp = fopen($fileLog, 'a+');
		fwrite($fp, $log);
		fclose($fp);		
	}//end function logEvents


} //end class classB2C

//************************************************************************************************************************************************************************************
//*****************************************  POR FAVOR, NÃO APAGUE O TRECHO ABAIXO ***********************************
//*************************************************************************************************************************************************************************************
/*
//Inicio do trecho de TESTES
$teste = new classB2C();


$params = array(
				'cnpj'			=> B2C_CNPJ_EPP,
				'chave'			=> B2C_CLIENT_ID,
				'pin'			=> '020050010000030151',
				);
$responsePINstatus = $teste->callService(B2C_ACTION_CONSULT_STATUS_PIN, $params); 

echo "Status do PIN Reservado<pre>".print_r($responsePINstatus,true)."</pre>\n";


if($responsePINstatus['vendaStatus']['status'] != 'HABILITADO') { // colocar condição && E no IF para naun aceitar entrar quando recebr um erro como resposta
//inicio do bloco para teste da classe cancelarVenda
$params = array(
				'cnpj'				=> B2C_CNPJ_EPP,
				'chave'				=> B2C_CLIENT_ID,
				'pin'				=> '020050010000030151',
				'dataCancelamento'	=> str_replace(' ','T',date("Y-m-d H:i:s")),
				'valorEstornado'	=> 44.90,
				'motivo'			=> 'Teste Homologação',
				);
$responseCancelamento = $teste->callService(B2C_ACTION_CANCEL_SALE, $params); 

echo "Venda cancelada<pre>".print_r($responseCancelamento,true)."</pre>\n";
}

//inicio do bloco para teste da classe cancelarVenda
$params = array(
				'cnpj'				=> B2C_CNPJ_EPP,
				'chave'				=> B2C_CLIENT_ID,
				'pin'				=> '004030060000000608',
				'dataCancelamento'	=> str_replace(' ','T',date("Y-m-d H:i:s")),
				'valorEstornado'	=> 29.90,
				'motivo'			=> 'Teste Homologação',
				);
$responseCancelamento = $teste->callService(B2C_ACTION_CANCEL_SALE, $params); 

echo "Venda cancelada<pre>".print_r($responseCancelamento,true)."</pre>\n";
//final do bloco para teste da classe cancelarVenda

//inicio do bloco para teste da classe reservarPin
$params = array(
				'cnpj'			=> B2C_CNPJ_EPP,
				'chave'			=> B2C_CLIENT_ID,
				//'coServico'		=> B2C_PRODUCT_SERVICE_ANTIVIRUS,
				//'coServico'		=> B2C_PRODUCT_SERVICE_CURSOSONLINE,
				//'coServico'		=> B2C_PRODUCT_SERVICE_INTERNETSECURITY,
				//'coServico'		=> B2C_PRODUCT_SERVICE_UFABACKUP,
				'coServico'		=> B2C_PRODUCT_SERVICE_FINANCIALPLAN,
				);
$responsePIN = $teste->callService(B2C_ACTION_RESERVE_PIN, $params); 

echo "PIN Reservado<pre>".print_r($responsePIN,true)."</pre>\n";
//final do bloco para teste da classe reservarPin

//inicio do bloco para teste da classe consultarStatusPin
$params = array(
				'cnpj'			=> B2C_CNPJ_EPP,
				'chave'			=> B2C_CLIENT_ID,
				'pin'			=> $responsePIN["pin"],
				);
$responsePINstatus = $teste->callService(B2C_ACTION_CONSULT_STATUS_PIN, $params); 

echo "Status do PIN Reservado<pre>".print_r($responsePINstatus,true)."</pre>\n";
//final do bloco para teste da classe consultarStatusPin

//inicio do bloco para teste da classe buscarFaixasPinPorData
$params = array(
				'cnpj'			=> B2C_CNPJ_EPP,
				'chave'			=> B2C_CLIENT_ID,
				'dataGeracao'	=> '2013-03-21T16:18:05',
				);
//$teste->callService(B2C_ACTION_SEARCH_RANGE_PIN_FOR_DATE, $params); 
//final do bloco para teste da classe buscarFaixasPinPorData

//inicio do bloco para teste da classe buscarFaixasPin
$params = array(
				'cnpj'		=> B2C_CNPJ_EPP,
				'chave'		=> B2C_CLIENT_ID,
				);
//$teste->callService(B2C_ACTION_SEARCH_RANGE_PIN, $params); 
//final do bloco para teste da classe buscarFaixasPin

//inicio do bloco para teste da classe registrarVenda
if($responsePINstatus['vendaStatus']['status'] == B2C_PIN_STATUS_DISPONIVEL) {
	$params = array(
					'cnpj'		=> B2C_CNPJ_EPP,
					'chave'		=> B2C_CLIENT_ID,
					'venda'		=> array( 
										'dataCadastroVenda'				=> str_replace(' ','T',date("Y-m-d H:i:s")),
										//'dataCadastroCancelamento'		=> str_replace(' ','T',date("Y-m-d H:i:s")),
										//'cnpjVarejo'					=> '12345678901234',
										'cnpjLoja'						=> B2C_CNPJ_EPP,							//obrigatório
										//'codigoLoja'					=> '123465',
										//'codigoGerente'					=> '1245',
										//'nomeGerente'					=> 'Fulano',
										//'codigoVendedor'				=> '45687',
										//'nomeVendedor'					=> 'Beltrano',
										'numeroNotaFiscal'				=> '00000001',		 							//obrigatório
										'siglaMoedaVenda'				=> B2C_CURRENCY_BRL,							//obrigatório
										'dataVenda'						=>	str_replace(' ','T',date("Y-m-d H:i:s")),	//obrigatório
										//'iforma'						=> 'A',
										//'valorParcelaFinanciamento'	=> 100.00,
										//'numeroParcelasFinanciamento'	=> 1,
										//'observacoes'					=> 'Teste de criação de Venda',
										'servicoVenda'					=> array(
																				'codigo'			=> B2C_PRODUCT_SERVICE_FINANCIALPLAN,				
																				'pin'				=> $responsePINstatus['vendaStatus']['pin'],						//obrigatório			
																				'statusPin'			=> B2C_PIN_STATUS_FATURADO,											//obrigatório
																				//'precoCusto'		=> NULL,			
																				
																				//'precoServico'		=> $GLOBALS['B2C_PRODUCT'][B2C_PRODUCT_SERVICE_ANTIVIRUS]['price'],		//obrigatório	
																				//'precoServico'		=> $GLOBALS['B2C_PRODUCT'][B2C_PRODUCT_SERVICE_CURSOSONLINE]['price'],	//obrigatório	
																				//'precoServico'		=> $GLOBALS['B2C_PRODUCT'][B2C_PRODUCT_SERVICE_INTERNETSECURITY]['price'],	//obrigatório	
																				//'precoServico'		=> $GLOBALS['B2C_PRODUCT'][B2C_PRODUCT_SERVICE_UFABACKUP]['price'],	//obrigatório	
																				'precoServico'		=> $GLOBALS['B2C_PRODUCT'][B2C_PRODUCT_SERVICE_FINANCIALPLAN]['price'],	//obrigatório	
																				
																				//'garantiaSeguro'	=> NULL,		
																				//'dataVigencia'		=> str_replace(' ','T',date("Y-m-d H:i:s")),		
																				//'prazoVigencia'		=> NULL,		
																				//'pis'				=> NULL,				
																				//'iss'				=> NULL,				
																				//'dataCancelamento'	=> str_replace(' ','T',date("Y-m-d H:i:s")),	
																				//'motivoCancelamento'=> NULL,	
																				//'valorEstornado'	=> NULL,		
																				),
										//'produto'						=> array(
										//										'codigo'						=> '123456',
										//										'descricao'						=> 'Produto de Teste',
										//										'preco'							=> 10.35,
										//										'garantiaFabrica'				=> '1',
										//										'dataVenda'						=> str_replace(' ','T',date("Y-m-d H:i:s")),
										//										'fabricante'					=> 'Testador',
										//										'tipoVenda'						=> 1,
										//										'tipoProduto'					=> '1',
										//										'modelo'						=> '32A',
										//										'numeroSerie'					=> '3',
										//										'numeroParcelasFinanciamento'	=> '1',
										//										'precoTabela'					=> 10.40,
										//										),
										'cliente'						=> array(
																				'tipoPessoa'			=> 'PF',
																				'cpfCnpj'				=> '012345678901',
																				'nome'					=> 'Maria',
																				//'dataNascimento'		=> '1980-10-25T11:05:45',
																				//'sexo'					=> 'F',
																				//'estadoCivil'			=> 'S',
																				'telefoneCelular'		=> '11999998888',
																				'telefoneResidencial'	=> '1122223333',
																				//'rg'					=> '545454',
																				//'localExpedicaoRg'		=> 'SAO PAULO',
																				//'dataExpedicaoRg'		=> '1989-01-20T15:05:40',
																				'email'					=> 'teste@teste.com',
																				'endereco'				=> array(
																												'logradouro'	=> 'Av. Sem Fim',
																												'numero'		=> 999,
																												'bairro'		=> 'Fim do Mundo',
																												'cidade'		=> 'São Paulo',
																												'cep'			=> '01000000',
																												'complemento'	=> 'sem complemento',
																												'uf'			=> 'SP',
																												),
																				),
										),		
					);


	//registra venda
	$responseRegistroVenda = $teste->callService(B2C_ACTION_REGISTER_SALE, $params); 
	//completa a venda
	//$responseRegistroVenda = $teste->callService(B2C_ACTION_COMPLETE_SALE_DATA, $params); 

	echo "Resposta do Registro da Venda<pre>".print_r($responseRegistroVenda,true)."</pre>\n";

} //end if($responsePINstatus['vendaStatus']['status'] == B2C_PIN_STATUS_DISPONIVEL)
//final do bloco para teste da classe registrarVenda
*/
?>