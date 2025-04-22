<?php 
//define o id da transacao
//$transaction_id = $_GET['txn_id'];
//echo $tx_id."<br>";

$transaction_id = $tx_id;

// Obtain information about a specific transaction
$details = $paypal->getTransactionDetails($transaction_id);
//var_dump($details);

// echo '<hr>';
// print_r($details);
// echo '<hr>';
$i = 0;

foreach ($details as $key => $value) { 
	//echo "$key: $value<br>"; 
	//$valor[$i] = $value;
	$i++;
}

$codprepag 	= $details['L_NUMBER0'];
$email		= $details['EMAIL'];
$dataordem 	= $details['ORDERTIME'];
$valor	 	= $details['AMT'];
$pagamento 	= $details['PAYMENTSTATUS'];
$tx_id		= $details['TRANSACTIONID'];
?>
  <tr align="center" bgcolor="#E8E8E8">
    <td><?php echo $codprepag;?></td>
    <td><?php echo $tx_id;?></td>
    <td><?php echo $email;?></td>
    <td><?php echo $dataordem;?></td>
    <td><?php echo $valor;?></td>
    <td><?php echo $pagamento?></td>
  </tr>
<?php
unset($codprepag);
unset($email);
unset($dataordem);
unset($valor);
unset($pagamento);
?>
