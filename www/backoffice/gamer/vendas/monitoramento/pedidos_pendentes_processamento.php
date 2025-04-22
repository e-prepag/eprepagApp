<?php 
    require_once $raiz_do_projeto."includes/main.php";
    require_once $raiz_do_projeto."includes/gamer/main.php"; 
    
    //Recupera qtde pedidos pendentes de processamento
	$qtde_processa = 0;
	$sql  = "select vg_pagto_tipo, count(*) as qtde_processa
			 from tb_venda_games vg 
			 where vg.vg_ultimo_status = " . $GLOBALS['STATUS_VENDA']['PAGTO_CONFIRMADO'] . "
				and vg.vg_ug_id <> " . $GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY'] . "
			 group by vg_pagto_tipo order by vg_pagto_tipo desc";
    
	$rs_processa = SQLexecuteQuery($sql);

    if($rs_processa && pg_num_rows($rs_processa) > 0)
    {
?>
        <table class="table  table-striped top10">
<?php 
        while($rs_processa_row = pg_fetch_array($rs_processa))
        {
            $pagto_tipo = $rs_processa_row['vg_pagto_tipo']; 
?>
            <tr>
                <td width="30" class="txt-vermelho" align="right" valign="top"><b><?php echo $rs_processa_row['qtde_processa'] ?></b></td>
                <td width="10">&nbsp;</td>
                <td>
                    <a href="/gamer/vendas/com_pesquisa_vendas.php?BtnSearch=1&tf_d_forma_pagto=<?php echo $pagto_tipo?>&tf_v_status=<?php echo $GLOBALS['STATUS_VENDA']['PAGTO_CONFIRMADO'] ?>&tf_v_data_inclusao_fim=<?php echo date("d/m/Y");?>&tf_v_data_inclusao_ini=01/01/2008">
<?php 
                        echo $GLOBALS['FORMAS_PAGAMENTO_DESCRICAO'][getCodigoCaracterParaPagto($pagto_tipo)];
?>
                    </a> <?php //echo "(tipo: ".$pagto_tipo.") ".getCodigoCaracterParaPagto($pagto_tipo);?>
                </td>
            </tr>
<?php 
        } 
?>
        </table>
<?php 
    } 
?>