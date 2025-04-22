<?php
echo phpversion();
// Habilita a exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);  // Exibe todos os tipos de erros

require "/www/db/connect.php";
require "/www/db/ConnectionPDO_teste.php";

// Conectando ao banco de dados
$pdo = ConnectionPDO::getConnection()->getLink();

$inicio = microtime(true);

for ($i = 0; $i < 10; $i++) {
// 1. INSERT
$insertQuery = "INSERT INTO log_erros_saldo (
                    ug_id, erro_mensagem, data_ocorrencia, saldo_anterior, saldo_novo, dia_da_semana, horario_ocorrencia
                ) VALUES (
                    :ug_id, :erro_mensagem, :data_ocorrencia, :saldo_anterior, :saldo_novo, :dia_da_semana, :horario_ocorrencia
                ) -- usuario";

$insertParams = [
    'ug_id' => 1,
    'erro_mensagem' => 'Erro ao calcular saldo',
    'data_ocorrencia' => '2024-12-19',
    'saldo_anterior' => 100.50,
    'saldo_novo' => 95.00,
    'dia_da_semana' => 1,
    'horario_ocorrencia' => '14:30:00'
];

$pdo->prepare($insertQuery)->execute($insertParams);

// 3. SELECT
$selectQuery = "SELECT * FROM log_erros_saldo WHERE data_ocorrencia = '2024-12-19'";
$statement = $pdo->prepare($selectQuery);
$statement->execute();
echo "Resultado do SELECT:\n";
while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: " . $row['id'] . " | UG ID: " . $row['ug_id'] . " | Mensagem: " . $row['erro_mensagem'] . " | Data: " . $row['data_ocorrencia'] . " | Saldo Anterior: " . $row['saldo_anterior'] . " | Saldo Novo: " . $row['saldo_novo'] . " | Dia da Semana: " . $row['dia_da_semana'] . " | Horário: " . $row['horario_ocorrencia'] . "<br /><br />";
}

// 2. UPDATE
$updateQuery = "UPDATE log_erros_saldo
                SET saldo_novo = :saldo_novo, erro_mensagem = :erro_mensagem
                WHERE id = :id";

$updateParams = [
    'saldo_novo' => 98.00,
    'erro_mensagem' => 'Saldo ajustado manualmente',
    'id' => 1
];

$pdo->prepare($updateQuery)->execute($updateParams);

// 3. SELECT
$selectQuery = "SELECT * FROM log_erros_saldo WHERE data_ocorrencia = '2024-12-19'";
$statement = $pdo->prepare($selectQuery);
$statement->execute();

echo "Resultado do SELECT:\n";
while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: " . $row['id'] . " | UG ID: " . $row['ug_id'] . " | Mensagem: " . $row['erro_mensagem'] . " | Data: " . $row['data_ocorrencia'] . " | Saldo Anterior: " . $row['saldo_anterior'] . " | Saldo Novo: " . $row['saldo_novo'] . " | Dia da Semana: " . $row['dia_da_semana'] . " | Horário: " . $row['horario_ocorrencia'] . "<br /><br />";
}

// 4. DELETE
$deleteQuery = "DELETE FROM log_erros_saldo WHERE data_ocorrencia = '2024-12-19'";
$pdo->prepare($deleteQuery)->execute();

}
$fim = microtime(true);
$tempoExecucao = $fim - $inicio;

// Echo de finalização
echo "O script levou " . number_format($tempoExecucao, 5) . " segundos para ser executado.";

?>