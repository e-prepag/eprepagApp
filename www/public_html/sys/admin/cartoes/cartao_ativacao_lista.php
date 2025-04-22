<?php 
require_once "../../../../includes/constantes.php";
require_once $raiz_do_projeto . "public_html/sys/includes/topo_sys.php";
include_once $raiz_do_projeto . "class/phpmailer/class.phpmailer.php";

if(!b_IsSysAdminONGAME() || !b_is_Administrator()){
    header("Location: /sys/admin/commerce/index.php");
    die;
}

	$pos_pagina = $seg_auxilar;
	//<div id='msgshow_outer' style='background-color:#FFFF66'><div id='msgshow'><_?php echo $msgbody ?_></div></div>
	$time_start = getmicrotime();

	$order   = array("\r\n", "\n", "\r");//, "\t");
	$replace = ""; //'<br />';

//echo "dd_operadora: ".$dd_operadora."<br>";
//echo "Submit: $Submit<br>";
	if($_SESSION["tipo_acesso_pub"]=='PU') {
		$dd_operadora = $_SESSION["opr_codigo_pub"];
		$Submit = "Buscar";
	}

	$tf_destinatarios = $dd_destinatarios;
	if(strlen($tf_destinatarios)==0) $tf_destinatarios = "";
	$tf_copia_oculta = $dd_copia_oculta;
	if($op=="env") {
		$msg = "";
//echo "<hr>Len: ".strlen($msgbody)."<hr><span style='background-color:#CCFF99'>$msgbody</span><hr>";
		//	function enviaEmail($to, $cc, $bcc, $subject, $msgEmail) 

		$ret = enviaEmail($tf_destinatarios, null, $tf_copia_oculta, "E-Prepag - Relatório de Ativação Ongame - ".date("Y-m-d H:i:s"), $msgbody);
		if($ret === false) $msg = "Não foi possível enviar seu email para ".$tf_destinatarios." e ".$tf_copia_oculta . "\n<br>";
		else $msg = "Email enviado com sucesso!";

//echo "<hr>$msgbody<hr>";

	}

	if(!$ncamp) $ncamp = 'vc_data';

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

		$tf_data_inicial = date('d/m/Y', mktime(0,0,0,$imonth,$iday,$iyear));
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
	$img_proxima  = "/sys/imagens/proxima.gif";
	$img_anterior = "/sys/imagens/anterior.gif";
	$max          = 100; //$qtde_reg_tela;
	$range_qtde   = $qtde_range_tela;

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
	
	if(qtde_dias($tf_data_inicial, $tf_data_inicial) < 0)
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
		
		if($dd_operadora=="") $dd_operadora = 13;
//echo "dd_operadora: ".$dd_operadora."<br>";
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

		$estat  = "select vc.*, ug.ug_id, (CASE WHEN (ug.ug_tipo_cadastro='PJ') THEN upper(ug.ug_razao_social) WHEN (ug.ug_tipo_cadastro='PF') THEN upper(ug.ug_nome) || ' (PF)' END) as ug_razao_social, (CASE WHEN (ug.ug_tipo_cadastro='PJ') THEN upper(ug.ug_nome_fantasia)  WHEN (ug.ug_tipo_cadastro='PF') THEN upper(ug.ug_nome)  END) as ug_nome_fantasia, ug.ug_tipo_cadastro from dist_vendas_cartoes_tmp vc  left join dist_usuarios_games ug on vc.vc_ug_id = ug.ug_id ";
		if($where_data||$where_valor||$where_opr||$where_canal)
			$estat  .= " where 1=1 ".$where_data." ".$where_valor." ".$where_opr." ".$where_canal." ".$where_estabelecimento." ".$where_ativo." ";		
	
		$res_count = pg_query($estat);
		$total_table = pg_num_rows($res_count);

//echo "total_table: $total_table<br>";	

		if($ncamp=="vc_data") {
			$estat .= " order by vc_data::date "; 
		} else {
			$estat .= " order by ".$ncamp; 
		}

		if($ordem == 0)
		{
			$estat .= " desc ";
			if($ncamp!="vc_id") $estat .= ", vc_id_seq desc, vc_id "; else $estat .= ", vc_id_seq desc "; 
			$img_seta = "/sys/imagens/seta_down.gif";	
		}
		else
		{
			$estat .= " asc ";
			if($ncamp!="vc_id") $estat .= ", vc_id_seq desc, vc_id "; else $estat .= ", vc_id_seq desc "; 
			$img_seta = "/sys/imagens/seta_up.gif";
		}

//$estat .= " limit 36 ";
		$qtde_geral_5k  = 0;
		$qtde_geral_10k = 0;
		$qtde_geral_15k = 0;
		$qtde_geral_20k = 0;
		$qtde_geral = 0;
		$valor_geral = 0;


//echo $estat."<br>";
		$res_geral = pg_exec($connid, $estat);
		while($pg_geral = pg_fetch_array($res_geral))
		{
			$qtde_ongame = $pg_geral['vc_total_5k']+$pg_geral['vc_total_10k']+$pg_geral['vc_total_15k']+$pg_geral['vc_total_20k'];
			$vendas_ongame = $pg_geral['vc_total_5k']*13+$pg_geral['vc_total_10k']*25+$pg_geral['vc_total_15k']*37+$pg_geral['vc_total_20k']*49;

			$qtde_geral_5k  += $pg_geral['vc_total_5k'];
			$qtde_geral_10k += $pg_geral['vc_total_10k'];
			$qtde_geral_15k += $pg_geral['vc_total_15k'];
			$qtde_geral_20k += $pg_geral['vc_total_20k'];

			$qtde_geral += $qtde_ongame;
			$valor_geral += $vendas_ongame;

//echo "$qtde_ongame  (".$pg_geral['vc_total_5k'].") -> $qtde_geral<br>";
		}

//		$estat .= " limit ".$max; 
//		$estat .= " offset ".$inicial;

	}
		
//	trace_sql($estat, "Arial", 2, "#666666", 'b');
$sql_transform=$estat;
	
	$resestat = pg_exec($connid, $estat);

//	if($max + $inicial > $total_table)
		$reg_ate = $total_table;
//	else
//		$reg_ate = $max + $inicial;

	$varsel  = "&dd_operadora=$dd_operadora&tf_data_inicial=$tf_data_inicial&tf_data_final=$tf_data_final&dd_valor=$dd_valor";
	$varsel .= "&dd_canal=$dd_canal&dd_estabelecimento=$dd_estabelecimento";
		
?>
<html>
<head>

<link href="../../incs/css.css" rel="stylesheet" type="text/css">
<title>E-Prepag</title>
<script language='javascript' src='/js/<?php echo LANG_NAME_CALENDAR_FILE; ?>'></script>
<script language="JavaScript">
<!--
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}

function envia_novo() { 
  document.formlista.op.value = "env";
//alert("op: "+document.formlista.op.value+", id:"+document.formlista.id.value+", action: "+document.formlista.action+"");
  document.formlista.submit();
}
function setMsg(smg) {
    console.log(smg);
	document.formlista.msgbody.value = smg;
<?php 
	//	document.getElementById('msgshow').innerHTML = smg; 
?>
}
function mandaEmail() {
	document.formlista.mandaemail.value = "1";
	document.formlista.submit();
}

//-->
</script>
</head>
<body bgcolor="#FFFFFF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<!-- INICIO CODIGO NOVO -->
<link href="/bootstrap/css/bootstrap.min_new.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/js/jquery.js"></script>
<script type="text/javascript" src="/includes/bootstrap/js/bootstrap.min.js"></script>
<div class="container-fluid">
    <div class="container txt-azul-claro bg-branco">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12 ">
                        <strong><?php echo LANG_CARDS_PAGE_TITLE_5; ?></strong>
                    </div>
                </div>
                <div class="row txt-cinza">
                    <div class="col-md-6">
                        <span class="pull-left"><strong><?php echo LANG_CARDS_REPORT; ?></strong></span>
                    </div>
                    <div class="col-md-6">
                        <span class="pull-right"><a href="/sys/admin/commerce/index.php" class="btn btn-primary"><strong><i><?php echo LANG_BACK; ?></i></strong></a></span>
                    </div>
                </div>
                <form name="formlista" method="post" action="">
                    <input type="hidden" name="op" id="op" value="">
                    <input type="hidden" name="id" id="id" value="">
                    <input type="hidden" name="msgbody" id="msgbody" value="">
                    <input type="hidden" name="mandaemail" id="mandaemail" value="">
                    <div class="row txt-cinza top10">
                        <div class="col-md-2">
                            <span class="pull-right">Email To</span>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control" name="dd_destinatarios" value="<?php echo $tf_destinatarios?>" maxlength="255">
                        </div>
                        <div class="col-md-2">
                            <span class="pull-right">Email Bcc</span>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control" name="dd_copia_oculta" value="<?php echo $tf_copia_oculta?>" maxlength="255">
                        </div>
                        <div class="col-md-2">
                            <input type="button" name="BtnInsert" value="<?php echo LANG_CARDS_SEND_REPORT; ?>" class="btn btn-success pull-right" onClick="envia_novo();">
                        </div>
                    </div>
                    <div class="row txt-cinza top10">
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_CARDS_START_DATE; ?></span>
                        </div>
                        <div class="col-md-3">
                            <input  alt="Calendário" name="tf_data_inicial" type="text" class="form-control w-ipt-medium pull-left data" id="tf_data_inicial" value="<?php  echo $tf_data_inicial ?>" size="9" maxlength="10">
                        </div>
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_CARDS_END_DATE; ?></span>
                        </div>
                        <div class="col-md-3">
                            <input alt="Calendário" name="tf_data_final" type="text" class="form-control w-ipt-medium pull-left data" id="tf_data_final" value="<?php  echo $tf_data_final ?>" size="9" maxlength="10">
                        </div>
                    </div>
                    <div class="row txt-cinza top10">
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_CARDS_OPERATOR; ?></span>
                        </div>
                        <div class="col-md-3 text-left">
                            <input type="hidden" name="dd_operadora" id="dd_operadora" value="13">ONGAME (13)
                        </div>
                        <div class="col-md-offset-5 col-md-2">
                            <button type="submit" name="BtnSearch" value="<?php echo LANG_CARDS_SEARCH_2; ?>" class="btn pull-right btn-success"><?php echo LANG_CARDS_SEARCH_2; ?></button>
                        </div>
                    </div>
                </form>
<?php 
                if(strlen($msg)>0)
                {
?>
                <div class="row txt-cinza ">
                    <span class="txt-vermelho bg-cinza-claro espacamento">
                        <?php echo $msg?>
                    </span>
                </div>
<?php 
                } 

                if(($data_inic_invalida == true) || ($data_fim_invalida == true) || ($data_inicial_menor == true) ) 
                {
?>
                <div class="row txt-cinza ">
                    <span class="txt-vermelho bg-cinza-claro espacamento">
<?php
                    if($data_inic_invalida == true) echo "<br><font face='Arial, Helvetica, sans-serif' size='2' color='#FF0000'><b>".LANG_CARDS_START_DATE."</b></font>";
                    if($data_fim_invalida == true) echo "<br><font face='Arial, Helvetica, sans-serif' size='2' color='#FF0000'><b>".LANG_CARDS_END_DATE."</b></font>";
                    if($data_inicial_menor == true) echo "<br><font face='Arial, Helvetica, sans-serif' size='2' color='#FF0000'><b>".LANG_CARDS_START_END_DATE."</b></font>";
?>
                    </span>
                </div>
<?php 
                } 
?>
                <div class="row txt-cinza espacamento">
                    <div class="col-md-12 bg-cinza-claro">
<?php
                if($total_table > 0) 
                { 
                    $tmpText = LANG_SHOW." <strong>".$total_table."</strong> ".LANG_DATA.".";
                    if($tf_data_inicial==$tf_data_final) 
                        $tmpText .= "(para dia ".$tf_data_inicial.")";
                    else 
                        $tmpText .= "(para período de ".$tf_data_inicial." a ".$tf_data_final.")";
                }
                
                $_SESSION['sqldata']=$sql_transform;
                
                require_once $raiz_do_projeto."class/util/CSV.class.php";

                $cabecalho = ";;;;;;5K;5K;5K;10K;10K;10K;15K;15K;15K;20K;20K;20K";
                $subCabecalho = LANG_CARDS_NAME.";".LANG_CARDS_FULL_NAME.";5K;10K;15K;20K;".LANG_CARDS_TOTAL_QUANTITY.";".LANG_CARDS_START.";".LANG_CARDS_END.";".LANG_CARDS_TOTAL_QUANTITY.";".LANG_CARDS_START.";".LANG_CARDS_END.";".LANG_CARDS_TOTAL_QUANTITY.";".LANG_CARDS_START.";".LANG_CARDS_END.";".LANG_CARDS_TOTAL_QUANTITY.";".LANG_CARDS_START.";".LANG_CARDS_END;
                
                $objCsv = new CSV($cabecalho, md5(uniqid()), $raiz_do_projeto."public_html/cache/");
                $objCsv->setCabecalho();
                
                $objCsv->setLine($subCabecalho);
                
                $qtde_total_5k_tela  = 0;
                $qtde_total_10k_tela = 0;
                $qtde_total_15k_tela = 0;
                $qtde_total_20k_tela = 0;
                $qtde_total_mu_online_tela = 0;
                $const = get_defined_constants();
                $sout = <<<HTML
                        <table id="table" class="table bg-branco txt-preto fontsize-p">
                            <thead>
                            <tr class="bg-cinza-claro">
                                <th class="text-center" colspan='6'>&nbsp;</th>
                                <th class="text-center border-left" colspan='3'><strong>5K</strong></th>
                                <!--<th>&nbsp;</th>-->
                                <th class="text-center border-left" colspan='3'><strong>10K</strong></th>
                                <!--<th>&nbsp;</th>-->
                                <th class="text-center border-left" colspan='3'><strong>15K</strong></th>
                                <!--<th>&nbsp;</th>-->
                                <th class="text-center border-left" colspan='3'><strong>20K</strong></td>
                            </tr>
                            <tr class="bg-cinza-claro">
                                <th class="text-center"><strong>{$const['LANG_CARDS_NAME']}</strong></th>
                                <th class="text-center"><strong>{$const['LANG_CARDS_FULL_NAME']}</strong></th>
                                <!--<th>&nbsp;</th>-->
                                <th><strong>5K</strong></th>
                                <th><strong>10K</strong></th>
                                <th><strong>15K</strong></th>
                                <th><strong>20K</strong></th>
                                <!--<th>&nbsp;</th>-->
                                <th class="border-left"><strong>{$const['LANG_CARDS_TOTAL_QUANTITY']}</strong> </th>
                                <th><strong>{$const['LANG_CARDS_START']}</strong> </th>
                                <th><strong>{$const['LANG_CARDS_END']}</strong> </th>
                                <!--<th>&nbsp;</th>-->
                                <th class="border-left"><strong>{$const['LANG_CARDS_TOTAL_QUANTITY']}</strong> </th>
                                <th><strong>{$const['LANG_CARDS_START']}</strong> </th>
                                <th><strong>{$const['LANG_CARDS_END']}</strong> </th>
                                <!--<th>&nbsp;</th>-->
                                <th class="border-left"><strong>{$const['LANG_CARDS_TOTAL_QUANTITY']}</strong> </th>
                                <th><strong>{$const['LANG_CARDS_START']}</strong> </th>
                                <th><strong>{$const['LANG_CARDS_END']}</strong> </th>
                                <!--<th>&nbsp;</th>-->
                                <th class="border-left"><strong>{$const['LANG_CARDS_TOTAL_QUANTITY']}</strong> </th>
                                <th><strong>{$const['LANG_CARDS_START']}</strong> </th>
                                <th><strong>{$const['LANG_CARDS_END']}</strong> </th>
                            </tr>
                            </thead>
HTML;
                                
                if(!(($data_inic_invalida == true) || ($data_fim_invalida == true) || ($data_inicial_menor == true)) ) 
                {
                    while ($pgrow = pg_fetch_array($resestat)) 
                    {
                        $valor = true;

                        $qtde_5k  = 0;
                        $qtde_10k = 0;
                        $qtde_15k = 0;
                        $qtde_20k = 0;

                        if(($dd_operadora==13) || ($dd_operadora=='')) 
                        {
                            $qtde_5k  = $pgrow['vc_total_5k'];
                            $qtde_10k = $pgrow['vc_total_10k'];
                            $qtde_15k = $pgrow['vc_total_15k'];
                            $qtde_20k = $pgrow['vc_total_20k'];

                            $qtde_ongame = $pgrow['vc_total_5k']+$pgrow['vc_total_10k']+$pgrow['vc_total_15k']+$pgrow['vc_total_20k'];
                            $vendas_ongame = $pgrow['vc_total_5k']*13+$pgrow['vc_total_10k']*25+$pgrow['vc_total_15k']*37+$pgrow['vc_total_20k']*49;
                        }

                        $valor_total_tela += $vendas_ongame;
                        $qtde_total_tela += $qtde_ongame;

                        $qtde_total_5k_tela += $qtde_5k;
                        $qtde_total_10k_tela += $qtde_10k;
                        $qtde_total_15k_tela += $qtde_15k;
                        $qtde_total_20k_tela += $qtde_20k;

                        $lineCsv = array();
                        $lineCsv[] = $pgrow['ug_nome_fantasia'];
                        $lineCsv[] = $pgrow['ug_razao_social'];
                        $lineCsv[] = ((($dd_operadora==13) || ($dd_operadora==''))?$pgrow['vc_total_5k']:'-');
                        $lineCsv[] = ((($dd_operadora==13) || ($dd_operadora==''))?$pgrow['vc_total_10k']:'-');
                        $lineCsv[] = ((($dd_operadora==13) || ($dd_operadora==''))?$pgrow['vc_total_15k']:'-');
                        $lineCsv[] = ((($dd_operadora==13) || ($dd_operadora==''))?$pgrow['vc_total_20k']:'-');
                        $lineCsv[] = ((($dd_operadora==13) || ($dd_operadora==''))?$pgrow['vc_total_5k']:'-');
                        $lineCsv[] = ((($dd_operadora==13) || ($dd_operadora==''))?$pgrow['vc_inicial_5k']:'-');
                        $lineCsv[] = ((($dd_operadora==13) || ($dd_operadora==''))?$pgrow['vc_final_5k']:'-');
                        $lineCsv[] = ((($dd_operadora==13) || ($dd_operadora==''))?$pgrow['vc_total_10k']:'-');
                        $lineCsv[] = ((($dd_operadora==13) || ($dd_operadora==''))?$pgrow['vc_inicial_10k']:'-');
                        $lineCsv[] = ((($dd_operadora==13) || ($dd_operadora==''))?$pgrow['vc_final_10k']:'-');
                        $lineCsv[] = ((($dd_operadora==13) || ($dd_operadora==''))?$pgrow['vc_total_15k']:'-');
                        $lineCsv[] = ((($dd_operadora==13) || ($dd_operadora==''))?$pgrow['vc_inicial_15k']:'-');
                        $lineCsv[] = ((($dd_operadora==13) || ($dd_operadora==''))?$pgrow['vc_final_15k']:'-');
                        $lineCsv[] = ((($dd_operadora==13) || ($dd_operadora==''))?$pgrow['vc_total_20k']:'-');
                        $lineCsv[] = ((($dd_operadora==13) || ($dd_operadora==''))?$pgrow['vc_inicial_20k']:'-');
                        $lineCsv[] = ((($dd_operadora==13) || ($dd_operadora==''))?$pgrow['vc_final_20k']:'-');

                        if(is_array($lineCsv)) $objCsv->setLine(implode(";",$lineCsv));
                        
                        $sout .= <<<HTML
                        <tr class="trListagem"> 
                            <td>{$pgrow['ug_nome_fantasia']}</td>
                            <td>{$pgrow['ug_razao_social']}</td>
                            <!--<td align='right'>&nbsp;</td>-->
HTML;
                        if(($dd_operadora==13) || ($dd_operadora==''))
                        {
                            $sout .= <<<HTML
                            <td>{$pgrow['vc_total_5k']}</td>
                            <td class="text-center">{$pgrow['vc_total_10k']}</td>
                            <td class="text-center">{$pgrow['vc_total_15k']}</td>
                            <td class="text-center">{$pgrow['vc_total_20k']}</td>
                            <!--  Por valor ===================== -->
                            <!--<td align='right'>&nbsp;</td>-->
                            <td class="border-left">{$pgrow['vc_total_5k']}</td>
                            <td class="text-center">{$pgrow['vc_inicial_5k']}</td>
                            <td class="text-center">{$pgrow['vc_final_5k']}</td>
                            <!--<td align='right'>&nbsp;</td>-->
                            <td class="border-left">{$pgrow['vc_total_10k']}</td>
                            <td class="text-center">{$pgrow['vc_inicial_10k']}</td>
                            <td class="text-center">{$pgrow['vc_final_10k']}</td>
                            <!--<td align='right'>&nbsp;</td>-->
                            <td class="border-left">{$pgrow['vc_total_15k']}</td>
                            <td class="text-center">{$pgrow['vc_inicial_15k']}</td>
                            <td class="text-center">{$pgrow['vc_final_15k']}</td>
                            <!--<td align='right'>&nbsp;</td>-->
                            <td class="border-left">{$pgrow['vc_total_20k']}</td>
                            <td class="text-center">{$pgrow['vc_inicial_20k']}</td>
                            <td class="text-center">{$pgrow['vc_final_20k']}</td>    
                        </tr>
HTML;
                        }else
                        {
                            $sout .= <<<HTML
                            <td-</td>
                            <td class="text-center">-</td>
                            <td class="text-center">-</td>
                            <td class="text-center">-</td>
                            <!--  Por valor ===================== -->
                            <!--<td align='right'>&nbsp;</td>-->
                            <td-</td>
                            <td class="text-center">-</td>
                            <td class="text-center">-</td>
                            <!--<td align='right'>&nbsp;</td>-->
                            <td-</td>
                            <td class="text-center">-</td>
                            <td class="text-center">-</td>
                            <!--<td align='right'>&nbsp;</td>-->
                            <td-</td>
                            <td class="text-center">-</td>
                            <td class="text-center">-</td>
                            <!--<td align='right'>&nbsp;</td>-->
                            <td-</td>
                            <td class="text-center">-</td>
                            <td class="text-center">-</td>    
                        </tr>
HTML;
                        }
                    }
                }
                
                $csv = $objCsv->export();
                                
                if (!$valor) 
                {
                    $sout .= <<<HTML
                        <tr class=""> 
                            <td colspan='18'>
                                <strong>{$const['LANG_NO_DATA']}</strong>
                            </td>
                        </tr>
HTML;
                } else 
                {
                    $time_end = getmicrotime();
                    $time = $time_end - $time_start;
//					paginacao_query($inicial, $total_table, $max, '11', $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel);
		    $time = number_format($time, 2, '.', '.');
                    $qtde_geral = number_format($qtde_geral, 0, ',', '.');
                    $qtde_geral_5k = number_format($qtde_geral_5k, 0, ',', '.');
                    $qtde_geral_10k = number_format($qtde_geral_10k, 0, ',', '.');
                    $qtde_geral_15k = number_format($qtde_geral_15k, 0, ',', '.');
                    $qtde_geral_20k = number_format($qtde_geral_20k, 0, ',', '.');
                    
                    $sout .= <<<HTML
                        <tr class="bg-cinza-claro">
                            <td align='right'><strong>{$const['LANG_CARDS_TOTAL']}</strong></td>
                            <td align='center'><strong>{$qtde_geral}</strong></td>
                            <td align='center'><strong>{$qtde_geral_5k}</strong></td>
                            <td align='center'><strong>{$qtde_geral_10k}</strong></td>
                            <td align='center'><strong>{$qtde_geral_15k}</strong></td>
                            <td align='center'><strong>{$qtde_geral_20k}</strong></td>
                            <td colspan='12'>&nbsp;</td>
                        </tr>
                        <tr class="bg-cinza-claro">
                            <td colspan='18'>
                                {$const['LANG_CARDS_SEARCH_MSG']} {$time} {$const["LANG_CARDS_SEARCH_MSG_UNIT"]}
                            </td>
                        </tr>
HTML;
              
                    if(isset($csv))
                    {
                        $sout .= <<<HTML
                        <tr>
                            <td colspan='18' class="text-center">
                                <a href='/includes/downloadCsv.php?csv={$csv}&dir=cache'" target="_blank" class="btn downloadCsv btn-info ">Download CSV</a>
                            </td>
                        </tr>
HTML;
                    }
                } 
                
                $sout .= <<<HTML
                    </table>
HTML;

                echo str_replace("<t", "\n<t", str_replace($order, $replace, $sout));

                /*
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


                            require_once $raiz_do_projeto."\class\util\CSV.class.php";

                            $cabecalho = "ID;N Tit.;".LANG_CARDS_DATE.";".LANG_CARDS_ESTABLISHMENT.";".LANG_PINS_CHANNEL.";5k;10k;20K;Mu;Mu+;".LANG_CARDS_QUANTITY.";".LANG_CARDS_SALES_REAL.";".LANG_CARDS_SALES_REAL.";".LANG_CARDS_SALES_REAL."(".LANG_CARDS_OUT_COMMISSION_FREIGHT.")";

                            $objCsv = new CSV($cabecalho, md5(uniqid()), $raiz_do_projeto."\www\web\cache\\");
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
                                    $csv = "/incs/downloadCsv.php?csv=$csv&dir=cache";
                                }elseif(isset($_GET["downloadCsv"]))
                                {
                                    require_once $raiz_do_projeto."/www/web/incs/downloadCsv.php";
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
                 *                  */
?>
                        
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
<?php require_once $raiz_do_projeto . "public_html/sys/includes/rodape_sys.php";?>
</body>
</html>
