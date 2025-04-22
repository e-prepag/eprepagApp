<?php

/** TransactionSearch NVP example; last modified 08MAY23.
 *
 *  Search your account history for transactions that meet the criteria you specify. 
*/

//require_once('credentials.php');

$environment = 'sandbox';	// or 'beta-sandbox' or 'live'

/**
 * Send HTTP POST Request
 *
 * @param	string	The API method name
 * @param	string	The POST Message fields in &name=value pair format
 * @return	array	Parsed HTTP Response body
 */
function PPHttpPost($methodName_, $nvpStr_) {
	global $environment;

	// Set up your API credentials, PayPal end point, and API version.
	$API_UserName = urlencode('renebm_1291837586_biz_api1.gmail.com');
	$API_Password = urlencode('1291837595');
	$API_Signature = urlencode('AkF1l4NDN1hlU.roQslK4ZX6UVl.AeAbVq4aUjQIDlY7pxTx00h9a-rK');
	$API_Endpoint = "https://api-3t.paypal.com/nvp";
	if("sandbox" === $environment || "beta-sandbox" === $environment) {
		$API_Endpoint = "https://api-3t.$environment.paypal.com/nvp";
	}
	$version = urlencode('51.0');

	// Set the curl parameters.
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $API_Endpoint);
	curl_setopt($ch, CURLOPT_VERBOSE, 1);

	// Turn off the server and peer verification (TrustManager Concept).
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);

	// Set the API operation, version, and API signature in the request.
	$nvpreq = "METHOD=$methodName_&VERSION=$version&PWD=$API_Password&USER=$API_UserName&SIGNATURE=$API_Signature$nvpStr_";

	// Set the request as a POST FIELD for curl.
	curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);

	// Get response from the server.
	$httpResponse = curl_exec($ch);

	if(!$httpResponse) {
		exit("$methodName_ failed: ".curl_error($ch).'('.curl_errno($ch).')');
	}

	// Extract the response details.
	$httpResponseAr = explode("&", $httpResponse);

	$httpParsedResponseAr = array();
	foreach ($httpResponseAr as $i => $value) {
		$tmpAr = explode("=", $value);
		if(sizeof($tmpAr) > 1) {
			$httpParsedResponseAr[$tmpAr[0]] = $tmpAr[1];
		}
	}

	if((0 == sizeof($httpParsedResponseAr)) || !array_key_exists('ACK', $httpParsedResponseAr)) {
		exit("Invalid HTTP Response for POST request($nvpreq) to $API_Endpoint.");
	}

	return $httpParsedResponseAr;
}

// Set request-specific fields.
//$email = ('renebj_1291837704_per@gmail.com');
//$invnum = '20110120125541877';
$nvpStr = "&INVNUM=$invnum";


// Add request-specific fields to the request string.
if(!empty($email)) {
	$nvpStr = "&EMAIL=$email";
}

// Set additional request-specific fields and add them to the request string.
//$startDateStr = '01/20/2011';			// in 'mm/dd/ccyy' format
//$endDateStr = '01/20/2011';			// in 'mm/dd/ccyy' format

if(isset($startDateStr)) {
   $start_time = strtotime($startDateStr);
   $iso_start = date('Y-m-d\T00:00:00\Z',  $start_time);
   $nvpStr .= "&STARTDATE=$iso_start";
  }

if(isset($endDateStr)&&$endDateStr!='') {
   $end_time = strtotime($endDateStr);
   $iso_end = date('Y-m-d\T24:00:00\Z', $end_time);
   $nvpStr .= "&ENDDATE=$iso_end";
}

// Execute the API operation; see the PPHttpPost function above.
$httpParsedResponseAr = PPHttpPost('TransactionSearch', $nvpStr);

if("SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"])) {
	// echo '<pre>';
	// print_r($httpParsedResponseAr);
	// echo '</pre>';
	// echo '<hr>';
	
	$itensqtd = 0;
	foreach ($httpParsedResponseAr as $key => $value) { 
		$itensqtd++;
		$chave[] = $key;
		$valor[] = $value;
	}

	$itensqtd = ($itensqtd-5);
	
	for ($dados = 0; $dados <= $itensqtd-1; $dados++) {
		if($valor[$dados] == 'Payment') {
			$marcaposicao[] = $chave[$dados]; 
		}
	}	
	
    for ($dados = 0; $dados <= count($marcaposicao)-1; $dados++) {
		$novodado = str_replace('L_TYPE','L_TRANSACTIONID',$marcaposicao[$dados]);
		
		$tx_id = $httpParsedResponseAr[$novodado];
		include('getTransactionDetails.php');
		
		unset($tx_id);
	}
	//exit('TransactionSearch Completed Successfully: <br><pre>'.print_r($httpParsedResponseAr, true).'</pre></hr>');
} else  {
	exit('TransactionSearch failed: ' . print_r($httpParsedResponseAr, true));
}
?>