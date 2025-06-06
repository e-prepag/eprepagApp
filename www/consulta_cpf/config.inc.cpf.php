<?php require_once __DIR__ . '/../includes/constantes_url.php'; ?>
<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
require_once RAIZ_DO_PROJETO . 'includes/configIP.php';
if(checkIP()) {
    $server_url = $_SERVER['SERVER_NAME'];
}
else {
    $server_url = "" . EPREPAG_URL . "";
}

// Constante a quantidade limite para aceitação do mesmo CPF
define("CPF_QUANTIDADE_LIMITE",	50);
    
// Constante a quantidade limite para numero de contas com o mesmo CPF
define("CPF_QUANTIDADE_CONTAS",	1); // 2
    
// Constante contendo a identificação do Parceiro Credify
define("CPF_PARTNER_CREDIFY",	1);
    
// Constante contendo a identificação do Parceiro OMNIDATA
define("CPF_PARTNER_OMNIDATA",	2);
    
// Constante contendo a identificação do Consulta CACHE
define("CPF_CONSULTA_CACHE",	3);

// Constante contendo a identificação do Hub do desenvolvedor
define("CPF_CONSULTA_HUB",	4);

define("CPF_PARTNER_CAF",	5);

//Definindo Vetor Legenda
$vetorLegenda = array(
                        CPF_PARTNER_CREDIFY => 'CREDIFY',
                        CPF_PARTNER_OMNIDATA => 'OMNIDATA',
                        CPF_CONSULTA_CACHE => 'Nosso CACHE',
						CPF_CONSULTA_HUB => 'Hub do Densenvolvedor',
						CPF_PARTNER_CAF => 'CAF'
);

//Definindo Vetor Reverso
$vetorReverso = array(
                        CPF_PARTNER_CREDIFY => 'CPF_PARTNER_CREDIFY',
                        CPF_PARTNER_OMNIDATA => 'CPF_PARTNER_OMNIDATA',
                        CPF_CONSULTA_CACHE => 'CPF_CONSULTA_CACHE',
						CPF_CONSULTA_HUB => 'CPF_CONSULTA_HUB',
						CPF_PARTNER_CAF => 'CPF_PARTNER_CAF'
);

require_once RAIZ_DO_PROJETO . "consulta_cpf/environment.cpf.php";

//Definindo valor Default no caso do include estar corrompido
if(!defined('CPF_PARTNER_ENVIRONMET')) {
    // Constante que define o Parceiro de Integração. Onde (CREDIFY = 1) ou (OMNIDATA = 2) ou (Consulta CACHE = 3)
    define("CPF_PARTNER_ENVIRONMET",CPF_PARTNER_OMNIDATA);
}// end if(empty(CPF_PARTNER_ENVIRONMET))

// Constante definindo o IP do cliente
define("CPF_CLIENT_IP_ADDR",	"");

// Identificadores de Login
define("CPF_CLIENT_ID_OMNIDATA","wagner");
define("CPF_CLIENT_ID_CREDIFY",	"6384");

// Identificadores de Login
define("CPF_CLIENT_PASSWORD_OMNIDATA",	"wgep7589");
define("CPF_CLIENT_PASSWORD_CREDIFY",	"58122240");

// Identificadores de Consulta
define("CPF_ID_CONSULT_OMNIDATA",	"RECEITA_PF");
define("CPF_ID_CONSULT_CREDIFY",	"216");

// CPF SOAP Action Name
define("CPF_XML_REQUISICAO_OMNIDATA",	"pesquisarUnica"); 
define("CPF_XML_REQUISICAO_CREDIFY",	"Consultar"); 

//OMNIDATA
define("CPF_SERVICE_URL_OMNIDATA",	"http://eprepag.wim.omninetworking.com.br/wim-http/WIMWS");
define("CPF_WSDL_URL_OMNIDATA",	"http://eprepag.wim.omninetworking.com.br/wim-http/WIMWS?wsdl");

//Credify
define("CPF_SERVICE_URL_CREDIFY",	"http://webservice.credify.com.br/wscredify.php");
define("CPF_WSDL_URL_CREDIFY",	"http://webservice.credify.com.br/wscredify.php?wsdl");

// URLS
if(CPF_PARTNER_ENVIRONMET == CPF_PARTNER_CREDIFY) {
	define("CPF_SERVICE_URL",	CPF_SERVICE_URL_CREDIFY);
	define("CPF_WSDL_URL",		CPF_WSDL_URL_CREDIFY);
	define("CPF_CLIENT_ID",		CPF_CLIENT_ID_CREDIFY);
	define("CPF_CLIENT_PASSWORD",	CPF_CLIENT_PASSWORD_CREDIFY);
	define("CPF_ID_CONSULT",	CPF_ID_CONSULT_CREDIFY);
	define("CPF_XML_REQUISICAO",	CPF_XML_REQUISICAO_CREDIFY); 
	define("CPF_PARTNER_NAME",	"CREDIFY"); 
	define("CPF_TIMEOUT",		"60000"); 
        // CPF Dado do Tipo de Pessoa
        define("CPF_TIPO_PESSOA_FISICA","F");
} elseif(CPF_PARTNER_ENVIRONMET == CPF_PARTNER_OMNIDATA) {
	define("CPF_SERVICE_URL",	CPF_SERVICE_URL_OMNIDATA);
	define("CPF_WSDL_URL",		CPF_WSDL_URL_OMNIDATA);
	define("CPF_CLIENT_ID",		CPF_CLIENT_ID_OMNIDATA);
	define("CPF_CLIENT_PASSWORD",	CPF_CLIENT_PASSWORD_OMNIDATA);
	define("CPF_ID_CONSULT",	CPF_ID_CONSULT_OMNIDATA);
	define("CPF_XML_REQUISICAO",	CPF_XML_REQUISICAO_OMNIDATA); 
	define("CPF_PARTNER_NAME",	"OMNIDATA"); 
	define("CPF_TIMEOUT",		"120000"); 
	if(!defined('CPF_VALIDADE')) define("CPF_VALIDADE",		"30");
	define("CPF_NOME_CAMPO",	"cpf"); 
	define("DATA_NASC_NOME_CAMPO",	"data_nascimento"); 
} elseif(CPF_PARTNER_ENVIRONMET == CPF_CONSULTA_CACHE) {
	define("CPF_PARTNER_NAME",	"CONSULTA_CACHE"); 
	define("CPF_TIMEOUT",		"60000"); 
}else{
	define("CPF_PARTNER_NAME",	"HUBDESENVOLVEDOR"); 
	define("CPF_TIMEOUT",		"60000"); 
}

// CPF Dado da Situação do CPF Regular junto a Receita
define("CPF_SITUCAO_REGULAR",			"REGULAR");

// ok hub do desenvolvedor
define("CPF_SITUCAO_REGULAR_HUB",			"OK");

// Tipo de Mensagem do Sistema
define("CPF_MSG_ERROR_LOG",			"ERROR_LOG");
define("CPF_MSG_TRANSACTION_LOG",		"TRANSACTION_LOG");

// mensagens para usuário
define("CPF_MSG_USER_PARSING_WSDL",		"Este código de serviço não foi identificado (ERRO: WS758).<br>Por favor, verifique se o serviço foi selecionado corretamente ou entre em contato com o <a href='mailto:suporte@e-prepag.com.br'>suporte@e-prepag.com.br</a><br>");

// Arquivo de Log onde serao registrados todos os erros gerados  
define("LOG_FILE_CPF_WS_ERRORS",		RAIZ_DO_PROJETO . "log/log_CPF_WS-Errors.log");

// Arquivo de Log onde serao registrados todos os cabecalhos de Request/Response
define("LOG_FILE_CPF_WS_TRANSACTIONS",		RAIZ_DO_PROJETO . "log/log_CPF_WS-Transactions.log");

// Arquivo com monitor de contatos ao WebService
define("CPF_MONITOR_FILE", 			RAIZ_DO_PROJETO . "log/monitor_CPF_online.txt");

// Classes do módulo CPF
include_once("classGerais.php");
include_once("classVerificaCPF.php");
include_once("classCPF.php");

?>