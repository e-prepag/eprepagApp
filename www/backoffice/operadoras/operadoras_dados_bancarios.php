<?php
if(!isset($opr_internacional))
    $opr_internacional = '';

if(!isset($opr_codigo))
    $opr_codigo = null;

$opr_internacional 		= isset($_POST['opr_internacional'])    ? $_POST['opr_internacional']	: $opr_internacional;
$opr_codigo 			= isset($_POST['opr_codigo'])		    ? $_POST['opr_codigo']			: $opr_codigo;
$opr_ajax 				= isset($_POST['opr_ajax'])				? $_POST['opr_ajax']			: null;

$msg_banco = "";
if(!empty($opr_codigo)&&!empty($opr_ajax)){
	require_once '../../includes/constantes.php';
        require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";
	$sql = "SELECT * FROM operadoras WHERE opr_codigo = $opr_codigo"; 
	$rs_operadoras = SQLexecuteQuery($sql);
	if(!($rs_operadoras_row = pg_fetch_array($rs_operadoras))) {
		$msg_banco .= "Erro ao consultar informa&ccedil;&otilde;es da Operadora. ($sql)<br>";
	}
	else {
		$opr_nome 				= $rs_operadoras_row['opr_nome'];
		$opr_numero_conta		= $rs_operadoras_row['opr_numero_conta'];
		$opr_tipo_conta			= $rs_operadoras_row['opr_tipo_conta'];
		$opr_numero_roteamento	= $rs_operadoras_row['opr_numero_roteamento'];
		$opr_banco_nome			= $rs_operadoras_row['opr_banco_nome'];
		$opr_banco_endereco		= $rs_operadoras_row['opr_banco_endereco'];
		$opr_banco_cidade		= $rs_operadoras_row['opr_banco_cidade'];
		$opr_banco_telefone		= $rs_operadoras_row['opr_banco_telefone'];
		$opr_moeda_corrente		= $rs_operadoras_row['opr_moeda_corrente'];
		$opr_iban				= $rs_operadoras_row['opr_iban'];
		$opr_bic_code			= $rs_operadoras_row['opr_bic_code'];
		$opr_numero_contrato	= $rs_operadoras_row['opr_numero_contrato'];
	}
}
echo $msg_banco;

if(empty($opr_internacional)) {
?>
<table class="table txt-preto fontsize-pp">
	<tr>
		<td>N&uacute;mero da Conta: </td>
		<td width="80%">
			<input name="opr_numero_conta" type="text" id="opr_numero_conta" size="30" maxlength="30" value="<?php if(isset($opr_numero_conta)) echo $opr_numero_conta; ?>" />&nbsp;Utilizar neste mesmo campo d&iacute;gitos separados por h&iacute;fen, espa&ccedil;o, ponto, etc.
		</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>Tipo de Conta: </td>
		<td><input name="opr_tipo_conta" type="text" id="opr_tipo_conta" size="30" maxlength="30" value="<?php if(isset($opr_tipo_conta)) echo $opr_tipo_conta; ?>" /></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>N&uacute;mero da Ag&ecirc;ncia: </td>
		<td><input name="opr_numero_roteamento" type="text" id="opr_numero_roteamento" size="20" maxlength="20" value="<?php if(isset($opr_numero_roteamento)) echo $opr_numero_roteamento; ?>" /></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>Nome do Banco:</td>
		<td><input name="opr_banco_nome" type="text" id="opr_banco_nome" size="40" maxlength="40" value="<?php if(isset($opr_banco_nome)) echo $opr_banco_nome; ?>" /></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>Endere&ccedil;o do Banco:</td>
		<td><input name="opr_banco_endereco" type="text" id="opr_banco_endereco" size="40" maxlength="70" value="<?php if(isset($opr_banco_endereco)) echo $opr_banco_endereco; ?>"/>&nbsp;Informar o endere&ccedil;o completo contendo todas as informa&ccedil;&otilde;es.</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>Cidade:</td>
		<td><input name="opr_banco_cidade" type="text" id="opr_banco_cidade" size="20" maxlength="20" value="<?php if(isset($opr_banco_cidade)) echo $opr_banco_cidade; ?>" /> </td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>Telefone:</td>
		<td><input name="opr_banco_telefone" type="text" id="opr_banco_telefone" size="20" maxlength="20" value="<?php if(isset($opr_banco_telefone)) echo $opr_banco_telefone; ?>" /> </td>
		<td>&nbsp;</td>
	</tr>
</table>
<input type="hidden" name="opr_moeda_corrente" id="opr_moeda_corrente" value="Real" />
<input type="hidden" name="opr_iban" id="opr_iban" value="" />
<input type="hidden" name="opr_bic_code" id="opr_bic_code" value="" />
<input type="hidden" name="opr_numero_contrato" id="opr_numero_contrato" value="" />
<script type="text/javascript">
	$('#dados_inter').html('');
</script>
<?php
}
else {
?>
<table class="table txt-preto fontsize-pp">
	<tr>
		<td>Account Number: </td>
		<td width="80%">
			<input name="opr_numero_conta" type="text" id="opr_numero_conta" size="30" maxlength="30" value="<?php if(isset($opr_numero_conta)) echo $opr_numero_conta; ?>" />&nbsp;
		</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>Account Type: </td>
		<td><input name="opr_tipo_conta" type="text" id="opr_tipo_conta" size="30" maxlength="30" value="<?php if(isset($opr_tipo_conta)) echo $opr_tipo_conta; ?>" /></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>Routing Number: </td>
		<td><input name="opr_numero_roteamento" type="text" id="opr_numero_roteamento" size="20" maxlength="20" value="<?php if(isset($opr_numero_roteamento)) echo $opr_numero_roteamento; ?>" /></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>Bank Name:</td>
		<td><input name="opr_banco_nome" type="text" id="opr_banco_nome" size="40" maxlength="40" value="<?php if(isset($opr_banco_nome)) echo $opr_banco_nome; ?>" /></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>Bank Address:</td>
		<td><input name="opr_banco_endereco" type="text" id="opr_banco_endereco" size="40" maxlength="70" value="<?php if(isset($opr_banco_endereco)) echo $opr_banco_endereco; ?>"/>&nbsp;Use Full Address.</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>Bank City:</td>
		<td><input name="opr_banco_cidade" type="text" id="opr_banco_cidade" size="20" maxlength="20" value="<?php if(isset($opr_banco_cidade)) echo $opr_banco_cidade; ?>" /> </td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>Bank Phone Number:</td>
		<td><input name="opr_banco_telefone" type="text" id="opr_banco_telefone" size="20" maxlength="20" value="<?php if(isset($opr_banco_telefone)) echo $opr_banco_telefone; ?>" /> </td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>Currency:</td>
		<td><input name="opr_moeda_corrente" type="text" id="opr_moeda_corrente" size="10" maxlength="10" value="<?php if(isset($opr_moeda_corrente)) echo $opr_moeda_corrente; ?>" /> </td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>IBAN:</td>
		<td><input name="opr_iban" type="text" id="opr_iban" size="30" maxlength="30" value="<?php if(isset($opr_iban)) echo $opr_iban; ?>" /> </td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>SWIFT/ BIC Code:</td>
		<td><input name="opr_bic_code" type="text" id="opr_bic_code" size="15" maxlength="15" value="<?php if(isset($opr_bic_code)) echo $opr_bic_code; ?>" /> </td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>Agreement Number:</td>
		<td><input name="opr_numero_contrato" type="text" id="opr_numero_contrato" size="15" maxlength="15" value="<?php if(isset($opr_numero_contrato)) echo $opr_numero_contrato; ?>" /> </td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>Intermediate Bank: </td>
		<td><input name="opr_banco_intermediario" type="checkbox" id="opr_banco_intermediario" value="1" <?php if(!empty($opr_banco_intermediario)) echo "checked" ?> onclick="carga_dados_inter();"/> Sim
		</td>
		<td>&nbsp;</td>
	</tr>
</table>
<?php
}
?>
