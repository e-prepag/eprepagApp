<?php
	require_once '../../../../../../includes/constantes.php';
        require_once $raiz_do_projeto."backoffice/includes/topo.php";
        
    if(!isset($seg_auxilar))
        $seg_auxilar = null;
    
	$pos_pagina = $seg_auxilar;

    $time_start = getmicrotime();

    
    if(!isset($ncamp))
        $ncamp = null;
    
    if(!isset($tf_data_final))
        $tf_data_final = null;
    
    if(!isset($tf_data_inicial))
        $tf_data_inicial = null;
    
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
    
    if(!isset($cb_estab_teste))
        $cb_estab_teste = null;
    
    if(!isset($tf_codigo_estab))
        $tf_codigo_estab = null;
    
    if(!isset($tf_nome_estab))
        $tf_nome_estab = null;
    
    if(!isset($dd_uf))
        $dd_uf = null;
    
    if(!isset($dd_uf_except))
        $dd_uf_except = null;
    
    if(!isset($dd_operadora))
        $dd_operadora = null;
    
    if(!isset($dd_valor))
        $dd_valor = null;
    
    if(!isset($dd_opr_area))
        $dd_opr_area = null;
    
    if(!isset($cb_opr_teste))
        $cb_opr_teste = null;
    
    if(!isset($cb_estab_teste))
        $cb_estab_teste = null;
    
    if(!isset($tf_codigo_estab))
        $tf_codigo_estab = null;
    
    if(!isset($tf_nome_estab))
        $tf_nome_estab = null;
    
    if(!isset($dd_uf))
        $dd_uf = null;
    
    if(!isset($dd_uf_except))
        $dd_uf_except = null;
    
    if(!isset($data_inic_invalida))
        $data_inic_invalida = null;
    
    if(!isset($data_fim_invalida))
        $data_fim_invalida = null;
    
    if(!isset($data_inicial_menor))
        $data_inicial_menor = null;
    
    if(!isset($valor))
        $valor = null;
    
    
    
    
    
    
	if(!$ncamp)            $ncamp           = 'trn_data';
	if(!$tf_data_final)    $tf_data_final   = date('d/m/Y');
	if(!$tf_data_inicial)  $tf_data_inicial = date('d/m/Y');
	if(!$inicial)          $inicial         = 0;
	if(!$range)            $range           = 1;
	if(!$ordem)            $ordem           = 0;

    if($BtnSearch=="Buscar") {
		$inicial     = 0;
		$range       = 1;
		$total_table = 0;
	}


	$data_inicial_limite = '01/08/2004';
	$FrmEnviar = 1;
	
	
	$default_add  = nome_arquivo($PHP_SELF);
	$img_proxima  = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/proxima.gif";
	$img_anterior = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/anterior.gif";
	$max          = $qtde_reg_tela;
	$range_qtde   = $qtde_range_tela;

		$sql = "select est_codigo, razao_social, nome_fantasia,  ";

	if($FrmEnviar == 1)
	{
		$estat  = "select t0.trn_data, t1.uf, t2.opr_nome, t0.pin_valor, count(t0.pin_valor) as quantidade, sum(t0.pin_valor) as total_face ";
		$estat .= "from estat_venda t0, estabelecimentos t1, operadoras t2 ";
		$estat .= "where (t0.est_codigo=t1.est_codigo) and (t0.opr_codigo=t2.opr_codigo) ";		

		if(!$cb_opr_teste)
			$estat .= "and (t0.opr_codigo <> ".$opr_teste.") ";
			
		if(!$cb_estab_teste)
			$estat .= "and (t1.est_teste = 0) ";
			
		if($tf_data_inicial && $tf_data_final) 
		{
			$data_inic = formata_data(trim($tf_data_inicial), 1);
			$data_fim = formata_data(trim($tf_data_final), 1); 
			$estat .= " and ((trn_data >= '".trim($data_inic)."') and (trn_data <= '".trim($data_fim)."')) "; 
		}

		if($tf_codigo_estab)
			$estat .= "and (t1.est_codigo = ".strtoupper($tf_codigo_estab).") ";

		if($tf_nome_estab)
			$estat .= "and (t1.nome_fantasia like '%".strtoupper($tf_nome_estab)."%') ";
		
		if($dd_uf)
			$estat .= " and (t1.uf = '".$dd_uf."') ";
			
		if($dd_uf_except)
			$estat .= " and (t1.uf <> '".$dd_uf_except."') ";
			
		if($dd_operadora)
			$estat .= " and (t0.opr_codigo = ".$dd_operadora.") ";

		if($dd_valor)
			$estat .= " and (t0.pin_valor = ".$dd_valor.") ";
		
		if($dd_opr_area)
		{
			$ddd_line = "";
			$res_area = pg_exec($connid, "select ddd from operadora_area_ddd where oparea_codigo = ".$dd_opr_area."");

			$cont = 0;
			while($pg_area = pg_fetch_array($res_area))
			{
				if($cont != 0) $ddd_line .= ", " ;
				$ddd_line .= $pg_area['ddd'];
				$cont++;
			}

			$estat .= " and t0.ddd in (".$ddd_line.") ";
		}

		$estat .= "group by t0.trn_data, t1.uf, t2.opr_nome, t0.pin_valor";

		$res_count = pg_query($estat);
		$total_table = pg_num_rows($res_count);
	
		$estat .= " order by ".$ncamp; 

		if($ordem == 0)
		{
			$estat .= " desc ";
			$img_seta = "../../../images/seta_down.gif";	
		}
		else
		{
			$estat .= " asc ";
			$img_seta = "../../../images/seta_up.gif";
		}

		$qtde_geral = 0;
		$valor_geral = 0;

		$res_geral = pg_exec($connid, $estat);
		while($pg_geral = pg_fetch_array($res_geral))
		{
			$qtde_geral += $pg_geral['quantidade'];
			$valor_geral += $pg_geral['total_face'];
		}

		$estat .= " limit ".$max; 
		$estat .= " offset ".$inicial;

	}
	else
		$estat = "select est_codigo from estabelecimentos where est_codigo = 0";
		
//	trace_sql($estat, "Arial", 2, "#666666", 'b');
//echo $estat;
	
	$resestat = pg_exec($connid, $estat);

	if($max + $inicial > $total_table)
		$reg_ate = $total_table;
	else
		$reg_ate = $max + $inicial;
		
	$varsel  = "&cb_opr_teste=$cb_opr_teste&&cb_estab_teste=$cb_estab_teste";
	$varsel .= "&tf_data_final=$tf_data_final&tf_data_inicial=$tf_data_inicial";
	$varsel .= "&tf_codigo_estab=$tf_codigo_estab&tf_nome_estab=$tf_nome_estab&dd_uf=$dd_uf&dd_uf_except=$dd_uf_except";
	$varsel .= "&dd_operadora=$dd_operadora&dd_valor=$dd_valor&dd_opr_area=$dd_opr_area";
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
        <li class="active">Vendas por PDV</li>
    </ol>
</div>
<table class="table txt-preto fontsize-pp">
  <tr> 
    <td width="902" align="center" valign="top" bgcolor="#FFFFFF"> <table class="table" width="100%" border="0" cellspacing="0" cellpadding="3" height="100%">
        <tr valign="top"> 
          <td height="620"> <form name="form1" method="post" action="">
              <table class="table">
                <tr> 
                  <td class="bg-azul-claro" colspan="9"><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><strong>Vendas 
                    por PDV</strong></font></td>
                </tr>
                <tr bgcolor="#ECE9D8"> 
                  <td colspan="9"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong>Pesquisa</strong></font></td>
                </tr>
                <tr bgcolor="#F5F5FB"> 
                  <td width="99"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">C&oacute;digo:</font></td>
                  <td width="135"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
                    <input name="txtestcod" type="text" id="txtestcod" size="12" maxlength="6">
                    </font></td>
                  <td width="99"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">C&oacute;digo 
                    Amex:</font><br></td>
                  <td width="464"> <font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
                    <input name="txtestcodamex" type="text" id="txtestcodamex" size="15" maxlength="10">
                    </font> <font color="#666666" size="2" face="Arial, Helvetica, sans-serif">&nbsp; 
                    </font></td>
                  <td width="61"><div align="center"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
                      </font></div></td>
                </tr>
                <tr bgcolor="#F5F5FB"> 
                  <td><div align="center"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">Nome 
                      Fantasia: </font></div></td>
                  <td colspan="2"><input name="txtnomfan" type="text" id="txtnomfan4"></td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                </tr>
                <tr bgcolor="#F5F5FB"> 
                  <td colspan="4">&nbsp;</td>
                  <td><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
                    <input type="submit" name="BtnSearch" value="Buscar" class="btn btn-sm btn-info">
                    </font></td>
                </tr>
              </table>
            </form>
            <table class="table">
              <tr> 
                <td> 
                  <?php
					if($data_inic_invalida == true) echo "<br><font face='Arial, Helvetica, sans-serif' size='2' color='#FF0000'><b>Data inicial Inválida</b></font>";
					if($data_fim_invalida == true) echo "<br><font face='Arial, Helvetica, sans-serif' size='2' color='#FF0000'><b>Data final Inválida</b></font>";
					if($data_inicial_menor == true) echo "<br><font face='Arial, Helvetica, sans-serif' size='2' color='#FF0000'><b>Data inicial menor do que a Data limite</b></font>";
				?>
                </td>
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
                <td bgcolor=""></td>
              </tr>
            </table>
            <table class="table" >
              <tr class="bg-azul-claro"> 
                <?php
				if($ordem == 1)
					$ordem = 0;
				else
					$ordem = 1;
				?>
                <td width="14%" height="50%"><div align="center"><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif align"><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=trn_data&inicial=".$inicial.$varsel ?>" class="txt-branco">Data</a></font></strong> 
                    <?php if($ncamp == 'trn_data') echo "<img src=".$img_seta." width='9' height='6' align='absmiddle'>"; ?>
                  </div></td>
                <td width="11%" height="50%"><div align="center"><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=uf&inicial=".$inicial.$varsel ?>" class="txt-branco">UF</a></font></strong> 
                    <?php if($ncamp == 'uf') echo "<img src=".$img_seta." width='9' height='6' align='absmiddle'>"; ?>
                  </div></td>
                <td width="20%" height="50%"><div align="center"><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=opr_nome&inicial=".$inicial.$varsel ?>" class="txt-branco">Operadora</a></font></strong> 
                    <?php if($ncamp == 'opr_nome') echo "<img src=".$img_seta." width='9' height='6' align='absmiddle'>"; ?>
                  </div></td>
                <td width=14% height="50%"> <strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"> 
                  </font></strong> <div align="center"><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=quantidade&inicial=".$inicial.$varsel ?>" class="txt-branco"> 
                    </a> 
                    <?php if($ncamp == 'quantidade') echo "<img src=".$img_seta." width='9' height='6' align='absmiddle'>"; ?>
                    <a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=quantidade&inicial=".$inicial.$varsel ?>" class="txt-branco">Qtde</a></font></strong></div></td>
                <td width="20%" height="50%"> <strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"> 
                  </font></strong> <div align="center"><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=pin_valor&inicial=".$inicial.$varsel ?>" class="txt-branco"> 
                    <?php if($ncamp == 'pin_valor') echo "<img src=".$img_seta." width='9' height='6' align='absmiddle'>"; ?>
                    Valor da Face</a></font></strong></div></td>
                <td width="21%" height="50%"> <strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"> 
                  </font></strong> <div align="center"><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=total_face&inicial=".$inicial.$varsel ?>" class="txt-branco"> 
                    </a> 
                    <?php if($ncamp == 'total_face') echo "<img src=".$img_seta." width='9' height='6' align='absmiddle'>"; ?>
                    <a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=total_face&inicial=".$inicial.$varsel ?>" class="txt-branco">Valor 
                    Total</a></font></strong></div></td>
              </tr>
              <?php
					$cor1 = $query_cor1;
					$cor2 = $query_cor1;
					$cor3 = $query_cor2;
					while ($pgrow = pg_fetch_array($resestat))
					{
						$valor = true;

						$valor_total_tela += $pgrow['total_face'];
						$qtde_total_tela += $pgrow['quantidade'];
				?>
              <tr bgcolor="#f5f5fb"> 
                <td bgcolor="<?php echo $cor1 ?>"><div align="left"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo formata_data($pgrow['trn_data'], 0) ?></font></div></td>
                <td bgcolor="<?php echo $cor1 ?>"><div align="left"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo $pgrow['uf'] ?></font></div></td>
                <td bgcolor="<?php echo $cor1 ?>"><div align="left"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo $pgrow['opr_nome'] ?></font></div></td>
                <td bgcolor="<?php echo $cor1 ?>"><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo $pgrow['quantidade']?></font></div></td>
                <td bgcolor="<?php echo $cor1 ?>"><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo number_format($pgrow['pin_valor'], 2, ',', '.') ?></font></div></td>
                <td bgcolor="<?php echo $cor1 ?>"><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo number_format($pgrow['total_face'], 2, ',', '.') ?></font></div></td>
              </tr>
              <?php
				 		if($cor1 == $cor2)
							$cor1 = $cor3;
						else
							$cor1 = $cor2;
					}
			 		if (!$valor) { ?>
              <tr bgcolor="#f5f5fb"> 
                <td colspan="6" bgcolor="<?php echo $cor1 ?>"><div align="center"><font size="2" face="Arial, Helvetica, sans-serif" color="#666666"><strong><br>
                    N&atilde;o h&aacute; registros.<br>
                    <br>
                    </strong></font></div></td>
              </tr>
              <?php } else { ?>
              <tr bgcolor="#E4E4E4"> 
                <td colspan="3"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong>SUBTOTAL</strong></font></td>
                <td><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong><?php echo number_format($qtde_total_tela, 0, ',', '.') ?></strong></font></div></td>
                <td colspan="2"><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong><?php echo number_format($valor_total_tela, 2, ',', '.') ?></strong></font></div></td>
              </tr>
              <?php
					$time_end = getmicrotime();
					$time = $time_end - $time_start;
					paginacao_query($inicial, $total_table, $max, '9', $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel);
				?>
              <tr> 
                <td colspan="6">&nbsp;</td>
              </tr>
              <tr bgcolor="#E4E4E4"> 
                <td colspan="3"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong>TOTAL</strong></font></td>
                <td><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong><?php echo number_format($qtde_geral, 0, ',', '.') ?></strong></font></div></td>
                <td colspan="2"><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong><?php echo number_format($valor_geral, 2, ',', '.') ?></strong></font></div></td>
              </tr>
              <tr bgcolor="#E4E4E4"> 
                <td colspan="6" bgcolor="#FFFFFF"><font color="#666666" size="1" face="Arial, Helvetica, sans-serif"><strong> 
                  OBS: Valores expressos em R$. </strong></font></td>
              </tr>
              <tr> 
                <td colspan="6" bgcolor="#FFFFFF"><font size="1" face="Arial, Helvetica, sans-serif" color="#666666"><?php echo $search_msg . number_format($time, 2, '.', '.') . $search_unit ?> 
                  </font></td>
              </tr>
              <?php } ?>
            </table>
            <p><font size="1" face="Arial, Helvetica, sans-serif" color="#666666"> 
              <?php //include "../../../incs/rodape_bko.php";?>
              </font></p></td>
        </tr>
      </table></td>
  </tr>
</table>
</body>
</html>
<?php
	require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";