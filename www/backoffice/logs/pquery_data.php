<?php
    require_once '../../includes/constantes.php';
    require_once $raiz_do_projeto."backoffice/includes/topo.php";
    require_once $raiz_do_projeto."public_html/sys/includes/language/eprepag_lang_pt.inc.php";

    $pos_pagina = isset($seg_auxilar) ? $seg_auxilar : null;
    
	$time_start = getmicrotime();

	$ncamp       = 'log_data';
	if(!isset($inicial) || !$inicial)  $inicial     = 0;
	$range       = 1;
	if(!isset($ordem) || !$ordem)    $ordem       = 0;
    
	if(isset($BtnSearch) && $BtnSearch=="Buscar") {
		$inicial     = 0;
		$range       = 1;
		$total_table = 0;
	}
	
	$default_add  = nome_arquivo($PHP_SELF);
	$img_proxima  = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/proxima.gif";
	$img_anterior = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/anterior.gif";
	$max          = $qtde_reg_tela;
	$range_qtde   = $qtde_range_tela;
	
	$resuf = pg_exec($connid, "select uf from uf order by uf");

	$sql  = " select log_data, count(*) as n from bko_access_log group by log_data ";
	$res_count = pg_query($sql);
	$total_table = pg_num_rows($res_count);

	$sql .= " order by ".$ncamp;
	
	if($ordem == 0)
	{
		$sql .= " desc ";
	}
	else
	{
		$sql .= " asc ";
	}
    
	$resid = pg_exec($connid, $sql);
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
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>

<table class="table txt-preto fontsize-pp">
    <td height="232">
        <?php if($total_table > 0) { ?>
        <table class="table txt-preto fontsize-pp">
                <tr> 
                  <td height="10" bgcolor=""><div align="left"><font color="#666666" size="1" face="Arial, Helvetica, sans-serif"> 

                      Exibindo <strong><?php echo $total_table ?></strong> resultados.</font> 
</td>
                </tr>
            </table>
        <?php } ?>
	  <table class="table table bordered">
        <tr> 
          <?php
			if($ordem == 1)
				$ordem = 0;
			else
				$ordem = 1;
		?>
          <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=log_data&inicial=".$inicial ?>"> Data </a> 
            <?php //if($ncamp == 'log_data') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
            </strong></td>
          <td align="center"><strong> &nbsp; 
            </strong></td>
          <td align="center"><strong> N 
            <?php //if($ncamp == 'shn_nome') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
            </strong></td>
        </tr>
          <?php
				$cor1 = $query_cor1; 
				$cor2 = $query_cor1;
				$cor3 = $query_cor2;
				while ($pgrow = pg_fetch_array($resid)) {
					$valor = 1;

					$bgcolor = "#666666";
					if(get_day_of_week($pgrow['log_data'])=="2aF") $bgcolor="#FF0000";
			?>
        <tr> 
          <td bgcolor="<?php echo $cor1 ?>" align="center"> <font color="<?=$bgcolor?>" size="2" face="Arial, Helvetica, sans-serif"><?php echo formata_data($pgrow['log_data'], 0) ?></font></td>
          <td bgcolor="<?php echo $cor1 ?>" align="center"> <font color="<?=$bgcolor?>" size="2" face="Arial, Helvetica, sans-serif"><?php echo get_day_of_week($pgrow['log_data']) ?></font></td>
          <td bgcolor="<?php echo $cor1 ?>" align="center"><font color="<?=$bgcolor?>" size="2" face="Arial, Helvetica, sans-serif"><?php echo $pgrow['n'] ?></font></td>
        </tr>
        <?php
					if ($cor1 == $cor2) {$cor1 = $cor3;} else {$cor1 = $cor2;} 
				}
				if (!$valor) {
			?>
        <tr bgcolor="#f5f5fb"> 
          <td colspan="4" bgcolor="<?php echo $cor1 ?>"><div align="center"><font size="2" face="Arial, Helvetica, sans-serif" color="#666666"><strong><br>
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
      <?php
      	require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
      ?>
    </td>
  </tr>
</table>
</body>
</html>