<?php
function getParameters($params) {

	//print_r($params);
	//die;
	
	//echo $params->getIdForMerchant();
	//die;

	echo "<table border='1' cellpadding='0' cellspacing='1' bordercolor='#cccccc' style='border-collapse:collapse;'>\n";
	echo "<tr><td colspan='2' align='center'>Payment Parameters</td></tr>\n";
	echo "<tr><td>Login</td><td align='right'>".$params->getLogin()."</td></tr>\n"; 
	// The amounts will be credited to account 59118, except the taxes which will be credited to account 59119
	echo "<tr><td>getItemAccount</td><td align='right'>".$params->getItemAccount()."</td></tr>\n";
	echo "<tr><td>getTaxAccount</td><td align='right'>".$params->getTaxAccount()."</td></tr>\n";
	echo "<tr><td>getInsuranceAccount</td><td align='right'>".$params->getInsuranceAccount()."</td></tr>\n";
	echo "<tr><td>getFixedCostAccount</td><td align='right'>".$params->getFixedCostAccount()."</td></tr>\n";
	echo "<tr><td>getShippingCostAccount</td><td align='right'>".$params->getShippingCostAccount()."</td></tr>\n";

	// The payment interface will be in International French by default
	echo "<tr><td>getDefaultLang</td><td align='right'>".$params->getDefaultLang()."</td></tr>\n";
	// The interface will be the Web interface
	echo "<tr><td>getMedia</td><td align='right'>".$params->getMedia()."</td></tr>\n";
	//The order content is intended for people at least 16 years old.
	echo "<tr><td>getRating</td><td align='right'>".$params->getRating()."</td></tr>\n";
	// This is a single payment
	echo "<tr><td>getPaymentMethod</td><td align='right'>".$params->getPaymentMethod()."</td></tr>\n";
	// The capture take place immediately
	echo "<tr><td>getCaptureDay</td><td align='right'>".$params->getCaptureDay()."</td></tr>\n";
	// The amounts are expressed in Euros
	echo "<tr><td>getCurrency</td><td align='right'>".$params->getCurrency()."</td></tr>\n";
	// The merchant-selected identifier for this order is 6522
	echo "<tr><td>getIdForMerchant</td><td align='right'>".$params->getIdForMerchant()."</td></tr>\n";

	//Two data elements of type key=value are declared and will be returned to the merchant after the payment in the notification data feed [C].
	echo "<tr><td>getMerchantDatas</td><td align='right'>";
		getMerchartDatas($params->getMerchantDatas());
		echo "</td></tr>\n";

	// This order relates to the web site which the merchant declared in the Hipay platform.  
	// The I.D. assigned to this website is '9'
	echo "<tr><td>getMerchantSiteId</td><td align='right'>".$params->getMerchantSiteId()."</td></tr>\n"; 
	// If the payment is accepted, the user will be asked to consult the page "page.html"
	echo "<tr><td>geturlOk</td><td align='right'>".$params->geturlOk()."</td></tr>\n";
	// The merchant's site will be notified of the result of the payment by a call to the script : "script_reception_marchand.php"
	echo "<tr><td>geturlAck</td><td align='right'>".$params->geturlAck()."</td></tr>\n";
	// The background color of the interface will be #FFFFFF
	echo "<tr><td>getBackgroundColor</td><td align='right'>".$params->getBackgroundColor()."</td></tr>\n";

	echo "</table>\n";
	echo "&nbsp;<br>\n";

}


function getMerchartDatas($merchantdatas) {
	echo "<table border='1' cellpadding='0' cellspacing='1' bordercolor='#cccccc' style='border-collapse:collapse;'>\n";
	echo "<tr><td colspan='2' align='center'>MerchantDatas</td></tr>\n";
	foreach($merchantdatas as $key => $val) {
		echo "<tr><td>$key </td><td align='right'>$val</td></tr>\n"; 
	}	
	echo "</table>\n";
	echo "&nbsp;<br>\n";

}

function getTaxes($tax) {
	echo "<table border='1' cellpadding='0' cellspacing='1' bordercolor='#cccccc' style='border-collapse:collapse;'>\n";
	echo "<tr><td colspan='2' align='center'>Tax</td></tr>\n";
	echo "<tr><td>getTaxName</td><td align='right'>".$tax->getTaxName()."</td></tr>\n"; 
	echo "<tr><td>getTaxVal</td><td align='right'>".$tax->getTaxVal()."</td></tr>\n"; 
	echo "</table>\n";
	echo "&nbsp;<br>\n";

}

function getAff($aff) {
	echo "<table border='1' cellpadding='0' cellspacing='1' bordercolor='#cccccc' style='border-collapse:collapse;'>\n";
	echo "<tr><td colspan='2' align='center'>Affiliate</td></tr>\n";
	echo "<tr><td>getCustomerId</td><td align='right'>".$aff->getCustomerId()."</td></tr>\n"; 
	echo "<tr><td>getAccountId</td><td align='right'>".$aff->getAccountId()."</td></tr>\n";
	echo "<tr><td>getpercentageTarget</td><td align='right'>".decbin($aff->getpercentageTarget())." (".$aff->getpercentageTarget().")</td></tr>\n";
	echo "<tr><td>getAmount</td><td align='right'>".$aff->getAmount()."</td></tr>\n";
	echo "</table>\n";
	echo "&nbsp;<br>\n";

}

function getProduct($item) {

	echo "<table border='1' cellpadding='0' cellspacing='1' bordercolor='#cccccc' style='border-collapse:collapse;'>\n";
	echo "<tr><td colspan='2' align='center'>Product</td></tr>\n";
	echo "<tr><td>getName</td><td align='right'>".$item->getName()."</td></tr>\n"; 
	echo "<tr><td>getInfo</td><td align='right'>".$item->getInfo()."</td></tr>\n"; 
	echo "<tr><td>getquantity</td><td align='right'>".$item->getquantity()."</td></tr>\n"; 
	echo "<tr><td>getRef</td><td align='right'>".$item->getRef()."</td></tr>\n"; 
	echo "<tr><td>getCategory</td><td align='right'>".$item->getCategory()."</td></tr>\n"; 
	echo "<tr><td>getPrice</td><td align='right'>".$item->getPrice()."</td></tr>\n"; 
	echo "<tr><td>getTax</td><td align='right'>";
		foreach($item->getTax() as $tax) {
			getTaxes($tax);
		}
		echo "</td></tr>\n"; 
	echo "</table>\n";
	echo "&nbsp;<br>\n";
}

function getOrder($order) {

	echo "<table border='1' cellpadding='0' cellspacing='1' bordercolor='#cccccc' style='border-collapse:collapse;'>\n";
	echo "<tr><td colspan='2' align='center'>Product</td></tr>\n";
	echo "<tr><td>getOrderTitle</td><td align='right'>".$order->getOrderTitle()."</td></tr>\n"; 
	echo "<tr><td>getOrderInfo</td><td align='right'>".$order->getOrderInfo()."</td></tr>\n"; 
	echo "<tr><td>getOrderCategory</td><td align='right'>".$order->getOrderCategory()."</td></tr>\n"; 

	echo "<tr><td>getShippingAmount</td><td align='right'>".$order->getShippingAmount()."</td></tr>\n"; 
	echo "<tr><td>getShippingTax</td><td align='right'>";
		foreach($order->getShippingTax() as $tax) {
			getTaxes($tax);
		}
		echo "</td></tr>\n"; 
	echo "<tr><td>getInsuranceAmount</td><td align='right'>".$order->getInsuranceAmount()."</td></tr>\n"; 
	echo "<tr><td>getInsuranceTax</td><td align='right'>";
		foreach($order->getInsuranceTax() as $tax) {
			getTaxes($tax);
		}
		echo "</td></tr>\n"; 
	echo "<tr><td>getFixedCostAmount</td><td align='right'>".$order->getFixedCostAmount()."</td></tr>\n"; 
	echo "<tr><td>getFixedCostTax</td><td align='right'>";
		foreach($order->getFixedCostTax() as $tax) {
			getTaxes($tax);
		}
		echo "</td></tr>\n"; 
	echo "<tr><td>getAffiliate</td><td align='right'>";
		foreach($order->getAffiliate() as $aff) {
			getAff($aff);
		}
		echo "</td></tr>\n"; 

	echo "</table>\n";
	echo "&nbsp;<br>\n";
}

//function getmicrotime()
//{
//   list($usec, $sec) = explode(" ", microtime());
//   return ((float)$usec + (float)$sec);
//}

?>