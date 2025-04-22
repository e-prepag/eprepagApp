<?php
require_once '../../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
//Include com conexão não persistente
require_once $raiz_do_projeto."db/connect.php";

function gravaLog($file, $mensagem){

	$msg = "";
	
	if (!file_exists($file)){
		if(!fopen($file, 'w')){
			$msg = "Não foi possível criar arquivo de log.";
			return $msg;
		}
	}

	if (file_exists($file) && (!is_writable($file))) {
		$msg = "Não foi possível gravar log #1.";
		return $msg;

	} else {
		if (!$handle = fopen($file, 'r+')) {
			$msg = "Não foi possível gravar log #2.";
			return $msg;
		} 
		
		//Le conteudo atual do log
		if((file_exists($file)) && (filesize($file)) > 0) {
			$mensagem .= fread($handle, filesize($file));
		}
		
		//grava o log no arquivo
		rewind($handle);
		if (fwrite($handle, $mensagem) === FALSE) {
			$msg = "Não foi possível gravar log #3.";
			return $msg;
		}
	
		fclose($handle);
		return "";
	}
}

function gravaLogBoleto($nomeArqUploaded, $mensagem){

	global $logDelimitador, $folder, $logFile;
	
	$mensagem = date('Y-m-d H:i:s') . " - " . $nomeArqUploaded . ": " . $mensagem . "\n";
	$mensagem .= $logDelimitador . "\n";
	
	return gravaLog($folder . $logFile, $mensagem);
	
}

function leLog($leLogCompleto){

	global $logDelimitador, $folder, $logFile;
	$buffer = '';
	$file = $folder . $logFile;
	
	if (file_exists($file)) {
		if ($handle = fopen($file, 'r')) {
		   	while (!feof($handle)) {
				$buffer_aux = fgets($handle);
				if($leLogCompleto){
					$buffer .= $buffer_aux;
				} else {
					if(trim($buffer_aux) == trim($logDelimitador)){
						break;
					} else {
						$buffer .= $buffer_aux;
					}
				}
			}
		}
		fclose($handle);
	}

	return $buffer;
}

	function processaBoleto_Caixa($carga, $data){
	
		$msg = "";
		$bol_banco = "104";
		$cargaAr = explode("\n", $carga);

		if(count($cargaAr) > 0){

			//Inicia transacao
			if($msg == ""){
				$sql = "BEGIN TRANSACTION ";
				$ret = pg_exec($GLOBALS['connid'], $sql);
				if(!$ret) $msg = "Erro ao iniciar transação.\n";
			}

			for($i=0; $i < count($cargaAr); $i++){

				//verifica se eh uma linha valida
				if(!is_numeric(substr($cargaAr[$i], 0, 4))) continue;
				
				//valida documento
  				$bol_documento = substr($cargaAr[$i], 0, 16);
				if(trim($bol_documento) == "") $msg = "Documento vázio: " . $cargaAr[$i] . ".\n";
				
				//valida valor
  				$bol_valor = substr($cargaAr[$i], 20, 12) . "." . substr($cargaAr[$i], 33, 2);
				if(!is_numeric($bol_valor)) $msg = "Valor inválido: " . $cargaAr[$i] . ".\n";
				else $bol_valor = 0 + $bol_valor;
				
				//Se houve erro na validacao, sai do loop
				if($msg != "") break;
				
				$sql = "insert into boletos_pendentes (bol_valor, bol_data, bol_banco, bol_documento, bol_importacao)";
				$sql .= "values(" . $bol_valor . ",'" . $data . "'," . $bol_banco . ",'" . $bol_documento . "','" . date('Y-m-d H:i:s') . "')";
				//echo $sql . "<br>";
				$ret = pg_exec($GLOBALS['connid'], $sql);
				if(!$ret){
					$msg = "Erro ao inserir registro: " . $cargaAr[$i] . ".\n";
					break;
				}

			}
			
			//Finaliza transacao
			if($msg == ""){
				$sql = "COMMIT TRANSACTION ";
				$ret = pg_exec($GLOBALS['connid'], $sql);
				if(!$ret) $msg = "Erro ao comitar transação.\n";
			} else {
				$sql = "ROLLBACK TRANSACTION ";
				$ret = pg_exec($GLOBALS['connid'], $sql);
				if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
			}
			
		}
				
		return $msg;
		
	}

	function processaBoleto_BancoBrasil($carga, $data){
		return "Função ainda não implementada";
	}

	function processaBoleto_Bradesco($carga, $data){
		return "Função ainda não implementada";
	}

// --------------------------------------------------------------------------------

//Log
$logFile = 'boletos_pendentes.log';
$logDelimitador = '#--------------------------------------------------------------';

//Diretorio repositorio
$folder =  $raiz_do_projeto . "log/boletos_pendentes/";
//desenvolvimento	
if(false) $folder = "D:\\Projetos\\Outros\\E-Prepag\\Sites\\Producao\\backoffice\\offweb\\boletos_pendentes\\";

	if(isset($Registrar) && $Registrar) {

		//Validacao
		$msg = "";
	
		//arquivo
		if($msg == ""){
			$fileSource = $_FILES['arquivo']['tmp_name']; 
			$fileDest = $folder . $_FILES['arquivo']['name']; 
		
			if (($fileSource == 'none') || ($fileSource == '' )) { 
				$msg = 'Nenhum arquivo fornecido.\n';
			} else {
				if (!move_uploaded_file($fileSource, $fileDest)) { 
						$msg = 'Não foi possivel copiar para o diretório destino.\n'; 
				} else {
					if((!file_exists($fileDest)) || (filesize($fileDest)) == 0) {
						$msg = 'Arquivo vazio ou inválido.\n';
					} 
				}
			}
		}
	
		//Banco
		if($msg == ""){
			if(trim($bco_id) == "")
				$msg = "O banco deve ser selecionado.\n";
			else if(!is_numeric($bco_id))
				$msg = "Banco inválido.\n";
		}
		
		//Data
		if($msg == ""){
			if(verifica_data($tf_data_dia . "/" . $tf_data_mes . "/" . $tf_data_ano) == 0)
				$msg = "A data dos boletos é inválida.\n";
			else
				$tf_data = $tf_data_ano . "-" . $tf_data_mes . "-" . $tf_data_dia;
		}
		
		if($msg == ""){
	
			//Abre arquivo e le conteudo
			$handle = fopen($fileDest, "r");
			$ta_depositos = fread($handle, filesize($fileDest));
			fclose($handle);

			if($bco_id == "001"){
				$msg = processaBoleto_BancoBrasil($ta_depositos, $tf_data);
			} else if($bco_id == "104"){
				$msg = processaBoleto_Caixa($ta_depositos, $tf_data);
			} else if($bco_id == "237"){
				$msg = processaBoleto_Bradesco($ta_depositos, $tf_data);
			}
			
			if($msg == "") $msg = "Boletos inseridos com sucesso.";
			$msg .= gravaLogBoleto($_FILES['arquivo']['name'], "Banco: '" . $bco_id. "' - Data: '" . $tf_data_dia . "/" . $tf_data_mes . "/" . $tf_data_ano . "'\n" . $msg);
			
		}
	}

	//Obtem os bancos disponiveis
	$sql = "select * from bancos_financeiros where bco_rpp = 1 ";
	$resbco = pg_exec($connid, $sql);

if (isset($VoltarPagina) && $VoltarPagina) {
?>
<meta http-equiv="refresh" content="0;URL=pendentes.php">
<?php } ?>
<script language="javascript">
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<script>
function fcnOnSubmit(){

	if(form1.bco_id.value==''){
		alert('Banco não selecionado');
		return false;
	} else	if(form1.arquivo.value==''){
		alert('Arquivo não especificado');
		return false;
	}
	
	return confirm('Deseja registrar estes Boletos ?');
	
}
</script>
<table class="txt-preto fontsize-pp table">
  <tr>
    <td>
		<form name="form1" method="post" action="" ENCTYPE="multipart/form-data" onSubmit="return fcnOnSubmit();">
            <table class="table">
			<tr>
				<td align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">Banco:&nbsp;</td>
				<td>
				  <select name="bco_id" class="combo_normal">
					<option value="" <?php if(!isset($bco_id) || $bco_id == "") echo "selected" ?>>Selecione</option>
					<?php while($pgbco = pg_fetch_array($resbco)) { ?>
					<option value="<?php echo $pgbco['bco_codigo'] ?>" <?php if(isset($bco_id) && $pgbco['bco_codigo'] == $bco_id) echo "selected" ?>><?php echo $pgbco['bco_nome'] ?></option>
					<?php } ?>
				  </select>
				</td>
				<td align="right">
					<input type="submit" name="Registrar" value="Registrar" class="btn btn-sm btn-info">
				</td>
			</tr>
			<tr bgcolor="#FFFFFF"><td colspan="3">&nbsp;</td></tr>
			<tr>
				<td align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">Arquivo:&nbsp;</td>
				<td colspan="2">
				  <input type="file" name="arquivo" size="30">
				</td>
			</tr>
			<tr>
				<td align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">Data dos Boletos:&nbsp;</td>
				<td colspan="2">
					<select name="tf_data_dia" class="form">
						<option value="" <?php if(!isset($tf_data_dia)) $tf_data_dia = null; if(!$tf_data_dia || $tf_data_dia == "") echo "selected" ?>>Dia</option>
						<?php if(!isset($tf_data_dia)) $tf_data_dia = null; for($i = 1; $i <= 31; $i++){?>
						<option value="<?php echo substr('00' . $i, -2) ?>" <?php if($tf_data_dia == substr('00' . $i, -2)) echo "selected" ?>><?php echo substr('00' . $i, -2) ?></option>
						<?php }?>
					</select>
					<select name="tf_data_mes" class="form">
						<option value="" <?php if(!isset($tf_data_mes)) $tf_data_mes = null; if(!$tf_data_mes || $tf_data_mes == "") echo "selected" ?>>Mês</option>
						<option value="01" <?php if($tf_data_mes == "01") echo "selected" ?>>Janeiro</option>
						<option value="02" <?php if($tf_data_mes == "02") echo "selected" ?>>Fevereiro</option>
						<option value="03" <?php if($tf_data_mes == "03") echo "selected" ?>>Março</option>
						<option value="04" <?php if($tf_data_mes == "04") echo "selected" ?>>Abril</option>
						<option value="05" <?php if($tf_data_mes == "05") echo "selected" ?>>Maio</option>
						<option value="06" <?php if($tf_data_mes == "06") echo "selected" ?>>Junho</option>
						<option value="07" <?php if($tf_data_mes == "07") echo "selected" ?>>Julho</option>
						<option value="08" <?php if($tf_data_mes == "08") echo "selected" ?>>Agosto</option>
						<option value="09" <?php if($tf_data_mes == "09") echo "selected" ?>>Setembro</option>
						<option value="10" <?php if($tf_data_mes == "10") echo "selected" ?>>Outubro</option>
						<option value="11" <?php if($tf_data_mes == "11") echo "selected" ?>>Novembro</option>
						<option value="12" <?php if($tf_data_mes == "12") echo "selected" ?>>Dezembro</option>
					</select>
					<select name="tf_data_ano" class="form">
						<option value="" <?php  if(!isset($tf_data_ano)) $tf_data_ano = null;  if(!$tf_data_ano || $tf_data_ano == "") echo "selected" ?>>Ano</option>
						<?php for($i = date('Y'); $i >= 2006; $i--){?>
						<option value="<?php echo $i ?>" <?php if($tf_data_ano == $i) echo "selected" ?>><?php echo $i ?></option>
						<?php }?>
					</select>
				</td>
			</tr>
			
			<?php if(isset($msg) && $msg != ""){ ?>
				<tr bgcolor="#FFFFFF"><td colspan="3" align="center"><font face="Arial, Helvetica, sans-serif" size="2" color="#FF0000"><?php echo str_replace("\n", "<br>", $msg) ?></font></td></tr>
			<?php } ?>
			<tr bgcolor="#FFFFFF"><td colspan="3">&nbsp;</td></tr>
			<tr bgcolor="#F0F0F0" height="30">
				<td colspan="2">
					<font color="#666666" size="2" face="Arial, Helvetica, sans-serif">
						<?php if(!isset($lc)) $lc = null; if($lc){ ?><b>Log completo</b>
						<?php } else {?><b>Último log</b>
						<?php } ?>
					</font>
				</td>
				<td align="right">
					<font color="#666666" size="2" face="Arial, Helvetica, sans-serif">
						<?php if($lc){ ?><a href='?'>Último log</a>
						<?php } else {?><a href='?lc=1'>Log completo</a>
						<?php } ?>
					</font>
				</td>
			</tr>
			<tr>
				<td colspan="3">
					<font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><br><?php echo str_replace($logDelimitador, "<hr>", str_replace("\n", "<br>", leLog($lc))) ?></font>
				</td>
			</tr>
			<tr bgcolor="#FFFFFF"><td colspan="3">&nbsp;</td></tr>
      	</table>
		</form>
<?php
	require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
   </td>
  </tr>
</table>
</body>
</html>
