<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";
require_once( $raiz_do_projeto . "includes/gamer/constantes.php");
$varsel = "&usuario_id=$usuario_id";

if($v_campo){

	//----------------------------------------------------------------------------------------------------------------------------------
	//Cielo
	//----------------------------------------------------------------------------------------------------------------------------------
	if($v_campo == 'cielo'){
		if(isset($novo_ativo)){?>
			<script>
				window.opener.location.href='com_usuario_detalhe.php?acao=a&v_campo=<?php echo $v_campo ?>&v_valor_new=<?php echo $novo_ativo ?><?php echo $varsel ?>';
				window.close();
			</script>
	<?php	exit;
		}

		//Valor inicial
		if(isset($ativo)) $novo_ativo = $ativo;
		
	}

	//----------------------------------------------------------------------------------------------------------------------------------
	//Estado
	//----------------------------------------------------------------------------------------------------------------------------------
	if($v_campo == 'ativo'){
		if(isset($novo_ativo) && $novo_ativo){?>
			<script>
				window.opener.location.href='com_usuario_detalhe.php?acao=a&v_campo=<?php echo $v_campo ?>&v_valor_new=<?php echo $novo_ativo ?><?php echo $varsel ?>';
				window.close();
			</script>
	<?php	exit;
		}

		//Valor inicial
		if($ativo) $novo_ativo = $ativo;
		
	}
	
	//----------------------------------------------------------------------------------------------------------------------------------
	//Newsletter
	//----------------------------------------------------------------------------------------------------------------------------------
	if($v_campo == 'news'){
		if($novo_news){?>
			<script>
				window.opener.location.href='com_usuario_detalhe.php?acao=a&v_campo=<?php echo $v_campo ?>&v_valor_new=<?php echo $novo_ativo ?><?php echo $varsel ?>';
				window.close();
			</script>
	<?php	exit;
		}

		//Valor inicial
		if($ativo) $novo_ativo = $ativo;
		
	}
	//----------------------------------------------------------------------------------------------------------------------------------
}
?>

<html>
<head>
<title>E-Prepag Meios de Pagamentos</title>
<link href="/css/css.css" rel="stylesheet" type="text/css">

<script language="javascript">

function fcnValidaAtivo() {

	if(document.formAtivo.novo_ativo.value == ''){
		alert('O Status deve ser selecionado');
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
				<font face="Arial, Helvetica, sans-serif" size="1" color="#FFFFFF"><b>Money - Usuário</b></font></td>
			</td>
          </tr>
		</table>

<?php if($v_campo == 'ativo'||$v_campo == 'cielo'){ ?>
 <form name="formAtivo" method="post" action="?v_campo=<?php echo $v_campo ?><?php echo $varsel ?>">

        <table width="100%" border="0" cellpadding="0" cellspacing="2">
          <tr bgcolor="#FFFFFF"> 
            <td colspan="5" bgcolor="#ECE9D8"><font color="#666666" size="1" face="Arial, Helvetica, sans-serif"><?php echo strtoupper($v_campo); ?></font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="100"><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">Status</font></td>
            <td>
				<select name="novo_ativo">
					<option value="">Selecione o status</option>
                                        <?php
                                        if($v_campo != 'ativo') {
                                        ?>
					<option value="1" <?php if(isset($novo_ativo) && $novo_ativo == "1") echo "selected"; ?>>Ativo</option>
					<option value="2" <?php if(isset($novo_ativo) && $novo_ativo != "1") echo "selected"; ?>>Inativo</option>
                                        <?php 
                                        } //end if($v_campo != 'ativo') 
                                        else {
                                                foreach($GLOBALS['STATUS_USUARIO'] as $key => $val) {
                                                ?>
                                                        <option value='<?php echo $val ?>'<?php echo (isset($novo_ativo) && ($novo_ativo==$val)?" selected":"") ?>><?php echo $GLOBALS['STATUS_USUARIO_LEGENDA'][$val]?></option>
                                                <?php
                                                } //end foreach
                                        } //end else do if($v_campo != 'ativo')
                                        ?>
				</select>
			</td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td></td>
            <td><input type="submit" name="BtnSearch" value="Alterar" class="botao_search" onclick="return fcnValidaAtivo();"></td>
          </tr>

		<?php if(isset($msg) && $msg != ""){?>
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
