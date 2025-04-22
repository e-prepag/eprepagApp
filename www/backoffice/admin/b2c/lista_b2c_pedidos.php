<?php
// B2C - Venda de Produtos Diversos
// lista_b2c_pedidos.php - Para auxiliar a tarefa automatica - lista os pedidos pendentes
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/pdv/b2c/config.inc.b2c.php";
require_once $raiz_do_projeto."includes/rs_ws/inc_utils.php";
require_once "/www/includes/bourls.php";
$n = 0;

if(empty($btnSubmit)) $btnSubmit = null;

if(!$btnSubmit) {
	$tf_v_data_inclusao_ini = date("d/m/Y");
	$tf_v_data_inclusao_fim = date("d/m/Y");
}

if(!isset($sel_status)) $sel_status = "";
if(!(($sel_status=="0") || ($sel_status=="1") || ($sel_status=="N"))) $sel_status = "";

$sql = "select * from tb_vendas_b2c where 1=1 ";
if($sel_status!="") {
	$sql .= "and vb2c_status = '$sel_status' ";
}
if(!empty($ug_id)) {
	$sql .= "and vb2c_ug_id_lan = $ug_id ";
}
else $ug_id = null;
if($tf_v_data_inclusao_ini) {
	if(verifica_data_rc($tf_v_data_inclusao_ini) != 0 ) {
		$sql .= "and \"vb2c_dataVenda\" >= '".formata_data_rc($tf_v_data_inclusao_ini,1)." 00:00:00' ";
	}
}
if($tf_v_data_inclusao_fim) {
	if(verifica_data_rc($tf_v_data_inclusao_fim) != 0 ) {
		$sql .= "and \"vb2c_dataVenda\" <= '".formata_data_rc($tf_v_data_inclusao_fim,1)." 23:59:59' ";
	}
}
$sql .= "order by \"vb2c_dataVenda\" desc";
//echo "$sql<br>";
$rs = SQLexecuteQuery($sql);
//echo "pg_num_rows(rs): ".pg_num_rows($rs)."<br>";

?>
<link href="<?php echo $url; ?>:<?php echo $server_port; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>:<?php echo $server_port; ?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $url;?>:<?php echo $server_port; ?>/js/global.js"></script>
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
        <li class="active"><?php echo $sistema->item->getDescricao(). " (".date("Y-m-d H:i:s"). ")"; ?></li>
    </ol>
</div>
<div class="col-md-12">
<form method="post" action="" name="form1">
<table class="table txt-preto fontsize-pp">
    <tr>
        <td>
            Data início:
            <input name="tf_v_data_inclusao_ini" type="text" class="form" id="tf_v_data_inclusao_ini" value="<?php echo $tf_v_data_inclusao_ini ?>" size="9" maxlength="10">
        </td>
        <td>
            Data fim:
            <input name="tf_v_data_inclusao_fim" type="text" class="form" id="tf_v_data_inclusao_fim" value="<?php echo $tf_v_data_inclusao_fim ?>" size="9" maxlength="10">
        </td>
    </tr>
    <tr>
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
            <input name="ug_id" type="text" class="form" id="ug_id" value="<?php echo $ug_id; ?>" size="9" maxlength="10">
        </td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td><input type="submit" class="btn btn-info btn-sm" name="btnSubmit" value="Atualiza"></td>
    </tr>
</table>
	
</form>
<?php


	if(!$rs || pg_num_rows($rs) == 0) {
		echo "Nenhum produto encontrado.<br>\n";
	} else {

		$s_several = ((pg_num_rows($rs)>1)?"s":"");

		echo "<p>Encontrado$s_several ".pg_num_rows($rs)." registro$s_several</p>";
		echo "<table class='table txt-preto fontsize-pp table-bordered'>\n";
		echo "<tr align='center' style='font-weight:bold; background-color:#ffffcc'>\n";
		echo "<td>idProduto</td>\n";
		echo "<td>data_inclusao</td>\n";
		echo "<td>nomeOperadora</td>\n";
		echo "<td>dataCadastroVenda</td>\n";
		echo "<td>dataCadastroCancelamento</td>\n";
		echo "<td>pin</td>\n";
		echo "<td>statusPin</td>\n";
		echo "<td>precoServico</td>\n";
		echo "<td>vg_id</td>\n";
		echo "<td>prazoVigencia</td>\n";
		echo "<td>dataCancelamento</td>\n";
		echo "<td>motivoCancelamento</td>\n";
		echo "<td>valorEstornado</td>\n";
		echo "<td>comissao_total</td>\n";
		echo "<td>comissao_para_repasse</td>\n";
		echo "<td>gamer</td>\n";
		echo "<td>status</td>\n";
		echo "<td>PDV</td>\n";
		echo "</tr>\n";

		while($rs_row = pg_fetch_array($rs)) {

			$vg_id = $rs_row['vb2c_vg_id'];
			$recibo_formatted = $rs_row['vb2c_pin'];	//wordwrap($rs_row['rprs_recibo'], 41, "\n", true);
			$b2cNomeServico = (isset($GLOBALS['B2C_PRODUCT'][$rs_row['vb2c_coServico']]['name'])) ? $GLOBALS['B2C_PRODUCT'][$rs_row['vb2c_coServico']]['name'] : "";
			// se limite é válido -> Procesa solicitação
			echo "<tr onmouseover=\"bgColor='#CFDAD7'\" onmouseout=\"bgColor='#FFFFFF'\"".((strlen($recibo_formatted)>0)?" title=\"".$recibo_formatted."\"":"").">\n";
			echo "<td align='center'>".$rs_row['vb2c_coServico']."</td>\n";
			echo "<td><nobr>".substr($rs_row['vb2c_dataVenda'], 0, 19)."</nobr></td>\n";
			echo "<td align='center'><nobr>".$b2cNomeServico."</nobr></td>\n";
			echo "<td align='center'><nobr>".substr($rs_row['vb2c_dataCadastroVenda'], 0, 19)."</nobr></td>\n";
			echo "<td align='center'><nobr>".substr($rs_row['vb2c_dataCadastroCancelamento'], 0, 19)."</nobr></td>\n";
			echo "<td align='center'>".$rs_row['vb2c_pin']."</td>\n";
			echo "<td align='center'>".$rs_row['vb2c_statusPin']."</td>\n";
			echo "<td align='right'>".number_format($rs_row['vb2c_precoServico'], 2, '.', '.')."</td>\n";
			echo "<td align='right'>".str_pad($vg_id, 8, "0", STR_PAD_LEFT)."</td>\n";
			echo "<td align='center'>".$rs_row['vb2c_prazoVigencia']."</td>\n";
			echo "<td align='center'><nobr>".substr($rs_row['vb2c_dataCancelamento'], 0, 19)."</nobr></td>\n";
			echo "<td align='center'>".$rs_row['vb2c_motivoCancelamento']."</td>\n";
			echo "<td align='center'><nobr>".$rs_row['vb2c_valorEstornado']."</nobr></td>\n";
			echo "<td align='center'>".$rs_row['vb2c_comissao_total']."</td>\n";
			echo "<td align='center'>".$rs_row['vb2c_comissao_para_repasse']."</td>\n";
			echo "<td align='center'>".$rs_row['vb2c_ug_id']."</td>\n";
			echo "<td align='center'>".$rs_row['vb2c_status']."</td>\n";
			echo "<td align='center'>".$rs_row['vb2c_ug_id_lan']."</td>\n";
			echo "</tr>\n";
		
//			die("<br>Stop");
		}
		echo "</table>\n";

	}
?>
</div>
