<?php
// Ponto Certo - Recarga de Celular
// lista_rc_consultas.php - Para visualizar as consultas
    require_once '../../../includes/constantes.php';
    require_once $raiz_do_projeto."backoffice/includes/topo.php";
	include_once $raiz_do_projeto."class/rc/classRecargaCelular.php";
	include_once $raiz_do_projeto."includes/rc/inc_Simul.php";
	require_once $raiz_do_projeto."includes/rc/inc_rc_utils.php";
	require_once "/www/includes/bourls.php";

	set_time_limit ( 3000 ) ;
	$time_start = getmicrotime();

	$n = 0;
	$file = $GLOBALS['ARQUIVO_RC_MONITOR'];

	if(!isset($btnSubmit) || !$btnSubmit) {
		$tf_v_data_inclusao_ini = date("d/m/Y");
		$tf_v_data_inclusao_fim = date("d/m/Y");
	}

	if(!isset($sel_tipo)) $sel_tipo = "";
	if(!isset($rc_codigooperadora)) $rc_codigooperadora = "";
	
	$sql = "select (CASE WHEN (LENGTH(rc_retorno)>1) THEN (substring(rc_retorno from 1 for 300)||'...') END) as rc_retorno2,* from tb_recarga_consultas where 1=1 ";
	if($sel_tipo!="") {
		$sql .= "and UPPER(rc_tipo) = '$sel_tipo' ";
	}
	if($rc_codigooperadora!="") {
		$sql .= "and rc_codigooperadora = '$rc_codigooperadora' ";
	}
	
	if($tf_v_data_inclusao_ini) {
		if(verifica_data_rc($tf_v_data_inclusao_ini) != 0 ) {
			$sql .= "and rc_data_inclusao >= '".formata_data_rc($tf_v_data_inclusao_ini,1)." 00:00:00' ";
		}
	}
	if($tf_v_data_inclusao_fim) {
		if(verifica_data_rc($tf_v_data_inclusao_fim) != 0 ) {
			$sql .= "and rc_data_inclusao <= '".formata_data_rc($tf_v_data_inclusao_fim,1)." 23:59:59' ";
		}
	}
	$sql .= "order by rc_data_inclusao desc";
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
<table class="table txt-preto fontsize-pp">
<tr>
	<td>
        Data início:
        <input name="tf_v_data_inclusao_ini" type="text" class="form" id="tf_v_data_inclusao_ini" value="<?php echo $tf_v_data_inclusao_ini ?>" size="9" maxlength="10"> <a href="#">
	</td>
	<td>
        Data fim:
        <input name="tf_v_data_inclusao_fim" type="text" class="form" id="tf_v_data_inclusao_fim" value="<?php echo $tf_v_data_inclusao_fim ?>" size="9" maxlength="10"> <a href="#">
	</td>
</tr>
<tr>
	<td>
        Tipo: 
		<select name="sel_tipo" id="sel_tipo">
			<option value="" <?php echo (($sel_tipo=="")?" selected":"") ?>>Todos</option>
			<option value="A" <?php echo (($sel_tipo=="A")?" selected":"") ?>>AtualizaOperadorasValores (A)</option>
			<option value="O" <?php echo (($sel_tipo=="O")?" selected":"") ?>>ConsultaOperadoras (O)</option>
			<option value="V" <?php echo (($sel_tipo=="V")?" selected":"") ?>>ConsultaValores (V)</option>
			<option value="R" <?php echo (($sel_tipo=="R")?" selected":"") ?>>SolicitacaoRecarga (R)</option>
		</select>
        
	</td>
	<td>
        Operadora:
        <?php echo get_select_operadora_nome($rc_codigooperadora); ?>
	</td>
</tr>
<tr>
	<td>&nbsp;</td>
    <td><input type="submit" name="btnSubmit" class="btn btn-info btn-success" value="Atualiza"></td>
</tr>
</table>
	
</form>
<?php


	if(!$rs || pg_num_rows($rs) == 0) {
		echo "Nenhum produto encontrado.<br>\n";
	} else {

		$s_several = ((pg_num_rows($rs)>1)?"s":"");

		echo "<p>Encontrado$s_several ".pg_num_rows($rs)." registro$s_several</p>";
		echo "<table class=\"table txt-preto bg-branco fontsize-pp\">\n";
		echo "<tr align='center' style='font-weight:bold; background-color:#ffffcc'>\n";
		echo "<td>id</td>\n";
		echo "<td>data_inclusao</td>\n";
		echo "<td>tipo</td>\n";
		echo "<td>codigoOperadora</td>\n";
		echo "<td>nomeOperadora</td>\n";
		echo "<td>codigoRede</td>\n";
		echo "<td>statusTransacao</td>\n";
		echo "<td>Parametros</td>\n";
//		echo "<td>Retorno</td>\n";
		echo "<td>URL Logo</td>\n";
		echo "<td>DDD</td>\n";
		echo "</tr>\n";
		while($rs_row = pg_fetch_array($rs)) {
			$vg_id = isset($rs_row['rp_vg_id']) ? $rs_row['rp_vg_id'] : null;
//			echo "<pre>".print_r($rs_row['rc_retorno'],true)."</pre>";
//			echo "<pre>".print_r(json_decode($rs_row['rc_retorno'],true),true)."</pre>";
			$recibo_formatted = wordwrap(str_replace('"',"'",$rs_row['rc_retorno2']), 41, "\n", true);
//			$recibo_formatted = wordwrap(print_r(json_decode($rs_row['rc_retorno'],true),true), 41, "\n", true);
			// se limite é válido -> Procesa solicitação

			echo "<tr onmouseover=\"bgColor='#CFDAD7'\" onmouseout=\"bgColor='#FFFFFF'\"".((strlen($recibo_formatted)>0)?" title=\"".$recibo_formatted."\"":"").">\n";
			echo "<td align='center'>".$rs_row['rc_id']."</td>\n";
			echo "<td><nobr>".substr($rs_row['rc_data_inclusao'], 0, 19)."</nobr></td>\n";
			echo "<td align='center'>".strtoupper($rs_row['rc_tipo'])."</td>\n";
			echo "<td align='center'>".$rs_row['rc_codigooperadora']."</td>\n";
			echo "<td align='center'>".get_operadora_nome_by_codigo($rs_row['rc_codigooperadora'])."</td>\n";
			echo "<td align='center'>".$rs_row['rc_codigorede']."</td>\n";
			echo "<td align='center'>".$rs_row['rc_status']."</td>\n";
			echo "<td align='center'>".$rs_row['rc_parametros']."</td>\n";
//			echo "<td align='center'>".$rs_row['rc_retorno2']."</td>\n";
//			echo "<td align='center'>".$rs_row['rc_retorno']."</td>\n";
			echo "<td align='center'>".$rs_row['rc_urllogo']."</td>\n";
			echo "<td align='center'>".$rs_row['rc_ddd']."</td>\n";
			echo "</tr>\n";
		
//			die("<br>Stop");
		}
		echo "</table>\n";

	}

echo "<hr><span style='color:darkgreen'>Elapsed time: ".number_format(getmicrotime() - $time_start, 2, '.', '.')."s</span><br>";
?>