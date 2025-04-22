<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
@header ('Content-type: text/html; charset=ISO-8859-1'); 

// Constante definindo o IP do cliente
define("BHN_CLIENT_IP_ADDR","");

// Identificadores de Consulta
define("BHN_SIGNATURE",	"BHNUMS"); //FIXO BHNUMS
define("PRODUCTCATEGORYCODE","01"); // 01- Gift Card
define("SPECVERSION","43"); // 4.3 Vers�o da integra��o
define("ACQUIRINGINSTITUTIONIDENTIFIER","60300004779"); //Pegar um valido da BHN para Prod ************************************** ===> ID Teste 60300003658 
define("MERCHANTCATEGORYCODE","5399"); //Fixo de acordo com ISO 18254 => MCC CODES 5399 Miscellaneous General Merchandise Stores V, M 
define("MERCHANTIDENTIFIER","60300004779    "); //Pegar um valido da BHN para Prod ******************************************** ==> ID Teste 60300003658
define("MERCHANTLOCATION","Sao Paulo SP BR"); //Definir o da E-Prepag *********************************
define("MERCHANTTERMINALID","     564   "); //Definir um c�digo para o PDV da E-prepag *****************************************
define("POINTOFSERVICEENTRYMODE","041"); //Fixo de acordo com o manual 041 (Online) 
define("PRIMARYACCOUNTNUMBER","6039534201000000024"); //Pegar um valido da BHN para Prod ************************************* ===> Card No ir� ser unico idependente do produto  ESTE USADO NOS TESTES => 6039534201000000024
define("PROCESSINGCODE","745400"); //Fixo 745400 => Digital Account
define("TRANSACTIONCURRENCYCODE","986"); //Fixo de acordo com ISO 4217 => <CtryNm>BRAZIL</CtryNm><CcyNm>Brazilian Real</CcyNm><Ccy>BRL</Ccy><CcyNbr>986</CcyNbr><CcyMnrUnts>2</CcyMnrUnts>

//Denfinindo o diretorio do certificado utilizado (utilizando o certificado da Cielo
define("ENDERECO_BASE_CERTIFICADO_BHN", $raiz_do_projeto . "/bhn/ssl");

// BHN SOAP Action Name Transaction
define("BHN_XML_REQUISICAO",	"request"); 

if(checkIP() ) {
        // =============> Ambiente DEV / HOMOLOGA��O
        //URL Transaction
        define("BHN_SERVICE_URL_TRANSACTION","https://blast.preprod.blackhawk-net.com:8443/transactionManagement/v2/transaction");
        //URL Reverse/ Desfazimento
        define("BHN_SERVICE_URL_REVERSE","https://blast.preprod.blackhawk-net.com:8443/transactionManagement/v2/transaction/reverse");
}
else {
        // =============> Ambiente PRODU��O

        // Servidores Santa Clara  
        //URL Transaction
        define("BHN_SERVICE_URL_TRANSACTION","https://webpos.blackhawk-net.com:8443/transactionManagement/v2/transaction");
        //URL Reverse/ Desfazimento
        define("BHN_SERVICE_URL_REVERSE","https://webpos.blackhawk-net.com:8443/transactionManagement/v2/transaction/reverse");

        // Servidores NEW Dallas  
        //URL Transaction
//        define("BHN_SERVICE_URL_TRANSACTION","https://blastapp.blackhawk-net.com:8443/transactionManagement/v2/transaction");
        //URL Reverse/ Desfazimento
//        define("BHN_SERVICE_URL_REVERSE","https://blastapp.blackhawk-net.com:8443/transactionManagement/v2/transaction/reverse");
}

//Nome Parceiro
define("BHN_PARTNER_NAME",	"Black Hawk Network"); 

//Timeout da requisi��o SOAP
define("BHN_TIMEOUT",		"90000"); 

// Tipo de Mensagem do Sistema
define("BHN_MSG_ERROR_LOG",		"ERROR_LOG");
define("BHN_MSG_TRANSACTION_LOG",	"TRANSACTION_LOG");

// mensagens para usu�rio
define("BHN_MSG_USER_PARSING_WSDL",	"Este c�digo de servi�o n�o foi identificado (ERRO: WS547).<br>Por favor, verifique se o servi�o foi selecionado corretamente ou entre em contato com o <a href='mailto:suporte@e-prepag.com.br'>suporte@e-prepag.com.br</a><br>");

// Arquivo de Log onde serao registrados todos os erros gerados  
define("LOG_FILE_BHN_WS_ERRORS",	$raiz_do_projeto . "/log/log_BHN_WS-Errors.log");

// Arquivo de Log onde serao registrados todos os cabecalhos de Request/Response
define("LOG_FILE_BHN_WS_TRANSACTIONS",	$raiz_do_projeto . "/log/log_BHN_WS-Transactions.log");

//C�digo de Sucesso da Transa��o
$BHN_CODE_SUCESS = array(
                                '00', //Approved ? balance available
                                '01', //Approved ? balance unavailable
                                '02'  //Approved ? balance unavailable on external account number 
                        );

//C�digo de Sucesso da Transa��o
$BHN_CODE_REVERSAL = array(
                                '15', //Time Out occurred- Auth Server not available /responding 
                                '74', //Unable to route / System Error 
                                '98', //Erro n�o catalogado
                                '99'  //TimeOut
                        );

//C�digo que n�o permite a Recria��o do pedido
$BHN_CODE_NO_CREATE = array(
                                '99'  //TimeOut
                        );

//Identificador de transa��o que sofreu Desfazimento(Reversal)
define("BHN_MSG_REVERSAL", "Reversal");

//N�mero de tentativas de recria��o de recria��o para pedidos BHN
define("BHN_ATTEMPTS_NUMBER", "5");

//Email que ser� utilizado no alerta de alcan�ar o n�mero m�ximo de recria��es autom�ticas
define("BHN_EMAIL_TO", "wagner@e-prepag.com.br");

//Email de c�pia que ser� utilizado no alerta de alcan�ar o n�mero m�ximo de recria��es autom�ticas
define("BHN_EMAIL_CC", "glaucia@e-prepag.com.br");

//Email de c�pia oculta que ser� utilizado no alerta de alcan�ar o n�mero m�ximo de recria��es autom�ticas
define("BHN_EMAIL_BCC", "wagner@e-prepag.com.br");

// Classes do m�dulo BHN
include_once("classGerais.php");
include_once("classXMLEstruturaBHN.php");
include_once("classBHN.php");

/*
 * ALL CODES RESPONSE BHN
 * 
 * 
 * 
 */

?>