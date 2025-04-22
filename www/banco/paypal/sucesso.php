<?php
print_r($_GET);
echo "<hr>";

//$numOrder 		 = $_GET['item_number'];
//$payment_status  = $_GET['payment_status'];
//
//if ($payment_status == 'Completed') { 
//	// aqui gravamos a aprovacao na base de dados
//	echo 'Seu pagamento foi aprovado.';
//} else {
//	// aqui gravamos que o pagamento foi recusado
//	echo 'Lamentamos, mas não foi possível processar seu pagamento.';
//}

// read the post from PayPal system and add 'cmd'
$req = 'cmd=_notify-validate';

foreach ($_GET as $key => $value) {
$value = urlencode(stripslashes($value));
$req .= "&$key=$value";
}

// post back to PayPal system to validate
$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
$fp = fsockopen ('ssl://www.sandbox.paypal.com', 443, $errno, $errstr, 30);

// assign posted variables to local variables
$item_name = $_GET['item_name'];
$item_number = $_GET['item_number'];
$payment_status = $_GET['payment_status'];
$payment_amount = $_GET['mc_gross'];
$payment_currency = $_GET['mc_currency'];
$txn_id = $_GET['txn_id'];
$receiver_email = $_GET['receiver_email'];
$payer_email = $_GET['payer_email'];

if (!$fp) {
// HTTP ERROR
} else {
fputs ($fp, $header . $req);
while (!feof($fp)) {
$res = fgets ($fp, 1024);
if (strcmp ($res, "VERIFIED") == 0) {
// check the payment_status is Completed
// check that txn_id has not been previously processed
// check that receiver_email is your Primary PayPal email
// check that payment_amount/payment_currency are correct
// process payment
echo 'ok';
}
else if (strcmp ($res, "INVALID") == 0) {
// log for manual investigation
echo "erro";
}
}
fclose ($fp);
}
?>