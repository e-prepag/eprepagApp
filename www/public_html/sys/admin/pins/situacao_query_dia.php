<?php
    require_once "../../../../includes/constantes.php";
    require_once $raiz_do_projeto . "public_html/sys/includes/topo_sys.php";

	set_time_limit ( 3000 ) ;

	if(!$ncamp) $ncamp = 'pin_codinterno';
	if(!$inicial)  $inicial     = 0;
	if(!$range)    $range       = 1;
	if($BtnSearch) $inicial     = 0;
	if($BtnSearch) $range       = 1;
	if($BtnSearch) $total_table = 0;

	$default_add  = nome_arquivo($PHP_SELF);
	$img_proxima  = "https://".$server_url_complete."/images/proxima.gif";
	$img_anterior = "https://".$server_url_complete."/images/anterior.gif";
	$max          = 6000; //$qtde_reg_tela;
	$range_qtde   = $qtde_range_tela;

	if(!$fpin) $fpin = '';
	if(!$fserial) $fserial = '';
	if(!$dd_opr_codigo) $dd_opr_codigo = '';
	if(!$tf_data_final)    $tf_data_final   = date('d/m/Y');
	if(!$tf_data_inicial)  $tf_data_inicial = date('d/m/Y');

	if(!$fcanal) $fcanal = 's';

//echo $num = cal_days_in_month(CAL_GREGORIAN, 5, 1979); 

//echo "dd_opr_codigo: ".$dd_opr_codigo."<br>";
	if($_SESSION["tipo_acesso_pub"]=='PU') {
		$dd_opr_codigo = $_SESSION["opr_codigo_pub"];
	}

//echo "dd_opr_codigo: ".$dd_opr_codigo."<br>";  
//echo "tf_data_inicial: ".$tf_data_inicial."<br>";  
//echo "tf_data_final: ".$tf_data_final."<br>";  

	$varsel = "&tf_data_inicial=$tf_data_inicial&tf_data_final=$tf_data_final&dd_opr_codigo=$dd_opr_codigo&tf_loteopr=$tf_loteopr&dd_status=$dd_status&tf_valor_total=$tf_valor_total&fserial=$fserial&fpin=$fpin&fcanal=$fcanal";


	if ($tf_pins && is_array($tf_pins)) {
		if (count($tf_pins) == 1) {
			$tf_pins = $tf_pins[0];
		} else {
			$tf_pins = implode("|",$tf_pins);
		}
	}
	if ($tf_pins && $tf_pins != "") {
		$tf_pins = explode("|",$tf_pins);	
	}
	if ($tf_pins && is_array($tf_pins)){
		$varsel_tf_pins = "";
		foreach($tf_pins as $key => $val) {
			$varsel_tf_pins .= "&tf_pins[]=$val";
		}
		$varsel .= $varsel_tf_pins;
	}
//echo "<pre>";
//print_r($tf_pins);
//echo "</pre><hr>";
//echo "dd_opr_codigo: ".$dd_opr_codigo."<br>";

//echo "varsel: $varsel<br>";
//echo "BtnSearch: $BtnSearch<br>";

	// Levanta lista de operadoras
	$sql  = "select opr_codigo, opr_nome from operadoras where opr_status='1' and opr_importa=1 order by opr_nome";
    $resopr = pg_exec($connid,$sql);


//	if(!$dd_pin_status) {
//		$dd_pin_status = "stVendido - TODOS";
//	}
//echo "dd_pin_status: ".$dd_pin_status."<br>";

	// Levanta lista de status	
	$sql  = "select stat_codigo, stat_descricao from pins_status order by stat_codigo;";
if($debug) {
//echo "sql : ".$sql ."<br>";
//echo "Elapsed time A1: ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.')."<br>";
//die("Stop 3434");
}
	$resstatus = pg_exec($connid,$sql);
	$a_status = array();
	while ($pgstatus = pg_fetch_array($resstatus)) { 
		$a_status[$pgstatus['stat_codigo']] = $pgstatus['stat_descricao'];
	}
	ksort($a_status);
//foreach($a_status as $key => $val) {echo $key." =&gt; ".$val."<br>";}
//die("Stop");

	// Levanta lista de valores
	$sql = "select pin_valor, count(*) as n from pins where 1=1 ";
	if($dd_opr_codigo) {
		$sql .= " and opr_codigo=".$dd_opr_codigo." ";
	}
	if($fcanal) {
		$sql .= " and pin_canal='".$fcanal."' ";
	}
	$sql .= " group by pin_valor ";
	$sql .= " order by pin_valor;";
if($debug) {
//echo "sql : ".$sql."<hr>";
echo "Elapsed time A1: ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.')."<br>";
//die("Stop 3434");
}

	$resvalue = pg_exec($connid,$sql);
	$a_valores = array();
	while ($pgvalue = pg_fetch_array($resvalue)) { 
		$a_valores[$pgvalue['pin_valor']] = $pgvalue['n'];
	}
	ksort($a_valores);
//foreach($a_valores as $key => $val) {echo $key." =&gt; ".$val."<br>";}
//die("Stop");

/*
select date_seq, sum(npins_pins) as quantidade, sum (valor_total_pins) as total
from 
	(select (generate_series(0,31) + date '2010-05-01') as date_seq) d
	left outer join
		(

		select t0.pin_datavenda, count(*) as npins_pins, sum(t0.pin_valor) as valor_total_pins from pins t0 
		where 1=1 
			and (pin_datavenda between '2010-05-01 00:00:00' and '2010-05-31 23:59:59') 
			and (t0.opr_codigo=35) 
			and (t0.pin_status='3') 
		group by t0.pin_datavenda 							

		) v
	on d.date_seq=v.pin_datavenda
group by date_seq
order by date_seq

*/

//echo "tf_data_inicial: '$tf_data_inicial'<br>";
//echo "tf_data_final: '$tf_data_final'<br>";
/*
//tf_data_inicial: '01/05/2010'
//tf_data_final: '31/05/2010'

$data_inicial = mktime(0,0,0,substr(trim($data_inic),0,4),substr(trim($data_inic),5,2),substr(trim($data_inic),0,4));
$data_final   = mktime(0,0,0,1,2,2007);

echo "Days difference = ".floor(($d2-$d1)/86400) . "<br>";
*/
$data_inic = formata_data(trim($tf_data_inicial), 1);
$data_fim = formata_data(trim($tf_data_final), 1); 
//echo "data_inic: '$data_inic'<br>";
//echo "data_fim: '$data_fim'<br>";
$f_date_inicial = strtotime($data_inic);
$f_date_final = strtotime($data_fim);
//echo "f_date_inicial: ".date("Y-m-d H:i:s",$f_date_inicial)."<br>";
//echo "f_date_final: ".date("Y-m-d H:i:s",$f_date_final)."<br>";

$ndays = intval(($f_date_final - $f_date_inicial)/86400);

//echo "difference2: $difference2<br>";

$sql = "";
$sql.="select date_seq, sum(npins_1) as quantidade, sum (valor_total_1) as total ";
$sql.="from  ";
$sql.="	(select (generate_series(0,".($ndays).") + date '".trim($data_inic)."') as date_seq) d ";
$sql.="	left outer join ";
$sql.="		( ";
	$sql.= "select t0.pin_datavenda, count(*) as npins_1, sum(t0.pin_valor) as valor_total_1 ";	
	$sql.= " from pins t0 ";	
	$sql.= " where 1=1 ";	
	
	if($tf_data_inicial) {
			$data_inic = formata_data(trim($tf_data_inicial), 1);
			$data_fim = formata_data(trim($tf_data_final), 1); 
			$sql .= " and (pin_datavenda between '".trim($data_inic)." 00:00:00' and  '".trim($data_fim)." 23:59:59')  \n"; 
	}

	//if(!trim($fpin) && !trim($fserial) && !($festab)) $sql.= "and (t0.pin_codigo='') and (t0.pin_serial='')  \n"; 
	//else{
		if($fserial)$sql .= " and (t0.pin_serial like '%".trim($fserial)."%')  \n"; 
		if($fpin)	$sql .= " and (t0.pin_codigo like '%".trim($fpin)."%')  \n";
//		if($festab)	$sql .= " and (t0.pin_est_codigo = ".$festab.")  \n";
		if($fcanal && $fcanal!='todos') $sql .= "and (t0.pin_canal='".$fcanal."') \n"; 
	//}

	if($dd_opr_codigo) $sql .= "and (t0.opr_codigo=".$dd_opr_codigo.")  \n";

	if($dd_pin_status) {
		if(($dd_pin_status=="stVendido - TODOS") || ($dd_pin_status=="stVendido-TODOS")) { 
			$sql .= " and (t0.pin_status='3' or t0.pin_status='6' or t0.pin_status='7')  \n";
		} else {
			$sql .= " and (t0.pin_status='".substr($dd_pin_status,2,1)."')  \n";
		}			
	}

	if ($tf_pins) {
		$sql .= " and (";
		for($i=0;$i<count($tf_pins);$i++) {
			$sql .= " (t0.pin_valor = ".$tf_pins[$i].")  ";
			if($i<count($tf_pins)-1) {
				$sql .= " or  ";
			}
		}
		$sql .= " ) ";
	}

	$sql .= " group by t0.pin_datavenda ";
//	$sql .= " order by pin_datavenda ";

	$sql .= "		) v ";
	$sql .= "	on d.date_seq=v.pin_datavenda ";
	$sql .= "group by date_seq ";
	$sql .= "order by date_seq ";


//echo "".str_replace("\n","<br>\n",$sql)."<br>\n<hr>";

	$resid_count = pg_exec($connid, $sql);
	$total_table = pg_num_rows($resid_count);

//echo "total_table: ".$total_table."<br>";
	$qtde_geral = 0;
	$valor_geral = 0;

//	$res_geral = pg_exec($connid, $sql);
	while($pg_geral = pg_fetch_array($resid_count))
	{
		$qtde_geral ++;
		$valor_geral += $pg_geral['pin_valor'];
	}

	$sql .= " limit ".$max." ";
	$sql .= " offset ".$inicial;

//if ($_SESSION['nome_bko']=="SUPORTE E-PREPAG") {
//if($_SESSION["tipo_acesso_pub"]!='PU') {
//echo str_replace("\n", "<br>\n", $sql)."<br>";
//}

//if ($_SESSION['nome_bko']=="SUPORTE E-PREPAG") {
//echo "Disponivel, ".LANG_PINS_STATUS_MSG_1."<br>";
//echo "Em processo, ".LANG_PINS_STATUS_MSG_2."<br>";
//echo "Vendido, ".LANG_PINS_STATUS_MSG_3."<br>";
//echo "Aguardando Liberação, ".LANG_PINS_STATUS_MSG_4."<br>";
//echo "E-Prepag, ".LANG_PINS_STATUS_MSG_5."<br>";
//echo "Desativado, ".LANG_PINS_STATUS_MSG_6."<br>";
//echo "Vendido – Lan House, ".LANG_PINS_STATUS_MSG_7."<br>";
//echo "Vendido - POS, ".LANG_PINS_STATUS_MSG_8."<br>";
//}

    //echo $sql;
	$resid = pg_exec($connid, $sql);


	if($max + $inicial > $total_table) $reg_ate = $total_table;
	else $reg_ate = $max + $inicial;

?>
<link rel="stylesheet" href="/sys/css/css.css" type="text/css">
<script language='javascript' src='/js/popcalendar.js'></script>
<script language="javascript" src="/js/jquery.js"></script>
<script language="JavaScript">
<!--
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}

function validade()
 {
/*
	if (document.form1.dd_opr_codigo.value == "" )
		{ window.alert("Por favor selecione a Operadora.");
	  document.form1.dd_opr_codigo.focus();
	  return false;
	}
*/
  return true;
}

	$(document).ready(function () {
		$('#dd_opr_codigo').change(function(){
			//var id = $(this).val();
			//alert(id);
			// reset values
			ResetCheckedValue();

			// values in dd_pin_status start with 'st' to avoid geting null when status = 0
			$.ajax({
				type: "POST",
				url: "/ajax/gamer/ajaxValorComPesquisaVendas.php",
				data: "id="+((document.getElementById('dd_opr_codigo').value>0)?document.getElementById('dd_opr_codigo').value:-1)+
					"&st="+document.getElementById('dd_pin_status').value.substring(2)+
					"&cn="+document.getElementById('fcanal').value,
				beforeSend: function(){
					$('#mostraValores').html("Aguarde...");
				},
				success: function(html){
					//alert('valor');
					$('#mostraValores').html(html);
				},
				error: function(){
					alert('erro valor');
				}
			});
		});

		$('#dd_pin_status').change(function(){
			
			// reset values
			ResetCheckedValue();

			// values in dd_pin_status start with 'st' to avoid geting null when status = 0
			$.ajax({
				type: "POST",
				url: "/ajax/gamer/ajaxValorComPesquisaVendas.php",
				data: "id="+((document.getElementById('dd_opr_codigo').value>0)?document.getElementById('dd_opr_codigo').value:-1)+
					"&st="+document.getElementById('dd_pin_status').value.substring(2)+
					"&cn="+document.getElementById('fcanal').value,
				beforeSend: function(){
					$('#mostraValores').html("Aguarde...");
				},
				success: function(html){
					//alert('valor');
					$('#mostraValores').html(html);
				},
				error: function(){
					alert('erro valor');
				}
			});
		});

		$('#fcanal').change(function(){
			
			// reset values
			ResetCheckedValue();

			// values in dd_pin_status start with 'st' to avoid geting null when status = 0
			$.ajax({
				type: "POST",
				url: "/ajax/gamer/ajaxValorComPesquisaVendas.php",
				data: "id="+((document.getElementById('dd_opr_codigo').value>0)?document.getElementById('dd_opr_codigo').value:-1)+
					"&st="+document.getElementById('dd_pin_status').value.substring(2)+
					"&cn="+document.getElementById('fcanal').value,
				beforeSend: function(){
					$('#mostraValores').html("Aguarde...");
				},
				success: function(html){
					//alert('valor');
					$('#mostraValores').html(html);
				},
				error: function(){
					alert('erro valor');
				}
			});
		});

	});

function ResetCheckedValue() {
	// reset the $varsel var 'tf_pins'
	if(document.form1.tf_pins) {
		document.form1.tf_pins.value = '';
	}

	// reset the checkboxes with values 'tf_pins[]'
	var chkObj = document.form1.elements.length;
	var chkLength = chkObj.length;
	for(var i = 0; i < chkLength; i++) {
		var type = document.form1.elements[i].type;
		if(type=="checkbox" && document.form1.elements[i].checked) {
			chkObj[i].checked = false;
		}
	}
}

//-->
</script>
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
                        <strong><?php echo LANG_PINS_PAGE_TITLE; ?></strong>
                    </div>
                </div>
                <div class="row txt-cinza">
                    <div class="col-md-6">
                        <span class="pull-left"><strong><?php echo LANG_PINS_SEARCH_1; ?></strong></span>
                    </div>
                    <div class="col-md-6">
                        <a href="/sys/admin/commerce/index.php" class="btn btn-primary pull-right"><strong><i><?php echo LANG_BACK; ?></i></strong></a>
                    </div>
                </div>
                <form name="form1" method="post" action="" onSubmit="return validade()">
                    <div class="row txt-cinza ">
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_PINS_START_DATE; ?></span>
                        </div>
                        <div class="col-md-3">
                            <input  alt="Calendário" name="tf_data_inicial" type="text" class="form-control w-ipt-medium pull-left data" id="tf_data_inicial" value="<?php  echo $tf_data_inicial ?>" size="9" maxlength="10">
                        </div>
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_PINS_END_DATE; ?></span>
                        </div>
                        <div class="col-md-3">
                            <input alt="Calendário" name="tf_data_final" type="text" class="form-control w-ipt-medium pull-left data" id="tf_data_final" value="<?php  echo $tf_data_final ?>" size="9" maxlength="10">
                        </div>

                    </div>
                    <div class="row txt-cinza top10">
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_PINS_SERIAL_NUMBER;?></span>
                        </div>
                        <div class="col-md-3">
                            <input name="fserial" type="text" class="form-control" id="fserial" value="<?php  echo $fserial ?>" size="20" maxlength="20">
                        </div>
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_PINS_PIN_NUMBER;?></span>
                        </div>
                        <div class="col-md-3">
                            <input name="fpin" type="text" class="form-control" id="fpin" value="<?php  echo $fpin ?>" size="16" maxlength="16">
                        </div>
                    </div>
                    <div class="row txt-cinza top10">
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_PINS_OPERATOR; ?></span>
                        </div>
                        <div class="col-md-3">
<?php 
                        if($_SESSION["tipo_acesso_pub"]=='PU') 
                        {
                            echo $_SESSION["opr_nome"];
?>
                            <input type="hidden" name="dd_opr_codigo" id="dd_opr_codigo" value="<?php echo $dd_opr_codigo?>">
<?php 
                        } else 
                        {
?>
                            <select name="dd_opr_codigo" id="dd_opr_codigo" class="form-control">
                                <option value=""><?php echo LANG_PINS_SELECT_OPERATOR; ?></option>
<?php  
                            while ($pgopr = pg_fetch_array($resopr)) 
                            {
?>
                                <option value="<?php  echo $pgopr['opr_codigo'] ?>" <?php  if($pgopr['opr_codigo'] == $dd_opr_codigo) echo "selected" ?>><?php  echo $pgopr['opr_nome'] ?></option>
<?php  
                            } 
?>
                            </select>
<?php 
                        } 
?>
                        </div>
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_PINS_CHANNEL; ?></span>
                        </div>
                        <div class="col-md-3">
                            <select name="fcanal" id="fcanal" class="form-control">
                                <option value="todos"><?php echo LANG_PINS_ALL_CHANNELS; ?></option>
								<option value="a" <?php  if(trim($fcanal) == 'a') echo "selected"?>>ATIMO</option>
                                <option value="s" <?php  if(trim($fcanal) == 's') echo "selected"?>>Site</option>
                                <option value="p" <?php  if(trim($fcanal) == 'p') echo "selected"?>>POS</option>
                            </select>
                        </div>
                    </div>
                    <div class="row txt-cinza top10">
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_PINS_STATUS; ?></span>
                        </div>
                        <div class="col-md-3">
                            <select name="dd_pin_status" id="dd_pin_status" class="form-control">
                                <option value="">Selecione o status do PIN</option>
                                <option value="stVendido - TODOS" <?php  if("stVendido - TODOS" == $dd_pin_status) echo "selected" ?>>Vendido - TODOS</option>
                                <?php  foreach($a_status as $key => $val) { ?>
                                <option value="st<?php  echo $key ?>" <?php  if("st".$key == $dd_pin_status) echo "selected" ?>><?php  echo $key." - ".$val ?></option>
                                <?php  } ?>
                            </select>
                        </div>
                        
                    </div>
                    <div class="row txt-cinza top10">
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_PINS_VALUE; ?></span>
                        </div>
                        <div class="col-md-10" id='mostraValores'>
<?php 
                    if($resvalue) 
                    {
                        foreach($a_valores as $key => $val) 
                        {
?>
                            <div class="pull-left text-left w66" style=""><input type="checkbox" id="tf_pins[]" name="tf_pins[]" value="<?php echo $key; ?>"
<?php
                            if ($tf_pins && is_array($tf_pins))
                                if (in_array($key, $tf_pins)) 
                                    echo " checked";
                                else
                                    if ($key == $tf_pins)
                                        echo " checked";
                                    ?>><span title="<?php echo "n: ".$val; ?>"><?php echo number_format($key,2,",","."); ?></span></div>
<?php  

                        } 
                    }
?>
                        </div> 
                    </div>
                    <div class="row txt-cinza top10">
                        <div class="col-md-12">
                            <button type="submit" name="BtnSearch" value="<?php echo LANG_PINS_SEARCH_2; ?>" class="btn pull-right btn-success"><?php echo LANG_INTEGRATION_SEARCH;?></button>
                        </div>
                    </div>
                </form>
                <div class="row txt-cinza espacamento">
                    <div class="col-md-12 bg-cinza-claro">
                        <table id="table" class="table bg-branco txt-preto fontsize-p">
                            <thead>
                                <tr class="bg-cinza-claro">
                                    <th class="text-center"><strong><?php echo "Data"; ?></strong></th>
                                    <th class="text-center"><strong><?php echo "n"; ?></strong></th>
                                    <th class="text-center"><strong><?php echo LANG_PINS_VALUE; ?></strong></th>
                                </tr>
                            </thead>
<?php
                            if($total_table > 0) 
                            {                            
?>                                
                            <tr>
                                <td colspan="3">
<?php  
                                if($total_table > 0) 
                                {
                                    echo ' '.LANG_SHOW_DATA.' '; ?> <strong><?php  echo $inicial + 1 ?></strong><?php echo ' '.LANG_TO.' '; ?><strong><?php  echo $reg_ate ?></strong><?php echo ' '.LANG_FROM.' '; ?><strong><?php  echo $total_table ?></strong>
<?php  
                                }
?>
                                </td>
                            </tr>
                            <tbody>
<?php
                            $cabecalho = "'Data','n','".LANG_PINS_VALUE."'";

                            $valor_total_tela = 0;
                            $qtde_total_tela = 0;
                                
                            while ($pgrow = pg_fetch_array($resid)) 
                            {
				$valor = 1;

				$valor_total_tela += $pgrow['total'];
				$qtde_total_tela += $pgrow['quantidade'];
?>
                                <tr class="trListagem">
                                    <td class="text-center"><?php if($pgrow['date_seq']) { ?><?php  echo monta_data($pgrow['date_seq']); } else echo "--"; ?></td>
                                    <td class="text-center"><?php  echo 1*$pgrow['quantidade']; ?></td>
                                    <td class="text-right"><?php  echo number_format($pgrow['total'], 2, ',', '.'); ?></td>
                                </tr>
<?php  
                            }
?>
                                <tr class="bg-cinza-claro"> 
                                    <td class="text-left"><strong>SUBTOTAL</strong></td>
                                    <td class="text-center"><strong><?php  echo $qtde_total_tela ?></strong></td>
                                    <td class="text-right"><strong><?php  echo number_format($valor_total_tela, 2, ',', '.') ?></strong></td>
                                </tr>
                                <tr class="bg-cinza-claro"> 
                                    <td class="text-left"><strong>TOTAL</strong></td>
                                    <td class="text-center"><strong><?php  echo $qtde_geral ?></strong></td>
                                    <td class="text-right"><strong><?php  //echo number_format($valor_geral, 2, ',', '.') ?></strong></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-center">
                                        <a href="#" class="btn downloadCsv btn-info ">Download CSV</a>
                                    </td>
                                </tr>
<?php  
                            }else
                            {
?>
                                <tr>
                                    <td colspan="3"><strong><?php echo LANG_NO_DATA; ?>.</strong></td>
                                </tr>
<?php
                            }
                            pg_close($connid);
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
<script type="text/javascript" src="/js/table2CSV.js"></script>
<script src="/js/global.js"></script>
<script>
$(function(){
    $('#table').table2CSV({header:[<?php echo $cabecalho; ?>],toStr:""});
    
    var optDate = new Object();
        optDate.interval = 1;

    setDateInterval('tf_data_inicial','tf_data_final',optDate);
});
</script>

<!-- FIM CODIGO NOVO -->
 </body>
</html>
        <?php  require_once $raiz_do_projeto . "public_html/sys/includes/rodape_sys.php"; ?>