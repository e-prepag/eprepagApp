<?php
	require_once('hipay_mapi/mapi_package.php');
	require_once('hipay_inc.php');
 
	$f_log_hipay = "C:/Sites/E-Prepag/backoffice/offweb/tarefas/log/LOG_pagtos_hipay.txt";
 	// Receipt of the notification from the Hipay platform 
	// The XML feed [C] is sent by POST, in the field "xml" 
	// The function analyzeNotificationXML processes the XML feed from the Hipay platform 
	$r=HIPAY_MAPI_COMM_XML::analyzeNotificationXML($_POST['xml'], $operation, $status, $date, $time, 
	$transid, $amount, $currency, $idformerchant, $merchantdatas,  $emailClient, $subscriptionId, $refProduct); 
	
//	print_r($_REQUEST);
//	echo '<hr>';
	
	
//	echo $zt;
	
//	echo '<hr>';
//	die('stop');
	
	
	// An error occurs 
	if ($r===false) { 
	 // Error log, in a text file on the server 
		file_put_contents($f_log_hipay, str_repeat("-", 80)."\n".date("Y-m-d H:i:s")."\n Erro\n\n", FILE_APPEND|LOCK_EX); 
	} else { 
	// The feed was processed 
	// Here, the merchant can update his database for orders etc. 
		file_put_contents($f_log_hipay, 
						str_repeat("-", 80)."\n".date("Y-m-d H:i:s")."\n
	operation=$operation\n 
	status=$status\n 
	date=$date\n 
	time=$time\n 
	transaction_id=$transid\n 
	amount=$amount\n 
	currency=$currency\n 
	idformerchant=$idformerchant\n 
	merchantData=". print_r($merchantdatas,true)."\n 
	emailClient=$emailClient\n 
	subscriptionId=$subscriptionId\n 
	refProduct=".print_r($refProduct,true)."\n\n", 
							FILE_APPEND|LOCK_EX); 
	} 

	//Procesa retorno


?>