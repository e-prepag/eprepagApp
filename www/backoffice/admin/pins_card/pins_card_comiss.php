<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once $raiz_do_projeto . "includes/gamer/main.php";
require_once $raiz_do_projeto . "class/classPinsCard.php";

// Configurado para naun expirar em 10 minutos de execução
// no DEV foi conseguido gerar pouco mais de 47000 PINs neste intervalo de tempo
set_time_limit(600);

$publisher_array	= VetorOperadorasCard();
$operacao_array		= VetorDistribuidorasCard();
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
?>
<div class="col-md-12">
     <ol class="breadcrumb top10">
         <li><a href="#" class="muda-aba" ordem="1">BackOffice - Money</a></li>
         <li class="active">Relatório de Comissão Paga de Cartões por Distribuidor</li>
     </ol>
 </div>
<div class="col-md-12 txt-preto fontsize-pp bg-branco">
<?php
include "pins_card_menu.php";
// TO DO ==> Alterar a query quando for gerar PINs CARDs para EPP CASH
//Novo modelo
$sql = "select date_trunc('month',date_seq) as mes,distributor_codigo, count(codigo_pin) as n, ";
if($chk_total==1) {
	$sql .= "SUM(pc.pin_valor) as total ";
}
else {
	$sql .= "SUM(pc.pin_valor*pcd_comissao/100) as total ";
}
$sql .= " 
        from ( select (generate_series(0,(CURRENT_DATE-'2019-01-01')) + date '2019-01-01') as date_seq) d 
             left outer join ( 
                                select date_trunc('day', min(pich.pih_data)) as s_dia, pc2.pin_codinterno as codigo_pin,pcd_comissao 
                                from pins_card_distribuidoras pcd 
                                inner join pins_card pc2 ON (pcd.pcd_id_distribuidor = pc2.distributor_codigo)
                                left outer join pins_integracao_card_historico pich ON (pc2.pin_codinterno = pich.pih_pin_id) 
                                where pich.pin_status = '".intval($PINS_STORE_STATUS_VALUES['U'])."' AND pich.pih_codretepp = '2'
                                    and pih_data >= '2019-01-01 00:00:00'
                                group by codigo_pin,pcd_comissao
                        ) t on d.date_seq=t.s_dia 
        left outer join pins_card pc ON (pc.pin_codinterno=t.codigo_pin) 
        where date_seq >= '2019-01-01 00:00:00'
        group by distributor_codigo,mes";
//echo "<br><br>".str_replace("\n", "<br>\n", $sql)."<br><br>";
$rs_pins = SQLexecuteQuery($sql);
if ($rs_pins) {
	if (is_array($vetorPeriodo)) {
		unset($vetorPeriodo);
	}
	// Preenche Valores mensais e totais ================================================= 
	 while($rs_pins_row = pg_fetch_array($rs_pins)){
		$vetorPeriodo[$rs_pins_row['mes']][$rs_pins_row['distributor_codigo']]['TotalValor']	+= $rs_pins_row['total'];
		$vetorPeriodo[$rs_pins_row['mes']][$rs_pins_row['distributor_codigo']]['TotalQte']		+= $rs_pins_row['n'];
		$vetorPeriodo[$rs_pins_row['mes']]['TotalValorGeral']	+= $rs_pins_row['total'];
		$vetorPeriodo[$rs_pins_row['mes']]['TotalQteGeral']		+= $rs_pins_row['n'];
	}//end while
	//echo "<pre>".print_r($vetorPeriodo,true)."</pre>";
	uksort( $vetorPeriodo, 'uksort_cmp');
	//echo "-----------------------------------------<br>";
	//echo "<pre>".print_r($vetorPeriodo,true)."</pre>";
}//end if ($rs_pins)
?>
<table class="table txt-preto fontsize-pp">
  <tr>
    <td>
        <form name="form1" method="post" action="<?php echo $php_self ?>">
        <div align="right" style="font-weight:bold;color:#00008C;">
            <input name="chk_total" id="chk_total" type="checkbox"  <?php if($chk_total) echo "checked"; ?> onClick="document.form1.submit();" value="1">Exibi o total utilizado com a comiss&atilde;o nos per&iacute;odos
        </div>
        <table class="table txt-preto fontsize-pp">
          <tr class="bg-azul-claro" style="font-size:14px;font-weight:bold;color:#FFFFFF;font-family:Arial, Helvetica, sans-serif;">
            <td rowspan="2" align="center">M&ecirc;s</td>
			<?php foreach ($operacao_array as $key => $value) { ?>
			<td colspan="2" align="center"><?php echo str_replace(" - Formato (4)","",$value); ?></td>
			<?php } ?>
			<td colspan="2" align="center">Total</td>
		  </tr>
		  <tr class="bg-azul-claro" style="font-size:10px;font-weight:bold;color:#FFFFFF;font-family:Arial, Helvetica, sans-serif;">
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
            <td class="texto"><b><?php echo mes_do_ano_comiss($data);?></b></td>
			<?php foreach ($operacao_array as $key => $value2) { ?>
            <td class="texto" align="right"><b><?php echo number_format($vetorPeriodo[$data][$key]['TotalQte'],0,',','.');?></b></td>
			<td class="texto" align="right"><b><?php echo number_format($vetorPeriodo[$data][$key]['TotalValor'],2,',','.');?></b></td>
			<?php
					$totais['TotalQte'][$key]	+= $vetorPeriodo[$data][$key]['TotalQte'];
					$totais['TotalValor'][$key]	+= $vetorPeriodo[$data][$key]['TotalValor'];
			  	} 
		    ?>
			<td class="texto" align="right"><b><?php echo number_format($vetorPeriodo[$data]['TotalQteGeral'],0,',','.');?></b></td>
			<td class="texto" align="right"><b><?php echo number_format($vetorPeriodo[$data]['TotalValorGeral'],2,',','.');?></b></td>
		  </tr>
          <?php
			  $totais['TotalQteGeral']	+= $vetorPeriodo[$data]['TotalQteGeral'];
			  $totais['TotalValorGeral']+= $vetorPeriodo[$data]['TotalValorGeral'];
			  $bg_col = ($bg_col==$bg_col_01)?$bg_col_02:$bg_col_01;
		  }//end foreach ($vetorPeriodo as $data => $value)
		  ?>
		  <tr class="bg-azul-claro" style="font-size:14px;font-weight:bold;color:#FFFFFF;font-family:Arial, Helvetica, sans-serif;">
			<td align="center">Totais</td>
			<?php foreach ($operacao_array as $key => $value2) { ?>
            <td align="right"><b><?php echo number_format($totais['TotalQte'][$key],0,',','.');?></b></td>
			<td align="right"><b><?php echo number_format($totais['TotalValor'][$key],2,',','.');?></b></td>
			<?php 	}  ?>
			<td align="right"><b><?php echo number_format($totais['TotalQteGeral'],0,',','.');?></b></td>
			<td align="right"><b><?php echo number_format($totais['TotalValorGeral'],2,',','.');?></b></td>
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
		  <tr><td> <?php require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php"; ?></td></tr>
		  <tr><td align="center" class="texto"> <?php echo " Segundos: ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.')." "; ?></td></tr>
        </table>
      </form></td>
  </tr>
</table>
</div>
</body>
</html>
