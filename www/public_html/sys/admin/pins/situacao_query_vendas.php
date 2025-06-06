<?php
    if(isset($_GET["downloadCsv"]) && $_GET["downloadCsv"] == 1)
        ob_start();

        require_once "../../../../includes/constantes.php";
        require_once $raiz_do_projeto . "public_html/sys/includes/topo_sys.php";
    
    
	set_time_limit ( 3000 ) ;
	$time_start_stats = getmicrotime();

	if(!$ncamp) $ncamp = 'pin_codinterno';
	if(!$inicial)  $inicial     = 0;
	if(!$range)    $range       = 1;
	if($BtnSearch) $inicial     = 0;
	if($BtnSearch) $range       = 1;
	if($BtnSearch) $total_table = 0;

	$default_add  = nome_arquivo($PHP_SELF);
	$img_proxima  = "https://".$server_url_complete."/images/proxima.gif";
	$img_anterior = "https://".$server_url_complete."/images/anterior.gif";
	$max          = 10; //$qtde_reg_tela;
	$range_qtde   = $qtde_range_tela;

	if(!$fpin)				$fpin = '';
	if(!$fserial)			$fserial = '';
	if(!$dd_opr_codigo)		$dd_opr_codigo = '';
	if(!$tf_data_final)		$tf_data_final   = date('d/m/Y');
	if(!$tf_data_inicial)	$tf_data_inicial = date('d/m/Y');
	
//echo "dd_pins_com_vendas: ".($dd_pins_com_vendas?"Yeah":"Nope")."<br>";

//echo "dd_opr_codigo: ".$dd_opr_codigo."<br>";
	if($dd_canal=="Site") {
		$fcanal = 's';
		$dd_pin_status = "stVendido - SITE";
//		echo "<font color='#FF0000'>SET pin_status='stVendido - SITE'</font><br>";
	} elseif($dd_canal=="SiteLH") {
		$fcanal = 's';
		$dd_pin_status = "st6";
//		echo "<font color='#FF0000'>SET pin_status='stVendidoLH'</font><br>";
	} elseif($dd_canal=="SiteGamer") {
		$fcanal = 's';
		$dd_pin_status = "st3";
//		echo "<font color='#FF0000'>SET pin_status='stVendidoGamer'</font><br>";
	}elseif($dd_canal=="Atimo") {
		$fcanal = 'a';
		$dd_pin_status = "st3";
//		echo "<font color='#FF0000'>SET pin_status='stVendidoGamer'</font><br>";
	}
	elseif($dd_canal=="POS") {
		$fcanal = 'p';
		$dd_pin_status = "st7";
//		echo "<font color='#FF0000'>SET pin_status='stVendidoPOS'</font><br>";
	} else {
		$fcanal = 's';
//		$dd_pin_status = "stVendido - TODOS";
//		echo "<font color='#FF0000'>SET pin_status='stVendido - TODOS'</font><br>";
	}

	if($_SESSION["tipo_acesso_pub"]=='PU') {
		$dd_opr_codigo = $_SESSION["opr_codigo_pub"];
	}

//echo "dd_opr_codigo: ".$dd_opr_codigo."<br>";  
//echo "tf_data_inicial: ".$tf_data_inicial."<br>";  
//echo "tf_data_final: ".$tf_data_final."<br>";  

	$varsel = "&tf_data_inicial=$tf_data_inicial&tf_data_final=$tf_data_final&dd_opr_codigo=$dd_opr_codigo&tf_loteopr=$tf_loteopr&dd_status=$dd_status&tf_valor_total=$tf_valor_total&fserial=$fserial&fpin=$fpin&dd_canal=$dd_canal&dd_pin_status=".str_replace(" ", "", $dd_pin_status)."";


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
//echo "<pre>".print_r($_REQUEST, true)."</pre><hr>";
//echo "dd_opr_codigo: ".$dd_opr_codigo."<br>";

//echo "varsel: $varsel<br>";
//echo "BtnSearch: $BtnSearch<br>";

	// Levanta lista de operadoras
	$sql  = "select opr_codigo, opr_nome from operadoras where opr_status='1' and opr_importa=1 order by opr_nome";
    $resopr = pg_exec($connid,$sql);

	// Lista de vendas encontradas para os parâmetros escolhidos
	// é o mesmo query de TOTAL_MES_stats.php e outros relatórios de vendas
	if($dd_pins_com_vendas) {
		$data_inic = formata_data(trim($tf_data_inicial), 1);
		$data_fim = formata_data(trim($tf_data_final), 1); 

		$sql  = "select vgm_pin_codinterno ";
		$sql .= "from tb_venda_games vg ";
		$sql .= "inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id ";
		$sql .= "where 1=1 ";
	//	$sql .= "and vg.vg_data_concilia>'2008-01-01 00:00:00' ";
	//	$sql .= "and COALESCE(vg.vg_data_concilia, (select datacompra from tb_pag_compras p where p.idvenda=vg.vg_id )>'2008-01-01 00:00:00' ";

		$sql .= "and vg.vg_ultimo_status='5' ";
		
		$sql .= "and vgm_opr_codigo=".$dd_opr_codigo." ";

	//	$sql .= "and vg.vg_data_concilia between '".trim($data_inic)." 00:00:00' and '".trim($data_fim)." 23:59:59' ";
		$sql .= "and COALESCE(vg.vg_data_concilia, (select datacompra from tb_pag_compras p where p.idvenda=vg.vg_id ) between '".trim($data_inic)." 00:00:00' and '".trim($data_fim)." 23:59:59' ";
	//	$sql .= "order by vg.vg_data_concilia desc";
		$sql .= "order by COALESCE(vg.vg_data_concilia, (select datacompra from tb_pag_compras p where p.idvenda=vg.vg_id ) desc";
	//echo "<hr>".$sql."<hr>";

		$s_lista_vgm_pin_codinterno = "";
		$resvendas = pg_exec($connid,$sql);
		while ($pgvendas = pg_fetch_array($resvendas)) { 
			if($s_lista_vgm_pin_codinterno!="") $s_lista_vgm_pin_codinterno .= " ";
			$s_lista_vgm_pin_codinterno .= $pgvendas['vgm_pin_codinterno'];
		}
	//echo "<hr>".$s_lista_vgm_pin_codinterno."<hr>";
	//echo "s_lista_vgm_pin_codinterno: ".($s_lista_vgm_pin_codinterno?"Yeah":"Nope")."<br>";
	}
	// Fixa o status para os Gamers
//	$dd_pin_status = "st3";

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


	$sql = "select t0.pin_codinterno, 
		t1.opr_nome, t0.pin_valor, t0.pin_status, t0.pin_canal, \n";
	$sql.= " t3.stat_descricao, t0.pin_datavenda, t0.pin_horavenda, t0.pin_est_codigo \n";	

	if($dd_pins_vendas) {
		$sql.= ", v.vgm_id, v.vg_id, v.vg_concilia, v.vg_data_concilia ";	
	}
	if($dd_pins_com_vendas && $s_lista_vgm_pin_codinterno) {
		$sql.= ", strpos('".$s_lista_vgm_pin_codinterno."'::text, ('' || t0.pin_codinterno)::text) as pin_com_venda ";	
	}
	if($tf_data_inicial) {
			$data_inic = formata_data(trim($tf_data_inicial), 1);
			$data_fim = formata_data(trim($tf_data_final), 1); 
	}

	$sql.= "\n";
	$sql.= " from pins t0, operadoras t1, pins_status t3 ";
	if($dd_pins_vendas) {
		$sql.= ", 
			(
					select 'Gamer' as canal, vgm.vgm_id, vg.vg_id, vg.vg_concilia, vg_data_concilia, vgmp.vgmp_pin_codinterno
					from tb_venda_games vg
						inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id
						inner join tb_venda_games_modelo_pins vgmp on vgmp.vgmp_vgm_id = vgm.vgm_id 
						inner join pins t1 on t1.pin_codinterno = vgmp.vgmp_pin_codinterno
					where t1.pin_datavenda between '".trim($data_inic)." 00:00:00' and  '".trim($data_fim)." 23:59:59' 
						".(($dd_opr_codigo)?" and (t1.opr_codigo=".$dd_opr_codigo.")  \n":"")."

					union all

					select 'LH' as canal, vgm.vgm_id, vg.vg_id, vg.vg_concilia, vg_data_inclusao, vgmp.vgmp_pin_codinterno
					from tb_dist_venda_games vg
						inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id
						inner join tb_dist_venda_games_modelo_pins vgmp on vgmp.vgmp_vgm_id = vgm.vgm_id 
						inner join pins t1 on t1.pin_codinterno = vgmp.vgmp_pin_codinterno
					where t1.pin_datavenda between '".trim($data_inic)." 00:00:00' and  '".trim($data_fim)." 23:59:59'  
						".(($dd_opr_codigo)?" and (t1.opr_codigo=".$dd_opr_codigo.")  \n":"")."
				) v 
		";	
	}
 

	$sql.= "\n";
	$sql.= " where ";	
	$sql.= " (t0.opr_codigo=t1.opr_codigo) and (t0.pin_status=t3.stat_codigo) \n";
	if($dd_pins_vendas) {
		$sql.= " and t0.pin_codinterno = v.vgmp_pin_codinterno \n";
	}
	
	if($tf_data_inicial) {
			$sql .= " and (t0.pin_datavenda between '".trim($data_inic)." 00:00:00' and  '".trim($data_fim)." 23:59:59')  \n"; 
	}
	//if(!trim($fpin) && !trim($fserial) && !($festab)) $sql.= "and (t0.pin_codigo='') and (t0.pin_serial='')  \n"; 
	//else{
		if($fserial)$sql .= " and (t0.pin_serial like '%".trim($fserial)."%')  \n"; 
		if($fpin)	$sql .= " and (t0.pin_codigo like '%".trim($fpin)."%')  \n";
		if($festab)	$sql .= " and (t0.pin_est_codigo = ".$festab.")  \n";
		if($fcanal && $fcanal!='todos') $sql .= "and (t0.pin_canal='".$fcanal."') \n"; 
	//}

	if($dd_opr_codigo) $sql .= " and (t0.opr_codigo=".$dd_opr_codigo.")  \n";

	if($dd_pin_status) {
		if(($dd_pin_status=="stVendido - TODOS") || ($dd_pin_status=="stVendido-TODOS")) { 
			$sql .= " and (t0.pin_status='3' or t0.pin_status='6' or t0.pin_status='7')  \n";
		} elseif(($dd_pin_status=="stVendido - SITE") || ($dd_pin_status=="stVendido-SITE")) { 
			$sql .= " and (t0.pin_status='3' or t0.pin_status='6')  \n";
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

	if($dd_pins_com_vendas && $s_lista_vgm_pin_codinterno) {
		$sql .= " and strpos('".$s_lista_vgm_pin_codinterno."'::text, ('' || t0.pin_codinterno)::text)=0 ";
	}

//	$sql .= " order by pin_datavenda desc, pin_horavenda desc, pin_codinterno ";
	if($dd_pins_vendas) {
		$sql .= " order by pin_datavenda desc, pin_horavenda desc, pin_codinterno ";	 //"vg_id, vgm_id, "
	} else {
		$sql .= " order by pin_datavenda desc, pin_horavenda desc, pin_codinterno ";
	}

//echo "".str_replace("\n","<br>\n",$sql)."<br>\n<hr>";
//die("Stop");

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

        if(!isset($_GET["downloadCsv"])){
            $sql .= " limit ".$max." ";
            $sql .= " offset ".$inicial;
        }
//if ($_SESSION['nome_bko']=="SUPORTE E-PREPAG") {
//if($_SESSION["tipo_acesso_pub"]!='PU') {
//echo str_replace("\n", "<br>\n", $sql)."<br>";
//}
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
//			var id = $(this).val();
//			alert(id);
			// reset values
			ResetCheckedValue();

			// values in dd_pin_status start with 'st' to avoid geting null when status = 0
			$.ajax({
				type: "POST",
				url: "/ajax/gamer/ajaxValorComPesquisaVendas.php",
				data: "id="+(($('#dd_opr_codigo').val()>0)?$('#dd_opr_codigo').val():-1)+
					"&st="+$('#dd_pin_status').val().substring(2)+
					"&cn="+$('#dd_canal').val(),
				beforeSend: function(){
//					alert('Sending: '+"id="+(($('#dd_opr_codigo').val()>0)?$('#dd_opr_codigo').val():-1)+
//					"&st="+$('#dd_pin_status').val().substring(2)+
//					"&cn="+$('#dd_canal').val());
					$('#mostraValores').html("Aguarde...");
				},
				success: function(html){
//					alert('Success: valor'+html);
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
				data: "id="+(($('#dd_opr_codigo').val()>0)?$('#dd_opr_codigo').val():-1)+
					"&st="+$('#dd_pin_status').val().substring(2)+
					"&cn="+$('#dd_canal').val(),
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

		$('#dd_canal').change(function(){
			
			// reset values
			ResetCheckedValue();

			// values in dd_pin_status start with 'st' to avoid geting null when status = 0
			$.ajax({
				type: "POST",
				url: "/ajax/gamer/ajaxValorComPesquisaVendas.php",
				data: "id="+(($('#dd_opr_codigo').val()>0)?$('#dd_opr_codigo').val():-1)+
					"&st="+$('#dd_pin_status').val().substring(2)+
					"&cn="+$('#dd_canal').val(),
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
<body>
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
                        <span class="pull-right"><a href="/sys/admin/commerce/index.php" class="btn btn-primary"><strong><i><?php echo LANG_BACK; ?></i></strong></a></span>
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
                            <input type="hidden" id="dd_opr_codigo" name="dd_opr_codigo" value="<?php echo $dd_opr_codigo?>">
<?php
                        } else 
                        {
?>
                            <select id="dd_opr_codigo" name="dd_opr_codigo" class="form-control">
                                <option value=""><?php echo LANG_PINS_SELECT_OPERATOR; ?></option>
                                <?php  while ($pgopr = pg_fetch_array($resopr)) { ?>
                                <option value="<?php  echo $pgopr['opr_codigo'] ?>" <?php  if($pgopr['opr_codigo'] == $dd_opr_codigo) echo "selected" ?>><?php  echo $pgopr['opr_nome']." (".$pgopr['opr_codigo'].")" ?></option>
                                <?php  } ?>
                            </select>
<?php 
                        } 
?>
                        </div>
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_PINS_CHANNEL; ?></span>
                        </div>
                        <div class="col-md-3">
                            <select id="dd_canal" name="dd_canal" class="form-control">
                                <option value="" <?php  if($dd_canal!="Site" and $dd_canal!="SiteLH" and $dd_canal!="SiteGamer" and $dd_canal!="POS") echo "selected" ?>><?php echo LANG_PINS_ALL; ?></option>
                                <option value="Site" <?php  if($dd_canal=="Site") echo "selected" ?>>Site (SiteLH+SiteGamer)</option>
                                <option value="SiteLH" <?php  if($dd_canal=="SiteLH") echo "selected" ?>>SiteLH</option>
                                <option value="SiteGamer" <?php  if($dd_canal=="SiteGamer") echo "selected" ?>>SiteGamer</option>
                                <option value="POS" <?php  if($dd_canal=="POS") echo "selected" ?>>POS</option>
								<option value="Atimo" <?php  if($dd_canal=="Atimo") echo "selected" ?>>Atimo</option>
                            </select>
                        </div>
                    </div>
                    <div class="row txt-cinza top10">
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_PINS_STATUS; ?></span>
                        </div>
                        <div class="col-md-3">
                            <select id="dd_pin_status" name="dd_pin_status" class="form-control">
                                <option value="">Selecione o status do PIN</option>
                                <option value="stVendido - TODOS" <?php  if(("stVendido - TODOS" == $dd_pin_status) || ("stVendido-TODOS" == $dd_pin_status)) echo "selected" ?>>Vendido - TODOS</option>
                                <option value="stVendido - SITE" <?php  if(("stVendido - SITE" == $dd_pin_status) || ("stVendido-SITE" == $dd_pin_status) ) echo "selected" ?>>Vendido - SITE</option>
                                <?php  foreach($a_status as $key => $val) { ?>
                                <option value="st<?php  echo $key ?>" <?php  if("st".$key == $dd_pin_status) echo "selected" ?>><?php  echo $key." - ".$val ?></option>
                                <?php  } ?>
                            </select>
                        </div>
                        
                    </div>
                    <div class="row txt-cinza ">
                        <div class="col-md-2">
                            <span class="pull-right">PINs&lt;-&gt;Vendas</span>
                        </div>
                        <div class="col-md-3">
                            <input type="checkbox" class="pull-left" id="dd_pins_vendas" name="dd_pins_vendas"<?php if($dd_pins_vendas) echo " checked"; ?>>
                        </div>
                        <div class="col-md-2">
                            <span class="pull-right">PINs COM Vendas</span>
                        </div>
                        <div class="col-md-3">
                            <input type="checkbox" class="pull-left" id="dd_pins_com_vendas" name="dd_pins_com_vendas"<?php if($dd_pins_com_vendas) echo " checked"; ?>>
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
                                        ?>><span title="<?php echo "n: ".$val; ?>"><?php echo number_format($key, 2, ',', '.'); ?></span>
                                </div>
                <?php  } 
			  }
			?>
			</div>
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
                                    <!--th class="text-center"><strong>Estabelecimento </strong></td-->
                                    <th class="text-center"><strong><?php echo LANG_PINS_SALES_DATE; ?></strong></th>
                                    <th class="text-center"><strong><?php echo LANG_PINS_VALUE; ?></strong></th>
                                    <th class="text-center"><strong><?php echo LANG_PINS_STATUS; ?></strong></th>
<?php
                                    $colspan = 5;
                                    if($dd_pins_vendas)
                                    {
                                        $colspan+=5;
?>
                                      <th class="text-center"><strong><?php echo "vg_id"; ?></strong></th>
                                      <th class="text-center"><strong><?php echo "vg_concilia"; ?></strong></th>
                                      <th class="text-center"><strong><?php echo "vgm_id"; ?></strong></th>
                                      <th class="text-right"><strong><?php echo "pin_com_venda"; ?></strong></th>
                                      <th class="text-center"><strong><?php echo "vg_data_concilia"; ?></strong></th>
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
                                <td colspan="<?php echo $colspan; ?>">
<?php  
                                    echo ' '.LANG_SHOW_DATA.' '; ?> <strong><?php  echo $inicial + 1 ?></strong><?php echo ' '.LANG_TO.' '; ?><strong><?php  echo $reg_ate ?></strong><?php echo ' '.LANG_FROM.' '; ?><strong><?php  echo $total_table ?></strong>
                                </td>
                            </tr>
                            <tbody>
<?php
                            $valor_total_tela = 0;
                            $vg_id_prev = -1;
                            $vgm_id_prev = -1;
    //			$s_vg_id_list = "";

                            require_once $raiz_do_projeto."class/util/CSV.class.php";

                            $cabecalho = LANG_PINS_ID."Pin;".LANG_PINS_OPERATOR.";".LANG_PINS_SALES_DATE.";".LANG_PINS_VALUE.";".LANG_PINS_STATUS;
                            if($dd_pins_vendas) $cabecalho .= ";vg_id;vg_concilia;vgm_id;pin_com_venda;vg_data_concilia";

                            $objCsv = new CSV($cabecalho, md5(uniqid()), $raiz_do_projeto."arquivos_gerados/csv/");
                            $objCsv->setCabecalho();

                            while ($pgrow = pg_fetch_array($resid)) 
                            {
				$valor = 1;

				$valor_total_tela += $pgrow['pin_valor'];
                                
                                $lineCsv = array();
                                $lineCsv[] = $pgrow['pin_codinterno'];
                                $lineCsv[] = $pgrow['opr_nome'];
                                $lineCsv[] = ($pgrow['pin_datavenda']) ? monta_data($pgrow['pin_datavenda'])." - ".$pgrow['pin_horavenda'] : "--";
                                $lineCsv[] = "R$ ".number_format($pgrow['pin_valor'], 2, ',', '.');
                                $lineCsv[] = constant("LANG_PINS_STATUS_MSG_".$pgrow['pin_status']);
?>
                                <tr class="trListagem">
                                    <td class="text-center"> 
                                        <a href="situacao_detalhe.php?PinCod=<?php  echo $pgrow['pin_codinterno'] ?>&PinStatus=<?php  echo $pgrow['pin_status'] ?>"><?php  echo $pgrow['pin_codinterno'] ?></a>
                                    </td>
                                    <td class="text-center"><?php  echo $pgrow['opr_nome'] ?></td>
                                    <td class="text-center"><?php if($pgrow['pin_datavenda']) { ?><?php  echo monta_data($pgrow['pin_datavenda']); ?> - <?php  echo $pgrow['pin_horavenda']; } else echo "--"; ?><nobr></font></td>
                                    <td class="text-right"><?php  echo "R$ ".number_format($pgrow['pin_valor'], 2, ',', '.'); ?><nobr></font></td>
                                    <td class="text-center"><?php echo constant("LANG_PINS_STATUS_MSG_".$pgrow['pin_status']);?></td>
<?php 
                                if($dd_pins_vendas)
                                {
                                    if($pgrow['pin_status']=="3")  {
                                            $url_com_venda_detalhe_prev = "<a href='https://".$server_url_complete."/gamer/vendas/com_venda_detalhe.php?venda_id=".$pgrow['vg_id']."' target='_blank'>";
                                            $url_com_venda_detalhe_pos = "</a>";
                                    } else if($pgrow['pin_status']=="6")  {
                                            $url_com_venda_detalhe_prev = "<a href='https://".$server_url_complete."/pdv/vendascom_venda_detalhe.php?venda_id=".$pgrow['vg_id']."' target='_blank'>";
                                            $url_com_venda_detalhe_pos = "</a>";
                                    } else {
                                            $url_com_venda_detalhe_prev = "<a href='https://".$server_url_complete."/pdv/vendas/com_venda_detalhe.php?venda_id=".$pgrow['vg_id']."' target='_blank'>";
                                            $url_com_venda_detalhe_pos = "</a>";
                                    }
?>
                                    <td>
<?php 
                                    if($pgrow['vg_id']!=$vg_id_prev) {
                                            echo $url_com_venda_detalhe_prev.$pgrow['vg_id'].$url_com_venda_detalhe_pos; 
                                    }
                                    $lineCsv[] = $pgrow['vg_id'];
                                    $lineCsv[] = $pgrow['vg_concilia'];
                                    $lineCsv[] = ($pgrow['vgm_id']!=$vgm_id_prev)? $pgrow['vgm_id'] : "";
                                    $lineCsv[] = $pgrow['pin_com_venda'];
                                    $lineCsv[] = $pgrow['vg_data_concilia'];
?>
                                    </td>
                                    <td><?php echo $pgrow['vg_concilia']; ?></td>
                                    <td><?php if($pgrow['vgm_id']!=$vgm_id_prev) echo $pgrow['vgm_id']; ?></td>
                                    <td><?php echo $pgrow['pin_com_venda']; ?></td>
                                    <td><?php echo $pgrow['vg_data_concilia']; ?></td>
<?php 
                                } 
                                $objCsv->setLine(implode(";",$lineCsv));
?>
                                </tr>
<?php
                                $vg_id_prev = $pgrow['vg_id'];
  				$vgm_id_prev = $pgrow['vgm_id'];
                            }
                            
                            if($reg_ate >= $total_table && !isset($_REQUEST["inicial"]))
                                $csv = $objCsv->export();
                            
                            $ncols_full = 7;
                            $ncols_short = $ncols_full-4;
?>
                                <tr class="bg-cinza-claro"> 
                                    <td colspan="2">&nbsp;</td>
                                    <td align="right"><strong>SUBTOTAL</strong></td>
                                    <td><div align="right"><strong><?php  echo number_format($valor_total_tela, 2, ',', '.') ?></strong></div></td>
                                    <td colspan="<?php echo $ncols_short; ?>">&nbsp;</td>
                                </tr>
                                <tr class="bg-cinza-claro">
                                      <td colspan="2">&nbsp;</td>
                                      <td align="right"><strong>TOTAL</strong></td>
                                      <td><div align="right"><strong><?php  echo number_format($valor_geral, 2, ',', '.') ?></strong></div></td>
                                      <td colspan="<?php echo $ncols_short; ?>">&nbsp;</td>
                                </tr>
<?php  
                                paginacao_query($inicial, $total_table, $max, $colspan, $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel); 

                                if(isset($csv))
                                {
                                    $csv = "/includes/downloadCsv.php?csv=$csv&dir=bkov";
                                }elseif(isset($_GET["downloadCsv"])){
                                    require_once $raiz_do_projeto."public_html/includes/downloadCsv.php";
                                }elseif($total_table > 0){
                                    $csv = "/sys/admin/pins/situacao_query_vendas.php?downloadCsv=1&".$varsel;//http_build_query($_POST);
                                }

                                if(isset($csv))
                                { 
?>
                                <tr>
                                    <td bgcolor="#FFFFFF" class="text-center" colspan="<?php echo $ncols_full; ?>"><a href="<?php echo $csv;?>" target="_blank" class="btn downloadCsv btn-info ">Download CSV</a></td>
                                </tr>
<?php
                                } 
                            }else
                            {
?>
                                <tr>
                                    <td colspan="3" class="text-center"><strong><?php echo LANG_NO_DATA; ?>.</strong></td>
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
<script src="/js/global.js"></script>
<script>
$(function(){
    var optDate = new Object();
        optDate.interval = 1;

    setDateInterval('tf_data_inicial','tf_data_final',optDate);
});
</script>

<!-- FIM CODIGO NOVO -->    
</body>
</html>
        <?php  require_once $raiz_do_projeto . "public_html/sys/includes/rodape_sys.php"; ?>