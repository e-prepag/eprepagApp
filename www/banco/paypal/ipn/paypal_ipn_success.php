<style>
td, a, b, p {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 11px;
	color: #333333;
	text-decoration: none;
}

</style>
<table width="500" border="0" align="center" cellpadding="1" cellspacing="0">
   <tr> 
      <td align="left" valign="top" bgcolor="#333333"> <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr> 
               <td align="center" bgcolor="#EEEEEE" valign="middle" width="50%">&nbsp;<br><a href="/eprepag/index.php" target="_blank"><img src="/eprepag/imgs/logo_eprepag.gif" title="E-PREPAG" border="0"/></a><br>&nbsp;</td>
               <td align="center" bgcolor="#EEEEEE" valign="middle" width="50%">&nbsp;<br><img src="../../images/Logo-paypal.jpg" width='159' height='35' border="0" title="PayPal"><br>&nbsp;</td>
            </tr>
         </table></td>
   </tr>
</table>
<?php
	require_once( "C:/Sites/E-Prepag/www/web/incs/inc_register_globals.php");	
/*
 * paypal_ipn_success.php
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

// include_once('./connec.php');
require_once("./includes/inc_paypal_utils.php");
 
$webstring = "http://".$_SERVER['SERVER_NAME'] . ":" . $_SERVER['SERVER_PORT'];
//require $_SERVER['DOCUMENT_ROOT']."/incs/configuracao.php";
require $_SERVER['DOCUMENT_ROOT']."/prepag2/pagamento/includes/connect.php";
//include $_SERVER['DOCUMENT_ROOT']."/incs/header.php";
//include $_SERVER['DOCUMENT_ROOT']."/incs/security.php";
require $_SERVER['DOCUMENT_ROOT']."/incs/functions.php";
//echo $_SERVER['DOCUMENT_ROOT']."/incs/functions.php"."<br>";
  // echo 'paypal: ';
  // print_r($paypal);
  // echo "<hr>"; 

   // echo 'post: ';
   // print_r($_POST);
   // echo "<hr>";
//$f_log_paypal_sucess = "C:/Sites/E-Prepag/backoffice/offweb/tarefas/log/LOG_pagtos_paypal_sucess.txt";
  
  $i = 0;
  $slog = "";
  foreach ($_POST as $key => $value) { 
	//echo "$key: $value<br>"; 
	$valor[$i] = $value;
	$i++;
	$slog .= "\t'$key' => '$value'\n";
  }
grava_LOG_Paypal(str_repeat("-", 80)."\nEntering pay/ipn/paypal_ipn_success", $slog);
  
  // print_r($valor);
  // echo '<hr>';
/*
  $sql = "SELECT txn_id FROM paypal WHERE txn_id = '$_POST[txn_id]'";
  $rss = mysql_query($sql, $fd_conn) or die($sql);
  $tot = mysql_num_rows($rss);
*/  
//echo "paypal[business]: '".$paypal[business]."'<br>";

//  $rss = mysql_query($sql, $fd_conn) or die($sql);
//  $tot = mysql_num_rows($rss);

   // echo $sql;
   // echo '<hr>';
   // echo $tot;
   // echo '<hr>';

  //die('stop');
 
 
//include file - not accessible directly
if(isset($paypal[business])) {
//log successful transaction to file or database
  if($tot == 0) {

/*
	echo "mc_gross: ".$_POST['mc_gross']."<br>";
	echo "invoice: ".$_POST['invoice']."<br>";
	echo "payment_status: ".$_POST['payment_status']."<br>";
	echo "item_number: ".$_POST['item_number']."<br>";
	echo "quantity: ".$_POST['quantity']."<br>";
	echo "txn_id: ".$_POST['txn_id']."<br>";
	echo "payment_date: ".$_POST['payment_date']."<br>";
*/
		if (($timestamp = strtotime($_POST['payment_date'])) === false) {
//			echo "O string '".$_POST['payment_date']."' é uma data inválida<br>";
			$date_payment = date('Y-m-d H:i:s');
		} else {
//			echo "$s".$_POST['payment_date']." == " . date('Y-m-d H:i:s', $timestamp)."<br>";
			$date_payment = date('Y-m-d H:i:s', $timestamp);
		}

	$sql = "SELECT * FROM tb_pag_compras WHERE numcompra = '$_POST[invoice]' and iforma='P' and status_processed=0;";	// and status=3 
file_put_contents($f_log_paypal_sucess, "Consulta - ".date("Y-m-d H:i:s")."\n  $sql\n", FILE_APPEND|LOCK_EX); 
//grava_LOG_Paypal(str_repeat("-", 80)."\nEntering pay/ipn/paypal_ipn_success", $slog);

//echo "SQL_abcd: ".$sql."<br>";
	$ret = SQLexecuteQuery($sql);
	if(!$ret) {
		echo "<p><font color='red'>Erro ao recuperar transação de pagamento (1p).</font></p>\n";
		die("Stop - 3243");
	}
//	echo "Transação de pagamento recuperada com sucesso<br>\n";

file_put_contents($f_log_paypal_sucess, "Consulta - ".date("Y-m-d H:i:s")."\n  pg_num_rows(ret):".pg_num_rows($ret)."\n", FILE_APPEND|LOCK_EX); 
	if($ret && pg_num_rows($ret) > 0){
		$ret_row = pg_fetch_array($ret); 
		$idvenda = $ret_row['idvenda'];
		$iforma = $ret_row['iforma'];
		$status = $ret_row['status'];
		$status_processed = $ret_row['status_processed'];
		if($_POST['payment_status']=='Completed' && $_POST['quantity']=='1' && $idvenda>0 && $iforma=='P' && $status==1 && $status_processed==0) {
			// salva em tid o txn_id de PayPal
			$sql_update = "UPDATE tb_pag_compras SET datacompra = '".$date_payment."', tid = '".$_POST['txn_id']."', status=3  WHERE  numcompra = '$_POST[invoice]' and iforma='P' and status_processed=0;";	// status=1 and 
//echo "SQL_update: ".$sql_update."<br>";
file_put_contents($f_log_paypal_sucess, "Atualiza - ".date("Y-m-d H:i:s")."\n  ".$sql_update."\n", FILE_APPEND|LOCK_EX); 
			$ret1 = SQLexecuteQuery($sql_update);
			if(!$ret1) {
				$msg_error = "<p><font color='red'>Erro ao salvar retorno de pagamento (3p).</font></p>\n";
				echo $msg_error;
file_put_contents($f_log_paypal_sucess, "Error - ".date("Y-m-d H:i:s")."\n  ".$msg_error."\n", FILE_APPEND|LOCK_EX); 
				die("Stop - 3245");
			}
		}
	} else {
		$msg_error = "<p><font color='red'>Erro ao ler transação de pagamento (2p).</font></p>\n";
		echo $msg_error;
file_put_contents($f_log_paypal_sucess, "Error - ".date("Y-m-d H:i:s")."\n  ".$msg_error."\n", FILE_APPEND|LOCK_EX); 
		die("Stop - 3244");
	}

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
										'VERIFIED' 
										)";
	$rss = mysql_query($sql, $fd_conn) or die($sql);
*/
  }
  include('../paypal_success.php');
  // echo $sql;
  // echo '<hr>';
  // die('stop');
} else {
	die('Acesso direto negado (1)');
}
?>
<table width="500" border="0" align="center" cellpadding="1" cellspacing="0">
   <tr> 
      <td align="left" valign="top" bgcolor="#333333"> <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr> 
               <td align="center" bgcolor="#EEEEEE"> <p>&nbsp;</p>
                  <p>Seu pagamento está completo e em breve será processado, aguarde mais um minuto!!!.</p>
                  <p>&nbsp;</p></td>
            </tr>
         </table></td>
   </tr>
</table>
<br>
<table width="500" border="0" align="center" cellpadding="1" cellspacing="0">
   <tr> 
      <td align="left" valign="top" bgcolor="#333333"> <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr> 
               <td align="center" bgcolor="#EEEEEE"> <p>&nbsp;</p>
                  <p>
					<form method="post">
					<input type="button" value="Clique aqui para fechar está janela" onclick="window.close()">
					</form>
				  </p>
                  <p>&nbsp;</p></td>
            </tr>
         </table></td>
   </tr>
</table>
