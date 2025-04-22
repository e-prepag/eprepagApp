<?php

	function remove_element(&$arr, $val){
		foreach ($arr as $key => $value){
			if ($arr[$key] == $val){
				unset($arr[$key]);
			}
		}
		return $arr = array_values($arr);
	}

	function formata_data_ts_pos($data, $gravar, $blComHora, $blComSegundos){
		
		$mask = $data;
		
		//Entra: yyyy-mm-dd hh:mm:ss.uuu
		//Sai: dd/mm/yyyy hh:mm:ss.uuu
		if($gravar == 0){
			$dia = substr($mask, 8, 2);
			$mes = substr($mask, 5, 2);
			$ano = substr($mask, 0, 4);
			$doc = $dia."/".$mes."/".$ano;
			
			if($blComHora){
				$hora = substr($mask, 11, 2);
				$minuto = substr($mask, 14, 2);
				$segundo = substr($mask, 17, 2);
				$milliseg = substr($mask, 20, 3);
				$doc = $doc . " " . $hora . ":" . $minuto;
				if($blComSegundos) $doc = $doc . ":" . $segundo;
//				if($milliseg) $doc = $doc . "." . $milliseg;
			}
			$doc = str_replace(" ","<br>\n",$doc);
		}
		
		//Entra: dd/mm/yyyy hh:mm:ss
		//Sai: yyyymmddhhmmss
		if($gravar == 1){
			$dia = substr($mask, 0, 2);
			$mes = substr($mask, 3, 2);
			$ano = substr($mask, 6, 4);
			$doc = $ano . $mes . $dia;
			if($blComHora){
				$hora = substr($mask, 11, 2);
				$minuto = substr($mask, 14, 2);
				$segundo = substr($mask, 17, 2);
				$milliseg = substr($mask, 20, 3);
				$doc .= " " . $hora . $minuto;
				if($blComSegundos) $doc .= $segundo;
				else $doc .= "00";
				if($milliseg) $doc = $doc . "." . $milliseg;
				
			} else {
				$doc .= "000000";
			}
		}

		//Entra: dd/mm/yyyy hh:mm:ss
		//Sai: yyyy-mm-dd hh:mm:ss
		if($gravar == 2){
			$dia = substr($mask, 0, 2);
			$mes = substr($mask, 3, 2);
			$ano = substr($mask, 6, 4);
			$doc = $ano . "-" . $mes . "-" . $dia;
			if($blComHora){
				$hora = substr($mask, 11, 2);
				$minuto = substr($mask, 14, 2);
				$segundo = substr($mask, 17, 2);
				$milliseg = substr($mask, 20, 3);
				$doc = $doc . " " . $hora . ":" . $minuto;
				if($blComSegundos) $doc = $doc . ":" . $segundo;
				if($milliseg) $doc = $doc . "." . $milliseg;
				
			} else {
				$doc .= "00:00:00";
			}
		}
		return $doc;
	}

	function is_csv_numeric_2($list) {
		$list1 = str_replace(" ", "", $list);
		$alist = explode(",", $list1);
		$bret = true;
		foreach($alist as $key => $val) {
			$bret = is_numeric($val);
			if(!$bret) {
				break;
			}
		}
		return $bret;
	}

?>