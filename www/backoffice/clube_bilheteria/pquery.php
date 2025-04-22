
<?php
	require_once '../../includes/constantes.php';
        require_once $raiz_do_projeto."backoffice/includes/topo.php";
        require_once $raiz_do_projeto."includes/main.php";
        require_once $raiz_do_projeto."includes/pdv/main.php";
	$pos_pagina = $seg_auxilar;
?>

<?php 
	$time_start = getmicrotime();

	$varsel = "&tf_v_codigo=$tf_v_codigo";
	$varsel .= "&tf_v_data_inclusao_ini=$tf_v_data_inclusao_ini&tf_v_data_inclusao_fim=$tf_v_data_inclusao_fim";

	//paginacao
	$p = $_REQUEST['p'];
	if(!$p) $p = 1;
	$registros = 50;
	$registros_total = 0;

	if(!$ncamp)    $ncamp       = 'vb_data_venda';
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
	$img_proxima  = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/proxima.gif";
	$img_anterior = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/anterior.gif";
	$max          = 100;	//$qtde_reg_tela;
	$range_qtde   = $qtde_range_tela;

	$lh_nome = ""; 		
	if($usuarioId) {
		$reslh = pg_exec($connid, "select (CASE WHEN (ug.ug_tipo_cadastro='PJ') THEN ug.ug_nome_fantasia||' ('||ug.ug_tipo_cadastro||')'  WHEN (ug.ug_tipo_cadastro='PF') THEN ug.ug_nome||' ('||ug.ug_tipo_cadastro||')'  END) as vb_lan_nome from dist_usuarios_games ug where ug.ug_id=".$usuarioId);
		if($reslh) {
			$pgrow_lh = pg_fetch_array($reslh); 
			$lh_nome = $pgrow_lh['vb_lan_nome'];
		}
	}

	$sql  = "select vb.*, (CASE WHEN (ug.ug_tipo_cadastro='PJ') THEN ug.ug_nome_fantasia||' ('||ug.ug_tipo_cadastro||')'  WHEN (ug.ug_tipo_cadastro='PF') THEN ug.ug_nome||' ('||ug.ug_tipo_cadastro||')'  END) as vb_lan_nome from dist_vendas_bilhetes vb left join dist_usuarios_games ug on vb.vb_ug_id=ug.ug_id where 1=1 ";
	if($usuarioId)
		$sql .= " and vb.vb_ug_id=".$usuarioId." ";
	if($tf_v_codigo) {
		$tf_v_codigo_sanitize = strtoupper($tf_v_codigo);
		$tf_v_codigo_sanitize = str_replace("'", "''", $tf_v_codigo_sanitize);
		$tf_v_codigo_sanitize = str_replace(";", "", $tf_v_codigo_sanitize);
		$tf_v_codigo_sanitize = str_replace("DROP", "", $tf_v_codigo_sanitize);
		$tf_v_codigo_sanitize = str_replace("CREATE", "", $tf_v_codigo_sanitize);

		$sql .= " and upper(vb.vb_espetaculo) like '%" . $tf_v_codigo_sanitize . "%' ";
	}
	if($tf_v_data_inclusao_ini && $tf_v_data_inclusao_fim) 
		if(verifica_data($tf_v_data_inclusao_ini) != 0 && verifica_data($tf_v_data_inclusao_fim) != 0)
			$sql .= " and vb.vb_data_inclusao between '".formata_data($tf_v_data_inclusao_ini,1)." 00:00:00' and '".formata_data($tf_v_data_inclusao_fim,1)." 23:59:59'";
//echo $sql;

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

	$resid = pg_exec($connid, $sql);
	
	if($max + $inicial > $total_table)
		$reg_ate = $total_table;
	else
		$reg_ate = $max + $inicial;

//echo $sql."<br>";
?>

<link rel="stylesheet" href="/css/css.css" type="text/css">
<script language="JavaScript" type="text/JavaScript">
<!--
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}
//-->
</script>
<script language='javascript' src='/js/popcalendar.js'></script>

<body bgcolor="#FFFFFF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">

<table width="100%" border="0" cellspacing="6" cellpadding="0">
  <tr> 
    <td height="22,5" bgcolor="#00008C"><p><font face="Arial, Helvetica, sans-serif" size="2" color="#00008C"><b><font face="Arial, Helvetica, sans-serif" size="2" color="#00008C"><b><font color="#FF1931" size="3"> 
        </font><font face="Arial, Helvetica, sans-serif" size="2" color="#00008C"><b><font color="#FFFFFF" size="2">Vendas Bilheteria.com<?php if($lh_nome) echo " - para o PDV <font color='#FF0000'>'$lh_nome'</font>"; ?><br>
        </font></b></font></b></font></b></font></p></td>
  </tr>
  <tr> 
    <td>
		<form name="form1" method="post" action="pquery.php">
        <table width="100%" border="0" cellpadding="0" cellspacing="2">
		  <tr bgcolor="#F5F5FB" class="texto"> 
			<td>Nome do espectáculo</td>
			<td> &nbsp;<input name="tf_v_codigo" type="text" class="form" value="<?php echo $tf_v_codigo ?>" size="10" maxlength="10"> </td>
			<td>Período da Compra</td>
			<td> &nbsp;	
			  <input name="tf_v_data_inclusao_ini" type="text" class="form" id="tf_v_data_inclusao_ini" value="<?php echo $tf_v_data_inclusao_ini ?>" size="9" maxlength="10">
			  <a href="#" onClick="return false;"><img src="/images/cal.gif" width="16" height="16" alt="Calendário" onclick="popUpCalendar(this, form1.tf_v_data_inclusao_ini, 'dd/mm/yyyy')" border="0" align="absmiddle"></a> 
			  a 
			  <input name="tf_v_data_inclusao_fim" type="text" class="form" id="tf_v_data_inclusao_fim" value="<?php echo $tf_v_data_inclusao_fim ?>" size="9" maxlength="10">
			  <a href="#" onClick="return false;"><img src="/images/cal.gif" width="16" height="16" alt="Calendário" onclick="popUpCalendar(this, form1.tf_v_data_inclusao_fim, 'dd/mm/yyyy')" border="0" align="absmiddle"></a>
			</td>
		    <td class="texto" align="center"><input type="submit" name="btPesquisar" value="Pesquisar" class="botao_simples"></td>
		  </tr>
	  </table>
	  </form>
    </td>
  </tr>

  <tr> 
    <td height="232">
	<table border='0' width="100%" cellpadding="" cellspacing="1">
        <tr> 
          <td height="1" colspan="2"><div align="left"><font color="#666666" size="1" face="Arial, Helvetica, sans-serif"> 
              </font></div></td>
        </tr>
        <tr> 
          <td height="10" bgcolor=""><div align="left"><font color="#666666" size="1" face="Arial, Helvetica, sans-serif"> 
              <?php if($total_table > 0) { ?>
              Exibindo resultados <strong><?php echo $inicial + 1 ?></strong> a <strong><?php echo $reg_ate ?></strong> 
              de <strong><?php echo $total_table ?></strong></font> 
              <?php } else { ?>
              <?php } ?>
              <font face="Arial, Helvetica, sans-serif" size="2" color="#00008C"></font></div></td>
          <td bgcolor=""><div align="right"><font face="Arial, Helvetica, sans-serif" size="2" color="#00008C"><a href="/index.php" class="menu"><img src="/images/voltar.gif" width="50" height="15" border="0"></a></font></div></td>
        </tr>
      </table>
	  <table border='0' width="100%" cellpadding="1" cellspacing="1">
        <tr> 
          <?php
			if($ordem == 1)
				$ordem = 0;
			else
				$ordem = 1;
		?>

          <td width="" bgcolor="#00008C"><nobr><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=vb_id&inicial=".$inicial ?>"><font color="#FFFFFF" size="1" class="link_br" face="Arial, Helvetica, sans-serif"><b title="No. Seqüencial">ID</b></font></a> 
            <?php if($ncamp == 'vb_id') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
            </strong></nobr></td>
          <td bgcolor="#00008C" align="center"><nobr><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=vb_tr_id&inicial=".$inicial ?>"><font color="#FFFFFF" size="1" class="link_br" face="Arial, Helvetica, sans-serif"><b title="No. Protocolo">Protocolo</b></font></a> 
            <?php if($ncamp == 'vb_tr_id') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
            </strong></nobr></td>
          <td bgcolor="#00008C" align="center"><nobr><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=vb_data_venda&inicial=".$inicial ?>"><font color="#FFFFFF" size="1" class="link_br" face="Arial, Helvetica, sans-serif">Data da Compra</font></a> 
            <?php if($ncamp == 'vb_data_venda') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
            </strong></nobr></td>
          <td width="" bgcolor="#00008C"><nobr><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=vb_teatro&inicial=".$inicial ?>"><font color="#FFFFFF" size="1" class="link_br" face="Arial, Helvetica, sans-serif">Teatro</font></a> 
            <?php if($ncamp == 'vb_teatro') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
            </strong></nobr></td>
          <td width="" bgcolor="#00008C"><nobr><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=vb_espetaculo&inicial=".$inicial ?>"><font color="#FFFFFF" size="1" class="link_br" face="Arial, Helvetica, sans-serif">Espetáculo</font></a> 
            <?php if($ncamp == 'vb_espetaculo') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
            </strong></nobr></td>
          <td width="" bgcolor="#00008C"><nobr><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=vb_ug_id&inicial=".$inicial ?>"><font color="#FFFFFF" size="1" class="link_br" face="Arial, Helvetica, sans-serif">Lan</font></a> 
            <?php if($ncamp == 'vb_ug_id') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
            </strong></nobr></td>
          <td width="" bgcolor="#00008C"><nobr><strong><font color="#FFFFFF" size="1" class="link_br" face="Arial, Helvetica, sans-serif">Lan nome</font> 
            </strong></nobr></td>
          <td width="" bgcolor="#00008C"><nobr><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=vb_cliente_nome&inicial=".$inicial ?>"><font color="#FFFFFF" size="1" class="link_br" face="Arial, Helvetica, sans-serif">Comprador</font></a> 
            <?php if($ncamp == 'vb_cliente_nome') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
            </strong></nobr></td>
          <td width="" bgcolor="#00008C"><nobr><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=vb_forma_pagto&inicial=".$inicial ?>"><font color="#FFFFFF" size="1" class="link_br" face="Arial, Helvetica, sans-serif">Pagmto.</font></a> 
            <?php if($ncamp == 'vb_forma_pagto') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
            </strong></nobr></td>
          <td width="" bgcolor="#00008C"><nobr><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=vb_preco_total&inicial=".$inicial ?>"><font color="#FFFFFF" size="1" class="link_br" face="Arial, Helvetica, sans-serif">Preço</font></a> 
            <?php if($ncamp == 'vb_preco_total') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
            </strong></nobr></td>
          <td width="" bgcolor="#00008C"><nobr><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=vb_comissao_lan&inicial=".$inicial ?>"><font color="#FFFFFF" size="1" class="link_br" face="Arial, Helvetica, sans-serif">Com.</font></a> 
            <?php if($ncamp == 'vb_comissao_lan') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
            </strong></nobr></td>

		</tr>
          <?php
				$cor1 = $query_cor1; 
				$cor2 = $query_cor1;
				$cor3 = $query_cor2;
				while ($pgrow = pg_fetch_array($resid))
				{
					$valor = 1;
					$id_prot = $pgrow['vb_tr_id'];
					if(strlen($id_prot)<5) {
						$id_prot = str_pad($id_prot, (5-strlen($id_prot)),"0",STR_PAD_RIGHT);
					}
					$venda_color = (($pgrow['vb_comissao_lan']>0)?"#FF0000":"#666666");
			?>

		<tr> 
          <td bgcolor="<?php echo $cor1 ?>"> <font color="<?php echo $venda_color?>" size="1" face="Arial, Helvetica, sans-serif"><a href="pquery.php?usuarioId=<?php echo $pgrow['vb_ug_id']?>"><?php echo $pgrow['vb_id'] ?></a></font></td>
          <td bgcolor="<?php echo $cor1 ?>" align="center"> <font color="<?php echo $venda_color?>" size="1" face="Arial, Helvetica, sans-serif"><?php echo $id_prot ?></font></td>
          <td bgcolor="<?php echo $cor1 ?>" align="center"> <font color="<?php echo $venda_color?>" size="1" face="Arial, Helvetica, sans-serif"><?php echo formata_data_ts($pgrow['vb_data_venda'], 0, true, false) ?></font></td>
          <td bgcolor="<?php echo $cor1 ?>"> <font color="<?php echo $venda_color?>" size="1" face="Arial, Helvetica, sans-serif"><?php echo $pgrow['vb_teatro'] ?></font></td>
          <td bgcolor="<?php echo $cor1 ?>"> <font color="<?php echo $venda_color?>" size="1" face="Arial, Helvetica, sans-serif"><?php echo $pgrow['vb_espetaculo'] ?></font></td>
          <td bgcolor="<?php echo $cor1 ?>"> <font color="<?php echo $venda_color?>" size="1" face="Arial, Helvetica, sans-serif"><?php echo $pgrow['vb_ug_id'] ?></font></td>
          <td bgcolor="<?php echo $cor1 ?>"> <font color="<?php echo $venda_color?>" size="1" face="Arial, Helvetica, sans-serif"><?php echo $pgrow['vb_lan_nome'] ?></font></td>
          <td bgcolor="<?php echo $cor1 ?>"> <font color="<?php echo $venda_color?>" size="1" face="Arial, Helvetica, sans-serif"><?php echo $pgrow['vb_cliente_nome'] ?></font></td>
          <td bgcolor="<?php echo $cor1 ?>"> <font color="<?php echo $venda_color?>" size="1" face="Arial, Helvetica, sans-serif"><?php echo $pgrow['vb_forma_pagto'] ?></font></td>
          <td bgcolor="<?php echo $cor1 ?>" align="right"> <font color="<?php echo $venda_color?>" size="1" face="Arial, Helvetica, sans-serif"><?php echo number_format($pgrow['vb_preco_total'], 2, ',','.') ?></font></td>
          <td bgcolor="<?php echo $cor1 ?>" align="right"> <font color="<?php echo $venda_color?>" size="1" face="Arial, Helvetica, sans-serif"><?php echo number_format($pgrow['vb_comissao_lan'], 2, ',','.') ?></font></td>
        </tr>
        <?php
					if ($cor1 == $cor2) {$cor1 = $cor3;} else {$cor1 = $cor2;} 
				}
				if (!$valor)
				{
			?>
        <tr bgcolor="#f5f5fb"> 
          <td colspan="4" bgcolor="<?php echo $cor1 ?>"><div align="center"><font size="1" face="Arial, Helvetica, sans-serif" color="#666666"><strong><br>
              N&atilde;o h&aacute; registros.<br>
              <br>
              </strong></font></div></td>
        </tr>
        <?php  } else {  ?>
        <?php
			$time_end = getmicrotime();
			$time = $time_end - $time_start;
		?>
        <tr> 
          <td colspan="7" bgcolor="#FFFFFF"><font size="1" face="Arial, Helvetica, sans-serif" color="#666666"><?php echo $search_msg . number_format($time, 2, '.', '.') . $search_unit ?> 
            </font></td>
        </tr>
        <?php
			paginacao_query($inicial, $total_table, $max, '4', $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, '');
		?>
        <?php } ?>
      </table>
      <p>
      <p>&nbsp; </p>
      <p></p>
      &nbsp; 
      <?php
      	require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
      ?>
    </td>
  </tr>
</table>
</body>
</html>