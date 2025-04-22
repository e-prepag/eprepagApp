<?php
// Configuração do banco de dados
require_once "/www/class/phpmailer/class.phpmailer.php";
require_once "/www/includes/configIP.php";
require_once "/www/class/phpmailer/class.smtp.php";
require_once "/www/includes/constantes.php";
require_once "/www/includes/gamer/functions.php";
require "/www/db/connect.php";
require "/www/db/ConnectionPDO.php";
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

$pdo = ConnectionPDO::getConnection()->getLink();

// Filtros do formulário
$dataInicio = $_GET['data_inicio'] ?? date('Y-m-01');
$dataFim = $_GET['data_fim'] ?? date('Y-m-d');
$tipo = $_GET['tipo'] ?? 'todos'; // 'pdv' ou 'pf'

// Consulta SQL
$sql = "
    SELECT p.data_inclusao AS data, 
           c.idcliente, 
           c.numcompra, 
           COALESCE(g.ug_cpf, d.ug_cnpj) AS cpf_cnpj_cadastro,
           COALESCE(g.ug_nome, d.ug_nome_fantasia) AS nome_cadastro,
           p.cpf_cnpj_pagador, 
           p.nome_pagador, 
           jsonb_extract_path_text(p.json_resposta, 'chave_pix') AS chave_pix_pagadora
    FROM tb_pag_pix p
    INNER JOIN tb_pag_compras c ON p.numcompra = c.numcompra
    LEFT JOIN usuarios_games g ON c.idcliente = g.ug_id
    LEFT JOIN dist_usuarios_games d ON c.idcliente = d.ug_id
    WHERE p.data_inclusao BETWEEN :data_inicio AND :data_fim";

if ($tipo == 'pf') {
    $sql .= " AND g.ug_cpf IS NOT NULL"; // Filtra apenas Pessoas Físicas
} elseif ($tipo == 'pdv') {
    $sql .= " AND d.ug_cnpj IS NOT NULL"; // Filtra apenas PDVs
}

$stmt = $pdo->prepare($sql);
$stmt->execute([
    'data_inicio' => $dataInicio,
    'data_fim' => $dataFim
]);

$pagamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamentos PIX</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.3/xlsx.full.min.js"></script>
    <script>
        function exportarExcel() {
            let tabela = document.getElementById("tabela-pagamentos");
            let workbook = XLSX.utils.table_to_book(tabela, {sheet: "Pagamentos"});
            XLSX.writeFile(workbook, "pagamentos_pix.xlsx");
        }
    </script>
</head>
<body>

    <h2>Consulta de Pagamentos PIX</h2>

    <!-- Formulário de Filtros -->
    <form method="GET">
        <label>Data Início:</label>
        <input type="date" name="data_inicio" value="<?= $dataInicio ?>">
        
        <label>Data Fim:</label>
        <input type="date" name="data_fim" value="<?= $dataFim ?>">
        
        <label>Tipo:</label>
        <select name="tipo">
            <option value="todos" <?= $tipo == 'todos' ? 'selected' : '' ?>>Todos</option>
            <option value="pf" <?= $tipo == 'pf' ? 'selected' : '' ?>>Pessoa Física</option>
            <option value="pdv" <?= $tipo == 'pdv' ? 'selected' : '' ?>>PDV</option>
        </select>

        <button type="submit">Filtrar</button>
    </form>

    <br>

    <!-- Tabela de Resultados -->
    <table border="1" id="tabela-pagamentos">
        <thead>
            <tr>
                <th>Data</th>
                <th>Valor da Transação</th>
                <th>CPF/CNPJ Cadastro</th>
                <th>Nome Cadastro</th>
                <th>CPF/CNPJ Pagador</th>
                <th>Nome Pagador</th>
                <th>Chave PIX Pagadora</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pagamentos as $p): ?>
                <tr>
                    <td><?= $p['data'] ?></td>
                    <td><?= $p['numcompra'] ?></td>
                    <td><?= $p['cpf_cnpj_cadastro'] ?></td>
                    <td><?= $p['nome_cadastro'] ?></td>
                    <td><?= $p['cpf_cnpj_pagador'] ?></td>
                    <td><?= $p['nome_pagador'] ?></td>
                    <td><?= $p['chave_pix_pagadora'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <br>
    <button onclick="exportarExcel()">Exportar para Excel</button>

</body>
</html>
