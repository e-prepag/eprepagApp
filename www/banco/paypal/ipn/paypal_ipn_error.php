<?php
/*
 * paypal_ipn_error.php
 *
 * PHP Toolkit for PayPal v0.51
 * http://www.paypal.com/pdn
 *
 * Copyright (c) 2004 PayPal Inc
 *
 * Released under Common Public License 1.0
 * http://opensource.org/licenses/cpl.php
 *
 */

// this is an include file - no functionality when
// called directly
// include_once('./connec.php');
 
  // echo 'paypal: ';
  // print_r($paypal);
  // echo "<hr>"; 

    // echo '<pre>post: ';
    // print_r($_POST);
    // echo "</pre><hr>";
/*  
  $i = 0;
  foreach ($_POST as $key => $value) { 
	//echo "$key: $value<br>"; 
	$valor[$i] = $value;
//echo "valor[$i]: ".$valor[$i]."<br>";
	$i++;
  }
*/
/*
echo '<hr>';
echo "<pre>".print_r($valor,true)."</pre>";
echo '<hr>';
*/
//echo "paypal Array<pre>".print_r($paypal,true)."</pre><hr color='blue'>";

//  $sql = "SELECT txn_id FROM paypal WHERE txn_id = '$_POST[txn_id]'";
//  $rss = mysql_query($sql, $fd_conn) or die($sql);
//  $tot = mysql_num_rows($rss);
  
    // echo $sql;
    // echo '<hr>';
    // echo $tot;
    // echo '<hr>';

    // die('stop');
 
 
//include file - not accessible directly
if(isset($paypal['business']))
{
//log error transaction to file or database
  if($tot == 0) {
/*
	  $sql = "INSERT INTO paypal        
	  (
		 idTransacao, 
		 mc_gross, 
		 settle_amount, 
		 protection_eligibility, 
		 address_status, 
		 payer_id, 
		 tax, 
		 address_street, 
		 payment_date, 
		 payment_status, 
		 charset, 
		 address_zip, 
		 first_name, 
		 mc_fee, 
		 address_country_code, 
		 address_name, 
		 exchange_rate, 
		 notify_version, 
		 custom, 
		 settle_currency, 
		 payer_status, 
		 business, 
		 address_country, 
		 address_city, 
		 quantity, 
		 payer_email, 
		 verify_sign, 
		 payment_type, 
		 txn_id, 
		 last_name, 
		 receiver_email, 
		 address_state, 
		 payment_fee, 
		 receiver_id, 
		 txn_type, 
		 item_name, 
		 mc_currency, 
		 item_number, 
		 residence_country, 
		 test_ipn, 
		 transaction_subject, 
		 handling_amount, 
		 payment_gross, 
		 shipping, 
		 merchant_return_link, 
		 retorno_ipn	  
	  )
	  
	  
	  VALUES (
										'', 
										'$_POST[mc_gross]', 
										'$_POST[settle_amount]', 
										'$_POST[protection_eligibility]', 
										'$_POST[address_status]', 
										'$_POST[payer_id]', 
										'$_POST[tax]', 
										'$_POST[address_street]', 
										'$_POST[payment_date]', 
										'$_POST[payment_status]', 
										'$_POST[charset]', 
										'$_POST[address_zip]', 
										'$_POST[first_name]', 
										'$_POST[mc_fee]', 
										'$_POST[address_country_code]', 
										'$_POST[address_name]', 
										'$_POST[exchange_rate]', 
										'$_POST[notify_version]', 
										'$_POST[custom]', 
										'$_POST[settle_currency]', 
										'$_POST[payer_status]', 
										'$_POST[business]', 
										'$_POST[address_country]', 
										'$_POST[address_city]', 
										'$_POST[quantity]', 
										'$_POST[payer_email]', 
										'$_POST[verify_sign]', 
										'$_POST[payment_type]', 
										'$_POST[txn_id]', 
										'$_POST[last_name]', 
										'$_POST[receiver_email]', 
										'$_POST[address_state]', 
										'$_POST[payment_fee]', 
										'$_POST[receiver_id]', 
										'$_POST[txn_type]', 
										'$_POST[item_name]', 
										'$_POST[mc_currency]', 
										'$_POST[item_number]', 
										'$_POST[residence_country]', 
										'$_POST[test_ipn]', 
										'$_POST[transaction_subject]', 
										'$_POST[handling_amount]', 
										'$_POST[payment_gross]', 
										'$_POST[shipping]', 
										'$_POST[merchant_return_link]', 
										'INVALID' 
										)";
	$rss = mysql_query($sql, $fd_conn) or die($sql);
*/
  }
  include('../paypal_unverified.php');
  // echo "<pre>".$sql."</pre>";
  // echo '<hr>';
  // die('stop');
}
else
{
	die('Acesso direto negado (2)');
}
?>