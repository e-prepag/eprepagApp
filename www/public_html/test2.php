<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require "/www/db/connect.php";
require "/www/db/ConnectionPDO.php";

$pdo = ConnectionPDO::getConnection()->getLink();
$sql = "WITH logs_filtrados AS (
    SELECT 
        dugsl_ug_id,
        dugsl_data_inclusao::date AS dia,
        dugsl_data_inclusao,
        dugsl_ug_perfil_saldo,
        dugsl_ug_perfil_saldo_antes
    FROM dist_usuarios_games_saldo_log
    WHERE dugsl_data_inclusao >= CURRENT_DATE - INTERVAL '3 day'
),
ordenados AS (
    SELECT *,
           ROW_NUMBER() OVER (PARTITION BY dugsl_ug_id, dia ORDER BY dugsl_data_inclusao ASC) AS rn_asc,
           ROW_NUMBER() OVER (PARTITION BY dugsl_ug_id, dia ORDER BY dugsl_data_inclusao DESC) AS rn_desc
    FROM logs_filtrados
),
por_usuario_dia AS (
    SELECT 
        dugsl_ug_id,
        dia,
        MAX(CASE WHEN rn_desc = 1 THEN dugsl_ug_perfil_saldo END) AS saldo_final,
        MAX(CASE WHEN rn_asc = 1 THEN dugsl_ug_perfil_saldo_antes END) AS saldo_inicial,
        SUM(CASE WHEN dugsl_ug_perfil_saldo > dugsl_ug_perfil_saldo_antes 
                 THEN dugsl_ug_perfil_saldo - dugsl_ug_perfil_saldo_antes ELSE 0 END) AS entradas,
        SUM(CASE WHEN dugsl_ug_perfil_saldo < dugsl_ug_perfil_saldo_antes 
                 THEN dugsl_ug_perfil_saldo_antes - dugsl_ug_perfil_saldo ELSE 0 END) AS saidas
    FROM ordenados
    GROUP BY dugsl_ug_id, dia
)
SELECT 
    dia,
    SUM(saldo_inicial) AS saldo_inicial,
    SUM(saldo_final) AS saldo_final,
    SUM(entradas) AS entradas,
    SUM(saidas) AS saidas
FROM por_usuario_dia
GROUP BY dia
ORDER BY dia DESC;
";

$stmt = $pdo->query($sql);
$saldos_agrupados = $stmt->fetchAll(PDO::FETCH_ASSOC);

$saldos_org = [];
foreach ($saldos_agrupados as $linha) {
    $saldos_org[$linha['dugsl_ug_id']][$linha['dia']] = [
        'saldo_inicial' => $linha['saldo_inicial'],
        'saldo_final' => $linha['saldo_final'],
        'entradas' => $linha['entradas'],
        'saidas' => $linha['saidas']
    ];
}


echo json_encode($saldos_org);