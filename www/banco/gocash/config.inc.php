<?php

// Live/Test
define("GC_LIVE_ENVIRONMET", 1);

// Constantes
define("GC_CLIENT_IP_ADDR", "189.38.238.205");

// Login ID
define("GC_CLIENT_ID_TEST", "EprepT8007");
define("GC_CLIENT_ID_LIVE", "Eprep8007");

// test 
define("GC_SERVICE_URL_TEST", "http://service.gocashgamecard.com:8081/GoCashCardService.asmx");
define("GC_WSDL_URL_TEST", "http://service.gocashgamecard.com:8081/GoCashCardService.asmx?WSDL");

// live
//define("GC_SERVICE_URL_LIVE", "https://applive.gocashgamecard.com:8001/GoCashCardService.asmx");
define("GC_SERVICE_URL_LIVE", "https://bill.g10vms.com:8444/GoCashWithCurrency.asmx"); // Teste do Joseph


//define("GC_WSDL_URL_LIVE", "https://applive.gocashgamecard.com:8001/GoCashCardService.asmx?WSDL");
define("GC_WSDL_URL_LIVE", "https://bill.g10vms.com:8444/GoCashWithCurrency.asmx?WSDL"); // Teste do Joseph

// URLS
if(GC_LIVE_ENVIRONMET==1) {
	define("GC_SERVICE_URL", GC_SERVICE_URL_LIVE);
	define("GC_WSDL_URL", GC_WSDL_URL_LIVE);
	define("GC_CLIENT_ID", GC_CLIENT_ID_LIVE);
} else {
	define("GC_SERVICE_URL", GC_SERVICE_URL_TEST);
	define("GC_WSDL_URL", GC_WSDL_URL_TEST);
	define("GC_CLIENT_ID", GC_CLIENT_ID_TEST);
}

// GoCash SOAP Action Name
define('GC_ACTION_SERIAL_CHECK', 'SerialCheck');
define('GC_ACTION_PAY_CASH', 'PayCash');
define('GC_ACTION_PAY_CASH_CHECK', 'PayCashCheck');

// Tipos de Moedas
define("GC_CURRENCY_BRL", "BRL");
define("GC_CURRENCY_USD", "USD");

// Tipo de Mensagem do Sistema
define('GC_MSG_ERROR_LOG', 'ERROR_LOG');
define('GC_MSG_TRANSACTION_LOG', 'TRANSACTION_LOG');

// mensagens para usuário
define('GC_MSG_USER_PARSING_WSDL', 'Este PIN não foi identificado (AWS23).<br>Por favor, verifique se o código digitado está correto ou entre em contato com o <a href="mailto:suporte@e-prepag.com.br">suporte@e-prepag.com.br</a><br>');


// Arquivo de Log onde serao registrados todos os erros gerados  
define("LOG_FILE_GOCASH_WS_ERRORS", $raiz_do_projeto . "log/log_GoCashWS-Errors.log");

// Arquivo de Log onde serao registrados todos os cabecalhos de Request/Response
define("LOG_FILE_GOCASH_WS_TRANSACTIONS", $raiz_do_projeto . "log/log_GoCashWS-Transactions.log");

// Arquivo com monitor de contatos ao WebService
define("GC_MONITOR_FILE", $raiz_do_projeto . "log/monitor_gocash_online.txt");

require_once $raiz_do_projeto . "banco/gocash/includes/GoCash_functions.php";

require_once $raiz_do_projeto . "banco/gocash/includes/classGoCash.php";
require_once $raiz_do_projeto . "banco/gocash/includes/classSerialCheck.php";
require_once $raiz_do_projeto . "banco/gocash/includes/classPayCashCheck.php";

require_once $raiz_do_projeto . "banco/gocash/includes/classPayCash.php";
require_once $raiz_do_projeto . "banco/gocash/includes/classReturnValue.php";
require_once $raiz_do_projeto . "banco/gocash/includes/function.files.inc.php";

?>