<div class="lista bg-amarelo txt-azul">
        Monitor de usuários bloqueados(1 min)<br>
<?php
    //Recupera totais de usuários bloqueados
    $qtde_user_bloqueados = 0;
    $ids__user_bloqueados = "";
    $sql  = "select ug_id
            from usuarios_games 
                inner join usuarios_games_historico_bloqueio ON (ug_id = ughb_ug_id)
            where ughb_ug_flag_usando_saldo=1 and
                ug_flag_usando_saldo=1 
            group by ug_id
            having max(ughb_data) < (NOW()- '1 minutes'::interval)
            order by ug_id";
    //if(b_IsUsuarioWagner()) { echo str_replace("\n", "<br>\n", $sql)."<br>"; }
    $rs_user_bloqueados = SQLexecuteQuery($sql);
    while ($rs_user_bloqueados_row = pg_fetch_array($rs_user_bloqueados)) {
        if(!empty($ids__user_bloqueados)) {
            $ids__user_bloqueados .= ",";
        }//end if(!empty($ids__user_bloqueados))
        $ids__user_bloqueados .= " ".$rs_user_bloqueados_row['ug_id'];
        $qtde_user_bloqueados++; 
    } //end while
    if($qtde_user_bloqueados > 0) {
        echo "<span class='txt-vermelho'>Total de Usuários bloqueados: $qtde_user_bloqueados<br>IDs dos usuários bloqueados: $ids__user_bloqueados</span>";
    } //end if($qtde_user_bloqueados > 0)
    else {
        echo "Total de Usuários bloqueados: $qtde_user_bloqueados";
        if (!empty($ids__user_bloqueados)) {
            echo "<br>IDs dos usuários bloqueados: $ids_user_bloqueados";
        }
    }
?>
</div>