<?php  
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
    if(isset($_GET["downloadCsv"]) && $_GET["downloadCsv"] == 1){
        ob_start();
        set_time_limit(3600);
    }
    
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
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

	if(!$ncamp) $ncamp = 've_estabelecimento';


	if(!$tf_data_inicial)  {
		$resdatainicio = pg_exec($connid, "select ve_data_inclusao from dist_vendas_pos order by ve_data_inclusao limit 1");
		if($pgdatainicio = pg_fetch_array ($resdatainicio)) {
			$tf_data_inicial = substr($pgdatainicio['ve_data_inclusao'],8,2)."/".substr($pgdatainicio['ve_data_inclusao'],5,2)."/".substr($pgdatainicio['ve_data_inclusao'],0,4);
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
	if($BtnSearch)         $inicial         = 0;
	if($BtnSearch)         $range           = 1;
	if($BtnSearch)         $total_table     = 0;

	$data_inicial_limite = data_menos_n(date('d/m/Y'), 120);
	$data_inicial_limite = '01/08/2004';
	$FrmEnviar = 1;
	
//echo "tf_data_inicial: $tf_data_inicial<br>";	
//echo "tf_data_final: $tf_data_final<br>";	
//echo "data_inicial_limite: $data_inicial_limite<br>";	

	$default_add  = nome_arquivo($PHP_SELF);
	$img_proxima  = "/sys/imagens/proxima.gif";
	$img_anterior = "/sys/imagens/anterior.gif";
	$max          = 200; //$qtde_reg_tela;
	$range_qtde   = $qtde_range_tela;

//	$resuf = pg_exec($connid, "select uf from uf order by uf");
//	$resuf_except = pg_exec($connid, "select uf from uf order by uf");


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
		$where_estabelecimento = "";
		$where_estabtipo = "";
		$where_cidade = "";
		$where_estado = "";

		if($tf_data_inicial && $tf_data_final) 
		{
			$data_inic = formata_data(trim($tf_data_inicial), 1);
			$data_fim = formata_data(trim($tf_data_final), 1); 
			$where_data = " and ((ve_data_inclusao >= '".trim($data_inic)." 00:00:00') and (ve_data_inclusao <= '".trim($data_fim)." 23:59:59')) "; 
		}

		if($dd_estabelecimento) {
			$where_estabelecimento = " and (ve_estabelecimento like '".str_replace("'", "''", $dd_estabelecimento)."') ";
		}

		if($dd_valor) {
			$where_valor= " and (ve_valor=$dd_valor) ";
		}

		if($dd_cidade) {
			$where_cidade= " and (ve_cidade='$dd_cidade') ";
		}

		if($dd_estado) {
			$where_estado= " and (ve_estado='$dd_estado') ";
		}

		if($dd_estabtipo) {
			$where_estabtipo= " and (ve_estabtipo='$dd_estabtipo') ";
		}

		if($dd_operadora) {
			if(($dd_operadora=="OG") || ($dd_operadora=="HB") || ($dd_operadora=="MU"))
				$where_opr = " and (ve_jogo='$dd_operadora') ";
		}
		if($dd_operadora=="") $dd_valor = "";
//echo "dd_valor: ".$dd_valor."<br>";


		$estat  = "select ve_estabelecimento, ve_estabtipo, ve_cidade, ve_estado, count(*) as ve_n, max(ve_data_inclusao) as ve_data_ultima, sum(ve_valor) as total from dist_vendas_pos ";
		$estat  .= " where 1=1 ".$where_data." ".$where_valor." ".$where_opr." ".$where_estabelecimento." ".$where_estabtipo." ".$where_cidade." ".$where_estado;		
		$estat  .= " group by ve_estabelecimento, ve_estabtipo, ve_cidade, ve_estado ";
	

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

                if(!isset($_GET["downloadCsv"])){
                    $estat .= " limit ".$max; 
                    $estat .= " offset ".$inicial;
                }


	}
		
//	trace_sql($estat, "Arial", 2, "#666666", 'b');
$sql_transform=$estat;

//echo "SQL: $estat<br>";

	$resestat = pg_exec($connid, $estat);

	if($max + $inicial > $total_table)
		$reg_ate = $total_table;
	else
		$reg_ate = $max + $inicial;

	$varsel = "&dd_operadora=$dd_operadora&tf_data_inicial=$tf_data_inicial&tf_data_final=$tf_data_final&dd_valor=$dd_valor";
	$varsel .= "&dd_estabelecimento=$dd_estabelecimento&dd_vendas=$dd_vendas&dd_ultima_vendas=$dd_ultima_vendas";
		
?>
<html>
<head>

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
  document.formlista.action = "pos_detalhe_insere.php";
//alert("op: "+document.formlista.op.value+", id:"+document.formlista.id.value+", action: "+document.formlista.action+"");
  document.formlista.submit();
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

<div class="container-fluid">
    <div class="container txt-azul-claro bg-branco">
        <div class="row txt-cinza-claro">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12 ">
                        <strong><?php echo LANG_POS_PAGE_TITLE; ?></strong>
                    </div>
                </div>
                <div class="row txt-cinza">
                    <div class="col-md-6">
                        <span class="pull-left"><strong><?php echo LANG_POS_SEARCH_1; ?></strong></span>
                    </div>
                    <div class="col-md-6">
                        <span class="pull-right"><a href="/sys/admin/commerce/index.php" class="btn btn-primary"><strong><i><?php echo LANG_BACK; ?></i></strong></a></span>
                    </div>
                </div>
                <form name="formlista" method="post">
                    <input type="hidden" name="op" id="op" value="">
                    <input type="hidden" name="id" id="id" value="">
                    <input type="hidden" name="ncamp" id="ncamp" value="<?php echo $ncamp?>">
                    <div class="row txt-cinza">
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_POS_SALES_START_DATE; ?></span>
                        </div>
                        <div class="col-md-3">
                            <input  alt="Calendário" name="tf_data_inicial" type="text" class="form-control w-ipt-medium pull-left data" id="tf_data_inicial" value="<?php  echo $tf_data_inicial ?>" size="9" maxlength="10">
                        </div>
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_POS_SALES_END_DATE; ?></span>
                        </div>
                        <div class="col-md-3">
                            <input alt="Calendário" name="tf_data_final" type="text" class="form-control w-ipt-medium pull-left data" id="tf_data_final" value="<?php  echo $tf_data_final ?>" size="9" maxlength="10">
                        </div>
                    </div>
                    <div class="row txt-cinza top10">
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_POS_ESTABLISHMENT; ?></span>
                        </div>
                        <div class="col-md-3">
                            <select name="dd_estabelecimento" id="dd_estabelecimento" class="form-control" onChange="document.formlista.submit()">
                                <option value=""><?php echo LANG_POS_ALL_ESTABLISHMENT; ?></option>
<?php 
                                    $resusuario = pg_exec($connid, "select ve_estabelecimento, count(*) as ve_n from dist_vendas_pos group by ve_estabelecimento order by ve_estabelecimento");  
                                    while ($pgusuario = pg_fetch_array ($resusuario)) 
                                    {
?>
                                <option value="<?php echo $pgusuario['ve_estabelecimento'] ?>" <?php if($pgusuario['ve_estabelecimento'] == $dd_estabelecimento) echo "selected" ?>><?php echo substr($pgusuario['ve_estabelecimento'],0,20) ?> <?php echo " (".$pgusuario['ve_n']." vendas)";?></option>
<?php 
                                    } 
?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_POS_ESTABLISHMENT_TYPE; ?></span>
                        </div>
                        <div class="col-md-3">
                            <select name="dd_estabtipo" id="dd_estabtipo" class="form-control" onChange="document.formlista.submit()">
                                <option value=""><?php echo LANG_POS_ALL_TYPES; ?></option>
<?php 
                        $resestabtipo = pg_exec($connid, "select ve_estabtipo, count(*) as ve_n from dist_vendas_pos group by ve_estabtipo order by ve_estabtipo");  
                        while ($pgestabtipo = pg_fetch_array ($resestabtipo)) 
                        {
?>
                                <option value="<?php echo $pgestabtipo['ve_estabtipo'] ?>" <?php if($pgestabtipo['ve_estabtipo'] == $dd_estabtipo) echo "selected" ?>><?php echo substr($pgestabtipo['ve_estabtipo'],0,20) ?> <?php echo " (".$pgestabtipo['ve_n']." vendas)";?></option>
<?php 
                        }
?>
                            </select>
                        </div>
                    </div>
                    <div class="row txt-cinza top10">
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_POS_CITY; ?></span>
                        </div>
                        <div class="col-md-3">
                            <select name="dd_cidade" id="dd_cidade" class="form-control" onChange="document.formlista.submit()">
                                <option value=""><?php echo LANG_POS_ALL_CITIES; ?></option>
<?php 
                                $rescidade = pg_exec($connid, "select ve_cidade, count(*) as ve_n from dist_vendas_pos group by ve_cidade order by ve_cidade");  
                                while ($pgcidade = pg_fetch_array ($rescidade)) 
                                {
?>
                                <option value="<?php echo $pgcidade['ve_cidade'] ?>" <?php if($pgcidade['ve_cidade'] == $dd_cidade) echo "selected" ?>><?php echo substr($pgcidade['ve_cidade'],0,20) ?> <?php echo " (".$pgcidade['ve_n']." vendas)";?></option>
<?php 
                                } 
?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_POS_STATES; ?></span>
                        </div>
                        <div class="col-md-3">
                            <select name="dd_estado" id="dd_estado" class="form-control" onChange="document.formlista.submit()">
                                <option value=""><?php echo LANG_POS_ALL_CITIES; ?></option>
<?php 
                                $resestado = pg_exec($connid, "select ve_estado, count(*) as ve_n from dist_vendas_pos group by ve_estado order by ve_estado");
                                while ($pgestado = pg_fetch_array ($resestado)) 
                                { 
?>
                                <option value="<?php echo $pgestado['ve_estado'] ?>" <?php if($pgestado['ve_estado'] == $dd_estado) echo "selected" ?>><?php echo substr($pgestado['ve_estado'],0,20) ?> <?php echo " (".$pgestado['ve_n']." vendas)";?></option>
<?php 
                                } 
?>
                            </select>
                        </div>
                    </div>
                    <div class="row txt-cinza top10">
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_POS_OPERATOR; ?></span>
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
                            <select name="dd_operadora" id="dd_operadora" class="form-control" onChange="document.formlista.dd_valor.value='';document.formlista.submit()">
                                <option value=""<?php if(($dd_operadora!="OG") && ($dd_operadora!="MU") && ($dd_operadora!="HB")) echo "selected" ?>><?php echo LANG_POS_ALL_OPERATOR; ?></option>
                                <option value="OG"<?php if($dd_operadora=="OG") echo "selected" ?>>ONGAME (13)</option>
                                <option value="HB"<?php if($dd_operadora=="HB") echo "selected" ?>>HABBO HOTEL (16)</option>
                                <option value="MU"<?php if($dd_operadora=="MU") echo "selected" ?>>MU ONLINE (17)</option>
                            </select>
<?php
                        } 
?>
                        </div>
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_POS_VALUE; ?></span>
                        </div>
                        <div class="col-md-3">
                            <select name="dd_valor" id="dd_valor" class="form-control" onChange="document.formlista.submit()">
<?php 
                            if($dd_operadora=="MU") 
                            {
?>
                                <option value=""<?php if($dd_valor!=10) echo "selected" ?>><?php echo LANG_POS_ALL_VALUE; ?></option>
                                <option value="10"<?php if($dd_valor==10) echo "selected" ?>>R$ 10,00</option>
<?php 
                            } 
                            
                            if($dd_operadora=="OG") 
                            {
?>
                                <option value=""<?php if(($dd_valor!=13) && ($dd_valor!=25) && ($dd_valor!=37) && ($dd_valor!=49)) echo "selected" ?>><?php echo LANG_POS_ALL_VALUE; ?></option>
                                <option value="13"<?php if($dd_valor==13) echo "selected" ?>>R$ 13,00</option>
                                <option value="25"<?php if($dd_valor==25) echo "selected" ?>>R$ 25,00</option>
                                <option value="37"<?php if($dd_valor==37) echo "selected" ?>>R$ 37,00</option>
                                <option value="49"<?php if($dd_valor==49) echo "selected" ?>>R$ 49,00</option>
<?php 
                            }
                            
                            if($dd_operadora=="HB") 
                            {
?>
                                <option value=""<?php if(($dd_valor!=10) && ($dd_valor!=25) && ($dd_valor!=50)) echo "selected" ?>><?php echo LANG_POS_ALL_VALUE; ?></option>
                                <option value="10"<?php if($dd_valor==10) echo "selected" ?>>R$ 10,00</option>
                                <option value="25"<?php if($dd_valor==25) echo "selected" ?>>R$ 25,00</option>
                                <option value="50"<?php if($dd_valor==50) echo "selected" ?>>R$ 50,00</option>
<?php 
                            }
                            
                            if(($dd_operadora!="OG") && ($dd_operadora!="MU") && ($dd_operadora!="HB")) 
                            {
?>
                                <option value=""><?php echo LANG_POS_ALL_VALUE; ?></option>
<?php 
                            }
?>

                            </select>
                        </div>
                    </div>
                    <div class="row txt-cinza top10 text-right">
                        <div class="col-md-12">
                            <button type="submit" name="BtnSearch" value="<?php echo LANG_POS_SEARCH_2; ?>" class="btn pull-right btn-success"><?php echo LANG_POS_SEARCH_2;?></button>
                        </div>
                    </div>
                </form>
                <div class="row txt-cinza espacamento">
                    <div class="col-md-12 bg-cinza-claro">
                        <table id="table" class="table bg-branco txt-preto fontsize-p">
                            <thead>
<?php
                                if($ordem == 1)
                                    $ordem = 0;
				else
                                    $ordem = 1;
?>
                                <tr class="bg-cinza-claro">
                                    <th class="text-center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ve_estabelecimento&inicial=".$inicial.$varsel ?>"><?php echo LANG_POS_ESTABLISHMENT; ?></a></strong><?php if($ncamp == 've_estabelecimento') echo "<span class='".$img_seta."'></span>"; ?></th>
                                    <th class="text-center"><strong><?php echo LANG_POS_TYPE; ?></strong></th>
                                    <th class="text-center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ve_cidade&inicial=".$inicial.$varsel ?>"><?php echo LANG_POS_CITY; ?></a></strong><?php if($ncamp == 've_cidade') echo "<span class='".$img_seta."'></span>"; ?></th>
                                    <th class="text-center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ve_estado&inicial=".$inicial.$varsel ?>"><?php echo LANG_POS_STATE; ?></a></strong><?php if($ncamp == 've_estado') echo "<span class='".$img_seta."'></span>"; ?></th>
                                    <th class="text-center"><strong><?php echo LANG_POS_PHONES; ?></strong></th>
                                    <th class="text-center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ve_n&inicial=".$inicial.$varsel ?>"><?php echo LANG_POS_SALES_NUMBER; ?></a></strong><?php if($ncamp == 've_n') echo "<span class='".$img_seta."'></span>"; ?></th>
                                    <th class="text-center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ve_data_ultima&inicial=".$inicial.$varsel ?>"><?php echo LANG_POS_LAST_SALE; ?></a></strong><?php if($ncamp == 've_data_ultima') echo "<span class='".$img_seta."'></span>"; ?></th>
                                    <th class="text-center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=total&inicial=".$inicial.$varsel ?>"><?php echo LANG_POS_TOTAL; ?> (R$)</a></strong><?php if($ncamp == 'total') echo "<span class='".$img_seta."'></span>"; ?></th>
                                </tr>
                            </thead>
<?php
                            $_SESSION['sqldata']=$sql_transform;
                            
                            if($total_table > 0) 
                            {                            
?>                                
                            <tr>
                                <th colspan="6">
<?php 
                                    echo LANG_SHOW_DATA.' '; 
?>
                                    <strong><?php echo $inicial + 1 ?></strong> 
                                    <?php echo ' '.LANG_TO.' '; ?><strong><?php echo $reg_ate ?></strong><?php echo ' '.LANG_FROM.' ' ?><strong><?php echo $total_table ?></strong>
                                </th>
                            </tr>
                            <tbody>
<?php
                                require_once $raiz_do_projeto."/class/util/CSV.class.php";
                                        
                                $cabecalho = LANG_POS_ESTABLISHMENT.";".LANG_POS_TYPE.";".LANG_POS_CITY.";".LANG_POS_STATE.";".LANG_POS_PHONES.";".LANG_POS_SALES_NUMBER.";".LANG_POS_LAST_SALE.";".LANG_POS_TOTAL;

                                $objCsv = new CSV($cabecalho, md5(uniqid()), $raiz_do_projeto."public_html/cache/");
                                $objCsv->setCabecalho();
                                
                                while ($pgrow = pg_fetch_array($resestat))
                                {	
                                    $valor = true;

                                    $telefones = "select ve_estabelecimento, ve_estabtipo, ve_cidade, ve_estado, ve_ddd, ve_tel from dist_vendas_pos where ve_estabelecimento='".str_replace("'","\'",$pgrow['ve_estabelecimento'])."' and ve_estabtipo='".$pgrow['ve_estabtipo']."' and ve_cidade='".$pgrow['ve_cidade']."' and ve_estado='".$pgrow['ve_estado']."' and not (ve_tel is null) group by ve_estabelecimento, ve_estabtipo, ve_cidade, ve_estado, ve_ddd, ve_tel";
        //echo $telefones."<br>";
                                    $restelefones = pg_exec($connid, $telefones);

                                    $bvirgula = false;
                                    $t = "";
                                    while ($pgtel = pg_fetch_array($restelefones)) 
                                    {
                                        if($bvirgula) $t .= ", ";
                                        if(!$bvirgula) $bvirgula = true;
                                        $t .= "<nobr>(".$pgtel['ve_ddd'].") ".$pgtel['ve_tel']."</nobr>";
                                    }

                                    $lineCsv = array();
                                    $lineCsv[] = $pgrow['ve_estabelecimento'];
                                    $lineCsv[] = $pgrow['ve_estabtipo'];
                                    $lineCsv[] = $pgrow['ve_cidade'];
                                    $lineCsv[] = $pgrow['ve_estado'];
                                    $lineCsv[] = str_replace(array("<nobr>","</nobr>"),array("",""),$t);
                                    $lineCsv[] = $pgrow['ve_n'];
                                    $lineCsv[] = $pgrow['ve_data_ultima'];
                                    $lineCsv[] = number_format($pgrow['total'], 2, ',', '.');

                                    if(is_array($lineCsv)) $objCsv->setLine(implode(";",$lineCsv));
?>
                                    <tr class="trListagem"> 
                                        <td class="text-left"><a href="pos_lista_detalhe.php?dd_estabelecimento1=<?php echo $pgrow['ve_estabelecimento']."&ordem=".$ordem."&ncamp=".$ncamp."&inicial=".$inicial.$varsel?>"><?php echo $pgrow['ve_estabelecimento']?></a></td>
                                        <td class="text-left"><?php echo $pgrow['ve_estabtipo']?></td>
                                        <td class="text-left"><?php echo $pgrow['ve_cidade']?></td>
                                        <td class="text-center"><?php echo $pgrow['ve_estado']?></td>
                                        <td class="text-center"><?php echo $t ?></td>
                                        <td class="text-center"><?php echo $pgrow['ve_n']?></td>
                                        <td class="text-center"><nobr><?php echo $pgrow['ve_data_ultima']?></nobr></td>
                                        <td class="text-center"><?php echo number_format($pgrow['total'], 2, ',', '.')?></td>
                                    </tr>
<?php  
                                    }

                                    if($reg_ate >= $total_table && !isset($_REQUEST["inicial"]))
                                        $csv = $objCsv->export();

                                    if(isset($_GET["downloadCsv"]))
                                    {
                                        require_once $raiz_do_projeto."public_html/includes/downloadCsv.php";
                                    }elseif(isset($csv))
                                    {
                                        $csv = "/includes/downloadCsv.php?csv=$csv&dir=cache";
                                    }elseif($total_table > 0)
                                    {
                                        $csv = "/sys/admin/stats/POS_lista.php?downloadCsv=1".$varsel;
                                    }

                                    if(isset($csv))
                                    {
?>
                                        <tr class="text-center">
                                            <td colspan="8"><a target="_blank" href="<?php print $csv;?>"><input class="btn downloadCsv btn-info " type="button" value="Download CSV"></a></td>
                                        </tr>
<?php   
                                    }
                                
                                    $time_end = getmicrotime();
                                    $time = $time_end - $time_start;

                                    paginacao_query($inicial, $total_table, $max, '8', $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel);
?>
                                <tr class="bg-cinza-claro"> 
                                    <td colspan="8"><?php echo LANG_POS_SEARCH_MSG.' '.number_format($time, 2, '.', '.').' '.LANG_POS_SEARCH_MSG_UNIT; ?></td>
                                </tr>
<?php
                            }else
                            {
?>
                                <tr>
                                    <td colspan="<?php echo $colspan?>"><strong><?php echo LANG_NO_DATA; ?>.</strong></td>
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
<?php require_once $raiz_do_projeto . "public_html/sys/includes/rodape_sys.php"; ?>
</body>
</html>