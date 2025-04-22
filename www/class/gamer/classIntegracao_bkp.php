<?php 
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

$INTEGRACAO_PERMITIDO = "1";

// Colocando contantes para habilitar solicitação de CPF
$CPF_OBRIGATORIO = 1;
$CPF_OPICIONAL = 2;

/*------------------------------------------------------------------------------------------------
|  Intervalo máximo em DIAS que define se a consulta do CPF do cliente deve ser feita novamente.  |
|  Se a consulta já foi feita em até $NUM_DIAS_CONSIDERADO não é necessário efetuar nova consulta |
------------------------------------------------------------------------------------------------ */
$NUM_DIAS_CONSIDERADO = "150";

$server_url = "www.e-prepag.com.br";
if(checkIP()) {
    $server_url = $GLOBALS['_SERVER']['SERVER_NAME'];
}

$INTEGRACAO_URL_EPP_GATEWAY = "https://".$server_url."/prepag2/commerce/pagamento_int.php";
$INTEGRACAO_URL_EPP_GATEWAY_COMPACT = "https://".$server_url."/prepag2/commerce/pagamento_int_comp.php";

// URL da página de testes, em produção os pedidos viram das páginas dos parceiros
$INTEGRACAO_URL_EPREPAG_TESTE = "https://".$server_url."/prepag2/commerce/aeria.php";

// ID in tb_operadora_games_produto -> ogp_id
//	for joining with tb_operadora_games_produto_modelo => ogpm_ogp_id = 60
//$INTEGRACAO_STORE_PRODUTO_ID = 63;	// Aeria - 60, Treinamento - 63
//$INTEGRACAO_STORE_NOME = "AeriaGames";
$INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID = 63;
$INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR = "$INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID";

// Aerial Games Site
//$INTEGRACAO_URL_PARCEIRO = "http://billing.aeriagames.com/index.asp";
//$INTEGRACAO_IP_PARCEIRO = "72.55.177.115";
//$INTEGRACAO_URL_PARCEIRO = "https://www.e-prepag.com.br/prepag2/commerce/aeria.php";
//$INTEGRACAO_IP_PARCEIRO = "187.45.247.106";

$epp_gateway = "https://".$server_url."/prepag2/commerce/epp_notify.php";

// Set it to 0, update from _POST
$INTEGRACAO_STORE_ID = 0;

$partner_list = array(
/*

partner_need_cpf:
 *  0 = Não precisa de CPF
 *  1 = Exige CPF como obrigatório
 *  2 = CPF como opcional

Original URLs
	'return_url' => 'http://www.e-prepag.com.br/prepag2/commerce/partner_return.php',
	'notify_url' => 'https://www.e-prepag.com.br/prepag2/commerce/partner_notify.php',
	'partner_url' => 'https://www.e-prepag.com.br/prepag2/commerce/aeria.php',
	'partner_ip' => '187.45.247.106',


2010-12-22
	'return_url' => 'http://billing.test.aeriagames.com/fillup/eprepag_return.asp',
	'notify_url' => 'https://www.e-prepag.com.br/prepag2/commerce/partner_notify.php',
	'partner_url' => 'http://billing.test.aeriagames.com/fillup/eprepag_frm.asp',
	'partner_ip' => '174.142.60.5',

2010-12-23

	- partner_url : http://billing.test.aeriagames.com/fillup/eprepag_frm.asp
	- return_url : http://billing.test.aeriagames.com/fillup/eprepag_return.asp
	- notify_url : http://billing.test.aeriagames.com/fillup/eprepag_notification.asp
	- partner_ip : '112.223.50.254'

2012-06-25
	production URLs
	'notify_url' = 'https://billing.aeriagames.com/fillup/eprepag_notification.asp'
	'return_url' = 'https://billing.aeriagames.com/fillup/eprepag_return.asp'
	'sonda_url' = 'https://billing.aeriagames.com/fillup/eprepag_sonda.asp'



*/
	'AeriaGames' => array(
					'partner_name' => 'AeriaGames',
					'partner_id' => '11010',
					'partner_opr_codigo' => '39',
					'partner_active' => '1',
					'partner_currency_code' => 'BRL',
					'partner_url' => 'https://billing.aeriagames.com/fillup/eprepag_frm.asp',
					'partner_ip' => '72.55.188.52',

					'notify_url' => 'https://billing.aeriagames.com/fillup/eprepag_notification.asp',
					'return_url' => 'https://billing.aeriagames.com/fillup/eprepag_return.asp',
					'sonda_url' => 'https://billing.aeriagames.com/fillup/eprepag_sonda.asp',

					'partner_produto_id' => 60,	// Aeria - 60, Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 0,
					'partner_email' => "",	// "billingdept@aeriagames.com"
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 1,
					'partner_img_logo' => 'https://www.e-prepag.com.br/prepag2/commerce/images/int_Aeriagames_logo.png',
					'amount_free' => 0,
					'lista_formas_pagto_bloqueadas' => '',

					'forma_pagto_direta' => '',
					'forma_pagto_direta_gocash' => 0,

					'forma_pagto_direta_in_frame' => 0,
					'partner_need_cpf' => 0,
					'integracao_transparente' => 0,
				),
	'AeriaGamesTestesA' => array(
					'partner_name' => 'AeriaGamesTestesA',
					'partner_id' => '10',
					'partner_opr_codigo' => '39',
					'partner_active' => '0',
					'partner_currency_code' => 'BRL',
					'partner_url' => 'https://www.e-prepag.com.br/prepag2/commerce/aeria.php',
					'partner_ip' => '187.45.247.106',	//'72.55.188.52',

					'notify_url' => 'https://www.e-prepag.com.br/prepag2/commerce/partner_notify.php',
					'return_url' => 'http://www.e-prepag.com.br/prepag2/commerce/partner_return.php',
					'sonda_url' => '',
					'partner_produto_id' => $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR,	// Aeria - 60, Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 0,
					'partner_email' => "",	// "billingdept@aeriagames.com"
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 1,
					'partner_img_logo' => 'https://www.e-prepag.com.br/prepag2/commerce/images/int_Aeriagames_logo.png',
					'amount_free' => 0,
					'lista_formas_pagto_bloqueadas' => '',

					'forma_pagto_direta' => '',
					'forma_pagto_direta_gocash' => 0,

					'forma_pagto_direta_in_frame' => 0,
					'partner_need_cpf' => 0,
					'integracao_transparente' => 0,
				),
	'AeriaGamesN' => array(
					'partner_name' => 'AeriaGamesN',
					'partner_id' => '10300',
					'partner_opr_codigo' => '39',
					'partner_active' => '0',
					'partner_currency_code' => 'BRL',
					'partner_url' => 'http://payletter-billing.test.aeriagames.com/fillup/eprepag_frm.asp',
					'partner_ip' => '72.55.188.52',
					'notify_url' => 'http://payletter-billing.test.aeriagames.com/fillup/eprepag_notification.asp',
					'return_url' => 'http://payletter-billing.test.aeriagames.com/fillup/eprepag_return.asp',
					'sonda_url' => 'http://payletter-billing.test.aeriagames.com/fillup/eprepag_sonda.asp',
					'partner_produto_id' => $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR,	// Aeria - 60, Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 0,
					'partner_email' => "",	//"courting@payletter.com",
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 1,
					'partner_img_logo' => 'https://www.e-prepag.com.br/prepag2/commerce/images/int_Aeriagames_logo.png',
					'amount_free' => 0,
					'lista_formas_pagto_bloqueadas' => '',

					'forma_pagto_direta' => '',
					'forma_pagto_direta_gocash' => 0,

					'forma_pagto_direta_in_frame' => 0,
					'partner_need_cpf' => 0,
					'integracao_transparente' => 0,
				),
	'AeriaGamesTestesB' => array(
					'partner_name' => 'AeriaGames',
					'partner_id' => '11011',
					'partner_opr_codigo' => '39',
					'partner_active' => '0',
					'partner_currency_code' => 'BRL',
					'partner_url' => 'http://billing.test.aeriagames.com/fillup/eprepag_frm.asp',
					'partner_ip' => '72.55.188.52',
					'notify_url' => 'http://billing.test.aeriagames.com/fillup/eprepag_notification.asp',
					'return_url' => 'http://billing.test.aeriagames.com/fillup/eprepag_return.asp',
					'sonda_url' => 'http://billing.test.aeriagames.com/fillup/eprepag_sonda.asp',
					'partner_produto_id' => $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR,	// Aeria - 60, Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 0,
					'partner_email' => "",	//"kmiyata@aeriagames.com",
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 1,
					'partner_img_logo' => 'https://www.e-prepag.com.br/prepag2/commerce/images/int_Aeriagames_logo.png',
					'amount_free' => 0,
					'lista_formas_pagto_bloqueadas' => '',

					'forma_pagto_direta' => '',
					'forma_pagto_direta_gocash' => 0,

					'forma_pagto_direta_in_frame' => 0,
					'partner_need_cpf' => 0,
					'integracao_transparente' => 0,
				),

/*
	2011-03-10

		Return URL: http://payment-spi.rdev.bigpoint.net/buildpay_eprepag/return
		Cancel URL: http://payment-spi.rdev.bigpoint.net/buildpay_eprepag/cancel
		Notify URL: http://payment-spi.rdev.bigpoint.net/buildpay_eprepag/notify
		Our live URLs are all secured by https, only our development servers are not, is that ok during the dev period?


		IP:

		62.146.89.64/27
				(62.146.89.64 - 62.146.89.95)
		62.146.190.0/23
				(62.146.190.0 - 62.146.190.255)
				(62.146.191.0 - 62.146.191.255)

					'partner_url' => 'http://mfleischer-paymentglobal.bpdevsys-payment.bigpoint.net',
					'partner_ip' => '62.146.89.64',
						'partner_ip_block'			=> '62.146.89.64/27',
						'partner_ip_block_start'	=> '62.146.89.64',
						'partner_ip_block_end'		=> '62.146.89.95',

					'partner_url' => 'http://mfleischer-paymentglobal.bpdevsys-payment.bigpoint.net',
					'partner_ip' => '62.146.190.0',
						'partner_ip_block'			=> '62.146.190.0/23',
						'partner_ip_block_start'	=> '62.146.190.0',
						'partner_ip_block_end'		=> '62.146.191.255',

	2011-03-17
			http://mfleischer-paymentglobal.bpdevsys-payment.bigpoint.net/
				(ping bpdevsys-payment.bigpoint.net -> 10.128.12.180)
		and later during development:
			https://ssl.bigpoint.net/paymentglobal_rdev/
		at the end live:
			https://ssl.bigpoint.net/billing/
*/
	'Bigpoint' => array(
					'partner_name' => 'Bigpoint',
					'partner_id' => '10400',
					'partner_opr_codigo' => '45',
					'partner_active' => '1',
					'partner_currency_code' => 'BRL',
					'partner_url' => 'https://ssl.bigpoint.net/paymentglobal_rdev/',
					'partner_ip' => '62.146.190.0',
						'partner_ip_block'			=> '62.146.190.0/23',
						'partner_ip_block_start'	=> '62.146.190.0',
						'partner_ip_block_end'		=> '62.146.191.255',

						'partner_ip_intervals' => array(
								'interval_1' => array(
									'partner_ip_block'			=> '178.132.240.0/21',
									'partner_ip_block_start'	=> '178.132.240.0',
									'partner_ip_block_end'		=> '178.132.240.254',
								),
								'interval_2' => array(
									'partner_ip_block'			=> '62.146.89.64/27',
									'partner_ip_block_start'	=> '62.146.89.64',
									'partner_ip_block_end'		=> '62.146.89.95',
								),
								'interval_3' => array(
									'partner_ip_block'			=> '62.146.190.0/23',
									'partner_ip_block_start'	=> '62.146.190.0',
									'partner_ip_block_end'		=> '62.146.190.255',
								),
							),

					'notify_url' => 'https://payment-spi.bigpoint.com/buildpay_eprepag/notify',
					'return_url' => 'https://payment-spi.bigpoint.com/buildpay_eprepag/return',
					'sonda_url' => '',
					'partner_produto_id' => '77',	// Bigpoint - 77, Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 0,
					'partner_email' => '',		// "M.Fleischer@bigpoint.net"
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 1,
					'partner_img_logo' => 'https://www.e-prepag.com.br/prepag2/commerce/images/int_bigpoint_logo.png',
					'amount_free' => 1,
					'lista_formas_pagto_bloqueadas' => '1,2,5',

					'forma_pagto_direta' => '',
					'forma_pagto_direta_gocash' => 0,

					'forma_pagto_direta_in_frame' => 0,
					'partner_need_cpf' => $CPF_OBRIGATORIO,
					'integracao_transparente' => 0,
				),


	'Koramgames' => array(
					'partner_name' => 'Koramgames',
					'partner_id' => '10401',
					'partner_opr_codigo' => '78',
					'partner_active' => '1',
					'partner_currency_code' => 'BRL',
					'partner_url' => 'https://www.e-prepag.com.br/prepag2/commerce/integracao_kg.php',
					'partner_ip' => '187.45.247.106',
					'notify_url' => 'https://www.e-prepag.com.br/prepag2/commerce/partner_notify.php',
					'return_url' => 'http://www.e-prepag.com.br/prepag2/commerce/partner_return.php',
					'sonda_url' => '',
					'partner_produto_id' => $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR,	// Koramgames - ??, Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 0,
					'partner_email' => "",	//"reinaldops@yahoo.com,reynaldo@e-prepag.com.br",
					'partner_do_notify' => 0,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 0,
					'partner_img_logo' => '',
					'amount_free' => 0,
					'lista_formas_pagto_bloqueadas' => '',

					'forma_pagto_direta' => '',
					'forma_pagto_direta_gocash' => 0,

					'forma_pagto_direta_in_frame' => 0,
					'partner_need_cpf' => 0,
					'integracao_transparente' => 0,
				),
	'Bilagames' => array(
// partner_ip	 return_url							notify_url
// 112.223.50.60	 http://test-lastwarbill.bilagames.com/EprepagComplete.aspx	http://bilabill.payletter.co.kr/Eprepag/EprepagNotify.aspx
/*
	2011-01-28
				 partner_ip		return_url														notify_url
 TEST MODE		112.223.50.60	http://lastwarbill.bilagames.com/Fillup/EprepagComplete.aspx	http://bilabill.payletter.co.kr/Fillup/Eprepag/EprepagNotify.aspx
 LIVE MODE		211.43.156.104	http://lastwarbill.bilagames.com/Fillup/EprepagComplete.aspx	http://lastwarbill.bilagames.com/Fillup/Eprepag/EprepagNotify.aspx
				 174.120.178.208

 2011-02-23

		Bilagames

partner_url http://lastwarbill.bilagames.com/fillup/PaymentReqFrm.aspx
notify_url http://lastwarbill.bilagames.com/Fillup/Eprepag/EprepagNotify.aspx
return_url http://lastwarbill.bilagames.com/Fillup/EprepagComplete.aspx
partner_ip 211.43.156.104
partner_email mjbyon@payletter.com
partner_do_notify COM NOTIFICAÇÃO



*/
					'partner_name' => 'Bilagames',
					'partner_id' => '10402',
					'partner_opr_codigo' => '42',
					'partner_active' => '0',
					'partner_currency_code' => 'BRL',
					'partner_url' => 'http://lastwarbill.bilagames.com/fillup/PaymentReqFrm.aspx',
					'partner_ip' => '211.43.156.104',
					'notify_url' => 'http://lastwarbill.bilagames.com/Fillup/Eprepag/EprepagNotify.aspx',
					'return_url' => 'http://lastwarbill.bilagames.com/Fillup/EprepagComplete.aspx',
					'sonda_url' => 'http://lastwarbill.bilagames.com/Fillup/Eprepag/EprepagSonda.aspx',
					'partner_produto_id' => $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR, //'71',
					'partner_testing_email' => 0,
					'partner_email' => '',		// mjbyon@payletter.com
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 0,
					'partner_img_logo' => 'https://www.e-prepag.com.br/prepag2/commerce/images/int_Bila_logo.png',
					'amount_free' => 0,
					'lista_formas_pagto_bloqueadas' => '',

					'forma_pagto_direta' => '',
					'forma_pagto_direta_gocash' => 0,

					'forma_pagto_direta_in_frame' => 0,
					'partner_need_cpf' => $CPF_OBRIGATORIO,
					'integracao_transparente' => 0,
				),
	'BilagamesTest' => array(
/*
partner_url http://lastwarbill.bilagames.com/fillup/PaymentReqFrm.aspx
notify_url	http://lastwarbill.bilagames.com/Fillup/Eprepag/EprepagNotify.aspx
return_url	http://lastwarbill.bilagames.com/Fillup/EprepagComplete.aspx
partner_ip	112.223.50.60
partner_email moon@bilagames.com

 2011-02-23
 		BilagamesTest

partner_url http://test-lastwarbill.bilagames.com/fillup/PaymentReqFrm.aspx
notify_url http://bilabill.payletter.co.kr/Fillup/Eprepag/EprepagNotify.aspx
return_url http://lastwarbill.bilagames.com/Fillup/EprepagComplete.aspx
partner_ip 112.223.50.254
partner_email mjbyon@payletter.com
partner_do_notify COM NOTIFICAÇÃO


*/
					'partner_name' => 'BilagamesTest',
					'partner_id' => '11402',
					'partner_opr_codigo' => '42',
					'partner_active' => '0',
					'partner_currency_code' => 'BRL',
					'partner_url' => 'http://test-lastwarbill.bilagames.com/fillup/PaymentReqFrm.aspx',
					'partner_ip' => '112.223.50.254',
					'notify_url' =>	'http://bilabill.payletter.co.kr/Fillup/Eprepag/EprepagNotify.aspx',
					'return_url' => 'http://lastwarbill.bilagames.com/Fillup/EprepagComplete.aspx',
					'sonda_url' => '',
					'partner_produto_id' => $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR,	// Bilagames - 71, Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 0,
					'partner_email' => "",	//"mjbyon@payletter.com",
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 1,
					'partner_img_logo' => 'https://www.e-prepag.com.br/prepag2/commerce/images/int_Bila_logo.png',
					'amount_free' => 0,
					'lista_formas_pagto_bloqueadas' => '',

					'forma_pagto_direta' => '',
					'forma_pagto_direta_gocash' => 0,

					'forma_pagto_direta_in_frame' => 0,
					'partner_need_cpf' => 0,
					'integracao_transparente' => 0,
				),
	'Rixty' => array(
					'partner_name' => 'Rixty',
					'partner_id' => '10403',
					'partner_opr_codigo' => '78',
					'partner_active' => '1',
					'partner_currency_code' => 'BRL',
					'partner_url' => 'https://www.e-prepag.com.br/prepag2/commerce/integracao_rt.php',
					'partner_ip' => '187.45.247.106',
					'partner_ip_defined_list' => '187.45.247.106,201.6.243.44',
					'notify_url' => 'https://www.e-prepag.com.br/prepag2/commerce/partner_notify.php',
					'return_url' => 'http://www.e-prepag.com.br/prepag2/commerce/partner_return.php',
					'sonda_url' => '',
					'partner_produto_id' => '79,80', 	// Rixty - 79,80, Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 0,
					'partner_email' => '',
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 0,
					'partner_img_logo' => 'https://www.e-prepag.com.br/prepag2/commerce/images/int_rixty_logo.jpg',
					'amount_free' => 0,
					'lista_formas_pagto_bloqueadas' => '5, 6, 9, A',
					'partner_need_cpf' => 0,
					'integracao_transparente' => 0,
/*
					'forma_pagto_direta' => 'E',
					'forma_pagto_direta_gocash' => 1,

					'forma_pagto_direta_in_frame' => 1,
*/
				),
	'Owlient' => array(
					'partner_name' => 'Owlient',
					'partner_id' => '10404',
					'partner_opr_codigo' => '78',
					'partner_active' => '1',
					'partner_currency_code' => 'BRL',
					'partner_url' => 'https://www.e-prepag.com.br/prepag2/commerce/integracao_ol.php',
					'partner_ip' => '187.45.247.106',
					'notify_url' => 'https://www.e-prepag.com.br/prepag2/commerce/partner_notify.php',
					'return_url' => 'http://www.e-prepag.com.br/prepag2/commerce/partner_return.php',
					'sonda_url' => '',
					'partner_produto_id' => $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR,	// Owlient - ??, Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 0,
					'partner_email' => "",
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 1,
					'partner_img_logo' => '',
					'amount_free' => 0,
					'lista_formas_pagto_bloqueadas' => '',

					'forma_pagto_direta' => '',
					'forma_pagto_direta_gocash' => 0,

					'forma_pagto_direta_in_frame' => 0,
					'partner_need_cpf' => 0,
					'integracao_transparente' => 0,
				),
	'OGPlanet' => array(
					'partner_name' => 'OGPlanet',
					'partner_id' => '10405',
					'partner_opr_codigo' => '78',
					'partner_active' => '1',
					'partner_currency_code' => 'BRL',
					'partner_url' => 'https://www.e-prepag.com.br/prepag2/commerce/integracao_og.php',
					'partner_ip' => '68.71.247.22',
					'notify_url' => 'https://www.e-prepag.com.br/prepag2/commerce/partner_notify.php',
					'return_url' => 'http://www.e-prepag.com.br/prepag2/commerce/partner_return.php',
					'sonda_url' => '',
					'partner_produto_id' => $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR,	// OGPlanet - ??, Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 0,
					'partner_email' => "",
					'partner_do_notify' => 0,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 0,
					'partner_img_logo' => 'https://www.e-prepag.com.br/prepag2/commerce/images/ogplanet-logo_peq.png',
					'amount_free' => 1,
					'lista_formas_pagto_bloqueadas' => '',

					'forma_pagto_direta' => '',
					'forma_pagto_direta_gocash' => 0,

					'forma_pagto_direta_in_frame' => 0,
					'partner_need_cpf' => 0,
					'integracao_transparente' => 0,
				),
	'Stardoll' => array(
					'partner_name' => 'Stardoll',
					'partner_id' => '10406',
					'partner_opr_codigo' => '38',
					'partner_active' => '1',
					'partner_currency_code' => 'BRL',
					'partner_url' => 'https://www.stardoll.com/br/account/payment/toExternalPartner.php',
					'partner_ip' => '91.204.2.0',
						'partner_ip_block'			=> '91.204.2.0/24',
						'partner_ip_block_start'	=> '91.204.2.0',
						'partner_ip_block_end'		=> '91.204.2.255',
						'partner_ip_intervals' => array(
								'interval_1' => array(
									'partner_ip_block'			=> '91.204.2.0/24',
									'partner_ip_block_start'	=> '91.204.2.0',
									'partner_ip_block_end'		=> '91.204.2.255',
								),
							),
					'notify_url' => 'https://www.stardoll.com/callback/eprepag/index.php',
					'return_url' => 'https://www.stardoll.com/br/account/payment/eprepagFinished.php',
					'sonda_url' => 'https://www.stardoll.com/en/callback/eprepag/sonda.php',
					'partner_produto_id' => '53,78',	// Stardoll - 53, Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 0,
					'partner_email' => '',		// jakob.skwarski@dynabyte.se
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 1,
					'partner_img_logo' => 'https://www.e-prepag.com.br/prepag2/commerce/images/int_Stardoll_logo.png',
					'amount_free' => 0,
					'lista_formas_pagto_bloqueadas' => '',

					'forma_pagto_direta' => '',
					'forma_pagto_direta_gocash' => 0,

					'forma_pagto_direta_in_frame' => 0,
					'partner_need_cpf' => $CPF_OBRIGATORIO,
					'integracao_transparente' => 0,
				),
/*
174.36.92.186
174.36.96.66
74.86.216.66
74.86.216.67
74.86.216.68
174.36.96.70
174.36.92.187
66.220.10.2
93.72.215.215

*/
	'Paymentwall' => array(
					'partner_name' => 'Paymentwall',
					'partner_id' => '10407',
					'partner_opr_codigo' => '47',
					'partner_active' => '1',
					'partner_currency_code' => 'BRL',
					'partner_url' => 'http://wallapi.com/',
					'partner_ip' => '174.36.92.187',
					'partner_ip_defined_list' => '174.36.96.70,74.86.216.68,74.86.216.67,74.86.216.66,174.36.92.186,174.36.96.66,174.36.92.187,174.36.92.190,216.127.71.34,216.127.71.234,216.127.71.66,216.127.71.67,216.127.71.1,216.127.71.2,216.127.71.3,216.127.71.4,216.127.71.5,216.127.71.6,216.127.71.7,216.127.71.8,216.127.71.9,216.127.71.10,216.127.71.11,216.127.71.12,216.127.71.13,216.127.71.14,216.127.71.15,216.127.71.16,216.127.71.17,216.127.71.18,216.127.71.19,216.127.71.20,216.127.71.21,216.127.71.22,216.127.71.23,216.127.71.24,216.127.71.25,216.127.71.26,216.127.71.27,216.127.71.28,216.127.71.29,216.127.71.30,216.127.71.31,216.127.71.32,216.127.71.33,216.127.71.34,216.127.71.35,216.127.71.36,216.127.71.37,216.127.71.38,216.127.71.39,216.127.71.40,216.127.71.41,216.127.71.42,216.127.71.43,216.127.71.44,216.127.71.45,216.127.71.46,216.127.71.47,216.127.71.48,216.127.71.49,216.127.71.50,216.127.71.51,216.127.71.52,216.127.71.53,216.127.71.54,216.127.71.55,216.127.71.56,216.127.71.57,216.127.71.58,216.127.71.59,216.127.71.60,216.127.71.61,216.127.71.62,216.127.71.63,216.127.71.64,216.127.71.65,216.127.71.66,216.127.71.67,216.127.71.68,216.127.71.69,216.127.71.70,216.127.71.71,216.127.71.72,216.127.71.73,216.127.71.74,216.127.71.75,216.127.71.76,216.127.71.77,216.127.71.78,216.127.71.79,216.127.71.80,216.127.71.81,216.127.71.82,216.127.71.83,216.127.71.84,216.127.71.85,216.127.71.86,216.127.71.87,216.127.71.88,216.127.71.89,216.127.71.90,216.127.71.91,216.127.71.92,216.127.71.93,216.127.71.94,216.127.71.95,216.127.71.96,216.127.71.97,216.127.71.98,216.127.71.99,216.127.71.100,216.127.71.101,216.127.71.102,216.127.71.103,216.127.71.104,216.127.71.105,216.127.71.106,216.127.71.107,216.127.71.108,216.127.71.109,216.127.71.110,216.127.71.111,216.127.71.112,216.127.71.113,216.127.71.114,216.127.71.115,216.127.71.116,216.127.71.117,216.127.71.118,216.127.71.119,216.127.71.120,216.127.71.121,216.127.71.122,216.127.71.123,216.127.71.124,216.127.71.125,216.127.71.126,216.127.71.127,216.127.71.128,216.127.71.129,216.127.71.130,216.127.71.131,216.127.71.132,216.127.71.133,216.127.71.134,216.127.71.135,216.127.71.136,216.127.71.137,216.127.71.138,216.127.71.139,216.127.71.140,216.127.71.141,216.127.71.142,216.127.71.143,216.127.71.144,216.127.71.145,216.127.71.146,216.127.71.147,216.127.71.148,216.127.71.149,216.127.71.150,216.127.71.151,216.127.71.152,216.127.71.153,216.127.71.154,216.127.71.155,216.127.71.156,216.127.71.157,216.127.71.158,216.127.71.159,216.127.71.160,216.127.71.161,216.127.71.162,216.127.71.163,216.127.71.164,216.127.71.165,216.127.71.166,216.127.71.167,216.127.71.168,216.127.71.169,216.127.71.170,216.127.71.171,216.127.71.172,216.127.71.173,216.127.71.174,216.127.71.175,216.127.71.176,216.127.71.177,216.127.71.178,216.127.71.179,216.127.71.180,216.127.71.181,216.127.71.182,216.127.71.183,216.127.71.184,216.127.71.185,216.127.71.186,216.127.71.187,216.127.71.188,216.127.71.189,216.127.71.190,216.127.71.191,216.127.71.192,216.127.71.193,216.127.71.194,216.127.71.195,216.127.71.196,216.127.71.197,216.127.71.198,216.127.71.199,216.127.71.200,216.127.71.201,216.127.71.202,216.127.71.203,216.127.71.204,216.127.71.205,216.127.71.206,216.127.71.207,216.127.71.208,216.127.71.209,216.127.71.210,216.127.71.211,216.127.71.212,216.127.71.213,216.127.71.214,216.127.71.215,216.127.71.216,216.127.71.217,216.127.71.218,216.127.71.219,216.127.71.220,216.127.71.221,216.127.71.222,216.127.71.223,216.127.71.224,216.127.71.225,216.127.71.226,216.127.71.227,216.127.71.228,216.127.71.229,216.127.71.230,216.127.71.231,216.127.71.232,216.127.71.233,216.127.71.234,216.127.71.235,216.127.71.236,216.127.71.237,216.127.71.238,216.127.71.239,216.127.71.240,216.127.71.241,216.127.71.242,216.127.71.243,216.127.71.244,216.127.71.245,216.127.71.246,216.127.71.247,216.127.71.248,216.127.71.249,216.127.71.250,216.127.71.251,216.127.71.252,216.127.71.253,216.127.71.254,216.127.71.255,216.127.71.256,169.46.81.96,169.46.81.97,169.46.81.98,169.46.81.99,169.46.81.100,169.46.81.101,169.46.81.102,169.46.81.103,169.46.81.104,169.46.81.105,169.46.81.106,169.46.81.107,169.46.81.108,169.46.81.109,169.46.81.110,169.46.81.111,169.46.81.112,169.46.81.113,169.46.81.114,169.46.81.115,169.46.81.116,169.46.81.117,169.46.81.118,169.46.81.119,169.46.81.120,169.46.81.121,169.46.81.122,169.46.81.123,169.46.81.124,169.46.81.125,169.46.81.126,169.46.81.127,169.46.167.165,173.193.57.221,216.127.71.34,216.127.71.234,216.127.71.66',
					'notify_url' => 'https://wallapi.com/api/paymentpingback/eprepag',
					'return_url' => 'http://wallapi.com/api/paymentpingback/eprepag-result',
					'sonda_url' => '',
					'partner_produto_id' => 81,	// Paymentwall - 81, Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 0,
					'partner_email' => '',	//'money@paymentwall.com',
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 1,
					'partner_img_logo' => 'https://www.e-prepag.com.br/prepag2/commerce/images/int_pwall_menor.png',
					'amount_free' => 1,
					'lista_formas_pagto_bloqueadas' => '',

					'forma_pagto_direta' => '',
					'forma_pagto_direta_gocash' => 0,

					'forma_pagto_direta_in_frame' => 0,
					'partner_need_cpf' => $CPF_OBRIGATORIO,
					'integracao_transparente' => 0,
				),
	'Softnyx' => array(
					'partner_name' => 'Softnyx',
					'partner_id' => '10408',
					'partner_opr_codigo' => '37',	//'78',
					'partner_active' => '1',
					'partner_currency_code' => 'BRL',
					'partner_url' => 'https://www.e-prepag.com.br/prepag2/commerce/integracao_sn.php',
					'partner_ip' => '38.82.217.11',		//'183.110.159.85',		//'187.45.247.106',
					//'partner_ip_defined_list' => '190.8.151.76,190.102.137.84,38.82.217.164,38.82.217.170,38.82.217.11,38.82.217.12',//'38.82.217.11,38.82.217.12',
					'partner_ip_defined_list' => '66.231.244.196,66.231.244.142,66.231.244.143,38.82.217.11,38.82.217.12',//'38.82.217.11,38.82.217.12',
					'notify_url' => 'http://shop.softnyxbrasil.com/eprepag/eprepag_pb.asp',	//'https://shop.softnyx.com/shop/Charge_Eprepag_OK.asp', //'https://shop.softnyx.com/shop/Bra_Eprepag_Ok.asp',
					'return_url' => 'https://billing.softnyxbrasil.com/History/Default.aspx?menuseq=3',//'http://www.softnyxbrasil.com/cash/03_Charge_05Eprepag_Completed.asp', //'https://shop.softnyx.com/shop/Bra_Eprepag_Completed.asp',
					'sonda_url' => 'http://shop.softnyxbrasil.com/eprepag/eprepag_pb.asp',	//'https://shop.softnyx.com/shop/Charge_Eprepag_OK.asp',
					'partner_produto_id' => 105,	// Softnyx - 105, Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 0,
					'partner_email' => "",	//"reynaldo@e-prepag.com.br",
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 0,
					'partner_img_logo' => 'https://www.e-prepag.com.br/prepag2/commerce/images/int_Softnyx_logo.png',
					'amount_free' => 0,
					'lista_formas_pagto_bloqueadas' => '',

					'forma_pagto_direta' => '',
					'forma_pagto_direta_gocash' => 0,

					'forma_pagto_direta_in_frame' => 0,
					'partner_need_cpf' => $CPF_OBRIGATORIO,
					'integracao_transparente' => 0,
				),
	'Habbo_Hotel' => array(
					'partner_name' => 'Habbo_Hotel',
					'partner_id' => '10409',
/*
    ATENÇÃO: QDO for para produção alterar o ID do PUBLISHER
 *              E do PRODUTO
 *  */					'partner_opr_codigo' => '78',
					'partner_active' => '1',
					'partner_currency_code' => 'BRL',
					'partner_url' => 'https://s1.varoke.net',
					'partner_ip' => '62.50.39.125',
					'partner_ip_defined_list' => '62.50.39.125,62.50.39.173,62.50.39.180,62.50.39.181',
					'notify_url' => 'https://cbs2.stage.varoke.net/payment-integrations/online/hhs1_eprepag/partner/paymentStatus',
					'return_url' => 'https://s1.varoke.net/credits',
					'sonda_url' => '',
					'partner_produto_id' => $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR,	// Sulake - ??, Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 1,
					'partner_email' => "payment@sulake.com",
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 1,
					'partner_img_logo' => 'https://www.e-prepag.com.br/prepag2/commerce/images/habbo_logo.png',
					'amount_free' => 1,
					'lista_formas_pagto_bloqueadas' => '',

					'forma_pagto_direta' => '',
					'forma_pagto_direta_gocash' => 0,

					'forma_pagto_direta_in_frame' => 0,
					'partner_need_cpf' => $CPF_OBRIGATORIO,
					'integracao_transparente' => 0,
				),
	'Snailgames' => array(
					'partner_name' => 'Snailgames',
					'partner_id' => '10410',
					'partner_opr_codigo' => '78',
					'partner_active' => '1',
					'partner_currency_code' => 'BRL',
					'partner_url' => 'https://www.e-prepag.com.br/prepag2/commerce/integracao_sg.php',
					'partner_ip' => '187.45.247.106',
					'notify_url' => 'https://www.e-prepag.com.br/prepag2/commerce/partner_notify.php',
					'return_url' => 'http://www.e-prepag.com.br/prepag2/commerce/partner_return.php',
					'sonda_url' => '',
					'partner_produto_id' => $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR,	// Snailgames - ??, Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 0,
					'partner_email' => "",
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 1,
					'partner_img_logo' => '',
					'amount_free' => 0,
					'lista_formas_pagto_bloqueadas' => '',

					'forma_pagto_direta' => '',
					'forma_pagto_direta_gocash' => 0,

					'forma_pagto_direta_in_frame' => 0,
					'partner_need_cpf' => 0,
					'integracao_transparente' => 0,
				),
/*
2012-06-12
Hi
The test url is
	Notify_url:     http://payelex-test.appspot.com/callback/eprepag
	Return_url:   http://payelex-test.appspot.com/status_eprepag.jsp
The  live url is
	Notify_url:     http://payelex.appspot.com/callback/eprepag
	Return_url:   http://payelex.appspot.com/status_eprepag.jsp

2012-06-14
	partner_url:     http://payelex.appspot.com/payelex
	Notify_url:     http://payelex.appspot.com/callback/eprepag
	Return_url:   http://payelex.appspot.com/status_eprepag.jsp
	Sonda_url: http://pay.337.com/api/eprepag_query.php    (it need a post request with parameter 'transId' and return a json string with the transaction info)

*/
	'Elextech' => array(
					'partner_name' => 'Elextech',
					'partner_id' => '10411',
					'partner_opr_codigo' => '58',
					'partner_active' => '0',
					'partner_currency_code' => 'BRL',
					'partner_url' => 'http://payelex.appspot.com/payelex',
					'partner_ip' => '187.45.247.106,50.22.193.86',
					'notify_url' => 'http://payelex.appspot.com/callback/eprepag',
					'return_url' => 'http://payelex.appspot.com/status_eprepag.jsp',
					'sonda_url' => 'http://pay.337.com/payelex/api/eprepag_query.php',
					'partner_produto_id' => $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR, //"112,113,114,115,116,117,118,119,120,121,142,154",	// Elextech - 112,113,114,115,116,117,118,119,120,121, Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 0,
					'partner_email' => "",
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 1,
					'partner_img_logo' => 'https://www.e-prepag.com.br/prepag2/commerce/images/elex_logo.jpg',
						'partner_img_prods_logo' => array(
							117 => 'https://www.e-prepag.com.br/prepag2/commerce/images/int_cesar_peq.jpg',
							112 => 'https://www.e-prepag.com.br/prepag2/commerce/images/int_ddt_peq.jpg',
							113 => 'https://www.e-prepag.com.br/prepag2/commerce/images/int_ik_peq.jpg',
							119 => 'https://www.e-prepag.com.br/prepag2/commerce/images/int_namo_peq.jpg',
							121 => 'https://www.e-prepag.com.br/prepag2/commerce/images/int_nindou_peq.jpg',
							116 => 'https://www.e-prepag.com.br/prepag2/commerce/images/int_poker_peq.jpg',
							142 => 'https://www.e-prepag.com.br/prepag2/commerce/images/337cash.png',
						),
					'amount_free' => 0,
					'lista_formas_pagto_bloqueadas' => '1, 2, 5, 6, 9, A',

					'forma_pagto_direta' => '',
					'forma_pagto_direta_gocash' => 0,

					'forma_pagto_direta_in_frame' => 0,
					'partner_need_cpf' => $CPF_OBRIGATORIO,
					'integracao_transparente' => 0,
				),
	'Elextech_2' => array(
					'partner_name' => 'Elextech_2',
					'partner_id' => '10421',
					'partner_opr_codigo' => '58',
					'partner_active' => '1',
					'partner_currency_code' => 'BRL',
					'partner_url' => 'http://pay.337.com', //'http://v3.pay.337.com',
					'partner_ip' => '174.37.255.61,174.37.255.60,173.192.195.130,50.22.193.86,50.97.45.21', //'174.37.255.61,174.37.255.60,173.192.195.130,119.254.245.114',
					'notify_url' => 'http://pay.337.com/payment/callback?custom_channel_id=eprepag', //'http://v3.pay.337.com/payment/callback?custom_channel_id=eprepag',
					'return_url' => 'http://pay.337.com/status/success', //'http://v3.pay.337.com/status/success',
					'sonda_url' => 'http://pay.337.com/payment/callback/custom_channel_id/eprepag/type/check_order', //'http://pay.337.com/payelex/api/v3/eprepag.php',
					'partner_produto_id' => "112,113,114,115,116,117,118,119,120,121,142,154",	// Elextech_2 - 112,113,114,115,116,117,118,119,120,121, Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 0,
					'partner_email' => "",		// "reynaldo@e-prepag.com.br", "zhangjing1@elex-tech.com"
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 1,
					'partner_img_logo' => 'https://www.e-prepag.com.br/prepag2/commerce/images/elex_logo.jpg',
						'partner_img_prods_logo' => array(
							117 => 'https://www.e-prepag.com.br/prepag2/commerce/images/int_cesar_peq.jpg',
							112 => 'https://www.e-prepag.com.br/prepag2/commerce/images/int_ddt_peq.jpg',
							113 => 'https://www.e-prepag.com.br/prepag2/commerce/images/int_ik_peq.jpg',
							119 => 'https://www.e-prepag.com.br/prepag2/commerce/images/int_namo_peq.jpg',
							121 => 'https://www.e-prepag.com.br/prepag2/commerce/images/int_nindou_peq.jpg',
							116 => 'https://www.e-prepag.com.br/prepag2/commerce/images/int_poker_peq.jpg',
							142 => 'https://www.e-prepag.com.br/prepag2/commerce/images/337cash.png',
						),
					'amount_free' => 0,
					'lista_formas_pagto_bloqueadas' => '1, 2, 5, 6, 9, A',

					'forma_pagto_direta' => '',
					'forma_pagto_direta_gocash' => 0,

					'forma_pagto_direta_in_frame' => 0,
					'partner_need_cpf' => $CPF_OBRIGATORIO,
					'integracao_transparente' => 0,
				),


	'Ankama' => array(
					'partner_name' => 'Ankama',
					'partner_id' => '10412',
					'partner_opr_codigo' => '57',
					'partner_active' => '1',
					'partner_currency_code' => 'BRL',
					'partner_url' => 'https://account.ankama.com/',
					'partner_ip' => '80.239.173.132',
					'partner_ip_defined_list' => '80.239.173.132,80.239.173.254',
					'notify_url' => 'https://api.ankama.com/payments/ep/process',
					'return_url' => 'https://api.ankama.com/payments/ep/result',
					'sonda_url' => 'https://api.ankama.com/payments/ep/sonda',
					'partner_produto_id' => 108,	// Ankama - 108, Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 0,
					'partner_email' => "",
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 1,
					'partner_img_logo' => 'https://www.e-prepag.com.br/prepag2/commerce/images/logo_ankama_peq.jpg',
					'amount_free' => 0,
					'lista_formas_pagto_bloqueadas' => '',

					'forma_pagto_direta' => '',
					'forma_pagto_direta_gocash' => 0,

					'forma_pagto_direta_in_frame' => 0,
					'partner_need_cpf' => $CPF_OBRIGATORIO,
					'integracao_transparente' => 0,
				),
	'Cyberstep' => array(
					'partner_name' => 'Cyberstep',
					'partner_id' => '10413',
					'partner_opr_codigo' => '62',	//'62',
					'partner_active' => '1',
					'partner_currency_code' => 'BRL',
					'partner_url' => 'https://secure.getamped.com/charge/eprepag/gate',
					'partner_ip' => '211.43.152.152',
					'partner_ip_defined_list' => '50.112.141.171,50.112.176.114,54.244.11.180,54.244.8.209,54.200.55.11,54.187.9.251,54.213.0.200,35.161.132.107',
                                         // old IPs changed by Wagner '211.43.152.152,211.43.152.157',
					'notify_url' => 'https://secure.getamped.com/api/eprepag/callback',
					'return_url' => 'https://secure.getamped.com/nolayout/charge/complete.html',
					'sonda_url' => 'https://secure.getamped.com/api/eprepag/sonda',
					'partner_produto_id' => '132,242,243,244,245,246,340',	// Cyberstep - 132 , Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 0,
					'partner_email' => "",	//"reynaldo@e-prepag.com.br",
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 1,
					'partner_img_logo' => 'https://www.e-prepag.com.br/prepag2/commerce/images/cyberstep_logo.jpg',
						'partner_img_prods_logo' => array(
							132 => 'http://www.e-prepag.com.br/prepag2/commerce/images/produtos/p_132.jpg',
						),
					'amount_free' => 0,
					'lista_formas_pagto_bloqueadas' => '',

					'forma_pagto_direta' => '',
					'forma_pagto_direta_gocash' => 0,

					'forma_pagto_direta_in_frame' => 0,
					'partner_need_cpf' => $CPF_OBRIGATORIO,
					'integracao_transparente' => 0,
				),
	'Ongame' => array(
					'partner_name' => 'Ongame',
					'partner_id' => '10414',
					'partner_opr_codigo' => '13', //'78',	// 13
					'partner_active' => '1',
					'partner_currency_code' => 'BRL',
					'partner_url' => 'http://pb.ongame.com.br/loja/?m=7',
					'partner_ip' => '187.45.247.106',
					'notify_url' => 'https://www.ongameshop.com.br/modulos/eprepag/notify_url.php',
					'return_url' => 'http://pb.ongame.com.br/loja/',
					'sonda_url' => 'https://www.ongameshop.com.br/modulos/eprepag/sonda_url.php',
					'partner_produto_id' => 109,	// Ongame - 109, Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 0,
					'partner_email' => "",	//"reynaldo@e-prepag.com.br,mardel@ongame.com.br",
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 1,
					'partner_img_logo' => 'https://www.e-prepag.com.br/prepag2/commerce/images/logo_ongame.png',
					'amount_free' => 0,
					'lista_formas_pagto_bloqueadas' => '5, 6, 9, A',

					'forma_pagto_direta' => '',
					'forma_pagto_direta_gocash' => 0,

					'forma_pagto_direta_in_frame' => 0,
					'partner_need_cpf' => 0,
					'integracao_transparente' => 0,
				),

	'Gamerage' => array(
					'partner_name' => 'Gamerage',
					'partner_id' => '10415',
					'partner_opr_codigo' => '78',
					'partner_active' => '0',
					'partner_currency_code' => 'BRL',
					'partner_url' => 'https://www.e-prepag.com.br/prepag2/commerce/integracao_gr.php',
					'partner_ip' => '187.45.247.106',
					'notify_url' => 'https://www.e-prepag.com.br/prepag2/commerce/partner_notify.php',
					'return_url' => 'http://www.e-prepag.com.br/prepag2/commerce/partner_return.php',
					'sonda_url' => '',
					'partner_produto_id' => $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR,	// Cyberstep - ??, Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 0,
					'partner_email' => "",
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 1,
					'partner_img_logo' => '',
					'amount_free' => 0,
					'lista_formas_pagto_bloqueadas' => '',
					'partner_need_cpf' => 0,
					'integracao_transparente' => 0,
				),

/*

			Test																Live
IP			216.39.108.142														216.39.108.144
Return URL	http://billtest.gamerage.com/Fillup/prepaid/EprepagComplete.aspx	https://bill.gamerage.com/Fillup/prepaid/EprepagComplete.aspx
Notify URL	http://billtest.gamerage.com/Fillup/prepaid/EprepagNotify.aspx		https://bill.gamerage.com/Fillup/prepaid/EprepagNotify.aspx
Sonda URL	http://billtest.gamerage.com/Fillup/prepaid/EprepagSonda.aspx		https://bill.gamerage.com/Fillup/prepaid/EprepagSonda.aspx


*/

	'SGInteractive' => array(
					'partner_name' => 'SGInteractive',
					'partner_id' => '10416',
					'partner_opr_codigo' => '59',
					'partner_active' => '1',
					'partner_currency_code' => 'BRL',
//					'partner_url' => 'http://billtest.gamerage.com/Fillup/RechargePoint.aspx',
//					'partner_ip' => '216.39.108.142',
//					'notify_url' => 'http://billtest.gamerage.com/Fillup/prepaid/EprepagNotify.aspx',
//					'return_url' => 'http://billtest.gamerage.com/Fillup/prepaid/EprepagComplete.aspx ',
//					'sonda_url' => 'http://billtest.gamerage.com/Fillup/prepaid/EprepagSonda.aspx',
					'partner_url' => 'http://billtest.gamerage.com/Fillup/RechargePoint.aspx',
					'partner_ip' => '216.39.108.144',
					'notify_url' => 'https://bill.gamerage.com/Fillup/prepaid/EprepagNotify.aspx',
					'return_url' => 'https://bill.gamerage.com/Fillup/prepaid/EprepagComplete.aspx',
					'sonda_url' => 'https://bill.gamerage.com/Fillup/prepaid/EprepagSonda.aspx',
					'partner_produto_id' => 122,	// SGInteractive - 122, Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 0,
					'partner_email' => "",
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 1,
					'partner_img_logo' => 'https://www.e-prepag.com.br/prepag2/commerce/images/gamerage_menor.jpg',
					'amount_free' => 0,
					'lista_formas_pagto_bloqueadas' => '',

					'forma_pagto_direta' => '',
					'forma_pagto_direta_gocash' => 0,

					'forma_pagto_direta_in_frame' => 0,
					'partner_need_cpf' => 0,
					'integracao_transparente' => 0,
				),

	'G4Box' => array(
					'partner_name' => 'G4Box',
					'partner_id' => '10417',
					'partner_opr_codigo' => '63',	
					'partner_active' => '1',
					'partner_currency_code' => 'BRL',

					'partner_url' => 'https://br.payment.z8games.com/eprepag/eprepagprocess.aspx',
					'partner_ip' => '198.49.88.91',//107.6.40.116
					//'partner_ip_defined_list' => '198.49.88.91',
					'notify_url' => 'https://br.payment.z8games.com/eprepag/eprepagnotify.aspx',
					'return_url' => 'https://br.crossfire.z8games.com/depositCompleted.aspx',
					'sonda_url' => 'https://br.payment.z8games.com/EPrepag/sondacheck.aspx',

					'partner_produto_id' => 145,	// G4Box - 145, Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 0,
					'partner_email' => "",
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 1,
					'partner_img_logo' => 'https://www.e-prepag.com.br/prepag2/commerce/images/z8_img.png',
					'amount_free' => 0,
					'lista_formas_pagto_bloqueadas' => '',

					'forma_pagto_direta' => '',
					'forma_pagto_direta_gocash' => 0,

					'forma_pagto_direta_in_frame' => 0,
					'partner_need_cpf' => $CPF_OBRIGATORIO,
					'integracao_transparente' => 0,
				),

	'G4Box_2' => array(
					'partner_name' => 'G4Box_2',
					'partner_id' => '10427',
					'partner_opr_codigo' => '63',
					'partner_active' => '1',
					'partner_currency_code' => 'BRL',

					'partner_url' => 'https://br.bill.z8games.com/Fillup/E-Wallet/EprePag/EprePagFrm.aspx', //'http://g4box.payletter.co.kr/Fillup/E-Wallet/EprePag/EprePagFrm.aspx',
					'partner_ip' => '198.49.88.91',//'67.210.208.159',//'67.210.208.187', //'74.200.6.187', //'203.238.156.137',  // 107.6.40.116
                    'partner_ip_defined_list' => '107.6.40.116,192.49.88.72,198.49.89.207,198.49.89.209,198.49.88.90,198.49.88.91',
					'notify_url' => 'https://br.bill.z8games.com/Fillup/E-Wallet/EprePag/EprepagNotify.aspx', //'http://g4box.payletter.co.kr/Fillup/E-Wallet/EprePag/EprepagNotify.aspx',
					'return_url' => 'https://br.bill.z8games.com/Fillup/E-Wallet/EprePag/EprePagResult.aspx', //'http://g4box.payletter.co.kr/Fillup/E-Wallet/EprePag/EprePagResult.aspx',
					'sonda_url' => 'https://br.bill.z8games.com/Fillup/E-Wallet/EprePag/EprepagSonda.aspx', //'http://g4box.payletter.co.kr/Fillup/E-Wallet/EprePag/EprepagSonda.aspx',

					'partner_produto_id' => 145,	// G4Box - 145, Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 0,
					'partner_email' => "",
					'partner_do_notify' => 1, 
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 1,
					'partner_img_logo' => 'https://www.e-prepag.com.br/prepag2/commerce/images/z8_img.png',
					'amount_free' => 0,
					'lista_formas_pagto_bloqueadas' => '',

					'forma_pagto_direta' => '',
					'forma_pagto_direta_gocash' => 0,

					'forma_pagto_direta_in_frame' => 0,
					'partner_need_cpf' => $CPF_OBRIGATORIO,
					'integracao_transparente' => 0,
				),
/*
	'Cyberstep_2' => array(
					'partner_name' => 'Cyberstep_2',
					'partner_id' => '10418',
					'partner_opr_codigo' => '62',	//'62',
					'partner_active' => '1',
					'partner_currency_code' => 'BRL',
					'partner_url' => 'https://www.cyberstep.com.br/charge/eprepag/gate',
					'partner_ip' => '177.71.196.143',
					'partner_ip_defined_list' => '',
					'notify_url' => 'https://www.cyberstep.com.br/api/eprepag/callback',
					'return_url' => 'https://www.cyberstep.com.br/nolayout/charge/complete.html',
					'sonda_url' => 'https://www.cyberstep.com.br/api/eprepag/sonda',
					'partner_produto_id' => '147,238,239,240,241,335',	// Cyberstep - 132,147 , Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 0,
					'partner_email' => "",	//"reynaldo@e-prepag.com.br",
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 1,
					'partner_img_logo' => 'https://www.e-prepag.com.br/prepag2/commerce/images/cyberstep_logo.jpg',
						'partner_img_prods_logo' => array(
							147 => 'http://www.e-prepag.com.br/prepag2/commerce/images/produtos/p_147.jpg',
						),
					'amount_free' => 0,
					'lista_formas_pagto_bloqueadas' => '',

					'forma_pagto_direta' => '',
					'forma_pagto_direta_gocash' => 0,

					'forma_pagto_direta_in_frame' => 0,
					'partner_need_cpf' => $CPF_OBRIGATORIO,
					'integracao_transparente' => 0,
				),
*/
	'BluePixel' => array(
					'partner_name' => 'BluePixel',
					'partner_id' => '10419',
					'partner_opr_codigo' => '64',
					'partner_active' => '1',
					'partner_currency_code' => 'BRL',
					'partner_url' => 'https://bpixel.com.br/servlets/ePrepag',
					'partner_ip' => '50.22.4.167',
					'notify_url' => 'https://bpixel.com.br/servlets/ePrepag',
					'return_url' => 'https://bpixel.com.br/ePrepag.html',
					'sonda_url' => 'https://bpixel.com.br/servlets/ePrepag',
					'partner_produto_id' => 153,	// BluePixel - 153, Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 1,
					'partner_email' => "wagner@e-prepag.com.br",
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 1,
					'partner_img_logo' => 'https://www.e-prepag.com.br/prepag2/commerce/images/logo_BP_160x31.png',
					'amount_free' => 0,
					'lista_formas_pagto_bloqueadas' => '',

					'forma_pagto_direta' => '',
					'forma_pagto_direta_gocash' => 0,

					'forma_pagto_direta_in_frame' => 0,
					'partner_need_cpf' => 0,
					'integracao_transparente' => 0, 
				),

/*
					'partner_url' => 'http://gamestest.vibrant3g.com',
					'partner_ip' => '124.153.108.100',
					'notify_url' => 'http://gamestest.vibrant3g.com/vmoney/eprepag.jsp',
					'return_url' => 'http://gamestest.vibrant3g.com',
					'sonda_url' => 'http://gamestest.vibrant3g.com/sondaurl.jsp',

*/
	'Vibrant' => array(
					'partner_name' => 'Vibrant',
					'partner_id' => '10420',
					'partner_opr_codigo' => '65',
					'partner_active' => '1',
					'partner_currency_code' => 'BRL',

					'partner_url' => 'http://games.vibrant3g.com',
					'partner_ip' => '124.153.108.122',
					'notify_url' => 'https://games.vibrant3g.com/payment/eprepag.jsp',
					'return_url' => 'http://games.vibrant3g.com',
					'sonda_url' => 'https://games.vibrant3g.com/payment/sondaurl.jsp',

					'partner_produto_id' => 155,	// Vibrant - 155, Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 0,
					'partner_email' => "",
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 1,
					'partner_img_logo' => 'https://www.e-prepag.com.br/prepag2/commerce/images/logo_Vibrant.png',
					'amount_free' => 0,
					'lista_formas_pagto_bloqueadas' => '',

					'forma_pagto_direta' => '',
					'forma_pagto_direta_gocash' => 0,

					'forma_pagto_direta_in_frame' => 0,
					'partner_need_cpf' => 0,
					'integracao_transparente' => 0,
				),

	'Xsolla' => array(
					'partner_name' => 'Xsolla',
					'partner_id' => '10422',
					'partner_opr_codigo' => '66',
					'partner_active' => '1',
					'partner_currency_code' => 'BRL',
					'partner_url' => 'https://secure.xsolla.com',
            
					'partner_ip' => '94.103.26.176',
                                                'partner_ip_block'		=> '94.103.26.176/29',
                                                'partner_ip_block_start'	=> '94.103.26.176',
                                                'partner_ip_block_end'		=> '94.103.26.183',
                                                'partner_ip_intervals' => array(
                                                                'interval_1' => array(
                                                                        'partner_ip_block'		=> '159.255.220.240/28',
                                                                        'partner_ip_block_start'	=> '159.255.220.240',
                                                                        'partner_ip_block_end'		=> '159.255.220.255',
                                                                ),
                                                                'interval_2' => array(
                                                                        'partner_ip_block'		=> '185.30.20.16/29',
                                                                        'partner_ip_block_start'	=> '185.30.20.16',
                                                                        'partner_ip_block_end'		=> '185.30.20.23',
                                                                ),
                                                                'interval_3' => array(
                                                                        'partner_ip_block'		=> '185.30.21.16/29',
                                                                        'partner_ip_block_start'	=> '185.30.21.16',
                                                                        'partner_ip_block_end'		=> '185.30.21.23',
                                                                ),
                                                        ),
						'partner_ip_defined_list' => '185.30.23.18,185.30.21.67,172.16.16.215', 

                                        // OLD 'partner_ip' => '94.103.26.178',
					// OLD	'partner_ip_defined_list' => '94.103.26.178,94.103.26.179,94.103.26.180,94.103.26.181,94.103.26.182,178.248.236.17,178.161.146.107',
                                        // TESTE 'notify_url' => 'https://test-ps.xsolla.com/eprepag/result',
                                        'notify_url' => 'https://ps.xsolla.com/eprepag/result',
                                        // TESTE 'return_url' => 'https://test-secure.xsolla.com/status/eprepag/return',
                                        'return_url' => 'https://secure.xsolla.com/status/eprepag/return',
					// TESTE 'sonda_url' => 'https://test-ps.xsolla.com/eprepag/sonda',
                                        'sonda_url' => 'https://ps.xsolla.com/eprepag/sonda',
					
					'partner_produto_id' => 191, //$INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR,	// Xsola - ??, Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 0,
					'partner_email' => "",	//	stats@xsolla.com
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 1,
					'partner_img_logo' => 'https://www.e-prepag.com.br/prepag2/commerce/images/xsolla-logo2.png',
					'amount_free' => 1,
					'lista_formas_pagto_bloqueadas' => '1, 2, 5, 6, 7, 9, A, F, G, H, I, J, K, L, M',

					'forma_pagto_direta' => '',
					'forma_pagto_direta_gocash' => 0,

					'forma_pagto_direta_in_frame' => 0,
					'partner_need_cpf' => $CPF_OBRIGATORIO,
					'integracao_transparente' => 0,
				),

	'NTTGame' => array(
					'partner_name' => 'NTTGame',
					'partner_id' => '10423',
					'partner_opr_codigo' => '67',
					'partner_active' => '1',
					'partner_currency_code' => 'BRL',
					'partner_url' => 'http://www.nttgame.com', //'https://billing.knightonlineworld.com',//'http://www.knightonlineworld.com', //'https://billing.knightonlineworld.com/FillUp/EPrePag/EPrePagProcess.aspx',
					'partner_ip' => '188.132.200.170', //'188.132.161.220', //'203.238.156.137',
					'notify_url' => 'http://billing.nttgame.com/FillUp/EPrePag/EPrePagNotification.aspx', //'http://billing.knightonlineworld.com/FillUp/EPrePag/EPrePagNotification.aspx',//'http://gcsus.payletter.co.kr/FillUp/EPrePag/EPrePagNotification.aspx',
					'return_url' => 'http://billing.nttgame.com/FillUp/EPrePag/EPrePagComplete.aspx', //'http://billing.knightonlineworld.com/FillUp/EPrePag/EPrePagComplete.aspx',//'http://billingpay.knightonlineworld.com/payment/payment.aspx?frameurl=http://billingpay.knightonlineworld.com/FillUp/EPrePag/EPrePagComplete.aspx',
					'sonda_url' => 'http://billing.nttgame.com/FillUp/EPrePag/EPrePagSonda.aspx', //'http://billing.knightonlineworld.com/FillUp/EPrePag/EPrePagSonda.aspx',//'http://gcsus.payletter.co.kr/FillUp/EPrePag/EPrePagSonda.aspx',
					'partner_produto_id' => 173,
                                        'partner_testing_email' => 0,
					'partner_email' => "moongmi@payletter.com",
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 1,
					'partner_img_logo' => 'https://www.e-prepag.com.br/prepag2/commerce/images/NTTGameLogo.png',
					'amount_free' => 0,
					'lista_formas_pagto_bloqueadas' => '',
                                        'auto_close' => 1,
                                        'link_url' => 'NaoExibirLink',
					'partner_need_cpf' => 0,
					'integracao_transparente' => 0,
				),

	'Payletter' => array(
					'partner_name' => 'Payletter',
					'partner_id' => '10424',
					'partner_opr_codigo' => '68',
					'partner_active' => '1',
					'partner_currency_code' => 'BRL',
//	antigo				'partner_url' => 'http://gamengame.payletter.co.kr/EPREPAG/EPrepagFrm.aspx',
					'partner_url' => 'https://bill.gamengame.com/FillUp/EPrepag/EPrepagFrm.aspx',
//	antigo				'partner_ip' => '203.238.156.157',
					'partner_ip' => '121.160.9.111',
                                        'partner_ip_defined_list' => '121.160.9.111,121.160.9.112',
//	antigo				'notify_url' => 'http://gamengame.payletter.co.kr/EPREPAG/EPrepagNoti.aspx',
//	antigo				'return_url' => 'http://gamengame.payletter.co.kr/EPREPAG/EPrepagComplete.aspx',
//	antigo				'sonda_url' => 'http://gamengame.payletter.co.kr/EPREPAG/EPrepagSonda.aspx',
					'notify_url' => 'https://bill.gamengame.com/FillUp/EPrepag/EPrepagNoti.aspx',
					'return_url' => 'https://bill.gamengame.com/FillUp/EPrepag/EPrepagComplete.aspx',
					'sonda_url' => 'https://bill.gamengame.com/FillUp/EPrepag/EPrepagSonda.aspx',
					'partner_produto_id' => 171,
                                        'partner_testing_email' => 0,	
					'partner_email' => "emhwang@payletter.com",	
					'partner_do_notify' => 1,	
					'partner_do_renotify_automatico' => 0,	
					'partner_bypass_ip_check' => 1,	
					'partner_img_logo' => '',	
					'partner_img_logo' => 'https://www.e-prepag.com.br/prepag2/commerce/images/gamengame_login_logo.gif',	
					'amount_free' => 0,	
					'lista_formas_pagto_bloqueadas' => '',	
					'partner_need_cpf' => 0,	
					'integracao_transparente' => 0,
				),

	'Gamegoo' => array(
					'partner_name' => 'Gamegoo',
					'partner_id' => '10425',
					'partner_opr_codigo' => '78',
					'partner_active' => '1',
					'partner_currency_code' => 'BRL',
					'partner_url' => 'https://www.e-prepag.com.br/prepag2/commerce/integracao_nt.php',
					'partner_ip' => '187.45.247.106',
					'notify_url' => 'https://www.e-prepag.com.br/prepag2/commerce/partner_notify.php',
					'return_url' => 'http://www.e-prepag.com.br/prepag2/commerce/partner_return.php',
					'sonda_url' => '',
					'partner_produto_id' => $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR,	// Cyberstep - ??, Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 1,
					'partner_email' => "wagner@e-prepag.com.br",
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 1,
					'partner_img_logo' => '',
					'amount_free' => 0,
					'lista_formas_pagto_bloqueadas' => '',
					'partner_need_cpf' => 0,
					'integracao_transparente' => 0,
				),

	'Smart2Pay' => array(
					'partner_name' => 'Smart2Pay',
					'partner_id' => '10426',
					'partner_opr_codigo' => '78',
					'partner_active' => '0',
					'partner_currency_code' => 'BRL',
					'partner_url' => 'https://www.e-prepag.com.br/prepag2/commerce/integracao_nt.php',
					'partner_ip' => '187.45.247.106',
					'notify_url' => 'https://www.e-prepag.com.br/prepag2/commerce/partner_notify.php',
					'return_url' => 'http://www.e-prepag.com.br/prepag2/commerce/partner_return.php',
					'sonda_url' => '',
					'partner_produto_id' => $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR,	// Cyberstep - ??, Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 1,
					'partner_email' => "wagner@e-prepag.com.br",
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 1,
					'partner_img_logo' => '',
					'amount_free' => 0,
					'lista_formas_pagto_bloqueadas' => '',
					'partner_need_cpf' => 0,
					'integracao_transparente' => 0,
				),

	'NurigoGames' => array(
					'partner_name' => 'NurigoGames',
					'partner_id' => '10428',
					'partner_opr_codigo' => '78',
					'partner_active' => '0',
					'partner_currency_code' => 'BRL',
					'partner_url' => 'http://www.nurigogames.com.br/store/54/payment/eprepag',
					'partner_ip' => '54.207.0.223',
					'notify_url' => 'https://www.nuriwallet.com/store/54/request/jsonconfirmation',
					'return_url' => 'http://www.nurigogames.com.br/loja',
					'sonda_url' => '',
					'partner_produto_id' => 184,	// Cyberstep - ??, Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 1,
					'partner_email' => "wagner@e-prepag.com.br",
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 1,
					'partner_img_logo' => '',
					'amount_free' => 0,
					'lista_formas_pagto_bloqueadas' => '',
					'partner_need_cpf' => 0,
					'integracao_transparente' => 0,
				),

	'Skillab' => array(
					'partner_name' => 'Skillab',
					'partner_id' => '10429',
					'partner_opr_codigo' => '72',
					'partner_active' => '1',
					'partner_currency_code' => 'BRL',
					'partner_url' => 'http://server01.skillab.com/LongLaser/Home/EPrePagRedirect/?paymentMethodInternalId=IDENTIFICADODASKILLAB&email=EMAILDOCLIENTE&userInternalId=IDSKILLAB',
					'partner_ip' => '23.96.6.14',
// antigo				'notify_url' => 'https://devserver.skillab.com/LongLaser/Home/EPrePagNotify',
					'notify_url' => 'https://server01.skillab.com/LongLaser/Home/EPrePagNotify',
					'return_url' => 'http://server01.skillab.com/LongLaser/Home/EPrePagCallback',
					'sonda_url' => '',
					'partner_produto_id' => 177,	//Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 0,	
					'partner_email' => "chohfi@skillab.com",	
					'partner_do_notify' => 1,	
					'partner_do_renotify_automatico' => 0,	
					'partner_bypass_ip_check' => 1,	
					'partner_img_logo' => 'https://www.e-prepag.com.br/prepag2/commerce/images/skillab.png',	
					'amount_free' => 0,	
					'lista_formas_pagto_bloqueadas' => '1, 2, 5, 6, 7, 9, A, F, G, H, I, J, K, L, M',	
					'forma_pagto_direta' => '',	
					'forma_pagto_direta_gocash' => 0,	
					'forma_pagto_direta_in_frame' => 0,	
					'partner_need_cpf' => 0,	
					'integracao_transparente' => 0,
				),

	'Nanostudio' => array(
					'partner_name' => 'Nanostudio',
					'partner_id' => '10430',
					'partner_opr_codigo' => '78',
					'partner_active' => '0',
					'partner_currency_code' => 'BRL',
					'partner_url' => 'https://www.e-prepag.com.br/prepag2/commerce/integracao_na.php',
					'partner_ip' => '187.45.247.106',
					'notify_url' => 'https://www.e-prepag.com.br/prepag2/commerce/partner_notify.php',
					'return_url' => 'http://www.e-prepag.com.br/prepag2/commerce/partner_return.php',
					'sonda_url' => '',
					'partner_produto_id' => $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR,	//Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 1,
					'partner_email' => "wagner@e-prepag.com.br",
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 1,
					'partner_img_logo' => '',
					'amount_free' => 0,
					'lista_formas_pagto_bloqueadas' => '',
					'forma_pagto_direta' => '',
					'forma_pagto_direta_gocash' => 0,
					'forma_pagto_direta_in_frame' => 0,
					'partner_need_cpf' => 0,
					'integracao_transparente' => 0,
				),

	'Westlake' => array(
					'partner_name' => 'Westlake',
					'partner_id' => '10431',
					'partner_opr_codigo' => '83',
					'partner_active' => '1',
					'partner_currency_code' => 'BRL',
					'partner_url' => 'http://pay.walgames.com/eprepag/partner_url',
					'partner_ip' => '198.11.247.170,112.64.175.190',
					'notify_url' => 'http://pay.walgames.com/eprepag/notify_url',
					'return_url' => 'http://pay.walgames.com/eprepag/return_url',
					'sonda_url' => 'http://pay.walgames.com/eprepag/sonda_url',
					'partner_produto_id' => 210,	//Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 0,
					'partner_email' => "qsun@xinyoudi.com",
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 1,
					'partner_img_logo' => 'https://www.e-prepag.com.br/prepag2/commerce/images/Walgames_logo.png',
					'amount_free' => 0,
					'lista_formas_pagto_bloqueadas' => '',
					'forma_pagto_direta' => '',
					'forma_pagto_direta_gocash' => 0,
					'forma_pagto_direta_in_frame' => 0,
					'partner_need_cpf' => $CPF_OBRIGATORIO, 
					'integracao_transparente' => 0,
				),

	'Stark_Inter' => array(
					'partner_name' => 'Stark_Inter',
					'partner_id' => '10432',
					'partner_opr_codigo' => '84',
					'partner_active' => '1',
					'partner_currency_code' => 'BRL',
					'partner_url' => 'http://www.gamesdom.com',
					'partner_ip' => '162.218.55.9',
					'notify_url' => 'https://www.gamesdom.com/Hooks/notify_eprepag',
					'return_url' => 'http://www.gamesdom.com/Hooks/return_eprepag',
					'sonda_url' => 'http://www.gamesdom.com/Hooks/sonda_eprepag',
					'partner_produto_id' => "212,213,215,216,309,310,311,318,337",	//Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 0,
					'partner_email' => "rongxiang.cheng@stark-corp.com",
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 1,
					'partner_img_logo' => 'https://www.e-prepag.com.br/prepag2/commerce/images/Stark_Inter.png',
					/*
                                        'partner_img_prods_logo' => array(
							212 => 'https://www.e-prepag.com.br/prepag2/commerce/images/int.jpg',
							213 => 'https://www.e-prepag.com.br/prepag2/commerce/images/int.jpg',
							215 => 'https://www.e-prepag.com.br/prepag2/commerce/images/int.jpg',
							216 => 'https://www.e-prepag.com.br/prepag2/commerce/images/int.jpg',
						),
                                         */
                                        'amount_free' => 1,
					'lista_formas_pagto_bloqueadas' => '',
					'forma_pagto_direta' => '',
					'forma_pagto_direta_gocash' => 0,
					'forma_pagto_direta_in_frame' => 0,
					'partner_need_cpf' => $CPF_OBRIGATORIO,
					'integracao_transparente' => 0,
				),

	'ProficientCity' => array(
					'partner_name' => 'ProficientCity',
					'partner_id' => '10433',
					'partner_opr_codigo' => '82',
					'partner_active' => '1',
					'partner_currency_code' => 'BRL',
					'partner_url' => 'https://www.wartune.com',
					'partner_ip' => '207.198.114.80',
					'partner_ip_defined_list' => '216.152.138.115,207.198.114.81,207.198.114.70,207.198.114.80,3.224.28.164,54.161.99.245',
                                        'notify_url' => 'https://www.wartune.com/pay/notifyEP.action',
					'return_url' => 'https://www.wartune.com/pay/EPComplete.action', //'http://www.wartune.com/pay/payCompleted.action',
					'sonda_url' => 'https://www.wartune.com/pay/sondaEP.action',
					'partner_produto_id' => 206,	//Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 0,
					'partner_email' => "Becky@proficientcity.com",
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 1,
					'partner_img_logo' => 'https://www.e-prepag.com.br/prepag2/commerce/images/proficient_logo.png',
					'amount_free' => 0,
					'lista_formas_pagto_bloqueadas' => 'F, G, H, I, J, K, L, M',
					'forma_pagto_direta' => '',
					'forma_pagto_direta_gocash' => 0,
					'forma_pagto_direta_in_frame' => 0,
					'partner_need_cpf' => $CPF_OBRIGATORIO,
					'integracao_transparente' => 0,
				),

	'Goodgames' => array(
					'partner_name' => 'Goodgames',
					'partner_id' => '10434',
					'partner_opr_codigo' => '78',
					'partner_active' => '0',
					'partner_currency_code' => 'BRL',
					'partner_url' => 'http://itemshop.goodgamestudios.com/',
					'partner_ip' => '46.51.204.36',
					'partner_ip_defined_list' => '46.51.204.36,54.251.133.73,107.23.106.11',
                                        'notify_url' => 'http://itemshop.goodgamestudios.com/provider/notification/163',
					'return_url' => 'http://itemshop.goodgamestudios.com/provider/callback/163',
					'sonda_url' => 'http://itemshop.goodgamestudios.com/provider/sonda/163',
					'partner_produto_id' => 217,	//Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 1,
					'partner_email' => "shop-psp-announcements@goodgamestudios.com",
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 1,
					'partner_img_logo' => 'https://www.e-prepag.com.br/prepag2/commerce/images/goodgames.png',
					'amount_free' => 0,
					'lista_formas_pagto_bloqueadas' => '',
					'forma_pagto_direta' => '',
					'forma_pagto_direta_gocash' => 0,
					'forma_pagto_direta_in_frame' => 0,
					'partner_need_cpf' => $CPF_OBRIGATORIO,
					'integracao_transparente' => 0,
				),

	'XCloudGame' => array(
					'partner_name' => 'XCloudGame',
					'partner_id' => '10435',
					'partner_opr_codigo' => '92',
					'partner_active' => '1',
					'partner_currency_code' => 'BRL',
					'partner_url' => 'https://br-payment.xcloudgame.com/',
					'partner_ip' => '64.251.13.99',
					'partner_ip_defined_list' => '64.251.13.99,65.111.161.216,54.233.150.179',
                                        'notify_url' => 'https://br-payment.xcloudgame.com/index.php?m=Eprepag&a=pay_notify',
					'return_url' => 'https://br-payment.xcloudgame.com/index.php?m=Eprepag&a=pay_return', 
					'sonda_url' => 'https://br-payment.xcloudgame.com/index.php?m=Eprepag&a=pay_sonda',
					'partner_produto_id' => '249,254,255',	//Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 0,
					'partner_email' => "marlon@xcloudgame.com",
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 1,
					'partner_img_logo' => 'https://www.e-prepag.com.br/prepag2/commerce/images/xcloudgame.gif',
					'amount_free' => 1,
					'lista_formas_pagto_bloqueadas' => '',
					'forma_pagto_direta' => '',
					'forma_pagto_direta_gocash' => 0,
					'forma_pagto_direta_in_frame' => 0,
					'partner_need_cpf' => $CPF_OBRIGATORIO,
					'integracao_transparente' => 0,
				),

	'FHLGames' => array(
					'partner_name' => 'FHLGames',
					'partner_id' => '10436',
					'partner_opr_codigo' => '61',
					'partner_active' => '1',
					'partner_currency_code' => 'BRL',
					'partner_url' => 'http://www.kaybo.com.br/',
					'partner_ip' => '64.251.20.22',
					'partner_ip_defined_list' => '64.251.20.22,64.251.20.30',
                                        'notify_url' => 'https://payletter.kaybo1.com/FillUp/EPrepagOnline/EPrepagOnlineNoti.aspx',
					'return_url' => 'https://payletter.kaybo1.com/FillUp/EPrepagOnline/EPrepagOnlineResult.aspx', 
					'sonda_url' =>  'https://payletter.kaybo1.com/FillUp/EPrepagOnline/EPrepagOnlineSonda.aspx',
					'partner_produto_id' => '294,295,296',	//Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 0,
					'partner_email' => "ironchung@fhlgames.com",
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 1,
					'partner_img_logo' => 'https://www.e-prepag.com.br/prepag2/commerce/images/produtos/p_129.jpg',
					'amount_free' => 1,
					'lista_formas_pagto_bloqueadas' => '',
					'forma_pagto_direta' => '',
					'forma_pagto_direta_gocash' => 0,
					'forma_pagto_direta_in_frame' => 0,
					'partner_need_cpf' => $CPF_OBRIGATORIO,
					'integracao_transparente' => 0,
				),

	'FHLGames TESTE' => array(
					'partner_name' => 'FHLGames TESTE',
					'partner_id' => '10437',
					'partner_opr_codigo' => '93',
					'partner_active' => '1',
					'partner_currency_code' => 'BRL',
					'partner_url' => 'http://www.kaybo.com.br/',
					'partner_ip' => '64.251.20.22',
					'partner_ip_defined_list' => '64.251.20.22,64.251.20.30',
                                        'notify_url' => 'http://kaybo1.payletter.co.kr/FillUp/EPrepagOnline/EPrepagOnlineNoti.aspx',
					'return_url' => 'http://kaybo1.payletter.co.kr/FillUp/EPrepagOnline/EPrepagOnlineResult.aspx', 
					'sonda_url' =>  'http://kaybo1.payletter.co.kr/FillUp/EPrepagOnline/EPrepagOnlineSonda.aspx',
					'partner_produto_id' => '299',	//Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 1,
					'partner_email' => "ironchung@fhlgames.com",
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 1,
					'partner_img_logo' => 'https://www.e-prepag.com.br/prepag2/commerce/images/produtos/p_129.jpg',
					'amount_free' => 0,
					'lista_formas_pagto_bloqueadas' => '',
					'forma_pagto_direta' => '',
					'forma_pagto_direta_gocash' => 0,
					'forma_pagto_direta_in_frame' => 0,
					'partner_need_cpf' => 0,
					'integracao_transparente' => 0,
				),

	'Stark_Inter2' => array(
					'partner_name' => 'Stark_Inter2',
					'partner_id' => '10438',
					'partner_opr_codigo' => '84',
					'partner_active' => '1',
					'partner_currency_code' => 'BRL',
					'partner_url' => 'http://www.gamesdom.com',
					'partner_ip' => '162.218.55.9',
					'notify_url' => 'https://www.gamesdom.com/Hooks/notify_eprepag',
					'return_url' => 'http://pay.rockhippo.com/return_eprepag/',
					'sonda_url' => 'http://pay.rockhippo.com/sonda_eprepag/',
					'partner_produto_id' => "308",	//Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 0,
					'partner_email' => "rongxiang.cheng@stark-corp.com",
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 1,
					'partner_img_logo' => 'https://www.e-prepag.com.br/prepag2/commerce/images/Stark_Inter.png',
                                        'amount_free' => 1,
					'lista_formas_pagto_bloqueadas' => '',
					'forma_pagto_direta' => '',
					'forma_pagto_direta_gocash' => 0,
					'forma_pagto_direta_in_frame' => 0,
					'partner_need_cpf' => $CPF_OBRIGATORIO,
					'integracao_transparente' => 0,
				),

	'Nuage_Times' => array(
					'partner_name' => 'Nuage_Times',
					'partner_id' => '10439',
					'partner_opr_codigo' => '104',
					'partner_active' => '1',
					'partner_currency_code' => 'BRL',
					'partner_url' => 'http://www.unicoin-shop.com/',
					'partner_ip' => '119.81.22.27',
					'notify_url' => 'https://gainextreme.com/e-prepag/notify',
					'return_url' => 'https://gainextreme.com/e-prepag/return',
					'sonda_url' => 'https://gainextreme.com/e-prepag/sonda',
					'partner_produto_id' => "349",	//Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 1,
					'partner_email' => "developer@nuagetimes.com",
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 1,
					'partner_img_logo' => 'https://www.e-prepag.com.br/prepag2/commerce/images/unicoin.png',
                                        'amount_free' => 1,
					'lista_formas_pagto_bloqueadas' => '2, F, G, H, I, J, K, L, M',
					'forma_pagto_direta' => '',
					'forma_pagto_direta_gocash' => 0,
					'forma_pagto_direta_in_frame' => 0,
					'partner_need_cpf' => $CPF_OBRIGATORIO,
					'integracao_transparente' => 0,
				),
 
 	
	'Nuage_Times_Teste' => array(
					'partner_name' => 'Nuage_Times_Teste',
					'partner_id' => '10440',
					'partner_opr_codigo' => '105',
					'partner_active' => '1',
					'partner_currency_code' => 'BRL',
					'partner_url' => 'http://www.unicoin-shop.com/',
					'partner_ip' => '49.213.8.218,114.113.114.51,114.113.114.50',
					'notify_url' => 'http://nuagetime.info/e-prepag/notify',
					'return_url' => 'http://nuagetime.info/e-prepag/return',
					'sonda_url' => 'http://nuagetime.info/e-prepag/sonda',
					'partner_produto_id' => "348",	//Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 1,
					'partner_email' => "developer@nuagetimes.com",
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 1,
					'partner_img_logo' => 'https://www.e-prepag.com.br/prepag2/commerce/images/unicoin.png',
                                        'amount_free' => 1,
					'lista_formas_pagto_bloqueadas' => '2, F, G, H, I, J, K, L, M',
					'forma_pagto_direta' => '',
					'forma_pagto_direta_gocash' => 0,
					'forma_pagto_direta_in_frame' => 0,
					'partner_need_cpf' => $CPF_OBRIGATORIO,
					'integracao_transparente' => 0,
				),
 
	'Encripta' => array(
					'partner_name' => 'Encripta',
					'partner_id' => '10441',
					'partner_opr_codigo' => '103',
					'partner_active' => '1',
					'partner_currency_code' => 'BRL',
					'partner_url' => 'https://www.looke.com.br',            
					'partner_ip' => '104.41.0.47',
					'notify_url' => 'https://integrationhomolog.ottvs.com.br/EPrepag/EprepagLooke.aspx', 
                                        'return_url' => 'https://www.looke.com.br/Settings/Giftcode',
                                        'sonda_url' => '',
                                        'partner_produto_id' => "351",	//Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 1,
					'partner_email' => "juliano.alexandre@encripta.com.br",
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 1,
					'partner_img_logo' => 'https://www.e-prepag.com.br/prepag2/commerce/images/Epp_cash_loja.jpg',
                                        'amount_free' => 1,
					'lista_formas_pagto_bloqueadas' => '1, 5, 6, 7, 9, A, E, F, G, H, I, J, K, L, M',
					'forma_pagto_direta' => '',
					'forma_pagto_direta_gocash' => 0,
					'forma_pagto_direta_in_frame' => 0,
					'partner_need_cpf' => 0,
					'integracao_transparente' => 1,
				),
 
	'Playredfox' => array(
					'partner_name' => 'Playredfox',
					'partner_id' => '10442',
					'partner_opr_codigo' => '107',
					'partner_active' => '1',
					'partner_currency_code' => 'BRL',
					'partner_url' => 'https://www.playredfox.com',            
					'partner_ip' => '208.80.250.1',
                                        'partner_ip_intervals' => array(
                                                                        'interval_1' => array(
                                                                                'partner_ip_block'		=> '208.80.250.0/24',
                                                                                'partner_ip_block_start'	=> '208.80.250.0',
                                                                                'partner_ip_block_end'		=> '208.80.250.255',
                                                                        ),
                                                                ),
					'notify_url' => 'https://payheater-api.playredfox.com/v1/eprepag/notification.json', 
                                        'return_url' => 'https://www.playredfox.com/users/billing/eprepag_complete',
                                        'sonda_url' => 'https://payheater-api.playredfox.com/v1/eprepag/sonda.text',
                                        'partner_produto_id' =>  '353',	//Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 1,
					'partner_email' => "choonwoo@playredfox.com",
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 1,
					'partner_img_logo' => 'https://www.e-prepag.com.br/prepag2/commerce/images/Logo_lightgray.png',
                                        'amount_free' => 1,
					'lista_formas_pagto_bloqueadas' => '',
					'forma_pagto_direta' => '',
					'forma_pagto_direta_gocash' => 0,
					'forma_pagto_direta_in_frame' => 0,
					'partner_need_cpf' => $CPF_OBRIGATORIO,
					'integracao_transparente' => 0,
				),
 
	'RedFox_TESTE' => array(
					'partner_name' => 'RedFox_TESTE',
					'partner_id' => '10443',
					'partner_opr_codigo' => '108',
					'partner_active' => '1',
					'partner_currency_code' => 'BRL',
					'partner_url' => 'https://www.playredfox.com',            
					'partner_ip' => '208.80.250.1',
                                        'partner_ip_intervals' => array(
                                                                        'interval_1' => array(
                                                                                'partner_ip_block'		=> '208.80.250.0/24',
                                                                                'partner_ip_block_start'	=> '208.80.250.0',
                                                                                'partner_ip_block_end'		=> '208.80.250.255',
                                                                        ),
                                                                ),
					'notify_url' => 'http://payheater-api-development.playredfox.com/v1/eprepag/notification.json', 
                                        'return_url' => 'http://www-development.playredfox.com/users/billing/eprepag_complete',
                                        'sonda_url' => 'http://payheater-api-development.playredfox.com/v1/eprepag/sonda.text',
                                        'partner_produto_id' =>  '352',	//Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 1,
					'partner_email' => "choonwoo@playredfox.com",
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 1,
					'partner_img_logo' => 'https://www.e-prepag.com.br/prepag2/commerce/images/Logo_lightgray.png',
                                        'amount_free' => 1,
					'lista_formas_pagto_bloqueadas' => '',
					'forma_pagto_direta' => '',
					'forma_pagto_direta_gocash' => 0,
					'forma_pagto_direta_in_frame' => 0,
					'partner_need_cpf' => $CPF_OBRIGATORIO,
					'integracao_transparente' => 0,
				),
 
	'Pororo' => array(
					'partner_name' => 'Pororo',
					'partner_id' => '10444',
					'partner_opr_codigo' => '109',
					'partner_active' => '0',
					'partner_currency_code' => 'BRL',
					'partner_url' => '',            
					'partner_ip' => '',
					'notify_url' => '', 
                                        'return_url' => '',
                                        'sonda_url' => '',
                                        'partner_produto_id' =>  $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR,	//Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 1,
					'partner_email' => "",
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 1,
					'partner_img_logo' => '',
                                        'amount_free' => 1,
					'lista_formas_pagto_bloqueadas' => '',
					'forma_pagto_direta' => '',
					'forma_pagto_direta_gocash' => 0,
					'forma_pagto_direta_in_frame' => 0,
					'partner_need_cpf' => 0,
					'integracao_transparente' => 0,
				),
 
 
	'Gamecredits' => array(
					'partner_name' => 'Gamecredits',
					'partner_id' => '10445',
					'partner_opr_codigo' => '111',
					'partner_active' => '1',
					'partner_currency_code' => 'BRL',
					'partner_url' => 'https://gamecredits.com',            
					'partner_ip' => '13.81.127.5',
					'notify_url' => 'https://api.gamecredits.com/api/v1/partner-payment/eprepag/gateway-callback', 
                                        'return_url' => 'https://wallet.gamecredits.com/dashboard/paymentResult?payMethod=eprepag',
                                        'sonda_url' => '',
                                        'partner_produto_id' =>  '357',	//Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 1,
					'partner_email' => "admin@gamecredits.com",
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 1,
					'partner_img_logo' => 'https://www.e-prepag.com.br/prepag2/commerce/images/new_with_text_final2_resize.png',
                                        'amount_free' => 1,
					'lista_formas_pagto_bloqueadas' => '',
					'forma_pagto_direta' => '',
					'forma_pagto_direta_gocash' => 0,
					'forma_pagto_direta_in_frame' => 0,
					'partner_need_cpf' => $CPF_OBRIGATORIO,
					'integracao_transparente' => 0,
				),
 


	'ProficientCity_2' => array(
					'partner_name' => 'ProficientCity_2',
					'partner_id' => '10446',
					'partner_opr_codigo' => '82',
					'partner_active' => '1',
					'partner_currency_code' => 'BRL',
					'partner_url' => 'https://www.dragonawaken.com/portugues/index.html',
					'partner_ip' => '64.34.178.226',
					'partner_ip_defined_list' => '3.224.28.164,54.161.99.245',	
                                        'notify_url' => 'https://www.dragonawaken.com/portuguesPay/notifyEP.action',
					'return_url' => 'https://www.dragonawaken.com/portuguesPay/EPComplete.action',
					'sonda_url' => 'https://www.dragonawaken.com/portuguesPay/sondaEP.action',
					'partner_produto_id' => 374,	//Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 0,
					'partner_email' => "Becky@gamehollywood.com",
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 1,
					'partner_img_logo' => 'https://www.e-prepag.com.br/prepag2/commerce/images/dragon_awaken.png',
					'amount_free' => 1,
					'lista_formas_pagto_bloqueadas' => 'F, G, H, I, J, K, L, M',
					'forma_pagto_direta' => '',
					'forma_pagto_direta_gocash' => 0,
					'forma_pagto_direta_in_frame' => 0,
					'partner_need_cpf' => $CPF_OBRIGATORIO,
					'integracao_transparente' => 0,
				),


	'Xcloudgame_Int' => array(
					'partner_name' => 'Xcloudgame_Int',
					'partner_id' => '10447',
					'partner_opr_codigo' => '115',
					'partner_active' => '1',
					'partner_currency_code' => 'BRL',
					'partner_url' => 'https://paychannel.pagsmile.com/index.php?r=/eprepag/senddata',
					'partner_ip' => '52.40.255.9',
					//'partner_ip_defined_list' => '',	
                                        'notify_url' => 'https://paychannel.pagsmile.com/eprepag/payback',
					'return_url' => 'https://paychannel.pagsmile.com/eprepag/payreturn',
					'sonda_url' => 'https://paychannel.pagsmile.com/eprepag/paybsonda',
					'partner_produto_id' => 391,	//Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 0,
					'partner_email' => "rensiya@xcloudgame.com",
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 1,
					'partner_img_logo' => 'https://www.e-prepag.com.br/prepag2/commerce/images/pagsmile.png',
					'amount_free' => 1,
					'lista_formas_pagto_bloqueadas' => 'F, G, H, I, J, K, L, M',
					'forma_pagto_direta' => '',
					'forma_pagto_direta_gocash' => 0,
					'forma_pagto_direta_in_frame' => 0,
					'partner_need_cpf' => $CPF_OBRIGATORIO,
					'integracao_transparente' => 0,
				),

	'ProficientCity_3' => array(
					'partner_name' => 'ProficientCity_3',
					'partner_id' => '10448',
					'partner_opr_codigo' => '82',
					'partner_active' => '1',
					'partner_currency_code' => 'BRL',
					'partner_url' => 'https://es.dragonawaken.com',
					'partner_ip' => '216.152.138.114',
					'partner_ip_defined_list' => '216.152.138.114,64.34.178.194,3.224.28.164,54.161.99.245',	
                                        'notify_url' => 'https://es.dragonawaken.com/pay/notifyEP.action',
					'return_url' => 'https://es.dragonawaken.com/pay/EPComplete.action',
					'sonda_url' => 'https://es.dragonawaken.com/pay/sondaEP.action',
					'partner_produto_id' => 397,	//Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 0,
					'partner_email' => "nero@gamehollywood.com",
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 1,
					'partner_img_logo' => 'https://www.e-prepag.com.br/prepag2/commerce/images/dragon_awaken.png',
					'amount_free' => 1,
					'lista_formas_pagto_bloqueadas' => 'F, G, H, I, J, K, L, M',
					'forma_pagto_direta' => '',
					'forma_pagto_direta_gocash' => 0,
					'forma_pagto_direta_in_frame' => 0,
					'partner_need_cpf' => $CPF_OBRIGATORIO,
					'integracao_transparente' => 0,
				),


	'ProficientCity_4' => array(
					'partner_name' => 'ProficientCity_4',
					'partner_id' => '10449',
					'partner_opr_codigo' => '82',
					'partner_active' => '1',
					'partner_currency_code' => 'BRL',
					'partner_url' => 'https://testpurchase.proficientcity.com',
					'partner_ip' => '64.34.178.227',
					'partner_ip_defined_list' => '3.224.28.164,54.161.99.245',	
                                        'notify_url' => 'https://testpurchase.proficientcity.com/notifyEP',
					'return_url' => 'https://testpurchase.proficientcity.com/EPComplete',
					'sonda_url' => 'https://testpurchase.proficientcity.com/sondaEP',
					'partner_produto_id' => '402,'.$INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR,	//Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 1,
					'partner_email' => "nero@gamehollywood.com",
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 1,
					'partner_img_logo' => 'https://www.e-prepag.com.br/prepag2/commerce/images/dragon_awaken.png',
					'amount_free' => 1,
					'lista_formas_pagto_bloqueadas' => 'F, G, H, I, J, K, L, M',
					'forma_pagto_direta' => '',
					'forma_pagto_direta_gocash' => 0,
					'forma_pagto_direta_in_frame' => 0,
					'partner_need_cpf' => $CPF_OBRIGATORIO,
					'integracao_transparente' => 0,
				),

	'ProficientCity_5' => array(
					'partner_name' => 'ProficientCity_5',
					'partner_id' => '10450',
					'partner_opr_codigo' => '82',
					'partner_active' => '1',
					'partner_currency_code' => 'BRL',
					'partner_url' => 'https://purchase.gamehollywood.com',
					'partner_ip' => '216.152.138.115',
					'partner_ip_defined_list' => '216.152.138.115,207.198.114.81,3.224.28.164,54.161.99.245,34.231.81.62,3.224.167.198,18.215.32.137',	
                                        'notify_url' => 'https://purchase.gamehollywood.com/notifyEP',
					'return_url' => 'https://purchase.gamehollywood.com/EPComplete',
					'sonda_url' => 'https://purchase.gamehollywood.com/sondaEP',
					'partner_produto_id' => '405',	//Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 0,
					'partner_email' => "weiyongyu_wt@gamehollywood.com, wagner@e-prepag.com.br", // nero@gamehollywood.com
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 1,
					'partner_img_logo' => 'https://www.e-prepag.com.br/prepag2/commerce/images/game_hollywood.png',
					'amount_free' => 1,
					'lista_formas_pagto_bloqueadas' => 'F, G, H, I, J, K, L, M',
					'forma_pagto_direta' => '',
					'forma_pagto_direta_gocash' => 0,
					'forma_pagto_direta_in_frame' => 0,
					'partner_need_cpf' => $CPF_OBRIGATORIO,
					'integracao_transparente' => 0,
				),


	'ProficientCity_6' => array(
					'partner_name' => 'ProficientCity_6',
					'partner_id' => '10451',
					'partner_opr_codigo' => '82',
					'partner_active' => '1',
					'partner_currency_code' => 'BRL',
					'partner_url' => 'https://www.omegazodiac.com.br',
					'partner_ip' => '64.34.178.199',
					'partner_ip_defined_list' => '64.34.178.199,64.34.178.204,3.224.28.164,54.161.99.245',	
                                        'notify_url' => 'https://www.omegazodiac.com.br/pay/notifyEP.action',
					'return_url' => 'https://www.omegazodiac.com.br/pay/completeEP.action',
					'sonda_url' => 'https://www.omegazodiac.com.br/pay/sondaEP.action',
					'partner_produto_id' => '409',	//Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 0,
					'partner_email' => "nero@gamehollywood.com",
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 1,
					'partner_img_logo' => 'https://www.e-prepag.com.br/prepag2/commerce/images/LogoGameHollywood.png',
					'amount_free' => 1,
					'lista_formas_pagto_bloqueadas' => 'F, G, H, I, J, K, L, M',
					'forma_pagto_direta' => '',
					'forma_pagto_direta_gocash' => 0,
					'forma_pagto_direta_in_frame' => 0,
					'partner_need_cpf' => $CPF_OBRIGATORIO,
					'integracao_transparente' => 0,
				),


	'Garena' => array(
					'partner_name' => 'Garena',
					'partner_id' => '10452',
					'partner_opr_codigo' => '124',
					'partner_active' => '1',
					'partner_currency_code' => 'BRL',
					'partner_url' => 'http://testpay.garena.sg',
					'partner_ip' => '203.117.155.75',
					//'partner_ip_defined_list' => '',	
                                        'notify_url' => 'http://testpay.garena.sg/api/callback/eprepag/notify',
					'return_url' => 'http://testpay.garena.sg/',
					'sonda_url' => 'http://testpay.garena.sg/api/callback/eprepag/sonda',
					'partner_produto_id' => '420',	//Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 1,
					'partner_email' => "gop@garena.com",
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 1,
					'partner_img_logo' => 'https://www.e-prepag.com.br/prepag2/commerce/images/logo_eprepag.jpg',
					'amount_free' => 1,
					'lista_formas_pagto_bloqueadas' => '',
					'forma_pagto_direta' => '',
					'forma_pagto_direta_gocash' => 0,
					'forma_pagto_direta_in_frame' => 0,
					'partner_need_cpf' => $CPF_OBRIGATORIO,
					'integracao_transparente' => 0,
				),

    

	'Bilheteria_Mais' => array(
					'partner_name' => 'Bilheteria_Mais',
					'partner_id' => '10453',
					'partner_opr_codigo' => '138',
					'partner_active' => '1',
					'partner_currency_code' => 'BRL',
					'partner_url' => 'https://bilheteriamais.com.br',
					'partner_ip' => '138.197.149.168',
					//'partner_ip_defined_list' => '',	
                                        'notify_url' => 'https://bilheteriamais.com.br/wp-json/eprepag/v1/notify_url',
					'return_url' => 'https://bilheteriamais.com.br/wp-json/eprepag/v1/return_url',
					'sonda_url' => ' https://bilheteriamais.com.br/wp-json/eprepag/v1/sonda_url',
					'partner_produto_id' => '436',	//Treinamento - $INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID_STR
					'partner_testing_email' => 1,
					'partner_email' => "webmaster@bilheteriamais.com.br",
					'partner_do_notify' => 1,
					'partner_do_renotify_automatico' => 0,
					'partner_bypass_ip_check' => 1,
					'partner_img_logo' => 'https://www.e-prepag.com.br/prepag2/commerce/images/logo_eprepag.jpg',
					'amount_free' => 1,
					'lista_formas_pagto_bloqueadas' => '',
					'forma_pagto_direta' => '',
					'forma_pagto_direta_gocash' => 0,
					'forma_pagto_direta_in_frame' => 0,
					'partner_need_cpf' => $CPF_OBRIGATORIO,
					'integracao_transparente' => 0,
				),

    
			// 10421 - já está tomado
			// 10427 - já está tomado

);

// CODRETEPP => DESCRIPTION
$notify_list = array(
	0 => 'Order successfully confirmed',
	1 => 'Order already confirmed',
	2 => 'Incorrect parameters values',
	3 => 'Order not found',
	4 => 'Postback is missing data',
	5 => 'Order not payed yet',
	6 => 'Order not processed yet',
	7 => 'Order canceled',
	8 => 'System not available',
);

$a_codretepp = array(
	array('codretepp' => '0', 'description' => 'Order successfully confirmed'),
	array('codretepp' => '1', 'description' => 'Order already confirmed'),
	array('codretepp' => '2', 'description' => 'Incorrect parameters values'),
	array('codretepp' => '3', 'description' => 'Order not found'),
	array('codretepp' => '4', 'description' => 'Postback is missing data'),
	array('codretepp' => '5', 'description' => 'Order not payed yet'),
	array('codretepp' => '6', 'description' => 'Order not processed yet'),
	array('codretepp' => '7', 'description' => 'Order canceled'),
	array('codretepp' => '8', 'description' => 'System not available'),
);




function get_Integracao_origem() {
	global $INTEGRACAO_STORE_ID;

	$INTEGRACAO_STORE_ID = $GLOBALS['_POST']['store_id'];

	return $INTEGRACAO_STORE_ID;
}

// Testa se temos um pedido de parceiros ou é de dentro da loja
// Obtem a origem a partir de IP e HTTP_Referer
function is_Integracao() {
	global $INTEGRACAO_URL_EPREPAG_TESTE, $INTEGRACAO_PERMITIDO;
	global $partner_list;

	$partner_id_tmp = get_Integracao_origem();	//$GLOBALS['_POST']['store_id'];
	$partner_bypass_ip_check_tmp = getPartner_param_By_ID('partner_bypass_ip_check', $partner_id_tmp);
	$bypass_ip_check = ($partner_bypass_ip_check_tmp==1)?true:false;

	$GLOBALS['_SERVER']['integration_debug_info'] = "";

	// Permite acesso para páginas no nosso site
	$epp_http_referer = "https://www.e-prepag.com.br/prepag2/commerce/";
	$epp_remote_addr = "187.45.247.106";	//"189.38.238.205";
//	$epp_remote_addr_1 = "201.6.243.44";

$remote_server_ip_address = gethostbyname(get_server_DNS_by_URL($GLOBALS['_SERVER']['HTTP_REFERER']));
$s_server_vars = "SERVER Information in \$_SERVER\n";

$s_server_vars .= "SERVER gethostbyname: ".$remote_server_ip_address."\n";
$s_server_vars .= "SCRIPT_NAME: ".$GLOBALS['_SERVER']['SCRIPT_NAME']."\n";
$s_server_vars .= "SERVER_ADDR: ".$GLOBALS['_SERVER']['SERVER_ADDR']."\n";
$s_server_vars .= "LOCAL_ADDR: ".$GLOBALS['_SERVER']['LOCAL_ADDR']."\n";

$s_server_vars .= "SERVER_ADDR: ".$GLOBALS['_SERVER']['SERVER_ADDR']."\n";
$s_server_vars .= "SERVER_NAME: ".$GLOBALS['_SERVER']['SERVER_NAME']."\n";
$s_server_vars .= "SERVER_SOFTWARE: ".$GLOBALS['_SERVER']['SERVER_SOFTWARE']."\n";
$s_server_vars .= "SERVER_PROTOCOL: ".$GLOBALS['_SERVER']['SERVER_PROTOCOL']."\n";

$s_server_vars .= "REMOTE_ADDR: ".$GLOBALS['_SERVER']['REMOTE_ADDR']."\n";
$s_server_vars .= "HTTP_REFERER: ".$GLOBALS['_SERVER']['HTTP_REFERER']."\n";
$s_server_vars .= "HTTP_CLIENT_IP: ".$GLOBALS['_SERVER']['HTTP_CLIENT_IP']."\n";
$s_server_vars .= "HTTP_X_FORWARDED_FOR: ".$GLOBALS['_SERVER']['HTTP_X_FORWARDED_FOR']."\n";

$s_server_vars .= "REMOTE_HOST: ".$GLOBALS['_SERVER']['REMOTE_HOST']."\n";
$s_server_vars .= "HTTP_HOST: ".$GLOBALS['_SERVER']['HTTP_HOST']."\n";

	$b_epp = ( (isset($GLOBALS['_SERVER']['HTTP_REFERER']) &&
				(strpos(strtoupper($GLOBALS['_SERVER']['HTTP_REFERER']) , strtoupper($epp_http_referer))!==false) ) &&
/*
				(	(isset($GLOBALS['_SERVER']['REMOTE_ADDR']) &&
					(strpos(strtoupper($GLOBALS['_SERVER']['REMOTE_ADDR']) , strtoupper($epp_remote_addr))!==false) ) ||
					(isset($GLOBALS['_SERVER']['REMOTE_ADDR']) &&
					(strpos(strtoupper($GLOBALS['_SERVER']['REMOTE_ADDR']) , strtoupper($epp_remote_addr_1))!==false) )
				)
*/
				(	(isset($remote_server_ip_address) &&
						(strpos(strtoupper($remote_server_ip_address) , strtoupper($epp_remote_addr))!==false) )
				)
			);

//echo (($b_epp)?"*":"-")."<br>";
grava_log_integracao("\nEm is_Integracao() Tem Origem EPP?: ".(($b_epp)?"SIM":"não")." \n     Tem ByPass IP check?: ".(($bypass_ip_check)?"SIM":"não")."\nPOST['store_id']: ".$GLOBALS['_POST']['store_id']." (".is_Partner_valido($GLOBALS['_POST']['store_id']).")\n".$s_server_vars."\n*******************\n");

	$partner_id_tmp = $GLOBALS['_POST']['store_id'];
	$url_parceiro_tmp = getPartner_param_By_ID('partner_url', $partner_id_tmp);
	$ip_parceiro_tmp = getPartner_param_By_ID('partner_ip', $partner_id_tmp);
	$b_ip_valid = false;

	// Testa IP Address
	$ip_remote_address = $GLOBALS['_SERVER']['REMOTE_ADDR'];
//	$b_ip_valid = b_is_address_valid($ip_parceiro_tmp, $partner_ip_block_start_tmp, $partner_ip_block_end_tmp);
	$b_ip_valid = b_is_address_valid($partner_id_tmp, $ip_remote_address);
grava_log_integracao("\nEm is_Integracao($partner_id_tmp, IP: '$ip_remote_address') IP valid?: ".(($b_ip_valid)?"SIM":"não")." \n*******************\n");

//echo $partner_id_tmp."<br>";
//echo $url_parceiro_tmp."-".$ip_parceiro_tmp."<br>";

	if ($INTEGRACAO_PERMITIDO=="1") {
		// Pedido vem do site da EPP ou do site cadastrado para o parceiro
//				(isset($GLOBALS['_SERVER']['REMOTE_ADDR']) &&
//				(strpos(strtoupper($GLOBALS['_SERVER']['REMOTE_ADDR']) , strtoupper($ip_parceiro_tmp))!==false) ))

/*
// Teste antigo, não é o que tem qeu testar
	||	(
			(isset($GLOBALS['_SERVER']['HTTP_REFERER']) &&
				(strpos(strtoupper($GLOBALS['_SERVER']['HTTP_REFERER']) , strtoupper($url_parceiro_tmp))!==false) ) &&
			(isset($remote_server_ip_address) &&
				(strpos(strtoupper($remote_server_ip_address) , strtoupper($ip_parceiro_tmp))!==false) )
		)

*/
		if( $b_epp || $bypass_ip_check || $b_ip_valid ) {
			if(is_Partner_valido($partner_id_tmp)) {
				// Integração aceita
				$integracao_b_origem = getPartner_param_By_ID('partner_name', $partner_id_tmp);
				$s_msg = "Integration Source ACCEPTED (from: '$integracao_b_origem', store_id: '$partner_id_tmp'): ".date("Y-m-d H:i:s")."\n  IP: ".$remote_server_ip_address."\n  URL: ".$GLOBALS['_SERVER']['HTTP_REFERER']."\n";
				grava_log_integracao($s_msg);
			} else {
				$integracao_b_origem = "";
				$s_msg = "Integration Source Refused - store_id UNKNOWN (from store_id: '$partner_id_tmp'): ".date("Y-m-d H:i:s")."\n  IP: ".$remote_server_ip_address."\n  URL: ".$GLOBALS['_SERVER']['HTTP_REFERER']."\n";
				grava_log_integracao($s_msg);
			}
		} else {
			// Integração desconhecida -> não aceita
			$s_msg = "Integration Source unknown (b_epp: ".(($b_epp)?"OK":"Nope").", bypass_ip_check: ".(($bypass_ip_check)?"OK":"Nope").", b_ip_valid: ".(($b_ip_valid)?"OK":"Nope").") -> blocked: ".date("Y-m-d H:i:s")."\n  IP: ".$remote_server_ip_address."\n  URL: ".$GLOBALS['_SERVER']['HTTP_REFERER']."\n";
			grava_log_integracao($s_msg);
		}
	} else {
		// Gateway fora de serviço
		$s_msg = "Integration blocked (INTEGRACAO_PERMITIDO: ".(($INTEGRACAO_PERMITIDO)?"OK":"Nope")."): ".date("Y-m-d H:i:s")."\n  IP: ".$remote_server_ip_address."\n  URL: ".$GLOBALS['_SERVER']['HTTP_REFERER']."\n";
		grava_log_integracao($s_msg);
	}
//echo "integracao_b_origem: '$integracao_b_origem'<br>";

	// Debug
	send_debug_info_by_email("E-Prepag - Testing integration (check Integration) (integracao_b_origem: $integracao_b_origem)", $s_msg, $partner_id_tmp);

	return $integracao_b_origem;
}

function is_Integracao_valida() {
	global $partner_list;

	$integracao_b_origem = get_Integracao_origem();
//echo "integracao_b_origem: $integracao_b_origem<br>";
	$bret = is_Partner_valido($integracao_b_origem);
	if($bret) {
		$s_msg = "Integration source is valid ($integracao_b_origem)";
	} else {
		$s_msg = "Integration source is unknown";
	}
	// Debug
	send_debug_info_by_email("E-Prepag - Testing integration (validate Integration source)", $s_msg, $partner_id_tmp);

	return $bret;

//	foreach($partner_list as $key => $val) {
//		if($val["partner_id"]==$integracao_b_origem && $val["partner_active"]=="1") {
//			return true;
//		}
//	}
//	return false;
}

function is_Partner_valido($partner_id) {
	global $partner_list;

//echo "partner_id: $partner_id<br>";
	foreach($partner_list as $key => $val) {
//echo "'$key' =&gt; partner_id: '".$val['partner_id']."'; partner_active: '".$val['partner_active']."'<br>";
//echo "Conditions".($val['partner_id']==$partner_id)."/".($val['partner_active']=="1")."<br>";
		if($val['partner_id']==$partner_id && $val['partner_active']=="1") {
			return true;
		}
	}
	return false;
}

// Não receve os URL, agora são parte do cadastro do parceiro
//		(trim($params["notify_url"])!="")
//		(trim($params["return_url"])!="")
function is_Integracao_params_valida($params, &$mensagem = null) {
//	global $INTEGRACAO_STORE_ID;
/*
echo " <font color='green'>is_Integracao_params_valida".(	(($params["store_id"]==$INTEGRACAO_STORE_ID) &&
		(is_numeric($params["amount"])) &&
		(is_numeric($params["order_id"])) &&
		($params["currency_code"] == 'BRL') &&
//		(trim($params["order_description"])!="") &&
		(trim($params["client_email"])!="")
		) ?"OK":"Nope")."</font><br>";
*/
	$s_msg = "Integration - Validating parameters - ".date("Y-m-d H:i:s")." - STORE_ID: ".$params["store_id"]."\n".print_r($params,true)."\n";

/*
	if(($params["store_id"]=='10400') && ($params["amount"] == '36000') && ($params["order_id"] == '1fe2f27bbd24f1361bd8d60ffd58ce59') && ($params['client_email'] == "reinaldops@hotmail.com")) {
		$params["amount"] = '144000';
grava_log_integracao("In is_Integracao_params_valida DUMMY: (store_id=".$params["store_id"]."), TROCA VALOR  (amount=".$params["amount"].")\n");
	} else {
//		echo "*";
//grava_log_integracao("In is_Integracao_params_valida DUMMY: (store_id=".$params["store_id"]."), TROCA VALOR  (amount=".$params["amount"].")\n");
	}
*/
	$msg = "";
	// Valida dados de entrada
	if(!is_Partner_valido($params["store_id"])) { $msg .= "Partner invalid (".get_param_value($params["store_id"]).")\n"; }
//	if(!is_numeric($params["order_id"]))		{ $msg .= "order_id invalid (".get_param_value($params["order_id"]).")\n"; }
	if(!($params["order_id"]))					{ $msg .= "order_id invalid - missing value ('".get_param_value($params["order_id"])."')\n"; }
	if(!is_numeric($params["amount"]))			{ $msg .= "amount invalid (".get_param_value($params["amount"]).")\n"; }
	if(($params["amount"]/100)>$GLOBALS['RISCO_GAMERS_FREE_MAXIMO_POR_PEDIDO']) { $msg .= "amount too high: ".($params["amount"]/100)." > ".$GLOBALS['RISCO_GAMERS_FREE_MAXIMO_POR_PEDIDO']."\n"; }
grava_log_integracao("In is_Integracao_params_valida: (store_id=".$params["store_id"]."), (amount=".$params["amount"].") - TESTING AMOUNT:  ".((($params["amount"]/100)<=$GLOBALS['RISCO_GAMERS_FREE_MAXIMO_POR_PEDIDO'])?"OK":"Too much")." (amount: ".($params["amount"]/100).", Max: ".$GLOBALS['RISCO_GAMERS_FREE_MAXIMO_POR_PEDIDO'].")\n");

//	if(!is_numeric($params["client_id"]))		{ $msg .= "client_id invalid (".get_param_value($params["client_id"]).")\n"; }
//	if(!is_numeric($params["transaction_id"]))	{ $msg .= "transaction_id invalid (".get_param_value($params["transaction_id"]).")\n"; }
//	if(!($params["transaction_id"]))			{ $msg .= "transaction_id invalid - missing value ('".get_param_value($params["transaction_id"])."')\n"; }

        //validando email
        $varRegExp = '/^[\+_a-z0-9-]+(\.[\+_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/i';
        //$varRegExp = '^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$';
        if(!preg_match($varRegExp,$params["client_email"])) {
            $mensagem = "<h4>Olá,<br>O e-mail informado ou o e-mail cadastrado no jogo não possui um formato válido: |<font color=blue>".$params["client_email"]."</font>|<br>Para garantirmos a segurança e a qualidade do serviço precisamos que seja fornecido um e-mail correto.<br>Por favor corrija o e-mail inserido na tela anterior, ou então o seu e-mail cadastrado na conta do jogo, e tente novamente.</h4>\n";
            $msg .= "Email disagree with regular expression validation\n[".$params["client_email"]."]";
        }

        //if(!($params["client_email"]))				{ $msg .= "client_email invalid - missing value ('".get_param_value($params["client_email"])."')\n"; }
	//if((strpos($params["client_email"], "@")===false))				{ $msg .= "client_email invalid - missing '@' ('".get_param_value($params["client_email"])."')\n"; }
	//if((strpos($params["client_email"], " ")!==false))				{ $msg .= "client_email invalid - character ' ' ('".get_param_value($params["client_email"])."')\n"; }
	if(!($params["currency_code"]=='BRL') )		{ $msg .= "currency_code invalid - missing value (".get_param_value($params["currency_code"]).")\n"; }
/*
	if( ($params["store_id"]==$INTEGRACAO_STORE_ID) &&
		(is_numeric($params["amount"])) &&
		(is_numeric($params["order_id"])) &&
		($params["currency_code"] == 'BRL') &&
//		(trim($params["order_description"])!="") &&
		(trim($params["client_email"])!="")
		) {
*/
	if(!$msg) {
		$ret = true;
		$s_msg .= "\nParameters ACCEPTED\n";
	} else {
		$ret = false;
		$s_msg .= "\nParameters refused ($msg)\n";
	}

	// Debug
	if($params && is_array($params)) {
//		$s_msg .= "\nParams: \n".print_r($params, true)."\n";
	} else {
		$s_msg .= "\nParams: NOT ARRAY or EMPTY\n".print_r($params, true)."\n";
	}
	grava_log_integracao($s_msg."\nb_testing_email: ".$b_testing_email."\n");
	send_debug_info_by_email("E-Prepag - Testing integration (Validate parameters)", $s_msg, $params["store_id"]);

	return $ret;
}

function get_Integracao_params_from_POST() {

	$params = array();

	if(isset($GLOBALS['_POST']['store_id']))			$params['store_id']				= $GLOBALS['_POST']['store_id'];
//	if(isset($GLOBALS['_POST']['return_url']))			$params['return_url']			= $GLOBALS['_POST']['return_url'];
//	if(isset($GLOBALS['_POST']['notify_url']))			$params['notify_url']			= $GLOBALS['_POST']['notify_url'];

	if(isset($GLOBALS['_POST']['currency_code']))		$params['currency_code']		= $GLOBALS['_POST']['currency_code'];
	if(isset($GLOBALS['_POST']['order_id']))			$params['order_id']				= $GLOBALS['_POST']['order_id'];
	if(isset($GLOBALS['_POST']['order_description']))	$params['order_description']	= $GLOBALS['_POST']['order_description'];
	if(isset($GLOBALS['_POST']['amount']))				$params['amount']				= $GLOBALS['_POST']['amount'];
	if(isset($GLOBALS['_POST']['product_id']))			$params['product_id']			= $GLOBALS['_POST']['product_id'];
	if(isset($GLOBALS['_POST']['transaction_id']))		$params['transaction_id']		= $GLOBALS['_POST']['transaction_id'];

	if(isset($GLOBALS['_POST']['client_email']))		$params['client_email']			= $GLOBALS['_POST']['client_email'];
	if(isset($GLOBALS['_POST']['client_id']))			$params['client_id']			= $GLOBALS['_POST']['client_id'];
	if(isset($GLOBALS['_POST']['client_name']))			$params['client_name']			= $GLOBALS['_POST']['client_name'];
	if(isset($GLOBALS['_POST']['client_zip_code']))		$params['client_zip_code']		= $GLOBALS['_POST']['client_zip_code'];
	if(isset($GLOBALS['_POST']['client_street']))		$params['client_street']		= $GLOBALS['_POST']['client_street'];
	if(isset($GLOBALS['_POST']['client_suburb']))		$params['client_suburb']		= $GLOBALS['_POST']['client_suburb'];
	if(isset($GLOBALS['_POST']['client_number']))		$params['client_number']		= $GLOBALS['_POST']['client_number'];
	if(isset($GLOBALS['_POST']['client_city']))			$params['client_city']			= $GLOBALS['_POST']['client_city'];
	if(isset($GLOBALS['_POST']['client_state']))		$params['client_state']			= $GLOBALS['_POST']['client_state'];
	if(isset($GLOBALS['_POST']['client_country']))		$params['client_country']		= $GLOBALS['_POST']['client_country'];
	if(isset($GLOBALS['_POST']['client_telephone']))	$params['client_telephone']		= $GLOBALS['_POST']['client_telephone'];
	if(isset($GLOBALS['_POST']['language']))			$params['language']				= $GLOBALS['_POST']['language'];
	if(isset($GLOBALS['_POST']['cmd']))					$params['cmd']					= $GLOBALS['_POST']['cmd'];

//echo "<pre>";
//print_r($params);
//echo "</pre>";

	return $params;
}
// ================================================
function get_Integracao_carrinho($params) {

	$carrinho = array();

	$carrinho["currency_code"]		= $params["currency_code"];
	$carrinho["order_id"]			= $params["order_id"];
	$carrinho["order_description"]	= $params["order_description"];
	$carrinho["amount"]				= $params["amount"];

	return $carrinho;
}

// ================================================
function get_Integracao_modelo($store_id, $valor, &$iativo, &$nome, $product_id = 0) {
	global $partner_list;

//grava_log_integracao("In get_Integracao_modelo: (store_id=$store_id), (valor=$valor)\n");
	$prod_id = getPartner_produto_id_By_ID($store_id);
	$amount_free = getPartner_amount_free_By_ID($store_id);
//if($store_id=="10407") echo "store_id: ".$store_id.", prod_id: ".$prod_id.", amount_free: $amount_free<br>";
	// Se não tem produto_id cadastrado -> retorna vazio (caso contrário o SQL pode retornar uma lista de produtos)
	if(!$prod_id) {
		return 0;
	}

//grava_log_integracao("In get_Integracao_modelo: (prod_id=$prod_id)\n");
	$sql = "select * from tb_operadora_games_produto ogp ";
//	if($amount_free==0) {	// Não faz diferencia se o cadastro é amount_free: Sempre vai ter que cadastrar pelo menos um modelo, mesmo inativo
		$sql .= "	inner join tb_operadora_games_produto_modelo ogpm on ogp.ogp_id = ogpm.ogpm_ogp_id ";
//	}
	$sql .= "where 1=1 ";
	$sql .= "	and ogp.ogp_id in ($prod_id) ";
	if($product_id>0) {
		$sql .= "	and ogp.ogp_id = $product_id ";
	}
	if($valor>0) {
		$sql .= "	and ogpm.ogpm_pin_valor = $valor ";
	}
	$sql .= "order by ogp_id desc";
//		"	--and (0=1 or ogp.ogp_ativo = 1) "
//if($store_id=="10407") echo "".str_replace("\n","<br>\n",$sql)."<br>";
//echo "".$sql."<br>";
//die("Stop");

//grava_log_integracao("In get_Integracao_modelo (ASDERF): $sql\n");
	$rs = SQLexecuteQuery($sql);

	$mod_id = "0";
	if($rs && pg_num_rows($rs) != 0){
		$rs_row = pg_fetch_array($rs);
		$mod_id = $rs_row['ogpm_id'];
		$iativo = $rs_row['ogpm_ativo'];
		$nome = $rs_row['ogp_nome'];
	}
//grava_log_integracao("In get_Integracao_modelo: mod_id: $mod_id, iativo: $iativo, nome: $nome\n");

	return $mod_id;
}

// ================================================
// Salva em BD o registro do pedido do parceiro
function set_Integracao_registro() {

	$b_ret = false;

//print_r2($GLOBALS['_POST']);

	// Procura por pedido - para evitar o F5
	$sql = "select * from tb_integracao_pedido where ip_store_id = '". $GLOBALS['_POST']['store_id']."' and ip_order_id = '". $GLOBALS['_POST']['order_id']."';";
//if($GLOBALS['_POST']['client_email']=="gamer_test@HOTMAIL.COM") echo  $sql ."<br>";

	$rs = SQLexecuteQuery($sql);

	if(pg_num_rows($rs) > 0){
		// Este pedido já foi registrado
		$rs_row = pg_fetch_array($rs);
		$ip_vg_id = $rs_row['ip_vg_id'];

		if($ip_vg_id>0) {
/*
			// procura por venda - para evitar o F5 ou manipulação
			$sql = "select * from tb_venda_games where vg_id = $ip_vg_id and vg_integracao_parceiro_origem_id = '". $GLOBALS['_POST']['store_id']."';";
//if($GLOBALS['_POST']['client_email']=="gamer_test@HOTMAIL.COM") echo  $sql ."<br>";

			$rs1 = SQLexecuteQuery($sql);

			if($rs1 || pg_num_rows($rs1) > 0){
				// Este pedido já foi registrado e tem venda cadastrada
				// -> logout e avisa
				echo "**";
			}
*/
			//
		}
	} else {

		// OK -> insere novo pedido de integração
		$sql = "insert into tb_integracao_pedido ( ip_store_id, ip_currency_code, ip_order_id, ip_order_description, ip_product_id, ip_amount, ip_store_session, ip_client_email, ip_language, ip_client_id, ip_client_name, ip_client_zip_code, ip_client_street, ip_client_suburb, ip_client_number, ip_client_city, ip_client_state, ip_client_country, ip_client_telephone ) values ( '".
			$GLOBALS['_POST']['store_id']."', '".
			$GLOBALS['_POST']['currency_code']."', '".
			$GLOBALS['_POST']['order_id']."', '".
			str_replace("'","''",$GLOBALS['_POST']['order_description'])."', ".
			(($GLOBALS['_POST']['product_id'])?$GLOBALS['_POST']['product_id']:0).", '".
			$GLOBALS['_POST']['amount']."', '".
			$GLOBALS['_POST']['store_session']."', '".
			str_replace("'","''",$GLOBALS['_POST']['client_email'])."', '".
			$GLOBALS['_POST']['language']."', '".
			$GLOBALS['_POST']['client_id']."', '".
			str_replace("'","''",$GLOBALS['_POST']['client_name'])."', '".
			$GLOBALS['_POST']['client_zcode']."', '".
			str_replace("'","''",$GLOBALS['_POST']['client_street'])."', '".
			str_replace("'","''",$GLOBALS['_POST']['client_suburb'])."', '".
			$GLOBALS['_POST']['client_number']."', '".
			str_replace("'","''",$GLOBALS['_POST']['client_city'])."', '".
			str_replace("'","''",$GLOBALS['_POST']['client_state'])."', '".
			str_replace("'","''",$GLOBALS['_POST']['client_country'])."', '".
			$GLOBALS['_POST']['client_telephone']."');";

	//		ip_transaction_id,
	//		str_replace("'","''",$GLOBALS['_POST']['transaction_id'])."', '".

	//		ip_return_url, ip_notify_url,
	//		$GLOBALS['_POST']['return_url']."', '".
	//		$GLOBALS['_POST']['notify_url']."', '".

//if($GLOBALS['_POST']['client_email']=="gamer_test@HOTMAIL.COM") echo  $sql ."<br>";

		grava_log_integracao($sql);

		$rs = SQLexecuteQuery($sql);
		$b_ret = true;
	}

	return $b_ret;
}

function VerifyPaymentsMethods($store_id){
	global $partner_list;
	foreach($partner_list as $key => $val) {
		if($val['partner_id']==$store_id) {
			 $oprCod = $val['partner_opr_codigo'];
			 $sql = "select opr_tipo_pagto_bloqueados from operadoras where opr_status = '1' and opr_flag_possui_restricao_pagto = 1 and opr_codigo = ".$oprCod;
			 $rs = SQLexecuteQuery($sql);
			 $resultado = pg_fetch_array($rs);
			 return $resultado;
		}
	}
}

function verifica_nome_operadora($store_id){
	global $partner_list;
	foreach($partner_list as $key => $val) {
		if($val['partner_id']==$store_id) {
			 $oprCod = $val['partner_opr_codigo'];
			 $sql = "select opr_nome_loja from operadoras where opr_status = '1' and opr_flag_possui_restricao_pagto = 1 and opr_codigo = ".$oprCod;
			 $rs = SQLexecuteQuery($sql);
			 $resultado = pg_fetch_array($rs);
			 return $resultado;
		}
	}

}


// ================================================
function get_Integracao_nome_parceiro($store_id) {
	global $partner_list;

	$snome = "???";
	$snome = getPartner_name_By_ID($store_id);

	return $snome;
}

// ================================================
function set_Integracao_marca_sessao_login($origem_id, $order_id) {
	$GLOBALS['_SESSION']['integracao_is_parceiro'] = "OK";
	$GLOBALS['_SESSION']['integracao_origem_id'] = $origem_id;
	$GLOBALS['_SESSION']['integracao_order_id'] = $order_id;
	$GLOBALS['_SESSION']['integracao_transaction_id'] = "";
	$GLOBALS['_SESSION']['integracao_error_msg'] = "";
}

// ================================================
// ver em ajax_info_pagamento.php - usa diretamente este código para não ter que incluir este arquivo
function set_Integracao_marca_sessao_logout() {
	//Invalida a sessao, caso exista
	//	(para evitar o caso onde o usuário faz login na página de pagamentos com PINs EPP para usar o saldo e volta a pagamento_int.php,
	//		que tenta o login automatico de integração e não consegue invalidando a integração, mas ficaria o login feito anteriormente)
	//		não seria uma ameaça porque o usuário teria que fazer login antes, mas queremos que o login de integração seja usado apenas para integração
	if(isValidaSessao()) {
		cancelarSessao();
	}

	$GLOBALS['_SESSION']['integracao_is_parceiro'] = "";
	$GLOBALS['_SESSION']['integracao_origem_id'] = "";
	$GLOBALS['_SESSION']['integracao_order_id'] = "";
	$GLOBALS['_SESSION']['integracao_transaction_id'] = "";
	$GLOBALS['_SESSION']['integracao_error_msg'] = "";
	$GLOBALS['_SESSION']['integracao_autenticado'] = "";

	unset($GLOBALS['_SESSION']['integracao_is_parceiro']);
	unset($GLOBALS['_SESSION']['integracao_origem_id']);
	unset($GLOBALS['_SESSION']['integracao_order_id']);
	unset($GLOBALS['_SESSION']['integracao_transaction_id']);
	unset($GLOBALS['_SESSION']['integracao_autenticado']);
//	unset($GLOBALS['_SESSION']['integracao_error_msg']);
}

// ================================================
function set_Integracao_error_msg($msg, $breset) {
	if($breset) {
		$GLOBALS['_SESSION']['integracao_error_msg'] = "";
	}
	$GLOBALS['_SESSION']['integracao_error_msg'] .= $msg;
}

// ================================================
// ver em ajax_info_pagamento.php - usa diretamente este código para não ter que incluir este arquivo
function get_Integracao_is_sessao_logged() {
	if(isset($GLOBALS['_SESSION']['integracao_is_parceiro']) && $GLOBALS['_SESSION']['integracao_is_parceiro']=="OK" && isset($GLOBALS['_SESSION']['integracao_origem_id']) && isset($GLOBALS['_SESSION']['integracao_order_id'])) {
		return $GLOBALS['_SESSION']['integracao_origem_id'];
	}
	return "";
}

// ================================================
function get_Integracao_order_id_is_sessao_logged() {
	if(isset($GLOBALS['_SESSION']['integracao_is_parceiro']) && $GLOBALS['_SESSION']['integracao_is_parceiro']=="OK" && isset($GLOBALS['_SESSION']['integracao_origem_id']) && isset($GLOBALS['_SESSION']['integracao_order_id'])) {
		return $GLOBALS['_SESSION']['integracao_order_id'];
	}
	return "";
}

// ================================================
function get_POST_as_string($b_only_required) {
	$partner_allowed_params = array(
		'store_id' => array('required' => 1),
		'return_url' => array('required' => 1),
		'notify_url' => array('required' => 1),
		'currency_code' => array('required' => 1),
		'order_id' => array('required' => 1),
		'order_description' => array('required' => 1),
		'product_id' => array('required' => 1),
		'amount' => array('required' => 1),

		'client_email' => array('required' => 1),
		'language' => array('required' => 1),

		'client_id' => array('required' => 0),
		'client_name' => array('required' => 0),
		'client_zip_code' => array('required' => 0),
		'client_street' => array('required' => 0),
		'client_suburb' => array('required' => 0),
		'client_number' => array('required' => 0),
		'client_city' => array('required' => 0),
		'client_state' => array('required' => 0),
		'client_country' => array('required' => 0),
		'client_telephone' => array('required' => 0),
		);

	$sout = "";
	foreach($partner_allowed_params as $key => $val) {
		if($val['required']=="1" || ($val['required']=="0" && !$b_only_required)) {
			if($sout!="") $sout .= "&";
			$sout .= "$key=".$GLOBALS['_POST'][$key]."";
		}
	}
	return $sout;
}

// ================================================
function grava_log_integracao($mensagem){
	$ARQUIVO_LOG_HTTP_REFERER = $GLOBALS['raiz_do_projeto'] . "log/log_integracao.txt";

	//Arquivo
	$file = $ARQUIVO_LOG_HTTP_REFERER;

	//Mensagem
//	$mensagem = date('Y-m-d H:i:s') . " " . (($GLOBALS['_SERVER']['HTTP_REFERER'])?$GLOBALS['_SERVER']['HTTP_REFERER']:'Empty') . " - " . $GLOBALS['_SERVER']["REMOTE_ADDR"] . "\n";
//echo 	$mensagem;
	$mensagem = $GLOBALS['_SERVER']['SCRIPT_NAME']."\n".$mensagem;
	//Grava mensagem no arquivo
	if ($handle = fopen($file, 'a+')) {
		fwrite($handle, $mensagem);
		fclose($handle);
	}
}

// ================================================
function grava_log_integracao_tmp($mensagem){
	$ARQUIVO_LOG_HTTP_REFERER = $GLOBALS['raiz_do_projeto'] . "log/log_integracao_tmp.txt";

	//Arquivo
	$file = $ARQUIVO_LOG_HTTP_REFERER;

	//Mensagem
//	$mensagem = date('Y-m-d H:i:s') . " " . (($GLOBALS['_SERVER']['HTTP_REFERER'])?$GLOBALS['_SERVER']['HTTP_REFERER']:'Empty') . " - " . $GLOBALS['_SERVER']["REMOTE_ADDR"] . "\n";
//echo 	$mensagem;
	//Grava mensagem no arquivo
	if ($handle = fopen($file, 'a+')) {
		fwrite($handle, $mensagem);
		fclose($handle);
	}
}

// ================================================
function getPartner_amount_free_By_ID($id) {
	global $partner_list;
	$amount_free = "";
	foreach($partner_list as $key => $val) {
		if($val['partner_id']==$id) {
			$amount_free = $val['amount_free'];
			break;
		}
	}
	return $amount_free;
}

// ================================================
function grava_log_integracao_ip_notify($mensagem){
	$ARQUIVO_LOG_HTTP_REFERER = $GLOBALS['raiz_do_projeto'] . "log/log_integracao_ip_notify.txt";

	//Arquivo
	$file = $ARQUIVO_LOG_HTTP_REFERER;

	//Mensagem
//	$mensagem = date('Y-m-d H:i:s') . " " . (($GLOBALS['_SERVER']['HTTP_REFERER'])?$GLOBALS['_SERVER']['HTTP_REFERER']:'Empty') . " - " . $GLOBALS['_SERVER']["REMOTE_ADDR"] . "\n";
//echo 	$mensagem;
	//Grava mensagem no arquivo
	if ($handle = fopen($file, 'a+')) {
		fwrite($handle, $mensagem);
		fclose($handle);
	}
}

// ================================================
function getPartner_do_renotify_automatico_By_ID($id) {
	global $partner_list;
	$do_renotify_automatico = 0;
	foreach($partner_list as $key => $val) {
		if($val['partner_id']==$id) {
			$do_renotify_automatico = $val['partner_do_renotify_automatico'];
			break;
		}
	}
	return $do_renotify_automatico;
}

// ================================================
function getPartner_Names_SQL() {
	global $partner_list;
	$sql_case = "CASE ";
	foreach($partner_list as $key => $val) {
//echo "'$key' => '$val'<br>";
		$sql_case .= "WHEN ip_store_id = '".$val['partner_id']."' THEN '".$val['partner_name']."' ";
	}
	$sql_case .= "END as opr_nome ";
	return $sql_case;
}

// ================================================
function getPartner_Codes_SQL() {
	global $partner_list;
	$sql_case = "CASE ";
	foreach($partner_list as $key => $val) {
//echo "'$key' => '$val'<br>";
		$sql_case .= "WHEN ip_store_id = '".$val['partner_id']."' THEN '".$val['partner_opr_codigo']."' ";
	}
	$sql_case .= "END as partner_opr_codigo ";
	return $sql_case;
}

// ================================================
function getPartner_notify_url_By_ID($id) {
	global $partner_list;
	$notify_url = "";
	foreach($partner_list as $key => $val) {
		if($val['partner_id']==$id) {
			$notify_url = $val['notify_url'];
			break;
		}
	}
	return $notify_url;
}

// ================================================
function getPartner_return_url_By_ID($id) {
	global $partner_list;
	$return_url = "";
	foreach($partner_list as $key => $val) {
		if($val['partner_id']==$id) {
			$return_url = $val['return_url'];
			break;
		}
	}
	return $return_url;
}

// ================================================
function getPartner_sonda_url_By_ID($id) {
	global $partner_list;
	$sonda_url = "";
	foreach($partner_list as $key => $val) {
		if($val['partner_id']==$id) {
			$sonda_url = $val['sonda_url'];
			break;
		}
	}
	return $sonda_url;
}

// ================================================
function getPartner_name_By_ID($id) {
	global $partner_list;
	$partner_name = "";
	foreach($partner_list as $key => $val) {
		if($val['partner_id']==$id) {
			$partner_name = $val['partner_name'];
			break;
		}
	}
	return $partner_name;
}

// ================================================
function b_Partner_is_Ativo($id) {
	global $partner_list;
	$b_ativo = false;

	foreach($partner_list as $key => $val) {
		if($val['partner_id']==$id) {
//			$partner_name = $val['partner_name'];
			$b_ativo = ($val['partner_opr_codigo']!=78 && $val['partner_produto_id']!=$GLOBALS['INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID']);
			break;
		}
	}
	return $b_ativo;
}

// ================================================
function get_list_Partner_IDs($b_ativo, $b_text) {
	global $partner_list;
	$list_Partner_IDs = "";
	if($b_ativo) {
		foreach($partner_list as $key => $val) {
			if(b_Partner_is_Ativo($val['partner_id'])) {
				$list_Partner_IDs .= (($list_Partner_IDs)?",":"");
				$list_Partner_IDs .= (($b_text)?"'":"").$val['partner_id'].(($b_text)?"'":"");
			}
		}
	} else {
		foreach($partner_list as $key => $val) {
			if(!b_Partner_is_Ativo($val['partner_id'])) {
				$list_Partner_IDs .= (($list_Partner_IDs)?",":"");
				$list_Partner_IDs .= (($b_text)?"'":"").$val['partner_id'].(($b_text)?"'":"");
			}
		}
	}
	return $list_Partner_IDs;
}

// ================================================
function getPartner_Store_id_By_opr_codigo($opr_codigo) {
	global $partner_list;
	$partner_name = "";
	foreach($partner_list as $key => $val) {
		if($val['partner_opr_codigo']==$opr_codigo) {
			$partner_name = $val['partner_id'];
			break;
		}
	}
	return $partner_name;
}

function getPartner_Store_id_By_opr_codigo_ALL_CODES($opr_codigo) {
	global $partner_list;
	$partner_name = "";
	foreach($partner_list as $key => $val) {
		if($val['partner_opr_codigo']==$opr_codigo && $val['partner_opr_codigo']!=78 && $val['partner_produto_id']!=$GLOBALS['INTEGRACAO_STORE_TREINAMENTO_PRODUTO_ID']) {
                    if(empty($partner_name)) {
                        $partner_name = $val['partner_id'];
                    }
                    else {
                        $partner_name .= "','".$val['partner_id'];
                    }
		}
	}
	return $partner_name;
}

// ================================================
function getPartner_produto_id_By_ID($id) {
	global $partner_list;
	$produto_id = "";
	foreach($partner_list as $key => $val) {
		if($val['partner_id']==$id) {
			$produto_id = $val['partner_produto_id'];
			break;
		}
	}
	return $produto_id;
}

// ================================================
function getPartner_lista_formas_pagto_bloqueadas_By_ID($id) {
	global $partner_list;
	$lista_formas_pagto_bloqueadas = "";
	foreach($partner_list as $key => $val) {
		if($val['partner_id']==$id) {
			$lista_formas_pagto_bloqueadas = $val['lista_formas_pagto_bloqueadas'];
			break;
		}
	}
	return $lista_formas_pagto_bloqueadas;
}

// ================================================
function get_img($store_id) {
	
	global $partner_list;
	foreach($partner_list as $key => $val) {
		if($val['partner_id']==$store_id) {
			return ["logo" => $val['partner_img_logo'], "name" => $val['partner_name']];
		}
	}
}

function b_is_forma_pagto_bloqueada($store_id, $iforma) {
	$lista_bloqueio = getPartner_lista_formas_pagto_bloqueadas_By_ID($store_id);
	$a_bloqueios = explode(",", str_replace(" ", "", $lista_bloqueio));
	$b_bloqueado = in_array($iforma, $a_bloqueios);
	return $b_bloqueado;
}

// 'partner_img_logo'

// ================================================
function getPartner_Integracao_Transparente_By_ID($id) {
	global $partner_list;
	$integracao_transparente = 0;
	foreach($partner_list as $key => $val) {
		if($val['partner_id']==$id) {
			$integracao_transparente = $val['integracao_transparente'];
			break;
		}
	}
	return $integracao_transparente;
}

// ================================================
function b_use_forma_pagto_direta($id) {
	global $partner_list;
	$b_forma_pagto_direta = false;
	foreach($partner_list as $key => $val) {
		if($val['partner_id']==$id) {
			$b_forma_pagto_direta = ($val['forma_pagto_direta']!='');
			break;
		}
	}
	return $b_forma_pagto_direta;
}

// ================================================
function b_is_forma_pagto_direta_epp_cash($id) {
	global $partner_list;
	$b_forma_pagto_direta_epp_cash = false;
	foreach($partner_list as $key => $val) {
		if($val['partner_id']==$id) {
			$b_forma_pagto_direta_epp_cash = ($val['forma_pagto_direta']=='E');
			break;
		}
	}
	return $b_forma_pagto_direta_epp_cash;
}

// ================================================
function b_is_forma_pagto_direta_gocash($id) {
	global $partner_list;
	$b_is_forma_pagto_direta_gocash = false;
	foreach($partner_list as $key => $val) {
		if($val['partner_id']==$id) {
			$b_is_forma_pagto_direta_epp_cash = b_is_forma_pagto_direta_epp_cash($id);
			if($b_is_forma_pagto_direta_epp_cash) {
				$b_is_forma_pagto_direta_gocash = ($val['forma_pagto_direta_gocash']==1);
			}
			break;
		}
	}
	return $b_is_forma_pagto_direta_gocash;
}

// ================================================
function b_use_inframe($id) {
	global $partner_list;
	$b_use_inframe = false;
	$b_is_forma_pagto_direta = b_use_forma_pagto_direta($id);
	if($b_is_forma_pagto_direta) {
		foreach($partner_list as $key => $val) {
			if($val['partner_id']==$id) {
				$b_use_inframe = ($val['forma_pagto_direta_in_frame']==1);
				break;
			}
		}
	}
	return $b_use_inframe;
}

// ================================================
function getPartner_forma_pagto_direta_By_ID($id) {
	global $partner_list;
	$forma_pagto_direta = "";
	foreach($partner_list as $key => $val) {
		if($val['partner_id']==$id) {
			$forma_pagto_direta = $val['forma_pagto_direta'];
			break;
		}
	}
	return $forma_pagto_direta;
}

// ================================================
function getPartner_partner_img_logo_By_ID($id, $prod_id = null) {
	global $partner_list;
	$partner_img_logo = "";
	foreach($partner_list as $key => $val) {
		if($val['partner_id']==$id) {
			$partner_img_logo = $val['partner_img_logo'];
			// Algumas operadoras têm um logo para cada produto -> mostrar logo de produto em lugar de logo da operadora
			if($prod_id && isset($val['partner_img_prods_logo'])) {
				$partner_img_prods_logo_array = $val['partner_img_prods_logo'];
				if(isset($partner_img_prods_logo_array[$prod_id])) {
					$partner_img_logo = $partner_img_prods_logo_array[$prod_id];
				}
			}
			break;
		}
	}
	return $partner_img_logo;
}
/*
// ================================================
// Quando $param_name = 'notify_url' => $ogp_id indica o notify_url que deve ser usado, se houver mais de um (caso Cyberstep)
function getPartner_param_By_ID($param_name, $id, $ogp_id = 0) {
	global $partner_list;
	$param = "";
	foreach($partner_list as $key => $val) {
		if($val['partner_id']==$id) {
			if($param_name == 'notify_url') {
				grava_log_integracao("TESTING multiple notify_url (Cyberstep) 1 : (param_name: '$param_name', ogp_id: $ogp_id)");
			}
			if($ogp_id>0) {
				grava_log_integracao("TESTING multiple notify_url (Cyberstep) 2 : (param_name: '$param_name', ogp_id: $ogp_id)");
			}
			$param = $val[$param_name];
			break;
		}
	}
	return $param;
}

*/
// ================================================
function getPartner_param_By_ID($param_name, $id) {
	global $partner_list;
	$param = "";
	foreach($partner_list as $key => $val) {
		if($val['partner_id']==$id) {
			$param = $val[$param_name];
			break;
		}
	}
	return $param;
}

// ================================================
function getPartner_payments_list($id) {

	$url_notify = getPartner_notify_url_By_ID($id);

	// recupera os dados da compra efetuada.
	$sql = "SELECT * FROM tb_integracao_pedido ip
				left outer join tb_pag_compras pc on ip.ip_vg_id = pc.idvenda
				inner join tb_venda_games vg on vg.vg_id = pc.idvenda
			WHERE ( not (vg.vg_integracao_parceiro_origem_id is null)) ";

	if($id && $id>0) {
		$sql .= "		and vg.vg_integracao_parceiro_origem_id = '$id'	";
	}
	$sql .= "order by pc.datainicio desc";
//echo "sql: $sql<br>";
	$retCompra = SQLexecuteQuery($sql);
	if(!$retCompra) {
		echo "Erro ao recuperar transação de integração (A) ('$numOrder').<br>\n";
		die("Stop");
	}

	// Recupera dados do pagamento
	if ($retCompra) {
		echo "<table cellpadding='2' cellspacing='2' border='1' bordercolor='#cccccc' style='border-collapse:collapse;'>\n";
		echo "<tr style='text-align:center;font-weight:bold'> <td>ID</td> <td>data</td> <td>numcompra</td> <td>total (R\$)</td> <td>idcliente</td> <td>cliente_nome</td>";
		echo "<td>store_id</td ><td>order_id</td> <td>amount</td> <td>curr</td> <td>vg_id</td> <td>client_id</td> <td>client_email</td> <td>status</td> <td>confirmed</td> ";

/*
		echo " <td>status_confirmed</td> <td>data_confirmed</td>";
		echo " <td> NOTIFICADO </td>";
*/
		echo "</tr>\n";

		while($pgCompra = pg_fetch_array($retCompra)) {
			echo "<tr> <td>".$pgCompra['ip_id']."</td> <td><nobr>".$pgCompra['datainicio']."</nobr></td> <td>".$pgCompra['numcompra']."</td>  <td align='right'>".number_format(($pgCompra['total']/100), 2, ',', '.')."</td> <td align='center'>" . $pgCompra['idcliente']."</td> <td>".$pgCompra['cliente_nome']."</td>\n";

			echo "<td align='center'><nobr>".getPartner_name_By_ID($pgCompra['ip_store_id'])." (".$pgCompra['ip_store_id'].")</nobr></td ><td>".$pgCompra['ip_order_id']."</td> <td>".$pgCompra['ip_amount']."</td> <td align='center'>".$pgCompra['ip_currency_code']."</td> <td>".$pgCompra['ip_vg_id']."</td> <td>".$pgCompra['ip_client_id']."</td> <td>".$pgCompra['ip_client_email']."</td> <td title='status: ".$pgCompra['status']."' align='center'><font color='".getStatusColor($pgCompra['status'])."'>".getStatusDescription($pgCompra['status'])."</font></td> <td align='center'><font color='".(($pgCompra['ip_status_confirmed']==1)?"#0000FF":"#CCCCFF")."'>".(($pgCompra['ip_status_confirmed']==1)?"Sim":"Não")."</font></td>\n";

			echo "</tr>\n";

		}

		echo "</table>\n";
	}
}

// ================================================
function getStatusDescription($status) {
	switch($status) {
		case '1': return "Incompleto"; break;
		case '3': return "Completo"; break;
		case '-1': return "Cancelado"; break;
	}
	return "Status Desconhecido ($status)";
}

// ================================================
function getStatusColor($status) {
	switch($status) {
		case '1':	//	"Incompleto"
			return "darkyellow";
			break;
		case '3':	//	"Completo"
			return "darkgreen";
			break;
		case '-1':	//	"Cancelado"
			return "darkred";
			break;
	}
	return "red";	// 	"Status Desconhecido ($status)";
}

// ================================================
function getIntegracaoCURL($url, $post_parameters) {

	$buffer = "";
//echo "SONDA Parameters: ".$url.", ".$post_parameters."<br>";
	// http://blog.unitedheroes.net/curl/
	$curl_handle = curl_init();
	curl_setopt($curl_handle, CURLOPT_URL,$url);

	// Some sites may protect themselves from remote logins by checking which site you came from.
	// http://php.net/manual/en/function.curl-setopt.php
	$ref_url = "http://www.e-prepag.com.br";
	curl_setopt($curl_handle, CURLOPT_REFERER, $ref_url);

	// http://www.weberdev.com/get_example-4136.html
	// http://www.php.net/manual/en/function.curl-setopt.php
	curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, 0);	// true - verifica certificado
	curl_setopt($curl_handle, CURLOPT_SSL_VERIFYHOST, 0);	// 1 - então, também verifica nome no certificado

	curl_setopt($curl_handle, CURLOPT_HEADER, 1);
	curl_setopt($curl_handle, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)");

	curl_setopt($curl_handle, CURLOPT_POST, 1);
	curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $post_parameters);

	// The number of seconds to wait while trying to connect. Use 0 to wait indefinitely.
	curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 10);
	// The maximum number of seconds to allow cURL functions to execute.
	curl_setopt($curl_handle, CURLOPT_TIMEOUT, 10);
	curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);

	$buffer = curl_exec($curl_handle);

/*	// Em caso de erro libera aqui
	$info = curl_getinfo($curl_handle);

	if ($output === false || $info['http_code'] != 200) {
	  $output = "No cURL data returned for URL [". $info['http_code']. "]";
	  if (curl_error($curl_handle)) {
		$output .= "\n". curl_error($curl_handle);
	  }
	  echo "CRL Error: ".$output."<br>Buffer: ".$buffer."\n";
//echo "<pre>";
//print_r($info);
//echo "</pre>";
	} else {
	  // 'OK' status; format $output data if necessary here:
	  echo "CRL OK<br>\n";
	}
	// Até aqui
*/
	curl_close($curl_handle);

	return $buffer;
}

/*
	parceiro_params =>
		[store_id] => 10
		[currency_code] => BRL
		[order_id] => 8450740
		[order_description] => Mensalidade - jogos
		[amount] => 2000
		[client_email] => REINALDOPS@HOTMAIL.COM
		[client_id] => 32233223
		[client_name] => Joe Smith
		[client_zip_code] => 05418000
		[client_street] => R. Dep. Lacerda Franco
		[client_suburb] => Pinheiros
		[client_number] => 300
		[client_city] => São Paulo
		[client_state] => SP
		[client_country] => Brasil
		[client_telephone] => 32233223
		[language] => PT

*/
function setNotifyPartnerAboutTransaction($parceiro_params){

	// setNotifyPartnerAboutTransaction está sendo usado para completar o partner_notify.php, uma pagina de testes
	// o correto é atualizar o notify apenas em epp_notify.php
	return true;

	$store_id		= $parceiro_params['store_id'];
	$order_id		= $parceiro_params['order_id'];
	$transaction_id	= $parceiro_params['transaction_id'];
	$amount			= $parceiro_params['amount'];
	$client_email	= $parceiro_params['client_email'];
	$client_id		= $parceiro_params['client_id'];
	$currency_code	= $parceiro_params['currency_code'];

	$msg = "";
/*
	// Valida dados de entrada
	if(!$msg) { if(!is_Partner_valido($store_id)) { $msg = "Parceiro inválido ($store_id)"; } }
	if(!$msg) { if(!is_numeric($order_id)) { $msg = "order_id inválido ($order_id)"; } }
	if(!$msg) { if(!is_numeric($amount)) { $msg = "amount inválido ($amount)"; } }
	if(!$msg) { if(!is_numeric($client_id)) { $msg = "client_id inválido ($client_id)"; } }
	if(!$msg) { if(!is_numeric($transaction_id)) { $msg = "transaction_id inválido ($transaction_id)"; } }
	if(!$msg) { if(!($client_email)) { $msg = "client_email inválido ($client_email)"; } }
	if(!$msg) { if(! ($currency_code=='BRL') ) { $msg = "currency_code inválido ($currency_code)"; } }
*/
	$bret = is_Integracao_params_valida($parceiro_params);

//$msg = "Teste";

	// Se teve algum erro -> grava log
	if(!$bret) {

		$s_msg = "setNotifyPartnerAboutTransaction (integracao_b_origem: $integracao_b_origem): ".date("Y-m-d H:i:s")."\n  IP: ".$GLOBALS['_SERVER']['REMOTE_ADDR']."\n  URL: ".$GLOBALS['_SERVER']['HTTP_REFERER']."\n";
		$GLOBALS['_SERVER']['integration_debug_info'] .= $s_msg;
		grava_log_integracao($s_msg);

		// Debug
		send_debug_info_by_email("E-Prepag - Testing integration (save parameters)", $s_msg, $INTEGRACAO_STORE_ID);

		echo "<font color='red'>Sem notificação</font>";
	} else {
		// recupera os dados da integracao.
		$sql = "SELECT * FROM tb_integracao_pedido ip
				WHERE 1=1
					and ip_store_id = '".$store_id."'
					and ip_order_id = '".$order_id."'
					and ip_amount = '".$amount."'
					and ip_client_email = '".$client_email."'
					and ip_currency_code = '".$currency_code."'
				order by ip_id desc";
//					left outer join tb_pag_compras pc on ip.ip_vg_id = pc.idvenda
//				order by pc.datainicio desc";
//					"and ip_id = '".$transaction_id."'"
//					"and "pc.iforma='Z' "
//					"and ip_client_id = '".$client_id."' "
//					and ip_transaction_id = '".$transaction_id."'
//echo "sql: $sql<br>";
grava_log_integracao("Integração em setNotifyPartnerAboutTransaction: ".date("Y-m-d H:i:s")."\n  $sql\n");

		$rs = SQLexecuteQuery($sql);
		if(!$rs) {
			echo "Erro ao recuperar transação de integração (B) ('$order_id').<br>\n";
			die("Stop");
		} else {
			$rs_row = pg_fetch_array($rs);
			$ip_id					= $rs_row['ip_id'];
			$ip_status_confirmed	= $rs_row['ip_status_confirmed'];
			$ip_data_confirmed		= $rs_row['ip_data_confirmed'];
			$ip_vg_id				= $rs_row['ip_vg_id'];

//echo "ID: $ip_id =&gt; ip_status_confirmed: '$ip_status_confirmed' (ip_data_confirmed: '$ip_data_confirmed')<br>";
			if($ip_status_confirmed==1) {
				// Pedido já foi confirmado -> Do nothing
/*
				echo "<font color='blue'>Pedido já foi notificado em '$ip_data_confirmed'</font><br>(store_id = '".$store_id."'<br>
					order_id = '".$order_id."'<br>
					amount = '".$amount."'<br>
					client_email = '".$client_email."'<br>
					currency_code = '".$currency_code."')<br>";
*/
grava_log_integracao("Integração em setNotifyPartnerAboutTransaction (Do nothing): ".date("Y-m-d H:i:s")."\n");
				return true;
			} else {
				// recupera os dados da compra efetuada.
				$sql = "SELECT * FROM tb_venda_games vg
						WHERE 1=1
							and vg_integracao_parceiro_origem_id = '".$store_id."'
							and vg_id = '".$ip_vg_id."'
						order by vg_datas_inclusao desc";
//echo "sql: $sql<br>";
grava_log_integracao("Integração em setNotifyPartnerAboutTransaction: ".date("Y-m-d H:i:s")."\n  $sql\n");

				$rs_vg = SQLexecuteQuery($sql);
				if(!$rs) {
					$msg = "Erro ao recuperar venda de transação de integração (ABC) (store_id: '$store_id', ip_vg_id: '$ip_vg_id').<br>\n";
grava_log_integracao("Integração em setNotifyPartnerAboutTransaction: ".date("Y-m-d H:i:s")."\n   $msg\n   $sql\n");
echo $msg;
					die("Stop");
				} else {
					$rs_vg_row = pg_fetch_array($rs_vg);
					$vg_ultimo_status = $rs_vg_row['vg_ultimo_status'];

					if($vg_ultimo_status=='5') {
						// Marca pedido como notificado - ip_status_confirmed	ip_data_confirmed
						$sql_update = "update tb_integracao_pedido set ip_status_confirmed = 1, ip_data_confirmed = CURRENT_TIMESTAMP
							WHERE ip_id = ".$ip_id."
								and ip_store_id = '".$store_id."'
								and ip_order_id = '".$order_id."'
								and ip_amount = '".$amount."'
								and ip_client_email = '".$client_email."'
								and ip_currency_code = '".$currency_code."'";
//echo "$sql_update<br>";
grava_log_integracao("Integração em setNotifyPartnerAboutTransaction (Update): ".date("Y-m-d H:i:s")."\n  $sql_update\n");

						$rs = SQLexecuteQuery($sql_update);
//				echo "<font color='blue'>Pedido notificado ID: $order_id de Parceiro $store_id com sucesso.</font><br>";

						// Notificação aceita e processada
					}

					return true;
				}
			}
		}
	}
	// Sem Notificação válida
	return false;

}

function get_param_value($param_value) {
	return ((!$param_value)?"Empty":$param_value);
}
function send_debug_email($to, $cc, $bcc, $subjectEmail, $msgEmail) {
	enviaEmail($to, $cc, $bcc, $subjectEmail, $msgEmail);
}

function get_server_DNS_by_URL($sname0) {
//	echo "sname0: $sname0\n";
//	echo "<hr>$sname0<br>";
	$i_start = strpos($sname0, "//")+2;
	@$i_stop = strpos($sname0, "/", $i_start)-1;
//	echo "[$i_start - $i_stop] = ".($i_stop-$i_start+1)."<br>";
	$sname = substr($sname0, $i_start, ($i_stop-$i_start+1));
//	echo "$sname<hr>";
	return $sname;

}

/*
// Test in a block
//		b_is_address_valid("62.146.190.10", "62.146.190.0", "62.146.191.255")
// Test only one IP address
//		b_is_address_valid("62.146.190.10", "62.146.190.0")
function b_is_address_valid($ip, $ip_defined_interval_start, $ip_defined_interval_end = null) {
	$b_ret = false;
	if(is_null($ip_defined_interval_end)) {
		if(trim($ip)==trim($ip_defined_interval_start)) {
			$b_ret = true;
		}
	} else {
		$ip_lng  = ip2long($ip);
		$ip0_lng = ip2long($ip_defined_interval_start);
		$ipN_lng = ip2long($ip_defined_interval_end);
		if(($ip_lng>=$ip0_lng) && ($ip_lng<=$ipN_lng)) {
			$b_ret = true;
		}
	}
	return $b_ret;
}
*/
/*
// Testa se $ip_client está nas definições de IP do parceiro $partner_id (seja IP fixo, bloco de IPs ou lista CSV de IPs)
function b_is_address_valid($partner_id, $ip_client) {

	$partner_ip_intervals = getPartner_param_By_ID('partner_ip_intervals', $partner_id);
	$partner_ip_block_start = getPartner_param_By_ID('partner_ip_block_start', $partner_id);
	$partner_ip_block_end = getPartner_param_By_ID('partner_ip_block_end', $partner_id);
	$partner_ip_defined_list = str_replace(" ", "", getPartner_param_By_ID('partner_ip_defined_list', $partner_id));
	$ip_parceiro = getPartner_param_By_ID('partner_ip', $partner_id);

	$b_ret = false;
	if($partner_ip_block_start && $partner_ip_block_end) {
		// Testamos IP block
//		echo "('$partner_id' -> '($ip_client' IP block) <br>\n";
		$ip_lng  = ip2long($ip_client);
		$ip0_lng = ip2long($partner_ip_block_start);
		$ipN_lng = ip2long($partner_ip_block_end);
//echo "   -  $ip_lng    [$ip0_lng, $ipN_lng]<br>\n";
		if(($ip_lng>=$ip0_lng) && ($ip_lng<=$ipN_lng)) {
			$b_ret = true;
		}
	} elseif($partner_ip_defined_list) {
		// Testamos lista CSV de IPs
//		echo "('$partner_id' IP list) <br>\n";
		$aIPs = explode(",", $partner_ip_defined_list);
		foreach($aIPs as $key => $val) {
			if(trim($val)==trim($ip_client)) {
				$b_ret = true;
			}
		}
	} else {
		// Testamos o IP diretamente
//		echo "('$partner_id' IP fixed) <br>\n";
		if(trim($ip_parceiro)==trim($ip_client)) {
			$b_ret = true;
		}
	}

	return $b_ret;
}

*/
// Testa se $ip_client está nas definições de IP do parceiro $partner_id (seja IP fixo, bloco de IPs ou lista CSV de IPs)
function b_is_address_valid($partner_id, $ip_client) {

	$partner_ip_intervals = getPartner_param_By_ID('partner_ip_intervals', $partner_id);
//		echo "$partner_id: <pre>".print_r($partner_ip_intervals, true)."</pre>\n";

	$partner_ip_block_start = getPartner_param_By_ID('partner_ip_block_start', $partner_id);
	$partner_ip_block_end = getPartner_param_By_ID('partner_ip_block_end', $partner_id);
	$partner_ip_defined_list = str_replace(" ", "", getPartner_param_By_ID('partner_ip_defined_list', $partner_id));
	$ip_parceiro = getPartner_param_By_ID('partner_ip', $partner_id);

	$b_ret = false;
	// Testa lista de blocos de IPs
	if(isset($partner_ip_intervals) && is_array($partner_ip_intervals)  ) {
//echo "Intervals: <br>";
		$ip_lng  = ip2long($ip_client);
		foreach($partner_ip_intervals as $key1 => $val1) {
//			echo "<pre>".print_r($val1, true)."</pre>\n";
			$ip0_lng = ip2long($val1['partner_ip_block_start']);
			$ipN_lng = ip2long($val1['partner_ip_block_end']);
	//echo "   -  $ip_lng    [$ip0_lng, $ipN_lng]<br>\n";
			if(($ip_lng>=$ip0_lng) && ($ip_lng<=$ipN_lng)) {
				$b_ret = true;
				break;
			}
		}
	}

	// Testa bloco de IP
	elseif($partner_ip_block_start && $partner_ip_block_end) {
//echo "Bloco: <br>";
//		echo "('$partner_id' -> '($ip_client' IP block) <br>\n";
		$ip_lng  = ip2long($ip_client);
		$ip0_lng = ip2long($partner_ip_block_start);
		$ipN_lng = ip2long($partner_ip_block_end);
//echo "   -  $ip_lng    [$ip0_lng, $ipN_lng]<br>\n";
		if(($ip_lng>=$ip0_lng) && ($ip_lng<=$ipN_lng)) {
			$b_ret = true;
		}
	}

	// Testa lista CSV de IPs
	elseif($partner_ip_defined_list) {
//echo "Defined List: <br>";
//		echo "('$partner_id' IP list) <br>\n";
		$aIPs = explode(",", $partner_ip_defined_list);
		foreach($aIPs as $key => $val) {
			if(trim($val)==trim($ip_client)) {
				$b_ret = true;
			}
		}
	}
	// Testa o IP diretamente
	else {
//		echo "('$partner_id' IP fixed) <br>\n";
		if(trim($ip_parceiro)==trim($ip_client)) {
			$b_ret = true;
		}
	}

	return $b_ret;
}


// sanitize input data in array $params[]
// returns the sanitized version of $params[]
function sanitize_input_data($params, &$err_cod){
//gravaLog_WS_processing("Em sanitize_input_data (A) (".date("Y-m-d H:i:s")."): err_cod: $err_cod, ".print_r($params,true)."\n");

	$params_out = array();
	$err_cod = "00";
	foreach($params as $key => $val) {
//			echo "<font color='blue'>Campo (key: '$key')</font><br>";
//gravaLog_WS_processing("Em sanitize_input_data (A) (".date("Y-m-d H:i:s")."): Campo (key: '$key')\n");
		switch($key) {

			//String
			case 'identificacao':
			case 'terminal':
			case 'code_cancela':
			case 'code_confirma':
//gravaLog_WS_processing("Em sanitize_input_data (String) (".date("Y-m-d H:i:s")."): (val: '$val')\n");
				$val_mod = sanitize_general($val);
//gravaLog_WS_processing("Em sanitize_input_data (String) (".date("Y-m-d H:i:s")."): (val_mod: '$val_mod')\n");
				if(filter_var($val_mod, FILTER_SANITIZE_STRING) === false) {
					$val_sanitized = $val_mod;
					$err_cod = "VS";
				} else {
					$val_sanitized = $val_mod;
					// 	continua com $err_cod = '00'
				}
//gravaLog_WS_processing("Em sanitize_input_data (String) (".date("Y-m-d H:i:s")."): (val_sanitized: '$val_sanitized', err_cod: $err_cod)\n");
				break;

			//Int
			case 'transacao_id':
			case 'operadora_id':
			case 'transacao_id_cancela':
			case 'transacao_id_confirma':

			case 'produtos_n':

			case 'produto_id':
			case 'opr_codigo':
			case 'pin_qtde':
			case 'pin_valor':

				// Simulate um error in input data
//					$val .= "S";
//gravaLog_WS_processing("Em sanitize_input_data (Int) (".date("Y-m-d H:i:s")."): (key: $key, val: '$val')\n");
				$val_mod = sanitize_general($val);
//gravaLog_WS_processing("Em sanitize_input_data (Int) (".date("Y-m-d H:i:s")."): (key: $key, val_mod: '$val_mod')\n");
				if(filter_var($val_mod, FILTER_VALIDATE_INT) === false) {
					$val_sanitized = $val_mod;
					$err_cod = "VI";
				} else {
					$val_sanitized = $val_mod;
					// 	continua com $err_cod = '00'
				}
//gravaLog_WS_processing("Em sanitize_input_data (Int) (".date("Y-m-d H:i:s")."): (val_sanitized: '$val_sanitized', err_cod: $err_cod)\n");
				break;

			//Data
			case 'data_trans':
			case 'data_trans_cancela':
			case 'data_trans_confirma':

//gravaLog_WS_processing("Em sanitize_input_data (Data) (".date("Y-m-d H:i:s")."): (val: '$val')\n");
				$val_mod = sanitize_general($val);
//gravaLog_WS_processing("Em sanitize_input_data (Data) (".date("Y-m-d H:i:s")."): (val_mod: '$val_mod')\n");
				$val_sanitized = sanitize_date($val_mod, $err_cod);

//gravaLog_WS_processing("Em sanitize_input_data (Data) (".date("Y-m-d H:i:s")."): (val_sanitized: '$val_sanitized', err_cod: $err_cod)\n");
				break;

			//Array
			case 'produtos':

//gravaLog_WS_processing("Em sanitize_input_data (Array) (".date("Y-m-d H:i:s")."): (val: '$val')\n");
				$val_sanitized = array();
				foreach($val as $key_p => $val_p) {
					$val_sanitized[$key_p] = sanitize_input_data($val_p, $err_cod);
				}
//gravaLog_WS_processing("Em sanitize_input_data (Array) (".date("Y-m-d H:i:s")."): (val_sanitized: '".print_r($val_sanitized,true)."', err_cod: $err_cod)\n");
				break;

			default:
				if(is_array($val)) {
					$val_sanitized = sanitize_input_data($val, $err_cod);
				} else {
//						echo "<font color='red'>&nbsp;&nbsp;Campo desconhecido (key: '$key', val: <pre>".print_r($val,true)."</pre>)</font><br>";
//gravaLog_WS_processing("Em sanitize_input_data (DEFAULT) (".date("Y-m-d H:i:s")."): (val: '".print_r($val,true)."')\n");
				}
				break;

		}
//gravaLog_WS_processing("Em sanitize_input_data (B) (".date("Y-m-d H:i:s")."): Campo processado (key: '$key', err_cod: '$err_cod', val: ".print_r($val_sanitized,true)."\n");

		$params_out[$key] = $val_sanitized;
	}
	return $params_out;
}

function sanitize_date($dateval, &$err_cod){
	if(strlen($dateval)==19) {
		// '    4  7  0  3  6  '
		// '0123456789012345678'
		// '2010-11-12 18:01:51'
		if( (substr($dateval,4,1)=="-") && (substr($dateval,7,1)=="-") && (substr($dateval,10,1)==" ") && (substr($dateval,13,1)==":") && (substr($dateval,16,1)==":") ) {

			if( (is_numeric(substr($dateval,0,4))) && (is_numeric(substr($dateval,5,2))) &&
				(is_numeric(substr($dateval,8,2))) &&  (is_numeric(substr($dateval,11,2))) &&
				(is_numeric(substr($dateval,14,2))) && (is_numeric(substr($dateval,17,2))) ) {
				$outval = $dateval;
				// 	continua com $err_cod = '00'
			} else {
				$outval = "";	// "DATEERRORValuesInt";
				$err_cod = "VD";
			}
		} else {
			$outval = "";	//"DATEERRORPunctuation";
			$err_cod = "VP";
		}
	} else {
		$outval = "";	//"DATEERROR";
		$err_cod = "VE";
	}
	return $outval;
}

function sanitize_general($strval){
	$outval = $strval;
	$outval = str_replace("DROP ", "d_r_o_p_", strtoupper($outval));
	$outval = str_replace("INSERT ", "i_n_s_e_r_t_", strtoupper($outval));
	$outval = str_replace("DELETE ", "d_e_l_e_t_e_", strtoupper($outval));
	$outval = str_replace("UPDATE ", "u_p_d_a_t_e_", strtoupper($outval));
	$outval = str_replace("CREATE ", "c_r_e_a_t_e_", strtoupper($outval));
	$outval = str_replace("ALTER ", "a_l_t_e_r_", strtoupper($outval));
	$outval = str_replace("--", "", strtoupper($outval));
	$outval = str_replace("HTTP", "", strtoupper($outval));
	$outval = str_replace(":", "_", strtoupper($outval));
	$outval = str_replace("=", "_", strtoupper($outval));
	$outval = str_replace("?", "_", strtoupper($outval));
	$outval = str_replace("/", "_", strtoupper($outval));
	$outval = str_replace("\\", "_", strtoupper($outval));
	return $outval;
}

function send_debug_info_by_email($subject, $body, $partner_id_tmp) {
	$b_testing_email = getPartner_param_By_ID('partner_testing_email', $partner_id_tmp);
	$s_testing_email = ($b_testing_email)?"".getPartner_param_By_ID('partner_email', $partner_id_tmp):"";

//grava_log_integracao_tmp("send_debug_info_by_email ('$partner_id_tmp', '".(($b_testing_email)?"YES":"no")."', ".getPartner_param_By_ID('partner_email', $partner_id_tmp).") - ".date("Y-m-d H:i:s")."\n");
	if($b_testing_email) {
		$GLOBALS['_SERVER']['integration_debug_info'] .= $body."\n".str_repeat("-", 80)."\n";
		send_debug_email(
						"reynaldo@e-prepag.com.br",
						"",
						"wagner@e-prepag.com.br".(($s_testing_email)?",".$s_testing_email:""),
						(($b_testing_email)?"":"SEM EMAIL DE DEBUG - ").$subject,
						str_replace("\n", "<br>\n", $GLOBALS['_SERVER']['integration_debug_info'])
		);
		$GLOBALS['_SERVER']['integration_debug_info'] = "";
	}
}

function send_debug_info_by_email_if_reynaldops($email, $subject, $msg) {
	//if(strtoupper($email)=="REYNALDOPS@BOL.COM.BR")
	{
		send_debug_email(
						"wagner@e-prepag.com.br",
						"",
						"",
						$subject,
						str_replace("\n", "<br>\n", $msg)
		);
	}
}

function grava_log_notify_db($store_id, $client_email, $cmd, $codretepp, $currency_code, $order_id, $amount, $vg_id = 0) {
	if($vg_id=="") $vg_id = 0;
	$sql_insert  = "insert into tb_integracao_pedido_notificacao_historico ";
	$sql_insert .= "(ipnh_store_id, ipnh_client_email, ipnh_cmd, ipnh_codretepp, ipnh_currency_code, ipnh_order_id, ipnh_amount, ipnh_vg_id) ";
	$sql_insert .= "values ('$store_id', '$client_email', '$cmd', '$codretepp', '$currency_code', '$order_id', '$amount', $vg_id);";
//echo "$sql_insert<br>";
	$rs = SQLexecuteQuery($sql_insert);
}


// 05/07/2011 -> 2011/07/05
function formata_data_ts_integracao($sdata) {
		$dia = substr($sdata,0,2);
		$mes = substr($sdata,3,2);
		$ano = substr($sdata,6,4);

	$sret = $ano."/".$mes."/".$dia;
	return $sret;
}

// ================================================
function getIntegracaoStatus($store_id, $order_id, &$a_retorno) {

	$ret = 0;
	if(!$store_id) return $ret;
	if(!$order_id) return $ret;

	$a_retorno = array();

	$sql = "select ipnh_data_inclusao, ipnh_codretepp, vg_ultimo_status, status, status_processed
		from tb_integracao_pedido_notificacao_historico ipnh
			left outer join tb_venda_games vg on vg.vg_id = ipnh_vg_id
			left outer join tb_pag_compras pg on pg.idvenda = ipnh_vg_id
		where 1=1 and ipnh.ipnh_store_id = '$store_id' and ipnh.ipnh_order_id = '$order_id' ";
//echo "".$sql."<br>";

	$rs = SQLexecuteQuery($sql);

	if($rs && pg_num_rows($rs) != 0){
		$rs_row = pg_fetch_array($rs);

		$a_retorno['ipnh_data_inclusao']	= $rs_row['ipnh_data_inclusao'];
		$a_retorno['ipnh_codretepp']		= $rs_row['ipnh_codretepp'];
		$a_retorno['vg_ultimo_status']		= $rs_row['vg_ultimo_status'];
		$a_retorno['status']				= $rs_row['status'];
		$a_retorno['status_processed']		= $rs_row['status_processed'];
		$ret = 1;
	}

	return $ret;
}

// ================================================
function getIntegracaoPedidoID_By_Venda($store_id, $vg_id) {

//grava_log_integracao_tmp("getIntegracaoPedidoID_By_Venda ('$store_id', '$vg_id') - ".date("Y-m-d H:i:s")."\n");
	$ip_id = 0;
	if( (!($store_id>0)) || (!($vg_id>0))) return $ip_id;

	$sql = "select ip_id
		from tb_integracao_pedido ip
		where 1=1 and ip.ip_store_id = '$store_id' and ip.ip_vg_id = '$vg_id' ";
//echo "".$sql."<br>";

	$rs = SQLexecuteQuery($sql);

	if($rs && pg_num_rows($rs) > 0){
		$rs_row = pg_fetch_array($rs);

		$ip_id = $rs_row['ip_id'];
	}

//grava_log_integracao_tmp("getIntegracaoPedidoID_By_Venda Continua ('$store_id', '$vg_id', ip_id: '$ip_id') - ".date("Y-m-d H:i:s")."\n");
	return $ip_id;
}

// ================================================
/*
Payment Received: http://lastwarbill.bilagames.com/Fillup/Eprepag/EprepagSonda.aspx?transaction_id=103041
Results: retcod = 1 & credit_date = 2011-08-31 11:24:47

Incomplete payment: http://lastwarbill.bilagames.com/Fillup/Eprepag/EprepagSonda.aspx?transaction_id=70669
Results: retcod = 2

retcod
	-1 -> Not found
	 1 -> Ok (credit_date)
	 2 -> Not yet

	// Dummy
//	 $sonda_url = "http://lastwarbill.bilagames.com/Fillup/Eprepag/EprepagSonda.aspx";

*/
function getIntegracaoSonda($store_id, $order_id, &$a_retorno) {

	$ret = 0;
	if(!$store_id) return $ret;
	if(!$order_id) return $ret;

	$sonda_url = getPartner_sonda_url_By_ID($store_id);

	$a_retorno = array();
	if(strlen($sonda_url)>0) {
		// Para Bilagames e Stardoll ainda envia o parâmetro com o nome antigo (transaction_id)
		// Para o resto (Softnyx em diante) envia o nome correto (order_id)
		$s_sonda_param_name = "order_id";
		if($store_id=="10402" || $store_id=="10406") {
			$s_sonda_param_name = "transaction_id";
		}

		$post_parameters = "".$s_sonda_param_name."=".$order_id."";
//echo "sonda_url: ".$sonda_url."<br>";
//echo "post_parameters: ".$post_parameters."<br>";
		$ret_integracao = getIntegracaoCURL($sonda_url, $post_parameters);

//echo "ret_integracao: ".$ret_integracao."<br>";
		$ret_integracao = substr($ret_integracao, strpos($ret_integracao, "retcod="));
		// Por algum motivo a Softnyx (da Korea) retorna com os caracteres "¿ÀÈÄ" -> retirar
		$ret_integracao = str_replace(" ¿ÀÀü ", "", $ret_integracao);
//echo "<hr>ret_integracao: <br>".$ret_integracao."<hr>";
		parse_str($ret_integracao, $a_retorno);
//echo "In getIntegracaoSonda('$store_id', '$order_id', -- )<br>";
//echo "In getIntegracaoSonda: ".$sonda_url."<br>".$post_parameters."<br>";
//echo "ret: ".str_replace("¿ÀÈÄ", "", $ret_integracao)."<br>";
//echo "<pre>".print_r($a_retorno, true)."</pre>";
		$ret = $a_retorno['retcod'];
	}

	return $ret;
}

function carrinho_sanitize_integracao($carrinho) {

//	grava_log_integracao(str_repeat("-", 80)."\nEm carrinho_sanitize_integracao() ".date("Y-m-d H:i:s")."\n".print_r($carrinho, true)."\n");

	$integracao_origem_id = get_Integracao_is_sessao_logged();
	if($integracao_origem_id) {
		$msg = "  - SESSÃO: ESTÁ EM INTEGRAÇÂO (store_id = $integracao_origem_id)\n";
	} else {
		$msg = "  - SESSÃO: Loja normal (sem integração)\n";
	}
//	grava_log_integracao("  (0) $msg\n");


	if(!$carrinho || count($carrinho) == 0){
	} else {
		$msg = "CARRINHO EM USO = ".(count($carrinho))." item(ns)";
//		grava_log_integracao("  (1) $msg\n");

		$a_produto_integracao = array();
		$a_produto_loja = array();
		foreach ($carrinho as $modeloId => $qtde){
			$qtde = intval($qtde);
			$s_produto_integracao_valor = get_valor_for_product($modeloId);

			$msg = "Modelo encontrado: $modeloId - $qtde - ".(($s_produto_integracao_valor)?$s_produto_integracao_valor:"-");
//			grava_log_integracao("  (2) $msg\n");

			// modelo foi encontrado no cadastro de integração
			if($s_produto_integracao_valor>=0) {
				$a_produto_integracao[] = $modeloId;
			}

			// modelo NÃO foi encontrado no cadastro de integração
			if($s_produto_integracao_valor<0) {
				$a_produto_loja[] = $modeloId;
			}
//			echo "\n";
		}
//		echo "\n";

		if(get_Integracao_is_sessao_logged()) {
			if($a_produto_loja) {
				$msg = "ACT: ESTÁ EM INTEGRAÇÂO e tem produtos da loja -> RETIRA do carrinho produtos da loja (".print_r($a_produto_loja, true).")";
//				grava_log_integracao("  (3) $msg\n");

				foreach ($carrinho as $modeloId => $qtde){
					if(in_array($modeloId, $a_produto_loja)) {
						$msg = " ** Retira '$modeloId' de carrinho (1)";
						grava_log_integracao("  (4) $msg\n");
						unset($carrinho[$modeloId]);
					}
				}
//				echo "Terminou de limpar <br>";
			} else {
				$msg = "DO NOTHING: ESTÁ EM INTEGRAÇÂO e não tem produtos da loja\n";
//				grava_log_integracao("  (4a) $msg\n");
			}
		} else {
			if($a_produto_integracao) {
				$msg = "ACT: ESTÁ na LOJA e tem produtos de integração -> RETIRA do carrinho produtos de integração (".print_r($a_produto_integracao, true).")";
//				grava_log_integracao("  (5) $msg\n");

				foreach ($carrinho as $modeloId => $qtde){
					if(in_array($modeloId, $a_produto_integracao)) {
						$msg = " ## Retira '$modeloId' de carrinho (2)<br>";
//						grava_log_integracao("  (6) $msg\n");
						unset($carrinho[$modeloId]);
					} else {
//						echo " ?? $modeloId não foi encontrado em a_produto_integracao<br>";
					}
				}
//				echo "Terminou de limpar <br>";
			} else {
				$msg = "DO NOTHING: ESTÁ na LOJA e não tem produtos de integração\n";
//				grava_log_integracao("  (5a) $msg\n");
			}
		}

		// Salva de volta o carrinho limpo
		$GLOBALS['_SESSION']['carrinho'] = $carrinho;
	}

//	grava_log_integracao("    Saindo de carrinho_sanitize_integracao() ".date("Y-m-d H:i:s")."\n".print_r($carrinho, true)."\n\n\n");
	return $carrinho;
}

function get_valor_for_product($carrinho_modelo_id) {
	global $partner_list;

	$b_modelo_existe = false;
	$modelo_valor = -1;

	foreach($partner_list as $key => $val) {
		$partner_id = $val['partner_id'];
		$produto_id_registrado = getPartner_produto_id_By_ID($partner_id);
//echo "  ==  partner_id: $partner_id   - modelo_id = $carrinho_modelo_id<br>";

		// Procura o modelo para oproduto cadastrado
		$sql_produtos = "select * from tb_operadora_games_produto_modelo ogpm
							where 1=1
								and (0=1 or ogpm.ogpm_ogp_id in (".$produto_id_registrado."))
							order by ogpm.ogpm_ogp_id, ogpm.ogpm_valor";
//if($partner_id=="10408") echo "".str_replace("\n","<br>\n",$sql_produtos)."<br>";
		$resprods = SQLexecuteQuery($sql_produtos);
		if($resprods && pg_num_rows($resprods) > 0) {
			while ($pgoprods = pg_fetch_array ($resprods)) {
				if($carrinho_modelo_id == $pgoprods['ogpm_id']) {
					$b_modelo_existe = true;
					$modelo_valor = $pgoprods['ogpm_valor'];
					break;
				}
			}
		}
		if($b_modelo_existe) {
//echo "'$key' =&gt; partner_id: '".$val['partner_id']."'; partner_active: '".$val['partner_active']."'; partner_produto_id: '".$val['partner_produto_id']."'  [MODELO EXISTE? ".(($b_modelo_existe)?"SIM":"não")."".(($b_modelo_existe)?", valor=$modelo_valor":"")."]<br>";
			break;
		}
	}
	return $modelo_valor;
}

function get_lista_produtos_integracao() {
	global $partner_list;

	$s_lista_prudutos_id = "";

	foreach($partner_list as $key => $val) {
		$partner_id = $val['partner_id'];
		if(b_Partner_is_Ativo($partner_id)) {
			$produto_id_registrado = getPartner_produto_id_By_ID($partner_id);
			$s_lista_prudutos_id .= (($s_lista_prudutos_id)?", ":"").$produto_id_registrado;
		}
	}

	return $s_lista_prudutos_id;
}

function rand_string( $length ) {
//	$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
//	          00000000001111111111222222
//            01234567890123456789012345
	$chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$numbers = "0123456789";

	$size_chars = strlen( $chars );
	$size_numbers = strlen( $numbers );
	for( $i = 0; $i < $length; $i++ ) {
		$rand_val = rand(0,9);
//echo "$i: $rand_val - ";
		if($rand_val>7) {
			$rand_pos = rand( 0, $size_chars - 1 );
			$chars_val = $chars[$rand_pos];
		} else {
			$rand_pos = rand( 0, $size_numbers - 1 );
			$chars_val = $numbers[$rand_pos];
		}
		$str .= $chars_val;
//echo " [ $rand_pos ] = '$chars_val'<br>";
	}

	return $str;
}

function b_order_exists($store_id, $order_id) {
	global $partner_list;

	$b_order_exists = false;

	// Procura pedido
	$sql_orders = "select * from tb_integracao_pedido where ip_store_id = '$store_id' and ip_order_id = '$order_id';";
	$resorders = SQLexecuteQuery($sql_orders);
	if($resorders && pg_num_rows($resorders) > 0) {
/*
		$pgorders = pg_fetch_array ($resorders)
		if($carrinho_modelo_id == $pgorders['ogpm_id']) {
			$b_order_exists = true;
		}
*/
		$b_order_exists = true;
	}
	return $b_order_exists;
}

function lista_pedidos_integracao_duplicados($store_id="", $order_id="") {
	$sql  = "select ip_store_id, ip_order_id, ip_vg_id, (select vg_ultimo_status from tb_venda_games where vg_id = ip_vg_id ) as vg_ultimo_status, min(ip_data_inclusao) as min_data, max(ip_data_inclusao) as max_data, count(*) as n
		from  tb_integracao_pedido
		where ip_data_inclusao >= NOW()-'3 month'::interval
			and (select vg_ultimo_status from tb_venda_games where vg_id = ip_vg_id ) = 5
		";
	if($store_id) {
		$sql .=	"and ip_store_id = '$store_id'\n";
	}
	if($order_id) {
		$sql .=	"and ip_order_id = '$order_id'\n";
	}
	$sql .=	"group by ip_store_id, ip_order_id, ip_vg_id, vg_ultimo_status
		having count(*) >1
		order by n desc, max_data desc
	";
/*
if(b_IsUsuarioReinaldo()) {
echo str_replace("\n", "<br>\n", $sql)."<br>";
}
*/
	$res = SQLexecuteQuery($sql);

	$sret  = "";
	$sret .= "<table cellpadding='2' cellspacing='2' border='1' bordercolor='#cccccc' style='border-collapse:collapse;background-color:#FFFF99'>\n";
	$sret .= "<tr style='text-align:center;font-weight:bold; color:blue; font-size:8pt'> <td colspan='6'><nobr>Pedidos de integração completos duplicados</nobr></td></tr>";
	if($res && pg_num_rows($res) > 0) {
		$s_str = ((pg_num_rows($res)>1)?"s":"");
		$sret .= "<tr style='text-align:center;color:red; font-size:8pt'> <td colspan='6'><nobr>Existe".((pg_num_rows($res)>1)?"m":"")." " .pg_num_rows($res). " pedido".$s_str." de integração <b>completo".$s_str."</b> DUPLICADO".strtoupper($s_str)."</nobr></td></tr>";
		$sret .= "<tr style='text-align:center;font-weight:bold; font-size:8pt'> <td>Store_ID</td> <td>Order_ID</td> <td>vg_id</td> <td>min_data</td> <td>max_data</td> <td>n</td></tr>";
		while ($pg = pg_fetch_array ($res)) {
			$sret .= "<tr style='font-size:8pt'> ";
			$sret .= "<td>".$pg['ip_store_id']."</td>";
			$sret .= "<td>".$pg['ip_order_id']."</td>";
			$sret .= "<td>".$pg['ip_vg_id']."</td>";
			$sret .= "<td>".$pg['min_data']."</td>";
			$sret .= "<td>".$pg['max_data']."</td>";
			$sret .= "<td>".$pg['n']."</td>";
			$sret .= "</tr>";
		}
	} else {
		$sret .= "<tr style='text-align:center;color:blue; font-size:8pt'> <td colspan='6'><nobr>Não existem pedidos de integração <b>completos</b> DUPLICADOS</nobr></td></tr>";
	}
	$sret .= "</table>\n";

	return $sret;
}

function retornaIdsIntegracao($opr_codigo) {
    //echo "<pre>".print_r($GLOBALS['partner_list'],true)."-</pre>";
    $retorno = array();
    foreach($GLOBALS['partner_list'] as $key => $value) {
        if($value['partner_opr_codigo'] == $opr_codigo) {
            $retorno[$value['partner_id']] = $key;
        }//end if
    }//end foreach
    return $retorno;
}//end function retornaIdsIntegração()

function montaSelectIdsIntegracao($dd_operadora, $dd_ids_integracao = null) {
    $idsIntegracao = retornaIdsIntegracao($dd_operadora);
    $retorno = "";
    if(count($idsIntegracao)>0) {
        $retorno .= "
        <select name='dd_ids_integracao' id='dd_ids_integracao' class='form-control'>
                          <option value=''>".LANG_PINS_ALL_VALUES."</option>
         ";
        foreach($idsIntegracao as $key => $value) {
            $retorno .= "
                           <option value='$key' ".(($key==$dd_ids_integracao)?"selected":"").">$value</option>
                    ";
        }//end foreach
        $retorno .= "
        </select>"; 
    }//end if(count($idsIntegracao)>0)
    return $retorno;
}//end function montaSelectIdsIntegracao($dd_operadora, $dd_ids_integracao = null)


// ================================================
function getAutoClose_By_ID($id) {
	global $partner_list;
	$partner_auto_close = false;
	foreach($partner_list as $key => $val) {
		if($val['partner_id']==$id) {
                        if(!empty($val['auto_close'])) {
                            $partner_auto_close = true;
			}
			break;
		}
	}
	return $partner_auto_close;
}//end function getAutoClose_By_ID

// ================================================
function getLinkURL_By_ID($id) {
	global $partner_list;
	$partner_link_url = "";
	foreach($partner_list as $key => $val) {
		if($val['partner_id']==$id) {
                        if(!empty($val['link_url'])) {
                            $partner_link_url = $val['link_url'];
			}
			break;
		}
	}
	return $partner_link_url;
}//end function getLinkURL_By_ID

// ================================================
function partnerNeedCPF($id) {
	global $partner_list;
	$partner_cpf = false;
	foreach($partner_list as $key => $val) {
		if($val['partner_id']==$id) {
                        if(!empty($val['partner_need_cpf'])) {
                            $partner_cpf = true;
			}
			break;
		}
	}
	return $partner_cpf;
}//end function partnerNeedCPF

function partnerKindCPF($id) {
	global $partner_list;
	$partner_cpf = 0;
	foreach($partner_list as $key => $val) {
		if($val['partner_id']==$id) {
                        if(!empty($val['partner_need_cpf'])) {
                            $partner_cpf = $val['partner_need_cpf'];
			}
			break;
		}
	}
	return $partner_cpf;
}//end function partnerKindCPF

function SolicitaCPF($ug_id) {
    global $CPF_OBRIGATORIO, $CPF_OPICIONAL;
    $id_partner = get_Integracao_is_sessao_logged();
    $server_url = "www.e-prepag.com.br";
    if(checkIP()) {
        $server_url = $_SERVER['SERVER_NAME'];
    }
    $server_url = "http".(($_SERVER['HTTPS']=="on")?"s":"") ."://" . $server_url;

    $sql = "SELECT *
                    FROM usuarios_games
                    WHERE ug_ativo = '1'
                            AND (length(ug_cpf) < 14 OR ug_cpf IS NULL)
                            AND ug_id = ".$ug_id.";";
    //echo $sql.":sql<br>";
    $rs_cpf = SQLexecuteQuery($sql);

    if($rs_cpf && pg_num_rows($rs_cpf) > 0) {
        require($_SERVER['DOCUMENT_ROOT'].'/prepag2/incs/rf_cpf/funcoes.php');
	$getCaptchaToken = getCaptchaToken();

	// pf, seja mais criativo
	if(!is_array($getCaptchaToken))
	{
		echo 'Não foi possível obter Captcha e Token';
		//exit;
	}

    $captcha = '<a href="'.$server_url.$GLOBALS['_SERVER']['PHP_SELF'].'"><span style="font-size:11px;font-weight: normal;font-style:italic;">Gerar nova imagem</span></a><br>
                          <input  class="input-captcha-cpf" name="captcha" type="text" maxlength="6" required /><br><span style="font-size:11px;font-weight: normal;font-style:italic;">O que vê na imagem acima?</span>
                          <input type="hidden" name="viewstate" value="'.$getCaptchaToken[1].'" />
                          ';
     $retorno = "<div id='popup_cpf' align='left' title=''>
                            <script type='text/javascript'>

                            function get_captcha(){
                                $.ajax({
                                    type:'GET',
                                    data:\"id=".$getCaptchaToken[0]."\",
                                    url:\"".$server_url."/prepag2/incs/rf_cpf/getcaptcha.php\",
                                    beforeSend: function(){
                                        $('#captcha_img').html(\"<table><tr class='box-principal-login-class'><td><img src='../../dist_commerce/images/loading1.gif' border='0' title='Aguardando pagamento...'/></td></tr><tr class='box-principal-login-class'><td><font size='1'><b>Aguarde... Verificando.</b></font></td></tr></table>\");
                                    },
                                    success: function(txt){
                                        if (txt != 'error') {
                                           $('#captcha_img').html(txt);
                                        }
                                        else{
                                           $('#captcha_img').html('Erro! Tente novamente, por favor!');
                                        }
                                    },
                                    error: function(){
                                            $('#captcha_img').html('Erro! Tente novamente, por favor!');
                                        }
                                });

                                }

                                function Trim(str){
                                    return str.replace(/^\\s+|\\s+$/g,'');
                                }
                                function validaform() {
                      			if (document.frmPreCadastro.ug_cpf.value == '') {
                                                alert('Informe o CPF');
                                                document.frmPreCadastro.ug_cpf.focus();
                                                document.frmPreCadastro.ug_cpf.select();
                                                return false;
                                        }
                                        else if(!validaRespostaCPF(document.frmPreCadastro.ug_cpf.value)) {
                                                alert('CPF inválido, por favor revise o número digitado.');
                                                document.frmPreCadastro.ug_cpf.focus();
                                                document.frmPreCadastro.ug_cpf.select();
                                                return false;
                                        }
                                        else return true;
                                }//end function validaform()

                                function validaRespostaCPF(cpf) {
                                    cpf = cpf.replace(/[^\d]+/g,'');
                                    if(cpf == '') return false;

                                    // Elimina CPFs invalidos conhecidos
                                    if (cpf.length != 11 ||
                                            cpf == '00000000000' ||
                                            cpf == '11111111111' ||
                                            cpf == '22222222222' ||
                                            cpf == '33333333333' ||
                                            cpf == '44444444444' ||
                                            cpf == '55555555555' ||
                                            cpf == '66666666666' ||
                                            cpf == '77777777777' ||
                                            cpf == '88888888888' ||
                                            cpf == '99999999999')
                                            return false;

                                    // Valida 1o digito
                                    add = 0;
                                    for (i=0; i < 9; i ++)
                                            add += parseInt(cpf.charAt(i)) * (10 - i);
                                    rev = 11 - (add % 11);
                                    if (rev == 10 || rev == 11)
                                            rev = 0;
                                    if (rev != parseInt(cpf.charAt(9)))
                                            return false;

                                    // Valida 2o digito
                                    add = 0;
                                    for (i = 0; i < 10; i ++)
                                            add += parseInt(cpf.charAt(i)) * (11 - i);
                                    rev = 11 - (add % 11);
                                    if (rev == 10 || rev == 11)
                                            rev = 0;
                                    if (rev != parseInt(cpf.charAt(10)))
                                            return false;

                                    return true;
                              }//end validaRespostaCPF()

                              get_captcha();

                             </script>
                            <form method='post' action='".$server_url.$GLOBALS['_SERVER']['PHP_SELF']."' name='frmPreCadastro' id='frmPreCadastro' onsubmit='return validaform();'>
                                 <input type='hidden' name='ug_show' id='ug_show' value='1' />
                                    <div style='color:#128327;font-size:16px;font-weight: bold;'>
                                        Por favor, complete o campo abaixo<br>com o seu número de CPF <span style='font-size:12px;font-weight: normal;'>(ou do responsável)</span><br>
                                    </div>
                                    <div style='background-color:#e7eef8;font-size:15px;font-weight: bold;'>
                                    </div>
                                    <div style='color:#717171;font-size:12px;font-style:italic;'>
                                        Esta solicitação será feita apenas 1 vez.<br><br>
                                    </div>
                                    <div style='color:#717171;font-size:15px;font-weight: bold;' >
                                        CPF<br>
                                        <input type='text' name='ug_cpf' id='ug_cpf' maxlength='11' size='29'/><br>
                                        <span style='font-size:11px;font-weight: normal;font-style:italic;'>Digite somente números, sem pontos e nem traços.</span>
                                         <div id='captcha_img'></div>$captcha
                                    </div>
                ";
            if(partnerKindCPF($id_partner) == $CPF_OPICIONAL) { 
                $retorno .= "<input type='button' name='fechar' value='' style='background:url(\"" . $server_url . "/prepag2/commerce/images/botao_agoranao.gif\");background-repeat:no-repeat;width:167px;height:34px;' onClick='javascript:document.frmPreCadastro.submit();'>
                            ";
            } //end if(partnerKindCPF($id_partner) == $CPF_OPICIONAL)                
            $retorno .= "
                                        <input type='hidden' name='Submit' id='Submit' value='RESPONDER'>
                                        <input type='submit' name='resp' id='resp' value='' style='background:url(\"" . $server_url . "/prepag2/commerce/images/botao_confirmar.gif\");background-repeat:no-repeat;width:167px;height:34px;'/>
                             </form>
                        </div>
                        <script type='text/javascript' src='".$server_url . "/prepag2/js/jqueryui/js/jquery-1.7.1.js'></script>
                        <script type='text/javascript' src='" . $server_url . "/prepag2/js/jqueryui/js/jquery-ui-1.8.16.custom.min.js'></script>
                        <style type='text/css'><!-- @import '" . $server_url . "/prepag2/js/jqueryui/css/eprepag/jquery-ui-1.8.16.custom.css';--></style>
                        ";
            if(partnerKindCPF($id_partner) == $CPF_OBRIGATORIO) { 
                $retorno .= "<script type='text/javascript' src='" . $server_url . "/prepag2/js/jqueryui/popup_cpf.js'></script>";
            } //end if(partnerKindCPF($id_partner) == $CPF_OBRIGATORIO) 
            elseif(partnerKindCPF($id_partner) == $CPF_OPICIONAL) {
                $retorno .= "<script type='text/javascript' src='" . $server_url . "/prepag2/js/jqueryui/popup_cpf_opcional.js'></script>";
            } //end elseif(partnerKindCPF($id_partner) == $CPF_OPICIONAL)
            return $retorno;

    }// end  if($rs_cpf && pg_num_rows($rs_cpf) > 0)
    else{
            // ~* Matches regular expression, case insensitive
            $sql = "SELECT ug_nome
                            FROM usuarios_games
                            WHERE ug_ativo = '1'
                                AND (ug_nome !~* '^\\\\s*[a-zA-ZÀ-ú\']{2,}(\\\\s+[a-zA-ZÀ-ú\']{2,}\\\\s*)+$' OR ug_nome='' )
                                AND ug_id = ".$ug_id.";";
            //echo $sql."<br>";
            $rs_nome = SQLexecuteQuery($sql);

            if($rs_nome && pg_num_rows($rs_nome) > 0) {
                $retorno = "<div id='popup_cpf' align='left' title=''>
                            <script type='text/javascript'>

                                function Trim(str){
                                    return str.replace(/^\\s+|\\s+$/g,'');
                                }

                                function validaform() {
                                    if (document.frmPreCadastro.ug_nome.value == '') {
                                        alert('Informe o nome completo.');
                                        document.frmPreCadastro.ug_nome.focus();
                                        document.frmPreCadastro.ug_nome.select();
                                        return false;
                                        }
                                    else if(!validaNome(document.frmPreCadastro.ug_nome.value)) {
                                        alert('Nome Inválido. \\nPor favor, tente novamente seu nome completo sem abreviações,\\npontos, números ou caracteres especiais.');
                                        document.frmPreCadastro.ug_nome.focus();
                                        document.frmPreCadastro.ug_nome.select();
                                        return false;
                                        }
                                    else return true;
                                }//end function validaform()

                                function validaNome(myString){
                                    /* RegExp created via www.regexp.com.br */
                                    var regExp = new RegExp('^\\\\s*[a-zA-ZÀ-ú\']{2,}(\\\\s+[a-zA-ZÀ-ú\']{2,}\\\\s*)+$');
                                    //alert(myString.match(regExp));
                                    return myString.match(regExp);
                                    }

                             </script>
                            <form method='post' action='".$server_url.$GLOBALS['_SERVER']['PHP_SELF']."' name='frmPreCadastro' id='frmPreCadastro' onsubmit='return validaform();'>
                                 <input type='hidden' name='ug_show' id='ug_show' value='1' />
                                    <div style='color:#128327;font-size:16px;font-weight: bold;'>
                                        Por favor, complete o campo abaixo<br>com o seu nome completo <span style='font-size:12px;font-weight: normal;'></span><br>
                                    </div>
                                    <div style='background-color:#e7eef8;font-size:15px;font-weight: bold;'>
                                    </div>
                                    <div style='color:#717171;font-size:12px;font-style:italic;'>
                                        Esta solicitação será feita apenas 1 vez.<br><br>
                                    </div>
                                    <div style='color:#717171;font-size:15px;font-weight: bold;' >
                                        Nome completo<br>
                                        <input type='text' name='ug_nome' id='ug_nome' maxlength='30' size='50'/><br>
                                        <span style='font-size:11px;font-weight: normal;font-style:italic;'>Não utilize números, caracteres especiais ou abreviaturas</span>
                                        <br>
                                    </div><br>";
                if(partnerKindCPF($id_partner) == $CPF_OPICIONAL) { 
                    $retorno .= "<input type='button' name='fechar' value='' style='background:url(\"" . $server_url . "/prepag2/commerce/images/botao_agoranao.gif\");background-repeat:no-repeat;width:167px;height:34px;' onClick='javascript:document.frmPreCadastro.submit();'>
                                ";
                } //end if(partnerKindCPF($id_partner) == $CPF_OPICIONAL)                
                $retorno .= "
                                        <input type='hidden' name='Submit' id='Submit' value='RESPONDER'>
                                        <input type='submit' name='resp' id='resp' value='' style='background:url(\"" . $server_url . "/prepag2/commerce/images/botao_confirmar.gif\");background-repeat:no-repeat;width:167px;height:34px;'/>
                             </form>
                        </div>
                        <script type='text/javascript' src='".$server_url . "/prepag2/js/jqueryui/js/jquery-1.7.1.js'></script>
                        <script type='text/javascript' src='" . $server_url . "/prepag2/js/jqueryui/js/jquery-ui-1.8.16.custom.min.js'></script>
                        <style type='text/css'><!-- @import '" . $server_url . "/prepag2/js/jqueryui/css/eprepag/jquery-ui-1.8.16.custom.css';--></style>
                        ";
                if(partnerKindCPF($id_partner) == $CPF_OBRIGATORIO) {
                    $retorno .= "<script type='text/javascript' src='" . $server_url . "/prepag2/js/jqueryui/popup_cpf.js'></script>";
                } //end if(partnerKindCPF($id_partner) == $CPF_OBRIGATORIO) 
                elseif(partnerKindCPF($id_partner) == $CPF_OPICIONAL) {
                    $retorno .= "<script type='text/javascript' src='" . $server_url . "/prepag2/js/jqueryui/popup_cpf_opcional.js'></script>";
                } //end elseif(partnerKindCPF($id_partner) == $CPF_OPICIONAL)

                return $retorno;
            }// end  if($rs_cpf && pg_num_rows($rs_cpf) > 0)
    }

}//end function SolicitaCPF()

function mask($val, $mask)
{
    $maskared = '';
    $k = 0;
    for($i = 0; $i<=strlen($mask)-1; $i++)
    {
        if($mask[$i] == '#')
        {
            if(isset($val[$k]))
                $maskared .= $val[$k++];
        }
        else
        {
            if(isset($mask[$i]))
                $maskared .= $mask[$i];
        }
    }
    return $maskared;
}//end function

function verificaCPF_int($cpf) {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);

	$RecebeCPF=$cpf;

		if (strlen($RecebeCPF)!=11)
		{ return 0; }
		else
		if ($RecebeCPF=="00000000000" || $RecebeCPF=="11111111111")
		{ return 0; }
		else
		{
			$Numero[1]=intval(substr($RecebeCPF,1-1,1));
			$Numero[2]=intval(substr($RecebeCPF,2-1,1));
			$Numero[3]=intval(substr($RecebeCPF,3-1,1));
			$Numero[4]=intval(substr($RecebeCPF,4-1,1));
			$Numero[5]=intval(substr($RecebeCPF,5-1,1));
			$Numero[6]=intval(substr($RecebeCPF,6-1,1));
			$Numero[7]=intval(substr($RecebeCPF,7-1,1));
			$Numero[8]=intval(substr($RecebeCPF,8-1,1));
			$Numero[9]=intval(substr($RecebeCPF,9-1,1));
			$Numero[10]=intval(substr($RecebeCPF,10-1,1));
			$Numero[11]=intval(substr($RecebeCPF,11-1,1));

			$soma=10*$Numero[1]+9*$Numero[2]+8*$Numero[3]+7*$Numero[4]+6*$Numero[5]+5*
			$Numero[6]+4*$Numero[7]+3*$Numero[8]+2*$Numero[9];
			$soma=$soma-(11*(intval($soma/11)));

			if ($soma==0 || $soma==1)
			{ $resultado1=0; }
			else
			{ $resultado1=11-$soma; }

			if ($resultado1==$Numero[10])
			{
				$soma=$Numero[1]*11+$Numero[2]*10+$Numero[3]*9+$Numero[4]*8+$Numero[5]*7+$Numero[6]*6+$Numero[7]*5+
				$Numero[8]*4+$Numero[9]*3+$Numero[10]*2;
				$soma=$soma-(11*(intval($soma/11)));

				if ($soma==0 || $soma==1)
				{ $resultado2=0; }
				else
				{ $resultado2=11-$soma; }
				if ($resultado2==$Numero[11])
				{ return TRUE;}
				else
				{ return 0; }
			}
			else
			{ return 0; }
	 }
}

function verificaDataCPFInformado($data_cpf_informado){
    
    if($data_cpf_informado){
        //Capturando no formato 'YYYY-MM-DD HH:mm:ss'
        $data_cpf_informado = substr($data_cpf_informado, 0, 19);

        $agora = date("YmdHis");

        //Adicionando NUM_DIAS_CONSIDERADO à data em que o CPF foi informado para comparar com a DATA DE HOJE. NUM_DIAS_CONSIDERADO definido no inicio dessa classe
        $limite_dias = date( "YmdHis", strtotime( $data_cpf_informado." +".$GLOBALS['NUM_DIAS_CONSIDERADO']." days" ));
        
        if($agora > $limite_dias){
            return FALSE;  //NECESSITA DE OUTRA CONSULTA
        } else{
            return TRUE;
        }
    } else{
        return FALSE;      //NECESSITA DE OUTRA CONSULTA
    }
    
}

function verificaNome($nome) {

    $reg = '/^\\s*[a-zA-ZÀ-ú\']{1,}(\\s+[a-zA-ZÀ-ú\']{1,}\\s*)+$/';

    if (preg_match($reg, $nome) && strpos($nome, "  ") === false) {
        return TRUE;
    }
    return FALSE;

}

function endereco_page($preencher_endereco){
    
    if(isset($GLOBALS['_SESSION']['usuarioGames_ser']) && !is_null($GLOBALS['_SESSION']['usuarioGames_ser'])){
            if($preencher_endereco){
                include DIR_WEB . "prepag2/commerce/includes/form_endereco.php";
                include DIR_WEB . "prepag2/commerce/includes/rodape.php"; 
                die();
            }            
    }
    else {
        echo "<div class='txt-vermelho text-center top50'><p>Sua sessão expirou. Volte no jogo e tente novamente. Obrigado!</p></div>";
        include "rodape.php"; 
        die();
    }
}

function cpf_page($partner_list){

    if(isset($GLOBALS['_SESSION']['usuarioGames_ser']) && !is_null($GLOBALS['_SESSION']['usuarioGames_ser'])){
        $usuarioGames = unserialize($GLOBALS['_SESSION']['usuarioGames_ser']);
        $usuarioId = $usuarioGames->getId();
    }

    //var_dump($GLOBALS['_SESSION']); die;

    $vg_integracao_parceiro_origem_id = $GLOBALS['_SESSION']['integracao_origem_id']; //$rs_venda_row['vg_integracao_parceiro_origem_id'];

    $user = getUserFromId($usuarioGames->getId());

    $partner = $partner_list[ array_query("partner_id", $vg_integracao_parceiro_origem_id, $partner_list) ];

    $is_data_valid = verificaNome($user->ug_nome_cpf) && verificaCPF_int($user->ug_cpf);

    //Ajustando dados de CPF para usuários cadastrados no sistema antes de usar a integração
    /*
    if(empty($user->ug_data_cpf_informado)|| empty($user->ug_nome_cpf)) {
        $sql_update = "update usuarios_games set ug_data_cpf_informado=NOW(), ug_nome_cpf = ug_nome where ug_id=".$usuarioGames->getId().";";
        $rs = SQLexecuteQuery($sql_update);
    }//end if(empty($user->ug_data_cpf_informado)|| empty($user->ug_data_cpf_informado))
    */
    
    if( isset($_REQUEST['skip']) && $partner['partner_need_cpf'] != 2 ){
        die('<script>window.history.back();</script>');
    }
    
    if( ( $partner['partner_need_cpf']==1 && !$is_data_valid ) || ( $partner['partner_need_cpf']==2 && !$is_data_valid && !isset($GLOBALS['_SESSION']['skip']) ) ){
        include RAIZ_DO_PROJETO . '/public_html/prepag2/commerce/includes/form_cpf.php';
        die();           
    }
    else {
        if( ( $partner['partner_need_cpf']==1) || ( $partner['partner_need_cpf']==2 && !isset($GLOBALS['_SESSION']['skip']) ) ){
            require_once RAIZ_DO_PROJETO . "consulta_cpf/config.inc.cpf.php";
            //require_once RAIZ_DO_PROJETO . "public_html/includes/functions.php";
            $parametros = array(
                            'cpfcnpj' => preg_replace('/[^0-9]/', '', $user->ug_cpf)
                            );
            $testeDadosAdicionais = new classCPF(false);
            if($testeDadosAdicionais->consultaQuantidadeUtilizada($parametros) >= $testeDadosAdicionais->get_quantidade_limite()) {
                if(empty($user->ug_nome_da_mae) || empty($user->ug_endereco) || empty($user->ug_numero) || empty($user->ug_bairro) || empty($user->ug_cidade) || empty($user->ug_estado) || empty($user->ug_cep) || (empty($user->ug_tel_ddd) && empty($user->ug_cel_ddd)) || (empty($user->ug_tel) && empty($user->ug_cel)) ) {       
                    echo modal_includes();
                    $mensagem ="Olá ".$user->ug_nome."!<br><br>Agradecemos sua preferência pela E-Prepag, porém o CPF cadastrado apresenta um grande volume de transações.<br><br>Para prosseguir com esta compra, precisamos que você acesse o formulário abaixo e envie os documentos solicitados. Após análise, sua conta poderá ser liberada em até um dia útil.<br><br>OBS: Utilize os navegadores Google Chrome ou Mozilla Firefox para visualizar o formulário corretamente.<br><br><span onclick='window.open(\"http://e-prepagpdv.com.br/e-prepag-limite-de-compras-com-cpf/\");' style='cursor:pointer; color:#2e5984;'>Clique aqui</span> para enviar os documentos.";
                    echo "<script>$(function(){ showMessage('".str_replace("'","\'",$mensagem)."'); });</script>";
                    echo "</table><div class='m-top20'>".$mensagem."</div></div></div>";
                    die(); 
                }//end 
            } //end if($testeDadosAdicionais->consultaQuantidadeUtilizada($parametros) >= $testeDadosAdicionais->get_quantidade_limite())
            
            //Validando a data da consulta
            if(!verificaDataCPFInformado($user->ug_data_cpf_informado)) {
                $_REQUEST['formsubmit'] = true;
                $_REQUEST['cpf'] = $user->ug_cpf;
                $_REQUEST['data_nascimento'] = formata_data($user->ug_data_nascimento,0);
                $_REQUEST['consulta_automatica'] = '1';
                include RAIZ_DO_PROJETO . '/public_html/prepag2/commerce/includes/form_cpf.php';
                die();
            }//end if(!verificaDataCPFInformado($user->ug_data_cpf_informado))
            
        }//end if( ( $partner['partner_need_cpf']==1) || ( $partner['partner_need_cpf']==2 && !isset($GLOBALS['_SESSION']['skip']) ) )
    } //end else do if(!$is_data_valid)

}

function getUserFromId($id){
        $instUsuarioGames = new UsuarioGames;
        $instUsuarioGames->obter( array('ug_id'=>$id), 'ug_id', $rs );
        return pg_fetch_object($rs);
    }

function array_query($key, $val, $arr){
    foreach($arr as $i=>$v){
        if(!array_key_exists($key, $v))
            continue;

        if( $v[$key]==$val )
            return $i;
    }

    return false;
}

function integracao_layout($type, $data=false){
    global $GLOBALS;

    $usr = unserialize($GLOBALS['_SESSION']['usuarioGames_ser']);

    if( $type=="css" || $type=="includes" ){
        $url = 'http' . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=="on") ? "s" : "") . '://' . $_SERVER['SERVER_NAME'];
        $html = "";
//        $html .= '<link rel="stylesheet" href="'.$url.'/prepag2/css/form_cpf.css" type="text/css" />';
        if(!isset($GLOBALS["jquery"]))
            $html .= PHP_EOL . '<script src="'.$url.'/js/jquery-1.11.3.min.js"></script>';
        $html .= PHP_EOL . '<script src="'.$url.'/js/form_cpf_valida.js"></script>';
        return $html;
    }

    if( $type=="header" ){
        $data['gamelogo'] = getPartner_partner_img_logo_By_ID($GLOBALS['_SESSION']['integracao_origem_id']);
        return Helper::transform( $data, file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/prepag2/commerce/includes/integracao_header.html'));
    }

    if( $type=="order" ){
        $template_file = count($GLOBALS['_SESSION']['int_cart']) > 0 ? "integracao_order.php" : "integracao_order_empty.php";

        $data['venda_id'] = $GLOBALS['_SESSION']['venda'];
        $data['email'] = strtolower($usr->ug_sEmail);
        $data['cart'] = $GLOBALS['_SESSION']['int_cart'];

        //return Helper::transform( $data, file_get_contents($_SERVER['DOCUMENT_ROOT'] . "\prepag2\commerce\includes\\" . $template_file) );
        include($_SERVER['DOCUMENT_ROOT'] . "/prepag2/commerce/includes/" . $template_file);
    }

}

//Função que verifica se o publisher exige CPF do Gamer
function checkingNeedCPF($opr_codigo) {
    $sql_function ="SELECT opr_need_cpf_lh from operadoras where opr_codigo=".intval($opr_codigo).";";
    $rs_function = SQLexecuteQuery($sql_function);
    if($rs_function_row = pg_fetch_array($rs_function)) {
            $opr_need_cpf_lh = $rs_function_row['opr_need_cpf_lh'];
    }
    return $opr_need_cpf_lh;
}//end function checkingNeedCPF


function cpf_page_gamer(){
    
    global $controller, $aux, $produto_idade_minima;

    if(isset($GLOBALS['_SESSION']['usuarioGames_ser']) && !is_null($GLOBALS['_SESSION']['usuarioGames_ser'])){
        $usuarioGames = unserialize($GLOBALS['_SESSION']['usuarioGames_ser']);
        $usuarioId = $usuarioGames->getId();
    }

    $user = getUserFromId($usuarioGames->getId());
    
    $is_data_valid = verificaNome($user->ug_nome_cpf) && verificaCPF_int($user->ug_cpf);
    
    if(!$is_data_valid){
        include DIR_INCS . 'gamer/form_cpf_gamer.php';
        if(isset($controller->logado) && $controller->logado) {
                echo "</div></div><div class='top20'></div>";
                require_once DIR_WEB . "game/includes/footer.php";
        } //end if(isset($controller->logado) && $controller->logado)
        die();           
    }
    elseif(!verificaIdadeMinima($user->ug_data_nascimento)){
        echo "<div class='row'> <div class='col-lg-12'><div class='alert alert-danger' role='alert'>O produto " . $GLOBALS["produto_idade_minima"] . " é destinado para maiores de " . $GLOBALS["IDADE_MINIMA"] . " anos. Esta compra só poderá ser concluída caso você informe o CPF e data de nascimento dos seus pais ou responsável.</div></div</div>";
//        echo "</div></div><div class='top20'></div></class='container-fluid>";
//        require_once DIR_WEB . "game/includes/footer.php";
        die();
    }
    else {
        
        require_once RAIZ_DO_PROJETO . "consulta_cpf/config.inc.cpf.php";
        //require_once DIR_WEB . "includes/functions.php";
        $parametros = array(
                        'cpfcnpj' => preg_replace('/[^0-9]/', '', $user->ug_cpf)
                        );
        $testeDadosAdicionais = new classCPF(false);
        if($testeDadosAdicionais->consultaQuantidadeUtilizada($parametros) >= $testeDadosAdicionais->get_quantidade_limite()) {
            if(empty($user->ug_nome_da_mae) || empty($user->ug_endereco) || empty($user->ug_numero) || empty($user->ug_bairro) || empty($user->ug_cidade) || empty($user->ug_estado) || empty($user->ug_cep) || (empty($user->ug_tel_ddd) && empty($user->ug_cel_ddd)) || (empty($user->ug_tel) && empty($user->ug_cel)) ) {       
                echo modal_includes();
                $mensagem ="Olá ".$user->ug_nome."!<br><br>Agradecemos sua preferência pela E-Prepag, porém o CPF cadastrado apresenta um grande volume de transações.<br><br>Para prosseguir com esta compra, precisamos que você acesse o formulário abaixo e envie os documentos solicitados. Após análise, sua conta poderá ser liberada em até um dia útil.<br><br>OBS: Utilize os navegadores Google Chrome ou Mozilla Firefox para visualizar o formulário corretamente.<br><br><span onclick='window.open(\"http://e-prepagpdv.com.br/e-prepag-limite-de-compras-com-cpf/\");' style='cursor:pointer; color:#2e5984;'>Clique aqui</span> para enviar os documentos.";
                echo "<script>$(function(){ showMessage('".str_replace("'","\'",$mensagem)."'); });</script>";
                echo "</table><div class='m-top20'>".$mensagem."</div></div></div>";
                require_once DIR_WEB . "game/includes/footer.php";
                die(); 
            }//end 
        } //end if($testeDadosAdicionais->consultaQuantidadeUtilizada($parametros) >= $testeDadosAdicionais->get_quantidade_limite())
            
        //Validando a data da consulta
        if(!verificaDataCPFInformado($user->ug_data_cpf_informado)) {
            $_POST['formsubmit'] = true;
            $_POST['cpf'] = $user->ug_cpf;
            $_POST['data_nascimento'] = formata_data($user->ug_data_nascimento,0);
            $_POST['consulta_automatica'] = '1';
            include DIR_INCS . 'gamer/form_cpf_gamer.php';
            if(isset($controller->logado) && $controller->logado) {
                    echo "</div></div><div class='top20'></div>";
                    require_once DIR_WEB . "game/includes/footer.php";
            } //end if(isset($controller->logado) && $controller->logado)            
            die();
        }//end if(!verificaDataCPFInformado($user->ug_data_cpf_informado))
        
    } //end else do if(!$is_data_valid)

}

function lista_pedidos_bancos_pagos_com_EPPCASH() {
	$sql  = "select vg_id,to_char(vg_data_inclusao,'DD/MM/YYYY HH24:MI:SS') as data,vg_pagto_tipo,vg_pagto_valor_pago 
                from pins_store_pag_epp_pin 
                        inner join tb_venda_games ON vg_id = tpc_idvenda
                where vg_pagto_tipo != 13;
		";
	$res = SQLexecuteQuery($sql);
	$sret  = "";
	$sret .= "<table cellpadding='2' cellspacing='2' border='1' bordercolor='#cccccc' style='border-collapse:collapse;background-color:#FFFF99'>\n";
	$sret .= "<tr style='text-align:center;font-weight:bold; color:blue; font-size:8pt'> <td colspan='6'><nobr>Tipo de pagamento Diferente de EPP CASH e pagos com PINs</nobr></td></tr>";
	if($res && pg_num_rows($res) > 0) {
		$s_str = ((pg_num_rows($res)>1)?"s":"");
		$sret .= "<tr style='text-align:center;color:red; font-size:8pt'> <td colspan='6'><nobr>Existe".((pg_num_rows($res)>1)?"m":"")." " .pg_num_rows($res). " pedido".$s_str." pago".$s_str."</b> com EPP CASH".strtoupper($s_str)."</nobr></td></tr>";
		$sret .= "<tr style='text-align:center;font-weight:bold; font-size:8pt'> <td>ID Pedido</td> <td>Data</td> <td>Tipo Pagto</td> <td>Valor</td></tr>";
		while ($pg = pg_fetch_array ($res)) {
			$sret .= "<tr style='font-size:8pt'> ";
			$sret .= "<td>".$pg['vg_id']."</td>";
			$sret .= "<td>".$pg['data']."</td>";
			$sret .= "<td>".$pg['vg_pagto_tipo']."</td>";
			$sret .= "<td>R$ ".number_format($pg['vg_pagto_valor_pago'], 2, ",", ".")."</td>";
			$sret .= "</tr>";
		}
	} else {
		$sret .= "<tr style='text-align:center;color:blue; font-size:8pt'> <td colspan='6'><nobr>Não existem pedidos com Problemas</nobr></td></tr>";
	}
	$sret .= "</table>\n";
	return $sret;
}//end function lista_pedidos_bancos_pagos_com_EPPCASH()

function verificaIdadeMinima($dataNascimento){
    $date = new DateTime($dataNascimento);
    $interval = $date->diff( new DateTime( date('Y-m-d') ) );
    return ($interval->format( '%Y' ) >= $GLOBALS["IDADE_MINIMA"]);
}
?>
