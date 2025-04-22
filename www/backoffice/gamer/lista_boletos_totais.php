<?php
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";

	set_time_limit ( 3000 ) ;

	$time_start = getmicrotime();
	
	if(!$ncamp)    $ncamp       = 'date_seq';
	if(!$inicial)  $inicial     = 0;
	if(!$range)    $range       = 1;
	if(!$ordem)    $ordem       = 1;

$BtnSearch="Buscar";
	if($btBrowser)	$BtnSearch  = 1;		// simula uma busca após atualizar dados do browser

	if($BtnSearch=="Buscar") {
		$inicial     = 0;
		$range       = 1;
		$total_table = 0;
	}

	if(!$BtnSearch && !$tf_v_data_year ) {
		$tf_v_data_year = date("Y");
	}
	if(!$tf_v_data_year) {
		$tf_v_data_year = date("Y");
	}


	$default_add  = nome_arquivo($PHP_SELF);
	$img_proxima  = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/proxima.gif";
	$img_anterior = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/anterior.gif";
	$max          = 100000; //$qtde_reg_tela;
	$range_qtde   = $qtde_range_tela;

	$varsel = "&BtnSearch=1";
	$varsel .= "&tf_data=$tf_data";
	$varsel .= "&tf_data_ini=$tf_data_ini&tf_data_fim=$tf_data_fim&tf_store_id=$tf_store_id&tf_cliente_id=$tf_cliente_id&tf_amount=$tf_amount";

	//Operadoras		
	$varsel .= "&tf_opr_codigo=$tf_opr_codigo";


	//Produtos
	if ($tf_produto && is_array($tf_produto))
		if (count($tf_produto) == 1)
			$tf_produto = $tf_produto[0];
		else
			$tf_produto = implode("|",$tf_produto);
	$varsel .= "&tf_produto=$tf_produto";
	if ($tf_produto && $tf_produto != "")
		$tf_produto = explode("|",$tf_produto);
	
	//Valores
	if ($tf_pins && is_array($tf_pins))
		if (count($tf_pins) == 1)
			$tf_pins = $tf_pins[0];
		else
			$tf_pins = implode("|",$tf_pins);
	$varsel .= "&tf_pins=$tf_pins";
	if ($tf_pins && $tf_pins != "")
		$tf_pins = explode("|",$tf_pins);	


	$total_pagos_geral = 0;
	$total_abertos_geral = 0;
	$n_pagos_geral = 0;
	$n_abertos_geral = 0;

	$total_pagos_pagina = 0;
	$total_abertos_pagina = 0;
	$n_pagos_pagina = 0;
	$n_abertos_pagina = 0;


	if(isset($BtnSearch)){
	
		//Validacao
		//------------------------------------------------------------------------------------------------------------------
		$msg = "";
			$rs_pedidos = null;
			$filtro['year'] = $tf_v_data_year;
			$filtro['operadora'] = $tf_opr_codigo;
			$filtro['produto'] = $tf_produto;
			$filtro['pins'] = $tf_pins;

			$ret = obter($filtro, null, $rs_pedidos);
			if($ret != "") $msg = $ret;
			else {
				$total_table = pg_num_rows($rs_pedidos);
//echo "total_table: $total_table<br>";
				while($rs_pedidos_row = pg_fetch_array($rs_pedidos)){
					$total_pagos_geral += $rs_pedidos_row['total_pagos'];
					$total_abertos_geral += $rs_pedidos_row['total_abertos'];
					$n_pagos_geral += $rs_pedidos_row['n_pagos'];
					$n_abertos_geral += $rs_pedidos_row['n_abertos'];
				}

				if($total_table == 0) {
					$msg = "Nenhum registro de integração encontrado." . PHP_EOL;
				} else {
					//Ordem
					$orderBy = $ncamp;
					if($ordem == 1){
						$orderBy .= " desc ";
						$img_seta = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/seta_down.gif";
					} else {
						$orderBy .= " asc ";
						$img_seta = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/seta_up.gif";
					}
			
					$orderBy .= " limit ".$max; 
					$orderBy .= " offset ".$inicial;
				
					$ret = obter($filtro, $orderBy, $rs_pedidos);
					if($ret != "") $msg = $ret;
					else {
				
						if($max + $inicial > $total_table)
							$reg_ate = $total_table;
						else
							$reg_ate = $max + $inicial;
					}
				}
			}


	}
	
	//Operadoras / Produtos / Valores
	$sql = "select * from operadoras ope where opr_status = '1' order by opr_nome";
	$rs_operadoras = SQLexecuteQuery($sql);
	if($tf_opr_codigo) {
		$sql = "select ogp_id,ogp_nome from tb_operadora_games_produto where ogp_opr_codigo = " . $tf_opr_codigo . "";
		$rs_oprProdutos = SQLexecuteQuery($sql);
		$sql = "select pin_valor from pins where opr_codigo = " . $tf_opr_codigo . " group by pin_valor order by pin_valor;";
		$rs_oprPins = SQLexecuteQuery($sql);
	}


ob_end_flush();
?>
<script language="javascript">
function GP_popupAlertMsg(msg) { //v1.0
  document.MM_returnValue = alert(msg);
}
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}

</script>
		<script language="javascript">
			$(document).ready(function () {
				//Ao selecionar a operadora
				$('#tf_opr_codigo').change(function(){
					var id = $(this).val();
					//alert(id);
					
					$.ajax({
						type: "POST",
						url: "/ajax/gamer/ajaxProdutoComPesquisaVendas.php",
						data: "id="+id,
						beforeSend: function(){
							$('#mostraProdutos').html("Aguarde...");
						},
						success: function(html){
							//alert('produto');
							$('#mostraProdutos').html(html);
						},
						error: function(){
							alert('erro produto');
						}
					});

					$.ajax({
						type: "POST",
						url: "/ajax/gamer/ajaxValorComPesquisaVendas.php",
						data: "id="+id,
						beforeSend: function(){
							$('#mostraValores').html("Aguarde...");
						},
						success: function(html){
							//alert('valor');
							$('#mostraValores').html(html);
						},
						error: function(){
							alert('erro valor');
						}
					});
				});
		
			});
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
    <td> 
		<form name="form1" method="post" action="lista_boletos_totais.php">
        <table class="table">
          <tr> 
            <td colspan="5">
                <table  class="table">
    	        <tr bgcolor="F5F5FB">
    	          <td width="100" class="texto"><b>Ano do boleto</b></td>
    	          <td class="texto" align="left">&nbsp;<select id='tf_v_data_year' name='tf_v_data_year' onChange='reload()'> 
						<option value=''<?php echo (($tf_v_data_year=="")?" selected":""); ?>>Selecione o ano</option> 
				  <?php
					$year_start = 2008;
					$year_now = date("Y");
					for($i=$year_now;$i>=$year_start;$i--) {
						?>
						<option value='<?php echo $i; ?>'<?php echo (($tf_v_data_year==$i)?" selected":""); ?>><?php echo $i; ?></option> 
						<?php
					}
				  ?></select></nobr>
				  </td>
    	          <td class="texto" align="center"><nobr>&nbsp;</nobr></td>
				  <td class="texto" align="center">&nbsp;</td>
    	        </tr>
					<tr bgcolor="#FFFFFF"> 
            			<td colspan="4" bgcolor="#ECE9D8" class="texto">Produto</font></td>
          			</tr>
          			<tr bgcolor="#F5F5FB"> 
            			<td width="100" class="texto">Operadora</font></td>
            			<td>
							<select name="tf_opr_codigo" id="tf_opr_codigo" class="form2">
								<option value="" <?php if($tf_opr_codigo == "") echo "selected" ?>>Selecione</option>
								<?php 
									if($rs_operadoras) 
										while($rs_operadoras_row = pg_fetch_array($rs_operadoras))
										{
								?>
										<option value="<?php echo $rs_operadoras_row['opr_codigo']; ?>"
										<?php 
											if ($tf_opr_codigo == $rs_operadoras_row['opr_codigo'] || $rs_operadoras_row['opr_codigo'] == $buscaOper) 
												echo " selected";
										?>><?php echo $rs_operadoras_row['opr_nome']; ?></option>
										<?php } ?>
							</select>
						</td>
            			<td colspan="2"></td>
          			</tr>
          			<tr bgcolor="#F5F5FB"> 
            			<td class="texto">Produtos</font></td>
            			<td colspan="3" class="texto">
								<div id='mostraProdutos'>
								<?php 
                        if($rs_oprProdutos)
                           while($rs_oprProdutos_row = pg_fetch_array($rs_oprProdutos))
                           { 
                        ?>
										<input type="checkbox" id="tf_produto[]" name="tf_produto[]" value="<?php echo $rs_oprProdutos_row['ogp_nome']; ?>" 
										<?php
											if ($tf_produto && is_array($tf_produto))
												if (in_array($rs_oprProdutos_row['ogp_nome'], $tf_produto)) 
													echo " checked";
											else
												if ($rs_oprProdutos_row['ogp_nome'] == $tf_produto)
													echo " checked";
										?>><?php echo $rs_oprProdutos_row['ogp_nome']; ?>
                        <?php 
									} 
								?>
								</div>
							</td>
                </tr>
                <tr bgcolor="#F5F5FB"> 
                    <td class="texto">Valores</font></td>
                    <td colspan="2" class="texto">
                        <div id='mostraValores'>
                        <?php 
                        if($rs_oprPins)
                            while($rs_oprPins_row = pg_fetch_array($rs_oprPins))
                            { 
                    ?>
                                <input type="checkbox" id="tf_pins[]" name="tf_pins[]" value="<?php echo $rs_oprPins_row['pin_valor']; ?>" 
                                <?php
                                    if ($tf_pins && is_array($tf_pins))
                                        if (in_array($rs_oprPins_row['pin_valor'], $tf_pins)) 
                                            echo " checked";
                                    else
                                        if ($rs_oprPins_row['pin_valor'] == $tf_pins)
                                            echo " checked";
                                ?>><?php echo $rs_oprPins_row['pin_valor'] . ",00"; ?>
                            <?php } ?>
                        </div>
                    </td>
                    <td align="right"><input type="submit" name="BtnSearch" value="Buscar" class="btn btn-sm btn-info"></td>
                </tr> 
		      </table>
			</td>
          </tr>
		</table>
		</form>

		<?php if($total_table > 0) { ?>
        <table class="table">
                <tr bgcolor="#00008C"> 
                  <td height="11" colspan="3" bgcolor="#FFFFFF"> 
                      <table class="table">
				  	  <tr> 
						<td colspan="20" class="texto"> 
                          Exibindo resultados <strong><?php echo $inicial + 1 ?></strong> 
                          a <strong><?php echo $reg_ate ?></strong> de <strong><?php echo $total_table ?></strong></font> 
                        </td>
					  </tr>
					  <?php $ordem = ($ordem == 1)?2:1; ?>
                      <tr  bgcolor="#ECE9D8"> 
                        <td align="center" class="texto"><strong>Data</strong></td>
                        <td align="center" class="r"><strong>DOW</strong></td>

                        <td align="center" class="lr" colspan="3"><strong>Boletos Pagos</strong></td>

                        <td align="center" class="lr" colspan="3"><strong>Boletos em Aberto</strong></td>

                        <td align="center" class="lr" colspan="3"><strong>Boletos TODOS</strong></td>

						<td align="center" class="l"><strong>% pagos</strong></td>
                      </tr>
                      <tr  bgcolor="#ECE9D8"> 
                        <td align="center" class="texto"><strong>&nbsp;</strong></td>
                        <td align="center" class="r"><strong>&nbsp;</strong></td>

                        <td align="center" class="l"><strong>N</strong></td>
                        <td align="center" class="texto"><strong>Total (R$)</strong></td>
                        <td align="center" class="r"><strong>médio (R$)</strong></td>

                        <td align="center" class="l"><strong>N</strong></td>
                        <td align="center" class="texto"><strong>Total (R$)</strong></td>
                        <td align="center" class="r"><strong>médio (R$)</strong></td>

                        <td align="center" class="l"><strong>N</strong></td>
                        <td align="center" class="texto"><strong>Total (R$)</strong></td>
                        <td align="center" class="r"><strong>médio (R$)</strong></td>

						<td align="center" class="l"><strong>&nbsp;</strong></td>
                      </tr>
					<?php
						$cor_hover = "#CCFFCC";
						$cor1 = $query_cor1;
						$cor2 = $query_cor1;
						$cor3 = $query_cor2;
						$corSunday = "#CCFFCC";

						while($rs_pedidos_row = pg_fetch_array($rs_pedidos)){
							$cor1 = ($cor1 == $cor2)?$cor3:$cor2;
							
							$perc = $rs_pedidos_row['total_pagos'] / ((1*($rs_pedidos_row['total_pagos']+$rs_pedidos_row['total_abertos'])>0)?($rs_pedidos_row['total_pagos']+$rs_pedidos_row['total_abertos']):1); 

							$total_pagos_pagina += $rs_pedidos_row['total_pagos'];
							$total_abertos_pagina += $rs_pedidos_row['total_abertos'];
							$n_pagos_pagina += $rs_pedidos_row['n_pagos'];
							$n_abertos_pagina += $rs_pedidos_row['n_abertos'];

							$i_dow = date('w', strtotime($rs_pedidos_row['date_seq']));
							$dia_da_semana = (($i_dow==0)?"Dom":(($i_dow==6)?"Sab":($i_dow+1)."aF"));


					?>
                      <tr class="trListagem" bgcolor="<?php echo ($i_dow==0)?$corSunday:$cor1 ?>"<?php if($i_dow==0) echo " style='color:blue'" ?>> 
							<td class="texto" align="center"><nobr><?php echo substr($rs_pedidos_row['date_seq'],0,19) ?></nobr></td>
							<td class="r" align="center"><nobr><?php echo $dia_da_semana ?></nobr></td>

							<td class="l" align="center"><?php echo (1*$rs_pedidos_row['n_pagos']) ?></td>
							<td class="texto" align="center"><?php echo number_format((1*$rs_pedidos_row['total_pagos']), 2, ',', '.')?></td>
							<td class="r" align="center"><?php echo number_format((1*$rs_pedidos_row['total_pagos']/((1*$rs_pedidos_row['n_pagos']>0)?$rs_pedidos_row['n_pagos']:1)), 2, ',', '.')?></td>

							<td class="l" align="center"><?php echo (1*$rs_pedidos_row['n_abertos']) ?></td>
							<td class="texto" align="center"><?php echo number_format((1*$rs_pedidos_row['total_abertos']), 2, ',', '.')?></td>
							<td class="r" align="center"><?php echo number_format((1*$rs_pedidos_row['total_abertos']/((1*$rs_pedidos_row['n_abertos']>0)?$rs_pedidos_row['n_abertos']:1)), 2, ',', '.')?></td>

							<td class="l" align="center"><?php echo (1*($rs_pedidos_row['n_pagos'] + $rs_pedidos_row['n_abertos'])) ?></td>
							<td class="texto" align="center"><?php echo number_format((1*($rs_pedidos_row['total_pagos'] + $rs_pedidos_row['total_abertos'])), 2, ',', '.')?></td>
							<td class="r" align="center"><?php echo number_format((1*($rs_pedidos_row['total_pagos'] + $rs_pedidos_row['total_abertos'])/((1*($rs_pedidos_row['n_pagos'] + $rs_pedidos_row['n_abertos'])>0)?($rs_pedidos_row['n_pagos'] + $rs_pedidos_row['n_abertos']):1)), 2, ',', '.')?></td>

							<td class="l" align="center"><?php echo number_format( 100*$perc, 2, ',', '.')?></td>
						  </tr>

					<?php 	}	?>

<?php 
	/*
?>	
					<?php 
						$perc = $total_pagos_pagina / ((1*($total_pagos_pagina + $total_abertos_pagina)>0)?($total_pagos_pagina + $total_abertos_pagina):1); 
					?>	
                      <tr bgcolor="#ECE9D8"> 
                        <td class="texto" align="center" colspan="2"><nobr>SUBTOTAL</nobr></td>

                        <td class="texto" align="center"><?php echo (1*$n_pagos_pagina) ?></td>
                        <td class="texto" align="center"><?php echo number_format((1*$total_pagos_pagina), 2, ',', '.')?></td>
                        <td class="texto" align="center"><?php echo number_format((1*$total_pagos_pagina/((1*$n_pagos_pagina>0)?$n_pagos_pagina:1)), 2, ',', '.')?></td>

                        <td class="texto" align="center"><?php echo (1*$n_abertos_pagina) ?></td>
                        <td class="texto" align="center"><?php echo number_format((1*$total_abertos_pagina), 2, ',', '.')?></td>
                        <td class="texto" align="center"><?php echo number_format((1*$total_abertos_pagina/((1*$n_abertos_geral>0)?$n_abertos_pagina:1)), 2, ',', '.')?></td>

                        <td class="texto" align="center"><?php echo (1*($n_pagos_pagina + $n_abertos_pagina)) ?></td>
                        <td class="texto" align="center"><?php echo number_format((1*($total_pagos_pagina + $total_abertos_pagina)), 2, ',', '.')?></td>
                        <td class="texto" align="center"><?php echo number_format((1*($total_pagos_pagina + $total_abertos_pagina)/((1*($n_pagos_pagina +$n_abertos_geral)>0)?($n_pagos_pagina + $n_abertos_pagina):1)), 2, ',', '.')?></td>

                        <td class="texto" align="center"><?php echo number_format( 100*$perc, 2, ',', '.')?></td>
					  </tr>

<?php 
	*/
?>	
 
					<?php 
						$perc = $total_pagos_geral / ((1*($total_pagos_geral + $total_abertos_geral)>0)?($total_pagos_geral + $total_abertos_geral):1); 
					?>	
                      <tr bgcolor="#ECE9D8"> 
                        <td class="r" align="center" colspan="2"><nobr>TOTAL</nobr></td>

                        <td class="l" align="center"><?php echo (1*$n_pagos_geral) ?></td>
                        <td class="texto" align="center"><?php echo number_format((1*$total_pagos_geral), 2, ',', '.')?></td>
                        <td class="r" align="center"><?php echo number_format((1*$total_pagos_geral/((1*$n_pagos_geral>0)?$n_pagos_geral:1)), 2, ',', '.')?></td>

                        <td class="l" align="center"><?php echo (1*$n_abertos_geral) ?></td>
                        <td class="texto" align="center"><?php echo number_format((1*$total_abertos_geral), 2, ',', '.')?></td>
                        <td class="r" align="center"><?php echo number_format((1*$total_abertos_geral/((1*$n_abertos_geral>0)?$n_abertos_geral:1)), 2, ',', '.')?></td>

                        <td class="l" align="center"><?php echo (1*($n_pagos_geral + $n_abertos_geral)) ?></td>
                        <td class="texto" align="center"><?php echo number_format((1*($total_pagos_geral + $total_abertos_geral)), 2, ',', '.')?></td>
                        <td class="r" align="center"><?php echo number_format((1*($total_pagos_geral + $total_abertos_geral)/((1*($n_pagos_geral + $n_abertos_geral)>0)?($n_pagos_geral + $n_abertos_geral):1)), 2, ',', '.')?></td>

                        <td class="l" align="center"><?php echo number_format( 100*$perc, 2, ',', '.')?></td>
					  </tr>

                      <tr> 
                        <td colspan="20" bgcolor="#FFFFFF" class="texto"><?php echo $search_msg . number_format(getmicrotime() - $time_start, 2, ',', '.') . $search_unit ?></font></td>
                      </tr>
					<?php paginacao_query($inicial, $total_table, $max, 100, $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel); ?>
                    </table>
				  </td>
                </tr>
              </table>
          <?php  }  ?>
    </td>
  </tr>
</table>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</html>
<?php
	function obter($filtro, $orderBy, &$rs){

		$ret = "";

		$sql = "
			select date_seq, sum(n_abertos) as n_abertos, sum(n_pagos) as n_pagos,
				sum(total_abertos) as total_abertos, sum(total_pagos) as total_pagos
			from 
				(select (generate_series(0,((current_date - '2008-01-01'))) + date '2008-01-01') as date_seq) d
				left outer join
					(

					select date_trunc('day', vg_data_inclusao) as s_dia, sum(vgm.vgm_valor * vgm.vgm_qtde) as total_pagos, count(*) as n_pagos 
					from tb_venda_games vg 
						inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
					where 1=1 
						and vg.vg_ultimo_status=5
						and vg.vg_ug_id = '7909' ";
// "						and vg.vg_data_inclusao between '2008-01-01 00:00:00' and '".date("Y-m-d")." 23:59:59' " 
		if($filtro['year']) {
			$sql .= " and date_part('year', vg.vg_data_inclusao)=".$filtro['year']." ";
		}
		if($filtro['operadora']) {
			$sql .= " and vgm.vgm_opr_codigo = ".$filtro['operadora']." ";
		}
		//Produtos
		if ($filtro['produto'] && is_array($filtro['produto'])) {
			if (count($filtro['produto']) == 1)
					$sql .= " and upper(vgm.vgm_nome_produto) like '%" . str_replace("'", "''",strtoupper($filtro['produto'][0])) . "%' ";	
			else
			{
				$sql .= " and (";
				foreach($filtro['produto'] as $tf_produto_id => $tf_produto_row)	
					if ($tf_produto_id == count($filtro['produto']) - 1)
						$sql .= "upper(vgm.vgm_nome_produto) like '%" . str_replace("'", "''",strtoupper($tf_produto_row)) . "%')";
					else
						$sql .= "upper(vgm.vgm_nome_produto) like '%" . str_replace("'", "''",strtoupper($tf_produto_row)) . "%' or ";
			}
		}
		//Valores
		if ($filtro['pins'] && is_array($filtro['pins'])) {
			if (count($filtro['pins']) == 1)
					$sql .= " and vgm.vgm_valor = " . moeda2numeric($filtro['pins'][0]) . " ";	
			else
			{
				$sql .= " and (";
				foreach($filtro['pins'] as $tf_pins_id => $tf_pins_row)	
					if ($tf_pins_id == count($filtro['pins']) - 1)
						$sql .= "vgm.vgm_valor = " . moeda2numeric($tf_pins_row) . ")";
					else
						$sql .= "vgm.vgm_valor = " . moeda2numeric($tf_pins_row) . " or ";
			}
		}
		$sql .= "
					group by date_trunc('day', vg_data_inclusao)
					order by s_dia desc

					) v
					on d.date_seq=v.s_dia
				left outer join
					(

					select date_trunc('day', vg_data_inclusao) as s_dia_t, sum(vgm.vgm_valor * vgm.vgm_qtde) as total_abertos, count(*) as n_abertos 
					from tb_venda_games vg 
						inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
					where 1=1 
						and (not vg.vg_ultimo_status=5)
						and vg.vg_ug_id = '7909' ";
//	"						and vg.vg_data_inclusao between '2008-01-01 00:00:00' and '".date("Y-m-d")." 23:59:59' "
		if($filtro['year']) {
			$sql .= " and date_part('year', vg.vg_data_inclusao)=".$filtro['year']." ";
		}
		if($filtro['operadora']) {
			$sql .= " and vgm.vgm_opr_codigo = ".$filtro['operadora']." ";
		}
		//Produtos
		if ($filtro['produto'] && is_array($filtro['produto'])) {
			if (count($filtro['produto']) == 1)
					$sql .= " and upper(vgm.vgm_nome_produto) like '%" . str_replace("'", "''",strtoupper($filtro['produto'][0])) . "%' ";	
			else
			{
				$sql .= " and (";
				foreach($filtro['produto'] as $tf_produto_id => $tf_produto_row)	
					if ($tf_produto_id == count($filtro['produto']) - 1)
						$sql .= "upper(vgm.vgm_nome_produto) like '%" . str_replace("'", "''",strtoupper($tf_produto_row)) . "%')";
					else
						$sql .= "upper(vgm.vgm_nome_produto) like '%" . str_replace("'", "''",strtoupper($tf_produto_row)) . "%' or ";
			}
		}
		//Valores
		if ($filtro['pins'] && is_array($filtro['pins'])) {
			if (count($filtro['pins']) == 1)
					$sql .= " and vgm.vgm_valor = " . moeda2numeric($filtro['pins'][0]) . " ";	
			else
			{
				$sql .= " and (";
				foreach($filtro['pins'] as $tf_pins_id => $tf_pins_row)	
					if ($tf_pins_id == count($filtro['pins']) - 1)
						$sql .= "vgm.vgm_valor = " . moeda2numeric($tf_pins_row) . ")";
					else
						$sql .= "vgm.vgm_valor = " . moeda2numeric($tf_pins_row) . " or ";
			}
		}
		$sql .= "
					group by date_trunc('day', vg_data_inclusao)
					order by s_dia_t desc

					) t
					on d.date_seq=t.s_dia_t
			where 1=1 " . PHP_EOL;
		if($filtro['year']) {
			$sql .= " and date_part('year', date_seq)=".$filtro['year']." " . PHP_EOL;
		}
// "and date_seq<=CURRENT_TIMESTAMP "
		$sql .= "group by date_seq ";
//"			order by date_seq desc"

		if(!is_null($orderBy)) $sql .= " order by " . $orderBy;

		
if(b_IsUsuarioReinaldo()) { 
//echo "(R) ".str_replace("\n", "<br>\n", $sql)."<br>";
//die("Stop");
}		
		$rs = SQLexecuteQuery($sql);
		if(!$rs) $ret = "Erro ao obter boletos." . PHP_EOL;

		return $ret;

	}

?>