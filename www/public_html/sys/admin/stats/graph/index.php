<?php 
session_start();
require_once "../../../../../includes/constantes.php";
require_once $raiz_do_projeto . "public_html/sys/includes/topo_sys.php";
//include('date.php');
require("dateClass.php");

require_once $raiz_do_projeto . "includes/sys/inc_stats.php";
$time_start_stats = getmicrotime();

// mostra os dados passados por POST
//echo '<pre>';
//print_r($_POST);
//echo '</pre>';
// fim
if($_SESSION["tipo_acesso_pub"]=='PU') {
    header("Location: /sys/admin/commerce/index.php");
    die;
}

$itensgeral = "";
$groupgeral = "";

$itensespec = "";
$groupespec = "";

$where		= "";

function diasemana($data) {
	$ano =  substr("$data", 0, 4);
	$mes =  substr("$data", 5, -3);
	$dia =  substr("$data", 8, 9);

	$diasemana = date("w", mktime(0,0,0,$mes,$dia,$ano) );

	switch($diasemana) {
		case"0": $diasemana = "Dom";       break;
		case"1": $diasemana = "2ªf"; break;
		case"2": $diasemana = "3ªF";   break;
		case"3": $diasemana = "4ªF";  break;
		case"4": $diasemana = "5ªF";  break;
		case"5": $diasemana = "6ªF";   break;
		case"6": $diasemana = "Sáb";        break;
	}

	echo "$diasemana";
}

function subDayIntoDate($date,$days) {
     $dtiaux = explode('-',$date);

     $thisyear  = $dtiaux[0];
     $thismonth = $dtiaux[1];
     $thisday   = $dtiaux[2];
     $nextdate = mktime ( 0, 0, 0, $thismonth, $thisday - $days, $thisyear );
     return strftime("%Y-%m-%d", $nextdate);
}

function addDayIntoDate($date,$days) {
	 $dtiaux = explode('-',$date);
     
     $thisyear  = $dtiaux[0];
     $thismonth = $dtiaux[1];
     $thisday   = $dtiaux[2];
	 
     $nextdate = mktime ( 0, 0, 0, $thismonth, $thisday + $days, $thisyear );
     //return strftime("%Y-%m-%d", $nextdate);
	 return strftime("%Y-%m-%d", $nextdate);
}

// Recupera os dados passados por POST
$act	 			= $_POST['act'];
$statuspgto			= $_POST['statuspgto'];
$fpgto 				= $_POST['fpgto'];
$canais				= $_POST['canais'];
$periodo 			= $_POST['periodo'];
$tf_data_inicial	= $_POST['tf_data_inicial'];
$tf_data_final 		= $_POST['tf_data_final'];
$tipoconsulta		= $_POST['tipoconsulta'];
// Fim

if(empty($fpgto)) {
	$fpgto = "0";
}

if(empty($statuspgto)) {
	$statuspgto = 5;
}

if(empty($periodo)) {
	$periodo = "M";
}

if(empty($tipoconsulta)) {
	$tipoconsulta = 'O';
}

if(empty($canais)) {
	$canais = "0";
} else if($canais == 'C' || $canais == 'P') {
	$statuspgto = 5;
} else {
	// vazio
}

if($fpgto != 0) {
	$itensgeral  .= ",pagto_tipo ";
	$groupgeral  .= ",pagto_tipo ";
	
	$itensespec  .= ",10000 AS pagto_tipo";
	$itensespec2 .= ", vg_pagto_tipo  as pagto_tipo";
	$groupespec  .= ",vg_pagto_tipo";
	
	$where       .= " AND pagto_tipo = $fpgto";
}

if($canais != "0") {
	$itensgeral  .= ",canal ";
	$groupgeral  .= ",canal ";
	$where       .= " AND canal = '$canais'";
	$groupespec  .= " ,canal ";
}


if(empty($tf_data_inicial) && empty($tf_data_final)) {
	$data_inicial = date('Y-m-d');

	if($periodo == 'M') {
		$data_final  = subDayIntoDate(date('Y-m-d'),30);
		$periodo1 	 = subDayIntoDate(date('Y-m-d'),30);
		$periodo2 	 = subDayIntoDate($data_final,30);
	} else if($periodo == 'S') {
		$data_final		 = subDayIntoDate(date('Y-m-d'),7);
		$periodo1 		 = subDayIntoDate(date('Y-m-d'),7);
		$periodo2 		 = subDayIntoDate($data_final,7);
	} else if($periodo == 'A') {
		$data_final      = subDayIntoDate(date('Y-m-d'),365);
		$periodo1		 = subDayIntoDate(date('Y-m-d'),365);	
		$periodo2 		 = subDayIntoDate($data_final,365);	
	} else if($periodo == 'D') {
		$data_final      = subDayIntoDate(date('Y-m-d'),1);	
		$periodo1		 = subDayIntoDate(date('Y-m-d'),1);	
		$periodo2 		 = subDayIntoDate($data_final,1);	
	} else if($periodo == 'T') {
		$data_final  	 = subDayIntoDate(date('Y-m-d'),90);
		$periodo1		 = subDayIntoDate(date('Y-m-d'),90);			
		$periodo2 		 = subDayIntoDate($data_final,90);
	} else {
		//vazio
	}	
}


if(!empty($tf_data_inicial) && empty($tf_data_final)) {
	$dtiaux 	  = explode('/',$tf_data_inicial);
	$data_inicial = $dtiaux[2].'-'.$dtiaux[1].'-'.$dtiaux[0]; 

	//Alteração Wagner - data auxiliar inicial
	$dia_aux_inicial = date("t", mktime(0, 0, 0, $dtiaux[1]-1, $dtiaux[0], $dtiaux[2]));

	if($periodo == 'M') {
		$data_final		 = subDayIntoDate($data_inicial,$dia_aux_inicial);
		$periodo1 		 = subDayIntoDate($data_inicial,30);
		$periodo2 		 = subDayIntoDate($data_final,30);
	} else if($periodo == 'S') {
		$data_final	     = subDayIntoDate($data_inicial,7);
		$periodo1 		 = subDayIntoDate($data_inicial,7);
		$periodo2 		 = subDayIntoDate($data_final,7);
	} else if($periodo == 'A') {
		$data_final	     = subDayIntoDate($data_inicial,365);	
		$periodo1 		 = subDayIntoDate($data_inicial,365);
		$periodo2 		 = subDayIntoDate($data_final,365);	
	} else if($periodo == 'D') {
		$data_final      = subDayIntoDate($data_inicial,1);	
		$periodo1 		 = subDayIntoDate($data_inicial,1);
		$periodo2 		 = subDayIntoDate($data_final,1);	
	} else if($periodo == 'T') {
		$data_final      = subDayIntoDate($data_inicial,90);	
		$periodo1 		 = subDayIntoDate($data_inicial,90);	
		$periodo2 		 = subDayIntoDate($data_final,90);
	} else {
		//vazio
	}
}

if(!empty($tf_data_inicial) && !empty($tf_data_final)) {
	$dtiaux 	  = explode('/',$tf_data_inicial);
	$data_inicial = $dtiaux[2].'-'.$dtiaux[1].'-'.$dtiaux[0]; 

	//Alteração Wagner - data auxiliar inicial
	$dia_aux_inicial = date("t", mktime(0, 0, 0, $dtiaux[1]-1, $dtiaux[0], $dtiaux[2]));

	$dtfaux 	  = explode('/',$tf_data_final);
	$data_final	  = $dtfaux[2].'-'.$dtfaux[1].'-'.$dtfaux[0]; 	

	//Alteração Wagner - data auxiliar final
	$dia_aux_final = date("t", mktime(0, 0, 0, $dtfaux[1]-1, $dtfaux[0], $dtfaux[2]));
	
	if($periodo == 'M') {
		$periodo1 		 = subDayIntoDate($data_inicial,$dia_aux_inicial);
		$periodo2 		 = subDayIntoDate($data_final,$dia_aux_final);
	} else if($periodo == 'S') {
		$periodo1 		 = subDayIntoDate($data_inicial,7);
		$periodo2 		 = subDayIntoDate($data_final,7);
	} else if($periodo == 'A') {
		$periodo1 		 = subDayIntoDate($data_inicial,365);
		$periodo2 		 = subDayIntoDate($data_final,365);
	} else if($periodo == 'D') {
		$periodo1 		 = subDayIntoDate($data_inicial,1);
		$periodo2 		 = subDayIntoDate($data_final,1);	
	} else if($periodo == 'T') {
		$periodo1 		 = subDayIntoDate($data_inicial,90);
		$periodo2 		 = subDayIntoDate($data_final,90);
	} else {
		//vazio
	}
}

$dataiaux = explode('-',$data_inicial);
$datafaux = explode('-',$data_final);
$tf_data_inicial = $dataiaux[2].'/'.$dataiaux[1].'/'.$dataiaux[0];
$tf_data_final   = $datafaux[2].'/'.$datafaux[1].'/'.$datafaux[0];
?>

<!doctype html public "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
    <title> <?php echo LANG_STATISTICS_TOTAL_SALES; ?> </title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <link href="/includes/bootstrap/css/bootstrap.min_new.css" rel="stylesheet" type="text/css" />
    <script type="text/javascript" src="/js/jquery.js"></script>
    <script type="text/javascript" src="/includes/bootstrap/js/bootstrap.min.js"></script>
    <link href="/sys/css/css.css" rel="stylesheet" type="text/css">
    <link href="/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
    <script src="/js/jquery-ui-1.9.2.custom.min.js"></script>
    <script src="/js/global.js"></script>
</head>
<script>
window.name = "index";
var a = jQuery.noConflict();
a(function(){
   a("#tf_data_inicial").datepicker({
        maxDate: "dateToday",
        changeMonth: true,
        dateFormat: "dd/mm/yy"
    });
    
    a("#tf_data_final").datepicker({
        maxDate: "dateToday",
        changeMonth: true,
        dateFormat: "dd/mm/yy"
    });
});

</script>
<body>

<?php
	$bg_col_01 = "#FFFFFF";
	$bg_col_02 = "#EEEEEE";
	$bg_col = $bg_col_01;
	$extra_where = "";
	$where_operadora = "";
	$where_operadora_pos = "";
	$where_operadora_cartoes = "";
	$dd_year = ""; //date("Y");


	if($_SESSION["tipo_acesso_pub"]=='PU') {
		$dd_operadora = $_SESSION["opr_codigo_pub"];
		$dd_mode = "S";

		if($dd_operadora==13)	//($dd_operadora_nome=='ONGAME') 
			$where_operadora_pos = " (ve_jogo = 'OG') ";
		elseif  ($dd_operadora==17)	//($dd_operadora_nome=='MU ONLINE') 
			$where_operadora_pos = " (ve_jogo = 'MU') ";
		elseif  ($dd_operadora==16)	//($dd_operadora_nome=='HABBO HOTEL') 
			$where_operadora_pos = " (ve_jogo = 'HB') ";
		else
			$where_operadora_pos = " (ve_jogo = 'xx') ";
		
		if($dd_operadora==13) { 
			$where_operadora_cartoes = " (ve_jogo='OG') ";
		} else if($dd_operadora==17) { 
			$where_operadora_cartoes = " (ve_jogo='MU') ";
		} else { 
			$where_operadora_cartoes = "xx";
		}
	}

	if(!$dd_mode || ($dd_mode!='V')) {
		$dd_mode = "S";
	}
	$smode = $dd_mode;

	$dd_operadora_nome = "";
	if($dd_operadora) {
		$sql 		 = "select opr_nome from operadoras where (opr_status = '1') and (opr_codigo='".$dd_operadora."') order by opr_ordem";
		$resopr_nome = SQLexecuteQuery($sql);
		//$resopr_nome = pg_exec($connid, "select opr_nome from operadoras where (opr_status = '1') and (opr_codigo='".$dd_operadora."') order by opr_ordem");
		if($pgopr_nome = pg_fetch_array ($resopr_nome)) { 
			$dd_operadora_nome = $pgopr_nome['opr_nome'];
		} 

		if($dd_operadora==13)	//($dd_operadora_nome=='ONGAME') 
			$where_operadora_pos = " (ve_jogo = 'OG') ";
		elseif  ($dd_operadora==17)	//($dd_operadora_nome=='MU ONLINE') 
			$where_operadora_pos = " (ve_jogo = 'MU') ";
		elseif  ($dd_operadora==16)	//($dd_operadora_nome=='HABBO HOTEL') 
			$where_operadora_pos = " (ve_jogo = 'HB') ";
		else
			$where_operadora_pos = " (ve_jogo = 'xx') ";
		

		if($dd_operadora==13) { 
			$where_operadora_cartoes = " (ve_jogo='OG') ";
		} else if($dd_operadora==17) { 
			$where_operadora_cartoes = " (ve_jogo='MU') ";
		} else { 
			$where_operadora_cartoes = " (ve_jogo = 'xx') ";
		}

		$where_operadora = " vgm_opr_codigo=".$dd_operadora."";
//		$extra_where = $where_operadora;
	}

	if($_SESSION["tipo_acesso_pub"]=='PU') {
		$sqlopr = "select opr_nome, opr_codigo from operadoras where (opr_status = '1') and (opr_codigo='".$dd_operadora."') order by opr_ordem";
	} else {
		$sqlopr = "select opr_nome, opr_codigo from operadoras where (opr_status != '0') order by opr_ordem";
	}
	$resopr = SQLexecuteQuery($sqlopr);
	//$resopr = pg_exec($connid, $sqlopr);
	//echo "$sqlopr<br>";
?>
<script language="javascript">
function montaPeriodo(periodo) {
	var periodo = periodo;
	alert(periodo);
}
</script>
<div class="container-fluid">
    <div class="container txt-azul-claro bg-branco txt-cinza">
        <div class="row">
            <div class="col-md-12 ">
                <strong><h3><?php echo LANG_STATISTICS_PAGE_TITLE_8; ?> <?="<font color='#3300FF'>".$dd_operadora_nome."</font>"?>(<?=get_current_date()?>)</h3></strong>
            </div>
        </div>
        <div class="row txt-cinza espacamento">
            <div class="col-md-6">
                <span class="pull-left"><strong><?php echo LANG_POS_SEARCH_1; ?></strong></span>
            </div>
            <div class="col-md-6">
                <span class="pull-right"><a href="/sys/admin/commerce/index.php" class="btn btn-primary"><strong><i><?php echo LANG_BACK; ?></i></strong></a></span>
            </div>
        </div>
        <form name="form1" method="post" action="">
        <input type="hidden" name="act" id="act" value="1" />
        <div class="row txt-cinza top10">
            <div class="col-md-2">
                <span class="pull-right">Tipo de Consulta</span>
            </div>
            <div class="col-md-3">
                <select name="tipoconsulta" id="tipoconsulta" class="form-control" onChange="document.form1.submit()" style="width:300px;">
                    <option value="O" <?php if($tipoconsulta == 'O') {?> selected <?php }?>>OPERADORA</option>
                    <option value="J" <?php if($tipoconsulta == 'J') {?> selected <?php }?>>JOGO</option>
                </select>
            </div>
            <div class="col-md-2">
                <span class="pull-right">Status da Venda</span>
            </div>
            <div class="col-md-3">
                <select name="statuspgto" id="statuspgto" class="form-control" onChange="document.form1.submit()" style="width:300px;">
                    <option value="1" <?php if($statuspgto == '1') {?> selected <?php }?>>PEDIDO EFETUADO</option>
                    <option value="2" <?php if($statuspgto == '2') {?> selected <?php }?>>DADOS PAGTO RECEBIDO</option>
                    <option value="3" <?php if($statuspgto == '3') {?> selected <?php }?>>PAGTO CONFIRMADO</option>
                    <option value="4" <?php if($statuspgto == '4') {?> selected <?php }?>>PROCESSAMENTO REALIZADO</option>
                    <option value="5" <?php if($statuspgto == '5' || (empty($_POST['statuspgto'])) ) {?> selected <?php }?>>VENDA REALIZADA</option>
                    <option value="6" <?php if($statuspgto == '6') {?> selected <?php }?>>VENDA CANCELADA</option>
              </select>    
            </div>
        </div>
        <div class="row txt-cinza top10">
            <div class="col-md-2">
                <span class="pull-right">Formas de Pagto</span>
            </div>
            <div class="col-md-3">
                <select id="fpgto" name="fpgto" class="form-control" onChange="document.form1.submit()" style="width:300px;"> 
                    <option value="0" selected>Todas as formas</option>
                    <option value="1" <?php if($fpgto == '1') { ?> selected <?php } ?>>Transferência Bancária, DOC Eletrônico, Depósito, Remessa do exterior ou Outros</option>
                    <option value="2" <?php if($fpgto == '2') { ?> selected <?php } ?>>Boleto Bancário</option>
                    <option value="3" <?php if($fpgto == '3') { ?> selected <?php } ?>>MasterCard</option>
                    <option value="5" <?php if($fpgto == '5') { ?> selected <?php } ?>>Transferência entre contas Bradesco (BRD5)</option>
                    <option value="6" <?php if($fpgto == '6') { ?> selected <?php } ?>>Pagamento Fácil Bradesco (BRD6)</option>
                    <option value="9" <?php if($fpgto == '9') { ?> selected <?php } ?>>Pagamento BB - Débito sua Conta (BBR9)</option>
                    <option value="15" <?php if($fpgto == "15") { ?> selected <?php } ?>>Pagamento Banco E-Prepag (BEPZ)</option>
                    <option value="10" <?php if($fpgto == '10') { ?> selected <?php } ?>>Pagamento Banco Itaú (BITA)</option>
                    <option value="11" <?php if($fpgto == '11') { ?> selected <?php } ?>>Pagamento HiPay (HIPB)</option>
                    <option value="12" <?php if($fpgto == '12') { ?> selected <?php } ?>>Pagamento PayPal (PYPP)</option>
                </select>    
            </div>
            <div class="col-md-2">
                <span class="pull-right">Canais</span>
            </div>
            <div class="col-md-3">
                <select name="canais" id="canais" class="form-control" onChange="document.form1.submit()">
                    <option value="0" <?php if($canais == "0") {?> selected <?php }?>>TODOS</option>
                    <option value="C" <?php if($canais == "C") {?> selected <?php }?>>Cart&otilde;es</option>
                    <option value="S" <?php if($canais == "S") {?> selected <?php }?>>Site (M+E+L)</option>
                    <option value="L" <?php if($canais == "L") {?> selected <?php }?>>LH Money</option>
                    <option value="P" <?php if($canais == "P") {?> selected <?php }?>>POS</option>
					<option value="A" <?php if($canais == "A") {?> selected <?php }?>>ATIMO</option>
                </select>
            </div>
        </div>
        <div class="row txt-cinza top10">
            <div class="col-md-2">
                <input name="chkMarketShare" class="pull-right" type="checkbox" id="chkMarketShare" value="1" <?php if($chkMarketShare == "1") echo "checked" ?>/>
            </div>
            <div class="col-md-3">
                <span class="pull-right">Utilizar Produto de Maior Venda como Base de 100% do Market Share.</span>
            </div>
            <div class="col-md-2">
                <span class="pull-right">Periodo</span>
            </div>
            <div class="col-md-3">
                <select name="periodo" id="periodo" class="form-control" onChange="document.form1.submit();">
                    <option value="M" <?php if($periodo == 'M') { ?> selected="selected" <?php } ?>>Mensal</option>
                    <option value="S" <?php if($periodo == 'S') { ?> selected="selected" <?php } ?>>Semanal</option>
                    <option value="A" <?php if($periodo == 'A') { ?> selected="selected" <?php } ?>>Anual</option>
                    <option value="D" <?php if($periodo == 'D') { ?> selected="selected" <?php } ?>>Diário</option>
                    <option value="T" <?php if($periodo == 'T') { ?> selected="selected" <?php } ?>>Trimestral</option>
                </select>  
            </div>
        </div>
        <div class="row txt-cinza top10">
            <div class="col-md-2">
                <span class="pull-right">Data Anterior (<?php diasemana($data_final);?>)</span>
            </div>
            <div class="col-md-3">
                <input name="tf_data_final" type="text" class="form-control" id="tf_data_final" value="<?php echo $tf_data_final ?>" size="9" maxlength="10">
            </div>
            <div class="col-md-2">
                <span class="pull-right">Data Atual (<?php diasemana($data_inicial);?>)</span>
            </div>
            <div class="col-md-3">
                <input name="tf_data_inicial" type="text" class="form-control" id="tf_data_inicial" value="<?php echo $tf_data_inicial ?>" size="9" maxlength="10">
            </div>
            <div class="col-md-2">
                <input type="submit" name="btconsultar" id="btconsultar" value="Consultar" class="btn pull-right btn-success">
            </div>
        </div>
        </form>
<br>
<?php

// resgato o total de vendas por operadoras
$rss = bcgGenerator($statuspgto, $canais, $fpgto, $periodo1, $data_inicial, $connid, $itensgeral, $groupgeral, $itensespec, $groupespec, $where, $itensespec2, $tipoconsulta);

$tot = pg_num_rows($rss);

if($tot > 0) {
	while($vlr=pg_fetch_array($rss)) {
		if($tipoconsulta == 'J') {
		//	echo "COROPR [".$vlr['operadora_codigo']."]<br>";
		//	echo "PUBLIS [".$vlr['publisher']."]<br>";
		$posicao_vetor = str_replace(":", "",str_replace("-", "",str_replace("+", "",str_replace("'", "",str_replace(" ", "",str_replace(".", "",str_replace(",", "",$vlr['jogo_nome'])))))));
		$vetor_indice[$posicao_vetor] = $vlr['publisher'].str_replace(",", "",str_replace(".", "",$vlr['venda_total']));
		$atualData[] = array('operadora_codigo_atual' => $vetor_indice[$posicao_vetor], 'operadora_nome_atual' => $vlr['jogo_nome'], 'n_total_atual' => $vlr['n_total'], 'venda_total_atual' => $vlr['venda_total']);
		$codAtual[] = $vetor_indice[$posicao_vetor];
		}
		else { 
		$atualData[] = array('operadora_codigo_atual' => $vlr['operadora_codigo'], 'operadora_nome_atual' => $vlr['publisher'], 'n_total_atual' => $vlr['n_total'], 'venda_total_atual' => $vlr['venda_total']);
		$codAtual[] = $vlr['operadora_codigo'];
		}
	}
}
// fim

//echo "atualData:<pre>".print_r($atualData, true)."</pre>";


// resgato o total de vendas por operadoras no periodo anterior
$rss = bcgGenerator($statuspgto, $canais, $fpgto, $periodo2, $data_final, $connid, $itensgeral, $groupgeral, $itensespec, $groupespec, $where, $itensespec2, $tipoconsulta);
$tot = pg_num_rows($rss);
if($tot > 0) {
	while($vlr=pg_fetch_array($rss)) {
		
		if($tipoconsulta == 'J') {
		$posicao_vetor = str_replace(":", "",str_replace("-", "",str_replace("+", "",str_replace("'", "",str_replace(" ", "",str_replace(".", "",str_replace(",", "",$vlr['jogo_nome'])))))));
		if(empty($vetor_indice[$posicao_vetor])) $vetor_indice[$posicao_vetor] = $vlr['publisher'].str_replace(",", "",str_replace(".", "",$vlr['venda_total']));
		$anteriorData[] = array('operadora_codigo_anterior' => $vetor_indice[$posicao_vetor], 'operadora_nome_anterior' => $vlr['jogo_nome'], 'n_total_anterior' => $vlr['n_total'], 'venda_total_anterior' => $vlr['venda_total']);
		$codAnterior[] = $vetor_indice[$posicao_vetor];
		}
		else { 
		$anteriorData[] = array('operadora_codigo_anterior' => $vlr['operadora_codigo'], 'operadora_nome_anterior' => $vlr['publisher'], 'n_total_anterior' => $vlr['n_total'], 'venda_total_anterior' => $vlr['venda_total']);
		$codAnterior[] = $vlr['operadora_codigo'];
		}
	}
}
// fim

//echo "anteriorData:<pre>".print_r($anteriorData, true)."</pre>";

if( (empty($codAtual)) || (empty($codAnterior)) ) {
	echo 'Nenhum resultado gerado.';
	die;
}



// Normalizacao das arrays da anterior para a atual
foreach ($anteriorData as $dadosAnterior) {
	foreach ($dadosAnterior as $keyAnterior => $valoresAnterior) {
		$codAnt = $dadosAnterior['operadora_codigo_anterior'];
		$nomAnt = $dadosAnterior['operadora_nome_anterior'];
		$n_Ant  = 0;
		$vlrAnt = 0;
				
		if($keyAnterior == "operadora_codigo_anterior") {
//			echo $valoresAnterior.'<br>';
			
			if(in_array($valoresAnterior,$codAtual)) {
				//echo 'ok<br>';
			} else {
//				echo 'codAnt: '.$codAnt.'<br>';
//				echo 'nomAnt: '.$nomAnt.'<br>';
//				echo 'n_Ant:  '.$n_Ant.'<br>';
//				echo 'vlrAnt: '.$vlrAnt.'<br>';
				
				$atualData[] = array('operadora_codigo_atual' => $codAnt, 'operadora_nome_atual' => $nomAnt, 'n_total_atual' => $n_Ant, 'venda_total_atual' => $vlrAnt);		
			}
		}
	}
}


// Normalizacao das arrays da atual para a anterior
foreach ($atualData as $dadosAtual) {
	foreach ($dadosAtual as $keyAtual => $valoresAtual) {
		$codAtual = $dadosAtual['operadora_codigo_atual'];
		$nomAtual = $dadosAtual['operadora_nome_atual'];
		$n_Atual  = 0;
		$vlrAtual = 0;
		
		if($keyAtual == "operadora_codigo_atual") {
			//echo $valoresAtual.'<br>';
			
			if(in_array($valoresAtual,$codAnterior)) {
				//echo 'ok<br>';
			} else {
				//echo 'codAtual: '.$codAtual.'<br>';
				//echo 'nomAtual: '.$nomAtual.'<br>';
				//echo 'n_Atual:  '.$n_Atual.'<br>';
				//echo 'vlrAtual: '.$vlrAtual.'<br>';
				
				$anteriorData[] = array('operadora_codigo_anterior' => $codAtual, 'operadora_nome_anterior' => $nomAtual, 'n_total_anterior' => $n_Atual, 'venda_total_anterior' => $vlrAtual);				
			}
		}
	}
}

sort($atualData);
sort($anteriorData);

// Agora depois da normailzacao, crio os arrays finais
	$opr_cod = array();
	foreach ($atualData as $dadosAtual) {
		foreach ($dadosAtual as $keyAtual => $valoresAtual) {
			if(!in_array($dadosAtual['operadora_codigo_atual'],$opr_cod)) {
				$opr_cod[]	= $dadosAtual['operadora_codigo_atual'];
				$nome_opr[]	= $dadosAtual['operadora_nome_atual'];
				$n_total[]	= $dadosAtual['n_total_atual'];
				$vnd_tot[]	= $dadosAtual['venda_total_atual'];
			}
		}
	}
	
	$opr_codAnt = array();
	foreach ($anteriorData as $dadosAnterior) {
		foreach ($dadosAnterior as $keyAnterior => $valoresAnterior) {
			if(!in_array($dadosAnterior['operadora_codigo_anterior'],$opr_codAnt)) {
				$opr_codAnt[]	= $dadosAnterior['operadora_codigo_anterior'];
				$nome_oprAnt[]	= $dadosAnterior['operadora_nome_anterior'];
				$n_totalAnt[]	= $dadosAnterior['n_total_anterior'];
				$vnd_totAnt[]	= $dadosAnterior['venda_total_anterior'];
			}
		}
	}

//echo "n: ".count($opr_cod)."<br>";
//echo "<pre>".print_r($opr_cod, true)."</pre>";
/*
	echo "<table border='1'>\n";
	for($i=0;$i<count($opr_cod);$i++) {
		echo "<tr><td>".$opr_cod[$i]."</td> <td>".$nome_opr[$i]."</td> <td>".$n_total[$i]."</td> <td>".$vnd_tot[$i]."</td> <td>".$opr_codAnt[$i]."</td> <td>".$nome_oprAnt[$i]."</td> <td>".$n_totalAnt[$i]."</td> <td>".$vnd_totAnt[$i]."</td> </tr>\n";
	}
	echo "</table>\n";
*/
	// Ordena os dois grupos de arrays
	for($i=0;$i<count($opr_cod);$i++) {
		for($j=$i+1;$j<count($opr_cod);$j++) {
			if($vnd_tot[$j]>$vnd_tot[$i]) {
				$tmp = $opr_cod[$i];	$opr_cod[$i] = $opr_cod[$j];	$opr_cod[$j] = $tmp;
				$tmp = $nome_opr[$i];	$nome_opr[$i] = $nome_opr[$j];	$nome_opr[$j] = $tmp;
				$tmp = $n_total[$i];	$n_total[$i] = $n_total[$j];	$n_total[$j] = $tmp;
				$tmp = $vnd_tot[$i];	$vnd_tot[$i] = $vnd_tot[$j];	$vnd_tot[$j] = $tmp;

				$tmp = $opr_codAnt[$i];		$opr_codAnt[$i] = $opr_codAnt[$j];		$opr_codAnt[$j] = $tmp;
				$tmp = $nome_oprAnt[$i];	$nome_oprAnt[$i] = $nome_oprAnt[$j];	$nome_oprAnt[$j] = $tmp;
				$tmp = $n_totalAnt[$i];		$n_totalAnt[$i] = $n_totalAnt[$j];		$n_totalAnt[$j] = $tmp;
				$tmp = $vnd_totAnt[$i];		$vnd_totAnt[$i] = $vnd_totAnt[$j];		$vnd_totAnt[$j] = $tmp;

			}
		}
	}

/*
	echo "<hr><table border='1'>\n";
	for($i=0;$i<count($opr_cod);$i++) {
		echo "<tr><td>".$opr_cod[$i]."</td> <td>".$nome_opr[$i]."</td> <td>".$n_total[$i]."</td> <td>".$vnd_tot[$i]."</td> <td>".$opr_codAnt[$i]."</td> <td>".$nome_oprAnt[$i]."</td> <td>".$n_totalAnt[$i]."</td> <td>".$vnd_totAnt[$i]."</td> </tr>\n";
	}
	echo "</table>\n";
*/
//echo "<pre>".print_r($opr_cod, true)."</pre>";

// Fim

//echo '<pre>';
//print_r($atualData);
//echo '</pre><hr>';
//
//echo '<pre>';
//print_r($anteriorData);
//echo '</pre><hr>';
//
//echo '<pre>';
//print_r($vnd_tot);
//echo '</pre><hr>';
//
//echo '<pre>';
//print_r($vnd_totAnt);
//echo '</pre><hr>';

$oprcod 	= '';
$opernome 	= '';
for ($i = 0; $i <= count($opr_cod)-1; $i++) {
	$oprcod 	.= $opr_cod[$i].',';
	$opernome 	.= $nome_opr[$i].',';	
}
$oprcod 	= substr($oprcod,0,strlen($oprcod)-1);
$opernome	= substr($opernome,0,strlen($opernome)-1);

$result = array_diff($opr_cod, $opr_codAnt);
if($result) {
	echo '<br>Erro na formação dos períodos...';
	//die();
}

// Calculamos as vendas totais do periodo atual
if(!empty($vnd_tot)) {
	$vendas_total_1 = array_sum($vnd_tot);
} else {
	$flagvazio = 1;
}
//echo 'vendas_total_1: '.$vendas_total_1.'<hr>';
// fim

// Calculamos as vendas totais do periodo anterior
if(!empty($vnd_totAnt)) {
	$vendas_total_2 = array_sum($vnd_totAnt);
} else {
	$flagvazio = 1;
}
// fim


// aqui calculamos o Market Share
$ms = '';
if($chkMarketShare=='1'){
	for ($i = 0; $i <= count($opr_cod)-1; $i++) {
		$marketshare1[$i]  = ($vnd_tot[$i]/$vnd_tot[0]);
		$marketshcalcs[$i] = ($vnd_tot[$i]/$vnd_tot[0]);
		
		$ms .= number_format(($vnd_tot[$i]/$vnd_tot[0]),4,'.','').',';
	}
}
else {
	for ($i = 0; $i <= count($opr_cod)-1; $i++) {
		$marketshare1[$i]  = ($vnd_tot[$i]/$vendas_total_1);
		$marketshcalcs[$i] = ($vnd_tot[$i]/$vendas_total_1);
		
		$ms .= number_format(($vnd_tot[$i]/$vendas_total_1),4,'.','').',';
	}
}
$ms = substr($ms,0,strlen($ms)-1);
// Fim

//echo '<hr>MS: '.$ms.'<br>';

// aqui pego o xmin e xmax
	if(!empty($marketshcalcs)) {
		sort($marketshcalcs);
	} else {
		$flagvazio = 1;
	}
	
	$xmin = number_format($marketshcalcs[0],2,'.','');
	$xmax = number_format($marketshcalcs[count($marketshcalcs)-1],2,'.','');
	
	//echo 'Xmin: '.$xmin.'<br>Xmax: '.$xmax.'<hr>';
// fim

// Aqui calculamos o Growth
$gw = '';

for ($i = 0; $i <= count($opr_cod)-1; $i++) {
	$growth[] = ($vnd_tot[$i]-$vnd_totAnt[$i])/(($vnd_totAnt[$i] > 0)?$vnd_totAnt[$i]:1);
	$growthcalcs[] = ($vnd_tot[$i]-$vnd_totAnt[$i])/(($vnd_totAnt[$i] > 0)?$vnd_totAnt[$i]:1);
	
	$gw .= number_format(($vnd_tot[$i]-$vnd_totAnt[$i])/(($vnd_totAnt[$i] > 0)?$vnd_totAnt[$i]:1),4,'.','').',';
}
$gw = substr($gw,0,strlen($gw)-1);
// Fim

//echo "<pre>".print_r($growth, true)."</pre>";
//echo "<pre>".print_r($growthcalcs, true)."</pre>";
//echo "<pre>".print_r($gw, true)."</pre>";

// aqui pego o ymin e ymax
	if(!empty($growthcalcs)) {
		sort($growthcalcs);
	} else {
		$flagvazio = 1;
	}
//echo "<pre>".print_r($growthcalcs, true)."</pre>";
	
	$ymin = number_format($growthcalcs[0],2,'.','');
	$ymax = number_format($growthcalcs[count($growthcalcs)-1],2,'.','');
	
	//echo 'Ymin: '.$ymin.'<br>Ymax: '.$ymax.'<hr>';
// fim
//echo "<pre>";
//print_r($growth);
//print_r($marketshare1);
//echo "</pre>";

//Gerando as cores dos baloes
$cor_aux = array(
			'red',
			'blue', 
			'yellow',
			'brown',
			'cadetblue',
			'purple',
			'chartreuse',
			'firebrick',
			'magenta',
			'darkorange',
			'gold',
			'darkviolet',
			'red',
			'yellow',
			'tomato',
			'aquamarine',
			'darkorange',
			'chartreuse',
			'greenyellow',
			'gold',
			'magenta',
			'tomato',
			'aquamarine',
			'brown',
			'cadetblue',
			'red',
			'purple',
			'chartreuse',
			'firebrick',
			'darkviolet',
			'darkorange',
			'blue',
			'blueviolet',
			'greenyellow',
			'gold',
			'magenta',
			'red',
			'yellow',
			'tomato',
			'aquamarine',
			'firebrick',
			'darkviolet',
			'darkorange',
			'blue',
			'blueviolet',
			'greenyellow',
			'gold',
			'yellow',
			'tomato',
			'aquamarine'
			);
for ($i = 0; $i < count($opr_cod); $i++) {
	$cor[$opr_cod[$i]] = $cor_aux[$i];
}
//echo '<pre>'.print_r($cor,true).'</pre>';
//die('stop');

$flagvazio = 2;
if($flagvazio != 1) {
	?>
 <hr>
 <table width="100%" border="0" align="center" cellpadding="0" cellspacing="1"  style="font:Arial, Helvetica, sans-serif; font-size:12px; border:thin; border-color:#CCCCCC; width:100%; height:auto;">
  <tr>
   <td align="left" style="width:100px;"><br>
  <form name="index2" id="index2">
    <table border="0" align="center" cellpadding="0" cellspacing="1"  style="font:Arial, Helvetica, sans-serif; font-size:12px; border:thin; border-color:#CCCCCC; width:100px; height:auto;"> 
        <tr style="background-color:#E3E6F0; font:Arial, Helvetica, sans-serif; font-sidataze:12px;">
		  <td align="center" style="background:#CCCCCC;" class="texto">&nbsp;</td>
		  <td align="center" style="background:#CCCCCC;" class="texto">Operadoras/Games:</td>
		  <td align="center" style="background:#CCCCCC;" class="texto">Totais em Vendas (atual):</td>
		  <td align="center" style="background:#CCCCCC;" class="texto">Totais em Vendas (anterior):</td>
          <td align="center" style="background:#CCCCCC;" class="texto">Market Share:</td>
          <td align="center" style="background:#CCCCCC;" class="texto">Growth:</td>
        </tr>
		  <?php 
			$marketshare_cummul = 0;
		  for ($i = 0; $i <= count($opr_cod)-1; $i++) {
			$marketshare_cummul += $marketshare1[$i];
          ?>
          <tr class="texto">
		  <td align="center" bgcolor="<?php echo $cor[$opr_cod[$i]];?>" onClick="showdata(<?php echo $opr_cod[$i]; ?>,<?php echo number_format($growth[$i]*100,2,'.',''); ?>,<?php echo number_format($marketshare1[$i],4,'.',''); ?>,'<?php echo $nome_opr[$i]; ?>',<?php echo count($opr_cod); ?>);">&nbsp;</td>
		  <td  align="right" id="<?php echo 'item'.$opr_cod[$i]; ?>" onClick="showdata(<?php echo $opr_cod[$i]; ?>,<?php echo number_format($growth[$i]*100,2,'.',''); ?>,<?php echo number_format($marketshare1[$i],4,'.',''); ?>,'<?php echo $nome_opr[$i]; ?>',<?php echo count($opr_cod); ?>);"><nobr>&nbsp;<?php echo $nome_opr[$i]; ?>&nbsp;</nobr></td>
          <td  align="right"  id="<?php echo 'item'.$opr_cod[$i]; ?>v" onClick="showdata(<?php echo $opr_cod[$i]; ?>,<?php echo number_format($growth[$i]*100,2,'.',''); ?>,<?php echo number_format($marketshare1[$i],4,'.',''); ?>,'<?php echo $nome_opr[$i]; ?>',<?php echo count($opr_cod); ?>);"><nobr><?php echo number_format($vnd_tot[$i],2,'.',''); ?></nobr></td>
          <td  align="right" id="<?php echo 'item'.$opr_cod[$i]; ?>va" onClick="showdata(<?php echo $opr_cod[$i]; ?>,<?php echo number_format($growth[$i]*100,2,'.',''); ?>,<?php echo number_format($marketshare1[$i],4,'.',''); ?>,'<?php echo $nome_opr[$i]; ?>',<?php echo count($opr_cod); ?>);"><nobr><?php echo number_format($vnd_totAnt[$i],2,'.',''); ?></nobr></td>
		  <td  align="right" id="<?php echo 'item'.$opr_cod[$i]; ?>vms" onClick="showdata(<?php echo $opr_cod[$i]; ?>,<?php echo number_format($growth[$i]*100,2,'.',''); ?>,<?php echo number_format($marketshare1[$i],4,'.',''); ?>,'<?php echo $nome_opr[$i]; ?>',<?php echo count($opr_cod); ?>);" title="&#8721; = <?php echo number_format($marketshare_cummul*100,2,'.','')." % com ".($i+1)." opr".(($i>0)?"s":"")."/game".(($i>0)?"s":"")."" ?>"><nobr>&nbsp;&nbsp;&nbsp;<?php echo number_format($marketshare1[$i]*100,2,'.',''); ?> %</nobr></td>
          <td  align="right" id="<?php echo 'item'.$opr_cod[$i]; ?>vgw" onClick="showdata(<?php echo $opr_cod[$i]; ?>,<?php echo number_format($growth[$i]*100,2,'.',''); ?>,<?php echo number_format($marketshare1[$i],4,'.',''); ?>,'<?php echo $nome_opr[$i]; ?>',<?php echo count($opr_cod); ?>);"><nobr><?php echo number_format($growth[$i],2,'.','')*100; ?> %</nobr></td>
		  </tr>
          <?php
          }
		  ?>
      <tr style="background-color:#FFF; font:Arial, Helvetica, sans-serif; font-size:10px;">
          <td align="center" style="background:#CCCCCC;" class="texto">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
		  <td align="right" style="background:#CCCCCC;" class="texto">TOTAL: </td><td align="right" style="background:#CCCCCC;" class="texto"><?php echo number_format(array_sum($vnd_tot),2,'.',''); ?></nobr></td>
          <?php
          $venda_total = number_format(array_sum($vnd_tot),2,'.','');
          ?>         
          <td align="right" style="background:#CCCCCC;" class="texto"><nobr>&nbsp;
          <?php echo number_format(array_sum($vnd_totAnt),2,'.',''); ?></nobr></td>
		  <td></td>
          <td></td>
          <td></td>
      </tr>
    </table>
</form>
</td>
<td align="center">
&nbsp;</td>
<td align="left" valign="top">
	<div id="dados" style="position:relative; top:60px; left: 560px; width:100px; height:1px;"></div>
    <div id="explicacao" style="position:relative; top:170px; left: 560px; width:170px; height:1px;" class="texto" align="justify">
	Este gr&aacute;fico tem o objetivo de exibir a proporcionalidade de participa&ccedil;&atilde;o dos produtos em fun&ccedil;&atilde;o do crescimento destes.<br><br>
	O gr&aacute;fico pode ter como base o total de vendas para compor 100% de Market Share (default), ou utilizar o produto de maior venda como base do Market Share (basta marcar o checkbox nos filtros).  
	</div>
	<br>
    <?php
	include('graphbcg.php');
} else {
	echo '<hr>Sem resultados na busca.<br>';
}
?>
</td>
</tr>
</table>
<script language="javascript" src="js/jquery-1.6.1.js"></script>
<script language="javascript" src="js/jquery.qtip-1.0.0-rc3.js"></script>
<script type="text/javascript">
// Create the tooltips only when document ready
$(document).ready(function() {
   // Use the each() method to gain access to each elements attributes
   $('area').each(function() {
	$(this).qtip(
	  {
		content: $(this).attr('alt'), // Use the ALT attribute of the area map
		style: {
			name: 'red', // Give it the preset dark style
			border: {
			   width: 0, 
			   radius: 4
			},
			tip: true // Apply a tip at the default tooltip corner
		 }//,
		 //hide: { when: 'inactive', delay: 1000 }
	  });
   });
});
</script>    
<script>
function number_format( number, decimals, dec_point, thousands_sep ) {
	// http://kevin.vanzonneveld.net
	// +   original by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
	// +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	// +     bugfix by: Michael White (http://crestidg.com)
	// +     bugfix by: Benjamin Lupton
	// +     bugfix by: Allan Jensen (http://www.winternet.no)
	// +    revised by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)    
	// *     example 1: number_format(1234.5678, 2, '.', '');
	// *     returns 1: 1234.57     
 
	var n = number, c = isNaN(decimals = Math.abs(decimals)) ? 2 : decimals;
	var d = dec_point == undefined ? "," : dec_point;
	var t = thousands_sep == undefined ? "." : thousands_sep, s = n < 0 ? "-" : "";
	var i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "", j = (j = i.length) > 3 ? j % 3 : 0;
	
	return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
}

//variavel que armazena nome operadora ANTERIOR
var oprnomeant = "";

function showdata(oprcod,gwt,ms,oprnome,total_itens) {
	var valor 		= eval(oprcod);
	var ms	 		= eval(ms);
	var gw	 		= eval(gwt);
	var oprnome 	= oprnome.replace(" ","_");
	var total_itens = eval(total_itens);
	
	//DEShabilita o focus ANTERIOR para exibir o balão
	if(oprnomeant!='') {
		$("#plot_"+oprnomeant).hide();
	}
	oprnomeant = oprnome;

	$("#plot_"+oprnome).qtip(
	  {
		 content: $("#plot_"+oprnome).attr('alt'), // Use the ALT attribute of the area map
		 style: {
			name: 'red', // Give it the preset dark style
			border: {
			   width: 0, 
			   radius: 4
			},
			tip: true // Apply a tip at the default tooltip corner
		 },
		 show: {
		   when: {
			   event: 'focus' //Exibe balão quando recebe focus
			} 
		 },
		 hide: { when: { event: 'unfocus' } } //Remove balão quando perde o focus
	  });	
	
	//habilita o focus para exibir o balão
	$("#plot_"+oprnome).focus();

	gwt = number_format(gwt, 2, '.', '')*100;
	ms	= ms*100;
	ms  = number_format(ms, 2, '.', '')*1;
	var dados	= '';

//alert(ms);
	<?php
		for ($i = 0; $i <= count($opr_cod)-1; $i++) {
			?>
			if(<?php echo $opr_cod[$i];?> != valor) {
				$("#item"+<?php echo $opr_cod[$i];?>).css({"background":"#CCC"});
				$("#item"+<?php echo $opr_cod[$i];?>+"v").css({"background":"#FFF"});
				$("#item"+<?php echo $opr_cod[$i];?>+"va").css({"background":"#FFF"});
				$("#item"+<?php echo $opr_cod[$i];?>+"vms").css({"background":"#FFF"});
				$("#item"+<?php echo $opr_cod[$i];?>+"vgw").css({"background":"#FFF"});
			}
			<?php
		}
	?>    
	$("#item"+valor).css({"background":"#BF7A77"});
	$("#item"+valor+"v").css({"background":"#BF7A77"});
	$("#item"+valor+"va").css({"background":"#BF7A77"});
	$("#item"+valor+"vms").css({"background":"#BF7A77"});
	$("#item"+valor+"vgw").css({"background":"#BF7A77"});

	dados += '<table  border="1" style="font:Arial, Helvetica, sans-serif; font-size:12px; border:thin; border-color:#CCCCCC; width:100px; height:auto;">';
	dados += '  <tr bgcolor="#E3E6F0">';
	dados += '	<td align="center" class="texto">DADOS</td>';
	dados += '  </tr>';

	dados += '  <tr>';
	dados += '	<td align="center" class="texto"><nobr>'+oprnome+'</nobr></td>';
	dados += '  </tr>';
	
	dados += '  <tr>';
	dados += '	<td class="texto">id:&nbsp;'+oprcod+'</td>';
	dados += '  </tr>';

	dados += '  <tr>';
	dados += '	<td class="texto"><nobr>MShare:&nbsp;'+((ms).toFixed(2))+' %</nobr></td>';
	dados += '  </tr>';

	dados += '  <tr>';
	dados += '	<td class="texto"><nobr>Growth:&nbsp;'+(gwt/100)+' %</nobr></td>';
	dados += '  </tr>';
	dados += '</table>';
	
	$('#dados').html(dados);

}
</script>
<table border='0' cellpadding='0' cellspacing='1' width='80%' bordercolor='#cccccc' style='border-collapse:collapse;'>	
  <tr align="center"> 
	<td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td><td bgcolor="#FFFFFF" class="texto"><?php echo LANG_STATISTICS_SEARCH_MSG." ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.')." ".LANG_STATISTICS_SEARCH_MSG_UNIT ?></font></td><td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td>
  </tr>
</table>
</div>
</div>
<?php
require_once $raiz_do_projeto . "public_html/sys/includes/rodape_sys.php";
?>