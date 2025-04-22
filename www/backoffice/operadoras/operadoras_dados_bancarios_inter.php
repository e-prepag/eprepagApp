<?php
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";

$opr_banco_intermediario= isset($_POST['opr_banco_intermediario'])? $_POST['opr_banco_intermediario']: $opr_banco_intermediario;
$opr_codigo 			= isset($_POST['opr_codigo'])		    ? $_POST['opr_codigo']			: $opr_codigo;
$opr_ajax 				= isset($_POST['opr_ajax'])				? $_POST['opr_ajax']			: null;

if(!empty($opr_banco_intermediario)) {
	if(!empty($opr_codigo)&&!empty($opr_ajax)){
		$sql = "SELECT * FROM operadoras_banco_intermediario WHERE opr_codigo = $opr_codigo"; 
		//echo "$sql<br>";
		$rs_operadoras = SQLexecuteQuery($sql);
		if($rs_operadoras_row = pg_fetch_array($rs_operadoras)) {
			$obi_bic_code			= $rs_operadoras_row['obi_bic_code'];
			$obi_banco_nome			= $rs_operadoras_row['obi_banco_nome'];
			$obi_numero_conta		= $rs_operadoras_row['obi_numero_conta'];
		}
		else {
			$obi_bic_code			= null;
			$obi_banco_nome			= null;
			$obi_numero_conta		= null;
		}
	}
?>
<fieldset>
	<legend>Dados do Banco Intermedi&aacute;rio</legend>
	<table width="90%" border="0" align="center" cellpadding="1" cellspacing="0" style="font-size:10px">
	<tr>
		<td>SWIFT/ BIC Code:</td>
		<td><input name="obi_bic_code" type="text" id="obi_bic_code" size="15" maxlength="15" value="<?php echo $obi_bic_code; ?>" /> </td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>Bank Name:</td>
		<td><input name="obi_banco_nome" type="text" id="obi_banco_nome" size="40" maxlength="40" value="<?php echo $obi_banco_nome; ?>" /></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>Account Number: </td>
		<td width="80%">
			<input name="obi_numero_conta" type="text" id="obi_numero_conta" size="30" maxlength="30" value="<?php echo $obi_numero_conta; ?>" />&nbsp;
		</td>
		<td>&nbsp;</td>
	</tr>
	</table>
</fieldset>
<br>
<?php
}
?>
