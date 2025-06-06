<?php
    if(isset($_GET["downloadCsv"]) && $_GET["downloadCsv"] == 1)
        ob_start();
    
    require_once "../../../../includes/constantes.php";
    require_once $raiz_do_projeto . "public_html/sys/includes/topo_sys.php";
    $pos_pagina = $seg_auxilar;
    $time_start = getmicrotime();

//echo "dd_operadora: ".$dd_operadora."<br>";
//echo "Submit: $Submit<br>";
	if($_SESSION["tipo_acesso_pub"]=='PU') {
		$dd_operadora = $_SESSION["opr_codigo_pub"];
		$Submit = "Buscar";
	}

	//
//echo "op_ch: ".$op_ch.", dir_ch: ".$dir_ch.", id_ch: ".$id_ch."<br>";
	if($op_ch=="ch") {
		if($dir_ch=="d") {
			// Obtem o id_seq
			$sql = "select vc_id_seq from dist_vendas_cartoes_tmp where vc_id=$id_ch";
//echo "sql: $sql<br>";
			$res_id_seq = pg_exec($connid, $sql);
			$pg_id_seq_info = pg_fetch_array($res_id_seq);
			if($pg_id_seq_info) {
				$id_seq_ch = $pg_id_seq_info['vc_id_seq'];

				// Modifica o registro
				$sql = "update dist_vendas_cartoes_tmp set vc_id_seq=0, vc_id_seq_str='Depósito' where vc_id=$id_ch";
//echo "sql: $sql<br>";
				$resseq = pg_exec($connid, $sql);
				// Muda a ordem dos que estão acima na sequencia
				$sql = "update dist_vendas_cartoes_tmp set vc_id_seq=(vc_id_seq-1) where vc_id_seq>$id_seq_ch";
//echo "sql: $sql<br>";
				$resseq = pg_exec($connid, $sql);

				// Informa
				echo "<font color='#0000FF'>".LANG_CARDS_REGISTER_MSG." ".$id_ch." ".LANG_CARDS_REGISTER_MSG."</font><br>";
			} else {
				echo "<font color='#FF0000'>".LANG_CARDS_NOT_FOUND_REGISTER_MSG." ".$id_ch."</font><br>";
			}
		} else if($dir_ch=="s") {
			$sql = "select max(vc_id_seq) as max_id_seq from dist_vendas_cartoes_tmp ";
//echo "sql: $sql<br>";
			$res_id_seq = pg_exec($connid, $sql);
			$pg_id_seq_info = pg_fetch_array($res_id_seq);
			if($pg_id_seq_info) {
				$max_id_seq = $pg_id_seq_info['max_id_seq'];

				// Modifica o registro
				$sql = "update dist_vendas_cartoes_tmp set vc_id_seq=($max_id_seq+1), vc_id_seq_str='' where vc_id=$id_ch";
//echo "sql: $sql<br>";
				$resseq = pg_exec($connid, $sql);

				// Informa
				echo "<font color='#0000FF'>".LANG_CARDS_REGISTER_MSG." ".$id_ch." ".LANG_CARDS_REGISTER_MSG_2." ".($max_id_seq+1).".</font><br>";
			} else {
				echo "<font color='#FF0000'>".LANG_CARDS_NOT_FOUND_REGISTER_MSG_1."</font><br>";
			}
		}
		
	}

	if(!$ncamp) {$ncamp = "vc_id"; $ordem = 0;}	// || $ncamp=="vc_data"

	if(!$tf_data_inicial)  {
		$resdatainicio = pg_exec($connid, "select vc_data from dist_vendas_cartoes_tmp order by vc_data limit 1");
		if($pgdatainicio = pg_fetch_array ($resdatainicio)) {
			$tf_data_inicial = substr($pgdatainicio['vc_data'],8,2)."/".substr($pgdatainicio['vc_data'],5,2)."/".substr($pgdatainicio['vc_data'],0,4);
		} else {
			$tf_data_inicial = date('d/m/Y');
		}
		$today_data = date('d/m/Y');
		$iday = intval(substr($today_data,0,2));
		$imonth = intval(substr($today_data,3,2));
		$iyear = intval(substr($today_data,6,4));

		$tf_data_inicial = date('d/m/Y', mktime(0,0,0,$imonth,$iday-7,$iyear));
	}
	if(!$tf_data_final)    $tf_data_final   = date('d/m/Y');
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
	}

	$data_inicial_limite = data_menos_n(date('d/m/Y'), 120);
	$data_inicial_limite = '01/08/2004';
	$FrmEnviar = 1;
	
//echo "tf_data_inicial: $tf_data_inicial<br>";	
//echo "tf_data_final: $tf_data_final<br>";	
//echo "data_inicial_limite: $data_inicial_limite<br>";	

	$default_add  = nome_arquivo($PHP_SELF);
	$img_proxima  = "https://".$server_url_complete."/images/proxima.gif";
	$img_anterior = "https://".$server_url_complete."/images/anterior.gif";
	$max          = 100; //$qtde_reg_tela;
	$range_qtde   = $qtde_range_tela;

//	$resuf = pg_exec($connid, "select uf from uf order by uf");
//	$resuf_except = pg_exec($connid, "select uf from uf order by uf");


	$rescanal = pg_exec($connid, "select vc_canal as canal, count(*) as n from dist_vendas_cartoes_tmp vc group by vc_canal order by vc_canal asc");

	$resusuario = pg_exec($connid, "select ug_id, (CASE WHEN (ug.ug_tipo_cadastro='PJ') THEN upper(ug.ug_nome_fantasia)  WHEN (ug.ug_tipo_cadastro='PF') THEN upper(ug.ug_nome)  END) as ug_nome_fantasia, (select count(*) from dist_vendas_cartoes_tmp vc where vc.vc_ug_id = ug.ug_id and vc_ativo='1') as n, ug.ug_tipo_cadastro from dist_usuarios_games ug where ug_ativo=1 and (select count(*) from dist_vendas_cartoes_tmp vc where vc.vc_ug_id = ug.ug_id and vc_ativo='1')>0 group by ug_id, ug_nome_fantasia, ug.ug_tipo_cadastro, ug.ug_nome order by ug_nome_fantasia");  // "and ug_usuario_cartao=1 "

/*
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
*/
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

		$where_data = "";
		$where_valor = "";
		$where_opr = "";
		$where_canal = "";
		$where_estabelecimento = "";
		$where_ativo = "";

		if($tf_data_inicial && $tf_data_final) 
		{
			$data_inic = formata_data(trim($tf_data_inicial), 1);
			$data_fim = formata_data(trim($tf_data_final), 1); 
			$where_data = " and ((vc_data >= '".trim($data_inic)." 00:00:00') and (vc_data <= '".trim($data_fim)." 23:59:59')) "; 
		}
		
		if($dd_operadora) {
			if($dd_operadora==13)
				$where_opr = " and ((vc_total_5k+vc_total_10k+vc_total_15k+vc_total_20k)>0) ";
			if($dd_operadora==17)
				$where_opr = " and (vc_total_mu_online>0) ";
		}
		if($dd_operadora=="") $dd_valor = "";
//echo "dd_valor: ".$dd_valor."<br>";

		if($dd_valor) {
			if($dd_operadora==13) {
				if($dd_valor==13)
					$where_valor = " and (vc_total_5k>0) ";
				elseif($dd_valor==25)
					$where_valor = " and (vc_total_10k>0) ";
				elseif($dd_valor==37)
					$where_valor = " and (vc_total_15k>0) ";
				elseif($dd_valor==49)
					$where_valor = " and (vc_total_20k>0) ";
			}
			if($dd_operadora==17)
				if($dd_valor==10)
					$where_valor = " and (vc_total_mu_online>0) ";
		}

		if($dd_canal) {
			$where_canal = " and (vc_canal='$dd_canal') ";
		}

		if($dd_ativo) {
			$where_ativo = " and (vc_ativo='$dd_ativo') ";
		}
		if($dd_estabelecimento) {
			$where_estabelecimento = " and (vc_ug_id=$dd_estabelecimento) ";
		}

		$estat  = "select vc.*, ug.ug_id, (CASE WHEN (ug.ug_tipo_cadastro='PJ') THEN upper(ug.ug_nome_fantasia)  WHEN (ug.ug_tipo_cadastro='PF') THEN upper(ug.ug_nome)  END) as ug_nome_fantasia, ug.ug_tipo_cadastro, (age(now(), date_trunc('day', vc_data)) <='1 day') as bedit from dist_vendas_cartoes_tmp vc  left join dist_usuarios_games ug on vc.vc_ug_id = ug.ug_id ";
		if($where_data||$where_valor||$where_opr||$where_canal)
			$estat  .= " where 1=1 ".$where_data." ".$where_valor." ".$where_opr." ".$where_canal." ".$where_estabelecimento." ".$where_ativo." ";		
	
		$res_count = pg_query($estat);
		$total_table = pg_num_rows($res_count);
/*	
		if($ncamp=="vc_data") {
			$estat .= " order by vc_data "; //" order by vc_data::date "; 
		} else {
			$estat .= " order by ".$ncamp; 
		}
*/
/*		if($ordem == 0) { $sordem = " desc "; $img_seta = "../../images/seta_down.gif";	 }
		else { $sordem = " asc "; $img_seta = "../../images/seta_up.gif"; }

		$estat .= " order by "; 
		if($ncamp=="vc_id") $estat .= " vc_id $sordem, vc_id_seq $sordem, vc_data $sordem "; 
		else				$estat .= " vc_data $sordem, vc_id_seq $sordem , vc_id $sordem"; 
*/
		$estat .= " order by vc_data::date desc, vc_id_seq desc, vc_id desc"; 

		$qtde_geral_5k  = 0;
		$qtde_geral_10k = 0;
		$qtde_geral_15k = 0;
		$qtde_geral_20k = 0;
		$qtde_geral_mu_online = 0;
		$qtde_geral_mu_ganha = 0;
		$qtde_geral = 0;
		$valor_geral = 0;
		$valor_geral_sem_comiss_frete_tela = 0;

//echo "Geral: ".$estat."<br>";//"$ncamp<br>";
		$res_geral = pg_exec($connid, $estat);
		while($pg_geral = pg_fetch_array($res_geral))
		{
			$qtde_ongame = 0;
			$vendas_ongame = 0;
			$qtde_mu = 0;
			$vendas_mu = 0;

			if(($dd_operadora==13) || ($dd_operadora=="")) {
				$qtde_geral_5k  += $pg_geral['vc_total_5k'];
				$qtde_geral_10k += $pg_geral['vc_total_10k'];
				$qtde_geral_15k += $pg_geral['vc_total_15k'];
				$qtde_geral_20k += $pg_geral['vc_total_20k'];

				$qtde_ongame = $pg_geral['vc_total_5k']+$pg_geral['vc_total_10k']+$pg_geral['vc_total_15k']+$pg_geral['vc_total_20k'];
				$vendas_ongame = $pg_geral['vc_total_5k']*13+$pg_geral['vc_total_10k']*25+$pg_geral['vc_total_15k']*37+$pg_geral['vc_total_20k']*49;
			}

			if(($dd_operadora==17) || ($dd_operadora=="")) {
				$qtde_geral_mu_online += $pg_geral['vc_total_mu_online'];
				$qtde_geral_mu_ganha +=  $pg_geral['vc_qtde_ganha'];

				$qtde_mu = $pg_geral['vc_total_mu_online'];
				$vendas_mu = $pg_geral['vc_total_mu_online']*10;
			}

			$qtde_geral += $qtde_ongame + $qtde_mu;
			$valor_geral += $vendas_ongame + $vendas_mu;

			$valor_ongame_comissao = ($pg_geral['vc_total_5k']*13 + $pg_geral['vc_total_10k']*25 + $pg_geral['vc_total_15k']*37 + $pg_geral['vc_total_20k']*49)*(100-$pg_geral['vc_comissao'])/100;
			$valor_mu_comissao = ($pg_geral['vc_total_mu_online']*10)*(100-$pg_geral['vc_comissao'])/100;
			$valor_geral_comissao = $valor_ongame_comissao + $valor_mu_comissao;
			$valor_geral_sem_comiss_frete_tela += $valor_geral_comissao;

//echo "$qtde_ongame, $qtde_mu (".$pg_geral['vc_total_5k'].") -> $qtde_geral<br>";
		}

                if(!isset($_GET["downloadCsv"])){
                    $estat .= " limit ".$max; 
                    $estat .= " offset ".$inicial;
                }

	}
	else
		$estat = "select est_codigo from estabelecimentos where est_codigo = 0";
		
//	trace_sql($estat, "Arial", 2, "#666666", 'b');
$sql_transform=$estat;

//echo "Subtotal: $estat<br>";

	$resestat = pg_exec($connid, $estat);

	if($max + $inicial > $total_table)
		$reg_ate = $total_table;
	else
		$reg_ate = $max + $inicial;

	$varsel  = "&dd_operadora=$dd_operadora&tf_data_inicial=$tf_data_inicial&tf_data_final=$tf_data_final&dd_valor=$dd_valor";
	$varsel .= "&dd_canal=$dd_canal&dd_estabelecimento=$dd_estabelecimento";
		
?>
<html>
<head>

<link href="/sys/css/css.css" rel="stylesheet" type="text/css">
<title>E-Prepag</title>
<script language='javascript' src='/js/<?php echo LANG_NAME_CALENDAR_FILE; ?>'></script>
<script language="JavaScript">
<!--
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}

function envia_lista(id) { 
  document.formlista.op.value = "lst";
  document.formlista.id.value = id;
  document.formlista.action = "cartao_detalhe_insere.php";
//alert("op: "+document.formlista.op.value+", id:"+document.formlista.id.value+", action: "+document.formlista.action+"");
  document.formlista.submit();
}

function envia_novo() { 
  document.formlista.op.value = "new";
  document.formlista.action = "cartao_detalhe_insere.php";
//alert("op: "+document.formlista.op.value+", id:"+document.formlista.id.value+", action: "+document.formlista.action+"");
  document.formlista.submit();
}

function confirma_edit(opt) { 
	if(opt=="d") {
		answer = window.confirm("Você quer mesmo trocar o registro de Sequencial para Depósito?");
	} else {
		answer = window.confirm("Você quer mesmo trocar o registro de Depósito para Sequencial?");
	}
	return answer;	// true if clicked OK
}

function Myf5() {
	var e = window.event;
	var myKeyCode = e.keyCode;

//alert("e.keyCode: "+myKeyCode+"\ne.altKey: "+e.altKey+"\ne.ctrlKey: "+e.ctrlKey+"\ne.shiftKey: "+e.shiftKey);
	// Enter(13), Shift(16), Ctrl(17), Alt(18), CapsLock(20) keys?
	if (myKeyCode >= 13 && myKeyCode <= 20)
		return true;
	if((myKeyCode==116)	|| (((myKeyCode==37) || (myKeyCode==39)) && (e.altKey==true)) ){
//alert("e.keyCode: "+myKeyCode+"\ne.altKey: "+e.altKey+"\ne.ctrlKey: "+e.ctrlKey+"\ne.shiftKey: "+e.shiftKey);
		e.returnValue=false;
		e.keyCode=0;
//alert("Myf5");
		return false;
	}
}
//function NoReload() {
//alert("Load NoReload()");
	document.body.onkeydown=Myf5;
//}

//-->
</script>
</head>
<body bgcolor="#FFFFFF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
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
                        <strong><?php echo LANG_CARDS_PAGE_TITLE; ?></strong>
                    </div>
                </div>
                <div class="row txt-cinza">
                    <div class="col-md-12 espacamento">
                            <span class="pull-left"><input type="button" name="BtnInsert" value="<?php echo LANG_CARDS_INSERT_NEW; ?>" class="btn pull-right btn-success" onClick="envia_novo();"></span>
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
                <form name="formlista" method="post" action="">
                    <input type="hidden" name="op" id="op" value="">
                    <input type="hidden" name="id" id="id" value="">
                    <input type="hidden" name="ncamp" id="ncamp" value="<?php echo $ncamp?>">
                    <div class="row txt-cinza ">
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_CARDS_SALES_START_DATE; ?></span>
                        </div>
                        <div class="col-md-3">
                            <input  alt="Calendário" name="tf_data_inicial" type="text" class="form-control w-ipt-medium pull-left data" id="tf_data_inicial" value="<?php  echo $tf_data_inicial ?>" size="9" maxlength="10">
                        </div>
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_CARDS_SALES_END_DATE; ?></span>
                        </div>
                        <div class="col-md-3">
                            <input alt="Calendário" name="tf_data_final" type="text" class="form-control w-ipt-medium pull-left data" id="tf_data_final" value="<?php  echo $tf_data_final ?>" size="9" maxlength="10">
                        </div>

                    </div>
                    <div class="row txt-cinza top10">
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_CARDS_ESTABLISHMENT; ?></span>
                        </div>
                        <div class="col-md-3">
                            <select name="dd_estabelecimento" id="dd_estabelecimento" class="form-control" onChange="document.formlista.submit()">
                                <option value=""><?php echo LANG_CARDS_ALL_USERS; ?></option>
                                <?php while ($pgusuario = pg_fetch_array ($resusuario)) { ?>
                                <option value="<?php echo $pgusuario['ug_id'] ?>" <?php if($pgusuario['ug_id'] == $dd_estabelecimento) echo "selected" ?>><?php echo substr($pgusuario['ug_nome_fantasia'],0,20) ?><?php echo ((strlen($pgusuario['ug_nome_fantasia'])>20)?"...":"")?> (ID: <?php echo $pgusuario['ug_id']?>)<?php if($pgusuario['n']>0) echo " (".$pgusuario['ug_tipo_cadastro'].") - ".$pgusuario['n']." regs";?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_PINS_CHANNEL; ?></span>
                        </div>
                        <div class="col-md-3">
                            <select name="dd_canal" id="dd_canal" class="form-control" onChange="document.formlista.submit()">
                                <option value=""><?php echo LANG_CARDS_ALL_CHANNELS; ?></option>
                                <?php while ($pgcanal = pg_fetch_array ($rescanal)) { ?>
                                <option value="<?php echo $pgcanal['canal'] ?>" <?php if($pgcanal['canal'] == $dd_canal) echo "selected" ?>><?php echo $pgcanal['canal'] ?> (<?php echo $pgcanal['n'].' '.LANG_CARDS_SALES; ?>)</option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="row txt-cinza top10">
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_CARDS_OPERATOR; ?></span>
                        </div>
                        <div class="col-md-3">
<?php
                        if($_SESSION["tipo_acesso_pub"]=='PU') 
                        {
?>
                            <?php echo $_SESSION["opr_nome"]?>
                            <input type="hidden" name="dd_operadora" id="dd_operadora" value="<?php echo $dd_operadora?>">
<?php
                        }
                        else 
                        {
?>
                            <select name="dd_operadora" id="dd_operadora" class="form-control" onChange="document.formlista.dd_valor.value='';document.formlista.submit()">
                                <option value=""<?php if(($dd_operadora!=13) && ($dd_operadora!=17)) echo "selected" ?>><?php echo LANG_CARDS_ALL_OPERATORS; ?></option>
                                <option value="13"<?php if($dd_operadora==13) echo "selected" ?>>ONGAME (13)</option>
                                <option value="17"<?php if($dd_operadora==17) echo "selected" ?>>MU ONLINE (17)</option>
                            </select>
<?php
                        } 
?>
                        </div>
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_CARDS_VALUE; ?></span>
                        </div>
                        <div class="col-md-3">
                            <select name="dd_valor" id="dd_valor" class="form-control" onChange="document.formlista.submit()">
<?php 
                            if($dd_operadora==17) 
                            {
?>
                                <option value=""<?php if($dd_valor!=10) echo "selected" ?>><?php echo LANG_CARDS_ALL_VALUES; ?></option>
                                <option value="10"<?php if($dd_valor==10) echo "selected" ?>>R$ 10,00</option>
<?php 
                            }
                            
                            if($dd_operadora==13) 
                            {
?>
                                <option value=""<?php if(($dd_valor!=13) && ($dd_valor!=25) && ($dd_valor!=37) && ($dd_valor!=49)) echo "selected" ?>><?php echo LANG_CARDS_ALL_VALUES; ?></option>
                                <option value="13"<?php if($dd_valor==13) echo "selected" ?>>R$ 13,00</option>
                                <option value="25"<?php if($dd_valor==25) echo "selected" ?>>R$ 25,00</option>
                                <option value="37"<?php if($dd_valor==37) echo "selected" ?>>R$ 37,00</option>
                                <option value="49"<?php if($dd_valor==49) echo "selected" ?>>R$ 49,00</option>
<?php 
                            } 
                            
                            if(($dd_operadora!=13) && ($dd_operadora!=17)) 
                            {
?>
                                <option value=""><?php echo LANG_CARDS_ALL_VALUES; ?></option>
<?php 
                            }
?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" name="BtnSearch" value="Buscar" class="btn pull-right btn-success"><?php echo LANG_CARDS_SEARCH_2; ?></button>
                        </div>
                    </div>
                </form>
                <div class="row txt-cinza espacamento">
<?php
                    if( $data_inic_invalida == true ||
                        $data_fim_invalida  == true ||
                        $data_inicial_menor == true)
                    {
                        if($data_inic_invalida == true) echo "<br><font face='Arial, Helvetica, sans-serif' size='2' color='#FF0000'><b>".LANG_CARDS_START_DATE."</b></font>";
                        if($data_fim_invalida == true) echo "<br><font face='Arial, Helvetica, sans-serif' size='2' color='#FF0000'><b>".LANG_CARDS_END_DATE."</b></font>";
                        if($data_inicial_menor == true) echo "<br><font face='Arial, Helvetica, sans-serif' size='2' color='#FF0000'><b>".LANG_CARDS_START_END_DATE."</b></font>";
                    }
                    
                    if($ordem == 1)
                        $ordem = 0;
                    else
                        $ordem = 1;
                    $ncamp = "";
?>
                    <div class="col-md-12 bg-cinza-claro">
                        <table id="table" class="table bg-branco txt-preto fontsize-p">
                            <thead>
                              <tr class="bg-cinza-claro">
                                <th class="text-center"><strong>ID</strong></th>
                                <th class="text-center">&nbsp;</th>
                                <th class="text-center"><strong>N Tit.</strong></th>
                                <th class="text-center"><strong><?php echo LANG_CARDS_DATE; ?></strong></th>
                                <th class="text-center"><strong><?php echo LANG_CARDS_ESTABLISHMENT ?></strong></th>
                                <th class="text-center"><strong><?php echo LANG_PINS_CHANNEL; ?></strong></th>
                                <th class="text-center"><strong>5K</strong></th>
                                <th class="text-center"><strong>10K</strong></th>
                                <th class="text-center"><strong>15K</strong></th>
                                <th class="text-center"><strong>20K</strong></th>
                                <th class="text-center"><strong>Mu</strong></th>
                                <th class="text-center"><strong>Mu+</strong></th>
                                <th class="text-center"><strong><?php echo LANG_CARDS_QUANTITY; ?></strong></th>
                                <th class="text-center"><strong><?php echo LANG_CARDS_SALES_REAL ?></strong></th>
                                <th class="text-center"><strong><?php echo LANG_CARDS_SALES_REAL ?><br><font size='1'>(<?php echo LANG_CARDS_OUT_COMMISSION_FREIGHT; ?>)</strong></th>
                              </tr>
<?php 
                            if($total_table > 0)
                            {
?>  
                                <tr>
                                    <th colspan="15">
                                    <?php echo LANG_SHOW_DATA.' '; ?><strong><?php echo $inicial + 1 ?></strong> 
                                    <?php echo ' '.LANG_TO.' '; ?><strong><?php echo $reg_ate ?></strong><?php echo ' '.LANG_FROM.' ' ?><strong><?php echo $total_table ?></strong>
                                    </th>
                                </tr>
<?php 
                            } 
?>
                            </thead>
                            

                            <tbody>
<?php
                            $qtde_total_5k_tela  = 0;
                            $qtde_total_10k_tela = 0;
                            $qtde_total_15k_tela = 0;
                            $qtde_total_20k_tela = 0;
                            $qtde_total_mu_online_tela = 0;
                            $qtde_total_mu_ganha_tela = 0;

                            $valor_total_tela = 0;
                            $qtde_total_tela = 0;

                            $qtde_total_5k_tela = 0;
                            $qtde_total_10k_tela = 0;
                            $qtde_total_15k_tela = 0;
                            $qtde_total_20k_tela = 0;
                            $qtde_total_mu_online_tela = 0;
                            $qtde_total_mu_ganha_tela = 0;

                            $valor_total_sem_comiss_frete_tela = 0;


                            require_once $raiz_do_projeto."class/util/CSV.class.php";

                            $cabecalho = "ID;N Tit.;".LANG_CARDS_DATE.";".LANG_CARDS_ESTABLISHMENT.";".LANG_PINS_CHANNEL.";5k;10k;20K;Mu;Mu+;".LANG_CARDS_QUANTITY.";".LANG_CARDS_SALES_REAL.";".LANG_CARDS_SALES_REAL.";".LANG_CARDS_SALES_REAL."(".LANG_CARDS_OUT_COMMISSION_FREIGHT.")";

                            $objCsv = new CSV($cabecalho, md5(uniqid()), $raiz_do_projeto."arquivos_gerados/csv/");
                            $objCsv->setCabecalho();

                            while ($pgrow = pg_fetch_array($resestat))
                            {
                                $valor = true;

                                $qtde_5k  = 0;
                                $qtde_10k = 0;
                                $qtde_15k = 0;
                                $qtde_20k = 0;
                                $qtde_mu_online = 0;
                                $qtde_mu_ganha = 0;

                                $qtde_ongame = 0;
                                $qtde_mu = 0;

                                $vendas_ongame = 0;
                                $vendas_mu = 0;

                                if(($dd_operadora==13) || ($dd_operadora=="")) {
                                        $qtde_5k  = $pgrow['vc_total_5k'];
                                        $qtde_10k = $pgrow['vc_total_10k'];
                                        $qtde_15k = $pgrow['vc_total_15k'];
                                        $qtde_20k = $pgrow['vc_total_20k'];

                                        $qtde_ongame = $pgrow['vc_total_5k']+$pgrow['vc_total_10k']+$pgrow['vc_total_15k']+$pgrow['vc_total_20k'];
                                        $vendas_ongame = $pgrow['vc_total_5k']*13+$pgrow['vc_total_10k']*25+$pgrow['vc_total_15k']*37+$pgrow['vc_total_20k']*49;
                                }
                                if(($dd_operadora==17) || ($dd_operadora=="")) {
                                        $qtde_mu_online = $pgrow['vc_total_mu_online'];
                                        $qtde_mu_ganha = $pgrow['vc_qtde_ganha'];

                                        $qtde_mu = $pgrow['vc_total_mu_online'];
                                        $vendas_mu = $pgrow['vc_total_mu_online']*10;
                                }

                                $valor_total_tela += $vendas_ongame + $vendas_mu;
                                $qtde_total_tela += $qtde_ongame + $qtde_mu;

                                $qtde_total_5k_tela += $qtde_5k;
                                $qtde_total_10k_tela += $qtde_10k;
                                $qtde_total_15k_tela += $qtde_15k;
                                $qtde_total_20k_tela += $qtde_20k;
                                $qtde_total_mu_online_tela += $qtde_mu_online;
                                $qtde_total_mu_ganha_tela += $qtde_mu_ganha;

                                $valor_ongame_comissao = ($pgrow['vc_total_5k']*13 + $pgrow['vc_total_10k']*25 + $pgrow['vc_total_15k']*37 + $pgrow['vc_total_20k']*49)*(100-$pgrow['vc_comissao'])/100;
                                $valor_mu_comissao = ($pgrow['vc_total_mu_online']*10)*(100-$pgrow['vc_comissao'])/100;
                                $valor_total_comissao = $valor_ongame_comissao + $valor_mu_comissao;
                                $valor_total_sem_comiss_frete_tela += $valor_total_comissao;

                                $lineCsv = array();
                                $lineCsv[] = $pgrow['vc_id'];

                                if(($dd_operadora==13) || ($dd_operadora=="")) {
                                    if($pgrow['vc_id_seq']!="0") 
                                        $lineCsv[] = $pgrow['vc_id_seq']; 
                                    else {
                                        if(strlen($pgrow['vc_id_seq_str'])>0) 
                                            $lineCsv[] = $pgrow['vc_id_seq_str'];
                                        else 
                                            $lineCsv[] = "(vazio)";

                                    }

                                } else 
                                    $lineCsv[] = "-";
                                $lineCsv[] = formata_data($pgrow['vc_data'], 0);
                                $lineCsv[] = (strlen($pgrow['ug_nome_fantasia'])>0)?substr($pgrow['ug_nome_fantasia'],0,25)." (".$pgrow['ug_tipo_cadastro'].") (ID: ".$pgrow['ug_id'].")":"--";
                                $lineCsv[] = $pgrow['vc_canal'];
                                $lineCsv[] = (($dd_operadora==13) || ($dd_operadora=="")) ? $pgrow['vc_total_5k'] : "-";
                                $lineCsv[] = (($dd_operadora==13) || ($dd_operadora=="")) ? $pgrow['vc_total_10k'] : "-";
                                $lineCsv[] = (($dd_operadora==13) || ($dd_operadora=="")) ? $pgrow['vc_total_15k'] : "-";
                                $lineCsv[] = (($dd_operadora==13) || ($dd_operadora=="")) ? $pgrow['vc_total_20k'] : "-";
                                $lineCsv[] = (($dd_operadora==13) || ($dd_operadora=="")) ? $pgrow['vc_total_mu_online'] : "-";
                                $lineCsv[] = (($dd_operadora==13) || ($dd_operadora=="")) ? $pgrow['vc_qtde_ganha'] : "-";
                                $lineCsv[] = $qtde_ongame + $qtde_mu + $qtde_mu_ganha;
                                $lineCsv[] = number_format(($vendas_ongame + $vendas_mu), 2, ',', '.');
                                $lineCsv[] = number_format($valor_total_comissao+$pgrow['vc_frete'], 2, ',', '.');

                                if(is_array($lineCsv)) $objCsv->setLine(implode(";",$lineCsv));
?>                                
                                <tr class="trListagem"> 
                                    <td class="text-center"><a href="#" onClick="envia_lista(<?php echo $pgrow['vc_id']?>);"><?php echo $pgrow['vc_id'] ?></a></td>
                                    <td class="text-center"><?php if($pgrow['bedit']=='t') { ?><a href="cartao_lista.php?op_ch=ch&dir_ch=<?php echo (($pgrow['vc_id_seq']=="0")?"s":"d"); ?>&id_ch=<?php echo $pgrow['vc_id']; ?>&ncamp=<?php echo $ncamp; ?>&inicial=<?php echo $inicial.$varsel; ?>" onClick="return confirma_edit('<?php echo (($pgrow['vc_id_seq']=="0")?"s":"d"); ?>');"><img src="../imgs/p_change_<?php echo (($pgrow['vc_id_seq']=="0")?"s":"d"); ?>.gif" width="20" height="14" border="0" title="<?php echo (($pgrow['vc_id_seq']=="0")?"Depósito -> Sequencial":"Sequencial -> Depósito"); ?>"></a><?php } else { ?>&nbsp;<?php } ?></td>
                                    <td class="text-center"><?php if(($dd_operadora==13) || ($dd_operadora=="")) {if($pgrow['vc_id_seq']!="0") echo $pgrow['vc_id_seq']; else {if(strlen($pgrow['vc_id_seq_str'])>0) echo "<b>".$pgrow['vc_id_seq_str']."</b>"; else echo "(vazio)";}} else echo "-"; ?></td>
                                    <td class="text-center"><?php echo formata_data($pgrow['vc_data'], 0) ?></td>
                                    <td><?php echo (strlen($pgrow['ug_nome_fantasia'])>0)?substr($pgrow['ug_nome_fantasia'],0,25)." (".$pgrow['ug_tipo_cadastro'].") (ID: ".$pgrow['ug_id'].")":"--"; ?></td>
                                    <td class="text-center"><?php echo $pgrow['vc_canal'] ?></td>
                                    <td class="text-center"><?php if(($dd_operadora==13) || ($dd_operadora=="")) echo $pgrow['vc_total_5k']; else echo "-"; ?></td>
                                    <td class="text-center"><?php if(($dd_operadora==13) || ($dd_operadora=="")) echo $pgrow['vc_total_10k']; else echo "-"; ?></td>
                                    <td class="text-center"><?php if(($dd_operadora==13) || ($dd_operadora=="")) echo $pgrow['vc_total_15k']; else echo "-"; ?></td>
                                    <td class="text-center"><?php if(($dd_operadora==13) || ($dd_operadora=="")) echo $pgrow['vc_total_20k']; else echo "-"; ?></td>
                                    <td class="text-center"><?php if(($dd_operadora==17) || ($dd_operadora=="")) echo $pgrow['vc_total_mu_online']; else echo "-"; ?></td>
                                    <td class="text-center"><?php if(($dd_operadora==17) || ($dd_operadora=="")) echo $pgrow['vc_qtde_ganha']; else echo "-"; ?></td>
                                    <td class="text-center"><?php echo $qtde_ongame + $qtde_mu + $qtde_mu_ganha ?></td>
                                    <td class="text-center"><?php echo number_format(($vendas_ongame + $vendas_mu), 2, ',', '.') ?></td>
                                    <td class="text-center"><?php echo number_format($valor_total_comissao+$pgrow['vc_frete'], 2, ',', '.') ?></td>
                                </tr>
<?php
                            }
                            
                            if($reg_ate >= $total_table && !isset($_REQUEST["inicial"]))
                                $csv = $objCsv->export();
                            
                            if (!$valor) 
                            {
?>
                                <tr> 
                                    <td colspan="15">
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
                                    <td colspan="6"><strong><?php echo LANG_PINS_SUBTOTAL; ?></strong></td>
                                    <td class="text-center"><strong><?php echo number_format($qtde_total_5k_tela, 0, ',', '.') ?></strong></td>
                                    <td class="text-center"><strong><?php echo number_format($qtde_total_10k_tela, 0, ',', '.') ?></strong></td>
                                    <td class="text-center"><strong><?php echo number_format($qtde_total_15k_tela, 0, ',', '.') ?></strong></td>
                                    <td class="text-center"><strong><?php echo number_format($qtde_total_20k_tela, 0, ',', '.') ?></strong></td>
                                    <td class="text-center"><strong><?php echo number_format($qtde_total_mu_online_tela, 0, ',', '.') ?></strong></td>
                                    <td class="text-center"><strong><?php echo number_format($qtde_total_mu_ganha_tela, 0, ',', '.') ?></strong></td>
                                    <td class="text-center"><strong><?php echo number_format($qtde_total_tela, 0, ',', '.') ?></strong></td>
                                    <td class="text-center"><strong><?php echo number_format($valor_total_tela, 2, ',', '.') ?></strong></td>
                                    <td class="text-center"><strong><?php echo number_format($valor_total_sem_comiss_frete_tela, 2, ',', '.') ?></strong></td>
                                </tr>
                                <tr class="bg-cinza-claro">
                                    <td colspan="6"><strong><?php echo LANG_ALL; ?></strong></td>
                                    <td class=text-center><strong><?php echo number_format($qtde_geral_5k, 0, ',', '.') ?></strong></td>
                                    <td class=text-center><strong><?php echo number_format($qtde_geral_10k, 0, ',', '.') ?></strong></td>
                                    <td class=text-center><strong><?php echo number_format($qtde_geral_15k, 0, ',', '.') ?></strong></td>
                                    <td class=text-center><strong><?php echo number_format($qtde_geral_20k, 0, ',', '.') ?></strong></td>
                                    <td class=text-center><strong><?php echo number_format($qtde_geral_mu_online, 0, ',', '.') ?></strong></td>
                                    <td class=text-center><strong><?php echo number_format($qtde_geral_mu_ganha, 0, ',', '.') ?></strong></td>
                                    <td class=text-center><strong><?php echo number_format($qtde_geral, 0, ',', '.') ?></strong></td>
                                    <td class=text-center><strong><?php echo number_format($valor_geral, 2, ',', '.') ?></strong></td>
                                    <td class=text-center><strong><?php echo number_format($valor_geral_sem_comiss_frete_tela, 2, ',', '.') ?></strong></td>
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
                                    $csv = "/sys/admin/cartoes/cartao_lista.php?downloadCsv=1&".$varsel;//http_build_query($_POST);
                                }

                                if(isset($csv))
                                { 
?>
                                 <tr>
                                     <td colspan="15" class="text-center"><a href="<?php print $csv;?>"><input class="btn downloadCsv btn-info" type="button" value="Download CSV"></a></td>
                                 </tr>
<?php 
                                }
?>
                                <tr>
                                    <td colspan="15"><?php echo $search_msg . number_format($time, 2, '.', '.') . $search_unit ?></td>
                                </tr>
                                 
<?php

                                paginacao_query($inicial, $total_table, $max, '11', $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel);
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
</body>
</html>
<?php
require_once $raiz_do_projeto . "public_html/sys/includes/rodape_sys.php";
?>
