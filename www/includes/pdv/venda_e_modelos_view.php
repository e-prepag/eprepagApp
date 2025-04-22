					<table border="0" cellspacing="0" width="30%" align="center">
		    	        <tr bgcolor="F0F0F0">
		    	          <td class="texto" align="center" height="25"><b>Número do Pedido</b></td>
		    	        </tr>
		    	        <tr bgcolor="F0F0F0">
		    	          <td class="texto" align="center" height="25"><font size="+1"><?=formata_codigo_venda($venda_id)?></font></td>
		    	        </tr>
					</table>

					<br>
					<table border="0" cellspacing="01" width="90%" align="center" bgcolor="F0F0F0">
		    	        <tr>
		    	          <td class="texto" align="center" height="25"><b>&nbsp;Produto&nbsp;</b></td>
		    	          <td class="texto" align="center"><b>&nbsp;Quantidade&nbsp;</b></td>
		    	          <td class="texto" align="center"><b>Preço &nbsp;Unitário&nbsp;</b></td>
		    	          <td class="texto" align="center"><b>Preço &nbsp;Total&nbsp;</b></td>
		    	          <td class="texto" align="center"><b>&nbsp;Desconto</b></td>
		    	          <td class="texto" align="center"><b>&nbsp;Valor Líquido&nbsp;</b></td>
		    	        </tr>
<?
					$total_geral = 0;
					pg_result_seek($rs_venda_modelos, 0);
					while ($rs_venda_modelos_row = pg_fetch_array($rs_venda_modelos)){
						$qtde = $rs_venda_modelos_row['vgm_qtde'];
						$valor = $rs_venda_modelos_row['vgm_valor'];
						$perc_desconto = $rs_venda_modelos_row['vgm_perc_desconto'];
						$geral = $valor*$qtde;
						$desconto = $geral*$perc_desconto/100;
						$repasse = $geral - $desconto;
						
						$qtde_total += $qtde;
						$total_geral += $geral;
						$total_desconto += $desconto;
						$total_repasse += $repasse;
?>
		    	        <tr bgcolor="FFFFFF">
		    	          <td class="texto" height="25" width="200">
		    	          	&nbsp;&nbsp;
		    	          	<?=$rs_venda_modelos_row['vgm_nome_produto']?> 
		    	          	<?if($rs_venda_modelos_row['vgm_nome_modelo']!=""){?> - <?=$rs_venda_modelos_row['vgm_nome_modelo']?><?}?>
		    	          </td>
		    	          <td class="texto" align="center"><?=$qtde?></td>
		    	          <td class="texto" align="right"><?=number_format($valor, 2, ',', '.')?></td>
		    	          <td class="texto" align="right"><?=number_format($geral, 2, ',', '.')?></td>
		    	          <td class="texto" align="right"><?=number_format($desconto, 2, ',', '.')?></td>
		    	          <td class="texto" align="right"><?=number_format($repasse, 2, ',', '.')?></td>
		    	        </tr>
				<?	} ?>
		    	        <tr bgcolor="FFFFFF">
		    	          <td class="texto" align="right" height="25" colspan="3"><b>Total:&nbsp;</b></td>
		    	          <td class="texto" align="right"><b><?=number_format($total_geral, 2, ',', '.')?></b></td>
		    	          <td class="texto" align="right"><b><?=number_format($total_desconto, 2, ',', '.')?></b></td>
		    	          <td class="texto" align="right"><b><?=number_format($total_repasse, 2, ',', '.')?></b></td>
		    	        </tr>
					</table>
