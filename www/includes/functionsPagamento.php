<?php

	/**
	 * @param array<int|string, mixed> $arr
	 * @param mixed $val
	 * @return array<int, mixed>
	 */
	function remove_element(array &$arr, mixed $val): array{
		foreach ($arr as $key => $value){
			if ($arr[$key] == $val){
				unset($arr[$key]);
			}
		}
		return $arr = array_values($arr);
	}

	function formata_data_ts_pos(mixed $data, mixed $gravar, mixed $blComHora, mixed $blComSegundos): string{
		
		$mask = $data;
		$doc = "";
		
		//Entra: yyyy-mm-dd hh:mm:ss.uuu
		//Sai: dd/mm/yyyy hh:mm:ss.uuu
		if($gravar == 0){
			$dia = substr((string)$mask, 8, 2);
			$mes = substr((string)$mask, 5, 2);
			$ano = substr((string)$mask, 0, 4);
			$doc = $dia."/".$mes."/".$ano;
			
			if($blComHora){
				$hora = substr((string)$mask, 11, 2);
				$minuto = substr((string)$mask, 14, 2);
				$segundo = substr((string)$mask, 17, 2);
				$milliseg = substr((string)$mask, 20, 3);
				$doc = $doc . " " . $hora . ":" . $minuto;
				if($blComSegundos) $doc = $doc . ":" . $segundo;
//				if($milliseg) $doc = $doc . "." . $milliseg;
			}
			$doc = str_replace(" ","<br>\n",$doc);
		}
		
		//Entra: dd/mm/yyyy hh:mm:ss
		//Sai: yyyymmddhhmmss
		if($gravar == 1){
			$dia = substr((string)$mask, 0, 2);
			$mes = substr((string)$mask, 3, 2);
			$ano = substr((string)$mask, 6, 4);
			$doc = $ano . $mes . $dia;
			if($blComHora){
				$hora = substr((string)$mask, 11, 2);
				$minuto = substr((string)$mask, 14, 2);
				$segundo = substr((string)$mask, 17, 2);
				$milliseg = substr((string)$mask, 20, 3);
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
			$dia = substr((string)$mask, 0, 2);
			$mes = substr((string)$mask, 3, 2);
			$ano = substr((string)$mask, 6, 4);
			$doc = $ano . "-" . $mes . "-" . $dia;
			if($blComHora){
				$hora = substr((string)$mask, 11, 2);
				$minuto = substr((string)$mask, 14, 2);
				$segundo = substr((string)$mask, 17, 2);
				$milliseg = substr((string)$mask, 20, 3);
				$doc = $doc . " " . $hora . ":" . $minuto;
				if($blComSegundos) $doc = $doc . ":" . $segundo;
				if($milliseg) $doc = $doc . "." . $milliseg;
				
			} else {
				$doc .= "00:00:00";
			}
		}
		return $doc;
	}

	function is_csv_numeric_2(mixed $list): bool {
		$list1 = str_replace(" ", "", (string)$list);
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