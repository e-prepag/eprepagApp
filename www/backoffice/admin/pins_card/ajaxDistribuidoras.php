<?php 
header("Content-Type: text/html; charset=ISO-8859-1",true);
function isAjax() {return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));}
function block_direct_calling() {
    if(!isAjax()) {
           echo "Chamada não permitida<br>";
           die("Stop");
    }
}
block_direct_calling();

require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "includes/main.php";
require_once $raiz_do_projeto . "includes/gamer/constantesPinEpp.php";

if ($_REQUEST['id'] > 0){
	echo '<select name="pin_operacao" id="pin_operacao" class="combo_normal" onChange="carga_valor();">';
	echo '<option value="">Selecione a Distribuidora</option>';
        $sql = "SELECT pcd_id_distribuidor from pins_card_distribuidoras where opr_codigo = ".$_REQUEST['id'].";";
        $rs_operadoras = SQLexecuteQuery($sql);
        while ($rs_operadoras_row = pg_fetch_array($rs_operadoras)) {
	  echo '<option value="'.$rs_operadoras_row['pcd_id_distribuidor'].'"'.((intval($_REQUEST['pin_operacao'])==$rs_operadoras_row['pcd_id_distribuidor'])?" selected":"").'>'.$GLOBALS['DISTRIBUIDORAS_CARTOES'][$rs_operadoras_row['pcd_id_distribuidor']] . '</option>'; 
	}
	echo '</select>';
}
?>
