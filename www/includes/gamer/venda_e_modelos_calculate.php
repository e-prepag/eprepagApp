<?php
	// Após venda_e_modelos_logica.php -> calcula $total_geral e $taxas
	// Usado em lugar de 	$total_geral_1 = mostraCarrinho_pag(false, 1); para calular $total_geral 
	// é idêntico a venda_e_modelos_view.php
        $total_geral = 0;
        $total_geral_epp_cash = 0;
        $taxas = 0;
        $vg_pagto_tipo_tmp = 0;
        if($rs_venda_modelos) {
                pg_result_seek($rs_venda_modelos, 0);
                while ($rs_venda_modelos_row = pg_fetch_array($rs_venda_modelos)){
                        $qtde = $rs_venda_modelos_row['vgm_qtde'];
                        $valor = $rs_venda_modelos_row['vgm_valor'];
                        $total_geral += $valor*$qtde;
                        $total_geral_epp_cash += $rs_venda_modelos_row['vgm_valor_eppcash']*$qtde;
                        // devem ser todos o sregistros da mesma forma de pagamento, na pratica usamos o valor do último registro
                        $vg_pagto_tipo_tmp = $rs_venda_modelos_row['vg_pagto_tipo'];
                } 

                if($vg_pagto_tipo_tmp==$GLOBALS['FORMAS_PAGAMENTO']['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) { 
                        if($total_geral < $GLOBALS['RISCO_GAMERS_VALOR_MIN_PARA_TAXA']) $taxas = $GLOBALS['BRADESCO_TRANSFERENCIA_ENTRE_CONTAS_TAXA_ADICIONAL'];					
                } else if($vg_pagto_tipo_tmp==$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BB_DEBITO_SUA_CONTA']) { 
                        if($total_geral < $GLOBALS['RISCO_GAMERS_VALOR_MIN_PARA_TAXA']) $taxas = $GLOBALS['BANCO_DO_BRASIL_TAXA_DE_SERVICO'];
                        $total_geral += $taxas;
                } else if($vg_pagto_tipo_tmp==$GLOBALS['PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC']) { 
                        if($total_geral < $GLOBALS['RISCO_GAMERS_VALOR_MIN_PARA_TAXA']) $taxas = $GLOBALS['BANCO_ITAU_TAXA_DE_SERVICO'];
                        $total_geral += $taxas;
                } else if($vg_pagto_tipo_tmp==$GLOBALS['PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC']) { 
                        if($total_geral < $GLOBALS['RISCO_GAMERS_VALOR_MIN_PARA_TAXA']) $taxas = $GLOBALS['BANCO_EPP_TAXA_DE_SERVICO'];
                        $total_geral += $taxas;
                }  
        }
        
?>
