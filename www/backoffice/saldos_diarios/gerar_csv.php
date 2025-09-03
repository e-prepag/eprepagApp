<?php
// Cabecalhos para forcar download
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="relatorio_saldos_' . date('Y-m-d H:m:i') . '.csv"');
header('Pragma: no-cache');
header('Expires: 0');
require __DIR__ .'/functions_saldos.php';

$output = fopen('php://output', 'w');

$data_inicial = isset($_GET['data_inicial']) ? urldecode($_GET['data_inicial']) : date('Y-m-d', strtotime('-1 Month'));
$data_final = isset($_GET['data_final']) ? urldecode($_GET['data_final']) . " 23:59:59" : date('Y-m-d', strtotime('-1 Day')) . " 23:59:59";
$tipo_cliente = isset($_GET['tipo_cliente']) ? $_GET['tipo_cliente'] : 4;

$dados = buscarSaldosDiarios($data_inicial, $data_final, $tipo_cliente);

// Escreve BOM para que Excel reconheça UTF-8 (evita problemas com acentos)
echo "\xEF\xBB\xBF";
$tipo_cliente_texto = $tipo_cliente == 4 ? 'Todos' : ($tipo_cliente == 3 ? 'PDVs' : ($tipo_cliente == 2 ? 'Gamers' : 'Desconhecido'));
// Cabeçalhos da tabela
// Cabeçalhos ajustados conforme os campos retornados pela função buscarSaldosDiarios
fputcsv($output, [
    'Data',
    'Tipo Cliente',
    'Saldo Inicial',
    'Entr. STR (18:30)',
    'Saídas STR (18:30)',
    'Saldo Final STR',
    'Entradas (23:59)',
    'Saídas (23:59)',
    'Saldo Final (23:59)'
], ';');

// Linhas dos dados
foreach ($dados as $linha) {
    fputcsv($output, [
        isset($linha['data']) ? $linha['data'] : '',
        $tipo_cliente_texto,
        number_format(isset($linha['saldo_inicial']) ? $linha['saldo_inicial'] : 0, 2, ',', '.'),
        number_format(isset($linha['entradas_ate_corte']) ? $linha['entradas_ate_corte'] : 0, 2, ',', '.'),
        number_format(isset($linha['saidas_ate_corte']) ? $linha['saidas_ate_corte'] : 0, 2, ',', '.'),
        number_format(isset($linha['saldo_corte']) ? $linha['saldo_corte'] : 0, 2, ',', '.'),
        number_format(isset($linha['entradas_completas']) ? $linha['entradas_completas'] : 0, 2, ',', '.'),
        number_format(isset($linha['saidas_completas']) ? $linha['saidas_completas'] : 0, 2, ',', '.'),
        number_format(isset($linha['saldo_final']) ? $linha['saldo_final'] : 0, 2, ',', '.')
    ], ';');
}

fclose($output);
exit;
