<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

if(isset($controller->logado) && $controller->logado) {
    
    //Recupera usuario
    $usuarioId = $controller->usuario->getId();

    //Recupera Id da venda
    if(empty($venda_id)) {
        if(!$venda_id_request_nome) $venda_id_request_nome = 'venda';
        $venda_id = $GLOBALS['_POST'][$venda_id_request_nome];
        if(!$venda_id) $venda_id = $GLOBALS['_SESSION']['venda'];
    }//end if(empty($venda_id)) 

    //Guarda id da venda no session
    $GLOBALS['_SESSION']['venda'] = $venda_id;

    //Validacoes
    $msg = "";	

    //Valida id da venda
    if(!$venda_id || !is_numeric($venda_id)){		
            $msg = "Id da venda inválido ou não fornecido.";
    }

    //Recupera a venda
    if($msg == ""){
            $sql  = " 
                    select * 
                    from tb_venda_games vg
                    where 
                        vg.vg_id = " . $venda_id . " 
                        and vg.vg_ug_id=" . $usuarioId."; ";
            $rs_venda = SQLexecuteQuery($sql);
            if(!$rs_venda || pg_num_rows($rs_venda) == 0) $msg = "Nenhuma venda encontrada.";
    }
    
    if(((strlen($GLOBALS['_SESSION']['pagamento.pagto.deposito.em.saldo'])>0) && (strlen($GLOBALS['_SESSION']['pagamento.pagto.deposito.em.saldo.num.docto'])>0))) {
            //Recupera modelos para deposito em saldo
            if($msg == ""){

                    if($GLOBALS['_SESSION']['pagamento.pagto.deposito.em.saldo']=='2') {
                            $sql  = " 
                                    select vg.*, 
                                            1 as vgm_qtde, 
                                            vg.vg_deposito_em_saldo_valor as vgm_valor, 
                                            0 as vgm_perc_desconto, 
                                            'Crédito online EPP Cash (R\$' || to_char(bbg_valor-bbg_valor_taxa,'FM9999.00') || ')' as vgm_nome_produto,
                                            '' as vgm_nome_modelo 
                                    from tb_venda_games vg
                                    inner join boleto_bancario_games bbg on bbg.bbg_vg_id = vg.vg_id 
                                    where 
                                        vg.vg_id = " . $venda_id . " 
                                        and vg.vg_ug_id=" . $usuarioId.";";
                    } 
                    else {
                            $sql  = " 
                                    select vg.*, 
                                        1 as vgm_qtde, 
                                        (total/100-taxas) as vgm_valor, 
                                        0 as vgm_perc_desconto, 
                                        'Crédito online EPP Cash (R\$' || to_char((total/100-taxas),'FM9999.00') || ')' as vgm_nome_produto,
                                        '' as vgm_nome_modelo 
                                    from tb_venda_games vg 
                                        inner join tb_pag_compras pg on pg.idvenda = vg.vg_id 
                                    where vg.vg_id = " . $venda_id . " 
                                          and vg.vg_ug_id=" . $usuarioId.";";
                    }
                    $rs_venda_modelos = SQLexecuteQuery($sql);
                    if(!$rs_venda_modelos || pg_num_rows($rs_venda_modelos) == 0) {
                            $msg = "Nenhum produto encontrado. (4335A)";
                            gravaLog_DRUPAL_TMP("Em venda_e_modelos_logica.php: {venda_id = '$venda_id', $msg} \n\t$sql\n");
                    }
            }
            //Variaveis necessárias para a consulta de CPF não perder a SESSÃO
            $aux['pagamento.pagto.deposito.em.saldo'] = $GLOBALS['_SESSION']['pagamento.pagto.deposito.em.saldo'];
            $aux['pagamento.pagto.deposito.em.saldo.num.docto'] = $GLOBALS['_SESSION']['pagamento.pagto.deposito.em.saldo.num.docto'];
            // Reset pagamento deposito
            $GLOBALS['_SESSION']['pagamento.pagto.deposito.em.saldo'] = "";
            $GLOBALS['_SESSION']['pagamento.pagto.deposito.em.saldo.num.docto'] = "";
            unset($GLOBALS['_SESSION']['pagamento.pagto.deposito.em.saldo']);
            unset($GLOBALS['_SESSION']['pagamento.pagto.deposito.em.saldo.num.docto']);
    } //end if(((strlen($GLOBALS['_SESSION']['pagamento.pagto.deposito.em.saldo'])>0) && (strlen($GLOBALS['_SESSION']['pagamento.pagto.deposito.em.saldo.num.docto'])>0)))
    else {
            //Recupera modelos normal
            if($msg == ""){
                    $sql  = "select * 
                             from tb_venda_games vg 
                                inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                             where vg.vg_id = " . $venda_id . " 
                                 and vg.vg_ug_id=" . $usuarioId.";";
                    $rs_venda_modelos = SQLexecuteQuery($sql);
                    if(!$rs_venda_modelos || pg_num_rows($rs_venda_modelos) == 0) $msg = "Nenhum produto encontrado (1rew).";
            }
    }//end else do if(((strlen($GLOBALS['_SESSION']['pagamento.pagto.deposito.em.saldo'])>0) && (strlen($GLOBALS['_SESSION']['pagamento.pagto.deposito.em.saldo.num.docto'])>0)))

    //Redireciona se ha algum dado invalido
    //----------------------------------------------------
    if($msg != ""){
    ?>
    <form name="pagamento" id="pagamento" method="POST" action="/game/mensagem.php">
        <input type='hidden' name='msg' id='msg' value='<?php echo $msg; ?>'>
        <input type='hidden' name='titulo' id='titulo' value='Comprovante'>
        <input type='hidden' name='link' id='link' value='/game/conta/pedidos.php'>
    </form> 
    <script language='javascript'>
        document.getElementById("pagamento").submit();
    </script>        
    <?php
        die();
    }//end if($msg != "")
}//end if(isset($controller->logado) && $controller->logado)
else {
    die("Não logado");
    echo "Acesso não permitido";
}
?>
