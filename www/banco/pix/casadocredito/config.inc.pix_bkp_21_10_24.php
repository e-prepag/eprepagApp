<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
@header ('Content-type: text/html; charset=ISO-8859-1'); 
//@header ('Content-type: text/html; charset=utf-8'); 

//Dados banc�rios
define("PIX_BANK","159");
define("PIX_AGENCIA",	"001"); 
define("PIX_CONTA","12955612");
define("PIX_CHAVE","19037276000172"); 

//Dados de Acesso
define("CLIENT_ID","geracaoqr");
define("CLIENT_SECRET","33er249d!odo4ff");
define("GRANT_TYPE","client_credentials");
define("SCOPE","geral");

//Dados da Empresa
define("CIDADE_EMPRESA","Sao Paulo");
define("RAZAO_EMPRESA","E-PREPAG ADMINISTRADORA DE CARTOES LTDA");

// PIX Action Name Transaction
define("PIX_JSON_REQUISICAO",	"request"); 

// PIX Autentica��o e gera��o de Token
define("PIX_RESQUEST_TOKEN",	"seguranca/connect/token"); 

// PIX Gerador de QRCode
define("PIX_QRCODE",	"QRCode/gerarEstatico"); 

// PIX ERRO na Comunica��o
define("PIX_ERRO",	"ERRO NA COMUNICACAO"); 

if(checkIP() ) {
        // =============> Ambiente DEV / HOMOLOGA��O
        //URL Transaction
        define("PIX_SERVICE_URL","https://contause.digital/s/oauth/");
}
else {
        // =============> Ambiente PRODU��O
        //URL Transaction
        define("PIX_SERVICE_URL","https://contause.digital/s/oauth/");
}

//Timeout da requisi��o SOAP
define("PIX_TIMEOUT",		"90000"); 

// Tipo de Mensagem do Sistema
define("PIX_MSG_ERROR_LOG",		"ERROR_LOG");
define("PIX_MSG_TRANSACTION_LOG",	"TRANSACTION_LOG");

// Arquivo de Log onde serao registrados todos os erros gerados  
define("PIX_ERROR_LOG_FILE", RAIZ_DO_PROJETO . "log/log_PIX_WS-Errors.log");

// Arquivo de Log onde serao registrados todos os cabecalhos de Request/Response
define("LOG_FILE_PIX_WS_ERRORS",RAIZ_DO_PROJETO . "log/log_PIX_WS-Hearders.log");

//C�digo de Sucesso da Transa��o
$PIX_CODE_SUCESS = array(
                                '200' //Sucesso
                        );

//C�digo de ERRO da Transa��o
$PIX_CODE_ERROR = array(
                                '401', //Provavel erro por conta de paramentro fora do formato 
                                '406'  //Payload inv�lido ou violado - Neste caso verificar o formato do JSON, desde acentua��o at� sintaxe.
                        );

// Classes do m�dulo PIX
require_once("classPIX.php");
require_once("classJSONEstruturaPIX.php");
require_once("classAuthentication.php");

?>