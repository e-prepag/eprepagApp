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
    $diretorio = "/www/log/sql_logs";

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

    $logFile = '/www/log/erro_log_acoes_gamer_' . date('Y-m-d') . '.log';
    $logMessage = date('Y-m-d H:i:s') . " | Exception: " . $ex->getMessage() . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND);

} catch (PDOException $ex) {

    $logFile = '/www/log/erro_log_acoes_gamer_' . date('Y-m-d') . '.log';
    $logMessage = date('Y-m-d H:i:s') . " | PDOException: " . $ex->getMessage() . PHP_EOL;
    $logMessage .= "Trace: " . $ex->getTraceAsString() . PHP_EOL; // Inclui o rastreamento da exceção
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

?>