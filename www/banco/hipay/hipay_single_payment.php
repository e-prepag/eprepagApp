<?php require_once __DIR__ . '/../../includes/constantes_url.php'; ?>
<body style="background-color: rgb(255, 255, 153);" onbeforeunload="">
<?php
	$numcompra = $_REQUEST['numcompra'];
	$amount    = $_REQUEST['amount'];
	
	require_once('hipay_mapi/mapi_package.php');
	require_once('hipay_inc.php');

	// Categoria TI no Hipay
	$HIPAY_CATEGORY = 636;

	//$time_start_stats = getmicrotime();

	// ============================================================
	// a - Payment Parameters
	$params = new HIPAY_MAPI_PaymentParams(); 
	
	//The Hipay platform connection parameters. This is not the information used to connect to your Hipay 
	//account, but the specific login and password used to connect to the payment platform. 
	//The login is the ID of the hipay merchant account receiving the payment, and the password is  
	//the « merchant password » set within your Hipay account. 
	$params->setLogin('26437','401213'); 
	
	// The amounts will be credited to account 59118, except the taxes which will be credited to account 59119 
	$params->setAccounts(26437);
	
	// The payment interface will be in International French by default 
	$params->setDefaultLang('en_US'); // fr_FR	//en_US
	// The interface will be the Web interface 
	$params->setMedia('WEB'); 
	//The order content is intended for people at least 16 years old. 
	$params->setRating('ALL'); 
	// This is a single payment 
	$params->setPaymentMethod(HIPAY_MAPI_METHOD_SIMPLE); 
	// The capture take place immediately 
	$params->setCaptureDay(HIPAY_MAPI_CAPTURE_IMMEDIATE); 
	// The amounts are expressed in Euros, this has to be the same currency as the merchant’s account. 
	$params->setCurrency('EUR'); 
	 
	// The merchant-selected identifier for this order is REF6522 
	$params->setIdForMerchant('26437'); 
	//Two data elements of type key=value are declared and will be returned to the merchant after the payment in the notification data feed [C]. 
	$params->setMerchantDatas('numcompra',$numcompra); 
	//$params->setMerchantDatas('credit','10'); 
	$params->getMerchantDatas(); 
	
	// This order relates to the web site which the merchant declared in the Hipay platform.   
	// The I.D. assigned to this website is ‘9’ 
	$params->setMerchantSiteId(1960);  
	// If the payment is accepted, the user will be redirected to this page 
	//$params->setURLOk('EPREPAG_URL_HTTP/prepag2/pag/hpy/success.php'); 
	$params->setURLOk('' . EPREPAG_URL_HTTP . '/prepag2/pag/hpy/listen_hipay_notification.php'); 
	
	// If the payment is refused, the user will be redirected to this page 
	$params->setUrlNok('' . EPREPAG_URL_HTTP . '/prepag2/pag/hpy/refused.html'); 
	// If the user cancels the payment, he will be redirected to this page  
	$params->setUrlCancel('' . EPREPAG_URL_HTTP . '/prepag2/pag/hpy/cancel.html'); 
	// The email address used to send the notifications, on top of the http notifications. 
	// cf chap 19 : RECEIVING A RESULTS NOTIFICATION ABOUT A PAYMENT ACTION 
	$params->setEmailAck('rene@e-prepag.com.br'); 
	// The merchant’s site will be notified of the result of the payment by a call to the script 
	// “listen_hipay_notification.php” 
	// cf chap 19 : RECEIVING A RESULTS NOTIFICATION ABOUT A PAYMENT ACTION 
	$params->setUrlAck('' . EPREPAG_URL_HTTP . '/prepag2/pag/hpy/listen_hipay_notification.php'); 
	 
	// The background color of the interface will be #FFFFFF (default color recommended) 
	$t=$params->setBackgroundColor('#FFFF99'); 

	// Check everything	
	$t=$params->check();
	if (!$t) {
	    echo "An error occurred while creating the paymentParams object<br>";
	    exit;
	} else {
		//getParameters($params);
	}

	// ============================================================
	// b - Creation of Taxes
/*
	// Tax at 19.6%
	$tax1 = new HIPAY_MAPI_Tax();
	$tax1->setTaxName('TVA (19.6)');
	$tax1->setTaxVal(19.6,true);
	$t=$tax1->check();
	if (!$t) {
	    echo "An error occurred while creating a tax object";
	    exit;
	} else {
		//getTaxes($tax1);
	}
	// Fixed tax of 3.50 euros
	$tax2 = new HIPAY_MAPI_Tax();
	$tax2->setTaxName('Taxe fixe');
	$tax2->setTaxVal(3.5,false);
	$t=$tax2->check();
	if (!$t) {
	    echo "An error occurred while creating a tax object";
	    exit;
	} else {
		//getTaxes($tax2);
	}
	// Tax at 5.5%
	$tax3 = new HIPAY_MAPI_Tax();
	$tax3->setTaxName('TVA (5.5)');
	$tax3->setTaxVal(5.5,true);
	$t=$tax3->check();
	if (!$t) {
	    echo "An error occurred while creating a tax object";
	    exit;
	} else {
		//getTaxes($tax2);
	}
*/
	// ============================================================
	// c. creation of affiliates
	// Affiliate who will receive 10% of all the items in the order
	//$aff1 = new HIPAY_MAPI_Affiliate();
	//$aff1->setCustomerId(331);
	//$aff1->setAccountId(59074);
	//echo "Set HIPAY_MAPI_TTARGET_ALL=".HIPAY_MAPI_TTARGET_ALL."<br>";
	//$aff1->setValue(10.0, HIPAY_MAPI_TTARGET_ALL);
	//$t = $aff1->check();
	//if (!$t) {
	    //echo "An error occurred while creating an affiliate object";
	    //exit;
	//} else {
		//getAff($aff1);
	//}

	//echo "&nbsp;<br>";
	// Affiliate who will receive 15% of the amount of the products, insurance and delivery amounts 
	//$aff2 = new HIPAY_MAPI_Affiliate();
	//$aff2->setCustomerId(332);
	//$aff2->setAccountId(59075);
	//echo "Set HIPAY_MAPI_TTARGET_ITEM=".HIPAY_MAPI_TTARGET_ITEM."<br>";
	//echo "Set HIPAY_MAPI_TTARGET_INSURANCE=".HIPAY_MAPI_TTARGET_INSURANCE."<br>";
	//echo "Set HIPAY_MAPI_TTARGET_SHIPPING=".HIPAY_MAPI_TTARGET_SHIPPING."<br>";
	//$aff2->setValue(15.0, HIPAY_MAPI_TTARGET_ITEM | HIPAY_MAPI_TTARGET_INSURANCE | HIPAY_MAPI_TTARGET_SHIPPING);
	//$t = $aff2->check();
	//if (!$t) {
	   //echo "An error occurred while creating an affiliate object";
	    //exit;
	//} else {
		//getAff($aff2);
	//}

	// ============================================================
	// d. creation of products (order lines)
	// First product: 2 copies of a book at 12.5 Euros per unit on which two taxes are applied 
	//(taxes $tax3 and $tax2)
	$item1 = new HIPAY_MAPI_Product();
	$item1->setName('Compra de Creditos'); 
	$item1->setInfo('E-Prepag'); 
	$item1->setquantity(1); 
	$item1->setRef($numcompra); 
	$item1->setCategory($HIPAY_CATEGORY);
	$item1->setPrice($amount);
	//$item1->setTax(array($tax3,$tax2));
	$t=$item1->check();
	if (!$t) {
	    echo "An error occurred while creating a product object";
	    exit;
	} else {
		//getProduct($item1);
	}

	// Second product: An example of a product at 2360 Euros, on which 3 taxes are applied 
	//($tax1, $tax2 and $tax3)
	//$item2 = new HIPAY_MAPI_Product();
	//$item2->setName('PC Linux'); 
	//$item2->setInfo('Computer 445'); 
	//$item2->setquantity(1); 
	//$item2->setRef('PC445'); 
	//$item2->setCategory($HIPAY_CATEGORY); 
	//$item2->setPrice(2.60);
	//$item2->setTax(array($tax1,$tax2,$tax3));
	//$t=$item2->check();
	if (!$t) {
	    echo "An error occurred while creating a product object";
	    exit;
	} else {
		//getProduct($item2);
	}

	// ============================================================
	// e. creation of the order object 

	$order = new HIPAY_MAPI_Order();
	//  Order title and information
	$order->setOrderTitle('Compra de Creditos');
	$order->setOrderInfo('E-Prepag');
	// The order category is 3 (Books)
	// Refer to annex 7 to see how to find out what category your site belongs to. 
	$order->setOrderCategory($HIPAY_CATEGORY);
	// The shipping costs are 1.50 Euros excluding taxes, and $tax1 is applied
//	$order->setShipping(0,array($tax1));
	// The insurance costs are 2 Euros excluding taxes, and $tax1 and $tax3 are applied
//	$order->setInsurance(0,array($tax3,$tax1));
	// The fixed costs are 2.25 Euros excluding taxes, and $tax3 is applied to this amount
//	$order->setFixedCost(0,array($tax3));
	// This order has two affiliates, $aff1 and $aff2
	$order->setAffiliate(array($aff1,$aff2));
	$t=$order->check();
	if (!$t)
	{
	    echo "An error occurred while creating a product object";
	    exit;
	} else {
		//getOrder($order);
	}

	// ============================================================
	// f. creation of the payment object 

	try {
	 //$commande = new HIPAY_MAPI_SimplePayment($params,$order,array($item1,$item2));
	 $commande = new HIPAY_MAPI_SimplePayment($params,$order,array($item1));
	} catch (Exception $e) {
	 echo "Error" .$e->getMessage();
	}

	// ============================================================
	// g. obtaining the XML representation of this order and sending the feed to the Hipay platform
	$xmlTx=$commande->getXML();
//echo "<hr>XML:<br>'<span style='background-color:#FFFF66'>".$xmlTx."</span>'<hr>";
	//die("Stop");
	$output=HIPAY_MAPI_SEND_XML::sendXML($xmlTx);
//echo "OUTPUT: <br>'<span style='background-color:#66CCFF'>".$output."</span>'<hr>";

	// ============================================================
	// h. Processing the platform's response 

	$r=HIPAY_MAPI_COMM_XML::analyzeResponseXML($output,$URL,$err_msg,$err_keyword, $err_value,$err_code);
	if ($r===true) {
	?>
	<script>
		window.open('<?php echo $URL;?>','_self');
	</script>
	<?php
	  
	 // The internet user is sent to the URL indicated by the Hipay platform header('Location: '.$URL) ;
	 //echo "OK, GOTO: <span style='background-color:#CCFF99'>".$URL."</span><br>";
	} else {
	 // An error message occurs
	 echo "URL='<span style='background-color:#CCFF99'>$URL</span>'<br>\n";
	 echo "err_msg='<span style='background-color:#CCFF99'>$err_msg</span>'<br>\n";
	 echo "err_keyword='<span style='background-color:#CCFF99'>$err_keyword</span>'<br>\n";
	 echo "err_value='<span style='background-color:#CCFF99'>$err_value</span>'<br>\n";
	 echo "err_code='<span style='background-color:#CCFF99'>$err_code</span>'<br>\n";
    } 
?>
</body>