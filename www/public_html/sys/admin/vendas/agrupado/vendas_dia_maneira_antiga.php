<?php 
set_time_limit( 300 );

require_once "../../../../../includes/constantes.php";
require_once $raiz_do_projeto . "public_html/sys/includes/topo_sys.php";
require_once $raiz_do_projeto . "includes/gamer/constantes.php";
$pos_pagina = $seg_auxilar;

$time_start = getmicrotime();

$bdebug = false;

if ($_SERVER['HTTP_REFERER'] != "https://www.e-prepag.com.br/sys/admin/vendas/agrupado/vendas_dia_maneira_antiga.php") {
        $dd_exclui_epp_cash = 1;
}

$where_operadora = "";
if ($dd_exclui_epp_cash==1 && ($_SESSION["tipo_acesso_pub"]=='AT')) {
        $where_operadora = " and vgm_opr_codigo!=".$dd_operadora_EPP_Cash." and  vgm_opr_codigo!=".$dd_operadora_EPP_Cash_LH ." ";
}

if($_SESSION["tipo_acesso_pub"]=='PU') {
        $dd_operadora = $_SESSION["opr_codigo_pub"];
        $dd_mode = "S";
        $Submit = "Buscar";
} else {
        $bdebug = true;
}

if(!$dd_mode || ($dd_mode!='V')) {
        $dd_mode = "S";
}

if(!$ncamp)    $ncamp       = 'trn_data';
if(!$dd_ano)   $dd_ano      = date('Y');
if(!$dd_mes)   $dd_mes      = date('m');
if(!$ordem)    $ordem       = 0;
if($BtnSearch && $BtnSearch!=1 ) {
        $total_table = 0;
}

$where_opr_1 = "";
$where_opr_2 = "";
$where_opr_3 = "";
$where_ano_1 = "";
$where_ano_2 = "";
$where_ano_3a = "";
$where_ano_3b = "";
$where_ano_ponto_certo = "";
$where_mes_1 = "";
$where_mes_2 = "";
$where_mes_3a = "";
$where_mes_3b = "";
$where_mes_ponto_certo = "";
$where_canal_1a = "";
$where_canal_1b = "";
$where_canal_2 = "";
$where_canal_c = "";
$where_opr_cartao = "";
$where_opr_gocash = "";
$where_operadora_rede_ponto_certo = "";

if($dd_canal) {
        if($dd_canal=="Site") {
                $where_canal_1a = "  ";
                $where_canal_1b = " and vg_ultimo_status_obs not like '%AtimoPay%' and vg_http_referer_origem <> 'ATIMO' ";
                $where_canal_2 = " and (FALSE) ";
                $where_canal_c = " and (FALSE) ";
        }
        if($dd_canal=="SiteLH") {
                $where_canal_1a = "  ";
                $where_canal_1b = " and (FALSE) ";
                $where_canal_2 = " and (FALSE) ";
                $where_canal_c = " and (FALSE) ";
        }
        if($dd_canal=="SiteGamer") {
                $where_canal_1a = " and (FALSE) ";
                $where_canal_1b = " and vg_ultimo_status_obs not like '%AtimoPay%' and vg_http_referer_origem <> 'ATIMO' ";
                $where_canal_2 = " and (FALSE) ";
                $where_canal_c = " and (FALSE) ";
        }
        if($dd_canal=="POS") {
                $where_canal_1a = " and (FALSE) ";
                $where_canal_1b = " and (FALSE) ";
                $where_canal_2 = " ";
                $where_canal_c = " and (FALSE) ";
        }
        if($dd_canal=="Cartao") {
                $where_canal_1a = " and (FALSE) ";
                $where_canal_1b = " and (FALSE) ";
                $where_canal_2 = " and (FALSE)  ";
                $where_canal_c = " ";
        }
		if($dd_canal=="ATIMO") {
                $where_canal_1a = " and (FALSE) ";
                $where_canal_1b = " and true and vg_ultimo_status_obs like '%AtimoPay%' and vg_http_referer_origem = 'ATIMO' ";
                $where_canal_2 = " and (FALSE) ";
                $where_canal_c = " and (FALSE) ";
        }
}

if($dd_operadora) {
        $where_opr_cartao = " and (pih_id = ".$dd_operadora.")";
        $where_opr_gocash = " and (pgc_opr_codigo = ".$dd_operadora.") ";
        $where_opr_1 = " and (t0.opr_codigo = ".$dd_operadora.") ";

        if($dd_operadora==13)	//($dd_operadora_nome=='ONGAME') 
                $where_opr_2 = " and (ve_jogo = 'OG') ";
        elseif  ($dd_operadora==17)	//($dd_operadora_nome=='MU ONLINE') 
                $where_opr_2 = " and (ve_jogo = 'MU') ";
        elseif  ($dd_operadora==16)	//($dd_operadora_nome=='HABBO HOTEL') 
                $where_opr_2 = " and (ve_jogo = 'HB') ";
        else
                $where_opr_2 = " and (ve_jogo = 'xx') ";

        $where_opr_3 = " and (vgm.vgm_opr_codigo= ".$dd_operadora.") ";
        
        $where_operadora_rede_ponto_certo = " and opr_codigo = ".$dd_operadora." ";

}

if($dd_ano) {
        $where_ano_1 = " and (extract (year from trn_data) = ".$dd_ano.") ";
        $where_ano_2 = " and (extract (year from ve_data_inclusao) = ".$dd_ano.") ";
        $where_ano_3a = " and (round(CAST (extract (year from vg.vg_data_inclusao::date) as int),0) =".$dd_ano.") ";
        $where_ano_3b = " and (round(CAST (extract (year from vg.vg_data_concilia::date) as int),0) =".$dd_ano.") ";
        $where_ano_3c = " and (round(CAST (extract (year from pih_data::date) as int),0) =".$dd_ano.") ";
        $where_ano_3d = " and (round(CAST (extract (year from pgc_pin_response_date::date) as int),0) =".$dd_ano.") ";
        $where_ano_ponto_certo = " and (round(CAST (extract (year from data_transacao::date) as int),0) =".$dd_ano.") ";
}

if($dd_mes) {
        $where_mes_1 = " and (extract (month from trn_data) = ".((int)$dd_mes).") ";
        $where_mes_2 = " and (extract (month from ve_data_inclusao) = ".((int)$dd_mes).") ";
        $where_mes_3a = " and (round(CAST (extract (month from vg.vg_data_inclusao::date) as int),0)=".((int)$dd_mes).") ";
        $where_mes_3b = " and (round(CAST (extract (month from vg.vg_data_concilia::date) as int),0)=".((int)$dd_mes).") ";
        $where_mes_3c = " and (round(CAST (extract (month from pih_data::date) as int),0)=".((int)$dd_mes).") ";
        $where_mes_3d = " and (round(CAST (extract (month from pgc_pin_response_date::date) as int),0)=".((int)$dd_mes).") ";
        $where_mes_ponto_certo = " and (round(CAST (extract (month from data_transacao::date) as int),0)=".((int)$dd_mes).") ";
}


$sql  = "select dia, mes, ano, sum(quantidade) as quantidade, sum (total) as total 
                from (
                        select round(CAST (extract (day from vg.vg_data_inclusao::date) as int), 0) as dia, round(CAST (extract (month from vg.vg_data_inclusao::date) as int),0) as mes, round(CAST (extract (year from vg.vg_data_inclusao::date) as int),0) as ano, sum(vgm.vgm_qtde) as quantidade, sum(vgm.vgm_valor * vgm.vgm_qtde) as total, 'SiteLH' as canal  
                        from tb_dist_venda_games vg inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join dist_usuarios_games ug on ug.ug_id = vg.vg_ug_id 
                        where vg.vg_data_inclusao>='2008-01-01 00:00:00' and vg.vg_ultimo_status='5' ".$where_opr_3." ".$where_ano_3a." ".$where_mes_3a." ".$where_canal_1a." ".$where_operadora."
                        group by vg.vg_data_inclusao::date 				

                        union all

                        select round(CAST (extract (day from vg.vg_data_concilia::date) as int), 0) as dia, round(CAST (extract (month from vg.vg_data_concilia::date) as int),0) as mes, round(CAST (extract (year from vg.vg_data_concilia::date) as int),0) as ano, sum(vgm.vgm_qtde) as quantidade, sum(vgm.vgm_valor * vgm.vgm_qtde) as total, 'SiteGamer' as canal  
                        from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id 
                        where vg.vg_data_concilia>='2008-01-01 00:00:00' and vg.vg_ultimo_status='5' and vg.vg_pagto_tipo != ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." ".$where_opr_3." ".$where_ano_3b." ".$where_mes_3b." ".$where_canal_1b." ".$where_operadora."
                        group by vg.vg_data_concilia::date 				

                        union all

                        select round(CAST (extract (day from vg.vg_data_concilia::date) as int), 0) as dia, round(CAST (extract (month from vg.vg_data_concilia::date) as int),0) as mes, round(CAST (extract (year from vg.vg_data_concilia::date) as int),0) as ano, sum(vgm.vgm_qtde) as quantidade, sum(vgm.vgm_valor * vgm.vgm_qtde) as total, 'SiteGamer' as canal  
                        from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id 
                        where vg.vg_data_concilia>='2008-01-01 00:00:00' and vg.vg_ultimo_status='5' and tvgpo.tvgpo_canal='G' and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." ".$where_opr_3." ".$where_ano_3b." ".$where_mes_3b." ".$where_canal_1b." ".$where_operadora."
                        group by vg.vg_data_concilia::date 	

                        union all

                        select round(CAST (extract (day from vg.vg_data_concilia::date) as int), 0) as dia, round(CAST (extract (month from vg.vg_data_concilia::date) as int),0) as mes, round(CAST (extract (year from vg.vg_data_concilia::date) as int),0) as ano, sum(vgm.vgm_qtde) as quantidade, sum(vgm.vgm_valor * vgm.vgm_qtde) as total, 'ATIMO' as canal  
                        from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id  
                        where vg.vg_data_concilia>='2008-01-01 00:00:00' and vg.vg_ultimo_status_obs like '%AtimoPay%' and vg.vg_http_referer_origem = 'ATIMO' and vg.vg_ultimo_status='5' and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." ".$where_opr_3." ".$where_ano_3b." ".$where_mes_3b." ".$where_canal_1b." ".$where_operadora."
                        group by vg.vg_data_concilia::date						

                        union all

                        select round(CAST (extract (day from vg.vg_data_concilia::date) as int), 0) as dia, round(CAST (extract (month from vg.vg_data_concilia::date) as int),0) as mes, round(CAST (extract (year from vg.vg_data_concilia::date) as int),0) as ano, sum(vgm.vgm_qtde) as quantidade, sum(vgm.vgm_valor * vgm.vgm_qtde) as total, 'SiteLH' as canal  
                        from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id 
                        where vg.vg_data_concilia>='2008-01-01 00:00:00' and vg.vg_ultimo_status='5' and tvgpo.tvgpo_canal='L' and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." ".$where_opr_3." ".$where_ano_3b." ".$where_mes_3b." ".$where_canal_1a." ".$where_operadora."
                        group by vg.vg_data_concilia::date 				

                        union all

                        select round(CAST (extract (day from vg.vg_data_concilia::date) as int), 0) as dia, round(CAST (extract (month from vg.vg_data_concilia::date) as int),0) as mes, round(CAST (extract (year from vg.vg_data_concilia::date) as int),0) as ano, sum(vgm.vgm_qtde) as quantidade, sum(vgm.vgm_valor * vgm.vgm_qtde) as total, 'Cartao' as canal  
                        from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id 
                        where vg.vg_data_concilia>='2008-01-01 00:00:00' and vg.vg_ultimo_status='5' and tvgpo.tvgpo_canal='C' and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." ".$where_opr_3." ".$where_ano_3b." ".$where_canal_c." ".$where_mes_3b." ".$where_operadora."
                        group by vg.vg_data_concilia::date 				
					
                        union all

                        select round(CAST (extract (day from pih_data::date) as int), 0) as dia, round(CAST (extract (month from pih_data::date) as int),0) as mes, round(CAST (extract (year from pih_data::date) as int),0) as ano, count(*) as quantidade, sum(pih_pin_valor/100) as total, 'Cartao' as canal  
                        from pins_integracao_card_historico
                        where pih_data>='2008-01-01 00:00:00' and pin_status = '4' and pih_codretepp = '2' ".$where_opr_cartao." ".$where_ano_3c." ".$where_mes_3c." ".$where_canal_c." 
                        group by pih_data::date 

                        union all

                        select round(CAST (extract (day from pgc_pin_response_date::date) as int), 0) as dia, round(CAST (extract (month from pgc_pin_response_date::date) as int),0) as mes, round(CAST (extract (year from pgc_pin_response_date::date) as int),0) as ano, count(*) as quantidade, CASE WHEN (select opr_product_type from operadoras inner join pins_gocash ON opr_codigo = pgc_opr_codigo limit 1) = 5 THEN sum(pgc_real_amount) WHEN ((select opr_product_type from operadoras  inner join pins_gocash ON  opr_codigo = pgc_opr_codigo limit 1) = 7 OR (select opr_product_type from operadoras  inner join pins_gocash ON  opr_codigo = pgc_opr_codigo limit 1) = 4 )  THEN sum (pgc_face_amount) ELSE sum (pgc_face_amount) END as total, 'Cartao' as canal
                        from pins_gocash
                        where pgc_pin_response_date>='2008-01-01 00:00:00' and pgc_opr_codigo != 0 ".$where_opr_gocash." ".$where_ano_3d." ".$where_mes_3d." ".$where_canal_c."
                        group by pgc_pin_response_date::date


                        union all

                        select round(CAST (extract (day from vg.vg_data_concilia::date) as int), 0) as dia, round(CAST (extract (month from vg.vg_data_concilia::date) as int),0) as mes, round(CAST (extract (year from vg.vg_data_concilia::date) as int),0) as ano, sum(vgm.vgm_qtde) as quantidade, sum(vgm.vgm_valor * vgm.vgm_qtde) as total, 'POS' as canal  
                        from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id 
                        where vg.vg_data_concilia>='2008-01-01 00:00:00' and vg.vg_ultimo_status='5' and SUBSTR(tvgpo.tvgpo_canal, 1, 1) ='P' and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." ".$where_opr_3." ".$where_ano_3b." ".$where_mes_3b." ".$where_canal_2." ".$where_operadora."
                        group by vg.vg_data_concilia::date 				

                        union all

                        select round(CAST (extract (day from data_transacao::date) as int), 0) as dia, round(CAST (extract (month from data_transacao::date) as int),0) as mes, round(CAST (extract (year from data_transacao::date) as int),0) as ano, count(*) as quantidade, sum(valor) as total, 'POS' as canal  
                        from pos_transacoes_ponto_certo 
                        where opr_codigo is not NULL ".$where_operadora_rede_ponto_certo." ".$where_ano_ponto_certo." ".$where_mes_ponto_certo." ".$where_canal_2." 
                        group by data_transacao::date 				

                        union all

                        select extract (day from ve_data_inclusao) as dia, extract (month from ve_data_inclusao) as mes, extract (year from ve_data_inclusao) as ano, count(*) as quantidade, sum(ve_valor) as total, 'POS' as canal  
                        from dist_vendas_pos 
                        where 1=1 ".$where_opr_2." ".$where_ano_2." ".$where_mes_2." ".$where_canal_2."
                        group by dia, mes, ano 
                ) v ";

$sql .= " where 1=1 ";
$sql .= " group by dia, mes, ano ";
$sql .= " order by dia, mes, ano ";

//if(b_IsUsuarioWagner()) {
//if($bdebug) {
//echo "".str_replace("\n", "<br>\n", $sql)."<br>";
//echo "<!-- ".$sql."-->\n\n";
//die("Stop");
//}


$res_count = pg_query($sql);
$total_table = pg_num_rows($res_count);

$ordem = 0;
$img_seta = "/sys/images/seta_down.gif";	

$resdia = pg_exec($connid, $sql);

if($_SESSION["tipo_acesso_pub"]=='PU') {
        $sqlopr = "select opr_nome, opr_codigo from operadoras where (opr_status = '1') and (opr_codigo='".$dd_operadora."') order by opr_nome";
} else {
        $sqlopr = "select opr_nome, opr_codigo from operadoras where (opr_status != '0') order by opr_nome";
}
$resopr = pg_exec($connid, $sqlopr);
?>
<link rel="stylesheet" href="/sys/css/css.css" type="text/css">
<title>E-Prepag</title>
<script language='javascript' src='/js/<?php echo LANG_NAME_CALENDAR_FILE; ?>'></script>
<script language="JavaScript">
<!--
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}
//-->
</script>
</head>
<!-- INICIO CODIGO NOVO -->
<link href="/includes/bootstrap/css/bootstrap.min_new.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/js/jquery.js"></script>
<script type="text/javascript" src="/includes/bootstrap/js/bootstrap.min.js"></script>
<div class="container-fluid">
    <div class="container txt-azul-claro bg-branco">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12 ">
                        <strong><?php echo LANG_PINS_PAGE_TITLE_4; ?></strong>
                    </div>
                </div>
                <div class="row txt-cinza">
                    <div class="col-md-6">
                        <span class="pull-left"><strong><?php echo LANG_PINS_SEARCH_1; ?></strong></span>
                    </div>
                    <div class="col-md-6">
                        <span class="pull-right"><a href="../../commerce/index.php" class="btn btn-primary"><strong><i><?php echo LANG_BACK; ?></i></strong></a></span>
                    </div>
                </div>
                <form name="form1" method="post" action="">
                    <div class="row txt-cinza top10">
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_PINS_CHANNEL; ?></span>
                        </div>
                        <div class="col-md-3">
                            <select name="dd_canal" id="dd_canal" class="form-control pull-left">
                                <option value="" <?php  if($dd_canal!="SiteLH" && $dd_canal!="SiteGamer" && $dd_canal!="POS" && $dd_canal!="ATIMO") echo "selected" ?>><?php echo LANG_PINS_ALL; ?></option>
                                <option value="Site" <?php  if($dd_canal=="Site") echo "selected" ?>>Site (SiteLH+SiteGamer)</option>
                                                    <?php if($_SESSION["tipo_acesso_pub"]=='AT') { ?>
                                <option value="SiteLH" <?php  if($dd_canal=="SiteLH") echo "selected"; ?>>SiteLH</option>
                                <option value="SiteGamer" <?php  if($dd_canal=="SiteGamer") echo "selected"; ?>>SiteGamer</option>
								<option value="ATIMO" <?php  if($dd_canal=="ATIMO") echo "selected"; ?>>ATIMO</option>
                                <?php } ?>
                                <option value="POS" <?php  if($dd_canal=="POS") echo "selected" ?>>POS</option>
                                <option value="Cartao" <?php if("Cartao" == $dd_canal) echo "selected" ?>>Cartão</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_PINS_OPERATOR; ?></span>
                        </div>
                        <div class="col-md-3">
<?php 
                        if($_SESSION["tipo_acesso_pub"]=='PU') 
                        {
                            echo $_SESSION["opr_nome"];
?>
                            <input type="hidden" name="dd_operadora" id="dd_operadora" value="<?php echo $dd_operadora?>">
<?php 
                        } else 
                        {
?>
                            <select name="dd_operadora" id="dd_operadora" class="form-control pull-left">
                                <option value=""><?php echo LANG_PINS_ALL_OPERATORS; ?></option>
                                <?php  while ($pgopr = pg_fetch_array ($resopr)) { ?>
                                <option value="<?php  echo $pgopr['opr_codigo'] ?>" <?php  if($pgopr['opr_codigo'] == $dd_operadora) echo "selected" ?>><?php  echo $pgopr['opr_nome'] ?></option>
                                <?php  } ?>
                            </select>
<?php 
                        } 
?>
                        </div> 
                    </div>
                    <div class="row txt-cinza top10">
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_PINS_REPORT_TYPE; ?></span>
                        </div>
                        <div class="col-md-3">
<?php  
                    if($_SESSION["tipo_acesso_pub"]=='PU') 
                    {
?>
                            <span style="font-weight: bold"><?php echo LANG_PINS_OUT; ?></span>
                            <input type="hidden" name="dd_mode" id="dd_mode" value="<?php echo $dd_mode?>">
<?php  
                    } else 
                    { 
?>	
                            <select name="dd_mode" id="dd_mode" class="form-control pull-left">
                                <option value="S" <?php  if($dd_mode=="S") echo "selected" ?>><?php echo LANG_PINS_OUT; ?></option>
                                <option value="V" <?php  if($dd_mode=="V") echo "selected" ?>><?php echo LANG_PINS_SALE; ?></option>
                            </select>
<?php 
                    } 
?>
                        </div>
                        
                        
<?php 
                        if($_SESSION["tipo_acesso_pub"]=='AT') 
                        {
?>
                        <div class="col-md-2"><?php echo "<input type='checkbox' name='dd_exclui_epp_cash' id='dd_exclui_epp_cash'" . (($dd_exclui_epp_cash==1)?" checked":"") . " class='pull-right' value= '1'>";?></div>
                        <div class="col-md-3"><span class="pull-left">Excluir vendas EPP CASH</span></div>
<?php
                        }
?>
                    </div>
                    <div class="row txt-cinza top10">    
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_MONTH_2; ?></span>
                        </div>
                        <div class="col-md-3">
                            <select name="dd_mes" id="dd_mes" class="form-control pull-left">
<?php
                            for ($codigoMes=1; $codigoMes<=12; $codigoMes++){
                                switch ($codigoMes){
                                    case 1:  $nomeMes = LANG_JANUARY; break;
                                    case 2:  $nomeMes = LANG_FEBRUARY; break;
                                    case 3:  $nomeMes = LANG_MARCH; break;
                                    case 4:  $nomeMes = LANG_APRIL; break;
                                    case 5:  $nomeMes = LANG_MAY; break;
                                    case 6:  $nomeMes = LANG_JUNE; break;
                                    case 7:  $nomeMes = LANG_JULY; break;
                                    case 8:  $nomeMes = LANG_AUGUST; break;
                                    case 9:  $nomeMes = LANG_SEPTEMBER; break;
                                    case 10: $nomeMes = LANG_OCTOBER; break;
                                    case 11: $nomeMes = LANG_NOVEMBER; break;
                                    case 12: $nomeMes = LANG_DECEMBER; break;
                                }
                                
                                if (strlen($codigoMes) == 1){
                                    $codigoMes = '0'.$codigoMes;
                                }

                                echo '<option value="'.$codigoMes.'"';
                                if ($dd_mes == $codigoMes){
                                    echo ' SELECTED';
                                }
                                echo '>'.$nomeMes.'</option>';
                            }
?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_YEAR_2; ?></span>
                        </div>
                        <div class="col-md-3">
                            <select name="dd_ano" id="dd_ano" class="form-control pull-left">
<?php  
                            for($i =  date('Y'); $i >= (int)(substr($inic_oper_data, 6)) ; $i--) 
                            {
?>
                                <option value="<?php  echo $i ?>" <?php  if($dd_ano == $i) echo "selected" ?>><?php  echo $i ?></option>
<?php  
                            } 
?>
                            </select>
                        </div>
                        <div class="col-md-2 pull-right">
                            <button type="submit" name="BtnSearch" value="<?php echo LANG_PINS_SEARCH_2; ?>" class="btn pull-right btn-success"><?php echo LANG_INTEGRATION_SEARCH;?></button>
                        </div>
                    </div>
                </form>
                <div class="row txt-cinza espacamento">
                    <div class="col-md-12 bg-cinza-claro">
                        <table id="table" class="table bg-branco txt-preto fontsize-p">
                        <thead>
                          <tr class="bg-cinza-claro">
                            <th class="text-center"><?php echo LANG_DAY_2; ?></th>
                            <th class="text-center"><?php echo LANG_WEEKDAY_2; ?></th>
                            <th class="text-right"><?php echo LANG_PINS_QUANTITY_1; ?></th>
                            <th class="text-right"><?php echo LANG_PINS_TOTAL_VALUE; ?></th>
                          </tr>
                        </thead>
                        <tbody>
<?php
                        $cabecalho = "'".LANG_DAY_2."','".LANG_WEEKDAY_2."','".LANG_PINS_QUANTITY_1."','".LANG_PINS_TOTAL_VALUE."'";
                        
                        while ($pgdia = pg_fetch_array($resdia))
                        {
                            $valor = true;

                            $pin_total_valor += $pgdia['total'];
                            $pin_total_qtde += $pgdia['quantidade'];

                            $dia =  $pgdia['ano']."/".((strlen($pgdia['mes'])<=1)?"0":"").$pgdia['mes']."/".((strlen($pgdia['dia'])<=1)?"0":"").$pgdia['dia'];
                            $dia_sem = date("w", strtotime($dia));
                            if($dia_sem==1) {
                                echo "<tr><td height='2' colspan='4'><img src='/sys/images/quad_azul.gif' width='100%' height='2' border='0'></td></tr>\n";
                            }
?>
                            <tr class="trListagem"> 
                                <td class="text-center"><?php  echo ((strlen($pgdia['dia'])<=1)?"0":"").$pgdia['dia']."/".((strlen($pgdia['mes'])<=1)?"0":"").$pgdia['mes']."/".$pgdia['ano'] ?></td>
                                <td class="text-center"><?php  echo get_day_of_week($dia) ?></td>
                                <td class="text-right"><?php  echo number_format($pgdia['quantidade'], 0, ',', '.') ?></td>
                                <td class="text-right"><?php  echo number_format($pgdia['total'], 2, ',', '.') ?></td>
                            </tr>
<?php                            

                        }
                        
                        if (!$valor)
                        {
?>
                            <tr class="bg-cinza-claro"> 
                                <td colspan="4">
                                  <?php echo LANG_NO_DATA; ?>.
                                </td>
                            </tr>
<?php  
                        } else 
                        { 
?>
                            <tr class="bg-cinza-claro"> 
                                <td colspan="2"><strong>TOTAL</strong></td>
                                <td class="text-right"><strong><?php  echo number_format($pin_total_qtde, 0, ',', '.') ?></strong></td>
                                <td class="text-right"><strong><?php  echo number_format($pin_total_valor, 2, ',', '.') ?></strong></td>
                            </tr>
<?php 
                            $time_end = getmicrotime();
                            $time = $time_end - $time_start;
?>
                            <tr class="bg-cinza-claro"> 
                                <td colspan="4" class="fontsize-pp"><strong><?php echo LANG_PINS_LAST_MSG; ?>. </strong></td>
                            </tr>
                            <tr class="bg-cinza-claro"> 
                                <td colspan="4" class="fontsize-pp"><?php  echo LANG_PINS_SEARCH_MSG.' '.number_format($time, 2, '.', '.').' '.LANG_PINS_SEARCH_MSG_UNIT; ?></td>
                            </tr>
                            <tr class="bg-cinza-claro"> 
                                <td colspan="4" class="fontsize-pp"><?php echo date('Y-m-d H:i:s'); ?></td>
                            </tr>
<?php  
                        } 
?>
                        </tbody>
                      </table>
<?php
                        if($valor) {
?>
                        <div class="row text-center" style="margin-bottom: 15px;">
                           <a href="#" class="btn downloadCsv btn-info ">Download CSV</a>
                        </div>
<?php
                        }
?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<link href="/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="/js/facebook.js"></script>
<script src="/js/jquery-ui-1.9.2.custom.min.js"></script>
<script type="text/javascript" src="/js/table2CSV.js"></script>t>
<script>
$(function(){
    $('#table').table2CSV({header:[<?php echo $cabecalho; ?>],toStr:""});
});
</script>

<!-- FIM CODIGO NOVO -->
<?php  
require_once $raiz_do_projeto . "public_html/sys/includes/rodape_sys.php";
?>