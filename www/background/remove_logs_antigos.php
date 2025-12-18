<?php

require_once "../db/connect.php";
require_once "../db/ConnectionPDO.php";

try {
    $con = ConnectionPDO::getConnection();
    if ($con->isConnected()) {

        $pdo = $con->getLink();

        $sql = "DELETE usuario_logs_acoes WHERE data_hora_registro <= DATE_SUB(NOW(), INTERVAL 30 DAY)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute();
    }


    // Diretório onde os arquivos serão verificados
    $diretorio = "/www/arquivos_gerados/logs/sql_logs";

    // Data limite para exclusão (30 dias atrás)
    $data_limite = time() - (30 * 24 * 60 * 60);

    // Encontrar todos os arquivos no diretório
    $arquivos = glob($diretorio . "*");

    foreach ($arquivos as $arquivo) {
        // Verificar se é um arquivo (e não um diretório)
        if (is_file($arquivo)) {
            // Obter a data da última modificação do arquivo
            $data_modificacao = filemtime($arquivo);

            // Se a data de modificação for anterior à data limite, excluir o arquivo
            if ($data_modificacao <= $data_limite) {
                unlink($arquivo);
                echo "Arquivo $arquivo excluído.\n";
            }
        }
    }
} catch (Exception $ex) {

    $logDir = '/www/arquivos_gerados/logs';
    $logFile = $logDir . '/erro_log_acoes_gamer_' . date('Y-m-d') . '.log';

    // Cria o diretório caso não exista
    if (!is_dir($logDir)) {
        if (!mkdir($logDir, 0777, true)) {
            throw new Exception("Não foi possível criar o diretório de logs: $logDir");
        }
    }

    // Garante extensão .log
    if (pathinfo($logFile, PATHINFO_EXTENSION) !== 'log') {
        throw new Exception("Extensão inválida para arquivo de log.");
    }

    // Impede path traversal
    if (strpos($logFile, '..') !== false) {
        throw new Exception("Caminho inválido para o arquivo de log.");
    }

    // Monta mensagem no formato padrão
    $logMessage = json_encode([
        'timestamp' => date('Y-m-d H:i:s'),
        'error'     => $ex->getMessage(),
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;

    // Escreve no arquivo com trava
    if (file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX) === false) {
        throw new Exception("Falha ao escrever no arquivo de log: $logFile");
    }
} catch (PDOException $ex) {

    $logDir = '/www/arquivos_gerados/logs';
    $logFile = $logDir . '/erro_log_acoes_gamer_' . date('Y-m-d') . '.log';

    // Cria o diretório caso não exista
    if (!is_dir($logDir)) {
        if (!mkdir($logDir, 0777, true)) {
            throw new Exception("Não foi possível criar o diretório de logs: $logDir");
        }
    }

    // Garante extensão .log
    if (pathinfo($logFile, PATHINFO_EXTENSION) !== 'log') {
        throw new Exception("Extensão inválida para arquivo de log.");
    }

    // Impede path traversal
    if (strpos($logFile, '..') !== false) {
        throw new Exception("Caminho inválido para o arquivo de log.");
    }

    // Monta log estruturado
    $logData = [
        'timestamp' => date('Y-m-d H:i:s'),
        'type'      => 'PDOException',
        'message'   => $ex->getMessage(),
        'trace'     => $ex->getTraceAsString(),
    ];

    $logEntry = json_encode(
        $logData,
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
    ) . PHP_EOL;

    // Escreve no arquivo com trava
    if (file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX) === false) {
        throw new Exception("Falha ao escrever no arquivo de log: $logFile");
    }
}
