<?php  
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
require_once "../../../../includes/constantes.php";
require_once $raiz_do_projeto . "public_html/sys/includes/topo_sys.php"; 
require_once $raiz_do_projeto . "public_html/sys/includes/gamer/inc_pub_access.php";
require_once $raiz_do_projeto . "class/gamer/classIntegracao.php";
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

require_once $raiz_do_projeto . "includes/sys/inc_stats.php";

$time_start = getmicrotime();

if ($_SERVER['HTTP_REFERER'] != "http://www.e-prepag.com.br/sys/admin/stats/TOTAL_MES_stats.php") {
        $dd_exclui_epp_cash = 1;
}

?>

<!doctype html public "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title> <?php echo LANG_STATISTICS_TOTAL_SALES; ?><?php if(strlen($_SESSION["opr_nome"])>0) echo " (".$_SESSION["opr_nome"].") ";?> </title>
<link href="/includes/bootstrap/css/bootstrap.min_new.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/js/jquery.js"></script>
<script type="text/javascript" src="/includes/bootstrap/js/bootstrap.min.js"></script>
<link href="/sys/css/css.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
</head>
<body>
<?php
if (b_IsSysAdminFinancial())  {
    $descricao = new DescriptionReport('volume');
    echo $descricao->MontaAreaDescricao();
}//end if (b_IsSysAdminFinancial())

$bg_col_01 = "#FFFFFF";
$bg_col_02 = "#EEEEEE";
$bg_col = $bg_col_01;
$extra_where = "";
$where_operadora = "";
$where_operadora_pos = "";
$where_operadora_cartoes = "";
$where_operadora_gift_card = "";
$where_operadora_gocash = "";
$where_operadora_rede_ponto_certo = "";
$where_origem = "";

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

if(!$dd_year)   $dd_year      = date('Y');

$dd_operadora_nome = "";
$possui_totalizacao_utilizacao = false;
if($dd_operadora) {
        $resopr_nome = SQLexecuteQuery("select opr_nome, opr_contabiliza_utilizacao, opr_data_inicio_contabilizacao_utilizacao from operadoras where (opr_status = '1') and (opr_codigo='".$dd_operadora."') order by opr_nome");
        if($pgopr_nome = pg_fetch_array ($resopr_nome)) { 
                $dd_operadora_nome = $pgopr_nome['opr_nome'];
                $possui_totalizacao_utilizacao = $pgopr_nome['opr_contabiliza_utilizacao'];
                $opr_data_inicio_contabilizacao_utilizacao = $pgopr_nome['opr_data_inicio_contabilizacao_utilizacao'];
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
        $where_operadora_gift_card = " and (pih_id = ".$dd_operadora.")";
        
        $where_operadora_gocash = " and (pgc_opr_codigo = ".$dd_operadora.") ";
        
        $where_operadora_rede_ponto_certo = " opr_codigo = ".$dd_operadora." ";

}
if ($dd_exclui_epp_cash==1) {
        if (empty($where_operadora)) {
                $where_operadora .= " vgm_opr_codigo!=".$dd_operadora_EPP_Cash." and  vgm_opr_codigo!=".$dd_operadora_EPP_Cash_LH ." ";
        }
        else {
                $where_operadora .= " and vgm_opr_codigo!=".$dd_operadora_EPP_Cash." and  vgm_opr_codigo!=".$dd_operadora_EPP_Cash_LH ." ";
        }
}
if($_SESSION["tipo_acesso_pub"]=='PU') {
        $sqlopr = "select opr_nome, opr_codigo from operadoras where (opr_status = '1') and (opr_codigo='".$dd_operadora."') order by opr_nome";
} else {
        $sqlopr = "select opr_nome, opr_codigo from operadoras where (opr_status != '0') order by opr_nome";
        if($dd_operadora==38) { 
                $where_origem = ($dd_origem_stardoll)?" and vg_http_referer_origem='STARDOLL' ":"";
        }
}

//IDS de integração
if (!empty($dd_ids_integracao)) {
        if (empty($where_operadora)) {
                $where_operadora .= " vg_integracao_parceiro_origem_id = '".$dd_ids_integracao."' ";
        }
        else {
                $where_operadora .= " and vg_integracao_parceiro_origem_id = '".$dd_ids_integracao."' ";
        }
}

        
$resopr = SQLexecuteQuery($sqlopr);
//echo $sqlopr."<br>";

?>
<div class="container-fluid">
    <div class="container txt-azul-claro bg-branco txt-cinza fontsize-p">
        <div class="row">
            <div class="col-md-12 ">
                <strong><h3><?php echo LANG_STATISTICS_TOTALS_SALES; ?> <?php echo "<font color='#3300FF'>".$dd_operadora_nome."</font>"?> <?php echo LANG_STATISTICS_FOR_MONTH; ?> <?php  if(strlen($_SESSION["opr_nome"])>0) echo "<font color='#66CC66'>".$_SESSION["opr_nome"]."</font> ";?>- <?php echo LANG_STATISTICS_PAGE_TITLE_2; ?> (<?php echo get_current_date()?>)</h3></strong>
            </div>
        </div>
        <div class="row txt-cinza top10">
            <div class="col-md-6">
                <span class="pull-left"><strong><?php echo LANG_POS_SEARCH_1; ?></strong></span>
            </div>
            <div class="col-md-6">
                <span class="pull-right"><a href="/sys/admin/commerce/index.php" class="btn btn-primary"><strong><i><?php echo LANG_BACK; ?></i></strong></a></span>
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
                    <select name="dd_operadora" id="dd_operadora" class="form-control">
                        <option value=""><?php echo LANG_POS_ALL_OPERATOR; ?></option>
<?php  
                        while ($pgopr = pg_fetch_array ($resopr)) 
                        {
?>
                        <option value="<?php  echo $pgopr['opr_codigo'] ?>" <?php  if($pgopr['opr_codigo'] == $dd_operadora) echo "selected" ?>><?php  echo $pgopr['opr_nome'] ?></option>
<?php  
                        } 
?>
                    </select>
<?php 
                } 
		?>
            </div>
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
                } else 
                { 
?>
		<select name="dd_mode" id="dd_mode" class="form-control">
		  <option value="S" <?php  if($dd_mode=="S") echo "selected" ?>><?php echo LANG_STATISTICS_OUT; ?></option>
		  <option value="V" <?php  if($dd_mode=="V") echo "selected" ?>><?php echo LANG_STATISTICS_SALES; ?></option>
		</select>
<?php 
                } 
?>
            </div>
        </div>
        <div class="row txt-cinza top10">
            <div class="col-md-2">
                <span class="pull-right"><?php echo LANG_YEAR_2; ?></span>
            </div>
            <div class="col-md-3">
                <select name="dd_year" id="dd_year" class="form-control">
<?php 
                for($i =  date('Y'); $i >= (int)(substr($inic_oper_data, 6)) ; $i--) 
                {
?>
                    <option value="<?php echo $i ?>" <?php if($dd_year == $i) echo "selected" ?>><?php echo $i ?></option>
<?php 
                } 

                if(b_IsUsuarioAdminList()) 
                {
?>
                    <option value="Todos" <?php if($dd_year == "Todos") echo "selected" ?>>Todos os anos</option>
<?php 
                }
?>
                </select>
            </div>
            <div class="col-md-2">
                <span class="pull-right">Excluir vendas EPP CASH</span>
            </div>
            <div class="col-md-3">
                <?php echo "<input class='pull-left' type='checkbox' name='dd_exclui_epp_cash' id='dd_exclui_epp_cash'" . (($dd_exclui_epp_cash==1)?" checked":"") . " value= '1'>"; ?>
            </div>
        </div>
<?php        

        
        if($_SESSION["tipo_acesso_pub"]=='AT') 
        {
            if($dd_operadora==38) 
            {
?>
        <div class="row txt-cinza top10">
            <div class="col-md-12">
<?php
                echo "(" . (($dd_origem_stardoll)?"<font style='color:#0000CC;background-color:#FFFF00'>":"") . "com origem na Stardoll/UOL" . (($dd_origem_stardoll)?"</font>":"") . " <input type='checkbox' name='dd_origem_stardoll' id='dd_origem_stardoll'" . (($dd_origem_stardoll)?" checked":"") . ">)";
?>
            </div>
        </div>
<?php
            }
        }
        
        
?>
        
<?php
        if(!b_is_Publisher() && count(retornaIdsIntegracao($dd_operadora)) > 0) 
        {
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
?>
        <div class="row txt-cinza top10">
            <div class="col-md-offset-10 col-md-2">
                <input type="submit" name="btnSubmit" value="Atualizar" class="btn pull-right btn-success">
            </div>
        </div>
</form>
<?php 

if($_REQUEST['btnSubmit']) {

		$iday = date("d"); // or any value from 1-12
		$imonth = date("n"); // or any value from 1-12
		$iyear	= date("Y"); // or any value >= 1
		$days_in_month = date("t",mktime(0,0,0,$imonth,1,$iyear));
		$days_in_month_prev = date("t",mktime(0,0,0,$imonth-1,1,$iyear));

		$twomonthsago  = mktime(0, 0, 0, date("m")-2, date("d"), date("Y"));

		// Cria array de meses ====================================================================================
		$dd_month = date("m");
		if(($dd_year!="Todos") && ($dd_year<$iyear)) $dd_month = 12;

		$thismonth_lastday  = mktime(23, 59, 59, $dd_month+1, 0, ((($dd_year!="Todos"))?$dd_year:date("Y")));
		$thismonth  = mktime(0, 0, 0, $dd_month, 1, ((($dd_year!="Todos"))?$dd_year:date("Y")) );
		if($dd_year!="Todos") {
			$firstmonth  = mktime(0, 0, 0, (($dd_month>1)?1:0), 1, ((($dd_year!="Todos"))?$dd_year:"2008"));
		} else {
			$firstmonth  = mktime(0, 0, 0, (($dd_month>1)?1:1), 1, ((($dd_year!="Todos"))?$dd_year:"2008"));
		}
		$today = date("d");
                
                $dd_data_start = date("Y-m-d H:i:s",$firstmonth);
                $dd_data_stop = date("Y-m-d H:i:s",$thismonth_lastday);

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

		$bg_col_01 = "#FFFFFF";
		$bg_col_02 = "#EEEEEE";
		$bg_col = $bg_col_01;

		// Cria array de canais ====================================================================================
		$aCanais = array("C", "E", "M", "L", "P");

                //Capturando informações de dados de totalização por utilização
                $where_opr_venda_lan = "";
                $where_opr_venda_lan_negativa = "";
                $where_opr_utilizacao_lan = "";
                if($possui_totalizacao_utilizacao) {
                    //echo "ID: ".$dd_operadora." => DATA: [".substr($opr_data_inicio_contabilizacao_utilizacao,0,19)."]<br>";
                    $where_opr_venda_lan = " AND ( CASE  WHEN vgm.vgm_opr_codigo = $dd_operadora THEN vg.vg_data_inclusao < '".substr($opr_data_inicio_contabilizacao_utilizacao,0,19)."' ELSE vg.vg_data_inclusao > '2008-01-01 00:00:00' END ) ";
                    $where_opr_venda_lan_negativa = " AND ( CASE WHEN vgm.vgm_opr_codigo = $dd_operadora THEN vg.vg_data_inclusao >= '".substr($opr_data_inicio_contabilizacao_utilizacao,0,19)."' ELSE FALSE END ) ";
                    $where_opr_utilizacao_lan = " AND ( CASE  WHEN pih_id = $dd_operadora THEN pih_data >= '".substr($opr_data_inicio_contabilizacao_utilizacao,0,19)."' ELSE FALSE END ) ";
                } //end if($possui_totalizacao_utilizacao)
                if(empty($dd_operadora)){
                    //Buscando Publisher que possuem totalização por utilização
                    //require_once $raiz_do_projeto . "includes/functions.php";
                    $vetorPublisherPorUtilizacao = levantamentoPublisherComFechamentoUtilizacao();

                    if(count($vetorPublisherPorUtilizacao)>0) {
                        $where_opr_venda_lan = " AND ( CASE ";
                        $where_opr_venda_lan_negativa = " AND ( CASE ";
                        $where_opr_utilizacao_lan = " AND ( CASE ";
                        foreach ($vetorPublisherPorUtilizacao as $opr_codigo => $opr_data_inicio_contabilizacao_utilizacao){ 
                            //echo "ID: ".$opr_codigo." => DATA: [".substr($opr_data_inicio_contabilizacao_utilizacao,0,19)."]<br>";
                            $where_opr_venda_lan .= " WHEN vgm.vgm_opr_codigo = $opr_codigo THEN vg.vg_data_inclusao < '".substr($opr_data_inicio_contabilizacao_utilizacao,0,19)."' ";
                            $where_opr_venda_lan_negativa .= " WHEN vgm.vgm_opr_codigo = $opr_codigo THEN vg.vg_data_inclusao >= '".substr($opr_data_inicio_contabilizacao_utilizacao,0,19)."' ";
                            $where_opr_utilizacao_lan .= "  WHEN pih_id = $opr_codigo THEN pih_data >= '".substr($opr_data_inicio_contabilizacao_utilizacao,0,19)."' ";
                        }//end foreach
                        $where_opr_venda_lan .= " ELSE vg.vg_data_inclusao > '2008-01-01 00:00:00' END )";
                        $where_opr_venda_lan_negativa .= " ELSE FALSE END )";
                        $where_opr_utilizacao_lan .= "  ELSE FALSE END ) ";
                        
                        $possui_totalizacao_utilizacao = true;
                        
                    }//end if(count($vetorPublisherPorUtilizacao)>0)
                }//end if(empty($dd_operadora))
                
		// Totais por mes ========================================================================================
		// Define vars ($dd_data_start - $dd_data_stop) to fix an interval (like [2012/12/01 00:00:00 - 2013/01/31 23:59:59]
		// Or set $dd_year to calculate just one year
		$sql_total_mes = get_sql_total_mes($extra_where, false, $smode, null, false, $where_origem, $possui_totalizacao_utilizacao);

                //echo $sql_total_mes."<br>";
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


					// $vendas_total_mes_row[]
					//	0		1					2		3
					//	Canal	mês					n		Vendas 
					//	E		2008-06-01 00:00:00 1032	26910 
				$bMonthFound = false;
				for($m=0;$m<count($aMonths);$m++) {
					if ((strval(str_replace("01:00:00","00:00:00",substr($vendas_total_mes_row[1],0,19)))==strval(date("Y-m-d H:i:s",$aMonths[$m])))) {
						$bMonthFound = true;	// -> month in $m
						break;
					}
				}
				if($bMonthFound) {
					$aNVendas[$vendas_total_mes_row[0]][$m] = $vendas_total_mes_row[2];	// n
					$aVendas[$vendas_total_mes_row[0]][$m] = $vendas_total_mes_row[3];	// Vendas
				} else {
					if($_SESSION["tipo_acesso_pub"]!='PU') {
						echo "<td colspan='4'>Mês não foi encontrado: ".substr(strval($vendas_total_mes_row[1]),0,8)." (".getChannelName($vendas_total_mes_row[0]).", R$".number_format($vendas_total_mes_row[3], 2, ',', '.').")</td>";
					}
				}
			}

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

			// Calcula totais por mês
			for($i=0;$i<count($aMonths);$i++) {
				$aNVendasMes[$i] = 0;	
				$aVendasMes[$i] = 0;	
				for($j=0;$j<count($aCanais);$j++) {
					$aNVendasMes[$i] += $aNVendas[$aCanais[$j]][$i];
					$aVendasMes[$i] += $aVendas[$aCanais[$j]][$i];
				}
			}
                        $cabecalho = array();
                        $cabecalho[] = "";
                        
			echo "<table border='1' cellpadding='0' cellspacing='1' bordercolor='#cccccc' style='border-collapse:collapse;' class='txt-cinza fontsize-13 tdp5'>";
			echo "<tr bgcolor='#99CCFF' width='150'><th bgcolor='#FFFFFF'>&nbsp;</th>";
			for($j=0;$j<count($aCanais);$j++) {
				echo "<th colspan='2' align='center' width='120'><b><font color='#337ab7'>".getChannelName($aCanais[$j])."</font></b></th>";
                                $cabecalho[] = getChannelName($aCanais[$j]);
                                $cabecalho[] = getChannelName($aCanais[$j]);
			}
                        
                        $cabecalho[] = LANG_STATISTICS_TOTAL;
                        $cabecalho[] = LANG_STATISTICS_TOTAL;
			echo "<th colspan='2' align='center' width='120'><b><font color='#337ab7'>".LANG_STATISTICS_TOTAL."</font></b></th>";
			echo "</tr>";
                        
                        require_once $raiz_do_projeto."class/util/CSV.class.php";
                                        
                        $objCsv = new CSV(implode(";", $cabecalho), md5(uniqid()), $raiz_do_projeto."public_html/cache/");
                        $objCsv->setCabecalho();
                        
                        $line = array();
                        $line[] = LANG_MONTH_2;
			echo "<tr bgcolor='#99CCFF' align='center'><td><b><font color='#337ab7'>".LANG_MONTH_2."</font></b></td>";
			for($j=0;$j<count($aCanais);$j++) {
				echo "<td align='center'>n</td><td align='center'>".LANG_STATISTICS_SALES_1." (R$)</td>";
                                $line[] = "n";
                                $line[] = LANG_STATISTICS_SALES_1;
			}
                        
                        $line[] = "n";
                        $line[] = LANG_STATISTICS_SALES_1;
                        
			echo "<td align='center'>n</td><td align='center'>".LANG_STATISTICS_SALES_1." (R$)</td>";
			echo "</tr>";
                        
                        $objCsv->setLine(implode(";",$line));

                        $line = array();
                        $line[] = LANG_STATISTICS_PROJECTION." - ".mes_do_ano2($aMonths[0]);
			// Calcula Projeção por canal ======================================

			// Projeção - Linha superior
			echo "<tr bgcolor='#CCFFCC'><td rowspan='2'><b>".LANG_STATISTICS_PROJECTION."<br>".mes_do_ano2($aMonths[0])."</b></td>";
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
				@$aNVendas_nestemes = $aNVendas[$aCanais[$j]][0];
				@$aVendas_nestemes = $aVendas[$aCanais[$j]][0];

				@$nvendasPrj = ($aNVendas_nestemes + ($aNVendas[$aCanais[$j]][1]*($days_in_month-$iday)/$days_in_month_prev) );
				@$vendasPrj = ($aVendas_nestemes + ($aVendas[$aCanais[$j]][1]*($days_in_month-$iday)/$days_in_month_prev) );
				@$nvendasPrj_nestemes = ($aNVendas_nestemes + ($aNVendas_nestemes*($days_in_month-$iday)/$iday) );
				@$vendasPrj_nestemes = ($aVendas_nestemes + ($aVendas_nestemes*($days_in_month-$iday)/$iday) );
				@$perc_NVendas_nesteMes = 100*($nvendasPrj_nestemes-$aNVendas[$aCanais[$j]][1])/$aNVendas[$aCanais[$j]][1];
				@$perc_Vendas_nesteMes = 100*($vendasPrj_nestemes-$aVendas[$aCanais[$j]][1])/$aVendas[$aCanais[$j]][1];

				$stitle_n = ($_SESSION["tipo_acesso_pub"]=='AT')?" title='Prj neste mês: ".number_format($nvendasPrj_nestemes, 2, ',', '.')." (".number_format($perc_NVendas_nesteMes, 2, ',', '.')."%)'":"";

				if(b_IsUsuarioReinaldo() && ($_SESSION["tipo_acesso_pub"]=='AT') && $aCanais[$j]=='M') {
					$vendasPrj_nestemes_M_E = (($aVendas[$aCanais[1]][0] + $aVendas[$aCanais[2]][0]) + (($aVendas[$aCanais[1]][0] + $aVendas[$aCanais[2]][0])*($days_in_month-$iday)/$iday) );
					$stitle_v = ($_SESSION["tipo_acesso_pub"]=='AT')?" title='Prj neste mês: R$".number_format($vendasPrj_nestemes, 2, ',', '.')." (".number_format($perc_Vendas_nesteMes, 2, ',', '.')."%)\nM+E: R$".number_format($vendasPrj_nestemes_M_E, 2, ',', '.')."'":"";
				} else {
					$stitle_v = ($_SESSION["tipo_acesso_pub"]=='AT')?" title='Prj neste mês: R$".number_format($vendasPrj_nestemes, 2, ',', '.')."' (".number_format($perc_Vendas_nesteMes, 2, ',', '.')."%)":"";
				}

                                $line[] = number_format($nvendasPrj, 0, ',', '');
                                $line[] = number_format($vendasPrj, 2, ',', '.');
                                
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
                        if(empty($aNVendasMes[1])) {
                            $perc_NVendas_nesteMes = 0;
                        }
                        else {
                            $perc_NVendas_nesteMes = 100*($nvendasPrj_nestemes-$aNVendasMes[1])/$aNVendasMes[1];
                        }
                        if(empty($aVendasMes[1])) {
                            $perc_Vendas_nesteMes = 0;
                        }
                        else {
                            $perc_Vendas_nesteMes = 100*($vendasPrj_nestemes-$aVendasMes[1])/$aVendasMes[1];
                        }

			$stitle_n = ($_SESSION["tipo_acesso_pub"]=='AT')?" title='Prj neste mês: ".number_format($nvendasPrj_nestemes, 2, ',', '.')." (".number_format($perc_NVendas_nesteMes, 2, ',', '.')."%)'":"";
			$stitle_v = ($_SESSION["tipo_acesso_pub"]=='AT')?" title='Prj neste mês: R$".number_format($vendasPrj_nestemes, 2, ',', '.')." (".number_format($perc_Vendas_nesteMes, 2, ',', '.')."%)'":"";

                        $line[] = number_format($nvendasPrj, 0, ',', '');
                        $line[] = number_format($vendasPrj, 2, ',', '.');
                        
			echo "<td align='center'$stitle_n><b>".number_format($nvendasPrj, 0, ',', '')."</b></td>";
			echo "<td align='center'$stitle_v><b>".number_format($vendasPrj, 2, ',', '.')."</b></td>";
			echo "</tr>";

                        $objCsv->setLine(implode(";",$line));
                        $line = array();
                        $line[] = "";
			// Projeção - Linha inferior
			echo "<tr bgcolor='#CCFFCC'>";	//"<td><b>".mes_do_ano2($aMonths[0])."</b></td>";
			for($j=0;$j<count($aCanais);$j++) {
				$nvendasPrj = ($aNVendas[$aCanais[$j]][0] + ($aNVendas[$aCanais[$j]][1]*($days_in_month-$iday)/$days_in_month_prev) );
				$vendasPrj = ($aVendas[$aCanais[$j]][0] + ($aVendas[$aCanais[$j]][1]*($days_in_month-$iday)/$days_in_month_prev) );
                                
				$ipos = ((100*( $nvendasPrj - $aNVendas[$aCanais[$j]][1]) ) /getValueNonZero($aNVendas[$aCanais[$j]][1]));
				$sarrow = "<img src='/sys/imagens/".(($ipos>0)?"arrow_up_2.gif":(($ipos<0)?"arrow_down_2.gif":"equal.gif"))."' width='12' height='12' border='0' title='".(($ipos>0)?"Up":(($ipos<0)?"Down":"Equal"))."'>";
				echo "<td align='center'>".$sarrow."&nbsp;".(($ipos>0)?"+":(($ipos<0)?"":"&nbsp;")).number_format($ipos, 0, ',', '')."%</td>";

				$ipos = ((100*( $vendasPrj - $aVendas[$aCanais[$j]][1]) ) / getValueNonZero($aVendas[$aCanais[$j]][1]));
				$sarrow = "<img src='/sys/imagens/".(($ipos>0)?"arrow_up_2.gif":(($ipos<0)?"arrow_down_2.gif":"equal.gif"))."' width='12' height='12' border='0' title='".(($ipos>0)?"Up":(($ipos<0)?"Down":"Equal"))."'>";
				$ndecimals = ($aCanais[$j]=="M" || $aCanais[$j]=="L")?1:0;
				$stit1 = " ".(($ipos>0)?"+":(($ipos<0)?"":" ")).number_format($ipos, 0, ',', '')."%";
				echo "<td align='center' title='$stit1'>".$sarrow."&nbsp;".(($ipos>0)?"+":(($ipos<0)?"":"&nbsp;")).number_format($ipos, $ndecimals, ',', '')."%</td>";
                                
                                $line[] = number_format($ipos, 0, ',', '')."%";
                                $line[] = number_format($ipos, $ndecimals, ',', '')."%";
			}
			// Calcula Projeção Total ======================================
			$nvendasPrj = ($aNVendasMes[0] + ($aNVendasMes[1]*($days_in_month-$iday)/$days_in_month_prev) );
			$vendasPrj = ($aVendasMes[0] + ($aVendasMes[1]*($days_in_month-$iday)/$days_in_month_prev)) ;

			$ipos = 100*( ( $nvendasPrj - $aNVendasMes[1] ) /  getValueNonZero($aNVendasMes[1]) );
			$sarrow = "<img src='/sys/imagens/".(($ipos>0)?"arrow_up_2.gif":(($ipos<0)?"arrow_down_2.gif":"equal.gif"))."' width='12' height='12' border='0' title='".(($ipos>0)?"Up":(($ipos<0)?"Down":"Equal"))."'>";
			echo "<td align='center'><b>".$sarrow."&nbsp;".(($ipos>0)?"+":(($ipos<0)?"":"&nbsp;")).number_format($ipos, 0, ',', '')."%</b></td>";

			$ipos = 100*( ( $vendasPrj - $aVendasMes[1] ) /  getValueNonZero($aVendasMes[1]) );
			$sarrow = "<img src='/sys/imagens/".(($ipos>0)?"arrow_up_2.gif":(($ipos<0)?"arrow_down_2.gif":"equal.gif"))."' width='12' height='12' border='0' title='".(($ipos>0)?"Up":(($ipos<0)?"Down":"Equal"))."'>";
			echo "<td align='center'><b>".$sarrow."&nbsp;".(($ipos>0)?"+":(($ipos<0)?"":"&nbsp;")).number_format($ipos, 1, ',', '')."%</b></td>";
			echo "</tr>";
                        
                        $line[] = number_format($ipos, 0, ',', '')."%";
                        $line[] = number_format($ipos, 1, ',', '')."%";
                        $objCsv->setLine(implode(";",$line));
                        $line = array();
                        
			$bg_col = $bg_col_01;
			for($i=0;$i<count($aMonths);$i++) {
                                $line[] = mes_do_ano2($aMonths[$i]);
                            
				echo "<tr class='trListagem'><td><b>".mes_do_ano2($aMonths[$i])."</b></td>";
				$bg_col = ($bg_col==$bg_col_01)?$bg_col_02:$bg_col_01;
				$ndays_in_month = (($i==0) && ($dd_year==date("Y")) )?$today:$aMonthsDays[$i];
				$ndays_in_month = ($ndays_in_month>0)?$ndays_in_month:1;
				$ndays_in_month_prev = ($aMonthsDays[1]>0)?$aMonthsDays[1]:1;
				for($j=0;$j<count($aCanais);$j++) {
					$n_prev_mes_anterior = 0;
					$v_prev_mes_anterior = 0;
					$n_prev_mes_corrente = 0;
					$v_prev_mes_corrente = 0;
					if(($i==0) && ($dd_year==date("Y"))) {
						$n_prev_mes_anterior = $today*$aNVendas[$aCanais[$j]][1]/$ndays_in_month_prev;
						$v_prev_mes_anterior = $today*$aVendas[$aCanais[$j]][1]/$ndays_in_month_prev;

						$n_prev_mes_corrente = $today*$aNVendas[$aCanais[$j]][1]/$days_in_month;
						$v_prev_mes_corrente = $today*$aVendas[$aCanais[$j]][1]/$days_in_month;
					}
					$stitle_n = (
									($_SESSION["tipo_acesso_pub"]=='AT')
										?" title='".number_format($aNVendas[$aCanais[$j]][$i]/$ndays_in_month, 2, ',', '.')."/dia em $ndays_in_month dias".
											(
												((b_IsUsuarioReinaldo()) && $n_prev_mes_anterior>0)
													?"\n(devia ser hoje ".number_format($n_prev_mes_anterior, 0, ',', '').
														(($n_prev_mes_corrente!=$n_prev_mes_anterior)?" ou ".number_format($n_prev_mes_corrente, 0, ',', ''):"").
														")"
													:""
											)."'"
										:""
								);
					$v_next_value = $ndays_in_month*(1000*floor( (($aVendas[$aCanais[$j]][$i]/$ndays_in_month))/1000+1));
					$stitle_v = (
									($_SESSION["tipo_acesso_pub"]=='AT')
										?" title='R$".number_format($aVendas[$aCanais[$j]][$i]/$ndays_in_month, 2, ',', '.')."/dia em $ndays_in_month dias".
											(
												((b_IsUsuarioReinaldo()) && $v_prev_mes_anterior>0)
														?"\n(devia ser hoje R$".number_format($v_prev_mes_anterior, 2, ',', '.') .	
															(($v_prev_mes_anterior!=$v_prev_mes_corrente)?" ou R$".number_format($v_prev_mes_corrente, 2, ',', '.'):"") .
															")"
														:""
											)
											."\n".((b_IsUsuarioReinaldo())?(
												"Perc. do total: ".number_format(100.*$aVendas[$aCanais[$j]][$i]/$aVendasMes[$i], 2, ',', '')."%"
													.(
														($j==2) 
															?"\n[Total: (C+M+E+P) R$" . number_format($aVendas[$aCanais[0]][$i] + $aVendas[$aCanais[1]][$i] + $aVendas[$aCanais[2]][$i] + $aVendas[$aCanais[4]][$i], 2, ',', '.') . "]".
															"\n[Perc. do total: (C+M+E+P) " . number_format(100.*($aVendas[$aCanais[0]][$i] + $aVendas[$aCanais[1]][$i] + $aVendas[$aCanais[2]][$i] + $aVendas[$aCanais[4]][$i])/$aVendasMes[$i], 2, ',', '.') . "%]"
															:""
													)
												):"")
											.((b_IsUsuarioReinaldo())?"\n"."Próximo valor diário: R$".number_format($v_next_value, 2, ',', '.'):"")
											."'"
										:""
								);
                                        $line[] = $aNVendas[$aCanais[$j]][$i];
                                        $line[] = number_format($aVendas[$aCanais[$j]][$i], 2, ',', '.');
                                        
					echo "<td align='center'$stitle_n>".$aNVendas[$aCanais[$j]][$i]."</td>";
					echo "<td align='center'$stitle_v>".number_format($aVendas[$aCanais[$j]][$i], 2, ',', '.')."</td>";
				}
				$n_prev_mes_anterior = 0;
				$v_prev_mes_anterior = 0;
				if(($i==0) && ($dd_year==date("Y"))) {
					$n_prev_mes_anterior = $today*$aNVendasMes[1]/$ndays_in_month_prev;
					$v_prev_mes_anterior = $today*$aVendasMes[1]/$ndays_in_month_prev;

					$n_prev_mes_corrente = $today*$aNVendasMes[1]/$days_in_month;
					$v_prev_mes_corrente = $today*$aVendasMes[1]/$days_in_month;

				}

				$stitle_n = (
								($_SESSION["tipo_acesso_pub"]=='AT')
									?" title='".number_format($aNVendasMes[$i]/$ndays_in_month, 2, ',', '.')."/dia em $ndays_in_month dias".
										(
											((b_IsUsuarioReinaldo()) && $n_prev_mes_anterior>0)
													?"\n(devia ser hoje ".number_format($n_prev_mes_anterior, 0, ',', '').
													(($n_prev_mes_corrente!=$n_prev_mes_anterior)?" ou ".number_format($n_prev_mes_corrente, 0, ',', ''):"").
													")"
												:""
										)."'"
									:""
							);
				$v_next_value = $ndays_in_month*(1000*floor( (($aVendasMes[$i]/$ndays_in_month))/1000+1));
				$stitle_v = (
								($_SESSION["tipo_acesso_pub"]=='AT')
									?" title='R$".number_format($aVendasMes[$i]/$ndays_in_month, 2, ',', '.')."/dia em $ndays_in_month dias".
										(
											((b_IsUsuarioReinaldo()) && $v_prev_mes_anterior>0)
													?"\n(devia ser hoje R$".number_format($v_prev_mes_anterior, 2, ',', '.').
													(($v_prev_mes_corrente!=$v_prev_mes_anterior)?" ou R$".number_format($v_prev_mes_corrente, 2, ',', '.'):"").
													")"
												:""
											)
											.((b_IsUsuarioReinaldo())?"\n"."Próximo valor diário: R$".number_format($v_next_value, 2, ',', '.'):"")
											."'"
									:""
							);
                                $line[] = $aNVendasMes[$i];
                                $line[] = number_format($aVendasMes[$i], 2, ',', '.');
                                $objCsv->setLine(implode(";",$line));
                                $line = array();
				echo "<td align='center'$stitle_n><b>".$aNVendasMes[$i]."</b></td>";
				echo "<td align='center'$stitle_v><b>".number_format($aVendasMes[$i], 2, ',', '.')."</b></td>";
				echo "</tr>";
			}

//                        $line[] = LANG_STATISTICS_TOTALS;
                                
			// SubTotais
			echo "<tr bgcolor='#FFFFCC'><td align='right'><b>".LANG_STATISTICS_TOTALS."&nbsp;</b></td>";
			for($j=0;$j<count($aCanais);$j++) {
				echo "<td align='center'>".$aNVendasTotal[$aCanais[$j]]."</td> <td align='center'><b>".number_format($aVendasTotal[$aCanais[$j]], 2, ',', '.')."</b></td>";
//                                $line[] = $aNVendasTotal[$aCanais[$j]];
//                                $line[] = number_format($aVendasTotal[$aCanais[$j]], 2, ',', '.');
			}
                        
//                        $line[] = $n;
//                        $line[] = number_format($vendas, 2, ',', '.');
                        
			echo "<td align='center'><b>".$n."</b></td> <td align='center'><b>".number_format($vendas, 2, ',', '.')."</b></td>";
			echo "</tr>";
                        
//                        $objCsv->setLine(implode(";",$line));
//                        $line = array();
//                        $line[] = "%";
			// Percentagens
			echo "<tr bgcolor='#FFFFCC'><td align='right'><b>%&nbsp;</b></td>";
			for($j=0;$j<count($aCanais);$j++) {
				echo "<td align='center'>".number_format((100*$aNVendasTotal[$aCanais[$j]]/getValueNonZero($n)), 0, ',', '.')."</td> <td align='center'><b>".number_format((100*$aVendasTotal[$aCanais[$j]]/getValueNonZero($vendas)), 0, ',', '.')."</b></td>";
//                                $line[] = number_format((100*$aNVendasTotal[$aCanais[$j]]/getValueNonZero($n)), 0, ',', '.');
//                                $line[] = number_format((100*$aVendasTotal[$aCanais[$j]]/getValueNonZero($vendas)), 0, ',', '.');
			}
			echo "<td align='center' bgcolor='#FFFFFF'>&nbsp;</td><td align='center' bgcolor='#FFFFFF'>&nbsp;</td>";
			echo "</tr>";
			echo "</table>";
//                        $objCsv->setLine(implode(";",$line));
                        

		} else {
			echo "<tr bgcolor='".$bg_col."'><td align='center' colspan='5'><font color='#FF0000'>".LANG_STATISTICS_NOT_FOUND_REGISTER_MSG_." &nbsp;</font></td></tr>";
		}
                
                $csv = $objCsv->export();

?>
<br>
<table border='0' cellpadding='0' cellspacing='1' width='897' bordercolor='#cccccc' style='border-collapse:collapse;'>	
  <tr align="center"> 
	<td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td><td bgcolor="#FFFFFF" class="texto"><span id="hammer" onClick="toggle_view();"><?php echo LANG_STATISTICS_INFO_MSG; ?></span> <?php echo LANG_STATISTICS_INFO_MSG_5; ?>.</font><br></td><td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td>
  </tr>
  <tr align="center"> 
	<td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td><td bgcolor="#FFFFFF" class="texto"><?php  echo LANG_STATISTICS_SEARCH_MSG." ". number_format(getmicrotime() - $time_start, 2, '.', '.')." ".LANG_STATISTICS_SEARCH_MSG_UNIT; ?></font></td><td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td>
  </tr>
<?php
    if(isset($csv)){ ?>
        <tr>
            <td colspan="5" class="text-center" bgcolor="#FFFFFF"><a href="/includes/downloadCsv.php?csv=<?php print $csv;?>&dir=cache"><input class="btn downloadCsv btn-info" type="button" value="Download CSV"></a></td>
        </tr>
<?php   } ?>
</table>

<?php  }
?>
    </div>
</div>
<?php  require_once $raiz_do_projeto . "public_html/sys/includes/rodape_sys.php"; ?>
</body>
</html>

<?php 
// ===============================
function getChannelName($ch) {
        $sName = "???";
        switch($ch) {
                case 'C':
                        $sName = LANG_STATISTICS_CARDS;
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