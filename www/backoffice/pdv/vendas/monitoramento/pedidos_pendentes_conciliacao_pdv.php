<?php 
    $sql  = "select vg_pagto_tipo, count(*) as qtde_concilia
                 from tb_dist_venda_games vg 
                         inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                         inner join dist_usuarios_games ug on ug.ug_id = vg.vg_ug_id 
                 where (vg.vg_concilia is null or vg.vg_concilia = 0) /* and vg.vg_pagto_tipo = '2' */
                 and vg.vg_ultimo_status not in (" . $GLOBALS['STATUS_VENDA']['VENDA_CANCELADA'] . ")
                 group by vg_pagto_tipo order by vg_pagto_tipo desc";
                 // "" . $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'] . ", "
//if(true || b_IsUsuarioReinaldo()) { 
//echo "(R) ".str_replace("\n", "<br>\n", $sql)."<br>";
//}

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
                <td class="txt-vermelho" width="30" align="right" valign="top"><b><?php echo $rs_concilia_row['qtde_concilia'] ?></b></td>
                <td>
                    <a href="/pdv/vendas/com_pesquisa_vendas.php?BtnSearch=1&tf_v_concilia=0&tf_d_forma_pagto=<?php echo $pagto_tipo?>" class="menu"><?php echo $GLOBALS['FORMAS_PAGAMENTO_DESCRICAO'][$pagto_tipo] ?></a>
                </td>
            </tr>
<?php 
        } 
?>
        </table>
<?php 
    } 
?>