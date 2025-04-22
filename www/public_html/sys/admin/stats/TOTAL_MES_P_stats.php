<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

require_once "../../../../includes/constantes.php";
require_once $raiz_do_projeto . "public_html/sys/includes/topo_sys.php";

require_once $raiz_do_projeto . "includes/sys/inc_stats.php";

$time_start_stats = getmicrotime();

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
$possui_totalizacao_utilizacao = false;
if($dd_operadora) {
        $resopr_nome = pg_exec($connid, "select opr_nome, opr_contabiliza_utilizacao, opr_data_inicio_contabilizacao_utilizacao from operadoras where (opr_status = '1') and (opr_codigo='".$dd_operadora."') order by opr_ordem");
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

if($_SESSION["tipo_acesso_pub"]=='PU') {
        $sqlopr = "select opr_nome, opr_codigo from operadoras where (opr_status = '1') and (opr_codigo='".$dd_operadora."') order by opr_ordem";
} else {
        $sqlopr = "select opr_nome, opr_codigo from operadoras where (opr_status != '0') order by opr_ordem";
}
$resopr = pg_exec($connid, $sqlopr);
?>
<!doctype html public "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title> <?php echo LANG_STATISTICS_TOTAL_SALES; ?> </title>
    <link href="/includes/bootstrap/css/bootstrap.min_new.css" rel="stylesheet" type="text/css" />
    <script type="text/javascript" src="/js/jquery.js"></script>
    <script type="text/javascript" src="/includes/bootstrap/js/bootstrap.min.js"></script>
    <link href="/sys/css/css.css" rel="stylesheet" type="text/css">
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
</head>
<body>
<div class="container-fluid">
    <div class="container txt-azul-claro bg-branco txt-cinza fontsize-p">
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
                <span class="pull-right"><a href="../commerce/index.php" class="btn btn-primary"><strong><i><?php echo LANG_BACK; ?></i></strong></a></span>
            </div>
        </div>
        <form name="form1" method="post" action="">
        <div class="row txt-cinza top10">
            <div class="col-md-2">
                <span class="pull-right"><?php echo LANG_STATISTICS_REPORT_TYPE; ?></span>
            </div>
            <div class="col-md-3">    
<?php 
            if($_SESSION["tipo_acesso_pub"]=='PU') 
            {
?>
		<span style="font-weight: bold"><?php echo LANG_STATISTICS_OUT; ?></span>
		<input type="hidden" name="dd_mode" id="dd_mode" value="<?=$dd_mode?>">
<?php 
            } else 
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
?>
		<span style="font-weight: bold"><?=$_SESSION["opr_nome"]?></span>
		<input type="hidden" name="dd_operadora" id="dd_operadora" value="<?=$dd_operadora?>">
<?php 
            } else
            { 
?>	
                <select name="dd_operadora" id="dd_operadora" class="form-control" onChange="document.form1.submit()">
                        <option value=""><?php echo LANG_STATISTICS_ALL_OPERATOR; ?></option>
                        <?php while ($pgopr = pg_fetch_array ($resopr)) {  ?>
                        <option value="<?php echo $pgopr['opr_codigo'] ?>" <?php if($pgopr['opr_codigo'] == $dd_operadora) echo "selected" ?>><?php echo $pgopr['opr_nome'] ?></option>
                        <?php } ?>
                </select>
<?php 
            } 
?>
            </div>
        </div>
        </form>
<?php

$iday = date("d"); // or any value from 1-12
$imonth = date("n"); // or any value from 1-12
$iyear	= date("Y"); // or any value >= 1
$days_in_month = date("t",mktime(0,0,0,$imonth,1,$iyear));
$days_in_month_prev = date("t",mktime(0,0,0,$imonth-1,1,$iyear));

$twomonthsago  = mktime(0, 0, 0, date("m")-2, date("d"), date("Y"));

// Cria array de meses ====================================================================================
$thismonth  = mktime(0, 0, 0, date("m"), 1, date("Y"));
$firstmonth  = mktime(0, 0, 0, 1, 1, 2008);
$i = 0;
$currentmonth = $thismonth;
while($currentmonth >=$firstmonth) {
        $aMonths[$i++] = $currentmonth;
        $currentmonth = mktime(0, 0, 0, date("m", $currentmonth)-1, 1, date("Y",$currentmonth));
}

$bg_col_01 = "#FFFFFF";
$bg_col_02 = "#EEEEEE";
$bg_col = $bg_col_01;

// Cria array de canais ====================================================================================
$aCanais = array("C", "S", "L", "P");	//array("C", "E", "M", "L", "P");

//Capturando informações de dados de totalização por utilização
if($possui_totalizacao_utilizacao) {
    //echo "ID: ".$dd_operadora." => DATA: [".substr($opr_data_inicio_contabilizacao_utilizacao,0,19)."]<br>";
    $where_opr_venda_lan = " AND ( CASE  WHEN vgm.vgm_opr_codigo = $dd_operadora THEN vg.vg_data_inclusao < '".substr($opr_data_inicio_contabilizacao_utilizacao,0,19)."' ELSE vg.vg_data_inclusao > '2008-01-01 00:00:00' END ) ";
    $where_opr_venda_lan_negativa = " AND ( CASE WHEN vgm.vgm_opr_codigo = $dd_operadora THEN vg.vg_data_inclusao >= '".substr($opr_data_inicio_contabilizacao_utilizacao,0,19)."' ELSE FALSE END ) ";
    $where_opr_utilizacao_lan = " AND ( CASE  WHEN pih_id = $dd_operadora THEN pih_data >= '".substr($opr_data_inicio_contabilizacao_utilizacao,0,19)."' ELSE FALSE END ) ";
} //end if($possui_totalizacao_utilizacao)
else {
    $where_opr_venda_lan = "";
    $where_opr_venda_lan_negativa = "";
    $where_opr_utilizacao_lan = "";
}//end else do if($possui_totalizacao_utilizacao)

// Totais por mes ========================================================================================
$sql_total_mes = get_sql_total_mes($extra_where, false, $smode, $dd_year, false, $where_origem, $possui_totalizacao_utilizacao);

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
                $scanal = strval($vendas_total_mes_row[0]);
                if((strcmp($scanal,"E")==0) || (strcmp($scanal,"M")==0)) $scanal='S';

                $bMonthFound = false;
                for($m=0;$m<count($aMonths);$m++) {
                        if ((strval(substr($vendas_total_mes_row[1],0,19))==strval(date("Y-m-d H:i:s",$aMonths[$m])))) {
                                $bMonthFound = true;
                                break;
                        }
                }
                if($bMonthFound) {
                        $aNVendas[$scanal][$m] += $vendas_total_mes_row[2];
                        $aVendas[$scanal][$m] += $vendas_total_mes_row[3];
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
        //echo "<!-- ".print_r($aVendas)." -->".PHP_EOL;
        echo "<table border='1' cellpadding='10' cellspacing='10' bordercolor='#cccccc' style='border-collapse:collapse; width:100%;' class='txt-cinza fontsize-p'>".PHP_EOL;
        echo "<tr class='bg-cinza-claro'><th>&nbsp;</th>".PHP_EOL;
        for($j=0;$j<count($aCanais);$j++) {
                echo "<th colspan='2' align='center' class='txt-azul-claro'><b>".getChannelName($aCanais[$j])."</b></th>".PHP_EOL;
                $cabecalho[] = getChannelName($aCanais[$j]);
                $cabecalho[] = getChannelName($aCanais[$j]);
        }
        
        $cabecalho[] = "Total";
        $cabecalho[] = "Total";
        
        echo "<th colspan='2' align='center' class='txt-azul-claro'><b>Total</b></th>".PHP_EOL;
        echo "</tr>".PHP_EOL;

        require_once $raiz_do_projeto."class/util/CSV.class.php";
                                        
        $objCsv = new CSV(implode(";", $cabecalho), md5(uniqid()), $raiz_do_projeto."public_html/cache/");
        $objCsv->setCabecalho();

        $line = array();
        $line[] = LANG_MONTH_2;
        
        echo "<tr align='center' class='txt-azul-claro bg-cinza-claro'><td><b>".LANG_MONTH_2."</b></td>".PHP_EOL;
        for($j=0;$j<count($aCanais);$j++) {
                echo "<td align='center'>n</td><td align='center'>".LANG_STATISTICS_SALES_1." (R$)</td>".PHP_EOL;
                $line[] = "n";
                $line[] = LANG_STATISTICS_SALES_1." (R$)";
        }
        
        $line[] = "n";
        $line[] = LANG_STATISTICS_SALES_1." (R$)";
        echo "<td align='center'>n</td><td align='center'>".LANG_STATISTICS_SALES_1." (R$)</td>".PHP_EOL;
        echo "</tr>".PHP_EOL;

        $objCsv->setLine(implode(";",$line));
        $line = array();
        $line[] = LANG_STATISTICS_PROJECTION." ".mes_do_ano2($aMonths[0]);
        
        // Calcula Projeção por canal ======================================
        echo "<tr class='trListagem'><td><b>".LANG_STATISTICS_PROJECTION." ".mes_do_ano2($aMonths[0])."</b></td>".PHP_EOL;
        for($j=0;$j<count($aCanais);$j++) {
                $nvendasPrj = ($aNVendas[$aCanais[$j]][0] + ($aNVendas[$aCanais[$j]][1]*($days_in_month-$iday)/$days_in_month_prev) );
                $vendasPrj = ($aVendas[$aCanais[$j]][0] + ($aVendas[$aCanais[$j]][1]*($days_in_month-$iday)/$days_in_month_prev) );

                $ipos = ((100*( $nvendasPrj - $aNVendas[$aCanais[$j]][1]) ) /getValueNonZero($aNVendas[$aCanais[$j]][1]));
                $sarrow = "<img src='/sys/imagens/".(($ipos>0)?"arrow_up_2.gif":(($ipos<0)?"arrow_down_2.gif":"equal.gif"))."' width='12' height='12' border='0' alt='".(($ipos>0)?"Up":(($ipos<0)?"Down":"Equal"))."'>".PHP_EOL;
                echo "<td align='center'>".number_format($nvendasPrj, 0, ',', '')."&nbsp;<br>".$sarrow."&nbsp;".(($ipos>0)?"+":(($ipos<0)?"":"&nbsp;")).number_format($ipos, 0, ',', '')."%</td>".PHP_EOL;

                $ipos = ((100*( $vendasPrj - $aVendas[$aCanais[$j]][1]) ) / getValueNonZero($aVendas[$aCanais[$j]][1]));
                $sarrow = "<img src='/sys/imagens/".(($ipos>0)?"arrow_up_2.gif":(($ipos<0)?"arrow_down_2.gif":"equal.gif"))."' width='12' height='12' border='0' alt='".(($ipos>0)?"Up":(($ipos<0)?"Down":"Equal"))."'>".PHP_EOL;
                echo "<td align='center'>".number_format($vendasPrj, 2, ',', '.')."&nbsp;<br>".$sarrow."&nbsp;".(($ipos>0)?"+":(($ipos<0)?"":"&nbsp;")).number_format($ipos, 0, ',', '')."%</td>".PHP_EOL;
                
                $line[] = number_format($nvendasPrj, 0, ',', '')." ".(($ipos>0)?"+":(($ipos<0)?"":" ")).number_format($ipos, 0, ',', '')."%";
                $line[] = number_format($vendasPrj, 2, ',', '.')." ".(($ipos>0)?"+":(($ipos<0)?"":" ")).number_format($ipos, 0, ',', '')."%";
        }

        // Calcula Projeção Total ======================================
        $nvendasPrj = ($aNVendasMes[0] + ($aNVendasMes[1]*($days_in_month-$iday)/$days_in_month_prev) );
        $vendasPrj = ($aVendasMes[0] + ($aVendasMes[1]*($days_in_month-$iday)/$days_in_month_prev)) ;
        
        
                
        $ipos = 100*( ( $nvendasPrj - $aNVendasMes[1] ) /  getValueNonZero($aNVendasMes[1]) );
        $sarrow = "<img src='/sys/imagens/".(($ipos>0)?"arrow_up_2.gif":(($ipos<0)?"arrow_down_2.gif":"equal.gif"))."' width='12' height='12' border='0' alt='".(($ipos>0)?"Up":(($ipos<0)?"Down":"Equal"))."'>".PHP_EOL;
        echo "<td align='center'><b>".number_format($nvendasPrj, 0, ',', '')."&nbsp;<br>".$sarrow."&nbsp;".(($ipos>0)?"+":(($ipos<0)?"":"&nbsp;")).number_format($ipos, 0, ',', '')."%</b></td>".PHP_EOL;

        $ipos = 100*( ( $vendasPrj - $aVendasMes[1] ) /  getValueNonZero($aVendasMes[1]) );
        $sarrow = "<img src='/sys/imagens/".(($ipos>0)?"arrow_up_2.gif":(($ipos<0)?"arrow_down_2.gif":"equal.gif"))."' width='12' height='12' border='0' alt='".(($ipos>0)?"Up":(($ipos<0)?"Down":"Equal"))."'>".PHP_EOL;
        echo "<td align='center'><b>".number_format($vendasPrj, 2, ',', '.')."&nbsp;<br>".$sarrow."&nbsp;".(($ipos>0)?"+":(($ipos<0)?"":"&nbsp;")).number_format($ipos, 0, ',', '')."%</b></td>".PHP_EOL;

        echo "</tr>".PHP_EOL;
        $line[] = number_format($nvendasPrj, 0, ',', '')."  ".(($ipos>0)?"+":(($ipos<0)?"":" ")).number_format($ipos, 0, ',', '')."%";
        $line[] = number_format($vendasPrj, 2, ',', '.')."  ".(($ipos>0)?"+":(($ipos<0)?"":" ")).number_format($ipos, 0, ',', '')."%";
        
        $objCsv->setLine(implode(";",$line));
        $line = array();
        
        $bg_col = $bg_col_01;
        for($i=0;$i<count($aMonths);$i++) {
                $line[] = mes_do_ano2($aMonths[$i]);
                
                echo "<tr class='trListagem'><td><b>".mes_do_ano2($aMonths[$i])."</b></td>".PHP_EOL;
                $bg_col = ($bg_col==$bg_col_01)?$bg_col_02:$bg_col_01;
                for($j=0;$j<count($aCanais);$j++) {
                        $line[] = $aNVendas[$aCanais[$j]][$i];
                        $line[] = number_format($aVendas[$aCanais[$j]][$i], 2, ',', '.');
                        echo "<td align='center'>".$aNVendas[$aCanais[$j]][$i]."</td>".PHP_EOL;
                        echo "<td align='center'>".number_format($aVendas[$aCanais[$j]][$i], 2, ',', '.')."</td>".PHP_EOL;
                }
                
                $line[] = $aNVendasMes[$i];
                $line[] = number_format($aVendasMes[$i], 2, ',', '.');
                echo "<td align='center'><b>".$aNVendasMes[$i]."</b></td>".PHP_EOL;
                echo "<td align='center'><b>".number_format($aVendasMes[$i], 2, ',', '.')."</b></td>".PHP_EOL;
                echo "</tr>".PHP_EOL;
                $objCsv->setLine(implode(";",$line));
                $line = array();
        }

//        $line[] = LANG_STATISTICS_TOTAL;
        // SubTotais
        echo "<tr bgcolor='#FFFFCC'><td align='right'><b>".LANG_STATISTICS_TOTAL."&nbsp;</b></td>".PHP_EOL;
        for($j=0;$j<count($aCanais);$j++) {
            
//                $line[] = $aNVendasTotal[$aCanais[$j]];
//                $line[] = number_format($aVendasTotal[$aCanais[$j]], 2, ',', '.');
                
                echo "<td align='center'>".$aNVendasTotal[$aCanais[$j]]."</td> <td align='center'><b>".number_format($aVendasTotal[$aCanais[$j]], 2, ',', '.')."</b></td>".PHP_EOL;
        }
//        $line[] = $n;
//        $line[] = number_format($vendas, 2, ',', '.');
//        $objCsv->setLine(implode(";",$line));
//        $line = array();
        
        echo "<td align='center'><b>".$n."</b></td> <td align='center'><b>".number_format($vendas, 2, ',', '.')."</b></td>".PHP_EOL;
        echo "</tr>".PHP_EOL;

//        $line[] = "%";
        // Percentagens
        echo "<tr bgcolor='#FFFFCC'><td align='right'><b>%&nbsp;</b></td>".PHP_EOL;
        for($j=0;$j<count($aCanais);$j++) {
                echo "<td align='center'>".number_format((100*$aNVendasTotal[$aCanais[$j]]/getValueNonZero($n)), 0, ',', '.')."</td> <td align='center'><b>".number_format((100*$aVendasTotal[$aCanais[$j]]/getValueNonZero($vendas)), 0, ',', '.')."</b></td>".PHP_EOL;
//                $line[] = number_format((100*$aNVendasTotal[$aCanais[$j]]/getValueNonZero($n)), 0, ',', '.');
//                $line[] = number_format((100*$aVendasTotal[$aCanais[$j]]/getValueNonZero($vendas)), 0, ',', '.');
        }
        
////        $line[] = "";
//        $line[] = "";
        $csv = $objCsv->export();
        echo "<td align='center' bgcolor='#FFFFFF'>&nbsp;</td><td align='center' bgcolor='#FFFFFF'>&nbsp;</td>".PHP_EOL;
        echo "</tr>".PHP_EOL;
        echo "</table>".PHP_EOL;


} else {
        echo "<tr bgcolor='".$bg_col."'><td align='center' colspan='5'><font color='#FF0000'>".LANG_NOT_FOUND_2." &nbsp;</font></td></tr>".PHP_EOL;
}


?>
<br>
<table border='0' cellpadding='0' cellspacing='1' bordercolor='#cccccc' style='border-collapse:collapse;'>	
<?php
    if(isset($csv)){ ?>
        <tr>
            <td colspan="2" bgcolor="#FFFFFF" class="text-center"><a href="/includes/downloadCsv.php?csv=<?php print $csv;?>&dir=cache"><input class="btn downloadCsv btn-info" type="button" value="Download CSV"></a></td>
        </tr>
<?php   } ?>
  <tr align="center"> 
	<td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td><td bgcolor="#FFFFFF" class="texto"><?php echo LANG_STATISTICS_INFO_MSG_4; ?>: <?php echo LANG_STATISTICS_INFO_MSG_5; ?>.</font><br></td><td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td>
  </tr>
  <tr align="center"> 
	<td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td><td bgcolor="#FFFFFF" class="texto"><?php echo LANG_STATISTICS_SEARCH_MSG." ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.')." ".LANG_STATISTICS_SEARCH_MSG_UNIT ?></font></td><td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td>
  </tr>
</table>

   <?php require_once $raiz_do_projeto . "public_html/sys/includes/rodape_sys.php"; ?>
    </div>
</div>            
</body>
</html>

<?php
	// ===============================
	function getChannelName($ch) {
		$sName = "???";
		switch($ch) {
			case 'S':
				$sName = "Site";
				break;
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