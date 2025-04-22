<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

if(isset($controller->logado) && $controller->logado) {
    
    //Definindo valor máximo
    if($controller->usuario->b_IsLogin_pagamento_free()) {
            $total_diario_const = $RISCO_GAMERS_FREE_TOTAL_DIARIO;
            $pagamentos_diario_const = $RISCO_GAMERS_FREE_PAGAMENTOS_DIARIO;
    //	Gamers VIP- Pagamento Online = no max R$1000,00 por día por usuário (ver getVendasMoneyTotalDiarioOnline()) em até 20 vezes
    } elseif($controller->usuario->b_IsLogin_pagamento_vip()) {
            $total_diario_const = $RISCO_GAMERS_VIP_TOTAL_DIARIO;
            $pagamentos_diario_const = $RISCO_GAMERS_VIP_PAGAMENTOS_DIARIO;
    //	Gamers - Pagamento Online = no max R$450,00 por día por usuário (ver getVendasMoneyTotalDiarioOnline()) em até 10 vezes
    } else {
            $total_diario_const = $RISCO_GAMERS_TOTAL_DIARIO;
            $pagamentos_diario_const = $RISCO_GAMERS_PAGAMENTOS_DIARIO;
    }

    if(!isset($arr_venda_modelos)){
        pg_result_seek($rs_venda_modelos, 0);
        $arr_venda_modelos = pg_fetch_all($rs_venda_modelos);
    }
    
?>
<div class="col-md-12 col-xs-12 col-sm-12 col-lg-12 txt-azul-claro top20">
    <h5><strong>Número do pedido: <span class="txt-cinza"><?php echo formata_codigo_venda($venda_id);?></span></strong> </h5>
</div>
<div class="col-md-12 col-xs-12 col-sm-12 col-lg-12 txt-cinza espacamento top20">
<?php
        foreach($arr_venda_modelos as $ind => $rs_venda_modelos_row){
            
            $qtde = $rs_venda_modelos_row['vgm_qtde'];
            $valor = $rs_venda_modelos_row['vgm_valor'];
            if(!$test_opr_need_cpf) {
                $test_opr_need_cpf = checkingNeedCPFGamer($rs_venda_modelos_row['vgm_opr_codigo']);
            }//end if(!$test_opr_need_cpf)
            $total_geral += $valor*$qtde;
            if($rs_venda_modelos_row['vgm_valor_eppcash']!="") {
                $total_geral_epp_cash += $rs_venda_modelos_row['vgm_valor_eppcash']*$qtde;
            }
            else {
                $total_geral_epp_cash += $rs_venda_modelos_row['vg_valor_eppcash'];
            }
            
            if(isset($rs_venda_modelos_row['vgm_ogpm_id']) && !empty($rs_venda_modelos_row['vgm_ogpm_id']) || $rs_venda_modelos_row['vg_deposito_em_saldo'] != 0){

                if($rs_venda_modelos_row['vg_deposito_em_saldo'] == 0) {
                    $iof = (new Produto)->buscaIOF($rs_venda_modelos_row['vgm_ogpm_id']) ? "Incluso" : "";
                } //end if($rs_venda_modelos_row['vg_deposito_em_saldo'] == 0) 
                else $iof = "";
?>
                <div class="col-xs-12 col-sm-12 bg-branco hidden-lg hidden-md espacamento borda-fina">
                    <div class="row">
                        <div class="col-xs-3 col-sm-5">
                            Produto:
                        </div>
                        <div class="col-xs-9 col-sm-7">
                            <strong><?php echo $rs_venda_modelos_row['vgm_nome_produto'];
                                if($rs_venda_modelos_row['vgm_nome_modelo']!=""){?> - <?php echo $rs_venda_modelos_row['vgm_nome_modelo']?><?php }?></strong>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-5 col-sm-5">
                            IOF.:
                        </div>
                        <div class="col-xs-7 col-sm-7">
                            <?php echo $iof;?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-5 col-sm-5">
                            Valor unit.:
                        </div>
                        <div class="col-xs-7 col-sm-7">
                            <?php echo number_format($valor, 2, ',', '.')?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-5 col-sm-5">
                            Qtde.:
                        </div>
                        <div class="col-xs-7 col-sm-7">
                           <?php echo htmlspecialchars($qtde, ENT_QUOTES);?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-5 col-sm-5">
                            Total:
                        </div>
                        <div class="col-xs-7 col-sm-7">
                           <?php echo number_format($valor*$qtde, 2, ',', '.');?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-5 col-sm-5 nowrap">
                            Preço em <img src="/imagens/eppcash_mini.png" width="30" height="17" border="0" alt="EPPCash" title="EPPCash"">:
                        </div>
                        <div class="col-xs-7 col-sm-7">
                           <?php echo (($rs_venda_modelos_row['vgm_valor_eppcash']!="")?get_info_EPPCash_NO_Table($rs_venda_modelos_row['vgm_valor_eppcash']*$qtde):number_format($rs_venda_modelos_row['vg_valor_eppcash'], 0, ',', '.'));?>
                        </div>
                    </div>
                </div>
<?php
            }
        }
        
        if(count($arr_venda_modelos) > 1){
?>
    <div class="col-xs-12 col-sm-12 hidden-lg hidden-md bg-cinza-claro espacamento borda-fina">
        <div class="row">
            <div class="col-xs-5 col-sm-5">
                <strong>Total:</strong>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-5 col-sm-5">
                Total: 
            </div>
            <div class="col-xs-7 col-sm-7">
               <?php echo number_format($total_geral, 2, ',', '.') ?>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-5 col-sm-5">
                Preço em: 
            </div>
            <div class="col-xs-7 col-sm-7">
               <?php echo get_info_EPPCash_NO_Table($total_geral_epp_cash); ?>
            </div>
        </div>
    </div>
<?php
        }
?>
    <div class="col-md-12 col-lg-12 hidden-sm hidden-xs bg-cinza-claro">
        <table class="table bg-branco txt-preto">
        <thead>
            <tr class="bg-cinza-claro text-center">
                <th class="txt-left">Produto</th>
                <th>I.O.F.</th>
                <th>Valor unitário</th>
                <th>Qtde.</th>
                <th>Total</th>
                <th>Preço em</th>
            </tr>
        </thead>
        <tbody>
            
<?php
        $total_geral = 0;
        $total_geral_epp_cash = 0;
       
        //Variavel retorna necessidade de solicitação de CPF do usuário Gamer
        $test_opr_need_cpf = false;

        foreach($arr_venda_modelos as $ind => $rs_venda_modelos_row){
            
            $qtde = $rs_venda_modelos_row['vgm_qtde'];
            $valor = $rs_venda_modelos_row['vgm_valor'];
            if(!$test_opr_need_cpf) {
                $test_opr_need_cpf = checkingNeedCPFGamer($rs_venda_modelos_row['vgm_opr_codigo']);
            }//end if(!$test_opr_need_cpf)
            $total_geral += $valor*$qtde;
            if($rs_venda_modelos_row['vgm_valor_eppcash']!="") {
                $total_geral_epp_cash += $rs_venda_modelos_row['vgm_valor_eppcash']*$qtde;
            }
            else {
                $total_geral_epp_cash += $rs_venda_modelos_row['vg_valor_eppcash'];
            }
            
            if(isset($rs_venda_modelos_row['vgm_ogpm_id']) && !empty($rs_venda_modelos_row['vgm_ogpm_id']) || $rs_venda_modelos_row['vg_deposito_em_saldo'] != 0){

                if($rs_venda_modelos_row['vg_deposito_em_saldo'] == 0) {
                    $iof = (new Produto)->buscaIOF($rs_venda_modelos_row['vgm_ogpm_id']) ? "Incluso" : "";
                } //end if($rs_venda_modelos_row['vg_deposito_em_saldo'] == 0) 
                else $iof = "";
?>
                    
                <tr class="text-center trListagem">
                  <td class="text-left">
                      <input name="produtos[]" id="produtos" type="hidden" value="<?php echo $rs_venda_modelos_row['vgm_ogpm_id'];?>" />
                      <input name="v<?php echo $rs_venda_modelos_row['vgm_ogpm_id'];?>" id="v<?php echo $rs_venda_modelos_row['vgm_ogpm_id'];?>" type="hidden" value="<?php echo $valor;?>" />
                      <input name="e<?php echo $rs_venda_modelos_row['vgm_ogpm_id'];?>" id="e<?php echo $rs_venda_modelos_row['vgm_ogpm_id'];?>" type="hidden" value="<?php echo $rs_venda_modelos_row['vgm_valor_eppcash'];?>" />
                      <input name="q<?php echo $rs_venda_modelos_row['vgm_ogpm_id'];?>" id="q<?php echo $rs_venda_modelos_row['vgm_ogpm_id'];?>" type="hidden" value="<?php echo $qtde;?>" />
                      <?php echo $rs_venda_modelos_row['vgm_nome_produto']; ?>
                      <?php if($rs_venda_modelos_row['vgm_nome_modelo']!=""){?> - <?php echo $rs_venda_modelos_row['vgm_nome_modelo']?><?php }?>
                  </td>
                  <td><?php echo $iof;?></td>
                  <td><?php echo number_format($valor, 2, ',', '.')?></td>
                  <td><?php echo htmlspecialchars($qtde, ENT_QUOTES);?></td>
                  <td><?php echo number_format($valor*$qtde, 2, ',', '.');?></td>
                  <td><?php echo (($rs_venda_modelos_row['vgm_valor_eppcash']!="")?get_info_EPPCash_NO_Table($rs_venda_modelos_row['vgm_valor_eppcash']*$qtde):number_format($rs_venda_modelos_row['vg_valor_eppcash'], 0, ',', '.'));?></td>
                </tr>

<?php
            }//end if(isset($rs_venda_modelos_row['vgm_ogpm_id']) && !empty($rs_venda_modelos_row['vgm_ogpm_id']))
        } //end while

        if($total_geral>$total_diario_const) {
            $msg = "O valor máximo por Pedido é de R$".number_format($total_diario_const,2,",",".");
            echo "<script>manipulaModal(1,'" . str_replace("\n", "\\n", $msg) . "','Erro') ; $('#modal-load').on('hidden.bs.modal', function () { location.href='/game/pedido/passo-1.php' });</script>";

            die;
        }
        
        if(count($arr_venda_modelos) > 1){
?>
            <tr class="bg-cinza-claro text-center">
                <td colspan="3">&nbsp;</td>
                <td><strong>Total:</strong></td>
                <td><?php echo number_format($total_geral, 2, ',', '.') ?></td>
                <td><?php echo get_info_EPPCash_NO_Table($total_geral_epp_cash); ?></td>
            </tr>
<?php
        }
?>
        </tbody>
        </table>
    </div>
</div>
<?php
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
$sql_total = "select total,taxas from tb_pag_compras where tipo_cliente = 'M' and idvenda = ".$GLOBALS['_SESSION']['venda'].";";
$rs_total = SQLexecuteQuery($sql_total);
$rs_total_row = pg_fetch_array($rs_total);
if($rs_total_row['total'] == 0) {
        $sql_update_total = "update tb_pag_compras set total = ".(($total_geral+$rs_total_row['taxas'])*100)." where tipo_cliente = 'M' and idvenda = ".$GLOBALS['_SESSION']['venda'];
        $rs_update_total = SQLexecuteQuery($sql_update_total);
}
//fim do trecho que atualiza o total no registro de pagamento
}//end if(isset($controller->logado) && $controller->logado) {
else {
    echo "Acesso não permitido";
}
?>
