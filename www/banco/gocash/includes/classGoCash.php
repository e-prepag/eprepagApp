<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

class GoCashAPI {
		
	private $soapClient;
	private $serialCode;	
	private $service_online;

	function __construct() {
		$this->service_online = false;

		try{
			$soapClient = @new SoapClient(GC_WSDL_URL, array('location'     => GC_SERVICE_URL,
															  'uri'          => GC_SERVICE_URL,
															  'cache_wsdl'   => WSDL_CACHE_NONE,
															  'soap_version' => SOAP_1_2,
															  'encoding'     => 'UTF-8',
															  'trace'        => 1,
															  'exceptions'   => 1));
			$this->service_online = true;
		} catch (SoapFault $e) {
			$this->logEvents("Caught exception A (".$e->faultcode."): ". $e->getMessage().PHP_EOL, GC_MSG_ERROR_LOG, 0);
//			echo GC_MSG_USER_PARSING_WSDL;
		}

	}

	public function get_service_status() {
		return $this->service_online;
	}
	public function callService($typeOfService = '', $requestParams = array()) {
						
		// Armazena na classe o Pin informado 
//		$this->serialCode = $requestParams['SerialCodes'];
		
		$goCashRequestRecord = $this->getRequestObject($typeOfService, $requestParams);
//echo "IN callService: <pre>".print_r($goCashRequestRecord, true)."</pre>\n";
//die("Stop trtr");

/*
		$context = array('http' =>
			array(
				'header'  => ''
				)
			);
			.... later in SoapClient definition
//												              'stream_context' => stream_context_create($context),
*/


		// http://stackoverflow.com/questions/6608086/soapclient-error-fallback-in-php
		try{
			$this->soapClient = @new SoapClient(GC_WSDL_URL, array('location'    => GC_SERVICE_URL,
																  'uri'          => GC_SERVICE_URL,
																  'cache_wsdl'   => WSDL_CACHE_NONE,
																  'soap_version' => SOAP_1_2,
																  'encoding'     => 'UTF-8',
																  'trace'        => 1,
																  'exceptions'   => 1,
																	)
												);
		} catch (SoapFault $e) {
			$this->logEvents( "Caught exception B (".$e->faultcode."): ". $e->getMessage()."<br>".PHP_EOL, GC_MSG_ERROR_LOG, 0);
//			echo GC_MSG_USER_PARSING_WSDL;
		}

// Testing SOAP headers
/*
$ns = 'http://service.gocashgamecard.com';
//Body of the Soap Header. 
$headerbody = array('Token' => '43344334', 
                    'Version' => '1.2.3', 
                    'MerchantID'=>'433221', 
                      'UserCredentials'=>array('UserID'=>'4444', 
                                             'Password'=>'xswedc')); 

//Create Soap Header.        
$header = new SOAPHeader($ns, 'RequestorCredentials', $headerbody);        
*/        
//		$header = new SoapHeader('http://service.gocashgamecard.com', 'ss', null);
//		$header = null;

//		$this->soapClient->__setSoapHeaders($header);
		
		if($this->soapClient) {
//echo "Before __soapCall ($typeOfService): <pre>".print_r($goCashRequestRecord, true)."</pre>\n";
			$resultWS = $this->soapClient->__soapCall($typeOfService, array($goCashRequestRecord));
		
		
			if ($resultWS instanceof SoapFault) {								
//				echo "  == ERROR: instanceof SoapFault<br> ";	
//echo "<hr>\n<pre>".htmlentities(str_replace("><", ">\n<", $this->getTransactionMessages()))."</pre>\n<hr>";
//				echo GC_MSG_USER_PARSING_WSDL;

				$this->logEvents($this->getErrorMessages($resultWS), GC_MSG_ERROR_LOG, 0);	
				$this->logEventsBD($this->getErrorMessages($resultWS), GC_MSG_ERROR_LOG, 0);		
			} else {
									
//echo "  == SUCCESS END: <pre>".print_r($resultWS, true)."</pre><br> ";	
//echo "<hr>\n<pre>".htmlentities(str_replace("><", ">\n<", $this->getTransactionMessages()))."</pre>\n<hr>";
//die("Stop ewewew");


				$goCashResponseRecord = $this->getResponseObject($typeOfService, $resultWS);			
			
//			echo "  == SUCCESS (2): <pre>".print_r($goCashResponseRecord, true)."</pre><br> ";	
			/* Obtem o PIN Response Status Code da Consulta WS
			if ($goCashResponseRecord instanceof stdClass)				
				$responseStatusCode = $goCashResponseRecord->PartnerServiceResult->RespCode;
			else if (is_array($goCashResponseRecord))
				$responseStatusCode = $goCashResponseRecord['RespCode'];
			
			// Se o PIN Response Status Code diferente de 200 (Normal) 
			if ( $responseStatusCode != GC_RESPONSE_STATUS_NORMAL ) {  
				$this->logEvents($this->getErrorMessages($resultWS, false), GC_MSG_ERROR_LOG, $responseStatusCode);
				$this->logEventsBD($this->getErrorMessages($resultWS, false), GC_MSG_ERROR_LOG, $responseStatusCode);
			}	
			else {		
				$this->logEvents($this->getTransactionMessages(), GC_MSG_TRANSACTION_LOG, $responseStatusCode);				
				$this->logEventsBD($this->getTransactionMessages(), GC_MSG_TRANSACTION_LOG, $responseStatusCode);
			}*/
				$this->logEvents($this->getTransactionMessages(), GC_MSG_TRANSACTION_LOG, $goCashResponseRecord);
			
				return $goCashResponseRecord;
			}
		} else {
			$this->logEvents( "Erro Interno B: soapClient n√£o definido<br>".PHP_EOL, GC_MSG_ERROR_LOG, 0);
//			echo GC_MSG_USER_PARSING_WSDL;
		}
		/*
		echo "<pre>";
		print_r($goCashResponseRecord);
		echo "<hr>";
		print_r($resultWS);
		//echo "<hr>";		
		//echo $this->getTransactionMessages();
		//echo "<hr>";
		//echo $this->getErrorMessages($resultWS,false);
		echo "</pre>";							
		*/
	}
		
	
	public function SerialCheckAction($params) {
		
		$sc = array();
		$sc["SerialCode"]	= $params["SerialCode"];
		$sc["GameCode"]		= "Null";
		$sc["ClientID"]		= GC_CLIENT_ID;
		$sc["Currency"]		= $params["Currency"];

		return $this->callService(GC_ACTION_SERIAL_CHECK, $sc);				
	}


	public function PayCashCheckAction($params) {
		
		$pc = array();

		$pc["ClientID"]		= GC_CLIENT_ID;
		$pc["Currency"]		= $params["Currency"];
		$pc["SerialCodes"]	= $params["SerialCodes"];
		$pc["GameCode"]		= "Null";
		$pc["OrderNo"]		= $params["OrderNo"];
		$pc["ChargeAmt"]	= $params["ChargeAmt"];

		return $this->callService(GC_ACTION_PAY_CASH_CHECK, $pc);
	}


	public function PayCashAction($params) {

		$pc = array();
		$pc["SerialCodes"]	= $params["SerialCodes"];
		$pc["PayerName"]	= $params["PayerName"];
		$pc["ServiceName"]	= $params["ServiceName"];
		$pc["PayerEmail"]	= $params["PayerEmail"];
		$pc["OrderNo"]		= $params["OrderNo"];
		$pc["PayerID"]		= $params["PayerID"];
		$pc["GameCode"]		= $params["GameCode"];
		$pc["ClientID"]		= GC_CLIENT_ID;
		$pc["ProdName"]		= $params["ProdName"];
		$pc["IPAddr"]		= $params["IPAddr"];
		$pc["ChargeAmt"]	= $params["ChargeAmt"];
		$pc["Currency"]		= $params["Currency"];

//		$goCashAPI->callService(GC_ACTION_PAY_CASH, $pc);
		return $this->callService(GC_ACTION_PAY_CASH, $pc);				

	}

		
	// General methods
	private function getRequestObject($typeOfService = '', $requestParams = array()) {		
		if ($typeOfService == GC_ACTION_SERIAL_CHECK) {
			$serialCheck = new SerialCheck();
			$serialCheckRequestObj = $serialCheck->getRequestData($requestParams);
			return $serialCheckRequestObj;
		}
		else if ($typeOfService == GC_ACTION_PAY_CASH) {
			$payCash = new PayCash();
			$payCashRequestObj = $payCash->getRequestData($requestParams);
			return $payCashRequestObj;
		}
		else if ($typeOfService == GC_ACTION_PAY_CASH_CHECK) {
//echo "  == REQUEST (1): <pre>".print_r($requestParams, true)."</pre><br> ";	
			$payCashCheck = new PayCashCheck();
			$payCashCheckRequestObj = $payCashCheck->getRequestData($requestParams);
//echo "  == REQUEST (2): <pre>".print_r($payCashCheckRequestObj, true)."</pre><br> ";	
//die("Stop dsdssd");
			return $payCashCheckRequestObj;
		}
		
	}	

	
	private function getResponseObject($typeOfService = '', $soapResponseData) {			
		if ($typeOfService == GC_ACTION_SERIAL_CHECK) {
			$serialCheck = new SerialCheck();
			$serialCheckResponseObj = $serialCheck->getResponseData($soapResponseData);
			return $serialCheckResponseObj;
		}
		else if ($typeOfService == GC_ACTION_PAY_CASH) {
//echo "  == SUCCESS (2): <pre>".print_r($soapResponseData, true)."</pre><br> ";	
			$payCash = new PayCash();
			$payCashResponseObj = $payCash->getResponseData($soapResponseData);
//echo "  == SUCCESS (3): <pre>".print_r($payCashRecord, true)."</pre><br> ";	
			return $payCashResponseObj;
		}
		else if ($typeOfService == GC_ACTION_PAY_CASH_CHECK) {
//echo "  == SUCCESS (2a): <pre>".print_r($soapResponseData, true)."</pre><br> ";	
			$payCashCheck = new PayCashCheck();
			$payCashCheckResponseObj = $payCashCheck->getResponseData($soapResponseData);
			return $payCashCheckResponseObj;
		}
	}
	
	
	public function getTransactionMessages() {

		if($this->soapClient) {
			$requestMsg        = htmlspecialchars_decode($this->soapClient->__getLastRequest());
			$requestHeaderMsg  = htmlspecialchars_decode($this->soapClient->__getLastRequestHeaders());
			$responseMsg       = htmlspecialchars_decode($this->soapClient->__getLastResponse());
			$responseHeaderMsg = htmlspecialchars_decode($this->soapClient->__getLastResponseHeaders());
			
			$msg  = "";
			$msg .= "--------------------------".PHP_EOL;
			$msg .= "Request :".PHP_EOL.PHP_EOL.str_replace('><','>'.PHP_EOL.'<',$requestMsg).PHP_EOL;
			$msg .= "--------------------------".PHP_EOL;
			$msg .= "RequestHeaders:".PHP_EOL.PHP_EOL.$requestHeaderMsg.PHP_EOL;
			$msg .= "--------------------------".PHP_EOL;
			$msg .= "Response:".PHP_EOL.PHP_EOL.str_replace('><','>'.PHP_EOL.'<',$responseMsg).PHP_EOL;
			$msg .= "--------------------------".PHP_EOL;
			$msg .= "ResponseHeaders:".PHP_EOL.PHP_EOL.$responseHeaderMsg.PHP_EOL.PHP_EOL;
		} else {
			$msg = "Erro Interno A: soapClient n„o definido";
		}
		return $msg;		
	}	

	
	public function getErrorMessages($resultWS, $isSoapFault = true) {
		
		if ($isSoapFault) {
			$msg .= "Message : ".$resultWS->getMessage().PHP_EOL;
			$msg .= "--------------------------".PHP_EOL;
			$msg .= "TraceString: ".$resultWS->getTraceAsString().PHP_EOL;
			$msg .= "--------------------------".PHP_EOL;
			$msg .= "Code: ".$resultWS->getCode().PHP_EOL;
			$msg .= "--------------------------".PHP_EOL;
			$msg .= "File: ".$resultWS->getFile().PHP_EOL;
			$msg .= "--------------------------".PHP_EOL;
			$msg .= "Line: ".$resultWS->getLine().PHP_EOL;
			$msg .= "--------------------------".PHP_EOL;
			$msg .= "FaultCode: ".$resultWS->faultcode.PHP_EOL;
			$msg .= "--------------------------".PHP_EOL;
			$msg .= "Detail: ".$resultWS->detail.PHP_EOL.PHP_EOL.PHP_EOL;
			$msg .= $this->getTransactionMessages();
		} else {
			$msg .= $this->getTransactionMessages();				
		}
		
		return $msg;
	}	
	
	
	private function logEvents($msg, $tipoLog = 'ERROR_LOG', $pinStatusCode = 0) {
			
		if($tipoLog == GC_MSG_ERROR_LOG) 
			$fileLog = LOG_FILE_GOCASH_WS_ERRORS;		
		else if($tipoLog == GC_MSG_TRANSACTION_LOG) 
			$fileLog = LOG_FILE_GOCASH_WS_TRANSACTIONS;
		
		$log  = "=================================================================================================".PHP_EOL;
		$log .= "DATA -> ".date("d/m/Y - H:i:s").PHP_EOL;
		$log .= "PIN  -> ".$this->pinNumber.PHP_EOL;
		$log .= "RESPONSE STATUS CODE -> ".$pinStatusCode.PHP_EOL;
		$log .= "---------------------------------".PHP_EOL;
		$log .= $msg;			
						
		$fp = fopen($fileLog, 'a+');
		fwrite($fp, $log);
		fclose($fp);		
	}

	public function SaveRedeemedPinList($pinNumber, $idvenda, $confirmaValorPin) {
	
	}
	
	public function saveSoapTransaction($params) {
/*
$b_is_reinaldo = false;
if(isset($GLOBALS['_SESSION']['usuarioGames_ser'])) {
	$usuarioGames = unserialize($_SESSION['usuarioGames_ser']);
	if($usuarioGames->b_IsLogin_reinaldopshotmail()) {
		$b_is_reinaldo = true;
echo "In saveSoapTransaction()<br>\n";
	}
}
*/
		$pgc_pin_number			= $params['PinNumber'];
		$pgc_face_amount		= $params['FaceAmount'] ? $params['FaceAmount'] : 0;
		$pgc_currency			= $params['Currency'] ? $params['Currency'] : '';
		$pgc_vg_id				= $params['IDVenda'] ? $params['IDVenda'] : 0;
		$pgc_order_no			= $params['OrderNo'] ? $params['OrderNo'] : 0;
		$pgc_ug_id				= $params['IDUsuario'] ? $params['IDUsuario'] : 0;
		$pgc_pin_response_date	= $params['RespDate'] ? $params['RespDate'] : '';
		$pgc_real_amount		= ConversionPINs::get_ValorReal('G', $pgc_face_amount);
		$pgc_opr_codigo			= $params['IDOperadora'] ? $params['IDOperadora'] : 0;

		$sql = "INSERT INTO pins_gocash (pgc_pin_number, pgc_face_amount, pgc_real_amount, pgc_currency, pgc_vg_id, 
										pgc_order_no, pgc_ug_id, pgc_pin_response_date, pgc_opr_codigo)
								VALUES (";
		$sql .= SQLaddFields($pgc_pin_number, "s"). ",";
		$sql .= SQLaddFields($pgc_face_amount, ""). ",";
		$sql .= SQLaddFields($pgc_real_amount, ""). ",";
		$sql .= SQLaddFields($pgc_currency, "s"). ",";
		$sql .= SQLaddFields($pgc_vg_id, ""). ",";
		$sql .= SQLaddFields($pgc_order_no, "s"). ",";
		$sql .= SQLaddFields($pgc_ug_id, ""). ",";
		$sql .= SQLaddFields($pgc_pin_response_date, "s"). ",";
		$sql .= SQLaddFields($pgc_opr_codigo, ""). "";
		$sql .= ")";

//if($b_is_reinaldo) {
//	echo "SQL: ".$sql."<br>\n";
//	die("Stop A1");
//}
//echo "<hr>$sql<hr>";
//die("Stop");			
		gravaLog_GoCash("Em saveSoapTransaction(): ".PHP_EOL.$sql.PHP_EOL);

		$rs   = SQLexecuteQuery($sql);

		if($rs) {
			$ret = true;
		}
		else {
			gravaLog_GoCash("Em saveSoapTransaction(): ERROR ao executar o SQL".PHP_EOL);
			$ret = false;
		}
		
		return $ret;
	}
	
	private function logEventsBD($msg, $tipoLog = 'ERROR_LOG', $pinStatusCode = 0) {
			
		$pgcl_pin_number = $this->pinNumber;
		$pgcl_request_type = $pinStatusCode;
		$pgcl_date_log = "CURRENT_TIMESTAMP";
		$pgcl_date_request = "CURRENT_TIMESTAMP";
		$pgcl_date_response = "CURRENT_TIMESTAMP";
		$pgcl_message = htmlspecialchars($msg);
		
		try {
			$sql = "INSERT INTO pins_gocash_log (pgcl_pin_number, pgcl_request_type, pgcl_date_log, pgcl_date_request,
			      								pgcl_date_response, pgcl_message) VALUES (";
							
			$sql .= SQLaddFields($pgcl_pin_number, "s"). ",";
			$sql .= SQLaddFields($pgcl_request_type, ""). ",";
			$sql .= SQLaddFields($pgcl_date_log, ""). ",";
			$sql .= SQLaddFields($pgcl_date_request, ""). ",";
			$sql .= SQLaddFields($pgcl_date_response, ""). ",";
			$sql .= SQLaddFields($pgcl_message, "s"). ")";
			$rs   = SQLexecuteQuery($sql);
				
			if($rs) {
				$ret = true;
			}
			else {
				throw new Exception("Erro ao tentar inserir o log : (".pg_errormessage().")");
			}
		} catch (Exception $e) {
			$msgError = "ERROR - ".$sql;
			$msgError .= $e->getMessage().PHP_EOL;
			$msgError .= $e->getTraceAsString().PHP_EOL;
			$this->logEvents($msgError);
		}		    								
	}

	private function selectEventsBD() {
		$sql = "SELECT * FROM pins_gocash_log";				
		$rs   = SQLexecuteQuery($sql);
		$l = array();	
		
		while ($result = pg_fetch_assoc($rs)) {
			$l[] = $result;	
		}
		
		return $l;
	}
	
}

?>
