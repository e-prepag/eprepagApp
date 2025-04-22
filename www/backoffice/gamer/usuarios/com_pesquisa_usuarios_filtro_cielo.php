<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/gamer/main.php";

	set_time_limit ( 3000 ) ;

	$time_start = getmicrotime();
	

	$default_add  = nome_arquivo($PHP_SELF);


	// Params
	$params = array(
		'n_months_compras' => array('name' => 'Meses desde a primeira compra', 'value' => 4, ),
		'n_min_compras_finalizadas' => array('name' => 'Número mínimo de compras finalizadas', 'value' =>  10, ),
		'n_min_compras_finalizadas_pagto_online' => array('name' => 'Número mínimo de compras finalizadas com Pagto. online', 'value' =>  5, ),
		'ticket_max' => array('name' => 'Valor máximo do ticket', 'value' =>  50, ),
		);

	$filtra_operadoras = false;
	if($filtra_operadoras) {
		$params['opr_codigo_lst'] = array('name' => 'Operadoras (lista)', 'value' =>  '38, 37, 34, 42', );
	}

	$fields = array(
		'ug_id' => 'ID', 
		'ug_email' => 'Email', 
		'ug_nome' => 'Nome', 
		'vg_valor' => 'Valor total das vendas', 
		'vg_qtde_itens' => 'Qtde total de vendas', 
		'vg_qtde_produtos' => 'Qtde total de produtos', 
		'vg_data_inclusao_min' => 'Data da primeira venda', 
		'ticket' => 'Ticket', 
		'n_pagtos_online' => 'Qtde total de pagtos online', 		
	);

	$sql  = "";
	$sql .= "
				select ug.ug_id, ug.ug_email, ug_nome, v.*, 
					(select count(*) as vg_qtde
						from tb_venda_games vg1 
							inner join tb_venda_games_modelo vgm1 on vg1.vg_id = vgm1.vgm_vg_id
						where 1=1
							and vg1.vg_ultimo_status=5 
							and vg1.vg_ug_id = ug.ug_id
							and vg1.vg_pagto_tipo >=5
						group by vg1.vg_ug_id
						) as n_pagtos_online		
				from usuarios_games ug 
					inner join (
						select vg_ug_id, 
							sum(vgm.vgm_valor * vgm.vgm_qtde) as vg_valor, 
							sum(vgm.vgm_qtde) as vg_qtde_itens, count(*) as vg_qtde_produtos,
							substr( min(vg_data_inclusao) ::text, 1, 19) as vg_data_inclusao_min, 							
							(sum(vgm.vgm_valor * vgm.vgm_qtde) / (coalesce(count(*), 1))) as ticket 
						from tb_venda_games vg 
							inner join tb_venda_games_modelo vgm on vg.vg_id = vgm.vgm_vg_id 		
						where 1=1
							and vg_ultimo_status=5 ";
	if($filtra_operadoras) {
		$sql .= "
							and vgm_opr_codigo in (".$params['opr_codigo_lst']['value'].")";
	}
	$sql .= "
						group by vg_ug_id
					) v on v.vg_ug_id = ug.ug_id
				where 1=1 
					-- 1ª compra finalizada há pelo menos ".$params['n_months_compras']['value']." meses
					and (
							(select min(vg1.vg_data_inclusao) as min_data
							from tb_venda_games vg1 
								inner join tb_venda_games_modelo vgm1 on vg1.vg_id = vgm1.vgm_vg_id
							where 1=1
								and vg1.vg_ultimo_status=5 
								and vg1.vg_ug_id = ug.ug_id
							group by vg1.vg_ug_id
							)<=(CURRENT_DATE - interval '".$params['n_months_compras']['value']." month')
						)
					-- perfil de compras finalizadas – quantidade mínima – ".$params['n_min_compras_finalizadas']['value']."
					and (
							(select count(*) as vg_qtde
							from tb_venda_games vg1 
								inner join tb_venda_games_modelo vgm1 on vg1.vg_id = vgm1.vgm_vg_id
							where 1=1
								and vg1.vg_ultimo_status=5 
								and vg1.vg_ug_id = ug.ug_id
							group by vg1.vg_ug_id
							)>=".$params['n_min_compras_finalizadas']['value']."
						)
					-- perfil de compras finalizadas – Ter realizado ao menos ".$params['n_min_compras_finalizadas_pagto_online']['value']." pagamentos online (ou seja, não conta Boleto, EPPCash e Depósito)
					and (
							(select count(*) as vg_qtde
							from tb_venda_games vg1 
								inner join tb_venda_games_modelo vgm1 on vg1.vg_id = vgm1.vgm_vg_id
							where 1=1
								and vg1.vg_ultimo_status=5 
								and vg1.vg_ug_id = ug.ug_id
								and vg1.vg_pagto_tipo >=5
							group by vg1.vg_ug_id
							)>=".$params['n_min_compras_finalizadas_pagto_online']['value']."
						)
					-- perfil de compras finalizadas – ticket até R$ ".$params['ticket_max']['value'].",00
					and (
						select (sum(vgm1.vgm_valor * vgm1.vgm_qtde) / (coalesce(count(*), 1))) as ticket 
						from tb_venda_games vg1 
							inner join tb_venda_games_modelo vgm1 on vg1.vg_id = vgm1.vgm_vg_id 		
						where 1=1
							and vg1.vg_ultimo_status=5 
							and vg1.vg_ug_id = ug.ug_id			
						group by vg1.vg_ug_id
						)<=".$params['ticket_max']['value']."
				order by vg_valor desc, ug_id desc
	";

	$rs = SQLexecuteQuery($sql);
	$total_table = pg_num_rows($rs);

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
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<table class="txt-preto fontsize-pp table bg-branco">
  <tr> 
    <td> 
        <form name="form1" method="post" action="com_pesquisa_usuarios_extrato.php">
        <table class="table txt-preto fontsize-pp">
          <tr bgcolor="#F5F5FB"> 
            <td align="right"><input type="submit" name="BtnSearch" value="Buscar" class="btn btn-info btn-sm"></td>
          </tr>
          <?php if($msg != ""){?><tr class="texto"><td align="center"><br><br><font color="#FF0000"><?php echo $msg?></font></td></tr><?php }?>
		</table>
		</form>
		<table class="table txt-preto fontsize-pp">
		  <tr class="texto"> 
			<td align="left">Obs.: Lista usuários Gamer que podem usar o pagamento Cielo segiundo as regras descritas na tabela.
				<table border='1' cellpadding='0' cellspacing='1' bordercolor='#cccccc' style='border-collapse:collapse;'>
					<tr align='center' class='texto'><td><b>Descrição</b></td><td><b>Valor</b></td></tr>
					<?php
						$sql_opr = "select opr_nome from operadoras where opr_codigo in (".$params['opr_codigo_lst']['value'].")";
						$rs_opr = SQLexecuteQuery($sql_opr);
						$s_opr_lst = "";
						if($rs_opr && pg_num_rows($rs_opr) != 0) {
							while ($pgrs_opr = pg_fetch_array ($rs_opr)) {
								$s_opr_lst .= (($s_opr_lst)?", ":"")."".$pgrs_opr['opr_nome'];
							}
						}

						foreach($params as $key => $val) {
							echo "<tr class='texto'>";
							echo "<td align='left'>".$val['name'].(($key=="opr_codigo_lst")?" [$s_opr_lst] ":"")."</td><td align='center'>".$val['value']."</td></tr>\n";
						}
					?>
				</table>
			</td>
          </tr>
		</table>
		<?php if($total_table > 0) { ?>
        <table class="table txt-preto fontsize-pp">
                <tr bgcolor="#00008C"> 
                  <td height="11" colspan="3" bgcolor="#FFFFFF"> 
				  	<table class="table txt-preto fontsize-pp">
				  	  <tr> 
						<td colspan="<?php echo count($fields)?>" class="texto"> 
                          Exibindo resultados <strong><?php echo $inicial + 1 ?></strong> 
                          a <strong><?php echo $reg_ate ?></strong> de <strong><?php echo $total_table ?></strong></font> 
                        </td>
					  </tr>
					  <?php $ordem = ($ordem == 1)?2:1; ?>
				  	  <tr class='texto' bgcolor='#ccff99' align='center'> 
						<?php
							foreach($fields as $key => $val) {
								echo "<td align='left'><b>$val</b></td>\n";
							}
						?>
				  	  </tr> 

					<?php
						$cor_hover = "#CCFFCC";
						$cor1 = $query_cor1;
						$cor2 = $query_cor1;
						$cor3 = $query_cor2;
						$lista_ug_id = "";
						$n_lista_ug_id = 0;

						if((pg_num_rows($rs) != 0) && ($rs)) {

							$i=0;			
							while ($pgrs = pg_fetch_array ($rs)) {
								$cor1 = ($cor1 == $cor2)?$cor3:$cor2;

								?>
							  <tr bgcolor="<?php echo $cor1 ?>" onmouseover="bgColor='#CFDAD7'" onmouseout="bgColor='<?php echo $cor1 ?>'" class='texto' title='<?php echo "Linha ".($i+1)."" ?>'> 
								<?php
									foreach($fields as $key => $val) {
										echo "<td align='left'>";
										if($key=='ug_email' || $key=='ug_nome' || $key=='vg_data_inclusao_min') echo "<nobr>";
										if($key=='vg_valor' || $key=='ticket') {
											echo number_format($pgrs[$key], 2, ',', '.');
										} else {
											if($key=='ug_id') echo "<a href='/gamer/usuarios/com_usuario_detalhe.php?usuario_id=".$pgrs[$key]."' target='_blank'>";
											echo $pgrs[$key];
											if($key=='ug_id') echo "</a>";
										}
										if($key=='ug_email' || $key=='ug_nome' || $key=='vg_data_inclusao_min') echo "</nobr>";
										echo "</td>\n";
									}
								?>
							  </tr> 
							<?php 
								if(strpos($lista_ug_id, $pgrs['ug_id'])===false) {
									if($lista_ug_id!="") $lista_ug_id .= ", ";
									$lista_ug_id .= $pgrs['ug_id'];
									$n_lista_ug_id ++;
								}
								$i++;
							}
							?>
						  <tr> 
							<td colspan="13" bgcolor="#FFFFFF" class="texto"><br><?php echo $search_msg . number_format(getmicrotime() - $time_start, 2, '.', '.') . $search_unit ?></font></td>
						  </tr>
							<tr bgcolor="D5D5DB"> 
								<td class="texto" align="right" colspan="2"><b>Lista de IDs de usuários</b></td>
								<td class="texto" align="left" colspan="7">
									<input type="button" id="but_ids_show" value="Mostra Lista de IDs" onclick="$('#div_ids').show();$('#but_ids_show').hide();">
								</td>
							</tr>
							<tr bgcolor="D5D5DB"> 
								<td class="texto" align="left" colspan="<?php echo count($fields)?>">
									<div id="div_ids" style="display:none;">
										Encontrados <?php echo $n_lista_ug_id ?> usuários. - <input type="button" id="but_ids_hide" value="Oculta Lista de IDs" onclick="$('#div_ids').hide(); $('#but_ids_show').show();"><br>
										<?php echo $lista_ug_id ?>
									</div>
								</td>
							</tr>

							<?php 
						} else {
						?>
						  <tr bgcolor="#ECE9D8" class="texto"> 
							<td align="center" colspan="13"><b>Não foram encontrados registros</b></td>
						  </tr>
						<?php
						}
					?>
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
