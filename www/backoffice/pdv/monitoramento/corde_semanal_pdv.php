<?php
require_once $raiz_do_projeto."includes/pdv/corte_constantes.php";
//Recupera qtde de usuarios ativos com corte por dia da semana
$qtde_corte_dia_da_semana = 0;
$s_corte_dia_da_semana = "";
//	$sql_corte_dia_da_semana  = "select count(*) as qtde from dist_usuarios_games where ug_ativo = 2 and ug_qtde_acessos = 0";
$sql_corte_dia_da_semana  = "select ug_perfil_corte_dia_semana, count(*) as qtde from dist_usuarios_games where ug_ativo = 1 group by ug_perfil_corte_dia_semana order by ug_perfil_corte_dia_semana";
$rs_corte_dia_da_semana = SQLexecuteQuery($sql_corte_dia_da_semana);
if($rs_corte_dia_da_semana && pg_num_rows($rs_corte_dia_da_semana) > 0){
        $s_corte_dia_da_semana = "<table class='table table-bordered top10' title='Número de Lans ativas com Corte em cada dia da semana'>\n";
        $scol1 = "#C8C9E1";
        $scol2 = "#B6C8DC";
        $scol = $scol1;

        $s_corte_dia_da_semana .= "<tr><th class='text-right'>Dia da semana&nbsp</th><th class='text-center'>N lans</th></tr>\n";
        while($rs_corte_dia_da_semana_row = pg_fetch_array($rs_corte_dia_da_semana)) {
//			$qtde_corte_dia_da_semana = $rs_corte_dia_da_semana_row['qtde'];
                $s_corte_dia_da_semana .= "<tr><td align='right'>".$CORTE_DIAS_DA_SEMANA_DESCRICAO["".$rs_corte_dia_da_semana_row['ug_perfil_corte_dia_semana'].""]."</td><td align='center'>".$rs_corte_dia_da_semana_row['qtde']."</td></tr>\n";
                $scol = ($scol==$scol1)?$scol2:$scol1;
        }
        $s_corte_dia_da_semana .= "</table>\n";
} else {
        $s_corte_dia_da_semana = "<b>Erro ao procurar dia de corte de lans</b>";
}

echo $s_corte_dia_da_semana;