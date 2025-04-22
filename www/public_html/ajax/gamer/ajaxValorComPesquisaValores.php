<?php 

//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

function isAjax() {return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));}
function block_direct_calling() {
    if(!isAjax()) {
           echo "Chamada não permitida<br>";
           die("Stop");
    }
}
block_direct_calling();

require_once "../../../includes/constantes.php";
require_once $raiz_do_projeto."public_html/sys/includes/topo_sys_inc.php";
require_once $raiz_do_projeto."class/classPinsStore.php";

if ($_REQUEST['id'] > 0){
	$sql = "SELECT pin_valor FROM pins WHERE opr_codigo = " . intval($_REQUEST['id']) . " GROUP BY pin_valor ORDER BY pin_valor;";
//echo "$sql<br>";
	$rs_oprPins = SQLexecuteQuery($sql);
}

if($rs_oprPins){
	echo '<select name="pin_valor" id="pin_valor" class="combo_normal">\n';
	echo '<option value="">Selecione o Valor</option>';
	while($rs_oprPins_row = pg_fetch_array($rs_oprPins)){ 
      echo '<option value="'.$rs_oprPins_row['pin_valor'].'"'.((intval($_REQUEST['valor'])==$rs_oprPins_row['pin_valor'])?" selected":"").'>'.$rs_oprPins_row['pin_valor'] . ',00</option>'; 
	}
	echo '</select>';
}
else {
	echo '<select name="pin_valor" id="pin_valor" class="combo_normal">\n';
	echo '<option value="">Selecione a Operadora</option>';
	echo '</select>';
}
?>
