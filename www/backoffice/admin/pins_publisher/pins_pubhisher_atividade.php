<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once $raiz_do_projeto . "includes/gamer/main.php";
require_once $raiz_do_projeto . "class/classPinsPublishers.php";
require_once $raiz_do_projeto . "class/classIntegracaoPinPub.php";
require_once $raiz_do_projeto . "class/classIntegracaoPin.php";

$opr_codigo = isset($_REQUEST['pin_operacao'])     ? (int) $_REQUEST['pin_operacao']		: null;
$pin_codigo	= isset($_POST['pin_codigo'])		? $_POST['pin_codigo']				: null;
$varsel = "&pin_operacao=$opr_codigo&opr_codigo=$opr_codigo&pin_codigo=$pin_codigo";
$operacao_array = VetorOperadoras();
$time_start_stats = getmicrotime();
//paginacao
$p = $_GET['p'];
if(!$p) $p = 1;
$registros = 1000000;
$registros_total = 0;
//rotinas de inicializacao se click em botao gerar arquivo

//VericaÃ§Ãµes e Update
$msg = "";

//Recupera as vendas
if($msg == ""){
    if(strlen($tf_v_data_inclusao_fim) && strlen($tf_v_data_inclusao_ini)){

	$sql_filters = array();
	$sql  = "SELECT pih_pin_id,pih_ip_id,pih_id,pih_codretepp,pih.pin_status,to_char(pih_data,'DD/MM/YYYY HH24:MI:SS') as pih_data_aux,pin_codigo 
                 FROM pins_integracao_historico pih
                 INNER JOIN pins p ON pin_codinterno = pih_pin_id "; 
	if(strlen($tf_v_data_inclusao_ini)){
        $sql_filters[] = "pih_data >= to_timestamp('".addslashes($tf_v_data_inclusao_ini)." 00:00:00', 'DD/MM/YYYY HH24:MI:SS')";
        $varsel .= "&tf_v_data_inclusao_ini=$tf_v_data_inclusao_ini";
    }
	if(strlen($tf_v_data_inclusao_fim)){
        $sql_filters[] = "pih_data <= to_timestamp('".addslashes($tf_v_data_inclusao_fim)." 23:59:59', 'DD/MM/YYYY HH24:MI:SS')";
        $varsel .= "&tf_v_data_inclusao_fim=$tf_v_data_inclusao_fim";
    }
	if(!empty($opr_codigo))
				$sql_filters[] = "pih_id = ".addslashes($opr_codigo);
	if(!empty($pin_codigo))
				$sql_filters[] = "pih_pin_id = ".retorna_id_pin(addslashes($pin_codigo),addslashes($opr_codigo));
	if (count($sql_filters) > 0) {
		$sql_aux = implode(" and ", $sql_filters);
		$sql  .= "WHERE ".$sql_aux;
	}
//	echo $sql;
	$rs_total = SQLexecuteQuery($sql);
	if($rs_total) $registros_total = pg_num_rows($rs_total);
	$sql .= " ORDER BY pih_data DESC";	
	$sql .= " offset " . intval(($p - 1) * $registros) . " limit " . intval($registros);
//echo $sql ."<br>\n";
	$rs_pins = SQLexecuteQuery($sql);
	if(!$rs_pins || pg_num_rows($rs_pins) == 0) $msg = "Nenhum pin encontrado.\n";
    }//end if(!strlen($tf_v_data_inclusao_fim) || !strlen($tf_v_data_inclusao_ini))
    else $msg = "Obrigatório selecionar um intervalo de datas.".PHP_EOL;
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
function timedRefresh(timeoutPeriod) {
		//setTimeout("location.reload(true);",timeoutPeriod);
	}

function validador() {
		if (document.form1.pin_codigo.value != "" && document.form1.pin_operacao.value == "") {
			alert("Quando é informado o PIN é obrigatório a seleção da Operadora.");
			return false;
		}
		else return true;
	}

-->
        
$(function(){
    timedRefresh(10000);
})
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><?php echo $sistema->item->getDescricao(); ?></li>
    </ol>
</div>
<form name="form1" method="post" action="pins_pubhisher_atividade.php" onSubmit="return validador();">
    <table class="table txt-preto fontsize-pp">
	<tr valign="top" align="center">
      <td align="center">
			<input type="hidden" name="p" value="<?php echo $p; ?>">
			<table class="table txt-preto fontsize-pp">
    	        <tr bgcolor="F0F0F0">
    	          <td class="texto" align="center" colspan="3"><b>Lista de <?php echo ((($p - 1) * $registros)+1); ?> a <?php echo (($p*$registros)); ?> <?php echo " (Total: ".$registros_total." registro"?><?php if($registros_total>1) echo "s"; ?><?php echo ")"?></b></td>
    	        </tr>
    	        <tr bgcolor="F0F0F0">
    	          <td class="texto" align="center"><b>Operadora</b></td>
    	          <td class="texto" align="center"><b>Per&iacute;odo de cadastro</b></td>
    	          <td class="texto" align="center"><b>PIN</b></td>
    	        </tr>
    	        <tr bgcolor="F5F5FB">
    	          <td class="texto" align="center"><nobr>
				  <select name="pin_operacao" id="pin_operacao" class="combo_normal">
					<option value=''<?php if(!$pin_operacao) echo "selected"?>>Selecione a operadora</option>
			        <?php foreach ($operacao_array as $key => $value) { ?>
				    <option value=<?php echo "\"".$key.(($opr_codigo==$key)?"\" selected":"\""); ?>><?php echo $value; ?></option>
					<?php } ?>
					</select>
				  </td>
    	          <td class="texto" align="center"><nobr>&nbsp;
					  <input name="tf_v_data_inclusao_ini" type="text" class="form" id="tf_v_data_inclusao_ini" value="<?php echo $tf_v_data_inclusao_ini ?>" size="9" maxlength="10">
					  a 
					  <input name="tf_v_data_inclusao_fim" type="text" class="form" id="tf_v_data_inclusao_fim" value="<?php echo $tf_v_data_inclusao_fim ?>" size="9" maxlength="10">
				  </td>
				  <td class="texto" align="center">&nbsp;<input name="pin_codigo" id="pin_codigo" type="text" value="<?php echo $pin_codigo; ?>" size="30" maxlength="40"></td>
    	        </tr>
    	        <tr bgcolor="F5F5FB">
    	          <td class="texto" align="center">&nbsp</td>
    	          <td class="texto" align="center"><input type="submit" name="btPesquisar" value="Pesquisar" class="btn btn-sm btn-info">
				  <td class="texto" align="center">&nbsp;</td>
				  </td>
    	        </tr>
			</table>
			<table class="table txt-preto fontsize-pp">
			<tr bgcolor="F0F0F0">
 			  <td class="texto" align="center" width="10%"><b>PIN</b>&nbsp;</td>
			  <td class="texto" align="center" width="10%"><b>IP Utilizado</b>&nbsp;</td>
			  <td class="texto" align="center" width="15%"><b>Operadora</b>&nbsp;</td>
			  <td class="texto" align="center" width="20%"><b>Dia e Hora</b></td>
			  <td class="texto" align="center" width="30%"><b>Mensagem</b></td>
			  <td class="texto" align="center" width="15%"><b>Status</b>&nbsp;</td>
			  
			</tr>
    	<?php	

			$i=0;
			$irows=0;
			if($rs_pins) {
				while($rs_pins_row = pg_fetch_array($rs_pins)){ 
					$bgcolor = (($i++) % 2)?" bgcolor=\"F5F5FB\"":"";
					$irows++;
 			?>
    	        <tr<?php echo $bgcolor?> valign="top">
				  <td class="texto" align="center">&nbsp;<?php echo $rs_pins_row['pin_codigo']?></td>
    	          <td class="texto" align="center">&nbsp;<?php echo $rs_pins_row['pih_ip_id']?></td>
    	          <td class="texto" align="center">&nbsp;<?php echo $operacao_array[$rs_pins_row['pih_id']]?></td>
    	          <td class="texto" align="center">&nbsp;<?php echo $rs_pins_row['pih_data_aux']?>&nbsp;</td>
    	          <td class="texto" align="center">&nbsp;<?php echo $notify_list[$rs_pins_row['pih_codretepp']]?>&nbsp;</td>
				  <td class="texto" align="center">&nbsp;<?php echo $rs_pins_row['pin_status']?>&nbsp;</td>
    	        </tr>
    	<?php	
				}
				if($irows==0) {
			?>
					<tr>
					  <td class="texto" align="center" colspan="13">&nbsp;<font color='#FF0000'>N&atilde;o foram encontradas tentativas para os valores escolhidos</font></td>
					</tr>
			<?php
				}

			} else {
		?>
    	        <tr>
    	          <td class="texto" align="center" colspan="13">&nbsp;<font color='#FF0000'><?php echo $msg; ?> N&atilde;o foram encontradas tentativas para os valores escolhidos</font></td>
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
    <tr>
      	<td align="center" class="texto"><nobr>
      		<?php if($p > 1){ ?>
         	<input type="button" name="btAnterior" value=" < " OnClick="window.location='?p=<?php echo $p-1?><?php echo $varsel?>';" class="btn btn-sm btn-info">
         	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<?php } ?>
         	<input type="button" name="btOK" value="Voltar" OnClick="window.location='<?php echo $sNomePaginaAux;?>';" class="btn btn-sm btn-info">
      		<?php if($p < ($registros_total/$registros)){ ?>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
         	<input type="button" name="btProximo" value=" > " OnClick="window.location='?p=<?php echo $p+1?><?php echo $varsel?>';" class="btn btn-sm btn-info">
			<?php } ?></nobr>
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