<div class="top10 lista bg-amarelo txt-azul">Monitor de PINs Cartões bloqueados(1 min)
<?php
//Recupera totais de usuários bloqueados
$qtde_pins_bloqueados = 0;
$ids__pins_bloqueados = "";
$sql  = "select pin_codinterno
                from pins_card 
                        inner join pins_card_db_historico_bloqueio ON (pin_codinterno = pcdhb_pin_codinterno)
                where pcdhb_pin_bloqueio=1 and
                        pin_bloqueio=1 
                group by pin_codinterno
                having max(pcdhb_data) < (NOW()- '1 minutes'::interval)
                order by pin_codinterno";
//if(b_IsUsuarioWagner()) { echo str_replace("\n", "<br>\n", $sql)."<br>"; }
$rs_pins_bloqueados = SQLexecuteQuery($sql);
while ($rs_pins_bloqueados_row = pg_fetch_array($rs_pins_bloqueados)) {
        if(!empty($ids__pins_bloqueados)) {
                $ids__pins_bloqueados .= ",";
        }//end if(!empty($ids__pins_bloqueados))
        $ids__pins_bloqueados .= " ".$rs_pins_bloqueados_row['pin_codinterno'];
        $qtde_pins_bloqueados++; 
} //end while
if($qtde_pins_bloqueados > 0) {
        echo "<br><span class='txt-vermelho'>Total de PINs bloqueados: $qtde_pins_bloqueados<br>IDs dos PINs bloqueados: $ids__pins_bloqueados</span>";
} //end if($qtde_pins_bloqueados > 0)
else {
        echo "<br>Total de PINs bloqueados: $qtde_pins_bloqueados";
        if (!empty($ids__pins_bloqueados)) {
                echo "<br>IDs dos PINs bloqueados: $ids_pins_bloqueados";
        }
}
?>
</div>