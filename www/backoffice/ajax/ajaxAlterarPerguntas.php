<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

$tipos_perguntas = array(
				'U' => "Resposta &Uacute;nica",
				'M' => "Resposta com Multiplas Op&ccedil;&otilde;es",
				);

header("Content-Type: text/html; charset=ISO-8859-1",true);
function isAjax() {return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));}
function block_direct_calling() {
    if(!isAjax()) {
           echo "Chamada não permitida<br>";
           die("Stop");
    }
}
block_direct_calling();

//error_reporting(E_ALL);
ini_set("display_errors", 1);

if(empty(session_id())){
    //session não está inicada
    session_start();
}

$pagina_titulo = "E-prepag - Créditos para Games";
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."includes/inc_register_globals.php";	

$url = $_SERVER['HTTPS']=="on" ? "https://" : "http://";
$url .= $_SERVER['SERVER_NAME'];

$webstring = "https://".$_SERVER['SERVER_NAME'] . ":" . $_SERVER['SERVER_PORT'];
require_once $raiz_do_projeto."includes/access_functions.php";
require_once $raiz_do_projeto.'includes/configIP.php';
require_once $raiz_do_projeto.'includes/configuracaoBO.php';
require_once $raiz_do_projeto."db/connect.php";
require_once $raiz_do_projeto."db/ConnectionPDO.php";
require_once $raiz_do_projeto."includes/header.php";
require_once $raiz_do_projeto."includes/security.php";
require_once $raiz_do_projeto."includes/functions.php";

//echo "<pre>".print_r($_REQUEST,true)."</pre>";

$qlp_id				= isset($_REQUEST['qlp_id'])			? $_REQUEST['qlp_id']				: NULL;
$qlp_texto_alterar	= isset($_REQUEST['qlp_texto_alterar'])	? $_REQUEST['qlp_texto_alterar']	: NULL;
$qlp_tipo_alterar	= isset($_REQUEST['qlp_tipo_alterar'])	? $_REQUEST['qlp_tipo_alterar']		: NULL;
$qlp_ativo_alterar	= isset($_REQUEST['qlp_ativo_alterar'])	? $_REQUEST['qlp_ativo_alterar']	: NULL;
$atualizar			= isset($_REQUEST['atualizar'])			? $_REQUEST['atualizar']			: NULL;
$qlp_outros_alterar	= isset($_REQUEST['qlp_outros_alterar'])? $_REQUEST['qlp_outros_alterar']	: NULL;


?>
<script type="text/javascript">
function fecha() {
	$('#boxPopUpAlterar').html("");
	$('#boxPopUpAlterar').hide();
}

function validaPergunta()
{
	if (Trim(document.frmAlterar.qlp_texto_alterar.value) == "")
    {
        alert("Favor informar o Pergunta.");
        document.frmAlterar.qlp_texto_alterar.focus();
        return false;
    }
	return true;
}

function showValuesAlterar() {
  var str = $("form").serialize();
  return str;
}

//funcao que adiciona linha de Pergunta
function MM_reload_alterar(){
        //alert("TESTE AJAX"+showValuesAlterar());
		$(document).ready(function(){
			$.ajax({
				type: "POST",
				url: "/ajax/ajaxAlterarPerguntas.php",
				data: showValuesAlterar(),
				success: function(html){
					$('#teste').hide();
					$('#boxPopUpAlterar').hide();
					location.reload(true);
				},
				error: function(){
					alert('Erro Valor');
				}
			});
		});
}

</script>
<fieldset>
	<legend>Alterar a Pergunta</legend>
<table width="100%" border="0" align="center" cellpadding="1" cellspacing="0" style="font-size:10px">
   	<?php

	//Adicionado a nova Pergunta
	if($atualizar=="OK") {
		//fazer Update
		$sql = "update tb_questionarios_perguntas set qlp_texto='".utf8_decode($qlp_texto_alterar)."',qlp_ativo=".intval($qlp_ativo_alterar*1).",qlp_tipo='$qlp_tipo_alterar',qlp_outros=".intval($qlp_outros_alterar*1)." where qlp_id=".$qlp_id;
		//echo "<tr><td>".$sql.":</td></tr>";
		$rs_questionario_perguntas = SQLexecuteQuery($sql);
		if(!$rs_questionario_perguntas) {
			echo "Erro ao salvar informa&ccedil;&otilde;es da pergunta. ($sql)<br>";
		}
	}

	//buscar pelo id da pergunta 
	$sql = "select * from tb_questionarios_perguntas where qlp_id=".$qlp_id;
	//echo $sql."<br>";
	$rs_perguntas = SQLexecuteQuery($sql);
	$rs_perguntas_row = pg_fetch_array($rs_perguntas);
	?>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;Nova Pergunta: <br><input name="qlp_texto_alterar" type="text" id="qlp_texto_alterar" size="70" maxlength="512" value="<?php echo $rs_perguntas_row["qlp_texto"]; ?>"/></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;Tipo de Resposta: <br>
			 <select name="qlp_tipo_alterar" id="qlp_tipo_alterar" class="combo_normal">
			  <?php foreach ($tipos_perguntas as $key => $value) { ?>
			  <option value="<?php echo $key ?>" <?php if($key == $rs_perguntas_row["qlp_tipo"]) echo "selected" ?>><?php echo "(".$key.") ".$value ?></option>
			  <?php } ?>
			</select>
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;Ativo: <input name="qlp_ativo_alterar" type="checkbox" id="qlp_ativo_alterar" value="1" <?php if($rs_perguntas_row["qlp_ativo"]==1) echo "checked";?>/>
		</td>
	</tr>
	<tr>
		<td>&nbsp;Op&ccedil;&atilde;o Outros: <select name="qlp_outros_alterar" id="qlp_outros_alterar" class="combo_normal">
			  <option value="1" <?php if($rs_perguntas_row["qlp_outros"] == '1') echo "selected" ?>>Sim</option>
			  <option value="0" <?php if($rs_perguntas_row["qlp_outros"] == '0') echo "selected" ?>>N&atilde;o</option>
			</select>
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
</table>
<input type="hidden" name="qlp_id" id="qlp_id" value="<?php echo $qlp_id;?>"/>
<input type="hidden" name="atualizar" id="atualizar" value="OK"/>
<img src="/images/finalizar_edicao.gif" width="67" height="22" border="0" alt="Alterar Pergunta" title="Alterar Pergunta" onclick="javascript:MM_reload_alterar();" style="cursor:pointer;cursor:hand;">
</fieldset>
