<?php
// Ponto Certo - Recarga de Celular
// lista_rc_pedidos.php - Para auxiliar a tarefa automatica - lista os pedidos pendentes
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/rs_ws/inc_utils.php"; 

?>
<style type="text/css">
<!--
body, p, td, b, a {font-family:verdana, sans serif, arial; font-size:10px}
-->
</style>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>

<?php

$n = 0;
$file = isset($GLOBALS['ARQUIVO_RC_MONITOR']) ? $GLOBALS['ARQUIVO_RC_MONITOR'] : "";

if(!isset($btnSubmit) || !$btnSubmit) {
	$tf_v_data_inclusao_ini = date("d/m/Y");
	$tf_v_data_inclusao_fim = date("d/m/Y");
}

echo "<h2>Lista LOG de modificação de Limite (".date("Y-m-d H:i:s").")</h2>\n";

//echo "sel_tipo: '$sel_tipo'<br>";
//echo "sel_status: '$sel_status'<br>";

if(!isset($sel_tipo)) $sel_tipo = "";
if(!(($sel_tipo=="R"))) $sel_tipo = "";

if(!isset($sel_status)) $sel_status = "";
if(!(($sel_status=="0") || ($sel_status=="1") || ($sel_status=="N"))) $sel_status = "";

$sql  = "select dugll.*, ug.ug_risco_classif, ug.ug_login \n"; 
$sql .= "from dist_usuarios_games_limite_log dugll \n";
$sql .= "inner join dist_usuarios_games ug on ug.ug_id = dugll.dugll_ug_id \n";
$sql .= "where 1=1 \n";

$sql_n  = "select dugll_ug_id, count(*) as n \n";
$sql_n .= "from dist_usuarios_games_limite_log dugll\n";
if(!empty($ug_risco_classif)) {
	$sql_n .= "inner join dist_usuarios_games ug on dugll.dugll_ug_id = ug.ug_id  \n";
}
$sql_n .= "where 1=1 \n";

if(!empty($ug_id)) {
	$sql .= "and dugll_ug_id = $ug_id ";
	$sql_n .= "and dugll_ug_id = $ug_id \n";
}
if($tf_v_data_inclusao_ini) {
	if(verifica_data_rc($tf_v_data_inclusao_ini) != 0 ) {
		$sql .= "and dugll_data_inclusao >= '".formata_data_rc($tf_v_data_inclusao_ini,1)." 00:00:00' ";
		$sql_n .= "and dugll_data_inclusao >= '".formata_data_rc($tf_v_data_inclusao_ini,1)." 00:00:00' \n";
	}
}
if($tf_v_data_inclusao_fim) {
	if(verifica_data_rc($tf_v_data_inclusao_fim) != 0 ) {
		$sql .= "and dugll_data_inclusao <= '".formata_data_rc($tf_v_data_inclusao_fim,1)." 23:59:59' ";
		$sql_n .= "and dugll_data_inclusao <= '".formata_data_rc($tf_v_data_inclusao_fim,1)." 23:59:59' \n";
	}
}
if(!empty($ug_risco_classif)) {
	$sql .= "and ug_risco_classif = $ug_risco_classif \n";
	$sql_n .= "and ug_risco_classif = $ug_risco_classif \n";
}

$sql   .= "order by dugll_data_inclusao desc";
$sql_n .= "group by dugll_ug_id \n";
$sql_n .= "order by n desc \n";

if(b_IsUsuarioReinaldo()) { 
//echo str_replace("\n", "<br>\n", $sql)."<br>";
//echo str_replace("\n", "<br>\n", $sql_n)."<br>";
}
$rs = SQLexecuteQuery($sql);
//echo "pg_num_rows(rs): ".pg_num_rows($rs)."<br>";

$rs_n = SQLexecuteQuery($sql_n);
	$a_n_regs = array();
	if(!$rs_n || pg_num_rows($rs_n) == 0) {
		echo "Nenhum produto encontrado (duplicado).<br>\n";
	} else {
		while($rs_n_row = pg_fetch_array($rs_n)) {
			$a_n_regs[$rs_n_row['dugll_ug_id']] = $rs_n_row['n'];
		}
	}

if(b_IsUsuarioReinaldo()) { 
//echo "<pre>".print_r($a_n_regs, true)."</pre><br>";
}

?>
<link href="/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="/js/global.js"></script>
	<script language="javascript">
    $(function(){
        var optDate = new Object();
            optDate.interval = 10000;

        setDateInterval('tf_v_data_inclusao_ini','tf_v_data_inclusao_fim',optDate);
    });
    </script>
<form method="post" action="" name="form1">
    <table class="table">
<tr>
    <td>Data início:</td>
    <td>
        <input name="tf_v_data_inclusao_ini" type="text" class="form" id="tf_v_data_inclusao_ini" value="<?php if(isset($tf_v_data_inclusao_ini)) echo $tf_v_data_inclusao_ini ?>" size="9" maxlength="10">
	</td>
    <td>Data fim:</td>
    <td><input name="tf_v_data_inclusao_fim" type="text" class="form" id="tf_v_data_inclusao_fim" value="<?php if(isset($tf_v_data_inclusao_fim)) echo $tf_v_data_inclusao_fim ?>" size="9" maxlength="10">
	</td>
</tr>
<tr>
    <td>Id da LAN:</td>
	<td>
        <input name="ug_id" type="text" class="form" id="ug_id" value="<?php if(isset($ug_id)) echo $ug_id; ?>" size="9" maxlength="10">
	</td>
    <td>Tipo da LAN:</td>
	<td>
        <select name="ug_risco_classif">
			<option value=""<?php if(isset($ug_risco_classif) && $ug_risco_classif=="") echo " selected"?>>Todos os Tipos</option>
			<option value="1"<?php if(isset($ug_risco_classif) && $ug_risco_classif=="1") echo " selected"?>>Lans Pós-pagas</option>
			<option value="2"<?php if(isset($ug_risco_classif) && $ug_risco_classif && $ug_risco_classif!="1") echo " selected"?>>Lans Pré-pagas</option>
		</select>
	</td>
</tr>
<tr>
    <td colspan="3">&nbsp;</td>
    <td><input type="submit" name="btnSubmit" class="btn btn-info" value="Atualiza"></td>
</tr>
</table>
	
</form>
<?php


	if(!$rs || pg_num_rows($rs) == 0) {
		echo "Nenhum produto encontrado.<br>\n";
	} else {

		$s_several = ((pg_num_rows($rs)>1)?"s":"");

		echo "<p>Encontrado$s_several ".pg_num_rows($rs)." registro$s_several</p>";

		echo "<table class='table table-bordered'>\n";
		echo "<tr align='center' style='font-weight:bold; background-color:#ffffcc'>\n";
		echo "<td colspan='2'>&nbsp;</td>\n";
		echo "<td colspan='4'>usuário</td>\n";
		echo "<td colspan='2'>valor</td>\n";
		echo "<td colspan='2'>Diff</td>\n";
		echo "</tr>\n";

		echo "<tr align='center' style='font-weight:bold; background-color:#ffffcc'>\n";
		echo "<td>id</td>\n";
		echo "<td>data_inclusao</td>\n";
		echo "<td>id</td>\n";
		echo "<td>n_regs</td>\n";
		echo "<td>login</td>\n";
		echo "<td>tipo</td>\n";
		echo "<td>anterior</td>\n";
		echo "<td>atual</td>\n";
		echo "<td>&nbsp;</td>\n";
		echo "<td>valor</td>\n";
		echo "</tr>\n";

		while($rs_row = pg_fetch_array($rs)) {
			
			echo "<tr onmouseover=\"bgColor='#CFDAD7'\" onmouseout=\"bgColor='#FFFFFF'\">\n";

/*
			echo "<td align='center'>".$rs_row['dugll_id']."</td>\n";
			echo "<td>".substr($rs_row['dugll_data_inclusao'], 0, 19)."</td>\n";
			echo "<td align='center'>".$rs_row['dugll_ug_id']."</td>\n";
			echo "<td align='right'>".number_format($rs_row['dugll_ug_perfil_limite_antes'], 2, '.', '.')."</td>\n";
			echo "<td align='right'>".number_format($rs_row['dugll_ug_perfil_limite'], 2, '.', '.')."</td>\n";
*/			

			echo "<td align='center'>".$rs_row['dugll_id']."</td>\n";
			echo "<td>".substr($rs_row['dugll_data_inclusao'], 0, 19)."</td>\n";

			echo "<td align='center'><a href='/pdv/usuarios/com_usuario_detalhe.php?usuario_id=".$rs_row['dugll_ug_id']."' target='_blank'>".$rs_row['dugll_ug_id']."</a></td>\n";
			echo "<td align='center'>".( (isset($a_n_regs[$rs_row['dugll_ug_id']]) && ($a_n_regs[$rs_row['dugll_ug_id']]>0)) ? $a_n_regs[$rs_row['dugll_ug_id']] : "0")."</td>\n";
			echo "<td align='center'>".$rs_row['ug_login']."</td>\n";
			
			echo "<td align='center'>".(($rs_row['ug_risco_classif']=="1")?"<img src='/images/balanco/POS.png' width='25' height='10' border='0' title='PÓS' alt='PÓS'>":"<img src='/images/balanco/PRE.png' width='25' height='10' border='0' title='PRE' alt='PRE'>")."</td>\n";
			echo "<td align='right'>".number_format($rs_row['dugll_ug_perfil_limite_antes'], 2, '.', '.')."</td>\n";
			echo "<td align='right'>".number_format($rs_row['dugll_ug_perfil_limite'], 2, '.', '.')."</td>\n";

			echo "<td align='right'>".(($rs_row['dugll_ug_perfil_limite_antes']<$rs_row['dugll_ug_perfil_limite'])?"<img src='/images/balanco/balanco_up.png' width='10' height='15' border='0' title='Up' alt='Up'>":"<img src='/images/balanco/balanco_down.png' width='10' height='15' border='0' title='Down' alt='Down'>")."</td>\n";

			echo "<td align='right' style='color:". (($rs_row['dugll_ug_perfil_limite_antes'] < $rs_row['dugll_ug_perfil_limite']) ? "blue" : "red")."'>". number_format(($rs_row['dugll_ug_perfil_limite']-$rs_row['dugll_ug_perfil_limite_antes']), 2, '.', '.'). "</td>\n";

			echo "</tr>\n";
		
//			die("<br>Stop");
		}
		echo "</table>\n";

	}



?>