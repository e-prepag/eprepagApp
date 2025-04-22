<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once $raiz_do_projeto . "includes/gamer/main.php";
require_once $raiz_do_projeto . "class/classPinsPublishers.php";

$publisher_array = VetorOperadoras();

$opr_codigo 		= isset($_POST['pin_operacao'])     ? (int) $_POST['pin_operacao']		: null;
$time_start_stats = getmicrotime();

set_time_limit ( 30000 ) ;

$registros_total = 0;
//rotinas de inicializacao se click em botao gerar arquivo

//Vericações e Update
$msg = "";

//Recupera as vendas
if(!empty($btPesquisar)){ 
        $sql  = "SELECT *,
                        to_char(pih_data,'DD/MM/YYYY HH24:MI:SS') as pih_data_aux ";
        if(strlen($data_venda_exclusao))
                            $sql .= ", to_char(vg_data_inclusao,'DD/MM/YYYY HH24:MI:SS') as vg_data_inclusao_aux";
        $sql .= "
                FROM pins_integracao_historico pih
                        inner join pins p ON pin_codinterno=pih_pin_id ";
        if(strlen($data_venda_exclusao))
                            $sql .= " 
                        inner join tb_dist_venda_games_modelo_pins on vgmp_pin_codinterno  = pih_pin_id
                        inner join tb_dist_venda_games_modelo on vgm_id = vgmp_vgm_id
                        inner join tb_dist_venda_games on vg_id = vgm_vg_id
                        ";
        $sql .= "
                WHERE pih_codretepp='2' 
                        and pih.pin_status = '8'
                    "; 
	if(!empty($opr_codigo))
				$sql .= " and pih_id = ".addslashes($opr_codigo);
	if(strlen($tf_v_data_inclusao_ini))
				$sql .= " and pih_data >= to_timestamp('".addslashes($tf_v_data_inclusao_ini)." 00:00:00', 'DD/MM/YYYY HH24:MI:SS')";
	if(strlen($tf_v_data_inclusao_fim))
				$sql .= " and pih_data <= to_timestamp('".addslashes($tf_v_data_inclusao_fim)." 23:59:59', 'DD/MM/YYYY HH24:MI:SS')";
	if(strlen($data_venda_exclusao))
                            $sql .= " and vg_ultimo_status='5' and vg_data_inclusao >= to_timestamp('".addslashes($data_venda_exclusao)." 00:00:00', 'DD/MM/YYYY HH24:MI:SS')";
        $sql .= " ORDER BY pih_data DESC";	
	//echo $sql ."<br>\n";
	$rs_pins = SQLexecuteQuery($sql);
        $registros_total = pg_num_rows($rs_pins);
	if(!$rs_pins || pg_num_rows($rs_pins) == 0) $msg = "Nenhum pin encontrado.\n";
}
?>
<link href="/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="/js/global.js"></script>
<script>
$(function(){
    var optDate = new Object();
        optDate.interval = 1000;

    setDateInterval('tf_v_data_inclusao_ini','tf_v_data_inclusao_fim',optDate);
    
    $("#data_venda_exclusao").datepicker();
});

	function validaFiltros()
	{
		if (document.form1.tf_v_data_inclusao_ini.value.trim() == "")
		{
			alert("Favor selecionar ao menos a Data Inicial da Pesquisa.");
			document.form1.tf_v_data_inclusao_ini.focus();
			return false;
		}
		else return true;
	}
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><?php echo $sistema->item->getDescricao(); ?></li>
    </ol>
</div>
<form id="form1" name="form1" method="post" action="" onSubmit="return validaFiltros()">
    <table class="table txt-preto fontsize-pp">
    <tr valign="top" align="center">
      <td align="center">
			<input type="hidden" name="p" value="<?php echo $p; ?>">
			<table class="table txt-preto fontsize-pp">
    	        <tr bgcolor="F0F0F0">
    	          <td class="texto" align="center" colspan="3"><b><?php echo " (Total: ".$registros_total." registro"?><?php if($registros_total>1) echo "s"; ?><?php echo ")"?></b></td>
    	        </tr>
    	        <tr bgcolor="F0F0F0">
    	          <td class="texto" align="center"><b>Operadora</b></td>
    	          <td class="texto" align="center"><b>Per&iacute;odo de Utilização</b></td>
    	          <td class="texto" align="center"><b>Excluir PINs Vendidos Anteriores à</b></td>
    	        </tr>
    	        <tr bgcolor="F5F5FB">
                    <td class="texto" align="center"><nobr>
				  <select name="pin_operacao" id="pin_operacao" class="combo_normal">
					<option value=''<?php if(!$pin_operacao) echo "selected"?>>Selecione a operadora</option>
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
                    <td class="texto" align="center">&nbsp;
                        <input name="data_venda_exclusao" type="text" class="form" id="data_venda_exclusao" value="<?php echo $data_venda_exclusao ?>" size="9" maxlength="10">
                    </td>
    	        </tr>
    	        <tr bgcolor="F5F5FB">
    	          <td class="texto" align="center" colspan="3"><input type="submit" name="btPesquisar" value="Pesquisar" class="btn btn-sm btn-info">
		  </td>
    	        </tr>
        </table>
<br>                        
        <table class="table txt-preto fontsize-pp">
                    <tr bgcolor="F0F0F0">
			  <td class="texto" align="center"><b>ID do PIN</b>&nbsp;</td>
			  <td class="texto" align="center"><b>Integrador</b>&nbsp;</td>
			  <td class="texto" align="center"><b>Dia e Hora Utilização</b>&nbsp;</td>
			  <td class="texto" align="center"><b><?php echo (strlen($data_venda_exclusao)?"Data de Venda":"VAZIO"); ?></b>&nbsp;</td>
			  <td class="texto" align="center"><b>PIN</b>&nbsp;</td>
			  <td class="texto" align="center"><b>Valor do PIN</b>&nbsp;</td>
			</tr>
    	<?php	

			$i=0;
			$irows=0;
                        $valorTotalGeral=0;
			if($rs_pins) {
				while($rs_pins_row = pg_fetch_array($rs_pins)){ 
					$bgcolor = (($i++) % 2)?" bgcolor=\"F5F5FB\"":"";
					$irows++;
                                        $valorTotalGeral += $rs_pins_row['pin_valor'];
			?>
    	        <tr<?php echo $bgcolor?> valign="top">
		  <td class="texto" align="right">&nbsp;<?php echo $rs_pins_row['pih_pin_id']; ?></td>
    	          <td class="texto" align="center">&nbsp;<?php echo " (".$rs_pins_row['pih_id'].") ".$publisher_array[$rs_pins_row['pih_id']]; ?></td>
    	          <td class="texto" align="center">&nbsp;<?php echo $rs_pins_row['pih_data_aux'];?>&nbsp;</td>
    	          <td class="texto" align="center">&nbsp;<?php echo (strlen($data_venda_exclusao)?$rs_pins_row['vg_data_inclusao_aux']:"&nbsp;");?></td>
                  <td class="texto" align="center">&nbsp;<?php echo $rs_pins_row['pin_codigo']; ?>&nbsp;</td>
    	          <td class="texto" align="right">&nbsp;<?php echo number_format($rs_pins_row['pin_valor'],2,',','.'); ?>&nbsp;</td>
    	        		</tr>
    	<?php	
				}
				if($irows==0) {
			?>
					<tr>
					  <td class="texto" align="center" colspan="13">&nbsp;<font color='#FF0000'>N&atilde;o foram encontradas arquivos para os valores escolhidos</font></td>
					</tr>
			<?php
				}
				else {
			?>
					<tr>
                        <td class="texto" align="center" colspan="13"><span class="txt-verde"><strong>Valor Total Geral no Per&iacute;odo: <?php echo number_format($valorTotalGeral,2,',','.')?></strong></span></td>
					</tr>
			<?php
				}

			} else {
		?>
    	        <tr>
    	          <td class="texto" align="center" colspan="13">&nbsp;<font color='#FF0000'>N&atilde;o foram encontradas arquivos para os valores escolhidos</font></td>
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
	  <tr align="center"> 
		<td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td><td bgcolor="#FFFFFF" class="texto">Processamento em <?php echo number_format(getmicrotime() - $time_start_stats, 2, '.', '.') ?> s.</font></td><td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td>
	  </tr>
	</table>

	</form>
	</center>

</body>
</html>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>