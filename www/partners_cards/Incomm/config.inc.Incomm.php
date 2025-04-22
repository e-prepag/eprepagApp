<?php
if(checkIP()) {
    $server_url = $_SERVER['SERVER_NAME'];
    }
else {
    $server_url = "www.e-prepag.com.br";
    }

// Constante contendo a identifica��o do Ambiente de Teste
define("INCOMM_ENVIRONMENT_TEST",	1);
    
// Constante contendo a identifica��o do Ambiente de Produ��o
define("INCOMM_ENVIRONMENT_LIVE",	2);
    
// Constante que define o Ambiente de Integra��o. Onde (TESTE = 1) ou (PRODU��O = 2)
define("INCOMM_ENVIRONMENT",INCOMM_ENVIRONMENT_LIVE);

// Identificadores de Consulta
define("INCOMM_ID_CONSULT_TEST",	"");
define("INCOMM_ID_CONSULT_LIVE",	"");

// INCOMM SOAP Action Name
define("INCOMM_XML_REQUISICAO_TEST",	"Consultar"); 
define("INCOMM_XML_REQUISICAO_LIVE",	"Consultar"); 

//TESTE
//Ambiente de Teste SEM HTTPS
//define("INCOMM_SERVICE_URL_TEST",	"http://milws4-test.incomm.com:8080/transferedvalue/gateway"); 
//define("INCOMM_WSDL_URL_TEST",	"http://milws4-test.incomm.com:8080/transferedvalue/gateway?wsdl");
//Ambiente de Teste com HTTPS
//URL antigas antes do TLSv1.2
//define("INCOMM_SERVICE_URL_TEST",	"https://milws4-test.incomm.com:8443/transferedvalue/gateway"); 
//define("INCOMM_WSDL_URL_TEST",		"https://milws4-test.incomm.com:8443/transferedvalue/gateway?wsdl");

define("INCOMM_SERVICE_URL_TEST",	"https://transferredvalue-test.incomm.com:8443/transferedvalue/gateway"); 
define("INCOMM_WSDL_URL_TEST",		"https://transferredvalue-test.incomm.com:8443/transferedvalue/gateway?wsdl");

//define("INCOMM_SERVICE_URL_TEST",	"https://incommfake.xxx:442/transferedvalue/gateway");
//define("INCOMM_WSDL_URL_TEST",		"https://incommfake.xxx:442/transferedvalue/gateway?wsdl");

//PRODU��O
//URL antigas antes do TLSv1.2
//define("INCOMM_SERVICE_URL_LIVE",	"https://milws.incomm.com:8443/transferedvalue/gateway");
//define("INCOMM_WSDL_URL_LIVE",		"https://milws.incomm.com:8443/transferedvalue/gateway?wsdl");

define("INCOMM_SERVICE_URL_LIVE",	"https://transferredvalue.incomm.com:8443/transferedvalue/gateway");
define("INCOMM_WSDL_URL_LIVE",		"https://transferredvalue.incomm.com:8443/transferedvalue/gateway?wsdl");

//define("INCOMM_SERVICE_URL_LIVE",	"https://incommfake.xxx:442/transferedvalue/gateway");
//define("INCOMM_WSDL_URL_LIVE",		"https://incommfake.xxx:442/transferedvalue/gateway?wsdl");

// URLS
if(INCOMM_ENVIRONMENT == INCOMM_ENVIRONMENT_LIVE) {
	define("SERVICE_URL",	INCOMM_SERVICE_URL_LIVE);
	define("WSDL_URL",	INCOMM_WSDL_URL_LIVE);
	define("ID_CONSULT",	INCOMM_ID_CONSULT_LIVE);
	define("XML_REQUISICAO",INCOMM_XML_REQUISICAO_LIVE); 
} else {
	define("SERVICE_URL",	INCOMM_SERVICE_URL_TEST);
	define("WSDL_URL",	INCOMM_WSDL_URL_TEST);
	define("ID_CONSULT",	INCOMM_ID_CONSULT_TEST);
	define("XML_REQUISICAO",INCOMM_XML_REQUISICAO_TEST); 
}

// VENDOR_NAME Paramnetro que idenfitica a E-Prepag junto ao distribuidor
define("VENDOR_NAME",			"E-PREPAG");

// REQ_CAT Paramnetro de uso interno do distribuidor
define("REQ_CAT",			"TransferredValue");

// INQUIRY Paramnetro de consulta de PIN
define("INQUIRY",			"ConsultaPIN");

// REDEEM Paramnetro de utiliza��o de PIN
define("REDEEM",			"UtilizaPIN");

// REVERSE Paramnetro de utiliza��o de PIN
define("REVERSE",			"ReversaoPIN");

// C�digos de Sucesso na Consulta do PIN
define("INQUIRY_SUCESS",		"4001");

// C�digos de Cart�o Existente mas n�o ativo para utiliza��o
define("INQUIRY_INACTIVE",		"4002");

// C�digos de Cart�o Existente e j� Utilizado anteriormente
define("INQUIRY_USED",			"4003");

// C�digos de Cart�o Existente e j� Utilizado anteriormente
define("INQUIRY_NOT_FOUND",		"4006");

// C�digos de Sucesso na Consulta do PIN
define("REDEEM_SUCESS",			"0");

// Tipo de Mensagem do Sistema
define("MSG_ERROR_LOG",			"ERROR_LOG");
define("MSG_TRANSACTION_LOG",		"TRANSACTION_LOG");

// mensagens para usu�rio
define("MSG_USER_PARSING_WSDL",		"Este c�digo de servi�o n�o foi identificado (ERRO: WS354).<br>Por favor, verifique se o servi�o foi selecionado corretamente ou entre em contato com o <a href='mailto:suporte@e-prepag.com.br'>suporte@e-prepag.com.br</a><br>");

// Arquivo de Log onde serao registrados todos os erros gerados  
define("LOG_FILE_WS_ERRORS",		$raiz_do_projeto . "log/log_Incomm_WS-Errors.log");

// Arquivo de Log onde serao registrados todos os cabecalhos de Request/Response
define("LOG_FILE_WS_TRANSACTIONS",	$raiz_do_projeto . "log/log_Incomm_WS-Transactions.log");

// Arquivo com monitor de contatos ao WebService
define("MONITOR_FILE", 			$raiz_do_projeto . "log/monitor_Incomm_online.txt");

// Classes do m�dulo Incomm
include_once("classIncomm.php");
include_once("classVerificaServicoOnline.php");
include_once("classGeraisServicoOnline.php");
include_once("classGeraisServicoRequisicao.php");

?>