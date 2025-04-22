<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once $raiz_do_projeto . "includes/gamer/main.php";
require_once $raiz_do_projeto . "class/classPinsCard.php";

$publisher_array	= VetorOperadorasCard();
$operacao_array		= VetorDistribuidorasCard();
$distributor_codigo 	= isset($_POST['pin_operacao'])      ? $_POST['pin_operacao']		: null;
$valor			= isset($_POST['pin_valor'])         ? $_POST['pin_valor']		: null;
?>

<script language="javascript">
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}
$(document).ready(function () {
                $('#pin_operacao').change(function(){
                        var id = $(this).val();
                        var opr_codigo = $('#opr_codigo').val();
                        $.ajax({
                                type: "POST",
                                url: "ajaxValor.php",
                                data: "id="+id+"&opr_codigo="+opr_codigo,
                                success: function(html){
                                        $('#mostraValores').html(html);
                                },
                                error: function(){
                                        alert('erro valor');
                                }
                        });
                });
                $('#opr_codigo').change(function(){
                        var opr_codigo = $(this).val();
                        var id = $('#pin_operacao').val();
                        $.ajax({
                                type: "POST",
                                url: "ajaxValor.php",
                                data: "id="+id+"&opr_codigo="+opr_codigo,
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
                                url: "ajaxValor.php",
                                data: "id="+<?php echo $distributor_codigo; ?>+"&opr_codigo="+<?php echo $opr_codigo; ?>+"&pin_valor="+<?php echo $valor; ?>,
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
        <li class="active"><?php echo $sistema->item->getDescricao(); ?></li>
    </ol>
</div>
<div class="col-md-12 txt-preto fontsize-pp">
<?php
include "pins_card_menu.php";
?>
<form name="form1" method="post" action="<?php echo $php_self ?>">
    <table class="table txt-preto fontsize-pp">
        <tr>
                <td width="20%"  class="bg-azul-claro"><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><b> Publisher: </b></font></td>
                <td width="80%" colspan="2" bgcolor="#f5f5fb"><font color="#666666">
                        <select name="opr_codigo" id="opr_codigo" class="combo_normal">
                        <option value=''<?php if(!$opr_codigo) echo "selected"?>>Todas as Publishers</option>
                        <?php foreach ($publisher_array as $key => $value) { ?>
                        <option value="<?php echo $key ?>"<?php if($opr_codigo==$key) echo "selected"?>><?php echo $value; ?></option>
                        <?php } ?>
                  </select>
                  </font></td>
        </tr>
        <tr>
                <td width="20%"  class="bg-azul-claro"><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><b> Distribuidora: </b></font></td>
                <td width="80%" colspan="2" bgcolor="#f5f5fb"><font color="#666666">
                        <select name="pin_operacao" id="pin_operacao" class="combo_normal">
                        <option value=''<?php if(!$pin_operacao) echo "selected"?>>Todas as Distribuidoras</option>
                        <?php foreach ($operacao_array as $key => $value) { ?>
                        <option value="<?php echo $key ?>"<?php if($pin_operacao==$key) echo "selected"?>><?php echo $value; ?></option>
                        <?php } ?>
                  </select>
                  </font></td>
        </tr>
        <tr bgcolor="#F5F5FB">
                <td width="20%"  class="bg-azul-claro"><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><b> Valor do PIN: </b></font></td>
                <td width="80%" colspan="2" bgcolor="#f5f5fb"><font color="#666666">
                        <div id='mostraValores'>
                           <select name="pin_valor" id="pin_valor" class="combo_normal">
                                <option value="" >Selecione a Distribuidora</option>
                           </select>
                  </div>
                  </font></td>
        </tr>
        <tr>
            <td colspan="3" align="center"><input name="Filtrar" type="submit" id="Filtrar" value="Filtrar Estoque" class="btn btn-sm btn-info" onClick="GP_popupConfirmMsg('Deseja filtar estoque com estes parametros?');return reload();">
            </td>
    </tr>
</table>
<table class="table txt-preto fontsize-pp">
    <tr>
        <td colspan="3" align="center"><font class="texto"><?php $strTable = displayEstoque_CARDS($opr_codigo, $distributor_codigo, $valor); echo str_replace("<table ","<table class='table txt-preto fontsize-pp'",$strTable); ?></font></td>
    </tr>
</table>
</form>
</div>
</body>
</html>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
