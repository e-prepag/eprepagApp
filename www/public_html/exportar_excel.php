<?php
require_once "../../../../../includes/constantes.php";
require_once $raiz_do_projeto . "public_html/sys/includes/topo_sys.php";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=exportacao_dados.csv');

$output = fopen('php://output', 'w');

// Cabeçalho do Excel
fputcsv($output, [
    'Data da Operação',
    'Nome do Consumidor',
    'Canal Usuário - Nome do Comprador',
    'Canal PDV - Nome do Comprador Informado pelo PDV',
    'Valor de Venda ao Consumidor'
]);

$sql = base64_decode($_POST['sql']);
$res = SQLexecuteQuery($sql);

while ($row = pg_fetch_assoc($res)) {
    fputcsv($output, [
        substr($row['trn_data'], 0, 19),
        $row['trn_nome'],
        $row['canal'],
        $row['opr_nome'],
        number_format($row['trn_valor'] * (1 - $row['trn_comissao']), 2, ',', '.')
    ]);
}

fclose($output);
exit;
