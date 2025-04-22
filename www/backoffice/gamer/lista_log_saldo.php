<?php
// Ponto Certo - Recarga de Celular
// lista_rc_pedidos.php - Para auxiliar a tarefa automatica - lista os pedidos pendentes
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once $raiz_do_projeto . "includes/rs_ws/inc_utils.php";
require_once "/www/includes/bourls.php";
?>
<link href="<?php echo $url; ?>:<?php echo $server_port; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>:<?php echo $server_port; ?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $url;?>:<?php echo $server_port; ?>/js/global.js"></script>
<script language="javascript">
    $(function(){
       var optDate = new Object();
            optDate.interval = 10000;

        setDateInterval('tf_v_data_inclusao_ini','tf_v_data_inclusao_fim',optDate);
        
    });
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() . " " . date("Y-m-d H:i:s"); ?></a></li>
    </ol>
</div>
<?php

$n = 0;
$file = (isset($GLOBALS['ARQUIVO_RC_MONITOR'])) ? $GLOBALS['ARQUIVO_RC_MONITOR'] : "";

if(!isset($btnSubmit) || !$btnSubmit) {
	$tf_v_data_inclusao_ini = date("d/m/Y");
	$tf_v_data_inclusao_fim = date("d/m/Y");
}

if(!isset($sel_tipo)) $sel_tipo = "";
if(!(($sel_tipo=="R"))) $sel_tipo = "";

if(!isset($sel_status)) $sel_status = "";
if(!(($sel_status=="0") || ($sel_status=="1") || ($sel_status=="N"))) $sel_status = "";

$sql  = "select ugsl.*, ug.ug_email " . PHP_EOL;
$sql .= "from usuarios_games_saldo_log ugsl " . PHP_EOL;
$sql .= "inner join usuarios_games ug on ug.ug_id = ugsl.ugsl_ug_id " . PHP_EOL;
$sql .= "where 1=1 ";

$sql_n  = "select ugsl_ug_id, count(*) as n " . PHP_EOL;
$sql_n .= "from usuarios_games_saldo_log " . PHP_EOL;
$sql_n .= "where 1=1 " . PHP_EOL;

if(!empty($ug_id)) {
	$sql .= "and ugsl_ug_id = $ug_id ";
	$sql_n .= "and ugsl_ug_id = $ug_id " . PHP_EOL;
}
if($tf_v_data_inclusao_ini) {
	if(verifica_data_rc($tf_v_data_inclusao_ini) != 0 ) {
		$sql .= "and ugsl_data_inclusao >= '".formata_data_rc($tf_v_data_inclusao_ini,1)." 00:00:00' ";
		$sql_n .= "and ugsl_data_inclusao >= '".formata_data_rc($tf_v_data_inclusao_ini,1)." 00:00:00' " . PHP_EOL;
	}
}
if($tf_v_data_inclusao_fim) {
	if(verifica_data_rc($tf_v_data_inclusao_fim) != 0 ) {
		$sql .= "and ugsl_data_inclusao <= '".formata_data_rc($tf_v_data_inclusao_fim,1)." 23:59:59' ";
		$sql_n .= "and ugsl_data_inclusao <= '".formata_data_rc($tf_v_data_inclusao_fim,1)." 23:59:59' " . PHP_EOL;
	}
}
$sql .= "order by ugsl_data_inclusao desc";
$sql_n .= "group by ugsl_ug_id " . PHP_EOL;
$sql_n .= "order by n desc " . PHP_EOL;


if(b_IsUsuarioReinaldo()) { 
//echo str_replace("\n", "<br>\n", $sql)."<br>";
//echo str_replace("\n", "<br>\n", $sql_n)."<br>";
}


$rs = SQLexecuteQuery($sql);
//echo "pg_num_rows(rs): ".pg_num_rows($rs)."<br>";

$rs_n = SQLexecuteQuery($sql_n);
	$a_n_regs = array();
	if(!$rs_n || pg_num_rows($rs_n) == 0) {
		echo "Nenhum produto encontrado (duplicado).<br>" . PHP_EOL;
	} else {
		while($rs_n_row = pg_fetch_array($rs_n)) {
			$a_n_regs[$rs_n_row['ugsl_ug_id']] = $rs_n_row['n'];
		}
	}

if(b_IsUsuarioReinaldo()) { 
//echo "<pre>".print_r($a_n_regs, true)."</pre><br>";
}

?>
<form method="post" action="" name="form1">
<table class="table txt-preto ">
<tr>
    <td>Data início:</td>
    <td> 	
        <input name="tf_v_data_inclusao_ini" type="text" id="tf_v_data_inclusao_ini" value="<?php echo $tf_v_data_inclusao_ini ?>" size="9" maxlength="10">
	</td>
    <td>Data fim:</td>
    <td>
        <input name="tf_v_data_inclusao_fim" type="text" id="tf_v_data_inclusao_fim" value="<?php echo $tf_v_data_inclusao_fim ?>" size="9" maxlength="10">
	</td>
    <td>Id do Gamer:</td>
    <td>
		<input name="ug_id" type="text" class="form" id="ug_id" value="<?php if(isset($ug_id)) echo $ug_id; ?>" size="9" maxlength="10">
	</td>
</tr>
<tr>
    <td colspan="6"><input type="submit" name="btnSubmit" value="Atualiza" class="btn btn-info btn-sm"></td>
</tr>
</table>
	
</form>
<?php


	if(!$rs || pg_num_rows($rs) == 0) {
		echo "Nenhum produto encontrado.<br>" . PHP_EOL;
	} else {

		$s_several = ((pg_num_rows($rs)>1)?"s":"");

		echo "<p>Encontrado$s_several ".pg_num_rows($rs)." registro$s_several</p>";
		echo "<table class='table table-bordered fontsize-pp'>" . PHP_EOL;
		
		echo "<tr align='center' style='font-weight:bold; background-color:#ffffcc'>" . PHP_EOL;
		echo "<td colspan='2'>&nbsp;</td>" . PHP_EOL;
		echo "<td colspan='3'>usuário</td>" . PHP_EOL;
		echo "<td colspan='2'>valor</td>" . PHP_EOL;
		echo "<td colspan='2'>Diff</td>" . PHP_EOL;
		echo "</tr>" . PHP_EOL;


		echo "<tr align='center' style='font-weight:bold; background-color:#ffffcc'>" . PHP_EOL;
		echo "<td>id</td>" . PHP_EOL;
		echo "<td>data_inclusao</td>" . PHP_EOL;

		echo "<td>usuário</td>" . PHP_EOL;
		echo "<td>n_regs</td>" . PHP_EOL;
		echo "<td>e-mail</td>" . PHP_EOL;

		echo "<td>anterior</td>" . PHP_EOL;
		echo "<td>atual</td>" . PHP_EOL;
		
		echo "<td>&nbsp;</td>" . PHP_EOL;
		echo "<td>valor</td>" . PHP_EOL;
		echo "</tr>" . PHP_EOL;

		while($rs_row = pg_fetch_array($rs)) {
			
			echo "<tr onmouseover=\"bgColor='#CFDAD7'\" onmouseout=\"bgColor='#FFFFFF'\">" . PHP_EOL;

			echo "<td align='center'>".$rs_row['ugsl_id']."</td>" . PHP_EOL;
			echo "<td><nobr>".substr($rs_row['ugsl_data_inclusao'], 0, 19)."</nobr></td>" . PHP_EOL;

			echo "<td align='center'><a href='/gamer/usuarios/com_usuario_detalhe.php?usuario_id=".$rs_row['ugsl_ug_id']."' target='_blank'>".$rs_row['ugsl_ug_id']."</a></td>" . PHP_EOL;
			echo "<td align='center'>".( (isset($a_n_regs[$rs_row['ugsl_ug_id']]) && ($a_n_regs[$rs_row['ugsl_ug_id']]>0)) ? $a_n_regs[$rs_row['ugsl_ug_id']] : "0")."</td>" . PHP_EOL;
			echo "<td align='center'>".$rs_row['ug_email']."</td>" . PHP_EOL;

			echo "<td align='right'>".number_format($rs_row['ugsl_ug_perfil_saldo_antes'], 2, '.', '.')."</td>" . PHP_EOL;
			echo "<td align='right'>".number_format($rs_row['ugsl_ug_perfil_saldo'], 2, '.', '.')."</td>" . PHP_EOL;

			echo "<td align='right'>".(($rs_row['ugsl_ug_perfil_saldo_antes']<$rs_row['ugsl_ug_perfil_saldo'])?"<img src='/images/gamer/balanco_up.png' width='10' height='15' border='0' title='Up' alt='Up'>":"<img src='/images/gamer/balanco_down.png' width='10' height='15' border='0' title='Down' alt='Down'>")."</td>" . PHP_EOL;

			echo "<td align='right' style='color:". (($rs_row['ugsl_ug_perfil_saldo_antes'] < $rs_row['ugsl_ug_perfil_saldo']) ? "blue" : "red")."'>". number_format(($rs_row['ugsl_ug_perfil_saldo']-$rs_row['ugsl_ug_perfil_saldo_antes']), 2, '.', '.'). "</td>" . PHP_EOL;

			echo "</tr>" . PHP_EOL;
		
//			die("<br>Stop");
		}
		echo "</table>" . PHP_EOL;

	}



?>