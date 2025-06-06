<?php
require_once "../../../includes/constantes.php";
require_once $raiz_do_projeto."public_html/sys/includes/topo_sys_inc.php";
require "/www/db/connect.php";
require "/www/db/ConnectionPDO.php";

date_default_timezone_set('America/Fortaleza');

$pdo = ConnectionPDO::getConnection()->getLink();

$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$tf_pins = isset($tf_pins) ? $tf_pins : null;

if ($id > 0) {
    $sql = "SELECT pin_valor FROM pins WHERE opr_codigo = :id GROUP BY pin_valor ORDER BY pin_valor";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $rs_oprPins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $rs_oprPins = false;
    }
}

if (!empty($rs_oprPins)) {
    foreach ($rs_oprPins as $row) {
        $pin_valor = (int)$row['pin_valor'];
        $checked = '';

        if (isset($tf_pins) && is_array($tf_pins)) {
            if (in_array($pin_valor, $tf_pins)) {
                $checked = ' checked';
            } elseif ($pin_valor == $tf_pins) {
                $checked = ' checked';
            }
        }

        echo '<nobr>';
        echo '<input type="checkbox" id="tf_pins[]" name="tf_pins[]" value="' . $pin_valor . '"' . $checked . '>';
        echo $pin_valor . ',00';
        echo '</nobr>' . "\n";
    }
}
?>