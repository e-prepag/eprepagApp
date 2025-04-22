<?php

	require_once( "C:/Sites/E-Prepag/www/web/incs/inc_register_globals.php");	

/*
<script>
// aqui fazemos a atualizacao em nosso servidor pois esse arquivo esta fora de nossa rede
window.open("http://www.e-prepag.com.br/prepag2/pag/pay/atualizavenda.php?item_number=<_?php echo $_POST[item_number];?_>&data_compra=<_?php echo $_POST[payment_date];?_>&tx_id=<_?php echo $_POST[txn_id];?_>", "_self", "");
</script>
*/

//die();
/*
 * paypal_success.php
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
?>

<html>
<head><title>::Obrigado::</title>
<link rel="stylesheet" type="text/css" href="styles.css">
<style>
td, a, b, p {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 11px;
	color: #333333;
	text-decoration: none;
}

</style>

</head>

<body bgcolor="ffffff">
<br>
<br>
<table width="500" border="0" align="center" cellpadding="1" cellspacing="0">
   <tr> 
      <td align="left" valign="top" bgcolor="#333333"> <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr> 
               <td align="center" bgcolor="#EEEEEE"> <p>&nbsp;</p>
                  <p>Obrigado! Seu pedido foi processado com sucesso.</p>
                  <p>&nbsp;</p></td>
            </tr>
         </table></td>
   </tr>
</table>
<br>
<table width="500" border="0" align="center" cellpadding="1" cellspacing="0">
   <tr> 
      <td align="left" valign="top" bgcolor="#333333"> <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr align="left" valign="top"> 
               <td width="20%" bgcolor="#EEEEEE"><table width="100%" border="0" cellspacing="0" cellpadding="3">
                     <tr align="left" valign="top"> 
                        <td bgcolor="#EEEEEE">Número da ordem:</td>
                        <td bgcolor="#EEEEEE"> 
                           <?php echo $_POST[txn_id]?>
                        </td>
                     </tr>
                     <tr align="left" valign="top"> 
                        <td bgcolor="#EEEEEE">Data:</td>
                        <td bgcolor="#EEEEEE"> 
                           <?php echo $_POST[payment_date]?>
                        </td>
                     </tr>
                     <tr align="left" valign="top"> 
                        <td width="20%" bgcolor="#EEEEEE"> Nome: </td>
                        <td width="80%" bgcolor="#EEEEEE"> 
                           <?php echo $_POST[first_name]?>
                        </td>
                     </tr>
                     <tr align="left" valign="top"> 
                        <td bgcolor="#EEEEEE">Sobrenome:</td>
                        <td bgcolor="#EEEEEE"> 
                           <?php echo $_POST[last_name]?>
                        </td>
                     </tr>
                     <tr align="left" valign="top"> 
                        <td bgcolor="#EEEEEE">Email:</td>
                        <td bgcolor="#EEEEEE"> 
                           <?php echo $_POST[payer_email]?>
                        </td>
                     </tr>
                  </table></td>
            </tr>
         </table></td>
   </tr>
</table>
<br>
</body>
</html>
