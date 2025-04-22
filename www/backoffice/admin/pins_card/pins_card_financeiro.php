<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once $raiz_do_projeto . "includes/gamer/main.php";
require_once $raiz_do_projeto . "class/classPinsCard.php";

set_time_limit ( 30000 ) ;
?>
<link href="/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="/js/global.js"></script>
<script>
$(function(){
    var optDate = new Object();
        optDate.interval = 1000;

    setDateInterval('tf_v_data_inclusao_ini','tf_v_data_inclusao_fim',optDate);
    setDateInterval('tf_v_data_inclusao_ini_env','tf_v_data_inclusao_fim_env',optDate);
});

<!--
function reload() {
        document.form1.action = "pins_card_financeiro.php";
        document.form1.submit();
}
function verifica()
{
        if ((event.keyCode<47)||(event.keyCode>58)){
                  alert("Somente numeros sao permitidos");
                  event.returnValue = false;
        }
}
-->
</script>
<?php
$publisher_array	= VetorOperadorasCard();
$operacao_array		= VetorDistribuidorasCard();
$opr_codigo		= isset($_POST['opr_codigo'])		? $_POST['opr_codigo']		: null;
$distributor_codigo 	= isset($_POST['pin_operacao'])		? $_POST['pin_operacao']	: null;
$lote			= isset($_POST['pin_lote'])		? $_POST['pin_lote']		: null;
$valor          	= isset($_POST['pin_valor'])		? $_POST['pin_valor']		: null;
$time_start_stats 	= getmicrotime();
//paginacao
$p = $_GET['p'];
if(!$p) $p = 1;
$registros = 50;
$registros_total = 0;

//Vericações e Update
$msg = "";
$msg_pin = "";
	
//Recupera as vendas
if($msg == "" && $btPesquisar=='Pesquisar'){
        $sql  = "select 
                                pc.opr_codigo, 
                                pc.distributor_codigo, 
                                pcra.pcra_nome, 
                                to_char(pcra.pcra_dataentrada,'DD/MM/YYYY HH24:MI:SS') as data_envio,	
                                pc.pin_lote_codigo, 
                                pc.pin_valor,
                                pcra.pcra_codinterno
                        from pins_card pc, 
                                pins_card_rel_arquivos pcra
                        where pc.pin_arq_gerado = pcra.pcra_codinterno";
        if(strlen($tf_v_data_inclusao_ini_env))
                        $sql .= " and pcra.pcra_dataentrada >= to_timestamp('".addslashes($tf_v_data_inclusao_ini_env)." 00:00:00', 'DD/MM/YYYY HH24:MI:SS')";
        if(strlen($tf_v_data_inclusao_fim_env))
                        $sql .= " and pcra.pcra_dataentrada <= to_timestamp('".addslashes($tf_v_data_inclusao_fim_env)." 23:59:59', 'DD/MM/YYYY HH24:MI:SS')";
	if(strlen($opr_codigo))
			$sql .= " and pc.opr_codigo=".intval($opr_codigo);
        if(strlen($distributor_codigo))
                        $sql .= " and pc.distributor_codigo=".intval($distributor_codigo);
        if(strlen($lote))
                        $sql .= " and pc.pin_lote_codigo=".intval($lote);
        if(strlen($valor))
                        $sql .= " and pc.pin_valor=".intval($valor);
        $sql .= " group by distributor_codigo, pin_lote_codigo, pc.opr_codigo, pin_valor, pcra.pcra_nome, pcra.pcra_dataentrada, pcra.pcra_codinterno";
        //echo "<br><br>".str_replace("\n", "<br>\n", $sql)."<br><br>";
        $rs_total = SQLexecuteQuery($sql);
        if($rs_total) {
                $registros_total = pg_num_rows($rs_total);

        }
        //inicio totalizador geral
        $total_geral_pins_lote_full = 0;
        $total_geral_valor_pins_lote_full = 0;
        $total_geral_pins_circulante_full = 0;
        $total_geral_valor_pins_circulante_full = 0;
        $total_geral_pins_utilizados_full = 0;
        $total_geral_valor_pins_utilizados_full = 0;
        while($rs_pins_row = pg_fetch_array($rs_total)){ 
                if(strlen($rs_pins_row['pcra_codinterno'])) {
                        $sql_total = "
                                select 
                                        count(pc.pin_codinterno) as total,
                                        count(case when ((pc.pin_arq_gerado is not null) AND (pc.pin_status != '".intval($PINS_STORE_STATUS_VALUES['U'])."') AND (pc.pin_status != '".intval($PINS_STORE_STATUS_VALUES['C'])."') AND (pc.pin_status != '".intval($PINS_STORE_STATUS_VALUES['B'])."')) then 1 end) as circulante,
                                        count(case when ((pc.pin_arq_gerado is not null) AND (pc.pin_status = '".intval($PINS_STORE_STATUS_VALUES['U'])."')) then 1 end) as utilizados
                                from pins_card pc
                                where pc.pin_lote_codigo = ".$rs_pins_row['pin_lote_codigo']." and
                                          pc.opr_codigo = ".$rs_pins_row['opr_codigo']." and
                                          pc.distributor_codigo = ".$rs_pins_row['distributor_codigo']." and
                                          pc.pin_arq_gerado = ".$rs_pins_row['pcra_codinterno'];
                        $rs_total_aux = SQLexecuteQuery($sql_total);
                        if($rs_total_aux && pg_num_rows($rs_total_aux) > 0) {
                                $rs_total_row = pg_fetch_array($rs_total_aux);
                                $total_geral_pins_lote_full += $rs_total_row['total'];
                                $total_geral_valor_pins_lote_full += $rs_pins_row['pin_valor'] * $rs_total_row['total'];
                                $total_geral_pins_circulante_full += $rs_total_row['circulante'];
                                $total_geral_valor_pins_circulante_full += $rs_pins_row['pin_valor'] * $rs_total_row['circulante'];
                        }
                        // TO DO ==> Alterar a query quando for gerar PINs CARDs para EPP CASH
                        $sql_total = "
                                select 
                                        count(case when ((pc.pin_arq_gerado is not null) AND (pich.pin_status = '".intval($PINS_STORE_STATUS_VALUES['U'])."') AND (pich.pih_codretepp = '2')) then 1 end) as utilizados
                                from pins_card pc 
                                        left outer join pins_integracao_card_historico pich ON (pc.pin_codinterno = pich.pih_pin_id) 
                                where pc.pin_lote_codigo = ".$rs_pins_row['pin_lote_codigo']." and
                                          pc.opr_codigo = ".$rs_pins_row['opr_codigo']." and
                                          pc.distributor_codigo = ".$rs_pins_row['distributor_codigo']." and
                                          pc.pin_arq_gerado = ".$rs_pins_row['pcra_codinterno'];

                        if(strlen($tf_v_data_inclusao_ini))
                                        $sql_total .= " and pich.pih_data >= to_timestamp('".addslashes($tf_v_data_inclusao_ini)." 00:00:00', 'DD/MM/YYYY HH24:MI:SS')";
                        if(strlen($tf_v_data_inclusao_fim))
                                        $sql_total .= " and pich.pih_data <= to_timestamp('".addslashes($tf_v_data_inclusao_fim)." 23:59:59', 'DD/MM/YYYY HH24:MI:SS')";
                        $rs_total_uti = SQLexecuteQuery($sql_total);
                        if($rs_total_uti && pg_num_rows($rs_total_uti) > 0) {
                                $rs_total_uti_row = pg_fetch_array($rs_total_uti);
                                $total_geral_pins_utilizados_full += $rs_total_uti_row['utilizados'];
                                $total_geral_valor_pins_utilizados_full += $rs_pins_row['pin_valor'] * $rs_total_uti_row['utilizados'];
                        }
                }
        }
// fim totalizador geral

        $sql .= " order by distributor_codigo,pcra.pcra_dataentrada DESC, pin_valor ";	
        $sql .= " offset " . intval(($p - 1) * $registros) . " limit " . intval($registros);
        $rs_pins = SQLexecuteQuery($sql);
        if(!$rs_pins || pg_num_rows($rs_pins) == 0) $msg = "Nenhum pin encontrado.\n";
}
?>
<div class="col-md-12">
     <ol class="breadcrumb top10">
         <li><a href="#" class="muda-aba" ordem="1">BackOffice - Money</a></li>
         <li class="active">Relatório Financeiro de Cartões</li>
     </ol>
 </div>
<div class="col-md-12 txt-preto fontsize-pp bg-branco">
<?php
include "pins_card_menu.php";
?>
<form name="form1" method="post" action="pins_card_financeiro.php" onsubmit="javascript:return reload();">
    <table class="table txt-preto fontsize-pp">
	<?php
    if($msg_pin)
        echo "<tr><td>".$msg_pin."</td></tr>";
	?>
    <tr valign="top" align="center">
      <td>
			<input type="hidden" name="p" value="<?php echo $p; ?>">
			<table class="table txt-preto fontsize-pp">
    	        <tr bgcolor="F0F0F0">
    	          <td class="texto" align="center" colspan="3"><b>Lista de <?php echo ((($p - 1) * $registros)+1); ?> a <?php echo (($p*$registros)); ?> <?php echo " (Total: ".$registros_total." registro"?><?php if($registros_total>1) echo "s"; ?><?php echo ")"?></b></td>
    	        </tr>
    	        <tr bgcolor="F0F0F0">
    	          <td class="texto" align="center" width="400"><b>Publisher</b></td>
    	          <td class="texto" align="center"><b>Per&iacute;odo de Utiliza&ccedil;&atilde;o</b></td>
    	          <td class="texto" align="center"><b>Per&iacute;odo de Envio</b></td>
    	        </tr>
    	        <tr bgcolor="F5F5FB">
    	          <td class="texto" align="center"><nobr>&nbsp;
                            <select name="opr_codigo" id="opr_codigo" class="combo_normal">
                                    <option value=''<?php if(!$opr_codigo) echo "selected"?>>Selecione o Publisher</option>
                                    <?php foreach ($publisher_array as $key => $value) { ?>
				    <option value=<?php echo "\"".$key.(($opr_codigo==$key)?"\" selected":"\""); ?>><?php echo $value; ?></option>
                                    <?php } ?>
                            </select>
		  </td>
    	          <td class="texto" align="center"><nobr>&nbsp;
					  <input name="tf_v_data_inclusao_ini" type="text" class="form" id="tf_v_data_inclusao_ini" value="<?php echo $tf_v_data_inclusao_ini ?>" size="9" maxlength="10">
					  a 
					  <input name="tf_v_data_inclusao_fim" type="text" class="form" id="tf_v_data_inclusao_fim" value="<?php echo $tf_v_data_inclusao_fim ?>" size="9" maxlength="10">
				  </td>
				  <td class="texto" align="center"><nobr>&nbsp;
					  <input name="tf_v_data_inclusao_ini_env" type="text" class="form" id="tf_v_data_inclusao_ini_env" value="<?php echo $tf_v_data_inclusao_ini_env ?>" size="9" maxlength="10">
					  a 
					  <input name="tf_v_data_inclusao_fim_env" type="text" class="form" id="tf_v_data_inclusao_fim_env" value="<?php echo $tf_v_data_inclusao_fim_env ?>" size="9" maxlength="10">
                  </td>
    	        </tr>
    	        <tr bgcolor="F5F5FB">
    	          <td class="texto" align="center">
                                    <select name="pin_operacao" id="pin_operacao">
                                        <option value='' <?php echo (($distributor_codigo=="")?" selected":"") ?>>Todas as Distribuidoras</option>
									<?php foreach ($operacao_array as $key => $value) { ?>
                                        <option value="<?php echo $key ?>" <?php if($key == $distributor_codigo) echo "selected"; ?>><?php echo $value; ?></option>
                                    <?php } ?>
                                     </select>
				  </td>
    	          <td class="texto" align="center">&nbsp;<b>Lote</b>
                    <input name="pin_lote" id="pin_lote" type="text" value="<?php echo $lote; ?>" size="10" maxlength="10" onKeypress="return verifica();">
                  </td>
                  <td class="texto" align="center">&nbsp;<b>Valor</b> <input name="pin_valor" id="pin_valor" type="text" value="<?php echo $valor; ?>" size="10" maxlength="10" onKeypress="return verifica();"></td>
    	        </tr>
				<tr bgcolor="F5F5FB">
					<td class="texto" align="center" colspan="3">&nbsp;<input type="submit" name="btPesquisar" value="Pesquisar" class="btn btn-sm btn-info"></td>
				</tr>
			</table>
			
			<table class="table txt-preto fontsize-pp">
    	        <tr bgcolor="F0F0F0">
    	          <td class="texto" align="center" width="5%"><b>Publisher</b>&nbsp;</td>
    	          <td class="texto" align="center" width="10%"><b>Distribuidora</b></td>
    	          <td class="texto" align="center" width="15%"><b>Nome do Arquivo</b></td>
				  <td class="texto" align="center" width="15%"><b>Data do Envio<br> do Arquivos</b></td>
    	          <td class="texto" align="center" width="5%"><b>Lote</b></td>
				  <td class="texto" align="center" width="10%"><b>Total de <br>PINs no Lote</b></td>
    	          <td class="texto" align="center" width="5%"><b>Valor</b></td>
    	          <td class="texto" align="center" width="5%"><b>Valor Total de<br>PINs Enviados</b></td>
				  <td class="texto" align="center" width="10%"><b>Total Circulante</b></td>
				  <td class="texto" align="center" width="5%"><b>Valor Total de<br>PINs Circulante</b></td>
				  <td class="texto" align="center" width="5%"><b>PINs Utilizados</td>
				  <td class="texto" align="center" width="5%"><b>Valor Total de<br>PINs Utilizados</b></td>
			    </tr>
		<?php	

			$i=0;
			$irows=0;
			if($rs_pins) {
				$total_geral_pins_lote = 0;
				$total_geral_valor_pins_lote = 0;
				$total_geral_pins_circulante = 0;
				$total_geral_valor_pins_circulante = 0;
				$total_geral_pins_utilizados = 0;
				$total_geral_valor_pins_utilizados = 0;
				while($rs_pins_row = pg_fetch_array($rs_pins)){ 
					$bgcolor = ((++$i) % 2)?" bgcolor=\"F5F5FB\"":"";
					$irows++;
										
			?>
    	        <tr<?php echo $bgcolor?> valign="top">
    	          <td class="texto" align="center"><nobr>&nbsp;<?php echo $publisher_array[$rs_pins_row['opr_codigo']]." (".$rs_pins_row['opr_codigo'].")";?></nobr></td>
    	          <td class="texto" align="center"><nobr>&nbsp;<?php echo substr($operacao_array[$rs_pins_row['distributor_codigo']],0,10)." (".$rs_pins_row['distributor_codigo'].")" ?>&nbsp;</nobr></td>
    	          <td class="texto" align="center"><nobr>&nbsp;<?php echo $rs_pins_row['pcra_nome']?>&nbsp;</nobr></td>
    	          <td class="texto" align="center"><nobr>&nbsp;<?php echo $rs_pins_row['data_envio']?>&nbsp;</nobr></td>
    	          <td class="texto" align="center">&nbsp;<?php echo $rs_pins_row['pin_lote_codigo']?>&nbsp;</td>
    	          <td class="texto" align="right">&nbsp;<?php
					if(strlen($rs_pins_row['pcra_codinterno'])) {
						$sql_total = "
							select 
								count(pc.pin_codinterno) as total,
								count(case when ((pc.pin_arq_gerado is not null) AND (pc.pin_status != '".intval($PINS_STORE_STATUS_VALUES['U'])."') AND (pc.pin_status != '".intval($PINS_STORE_STATUS_VALUES['C'])."') AND (pc.pin_status != '".intval($PINS_STORE_STATUS_VALUES['B'])."')) then 1 end) as circulante
							from pins_card pc
							where pc.pin_lote_codigo = ".$rs_pins_row['pin_lote_codigo']." and
                                                                  pc.opr_codigo = ".$rs_pins_row['opr_codigo']." and
                                                        	  pc.distributor_codigo = ".$rs_pins_row['distributor_codigo']." and
								  pc.pin_arq_gerado = ".$rs_pins_row['pcra_codinterno'];
					 	$rs_total = SQLexecuteQuery($sql_total);
						if($rs_total && pg_num_rows($rs_total) > 0) {
							$rs_total_row = pg_fetch_array($rs_total);
							echo $rs_total_row['total'];
							$total_geral_pins_lote += $rs_total_row['total'];
						}
				   }
				  ?>&nbsp;</td>
				  <td class="texto" align="right"><nobr>&nbsp;<?php echo $rs_pins_row['pin_valor']?>&nbsp;</nobr></td>
				  <td class="texto" align="right"><nobr>&nbsp;<?php
					  $valor_aux = $rs_pins_row['pin_valor'] * $rs_total_row['total'];
					  echo "R$".number_format($valor_aux, 2, ',', '.');
					  $total_geral_valor_pins_lote += $valor_aux;
				  ?>&nbsp;</nobr></td>
				  <td class="texto" align="right"><nobr>&nbsp;<?php 
					if(strlen($rs_pins_row['pcra_codinterno'])&&$rs_total && pg_num_rows($rs_total) > 0) {
						echo $rs_total_row['circulante'];
						$total_geral_pins_circulante += $rs_total_row['circulante'];	
					}
				  ?>&nbsp;</nobr></td>
				  <td class="texto" align="right">&nbsp;<?php
					  $valor_aux = $rs_pins_row['pin_valor'] * $rs_total_row['circulante'];
					  echo "R$".number_format($valor_aux, 2, ',', '.');
					  $total_geral_valor_pins_circulante += $valor_aux;
				  ?>&nbsp;</td>
    	          <td class="texto" align="right">&nbsp;<?php
					if(strlen($rs_pins_row['pcra_codinterno'])) {
                                                // TO DO ==> Alterar a query quando for gerar PINs CARDs para EPP CASH
						$sql_total = "
							select 
								count(case when ((pc.pin_arq_gerado is not null) AND (pich.pin_status = '".intval($PINS_STORE_STATUS_VALUES['U'])."') AND (pich.pih_codretepp = '2')) then 1 end) as utilizados
							from pins_card pc 
                                                                left outer join pins_integracao_card_historico pich ON (pc.pin_codinterno = pich.pih_pin_id) 
							where pc.pin_lote_codigo = ".$rs_pins_row['pin_lote_codigo']." and
                                                                  pc.opr_codigo = ".$rs_pins_row['opr_codigo']." and
                                                        	  pc.distributor_codigo = ".$rs_pins_row['distributor_codigo']." and
								  pc.pin_arq_gerado = ".$rs_pins_row['pcra_codinterno'];
						if(strlen($tf_v_data_inclusao_ini))
								$sql_total .= " and pich.pih_data >= to_timestamp('".addslashes($tf_v_data_inclusao_ini)." 00:00:00', 'DD/MM/YYYY HH24:MI:SS')";
						if(strlen($tf_v_data_inclusao_fim))
								$sql_total .= " and pich.pih_data <= to_timestamp('".addslashes($tf_v_data_inclusao_fim)." 23:59:59', 'DD/MM/YYYY HH24:MI:SS')";
						$rs_total_uti = SQLexecuteQuery($sql_total);
						if($rs_total_uti && pg_num_rows($rs_total_uti) > 0) {
							$rs_total_uti_row = pg_fetch_array($rs_total_uti);
							echo $rs_total_uti_row['utilizados'];
							$total_geral_pins_utilizados += $rs_total_uti_row['utilizados'];	
						}
				   }
				  ?>&nbsp;</td>
    	          <td class="texto" align="right">&nbsp;<?php
					 $valor_aux = $rs_pins_row['pin_valor'] * $rs_total_uti_row['utilizados'];
					  echo "R$".number_format($valor_aux, 2, ',', '.');
					  $total_geral_valor_pins_utilizados += $valor_aux;
				  ?>&nbsp;</td>
				</tr>
		<?php
				}
		?>
					<tr>
					  <td class="texto" align="right" colspan="5">&nbsp;<font style='font-weight:bold;' size='2'>Subtotais da P&aacute;gina Atual  :</font>&nbsp;</td>
					  <td class="texto" align="right"><font style='font-weight:bold;' size='2'><?php echo $total_geral_pins_lote;?></font></nobr></td>
					  <td class="texto" align="right">&nbsp;&nbsp;</td>
					  <td class="texto" align="right"><nobr><font style='font-weight:bold;' size='2'>R$<?php echo number_format($total_geral_valor_pins_lote, 2, ',', '.');?></font></nobr></td>
					  <td class="texto" align="right"><nobr><font style='font-weight:bold;' size='2'><?php echo $total_geral_pins_circulante;?></font></nobr></td>
					  <td class="texto" align="right"><nobr><font style='font-weight:bold;' size='2'>R$<?php echo number_format($total_geral_valor_pins_circulante, 2, ',', '.');?></font></nobr></td>
					  <td class="texto" align="right"><nobr><font style='font-weight:bold;' size='2'><?php echo $total_geral_pins_utilizados;?></font></nobr></td>
					  <td class="texto" align="right"><nobr><font style='font-weight:bold;' size='2'>R$<?php echo number_format($total_geral_valor_pins_utilizados, 2, ',', '.');?></font></nobr></td>
					</tr>
					<tr>
					  <td class="texto" align="right" colspan="5">&nbsp;<font style='font-weight:bold;' size='2'>Total Geral de Todas as P&aacute;ginas  :</font>&nbsp;</td>
					  <td class="texto" align="right"><font style='font-weight:bold;' size='2'><?php echo $total_geral_pins_lote_full;?></font></nobr></td>
					  <td class="texto" align="right">&nbsp;&nbsp;</td>
					  <td class="texto" align="right"><nobr><font style='font-weight:bold;' size='2'>R$<?php echo number_format($total_geral_valor_pins_lote_full, 2, ',', '.');?></font></nobr></td>
					  <td class="texto" align="right"><nobr><font style='font-weight:bold;' size='2'><?php echo $total_geral_pins_circulante_full;?></font></nobr></td>
					  <td class="texto" align="right"><nobr><font style='font-weight:bold;' size='2'>R$<?php echo number_format($total_geral_valor_pins_circulante_full, 2, ',', '.');?></font></nobr></td>
					  <td class="texto" align="right"><nobr><font style='font-weight:bold;' size='2'><?php echo $total_geral_pins_utilizados_full;?></font></nobr></td>
					  <td class="texto" align="right"><nobr><font style='font-weight:bold;' size='2'>R$<?php echo number_format($total_geral_valor_pins_utilizados_full, 2, ',', '.');?></font></nobr></td>
					</tr>
		<?php
				if($irows==0) {
			?>
					<tr>
					  <td class="texto" align="center" colspan="12">&nbsp;<font color='#FF0000'>N&atilde;o foram encontrados pins para os valores escolhidos</font></td>
					</tr>
			<?php
				}

			} else {
		?>
    	        <tr>
    	          <td class="texto" align="center" colspan="12">&nbsp;<font color='#FF0000'>N&atilde;o foram encontrados pins para os valores escolhidos</font></td>
    	        </tr>
		<?php
			}
		?>
			</table>

      </td>
    </tr>
	</table>

	<br>&nbsp;
	<table class="table txt-preto fontsize-pp">
    <tr>
      	<td align="center" class="texto"><nobr>
      		<?php if($p > 1){ ?>
         	<input type="button" name="btAnterior" value=" < " OnClick="window.location='?p=<?php echo $p-1?><?php echo $varsel?>';" class="btn btn-sm btn-info">
         	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<?php } ?>
         	<input type="button" name="btOK" value="Voltar" OnClick="window.location='index.php';" class="btn btn-sm btn-info">
      		<?php if($p < ($registros_total/$registros)){ ?>
         	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
         	<input type="button" name="btProximo" value=" > " OnClick="window.location='?p=<?php echo $p+1?><?php echo $varsel?>';" class="btn btn-sm btn-info">
			<?php } ?></nobr>
      	</td>
    </tr>
	</table>
	<br>&nbsp;

	<table class="table txt-preto fontsize-pp">
	  <tr align="center"> 
		<td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td><td bgcolor="#FFFFFF" class="texto">Processamento em <?php echo number_format(getmicrotime() - $time_start_stats, 2, '.', '.') ?> s.</font></td><td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td>
	  </tr>
	</table>
</form>
</div>
</body>
</html>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
