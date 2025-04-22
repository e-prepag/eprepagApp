<?php
// Include API credentials
include('./php/credentials.php');

// Include and instantiate the API
include('./PayPal.class.php');
$paypal = new PayPal('SANDBOX', $username, $password, $signature);
?>
<table width="100%" border="0" cellspacing="1" cellpadding="1">
  <tr align="center" valign="top">
    <td>CÓD. E-PREPAG</td>
    <td>TX ID</td>
    <td>E-MAIL</td>
    <td>DATA</td>
    <td>VALOR</td>
    <td>STATUS</td>
  </tr>
<form action="index.php" method="post" name="fsearch" id="fsearch">
</form>
<hr />
<?php
if(empty($_POST)) {
	$email 	 		= '';
	$startDateStr 	= date('m/d/Y');
	$endDateStr		= date('m/d/Y');
	$invnum			= '20110120125541877';
	include('./php/TransactionSearch.php');
} else {
}
?>
</table>
<hr />