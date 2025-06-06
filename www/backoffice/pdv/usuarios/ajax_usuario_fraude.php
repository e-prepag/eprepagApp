<?php
require "/www/db/connect.php";
require "/www/db/ConnectionPDO.php";
require "../../../public_html/creditos/includes/funcoes_login.php";

if ($_POST['codigo'] != 'Gz8#kV2!mP$Xr9@tQw') {
    echo json_encode(["status" => "error", "message" => "Código de acesso inválido."]);
    exit;
}

$pdo = ConnectionPDO::getConnection()->getLink();

if ($_POST['acao'] == "add") {
    $ug_id = 0 + $_POST['ug_id'];
    adicionarUsuarioBloqueado($ug_id, utf8_decode("(BLQ103) Usuário bloqueado manualmente pela equipe de administração do sistema."));
    $removeLog = $pdo->prepare("
            UPDATE dist_usuarios_games
                SET ug_senha = 'sghfd34251j0k978l78z5x6cv12du0chim5vkhj'
                WHERE ug_id = :ug_id
            ");
            $removeLog->execute([
                ':ug_id' => $ug_id,
            ]);
    echo json_encode(["status" => "success", "message" => "Usuário bloqueado com sucesso."]);
} else if ($_POST['acao'] == "rm") {
    $ug_id = 0 + $_POST['ug_id'];

    removerUsuarioBloqueado($ug_id);

    $stmt = $pdo->prepare("
        SELECT id, ug_id, data_log
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
            $removeLog = $pdo->prepare("
            UPDATE log_alteracao_email
                SET data_log = null
                WHERE id = :id AND ug_id = :ug_id
            ");
            $removeLog->execute([
                ':id' => $log['id'],
                ':ug_id' => $ug_id,
            ]);
        }
    }

    echo json_encode(["status" => "success", "message" => "Usuário desbloqueado com sucesso."]);
} else {
    echo json_encode(["status" => "error", "message" => "Ação inválida."]);
}

?>