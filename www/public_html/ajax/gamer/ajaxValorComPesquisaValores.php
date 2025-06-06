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
require_once __DIR__ . "/../../../db/connect.php"; 
require_once __DIR__ . "/../../../db/ConnectionPDO.php";

$pdo = ConnectionPDO::getConnection()->getLink();

$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$valorSelecionado = isset($_REQUEST['valor']) ? intval($_REQUEST['valor']) : null;

if ($id > 0) {
    // Prepara SQL
    $sql = "SELECT pin_valor FROM pins WHERE opr_codigo = :id GROUP BY pin_valor ORDER BY pin_valor;";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $rs_oprPins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $rs_oprPins = false;
    }
}

if (!empty($rs_oprPins)) {
    echo '<select name="pin_valor" id="pin_valor" class="combo_normal">' . "\n";
    echo '<option value="">Selecione o Valor</option>' . "\n";

    foreach ($rs_oprPins as $row) {
        $pin_valor = (int)$row['pin_valor'];
        $selected = ($valorSelecionado === $pin_valor) ? ' selected' : '';
        echo '<option value="' . $pin_valor . '"' . $selected . '>' . $pin_valor . ',00</option>' . "\n";
    }

    echo '</select>' . "\n";
}
else {
	echo '<select name="pin_valor" id="pin_valor" class="combo_normal">\n';
	echo '<option value="">Selecione a Operadora</option>';
	echo '</select>';
}
?>
