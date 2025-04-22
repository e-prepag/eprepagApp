<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

// include do arquivo contendo IPs DEV
require_once RAIZ_DO_PROJETO . 'includes/configIP.php';

//Credenciais
//Os dados abaixo sуo credenciais de autenticaчуo e sуo fornecidos pela norton
define("NORTON_VENDOR_ID", "1021980");
define("NORTON_VENDOR_PW", "o^tw5n)x1");
define("NORTON_VENDOR_SITE_ID", "996019643");
//

//Url's
define("NORTON_WSDL_VERSION", "202042"); //Esta informaчуo serс fornecida pelo seu Integration Manager
define("NORTON_WSDL_URL", "https://cebe-int.norton.com/entitlement/EntitlementService?wsdl"); //Url do WSDL
define("NORTON_URL", "https://cebe-int.norton.com/entitlement/EntitlementService"); //Url do serviчo
//

//Operaчѕes
define("NORTON_ELETRONIC_PURCHASE", "electronicPurchase");
define("NORTON_REFUND_TRANSACTION", "refundTransactions");
//

//Opчѕes Diversas
define("NORTON_LANG", "ENG"); //Linguagem utilizada nas transaчѕes
define("NORTON_CURRENCY", "USD"); //Moeda utilizada nas transaчѕes
define("NORTON_AUTO_RENEWALL", "ACTIVE_OPT_OUT");
define("NORTON_RENEWALL_CONTEXT", "ESTORE_SERVICE");
//

//Produto
define("NORTON_PRODUCT_SKU", "21416528");

//LOG's
define("NORTON_ERROR_LOG", "ERROR_LOG");
define("NORTON_ERROR_LOG_FILE", RAIZ_DO_PROJETO . "log/log_NORTON_WS-Errors.log");

define("NORTON_ELETRONIC_PURCHASE_LOG", "ELETRONIC_PURCHASE_LOG");
define("NORTON_ELETRONIC_PURCHASE_LOG_FILE", RAIZ_DO_PROJETO . "log/log_NORTON_WS-Eletronic_purchase.log");

define("NORTON_REFUND_TRANSACTION_LOG", "REFUND_TRANSACTION_LOG");
define("NORTON_REFUND_TRANSACTION_LOG_FILE", RAIZ_DO_PROJETO . "log/log_NORTON_WS-Refund_transaction.log");
//





