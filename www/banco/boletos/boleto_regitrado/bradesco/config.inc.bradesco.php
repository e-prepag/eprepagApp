<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
require_once $raiz_do_projeto . 'banco/bradesco/config.inc.urls_bradesco.php';

define("BRADESCO_CARTEIRA", "26");      //CARTEIRA NOVA - AGOSTO 2018

// Tipo de Mensagem do Sistema
define("BRADESCO_MSG_ERROR_LOG", "ERROR_LOG");
define("BRADESCO_MSG_TRANSACTION_LOG","TRANSACTION_LOG");

// Arquivo de Log onde serao registrados todos os erros gerados  
define("LOG_FILE_BRADESCO_WS_ERRORS",	$raiz_do_projeto . "log/log_BRADESCO_WS-Errors.log");

// Arquivo de Log onde serao registrados todos os cabecalhos de Request/Response
define("LOG_FILE_BRADESCO_WS_TRANSACTIONS",	$raiz_do_projeto . "log/log_BRADESCO_WS-Transactions.log");

$BRADESCO_CODE_STATUS_PROTOCOL_HTTP = array(
                                '401' => "Credenciais de acesso não estão presentes no cabeçalho da requisição BASE_64(MerchantID:ChaveDeSeguranca)", 
                                '201' => "Boleto Bancário gerado com sucesso. O código de retorno neste caso é 0 (Zero)", 
                                '200' => "Requisição recebida, porém, o boleto não pode ser gerado devido as regras de negócio aplicadas.",
                                '415' => "Tipo de conteúdo da mensagem não suportado. Valores válidos: application/json ou application/xml",
                                '400' => "Conteúdo da mensagem vazio ou mal formatado",
                                '503' => "Erro ao processar requisição. Necessário acionar suporte técnico"
                        );

$BRADESCO_CODE_ERRORS_REGISTRO = array(
                                '0' => "REGISTRO EFETUADO COM SUCESSO",
                                '-902'   => "SISTEMA INDISPONIVEL NO MOMENTO",
                                '930051' => "REGISTRO EFETUADO COM SUCESSO",
                                '930052' => "PARAMETROS INVALIDOS",
                                '930053' => "REGISTRO EFETUADO COM SUCESSO",
                                '930054' => "TIPO DE PESQUISA INVALIDO",
                                '930055' => "CODIGO DE USUARIO INVALIDO",
                                '930056' => "CPF/CNPJ INVALIDO",
                                '930057' => "NOSSO NUMERO INVALIDO",
                                '930058' => "CODIGO DA PESSOA JURIDICA DO CONTRATO INVALIDO",
                                '930059' => "TIPO DO CONTRATO DE NEGOCIO INVALIDO",
                                '9300510' => "CODIGO DO PRODUTO DE SERVICO DA OPERACAO INVALIDO",
                                '9300511' => "NOSSO NUMERO INVALIDO",
                                '9300512' => "CODIGO DO BANCO INVALIDO",
                                '9300513' => "CODIGO DA AGENCIA CENTRALIZADORA INVALIDA",
                                '9300514' => "CPF OU CNPJ DO SACADO INVALIDO",
                                '9300515' => "CODIGO DO PRODUTO INVALIDO",
                                '9300516'=> "NUMERO DE SEQUENCIA DO CONTRATO INVALIDO",
                                '9300517' => "DATA DE EMISSAO INVALIDA",
                                '9300518' => "TIPO DE VENCIMENTO INVALIDO",
                                '9300519' => "REGISTRO DE TITULO NAO PERMITIDO, DE ACORDO COM PARAMETRO NEGOCIADO PARA O CONTRATO",
                                '9300520' => "VALOR DO TITULO INVALIDO",
                                '9300521' => "ESPECIE DO TITULO INVALIDA",
                                '9300522' => "DATA LIMITE OBRIGATORIA PARA BONIFICACAO",
                                '9300523' => "A SOMATORIA DOS CAMPOS ABATIMENTO, DESCONTO E BONIFICACAO, EXCEDEU O VALOR DO TITULO",
                                '9300524' => "VALOR DO JUROS/MORA INFORMADO EXCEDEU O PARAMETRO",
                                '9300525' => "CONTRATO BLOQUEADO POR CLIENTE COM RESTRICOES E/OU IMPEDIMENTOS",
                                '9300526' => "E-MAIL INVALIDO",
                                '9300527' => "CODIGO DO CONTRATO INVALIDO",
                                '9300528' => "DATA DE VENCIMENTO INVALIDA",
                                '9300529' => "DEVERA SER INFORMADO ALGUM ARGUMENTO",
                                '9300530' => "INFORMAR APENAS PERCENTUAL OU VALOR DE JUROS",
                                '9300531' => "INFORMAR APENAS PERCENTUAL OU VALOR DE MULTA",
                                '9300532' => "DIAS PARA COBRANCA DE MULTA INVALIDO",
                                '9300533' => "SITUACAO OPERACIONAL DO CONTRATO NAO PERMITE O REGISTRO DO TITULO",
                                '9300534' => "INFORMAR APENAS PERCENTUAL OU VALOR DO DESCONTO",
                                '9300535' => "DATA LIMITE DE DESCONTO INVALIDA",
                                '9300536' => "INFORMAR APENAS PERCENTUAL OU VALOR DA BONIFICACAO",
                                '9300537' => "DATA LIMITE PARA BONIFICACAO INVALIDA",
                                '9300538' => "CODIGO DO TIPO DE BOLETO INVALIDO" ,
                                '9300539' => "UTILIZAR 3 DESCONTOS OU 2 DESCONTOS E BONIFICACAO",
                                '9300540' => "DESCONTO - DATA LIMITE 2 IGUAL OU MAIOR QUE DATA LIMITE 3",
                                '9300541' => "DESCONTO - DATA LIMITE 1 IGUAL OU MAIOR QUE DATA LIMITE 3",
                                '9300542' => "DESCONTO - DATA LIMITE 1 IGUAL OU MAIOR QUE DATA LIMITE 2",
                                '9300543' => "CPF/CNPJ OBRIGATORIO PARA DEBITO AUTOMATICO",
                                '9300544' => "CEP SACADO INVALIDO",
                                '9300545' => "CEP SACADOR AVALISTA INVALIDO",
                                '9300546' => "USUARIO NAO AUTORIZADO",
                                '9300547' => "DATA DESCONTO MENOR OU IGUAL DATA EMISSAO",
                                '9300548' => "VALOR DESCONTO MAIOR OU IGUAL VALOR TITULO",
                                '9300549' => "VALOR ABATIMENTO MAIOR OU IGUAL VALOR TITULO",
                                '9300550' => "CEP INVALIDO",
                                '9300551' => "DATA EMISSAO INVALIDA",
                                '9300552' => "DATA VENCIMENTO INVALIDA",
                                '9300553' => "VALOR IOF MAIOR OU IGUAL VALOR TITULO",
                                '9300554' => "PERCENTUAL INFORMADO MAIOR OU IGUAL 100,00",
                                '9300555' => "NUMERO CGC/CPF INVALIDO",
                                '9300556' => "NEGOCIACAO/CLIENTE BLOQUEADO OU PENDENTE",
                                '9300557' => "BANCO/AGENCIA DEPOSITARIA INVALIDO",
                                '9300558' => "ESPECIE DE DOCUMENTO INVALIDO",
                                '9300559' => "DIAS PARA INSTRUCAO DE PROTESTO INVALIDO",
                                '9300560' => "DIAS PARA DECURSO DE PRAZO INVALIDO",
                                '9300561' => "CODIGO PARA DESCONTO INVALIDO",
                                '9300562' => "CODIGO PARA MULTA INVALIDO",
                                '9300563' => "CODIGO DA COMISSAO DE PERMANENCIA INVALIDO",
                                '9300564' => "DATA EMISSAO MAIOR OU IGUAL DATA VENCIMENTO",
                                '9300565' => "DATA DESCONTO INVALIDA",
                                '9300566' => "PERCENTUAL MULTA INFORMADO MAIOR QUE O PERMITIDO",
                                '9300567' => "PERCENTUAL BONIFICACAO INFORMADO MAIOR QUE O PERMITIDO",
                                '9300568' => "VALOR IOF INCOMPATIVEL COM ID PROD",
                                '9300569' => "NAO PODE HAVER MAIS DE UMA BONIFICACAO",
                                '9300570' => "DIGITO INVALIDO",
                                '9300571' => "CLIENTE INEXISTENTE",
                                '9300572' => "PERCENTUAL COMISSAO PERMANENCIA INFORMADO MAIOR QUE O PERMITIDO",
                                '9300573' => "CNPJ/CPF INVALIDO",
                                '9300574' => "TITULO JA CADASTRADO",
                                '9300575' => "INFORME A DATA DE VENCIMENTO",
                                '9300576' => "DATA VENCIMENTO POSTERIOR A 10 ANOS",
                                '9300577' => "VALOR IOF OBRIGATORIO",
                                '9300578' => "INFORME TODOS OS CAMPOS P/ ABATIMENTO",
                                '9300579' => "TIPO INVALIDO",
                                '9300580' => "INFORME TODOS OS DADOS DO SACADOR AVALISTA",
                                '9300581' => "REGISTRO ON-LINE NAO PERMITIDO - BANCO-CLIENTE DIFERENTE DE 237",
                                '9300582' => "INFORME TODOS OS DADOS PARA DESCONTO/BONIFICACAO",
                                '9300583' => "VL ACUMULADO DESCONTO/BONIFICACAO MAIOR OU IGUAL VL TITULO",
                                '9300584' => "DATAS DE DESCONTO/BONIFICACAO FORA DE SEQUENCIA",
                                '9300585' => "INFORME TODOS OS CAMPOS PARA MULTA",
                                '9300586' => "INFORME TODOS OS CAMPOS PARA COMISSAO DE PERMANENCIA",
                                '9300587' => "ACESSO NAO AUTORIZADO A ESTA NEGOCIACAO",
                                '9300588' => "NEGOCIACAO BLOQUEADA",
                                '9300589' => "CODIGO DO BANCO DIFERENTE DE 237",
                                '9300590' => "VL ACUMULADO ABAT./DESC./BONIF. MAIOR OU IGUAL VL TITULO",
                                '9300591' => "NEGOCIACAO NAO PODE REGISTRAR TITULO",
                                '9300592' => "QUANTIDADE EXCESSIVA DE CASAS DECIMAIS",
                                '9300593' => "NOSSO NUMERO INFORMADO JA EXISTE NA BASE DE TITULO PENDENTE",
                                '9300594' => "VALOR DE IOF INVALIDO",
                                '9300595' => "DATA DE EMISSAO DEVE SER MENOR QUE A DATA DE VENCIMENTO",
                                '9300596' => "DATA DE EMISSAO DEVE SER MENOR OU IGUAL A DATA DE REGISTRO",
                                '9300597' => "NAO EXISTE PRACA COBRADORA PARA ESTE TITULO",
                                '9300598' => "TIPO DE BOLETO E-MAIL, INFORMAR O ENDERECO DE E-MAIL DO SACADO",
                                '9300599' => "TIPO DE BOLETO SMS, INFORMAR O DDD/CELULAR DO SACADO",
                                '93005100' => "DIAS DE JUROS INVALIDO",
                                '93005101' => "VALOR DA MULTA INFORMADO EXCEDEU O PARAMETRO",
                                '93005102' => "MULTA NAO PERMITIDA PARA BOLETO DE PROPOSTA",
                                '93005103' => "JUROS NAO PERMITIDO PARA BOLETO DE PROPOSTA",
                                '93005104' => "CADASTRO DE PROTESTO AUTOMATICO NAO PERMITIDO - BOLETO DE PROPOSTA",
                                '93005105' => "ESPECIE DO TITULO NAO PERMITIDA - BOLETO DE PROPOSTA NAO CONTRATADO",
                                '93005106' => "NAO E POSSIVEL REGISTRAR O TITULO",
                                '93005107' => "DIAS PARA NEGATIVACAO MENOR QUE O PERMITIDO EM CONTRATO",
                                '93005108' => "ESPECIE DE TITULO NAO PERMITE NEGATIVACAO",
                                '93005109' => "SOLICITACAO DE SERVICO DE NEGATIVACAO NAO NEGOCIADO",
                                '93005110' => "DIAS UTEIS PARA NEGATIVACAO NAO PERMITIDO - CONTRATO EM DIAS CORRIDOS",
                                '93005111' => "DIAS CORRIDOS PARA NEGATIVACAO NAO PERMITIDO - CONTRATO EM DIAS UTEIS",
                                '93005112' => "DADOS MINIMOS PARA REGISTRO NAO INFORMADOS",
                                '93005113' => "O CODIGO DA LOJA ENVIADO NA REQUISICAO NAO CONFERE",
                                '93005114' => "CODIGO DA LOJA NAO ENCONTRADO",
                                '93005115' => "CHAVE DE ACESSO NAO ENCONTRADA/INVALIDA",
                                '93005116' => "ERRO NA FORMATACAO DOS DADOS DE EMISSAO",
                                '93005117' => "REGISTRO NAO ENCONTRADO NAS BASES CDDA/CIP",
                                '93005118' => "INFORMACOES DE ENTRADA INCONSISTENTES CDDA/CIP",
                                '93005119' => "REGISTRO EFETUADO COM SUCESSO - CIP CONFIRMADA",
                                '93005120' => "CARTEIRA DE COBRANCA NAO ACEITA",

);

//Código de Sucesso da Transação
$BRADESCO_CODE_SUCESS = array(
                                '0',      //REGISTRO EFETUADO COM SUCESSO
                                '930051', //REGISTRO EFETUADO COM SUCESSO
                                '930053', //REGISTRO EFETUADO COM SUCESSO
                                '9300574', //TITULO JA CADASTRADO
                                '93005119' //REGISTRO EFETUADO COM SUCESSO - CIP CONFIRMADA
                        );

include_once("classGerais.php");
include_once("classXMLEstruturaBradesco.php");
include_once("classBradesco.php");
?>

