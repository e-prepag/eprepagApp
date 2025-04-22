<?php
set_time_limit(90);

//Email cadastrado no gerenciador Bradesco
define("EMAIL_AUTENTICACAO_BRADESCO", "wagner@e-prepag.com.br");

//Cуdigo para o meio de pagamento boleto
define("BRADESCO_MEIO_PAGAMENTO_BOLETO", "300");

//Cуdigo para o meio de pagamento transferкncia entre contas Bradesco
define("BRADESCO_MEIO_PAGAMENTO_TRANSFERENCIA", "800");

//Timeout da requisiзгo SOAP
define("BRADESCO_TIMEOUT", "90000");

define("BRADESCO_XML_REQUISICAO",   "request");

if(checkIP()) {
        
    //URL Homologaзгo/testes 
    define("URL_ACESSO_BRADESCO", "https://homolog.meiosdepagamentobradesco.com.br/");
    
    //Credenciais para EPP Pagamentos
    //define("BRADESCO_CHAVE_SEGURANCA","a9swOV7J8krabDX5ytCHXilKyLDqvOFXlxsk5G7NZmo");
    
    //Credenciais para EPP Administradora
    define("BRADESCO_CHAVE_SEGURANCA","FdPE13g3b969dlIANROc190EYXi8ydXEjXdr7ZEDCk4");
    
}
else { 
    //URL Producao
    define("URL_ACESSO_BRADESCO", "https://meiosdepagamentobradesco.com.br/");
    
    //Credenciais para EPP Pagamentos
    //define("BRADESCO_CHAVE_SEGURANCA","8hUmFRplkO9BkG8EcDSizFLFg7J2bWU5gwG40fHQ9qk");
    
    //Credenciais para EPP Administradora
    //define("BRADESCO_CHAVE_SEGURANCA","qtc9Mf8Bq8JkMkjaHsRgCtndow9vf1RkH4wr69IAmoU");
    define("BRADESCO_CHAVE_SEGURANCA","5I2mFPvN38NdilJj9IlXzGMsImigqec8I1DIfmTXRzI");
    
}

define("BRADESCO_URL_HOMOLOGACAO", URL_ACESSO_BRADESCO."apiregistro/api");

define("BRADESCO_URL_TRASFERENCIAS", URL_ACESSO_BRADESCO."transf/transacao");

//Array com os possнveis erros que levam a geraзгo de um novo token
$ARRAY_ERROR_TO_NEW_TOKEN = array('-201','-205','-206','-208');

//Credenciais para EPP Pagamentos
//define("BRADESCO_MERCHANTID", "004552539");

//Credenciais para EPP Administradora
define("BRADESCO_MERCHANTID", "100004675");

?>