<?php
    error_reporting(E_ALL ^ E_NOTICE);
    // Ponto Certo - Recarga de Celular
    // lista_rc_pedidos.php - Para auxiliar a tarefa automatica - lista os pedidos pendentes
    require_once '../../../includes/constantes.php';
    require_once $raiz_do_projeto."backoffice/includes/topo.php";
	include_once $raiz_do_projeto."class/rc/classRecargaCelular.php";
	include_once $raiz_do_projeto."includes/rc/inc_Simul.php";
	require_once $raiz_do_projeto."includes/rc/inc_rc_utils.php";
	require_once "/www/includes/bourls.php";
	$rc = new RecargaCelular();

	$n = 0;
	$file = $GLOBALS['ARQUIVO_RC_MONITOR'];

	if(!isset($btnSubmit)) {
		$tf_v_data_inclusao_ini = date("d/m/Y");
		$tf_v_data_inclusao_fim = date("d/m/Y");
	}

	$rc = new RecargaCelular();

	if(!isset($sel_tipo)) $sel_tipo = "";
	if(!(($sel_tipo=="R"))) $sel_tipo = "";

	if(!isset($sel_status)) $sel_status = "";
	if(!(($sel_status=="0") || ($sel_status=="1") || ($sel_status=="N"))) $sel_status = "";

	$sql = "select * from tb_recarga_pedidos where 1=1 ";
	if($sel_status!="") {
		$sql .= "and rp_status = '$sel_status' ";
	}
	if($sel_tipo!="") {
		$sql .= "and rp_tipo = '$sel_tipo' ";
	}
	if($tf_v_data_inclusao_ini) {
		if(verifica_data_rc($tf_v_data_inclusao_ini) != 0 ) {
			$sql .= "and rp_data_inclusao >= '".formata_data_rc($tf_v_data_inclusao_ini,1)." 00:00:00' ";
		}
	}
	if($tf_v_data_inclusao_fim) {
		if(verifica_data_rc($tf_v_data_inclusao_fim) != 0 ) {
			$sql .= "and rp_data_inclusao <= '".formata_data_rc($tf_v_data_inclusao_fim,1)." 23:59:59' ";
		}
	}
	$sql .= "order by rp_data_inclusao desc";
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
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() . " ".date("Y-m-d H:i:s"); ?></a></li>    
    </ol>
</div>

<form method="post" action="" name="form1">
<table class="table txt-preto fontsize-pp">
<tr>
	<td>
        Data início:
    </td>
    <td>
        <input name="tf_v_data_inclusao_ini" type="text" class="form" id="tf_v_data_inclusao_ini" value="<?php echo $tf_v_data_inclusao_ini ?>" size="9" maxlength="10">
	</td>
	<td>
        Data fim:
    </td>
    <td>
        <input name="tf_v_data_inclusao_fim" type="text" class="form" id="tf_v_data_inclusao_fim" value="<?php echo $tf_v_data_inclusao_fim ?>" size="9" maxlength="10">
	</td>
</tr>
<tr>
	<td>
        Tipo: 
    </td>
    <td>
        <select name="sel_tipo" id="sel_tipo">
			<option value="" <?php echo (($sel_tipo=="")?" selected":"") ?>>Todos</option>
			<option value="R" <?php echo (($sel_tipo=="R")?" selected":"") ?>>Tipo R</option>
		</select></nobr>
	</td>
	<td>
        Status: 
    </td>
    <td>
        <select name="sel_status" id="sel_status">
			<option value="" <?php echo (($sel_status=="")?" selected":"") ?>>Todos</option>
			<option value="0" <?php echo (($sel_status=="0")?" selected":"") ?>>status 0</option>
			<option value="1" <?php echo (($sel_status=="1")?" selected":"") ?>>status 1</option>
			<option value="N" <?php echo (($sel_status=="N")?" selected":"") ?>>status N</option>
		</select>
	</td>
</tr>
<tr>
    <td colspan="4"><input type="submit" class="btn btn-sm btn-info" name="btnSubmit" value="Atualiza"></td>
</tr>
</table>
	
</form>
<?php


	if(!$rs || pg_num_rows($rs) == 0) {
		echo "Nenhum produto encontrado.<br>\n";
	} else {

		$s_several = ((pg_num_rows($rs)>1)?"s":"");

		echo "<p>Encontrado$s_several ".pg_num_rows($rs)." registro$s_several</p>";
		echo "<table class=\"bg-branco table txt-preto fontsize-pp\">\n";
		echo "<tr align='center' style='font-weight:bold; background-color:#ffffcc'>\n";
		echo "<td>id</td>\n";
		echo "<td>data_inclusao</td>\n";
		echo "<td>codigoOperadora</td>\n";
		echo "<td>nomeOperadora</td>\n";
		echo "<td>codigoRede</td>\n";
		echo "<td>versaoOperadora</td>\n";
		echo "<td>versaoFilial</td>\n";
		echo "<td>ddd</td>\n";
		echo "<td>codigoProduto</td>\n";
		echo "<td>numeroCelular</td>\n";
		echo "<td>valor</td>\n";
		echo "<td>vg_id</td>\n";
		echo "<td>ug_id</td>\n";
		echo "<td>status</td>\n";
		echo "<td>statusRC</td>\n";
		echo "<td>data_recarga</td>\n";
		echo "<td>recibo?</td>\n";
		echo "</tr>\n";

		while($rs_row = pg_fetch_array($rs)) {
			$vg_id = $rs_row['rp_vg_id'];
			$recibo_formatted = wordwrap($rs_row['rp_recibo'], 41, "\n", true);

			// se limite é válido -> Procesa solicitação
			$rc->carregaPedido($vg_id);
//			echo "<pre>".print_r($rc, true)."</pre>";

			echo "<tr onmouseover=\"bgColor='#CFDAD7'\" onmouseout=\"bgColor='#FFFFFF'\"".((strlen($recibo_formatted)>0)?" title=\"".$recibo_formatted."\"":"").">\n";
			echo "<td align='center'>".$rs_row['rp_id']."</td>\n";
			echo "<td><nobr>".substr($rc->data_inclusao, 0, 19)."</nobr></td>\n";
			echo "<td align='center'>".$rc->codigoOperadora."</td>\n";
			echo "<td align='center'>".get_operadora_nome_by_codigo($rc->codigoOperadora)."</td>\n";
			echo "<td align='center'>".$rc->codigoRede."</td>\n";
			echo "<td align='center'>".$rc->versaoOperadora."</td>\n";
			echo "<td align='center'>".$rc->versaoFilial."</td>\n";
			echo "<td align='center'>".$rc->ddd."</td>\n";
			echo "<td align='center'>".$rc->codigoProduto."</td>\n";
			echo "<td align='center'>".$rc->numeroCelular."</td>\n";
			echo "<td align='right'>".number_format($rc->valor, 2, '.', '.')."</td>\n";
			echo "<td align='right'>".str_pad($rc->vg_id, 8, "0", STR_PAD_LEFT)."</td>\n";
			echo "<td align='center'>".$rc->ug_id."</td>\n";
			echo "<td align='center'>".$rc->rp_status."</td>\n";
			echo "<td align='center'>".$rc->rp_statustransacao."</td>\n";
			echo "<td align='center'><nobr>".substr($rs_row['rp_data_recarga'], 0, 19)."</nobr></td>\n";
			echo "<td align='center'>".((strlen($rs_row['rp_recibo'])>0)?"<font color='red'>SIM</font>":"")."</td>\n";
			echo "</tr>\n";
		
//			die("<br>Stop");
		}
		echo "</table>\n";

	}



?>