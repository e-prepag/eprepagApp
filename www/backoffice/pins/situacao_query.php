<?php

require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
	set_time_limit ( 3000 ) ;
	$debug = false;
    $teste = false;
	
	$time_start_stats = getmicrotime();
	
//echo "inicial: $inicial<br>";
	if(!isset($ncamp) || !$ncamp) $ncamp = 'pin_codinterno';
	if(!isset($inicial) || !$inicial)  $inicial     = 0;
	if(!isset($range) || !$range)    $range       = 1;
//	if($BtnSearch) $inicial     = 0;
//	if($BtnSearch) $range       = 1;
//	if($BtnSearch) $total_table = 0;

	if(isset($BtnSearch) && $BtnSearch=="Buscar") {
		$inicial     = 0;
		$range       = 1;
		$teste = true;
		$total_table = 0;
	}

	$default_add  = nome_arquivo($PHP_SELF);
	$img_proxima  = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/proxima.gif";
	$img_anterior = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/anterior.gif";
	$max          = 100; //$qtde_reg_tela;
	$range_qtde   = $qtde_range_tela;

	if(!isset($fcodinterno) || !$fcodinterno) $fcodinterno = '';
	if(!isset($fcaracter) || !$fcaracter) $fcaracter = '';
	if(!isset($fpin) || !$fpin) $fpin = '';
	if(!isset($fserial) || !$fserial) $fserial = '';
	if(!isset($dd_opr_codigo) || !$dd_opr_codigo) $dd_opr_codigo = '';

	if(!isset($fcanal) || !$fcanal) $fcanal = 's';
        
	if (!empty($tf_pins)) {
		if (count($tf_pins) == 1) {
			$tf_pins = $tf_pins[0];                       
		} else {                   
			$tf_pins = implode("|",$tf_pins);                        
		}
	}
	if (isset($tf_pins) && $tf_pins != "") {              
		$tf_pins = explode("|",$tf_pins);	
	}

	// levanta operadoras
	$sql  = "select opr_codigo, opr_nome from operadoras where opr_status='1' and opr_importa=1 order by opr_nome";
    $resopr = pg_exec($connid,$sql);
	$a_opr = array();
	while ($pgopr = pg_fetch_array($resopr)) { 
		$a_opr[$pgopr['opr_codigo']] = $pgopr['opr_nome'];
	}
	ksort($a_opr);
    
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

	$resvalue = pg_exec($connid,$sql);
	$a_valores = array();
	while ($pgvalue = pg_fetch_array($resvalue)) { 
		$a_valores[$pgvalue['pin_valor']] = $pgvalue['n'];
	}
	ksort($a_valores);
		//var_dump($a_valores);
//        if(isset($tf_pins[0]) && $tf_pins[0] == "<"){;
//            $tf_pins = null;
//        }

	if(isset($BtnSearch) && ($BtnSearch=="Buscar" || $BtnSearch=="Buscarpag")) {
	
	    if($BtnSearch!="Buscar"){
	
			if(isset($_GET["tf_pins"])){
			   $tf_pins = array($_GET["tf_pins"]);
			   $teste = true;
			}
	
	    }
	
		$sql = "select t0.pin_codinterno, \n
			CASE WHEN pin_codigo = '0000000000000000' THEN pin_caracter \n
			ELSE pin_codigo \n
			END as case_codigo, \n
			t0.pin_serial, t0.pin_valor, t0.pin_status, t0.pin_canal,  \n";
		$sql.= " t0.pin_datavenda, t0.pin_horavenda, t0.pin_est_codigo, t0.opr_codigo ";	//"--, t4.est_codigo, t4.nome_fantasia  \n";
		$sql.= " from pins t0 ";	//	operadoras t1, pins_status t3 //"--, estabelecimentos t4  \n";
		$sql.= " where 1=1 ";	//"--(t0.pin_est_codigo = t4.est_codigo) and \n";
		$sql.= "   \n";
		
		if($tf_data_inicial) {
				$data_inic = formata_data(trim($tf_data_inicial), 1);
				$data_fim = formata_data(trim($tf_data_final), 1); 
				$sql .= " and (pin_datavenda between '".trim($data_inic)."' and  '".trim($data_fim)."')  \n"; 
		}

		//if(!trim($fpin) && !trim($fserial) && !($festab)) $sql.= "and (t0.pin_codigo='') and (t0.pin_serial='')  \n"; 
		//else{
			if(isset($fcodinterno) && $fcodinterno)	$sql .= " and (t0.pin_codinterno='".trim($fcodinterno)."')  \n";
			if(isset($fcaracter) && $fcaracter)	$sql .= " and (t0.pin_caracter='".trim($fcaracter)."')  \n";
			if(isset($fpin) && $fpin)	$sql .= " and (t0.pin_codigo='".trim($fpin)."')  \n";
			if(isset($fserial) && $fserial)$sql .= " and (t0.pin_serial='".trim($fserial)."')  \n"; 
			if(isset($festab) && $festab)	$sql .= " and (t0.pin_est_codigo = ".$festab.")  \n";
			if(isset($fcanal) && $fcanal) $sql .= " and (t0.pin_canal='".$fcanal."') \n"; 
		//}

		if($dd_opr_codigo) $sql .= " and (t0.opr_codigo=".$dd_opr_codigo.")  \n";

		if($dd_pin_status) {
			if($dd_pin_status=="stVendido-TODOS") { 
				$sql .= " and (t0.pin_status='3' or t0.pin_status='6' or t0.pin_status='7')  \n";
			} else {
				$sql .= " and (t0.pin_status='".substr($dd_pin_status,2,1)."')  \n";
			}			
		}
                //die(var_dump($tf_pins));
		if (!empty($tf_pins)) {
			$sql .= " and (";
			for($i=0;$i<count($tf_pins);$i++) {
                $sql .= " (t0.pin_valor = ".$tf_pins[$i].")  ";
				
                if($i<count($tf_pins)-1) {
					$sql .= " or  ";
				}
			}
			$sql .= " ) ";
		}
        
if($debug) {
echo str_replace("\n","<br>\n",$sql)."<br>";
echo "Elapsed time A2(*): ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.')."<br>";
//die("Stop 3434333");
}

//echo str_replace("\n","<br>\n",$sql)."<br>";
		$resid_count = pg_exec($connid, $sql);
		$total_table = pg_num_rows($resid_count);

		//$sql .= " order by pin_datavenda desc, pin_horavenda desc ";
		$sql .= " limit ".$max." ";
		$sql .= " offset ".$inicial;
		

    //echo $sql;
		
if($debug) {
echo str_replace("\n","<br>\n",$sql)."<br>";
echo "Elapsed time A3: ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.')."<br>";
//die("Stop 3434ddd3");
}
		$resid = pg_exec($connid, $sql);
	
if($debug) {
echo "Elapsed time A4: ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.')."<br>";
//die("Stop 3434ddd3");
}
		if($max + $inicial > $total_table) $reg_ate = $total_table;
		else $reg_ate = $max + $inicial;
	}
//echo "$total_table - $reg_ate<br>";
	@$varsel = "&tf_data_inicial=$tf_data_inicial&tf_data_final=$tf_data_final&dd_opr_codigo=$dd_opr_codigo&tf_loteopr=$tf_loteopr&dd_status=$dd_status&tf_valor_total=$tf_valor_total&fserial=$fserial&fpin=$fpin&fcodinterno=$fcodinterno&fcaracter=$fcaracter&fcanal=$fcanal&dd_pin_status=$dd_pin_status&BtnSearch=Buscarpag";
	if(isset($tf_pins) && $tf_pins) {
		$varsel .= "&tf_pins=".implode("|",$tf_pins);
	}
//echo $varsel."<br>";
require_once "/www/includes/bourls.php";
?>
<link href="<?php echo $url; ?>:<?php echo $server_port ;?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>:<?php echo $server_port ;?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $url;?>:<?php echo $server_port ;?>/js/global.js"></script>
<script language="javascript">
$(function(){
   var optDate = new Object();
        optDate.interval = 10000;

    setDateInterval('tf_data_inicial','tf_data_final',optDate);
});

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
					"&cn="+document.getElementById('fcanal').value+
					"&tf_pins="+document.getElementById('tfph').value,
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
		
		function valores(){
		
				ResetCheckedValue();

				// values in dd_pin_status start with 'st' to avoid geting null when status = 0
				$.ajax({
					type: "POST",
					url: "/ajax/gamer/ajaxValorComPesquisaVendas.php",
					data: "id="+((document.getElementById('dd_opr_codigo').value>0)?document.getElementById('dd_opr_codigo').value:-1)+
						"&st="+document.getElementById('dd_pin_status').value.substring(2)+
						"&cn="+document.getElementById('fcanal').value+
						"&tf_pins="+document.getElementById('tfph').value,
					beforeSend: function(){
						$('#mostraValores').html("Aguarde...");
					},
					success: function(html){
						//alert('valor');
						console.log(html);
						$('#mostraValores').html(html);
					},
					error: function(){
						alert('erro valor');
					}
				});
		
		}
		
		<?php  
		    if($teste){
			?>
			    valores();
			<?php
			}
		?>
		  

		$('#dd_pin_status').change(function(){
			
			// reset values
			ResetCheckedValue();
         
			// values in dd_pin_status start with 'st' to avoid geting null when status = 0
			$.ajax({
				type: "POST",
				url: "/ajax/gamer/ajaxValorComPesquisaVendas.php",
				data: "id="+((document.getElementById('dd_opr_codigo').value>0)?document.getElementById('dd_opr_codigo').value:-1)+
					"&st="+document.getElementById('dd_pin_status').value.substring(2)+
					"&cn="+document.getElementById('fcanal').value+
					"&tf_pins="+document.getElementById('tfph').value,
				beforeSend: function(){
					$('#mostraValores').html("Aguarde...");
				},
				success: function(html){
					//alert('valor');
					console.log(html);
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
					"&cn="+document.getElementById('fcanal').value+
					"&tf_pins="+document.getElementById('tfph').value,
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
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="1">BackOffice - Money</a></li>
        <li><a href="index.php">Estoque</a></li>
        <li class="active">Situação do PIN</li>
    </ol>
</div>
<table class="table txt-preto fontsize-pp">
  <tr> 
    <td>	
	<form name="form1" method="post" action="" onSubmit="return validade()">
            <input type="hidden" name="tf_pins" id="tf_pins" value="<?php isset($tf_pins) && (is_array($tf_pins)) ? implode("|",$tf_pins) : "" ?>">
        <table class="table">
          <tr> 
            <td colspan="3"><strong>Pesquisa</strong></td>
            <td><div align="right"> 
                <input type="submit" name="BtnSearch" value="Buscar" class="btn btn-info btn-sm">
              </div></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td>Data 
              Inicial:</td>
            <td>
              <input name="tf_data_inicial" type="text" class="form" id="tf_data_inicial" value="<?php if(isset($tf_data_inicial)) echo $tf_data_inicial ?>" size="9" maxlength="10">
              </td>
            <td width="13%">Data 
              Final:</td>
            <td width="45%">
              <input name="tf_data_final" type="text" class="form" id="tf_data_final" value="<?php if(isset($tf_data_final))  echo $tf_data_final ?>" size="9" maxlength="10">
              </td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td>PIN Código:</td>
            <td>
              <input name="fcodinterno" type="text" class="form" id="fcodinterno" value="<?php if(isset($fcodinterno))  echo $fcodinterno ?>" size="20" maxlength="30">
              </td>
            <td>PIN Caracter:</td>
            <td>
              <input name="fcaracter" type="text" class="form" id="fcaracter" value="<?php if(isset($fcaracter))  echo $fcaracter ?>" size="16" maxlength="30">
              </td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td>N&uacute;mero 
              de S&eacute;rie:</td>
            <td>
              <input name="fserial" type="text" class="form" id="fserial" value="<?php  if(isset($fserial)) echo $fserial ?>" size="20" maxlength="40">
              </td>
            <td>N&uacute;mero 
              do PIN:</td>
            <td> 
              <input name="fpin" type="text" class="form" id="fpin2" value="<?php if(isset($fpin))  echo $fpin ?>" size="16" maxlength="40">
              </td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td>Operadora:</td>
            <td>
              <select name="dd_opr_codigo" id="dd_opr_codigo" class="combo_normal">
                <option value="">Selecione a Operadora</option>
                <?php  foreach($a_opr as $key => $val) { ?>
                <option value="<?php  echo $key ?>" <?php  if($key == $dd_opr_codigo) echo "selected" ?>><?php  echo $val ?> (<?php  echo $key ?>)</option>
                <?php  } ?>
              </select>
              </td>
            <td>Valor:</td>
            <td>
			<div id='mostraValores'>
			<?php 
			
			if($resvalue) {
		      
                foreach($a_valores as $key => $val) { ?>
					<input type="checkbox" id="tf_pins[]" name="tf_pins[]" value="<?php echo $key; ?>" 
					<?php
					    
						if (isset($tf_pins) && is_array($tf_pins))
						 
							if (isset($tf_pins) && in_array($key, $tf_pins)) 
								echo " checked";
						else
							if (isset($tf_pins) && $key == $tf_pins)
							
								echo " checked";
					?>><span title="<?php echo "n: ".$val; ?>"><?php echo $key . ",00"; ?></span>
                <?php  } 
			  }
			?>
			</div>

            </td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td>PIN status:</td>
            <td>
              <select name="dd_pin_status" id="dd_pin_status" class="combo_normal">
                <option value="">Selecione o status do PIN</option>
                <option value="stVendido-TODOS" <?php  if(isset($dd_pin_status) && "stVendido-TODOS" == $dd_pin_status) echo "selected" ?>>Vendido - TODOS</option>
                <?php  foreach($a_status as $key => $val) { ?>
                <option value="st<?php  echo $key ?>" <?php  if(isset($dd_pin_status) && "st".$key == $dd_pin_status) echo "selected" ?>><?php  echo $key." - ".$val ?></option>
                <?php  } ?>
              </select>
              </td>
            <td>Canal:</td>
            <td>
				<select name="fcanal" id="fcanal" class="combo_normal">
				  <option value="">Todos os canais</option>
				  <option value="s" <?php  if(trim($fcanal) == 's') echo "selected"?>>Site</option>
				  <option value="p" <?php  if(trim($fcanal) == 'p') echo "selected"?>>POS</option>
				  <option value="r" <?php  if(trim($fcanal) == 'r') echo "selected"?>>Rede POS</option>
				  <option value="a" <?php  if(trim($fcanal) == 'a') echo "selected"?>>AtimoPay</option>
				</select>
			</td>
          </tr>
        </table>
		<input name="tfph" type="hidden" class="form" id="tfph" value="<?php if(isset($tf_pins))echo $tf_pins[0] ?>">
      </form>
	
	
      <table class="table table-bordered txt-preto">
        <tr> 
          <td><div align="center"><strong>C&oacute;digo</strong></div></td>
          <td><div align="center"><strong>Operadora</strong></div></td>
		  <td><div align="center"><strong> Data venda</strong></div></td>
          <td><div align="center"><strong>N&uacute;mero do PIN</strong></div></td>
          <td><div align="center"><strong>N&uacute;mero de S&eacute;rie</strong></div></td>
          <td><div align="center"><strong>Valor</strong></div></td>
          <td><div align="center"><strong>Canal</strong></div></td>
          <td><div align="center"><strong>Status</strong></div></td>
        </tr>
		<?php  if(isset($total_table) && $total_table > 0) { ?>
        

        <?php 
			$cor1 = "#F5F5FB"; 
			$cor2 = "#F5F5FB";
			$cor3 = "#FFFFFF";
			while ($pgrow = pg_fetch_array($resid)) {
				$valor = 1;
		?>
        <tr> 
          <td>
            <a href="situacao_detalhe.php?PinCod=<?php  echo $pgrow['pin_codinterno'] ?>&PinStatus=<?php  echo $pgrow['pin_status'] ?>" class="menu"><?php  echo $pgrow['pin_codinterno'] ?></a>
          </td>
          <td><?php if(isset($a_opr[$pgrow['opr_codigo']])) echo $a_opr[$pgrow['opr_codigo']] ?></td>
		  <td><?php  if(isset($pgrow['pin_datavenda'])) echo monta_data($pgrow['pin_datavenda']) ?> <?php  if(isset($pgrow['pin_horavenda'])) echo $pgrow['pin_horavenda']?></nobr></td>
		  <td align="center"><?php  echo ((b_IsBKOUsuarioAdminPINs() || b_IsBKOUsuarioAdminBKO() || b_Is_PIN_Vendido($pgrow['pin_status']))?@formata_string($pgrow['case_codigo'], ' ', 4):"-") ?></nobr></td>
          <td align="center"><?php  echo ((b_IsBKOUsuarioAdminPINs() || b_IsBKOUsuarioAdminBKO() || b_Is_PIN_Vendido($pgrow['pin_status']))?$pgrow['pin_serial']:"-"); ?></nobr></td>
          <td align="right"><nobr><?php  echo "R$ ".number_format($pgrow['pin_valor'], 2, ',', '.'); ?></nobr></td>
          <td align="center"><span title="<?php echo "(canal = '".$pgrow['pin_canal']."')" ?>"><?php  echo (($pgrow['pin_canal']=='s')?"Site":(($pgrow['pin_canal']=='p')?"POS":(($pgrow['pin_canal']=='r')?"Rede":($pgrow['pin_canal']=='a')?"AtimoPay":"???"))); ?></td>
          <td><?php  echo $a_status[$pgrow['pin_status']]; ?></nobr></td>
        </tr>
        <?php  
				if ($cor1 == $cor2) {$cor1 = $cor3;} else {$cor1 = $cor2;}
			}

			if(!$valor) {
		?>
        <tr> 
          <td colspan="10" bgcolor=""><div align="center"><strong><br>
              <?php echo 'Não há registros'; ?>.<br>
              <br>
              </strong></div></td>
        </tr>
        <?php 		} ?>
        <tr> 
          <td colspan="8">
                <?php  if($total_table > 0) { ?>
                    <?php echo 'Exibindo resultados'.' '; ?> <strong><?php  echo $inicial + 1 ?></strong> a <strong><?php  echo $reg_ate ?></strong> de <strong><?php  echo $total_table ?></strong>
                <?php  } else { ?>
                    &nbsp;
                <?php  } ?>
		  </td>
        </tr>
        <?php 	paginacao_query($inicial, $total_table, $max, '11', $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel); ?>
        <?php   } else {  ?>
        <tr bgcolor="#f5f5fb"> 
          <td colspan="10" bgcolor=""><div align="center">
              <?php echo 'Não há registros'; ?>.</div></td>
        </tr>
        <?php   }   ?>
      </table>
      <?php  pg_close($connid) ?>
      </td>
  </tr>
</table>
</html>
<?php
//if($debug) {
echo "Tempo de execução: ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.')."<br>";
//}			
?>