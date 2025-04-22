<?php
	require_once '../../../../../../includes/constantes.php';
        require_once $raiz_do_projeto."backoffice/includes/topo.php";
	$pos_pagina = isset($seg_auxilar) ? $seg_auxilar : '';
    $time_start = getmicrotime();
	
	if(!isset($tf_data_inic) || !$tf_data_inic) $tf_data_inic = date('d/m/Y');
	if(!isset($tf_data_final) || !$tf_data_final) $tf_data_final = date('d/m/Y');

	$default_add  = nome_arquivo($PHP_SELF);

	$qtde_dias_vendas = qtde_dias($tf_data_inic, $tf_data_final) + 1;
  require_once "/www/includes/bourls.php";
?>
<link href="<?php echo $url; ?>:<?php echo $server_port; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>:<?php echo $server_port; ?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $url;?>:<?php echo $server_port; ?>/js/global.js"></script>
<script language="javascript">
    $(function(){
       var optDate = new Object();
            optDate.interval = 10000;

        setDateInterval('tf_data_inic','tf_data_final',optDate);
        
    });
    
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
        <li class="active">Vendas por HORA</li>
    </ol>
</div>
<table class="table txt-preto fontsize-pp">
  <tr> 
    <td height="200" align="center" valign="top" bgcolor="#FFFFFF"> <form name="form1" method="post" action="">
        <table class="table">
          <tr bgcolor="#ECE9D8"> 
            <td colspan="7"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong>Pesquisa</strong></font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="148"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">Intervalo 
              de Datas:</font></td>
            <td width="1038" bgcolor="#F5F5FB"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
              <input name="tf_data_inic" type="text" class="form" id="tf_data_inic" value="<?php echo $tf_data_inic ?>" size="9" maxlength="10">
              - 
              <input name="tf_data_final" type="text" class="form" id="tf_data_final" value="<?php echo $tf_data_final ?>" size="9" maxlength="10">
              </font> </td>
            <td width="57"><div align="center"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
                <input type="submit" name="BtnSearch" value="Buscar" class="btn btn-sm btn-info">
                </font></div></td>
          </tr>
        </table>
            <table class="table">
          <tr> 
            <td bgcolor=""><font color="#666666" size="1" face="Arial, Helvetica, sans-serif"><strong><?php echo $qtde_dias_vendas ?> 
              dia 
              <?php if($qtde_dias_vendas > 1) echo "s" ?>
              </strong></font> </td>
            <td bgcolor=""><div align="right"><a href="../../pquery.php"><img src="/images/voltar_menu.gif" width="107" height="15" border="0"></a></div></td>
          </tr>
        </table>
            <table class="table">
          <tr bgcolor="#00008C"> 
            <td><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif" class="link_br">Range</font></strong></td>
            <td><div align="right"><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif" class="link_br">Qtde</font></strong></div></td>
            <td><div align="right"><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif">M&eacute;dia 
                Qtde</font></strong></div></td>
            <td><div align="right"><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif">Valor 
                Total</font></strong></div></td>
            <td><div align="right"><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif">M&eacute;dia 
                Valor</font></strong></div></td>
          </tr>
          <?php
					$cor1 = $query_cor1;
					$cor2 = $query_cor1;
					$cor3 = $query_cor2;

                    if(!isset($pin_total_valor))
                        $pin_total_valor = null;
                    
                    if(!isset($pin_total_qtde))
                        $pin_total_qtde = null;
                    
                    if(!isset($pin_total_valor))
                        $pin_total_valor = null;
                    
                    if(!isset($pin_total_media_valor))
                        $pin_total_media_valor = null;
                    
                    if(!isset($pin_total_media_qtde))
                        $pin_total_media_qtde = null;

					for ($i = 0 ; $i <= 23 ; $i++)
					{
						if($i < 10)
							$i = '0' . $i;
							
						$sql  = "select count(trn_hora) as quantidade, sum(pin_valor) as total from estat_venda ";
						$sql .= "where (trn_data>='".formata_data($tf_data_inic, 1)."' and trn_data<='".formata_data($tf_data_final, 1)."') and trn_hora>='" . $i . ":00:00' and trn_hora<= '" . $i . ":59:59' ";

						if(!isset($cb_opr_teste) || !$cb_opr_teste)
							$sql .= "and opr_codigo <> ".$opr_teste." ";

						$resven = pg_exec($connid, $sql);
						$pgven = pg_fetch_array($resven);
						
						$media_qtde = $pgven['quantidade'] / $qtde_dias_vendas;
						$media_valor = $pgven['total'] / $qtde_dias_vendas;

						$pin_total_valor += $pgven['total'];
						$pin_total_qtde += $pgven['quantidade'];
						
						$pin_total_media_valor += $media_valor;
						$pin_total_media_qtde += $media_qtde;
				?>
          <tr bgcolor="#f5f5fb"> 
            <td bgcolor="<?php echo $cor1 ?>"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo "das ".$i.":00:00 as ".$i.":59:59" ?></font></td>
            <td bgcolor="<?php echo $cor1 ?>"><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo number_format($pgven['quantidade'], 0, ',', '.') ?></font></div></td>
            <td bgcolor="<?php echo $cor1 ?>"><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo number_format($media_qtde, 2, ',', '.') ."/dia" ?></font></div></td>
            <td bgcolor="<?php echo $cor1 ?>"><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo number_format($pgven['total'], 2, ',', '.') ?></font></div></td>
            <td bgcolor="<?php echo $cor1 ?>"><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo number_format($media_valor, 2, ',', '.') ."/dia" ?></font></div></td>
          </tr>
          <?php
				 		if ($cor1==$cor2) {$cor1=$cor3;} else {$cor1=$cor2;} 			  
					}
				?>
          <tr bgcolor="#E4E4E4"> 
            <td><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong>TOTAL</strong></font></td>
            <td><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong><?php echo number_format($pin_total_qtde, 0, ',', '.') ?></strong></font></div></td>
            <td><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong><?php echo number_format($pin_total_media_qtde, 2, ',', '.') ."/dia" ?></strong></font></div></td>
            <td><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong><?php echo number_format($pin_total_valor, 2, ',', '.') ?></strong></font></div></td>
            <td><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong><?php echo number_format($pin_total_media_valor, 2, ',', '.') ."/dia" ?></strong></font></div></td>
          </tr>
          <?php
					$time_end = getmicrotime();
					$time = $time_end - $time_start;
				?>
          <tr bgcolor="#E4E4E4"> 
            <td colspan="5" bgcolor="#FFFFFF"><font color="#666666" size="1" face="Arial, Helvetica, sans-serif"><strong> 
              OBS: Valores expressos em R$. </strong></font></td>
          </tr>
          <tr> 
            <td colspan="5" bgcolor="#FFFFFF"><p><font size="1" valign= top><?php echo $search_msg . number_format($time, 2, '.', '.') . $search_unit ?> 
                </font> 
            </td>
          </tr>
        </table>
      </form></td>
  </tr>
</table>
   </body>
  <p></p> 
<?php
	require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>      
</html>