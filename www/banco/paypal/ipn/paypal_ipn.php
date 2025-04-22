<?php
/*
 * paypal_ipn.php
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
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
 
  // print_r($_POST);
  // echo '<hr>';
  // die('se murio');

require_once("C:/Sites/E-Prepag/www/web/incs/inc_register_globals.php");	

//get global configuration information
include_once('../includes/global_config.inc.php'); 

//get pay pal configuration file
include_once('../includes/config.inc.php'); 


grava_LOG_Paypal(str_repeat("*", 80)."\nEm pag/pay/ipn/paypal_ipn.php", print_r($_POST,true));

?>
<div id="icon_loader" name="icon_loader">
<img src="/images/AjaxLoadingIcon.gif" width="75" height="75" border="0" title="Aguarde...">
</div>
<?php
/*
echo "post_method: ".$paypal['post_method']."<br>";
echo "url: ".$paypal['url']."<br>";
echo "<br>";

echo "mc_gross: ".$_POST['mc_gross']."<br>";
echo "invoice: ".$_POST['invoice']."<br>";
echo "payment_status: ".$_POST['payment_status']."<br>";
echo "item_number: ".$_POST['item_number']."<br>";
echo "quantity: ".$_POST['quantity']."<br>";
echo "payment_date: ".$_POST['payment_date']."<br>";
echo "txn_id: ".$_POST['txn_id']."<br>";
//echo "<pre>".print_r($paypal,true)."</pre>";
*/

//echo "<hr>_POST Array<pre>".print_r($_POST,true)."</pre><hr>";
//decide which post method to use
switch($paypal[post_method]) {
	case "libCurl": //php compiled with libCurl support
//echo "libCurl - paypal[post_method]: '".$paypal[post_method]."'<br>";
//die("Stop A");

		// echo 'libCurl<hr>';
		$result=libCurlPost($paypal[url],$_POST); 
		// echo $result."<hr>";
		break;
	case "curl": //cURL via command line
echo "curl - paypal[post_method]: '".$paypal[post_method]."'<br>";
//die("Stop B");
		$result=curlPost($paypal[url],$_POST); 
		break; 
	case "fso": //php fsockopen(); 
echo "fso - paypal[post_method]: '".$paypal[post_method]."'<br>";
//die("Stop Cc");
		// echo 'fso<hr>';
		$result=fsockPost($paypal[url],$_POST); 
		// echo $result."<hr>";
		break; 
	default: //use the fsockopen method as default post method
//echo "default - paypal[post_method]: '".$paypal[post_method]."'<br>";
//die("Stop D");
		if(strlen($paypal[post_method])>0) {
			if(strlen($paypal[url])>0) {
				$result=fsockPost($paypal[url],$_POST);
			} else {
				echo "ERROR in paypal_ipn.php - paypal[post_method]: '".$paypal[post_method]."', Sem paypal[url]\n";
			}
		} else {
			echo "ERROR in paypal_ipn.php - Sem paypal[post_method], Sem paypal[url]\n";
		}
		break;
}

?>
<script language="JavaScript">
	document.getElementById("icon_loader").innerHTML = "Consulta finalizada";
</script>

<?php
//echo "<hr>Resultado do '".$paypal[post_method]."':<pre>".print_r($result,true)."</pre><hr color='red'>";


// echo '<pre>'.print_r($$result,true).'</pre>';
// die;


//check the ipn result received back from paypal

//echo "VERIFIED: ".ereg("VERIFIED",$result);
//echo '<br>';
//die('stop');

if(ereg("VERIFIED",$result)) { 
	include_once('./paypal_ipn_success.php'); 
} else { 
	include_once('./paypal_ipn_error.php'); 
} 
//echo "<pre>".$GLOBALS."</pre>";
?>