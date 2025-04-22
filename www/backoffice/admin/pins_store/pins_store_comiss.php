<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once $raiz_do_projeto . "includes/main.php";
require_once $raiz_do_projeto . "includes/gamer/main.php";
require_once $raiz_do_projeto . "class/classIntegracaoPin.php";
require_once $raiz_do_projeto . "class/classIntegracaoPinCash.php"; 
require_once $raiz_do_projeto . "includes/inc_functions.php";
require_once $raiz_do_projeto . "class/classPinsStore.php";       

// Configurado para naun expirar em 10 minutos de execução
// no DEV foi conseguido gerar pouco mais de 47000 PINs neste intervalo de tempo
set_time_limit(600);

$operacao_array = VetorDistribuidoras();

$testeSubmit		= isset($_POST['BtnAtualizar'])      ? $_POST['BtnAtualizar']			: null;

$time_start_stats = getmicrotime();

//Converte data do bando para legenda
function mes_do_ano_comiss($this_date){
	$this_date = substr($this_date,0,19);
	$this_date = strtotime($this_date);
	//'posicao = número relacionado a string de dados
	$meses = array("", "JANEIRO", "FEVEREIRO", "MARCO", "ABRIL", "MAIO", "JUNNHO", "JULHO", "AGOSTO", "SETEMBRO", "OUTUBRO", "NOVEMBRO", "DEZEMBRO");
	return $meses[date("n", $this_date)]."/".date("y", $this_date);
}

//Sort items ordem decrescente
function uksort_cmp($a, $b) { 
   if ($a == $b) return 0;
   return ($a > $b) ? -1 : 1;
}

//Função converte ID numérico de Distribuidor ID caracter de Distribuidor
function number_to_character($number) { 
	switch ($number) {
		case 1: return 'P1';
				break;
		case 2: return 'G';
				break;
		case 3: return 'L';
				break;
		case 5: return 'P2';
				break;
		case 6: return 'P3';
				break;
		case 7: return 'P4';
				break;
		case 8: return 'P5';
				break;
		case 9: return 'P6';
				break;
	}//end switch
}//end function number_to_character

//SQL Wagner antes da solicitação do Reynaldo
/*
$sql = "select pspep_canal,date_trunc('month', pspep_data) as mes, count(pspep.ps_pin_codinterno) as n, SUM(ps.pin_valor*pspep.pspep_comissao/100) as total 
		from pins_store_pag_epp_pin pspep
		INNER JOIN pins_store ps ON (ps.pin_codinterno=pspep.ps_pin_codinterno)
		group by pspep_canal,mes";
*/
//SQL com a solicitação do Reynaldo
$sql = "select date_trunc('month',date_seq) as mes,pspep_canal, count(ps_pin_codinterno) as n, ";
if($chk_total==1) {
	$sql .= "SUM(ps.pin_valor) as total ";
}
else {
	$sql .= "SUM(ps.pin_valor*pspep_comissao/100) as total ";
}
$sql .= "		from (select (generate_series(0,(CURRENT_DATE-'2011-08-01')) + date '2011-08-01') as date_seq) d
			  left outer join (
					select date_trunc('day', pspep_data) as s_dia,ps_pin_codinterno,pspep_canal,pspep_comissao
					from pins_store_pag_epp_pin pspep
					) t on d.date_seq=t.s_dia
		left outer join  pins_store ps ON (ps.pin_codinterno=t.ps_pin_codinterno)
		group by pspep_canal,mes";
$rs_pins = SQLexecuteQuery($sql);
if ($rs_pins) {
	if (is_array($vetorPeriodo)) {
		unset($vetorPeriodo);
	}
	// Preenche Valores mensais e totais ================================================= 
	 while($rs_pins_row = pg_fetch_array($rs_pins)){
		$vetorPeriodo[$rs_pins_row['mes']][$rs_pins_row['pspep_canal']]['TotalValor']	+= $rs_pins_row['total'];
		$vetorPeriodo[$rs_pins_row['mes']][$rs_pins_row['pspep_canal']]['TotalQte']		+= $rs_pins_row['n'];
		$vetorPeriodo[$rs_pins_row['mes']]['TotalValorGeral']	+= $rs_pins_row['total'];
		$vetorPeriodo[$rs_pins_row['mes']]['TotalQteGeral']		+= $rs_pins_row['n'];
	}//end while
	//echo "<pre>".print_r($vetorPeriodo,true)."</pre>";
	uksort( $vetorPeriodo, 'uksort_cmp');
	//echo "-----------------------------------------<br>";
	//echo "<pre>".print_r($vetorPeriodo,true)."</pre>";
}//end if ($rs_pins)
?>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="1">BackOffice - Money</a></li>
        <li class="active">Relatório de Comissão Paga de PINs E-PREPAG</li>
    </ol>
</div>  
<div class="col-md-12 txt-preto fontsize-pp">
<?php include "pins_store_menu.php";?>
<table class="table txt-preto fontsize-pp bg-branco">
  <tr>
    <td>
        <form name="form1" method="post">
        <div align="right" style="font-size:10px;font-weight:bold;color:#00008C;font-family:Arial, Helvetica, sans-serif;">
            <input name="chk_total" id="chk_total" type="checkbox"  <?php if($chk_total) echo "checked"; ?> onClick="document.form1.submit();" value="1">
            Exibi o total utilizado com a comiss&atilde;o nos per&iacute;odos&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        </div>
        <table class="table txt-preto fontsize-pp">
          <tr bgcolor="#00008C" style="font-size:14px;font-weight:bold;color:#FFFFFF;font-family:Arial, Helvetica, sans-serif;">
            <td rowspan="2" align="center">M&ecirc;s</td>
			<?php foreach ($operacao_array as $key => $value) { ?>
			<td colspan="2" align="center"><?php echo str_replace(" - Formato (4)","",$value); ?></td>
			<?php } ?>
			<td colspan="2" align="center">Total</td>
		  </tr>
		  <tr bgcolor="#00008C" style="font-size:10px;font-weight:bold;color:#FFFFFF;font-family:Arial, Helvetica, sans-serif;">
			<?php foreach ($operacao_array as $key => $value) { ?>
			<td align="center">N</td><td align="center">Comiss&atilde;o (R$)</td>
            <?php } ?>
			<td align="center">N</td><td align="center">Comiss&atilde;o (R$)</td>
          </tr>
          <?php if($msg != ""){ ?>
          <tr bgcolor="#FFFFFF">
            <td colspan="<?php echo (count($operacao_array)*2+2);?>"><font color="red" size="2"><?php echo str_replace("\n", "<br>", $msg)?></font></td>
          </tr>
          <?php } 
		  if (is_array($totais)) {
			unset($totais);
		  }
		  $bg_col_01 = "#FFFFFF";
		  $bg_col_02 = "#EEEEEE";
		  $bg_col = $bg_col_01;
		  foreach ($vetorPeriodo as $data => $value){
		  ?>
		  <tr bgcolor="<?php echo $bg_col;?>">
            <td class="texto"><b>&nbsp;<?php echo mes_do_ano_comiss($data);?></b></td>
			<?php foreach ($operacao_array as $key => $value2) { ?>
            <td class="texto" align="right"><b>&nbsp;<?php echo number_format($vetorPeriodo[$data][number_to_character($key)]['TotalQte'],0,',','.');?></b></td>
			<td class="texto" align="right"><b>&nbsp;<?php echo number_format($vetorPeriodo[$data][number_to_character($key)]['TotalValor'],2,',','.');?></b></td>
			<?php
					$totais['TotalQte'][number_to_character($key)]	+= $vetorPeriodo[$data][number_to_character($key)]['TotalQte'];
					$totais['TotalValor'][number_to_character($key)]+= $vetorPeriodo[$data][number_to_character($key)]['TotalValor'];
			  	} 
		    ?>
			<td class="texto" align="right"><b>&nbsp;<?php echo number_format($vetorPeriodo[$data]['TotalQteGeral'],0,',','.');?></b></td>
			<td class="texto" align="right"><b>&nbsp;<?php echo number_format($vetorPeriodo[$data]['TotalValorGeral'],2,',','.');?></b></td>
		  </tr>
          <?php
			  $totais['TotalQteGeral']	+= $vetorPeriodo[$data]['TotalQteGeral'];
			  $totais['TotalValorGeral']+= $vetorPeriodo[$data]['TotalValorGeral'];
			  $bg_col = ($bg_col==$bg_col_01)?$bg_col_02:$bg_col_01;
		  }//end foreach ($vetorPeriodo as $data => $value)
		  ?>
		  <tr bgcolor="#00008C" style="font-size:14px;font-weight:bold;color:#FFFFFF;font-family:Arial, Helvetica, sans-serif;">
			<td align="center">Totais</td>
			<?php foreach ($operacao_array as $key => $value2) { ?>
            <td align="right"><b>&nbsp;<?php echo number_format($totais['TotalQte'][number_to_character($key)],0,',','.');?></b></td>
			<td align="right"><b>&nbsp;<?php echo number_format($totais['TotalValor'][number_to_character($key)],2,',','.');?></b></td>
			<?php 	}  ?>
			<td align="right"><b>&nbsp;<?php echo number_format($totais['TotalQteGeral'],0,',','.');?></b></td>
			<td align="right"><b>&nbsp;<?php echo number_format($totais['TotalValorGeral'],2,',','.');?></b></td>
		  </tr>		
		  </table>
		  <table class="table txt-preto fontsize-pp">
          <tr>
            <td align="center">
      		<input name="BtnAtualizar" type="submit" id="BtnAtualizar" value="Atualizar" class="btn btn-info btn-sm" onClick="GP_popupConfirmMsg('Deseja Atualizar está página?');return document.MM_returnValue">
            </td>
          </tr>
          <tr>
			<td></td>
		  </tr>
		  <tr><td align="center" class="texto"> <?php echo " Segundos: ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.')." "; ?></td></tr>
        </table>
      </form></td>
  </tr>
</table>
</div>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</html>
