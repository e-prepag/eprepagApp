<?php 

require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "db/connect.php"; 
require_once $raiz_do_projeto . "includes/inc_register_globals.php";	

?>
<?php


//echo "venda_id:".$venda_id."<br>";
//echo "novo_email: $novo_email<br>";
//echo "<pre>";
//print_r($GLOBALS);
//echo "<pre>";

$varsel = "&venda_id=$venda_id";

if($v_campo){
	//----------------------------------------------------------------------------------------------------------------------------------
	//Email
	//----------------------------------------------------------------------------------------------------------------------------------
	if($v_campo == 'email'){
		if($novo_email){ ?>
			<script>
				window.opener.location.href='com_venda_detalhe.php?acao=a&v_campo=<?php echo $v_campo ?>&v_valor_new=<?php echo $novo_email ?><?php echo $varsel ?>';
				window.close();
			</script>
	<?php		exit;
		}

		//Valor inicial
		if($email) $novo_email = $email;
		
	}
	
	//----------------------------------------------------------------------------------------------------------------------------------
}
?>

<html>
<head>
<title>E-Prepag Meios de Pagamentos</title>
<link href="/css/css.css" rel="stylesheet" type="text/css">

<script language="javascript">

function fcnValidaEmail() {

	if(document.formVenda.novo_email.value == ''){
		alert('O Email deve ser preenchido');
		return false;
	}
	
	return true;
}

</script>

<title>E-Prepag</title>
</head>

<body bgcolor="#FFFFFF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
  <tr>
    <td align="center" valign="top" bgcolor="#FFFFFF" width="100%">
	
        <table width="100%" border="0" cellpadding="0" cellspacing="2">
          <tr> 
            <td colspan="5" height="21" bgcolor="00008C">
				<font face="Arial, Helvetica, sans-serif" size="1" color="#FFFFFF"><b>Money - Venda</b></font></td>
			</td>
          </tr>
		</table>

<?php if($v_campo == 'email'){ ?>
 <form name="formVenda" method="post" action="?v_campo=<?php echo $v_campo ?><?php echo $varsel ?>">

        <table width="100%" border="0" cellpadding="0" cellspacing="2">
          <tr bgcolor="#FFFFFF"> 
            <td colspan="5" bgcolor="#ECE9D8"><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">Email</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="100"><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">Email</font></td>
          <td>
			<input type="text" name="novo_email" value="<?php echo $email; ?>">
  			</td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td></td>
            <td><input type="submit" name="BtnSearch" value="Alterar" class="botao_search" onclick="return fcnValidaEmail();"></td>
          </tr>

		<?php if($msg != ""){?>
		  	<tr><td colspan="2">&nbsp;</td></tr>
		  	<tr><td colspan="2" align="center"><font color="#FF0000" size="1" face="Arial, Helvetica, sans-serif"><?php echo $msg?></font></td></tr>
		<?php }?>
		</table>
</form>
<?php } ?>

   </td>
  </tr>
</table>
</body>
</html>
