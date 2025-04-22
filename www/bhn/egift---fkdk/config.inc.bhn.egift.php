<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

define("ENDERECO_BASE_CERTIFICADO_BHN_EGIFT", $raiz_do_projeto . "/bhn/egift/ssl");

define("BHN_EGIFT_PRODUCTCATALOGS",   "productCatalog");

define("BHN_EGIFT_PRODUCT",   "product");

define("BHN_EGIFT_GET_ACCOUNT",   "getAccount");

if(checkIP()) {
        // =============> Ambiente DEV / HOMOLOGAÇÃO

        //URL Desenvolvimento 
        //define("BHN_EGIFT_URL_PREFIX_RESPONSE",     "https://sandbox.blackhawknetwork.com/");
        //define("BHN_EGIFT_URL_PREFIX",              "https://api.sandbox.blackhawknetwork.com");

        //URL Pré Produção
        define("BHN_EGIFT_URL_PREFIX_RESPONSE",     "https://certification.blackhawknetwork.com/");
        define("BHN_EGIFT_URL_PREFIX",              "https://api.certification.blackhawknetwork.com");
        define("BHN_EGIFT_URL_QUERY_PRODUCT_CATALOGS",  BHN_EGIFT_URL_PREFIX."/productCatalogManagement/v1/productCatalogs");
        define("BHN_EGIFT_URL_READ_PRODUCT_CATALOG",    BHN_EGIFT_URL_PREFIX."/productCatalogManagement/v1/productCatalog/");
        define("BHN_EGIFT_URL_READ_PRODUCT",            BHN_EGIFT_URL_PREFIX."/productManagement/v1/product/");
        define("BHN_EGIFT_URL_GENERATE_EGIFT",          BHN_EGIFT_URL_PREFIX."/eGiftProcessing/v1/generateEGift");
        define("BHN_EGIFT_URL_READ_EGIFT",              BHN_EGIFT_URL_PREFIX."/accountProcessing/v1/readAccount");
        define("BHN_EGIFT_URL_REVERSE_EGIFT",           BHN_EGIFT_URL_PREFIX."/eGiftProcessing/v1/reverseEGift");

        // Desenvolvimento 
        //define("BHN_EGIFT_REQUESTORID",   "CLMMVC5PQRRYHGZCG6LX47Z6T8");

         //Pré Produção
        //define("BHN_EGIFT_REQUESTORID",   "DCAXGG78W5S99WY3CZS6032BQ8"); // E-PREPAG
        //define("BHN_EGIFT_REQUESTORID",   "R698MM86JGS0W097KCSD79F4DR"); // BHN
        //define("BHN_EGIFT_REQUESTORID",   "GJJ74VLVTDQQRTWH1YWNNZ0B34"); // E-PREPAG eGIFT
        define("BHN_EGIFT_REQUESTORID",   "ZHYMPVWF7DT5TC2TP3HH2DGFY0"); // E-PREPAG eGIFT (NOVO)
        
        //Desenvolvimento
        //define("CERTIFICADO_BHN_EGIFT_CURLOPT_SSLCERT",     ENDERECO_BASE_CERTIFICADO_BHN_EGIFT ."/cert.crt.pem");
        //define("CERTIFICADO_BHN_EGIFT_CURLOPT_SSLKEY",      ENDERECO_BASE_CERTIFICADO_BHN_EGIFT .'/cert.key.pem');

         //Pré Produção
        //define("CERTIFICADO_BHN_EGIFT_CURLOPT_SSLCERT",     ENDERECO_BASE_CERTIFICADO_BHN_EGIFT ."/Eprepag-Brasil-API-CertificationService-GW.crt.pem");  // E-PREPAG
        //define("CERTIFICADO_BHN_EGIFT_CURLOPT_SSLKEY",      ENDERECO_BASE_CERTIFICADO_BHN_EGIFT .'/Eprepag-Brasil-API-CertificationService-GW.key.pem');  // E-PREPAG

        //define("CERTIFICADO_BHN_EGIFT_CURLOPT_SSLCERT",     ENDERECO_BASE_CERTIFICADO_BHN_EGIFT ."/BHN-DIGITAL-OPS-BRASIL-API-CertificationService-GW.crt.pem");    // BHN
        //define("CERTIFICADO_BHN_EGIFT_CURLOPT_SSLKEY",      ENDERECO_BASE_CERTIFICADO_BHN_EGIFT .'/BHN-DIGITAL-OPS-BRASIL-API-CertificationService-GW.key.pem');    // BHN

        //define("CERTIFICADO_BHN_EGIFT_CURLOPT_SSLCERT",     ENDERECO_BASE_CERTIFICADO_BHN_EGIFT ."/Eprepag_eGift_Brasil-API-CertificationService-GW.crt.pem");  // E-PREPAG eGIFT
        //define("CERTIFICADO_BHN_EGIFT_CURLOPT_SSLKEY",      ENDERECO_BASE_CERTIFICADO_BHN_EGIFT .'/Eprepag_eGift_Brasil-API-CertificationService-GW.key.pem');  // E-PREPAG eGIFT

        define("CERTIFICADO_BHN_EGIFT_CURLOPT_SSLCERT",     ENDERECO_BASE_CERTIFICADO_BHN_EGIFT ."/Eprepag-Digital-API-CertificationService-GW.crt.pem");  // E-PREPAG eGIFT (NOVO)
        define("CERTIFICADO_BHN_EGIFT_CURLOPT_SSLKEY",      ENDERECO_BASE_CERTIFICADO_BHN_EGIFT .'/Eprepag-Digital-API-CertificationService-GW.key.pem');  // E-PREPAG eGIFT (NOVO)

        //Desenvolvimento
        //define("PASSWORD_CURLOPT_SSLCERTPASSWD", "49NHDRYS");              
        //define("PASSWORD_CURLOPT_SSLKEYPASSWD", '49NHDRYS'); 

        //Pré Produção
        //define("PASSWORD_CURLOPT_SSLCERTPASSWD", "0ZVLNQ3VY2GJJHXYH2FD68A3JW");    // E-PREPAG           
        //define("PASSWORD_CURLOPT_SSLKEYPASSWD", "0ZVLNQ3VY2GJJHXYH2FD68A3JW");     // E-PREPAG

        //define("PASSWORD_CURLOPT_SSLCERTPASSWD", "XKG93V4NNHLDQ00AJQVBLYYLJW");    // BHN           
        //define("PASSWORD_CURLOPT_SSLKEYPASSWD", "XKG93V4NNHLDQ00AJQVBLYYLJW");     // BHN

        //define("PASSWORD_CURLOPT_SSLCERTPASSWD", "WDYFCC9HLT17W9LQYY2W30HTBR");    // E-PREPAG eGIFT       
        //define("PASSWORD_CURLOPT_SSLKEYPASSWD", "WDYFCC9HLT17W9LQYY2W30HTBR");     // E-PREPAG eGIFT
        
        define("PASSWORD_CURLOPT_SSLCERTPASSWD", "NYRWB8HJDH9GB4H7LJV5VDK74C");    // E-PREPAG eGIFT (NOVO)        
        define("PASSWORD_CURLOPT_SSLKEYPASSWD", "NYRWB8HJDH9GB4H7LJV5VDK74C");     // E-PREPAG eGIFT (NOVO)
               
}
else { 
        //URL Producao
        define("BHN_EGIFT_URL_PREFIX_RESPONSE",     "https://blackhawknetwork.com/");
        define("BHN_EGIFT_URL_PREFIX",              "https://api.blackhawknetwork.com");
        define("BHN_EGIFT_URL_QUERY_PRODUCT_CATALOGS",  BHN_EGIFT_URL_PREFIX."/productCatalogManagement/v1/productCatalogs");
        define("BHN_EGIFT_URL_READ_PRODUCT_CATALOG",    BHN_EGIFT_URL_PREFIX."/productCatalogManagement/v1/productCatalog/");
        define("BHN_EGIFT_URL_READ_PRODUCT",            BHN_EGIFT_URL_PREFIX."/productManagement/v1/product/");
        define("BHN_EGIFT_URL_GENERATE_EGIFT",          BHN_EGIFT_URL_PREFIX."/eGiftProcessing/v1/generateEGift");
        define("BHN_EGIFT_URL_READ_EGIFT",              BHN_EGIFT_URL_PREFIX."/accountProcessing/v1/readAccount");
        define("BHN_EGIFT_URL_REVERSE_EGIFT",           BHN_EGIFT_URL_PREFIX."/eGiftProcessing/v1/reverseEGift");

        define("BHN_EGIFT_REQUESTORID",   "ZHYMPVWF7DT5TC2TP3HH2DGFY0");
        
        define("CERTIFICADO_BHN_EGIFT_CURLOPT_SSLCERT",     ENDERECO_BASE_CERTIFICADO_BHN_EGIFT ."/Eprepag-Digital-API-Production.crt.pem");
        define("CERTIFICADO_BHN_EGIFT_CURLOPT_SSLKEY",      ENDERECO_BASE_CERTIFICADO_BHN_EGIFT .'/Eprepag-Digital-API-Production.key.pem');
        
        //define("PASSWORD_CURLOPT_SSLCERTPASSWD", "LGXR1FMWQ5C7FQ5PQQ4LTY1F4C");   // para certificado v1           
        //define("PASSWORD_CURLOPT_SSLKEYPASSWD", "LGXR1FMWQ5C7FQ5PQQ4LTY1F4C");    // para certificado v1
        
        define("PASSWORD_CURLOPT_SSLCERTPASSWD", "8V39WP5F8358JL1GQZTLSCRF24");         
        define("PASSWORD_CURLOPT_SSLKEYPASSWD", "8V39WP5F8358JL1GQZTLSCRF24"); 
        
}

        
//Timeout da requisição SOAP
define("BHN_EGIFT_TIMEOUT", "90000");

// Tipo de Mensagem do Sistema
define("BHN_EGIFT_MSG_ERROR_LOG", "ERROR_LOG");
define("BHN_EGIFT_MSG_TRANSACTION_LOG","TRANSACTION_LOG");

// Arquivo de Log onde serao registrados todos os erros gerados  
define("LOG_FILE_BHN_EGIFT_WS_ERRORS",	$raiz_do_projeto . "log/log_BHN_EGIFT_WS-Errors.log");

// Arquivo de Log onde serao registrados todos os cabecalhos de Request/Response
define("LOG_FILE_BHN_EGIFT_WS_TRANSACTIONS",	$raiz_do_projeto . "log/log_BHN_EGIFT_WS-Transactions.log");

		

$BHN_EGIFT_CODE_STATUS_PROTOCOL_HTTP = array (
                            '400' => array (
                                    'egiftprocessing.reversalEGiftRequestId.blank' => "reversalEGiftRequestId is blank."
                                    ),
                            '400' => array (
                                    'egitprocessing.invalid.contract.id' => "The contractId passed in the header is invalid. It should not be more than 11 characters in length."
                                    ),
                            '403' => array (
                                    'query.all.product.catalogs.by.product.unauthorized' => 'Not authorized'
                                    ),
                            '400' => array (
                                    'egiftprocessing.reversalEGiftRequestId.blank' => "reversalEGiftRequestId is blank."
                                    ),
                            '400' => array (
                                    'egitprocessing.invalid.contract.id' => "The contractId passed in the header is invalid. It should not be more than 11 characters in length."
                                    ),
                            '400' => array (
                                    'egiftprocessing.giftAmount.null' => 'The gift amount is missing in the request'
                                    ),
                            '400' => array (
                                    'egiftprocessing.productConfigurationId.null' => 'The product configuration id is missing in the request'
                                    ),
                            '400' => array (
                                    'egiftprocessing.retrievalReferenceNumber.invalid' => 'retrievalReferenceNumber is invalid. It needs to have exactly 12 numeric characters'
                                    ),
                            '400' => array (
                                    'egitprocessing.invalid.contract.id' => 'The contractId passed in the header is invalid. It should not be more than 11 characters in length.'
                                    ),
                            '400' => array (
                                    'egiftprocessing.delayDisplay.invalid' => 'The value of delayDisplay passed in the request is invalid. It should not be more than 5 digits in length.'
                                    ),
                            '404' => array (
                                    'attempt.to.retrieve.nonexistent.entity' => 'Nonexistent entity'
                                    ),
                            '404' => array (
                                    'attempt.to.retrieve.nonexistent.entity' => 'Nonexistent entity'
                                    ),
                            '409' => array (
                                    'egiftprocessing.product.not.found' => "The productConfigurationId is not set up correctly. It does not have a parent product."
                                    ),
                            '409' => array (
                                    'egiftprocessing.account.creation.failed.merchant.data.absent' => "The client is not on-boarded completely. Merchant information is missing."
                                    ),
                            '409' => array (
                                    'egiftprocessing.account.creation.failed.product.not.in.catalog' => "The given productConfigurationId does not belong to any product in the client's product catalog. Client cannot use this productConfigurationId to generate an egift."
                                    ),
                            '409' => array (
                                    'egiftprocessing.account.creation.failed.productline.not.found' => "The productConfigurationId is not set up correctly. It does not have a parent productLine."
                                    ),
                            '409' => array (
                                    'egiftprocessing.account.creation.failed.transaction.cannot.process' => "The transaction could not be processed by the provider"
                                    ),
                            '409' => array (
                                    'egiftprocessing.account.creation.failed.general.decline' => "The transaction was declined by the provider"
                                    ),
                            '409' => array (
                                    'egiftprocessing.account.creation.failed.card.not.found' => "The transaction was not fulfilled since the system could get a card"
                                    ),
                            '409' => array (
                                    'egiftprocessing.account.creation.failed.invalid.merchant' => "The client is not authorized to make this transaction."
                                    ),
                            '409' => array (
                                    'egiftprocessing.account.creation.failed.invalid.transaction' => "The transaction was rejected by the provider indicating it as invalid"
                                    ),
                            '409' => array (
                                    'egiftprocessing.account.creation.failed.invalid.amount' => "The amount passed in the request is not a valid amount for the product. Check baseValueAmount and maxValueAmount of the product to know the amount range"
                                    ),
                            '409' => array (
                                    'egiftprocessing.account.creation.failed.exceeds.transaction.count.limit' => "The transaction was rejected by the provider indicating that the maximum number of transaction limit has reached"
                                    ),
                            '409' => array (
                                    'egiftprocessing.account.creation.failed.invalid.expiration.date' => "The system could not fetch a valid card"
                                    ),
                            '409' => array (
                                    'egiftprocessing.account.creation.failed.transaction.duplicate' => "The transaction was rejected by the provider indicating it as a Duplicate transaction"
                                    ),
                            '409' => array (
                                    'egiftprocessing.account.creation.failed.cannot.route.transaction' => "The provider had an error in completing the transaction."
                                    ),
                            '409' => array (
                                    'egiftprocessing.account.creation.failed.account.inventory.unavailable' => "There was no inventory available in the system to generate an eGift for this productConfigurationId"
                                    ),
                            '409' => array (
                                    'egiftprocessing.account.creation.failed.product.setup.not.complete' => "The product is not set up correctly for creating the eGift."
                                    ),
                            '409' => array (
                                    'egiftprocessing.account.creation.failed.ws.provider.invalid.status.code' => "There was an internal system error in creating a card for the eGift."
                                    ),
                            '409' => array (
                                    'egiftprocessing.account.creation.failed.internal.process.exception' => "There was an internal system error in fetching the necessary data for creating a card for the eGift."
                                    ),
                            '409' => array (
                                    'egiftprocessing.egift.creation.failed' => "There was a system error in creating the egift"
                                    ),
                            '409' => array (
                                    'account.does.not.exist' => "There was no account found for this requestId"
                                    ),
                            '409' => array (
                                    'egiftprocessing.egift.not.found' => "There was no eGift found for this requestId"
                                    ),
                            '409' => array (
                                    'original.transaction.not.found' => "There was no transaction found for this requestId"
                                    ),
                            '409' => array (
                                    'max.reversal.time.elapsed' => "Time allowed for reversal of transaction elapsed."
                                    ),
                            '409' => array (
                                    'egiftprocessing.account.creation.failed.card.not.found' => "Business Exception occurred while performing the operation"
                                    ),
                            '500' => array (
                                    'Internal Server Error' => "There was an internal system error in processing the request"
                                    ),
                            '500' => array (
                                    'Internal Server Error' => "There was an internal system error in processing the request"
                                    ),
                            '504' => array (
                                    'provider.transaction.timeout' => "There was a timeout in processing the request from the provider side"
                                    ),
                            '502' => array (
                                    '-' => "There was a timeout in processing the request"
                                    ),
                            '502' => array (
                                    'provider.transaction.timeout' => "There was a timeout in processing the request from the provider side"
                                    ),
                            '503' => array (
                                    '-' => "The service is temporarily unreachable"
                                    ),
                            '503' => array (
                                    '-' => "The service is temporarily unreachable"
                                    ),
                            '504' => array (
                                    '-' => "There was a timeout in processing the request"
                                    ),
                            'No' => array (
                                    'no have' => "Haven't"
                                    )
                    );


//Código de Sucesso da Transação
$BHN_EGIFT_CODE_SUCESS = array(
                                '00',      //SUCESSO
                        );

//Código que exige Reversal da Transação
$BHN_EGIFT_CODE_REVERSAL = array(
                                '15', //CLOSED 
                                '74', //TEMPORARILY_SUSPENDED 
                                '98'  //Erro não catalogado
                        );

//Arrays conversão Status para Código
$BHN_EGIFT_CODE_STATUS = array(
                                'ACTIVATED'  =>  '00',   
                                'APPROVED'  =>  '00', //Para Reversal
                                'CLOSED'    =>  '15',   
                                'TEMPORARILY_SUSPENDED'  =>  '74', 
                                ''           =>  '98'   //ERRO NÃO CATALOGADO
                        );

//Identificador de transação que sofreu Desfazimento(Reversal)
define("BHN_EGIFT_MSG_REVERSAL", "Reversal");

//Número de tentativas de recriação para pedidos BHN
define("BHN_ATTEMPTS_NUMBER", "5");

//Email que será utilizado no alerta de alcançar o número máximo de recriações automáticas
define("BHN_EMAIL_TO", "wagner@e-prepag.com.br");

//Email de cópia que será utilizado no alerta de alcançar o número máximo de recriações automáticas
define("BHN_EMAIL_CC", "glaucia@e-prepag.com.br");

//Email de cópia oculta que será utilizado no alerta de alcançar o número máximo de recriações automáticas
define("BHN_EMAIL_BCC", "wagner@e-prepag.com.br");

require_once('classAllCatalogs.php');
require_once('classReadProductsCatalogs.php');
require_once('classReadProduct.php');
require_once('classGenerateeGift.php');
require_once('classReadeGift.php');
require_once('classReverseeGift.php');
require_once ($raiz_do_projeto.'/class/classRegistroPinRequest.php');

?>

