<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once $raiz_do_projeto . "includes/main.php";
require_once $raiz_do_projeto . "includes/gamer/main.php";
require_once $raiz_do_projeto . "class/classIntegracaoPin.php";
require_once $raiz_do_projeto . "class/classIntegracaoPinCash.php";
require_once $raiz_do_projeto . "includes/inc_functions.php";
require_once $raiz_do_projeto . "class/classPinsStore.php";   

$chave256bits = new Chave();
$pc = new AES($chave256bits->retornaChave());

$operacao_array		= VetorDistribuidoras();
$distributor_codigo	= isset($_POST['pin_operacao'])     ? $_POST['pin_operacao']	: null;
$lote				= isset($_POST['pin_lote'])         ? $_POST['pin_lote']        : null;
$valor				= isset($_POST['pin_valor'])        ? $_POST['pin_valor']       : null;
$op					= isset($_POST['op'])				? $_POST['op']				: null;
$tf_v_tipo			= isset($_POST['tf_v_tipo'])		? $_POST['tf_v_tipo']		: null;
$tf_v_formato		= isset($_POST['tf_v_formato'])		? $_POST['tf_v_formato']	: null;
$pin_codigo			= isset($_POST['pin_codigo'])		? $_POST['pin_codigo']		: null;
$ids_temp			= isset($_POST['chkPIN'])           ? $_POST['chkPIN']          : null;
$time_start_stats	= getmicrotime();

//paginacao
$p = $_GET['p'];
if(!$p) $p = 1;
$registros = 50;
$registros_total = 0;

//Vericações e Update
$msg = "";

//Recupera as vendas
if($msg == "" && !empty($btPesquisar)){

	$sql  = "SELECT * from pins_store WHERE 1=1 "; 

	if($tf_v_tipo) {
		if(!(array_key_exists($tf_v_tipo,$PINS_STORE_STATUS)) ) {
			$tf_v_tipo = "";
		}

		if($tf_v_tipo) {
			$sql .= " and pin_status='".intval($tf_v_tipo)."' ";	
		}
	}
	if(strlen($tf_v_formato)) {
		if(!($tf_v_formato==0 || $tf_v_formato==1 || $tf_v_formato==2 || $tf_v_formato==3 || $tf_v_formato==4)) {
			$tf_v_formato = "";
		}

		if(strlen($tf_v_formato)) {
			$sql .= " and pin_formato='".intval($tf_v_formato)."' ";	
		}
	}
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
	if(strlen($pin_codigo)) {
		//Instanciando Objetos para Descriptografia
		$chave256bits = new Chave();
		$ps = new AES($chave256bits->retornaChave());
		$sql .= " and pin_codigo='".base64_encode($ps->encrypt(addslashes($pin_codigo)))."'";
	}
	if(strlen($pin_bloqueio))
				$sql .= " and pin_bloqueio=".intval($pin_bloqueio);
	if(strlen($pin_codinterno))
				$sql .= " and pin_codinterno=".intval($pin_codinterno);
	if(strlen($pin_serial))
				$sql .= " and pin_serial like '%".intval($pin_serial)."'";
	$rs_total = SQLexecuteQuery($sql);
//echo $sql."<br>";
	if($rs_total) $registros_total = pg_num_rows($rs_total);
	$sql .= " ORDER BY pin_codinterno DESC";	
	$sql .= " offset " . intval(($p - 1) * $registros) . " limit " . intval($registros);
//echo $sql ."<br>\n";
	$rs_pins = SQLexecuteQuery($sql);
	if(!$rs_pins || pg_num_rows($rs_pins) == 0) $msg = "Nenhum pin encontrado.\n";
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
		document.form1.action = "pins_store_historico.php";
		document.form1.submit();
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
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><?php echo $sistema->item->getDescricao(); ?></li>
    </ol>
</div>  
<div class="col-md-12 txt-preto fontsize-pp">
<?php
include "pins_store_menu.php";
?>
<form name="form1" method="post" action="pins_store_historico.php">
    <table class="txt-preto fontsize-pp table">
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
    	          <td class="texto" align="center"><select name="pin_operacao" id="pin_operacao" onChange='reload()'>
                                        <option value='' <?php echo (($distributor_codigo=="")?" selected":"") ?>>Todas as Distribuidoras</option>
					<?php foreach ($operacao_array as $key => $value) { ?>
                                        <option value="<?php echo $key ?>" <?php if($key == $distributor_codigo) echo "selected"; ?>><?php echo $value; ?></option>
                                        <?php } ?>
                                     </select>
                  </td>
    	        </tr>
    	        <tr bgcolor="F5F5FB">
    	          <td class="texto" align="center"><nobr>&nbsp;
					<select id='tf_v_tipo' name='tf_v_tipo' onChange='reload()'> 
						<option value=''<?php echo (($tf_v_tipo=="")?" selected":""); ?>>Todos os status</option> 
					<?php
						foreach($PINS_STORE_STATUS as $key => $val) {
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
					<select id='tf_v_formato' name='tf_v_formato' onChange='reload()'> 
						<option value=''"<?php echo (($tf_v_formato=="")?" selected":"") ?>>Todos os formatos</option>
						<?php
							foreach($formato_array as $key => $val) {
								echo "<option value='".$key."'".(($tf_v_formato==(string)$key)?" selected":"").">".$val." - Formato (".$key.")</option>\n";
							}
						?>
					  </select>
				  </td>
    	          <td class="texto" align="center">&nbsp;Lote
                    <input name="pin_lote" id="pin_lote" type="text" value="<?php echo $lote; ?>" size="10" maxlength="10" onKeypress="return verifica();">
                  </td>
				  <td class="texto" align="center">&nbsp;PIN
                    <input name="pin_codigo" id="pin_codigo" type="text" value="<?php echo $pin_codigo; ?>" size="20" maxlength="16">
				  </td>
    	        </tr> 
				<tr bgcolor="F5F5FB">
    	          <td class="texto" align="center">&nbsp;Serial Number
                    <input name="pin_serial" id="pin_serial" type="text" value="<?php echo $pin_serial; ?>" size="10" maxlength="10">
				  </td>
    	          <td class="texto" align="center">&nbsp;PINs Bloqueados
                    <input type="checkbox" name="pin_bloqueio" id="pin_bloqueio"<?php if($pin_bloqueio) echo " CHECKED"; ?> value="1">
                  </td>
				  <td class="texto" align="center">&nbsp;ID do PIN
                    <input name="pin_codinterno" id="pin_codinterno" type="text" value="<?php echo $pin_codinterno; ?>" size="10" maxlength="10">
				  </td>
    	        </tr>
				<tr bgcolor="F5F5FB">
					<td class="texto" align="center" colspan="3"><input type="submit" name="btPesquisar" id="btPesquisar" value="Pesquisar" class="botao_simples">
					</td>
    	        </tr>
			</table>
<?php
$sql_apl  = "SELECT count(*) as total from pins_store_apl_historico WHERE psah_pin_id = 0 or pin_status = 0 ";
$rs_hist_apl = SQLexecuteQuery($sql_apl);
$rs_hist_apl_row = pg_fetch_array($rs_hist_apl);
if($rs_hist_apl_row['total'] > 0) { 
	?>
	<font color='DARKRED'>
			<H3><blink>ALERTA:</blink></H3>
                        Existem [<?php echo number_format($rs_hist_apl_row['total'],0,',','.'); ?>] tentativas de uso de PINs INEXISTENTES. Clique <a href="pins_store_alerta.php">aqui</a> para ver detalhamento.<br><br>
	</font>
<?php
}	  
?>
		
			<table class="txt-preto fontsize-pp table">
    	<?php	

			$i=0;
			$irows=0;
			if($rs_pins) {
				while($rs_pins_row = pg_fetch_array($rs_pins)){ 
    if ($GLOBALS['_SESSION']['userlogin_bko']=='WAGNER') {
                //Instanciando Objetos para Descriptografia
		$chave256bits = new Chave();
		$ps = new AES($chave256bits->retornaChave());
		echo "[".$ps->decrypt(base64_decode(($rs_pins_row['pin_codigo'])))."]";
    }//end if ($GLOBALS['_SESSION']['userlogin_bko']=='WAGNER')

					$bgcolor = (($i++) % 2)?" bgcolor=\"F5F5FB\"":"";
					$irows++;
			?>
    	        <tr bgcolor='#000000'><td colspan='10' height='1'></td></tr>
    	        <tr bgcolor="F0F0F0">
    	          <td class="texto" align="center" width="5%"><b>ID</b>&nbsp;</td>
    	          <td class="texto" align="center" width="5%"><b>Formato</b>&nbsp;</td>
    	          <td class="texto" align="center" width="15%"><b>Distribuidora</b></td>
    	          <td class="texto" align="center" width="10%"><b>Lote</b></td>
    	          <td class="texto" align="center" width="5%"><b>Valor</b></td>
    	          <td class="texto" align="center" width="5%"><b>Canal</b></td>
    	          <td class="texto" align="center" width="5%"><b>Status</b></td>
    	          <td class="texto" align="center" width="1%"><b>&nbsp;</b></td>
    	          <td class="texto" align="center" width="19%"><b>Serial Number</b></td>
                  <td class="texto" align="center" width="19%"><b>PIN</b></td>
    	        </tr>
				<tr bgcolor='#000000'><td colspan='10' height='1'></td></tr>
    	        <tr<?php echo $bgcolor?> valign="top">
				  <td class="texto" align="center">&nbsp;<?php echo $rs_pins_row['pin_codinterno']?></td>
    	          <td class="texto" align="center">&nbsp;<?php echo $rs_pins_row['pin_formato']?></td>
    	          <td class="texto" align="center"><nobr>&nbsp;<?php echo $operacao_array[$rs_pins_row['distributor_codigo']]." (".$rs_pins_row['distributor_codigo'].")" ?>&nbsp;</nobr></td>
    	          <td class="texto" align="center"><nobr>&nbsp;<?php echo str_replace("-", "", substr($rs_pins_row['pin_dataentrada'],0,10))."_".$rs_pins_row['pin_lote_codigo']?>&nbsp;</nobr></td>
    	          <td class="texto" align="center">&nbsp;<?php echo $rs_pins_row['pin_valor']?>&nbsp;</td>
    	          <td class="texto" align="center">&nbsp;<?php echo (($rs_pins_row['pin_canal']=="s")?"Site": (($rs_pins_row['pin_canal']=="p")?"POS":(($rs_pins_row['pin_canal']=="w")?"Walet":"??desconhecido??")) ) ?>&nbsp;</td>
    	          <td class="texto" align="center"><nobr>&nbsp;<font color="<?php echo $PINS_STORE_STATUS_COLORS[$rs_pins_row['pin_status']]?>"><?php echo $PINS_STORE_STATUS[$rs_pins_row['pin_status']]." (".$rs_pins_row['pin_status'].")"?></font>&nbsp;</nobr></td>
    	          <td class="texto" align="center">&nbsp;&nbsp;</td>

    	          <td class="texto" align="center"><nobr>&nbsp;<?php echo $rs_pins_row['pin_serial']?>&nbsp;</nobr></td>
                  <td class="texto" align="center"><nobr>&nbsp;<?php echo $pc->decrypt(base64_decode($rs_pins_row['pin_codigo'])) ?>&nbsp;</nobr></td>
    	          <?php 
				  //$sql  = "SELECT *,to_char(psdh_data,'DD/MM/YYYY HH24:MI:SS') as psdh_data_aux from pins_store_db_historico WHERE psdh_pin_codinterno = '".$rs_pins_row['pin_codinterno']."' order by psdh_data desc";
				  $sql  = "SELECT psdh_pin_status,to_char(MAX(psdh_data),'DD/MM/YYYY HH24:MI:SS') as psdh_data_aux from pins_store_db_historico WHERE psdh_pin_codinterno = '".$rs_pins_row['pin_codinterno']."' group by psdh_pin_status order by MAX(psdh_data) desc";
//if(b_IsUsuarioReinaldo()) { 
//echo str_replace("\n", "<br>\n", $sql)."<br>";
//}
				  
				  $rs_hist = SQLexecuteQuery($sql);
				  if($rs_hist) {?>
					<tr bgcolor='#cccccc'><td colspan='10' height='1'></td></tr>
    	          	</tr><tr bgcolor="F0F0F0">
					<td class="texto" align="center">&nbsp;&nbsp;</td>
					<td class="texto" align="center">&nbsp;&nbsp;</td>
					<td class="texto" align="center"><b>Status</b></td>
    				<td class="texto" align="center" colspan="3"><b>Data Novo Status</b></td>
					<td class="texto" align="center" colspan="3"><b>Tentativas</b></td>
					<td class="texto" align="center"><b>Data da Tentativa</b></td>
					</tr>
					<tr bgcolor='#cccccc'><td colspan='10' height='1'></td></tr>
    	          	<?php
					while($rs_hist_row = pg_fetch_array($rs_hist)){
						$bgcolor = (($i++) % 2)?" bgcolor=\"F5F5FB\"":"";
						$irows++;
					?>
						<tr<?php echo $bgcolor?> valign="top">
						<td class="texto" align="center">&nbsp;&nbsp;</td>
						<td class="texto" align="center">&nbsp;&nbsp;</td>
						<td class="texto" align="center"><nobr>&nbsp;
					    <font color="<?php
							echo $PINS_STORE_STATUS_COLORS[$rs_hist_row['psdh_pin_status']]."\">". $PINS_STORE_STATUS[$rs_hist_row['psdh_pin_status']]." (".$rs_hist_row['psdh_pin_status'].")";
						?></font>&nbsp;</nobr></td>
					    <td class="texto" align="center" colspan="3">
						<?php echo $rs_hist_row['psdh_data_aux'];?>&nbsp;</nobr></td>
						<td class="texto" align="left" colspan="4">
						<?php
						  $sql_apl  = "SELECT *,to_char(psah_data,'DD/MM/YYYY HH24:MI:SS') as psah_data_aux from pins_store_apl_historico WHERE pin_status = ".$rs_hist_row['psdh_pin_status']." and psah_pin_id = '".$rs_pins_row['pin_codinterno']."' order by psah_data DESC";
//if(b_IsUsuarioReinaldo()) { 
//echo str_replace("\n", "<br>\n", $sql_apl)."<br>";
//}

						  $rs_hist_apl = SQLexecuteQuery($sql_apl);
						  if($rs_hist_apl) { ?>
								<table border="0" cellspacing="01" align="center" width="100%">
    	    					<?php
								while($rs_hist_apl_row = pg_fetch_array($rs_hist_apl)){
									?>
									<tr>
									<td class="texto" align="left" width="240">
									<?php
									if (($rs_hist_apl_row['psah_acao'] == $PINS_STORE_MSG_LOG_STATUS['ERRO_VALIDACAO']) || ($rs_hist_apl_row['psah_acao'] == $PINS_STORE_MSG_LOG_STATUS['ERRO_VALOR']) || ($rs_hist_apl_row['psah_acao'] == $PINS_STORE_MSG_LOG_STATUS['ERRO_UTILIZACAO'])) {
										echo "<font color='red'>";
									}
									else {
										echo "<font color='darkgreen'>"; 
									}
									echo $PINS_STORE_MSG_LOG[$rs_hist_apl_row['psah_acao']]."</td><td class=\"texto\" align=\"center\" width=\"130\">".$rs_hist_apl_row['psah_data_aux']."</font>";
									if($rs_hist_apl_row['psah_acao'] == $PINS_STORE_MSG_LOG_STATUS['SUCESSO_UTILIZACAO']) {
										$sql_venda = "select tpc_idvenda from pins_store_pag_epp_pin where ps_pin_codinterno = ".$rs_pins_row['pin_codinterno']."";
//if(b_IsUsuarioReinaldo()) { 
//echo str_replace("\n", "<br>\n", $sql_venda)."<br>";
//}

										echo "<br><div style='background-color:#ccff99; color:blue'>Consultar venda para PIN ".$rs_pins_row['pin_codinterno']."<br>";
										$rs_venda = SQLexecuteQuery($sql_venda);
										if($rs_venda) { 
											while($rs_venda_row = pg_fetch_array($rs_venda)){
												echo "<a href='/gamer/vendas/com_venda_detalhe.php?BtnSearch=1&venda_id=".$rs_venda_row['tpc_idvenda']."' target='_blank'>".$rs_venda_row['tpc_idvenda']."</a> ";
											}
										} else {
										  echo "Venda ".$rs_pins_row['pin_codinterno']." n�o foi encontrada<br></div>";
										}
									}
									echo "</td></tr>";
								} ?> 
								</table>
								<?php
						  }	  
						  ?>
						</td>
						</tr>
						<?php
					} 
				  }						 
				 ?>
		<?php	
				}
				if($irows==0) {
			?>
					<tr>
					  <td class="texto" align="center" colspan="13">&nbsp;<font color='#FF0000'>N&atilde;o foram encontrados pins para os valores escolhidos</font></td>
					</tr>
			<?php
				}

			} else {
		?>
    	        <tr>
    	          <td class="texto" align="center" colspan="13">&nbsp;<font color='#FF0000'>N&atilde;o foram encontrados pins para os valores escolhidos</font></td>
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
         	<input type="button" name="btAnterior" value=" < " OnClick="window.location='?p=<?php echo $p-1?><?php echo $varsel?>';" class="botao_simples">
         	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<?php } ?>
         	<input type="button" name="btOK" value="Voltar" OnClick="window.location='index.php';" class="botao_simples">
      		<?php if($p < ($registros_total/$registros)){ ?>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
         	<input type="button" name="btProximo" value=" > " OnClick="window.location='?p=<?php echo $p+1?><?php echo $varsel?>';" class="botao_simples">
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