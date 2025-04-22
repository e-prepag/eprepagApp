<?php
	require_once "../../../../../includes/constantes.php";
        require_once $raiz_do_projeto . "public_html/sys/includes/topo_sys.php";    
	//include "../../../incs/configuracao.inc";
	$pos_pagina = $seg_auxilar;
	
	//include "../../../connections/connect.php";
	//include "../../../incs/header.php";
    //include "../../../incs/functions.php";
	$time_start = getmicrotime();

//echo "dd_operadora: ".$dd_operadora."<br>";
//echo "Submit: $Submit<br>";
	if($_SESSION["tipo_acesso_pub"]=='PU') {
		$dd_operadora = $_SESSION["opr_codigo_pub"];
		if($dd_operadora==13) $dd_operadora_nome = "ONGAME";
		elseif($dd_operadora==17) $dd_operadora_nome = "MUONLINE";
//		elseif($dd_operadora==16) $dd_operadora_nome = "HABBOHOTEL";
		else $dd_operadora_nome = "????";
		$Submit = "Buscar";
	}
//echo "dd_operadora: ".$dd_operadora."<br>";
//echo "dd_operadora_nome: ".$dd_operadora_nome."<br>";

	if(!$ncamp)    $ncamp       = 'mes';
	if(!$dd_ano)   $dd_ano      = date('Y');
	if(!$ordem)    $ordem       = 0;
	if($BtnSearch && $BtnSearch!=1 )
            $total_table = 0;
	

	$where_opr_1 = "";
	$where_opr_2 = "";
	$where_ano_1 = "";
	$where_ano_2 = "";

	if (($dd_operadora!=13) && ($dd_operadora!=17) && ($_SESSION["tipo_acesso_pub"]=='PU')) {
		$where_opr_1 = " and ( FALSE )";
		$where_opr_2 = " and ( FALSE ) ";
	}
	if($dd_operadora_nome) {
		if($dd_operadora_nome=='ONGAME') {
			$where_opr_1 = " and ( FALSE )";
			$where_opr_2 = " and ( TRUE ) ";
		} elseif($dd_operadora_nome=='MUONLINE') {
			$where_opr_1 = " and ( TRUE )";
			$where_opr_2 = " and ( FALSE ) ";
		} 
	}

	if($dd_ano) {
		$where_ano_1 = " and (extract (year from vc_data) = ".$dd_ano.") ";
		$where_ano_2 = " and (extract (year from vc_data) = ".$dd_ano.") ";
	}

	$sql  = "select extract (month from vc_data) as mes, extract (year from vc_data) as ano, sum(n) as quantidade, sum (vendas1) as total 
				from (
					select vc_data, vc_total_mu_online as n, vc_total_mu_online*10 as vendas1, '10' as pin_valor, 'MUONLINE' as opr_nome 
					from dist_vendas_cartoes_tmp 
					where vc_total_mu_online>0 ".$where_opr_1.$where_ano_1."
					union all
					select vc_data, vc_total_5k as n, vc_total_5k*13 as vendas1, '13' as pin_valor, 'ONGAME' as opr_nome
					from dist_vendas_cartoes_tmp 
					where vc_total_5k>0 ".$where_opr_2.$where_ano_2."
					union all
					select vc_data, vc_total_10k as n, vc_total_10k*25 as vendas1, '25' as pin_valor, 'ONGAME' as opr_nome
					from dist_vendas_cartoes_tmp 
					where vc_total_10k>0 ".$where_opr_2.$where_ano_2."
					union all
					select vc_data, vc_total_15k as n, vc_total_15k*37 as vendas1, '37' as pin_valor, 'ONGAME' as opr_nome
					from dist_vendas_cartoes_tmp 
					where vc_total_15k>0 ".$where_opr_2.$where_ano_2."
					union all
					select vc_data, vc_total_20k as n, vc_total_20k*49 as vendas1, '49' as pin_valor, 'ONGAME' as opr_nome
					from dist_vendas_cartoes_tmp 
					where vc_total_20k>0 ".$where_opr_2.$where_ano_2."
			) v ";	
		
	$sql .= " group by mes, ano "; 

	$sql .= " order by ano desc, mes desc";
	
//echo str_replace("\n","<br>\n",$sql)."<br>";

	
	$res_count = pg_query($sql);
	$total_table = pg_num_rows($res_count);

//	$sql .= " order by ".$ncamp;
	
	$ordem = 0;
	$img_seta = "/sys/imagens/seta_down.gif";	
//	if($ordem == 0)
//	{
//		$sql .= " desc ";
//		$img_seta = "../../../images/seta_down.gif";	
//	}
//	else
//	{
//		$sql .= " asc ";
//		$img_seta = "../../../images/seta_up.gif";
//	}

//	$sql .= " limit ".$max; 
//	$sql .= " offset ".$inicial;

//echo "$sql<br>";

	$resmes = pg_exec($connid, $sql);
	
	if($_SESSION["tipo_acesso_pub"]=='PU') {
		$sqlopr = "select opr_nome, opr_codigo from operadoras where (opr_status = '1') and (opr_codigo='".$dd_operadora."') order by opr_ordem";
	} else {
		$sqlopr = "select opr_nome, opr_codigo from operadoras where (opr_status != '0') order by opr_ordem";
	}
	$resopr = pg_exec($connid, $sqlopr);
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
<body bgcolor="#FFFFFF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<!-- INICIO CODIGO NOVO -->
<link rel="stylesheet" href="/sys/css/css.css" type="text/css">
<link href="/includes/bootstrap/css/bootstrap.min_new.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/js/jquery.js"></script>
<script type="text/javascript" src="/includes/bootstrap/js/bootstrap.min.js"></script>
<script type="text/javascript" src="/js/table2CSV.js"></script>
<div class="container-fluid">
    <div class="container txt-azul-claro bg-branco">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12 ">
                        <strong><?php echo LANG_REPORTS_PAGE_TITLE_3; ?></strong>
                    </div>
                </div>
                <div class="row txt-cinza">
                    <div class="col-md-6">
                        <span class="pull-left"><strong><?php echo LANG_REPORTS_SEARCH_1; ?></strong></span>
                    </div>
                    <div class="col-md-6">
                        <span class="pull-right"><a href="/sys/admin/commerce/index.php" class="btn btn-primary"><strong><i><?php echo LANG_BACK; ?></i></strong></a></span>
                    </div>
                </div>
                <form name="form1" method="post" action="">
                <div class="row txt-cinza espacamento">
                    <div class="col-md-3">
                        <?php echo LANG_REPORTS_OPERATOR; ?>
                    </div>
                    <div class="col-md-2">
                        <?php echo LANG_YEAR_2; ?>
                    </div>
                </div>
                <div class="row txt-cinza">
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
                        <select name="dd_operadora_nome" id="dd_operadora_nome" class="form-control" onChange="document.form1.submit()">
                            <option value=""><?php echo LANG_REPORTS_ALL_OPERATOR; ?></option>
                            <option value="MUONLINE" <?php if('MUONLINE' == $dd_operadora_nome) echo "selected" ?>>MUONLINE</option>
                            <option value="ONGAME" <?php if('ONGAME' == $dd_operadora_nome) echo "selected" ?>>ONGAME</option>
                        </select>
<?php
                    } 
?>
                    </div>
                    <div class="col-md-2">
                        <select name="dd_ano" id="dd_ano" class="form-control" onChange="document.form1.submit()">
                            <?php for($i =  date('Y'); $i >= (int)(substr($inic_oper_data, 6)) ; $i--) { ?>
                                <option value="<?php echo $i ?>" <?php if($dd_ano == $i) echo "selected" ?>><?php echo $i ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <button type="submit" name="BtnSearch" value="<?php echo LANG_PINS_SEARCH_2; ?>" class="btn pull-right btn-success"><?php echo LANG_PINS_SEARCH_2;?></button>
                    </div>
                </div>
                </form>
                <div class="row txt-cinza espacamento">
                    <div class="col-md-12 bg-cinza-claro">
                        <table id="table" class="table bg-branco txt-preto fontsize-p">
                        <thead>
                          <tr class="bg-cinza-claro">
                            <th><?php echo LANG_MONTH_2; ?></th>
                            <th class="text-right"><?php echo LANG_REPORTS_QUANTITY; ?></th>
                            <th class="text-right"><?php echo LANG_REPORTS_TOTAL_VALUE; ?></th>
                          </tr>
                        </thead>
                        <tbody>
<?php
                        
                        if($ordem == 1)
                            $ordem = 0;
                        else
                            $ordem = 1;
			
                        $cabecalho = "'".LANG_MONTH_2."','".LANG_REPORTS_QUANTITY."','".LANG_REPORTS_TOTAL_VALUE."'";
                        
                        while ($pgmes = pg_fetch_array($resmes))
                        {
                            $valor = true;

                            $pin_total_valor += $pgmes['total'];
                            $pin_total_qtde += $pgmes['quantidade'];
                            
                            switch($pgmes['mes'])
                            {
                                    case 1:  $mes_nome = LANG_JANUARY; break;
                                    case 2:  $mes_nome = LANG_FEBRUARY; break;
                                    case 3:  $mes_nome = LANG_MARCH; break;
                                    case 4:  $mes_nome = LANG_APRIL; break;
                                    case 5:  $mes_nome = LANG_MAY; break;
                                    case 6:  $mes_nome = LANG_JUNE; break;
                                    case 7:  $mes_nome = LANG_JULY; break;
                                    case 8:  $mes_nome = LANG_AUGUST; break;
                                    case 9:  $mes_nome = LANG_SEPTEMBER; break;
                                    case 10: $mes_nome = LANG_OCTOBER; break;
                                    case 11: $mes_nome = LANG_NOVEMBER; break;
                                    case 12: $mes_nome = LANG_DECEMBER; break;
                            }
?>
                            <tr class="trListagem"> 
                                <td><?php echo $mes_nome." / ".$pgmes['ano'] ?></td>
                                <td class="text-right"><?php echo number_format($pgmes['quantidade'], 0, ',', '.') ?></td>
                                <td class="text-right"><?php echo number_format($pgmes['total'], 2, ',', '.') ?></td>
                            </tr>
<?php
                        }
                        if(!$valor) { 
?>
                            <tr class="bg-cinza-claro">
                                <td colspan="3"><?php echo LANG_NO_DATA; ?>.</td>
                            </tr>
<?php 
                        } else {
?>
                            <tr class="bg-cinza-claro">
                                <td><?php echo LANG_REPORTS_TOTAL_1; ?></td>
                                <td class="text-right"><?php echo number_format($pin_total_qtde, 0, ',', '.') ?></td>
                                <td class="text-right"><?php echo number_format($pin_total_valor, 2, ',', '.') ?></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="bg-cinza-claro fontsize-pp">
                                    <?php echo LANG_REPORTS_LAST_MSG; ?>
                                </td>
                            </tr>
<?php
                        }
                        $time_end = getmicrotime();
                        $time = $time_end - $time_start;
?>
<!--                            <tr class="bg-cinza-claro">
                                <td colspan="3">
                                    <strong><?php LANG_PINS_LAST_MSG; ?>.</strong>
                                </td>
                            </tr>-->
                            <tr class="bg-cinza-claro">
                                <td colspan="3" class="fontsize-pp"><?php echo LANG_PINS_SEARCH_MSG.' '.number_format($time, 2, '.', '.').' '.LANG_PINS_SEARCH_MSG_UNIT; ?></td>
                            </tr>
                            <tr class="bg-cinza-claro"> 
                                <td colspan="3" class="fontsize-pp"><?php echo date('Y-m-d H:i:s'); ?></td>
                            </tr>
                        </tbody>
                      </table>
<?php
                        if($valor) {
?>
                         <div  class = "text-center ptb-15">
                            <a href="#" class="btn downloadCsv btn-info">Download CSV</a>
                        </div>
<?php
                        }
?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
require_once $raiz_do_projeto . "public_html/sys/includes/footer.php";
require_once $raiz_do_projeto . "public_html/sys/includes/rodape_sys.php";
?>
<script>
$(function(){
    $('#table').table2CSV({header:[<?php echo $cabecalho; ?>],toStr:""});
});
</script>
<!-- FIM CODIGO NOVO -->
</body>
</html>
