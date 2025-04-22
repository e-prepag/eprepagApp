<?php
header("Content-Type: text/html; charset=ISO-8859-1",true);
// Pr�-Processamento para fechamento financeiro
// financial_processing.php 
// - Processa totais por publisher, dia e canal

error_reporting(E_ALL); 
ini_set("display_errors", 1); 

$raiz_do_projeto = "/www/";
require_once "/www/includes/main.php";
require_once $raiz_do_projeto . "includes/gamer/main.php"; 

//Esse ID � concatenado no inicio de cada id da opera��o('id_venda' => 'id_op') para diferenciar o tipo de venda que foi feito
$ARRAY_CONCATENA_ID_VENDA = array
                                    (
                                        'gamer'          => '10',
                                        'pdv'            => '20',
                                        'cards'          => '30',
                                        'boleto_express' => '40'
                                    );

$time_start_stats = getmicrotime();

//Buscando Publisher que possuem totaliza��o por utiliza��o
//$vetorPublisherPorUtilizacao = levantamentoPublisherComFechamentoUtilizacao();
// { [13]=> string(22) "2015-08-16 00:00:00-03" [124]=> string(22) "2018-08-28 00:00:00-03" [137]=> string(22) "2018-10-16 00:00:00-03" [143]=> string(22) "2020-01-07 00:00:00-03" [147]=> string(22) "2019-11-30 00:00:00-03" [148]=> string(22) "2020-07-31 00:00:00-03" }

//$oar[0]['124'] = "2018-08-28 00:00:00-03";
$oar[1]['137'] = "2018-10-16 00:00:00-03";
$oar[2]['143'] = "2020-01-07 00:00:00-03";
$oar[3]['147'] = "2019-11-30 00:00:00-03";
$oar[4]['148'] = "2020-07-31 00:00:00-03";
$oar[5]['13'] = "2015-08-16 00:00:00-03";

$escolhe = $_GET['i'];
$vetorPublisherPorUtilizacao = $oar[$escolhe]; // 
$opr_id = 0;
// $vetorPublisherPorUtilizacao ;
if(count($vetorPublisherPorUtilizacao)>0) {
    $where_opr_venda_lan = " AND ( CASE ";
    $where_opr_venda_lan_negativa = " AND ( CASE ";
    $where_opr_utilizacao_lan = " AND ( CASE ";
    foreach ($vetorPublisherPorUtilizacao as $opr_codigo => $opr_data_inicio_contabilizacao_utilizacao){ 
        //echo "ID: ".$opr_codigo." => DATA: [".substr($opr_data_inicio_contabilizacao_utilizacao,0,19)."]<br>";
		$opr_id = $opr_codigo;
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

$verificaOpr = (count($vetorPublisherPorUtilizacao)>0)?" and vgm_opr_codigo = $opr_id":" and vgm_opr_codigo not in(124,137,143,147,148,13)";

echo PHP_EOL.str_repeat("=", 80).PHP_EOL."Pr�-Processamento para fechamento financeiro (".date("Y-m-d H:i:s").")".PHP_EOL.PHP_EOL;

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
		".$verificaOpr."
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
		".$verificaOpr."
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
		".$verificaOpr."
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
		".$verificaOpr."
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
		sum((CASE
                    WHEN vgm_opr_codigo = 124 and (vgm_valor = 4 or vgm_valor = 5) THEN 4.49
					WHEN vgm_opr_codigo = 124 and vgm_valor = 14 THEN 13.99
					WHEN vgm_opr_codigo = 124 and vgm_valor = 21 THEN 20.99
					WHEN vgm_opr_codigo = 124 and vgm_valor = 45 THEN 44.99
					WHEN vgm_opr_codigo = 124 and vgm_valor = 88 THEN 87.99
					WHEN vgm_opr_codigo = 124 and vgm_valor = 210 THEN 209.99
                    ELSE vgm_valor
                END) * vgm.vgm_qtde) as total 
	from tb_dist_venda_games vg 
		inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
	where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' ".$where_opr_venda_lan."
		and vg.vg_data_inclusao >= (select min(fp_date) from financial_processing inner join operadoras on opr_codigo=fp_publisher where fp_freeze=0 and opr_status='1')
		and vg.vg_ultimo_status_obs not like '%Pagamento via API%'
		".$verificaOpr."
	group by dia,
		publisher) 
		
	union all 
	(select 
		'L' as canal,
		date_trunc('day', vg.vg_data_inclusao) as dia,
		vgm_opr_codigo as publisher,
		sum(vgm.vgm_qtde) as n, 
                count(distinct('".$ARRAY_CONCATENA_ID_VENDA['gamer']."'  || to_char(vg.vg_data_inclusao,'YYMMDD') || lpad(vg.vg_id::text , 8, '0'))) as total_order,
		sum((CASE
                    WHEN vgm_opr_codigo = 124 and (vgm_valor = 4 or vgm_valor = 5) THEN 4.49
					WHEN vgm_opr_codigo = 124 and vgm_valor = 14 THEN 13.99
					WHEN vgm_opr_codigo = 124 and vgm_valor = 21 THEN 20.99
					WHEN vgm_opr_codigo = 124 and vgm_valor = 45 THEN 44.99
					WHEN vgm_opr_codigo = 124 and vgm_valor = 88 THEN 87.99
					WHEN vgm_opr_codigo = 124 and vgm_valor = 210 THEN 209.99
                    ELSE vgm_valor
                END) * vgm.vgm_qtde) as total 
	from tb_dist_venda_games vg 
		inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
	where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' ".$where_opr_venda_lan."
		and vg.vg_data_inclusao >= (select min(fp_date) from financial_processing inner join operadoras on opr_codigo=fp_publisher where fp_freeze=0 and opr_status='1')
		and vg.vg_ultimo_status_obs like '%Pagamento via API%'
		".$verificaOpr."
	group by dia,
		publisher)	
		
	";
//Contabilizando vendas por utiliza��o de PINs Publisher
if(count($vetorPublisherPorUtilizacao)>0) {
    $sql .= "
        
union all

        (select 
                'L' as canal,
                date_trunc('day', pih_data) as dia,
                vgm_opr_codigo as publisher,
                count(*) as n, 
                count(distinct('".$ARRAY_CONCATENA_ID_VENDA['pdv']."'  || to_char(vg.vg_data_inclusao,'YYMMDD') || lpad(vg.vg_id::text , 8, '0'))) as total_order,
                sum(CASE
                    WHEN vgm_opr_codigo = 124 and (vgm_valor = 4 or vgm_valor = 5) THEN 4.49
					WHEN vgm_opr_codigo = 124 and vgm_valor = 14 THEN 13.99
					WHEN vgm_opr_codigo = 124 and vgm_valor = 21 THEN 20.99
					WHEN vgm_opr_codigo = 124 and vgm_valor = 45 THEN 44.99
					WHEN vgm_opr_codigo = 124 and vgm_valor = 88 THEN 87.99
					WHEN vgm_opr_codigo = 124 and vgm_valor = 210 THEN 209.99
                    ELSE vgm_valor
                END) as total
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
			 ".$verificaOpr."
        group by dia,
                publisher)  
				
            ";
}//end if(count($vetorPublisherPorUtilizacao)>0)
 /*
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
			 and vg.vg_ultimo_status_obs like '%Pagamento via API Barramento%'
             ".$where_opr_venda_lan_negativa."
             and pih_data >= (select min(fp_date) from financial_processing inner join operadoras on opr_codigo=fp_publisher where fp_freeze=0 and opr_status='1')
             ".$where_opr_utilizacao_lan."
        group by dia,
                publisher) 		

*/
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
		".$verificaOpr."
	group by dia,
		publisher)            
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
//echo $sql.PHP_EOL.PHP_EOL;
//exit; 
$rs = SQLexecuteQuery($sql);
$n_updates = pg_num_rows($rs);
echo "Encontrado".(($n_updates>1)?"s":"")." : ".$n_updates." Registro".(($n_updates>1)?"s":"")." para serem verifidos e atualizados".PHP_EOL.PHP_EOL;

if(!$rs || pg_num_rows($rs) == 0) {
        $msg = "Nenhum usu�rios selecionado
";
} else {
	while($rs_row = pg_fetch_array($rs)) {
            $sql = "
                select * 
                from financial_processing 
                where fp_channel = '".$rs_row['canal']."'
                    and fp_publisher = ".$rs_row['publisher']."
                    and fp_date = '".$rs_row['dia']."' ;";
            //echo($sql);
            $rs_existe = SQLexecuteQuery($sql);
            $n_existente  = pg_num_rows($rs_existe);
            //Verificando se existe o registro
            if($n_existente == 1) {
					
                $sql = "
                    update financial_processing 
                    set fp_number = ".$rs_row['n'].",
                        fp_total = ".$rs_row['total'].",
                        fp_total_order = ".$rs_row['total_order']."
                    where fp_channel = '".$rs_row['canal']."'
                        and fp_publisher = ".$rs_row['publisher']."
                        and fp_date = '".$rs_row['dia']."' 
                        and fp_freeze = 0;";
                echo "SQL do Update: ".$sql.PHP_EOL;
                $ret = SQLexecuteQuery($sql);
                if(!$ret) echo "Erro ao Atualizar o Per�odo [".$rs_row['dia']."], Publisher [".$rs_row['publisher']."] e Canal [".$rs_row['canal']."]".PHP_EOL;
            }//end if($n_existente > 0) 
            //Verificando se existe erro nos dados
            elseif($n_existente > 1) {
                echo "ERRO: EXISTEM MAIS DE UM REGISTRO PARA O PERIODO, CANAL E PUBLISHER **********************************************************************************".PHP_EOL;
            }//end elseif($n_existente > 1)
            //Inserindo por n�o existir o registro
            else {
                $sql = "
                    insert into financial_processing 
                    (fp_channel, fp_publisher, fp_date, fp_number, fp_total, fp_total_order)
                    values
                    ('".$rs_row['canal']."', ".$rs_row['publisher'].", '".$rs_row['dia']."', ".$rs_row['n'].", ".$rs_row['total'].", ".$rs_row['total_order'].");";
                echo "SQL do Insert: ".$sql.PHP_EOL;
                $ret = SQLexecuteQuery($sql);
                if(!$ret) echo "Erro ao Inserir o Per�odo [".$rs_row['dia']."], Publisher [".$rs_row['publisher']."] e Canal [".$rs_row['canal']."]".PHP_EOL;
            }//end else do elseif($n_existente > 1)
	}//end while
}//end else do if(!$rs || pg_num_rows($rs) == 0)

/*********************************************************************** 
 * *****   Marcando os per�odos a quais empresas pertencem
 * *****   E se houve movimenta��o de nacional para internacional
 * *****   INICIO
 ***********************************************************************/
$sql = "SELECT opr_nome, opr_codigo, opr_vinculo_empresa, substring(opr_data_inicio_operacoes::varchar from 1 for 19) as data_inicio, opr_internacional_alicota FROM operadoras;";
$rs_publishers = SQLexecuteQuery($sql);
$n_publishers = pg_num_rows($rs_publishers);
echo PHP_EOL.$sql.PHP_EOL.PHP_EOL."N�mero total de Publisher Selecionados ".$n_publishers.PHP_EOL.PHP_EOL;

//Verificando se retornou ao menos 1 Publisher
if($n_publishers > 0) {
    while($rs_publishers_row = pg_fetch_array($rs_publishers)) {

        //Verificando se Publisher est� vinculado � EPP Pagto
        if($rs_publishers_row['opr_vinculo_empresa'] == 0) {
            echo "Publisher ".$rs_publishers_row['opr_nome']." est� vinculado � E-Prepag Pagamentos".PHP_EOL;
            $sql = "UPDATE financial_processing SET fp_company = ".$rs_publishers_row['opr_vinculo_empresa']." WHERE fp_publisher = ".$rs_publishers_row['opr_codigo'].";";
            echo $sql.PHP_EOL;
            $rs_update = SQLexecuteQuery($sql);
            if(!$rs_update) echo "Erro ao Atualizar o Publisher [".$rs_publishers_row['opr_codigo']."] que est� vinculado a Empresa [".$rs_publishers_row['opr_vinculo_empresa']."]".PHP_EOL;
            //Publisher Internacional
            if($rs_publishers_row['opr_internacional_alicota'] > 0) {
                $sql = "UPDATE financial_processing SET fp_nationality = 1 WHERE fp_publisher = ".$rs_publishers_row['opr_codigo'].";";
                echo "*** Todos tempos Internacional: ".PHP_EOL.$sql.PHP_EOL;
                $rs_updateTrocaNacionalInternacional = SQLexecuteQuery($sql);
                if(!$rs_updateTrocaNacionalInternacional) echo "Erro ao Atualizar ao Marcar a Nacionalidade do Publisher [".$rs_publishers_row['opr_codigo']."] para Todos os Tempos".PHP_EOL;
            }//end if($rs_publishers_row['opr_internacional_alicota'] > 0)
            //Publisher Nacional
            else {
                $sql = "UPDATE financial_processing SET fp_nationality = 0 WHERE fp_publisher = ".$rs_publishers_row['opr_codigo'].";";
                echo "*** Todos tempos Nacional: ".PHP_EOL.$sql.PHP_EOL;
                $rs_updateTrocaNacionalInternacional = SQLexecuteQuery($sql);
                if(!$rs_updateTrocaNacionalInternacional) echo "Erro ao Atualizar ao Marcar a Nacionalidade do Publisher [".$rs_publishers_row['opr_codigo']."] para Todos os Tempos".PHP_EOL;
            }//end else do if($rs_publishers_row['opr_internacional_alicota'] > 0)
        }//end if($rs_publishers_row['opr_vinculo_empresa'] == 0)
        
        //Para o Publisher que est� vinculado � EPP ADM
        else {
            echo "Publisher ".$rs_publishers_row['opr_nome']." est� vinculado � E-Prepag Administradora".(!empty($rs_publishers_row['data_inicio'])?" desde [".$rs_publishers_row['data_inicio']."]":"").PHP_EOL;
            if(!empty($rs_publishers_row['data_inicio'])) {
                $sql = "UPDATE financial_processing SET fp_company = ".$rs_publishers_row['opr_vinculo_empresa']." WHERE fp_publisher = ".$rs_publishers_row['opr_codigo']." AND fp_date >='".$rs_publishers_row['data_inicio']."';";
                echo $sql.PHP_EOL;
                $rs_update = SQLexecuteQuery($sql);
                if(!$rs_update) echo "Erro ao Atualizar o Publisher [".$rs_publishers_row['opr_codigo']."] que est� vinculado a Empresa [".$rs_publishers_row['opr_vinculo_empresa']."] desde [".$rs_publishers_row['data_inicio']."]".PHP_EOL;
                $sql = "UPDATE financial_processing SET fp_company = 0 WHERE fp_publisher = ".$rs_publishers_row['opr_codigo']." AND fp_date <'".$rs_publishers_row['data_inicio']."';";
                echo $sql.PHP_EOL;
                $rs_update = SQLexecuteQuery($sql);
                if(!$rs_update) echo "Erro ao Atualizar o Publisher [".$rs_publishers_row['opr_codigo']."] era vinculado a Empresa [0] anterior � [".$rs_publishers_row['data_inicio']."]".PHP_EOL;
            }//end if(!empty($rs_publishers_row['data_inicio']))
            else {
                $sql = "UPDATE financial_processing SET fp_company = ".$rs_publishers_row['opr_vinculo_empresa']." WHERE fp_publisher = ".$rs_publishers_row['opr_codigo'].";";
                echo $sql.PHP_EOL;
                $rs_update = SQLexecuteQuery($sql);
                if(!$rs_update) echo "Erro ao Atualizar o Publisher [".$rs_publishers_row['opr_codigo']."] que est� vinculado a Empresa [".$rs_publishers_row['opr_vinculo_empresa']."]".PHP_EOL;
            }//end else do if(!empty($rs_publishers_row['data_inicio']))
            $sql = "SELECT substring(otni_data::varchar from 1 for 19) as data_transferencia,otni_origem,otni_destino FROM operadoras_troca_nacional_internacional WHERE opr_codigo = ".$rs_publishers_row['opr_codigo']." ORDER BY otni_data;";
            echo "Verificando se teve movimenta��o de Internacional para nacional e vice-versa para o Publisher [".$rs_publishers_row['opr_codigo']."]:".PHP_EOL.$sql.PHP_EOL;
            $rs_TrocaNacionalInternacional = SQLexecuteQuery($sql);
            if(pg_num_rows($rs_TrocaNacionalInternacional) > 0) {
                echo "** Publisher [".$rs_publishers_row['opr_codigo']."] POSSUI TROCA ".PHP_EOL;
                $data_anterior = null;
                while($rs_TrocaNacionalInternacional_row = pg_fetch_array($rs_TrocaNacionalInternacional)) {
                    echo "*** Dados coletados: ".$rs_TrocaNacionalInternacional_row['data_transferencia']." Origem = ".$rs_TrocaNacionalInternacional_row['otni_origem']." Destino = ".$rs_TrocaNacionalInternacional_row['otni_destino'].PHP_EOL;
                    if($rs_TrocaNacionalInternacional_row['otni_origem'] == 0 && $rs_TrocaNacionalInternacional_row['otni_destino'] == 1) {
                        echo "**** Mudou de Nacional para Internacional".PHP_EOL;
                        if(is_null($data_anterior)) {
                            $sql = "UPDATE financial_processing SET fp_nationality = 0 WHERE fp_publisher = ".$rs_publishers_row['opr_codigo']." AND fp_date <'".$rs_TrocaNacionalInternacional_row['data_transferencia']."';";
                            echo "***** Parte 01 => In�cio dos tempo at� a PRIMEIRA TROCA: ".PHP_EOL.$sql.PHP_EOL;
                            $rs_updateTrocaNacionalInternacional = SQLexecuteQuery($sql);
                            if(!$rs_updateTrocaNacionalInternacional) echo "Erro ao Atualizar a Troca de Nacionalidade do Publisher [".$rs_publishers_row['opr_codigo']."] para In�cio dos tempo at� a PRIMEIRA TROCA".PHP_EOL;
                            $sql = "UPDATE financial_processing SET fp_nationality = 1 WHERE fp_publisher = ".$rs_publishers_row['opr_codigo']." AND fp_date >='".$rs_TrocaNacionalInternacional_row['data_transferencia']."';";
                            echo "***** Parte 02 => De ".$rs_TrocaNacionalInternacional_row['data_transferencia']." at� o Final dos tempos para a PRIMEIRA TROCA: ".PHP_EOL.$sql.PHP_EOL;
                            $rs_updateTrocaNacionalInternacional = SQLexecuteQuery($sql);
                            if(!$rs_updateTrocaNacionalInternacional) echo "Erro ao Atualizar a Troca de Nacionalidade do Publisher [".$rs_publishers_row['opr_codigo']."] para de ".$rs_TrocaNacionalInternacional_row['data_transferencia']." at� o Final dos tempos para a PRIMEIRA TROCA".PHP_EOL;
                        }//end de if(is_null($data_anterior))
                        else {
                            $sql = "UPDATE financial_processing SET fp_nationality = 0 WHERE fp_publisher = ".$rs_publishers_row['opr_codigo']." AND fp_date <'".$rs_TrocaNacionalInternacional_row['data_transferencia']."' AND fp_date >= '".$data_anterior."';";
                            echo "***** Parte 01 => De ".$data_anterior." at� a PR�XIMA TROCA: ".PHP_EOL.$sql.PHP_EOL;
                            $rs_updateTrocaNacionalInternacional = SQLexecuteQuery($sql);
                            if(!$rs_updateTrocaNacionalInternacional) echo "Erro ao Atualizar a Troca de Nacionalidade do Publisher [".$rs_publishers_row['opr_codigo']."] para De ".$data_anterior." at� a PR�XIMA TROCA".PHP_EOL;
                            $sql = "UPDATE financial_processing SET fp_nationality = 1 WHERE fp_publisher = ".$rs_publishers_row['opr_codigo']." AND fp_date >='".$rs_TrocaNacionalInternacional_row['data_transferencia']."';";
                            echo "***** Parte 02 => De ".$rs_TrocaNacionalInternacional_row['data_transferencia']." at� o Final dos tempos para a PR�XIMA TROCA: ".PHP_EOL.$sql.PHP_EOL;
                            $rs_updateTrocaNacionalInternacional = SQLexecuteQuery($sql);
                            if(!$rs_updateTrocaNacionalInternacional) echo "Erro ao Atualizar a Troca de Nacionalidade do Publisher [".$rs_publishers_row['opr_codigo']."] para De ".$rs_TrocaNacionalInternacional_row['data_transferencia']." at� o Final dos tempos para a PR�XIMA TROCA".PHP_EOL;
                        }//end else do if(is_null($data_anterior))
                    }//end if($rs_TrocaNacionalInternacional_row['otni_origem'] == 0 && $rs_TrocaNacionalInternacional_row['otni_destino'])
                    elseif($rs_TrocaNacionalInternacional_row['otni_origem'] == 1 && $rs_TrocaNacionalInternacional_row['otni_destino'] == 0) {
                        echo "**** Mudou de Internacional para Nacional".PHP_EOL;
                        if(is_null($data_anterior)) {
                            $sql = "UPDATE financial_processing SET fp_nationality = 1 WHERE fp_publisher = ".$rs_publishers_row['opr_codigo']." AND fp_date <'".$rs_TrocaNacionalInternacional_row['data_transferencia']."';";
                            echo "***** Parte 01 => In�cio dos tempo at� a PRIMEIRA TROCA: ".PHP_EOL.$sql.PHP_EOL;
                            $rs_updateTrocaNacionalInternacional = SQLexecuteQuery($sql);
                            if(!$rs_updateTrocaNacionalInternacional) echo "Erro ao Atualizar a Troca de Nacionalidade do Publisher [".$rs_publishers_row['opr_codigo']."] para In�cio dos tempo at� a PRIMEIRA TROCA".PHP_EOL;
                            $sql = "UPDATE financial_processing SET fp_nationality = 0 WHERE fp_publisher = ".$rs_publishers_row['opr_codigo']." AND fp_date >='".$rs_TrocaNacionalInternacional_row['data_transferencia']."';";
                            echo "***** Parte 02 => De ".$rs_TrocaNacionalInternacional_row['data_transferencia']." at� o Final dos tempos para a PRIMEIRA TROCA: ".PHP_EOL.$sql.PHP_EOL;
                            $rs_updateTrocaNacionalInternacional = SQLexecuteQuery($sql);
                            if(!$rs_updateTrocaNacionalInternacional) echo "Erro ao Atualizar a Troca de Nacionalidade do Publisher [".$rs_publishers_row['opr_codigo']."] para de ".$rs_TrocaNacionalInternacional_row['data_transferencia']." at� o Final dos tempos para a PRIMEIRA TROCA".PHP_EOL;
                        }//end de if(is_null($data_anterior))
                        else {
                            $sql = "UPDATE financial_processing SET fp_nationality = 1 WHERE fp_publisher = ".$rs_publishers_row['opr_codigo']." AND fp_date <'".$rs_TrocaNacionalInternacional_row['data_transferencia']."' AND fp_date >= '".$data_anterior."';";
                            echo "***** Parte 01 => De ".$data_anterior." at� a PR�XIMA TROCA: ".PHP_EOL.$sql.PHP_EOL;
                            $rs_updateTrocaNacionalInternacional = SQLexecuteQuery($sql);
                            if(!$rs_updateTrocaNacionalInternacional) echo "Erro ao Atualizar a Troca de Nacionalidade do Publisher [".$rs_publishers_row['opr_codigo']."] para De ".$data_anterior." at� a PR�XIMA TROCA".PHP_EOL;
                            $sql = "UPDATE financial_processing SET fp_nationality = 0 WHERE fp_publisher = ".$rs_publishers_row['opr_codigo']." AND fp_date >='".$rs_TrocaNacionalInternacional_row['data_transferencia']."';";
                            echo "***** Parte 02 => De ".$rs_TrocaNacionalInternacional_row['data_transferencia']." at� o Final dos tempos para a PR�XIMA TROCA: ".PHP_EOL.$sql.PHP_EOL;
                            $rs_updateTrocaNacionalInternacional = SQLexecuteQuery($sql);
                            if(!$rs_updateTrocaNacionalInternacional) echo "Erro ao Atualizar a Troca de Nacionalidade do Publisher [".$rs_publishers_row['opr_codigo']."] para De ".$rs_TrocaNacionalInternacional_row['data_transferencia']." at� o Final dos tempos para a PR�XIMA TROCA".PHP_EOL;
                        }//end else do if(is_null($data_anterior))
                    }//end do elseif($rs_TrocaNacionalInternacional_row['otni_origem'] == 0 && $rs_TrocaNacionalInternacional_row['otni_destino'])
                    else {
                        echo "**** Mudan�a com dire��o n�o Identificada".PHP_EOL;
                    }//end do else do do elseif($rs_TrocaNacionalInternacional_row['otni_origem'] == 0 && $rs_TrocaNacionalInternacional_row['otni_destino'])
                    $data_anterior = $rs_TrocaNacionalInternacional_row['data_transferencia'];
                }//end while
            }//end if(pg_num_rows($rs_TrocaNacionalInternacional) > 0)
            else {
                echo "** Publisher [".$rs_publishers_row['opr_codigo']."] N�O Possui Troca ".PHP_EOL;
                //Publisher Internacional
                if($rs_publishers_row['opr_internacional_alicota'] > 0) {
                    $sql = "UPDATE financial_processing SET fp_nationality = 1 WHERE fp_publisher = ".$rs_publishers_row['opr_codigo'].";";
                    echo "*** Todos tempos Internacional: ".PHP_EOL.$sql.PHP_EOL;
                    $rs_updateTrocaNacionalInternacional = SQLexecuteQuery($sql);
                    if(!$rs_updateTrocaNacionalInternacional) echo "Erro ao Atualizar ao Marcar a Nacionalidade do Publisher [".$rs_publishers_row['opr_codigo']."] para Todos os Tempos".PHP_EOL;
                }//end if($rs_publishers_row['opr_internacional_alicota'] > 0)
                //Publisher Nacional
                else {
                    $sql = "UPDATE financial_processing SET fp_nationality = 0 WHERE fp_publisher = ".$rs_publishers_row['opr_codigo'].";";
                    echo "*** Todos tempos Nacional: ".PHP_EOL.$sql.PHP_EOL;
                    $rs_updateTrocaNacionalInternacional = SQLexecuteQuery($sql);
                    if(!$rs_updateTrocaNacionalInternacional) echo "Erro ao Atualizar ao Marcar a Nacionalidade do Publisher [".$rs_publishers_row['opr_codigo']."] para Todos os Tempos".PHP_EOL;
                }//end else do if($rs_publishers_row['opr_internacional_alicota'] > 0)
            }//end else do if(pg_num_rows($rs_TrocaNacionalInternacional) > 0)
        }//end else do if($rs_publishers_row['opr_vinculo_empresa'] == 0)
        echo str_repeat('-',80).PHP_EOL;
    }//end while
}//end if($n_publishers > 0)
else {
    echo "ERRO: Nenhum Publisher foi selecionado para v�nculo entre empresas".PHP_EOL.PHP_EOL;
}//end else do if($n_publishers > 0)
/*********************************************************************** 
 * *****   Marcando os per�odos a quais empresas pertencem
 * *****   FINAL
 ***********************************************************************/

echo str_repeat("_", 80) .PHP_EOL."Elapsed time: ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.').PHP_EOL.str_repeat("=", 80) .PHP_EOL;
?>