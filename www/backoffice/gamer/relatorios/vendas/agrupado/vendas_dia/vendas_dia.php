<?php
	require_once '../../../../../../includes/constantes.php';
        require_once $raiz_do_projeto."backoffice/includes/topo.php";
	//include "../../../incs/configuracao.inc";
	$pos_pagina = isset($seg_auxilar) ? $seg_auxilar : '';
	$time_start = getmicrotime();

    if(!isset($ncamp))
        $ncamp = null;
    
    if(!isset($dd_ano))
        $dd_ano = null;
    
    if(!isset($dd_mes))
        $dd_mes = null;
    
    if(!isset($inicial))
        $inicial = null;
    
    if(!isset($range))
        $range = null;
    
    if(!isset($ordem))
        $ordem = null;
    
    if(!isset($BtnSearch))
        $BtnSearch = null;
    
    if(!isset($cb_opr_teste))
        $cb_opr_teste = null;
    
    if(!isset($valor))
        $valor = null;
    
    if(!isset($pin_total_valor))
        $pin_total_valor = null;
    
    if(!isset($pin_total_qtde))
        $pin_total_qtde = null;
    
	if(!$ncamp)    $ncamp       = 'trn_data';
	if(!$dd_ano)   $dd_ano      = date('Y');
	if(!$dd_mes)   $dd_mes      = date('m');
	if(!$inicial)  $inicial     = 0;
	if(!$range)    $range       = 1;
	if(!$ordem)    $ordem       = 0;

    if($BtnSearch=="Buscar") {
		$inicial     = 0;
		$range       = 1;
		$total_table = 0;
	}

	
	$default_add  = nome_arquivo($PHP_SELF);
	$img_proxima  = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/proxima.gif";
	$img_anterior = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/anterior.gif";
	$max          = 31;
	$range_qtde   = $qtde_range_tela;

	$sql  = "select * from meses order by mes_codigo";
	$resmes = pg_exec($connid, $sql);

	$sql  = "select trn_data, extract (month from trn_data) as mes, extract (year from trn_data) as ano, count(pin_valor) as quantidade, sum(pin_valor) as total ";
	$sql .= "from estat_venda ";			
	$sql .= "where extract (year from trn_data) = '".$dd_ano."' and extract (month from trn_data) = '".$dd_mes."' ";

	if(!$cb_opr_teste)
		$sql .= "and opr_codigo <> ".$opr_teste." ";
		
	$sql .= "group by trn_data ";
	
	$res_count = pg_query($sql);
	$total_table = pg_num_rows($res_count);

	$sql .= " order by ".$ncamp;
	
	if($ordem == 0)
	{
		$sql .= " desc ";
		$img_seta = "/images/seta_down.gif";	
	}
	else
	{
		$sql .= " asc ";
		$img_seta = "/images/seta_up.gif";
	}

	$sql .= " limit ".$max; 
	$sql .= " offset ".$inicial;

	
	$resdia = pg_exec($connid, $sql);

	if($max + $inicial > $total_table)
		$reg_ate = $total_table;
	else
		$reg_ate = $max + $inicial;

	$varsel = "&cb_opr_teste=$cb_opr_teste&dd_ano=$dd_ano&dd_mes=$dd_mes";
?>
<script language="JavaScript" type="text/JavaScript">
<!--
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}
//-->
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="1">BackOffice - Money</a></li>
        <li><a href="../../pquery.php">Relatórios de Venda</a></li>
        <li class="active">Vendas por DIA</li>
    </ol>
</div>
<table class="table txt-preto fontsize-pp">
    <td width="100%" height="478" align="center" valign="top" bgcolor="#FFFFFF">
      <table class="table" width="100%" border="0" cellspacing="0" cellpadding="3" height="100%">
        <tr valign="top">
          <td height="100%" width="100%">  

		<form name="form1" method="post" action="">
              <table class="table" border='0' width="100%" cellpadding="2" cellspacing="1">
                <tr> 
                  <td width="91%" bordercolor="" bgcolor=""><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
                    <?php  if($total_table > 0) { ?>
                    Exibindo resultados <strong><?php  echo $inicial + 1 ?></strong> 
                    a <strong><?php  echo $reg_ate ?></strong> de <strong><?php  echo $total_table ?></strong></font> 
                    <?php  } ?>
                  </td>
                 <td width="9%">
				</tr>
              </table>
              <table class="table">
                <tr bgcolor="#ECE9D8"> 
                  <td height="22,5" colspan="9"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong>Pesquisa</strong></font></td>
                </tr>
                <tr bgcolor="#F5F5FB"> 
                  <td width="53"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">M&ecirc;s:</font></td>
                  <td width="125"><select name="dd_mes" id="dd_mes" class="combo_normal">
                      <?php  while($pgmes = pg_fetch_array($resmes)) { ?>
                      <option value="<?php  echo $pgmes['mes_codigo'] ?>" <?php  if($dd_mes == $pgmes['mes_codigo']) echo "selected" ?>><?php  echo $pgmes['mes_nome'] ?></option>
                      <?php  } ?>
                    </select></td>
                  <td width="42"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">Ano:</font></td>
                  <td width="948" bgcolor="#F5F5FB"> <select name="dd_ano" id="dd_ano" class="combo_normal">
                      <?php  for($i = 2007 ; $i <= date('Y') ; $i++) { ?>
                      <option value="<?php  echo $i ?>" <?php  if($dd_ano == $i) echo "selected" ?>><?php  echo $i ?></option>
                      <?php  } ?>
                    </select></td>
                  <td width="57"><div align="center"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
                      <input type="submit" name="BtnSearch" value="Buscar" class="btn btn-sm btn-info">
                      </font></div></td>
                </tr>
              </table>
      </form>  
		
		    <table class="table">
              <tr bgcolor="#00008C"> 
	  		<?php 
				if($ordem == 1)
					$ordem = 0;
				else
					$ordem = 1;
			?>
                <td><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><a href="<?php  echo $default_add."?ordem=".$ordem."&ncamp=trn_data&inicial=".$inicial.$varsel ?>" class="link_br">Dia</a> 
                  <?php  if($ncamp == 'trn_data') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                  </font></strong></td>
                <td><div align="right"><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif">
				<?php  if($ncamp == 'quantidade') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
				<a href="<?php  echo $default_add."?ordem=".$ordem."&ncamp=quantidade&inicial=".$inicial.$varsel ?>" class="link_br">Qtde</a></font></strong></div></td>
				<td><div align="right"><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif">
				<?php  if($ncamp == 'total') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
				<a href="<?php  echo $default_add."?ordem=".$ordem."&ncamp=total&inicial=".$inicial.$varsel ?>" class="link_br">Valor Total</a></font></strong></div></td>
			  </tr>
                <?php 
					$cor1 = $query_cor1;
					$cor2 = $query_cor1;
					$cor3 = $query_cor2;
					while ($pgdia = pg_fetch_array($resdia))
					{
						$valor = true;

						$pin_total_valor += $pgdia['total'];
						$pin_total_qtde += $pgdia['quantidade'];
				?>
              <tr bgcolor="#f5f5fb"> 
                <td bgcolor="<?php  echo $cor1 ?>"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php  echo formata_data($pgdia['trn_data'], 0) ?></font></td>
                <td bgcolor="<?php  echo $cor1 ?>"><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php  echo number_format($pgdia['quantidade'], 0, ',', '.') ?></font></div></td>
                <td bgcolor="<?php  echo $cor1 ?>"><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php  echo number_format($pgdia['total'], 2, ',', '.') ?></font></div></td>
              </tr>
              <?php 
				 		if($cor1 == $cor2)
							$cor1 = $cor3;
						else
							$cor1 = $cor2;
					}
			 		if (!$valor) { ?>
              <tr bgcolor="#f5f5fb"> 
                <td colspan="3" bgcolor="<?php  echo $cor1 ?>"><div align="center"><font size="2" face="Arial, Helvetica, sans-serif" color="#666666"><strong><br>
                    N&atilde;o h&aacute; registros.<br>
                    <br>
                    </strong></font></div></td>
              </tr>
              <?php  } else { ?>
              <tr bgcolor="#E4E4E4"> 
                <td><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong>TOTAL</strong></font></td>
                <td><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong><?php  echo number_format($pin_total_qtde, 0, ',', '.') ?></strong></font></div></td>
                <td><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong><?php  echo number_format($pin_total_valor, 2, ',', '.') ?></strong></font></div></td>
              </tr>
				<?php 
					$time_end = getmicrotime();
					$time = $time_end - $time_start;
				?>
			  <tr bgcolor="#E4E4E4"> 
                <td colspan="3" bgcolor="#FFFFFF"><font color="#666666" size="1" face="Arial, Helvetica, sans-serif"><strong> 
                  OBS: Valores expressos em R$. </strong></font></td>
              </tr>
 			  <tr> 
				  <td colspan="3" bgcolor="#FFFFFF"><p><font size="1" face="Arial, Helvetica, sans-serif" color="#666666"><?php  echo $search_msg . number_format($time, 2, '.', '.') . $search_unit ?></font><br>
                  </p>
                  </td>
			  </tr>			
				<?php 
					paginacao_query($inicial, $total_table, $max, '3', $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel);
				?>
              <?php  } ?>
            </table>
			<p>&nbsp;</p>
			<p align="center"><br><br><br><br><br><br>
              <?php 
	require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
          </td>
        </tr>
      </table>
   </td>
  </tr>
</table>
</body>
</html>
