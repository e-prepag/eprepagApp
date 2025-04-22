<?php
/*************************************************************
********** Variaveis necessarias ao módulo PIN EPP ***********
**************************************************************/

//ID dos Publishers EPP CASH
$dd_operadora_EPP_Cash = 49;
$dd_operadora_EPP_Cash_LH = 53;

//Tamanho Máximo do PIN
$PIN_STORE_TAMANHO = 16;

//Tamanho Único do PIN Card
$PIN_CARD_TAMANHO = 18;

//Número Máximo de Tentativas em um determinado Período
$PIN_STORE_TENTATIVAS = 10;

//Intervalo Máximo de Período considerado no número de Tentativas acima
$PIN_STORE_PERIODO = 10;

//Contem o valor máximo de PINs no pagamento
$PAGTO_RESTR_NUM_MAX_PINS_DEFAULT = 5;

//Contem o valor máximo de PINs no DEPÓSITO
$PAGTO_RESTR_NUM_MAX_PINS_DEFAULT_DEP = 5;

//Habilita pagamento com EPPCASH
$PAGTO_RESTR_USE_EPPCASH_DEFAULT = true;

//Habilita pagamento com GoCASH
$PAGTO_RESTR_USE_GOCASH_DEFAULT = true;

// Custo para pagamentos GoCash
$GOCASH_CUSTO = 15;

//Formatos PINs E-PREPAG
$formato_array	=array(
                '0' => 'Somente N&uacute;meros (8 Posi&ccedil;&otilde;es)',
		'1' => 'N&uacute;meros e Letras Min&uacute;sculas (16 Posi&ccedil;&otilde;es)',
		'2' => 'N&uacute;meros e Letras Mai&uacute;sculas (16 Posi&ccedil;&otilde;es)',
		'3' => 'N&uacute;meros e Letras Min&uacute;sculas e Mai&uacute;sculas (16 Posi&ccedil;&otilde;es)',
		'4' => 'Somente N&uacute;meros (16 Posi&ccedil;&otilde;es)',
		'5' => 'Somente N&uacute;meros (14 Posi&ccedil;&otilde;es)',
		'6' => 'Somente N&uacute;meros (20 Posi&ccedil;&otilde;es)',
	);

//Status dos LOGs PINs E-PREPAG 
$PINS_STORE_MSG_LOG_STATUS =array(
								'ERRO_VALIDACAO'	=> '1',
								'SUCESSO_VALIDACAO' => '2',
								'ERRO_VALOR'		=> '3',
								'SUCESSO_VALOR'		=> '4',
								'ERRO_UTILIZACAO'	=> '5',
								'SUCESSO_UTILIZACAO'=> '6',
								'ERRO_CTRANSACAO'	=> '7',
								'SUCESSO_CTRANSACAO'=> '8',
								'ERRO_STRANSACAO'	=> '9',
							);

//Legenda dos LOGs PINs E-PREPAG 
$PINS_STORE_MSG_LOG =array(
						$PINS_STORE_MSG_LOG_STATUS['ERRO_VALIDACAO']	=> 'ERRO na Valida&ccedil;&atilde;o do PIN',
						$PINS_STORE_MSG_LOG_STATUS['SUCESSO_VALIDACAO'] => 'SUCESSO na Valida&ccedil;&atilde;o do PIN',
						$PINS_STORE_MSG_LOG_STATUS['ERRO_VALOR']		=> 'ERRO de Verifica&ccedil;&atilde;o de Valor do PIN',
						$PINS_STORE_MSG_LOG_STATUS['SUCESSO_VALOR']		=> 'SUCESSO de Verifica&ccedil;&atilde;o de Valor do PIN',
						$PINS_STORE_MSG_LOG_STATUS['ERRO_UTILIZACAO']	=> 'ERRO na Utiliza&ccedil;&atilde;o do PIN',
						$PINS_STORE_MSG_LOG_STATUS['SUCESSO_UTILIZACAO']=> 'SUCESSO na Utiliza&ccedil;&atilde;o do PIN',
						$PINS_STORE_MSG_LOG_STATUS['ERRO_CTRANSACAO']	=> 'ERRO ao Colocar o PIN em Transaction',
						$PINS_STORE_MSG_LOG_STATUS['SUCESSO_CTRANSACAO']=> 'SUCESSO ao Colocar o PIN em Transaction',
						$PINS_STORE_MSG_LOG_STATUS['ERRO_STRANSACAO']	=> 'ERRO ao Tirar o PIN do Transaction',
					);

//ID do Publisher EPP - A T E N C A O: Sempre manter sincronizados este ID com opr_codigo da tabela OPERADORAS
$OPR_CODIGO_EPP = 49;

//ID do Distribuidor EPP - A T E N C A O: Sempre manter sincronizados este ID com a respectiva matriz DISTRIBUIDRAS
$DISTRIBUIDORA_EPP = 2;

//ID do Publisher EPP LAN HOUSE - A T E N C A O: Sempre manter sincronizados este ID com opr_codigo da tabela OPERADORAS
$OPR_CODIGO_EPP_LH = 53;

//ID do Distribuidor EPP LAN HOUSE - A T E N C A O: Sempre manter sincronizados este ID com a respectiva matriz DISTRIBUIDRAS
$DISTRIBUIDORA_EPP_LH = 3;

//ID do Distribuidor EPAY - A T E N C A O: Sempre manter sincronizados este ID com a respectiva matriz DISTRIBUIDRAS
$DISTRIBUIDORA_EPAY = 5;

//ID do Distribuidor Incomm Redetrel - A T E N C A O: Sempre manter sincronizados este ID com a respectiva matriz DISTRIBUIDRAS
$DISTRIBUIDORA_INCOMM_REDETREL = 8;

//CANAIS Distribuidoras de PINs EPP
$DISTRIBUIDORAS_CANAIS = array(
							'P1'	=> 'Rede Ponto Certo',
							'P2'	=> 'ePay',
                                                        'P3'    => 'Qiwi',
                                                        'P4'    => 'Bilheteria',
                                                        'P5'    => 'Incomm Redetrel',
                                                        'P6'    => 'ZAZZY',
							'G'		=> 'Loja Gamer',
							'L'		=> 'Loja Lan House',
							);


//Distribuidoras de PINs EPP
// ATENCAUN para um novo distribuidor naun esquecer de colocar um insert manual na tabela distribuidoras_epp_cash
// Exemplo: -- insert into distribuidoras_epp_cash (dec_id_epp_cash) values (5);
$DISTRIBUIDORAS = array(
						'1' => array(
									'distributor_name'		=> 'RedePontoCerto', 
									'distributor_format'	=> '4',
									'distributor_active'	=> '1',
									'distributor_commiss'	=> '11',
									'distributor_url'		=> 'http://www.redepontocerto.com.br/',
									'distributor_flag_email'=> 1,	
									'distributor_email'		=> 'glaucia@e-prepag.com.br',
									'distributor_valores'	=> array(
																	'3' => 'R$ 3,00',
																	'5' => 'R$ 5,00',
																	'10' => 'R$ 10,00',
																	'25' => 'R$ 25,00',
																	'50' => 'R$ 50,00',
																	'100'=> 'R$ 100,00',
																	),
									),
						'2' => array(
									'distributor_name'		=> 'EPREPAG', 
									'distributor_format'	=> '4',
									'distributor_active'	=> '1',
									'distributor_commiss'	=> '0',
									'distributor_url'		=> 'http://www.e-prepag.com.br/',
									'distributor_flag_email'=> 1,	
									'distributor_email'		=> 'glaucia@e-prepag.com.br',
									'distributor_valores'	=> array(
																	'3' => 'R$ 3,00',
																	'5' => 'R$ 5,00',
																	'10' => 'R$ 10,00',
																	'15' => 'R$ 15,00',
																	'25' => 'R$ 25,00',
																	'29' => 'R$ 29,00',
																	'30' => 'R$ 30,00',
																	'50' => 'R$ 50,00',
																	'71' => 'R$ 71,00',
																	'100'=> 'R$ 100,00',
																	'136' => 'R$ 136,00',
																	'200' => 'R$ 200,00',
																	),
									),
						'3' => array(
									'distributor_name'		=> 'EPREPAG LAN HOUSE', 
									'distributor_format'	=> '4',
									'distributor_active'	=> '1',
									'distributor_commiss'	=> '0',
									'distributor_url'		=> 'http://www.e-prepag.com.br/',
									'distributor_flag_email'=> 1,	
									'distributor_email'		=> 'glaucia@e-prepag.com.br',
									'distributor_valores'	=> array(
																	'3' => 'R$ 3,00',
																	'5' => 'R$ 5,00',
																	'9' => 'R$ 9,00',
																	'10' => 'R$ 10,00',
																	'15' => 'R$ 15,00',
																	'23' => 'R$ 23,00',
																	'25' => 'R$ 25,00',
																	'29' => 'R$ 29,00',
																	'30' => 'R$ 30,00',
																	'50' => 'R$ 50,00',
																	'71' => 'R$ 71,00',
																	'100'=> 'R$ 100,00',
																	'136' => 'R$ 136,00',
																	'200' => 'R$ 200,00',
																	),
									),
						'4' => array(
									'distributor_name'		=> 'EPREPAG PROMOCAO', 
									'distributor_format'	=> '4',
									'distributor_active'	=> '1',
									'distributor_commiss'	=> '0',
									'distributor_url'		=> 'http://www.e-prepag.com.br/',
									'distributor_flag_email'=> 1,	
									'distributor_email'		=> 'glaucia@e-prepag.com.br',
									'distributor_valores'	=> array(
																	'2' => 'R$ 2,00',
																	'3' => 'R$ 3,00',
																	'5' => 'R$ 5,00',
																	'10' => 'R$ 10,00',
																	),
									),
						'5' => array(
									'distributor_name'		=> 'ePay', 
									'distributor_format'	=> '4',
									'distributor_active'	=> '1',
									'distributor_commiss'	=> '13',
									'distributor_url'		=> 'http://www.epay.com/',
									'distributor_flag_email'=> 1,	
									'distributor_email'		=> 'glaucia@e-prepag.com.br',
									'distributor_valores'	=> array(
																	'5' => 'R$ 5,00',
																	'10' => 'R$ 10,00',
																	'25' => 'R$ 25,00',
																	'50' => 'R$ 50,00',
																	'100'=> 'R$ 100,00',
																	),
									),
						'6' => array(
									'distributor_name'		=> 'Qiwi', 
									'distributor_format'	=> '4',
									'distributor_active'	=> '1',
									'distributor_commiss'	=> '8',
									'distributor_url'		=> 'http://www.qiwi.com/',
									'distributor_flag_email'=> 1,	
									'distributor_email'		=> 'glaucia@e-prepag.com.br',
									'distributor_valores'	=> array(
																	'5' => 'R$ 5,00',
																	'10' => 'R$ 10,00',
																	'15' => 'R$ 15,00',
																	'20' => 'R$ 20,00',
																	'25' => 'R$ 25,00',
																	'50' => 'R$ 50,00',
																	),
									),
						'7' => array(
									'distributor_name'		=> 'Bilheteria', 
									'distributor_format'	=> '4',
									'distributor_active'	=> '1',
									'distributor_commiss'	=> '10',
									'distributor_url'		=> 'http://www.bilheteria.com/',
									'distributor_flag_email'=> 1,	
									'distributor_email'		=> 'glaucia@e-prepag.com.br',
									'distributor_valores'	=> array(
																	'5' => 'R$ 5,00',
																	'10' => 'R$ 10,00',
																	'25' => 'R$ 25,00',
																	'50' => 'R$ 50,00',
																	'100'=> 'R$ 100,00',
																	),
									),
						'8' => array(
									'distributor_name'		=> 'Incomm Redetrel', 
									'distributor_format'	=> '4',
									'distributor_active'	=> '1',
									'distributor_commiss'	=> '8',
									'distributor_url'		=> 'http://www.incomm.com/',
									'distributor_flag_email'=> 1,	
									'distributor_email'		=> 'glaucia@e-prepag.com.br',
									'distributor_valores'	=> array(
																	'10' => 'R$ 10,00',
																	'15' => 'R$ 15,00',
																	'29' => 'R$ 29,00',
																	'50' => 'R$ 50,00',
																	'71' => 'R$ 71,00',
																	'100'=> 'R$ 100,00',
																	'136' => 'R$ 136,00',
																	),
									),
						'9' => array(
									'distributor_name'		=> 'ZAZZY', 
									'distributor_format'	=> '4',
									'distributor_active'	=> '1',
									'distributor_commiss'	=> '8',
									'distributor_url'		=> 'https://zazzytec.com.br/',
									'distributor_flag_email'=> 1,	
									'distributor_email'		=> 'glaucia@e-prepag.com.br',
									'distributor_valores'	=> array(
																	'3' => 'R$ 3,00',
																	'5' => 'R$ 5,00',
																	'10' => 'R$ 10,00',
																	'25' => 'R$ 25,00',
																	'50' => 'R$ 50,00',
																	'100'=> 'R$ 100,00',
																	),
									),
							);

//Status dos PINs E-PREPAG
$PINS_STORE_STATUS_VALUES = array(
								'D'	=> '1',
								'P'	=> '2',
								'A'	=> '3',
								'U'	=> '4',
								'B' => '5',
								'T' => '6',
								'C'	=> '-1',
								);
//Legenda Status PINs
$PINS_STORE_STATUS = array(
							$PINS_STORE_STATUS_VALUES['D']	=> 'Dispon&iacute;vel',
							$PINS_STORE_STATUS_VALUES['P']	=> 'Publicado',
							$PINS_STORE_STATUS_VALUES['A']	=> 'Ativado',
							$PINS_STORE_STATUS_VALUES['U']	=> 'Utilizado',
							$PINS_STORE_STATUS_VALUES['B']	=> 'Bloqueado',
							$PINS_STORE_STATUS_VALUES['T']	=> 'Transaction',
							$PINS_STORE_STATUS_VALUES['C']	=> 'Cancelado',
							);
//Cores para os diferentes status dos PINs E-PREPAG
$PINS_STORE_STATUS_COLORS = array(
							$PINS_STORE_STATUS_VALUES['D']	=> '#0000FF',
							$PINS_STORE_STATUS_VALUES['P']	=> '#33CC00',
							$PINS_STORE_STATUS_VALUES['A']	=> '#FFCC00',
							$PINS_STORE_STATUS_VALUES['U']	=> '#000000',
							$PINS_STORE_STATUS_VALUES['B']	=> '#999999',
							$PINS_STORE_STATUS_VALUES['T']	=> '#FC021F',
							$PINS_STORE_STATUS_VALUES['C']	=> '#FF0000',
							);

$PINS_STORE_BOTOES = array(
						'pagar'		=> '../images/pagar.gif',
						'sim'		=> '../images/sim.gif',
						'nao'		=> '../images/nao.gif',
						'adicionar'	=> '../images/adicionar.gif',
						'excluir'	=> '../images/excluir2.gif',
						'logoregra'	=> '../images/epreagcash.gif',
						'depositar'	=> '../images/concluir.gif',
						);

//CANAIS Distribuidoras de Cartões Físicos
$DISTRIBUIDORAS_CARTOES = array(
                                '11'	=> 'Incomm',
                                '22'	=> 'ePay',
                                '33'    => 'BlackHawk',
                                );


?>