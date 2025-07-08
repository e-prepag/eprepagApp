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

if (isset($_REQUEST['id']) && intval($_REQUEST['id']) > 0) {
    $opr_codigo = intval($_REQUEST['id']);

    $sql = "SELECT valor FROM operadoras_valores 
            WHERE opr_codigo = $opr_codigo 
            ORDER BY valor";
    $rs_oprPins = SQLexecuteQuery($sql);
}

if ($rs_oprPins) {
    echo '<select name="pin_valor" id="pin_valor" class="combo_normal">'.PHP_EOL;
    echo '<option value="">Selecione o Valor</option>';

    $num_rows = pg_num_rows($rs_oprPins);
    for ($i = 0; $i < $num_rows; $i++) {
        $row = pg_fetch_array($rs_oprPins, $i);
        $valor = $row['valor'];

        if (!empty($valor) && $valor != "0.00") {
            $valorFormatado = number_format($valor, 2, ',', '.');
            $selected = (isset($_REQUEST['valor']) && floatval(str_replace(',', '.', $_REQUEST['valor'])) == floatval($valor)) ? ' selected' : '';
            echo '<option value="'.$valorFormatado.'"'.$selected.'>'.$valorFormatado.'</option>'.PHP_EOL;
        }
    }

    echo '</select>';
}
?>
