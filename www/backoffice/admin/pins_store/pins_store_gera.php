<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once $raiz_do_projeto . "includes/main.php";
require_once $raiz_do_projeto . "includes/gamer/main.php";
require_once $raiz_do_projeto . "class/classIntegracaoPin.php";
require_once $raiz_do_projeto . "class/classIntegracaoPinCash.php";
require_once $raiz_do_projeto . "includes/inc_functions.php";
require_once $raiz_do_projeto . "class/classPinsStore.php";       

?>
<script language="javascript">
function GP_popupConfirmMsg(msg) { //v1.0
    if(document.getElementById('pin_operacao').value != "") {
        if(document.getElementById('pin_valor').value != "") {
            document.MM_returnValue = confirm(msg);
        }
        else {
            alert('É obrigatório informar o valor do PIN a ser gerado!');
            document.MM_returnValue = false;
        }            
    }
    else {
        alert('É obrigatório informar a Distribuidora!');
        document.MM_returnValue = false;
    }
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
<?php

// Configurado para naun expirar em 10 minutos de execução
// no DEV foi conseguido gerar pouco mais de 47000 PINs neste intervalo de tempo
set_time_limit(6000);

$operacao_array = VetorDistribuidoras();

$distributor_codigo = isset($_POST['pin_operacao'])      ? (int) $_POST['pin_operacao']		: null;
$qtde				= isset($_POST['pin_qtde'])          ? (int) $_POST['pin_qtde']			: null;
$pin_valor 			= isset($_POST['pin_valor'])         ? (int) $_POST['pin_valor']		: null;
$testeSubmit		= isset($_POST['BtnRegistrar'])      ? $_POST['BtnRegistrar']			: null;

$time_start_stats = getmicrotime();

//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
?>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="1">BackOffice - Money</a></li>
        <li class="active">Gerar PINs E-PREPAG</li>
    </ol>
</div>
<div class="col-md-12 txt-preto fontsize-pp">
<?php
include "pins_store_menu.php";
?>
<table class="table txt-preto fontsize-pp">
  <tr>
    <td height="22,5" valign="center" align="center"><b>Gerar PINs E-PREPAG</b></td>
  </tr>
  <tr>
    <td>
        <form name="form1" method="post">
        <table class="table fontsize-pp">
          <tr>
            <td width="30%"><b> C&oacute;digo da Distribuidora:<b></td>
            <td width="70%" colspan="2">
		        <select name="pin_operacao" id="pin_operacao" class="combo_normal">
					<option value=''<?php if(!$pin_operacao) echo "selected"?>>Selecione a Distribuidora</option>
			        <?php foreach ($operacao_array as $key => $value) { ?>
				    <option value=<?php echo "\"".$key.(($distributor_codigo==$key)?"\" selected":"\""); ?>><?php echo $value; ?></option>
					<?php } ?>
              </select>
            </td>
          </tr>
		  <tr>
            <td width="30%"><b> Valor do PIN: </b></td>
            <td width="70%" colspan="2">
				<div id='mostraValores'>
				<?php if(strlen($distributor_codigo)>0) { ?>
				<script language="javascript">
				carga_valor();
				</script>
				<?php
				}
				else {?>
				   <select name="pin_valor" id="pin_valor" class="combo_normal">
					<option value="" >Selecione a Distribuidora</option>
				   </select>
				<?php } ?>
			  </div>
              </font></td>
          </tr>
          <tr>
            <td width="30%"><b> Quantidade de PINs no Lote: </b></td>
            <td width="70%" colspan="2">
                    <input name="pin_qtde" id="pin_qtde" type="text" value="<?php echo $qtde; ?>" size="25" maxlength="25" onKeypress="return verifica();">
            </font></td>
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
           <tr>
            <td colspan="3" align="left">
      		<font color="blue" size="2"><hr>ATEN&Ccedil;&Atilde;O: Para carga no estoque de E-PREPAG Cash deve-se:
			<ol>
				<li>Gerar PINs selecionando o distribuidor EPREPAG;
				<li>Publicar os PINS gerados;
				<li>Ativar os PINs Publicados;
				<li>E finalmente gerar o arquivo com os PINs ativado.
			</ol>
			OBS.: Quando &eacute; acionado o programa gerador do arquivo contendo os PINs e &eacute; identificado que os PINs pertencem ao distribuidor EPREPAG, automaticamente ocorre carga na tabela de estoque e consequentemente n&atilde;o &eacute; gerado o arquivo com os PINs, como acontece para os demais distribuidores.<hr>
			</font>
            </td>
          </tr>
          <tr><td colspan="3"> <p>&nbsp;</p><?php
				if ($testeSubmit == 'Registrar') {
					if (!is_null($distributor_codigo)&& (!empty($distributor_codigo))) {
						if (!is_null($pin_valor)) { //&&(!empty($pin_valor))
							if (!is_null($qtde)&&$qtde<>0) {
								$ps = new Pins_Store();
								echo "<pre>";
									$ps->gera_lote(intval($distributor_codigo), intval($pin_valor), intval($qtde));
								echo "</pre>";
							} else {
								echo "&Eacute; necessario informar a Quantidade.<br>";
							}
						} else {
							echo "&Eacute; necessario selecionar um Valor.<br>";
						}
					} else {
						echo "&Eacute; necessario selecionar uma Distribuidora.<br>";
					}
                }
          ?></td></tr>
        </table>
      </form></td>
  </tr>
</table>
<table class="table">	
  <tr align="center"> 
	<td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td><td bgcolor="#FFFFFF" class="texto"><?php echo " Segundos: ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.')." "; ?></font></td><td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td>
  </tr>
</table>
</div>
</html>
<?php
if (!empty($pin_valor)) {
	$fp = fopen($raiz_do_projeto.'log/geracao_pin.txt', 'a+');
	$conteudo = PHP_EOL."====================Inicio EPP CASH============================".PHP_EOL;
	$conteudo .= " Segundos: ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.').PHP_EOL;
	$conteudo .= " Qtde de PINs : ".number_format($qtde, 0, '.', '.').PHP_EOL;
	$conteudo .= "=====================Fim EPP CASH==============================".PHP_EOL;
	fwrite($fp, $conteudo);
	fclose($fp);
}//end if (!empty($pin_valor))

require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";

?>