<?php
//URLs

//test
define("RS_SERVICE_URL_TEST", "http://172.30.10.10/wsredesim/reqsrv.asmx");
define("RS_WSDL_URL_TEST", "http://172.30.10.10/wsredesim/reqsrv.asmx?WSDL");

//live
define("RS_SERVICE_URL_LIVE", "http://177.72.160.164/wsadvserv/reqsrv.asmx");
define("RS_WSDL_URL_LIVE", "http://177.72.160.164/wsadvserv/reqsrv.asmx?WSDL");

// Tipo de Mensagem do Sistema
define('RS_MSG_ERROR_LOG', 'ERROR_LOG');
define('RS_MSG_TRANSACTION_LOG', 'TRANSACTION_LOG');
define('RS_MSG_USER_PARSING_WSDL', 'RS_MSG_USER_PARSING_WSDL');

// Arquivo de Log onde serao registrados todos os erros gerados  
define("LOG_FILE_REDESIM_WS_ERRORS", DIR_LOG . "log_RedeSimWS-Errors.log");

// Arquivo de Log onde serao registrados todos os cabecalhos de Request/Response
define("LOG_FILE_REDESIM_WS_TRANSACTIONS", DIR_LOG . "log_RedeSimWS-Transactions.log");

// Arquivo com monitor de contatos ao WebService
define("RS_MONITOR_FILE", DIR_LOG . "monitor_redesim_online.txt");
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

// Funções disponíveis na API
define("RS_ACTION_RECARGA", "Req_Recarga");
define("RS_ACTION_SEGUROS", "Req_Seguros");

define("RS_USUARIO_EPP", "eprepag");
define("RS_SENHA_EPP", "hdvjadbn32");

//$ARQUIVO_RC_VECTOR_OPERADORAS = "C:/Sites/E-Prepag/www/web/prepag2/rs_ws/inc/inc_vector_recarga.php";
//$ARQUIVO_RC_VECTOR_SEGUROS = "C:/Sites/E-Prepag/www/web/prepag2/rs_ws/inc/inc_vector_seguros.php";

$ARQUIVO_RC_MONITOR = $raiz_do_projeto . "includes/rc/inc_monitor.txt";

// Constante que define o ambiente de conexão Produção (Live = 1) ou Homologação (Test = 2)
define("RS_LIVE_ENVIRONMET",	1);

// B2C Código dos Produtos

//test
define("RS_CELL_VIVO_TEST",	"VIVO");
define("RS_CELL_CLARO_TEST",	"CLARO");
define("RS_CELL_OI_TEST",	"OI");
define("RS_CELL_TIM_TEST",	"TIM");
define("RS_CELL_NEXTEL_TEST",	"NEXTEL");
define("RS_CELL_EMBRATEL_TEST",	"EMBRATEL");
define("RS_CELL_VIVOF_TEST",	"VIVOFIXO");
define("RS_CELL_VIVOF15_TEST",	"VIVOFIXOS15");

// live
define("RS_CELL_VIVO_LIVE",	"VIVO");
define("RS_CELL_CLARO_LIVE",	"CLARO");
define("RS_CELL_OI_LIVE",	"OI");
define("RS_CELL_TIM_LIVE",	"TIM");
define("RS_CELL_NEXTEL_LIVE",	"NEXTEL");
define("RS_CELL_EMBRATEL_LIVE",	"EMBRATEL");
define("RS_CELL_VIVOF_LIVE",	"VIVOFIXO");
define("RS_CELL_VIVOF15_LIVE",	"VIVOFIXOS15");

// CHANGING TEST AND LIVE
if(RS_LIVE_ENVIRONMET==1) {
        define("RS_SERVICE_URL",    RS_SERVICE_URL_LIVE);
        define("RS_WSDL_URL",       RS_WSDL_URL_LIVE);
	define("RS_CELL_VIVO",      RS_CELL_VIVO_LIVE);
	define("RS_CELL_CLARO",     RS_CELL_CLARO_LIVE);
	define("RS_CELL_OI",        RS_CELL_OI_LIVE);
	define("RS_CELL_TIM",       RS_CELL_TIM_LIVE);
	define("RS_CELL_NEXTEL",    RS_CELL_NEXTEL_LIVE);
        define("RS_CELL_EMBRATEL",  RS_CELL_EMBRATEL_LIVE);
        define("RS_CELL_VIVOF",     RS_CELL_VIVOF_LIVE);
        define("RS_CELL_VIVOF15",   RS_CELL_VIVOF15_LIVE);
} else {
        define("RS_SERVICE_URL",    RS_SERVICE_URL_TEST);
        define("RS_WSDL_URL",       RS_WSDL_URL_TEST);
	define("RS_CELL_VIVO",      RS_CELL_VIVO_TEST);
	define("RS_CELL_CLARO",     RS_CELL_CLARO_TEST);
	define("RS_CELL_OI",        RS_CELL_OI_TEST);
	define("RS_CELL_TIM",       RS_CELL_TIM_TEST);
	define("RS_CELL_NEXTEL",    RS_CELL_NEXTEL_TEST);
        define("RS_CELL_EMBRATEL",  RS_CELL_EMBRATEL_TEST);
        define("RS_CELL_VIVOF",     RS_CELL_VIVOF_TEST);
        define("RS_CELL_VIVOF15",   RS_CELL_VIVOF15_TEST);
}
//Comissões dos Produtos Recarga Rede Sim
$RS_COMISS_DEFULT = 1;
$RS_COMISS_DEFULT_LAN = 0.5;
$RS_PRODUCT = array(	
                    RS_CELL_VIVO    => array(
                                             'comiss'       => '4', //EM PERCENTAGEM
                                             'comiss_lan'   => '1', //EM PERCENTAGEM
					),
                    RS_CELL_CLARO   => array(
                                             'comiss'       => '4.5', //EM PERCENTAGEM
                                             'comiss_lan'   => '1.5', //EM PERCENTAGEM
					),
                    RS_CELL_OI      => array(
                                             'comiss'       => '4.5', //EM PERCENTAGEM
                                             'comiss_lan'   => '1.5', //EM PERCENTAGEM
					),
                    RS_CELL_TIM     => array(
                                             'comiss'       => '4', //EM PERCENTAGEM
                                             'comiss_lan'   => '1', //EM PERCENTAGEM
					),
                    RS_CELL_NEXTEL  => array(
                                             'comiss'       => '5', //EM PERCENTAGEM
                                             'comiss_lan'   => '2', //EM PERCENTAGEM
					),
                    RS_CELL_EMBRATEL=> array(
                                             'comiss'       => '5', //EM PERCENTAGEM
                                             'comiss_lan'   => '2', //EM PERCENTAGEM
					),
                    RS_CELL_VIVOF   => array(
                                             'comiss'       => '7', //EM PERCENTAGEM
                                             'comiss_lan'   => '3', //EM PERCENTAGEM
					),
                    RS_CELL_VIVOF15 => array(
                                             'comiss'       => '9', //EM PERCENTAGEM
                                             'comiss_lan'   => '4', //EM PERCENTAGEM
					),
                   );

$RS_DDD = array (
                 68 => 'AC',
                 82 => 'AL',
                 96 => 'AP',
                 92 => 'AM',
                 97 => 'AM',
                 71 => 'BA',
                 72 => 'BA',
                 73 => 'BA',
                 74 => 'BA',
                 75 => 'BA',
                 77 => 'BA',
                 85 => 'CE',
                 88 => 'CE',
                 61 => 'DF',
                 27 => 'ES',
                 28 => 'ES',
                 62 => 'GO',
                 64 => 'GO',
                 98 => 'MA',
                 99 => 'MA',
                 65 => 'MT',
                 66 => 'MT',
                 67 => 'MS',
                 31 => 'MG',
                 32 => 'MG',
                 33 => 'MG',
                 34 => 'MG',
                 35 => 'MG',
                 37 => 'MG',
                 38 => 'MG',
                 91 => 'PA',
                 93 => 'PA',
                 94 => 'PA',
                 83 => 'PB',
                 41 => 'PR',
                 42 => 'PR',
                 43 => 'PR',
                 44 => 'PR',
                 45 => 'PR',
                 46 => 'PR',
                 81 => 'PE',
                 87 => 'PE',
                 86 => 'PI',
                 21 => 'RJ',
                 22 => 'RJ',
                 24 => 'RJ',
                 83 => 'RN',
                 51 => 'RS',
                 53 => 'RS',
                 54 => 'RS',
                 55 => 'RS',
                 69 => 'RO',
                 95 => 'RR',
                 47 => 'SC',
                 48 => 'SC',
                 49 => 'SC',
                 11 => 'SP',
                 12 => 'SP',
                 13 => 'SP',
                 14 => 'SP',
                 15 => 'SP',
                 16 => 'SP',
                 17 => 'SP',
                 18 => 'SP',
                 19 => 'SP',
                 79 => 'SE',
                 63 => 'TO'
                );

//include $ARQUIVO_RC_VECTOR_OPERADORAS;
//include $ARQUIVO_RC_VECTOR_SEGUROS;

include_once "classReq_Recarga.php";
include_once "classReq_Seguros.php";

class RedeSimAPI {
		
	private $soapClient;
	private $service_online;
	private $bdebug;
	private $a_operadoras;
	private $a_seguros;
	private $a_valores;


	function __construct() {

		$this->a_operadoras = array();
		$this->a_valores = array();

		$this->service_online = false;

                //Hanilta o LOG
                //$this->set_debug_on();
                //Desabilita o LOG
                $this->set_debug_off();

		try{
			$soapClient = new SoapClient(RS_WSDL_URL, array('location'      => RS_SERVICE_URL,
															  'uri'          => RS_SERVICE_URL,
															  'cache_wsdl'   => WSDL_CACHE_NONE,
															  'soap_version' => SOAP_1_2,
															  'encoding'     => 'UTF-8',
															  'trace'        => 1,
															  'exceptions'   => 1));
			$this->service_online = true;
		} catch (SoapFault $e) {
			$this->logEvents("Caught exception A (".$e->faultcode."): ". $e->getMessage()."\n", RS_MSG_ERROR_LOG, 0);
			echo RS_MSG_USER_PARSING_WSDL;
		}

	}

	public function get_service_status() {
		return $this->service_online;
	}
        
        public function get_Comissao_EPP($id) {
            if(isset($GLOBALS['RS_PRODUCT'][$id]['comiss'])) {
		return $GLOBALS['RS_PRODUCT'][$id]['comiss'];
            }
            else return $GLOBALS['RS_COMISS_DEFULT'];
	}
	
	public function get_Comissao_LAN($id) {
            if(isset($GLOBALS['RS_PRODUCT'][$id]['comiss_lan'])) {
		return $GLOBALS['RS_PRODUCT'][$id]['comiss_lan'];
            }
            else return $GLOBALS['RS_COMISS_DEFULT_LAN'];
	}
	
	public function callService($typeOfService = '', $requestParams = array()) {
						
		$redeSimRequestRecord = $this->getRequestObject($typeOfService, $requestParams);
if($this->bdebug) echo "IN callService: <pre>".print_r($redeSimRequestRecord, true)."</pre>\n";

		// http://stackoverflow.com/questions/6608086/soapclient-error-fallback-in-php
		try{
			$this->soapClient = new SoapClient(RS_WSDL_URL, array('location'     => RS_SERVICE_URL,
																  'uri'          => RS_SERVICE_URL,
																  'cache_wsdl'   => WSDL_CACHE_NONE,
																  'soap_version' => SOAP_1_2,
																  'encoding'     => 'UTF-8',
																  'trace'        => 1,
																  'exceptions'   => 0,	// 1
																	)
												);
//                        echo "TRY OK";
		} catch (SoapFault $e) {
			$this->logEvents( "Caught exception B (".$e->faultcode."): ". $e->getMessage()."<br>\n", RS_MSG_ERROR_LOG, 0);
		}
		if($this->soapClient) {
			$resultWS = $this->soapClient->__soapCall($typeOfService, array($redeSimRequestRecord));
			if ($resultWS instanceof SoapFault) {								
				echo "  == ERROR: instanceof SoapFault<br> ";	
				echo RS_MSG_USER_PARSING_WSDL;

				$this->logEvents($this->getErrorMessages($resultWS), RS_MSG_ERROR_LOG, 0);	
				$this->logEventsBD($this->getErrorMessages($resultWS), RS_MSG_ERROR_LOG, 0);		
			} else {

if($this->bdebug) $this->logEvents(" callService RESPONSE :\n".$this->getTransactionMessages());
if($this->bdebug) echo "  == SUCCESS END: <pre>".print_r($resultWS, true)."</pre><br> ";	

				$redeSimResponseRecord = $this->getResponseObject($typeOfService, $resultWS);			
			
if($this->bdebug) echo "  == Depois de captura por [getResponseObject]: <pre>".print_r($redeSimResponseRecord, true)."</pre><br> ";
                                return $redeSimResponseRecord;
			}
		} else {
			$this->logEvents( "Erro Interno B: soapClient não definido<br>\n", RS_MSG_ERROR_LOG, 0);
		}
	}
		
	
	public function Req_RecargaAction($params) {
		
		$rc = array();

		$rc["Usuario"]	= RS_USUARIO_EPP;
		$rc["Senha"]	= RS_SENHA_EPP;

		if(isset($params["Telefone"])){
			$rc["Telefone"]	= $params["Telefone"];
		}
		if(isset($params["Operadora"])){
			$rc["Operadora"]	= $params["Operadora"];
		}
		if(isset($params["Valor"])){
			$rc["Valor"]	= $params["Valor"];
		}
		$rc["PontodeVenda"]	= $params["PontodeVenda"];
		$rc["NIR"]              = $params["NIR"];
if($this->bdebug) echo "Em Req_RecargaAction(): <pre>".print_r($rc, true)."</pre>\n";

		return $this->callService(RS_ACTION_RECARGA, $rc);				
	}


	public function Req_SegurosAction($params) {

		$sg = array();
		$sg["Usuario"]	= RS_USUARIO_EPP;
		$sg["Senha"]	= RS_SENHA_EPP;
		if(isset($params["Produto"])){
			$sg["Produto"]	= $params["Produto"];
		}
		if(isset($params["CPF"])){
			$sg["CPF"]		= $params["CPF"];
		}
		if(isset($params["LocaldeVenda"])){
			$sg["LocaldeVenda"]	= $params["LocaldeVenda"];
		}

		return $this->callService(RS_ACTION_SEGUROS, $sg);				

	}

	
	// General methods
	private function getRequestObject($typeOfService = '', $requestParams = array()) {		
		if ($typeOfService == RS_ACTION_RECARGA) {
			$Req_Recarga = new Req_Recarga();
			$Req_RecargaRequestObj = $Req_Recarga->getRequestData($requestParams);
			return $Req_RecargaRequestObj;
		}
		else if ($typeOfService == RS_ACTION_SEGUROS) {
			$Req_Seguros = new Req_Seguros();
			$Req_SegurosRequestObj = $Req_Seguros->getRequestData($requestParams);
			return $Req_SegurosRequestObj;
		}		
	}	

	
	private function getResponseObject($typeOfService = '', $soapResponseData) {			
		if ($typeOfService == RS_ACTION_RECARGA) {
			$Req_Recarga = new Req_Recarga();
			$Req_RecargaResponseObj = $Req_Recarga->getResponseData($soapResponseData);
			return $Req_RecargaResponseObj;
		}
		else if ($typeOfService == RS_ACTION_SEGUROS) {
if($this->bdebug) echo "  == SUCCESS (2): <pre>".print_r($soapResponseData, true)."</pre><br> ";	
			$Req_Seguros = new Req_Seguros();
			$Req_SegurosResponseObj = $Req_Seguros->getResponseData($soapResponseData);
if($this->bdebug) echo "  == SUCCESS (3): <pre>".print_r($Req_SegurosRecord, true)."</pre><br> ";	
			return $Req_SegurosResponseObj;
		}
	}
	
	
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
	}	

	
	public function set_debug_on() {
		$this->bdebug = true;
	}
	public function set_debug_off() {
		$this->bdebug = false;
	}
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
	}	
	
	
	private function logEvents($msg, $tipoLog = 'ERROR_LOG', $pinStatusCode = 0) {
			
		if($tipoLog == RS_MSG_ERROR_LOG) 
			$fileLog = LOG_FILE_REDESIM_WS_ERRORS;		
		else if($tipoLog == RS_MSG_TRANSACTION_LOG) 
			$fileLog = LOG_FILE_REDESIM_WS_TRANSACTIONS;
		
		$log  = "=================================================================================================\n";
		$log .= "DATA -> ".date("d/m/Y - H:i:s")."\n";
		$log .= "---------------------------------\n";
		$log .= $msg;			
						
		$fp = fopen($fileLog, 'a+');
		fwrite($fp, $log);
		fclose($fp);		
	}

	public function get_Operadoras_list() {
		return $this->a_operadoras;
	}

	public function set_Operadoras_list($a_menu) {
		$this->a_operadoras = array();
		foreach($a_menu as $key => $val) {
			if($val) {
				$a_opr = explode(":", $val);
				$this->a_operadoras[$a_opr[0]] = $a_opr[1];
			}
		}

	}

	public function get_Valores_list() {
		return $this->a_valores;
	}

	public function set_Valores_list($a_menu) {
		$this->a_valores = array();
		foreach($a_menu as $key => $val) {
			if($val) {
				$a_val = explode(":", $val);
				$this->a_valores[$a_val[0]] = $a_val[1];
			}
		}

	}
	/*****************************************************************************************
		$b_reset: true -> re-read from the webservice; false -> read data from cache, 
		$pontodevenda - id identifying the POS (Lan_ID, etc)
	*/
	public function load_Operadoras($b_reset, $pontodevenda) {
		// Default is empty
		$a_menu = array();
		if(!$b_reset) { }
		// get the list from the Webservice
		$params_rc = array();
		$params_rc["Usuario"]		= RS_USUARIO_EPP;
		$params_rc["Senha"]		= RS_SENHA_EPP;
		$params_rc["PontodeVenda"]	= "Lan_".$pontodevenda;
		if(isset($_SESSION['RS_NIR']) && $_SESSION['RS_NIR']) {
			$params_rc["NIR"]	= $_SESSION['RS_NIR'];
		}
if($this->bdebug) echo "Req_RecargaAction PARAMS: <pre>".print_r($params_rc, true)."</pre>\n";
		$resultReq_RecargaAction = $this->Req_RecargaAction($params_rc);

if($this->bdebug) echo "Req_RecargaAction RESPONSE: <pre style='background-color:#ccffcc'>".print_r($resultReq_RecargaAction, true)."</pre>\n";
		if($resultReq_RecargaAction['Retorno']=="0") {
			echo "<p>Req_RecargaAction SUCESSO: Comprovante: '".$resultReq_RecargaAction['Comprovante']."'</p>";
		} elseif($resultReq_RecargaAction['Retorno']=="1") {
			//$menu = $resultReq_RecargaAction['Menu']->string;
			//$this->set_Operadoras_list($menu);
			//$a_menu = $this->get_Operadoras_list();
                        $a_menu = $resultReq_RecargaAction['Menu']->string;
if($this->bdebug) echo "<p>Operadoras: <pre>".print_r($a_menu, true)."</pre></p>";
if($this->bdebug) echo "  ===  Read operadoras from Webservice<br>";
			// save everything
			$this->a_operadoras = $a_menu;
			return $a_menu;
		}
		return $a_menu;
	}

	/*****************************************************************************************
		$b_reset: true -> re-read from the webservice; false -> read data from cache, 
		$pontodevenda - id identifying the POS (Lan_ID, etc)
		$id_operadora - id da operadora em $this->a_operadoras
	*/
	public function load_Valores($b_reset, $pontodevenda, $id_operadora) {
if($this->bdebug) echo "Force?: ".(($b_reset)?"YES":"nope")."<br>";
		// Default is empty
		$a_menu = array();

		// If not defined -> try to load and exit if failed
		if(!($this->b_Operadoras_loaded())) {
			$a_menu = $this->load_Operadoras(false, $pontodevenda);
			if($this->b_Operadoras_loaded()) {
				return $this->a_operadoras;
			} else {
				return array();
			}
		}

		// Is it a valid ID?
		if($id_operadora<=0 || $id_operadora>count($this->a_operadoras)) {
			return array();
		}

		// get opr's name by ID
		$opr_name = $this->a_operadoras[$id_operadora];

		if(!$b_reset) {
			// get the list from this instance
			if($this->b_Operadoras_loaded()) {
echo "  ===  Read operadoras from instance<br>";
				return $this->a_valores;
			}
			// get the list from Session
			if($this->b_Valores_loaded($opr_name)) {
				$this->a_valores = $_SESSION['rc_db']['valores'][$opr_name];
echo "  ===  Read valores from SESSION<br>";
				return $this->a_valores;
			}
		}
		// get the list from the Webservice
		$params_rc = array();
		$params_rc["Usuario"]		= RS_USUARIO_EPP;
		$params_rc["Senha"]			= RS_SENHA_EPP;
		$params_rc["Operadora"]		= $id_operadora;
		$params_rc["PontodeVenda"]	= "Lan_".$pontodevenda;

if($this->bdebug) echo "Req_RecargaAction PARAMS: <pre>".print_r($params_rc, true)."</pre>\n";
		$resultReq_RecargaAction = $this->Req_RecargaAction($params_rc);

if($this->bdebug) echo "Retorno: '".$resultReq_RecargaAction['Retorno']."'<br>";
		if($resultReq_RecargaAction['Retorno']=="0") {
			echo "<p>Req_RecargaAction SUCESSO: Comprovante: '".$resultReq_RecargaAction['Comprovante']."'</p>";
		} elseif($resultReq_RecargaAction['Retorno']=="1") {
			$menu = $resultReq_RecargaAction['Menu']->string;
			$this->set_Operadoras_list($menu);
			$a_menu = $this->get_Operadoras_list();
if($this->bdebug) echo "<p>Valores: <pre>".print_r($a_menu, true)."</pre></p>";
if($this->bdebug) echo "  ===  Read valores from Webservice<br>";

			// save everything
			$this->a_valores = $a_menu;
			$_SESSION['rc_db']['valores'][$opr_name] = $a_menu;
			return $a_menu;
		}
		return $a_menu;
	}

	/*****************************************************************************************
		Load all the values
		$b_reset: true -> re-read from the webservice; false -> read data from cache, 
	*/
	public function load_Valores_Full($b_reset) {
		if($this->b_Operadoras_loaded()){
			foreach($this->a_operadoras as $key => $val) {
				echo "'$key' => <pre>".print_r($val, true)."</pre><br>";
			}
		} else {
			// Não foi possível carregar Operadoras
			return array();
		}

	}

	/*****************************************************************************************
		$b_reset: true -> re-read from the webservice; false -> read data from cache, 
	*/
	public function load_Seguros($b_reset) {
//echo "Force?: ".(($b_reset)?"YES":"nope")."<br>";
		// Default is empty
		$a_menu = array();
		if(!$b_reset) {
//			echo "get the list from this instance<br>";
//echo "<p>  Seguros: <pre>".print_r($this->a_seguros, true)."</pre></p>";
			// get the list from this instance
			if(isset($this->a_seguros) && is_array($this->a_seguros) && count($this->a_seguros)>0) {
echo "  ===  Read Seguros from instance<br>";
				return $this->a_seguros;
			}
//			echo "get the list from VECTOR<br>";
//echo "b_Vector_Seguros_loaded()?: ".(($this->b_Vector_Seguros_loaded())?"YES":"nope")."<br>";
			if($this->b_Vector_Seguros_loaded()) {
				$this->a_seguros = $GLOBALS['seguros_redesim_current'];
//echo "<p>  Seguro VECTOR: <pre>".print_r($this->a_seguros, true)."</pre></p>";
echo "  ===  Read seguros from VECTOR<br>";
				return $this->a_seguros;
			}
		}
		// get the list from the Webservice
echo "  ===  Read seguros from Webservice<br>";
		$params_sg = array();
		$params_sg["Usuario"]		= RS_USUARIO_EPP;
		$params_sg["Senha"]			= RS_SENHA_EPP;

//echo "Req_SegurosAction PARAMS: <pre>".print_r($params_sg, true)."</pre>\n";
		$resultReq_SegurosAction = $this->Req_SegurosAction($params_sg);
//echo "Req_SegurosAction Return: <pre>".print_r($resultReq_SegurosAction, true)."</pre>\n";


//echo "Req_SegurosAction RESPONSE: <pre style='background-color:#ccffcc'>".print_r($resultReq_SegurosAction, true)."</pre>\n";
//echo "Retorno: '".$resultReq_SegurosAction['Retorno']."'<br>";
		if($resultReq_SegurosAction['Retorno']=="0") {
			foreach($resultReq_SegurosAction['Item']->string as $key => $val) {
echo "$key => $val (R$ ".$resultReq_SegurosAction['ItemValor']->string[$key].", ".$resultReq_SegurosAction['ItemDescricao']->string[$key].")<br>";
				$a_menu_item = array();
				$a_menu_item['Item'] = $resultReq_SegurosAction['Item']->string[$key];
				$a_menu_item['ItemValor'] = $resultReq_SegurosAction['ItemValor']->string[$key];
				$a_menu_item['ItemDescricao'] = str_replace("'", "\'", $resultReq_SegurosAction['ItemDescricao']->string[$key]);
				$a_menu_item['ItemContrato'] = str_replace("'", "\'", $resultReq_SegurosAction['ItemContrato']->string[$key]);
				$a_menu[$val] = $a_menu_item;
			}
		}
//echo "Req_SegurosAction RESPONSE: <pre style='background-color:#ffffcc'>".print_r($a_menu, true)."</pre>\n";
//die("Stop 1234");
		return $a_menu;
	}

	/*****************************************************************************************/
	public function b_Operadoras_loaded() {
		return (isset($_SESSION['rc_db']['operadoras']) && is_array($_SESSION['rc_db']['operadoras']) && count($_SESSION['rc_db']['operadoras'])>0);
	}
	public function b_Vector_Recarga_loaded() {
		return (isset($GLOBALS['operadoras_redesim_current']) && is_array($GLOBALS['operadoras_redesim_current']) && count($GLOBALS['operadoras_redesim_current'])>0);
	}

	/*****************************************************************************************/
	public function b_Valores_loaded($opr_name) {
		return (isset($_SESSION['rc_db']['valores'][$opr_name]) && is_array($_SESSION['rc_db']['valores'][$opr_name]) && count($_SESSION['rc_db']['valores'][$opr_name])>0);
	}

	/*****************************************************************************************/
	public function b_Vector_Seguros_loaded() {
		return (isset($GLOBALS['seguros_redesim_current']) && is_array($GLOBALS['seguros_redesim_current']) && count($GLOBALS['seguros_redesim_current'])>0);
	}

	/*****************************************************************************************/
	public function print_db($db_prefix) {
		$sret = "";

		if($db_prefix=="rc" || $db_prefix=="all") { 
			$db = $GLOBALS['operadoras_redesim_current']; 
			$sret .= "<h3>Recarga de celular</h3>\n";
			foreach($db as $key => $val) {
				$sret .= " * [".$val['opr_id']."] ".$val['opr_nome'].": ";
				if(isset($val['opr_valores'])) {
					foreach($val['opr_valores'] as $key1 => $val1) {
						$sret .= "(".key($val1)." - ".current($val1)."), ";
					}
				}
				$sret .= "<br>\n";
			}
		}
		
		if($db_prefix=="sg" || $db_prefix=="all") { 
			$db = $GLOBALS['seguros_redesim_current']; 
			$sret .= "<h3>Seguros</h3>\n";
			foreach($db as $key => $val) {
				$sret .= " * [".$val['Item']."]  [".$val['ItemValor']."] '".$val['ItemDescricao']."'";
				$sret .= "<br>\n";
			}
		}
		return $sret;
	}
	/*****************************************************************************************/
	public function print_db_session_old($db_prefix) {
		$db = $_SESSION[$db_prefix.'_db'];
		$sret = "";
		foreach($db['operadoras'] as $key => $val) {
			$sret .= " * [$key] $val: ";
			if(isset($db['valores'][$val])) {
				foreach($db['valores'][$val] as $key1 => $val1) {
					$sret .= "[$key1] $val1, ";
				}
			}
			$sret .= "<br>";
		}
		return $sret;
	}

	// ================================================
	function geraVetor_recarga_db($a_db, $s_elapsed_time) {
            /*
		if(count($a_db)==0) {
			echo "<font color='red'>a_db() vazio -> VetorDB não foi salvo.</font><br>\n";
			return false;
		}
		$msg = "";
		$msg .= "<?php \n";
		$msg .= "// created: ".date("Y-m-d H:i:s")." \n";
		$msg .= "// total found: ".count($a_db)." oprs\n";
		$msg .= "// elapsed time: ".$s_elapsed_time."s\n";	
		$msg .= "\$operadoras_redesim_current_date_created = '".date("Y-m-d H:i:s")."'; \n";

		$msg .= "\$operadoras_redesim_current = array(\n";
		foreach($a_db as $key => $val) {
			$msg .= "\t'$key' => array('opr_id' => '".$val['opr_id']."', 'opr_nome' => '".$val['opr_nome']."', ";
			$msg .= "\n\t\t\t\t'opr_valores' => array(\n";
			foreach($val['opr_valores'] as $key1 => $val1) {
				$msg .= "\t\t\t\t\t\t\t\tarray('$key1' => '$val1'),\n";
			}
			$msg .= "\t\t),\n";	// Termina valoresPorDDD
			$msg .= "\t),\n";	// Termina cada operadora
		}
		$msg .= ");\n";	// Termina $operadoras_current
		$msg .= "\n?>\n";

		$this->grava_inc_vector_recarga_file($msg);
             */
	}

	function grava_inc_vector_recarga_file($mensagem){
            /*
		if(php_sapi_name()=="isapi") {
			$cParagraphBlue_open = "<p style='color:blue'>";
			$cParagraphRed_open = "<p style='color:red'>";
			$cParagraph_close = "</p>";
			$cReturn = "<br>\n";
		} else {
			$cParagraphBlue_open = "";
			$cParagraphRed_open = "";
			$cParagraph_close = "\n";
			$cReturn = "\n";
		}

//		$time_start = getmicrotime();
		//Arquivo
		$file = $GLOBALS['ARQUIVO_RC_VECTOR_OPERADORAS'];
		$file_name = basename($file); 
		$a_fname = explode(".", $file_name);
	//echo "<pre>".print_r($a_fname, true)."</pre>";

		$srand =  str_pad(rand(1,999), 3, "0", STR_PAD_LEFT);
		$file_name_new = $a_fname[0]."_".date("YmdHis")."_".$srand.".".$a_fname[1];
	//echo "file_name_new: '$file_name_new'<br>";
		$s_lmod = date("Y/m/d H:i:s", filemtime($file));
	//echo "Last modified: " . $s_lmod."<br>";
		$file_name_old = $a_fname[0]."_".date("YmdHis", filemtime($file))."_".date("YmdHis").".".$a_fname[1];
	//echo "file_name_old: '$file_name_old'<br>";

		$spath_new = str_replace($file_name, $file_name_new, $file);
		$file_old = str_replace($file_name, "bkp/".$file_name_old, $file);
	//echo "<br>file: '$file'<br>spath_new: '$spath_new'<br>file_old: '$file_old'<br>";

	//	if (is_writable($file)) {
			//Grava mensagem no arquivo
			if ($handle = fopen($spath_new, 'w')) {
				fwrite($handle, $mensagem);
				fclose($handle);
				echo $cParagraphBlue_open."Salvou novo arquivo INC ($spath_new)".$cParagraph_close;

				echo $cParagraphBlue_open."Rename ('$file' -> '$file_old')".$cParagraph_close;
				rename($file, $file_old);
				echo $cParagraphBlue_open."Rename ('$spath_new' -> '$file')".$cParagraph_close;
				rename($spath_new, $file);
			} else {
				echo $cParagraphRed_open."ERRO ao salvar novo arquivo INC ($spath_new)".$cParagraph_close;
			}	
	//	} else {
	//		echo $cParagraphRed_open."NÃO salvou arquivo INC (arquivo protegido para escrita)".$cParagraph_close;
	//	}
//		echo $cParagraphBlue_open."(Create and rename files - elapsed time: ".number_format(getmicrotime() - $time_start, 2, '.', '.')."s)".$cParagraph_close;
             */
	}


	// ================================================
	function geraVetor_seguros_db($a_db, $s_elapsed_time) {
            /*
//echo "<hr><pre style='background-color:#CCCCFF;color:blue'>".print_r($a_db, true)."</pre><hr>";
		if(count($a_db)==0) {
			echo "<font color='red'>a_db() vazio -> Vetor_Seguros_DB não foi salvo.</font><br>\n";
			return false;
		}
		$msg = "";
//die("Stop");
//$msg .= "//".str_repeat("=",80)."\n"; 
//		$time_start0 = getmicrotime();
		$msg .= "<?php \n";
		$msg .= "// created: ".date("Y-m-d H:i:s")." \n";
		$msg .= "// total found: ".count($a_db)." oprs\n";
		$msg .= "// elapsed time: ".$s_elapsed_time."s\n";	
		$msg .= "\$seguros_redesim_current_date_created = '".date("Y-m-d H:i:s")."'; \n";

		$msg .= "\$seguros_redesim_current = array(\n";
		foreach($a_db as $key => $val) {
			$msg .= "\t'$key' => ";
			$msg .= "\tarray(\n";
			$msg .= "\t\t'Item' => '".$val['Item']."',\n";
			$msg .= "\t\t'ItemDescricao' => '".$val['ItemDescricao']."',\n";
			$msg .= "\t\t'ItemValor' => '".$val['ItemValor']."',\n";
			$msg .= "\t\t'ItemContrato' => '".$val['ItemContrato']."',\n";

			$msg .= "\t),\n";	// 
		}
		$msg .= ");\n";	// Termina $seguros_current
//		$msg .= "// elapsed time: ".number_format((getmicrotime() - $time_start0), 2, '.', '.')."s\n";	
		$msg .= "\n?>\n";

//echo str_replace("\n", "".$GLOBALS['cReturn']."\n", ("\n// created: ".date("Y-m-d H:i:s")." \n".$msg));
		$this->grava_inc_vector_seguros_file($msg);
             */
	}

	function grava_inc_vector_seguros_file($mensagem){
                /*
		if(php_sapi_name()=="isapi") {
			$cParagraphBlue_open = "<p style='color:blue'>";
			$cParagraphRed_open = "<p style='color:red'>";
			$cParagraph_close = "</p>";
			$cReturn = "<br>\n";
		} else {
			$cParagraphBlue_open = "";
			$cParagraphRed_open = "";
			$cParagraph_close = "\n";
			$cReturn = "\n";
		}

//		$time_start = getmicrotime();
		//Arquivo
		$file = $GLOBALS['ARQUIVO_RC_VECTOR_SEGUROS'];
		$file_name = basename($file); 
		$a_fname = explode(".", $file_name);
	//echo "<pre>".print_r($a_fname, true)."</pre>";

		$srand =  str_pad(rand(1,999), 3, "0", STR_PAD_LEFT);
		$file_name_new = $a_fname[0]."_".date("YmdHis")."_".$srand.".".$a_fname[1];
	//echo "file_name_new: '$file_name_new'<br>";
		$s_lmod = date("Y/m/d H:i:s", filemtime($file));
	//echo "Last modified: " . $s_lmod."<br>";
		$file_name_old = $a_fname[0]."_".date("YmdHis", filemtime($file))."_".date("YmdHis").".".$a_fname[1];
	//echo "file_name_old: '$file_name_old'<br>";

		$spath_new = str_replace($file_name, $file_name_new, $file);
		$file_old = str_replace($file_name, "bkp/".$file_name_old, $file);
	//echo "<br>file: '$file'<br>spath_new: '$spath_new'<br>file_old: '$file_old'<br>";

	//	if (is_writable($file)) {
			//Grava mensagem no arquivo
			if ($handle = fopen($spath_new, 'w')) {
				fwrite($handle, $mensagem);
				fclose($handle);
				echo $cParagraphBlue_open."Salvou novo arquivo INC ($spath_new)".$cParagraph_close;

				echo $cParagraphBlue_open."Rename ('$file' -> '$file_old')".$cParagraph_close;
				rename($file, $file_old);
				echo $cParagraphBlue_open."Rename ('$spath_new' -> '$file')".$cParagraph_close;
				rename($spath_new, $file);
			} else {
				echo $cParagraphRed_open."ERRO ao salvar novo arquivo INC ($spath_new)".$cParagraph_close;
			}	
	//	} else {
	//		echo $cParagraphRed_open."NÃO salvou arquivo INC (arquivo protegido para escrita)".$cParagraph_close;
	//	}
//		echo $cParagraphBlue_open."(Create and rename files - elapsed time: ".number_format(getmicrotime() - $time_start, 2, '.', '.')."s)".$cParagraph_close;
                 */
	}

	function get_select_Operadoras($id_selected = null, $operadoras) {
		$sret = "";
		//if($this->get_service_status()) {
			$aValores = $operadoras;
		//}//end if($dados->get_service_status())
		//else {
		//	$aValores = array();
		//}
                //echo "<pre>".print_r($aValores,true)."</pre>";
		$sret .= "<select id='provider' name='provider' class='form-xl' onchange='javascript:carga_valor();'>\n"; 
		$sret .= "<option value=''>Selecione a Operadora</option>\n";
		foreach($aValores as $key => $val) {
                    //echo "val: ".$val."<br>";
                    $labelProvider = strtoupper($val);
                    if(strpos($labelProvider," ")) {
                        $labelProvider  =   substr($labelProvider,(strpos($labelProvider," ")+1),strlen($labelProvider));
                    }
                    if(strpos($labelProvider,"-")) {
                        $labelProvider  =   substr($labelProvider,(strpos($labelProvider,"-")+1),strlen($labelProvider));
                    }
                    //echo "POSICAUN: {".$GLOBALS['RS_DDD'][$id_selected]."} - [$labelProvider]<br>";
                    
                    //If para disponibilizar somente operadoras que atuam no DDD informado
                    if(!empty($val) && ($GLOBALS['RS_DDD'][$id_selected] == $labelProvider || (strlen(str_replace($GLOBALS['RS_DDD'][$id_selected], "", $labelProvider))&& strlen(str_replace($GLOBALS['RS_DDD'][$id_selected], "", $labelProvider)) <> strlen($labelProvider)))) {
			list($valor,$label) = explode(":", $val, 2);
                        //If para limpar o estado do nome da operadora
                        //if(!(strlen(str_replace($GLOBALS['RS_DDD'][$id_selected], "", $labelProvider))&& strlen(str_replace($GLOBALS['RS_DDD'][$id_selected], "", $labelProvider)) <> strlen($labelProvider))) {
                            if(strpos($label," ")) {
                                $label  =   substr($label,0,strpos($label," "));
                            }
                            if(strpos($label,"-")) {
                                $label  =   substr($label,0,strpos($label,"-"));
                            }
                        //}//end if(!(strlen(str_replace($GLOBALS['RS_DDD'][$id_selected], "", $labelProvider))&& strlen(str_replace($GLOBALS['RS_DDD'][$id_selected], "", $labelProvider)) <> strlen($labelProvider))) 
                        
                        //IF abaixo exclui Claro SP1 e Claro SP2 das opções do menu
                        if (!((strlen(str_replace("CLARO", "", strtoupper($val))) <> strlen($val)) && (strlen(str_replace($GLOBALS['RS_DDD'][11], "", $labelProvider)) <> strlen($labelProvider)))) {
                            $sret .= "<option value='".$val."'";
                            if($id_selected == $val) {
                                    $sret .= " selected";
                            }
                            $sret .= ">".$label."</option>\n";
                        }//end $val
                    }//end if(!empty($val))
		}//end foreach
		$sret .= "</select> \n";
		$sret .= "<br>\n";

		return $sret;
	}

	function get_select_Valores($id, $id_selected = null, $pontodevenda = null) {
		$sret = "";
		if ($id > 0){
                        $a_menu = array();
                        // get the list from the Webservice
                        $params_rc = array();
                        $params_rc["Usuario"]		= RS_USUARIO_EPP;
                        $params_rc["Senha"]		= RS_SENHA_EPP;
                        $params_rc["PontodeVenda"]	= "Lan_".$pontodevenda;
                        $params_rc["Operadora"]         = $id;//"'".str_pad($id, 4, "0", STR_PAD_LEFT)."'";  // produces "-=-=-Alien" //"'".$id."'";
                        if(isset($GLOBALS['_SESSION']['RS_NIR']) && $GLOBALS['_SESSION']['RS_NIR']) {
                                $params_rc["NIR"]	= $GLOBALS['_SESSION']['RS_NIR'];
                        }
        if($this->bdebug) echo "Req_RecargaAction PARAMS: <pre>".print_r($params_rc, true)."</pre>\n";
                        $resultReq_RecargaAction = $this->Req_RecargaAction($params_rc);
        if($this->bdebug) echo "Req_RecargaAction RESPONSE: <pre style='background-color:#ccffcc'>".print_r($resultReq_RecargaAction, true)."</pre>\n";
                        if($resultReq_RecargaAction['Retorno']=="0") {
                                echo "<p>Req_RecargaAction SUCESSO: Comprovante: '".$resultReq_RecargaAction['Comprovante']."'</p>";
                        } elseif($resultReq_RecargaAction['Retorno']=="1") {
                                $menu = $resultReq_RecargaAction['Menu']->string;
        if($this->bdebug) echo "<p>Valores: <pre>".print_r($menu, true)."</pre></p>";
        if($this->bdebug) echo "  ===  Read operadoras from Webservice<br>";
                                $this->a_valores = $menu;
                        }
                        $sret .= "<select class='form-xl' id='planId' name='planId'>\n"; // onChange='javascript:do_change_value();'
			$sret .= "<option value='-1'>";
			$sret .= ((count($this->a_valores)>0)?"Selecione o Valor":"Sem valores fixos");
			$sret .= "</option>\n";
			foreach($this->a_valores as $key => $val) {
                            if(!empty($val)) {
                                list($valor,$label) = explode(":", $val, 2);
				$sret .= "<option value='$valor'>$label</option>\n";
                            }
			}//end foreach
			$sret .= "</select> \n";
			$sret .= "<br>\n";
		} else {
			$sret .= "Não foi selecionado a Operadora<br>\n";
		}
		return $sret;
	}//end function get_select_Valores($id, $id_selected = null, $pontodevenda = null) 

        function get_ValorFixo($id, $idvalor) {
                $aValorFixo = 0;
        	if ($id > 0){
                        $a_menu = array();
                        // get the list from the Webservice
                        $params_rc = array();
                        $params_rc["Usuario"]		= RS_USUARIO_EPP;
                        $params_rc["Senha"]		= RS_SENHA_EPP;
                        $params_rc["PontodeVenda"]	= "Lan_".$pontodevenda;
                        $params_rc["Operadora"]         = $id;//"'".str_pad($id, 4, "0", STR_PAD_LEFT)."'";  // produces "-=-=-Alien" //"'".$id."'";
                        if(isset($GLOBALS['_SESSION']['RS_NIR']) && $GLOBALS['_SESSION']['RS_NIR']) {
                                $params_rc["NIR"]	= $GLOBALS['_SESSION']['RS_NIR'];
                        }
        if($this->bdebug) echo "Req_RecargaAction PARAMS: <pre>".print_r($params_rc, true)."</pre>\n";
                        $resultReq_RecargaAction = $this->Req_RecargaAction($params_rc);
        if($this->bdebug) echo "Req_RecargaAction RESPONSE: <pre style='background-color:#ccffcc'>".print_r($resultReq_RecargaAction, true)."</pre>\n";
                        if($resultReq_RecargaAction['Retorno']=="0") {
                                echo "<p>Req_RecargaAction SUCESSO: Comprovante: '".$resultReq_RecargaAction['Comprovante']."'</p>";
                        } elseif($resultReq_RecargaAction['Retorno']=="1") {
                                $menu = $resultReq_RecargaAction['Menu']->string;
        if($this->bdebug) echo "<p>Valores: <pre>".print_r($menu, true)."</pre></p>";
        if($this->bdebug) echo "  ===  Read operadoras from Webservice<br>";
                                $this->a_valores = $menu;
                                foreach($this->a_valores as $key => $val) {
                                    if(!empty($val)) {
                                        list($valor,$label) = explode(":", $val, 2);
                                        if($valor == $idvalor) {
                                            $aValorFixo = str_replace(",", ".",str_replace("R$ ", "", $label));
                                        }//end if($valor == $idvalor)
                                    }//end if(!empty($val))
                                }//end foreach
                        }//end elseif($resultReq_RecargaAction['Retorno']=="1")
                }//end if ($id > 0)
	        return $aValorFixo;
        }

        function get_select_Seguros($id_selected = null) {
		$sret = "";
		if($this->get_service_status()) {
				
			$aValores = $GLOBALS['seguros_redesim_current'];
			

		}//end if($dados->get_service_status())
		else {
			$aValores = array();
		}
		$sret .= "<select id='provider' name='provider' class='form-xl' onchange='javascript:carga_valor_seguros();'>\n"; 
		$sret .= "<option value=''>Selecione o Seguro</option>\n";
		foreach($aValores as $key => $val) {
			$sret .= "<option value='".$val['Item']."'";
			if($id_selected != null) {
				$sret .= " selected";
			}
			$sret .= ">".$val['Item']." - ".utf8_decode($val['ItemDescricao'])."</option>\n";
		}
		$sret .= "</select> \n";
		$sret .= "<br>\n";

		return $sret;
	}

	function get_select_Valores_Seguros($id) {
		$sret = "";
		if (($id*1) > 0){
			if($this->get_service_status()) {
					
				$aValoresAux = $GLOBALS['seguros_redesim_current'];
				foreach($aValoresAux as $key => $val) {
					if ($val['Item'] == $id) {
						$aValores = $val['ItemValor'];
					}//end if ($val['opr_id'] == $id)
				}//end foreach
				

			}//end if($dados->get_service_status())
			
			$sret .= "<b>R$ ".$aValores."</b><br><br>\n<textarea rows='8' cols='50'>".$this->getContratoSeguro($id)."</textarea>";

		} else {
			$sret .= "Não foi selecionado o Seguro<br>\n";
		}

		return $sret;
	}//end function get_select_Valores($id) 


	function getContratoSeguro($id) {
		$sret = "";
		if (($id*1) > 0){
			if($this->get_service_status()) {
					
				$aValoresAux = $GLOBALS['seguros_redesim_current'];
				foreach($aValoresAux as $key => $val) {
					if ($val['Item'] == $id) {
						$aContrato= $val['ItemContrato'];
					}//end if ($val['opr_id'] == $id)
				}//end foreach
				

			}//end if($dados->get_service_status())
			
			$sret .= utf8_decode($aContrato);

		} else {
			$sret .= "Não foi selecionado o Seguro<br>\n";
		}

		return $sret;
	}//end function getContratoSeguro($id) 

	// Carrega ddos de Consulta com id_venda vg_id
	function get_new_idvenda() {
		$b_unique = false;
		$iloop = 1;
		do{
			$vg_id = rand(1, 1e7-1);

			$sql = "select * from tb_recarga_pedidos_rede_sim where rprs_vg_id = $vg_id order by rprs_data_inclusao desc limit 1";
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

	//Salva o Pedido
	function salvaPedido($tipo, $params) {
		$vg_id = $this->get_new_idvenda();
		$GLOBALS['_SESSION']['vendarecarga'] = $vg_id;
		if(isset($GLOBALS['_SESSION']['dist_usuarioGames_ser'])) {
			$usuarioGames = unserialize($_SESSION['dist_usuarioGames_ser']);
			$ug_id = $usuarioGames->getId();
		}
		else {
			$ug_id = 0;
		}
		$sql = "INSERT INTO tb_recarga_pedidos_rede_sim ( rprs_data_inclusao,  rprs_tipo,  rprs_vg_id,  rprs_codigooperadora,  rprs_codigorede,  rprs_codigoproduto,  rprs_numerocelular,  rprs_valor, rprs_ug_id, rprs_email, rprs_nir, rprs_comissao_total, rprs_comissao_para_repasse, rprs_label_operadora ) ";
		$sql .= "VALUES (NOW(),'$tipo',$vg_id, '".$params['provider']."', '".$params['codigorede']."', '".$params['planId']."','".$params['numerocelular']."', ".$params['valor'].", $ug_id, '".$params['email']."', '".$params['nir']."', ".$this->get_Comissao_EPP(trim(strtoupper($params['labelProvider']))).", ".$this->get_Comissao_LAN(trim(strtoupper($params['labelProvider']))).", '".$params['labelProvider']."');";
		$rs = SQLexecuteQuery($sql);
	//if($this->bdebug) echo "SQL:[$sql]<br>";
                if(!$rs) {
			echo "Erro ao Salvar Recarga (256).";
			return false;
		} 
		else {
			return true;
		}
	}//end function salvaPedido($tipo, $params)

	// Carrega dados de Consulta com id_venda vg_id para seguros
	function get_new_idvenda_seguro() {
		$b_unique = false;
		$iloop = 1;
		do{
			$vg_id = rand(1, 1e7-1);

			$sql = "select * from tb_seguro_pedidos_rede_sim where sprs_vg_id = $vg_id order by sprs_data_inclusao desc limit 1";
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

	//Salva o Pedido Seguro
	function salvaPedidoSeguro($tipo, $params) {

		$vg_id = $this->get_new_idvenda_seguro();
		$GLOBALS['_SESSION']['vendaseguro'] = $vg_id;
		if(isset($GLOBALS['_SESSION']['dist_usuarioGames_ser'])) {
			$usuarioGames = unserialize($_SESSION['dist_usuarioGames_ser']);
			$ug_id = $usuarioGames->getId();
		}
		else {
			$ug_id = 0; 
		}
		$sql = "INSERT INTO tb_seguro_pedidos_rede_sim (sprs_data_inclusao, sprs_tipo, sprs_vg_id, sprs_codigoproduto, sprs_valor, sprs_contrato, sprs_ug_id, sprs_email, sprs_cpf) ";
		$sql .= "VALUES (NOW(),'$tipo',$vg_id, '".$params['provider']."', ".$params['valor'].", '".$params['contrato']."', $ug_id, '".$params['email']."', '".$params['cpf']."');";
		$rs = SQLexecuteQuery($sql);
		if(!$rs) {
			echo "Erro ao Salvar Recarga (256).";
			return false;
		} 
		else {
			return true;
		}
	}//end function salvaPedido($tipo, $params)


}//end class
?>