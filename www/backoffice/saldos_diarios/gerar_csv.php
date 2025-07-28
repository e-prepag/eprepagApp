<?php
// Cabe�alhos para for�ar download
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="relatorio_saldos.csv"');
header('Pragma: no-cache');
header('Expires: 0');
require __DIR__ .'/functions_saldos.php';

$output = fopen('php://output', 'w');

$data_inicial = isset($_GET['dt_inicial']) ? $_GET['dt_inicial'] : date('Y-m-d', strtotime('-30 days'));
$data_final = isset($_GET['dt_final']) ? $_GET['dt_final'] . " 23:59:59" : date('Y-m-d') . " 23:59:59";
$tipo_cliente = isset($_GET['tipo_cliente']) ? $_GET['tipo_cliente'] : 4;

$dados = buscarSaldosDiarios($data_inicial, $data_final, $tipo_cliente);

// Escreve BOM para que Excel reconheça UTF-8 (evita problemas com acentos)
echo "\xEF\xBB\xBF";

// Cabeçalhos da tabela
fputcsv($output, ['Data', 'Tipo Cliente', 'Saldo Inicial', 'Entradas', 'Saídas', 'Saldo Final'], ';');

// Linhas dos dados
foreach ($dados as $linha) {
    fputcsv($output, [
        $linha['data'],
        'PDV',
        number_format($linha['saldo_inicial'], 2, ',', '.'),
        number_format($linha['entradas'], 2, ',', '.'),
        number_format($linha['saidas'], 2, ',', '.'),
        number_format($linha['saldo_final'], 2, ',', '.')
    ], ';');
}

fclose($output);
exit;
