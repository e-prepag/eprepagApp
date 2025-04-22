<?php  
require_once "../../../../../includes/constantes.php";
require_once $raiz_do_projeto . "public_html/sys/includes/topo_sys.php";
require_once $raiz_do_projeto . "public_html/sys/includes/gamer/inc_pub_access.php";
require_once $raiz_do_projeto . "class/gamer/classIntegracao.php";
require_once $raiz_do_projeto . "includes/gamer/constantes.php";
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

	set_time_limit ( 3000 ) ;
	$pos_pagina = $seg_auxilar;

	$time_start = getmicrotime();

	block_user_publisher();

//echo "dd_operadora: ".$dd_operadora."<br>";
//echo "dd_mode: ".$dd_mode."<br>";
//echo "Submit: $Submit<br>";
	if(b_is_Publisher()) {
		$dd_operadora = $_SESSION["opr_codigo_pub"];
		$dd_mode = "S";
		$Submit = "Buscar";
	}

	if(!$dd_mode || ($dd_mode!='V')) {
		$dd_mode = "S";
	}
	$where_mode_data = "vg.vg_data_inclusao";	// default
	if($dd_mode=='S') $where_mode_data = "vg.vg_data_concilia";

	if(!$ncamp)            $ncamp           = 'trn_data';
	if(!$tf_data_final)    $tf_data_final   = date('d/m/Y');
	if(!$tf_data_inicial)  $tf_data_inicial = date('d/m/Y');
	if(!$inicial)          $inicial         = 0;
	if(!$range)            $range           = 1;
	if(!$ordem)            $ordem           = 1;
//	if($BtnSearch)         $inicial         = 0;
//	if($BtnSearch)         $range           = 1;
//	if($BtnSearch)         $total_table     = 0;
	if($BtnSearch && $BtnSearch!=1 ) {
		$inicial     = 0;
		$range       = 1;
		$total_table = 0;
	}

	$data_inicial_limite = data_menos_n(date('d/m/Y'), 120);
	$data_inicial_limite = '01/08/2004';
	$FrmEnviar = 1;
	
	
	$default_add  = nome_arquivo($PHP_SELF);
	$img_proxima  = "/sys/images/proxima.gif";
	$img_anterior = "/sys/images/anterior.gif";
	$max          = 100; //$qtde_reg_tela;
	$range_qtde   = $qtde_range_tela;

//	$resuf = pg_exec($connid, "select uf from uf order by uf");
//	$resuf_except = pg_exec($connid, "select uf from uf order by uf");

	if($cb_opr_teste) {
		$resopr = pg_exec($connid, "select opr_nome, opr_codigo from operadoras where (opr_status = '".$operadora_ativada."') order by opr_nome");
	} else {
		$resopr = pg_exec($connid, "select opr_nome, opr_codigo from operadoras where (opr_status = '".$operadora_ativada."') and (opr_codigo <> ".$opr_teste.") order by opr_nome");
	}

	if($dd_operadora)
	{			
		$res_opr_info = pg_exec($connid, "select opr_codigo, opr_nome, opr_pin_online from operadoras where opr_codigo=".$dd_operadora."");
		$pg_opr_info = pg_fetch_array($res_opr_info);

		$dd_operadora_nome = $pg_opr_info['opr_nome'];
	
		if($pg_opr_info['opr_pin_online'] == 0) {
			$sql_valor  = "select opr_valor1, opr_valor2, opr_valor3, opr_valor4, opr_valor5, opr_valor6, opr_valor7, opr_valor8, opr_valor9, opr_valor10, opr_valor11, opr_valor12, opr_valor13, opr_valor14, opr_valor15 from operadoras where opr_codigo = " . $dd_operadora . "";

			$resval = pg_exec($connid, $sql_valor);
		} else {
			$sql_valor = "select valor_fixo as valor from pin_valor_lista t0, pin_valor_fixo t1 where t0.valor_lista_cod = t1.valor_lista_cod and opr_codigo = ".$pg_opr_info['opr_codigo']." group by valor_fixo order by valor_fixo";
			$resval = pg_exec($connid, $sql_valor);
			$res_opr_area = pg_exec($connid, "select oparea_codigo, area_nome from operadora_area where opr_codigo=".$pg_opr_info['opr_codigo']." order by oparea_codigo");
		}
	}

	if(!verifica_data($tf_data_inicial))
	{
		$data_inic_invalida = true;
		$FrmEnviar = 0;
	}

	if(!verifica_data($tf_data_final))
	{
		$data_fim_invalida = true;
		$FrmEnviar = 0;
	}
	
	if(qtde_dias($data_inicial_limite, $tf_data_inicial) < 0)
	{
		$data_inicial_menor = true;
		$FrmEnviar = 0;
	}

	if($FrmEnviar == 1)
	{

		$where_data_1a = "";
		$where_data_1b = "";
		$where_data_2 = "";
		$where_valor_1 = "";
		$where_valor_2 = "";
		$where_opr_1 = "";
		$where_opr_2 = "";
		$where_canal_1 = "";
		$where_canal_2 = "";
		$where_canal_pos = "";
		$where_canal_pos_integradas = "";
                //Where para ids de integração
                $where_ids_integracao = "";

		if($tf_data_inicial && $tf_data_final) 
		{
			$data_inic = formata_data(trim($tf_data_inicial), 1);
			$data_fim = formata_data(trim($tf_data_final), 1); 
//			$where_data_1 = " and ((t0.trn_data >= '".trim($data_inic)." 00:00') and (t0.trn_data <= '".trim($data_fim)." 23:59')) "; 
			$where_data_1a = " and ((vg_data_inclusao >= '".trim($data_inic)." 00:00:00') and (vg_data_inclusao <= '".trim($data_fim)." 23:59:59')) "; 
			$where_data_1b = " and (($where_mode_data >= '".trim($data_inic)." 00:00:00') and ($where_mode_data <= '".trim($data_fim)." 23:59:59')) "; 
			$where_data_2 = " and ((ve_data_inclusao >= '".trim($data_inic)." 00:00:00') and (ve_data_inclusao <= '".trim($data_fim)." 23:59:59')) "; 
		}

		
		if($dd_operadora) {
//			$where_opr_1 = " and (t0.opr_codigo = ".$dd_operadora.") ";
			$where_opr_1 = " and (vgm.vgm_opr_codigo = ".$dd_operadora.") ";
			if($dd_operadora_nome=='ONGAME') 
				$where_opr_2 = " and (ve_jogo = 'OG') ";
			elseif  ($dd_operadora_nome=='MU ONLINE') 
				$where_opr_2 = " and (ve_jogo = 'MU') ";
			elseif  ($dd_operadora_nome=='HABBO HOTEL') 
				$where_opr_2 = " and (ve_jogo = 'HB') ";
			else
				$where_opr_2 = " and (ve_jogo = 'xx') ";
		}
		if($dd_operadora=="") $dd_valor = "";

		if($dd_valor) {
//			$where_valor_1 = " and (t0.pin_valor = ".$dd_valor.") ";
			$where_valor_1 = " and (vgm.vgm_valor = ".$dd_valor.") ";
			$where_valor_2 = " and (ve_valor = ".$dd_valor.")";
		}

		
		if($dd_canal == ""){
		    $atimo = true;
			$where_atimo = " and vg.vg_ultimo_status_obs like '%Pagamento via AtimoPay%' ";
		}
		if($dd_canal) {
	
			if($dd_canal=="Site") {
				$where_canal_1 = " and vg.vg_ultimo_status_obs not like '%Pagamento via AtimoPay%' ";
				$where_canal_2 = " and (FALSE) ";
			}
			if($dd_canal=="POS") {
				$where_canal_1 = " and (FALSE) ";
				$where_canal_2 = " ";
			}
			//atimo
			if($dd_canal=="ATIMO") {
				$where_canal_1 = " and vg.vg_ultimo_status_obs like '%Pagamento via AtimoPay%' ";
				$where_canal_2 = " and (FALSE) ";
				$where_atimo = " ";
				$atimo = true;
			}
		}

		if(!empty($canal_pos)) {
			if(substr($canal_pos,0,1)=='P') {
				$where_canal_pos = " and tvgpo.tvgpo_canal = '".$canal_pos."'";
				$where_canal_pos_integradas = " and (FALSE) ";
			}
			if($canal_pos=="EPP") {
				$where_canal_pos = " and (FALSE) ";
				$where_canal_pos_integradas = " ";
			}
		}
                
                //IDS de integração
                if($dd_ids_integracao) {
			$where_ids_integracao = " and (vg.vg_integracao_parceiro_origem_id = '".$dd_ids_integracao."')";
		}

/*

Antigo 
	select t0.trn_data, t2.opr_nome, t0.pin_valor, 
		count(t0.pin_valor) as quantidade, sum(t0.pin_valor) as total_face, 'Site' as canal  
	from estat_venda t0, operadoras t2
	where (t0.opr_codigo=t2.opr_codigo) and (t0.opr_codigo <> 78) 
			".$where_data_1." ".$where_valor_1." ".$where_opr_1." ".$where_canal_1."
	group by trn_data, t2.opr_nome, t0.pin_valor 

*/		
//		$estat  = "select trn_data, opr_nome, pin_valor, quantidade, total_face, canal  
		$estat  = "select trn_data, opr_nome, pin_valor, sum(quantidade) as quantidade, sum(total_face) as total_face, canal, vg_id, vg_source
			from (
			";

		// L - Adiciona os pedidos Lans
		$estat .= "select vg.vg_id, vg.vg_data_inclusao as trn_data, t2.opr_nome, vgm.vgm_valor as pin_valor, sum(vgm.vgm_qtde) as quantidade, sum(vgm.vgm_valor*vgm.vgm_qtde) as total_face, 'Site' as canal, 'L' as vg_source  
				from tb_dist_venda_games vg inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id , operadoras t2 
				where vgm.vgm_opr_codigo=t2.opr_codigo ".$where_valor_1." ".$where_opr_1." and vg.vg_data_inclusao>='2008-01-01 00:00:00' and vg.vg_ultimo_status = ".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']." 
						".$where_data_1a." ".$where_canal_1." 
				group by vg.vg_data_inclusao, t2.opr_nome, vgm.vgm_valor, vg.vg_id 
			";
		$estat .= "	union all
		";

		// G - Adiciona os pedidos Gamers
		$estat .= "select vg.vg_id, $where_mode_data as trn_data, t2.opr_nome, vgm.vgm_valor as pin_valor, sum(vgm.vgm_qtde) as quantidade, sum(vgm.vgm_valor*vgm.vgm_qtde) as total_face, 'Site' as canal, 'G' as vg_source    
				from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id , operadoras t2 
				where vgm.vgm_opr_codigo=t2.opr_codigo ".$where_valor_1." ".$where_opr_1." and $where_mode_data>='2008-01-01 00:00:00' and vg.vg_ultimo_status = ".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']." and vg.vg_pagto_tipo != ".$GLOBALS['PAGAMENTO_PIN_EPREPAG_NUMERIC']." 
						".$where_data_1b." ".$where_canal_1." ".$where_ids_integracao."
				group by $where_mode_data, t2.opr_nome, vgm.vgm_valor, vg.vg_id 
			";
		$estat .= "	union all
		";

		if($atimo){
		
			// a+ - atimo - Adiciona os pedidos com pagamentos Saldo Gamers ATIMO
			$estat .= " select vg.vg_id, $where_mode_data as trn_data, t2.opr_nome, vgm.vgm_valor as pin_valor, sum(vgm.vgm_qtde) as quantidade, sum(vgm.vgm_valor*vgm.vgm_qtde) as total_face, 'Atimo' as canal, 'G' as vg_source    
					from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id , operadoras t2 
					where vgm.vgm_opr_codigo=t2.opr_codigo ".$where_valor_1." ".$where_opr_1." and $where_mode_data>='2008-01-01 00:00:00' and vg.vg_ultimo_status = ".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']." and vg.vg_pagto_tipo = ".$GLOBALS['PAGAMENTO_PIN_EPREPAG_NUMERIC']." 
							".$where_data_1b." ".$where_canal_1." ".$where_atimo." ".$where_ids_integracao."
					group by $where_mode_data, t2.opr_nome, vgm.vgm_valor, vg.vg_id 
				";
			
			$estat .= "	union all ";
		
		}
		
		// G+ - Adiciona os pedidos com pagamentos Saldo Gamers
		$estat .= "select vg.vg_id, $where_mode_data as trn_data, t2.opr_nome, vgm.vgm_valor as pin_valor, sum(vgm.vgm_qtde) as quantidade, sum(vgm.vgm_valor*vgm.vgm_qtde) as total_face, 'Site' as canal, 'G' as vg_source      
				from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id, operadoras t2 
				where vgm.vgm_opr_codigo=t2.opr_codigo ".$where_valor_1." ".$where_opr_1." and $where_mode_data>='2008-01-01 00:00:00' and vg.vg_ultimo_status = ".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']." and tvgpo.tvgpo_canal='G' and vg.vg_pagto_tipo = ".$GLOBALS['PAGAMENTO_PIN_EPREPAG_NUMERIC']." 
						".$where_data_1b." ".$where_canal_1." ".$where_ids_integracao."
				group by $where_mode_data, t2.opr_nome, vgm.vgm_valor, vg.vg_id 
			";
		$estat .= "	union all
		";

		// L+ - Adiciona os pedidos com pagamentos Saldo Lans
		$estat .= "select vg.vg_id, $where_mode_data as trn_data, t2.opr_nome, vgm.vgm_valor as pin_valor, sum(vgm.vgm_qtde) as quantidade, sum(vgm.vgm_valor*vgm.vgm_qtde) as total_face, 'Site' as canal, 'G' as vg_source      
				from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id, operadoras t2 
				where vgm.vgm_opr_codigo=t2.opr_codigo ".$where_valor_1." ".$where_opr_1." and $where_mode_data>='2008-01-01 00:00:00' and vg.vg_ultimo_status = ".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']." and tvgpo.tvgpo_canal='L' and vg.vg_pagto_tipo = ".$GLOBALS['PAGAMENTO_PIN_EPREPAG_NUMERIC']." 
						".$where_data_1b." ".$where_canal_1." ".$where_ids_integracao."
				group by $where_mode_data, t2.opr_nome, vgm.vgm_valor, vg.vg_id 
			";
		$estat .= "	union all
		";

		// P+ - Adiciona os pedidos com pagamentos Saldo POS
		$estat .= "select vg.vg_id, $where_mode_data as trn_data, t2.opr_nome, vgm.vgm_valor as pin_valor, sum(vgm.vgm_qtde) as quantidade, sum(vgm.vgm_valor*vgm.vgm_qtde) as total_face, 'POS' as canal, 'G' as vg_source      
				from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id, operadoras t2 
				where vgm.vgm_opr_codigo=t2.opr_codigo ".$where_valor_1." ".$where_opr_1." and $where_mode_data>='2008-01-01 00:00:00' and vg.vg_ultimo_status = ".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']." and SUBSTR(tvgpo.tvgpo_canal, 1, 1) ='P' and vg.vg_pagto_tipo = ".$GLOBALS['PAGAMENTO_PIN_EPREPAG_NUMERIC']." 
						".$where_data_1b." ".$where_canal_2." ".$where_canal_pos." ".$where_ids_integracao."
				group by $where_mode_data, t2.opr_nome, vgm.vgm_valor, vg.vg_id 
			";
		$estat .= "	union all
		";

		// P - Adiciona os pedidos POS
		$estat .= "select ve_id, date(ve_data_inclusao) as trn_data, (select opr_nome from operadoras o where o.opr_codigo=ve.ve_opr_codigo) as opr_nome, ve_valor as pin_valor, count(*) as quantidade, sum(ve_valor) as total_face, 'POS' as canal, 'P' as vg_source     
				from dist_vendas_pos ve 
				where 1=1 ".$where_data_2."".$where_valor_2." ".$where_opr_2." ".$where_canal_2." ".$where_canal_pos_integradas."
				group by date(ve_data_inclusao), ve_opr_codigo, ve_valor, ve_id 
			";

		$estat .= "	union all
		";

		// C - Adiciona os pedidos Cartões
		// falta este  !!!!!!!!!!!!

		// C+ - Adiciona os pedidos com pagamentos Saldo Cartões
		$estat .= " select vg.vg_id, $where_mode_data as trn_data, t2.opr_nome, vgm.vgm_valor as pin_valor, sum(vgm.vgm_qtde) as quantidade, sum(vgm.vgm_valor*vgm.vgm_qtde) as total_face, 'Cartões' as canal, 'G' as vg_source      
				from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id, operadoras t2 
				where vgm.vgm_opr_codigo=t2.opr_codigo ".$where_valor_1." ".$where_opr_1." and $where_mode_data>='2008-01-01 00:00:00' and vg.vg_ultimo_status = ".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']." and tvgpo.tvgpo_canal ='C' and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." 
						".$where_data_1b." ".$where_canal_2." ".$where_canal_pos." ".$where_ids_integracao."
				group by $where_mode_data, t2.opr_nome, vgm.vgm_valor, vg.vg_id 
		";
	/*
		$meu_ip_1 = '201.93.162.169';
		$meu_ip_2 = '189.62.151.212';
	
		if ($_SERVER['REMOTE_ADDR'] == $meu_ip_1 || $_SERVER['REMOTE_ADDR'] == $meu_ip_2) {
			echo $estat;
		}
	*/
/*
		// Este bloco não é mais necessário, é levado em conta no bloco C+
		// Adiciona os pedidos com pagamentos GoCash -> cartões
		$estat .= " 
				union all
		select vg.vg_id, vg.vg_data_concilia as trn_data, t2.opr_nome, vgm.vgm_valor as pin_valor, sum(vgm.vgm_qtde) as quantidade, sum(vgm.vgm_valor*vgm.vgm_qtde) as total_face, 'Cartões' as canal, 'G' as vg_source    
				from tb_venda_games vg 
					inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
					inner join tb_pag_compras tpc on tpc.idvenda = vg.vg_id 
					inner join operadoras t2 on vgm.vgm_opr_codigo=t2.opr_codigo 
				where 1=1 ".$where_valor_1." ".$where_opr_1." and $where_mode_data>='2008-01-01 00:00:00' and vg.vg_ultimo_status = ".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']." 
					".$where_data_1b." ".$where_canal_1."
					and tpc.valorpagtogocash !=0 
					and (vgm_opr_codigo!=49 and vgm_opr_codigo!=53)
					and vg.vg_pagto_tipo = 13
				group by vg.vg_data_concilia, t2.opr_nome, vgm.vgm_valor, vg.vg_id	
			";
*/
		// 

		$estat .= " ) v	
			";

//   "(case when ve_jogo='OG' then 'ONGAME' when ve_jogo='HB' then 'HABBO HOTEL' when ve_jogo='MU' then 'MU ONLINE' else '???' end)"

//	Para listar apenas registros da Rede Prepag
//				and ve_cod_rede=9999 
		
//--The End


		$estat .= " group by trn_data, opr_nome, pin_valor, canal, vg_id, vg_source \n"; 
		$estat .= " order by trn_data, opr_nome, pin_valor"; 

//if(b_is_G4BOX()) {
//	echo "<!-- ".str_replace("\n", "\n<br>", $estat)." --><br>";
//}

if(b_IsUsuarioWagner()) {
//echo "(R) ".str_replace("\n", "\n<br>", $estat)."<br>";
}
		$res_count = pg_query($estat);
		$total_table = pg_num_rows($res_count);
	

/*
		$estat .= " order by ".$ncamp; 
*/
		if($ordem == 0)
		{
			$estat .= " desc ";
			$img_seta = "/sys/imagens/seta_down.gif";	
		}
		else
		{
			$estat .= " asc ";
			$img_seta = "/sys/imagens/seta_up.gif";
		}

		$qtde_geral = 0;
		$valor_geral = 0;

//echo "".str_replace("\n", "<br>\n", $estat)."<br>";
//echo $estat."<br>";
//die("Stop");
		$s_ids_gamers = "";
		$s_ids_lans = "";
		$s_ids_outros = "";

		$n_ids_gamers = 0;
		$n_ids_lans = 0;
		$n_ids_outros = 0;

		$res_geral = pg_exec($connid, $estat);
		while($pg_geral = pg_fetch_array($res_geral))
		{
			$qtde_geral += $pg_geral['quantidade'];
			$valor_geral += $pg_geral['total_face'];

/*
if(b_IsUsuarioReinaldo()) {
			if($pg_geral['vg_source']=="G") {
				$s_ids_gamers .= $pg_geral['vg_id'].", ";
				$n_ids_gamers++;
			} else if($pg_geral['vg_source']=="L") {
				$s_ids_lans .= $pg_geral['vg_id'].", ";
				$n_ids_lans++;
			} else {
				$s_ids_outros .= $pg_geral['vg_id'].", ";
				$n_ids_outros++;
			}
}
*/
		}

if(b_IsUsuarioReinaldo()) {
//echo "<hr>Gamers ($n_ids_gamers) vg_id :".$s_ids_gamers."<br>";
//echo "<hr>Lans ($n_ids_lans) vg_id :".$s_ids_lans."<br>";
//echo "<hr>Outros ($n_ids_outros) vg_id :".$s_ids_outros."<br>";
//echo "<hr>Totais: (n_ids_gamers: $n_ids_gamers) (n_ids_lans: $n_ids_lans) (n_ids_outros: $n_ids_outros), Total".($n_ids_gamers+$n_ids_lans+$n_ids_outros)."<br>";
}

		$estat .= " limit ".$max; 
		$estat .= " offset ".$inicial;

	}
	else
		$estat = "select est_codigo from estabelecimentos where est_codigo = 0";
		
//	trace_sql($estat, "Arial", 2, "#666666", 'b');
$sql_transform=$estat;
	
	//echo $estat;
	
	$resestat = pg_exec($connid, $estat);

	if($max + $inicial > $total_table)
		$reg_ate = $total_table;
	else
		$reg_ate = $max + $inicial;
		
	$varsel  = "&cb_opr_teste=$cb_opr_teste&cb_estab_teste=$cb_estab_teste";
	$varsel .= "&tf_data_final=$tf_data_final&tf_data_inicial=$tf_data_inicial";
	$varsel .= "&tf_codigo_estab=$tf_codigo_estab&tf_nome_estab=$tf_nome_estab&dd_uf=$dd_uf&dd_uf_except=$dd_uf_except";
	$varsel .= "&dd_operadora=$dd_operadora&dd_valor=$dd_valor&dd_opr_area=$dd_opr_area";
?>
<html>
<head>

<link rel="stylesheet" href="/sys/css/css.css" type="text/css">
<title>E-Prepag</title>
<script language='javascript' src='/js/<?php echo LANG_NAME_CALENDAR_FILE; ?>'></script>
<script language='javascript' src='../../stats/js/jquery-1.4.4.js'></script>
<script language="JavaScript">
<!--
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}

<?php
if($_SESSION["tipo_acesso_pub"]=='AT') {
?>
//Carga Canais POS
$(document).ready(function () {
		$('#dd_canal').change(function(){
			var id = $(this).val();
			$.ajax({
				type: "POST",
				url: "/ajax/sys/ajaxCanaisPOS.php",
				data: "id="+id,
				success: function(html){
					$('#canalPOS').html(html);
				},
				error: function(){
					alert('erro valor');
				}
			});
		});
});

<?php
if ($dd_canal=="POS") {
?>
$(document).ready(function () {
	$('#dd_canal').ready(function(){
			var id = $(this).val();
			$.ajax({
				type: "POST",
				url: "/ajax/sys/ajaxCanaisPOS.php",
				data: "id=POS&canal_pos=<?php echo $canal_pos;?>",
				success: function(html){
					$('#canalPOS').html(html);
				},
				error: function(){
					alert('erro valor');
				}
			});
		});
});
<?php
}
}//end if($_SESSION["tipo_acesso_pub"]=='AT')
?>
//-->
</script>
<script>
postdata = <?php echo json_encode($_POST) ?>;

$(function(){
    
    $('#btn-download').click(function(){
   
        $.ajax({
            url: "pquery_download.php",
            type: "POST",
            data: postdata,
            
            beforeSend: function() {
                $('#btn-download').attr('disabled', 'true').val('Aguarde...');
            },
            
            success: function(data) {
                $('#btn-download').hide();
                $('#download-relatorio').show();
                $('#download-relatorio').find('a').attr('href', data)
            }
        });

    });
    
});
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
				 <?php //echo $estat;?>
                    <div class="col-md-12 ">
                        <strong><?php echo LANG_PINS_PAGE_TITLE_1; ?></strong>
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
                    <div class="row txt-cinza ">
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_PINS_START_DATE; ?></span>
                        </div>
                        <div class="col-md-3">
                            <input  alt="Calendário" name="tf_data_inicial" type="text" class="form-control w-ipt-medium pull-left data" id="tf_data_inicial" value="<?php  echo $tf_data_inicial ?>" size="9" maxlength="10">
                        </div>
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_PINS_END_DATE; ?></span>
                        </div>
                        <div class="col-md-3">
                            <input alt="Calendário" name="tf_data_final" type="text" class="form-control w-ipt-medium pull-left data" id="tf_data_final" value="<?php  echo $tf_data_final ?>" size="9" maxlength="10">
                        </div>

                    </div>
                    <div class="row txt-cinza top10">
<?php 
                    if(!b_is_G4BOX()) 
                    { 
?>
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_PINS_CHANNEL; ?></span>
                        </div>
                        <div class="col-md-3">
                            <select name="dd_canal" id="dd_canal" class="form-control">
                                    <option value="" <?php  if($dd_canal!="Site" and $dd_canal!="POS") echo "selected" ?>><?php echo LANG_PINS_ALL; ?></option>
                                    <option value="Site" <?php  if($dd_canal=="Site") echo "selected" ?>>Site</option>
                                    <option value="POS" <?php  if($dd_canal=="POS") echo "selected" ?>>POS</option> 
									<option value="ATIMO" <?php  if($dd_canal=="ATIMO") echo "selected" ?>>Atimo</option>
                            </select>
                            <div id='canalPOS'></div>
                        </div>
<?php           
                    } else 
                    { 
?>
                        <div class="col-md-2"></div>
                        <div class="col-md-3"></div>
<?php           
                    }
?>
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_PINS_REPORT_TYPE; ?></span>
                        </div>
                        <div class="col-md-3">
<?php  
                        if(b_is_Publisher()) 
                        {
?>
                            <span class="pull-right"><?php echo LANG_PINS_OUT; ?></span>
                            <input type="hidden" name="dd_mode" id="dd_mode" value="<?php echo $dd_mode?>">
<?php  
                        } else 
                        {
?>
                            <select name="dd_mode" id="dd_mode" class="form-control">
                                <option value="S" <?php  if($dd_mode=="S") echo "selected" ?>><?php echo LANG_PINS_OUT; ?></option>
                                <option value="V" <?php  if($dd_mode=="V") echo "selected" ?>><?php echo LANG_PINS_SALES; ?></option>
                            </select>
<?php 
                        } 
?>
                        </div>
                    </div>
                    <div class="row txt-cinza top10">
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_PINS_OPERATOR; ?></span>
                        </div>
                        <div class="col-md-3">
<?php 
                            if($_SESSION["tipo_acesso_pub"]=='PU') {

                            echo $_SESSION["opr_nome"];
?>
                            <input type="hidden" name="dd_operadora" id="dd_operadora" value="<?php echo $dd_operadora?>">
<?php 
                            } else {
?>
                            <select name="dd_operadora" id="dd_operadora" class="form-control" onChange="document.form1.dd_valor.value=''">
                                <option value=""><?php echo LANG_PINS_ALL_OPERATORS; ?></option>
                                <?php  while ($pgopr = pg_fetch_array ($resopr)) { ?>
                                <option value="<?php  echo $pgopr['opr_codigo'] ?>" <?php  if($pgopr['opr_codigo'] == $dd_operadora) echo "selected" ?>><?php  echo $pgopr['opr_nome'] ?> (<?php echo $pgopr['opr_codigo']?>)</option>
                                <?php  } ?>
                            </select>
<?php 
                            } 
?>
                        </div>
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_PINS_VALUE; ?></span>
                        </div>
                        <div class="col-md-3">
                            <select name="dd_valor" id="dd_valor" class="form-control">
                                <option value=""><?php echo LANG_PINS_ALL_VALUES; ?></option>
<?php 
                        if($resval) {
                            $resval_row = pg_fetch_array($resval); 
                            for($i=1;$i<=15;$i++) {
                                if($resval_row["opr_valor$i"]>0) {
?>
                                <option value="<?php echo $resval_row["opr_valor$i"]; ?>" <?php if ($dd_valor == $resval_row["opr_valor$i"]) echo "selected";?>><?php echo $resval_row["opr_valor$i"]; ?></option>
<?php 
                                }
                                if($i>15) break;
                            }
                        } 
?>
                          </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" name="BtnSearch" value="<?php echo LANG_PINS_SEARCH_2; ?>" class="btn pull-right btn-success"><?php echo LANG_INTEGRATION_SEARCH;?></button>
                        </div>
                    </div>
<?php
                    if(!b_is_Publisher() && count(retornaIdsIntegracao($dd_operadora)) > 0) { //if(b_IsUsuarioWagner()) {
?>
                    <div class="row txt-cinza top10">
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_INTEGRATION; ?></span>
                        </div>
                        <div class='col-md-3'>
<?php
                        echo montaSelectIdsIntegracao($dd_operadora, $dd_ids_integracao);
?>
                        </div>
                    </div>
<?php
                }

                if($data_inic_invalida == true) echo '<div class="row txt-cinza txt-vermelho">'.LANG_PINS_START_DATE."</div>";
                if($data_fim_invalida == true) echo '<div class="row txt-cinza txt-vermelho">'.LANG_PINS_END_DATE."</div>";
                if($data_inicial_menor == true) echo '<div class="row txt-cinza txt-vermelho">'.LANG_PINS_COMP_DATE_START_WITH_END."</div>";
                
                $colspan = 6;
                
                $cabecalho = "'".LANG_PINS_DATE."',";
?>
                </form>
                <div class="row txt-cinza espacamento">
                    <div class="col-md-12 bg-cinza-claro">
                        <table id="table" class="table bg-branco txt-preto fontsize-p">
                            <thead>
                              <tr class="bg-cinza-claro">
                                <th class="text-center"><?php echo LANG_PINS_DATE; ?></th>
                                <th class="text-center">
<?php 
                            if(!b_is_G4BOX()) 
                            {
                                $cabecalho .= "'".LANG_PINS_CHANNEL."',";
?>
                                    <strong><?php echo LANG_PINS_CHANNEL; ?></div>
<?php 
                            } else 
                            { 
?>
                                    &nbsp;
<?php 
                            } 
                            $cabecalho .= "'".LANG_PINS_OPERATOR."','".LANG_PINS_QUANTITY_1."','".LANG_PINS_FACE_VALUE."','".LANG_PINS_TOTAL_VALUE."',";
?>
                                </th>
                                <th class="text-center"><?php echo LANG_PINS_OPERATOR; ?></th>
                                <th class="text-center"><?php echo LANG_PINS_QUANTITY_1;  ?></th>
                                <th class="text-center"><?php echo LANG_PINS_FACE_VALUE; ?></th>
                                <th class="text-center"><?php echo LANG_PINS_TOTAL_VALUE; ?></th>
<?php 
                            if(b_is_Financeiro()) 
                            {
                                $cabecalho .= "'vg_id','vg_source'";
                                $colspan = 8;
?>
                                <th class="text-center"><strong>vg_id</strong></th>
                                <th class="text-center"><strong>vg_source</strong></th>
<?php
                            }
?>
                              </tr>
                            </thead>
                            <tr>
                                <th colspan="<?php echo $colspan;?>">
<?php
                                if($total_table > 0) 
                                {

                                    echo LANG_SHOW_DATA; 
?>
                                    <strong><?php  echo $inicial + 1 ?></strong> 
<?php 
                                    echo LANG_TO; 
?>
                                    <strong><?php  echo $reg_ate ?></strong>
<?php 
                                    echo LANG_FROM; ?>
                                    <strong><?php  echo $total_table ?></strong>
                                    <span id="txt_totais" class="txt-azul-claro"></span>
<?php  
                                } 
?>
                                </th>
                            </tr>
                            <tbody>
<?php
                            while ($pgrow = pg_fetch_array($resestat))
                            {
                                $valor = true;
                                $qtde_total_tela += $pgrow['quantidade'];
                                $valor_total_tela += $pgrow['total_face'];
?>                                
                                <tr class="trListagem">
                                    <td class="text-center"><?php  echo substr($pgrow['trn_data'], 0, 19) ?></td>
                                    <td><?php echo (!b_is_G4BOX()) ? $pgrow['canal'] : "&nbsp";?></td>
                                    <td><?php  echo $pgrow['opr_nome'] ?></td>
                                    <td class="text-right"><?php  echo $pgrow['quantidade'] ?></td>
                                    <td class="text-right"><?php  echo number_format($pgrow['pin_valor'], 2, ',', '.') ?></td>
                                    <td class="text-right"><?php  echo number_format(($pgrow['total_face']), 2, ',', '.') ?></td>
<?php
                                if(b_is_Financeiro()) {
                                    $schanel = (($pgrow['vg_canal']=="L")?"pdv":"gamer");
?>
                                    <td class="text-right"><a href="https://<?php echo $_SERVER['SERVER_NAME'] ?>:8080/<?php  echo $schanel; ?>/vendas/com_venda_detalhe.php?venda_id=<?php  echo "".$pgrow['vg_id']; ?>" target="_blank"><?php  echo $pgrow['vg_id'] ?></a></td>
                                    <td class="text-right"><?php echo $pgrow['vg_source']; ?></td>
<?php 
                                }
?>
                                </tr>
<?php
                            }
                            if (!$valor) 
                            {
?>
                                <tr> 
                                    <td colspan="<?php echo $colspan;?>">
                                        <strong><?php echo LANG_NO_DATA; ?>.</strong>
                                    </td>
                                </tr>
<?php
                            } else 
                            {
                                $time_end = getmicrotime();
                                $time = $time_end - $time_start;
?>
                                <tr class="bg-cinza-claro"> 
                                    <td colspan="3"><strong><?php echo LANG_PINS_SUBTOTAL; ?></strong></td>
                                    <td class="text-right"><strong><?php  echo number_format($qtde_total_tela, 0, ',', '.') ?></strong></td>
                                    <td class="text-right"><strong><?php  echo number_format($valor_total_tela, 2, ',', '.') ?></strong></td>
                                    <td colspan="<?php echo (b_is_Financeiro()) ? 3 : 1;?>"></td>
                                </tr>
                                <tr class="bg-cinza-claro">
                                    <td colspan="3"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong><?php echo LANG_PINS_TOTAL; ?></strong></font></td>
                                    <td class="text-right"><strong><?php  echo number_format($qtde_geral, 0, ',', '.') ?></strong></td>
                                    <td class="text-right"><strong><?php  echo number_format($valor_geral, 2, ',', '.') ?></strong></td>
                                    <td colspan="<?php echo (b_is_Financeiro()) ? 3 : 1;?>"></td>
                                </tr>
                                <script language="JavaScript">
                                  document.getElementById('txt_totais').innerHTML = '( <?php echo number_format($valor_total_tela, 2, ',', '.') ?> / <?php echo number_format($valor_geral, 2, ',', '.') ?>)';
                                </script>
<?php
                                paginacao_query($inicial, $total_table, $max, $colspan, $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel);
?>
                                <tr class="bg-cinza-claro">
                                    <td colspan="<?php echo $colspan;?> " class="fontsize-pp">
                                        <strong><?php echo LANG_PINS_LAST_MSG; ?>.</strong>
                                    </td>
                                </tr>
                                <tr class="bg-cinza-claro">
                                    <td colspan="<?php echo $colspan;?>" class="fontsize-pp"><?php echo LANG_PINS_SEARCH_MSG.' '.number_format($time, 2, '.', '.').' '.LANG_PINS_SEARCH_MSG_UNIT; ?></td>
                                </tr>
                                <tr class="bg-cinza-claro"> 
                                    <td colspan="<?php echo $colspan;?>" class="fontsize-pp"><?php echo date('Y-m-d H:i:s'); ?></td>
                                </tr>
                                
                                
<?php  
                            } 
?>                                
                            </tbody>
                        </table>
                        <div class="row text-center" style="margin-bottom: 15px;">
                            <a href="#" class="btn downloadCsv btn-info ">Download CSV</a>
                        </div>                        
                       <!-- <center>
                            <input type="button" value="GERAR ARQUIVO" id="btn-download" />
                            <div id="download-relatorio" style="display: none">
                                <a href="#">Clique aqui para fazer o download do relatório.</a>
                            </div>
                        </center>-->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<link href="/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="/js/facebook.js"></script>
<script src="/js/jquery-ui-1.9.2.custom.min.js"></script>
<script type="text/javascript" src="/js/table2CSV.js"></script>
<script src="/js/global.js"></script>
<script>
$(function(){
    $('#table').table2CSV({header:[<?php echo $cabecalho; ?>],toStr:""});
    
    var optDate = new Object();
        optDate.interval = 1;

    setDateInterval('tf_data_inicial','tf_data_final',optDate);
});
</script>

<!-- FIM CODIGO NOVO -->
<?php  
require_once $raiz_do_projeto . "public_html/sys/includes/rodape_sys.php";
?>