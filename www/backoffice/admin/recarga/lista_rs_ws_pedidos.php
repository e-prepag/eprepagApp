<?php
// Ponto Certo - Recarga de Celular
// lista_rc_pedidos.php - Para auxiliar a tarefa automatica - lista os pedidos pendentes
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."class/rs_ws/classRedeSim.php";
require_once $raiz_do_projeto."includes/rs_ws/inc_utils.php";
require_once "/www/includes/bourls.php";

$n = 0;
$file = $GLOBALS['ARQUIVO_RC_MONITOR'];

if(!isset($btnSubmit) || !$btnSubmit) {
	$tf_v_data_inclusao_ini = date("d/m/Y");
	$tf_v_data_inclusao_fim = date("d/m/Y");
}

echo "<h2>Lista pedidos Rede SIM (".date("Y-m-d H:i:s").")</h2>";

//echo "sel_tipo: '$sel_tipo'<br>";
//echo "sel_status: '$sel_status'<br>";

if(!isset($sel_tipo)) $sel_tipo = "";
if(!(($sel_tipo=="R"))) $sel_tipo = "";

if(!isset($sel_status)) $sel_status = "";
if(!(($sel_status=="0") || ($sel_status=="1") || ($sel_status=="N"))) $sel_status = "";

$sql = "select * from tb_recarga_pedidos_rede_sim where 1=1 ";
if($sel_status!="") {
	$sql .= "and rprs_status = '$sel_status' ";
}
if($sel_tipo!="") {
	$sql .= "and rprs_tipo = '$sel_tipo' ";
}
if(!empty($ug_id)) {
	$sql .= "and rprs_ug_id = $ug_id ";
}
if($tf_v_data_inclusao_ini) {
	if(verifica_data_rc($tf_v_data_inclusao_ini) != 0 ) {
		$sql .= "and rprs_data_inclusao >= '".formata_data_rc($tf_v_data_inclusao_ini,1)." 00:00:00' ";
	}
}
if($tf_v_data_inclusao_fim) {
	if(verifica_data_rc($tf_v_data_inclusao_fim) != 0 ) {
		$sql .= "and rprs_data_inclusao <= '".formata_data_rc($tf_v_data_inclusao_fim,1)." 23:59:59' ";
	}
}
$sql .= "order by rprs_data_inclusao desc";
//echo "$sql<br>";
$rs = SQLexecuteQuery($sql);
//echo "pg_num_rows(rs): ".pg_num_rows($rs)."<br>";

?>
<link href="<?php echo $url; ?>:<?php echo $server_port; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>:<?php echo $server_port; ?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $url;?>:<?php echo $server_port; ?>/js/global.js"></script>
<!--<script type="text/javascript" src="js/jquery.ui.nestedSortable.js"></script>-->
<script>
    $(function(){

        var optDate = new Object();
            optDate.interval = 6;
            optDate.minDate = "01/01/2010";

        setDateInterval('tf_v_data_inclusao_ini','tf_v_data_inclusao_fim',optDate);
    });
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><?php echo $sistema->item->getDescricao() . " ".date("Y-m-d H:i:s") ; ?></li>
    </ol>
</div>
<form method="post" action="" name="form1">
<table class="table fontsize-pp txt-preto">
<tr>
	<td>
        Data início:
        <input name="tf_v_data_inclusao_ini" type="text" class="form" id="tf_v_data_inclusao_ini" value="<?php echo $tf_v_data_inclusao_ini ?>" size="9" maxlength="10">
	</td>
	<td>
        Data fim:
        <input name="tf_v_data_inclusao_fim" type="text" class="form" id="tf_v_data_inclusao_fim" value="<?php echo $tf_v_data_inclusao_fim ?>" size="9" maxlength="10">
	</td>
    <td>
        
    </td>
</tr>
<tr>
	<td>
        Tipo: 
		<select name="sel_tipo" id="sel_tipo">
			<option value="" <?php echo (($sel_tipo=="")?" selected":"") ?>>Todos</option>
			<option value="R" <?php echo (($sel_tipo=="R")?" selected":"") ?>>Tipo R</option>
		</select>
	</td>
	<td>
        Status: 
		<select name="sel_status" id="sel_status">
			<option value="" <?php echo (($sel_status=="")?" selected":"") ?>>Todos</option>
			<option value="0" <?php echo (($sel_status=="0")?" selected":"") ?>>status 0</option>
			<option value="1" <?php echo (($sel_status=="1")?" selected":"") ?>>status 1</option>
			<option value="N" <?php echo (($sel_status=="N")?" selected":"") ?>>status N</option>
		</select>
	</td>
	<td>
        Id da LAN: 
		<input name="ug_id" type="text" class="form" id="ug_id" value="<?php if(isset($ug_id)) echo $ug_id; ?>" size="9" maxlength="10">
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
		echo "Nenhum produto encontrado.<br>";
	} else {

		$s_several = ((pg_num_rows($rs)>1)?"s":"");

		echo "<p>Encontrado$s_several ".pg_num_rows($rs)." registro$s_several</p>
                <table class=\"table txt-preto\">
                <tr align='center' style='font-weight:bold; background-color:#ffffcc'>
                <td>id</td>
                <td>data_inclusao</td>
                <td>codigoOperadora</td>
                <td>nomeOperadora</td>
                <td>codigoRede</td>
                <td>versaoOperadora</td>
                <td>versaoFilial</td>
                <td>codigoProduto</td>
                <td>numeroCelular</td>
                <td>valor</td>
                <td>vg_id</td>
                <td>ug_id</td>
                <td>status</td>
                <td>statusRC</td>
                <td>data_recarga</td>
                <td>recibo?</td>
                <td>NIR</td>
                <td>email</td>
                </tr>";

		while($rs_row = pg_fetch_array($rs)) {
			$vg_id = $rs_row['rprs_vg_id'];
			$recibo_formatted = $rs_row['rprs_recibo'];	//wordwrap($rs_row['rprs_recibo'], 41, "", true);

			// se limite é válido -> Procesa solicitação

			echo "<tr onmouseover=\"bgColor='#CFDAD7'\" onmouseout=\"bgColor='#FFFFFF'\"".((strlen($recibo_formatted)>0)?" title=\"".$recibo_formatted."\"":"").">";
			echo "<td align='center'>".$rs_row['rprs_id']."</td>";
			echo "<td><nobr>".substr($rs_row['rprs_data_inclusao'], 0, 19)."</nobr></td>";
			echo "<td align='center'>".$rs_row['rprs_codigooperadora']."</td>";
			echo "<td align='center'>".$rs_row['rprs_label_operadora']."</td>";
			echo "<td align='center'>".$rs_row['rprs_codigorede']."</td>";
			echo "<td align='center'>".$rs_row['rprs_versaooperadora']."</td>";
			echo "<td align='center'>".$rs_row['rprs_versaofilial']."</td>";
			echo "<td align='center'>".$rs_row['rprs_codigoproduto']."</td>";
			echo "<td align='center'>".$rs_row['rprs_numerocelular']."</td>";
			echo "<td align='right'>".number_format($rs_row['rprs_valor'], 2, '.', '.')."</td>";
			echo "<td align='right'>".str_pad($vg_id, 8, "0", STR_PAD_LEFT)."</td>";
			echo "<td align='center'>".$rs_row['rprs_ug_id']."</td>";
			echo "<td align='center'>".$rs_row['rprs_status']."</td>";
			echo "<td align='center'>".$rs_row['rprs_statustransacao']."</td>";
			echo "<td align='center'><nobr>".substr($rs_row['rprs_data_recarga'], 0, 19)."</nobr></td>";
			echo "<td align='center'>".((strlen($rs_row['rprs_recibo'])>0)?"<font color='red'>SIM</font>":"")."</td>";
			echo "<td align='center'><nobr>".$rs_row['rprs_nir']."</nobr></td>";
			echo "<td align='center'><nobr>".$rs_row['rprs_email']."</nobr></td>";
			echo "</tr>";
		}
		echo "</table>";

	}



?>