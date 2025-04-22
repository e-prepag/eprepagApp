<?php 
die();require_once '../../includes/constantes.php'; ?>
<?php  require_once $raiz_do_projeto."includes/main.php"; ?>
<head>
<script language="JavaScript">
<!--
function send_to_notify(url_notify, store_id, order_id, amount, currency_code, transaction_id, client_id, client_email) {

	document.billing.target = '_blank';

	document.billing.action = url_notify;
	document.billing.store_id.value = store_id;
	document.billing.order_id.value = order_id;
	document.billing.amount.value = amount;
	document.billing.currency_code.value = currency_code;
	document.billing.transaction_id.value = transaction_id;
	document.billing.client_id.value = client_id;
	document.billing.client_email.value = client_email;
//alert('document.billing.transaction_id.value: '+document.billing.transaction_id.value);
	document.billing.submit();
}
//-->
</script>
</head>

<style>
body, p, a, td, th, b {font-family: tahoma, arial;font-size:10}
input.main, select.main {color:#0000CC;background-color:#FFFFCC; text-align:right}
</style>

<?php

// Flow - step 2
// Payment request
// Aerial -> EPP

// error_reporting(E_ALL); 
// ini_set("display_errors", 1); 

//echo "<pre>".print_r($_POST,true)."</pre>";
$dd_partner_id = $_POST['dd_partner_id'];
$dd_phrase = $_POST['dd_phrase'];
//echo "dd_phrase: $dd_phrase<br>";
$valid_user = ($dd_phrase=="quency")?"OK":"";

//$id_partner = get_Integracao_origem();
//echo "valid_user: $valid_user<br>";

?>
<body>
<?php
if($valid_user=="OK") {

	//parceiros
	$sql  = "select distinct ip_store_id as parceiro, count(*) as n, ".getPartner_Names_SQL()." from tb_integracao_pedido group by ip_store_id order by opr_nome, ip_store_id;";
//echo "sql: $sql<br>";
	$rs_parceiros = SQLexecuteQuery($sql);
}

//if($dd_partner_id) {
if($valid_user=="OK") {

	$url_notify = getPartner_notify_url_By_ID($dd_partner_id);

?>

<h3><font face="arial, sans serif"><?php echo getPartner_name_By_ID($dd_partner_id)." ($id_partner)" ?> - Return from E-Prepag</font></h3>

<p><font face="arial, sans serif"><a href="<?php echo getPartner_param_By_ID('partner_url', $dd_partner_id) ?>">Go to the initial test page for <?php echo getPartner_name_By_ID($dd_partner_id) ?></a></font></p>
<p><font face="arial, sans serif">Return from a payment request</font></p>
  
<?php //echo "<p><font face='arial, sans serif'>Notify URL: ".$url_notify."</font></p>"; ?>

 <p><font face="arial, sans serif"><?php echo date("Y-m-d H:i:s") ?></font></p>
<form method=post action="partner_return_all.php">
<?php
if($valid_user=="OK") {
?>
Partner: 
	<select name="dd_partner_id" class="form2">
		<option value="" <?php if($tf_ip_store_id == "") echo "selected" ?>>Selecione</option>
		<?php if($rs_parceiros) while($rs_parceiros_row = pg_fetch_array($rs_parceiros)){ ?>					
		<option value="<?php echo $rs_parceiros_row['parceiro']; ?>" <?php if ($dd_partner_id == $rs_parceiros_row['parceiro']) echo "selected";?>><?php echo getPartner_name_By_ID($rs_parceiros_row["parceiro"])." (ID: ".$rs_parceiros_row["parceiro"].") ".$rs_parceiros_row["n"]." registro".(($rs_parceiros_row["n"]>1)?"s":"")." "; ; ?></option>
		<?php } ?>
	</select><br>
<?php
 }
?>
<input type="hidden" name="dd_phrase" id="dd_phrase" value="<?php echo $dd_phrase ?>">
<input type="submit" value="Atualiza">
</form>

<?php

	getPartner_payments_list($dd_partner_id);

} else {

?>
<h3 style='color:red'>Unknown user (partner)</h3>
<form method=post action="partner_return_all.php">
<?php
	echo "Partner: ".$s_select."<br>";
?>
<input type="text" name="dd_phrase" id="dd_phrase" value="">
<input type="submit" value="manda">
</form>
<?php

} 

?>
</body>