<?php 

//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
require_once "../../../includes/constantes.php";
header("Content-Type: text/html; charset=ISO-8859-1",true);
function isAjax() {return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));}
function block_direct_calling() {
    if(!isAjax()) {
           echo "Chamada não permitida<br>";
           die("Stop");
    }
}
block_direct_calling();

require_once $raiz_do_projeto . "includes/gamer/constantesPinEpp.php";

if ($_REQUEST['id'] == "POS"){
	echo '<select name="canal_pos" id="canal_pos" class="combo_normal" onChange="document.form1.submit()">\n';
	echo '<option value="">Todos</option>';
	echo '<option value="EPP" '.(($_REQUEST['canal_pos']=="EPP")?" selected":"").'>Rede Prepag</option>';
	foreach($DISTRIBUIDORAS_CANAIS as $key => $val) {
		if (substr($key,0,1)=='P') {
			echo '<option value="'.$key.'"'.(($_REQUEST['canal_pos']==$key)?" selected":"").'>'.$val . '</option>'; 
		}
	}
	echo '</select>';
}

?>
