<?php
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once $raiz_do_projeto . "includes/main.php";
require_once $raiz_do_projeto . "includes/gamer/main.php";
require_once $raiz_do_projeto . "class/classIntegracaoPin.php";
require_once $raiz_do_projeto . "class/classIntegracaoPinCash.php";
require_once $raiz_do_projeto . "includes/inc_functions.php";
require_once $raiz_do_projeto . "class/classPinsStore.php";         

if(!isset($btnSubmit) || !$btnSubmit) {
	$tf_v_data_inclusao_ini = date("d/m/Y");
	$tf_v_data_inclusao_fim = date("d/m/Y");
}

//echo "distributor_codigo: '$distributor_codigo'<br>";
//echo "sel_status: '$sel_status'<br>";

if(!isset($distributor_codigo)) $distributor_codigo = "";

if(!isset($sel_status)) $sel_status = "";

$sql = "select * from pins_store where pin_qtde_carrinho>1 ";
if($sel_status!="") {
	$sql .= "and pin_status = $sel_status ";
}
if($distributor_codigo!="") {
	$sql .= "and distributor_codigo = '$distributor_codigo' ";
}
if($tf_v_data_inclusao_ini) {
	if(verifica_data_rc($tf_v_data_inclusao_ini) != 0 ) {
		$sql .= "and pin_dataentrada >= '".formata_data_rc($tf_v_data_inclusao_ini,1)." 00:00:00' ";
	}
}
if($tf_v_data_inclusao_fim) {
	if(verifica_data_rc($tf_v_data_inclusao_fim) != 0 ) {
		$sql .= "and pin_dataentrada <= '".formata_data_rc($tf_v_data_inclusao_fim,1)." 23:59:59' ";
	}
}
if(!empty($pin_id)) {
	$sql .= "and pin_codinterno = $pin_id ";
}
$sql .= " order by pin_qtde_carrinho desc";
//echo "$sql<br>";
$rs = SQLexecuteQuery($sql);
//echo "pg_num_rows(rs): ".pg_num_rows($rs)."<br>";

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
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><?php echo $sistema->item->getDescricao()." ".date("Y-m-d H:i:s") ; ?></li>
    </ol>
</div>
<form method="post" action="" name="form1">
<table class="table txt-preto fontsize-pp">
<tr>
	<td>
        Data início:
    </td>
    <td>
        <input name="tf_v_data_inclusao_ini" type="text" class="form" id="tf_v_data_inclusao_ini" value="<?php echo $tf_v_data_inclusao_ini ?>" size="10" maxlength="10">
	</td>
	<td>
        Data fim:
    </td>
    <td>
        <input name="tf_v_data_inclusao_fim" type="text" class="form" id="tf_v_data_inclusao_fim" value="<?php echo $tf_v_data_inclusao_fim ?>" size="10" maxlength="10">
	</td>
    <td>
        ID do PIN:
    </td>
    <td>
		<input name="pin_id" type="text" class="form" id="pin_id" value="<?php if(isset($pin_id)) echo $pin_id; ?>" size="9" maxlength="10">
	</td>
</tr>
<tr>
	<td>
        Distribuidor:
    </td>
    <td>
        <select name="distributor_codigo" id="distributor_codigo">
			<option value='' <?php echo (($distributor_codigo=="")?" selected":"") ?>>Todas as Distribuidoras</option>
			<?php
			$operacao_array = VetorDistribuidoras();
			foreach ($operacao_array as $key => $value) { ?>
			<option value="<?php echo $key ?>" <?php if($key == $distributor_codigo) echo "selected"; ?>><?php echo $value; ?></option>
			<?php } ?>
		 </select>
	</td>
	<td>
        Status: 
    </td>
    <td colspan="3">
		<select id='sel_status' name='sel_status'> 
			<option value=''<?php echo (($sel_status=="")?" selected":""); ?>>Todos os status</option> 
			<?php
			foreach($GLOBALS['PINS_STORE_STATUS'] as $key => $val) {
			if ($key <> 1)
				echo "<option value='".$key."'".(($sel_status== $key)?" selected":"").">".$val." (".$key.")</option>\n";
			}
			?>
		</select>
	</td>
</tr>
<tr>
    <td colspan="6"><input type="submit" name="btnSubmit" class="btn btn-sm btn-info pull-right" value="Atualiza"></td>
</tr>
</table>
	
</form>
<?php


	if(!$rs || pg_num_rows($rs) == 0) {
		echo "Nenhum produto encontrado.<br>\n";
	} else {

		$s_several = ((pg_num_rows($rs)>1)?"s":"");
		echo "<p>Encontrado$s_several ".pg_num_rows($rs)." registro$s_several</p>";
		echo "<table border='1' cellpadding='2' cellspacing='2' bordercolor='#cccccc' style='border-collapse:collapse;'>\n";
		echo "<tr align='center' style='font-weight:bold; background-color:#ffffcc'>\n";
		echo "<td>Qtde</td>\n";
		echo "<td>Data Criação</td>\n";
		echo "<td>Distribuidor</td>\n";
		echo "<td>ID PIN</td>\n";
		echo "<td>PIN</td>\n";
		echo "<td>Valor</td>\n";
		echo "<td>Status</td>\n";
		echo "<td>Serial</td>\n";
		echo "<td>Validade</td>\n";
		echo "<td>Lote</td>\n";
		echo "<td>Formato</td>\n";
		echo "<td>Caracter</td>\n";
		echo "<td>Em Arquivo</td>\n";
		echo "<td>Canal</td>\n";
		echo "<td>Bloqueio</td>\n";
		echo "</tr>\n";

		while($rs_row = pg_fetch_array($rs)) {
			$recibo_formatted = $rs_row['pin_qtde_carrinho'];	//wordwrap($rs_row['rprs_recibo'], 41, "\n", true);

			// se limite é válido -> Procesa solicitação
			echo "<tr onmouseover=\"bgColor='#CFDAD7'\" onmouseout=\"bgColor='#FFFFFF'\"".((strlen($recibo_formatted)>0)?" title=\"".$recibo_formatted." tentativas\"":"").">\n";
			echo "<td align='center'>&nbsp;".$rs_row['pin_qtde_carrinho']."&nbsp;</td>\n";
			echo "<td>&nbsp;".substr($rs_row['pin_dataentrada'], 0, 19)."&nbsp;</td>\n";
			echo "<td align='center'>&nbsp;".substr($operacao_array[$rs_row['distributor_codigo']],0,strpos($operacao_array[$rs_row['distributor_codigo']],'-'))."&nbsp;</td>\n";
			echo "<td align='center'>&nbsp;".$rs_row['pin_codinterno']."&nbsp;</td>\n";
			echo "<td align='center'>&nbsp;".$rs_row['pin_codigo']."&nbsp;</td>\n";
			echo "<td align='center'>&nbsp;".$rs_row['pin_valor']."&nbsp;</td>\n";
			echo "<td>&nbsp;".$GLOBALS['PINS_STORE_STATUS'][$rs_row['pin_status']]."&nbsp;</td>\n";
			echo "<td align='center'>&nbsp;".$rs_row['pin_serial']."&nbsp;</td>\n";
			echo "<td align='center'>&nbsp;".substr($rs_row['pin_validade'], 0, 19)."&nbsp;</td>\n";
			echo "<td align='center'>&nbsp;".$rs_row['pin_lote_codigo']."&nbsp;</td>\n";
			echo "<td align='center'>&nbsp;".$rs_row['pin_formato']."&nbsp;</td>\n";
			echo "<td align='center'>&nbsp;".$rs_row['pin_caracter']."&nbsp;</td>\n";
			echo "<td align='center'>&nbsp;".$rs_row['pin_arq_gerado']."&nbsp;</td>\n";
			echo "<td align='center'>&nbsp;".$rs_row['pin_canal']."&nbsp;</td>\n";
			echo "<td align='center'>&nbsp;".$rs_row['pin_bloqueio']."&nbsp;</td>\n";
			echo "</tr>\n";
		
//			die("<br>Stop");
		}
		echo "</table>\n";

	}

?>