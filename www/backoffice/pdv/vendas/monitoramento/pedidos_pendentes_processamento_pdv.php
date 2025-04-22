<?php 
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";
    $sql  = "select vg_ultimo_status, count(*) as qtde_processa
                 from tb_dist_venda_games vg 
                        inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                        inner join dist_usuarios_games ug on ug.ug_id = vg.vg_ug_id 
                 where vg.vg_ultimo_status in (" . $GLOBALS['STATUS_VENDA']['PEDIDO_EFETUADO'] . "," . $GLOBALS['STATUS_VENDA']['PEDIDO_EM_STANDBY'] . "," . $GLOBALS['STATUS_VENDA']['AGUARDANDO_PROCESSAMENTO'] . ")
                 group by vg.vg_ultimo_status
                 order by vg.vg_ultimo_status";
//echo "sql: ".str_replace("\n", "<br>\n", $sql)."<br>";
//echo "<pre>";
//print_r($GLOBALS['STATUS_VENDA_DESCRICAO']);
//echo "<pre>";

/*
if(true || b_IsUsuarioReinaldo()) { 
echo "(R) ".str_replace("\n", "<br>\n", $sql)."<br>";
}
*/
$rs_processa = SQLexecuteQuery($sql);

    if($rs_processa && pg_num_rows($rs_processa) > 0)
    { 
?>
        <table class="table  table-striped top10">
<?php 
        while($rs_processa_row = pg_fetch_array($rs_processa))
        {
            $ultimo_status = $rs_processa_row['vg_ultimo_status']; 
?>
            <tr>
                <td width="30" class="txt-vermelho" align="right" valign="top"><b><?php echo $rs_processa_row['qtde_processa'] ?></b></td>
                <td>
                    <a href="/pdv/vendas/com_pesquisa_vendas.php?BtnSearch=1&tf_v_status=<?php echo $ultimo_status?>" class="menu"><?php echo $GLOBALS['STATUS_VENDA_DESCRICAO'][$ultimo_status] ?></a>
                </td>
            </tr>
<?php 
        }
?>
        </table>
<?php 
    }
    else
    {
?>
        <table class="table top10">
            <tr>
                <td width="30" align="right" valign="top"><b>0</b></td>
                <td class="txt-vermelho">Sem pedidos pendentes de processamento</font><br>
                </td>
            </tr>
        </table>
<?php 
    }