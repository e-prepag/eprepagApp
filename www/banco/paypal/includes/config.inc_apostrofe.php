<?php
/*
 * config.inc.php
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
//Configuration Settings
// echo "<pre>";
// print_r($_GET);
// echo "</pre><hr>";

//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
//die("Stop");

$paypal['business']=$_GET['business'];
$paypal['site_url']="http://www.e-prepag.com.br";
$paypal['image_url']="/eprepag/imgs/home/logo.gif";
$paypal['success_url']="/prepag2/pag/pay/ipn/paypal_ipn.php";
$paypal['cancel_url']="/prepag2/pag/pay/paypal_error.php";
$paypal['notify_url']="/prepag2/pag/pay/ipn/paypal_ipn.php";
$paypal['return_method']="2"; //1=GET 2=POST

if(empty($_GET['currency'])) {
	$paypal['currency_code']="BRL"; //[USD,GBP,JPY,CAD,EUR]
	$paypal['lc']="BR";
} else {
	if($_GET['currency'] != 'BRL') {
		$paypal['currency_code']= 'USD'; //[USD,GBP,JPY,CAD,EUR]
		$paypal['lc']="US";
	} else {
		$paypal['currency_code']= 'BRL'; //[USD,GBP,JPY,CAD,EUR]
		$paypal['lc']="BR";
	}
}

//$paypal['url']="http://www.paypal.com/cgi-bin/webscr";
//$paypal['url']="https://www.paypal.com/cgi-bin/webscr";
$paypal['url']="https://www.sandbox.paypal.com/cgi-bin/webscr";
$paypal['post_method']="libCurl"; //fso=fsockopen(); curl=curl command line libCurl=php compiled with libCurl support
$paypal['curl_location']="/usr/local/bin/curl";

//$paypal['bn]="toolkit-php";
$paypal['bn']=$_GET['bn'];
$paypal['cmd']="_xclick";

//Payment Page Settings
$paypal['display_comment']="0"; //0=yes 1=no
$paypal['comment_header']="Comments";
$paypal['button_subtype']=$_GET['button_subtype'];
$paypal['continue_button_text']="Continue >>";
$paypal['background_color']=""; //""=white 1=black
$paypal['display_shipping_address']=""; //""=yes 1=no
$paypal['display_comment']="1"; //""=yes 1=no
$paypal['no_note']=$_GET['no_note']; //""=yes 1=no



//Product Settings
$paypal['item_name']="$_GET[item_name]";
$paypal['item_number']="$_GET[item_number]";
$paypal['amount']="$_GET[amount]";
$paypal['on0']="$_GET[on0]";
$paypal['os0']="$_GET[os0]";
$paypal['on1']="$_GET[on1]";
$paypal['os1']="$_GET[os1]";
$paypal['quantity']="$_GET[quantity]";
$paypal['edit_quantity']=""; //1=yes ""=no
$paypal['invoice']="$_GET[invoice]";
$paypal['tax']="$_GET[tax]";

//Shipping and Taxes
$paypal['no_shipping']="$_GET[no_shipping]";
$paypal['shipping_amount']="$_GET[shipping_amount]";
$paypal['rm']="$_GET[rm]";
$paypal['shipping_amount_per_item']="";
$paypal['handling_amount']="";
$paypal['custom_field']="";

//Customer Settings
$paypal['firstname']="$_GET[firstname]";
$paypal['lastname']="$_GET[lastname]";
$paypal['address1']="$_GET[address1]";
$paypal['address2']="$_GET[address2]";
$paypal['city']="$_GET[city]";
$paypal['state']="$_GET[state]";
$paypal['zip']="$_GET[zip]";
$paypal['email']="$_GET[email]";
$paypal['phone_1']="$_GET[phone1]";
$paypal['phone_2']="$_GET[phone2]";
$paypal['phone_3']="$_GET[phone3]";

// echo "<pre>";
// print_r($paypal);
// echo "</pre><hr>";
// die('stop');

?>