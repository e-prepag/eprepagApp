<?php
        require_once '../../../../../../includes/constantes.php';
	include $raiz_do_projeto."db/connect.php";
	include $raiz_do_projeto."includes/functions.php";
	//$connid = pg_connect("host=$host port=$port dbname=$banco user=$usuario password=$senha");

	if(!$ncamp) $ncamp = 'ano';			
	$ano = 2003;			
	
	$estat  = "select ";
	$estat .= "extract (year from trn_data) as ano, ";
	$estat .= "count(t0.pin_valor) as quantidade, ";
	$estat .= "sum(t0.pin_valor) as total ";
	$estat .= "from estat_venda t0 ";			
	$estat .= "where extract (year from trn_data) >= '".$ano."' and opr_codigo<>78 ";
	$estat .= "group by ano ";
	$estat .= "order by ".$ncamp." desc"; 
	$resestat = pg_exec($connid, $estat);
?>
<html>
<head>

<link href="/css/css.css" rel="stylesheet" type="text/css">
<title>E-Prepag</title>
<script language="JavaScript" type="text/JavaScript">
<!--
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}
//-->
</script>
</head>
<body bgcolor="#FFFFFF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<table width="100%" border="0" cellspacing="6" cellpadding="0" align="center">
  <tr>
    <td height="75" valign="top"><p><font face="Arial, Helvetica, sans-serif" size="2" color="#00008C"><b><font size="3"><br>
        Backoffice</font><font face="Arial, Helvetica, sans-serif" size="2" color="#00008C"><b><font color="#FF1931" size="3"><br>
        </font><font face="Arial, Helvetica, sans-serif" size="2" color="#00008C"><b><font color="#FF1931" size="3">Por 
        Ano<br>
    </font></b></font></b></font></b></font></p>
    </td>
  </tr>
  <tr>
    <td align="center" valign="top" bgcolor="#FFFFFF">
      <table width="100%" border="0" cellspacing="0" cellpadding="3" height="100%">
        <tr valign="top">
          <td height="100%">   
		<table border="0" cellpadding="0" cellspacing="0" width="100%">
		  <tr>
			  <td> <font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong>Data 
				do in&iacute;cio das Opera&ccedil;&otilde;es: 29/08/2003</strong></font></td>
		  </tr>
		</table>   
            <table width="100%" border='0' cellpadding="2" cellspacing="1">
              <tr bgcolor="#00008C"> 
                <td><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><a href="pquery.php<?php echo $php_self ?><?php echo "?ncamp=ano" ?><?php echo $varsel ?>" class="link_br">Ano</a></font></strong></td>
                <td ><div align="right"><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><a href="pquery.php<?php echo $php_self ?><?php echo "?ncamp=quantidade" ?><?php echo $varsel ?>" class="link_br">Qtde</a></font></strong></div></td>
                <td><div align="right"><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><a href="pquery.php<?php echo $php_self ?><?php echo "?ncamp=total" ?><?php echo $varsel ?>" class="link_br">Valor 
                    Total</a></font></strong></div></td>
                <?php
					$cor1 = "#F5F5FB";
					$cor2 = "#F5F5FB";
					$cor3 = "#FFFFFF";				
					while ($pgestat = pg_fetch_array($resestat))
					{
						$valor = 1;
						$total_reg ++;

						$pin_total_valor += $pgestat['total'];
						$pin_total_qtde += $pgestat['quantidade'];
				?>
              <tr bgcolor="#f5f5fb"> 
                <td bgcolor="<?php echo $cor1 ?>"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo $pgestat['ano'] ?></font></td>
                <td bgcolor="<?php echo $cor1 ?>"><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo number_format($pgestat['quantidade'], 0, ',', '.') ?></font></div></td>
                <td bgcolor="<?php echo $cor1 ?>"><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo number_format($pgestat['total'], 2, ',', '.') ?></font></div></td>
              </tr>
              <?php
				 		if ($cor1==$cor2) {$cor1=$cor3;} else {$cor1=$cor2;} 			  
					}
			 		if (!$valor) { ?>
              <tr bgcolor="#f5f5fb"> 
                <td colspan="10" bgcolor="<?php echo $cor1 ?>"><div align="center"><font size="2" face="Arial, Helvetica, sans-serif" color="#666666"><strong><br>
                    N&atilde;o h&aacute; registros.<br>
                    <br>
                    </strong></font></div></td>
              </tr>
              <?php } else { ?>
              <tr bgcolor="#E4E4E4"> 
                <td><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong>TOTAIS</strong></font></td>
                <td><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong><?php echo number_format($pin_total_qtde, 0, ',', '.') ?></strong></font></div></td>
                <td colspan="4"><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"></font></div>
                  <div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"></font></div>
                  <div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong><?php echo number_format($pin_total_valor, 2, ',', '.') ?></strong></font></div></td>
              </tr>
              <tr bgcolor="#E4E4E4"> 
                <td colspan="6" bgcolor="#FFFFFF"><font size="1" face="Arial, Helvetica, sans-serif" color="#666666"><strong>Total 
                  de registros na tela: </strong></font><font color="#666666" size="1" face="Arial, Helvetica, sans-serif"><strong><?php echo $total_reg ?><br>
                  OBS: Valores expressos em R$. </strong></font></td>
              </tr>
              <?php } ?>
            </table>
          </td>
        </tr>
      </table>
   </td>
  </tr>
</table>
</body>
</html>
