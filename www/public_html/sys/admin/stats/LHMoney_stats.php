<?php 
require_once "../../../../includes/constantes.php";
require_once $raiz_do_projeto . "public_html/sys/includes/topo_sys.php";
//session_start();
if(!b_IsSysAdminStatistics_Abas()) {
    die("ATENÇÃO: Para o bom funcionamento de nosso sistema foi bloqueado o acesso a este relatório.<br>Contamos com sua compreensão.<br>Obrigado.");
}

/*	if($_SESSION["tipo_acesso_pub"]=='PU') {
		//redireciona
		$strRedirect = "/sys/admin/commerce/index.php";
		ob_end_clean();
		header("Location: " . $strRedirect);
		exit;
		?_><html><body onload="window.location='<_?=$strRedirect?>'"><_?
		exit;
		
		ob_end_flush();
	}
*/
	ob_end_flush();

//	include "../../../prepag2/commerce/includes/connect.php";
//	include "../../../incs/functions.php";
/**************************************************************************************************************
*******************************************************  A T E N Ç A O    A H H H H H H H H H H 
*******************************************************  MUDAR O INCLUDE ABAIXO TB 
***************************************************************************************************************/
require_once $raiz_do_projeto . "includes/sys/inc_stats.php";

	$time_start_stats = getmicrotime();

	$bg_col_01 = "#FFFFFF";
	$bg_col_02 = "#EEEEEE";
	$bg_col = $bg_col_01;

	if($_SESSION["tipo_acesso_pub"]=='PU') {
		$dd_operadora = $_SESSION["opr_codigo_pub"];
		if(strlen($dd_operadora)>0) { 
			$where_operadora = " and vgm_opr_codigo='".$dd_operadora."'";
		} else {
			$where_operadora = "";
		}
	}

	$dd_operadora_nome = "";
	if($dd_operadora) {
		$resopr_nome = pg_exec($connid, "select opr_nome from operadoras where (opr_status = '1') and (opr_codigo='".$dd_operadora."') order by opr_ordem");
		if($pgopr_nome = pg_fetch_array ($resopr_nome)) { 
			$dd_operadora_nome = $pgopr_nome['opr_nome'];
		} 
		$where_operadora = " vgm_opr_codigo='".$dd_operadora."'";
	}

	if($_SESSION["tipo_acesso_pub"]=='PU') {
		$sqlopr = "select opr_nome, opr_codigo from operadoras where (opr_status = '1') and (opr_codigo='".$dd_operadora."') order by opr_ordem";
	} else {
		$sqlopr = "select opr_nome, opr_codigo from operadoras where (opr_status != '0') order by opr_ordem";
	}
	$resopr = pg_exec($connid, $sqlopr);
//echo "$sqlopr<br>";

//echo "dd_operadora: ".$dd_operadora."<br>";
//echo "where_operadora: ".$where_operadora."<br>";

?>
<!doctype html public "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
    <title> <?php echo LANG_STATISTICS_TITLE_5; ?> </title>
    <link href="/includes/bootstrap/css/bootstrap.min_new.css" rel="stylesheet" type="text/css" />
    <script type="text/javascript" src="/js/jquery.js"></script>
    <script type="text/javascript" src="/includes/bootstrap/js/bootstrap.min.js"></script>
    <link href="/sys/css/css.css" rel="stylesheet" type="text/css">
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
</head>
<body>
<div class="container-fluid">
    <div class="container txt-azul-claro bg-branco txt-cinza">
        <div class="row">
            <div class="col-md-12 ">
                <strong><h3><?php echo LANG_STATISTICS_PAGE_TITLE_6; ?> <?php if(strlen($dd_operadora_nome)>0) echo "<font color='#337ab7'>".$dd_operadora_nome."</font> ";?> - <?php echo LANG_STATISTICS_PAGE_TITLE_2; ?> (<?php echo get_current_date()?>)</h3></strong>
            </div>
        </div>
        <div class="row txt-cinza espacamento">
            <div class="col-md-6">
                <span class="pull-left"><strong><?php echo LANG_POS_SEARCH_1; ?></strong></span>
            </div>
            <div class="col-md-6">
                <span class="pull-right"><a href="../commerce/index.php" class="btn btn-primary"><strong><i><?php echo LANG_BACK; ?></i></strong></a></span>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <ul class="nav nav-pills">
                    <li role="presentation"><a href="#Totalpormes"><?php echo LANG_STATISTICS_FOR_MONTH; ?></a></li>
                    <li role="presentation"><a href="#Totalpordiadasemana"><?php echo LANG_STATISTICS_FOR_WEEK_DAY; ?></a></li>
                    <li role="presentation"><a href="#Totalpordia"><?php echo LANG_STATISTICS_FOR_DAY; ?></a></li>
                    <li role="presentation"><a href="#Totais"><?php echo LANG_STATISTICS_TOTALS; ?></a></li>
                    <li role="presentation"><a href="#Totalporjogo"><?php echo LANG_STATISTICS_FOR_GAME; ?></a></li>
                    <li role="presentation"><a href="#Totalporjogonestemes"><?php echo LANG_STATISTICS_FOR_GAME_THIS_MONTH; ?></a></li>
                    <li role="presentation"><a href="#Totalporestado"><?php echo LANG_STATISTICS_FOR_STATE; ?></a></li>
                    <li role="presentation"><a href="#Totalporcidade"><?php echo LANG_STATISTICS_FOR_CITY; ?></a></li>
<?php
                if($_SESSION["tipo_acesso_pub"]!='PU') 
                {
?>
                    <li role="presentation"><a href="#Totalporlanhouse"><?php echo LANG_STATISTICS_FOR_USER; ?></a></li>
                    <li role="presentation"><a href="#TotalporUsuarioUltimoMes"><?php echo LANG_STATISTICS_FOR_LAST_MONTH; ?></a></li>
                    <li role="presentation"><a href="#TotalporUsuarioUltimaSemana"><?php echo LANG_STATISTICS_FOR_LAST_WEEK; ?></a></li>
<?php
                }
?>
                </ul>
            </div>
        </div>
        <form name="form1" method="post" action="">
        <div class="row txt-cinza top10">
            <div class="col-md-2">
                <span class="pull-right"><?php echo LANG_STATISTICS_OPERATOR; ?></span>
            </div>
            <div class="col-md-3">
<?php
        if($_SESSION["tipo_acesso_pub"]=='PU') 
        {
?>
		<span style="font-weight: bold"><?php echo $_SESSION["opr_nome"]?></span>
		<input type="hidden" name="dd_operadora" id="dd_operadora" value="<?php echo $dd_operadora?>">
<?php
        } else 
        {
?>
                <select name="dd_operadora" id="dd_operadora" class="form-control" onChange="document.form1.submit()">
                    <option value=""><?php echo LANG_POS_ALL_OPERATOR; ?></option>
<?php
            while ($pgopr = pg_fetch_array ($resopr)) 
            {
?>
                    <option value="<?php echo $pgopr['opr_codigo'] ?>" <?php if($pgopr['opr_codigo'] == $dd_operadora) echo "selected" ?>><?php echo $pgopr['opr_nome'] ?></option>
<?php
            } 
?>
                </select>
<?php
        } 
?>
            </div>
        </div>
        </form>
        <div class="row txt-cinza top10">
<?php
	

//	$days_in_month = MonthDays($iMonth, $iYear)

	$imonth = date("n"); // or any value from 1-12
	$iyear	= date("Y"); // or any value >= 1
	$days_in_month = date("t",mktime(0,0,0,$imonth,1,$iyear));
//	echo "days_in_month: $days_in_month<br>";

	// Totais de Cadastros 
	$n_cadastros = get_ncadastros("L", addWhereClause($extra_where, $where_operadora), $smode);

	// Totais de vendas
	$sql = get_sql_query("L", "totais_de_vendas", addWhereClause($extra_where, $where_operadora), $smode);
//echo "L: ".$sql."<br>";
	$total_vendas = 0;
	$n_vendas = 0;
	$vendas_estado = SQLexecuteQuery($sql);
	if($vendas_estado) {
		while ($vendas_estado_row = pg_fetch_array($vendas_estado)){
			$total_vendas = $vendas_estado_row['vendas'];
			$n_vendas = $vendas_estado_row['n'];
		}
	} else {
		echo "<tr bgcolor='".$bg_col."'><td align='center' colspan='5'><font color='#FF0000'>".LANG_NOT_FOUND_2."&nbsp;</font></td></tr>";
	}
//echo "$total_vendas em $n_vendas vendas<br>";

	// Datas Limites no BD
	$sql = get_sql_query("L", "datas_limites_no_bd", addWhereClause($extra_where, $where_operadora), $smode);
//echo "sql: $sql<br>";
	$data_min = date("Y-m-d");
	$data_max = date("Y-m-d");
	$vendas_estado = SQLexecuteQuery($sql);
	if($vendas_estado) {
		while ($vendas_estado_row = pg_fetch_array($vendas_estado)){
			$data_min = $vendas_estado_row['data_min'];
			$data_max = $vendas_estado_row['data_max'];
		}
		if(!$data_min) $data_min = date("Y-m-d");
		if(!$data_max) $data_max = date("Y-m-d");
	} else {
		echo "<tr bgcolor='".$bg_col."'><td align='center' colspan='5'><font color='#FF0000'>".LANG_NOT_FOUND_2."&nbsp;</font></td></tr>";
	}

	// Estatisticas por mês
	$sql = get_sql_query("L", "por_mes", addWhereClause($extra_where, $where_operadora), $smode);
//echo "sql: $sql<br>";
	$vendas_estado = SQLexecuteQuery($sql);
	$bg_col = $bg_col_01;
	$n_dias = pg_num_rows($vendas_estado);
	echo "<a name='Totalpormes'><table class='txt-cinza' border='1' cellpadding='0' cellspacing='1' bordercolor='#cccccc' style='border-collapse:collapse;'>";
	echo "<tr><th align='center' colspan='4'><b><font color='#337ab7'>".LANG_STATISTICS_TOTAL." ".$dd_operadora_nome." <?php echo LANG_STATISTICS_FOR_MONTH; ?> (".(($vendas_estado)?pg_num_rows($vendas_estado):"0")." ".LANG_MONTHS.")</font></b></th></tr>";
	echo "<tr bgcolor='#99CCFF'><td align='center'>".LANG_MONTH_2."</td><td align='center'>".LANG_STATISTICS_SALES_NUMBER."</td><td align='center'>".LANG_STATISTICS_SALES_VALUE." (".LANG_STATISTICS_IN." R\$)</td><td align='center'>% ".LANG_STATISTICS_IN_VALUE."</td></tr>";
	if($vendas_estado) {
		while ($vendas_estado_row = pg_fetch_array($vendas_estado)){
			$bg_col = ($bg_col==$bg_col_01)?$bg_col_02:$bg_col_01;
			echo "<tr bgcolor='".$bg_col."'><td align='center'>&nbsp;".mes_do_ano($vendas_estado_row['mes'])."&nbsp;</td><td align='center'>".$vendas_estado_row['n']."</td><td align='center'>".number_format(($vendas_estado_row['vendas']), 2, ',', '.')."</td><td align='center'>".number_format(100*($vendas_estado_row['vendas'])/(($total_vendas==0)?1:$total_vendas), 2, ',', '.')."</td></tr>";
			$previous_value = $vendas_estado_row['vendas'];
		}
	} else {
		echo "<tr bgcolor='".$bg_col."'><td align='center' colspan='5'><font color='#FF0000'>".LANG_NOT_FOUND_2."&nbsp;</font></td></tr>";
	}
	echo "</table><br>";


	// ".LANG_STATISTICS_FOR_WEEK_DAY."
	$sql = get_sql_query("L", "por_dia_da_semana", addWhereClause($extra_where, $where_operadora), $smode);
//echo "sql: $sql<br>";
	$vendas_estado = SQLexecuteQuery($sql);
	$bg_col = $bg_col_01;
	$n_dias = pg_num_rows($vendas_estado);
	echo "<a name='Totalpordiadasemana'><table class='txt-cinza' border='1' cellpadding='0' cellspacing='1' bordercolor='#cccccc' style='border-collapse:collapse;'>";
	echo "<tr><th align='center' colspan='4'><b><font color='#337ab7'>".LANG_STATISTICS_TOTAL." ".$dd_operadora_nome." ".LANG_STATISTICS_FOR_WEEK_DAY."</font></b></th></tr>";
	echo "<tr bgcolor='#99CCFF'><td align='center'>".LANG_STATISTICS_DAY_OF_THE_WEEK."</td><td align='center'>".LANG_STATISTICS_SALES_NUMBER."</td><td align='center'>".LANG_STATISTICS_SALES_VALUE." (".LANG_STATISTICS_IN." R\$)</td><td align='center'>% ".LANG_STATISTICS_IN_VALUE."</td></tr>";
	if($vendas_estado) {
		while ($vendas_estado_row = pg_fetch_array($vendas_estado)){
			$bg_col = ($bg_col==$bg_col_01)?$bg_col_02:$bg_col_01;
			echo "<tr bgcolor='".$bg_col."'><td align='center'>&nbsp;".get_day_of_week_db($vendas_estado_row['dow'])."&nbsp;</td><td align='center'>".$vendas_estado_row['n']."</td><td align='center'>".number_format(($vendas_estado_row['vendas']), 2, ',', '.')."</td><td align='center'>".number_format(100*($vendas_estado_row['vendas'])/(($total_vendas==0)?1:$total_vendas), 2, ',', '.')."</td></tr>";
			$previous_value = $vendas_estado_row['vendas'];
		}
	} else {
		echo "<tr bgcolor='".$bg_col."'><td align='center' colspan='5'><font color='#FF0000'>".LANG_NOT_FOUND_2."&nbsp;</font></td></tr>";
	}
	echo "</table><br>";

	// Por dia
	$sql = get_sql_query("L", "por_dia", addWhereClause($extra_where, $where_operadora), $smode);
//echo "sql: $sql<br>";
	$vendas_estado = SQLexecuteQuery($sql);
	$bg_col = $bg_col_01;
	$n_dias = pg_num_rows($vendas_estado);
	echo "<a name='Totalpordia'><table class='txt-cinza' border='1' cellpadding='0' cellspacing='1' bordercolor='#cccccc' style='border-collapse:collapse;'>";
	echo "<tr><th align='center' colspan='5'><b><font color='#337ab7'>".LANG_STATISTICS_TOTAL." ".$dd_operadora_nome." ".LANG_STATISTICS_FOR_DAY." (".pg_num_rows($vendas_estado)." ".LANG_DAYS.")</font></b></th></tr>";
	echo "<tr bgcolor='#99CCFF'><td align='center'>".LANG_DAY_2."</td><td align='center'>&nbsp;</td><td align='center'>".LANG_STATISTICS_SALES_NUMBER."</td><td align='center'>".LANG_STATISTICS_SALES_VALUE." (".LANG_STATISTICS_IN." R\$)</td><td align='center'>% ".LANG_STATISTICS_IN_VALUE."</td></tr>";
	if($vendas_estado) {
		while ($vendas_estado_row = pg_fetch_array($vendas_estado)){
			$bg_col = ($bg_col==$bg_col_01)?$bg_col_02:$bg_col_01;
			echo "<tr bgcolor='".$bg_col."'><td align='center'>".$vendas_estado_row['data']."</td><td align='center'>".get_day_of_week($vendas_estado_row['data'])."</td><td align='center'>".$vendas_estado_row['n']."</td><td align='center'>".number_format(($vendas_estado_row['vendas']), 2, ',', '.')."</td><td align='center'>".number_format(100*($vendas_estado_row['vendas'])/(($total_vendas==0)?1:$total_vendas), 2, ',', '.')."</td></tr>";
			$previous_value = $vendas_estado_row['vendas'];
		}
	} else {
		echo "<tr bgcolor='".$bg_col."'><td align='center' colspan='5'><font color='#FF0000'>".LANG_NOT_FOUND_2."&nbsp;</font></td></tr>";
	}
	echo "<tr bgcolor='#CCFFCC'><td align='right' colspan='2'><a name='Totais'></a><b>".LANG_STATISTICS_TOTAL."</b></td><td align='center'><b>".$n_vendas."</b></td><td align='center'><b>".number_format(($total_vendas), 2, ',', '.')."</b> <br><b>";
	if($_SESSION["tipo_acesso_pub"]=='PU') {
		echo "(EPP: ".number_format(($total_vendas*0.04), 2, ',', '.').")</b>";
	}
	echo "</td><td>&nbsp;</td></tr>";
	echo "<tr><td align='center' colspan='5'><b>".LANG_STATISTICS_AVERAGE."</b> ".LANG_STATISTICS_IN." ".$n_dias." ".LANG_DAYS.": R\$".number_format(($total_vendas/(($n_dias>0)?$n_dias:1)), 2, ',', '.')."/".LANG_DAY."&nbsp;<br>";
	if($_SESSION["tipo_acesso_pub"]=='PU') {
		echo "(EPP: R\$".number_format(($total_vendas*0.04/(($n_dias>0)?$n_dias:1)), 2, ',', '.')."/".LANG_DAY.")<br>";
		echo "Projeção EPP $days_in_month dias: R\$".number_format(($days_in_month*$total_vendas*0.04/(($n_dias>0)?$n_dias:1)), 2, ',', '.');
	} else {
		echo "Projeção $days_in_month dias: R\$".number_format(($days_in_month*$total_vendas/(($n_dias>0)?$n_dias:1)), 2, ',', '.');
	}
	echo "&nbsp;</td></tr>";
	echo "</table><br>";

	// ".LANG_STATISTICS_FOR_GAME."
	$sql = get_sql_query("L", "por_publisher", addWhereClause($extra_where, $where_operadora), $smode);
//echo "sql: $sql<br>";
	$vendas_estado = SQLexecuteQuery($sql);
	$bg_col = $bg_col_01;
	$n_dias = pg_num_rows($vendas_estado);
	echo "<a name='Totalporjogo'><table class='txt-cinza' border='1' cellpadding='0' cellspacing='1' bordercolor='#cccccc' style='border-collapse:collapse;'>";
	echo "<tr><th align='center' colspan='4'><b><font color='#337ab7'>".LANG_STATISTICS_TOTAL." ".$dd_operadora_nome." ".LANG_STATISTICS_FOR_GAME." (".pg_num_rows($vendas_estado)." ".LANG_STATISTICS_GAME_1.") [".date("d/M/Y",strtotime($data_min)).", ".date("d/M/Y",strtotime($data_max))."]</font></b></th></tr>";
	echo "<tr bgcolor='#99CCFF'><td align='center'>".LANG_STATISTICS_GAME."</td><td align='center'>".LANG_STATISTICS_SALES_NUMBER."</td><td align='center'>".LANG_STATISTICS_SALES_VALUE." (".LANG_STATISTICS_IN." R\$)</td><td align='center'>% ".LANG_STATISTICS_IN_VALUE."</td></tr>";
	if($vendas_estado) {
		while ($vendas_estado_row = pg_fetch_array($vendas_estado)){
			$bg_col = ($bg_col==$bg_col_01)?$bg_col_02:$bg_col_01;
			$nome_jogo = $vendas_estado_row['ve_jogo'];
			echo "<tr bgcolor='".$bg_col."'><td align='center'>".$nome_jogo."</td><td align='center'>".$vendas_estado_row['n']."</td><td align='center'>".number_format(($vendas_estado_row['vendas']), 2, ',', '.')."</td><td align='center'>".number_format(100*($vendas_estado_row['vendas'])/(($total_vendas==0)?1:$total_vendas), 2, ',', '.')."</td></tr>";
			$previous_value = $vendas_estado_row['vendas'];
		}
	} else {
		echo "<tr bgcolor='".$bg_col."'><td align='center' colspan='5'><font color='#FF0000'>".LANG_NOT_FOUND_2."&nbsp;</font></td></tr>";
	}
	echo "</table><br>";


	// ".LANG_STATISTICS_FOR_GAME."
	$thismonth = mktime(0, 0, 0, date("m"), 1, date("Y")); 
	$extra_where = " (vg.vg_data_inclusao>='".date("Y-m-d H:i:s", $thismonth)."') ";
	$sql = get_sql_query("L", "por_publisher", addWhereClause($extra_where, $where_operadora), $smode);
	$vendas_estado = SQLexecuteQuery($sql);
	$extra_where = "";
	$bg_col = $bg_col_01;
	$n_dias = pg_num_rows($vendas_estado);
	echo "<a name='Totalporjogonestemes'><table class='txt-cinza' border='1' cellpadding='0' cellspacing='1' bordercolor='#cccccc' style='border-collapse:collapse;'>";
	echo "<tr><th align='center' colspan='3'><b><font color='#337ab7'>".LANG_STATISTICS_TOTAL." ".$dd_operadora_nome." ".LANG_STATISTICS_FOR_GAME." (".pg_num_rows($vendas_estado)." ".LANG_STATISTICS_GAME_1.") ".LANG_STATISTICS_THIS_MONTH."</font></b></th></tr>";
	echo "<tr bgcolor='#99CCFF'><td align='center'>".LANG_STATISTICS_GAME."</td><td align='center'>".LANG_STATISTICS_SALES_NUMBER."</td><td align='center'>".LANG_STATISTICS_SALES_VALUE." (".LANG_STATISTICS_IN." R\$)</td></tr>";
	if($vendas_estado) {
		$valtmp = 0;
		$ntmp = 0;
		while ($vendas_estado_row = pg_fetch_array($vendas_estado)){
			$bg_col = ($bg_col==$bg_col_01)?$bg_col_02:$bg_col_01;
			$nome_jogo = $vendas_estado_row['ve_jogo'];
			echo "<tr bgcolor='".$bg_col."'><td align='center'>".$nome_jogo."</td><td align='center'>".$vendas_estado_row['n']."</td><td align='center'>".number_format(($vendas_estado_row['vendas']), 2, ',', '.')."</td></tr>";
			$previous_value = $vendas_estado_row['vendas'];
			$valtmp += $vendas_estado_row['vendas'];
			$ntmp += $vendas_estado_row['n'];
		}
		echo "<tr bgcolor='#FFFFCC'><td align='center'><b>".LANG_STATISTICS_TOTAL."</b></td><td align='center'><b>".$ntmp."</b></td><td align='center'><b>".number_format(($valtmp), 2, ',', '.')."</b></td></tr>";
	} else {
		echo "<tr bgcolor='".$bg_col."'><td align='center' colspan='5'><font color='#FF0000'>".LANG_NOT_FOUND_2."&nbsp;</font></td></tr>";
	}
	echo "</table><br>";


	// ".LANG_STATISTICS_FOR_STATE."
	$sql = get_sql_query("L", "por_estado", addWhereClause($extra_where, $where_operadora), $smode);
//echo "sql: $sql<br>";
	$vendas_estado = SQLexecuteQuery($sql);
	$previous_value = -1;
	$bg_col = $bg_col_01;
	echo "<a name='Totalporestado'><table class='txt-cinza' border='1' cellpadding='0' cellspacing='1' bordercolor='#cccccc' style='border-collapse:collapse;'>";
	echo "<tr><th align='center' colspan='4'><b><font color='#337ab7'>".LANG_STATISTICS_TOTAL." ".$dd_operadora_nome." ".LANG_STATISTICS_FOR_STATE." (".pg_num_rows($vendas_estado)." ".LANG_STATISTICS_STATES.") [".date("d/M/Y",strtotime($data_min)).", ".date("d/M/Y",strtotime($data_max))."]</font></b></th></tr>";
	echo "<tr bgcolor='#99CCFF'><td align='center'>".LANG_POS_STATE."</td><td align='center'>".LANG_STATISTICS_SALES_NUMBER."</td><td align='center'>".LANG_STATISTICS_SALES_VALUE." (".LANG_STATISTICS_IN." R\$)</td><td align='center'>% ".LANG_STATISTICS_IN_VALUE."</td></tr>";
	if($vendas_estado) {
		while ($vendas_estado_row = pg_fetch_array($vendas_estado)){
			$bg_col = ($previous_value!=$vendas_estado_row['vendas'])?(($bg_col==$bg_col_01)?$bg_col_02:$bg_col_01):$bg_col;
			echo "<tr bgcolor='".$bg_col."'><td align='center'>".$vendas_estado_row['ve_estado']."</td><td align='center'>".$vendas_estado_row['n']."</td><td align='center'>".number_format(($vendas_estado_row['vendas']), 2, ',', '.')."</td><td align='center'>".number_format(100*($vendas_estado_row['vendas'])/(($total_vendas==0)?1:$total_vendas), 2, ',', '.')."</td></tr>";
			$previous_value = $vendas_estado_row['vendas'];
		}
	} else {
		echo "<tr bgcolor='".$bg_col."'><td align='center' colspan='5'><font color='#FF0000'>".LANG_NOT_FOUND_2."&nbsp;</font></td></tr>";
	}
	echo "</table><br>";

	// Por Cidade
	$sql = get_sql_query("L", "por_cidade", addWhereClause($extra_where, $where_operadora), $smode);
//echo "sql: $sql<br>";
	$vendas_estado = SQLexecuteQuery($sql);
	$previous_value = -1;
	$bg_col = $bg_col_01;
	echo "<a name='Totalporcidade'><table class='txt-cinza' border='1' cellpadding='0' cellspacing='1' bordercolor='#cccccc' style='border-collapse:collapse;'>";
	echo "<tr><th align='center' colspan='5'><b><font color='#337ab7'>".LANG_STATISTICS_TOTAL." ".$dd_operadora_nome." ".LANG_STATISTICS_FOR_CITY." (".pg_num_rows($vendas_estado)." ".LANG_STATISTICS_CITIES.") [".date("d/M/Y",strtotime($data_min)).", ".date("d/M/Y",strtotime($data_max))."]</font></b></th></tr>";
	echo "<tr bgcolor='#99CCFF'><td align='center'>".LANG_POS_CITY."</td><td align='center'>".LANG_POS_STATE."</td><td align='center'>".LANG_STATISTICS_SALES_NUMBER."</td><td align='center'>".LANG_STATISTICS_SALES_VALUE." (".LANG_STATISTICS_IN." R\$)</td><td align='center'>% ".LANG_STATISTICS_IN_VALUE."</td></tr>";
	if($vendas_estado) {
		while ($vendas_estado_row = pg_fetch_array($vendas_estado)){
			$bg_col = ($previous_value!=$vendas_estado_row['vendas'])?(($bg_col==$bg_col_01)?$bg_col_02:$bg_col_01):$bg_col;
			echo "<tr bgcolor='".$bg_col."'><td align='left'>".$vendas_estado_row['ve_cidade']."</td><td align='center'>".$vendas_estado_row['ve_estado']."</td><td align='center'>".$vendas_estado_row['n']."</td><td align='center'>".number_format(($vendas_estado_row['vendas']), 2, ',', '.')."</td><td align='center'>".number_format(100*($vendas_estado_row['vendas'])/(($total_vendas==0)?1:$total_vendas), 2, ',', '.')."</td></tr>";
			$previous_value = $vendas_estado_row['vendas'];
		}
	} else {
		echo "<tr bgcolor='".$bg_col."'><td align='center' colspan='5'><font color='#FF0000'>".LANG_NOT_FOUND_2."&nbsp;</font></td></tr>";
	}
	echo "</table><br>";
	
	$time_start_stats2 = getmicrotime();

	if($_SESSION["tipo_acesso_pub"]!='PU') {
		// ".LANG_STATISTICS_FOR_USER."
		$sql = get_sql_query("L", "por_usuario", addWhereClause($extra_where, $where_operadora), $smode);
		$vendas_estado = SQLexecuteQuery($sql);
//echo "<b>sql</b>: ".str_replace("\n","<br>\n",$sql)."<br>";
		$previous_value = -1;
		$bg_col = $bg_col_01;
		echo "<a name='Totalporlanhouse'><table class='txt-cinza' border='1' cellpadding='0' cellspacing='1' bordercolor='#cccccc' style='border-collapse:collapse;'>";
		echo "<tr><th align='center' colspan='7'><b><font color='#337ab7'>".LANG_STATISTICS_TOTAL." ".$dd_operadora_nome." ".LANG_STATISTICS_FOR_USER." (".pg_num_rows($vendas_estado)." lanhouses, ".number_format((100*(($vendas_estado)?pg_num_rows($vendas_estado):"0")/(($n_cadastros>0)?$n_cadastros:1)), 2, ',', '.')."% ".LANG_STATISTICS_OF_REGISTER.") [".date("d/M/Y",strtotime($data_min)).", ".date("d/M/Y",strtotime($data_max))."]</font></b></th></tr>";
		echo "<tr bgcolor='#99CCFF'><td align='center'>LanHouse</td><td align='center'>1<sup>".LANG_TO."</sup>-".LANG_STATISTICS_LAST_SALES."</td><td align='center'>".LANG_POS_CITY."</td><td align='center'>UF</td><td align='center'>".LANG_STATISTICS_NUMBER_OF."<br>".LANG_STATISTICS_SALES_2."</td><td align='center'>". LANG_STATISTICS_SALES_2."<br>(".LANG_STATISTICS_IN." R\$)</td><td align='center'>%</td></tr>";
		if($vendas_estado) {
			$i = 0;
			$a_vendas_ultimo_mes = array();
			while ($vendas_estado_row = pg_fetch_array($vendas_estado)){
				$a_vendas_ultimo_mes[$i++] = $vendas_estado_row;
				$bg_col = ($previous_value!=$vendas_estado_row['vendas'])?(($bg_col==$bg_col_01)?$bg_col_02:$bg_col_01):$bg_col;
				echo "<tr bgcolor='".$bg_col."'><td align='left'".(($vendas_estado_row['ug_contatada_ultimo_mes']=="1")?" style='color:#6600CC' title='Contatada no último mês'":"").">".$vendas_estado_row['ve_nome'].(($vendas_estado_row['ug_contatada_ultimo_mes']=="1")?" (CONTATO) ":"")."</td><td align='center'> ".get_delay_alert_live($vendas_estado_row['primeira_venda'], $vendas_estado_row['ultima_venda'])." </td><td align='center'>".$vendas_estado_row['ve_cidade']."</td><td align='center'>".$vendas_estado_row['ve_estado']."</td><td align='center'>".$vendas_estado_row['n']."</td><td align='center'>".number_format(($vendas_estado_row['vendas']), 2, ',', '.')."</td><td align='center'>".number_format(100*($vendas_estado_row['vendas'])/(($total_vendas==0)?1:$total_vendas), 2, ',', '.')."</td></tr>";
				$previous_value = $vendas_estado_row['vendas'];
			}
		} else {
			echo "<tr bgcolor='".$bg_col."'><td align='center' colspan='7'><font color='#FF0000'>".LANG_NOT_FOUND_2."&nbsp;</font></td></tr>";
		}
		echo "</table><br>";
 
		// Totais de vendas Mês
		$previousmonth = mktime(0, 0, 0, date("m"), date("d")-$days_in_month, date("Y"));
		$extra_where = " (vg.vg_data_inclusao>='".date("Y-m-d H:i:s", $previousmonth)."') ";
		$sql = get_sql_query("L", "totais_de_vendas", addWhereClause($extra_where, $where_operadora), $smode);
		$total_vendas = 0;
		$n_vendas = 0;
		$vendas_estado = SQLexecuteQuery($sql);
		if($vendas_estado) {
			while ($vendas_estado_row = pg_fetch_array($vendas_estado)){
				$total_vendas = $vendas_estado_row['vendas'];
				$n_vendas = $vendas_estado_row['n'];
			}
		} else {
			echo "<tr bgcolor='".$bg_col."'><td align='center' colspan='5'><font color='#FF0000'>".LANG_NOT_FOUND_2."&nbsp;</font></td></tr>";
		}

		// ".LANG_STATISTICS_FOR_USER." último mês
		$sql = get_sql_query("L", "por_usuario", addWhereClause($extra_where, $where_operadora), $smode);
//echo "sql: $sql<br>";
		$vendas_estado = SQLexecuteQuery($sql);
		$previous_value = -1;
		$bg_col = $bg_col_01;
		$total_vendas_mes = 0;
		$n_vendas_mes = 0;
		echo "<a name='TotalporUsuarioUltimoMes'><table class='txt-cinza' border='1' cellpadding='0' cellspacing='1' bordercolor='#cccccc' style='border-collapse:collapse;'>";
		echo "<tr><th align='center' colspan='7'><b><font color='#337ab7'>".LANG_STATISTICS_TOTAL." ".$dd_operadora_nome." ".LANG_STATISTICS_FOR_USER." (".(($vendas_estado)?pg_num_rows($vendas_estado):"0")." ".LANG_STATISTICS_USERS.")  (<font color='#FF0000'>".LANG_STATISTICS_LAST_MONTH_SINCE." ".date("d/M/Y",$previousmonth)."</font>)</font></b></th></tr>";
		echo "<tr bgcolor='#99CCFF'><td align='center'>LanHouse</td><td align='center'>&nbsp;Pos.&nbsp;</td><td align='center'>".LANG_POS_CITY."</td><td align='center'>".LANG_POS_STATE."</td><td align='center'>".LANG_STATISTICS_SALES_NUMBER."</td><td align='center'>".LANG_STATISTICS_SALES_VALUE."  (".LANG_STATISTICS_IN." R\$)</td><td align='center'>% ".LANG_STATISTICS_IN_VALUE."</td></tr>";//<td align='center'>E-Mail</td>

		$s_total_mes_contatada = "<a name='TotalporUsuarioUltimoMesContatada'><table class='txt-cinza' border='1' cellpadding='0' cellspacing='1' bordercolor='#cccccc' style='border-collapse:collapse;'>";
		$s_total_mes_contatada .= "<tr><th align='center' colspan='7'><b><font color='#337ab7'>".LANG_STATISTICS_TOTAL." CONTATADAS ".$dd_operadora_nome." ".LANG_STATISTICS_FOR_USER." (<font color='#FF0000'>".LANG_STATISTICS_LAST_MONTH_SINCE." ".date("d/M/Y",$previousmonth)."</font>)</font></b></th></tr>";
		$s_total_mes_contatada .= "<tr bgcolor='#99CCFF'><td align='center'>LanHouse</td><td align='center'>".LANG_POS_CITY."</td><td align='center'>".LANG_POS_STATE."</td><td align='center'>".LANG_STATISTICS_SALES_NUMBER."</td><td align='center'>".LANG_STATISTICS_SALES_VALUE."  (".LANG_STATISTICS_IN." R\$)</td></tr>";
		$total_vendas_mes_contatada = 0;
		$n_vendas_mes_contatada = 0;
		 
		if($vendas_estado) {
			$iorder = 0;
			while ($vendas_estado_row = pg_fetch_array($vendas_estado)){
				$bg_col = ($previous_value!=$vendas_estado_row['vendas'])?(($bg_col==$bg_col_01)?$bg_col_02:$bg_col_01):$bg_col;
				$ipos = getPositionInArray("M", $vendas_estado_row['ve_nome'], $iorder++, $a_vendas_ultimo_mes);
				$sarrow_alt = (($ipos>0)?"Up":(($ipos==0)?"Equal":"Down"));
				$sarrow = "<img src='/sys/imagens/".(($ipos>0)?"arrow_up_2.gif":(($ipos==0)?"equal.gif":"arrow_down_2.gif"))."' width='12' height='12' border='0' alt='$sarrow_alt'>";

				echo "<tr bgcolor='".$bg_col."'><td align='left'".(($vendas_estado_row['ug_contatada_ultimo_mes']=="1")?" style='color:#6600CC' title='Contatada no último mês'":"").">".$vendas_estado_row['ve_nome'].(($vendas_estado_row['ug_contatada_ultimo_mes']=="1")?" (CONTATO) ":"")."</td><td align='center'>".$sarrow.(($ipos>0)?"+":(($ipos==0)?"&nbsp;":"")).$ipos."</td><td align='center'>".$vendas_estado_row['ve_cidade']."</td><td align='center'>".$vendas_estado_row['ve_estado']."</td><td align='center'>".$vendas_estado_row['n']."</td><td align='center'>".number_format(($vendas_estado_row['vendas']), 2, ',', '.')."</td><td align='center'>".number_format(100*($vendas_estado_row['vendas'])/(($total_vendas==0)?1:$total_vendas), 2, ',', '.')."</td></tr>"; //<td align='center'>".$vendas_estado_row['ug_email']."</td> coluna de e-mail
				if($vendas_estado_row['ug_contatada_ultimo_mes']=="1") {
					$s_total_mes_contatada .= "<tr bgcolor='".$bg_col."'><td align='left'>".$vendas_estado_row['ve_nome']."</td><td align='center'>".$vendas_estado_row['ve_cidade']."</td><td align='center'>".$vendas_estado_row['ve_estado']."</td><td align='center'>".$vendas_estado_row['n']."</td><td align='center'>".number_format(($vendas_estado_row['vendas']), 2, ',', '.')."</td></tr>";
					$total_vendas_mes_contatada += $vendas_estado_row['vendas'];
					$n_vendas_mes_contatada += $vendas_estado_row['n'];
				}
				$previous_value = $vendas_estado_row['vendas'];
				$total_vendas_mes += $vendas_estado_row['vendas'];
				$n_vendas_mes += $vendas_estado_row['n'];
			}
		} else {
			echo "<tr bgcolor='".$bg_col."'><td align='center' colspan='5'><font color='#FF0000'>".LANG_NOT_FOUND_2."&nbsp;</font></td></tr>";
		}
		echo "<tr bgcolor='#CCFFCC'><td align='right' colspan='4'><b>Total</b>&nbsp;</td><td align='center'>".$n_vendas_mes."</td><td align='center'>".number_format(($total_vendas_mes), 2, ',', '.')."</td><td align='center'>&nbsp;</td></tr>";
		echo "</table><br>";
		$s_total_mes_contatada .= "<tr bgcolor='#CCFFCC'><td align='right' colspan='3'><b>Total</b>&nbsp;</td><td align='center'>".$n_vendas_mes_contatada."</td><td align='center'>".number_format(($total_vendas_mes_contatada), 2, ',', '.')."</td></tr>";
		$s_total_mes_contatada .= "</table><br>";
/*
?>
<tr> 
	<td colspan="20" bgcolor="#FFFFFF" class="texto"><?php echo LANG_STATISTICS_SEARCH_MSG." ".number_format(getmicrotime() - $time_start_stats2, 2, '.', '.')." ".LANG_STATISTICS_SEARCH_MSG_UNIT; ?></font></td>
</tr>
<?php
*/
		// Totais de vendas Semana
		$previousweek = mktime(0, 0, 0, date("m"), date("d")-7, date("Y"));
		$extra_where = " (vg.vg_data_inclusao>='".date("Y-m-d H:i:s", $previousweek)."') ";
		$sql = get_sql_query("L", "totais_de_vendas", addWhereClause($extra_where, $where_operadora), $smode);
		$total_vendas = 0;
		$n_vendas = 0;
		$vendas_estado = SQLexecuteQuery($sql);
		if($vendas_estado) {
			while ($vendas_estado_row = pg_fetch_array($vendas_estado)){
				$total_vendas = $vendas_estado_row['vendas'];
				$n_vendas = $vendas_estado_row['n'];
			}
		} else {
			echo "<tr bgcolor='".$bg_col."'><td align='center' colspan='5'><font color='#FF0000'>".LANG_NOT_FOUND_2."&nbsp;</font></td></tr>";
		}

		// ".LANG_STATISTICS_FOR_USER." última semana
		$sql = get_sql_query("L", "por_usuario", addWhereClause($extra_where, $where_operadora), $smode);
//echo "sql: $sql<br>";
		$vendas_estado = SQLexecuteQuery($sql);
		$previous_value = -1;
		$bg_col = $bg_col_01;
		$total_vendas_sem = 0;
		$n_vendas_sem = 0;
		echo "<a name='TotalporUsuarioUltimaSemana'><table class='txt-cinza' border='1' cellpadding='0' cellspacing='1' bordercolor='#cccccc' style='border-collapse:collapse;'>";
		echo "<tr><th align='center' colspan='7'><b><font color='#337ab7'>".LANG_STATISTICS_TOTAL." ".$dd_operadora_nome." ".LANG_STATISTICS_FOR_USER." (".(($vendas_estado)?pg_num_rows($vendas_estado):"0")." ".LANG_STATISTICS_USERS.")  (<font color='#FF0000'>".LANG_STATISTICS_LAST_WEEK_SINCE." ".date("d/M/Y",$previousweek)."</font>)</font></b></th></tr>";
		echo "<tr bgcolor='#99CCFF'><td align='center'>LanHouse</td><td align='center'>&nbsp;Pos.&nbsp;</td><td align='center'>".LANG_POS_CITY."</td><td align='center'>".LANG_POS_STATE."</td><td align='center'>".LANG_STATISTICS_SALES_NUMBER."</td><td align='center'>".LANG_STATISTICS_SALES_VALUE."  (".LANG_STATISTICS_IN." R\$)</td><td align='center'>% ".LANG_STATISTICS_IN_VALUE."</td></tr>";

		$s_total_semana_contatada = "<a name='TotalporUsuarioUltimaSemanaContatada'><table class='txt-cinza' border='1' cellpadding='0' cellspacing='1' bordercolor='#cccccc' style='border-collapse:collapse;'>";
		$s_total_semana_contatada .= "<tr><th align='center' colspan='7'><b><font color='#337ab7'>".LANG_STATISTICS_TOTAL." CONTATADA ".$dd_operadora_nome." ".LANG_STATISTICS_FOR_USER." (".(($vendas_estado)?pg_num_rows($vendas_estado):"0")." ".LANG_STATISTICS_USERS.")  (<font color='#FF0000'>".LANG_STATISTICS_LAST_WEEK_SINCE." ".date("d/M/Y",$previousweek)."</font>)</font></b></th></tr>";
		$s_total_semana_contatada .= "<tr bgcolor='#99CCFF'><td align='center'>LanHouse</td><td align='center'>".LANG_POS_CITY."</td><td align='center'>".LANG_POS_STATE."</td><td align='center'>".LANG_STATISTICS_SALES_NUMBER."</td><td align='center'>".LANG_STATISTICS_SALES_VALUE."  (".LANG_STATISTICS_IN." R\$)</td></tr>";
		$total_vendas_sem_contatada = 0;
		$n_vendas_sem_contatada = 0;

		if($vendas_estado) {
			$iorder = 0;
			while ($vendas_estado_row = pg_fetch_array($vendas_estado)){
				$bg_col = ($previous_value!=$vendas_estado_row['vendas'])?(($bg_col==$bg_col_01)?$bg_col_02:$bg_col_01):$bg_col;
				$ipos = getPositionInArray("M", $vendas_estado_row['ve_nome'], $iorder++, $a_vendas_ultimo_mes);
				$sarrow_alt = (($ipos>0)?"Up":(($ipos==0)?"Equal":"Down"));
				$sarrow = "<img src='/sys/imagens/".(($ipos>0)?"arrow_up_2.gif":(($ipos==0)?"equal.gif":"arrow_down_2.gif"))."' width='12' height='12' border='0' alt='$sarrow_alt'>";

				echo "<tr bgcolor='".$bg_col."'".(($vendas_estado_row['ug_contatada_ultimo_mes']=="1")?" style='color:#6600CC' title='Contatada no último mês'":"")."><td align='left'>".$vendas_estado_row['ve_nome']."</td><td align='center'>".$sarrow.(($ipos>0)?"+":(($ipos==0)?"&nbsp;":"")).$ipos."</td><td align='center'>".$vendas_estado_row['ve_cidade']."</td><td align='center'>".$vendas_estado_row['ve_estado']."</td><td align='center'>".$vendas_estado_row['n']."</td><td align='center'>".number_format(($vendas_estado_row['vendas']), 2, ',', '.')."</td><td align='center'>".number_format(100*($vendas_estado_row['vendas'])/(($total_vendas==0)?1:$total_vendas), 2, ',', '.')."</td></tr>";
				if($vendas_estado_row['ug_contatada_ultimo_mes']=="1") {
					$s_total_semana_contatada .= "<tr><td align='left'>".$vendas_estado_row['ve_nome']."</td><td align='center'>".$vendas_estado_row['ve_cidade']."</td><td align='center'>".$vendas_estado_row['ve_estado']."</td><td align='center'>".$vendas_estado_row['n']."</td><td align='center'>".number_format(($vendas_estado_row['vendas']), 2, ',', '.')."</td></tr>";
					$total_vendas_sem_contatada += $vendas_estado_row['vendas'];
					$n_vendas_sem_contatada += $vendas_estado_row['n'];
				}
				$previous_value = $vendas_estado_row['vendas'];
				$total_vendas_sem += $vendas_estado_row['vendas'];
				$n_vendas_sem += $vendas_estado_row['n'];
			}
		} else {
			echo "<tr bgcolor='".$bg_col."'><td align='center' colspan='5'><font color='#FF0000'>".LANG_NOT_FOUND_2."&nbsp;</font></td></tr>";
		}
		echo "<tr bgcolor='#CCFFCC'><td align='right' colspan='4'><b>".LANG_STATISTICS_TOTAL."</b>&nbsp;</td><td align='center'>".$n_vendas_sem."</td><td align='center'>".number_format(($total_vendas_sem), 2, ',', '.')."</td><td align='center'>&nbsp;</td></tr>";
		echo "</table><br>";
		$s_total_semana_contatada .= "<tr bgcolor='#CCFFCC'><td align='right' colspan='3'><b>".LANG_STATISTICS_TOTAL."</b>&nbsp;</td><td align='center'>".$n_vendas_sem_contatada."</td><td align='center'>".number_format(($total_vendas_sem_contatada), 2, ',', '.')."</td></tr>";
		$s_total_semana_contatada .= "</table><br>";

	}
	echo "</table><br>";

//	echo "previousmonth: ".date("Y-M-d H:i:s",$previousmonth).", days_in_month: $days_in_month<br>";

	echo "data: ".date("Y-M-d H:i:s")."<br>";
	// Vendas último mês de lans contatadas
	echo $s_total_mes_contatada."<br>";

	// Vendas última semana de lans contatadas
	echo $s_total_semana_contatada;

?>
            <table class='txt-cinza' border='0' cellpadding='0' cellspacing='1' bordercolor='#cccccc' style='border-collapse:collapse;'>	
              <tr> 
                    <td colspan="20" bgcolor="#FFFFFF" class="texto"><?php echo LANG_STATISTICS_SEARCH_MSG." ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.')." ".LANG_STATISTICS_SEARCH_MSG_UNIT; ?></font></td>
              </tr>
            </table>
        </div>
    </div>
</div>
   <?php require_once $raiz_do_projeto . "public_html/sys/includes/rodape_sys.php"; ?>
</body>
</html>

