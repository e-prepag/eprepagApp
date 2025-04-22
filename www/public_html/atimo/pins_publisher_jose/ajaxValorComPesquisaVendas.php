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
	$sql = "SELECT opr_valor1,opr_valor2,opr_valor3,opr_valor4,opr_valor5,opr_valor6,opr_valor7,opr_valor8,opr_valor9,opr_valor10,opr_valor11,opr_valor12,opr_valor13,opr_valor14,opr_valor15,opr_valor16,opr_valor17,opr_valor18, opr_valor19,opr_valor20, opr_valor21 FROM operadoras WHERE opr_codigo = " . intval($_REQUEST['id']) . ";";
//echo "$sql<br>";
	$rs_oprPins = SQLexecuteQuery($sql);
}

if($rs_oprPins){
	echo '<select name="pin_valor" id="pin_valor" class="combo_normal">'.PHP_EOL;
	echo '<option value="">Selecione o Valor</option>';
	if($rs_oprPins_row = pg_fetch_array($rs_oprPins)){ 
		for ($i = 1; $i <= 21; $i++) {
			//echo $i." : ".$rs_oprPins_row['opr_valor'.$i]."<br>";
			if (!empty($rs_oprPins_row['opr_valor'.$i])&&$rs_oprPins_row['opr_valor'.$i]!="0.00")
				echo '<option value="'.number_format($rs_oprPins_row['opr_valor'.$i], 2, ',', '.').'"'.((intval($_REQUEST['valor'])==number_format($rs_oprPins_row['opr_valor'.$i], 0, ',', '.'))?" selected":"").'>'.number_format($rs_oprPins_row['opr_valor'.$i], 2, ',', '.') . '</option>'; 
		}
	}
	echo '</select>';
}
?>
