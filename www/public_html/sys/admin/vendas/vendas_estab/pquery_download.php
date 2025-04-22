<?php
ob_start();
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

		if($dd_canal) {
			if($dd_canal=="Site") {
				$where_canal_1 = "  ";
				$where_canal_2 = " and (FALSE) ";
			}
			if($dd_canal=="POS") {
				$where_canal_1 = " and (FALSE) ";
				$where_canal_2 = " ";
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
	
        $estat = preg_replace('/limit [0-9]*/s', '', $estat);
        $estat = preg_replace('/offset [0-9]*/s', '', $estat);

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
				url: "ajaxCanaisPOS.php",
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
				url: "ajaxCanaisPOS.php",
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

  <tr> 
    <td align="center" valign="top" bgcolor="#FFFFFF"> <table width="100%" border="0" cellspacing="0" cellpadding="3" height="100%">
        <tr valign="top"> 
          <td height="100%"> <form name="form1" method="post" action="">
              <table width="100%" border="0" cellpadding="2" cellspacing="2">
                <tr bgcolor="#00008C"> 
                  <td colspan="11" bgcolor="#ECE9D8"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo LANG_PINS_SEARCH_1; ?></font></td>
                </tr>
                <tr bgcolor="#F5F5FB"> 
                  <td width="96"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo LANG_PINS_START_DATE; ?>:</font></td>
                  <td width="196"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
                    <input name="tf_data_inicial" type="text" class="form" id="tf_data_inicial" value="<?php  echo $tf_data_inicial ?>" size="9" maxlength="10">
                    <a href="#"><img src="/sys/imagens/cal.gif" width="16" height="16" alt="Calendário" onClick="popUpCalendar(this, form1.tf_data_inicial, 'dd/mm/yyyy')" border="0" align="absmiddle"></a> 
                    </font></td>
                  <td width="90"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo LANG_PINS_END_DATE; ?>:</font></td>
                  <td colspan="3"> <font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
                    <input name="tf_data_final" type="text" class="form" id="tf_data_final" value="<?php  echo $tf_data_final ?>" size="9" maxlength="10">
                    <a href="#"><img src="/sys/imagens/cal.gif" width="16" height="16" alt="Calendário" onClick="popUpCalendar(this, form1.tf_data_final, 'dd/mm/yyyy')" border="0" align="absmiddle"></a> 
                    </font></td>
                  <td width="62">&nbsp;</td>
                </tr>
                <tr bgcolor="#F5F5FB"> 
				<?php if(!b_is_G4BOX()) { ?>
                  <td width="96"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo LANG_PINS_CHANNEL; ?> </font></td>
                  <td width="196"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
                    <select name="dd_canal" id="dd_canal" class="combo_normal">
                      <option value="" <?php  if($dd_canal!="Site" and $dd_canal!="POS") echo "selected" ?>><?php echo LANG_PINS_ALL; ?></option>
                      <option value="Site" <?php  if($dd_canal=="Site") echo "selected" ?>>Site</option>
                      <option value="POS" <?php  if($dd_canal=="POS") echo "selected" ?>>POS</option>
                    </select>
						<div id='canalPOS'></div>
					</font>
					</td>
				<?php } else { ?>
                  <td width="96"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
                  <td width="196"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
				<?php } ?>
                  <td width="90"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo LANG_PINS_REPORT_TYPE; ?>: &nbsp;</font></td>
                  <td colspan="3"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
					<?php  if(b_is_Publisher()) { ?>
						<span style="font-weight: bold"><?php echo LANG_PINS_OUT; ?></span>
						<input type="hidden" name="dd_mode" id="dd_mode" value="<?php echo $dd_mode?>">
					<?php  } else { ?>	
					<select name="dd_mode" id="dd_mode" class="combo_normal">
					  <option value="S" <?php  if($dd_mode=="S") echo "selected" ?>><?php echo LANG_PINS_OUT; ?></option>
					  <option value="V" <?php  if($dd_mode=="V") echo "selected" ?>><?php echo LANG_PINS_SALES; ?></option>
					</select>
					<?php 
					  } 
					?>
					</font></td>
                  <td width="62"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
                </tr>
                <tr bgcolor="#F5F5FB"> 
                  <td width="96"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo LANG_PINS_OPERATOR; ?>:</font></td>
                  <td width="196"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
					<?php 
						if($_SESSION["tipo_acesso_pub"]=='PU') {
					?>
						<?php echo $_SESSION["opr_nome"]?>
						<input type="hidden" name="dd_operadora" id="dd_operadora" value="<?php echo $dd_operadora?>">
					<?php 
					  } else {
					?>

                    <select name="dd_operadora" id="dd_operadora" class="combo_normal" onChange="document.form1.dd_valor.value=''">
                      <option value=""><?php echo LANG_PINS_ALL_OPERATORS; ?></option>
                      <?php  while ($pgopr = pg_fetch_array ($resopr)) { ?>
                      <option value="<?php  echo $pgopr['opr_codigo'] ?>" <?php  if($pgopr['opr_codigo'] == $dd_operadora) echo "selected" ?>><?php  echo $pgopr['opr_nome'] ?> (<?php echo $pgopr['opr_codigo']?>)</option>
                      <?php  } ?>
                    </select>
					<?php 
					  } 
					?>
                    </font></td>
                  <td ><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo LANG_PINS_VALUE; ?>:</font></td>
                  <td colspan="3"> <font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
                    <select name="dd_valor" id="dd_valor" class="combo_normal">
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
                    </font></td>
                  <td width="62"><div align="center"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
                      <input type="submit" name="BtnSearch" value="<?php echo LANG_PINS_SEARCH_2; ?>" class="botao_search">
                      </font></div></td>
                </tr>
                <tr bgcolor="#F5F5FB">
                    <td colspan="2"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">
                    <?php
                    if(!b_is_Publisher()) { //if(b_IsUsuarioWagner()) {
                        echo montaSelectIdsIntegracao($dd_operadora, $dd_ids_integracao);
                    }
                    ?>  
                    </font></td>
                </tr>
              </table>
            </form>
            <table border="0" cellpadding="0" cellspacing="0" width="897">
              <tr> 
                <td> 
                  <?php 
					if($data_inic_invalida == true) echo "<br><font face='Arial, Helvetica, sans-serif' size='2' color='#FF0000'><b>".LANG_PINS_START_DATE."</b></font>";
					if($data_fim_invalida == true) echo "<br><font face='Arial, Helvetica, sans-serif' size='2' color='#FF0000'><b>".LANG_PINS_END_DATE."</b></font>";
					if($data_inicial_menor == true) echo "<br><font face='Arial, Helvetica, sans-serif' size='2' color='#FF0000'><b>".LANG_PINS_COMP_DATE_START_WITH_END."</b></font>";
				?>
                </td>
              </tr>
            </table>
            <table border='0' width="100%" cellpadding="2" cellspacing="1">
              <tr> 
                <td><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
                  <?php  if($total_table > 0) { ?>
                  <?php echo LANG_SHOW_DATA; ?> <strong><?php  echo $inicial + 1 ?></strong> 
                  <?php echo LANG_TO; ?> <strong><?php  echo $reg_ate ?></strong> <?php echo LANG_FROM; ?> <strong><?php  echo $total_table ?></strong> <span id="txt_totais" style="color:blue"></span></font> 
                  <?php  } ?>
                </td>
                <td><div align="right"><a href="https://<?php  echo $_SERVER['SERVER_NAME'] . ":" . $_SERVER['SERVER_PORT'] ?>/sys/admin/commerce/index.php"><img src="/sys/imagens/voltar_menu.gif" width="107" height="15" border="0"></a> 
                    <?php 
					$_SESSION['sqldata']=$sql_transform;
					?>
                    </div>
                </td>
              </tr>
            </table>
            <table width="100%" border='0' cellpadding="2" cellspacing="1">
              <tr bgcolor="#00008C"> 
                <?php 
				if($ordem == 1)
					$ordem = 0;
				else
					$ordem = 1;
				?>
                <td><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><?php echo LANG_PINS_DATE; ?></font></strong> 
                  <?php  if($ncamp == 'trn_data') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                </td>
                <!--td><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><a href="<?php  echo $default_add."?ordem=".$ordem."&ncamp=est_codigo&inicial=".$inicial.$varsel ?>" class="link_br">Codigo</a></font></strong> 
                  <?php  if($ncamp == 'est_codigo') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                </td>
                <td><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><a href="<?php  echo $default_add."?ordem=".$ordem."&ncamp=nome_fantasia&inicial=".$inicial.$varsel ?>" class="link_br">Canal 
                  de Venda</a></font></strong> 
                  <?php  if($ncamp == 'nome_fantasia') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                </td>
                <td><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><a href="<?php  echo $default_add."?ordem=".$ordem."&ncamp=nome_fantasia&inicial=".$inicial.$varsel ?>" class="link_br">Estabelecimento</a></font></strong> 
                  <?php  if($ncamp == 'nome_fantasia') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                </td>
                <td><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><a href="<?php  echo $default_add."?ordem=".$ordem."&ncamp=municipio&inicial=".$inicial.$varsel ?>" class="link_br">Municipio</a></font></strong> 
                  <?php  if($ncamp == 'municipio') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                </td-->
                <td><div align="center"> 
				<?php if(!b_is_G4BOX()) { ?>
                    <?php  if($ncamp == 'canal') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                    <strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><?php echo LANG_PINS_CHANNEL; ?>
                    </font></strong></div>
				<?php } else { ?>
					&nbsp;
				<?php } ?>
					</td>
                <td><div align="center"><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><?php echo LANG_PINS_OPERATOR; ?></font></strong> 
                  <?php  if($ncamp == 'opr_nome') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?></div>
                </td>
                <td><div align="right"> 
                    <?php  if($ncamp == 'quantidade') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                    <strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><?php echo LANG_PINS_QUANTITY_1; ?></font></strong></div></td>
                <td><div align="right"> 
                    <?php  if($ncamp == 'pin_valor') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                    <strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><?php echo LANG_PINS_FACE_VALUE; ?></font></strong></div></td>
                <td><div align="right"> 
                    <?php  if($ncamp == 'total_face') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                    <strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><?php echo LANG_PINS_TOTAL_VALUE; ?></font></strong></div></td>
              <?php 
				if(b_is_Financeiro()) {
				?>
                <td><div align="right"> 
                    <?php  if($ncamp == 'total_face') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                    <strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif">vg_id</font></strong></div></td>
                <td><div align="right"> 
                    <?php  if($ncamp == 'total_face') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                    <strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif">vg_source</font></strong></div></td>
              <?php 
				}
				?>
              </tr>
              <?php 

					$cor1 = $query_cor1;
					$cor2 = $query_cor1;
					$cor3 = $query_cor2;
                                        
                                        $csv = array();
                                        
                                        $csv_headers = "Data;Canal;Operadora;Qtde;Valor da Face;Valor Total";

                                        if(b_is_Financeiro())
                                            $csv_headers .= ";VG_ID;VG_SOURCE";
                                        
                                        $csv[] = $csv_headers;
                                        
					while ($pgrow = pg_fetch_array($resestat))
					{
						$valor = true;

						$qtde_total_tela += $pgrow['quantidade'];
						$valor_total_tela += $pgrow['total_face'];
				?>
              <tr bgcolor="#f5f5fb"> 
                <td bgcolor="<?php  echo $cor1 ?>"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php  echo substr($pgrow['trn_data'], 0, 19) ?></font></td>
                <!--td bgcolor="<?php  echo $cor1 ?>"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php  echo $pgrow['est_codigo'] ?></font></td>
                <td bgcolor="<?php  echo $cor1 ?>"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php  echo $pgrow['nome'] ?></font></td>
                <td bgcolor="<?php  echo $cor1 ?>"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php  echo $pgrow['nome_fantasia'] ?></font></td>
                <td bgcolor="<?php  echo $cor1 ?>"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php  echo $pgrow['municipio'] ?></font></td-->
                <td bgcolor="<?php  echo $cor1 ?>">
					<?php if(!b_is_G4BOX()) { ?>
						<div align="center"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php  echo $pgrow['canal'] ?></font></div>
					<?php } else { ?>
						&nbsp;
					<?php } ?>
				</td>
                <td bgcolor="<?php  echo $cor1 ?>"><div align="center"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php  echo $pgrow['opr_nome'] ?></font></div></td>
                <td bgcolor="<?php  echo $cor1 ?>"><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php  echo $pgrow['quantidade'] ?></font></div></td>
                <td bgcolor="<?php  echo $cor1 ?>"><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php  echo number_format($pgrow['pin_valor'], 2, ',', '.') ?></font></div></td>
                <td bgcolor="<?php  echo $cor1 ?>"><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php  echo number_format(($pgrow['total_face']), 2, ',', '.') ?></font></div></td>

              <?php 
if(b_is_Financeiro()) {
				  $schanel = (($pgrow['vg_canal']=="L")?"dist_":"");
				?>
                <td bgcolor="<?php  echo $cor1 ?>"><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><a href="http://www.e-prepag.com.br:8080/bkov2_prepag/<?php  echo $schanel; ?>commerce/com_venda_detalhe.php?venda_id=<?php  echo "".$pgrow['vg_id']; ?>" target="_blank"><?php  echo $pgrow['vg_id'] ?></a></font></div></td>
                <td bgcolor="<?php  echo $cor1 ?>"><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php  echo $pgrow['vg_source'] ?></font></div></td>
              <?php 
}
				?>
              </tr>
              <?php 
				 		if($cor1 == $cor2)
							$cor1 = $cor3;
						else
							$cor1 = $cor2;
                                                
                                                
                                                $csv_row = array();
    
                                                $csv_row[] = substr($pgrow['trn_data'], 0, 19);
                                                $csv_row[] = (!b_is_G4BOX()) ? $pgrow['canal'] : "";
                                                $csv_row[] = $pgrow['opr_nome'];
                                                $csv_row[] = $pgrow['quantidade'];
                                                $csv_row[] = number_format($pgrow['pin_valor'], 2, ',', '.');
                                                $csv_row[] = number_format(($pgrow['total_face']), 2, ',', '.');

                                                if(b_is_Financeiro()) {
                                                    $csv_row[] = $pgrow['vg_id'];
                                                    $csv_row[] = $pgrow['vg_source'];
                                                }
                                                
                                                $csv[] = implode(";", $csv_row);
                                                
					}
			 		if (!$valor) { ?>
              <tr bgcolor="#f5f5fb"> 
                <td colspan="10" bgcolor="<?php  echo $cor1 ?>"><div align="center"><font size="2" face="Arial, Helvetica, sans-serif" color="#666666"><strong><br>
                    <?php echo LANG_NO_DATA; ?>.<br>
                    <br>
                    </strong></font></div></td>
              </tr>
              <?php  } else { ?>
              <tr bgcolor="#E4E4E4"> 
                <td colspan="3"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong><?php echo LANG_PINS_SUBTOTAL; ?></strong></font></td>
                <td><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong><?php  echo number_format($qtde_total_tela, 0, ',', '.') ?></strong></font></div></td>
                <td colspan="3"><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong><?php  echo number_format($valor_total_tela, 2, ',', '.') ?></strong></font></div></td>
              </tr>
              <tr bgcolor="#E4E4E4"> 
                <td colspan="3"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong><?php echo LANG_PINS_TOTAL; ?></strong></font></td>
                <td><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong><?php  echo number_format($qtde_geral, 0, ',', '.') ?></strong></font></div></td>
                <td colspan="2"><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong><?php  echo number_format($valor_geral, 2, ',', '.') ?></strong></font></div></td>
              </tr>
<script language="JavaScript">
  document.getElementById('txt_totais').innerHTML = '( <?php echo number_format($valor_total_tela, 2, ',', '.') ?> / <?php echo number_format($valor_geral, 2, ',', '.') ?>)';
</script>

              <?php 
					paginacao_query($inicial, $total_table, $max, '9', $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel);
				?>
                <tr bgcolor="#E4E4E4"> 
                <td colspan="10" bgcolor="#FFFFFF"><font color="#666666" size="1" face="Arial, Helvetica, sans-serif"><strong> 
                  <?php echo LANG_PINS_LAST_MSG; ?>. </strong></font></td>
              </tr>
              <?php  } ?>
	          <?php 
					$time_end = getmicrotime();
					$elapsed_time = $time_end - $time_start;
				?>
              <tr> 
                <td height="52" colspan="10" bgcolor="#FFFFFF"><p><font size="1" face="Arial, Helvetica, sans-serif" color="#666666"><?php  echo LANG_POS_SEARCH_MSG." ".number_format($elapsed_time, 2, '.', '.')." ".LANG_POS_SEARCH_MSG_UNIT ?> 
                    </font></p>
                  </td>
              </tr>
            </table>
			</td>
        </tr>
      </table>
    <center>
        <input type="button" value="DOWNLOAD" id="btn-download" />
    </center>
    
    </td>
  </tr>
  <tr>
    <td colspan="3">
      <?php  require_once $raiz_do_projeto . "public_html/sys/includes/rodape_sys.php"; ?>
      <div align="center"></div></td></tr>
</table>    
</body>
</html>

<?php 
ob_clean();

$dirpath = $raiz_do_projeto . "public_html/tmp/txt/";
$webpath = "/tmp/txt/";
$filename = "relatorio-" . time() . ".csv"; 

$content = implode("\n", $csv);

$content = str_replace('.', '', $content);
$content = str_replace(',', '.', $content);

if(!file_put_contents($dirpath.$filename, $content)){
    echo "Erro ao criar arquivo";
    die;
}

echo $webpath . $filename;

?>