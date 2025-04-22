<?php
ob_start();

require_once "../../../../includes/constantes.php";
require_once $raiz_do_projeto . "includes/gamer/constantes.php";
require_once $raiz_do_projeto . "public_html/sys/includes/topo_sys.php";
require_once $raiz_do_projeto . "public_html/sys/includes/gamer/inc_pub_access.php";

set_time_limit ( 3000 ) ;

$time_start = getmicrotime();

if(!$ncamp) $ncamp = 'pin_codinterno';
if(!$inicial)  $inicial     = 0;
if(!$range)    $range       = 1;

if(isset($_POST['BtnSearch'])){
    $inicial = 0;
    $range   = 1;
    $total_table = 0;
}

//if($BtnSearch) $inicial     = 0;
//if($BtnSearch) $range       = 1;
//if($BtnSearch) $total_table = 0;
if($flistall) {
        $inicial     = 0;
        $range       = 1;
}

$b_debug = false;
if ($_SESSION["tipo_acesso_pub"]=='AT') {
    if(!$flist_vg_id) {
	$flist_vg_id = false;
    }//end if(!$flist_vg_id)
}

$default_add  = nome_arquivo($PHP_SELF);
$img_proxima  = "/sys/imagens/proxima.gif";
$img_anterior = "/sys/imagens/anterior.gif";
$max          = 500; //$qtde_reg_tela;
$range_qtde   = $qtde_range_tela;

if(!$fpin) $fpin = '';
if(!$fserial) $fserial = '';
if(!$dd_opr_codigo) $dd_opr_codigo = '';
if(!$tf_data_final)    $tf_data_final   = date('d/m/Y');
if(!$tf_data_inicial)  $tf_data_inicial = date('d/m/Y');

//if(!$fcanal) $fcanal = 's';

if(b_is_Publisher()) {
        $dd_opr_codigo = $_SESSION["opr_codigo_pub"];
}

$varsel = "&tf_data_inicial=$tf_data_inicial&tf_data_final=$tf_data_final&dd_opr_codigo=$dd_opr_codigo&tf_loteopr=$tf_loteopr&dd_status=$dd_status&tf_valor_total=$tf_valor_total&fserial=$fserial&fpin=$fpin&fcodinterno=$fcodinterno&fcanal=$fcanal&dd_pin_status=".str_replace(" ", "", $dd_pin_status)."&BtnSearch=".$BtnSearch;

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

if($flist_vg_id)
{
    $varsel .= "&flist_vg_id=1";
}

// Levanta lista de operadoras
$sql  = "select opr_codigo, opr_nome from operadoras where opr_status='1' and opr_importa=1 order by opr_nome";
$resopr = pg_exec($connid,$sql);


// Levanta lista de status	
$sql  = "select stat_codigo, stat_descricao from pins_status order by stat_codigo;";
$resstatus = pg_exec($connid,$sql);
$a_status = array();
while ($pgstatus = pg_fetch_array($resstatus)) { 
        $a_status[$pgstatus['stat_codigo']] = $pgstatus['stat_descricao'];
}
ksort($a_status);

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
    echo "Elapsed time A1: ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.')."<br>";
}
echo "felipe ".$sql;
$resvalue = pg_exec($connid,$sql);
$a_valores = array();
while ($pgvalue = pg_fetch_array($resvalue)) { 
        $a_valores[$pgvalue['pin_valor']] = $pgvalue['n'];
}
ksort($a_valores);

if($BtnSearch) {
	$sql = "select t0.pin_codinterno, 
		CASE WHEN pin_codigo = '0000000000000000' THEN pin_caracter 
		ELSE pin_codigo 
    	END as case_codigo, 
		t1.opr_nome, t0.pin_serial, t0.pin_valor, t0.pin_status, t0.pin_canal, t0.opr_codigo, \n";
	$sql.= " t3.stat_descricao, t0.pin_datavenda, t0.pin_horavenda, t0.pin_est_codigo ";	//"--, t4.est_codigo, t4.nome_fantasia  \n";

	if($_SESSION["tipo_acesso_pub"]=='AT' && $flist_vg_id) {
		$sql.= " , case 
				when pin_status = '3' then (
				select vg_id 
				from tb_venda_games vg 
					inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id  
					inner join tb_venda_games_modelo_pins vgmp on vgmp.vgmp_vgm_id = vgm.vgm_id  and vgmp.vgmp_pin_codinterno = t0.pin_codinterno
				) 
				when pin_status = '6' then (
				select vg_id 
				from tb_dist_venda_games vg 
					inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id  
					inner join tb_dist_venda_games_modelo_pins vgmp on vgmp.vgmp_vgm_id = vgm.vgm_id  and vgmp.vgmp_pin_codinterno = t0.pin_codinterno
				) 
				when pin_status = '8' then (
                                    (
                                    select vg_id 
                                    from tb_dist_venda_games vg 
                                            inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id  
                                            inner join tb_dist_venda_games_modelo_pins vgmp on vgmp.vgmp_vgm_id = vgm.vgm_id  and vgmp.vgmp_pin_codinterno = t0.pin_codinterno
                                    )
                                    -- Para considerar venda para Gamer com status Utilizado acrescentar aas linhas comentadas abaixo
                                    -- Comentado em funçaõ de performance
                                    union all
                                    (
                                    select vg_id 
                                    from tb_venda_games vg 
                                            inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id  
                                            inner join tb_venda_games_modelo_pins vgmp on vgmp.vgmp_vgm_id = vgm.vgm_id  and vgmp.vgmp_pin_codinterno = t0.pin_codinterno
                                    )
				) 
				end as vg_id,
				case when pin_status = '3' then 'G'
						when pin_status = '6' then 'L'
                                                -- Alterar a linha abaico para verificar se a venda foi para gamer ou LAN
						when pin_status = '8' then (
                                                    (
                                                        select 'L'
                                                        from tb_dist_venda_games vg
                                                        inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id
                                                        inner join tb_dist_venda_games_modelo_pins vgmp on vgmp.vgmp_vgm_id = vgm.vgm_id and vgmp.vgmp_pin_codinterno = t0.pin_codinterno
                                                    )
                                                    -- Para considerar venda para Gamer com status Utilizado acrescentar aas linhas comentadas abaixo
                                                    -- Comentado em funçaõ de performance
                                                    union all
                                                    (
                                                        select 'G'
                                                        from tb_venda_games vg
                                                        inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id
                                                        inner join tb_venda_games_modelo_pins vgmp on vgmp.vgmp_vgm_id = vgm.vgm_id and vgmp.vgmp_pin_codinterno = t0.pin_codinterno
                                                    )
                                                )
						end as vg_canal
				";
	}

	$sql.= " from pins t0, operadoras t1, pins_status t3 ";	//"--, estabelecimentos t4  \n";
	$sql.= " where ";	//"--(t0.pin_est_codigo = t4.est_codigo) and \n";
	$sql.= " (t0.opr_codigo=t1.opr_codigo) and (t0.pin_status=t3.stat_codigo)  \n";

       if($tf_data_inicial) {
                // Formatando a data e Hora de acordo com o TimeZone
                $data_inic = formata_data(trim($tf_data_inicial), 1);
                $data_fim = formata_data(trim($tf_data_final), 1); 
                if (!empty($dd_california) ) {
                        date_default_timezone_set('America/Los_Angeles');
                        //date_default_timezone_set('UTC');
                        $hora_california = date("G");
                        date_default_timezone_set('America/Fortaleza');
                        $hora_sp = date("G");

                        $dataIncial  = mktime((($hora_sp - $hora_california)*1), 0, 0, (substr($data_inic, 5, 2)*1), (substr($data_inic, 8, 2)*1), (substr($data_inic, 0, 4)*1));
                        //$dataFinal  = mktime((($hora_sp - $hora_california)*1), 0, 0, (substr($data_fim, 5, 2)*1), (substr($data_fim, 8, 2)*1), (substr($data_fim, 0, 4)*1));
                        $dataFinal  = mktime((23+($hora_sp - $hora_california)*1), 59, 59, (substr($data_fim, 5, 2)*1), (substr($data_fim, 8, 2)*1), (substr($data_fim, 0, 4)*1));

                        $data_inic = date('Y-m-d H:i:s',$dataIncial);
                        $data_fim = date('Y-m-d H:i:s',$dataFinal);
                        
                        $sql .= " and ((pin_datavenda||' '||pin_horavenda)::timestamp between '".trim($data_inic)."' and  '".trim($data_fim)."')  \n"; 
                
                }
                else {
                        $data_inic .=  " 00:00:00";
                        $data_fim .=  " 23:59:59";
                        $sql .= " and (pin_datavenda between '".trim($data_inic)."' and  '".trim($data_fim)."')  \n"; 
                }
	}

	//if(!trim($fpin) && !trim($fserial) && !($festab)) $sql.= "and (t0.pin_codigo='') and (t0.pin_serial='')  \n"; 
	//else{
		if($fcodinterno)	$sql .= " and (t0.pin_codinterno in (".trim($fcodinterno)."))  \n";
		if($fserial)$sql .= " and (t0.pin_serial like '%".trim($fserial)."%')  \n"; 
		if($fpin)	$sql .= " and (t0.pin_codigo like '%".trim($fpin)."%')  \n";
		if($festab)	$sql .= " and (t0.pin_est_codigo = ".$festab.")  \n";
		if($fcanal) $sql .= " and (t0.pin_canal='".$fcanal."') \n"; 
	//}

	if($dd_opr_codigo) $sql .= "and (t0.opr_codigo=".$dd_opr_codigo.")  \n";

	if($dd_pin_status) {
		if(($dd_pin_status=="stVendido - TODOS") || ($dd_pin_status=="stVendido-TODOS")){ 
			$sql .= " and (t0.pin_status='3' or t0.pin_status='6' or t0.pin_status='7' or t0.pin_status='8')  \n";
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

	$sql .= " order by pin_datavenda desc, pin_horavenda desc";

        //echo "(R) ".str_replace("\n","<br>\n",$sql)."<br>\n<hr>";


	$meu_ip_1 = '201.93.162.169';
	$meu_ip_2 = '189.62.151.212';

	if ($_SERVER['REMOTE_ADDR'] == $meu_ip_1 || $_SERVER['REMOTE_ADDR'] == $meu_ip_2) {
			echo "<div style='background: #000; color: #fff'>" . $sql . "</div>";
	}
	echo "felipe: ". $sql;
        $resid_count = pg_exec($connid, $sql);
        $total_table = pg_num_rows($resid_count);

        $qtde_geral = 0;
        $valor_geral = 0;

        if  ($b_debug && ($_SESSION['userlogin_bko']=="REINALDO")) {$s_id_gamer = ""; $s_id_lans = ""; }
                while($pg_geral = pg_fetch_array($resid_count))
                {
                        $qtde_geral ++;
                        $valor_geral += $pg_geral['pin_valor'];
        if  ($b_debug && ($_SESSION['userlogin_bko']=="REINALDO")) {
                if($pg_geral['pin_status']=="3") {
                        if($s_id_gamer) $s_id_gamer .= ", ";
                        $s_id_gamer .= $pg_geral['vg_id']; 
                } else if($pg_geral['pin_status']=="6") {
                        if($s_id_lans) $s_id_lans .= ", ";
                        $s_id_lans .= $pg_geral['vg_id'];
                } else {
                        echo "Status desconhecido: ".$pg_geral['pin_status']." (pin_codinterno: ".$pg_geral['pin_codinterno'].", valor: ".$pg_geral['pin_valor'].")<br>";
                }
        }
                }
        if  ($b_debug && ($_SESSION['userlogin_bko']=="WAGNER")) {echo "<hr>Gamers vg_id: ".$s_id_gamer."<hr>Lans vg_id: ".$s_id_lans."<hr>"; }

	if($flistall) {
		$max = $total_table;
	} else {
		$sql .= " limit ".$max." ";
		$sql .= " offset ".$inicial;
	}

	//echo $sql;
        if(isset($_POST['download'])){
            $sql = preg_replace('/limit [0-9]*/s', '', $sql);
            $sql = preg_replace('/offset [0-9]*/s', '', $sql);
        }
        echo "felipe2: ".$sql;    
	$resid = pg_exec($connid, $sql);
		
	if($max + $inicial > $total_table) $reg_ate = $total_table;
	else $reg_ate = $max + $inicial;
} //end if($BtnSearch)
?>
<link href="/sys/css/css.css" rel="stylesheet" type="text/css">
<script language='javascript' src='/js/popcalendar.js'></script>
<!-- <_script language="javascript" src="../../../prepag2/dist_commerce/includes/jquery.js"><_/_script> -->
<script language="javascript" src="/js/epp_posrede/jquery.js"></script>
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
					"&cn="+document.getElementById('fcanal').value+"<?php echo $varsel_tf_pins ?>",
				beforeSend: function(){
					$('#mostraValores').html("<?php echo LANG_PINS_HOPE; ?>...");
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
					$('#mostraValores').html("<?php echo LANG_PINS_HOPE; ?>...");
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
					$('#mostraValores').html("<?php echo LANG_PINS_HOPE; ?>...");
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

function getCSVData(){
	var csv_value = $("<div>").append( $("#ReportTable").eq(0).clone() ).html();
//	alert(csv_value );
	 $("#csv_text").val(csv_value);	
}


//-->
</script>

<script>
postdata = <?php echo json_encode($_POST) ?>;
postdata.download = true;

$(function(){
    
    $('#btn-download').click(function(){
   
        $.ajax({
            url: "situacao_query.php",
            type: "POST",
            data: postdata,
            
            beforeSend: function() {
                $('#btn-download').attr('disabled', 'true').val('<?php echo LANG_PINS_HOPE; ?>...');
            },
            
            success: function(data) {
                $('#btn-download').hide();
                $('#download-relatorio').show();
                $('#download-relatorio').find('a').attr('href', data)
                
                //console.log(data);
            }
        });

    });
    
});
</script>

<body bgcolor="#FFFFFF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">

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
                        <strong><?php echo LANG_PINS_PAGE_TITLE_1; ?></strong>
                    </div>
                </div>
                <div class="row txt-cinza">
                    <div class="col-md-6">
                        <span class="pull-left"><strong><?php echo LANG_PINS_SEARCH_1; ?></strong></span>
                    </div>
                    <div class="col-md-6">
                        <span class="pull-right"><a href="/sys/admin/pins/index.php" class="btn btn-primary"><strong><i><?php echo LANG_BACK; ?></i></strong></a></span>
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
                            <span class="pull-right">Codinterno</span>
                        </div>
                        <div class="col-md-3">
                            <input name="fcodinterno" type="text" class="form-control" id="fcodinterno" value="<?php  echo $fcodinterno?>" size="30">
                        </div>
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_PINS_OPERATOR; ?></span>
                        </div>
                        <div class="col-md-3">
<?php 
                        if(b_is_Publisher())
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
                                <?php  while ($pgopr = pg_fetch_array($resopr)) { ?>
                                <option value="<?php  echo $pgopr['opr_codigo'] ?>" <?php  if($pgopr['opr_codigo'] == $dd_opr_codigo) echo "selected" ?>><?php  echo $pgopr['opr_nome'] ?></option>
                                <?php  } ?>
                            </select>
<?php 
                        } 
?>
                        </div>
                    </div>
                    
<?php 
                    if (b_is_PublisherMostraEstoquePINs())
                    {
?>
                    <div class="row txt-cinza top10">
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_PINS_STATUS; ?></span><input type="hidden" name="dd_pin_status" id="dd_pin_status" value="stVendido - TODOS">
                        </div>
                        <div class="col-md-3">
                            <span class="pull-left"><strong>'<?php echo LANG_PINS_SOLD_ALL; ?>'</strong></span>
                        </div>
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_PINS_CHANNEL; ?></span> <input type="hidden" name="fcanal" id="fcanal" value="">
                        </div>    
                        <div class="col-md-3">
                            <span class="pull-left"><strong>'<?php echo LANG_PINS_ALL_CHANNELS; ?>'</strong></span>
                        </div>
                    </div>
<?php
                    } else 
                    {
?>

                    <div class="row txt-cinza top10">
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_PINS_STATUS; ?></span>
                        </div>
                        <div class="col-md-3">
                            <select name="dd_pin_status" id="dd_pin_status" class="form-control">
                            <option value=""><?php echo LANG_PINS_SELECT_PIN_STATUS; ?></option>
                            <option value="stVendido - TODOS" <?php  if(("stVendido - TODOS" == $dd_pin_status) || ("stVendido-TODOS" == $dd_pin_status)) echo "selected" ?>><?php echo LANG_PINS_SOLD_USED; ?></option>
                            <?php  foreach($a_status as $key => $val) { ?>
                            <option value="st<?php  echo $key ?>" <?php  if("st".$key == $dd_pin_status) echo "selected" ?>><?php  echo $key." - ".constant("LANG_PINS_STATUS_MSG_{$key}") ?></option>
                            <?php  } ?>
                          </select>
                        </div>
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_PINS_CHANNEL; ?></span>
                        </div>
                        <div class="col-md-3">
                            <select name="fcanal" id="fcanal" class="form-control">
                                <option value=""<?php  if(trim($fcanal) == '') echo "selected"?>><?php echo LANG_PINS_ALL_CHANNELS; ?></option>
                                <option value="s"<?php  if(trim($fcanal) == 's') echo "selected"?>><?php echo LANG_PINS_SITE_CHANNEL; ?></option>
                                <option value="p"<?php  if(trim($fcanal) == 'p') echo "selected"?>><?php echo LANG_PINS_POS_CHANNEL; ?></option>
								<option value="a"<?php  if(trim($fcanal) == 'a') echo "selected"?>>AtimoPay</option>
                            </select>
                        </div>
                    </div>
<?php 
                    } 

                    if ($_SESSION["tipo_acesso_pub"]=='AT')
                    {
?>
                    <div class="row txt-cinza top10">
                        <div class="col-md-2">
                            <span class="pull-right text-right"><?php echo LANG_PINS_LIST_ALL_REGISTERS; ?></span>
                        </div>
                        <div class="col-md-3">
                            <span class="pull-left p-top10"><input type="checkbox" name="flistall" id="flistall"<?php if($flistall) echo " checked"; ?>></span>
                        </div>
                        <div class="col-md-2">
                            <span class="pull-right text-right"><?php echo LANG_PINS_LIST_VG_ID; ?></span>
                        </div>
                        <div class="col-md-3">
                            <span class="pull-left p-top10"><input type="checkbox" name="flist_vg_id" id="flist_vg_id"<?php if($flist_vg_id) echo " checked"; ?>></span>
                        </div>
                    </div>
<?php 
                    } 
?>
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
                                            echo " checked";?>>
                                    <span title="<?php echo "n: ".$val; ?>"><?php echo number_format($key, 2, ',', '.'); ?></span></div>
<?php  
                            } 
                        }
?>
                        </div> 
                    </div>
                    <div class="col-md-7 top20 txt-cinza">
                        <input type="checkbox" name="dd_california" id="dd_california" <?php echo ((!empty($dd_california))?" checked":"")?> value="1"> <?php echo LANG_PINS_CALIFORNIA_TIME;?>.</td>
                    </div>
                    <div class="row txt-cinza top10 text-right">
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
                                    <th class="text-center"><strong><?php echo LANG_PINS_ID; ?></strong></th>
                                    <th class="text-center"><strong><?php echo LANG_PINS_OPERATOR; ?></strong></th>
                                    <th class="text-center"><strong><?php echo LANG_PINS_SALES_DATE; ?></strong></th>
                                    <th class="text-center"><strong><?php echo LANG_PINS_SERIAL_NUMBER; ?></strong></th>
<?php 
                            $colspan = 5;
                            if (b_is_PublisherMostraEstoquePINs())
                            {
                                $ncols = 4-3;
                                $ncols1 = 2;
                            } else 
                            {
                                $ncols = 4;
                                $ncols1 = 0;
                                $colspan +=3;
?>
                                    <th class="text-center"><strong><?php echo LANG_PINS_PIN_NUMBER; ?></strong></th>
<?php 
                            } 
?>                                    
                                    <th class="text-center"><strong><?php echo LANG_PINS_VALUE; ?></strong></th>
                                    <th class="text-center"><strong><?php echo LANG_PINS_CHANNEL; ?></strong></th>
                                    <th class="text-center"><strong><?php echo LANG_PINS_STATUS; ?></strong></th>
<?php
                            
                            if ($_SESSION["tipo_acesso_pub"]=='AT' && $flist_vg_id) 
                            {
                                $colspan+=2;
?>
                                    <th class="text-center"><strong><?php echo "vg_id" ; ?></strong></th>
                                    <th class="text-center"><strong><?php echo "vg_canal" ; ?></strong></th>
<?php
                            }
?>
                                </tr>
                            </thead>
<?php
                            if($total_table > 0) 
                            {                            
?>                                
                            <tr>
                                <th colspan="<?php echo $colspan;?>">
<?php 
                                    echo ' '.LANG_SHOW_DATA.' '; ?> 
                                    <strong><?php  echo $inicial + 1 ?></strong>
                                    <?php echo ' '.LANG_TO.' '; ?>
                                    <strong><?php  echo $reg_ate ?></strong>
                                    <?php echo ' '.LANG_FROM.' '; ?>
                                    <strong><?php  echo $total_table ?> 
                                    <span id="txt_totais" style="color:blue"></span></strong>
                                </th>
                            </tr>
                            <tbody>
<?php
                                $valor_total_tela = 0;
                                $csv = array();
                                
                                while ($pgrow = pg_fetch_array($resid)) 
                                {
                                    $valor = 1;

                                    $valor_total_tela += $pgrow['pin_valor'];

                                    $pin_serial		= $pgrow['case_codigo'];
                                    $case_codigo	= $pgrow['pin_serial'];
                                    $opr_codigo		= $pgrow['opr_codigo'];


                                    // o carregaemnto no estoque para 'Axeso5', 'PayByCash' e 'Webzen' está trocado -> então troca de novo aqui
                                    //	opr_codigo = 44 -> 'Axeso5' , 28 -> 'PayByCash', 34 -> 'Webzen'
                                    if($opr_codigo == 28 || $opr_codigo == 44  || $opr_codigo == 34 ) 
                                    {
                                            $pin_serial		= $pgrow['pin_serial'];
                                            $case_codigo	= $pgrow['case_codigo'];
                                    }

                                    $csv_row = array();

                                    $csv_row[LANG_PINS_ID] = $pgrow['pin_codinterno'];
                                    $csv_row[LANG_PINS_OPERATOR] = $pgrow['opr_nome'];
                                    $csv_row[LANG_PINS_SALES_DATE] = ($pgrow['pin_datavenda'] ? monta_data($pgrow['pin_datavenda']) . " - " . $pgrow['pin_horavenda'] : "--") ;

                                    if(b_is_Administrator() || b_is_PublisherMostraEstoquePINs())
                                    {
                                        $csv_row[LANG_PINS_PIN_NUMBER] = "'".$pin_serial."'";
                                    }else{
                                        $csv_row[LANG_PINS_PIN_NUMBER] = "---- ----";
                                    }
                                
                                    if(b_is_PublisherMostraEstoquePINs())
                                    {
                                        $csv[LANG_PINS_SERIAL_NUMBER] = "???";
                                    }else
                                    {
                                        if(b_is_Administrator())
                                            $csv_row[LANG_PINS_SERIAL_NUMBER] = ((strlen(trim($case_codigo))>0)?formata_string($case_codigo, '', 4):"--vazio--");
                                        else
                                            $csv_row[LANG_PINS_SERIAL_NUMBER] = "-----";
                                    }
                                   
                                    $csv_row[LANG_PINS_VALUE] = number_format($pgrow['pin_valor'], 2, ',', '.');
                                    
                                    $csv_row[LANG_PINS_STATUS] = constant("LANG_PINS_STATUS_MSG_".$pgrow['pin_status']);
                                
                                    if($_SESSION["tipo_acesso_pub"]!='PU' && $flist_vg_id){
                                        $csv_row[] = $pgrow['vg_id'];
                                        $csv_row[] = $pgrow['vg_canal'];
                                    }else{
                                        $csv_row[] = "";
                                        $csv_row[] = "";
                                    }

                                    $csv[] = $csv_row;
?>
                                <tr class="trListagem">
                                    <td class="text-center"><?php  echo $pgrow['pin_codinterno'] ?></td>
                                    <td class="text-center"><?php  echo $pgrow['opr_nome'] ?></td>
                                    <td class="text-center"><?php if($pgrow['pin_datavenda']) { ?><?php  echo monta_data($pgrow['pin_datavenda']); ?> - <?php  echo $pgrow['pin_horavenda']; } else echo "--"; ?></td>
<?php 
                                    if (b_is_PublisherMostraEstoquePINs()) 
                                    {
                                    } else 
                                    {
?>
                                        <td class="text-center">
<?php  
                                        if (b_is_Administrator()) 
                                        {
                                            echo ((strlen(trim($case_codigo))>0)?formata_string($case_codigo, '', 4):"--vazio--");
                                        } else 
                                        {
                                            echo "----&nbsp;----";
                                        }
?>
                                        </td>
<?php 
                                    } 
?>
                                    <td class="text-center">
<?php  
                                    // Mostra pin_serial para não-Publishers e para NDoors (opr_codigo=33) e PayByCash (opr_codigo=28)
                                    if (b_is_Administrator() || b_is_PublisherMostraEstoquePINs()) 
                                    {
                                        echo $pin_serial; 
                                    } else {
                                        echo "-----&nbsp;-----";
                                    }
?>
                                    </td>
                                    <td class="text-center"><?php  echo "R$ ".number_format($pgrow['pin_valor'], 2, ',', '.'); ?></td>
<?php 
                                    if (b_is_PublisherMostraEstoquePINs())
                                    {
                                    } else 
                                    {
?>
                                    <td class="text-center"><span title="<?php echo "(".LANG_PINS_CHANNEL." = '".$pgrow['pin_canal']."')" ?>"><?php  echo (($pgrow['pin_canal']=='s')?"Site":(($pgrow['pin_canal']=='p')?"POS":(($pgrow['pin_canal']=='a')?"AtimoPay":"???"))); ?></td>
                                    <td class="text-center"><?php echo constant("LANG_PINS_STATUS_MSG_".$pgrow['pin_status']); ?></td>
<?php 
                                    } 

                                    if ($_SESSION["tipo_acesso_pub"]!='PU' && $flist_vg_id) 
                                    { //
                                        $schanel = (($pgrow['vg_canal']=="L")?"pdv":"gamer");
?>
                                        <td class="text-center"><a href="https://<?php echo $_SERVER["SERVER_NAME"] ?>:8080/<?php  echo $schanel; ?>/vendas/com_venda_detalhe.php?venda_id=<?php  echo "".$pgrow['vg_id']; ?>" target="_blank"><?php  echo "".$pgrow['vg_id']; ?></a></td>
                                        <td class="text-center"><?php  echo "".$pgrow['vg_canal']; ?></td>
<?php
                                    }
?>
                                </tr>
<?php  
                                }
                                
                                ####
                            
                                $time_end = getmicrotime();
                                $time = $time_end - $time_start;
?>
                                <tr class="bg-cinza-claro"> 
                                    <td colspan="<?php echo ($ncols1+$ncols) ?>">&nbsp;</td>
                                    <td><strong><?php echo LANG_PINS_SUBTOTAL; ?></strong></td>
                                    <td class="text-right"><strong><?php  echo number_format($valor_total_tela, 2, ',', '.') ?></strong></td>
                                    <td colspan="2">&nbsp;</td>
                                </tr>
                                <tr> 
                                    <td colspan="<?php echo ($ncols1+$ncols) ?>">&nbsp;</td>
                                    <td><strong>TOTAL</strong></td>
                                    <td class="text-right"><strong><?php  echo number_format($valor_geral, 2, ',', '.') ?></strong></td>
                                    <td colspan="2">&nbsp;</td>
                                </tr>
                                <script language="JavaScript">
                                  document.getElementById('txt_totais').innerHTML = '( <?php echo number_format($valor_total_tela, 2, ',', '.') ?> / <?php echo number_format($valor_geral, 2, ',', '.') ?>)';
                                </script>
<?php  
                                paginacao_query($inicial, $total_table, $max, $colspan, $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel); 
?>
                                <tr> 
                                    <td colspan="<?php echo (4+$ncols) ?>">
                                        <?php echo LANG_STATISTICS_SEARCH_MSG." ". number_format(getmicrotime() - $time_start, 2, '.', '.')." ".LANG_STATISTICS_SEARCH_MSG_UNIT; ?>
                                    </td>
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
                            pg_close($connid);
?>
                            </tbody>
                        </table>
<!--                        <a href="#" class="btn downloadCsv btn-info ">Download CSV</a>-->
                        <center>
                            <input type="button" value="<?php echo LANG_PINS_CREATE_FILE; ?>" id="btn-download" />

                            <div id="download-relatorio" style="display: none">
                                <a href="#"><?php echo LANG_PINS_CLICK_HERE_TO_DOWNLOAD; ?>.</a>
                            </div>
                            
                        </center>
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
<?php  
require_once $raiz_do_projeto . "public_html/sys/includes/rodape_sys.php";; 
if(isset($_POST['download']))
{
    ob_clean();
    //error_reporting(E_ALL); 
    //ini_set("display_errors", 1); 
 
    $dirpath = $raiz_do_projeto . "public_html/tmp/txt/";
    $webpath = "/tmp/txt/";
    $filename = "relatorio-situacao-query-" . time() . ".csv"; 

    $headers = array_keys($csv[0]);
    
    array_unshift($csv, $headers);
    
    foreach($csv as $k=>$v)
        $csv[$k] = implode(";", $v);
    
    $content = implode("\n", $csv);

    $content = str_replace('.', '', $content);
    $content = str_replace(',', '.', $content);

    if(!file_put_contents($dirpath.$filename, $content)){
        echo "Erro ao criar arquivo";
        die;
    }

    echo $webpath . $filename;
}
?>