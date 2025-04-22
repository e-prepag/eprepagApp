<?php 
    if(isset($_GET["downloadCsv"]) && $_GET["downloadCsv"] == 1)
        ob_start();

    require_once "../../../../../includes/constantes.php";
    require_once $raiz_do_projeto . "public_html/sys/includes/topo_sys.php";  
	$pos_pagina = $seg_auxilar;
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
	}

	$data_inicial_limite = data_menos_n(date('d/m/Y'), 120);
	$data_inicial_limite = '01/08/2004';
	$FrmEnviar = 1;
	
	
	$default_add  = nome_arquivo($PHP_SELF);
	$img_proxima  = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/proxima.gif";
	$img_anterior = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/anterior.gif";
	$max          = 100; //$qtde_reg_tela;
	$range_qtde   = $qtde_range_tela;

	if($cb_opr_teste)
		$resopr = pg_exec($connid, "select opr_nome, opr_codigo from operadoras where (opr_status = '".$operadora_ativada."') order by opr_ordem");
	else
		$resopr = pg_exec($connid, "select opr_nome, opr_codigo from operadoras where (opr_status = '".$operadora_ativada."') and (opr_codigo <> ".$opr_teste.") order by opr_ordem");

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

		$where_data_2 = "";
		$where_valor_2 = "";
		$where_opr_1 = "";
		$where_opr_2 = "";
		$where_valor_10 = "";
		$where_valor_13 = "";
		$where_valor_25 = "";
		$where_valor_37 = "";
		$where_valor_49 = "";

		if($tf_data_inicial && $tf_data_final) 
		{
			$data_inic = formata_data(trim($tf_data_inicial), 1);
			$data_fim = formata_data(trim($tf_data_final), 1); 
			$where_data_2 = " and ((vc_data >= '".trim($data_inic)." 00:00:00') and (vc_data <= '".trim($data_fim)." 23:59:59')) "; 
		}


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
//echo $where_opr_1 ."<br>".	$where_opr_2 ."<br>";

		if($dd_operadora_nome=="") $dd_valor = "";

		if($dd_valor) {
			if($dd_valor=='10') {
				$where_valor_10 = " and ( TRUE ) ";
				$where_valor_13 = " and ( FALSE ) ";
				$where_valor_25 = " and ( FALSE ) ";
				$where_valor_37 = " and ( FALSE ) ";
				$where_valor_49 = " and ( FALSE ) ";
			} elseif($dd_valor=='13') {
				$where_valor_10 = " and ( FALSE ) ";
				$where_valor_13 = " and ( TRUE ) ";
				$where_valor_25 = " and ( FALSE ) ";
				$where_valor_37 = " and ( FALSE ) ";
				$where_valor_49 = " and ( FALSE ) ";
			} elseif($dd_valor=='25') {
				$where_valor_10 = " and ( FALSE ) ";
				$where_valor_13 = " and ( FALSE ) ";
				$where_valor_25 = " and ( TRUE ) ";
				$where_valor_37 = " and ( FALSE ) ";
				$where_valor_49 = " and ( FALSE ) ";
			} elseif($dd_valor=='37') {
				$where_valor_10 = " and ( FALSE ) ";
				$where_valor_13 = " and ( FALSE ) ";
				$where_valor_25 = " and ( FALSE ) ";
				$where_valor_37 = " and ( TRUE ) ";
				$where_valor_49 = " and ( FALSE ) ";
			} elseif($dd_valor=='49') {
				$where_valor_10 = " and ( FALSE ) ";
				$where_valor_13 = " and ( FALSE ) ";
				$where_valor_25 = " and ( FALSE ) ";
				$where_valor_37 = " and ( FALSE ) ";
				$where_valor_49 = " and ( TRUE ) ";
			} 
		}

		$sqlwhere = $where_data_2.$where_valor_2;

		$estat  = "select trn_data, opr_nome, pin_valor, sum(n) as quantidade, sum(vendas1) as total_face
					from (
						select vc_data::date as trn_data, vc_total_mu_online as n, vc_total_mu_online*10 as vendas1, '10' as pin_valor, 'MUONLINE' as opr_nome 
						from dist_vendas_cartoes_tmp 
						where vc_total_mu_online>0 ".$sqlwhere.$where_opr_1.$where_valor_10."
						union all
						select vc_data::date as trn_data, vc_total_5k as n, vc_total_5k*13 as vendas1, '13' as pin_valor, 'ONGAME' as opr_nome
						from dist_vendas_cartoes_tmp 
						where vc_total_5k>0 ".$sqlwhere.$where_opr_2.$where_valor_13."
						union all
						select vc_data::date as trn_data, vc_total_10k as n, vc_total_10k*25 as vendas1, '25' as pin_valor, 'ONGAME' as opr_nome
						from dist_vendas_cartoes_tmp 
						where vc_total_10k>0 ".$sqlwhere.$where_opr_2.$where_valor_25."
						union all
						select vc_data::date as trn_data, vc_total_15k as n, vc_total_15k*37 as vendas1, '37' as pin_valor, 'ONGAME' as opr_nome
						from dist_vendas_cartoes_tmp 
						where vc_total_15k>0 ".$sqlwhere.$where_opr_2.$where_valor_37."
						union all
						select vc_data::date as trn_data, vc_total_20k as n, vc_total_20k*49 as vendas1, '49' as pin_valor, 'ONGAME' as opr_nome
						from dist_vendas_cartoes_tmp 
						where vc_total_20k>0 ".$sqlwhere.$where_opr_2.$where_valor_49."
					) v ";				
		$estat  .= " group by trn_data, opr_nome, pin_valor ";

		
		$res_count = pg_query($estat);
		$total_table = pg_num_rows($res_count);
	
		$estat .= " order by ".$ncamp; 

		if($ordem == 0)
		{
			$estat .= " desc ";
			$img_seta = "glyphicon glyphicon-menu-down top0";	
		}
		else
		{
			$estat .= " asc ";
			$img_seta = "glyphicon glyphicon-menu-up top0";
		}

		$qtde_geral = 0;
		$valor_geral = 0;

//echo str_replace("\n","<br>\n",$estat)."<br>";
		$res_geral = pg_exec($connid, $estat);
		while($pg_geral = pg_fetch_array($res_geral))
		{
			$qtde_geral += $pg_geral['quantidade'];
			$valor_geral += $pg_geral['total_face'];
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
	
	$resestat = pg_exec($connid, $estat);

	if($max + $inicial > $total_table)
		$reg_ate = $total_table;
	else
		$reg_ate = $max + $inicial;
		
	$varsel  = "&cb_opr_teste=$cb_opr_teste&cb_estab_teste=$cb_estab_teste";
	$varsel .= "&tf_data_final=$tf_data_final&tf_data_inicial=$tf_data_inicial";
	$varsel .= "&tf_codigo_estab=$tf_codigo_estab&tf_nome_estab=$tf_nome_estab&dd_uf=$dd_uf&dd_uf_except=$dd_uf_except";
	$varsel .= "&dd_operadora_nome=$dd_operadora_nome&dd_valor=$dd_valor&dd_opr_area=$dd_opr_area";
?>
<html>
<head>
<title>E-Prepag</title>
<script language="JavaScript">
<!--
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}
//-->
</script>
<link rel="stylesheet" href="/sys/css/css.css" type="text/css">
<link href="/includes/bootstrap/css/bootstrap.min_new.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/js/jquery.js"></script>
<script type="text/javascript" src="/includes/bootstrap/js/bootstrap.min.js"></script>
<link href="/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="/js/jquery-ui-1.9.2.custom.min.js"></script>
</head>
<body>
<!-- INICIO CODIGO NOVO -->
<div class="container-fluid">
    <div class="container txt-azul-claro bg-branco">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12 ">
                        <strong><?php echo LANG_REPORTS_PAGE_TITLE; ?></strong>
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
                <div class="row txt-cinza espacamento">
                    <div class="col-md-2">
                        <?php echo LANG_PINS_START_DATE; ?>
                    </div>
                    <div class="col-md-2">
                        <?php echo LANG_PINS_END_DATE; ?>
                    </div>
                    <div class="col-md-3">
                        <?php echo LANG_REPORTS_OPERATOR; ?>
                    </div>
                    <div class="col-md-3">
                        <?php echo LANG_REPORTS_VALUE; ?>
                    </div>
                </div>
                <div class="row txt-cinza">
                    <form name="form1" method="post" action="">
                        <div class="col-md-2">
                            <input  alt="Calendário" name="tf_data_inicial" type="text" class="form-control data" id="tf_data_inicial" value="<?php  echo $tf_data_inicial ?>" size="9" maxlength="10">
                        </div>
                        <div class="col-md-2">
                            <input alt="Calendário" name="tf_data_final" type="text" class="form-control data" id="tf_data_final" value="<?php  echo $tf_data_final ?>" size="9" maxlength="10">
                        </div>
                        <div class="col-md-3">
    <?php 
                            if($_SESSION["tipo_acesso_pub"]=='PU') 
                            {
                                echo $_SESSION["opr_nome"];
    ?>
                                <input type="hidden" name="dd_operadora_nome" id="dd_operadora_nome" value="<?php echo $dd_operadora_nome?>">
    <?php 
                            } else 
                            {
    ?>
                            <select name="dd_operadora_nome" id="dd_operadora_nome" class="form-control" onChange="document.form1.dd_valor.value='';document.form1.submit()">
                                <option value=""><?php echo LANG_REPORTS_ALL_OPERATOR; ?></option>
                                <option value="MUONLINE" <?php if('MUONLINE' == $dd_operadora_nome) echo "selected" ?>>MUONLINE</option>
                                <option value="ONGAME" <?php if('ONGAME' == $dd_operadora_nome) echo "selected" ?>>ONGAME</option>
                            </select>
    <?php 
                            } 
    ?>
                        </div>
                        <div class="col-md-3">
                            <select name="dd_valor" id="dd_valor" class="form-control" onChange="document.form1.submit()">
                                <option value=""><?php echo LANG_REPORTS_ALL_VALUE; ?></option>
                                <?php if($dd_operadora_nome=='MUONLINE') { ?>
                                <option value="10" <?php if('10' == $dd_valor) echo "selected" ?>>R$10.00</option>
                                <?php } elseif($dd_operadora_nome=='ONGAME') { ?>
                                <option value="13" <?php if('13' == $dd_valor) echo "selected" ?>>R$13.00</option>
                                <option value="25" <?php if('25' == $dd_valor) echo "selected" ?>>R$25.00</option>
                                <option value="37" <?php if('37' == $dd_valor) echo "selected" ?>>R$37.00</option>
                                <option value="49" <?php if('49' == $dd_valor) echo "selected" ?>>R$49.00</option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" name="BtnSearch" value="<?php echo LANG_PINS_SEARCH_2; ?>" class="btn pull-right btn-success"><?php echo LANG_INTEGRATION_SEARCH;?></button>
                        </div>
                    </form>
                </div>
                <div class="row txt-cinza espacamento">
                    <div class="col-md-12 bg-cinza-claro">
<?php  
                    if($data_inic_invalida == true) echo '<div class="row txt-cinza txt-vermelho">'.LANG_REPORTS_START_DATE."</div>";
                    if($data_fim_invalida == true) echo '<div class="row txt-cinza txt-vermelho">'.LANG_REPORTS_END_DATE."</div>";
                    if($data_inicial_menor == true) echo '<div class="row txt-cinza txt-vermelho">'.LANG_REPORTS_COMP_DATE_START_WITH_END."</div>";

                    if($total_table > 0) 
                    {
                        $_SESSION['sqldata']=$sql_transform;
                        
                        $cabecalho = "'".LANG_REPORTS_DATE."','".LANG_REPORTS_OPERATOR."','".LANG_REPORTS_QUANTITY."','".LANG_REPORTS_FACE_VALUE."','".LANG_REPORTS_TOTAL_VALUE."'";
                        
                        if($ordem == 1)
                            $ordem = 0;
                        else
                            $ordem = 1;
?>
                        <table id="table" class="table bg-branco txt-preto fontsize-p">
                        <thead>
                          <tr class="bg-cinza-claro">
                              <th class="text-center"><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=trn_data&inicial=".$inicial.$varsel ?>"><?php echo LANG_REPORTS_DATE; ?></a> <?php if($ncamp == 'trn_data') echo "<span class='".$img_seta."'></span>"; ?></th>
                              <th class="text-center"><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=opr_nome&inicial=".$inicial.$varsel ?>"><?php echo LANG_REPORTS_OPERATOR; ?></a> <?php if($ncamp == 'opr_nome') echo "<span class='".$img_seta."'></span>"; ?></div></th>
                              <th class="text-right"><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=quantidade&inicial=".$inicial.$varsel ?>"><?php echo LANG_REPORTS_QUANTITY; ?></a><?php if($ncamp == 'quantidade') echo "<span class='".$img_seta."'></span>"; ?></th>
                              <th class="text-right"><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=pin_valor&inicial=".$inicial.$varsel ?>"><?php echo LANG_REPORTS_FACE_VALUE; ?></a><?php if($ncamp == 'pin_valor') echo "<span class='".$img_seta."'></span>"; ?></th>
                              <th class="text-right"><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=total_face&inicial=".$inicial.$varsel ?>"><?php echo LANG_REPORTS_TOTAL_VALUE; ?></a><?php if($ncamp == 'total_face') echo "<span class='".$img_seta."'></span>"; ?></th>
                          </tr>
                          <tr>
                            <th colspan="5">
                                <?php echo ' '.LANG_SHOW_DATA.' '; ?> <strong><?php  echo $inicial + 1 ?></strong><?php echo ' '.LANG_TO.' '; ?><strong><?php  echo $reg_ate ?></strong><?php echo ' '.LANG_FROM.' '; ?><strong><?php  echo $total_table ?> </strong>
                            </th>
                          </tr>
                        </thead>
                        <tbody>
<?php
                        require_once $raiz_do_projeto."/class/util/CSV.class.php";
                        $cabecalho = "Data;Operadora;Qtde;Valor de Face;Valor Total";
                        $objCsv = new CSV($cabecalho, md5(uniqid()), $raiz_do_projeto . "public_html/cache/");
                        $objCsv->setCabecalho();

                        while ($pgrow = pg_fetch_array($resestat))
                        {
                            $valor = true;

                            $valor_total_tela += $pgrow['total_face'];
                            $qtde_total_tela += $pgrow['quantidade'];
                            $lineCsv = array();
                            if(isset($pgrow['trn_data']))                 $lineCsv[] = formata_data($pgrow['trn_data'], 0);
                            if(isset($pgrow['opr_nome']))                 $lineCsv[] = $pgrow['opr_nome'];
                            if(isset($pgrow['quantidade']))                 $lineCsv[] = $pgrow['quantidade'];
                            if(isset($pgrow['pin_valor']))                 $lineCsv[] = number_format($pgrow['pin_valor'], 2, ',', '.');
                            if(isset($pgrow['total_face']))                 $lineCsv[] = number_format($pgrow['total_face'], 2, ',', '.');

                            if(is_array($lineCsv)) $objCsv->setLine(implode(";",$lineCsv));
?>
                            <tr class="trListagem">
                                <td class="text-center"><?php echo formata_data($pgrow['trn_data'], 0) ?></td>
                                <td class="text-center"><?php echo $pgrow['opr_nome'] ?></td>
                                <td class="text-right"><?php echo $pgrow['quantidade'] ?></td>
                                <td class="text-right"><?php echo number_format($pgrow['pin_valor'], 2, ',', '.') ?></td>
                                <td class="text-right"><?php echo number_format($pgrow['total_face'], 2, ',', '.') ?></td>
                            </tr>
<?php
                      }
                      
                        if($reg_ate >= $total_table && !isset($_REQUEST["inicial"]))
                            $csv = $objCsv->export();
                      
                        if(!$valor)
                        {
?>
                            <tr bgcolor="#f5f5fb"> 
                                <td colspan="5"><?php echo LANG_NO_DATA; ?>.</td>
                            </tr>
<?php  
                        } else { 
                            $time_end = getmicrotime();
                            $time = $time_end - $time_start;
?>
                            <tr class="bg-cinza-claro"> 
                                <td colspan="3">SUBTOTAL</td>
                                <td class="text-right"><strong><?php echo number_format($qtde_total_tela, 0, ',', '.') ?></strong></td>
                                <td class="text-right" colspan="4"><strong><?php echo number_format($valor_total_tela, 2, ',', '.') ?></strong></td>
                            </tr>
                            <tr class="bg-cinza-claro">
                                <td colspan="5" class="fontsize-pp"><?php echo LANG_PINS_SEARCH_MSG.' '.number_format($time, 2, '.', '.').' '.LANG_PINS_SEARCH_MSG_UNIT; ?></td>
                            </tr>
                            <tr class="bg-cinza-claro"> 
                                <td colspan="5" class="fontsize-pp"><?php echo date('Y-m-d H:i:s'); ?></td>
                            </tr>
<?php  
                            paginacao_query($inicial, $total_table, $max, '5', $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel);
                        
                            if(isset($_GET["downloadCsv"]))
                            {
                                require_once $raiz_do_projeto."public_html/includes/downloadCsv.php";
                            }
                            elseif(isset($csv))
                            {
                                $csv = "/includes/downloadCsv.php?csv=$csv&dir=cache";
                            }elseif($total_table > 0)
                            {
                                $csv = "/sys/admin/vendas_cartoes/vendas_estab/pquery.php?downloadCsv=1&".$varsel;//http_build_query($_POST);
                            }

                            if(isset($csv))
                            { 
?>
                        <tr class="bg-cinza-claro">
                            <td colspan="5" class="text-center pdt-15">
                                <a href="<?php echo $csv;?>" target="_blank"><input class="btn downloadCsv btn-info" type="button" value="Download CSV"></a>
                            </td>
                        </tr>
<?php 
                            } 
                        } 
?>
                        </tbody>
                    </table>
<?php
                    }
                    elseif($BtnSearch) 
                    {  
                         echo LANG_NO_DATA.".";
                    }
?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="/js/global.js"></script>
<script language="JavaScript">
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
<?php
	#include "../../../incs/footer.php";
?>