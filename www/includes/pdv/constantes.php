<?php require_once __DIR__ . '/../constantes_url.php'; ?>
<?php
//Constantes
//------------------------------------------------------------------------------------------------
$BOLETO_TAXA_ADICIONAL = 2.00; //TAXA BOLETO COBRADO PDV
$server_url = "" . EPREPAG_URL . "";
if(checkIP()) {
    $server_url = $_SERVER['SERVER_NAME'];
}
$PREPAG_DOMINIO = "http://".$server_url;	
$ENTRE_CONTATO_CENTRAL = "Por favor, entre em contato com nossa Central de Atendimento através do e-mail suporte@e-prepag.com.br. \nObrigado.";
$GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY_LH'] = 8576; // cadastro de ReinaldoLH (reinaldops@hotmail.com)

//Log de comandos SQL	
//	$ARQUIVO_LOG_SQL_EXECUTE_QUERY = $raiz_do_projeto . "log/log_dist_commerce_sql_execute_query.txt";	

//Log de vendas Bilheteria	
$ARQUIVO_LOG_VENDAS_BILHETERIA = $raiz_do_projeto . "log/log_ingressos.txt";	
$RC4_PASSW = "@#\$eprepag";

//Diretorio de arquivos upload - comprovante
$FOLDER_COMMERCE_UPLOAD = $raiz_do_projeto . "/backoffice/offweb/upload_arquivos/dist_commerce/";
$FOLDER_COMMERCE_UPLOAD_TMP = $raiz_do_projeto . "/backoffice/offweb/upload_arquivos/dist_commerce/tmp/";

//Imagens dos produtos e modelos
$IMAGES_PRODUTO_EXTENSOES = array("gif", "jpg", "png");
$URL_DIR_IMAGES_PRODUTO = "/imagens/pdv/produtos/";
$FIS_DIR_IMAGES_PRODUTO = $raiz_do_projeto . "public_html/imagens/pdv/produtos/";

//Imagens dos banners
$IMAGES_BANNER_EXTENSOES = array("gif", "jpg", "png", "jpeg");
$URL_DIR_IMAGES_BANNER = "/imagens/pdv/banners/";
$FIS_DIR_IMAGES_BANNER = $raiz_do_projeto . "public_html/imagens/pdv/banners/";

//BOLETO
//------------------------------------------------------------------------------------------------
$BOLETO_DIR = $raiz_do_projeto . "banco/boletos/";
$BOLETO_TAXA_ADICIONAL 						= 2.00;

//Variaveis Banco do Brasil
$BOLETO_MONEY_BANCO_DO_BRASIL_COD_BANCO 	= "001";

//Variaveis Itau
$BOLETO_MONEY_BANCO_ITAU_COD_BANCO 		= "341";
$BOLETO_MONEY_ITAU_QTDE_DIAS_VENCIMENTO         = 3;
$BOLETO_MONEY_ITAU_COD_BANCO 			= 341;
$BOLETO_MONEY_ITAU_TAXA_ADICIONAL 		= 2; //TAXA BOLETO COBRADO PDV
$BOLETO_MONEY_ITAU_CEDENTE_AGENCIA 		= "0444";
$BOLETO_MONEY_ITAU_CEDENTE_AGENCIA_DV           = "1";
$BOLETO_MONEY_ITAU_CEDENTE_CONTA 		= "77567";
$BOLETO_MONEY_ITAU_CEDENTE_CONTA_DV             = "0"; 
        $BOLETO_MONEY_ITAU_CEDENTE_CONTA_NOVA 		= "89756";	
        $BOLETO_MONEY_ITAU_CEDENTE_CONTA_DV_NOVA	= "5"; 	
        $BOLETO_MONEY_ITAU_TAXA_CUSTO_BANCO_NOVA 	= 1.80;	// é a taxa cobrada pelo Itaú da E-Prepag para boletos na nova conta
$BOLETO_MONEY_ITAU_CARTEIRA                   	= "176";
$BOLETO_MONEY_ITAU_JUROS_AO_MES_PRCT            = 00.00; //usar valor numerico computacional 10.0 e nao 10,0

//Variaveis Caixa
$BOLETO_MONEY_CAIXA_QTDE_DIAS_VENCIMENTO 	= 2;
$BOLETO_MONEY_CAIXA_COD_BANCO 				= 104;
$BOLETO_MONEY_CAIXA_TAXA_ADICIONAL 			= 2.00; //TAXA BOLETO COBRADO PDV

//Variaveis Santander
$BOLETO_BANCO_BANESPA_COD_BANCO = "033";
$BOLETO_BANESPA_CODIGO_CEDENTE = "6377980";
$BOLETO_BANESPA_CARTEIRA  = "102";
$BOLETO_BANESPA_CEDENTE_AGENCIA = "3793";
$BOLETO_BANESPA_CEDENTE_AGENCIA_DV = "1";
$BOLETO_BANESPA_CEDENTE_CONTA = "130062938";
$BOLETO_BANESPA_CEDENTE_CONTA_DV = "";
$BOLETO_BANESPA_QTDE_DIAS_VENCIMENTO = 3;
$BOLETO_BANESPA_TAXA_CUSTO_BANCO = 1.02; 
$BOLETO_BANESPA_TAXA_ADICIONAL = 2;  //TAXA BOLETO COBRADO DO GAMER

//Variaveis Bradesco
$BOLETO_MONEY_BRADESCO_QTDE_DIAS_VENCIMENTO = 3;
$BOLETO_MONEY_BRADESCO_COD_BANCO 			= 237;
$BOLETO_MONEY_BRADESCO_TAXA_ADICIONAL 		= 2.00; //TAXA BOLETO COBRADO PDV
$BOLETO_MONEY_BRADESCO_CEDENTE_AGENCIA 		= "2062";
$BOLETO_MONEY_BRADESCO_CEDENTE_AGENCIA_DV 	= "1";
$BOLETO_MONEY_BRADESCO_CEDENTE_CONTA 		= "0020459";	//"0020888";
$BOLETO_MONEY_BRADESCO_CEDENTE_CONTA_NOVA 	= "0001689";     //Conta nova da EPP ADMINISTRADORA DE CARTOES (julho/18)
$BOLETO_MONEY_BRADESCO_CEDENTE_CONTA_DV 	= "5"; 	//"4";
$BOLETO_MONEY_BRADESCO_CEDENTE_CONTA_NOVA_DV = "6";         //Digito verificador da conta nova da EPP ADMINISTRADORA DE CARTOES (julho/18)
//	$BOLETO_MONEY_BRADESCO_CARTEIRA				= "06";
// Nova carteira em 2014-03-18
$BOLETO_MONEY_BRADESCO_CARTEIRA			= "26";             //CARTEIRA NOVA - AGOSTO 2018
$BOLETO_MONEY_BRADESCO_JUROS_AO_MES_PRCT	= 24.32; //usar valor numerico computacional 10.0 e nao 10,0

// ASAAS
$BOLETO_MONEY_ASAAS_COD_BANCO 			= 461;

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

//Regioes
//------------------------------------------------------------------------------------------------
$SIGLA_REGIOES = array( 
                                        "CO" => array ("DF","GO","MS", "MT"),	
                                        "N"	 =>	array ("AC","AM", "AP","PA","RO", "RR","TO"),
                                        "NE" =>	array ("AL","BA", "CE","MA","PB", "PE", "PI","RN","SE"),
                                        "S"	 =>	array ("PR","RS", "SC"),
                                        "SE" => array ("ES","MG","RJ","SP"),
                                        );

//Regioes Legenda
//------------------------------------------------------------------------------------------------
$SIGLA_REGIOES_LEG = array( 
                                        "CO" => "Centro-Oeste",	
                                        "N"	 =>	"Norte",
                                        "NE" =>	"Nordeste",
                                        "S"	 =>	"Sul",
                                        "SE" => "Sudeste",
                                        );

//Tipos de Logradouro
//------------------------------------------------------------------------------------------------
$TIPOS_END = array("Aeroporto", "Alameda", "Área", "Avenida", "Campo", "Chácara", "Colônia", "Condomínio", "Conjunto", "Distrito", "Esplanada", "Estação", "Estrada", "Favela", "Fazenda", "Feira", "Jardim", "Ladeira", "Lago", "Lagoa", "Largo", "Loteamento", "Morro", "Núcleo", "Parque", "Passarela", "Páteo", "Praça", "Quadra", "Recanto", "Residencial", "Rodovia", "Rua", "Setor", "Sítio", "Travessa", "Trecho", "Trevo", "Vale", "Vereda", "Via", "Viaduto");

//Formas de Pagamento
//------------------------------------------------------------------------------------------------
// 1: Transferência Bancária, DOC Eletrônico, Depósito ou Outros
//		Nao eh uma forma de pagamento propriamente dita,
//		pois o cliente ainda nao pagou, ou pelo menos,
//		nao entrou com os dados de pagamento.
// 2: Boleto Bancario
$FORMAS_PAGAMENTO = array(	 'DEP_DOC_TRANSF' => '1'
                                                        ,'BOLETO_BANCARIO'=> '2'
//								,'REDECARD_MASTERCARD'=> '3'
//								,'REDECARD_DINERS'=> '4'
//								,'REPASSE'=> '5'
                                                        ,'TRANSFERENCIA_ENTRE_CONTAS_BRADESCO' => '5'
                                                        ,'PAGAMENTO_FACIL_BRADESCO_DEBITO'=> '6' 
                                                        ,'PAGAMENTO_FACIL_BRADESCO_CREDITO'=> '7' 
                                                        //,'PAGAMENTO_BB_DEBITO_SUA_EMPRESA'=> '8' 
                                                        ,'PAGAMENTO_BB_DEBITO_SUA_CONTA'=> '9' 
                                                        ,'PAGAMENTO_BANCO_ITAU_ONLINE'=> 'A'
                                                        ,'PAGAMENTO_PIX' => 'R',
                                                );

$PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC = 10;	// para tabelas tb_venda_games e tb_dist_venda_games o campo vg_tipo_pagamento é numerico
                                                                                        // não pode usar $FORMAS_PAGAMENTO['PAGAMENTO_BANCO_ITAU_ONLINE']

// Banco E-Prepag - Testes
$PAGAMENTO_BANCO_EPP_ONLINE = 'Z';			// Banco E-Prepag de TESTES
$PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC = 999;	// para tabelas tb_venda_games e tb_dist_venda_games o campo vg_tipo_pagamento é numerico
                                                                                        // não pode usar $PAGAMENTO_BANCO_EPP_ONLINE
$FORMAS_PAGAMENTO_DESCRICAO_EPP = 'Pagamento de testes';
$FORMAS_PAGAMENTO_ICONES_EPP = 'botao_conta_epp_2.gif';
$BANCO_EPP_TAXA_DE_SERVICO = 0;
$BOLETO_MONEY_BANCO_EPP_COD_BANCO = "999";

// Pagamento PIX
$PAGAMENTO_PIX_NUMERIC = 24;
$PAGAMENTO_PIX_COD_BANCO = "400"; 
$PAGAMENTO_PIX_NOME_BANCO = "PIX";
$PAGAMENTO_PIX_TAXA = 0;

$FORMAS_PAGAMENTO_DESCRICAO = array( '1' => 'Depósito'
                                                                        ,'2' => 'Boleto Bancário'
//										,'3' => 'MasterCard'
//										,'4' => 'Diners'
//										,'5' => 'Repasse'
                                                                        ,'5' => 'Transferência entre contas Bradesco'
                                                                        ,'6' => 'Pagamento Fácil Bradesco - Débito'
                                                                        ,'7' => 'Pagamento Fácil Bradesco - Crédito'
                                                                        //,'8' => 'Pagamento BB - Débito sua Empresa'
                                                                        ,'9' => 'Pagamento BB - Débito sua Conta'
                                                                        ,'A' => 'Pagamento Itau - À vista (Transferência)'
                                                                        ,'R' => 'Pix'
                                                                        );

$FORMAS_PAGAMENTO_DESCRICAO_NEW = array( '1' => 'Depósito'
                                                                        ,'2' => 'Cartão'
                                                                        ,'5' => 'Transferência entre contas Bradesco'
                                                                        ,'6' => 'Pagamento Fácil Bradesco - Débito'
                                                                        ,'7' => 'Pagamento Fácil Bradesco - Crédito'
                                                                        ,'9' => 'Pagamento BB - Débito sua Conta'
                                                                        ,'A' => 'Pagamento Itau - À vista (Transferência)'
                                                                        );


$FORMAS_PAGAMENTO_ICONES    = array( '1' => 'p_transf.gif'
                                                                        ,'2' => 'p_boleto.jpg'
//										,'3' => 'p_mastercard.jpg'
//										,'4' => 'p_diners.jpg'
                                                                        ,'5' => 'bt_conta_brad_tranf_onli.png' 
                                                                        ,'6' => 'bt_conta_brad_electron2o.png' 
                                                                        ,'7' => 'bt_pagto_facil.gif'
                                                                        //,'8' => 'imgDebitoSuaEmpresa.gif'
                                                                        ,'9' => 'bt_conta_BB_menor_debito.png'
                                                                        ,'A' => 'botao_conta_itau_.jpg'
                                                                        ,'R' => 'ico_pix.png'
                                                                        );
																		
																		$CATEGORIA = array( 
																			'0' => 'Normal'
																			,'1' => 'Vip'
																			,'2' => 'Master'
																			,'3' => 'Black'
																			,'4' => 'Gold'
                                                                        );


$BRADESCO_TRANSFERENCIA_ENTRE_CONTAS_TAXA_ADICIONAL = 0;	// R$1.20/transação
$BRADESCO_DEBITO_EM_CONTA_TAXA_ADICIONAL = 0;	// R$0.00/transação

$BANCO_DO_BRASIL_TAXA_DE_SERVICO	= 2.99;	// R$1.00/transação

$BANCO_ITAU_TAXA_DE_SERVICO			= 1.49;	// R$0.50/transação 

//Status da venda
//------------------------------------------------------------------------------------------------
//	1: Pedido efetuado, aguardando dados do pagamento
//	2: Dados do pagamento recebido, aguardando confirmacao de pagamento
//	3: Pagamento confirmado e liberado para venda
//	4: Processamento realizado. Crédito será encaminhado para o usuário
//	5: Venda realizada. Crédito encaminhado para o usuário
//	6: Venda cancelada.
$STATUS_VENDA = array(	'PEDIDO_EFETUADO' 			=> '1',
                                                'PEDIDO_EM_STANDBY' 		=> '2',
                                                'AGUARDANDO_PROCESSAMENTO'	=> '3',
                                                'PROCESSAMENTO_REALIZADO'	=> '4',
                                                'VENDA_REALIZADA' 			=> '5',
                                                'VENDA_CANCELADA'			=> '6',

                                                'DADOS_PAGTO_RECEBIDO' 		=> '7',
                                                'PAGTO_CONFIRMADO' 			=> '8'
                                                );
//Ate o primeiro ponto final eh o chamado Descricao Curta, usado em alguns lugares
//Para novos status, evitar passar de 25 caracteres ateh o primeiro ponto final.
$STATUS_VENDA_DESCRICAO = array('1' => 'Pedido efetuado.',
                                                                '2' => 'Pedido aguardando liberação.',
                                                                '3' => 'Pedido aguardando processamento.',
                                                                '4' => 'Pedido processado. Crédito será encaminhado.',
                                                                '5' => 'Venda realizada. Pins disponíveis para impressão.',
                                                                '6'	=> 'Venda cancelada.',
                                                                '7' => 'Dados do pagamento recebidos',
                                                                '8' => 'Pagamento confirmado'
                                                                );
$STATUS_VENDA_ICONES    = array('1' => 'Blue-5-1.gif',
                                                                '2' => 'Blue-5-2.gif',
                                                                '3' => 'Blue-5-3.gif',
                                                                '4' => 'Blue-5-4.gif',
                                                                '5' => 'Blue-5-5.gif',
                                                                '6'	=> 'cancel.gif',
                                                                '7' => '',
                                                                '8' => ''
                                                                );


//Pagamento
//------------------------------------------------------------------------------------------------
//Pagamento Bancos
$PAGTO_BANCOS	= array('001' => 'Banco do Brasil', 
                                                '033' => 'Santander',
                                                '237' => 'Bradesco / Banco Postal',
                                                '104' => 'Caixa Econômica Federal / Casas Lotéricas');

//Pagamento Locais
$PAGTO_LOCAIS	= array('001' => array(	'01' => 'Caixa Automático / Auto-Atendimento', 
                                                                                '02' => 'Agência / Boca do Caixa',
                                                                                '03' => 'Transferência pela Internet / DOC',
                                                                                '04' => 'Depósito em conta corrente em dinheiro',
                                                                                '05' => 'Auto-Atendimento Transferência entre contas correntes',
                                                                                '06' => 'Remessa do exterior'
                                                                                ),
                                                '237' => array(	'01' => 'Caixa Automático / Auto-Atendimento', 
                                                                                '02' => 'Agência / Boca do Caixa',
                                                                                '03' => 'Transferência pela Internet / DOC',
                                                                                '04' => 'Banco Postal',
                                                                                '05' => 'BDN – Deposito em conta corrente',
                                                                                '06' => 'Remessa do exterior'
                                                                                ),
                                                '104' => array(	'01' => 'Caixa Automático / Auto-Atendimento', 
                                                                                '02' => 'Agência / Boca do Caixa',
                                                                                '03' => 'Transferência pela Internet / DOC',
                                                                                '04' => 'Casas Lotéricas',
                                                                                '05' => 'Caixa Aqui',
                                                                                '06' => 'Remessa do exterior'
                                                                                )
                                                );

//Nome do Numero do Documento
$PAGTO_NOME_DOCTO = array(	'001' => array( '01' => 'Nro. do Envelope', 
                                                                                        '02' => 'Agência de Origem ou Débito',
                                                                                        '03' => 'Agência de Origem ou Débito;Conta de Origem ou Débito',
                                                                                        '04' => 'Nr. Documento',
                                                                                        '05' => 'Agência de débito;Conta de débito',
                                                                                        '06' => 'Remetente;Banco;País;Moeda'
                                                                                        ),
                                                        '237' => array(	'01' => 'Nro. do Terminal;Nro. da Transação;Agência Tomadora', 
                                                                                        '02' => 'Agência Tomadora;Nr. Terminal',
                                                                                        '03' => 'Nro. do Documento;Agencia de Débito',
                                                                                        '04' => 'Agência Relacionada e PACB',
                                                                                        '05' => 'Nro. do Terminal;Nro. da Transação',
                                                                                        '06' => 'Remetente;Banco;País;Moeda'
                                                                                        ),
                                                        '104' => array(	'01' => 'Nro. do Envelope', 
                                                                                        '02' => 'Nro. do Documento',
                                                                                        '03' => 'Código da Operação',
                                                                                        '04' => 'Horário do Depósito',
                                                                                        '05' => 'Código da Operação;Horário do Depósito',
                                                                                        '06' => 'Remetente;Banco;País;Moeda'
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
$USUARIO_GAMES_LOG_TIPOS = array('CRIACAO_DO_CADASTRO' 			=> '1', 
                                                                 'LOGIN' 						=> '2',
                                                                 'ALTERACAO_DO_CADASTRO' 		=> '3',
                                                                 'TROCA_DE_SENHA' 				=> '4',
                                                                 'VENDA' 						=> '5',
                                                                 'INFORMA_DADOS_DE_PAGAMENTO' 	=> '6',
                                                                 'LOGOUT' 						=> '7',
                                                                 'LOGIN_OPERADOR' 				=> '8',
                                                                 'TROCA_DE_SENHA_OPERADOR'		=> '9',
                                                                 'CADASTRA_OPERADOR'			=> '10',
                                                                 'BANNER'						=> '11',
                                                                 'FALHA_LOGIN_CONSECUTIVA'  => '12',
                                                                 'IP_DIFERENTE'            => '13',
                                                                 'SEMANA_HORARIO_DIFERENTE' => '14',
                                                                 'LOGIN_SIMULTANEO'         => '15',
                                                                 'ATUALIZACAO_LOGIN_SIMULTANEO' => '16',
                                                                 'ATUALIZACAO_LOGIN_IP' => '17',
                                                                 'ATUALIZACAO_LOGIN_HORARIO_INI_SEGUNDA' => '18',
                                                                 'ATUALIZACAO_LOGIN_HORARIO_FIM_SEGUNDA' => '19',
                                                                 'ATUALIZACAO_LOGIN_HORARIO_INI_TERCA' => '20',
                                                                 'ATUALIZACAO_LOGIN_HORARIO_FIM_TERCA' => '21',
                                                                 'ATUALIZACAO_LOGIN_HORARIO_INI_QUARTA' => '22',
                                                                 'ATUALIZACAO_LOGIN_HORARIO_FIM_QUARTA' => '23',
                                                                 'ATUALIZACAO_LOGIN_HORARIO_INI_QUINTA' => '24',
                                                                 'ATUALIZACAO_LOGIN_HORARIO_FIM_QUINTA' => '25',
                                                                 'ATUALIZACAO_LOGIN_HORARIO_INI_SEXTA' => '26',
                                                                 'ATUALIZACAO_LOGIN_HORARIO_FIM_SEXTA' => '27',
                                                                 'ATUALIZACAO_LOGIN_HORARIO_INI_SABADO' => '28',
                                                                 'ATUALIZACAO_LOGIN_HORARIO_FIM_SABADO' => '29',
                                                                 'ATUALIZACAO_LOGIN_HORARIO_INI_DOMINGO' => '30',
                                                                 'ATUALIZACAO_LOGIN_HORARIO_FIM_DOMINGO' => '31', 
                                                                 'CARREGA_SESSAO_NEXCAFE' => '32',	
                                                                 'LOGIN_AUTOMATICO_VIA_NEXCAFE' => '33',
                                                                 'LOGIN_VIA_FORMULARIO_NEXCAFE' => '34',
                                                                 'ERRO_CARREGA_SESSAO_NEXCAFE' => '35',
                                                                 'ERRO_LOGIN_AUTOMATICO_VIA_NEXCAFE' => '36',
                                                                 'ERRO_LOGIN_VIA_FORMULARIO_NEXCAFE' => '37',
                                                                 'UPDATE_REGISTRO_NEXCAFE' => '38', 
                                                                 'CADASTRO_LANHOUSE_VIA_NEXCAFE' => '39',

                                                                 );
$USUARIO_GAMES_LOG_TIPOS_DESCRICAO = array(	'1' => 'Criação de Cadastro',
                                                                                        '2' => 'Login',
                                                                                        '3' => 'Alteração cadastral',
                                                                                        '4' => 'Troca de senha',
                                                                                        '5' => 'Realizou um pedido',
                                                                                        '6'	=> 'Informou dados do pagamento',
                                                                                        '7'	=> 'Encerrou a sessão',
                                                                                        '8' => 'Login operador',
                                                                                        '9' => 'Troca de senha operador',
                                                                                        '10' => 'Cadastra operador',
                                                                                        '11' => 'Acessou um banner de promoção',
                                                                                        '12' => 'Três tentativas de login incorreto',
                                                                                        '13' => 'Ip da conexão diferente do ip cadastrado',
                                                                                        '14' => 'Diferente do dia da semana ou horário cadastrado',
                                                                                        '15' => 'Tentativa de login simultaneo',
                                                                                        '16' => 'Alteração na opção de login simultaneo',
                                                                                        '17' => 'Alteração no cadastro de ip',
                                                                                        '18' => 'Alteração no Horario de Inicio - Segunda',
                                                                                        '19' => 'Alteração no Horario do Fim - Segunda',
                                                                                        '20' => 'Alteração no Horario de Inicio - Terça',
                                                                                        '21' => 'Alteração no Horario do Fim - Terça',
                                                                                        '22' => 'Alteração no Horario de Inicio - Quarta',
                                                                                        '23' => 'Alteração no Horario do Fim - Quarta',
                                                                                        '24' => 'Alteração no Horario de Inicio - Quinta',
                                                                                        '25' => 'Alteração no Horario do Fim - Quinta',
                                                                                        '26' => 'Alteração no Horario de Inicio - Sexta',
                                                                                        '27' => 'Alteração no Horario do Fim - Sexta',
                                                                                        '28' => 'Alteração no Horario de Inicio - Sabado',
                                                                                        '29' => 'Alteração no Horario do Fim - Sabado',
                                                                                        '30' => 'Alteração no Horario de Inicio - Domingo',
                                                                                        '31' => 'Alteração no Horario do Fim - Domingo',
                                                                                        '32' => 'Carrega Sessão via NexCafe',
                                                                                        '33' => 'Login Automático via NexCafe',
                                                                                        '34' => 'Login via Formulário NexCafe',
                                                                                        '35' => 'Erro ao Tentar Carregar Sessão via NexCafe',
                                                                                        '36' => 'Erro ao Tentar Login Automático via NexCafe',
                                                                                        '37' => 'Erro ao Tentar Login via Formulário NexCafe',
                                                                                        '38' => 'Atualiza Dados do NexCafé na Conta E-Prepag',
                                                                                        '39' => 'Cadastro de Lan House via NexCafé',
                                                                                        );

$USUARIO_GAMES_OPERADOR_TIPOS_NOME = array(	'0'	=>	'Funcionário 2', 
                                                                                        '1'	=>	'Funcionário 1');
$USUARIO_GAMES_OPERADOR_TIPOS = array(	'FUNCIONARIO_2' 		=> 0, 
                                                                                'FUNCIONARIO_1' 		=> 1);
$USUARIO_GAMES_OPERADOR_TIPOS_DESCRICAO = array(	'0' => 'Funcionário 2: Impressão de senhas',
                                                                                                        '1' => 'Funcionário 1: Compras e impressão de senhas');

//Dados de repasse do pagamento Prepag
//------------------------------------------------------------------------------------------------
$DADOS_PAGTO_REPASSE_PREPAG = array('BANCO' 			=> '0001/BANCO DO BRASIL', 
                                                                        'AGENCIA' 			=> '1755/CORPORATE-RIO DE JAN',
                                                                        'CONTA' 			=> '5.985-4',
                                                                        'COD_ID_REDE_PREPAG'=> '1700136',
                                                                        'FAVORECIDO' 		=> 'TNL PCS S/A',
                                                                        'CNPJ_FAVORECIDO' 	=> '4.164.616/0001-59',
                                                                        'PERC_DESCONTO'		=> 8.5);

//Cadastro
$CADASTRO_CARTOES 		= array('VISA'		=> 'Visa',
                                                                'MASTERCARD'=> 'MasterCard',
                                                                'AMEX' 		=> 'Amex',
                                                                'OUTROS' 	=> 'Outros');

$CADASTRO_COMUNICACAO 	= array('POSTER' 	=> 'Poster',
                                                                'MOUSE_PAD' => 'Mouse Pad',
                                                                'DISPLAY' 	=> 'Display',
                                                                'TESTEIRA' 	=> 'Testeira',
                                                                'FOLHETO' 	=> 'Folheto');

$CADASTRO_FATURAMENTO 	= array('1' 		=> 'Até R$5.000,00',
                                                                '2' 		=> 'R$  5.000,01 a R$ 10.000,00',
                                                                '3' 		=> 'R$ 10.000,01 a R$ 20.000,00',
                                                                '4' 		=> 'Mais de R$ 20.000,00');

$CADASTRO_COMPUTADORES 	= array('1' 		=> 'Até 05',
                                                                '2' 		=> '06 a 10',
                                                                '3' 		=> '11 a 20',
                                                                '4' 		=> 'Mais de 20');

// Dados de Gestão de Risco LH
//------------------------------------------------------------------------------------------------
$RISCO_CLASSIFICACAO = array(	'PÓS-PAGO' => '1',	
                                                                'PRÉ-PAGO' => '2'	
                                                        );

$RISCO_CLASSIFICACAO_NOMES = array(	'1' => 'PÓS-PAGO',	
                                                                        '2' => 'PRÉ-PAGO'	
                                                        );

$RISCO_CLASSIFICACAO_DESCRICAO = array( '1' => 'LH pós-paga, com crédito semanal',
                                                                                '2' => 'LH só compra pré-pago'
                                                                                );

// Dados de Substatus de LH
//------------------------------------------------------------------------------------------------


$SUBSTATUS_LH = array(
                                                "-1" => "Não definido",
                                                "1" => "Pendente de Contato e Análise",
                                                "2" => "Loja não Localizada",
                                                "3" => "Representante Divergente",
                                                "4" => "Cadastro não Aprovado",
                                                "5" => "Cnpj Baixado",
                                                "6" => "Não quer mais vender",
                                                "7" => "Bloqueado por fraude",
                                                "8" => "Pré-Cadastro/Prospecção", 
                                                "9" => "Ainda não fez 1º compra",
                                                "10" => "Inadimplentes",
                                                "11" => "PDV aprovado",
												"12" => "PDV inativo por falta de uso",
												"13" => "Bloqueio temporário e preventivo à fraudes"
                                                );

$SUBSTATUS_LH_PAG_ONLINE = array(
                                                "-1" => "Não definido",
                                                "1" => "Não possui conta nos bancos disponíveis",
                                                "2" => "Não consegue efetuar pagamento online",
                                                "3" => "Não quer mais vender",
                                                "4" => "Tem receio de efetuar transações online",
                                                "5" => "Telefone incorreto",
                                                "6" => "Tentativa de contato sem sucesso",
                                                "7" => "Retornar mais tarde",
                                                "8" => "Não conhecia essa forma de pagamento",
                                                "9" => "Aceitou utilizar o pagamento online",
                                        );

/*
// Gestão de Risco
// Usuários cartão permitidos pela Cielo
$RISCO_CIELO_TOTAL_DIARIO = 200;
$RISCO_CIELO_PAGAMENTOS_DIARIO = 10;
$RISCO_CIELO_VALOR_MIN_PARA_TAXA = 0;
$RISCO_CIELO_VALOR_MIN = 0;
$RISCO_CIELO_VALOR_MAX = 200;
*/

/*
 *  0 => DIÁRIO
 *  1 => SEMANAL
 */

$TIPO_LIMITE = 0;

//	Lans Pre - Pagamento Online = no max R$900,00 por día por usuário (ver getVendasLHTotalDiarioOnline())
$RISCO_LANS_PRE_TOTAL_DIARIO = 5000; //1200
$RISCO_LANS_PRE_PAGAMENTOS_DIARIO = 60; //10
$RISCO_LANS_PRE_TOTAL_SEMANAL = 1200;
$RISCO_LANS_PRE_PAGAMENTOS_SEMANAL = 60; //10
$RISCO_LANS_PRE_VALOR_MIN_PARA_TAXA = 80;
$RISCO_LANS_PRE_VALOR_MIN = 80;
$RISCO_LANS_PRE_VALOR_MAX = 5000; //1200
$RISCO_LANS_PRE_QTDE_PRODUTO = 5;
$RISCO_LANS_PRE_QTDE_MODELO = 60;

//	Lans Pre - VIP
$RISCO_LANS_PRE_VIP_TOTAL_DIARIO = 7500; //5000
$RISCO_LANS_PRE_VIP_PAGAMENTOS_DIARIO = 60; //15
$RISCO_LANS_PRE_VIP_TOTAL_SEMANAL = 5000;
$RISCO_LANS_PRE_VIP_PAGAMENTOS_SEMANAL = 60; //15
$RISCO_LANS_PRE_VIP_VALOR_MIN_PARA_TAXA = 100;
$RISCO_LANS_PRE_VIP_VALOR_MIN = 100;
$RISCO_LANS_PRE_VIP_VALOR_MAX = 7500; //5000
$RISCO_LANS_PRE_VIP_QTDE_PRODUTO = 10;
$RISCO_LANS_PRE_VIP_QTDE_MODELO = 70;


//	Lans Pre - MASTER
$RISCO_LANS_PRE_MASTER_TOTAL_DIARIO = 10000;
$RISCO_LANS_PRE_MASTER_PAGAMENTOS_DIARIO = 60; //20
$RISCO_LANS_PRE_MASTER_TOTAL_SEMANAL = 10000;
$RISCO_LANS_PRE_MASTER_PAGAMENTOS_SEMANAL = 60; //20
$RISCO_LANS_PRE_MASTER_VALOR_MIN_PARA_TAXA = 100;
$RISCO_LANS_PRE_MASTER_VALOR_MIN = 100;
$RISCO_LANS_PRE_MASTER_VALOR_MAX = 10000;
$RISCO_LANS_PRE_MASTER_QTDE_PRODUTO = 15;
$RISCO_LANS_PRE_MASTER_QTDE_MODELO = 80;


//	Lans Pre - BLACK
$RISCO_LANS_PRE_BLACK_TOTAL_DIARIO = 30000;
$RISCO_LANS_PRE_BLACK_PAGAMENTOS_DIARIO = 60; //20
$RISCO_LANS_PRE_BLACK_TOTAL_SEMANAL = 30000;
$RISCO_LANS_PRE_BLACK_PAGAMENTOS_SEMANAL = 60; //20
$RISCO_LANS_PRE_BLACK_VALOR_MIN_PARA_TAXA = 100;
$RISCO_LANS_PRE_BLACK_VALOR_MIN = 100;
$RISCO_LANS_PRE_BLACK_VALOR_MAX = 30000;
$RISCO_LANS_PRE_BLACK_QTDE_PRODUTO = 20;
$RISCO_LANS_PRE_BLACK_QTDE_MODELO = 90;


//	Lans Pre - GOLD
$RISCO_LANS_PRE_GOLD_TOTAL_DIARIO = 50000;
$RISCO_LANS_PRE_GOLD_PAGAMENTOS_DIARIO = 60; //20
$RISCO_LANS_PRE_GOLD_TOTAL_SEMANAL = 100000;
$RISCO_LANS_PRE_GOLD_PAGAMENTOS_SEMANAL = 60; //20
$RISCO_LANS_PRE_GOLD_VALOR_MIN_PARA_TAXA = 100;
$RISCO_LANS_PRE_GOLD_VALOR_MIN = 100; 
$RISCO_LANS_PRE_GOLD_VALOR_MAX = 50000;
$RISCO_LANS_PRE_GOLD_QTDE_PRODUTO = 25;
$RISCO_LANS_PRE_GOLD_QTDE_MODELO = 100;


//	$RISCO_LANS_POS_TOTAL_DIARIO = 450;
//	$RISCO_LANS_POS_PAGAMENTOS_DIARIO = 10;

// Dados do Balanco
//-------------------------------------------------------------------------------------------------
$BALANCO_DATA_ABERTURA = '2007-04-30';
$BALANCO_ZERO_FLOAT = pow(10,-3);

// Para diferenciar usuários Frequente/Atrasados/Abandonados
$ATRASO_LANS_DIAS_LIM_1 = 15;
$ATRASO_LANS_DIAS_LIM_2 = 30;

// Busca de lans - Ativa/desativa
$CONST_ATIVA_BUSCA_LANS_MESES_APOS_CADASTRO_PARA_ATIVAR = 2;
$CONST_ATIVA_BUSCA_LANS_MESES_APOS_ULTIMO_LOGIN = 3;
$CONST_ATIVA_BUSCA_LANS_MESES_APOS_PEDIDOS_PARA_ATIVAR = 6;
$CONST_DESATIVA_BUSCA_LANS_MESES_APOS_SEM_PEDIDOS_PARA_DESATIVAR = 6;

$CONST_ATIVA_BUSCA_LANS_BLACK_LIST = "3, 5, 6, 17, 52, 273, 302, 389, 468, 505, 2652, 2857, 3254, 3357, 3362, 3366, 3480, 3903, 3904, 4404, 4425, 4453, 4708, 4842, 4907, 4929, 4992, 5041, 5045, 5206, 5219, 5317, 5331, 5346, 5525, 5698, 6249, 6444, 6533, 6534, 6538, 6542, 6664, 6680, 6739, 6774, 6776, 6778, 6781, 6796, 6808, 6812, 6818, 6852, 6854, 6857, 6864, 6865, 6885, 6886, 6887, 6888, 6911, 6918, 6968, 6988, 7124, 7125, 7229, 7270, 7283, 7285, 7314, 7493, 7502, 7644, 7751, 7753, 7754, 7763, 7788, 7794, 6217, 6502, 6812, 7021, 7451, 4421, 7608, 9008, 6059, 8652, 9861, 10829, 11165, 11445, 10945, 8764, 7715, 11521, 5590, 10485, 9756, 9109, 5218, 10899, 10594, 7564, 11675, 3795, 10883, 11700, 5665, 11190, 11908, 6011, 10869, 11166, 9890, 5597, 9457, 11668, 10840, 2658, 9809, 11421, 10669, 11208, 8032, 11606, 7041, 9051, 6131, 9132, 7279, 11400, 11307, 10001, 11949, 9723, 5418, 7466, 11414, 11387, 10024, 11594, 11656, 11116, 11377, 11131, 10538, 5341, 522, 11523, 11218, 10794, 9500, 5104, 11555, 4584, 6205, 9608, 7005, 12370";

//Constante que limita o número de PINS permitidos por produto em uma venda
$LIMITE_QUANTIDADE_PINS = 100;

//Identificação de empresas
$IDENTIFICACAO_EMPRESA_ADMINISTRADORA_CARTAO = 1;
$IDENTIFICACAO_EMPRESA_PAGAMENTOS = 0;

//Limite de valor para publishers com facilitadora (publishers que utilizam o BEXS)
$LIMITE_VALOR_PUBLISHERS_COM_FACILITADORA = 3000;

//Identificador de modelo de produto que utiliza valor variável
$NO_HAVE = 'NO HAVE';

?>