<?php
//-----------------------------------------------------------------------
$SITE_DOMINIO = $_SERVER['SERVER_NAME'];
$SITE_URL = "https://" . $SITE_DOMINIO;
$BOLETO_DIR = "/banco/boletos/";
$IMAGES_DIR = $SITE_URL . "/images/boletos/";
$PROCESS_AUTOM_IDUSER_BKO = '0401121156014';
// Boleto de corte emitido atй 01:00 da 2aF com vencimento para prуximo dнa, ou seja 3aF, atй 4aF й mais um dia e meio
// Alterado para 5aF e mais meio dia alterando PROCESS_AUTOM_ZERA_LIMITE_BOLETO_DIAS_VENCIDO de 1.5 para 2.5 em 24/12/2013
// Alterado para 4aF e mais meio dia novamente em 07/01/2014
// Alterado para 5aF e mais meio dia alterando PROCESS_AUTOM_ZERA_LIMITE_BOLETO_DIAS_VENCIDO de 1.5 para 2.5 em 09/02/2018
$PROCESS_AUTOM_ZERA_LIMITE_BOLETO_DIAS_VENCIDO = 1.5;

//BOLETO
//------------------------------------------------------------------------------------------------

//Variaveis Bradesco
// Alterado para 3 dias alterando BOLETO_QTDE_DIAS_VENCIMENTO de 1 para 3 em 28/02/2014
// Alterado para 1 dia alterando BOLETO_QTDE_DIAS_VENCIMENTO de 3 para 1 em 07/03/2014
// Alterado para 3 dias alterando BOLETO_QTDE_DIAS_VENCIMENTO de 1 para 3 em 13/02/2015
// Alterado para 3 dias alterando BOLETO_QTDE_DIAS_VENCIMENTO de 1 para 3 em 05/02/2016
// Alterado para 3 dias alterando BOLETO_QTDE_DIAS_VENCIMENTO de 1 para 3 em 23/02/2017
// Alterado para 3 dias alterando BOLETO_QTDE_DIAS_VENCIMENTO de 1 para 3 em 09/02/2018
$BOLETO_QTDE_DIAS_VENCIMENTO 	= 1;
$BOLETO_COD_BANCO_BRADESCO 		= 237;
$BOLETO_CEDENTE_AGENCIA 		= "2062";
$BOLETO_CEDENTE_AGENCIA_DV 		= "1";
$BOLETO_CEDENTE_CONTA 			= "0020459";	//"0020888";
$BOLETO_CEDENTE_CONTA_DV 		= "5"; 	//"4";
//$BOLETO_CARTEIRA			= "06";
// Nova carteira em 2014-03-18
$BOLETO_CARTEIRA			= "26";         //CARTEIRA NOVA - AGOSTO 2018

$BOLETO_JUROS_AO_MES_PRCT		= 0.5; // 24.32; // usar valor numerico computacional 10.0 e nao 10,0
$BOLETO_LIMITE_PARA_TAXA_ADICIONAL_BRADESCO	= 60;
$BOLETO_TAXA_ADICIONAL_BRADESCO	= 1.80;

//Variaveis Itau
$BOLETO_ITAU_QTDE_DIAS_VENCIMENTO   = 3;
$BOLETO_ITAU_COD_BANCO              = 341;
$BOLETO_ITAU_CEDENTE_AGENCIA        = "0444";
$BOLETO_ITAU_CEDENTE_AGENCIA_DV     = "1";
$BOLETO_ITAU_CEDENTE_CONTA          = "89756";
$BOLETO_ITAU_CEDENTE_CONTA_DV       = "5";
$BOLETO_ITAU_CARTEIRA               = "175";
$BOLETO_ITAU_JUROS_AO_MES_PRCT      = 0.5; // usar valor numerico computacional 10.0 e nao 10,0
$BOLETO_ITAU_LIMITE_PARA_TAXA_ADICIONAL	= 60;
$BOLETO_ITAU_TAXA_ADICIONAL         = 1.80;

//Variaveis Santander
$BOLETO_BANESPA_QTDE_DIAS_VENCIMENTO = 3;
$BOLETO_BANESPA_COD_BANCO = "033";
$BOLETO_BANESPA_CODIGO_CEDENTE = "6377980";
$BOLETO_BANESPA_CARTEIRA  = "102";
$BOLETO_BANESPA_CEDENTE_AGENCIA = "3793";
$BOLETO_BANESPA_CEDENTE_AGENCIA_DV = "1";
$BOLETO_BANESPA_CEDENTE_CONTA = "130062938";
$BOLETO_BANESPA_CEDENTE_CONTA_DV = "";
$BOLETO_BANESPA_TAXA_CUSTO_BANCO = 1.02; 
$BOLETO_BANESPA_LIMITE_PARA_TAXA_ADICIONAL	= 60;
$BOLETO_BANESPA_TAXA_ADICIONAL = 2;  //TAXA BOLETO COBRADO DO GAMER

//Status Boleto Bancario Corte
//------------------------------------------------------------------------------------------------
//	1: Aberto
//	2: Enviado
//	3: Conciliado
//	4: Cancelado
$CORTE_BOLETO_STATUS = array(	'ABERTO' 	=> '1',
                                'ENVIADO'	=> '2',
                                'CONCILIADO'=> '3',
                                'CANCELADO'	=> '4');
//Ate o primeiro ponto final eh o chamado Descricao Curta, usado em alguns lugares
//Para novos status, evitar passar de 25 caracteres ateh o primeiro ponto final.
$CORTE_BOLETO_STATUS_DESCRICAO = array(	'1' => 'Em aberto. Aguardando envio.',
                                        '2' => 'Enviado. Aguardando retorno e conciliaзгo.',
                                        '3' => 'Conciliado.',
                                        '4' => 'Cancelado.');

//Status Boleto Banco
//------------------------------------------------------------------------------------------------
$BOLETO_BANCO_STATUS = array(	'ENVIADO'	=> '1',
                                'REJEITADO'	=> '2',
                                'ACEITO'	=> '3',
                                'LIQUIDADO'	=> '4');
//Ate o primeiro ponto final eh o chamado Descricao Curta, usado em alguns lugares
//Para novos status, evitar passar de 25 caracteres ateh o primeiro ponto final.
$BOLETO_BANCO_STATUS_DESCRICAO = array(	'1' => 'Enviado. Aguardando retorno.',
                                        '2' => 'Rejeitado.',
                                        '3' => 'Aceito.',
                                        '4' => 'Liquidado.');


//Status Corte
//------------------------------------------------------------------------------------------------
//	1: Corte em aberto
//	3: Corte conciliado
//	4: Corte cancelado
$CORTE_STATUS = array(	'ABERTO' 	=> '1',
                        'CONCILIADO'=> '3',
                        'CANCELADO'	=> '4',
                        'CONCILIADO_MANUALMENTE'=> '63');
//Ate o primeiro ponto final eh o chamado Descricao Curta, usado em alguns lugares
//Para novos status, evitar passar de 25 caracteres ateh o primeiro ponto final.
$CORTE_STATUS_DESCRICAO = array('1' => 'Em aberto. Aguardando confirmaзгo bancбria.',
                                '3' => 'Quitado.',
                                '4' => 'Cancelado.',
                                '63' => 'Quitado por depуsito.');


// Nъmero de dias para bloquear conta de LH apуs a emissгo do boleto de corte, 
// se este nгo for pago
$CORTE_BOLETO_PRACO_BLOQUEIO = 1;

//Formas de Pagamento
//------------------------------------------------------------------------------------------------
// 1: Deposito
// 2: Boleto Bancario
$CORTE_FORMAS_PAGAMENTO = array( 'BOLETO_BANCARIO'=> '2');
$CORTE_FORMAS_PAGAMENTO_DESCRICAO = array('2' => 'Boleto Bancбrio');

//Dias da semana de corte - Segue mesma definicao e valores da funcao date('w')
//------------------------------------------------------------------------------------------------
$CORTE_DIAS_DA_SEMANA = array(	'DOMINGO' 	=> '0',
                                'SEGUNDA' 	=> '1',
                                'TERCA' 	=> '2',
                                'QUARTA' 	=> '3',
                                'QUINTA' 	=> '4',
                                'SEXTA' 	=> '5',
                                'SABADO' 	=> '6');

$CORTE_DIAS_DA_SEMANA_DESCRICAO = array('0' => 'Domingo',
                                        '1' => 'Segunda-feira',
                                        '2' => 'Terзa-feira',
                                        '3' => 'Quarta-feira',
                                        '4' => 'Quinta-feira',
                                        '5' => 'Sexta-feira',
                                        '6' => 'Sбbado');

?>