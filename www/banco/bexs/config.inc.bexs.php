<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
require_once '../../includes/constantes.php';
set_time_limit(90);
ini_set('max_execution_time', 90); 

// include do arquivo contendo IPs DEV
require_once $raiz_do_projeto . 'includes/configIP.php';

//*******************************************************************************************//
//*******************************************************************************************//
//  EM CASO DE PROBLEMA, VERIFICAR SE O IP NAO SE ALTEROU                                    //
//   - SE ALTEROU, ENVIAR O NOVO IP PARA SER CADASTRADO NA WHITE LIST DO BEXS]               //
//                                                                                           //
//  URL NA QUAL O BEXS RETORNAR� UMA RESPOSTA DO PROCESSAMENTO DO ARQUIVO DE OPERA��ES:      //
//  www.e-prepag.com.br/bexs/notification_bexs/resposta_remessa_operacoes.php                //
//*******************************************************************************************//
//*******************************************************************************************//
if(checkIP()) {
    $server_url = (isset($_SERVER['SERVER_NAME']))?$_SERVER['SERVER_NAME']:"";
    //WSDL e URL para ambiente DEV
    define("BEXS_SERVICE_URL_WSDL",	"http://remessashomologa.bexs.com.br/servicos?wsdl");
    define("BEXS_SERVICE_URL",	"http://remessashomologa.bexs.com.br/servicos");
    
    //Usu�rio e senha para comunica��o com o Webservice SOAP
    define("BEXS_WS_USER_EPREPAG", "eprepag");
    define("BEXS_WS_PASSWD_EPREPAG", "didier123@");
    
    //Usu�rio e senha para comunica��o via sFTP
    define("BEXS_SFTP_USER_EPREPAG", "eprepag");
    define("BEXS_SFTP_PASSWD_EPREPAG", "XAeEMvllncYo");
}
else {
    $server_url = "www.e-prepag.com.br";
    //WSDL e URL para ambiente PROD
    define("BEXS_SERVICE_URL_WSDL",	"http://remessas.bexs.com.br/servicos?wsdl");
    define("BEXS_SERVICE_URL",	"http://remessas.bexs.com.br/servicos");
    
    //Usu�rio e senha para comunica��o com o Webservice SOAP
    define("BEXS_WS_USER_EPREPAG", "eprepag");
    define("BEXS_WS_PASSWD_EPREPAG", "didier123@");
    
    //Usu�rio e senha para comunica��o via sFTP
    define("BEXS_SFTP_USER_EPREPAG", "eprepag");
    define("BEXS_SFTP_PASSWD_EPREPAG", "XAeEMvllncYo");
    
}

//BEXS SOAP 
define("BEXS_XML_REQUISICAO_INFORMACOES_REMESSA", "novaRemessa"); 
define("BEXS_XML_REQUISICAO_OPERACOES_REMESSA", "operacaoRemessa");

//define("BEXS_XML_OPERACOES", "operacoes");

//C�digo da moeda estrangeira da remessa que est� sendo inclu�da
define("BEXS_MOEDA", "220");

//Preencher sempre com esses valores os campos <formame> e <formamn>
define("BEXS_FORMAME","TX");
define("BEXS_FORMAMN","TED");

//Preencher sempre com esse valor o campo <payment_method> - [CPP => Cartao Pre Pago]
define("BEXS_PAYMENT_METHOD", "CPP");

// TIPO de OPERACAO
// 1 => Exporta��o; 2 => Importa��o;
// 3 => Fin. Compra; 4 => Fin. Venda;
// 5 => Ban. Compra; 6 => Ban. Venda;
define("BEXS_TIPO_OP", 4);

// TIPO DE NATUREZA DO DOCUMENTO
define("BEXS_TIPO_NATUREZA_PF", "F");
define("BEXS_TIPO_NATUREZA_PJ", "J");

//Tempo maximo em MINUTOS para resposta do processamento do arquivo de opera��es BEXS
define("BEXS_TEMPO_MAX_RESPOSTA", "120");

//Remessas salvas no banco de dados no intervalo de dias definido
define("BEXS_INT_DIAS_BACKGROUND", "30");

//Intervalo de horas para captura de remessas
define("BEXS_INT_HORAS_CONSIDERADO", "24");

//Limite em d�lar que cada CPF pode comprar por M�S
define("BEXS_LIMITE_DOLAR", 1000.0);

//Coeficiente de aceita��o p/ a diferen�a no valor do d�lar enviado nas informa��es da remessa (web service) e da soma do valor total em d�lar das opera��es enviadas via sFTP
define("BEXS_COEFICIENTE_DOLAR", 0);

//Logs
define("BEXS_MSG_ERROR_LOG", "ERROR_LOG");
define("BEXS_MSG_TRANSACTION_LOG", "TRANSACTION_LOG");

//Caminho para arquivo de operaa��es da remessa
define("PATH_OPERACOES_BEXS", $raiz_do_projeto . "arquivos_gerados/bexs_arquivos_operacoes/");

define("LOG_FILE_BEXS_WS_ERRORS", $raiz_do_projeto."log/log_BEXS_WS-Errors.log");
define("LOG_FILE_BEXS_WS_TRANSACTIONS", $raiz_do_projeto."log/log_BEXS_WS-Transactions.log");

//Emails a serem utilizados quando o ambiente for DEV e quando o ambiente for PROD
define("EMAIL_DEV", "estagiario1@e-prepag.com");
define("EMAILS_PROD", "estagiario1@e-prepag.com, financeiro@e-prepag.com.br, wagner@e-prepag.com.br");

//C�digos para concatenar aos IDs das vendas para o arquivo de opera��es BEXS (somenteNumeros) - diferenciar gamer, pdv, cards, boleto_express 
// 10 => Vendas Gamers
// 20 => Vendas PDV
// 30 => Vendas Cards
// 40 => Vendas Boleto Express

//Esse ID � concatenado no inicio de cada id da opera��o('id_venda' => 'id_op') para diferenciar o tipo de venda que foi feito
$ARRAY_CONCATENA_ID_VENDA = array
                                    (
                                        'gamer'          => '10',
                                        'pdv'            => '20',
                                        'cards'          => '30',
                                        'boleto_express' => '40'
                                    );

$ARRAY_TIPO_VENDA_AUX = array
                            (
                                '10' => 'GAMER',
                                '20' => 'PDV',
                                '30' => 'CARDS',
                                '40' => 'BOLETO EXPRESS'
                            );

$ARRAY_STATUS_REMESSA = array(
                              '1' => 'Registro salvo na tabela - ainda n�o houve intera��o com o BEXS',
                              '2' => "Envio informa��es via WS retornou sucesso",
                              '3' => "Sucesso ao enviar o arquivo de opera��es via sFTP",
                              '4' => "Transa��o processada com sucesso (XML arquivo de opera��es processado com SUCESSO) - CONCLU�DA",
                              '5' => "Campos obrigat�rios incorretos no arquivo de opera��es a enviar via sFTP ao BEXS",
                              '6' => "Falha na transmiss�o do arquivo de opera��es via sFTP ao BEXS",
                              '7' => "Falha na comunica��o com Web Service BEXS",
                              '8' => "Requisi��o ao Web Service BEXS retonou ERRO",
                              '9' => "Processamento do arquivo de opera��es retornou ERRO(XML do arquivo de opera��es retornou ERRO ao ser processado)",
                              '10' => "Remessa cancelada",
);

$ARRAY_STATUS = array(
                        'REGISTRO_CRIADO'       => '1',
                        'SUCESSO_WS'            => '2',
                        'SUCESSO_SFTP'          => '3',
                        'SUCESSO_PROCESSAMENTO' => '4',
                        'ERRO_CAMPO_OBRIGATORIO'=> '5',
                        'ERRO_SFTP'             => '6',
                        'ERRO_ACESSO_WS'        => '7',
                        'ERRO_WS'               => '8',
                        'ERRO_PROCESSAMENTO'    => '9',
                        "CANCELADA"             => '10'
);

//IPs com acesso permitido a p�gina que recebe a resposta do processamento de cada remessa BEXS
$ARRAY_WHITE_LIST_PAGE_RESPOSTA = array('179.191.88.213','187.45.247.106');

include_once $raiz_do_projeto.'banco/bexs/classGerais.php';
include_once $raiz_do_projeto.'banco/bexs/classBexs.php';
include_once $raiz_do_projeto.'banco/bexs/classTipoTransmissaoBexs.php';
include_once $raiz_do_projeto.'banco/bexs/ExSimpleXMLElement.php';
