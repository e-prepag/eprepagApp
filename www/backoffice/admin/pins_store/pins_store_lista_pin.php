<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once $raiz_do_projeto . "includes/main.php";
require_once $raiz_do_projeto . "includes/gamer/main.php";
require_once $raiz_do_projeto . "class/classIntegracaoPin.php";
require_once $raiz_do_projeto . "class/classIntegracaoPinCash.php";
require_once $raiz_do_projeto . "includes/inc_functions.php";
require_once $raiz_do_projeto . "class/classPinsStore.php";       

$operacao_array = VetorDistribuidoras();
$tot_lote 		= isset($_POST['pin_tot_lote'])	? $_POST['pin_tot_lote']: 'S';
$distributor_codigo 	= isset($_POST['pin_operacao'])	? $_POST['pin_operacao']: null;
$lote           = isset($_POST['pin_lote'])		? $_POST['pin_lote']	: null;
$valor          = isset($_POST['pin_valor'])	? $_POST['pin_valor']	: null;
$op				= isset($_POST['op'])			? $_POST['op']			: null;
$BtnGerarArq	= isset($_POST['hidgerar'])		? $_POST['hidgerar']	: null;
$tf_v_tipo		= isset($_POST['tf_v_tipo'])	? $_POST['tf_v_tipo']	: null;
$tf_v_formato	= isset($_POST['tf_v_formato'])	? $_POST['tf_v_formato']: null;
$ids_temp       = isset($_POST['chkPIN'])		? $_POST['chkPIN']		: null;
$time_start_stats = getmicrotime();
?>
<!doctype html public "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" /> 
<meta http-equiv="Content-Language" content="pt-br" /> 
<title> pins_store lista </title>
<link href="/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="/js/global.js"></script>
<script language="JavaScript">
<!--
        $(function(){
            var optDate = new Object();
                optDate.interval = 1000;

            setDateInterval('tf_v_data_inclusao_ini','tf_v_data_inclusao_fim',optDate);
        });
        
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
		document.form1.action = "pins_store_lista_pin.php";
		document.form1.submit();
	}
	function reload_tot() {
		if (document.form1.pin_tot_lote.value=="S")
			document.form1.action = "pins_store_lista.php";
		else document.form1.action = "pins_store_lista_pin.php";
		document.form1.submit();
	}
	function reload_gerar_arquivo() {
		if (algumCheckBoxSel())
		{
			if (confirm("Realmente deseja gerar o Arquivo?"))
			{
				document.form1.action = "pins_store_lista_pin.php";
				document.getElementById('hidgerar').value = "gerar";
				document.form1.submit();
			}
		}
		else  {
			alert("Deve ser selecionado ao menos um PIN\n para gerar o arquivo!");
			return false;
		}
	}
	function reload_acao() {
		if (algumCheckBoxSel())
		{
			document.form1.action = "pins_store_lista_pin.php";
			if (document.getElementById('op').value == 'blo')
			{
				if (confirm("ATENCAO: Somete serao bloqueados os PIN ATIVOS do LOTE.\n Realmente deseja bloquear?"))
				{
					document.form1.submit();
				}
				else document.getElementById('op').value='';
			}
			else if (document.getElementById('op').value == 'des')
				{
					if (confirm("ATENCAO: Somete serao DESbloqueados os PIN com STATUS de bloqueado dentro do LOTE.\n Realmente deseja DESbloquear?"))
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
			alert("Deve ser selecionado ao menos um PIN\n para executar esta tarefa!");
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
</head>

<body>
<?php
//paginacao
$p = $_GET['p'];
if(!$p) $p = 1;
$registros = 50;
$registros_total = 0;
//rotinas de inicializacao se click em botao gerar arquivo
if(!empty($BtnGerarArq) && $tf_v_tipo==3) {
	// Deleta arquivos >5horas
	$now = mktime(date('H'), date('i'), date('s'), date('m'), date('d'), date('Y'));
	foreach (glob("arquivos/*.txt") as $filename) {
		if(($now-filemtime($filename))>5*3600) {
			unlink($filename);
		}
	}
	// Arquivo
	$path = $GLOBALS['raiz_do_projeto'];
	$url = "arquivos_gerados/pins_store/";
	$file = date("YmdHis").str_pad(rand(0,999), 3, "0", STR_PAD_LEFT).".txt";
	$varArquivo = $path.$url.$file;
	$sArq = "Lote;Valor;PIN code\n";
	//variavel de controle
	$habil_download = true;
}
else {
	//variavel de controle
	$habil_download = false;
}
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
		$ids="";
		for ($i=0; $i<count($ids_temp);$i++) {
			$sql = "select pin_status from pins_store where pin_codinterno=".intval($ids_temp[$i]).";";
			$rs_pins = SQLexecuteQuery($sql);
			if(pg_num_rows($rs_pins) <> 0) {
				$rs_pin_row = pg_fetch_array($rs_pins);
				$pin_status = $rs_pin_row['pin_status'];
				if (($pin_status == $PINS_STORE_STATUS_VALUES['D'])||($pin_status == $PINS_STORE_STATUS_VALUES['U'])||($pin_status == $PINS_STORE_STATUS_VALUES['C'])||($pin_status == $status_new)||(($pin_status == $PINS_STORE_STATUS_VALUES['P'])&&($status_new==$PINS_STORE_STATUS_VALUES['B']))||(($pin_status == $PINS_STORE_STATUS_VALUES['P'])&&($op=='des'))) {
					$msg_pin .= "O PIN ".$ids_temp[$i]." n&atilde;o pode ter seu Status alterado pois est&aacute; com Status de <font color='".$PINS_STORE_STATUS_COLORS[$pin_status]."'>".strtoupper($PINS_STORE_STATUS[$pin_status])." (".$pin_status.")</font>. <br>";
				}
				else if (strlen($ids)==0) {
							$ids = $ids_temp[$i];
					}
					else $ids .= ','.$ids_temp[$i];
			}
		}
		if(strlen($ids)>0) {
				if($msg == ""){
					$sql  = "update pins_store set pin_status='".intval($status_new)."'".$setAdicinal." where pin_codinterno IN (".$ids.") and pin_status!='".intval($PINS_STORE_STATUS_VALUES['D'])."' and pin_status!='".intval($PINS_STORE_STATUS_VALUES['U'])."' and pin_status!='".intval($PINS_STORE_STATUS_VALUES['C'])."' ".$condAdicional.";";
					$rs_pins_save = SQLexecuteQuery($sql);
					if($rs_pins_save ) {
						$msg_pin .= "<font color='#FF0000'><b>PINs atualizado com sucesso ('$op', $ids)</b></font><br>";
					} else {
						 $msg = "<font color='#FF0000'><b>Erro ao atualizar o range de PINs (".$ids.").\n</b></font><br>";
					}
				}
				
		} else {
			$msg_pin .= "<font color='#FF0000'><b>N&atilde;o foi selecionado nenhum PIN v&aacute;lido ('$op', '<<$ids>>')</b></font><br>";
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

//Recupera as vendas
if($msg == ""){

	$sql  = "select * from pins_store ps where pin_status != '".intval($PINS_STORE_STATUS_VALUES['D'])."' "; //and pin_status != 4

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
	
	$rs_total = SQLexecuteQuery($sql);
	if($rs_total) $registros_total = pg_num_rows($rs_total);
	$sql .= " order by pin_codinterno desc ";	
	$sql .= " offset " . intval(($p - 1) * $registros) . " limit " . intval($registros);
//echo $sql ."<br>\n";
	$rs_pins = SQLexecuteQuery($sql);
	if(!$rs_pins || pg_num_rows($rs_pins) == 0) $msg = "Nenhum pin encontrado.\n";
	// permitindo a descryptar
	else {
		$chave256bits = new Chave();
		$ps = new AES($chave256bits->retornaChave());
	}
}
?>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="1">BackOffice - Money</a></li>
        <li class="active">Listar PINs E-PREPAG PIN por PIN</li>
    </ol>
</div>  
<div class="col-md-12 txt-preto fontsize-pp">
<?php
include "pins_store_menu.php";
?>
<form name="form1" method="post" action="pins_store_lista_pin.php">
	<table class="table txt-preto fontsize-pp bg-branco">
    <tr><td align="right"><font face="Arial, Helvetica, sans-serif" size="2" color="#00008C"><a href="index.php" class="menu"><img src="../../../images/voltar.gif" width="47" height="15" border="0"></a></font></td></tr>
	<?php
	echo "<tr><td>".$msg_pin."</td></tr>";
	?>
    <tr><td>&nbsp;<?php if($habil_download) {?><div onclick="javascript:ShowPopupWindowXY('<?php echo $url.$file; ?>', 800, 600);" alt="Clique aqui para visualizar o arquivo">Salvar arquivo click aqui.</div> 
		<SCRIPT LANGUAGE=JAVASCRIPT><!--
		// Abre janela para download
		ShowPopupWindowXY('<?php echo $url.$file; ?>', 800, 600);
		//--></SCRIPT><?php } ?>
	</td></tr>
        <tr valign="top" align="center">
      <td>
			<input type="hidden" name="p" value="<?php echo $p; ?>">
			<table class="table txt-preto fontsize-pp bg-branco">
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
                            if ($key <> 1)
								echo "<option value='".$key."'".(($tf_v_tipo== $key)?" selected":"").">".$val." (".$key.")</option>\n";
						}
					?>
					  </select>
				  </td>
    	          <td class="texto" align="center"><nobr>&nbsp;
					  <input name="tf_v_data_inclusao_ini" type="text" class="form" id="tf_v_data_inclusao_ini" value="<?php echo $tf_v_data_inclusao_ini ?>" size="9" maxlength="10">
					  <a href="#" onClick="return false;"><img src="../../../../images/cal.gif" width="16" height="16" alt="Calendario" onclick="popUpCalendar(this, form1.tf_v_data_inclusao_ini, 'dd/mm/yyyy')" border="0" align="absmiddle"></a> 
					  a 
					  <input name="tf_v_data_inclusao_fim" type="text" class="form" id="tf_v_data_inclusao_fim" value="<?php echo $tf_v_data_inclusao_fim ?>" size="9" maxlength="10">
					  <a href="#" onClick="return false;"><img src="../../../../images/cal.gif" width="16" height="16" alt="Calendario" onclick="popUpCalendar(this, form1.tf_v_data_inclusao_fim, 'dd/mm/yyyy')" border="0" align="absmiddle"></a>&nbsp;</nobr>
				  </td>
				  <td class="texto" align="center">&nbsp;Valor
                                    <input name="pin_valor" id="pin_valor" type="text" value="<?php echo $valor; ?>" size="10" maxlength="10" onKeypress="return verifica();"></td>
    	        </tr>
    	        <tr bgcolor="F5F5FB">
    	          <td class="texto" align="center">
					<select id='tf_v_formato' name='tf_v_formato' onChange='reload()'> 
						<option value=''<?php echo (($tf_v_formato=="")?" selected":"") ?>>Todos os formatos</option>
						<?php
							foreach($formato_array as $key => $val) {
								echo "<option value='".$key."'".(($tf_v_formato==(string)$key)?" selected":"").">".$val." - Formato (".$key.")</option>\n";
							}
						?>
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
				  <td class="texto" align="center"><input type="submit" name="btPesquisar" value="Pesquisar" class="botao_simples">
				  <?php /*
				  if ($tf_v_tipo==3) {
				  	  <input type="hidden" id="hidgerar" name="hidgerar" value="">
					  <input type="button" name="btGerar" value="Gerar Arquivo" class="botao_simples" onClick="javascript:reload_gerar_arquivo();">
				  } */
				  ?>
				  </td>
    	        </tr>
			</table>
			<table class="table txt-preto fontsize-pp bg-branco">
    	        <tr bgcolor="F0F0F0">
    	          <td class="texto" align="center" width="10%"><a href="javascript:marcar_desmarcar();">Marcar<br>Desmarcar</a></td>
    	          <td class="texto" align="center" width="5%"><b>ID</b>&nbsp;</td>
    	          <td class="texto" align="center" width="5%"><b>Formato</b>&nbsp;</td>
    	          <td class="texto" align="center" width="10%"><b>Distribuidora</b></td>
    	          <td class="texto" align="center" width="10%"><b>Lote</b></td>
    	          <td class="texto" align="center" width="10%"><b>Valor</b></td>
    	          <td class="texto" align="center" width="10%"><b>Canal</b></td>
    	          <td class="texto" align="center" width="10%"><b>Status</b></td>
    	          <td class="texto" align="center" width="10%"><b>&nbsp;</b></td>
    	          <td class="texto" align="center" width="40%"><b>Serial Number</b></td>
    	        </tr>
		<?php	

			$i=0;
			$irows=0;
			if($rs_pins) {

				while($rs_pins_row = pg_fetch_array($rs_pins)){ 
					$bgcolor = ((++$i) % 2)?" bgcolor=\"F5F5FB\"":"";
					$irows++;
					// Gera conteudo do Arquivo
					if($habil_download) {
						if (in_array($rs_pins_row['pin_codinterno'], $ids_temp, true)) 
							$sArq .= $rs_pins_row['pin_lote_codigo'].";".$rs_pins_row['pin_valor'].";".$ps->decrypt(base64_decode($rs_pins_row['pin_codigo']))."\n";
							//$sArq .= $rs_pins_row['pin_formato'].";".str_replace(' - Formato ('.$rs_pins_row['pin_formato'].')','',$operacao_array[$rs_pins_row['distributor_codigo']])." (".$rs_pins_row['distributor_codigo'].")".";".$rs_pins_row['pin_lote_codigo'].";".$rs_pins_row['pin_valor'].";".$rs_pins_row['pin_status'].";".$rs_pins_row['pin_serial'].";".$ps->decrypt(base64_decode($rs_pins_row['pin_codigo']))."\n";
						
					}

										
			?>
    	        <tr<?php echo $bgcolor?> valign="top">
    	          <td class="texto" align="center">&nbsp;<input name="chkPIN[]" id="chkPIN" type="checkbox" value="<?php echo $rs_pins_row['pin_codinterno'];?>" />&nbsp;</td>
                  <td class="texto" align="center">&nbsp;<?php echo $rs_pins_row['pin_codinterno']?></td>
    	          <td class="texto" align="center">&nbsp;<?php echo $rs_pins_row['pin_formato']?></td>
    	          <td class="texto" align="center"><nobr>&nbsp;<?php echo $operacao_array[$rs_pins_row['distributor_codigo']]." (".$rs_pins_row['distributor_codigo'].")" ?>&nbsp;</nobr></td>
    	          <td class="texto" align="center"><nobr>&nbsp;<?php echo str_replace("-", "", substr($rs_pins_row['pin_dataentrada'],0,10))."_".$rs_pins_row['pin_lote_codigo']?>&nbsp;</nobr></td>
    	          <td class="texto" align="center">&nbsp;<?php echo $rs_pins_row['pin_valor']?>&nbsp;</td>
    	          <td class="texto" align="center">&nbsp;<?php echo (($rs_pins_row['pin_canal']=="s")?"Site": (($rs_pins_row['pin_canal']=="p")?"POS":(($rs_pins_row['pin_canal']=="w")?"Walet":"??desconhecido??")) ) ?>&nbsp;</td>
    	          <td class="texto" align="center"><nobr>&nbsp;<font color="<?php echo $PINS_STORE_STATUS_COLORS[$rs_pins_row['pin_status']]?>"><?php echo $PINS_STORE_STATUS[$rs_pins_row['pin_status']]." (".$rs_pins_row['pin_status'].")"?></font>&nbsp;</nobr></td>
    	          <td class="texto" align="center">&nbsp;&nbsp;</td>

    	          <td class="texto" align="center"><nobr>&nbsp;<?php echo $rs_pins_row['pin_serial']?>&nbsp;</nobr></td>
    	        </tr>
		<?php
				}
				// Grava Arquivo
				if($habil_download) {
						$handle = fopen($varArquivo, "w+");
						if (fwrite($handle, $sArq) === FALSE) {
							echo "<tr><td colspan=12><font color='#0000CC'>N&atilde;o foi poss&iacute;vel gravar o Arquivo(2).</font></td></tr>";
						} else {
							echo "<tr><td colspan=12><font color='#0000CC'>Arquivo gravado com sucesso.</font></td></tr>";
						}
						fclose($handle);
				}

				if($irows==0) {
			?>
					<tr>
					  <td class="texto" align="center" colspan="7">&nbsp;<font color='#FF0000'>N&atilde;o foram encontrados pins para os valores escolhidos.</font></td>
					</tr>
			<?php
				}

			} else {
		?>
    	        <tr>
    	          <td class="texto" align="center" colspan="7">&nbsp;<font color='#FF0000'>N&atilde;o foram encontrados pins para os valores escolhidos</font></td>
    	        </tr>
		<?php
			}
		?>
			</table>
      </td>
    </tr>
	</table>

	<br>&nbsp;
	<table class="table txt-preto fontsize-pp bg-branco">
    <tr>
      	<td align="center" class="texto"><nobr>
      		<?php if($p > 1){ ?>
         	<input type="button" name="btAnterior" value=" < " OnClick="window.location='?p=<?php echo $p-1?><?php echo $varsel?>';" class="botao_simples">
         	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<?php } ?>
         	<input type="button" name="btOK" value="Voltar" OnClick="window.location='index.php';" class="botao_simples">
      		<select id='op' name='op' onChange='reload_acao();'>
                        <option value=''>Selecione uma Op&ccedil;&atilde;o</option>
                        <option value='ati'>Ativar</option>
						<option value='can'>Cancelar</option>
						<option value='blo'>Bloquear</option>
						<option value='des'>Desbloquear</option>
				 </select>
                 <?php if($p < ($registros_total/$registros)){ ?>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
         	<input type="button" name="btProximo" value=" > " OnClick="window.location='?p=<?php echo $p+1?><?php echo $varsel?>';" class="botao_simples">
			<?php } ?></nobr>
      	</td>
    </tr>
	</table>
	<br>&nbsp;

	<table class="table txt-preto fontsize-pp bg-branco">
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
