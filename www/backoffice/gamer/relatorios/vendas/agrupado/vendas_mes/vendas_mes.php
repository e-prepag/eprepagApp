<?php
	require_once '../../../../../../includes/constantes.php';
        require_once $raiz_do_projeto."backoffice/includes/topo.php";
	$pos_pagina = isset($seg_auxilar) ? $seg_auxilar : '';
	$time_start = getmicrotime();

    if(!isset($ncamp))
        $ncamp = null;
    
    if(!isset($dd_ano))
        $dd_ano = null;
    
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
    
	if(!$ncamp)    $ncamp       = 'mes';
	if(!$dd_ano)   $dd_ano      = date('Y');
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
	$max          = 12;
	$range_qtde   = $qtde_range_tela;

	$sql  = "select extract (month from trn_data) as mes, extract (year from trn_data) as ano, count(t0.pin_valor) as quantidade, sum(t0.pin_valor) as total ";
	$sql .= " from changethis t0 ";			
	$sql .= " where extract (year from trn_data) = '".$dd_ano."' ";
	
	if(!$cb_opr_teste)
		$sql .= "and opr_codigo <> ".$opr_teste." ";
		
	$sql .= " group by mes, ano";
	
	
	//Mounting as Special Sub-select by Union Platform

$estatp1 = str_replace("changethis","estat_venda",$sql);
$estatp2 = str_replace("changethis","estat_venda_2004",$sql);
$estatp3 = str_replace("changethis","estat_venda_1sem05",$sql);
//$sql = "$estatp1 UNION $estatp2 UNION $estatp3 ";
$sql = $estatp1;
//--The End
	
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
	
	$resmes = pg_exec($connid, $sql);
	
	if($max + $inicial > $total_table)
		$reg_ate = $total_table;
	else
		$reg_ate = $max + $inicial;

	$varsel = "&cb_opr_teste=$cb_opr_teste&dd_ano=$dd_ano";
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
        <li class="active">Vendas por Mês</li>
    </ol>
</div>
<table class="table fontsize-pp txt-preto">
  <tr>
    <td align="center" valign="top" bgcolor="#FFFFFF">
      <table class="table">
        <tr valign="top">
          <td height="100%">  

		<form name="form1" method="post" action="">
              <table class="table">
                <tr bgcolor="#ECE9D8"> 
                  <td colspan="6"><font color="#66666" size="2" face="Arial, Helvetica, sans-serif"><strong>Pesquisa</strong></font></td>
                </tr>
                <tr bgcolor="#F5F5FB"> 
                  <td width="55"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">Ano:</font></td>
                  <td width="1125" bgcolor="#F5F5FB">
                    <select name="dd_ano" id="dd_ano" class="combo_normal">
                      <?php for($i = substr($inic_oper_data, 6) ; $i <= date('Y') ; $i++) { ?>
					  	<option value="<?php echo $i ?>" <?php if($dd_ano == $i) echo "selected" ?>><?php echo $i ?></option>
					  <?php } ?>
                    </select></td>
                  <td width="57"><div align="center"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
                      <input type="submit" name="BtnSearch" value="Buscar" class="btn btn-sm btn-info">
                      </font></div></td>
                </tr>
              </table>
              <table class="table">
                <tr> 
                  <td bgcolor=""><font color="#666666" size="1" face="Arial, Helvetica, sans-serif"> 
                    <?php if($total_table > 0) { ?>
                    Exibindo resultados <strong><?php echo $inicial + 1 ?></strong> 
                    a <strong><?php echo $reg_ate ?></strong> de <strong><?php echo $total_table ?></strong></font> 
                    <?php } ?>
                  </td>
                  <td bgcolor=""><div align="right"><a href="../../pquery.php"><img src="/images/voltar.gif" width="47" height="15" border="0"></a></div></td>
                </tr>
              </table>
              <table class="table">
                <tr bgcolor="#00008C"> 
                  <?php
				if($ordem == 1)
					$ordem = 0;
				else
					$ordem = 1;
			?>
                  <td><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=mes&inicial=".$inicial.$varsel ?>" class="link_br">M&ecirc;s</a> 
                    <?php if($ncamp == 'mes') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                    </font></strong></td>
                  <td><div align="right"><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"> 
                      <?php if($ncamp == 'quantidade') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                      <a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=quantidade&inicial=".$inicial.$varsel ?>" class="link_br">Qtde</a></font></strong></div></td>
                  <td><div align="right"><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"> 
                      <?php if($ncamp == 'total') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                      <a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=total&inicial=".$inicial.$varsel ?>" class="link_br">Valor 
                      Total</a></font></strong></div></td>
                </tr>
                <?php
					$cor1 = $query_cor1;
					$cor2 = $query_cor1;
					$cor3 = $query_cor2;
					while ($pgmes = pg_fetch_array($resmes))
					{
						$valor = true;

						$pin_total_valor += $pgmes['total'];
						$pin_total_qtde += $pgmes['quantidade'];
						
						switch($pgmes['mes'])
						{
							case 1:  $mes_nome = "Janeiro"; break;
							case 2:  $mes_nome = "Fevereiro"; break;
							case 3:  $mes_nome = "Março"; break;
							case 4:  $mes_nome = "Abril"; break;
							case 5:  $mes_nome = "Maio"; break;
							case 6:  $mes_nome = "Junho"; break;
							case 7:  $mes_nome = "Julho"; break;
							case 8:  $mes_nome = "Agosto"; break;
							case 9:  $mes_nome = "Setembro"; break;
							case 10: $mes_nome = "Outubro"; break;
							case 11: $mes_nome = "Novembro"; break;
							case 12: $mes_nome = "Dezembro"; break;
						}
				?>
                <tr bgcolor="#f5f5fb"> 
                  <td bgcolor="<?php echo $cor1 ?>"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo $mes_nome." / ".$pgmes['ano'] ?></font></td>
                  <td bgcolor="<?php echo $cor1 ?>"><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo number_format($pgmes['quantidade'], 0, ',', '.') ?></font></div></td>
                  <td bgcolor="<?php echo $cor1 ?>"><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo number_format($pgmes['total'], 2, ',', '.') ?></font></div></td>
                </tr>
                <?php
				 		if($cor1 == $cor2)
							$cor1 = $cor3;
						else
							$cor1 = $cor2;
					}
			 		if (!$valor) { ?>
                <tr bgcolor="#f5f5fb"> 
                  <td colspan="3" bgcolor="<?php echo $cor1 ?>"><div align="center"><font size="2" face="Arial, Helvetica, sans-serif" color="#666666"><strong><br>
                      N&atilde;o h&aacute; registros.<br>
                      <br>
                      </strong></font></div></td>
                </tr>
                <?php } else { ?>
                <tr bgcolor="#E4E4E4"> 
                  <td><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong>TOTAL</strong></font></td>
                  <td><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong><?php echo number_format($pin_total_qtde, 0, ',', '.') ?></strong></font></div></td>
                  <td><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong><?php echo number_format($pin_total_valor, 2, ',', '.') ?></strong></font></div></td>
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
                  <td colspan="3" bgcolor="#FFFFFF"><font size="1" face="Arial, Helvetica, sans-serif" color="#666666"><?php echo $search_msg . number_format($time, 2, '.', '.') . $search_unit ?> 
                    </font></td>
                </tr>
                <?php
					paginacao_query($inicial, $total_table, $max, '3', $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel);
				?>
                <?php } ?>
              </table>
            </form>  
 			<div align="center"><p><br><br><br></p>
              <?php
	require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
            </div></td>
        </tr>
      </table>
   </td>
  </tr>
</table>
</body>
</html>
