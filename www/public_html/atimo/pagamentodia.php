<?php
error_reporting(E_ALL); 
ini_set("display_errors", 1); 

require_once "/www/includes/main.php";

$time_start_stats = getmicrotime();

echo PHP_EOL.str_repeat("=", 80).PHP_EOL."Processa Conciliação Bancaria Pagamento no Dia (".date("Y-m-d H:i:s").")".PHP_EOL.PHP_EOL;

$vetor_aux = array();

//Capturando Dados LANs
$sql = "
select 
	TO_DATE(TO_CHAR(EXTRACT(YEAR FROM pgt.datainicio),'9999')||'-'||TO_CHAR(EXTRACT(MONTH FROM pgt.datainicio),'99')||'-'||TO_CHAR(EXTRACT(DAY FROM pgt.datainicio),'99'),'YYYY-MM-DD') as iday, 
	count(*) as n, 
	sum(total)/100 as sum_total,
	pgt.iforma
from tb_pag_compras pgt 
where total>0 
	and TO_DATE(TO_CHAR(EXTRACT(YEAR FROM pgt.datainicio),'9999')||'-'||TO_CHAR(EXTRACT(MONTH FROM pgt.datainicio),'9999')||'-'||TO_CHAR(EXTRACT(DAY FROM pgt.datainicio),'99'),'YYYY-MM-DD') >= ('".date("Y-m-d")." 00:00:00'::timestamp - '1 year'::interval)
	and pgt.tipo_cliente='LR' 
	and pgt.status=3 
group by TO_DATE(TO_CHAR(EXTRACT(YEAR FROM pgt.datainicio),'9999')||'-'||TO_CHAR(EXTRACT(MONTH FROM pgt.datainicio),'99')||'-'||TO_CHAR(EXTRACT(DAY FROM pgt.datainicio),'99'),'YYYY-MM-DD'), pgt.iforma
";

$rs_lans = SQLexecuteQuery($sql);
if($rs_lans && pg_num_rows($rs_lans) > 0){
	while ($rs_lans_row = pg_fetch_array($rs_lans)) {
		$vetor_aux[$rs_lans_row['iday']][$rs_lans_row['iforma']]['LR']['TOTAL'] = $rs_lans_row['sum_total'];
		$vetor_aux[$rs_lans_row['iday']][$rs_lans_row['iforma']]['LR']['QTDE'] = $rs_lans_row['n'];
		if($rs_lans_row['iforma'] == '5') {
			$sql = "select sum(dep_valor) as total,
						TO_DATE(TO_CHAR(EXTRACT(YEAR FROM dep_data),'9999')||'-'||TO_CHAR(EXTRACT(MONTH FROM dep_data),'99')||'-'||TO_CHAR(EXTRACT(DAY FROM dep_data),'99'),'YYYY-MM-DD') as iday
					from depositos_pendentes, bancos_financeiros 
					where (dep_banco = bco_codigo) and (bco_rpp = 1) and (dep_data >= '".$rs_lans_row['iday']."' and dep_data <= '".$rs_lans_row['iday']."') and dep_aprovado = 1 and dep_banco = '237' and dep_conta != '4707-4' and dep_conta != '0030265-1'
					group by iday";
			//echo $sql.PHP_EOL;
			$rs_deposito = SQLexecuteQuery($sql);
			if($rs_deposito && $rs_deposito_row = pg_fetch_array($rs_deposito)) {
				$vetor_aux[$rs_lans_row['iday']][$rs_lans_row['iforma']]['DEP'] = $rs_deposito_row['total'];
			}//end if($rs_deposito && $rs_deposito_row = pg_fetch_array($rs_deposito))
		}//end if($rs_lans_row['iforma'] == '5') 
	}//end while
}//end if($rs_lans && pg_num_rows($rs_lans) > 0)

//Capturando Dados Gamers
$sql = "
select 
	TO_DATE(TO_CHAR(EXTRACT(YEAR FROM pgt.datainicio),'9999')||'-'||TO_CHAR(EXTRACT(MONTH FROM pgt.datainicio),'99')||'-'||TO_CHAR(EXTRACT(DAY FROM pgt.datainicio),'99'),'YYYY-MM-DD') as iday, 
	count(*) as n, 
	sum(total)/100 as sum_total,
	-- sum((total/100) - taxas) as sum_total,
	pgt.iforma
from tb_pag_compras pgt 
where total>0 
	and TO_DATE(TO_CHAR(EXTRACT(YEAR FROM pgt.datainicio),'9999')||'-'||TO_CHAR(EXTRACT(MONTH FROM pgt.datainicio),'9999')||'-'||TO_CHAR(EXTRACT(DAY FROM pgt.datainicio),'99'),'YYYY-MM-DD') >= ('".date("Y-m-d")." 00:00:00'::timestamp - '1 year'::interval) 
	and pgt.tipo_cliente='M'
	and pgt.status=3 
group by TO_DATE(TO_CHAR(EXTRACT(YEAR FROM pgt.datainicio),'9999')||'-'||TO_CHAR(EXTRACT(MONTH FROM pgt.datainicio),'99')||'-'||TO_CHAR(EXTRACT(DAY FROM pgt.datainicio),'99'),'YYYY-MM-DD'), pgt.iforma
";
$rs_gamers = SQLexecuteQuery($sql);
if($rs_gamers && pg_num_rows($rs_gamers) > 0){
	while ($rs_gamers_row = pg_fetch_array($rs_gamers)) {
		$vetor_aux[$rs_gamers_row['iday']][$rs_gamers_row['iforma']]['M']['TOTAL'] = $rs_gamers_row['sum_total'];
		$vetor_aux[$rs_gamers_row['iday']][$rs_gamers_row['iforma']]['M']['QTDE'] = $rs_gamers_row['n'];
	}//end while
}//end if($rs_gamers && pg_num_rows($rs_gamers) > 0)

ksort($vetor_aux);
//print_r($vetor_aux);

foreach ($vetor_aux as $key => $value){
	//echo $key . "=" . $vetor_aux[$key] . "".PHP_EOL;
	foreach ($vetor_aux[$key] as $key2 => $value2) {
		//echo $key2 . "= M: " . ($vetor_aux[$key][$key2]['M']['TOTAL']*1) . " LR: ".($vetor_aux[$key][$key2]['LR']['TOTAL']*1).PHP_EOL;
		$sql = "select * from relfin_conciliacao_bancaria where rfcb_data_registro = '".$key."' and rfcb_tipo_pagamento = '".$key2."';";
		//echo $sql.PHP_EOL;
		$rs_verify = SQLexecuteQuery($sql);
		if(pg_num_rows($rs_verify) > 0) {
			$rs_verify_row = pg_fetch_array($rs_verify);
			if($rs_verify_row['rfcb_venda_gamer'] <> ($vetor_aux[$key][$key2]['M']['TOTAL']*1) || $rs_verify_row['rfcb_venda_lans'] <> ($vetor_aux[$key][$key2]['LR']['TOTAL']*1) || $rs_verify_row['rfcb_valor_dep_brad'] <> ($vetor_aux[$key][$key2]['DEP']*1)) {
				$sql = "update relfin_conciliacao_bancaria set rfcb_venda_gamer = ".($vetor_aux[$key][$key2]['M']['TOTAL']*1).", rfcb_venda_lans = ".($vetor_aux[$key][$key2]['LR']['TOTAL']*1).", rfcb_data_atualizacao = NOW(), rfcb_qtde_gamer =  ".($vetor_aux[$key][$key2]['M']['QTDE']*1).", rfcb_qtde_lans = ".($vetor_aux[$key][$key2]['LR']['QTDE']*1).", rfcb_valor_dep_brad = ".($vetor_aux[$key][$key2]['DEP']*1)." where rfcb_id = ".$rs_verify_row['rfcb_id']." and rfcb_data_registro = '".$key."' and rfcb_tipo_pagamento = '".$key2."';";
				echo "UPDATE ".$key." PAGTO ".$key2." : ".$sql.PHP_EOL;
				$rs_update = SQLexecuteQuery($sql);
				if($rs_update) {
					echo "UPDATE EXECUTADO OK ".PHP_EOL;
				}//if($rs_update) 
			}//end if($rs_verify_row['rfcb_venda_gamer'] <> ($vetor_aux[$key][$key2]['M']['TOTAL']*1) || $rs_verify_row['rfcb_venda_lans'] <> ($vetor_aux[$key][$key2]['LR']['TOTAL']*1)) 
			else {
				echo "(".$key.") - (".$key2.") => Não é necessário atualizar o registro!".PHP_EOL;
			}
		}//end if(pg_num_rows($rs_verify) > 0)
		else {
			$sql = "insert into relfin_conciliacao_bancaria (rfcb_venda_gamer, rfcb_venda_lans, rfcb_data_registro, rfcb_data_inclusao, rfcb_tipo_pagamento, rfcb_qtde_gamer, rfcb_qtde_lans, rfcb_valor_dep_brad) values (".($vetor_aux[$key][$key2]['M']['TOTAL']*1).", ".($vetor_aux[$key][$key2]['LR']['TOTAL']*1).", '".$key."', NOW(),'".$key2."', ".($vetor_aux[$key][$key2]['M']['QTDE']*1).", ".($vetor_aux[$key][$key2]['LR']['QTDE']*1).", ".($vetor_aux[$key][$key2]['DEP']*1).");";
			echo "INSERT ".$key." PAGTO ".$key2.": ".$sql.PHP_EOL;
			$rs_insert = SQLexecuteQuery($sql);
			if($rs_insert) {
				echo "INSERT EXECUTADO OK ".PHP_EOL;
			}//if($rs_insert) 
		}
	}//end foreach ($vetor_aux[$key] as $key2 => $value2) 
}//end foreach ($vetor_aux as $key => $value)

echo PHP_EOL.str_repeat("_", 80) . PHP_EOL."Elapsed time: ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.').PHP_EOL.str_repeat("=", 80) . PHP_EOL;

//Fechando Conexão
pg_close($connid);

?>