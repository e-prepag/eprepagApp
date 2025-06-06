<?php
require "/www/db/connect.php";
require "/www/db/ConnectionPDO.php";

function temLogInconsistente($ug_id, $pdo) {
    // 1. Pega todos os logs de alteração de e-mail na última semana
    $stmt = $pdo->prepare("
        SELECT ug_id, data_log
        FROM log_alteracao_email
        WHERE data_log >= NOW() - INTERVAL '7 days'
          AND ug_id = :ug_id
    ");
    $stmt->execute([':ug_id' => $ug_id]);
    $logsEmail = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($logsEmail as $log) {
        $dataLog = $log['data_log'];

        // 2. Verifica se existe um log do tipo 3 no intervalo de 5 segundos
        $stmtCheck = $pdo->prepare("
            SELECT 1
            FROM dist_usuarios_games_log
            WHERE ugl_ug_id = :ug_id
              AND ugl_uglt_id = 3
              AND ABS(EXTRACT(EPOCH FROM (ugl_data_inclusao - :data_log))) <= 5
            LIMIT 1
        ");
        $stmtCheck->execute([
            ':ug_id' => $ug_id,
            ':data_log' => $dataLog,
        ]);

        if (!$stmtCheck->fetch()) {
            // Log de e-mail sem log correspondente dentro de 5s
            return true;
        }
    }

    // Todos os logs têm correspondência válida
    return false;
}

$pdo = ConnectionPDO::getConnection()->getLink();

// Pega os últimos 100 usuários com data de acesso não nula
$stmt = $pdo->query("
    SELECT ug_id
    FROM dist_usuarios_games
    WHERE ug_data_ultimo_acesso IS NOT NULL
    ORDER BY ug_data_ultimo_acesso DESC
    LIMIT 100
");

$usuarios = $stmt->fetchAll(PDO::FETCH_COLUMN);

$resultados = [];

foreach ($usuarios as $ug_id) {
    if (temLogInconsistente($ug_id, $pdo)) {
        $resultados[] = $ug_id;
    }
}

if (count($resultados) > 0) {
    echo "⚠️ Inconsistências detectadas nos usuários: " . implode(", ", $resultados);
} else {
    echo "✅ Todos os últimos 100 usuários estão consistentes.";
}
?>
