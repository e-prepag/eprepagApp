					<table border="0" cellspacing="0" width="30%" align="center" <?php if($GLOBALS['_SESSION']['is_integration']==true) echo 'style="display: none"'; ?>>
		    	        <tr bgcolor="F0F0F0">
		    	          <td class="texto" align="center" height="25"><b>Número do Pedido</b></td>
		    	        </tr>
		    	        <tr bgcolor="F0F0F0">
		    	          <td class="texto" align="center" height="25"><font size="+1"><?php echo formata_codigo_venda($venda_id)?></font></td>
		    	        </tr>
					</table>

					<br>
					<table border="0" cellspacing="0" width="90%" align="center" <?php if($GLOBALS['_SESSION']['is_integration']==true) echo 'style="display: none"'; ?>>
		    	        <tr bgcolor="F0F0F0">
		    	          <td class="texto" align="center" height="25"><b>Produto</b></td>
		    	          <td class="texto" align="center"><b>I.O.F.</b></td>
		    	          <td class="texto" align="center"><b>Quantidade</b></td>
		    	          <td class="texto" align="right"><b>Preço Unitário</b></td>
		    	          <td class="texto" align="right"><b>Preço Total</b></td>
		    	          <td class="texto" align="right"></td>
		    	        </tr>
<?php
					$total_geral = 0;
					$total_geral_epp_cash = 0;
					pg_result_seek($rs_venda_modelos, 0);
                                        
                    //Variavel retorna necessidade de solicitação de CPF do usuário Gamer
                    $test_opr_need_cpf = false;

					while ($rs_venda_modelos_row = pg_fetch_array($rs_venda_modelos)){
                                           // echo "<pre>".print_r($rs_venda_modelos_row,true)."</pre>";
						$qtde = $rs_venda_modelos_row['vgm_qtde'];
						$valor = $rs_venda_modelos_row['vgm_valor'];
                        if(!$test_opr_need_cpf) {
                            if(function_exists("checkingNeedCPF"))
                                $test_opr_need_cpf = checkingNeedCPF($rs_venda_modelos_row['vgm_opr_codigo']);
                            
                        }//end if(!$test_opr_need_cpf)
						$total_geral += $valor*$qtde;
						$total_geral_epp_cash += $rs_venda_modelos_row['vgm_valor_eppcash']*$qtde;
?>
		    	        <tr>
		    	          <td class="texto" height="25" width="200">
		    	          	&nbsp;&nbsp;
		    	          	<?php echo $rs_venda_modelos_row['vgm_nome_produto']?> 
		    	          	<?php if($rs_venda_modelos_row['vgm_nome_modelo']!=""){?> - <?php echo $rs_venda_modelos_row['vgm_nome_modelo']?><?php }?>
		    	          </td>
		    	          <td class="texto" align="center"><?php echo ((!empty($rs_venda_modelos_row['vgm_ogpm_id']))?(((new Produto)->buscaIOF($rs_venda_modelos_row['vgm_ogpm_id']))?"incluso":"&nbsp;"):"&nbsp;") ?></td>
		    	          <td class="texto" align="center"><?php echo $qtde?></td>
		    	          <td class="texto" align="right"><?php echo number_format($valor, 2, ',', '.')?></td>
		    	          <td class="texto" align="right"><?php echo number_format($valor*$qtde, 2, ',', '.')?></td>
		    	          <td class="texto" align="right"><?php 
							//if(isset($usuarioGames)) {
							//	if($usuarioGames->b_IsLogin_valorPINEPPCash()) {
										echo "<nobr>".get_info_EPPCash_NO_Table($rs_venda_modelos_row['vgm_valor_eppcash']*$qtde)."</nobr>";
							//	}
							//}
							?></td>
		    	        </tr>
<?php 	
					} 
					
					// colocar atualização do total no registro de pagamento	
					
					//Recupera usuario
					//if(isset($_SESSION['usuarioGames_ser']) && !is_null($_SESSION['usuarioGames_ser'])){ $usuarioGames = unserialize($_SESSION['usuarioGames_ser']); }
					//if($usuarioGames->b_IsLogin_Wagner()) 					
					{
                                                if(isset($pagto_tipo)) {
                                                    if($total_geral  < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA) {
                                                        if($pagto_tipo == $PAGAMENTO_PIN_EPREPAG_NUMERIC) {
                                                                $taxa = $PAGAMENTO_PIN_EPP_TAXA;
                                                        }
                                                        else if($pagto_tipo == $PAGAMENTO_VISA_CREDITO_NUMERIC) {
                                                                $taxa = $PAGAMENTO_VISA_CREDITO_TAXA;
                                                        }
                                                        else if($pagto_tipo == $PAGAMENTO_MASTER_CREDITO_NUMERIC) {
                                                                $taxa = $PAGAMENTO_MASTER_CREDITO_TAXA;
                                                        }                       
                                                        else if($pagto_tipo == $PAGAMENTO_VISA_DEBITO_NUMERIC) {
                                                                $taxa = $PAGAMENTO_VISA_DEBITO_TAXA;
                                                        }                       
                                                        else if($pagto_tipo == $PAGAMENTO_MASTER_DEBITO_NUMERIC) {
                                                                $taxa = $PAGAMENTO_MASTER_DEBITO_TAXA;
                                                        }                       
                                                        else if($pagto_tipo == $PAGAMENTO_ELO_DEBITO_NUMERIC) {
                                                                $taxa = $PAGAMENTO_ELO_DEBITO_TAXA;
                                                        }                       
                                                        else if($pagto_tipo == $PAGAMENTO_ELO_CREDITO_NUMERIC) {
                                                                $taxa = $PAGAMENTO_ELO_CREDITO_TAXA;
                                                        }                       
                                                        else if($pagto_tipo == $PAGAMENTO_DINERS_CREDITO_NUMERIC) {
                                                                $taxa = $PAGAMENTO_DINERS_CREDITO_TAXA;
                                                        }                       
                                                        else if($pagto_tipo == $PAGAMENTO_DISCOVER_CREDITO_NUMERIC) {
                                                                $taxa = $PAGAMENTO_DISCOVER_CREDITO_TAXA;
                                                        }                       
                                                    }//end if($total_geral  < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA) 
                                                    else $taxa = 0;
                                                }//end if(isset($pagto_tipo))
						$sql_total = "select total,taxas from tb_pag_compras where tipo_cliente = 'M' and idvenda = ".$GLOBALS['_SESSION']['venda'];
						$rs_total = SQLexecuteQuery($sql_total);
						$rs_total_row = pg_fetch_array($rs_total);
						if($rs_total_row['total'] == 0) {
							$sql_update_total = "update tb_pag_compras set total = ".(($total_geral+$rs_total_row['taxas'])*100)." where tipo_cliente = 'M' and idvenda = ".$GLOBALS['_SESSION']['venda'];
							$rs_update_total = SQLexecuteQuery($sql_update_total);
						}
					}
					//fim do trecho que atualiza o total no registro de pagamento
?>

				<?php 	
					$taxas = 0;
					if($rs_venda_modelos_row['vg_pagto_tipo']==$GLOBALS['FORMAS_PAGAMENTO']['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) { 
						if($total_geral  < $GLOBALS['RISCO_GAMERS_VALOR_MIN_PARA_TAXA']) {
                                                    $taxas = $GLOBALS['BRADESCO_TRANSFERENCIA_ENTRE_CONTAS_TAXA_ADICIONAL'];
                                                } //end if($total_geral  < $GLOBALS['RISCO_GAMERS_VALOR_MIN_PARA_TAXA'])
                                                else {
                                                    $taxas = 0;
                                                }//end else do if($total_geral  < $GLOBALS['RISCO_GAMERS_VALOR_MIN_PARA_TAXA'])
					} else if($rs_venda_modelos_row['vg_pagto_tipo']==$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BB_DEBITO_SUA_CONTA']) { 
                                                if($total_geral  < $GLOBALS['RISCO_GAMERS_VALOR_MIN_PARA_TAXA']) {
                                                    $taxas = $GLOBALS['BANCO_DO_BRASIL_TAXA_DE_SERVICO'];
                                                } //end if($total_geral  < $GLOBALS['RISCO_GAMERS_VALOR_MIN_PARA_TAXA'])
                                                else {
                                                    $taxas = 0;
                                                }//end else do if($total_geral  < $GLOBALS['RISCO_GAMERS_VALOR_MIN_PARA_TAXA'])
					}
					if($taxas>0) { 
					?>
		    	        <tr bgcolor="F0F0F0">
		    	          <td colspan="2">&nbsp;</td>
		    	          <td class="texto" align="right" height="25"><b>Taxa:</b></td>
		    	          <td class="texto" align="right"><b><?php echo number_format($taxas, 2, ',', '.')?></b></td>
						  <td class="texto" align="right"></td>
		    	        </tr>
				<?php 	}  ?>
		    	        <tr bgcolor="F0F0F0">
		    	          <td colspan="3">&nbsp;</td>
		    	          <td class="texto" align="right" height="25"><b>Total</b></td>
		    	          <td class="texto" align="right"><b><?php echo number_format($total_geral+$taxas, 2, ',', '.')?></b></td>
						  <td class="texto" align="right">
						  <?php 
                                                    echo "<nobr>".get_info_EPPCash_NO_Table($total_geral_epp_cash)."</nobr>";
                                                  ?>
							</td>
		    	        </tr>
					</table>
