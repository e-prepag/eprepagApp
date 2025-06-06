<?php
require_once "../../../includes/constantes.php";
require_once $raiz_do_projeto . "public_html/sys/includes/topo_sys_inc.php";
require "/www/db/connect.php";
require "/www/db/ConnectionPDO.php";

$pdo = ConnectionPDO::getConnection()->getLink();

$tf_pins = isset($tf_pins) ? $tf_pins : null;

if (isset($_REQUEST['id'])) {
    $id = intval($_REQUEST['id']);
    $status = isset($_REQUEST['st']) ? $_REQUEST['st'] : null;
    $canal = isset($_REQUEST['cn']) ? $_REQUEST['cn'] : null;

    // Base da query
    $sql = "SELECT pin_valor FROM pins WHERE 1=1";
    $params = [];

    // Filtro por operadora
    if ($id !== -1) {
        $sql .= " AND opr_codigo = :id";
        $params[':id'] = $id;
    }

    // Filtro por status
    if (!empty($status)) {
        if ($status === "Vendido - TODOS" || $status === "stVendido-TODOS") {
            $sql .= " AND (pin_status = '3' OR pin_status = '6' OR pin_status = '7')";
        } else {
            $sql .= " AND pin_status = :status";
            $params[':status'] = $status;
        }
    }

    // Filtro por canal
    if (!empty($canal)) {
        $sql .= " AND pin_canal = :canal";
        $params[':canal'] = $canal;
    }

    $sql .= " GROUP BY pin_valor ORDER BY pin_valor";

    // Executa a query
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rs_oprPins = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Exibe os checkboxes
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
} else {
    echo "Valores não encontrados (0)";
}
?>
