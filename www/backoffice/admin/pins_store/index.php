<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once $raiz_do_projeto . "includes/main.php";
require_once $raiz_do_projeto . "includes/gamer/main.php";
require_once $raiz_do_projeto . "class/classIntegracaoPin.php";
require_once $raiz_do_projeto . "class/classIntegracaoPinCash.php";
require_once $raiz_do_projeto . "includes/inc_functions.php";
require_once $raiz_do_projeto . "class/classPinsStore.php";       

$operacao_array			= VetorDistribuidoras();
$distributor_codigo 	= isset($_POST['pin_operacao'])      ? $_POST['pin_operacao']		: null;
$valor					= isset($_POST['pin_valor'])         ? $_POST['pin_valor']			: null;
?>
<script language="javascript">
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}
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
	<?php
		if($distributor_codigo) {
	?>
			$.ajax({
				type: "POST",
				url: "ajaxValorComPesquisaVendas.php",
				data: "id=<?php echo $distributor_codigo."&valor=".$valor; ?>",
				success: function(html){
					$('#mostraValores').html(html);
				},
				error: function(){
					alert('erro valor');
				}
			});

	<?php
		}
	?>
});
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><?php echo $sistema->item->getDescricao() ; ?></li>
    </ol>
</div>
<div class="col-md-12 txt-preto fontsize-pp">
<?php
include "pins_store_menu.php";
?>

<form name="form1" method="post" action="<?php echo $php_self ?>">
<table class="txt-preto fontsize-pp table top20">
	<tr>
        <td width="20%"><b>C&oacute;digo da Distribuidora: </b></td>
		<td width="80%">
			<select name="pin_operacao" id="pin_operacao" class="combo_normal">
			<option value=''<?php if(!$pin_operacao) echo "selected"?>>Todas as Distribuidoras</option>
			<?php foreach ($operacao_array as $key => $value) { ?>
			<option value="<?php echo $key ?>"<?php if($pin_operacao==$key) echo "selected"?>><?php echo $value; ?></option>
			<?php } ?>
		  </select>
        </td>
	</tr>
	<tr>
		<td width="20%"><b> Valor do PIN: </b></td>
		<td width="80%">
			<div id='mostraValores'>
			   <select name="pin_valor" id="pin_valor" class="combo_normal">
				<option value="" >Selecione a Distribuidora</option>
			   </select>
		  </div>
		  </font></td>
	</tr>
	<tr>
            <td colspan="2" align="center"><input name="Filtrar" type="submit" id="Filtrar" value="Filtrar Estoque" class="btn btn-sm btn-info" onClick="GP_popupConfirmMsg('Deseja filtar estoque com estes parametros?');return reload();">
            </td>
    </tr>
	<tr>
        <td colspan="2" align="center"><div class="top20"><?php $strTable = displayEstoque_POS($distributor_codigo,$valor); echo str_replace("<table", "<table class=\"table txt-preto\"", $strTable) ?></div></td>
	</tr>
</table>
</form>
</div>
</body>
</html>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
