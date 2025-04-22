<?php
// Configurado para naun expirar em 10 minutos de execução
// no DEV foi conseguido gerar pouco mais de 47000 PINs neste intervalo de tempo
set_time_limit(600);
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once $raiz_do_projeto . "includes/gamer/main.php";
require_once $raiz_do_projeto . "class/classPinsPublishers.php";

$operacao_array = VetorOperadoras();

$opr_codigo = isset($_POST['pin_operacao'])      ? (int) $_POST['pin_operacao']		: null;
$qtde       = isset($_POST['pin_qtde'])          ? (int) $_POST['pin_qtde']			: null;
$pin_valor 	= isset($_POST['pin_valor'])         ? $_POST['pin_valor']		: null;
$tf_v_formato= isset($_POST['tf_v_formato'])     ? (int) $_POST['tf_v_formato']		: null;
$testeSubmit= isset($_POST['BtnRegistrar'])      ? $_POST['BtnRegistrar']			: null;
?>
<script language="javascript">
function GP_popupConfirmMsg(msg) { //v2.0
	if (document.getElementById('pin_valor').value == "")
	{
		alert('Deve ser informado o valor do PIN a ser gerado!');
		document.MM_returnValue = false;
	}
	else document.MM_returnValue = confirm(msg);
}
function verifica()
{
    if ((event.keyCode<47)||(event.keyCode>58)){
          alert("Somente numeros sao permitidos");
          event.returnValue = false;
    }
}

// Carrega valores reflesh 
function carga_valor(){
		$(document).ready(function(){
			$.ajax({
				type: "POST",
				url: "ajaxValorComPesquisaVendas.php",
				data: "id="+document.getElementById('pin_operacao').value,
				success: function(html){
					$('#mostraValores').html(html);
				},
				error: function(){
					alert('erro valor');
				}
			});
		});
}
//Carga Valor onChange
$(document).ready(function () {
		$('#pin_operacao').change(function(){
			var id = $(this).val();
			$.ajax({
				type: "POST",
				url: "ajaxValorComPesquisaVendas.php",
				data: "id="+id,
				success: function(html){
					$('#mostraValores').html(html);
				},
				error: function(){
					alert('erro valor');
				}
			});
		});
});
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><?php echo $sistema->item->getDescricao(); ?></li>
    </ol>
</div>
<table class="table txt-preto fontsize-pp">
  <tr>
    <td>
        <form name="form1" method="post" action="<?php echo $php_self ?>">
        <table class="table txt-preto fontsize-pp">
          <tr>
            <td width="30%" class="bg-azul-claro txt-branco"><b> C&oacute;digo da Operadora:<b></td>
            <td width="70%" colspan="2" bgcolor="#f5f5fb">
		        <select name="pin_operacao" id="pin_operacao" class="combo_normal">
					<option value=''<?php if(!$pin_operacao) echo "selected"?>>Selecione a operadora</option>
			        <?php foreach ($operacao_array as $key => $value) { ?>
				    <option value=<?php echo "\"".$key.(($opr_codigo==$key)?"\" selected":"\""); ?>><?php echo $value; ?></option>
					<?php } ?>
              </select>
              </td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td width="30%" class="bg-azul-claro txt-branco"><b> Valor do PIN: </b></td>
            <td width="40%" bgcolor="#f5f5fb">
				<div id='mostraValores'>
				<?php if(strlen($opr_codigo)>0) { ?>
				<script language="javascript">
				carga_valor();
				</script>
				<?php
				}
				else {?>
				   <select name="pin_valor" id="pin_valor" class="combo_normal">
					<option value="" >Selecione a Operadora</option>
				   </select>
				<?php } ?>
			  </div>
            </td>
			
			<td width="30%" class="hide" id="isPdvTd">
				<input type="checkbox" name="ispdv" id="ispdv"/>
				<span>É PDV ?</span>
			</td>
          </tr>
          <tr bgcolor="#F5F5FB">
            <td width="30%" class="bg-azul-claro txt-branco"><b> Quantidade de PINs no Lote: </b></td>
            <td width="70%" colspan="2" bgcolor="#f5f5fb">
                    <input name="pin_qtde" id="pin_qtde" type="text" value="<?php echo $qtde; ?>" size="25" maxlength="25" onKeypress="return verifica();">
            </td>
          </tr>
          <?php if($msg != ""){ ?>
          <tr bgcolor="#FFFFFF">
              <td colspan="3"><font color="red" size="2"><?php echo str_replace("\n", "<br>", $msg)?></font></td>
          </tr>
          <?php } ?>
          <tr>
            <td colspan="3" align="center">
      		<input name="BtnRegistrar" type="submit" id="BtnRegistrar" value="Registrar" class="btn btn-sm btn-info" onClick="GP_popupConfirmMsg('Deseja Gerar PINs neste Formato?');return document.MM_returnValue">
            </td>
          </tr>
          <tr><td colspan="3"> <p>&nbsp;</p><?php
				if ($testeSubmit == 'Registrar') {
					if (!is_null($opr_codigo)&& (!empty($opr_codigo))) {
						if (!is_null($pin_valor)) { //&&(!empty($pin_valor))
							if (!is_null($qtde)&&$qtde<>0) {
								$ps = new Pins_Publishers();
								
								// tirando ponto fluante valofe
								if(intval($opr_codigo) == 143){
									$pin_valor = str_replace(".", "", $pin_valor);
								}

								echo "<pre>";
								$ps->gera_lote(intval($opr_codigo), floatval($pin_valor), intval($qtde), null, isset($_POST['ispdv']) && $_POST['ispdv'] == 'on' ? true : null);
								echo "</pre>";
							} else {
								echo "&Eacute; necessario informar a Quantidade.<br>";
							}
						} else {
							echo "&Eacute; necessario selecionar um Valor.<br>";
						}
					} else {
						echo "&Eacute; necessario selecionar uma Operadora.<br>";
					}
                }
          ?></td></tr>
		  <tr><td colspan="3"> <?php require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php"; ?></td></tr>
        </table>
      </form></td>
  </tr>
</table>

<script>

	$("#pin_operacao").change(function(e)  {
		console.log($("#pin_operacao").val() == 124);
		if($("#pin_operacao").val() == 124) {
			console.log("opa");
			$("#isPdvTd").toggleClass('hide');  
		}else {
			console.log("hey");
			$("#isPdvTd").addClass('hide');
		}
	});
</script>
<style>
.hide {
	display: none !important;
}
</style>
</html>