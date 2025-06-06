<?php
	function obter($filtro, $orderBy, $limitTo, &$rs){

		$ret = "";
	
		
		$filtro = array_map("strtoupper", $filtro);

		//$com ="";
//echo "<pre>".print_r($filtro, true)."</pre>";
		$news_query = "";
		if ($filtro['news'] == 'T' || $filtro['news'] == 'S' || $filtro['news'] == 'N' || $filtro['news'] == 'H') {
			if ($filtro['news'] == 'S') {
				$news_query = " and (upper(ug_news) = 'T' or upper(ug_news) = 'H') ";
			} elseif ($filtro['news'] == 'N') {
				$news_query = " and (upper(ug_news) = '".$filtro['news']."' or ug_news = ' ' or ug_news = '') ";
			} else {	// "T", "H"
				$news_query = " and upper(ug_news) = '".$filtro['news']."' ";
			}
			
			// Express Money é todo mundo 'h'
			if ($filtro['news'] != 'H') {
				$news_query_express_money = " and false";
			}
		}
		if ($filtro['dd_opr_codigo'] != '') {

		//	$com .= ", '".$filtro['dd_opr_codigo']."' as operadora ";
			$num_op = $filtro['dd_opr_codigo'];
			
			if ($filtro['produto0'] != '') {
			
					$i = count($filtro) - 3 ; // quantidade de produtos dentro do vetor sem contar os que não são
					$s = 0;
					
					$produtos_query .= " and  ( ";
					while ($filtro['produto'.$s] != '') {
						$com .= ", '".$filtro['produto'.$s]."' as produto".$s." ";
						$produtos_query .= " upper(vgm_nome_produto) = '".str_replace("'", "''", $filtro['produto'.$s])."' ";
						$s++;
						if ($filtro['produto'.$s] != '') $produtos_query .= " or ";
					}
				$produtos_query .= ") ";	
			} 

			

			if ($filtro['pin0'] != '') {
			
				//	$i = count($filtro) -3 ; // quantidade de produtos dentro do vetor sem contar os que não são
					$s = 0;
					$produtos_query .= " and  ( ";
					
					//while ($s <= $i) {
					
					while ($filtro['pin'.$s] != '') {
						$com .= ", '".$filtro['pin'.$s]."' as pin".$s." ";
						$produtos_query .= " vgm_valor = '".$filtro['pin'.$s]."' ";
						$s++;
						if ($filtro['pin'.$s] != '') $produtos_query .= " or ";
					}

			$produtos_query .= ") ";		
			}
		}

		$op = "";
		if ($num_op != '') { 
			$op = " and vgm_opr_codigo= '".$num_op."' ";
		}

		if($filtro['data_inclusao_ini'] && $filtro['data_inclusao_fim']) {
			$data_inclusao_ini = $filtro['data_inclusao_ini']." 00:00:00";
			$data_inclusao_fim = $filtro['data_inclusao_fim']." 23:59:59";
		}


		$sql = "select * from 	(	";

		if($filtro['tipo']=="" || $filtro['tipo']=="GAMERS") {
			$sql .= "select 'GAMERS'::text as tipo, upper(ug_email) as email, count(*) as n, ug_ativo as ativo, upper(ug_news) as news";
			$sql .= "		from usuarios_games ug ";
			//if ($filtro['dd_opr_codigo'] != '') {
				$sql .= "inner join tb_venda_games vg on vg.vg_ug_id  = ug.ug_id " . PHP_EOL;

				if ($filtro['dd_opr_codigo'] != '') {
					$sql .= "		inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id ";
				}
			//}
			$sql .= "where 1=1 and ug.ug_id != '7909' ".$op." and (not ug_email is null) ".$produtos_query." ".$news_query." ";
			//if($filtro['dd_opr_codigo'] != '') {
				$sql .= " and vg.vg_ultimo_status='5' ";
				if($filtro['data_inclusao_ini'] && $filtro['data_inclusao_fim']) {
					$sql .= " and (vg_data_inclusao between '$data_inclusao_ini' and '$data_inclusao_fim') ";
				}
			//}
			$sql .= "group by ug_email, ativo, ug_news ";
		}
		
		if($filtro['tipo']=="") {
			$sql .=	" union all ";
		}

		if($filtro['tipo']=="" || $filtro['tipo']=="LANHOUSES") {
				$sql .=	" select 'LANHOUSES'::text as tipo, upper(ug_email) as email, count(*) as n, ug_ativo as ativo, upper(ug_news) as news " . PHP_EOL ;
				$sql .= "	from dist_usuarios_games ug " . PHP_EOL;
				//if ($filtro['dd_opr_codigo'] != '') {
					$sql .= "inner join tb_dist_venda_games vg on vg.vg_ug_id  = ug.ug_id " . PHP_EOL;

					if ($filtro['dd_opr_codigo'] != '') {
						$sql .= "	inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id " . PHP_EOL;
					}
				//}
				$sql .= "where 1=1 ".$op." and not ug_email is null ".$produtos_query." ".$news_query." " . PHP_EOL;
			//if($filtro['dd_opr_codigo'] != '') {
				$sql .= " and vg.vg_ultimo_status='5' ";
				if($filtro['data_inclusao_ini'] && $filtro['data_inclusao_fim']) {
					$sql .= " and (vg_data_inclusao between '$data_inclusao_ini' and '$data_inclusao_fim') ";
				}
				$sql .= PHP_EOL;
			//}
			$sql .= "group by ug_email, ativo, ug_news ";
		}

		if($filtro['tipo']=="") {
			$sql .=	" union all ";
		}

		if($filtro['tipo']=="" || $filtro['tipo']=="EXPRESSMONEY") {
				$sql .=	" select 'EXPRESSMONEY'::text as tipo, upper(vg_ex_email) as email, count(*) as n, " . PHP_EOL;
				$sql .=	" (case when vg_ultimo_status='5' then '1'::integer else '2'::integer end) as ativo," . PHP_EOL;
				$sql .=	" 'H'::text as news " . PHP_EOL;
				$sql .= " from tb_venda_games  vg " . PHP_EOL;
				if ($filtro['dd_opr_codigo'] != '') {
					$sql .= "	inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id " . PHP_EOL;
				}
				$sql .= "where 1=1 and vg.vg_ug_id = '7909' ";
				// Ativos -> só os que completaram vendas
				if($filtro['ativo']=='1') {
					$sql .= " and vg_ultimo_status='5' ";
				} elseif($filtro['ativo']=='2') {
					$sql .= " and (not vg_ultimo_status='5') ";
				} else {
					//  todos
				}
				$sql .= $op." and (not vg_ex_email is null) ".$produtos_query." ".$news_query_express_money."";
				if($filtro['data_inclusao_ini'] && $filtro['data_inclusao_fim']) {
					$sql .= " and (vg_data_inclusao between '$data_inclusao_ini' and '$data_inclusao_fim') ";
				}
				$sql .= "group by vg_ex_email, vg_ultimo_status " . PHP_EOL;
		}
		
		$sql .=	") lista_emails " . PHP_EOL;


		if(!is_null($filtro) && $filtro != ""){
			$sql .= " where 1=1";

			$sql .= " and (" . (is_null($filtro['tipo'])?1:0);
			$sql .= "=1 or upper(tipo) = '" . SQLaddFields($filtro['tipo'], "") . "')";

			$sql .= " and (" . (is_null($filtro['email'])?1:0);
			$sql .= "=1 or upper(email) = " . SQLaddFields($filtro['email'], "") . ")";

			$sql .= " and (" . (($filtro['ativo']!='1' && $filtro['ativo']!='2')?1:0);
			$sql .= "=1 ".(($filtro['ativo']=='1')?" or ativo = 1":(($filtro['ativo']=='2')?" or ativo = 2":"")).") ";

			// Retira usuários da lista de OptOut
			if($filtro['sem_optout']) {
				$sql .= " and not exists (select 1 from tb_usuarios_optout uo where upper(uo.uo_email) = upper(lista_emails .email) and uo.uo_tipo_cadastro = 'O') ";
			}
//echo "filtro['ativo']: ".$filtro['ativo']."<br>";

		}

		if(!is_null($orderBy)) $sql .= $orderBy." ";
	
		if(!is_null($limitTo) && !($limitTo=="")) $sql .= $limitTo." ;";

//if(b_IsUsuarioReinaldo()) { 
//echo "(R) ".str_replace("\n", "<br>\n", $sql)."<br>";
//die("Stop");
//}		

		$rs = SQLexecuteQuery($sql);
		if(!$rs) $ret = "Erro ao obter Newsletter(s)." . PHP_EOL;

		return $ret;
	}

	?>