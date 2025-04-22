<?php
  	require_once '../../../../../includes/constantes.php';
        require_once $raiz_do_projeto."backoffice/includes/topo.php";
	
	$pos_pagina = isset($seg_auxilar) ? $seg_auxilar : null;
	$time_start = getmicrotime();

	if(!isset($ncamp) || !$ncamp)            $ncamp           = 'trn_data';
	if(!isset($tf_data_final) || !$tf_data_final)    $tf_data_final   = date('d/m/Y');
	if(!isset($tf_data_inicial) || !$tf_data_inicial)  $tf_data_inicial = date('d/m/Y');
	if(!isset($inicial) || !$inicial)          $inicial         = 0;
	if(!isset($range) || !$range)            $range           = 1;
	if(!isset($ordem) || !$ordem)            $ordem           = 0;
    
	if(isset($BtnSearch) && $BtnSearch=="Buscar") {
		$inicial     = 0;
		$range       = 1;
		$total_table = 0;
	}

	$data_inicial_limite = data_menos_n(date('d/m/Y'), 120);
	$data_inicial_limite = '01/08/2004';
	$FrmEnviar = 1;
	
	
	$default_add  = nome_arquivo($PHP_SELF);
	$img_proxima  = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/proxima.gif";
	$img_anterior = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/anterior.gif";
	$max          = $qtde_reg_tela;
	$range_qtde   = $qtde_range_tela;

	$resuf = pg_exec($connid, "select uf from uf order by uf");
	$resuf_except = pg_exec($connid, "select uf from uf order by uf");

	if(isset($cb_opr_teste) && $cb_opr_teste)
		$resopr = pg_exec($connid, "select opr_nome, opr_codigo from operadoras where (opr_status = '".$operadora_ativada."') order by opr_ordem");
	else
		$resopr = pg_exec($connid, "select opr_nome, opr_codigo from operadoras where (opr_status = '".$operadora_ativada."') and (opr_codigo <> ".$opr_teste.") order by opr_ordem");

	if(isset($dd_operadora) && $dd_operadora)
	{			
		$res_opr_info = pg_exec($connid, "select opr_codigo, opr_pin_online from operadoras where opr_codigo=".$dd_operadora."");
		$pg_opr_info = pg_fetch_array($res_opr_info);
	
		if($pg_opr_info['opr_pin_online'] == 0)
			$resval = pg_exec($connid, "select pin_valor as valor from pins where opr_codigo='".$pg_opr_info['opr_codigo']."' and pin_canal='s' group by pin_valor order by pin_valor");
		else
		{
			$resval = pg_exec($connid, "select valor_fixo as valor from pin_valor_lista t0, pin_valor_fixo t1 where t0.valor_lista_cod = t1.valor_lista_cod and opr_codigo = ".$pg_opr_info['opr_codigo']." group by valor_fixo order by valor_fixo");
			$res_opr_area = pg_exec($connid, "select oparea_codigo, area_nome from operadora_area where opr_codigo=".$pg_opr_info['opr_codigo']." order by oparea_codigo");
		}
	}

	if(!verifica_data($tf_data_inicial))
	{
		$data_inic_invalida = true;
		$FrmEnviar = 0;
	}

	if(!verifica_data($tf_data_final))
	{
		$data_fim_invalida = true;
		$FrmEnviar = 0;
	}
	
	if(qtde_dias($data_inicial_limite, $tf_data_inicial) < 0)
	{
		$data_inicial_menor = true;
		$FrmEnviar = 0;
	}

	if(isset($FrmEnviar) && $FrmEnviar == 1)
	{
		$estat  = "select t0.pin_serial, t0.trn_data, t0.trn_hora, t4.nome, t1.est_codigo, t1.nome_fantasia, t1.uf, t3.municipio, t2.opr_nome, t0.pin_valor, t0.ddd, t0.celular ";
		$estat .= "from changethis t0, estabelecimentos t1, operadoras t2, cidades t3, canais t4 ";
		$estat .= "where (t1.cidade=t3.cid_codigo) and (t4.canal_codigo = t1.canal_codigo) and (t0.est_codigo=t1.est_codigo) and (t0.opr_codigo=t2.opr_codigo) ";		

		if(!isset($cb_opr_teste) || !$cb_opr_teste)
			$estat .= "and (t0.opr_codigo <> ".$opr_teste.") ";
			
		if(!isset($cb_estab_teste) || !$cb_estab_teste)
			$estat .= "and (t1.est_teste = 0) ";
			
		if($tf_data_inicial && $tf_data_final) 
		{
			$data_inic = formata_data(trim($tf_data_inicial), 1);
			$data_fim = formata_data(trim($tf_data_final), 1); 
			$estat .= " and ((trn_data >= '".trim($data_inic)."') and (trn_data <= '".trim($data_fim)."')) "; 
		}

		if(isset($tf_codigo_estab) && $tf_codigo_estab)
			$estat .= "and (t1.est_codigo = ".strtoupper($tf_codigo_estab).") ";

		if(isset($tf_nome_estab) && $tf_nome_estab)
			$estat .= "and (t1.nome_fantasia like '%".strtoupper($tf_nome_estab)."%') ";
		
		if(isset($dd_uf) && $dd_uf)
			$estat .= " and (t1.uf = '".$dd_uf."') ";
			

		if(isset($tf_canal_venda) && $tf_canal_venda)
			$estat .= " and (t4.nome LIKE '%".$tf_canal_venda."%') ";

		if(isset($dd_uf_except) && $dd_uf_except)
			$estat .= " and (t1.uf <> '".$dd_uf_except."') ";
			
		if(isset($dd_operadora) && $dd_operadora)
			$estat .= " and (t0.opr_codigo = ".$dd_operadora.") ";

		if(isset($dd_valor) && $dd_valor)
			$estat .= " and (t0.pin_valor = ".$dd_valor.") ";
		
		if(isset($dd_opr_area) && $dd_opr_area)
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

//Mounting as Special Sub-select by Union Platform

$estatp1 = str_replace("changethis","estat_venda",$estat);
$estatp2 = str_replace("changethis","estat_venda_2004",$estat);
$estatp3 = str_replace("changethis","estat_venda_1sem05",$estat);
//$estat = "$estatp1 UNION $estatp2 UNION $estatp3 ";
$estat = $estatp1;
//--The End

		$res_count = pg_query($estat);
		$total_table = pg_num_rows($res_count);
	
		$estat .= " order by ".$ncamp; 


		if(isset($ordem) && $ordem == 0)
		{
			$estat .= " desc ";
			$img_seta = "/images/seta_down.gif";	
		}
		else
		{
			$estat .= " asc ";
			$img_seta = "/images/seta_up.gif";
		}

		$estat .= ",trn_hora desc";
		$estat .= " limit ".$max; 
		$estat .= " offset ".$inicial;
//		echo $estat;

	}
	else
		$estat = "select est_codigo from estabelecimentos where est_codigo = 0";

//echo $estat;		
//	trace_sql($estat, "Arial", 2, "#666666", 'b');
$sql_transform=$estat;
	
	$resestat = pg_exec($connid, $estat);

	if($max + $inicial > $total_table)
		$reg_ate = $total_table;
	else
		$reg_ate = $max + $inicial;
		
	@$varsel  = "&cb_opr_teste=$cb_opr_teste&&cb_estab_teste=$cb_estab_teste";
	@$varsel .= "&tf_data_final=$tf_data_final&tf_data_inicial=$tf_data_inicial";
	@$varsel .= "&tf_codigo_estab=$tf_codigo_estab&tf_nome_estab=$tf_nome_estab&dd_uf=$dd_uf&dd_uf_except=$dd_uf_except";
	@$varsel .= "&dd_operadora=$dd_operadora&dd_valor=$dd_valor&dd_opr_area=$dd_opr_area";
  require_once "/www/includes/bourls.php";
?>
<link href="<?php echo $url; ?>:<?php echo $server_port; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>:<?php echo $server_port; ?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $url;?>:<?php echo $server_port; ?>/js/global.js"></script>
<script language="javascript">
    $(function(){
       var optDate = new Object();
            optDate.interval = 10000;

        setDateInterval('tf_data_inicial','tf_data_final',optDate);
        
    });
<!--
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}
//-->
</script>
<SCRIPT LANGUAGE=JAVASCRIPT><!--
    function ShowPopupWindowXY(fileName,x,y) {
    myFloater = window.open('','myWindow','scrollbars=no,status=no,width=' + x + ',height=' + y)
    myFloater.location.href = fileName;
}
//--></SCRIPT>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="1">BackOffice - Money</a></li>
        <li><a href="../pquery.php">Relatórios de Venda</a></li>
        <li class="active">Estabelecimentos</li>
    </ol>
</div>
<table class="table txt-preto fontsize-pp">
  <tr> 
    <td align="center" valign="top" bgcolor="#FFFFFF">
        <form name="form1" method="post" action="">
        <table class="table">
          <tr bgcolor="#ECE9D8"> 
            <td colspan="11">Pesquisa</td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td colspan="6"><strong>Data</strong> 
            </td>
            <td width="57"><div align="center"> 
                </div></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="110" height="37">Datas 
              Inicial:</td>
            <td width="182"> 
              <input name="tf_data_inicial" type="text" class="form" id="tf_data_inicial" value="<?php echo $tf_data_inicial ?>" size="9" maxlength="10">
              </td>
            <td width="45">Data 
              Final:</td>
            <td colspan="3">  
              <input name="tf_data_final" type="text" class="form" id="tf_data_final" value="<?php echo $tf_data_final ?>" size="9" maxlength="10">
            </td>
            <td width="57"><div align="center"> 
                </div></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td colspan="6"><strong>Estabelecimento</strong> 
            </td>
            <td width="57"><div align="center"> 
                </div></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="110">C&oacute;digo:</td>
            <td width="182"> 
              <input name="tf_codigo_estab" type="text" class="form" id="tf_codigo_estab" value="<?php echo isset($tf_codigo_estab) ? $tf_codigo_estab : ''; ?>" size="7" maxlength="7">
              </td>
            <td width="45">Nome:</td>
            <td colspan="3">  
              <input name="tf_nome_estab" type="text" class="form" id="tf_nome_estab" value="<?php echo isset($tf_nome_estab) ? $tf_nome_estab : ''; ?>" size="30" maxlength="30">
              </td>
            <td width="57"><div align="center"> 
                </div></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="110">UF:</td>
            <td width="182"> 
              <select name="dd_uf" id="dd_uf" class="combo_normal">
                <option value="">Todos os Estados</option>
                <?php while ($pguf = pg_fetch_array ($resuf)) { ?>
                <option value="<?php echo $pguf['uf'] ?>" <?php if(isset($dd_uf) && $pguf['uf'] == $dd_uf) echo "selected" ?>><?php echo $pguf['uf'] ?></option>
                <?php } ?>
              </select>
              </td>
            <td width="45">Exceto:</td>
            <td colspan="3">  
              <select name="dd_uf_except" id="dd_uf_except" class="combo_normal">
                <option value="">Nenhum Estado</option>
                <?php while ($pguf = pg_fetch_array ($resuf_except)) { ?>
                <option value="<?php echo $pguf['uf'] ?>" <?php if(isset($dd_uf_except) && $pguf['uf'] == $dd_uf_except) echo "selected" ?>><?php echo $pguf['uf'] ?></option>
                <?php } ?>
              </select>
              </td>
            <td width="57"><div align="center"> 
                </div></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="110">Canal 
              de Venda:</td>
            <td colspan="5"> 
              <input name="tf_canal_venda" type="text" class="form" id="tf_canal_venda" value="<?php if(isset($tf_canal_venda)) echo $tf_canal_venda ?>" size="30" maxlength="30">
              </td>
            <td width="57"><div align="center"> 
                </div></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td colspan="6"><strong>Operadora</strong> 
            </td>
            <td width="57"><div align="center"> 
                </div></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="110">Nome:</td>
            <td width="182"> 
              <select name="dd_operadora" id="dd_operadora" class="combo_normal" onChange="document.form1.submit()">
                <option value="">Todos as Operadoras</option>
                <?php while ($pgopr = pg_fetch_array ($resopr)) { ?>
                <option value="<?php echo $pgopr['opr_codigo'] ?>" <?php if(isset($dd_operadora) && $pgopr['opr_codigo'] == $dd_operadora) echo "selected" ?>><?php echo $pgopr['opr_nome'] ?></option>
                <?php } ?>
              </select>
              </td>
            <td width="45">Valor:</td>
            <td width="116">  
              <select name="dd_valor" id="dd_valor" class="combo_normal">
                <option value="">Todos os Valores</option>
                <?php while ($pgval = pg_fetch_array ($resval)) { ?>
                <option value="<?php echo $pgval['valor'] ?>" <?php if(isset($dd_valor) && $pgval['valor'] == $dd_valor) echo "selected" ?>><?php echo number_format($pgval['valor'], 2, ',', '.'); ?></option>
                <?php } ?>
              </select>
              </td>
            <td width="31">&Aacute;rea:</td>
            <td width="177"> 
              <?php if(isset($pg_opr_info) && $pg_opr_info['opr_pin_online'] == 1) { ?>
              <select name="dd_opr_area" id="dd_opr_area" class="combo_normal">
                <option value="">Todas as Areas</option>
                <?php while ($pgopr_area = pg_fetch_array ($res_opr_area)) { ?>
                <option value="<?php echo $pgopr_area['oparea_codigo'] ?>" <?php if(isset($dd_opr_area) && $pgopr_area['oparea_codigo'] == $dd_opr_area) echo "selected" ?>><?php echo $pgopr_area['area_nome'] ?></option>
                <?php } ?>
              </select>
               
              <?php
              }
              else
                  echo "Apenas para Operadora On-Line";
            ?>
            </td>
            <td width="57"><div align="center"> 
                <input type="submit" name="BtnSearch" value="Buscar" class="btn btn-sm btn-info">
                </div></td>
          </tr>
        </table>
      </form>
      <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr> 
          <td> 
            <?php
              if(isset($data_inic_invalida) && $data_inic_invalida == true) echo "<br><font face='Arial, Helvetica, sans-serif' size='2' color='#FF0000'><b>Data inicial Inválida</font></b>";
              if(isset($data_fim_invalida) && $data_fim_invalida == true) echo "<br><font face='Arial, Helvetica, sans-serif' size='2' color='#FF0000'><b>Data final Inválida</font></b>";
              if(isset($data_inicial_menor) && $data_inicial_menor == true) echo "<br><font face='Arial, Helvetica, sans-serif' size='2' color='#FF0000'><b>Data inicial menor do que a Data limite</font></b>";
          ?>
          </td>
        </tr>
      </table>
        <table class="table">
        <tr> 
          <td> 
            <?php if($total_table > 0) { ?>
            Exibindo resultados <strong><?php echo $inicial + 1 ?></strong> 
            a <strong><?php echo $reg_ate ?></strong> de <strong><?php echo $total_table ?></strong> 
            <?php } ?>
          </td>
          <td><div align="right"><a href="../pquery.php"><img src="../../../images/voltar_menu.gif" width="107" height="15" border="0"></a> 
              <?php
              $_SESSION['sqldata']=$sql_transform;
              ?>
              <strong><a href="#" onClick="window.open('../../pro_tracer.php',null,'directories=no,height=400,width=700,location=no,menubar=no,scrollbars=yes,resizable=no,status=no,titlebar=no,toolbar=no')">SQL</a></strong></div></td>
        </tr>
      </table>
        <table class="table">
        <tr bgcolor="#00008C"> 
          <?php
          if($ordem == 1)
              $ordem = 0;
          else
              $ordem = 1;
          if(!isset($ncamp))
              $ncamp = null;
          ?>
          <td width="0" height="0"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=trn_data&inicial=".$inicial.$varsel ?>" class="link_br">Data</a></strong> 
            <?php if($ncamp == 'trn_data') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
          </td>
          <td width="0" height="0"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=trn_hora&inicial=".$inicial.$varsel ?>" class="link_br">Hora</a></strong> 
            <?php if($ncamp == 'trn_hora') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
          </td>
          <td width="0" height="0"><div align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=nome_fantasia&inicial=".$inicial.$varsel ?>" class="link_br">Cod.</a></strong></div></td>
          <td width="0" height="0"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=nome_fantasia&inicial=".$inicial.$varsel ?>" class="link_br">Canal 
            de Venda</a></strong> 
            <?php if($ncamp == 'nome_fantasia') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
          </td>
          <td width="0" height="0"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=nome_fantasia&inicial=".$inicial.$varsel ?>" class="link_br">Estabelecimento</a></strong> 
            <?php if($ncamp == 'nome_fantasia') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
          </td>
          <td width="0" height="0"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=municipio&inicial=".$inicial.$varsel ?>" class="link_br">Municipio</a></strong> 
            <?php if($ncamp == 'municipio') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
          </td>
          <td width="0" height="0"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=uf&inicial=".$inicial.$varsel ?>" class="link_br">UF</a></strong> 
            <?php if($ncamp == 'uf') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
          </td>
          <td width="0" height="0"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=opr_nome&inicial=".$inicial.$varsel ?>" class="link_br">Operadora</a></strong> 
            <?php if($ncamp == 'opr_nome') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
          </td>
          <td width="0" height="0"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ddd&inicial=".$inicial.$varsel ?>" class="link_br">DDD</a></strong> 
            <?php if($ncamp == 'ddd') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?></div> 
          </td>
          <td width="0" height="0"><div align="right"> 
              <?php if($ncamp == 'celular') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
              <strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=celular&inicial=".$inicial.$varsel ?>" class="link_br">Celular</a></strong></div></td>
          <td width="0" height="0"><div align="right"> 
              <?php if($ncamp == 'pin_valor') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
              <strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=pin_valor&inicial=".$inicial.$varsel ?>" class="link_br">Valor 
              da Face</a></strong></div></td>
        </tr>
        <?php
              $cor1 = $query_cor1;
              $cor2 = $query_cor1;
              $cor3 = $query_cor2;
              while ($pgrow = pg_fetch_array($resestat))
              {
                  $valor = true;
          ?>
        <tr bgcolor="#f5f5fb"> 
          <td width="0" height="0" bgcolor="<?php echo $cor1 ?>"><?php echo formata_data($pgrow['trn_data'], 0) ?></td>
          <td width="0" height="0" bgcolor="<?php echo $cor1 ?>"><?php echo $pgrow['trn_hora'] ?></td>
          <td width="0" height="0" bgcolor="<?php echo $cor1 ?>"><div align="right"><?php echo $pgrow['est_codigo'] ?></div></td>
          <td width="0" height="0" bgcolor="<?php echo $cor1 ?>"><?php echo $pgrow['nome'] ?></td>
          <td width="0" height="0" bgcolor="<?php echo $cor1 ?>"><?php echo $pgrow['nome_fantasia'] ?></td>
          <td width="0" height="0" bgcolor="<?php echo $cor1 ?>"><?php echo $pgrow['municipio'] ?></td>
          <td width="0" height="0" bgcolor="<?php echo $cor1 ?>"><?php echo $pgrow['uf'] ?></td>
          <td width="0" height="0" bgcolor="<?php echo $cor1 ?>"><a title="<?php echo strtoupper(substr($pgrow['pin_serial'],0,100)) ?>"><?php echo $pgrow['opr_nome'] ?></a></td>
          <td width="0" height="0" bgcolor="<?php echo $cor1 ?>"><?php echo $pgrow['ddd'] ?></td>
          <td width="0" height="0" bgcolor="<?php echo $cor1 ?>"><div align="right"><?php echo $pgrow['celular'] ?></div></td>
          <td width="0" height="0" bgcolor="<?php echo $cor1 ?>"><div align="right"><?php echo number_format($pgrow['pin_valor'], 2, ',', '.') ?></div></td>
        </tr>
        <?php
                  if($cor1 == $cor2)
                      $cor1 = $cor3;
                  else
                      $cor1 = $cor2;
              }
              if (!isset($valor) || !$valor) { ?>
        <tr bgcolor="#f5f5fb"> 
          <td colspan="11" bgcolor="<?php echo $cor1 ?>"><div align="center"><font size="2" face="Arial, Helvetica, sans-serif" color="#666666"><strong><br>
              N&atilde;o h&aacute; registros.<br>
              <br>
              </strong></div></td>
        </tr>
        <?php } else { ?>
        <tr bgcolor="#E4E4E4"> 
          <td colspan="11" bgcolor="#FFFFFF"><font color="#666666" size="1" face="Arial, Helvetica, sans-serif"><strong> 
            OBS: Valores expressos em R$. </strong></td>
        </tr>
        <tr> 
          <td colspan="11" bgcolor="#FFFFFF"><font size="1" face="Arial, Helvetica, sans-serif" color="#666666"><?php if(!isset($time)) $time = null; echo $search_msg . number_format($time, 2, '.', '.') . $search_unit ?> 

            </td>
        </tr>
        <?php
              $time_end = getmicrotime();
              $time = $time_end - $time_start;
              paginacao_query($inicial, $total_table, $max, '9', $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel);
          ?>
        <?php } ?>
      </table>
      <p> <?php require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";?></p></td>
  </tr>
</table>
</body>
</html>