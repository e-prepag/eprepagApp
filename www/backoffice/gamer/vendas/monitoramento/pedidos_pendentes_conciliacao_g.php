<?php 
    require_once $raiz_do_projeto."includes/main.php";
    require_once $raiz_do_projeto."includes/gamer/main.php";
    
    $qtde_concilia = 0;
	$sql  = "select vg_pagto_tipo, count(*) as qtde_concilia
			 from tb_venda_games vg 
			 where (vg.vg_concilia is null or vg.vg_concilia = 0)
				and vg.vg_ultimo_status = " . $GLOBALS['STATUS_VENDA']['DADOS_PAGTO_RECEBIDO'] . "
				and vg.vg_ug_id <> " . $GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY'] . "
			 group by vg_pagto_tipo order by vg_pagto_tipo desc";
//echo str_replace("\n", "<br>\n", $sql)."<br>";
	$rs_concilia = SQLexecuteQuery($sql);

    if($rs_concilia && pg_num_rows($rs_concilia) > 0)
    {
?>
        <table class="table  table-striped top10">
<?php 
        while($rs_concilia_row = pg_fetch_array($rs_concilia))
        {
            $pagto_tipo = $rs_concilia_row['vg_pagto_tipo']; 
?>
            <tr>
                <td width="30" align="right" class="txt-vermelho" valign="top"><b><?php echo $rs_concilia_row['qtde_concilia'] ?></b></td>
                <td width="10">&nbsp;</td>
                <td>
                    <a href="/gamer/vendas/com_pesquisa_vendas.php?BtnSearch=1&tf_v_concilia=0&tf_d_forma_pagto=<?php echo $pagto_tipo?>&tf_v_status=<?php echo $GLOBALS['STATUS_VENDA']['DADOS_PAGTO_RECEBIDO'] ?>&tf_v_origem=mo&tf_v_data_inclusao_fim=<?php echo date("d/m/Y");?>&tf_v_data_inclusao_ini=01/01/2008">
<?php
                        echo $GLOBALS['FORMAS_PAGAMENTO_DESCRICAO'][getCodigoCaracterParaPagto($pagto_tipo)];
?>
                    </a>
                </td>
            </tr>
<?php 
                } 
?>
        </table>
<?php 
    } 
?>