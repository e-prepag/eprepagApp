<?php
include('connec.php');
//print_r($_POST);
//echo "<hr>";

$dia = $_POST['dia'];

if(empty($dia)) {
	$data = date("M d, Y");
} else {
	$mes = date("M ");
	$dia  = $_POST['dia'];
	
	if(strlen($dia) == 1) {
		$dia = '0'.$dia;
	}
	
	
	$ano  = date(", Y");
	$data = $mes.$dia.$ano;
}

echo 'Data para a listagem de compras: '.$data;
?>
<script src="SpryAssets/SpryValidationTextField.js" type="text/javascript"></script>
<link href="SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css" />

<form action="index.php" method="post">
	Escolha o dia para a busca no mes de janeiro:
	<span id="sprytextfield1">
    <input name="dia" type="text" id="dia" size="3" maxlength="2" />
    <span class="textfieldRequiredMsg">A value is required.</span><span class="textfieldInvalidFormatMsg">Invalid format.</span><span class="textfieldMaxCharsMsg">Exceeded maximum number of characters.</span><span class="textfieldMinValueMsg">The entered value is less than the minimum required.</span><span class="textfieldMaxValueMsg">The entered value is greater than the maximum allowed.</span></span> <br />
    <input name="listar" id="listar" type="submit" value="Listar" />
<form>
<script type="text/javascript">
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1", "integer", {maxChars:2, minValue:1, maxValue:31, useCharacterMasking:true});
//-->
</script>
<hr />
<?php
$sql = "SELECT * FROM paypal WHERE payment_date LIKE '%$data%'";
$rss = mysql_query($sql, $fd_conn) or die($sql);
$tot = mysql_num_rows($rss);

if($tot <1) {
	echo 'Não existem dados para essa consulta';
} else {
	echo "Venda -> Nome/Sobrenome email / valor / datapagamento / status / SONDA<hr>";
	while($vlr = mysql_fetch_array($rss)) {
		$first_name = $vlr['first_name'];
		$last_name	= $vlr['last_name'];
		$txn_id		= $vlr['txn_id'];
		$email		= $vlr['receiver_email'];
		$valor		= $vlr['mc_gross'];
		$status		= $vlr['payment_status'];
		$datapagto  = $vlr['payment_date'];
		$vendanum   = $vlr['item_number'];
		
		
		echo $vendanum." -> ".$first_name." ".$last_name." ".$email." / ".$valor." / ".$datapagto." -> ".$status;
		echo " <a href='./php/getTransactionDetails.php?txn_id=".$txn_id."' target='_blank'>(Verificar Pagamento)</a> ";
		echo "<br>";
	}
}



unset($_POST);
$_POST = array();
unset($dia);
?>