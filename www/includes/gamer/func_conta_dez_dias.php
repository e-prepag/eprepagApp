<?php 
	function data_today($sql,$num) {
		$data_fim_query = date("Y-m-d");
		
		$hoje = date("Y-m-d");

		$hoje = formata_data_ts($hoje,0,true,false);

		$data_ini_query = data_menos_n($hoje,30);
		
		$data_ini_query = formata_data($data_ini_query,1);

		//	echo $data_ini_query;

		if ($num == '2') { // venda
			$sql .= " and vg.vg_data_inclusao between '".$data_ini_query." 00:00:00' and '".$data_fim_query." 23:59:59' ";
		}
		
		if ($num == '1') { // balanco
			$sql .= " and db_data_balanco between '".$data_ini_query." 00:00:00' and '".$data_fim_query." 23:59:59' ";
		}	
		if ($num == '3') { // boleto
			$sql .= " and vg_pagto_data_inclusao between '".$data_ini_query." 00:00:00' and '".$data_fim_query." 23:59:59' ";
		}	
		if ($num == '4') { // corte
			$sql .= " and cor_data_concilia between '".$data_ini_query." 00:00:00' and '".$data_fim_query." 23:59:59' ";
		}	
	
		return $sql;
	}

?>