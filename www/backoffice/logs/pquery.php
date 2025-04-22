<?php
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
$pos_pagina = isset($seg_auxilar) ? $seg_auxilar : null;

	$time_start = getmicrotime();

	if(!isset($ncamp) || !$ncamp)    $ncamp       = 'log_data';
	if(!isset($inicial) || !$inicial)  $inicial     = 0;
	if(!isset($range) || !$range)    $range       = 1;
	if(!isset($ordem) || !$ordem)    $ordem       = 0;
//	if($BtnSearch) $inicial     = 0;
//	if($BtnSearch) $range       = 1;
//	if($BtnSearch) $total_table = 0;
	if(isset($BtnSearch) && $BtnSearch=="Buscar") {
		$inicial     = 0;
		$range       = 1;
		$total_table = 0;
	}
	
	$default_add  = nome_arquivo($PHP_SELF);
	$img_proxima  = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/proxima.gif";
	$img_anterior = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/anterior.gif";
	$max          = 4000; //$qtde_reg_tela;
	$range_qtde   = $qtde_range_tela;
	
	$resuf = pg_exec($connid, "select uf from uf order by uf");

	$sql  = " select log_data, log_hora, log_ip, shn_nome from bko_access_log, usuarios where log_user_id=id";
	$res_count = pg_query($sql);
	$total_table = pg_num_rows($res_count);

	if($ncamp == "log_hora") {
		$ncamp == "log_data";
	}
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
	if($ncamp == "log_data") {
		$sql .= ", log_hora desc";
	}
//echo $sql."<br>";

	$sql .= " limit ".$max; 
	$sql .= " offset ".$inicial;

	$resid = pg_exec($connid, $sql);
	
	if($max + $inicial > $total_table)
		$reg_ate = $total_table;
	else
		$reg_ate = $max + $inicial;
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

<div class="col-md-12">
<table class="txt-preto fontsize-pp">
  <tr> 
    <td>
        <?php if($total_table > 0) { ?>
        <table class="">
            <tr> 
                <td height="10" bgcolor="">
                    Exibindo resultados <strong><?php echo $inicial + 1 ?></strong> a <strong><?php echo $reg_ate ?></strong> 
                    de <strong><?php echo $total_table ?></strong>
                </td>
            </tr>
        </table>
        <?php }  ?>
        <table class="table table-bordered">
            <tr class="bg-cinza-claro txt-preto"> 
<?php
                if($ordem == 1)
                    $ordem = 0;
                else
                    $ordem = 1;
?>
                <td>
                    <strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=log_data&inicial=".$inicial ?>">Data</a> 
                    <?php if($ncamp == 'log_data') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                    </strong>
                </td>
                <td>
                    <div align="left"><strong><span class="link_br">Hora</span>
                    <?php if($ncamp == 'log_hora') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                  </strong></div>
                </td>
                <td>
                    <div align="left"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=log_ip&inicial=".$inicial ?>">IP</a> 
                    <?php if($ncamp == 'log_ip') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                    </strong></div>
                </td>
                <td>
                    <strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=shn_nome&inicial=".$inicial ?>">Usu&aacute;rio</a> 
                    <?php if($ncamp == 'shn_nome') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                    </strong>
                </td>
            </tr>
<?php
            $cor1 = $query_cor1; 
            $cor2 = $query_cor1;
            $cor3 = $query_cor2;
            while ($pgrow = pg_fetch_array($resid))
            {
                $valor = 1;
?>
            <tr> 
                <td bgcolor="<?php echo $cor1 ?>"><?php echo formata_data($pgrow['log_data'], 0) ?></td>
                <td bgcolor="<?php echo $cor1 ?>"><div align="left"><?php echo $pgrow['log_hora'] ?></div></td>
                <td bgcolor="<?php echo $cor1 ?>"><div align="left"><?php echo $pgrow['log_ip'] ?></div></td>
                <td bgcolor="<?php echo $cor1 ?>"><?php echo $pgrow['shn_nome'] ?></td>
            </tr>
        <?php
					if ($cor1 == $cor2) {$cor1 = $cor3;} else {$cor1 = $cor2;} 
            }
				
            if (!$valor)
            {
?>
            <tr bgcolor="#f5f5fb"> 
                <td colspan="4" bgcolor="<?php echo $cor1 ?>"><div align="center"><strong><br>
                    N&atilde;o h&aacute; registros.<br>
                    <br>
                    </strong></div>
                </td>
            </tr>
<?php  
            } else {

                $time_end = getmicrotime();
                $time = $time_end - $time_start;
?>
        <tr> 
          <td colspan="7" bgcolor="#FFFFFF"><?php echo $search_msg . number_format($time, 2, '.', '.') . $search_unit ?> 
            </td>
        </tr>
        <?php
			paginacao_query($inicial, $total_table, $max, '4', $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, '');
		?>
        <?php } ?>
      </table>
    </td>
  </tr>
</table>
</div>
<?php
      	require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
      ?>
    </td>
</body>
</html>