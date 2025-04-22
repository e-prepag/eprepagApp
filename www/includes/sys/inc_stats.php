<?php
set_time_limit ( 30000 ) ;

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
		var DI=document.sss; 
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

/*	function get_day_of_week($date1) {

		$dia_semana = "???";
		$dia_ingles = date("w", strtotime($date1));
//echo "$date1 -> $dia_ingles";
//		if(date('H') >= 6)$cpa="Bom dia";
//		if(date('H') >= 13)$cpa="Boa tarde";
//		if(date('H') >= 18)$cpa="Boa noite";
			//echo $cpa;				
		switch($dia_ingles) 
		{
			case "1": $dia_semana = LANG_SITE_DAY_OF_WEEK_MONDAY; break; 
			case "2": $dia_semana = LANG_SITE_DAY_OF_WEEK_TUESDAY; break; 
			case "3": $dia_semana = LANG_SITE_DAY_OF_WEEK_WEDNESDAY; break; 
			case "4": $dia_semana = LANG_SITE_DAY_OF_WEEK_THURSDAY; break; 
			case "5": $dia_semana = LANG_SITE_DAY_OF_WEEK_FRIDAY; break; 
			case "6": $dia_semana = LANG_SITE_DAY_OF_WEEK_SATURDAY; break; 
			case "0": $dia_semana = LANG_SITE_DAY_OF_WEEK_SUNDAY; break; 
		}
		return $dia_semana;
	}
*/
	function get_day_of_week_db($iday_of_week) {

		$dia_semana = "???";
		switch($iday_of_week) 
		{
			case 1: $dia_semana = LANG_SITE_DAY_OF_WEEK_MONDAY; break; 
			case 2: $dia_semana = LANG_SITE_DAY_OF_WEEK_TUESDAY; break; 
			case 3: $dia_semana = LANG_SITE_DAY_OF_WEEK_WEDNESDAY; break; 
			case 4: $dia_semana = LANG_SITE_DAY_OF_WEEK_THURSDAY; break; 
			case 5: $dia_semana = LANG_SITE_DAY_OF_WEEK_FRIDAY; break; 
			case 6: $dia_semana = LANG_SITE_DAY_OF_WEEK_SATURDAY; break; 
			case 0: $dia_semana = LANG_SITE_DAY_OF_WEEK_SUNDAY; break; 
		}
		return $dia_semana;
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


/*
	$query_channel = 
		"P"	= "POS"
		"M"	= "Money"
		"E" = "Money Express"
		"L" = "LH Money"
		"X" = "LH Money Express"
		"C" = "Cartoes"
	$query_type = 
		Totais de Vendas			->	"totais_de_vendas"
		Datas Limites no BD			->	"datas_limites_no_bd"
		Por dia						->	"por_dia"
		Por Publisher				->	"por_publisher"
		".LANG_STATISTICS_FOR_STATE."					->	"por_estado"
		Por Cidade					->	"por_cidade"
		Por Tipo de Estabelecimento	->	"por_tipo_de_estabelecimento"
		Por Estabelecimento			->	"por_estabelecimento"
		Por Usuario					->	"por_usuario"
										                            
		Por Hora do Dia				->	"por_hora_do_dia"
		".LANG_STATISTICS_FOR_WEEK_DAY."			->	"por_dia_da_semana"

			switch($query_type) {
				case "totais_de_vendas": 
					break;
				case "datas_limites_no_bd": 
					break;
				case "por_dia":  
					break;
				case "por_publisher":  
					break;
				case "por_estado":  
					break;
				case "por_cidade":  
					break;
				case "por_tipo_de_estabelecimento": 
					break;
				case "por_estabelecimento": 
					break;
				case "por_usuario":  
					break;
				// ===================================   
				case "por_mes": 
					break;
				case "por_hora_do_dia": 
					break;
				case "por_dia_da_semana": 
					break;
			}


*/
function get_sql_query($query_channel, $query_type, $extra_where, $smode) {
	global $where_operadora_pos, $where_operadora_cartoes, $PAGAMENTO_PIN_EPREPAG_NUMERIC;

	$where_mode_data = "vg.vg_data_inclusao";	// default
	if($smode=='S') $where_mode_data = "vg.vg_data_concilia";
//	if($dd_mode=='S') $where_mode_data = "COALESCE(vg.vg_data_concilia, (select datacompra from tb_pag_compras p where p.idvenda=vg.vg_id ), vg.vg_data_inclusao)";
//	if($dd_mode=='S') $where_mode_data = "COALESCE(vg.vg_data_concilia, (select datacompra from tb_pag_compras p where p.idvenda=vg.vg_id )usao)";

	$sql = "";
	switch($query_channel) {
		case "P":	// POS ========================================================================================
	
			switch($query_type) {
				case "totais_de_vendas": 
					$sql = "select sum(n) as n, sum(vendas) as vendas from(";
					$sql .= "select count(*) as n, sum(ve_valor) as vendas from dist_vendas_pos ";
					if((strlen($where_operadora_pos)>0) || (strlen($extra_where)>0)) $sql .= " where ";
					if(strlen($where_operadora_pos)>0) $sql .= " ".$where_operadora_pos;
					if(strpos($extra_where,"|")) {
						list($extra_where, $extra_where_2) = explode("|", $extra_where);
					}
					else {
						$extra_where_2 = $extra_where;
					}
					if((strlen($extra_where)>0)) {
						if(strlen($where_operadora_pos)>0) $sql .= " and ";
						$sql .= $extra_where;
					}
					$sql .=" union all select sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id where (vg.vg_ultimo_status='5' and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." and SUBSTR(tvgpo.tvgpo_canal, 1, 1) ='P') and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where_2)>0)) {
						$sql .= " and ".$extra_where_2;
					}
					$sql .= ") as V";
					
					break;
				case "total_usuarios_cadastrados": 
					$sql = "select count(*) as n from (select distinct ve_estabelecimento from dist_vendas_pos ";
					if((strlen($where_operadora_pos)>0) || (strlen($extra_where)>0)) $sql .= " where ";
					if(strlen($where_operadora_pos)>0) $sql .= " ".$where_operadora_pos;
					if((strlen($extra_where)>0)) {
						if(strlen($where_operadora_pos)>0) $sql .= " and ";
						$sql .= $extra_where;
					}
					$sql .= " group by ve_estabelecimento ";
					$sql .= "\n union all ";
					$sql .= "\n select distinct tvgpo_id::character varying(100) from tb_venda_games_pinepp_origem tvgpo where SUBSTR(tvgpo.tvgpo_canal, 1, 1) ='P' group by tvgpo_id ";
					$sql .= ") v ";
					//echo $sql;
					break;
				case "total_estados": 
					$sql = "select count(*) as n from (select distinct ve_estado from dist_vendas_pos ";
					if((strlen($where_operadora_pos)>0) || (strlen($extra_where)>0)) $sql .= " where ";
					if(strlen($where_operadora_pos)>0) $sql .= " ".$where_operadora_pos;
					if((strlen($extra_where)>0)) {
						if(strlen($where_operadora_pos)>0) $sql .= " and ";
						$sql .= $extra_where;
					}
					$sql .= " group by ve_estado ) v ";
					break;
				case "total_cidades": 
					$sql = "select count(*) as n from (select distinct ve_cidade from dist_vendas_pos ";
					if((strlen($where_operadora_pos)>0) || (strlen($extra_where)>0)) $sql .= " where ";
					if(strlen($where_operadora_pos)>0) $sql .= " ".$where_operadora_pos;
					if((strlen($extra_where)>0)) {
						if(strlen($where_operadora_pos)>0) $sql .= " and ";
						$sql .= $extra_where;
					}
					$sql .= " group by ve_cidade ) v ";
					break;
				case "total_usuarios_compraram": 
					$sql = "select count(*) as n from (select distinct ve_estabelecimento from dist_vendas_pos ";
					if((strlen($where_operadora_pos)>0) || (strlen($extra_where)>0)) $sql .= " where ";
					if(strlen($where_operadora_pos)>0) $sql .= " ".$where_operadora_pos;
					if((strlen($extra_where)>0)) {
						if(strlen($where_operadora_pos)>0) $sql .= " and ";
						$sql .= $extra_where;
					}
					$sql .= " group by ve_estabelecimento ";
					$sql .= "\n union all ";
					$sql .= "\n select distinct tvgpo_id::character varying(100) from tb_venda_games_pinepp_origem tvgpo where SUBSTR(tvgpo.tvgpo_canal, 1, 1) ='P' group by tvgpo_id ";
					$sql .= ") v ";
					break;
				case "datas_limites_no_bd": 
					$sql = "select min(data_min) as data_min, max(data_max) as data_max from (";
					$sql .= "select min(ve_data_inclusao::date) as data_min, max(ve_data_inclusao::date) as data_max from dist_vendas_pos ";
					if((strlen($where_operadora_pos)>0) || (strlen($extra_where)>0)) $sql .= " where ";
					if(strlen($where_operadora_pos)>0) $sql .= " ".$where_operadora_pos;
					if((strlen($extra_where)>0)) {
						if(strlen($where_operadora_pos)>0) $sql .= " and ";
						$sql .= $extra_where;
					}
					$sql .= " union all select min($where_mode_data::date) as data_min, max($where_mode_data::date) as data_max from tb_venda_games vg inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id where (vg.vg_ultimo_status='5' and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." and SUBSTR(tvgpo.tvgpo_canal, 1, 1) ='P') and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= ") as v";
					break;
				case "por_dia":  
					$sql = "select data, sum(n) as n, sum(vendas) as vendas from (";
					$sql .= "select ve_data_inclusao::date as data, count(*) as n, sum(ve_valor) as vendas from dist_vendas_pos ";
					if((strlen($where_operadora_pos)>0) || (strlen($extra_where)>0)) $sql .= " where ";
					if(strlen($where_operadora_pos)>0) $sql .= " ".$where_operadora_pos;
					if(strpos($extra_where,"|")) {
						list($extra_where, $extra_where_2) = explode("|", $extra_where);
					}
					else {
						$extra_where_2 = $extra_where;
					}
					if((strlen($extra_where)>0)) {
						if(strlen($where_operadora_pos)>0) $sql .= " and ";
						$sql .= (string)$extra_where;
					}
					$sql .= " group by data ";
					$sql .= " union all select $where_mode_data::date as data, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id where (vg.vg_ultimo_status='5' and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC."  and SUBSTR(tvgpo.tvgpo_canal, 1, 1) ='P') and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where_2)>0)) {
						$sql .= " and ".$extra_where_2;
					}
					$sql .= " group by $where_mode_data::date ";
					$sql .= ") as v group by data order by data desc ";
					break;
				case "por_publisher":  
					$sql = "select ve_jogo, sum(n) as n, sum(vendas) as vendas from (";
					$sql .= "select ve_jogo, count(*) as n, sum(ve_valor) as vendas from dist_vendas_pos ";
					if((strlen($where_operadora_pos)>0) || (strlen($extra_where)>0)) $sql .= " where ";
					if(strlen($where_operadora_pos)>0) $sql .= " ".$where_operadora_pos;
					if((strlen($extra_where)>0)) {
						if(strlen($where_operadora_pos)>0) $sql .= " and ";
						$sql .= $extra_where;
					}
					$sql .= " group by ve_jogo";
					$sql .= " union all select vgm.vgm_nome_produto as ve_jogo, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id where (vg.vg_ultimo_status='5' and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." and SUBSTR(tvgpo.tvgpo_canal, 1, 1) ='P') and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= " group by vgm.vgm_nome_produto";
					$sql .= ") as v group by ve_jogo order by vendas desc,ve_jogo ";
					break;
				case "por_jogo":  
					$sql = "select ve_jogo, sum(n) as n, sum(vendas) as vendas from (";
					$sql .= "select ve_jogo, ve_valor, count(*) as n, sum(ve_valor) as vendas from dist_vendas_pos ";
					if((strlen($where_operadora_pos)>0) || (strlen($extra_where)>0)) $sql .= " where ";
					if(strlen($where_operadora_pos)>0) $sql .= " ".$where_operadora_pos;
					if((strlen($extra_where)>0)) {
						if(strlen($where_operadora_pos)>0) $sql .= " and ";
						$sql .= $extra_where;
					}
					$sql .= " group by ve_jogo, ve_valor order by vendas desc";
					$sql .= " union all select vgm.vgm_nome_produto as ve_jogo, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id where (vg.vg_ultimo_status='5' and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." and SUBSTR(tvgpo.tvgpo_canal, 1, 1) ='P') and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= " group by vgm.vgm_nome_produto";
					$sql .= ") as v group by ve_jogo order by vendas desc,ve_jogo ";
					break;
				case "por_estado":  
					$sql = "select ve_estado, sum(n) as n, sum(vendas) as vendas from (";
					$sql .= "select ve_estado, count(*) as n, sum(ve_valor) as vendas from dist_vendas_pos ";
					if((strlen($where_operadora_pos)>0) || (strlen($extra_where)>0)) $sql .= " where ";
					if(strlen($where_operadora_pos)>0) $sql .= " ".$where_operadora_pos;
					if((strlen($extra_where)>0)) {
						if(strlen($where_operadora_pos)>0) $sql .= " and ";
						$sql .= $extra_where;
					}
					$sql .= " group by ve_estado";
					$sql .= " union all select ug.ug_estado as ve_estado, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id where (vg.vg_ultimo_status='5' and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." and SUBSTR(tvgpo.tvgpo_canal, 1, 1) ='P') and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= " group by ug.ug_estado";
					$sql .= ") as v group by ve_estado order by vendas desc,ve_estado ";
					break;
				case "por_cidade":  
					$sql = "select ve_cidade, sum(n) as n, sum(vendas) as vendas from (";
					$sql .= "select ve_cidade, ve_estado, count(*) as n, sum(ve_valor) as vendas from dist_vendas_pos ";
					if((strlen($where_operadora_pos)>0) || (strlen($extra_where)>0)) $sql .= " where ";
					if(strlen($where_operadora_pos)>0) $sql .= " ".$where_operadora_pos;
					if((strlen($extra_where)>0)) {
						if(strlen($where_operadora_pos)>0) $sql .= " and ";
						$sql .= $extra_where;
					}
					$sql .= " group by ve_cidade, ve_estado";
					$sql .= " union all select ug.ug_cidade as ve_cidade, ug.ug_estado as ve_estado, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id where (vg.vg_ultimo_status='5' and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." and SUBSTR(tvgpo.tvgpo_canal, 1, 1) ='P') and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= " group by ug.ug_cidade, ug.ug_estado";
					$sql .= ") as v group by ve_cidade order by vendas desc,ve_cidade ";
					break;
				case "por_tipo_de_estabelecimento": 
					$sql = "select ve_estabtipo, count(*) as n, sum(ve_valor) as vendas from dist_vendas_pos ";
					if((strlen($where_operadora_pos)>0) || (strlen($extra_where)>0)) $sql .= " where ";
					if(strlen($where_operadora_pos)>0) $sql .= " ".$where_operadora_pos;
					if((strlen($extra_where)>0)) {
						if(strlen($where_operadora_pos)>0) $sql .= " and ";
						$sql .= $extra_where;
					}
					$sql .= " group by ve_estabtipo order by vendas desc, ve_estabtipo";
					// não colocado para rede P* como ex. Rede Ponto Certo
					break;
				case "por_estabelecimento": 
					$sql = "select ve_estabelecimento, ve_estabtipo, ve_cidade, ve_estado, count(*) as n, sum(ve_valor) as vendas ";
//					if((strlen($extra_where)==0)) {
						$sql .= ", min(ve_data_inclusao) as primeira_venda, max(ve_data_inclusao) as ultima_venda ";
//					}
					$sql .= " from dist_vendas_pos ";
					if((strlen($where_operadora_pos)>0) || (strlen($extra_where)>0)) $sql .= " where ";
					if(strlen($where_operadora_pos)>0) $sql .= " ".$where_operadora_pos;
					if((strlen($extra_where)>0)) {
						if(strlen($where_operadora_pos)>0) $sql .= " and ";
						$sql .= $extra_where;
					}
					$sql .= " group by ve_estabelecimento, ve_estabtipo, ve_cidade, ve_estado order by vendas desc, ve_estabelecimento";
					// não colocado para rede P* como ex. Rede Ponto Certo
					break;
				case "por_estabelecimento_barra": 
					$sql = "select ve_estabelecimento, ve_estabtipo, min(ve_data_inclusao) as primeira_venda, max(ve_data_inclusao) as ultima_venda,(NOW()-max(ve_data_inclusao)) as abandonou, ve_cidade, ve_estado, count(*) as n, sum(ve_valor) as vendas ";
					$sql .= " from dist_vendas_pos ";
					if((strlen($where_operadora_pos)>0) || (strlen($extra_where)>0)) $sql .= " where ";
					if(strlen($where_operadora_pos)>0) $sql .= " ".$where_operadora_pos;
					if((strlen($extra_where)>0)) {
						if(strlen($where_operadora_pos)>0) $sql .= " and ";
						$sql .= $extra_where;
					}
					$sql .= " group by ve_estabelecimento, ve_estabtipo, ve_cidade, ve_estado order by vendas desc, ve_estabelecimento";
					// não colocado para rede P* como ex. Rede Ponto Certo
					break;
				case "por_usuario":  
					$sql = "";
					break;
				// ===================================   
				case "por_mes": 
					$sql = "select mes,sum(n) as n,sum(vendas) as vendas from (";
					$sql .= "select date_trunc('month', ve_data_inclusao) as mes, count(*) as n, sum(ve_valor) as vendas from dist_vendas_pos ";
					if((strlen($where_operadora_pos)>0) || (strlen($extra_where)>0)) $sql .= " where ";
					if(strlen($where_operadora_pos)>0) $sql .= " ".$where_operadora_pos;
					if((strlen($extra_where)>0)) {
						if(strlen($where_operadora_pos)>0) $sql .= " and ";
						$sql .= $extra_where;
					}
					$sql .= " group by mes";
					$sql .= " union all select date_trunc('month', $where_mode_data) as mes, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id where (vg.vg_ultimo_status='5' and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." and SUBSTR(tvgpo.tvgpo_canal, 1, 1) ='P') and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= " group by mes ";
					$sql .= " ) as v group by mes order by mes desc ";
					break;
				case "por_hora_do_dia": 
					$sql = "";
					break;
				case "por_dia_da_semana": 
					$sql = "select dow1 AS DOW ,sum(n) as n,sum(vendas) as vendas from (";
					$sql .= "select EXTRACT(dow from ve_data_inclusao) as dow1, count(*) as n, sum(ve_valor) as vendas from dist_vendas_pos ";
					if((strlen($where_operadora_pos)>0) || (strlen($extra_where)>0)) $sql .= " where ";
					if(strlen($where_operadora_pos)>0) $sql .= " ".$where_operadora_pos;
					if((strlen($extra_where)>0)) {
						if(strlen($where_operadora_pos)>0) $sql .= " and ";
						$sql .= $extra_where;
					}
					$sql .= " group by dow1 ";					
					$sql .= " union all select EXTRACT(dow from $where_mode_data) as dow1, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id where (vg.vg_ultimo_status='5' and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." and SUBSTR(tvgpo.tvgpo_canal, 1, 1) ='P') and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= "group by dow1 ";
					$sql .= ") AS V group by dow order by dow  ";
					break;
			}
			break;

		case "M":		// Money ========================================================================================
			switch($query_type) {
				case "totais_de_vendas": 
					$sql = "select sum(n) as n, sum(vendas) as vendas from(";
					$sql .= "select sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id where ug.ug_id != '7909' and vg.vg_ultimo_status='5' and vg.vg_pagto_tipo != ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .=" union all select sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id where (vg.vg_ultimo_status='5' and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." and tvgpo.tvgpo_canal='G') and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= ") as V";
					break;
				case "total_usuarios_cadastrados": 
					$sql = "";
					break;
				case "total_estados": 
					$sql = "";
					break;
				case "total_cidades": 
					$sql = "";
					break;
				case "total_usuarios_compraram": 
					$sql = "";
					break;
				case "datas_limites_no_bd": 
					$sql = "select min(data_min) as data_min, max(data_max) as data_max from (";
					$sql .= "select min($where_mode_data::date) as data_min, max($where_mode_data::date) as data_max from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id where ug.ug_id != '7909' and vg.vg_ultimo_status='5' and vg.vg_pagto_tipo != ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= " union all select min($where_mode_data::date) as data_min, max($where_mode_data::date) as data_max from tb_venda_games vg inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id where (vg.vg_ultimo_status='5' and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." and tvgpo.tvgpo_canal='G') and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= ") as v";
					break;
				case "por_dia":  
					$sql = "select data, sum(n) as n, sum(vendas) as vendas from (";
					$sql .= "select $where_mode_data::date as data, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id where ug.ug_id != '7909' and vg.vg_ultimo_status='5' and vg.vg_pagto_tipo != ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= " group by $where_mode_data::date ";
					$sql .= " union all select $where_mode_data::date as data, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id where (vg.vg_ultimo_status='5' and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC."  and tvgpo.tvgpo_canal='G') and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= " group by $where_mode_data::date ";
					$sql .= ") as v group by data order by data desc ";
					break;
				case "por_publisher":  
					$sql = "select ve_jogo, sum(n) as n, sum(vendas) as vendas from (";
					$sql .= "select vgm.vgm_nome_produto as ve_jogo, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id where ug.ug_id != '7909' and vg.vg_ultimo_status='5' and vg.vg_pagto_tipo != ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= " group by vgm.vgm_nome_produto ";
					$sql .= " union all select vgm.vgm_nome_produto as ve_jogo, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id where (vg.vg_ultimo_status='5' and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." and tvgpo.tvgpo_canal='G') and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= " group by vgm.vgm_nome_produto";
					$sql .= ") as v group by ve_jogo order by vendas desc,ve_jogo ";
					break;
				case "por_estado":  
					$sql = "select ve_estado, sum(n) as n, sum(vendas) as vendas from (";
					$sql .= "select ug.ug_estado as ve_estado, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id where ug.ug_id != '7909' and vg.vg_ultimo_status='5' and vg.vg_pagto_tipo != ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= " group by ug.ug_estado";
					$sql .= " union all select ug.ug_estado as ve_estado, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id where (vg.vg_ultimo_status='5' and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." and tvgpo.tvgpo_canal='G') and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= " group by ug.ug_estado";
					$sql .= ") as v group by ve_estado order by vendas desc,ve_estado ";
					break;
				case "por_cidade":  
					$sql = "select ve_cidade, sum(n) as n, sum(vendas) as vendas from (";
					$sql .= "select ug.ug_cidade as ve_cidade, ug.ug_estado as ve_estado, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id where ug.ug_id != '7909' and vg.vg_ultimo_status='5' and vg.vg_pagto_tipo != ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= " group by ug.ug_cidade, ug.ug_estado";
					$sql .= " union all select ug.ug_cidade as ve_cidade, ug.ug_estado as ve_estado, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id where (vg.vg_ultimo_status='5' and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." and tvgpo.tvgpo_canal='G') and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= " group by ug.ug_cidade, ug.ug_estado";
					$sql .= ") as v group by ve_cidade order by vendas desc,ve_cidade ";
					break;
				case "por_tipo_de_estabelecimento": 
					$sql = "";
					break;
				case "por_estabelecimento": 
					$sql = "";
					break;
				case "por_usuario":  
					$sql = "select ve_nome,ve_cidade,ve_estado,min(primeira_venda) as primeira_venda,max(ultima_venda) as ultima_venda, sum(n) as n, sum(vendas) as vendas from (";
					$sql .= "select ug.ug_nome as ve_nome, ug.ug_cidade as ve_cidade, ug.ug_estado as ve_estado, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas ";
//					if((strlen($extra_where)==0)) {
//						$sql .= ", (select vg1.vg_data_inclusao from tb_venda_games vg1 where vg.vg_ex_email=vg1.vg_ex_email order by vg1.vg_data_inclusao desc limit 1) as primeira_venda ";
						$sql .= ", min($where_mode_data) as primeira_venda ";
//						$sql .= ", (select vg1.vg_data_inclusao from tb_venda_games vg1 where vg.vg_ex_email=vg1.vg_ex_email order by vg1.vg_data_inclusao limit 1) as ultima_venda ";
						$sql .= ", max($where_mode_data) as ultima_venda ";
//					}
					$sql .= " from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id where ug.ug_id != '7909' and vg.vg_ultimo_status='5' and vg.vg_pagto_tipo != ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= " group by ug.ug_nome, ug.ug_cidade, ug.ug_estado";
					$sql .= " union all select (case when ug.ug_id != '7909' then ug.ug_nome when ug.ug_id = '7909' then vg.vg_ex_email end) as ve_nome, ug.ug_cidade as ve_cidade, ug.ug_estado as ve_estado, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas ";					
//					if((strlen($extra_where)==0)) {
//						$sql .= ", (select vg1.vg_data_inclusao from tb_venda_games vg1 where vg.vg_ex_email=vg1.vg_ex_email order by vg1.vg_data_inclusao desc limit 1) as primeira_venda ";
						$sql .= ", min($where_mode_data) as primeira_venda ";
//						$sql .= ", (select vg1.vg_data_inclusao from tb_venda_games vg1 where vg.vg_ex_email=vg1.vg_ex_email order by vg1.vg_data_inclusao limit 1) as ultima_venda ";
						$sql .= ", max($where_mode_data) as ultima_venda ";
//					}
					$sql .= " from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id where (vg.vg_ultimo_status='5' and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." and tvgpo.tvgpo_canal='G') and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= " group by ug.ug_nome, ug.ug_cidade, ug.ug_estado, ug.ug_id, vg.vg_ex_email";
					$sql .= ") as v group by ve_nome,ve_cidade,ve_estado order by vendas desc,ve_nome ";
					break;
				// ===================================   
				case "por_mes": 
					$sql = "select mes,sum(n) as n,sum(vendas) as vendas from (";
					$sql .= "select date_trunc('month', $where_mode_data) as mes, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id where ug.ug_id != '7909' and vg.vg_ultimo_status='5' and vg.vg_pagto_tipo != ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= " group by mes ";
					$sql .= " union all select date_trunc('month', $where_mode_data) as mes, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id where (vg.vg_ultimo_status='5' and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." and tvgpo.tvgpo_canal='G') and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= " group by mes ";
					$sql .= " ) as v group by mes order by mes desc ";
					break;
				case "por_hora_do_dia": 
					$sql = "";
					break;
				case "por_dia_da_semana": 
					$sql = "select dow1 AS DOW ,sum(n) as n,sum(vendas) as vendas from (";
					$sql .= "select EXTRACT(dow from $where_mode_data) as dow1, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id where ug.ug_id != '7909' and vg.vg_ultimo_status='5' and vg.vg_pagto_tipo != ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= "group by dow1 ";
					$sql .= " union all select EXTRACT(dow from $where_mode_data) as dow1, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id where (vg.vg_ultimo_status='5' and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." and tvgpo.tvgpo_canal='G') and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= "group by dow1 ";
					$sql .= ") AS V group by dow order by dow  ";
					break;
			}
			break;

		case "E":		// Money Express ========================================================================================
			switch($query_type) {
				case "totais_de_vendas": 
					$sql = "select sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id where ug.ug_id = '7909' and vg.vg_ultimo_status='5' and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					break;
				case "total_usuarios_cadastrados": 
					$sql = "";
					break;
				case "total_estados": 
					$sql = "";
					break;
				case "total_cidades": 
					$sql = "";
					break;
				case "total_usuarios_compraram": 
					$sql = "";
					break;
				case "datas_limites_no_bd": 
					$sql = "select min($where_mode_data::date) as data_min, max($where_mode_data::date) as data_max from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id where ug.ug_id = '7909' and vg.vg_ultimo_status='5' and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					break;
				case "por_dia":  
					$sql = "select $where_mode_data::date as data, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id where ug.ug_id = '7909' and vg.vg_ultimo_status='5' and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= " group by $where_mode_data::date order by $where_mode_data::date desc ";
					break;
				case "por_publisher":  
					$sql = "select vgm.vgm_nome_produto as ve_jogo, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id where ug.ug_id = '7909' and vg.vg_ultimo_status='5' and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= " group by vgm.vgm_nome_produto order by vendas desc, vgm.vgm_nome_produto";
					break;
				case "por_estado":  
					$sql = "select ug.ug_estado as ve_estado, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id where ug.ug_id = '7909' and vg.vg_ultimo_status='5' and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= " group by ug.ug_estado order by vendas desc, ve_estado";
					break;
				case "por_cidade":  
					$sql = "select ug.ug_cidade as ve_cidade, ug.ug_estado as ve_estado, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id where ug.ug_id = '7909' and vg.vg_ultimo_status='5' and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= " group by ug.ug_cidade, ug.ug_estado order by vendas desc, ve_cidade";
					break;
				case "por_tipo_de_estabelecimento": 
					$sql = "";
					break;
				case "por_estabelecimento": 
					$sql = "";
					break;
				case "por_usuario":  //	vg_ex_email		// '' as ve_cidade, '' as ve_estado, 
/*
	Para encontrar primeira e ultima compras nos registros selecionados
	min(vg.vg_data_inclusao) as primeira_venda, max(vg.vg_data_inclusao) as ultima_venda 

	Para encontrar primeira e ultima compras em todos os registros, mesmo para último mês e última semana
	Está muito lento: 93s
	(select vg1.vg_data_inclusao from tb_venda_games vg1 where vg.vg_ex_email=vg1.vg_ex_email order by vg1.vg_data_inclusao desc limit 1) as primeira_venda, (select vg1.vg_data_inclusao from tb_venda_games vg1 where vg.vg_ex_email=vg1.vg_ex_email order by vg1.vg_data_inclusao limit 1) as ultima_venda 
*/
//echo "strlen(extra_where): ".strlen($extra_where)."<br>";
					$sql = "select vg.vg_ex_email as ve_nome, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas ";
//					if((strlen($extra_where)==0)) {
//						$sql .= ", (select vg1.vg_data_inclusao from tb_venda_games vg1 where vg.vg_ex_email=vg1.vg_ex_email order by vg1.vg_data_inclusao desc limit 1) as primeira_venda ";
						$sql .= ", min($where_mode_data) as primeira_venda ";
//						$sql .= ", (select vg1.vg_data_inclusao from tb_venda_games vg1 where vg.vg_ex_email=vg1.vg_ex_email order by vg1.vg_data_inclusao limit 1) as ultima_venda ";
						$sql .= ", max($where_mode_data) as ultima_venda ";
//					}
					$sql .= " from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id where vg.vg_ug_id = '7909' and vg.vg_ultimo_status='5' and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= " group by ve_nome order by vendas desc, ve_nome";
					break;
				// ===================================   
				case "por_mes": 
					$sql = "select date_trunc('month', $where_mode_data) as mes, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id where ug.ug_id = '7909' and vg.vg_ultimo_status='5' and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= " group by mes order by mes desc ";
					break;
				case "por_hora_do_dia": 
					$sql = "";
					break;
				case "por_dia_da_semana": 
					$sql = "select EXTRACT(dow from $where_mode_data) as dow, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id where ug.ug_id = '7909' and vg.vg_ultimo_status='5' and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= "group by dow order by dow  ";
					break;
			}
			break;

		case "S":		// Site (M+E) ========================================================================================
			switch($query_type) {
				case "totais_de_vendas": 
					$sql = "select sum(n) as n, sum(vendas) as vendas from(";
					$sql .= "select sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id where (vg.vg_ultimo_status='5' and vg.vg_pagto_tipo != ".$PAGAMENTO_PIN_EPREPAG_NUMERIC.") and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .=" union all select sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id where (vg.vg_ultimo_status='5' and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." and tvgpo.tvgpo_canal='G') and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= ") as V";
					break;
				case "total_usuarios_cadastrados": 
					$sql = "";
					break;
				case "total_estados": 
					$sql = "";
					break;
				case "total_cidades": 
					$sql = "";
					break;
				case "total_usuarios_compraram": 
					$sql = "";
					break;
				case "datas_limites_no_bd": 
					$sql = "select min(data_min) as data_min, max(data_max) as data_max from (";
					$sql .="select min($where_mode_data::date) as data_min, max($where_mode_data::date) as data_max from tb_venda_games vg where (vg.vg_ultimo_status='5' and vg.vg_pagto_tipo != ".$PAGAMENTO_PIN_EPREPAG_NUMERIC.") and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= " union all select min($where_mode_data::date) as data_min, max($where_mode_data::date) as data_max from tb_venda_games vg inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id where (vg.vg_ultimo_status='5' and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." and tvgpo.tvgpo_canal='G') and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= ") as v";
					break;
				case "por_dia":  
					$sql = "select data, sum(n) as n, sum(vendas) as vendas from (";
					$sql .= "select $where_mode_data::date as data, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id where (vg.vg_ultimo_status='5' and vg.vg_pagto_tipo != ".$PAGAMENTO_PIN_EPREPAG_NUMERIC.") and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= " group by $where_mode_data::date ";
					$sql .= " union all select $where_mode_data::date as data, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id where (vg.vg_ultimo_status='5' and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC."  and tvgpo.tvgpo_canal='G') and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= " group by $where_mode_data::date ";
					$sql .= ") as v group by data order by data desc ";
					break;
				case "por_publisher":  
					$sql = "select ve_jogo, sum(n) as n, sum(vendas) as vendas from (";
					$sql .= "select vgm.vgm_nome_produto as ve_jogo, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id where (vg.vg_ultimo_status='5' and vg.vg_pagto_tipo != ".$PAGAMENTO_PIN_EPREPAG_NUMERIC.") and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= " group by vgm.vgm_nome_produto ";
					$sql .= " union all select vgm.vgm_nome_produto as ve_jogo, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id where (vg.vg_ultimo_status='5' and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." and tvgpo.tvgpo_canal='G') and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= " group by vgm.vgm_nome_produto";
					$sql .= ") as v group by ve_jogo order by vendas desc,ve_jogo ";
					break;
				case "por_estado":  
					$sql = "select ve_estado, sum(n) as n, sum(vendas) as vendas from (";
					$sql .= "select ug.ug_estado as ve_estado, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id where (vg.vg_ultimo_status='5' and vg.vg_pagto_tipo != ".$PAGAMENTO_PIN_EPREPAG_NUMERIC.") and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= " group by ug.ug_estado";
					$sql .= " union all select ug.ug_estado as ve_estado, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id where (vg.vg_ultimo_status='5' and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." and tvgpo.tvgpo_canal='G') and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= " group by ug.ug_estado";
					$sql .= ") as v group by ve_estado order by vendas desc,ve_estado ";
					break;
				case "por_cidade":  
					$sql = "select ve_cidade, sum(n) as n, sum(vendas) as vendas from (";
					$sql .= "select ug.ug_cidade as ve_cidade, ug.ug_estado as ve_estado, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id where (vg.vg_ultimo_status='5' and vg.vg_pagto_tipo != ".$PAGAMENTO_PIN_EPREPAG_NUMERIC.") and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= " group by ug.ug_cidade, ug.ug_estado";
					$sql .= " union all select ug.ug_cidade as ve_cidade, ug.ug_estado as ve_estado, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id where (vg.vg_ultimo_status='5' and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." and tvgpo.tvgpo_canal='G') and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= " group by ug.ug_cidade, ug.ug_estado";
					$sql .= ") as v group by ve_cidade order by vendas desc,ve_cidade ";
					break;
				case "por_tipo_de_estabelecimento": 
					$sql = "";
					break;
				case "por_estabelecimento": 
					$sql = "";
					break;
				case "por_usuario":  
					$sql = "select ve_nome,ve_cidade,ve_estado,min(primeira_venda) as primeira_venda,max(ultima_venda) as ultima_venda, sum(n) as n, sum(vendas) as vendas from (";
					$sql .= "select (case when ug.ug_id != '7909' then ug.ug_nome when ug.ug_id = '7909' then vg.vg_ex_email end) as ve_nome, ug.ug_cidade as ve_cidade, ug.ug_estado as ve_estado, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas ";					
//					if((strlen($extra_where)==0)) {
//						$sql .= ", (select vg1.vg_data_inclusao from tb_venda_games vg1 where vg.vg_ex_email=vg1.vg_ex_email order by vg1.vg_data_inclusao desc limit 1) as primeira_venda ";
						$sql .= ", min($where_mode_data) as primeira_venda ";
//						$sql .= ", (select vg1.vg_data_inclusao from tb_venda_games vg1 where vg.vg_ex_email=vg1.vg_ex_email order by vg1.vg_data_inclusao limit 1) as ultima_venda ";
						$sql .= ", max($where_mode_data) as ultima_venda ";
//					}
					$sql .= " from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id where (vg.vg_ultimo_status='5' and vg.vg_pagto_tipo != ".$PAGAMENTO_PIN_EPREPAG_NUMERIC.") and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= " group by ug.ug_nome, ug.ug_cidade, ug.ug_estado, ug.ug_id, vg.vg_ex_email";
					$sql .= " union all select (case when ug.ug_id != '7909' then ug.ug_nome when ug.ug_id = '7909' then vg.vg_ex_email end) as ve_nome, ug.ug_cidade as ve_cidade, ug.ug_estado as ve_estado, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas ";					
//					if((strlen($extra_where)==0)) {
//						$sql .= ", (select vg1.vg_data_inclusao from tb_venda_games vg1 where vg.vg_ex_email=vg1.vg_ex_email order by vg1.vg_data_inclusao desc limit 1) as primeira_venda ";
						$sql .= ", min($where_mode_data) as primeira_venda ";
//						$sql .= ", (select vg1.vg_data_inclusao from tb_venda_games vg1 where vg.vg_ex_email=vg1.vg_ex_email order by vg1.vg_data_inclusao limit 1) as ultima_venda ";
						$sql .= ", max($where_mode_data) as ultima_venda ";
//					}
					$sql .= " from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id where (vg.vg_ultimo_status='5' and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." and tvgpo.tvgpo_canal='G') and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= " group by ug.ug_nome, ug.ug_cidade, ug.ug_estado, ug.ug_id, vg.vg_ex_email";
					$sql .= ") as v group by ve_nome,ve_cidade,ve_estado order by vendas desc,ve_nome ";
					break;
				// ===================================   
				case "por_mes": 
					$sql = "select mes,sum(n) as n,sum(vendas) as vendas from (";
					$sql .= "select date_trunc('month', $where_mode_data) as mes, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id where (vg.vg_ultimo_status='5' and vg.vg_pagto_tipo != ".$PAGAMENTO_PIN_EPREPAG_NUMERIC.") and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= " group by mes ";
					$sql .= " union all select date_trunc('month', $where_mode_data) as mes, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id where (vg.vg_ultimo_status='5' and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." and tvgpo.tvgpo_canal='G') and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= " group by mes ";
					$sql .= " ) as v group by mes order by mes desc ";
					break;
				case "por_hora_do_dia": 
					$sql = "";
					break;
				case "por_dia_da_semana": 
					$sql = "select dow1 AS DOW ,sum(n) as n,sum(vendas) as vendas from (";
					$sql .= "select EXTRACT(dow from $where_mode_data) as dow1, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id where (vg.vg_ultimo_status='5' and vg.vg_pagto_tipo != ".$PAGAMENTO_PIN_EPREPAG_NUMERIC.") and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= "group by dow1 ";
					$sql .= " union all select EXTRACT(dow from $where_mode_data) as dow1, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id where (vg.vg_ultimo_status='5' and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." and tvgpo.tvgpo_canal='G') and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= "group by dow1 ";
					$sql .= ") AS V group by dow order by dow  ";
					break;
			}
			break;

		case "L":	// LH Money ========================================================================================
			switch($query_type) {
				case "totais_de_vendas":
					$sql = "select sum(n) as n, sum(vendas) as vendas from(";
					$sql .= "select sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_dist_venda_games vg inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id where vg.vg_ultimo_status='5' and vg.vg_data_inclusao>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= " union all select sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id where (vg.vg_ultimo_status='5' and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." and tvgpo.tvgpo_canal='L') and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= ") as V";
					break;
				case "total_usuarios_cadastrados": 
					$sql = "";
					break;
				case "total_estados": 
					$sql = "";
					break;
				case "total_cidades": 
					$sql = "";
					break;
				case "total_usuarios_compraram": 
					$sql = "";
					break;
				case "datas_limites_no_bd": 
					$sql = "select min(data_min) as data_min, max(data_max) as data_max from (";
					$sql .= "select min(vg.vg_data_inclusao::date) as data_min, max(vg.vg_data_inclusao::date) as data_max from tb_dist_venda_games vg inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id where vg.vg_ultimo_status='5' and vg.vg_data_inclusao>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= " union all select min($where_mode_data::date) as data_min, max($where_mode_data::date) as data_max from tb_venda_games vg inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id where (vg.vg_ultimo_status='5' and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." and tvgpo.tvgpo_canal='L') and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= ") as v";
					break;
				case "por_dia":		
					$sql = "select data, sum(n) as n, sum(vendas) as vendas from(";
					$sql .= "select vg.vg_data_inclusao::date as data, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_dist_venda_games vg inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id where vg.vg_ultimo_status='5' and vg.vg_data_inclusao>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= " group by vg.vg_data_inclusao::date ";
					$sql .= " union all select $where_mode_data::date as data, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id where (vg.vg_ultimo_status='5' and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC."  and tvgpo.tvgpo_canal='L') and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= " group by $where_mode_data::date ";
					$sql .= " ) as v group by data order by data desc ";
					break;
				case "por_publisher":  
					$sql = "select ve_jogo, sum(n) as n, sum(vendas) as vendas from (";
					$sql .= "select vgm.vgm_nome_produto as ve_jogo, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_dist_venda_games vg inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id where vg.vg_ultimo_status='5' and vg.vg_data_inclusao>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= " group by vgm.vgm_nome_produto";
					$sql .= " union all select vgm.vgm_nome_produto as ve_jogo, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id where (vg.vg_ultimo_status='5' and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." and tvgpo.tvgpo_canal='L') and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= " group by vgm.vgm_nome_produto";
					$sql .= ") as v group by ve_jogo order by vendas desc,ve_jogo ";
					break;
				case "por_estado":  
					$sql = "select ve_estado, sum(n) as n, sum(vendas) as vendas from (";
					$sql .= "select ug.ug_estado as ve_estado, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_dist_venda_games vg inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join dist_usuarios_games ug on ug.ug_id = vg.vg_ug_id where vg.vg_ultimo_status='5' and vg.vg_data_inclusao>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= " group by ug.ug_estado";
					$sql .= " union all select ug.ug_estado as ve_estado, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id where (vg.vg_ultimo_status='5' and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." and tvgpo.tvgpo_canal='L') and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= " group by ug.ug_estado";
					$sql .= ") as v group by ve_estado order by vendas desc,ve_estado ";
					break;
				case "por_cidade":  
					$sql = "select ve_cidade, sum(n) as n, sum(vendas) as vendas from (";
					$sql .= "select ug.ug_cidade as ve_cidade, ug.ug_estado as ve_estado, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_dist_venda_games vg inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join dist_usuarios_games ug on ug.ug_id = vg.vg_ug_id where vg.vg_ultimo_status='5' and vg.vg_data_inclusao>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= " group by ug.ug_cidade, ug.ug_estado";
					$sql .= " union all select ug.ug_cidade as ve_cidade, ug.ug_estado as ve_estado, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id where (vg.vg_ultimo_status='5' and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." and tvgpo.tvgpo_canal='L') and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= " group by ug.ug_cidade, ug.ug_estado";
					$sql .= ") as v group by ve_cidade order by vendas desc,ve_cidade ";
					break;
				case "por_tipo_de_estabelecimento": 
					$sql = "";
					break;
				case "por_estabelecimento": 
					$sql = "";
					break;
				case "por_usuario":  
					$sql = "select ve_nome,ug_email,ug_contatada_ultimo_mes,ve_cidade,ve_estado,min(primeira_venda) as primeira_venda,max(ultima_venda) as ultima_venda, sum(n) as n, sum(vendas) as vendas from (";
					$sql .= "select (CASE WHEN (ug.ug_tipo_cadastro='PJ') THEN ug.ug_nome_fantasia||' ('||ug.ug_tipo_cadastro||')'  WHEN (ug.ug_tipo_cadastro='PF') THEN ug.ug_nome||' ('||ug.ug_tipo_cadastro||')'  END) as ve_nome, ug.ug_email as ug_email, ug.ug_cidade as ve_cidade, ug.ug_estado as ve_estado, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas ";
//					if((strlen($extra_where)==0)) {
//						$sql .= ", (select vg1.vg_data_inclusao from tb_venda_games vg1 where vg.vg_ex_email=vg1.vg_ex_email order by vg1.vg_data_inclusao desc limit 1) as primeira_venda ";
						$sql .= ", min(vg.vg_data_inclusao) as primeira_venda ";
//						$sql .= ", (select vg1.vg_data_inclusao from tb_venda_games vg1 where vg.vg_ex_email=vg1.vg_ex_email order by vg1.vg_data_inclusao limit 1) as ultima_venda ";
						$sql .= ", max(vg.vg_data_inclusao) as ultima_venda ";
//					}
					$sql .= ", ug_contatada_ultimo_mes ";
					$sql .= " from tb_dist_venda_games vg inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join dist_usuarios_games ug on ug.ug_id = vg.vg_ug_id where vg.vg_ultimo_status='5' and vg.vg_data_inclusao>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= " group by ug.ug_nome_fantasia, ug.ug_nome, ug.ug_cidade, ug.ug_estado, ug.ug_tipo_cadastro, ug_contatada_ultimo_mes,ug_email";
					$sql .= " union all select (CASE WHEN (ug.ug_tipo_cadastro='PJ') THEN ug.ug_nome_fantasia||' ('||ug.ug_tipo_cadastro||')'  WHEN (ug.ug_tipo_cadastro='PF') THEN ug.ug_nome||' ('||ug.ug_tipo_cadastro||')'  END) as ve_nome, ug.ug_email as ug_email, ug.ug_cidade as ve_cidade, ug.ug_estado as ve_estado, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas ";					
//					if((strlen($extra_where)==0)) {
//						$sql .= ", (select vg1.vg_data_inclusao from tb_venda_games vg1 where vg.vg_ex_email=vg1.vg_ex_email order by vg1.vg_data_inclusao desc limit 1) as primeira_venda ";
						$sql .= ", min($where_mode_data) as primeira_venda ";
//						$sql .= ", (select vg1.vg_data_inclusao from tb_venda_games vg1 where vg.vg_ex_email=vg1.vg_ex_email order by vg1.vg_data_inclusao limit 1) as ultima_venda ";
						$sql .= ", max($where_mode_data) as ultima_venda ";
//					}
					$sql .= ", ug_contatada_ultimo_mes ";
					$sql .= " from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id inner join dist_usuarios_games ug on ug.ug_id = tvgpo.tvgpo_id where (vg.vg_ultimo_status='5' and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." and tvgpo.tvgpo_canal='L') and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= " group by ug.ug_nome_fantasia, ug.ug_nome, ug.ug_cidade, ug.ug_estado, ug.ug_tipo_cadastro, ug_contatada_ultimo_mes,ug_email";
					$sql .= ") as v group by ve_nome,ug_email,ug_contatada_ultimo_mes,ve_cidade,ve_estado order by vendas desc,ve_nome ";
					break;
				// ===================================   
				case "por_mes": 
					$sql = "select mes,sum(n) as n,sum(vendas) as vendas from (";
					$sql .= "select date_trunc('month', vg.vg_data_inclusao) as mes, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_dist_venda_games vg inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id where vg.vg_ultimo_status='5' and vg.vg_data_inclusao>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= " group by mes ";
					$sql .= " union all select date_trunc('month', $where_mode_data) as mes, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id where (vg.vg_ultimo_status='5' and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." and tvgpo.tvgpo_canal='L') and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= " group by mes ";
					$sql .= " ) as v group by mes order by mes desc ";
					break;
				case "por_hora_do_dia": 
					$sql = "";
					break;
				case "por_dia_da_semana": 
					$sql = "select dow1 AS DOW ,sum(n) as n,sum(vendas) as vendas from (";
					$sql .= "select EXTRACT(dow from vg.vg_data_inclusao) as dow1, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_dist_venda_games vg inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join dist_usuarios_games ug on ug.ug_id = vg.vg_ug_id where vg.vg_ultimo_status='5' and vg.vg_data_inclusao>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= "group by dow1 ";
					$sql .= " union all select EXTRACT(dow from $where_mode_data) as dow1, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id where (vg.vg_ultimo_status='5' and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." and tvgpo.tvgpo_canal='L') and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
					if((strlen($extra_where)>0)) {
						$sql .= " and ".$extra_where;
					}
					$sql .= "group by dow1 ";
					$sql .= ") AS V group by dow order by dow  ";
					break;
			}
			break;

		case "C":	// Cartoes ========================================================================================
			switch($query_type) {
				case "totais_de_vendas": 
					if((strlen($extra_where)>0)) {
						$sqlwhere .= " and ".$extra_where;
					}
					$sql = "select sum(n) as n, sum(vendas1) as vendas
					from (
						select vc_total_mu_online as n, vc_total_mu_online*10 as vendas1, '10' as valor, 'MU' as ve_jogo  
						from dist_vendas_cartoes_tmp 
						where vc_total_mu_online>0 ".$sqlwhere."
						union all
						select vc_total_5k as n, vc_total_5k*13 as vendas1, '13' as valor, 'OG' as ve_jogo
						from dist_vendas_cartoes_tmp 
						where vc_total_5k>0 ".$sqlwhere."
						union all
						select vc_total_10k as n, vc_total_10k*25 as vendas1, '25' as valor, 'OG' as ve_jogo
						from dist_vendas_cartoes_tmp 
						where vc_total_10k>0 ".$sqlwhere."
						union all
						select vc_total_15k as n, vc_total_15k*37 as vendas1, '37' as valor, 'OG' as ve_jogo
						from dist_vendas_cartoes_tmp 
						where vc_total_15k>0 ".$sqlwhere."
						union all
						select vc_total_20k as n, vc_total_20k*49 as vendas1, '49' as valor, 'OG' as ve_jogo
						from dist_vendas_cartoes_tmp 
						where vc_total_20k>0 ".$sqlwhere."
					) v ";
					if(strlen($where_operadora_cartoes)>0) $sql .= " where ".$where_operadora_cartoes;

					break;
				case "total_usuarios_cadastrados": 
					$sql = "";
					break;
				case "total_estados": 
					$sql = "";
					break;
				case "total_cidades": 
					$sql = "";
					break;
				case "total_usuarios_compraram": 
					$sql = "";
					break;
				case "datas_limites_no_bd": 
					$sql = "select min(vc_data::date) as data_min, max(vc_data::date) as data_max from dist_vendas_cartoes_tmp";
					if((strlen($extra_where)>0)) {
						$sql .= " where ".$extra_where;
					}
					break;
				case "por_dia":  
					if((strlen($extra_where)>0)) {
						$sqlwhere .= " and ".$extra_where;
					}
					$sql = "select data, sum(n) as n, sum(vendas1) as vendas
					from (
						select date(vc_data) as data, vc_total_mu_online as n, vc_total_mu_online*10 as vendas1, '10' as valor, 'MU' as ve_jogo  
						from dist_vendas_cartoes_tmp 
						where vc_total_mu_online>0 ".$sqlwhere."
						union all
						select date(vc_data) as data, vc_total_5k as n, vc_total_5k*13 as vendas1, '13' as valor, 'OG' as ve_jogo
						from dist_vendas_cartoes_tmp 
						where vc_total_5k>0 ".$sqlwhere."
						union all
						select date(vc_data) as data, vc_total_10k as n, vc_total_10k*25 as vendas1, '25' as valor, 'OG' as ve_jogo
						from dist_vendas_cartoes_tmp 
						where vc_total_10k>0 ".$sqlwhere."
						union all
						select date(vc_data) as data, vc_total_15k as n, vc_total_15k*37 as vendas1, '37' as valor, 'OG' as ve_jogo
						from dist_vendas_cartoes_tmp 
						where vc_total_15k>0 ".$sqlwhere."
						union all
						select date(vc_data) as data, vc_total_20k as n, vc_total_20k*49 as vendas1, '49' as valor, 'OG' as ve_jogo
						from dist_vendas_cartoes_tmp 
						where vc_total_20k>0 ".$sqlwhere."
					) v ";

					if(strlen($where_operadora_cartoes)>0) $sql .= " where ".$where_operadora_cartoes;
					$sql .= " group by data order by data desc";
					break;
				case "por_publisher":  
//					$sql = "select (CASE WHEN vc_total_mu_online>0 THEN 'MU' WHEN (vc_total_5k+vc_total_10k+vc_total_15k+vc_total_20k)>0 THEN 'OG' ELSE '????' END) as ve_jogo, count(*) as n, sum(vc_valor_total) as vendas from dist_vendas_cartoes_tmp ";

					$sqlwhere = "";
					if((strlen($extra_where)>0)) {
						$sqlwhere .= " and ".$extra_where;
					}

					$sql = "select ve_jogo, sum(n) as n, sum(vendas1) as vendas
					from (
						select vc_total_mu_online as n, vc_total_mu_online*10 as vendas1, '10' as valor, 'MU' as ve_jogo  
						from dist_vendas_cartoes_tmp 
						where vc_total_mu_online>0 ".$sqlwhere."
						union all
						select vc_total_5k as n, vc_total_5k*13 as vendas1, '13' as valor, 'OG' as ve_jogo
						from dist_vendas_cartoes_tmp 
						where vc_total_5k>0 ".$sqlwhere."
						union all
						select vc_total_10k as n, vc_total_10k*25 as vendas1, '25' as valor, 'OG' as ve_jogo
						from dist_vendas_cartoes_tmp 
						where vc_total_10k>0 ".$sqlwhere."
						union all
						select vc_total_15k as n, vc_total_15k*37 as vendas1, '37' as valor, 'OG' as ve_jogo
						from dist_vendas_cartoes_tmp 
						where vc_total_15k>0 ".$sqlwhere."
						union all
						select vc_total_20k as n, vc_total_20k*49 as vendas1, '49' as valor, 'OG' as ve_jogo
						from dist_vendas_cartoes_tmp 
						where vc_total_20k>0 ".$sqlwhere."
					) v ";
					if(strlen($where_operadora_cartoes)>0) $sql .= " where ".$where_operadora_cartoes;
					$sql .= " group by ve_jogo order by vendas desc";
					break;
				case "por_jogo":  

					$sqlwhere = "";
					if((strlen($extra_where)>0)) {
						$sqlwhere .= " and ".$extra_where;
					}

					$sql = "select ve_jogo, valor, sum(n) as n, sum(vendas1) as vendas
					from (
						select vc_total_mu_online as n, vc_total_mu_online*10 as vendas1, '10' as valor, 'MU' as ve_jogo  
						from dist_vendas_cartoes_tmp 
						where vc_total_mu_online>0 ".$sqlwhere."
						union all
						select vc_total_5k as n, vc_total_5k*13 as vendas1, '13' as valor, 'OG' as ve_jogo
						from dist_vendas_cartoes_tmp 
						where vc_total_5k>0 ".$sqlwhere."
						union all
						select vc_total_10k as n, vc_total_10k*25 as vendas1, '25' as valor, 'OG' as ve_jogo
						from dist_vendas_cartoes_tmp 
						where vc_total_10k>0 ".$sqlwhere."
						union all
						select vc_total_15k as n, vc_total_15k*37 as vendas1, '37' as valor, 'OG' as ve_jogo
						from dist_vendas_cartoes_tmp 
						where vc_total_15k>0 ".$sqlwhere."
						union all
						select vc_total_20k as n, vc_total_20k*49 as vendas1, '49' as valor, 'OG' as ve_jogo
						from dist_vendas_cartoes_tmp 
						where vc_total_20k>0 ".$sqlwhere."
					) v ";
					if(strlen($where_operadora_cartoes)>0) $sql .= " where ".$where_operadora_cartoes;
					$sql .= " group by ve_jogo, valor order by vendas desc";

					break;
				case "por_estado":  
					if((strlen($extra_where)>0)) {
						$sqlwhere .= " and ".$extra_where;
					}
					$sql = "select ug_estado as ve_estado, sum(n) as n, sum(vendas1) as vendas
					from (
						select ug_estado, vc_total_mu_online as n, vc_total_mu_online*10 as vendas1, '10' as valor, 'MU' as ve_jogo  
						from dist_vendas_cartoes_tmp vc inner join dist_usuarios_games ug on vc.vc_ug_id = ug.ug_id 
						where vc_total_mu_online>0 ".$sqlwhere."
						union all
						select ug_estado, vc_total_5k as n, vc_total_5k*13 as vendas1, '13' as valor, 'OG' as ve_jogo
						from dist_vendas_cartoes_tmp vc inner join dist_usuarios_games ug on vc.vc_ug_id = ug.ug_id 
						where vc_total_5k>0 ".$sqlwhere."
						union all
						select ug_estado, vc_total_10k as n, vc_total_10k*25 as vendas1, '25' as valor, 'OG' as ve_jogo
						from dist_vendas_cartoes_tmp vc inner join dist_usuarios_games ug on vc.vc_ug_id = ug.ug_id 
						where vc_total_10k>0 ".$sqlwhere."
						union all
						select ug_estado, vc_total_15k as n, vc_total_15k*37 as vendas1, '37' as valor, 'OG' as ve_jogo
						from dist_vendas_cartoes_tmp vc inner join dist_usuarios_games ug on vc.vc_ug_id = ug.ug_id 
						where vc_total_15k>0 ".$sqlwhere."
						union all
						select ug_estado, vc_total_20k as n, vc_total_20k*49 as vendas1, '49' as valor, 'OG' as ve_jogo
						from dist_vendas_cartoes_tmp vc inner join dist_usuarios_games ug on vc.vc_ug_id = ug.ug_id 
						where vc_total_20k>0 ".$sqlwhere."
					) v ";

					if(strlen($where_operadora_cartoes)>0) $sql .= " where ".$where_operadora_cartoes;
					$sql .= " group by ve_estado order by vendas desc, ve_estado";

					break;
				case "por_cidade":  
					if((strlen($extra_where)>0)) {
						$sqlwhere .= " and ".$extra_where;
					}
					$sql = "select ug_cidade as ve_cidade, ug_estado as ve_estado, sum(n) as n, sum(vendas1) as vendas
					from (
						select ug_cidade, ug_estado, vc_total_mu_online as n, vc_total_mu_online*10 as vendas1, '10' as valor, 'MU' as ve_jogo  
						from dist_vendas_cartoes_tmp vc inner join dist_usuarios_games ug on vc.vc_ug_id = ug.ug_id 
						where vc_total_mu_online>0 ".$sqlwhere."
						union all
						select ug_cidade, ug_estado, vc_total_5k as n, vc_total_5k*13 as vendas1, '13' as valor, 'OG' as ve_jogo
						from dist_vendas_cartoes_tmp vc inner join dist_usuarios_games ug on vc.vc_ug_id = ug.ug_id 
						where vc_total_5k>0 ".$sqlwhere."
						union all
						select ug_cidade, ug_estado, vc_total_10k as n, vc_total_10k*25 as vendas1, '25' as valor, 'OG' as ve_jogo
						from dist_vendas_cartoes_tmp vc inner join dist_usuarios_games ug on vc.vc_ug_id = ug.ug_id 
						where vc_total_10k>0 ".$sqlwhere."
						union all
						select ug_cidade, ug_estado, vc_total_15k as n, vc_total_15k*37 as vendas1, '37' as valor, 'OG' as ve_jogo
						from dist_vendas_cartoes_tmp vc inner join dist_usuarios_games ug on vc.vc_ug_id = ug.ug_id 
						where vc_total_15k>0 ".$sqlwhere."
						union all
						select ug_cidade, ug_estado, vc_total_20k as n, vc_total_20k*49 as vendas1, '49' as valor, 'OG' as ve_jogo
						from dist_vendas_cartoes_tmp vc inner join dist_usuarios_games ug on vc.vc_ug_id = ug.ug_id 
						where vc_total_20k>0 ".$sqlwhere."
					) v ";

					if(strlen($where_operadora_cartoes)>0) $sql .= " where ".$where_operadora_cartoes;
					$sql .= " group by ve_cidade, ve_estado order by vendas desc, ve_cidade, ve_estado";

					break;
				case "por_tipo_de_estabelecimento": 
					$sql = "";
					break;
				case "por_estabelecimento": 
					if((strlen($extra_where)>0)) {
						$sqlwhere .= " and ".$extra_where;
					}

					$sql = "select ug_nome_fantasia as ve_estabelecimento, ug_cidade as ve_cidade, ug_estado as ve_estado, sum(n) as n, sum(vendas1) as vendas ";
//					if((strlen($extra_where)==0)) {
						$sql .= ", min(vc_data) as primeira_venda, max(vc_data) as ultima_venda ";
//					}
					$sql .= " 
					from (
						select (CASE WHEN (ug.ug_tipo_cadastro='PJ') THEN upper(ug.ug_nome_fantasia)||' ('||ug.ug_tipo_cadastro||')' WHEN (ug.ug_tipo_cadastro='PF') THEN upper(ug.ug_nome)||' ('||ug.ug_tipo_cadastro||')' END) as ug_nome_fantasia, ug_cidade, ug_estado, vc_total_mu_online as n, vc_total_mu_online*10 as vendas1, '10' as valor, 'MU' as ve_jogo, vc.vc_data  
						from dist_vendas_cartoes_tmp vc inner join dist_usuarios_games ug on vc.vc_ug_id = ug.ug_id 
						where vc_total_mu_online>0 ".$sqlwhere."
						union all
						select (CASE WHEN (ug.ug_tipo_cadastro='PJ') THEN upper(ug.ug_nome_fantasia)||' ('||ug.ug_tipo_cadastro||')' WHEN (ug.ug_tipo_cadastro='PF') THEN upper(ug.ug_nome)||' ('||ug.ug_tipo_cadastro||')' END) as ug_nome_fantasia, ug_cidade, ug_estado, vc_total_5k as n, vc_total_5k*13 as vendas1, '13' as valor, 'OG' as ve_jogo, vc.vc_data
						from dist_vendas_cartoes_tmp vc inner join dist_usuarios_games ug on vc.vc_ug_id = ug.ug_id 
						where vc_total_5k>0 ".$sqlwhere."
						union all
						select (CASE WHEN (ug.ug_tipo_cadastro='PJ') THEN upper(ug.ug_nome_fantasia)||' ('||ug.ug_tipo_cadastro||')' WHEN (ug.ug_tipo_cadastro='PF') THEN upper(ug.ug_nome)||' ('||ug.ug_tipo_cadastro||')' END) as ug_nome_fantasia, ug_cidade, ug_estado, vc_total_10k as n, vc_total_10k*25 as vendas1, '25' as valor, 'OG' as ve_jogo, vc.vc_data
						from dist_vendas_cartoes_tmp vc inner join dist_usuarios_games ug on vc.vc_ug_id = ug.ug_id 
						where vc_total_10k>0 ".$sqlwhere."
						union all
						select (CASE WHEN (ug.ug_tipo_cadastro='PJ') THEN upper(ug.ug_nome_fantasia)||' ('||ug.ug_tipo_cadastro||')' WHEN (ug.ug_tipo_cadastro='PF') THEN upper(ug.ug_nome)||' ('||ug.ug_tipo_cadastro||')' END) as ug_nome_fantasia, ug_cidade, ug_estado, vc_total_15k as n, vc_total_15k*37 as vendas1, '37' as valor, 'OG' as ve_jogo, vc.vc_data
						from dist_vendas_cartoes_tmp vc inner join dist_usuarios_games ug on vc.vc_ug_id = ug.ug_id 
						where vc_total_15k>0 ".$sqlwhere."
						union all
						select (CASE WHEN (ug.ug_tipo_cadastro='PJ') THEN upper(ug.ug_nome_fantasia)||' ('||ug.ug_tipo_cadastro||')' WHEN (ug.ug_tipo_cadastro='PF') THEN upper(ug.ug_nome)||' ('||ug.ug_tipo_cadastro||')' END) as ug_nome_fantasia, ug_cidade, ug_estado, vc_total_20k as n, vc_total_20k*49 as vendas1, '49' as valor, 'OG' as ve_jogo, vc.vc_data
						from dist_vendas_cartoes_tmp vc inner join dist_usuarios_games ug on vc.vc_ug_id = ug.ug_id 
						where vc_total_20k>0 ".$sqlwhere."
					) v ";

					if(strlen($where_operadora_cartoes)>0) $sql .= " where ".$where_operadora_cartoes;
					$sql .= " group by ve_estabelecimento, ve_cidade, ve_estado order by  vendas desc, ve_cidade, ve_estado, ve_estabelecimento";

					break;
				case "por_usuario":  
					$sql = "";
					break;
				// ===================================   
				case "por_mes": 
					if((strlen($extra_where)>0)) {
						$sqlwhere .= " and ".$extra_where;
					}
					$sql = "select date_trunc('month', vc_data) as mes, sum(n) as n, sum(vendas1) as vendas
					from (
						select vc_data, vc_total_mu_online as n, vc_total_mu_online*10 as vendas1, '10' as valor, 'MU' as ve_jogo  
						from dist_vendas_cartoes_tmp 
						where vc_total_mu_online>0 ".$sqlwhere."
						union all
						select vc_data, vc_total_5k as n, vc_total_5k*13 as vendas1, '13' as valor, 'OG' as ve_jogo
						from dist_vendas_cartoes_tmp 
						where vc_total_5k>0 ".$sqlwhere."
						union all
						select vc_data, vc_total_10k as n, vc_total_10k*25 as vendas1, '25' as valor, 'OG' as ve_jogo
						from dist_vendas_cartoes_tmp 
						where vc_total_10k>0 ".$sqlwhere."
						union all
						select vc_data, vc_total_15k as n, vc_total_15k*37 as vendas1, '37' as valor, 'OG' as ve_jogo
						from dist_vendas_cartoes_tmp 
						where vc_total_15k>0 ".$sqlwhere."
						union all
						select vc_data, vc_total_20k as n, vc_total_20k*49 as vendas1, '49' as valor, 'OG' as ve_jogo
						from dist_vendas_cartoes_tmp 
						where vc_total_20k>0 ".$sqlwhere."
					) v ";

					if(strlen($where_operadora_cartoes)>0) $sql .= " where ".$where_operadora_cartoes;
					$sql .= " group by mes order by mes desc";
					break;
				case "por_hora_do_dia": 
					$sql = "";
					break;
				case "por_dia_da_semana": 
					if((strlen($extra_where)>0)) {
						$sqlwhere .= " and ".$extra_where;
					}
					$sql = "select EXTRACT(dow from data) as dow, sum(n) as n, sum(vendas1) as vendas
					from (
						select date(vc_data) as data, vc_total_mu_online as n, vc_total_mu_online*10 as vendas1, '10' as valor, 'MU' as ve_jogo  
						from dist_vendas_cartoes_tmp 
						where vc_total_mu_online>0 ".$sqlwhere."
						union all
						select date(vc_data) as data, vc_total_5k as n, vc_total_5k*13 as vendas1, '13' as valor, 'OG' as ve_jogo
						from dist_vendas_cartoes_tmp 
						where vc_total_5k>0 ".$sqlwhere."
						union all
						select date(vc_data) as data, vc_total_10k as n, vc_total_10k*25 as vendas1, '25' as valor, 'OG' as ve_jogo
						from dist_vendas_cartoes_tmp 
						where vc_total_10k>0 ".$sqlwhere."
						union all
						select date(vc_data) as data, vc_total_15k as n, vc_total_15k*37 as vendas1, '37' as valor, 'OG' as ve_jogo
						from dist_vendas_cartoes_tmp 
						where vc_total_15k>0 ".$sqlwhere."
						union all
						select date(vc_data) as data, vc_total_20k as n, vc_total_20k*49 as vendas1, '49' as valor, 'OG' as ve_jogo
						from dist_vendas_cartoes_tmp 
						where vc_total_20k>0 ".$sqlwhere."
					) v ";

					if(strlen($where_operadora_cartoes)>0) $sql .= " where ".$where_operadora_cartoes;
					$sql .= " group by dow order by dow";

					break;
			}
			break;
			case 'A': //atimo 
			
				switch($query_type) {
				
					case "totais_de_vendas": 
						$sql = "select sum(n) as n, sum(vendas) as vendas from(";
						$sql .= "select sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id where ug.ug_id != '7909' and vg.vg_ultimo_status='5' and vg.vg_ultimo_status_obs like '%AtimoPay%' and vg.vg_http_referer_origem = 'ATIMO' and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
						if((strlen($extra_where)>0)) {
							$sql .= " and ".$extra_where;
						}
						$sql .= ") as V";
					break;
					case "datas_limites_no_bd": 
						$sql = "select min(data_min) as data_min, max(data_max) as data_max from (";
						$sql .= "select min($where_mode_data::date) as data_min, max($where_mode_data::date) as data_max from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id where ug.ug_id != '7909' and vg.vg_ultimo_status='5' and vg.vg_ultimo_status_obs like '%AtimoPay%' and vg.vg_http_referer_origem = 'ATIMO' and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
						if((strlen($extra_where)>0)) {
							$sql .= " and ".$extra_where;
						}
						$sql .= ") as v";
					break;
					case "por_dia":  
						$sql = "select data, sum(n) as n, sum(vendas) as vendas from (";
						$sql .= "select $where_mode_data::date as data, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id where ug.ug_id != '7909' and vg.vg_ultimo_status='5' and vg.vg_ultimo_status_obs like '%AtimoPay%' and vg.vg_http_referer_origem = 'ATIMO' and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." and $where_mode_data>='2008-01-01 00:00:00' ".$where_operadora;
						if((strlen($extra_where)>0)) {
							$sql .= " and ".$extra_where;
						}
						$sql .= " group by $where_mode_data::date ";
						$sql .= ") as v group by data order by data desc ";
					break;
					
				
				}
			
			break;
	}
//echo "sql: $sql<br>";

	return $sql;
}

// ==========================================================================
function get_ncadastros_old($query_channel, $extra_where, $smode) {
	global $where_operadora, $where_operadora_pos, $where_operadora_cartoes;
	$sql = "";
	$ncadastros = 0;
	switch($query_channel) {
		case "P":	// POS ========================================================================================
				$sql = "select ve_estabelecimento, count(*) from dist_vendas_pos group by ve_estabelecimento order by ve_estabelecimento";
				break;
		case "M":		// Money ========================================================================================
				$sql = "select * from usuarios_games ug";
				break;
		case "E":		// Money Express ========================================================================================
				$sql = "select vg.vg_ex_email as ve_nome, count(*) as n from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id where vg.vg_ug_id = '7909' and vg.vg_ultimo_status='5' ";
				$sql .= " group by ve_nome order by ve_nome";
				break;
		case "L":	// LH Money ========================================================================================
				$sql = "select (CASE WHEN (ug.ug_tipo_cadastro='PJ') THEN ug.ug_nome_fantasia||' ('||ug.ug_tipo_cadastro||')' WHEN (ug.ug_tipo_cadastro='PF') THEN ug.ug_nome||' ('||ug.ug_tipo_cadastro||')' END) as ve_nome, ug.ug_cidade as ve_cidade, ug.ug_estado as ve_estado from dist_usuarios_games ug";
				break;
		case "C":	// Cartoes ========================================================================================
				$sql = "select dist_usuarios_games.ug_nome_fantasia as ve_estabelecimento, ug_estado as ve_estado from dist_usuarios_games where ug_ativo=1 and ug_usuario_cartao=1 ";
				break;
	}
//echo "$query_channel -> sql: $sql<br>";
	$vendas_estado = SQLexecuteQuery($sql);
	$ncadastros = pg_num_rows($vendas_estado);
//echo "ncadastros: $ncadastros<br>";

	return $ncadastros;
}

// ==========================================================================
function get_ncadastros($query_channel, $extra_where, $smode) {
	global $where_operadora, $where_operadora_pos, $where_operadora_cartoes,$PAGAMENTO_PIN_EPREPAG_NUMERIC;
	$sql = "";
	$ncadastros = 0;
	switch($query_channel) {
		case "P":	// POS ========================================================================================
				// O "GROUP BY" tem que ficar igual ao "Por Estabelecimento" -> 		$sql = get_sql_query("P", "por_estabelecimento", addWhereClause($extra_where, $where_opr_2));" para poder comparar os totais retornados
				$sql = "select count(*) as n from (select ve_estabelecimento, ve_estabtipo, ve_cidade, ve_estado, count(*) from dist_vendas_pos group by ve_estabelecimento, ve_estabtipo, ve_cidade, ve_estado  ";
				$sql .= "\n union all ";
				$sql .= "\n select tvgpo_canal, tvgpo_id::character varying(100), tvgpo_perc_desconto::character varying(100), tvgpo_cons_financial::character varying(2),	count(*) as n from tb_venda_games_pinepp_origem tvgpo where SUBSTR(tvgpo.tvgpo_canal, 1, 1) ='P' group by tvgpo_canal, tvgpo_id ,tvgpo_perc_desconto,tvgpo_cons_financial ";
				$sql .= "\n ) a ";	
				break;
		case "M":		// Money ========================================================================================
//				$sql = "select count(*) as n from (select * from usuarios_games ug) a";
				$sql = "select count(*) as n from usuarios_games ug ";
				break;
		case "E":		// Money Express ========================================================================================
				$sql = "select count(*) as n  from (select vg.vg_ex_email as ve_nome, count(*) as n from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id where vg.vg_ug_id = '7909' and vg.vg_ultimo_status='5' group by ve_nome order by ve_nome) a ";
				break;
//		case "S":		// Site (M+E) ========================================================================================
//				$sql = "select count(*) as n from (select ug_email, count(*) as n from usuarios_games ug group by ug_email  union all select vg.vg_ex_email as ve_nome, count(*) as n from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id where vg.vg_ug_id = '7909' and vg.vg_ultimo_status='5' group by ve_nome ) a ";
//				break;
		case "L":	// LH Money ========================================================================================
//				$sql = "select count(*) as n from (select (CASE WHEN (ug.ug_tipo_cadastro='PJ') THEN ug.ug_nome_fantasia||' ('||ug.ug_tipo_cadastro||')' WHEN (ug.ug_tipo_cadastro='PF') THEN ug.ug_nome||' ('||ug.ug_tipo_cadastro||')' END) as ve_nome, ug.ug_cidade as ve_cidade, ug.ug_estado as ve_estado from dist_usuarios_games ug) a ";
				$sql = "select count(*) as n from dist_usuarios_games ug ";
				break;
		case "C":	// Cartoes ========================================================================================
//				$sql = "select count(*) as n from (select dist_usuarios_games.ug_nome_fantasia as ve_estabelecimento, ug_estado as ve_estado from dist_usuarios_games where ug_ativo=1 ) a ";  // "and ug_usuario_cartao=1 "
				$sql = "select count(*) as n from dist_usuarios_games where ug_ativo=1 ";  // "and ug_usuario_cartao=1 "
				break;
	}
//echo "$query_channel -> sql: $sql<br>";
	$rs_n_usuarios = SQLexecuteQuery($sql);
	if($rs_n_usuarios) {
		$rs_n_usuarios_row = pg_fetch_array($rs_n_usuarios);
		$ncadastros = $rs_n_usuarios_row['n'];
	} else {
		$ncadastros = 0;
	}

//echo "ncadastros: $ncadastros<br>";

	return $ncadastros;
}


// ==========================================================================
// os filtros de operadoras são definidos com $where_operadora, $where_operadora_pos, $where_operadora_cartoes
//	se $opr<>"" => retorna dados agrupados por operadora
function get_sql_total_mes($extra_where, $bcomiss, $smode, $year, $b_opr, $where_origem, $possui_totalizacao_utilizacao = false, $dd_operadora = false) {
	global $where_operadora, $where_operadora_pos, $where_operadora_cartoes, $where_operadora_gift_card,$where_operadora_gocash,$where_operadora_rede_ponto_certo,$where_opr_venda_lan,$where_opr_venda_lan_negativa,$where_opr_utilizacao_lan;
	global $COMISSOES_BRUTAS_PUBLISHER_M_E, $COMISSAO_POS, $COMISSAO_LANS_MIN, $COMISSAO_LANS_CARTOES_MIN, $COMISSOES_BRUTAS, $OPR_CODIGOS, $COMISSOES_BRUTAS_BY_OPR_CODIGO,$PAGAMENTO_PIN_EPREPAG_NUMERIC, $COMISSAO_REDE_PONTO_CERTO;

	$where_mode_data = "vg.vg_data_inclusao";	// default
	if($smode=='S') $where_mode_data = "vg.vg_data_concilia";

	$sql = "";

	$sql = "select canal, mes, sum(n) as n, sum(vendas) as vendas from ( ";

//"P"
	if(!$where_origem) {	// $where_origem é definido apenas para Stardoll em M/E
		$sql .= "\n(select 'P' as canal, date_trunc('month', ve_data_inclusao) as mes, count(*) as n, sum(ve_valor";
		if($bcomiss) {
			$sql .= "*(case when ve_jogo='HB' then (".(($COMISSOES_BRUTAS['P']['HABBO HOTEL']-$COMISSAO_POS))."./100) when ve_jogo='MU' then (".(($COMISSOES_BRUTAS['P']['MU ONLINE']-$COMISSAO_POS))."./100) when ve_jogo='OG' then (".(($COMISSOES_BRUTAS['P']['ONGAME']-$COMISSAO_POS))."./100) end)";
		}
		$sql .= ") as vendas from dist_vendas_pos where 1=1 ";
		if(strlen($where_operadora_pos)>0)		$sql .= " and ".$where_operadora_pos;
		if((strlen($extra_where)>0))			$sql .= " and ".$extra_where;
		$sql .= " and ve_data_inclusao>'2008-01-01 00:00:00' ";
		if($year>0) $sql.= " and extract(YEAR FROM ve_data_inclusao)=$year \n";
		if(isset($GLOBALS['dd_data_start']) && isset($GLOBALS['dd_data_stop'])) {
			$sql.= " and (ve_data_inclusao between '".$GLOBALS['dd_data_start']."' and '".$GLOBALS['dd_data_stop']."') \n";
		}
		$sql .= "\n group by mes ) ";					
		$sql .= "\n union all ";

//"P" contendo pagamento com PIN CASH que foram comprados em P* (Redes POS)
		$sql .= "\n(select 'P' as canal, date_trunc('month', vg.vg_data_inclusao) as mes, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde ";
		if($bcomiss) {
			$sql .= "* (";
			$sql .= " case ";
			foreach ($OPR_CODIGOS as $opr_codigo => $opr_nome){ 
				$comiss = $COMISSOES_BRUTAS['P'][$opr_nome];
				if(!$comiss) $comiss = "0";
				$sql .= " when vgm.vgm_opr_codigo=$opr_codigo then ( case when ((".$comiss.".)>0) then ((".$comiss.".-".$COMISSAO_REDE_PONTO_CERTO.")/100) else 0 end) ";
			}
			$sql .= " end ";
			$sql .= ")";
		}
		$sql .= ") as vendas from tb_venda_games vg \ninner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id \n".
				"inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id \n".
				//"inner join tb_pag_compras tpc on tpc.idvenda = vg.vg_id \n".
				" where vg.vg_ultimo_status='5' and SUBSTR(tvgpo.tvgpo_canal, 1, 1) ='P' ";//and tpc.valorpagtogocash =0 ";
		//if(strlen($where_operadora_pos)>0)		$sql .= " and ".$where_operadora_pos;
		if((strlen($where_operadora)>0))	$sql .= " and ".$where_operadora;
		if((strlen($extra_where)>0))		$sql .= " and ".$extra_where;
		$sql .= " and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC;
		$sql .= " and vg.vg_data_inclusao > '2008-01-01 00:00:00' ";
		if($year>0) $sql.= " and extract(YEAR FROM vg.vg_data_inclusao)=$year \n";
		if(isset($GLOBALS['dd_data_start']) && isset($GLOBALS['dd_data_stop'])) {
			$sql.= " and (vg.vg_data_inclusao between '".$GLOBALS['dd_data_start']."' and '".$GLOBALS['dd_data_stop']."') \n";
		}

		$sql .= "\n group by mes ) ";					
		$sql .= "\n union all ";

//"P" contendo PINs vendidos na Rede Ponto Certo
		$sql .= "\n(select 'P' as canal, date_trunc('month', data_transacao) as mes, count(*) as n, sum(valor ";
		if($bcomiss) {
			$sql .= "* (";
			$sql .= " case ";
			foreach ($OPR_CODIGOS as $opr_codigo => $opr_nome){ 
				$comiss = $COMISSOES_BRUTAS['P'][$opr_nome];
				if(!$comiss) $comiss = "0";
				$sql .= " when opr_codigo=$opr_codigo then ( case when ((".$comiss.".)>0) then ((".$comiss.".-".$COMISSAO_REDE_PONTO_CERTO.")/100) else 0 end) ";
			}
			$sql .= " end ";
			$sql .= ")";
		}
		$sql .= ") as vendas 
                    FROM pos_transacoes_ponto_certo 
                    WHERE opr_codigo is not NULL ";
		if((strlen($where_operadora_rede_ponto_certo)>0)) {
                    $sql .= " AND ".$where_operadora_rede_ponto_certo;
                }
		if($year>0) {
                    $sql.= " AND extract(YEAR FROM data_transacao)=$year \n";
                }
		if(isset($GLOBALS['dd_data_start']) && isset($GLOBALS['dd_data_stop'])) {
                    $sql.= " AND (data_transacao between '".$GLOBALS['dd_data_start']."' and '".$GLOBALS['dd_data_stop']."') \n";
		}
		$sql .= "\n group by mes ) ";					
		$sql .= "\n union all ";

	}
/*
	foreach ($COMISSOES_BRUTAS_BY_OPR_CODIGO['M'] as $ComissaoOperadoraID => $ComissaoValor){ 
		echo "&nbsp;&nbsp;".$ComissaoOperadoraID." -> ".$ComissaoValor."%<br>"; 
	} 
*/
//"M" - "E" "-" menos pagamento PIN CASH
	$sql .= "\n(select case when vg.vg_ug_id = '7909' then 'E' when vg.vg_ug_id != '7909' then 'M' end as canal, date_trunc('month', $where_mode_data) as mes, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde ";
	if($bcomiss) {
		$sql .= "* (";
		$sql .= " case ";		
/*
		foreach ($COMISSOES_BRUTAS_PUBLISHER_M_E as $NomeProduto => $Publisher){ 
			$sql .= " when vgm_nome_produto='".str_replace("'","''",$NomeProduto)."' then (".$COMISSOES_BRUTAS['M'][$Publisher]."./100) ";
		}
*/
		foreach ($COMISSOES_BRUTAS_BY_OPR_CODIGO['M'] as $opr_codigo => $opr_comissao){ 
			$sql .= " when vgm_opr_codigo=".$opr_codigo." then (".$opr_comissao."./100) ";
//echo "opr_codigo: ".$opr_codigo." => ".$opr_comissao."%<br>";
		}

		$sql .= " end ";
		$sql .= ")";
	}
	$sql .= ") as vendas \n";
	$sql .= "from tb_venda_games vg \ninner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id \n where vg.vg_ultimo_status='5' ";
	// inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id \n
	if((strlen($where_operadora)>0))	$sql .= " and ".$where_operadora;
	if((strlen($extra_where)>0))		$sql .= " and ".$extra_where;
	$sql .= " and vg.vg_pagto_tipo != ".$PAGAMENTO_PIN_EPREPAG_NUMERIC;
	$sql .= " and $where_mode_data>'2008-01-01 00:00:00' \n";
	if($year>0) $sql.= " and extract(YEAR FROM vg_data_inclusao)=$year \n";
	if(isset($GLOBALS['dd_data_start']) && isset($GLOBALS['dd_data_stop'])) {
		$sql.= " and ($where_mode_data between '".$GLOBALS['dd_data_start']."' and '".$GLOBALS['dd_data_stop']."') \n";
	}

	if($where_origem) $sql.= " $where_origem ";
	$sql .= " group by mes, canal ) \n";
	$sql .= " union all \n";

//"M" - "E" contendo pagamento PIN CASH que foram comprados por GAMERS
	$sql .= "\n(select case when vg.vg_ug_id = '7909' then 'E' when vg.vg_ug_id != '7909' then 'M' end as canal, date_trunc('month', $where_mode_data) as mes, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde ";
	if($bcomiss) {
		$sql .= "* (";
		$sql .= " case ";		
/*
		foreach ($COMISSOES_BRUTAS_PUBLISHER_M_E as $NomeProduto => $Publisher){ 
			$sql .= " when vgm_nome_produto='".str_replace("'","''",$NomeProduto)."' then (".$COMISSOES_BRUTAS['M'][$Publisher]."./100) ";
		}
*/
		foreach ($COMISSOES_BRUTAS_BY_OPR_CODIGO['M'] as $opr_codigo => $opr_comissao){ 
			$sql .= " when vgm_opr_codigo=".$opr_codigo." then (".$opr_comissao."./100) ";
//echo "opr_codigo: ".$opr_codigo." => ".$opr_comissao."%<br>";
		}

		$sql .= " end ";
		$sql .= ")";
	}
	$sql .= ") as vendas \n";
	$sql .= "from tb_venda_games vg \ninner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id \n".
				"inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id \n".
				//"inner join tb_pag_compras tpc on tpc.idvenda = vg.vg_id \n".
				" where vg.vg_ultimo_status='5' and tvgpo.tvgpo_canal='G' ";//and tpc.valorpagtogocash =0 ";
	// inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id \n
	if((strlen($where_operadora)>0))	$sql .= " and ".$where_operadora;
	if((strlen($extra_where)>0))		$sql .= " and ".$extra_where;
	$sql .= " and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC;
	$sql .= " and $where_mode_data>'2008-01-01 00:00:00' \n";
	if($year>0) $sql.= " and extract(YEAR FROM vg_data_inclusao)=$year \n";
	if(isset($GLOBALS['dd_data_start']) && isset($GLOBALS['dd_data_stop'])) {
		$sql.= " and ($where_mode_data between '".$GLOBALS['dd_data_start']."' and '".$GLOBALS['dd_data_stop']."') \n";
	}

	if($where_origem) $sql.= " $where_origem ";
	$sql .= " group by mes, canal ) \n";
	$sql .= " union all \n";

// atimoPay	
//"M" - "E" - "A" contendo pagamento PIN CASH que foram comprados por GAMERS via atimoPay
	$sql .= "\n(select case when vg.vg_ug_id = '7909' then 'E' when vg.vg_ug_id != '7909' then 'M' end as canal, date_trunc('month', $where_mode_data) as mes, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde ";
	if($bcomiss) {
		$sql .= "* (";
		$sql .= " case ";		
		foreach ($COMISSOES_BRUTAS_BY_OPR_CODIGO['M'] as $opr_codigo => $opr_comissao){ 
			$sql .= " when vgm_opr_codigo=".$opr_codigo." then (".$opr_comissao."./100) ";
		}
		$sql .= " end ";
		$sql .= ")";
	}
	$sql .= ") as vendas \n";
	$sql .= "from tb_venda_games vg \ninner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id \n".
				" where vg.vg_ultimo_status='5'";
	if((strlen($where_operadora)>0))	$sql .= " and ".$where_operadora;
	if((strlen($extra_where)>0))		$sql .= " and ".$extra_where;
	$sql .= " and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC;  
	$sql .= " and $where_mode_data>'2008-01-01 00:00:00' \n";
	$sql .= " and vg.vg_ultimo_status_obs like '%Pagamento via AtimoPay%' \n";
	if($year>0) $sql.= " and extract(YEAR FROM vg_data_inclusao)=$year \n";
	if(isset($GLOBALS['dd_data_start']) && isset($GLOBALS['dd_data_stop'])) {
		$sql.= " and ($where_mode_data between '".$GLOBALS['dd_data_start']."' and '".$GLOBALS['dd_data_stop']."') \n";
	}

	if($where_origem) $sql.= " $where_origem ";
	$sql .= " group by mes, canal ) \n";
	$sql .= " union all \n";
/*
//"M"
	$sql .= "\n(select 'M' as canal, date_trunc('month', $where_mode_data) as mes, count(*) as n, sum(vgm.vgm_valor * vgm.vgm_qtde ";
	if($bcomiss) {
		$sql .= "* (";
		$sql .= " case ";
		foreach ($COMISSOES_BRUTAS_PUBLISHER_M_E as $NomeProduto => $Publisher){ 
			$sql .= " when vgm_nome_produto='".str_replace("'","''",$NomeProduto)."' then (".$COMISSOES_BRUTAS['M'][$Publisher]."./100) ";
		}
		$sql .= " end ";
		$sql .= ")";
	}
	$sql .= ") as vendas \n";
	$sql .= "from tb_venda_games vg \ninner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id \nwhere vg.vg_ug_id != '7909' and vg.vg_ultimo_status='5' and $where_mode_data>='2008-01-01 00:00:00' ";
	// inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id \n
	if((strlen($where_operadora)>0))	$sql .= " and ".$where_operadora;
	if((strlen($extra_where)>0))		$sql .= " and ".$extra_where;
	$sql .= " and $where_mode_data>'2008-01-01 00:00:00' \n";
	$sql .= " group by mes ) \n";
	$sql .= " union all \n";

//"E"
	$sql .= "(select 'E' as canal, date_trunc('month', $where_mode_data) as mes, count(*) as n, sum(vgm.vgm_valor * vgm.vgm_qtde ";
	if($bcomiss) {
		$sql .= "* (";
		$sql .= " case ";
		foreach ($COMISSOES_BRUTAS_PUBLISHER_M_E as $NomeProduto => $Publisher){ 
			$sql .= " when vgm_nome_produto='".str_replace("'","''",$NomeProduto)."' then (".$COMISSOES_BRUTAS['E'][$Publisher]."./100) ";
		}
		$sql .= " end ";
		$sql .= ")";
	}
	$sql .= ") as vendas \nfrom tb_venda_games vg \ninner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id \nwhere vg.vg_ug_id = '7909' and vg.vg_ultimo_status='5' ";
	// inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id \n
	if((strlen($where_operadora)>0))	$sql .= " and ".$where_operadora;
	if((strlen($extra_where)>0))		$sql .= " and ".$extra_where;
	$sql .= " and $where_mode_data>'2008-01-01 00:00:00' \n";
	$sql .= " group by mes ) \n";
	$sql .= " union all \n";
*/

//"L"
//	echo "<hr>'E', 'ONGAME' (".getComissaoValue("E", "ONGAME")."%)<br>";
//	echo "<hr>'L', 'Escola 24hs' (".getComissaoValue("L", "Escola 24hs")."%)<br>";
	if(!$where_origem) {	// $where_origem é definido apenas para Stardoll em M/E

		$sql .= "(select 'L' as canal, date_trunc('month', vg.vg_data_inclusao) as mes, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde ";
		if($bcomiss) {
			$sql .= "* (";
			$sql .= " case ";
			foreach ($OPR_CODIGOS as $opr_codigo => $opr_nome){ 
				$comiss = $COMISSOES_BRUTAS['L'][$opr_nome];
	//echo $opr_nome." -> ".$comiss."<br>";
				if(!$comiss) $comiss = "0";
				$sql .= " when vgm.vgm_opr_codigo=$opr_codigo then ( case when ((".$comiss.".-vgm.vgm_perc_desconto)>0) then ((".$comiss.".-vgm.vgm_perc_desconto)/100) else 0 end) ";
			}
			$sql .= " end ";
			$sql .= ")";
		}
		$sql .= ") as vendas from tb_dist_venda_games vg inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id where vg.vg_ultimo_status='5' and vg.vg_ultimo_status_obs not like '%Pagamento via API Barramento%'";
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
		$sql .= " and vg.vg_data_inclusao>'2008-01-01 00:00:00' \n".$where_opr_venda_lan.PHP_EOL;
		if($year>0) $sql.= " and extract(YEAR FROM vg_data_inclusao)=$year \n";
		if(isset($GLOBALS['dd_data_start']) && isset($GLOBALS['dd_data_stop'])) {
			$sql.= " and (vg_data_inclusao between '".$GLOBALS['dd_data_start']."' and '".$GLOBALS['dd_data_stop']."') \n";
		}

		$sql .= " group by mes ) \n";
		$sql .= " union all \n";
		
		// voltar-sql
		$sql .= "(select 'L' as canal, date_trunc('month', vg.vg_data_inclusao) as mes, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde "; 
		if($bcomiss) {
			$sql .= "* (";
			$sql .= " case ";
			foreach ($OPR_CODIGOS as $opr_codigo => $opr_nome){ 
				$comiss = $COMISSOES_BRUTAS['L'][$opr_nome];
	//echo $opr_nome." -> ".$comiss."<br>";
				if(!$comiss) $comiss = "0";
				$sql .= " when vgm.vgm_opr_codigo=$opr_codigo then ( case when ((".$comiss.".-vgm.vgm_perc_desconto)>0) then ((".$comiss.".-vgm.vgm_perc_desconto)/100) else 0 end) ";
			}
			$sql .= " end ";
			$sql .= ")";
		}
		$sql .= ") as vendas from tb_dist_venda_games vg inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id where vg.vg_ultimo_status='5' ";
        if((strlen($extra_where)>0)) $sql .= " and ".$extra_where;
		$sql .= " and vg.vg_ultimo_status_obs like '%Pagamento via API Barramento%' and vg.vg_data_inclusao>'2008-01-01 00:00:00' and \n".$where_operadora.PHP_EOL;
		if($year>0) $sql.= " and extract(YEAR FROM vg_data_inclusao)=$year \n";
		if(isset($GLOBALS['dd_data_start']) && isset($GLOBALS['dd_data_stop'])) {
			$sql.= " and (vg_data_inclusao between '".$GLOBALS['dd_data_start']."' and '".$GLOBALS['dd_data_stop']."') \n";
		}
 
		$sql .= " group by mes ) \n";  
		$sql .= " union all \n";

//"L" contendo PINs Publisher totalizados por utilização
if($possui_totalizacao_utilizacao) {
    $sql .= "
                        select  'L' as canal,
                                date_trunc('month', pih_data) as mes,
                                count(*) as n, 
                                sum(vgm.vgm_valor";
                        if($bcomiss) {
                                $sql .= "* (";
                                $sql .= " case ";
                                foreach ($OPR_CODIGOS as $opr_codigo => $opr_nome){ 
                                        $comiss = $COMISSOES_BRUTAS['L'][$opr_nome];
                //echo $opr_nome." -> ".$comiss."<br>";
                                        if(!$comiss) $comiss = "0";
                                        $sql .= " when vgm.vgm_opr_codigo=$opr_codigo then ( case when ((".$comiss.".-vgm.vgm_perc_desconto)>0) then ((".$comiss.".-vgm.vgm_perc_desconto)/100) else 0 end) ";
                                }
                                $sql .= " end ";
                                $sql .= ")";
                        }
                        $sql .= ") as vendas
                        from tb_dist_venda_games vg 
                             inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                             inner join tb_dist_venda_games_modelo_pins vgmp on vgmp_vgm_id = vgm.vgm_id 
                             inner join pins_integracao_historico pih on pih_pin_id = vgmp_pin_codinterno
                        where vg.vg_data_inclusao>='2008-01-01 00:00:00' 
                             and vg.vg_ultimo_status='5'
                             and pin_status = '8'
                             and pih_codretepp='2'
                             ".$where_opr_venda_lan_negativa."
                             ".$where_opr_utilizacao_lan."
                             ";
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
                        $sql .=" 
                        group by mes, canal 				
                        
                        union all ".PHP_EOL;
}//end if($possui_totalizacao_utilizacao) 
                
                
//"L" contendo pagamento com PIN CASH que foram comprados em LANs
		$sql .= "(select 'L' as canal, date_trunc('month', vg.vg_data_inclusao) as mes, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde ";
		if($bcomiss) {
			$sql .= "* (";
			$sql .= " case ";
			foreach ($OPR_CODIGOS as $opr_codigo => $opr_nome){ 
				$comiss = $COMISSOES_BRUTAS['L'][$opr_nome];
	//echo $opr_nome." -> ".$comiss."<br>";
				if(!$comiss) $comiss = "0";
				$sql .= " when vgm.vgm_opr_codigo=$opr_codigo then ( case when ((".$comiss.".-tvgpo.tvgpo_perc_desconto)>0) then ((".$comiss.".-tvgpo.tvgpo_perc_desconto)/100) else 0 end) ";
			}
			$sql .= " end ";
			$sql .= ")";
		}
		$sql .= ") as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id \n".
				"inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id \n".
				//"inner join tb_pag_compras tpc on tpc.idvenda = vg.vg_id \n".
				" where vg.vg_ultimo_status='5' and vg.vg_ultimo_status_obs not like '%Pagamento via API Barramento%' and tvgpo.tvgpo_canal='L' ";//and tpc.valorpagtogocash =0 ";
		if((strlen($where_operadora)>0))		$sql .= " and ".$where_operadora;
		if((strlen($extra_where)>0))			$sql .= " and ".$extra_where;
		$sql .= " and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC;
		$sql .= " and vg.vg_data_inclusao>'2008-01-01 00:00:00' \n";
		if($year>0) $sql.= " and extract(YEAR FROM vg_data_inclusao)=$year \n";
		if(isset($GLOBALS['dd_data_start']) && isset($GLOBALS['dd_data_stop'])) {
			$sql.= " and (vg_data_inclusao between '".$GLOBALS['dd_data_start']."' and '".$GLOBALS['dd_data_stop']."') \n";
		}

		$sql .= " group by mes ) \n";
		$sql .= " union all \n";
	}
//"C"	
	$sqlwherecartaodatainit = "and vc_data>'2008-01-01 00:00:00'";
	$sql .= "(select 'C' as canal, date_trunc('month', vc_data) as mes, sum(n) as n, sum(vendas1";
	if($bcomiss) {
		$sql .= "*(comissao/100.)";
	}
	$sql .= ") as vendas\n
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
	if($year>0) $sql.= "  and extract(YEAR FROM vc_data)= $year \n";
	if(isset($GLOBALS['dd_data_start']) && isset($GLOBALS['dd_data_stop'])) {
		$sql.= " and (vc_data between '".$GLOBALS['dd_data_start']."' and '".$GLOBALS['dd_data_stop']."') \n";
	}
	$sql .= "\n group by mes ) \n";
	$sql .= " union all \n";
	
	// Selecionando Pagamentos com GoCASH na Loja
	$sql .= "(select 'C' as canal, date_trunc('month', vg.vg_data_inclusao) as mes, sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde ";
	if($bcomiss) {
		$sql .= "* (";
		$sql .= " case ";
		foreach ($OPR_CODIGOS as $opr_codigo => $opr_nome){ 
			$comiss = $COMISSOES_BRUTAS['C'][$opr_nome];
//echo $opr_nome." -> ".$comiss."<br>";
			if(!$comiss) $comiss = "0";
			$sql .= " when vgm.vgm_opr_codigo=$opr_codigo then ( case when ((".$comiss.".-tvgpo.tvgpo_perc_desconto)>0) then ((".$comiss.".-tvgpo.tvgpo_perc_desconto)/100) else 0 end) ";
		}
		$sql .= " end ";
		$sql .= ")";
	}
	$sql .= ") as vendas from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id \n".
			"inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id \n".
			" where vg.vg_ultimo_status='5' and  tvgpo.tvgpo_canal='C' ";
	if((strlen($where_operadora)>0))		$sql .= " and ".$where_operadora;
	if((strlen($extra_where)>0))			$sql .= " and ".$extra_where;
	$sql .= " and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC;
	$sql .= " and vg.vg_data_inclusao>'2008-01-01 00:00:00' \n";
	if($year>0) $sql.= " and extract(YEAR FROM vg_data_inclusao)=$year \n";
	if(isset($GLOBALS['dd_data_start']) && isset($GLOBALS['dd_data_stop'])) {
		$sql.= " and (vg_data_inclusao between '".$GLOBALS['dd_data_start']."' and '".$GLOBALS['dd_data_stop']."') \n";
	}
	$sql .= " group by mes ) \n";
        
	// Selecionando Utilização de GiftCard
        $sql .= "
            union all 
            
            (select 'C' as canal, date_trunc('month', pih_data) as mes, count(*) as n, sum(pih_pin_valor/100) as vendas 
            from pins_integracao_card_historico 
             where pin_status = '4' and pih_codretepp = '2'  ".$where_operadora_gift_card." and pih_data>'2008-01-01 00:00:00' 
             group by mes ) ";
        
	// Selecionando Utilização de GoCASH
        $sql .=" 
            union all 
            
            (select 'C' as canal, 
		date_trunc('month', pgc_pin_response_date) as mes, 
		count(*) as n, 
		CASE WHEN (select opr_product_type from operadoras inner join pins_gocash ON opr_codigo = pgc_opr_codigo limit 1) = 5 THEN sum(pgc_real_amount) WHEN ((select opr_product_type from operadoras  inner join pins_gocash ON  opr_codigo = pgc_opr_codigo limit 1) = 7 OR (select opr_product_type from operadoras  inner join pins_gocash ON  opr_codigo = pgc_opr_codigo limit 1) = 4 )  THEN sum (pgc_face_amount) ELSE sum (pgc_face_amount) END as vendas 
            from pins_gocash
             where pgc_opr_codigo != 0  ".$where_operadora_gocash." and pgc_pin_response_date>'2008-01-01 00:00:00' 
             group by mes
             order by mes desc ) 

                ";
        
	$sql .= ") t group by canal, mes order by mes desc, canal \n";

    //echo "<!-- sql: ".$sql."-->"; 

	return $sql;
}

// ==========================================================================
// Procura $estabelecimento no array bidimensional $a_vendas_ultimo_mes, hoje com posição $iorder_1, 
//		ordenado por valor desc, várias linhas podêm ter o mesmo valor
// retorna a diferencia entre a posição fornecida e a encontrada em $a_vendas_ultimo_mes para o estabelecimento
function getPositionInArray($query_channel, $estabelecimento, $iorder_1, $a_vendas_ultimo_mes) {
	$ipos = -1;
	$iorder = 0;
	$previous_value = -1;

	$sUserVar = "ve_estabelecimento";
	switch($query_channel) {
		case "P":	// POS 
				$sUserVar = "ve_estabelecimento";
				break;
		case "M":		// Money 
				$sUserVar = "ve_nome";
				break;
		case "E":		// Money Express 
				$sUserVar = "ve_nome";
				break;
		case "L":	// LH Money 
				$sUserVar = "ve_nome";
				break;
		case "C":	// Cartoes 
				$sUserVar = "ve_estabelecimento";
				break; 
	}

//    foreach($a_vendas_ultimo_mes as $key => $value) {
//		if(strcmp($value['ve_estabelecimento'],$estabelecimento)==0) {
    for ($i = 0; $i <= count($a_vendas_ultimo_mes) - 1; $i++) {
		if($i==0) {
			$previous_value = $a_vendas_ultimo_mes[$i]['vendas'];
		}
		if($a_vendas_ultimo_mes[$i]['vendas']!=$previous_value) {
			$iorder++;
			$previous_value = $a_vendas_ultimo_mes[$i]['vendas'];
		}
		if(strcmp($a_vendas_ultimo_mes[$i][$sUserVar],$estabelecimento)==0) {
			$ipos = $iorder - $iorder_1;
			break;
		}
	}
//echo "[== ".(($ipos>0)?"+":(($ipos==0)?"&nbsp;":"-")).$ipos." ==] (".$iorder_1.",  ".$iorder." => ". $ipos.") $estabelecimento ====> $previous_value -> ".$a_vendas_ultimo_mes[$i]['vendas'];
	return $ipos;	// (($ipos>0)?"+":(($ipos==0)?"&nbsp;":"-")).$ipos; //"(".$iorder_1.",  ".$i." => ". $ipos.")";
}

// ==========================================================================
function addWhereClause($swhere, $swhereadd) {
	$stmp = $swhere;
//echo "strlen: ".(strlen(trim($stmp)))."<br>";
	if( (strlen(trim($stmp))>0) && (strlen(trim($swhereadd))>0)) {
		$stmp .= " and ";
	}
	$stmp .= $swhereadd;
	return $stmp; 
}

// ==========================================================================
// "OG", "MU", "HB" -> 13, 17, 16
function getOperadoraCartoesID($ve_jogo) { 
	$dd_ope = 0;
	switch($ve_jogo) {
		case "OG": 
			$dd_ope = 13;
			break;
		case "MU": 
			$dd_ope = 17;
			break;
		case "HB": 
			$dd_ope = 16;
			break;
	}
	return $dd_ope; 
}

// ==========================================================================
// "OG", "MU", "HB" -> "OnGame", "Mu Online", "Habbo Hotel"
function getOperadoraCartoesNome($ve_jogo) { 
	$nome_jogo = "";
	switch($ve_jogo) {
		case "OG": 
			$nome_jogo = "OnGame";
			break;
		case "MU": 
			$nome_jogo = "Mu Online";
			break;
		case "HB": 
			$nome_jogo = "Habbo Hotel";
			break;
	}
	return $nome_jogo; 
}

// ==========================================================================
function getValueNonZero( $val ) {
	$val1 = (($val>0)?$val:1);
	return $val1;
}

// ==========================================================================
function get_delay_alert( $del0 ) {
	$delay = intval($del0);
	$stmp = ""; //" ".$delay.", ";
	if($delay>=0 && $delay<15) {
		$stmp .= "<img src='/imagens/1x1.gif' width='7' height='8' border='0' alt='".LANG_STATISTICS_TITLE_STATUS_1." (".$delay." ".LANG_DAYS.")'>";
	} else if($delay>15 && $delay<30) {
		$stmp .= "<img src='/imagens/1x1y.gif' width='7' height='8' border='0' alt='".LANG_STATISTICS_TITLE_STATUS_2." (".$delay." ".LANG_DAYS.")'>";
	} else {
		$stmp .= "<img src='/imagens/1x1r.gif' width='7' height='8' border='0' alt='".LANG_STATISTICS_TITLE_STATUS_3." (".$delay." ".LANG_DAYS.")'>";
	}
	return $stmp;
}

$iwidth = 100;
$iheight = 8;
$v_start = strtotime ($inic_oper_data);
$today1 = strtotime ('now');
$itotal = intval(($today1-$v_start)/86400+1);

// ==========================================================================
function get_delay_alert_live( $v_primeira, $v_ultima ) {
	global $today1, $iwidth, $iheight, $v_start, $itotal;

	$stmp = "<table border='0' width='100%' cellpadding='0' cellspacing='1' style='border-collapse:collapse;'><tr><td align='center'>&nbsp;"; 
	$salt = date("d/M/y",strtotime($v_primeira))." - ".date("d/M/y",strtotime($v_ultima));

	// Mostra barra de vendas
	$istart = intval((strtotime($v_primeira)-$v_start)/86400+1);
		if($istart<0) $istart = 0;
		if($istart>$itotal) $istart = $itotal;
	$istop = intval((strtotime($v_ultima)-$v_start)/86400+1);
		if($istop<0) $istop = 0;
		if($istop>$itotal) $istop = $itotal;
	$stmp .= "<img src='/imagens/1x1c.gif' width='".(intval($istart*$iwidth/$itotal))."' height='8' border='0' title='$salt'>";
	$stmp .= "<img src='/imagens/1x1g.gif' width='".(intval(($istop-$istart)*$iwidth/$itotal))."' height='8' border='0' title='$salt'>";
	$stmp .= "<img src='/imagens/1x1b.gif' width='".(intval(($itotal-$istop)*$iwidth/$itotal))."' height='8' border='0' title='$salt'>";

	$stmp .= "&nbsp;</td><td align='center'>&nbsp;";
//	$stmp .= "&nbsp;[".$istart."-".$istop."/".$itotal." (".(intval($istart*$iwidth/$itotal)).", ".(intval(($istop-$istart)*$iwidth/$itotal)).", ".(intval(($itotal-$istop)*$iwidth/$itotal)).")]"; 

	// Mostra icone de atraso
	$delay = intval(($today1 - strtotime($v_ultima))/86400+1);
	if($delay>=0 && $delay<15) {
		$stmp .= "<img src='/imagens/1x1.gif' width='7' height='8' border='1' title='".LANG_STATISTICS_TITLE_STATUS_1." (".$delay." ".LANG_DAYS.")'>";
	} else if($delay>15 && $delay<30) {
		$stmp .= "<img src='/imagens/1x1y.gif' width='7' height='8' border='1' title='".LANG_STATISTICS_TITLE_STATUS_2." (".$delay." ".LANG_DAYS.")'>";
	} else {
		$stmp .= "<img src='/imagens/1x1r.gif' width='7' height='8' border='1' title='".LANG_STATISTICS_TITLE_STATUS_3." (".$delay." ".LANG_DAYS.")'>";
	}
	$stmp .= "&nbsp;</td></tr></table>";

	return $stmp;
}

function bcgGenerator($statuspgto, $canais, $fpgto, $datainicio, $datatermino, $connid, $itensgeral, $groupgeral, $itensespec, $groupespec, $where, $itensespec2, $tipoconsulta) {
    //    echo 'statuspgto: '.$statuspgto.'<br>'; // filtro ok
    //    echo 'canais: ['.$canais.']<br>'; // filtro ok
    //    echo 'fpgto: '.$fpgto.'<br>';  //  filtro ok
    //    echo 'datainicio: '.$datainicio.'<br>'; //  filtro ok
    //    echo 'datatermino: '.$datatermino.'<br>'; //  filtro ok
    //    
    
    //echo 'itensgeral: '.$itensgeral.'<br>';
    //echo 'groupgeral: '.$groupgeral.'<br>';
    //echo 'itensespec: '.$itensespec.'<br>';
    //echo 'groupespec: '.$groupespec.'<br>';
    //echo 'where: '.$where.'<br>';
    //die('stop');
    //
    //echo '<hr>';

    if( $statuspgto == 0 || (empty($statuspgto)))  {
        $statuspgto = 5;
    }
    
    if(empty($canais)) {
        $canais = 0;
    }

    $datainicio .= " 00:00:00";
    $datatermino .= " 23:59:59";
    
    if($tipoconsulta == 'O') {
    //por operadora
    $sql = "SELECT 
				operadora_codigo, sum(n) as n_total, sum(vendas) as venda_total, opr.opr_nome as publisher $itensgeral  
			FROM ( ";
	if($canais==='P' || empty($canais)) {
	
	if($statuspgto==5) {
		$aux_data_pos = "ve_data_confirmado";
	}
	else {
		$aux_data_pos = "ve_data_inclusao";
	}
	$sql_union[] = "
					(SELECT 'P' AS canal, ve_opr_codigo AS operadora_codigo, count(*) as n, sum(ve_valor) as vendas $itensespec 
					FROM 
					dist_vendas_pos 
					WHERE 
					ve_data_inclusao>='$datainicio' AND ve_data_inclusao<='$datatermino' 
					GROUP BY ve_opr_codigo $groupgeral 
					ORDER BY vendas DESC
					)
		UNION ALL 
					(SELECT 
					'P' AS canal,
					pogp.ogp_opr_codigo AS operadora_codigo,
					cast(Sum(1) as int8) AS n,
					cast(Sum(ve.ve_valor)  as int8) AS vendas $itensespec
					FROM
					dist_vendas_rede ve
					INNER JOIN tb_pos_operadora_games_produto pogp ON  ve.ve_ogp_id = pogp.ogp_id 
					WHERE
					ve.$aux_data_pos >='2012-06-24 00:00:00' AND ve.$aux_data_pos <= '2012-07-24 23:59:59' 
					AND ve.ve_status_confirmado = '1' 
					GROUP BY operadora_codigo  
					ORDER BY vendas DESC
					) 
			";
	}//end if($canais==='P' || empty($canais))
    if($canais==='S' || empty($canais)) {
	$sql_union[] = "
			(SELECT case when vg.vg_ug_id = '7909' then 'S' when vg.vg_ug_id != '7909' then 'S' end as canal,vgm_opr_codigo AS operadora_codigo, sum(vgm.vgm_qtde) as n, 
			sum(vgm.vgm_valor * vgm.vgm_qtde ) as vendas $itensespec2 
			FROM 
			tb_venda_games vg 
			INNER JOIN tb_venda_games_modelo vgm ON vgm.vgm_vg_id = vg.vg_id
			WHERE 
			vg.vg_ultimo_status='$statuspgto' 
			AND vgm_opr_codigo>0 AND vg.vg_data_concilia>='$datainicio' AND vg.vg_data_concilia<='$datatermino' 
			GROUP BY canal, vgm_opr_codigo,vg.vg_ug_id $groupespec  
			ORDER BY vendas DESC
			)
		UNION ALL
			(SELECT 
			'S' as canal, vgm_opr_codigo AS operadora_codigo, 
			sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde ) as vendas $itensespec2 
			FROM 
			tb_dist_venda_games vg 
			INNER JOIN tb_dist_venda_games_modelo vgm ON vgm.vgm_vg_id = vg.vg_id 
			WHERE vg.vg_ultimo_status='$statuspgto' AND vgm_opr_codigo>0 
			AND vg.vg_data_inclusao>='$datainicio' AND vg.vg_data_inclusao<='$datatermino' 
			GROUP BY canal, vgm_opr_codigo $groupespec  
			ORDER BY vendas DESC
			) ";
	}//end if($canais==='S' || empty($canais))
	if($canais==='A' || empty($canais)) {
	$sql_union[] = "
			(SELECT case when vg.vg_ug_id = '7909' then 'A' when vg.vg_ug_id != '7909' then 'A' end as canal,vgm_opr_codigo AS operadora_codigo, sum(vgm.vgm_qtde) as n, 
			sum(vgm.vgm_valor * vgm.vgm_qtde ) as vendas $itensespec2 
			FROM 
			tb_venda_games vg 
			INNER JOIN tb_venda_games_modelo vgm ON vgm.vgm_vg_id = vg.vg_id
			WHERE 
			vg.vg_ultimo_status='$statuspgto' 
			AND vg.vg_ultimo_status_obs like '%AtimoPay%'
			AND vg.vg_http_referer_origem = 'ATIMO' 
			AND vgm_opr_codigo>0 AND vg.vg_data_concilia>='$datainicio' AND vg.vg_data_concilia<='$datatermino' 
			GROUP BY canal, vgm_opr_codigo,vg.vg_ug_id $groupespec  
			ORDER BY vendas DESC
			)
			
		 ";
	}
    if($canais==='L' || empty($canais)) {
	$sql_union[] = "
			(SELECT 
			'L' as canal, vgm_opr_codigo AS operadora_codigo, 
			sum(vgm.vgm_qtde) as n, sum(vgm.vgm_valor * vgm.vgm_qtde ) as vendas $itensespec2 
			FROM 
			tb_dist_venda_games vg
			INNER JOIN tb_dist_venda_games_modelo vgm ON vgm.vgm_vg_id = vg.vg_id 
			WHERE 
			vg.vg_ultimo_status='$statuspgto' AND vgm_opr_codigo>0 
			AND vg.vg_data_inclusao>='$datainicio' AND vg.vg_data_inclusao<='$datatermino' 
			GROUP BY canal, vgm_opr_codigo $groupespec  
			ORDER BY vendas DESC
			) 
    ";
	}//end if($canais==='L' || empty($canais)) 
	if($canais==='C' || empty($canais)) {
	$sql_union[] = "
			(
			SELECT 
			'C' as canal, vc_total_5k as n, vc_total_5k*13 as vendas, 13 as operadora_codigo $itensespec from dist_vendas_cartoes_tmp 
			WHERE 
			vc_total_5k>0 AND vc_data>='$datainicio' AND vc_data<='$datatermino'  
			GROUP BY  operadora_codigo, vc_total_5k $groupgeral  
			ORDER BY vendas DESC
			)
        UNION ALL 
			(
			SELECT 
			'C' as canal, vc_total_10k as n, vc_total_10k*25 as vendas, 13 as operadora_codigo $itensespec 
			FROM dist_vendas_cartoes_tmp 
			WHERE 
			vc_total_10k>0 and vc_data>='$datainicio' AND vc_data<='$datatermino'  
			GROUP BY operadora_codigo,vc_total_10k $groupgeral  
			ORDER BY vendas DESC
			)
        UNION ALL
			(
			SELECT 'C' as canal, vc_total_15k as n, vc_total_15k*37 as vendas, 13 as operadora_codigo $itensespec
			FROM 
			dist_vendas_cartoes_tmp 
			WHERE
			vc_total_15k>0 AND vc_data>='$datainicio' AND vc_data<='$datatermino'  
			GROUP BY operadora_codigo,vc_total_15k $groupgeral 
			ORDER BY vendas DESC
			)
        UNION ALL 
			(
			SELECT
			'C' as canal, vc_total_20k as n, vc_total_20k*49 as vendas,13 as operadora_codigo $itensespec 
			FROM
			dist_vendas_cartoes_tmp 
			WHERE 
			vc_total_20k>0 AND vc_data>='$datainicio' AND vc_data<='$datatermino'  
			GROUP BY operadora_codigo,vc_total_20k $groupgeral      
			ORDER BY vendas DESC
			)
    ";
	} //end if($canais==='C' || empty($canais))
	$sql_aux = implode("\n\t\t UNION ALL \n", $sql_union);
	$sql .= $sql_aux."
        ) A  
        INNER JOIN operadoras opr ON opr.opr_codigo = a.operadora_codigo 
        WHERE  1= 1 
        $where 
        GROUP BY operadora_codigo ,opr.opr_nome $groupgeral    
        ORDER BY venda_total DESC";    
} else {
	// por game
    $sql  = "SELECT
             jogo_nome, 
             sum(cast(n as int8)) as n_total, 
             sum(cast(vendas as decimal)) as venda_total, 
             publisher  
             $itensgeral
             FROM (";
    if($canais==='P' || empty($canais)) {
	if($statuspgto==5) {
		$aux_data_pos = "ve_data_confirmado";
		$aux_data_pos_vg = "vg_data_concilia";
	}
	else {
		$aux_data_pos = "ve_data_inclusao";
		$aux_data_pos_vg = "vg_data_inclusao";
	}
	$sql_union[] = "
					(SELECT  
					'P' AS canal, 
					cast (ve_opr_codigo as int8) AS publisher,
					cast (case when ve_jogo = 'OG' then 'Metin2' when ve_jogo = 'HB' then 'Habbo Hotel' when ve_jogo = 'MU' then 'MU Online' end as varchar(255)) AS jogo_nome,					
					cast(count(*) as int8) as n, 
					cast (sum(ve_valor) as decimal) as vendas 
					$itensespec 
					FROM
					dist_vendas_pos 
					WHERE
					ve_data_inclusao>='$datainicio' AND ve_data_inclusao<='$datatermino' 
					GROUP BY publisher,jogo_nome $groupgeral
					)
	--	UNION ALL 
	--				(SELECT 
	--				'P' AS canal,
	--				cast (ve_opr_codigo as int8) AS publisher,
	--				cast (ogp.ogp_nome as varchar(255)) AS jogo_nome,
	--				cast(Sum(1) as int8) AS n,
	--				cast(Sum(ve.ve_valor)  as decimal) AS vendas 
	--				FROM
	--				dist_vendas_rede ve
	--				INNER JOIN tb_pos_operadora_games_produto ogp ON  ve.ve_ogp_id = ogp.ogp_id 
	--				WHERE
	--				ve.$aux_data_pos>='$datainicio' AND ve.$aux_data_pos<='$datatermino' 
	--				AND ve.ve_status_confirmado = '1' 
	--				GROUP BY publisher,jogo_nome  
	--				) 
		UNION ALL 
					(SELECT 
					'P' as canal, 
					cast (vgm.vgm_opr_codigo as int8) AS publisher,
					cast (vgm.vgm_nome_produto  as varchar(255)) AS jogo_nome,
					cast(sum(vgm.vgm_qtde) as int8) as n, 
					cast(sum(vgm.vgm_valor * vgm.vgm_qtde) as decimal) as vendas 
					FROM 
					tb_venda_games vg 
					INNER JOIN tb_venda_games_modelo vgm ON vgm.vgm_vg_id = vg.vg_id 
					INNER JOIN tb_venda_games_pinepp_origem tvgpo ON tvgpo.vg_id = vg.vg_id 
					WHERE 
					vg.vg_ultimo_status='5' 
					AND SUBSTR(tvgpo.tvgpo_canal, 1, 1) ='P' 
					AND vg.vg_pagto_tipo = 13 
					AND vg.$aux_data_pos_vg>='$datainicio' AND vg.$aux_data_pos_vg<='$datatermino' 
					GROUP BY jogo_nome, publisher 
					) 
			";
	}//end if($canais==='P' || empty($canais))
    if($canais==='L' || empty($canais)) {
	if($statuspgto==5) {
		$aux_data_vg = "vg_data_concilia";
	}
	else {
		$aux_data_vg = "vg_data_inclusao";
	}
	$sql_union[] = "
					(SELECT 
					'L' AS canal,
					cast (vgm.vgm_opr_codigo as int8) AS publisher,
					cast (vgm.vgm_nome_produto  as varchar(255)) AS jogo_nome,
					cast(Sum(vgm.vgm_qtde) as int8) AS n,
					cast(Sum(vgm.vgm_valor * vgm.vgm_qtde)  as decimal) AS vendas 
					$itensespec2 
					FROM
					tb_dist_venda_games AS vg
					INNER JOIN tb_dist_venda_games_modelo AS vgm ON vgm.vgm_vg_id = vg.vg_id
					WHERE
					vg.vg_ultimo_status = '$statuspgto'	
					AND vg.vg_data_inclusao>='$datainicio' AND vg.vg_data_inclusao<= '$datatermino' 
					GROUP BY publisher,jogo_nome $groupespec) 
		UNION ALL 
					(SELECT 
					'L' as canal, 
					cast (vgm.vgm_opr_codigo as int8) AS publisher,
					cast (vgm.vgm_nome_produto  as varchar(255)) AS jogo_nome,
					cast(sum(vgm.vgm_qtde) as int8) as n, 
					cast(sum(vgm.vgm_valor * vgm.vgm_qtde) as decimal) as vendas 
					FROM 
					tb_venda_games vg 
					INNER JOIN tb_venda_games_modelo vgm ON vgm.vgm_vg_id = vg.vg_id 
					INNER JOIN tb_venda_games_pinepp_origem tvgpo ON tvgpo.vg_id = vg.vg_id 
					WHERE 
					vg.vg_ultimo_status='5' 
					AND tvgpo.tvgpo_canal='L' 
					AND vg.vg_pagto_tipo = 13 
					AND vg.$aux_data_vg>='$datainicio' AND vg.$aux_data_vg<='$datatermino' 
					GROUP BY jogo_nome, publisher 
					) ";
    }//end if($canais==='L' || empty($canais)) 
	if($canais==='S' || empty($canais)) {
	if($statuspgto==5) {
		$aux_data_vg = "vg_data_concilia";
	}
	else {
		$aux_data_vg = "vg_data_inclusao";
	}
	if($canais==='S') {
		$sql_union[] = "  
					(SELECT 
					'S' AS canal,
					cast (vgm.vgm_opr_codigo as int8) AS publisher,
					cast (vgm.vgm_nome_produto  as varchar(255)) AS jogo_nome,
					cast(Sum(vgm.vgm_qtde) as int8) AS n,
					cast(Sum(vgm.vgm_valor * vgm.vgm_qtde)  as decimal) AS vendas 
					$itensespec2 
					FROM
					tb_dist_venda_games AS vg
					INNER JOIN tb_dist_venda_games_modelo AS vgm ON vgm.vgm_vg_id = vg.vg_id
					WHERE
					vg.vg_ultimo_status = '$statuspgto'	
					AND vg.vg_data_inclusao>='$datainicio' AND vg.vg_data_inclusao<= '$datatermino' 
					GROUP BY publisher,jogo_nome $groupespec ) 
		UNION ALL 
					(SELECT 
					'S' as canal, 
					cast (vgm.vgm_opr_codigo as int8) AS publisher,
					cast (vgm.vgm_nome_produto  as varchar(255)) AS jogo_nome,
					cast(sum(vgm.vgm_qtde) as int8) as n, 
					cast(sum(vgm.vgm_valor * vgm.vgm_qtde) as decimal) as vendas 
					FROM 
					tb_venda_games vg 
					INNER JOIN tb_venda_games_modelo vgm ON vgm.vgm_vg_id = vg.vg_id 
					INNER JOIN tb_venda_games_pinepp_origem tvgpo ON tvgpo.vg_id = vg.vg_id 
					WHERE 
					vg.vg_ultimo_status='5' 
					AND tvgpo.tvgpo_canal='L' 
					AND vg.vg_pagto_tipo = 13 
					AND vg.$aux_data_vg>='$datainicio' AND vg.$aux_data_vg<='$datatermino' 
					GROUP BY jogo_nome, publisher) ";
    }//end if($canais==='S')
	$sql_union[] = "
					(SELECT 
					'S' as canal,
					cast (vgm.vgm_opr_codigo as int8) AS publisher,
					cast (vgm.vgm_nome_produto  as varchar(255)) AS jogo_nome,
					cast(sum(vgm.vgm_qtde) as int8) as n, 
					cast(sum(vgm.vgm_valor * vgm.vgm_qtde) as decimal) as vendas 
					$itensespec2 
					FROM
					tb_venda_games vg 
					INNER JOIN tb_venda_games_modelo vgm ON vgm.vgm_vg_id = vg.vg_id 
					WHERE
					vg.vg_ultimo_status= '$statuspgto' 
					AND vg.vg_pagto_tipo != 13
					AND vg.$aux_data_vg>='$datainicio' AND vg.$aux_data_vg<='$datatermino' 
					GROUP BY jogo_nome,canal,publisher 
					) 
		UNION ALL 
					(SELECT 
					'S' as canal,
					cast (vgm.vgm_opr_codigo as int8) AS publisher,
					cast (vgm.vgm_nome_produto  as varchar(255)) AS jogo_nome,
					cast(sum(vgm.vgm_qtde) as int8) as n, 
					cast(sum(vgm.vgm_valor * vgm.vgm_qtde) as decimal) as vendas 
					$itensespec2 
					FROM 
					tb_venda_games vg 
					INNER JOIN tb_venda_games_modelo vgm ON vgm.vgm_vg_id = vg.vg_id 
					INNER JOIN tb_venda_games_pinepp_origem tvgpo ON tvgpo.vg_id = vg.vg_id 
					WHERE 
					vg.vg_ultimo_status='$statuspgto' AND tvgpo.tvgpo_canal='G' AND vg.vg_pagto_tipo = 13 
					AND vg.$aux_data_vg>='$datainicio' AND vg.$aux_data_vg<='$datatermino' 
					GROUP BY jogo_nome,canal,publisher 
					)";
    }  //end if($canais==='S' || empty($canais))

    if($canais==='A' || empty($canais)) {
	
		if($statuspgto==5) {
			$aux_data_vg = "vg_data_concilia";
		}
		else {
			$aux_data_vg = "vg_data_inclusao";
		}

		$sql_union[] = "
				(SELECT 
				'A' as canal, 
				cast (vgm.vgm_opr_codigo as int8) AS publisher,
				cast (vgm.vgm_nome_produto  as varchar(255)) AS jogo_nome,
				cast(sum(vgm.vgm_qtde) as int8) as n, 
				cast(sum(vgm.vgm_valor * vgm.vgm_qtde) as decimal) as vendas
				FROM 
				tb_venda_games vg 
				INNER JOIN tb_venda_games_modelo vgm ON vgm.vgm_vg_id = vg.vg_id 
				WHERE 
				vg.vg_ultimo_status='5' 
				AND vg.vg_pagto_tipo = 13 
				AND vg.vg_ultimo_status_obs like '%AtimoPay%'
				AND vg.vg_http_referer_origem = 'ATIMO' 
				AND vg.$aux_data_vg>='$datainicio' AND vg.$aux_data_vg<='$datatermino' 
				GROUP BY jogo_nome, canal, publisher)
	
		";
		
	}	
    if($canais==='C' || empty($canais)) {
	
	$sql_union[] = "
					(SELECT  
					'C' as canal,
					cast ('17' as int8)  as   publisher ,
					'MU Online' as jogo_nome, 
					cast(vc_total_mu_online  as int8) as n, 
					cast(vc_total_mu_online*10 as decimal) as vendas 
					$itensespec 
					FROM 
					dist_vendas_cartoes_tmp 
					WHERE 
					vc_total_mu_online>0 
					AND vc_data>='$datainicio' AND vc_data<='$datatermino'  
					GROUP BY publisher, jogo_nome, vc_total_mu_online $groupgeral 
					)
				UNION ALL 
					(SELECT  
					'C' as canal, 
					cast ('13' as int8)   as publisher ,
					'Metin2' as jogo_nome, 
					cast(vc_total_5k  as int8)  as n, 
					cast(vc_total_5k*13 as decimal) as vendas 
					$itensespec 
					FROM
					dist_vendas_cartoes_tmp 
					WHERE 
					vc_total_5k>0  
					AND vc_data>='$datainicio' AND vc_data<='$datatermino' 
					GROUP BY publisher, jogo_nome, vc_total_mu_online, vc_total_5k $groupgeral 
					)
                UNION ALL 
					(SELECT  
					'C' as canal, 
					cast ('13' as int8) as publisher ,
					'Metin2' as jogo_nome , 
					cast(vc_total_10k as int8) as n, 
					cast(vc_total_10k*25 as decimal) as vendas 
					$itensespec 
					FROM 
					dist_vendas_cartoes_tmp 
					WHERE
					vc_total_10k>0  
					AND vc_data>='$datainicio' AND vc_data<='$datatermino' 
					GROUP BY publisher, jogo_nome, vc_total_mu_online, vc_total_10k $groupgeral 
					)
                UNION ALL 
					(SELECT  
					'C' as canal, 
					cast ('13' as int8) as publisher,
					'Metin2' as jogo_nome, 
					cast(vc_total_15k  as int8) as n, 
					cast(vc_total_15k*37 as decimal) as vendas  
					$itensespec 
					FROM 
					dist_vendas_cartoes_tmp 
					WHERE vc_total_15k>0 
					AND vc_data>='$datainicio' AND vc_data<='$datatermino' 
					GROUP BY publisher, jogo_nome, vc_total_mu_online, vc_total_15k $groupgeral 
					)
                UNION ALL 
					(SELECT 
					'C' as canal, 
					cast ('13' as int8) as publisher, 
					'Metin2' as jogo_nome, 
					cast(vc_total_20k  as int8) as n, 
					cast(vc_total_20k*49 as decimal) as vendas 
					$itensespec 
					FROM 
					dist_vendas_cartoes_tmp 
					WHERE 
					vc_total_20k>0 
					AND vc_data>='$datainicio' AND vc_data<='$datatermino' 
					GROUP BY publisher, jogo_nome, vc_total_mu_online, vc_total_20k $groupgeral 
					)
                ";
    }//end if($canais==='C' || empty($canais))
    $sql_aux = implode("\n\t\t UNION ALL \n", $sql_union);
	$sql .= $sql_aux."
		) A  
    WHERE  1= 1 
    $where 
    GROUP BY publisher,jogo_nome $groupgeral 
    ORDER BY venda_total DESC
    ";


}

	//echo 'SQL:<pre>'.$sql.'</pre><br><br>';
    //die('stop');
    $rss = pg_exec($connid, $sql);
    return $rss;
}

// ==========================================================================
function getSQLTotaisporJogos($opr_codigo=null,$jogo_nome=null,$data_inicial=null,$data_final=null,$pins_valor=null) {

	//ATENÇÃO: para novos canais deve ser cadastrado no vetor de canais no inicio do programa financial_totais_por_jogos.php 
	

	//Transformando $jogo_nome em array para testes dos nomes
	$jogo_nome_aux = str_replace("'","",$jogo_nome);
	$jogo_nome_aux = explode(',',$jogo_nome_aux);

	//Transformando $pins_valor em array para testes dos valores
	$pins_valor_aux = str_replace("'","",$pins_valor);
	$pins_valor_aux = explode(',',$pins_valor_aux);

	//Transformando a Data no formato para Banco de Dados
	$data_inicial_aux  = explode('/',$data_inicial);
	$data_inicial = $data_inicial_aux[2].'-'.$data_inicial_aux[1].'-'.$data_inicial_aux[0]; 
	$data_final_aux  = explode('/',$data_final);
	$data_final = $data_final_aux[2].'-'.$data_final_aux[1].'-'.$data_final_aux[0]; 

	//Montando o SQL
	$sql = "
	SELECT
		 mes,
		 publisher,
		 upper(jogo_nome) as jogo_nome,
		 canal,
		 sum(n) as n_total, 
		 sum(vendas) as venda_total
		 
		 FROM (
	-- inicio POS --";
	 if ((in_array('Metin2', $jogo_nome_aux))||(in_array('Habbo Hotel', $jogo_nome_aux))||(in_array('MU Online', $jogo_nome_aux))||is_null($jogo_nome)) {
		 $sql .= "	
					(SELECT  
					'P' AS canal, 
					date_trunc('month', ve_data_inclusao) as mes,
					cast (ve_opr_codigo as int8) AS publisher,
					-- 'OG' -> Metin2, 'HB' -> 'Habbo', 'MU' -> 'MU Online'
					cast (case when ve_jogo = 'OG' then 'Metin2' when ve_jogo = 'HB' then 'Habbo Hotel' when ve_jogo = 'MU' then 'MU Online' end as varchar(255)) AS jogo_nome,					
					cast(count(*) as int8) as n, 
					cast (sum(ve_valor) as decimal) as vendas 				 
					FROM
					dist_vendas_pos 
					WHERE
					1 = 1";
		if($data_inicial) {
			$sql .= "	
					AND ve_data_inclusao>='".$data_inicial." 00:00:00' ";
		}//end if($data_inicial)
		if($data_final) {
			$sql .= "	
					AND ve_data_inclusao<='".$data_final." 23:59:59' ";
		}//end if($data_final)
		if($opr_codigo) {
			$sql .= "	
					AND ve_opr_codigo=".$opr_codigo;
		}//end if($opr_codigo)
		if($pins_valor) {
			 $sql .= "	
					AND ve_valor IN (" .$pins_valor. ")";
		}//end if($pins_valor)
		 $sql .= "
					GROUP BY jogo_nome,mes,publisher 
					)
		UNION ALL ";
	 }//end  if ((in_array('Metin2', $jogo_nome_aux))||(in_array('Habbo Hotel', $jogo_nome_aux))||(in_array('MU Online', $jogo_nome_aux))||is_null($jogo_nome))
	 $sql .= "
	-- POS contendo pagamento com PIN CASH que foram comprados em P* (Redes POS)
					(SELECT 
					'P' as canal, 
					date_trunc('month', vg.vg_data_concilia) as mes,
					cast (vgm.vgm_opr_codigo as int8) AS publisher,
					cast (vgm.vgm_nome_produto  as varchar(255)) AS jogo_nome,
					cast(sum(vgm.vgm_qtde) as int8) as n, 
					cast(sum(vgm.vgm_valor * vgm.vgm_qtde) as decimal) as vendas 
					FROM 
					tb_venda_games vg 
					INNER JOIN tb_venda_games_modelo vgm ON vgm.vgm_vg_id = vg.vg_id 
					INNER JOIN tb_venda_games_pinepp_origem tvgpo ON tvgpo.vg_id = vg.vg_id 
					WHERE 
					vg.vg_ultimo_status='5' 
					AND SUBSTR(tvgpo.tvgpo_canal, 1, 1) ='P' 
					AND vg.vg_pagto_tipo = 13 ";
	if($data_inicial) {
		$sql .= "	
				AND vg.vg_data_concilia>='".$data_inicial." 00:00:00' ";
	}//end if($data_inicial)
	if($data_final) {
		$sql .= "	
				AND vg.vg_data_concilia<='".$data_final." 23:59:59' ";
	}//end if($data_final)
	if($opr_codigo) {
	 $sql .= "	
				AND vgm.vgm_opr_codigo=".$opr_codigo;
	}//end if($opr_codigo)
	if($jogo_nome) {
		 $sql .= "	
				AND vgm_nome_produto IN ('" . $jogo_nome . "')";
	}//end if($jogo_nome)
	if($pins_valor) {
		 $sql .= "	
				AND vgm_valor IN (" .$pins_valor. ")";
	}//end if($pins_valor)
	$sql .= " 
					GROUP BY jogo_nome, mes,publisher 
					) 

	-- inicio trecho vendas redesim  // ver com o Reynaldo quando desbloquear
	--	UNION ALL 
	--				(SELECT 
	--				'P' AS canal,
	--				date_trunc('month', ve_data_confirmado) as mes,
	--				cast (ve_opr_codigo as int8) AS publisher,
	--				cast (ogp.ogp_nome as varchar(255)) AS jogo_nome,
	--				cast(Sum(1) as int8) AS n,
	--				cast(Sum(ve.ve_valor)  as decimal) AS vendas 
	--				FROM
	--				dist_vendas_rede ve
	--				INNER JOIN tb_pos_operadora_games_produto ogp ON  ve.ve_ogp_id = ogp.ogp_id 
	--				WHERE
	--				ve.ve_status_confirmado = '1' ";
	if($data_inicial) {
		$sql .= "	
	--			AND ve.ve_data_confirmado>='".$data_inicial." 00:00:00' ";
	}//end if($data_inicial)
	if($data_final) {
		$sql .= "	
	--			AND ve.ve_data_confirmado<='".$data_final." 23:59:59' ";
	}//end if($data_final)
	if($opr_codigo) {
		 $sql .= "	
	--			AND ve_opr_codigo=".$opr_codigo;
	}//end if($opr_codigo)
	if($jogo_nome) {
		 $sql .= "	
	--			AND ogp.ogp_nome IN ('" . $jogo_nome . "')";
	}//end if($jogo_nome)
	if($pins_valor) {
		 $sql .= "	
	--			AND ve.ve_valor IN (" .$pins_valor. ")";
	}//end if($pins_valor)
	$sql .= " 
	--				AND ve.ve_status_confirmado = '1' 
	--				GROUP BY jogo_nome,mes,publisher  
	--				) 
	-- fim trecho vendas rede sim
	-- fim POS ---
		 UNION ALL 
	-- inicio LAN --
					(SELECT 
					'L' AS canal,
					date_trunc('month', vg.vg_data_inclusao) as mes,
					cast (vgm.vgm_opr_codigo as int8) AS publisher,
					cast (vgm.vgm_nome_produto  as varchar(255)) AS jogo_nome,
					cast(Sum(vgm.vgm_qtde) as int8) AS n,
					cast(Sum(vgm.vgm_valor * vgm.vgm_qtde)  as decimal) AS vendas 
					FROM
					tb_dist_venda_games AS vg
					INNER JOIN tb_dist_venda_games_modelo AS vgm ON vgm.vgm_vg_id = vg.vg_id
					--INNER JOIN tb_dist_operadora_games_produto AS ogp ON vgm.vgm_ogp_id = ogp.ogp_id
					WHERE
					vg.vg_ultimo_status = '5'";
	if($data_inicial) {
		$sql .= "	
				AND vg.vg_data_inclusao>='".$data_inicial." 00:00:00' ";
	}//end if($data_inicial)
	if($data_final) {
		$sql .= "	
				AND vg.vg_data_inclusao<='".$data_final." 23:59:59' ";
	}//end if($data_final)
	if($opr_codigo) {
		 $sql .= "	
				AND vgm.vgm_opr_codigo=".$opr_codigo;
	}//end if($opr_codigo)
	if($jogo_nome) {
		 $sql .= "	
				AND vgm.vgm_nome_produto IN ('" . $jogo_nome . "')";
	}//end if($jogo_nome)
	if($pins_valor) {
		 $sql .= "	
				AND vgm.vgm_valor IN (" .$pins_valor. ")";
	}//end if($pins_valor)
	$sql .= "  
					GROUP BY jogo_nome,mes,publisher  
					) 
	-- LAN contendo pagamento com PIN CASH que foram comprados em LANs
		UNION ALL 
					(SELECT 
					'L' as canal, 
					date_trunc('month', vg.vg_data_concilia) as mes,
					cast (vgm.vgm_opr_codigo as int8) AS publisher,
					cast (vgm.vgm_nome_produto  as varchar(255)) AS jogo_nome,
					cast(sum(vgm.vgm_qtde) as int8) as n, 
					cast(sum(vgm.vgm_valor * vgm.vgm_qtde) as decimal) as vendas 
					FROM 
					tb_venda_games vg 
					INNER JOIN tb_venda_games_modelo vgm ON vgm.vgm_vg_id = vg.vg_id 
					INNER JOIN tb_venda_games_pinepp_origem tvgpo ON tvgpo.vg_id = vg.vg_id 
					WHERE 
					vg.vg_ultimo_status='5' 
					AND tvgpo.tvgpo_canal='L' 
					AND vg.vg_pagto_tipo = 13 ";
	if($data_inicial) {
		$sql .= "	
				AND vg.vg_data_concilia>='".$data_inicial." 00:00:00' ";
	}//end if($data_inicial)
	if($data_final) {
		$sql .= "	
				AND vg.vg_data_concilia<='".$data_final." 23:59:59' ";
	}//end if($data_final)
	if($opr_codigo) {
		 $sql .= "	
				AND vgm.vgm_opr_codigo=".$opr_codigo;
	}//end if($opr_codigo)
	if($jogo_nome) {
		 $sql .= "	
				AND vgm.vgm_nome_produto IN ('" . $jogo_nome . "')";
	}//end if($jogo_nome)
	if($pins_valor) {
		 $sql .= "	
				AND vgm.vgm_valor IN (" .$pins_valor. ")";
	}//end if($pins_valor)
	$sql .= "  
					GROUP BY jogo_nome, mes,publisher 
					) 
	-- fim LAN ---
		 UNION ALL 
	-- inicio Gamer --
					(SELECT 
					case when vg.vg_ug_id = '7909' then 'E' when vg.vg_ug_id != '7909' then 'M' end as canal,
					date_trunc('month', vg.vg_data_concilia) as mes,
					cast (vgm.vgm_opr_codigo as int8) AS publisher,
					cast (vgm.vgm_nome_produto  as varchar(255)) AS jogo_nome,
					cast(sum(vgm.vgm_qtde) as int8) as n, 
					cast(sum(vgm.vgm_valor * vgm.vgm_qtde) as decimal) as vendas 
					FROM
					tb_venda_games vg 
					INNER JOIN tb_venda_games_modelo vgm ON vgm.vgm_vg_id = vg.vg_id 
					--INNER JOIN tb_operadora_games_produto AS ogp ON vgm.vgm_nome_produto = ogp.ogp_nome
					WHERE
					vg.vg_ultimo_status= '5' 
					AND vg.vg_pagto_tipo != 13";
	if($data_inicial) {
		$sql .= "	
				AND vg.vg_data_concilia>='".$data_inicial." 00:00:00' ";
	}//end if($data_inicial)
	if($data_final) {
		$sql .= "	
				AND vg.vg_data_concilia<='".$data_final." 23:59:59' ";
	}//end if($data_final)
	if($opr_codigo) {
		 $sql .= "	
				AND vgm.vgm_opr_codigo=".$opr_codigo;
	}//end if($opr_codigo)
	if($jogo_nome) {
		 $sql .= "	
				AND vgm.vgm_nome_produto IN ('" . $jogo_nome . "')";
	}//end if($jogo_nome)
	if($pins_valor) {
		 $sql .= "	
				AND vgm.vgm_valor IN (" .$pins_valor. ")";
	}//end if($pins_valor)
	
	
	$sql .= "  
					GROUP BY jogo_nome,mes,canal,publisher 
					) 
		 UNION ALL 
	-- inicio Gamer atimo --
					(SELECT 
					case when vg.vg_ug_id = '7909' then 'E' when vg.vg_ug_id != '7909' then 'A' end as canal,
					date_trunc('month', vg.vg_data_concilia) as mes,
					cast (vgm.vgm_opr_codigo as int8) AS publisher,
					cast (vgm.vgm_nome_produto  as varchar(255)) AS jogo_nome,
					cast(sum(vgm.vgm_qtde) as int8) as n, 
					cast(sum(vgm.vgm_valor * vgm.vgm_qtde) as decimal) as vendas 
					FROM
					tb_venda_games vg 
					INNER JOIN tb_venda_games_modelo vgm ON vgm.vgm_vg_id = vg.vg_id 
					--INNER JOIN tb_operadora_games_produto AS ogp ON vgm.vgm_nome_produto = ogp.ogp_nome
					WHERE
					vg.vg_ultimo_status= '5' 
					AND vg.vg_ultimo_status_obs like '%AtimoPay%'
					AND vg.vg_http_referer_origem = 'ATIMO'
					AND vg.vg_pagto_tipo = 13";
	if($data_inicial) {
		$sql .= "	
				AND vg.vg_data_concilia>='".$data_inicial." 00:00:00' ";
	}//end if($data_inicial)
	if($data_final) {
		$sql .= "	
				AND vg.vg_data_concilia<='".$data_final." 23:59:59' ";
	}//end if($data_final)
	if($opr_codigo) {
		 $sql .= "	
				AND vgm.vgm_opr_codigo=".$opr_codigo;
	}//end if($opr_codigo)
	if($jogo_nome) {
		 $sql .= "	
				AND vgm.vgm_nome_produto IN ('" . $jogo_nome . "')";
	}//end if($jogo_nome)
	if($pins_valor) {
		 $sql .= "	
				AND vgm.vgm_valor IN (" .$pins_valor. ")";
	}//end if($pins_valor)
	
	
	
	$sql .= "  
					GROUP BY jogo_nome,mes,canal,publisher 
					) 
	-- Gamer contendo pagamento PIN CASH que foram comprados por GAMERS
		UNION ALL 
					(SELECT 
					case when vg.vg_ug_id = '7909' then 'E' when vg.vg_ug_id != '7909' then 'M' end as canal,
					date_trunc('month', vg.vg_data_concilia) as mes,
					cast (vgm.vgm_opr_codigo as int8) AS publisher,
					cast (vgm.vgm_nome_produto  as varchar(255)) AS jogo_nome,
					cast(sum(vgm.vgm_qtde) as int8) as n, 
					cast(sum(vgm.vgm_valor * vgm.vgm_qtde) as decimal) as vendas 
					FROM 
					tb_venda_games vg 
					INNER JOIN tb_venda_games_modelo vgm ON vgm.vgm_vg_id = vg.vg_id 
					INNER JOIN tb_venda_games_pinepp_origem tvgpo ON tvgpo.vg_id = vg.vg_id 
					WHERE 
					vg.vg_ultimo_status='5' AND tvgpo.tvgpo_canal='G' AND vg.vg_pagto_tipo = 13 ";
	if($data_inicial) {
		$sql .= "	
				AND vg.vg_data_concilia>='".$data_inicial." 00:00:00' ";
	}//end if($data_inicial)
	if($data_final) {
		$sql .= "	
				AND vg.vg_data_concilia<='".$data_final." 23:59:59' ";
	}//end if($data_final)
	if($opr_codigo) {
		 $sql .= "	
				AND vgm.vgm_opr_codigo=".$opr_codigo;
	}//end if($opr_codigo)
	if($jogo_nome) {
		 $sql .= "	
				AND vgm.vgm_nome_produto IN ('" . $jogo_nome . "')";
	}//end if($jogo_nome)
	if($pins_valor) {
		 $sql .= "	
				AND vgm.vgm_valor IN (" .$pins_valor. ")";
	}//end if($pins_valor)
	$sql .= "  
					GROUP BY jogo_nome,mes,canal,publisher 
					) 
	-- fim Gamer ---
	-- inicio Cartoes --";
	 if (in_array('MU Online', $jogo_nome_aux)||is_null($jogo_nome)) {
		if($opr_codigo == '17'||is_null($opr_codigo)) {
			if (in_array('10', $pins_valor_aux)||is_null($pins_valor)) {
				$sql .= " 
		 UNION ALL 
					(SELECT  
					'C' as canal,
					date_trunc('month', vc_data) as mes,
					cast ('17' as int8) AS publisher,
					'MU Online'  as   jogo_nome ,
					cast(vc_total_mu_online as int8) as n, 
					cast(vc_total_mu_online*10 as decimal) as vendas 
					FROM 
					dist_vendas_cartoes_tmp 
					WHERE 
					vc_total_mu_online>0 ";
				if($data_inicial) {
					$sql .= "	
				AND vc_data>='".$data_inicial." 00:00:00' ";
				}//end if($data_inicial)
				if($data_final) {
					$sql .= "	
				AND vc_data<='".$data_final." 23:59:59' ";
				}//end if($data_final)    
				$sql .= "  
					GROUP BY jogo_nome, vc_total_mu_online,mes,publisher  
					)";
			}//end 	if (in_array('10', $pins_valor_aux)||is_null($pins_valor)) 
		}//end if($opr_codigo == '17'||is_null($opr_codigo)) 
	}//end if (in_array('MU Online', $jogo_nome_aux)||is_null($jogo_nome))
	if (in_array('Metin2', $jogo_nome_aux)||is_null($jogo_nome)) {
		if($opr_codigo == '13'||is_null($opr_codigo)) {
			if (in_array('13', $pins_valor_aux)||is_null($pins_valor)) {
				$sql .= " 
		UNION ALL 
					(SELECT  
					'C' as canal, 
					date_trunc('month', vc_data) as mes,
					cast ('13' as int8) AS publisher,
					'Metin2'   as jogo_nome ,
					cast(vc_total_5k as int8) as n, 
					cast(vc_total_5k*13 as decimal) as vendas 
					FROM
					dist_vendas_cartoes_tmp 
					WHERE 
					vc_total_5k>0 ";
				if($data_inicial) {
					$sql .= "	
				AND vc_data>='".$data_inicial." 00:00:00' ";
				}//end if($data_inicial)
				if($data_final) {
					$sql .= "	
				AND vc_data<='".$data_final." 23:59:59' ";
				}//end if($data_final)    
				$sql .= "     
					GROUP BY jogo_nome, vc_total_mu_online, vc_total_5k,mes,publisher  
					)";
			}//end if (in_array('13', $pins_valor_aux)||is_null($pins_valor))
			if (in_array('25', $pins_valor_aux)||is_null($pins_valor)) {
				$sql .= " 
		UNION ALL 
					(SELECT  
					'C' as canal, 
					date_trunc('month', vc_data) as mes,
					cast ('13' as int8) AS publisher,
					'Metin2' as jogo_nome ,
					cast(vc_total_10k as int8) as n, 
					cast(vc_total_10k*25 as decimal) as vendas 
					FROM 
					dist_vendas_cartoes_tmp 
					WHERE
					vc_total_10k>0 ";
				if($data_inicial) {
					$sql .= "	
				AND vc_data>='".$data_inicial." 00:00:00' ";
				}//end if($data_inicial)
				if($data_final) {
					$sql .= "	
				AND vc_data<='".$data_final." 23:59:59' ";
				}//end if($data_final)    
				$sql .= "     
					GROUP BY jogo_nome, vc_total_mu_online, vc_total_10k, mes,publisher  
					)";
			}//end if (in_array('25', $pins_valor_aux)||is_null($pins_valor))
			if (in_array('37', $pins_valor_aux)||is_null($pins_valor)) {
				$sql .= " 
		UNION ALL 
					(SELECT  
					'C' as canal, 
					date_trunc('month', vc_data) as mes,
					cast ('13' as int8) AS publisher,
					'Metin2' as jogo_nome,
					cast(vc_total_15k as int8) as n, 
					cast(vc_total_15k*37 as decimal) as vendas  
					FROM 
					dist_vendas_cartoes_tmp 
					WHERE vc_total_15k>0 ";
				if($data_inicial) {
					$sql .= "	
				AND vc_data>='".$data_inicial." 00:00:00' ";
				}//end if($data_inicial)
				if($data_final) {
					$sql .= "	
				AND vc_data<='".$data_final." 23:59:59' ";
				}//end if($data_final)    
				$sql .= "   
					GROUP BY jogo_nome, vc_total_mu_online, vc_total_15k, mes,publisher  
					)";
			}//end if (in_array('37', $pins_valor_aux)||is_null($pins_valor))
			if (in_array('49', $pins_valor_aux)||is_null($pins_valor)) {
				$sql .= " 
		UNION ALL 
					(SELECT 
					'C' as canal, 
					date_trunc('month', vc_data) as mes,
					cast ('13' as int8) AS publisher,
					'Metin2' as jogo_nome, 
					cast(vc_total_20k  as int8) as n, 
					cast(vc_total_20k*49 as decimal) as vendas 
					FROM 
					dist_vendas_cartoes_tmp 
					WHERE 
					vc_total_20k>0 ";
				if($data_inicial) {
					$sql .= "	
				AND vc_data>='".$data_inicial." 00:00:00' ";
				}//end if($data_inicial)
				if($data_final) {
					$sql .= "	
				AND vc_data<='".$data_final." 23:59:59' ";
				}//end if($data_final)    
				$sql .= "  
					GROUP BY jogo_nome, vc_total_mu_online, vc_total_20k, mes,publisher  
					)";
			}//end if (in_array('49', $pins_valor_aux)||is_null($pins_valor))
		}//end if($opr_codigo)
	}//end if (in_array('Metin2', $jogo_nome_aux)||is_null($jogo_nome))
	$sql .= "
	-- Gamer contendo pagamento PIN CASH que foram comprados com GoCASH
		UNION ALL 
					(SELECT 
					'C' as canal,
					date_trunc('month', vg.vg_data_concilia) as mes,
					cast (vgm.vgm_opr_codigo as int8) AS publisher,
					cast (vgm.vgm_nome_produto  as varchar(255)) AS jogo_nome,
					cast(sum(vgm.vgm_qtde) as int8) as n, 
					cast(sum(vgm.vgm_valor * vgm.vgm_qtde) as decimal) as vendas 
					FROM 
					tb_venda_games vg 
					INNER JOIN tb_venda_games_modelo vgm ON vgm.vgm_vg_id = vg.vg_id 
					INNER JOIN tb_venda_games_pinepp_origem tvgpo ON tvgpo.vg_id = vg.vg_id 
					WHERE 
					vg.vg_ultimo_status='5' AND tvgpo.tvgpo_canal='C' AND vg.vg_pagto_tipo = 13 ";
	if($data_inicial) {
		$sql .= "	
				AND vg.vg_data_concilia>='".$data_inicial." 00:00:00' ";
	}//end if($data_inicial)
	if($data_final) {
		$sql .= "	
				AND vg.vg_data_concilia<='".$data_final." 23:59:59' ";
	}//end if($data_final)
	if($opr_codigo) {
		 $sql .= "	
				AND vgm.vgm_opr_codigo=".$opr_codigo;
	}//end if($opr_codigo)
	if($jogo_nome) {
		 $sql .= "	
				AND vgm.vgm_nome_produto IN ('" . $jogo_nome . "')";
	}//end if($jogo_nome)
	if($pins_valor) {
		 $sql .= "	
				AND vgm.vgm_valor IN (" .$pins_valor. ")";
	}//end if($pins_valor)
	$sql .= "  
					GROUP BY jogo_nome,mes,canal,publisher 
					) 
	-- fim Cartoes --
		) A  
	GROUP BY jogo_nome, mes, canal, publisher ";
	return $sql;
}

function bcgGenerator_receita($statuspgto, $canais, $fpgto, $datainicio, $datatermino, $connid, $itensgeral, $groupgeral, $itensespec, $groupespec, $where, $itensespec2, $tipoconsulta, $periodo = null) {
    //    echo 'statuspgto: '.$statuspgto.'<br>'; // filtro ok
    //    echo 'canais: ['.$canais.']<br>'; // filtro ok
    //    echo 'fpgto: '.$fpgto.'<br>';  //  filtro ok
    //    echo 'datainicio: '.$datainicio.'<br>'; //  filtro ok
    //    echo 'datatermino: '.$datatermino.'<br>'; //  filtro ok
    //    
    
    //echo 'itensgeral: '.$itensgeral.'<br>';
    //echo 'groupgeral: '.$groupgeral.'<br>';
    //echo 'itensespec: '.$itensespec.'<br>';
    //echo 'groupespec: '.$groupespec.'<br>';
    //echo 'where: '.$where.'<br>';
    //die('stop');
    //
    //echo '<hr>';

    if( $statuspgto == 0 || (empty($statuspgto)))  {
        $statuspgto = 5;
    }
    
    if(empty($canais)) {
        $canais = 0;
    }
	
	if(empty($periodo)) {
		$datainicio	= "'".$datainicio." 00:00:00'";
		$datatermino= "'".$datatermino." 23:59:59'";
	}
	else {
		switch ($periodo) {
			case 'M':
				$datainicio	= "('".$datainicio." 00:00:00'::timestamp - '1 month'::interval)";
				$datatermino= "('".$datatermino." 23:59:59'::timestamp - '1 month'::interval)";
				break;
			case 'S':
				$datainicio	= "('".$datainicio." 00:00:00'::timestamp - '1 week'::interval)";
				$datatermino= "('".$datatermino." 23:59:59'::timestamp - '1 week'::interval)";
				break;
			case 'A':
				$datainicio	= "('".$datainicio." 00:00:00'::timestamp - '1 year'::interval)";
				$datatermino= "('".$datatermino." 23:59:59'::timestamp - '1 year'::interval)";
				break;
			case 'D':
				$datainicio	= "('".$datainicio." 00:00:00'::timestamp - '1 day'::interval)";
				$datatermino= "('".$datatermino." 23:59:59'::timestamp - '1 day'::interval)";
				break;
			case 'T':
				$datainicio	= "('".$datainicio." 00:00:00'::timestamp - '3 months'::interval)";
				$datatermino= "('".$datatermino." 23:59:59'::timestamp - '3 months'::interval)";
				break;
		}//end switch
	}//end else do if(empty($periodo)) 
    
	//echo "$datainicio AND ve_data_inclusao<=$datatermino<br>";

    if($tipoconsulta == 'O') {
    //por operadora
    $sql = "SELECT 
				operadora_codigo, sum(n) as n_total, sum(vendas) as venda_total, opr.opr_nome as publisher $itensgeral  
			FROM ( ";
	if($canais==='P' || empty($canais)) {
	
	if($statuspgto==5) {
		$aux_data_pos = "ve_data_confirmado";
	}
	else {
		$aux_data_pos = "ve_data_inclusao";
	}
	$sql_union[] = "
					(SELECT 'P' AS canal, ve_opr_codigo AS operadora_codigo, count(*) as n, 
					sum(ve_valor * obtem_comissao(case when ve_jogo='HB' then 16 when ve_jogo='MU' then 17 when ve_jogo='OG' then 13 end, 'P'::char(1), ve_data_inclusao, 0)) as vendas $itensespec 
					FROM 
					dist_vendas_pos 
					WHERE 
					ve_data_inclusao>=$datainicio AND ve_data_inclusao<=$datatermino 
					GROUP BY ve_opr_codigo $groupgeral 
					)
		UNION ALL 
					(SELECT 
					'P' AS canal,
					pogp.ogp_opr_codigo AS operadora_codigo,
					cast(Sum(1) as int8) AS n,
					sum(ve.ve_valor * obtem_comissao(ve_opr_codigo, 'P'::char(1), ve.$aux_data_pos, 0)) as vendas
					FROM
					dist_vendas_rede ve
					INNER JOIN tb_pos_operadora_games_produto pogp ON  ve.ve_ogp_id = pogp.ogp_id 
					WHERE
					ve.$aux_data_pos>=$datainicio AND ve.$aux_data_pos<=$datatermino 
					AND ve.ve_status_confirmado = '1' 
					GROUP BY operadora_codigo  
					) 
		UNION ALL 
					(SELECT 
					'P' as canal,
					vgm_opr_codigo AS operadora_codigo,
					sum(vgm.vgm_qtde) as n, 
					sum((vgm.vgm_valor * vgm.vgm_qtde) * obtem_comissao(vgm.vgm_opr_codigo, 'P', vg.vg_data_concilia, 0)-(vgm.vgm_valor * vgm.vgm_qtde * tvgpo.tvgpo_perc_desconto/100)) as vendas 
					$itensespec2 
					FROM 
					tb_venda_games vg 
					INNER JOIN tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
					INNER JOIN tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id 
					WHERE 
					vg.vg_ultimo_status='$statuspgto' 
					AND SUBSTR(tvgpo.tvgpo_canal, 1, 1) ='P' 
					AND vg.vg_pagto_tipo = 13 
					AND vgm.vgm_opr_codigo!=49 
					AND vgm.vgm_opr_codigo!=53 
					AND vg.vg_data_concilia>=$datainicio 
					AND vg.vg_data_concilia<=$datatermino 
					GROUP BY  canal, vgm_opr_codigo $groupespec  
					)
			";
	}//end if($canais==='P' || empty($canais)) //
    if($canais==='S' || empty($canais)) {
	$sql_union[] = "
			(SELECT 
			'S' as canal,
			vgm_opr_codigo AS operadora_codigo, 
			sum(vgm.vgm_qtde) as n, 
			sum((vgm.vgm_valor * vgm.vgm_qtde) * obtem_comissao(vgm.vgm_opr_codigo, (case when vg.vg_ug_id = '7909' then 'E'::text when vg.vg_ug_id != '7909' then 'M'::text end), vg.vg_data_concilia, 0)) as vendas
			$itensespec2 
			FROM 
			tb_venda_games vg 
			INNER JOIN tb_venda_games_modelo vgm ON vgm.vgm_vg_id = vg.vg_id
			WHERE 
			vg.vg_ultimo_status='$statuspgto' 
			AND vg.vg_pagto_tipo != 13 
			AND vgm.vgm_opr_codigo!=49 
			AND vgm.vgm_opr_codigo!=53 
			AND vgm_opr_codigo>0 
			AND vg.vg_data_concilia>=$datainicio 
			AND vg.vg_data_concilia<=$datatermino 
			GROUP BY canal, vgm_opr_codigo $groupespec  
			)

		UNION ALL 

			(SELECT
			'S' as canal,
			vgm_opr_codigo AS operadora_codigo, 
			sum(vgm.vgm_qtde) as n, 
			sum((vgm.vgm_valor * vgm.vgm_qtde) * obtem_comissao(vgm.vgm_opr_codigo, (case when vg.vg_ug_id = '7909' then 'E'::text when vg.vg_ug_id != '7909' then 'M'::text end), vg.vg_data_concilia, 0)) as vendas
			$itensespec2 
			FROM tb_venda_games vg 
			INNER JOIN tb_venda_games_modelo vgm ON vgm.vgm_vg_id = vg.vg_id 
			INNER JOIN tb_venda_games_pinepp_origem tvgpo ON tvgpo.vg_id = vg.vg_id 
			WHERE 
			vg.vg_ultimo_status='$statuspgto' 
			AND tvgpo.tvgpo_canal='G' 
			AND vg.vg_pagto_tipo = 13 
			AND vg.vg_data_concilia>=$datainicio 
			AND vg.vg_data_concilia<=$datatermino 
			AND vgm.vgm_opr_codigo!=49 
			AND vgm.vgm_opr_codigo!=53 
			GROUP BY canal, vgm_opr_codigo $groupespec   
			)
	";
	}//end if($canais==='S' || empty($canais))
	if($canais==='A' || empty($canais)) {
	$sql_union[] = "
			(SELECT 
			'A' as canal,
			vgm_opr_codigo AS operadora_codigo, 
			sum(vgm.vgm_qtde) as n, 
			sum((vgm.vgm_valor * vgm.vgm_qtde) * obtem_comissao(vgm.vgm_opr_codigo, (case when vg.vg_ug_id = '7909' then 'E'::text when vg.vg_ug_id != '7909' then 'M'::text end), vg.vg_data_concilia, 0)) as vendas
			$itensespec2 
			FROM 
			tb_venda_games vg 
			INNER JOIN tb_venda_games_modelo vgm ON vgm.vgm_vg_id = vg.vg_id
			WHERE 
			vg.vg_ultimo_status='$statuspgto' 
			AND vg.vg_pagto_tipo = 13 
			AND vg.vg_ultimo_status_obs like '%AtimoPay%'
			AND vg.vg_http_referer_origem = 'ATIMO' 
			AND vgm.vgm_opr_codigo!=49 
			AND vgm.vgm_opr_codigo!=53 
			AND vgm_opr_codigo>0 
			AND vg.vg_data_concilia>=$datainicio 
			AND vg.vg_data_concilia<=$datatermino 
			GROUP BY canal, vgm_opr_codigo $groupespec  
			)
			
	";
	} 
    if($canais==='L' || empty($canais)) {

	$sql_union[] = "
			(SELECT 
			'L' as canal, 
			vgm_opr_codigo AS operadora_codigo, 
			sum(vgm.vgm_qtde) as n, 
			sum((vgm.vgm_valor * vgm.vgm_qtde) * obtem_comissao(vgm.vgm_opr_codigo, 'L', vg.vg_data_inclusao, vgm.vgm_perc_desconto:: integer)) as vendas 
			$itensespec2 
			FROM 
			tb_dist_venda_games vg
			INNER JOIN tb_dist_venda_games_modelo vgm ON vgm.vgm_vg_id = vg.vg_id 
			WHERE 
			vg.vg_ultimo_status='$statuspgto' 
			AND vgm_opr_codigo>0 
			AND vgm_opr_codigo!=49 
			AND vgm_opr_codigo!=53 
			AND vg.vg_data_inclusao>=$datainicio 
			AND vg.vg_data_inclusao<=$datatermino 
			GROUP BY canal, vgm_opr_codigo $groupespec  
			) 
	
		UNION ALL 
			(select 
			'L' as canal, 
			vgm_opr_codigo AS operadora_codigo, 
			sum(vgm.vgm_qtde) as n, 
			--sum((vgm.vgm_valor * vgm.vgm_qtde) * obtem_comissao(vgm.vgm_opr_codigo, 'L', vg.vg_data_concilia, 0)-(vgm.vgm_valor * vgm.vgm_qtde * tvgpo.tvgpo_perc_desconto/100)) as vendas
			sum((vgm.vgm_valor * vgm.vgm_qtde) * obtem_comissao(vgm.vgm_opr_codigo, 'L', vg.vg_data_concilia, tvgpo.tvgpo_perc_desconto:: integer)) as vendas
			$itensespec2 
			FROM tb_venda_games vg 
			INNER JOIN tb_venda_games_modelo vgm ON vgm.vgm_vg_id = vg.vg_id 
			INNER JOIN tb_venda_games_pinepp_origem tvgpo ON tvgpo.vg_id = vg.vg_id 
			WHERE vg.vg_ultimo_status='$statuspgto' 
			AND tvgpo.tvgpo_canal='L' 
			AND vg.vg_pagto_tipo = 13
			AND vgm_opr_codigo>0 
			AND vgm_opr_codigo!=49 
			AND vgm_opr_codigo!=53 
			AND vg.vg_data_inclusao>=$datainicio 
			AND vg.vg_data_inclusao<=$datatermino 
			GROUP BY canal, vgm_opr_codigo $groupespec 
			) 

    ";
	}//end if($canais==='L' || empty($canais)) 
	if($canais==='C' || empty($canais)) {
	$aux_sql = "";
	$sql_union[] = "
			(SELECT 
			'C' as canal, 
			vgm_opr_codigo AS operadora_codigo, 
			sum(vgm.vgm_qtde) as n, 
			sum((vgm.vgm_valor * vgm.vgm_qtde) * obtem_comissao(vgm.vgm_opr_codigo, 'C', vg.vg_data_concilia, 0)-(vgm.vgm_valor * vgm.vgm_qtde * tvgpo.tvgpo_perc_desconto/100)) as vendas
			$itensespec2 
			FROM tb_venda_games vg 
			INNER JOIN tb_venda_games_modelo vgm ON vgm.vgm_vg_id = vg.vg_id 
			INNER JOIN tb_venda_games_pinepp_origem tvgpo ON tvgpo.vg_id = vg.vg_id 
			WHERE  vg.vg_ultimo_status='$statuspgto' 
			AND tvgpo.tvgpo_canal='C' 
			AND vg.vg_pagto_tipo = 13
			AND vgm_opr_codigo>0 
			AND vgm_opr_codigo!=49 
			AND vgm_opr_codigo!=53 
			AND vg.vg_data_inclusao>=$datainicio 
			AND vg.vg_data_inclusao<=$datatermino 
			GROUP BY canal, vgm_opr_codigo $groupespec
			)
		UNION ALL 
			( 
			SELECT 'C' as canal, vc_total_mu_online as n, sum(vc_total_mu_online*10*(".$GLOBALS['COMISSOES_BRUTAS']['C']['MU ONLINE'].".-vc_comissao)/100) as vendas, 17 as operadora_codigo $itensespec from dist_vendas_cartoes_tmp 
			WHERE 
			vc_total_mu_online>0 AND vc_data>=$datainicio AND vc_data<=$datatermino  
			GROUP BY  operadora_codigo, vc_total_mu_online $groupgeral  
			)
		UNION ALL 
			(
			SELECT 
			'C' as canal, vc_total_5k as n, sum(vc_total_5k*10*(".$GLOBALS['COMISSOES_BRUTAS']['C']['ONGAME'].".-vc_comissao)/100) as vendas, 13 as operadora_codigo $itensespec from dist_vendas_cartoes_tmp 
			WHERE 
			vc_total_5k>0 AND vc_data>=$datainicio AND vc_data<=$datatermino  
			GROUP BY  operadora_codigo, vc_total_5k $groupgeral  
			)
        UNION ALL 
			(
			SELECT 
			'C' as canal, vc_total_10k as n, sum(vc_total_10k*25*(".$GLOBALS['COMISSOES_BRUTAS']['C']['ONGAME'].".-vc_comissao)/100) as vendas, 13 as operadora_codigo $itensespec 
			FROM dist_vendas_cartoes_tmp 
			WHERE 
			vc_total_10k>0 and vc_data>=$datainicio AND vc_data<=$datatermino  
			GROUP BY operadora_codigo,vc_total_10k $groupgeral  
			)
        UNION ALL
			(
			SELECT 'C' as canal, vc_total_15k as n, sum(vc_total_15k*37*(".$GLOBALS['COMISSOES_BRUTAS']['C']['ONGAME'].".-vc_comissao)/100) as vendas, 13 as operadora_codigo $itensespec
			FROM 
			dist_vendas_cartoes_tmp 
			WHERE
			vc_total_15k>0 AND vc_data>=$datainicio AND vc_data<=$datatermino  
			GROUP BY operadora_codigo,vc_total_15k $groupgeral 
			)
        UNION ALL 
			(
			SELECT
			'C' as canal, vc_total_20k as n, sum(vc_total_20k*49*(".$GLOBALS['COMISSOES_BRUTAS']['C']['ONGAME'].".-vc_comissao)/100) as vendas,13 as operadora_codigo $itensespec 
			FROM
			dist_vendas_cartoes_tmp 
			WHERE 
			vc_total_20k>0 AND vc_data>=$datainicio AND vc_data<=$datatermino  
			GROUP BY operadora_codigo,vc_total_20k $groupgeral      
			)
    ";
	} //end if($canais==='C' || empty($canais))
	$sql_aux = implode("\n\t\t UNION ALL \n", $sql_union);
	$sql .= $sql_aux."
        ) A  
        INNER JOIN operadoras opr ON opr.opr_codigo = a.operadora_codigo 
        WHERE  1= 1 
        $where 
        GROUP BY operadora_codigo ,opr.opr_nome $groupgeral    
        ORDER BY venda_total DESC";    
} /*else {
	// por game
    $sql  = "SELECT
             jogo_nome, 
             sum(cast(n as int8)) as n_total, 
             sum(cast(vendas as decimal)) as venda_total, 
             publisher  
             $itensgeral
             FROM (";
    if($canais==='P' || empty($canais)) {
	if($statuspgto==5) {
		$aux_data_pos = "ve_data_confirmado";
		$aux_data_pos_vg = "vg_data_concilia";
	}
	else {
		$aux_data_pos = "ve_data_inclusao";
		$aux_data_pos_vg = "vg_data_inclusao";
	}
	$sql_union[] = "
					(SELECT  
					'P' AS canal, 
					cast (ve_opr_codigo as int8) AS publisher,
					cast (case when ve_jogo = 'OG' then 'Metin2' when ve_jogo = 'HB' then 'Habbo Hotel' when ve_jogo = 'MU' then 'MU Online' end as varchar(255)) AS jogo_nome,					
					cast(count(*) as int8) as n, 
					cast (sum(ve_valor) as decimal) as vendas 
					$itensespec 
					FROM
					dist_vendas_pos 
					WHERE
					ve_data_inclusao>=$datainicio AND ve_data_inclusao<=$datatermino 
					GROUP BY publisher,jogo_nome $groupgeral
					)
	--	UNION ALL 
	--				(SELECT 
	--				'P' AS canal,
	--				cast (ve_opr_codigo as int8) AS publisher,
	--				cast (ogp.ogp_nome as varchar(255)) AS jogo_nome,
	--				cast(Sum(1) as int8) AS n,
	--				cast(Sum(ve.ve_valor)  as decimal) AS vendas 
	--				FROM
	--				dist_vendas_rede ve
	--				INNER JOIN tb_pos_operadora_games_produto ogp ON  ve.ve_ogp_id = ogp.ogp_id 
	--				WHERE
	--				ve.$aux_data_pos>=$datainicio AND ve.$aux_data_pos<=$datatermino 
	--				AND ve.ve_status_confirmado = '1' 
	--				GROUP BY publisher,jogo_nome  
	--				) 
		UNION ALL 
					(SELECT 
					'P' as canal, 
					cast (vgm.vgm_opr_codigo as int8) AS publisher,
					cast (vgm.vgm_nome_produto  as varchar(255)) AS jogo_nome,
					cast(sum(vgm.vgm_qtde) as int8) as n, 
					cast(sum(vgm.vgm_valor * vgm.vgm_qtde) as decimal) as vendas 
					FROM 
					tb_venda_games vg 
					INNER JOIN tb_venda_games_modelo vgm ON vgm.vgm_vg_id = vg.vg_id 
					INNER JOIN tb_venda_games_pinepp_origem tvgpo ON tvgpo.vg_id = vg.vg_id 
					WHERE 
					vg.vg_ultimo_status='5' 
					AND SUBSTR(tvgpo.tvgpo_canal, 1, 1) ='P' 
					AND vg.vg_pagto_tipo = 13 
					AND vg.$aux_data_pos_vg>=$datainicio AND vg.$aux_data_pos_vg<=$datatermino 
					GROUP BY jogo_nome, publisher 
					) 
			";
	}//end if($canais==='P' || empty($canais))
    if($canais==='L' || empty($canais)) {
	if($statuspgto==5) {
		$aux_data_vg = "vg_data_concilia";
	}
	else {
		$aux_data_vg = "vg_data_inclusao";
	}
	$sql_union[] = "
					(SELECT 
					'L' AS canal,
					cast (vgm.vgm_opr_codigo as int8) AS publisher,
					cast (vgm.vgm_nome_produto  as varchar(255)) AS jogo_nome,
					cast(Sum(vgm.vgm_qtde) as int8) AS n,
					cast(Sum(vgm.vgm_valor * vgm.vgm_qtde)  as decimal) AS vendas 
					$itensespec2 
					FROM
					tb_dist_venda_games AS vg
					INNER JOIN tb_dist_venda_games_modelo AS vgm ON vgm.vgm_vg_id = vg.vg_id
					WHERE
					vg.vg_ultimo_status = '$statuspgto'	
					AND vg.vg_data_inclusao>=$datainicio AND vg.vg_data_inclusao<= $datatermino 
					GROUP BY publisher,jogo_nome $groupespec) 
		UNION ALL 
					(SELECT 
					'L' as canal, 
					cast (vgm.vgm_opr_codigo as int8) AS publisher,
					cast (vgm.vgm_nome_produto  as varchar(255)) AS jogo_nome,
					cast(sum(vgm.vgm_qtde) as int8) as n, 
					cast(sum(vgm.vgm_valor * vgm.vgm_qtde) as decimal) as vendas 
					FROM 
					tb_venda_games vg 
					INNER JOIN tb_venda_games_modelo vgm ON vgm.vgm_vg_id = vg.vg_id 
					INNER JOIN tb_venda_games_pinepp_origem tvgpo ON tvgpo.vg_id = vg.vg_id 
					WHERE 
					vg.vg_ultimo_status='5' 
					AND tvgpo.tvgpo_canal='L' 
					AND vg.vg_pagto_tipo = 13 
					AND vg.$aux_data_vg>=$datainicio AND vg.$aux_data_vg<=$datatermino 
					GROUP BY jogo_nome, publisher 
					) ";
    }//end if($canais==='L' || empty($canais)) 
	if($canais==='S' || empty($canais)) {
	if($statuspgto==5) {
		$aux_data_vg = "vg_data_concilia";
	}
	else {
		$aux_data_vg = "vg_data_inclusao";
	}
	if($canais==='S') {
		$sql_union[] = "  
					(SELECT 
					'S' AS canal,
					cast (vgm.vgm_opr_codigo as int8) AS publisher,
					cast (vgm.vgm_nome_produto  as varchar(255)) AS jogo_nome,
					cast(Sum(vgm.vgm_qtde) as int8) AS n,
					cast(Sum(vgm.vgm_valor * vgm.vgm_qtde)  as decimal) AS vendas 
					$itensespec2 
					FROM
					tb_dist_venda_games AS vg
					INNER JOIN tb_dist_venda_games_modelo AS vgm ON vgm.vgm_vg_id = vg.vg_id
					WHERE
					vg.vg_ultimo_status = '$statuspgto'	
					AND vg.vg_data_inclusao>=$datainicio AND vg.vg_data_inclusao<= $datatermino 
					GROUP BY publisher,jogo_nome $groupespec ) 
		UNION ALL 
					(SELECT 
					'S' as canal, 
					cast (vgm.vgm_opr_codigo as int8) AS publisher,
					cast (vgm.vgm_nome_produto  as varchar(255)) AS jogo_nome,
					cast(sum(vgm.vgm_qtde) as int8) as n, 
					cast(sum(vgm.vgm_valor * vgm.vgm_qtde) as decimal) as vendas 
					FROM 
					tb_venda_games vg 
					INNER JOIN tb_venda_games_modelo vgm ON vgm.vgm_vg_id = vg.vg_id 
					INNER JOIN tb_venda_games_pinepp_origem tvgpo ON tvgpo.vg_id = vg.vg_id 
					WHERE 
					vg.vg_ultimo_status='5' 
					AND tvgpo.tvgpo_canal='L' 
					AND vg.vg_pagto_tipo = 13 
					AND vg.$aux_data_vg>=$datainicio AND vg.$aux_data_vg<=$datatermino 
					GROUP BY jogo_nome, publisher) ";
    }//end if($canais==='S')
	$sql_union[] = "
					(SELECT 
					'S' as canal,
					cast (vgm.vgm_opr_codigo as int8) AS publisher,
					cast (vgm.vgm_nome_produto  as varchar(255)) AS jogo_nome,
					cast(sum(vgm.vgm_qtde) as int8) as n, 
					cast(sum(vgm.vgm_valor * vgm.vgm_qtde) as decimal) as vendas 
					$itensespec2 
					FROM
					tb_venda_games vg 
					INNER JOIN tb_venda_games_modelo vgm ON vgm.vgm_vg_id = vg.vg_id 
					WHERE
					vg.vg_ultimo_status= '$statuspgto' 
					AND vg.vg_pagto_tipo != 13
					AND vg.$aux_data_vg>=$datainicio AND vg.$aux_data_vg<=$datatermino 
					GROUP BY jogo_nome,canal,publisher 
					) 
		UNION ALL 
					(SELECT 
					'S' as canal,
					cast (vgm.vgm_opr_codigo as int8) AS publisher,
					cast (vgm.vgm_nome_produto  as varchar(255)) AS jogo_nome,
					cast(sum(vgm.vgm_qtde) as int8) as n, 
					cast(sum(vgm.vgm_valor * vgm.vgm_qtde) as decimal) as vendas 
					$itensespec2 
					FROM 
					tb_venda_games vg 
					INNER JOIN tb_venda_games_modelo vgm ON vgm.vgm_vg_id = vg.vg_id 
					INNER JOIN tb_venda_games_pinepp_origem tvgpo ON tvgpo.vg_id = vg.vg_id 
					WHERE 
					vg.vg_ultimo_status='$statuspgto' AND tvgpo.tvgpo_canal='G' AND vg.vg_pagto_tipo = 13 
					AND vg.$aux_data_vg>=$datainicio AND vg.$aux_data_vg<=$datatermino 
					GROUP BY jogo_nome,canal,publisher 
					)";
    }  //end if($canais==='S' || empty($canais))         
    if($canais==='C' || empty($canais)) {
	
	$sql_union[] = "
					(SELECT  
					'C' as canal,
					cast ('17' as int8)  as   publisher ,
					'MU Online' as jogo_nome, 
					cast(vc_total_mu_online  as int8) as n, 
					cast(vc_total_mu_online*10 as decimal) as vendas 
					$itensespec 
					FROM 
					dist_vendas_cartoes_tmp 
					WHERE 
					vc_total_mu_online>0 
					AND vc_data>=$datainicio AND vc_data<=$datatermino  
					GROUP BY publisher, jogo_nome, vc_total_mu_online $groupgeral 
					)
				UNION ALL 
					(SELECT  
					'C' as canal, 
					cast ('13' as int8)   as publisher ,
					'Metin2' as jogo_nome, 
					cast(vc_total_5k  as int8)  as n, 
					cast(vc_total_5k*13 as decimal) as vendas 
					$itensespec 
					FROM
					dist_vendas_cartoes_tmp 
					WHERE 
					vc_total_5k>0  
					AND vc_data>=$datainicio AND vc_data<=$datatermino 
					GROUP BY publisher, jogo_nome, vc_total_mu_online, vc_total_5k $groupgeral 
					)
                UNION ALL 
					(SELECT  
					'C' as canal, 
					cast ('13' as int8) as publisher ,
					'Metin2' as jogo_nome , 
					cast(vc_total_10k as int8) as n, 
					cast(vc_total_10k*25 as decimal) as vendas 
					$itensespec 
					FROM 
					dist_vendas_cartoes_tmp 
					WHERE
					vc_total_10k>0  
					AND vc_data>=$datainicio AND vc_data<=$datatermino 
					GROUP BY publisher, jogo_nome, vc_total_mu_online, vc_total_10k $groupgeral 
					)
                UNION ALL 
					(SELECT  
					'C' as canal, 
					cast ('13' as int8) as publisher,
					'Metin2' as jogo_nome, 
					cast(vc_total_15k  as int8) as n, 
					cast(vc_total_15k*37 as decimal) as vendas  
					$itensespec 
					FROM 
					dist_vendas_cartoes_tmp 
					WHERE vc_total_15k>0 
					AND vc_data>=$datainicio AND vc_data<=$datatermino 
					GROUP BY publisher, jogo_nome, vc_total_mu_online, vc_total_15k $groupgeral 
					)
                UNION ALL 
					(SELECT 
					'C' as canal, 
					cast ('13' as int8) as publisher, 
					'Metin2' as jogo_nome, 
					cast(vc_total_20k  as int8) as n, 
					cast(vc_total_20k*49 as decimal) as vendas 
					$itensespec 
					FROM 
					dist_vendas_cartoes_tmp 
					WHERE 
					vc_total_20k>0 
					AND vc_data>=$datainicio AND vc_data<=$datatermino 
					GROUP BY publisher, jogo_nome, vc_total_mu_online, vc_total_20k $groupgeral 
					)
                ";
    }//end if($canais==='C' || empty($canais))
    $sql_aux = implode("\n\t\t UNION ALL \n", $sql_union);
	$sql .= $sql_aux."
		) A  
    WHERE  1= 1 
    $where 
    GROUP BY publisher,jogo_nome $groupgeral 
    ORDER BY venda_total DESC
    ";


}
*/
	//echo 'SQL:<pre>'.$sql.'</pre><br><br>';
    //die('stop');
    $rss = pg_exec($connid, $sql);
    return $rss;
}

?>