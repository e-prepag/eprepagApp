<?php
	require_once '../../../../../../includes/constantes.php';
        require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";

	$pos_pagina = $seg_auxilar;
        
?>
<?php
	$anos = array();

	$anos[0] = '2003';
	$anos[1] = '2004';
	$anos[2] = '2005';
	$anos[3] = '2006';
	$anos[4] = '2007';
	$anos[5] = '2008';
	$anos[6] = '2009';
	$anos[7] = '2010';

	if(!$ncamp)  $ncamp  = 'trn_data';
	if(!$dd_mes) $dd_mes = date('m');
	if(!$dd_ano) $dd_ano = date('Y');
	
	$resmes = pg_exec($connid, "select * from meses order by mes_codigo");
	
	$estat  = " select t0.trn_data, count(t0.pin_valor) as quantidade, sum(t0.pin_valor) as total ";
	$estat .= " from estat_venda t0 ";
	$estat .= " where extract (month from trn_data) ='".$dd_mes."' and extract (year from trn_data) ='".$dd_ano."' and opr_codigo<>".$opr_teste." ";
	$estat .= " group by t0.trn_data ";
	$estat .= " order by ".$ncamp." desc";

	$resestat = pg_exec($connid, $estat);
	
	$varsel = "&dd_mes=$dd_mes&dd_ano=$dd_ano";
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
        Dia<br>
    </font></b></font></b></font></b></font></p>
    </td>
  </tr>
  <tr>
    <td align="center" valign="top" bgcolor="#FFFFFF">
      <table width="100%" border="0" cellspacing="0" cellpadding="3" height="100%">
        <tr valign="top">
          <td height="100%">   
	       
            <form name="form1" method="post" action="<?php echo $PHP_SELF ?>">
		<table border="0" cellpadding="0" cellspacing="0" width="100%">
		  <tr>
			  <td> <font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong>
			  <?php echo $inic_oper_msg . $inic_oper_data ?></strong></font></td>
		  </tr>
		</table>   
              <table width="100%" border="0" cellpadding="2" cellspacing="2">
                <tr bgcolor="#00008C"> 
                  <td colspan="8"><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><strong>Pesquisa</strong></font></td>
                </tr>
                <tr bgcolor="#F5F5FB"> 
                  <td width="59"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">M&ecirc;s:</font></td>
                  <td width="196"> <select name="dd_mes" id="dd_mes" class="combo_normal">
                      <?php while ($pgmes = pg_fetch_array ($resmes)) { ?>
                      <option value="<?php echo $pgmes['mes_codigo'] ?>" <?php if($pgmes['mes_codigo'] == $dd_mes) echo "selected" ?>><?php echo $pgmes['mes_nome'] ?></option>
                      <?php } ?>
                    </select></td>
                  <td width="49"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">Ano:</font></td>
                  <td width="861">
				  <select name="dd_ano" id="dd_ano" class="combo_normal">
					<?php
						for($i = 0 ; $i <= 7 ; $i++)
						{
							echo "<option value='".$anos[$i]."' ";
							if($anos[$i] == $dd_ano) echo "selected";
							echo ">".$anos[$i]."</option>";
						}
					?>
                    </select>
				  </td>
                  <td width="60"><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
                      <input type="submit" name="Submit" value="Buscar" class="botao_search">
                      </font></div></td>
                </tr>
              </table>
      </form>
	  
            <table width="100%" border='0' cellpadding="2" cellspacing="1">
              <tr bgcolor="#00008C"> 
                <td><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><a href="pquery.php<?php echo $php_self ?><?php echo "?ncamp=trn_data" ?><?php echo $varsel ?>" class="link_br">Dia</a></font></strong></td>
                <td ><div align="right"><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><a href="pquery.php<?php echo $php_self ?><?php echo "?ncamp=quantidade" ?><?php echo $varsel ?>" class="link_br">Qtde</a></font></strong></div></td>
                <td align="right"><div align="right"><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><a href="pquery.php<?php echo $php_self ?><?php echo "?ncamp=total" ?><?php echo $varsel ?>" class="link_br">Valor 
                    Total</a></font></strong></div></td>
                <?php
					$cor1 = $query_cor1;
					$cor2 = $query_cor1;
					$cor3 = $query_cor2;				
					while ($pgestat = pg_fetch_array($resestat))
					{
						$valor = 1;
						$pin_total_valor += $pgestat['total'];
						$pin_total_qtde += $pgestat['quantidade'];
				?>
              <tr bgcolor="#f5f5fb"> 
                <td bgcolor="<?php echo $cor1 ?>"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo formata_data($pgestat['trn_data'], 0) ?></font></td>
                <td bgcolor="<?php echo $cor1 ?>"><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo number_format($pgestat['quantidade'], 0, ',', '.') ?></font></div></td>
                <td align="right" bgcolor="<?php echo $cor1 ?>"><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo number_format($pgestat['total'], 2, ',', '.') ?></font></div></td>
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
                <td><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong>TOTAL</strong></font></td>
                <td><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong><?php echo number_format($pin_total_qtde, 0, ',', '.') ?></strong></font></div></td>
                <td colspan="4"><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong><?php echo number_format($pin_total_valor, 2, ',', '.') ?></strong></font></div></td>
              </tr>
              <tr bgcolor="#E4E4E4"> 
                <td colspan="6" bgcolor="#FFFFFF"><font color="#666666" size="1" face="Arial, Helvetica, sans-serif"><strong>OBS: 
                  Valores expressos em R$. </strong></font></td>
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
<?php
    require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>