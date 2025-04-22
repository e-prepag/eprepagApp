<?php
set_time_limit ( 300 ) ;

require_once "../../../../../includes/constantes.php";
require_once $raiz_do_projeto . "public_html/sys/includes/topo_sys.php";
require_once $raiz_do_projeto . "includes/gamer/constantes.php";
//require_once $raiz_do_projeto . "/incs/functions.php";

$vetorPublisherPorUtilizacao = levantamentoPublisherComFechamentoUtilizacao();

$pos_pagina = $seg_auxilar;

$time_start = getmicrotime();

if($_SESSION["tipo_acesso_pub"]=='PU') {
        $dd_operadora = $_SESSION["opr_codigo_pub"];
        $dd_mode = "S";
        $Submit = "Buscar";
}

if(!$dd_mode || ($dd_mode!='V')) {
        $dd_mode = "S";
}

if(!$ncamp)    $ncamp       = 'ano';
if(!$ordem)    $ordem       = 0;
if($BtnSearch && $BtnSearch!=1 ) $total_table = 0;

$where_opr_1 = "";
$where_opr_2 = "";
$where_canal = "";
$where_opr_3 = "";
$where_opr_cartao = "";
$where_opr_gocash = "";
$where_operadora_rede_ponto_certo = "";

if(count($vetorPublisherPorUtilizacao)>0) {
    $where_opr_venda_lan = " AND ( CASE ";
    $where_opr_venda_lan_negativa = " AND ( CASE ";
    $where_opr_utilizacao_lan = " AND ( CASE ";
    foreach ($vetorPublisherPorUtilizacao as $opr_codigo => $opr_data_inicio_contabilizacao_utilizacao){ 
        //echo "ID: ".$opr_codigo." => DATA: [".substr($opr_data_inicio_contabilizacao_utilizacao,0,19)."]<br>";
        $where_opr_venda_lan .= " WHEN vgm.vgm_opr_codigo = $opr_codigo THEN vg.vg_data_inclusao < '".substr($opr_data_inicio_contabilizacao_utilizacao,0,19)."' ";
        $where_opr_venda_lan_negativa .= " WHEN vgm.vgm_opr_codigo = $opr_codigo THEN vg.vg_data_inclusao >= '".substr($opr_data_inicio_contabilizacao_utilizacao,0,19)."' ";
        $where_opr_utilizacao_lan .= "  WHEN pih_id = $opr_codigo THEN pih_data >= '".substr($opr_data_inicio_contabilizacao_utilizacao,0,19)."' ";
    }//end foreach
    $where_opr_venda_lan .= " ELSE vg.vg_data_inclusao > '2008-01-01 00:00:00' END )";
    $where_opr_venda_lan_negativa .= " ELSE FALSE END )";
    $where_opr_utilizacao_lan .= "  ELSE FALSE END ) ";
} //end if(count($vetorPublisherPorUtilizacao)>0)
else {
    $where_opr_venda_lan = "";
    $where_opr_venda_lan_negativa = "";
    $where_opr_utilizacao_lan = "";
}//end else do if(count($vetorPublisherPorUtilizacao)>0)


if($dd_canal) {
        $where_canal = " and canal= '".$dd_canal."' ";
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

$sql  = "select ano, sum(quantidade) as quantidade, sum (total) as total 
                        from ( ";
if(empty($dd_canal) || $dd_canal == "Site") {
        $sql .= "
                                select round(CAST (extract (year from vg.vg_data_inclusao::date) as int),0) as ano, sum(vgm.vgm_qtde) as quantidade, sum(vgm.vgm_valor * vgm.vgm_qtde) as total, 'Site' as canal  
                                from tb_dist_venda_games vg inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join dist_usuarios_games ug on ug.ug_id = vg.vg_ug_id 
                                where vg.vg_data_inclusao>='2008-01-01 00:00:00' and vg.vg_ultimo_status='5' ".$where_opr_3." ".$where_opr_venda_lan."
                                group by ano 
                                union all

                                select round(CAST (extract (year from vg.vg_data_concilia::date) as int),0) as ano, sum(vgm.vgm_qtde) as quantidade, sum(vgm.vgm_valor * vgm.vgm_qtde) as total, 'Site' as canal  
                                from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id 
                                where vg.vg_data_concilia>='2008-01-01 00:00:00' and vg.vg_ultimo_status='5' and vg.vg_pagto_tipo != ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." ".$where_opr_3." 
                                group by ano 
								
								union all

                                select round(CAST (extract (year from vg.vg_data_concilia::date) as int),0) as ano, sum(vgm.vgm_qtde) as quantidade, sum(vgm.vgm_valor * vgm.vgm_qtde) as total, 'ATIMO' as canal  
                                from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id 
                                where vg.vg_data_concilia>='2008-01-01 00:00:00' and vg.vg_ultimo_status='5' and vg.vg_ultimo_status_obs like '%AtimoPay%' and vg.vg_http_referer_origem = 'ATIMO' and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." ".$where_opr_3."
                                group by ano 

                                union all

                                select round(CAST (extract (year from vg.vg_data_concilia::date) as int),0) as ano, sum(vgm.vgm_qtde) as quantidade, sum(vgm.vgm_valor * vgm.vgm_qtde) as total, 'Site' as canal  
                                from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id
                                where vg.vg_data_concilia>='2008-01-01 00:00:00' and vg.vg_ultimo_status='5' and (tvgpo.tvgpo_canal='G' or tvgpo.tvgpo_canal='L') and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." ".$where_opr_3."
                                group by ano 

                                ";

        if(count($vetorPublisherPorUtilizacao)>0) {
            $sql .= "
                                union all

                                select  round(CAST (extract (year from pih_data::date) as int),0) as ano, 
                                        count(*) as quantidade, 
                                        sum(vgm.vgm_valor) as total, 
                                        'Site' as canal
                                from tb_dist_venda_games vg 
                                     inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                                     inner join tb_dist_venda_games_modelo_pins vgmp on vgmp_vgm_id = vgm.vgm_id 
                                     inner join pins_integracao_historico pih on pih_pin_id = vgmp_pin_codinterno
                                where vg.vg_data_inclusao>='2008-01-01 00:00:00' 
                                     and vg.vg_ultimo_status='5'
                                     and pin_status = '8'
                                     and pih_codretepp='2'
                                     ".$where_opr_venda_lan_negativa."
                                     ".$where_opr_3." 
                                     ".$where_opr_utilizacao_lan."
                                group by pih_data::date				
                    ";
        }//end if(count($vetorPublisherPorUtilizacao)>0)


} //end if(empty($dd_canal) || $dd_canal = "Site")

if(empty($dd_canal) || $dd_canal == "Cartao") {
        if(empty($dd_canal)) {
             $sql .= " union all 
                     ";
        }
        $sql .= "

                                select round(CAST (extract (year from vg.vg_data_concilia::date) as int),0) as ano, sum(vgm.vgm_qtde) as quantidade, sum(vgm.vgm_valor * vgm.vgm_qtde) as total, 'Cartao' as canal  
                                from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id
                                where vg.vg_data_concilia>='2008-01-01 00:00:00' and vg.vg_ultimo_status='5' and tvgpo.tvgpo_canal='C' and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." ".$where_opr_3."
                                group by ano 

                                union all

                                select round(CAST (extract (year from pih_data::date) as int),0) as ano, count(*) as quantidade, sum(pih_pin_valor/100) as total, 'Cartao' as canal  
                                from pins_integracao_card_historico
                                where pih_data>='2008-01-01 00:00:00' and pin_status = '4' and pih_codretepp = '2' ".$where_opr_cartao."
                                group by ano 

                                union all

                                select round(CAST (extract (year from pgc_pin_response_date::date) as int),0) as ano, count(*) as quantidade, CASE WHEN (select opr_product_type from operadoras inner join pins_gocash ON opr_codigo = pgc_opr_codigo limit 1) = 5 THEN sum(pgc_real_amount) WHEN ((select opr_product_type from operadoras  inner join pins_gocash ON  opr_codigo = pgc_opr_codigo limit 1) = 7 OR (select opr_product_type from operadoras  inner join pins_gocash ON  opr_codigo = pgc_opr_codigo limit 1) = 4 )  THEN sum (pgc_face_amount) ELSE sum (pgc_face_amount) END as total, 'Cartao' as canal
                                from pins_gocash
                                where pgc_pin_response_date>='2008-01-01 00:00:00' and pgc_opr_codigo != 0 ".$where_opr_gocash." 
                                group by pgc_pin_response_date::date

                                ";

}//end  if(empty($dd_canal) || $dd_canal = "Cartao")  

if($dd_canal == "ATIMO") {

        $sql .= "
                        select round(CAST (extract (year from vg.vg_data_concilia::date) as int),0) as ano, sum(vgm.vgm_qtde) as quantidade, sum(vgm.vgm_valor * vgm.vgm_qtde) as total, 'ATIMO' as canal  
                        from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id 
                        where vg.vg_data_concilia>='2008-01-01 00:00:00' and vg.vg_ultimo_status='5' and vg.vg_ultimo_status_obs like '%AtimoPay%' and vg.vg_http_referer_origem = 'ATIMO' and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." ".$where_opr_3."
                        group by ano  ";
						
						
						
} //end if(empty($dd_canal) || $dd_canal = "Site")            

if(empty($dd_canal) || $dd_canal == "POS") {
        if(empty($dd_canal)) {
             $sql .= " union all 
                     ";
        }
        $sql .= "

                                select round(CAST (extract (year from vg.vg_data_concilia::date) as int),0) as ano, sum(vgm.vgm_qtde) as quantidade, sum(vgm.vgm_valor * vgm.vgm_qtde) as total, 'POS' as canal  
                                from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id
                                where vg.vg_data_concilia>='2008-01-01 00:00:00' and vg.vg_ultimo_status='5' and SUBSTR(tvgpo.tvgpo_canal, 1, 1) ='P' and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." ".$where_opr_3." 
                                group by ano 

                                union all

                                select round(CAST (extract (year from data_transacao::date) as int),0) as ano, count(*) as quantidade, sum(valor) as total, 'POS' as canal  
                                from pos_transacoes_ponto_certo 
                                where opr_codigo is not NULL ".$where_operadora_rede_ponto_certo." 
                                group by data_transacao::date 				

                                union all

                                select extract (year from ve_data_inclusao) as ano, count(*) as quantidade, sum(ve_valor) as total, 'POS' as canal  
                                from dist_vendas_pos 
                                where extract (year from ve_data_inclusao) >= '".$inic_oper_ano."' ".$where_opr_2." 
                                group by ano  ";

}//end  if(empty($dd_canal) || $dd_canal = "POS")              

$sql .= "
                        ) v ";



$sql .= " where 1=1 ".($dd_canal == "ATIMO")?"":$where_canal." ";
$sql .= "group by ano"; //", canal";

$res_count = pg_query($sql);
$total_table = pg_num_rows($res_count);

$sql .= " order by ".$ncamp;

if($ordem == 0)
{
        $sql .= " desc ";
        $img_seta = "/sys/imagens/seta_down.gif";	
}
else
{
        $sql .= " asc ";
        $img_seta = "/sys/imagens/seta_up.gif";
}

//echo "<!-- $sql<br> --->";

$resano = pg_exec($connid, $sql);

if($_SESSION["tipo_acesso_pub"]=='PU') {
        $sqlopr = "select opr_nome, opr_codigo from operadoras where (opr_status = '1') and (opr_codigo='".$dd_operadora."') order by opr_nome";
} else {
        $sqlopr = "select opr_nome, opr_codigo from operadoras where (opr_status != '0') order by opr_nome";
}
$resopr = pg_exec($connid, $sqlopr);
?>
<script language="JavaScript">
<!--
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}
//-->
</script>

<!-- INICIO CODIGO NOVO -->
<link href="/includes/bootstrap/css/bootstrap.min_new.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/js/jquery.js"></script>
<script type="text/javascript" src="/includes/bootstrap/js/bootstrap.min.js"></script>
<link href="/sys/css/css.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="/js/table2CSV.js"></script>
<div class="container-fluid">
    <div class="container txt-azul-claro bg-branco">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12 ">
                        <strong><?php echo LANG_PINS_PAGE_TITLE_2; ?></strong>
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
                <div class="row txt-cinza espacamento">
                    <div class="col-md-3">
                        <?php echo LANG_PINS_CHANNEL; ?>
                    </div>
                    <div class="col-md-3">
                        <?php echo LANG_PINS_OPERATOR; ?>
                    </div>
                    <div class="col-md-3">
                        <?php echo LANG_PINS_REPORT_TYPE; ?>
                    </div>
                </div>
                <div class="row txt-cinza">
                    <div class="col-md-3">
                        <select name="dd_canal" id="dd_canal" class="form-control" onChange="document.form1.submit()">
                            <option value=""><?php echo LANG_PINS_ALL_CHANNELS; ?></option>
                            <option value="Site" <?php if("Site" == $dd_canal) echo "selected" ?>>Site</option>
                            <option value="POS" <?php if("POS" == $dd_canal) echo "selected" ?>>POS</option>
							<option value="ATIMO" <?php if($dd_canal=="ATIMO") echo "selected"; ?>>ATIMO</option> 
                            <option value="Cartao" <?php if("Cartao" == $dd_canal) echo "selected" ?>>Cartão</option>
                        </select>
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

                            <select name="dd_operadora" id="dd_operadora" class="form-control" onChange="document.form1.submit()">
                                <option value=""><?php echo LANG_PINS_ALL_OPERATORS; ?></option>
<?php 
                            while ($pgopr = pg_fetch_array ($resopr)) 
                            {
?>
                                <option value="<?php echo $pgopr['opr_codigo'] ?>" <?php if($pgopr['opr_codigo'] == $dd_operadora) echo "selected" ?>><?php echo $pgopr['opr_nome'] ?></option>
<?php 
                            }
?>
                            </select>
<?php
                        }
?>
                    </div>
                    <div class="col-md-3">
<?php 
                        if($_SESSION["tipo_acesso_pub"]=='PU')
                        {
?>
                        <strong><?php echo LANG_PINS_OUT; ?></strong>
                        <input type="hidden" name="dd_mode" id="dd_mode" value="<?php echo $dd_mode?>">
<?php 
                        } else 
                        {
?>	
                        <select name="dd_mode" id="dd_mode" class="form-control" onChange="document.form1.submit()">
                            <option value="S" <?php if($dd_mode=="S") echo "selected" ?>><?php echo LANG_PINS_OUT; ?></option>
                            <option value="V" <?php if($dd_mode=="V") echo "selected" ?>><?php echo LANG_PINS_SALE; ?></option>
                        </select>
<?php
                        } 
?>
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
                            <th><?php echo LANG_YEAR_2; ?></th>
                            <th class="text-right"><?php echo LANG_PINS_QUANTITY_1; ?></th>
                            <th class="text-right"><?php echo LANG_PINS_TOTAL_VALUE; ?></th>
                          </tr>
                        </thead>
                        <tbody>
<?php
                        $pin_total_valor = 0;
                        $pin_total_qtde = 0;
                        $cabecalho = "'".LANG_YEAR_2."','".LANG_PINS_QUANTITY_1."','".LANG_PINS_TOTAL_VALUE."'";
                        
                        while ($pgano = pg_fetch_array($resano))
                        {
                            $valor = true;

                            $pin_total_valor += $pgano['total'];
                            $pin_total_qtde += $pgano['quantidade'];
?>
                            <tr class="trListagem"> 
                                <td><?php echo $pgano['ano'] ?></td>
                                <td class="text-right"><?php echo number_format($pgano['quantidade'], 0, ',', '.') ?></td>
                                <td class="text-right"><?php echo number_format($pgano['total'], 2, ',', '.') ?></td>
                            </tr>
<?php
                        }
                        if(!$valor) { 
?>
                            <tr class="bg-cinza-claro">
                                <td><?php echo LANG_NO_DATA; ?>.</td>
                            </tr>
<?php 
                        } else {
?>
                            <tr class="bg-cinza-claro">
                                <td><strong><?php echo LANG_PINS_TOTAL; ?></strong></font></td>
                                <td class="text-right"><strong><?php echo number_format($pin_total_qtde, 0, ',', '.') ?></strong></td>
                                <td class="text-right"><strong><?php echo number_format($pin_total_valor, 2, ',', '.') ?></strong></td>
                            </tr>
<?php
                        }
                        $time_end = getmicrotime();
                        $time = $time_end - $time_start;
?>
<!--                            <tr class="bg-cinza-claro">
                                <td colspan="3">
                                    <strong><?php LANG_PINS_LAST_MSG; ?>.</strong>
                                </td>
                            </tr>-->
                            <tr class="bg-cinza-claro">
                                <td colspan="3"><?php echo LANG_PINS_SEARCH_MSG.' '.number_format($time, 2, '.', '.').' '.LANG_PINS_SEARCH_MSG_UNIT; ?></td>
                            </tr>
                            <tr class="bg-cinza-claro"> 
                                <td colspan="3" class="fontsize-pp"><?php echo date('Y-m-d H:i:s'); ?></td>
                            </tr>
                        </tbody>
                      </table>
<?php
                        if($valor) {
?>
                        <div class="row text-center" >
                            <a href="#" class="btn downloadCsv btn-info ">Download CSV</a>
                        <div class="row text-center" style="margin-bottom: 15px;">
<?php
                        }
?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
$(function(){
    $('#table').table2CSV({header:[<?php echo $cabecalho; ?>],toStr:""});
});
</script>
<!-- FIM CODIGO NOVO -->
<?php
    require_once $raiz_do_projeto . "public_html/sys/includes/rodape_sys.php";
?>