<?php
require_once "/www/includes/load_dotenv.php";
require_once "../../../includes/functions.php";
/* Constantes com os IDs dos Canais de Ofertas */

$canaisOfertas = array("MATOMY" => 1, 
					   "SPONSORPAY" => 2, 
					   "SUPER_REWARDS" => 3
					   );

/* URL das images dos logos dos canais de ofertas */
define("URL_LOGO_IMAGE", "/images/gamer/");

/* Valores que representam o Status */
define("EPP_STATUS_OFERTA_REGISTRADA", 1);
define("EPP_STATUS_OFERTA_PROCESSADA", 2);
define("EPP_STATUS_OFERTA_CREDITADA", 3);

/* Valores de Retorno para as URLs de CallBack */
define("EPP_CALLBACK_OK", "1");
define("EPP_CALLBACK_ERROR", "0");

/* Arquivo de Log dos Erros Gerados pelas URLs de Callback */
define("LOG_FILE_OFFER_ERRORS", $raiz_do_projeto . "log/logOfferErrors.log");

/* Path de Includes da Loja Virtual E-Prepag */
define("PATH_INCLUDES", "C:\\Sites\\E-Prepag\\www\\web\\prepag2\\commerce\\includes\\");


/** ==# Matomy - Configurações #== **/

/* Secret Key da Aplicacao denominada 'E-Prepag Matomy Ads - 01' */
define("MATOMY_APP_SECRET_KEY", getenv('MATOMY_APP_SECRET_KEY'));

/** ==# SponsorPay - Configurações #== **/

/* Secret Key da Aplicação denominada 'E-Prepag Ads # 01' */
define("SECURITY_TOKEN_SPONSORPAY", getenv('SECURITY_TOKEN_SPONSORPAY'));

/** ==# Super Rewards - Configurações #== **/

/* Secret Key da Aplicacao denominada 'E-Prepag SRPOINTS Ads #1' */
define("SUPER_REWARDS_APP_SECRET_KEY", getenv('SUPER_REWARDS_APP_SECRET_KEY'));
define("SUPER_REWARDS_API_KEY", getenv('SUPER_REWARDS_API_KEY'));

?>