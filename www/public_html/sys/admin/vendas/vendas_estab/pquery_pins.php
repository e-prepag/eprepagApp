<?php  
    if(isset($_GET["downloadCsv"]) && $_GET["downloadCsv"] == 1)
        ob_start();
    require_once "../../../../../includes/constantes.php";
    require_once $raiz_do_projeto . "public_html/sys/includes/topo_sys.php";
    set_time_limit ( 3000 ) ;
    $pos_pagina = $seg_auxilar;
    $time_start = getmicrotime();


   // echo $dd_vendas_pins;
//echo "dd_operadora: ".$dd_operadora."<br>";
//echo "dd_mode: ".$dd_mode."<br>";
//echo "Submit: $Submit<br>";
	if($_SESSION["tipo_acesso_pub"]=='PU') {
		$dd_operadora = $_SESSION["opr_codigo_pub"];
		$dd_mode = "S";
//		$Submit = "Buscar";
	}

	if(!$dd_mode || ($dd_mode!='V')) {
		$dd_mode = "S";
	}
	$where_mode_data = "vg.vg_data_inclusao";	// default
//	if($dd_mode=='S') $where_mode_data = "vg.vg_data_concilia";
	if($dd_mode=='S') $where_mode_data = "COALESCE(vg.vg_data_concilia, (select datacompra from tb_pag_compras p where p.idvenda=vg.vg_id ))";

	$dd_vendas_pins_diff = ($dd_vendas_pins)?$dd_vendas_pins_diff:false;
	if(!$ncamp)            $ncamp           = 'trn_data';
	if(!$tf_data_final)    $tf_data_final   = date('d/m/Y');
	if(!$tf_data_inicial)  $tf_data_inicial = date('d/m/Y');
	if(!$inicial)          $inicial         = 0;
	if(!$range)            $range           = 1;
	if(!$ordem)            $ordem           = 0;
//	if($BtnSearch)         $inicial         = 0;
//	if($BtnSearch)         $range           = 1;
//	if($BtnSearch)         $total_table     = 0;
	if($BtnSearch && $BtnSearch!=1 ) {
		$inicial     = 0;
		$range       = 1;
		$total_table = 0;
		$FrmEnviar   = 1;
	} elseif($inicial > 0 || $downloadCsv == 1) {
                $FrmEnviar   = 1;
        }
        else {
		$FrmEnviar   = 0;
	}
//echo "dd_vendas_pins: ".(($dd_vendas_pins)?"ON":"off")."<br>";
//echo "dd_vendas_pins_diff: ".(($dd_vendas_pins_diff)?"ON":"off")."<br>";

	$data_inicial_limite = data_menos_n(date('d/m/Y'), 120);
	$data_inicial_limite = '01/08/2004';
	
	
	$default_add  = nome_arquivo($PHP_SELF);
	$img_proxima  = "https://".$_SERVER['SERVER_NAME'].":8080/images/proxima.gif";
	$img_anterior = "https://".$_SERVER['SERVER_NAME'].":8080/images/anterior.gif";
	$max          = 6000; //$qtde_reg_tela;
	$range_qtde   = $qtde_range_tela;

//	$resuf = pg_exec($connid, "select uf from uf order by uf");
//	$resuf_except = pg_exec($connid, "select uf from uf order by uf");

	if($cb_opr_teste)
		$resopr = pg_exec($connid, "select opr_nome, opr_codigo from operadoras where (opr_status = '".$operadora_ativada."') order by opr_nome");
	else
		$resopr = pg_exec($connid, "select opr_nome, opr_codigo from operadoras where (opr_status = '".$operadora_ativada."') and (opr_codigo <> ".$opr_teste.") order by opr_nome");

	if($dd_operadora)
	{			
		$res_opr_info = pg_exec($connid, "select opr_codigo, opr_nome, opr_pin_online from operadoras where opr_codigo=".$dd_operadora."");
		$pg_opr_info = pg_fetch_array($res_opr_info);

		$dd_operadora_nome = $pg_opr_info['opr_nome'];
	
		if($pg_opr_info['opr_pin_online'] == 0)
			$resval = pg_exec($connid, "select pin_valor as valor from pins where opr_codigo='".$pg_opr_info['opr_codigo']."' group by pin_valor order by pin_valor");
		else
		{
			$resval = pg_exec($connid, "select valor_fixo as valor from pin_valor_lista t0, pin_valor_fixo t1 where t0.valor_lista_cod = t1.valor_lista_cod and opr_codigo = ".$pg_opr_info['opr_codigo']." group by valor_fixo order by valor_fixo");
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

	if($FrmEnviar == 1) {

		$where_data_1a = "";
		$where_data_1b = "";
		$where_data_2 = "";
		$where_valor_1 = "";
		$where_valor_2 = "";
		$where_opr_1 = "";
		$where_opr_2 = "";
		$where_canal_1a = "";
		$where_canal_1b = "";
		$where_canal_2 = "";

		if($tf_data_inicial && $tf_data_final) 
		{
			$data_inic = formata_data(trim($tf_data_inicial), 1);
			$data_fim = formata_data(trim($tf_data_final), 1); 

//			$where_data_1 = " and ((t0.trn_data >= '".trim($data_inic)." 00:00') and (t0.trn_data <= '".trim($data_fim)." 23:59')) "; 
			$where_data_1a = " and ((vg_data_inclusao >= '".trim($data_inic)." 00:00:00') and (vg_data_inclusao <= '".trim($data_fim)." 23:59:59')) "; 

			$where_data_1b = " and (($where_mode_data >= '".trim($data_inic)." 00:00:00') and ($where_mode_data <= '".trim($data_fim)." 23:59:59')) "; 
// Dummy para mostrar uma página só
//$tf_data_final   = '2010-05-31 23:49:59';
//$tf_data_inicial = '2010-05-31 10:13:21';
//			$where_data_1b = " and (($where_mode_data >= '2010-05-31 10:13:21') and ($where_mode_data <= '2010-05-31 23:49:59')) "; 
// End Dummy

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

//echo "dd_vendas_pins: ".(($dd_vendas_pins)?"ON":"off")."<br>";
//echo "dd_canal: $dd_canal<br>";
		if($dd_canal) {
			if($dd_canal=="Site") {
				$where_canal_1a = "  ";
				$where_canal_1b = " and vg_ultimo_status_obs not like '%AtimoPay%' and vg_http_referer_origem <> 'ATIMO' ";
				$where_canal_2 = " and (FALSE) ";
			}
			if($dd_canal=="SiteLH") {
				$where_canal_1a = "  ";
				$where_canal_1b = " and (FALSE) ";
				$where_canal_2 = " and (FALSE) ";
			}
			if($dd_canal=="SiteGamer") {
				$where_canal_1a = " and (FALSE) ";
				$where_canal_1b = " and vg_ultimo_status_obs not like '%AtimoPay%' and vg_http_referer_origem <> 'ATIMO' ";
				$where_canal_2 = " and (FALSE) ";
			}
			if($dd_canal=="ATIMO") {
				$where_canal_1a = " and (FALSE) ";
				$where_canal_1b = " and true and vg_ultimo_status_obs like '%AtimoPay%' and vg_http_referer_origem = 'ATIMO' ";
				$where_canal_2 = " and (FALSE) ";
			}
			if($dd_canal=="POS") {
				$where_canal_1a = " and (FALSE) ";
				$where_canal_1b = " and (FALSE) ";
				$where_canal_2 = " ";
			}
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

/*
	Retirar POS
				union all

				select date(ve_data_inclusao)::date as trn_data, (select opr_nome from operadoras o where o.opr_codigo=ve.ve_opr_codigo) as opr_nome, ve_valor as pin_valor, count(*) as quantidade, sum(ve_valor) as total_face, 'POS' as canal 
				from dist_vendas_pos ve 
				where 1=1 ".$where_data_2."".$where_valor_2." ".$where_opr_2." ".$where_canal_2."
				group by date(ve_data_inclusao)::date, ve_opr_codigo, ve_valor 

*/
		$estat  = "select trn_data, opr_nome, vgm_valor, quantidade, total_face, canal, vg_id, vgm_id, pins_valor_total ";
		if ($dd_vendas_pins) {
			$estat .= ", pin_codinterno, pin_valor ";
		}
		$estat .= "	from (

				select vg.vg_data_inclusao as trn_data, t2.opr_nome, t2.opr_codigo, vgm.vgm_valor as vgm_valor, vgm.vgm_qtde as quantidade, 
					vgm.vgm_valor*vgm.vgm_qtde as total_face, 'SiteLH' as canal, vg_id, vgm_id 
					, (select sum(t0.pin_valor) as soma from 
						pins t0, tb_dist_venda_games_modelo_pins vgmp 
						where (t0.pin_codinterno = vgmp.vgmp_pin_codinterno) and (vgmp.vgmp_vgm_id = vgm.vgm_id) 
					) as pins_valor_total	
					";
		if ($dd_vendas_pins) {
			$estat .= ", t0.pin_codinterno, t0.pin_valor ";
		}
		$estat .= "from tb_dist_venda_games vg 
					inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id , 
					operadoras t2 ";
				
		if ($dd_vendas_pins) {
			$estat .= ", tb_dist_venda_games_modelo_pins vgmp, pins t0  ";
		}				
		$estat .= "where vgm.vgm_opr_codigo=t2.opr_codigo ".$where_valor_1." ".$where_opr_1." ";

		if ($dd_vendas_pins) {
			$estat .= "	and (t0.pin_codinterno = vgmp.vgmp_pin_codinterno) 
					and (vgmp.vgmp_vgm_id = vgm.vgm_id) 		";
		}
		$estat .= "	and vg.vg_data_inclusao>='2008-01-01 00:00:00' 
					and vg.vg_ultimo_status='5' 
						".$where_data_1a." ".$where_canal_1a." 

				union all
				
				select $where_mode_data as trn_data, t2.opr_nome, t2.opr_codigo, vgm.vgm_valor as vgm_valor, vgm.vgm_qtde as quantidade, 
					vgm.vgm_valor*vgm.vgm_qtde as total_face, (case when (vg.vg_ultimo_status_obs like '%AtimoPay%') then 'ATIMO' else 'SiteGamer' end) as canal, vg_id, vgm_id
					, (select sum(t0.pin_valor) as soma from 
						pins t0, tb_venda_games_modelo_pins vgmp 
						where (t0.pin_codinterno = vgmp.vgmp_pin_codinterno) and (vgmp.vgmp_vgm_id = vgm.vgm_id) 
					) as pins_valor_total
					
				
					";

		if ($dd_vendas_pins) {
			$estat .= ", t0.pin_codinterno, t0.pin_valor ";
		}
		$estat .= "from tb_venda_games vg 
						inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id , 
						operadoras t2 ";
		if ($dd_vendas_pins) {
			$estat .= ", tb_venda_games_modelo_pins vgmp, pins t0 ";
		}
		$estat .= "where vgm.vgm_opr_codigo=t2.opr_codigo ".$where_valor_1." ".$where_opr_1." ";

		if ($dd_vendas_pins) {
			$estat .= "	and (t0.pin_codinterno = vgmp.vgmp_pin_codinterno) 
					and (vgmp.vgmp_vgm_id = vgm.vgm_id) ";
		}
		$estat .= "	and $where_mode_data>='2008-01-01 00:00:00' and vg.vg_ultimo_status='5' 
					".$where_data_1b." ".$where_canal_1b."
			) v ";

		if($dd_vendas_pins_diff) {
			$estat .= " where not (total_face = pins_valor_total) "; 
		}

//   "(case when ve_jogo='OG' then 'ONGAME' when ve_jogo='HB' then 'HABBO HOTEL' when ve_jogo='MU' then 'MU ONLINE' else '???' end)"

//	Para listar apenas registros da Rede Prepag
//				and ve_cod_rede=9999 
		
//--The End

		$estat .= " order by trn_data "; 

		$res_count = pg_query($estat);
		$total_table = pg_num_rows($res_count);
	

/*
		$estat .= " order by ".$ncamp; 
*/
		if($ordem == 0)
                    $estat .= " desc ";
		else
                    $estat .= " asc ";
		
		$estat .= ", vgm_id";	// "opr_codigo, "
		$qtde_geral = 0;
		$valor_geral = 0;

if(b_IsUsuarioWagner()) {
//echo "(R) ".str_replace("\n", "\n<br>", $estat)."<br>";
}

		$vg_id_prev = -1;
		$vgm_valor_venda_prev = -1;
		$res_geral = pg_exec($connid, $estat);
		while($pg_geral = pg_fetch_array($res_geral))
		{
			if (($pg_geral['vg_id']!=$vg_id_prev) || ($pg_geral['vgm_valor']!=$vgm_valor_venda_prev)) {
				$qtde_geral += $pg_geral['quantidade'];
				$valor_geral += $pg_geral['total_face'];
			}
			$vgm_valor_venda_prev = $pg_geral['vgm_valor'];
			$vg_id_prev = $pg_geral['vg_id'];

		}
		$vgm_valor_venda_prev = -1;
		$vg_id_prev = -1;
                
                if(!isset($_GET["downloadCsv"])){
                    $estat .= " limit ".$max; 
                    $estat .= " offset ".$inicial;
                }
		
	}
	else {
		$estat = "select est_codigo from estabelecimentos where est_codigo = 0";
	}
		
//	trace_sql($estat, "Arial", 2, "#666666", 'b');
$sql_transform=$estat;
	
	$resestat = pg_exec($connid, $estat);

	if($max + $inicial > $total_table)
		$reg_ate = $total_table;
	else
		$reg_ate = $max + $inicial;
		
	$varsel  = ""; //"&cb_opr_teste=$cb_opr_teste&cb_estab_teste=$cb_estab_teste";
	$varsel .= "&tf_data_final=$tf_data_final&tf_data_inicial=$tf_data_inicial";
	$varsel .= "&dd_operadora=$dd_operadora&dd_valor=$dd_valor&dd_canal=$dd_canal&dd_mode=$dd_mode";
	$varsel .= "&dd_vendas_pins=$dd_vendas_pins&dd_vendas_pins_diff=$dd_vendas_pins_diff";

// Dummy 
//$tf_data_final   = '2010-05-31';
//$tf_data_inicial = '2010-05-31';

?>
<html>
<head>
<link href="/sys/css/css.css" rel="stylesheet" type="text/css">
<title>E-Prepag</title>
<script language="JavaScript" type="text/JavaScript">
<!--
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}
//-->
</script>
</head>
<body>
<!-- INICIO CODIGO NOVO -->
<link href="/includes/bootstrap/css/bootstrap.min_new.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/js/jquery.js"></script>
<script type="text/javascript" src="/includes/bootstrap/js/bootstrap.min.js"></script>
<div class="container-fluid">
    <div class="container txt-azul-claro bg-branco">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12 ">
                        <strong><?php echo LANG_PINS_PAGE_TITLE_1; ?></strong>
                    </div>
                </div>
                <div class="row txt-cinza">
                    <div class="col-md-6">
                        <span class="pull-left"><strong><?php echo LANG_PINS_SEARCH_1; ?></strong></span>
                    </div>
                    <div class="col-md-6">
                        <span class="pull-right"><a href="/sys/admin/commerce/index.php" class="btn btn-primary"><strong><i><?php echo LANG_BACK; ?></i></strong></a></span>
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

                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_PINS_CHANNEL; ?></span>
                        </div>
                        <div class="col-md-3">
                            <select name="dd_canal" id="dd_canal" class="form-control">
                                <option value="" <?php  if($dd_canal!="SiteLH" && $dd_canal!="SiteGamer" && $dd_canal!="POS") echo "selected" ?>><?php echo LANG_PINS_ALL; ?></option>
								<option value="ATIMO" <?php  if($dd_canal=="ATIMO") echo "selected" ?>>ATIMO</option>
                                <option value="Site" <?php  if($dd_canal=="Site") echo "selected" ?>>Site (SiteLH+SiteGamer)</option>
                                <option value="SiteLH" <?php  if($dd_canal=="SiteLH") echo "selected" ?>>SiteLH</option>
                                <option value="SiteGamer" <?php  if($dd_canal=="SiteGamer") echo "selected" ?>>SiteGamer</option>
                                <option value="POS" <?php  if($dd_canal=="POS") echo "selected" ?>>POS</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_PINS_REPORT_TYPE; ?></span>
                        </div>
                        <div class="col-md-3">
<?php  
                        if($_SESSION["tipo_acesso_pub"]=='PU')
                        {
?>
                            <span style="font-weight: bold"><?php echo LANG_PINS_OUT; ?></span>
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
                        if($_SESSION["tipo_acesso_pub"]=='PU') 
                        {
                            echo $_SESSION["opr_nome"];
?>
                            <input type="hidden" name="dd_operadora" id="dd_operadora" value="<?php echo $dd_operadora?>">
<?php 
                        } else 
                        {
?>

                            <select name="dd_operadora" id="dd_operadora" class="form-control" onChange="document.form1.dd_valor.value=''; return true;">
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
                                <?php if(isset($resval) && !empty($resval)){ ?>
                                <?php  while ($pgval = pg_fetch_array ($resval)) { ?>
                                <option value="<?php  echo $pgval['valor'] ?>" <?php  if($pgval['valor'] == $dd_valor) echo "selected" ?>><?php  echo number_format($pgval['valor'], 2, ',', '.'); ?></option>
                                <?php  } ?>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" name="BtnSearch" value="<?php echo LANG_PINS_SEARCH_2; ?>" class="btn pull-right btn-success"><?php echo LANG_INTEGRATION_SEARCH;?></button>
                        </div>
                    </div>
                    <div class="row txt-cinza ">
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo "Vendas&lt;-&gt;PINs"; ?></span>
                        </div>
                        <div class="col-md-3">
                            <input type="checkbox" id="dd_vendas_pins" class="pull-left" name="dd_vendas_pins"<?php if($dd_vendas_pins) echo " checked"; ?>>
                        </div>
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo (($dd_vendas_pins)?"Apenas com Diff":"&nbsp;"); ?></span>
                        </div>
                        <div class="col-md-3">
<?php 
                        if($dd_vendas_pins) 
                        {
                            echo "<input type='checkbox' id='dd_vendas_pins_diff' name='dd_vendas_pins_diff'".(($dd_vendas_pins_diff)?" checked":"").">\n";
                        }
?>
                        </div>
                    </div>
<?php
                if($data_inic_invalida == true) echo "<br><b>".LANG_PINS_START_DATE."</b>";
                if($data_fim_invalida == true) echo "<br><b>".LANG_PINS_END_DATE."</b>";
                if($data_inicial_menor == true) echo "<br><b>".LANG_PINS_COMP_DATE_START_WITH_END."</b>";
                
                $cabecalho = "'".LANG_PINS_DATE."',";
                
                if($ordem == 1)
                    $ordem = 0;
                else
                    $ordem = 1;
?>
                </form>
                <div class="row txt-cinza espacamento">
                    <div class="col-md-12 bg-cinza-claro">
                        <table id="table" class="table bg-branco txt-preto fontsize-pp">
                            <thead>
                              <tr class="bg-cinza-claro">
                                <th>
                                    <strong><?php echo LANG_PINS_DATE; ?></strong> 
                                </th>
                                <th class='text-center'> 
                                    <strong><?php echo LANG_PINS_CHANNEL; ?></strong>
                                </th>
                                <th class='text-center'>
                                    <strong><?php echo LANG_PINS_OPERATOR; ?></strong>
                                </th>
                                <th class="text-right"> 
                                    <strong><?php echo LANG_PINS_QUANTITY_1; ?></strong>
                                </th>
                                <th class="text-right"> 
                                    <strong><?php echo LANG_PINS_FACE_VALUE; ?></strong>
                                </th>
                                <th class="text-right"> 
                                    <strong><?php echo LANG_PINS_TOTAL_VALUE; ?></strong>
                                </th>
                                <th class="text-right"> 
                                    <strong><?php echo "vg_id"; ?></strong>
                                </th>
                                <th class="text-right"> 
                                    <strong><?php echo "vgm_id"; ?></strong>
                                </th>
                                <th class="text-right"> 
                                    <strong><?php echo "pin_codinterno"; ?></strong>
                                </th>
                                <th class="text-right"> 
                                    <strong><?php echo "pin_valor"; ?></strong>
                                </th>
                                <th class="text-right"> 
                                    <strong>pins_valor_total</strong>
                                </th>
                                <th class="text-right"> 
                                    <strong>&nbsp;</strong>
                                </th>
                              </tr>
                            </thead>
                            <tr>
                                <th colspan="12">
<?php  
                            if($total_table > 0) 
                            {
                                echo LANG_SHOW_DATA; ?> <strong><?php  echo $inicial + 1 ?></strong> 
                                <?php echo LANG_TO; ?> <strong><?php  echo $reg_ate ?></strong> <?php echo LANG_FROM; ?> <strong><?php  echo $total_table ?></strong>
<?php  
                            } 
?>
                                </th>
                            </tr>
                            <tbody>
<?php
                            require_once $raiz_do_projeto."class/util/CSV.class.php";
                                        
                            $cabecalho = LANG_PINS_DATE.";".LANG_PINS_CHANNEL.";".LANG_PINS_OPERATOR.";".LANG_PINS_QUANTITY_1.";".LANG_PINS_FACE_VALUE.";".LANG_PINS_TOTAL_VALUE.";vg_id;vgm_id;pin_codinterno;pin_valor;pins_valor_total";

                            $objCsv = new CSV($cabecalho, md5(uniqid()), $raiz_do_projeto."arquivos_gerados/csv/");
                            $objCsv->setCabecalho();

                            while ($pgrow = pg_fetch_array($resestat)) 
                            {
                                    $valor = true;

                                    if (($pgrow['vg_id']!=$vg_id_prev) || ($pgrow['vgm_valor']!=$vgm_valor_venda_prev)) 
                                    {
                                            $qtde_total_tela += $pgrow['quantidade'];
                                            $valor_total_tela += $pgrow['total_face'];
                                    }
                                    
                                    $lineCsv = array();
                                    $lineCsv[] = substr($pgrow['trn_data'], 0, 19);
                                    $lineCsv[] = $pgrow['canal'];
                                    $lineCsv[] = $pgrow['opr_nome'];
                                    $lineCsv[] = (($pgrow['vg_id']!=$vg_id_prev) || ($pgrow['vgm_valor']!=$vgm_valor_venda_prev)) ? $pgrow['quantidade'] :$qtde_total_tela;
                                    $lineCsv[] = number_format($pgrow['vgm_valor'], 2, ',', '.');
                                    $lineCsv[] = (($pgrow['vg_id']!=$vg_id_prev) || ($pgrow['vgm_valor']!=$vgm_valor_venda_prev))? number_format(($pgrow['total_face']), 2, ',', '.')  : number_format($valor_total_tela, 2, ',', '.');
                                    $lineCsv[] = $pgrow['vg_id']; 
                                    $lineCsv[] = $pgrow['vgm_id'];
                                    $lineCsv[] = (($dd_vendas_pins)?$pgrow['pin_codinterno']:"-");
                                    $lineCsv[] = (($dd_vendas_pins)?number_format($pgrow['pin_valor'], 2, ',', '.'):"-");
                                    $lineCsv[] = ((($pgrow['vg_id']!=$vg_id_prev) || ($pgrow['vgm_valor']!=$vgm_valor_venda_prev)) ? number_format($pgrow['pins_valor_total'], 2, ',', '.'):"");

                                    $objCsv->setLine(implode(";",$lineCsv));
									
									//var_dump($pgrow);
?>
                                <tr class="trListagem"> 
                                  <td><?php echo substr($pgrow['trn_data'], 0, 19); ?></td>
                                  <td class="text-center"><?php echo $pgrow['canal'] ?></td>
                                  <td class="text-center"><?php echo $pgrow['opr_nome'] ?></td>
                                  <td class="text-right"><?php echo ((($pgrow['vg_id']!=$vg_id_prev) || ($pgrow['vgm_valor']!=$vgm_valor_venda_prev)) ? $pgrow['quantidade'] :"")." <span class='txt-vermelho'>(".$qtde_total_tela.")</span>"; ?></td>
                                  <td class="text-right"><?php echo number_format($pgrow['vgm_valor'], 2, ',', '.') ?></div></td>
                                  <td class="text-right"><?php echo ((($pgrow['vg_id']!=$vg_id_prev) || ($pgrow['vgm_valor']!=$vgm_valor_venda_prev))? number_format(($pgrow['total_face']), 2, ',', '.')  :"-")." <span class='txt-verde'>(".number_format($valor_total_tela, 2, ',', '.').")</span>" ; ?></td>
                                  <td class="text-center"><?php if($pgrow['vg_id']!=$vg_id_prev) { ?><a href="https://<?php $_SERVER["SERVER_NAME"] ?>:8080/<?php echo (($pgrow['canal']=="SiteLH")?"pdv":"gamer") ?>/vendas/com_venda_detalhe.php?venda_id=<?php  echo $pgrow['vg_id']; ?>" target="_blank"><?php } ?><?php echo $pgrow['vg_id']; ?><?php if($pgrow['vg_id']!=$vg_id_prev) { ?></a><?php } ?></td>
                                  <td class="text-right"><?php echo $pgrow['vgm_id'] ?></td>
                                  <td class="text-right"><?php echo (($dd_vendas_pins)?$pgrow['pin_codinterno']:"-"); ?></td>
                                  <td class="text-right"><?php echo (($dd_vendas_pins)?number_format($pgrow['pin_valor'], 2, ',', '.'):"-") ?></td>
								  <?php
								    if($pgrow['pins_valor_total'] == null){ // atimo adaptaçao
									    $pgrow['pins_valor_total'] = $pgrow['vgm_valor'];
									}
								  ?>
                                  <td class="text-right"><?php echo ((($pgrow['vg_id']!=$vg_id_prev) || ($pgrow['vgm_valor']!=$vgm_valor_venda_prev)) ? number_format($pgrow['pins_valor_total'], 2, ',', '.'):"") ?></td>
                                  <td class="text-right"><?php echo ((($pgrow['pins_valor_total']-$pgrow['total_face'])!=0)?" <span class='txt-vermelho bg-amarelo'>DIFF</span> ":"&nbsp;"); ?></td>
                                </tr>
<?php 
                                $vgm_valor_venda_prev = $pgrow['vgm_valor'];
                                $vg_id_prev = $pgrow['vg_id'];

                            }
                                        
                            if($reg_ate >= $total_table && !isset($_REQUEST["inicial"]))
                                $csv = $objCsv->export();

                            if (!$valor) {
?>
                                <tr class="bg-cinza-claro"> 
                                    <td colspan="12" class="text-center">
                                        <strong><?php echo LANG_NO_DATA; ?></strong>
                                    </td>
                                </tr>
<?php  
                            } else 
                            { 
?>
                                <tr class="bg-cinza-claro"> 
                                    <td colspan="3"><strong><?php echo LANG_PINS_SUBTOTAL; ?></strong></td>
                                    <td><div align="right"><strong><?php echo number_format($qtde_total_tela, 0, ',', '.') ?></strong></div></td>
                                    <td colspan="2"><div align="right"><strong><?php echo number_format($valor_total_tela, 2, ',', '.') ?></strong></div></td>
                                    <td colspan="6">&nbsp;</td>
                                </tr>
                                <tr class="bg-cinza-claro"> 
                                    <td colspan="3"><strong><?php echo LANG_PINS_TOTAL; ?></strong></td>
                                    <td><div align="right"><strong><?php  echo number_format($qtde_geral, 0, ',', '.') ?></strong></div></td>
                                    <td colspan="2"><div align="right"><strong><?php  echo number_format($valor_geral, 2, ',', '.') ?></strong></div></td>
                                    <td colspan="6">&nbsp;</td>
                                </tr>
<?php 
                                if(isset($csv))
                                {
                                    $csv = "/includes/downloadCsv.php?csv=$csv&dir=bkov";
                                }elseif(isset($_GET["downloadCsv"]))
                                {
                                    require_once $raiz_do_projeto."public_html/includes/downloadCsv.php";
                                }elseif($total_table > 0)
                                {
                                    $csv = "/sys/admin/vendas/vendas_estab/pquery_pins.php?downloadCsv=1".$varsel;
                                }

                                if(isset($csv))
                                { 
?>
                                <tr>
                                    <td colspan="12" class="text-center"><a target="_blank" href="<?php print $csv;?>"><span class="btn downloadCsv btn-info ">Download CSV</span></a></td>
                                </tr>
<?php 
                                } 
                                
                                $time_end = getmicrotime();
                                $time = $time_end - $time_start;
                                
                                paginacao_query($inicial, $total_table, $max, '12', $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel);
?>
                                <tr bgcolor="#E4E4E4"> 
                                    <td colspan="12" bgcolor="#FFFFFF"><strong> 
                                      <?php echo LANG_PINS_LAST_MSG; ?>. </strong></td>
                                </tr>
                                <tr> 
                                    <td height="52" colspan="12" bgcolor="#FFFFFF"><p><?php  echo LANG_POS_SEARCH_MSG." ".number_format($time, 2, '.', '.')." ".LANG_POS_SEARCH_MSG_UNIT ?> 
                                        </p>
                                    </td>
                                </tr>
<?php  
                            } 
?>                                
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<link href="/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="/js/facebook.js"></script>
<script src="/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="/js/global.js"></script>
<script>
$(function(){
    var optDate = new Object();
        optDate.interval = 1;

    setDateInterval('tf_data_inicial','tf_data_final',optDate);
});
</script>

<!-- FIM CODIGO NOVO -->
<?php  
require_once $raiz_do_projeto . "public_html/sys/includes/rodape_sys.php";
?>