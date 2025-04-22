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

if(!isset($sel_tipo)) $sel_tipo = "";

if(!isset($sel_status)) $sel_status = "";

$sql = "select count(*) as total, max(psah_data) as data_max,min(psah_data) as data_min,psah_pin_id,psah_acao from pins_store_apl_historico where psah_pin_id!=0 ";
if($sel_status!="") {
	$sql .= "and pin_status = $sel_status ";
}
if($sel_tipo!="") {
	$sql .= "and psah_acao = '$sel_tipo' ";
}
if(!empty($ug_id)) {
	$sql .= "and psah_autor = $ug_id ";
}
if($tf_v_data_inclusao_ini) {
	if(verifica_data_rc($tf_v_data_inclusao_ini) != 0 ) {
		$sql .= "and psah_data >= '".formata_data_rc($tf_v_data_inclusao_ini,1)." 00:00:00' ";
	}
}
if($tf_v_data_inclusao_fim) {
	if(verifica_data_rc($tf_v_data_inclusao_fim) != 0 ) {
		$sql .= "and psah_data <= '".formata_data_rc($tf_v_data_inclusao_fim,1)." 23:59:59' ";
	}
}
if(!empty($pin_id)) {
	$sql .= "and psah_pin_id = $pin_id ";
}
$sql .= "group by psah_pin_id,psah_acao having count(*) > 1 order by total desc";
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
	<td><nobr>Data início: 	<input name="tf_v_data_inclusao_ini" type="text" class="form" id="tf_v_data_inclusao_ini" value="<?php echo $tf_v_data_inclusao_ini ?>" size="10" maxlength="10">
	</td>
	<td><nobr>Data fim: <input name="tf_v_data_inclusao_fim" type="text" class="form" id="tf_v_data_inclusao_fim" value="<?php echo $tf_v_data_inclusao_fim ?>" size="10" maxlength="10">
	</td>
	<td><nobr>ID do PIN:
		<input name="pin_id" type="text" class="form" id="pin_id" value="<?php if(isset($pin_id)) echo $pin_id; ?>" size="9" maxlength="10"></nobr>
	</td>
</tr>
<tr>
	<td><nobr>Tipo: 
		<select id='sel_tipo' name='sel_tipo'> 
			<option value=''<?php echo (($sel_tipo=="")?" selected":""); ?>>Todos os tipos</option> 
			<?php
			foreach($GLOBALS['PINS_STORE_MSG_LOG'] as $key => $val) {
			if ($key <> 1)
				echo "<option value='".$key."'".(($sel_tipo== $key)?" selected":"").">".$val." (".$key.")</option>\n";
			}
			?>
		</select></nobr>
	</td>
	<td><nobr>Status: 
		<select id='sel_status' name='sel_status'> 
			<option value=''<?php echo (($sel_status=="")?" selected":""); ?>>Todos os status</option> 
			<?php
			foreach($GLOBALS['PINS_STORE_STATUS'] as $key => $val) {
			if ($key <> 1)
				echo "<option value='".$key."'".(($sel_status== $key)?" selected":"").">".$val." (".$key.")</option>\n";
			}
			?>
		</select></nobr>
	</td>
	<td><nobr>Id da GAMER: 
		<input name="ug_id" type="text" class="form" id="ug_id" value="<?php if(isset($ug_id)) echo $ug_id; ?>" size="9" maxlength="10"></nobr>
	</td>
</tr>
<tr>
	<td>&nbsp;</td>
    <td><input type="submit" name="btnSubmit" class="btn btn-sm btn-info" value="Atualiza"></td>
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
		echo "<td>dataMinima</td>\n";
		echo "<td>dataMaxima</td>\n";
		echo "<td>IdPIN</td>\n";
		echo "<td>Acao</td>\n";
		echo "</tr>\n";

		while($rs_row = pg_fetch_array($rs)) {
			$recibo_formatted = $rs_row['total'];	//wordwrap($rs_row['rprs_recibo'], 41, "\n", true);

			// se limite é válido -> Procesa solicitação
			echo "<tr onmouseover=\"bgColor='#CFDAD7'\" onmouseout=\"bgColor='#FFFFFF'\"".((strlen($recibo_formatted)>0)?" title=\"".$recibo_formatted." tentativas\"":"").">\n";
			echo "<td align='center'>&nbsp;".$rs_row['total']."&nbsp;</td>\n";
			echo "<td><nobr>&nbsp;".substr($rs_row['data_min'], 0, 19)."&nbsp;</nobr></td>\n";
			echo "<td><nobr>&nbsp;".substr($rs_row['data_max'], 0, 19)."&nbsp;</nobr></td>\n";
			echo "<td align='center'>&nbsp;<a href='/admin/pins_store/pins_store_historico.php?pin_codinterno=".$rs_row['psah_pin_id']."' target='_blank'>".$rs_row['psah_pin_id']."</a>&nbsp;</td>\n";
			echo "<td>&nbsp;".$GLOBALS['PINS_STORE_MSG_LOG'][$rs_row['psah_acao']]."&nbsp;</td>\n";
			echo "</tr>\n";
		
//			die("<br>Stop");
		}
		echo "</table>\n";

	}
?>