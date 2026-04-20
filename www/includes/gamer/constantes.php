<?php 
require_once __DIR__ . '/../constantes_url.php'; 
$raiz_do_projeto = dirname(__DIR__, 2) . '/';
?>
<?php
//Constantes

require_once("constantesPinEpp.php");

//ID do Publisher TREINAMENTO
$dd_operadora_Treinamento = 78;

//------------------------------------------------------------------------------------------------
$PROCESS_AUTOM_PEDIDO_EFETUADO_CANCELAMENTO_DIAS_VENCIDO = 7;
$PROCESS_AUTOM_BOLETO_CANCELAMENTO_DIAS_VENCIDO = 5;
$PROCESS_AUTOM_BOLETO_AVISO_CANCELAMENTO_DIAS = 3;
$PROCESS_AUTOM_PEDIDO_ONLINE_EFETUADO_CANCELAMENTO_MINUTOS_VENCIDO = 90;	// minutos, apenas para pagamentos Online (Bradesco, Banco do Brasil)

$BOLETO_MONEY_BRADESCO_QTDE_DIAS_UTEIS_VENCIMENTO = 5;
$BOLETO_MONEY_ITAU_QTDE_DIAS_UTEIS_VENCIMENTO = 5;


$PROCESS_AUTOM_IDUSER_BKO = '0401121156014';
//	$BOLETO_TAXA_ADICIONAL = 2.00;
$PREPAG_DOMINIO = "https://" . EPREPAG_URL . "";
$ENTRE_CONTATO_CENTRAL = "Por favor, entre em contato com nossa Central de Atendimento atrav�s do e-mail suporte@e-prepag.com.br";
$GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY'] = 7909;

//Log de comandos SQL	
$ARQUIVO_LOG_SQL_EXECUTE_QUERY = $raiz_do_projeto . "arquivos_gerados/logs/log_commerce_sql_execute_query.txt";
//Log de HTTP_REFERER	
$ARQUIVO_LOG_HTTP_REFERER = $raiz_do_projeto . "arquivos_gerados/logs/log_commerce_http_referer.txt";

//Diretorio de arquivos upload - comprovante
$FOLDER_COMMERCE_UPLOAD = $raiz_do_projeto . "/backoffice/offweb/upload_arquivos/commerce/";
$FOLDER_COMMERCE_UPLOAD_TMP = $raiz_do_projeto . "/backoffice/offweb/upload_arquivos/commerce/tmp/";

//Imagens dos produtos e modelos
$IMAGES_PRODUTO_EXTENSOES = array("gif", "jpg", "png");
$URL_DIR_IMAGES_PRODUTO = "/imagens/gamer/produtos/";
$FIS_DIR_IMAGES_PRODUTO = $raiz_do_projeto . "arquivos_gerados/imagens/gamer/produtos/";

//Imagens dos banners
$IMAGES_BANNER_EXTENSOES = array("gif", "jpg", "png", "jpeg");
$URL_DIR_IMAGES_BANNER = "/prepag2/dist_commerce/images/banners/";
$FIS_DIR_IMAGES_BANNER = $raiz_do_projeto . "/www/web/prepag2/dist_commerce/images/banners/";

//BOLETO
//------------------------------------------------------------------------------------------------
$BOLETO_DIR = $raiz_do_projeto . "banco/boletos/";
//	$BOLETO_TAXA_ADICIONAL 						= 2.00;

$BOLETO_MONEY_BANCO_DO_BRASIL_COD_BANCO 	= "001";

$BOLETO_MONEY_BANCO_ITAU_COD_BANCO 			= "341";

$BOLETO_MONEY_CAIXA_QTDE_DIAS_VENCIMENTO 	= 2;
$BOLETO_MONEY_CAIXA_COD_BANCO 				= 104;
$BOLETO_MONEY_CAIXA_TAXA_ADICIONAL 			= 2.00;

// DADOS PERSONALIZADOS - Banespa       
$BOLETO_MONEY_BANCO_BANESPA_COD_BANCO = "033";
$BOLETO_MONEY_BANESPA_CODIGO_CEDENTE = "6377980";
$BOLETO_MONEY_BANESPA_CARTEIRA  = "102";
$BOLETO_MONEY_BANESPA_CEDENTE_AGENCIA = "3793";
$BOLETO_MONEY_BANESPA_CEDENTE_AGENCIA_DV = "1";
$BOLETO_MONEY_BANESPA_CEDENTE_CONTA = "130062938";
$BOLETO_MONEY_BANESPA_CEDENTE_CONTA_DV = "";
$BOLETO_MONEY_BANESPA_QTDE_DIAS_VENCIMENTO = 3;
$BOLETO_MONEY_BANESPA_TAXA_CUSTO_BANCO = 1.02;
$BOLETO_MONEY_BANESPA_TAXA_ADICIONAL = 2;  //TAXA BOLETO COBRADO DO GAMER

$BOLETO_MONEY_BRADESCO_QTDE_DIAS_VENCIMENTO = 3;	// ver BOLETO_MONEY_BRADESCO_QTDE_DIAS_UTEIS_VENCIMENTO = 5 
$BOLETO_MONEY_BRADESCO_COD_BANCO 			= 237;
$BOLETO_MONEY_BRADESCO_TAXA_ADICIONAL 		= 1.99; //2; //TAXA BOLETO COBRADO DO GAMER
$BOLETO_MONEY_BRADESCO_CEDENTE_AGENCIA 		= "2062";
$BOLETO_MONEY_BRADESCO_CEDENTE_AGENCIA_DV 	= "1";
$BOLETO_MONEY_BRADESCO_CEDENTE_CONTA 		= "0020459";	//"0020888";
$BOLETO_MONEY_BRADESCO_CEDENTE_CONTA_NOVA 	= "0001689";     //Conta nova da EPP ADMINISTRADORA DE CARTOES (julho/18)
$BOLETO_MONEY_BRADESCO_CEDENTE_CONTA_DV 	= "5"; 	//"4";
$BOLETO_MONEY_BRADESCO_CEDENTE_CONTA_NOVA_DV = "6";         //Digito verificador da conta nova da EPP ADMINISTRADORA DE CARTOES (julho/18)
//	$BOLETO_MONEY_BRADESCO_CARTEIRA				= "06";
// Nova carteira em 2014-03-18
$BOLETO_MONEY_BRADESCO_CARTEIRA			= "26";         //CARTEIRA NOVA - AGOSTO 2018
$BOLETO_MONEY_BRADESCO_JUROS_AO_MES_PRCT	= 24.32; //usar valor numerico computacional 10.0 e nao 10,0

// ASAAS
$BOLETO_MONEY_ASAAS_COD_BANCO 			= 461;

$BOLETO_MONEY_ITAU_QTDE_DIAS_VENCIMENTO		= 3;	// ver BOLETO_MONEY_ITAU_QTDE_DIAS_UTEIS_VENCIMENTO = 5
$BOLETO_MONEY_ITAU_COD_BANCO 				= 341;
$BOLETO_MONEY_ITAU_TAXA_ADICIONAL 			= 1.99;	 //TAXA BOLETO COBRADO DO GAMER // � a taxa cobrada pelo site para pagamento por boleto Ita� 
$BOLETO_MONEY_ITAU_TAXA_CUSTO_BANCO 		= 1.35;	// � a taxa cobrada pelo Ita� da E-Prepag para boletos
$BOLETO_MONEY_ITAU_TAXA_CUSTO_BANCO_2 		= 2.40; //1.10;	// � a taxa cobrada pelo Ita� da E-Prepag para boletos - transi��o para novo valor
// a concilia��o ficar� com dois valores de custo desde 2011-10-13 at� pr�xima semana
$BOLETO_MONEY_ITAU_CEDENTE_AGENCIA 			= "0444";
$BOLETO_MONEY_ITAU_CEDENTE_AGENCIA_DV 		= "1";
$BOLETO_MONEY_ITAU_CEDENTE_CONTA 			= "77567";
$BOLETO_MONEY_ITAU_CEDENTE_CONTA_DV 		= "0";
$BOLETO_MONEY_ITAU_CEDENTE_CONTA_NOVA 		= "89756";
$BOLETO_MONEY_ITAU_CEDENTE_CONTA_DV_NOVA	= "5";
$BOLETO_MONEY_ITAU_TAXA_CUSTO_BANCO_NOVA 	= 1.35;	// � a taxa cobrada pelo Ita� da E-Prepag para boletos na nova conta
$BOLETO_MONEY_ITAU_CARTEIRA					= "176";
$BOLETO_MONEY_ITAU_JUROS_AO_MES_PRCT		= 00.00; //usar valor numerico computacional 10.0 e nao 10,0


$BOLETO_TAXA_ADICIONAL 						= $BOLETO_MONEY_ITAU_TAXA_ADICIONAL;

//Areas
//------------------------------------------------------------------------------------------------
$AREAS_REGIAO_1 = array("3", "4", "8", "9", "10");
$AREAS_REGIAO_2 = array("5", "6", "7");
$AREAS_REGIAO_3 = array("1", "2");

$ESTADOS_AREA_1 = array("SP");
$ESTADOS_AREA_2 = array("SP");
$ESTADOS_AREA_3 = array("ES", "RJ");
$ESTADOS_AREA_4 = array("MG");
$ESTADOS_AREA_5 = array("PR", "SC");
$ESTADOS_AREA_6 = array("RS");
$ESTADOS_AREA_7 = array("AC", "DF", "GO", "MS", "MT", "RO", "TO");
$ESTADOS_AREA_8 = array("AM", "AP", "MA", "PA", "RR");
$ESTADOS_AREA_9 = array("BA", "SE");
$ESTADOS_AREA_10 = array("AL", "CE", "PB", "PE", "PI", "RN");

//Estados
//------------------------------------------------------------------------------------------------
$SIGLA_ESTADOS = array("AC", "AL", "AM", "AP", "BA", "CE", "DF", "ES", "GO", "MA", "MG", "MS", "MT", "PA", "PB", "PE", "PI", "PR", "RJ", "RN", "RO", "RR", "RS", "SC", "SE", "SP", "TO");


//Formas de Pagamento
//------------------------------------------------------------------------------------------------
// 1: Transfer�ncia Banc�ria, DOC Eletr�nico, Dep�sito ou Outros
//		Nao eh uma forma de pagamento propriamente dita,
//		pois o cliente ainda nao pagou, ou pelo menos,
//		nao entrou com os dados de pagamento.
// 2: Boleto Bancario
$FORMAS_PAGAMENTO = array(
	'DEP_DOC_TRANSF' => '1',
	'BOLETO_BANCARIO' => '2',
	//'REDECARD_MASTERCARD'=> '3',
	//'REDECARD_DINERS'=> '4', 
	'TRANSFERENCIA_ENTRE_CONTAS_BRADESCO' => '5',
	'PAGAMENTO_FACIL_BRADESCO_DEBITO' => '6',
	'PAGAMENTO_FACIL_BRADESCO_CREDITO' => '7',
	//'PAGAMENTO_BB_DEBITO_SUA_EMPRESA'=> '8', 
	'PAGAMENTO_BB_DEBITO_SUA_CONTA' => '9',
	'PAGAMENTO_BANCO_ITAU_ONLINE' => 'A',
	'PAGAMENTO_HIPAY_ONLINE' => 'B',
	'PAGAMENTO_PAYPAL_ONLINE' => 'P',
	'PAGAMENTO_PIN_EPREPAG' => 'E',

	'PAGAMENTO_VISA_DEBITO' => 'F',
	'PAGAMENTO_VISA_CREDITO' => 'G',
	'PAGAMENTO_MASTER_DEBITO' => 'H',
	'PAGAMENTO_MASTER_CREDITO' => 'I',
	'PAGAMENTO_ELO_DEBITO' => 'J',
	'PAGAMENTO_ELO_CREDITO' => 'K',
	'PAGAMENTO_DINERS_CREDITO' => 'L',
	'PAGAMENTO_DISCOVER_CREDITO' => 'M',

	'OFERTAS' => 'O', // Credito de Ofertas Matomy, Sponsor, Reward 

	'PAGAMENTO_MCOIN' => 'Q', // Pagamentos MCOIN - celular
	'PAGAMENTO_PIX' => 'R',
	'PAGAMENTO_PERSONALIZADO' => 'S'

);

//N�o apresentar na pagina de vendas da loja
$a_formas_ocultas = array("7", "F", "G", "H", "I", "J", "K", "L", "M");

/* Legenda
[Visa d�bito]			= F
[Visa cr�dito]			= G
[Mastercard d�bito]		= H
[Mastercard cr�dito]	= I
[Elo d�bito]			= J
[Elo cr�dito]			= K
[Diners cr�dito]		= L
[Discover cr�dito]		= M
*/

// 	$PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC
//	$PAGAMENTO_HIPAY_ONLINE_NUMERIC
//	$PAGAMENTO_PAYPAL_ONLINE_NUMERIC

//	$BOLETO_MONEY_HIPAY_COD_BANCO
//	$BOLETO_MONEY_PAYPAL_COD_BANCO

$PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC = 10;	// para tabelas tb_venda_games e tb_dist_venda_games o campo vg_tipo_pagamento � numerico
// n�o pode usar $FORMAS_PAGAMENTO['PAGAMENTO_BANCO_ITAU_ONLINE']

$PAGAMENTO_HIPAY_ONLINE_NUMERIC = 11;		// para tabelas tb_venda_games e tb_dist_venda_games o campo vg_tipo_pagamento � numerico				
$PAGAMENTO_HIPAY_ONLINE_TAXA = 0;
$BOLETO_MONEY_HIPAY_COD_BANCO = "998";

$PAGAMENTO_PAYPAL_ONLINE_NUMERIC = 12;		// para tabelas tb_venda_games e tb_dist_venda_games o campo vg_tipo_pagamento � numerico
$PAGAMENTO_PAYPAL_ONLINE_TAXA = 0;
$PAGAMENTO_PAYPAL_ONLINE_CURRENCY = 'Brazil';
$BOLETO_MONEY_PAYPAL_COD_BANCO = "997";

$PAGAMENTO_PIN_EPREPAG_NUMERIC = 13;
$PAGAMENTO_PIN_EPP_COD_BANCO = "996";
$PAGAMENTO_PIN_EPP_NOME_BANCO = "E-PREPAG CASH";
$PAGAMENTO_PIN_EPP_TAXA = 0;

$PAGAMENTO_VISA_DEBITO_NUMERIC = 14;
$PAGAMENTO_VISA_DEBITO_COD_BANCO = "995";
$PAGAMENTO_VISA_DEBITO_NOME_BANCO = "VISA DEBITO";
$PAGAMENTO_VISA_DEBITO_TAXA = 2.99;

$PAGAMENTO_VISA_CREDITO_NUMERIC = 15;
$PAGAMENTO_VISA_CREDITO_COD_BANCO = "994";
$PAGAMENTO_VISA_CREDITO_NOME_BANCO = "VISA CREDITO";
$PAGAMENTO_VISA_CREDITO_TAXA = 2.99;

$PAGAMENTO_MASTER_DEBITO_NUMERIC = 16;
$PAGAMENTO_MASTER_DEBITO_COD_BANCO = "993";
$PAGAMENTO_MASTER_DEBITO_NOME_BANCO = "MASTER DEBITO";
$PAGAMENTO_MASTER_DEBITO_TAXA = 2.99;

$PAGAMENTO_MASTER_CREDITO_NUMERIC = 17;
$PAGAMENTO_MASTER_CREDITO_COD_BANCO = "992";
$PAGAMENTO_MASTER_CREDITO_NOME_BANCO = "MASTER CREDITO";
$PAGAMENTO_MASTER_CREDITO_TAXA = 2.99;

$PAGAMENTO_ELO_DEBITO_NUMERIC = 18;
$PAGAMENTO_ELO_DEBITO_COD_BANCO = "991";
$PAGAMENTO_ELO_DEBITO_NOME_BANCO = "ELO DEBITO";
$PAGAMENTO_ELO_DEBITO_TAXA = 2.99;

$PAGAMENTO_ELO_CREDITO_NUMERIC = 19;
$PAGAMENTO_ELO_CREDITO_COD_BANCO = "990";
$PAGAMENTO_ELO_CREDITO_NOME_BANCO = "ELO CREDITO";
$PAGAMENTO_ELO_CREDITO_TAXA = 2.99;

$PAGAMENTO_DINERS_CREDITO_NUMERIC = 20;
$PAGAMENTO_DINERS_CREDITO_COD_BANCO = "989";
$PAGAMENTO_DINERS_CREDITO_NOME_BANCO = "DINERS CREDITO";
$PAGAMENTO_DINERS_CREDITO_TAXA = 2.99;

$PAGAMENTO_DISCOVER_CREDITO_NUMERIC = 21;
$PAGAMENTO_DISCOVER_CREDITO_COD_BANCO = "988";
$PAGAMENTO_DISCOVER_CREDITO_NOME_BANCO = "DISCOVER CREDITO";
$PAGAMENTO_DISCOVER_CREDITO_TAXA = 2.99;

// Credito de Ofertas Matomy, Sponsor, Reward
$PAGAMENTO_OFERTAS_NUMERIC = 22;
$PAGAMENTO_OFERTAS_COD_BANCO = "987";
$PAGAMENTO_OFERTAS_NOME_BANCO = "OFERTAS";
$PAGAMENTO_OFERTAS_TAXA = 0;

// pagamentos MCOIN por celular
$PAGAMENTO_MCOIN_NUMERIC = 23;
$PAGAMENTO_MCOIN_COD_BANCO = "986";
$PAGAMENTO_MCOIN_NOME_BANCO = "MCOIN";
$PAGAMENTO_MCOIN_TAXA = 0;
$PAGAMENTO_MCOIN_VALOR_FULL = 560;	// O sistema funciona apenas com pagamentos de R$4,00
$PAGAMENTO_MCOIN_VALOR = 400;	// O sistema funciona apenas com pagamentos de R$4,00

// Pagamento PIX
$PAGAMENTO_PIX_NUMERIC = 24;
$PAGAMENTO_PIX_COD_BANCO = "400";
$PAGAMENTO_PIX_NOME_BANCO = "PIX";
$PAGAMENTO_PIX_TAXA = 0;


$TIPO_DEPOSITO = array(
	'DEPOSITO_RESTO_PINS' => 1,
	'DEPOSITO_DIRETO_COM_PAGAMENTO' => 2,
	'DEPOSITO_ENQUETES' => 3,
	'DEPOSITO_PROMOCAO' => 4,
	'DEPOSITO_OFERTAS' => 5,
);
$TIPO_DEPOSITO_LEGENDA = array(
	1 => 'Resto de pagamento com PINs',
	2 => 'Dep�sito direto no Saldo',
	3 => 'Responder enquetes de parceiro',
	4 => 'Promo��es',
	5 => 'Ofertas',
);


// Banco E-Prepag - Testes
$PAGAMENTO_BANCO_EPP_ONLINE = 'Z';			// Banco E-Prepag de TESTES
$PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC = 999;	// para tabelas tb_venda_games e tb_dist_venda_games o campo vg_tipo_pagamento � numerico
// n�o pode usar $PAGAMENTO_BANCO_EPP_ONLINE
$FORMAS_PAGAMENTO_DESCRICAO_EPP = 'Pagamento de Testes EPP';
$FORMAS_PAGAMENTO_ICONES_EPP = 'botao_conta_epp_2.gif';
$BANCO_EPP_TAXA_DE_SERVICO = 0;
$BOLETO_MONEY_BANCO_EPP_COD_BANCO = "999";


$FORMAS_PAGAMENTO_DESCRICAO = array(
	'1' => 'Transfer&ecirc;ncia Banc&aacute;ria, DOC Eletr&ocirc;nico, Dep&oacute;sito, Remessa do exterior ou Outros',
	'2' => 'Boleto Banc&aacute;rio',
	//'3' => 'MasterCard',
	//'4' => 'Diners',
	'5' => 'Transfer&ecirc;ncia entre contas Bradesco',
	'6' => 'Pagamento F&aacute;cil Bradesco - D&eacute;bito',
	'7' => 'Pagamento F&aacute;cil Bradesco - Cr&eacute;dito',
	//'8' => 'Pagamento BB - D�bito sua Empresa',
	'9' => 'Pagamento BB - D&eacute;bito sua Conta',
	'A' => 'Pagamento Itau - &Agrave; vista (Transfer&ecirc;ncia)',
	'B' => 'Pagamento Online HiPay',
	'P' => 'Pagamento Online PayPal',
	'E' => 'Pagamento Atrav&eacute;s de E-PREPAG CASH',

	'F' => 'Pagamento Visa Net',
	'G' => 'Pagamento Visa Cr&eacute;dito',
	'H' => 'Pagamento Maestro',
	'I' => 'Pagamento Mastercard Cr&eacute;dito',
	'J' => 'Pagamento Elo D&eacute;bito',
	'K' => 'Pagamento Elo Cr&eacute;dito',
	'L' => 'Pagamento Diners Cr&eacute;dito',
	'M' => 'Pagamento Discover Cr&eacute;dito',

	'O' => 'Ofertas',

	'Q' => 'Pagamentos MCOIN - celular',
	'R' => 'Pix',
	'S' => 'E-PREPAG CASH personalizado'
);

$FORMAS_PAGAMENTO_DESCRICAO_NUMERICO = array(
	'1' => 'Transfer&ecirc;ncia Banc&aacute;ria, DOC Eletr&ocirc;nico, Dep&oacute;sito, Remessa do exterior ou Outros',
	'2' => 'Boleto Banc&aacute;rio',
	//'3' => 'MasterCard',
	//'4' => 'Diners',
	'5' => 'Transfer&ecirc;ncia entre contas Bradesco',
	'6' => 'Pagamento F&aacute;cil Bradesco - D&eacute;bito',
	'7' => 'Pagamento F&aacute;cil Bradesco - Cr&eacute;dito',
	//'8' => 'Pagamento BB - D�bito sua Empresa',
	'9' => 'Pagamento BB - D&eacute;bito sua Conta',
	'10' => "Pagamento It�u online",
	'11' => "Pagamento Hipay online",
	'12' => "Pagamento Paypal online",
	'13' => "Pagamento EPP Cash",
	'14' => "Pagamento Visa Net",
	'15' => "Pagamento Visa Cr&eacute;dito",
	'16' => "Pagamento Maestro",
	'17' => "Pagamento Mastercard Cr&eacute;dito",
	'18' => "Pagamento Elo D&eacute;bito",
	'19' => "Pagamento Elo Cr&eacute;dito",
	'20' => "Pagamento Diners Cr&eacute;dito",
	'21' => "Pagamento Discover Cr&eacute;dito",
	'22' => "Ofertas",
	'23' => "Pagamentos MCOIN - celular",
	'24' => "Pix",
);
$FORMAS_PAGAMENTO_ICONES    = array(
	'1' => 'seta_icon.png',
	'2' => 'boleto.png',
	//'3' => 'p_mastercard.png',
	//'4' => 'p_diners.jpg', 
	'5' => 'bt_conta_brad_tranf_onli.png',
	'6' => 'bt_conta_brad_electron2o.png',
	'7' => 'bt_pagto_facil.gif',
	//'8' => 'imgDebitoSuaEmpresa.gif',
	'9' => 'bt_conta_BB_menor_debito.png',
	'A' => 'botao_conta_itau_.jpg',
	'B' => 'botao_hipay.gif',
	'P' => 'botao_paypal.gif',
	'E' => 'eppcash.gif', //eprepag_cash_pagto.gif
	'F' => '',
	'G' => 'visa.gif',
	'H' => '',
	'I' => 'mastercard.gif',
	'J' => '',
	'K' => 'elo.gif',
	'L' => 'dinners.gif',
	'M' => 'discover.gif',
	'O' => 'ico_bonus.gif',
	'Q' => 'ico_mcoin.gif',
	'R' => 'ico_pix.png'
);

$FORMAS_PAGAMENTO_ICONES_GAMER    = array(
	'1' => '/imagens/gamer/seta_icon.png',
	'2' => '/imagens/formasPagamento/boleto.gif',
	//'3' => '/prepag2/commerce/images/p_mastercard.png',
	//'4' => '/prepag2/commerce/images/p_diners.jpg', 
	'5' => '/imagens/formasPagamento/bradesco.gif',
	//										'6' => '/prepag2/commerce/images/bt_conta_brad_electron2o.png', 
	//										'7' => '/images/formasPagamento/bt_pagto_facil.gif',
	//'8' => 'imgDebitoSuaEmpresa.gif',
	'9' => '/imagens/formasPagamento/bancodobrasil.gif',
	'A' => '/imagens/formasPagamento/itau.gif',
	//										'B' => '/images/formasPagamento/botao_hipay.gif',
	//										'P' => '/images/formasPagamento/botao_paypal.gif',
	'E' => '/imagens/formasPagamento/eppcash.gif',
	'F' => '/imagens/formasPagamento/cielo.gif',
	'G' => '/imagens/formasPagamento/cielo.gif',
	'H' => '/imagens/formasPagamento/cielo.gif',
	'I' => '/imagens/formasPagamento/cielo.gif',
	'J' => '/imagens/formasPagamento/cielo.gif',
	'K' => '/imagens/formasPagamento/cielo.gif',
	'L' => '/imagens/formasPagamento/cielo.gif',
	'M' => '/imagens/formasPagamento/cielo.gif',
	//'O' => '/images/formasPagamento/ico_bonus.gif',
	//'Q' => '/images/formasPagamento/ico_mcoin.gif',
	'R' => '/imagens/ico_pix.png'
);

//FORMAS DE PAGAMENTO QUE N�O S�O LISTADAS NA ADMINISTRA��O DAS FORMAS DE PAGAMENTO PARA O PUBLISHER
$FORMAS_PAGAMENTO_INATIVAS = array(
	"O",
	"Q"
);

$BRADESCO_TRANSFERENCIA_ENTRE_CONTAS_TAXA_ADICIONAL = 0;	// R$1.20/transa��o
$BRADESCO_DEBITO_EM_CONTA_TAXA_ADICIONAL = 0;	// R$0.00/transa��o

$BANCO_DO_BRASIL_TAXA_DE_SERVICO	= 2.99;	// R$1.00/transa��o

$BANCO_ITAU_TAXA_DE_SERVICO			= 1.49;	// R$0.50/transa��o 

//Status da venda
//------------------------------------------------------------------------------------------------
//	1: Pedido efetuado, aguardando dados do pagamento
//	2: Dados do pagamento recebido, aguardando confirmacao de pagamento
//	3: Pagamento confirmado e liberado para venda
//	4: Processamento realizado. Cr�dito ser� encaminhado para o usu�rio
//	5: Venda realizada. Cr�dito encaminhado para o usu�rio
//	6: Venda cancelada.
$STATUS_VENDA = array(
	'PEDIDO_EFETUADO' 			=> '1',
	'DADOS_PAGTO_RECEBIDO' 		=> '2',
	'PAGTO_CONFIRMADO' 			=> '3',
	'PROCESSAMENTO_REALIZADO'	=> '4',
	'VENDA_REALIZADA' 			=> '5',
	'VENDA_CANCELADA'			=> '6'
);
//Ate o primeiro ponto final eh o chamado Descricao Curta, usado em alguns lugares
//Para novos status, evitar passar de 25 caracteres ateh o primeiro ponto final.
$STATUS_VENDA_DESCRICAO = array(
	'1' => 'Pedido efetuado. Aguardando dados do pagamento.',
	'2' => 'Dados do pagamento j� informados. Aguardando confirma��o banc�ria.',
	'3' => 'Pagamento confirmado. Liberado para venda.',
	'4' => 'Processamento realizado. Cr�dito ser� encaminhado para o usu�rio.',
	'5' => 'Venda realizada. Cr�dito encaminhado para o usu�rio.',
	'6'	=> 'Venda cancelada.'
);
$STATUS_VENDA_ICONES    = array(
	'1' => 'Blue-5-1.gif',
	'2' => 'Blue-5-2.gif',
	'3' => 'Blue-5-3.gif',
	'4' => 'Blue-5-4.gif',
	'5' => 'Blue-5-5.gif',
	'6'	=> 'cancel.gif'
);

$STATUS_VENDA_GAMER = array(
	'PROCESSAMENTO_REALIZADO'	=> '4',
	'VENDA_REALIZADA' 			=> '5',
	'VENDA_CANCELADA'			=> '6'
);

$STATUS_VENDA_DESCRICAO_GAMER = array(
	'1' => 'Pedido efetuado. Aguardando finaliza��o do pagamento.',
	'2' => 'Pedido efetuado. Aguardando finaliza��o do pagamento.',
	'3' => 'Pedido efetuado. Aguardando finaliza��o do pagamento.',
	'4' => 'Pedido efetuado. Aguardando finaliza��o do pagamento.',
	'5' => 'Venda realizada. Cr�dito encaminhado para o usu�rio',
	'6'	=> 'Venda cancelada.'
);
$STATUS_VENDA_ICONES_GAMER    = array(
	'1' => 'Blue-5-1.gif',
	'2' => 'Blue-5-1.gif',
	'3' => 'Blue-5-1.gif',
	'4' => 'Blue-5-1.gif',
	'5' => 'Blue-5-5.gif',
	'6'	=> 'cancel.gif'
);

//Pagamento
//------------------------------------------------------------------------------------------------
//Pagamento Bancos
$PAGTO_BANCOS	= array(
	'001' => 'Banco do Brasil / Banco Postal',
	'237' => 'Bradesco',
	'033' => 'Santander',
	'341' => 'Ita�',
	'104' => 'Caixa Econ�mica Federal / Casas Lot�ricas'
);

//Pagamento Locais
$PAGTO_LOCAIS	= array(
	'001' => array(
		'01' => 'Caixa Autom�tico / Auto-Atendimento',
		'02' => 'Ag�ncia / Boca do Caixa',
		'03' => 'Transfer�ncia pela Internet / DOC',
		'04' => 'Dep�sito em conta corrente em dinheiro',
		'05' => 'Auto-Atendimento Transfer�ncia entre contas correntes',
		'06' => 'Remessa do exterior',
		'07' => 'Banco Postal'
	),
	'237' => array(
		'01' => 'Caixa Autom�tico / Auto-Atendimento',
		'02' => 'Ag�ncia / Boca do Caixa',
		'03' => 'Transfer�ncia pela Internet / DOC',
		'04' => 'Banco Postal',
		'05' => 'BDN � Deposito em conta corrente',
		'06' => 'Remessa do exterior'
	),
	'104' => array(
		'01' => 'Caixa Autom�tico / Auto-Atendimento',
		'02' => 'Ag�ncia / Boca do Caixa',
		'03' => 'Transfer�ncia pela Internet / DOC',
		'04' => 'Casas Lot�ricas',
		'05' => 'Caixa Aqui',
		'06' => 'Remessa do exterior'
	)
);

//Nome do Numero do Documento
$PAGTO_NOME_DOCTO = array(
	'001' => array(
		'01' => 'Nro. do Envelope',
		'02' => 'Ag�ncia de Origem ou D�bito',
		'03' => 'Ag�ncia de Origem ou D�bito;Conta de Origem ou D�bito',
		'04' => 'Nr. Documento',
		'05' => 'Ag�ncia de d�bito;Conta de d�bito',
		'06' => 'Remetente;Banco;Pa�s;Moeda',
		'07' => 'Nro. do Documento'
	),
	'237' => array(
		'01' => 'Nro. do Terminal;Nro. da Transa��o;Ag�ncia Tomadora',
		'02' => 'Ag�ncia Tomadora;Nr. Terminal',
		'03' => 'Nro. do Documento;Agencia de D�bito',
		'04' => 'Ag�ncia Relacionada e PACB',
		'05' => 'Nro. do Terminal;Nro. da Transa��o',
		'06' => 'Remetente;Banco;Pa�s;Moeda'
	),
	'104' => array(
		'01' => 'Nro. do Envelope',
		'02' => 'Nro. do Documento',
		'03' => 'C�digo da Opera��o',
		'04' => 'Hor�rio do Dep�sito',
		'05' => 'C�digo da Opera��o;Hor�rio do Dep�sito',
		'06' => 'Remetente;Banco;Pa�s;Moeda'
	)
);

//usuarios_games_log_tipo
//------------------------------------------------------------------------------------------------
//	1: Criacao do cadastro
//	2: Login
//	3: Alteracao do cadastro
//	4: Troca de senha
//	5: Compra
//	6: Informa dados de pagamento
$USUARIO_GAMES_LOG_TIPOS = array(
	'CRIACAO_DO_CADASTRO' 			=> '1',
	'LOGIN' 						=> '2',
	'ALTERACAO_DO_CADASTRO' 		=> '3',
	'TROCA_DE_SENHA' 				=> '4',
	'VENDA' 						=> '5',
	'INFORMA_DADOS_DE_PAGAMENTO' 	=> '6',
	'LOGOUT' 						=> '7',
	'BANNER' 						=> '8',
	'OFERTAS_MATOMY_VIEW'			=> '9',
	'OFERTAS_MATOMY_POSTBACK'		=> '10',
	'OFERTAS_MATOMY_POSTBACK_ERROR' => '11',
	'OFERTAS_SPONSORPAY_VIEW'		 	 	=> '12',
	'OFERTAS_SPONSORPAY_POSTBACK'		 	=> '13',
	'OFERTAS_SPONSORPAY_POSTBACK_ERROR' 	=> '14',
	'OFERTAS_SUPER_REWARDS_VIEW'		 	=> '15',
	'OFERTAS_SUPER_REWARDS_POSTBACK'		=> '16',
	'OFERTAS_SUPER_REWARDS_POSTBACK_ERROR' => '17',
	'LOGIN_INTEGRACAO'				=> '18',
);
$USUARIO_GAMES_LOG_TIPOS_DESCRICAO = array(
	'1' => 'Cria��o de Cadastro',
	'2' => 'Login com sucesso',
	'3' => 'Altera��o cadastral',
	'4' => 'Troca de senha',
	'5' => 'Realizou um pedido',
	'6'	=> 'Informou dados do pagamento',
	'7'	=> 'Encerrou a sess�o',
	'8'	=> 'Acessou um banner de promo��o',
	'9'  => 'Acessou a p�gina de Ofertas Matomy',
	'10' => 'Registro dos dados Ofertas Matomy (postback)',
	'11' => 'Erro ao tentar registrar Ofertas Matomy (postback)',
	'12' => 'Acessou a p�gina de Ofertas SponsorPay',
	'13' => 'Registro dos dados Ofertas SponsorPay (postback)',
	'14' => 'Erro ao tentar registrar Ofertas SponsorPay (postback)',
	'15' => 'Acessou a p�gina de Ofertas Super Rewards',
	'16' => 'Registro dos dados Ofertas Super Rewards (postback)',
	'17' => 'Erro ao tentar registrar Ofertas Super Rewards (postback)',
	'18' => 'Login com sucesso para libera��o de saldo na integra��o',
	'19' => 'Desativa��o por falta de uso',
	'20' => 'Desativa��o por duplicidade',
);

/*
	// Gest�o de Risco
	// Usu�rios cart�o permitidos pela Cielo
	//		foi para classLimite.php
	$RISCO_CIELO_TOTAL_DIARIO = 200;
	$RISCO_CIELO_PAGAMENTOS_DIARIO = 10;
	$RISCO_CIELO_VALOR_MIN_PARA_TAXA = 0;
	$RISCO_CIELO_VALOR_MIN = 0;
	$RISCO_CIELO_VALOR_MAX = 200;
*/
//	Gamers - Pagamento Online = no max R$700,00 por d�a por usu�rio (ver getVendasMoneyTotalDiarioOnline()) em at� 10 vezes
$RISCO_GAMERS_TOTAL_DIARIO = 700;
$RISCO_GAMERS_PAGAMENTOS_DIARIO = 10;

//Define limite para insen��o de taxas
$RISCO_GAMERS_VALOR_MIN_PARA_TAXA = 49;
$RISCO_GAMERS_VALOR_MIN = 5;	//60;
$RISCO_GAMERS_VALOR_MAX = $RISCO_GAMERS_TOTAL_DIARIO;

//	Gamers VIP- Pagamento Online = no max R$1500,00 por d�a por usu�rio (ver getVendasMoneyTotalDiarioOnline()) em at� 20 vezes
$RISCO_GAMERS_VIP_TOTAL_DIARIO = 1500;
$RISCO_GAMERS_VIP_PAGAMENTOS_DIARIO = 20;

//	Gamers FREE-Integra��o Pagamento Online = no max R$700,00 por d�a por usu�rio (ver getVendasMoneyTotalDiarioOnline()) em at� 100 vezes
$RISCO_GAMERS_FREE_TOTAL_DIARIO = 700;
$RISCO_GAMERS_FREE_PAGAMENTOS_DIARIO = 100;
$RISCO_GAMERS_FREE_MAXIMO_POR_PEDIDO = 700;

//	Gamers - Pagamento Boleto = no max R$1500,00 por venda 
$RISCO_GAMERS_BOLETOS_TOTAL_DIARIO = 1500;

//	Gamers - Pagamento Dep�sito = no max R$1500,00 por venda 
$RISCO_GAMERS_DEPOSITOS_TOTAL_DIARIO = 1500;

//	Gamers - Pagamento para Dep�sito no Saldo
$RISCO_GAMERS_SALDO_TOTAL_DIARIO = 700;
$RISCO_GAMERS_SALDO_PAGAMENTOS_DIARIO = 10;

// Gamer - Integra��o - Prazo (em minutos) para cancelar a re-notifica��o automatica
$PROCESS_AUTOM_PEDIDO_INTEGRACAO_RE_NOTIFICACAO_MINUTOS_VENCIDO = 90;
//-----------------------------------------------------------------------------------------------
//	Campeonatos
$CAMPEONATO_PROD_ID = 76;	// Usa Produtos Campeonatos
$CAMPEONATO_OPR_ID = 99;	// Usa Operadora Campeonatos
$CAMPEONATO_PROD_MOD_ID = 345;	// Modelo de R$20,00


// Para diferenciar usu�rios Frequente/Atrasados/Abandonados
$ATRASO_GAMER_DIAS_LIM_1 = 15;
$ATRASO_GAMER_DIAS_LIM_2 = 30;

// PINs Alawar 
$opr_codigo_Alawar = 55;
$prod_Alawar = 106;
$prod_mod_Alawar = 459;

// Comissao por valume
$opr_codigo_Habbo = 16;
$opr_codigo_Softnyx = 37;

// Dados do Balanco
//-------------------------------------------------------------------------------------------------
//$BALANCO_DATA_ABERTURA = '2012-05-15';
$BALANCO_ZERO_FLOAT = pow(10, -3);

//Tamanhos do PIN GoCASH
$PIN_GOCASH_TAMANHO	= array('15', '16', '17');

//Quatidade m�xima de ITENS permitida na CARRINHO
$QTDE_MAX_ITENS = 50;

//Identificador de modelo de produto que utiliza valor vari�vel
$NO_HAVE = 'NO HAVE';

//Identifica��o de empresas
$IDENTIFICACAO_EMPRESA_ADMINISTRADORA_CARTAO = 1;
$IDENTIFICACAO_EMPRESA_PAGAMENTOS = 0;

// Status Empresa em rela��o aos arquivos do BACEN
$STATUS_ARQUIVO_BACEN = array(
	'NAO_CONTABILIZOU'	 	=> '0', // N�O contabilizou
	'CONTABILIZOU' 		 	=> '1', // Contabilizou;
	'AGUARDANDO_RETORNO_BACEN' 	=> '2', // J� em arquivo aguardando arquivo de retorno do BACEN.
);


// Status de usu�rios Gamers (ug_ativo)
$STATUS_USUARIO = array(
	'USER_ATIVO'    => 1,
	'USER_INATIVO'  => 2,
	'USER_FRAUDE'   => 3,
	'USER_SUSPEITO' => 4,
	'USER_SUSPENSO' => 5,
	'USER_FALTA_DE_USO' => 6,
);
$STATUS_USUARIO_LEGENDA = array(
	1 => 'Ativo',
	2 => 'Inativo',
	3 => 'Fraude',
	4 => 'Suspeita de Fraude',
	5 => 'Bloqueio Tempor�rio e Preventivo de Fraude',
	6 => 'Falta de Uso'
);

/*
 * Costante de porcentagem para calcular se o valor do saldo � ela% maior que o valor da compra para exibir mensagem de alerta
 */
define("PERCENT_SALDO_MAIOR_QUE_COMPRA", '0.07');
?>