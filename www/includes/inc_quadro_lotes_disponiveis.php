    	        <?php if($vg_ultimo_status == $STATUS_VENDA['PROCESSAMENTO_REALIZADO'] || $vg_ultimo_status == $STATUS_VENDA['VENDA_REALIZADA']){?>
				<table border="0" cellspacing="0" width="90%" align="center">
    	        <tr bgcolor="E5E5EB">
    	          <td class="texto" height="20" colspan="6" align="center">
					&nbsp;<a href="/prepag2/dist_commerce/conta/cupom_impressao_selecao.php?venda=<?php echo $venda_id?>" class="link_azul" target="_top">Lotes disponíveis para impressão - Imprimir cupons</a>
    	          </td>
    	        </tr>
				<?php
						$sql = "select vgm_id, vgm_nome_produto, vgm_nome_modelo, 
									(select count(*) from tb_dist_venda_games_modelo_pins vgmp where vgmp.vgmp_impressao_qtde is not NULL and vgmp.vgmp_impressao_qtde > 0 and vgmp.vgmp_vgm_id = vgm_id) as impresso,
									(select count(*) from tb_dist_venda_games_modelo_pins vgmp where (vgmp.vgmp_impressao_qtde is NULL or vgmp.vgmp_impressao_qtde = 0) and vgmp.vgmp_vgm_id = vgm_id) as nao_impresso
								from tb_dist_venda_games vg 
								inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
								where vg.vg_id = " . $venda_id;
						$rs_pins = SQLexecuteQuery($sql);
						if($rs_pins && pg_num_rows($rs_pins) > 0){
				?>
    	        <tr bgcolor="F5F5FB">
    	          <td class="texto" height="20" colspan="6">
					<table border="0" cellspacing="01" width="90%" align="center">
						<tr class="texto">
							<td align="center"><b>Produto</b></td>
							<td align="center"><b>Não Impressos</b></td>
							<td align="center"><b>Impressos</b></td>
						</tr>
						<?php	while ($rs_pins_row = pg_fetch_array($rs_pins)){?>
						<tr class="texto">
							<td align="center">
								<a href="/prepag2/dist_commerce/conta/cupom_impressao_selecao.php?venda=<?php echo $venda_id?>&btMostrar=1&cmb_produto_modelo=<?php echo $rs_pins_row['vgm_id']?>" class="link_azul" target="_top">
				   	          	<?php echo $rs_pins_row['vgm_nome_produto']?> 
					          	<?php if($rs_pins_row['vgm_nome_modelo']!=""){?> - <?php echo $rs_pins_row['vgm_nome_modelo']?><?}?>
					          	</a>
							</td>
							<td align="center">
								<a href="/prepag2/dist_commerce/conta/cupom_impressao_selecao.php?venda=<?php echo $venda_id?>&btMostrar=1&cmb_produto_modelo=<?php echo $rs_pins_row['vgm_id']?>&tf_pin_impressao_qtde=2" class="link_azul" target="_top">
								<?php echo $rs_pins_row['nao_impresso']?>
					          	</a>
							</td>
							<td align="center">
								<a href="/prepag2/dist_commerce/conta/cupom_impressao_selecao.php?venda=<?php echo $venda_id?>&btMostrar=1&cmb_produto_modelo=<?php echo $rs_pins_row['vgm_id']?>&tf_pin_impressao_qtde=1" class="link_azul" target="_top">
								<?php echo $rs_pins_row['impresso']?>
					          	</a>
							</td></tr>
						</tr>
						<?php	} ?>
					</table>
    	          </td>
    	        </tr>
				<?php		}?>    	        
				</table>
    	        <?php } ?>

