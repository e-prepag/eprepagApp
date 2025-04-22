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

if ($_REQUEST['id'] > 0 AND $_REQUEST['opr_codigo'] > 0){
	echo '<select name="pin_valor" id="pin_valor" class="combo_normal">\n';
	echo '<option value="">Selecione o Valor</option>';
	 $sql = "SELECT pcdv_valor from pins_card_distribuidoras_valores where opr_codigo = ".$_REQUEST['opr_codigo']." and pcd_id_distribuidor = ".$_REQUEST['id'].";";
        $rs_operadoras = SQLexecuteQuery($sql);
        while ($rs_operadoras_row = pg_fetch_array($rs_operadoras)) {
	  echo '<option value="'.$rs_operadoras_row['pcdv_valor'].'"'.((intval($_REQUEST['pin_valor'])==$rs_operadoras_row['pcdv_valor'])?" selected":"").'>R$ '.$rs_operadoras_row['pcdv_valor']. '</option>'; 
	}
	echo '</select>';
}

?>

