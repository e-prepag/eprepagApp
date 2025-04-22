<?php
require_once "../../../../includes/constantes.php";
require_once $raiz_do_projeto . "public_html/sys/includes/topo_sys.php";
require_once $raiz_do_projeto . "public_html/sys/includes/gamer/inc_pub_access.php";

//	include "../incs/clshighlightSQL.php";
//session_start();

	if($_SESSION["tipo_acesso_pub"]=='PU') {
		//redireciona
		$strRedirect = "/sys/admin/commerce/index.php";
		ob_end_clean();
		header("Location: " . $strRedirect);
		exit;
		?><html><body onLoad="window.location='<?php echo $strRedirect?>'"><?php
		exit;
		
		ob_end_flush();
	}


//	include "../../../prepag2/commerce/includes/connect.php";
//	include "../../../incs/functions.php";

require_once $raiz_do_projeto . "includes/sys/inc_stats.php";

$time_start_stats = getmicrotime();
?>
<!doctype html public "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title> <?php echo LANG_COMMISSIONS_TITLE; ?><?php if(strlen($_SESSION["opr_nome"])>0) echo " (".$_SESSION["opr_nome"].") ";?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <link href="/includes/bootstrap/css/bootstrap.min_new.css" rel="stylesheet" type="text/css" />
    <script type="text/javascript" src="/js/jquery.js"></script>
    <script type="text/javascript" src="/includes/bootstrap/js/bootstrap.min.js"></script>
    <link href="/sys/css/css.css" rel="stylesheet" type="text/css">

</head>
<body class="txt-cinza fontsize-p">
    <div class="container-fluid">
    <div class="container txt-azul-claro bg-branco txt-cinza">
        <div class="row">
            <div class="col-md-12 ">
                <strong><h3><span class="txt-azul-claro"><?php echo LANG_COMMISSIONS_PAGE_TITLE; ?></span><?php echo $dd_operadora_nome;?> <?php echo LANG_STATISTICS_FOR_MONTH; ?><?php if(strlen($_SESSION["opr_nome"])>0) echo "<font color='#66CC33'>".$_SESSION["opr_nome"]."</font> ";?>- <?php echo LANG_COMMISSIONS_PAGE_TITLE_1; ?> (<?php echo get_current_date()?>)</h3></strong>
            </div>
        </div>
        <div class="row txt-cinza espacamento">
            <div class="col-md-offset-6 col-md-6">
                <span class="pull-right"><a href="../commerce/index.php" class="btn btn-primary"><strong><i><?php echo LANG_BACK; ?></i></strong></a></span>
            </div>
        </div>
        <div class="row txt-cinza espacamento">
<?php
//if (b_IsSysAdminFinancial())  {
	$descricao = new DescriptionReport('comissao');
	echo $descricao->MontaAreaDescricao();
//}//end if (b_IsSysAdminFinancial())

	if($_SESSION["tipo_acesso_pub"]=='PU') {
		$sqlopr = "select opr_nome, opr_codigo from operadoras where (opr_status = '1') and (opr_codigo='".$dd_operadora."') order by opr_nome";
	} else {
		$sqlopr = "select opr_nome, opr_codigo from operadoras where (opr_status != '0') and (not ((opr_codigo in ($dd_operadora_EPP_Cash, $dd_operadora_EPP_Cash_LH)) )) order by opr_nome";
	}
//	$resopr = pg_exec($connid, $sqlopr);
	$resopr = SQLexecuteQuery($sqlopr);
//echo "$sqlopr<br>";


	$bg_col_01 = "#FFFFFF";
	$bg_col_02 = "#EEEEEE";
	$bg_col = $bg_col_01;
	$extra_where = "";
	$dd_year = ""; //date("Y");

$msg_spot = "";
//echo "dd_operadora: ".$dd_operadora."<br>";
//echo "Submit: $Submit<br>";

//echo "tipo_acesso: $tipo_acesso<br>";	
//echo "tipo_acesso_var: $tipo_acesso_var<br>";	
/*
echo "_SESSION[\"iduser_bko\"]: ".$_SESSION["iduser_bko_pub"]."<br>
_SESSION[\"nome_bko\"]: ".$_SESSION["nome_bko"]."<br>
_SESSION[\"opr_codigo\"]: ".$_SESSION["opr_codigo_pub"]."<br>
_SESSION[\"opr_nome\"]: ".$_SESSION["opr_nome"]."<br>
_SESSION[\"tipo_acesso\"]: ".$_SESSION["tipo_acesso_pub"]."<br>
_SESSION[\"datalog_bko\"]: ".$_SESSION["datalog_bko"]."<br>
_SESSION[\"horalog_bko\"]: ".$_SESSION["horalog_bko"]."<br>";
*/

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
			$where_operadora_cartoes = " (ve_jogo='xx') ";
		}
	}

	if(!$dd_mode || ($dd_mode!='V')) {
		$dd_mode = "S";
	}
	$smode = $dd_mode;

	$dd_operadora_nome = "";
	if($dd_operadora) {
//		$resopr_nome = pg_exec($connid, "select opr_nome from operadoras where (opr_status = '1') and (opr_codigo='".$dd_operadora."') order by opr_ordem");
		$resopr_nome = SQLexecuteQuery("select opr_nome from operadoras where (opr_status = '1') and (opr_codigo='".$dd_operadora."') order by opr_ordem");
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
	else {
		$where_operadora = " vgm_opr_codigo!=".$dd_operadora_EPP_Cash." and  vgm_opr_codigo!=".$dd_operadora_EPP_Cash_LH ."";
	}//end else

//echo "tipo_acesso: ".$tipo_acesso."<br>";
//echo "tipo_acesso_var: ".$tipo_acesso_var."<br>";
//echo "dd_operadora: ".$dd_operadora."<br>";
//echo "dd_mode: ".$dd_mode."<br>";
//echo "dd_operadora_nome: ".$dd_operadora_nome."<br>";
//echo "where_operadora_pos: ".$where_operadora_pos."<br>";

//	$days_in_month = MonthDays($iMonth, $iYear)

	$iday = date("d"); // or any value from 1-12
	$imonth = date("n"); // or any value from 1-12
	$iyear	= date("Y"); // or any value >= 1
	$days_in_month = date("t",mktime(0,0,0,$imonth,1,$iyear));
	$days_in_month_prev = date("t",mktime(0,0,0,$imonth-1,1,$iyear));
//echo "iday: $iday<br>";
//echo "days_in_month: $days_in_month<br>";
//echo "days_in_month_prev: $days_in_month_prev<br>";

	$twomonthsago  = mktime(0, 0, 0, date("m")-2, date("d"), date("Y"));

	// Cria array de meses ====================================================================================
	$thismonth  = mktime(0, 0, 0, date("m"), 1, date("Y"));
	$firstmonth  = mktime(0, 0, 0, 1, 1, 2008);
	$today = date("d");
//echo "today: $today<br>";
//echo "thismonth: ".date("Y/m/d H:i:s",$thismonth)." <br> ";
//echo "firstmonth: ".date("Y/m/d H:i:s",$firstmonth)." <br> ";
	$i = 0;
	$currentmonth = $thismonth;
	$aMonths = array();
	$aMonthsDays = array();
	while($currentmonth >=$firstmonth) {
		$aMonths[$i] = $currentmonth;
		$aMonthsDays[$i] = date("t",$currentmonth);
		$currentmonth = mktime(0, 0, 0, date("m", $currentmonth)-1, 1, date("Y",$currentmonth));
		$i++;
	}
//for($i=0;$i<count($aMonths);$i++) echo date("Y/m/d H:i:s",$aMonths[$i])."<br>";

	$bg_col_01 = "#FFFFFF";
	$bg_col_02 = "#EEEEEE";
	$bg_col = $bg_col_01;

	// Cria array de canais ====================================================================================
	$aCanais = array("C", "E", "M", "L", "P");
//for($i=0;$i<count($aCanais);$i++) echo $aCanais[$i]."<br>";

	// Totais por mes ========================================================================================
	if($_SESSION["tipo_acesso_pub"]=='PU') {
		$dd_operadora = $_SESSION["opr_codigo_pub"];
	}
	if($dd_operadora==13) { 
		$where_operadora_cartoes = " (ve_jogo='OG') ";
	} else if($dd_operadora==17) { 
		$where_operadora_cartoes = " (ve_jogo='MU') ";
	} else { 
		if(strlen($dd_operadora)>0) 
			$where_operadora_cartoes = " (ve_jogo='??') ";
	}

//echo "where_operadora_pos: ".$where_operadora_pos."<br>";
//echo "where_operadora_cartoes: ".$where_operadora_cartoes."<br>";
//echo "extra_where: ".$extra_where."<br>";
	$sql_total_mes = get_sql_total_mes($extra_where, true, $smode, $dd_year, false, $where_origem);

//if (b_IsSysAdminFinancial())  {
//	echo "<b>sql_total_mes</b>: ".str_replace("\n","<br>\n",$sql_total_mes)."<br><hr>";
//}
//$msg_spot .= "<span class='texto'>Spot 1 ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.')." ".LANG_STATISTICS_SEARCH_MSG_UNIT."</span><br>&nbsp;"; 

//if(b_IsUsuarioWagner()) {
//	echo "<b>sql_total_mes</b>: ".str_replace("\n","<br>\n",$sql_total_mes)."<br><hr>";
//}

	$vendas_total_mes = SQLexecuteQuery($sql_total_mes);
	if($vendas_total_mes) {
		$aNVendas = array();
		$aVendas = array();
		$aNVendasMes = array();
		$aVendasMes = array();
		$aNVendasTotal = array();
		$aVendasTotal = array();

		// Reset Valores mensais e totais ================================================= 
		for($i=0;$i<count($aMonths);$i++) {
			for($j=0;$j<count($aCanais);$j++) {
				$aNVendas[$aCanais[$j]][$i] = 0;
				$aVendas[$aCanais[$j]][$i] = 0;
			}
		}
		for($j=0;$j<count($aCanais);$j++) {
			$aNVendasTotal[$aCanais[$j]] = 0;
			$aVendasTotal[$aCanais[$j]] = 0;
		}

		// Preenche Valores mensais e totais ================================================= 
		while ($vendas_total_mes_row = pg_fetch_array($vendas_total_mes)){
				//	0		1					2		3
				//	Canal	mês					n		Vendas 
				//	E		2008-06-01 00:00:00 1032	26910 
			$bMonthFound = false;
			for($m=0;$m<count($aMonths);$m++) {
				if ((strval($vendas_total_mes_row[1])==strval(date("Y-m-d H:i:s",$aMonths[$m])))) {
					$bMonthFound = true;
					break;
				}
			}

			if($bMonthFound) {
				$aNVendas[$vendas_total_mes_row[0]][$m] = $vendas_total_mes_row[2];
				$aVendas[$vendas_total_mes_row[0]][$m] = $vendas_total_mes_row[3];
			} else {
				if($_SESSION["tipo_acesso_pub"]!='PU') {
					echo "<div class='col-md-2 txt-vermelho borda-fina'>Mês não foi encontrado: ".substr(strval($vendas_total_mes_row[1]),0,8)." (".getChannelName($vendas_total_mes_row[0]).", R$".number_format($vendas_total_mes_row[3], 2, ',', '.').")</div>";
				}
			}
		}
//$msg_spot .= "<span class='texto'>Spot 2a: ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.')." ".LANG_STATISTICS_SEARCH_MSG_UNIT."</span><br>&nbsp;"; 

//echo "<pre>";
//print_r($aVendas);
//print_r($aNVendas);
//echo "</pre>";


		// Calcula totais por canal e geral =================================================
		$n = 0;
		$vendas = 0;
		for($j=0;$j<count($aCanais);$j++) {
			for($i=0;$i<count($aMonths);$i++) {
				$aNVendasTotal[$aCanais[$j]] += $aNVendas[$aCanais[$j]][$i];
				$aVendasTotal[$aCanais[$j]] += $aVendas[$aCanais[$j]][$i];
			}
			$n += $aNVendasTotal[$aCanais[$j]];
			$vendas += $aVendasTotal[$aCanais[$j]];
		}

		// Calcula totais <_?php echo LANG_STATISTICS_FOR_MONTH; ?_>
		for($i=0;$i<count($aMonths);$i++) {
			$aNVendasMes[$i] = 0;	
			$aVendasMes[$i] = 0;	
			for($j=0;$j<count($aCanais);$j++) {
				$aNVendasMes[$i] += $aNVendas[$aCanais[$j]][$i];
				$aVendasMes[$i] += $aVendas[$aCanais[$j]][$i];
			}
		}

//$msg_spot .= "<span class='texto'>Spot 2b: ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.')." ".LANG_STATISTICS_SEARCH_MSG_UNIT."</span><br>&nbsp;"; 

?>
        </div>
        <form name="form1" method="post">
            <div class="row txt-cinza espacamento">
                <div class="col-md-2">
                    <span class="pull-right"><?php echo LANG_STATISTICS_REPORT_TYPE; ?></span>
                </div>
                <div class="col-md-3">
<?php 
                if($_SESSION["tipo_acesso_pub"]=='PU')
                {
?>
                    <span style="font-weight: bold"><?php echo LANG_STATISTICS_OUT; ?></span>
                    <input type="hidden" name="dd_mode" id="dd_mode" value="<?php echo $dd_mode?>">
<?php 
                }else 
                { 
?>
                    <select name="dd_mode" id="dd_mode" class="form-control" onChange="document.form1.submit()">
                      <option value="S" <?php if($dd_mode=="S") echo "selected" ?>><?php echo LANG_STATISTICS_OUT; ?></option>
                      <option value="V" <?php if($dd_mode=="V") echo "selected" ?>><?php echo LANG_STATISTICS_SALES; ?></option>
                    </select>
<?php
                } 
?>
                </div>
                <div class="col-md-2">
                    <span class="pull-right"><?php echo LANG_STATISTICS_OPERATOR; ?></span>
                </div>
                <div class="col-md-3">
<?php
                if($_SESSION["tipo_acesso_pub"]=='PU')
                {
                    echo $_SESSION["opr_nome"]?>
                    <input type="hidden" name="dd_operadora" id="dd_operadora" value="<?php echo $dd_operadora?>">
<?php
                }else
                {
?>
                    <select name="dd_operadora" id="dd_operadora" class="form-control" onChange="document.form1.submit()">
                    <option value=""><?php echo LANG_STATISTICS_ALL_OPERATOR; ?></option>
                    <?php while ($pgopr = pg_fetch_array ($resopr)) { ?>
                    <option value="<?php echo $pgopr['opr_codigo'] ?>" <?php if($pgopr['opr_codigo'] == $dd_operadora) echo "selected" ?>><?php echo $pgopr['opr_nome']." (".$pgopr['opr_codigo'].")" ?></option>
                    <?php } ?>
                    </select>
<?php
                } 
?>
                </div>
            </div>
        </form>
        <div class="row txt-cinza espacamento">
<?php
		echo "<table border='1' cellpadding='0' cellspacing='1' bordercolor='#cccccc' style='border-collapse:collapse;'>";
		echo "<tr bgcolor='#FFFFCC' width='150'><th bgcolor='#FFFFFF'>&nbsp;</th>";
		for($j=0;$j<count($aCanais);$j++) {
			echo "<th colspan='2' align='center' width='120'><b><font color='#337ab7'>".getChannelName($aCanais[$j])."</font></b></th>";
		}
		echo "<th colspan='2' align='center' width='120'><b><font color='#337ab7'>".LANG_COMMISSIONS_TOTAL."</font></b></th>";
		echo "</tr>";

		echo "<tr bgcolor='#CCFFCC' align='center'><td><b><font color='#337ab7'>".LANG_MONTH_2."</font></b></td>";
		for($j=0;$j<count($aCanais);$j++) {
			echo "<td align='center'>n</td><td align='center'>".LANG_COMMISSIONS_SALES." (R$)</td>";
		}
		echo "<td align='center'>n</td><td align='center'>".LANG_COMMISSIONS_SALES." (R$)</td>";
		echo "</tr>";

		// Calcula Projeção por canal ======================================
		// Projeção - Linha superior
		echo "<tr bgcolor='#CCFFCC'><td rowspan='2'><b>Projeção<br>".mes_do_ano2($aMonths[0])."</b></td>\n";
/*
	$aCanais
    [0] => C
    [1] => E
    [2] => M
    [3] => L
    [4] => P

	$aVendas[canal][0] = current month
	$aVendas[canal][1] = previous month

*/
			for($j=0;$j<count($aCanais);$j++) {
				$aNVendas_nestemes = $aNVendas[$aCanais[$j]][0];
				$aVendas_nestemes = $aVendas[$aCanais[$j]][0];

				$nvendasPrj = ($aNVendas_nestemes + ($aNVendas[$aCanais[$j]][1]*($days_in_month-$iday)/$days_in_month_prev) );
				$vendasPrj = ($aVendas_nestemes + ($aVendas[$aCanais[$j]][1]*($days_in_month-$iday)/$days_in_month_prev) );
				$nvendasPrj_nestemes = ($aNVendas_nestemes + ($aNVendas_nestemes*($days_in_month-$iday)/$iday) );
				$vendasPrj_nestemes = ($aVendas_nestemes + ($aVendas_nestemes*($days_in_month-$iday)/$iday) );
				
				$stitle_n = ($_SESSION["tipo_acesso_pub"]=='AT')?" title='Prj neste mês: ".number_format($nvendasPrj_nestemes, 2, ',', '.')."'":"";

				if(b_IsUsuarioReinaldo() && ($_SESSION["tipo_acesso_pub"]=='AT') && $aCanais[$j]=='M') {
					$vendasPrj_nestemes_M_E = (($aVendas[$aCanais[1]][0] + $aVendas[$aCanais[2]][0]) + (($aVendas[$aCanais[1]][0] + $aVendas[$aCanais[2]][0])*($days_in_month-$iday)/$iday) );
					$stitle_v = ($_SESSION["tipo_acesso_pub"]=='AT')?" title='Prj neste mês: R$".number_format($vendasPrj_nestemes, 2, ',', '.')." \nM+E: R$".number_format($vendasPrj_nestemes_M_E, 2, ',', '.')."'":"";
				} else {
					$stitle_v = ($_SESSION["tipo_acesso_pub"]=='AT')?" title='Prj neste mês: R$".number_format($vendasPrj_nestemes, 2, ',', '.')."'":"";
				}


				echo "<td align='center'$stitle_n>".number_format($nvendasPrj, 0, ',', '')."</td>";
				echo "<td align='center'$stitle_v>".number_format($vendasPrj, 2, ',', '.')."</td>";
			}
			// Calcula Projeção Total ======================================

			$aNVendasMes_nestemes = $aNVendasMes[0];
			$aVendasMes_nestemes = $aVendasMes[0];

			$nvendasPrj = ($aNVendasMes_nestemes + ($aNVendasMes[1]*($days_in_month-$iday)/$days_in_month_prev) );
			$vendasPrj = ($aVendasMes_nestemes + ($aVendasMes[1]*($days_in_month-$iday)/$days_in_month_prev)) ;
			$nvendasPrj_nestemes = ($aNVendasMes_nestemes + ($aNVendasMes_nestemes*($days_in_month-$iday)/$iday) );
			$vendasPrj_nestemes = ($aVendasMes_nestemes + ($aVendasMes_nestemes*($days_in_month-$iday)/$iday) );
			$stitle_n = ($_SESSION["tipo_acesso_pub"]=='AT')?" title='Prj neste mês: ".number_format($nvendasPrj_nestemes, 2, ',', '.')."'":"";
			$stitle_v = ($_SESSION["tipo_acesso_pub"]=='AT')?" title='Prj neste mês: R$".number_format($vendasPrj_nestemes, 2, ',', '.')."'":"";

			$ipos = 100*( ( $nvendasPrj - $aNVendasMes[1] ) /  (($aNVendasMes[1])?$aNVendasMes[1]:1) );
			$sarrow = "<img src='/sys/imagens/".(($ipos>0)?"arrow_up_2.gif":(($ipos<0)?"arrow_down_2.gif":"equal.gif"))."' width='12' height='12' border='0' title='".(($ipos>0)?"Up":(($ipos<0)?"Down":"Equal"))."'>";
			echo "<td align='center'$stitle_n><b>".number_format($nvendasPrj, 0, ',', '')."</b></td>";
			echo "<td align='center'$stitle_v><b>".number_format($vendasPrj, 2, ',', '.')."</b></td>";
			echo "</tr>";

		echo "</tr>\n";

		// Projeção - Linha inferior
		echo "<tr bgcolor='#CCFFCC'>";	//"<td><b>".mes_do_ano2($aMonths[0])."</b></td>";
			for($j=0;$j<count($aCanais);$j++) {
				$nvendasPrj = ($aNVendas[$aCanais[$j]][0] + ($aNVendas[$aCanais[$j]][1]*($days_in_month-$iday)/$days_in_month_prev) );
				$vendasPrj = ($aVendas[$aCanais[$j]][0] + ($aVendas[$aCanais[$j]][1]*($days_in_month-$iday)/$days_in_month_prev) );
				$ipos = ((100*( $nvendasPrj - $aNVendas[$aCanais[$j]][1]) ) /((($aNVendas[$aCanais[$j]][1])>0)?$aNVendas[$aCanais[$j]][1]:1));
				$sarrow = "<img src='/sys/imagens/".(($ipos>0)?"arrow_up_2.gif":(($ipos<0)?"arrow_down_2.gif":"equal.gif"))."' width='12' height='12' border='0' title='".(($ipos>0)?"Up":(($ipos<0)?"Down":"Equal"))."'>";
				echo "<td align='center'>".$sarrow."&nbsp;".(($ipos>0)?"+":(($ipos<0)?"":"&nbsp;")).number_format($ipos, 0, ',', '')."%</td>\n";

				$ipos = ((100*( $vendasPrj - $aVendas[$aCanais[$j]][1]) ) /(($aVendas[$aCanais[$j]][1]>0)?$aVendas[$aCanais[$j]][1]:1));
				$sarrow = "<img src='/sys/imagens/".(($ipos>0)?"arrow_up_2.gif":(($ipos<0)?"arrow_down_2.gif":"equal.gif"))."' width='12' height='12' border='0' title='".(($ipos>0)?"Up":(($ipos<0)?"Down":"Equal"))."'>";
				$ndecimals = ($aCanais[$j]=="M" || $aCanais[$j]=="L")?1:0;
				$stit1 = " ".(($ipos>0)?"+":(($ipos<0)?"":" ")).number_format($ipos, 0, ',', '')."%";
				echo "<td align='center' title='$stit1'>".$sarrow."&nbsp;".(($ipos>0)?"+":(($ipos<0)?"":"&nbsp;")).number_format($ipos, $ndecimals, ',', '')."%</td>\n";
			}
			// Calcula Projeção Total ======================================
			$nvendasPrj = ($aNVendasMes[0] + ($aNVendasMes[1]*($days_in_month-$iday)/$days_in_month_prev) );
			$vendasPrj = ($aVendasMes[0] + ($aVendasMes[1]*($days_in_month-$iday)/$days_in_month_prev)) ;
			$ipos = 100*( ( $nvendasPrj - $aNVendasMes[1] ) /  (($aNVendasMes[1])?$aNVendasMes[1]:1) );
			$sarrow = "<img src='/sys/imagens/".(($ipos>0)?"arrow_up_2.gif":(($ipos<0)?"arrow_down_2.gif":"equal.gif"))."' width='12' height='12' border='0' title='".(($ipos>0)?"Up":(($ipos<0)?"Down":"Equal"))."'>";
			echo "<td align='center'><b>".$sarrow."&nbsp;".(($ipos>0)?"+":(($ipos<0)?"":"&nbsp;")).number_format($ipos, 0, ',', '')."%</b></td>";

			$vendasPrj = ($aVendasMes[0] + ($aVendasMes[1]*($days_in_month-$iday)/$days_in_month_prev)) ;
			$ipos = 100*( ( $vendasPrj - $aVendasMes[1] ) /  (($aVendasMes[1])?$aVendasMes[1]:1) );
			$sarrow = "<img src='/sys/imagens/".(($ipos>0)?"arrow_up_2.gif":(($ipos<0)?"arrow_down_2.gif":"equal.gif"))."' width='12' height='12' border='0' title='".(($ipos>0)?"Up":(($ipos<0)?"Down":"Equal"))."'>";
			echo "<td align='center'><b>".$sarrow."&nbsp;".(($ipos>0)?"+":(($ipos<0)?"":"&nbsp;")).number_format($ipos, 1, ',', '')."%</b></td>";

		echo "</tr>\n";

//$msg_spot .= "<span class='texto'>Spot 2c: ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.')." ".LANG_STATISTICS_SEARCH_MSG_UNIT."</span><br>&nbsp;"; 


		$bg_col = $bg_col_01;
		for($i=0;$i<count($aMonths);$i++) {
			echo "<tr bgcolor='".$bg_col."'><td><b>".mes_do_ano2($aMonths[$i])."</b></td>";
			$bg_col = ($bg_col==$bg_col_01)?$bg_col_02:$bg_col_01;
			$ndays_in_month = ($i==0)?$today:$aMonthsDays[$i];
			$ndays_in_month = ($ndays_in_month>0)?$ndays_in_month:1;
			$ndays_in_month_prev = ($aMonthsDays[1]>0)?$aMonthsDays[1]:1;
			for($j=0;$j<count($aCanais);$j++) {
				$n_prev_mes_anterior = 0;
				$v_prev_mes_anterior = 0;
				$n_prev_mes_corrente = 0;
				$v_prev_mes_corrente = 0;
				if($i==0) {
					$n_prev_mes_anterior = $today*$aNVendas[$aCanais[$j]][1]/$ndays_in_month_prev;
					$v_prev_mes_anterior = $today*$aVendas[$aCanais[$j]][1]/$ndays_in_month_prev;

					$n_prev_mes_corrente = $today*$aNVendas[$aCanais[$j]][1]/$days_in_month;
					$v_prev_mes_corrente = $today*$aVendas[$aCanais[$j]][1]/$days_in_month;
				}

//				$stitle_n = ($_SESSION["tipo_acesso_pub"]=='AT')?" title='".number_format($aNVendas[$aCanais[$j]][$i]/$ndays_in_month, 2, ',', '.')."/dia em $ndays_in_month dias'":"";
//				$stitle_v = ($_SESSION["tipo_acesso_pub"]=='AT')?" title='R$".number_format($aVendas[$aCanais[$j]][$i]/$ndays_in_month, 2, ',', '.')."/dia em $ndays_in_month dias'":"";

				$stitle_n = (
								($_SESSION["tipo_acesso_pub"]=='AT')
									?" title='".number_format($aNVendas[$aCanais[$j]][$i]/$ndays_in_month, 2, ',', '.')."/dia em $ndays_in_month dias".
										(
											($n_prev_mes_anterior>0)
												?"\n(devia ser hoje ".number_format($n_prev_mes_anterior, 0, ',', '')." ou ".number_format($n_prev_mes_corrente, 0, ',', '').")"
												:""
										)."'"
									:""
							);
				$stitle_v = (
								($_SESSION["tipo_acesso_pub"]=='AT')
									?" title='R$".number_format($aVendas[$aCanais[$j]][$i]/$ndays_in_month, 2, ',', '.')."/dia em $ndays_in_month dias".
										(
											($v_prev_mes_anterior>0)
													?"\n(devia ser hoje R$".number_format($v_prev_mes_anterior, 2, ',', '.')." ou R$".number_format($v_prev_mes_corrente, 2, ',', '.').")"
													:""
										)
										."\n".((b_IsUsuarioReinaldo())?(
											"Perc. do total: ".number_format(100.*$aVendas[$aCanais[$j]][$i]/$aVendasMes[$i], 2, ',', '')."%"
												.(
													($j==2) 
														?"\n[Total: (M+E) R$" . number_format($aVendas[$aCanais[1]][$i]+$aVendas[$aCanais[2]][$i], 2, ',', '.') . "]".
														"\n[Perc. do total: (M+E) " . number_format(100.*($aVendas[$aCanais[1]][$i]+$aVendas[$aCanais[2]][$i])/$aVendasMes[$i], 2, ',', '') . "%]"
														:""
												)
											):"")
										."'"
									:""
							);

				echo "<td align='center'$stitle_n>".$aNVendas[$aCanais[$j]][$i]."</td>";
				echo "<td align='center'$stitle_v>".number_format($aVendas[$aCanais[$j]][$i], 2, ',', '.')."</td>";
			}
			$stitle_n = ($_SESSION["tipo_acesso_pub"]=='AT')?" title='".number_format($aNVendasMes[$i]/$ndays_in_month, 2, ',', '.')."/dia em $ndays_in_month dias'":"";
			$stitle_v = ($_SESSION["tipo_acesso_pub"]=='AT')?" title='R$".number_format($aVendasMes[$i]/$ndays_in_month, 2, ',', '.')."/dia em $ndays_in_month dias'":"";

			echo "<td align='center'$stitle_n><b>".$aNVendasMes[$i]."</b></td>";
			echo "<td align='center'$stitle_v><b>".number_format($aVendasMes[$i], 2, ',', '.')."</b></td>";
			echo "</tr>";
		}

		// SubTotais
		echo "<tr bgcolor='#FFFFCC'><td align='right'><b>".LANG_COMMISSIONS_TOTALS."&nbsp;</b></td>";
		for($j=0;$j<count($aCanais);$j++) {
			echo "<td align='center'>".$aNVendasTotal[$aCanais[$j]]."</td> <td align='center'><b>".number_format($aVendasTotal[$aCanais[$j]], 2, ',', '.')."</b></td>";
		}
		echo "<td align='center'><b>".$n."</b></td> <td align='center'><b>".number_format($vendas, 2, ',', '.')."</b></td>";
		echo "</tr>";

		// Percentagens
		echo "<tr bgcolor='#FFFFCC'><td align='right'><b>%&nbsp;</b></td>";
		for($j=0;$j<count($aCanais);$j++) {
			echo "<td align='center'>".number_format((100*$aNVendasTotal[$aCanais[$j]]/(($n>0)?$n:1)), 0, ',', '.')."</td> <td align='center'><b>".number_format((100*$aVendasTotal[$aCanais[$j]]/(($vendas>0)?$vendas:1)), 0, ',', '.')."</b></td>";
		}
		echo "<td align='center' bgcolor='#FFFFFF'>&nbsp;</td><td align='center' bgcolor='#FFFFFF'>&nbsp;</td>";
		echo "</tr>";
		echo "</table>";


	} else {
		echo "<tr bgcolor='".$bg_col."'><td align='center' colspan='5'><font color='#FF0000'>".LANG_NOT_FOUND_2." &nbsp;</font></td></tr>";
	}

//echo "n_Cartoes: $n_vendas_Cartoes, vendas_Cartoes: $total_vendas_Cartoes<br>";
//$msg_spot .= "<span class='texto'>Spot 2 ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.')." ".LANG_STATISTICS_SEARCH_MSG_UNIT."</span><br>&nbsp;"; 

?>
<br>
<table border='0' cellpadding='0' cellspacing='1' width='80%' bordercolor='#cccccc' style='border-collapse:collapse;'>	
  <tr align="center"> 
	<td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td><td bgcolor="#FFFFFF" class="texto">	
	<?php
		// Testa cadastro de comissões e de produtos
		$sql_produtos = "select ogp_nome, opr_nome, ogp_opr_codigo from tb_operadora_games_produto ogp inner join operadoras ope on ope.opr_codigo = ogp.ogp_opr_codigo where ogp_ativo=1 ";
		$smsg_opr = "";
		if (($_SESSION["tipo_acesso_pub"]=='PU') || ($dd_operadora)) {
			$sql_produtos .= " and ope.opr_codigo = ".$dd_operadora;
			$smsg_opr = "(<font color='#3300FF'>".$dd_operadora_nome."</font>) ";
		}
//echo "sql_produtos: ".$sql_produtos."<br>";
//echo "smsg_opr: ".$smsg_opr."<br>";
		$res_produtos = SQLexecuteQuery($sql_produtos);
		if($res_produtos) {
			$sOutput = "";
			while ($res_produtos_row = pg_fetch_array($res_produtos)){
				$sRet = "";
				$bOutput = false;
				$sRet .= "'".$res_produtos_row['ogp_nome']."' (".$res_produtos_row['opr_nome'].", ID: ".$res_produtos_row['ogp_opr_codigo'].")";

//echo "'".$res_produtos_row['ogp_nome']."' (".$res_produtos_row['opr_nome'].", ID: ".$res_produtos_row['ogp_opr_codigo'].")<br>";

				foreach ($COMISSOES_BRUTAS as $ComissaoID => $ComissaoArray){ 
//					if($res_produtos_row['ogp_nome']=="Apoio Escolar 24Horas") {
//						echo $ComissaoID." ** ".$COMISSOES_BRUTAS[$ComissaoID][$COMISSOES_BRUTAS_PUBLISHER_M_E[$res_produtos_row['ogp_nome']]]." **<br>";
//					}
					if(!isset($COMISSOES_BRUTAS[$ComissaoID][$COMISSOES_BRUTAS_PUBLISHER_M_E[$res_produtos_row['ogp_nome']]]) || (($COMISSOES_BRUTAS[$ComissaoID][$COMISSOES_BRUTAS_PUBLISHER_M_E[$res_produtos_row['ogp_nome']]]==0) && ('Cartões'!=getChannelName($ComissaoID)))) {
						$sRet .= "[<font color='#FF0000'>".getChannelName($ComissaoID)."</font>";
//						$sRet .= "<font color='#FF0000'>Falta</font>";
						$sRet .= "] ";
						$bOutput = true;
					} else {
						$sRet .= "[<font color='#3300FF'>".$res_produtos_row['ogp_nome']."=".$COMISSOES_BRUTAS[$ComissaoID][$COMISSOES_BRUTAS_PUBLISHER_M_E[$res_produtos_row['ogp_nome']]]."</font>";
						$sRet .= "] ";
//echo "[<font color='#3300FF'>".$res_produtos_row['ogp_nome']."=".$COMISSOES_BRUTAS[$ComissaoID][$COMISSOES_BRUTAS_PUBLISHER_M_E[$res_produtos_row['ogp_nome']]]."</font>] ";
					}
				}
				if($bOutput)
					$sOutput .= "<hr>".$sRet."<br>";
			}

//$msg_spot .= "<span class='texto'>Spot 3 ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.')." ".LANG_STATISTICS_SEARCH_MSG_UNIT."</span><br>&nbsp;"; 

			if($sOutput!="")
				echo "<h3>".LANG_COMMISSIONS_PRODUCTS_REGISTERED." ".$smsg_opr." ".LANG_COMMISSIONS_WITHOUT_REGISTERED." </h3>".$sOutput."<br>";
			else 
				echo "<p><font color='#337ab7'>".LANG_COMMISSIONS_ALL_PRODUCTS_REGISTERED." ".$smsg_opr."".LANG_COMMISSIONS_HAVE_REGISTERED." </font></p>";
		} else {
			echo "<p><font color='#FF0000'>".LANG_COMMISSIONS_DB_ERROR_MSG."</font></p><!-- $sql_produtos -->";
		}
/*		
		echo "<hr>";
		foreach ($COMISSOES_BRUTAS as $ComissaoID => $ComissaoArray){ 
			echo "<hr>'$ComissaoID'<br>";
			foreach ($COMISSOES_BRUTAS_PUBLISHER_M_E as $NomeProduto => $Publisher){ 
				echo "'".$NomeProduto."' ('".$Publisher."') -> ".$COMISSOES_BRUTAS[$ComissaoID][$Publisher]."'<br>";
			}
		}
*/
	?>
	</td><td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td>
  </tr>
</table>
<br>
<table border='0' cellpadding='0' cellspacing='1' width='80%' bordercolor='#cccccc' style='border-collapse:collapse;'>	
  <tr align="center"> 
	<td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td><td bgcolor="#FFFFFF" class="texto"><span id="hammer" onClick="toggle_view();"><?php echo LANG_COMMISSIONS_INFO_MSG_1; ?></span>: <?php echo LANG_COMMISSIONS_INFO_MSG_2; ?>.</font><br></td><td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td>
  </tr>
  <tr align="center"> 
	<td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td><td bgcolor="#FFFFFF" class="texto"><?php echo LANG_STATISTICS_SEARCH_MSG." ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.')." ".LANG_STATISTICS_SEARCH_MSG_UNIT; ?></font></td><td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td>
  </tr>
</table>
   <?php 	
//	if($_SESSION["tipo_acesso_pub"]!='PU') {
//		echo "<hr>".$msg_spot."<hr>";
//	}
 ?>
</div>
</div>
</div>
   <?php require_once $raiz_do_projeto . "public_html/sys/includes/rodape_sys.php"; ?>
</body>
</html>

<?php
	// ===============================
	function getChannelName($ch) {
		$sName = "???";
		switch($ch) {
			case 'C':
				$sName = LANG_COMMISSIONS_CARDS;
				break;
			case 'E':
				$sName = "Money Express";
				break;
			case 'M':
				$sName = "Money";
				break;
			case 'L':
				$sName = "LH Money";
				break;
			case 'P':
				$sName = "POS";
				break;
		}
		return $sName;
	}
?>