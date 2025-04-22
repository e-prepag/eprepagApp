<?php
	require_once '../../includes/constantes.php';
        require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";
	$pos_pagina = $seg_auxilar;
?>
<?php
	$time_start = getmicrotime();

	if(!$tf_data_inic) $tf_data_inic = date('d/m/Y');
	if(!$tf_data_final) $tf_data_final = date('d/m/Y');
	if(!$ncamp) $ncamp = 'dep_data';
	if(!$inicial)  $inicial     = 0;
	if(!$range)    $range       = 1;
	if(!$ordem)    $ordem       = 0;
//	if($BtnSearch) $inicial     = 0;
//	if($BtnSearch) $range       = 1;
//	if($BtnSearch) $total_table = 0;
	if($BtnSearch=="Buscar") {
		$inicial     = 0;
		$range       = 1;
		$total_table = 0;
	}

	
	$default_add  = nome_arquivo($PHP_SELF);
	$img_proxima  = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/proxima.gif";
	$img_anterior = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/anterior.gif";
	$max          = $qtde_reg_tela;
	$range_qtde   = $qtde_range_tela;

	$sql = "select dep_codigo, dep_valor, dep_data, dep_importacao, dep_banco, dep_documento, dep_cod_documento, bco_nome, dep_aprovado ";

	$sql .= "from depositos_pendentes, bancos_financeiros ";

	$sql .= "where (dep_banco = bco_codigo) and (bco_rpp = 1) and (dep_data <= '".formata_data($tf_data_final, 1)."')";

	$sql .= "and dep_aprovado = 0 ";
		
	$res_count = pg_query($sql);
	$total_table = pg_num_rows($res_count);
	
	$sql .= "order by ".$ncamp." ";

	if($ordem == 1)
	{
		$sql .= " asc ";
		$img_seta = "/images/seta_up.gif";
	}
	else
	{
		$sql .= " desc ";
		$img_seta = "/images/seta_down.gif";
	}

	$sql .= " limit ".$max." ";
	$sql .= " offset ".$inicial;

//	trace_sql($sql, "Arial", 2, "#666666", 'b');			
	$resest = pg_exec($connid,$sql);
	
	if($max + $inicial > $total_table)
		$reg_ate = $total_table;
	else
		$reg_ate = $max + $inicial;
		
		
	$varsel = "&tf_data_inic=$tf_data_inic&tf_data_final=$tf_data_final&dd_banco=$dd_banco&tf_cod_documento=$tf_cod_documento&tf_documento=$tf_documento&dd_situacao=$dd_situacao";
?>

<html>
<head>
<title>Depósitos Pendentes</title>
<link rel="stylesheet" href="/css/css.css" type="text/css">
<script language='javascript' src='/js/popcalendar.js'></script>
<script language="JavaScript">
function GP_popupConfirmMsg(msg) { 
  document.MM_returnValue = confirm(msg);
}

function GP_popupAlertMsg(msg) { 
  document.MM_returnValue = alert(msg);
}
</script>

</head>
<body bgcolor="#FFFFFF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<table width="100%" border="0" cellspacing="6" cellpadding="0">
  <tr> 
    <td valign="top"> 
		<table border='0' width="100%" cellpadding="2" cellspacing="1">
			<tr> 
				<td><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">
					<?php if($total_table > 0) { ?>
						Exibindo resultados <strong><?php echo $inicial + 1 ?></strong> a <strong><?php echo $reg_ate ?></strong> de <strong><?php echo $total_table ?></strong></font>
					<?php } else { ?>
						&nbsp;
					<?php } ?>
				</td>
				<td><div align="right"><a href="javascript:window.close()"><img src="/images/deletar.gif" alt="Fechar Janela" width="12" height="14" border="0" align="absmiddle"></a></font></div></td>
			</tr>
		</table>   
      <table border='0' width="100%" cellpadding="2" cellspacing="1">
        <tr bgcolor="#00008C"> 
          <td><strong><a href="<?php echo $default_add . "?ordem=" . !$ordem . "&inicial=" . $inicial . "&ncamp=dep_data" . $varsel ?>"><font face="Arial, Helvetica, sans-serif" size="2" color="#FFFFFF" class="link_br">Data</font></a></strong> 
            <?php if($ncamp == 'dep_data') echo "<img src=".$img_seta." width='10' height='7'>"; ?>
          </td>
          <td><strong><a href="<?php echo $default_add . "?ordem=" . !$ordem . "&inicial=" . $inicial . "&ncamp=dep_importacao" . $varsel ?>"><font face="Arial, Helvetica, sans-serif" size="2" color="#FFFFFF" class="link_br">Importa&ccedil;&atilde;o</font></a></strong> 
            <?php if($ncamp == 'dep_importacao') echo "<img src=".$img_seta." width='10' height='7'>"; ?>
          </td>
          <td><strong><a href="<?php echo $default_add . "?ordem=" . !$ordem . "&inicial=" . $inicial . "&ncamp=dep_banco" . $varsel ?>"><font face="Arial, Helvetica, sans-serif" size="2" color="#FFFFFF"><span class="link_br">Banco</span></font></a></strong> 
            <?php if($ncamp == 'dep_banco') echo "<img src=".$img_seta." width='10' height='7'>"; ?>
          </td>
          <td><strong><a href="<?php echo $default_add . "?ordem=" . !$ordem . "&inicial=" . $inicial . "&ncamp=dep_documento" . $varsel ?>"><font face="Arial, Helvetica, sans-serif" size="2" color="#FFFFFF"><span class="link_br">Documento</span></font></a></strong> 
            <?php if($ncamp == 'dep_documento') echo "<img src=".$img_seta." width='10' height='7'>"; ?>
          </td>
          <td><div align="right"> 
              <?php if($ncamp == 'dep_valor') echo "<img src=".$img_seta." width='10' height='7'>"; ?>
              <strong><a href="<?php echo $default_add . "?ordem=" . !$ordem . "&inicial=" . $inicial . "&ncamp=dep_valor" . $varsel ?>"><font face="Arial, Helvetica, sans-serif" size="2" color="#FFFFFF"><span class="link_br">Valor</span></font></a></strong></div></td>
        </tr>
        <?php
			$cor1 = "#F5F5FB";
			$cor2 = "#F5F5FB";
			$cor3 = "#FFFFFF"; 	
			while ($pgest = pg_fetch_array($resest))
			{
				$valor = 1;
				$dep_valor_total += $pgest['dep_valor'];
				
				if($pgest['dep_aprovado'] == 1)
					$dep_aprovado = "Conciliado";
				else
					$dep_aprovado = "Disponível";
		 ?>
        <tr> 
          <td bgcolor="<?php echo $cor1 ?>"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo formata_data($pgest['dep_data'], 0) ?></font></td>
          <td bgcolor="<?php echo $cor1 ?>"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo formata_timestamp ($pgest['dep_importacao'],2) ?></font></td>
          <td bgcolor="<?php echo $cor1 ?>"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo $pgest['dep_banco'] ?></font></td>
          <td bgcolor="<?php echo $cor1 ?>"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo $pgest['dep_documento'] ?></font></td>
          <td bgcolor="<?php echo $cor1 ?>"><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo number_format($pgest['dep_valor'], 2, ',', '.') ?></font></div></td>
        </tr>
        <?php

       if ($cor1 == $cor2) {
           $cor1 = $cor3;
       } else {
           $cor1 = $cor2;
           
        } }
	   if (!$valor)
	   {  ?>
        <tr bgcolor="#f5f5fb"> 
          <td colspan="5" bgcolor="<?php echo $cor1 ?>"><div align="center"><font size="2" face="Arial, Helvetica, sans-serif" color="#666666"><strong><br>
              N&atilde;o h&aacute; registros<br>
              <br>
              </strong></font></div></td>
        </tr>
        <?php } else { ?>
        <tr bgcolor="#E4E4E4"> 
          <td colspan="4"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong>TOTAL</strong></font></td>
          <td><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong><?php echo number_format($dep_valor_total, 2, ',', '.') ?></strong></font></div></td>
        </tr>
        <tr bgcolor="#E4E4E4"> 
          <td colspan="5" bgcolor="#FFFFFF"><font size="1" face="Arial, Helvetica, sans-serif" color="#666666"><strong> 
            OBS: Valores expressos em R$.</strong></font></td>
        </tr>
        <?php
			  $time_end = getmicrotime();
			  $time = $time_end - $time_start;
	  ?>
        <tr> 
          <td colspan="5" bgcolor="#FFFFFF"><font size="1" face="Arial, Helvetica, sans-serif" color="#666666"><?php echo $search_msg . number_format($time, 2, '.', '.') . $search_unit?> 
            </font></td>
        </tr>
        <?php
			paginacao_query($inicial, $total_table, $max, '3', $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel);
		?>
        <?php  }  ?>
      </table>
      <?php pg_close ($connid); ?>
    </td>
  </tr>
</table>
</html>