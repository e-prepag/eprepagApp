<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
@header ('Content-type: text/html; charset=ISO-8859-1'); 
//@header ('Content-type: text/html; charset=utf-8'); 
require_once '../../../includes/functions.php';
require_once "/www/includes/load_dotenv.php";

//Dados bancários
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

// PIX ERRO na Comunicação
define("PIX_ERRO",	"ERRO NA COMUNICACAO"); 

// Tipo de Método de Requisição
define("PIX_REGISTER","PUT");
define("PIX_SONDA","GET");

// Resposta Sanda de PIX pago com sucesso
define("PIX_SONDA_PAGO_OK","CONCLUIDA");

if(checkIP()) {
    // Ambiente DEV / HOMOLOGAÇÃO
    define("CLIENT_ID", getenv('CLIENT_ID'));
    define("CLIENT_SECRET", getenv('CLIENT_SECRET_HML'));
    define("PIX_SERVICE_URL_AUTH", getenv('PIX_SERVICE_URL_AUTH_HML'));
    define("PIX_SERVICE_URL_SERVICE", getenv('PIX_SERVICE_URL_SERVICE_HML'));
    define("PIX_SERVICE_URL_SONDA", getenv('PIX_SERVICE_URL_SONDA_HML'));
} else {
    // Ambiente PRODUÇÃO
    define("CLIENT_ID", getenv('CLIENT_ID'));
    define("CLIENT_SECRET", getenv('CLIENT_SECRET_PROD'));
    define("PIX_SERVICE_URL_AUTH", getenv('PIX_SERVICE_URL_AUTH_PROD'));
    define("PIX_SERVICE_URL_SERVICE", getenv('PIX_SERVICE_URL_SERVICE_PROD'));
    define("PIX_SERVICE_URL_SONDA", getenv('PIX_SERVICE_URL_SONDA_PROD'));
}

//Timeout da requisição SOAP
define("PIX_TIMEOUT",		"90000"); 

// Arquivo de Log onde serao registrados todos os erros gerados  
define("PIX_ERROR_LOG_FILE", RAIZ_DO_PROJETO . "arquivos_gerados/logs/log_PIX_WS-Errors.log");

// Arquivo de Log onde serao registrados todos os cabecalhos de Request/Response
define("LOG_FILE_PIX_WS_ERRORS",RAIZ_DO_PROJETO . "arquivos_gerados/logs/log_PIX_WS-Hearders.log");

//Código de Sucesso da Transação
$PIX_CODE_SUCESS = array(
                                '200' //Sucesso
                        );

//Código de ERRO da Transação
$PIX_CODE_ERROR = array(
                                '401', //Provavel erro por conta de paramentro fora do formato 
                                '406'  //Payload inválido ou violado - Neste caso verificar o formato do JSON, desde acentuação até sintaxe.
                        );

//Esse ID é concatenado no inicio de cada id da operação('id_venda' => 'id_op') para diferenciar o tipo de venda que foi feito
$ARRAY_CONCATENA_ID_VENDA = array(
                                        'gamer'          => '10',
                                        'pdv'            => '20',
                                        'cards'          => '30',
                                        'boleto_express' => '40'
                                    );

// Classes do módulo PIX
require_once("classPIX.php");
require_once("classJSONEstruturaPIX.php");

?>