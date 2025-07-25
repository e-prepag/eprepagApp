<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require "/www/db/connect.php";
require "/www/db/ConnectionPDO.php";

$pdo = ConnectionPDO::getConnection()->getLink();
$sql = "SELECT distinct dugsl_ug_id from dist_usuarios_games_saldo_log where dugsl_data_inclusao >= CURRENT_DATE - INTERVAL '30 day'";
$stmt = $pdo->query($sql);
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

$saldos_org = [];

foreach ($usuarios as $usuario) {
    $j = 0;
    $sql = "SELECT dugsl_data_inclusao, dugsl_ug_perfil_saldo, dugsl_ug_perfil_saldo_antes 
                FROM dist_usuarios_games_saldo_log 
                WHERE dugsl_data_inclusao >= CURRENT_DATE - INTERVAL '30 day' AND dugsl_ug_id = {$usuario['dugsl_ug_id']} 
                ORDER BY dugsl_data_inclusao DESC";
    $stmt = $pdo->query($sql);
    $saldos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    //print_r($saldos);
    for ($i = 0; $i < 30; $i++) {
        $primeiro = false;
        $dias_atras = $i;
        $data = (new DateTime())->modify("-$dias_atras days")->format('Y-m-d');

        $saldo_final = 0;
        $saldo_inicial = 0;
        $entradas = 0;
        $saidas = 0;

        while ($j < count($saldos) && substr($saldos[$j]['dugsl_data_inclusao'], 0, 10) == $data) {
            echo "Usuario: {$usuario['dugsl_ug_id']} - Data: $data - Saldo: {$saldos[$j]['dugsl_ug_perfil_saldo']} - Saldo antes: {$saldos[$j]['dugsl_ug_perfil_saldo_antes']}\n";
            if($primeiro == false){
                $saldo_final = $saldos[$j]['dugsl_ug_perfil_saldo'];
            }
            if($saldos[$j]['dugsl_ug_perfil_saldo'] > $saldos[$j]['dugsl_ug_perfil_saldo_antes']) {
                $entradas += $saldos[$j]['dugsl_ug_perfil_saldo'] - $saldos[$j]['dugsl_ug_perfil_saldo_antes'];
            } else {
                $saidas += $saldos[$j]['dugsl_ug_perfil_saldo_antes'] - $saldos[$j]['dugsl_ug_perfil_saldo'];
            }
            $j++;
            $primeiro = true;
        }
        if($primeiro){
            $saldo_inicial = $saldos[$j-1]['dugsl_ug_perfil_saldo_antes'];
        }
        
        $saldos_org[$usuario['dugsl_ug_id']][$data] = [
            'saldo_final' => $saldo_final,
            'saldo_inicial' => $saldo_inicial,
            'entradas' => $entradas,
            'saidas' => $saidas
        ];
    }
}
echo "\nsaldos:\n";

echo json_encode($saldos_org);