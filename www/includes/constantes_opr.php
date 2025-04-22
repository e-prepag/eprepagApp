<?php
//Constantes para Operadoras
/*****************************************************
LEGENDA:
=> 78 = Operadora de Treinamento
=> 98 = Operadora EPP
=> 99 = Campeonatos


OPR_CODIGO_BLOCK		=>(operadoras não considerados no MAX opr_codigo)

OPR_CODIGO_EXCEPTIONS	=>(operadoras que podem possuir valor de PIN = 0 - ZERO)

*******************************************************/
	$OPR_CODIGO_BLOCK		= array('78','98','99');

	$OPR_CODIGO_EXCEPTIONS	= array('78');

	$PRODUCT_TYPE			= array(
									'1' => 'Utilizar somente PINs Produto',
									'2' => 'Utilizar somente PINs Moeda',
									'3' => 'Utilizar PINs Produto e PINs Moeda simultaneamente',
									'4' => 'Utilizar somente PINs GoCASH valor NOMINAL R$ 20',
									'5' => 'Utilizar somente PINs GoCASH valor REAL R$ 17',
									'6' => 'Utilizar PINs Produto e PINs GiftCard simultaneamente',
									'7' => 'Utilizar PINs Produto e PINs GoCASH valor NOMINAL',
									'8' => 'Utilizar PINs Produto, PINs GoCASH(NOMINAL R$20) e PINs GiftCard',
									);

	$USE_CHECK				= array(
									'1' => 'Sim',
									'2' => 'N&atilde;o',
									);
?>