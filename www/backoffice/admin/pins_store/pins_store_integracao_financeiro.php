<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once $raiz_do_projeto . "includes/main.php";
require_once $raiz_do_projeto . "includes/gamer/main.php";
require_once $raiz_do_projeto . "class/classIntegracaoPin.php";
require_once $raiz_do_projeto . "class/classIntegracaoPinCash.php";
require_once $raiz_do_projeto . "includes/inc_functions.php";
require_once $raiz_do_projeto . "class/classPinsStore.php";       

$opr_codigo = isset($_POST['pin_operacao'])     ? (int) $_POST['pin_operacao']		: null;
set_time_limit ( 30000 ) ;
$time_start_stats = getmicrotime();

$operacao_array = VetorIntegrator();
//paginacao
$p = isset($_GET['p']) ? $_GET['p'] : false;
if(!$p) $p = 1;
$registros = 50;
$registros_total = 0;
//rotinas de inicializacao se click em botao gerar arquivo

//Vericações e Update
$msg = "";

//Recupera as vendas
if(!empty($btPesquisar)){ //case pih_gocash
	$sql  = "SELECT *,
					to_char(pih_data,'DD/MM/YYYY HH24:MI:SS') as pih_data_aux from pins_integracao_cash_historico WHERE pih_codretepp='".$notify_list_values['SU']."'"; 
	if(!empty($opr_codigo))
				$sql .= " and pih_id = ".addslashes($opr_codigo);
	if(strlen($tf_v_data_inclusao_ini))
				$sql .= " and pih_data >= to_timestamp('".addslashes($tf_v_data_inclusao_ini)." 00:00:00', 'DD/MM/YYYY HH24:MI:SS')";
	if(strlen($tf_v_data_inclusao_fim))
				$sql .= " and pih_data <= to_timestamp('".addslashes($tf_v_data_inclusao_fim)." 23:59:59', 'DD/MM/YYYY HH24:MI:SS')";
	$rs_total = SQLexecuteQuery($sql);
	if($rs_total) {
		$registros_total = pg_num_rows($rs_total);
		$vetorValores = array();
		$valorTotalGeral=0;
		while($rs_total_row = pg_fetch_array($rs_total)){
			if($rs_total_row['pih_gocash'] == 1) {
				$sql_valor = "select pgc_face_amount as pin_valor from pins_gocash where pgc_id=".$rs_total_row['pih_pin_id'];
				$rs_valor = SQLexecuteQuery($sql_valor);
				if ($rs_valor_row = pg_fetch_array($rs_valor)) {
					$vetorValores[$rs_total_row['pih_pin_id']] = $rs_valor_row['pin_valor'];
					$valorTotalGeral += $rs_valor_row['pin_valor'];
				}
			}
			else {
				$sql_valor = "select pin_valor from pins_store where pin_codinterno=".$rs_total_row['pih_pin_id'];
				$rs_valor = SQLexecuteQuery($sql_valor);
				if ($rs_valor_row = pg_fetch_array($rs_valor)) {
					$vetorValores[$rs_total_row['pih_pin_id']] = $rs_valor_row['pin_valor'];
					$valorTotalGeral += $rs_valor_row['pin_valor'];
				}
			}//end else
		}
	}
	$sql .= " ORDER BY pih_data DESC";	
	$sql .= " offset " . intval(($p - 1) * $registros) . " limit " . intval($registros);
//echo $sql ."<br>\n";
	$rs_pins = SQLexecuteQuery($sql);
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
});

<!--
	function algumCheckBoxSel()
	{
		frm = document.form1;
		for(i=0; i < frm.length; i++)
		{
			if (frm.elements[i].type == "checkbox")
			{
				if(frm.elements[i].checked)
				{
					return true;
				}
			}
		}
		return false;
	}
	function marcar_desmarcar() {
		frm = document.form1;
		for ( i=1; i < frm.elements.length; i++ ) {
			if ( frm.elements[i].type == "checkbox" ) {
				if ( frm.elements[i].checked == 1 ) {
				   frm.elements[i].checked = 0;
				} else {
				   frm.elements[i].checked = 1;
				}
			}
		}
	}
--> 
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><?php echo $sistema->item->getDescricao() ; ?></li>
    </ol>
</div>  
<div class="col-md-12 txt-preto fontsize-pp">
<?php
include "pins_store_menu.php";
?>
<form name="form1" method="post" action="pins_store_integracao_financeiro.php">
<table class="txt-preto fontsize-pp table">
    <tr valign="top" align="center">
      <td align="center">
			<input type="hidden" name="p" value="<?php echo $p; ?>">
			<table class="txt-preto fontsize-pp table">
    	        <tr bgcolor="F0F0F0">
    	          <td class="texto" align="center" colspan="3"><b>Lista de <?php echo ((($p - 1) * $registros)+1); ?> a <?php echo (($p*$registros)); ?> <?php echo " (Total: ".$registros_total." registro"?><?php if($registros_total>1) echo "s"; ?><?php echo ")"?></b></td>
    	        </tr>
    	        <tr bgcolor="F0F0F0">
    	          <td class="texto" align="center"><b>Operadora</b></td>
    	          <td class="texto" align="center"><b>Per&iacute;odo de cadastro</b></td>
    	          <td class="texto" align="center">&nbsp;</td>
    	        </tr>
    	        <tr bgcolor="F5F5FB">
    	          <td class="texto" align="center"><nobr>
				  <select name="pin_operacao" id="pin_operacao" class="combo_normal">
					<option value=''>Selecione a operadora</option>
			        <?php foreach ($operacao_array as $key => $value) { ?>
				    <option value=<?php echo "\"".$key.(($opr_codigo==$key)?"\" selected":"\""); ?>><?php echo $value; ?></option>
					<?php } ?>
					</select>
				  </td>
    	          <td class="texto" align="center"><nobr>&nbsp;
					  <input name="tf_v_data_inclusao_ini" type="text" class="form" id="tf_v_data_inclusao_ini" value="<?php if(isset($tf_v_data_inclusao_ini)) echo $tf_v_data_inclusao_ini ?>" size="9" maxlength="10">
					  a 
					  <input name="tf_v_data_inclusao_fim" type="text" class="form" id="tf_v_data_inclusao_fim" value="<?php if(isset($tf_v_data_inclusao_fim)) echo $tf_v_data_inclusao_fim ?>" size="9" maxlength="10">
				  </td>
				  <td class="texto" align="center">&nbsp;</td>
    	        </tr>
    	        <tr bgcolor="F5F5FB">
    	          <td class="texto" align="center" colspan="3"><input type="submit" name="btPesquisar" value="Pesquisar" class="btn btn-sm btn-info">
				  </td>
    	        </tr>
			</table>
			<table class="txt-preto table fontsize-pp">
			<tr bgcolor="F0F0F0">
			  <!--td class="texto" align="center" width="10%"><a href="javascript:marcar_desmarcar();">Marcar<br>Desmarcar</a></td-->
    	      <td class="texto" align="center" width="25%"><b>Integrador</b>&nbsp;</td>
			  <td class="texto" align="center" width="25%"><b>ID do PIN</b>&nbsp;</td>
			  <td class="texto" align="center" width="25%"><b>Dia e Hora</b>&nbsp;</td>
			  <td class="texto" align="center" width="25%"><b>Valor do PIN</b>&nbsp;</td>
			  <!--td class="texto" align="center" width="15%"><b>Enviado Para</b></td-->
 			</tr>
    	<?php	

			$i=0;
			$irows=0;
			if(isset($rs_pins) && $rs_pins) {
				while($rs_pins_row = pg_fetch_array($rs_pins)){ 
					$bgcolor = (($i++) % 2)?" bgcolor=\"F5F5FB\"":"";
					$irows++;
			?>
    	        <tr<?php echo $bgcolor?> valign="top">
				  <!--td class="texto" align="center">&nbsp;<input name="chkArq[]" id="chkArq" type="checkbox" value="<?php echo $rs_pins_row['psra_codinterno'];?>" />&nbsp;</td-->
                  <td class="texto" align="center">&nbsp;<?php echo $operacao_array[$rs_pins_row['pih_id']]." (".$rs_pins_row['pih_id'].")"?></td>
    	          <td class="texto" align="right">&nbsp;<?php echo $rs_pins_row['pih_pin_id']?></td>
    	          <td class="texto" align="center">&nbsp;<?php echo $rs_pins_row['pih_data_aux']?>&nbsp;</td>
    	          <td class="texto" align="right">&nbsp;<?php if(isset($vetorValores[$rs_pins_row['pih_pin_id']])) echo number_format($vetorValores[$rs_pins_row['pih_pin_id']],2,',','.')?>&nbsp;</td>
    	          <!--td class="texto">&nbsp;<?php echo $rs_pins_row['psra_envio']?>&nbsp;</td-->
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
					  <td class="texto" align="center" colspan="13">&nbsp;<font color='#FF0000'>Valor Total Geral no Per&iacute;odo: <?php echo number_format($valorTotalGeral,2,',','.')?></font></td>
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
	<table class="txt-preto fontsize-pp">
    <tr>
      	<td align="center" class="texto"><nobr>
      		<?php if($p > 1){ ?>
         	<input type="button" name="btAnterior" value=" < " OnClick="window.location='?p=<?php echo $p-1?><?php echo $varsel?>';" class="btn btn-sm btn-info">
         	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<?php } ?>
         	<input type="button" name="btOK" value="Voltar" OnClick="window.location='../../commerce/index.php';" class="btn btn-sm btn-info">
      		<?php if($p < ($registros_total/$registros)){ ?>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
         	<input type="button" name="btProximo" value=" > " OnClick="window.location='?p=<?php echo $p+1?><?php echo $varsel?>';" class="btn btn-sm btn-info">
			<?php } ?></nobr>
      	</td>
    </tr>
	</table>
	<br>&nbsp;

	<table class="txt-preto fontsize-pp">
	  <tr align="center"> 
		<td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td><td bgcolor="#FFFFFF" class="texto">Processamento em <?php echo number_format(getmicrotime() - $time_start_stats, 2, '.', '.') ?> s.</font></td><td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td>
	  </tr>
	</table>

	</form>
	<!--A T E N &Ccedil; &Atilde; O : Definido quem receber&aacute; os arquivos ser&aacute; exibido um combo com a listagem de poss&iacute;veis receptores e um bot&atilde;o para vincular os arquivos a estes.-->
	</center>
</div>
</body>
</html>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>