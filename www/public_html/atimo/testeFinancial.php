<?php
header("Content-Type: text/html; charset=ISO-8859-1",true);
// Pré-Processamento para fechamento financeiro
// financial_processing.php 
// - Processa totais por publisher, dia e canal

error_reporting(E_ALL); 
ini_set("display_errors", 1); 

$raiz_do_projeto = "/www/";

require_once $raiz_do_projeto ."includes/main.php";
require_once $raiz_do_projeto . "includes/gamer/main.php"; 

//Esse ID é concatenado no inicio de cada id da operação('id_venda' => 'id_op') para diferenciar o tipo de venda que foi feito
$ARRAY_CONCATENA_ID_VENDA = array
                                    (
                                        'gamer'          => '10',
                                        'pdv'            => '20',
                                        'cards'          => '30',
                                        'boleto_express' => '40'
                                    );

$time_start_stats = getmicrotime();

//Buscando Publisher que possuem totalização por utilização
$vetorPublisherPorUtilizacao = levantamentoPublisherComFechamentoUtilizacao();

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

$msg = "";

echo PHP_EOL.str_repeat("=", 80).PHP_EOL."Pré-Processamento para fechamento financeiro (".date("Y-m-d H:i:s").")".PHP_EOL.PHP_EOL;

$sql = "select 
	canal, 
	dia,
	publisher,
	sum(n) as n, 
        sum(total_order) as total_order,
	round(sum(total)::numeric,2) as total 
from ( 
	(select 
		'P' as canal, 
		date_trunc('day', ve_data_inclusao) as dia,
		CASE WHEN ve_jogo='HB' THEN 16 WHEN ve_jogo='OG' THEN 13 WHEN ve_jogo='MU' THEN 34 END as publisher,
		count(*) as n, 
                count(distinct('".$ARRAY_CONCATENA_ID_VENDA['pdv']."'  || to_char(ve_data_inclusao,'YYMMDD') || lpad(ve_id::text , 8, '0'))) as total_order,
		sum(ve_valor) as total 
	from dist_vendas_pos 
	where ve_data_inclusao >= (select min(fp_date) from financial_processing inner join operadoras on opr_codigo=fp_publisher where fp_freeze=0 and opr_status='1')
	group by dia,
		publisher) 
union all 
	(select 
		'P' as canal,
		date_trunc('day', vg.vg_data_concilia) as dia,
		vgm_opr_codigo as publisher,
		sum(vgm.vgm_qtde) as n, 
                count(distinct('".$ARRAY_CONCATENA_ID_VENDA['pdv']."'  || to_char(vg.vg_data_concilia,'YYMMDD') || lpad(vg.vg_id::text , 8, '0'))) as total_order,
		sum(vgm.vgm_valor * vgm.vgm_qtde) as total 
	from tb_venda_games vg 
		inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
		inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id 
	where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
		and vg.vg_data_concilia >= (select min(fp_date) from financial_processing inner join operadoras on opr_codigo=fp_publisher where fp_freeze=0 and opr_status='1')
		and SUBSTR(tvgpo.tvgpo_canal, 1, 1) ='P' 
		and vg.vg_pagto_tipo = ".$GLOBALS['PAGAMENTO_PIN_EPREPAG_NUMERIC']." 
	group by dia,
		publisher) 
union all
	(select 
		'P' as canal,
		date_trunc('day', data_transacao) as dia,
		opr_codigo as publisher,
		count(*) as n, 
                count(distinct('".$ARRAY_CONCATENA_ID_VENDA['pdv']."'  || to_char(data_transacao,'YYMMDD') || lpad(id_transacao::text , 8, '0'))) as total_order,
		sum(valor) as total 
        from pos_transacoes_ponto_certo 
        where opr_codigo is not NULL 
		and data_transacao >= (select min(fp_date) from financial_processing inner join operadoras on opr_codigo=fp_publisher where fp_freeze=0 and opr_status='1')
	group by dia,
		publisher) 
union all 
	(select 
		case when vg.vg_ug_id = 7909 then 'E' when vg.vg_ug_id != 7909 then 'M' end as canal,
		date_trunc('day', vg.vg_data_concilia) as dia,
		vgm_opr_codigo as publisher,
		sum(vgm.vgm_qtde) as n, 
			count(distinct(10 || to_char(vg.vg_data_concilia,'YYMMDD') || lpad(vg.vg_id::text , 8, '0'))) as total_order,
		sum(vgm.vgm_valor * vgm.vgm_qtde) as total 
		from tb_venda_games vg 
		inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
		where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."'
		and vg.vg_data_concilia >= (select min(fp_date) from financial_processing inner join operadoras on opr_codigo=fp_publisher where fp_freeze=0 and opr_status='1')
		and vg.vg_ultimo_status_obs like '%Pagamento via AtimoPay%'
		and vg.vg_pagto_tipo = ".$GLOBALS['PAGAMENTO_PIN_EPREPAG_NUMERIC']."
		group by dia,canal,publisher)
union all 
	(select 
		case when vg.vg_ug_id = '".$GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY']."' then 'E' when vg.vg_ug_id != '".$GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY']."' then 'M' end as canal,
		date_trunc('day', vg.vg_data_concilia) as dia,
		vgm_opr_codigo as publisher,
		sum(vgm.vgm_qtde) as n,
                count(distinct('".$ARRAY_CONCATENA_ID_VENDA['gamer']."'  || to_char(vg.vg_data_concilia,'YYMMDD') || lpad(vg.vg_id::text , 8, '0'))) as total_order,
		sum(vgm.vgm_valor * vgm.vgm_qtde) as total 
	from tb_venda_games vg 
		inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
	where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
		and vg.vg_data_concilia >= (select min(fp_date) from financial_processing inner join operadoras on opr_codigo=fp_publisher where fp_freeze=0 and opr_status='1')
		and vg.vg_pagto_tipo != ".$GLOBALS['PAGAMENTO_PIN_EPREPAG_NUMERIC']." 
	group by dia, 
		canal,
		publisher) 
union all 
	(select 
		case when vg.vg_ug_id = '".$GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY']."' then 'E' when vg.vg_ug_id != '".$GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY']."' then 'M' end as canal,
		date_trunc('day', vg.vg_data_concilia) as dia,
		vgm_opr_codigo as publisher,
		sum(vgm.vgm_qtde) as n, 
                count(distinct('".$ARRAY_CONCATENA_ID_VENDA['gamer']."'  || to_char(vg.vg_data_concilia,'YYMMDD') || lpad(vg.vg_id::text , 8, '0'))) as total_order,
		sum(vgm.vgm_valor * vgm.vgm_qtde) as total 
	from tb_venda_games vg 
		inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
		inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id 
	where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
		and vg.vg_data_concilia >= (select min(fp_date) from financial_processing inner join operadoras on opr_codigo=fp_publisher where fp_freeze=0 and opr_status='1')
		and tvgpo.tvgpo_canal='G' 
		and vg.vg_pagto_tipo = ".$GLOBALS['PAGAMENTO_PIN_EPREPAG_NUMERIC']." 
	group by dia, 
		canal,
		publisher) 
union all 
	(select 
		'L' as canal,
		date_trunc('day', vg.vg_data_inclusao) as dia,
		vgm_opr_codigo as publisher,
		sum(vgm.vgm_qtde) as n, 
                count(distinct('".$ARRAY_CONCATENA_ID_VENDA['gamer']."'  || to_char(vg.vg_data_inclusao,'YYMMDD') || lpad(vg.vg_id::text , 8, '0'))) as total_order,
		sum(vgm.vgm_valor * vgm.vgm_qtde) as total 
	from tb_dist_venda_games vg 
		inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
	where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' ".$where_opr_venda_lan."
		and vg.vg_data_inclusao >= (select min(fp_date) from financial_processing inner join operadoras on opr_codigo=fp_publisher where fp_freeze=0 and opr_status='1')
	group by dia,
		publisher) ";
//Contabilizando vendas por utilização de PINs Publisher
if(count($vetorPublisherPorUtilizacao)>0) {
    $sql .= "
        
union all

        (select 
                'L' as canal,
                date_trunc('day', pih_data) as dia,
                vgm_opr_codigo as publisher,
                count(*) as n, 
                count(distinct('".$ARRAY_CONCATENA_ID_VENDA['pdv']."'  || to_char(vg.vg_data_inclusao,'YYMMDD') || lpad(vg.vg_id::text , 8, '0'))) as total_order,
                sum(vgm.vgm_valor) as total
        from tb_dist_venda_games vg 
             inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
             inner join tb_dist_venda_games_modelo_pins vgmp on vgmp_vgm_id = vgm.vgm_id 
             inner join pins_integracao_historico pih on pih_pin_id = vgmp_pin_codinterno
        where vg.vg_data_inclusao>='2008-01-01 00:00:00' 
             and vg.vg_ultimo_status='5'
             and pin_status = '8'
             and pih_codretepp='2'
             ".$where_opr_venda_lan_negativa."
             and pih_data >= (select min(fp_date) from financial_processing inner join operadoras on opr_codigo=fp_publisher where fp_freeze=0 and opr_status='1')
             ".$where_opr_utilizacao_lan."
        group by dia,
                publisher) 
            ";
}//end if(count($vetorPublisherPorUtilizacao)>0)
 
$sql .=" 
union all 
	(select 
		'L' as canal,
		date_trunc('day', vg.vg_data_concilia) as dia,
		vgm_opr_codigo as publisher,
		sum(vgm.vgm_qtde) as n, 
                count(distinct('".$ARRAY_CONCATENA_ID_VENDA['pdv']."'  || to_char(vg.vg_data_inclusao,'YYMMDD') || lpad(vg.vg_id::text , 8, '0'))) as total_order,
		sum(vgm.vgm_valor * vgm.vgm_qtde) as total 
	from tb_venda_games vg 
		inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
		inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id 
	where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
		and vg.vg_data_concilia >= (select min(fp_date) from financial_processing inner join operadoras on opr_codigo=fp_publisher where fp_freeze=0 and opr_status='1')
		and tvgpo.tvgpo_canal='L' 
		and vg.vg_pagto_tipo = ".$GLOBALS['PAGAMENTO_PIN_EPREPAG_NUMERIC']." 
	group by dia,
		publisher) 
-- naun vai calcular os cartoes fisicos da webzen e ongame por conta da incoerencia de informações
-- Contabilizando PINs GoCASH utilizado na loja como EPP CASH                
union all 
	(select 
		'C' as canal,
		date_trunc('day', vg.vg_data_concilia) as dia,
		vgm_opr_codigo as publisher,
		sum(vgm.vgm_qtde) as n, 
                count(distinct('".$ARRAY_CONCATENA_ID_VENDA['pdv']."'  || to_char(vg.vg_data_concilia,'YYMMDD') || lpad(vg.vg_id::text , 8, '0'))) as total_order,
		sum(vgm.vgm_valor * vgm.vgm_qtde) as total 
	from tb_venda_games vg 
		inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
		inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id 
	where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
		and vg.vg_data_concilia >= (select min(fp_date) from financial_processing inner join operadoras on opr_codigo=fp_publisher where fp_freeze=0 and opr_status='1')
		and tvgpo.tvgpo_canal='C' 
		and vg.vg_pagto_tipo = ".$GLOBALS['PAGAMENTO_PIN_EPREPAG_NUMERIC']." 
	group by dia,
		publisher) 
-- Contabilizando PINs GiftCards utilizados por Integração                
union all 
	(select 
		'C' as canal,
		date_trunc('day', pih_data) as dia,
		pih_id as publisher,
		count(*) as n, 
                count(distinct('".$ARRAY_CONCATENA_ID_VENDA['cards']."'  || to_char(pih_data,'YYMMDD') || lpad(pih_pin_id::text , 8, '0'))) as total_order,
		sum(pih_pin_valor/100) as total 
	from pins_integracao_card_historico
	where pin_status = '".intval($PINS_STORE_STATUS_VALUES['U'])."' 
		and pih_codretepp = '2'
		and pih_data >= (select min(fp_date) from financial_processing inner join operadoras on opr_codigo=fp_publisher where fp_freeze=0 and opr_status='1')
	group by dia,
		publisher)
-- Contabilizando PINs GoCASH utilizado por Integração de Utilização                
union all 
	(select 
		'C' as canal,
		date_trunc('day', pgc_pin_response_date) as dia,
		pgc_opr_codigo as publisher,
		count(*) as n, 
                count(distinct('".$ARRAY_CONCATENA_ID_VENDA['cards']."'  || to_char(pgc_pin_response_date,'YYMMDD') || lpad(pgc_id::text , 8, '0'))) as total_order,
		CASE WHEN (select opr_product_type from operadoras where opr_codigo = pgc_opr_codigo) = 5 THEN sum(pgc_real_amount) 
                     WHEN ((select opr_product_type from operadoras where opr_codigo = pgc_opr_codigo) = 7 OR (select opr_product_type from operadoras where opr_codigo = pgc_opr_codigo) = 4 )  THEN sum (pgc_face_amount) 
                     ELSE sum (pgc_face_amount) END as total 
	from pins_gocash
	where pgc_opr_codigo != 0 
		 and pgc_pin_response_date >= (select min(fp_date) from financial_processing inner join operadoras on opr_codigo=fp_publisher where fp_freeze=0 and opr_status='1')
	group by dia,
		publisher) 
) t 
where publisher NOT IN (".$GLOBALS['dd_operadora_EPP_Cash'].",".$GLOBALS['dd_operadora_EPP_Cash_LH'].",".$GLOBALS['dd_operadora_Treinamento'].")
group by canal, 
	dia,
	publisher
order by dia desc, 
	canal,
	publisher;";
echo $sql.PHP_EOL.PHP_EOL;
