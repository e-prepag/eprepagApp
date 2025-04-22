<?php
	require_once '../../../../../../includes/constantes.php';
        require_once $raiz_do_projeto."backoffice/includes/topo.php";
        require_once $raiz_do_projeto."includes/gamer/main.php";

	$pos_pagina = isset($seg_auxilar) ? $seg_auxilar : null;

	$time_start = getmicrotime();

	if(!isset($ncamp))            $ncamp           = 'trn_data';
	if(!isset($tf_data_final))    $tf_data_final   = date('d/m/Y');
	if(!isset($tf_data_inicial))  $tf_data_inicial = date('d/m/Y');
	if(!isset($inicial))          $inicial         = 0;
	if(!isset($range))            $range           = 1;
	if(!isset($ordem))            $ordem           = 0;
    
	if(isset($BtnSearch) && $BtnSearch=="Buscar") {
		$inicial     = 0;
		$range       = 1;
		$total_table = 0;
	}

	$data_inicial_limite = data_menos_n(date('d/m/Y'), 120);
	$data_inicial_limite = '01/08/2004';
	$FrmEnviar = 1;
	
	
	$default_add  = nome_arquivo($PHP_SELF);
	$img_proxima  = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/proxima.gif";
	$img_anterior = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/anterior.gif";
	$max          = $qtde_reg_tela;
	$range_qtde   = $qtde_range_tela;

	$resuf = pg_exec($connid, "select uf from uf order by uf");
	$resuf_except = pg_exec($connid, "select uf from uf order by uf");

    if(!isset($cb_opr_teste))
        $cb_opr_teste = null;
    
    if(!isset($operadora_ativada))
        $operadora_ativada = null;
    
    if(!isset($opr_teste))
        $opr_teste = null;
    
    if(!isset($dd_operadora))
        $dd_operadora = null;
    
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
    
    if(!isset($tf_canal_venda))
        $tf_canal_venda = null;
    
    if(!isset($dd_valor))
        $dd_valor = null;
    
    if(!isset($dd_opr_area))
        $dd_opr_area = null;
    
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
    
    if(!isset($dd_valor))
        $dd_valor = null;
    
    if(!isset($dd_opr_area))
        $dd_opr_area = null;
    
    if(!isset($seg_auxilar))
        $seg_auxilar = null;
    
    if(!isset($data_inic_invalida))
        $data_inic_invalida = null;
    
    if(!isset($data_fim_invalida))
        $data_fim_invalida = null;
    
    if(!isset($data_inicial_menor))
        $data_inicial_menor = null;
    if(!isset($valor))
        $valor = null;
    
	if($cb_opr_teste)
		$resopr = pg_exec($connid, "select opr_nome, opr_codigo from operadoras where (opr_status = '".$operadora_ativada."') order by opr_ordem");
	else
		$resopr = pg_exec($connid, "select opr_nome, opr_codigo from operadoras where (opr_status = '".$operadora_ativada."') and (opr_codigo <> ".$opr_teste.") order by opr_ordem");

	if($dd_operadora) {		
            
                $where = '';
                if(!empty($pg_opr_info['opr_codigo'])){
                    $where = "opr_codigo = '". $pg_opr_info['opr_codigo'] ."' and"; 
                }
		$res_opr_info = pg_exec($connid, "select opr_codigo, opr_pin_online from operadoras where opr_codigo=".$dd_operadora."");
		$pg_opr_info = pg_fetch_array($res_opr_info);
	
		if($pg_opr_info['opr_pin_online'] == 0)
			$resval = pg_exec($connid, "select pin_valor as valor from pins where $where pin_canal='s' group by pin_valor order by pin_valor");
		else
		{
			$resval = pg_exec($connid, "select valor_fixo as valor from pin_valor_lista t0, pin_valor_fixo t1 where t0.valor_lista_cod = t1.valor_lista_cod and opr_codigo = ".$pg_opr_info['opr_codigo']." group by valor_fixo order by valor_fixo");
			$res_opr_area = pg_exec($connid, "select oparea_codigo, area_nome from operadora_area ".(!empty($where)?"where $where ":"")."order by oparea_codigo");
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

	if($FrmEnviar == 1)
	{
		$estat  = "select t0.trn_data, t4.nome, t1.ra_codigo, t1.est_codigo, t1.nome_fantasia, t1.uf, t3.municipio, t2.opr_nome, t0.pin_valor, count(t0.pin_valor) as quantidade, sum(t0.pin_valor) as total_face ";
		$estat .= "from changethis t0, estabelecimentos t1, operadoras t2, cidades t3, canais t4 ";
		$estat .= "where (t1.cidade=t3.cid_codigo) and (t0.est_codigo=t1.est_codigo) and (t4.canal_codigo=t1.canal_codigo) and (t0.opr_codigo=t2.opr_codigo) ";		

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


		if($tf_canal_venda)
			$estat .= " and (t4.nome LIKE '%".$tf_canal_venda."%') ";
		
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

		$estat .= "group by trn_data, t4.nome, t1.ra_codigo, t1.est_codigo, t1.nome_fantasia, t1.uf, t3.municipio, t2.opr_nome, t0.pin_valor ";
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

		if($ordem == 0)
		{
			$estat .= " desc ";
			$img_seta = "/images/seta_down.gif";	
		}
		else
		{
			$estat .= " asc ";
			$img_seta = "/images/seta_up.gif";
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

//echo str_replace("\n", "<br>\n", $estat)."<br>";
	}
	else
		$estat = "select est_codigo from estabelecimentos where est_codigo = 0";
		
//	trace_sql($estat, "Arial", 2, "#666666", 'b');
$sql_transform=$estat;
	
	$resestat = pg_exec($connid, $estat);

	if($max + $inicial > $total_table)
		$reg_ate = $total_table;
	else
		$reg_ate = $max + $inicial;
		
	$varsel  = "&cb_opr_teste=$cb_opr_teste&&cb_estab_teste=$cb_estab_teste";
	$varsel .= "&tf_data_final=$tf_data_final&tf_data_inicial=$tf_data_inicial";
	$varsel .= "&tf_codigo_estab=$tf_codigo_estab&tf_nome_estab=$tf_nome_estab&dd_uf=$dd_uf&dd_uf_except=$dd_uf_except";
	$varsel .= "&dd_operadora=$dd_operadora&dd_valor=$dd_valor&dd_opr_area=$dd_opr_area";
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
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<table class="table txt-preto fontsize-pp">
  <tr> 
    <td align="center" valign="top" bgcolor="#FFFFFF"> 
        <table width="100%" border="0" cellspacing="0" cellpadding="3" height="100%">
        <tr valign="top"> 
          <td height="100%"> <form name="form1" method="post" action="">
            <table class="table">
                <tr bgcolor="#00008C"> 
                  <td colspan="11" bgcolor="#ECE9D8"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">Pesquisa</font></td>
                </tr>
                <tr bgcolor="#F5F5FB"> 
                  <td colspan="6"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong>Data da venda</strong></font> 
                  </td>
                  <td width="62"><div align="center"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
                      </font></div></td>
                </tr>
                <tr bgcolor="#F5F5FB"> 
                  <td width="96"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">Datas 
                    Inicial:</font></td>
                  <td width="196"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
                    <input name="tf_data_inicial" type="text" class="form" id="tf_data_inicial" value="<?php echo $tf_data_inicial ?>" size="9" maxlength="10">
                    </font></td>
                  <td width="90"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">Data 
                    Final:</font></td>
                  <td colspan="3"> <font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
                    <input name="tf_data_final" type="text" class="form" id="tf_data_final" value="<?php echo $tf_data_final ?>" size="9" maxlength="10">
                    </font></td>
                  <td width="62"><div align="center"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
                      </font></div></td>
                </tr>
                <tr bgcolor="#F5F5FB"> 
                  <td colspan="6"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong>Operadora</strong></font> 
                  </td>
                  <td width="62"><div align="center"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
                      </font></div></td>
                </tr>
                <tr bgcolor="#F5F5FB"> 
                  <td width="96"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">Nome:</font></td>
                  <td width="196"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
                    <select name="dd_operadora" id="dd_operadora" class="combo_normal" onChange="document.form1.submit()">
                      <option value="">Todos as Operadoras</option>
                      <?php while ($pgopr = pg_fetch_array ($resopr)) { ?>
                      <option value="<?php echo $pgopr['opr_codigo'] ?>" <?php if($pgopr['opr_codigo'] == $dd_operadora) echo "selected" ?>><?php echo $pgopr['opr_nome'] ?></option>
                      <?php } ?>
                    </select>
                    </font></td>
                  <td ><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">Valor:</font></td>
                  <td colspan="3"> <font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
                    <select name="dd_valor" id="dd_valor" class="combo_normal">
                      <option value="">Todos os Valores</option>
                      <?php 
                      if($resval){
                                while ($pgval = pg_fetch_array ($resval)) { 
                          
?>
                      <option value="<?php echo $pgval['valor'] ?>" <?php if($pgval['valor'] == $dd_valor) echo "selected" ?>><?php echo number_format($pgval['valor'], 2, ',', '.'); ?></option>
                      <?php 
                      
                                } //end while
                      }//end if($resval)
                      ?>
                    </select>
                    </font></td>
                  <td width="62"><div align="center"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
                      <input type="submit" name="BtnSearch" value="Buscar" class="btn btn-sm btn-info">
                      </font></div></td>
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
                <td><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
                  <?php if($total_table > 0) { ?>
                  Exibindo resultados <strong><?php echo $inicial + 1 ?></strong> 
                  a <strong><?php echo $reg_ate ?></strong> de <strong><?php echo $total_table ?></strong></font> 
                  <?php } ?>
                </td>
                <td><div align="right"><a href="/frameset.php"><img src="/images/voltar_menu.gif" width="107" height="15" border="0"></a> 
                    <?php
					$_SESSION['sqldata']=$sql_transform;
					?>
                    </div>
                </td>
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
                <td><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=trn_data&inicial=".$inicial.$varsel ?>" class="link_br">Data</a></font></strong> 
                  <?php if($ncamp == 'trn_data') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                </td>
                <!--td><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=est_codigo&inicial=".$inicial.$varsel ?>" class="link_br">Codigo</a></font></strong> 
                  <?php if($ncamp == 'est_codigo') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                </td>
                <td><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=nome_fantasia&inicial=".$inicial.$varsel ?>" class="link_br">Canal 
                  de Venda</a></font></strong> 
                  <?php if($ncamp == 'nome_fantasia') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                </td>
                <td><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=nome_fantasia&inicial=".$inicial.$varsel ?>" class="link_br">Estabelecimento</a></font></strong> 
                  <?php if($ncamp == 'nome_fantasia') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                </td>
                <td><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=municipio&inicial=".$inicial.$varsel ?>" class="link_br">Municipio</a></font></strong> 
                  <?php if($ncamp == 'municipio') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                </td-->
                <td><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=uf&inicial=".$inicial.$varsel ?>" class="link_br">UF</a></font></strong> 
                  <?php if($ncamp == 'uf') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                </td>
                <td><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=opr_nome&inicial=".$inicial.$varsel ?>" class="link_br">Operadora</a></font></strong> 
                  <?php if($ncamp == 'opr_nome') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                </td>
                <td><div align="right"> 
                    <?php if($ncamp == 'quantidade') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                    <strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=quantidade&inicial=".$inicial.$varsel ?>" class="link_br">Qtde</a></font></strong></div></td>
                <td><div align="right"> 
                    <?php if($ncamp == 'pin_valor') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                    <strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=pin_valor&inicial=".$inicial.$varsel ?>" class="link_br">Valor 
                    da Face</a></font></strong></div></td>
                <td><div align="right"> 
                    <?php if($ncamp == 'total_face') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                    <strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=total_face&inicial=".$inicial.$varsel ?>" class="link_br">Valor 
                    Total</a></font></strong></div></td>
              </tr>
              <?php
					$cor1 = $query_cor1;
					$cor2 = $query_cor1;
					$cor3 = $query_cor2;
					$valor_total_tela = 0;
					$qtde_total_tela = 0;

					while ($pgrow = pg_fetch_array($resestat))
					{
						$valor = true;

						$valor_total_tela += $pgrow['total_face'];
						$qtde_total_tela += $pgrow['quantidade'];
				?>
              <tr bgcolor="#f5f5fb"> 
                <td bgcolor="<?php echo $cor1 ?>"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo formata_data($pgrow['trn_data'], 0) ?></font></td>
                <!--td bgcolor="<?php echo $cor1 ?>"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo $pgrow['est_codigo'] ?></font></td>
                <td bgcolor="<?php echo $cor1 ?>"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo $pgrow['nome'] ?></font></td>
                <td bgcolor="<?php echo $cor1 ?>"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo $pgrow['nome_fantasia'] ?></font></td>
                <td bgcolor="<?php echo $cor1 ?>"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo $pgrow['municipio'] ?></font></td-->
                <td bgcolor="<?php echo $cor1 ?>"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo $pgrow['uf'] ?></font></td>
                <td bgcolor="<?php echo $cor1 ?>"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo $pgrow['opr_nome'] ?></font></td>
                <td bgcolor="<?php echo $cor1 ?>"><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo $pgrow['quantidade'] ?></font></div></td>
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
                <td colspan="10" bgcolor="<?php echo $cor1 ?>"><div align="center"><font size="2" face="Arial, Helvetica, sans-serif" color="#666666"><strong><br>
                    N&atilde;o h&aacute; registros.<br>
                    <br>
                    </strong></font></div></td>
              </tr>
              <?php } else { ?>
              <tr bgcolor="#E4E4E4"> 
                <td colspan="3"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong>SUBTOTAL</strong></font></td>
                <td><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong><?php echo number_format($qtde_total_tela, 0, ',', '.') ?></strong></font></div></td>
                <td colspan="3"><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong><?php echo number_format($valor_total_tela, 2, ',', '.') ?></strong></font></div></td>
              </tr>
              <?php
					$time_end = getmicrotime();
					$time = $time_end - $time_start;
					paginacao_query($inicial, $total_table, $max, '9', $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel);
				?>
              <tr> 
                <td colspan="10">&nbsp;</td>
              </tr>
              <tr bgcolor="#E4E4E4"> 
                <td colspan="3"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong>TOTAL</strong></font></td>
                <td><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong><?php echo number_format($qtde_geral, 0, ',', '.') ?></strong></font></div></td>
                <td colspan="2"><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong><?php echo number_format($valor_geral, 2, ',', '.') ?></strong></font></div></td>
              </tr>
              <tr bgcolor="#E4E4E4"> 
                <td colspan="10" bgcolor="#FFFFFF"><font color="#666666" size="1" face="Arial, Helvetica, sans-serif"><strong> 
                  OBS: Valores expressos em R$. </strong></font></td>
              </tr>
              <tr> 
                <td height="52" colspan="10" bgcolor="#FFFFFF"><p><font size="1" face="Arial, Helvetica, sans-serif" color="#666666"><?php echo $search_msg . number_format($time, 2, '.', '.') . $search_unit ?> 
                    </font></p>
                  </td>
              </tr>
              <?php } ?>
            </table></td>
        </tr>
      </table></td>
  </tr>
  <tr>
    <td colspan="3">
      <?php require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php"; ?>
      <div align="center"></div></td></tr>
</table>
</body>
</html>