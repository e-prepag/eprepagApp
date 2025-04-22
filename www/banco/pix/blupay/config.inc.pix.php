<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
@header ('Content-type: text/html; charset=ISO-8859-1'); 
//@header ('Content-type: text/html; charset=utf-8'); 

//Dados banc�rios
define("PIX_BANK","");
define("PIX_AGENCIA",	""); 
define("PIX_CONTA","");
define("PIX_CHAVE",""); 

//Dados de Acesso
define("GRANT_TYPE","client_credentials");

//Dados da Empresa
define("CIDADE_EMPRESA","Sao Paulo");
define("RAZAO_EMPRESA","E-PREPAG ADMINISTRADORA DE CARTOES LTDA");

// PIX Action Name Transaction
define("PIX_JSON_REQUISICAO",	"request"); 

// PIX ERRO na Comunica��o
define("PIX_ERRO",	"ERRO NA COMUNICACAO"); 

// Tipo de M�todo de Requisi��o
define("PIX_REGISTER","POST"); //PUT
define("PIX_SONDA","GET");

// Resposta Sanda de PIX pago com sucesso
define("PIX_SONDA_PAGO_OK","CONCLUIDA");

/*if(checkIP() ) {
        // =============> Ambiente DEV / HOMOLOGA��O
        //Dados de Acesso
        define("CLIENT_ID","");
        define("CLIENT_SECRET","");
        //URL Transaction
        define("PIX_SERVICE_URL_AUTH","");
        define("PIX_SERVICE_URL_SERVICE","");
        define("PIX_SERVICE_URL_SONDA",""); ///api/v2/cob/{txId},  txId � ID do Pedido
        
          O txid � de uso opcional, caso o parceiro deseje utilizar uma identifica��o
        pr�pria. O mesmo deve possuir entre 25 e 35 caracteres, podendo ser
        alfanum�ricos, devendo ser �nico para cada solicita��o.
        O n�o envio do mesmo acarretara um txid rand�mico gerado pelo sistema.
         
} 
else {
        // =============> Ambiente PRODU��O
        //Dados de Acesso
        define("CLIENT_ID","");
        define("CLIENT_SECRET","");
        //URL Transaction
        define("PIX_SERVICE_URL_AUTH","");
        define("PIX_SERVICE_URL_SERVICE","");
        define("PIX_SERVICE_URL_SONDA",""); ///api/v2/cob/{txId},  txId � ID do Pedido
        
          O txid � de uso opcional, caso o parceiro deseje utilizar uma identifica��o
        pr�pria. O mesmo deve possuir entre 25 e 35 caracteres, podendo ser
        alfanum�ricos, devendo ser �nico para cada solicita��o.
        O n�o envio do mesmo acarretara um txid rand�mico gerado pelo sistema.
         
} */

//Timeout da requisi��o SOAP
define("PIX_TIMEOUT",		"90000"); 

// Arquivo de Log onde serao registrados todos os erros gerados  
define("PIX_ERROR_LOG_FILE", "/www/log/log_PIX_WS-Errors.log");

// Arquivo de Log onde serao registrados todos os cabecalhos de Request/Response
define("LOG_FILE_PIX_WS_ERRORS", "/www/log/log_PIX_WS-Hearders.log");

//C�digo de Sucesso da Transa��o
$PIX_CODE_SUCESS = array(
                                '200' //Sucesso
                        );

//C�digo de ERRO da Transa��o
$PIX_CODE_ERROR = array(
                                '401', //Provavel erro por conta de paramentro fora do formato 
                                '406'  //Payload inv�lido ou violado - Neste caso verificar o formato do JSON, desde acentua��o at� sintaxe.
                        );

//Esse ID � concatenado no inicio de cada id da opera��o('id_venda' => 'id_op') para diferenciar o tipo de venda que foi feito
$ARRAY_CONCATENA_ID_VENDA = array(
                                        'gamer'          => '10',
                                        'pdv'            => '20',
                                        'cards'          => '30',
                                        'boleto_express' => '40'
                                    );

// Classes do m�dulo PIX
require_once("classPIX.php");
require_once("classJSONEstruturaPIX.php");

?>