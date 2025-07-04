<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."includes/functions.php";
require_once $raiz_do_projeto."includes/rs_ws/inc_utils.php";

$ug_risco_classif = $_GET['ug_risco_classif'] ? $_GET['ug_risco_classif'] : '';
$ug_id = $_GET['ug_id'] ? $_GET['ug_id'] : '';
$tf_v_data_inclusao_ini = $_GET['data_ini'] ? $_GET['data_ini'] : '';
$tf_v_data_inclusao_fim = $_GET['data_fim'] ? $_GET['data_fim'] : '';

// Monte sua query com esses parametros (igual no seu relatorio)
$sql  = "select dugsl.*, ug.ug_risco_classif, ug.ug_login
         from dist_usuarios_games_saldo_log dugsl
         inner join dist_usuarios_games ug on ug.ug_id = dugsl.dugsl_ug_id
         where 1=1 ";
if (!empty($ug_id)) {
    $sql .= "and dugsl_ug_id = ".intval($ug_id)." ";
}
if ($tf_v_data_inclusao_ini) {
    $sql .= "and dugsl_data_inclusao >= '".formata_data_rc($tf_v_data_inclusao_ini,1)." 00:00:00' ";
}
if ($tf_v_data_inclusao_fim) {
    $sql .= "and dugsl_data_inclusao <= '".formata_data_rc($tf_v_data_inclusao_fim,1)." 23:59:59' ";
}
if (!empty($ug_risco_classif)) {
    $sql .= "and ug_risco_classif = ".intval($ug_risco_classif)." ";
}
$sql .= "order by dugsl_data_inclusao desc";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="relatorio.csv"');
$output = fopen('php://output', 'w');

fputcsv($output, [
    'ID', 'Data inclusão', 'ID PDV', 'Login PDV',
    'Tipo PDV', 'Valor anterior', 'Valor atual', 'Tipo transação', 'Diferença'
], ';');

$rs = SQLexecuteQuery($sql);
while ($rs_row = pg_fetch_array($rs)) {
    $linha = [
        $rs_row['dugsl_id'],
        substr($rs_row['dugsl_data_inclusao'], 0, 19),
        $rs_row['dugsl_ug_id'],
        utf8_encode($rs_row['ug_login']),
        ($rs_row['ug_risco_classif'] == '1' ? 'POS' : 'PRE'),
        number_format($rs_row['dugsl_ug_perfil_saldo_antes'], 2, ',', ''),
        number_format($rs_row['dugsl_ug_perfil_saldo'], 2, ',', ''),
        ($rs_row['dugsl_ug_perfil_saldo_antes'] < $rs_row['dugsl_ug_perfil_saldo']) ? 'Entrada' : 'Saída',
        number_format(($rs_row['dugsl_ug_perfil_saldo'] - $rs_row['dugsl_ug_perfil_saldo_antes']), 2, ',', '')
    ];
    fputcsv($output, $linha, ';');
}

fclose($output);
exit;
?>
