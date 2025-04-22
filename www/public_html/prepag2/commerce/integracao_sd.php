<?php  
header("Content-Type: text/html; charset=ISO-8859-1",true);
require_once "../../../includes/constantes.php";
require_once DIR_INCS . "main.php";
require_once DIR_INCS . "gamer/main.php";
require_once DIR_CLASS . "gamer/classIntegracao.php"; 

?>
<style>
body, p, a, td, th, b {font-family: tahoma, arial;font-size:10}
input.main, select.main {color:#0000CC;background-color:#FFFFCC; text-align:right}
</style>
<?php


//die("Stop");


// Define Stardoll Integration
$INTEGRACAO_STORE_ID = "10406";

// Flow - step 2
// Payment request
// aeria -> EPP

// error_reporting(E_ALL);
// ini_set("display_errors", 1);

if($_SERVER['HTTPS']=="on") {
//	echo "HTTPS está correto<br>";
//echo "<pre>";
//print_r($_SERVER);
//echo "</pre>";

} else {
	echo "Esta página funciona apenas com HTTPS<br>";
	die("Stop");
}

$send_to_url = $INTEGRACAO_URL_EPP_GATEWAY;

$partner_params = array(
	"store_id" => $INTEGRACAO_STORE_ID,
//	"return_url" => "http://www.e-prepag.com.br/prepag2/commerce/partner_return.php",
//	"notify_url" => "https://www.e-prepag.com.br/prepag2/commerce/partner_notify.php",
	"currency_code" => getPartner_param_By_ID('partner_currency_code', $INTEGRACAO_STORE_ID),
	"order_id" => get_random_order_id(),
//	"transaction_id" => get_random_order_id(),
	"order_description" => "Mensalidade - jogos",
	"amount" => "2000",

	"client_email" => "gamer_test@hotmail.com",
	"client_id" => "32233223",
	"client_name" => "Joe Smith",
	"client_zip_code" => "05418000",
	"client_street" => "R. Dep. Lacerda Franco",
	"client_suburb" => "Pinheiros",
	"client_number" => "300",
	"client_city" => "São Paulo",
	"client_state" => "SP",
	"client_country" => "Brasil",
	"client_telephone" => "32233223",
	"language" => "PT",
	);

	$url_notify = getPartner_notify_url_By_ID($INTEGRACAO_STORE_ID);
	$partner_produto_id = getPartner_param_By_ID('partner_produto_id', $INTEGRACAO_STORE_ID);

	$sql = "select ogpm_id, ogpm_pin_valor
			from tb_operadora_games_produto_modelo ogpm
			where ogpm.ogpm_ogp_id IN (".$partner_produto_id. ")";
//echo "$sql<br>";
	$s_select_amount = "<select id='amount' name='amount' class='main'>\n";
	$s_select_amount .= "<option value='Escolha um valor'".(($partner_params["amount"]=="Escolha um valor")?" selected":"").">Escolha um valor</option>\n";
//	$s_select_amount .= "<option value='91'".(($partner_params["amount"]=="91")?" selected":"").">R$91,00 - Wrong value (for testing)</option>\n";
	$rs = SQLexecuteQuery($sql);
	if($rs && pg_num_rows($rs) != 0){
		while ($rs_row = pg_fetch_array($rs)){
			$s_select_amount .= "<option value='".(100*$rs_row['ogpm_pin_valor']) ."'".(($partner_params["amount"]==(100*$rs_row['ogpm_pin_valor']))?" selected":"").">R$".$rs_row['ogpm_pin_valor'] .",00</option>\n";
		}
	}
	$s_select_amount .= "</select>\n";

	$s_select_language = "<select id='language' name='language' class='main'>\n";
	$s_select_language .= "<option value='PT'".(($partner_params["language"]=="PT")?" selected":"").">PT - Português</option>\n";
	$s_select_language .= "<option value='ES'".(($partner_params["language"]=="ES")?" selected":"").">ES - Español</option>\n";
	$s_select_language .= "<option value='EN'".(($partner_params["language"]=="EN")?" selected":"").">EN - English</option>\n";
	$s_select_language .= "</select>\n";

	$s_select_store_id = "<select id='store_id' name='store_id' class='main'>\n";
	$s_select_store_id .= "<option value='".$INTEGRACAO_STORE_ID."'".(($partner_params["store_id"]==$INTEGRACAO_STORE_ID)?" selected":"").">".$INTEGRACAO_STORE_ID." - ".getPartner_name_By_ID($INTEGRACAO_STORE_ID)."</option>\n";
	$s_select_store_id .= "<option value='20000'".(($partner_params["store_id"]=="20000")?" selected":"").">20000 - Non-existing Games</option>\n";
	$s_select_store_id .= "</select>\n";

	// Obtem o order_id do último registro solicitado para simular um pedido a partner_notify
	$sql = "SELECT * from
				(
					select ip_order_id as ip_order_id_selected
					FROM tb_integracao_pedido ip
					WHERE ip.ip_store_id = '".$partner_params["store_id"]."' and ip_status_confirmed=1
					ORDER BY ip_data_inclusao DESC
					limit 1
				) i1,  coalesce(
				(
					select ip_order_id as ip_order_id_not_selected
					FROM tb_integracao_pedido ip
						LEFT OUTER JOIN tb_venda_games vg ON ip.ip_vg_id = vg.vg_id
					WHERE ip.ip_store_id = '".$partner_params["store_id"]."' and ip_status_confirmed=0 and vg_ultimo_status=5
					ORDER BY ip_data_inclusao DESC
					limit 1
				) , '-1') as ip_order_id_not_selected
			";

// 				left outer join tb_venda_games vg on ip.ip_vg_id = vg.vg_id
//				and ip.ip_currency_code = '".$partner_params["currency_code"]."'
//				and ip.ip_amount  = '".$partner_params["amount"]."'
//				and ip.ip_client_email = '".$partner_params["client_email"]."'

//echo str_replace("\n", "<br>\n", $sql)."<br>";
	$rs = SQLexecuteQuery($sql);
	if($rs && pg_num_rows($rs) != 0){
		$rs_row = pg_fetch_array($rs);
		$ip_order_id_selected = $rs_row['ip_order_id_selected'];
		$ip_order_id_not_selected = $rs_row['ip_order_id_not_selected'];
	}
//echo "ip_order_id_selected: $ip_order_id_selected<br>";
//echo "ip_order_id_not_selected: $ip_order_id_not_selected<br>";
if($ip_order_id_not_selected>0) {
	$order_id_last = $ip_order_id_not_selected;
} else {
	$order_id_last = $ip_order_id_selected;
}
//echo "order_id_last: $order_id_last<br>";


?>
<script language="JavaScript" type="text/JavaScript">
<!--

function send_to_notify() {

	document.getElementById("bt_Submit").disabled = true;

	var input = document.createElement("input");
	input.setAttribute("type", "hidden");
	input.setAttribute("name", "CODRETEPP");
	input.setAttribute("id","CODRETEPP");
	input.setAttribute("value", "987asd9f87s9df87");
	document.getElementById("mydiv").appendChild(input);

	input = document.createElement("input");
	input.setAttribute("type", "hidden");
	input.setAttribute("name", "cmd");
	input.setAttribute("id","cmd");
	input.setAttribute("value", "processed");
	document.getElementById("mydiv").appendChild(input);

	document.billing.order_id.value = <?php echo $order_id_last; ?>;
	document.billing.target = '_blank';
	document.billing.action = '<?php echo $url_notify; ?>';
	document.billing.submit();
}

function send_to_epp_notify() {

	document.getElementById("bt_Submit").disabled = true;

	var input = document.createElement("input");
	input.setAttribute("type", "hidden");
	input.setAttribute("name", "CODRETEPP");
	input.setAttribute("id","CODRETEPP");
	input.setAttribute("value", "987asd9f87s9df87");
	document.getElementById("mydiv").appendChild(input);

	input = document.createElement("input");
	input.setAttribute("type", "hidden");
	input.setAttribute("name", "cmd");
	input.setAttribute("id","cmd");
	input.setAttribute("value", "processed");
	document.getElementById("mydiv").appendChild(input);

	document.billing.order_id.value = <?php echo $order_id_last; ?>;
	document.billing.target = '_blank';
	document.billing.action = '<?php echo $epp_gateway; ?>';
	document.billing.submit();
}


function send_to_list_trans() {
  document.list_trans.submit() ;
}

//-->
</script>

<body>

<h1>At Partner's site</h1>
<h3>2 - <?php echo getPartner_name_By_ID($INTEGRACAO_STORE_ID) ?> - Integração</h3>

	<form method=post name="send_to_list_trans" target="_blank" action="<?php echo getPartner_return_url_By_ID($INTEGRACAO_STORE_ID) ?>">
		<input type="hidden" name="store_id" value="<?php echo $INTEGRACAO_STORE_ID ?>">
		<input type="submit" value="Go to the list test page for <?php echo getPartner_name_By_ID($INTEGRACAO_STORE_ID) ?>">

	</form>
<br>

  <form method="POST" name="billing" action="<?php echo $send_to_url; ?>" >
  <div id="mydiv"></div>
  <?php
	echo "<table border='1' cellpadding='0' cellspacing='1' bordercolor='#cccccc' style='border-collapse:collapse;'>\n";
	echo "<tr bgcolor='#FFCC66' align='center'><td><b>param</b></td><td><b>value</b></td></tr>\n";
	foreach($partner_params as $key => $val) {
		if($key=="amount") {
			echo "<tr><td>$key</td><td align='right'>$s_select_amount</td></tr>\n";
		} elseif($key=="store_id") {
			echo "<tr><td>$key</td><td align='right'>$s_select_store_id</td></tr>\n";
		} elseif($key=="language") {
			echo "<tr><td>$key</td><td align='right'>$s_select_language</td></tr>\n";
		} elseif((strpos($key,"url")>0) or ($key=="order_id") or ($key=="transaction_id") or ($key=="currency_code")){
			echo "<tr><td>$key</td><td align='right'><input type='hidden' id='$key' name='$key' value='$val'>$val</td></tr>\n";
		} else {
			echo "<tr><td>$key</td><td align='right'><input type='text' id='$key' name='$key' value='$val' class='main'></td></tr>\n";
		}
	}
	echo "</table>\n";

	echo "<hr>";
	echo "notify_url: <b>".getPartner_param_By_ID('notify_url', $INTEGRACAO_STORE_ID)."</b><br>";
	echo "return_url: <b>".getPartner_param_By_ID('return_url', $INTEGRACAO_STORE_ID)."</b><br>";
	echo "<hr>";
  ?>

  <br>&nbsp;<br>&nbsp;
  <input type="submit" value="Send to <?php echo $send_to_url; ?>">

  <br>&nbsp;<br>&nbsp;

  <input type="button" name="bt_Submit" id="bt_Submit" value="Simulate Payment return for order_id: <?php echo $order_id_last ?> (Go to <?php echo $url_notify; ?>)" onClick="send_to_notify()"><br>

  <input type="button" name="bt_Submit2" id="bt_Submit2" value="Simulate calling EPP_NOTIFY for order_id: <?php echo $order_id_last ?> (Go to <?php echo $url_notify; ?>)" onClick="send_to_epp_notify()">


  </form>

  <p>Note: Use the email '<b>WALTER@MAIL.COM</b>' to get an error when trying to login the user to E-Prepag's store.</p>
  <?php
//	foreach($partner_params as $key => $val) {
//		echo "<input type='hidden' name='$key' id='$key' value='$val'>\n";
//	}

//echo "getrandmax(): ".(getrandmax()+1)." (2^".(log(getrandmax()+1)/log(2))."), (10^".(log(getrandmax()+1)/log(10)).")<br>";
//echo "mt_getrandmax(): ".(mt_getrandmax()+1)." (2^".(log(mt_getrandmax()+1)/log(2))."), (10^".(log(mt_getrandmax()+1)/log(10)).")<br>";

// Resultados
//		getrandmax(): 32768 (2^15), (10^4.5)
//		mt_getrandmax(): 2147483648 (2^31), (10^9.3)

//echo "Limits: ".(10e3).", ".(10e6-1)."<br>";

	function get_random_order_id() {
		$iret = mt_rand(10e3, 10e6-1);
		return $iret;
	}
  ?>
</body>