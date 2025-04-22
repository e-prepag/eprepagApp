<!doctype html public "-//W3C//DTD HTML 4.0 Transitional//EN">
<?php
session_start();
session_destroy();
die('');
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
header("Content-Type: text/html; charset=ISO-8859-1",true);

// include do arquivo contendo IPs DEV
require_once '../includes/main.php';
$server_url = "www.e-prepag.com.br";
if(checkIP()) {
    $server_url = $_SERVER['SERVER_NAME'];
}

$id		= isset($_POST['id'])			? (int) $_POST['id']			: null;
$pin_code	= isset($_POST['pin_code'])		? (int) $_POST['pin_code']		: null;
$pin_value 	= isset($_POST['pin_value'])		? (int) $_POST['pin_value']		: null;
$action		= isset($_POST['action'])		? (int) $_POST['action']		: null;
$riot_order_id	= isset($_POST["riot_order_id"])	? $_POST["riot_order_id"]		: null;
$checkout_id	= isset($_POST["checkout_id"])		? $_POST["checkout_id"]			: null;

//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
?>
<html>
<title> Teste integração de PINs </title>
<script language="javascript">
function verifica()
{
    if ((event.keyCode<47)||(event.keyCode>58)){
          alert("Somente numeros sao permitidos");
          event.returnValue = false;
    }
}

</script>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<br>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td height="22,5" valign="center" align="center" bgcolor="#00008C"><font face="Arial, Helvetica, sans-serif" size="3" color="#FFFFFF"><b>Teste de Integra&ccedil;&atilde;o de PINs E-PREPAG</b></font></td>
  </tr>
  <tr>
    <td><form name="form1" method="post" action="http<?php echo (($_SERVER['HTTPS']=="on")?"s":"")."://" . $server_url; ?>/check-redeem/">
        <table width="100%" border='0' cellpadding="2" cellspacing="1">
          <tr>
            <td width="30%" bgcolor="#00008C"><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><b> C&oacute;digo do Parceiro:<b></font></td>
            <td width="70%" colspan="2" bgcolor="#f5f5fb"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">
		        <input name="ID" id="ID" type="text" value="<?php echo $id; ?>" size="2" maxlength="2" onKeypress="return verifica();">
              </font></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td width="30%" bgcolor="#00008C"><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><b> Valor do PIN: </b></font></td>
            <td width="70%" colspan="2" bgcolor="#f5f5fb"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">
				<div id='mostraValores'>
				<input name="PIN_VALUE" id="PIN_VALUE" type="text" value="<?php echo $pin_code; ?>" size="10" maxlength="10" onKeypress="return verifica();">
			  </div>
              </font></td>
          </tr>
          <tr bgcolor="#F5F5FB">
            <td width="30%" bgcolor="#00008C"><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><b> Codigo do PIN: </b></font></td>
            <td width="70%" colspan="2" bgcolor="#f5f5fb"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">
                    <input name="PIN_CODE" id="PIN_CODE" type="text" value="<?php echo $pin_code; ?>" size="30" maxlength="30" onKeypress="return verifica();">
            </font></td>
          </tr>
          <tr bgcolor="#F5F5FB">
            <td width="30%" bgcolor="#00008C"><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><b> Ação: </b></font></td>
            <td width="70%" colspan="2" bgcolor="#f5f5fb"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">
                    <input name="ACTION" id="ACTION" type="text" value="<?php echo $action; ?>" size="1" maxlength="1" onKeypress="return verifica();">
            </font></td>
          </tr>
          <tr bgcolor="#F5F5FB">
            <td width="30%" bgcolor="#00008C"><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><b> RIOT ID: </b></font></td>
            <td width="70%" colspan="2" bgcolor="#f5f5fb"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">
            <input name="riot_order_id" id="riot_order_id" type="text" value="<?php echo htmlspecialchars($riot_order_id, ENT_QUOTES, 'UTF-8'); ?>" size="15" maxlength="15">
            </font></td>
          </tr>
          <tr bgcolor="#F5F5FB">
            <td width="30%" bgcolor="#00008C"><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><b> CHECKOUT ID: </b></font></td>
            <td width="70%" colspan="2" bgcolor="#f5f5fb"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">
            <input name="checkout_id" id="checkout_id" type="text" value="<?php echo htmlspecialchars($checkout_id, ENT_QUOTES, 'UTF-8'); ?>" size="15" maxlength="25">
            </font></td>
          </tr>
          <tr bgcolor="#FFFFFF">
            <td>&nbsp;</td>
            <td colspan="2">&nbsp;</td>
          </tr>
          <tr>
            <td colspan="3" align="center">
      		<input name="testar" type="submit" id="testar" value="Testar">
            </td>
          </tr>
          <tr><td colspan="3"> <p>&nbsp;</p></td></tr>
        </table>
      </form></td>
  </tr>
</table>
</html>