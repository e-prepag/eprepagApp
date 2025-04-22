<?php
set_time_limit ( 300 ) ;

require_once $raiz_do_projeto . "includes/sys/inc_sanitize_input.php";
require_once $raiz_do_projeto . "public_html/sys/admin/stats/inc_Comissoes.php";
require_once $raiz_do_projeto . "includes/gamer/constantes.php";

if(php_sapi_name()=="isapi") {
        ?>
        <SCRIPT language=JavaScript>
        <!--
                var bshow = true;
                function toggle_view() {
                        bshow = (bshow?false:true);
                        var DI=document.images; 
                        var dil=DI.length; 
                        for(i=0;i<dil;i++){
                                if(DI[i].src.indexOf("arrow")>=0) {
        //				DI[i].style.visibility = (bshow)?'visible':'hidden';
                                        DI[i].style.display = (bshow)?'block':'none';
                                }
                        }
                }
        -->
        </SCRIPT>
        <?php
}	

$search_msg_stats = LANG_SITE_SEARCH_MSG_1;
$search_unit_stats = LANG_SITE_SEARCH_MSG_2;

function get_current_date() {
        $date1 = date("Y M d H i s");
        $snow = date("d", $date1)."/".substr(mes_do_ano($date1),0,3)."/".date("Y", $date1)." ".date("h", $date1).":".date("m", $date1).":".date("s", $date1);
        $snow = date("Y/M/d H:i:s");
        return $snow;
}


function mes_do_ano($this_date){
        //'posicao = número relacionado a string de dados
        $meses = array("", LANG_JANUARY, LANG_FEBRUARY, LANG_MARCH, LANG_APRIL, LANG_MAY, LANG_JUNE, LANG_JULY, LANG_AUGUST, LANG_SEPTEMBER, LANG_OCTOBER, LANG_NOVEMBER, LANG_DECEMBER);
        return $meses[date("n", strtotime($this_date))]."/".date("y", strtotime($this_date));
}

function mes_do_ano2($this_date){
        //'posicao = número relacionado a string de dados
        $meses = array("", LANG_JANUARY, LANG_FEBRUARY, LANG_MARCH, LANG_APRIL, LANG_MAY, LANG_JUNE, LANG_JULY, LANG_AUGUST, LANG_SEPTEMBER, LANG_OCTOBER, LANG_NOVEMBER, LANG_DECEMBER);
        return $meses[date("n", $this_date)]."/".date("y", $this_date);
}

function mes_do_ano2_en($this_date){
        //'posicao = número relacionado a string de dados
        $meses = array("", "JANUARY", "FEBRUARY", "MARCH", "APRIL", "MAY", "JUNE", "JULY", "AUGUST", "SEPTEMBER", "OCTOBER", "NOVEMBER", "DECEMBER");
        return $meses[date("n", $this_date)]."/".date("y", $this_date);
}

// ==========================================================================
// os filtros de operadoras são definidos com $where_operadora, $where_operadora_pos, $where_operadora_cartoes
//	se $opr<>"" => retorna dados agrupados por operadora
function get_sql_total_mes($extra_where, $smode, $year, $b_opr, $where_origem,$mes,$periodo,$cartoes,$onlyeppcash = null) {
        global $where_operadora, $where_operadora_pos, $where_operadora_cartoes;
        global $COMISSAO_POS, $COMISSAO_LANS_MIN, $COMISSAO_LANS_CARTOES_MIN, $COMISSOES_BRUTAS, $OPR_CODIGOS, $COMISSOES_BRUTAS_BY_OPR_CODIGO,$PAGAMENTO_PIN_EPREPAG_NUMERIC;

        $where_mode_data = "vg.vg_data_inclusao";	// default
        if($smode=='S') $where_mode_data = "vg.vg_data_concilia";

        $sql = "select canal, mes, sum(n) as n, sum(vendas) as vendas, sum(total) as total from ( ";


//"P"
if (($onlyeppcash == 2)||(empty($onlyeppcash))) {
            if(!$where_origem) {	// $where_origem é definido apenas para Stardoll em M/E
                    // ver -'5 days'::interval no date_trunc 
                    //ex: date_trunc('$periodo', ve_data_inclusao)-'5 days'::interval as mes
                    //para semanas
                    $sql .= "\n(select 'P' as canal, ";
                    if($periodo=="week") {
                            //$sql .= "date_trunc('$periodo', ve_data_inclusao)-'5 days'::interval as mes,";
                            $currentmonth = mktime(0, 0, 0, date('n'), (date('j')-(date('N')-1))+2, date('Y'));
                            $firstmonth = mktime(0, 0, 0, 1, 1, 2008);
                            $sql .= "(case ";
                            while($currentmonth >=$firstmonth) {
                                    $sql .= "when (ve_data_inclusao>='".date("Y-m-d H:i:s",$currentmonth)."' AND ve_data_inclusao<='".date("Y-m-d H:i:s",mktime(23, 59, 59, date('n',$currentmonth), date('d',$currentmonth)+7, date('Y',$currentmonth)))."') then ('".date("Y-m-d H:i:s",$currentmonth)."') ";
                                    $currentmonth = mktime(0, 0, 0, date("m", $currentmonth), date('d',$currentmonth)-7, date("Y",$currentmonth));
                            }
                            $sql .= " end) as mes, "; 
                    }
                    else if ($periodo=="fortnightly"){
                            if (date('j')>15) {
                                    $currentmonth  = mktime(0, 0, 0, date('n'), 15, date('Y'));
                            }
                            else {
                                    $currentmonth  = mktime(0, 0, 0, date('n'), 1, date('Y'));
                            }
                            $firstmonth  = mktime(0, 0, 0, 1, 1, 2008);
                            $sql .= "(case ";
                            while($currentmonth >=$firstmonth) {
                                    if (date("d",$currentmonth) == 15){
                                            $sql .= "when (ve_data_inclusao>'".date("Y-m-d H:i:s",mktime(23, 59, 59, date('n',$currentmonth), 15, date('Y',$currentmonth)))."' AND ve_data_inclusao<'".date("Y-m-d H:i:s",mktime(0, 0, 0, date('n',$currentmonth)+1, 1, date('Y',$currentmonth)))."') then ('".date("Y-m-d H:i:s",$currentmonth)."') ";
                                            $currentmonth = mktime(0, 0, 0, date("m", $currentmonth), 1, date("Y",$currentmonth)); 
                                    }
                                    else {
                                            $sql .= "when (ve_data_inclusao>='".date("Y-m-d H:i:s",$currentmonth)."' AND ve_data_inclusao<'".date("Y-m-d H:i:s",mktime(0, 0, 0, date('n',$currentmonth), 16, date('Y',$currentmonth)))."') then ('".date("Y-m-d H:i:s",$currentmonth)."') ";
                                            $currentmonth = mktime(0, 0, 0, date("m", $currentmonth)-1, 15, date("Y",$currentmonth));
                                    }
                            }
                            $sql .= " end) as mes, "; 
                    }
                    else {
                            $sql .= "date_trunc('$periodo', ve_data_inclusao) as mes,";
                    }
                    $sql .= " count(*) as n, sum(ve_valor) as vendas, sum(ve_valor) as total from dist_vendas_pos where 1=1 ";
                    if(strlen($where_operadora_pos)>0)		$sql .= " and ".$where_operadora_pos;
                    if((strlen($extra_where)>0))			$sql .= " and ".$extra_where;
                    if(($year>0)&&($periodo!="week")) $sql.= " and extract(YEAR FROM ve_data_inclusao)=$year ";
                    if(strlen($mes)) {
                            if($periodo=="week") {
                                    //$sql.= " and extract($periodo FROM ve_data_inclusao)= extract(WEEK from date_trunc('week', NOW()-'7 days'::interval)) ";date_trunc('$periodo', ve_data_inclusao)-'5 days'::interval
                                    //$sql.= " and ve_data_inclusao >= date_trunc('week', NOW())-'5 days'::interval and ve_data_inclusao <= date_trunc('week', NOW())+'2 days'::interval ";
                                    $sql.= " and ve_data_inclusao >= date_trunc('week', NOW())-'15 days'::interval ";
                            }
                            else if ($periodo=="fortnightly"){
                                    /*
                                    if ($mes == "15") {
                                            $sql.= " and ve_data_inclusao >= '".date('Y')."-".str_pad(date('n')-1, 2, "0", STR_PAD_LEFT)."-$mes 00:00:00' and ve_data_inclusao <= '".date('Y')."-".date('m')."-01 00:00:00'";
                                    }
                                    else {
                                                    $sql.= " and ve_data_inclusao >= '".date('Y')."-".date('m')."-$mes 00:00:00' and ve_data_inclusao <= '".date('Y')."-".date('m')."-15 00:00:00'";
                                    }*/
                                    $sql.= " and ve_data_inclusao >= date_trunc('week', NOW())-'30 days'::interval ";
                            }
                            else {
                                    $sql.= " and extract($periodo FROM ve_data_inclusao)=$mes ";
                            }
                    }
                    else {
                            $sql .= " and ve_data_inclusao>'2008-01-01 00:00:00' ";
                    }
                    $sql .= "\n group by mes ) ";					
                    $sql .= "\n union all ";

            }
}//end if (($onlyeppcash == 2)||(empty($onlyeppcash))) 

//"P" "+" mais pagamento com PIN CASH que foram comprados em P (Rede POS)
if (($onlyeppcash == 1)||(empty($onlyeppcash))) {
            $sql .= "\n(select 'P' as canal,";
            if($periodo=="week") {
                    //$sql .= "date_trunc('$periodo', $where_mode_data)-'5 days'::interval as mes,";
                    $currentmonth = mktime(0, 0, 0, date('n'), (date('j')-(date('N')-1))+2, date('Y'));
                    $firstmonth = mktime(0, 0, 0, 1, 1, 2008);
                    $sql .= "(case ";
                    while($currentmonth >=$firstmonth) {
                            $sql .= "when ($where_mode_data>='".date("Y-m-d H:i:s",$currentmonth)."' AND $where_mode_data<='".date("Y-m-d H:i:s",mktime(23, 59, 59, date('n',$currentmonth), date('d',$currentmonth)+7, date('Y',$currentmonth)))."') then ('".date("Y-m-d H:i:s",$currentmonth)."') ";
                            $currentmonth = mktime(0, 0, 0, date("m", $currentmonth), date('d',$currentmonth)-7, date("Y",$currentmonth));
                    }
                    $sql .= " end) as mes, "; 
            }
            else if ($periodo=="fortnightly"){
                            if (date('j')>15) {
                                    $currentmonth  = mktime(0, 0, 0, date('n'), 15, date('Y'));
                            }
                            else {
                                    $currentmonth  = mktime(0, 0, 0, date('n'), 1, date('Y'));
                            }
                            $firstmonth  = mktime(0, 0, 0, 1, 1, 2008);
                            $sql .= "(case ";
                            while($currentmonth >=$firstmonth) {
                                    if (date("d",$currentmonth) == 15){
                                            $sql .= "when ($where_mode_data>'".date("Y-m-d H:i:s",mktime(23, 59, 59, date('n',$currentmonth), 15, date('Y',$currentmonth)))."' AND $where_mode_data<'".date("Y-m-d H:i:s",mktime(0, 0, 0, date('n',$currentmonth)+1, 1, date('Y',$currentmonth)))."') then ('".date("Y-m-d H:i:s",$currentmonth)."') ";
                                            $currentmonth = mktime(0, 0, 0, date("m", $currentmonth), 1, date("Y",$currentmonth)); 
                                    }
                                    else {
                                            $sql .= "when ($where_mode_data>='".date("Y-m-d H:i:s",$currentmonth)."' AND $where_mode_data<'".date("Y-m-d H:i:s",mktime(0, 0, 0, date('n',$currentmonth), 16, date('Y',$currentmonth)))."') then ('".date("Y-m-d H:i:s",$currentmonth)."') ";
                                            $currentmonth = mktime(0, 0, 0, date("m", $currentmonth)-1, 15, date("Y",$currentmonth));
                                    }
                            }
                            $sql .= " end) as mes, ";
            }
            else {
                    $sql .= "date_trunc('$periodo', $where_mode_data) as mes,";
            }
            $sql .= "sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas, sum(vgm.vgm_valor * vgm.vgm_qtde) as total \n";
            $sql .= "from tb_venda_games vg \ninner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id \n".
                            "inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id \n".
                            //"inner join tb_pag_compras tpc on tpc.idvenda = vg.vg_id \n".
                            " where vg.vg_ultimo_status='5' and SUBSTR(tvgpo.tvgpo_canal, 1, 1) ='P' ";//and tpc.valorpagtogocash =0 ";
            //echo $sql."<br>\n";

            if((strlen($where_operadora)>0))	$sql .= " and ".$where_operadora;
            if((strlen($extra_where)>0))		$sql .= " and ".$extra_where;
            if(($year>0)&&($periodo!="week")) $sql.= " and extract(YEAR FROM $where_mode_data)=$year ";
            if(strlen($mes)) {
                            if($periodo=="week") {
                                    //$sql.= " and extract($periodo FROM $where_mode_data)= extract(WEEK from date_trunc('week', NOW()-'7 days'::interval)) ";
                                    //$sql.= " and $where_mode_data >= date_trunc('week', NOW())-'5 days'::interval and $where_mode_data <= date_trunc('week', NOW())+'2 days'::interval ";
                                    $sql.= " and $where_mode_data >= date_trunc('week', NOW())-'15 days'::interval ";
                            }
                            else if ($periodo=="fortnightly"){
                                    /*
                                    if ($mes == "15") {
                                            $sql.= " and $where_mode_data >= '".date('Y')."-".str_pad(date('n')-1, 2, "0", STR_PAD_LEFT)."-$mes 00:00:00' and $where_mode_data <= '".date('Y')."-".date('m')."-01 00:00:00'";
                                    }
                                    else {
                                                    $sql.= " and $where_mode_data >= '".date('Y')."-".date('m')."-$mes 00:00:00' and $where_mode_data <= '".date('Y')."-".date('m')."-15 00:00:00'";
                                    }*/
                                    $sql.= " and $where_mode_data >= date_trunc('week', NOW())-'30 days'::interval ";
                            }
                            else {
                                    $sql.= " and extract($periodo FROM $where_mode_data)=$mes ";
                            }
            }
            else {
                    $sql .= " and $where_mode_data>'2008-01-01 00:00:00' \n";
            }
            if($where_origem) $sql.= " $where_origem ";
            $sql .= " and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC;
            $sql .= " group by mes, canal ) \n";
            $sql .= " union all \n";
}

//"M" - "E" "-" menos pagamento PIN CASH
if (($onlyeppcash == 2)||(empty($onlyeppcash))) {
            $sql .= "\n(select case when vg.vg_ug_id = '7909' then 'E' when vg.vg_ug_id != '7909' then 'M' end as canal,";
            if($periodo=="week") {
                    //$sql .= "date_trunc('$periodo', $where_mode_data)-'5 days'::interval as mes,";
                    $currentmonth = mktime(0, 0, 0, date('n'), (date('j')-(date('N')-1))+2, date('Y'));
                    $firstmonth = mktime(0, 0, 0, 1, 1, 2008);
                    $sql .= "(case ";
                    while($currentmonth >=$firstmonth) {
                            $sql .= "when ($where_mode_data>='".date("Y-m-d H:i:s",$currentmonth)."' AND $where_mode_data<='".date("Y-m-d H:i:s",mktime(23, 59, 59, date('n',$currentmonth), date('d',$currentmonth)+7, date('Y',$currentmonth)))."') then ('".date("Y-m-d H:i:s",$currentmonth)."') ";
                            $currentmonth = mktime(0, 0, 0, date("m", $currentmonth), date('d',$currentmonth)-7, date("Y",$currentmonth));
                    }
                    $sql .= " end) as mes, "; 
            }
            else if ($periodo=="fortnightly"){
                            if (date('j')>15) {
                                    $currentmonth  = mktime(0, 0, 0, date('n'), 15, date('Y'));
                            }
                            else {
                                    $currentmonth  = mktime(0, 0, 0, date('n'), 1, date('Y'));
                            }
                            $firstmonth  = mktime(0, 0, 0, 1, 1, 2008);
                            $sql .= "(case ";
                            while($currentmonth >=$firstmonth) {
                                    if (date("d",$currentmonth) == 15){
                                            $sql .= "when ($where_mode_data>'".date("Y-m-d H:i:s",mktime(23, 59, 59, date('n',$currentmonth), 15, date('Y',$currentmonth)))."' AND $where_mode_data<'".date("Y-m-d H:i:s",mktime(0, 0, 0, date('n',$currentmonth)+1, 1, date('Y',$currentmonth)))."') then ('".date("Y-m-d H:i:s",$currentmonth)."') ";
                                            $currentmonth = mktime(0, 0, 0, date("m", $currentmonth), 1, date("Y",$currentmonth)); 
                                    }
                                    else {
                                            $sql .= "when ($where_mode_data>='".date("Y-m-d H:i:s",$currentmonth)."' AND $where_mode_data<'".date("Y-m-d H:i:s",mktime(0, 0, 0, date('n',$currentmonth), 16, date('Y',$currentmonth)))."') then ('".date("Y-m-d H:i:s",$currentmonth)."') ";
                                            $currentmonth = mktime(0, 0, 0, date("m", $currentmonth)-1, 15, date("Y",$currentmonth));
                                    }
                            }
                            $sql .= " end) as mes, ";
            }
            else {
                    $sql .= "date_trunc('$periodo', $where_mode_data) as mes,";
            }
            $sql .= "sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas, sum(vgm.vgm_valor * vgm.vgm_qtde) as total \n";
            $sql .= "from tb_venda_games vg \ninner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id \n where vg.vg_ultimo_status='5' ";
            //echo $sql."<br>\n";

            if((strlen($where_operadora)>0))	$sql .= " and ".$where_operadora;
            if((strlen($extra_where)>0))		$sql .= " and ".$extra_where;
            if(($year>0)&&($periodo!="week")) $sql.= " and extract(YEAR FROM $where_mode_data)=$year ";
            if(strlen($mes)) {
                            if($periodo=="week") {
                                    //$sql.= " and extract($periodo FROM $where_mode_data)= extract(WEEK from date_trunc('week', NOW()-'7 days'::interval)) ";
                                    //$sql.= " and $where_mode_data >= date_trunc('week', NOW())-'5 days'::interval and $where_mode_data <= date_trunc('week', NOW())+'2 days'::interval ";
                                    $sql.= " and $where_mode_data >= date_trunc('week', NOW())-'15 days'::interval ";
                            }
                            else if ($periodo=="fortnightly"){
                                    /*
                                    if ($mes == "15") {
                                            $sql.= " and $where_mode_data >= '".date('Y')."-".str_pad(date('n')-1, 2, "0", STR_PAD_LEFT)."-$mes 00:00:00' and $where_mode_data <= '".date('Y')."-".date('m')."-01 00:00:00'";
                                    }
                                    else {
                                                    $sql.= " and $where_mode_data >= '".date('Y')."-".date('m')."-$mes 00:00:00' and $where_mode_data <= '".date('Y')."-".date('m')."-15 00:00:00'";
                                    }*/
                                    $sql.= " and $where_mode_data >= date_trunc('week', NOW())-'30 days'::interval ";
                            }
                            else {
                                    $sql.= " and extract($periodo FROM $where_mode_data)=$mes ";
                            }
            }
            else {
                    $sql .= " and $where_mode_data>'2008-01-01 00:00:00' \n";
            }
            if($where_origem) $sql.= " $where_origem ";
            $sql .= " and vg.vg_pagto_tipo != ".$PAGAMENTO_PIN_EPREPAG_NUMERIC;
            $sql .= " group by mes, canal ) \n";
            $sql .= " union all \n";
}// end if (($onlyeppcash == 2)||(empty($onlyeppcash))) 

//"M" - "E" "+" mais pagamento PIN CASH que foram comprados por GAMERS
if (($onlyeppcash == 1)||(empty($onlyeppcash))) {
            $sql .= "\n(select case when vg.vg_ug_id = '7909' then 'E' when vg.vg_ug_id != '7909' then 'M' end as canal,";
            if($periodo=="week") {
                    //$sql .= "date_trunc('$periodo', $where_mode_data)-'5 days'::interval as mes,";
                    $currentmonth = mktime(0, 0, 0, date('n'), (date('j')-(date('N')-1))+2, date('Y'));
                    $firstmonth = mktime(0, 0, 0, 1, 1, 2008);
                    $sql .= "(case ";
                    while($currentmonth >=$firstmonth) {
                            $sql .= "when ($where_mode_data>='".date("Y-m-d H:i:s",$currentmonth)."' AND $where_mode_data<='".date("Y-m-d H:i:s",mktime(23, 59, 59, date('n',$currentmonth), date('d',$currentmonth)+7, date('Y',$currentmonth)))."') then ('".date("Y-m-d H:i:s",$currentmonth)."') ";
                            $currentmonth = mktime(0, 0, 0, date("m", $currentmonth), date('d',$currentmonth)-7, date("Y",$currentmonth));
                    }
                    $sql .= " end) as mes, "; 
            }
            else if ($periodo=="fortnightly"){
                            if (date('j')>15) {
                                    $currentmonth  = mktime(0, 0, 0, date('n'), 15, date('Y'));
                            }
                            else {
                                    $currentmonth  = mktime(0, 0, 0, date('n'), 1, date('Y'));
                            }
                            $firstmonth  = mktime(0, 0, 0, 1, 1, 2008);
                            $sql .= "(case ";
                            while($currentmonth >=$firstmonth) {
                                    if (date("d",$currentmonth) == 15){
                                            $sql .= "when ($where_mode_data>'".date("Y-m-d H:i:s",mktime(23, 59, 59, date('n',$currentmonth), 15, date('Y',$currentmonth)))."' AND $where_mode_data<'".date("Y-m-d H:i:s",mktime(0, 0, 0, date('n',$currentmonth)+1, 1, date('Y',$currentmonth)))."') then ('".date("Y-m-d H:i:s",$currentmonth)."') ";
                                            $currentmonth = mktime(0, 0, 0, date("m", $currentmonth), 1, date("Y",$currentmonth)); 
                                    }
                                    else {
                                            $sql .= "when ($where_mode_data>='".date("Y-m-d H:i:s",$currentmonth)."' AND $where_mode_data<'".date("Y-m-d H:i:s",mktime(0, 0, 0, date('n',$currentmonth), 16, date('Y',$currentmonth)))."') then ('".date("Y-m-d H:i:s",$currentmonth)."') ";
                                            $currentmonth = mktime(0, 0, 0, date("m", $currentmonth)-1, 15, date("Y",$currentmonth));
                                    }
                            }
                            $sql .= " end) as mes, ";
            }
            else {
                    $sql .= "date_trunc('$periodo', $where_mode_data) as mes,";
            }
            $sql .= "sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas, sum(vgm.vgm_valor * vgm.vgm_qtde) as total \n";
            $sql .= "from tb_venda_games vg \ninner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id \n".
                            "inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id \n".
                            //"inner join tb_pag_compras tpc on tpc.idvenda = vg.vg_id \n".
                            "where vg.vg_ultimo_status='5' and tvgpo.tvgpo_canal='G' ";//and tpc.valorpagtogocash =0 ";
            //echo $sql."<br>\n";

            if((strlen($where_operadora)>0))	$sql .= " and ".$where_operadora;
            if((strlen($extra_where)>0))		$sql .= " and ".$extra_where;
            if(($year>0)&&($periodo!="week")) $sql.= " and extract(YEAR FROM $where_mode_data)=$year ";
            if(strlen($mes)) {
                            if($periodo=="week") {
                                    //$sql.= " and extract($periodo FROM $where_mode_data)= extract(WEEK from date_trunc('week', NOW()-'7 days'::interval)) ";
                                    //$sql.= " and $where_mode_data >= date_trunc('week', NOW())-'5 days'::interval and $where_mode_data <= date_trunc('week', NOW())+'2 days'::interval ";
                                    $sql.= " and $where_mode_data >= date_trunc('week', NOW())-'15 days'::interval ";
                            }
                            else if ($periodo=="fortnightly"){
                                    /*
                                    if ($mes == "15") {
                                            $sql.= " and $where_mode_data >= '".date('Y')."-".str_pad(date('n')-1, 2, "0", STR_PAD_LEFT)."-$mes 00:00:00' and $where_mode_data <= '".date('Y')."-".date('m')."-01 00:00:00'";
                                    }
                                    else {
                                                    $sql.= " and $where_mode_data >= '".date('Y')."-".date('m')."-$mes 00:00:00' and $where_mode_data <= '".date('Y')."-".date('m')."-15 00:00:00'";
                                    }*/
                                    $sql.= " and $where_mode_data >= date_trunc('week', NOW())-'30 days'::interval ";
                            }
                            else {
                                    $sql.= " and extract($periodo FROM $where_mode_data)=$mes ";
                            }
            }
            else {
                    $sql .= " and $where_mode_data>'2008-01-01 00:00:00' \n";
            }
            if($where_origem) $sql.= " $where_origem ";
            $sql .= " and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC;
            $sql .= " group by mes, canal ) \n";
            $sql .= " union all \n";			
}

//"L"
if (($onlyeppcash == 2)||(empty($onlyeppcash))) {

            if(!$where_origem) {	// $where_origem é definido apenas para Stardoll em M/E

                    $sql .= "(select 'L' as canal,";
                    if($periodo=="week") {
                            //$sql .= "date_trunc('$periodo', vg.vg_data_inclusao)-'5 days'::interval as mes,";
                            $currentmonth = mktime(0, 0, 0, date('n'), (date('j')-(date('N')-1))+2, date('Y'));
                            $firstmonth = mktime(0, 0, 0, 1, 1, 2008);
                            $sql .= "(case ";
                            while($currentmonth >=$firstmonth) {
                                    $sql .= "when (vg.vg_data_inclusao>='".date("Y-m-d H:i:s",$currentmonth)."' AND vg.vg_data_inclusao<='".date("Y-m-d H:i:s",mktime(23, 59, 59, date('n',$currentmonth), date('d',$currentmonth)+7, date('Y',$currentmonth)))."') then ('".date("Y-m-d H:i:s",$currentmonth)."') ";
                                    $currentmonth = mktime(0, 0, 0, date("m", $currentmonth), date('d',$currentmonth)-7, date("Y",$currentmonth));
                            }
                            $sql .= " end) as mes, "; 
                    }
                    else if ($periodo=="fortnightly"){
                                    if (date('j')>15) {
                                            $currentmonth  = mktime(0, 0, 0, date('n'), 15, date('Y'));
                                    }
                                    else {
                                            $currentmonth  = mktime(0, 0, 0, date('n'), 1, date('Y'));
                                    }
                                    $firstmonth  = mktime(0, 0, 0, 1, 1, 2008);
                                    $sql .= "(case ";
                                    while($currentmonth >=$firstmonth) {
                                            /*
                                            if (date("d",$currentmonth) == 15){
                                                    $sql .= "when (vg.vg_data_inclusao>='".date("Y-m-d H:i:s",$currentmonth)."' AND vg.vg_data_inclusao<='".date("Y-m-d H:i:s",mktime(23, 59, 59, date('n',$currentmonth)+1, 1, date('Y',$currentmonth)))."') then ('".date("Y-m-d H:i:s",$currentmonth)."') ";
                                                    $currentmonth = mktime(0, 0, 0, date("m", $currentmonth), 1, date("Y",$currentmonth)); 
                                            }
                                            else {
                                                    $sql .= "when (vg.vg_data_inclusao>='".date("Y-m-d H:i:s",$currentmonth)."' AND vg.vg_data_inclusao<='".date("Y-m-d H:i:s",mktime(23, 59, 59, date('n',$currentmonth), 15, date('Y',$currentmonth)))."') then ('".date("Y-m-d H:i:s",$currentmonth)."') ";
                                                    $currentmonth = mktime(0, 0, 0, date("m", $currentmonth)-1, 15, date("Y",$currentmonth));
                                            }
                                            if (date("d",$currentmonth) == 15){
                                                    $sql .= "when (vg.vg_data_inclusao<='".date("Y-m-d H:i:s",mktime(23, 59, 59, date('n',$currentmonth), 15, date('Y',$currentmonth)))."' AND vg.vg_data_inclusao>='".date("Y-m-d H:i:s",mktime(0, 0, 0, date('n',$currentmonth), 1, date('Y',$currentmonth)))."') then ('".date("Y-m-d H:i:s",$currentmonth)."') ";
                                                    $currentmonth = mktime(0, 0, 0, date("m", $currentmonth), 1, date("Y",$currentmonth)); 
                                            }
                                            else {
                                                    $sql .= "when (vg.vg_data_inclusao<'".date("Y-m-d H:i:s",$currentmonth)."' AND vg.vg_data_inclusao>='".date("Y-m-d H:i:s",mktime(0, 0, 0, date('n',$currentmonth)-1, 16, date('Y',$currentmonth)))."') then ('".date("Y-m-d H:i:s",$currentmonth)."') ";
                                                    $currentmonth = mktime(0, 0, 0, date("m", $currentmonth)-1, 15, date("Y",$currentmonth));
                                            }*/
                                            if (date("d",$currentmonth) == 15){
                                                    $sql .= "when (vg.vg_data_inclusao>'".date("Y-m-d H:i:s",mktime(23, 59, 59, date('n',$currentmonth), 15, date('Y',$currentmonth)))."' AND vg.vg_data_inclusao<'".date("Y-m-d H:i:s",mktime(0, 0, 0, date('n',$currentmonth)+1, 1, date('Y',$currentmonth)))."') then ('".date("Y-m-d H:i:s",$currentmonth)."') ";
                                                    $currentmonth = mktime(0, 0, 0, date("m", $currentmonth), 1, date("Y",$currentmonth)); 
                                            }
                                            else {
                                                    $sql .= "when (vg.vg_data_inclusao>='".date("Y-m-d H:i:s",$currentmonth)."' AND vg.vg_data_inclusao<'".date("Y-m-d H:i:s",mktime(0, 0, 0, date('n',$currentmonth), 16, date('Y',$currentmonth)))."') then ('".date("Y-m-d H:i:s",$currentmonth)."') ";
                                                    $currentmonth = mktime(0, 0, 0, date("m", $currentmonth)-1, 15, date("Y",$currentmonth));
                                            }
                                    }
                                    $sql .= " end) as mes, ";
                    }
                    else {
                            $sql .= "date_trunc('$periodo', vg.vg_data_inclusao) as mes,";
                    }
                    $sql .= "sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas, sum(vgm.vgm_valor * vgm.vgm_qtde) as total from tb_dist_venda_games vg inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id where vg.vg_ultimo_status='5' ";
                    //if((strlen($where_operadora)>0))		$sql .= " and ".$where_operadora;
                    if((strlen($where_operadora)>0)) {
                        //verificando se existe o campo contido somente na tabela tb_venda_games
                        $tamanho_corte = strpos($where_operadora,'and vg_integracao_parceiro_origem_id');
                        if($tamanho_corte == 0) {
                            $tamanho_corte = strpos($where_operadora,'vg_integracao_parceiro_origem_id');
                        }
                        //capturando a where inicial
                        $auxiliar = $where_operadora;
                        //excluindo o campo contido somente na tabela tb_venda_games
                        if($tamanho_corte > 0) {
                            $auxiliar = substr($auxiliar, 0 , strpos($where_operadora,'and vg_integracao_parceiro_origem_id'));
                            $auxiliar = substr($auxiliar, 0 , strpos($where_operadora,'vg_integracao_parceiro_origem_id'));
                        }
                        //concatenando com o SQL
                        if((strlen($auxiliar)>0)) {
                            $sql .= " and ".$auxiliar;
                        }
                    }//end if((strlen($where_operadora)>0))
                    if((strlen($extra_where)>0))			$sql .= " and ".$extra_where;
                    if(($year>0)&&($periodo!="week")) $sql.= " and extract(YEAR FROM vg_data_inclusao)=$year ";
                    if(strlen($mes)) {
                            if($periodo=="week") {
                                    //$sql.= " and extract($periodo FROM vg_data_inclusao)= extract(WEEK from date_trunc('week', NOW()-'7 days'::interval)) ";
                                    //$sql.= " and vg_data_inclusao >= date_trunc('week', NOW())-'5 days'::interval and vg_data_inclusao <= date_trunc('week', NOW())+'2 days'::interval ";
                                    $sql.= " and vg_data_inclusao >= date_trunc('week', NOW())-'15 days'::interval ";
                            }
                            else if ($periodo=="fortnightly"){
                                    /*
                                    if ($mes == "15") {
                                            $sql.= " and vg_data_inclusao >= '".date('Y')."-".str_pad(date('n')-1, 2, "0", STR_PAD_LEFT)."-$mes 00:00:00' and vg_data_inclusao <= '".date('Y')."-".date('m')."-01 00:00:00'";
                                    }
                                    else {
                                                    $sql.= " and vg_data_inclusao >= '".date('Y')."-".date('m')."-$mes 00:00:00' and vg_data_inclusao <= '".date('Y')."-".date('m')."-15 00:00:00'";
                                    }*/
                                    $sql.= " and vg_data_inclusao >= date_trunc('week', NOW())-'30 days'::interval ";
                            }
                            else {
                                    $sql.= " and extract($periodo FROM vg_data_inclusao)=$mes ";
                            }
                    }
                    else {
                            $sql .= " and vg.vg_data_inclusao>'2008-01-01 00:00:00' \n";
                    }
                    $sql .= " group by mes ) \n";
                    $sql .= " union all \n";
            }
}//end if (($onlyeppcash == 2)||(empty($onlyeppcash))) 

//"L" "+" mais pagamento com PIN CASH que foram comprados em LANs
if (($onlyeppcash == 1)||(empty($onlyeppcash))) {
            $sql .= "\n(select 'L' as canal,";
            if($periodo=="week") {
                    //$sql .= "date_trunc('$periodo', $where_mode_data)-'5 days'::interval as mes,";
                    $currentmonth = mktime(0, 0, 0, date('n'), (date('j')-(date('N')-1))+2, date('Y'));
                    $firstmonth = mktime(0, 0, 0, 1, 1, 2008);
                    $sql .= "(case ";
                    while($currentmonth >=$firstmonth) {
                            $sql .= "when ($where_mode_data>='".date("Y-m-d H:i:s",$currentmonth)."' AND $where_mode_data<='".date("Y-m-d H:i:s",mktime(23, 59, 59, date('n',$currentmonth), date('d',$currentmonth)+7, date('Y',$currentmonth)))."') then ('".date("Y-m-d H:i:s",$currentmonth)."') ";
                            $currentmonth = mktime(0, 0, 0, date("m", $currentmonth), date('d',$currentmonth)-7, date("Y",$currentmonth));
                    }
                    $sql .= " end) as mes, "; 
            }
            else if ($periodo=="fortnightly"){
                            if (date('j')>15) {
                                    $currentmonth  = mktime(0, 0, 0, date('n'), 15, date('Y'));
                            }
                            else {
                                    $currentmonth  = mktime(0, 0, 0, date('n'), 1, date('Y'));
                            }
                            $firstmonth  = mktime(0, 0, 0, 1, 1, 2008);
                            $sql .= "(case ";
                            while($currentmonth >=$firstmonth) {
                                    if (date("d",$currentmonth) == 15){
                                            $sql .= "when ($where_mode_data>'".date("Y-m-d H:i:s",mktime(23, 59, 59, date('n',$currentmonth), 15, date('Y',$currentmonth)))."' AND $where_mode_data<'".date("Y-m-d H:i:s",mktime(0, 0, 0, date('n',$currentmonth)+1, 1, date('Y',$currentmonth)))."') then ('".date("Y-m-d H:i:s",$currentmonth)."') ";
                                            $currentmonth = mktime(0, 0, 0, date("m", $currentmonth), 1, date("Y",$currentmonth)); 
                                    }
                                    else {
                                            $sql .= "when ($where_mode_data>='".date("Y-m-d H:i:s",$currentmonth)."' AND $where_mode_data<'".date("Y-m-d H:i:s",mktime(0, 0, 0, date('n',$currentmonth), 16, date('Y',$currentmonth)))."') then ('".date("Y-m-d H:i:s",$currentmonth)."') ";
                                            $currentmonth = mktime(0, 0, 0, date("m", $currentmonth)-1, 15, date("Y",$currentmonth));
                                    }
                            }
                            $sql .= " end) as mes, ";
            }
            else {
                    $sql .= "date_trunc('$periodo', $where_mode_data) as mes,";
            }
            $sql .= "sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas, sum(vgm.vgm_valor * vgm.vgm_qtde) as total \n";
            $sql .= "from tb_venda_games vg \ninner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id \n".
                            "inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id \n".
                            //"inner join tb_pag_compras tpc on tpc.idvenda = vg.vg_id \n".
                            " where vg.vg_ultimo_status='5' and tvgpo.tvgpo_canal='L' ";//and tpc.valorpagtogocash =0 ";
            //echo $sql."<br>\n";

            if((strlen($where_operadora)>0))	$sql .= " and ".$where_operadora;
            if((strlen($extra_where)>0))		$sql .= " and ".$extra_where;
            if(($year>0)&&($periodo!="week")) $sql.= " and extract(YEAR FROM $where_mode_data)=$year ";
            if(strlen($mes)) {
                            if($periodo=="week") {
                                    //$sql.= " and extract($periodo FROM $where_mode_data)= extract(WEEK from date_trunc('week', NOW()-'7 days'::interval)) ";
                                    //$sql.= " and $where_mode_data >= date_trunc('week', NOW())-'5 days'::interval and $where_mode_data <= date_trunc('week', NOW())+'2 days'::interval ";
                                    $sql.= " and $where_mode_data >= date_trunc('week', NOW())-'15 days'::interval ";
                            }
                            else if ($periodo=="fortnightly"){
                                    /*
                                    if ($mes == "15") {
                                            $sql.= " and $where_mode_data >= '".date('Y')."-".str_pad(date('n')-1, 2, "0", STR_PAD_LEFT)."-$mes 00:00:00' and $where_mode_data <= '".date('Y')."-".date('m')."-01 00:00:00'";
                                    }
                                    else {
                                                    $sql.= " and $where_mode_data >= '".date('Y')."-".date('m')."-$mes 00:00:00' and $where_mode_data <= '".date('Y')."-".date('m')."-15 00:00:00'";
                                    }*/
                                    $sql.= " and $where_mode_data >= date_trunc('week', NOW())-'30 days'::interval ";
                            }
                            else {
                                    $sql.= " and extract($periodo FROM $where_mode_data)=$mes ";
                            }
            }
            else {
                    $sql .= " and $where_mode_data>'2008-01-01 00:00:00' \n";
            }
            if($where_origem) $sql.= " $where_origem ";
            $sql .= " and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC;
            $sql .= " group by mes, canal ) \n";
            $sql .= " union all \n";
}


//"C"	
if (($onlyeppcash == 2)||(empty($onlyeppcash))) {

            if ($cartoes) {
                    $sqlwherecartaodatainit = "and vc_data>'2008-01-01 00:00:00'";
                    $sql .= "(select 'C' as canal, ";
                    if($periodo=="week") {
                            //$sql .= "date_trunc('$periodo', vc_data)-'5 days'::interval as mes,";
                            $currentmonth = mktime(0, 0, 0, date('n'), (date('j')-(date('N')-1))+2, date('Y'));
                            $firstmonth = mktime(0, 0, 0, 1, 1, 2008);
                            $sql .= "(case ";
                            while($currentmonth >=$firstmonth) {
                                    $sql .= "when (vc_data>='".date("Y-m-d H:i:s",$currentmonth)."' AND vc_data<='".date("Y-m-d H:i:s",mktime(23, 59, 59, date('n',$currentmonth), date('d',$currentmonth)+7, date('Y',$currentmonth)))."') then ('".date("Y-m-d H:i:s",$currentmonth)."') ";
                                    $currentmonth = mktime(0, 0, 0, date("m", $currentmonth), date('d',$currentmonth)-7, date("Y",$currentmonth));
                            }
                            $sql .= " end) as mes, "; 
                    }
                    else if ($periodo=="fortnightly"){
                                            if (date('j')>15) {
                                                    $currentmonth  = mktime(0, 0, 0, date('n'), 15, date('Y'));
                                            }
                                            else {
                                                    $currentmonth  = mktime(0, 0, 0, date('n'), 1, date('Y'));
                                            }
                                            $firstmonth  = mktime(0, 0, 0, 1, 1, 2008);
                                            $sql .= "(case ";
                                            while($currentmonth >=$firstmonth) {
                                                    if (date("d",$currentmonth) == 15){
                                                            $sql .= "when (vc_data>='".date("Y-m-d H:i:s",mktime(0, 0, 0, date('n',$currentmonth), 1, date('Y',$currentmonth)))."' AND vc_data<'".date("Y-m-d H:i:s",mktime(0, 0, 0, date('n',$currentmonth)+1, 1, date('Y',$currentmonth)))."') then ('".date("Y-m-d H:i:s",$currentmonth)."') ";
                                                            $currentmonth = mktime(0, 0, 0, date("m", $currentmonth), 1, date("Y",$currentmonth)); 
                                                    }
                                                    else {
                                                            $sql .= "when (vc_data>='".date("Y-m-d H:i:s",$currentmonth)."' AND vc_data<'".date("Y-m-d H:i:s",mktime(0, 0, 0, date('n',$currentmonth)+1, 1, date('Y',$currentmonth)))."') then ('".date("Y-m-d H:i:s",$currentmonth)."') ";
                                                            $currentmonth = mktime(0, 0, 0, date("m", $currentmonth)-1, 15, date("Y",$currentmonth));
                                                    }
                                            }
                                            $sql .= " end) as mes, ";
                    }
                    else {
                            $sql .= "date_trunc('$periodo', vc_data) as mes, ";
                    }
                    $sql .= "sum(n) as n, sum(vendas1) as vendas, sum(vendas1) as total\n
                    from (
                            select vc_data, vc_total_mu_online as n, vc_total_mu_online*10 as vendas1, (".$COMISSOES_BRUTAS['C']['MU ONLINE'].".-vc_comissao) as comissao, '10' as valor, 'MU' as ve_jogo  
                            from dist_vendas_cartoes_tmp 
                            where vc_total_mu_online>0  ".$sqlwherecartaodatainit."
                            union all
                            select vc_data, vc_total_5k as n, vc_total_5k*13 as vendas1, (".$COMISSOES_BRUTAS['C']['ONGAME'].".-vc_comissao) as comissao, '13' as valor, 'OG' as ve_jogo
                            from dist_vendas_cartoes_tmp 
                            where vc_total_5k>0 ".$sqlwherecartaodatainit."  
                            union all
                            select vc_data, vc_total_10k as n, vc_total_10k*25 as vendas1, (".$COMISSOES_BRUTAS['C']['ONGAME'].".-vc_comissao) as comissao, '25' as valor, 'OG' as ve_jogo
                            from dist_vendas_cartoes_tmp 
                            where vc_total_10k>0 ".$sqlwherecartaodatainit."
                            union all
                            select vc_data, vc_total_15k as n, vc_total_15k*37 as vendas1, (".$COMISSOES_BRUTAS['C']['ONGAME'].".-vc_comissao) as comissao, '37' as valor, 'OG' as ve_jogo
                            from dist_vendas_cartoes_tmp 
                            where vc_total_15k>0 ".$sqlwherecartaodatainit."
                            union all
                            select vc_data, vc_total_20k as n, vc_total_20k*49 as vendas1, (".$COMISSOES_BRUTAS['C']['ONGAME'].".-vc_comissao) as comissao, '49' as valor, 'OG' as ve_jogo
                            from dist_vendas_cartoes_tmp 
                            where vc_total_20k>0 ".$sqlwherecartaodatainit."
                    ) v1 \n\n where 1=1 ";
                    if(strlen($where_operadora_cartoes)>0) $sql.= " and ".$where_operadora_cartoes;
                    if((strlen($extra_where)>0)) $sql.= " and ".$extra_where;
                    if(($year>0)&&($periodo!="week")) $sql.= "  and extract(YEAR FROM vc_data)= $year ";
                    if(strlen($mes)) {
                            if($periodo=="week") {
                                    //$sql.= " and extract($periodo FROM vc_data)= extract(WEEK from date_trunc('week', NOW()-'7 days'::interval)) ";
                                    //$sql.= " and vc_data >= date_trunc('week', NOW())-'5 days'::interval and vc_data <= date_trunc('week', NOW())+'2 days'::interval ";
                                    $sql.= " and vc_data >= date_trunc('week', NOW())-'15 days'::interval ";
                            }
                            else if ($periodo=="fortnightly"){
                                            /*
                                            if ($mes == "15") {
                                                    $sql.= " and vc_data >= '".date('Y')."-".str_pad(date('n')-1, 2, "0", STR_PAD_LEFT)."-$mes 00:00:00' and vc_data <= '".date('Y')."-".date('m')."-01 00:00:00'";
                                            }
                                            else {
                                                            $sql.= " and vc_data >= '".date('Y')."-".date('m')."-$mes 00:00:00' and vc_data <= '".date('Y')."-".date('m')."-15 00:00:00'";
                                            }*/
                                            $sql.= " and vc_data >= date_trunc('week', NOW())-'30 days'::interval ";
                                    }
                                    else {
                                            $sql.= " and extract($periodo FROM vc_data)=$mes ";
                                    }
                    }
                    $sql .= "\n group by mes ) \n";
                    $sql .= " union all \n";

                    //"C" "+" mais pagamento PIN CASH que foram comprados por GAMERS
                    if (($onlyeppcash == 1)||(empty($onlyeppcash))) {
                                    $sql .= "\n(select 'C' as canal,";
                                    if($periodo=="week") {
                                            //$sql .= "date_trunc('$periodo', $where_mode_data)-'5 days'::interval as mes,";
                                            $currentmonth = mktime(0, 0, 0, date('n'), (date('j')-(date('N')-1))+2, date('Y'));
                                            $firstmonth = mktime(0, 0, 0, 1, 1, 2008);
                                            $sql .= "(case ";
                                            while($currentmonth >=$firstmonth) {
                                                    $sql .= "when ($where_mode_data>='".date("Y-m-d H:i:s",$currentmonth)."' AND $where_mode_data<='".date("Y-m-d H:i:s",mktime(23, 59, 59, date('n',$currentmonth), date('d',$currentmonth)+7, date('Y',$currentmonth)))."') then ('".date("Y-m-d H:i:s",$currentmonth)."') ";
                                                    $currentmonth = mktime(0, 0, 0, date("m", $currentmonth), date('d',$currentmonth)-7, date("Y",$currentmonth));
                                            }
                                            $sql .= " end) as mes, "; 
                                    }
                                    else if ($periodo=="fortnightly"){
                                                    if (date('j')>15) {
                                                            $currentmonth  = mktime(0, 0, 0, date('n'), 15, date('Y'));
                                                    }
                                                    else {
                                                            $currentmonth  = mktime(0, 0, 0, date('n'), 1, date('Y'));
                                                    }
                                                    $firstmonth  = mktime(0, 0, 0, 1, 1, 2008);
                                                    $sql .= "(case ";
                                                    while($currentmonth >=$firstmonth) {
                                                            if (date("d",$currentmonth) == 15){
                                                                    $sql .= "when ($where_mode_data>'".date("Y-m-d H:i:s",mktime(23, 59, 59, date('n',$currentmonth), 15, date('Y',$currentmonth)))."' AND $where_mode_data<'".date("Y-m-d H:i:s",mktime(0, 0, 0, date('n',$currentmonth)+1, 1, date('Y',$currentmonth)))."') then ('".date("Y-m-d H:i:s",$currentmonth)."') ";
                                                                    $currentmonth = mktime(0, 0, 0, date("m", $currentmonth), 1, date("Y",$currentmonth)); 
                                                            }
                                                            else {
                                                                    $sql .= "when ($where_mode_data>='".date("Y-m-d H:i:s",$currentmonth)."' AND $where_mode_data<'".date("Y-m-d H:i:s",mktime(0, 0, 0, date('n',$currentmonth), 16, date('Y',$currentmonth)))."') then ('".date("Y-m-d H:i:s",$currentmonth)."') ";
                                                                    $currentmonth = mktime(0, 0, 0, date("m", $currentmonth)-1, 15, date("Y",$currentmonth));
                                                            }
                                                    }
                                                    $sql .= " end) as mes, ";
                                    }
                                    else {
                                            $sql .= "date_trunc('$periodo', $where_mode_data) as mes,";
                                    }
                                    $sql .= "sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas, sum(vgm.vgm_valor * vgm.vgm_qtde) as total \n";
                                    $sql .= "from tb_venda_games vg \ninner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id \n".
                                                    "inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id \n".
                                                    //"inner join tb_pag_compras tpc on tpc.idvenda = vg.vg_id \n".
                                                    "where vg.vg_ultimo_status='5' and tvgpo.tvgpo_canal='C' ";//and tpc.valorpagtogocash =0 ";
                                    //echo $sql."<br>\n";

                                    if((strlen($where_operadora)>0))	$sql .= " and ".$where_operadora;
                                    if((strlen($extra_where)>0))		$sql .= " and ".$extra_where;
                                    if(($year>0)&&($periodo!="week")) $sql.= " and extract(YEAR FROM $where_mode_data)=$year ";
                                    if(strlen($mes)) {
                                                    if($periodo=="week") {
                                                            //$sql.= " and extract($periodo FROM $where_mode_data)= extract(WEEK from date_trunc('week', NOW()-'7 days'::interval)) ";
                                                            //$sql.= " and $where_mode_data >= date_trunc('week', NOW())-'5 days'::interval and $where_mode_data <= date_trunc('week', NOW())+'2 days'::interval ";
                                                            $sql.= " and $where_mode_data >= date_trunc('week', NOW())-'15 days'::interval ";
                                                    }
                                                    else if ($periodo=="fortnightly"){
                                                            /*
                                                            if ($mes == "15") {
                                                                    $sql.= " and $where_mode_data >= '".date('Y')."-".str_pad(date('n')-1, 2, "0", STR_PAD_LEFT)."-$mes 00:00:00' and $where_mode_data <= '".date('Y')."-".date('m')."-01 00:00:00'";
                                                            }
                                                            else {
                                                                            $sql.= " and $where_mode_data >= '".date('Y')."-".date('m')."-$mes 00:00:00' and $where_mode_data <= '".date('Y')."-".date('m')."-15 00:00:00'";
                                                            }*/
                                                            $sql.= " and $where_mode_data >= date_trunc('week', NOW())-'30 days'::interval ";
                                                    }
                                                    else {
                                                            $sql.= " and extract($periodo FROM $where_mode_data)=$mes ";
                                                    }
                                    }
                                    else {
                                            $sql .= " and $where_mode_data>'2008-01-01 00:00:00' \n";
                                    }
                                    if($where_origem) $sql.= " $where_origem ";
                                    $sql .= " and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC;
                                    $sql .= " group by mes, canal ) \n";
                                    $sql .= " union all \n";			
                    }//end if (($onlyeppcash == 1)||(empty($onlyeppcash))) para cartões
     } // end if ($cartoes) 

}//end if (($onlyeppcash == 2)||(empty($onlyeppcash))) 

 
$sql = substr($sql,0,strlen($sql)-12);

        $sql .= ") t group by canal, mes order by mes desc, canal \n";

//if($periodo=="fortnightly") {
//	echo "sql: ".str_replace("\n","<br>\n",$sql)."<br>";
//	die("AKI");
//}

        return $sql;
}

function get_comiss_canal_opr_DB($canal, $opr_codigo, $data) {
        //	-- Function: obtem_comissao_publisher(bigint, text, timestamp without time zone)
        //	select obtem_comissao_publisher($opr_codigo, 'P'::text, CURRENT_TIMESTAMP::timestamp without time zone)
        $sql = "select obtem_comissao_publisher($opr_codigo::bigint, '$canal'::text, '$data') as comiss_db";
        $rescomiss = SQLexecuteQuery($sql);
        if($rescomiss) {
                if($pgcomiss = pg_fetch_array ($rescomiss)) { 
                        $comiss_db	= $pgcomiss['comiss_db'];
                } else {
                        $comiss_db	= -1;
                }
        } else {
                echo "ERROR: No data for comiss_db<br>\n";
        }
        return $comiss_db;
}

function get_comiss_canal_opr_CONST($canal, $opr_nome) {
        $comiss_const = $GLOBALS['COMISSOES_BRUTAS'][$canal][$opr_nome];
        if(!$comiss_const) $comiss_const = "0";
        return $comiss_const;
}


$iwidth = 100;
$iheight = 8;
$v_start = strtotime ($inic_oper_data);
$today1 = strtotime ('now');
$itotal = intval(($today1-$v_start)/86400+1);
?>