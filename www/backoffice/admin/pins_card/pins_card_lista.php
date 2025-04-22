<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once $raiz_do_projeto . "includes/gamer/main.php";
require_once $raiz_do_projeto . "class/classPinsCard.php";

set_time_limit(6000);

$publisher_array	= VetorOperadorasCard();
$operacao_array		= VetorDistribuidorasCard();
$tot_lote 		= isset($_POST['pin_tot_lote'])		? $_POST['pin_tot_lote']	: 'S';
$distributor_codigo 	= isset($_POST['pin_operacao'])		? $_POST['pin_operacao']	: null;
$lote			= isset($_POST['pin_lote'])		? $_POST['pin_lote']		: null;
$valor			= isset($_POST['pin_valor'])		? $_POST['pin_valor']		: null;
$op			= isset($_POST['op'])			? $_POST['op']			: null;
$BtnGerarArq		= isset($_POST['hidgerar'])		? $_POST['hidgerar']		: null;
$tf_v_tipo		= isset($_POST['tf_v_tipo'])		? $_POST['tf_v_tipo']		: null;
$opr_codigo		= isset($_POST['opr_codigo'])		? $_POST['opr_codigo']	: null;
$ids_temp		= isset($_POST['chkPIN'])           	? $_POST['chkPIN']		: null;
$time_start_stats	= getmicrotime();
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
	function ShowPopupWindowXY(fileName,x,y) {
		myFloater = window.open('','myWindow','scrollbars=1,status=0,resizable=1,width=' + x + ',height=' + y)
		myFloater.location.href = fileName;
	}

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
	function reload() {
		document.form1.action = "pins_card_lista.php";
		document.form1.submit();
	}
	function reload_tot() {
		if (document.form1.pin_tot_lote.value=="S")
			document.form1.action = "pins_card_lista.php";
		else document.form1.action = "pins_card_lista_pin.php";
		document.form1.submit();
	}
	function reload_gerar_arquivo() {
		if (algumCheckBoxSel())
		{
			if ((document.getElementById('pin_operacao').value!="") && (document.getElementById('opr_codigo').value!=""))
			{
				if (confirm("Realmente deseja gerar o Arquivo?"))
				{
					document.form1.action = "pins_card_lista.php";
					document.getElementById('hidgerar').value = "gerar";
					document.form1.submit();
				}
			}
			else {
				alert("Deve ser selecionado uma Distribuidora e Publisher\n para gerar o arquivo!");
				return false;
			}
		}
		else  {
			alert("Deve ser selecionado ao menos um LOTE\n para gerar o arquivo!");
			return false;
		}
	}
	function reload_acao() {
		if (algumCheckBoxSel())
		{
			document.form1.action = "pins_card_lista.php";
			if (document.getElementById('op').value == 'blo')
			{
				if (confirm("ATENCAO: Somente serao bloqueados os PIN ATIVOS do LOTE.\n Realmente deseja bloquear?"))
				{
					document.form1.submit();
				}
				else document.getElementById('op').value='';
			}
			else if (document.getElementById('op').value == 'des')
				{
					if (confirm("ATENCAO: Somente serao DESbloqueados os PIN com STATUS de bloqueado dentro do LOTE.\n Realmente deseja DESbloquear?"))
					{
						document.form1.submit();
					}
					else document.getElementById('op').value='';
				}
				else if (confirm("Realmente deseja executar esta opcao?"))
					{
						document.form1.submit();
					}
					else {
						document.getElementById('op').value='';
						return false;
					}
		}
		else  {
			alert("Deve ser selecionado ao menos um LOTE\n para executar esta tarefa!");
			document.getElementById('op').value='';
			return false;
		}
	}
	function verifica()
        {
            if ((event.keyCode<47)||(event.keyCode>58)){
                  alert("Somente numeros sao permitidos");
                  event.returnValue = false;
            }
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
<?php
//paginacao
$p = $_GET['p'];
if(!$p) $p = 1;
$registros = 50;
$registros_total = 0;
	
//Vericações e Update
$msg = "";
$msg_pin = "";

if(!empty($op)) {
	if($op=="ati" || $op=="can" || $op=="blo" || $op=="des") {
		// Passa para o novo estado
		$status_new = "";
		$condAdicional = "";
		$setAdicinal = "";
		switch($op) {
			case "ati":
				$status_new = intval($PINS_STORE_STATUS_VALUES['A']);
				$setAdicinal =", pin_validade = (NOW() + interval '6 month')";
				break;
			case "can":
				$status_new = intval($PINS_STORE_STATUS_VALUES['C']);
				break;
			case "blo":
				$status_new = intval($PINS_STORE_STATUS_VALUES['B']);
				$condAdicional = "and pin_status='".intval($PINS_STORE_STATUS_VALUES['A'])."' ";
				break;
			case "des":
				$status_new = intval($PINS_STORE_STATUS_VALUES['A']);
				$condAdicional = "and pin_status='".intval($PINS_STORE_STATUS_VALUES['B'])."' ";
				break;
		}
		//Inicia transacao
		if($msg == ""){
			$sql = "BEGIN TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "<font color='#FF0000'><b>Erro ao iniciar transa&cceil;&atilde;o.\n</b></font><br>";
		}
		// testa status do pin antes de mudar
		for ($i=0; $i<count($ids_temp);$i++) {
			if($msg == ""){
				list($codlote,$codopr,$opr_codigo) = explode("|",$ids_temp[$i]);
				$sql  = "update pins_card set pin_status='".intval($status_new)."'".$setAdicinal." where pin_lote_codigo=".intval($codlote)." and distributor_codigo=".intval($codopr)." and opr_codigo=".intval($opr_codigo)." and pin_status!='".intval($status_new)."' and pin_status!='".intval($PINS_STORE_STATUS_VALUES['D'])."' and pin_status!='".intval($PINS_STORE_STATUS_VALUES['U'])."' and pin_status!='".intval($PINS_STORE_STATUS_VALUES['C'])."' ".$condAdicional;
				//echo $sql."<br>";die();
				$rs_pins_save = SQLexecuteQuery($sql);
				if($rs_pins_save ) {
					$msg_pin .= "<span class='txt-verde'><b>Lote ($codlote) da Distribuidora ($codopr) atualizado com sucesso ('$op')</b></span><br>";
				} else {
					 $msg = "<font color='#FF0000'><b>Erro ao atualizar o Lote ($codlote) da Distribuidora ($codopr).\n</b></font><br>";
				}
			}
		}
		if(strlen($ids)>0) {
			$msg_pin .= "<font color='#FF0000'><b>N&atilde;o foi selecionado nenhum LOTE v&aacute;lido ('$op', '<<$ids>>')</b></font><br>";
		}
		//Finaliza transacao
		if($msg == ""){
			$sql = "COMMIT TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "<font color='#FF0000'><b>Erro ao comitar transa&ccedil;&atilde;o.\n<br></b></font><br>";
		} else {
			$sql = "ROLLBACK TRANSACTION ";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "<font color='#FF0000'><b>Erro ao dar rollback na transa&ccedil;&atilde;o.\n<br></b></font><br>";
		}
	}
}
echo $msg;

//rotinas de inicializacao se click em botao gerar arquivo
if(!empty($BtnGerarArq) && $tf_v_tipo==3) {
	include ("pins_card_envio_email.php");
}

//Recupera as vendas
if($msg == "" && isset($btPesquisar)){
	$sql  = "select opr_codigo, distributor_codigo, pin_lote_codigo, to_char(pin_dataentrada,'DD/MM/YYYY') as data, pin_valor from pins_card pc where pin_status!='".intval($PINS_STORE_STATUS_VALUES['D'])."' ";

	if($tf_v_tipo) {
		if(!(array_key_exists($tf_v_tipo,$PINS_STORE_STATUS)) ) {
			$tf_v_tipo = "";
		}

		if($tf_v_tipo) {
			$sql .= " and pin_status='".intval($tf_v_tipo)."' ";	
		}
	}
	if(strlen($opr_codigo))
				$sql .= " and opr_codigo=".intval($opr_codigo);
	if(strlen($distributor_codigo))
				$sql .= " and distributor_codigo=".intval($distributor_codigo);
	if(strlen($lote))
				$sql .= " and pin_lote_codigo=".intval($lote);
	if(strlen($valor))
				$sql .= " and pin_valor=".intval($valor);
	if(strlen($tf_v_data_inclusao_ini))
				$sql .= " and pin_dataentrada >= to_timestamp('".addslashes($tf_v_data_inclusao_ini)." 00:00:00', 'DD/MM/YYYY HH24:MI:SS')";
	if(strlen($tf_v_data_inclusao_fim))
				$sql .= " and pin_dataentrada <= to_timestamp('".addslashes($tf_v_data_inclusao_fim)." 23:59:59', 'DD/MM/YYYY HH24:MI:SS')";
	$sql .= " group by opr_codigo, distributor_codigo, pin_lote_codigo, data, pin_valor ";
	$rs_total = SQLexecuteQuery($sql);
	if($rs_total) $registros_total = pg_num_rows($rs_total);
	$sql .= " order by distributor_codigo, pin_valor ";	
	$sql .= " offset " . intval(($p - 1) * $registros) . " limit " . intval($registros);
        //echo $sql ."<br>\n";//die();
	$rs_pins = SQLexecuteQuery($sql);
	if(!$rs_pins || pg_num_rows($rs_pins) == 0) $msg = "Nenhum pin encontrado.\n";
}
?>
<div class="col-md-12">
     <ol class="breadcrumb top10">
         <li><a href="#" class="muda-aba" ordem="1">BackOffice - Money</a></li>
         <li class="active">Listar PINs Cart�es por LOTE</li>
     </ol>
 </div>
 <div class="col-md-12 txt-preto fontsize-pp">
<?php
include "pins_card_menu.php";
?>
<form name="form1" method="post" action="pins_card_lista.php">
    <table class="txt-preto fontsize-pp table">
	<?php
    if($msg_pin)
        echo "<tr><td>".$msg_pin."</td></tr>";
	?>
   <tr valign="top" align="center">
      <td>
			<input type="hidden" name="p" value="<?php echo $p; ?>">
			<table class="txt-preto fontsize-pp table">
    	        <tr bgcolor="F0F0F0">
    	          <td class="texto" align="center" colspan="3"><b>Lista de <?php echo ((($p - 1) * $registros)+1); ?> a <?php echo (($p*$registros)); ?> <?php echo " (Total: ".$registros_total." registro"?><?php if($registros_total>1) echo "s"; ?><?php echo ")"?></b></td>
    	        </tr>
    	        <tr bgcolor="F0F0F0">
    	          <td class="texto" align="center"><b>Status</b></td>
    	          <td class="texto" align="center"><b>Per&iacute;odo de cadastro</b></td>
    	          <td class="texto" align="center">&nbsp;</td>
    	        </tr>
    	        <tr bgcolor="F5F5FB">
    	          <td class="texto" align="center"><nobr>&nbsp;
					<select id='tf_v_tipo' name='tf_v_tipo' onChange='document.form1.btPesquisar.click();'> 
						<option value=''<?php echo (($tf_v_tipo=="")?" selected":""); ?>>Todos os status</option> 
					<?php
						foreach($PINS_STORE_STATUS as $key => $val) {
                            if ($key <> 1)
								echo "<option value='".$key."'".(($tf_v_tipo== $key)?" selected":"").">".$val." (".$key.")</option>\n";
						}
					?>
					  </select>
				  </td>
    	          <td class="texto" align="center"><nobr>&nbsp;
					  <input name="tf_v_data_inclusao_ini" type="text" class="form" id="tf_v_data_inclusao_ini" value="<?php echo $tf_v_data_inclusao_ini ?>" size="9" maxlength="10">
					  a 
					  <input name="tf_v_data_inclusao_fim" type="text" class="form" id="tf_v_data_inclusao_fim" value="<?php echo $tf_v_data_inclusao_fim ?>" size="9" maxlength="10">
				  </td>
				  <td class="texto" align="center">&nbsp;Valor
                        <input name="pin_valor" id="pin_valor" type="text" value="<?php echo $valor; ?>" size="10" maxlength="10" onKeypress="return verifica();"></td>
    	        </tr>
    	        <tr bgcolor="F5F5FB">
    	          <td class="texto" align="center">
                            <select name="opr_codigo" id="opr_codigo" class="combo_normal" onChange='document.form1.btPesquisar.click();'>
                                    <option value=''<?php if(!$opr_codigo) echo "selected"?>>Selecione o Publisher</option>
                                    <?php foreach ($publisher_array as $key => $value) { ?>
				    <option value=<?php echo "\"".$key.(($opr_codigo==$key)?"\" selected":"\""); ?>><?php echo $value; ?></option>
                                    <?php } ?>
                            </select>
                            <select name="pin_operacao" id="pin_operacao" class="combo_normal" onChange='document.form1.btPesquisar.click();'>
                                        <option value='' <?php echo (($distributor_codigo=="")?" selected":"") ?>>Todas as Distribuidoras</option>
					<?php foreach ($operacao_array as $key => $value) { ?>
                                        <option value="<?php echo $key ?>" <?php if($key == $distributor_codigo) echo "selected"; ?>><?php echo $value; ?></option>
                                        <?php } ?>
                            </select>
		  </td>
    	          <td class="texto" align="center">&nbsp;Lote
                    <input name="pin_lote" id="pin_lote" type="text" value="<?php echo $lote; ?>" size="10" maxlength="10" onKeypress="return verifica();">
					<br>Totaliza por Lotes
					 <select name="pin_tot_lote" id="pin_tot_lote" onChange='reload_tot()'>
						<option value='S' <?php echo (($tot_lote=="S")?" selected":"") ?>>SIM</option>
						<option value="N" <?php echo (($tot_lote=="N")?" selected":"") ?>>N&Atilde;O</option>
					 </select>
                  </td>
				  <td class="texto" align="center"><input type="submit" name="btPesquisar" id="btPesquisar" value="Pesquisar" class="btn btn-sm btn-info">
				  <?php
				  if ($tf_v_tipo==3 && !empty($distributor_codigo) && !empty($opr_codigo)) {
				  ?>
					  <input type="hidden" id="hidgerar" name="hidgerar" value="">
					  <input type="button" name="btGerar" value="Gerar Arquivo" class="btn btn-sm btn-info" onClick="javascript:reload_gerar_arquivo();">
				  <?php
				  }
				  ?>
				  </td>
    	        </tr>
			</table>
			<table class="txt-preto fontsize-pp table">
    	        <tr bgcolor="F0F0F0">
                    <td class="texto" align="center" width="4%"><a href="javascript:marcar_desmarcar();">Marcar<br>Desmarcar</a></td>
                    <td class="texto" align="center" width="5%"><b>Publisher</b>&nbsp;</td>
                    <td class="texto" align="center" width="14%"><b>Distribuidora</b></td>
                    <td class="texto" align="center" width="6%"><b>Lote</b></td>
                    <td class="texto" align="center" width="5%"><b>Valor</b></td>
                    <td class="texto" align="center" width="9%"><b>Data de Gera&ccedil;&atilde;o</b></td>
                    <td class="texto" align="center" width="9%"><b><nobr>Total PINs</nobr> no Lote</b></td>
                    <td class="texto" align="center" width="7%"><b><font color="<?php echo $PINS_STORE_STATUS_COLORS[$PINS_STORE_STATUS_VALUES['D']]?>"><?php echo " (".$PINS_STORE_STATUS_VALUES['D'].")<br>".$PINS_STORE_STATUS[$PINS_STORE_STATUS_VALUES['D']]?></font></b></td>
                    <td class="texto" align="center" width="7%"><b><font color="<?php echo $PINS_STORE_STATUS_COLORS[$PINS_STORE_STATUS_VALUES['P']]?>"><?php echo " (".$PINS_STORE_STATUS_VALUES['P'].")<br>".$PINS_STORE_STATUS[$PINS_STORE_STATUS_VALUES['P']]?></font></b></td>
                    <td class="texto" align="center" width="7%"><b><font color="<?php echo $PINS_STORE_STATUS_COLORS[$PINS_STORE_STATUS_VALUES['A']]?>"><?php echo " (".$PINS_STORE_STATUS_VALUES['A'].")<br>".$PINS_STORE_STATUS[$PINS_STORE_STATUS_VALUES['A']]?></font></b></td>
                    <td class="texto" align="center" width="7%"><b><font color="<?php echo $PINS_STORE_STATUS_COLORS[$PINS_STORE_STATUS_VALUES['U']]?>"><?php echo " (".$PINS_STORE_STATUS_VALUES['U'].")<br>".$PINS_STORE_STATUS[$PINS_STORE_STATUS_VALUES['U']]?></font></b></td>
                    <td class="texto" align="center" width="7%"><b><font color="<?php echo $PINS_STORE_STATUS_COLORS[$PINS_STORE_STATUS_VALUES['B']]?>"><?php echo " (".$PINS_STORE_STATUS_VALUES['B'].")<br>".$PINS_STORE_STATUS[$PINS_STORE_STATUS_VALUES['B']]?></font></b></td>
                    <td class="texto" align="center" width="7%"><b><font color="<?php echo $PINS_STORE_STATUS_COLORS[$PINS_STORE_STATUS_VALUES['C']]?>"><?php echo " (".$PINS_STORE_STATUS_VALUES['C'].")<br>".$PINS_STORE_STATUS[$PINS_STORE_STATUS_VALUES['C']]?></font></b></td>
                    <td class="texto" align="center" width="3%"><b>J&aacute; em<br>Arquivo</b></td>
                    <td class="texto" align="center" width="3%"><b>Circulante</b></td>
                </tr>
		<?php	

			$i=0;
			$irows=0;
			if(isset($rs_pins)) {

				while($rs_pins_row = pg_fetch_array($rs_pins)){ 
					$bgcolor = ((++$i) % 2)?" bgcolor=\"F5F5FB\"":"";
					$irows++;
			?>
    	        <tr<?php echo $bgcolor?> valign="top">
    	          <td class="texto" align="center">&nbsp;<input name="chkPIN[]" id="chkPIN" type="checkbox" value="<?php echo $rs_pins_row['pin_lote_codigo']."|".$rs_pins_row['distributor_codigo']."|".$rs_pins_row['opr_codigo'];?>"/>&nbsp;</nobr></td>
                  <td class="texto" align="center"><nobr>&nbsp;<?php echo $publisher_array[$rs_pins_row['opr_codigo']]." (".$rs_pins_row['opr_codigo'].")" ?>&nbsp;</td>
    	          <td class="texto" align="center"><nobr>&nbsp;<?php echo $operacao_array[$rs_pins_row['distributor_codigo']]." (".$rs_pins_row['distributor_codigo'].")" ?>&nbsp;</nobr></td>
    	          <td class="texto" align="center"><nobr>&nbsp;<?php echo $rs_pins_row['pin_lote_codigo']?>&nbsp;</nobr></td>
    	          <td class="texto" align="center">&nbsp;<?php echo $rs_pins_row['pin_valor']?>&nbsp;</td>
    	          <td class="texto" align="center"><nobr>&nbsp;<?php echo $rs_pins_row['data']?>&nbsp;</nobr></td>
				  <td class="texto" align="center"><nobr>&nbsp;
				  <?php 
					$sql_total = "select count(pc.pin_codinterno) as total from pins_card pc where distributor_codigo = ".$rs_pins_row['distributor_codigo']." and opr_codigo = ".$rs_pins_row['opr_codigo']." and pin_lote_codigo = ".$rs_pins_row['pin_lote_codigo']." and to_char(pin_dataentrada,'DD/MM/YYYY') = '".$rs_pins_row['data']."'";
				  	$rs_total = SQLexecuteQuery($sql_total);
					unset($rs_total_row);
					if($rs_total && pg_num_rows($rs_total) > 0) {
						$rs_total_row = pg_fetch_array($rs_total);
						echo $rs_total_row['total'];
						$auxTesteDip = $rs_total_row['total'];
					}
					else $auxTesteDip=0;
				  ?>&nbsp;</nobr></td>
    	          <td class="texto" align="center"><nobr>&nbsp;
				  <?php 
					$sql_total = "select count(pc.pin_codinterno) as total from pins_card pc where distributor_codigo = ".$rs_pins_row['distributor_codigo']." and opr_codigo = ".$rs_pins_row['opr_codigo']." and pin_lote_codigo = ".$rs_pins_row['pin_lote_codigo']." and to_char(pin_dataentrada,'DD/MM/YYYY') = '".$rs_pins_row['data']."' and pin_status='".intval($PINS_STORE_STATUS_VALUES['D'])."' ";
					$rs_total = SQLexecuteQuery($sql_total);
					unset($rs_total_row);
					if($rs_total && pg_num_rows($rs_total) > 0) {
						$rs_total_row = pg_fetch_array($rs_total);
						//if ($rs_total_row['total']<$auxTesteDip)
						if ($rs_total_row['total']>0)
							echo "<font color='#000000' size='2' style='font-weight:bold;'>".$rs_total_row['total']."</font>";
						else echo $rs_total_row['total'];
					}
				  ?>&nbsp;</nobr></td>
				  <td class="texto" align="center"><nobr>&nbsp;
				  <?php 
					$sql_total = "select count(pc.pin_codinterno) as total from pins_card pc where distributor_codigo = ".$rs_pins_row['distributor_codigo']." and opr_codigo = ".$rs_pins_row['opr_codigo']." and pin_lote_codigo = ".$rs_pins_row['pin_lote_codigo']." and to_char(pin_dataentrada,'DD/MM/YYYY') = '".$rs_pins_row['data']."' and pin_status='".intval($PINS_STORE_STATUS_VALUES['P'])."' ";
				  // echo $sql_total;
					$rs_total = SQLexecuteQuery($sql_total);
					unset($rs_total_row);
					if($rs_total && pg_num_rows($rs_total) > 0) {
						$rs_total_row = pg_fetch_array($rs_total);
						if ($rs_total_row['total']>0)
							echo "<font color='#000000' size='2' style='font-weight:bold;'>".$rs_total_row['total']."</font>";
						else echo $rs_total_row['total'];
					}
				  ?>&nbsp;</nobr></td>
				  <td class="texto" align="center"><nobr>&nbsp;
				  <?php 
					$sql_total = "select count(pc.pin_codinterno) as total from pins_card pc where distributor_codigo = ".$rs_pins_row['distributor_codigo']." and opr_codigo = ".$rs_pins_row['opr_codigo']." and pin_lote_codigo = ".$rs_pins_row['pin_lote_codigo']." and to_char(pin_dataentrada,'DD/MM/YYYY') = '".$rs_pins_row['data']."' and pin_status='".intval($PINS_STORE_STATUS_VALUES['A'])."' ";
					$rs_total = SQLexecuteQuery($sql_total);
					unset($rs_total_row);
					if($rs_total && pg_num_rows($rs_total) > 0) {
						$rs_total_row = pg_fetch_array($rs_total);
						if ($rs_total_row['total']>0)
							echo "<font color='#000000' size='2' style='font-weight:bold;'>".$rs_total_row['total']."</font>";
						else echo $rs_total_row['total'];
					}
				  ?>&nbsp;</nobr></td>
				  <td class="texto" align="center"><nobr>&nbsp;
				  <?php 
					$sql_total = "select count(pc.pin_codinterno) as total from pins_card pc where distributor_codigo = ".$rs_pins_row['distributor_codigo']." and opr_codigo = ".$rs_pins_row['opr_codigo']." and pin_lote_codigo = ".$rs_pins_row['pin_lote_codigo']." and to_char(pin_dataentrada,'DD/MM/YYYY') = '".$rs_pins_row['data']."' and pin_status='".intval($PINS_STORE_STATUS_VALUES['U'])."' ";
					$rs_total = SQLexecuteQuery($sql_total);
					unset($rs_total_row);
					unset($aux_utilizado);
					if($rs_total && pg_num_rows($rs_total) > 0) {
						$rs_total_row = pg_fetch_array($rs_total);
						if ($rs_total_row['total']>0)
							echo "<font color='#000000' size='2' style='font-weight:bold;'>".$rs_total_row['total']."</font>";
						else echo $rs_total_row['total'];
						$aux_utilizado = $rs_total_row['total'];
					}
				  ?>&nbsp;</nobr></td>
				  <td class="texto" align="center"><nobr>&nbsp;
				  <?php 
					$sql_total = "select count(pc.pin_codinterno) as total from pins_card pc where distributor_codigo = ".$rs_pins_row['distributor_codigo']." and opr_codigo = ".$rs_pins_row['opr_codigo']." and pin_lote_codigo = ".$rs_pins_row['pin_lote_codigo']." and to_char(pin_dataentrada,'DD/MM/YYYY') = '".$rs_pins_row['data']."' and pin_status='".intval($PINS_STORE_STATUS_VALUES['B'])."' ";
					$rs_total = SQLexecuteQuery($sql_total);
					unset($rs_total_row);
					unset($aux_bloqueado);
					if($rs_total && pg_num_rows($rs_total) > 0) {
						$rs_total_row = pg_fetch_array($rs_total);
						if ($rs_total_row['total']>0)
							echo "<font color='#000000' size='2' style='font-weight:bold;'>".$rs_total_row['total']."</font>";
						else echo $rs_total_row['total'];
						$aux_bloqueado = $rs_total_row['total'];
					}
				  ?>&nbsp;</nobr></td>
				  <td class="texto" align="center"><nobr>&nbsp;
				  <?php 
					$sql_total = "select count(pc.pin_codinterno) as total from pins_card pc where distributor_codigo = ".$rs_pins_row['distributor_codigo']." and opr_codigo = ".$rs_pins_row['opr_codigo']." and pin_lote_codigo = ".$rs_pins_row['pin_lote_codigo']." and to_char(pin_dataentrada,'DD/MM/YYYY') = '".$rs_pins_row['data']."' and pin_status='".intval($PINS_STORE_STATUS_VALUES['C'])."' ";
					$rs_total = SQLexecuteQuery($sql_total);
					unset($rs_total_row);
					unset($aux_cancelado);
					if($rs_total && pg_num_rows($rs_total) > 0) {
						$rs_total_row = pg_fetch_array($rs_total);
						if ($rs_total_row['total']>0)
							echo "<font color='#000000' size='2' style='font-weight:bold;'>".$rs_total_row['total']."</font>";
						else echo $rs_total_row['total'];
						$aux_cancelado = $rs_total_row['total'];
					}
				  ?>&nbsp;</nobr></td>
				  <td class="texto" align="center"><nobr>&nbsp;
				  <?php 
					$sql_total = "select count(pc.pin_codinterno) as total from pins_card pc where distributor_codigo = ".$rs_pins_row['distributor_codigo']." and opr_codigo = ".$rs_pins_row['opr_codigo']." and pin_lote_codigo = ".$rs_pins_row['pin_lote_codigo']." and to_char(pin_dataentrada,'DD/MM/YYYY') = '".$rs_pins_row['data']."' and pin_arq_gerado IS NOT NULL";
					//echo $sql_total;
					$rs_total = SQLexecuteQuery($sql_total);
					unset($rs_total_row);
					unset($aux_ja_arq);
					if($rs_total && pg_num_rows($rs_total) > 0) {
						$rs_total_row = pg_fetch_array($rs_total);
						if ($rs_total_row['total']>0)
							echo "<font color='#000000' size='2' style='font-weight:bold;'>".$rs_total_row['total']."</font>";
						else echo $rs_total_row['total'];
						$aux_ja_arq = $rs_total_row['total'];
					}
				  ?>&nbsp;</nobr></td>
    			  <td class="texto" align="center"><nobr>&nbsp;
				  <?php 
					unset($aux_total);
					$sql_total = "select 
									count(case when ((pc.pin_arq_gerado is not null) AND 
									(pc.pin_status != '".intval($PINS_STORE_STATUS_VALUES['U'])."') AND 
									(pc.pin_status != '".intval($PINS_STORE_STATUS_VALUES['C'])."') AND 
									(pc.pin_status != '".intval($PINS_STORE_STATUS_VALUES['B'])."')) then 1 end) as total
								from pins_card pc
								where distributor_codigo = ".$rs_pins_row['distributor_codigo']." and
									opr_codigo = ".$rs_pins_row['opr_codigo']." and 
                                                                        pin_lote_codigo = ".$rs_pins_row['pin_lote_codigo']." and
									to_char(pin_dataentrada,'DD/MM/YYYY') = '".$rs_pins_row['data']."' and 
									pin_arq_gerado IS NOT NULL";
						//echo $sql_total;
						$rs_total = SQLexecuteQuery($sql_total);
						unset($rs_total_row);
						unset($aux_ja_arq);
						if($rs_total && pg_num_rows($rs_total) > 0) {
							$rs_total_row = pg_fetch_array($rs_total);
							if ($rs_total_row['total']>0)
								echo "<font color='#000000' size='2' style='font-weight:bold;'>".$rs_total_row['total']."</font>";
							else echo $rs_total_row['total'];
							$aux_ja_arq = $rs_total_row['total'];
						}
				  ?>&nbsp;</nobr></td>
    	        </tr>
		<?php
				}
				if($irows==0) {
			?>
					<tr>
					  <td class="texto" align="center" colspan="16">&nbsp;<font color='#FF0000'>N&atilde;o foram encontrados pins para os valores escolhidos.</font></td>
					</tr>
			<?php
				}

			} else {
		?>
    	        <tr>
    	          <td class="texto" align="center" colspan="16">&nbsp;<font color='#FF0000'>N&atilde;o foram encontrados pins para os valores escolhidos</font></td>
    	        </tr>
		<?php
			}
		?>
			</table>
      </td>
    </tr>
	</table>

	<br>&nbsp;
	<table class="txt-preto fontsize-pp table">
    <tr>
      	<td align="center" class="texto"><nobr>
      		<?php if($p > 1){ ?>
         	<input type="button" name="btAnterior" value=" < " OnClick="window.location='?p=<?php echo $p-1?><?php echo $varsel?>';" class="btn btn-sm btn-info">
         	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<?php } ?>
         	<input type="button" name="btOK" value="Voltar" OnClick="window.location='index.php';" class="btn btn-sm btn-info">
      		<select id='op' name='op' onChange='reload_acao();'>
                        <option value=''>Selecione uma Op&ccedil;&atilde;o</option>
                        <option value='ati'>Ativar</option>
						<option value='can'>Cancelar</option>
						<option value='blo'>Bloquear</option>
						<option value='des'>Desbloquear</option>
				 </select>
                 <?php if($p < ($registros_total/$registros)){ ?>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
         	<input type="button" name="btProximo" value=" > " OnClick="window.location='?p=<?php echo $p+1?><?php echo $varsel?>';" class="btn btn-sm btn-info">
			<?php } ?></nobr>
      	</td>
    </tr>
	</table>
	<br>&nbsp;

	<table class="txt-preto fontsize-pp table">
	  <tr align="center"> 
		<td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td><td bgcolor="#FFFFFF" class="texto">Processamento em <?php echo number_format(getmicrotime() - $time_start_stats, 2, '.', '.') ?> s.</font></td><td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td>
	  </tr>
	</table>

	</form>
	</center>
</div>
</body>
</html>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
