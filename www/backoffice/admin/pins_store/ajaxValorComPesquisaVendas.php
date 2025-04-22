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
require_once $raiz_do_projeto . "includes/gamer/main.php";
require_once $raiz_do_projeto . "class/classIntegracaoPin.php";
require_once $raiz_do_projeto . "class/classIntegracaoPinCash.php";
require_once $raiz_do_projeto . "includes/inc_functions.php";
require_once $raiz_do_projeto . "class/classPinsStore.php";       

if ($_REQUEST['id'] > 0){
	echo '<select name="pin_valor" id="pin_valor" class="combo_normal">\n';
	echo '<option value="">Selecione o Valor</option>';
	foreach($DISTRIBUIDORAS[$_REQUEST['id']]['distributor_valores'] as $key => $val) {
	  echo '<option value="'.$key.'"'.((intval($_REQUEST['valor'])==$key)?" selected":"").'>'.$val . '</option>'; 
	}
	echo '</select>';
}

?>
