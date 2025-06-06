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


    // Diret�rio onde os arquivos ser�o verificados
    $diretorio = "/www/log/sql_logs";

    // Data limite para exclus�o (30 dias atr�s)
    $data_limite = time() - (30 * 24 * 60 * 60);

    // Encontrar todos os arquivos no diret�rio
    $arquivos = glob($diretorio . "*");

    foreach ($arquivos as $arquivo) {
        // Verificar se � um arquivo (e n�o um diret�rio)
        if (is_file($arquivo)) {
            // Obter a data da �ltima modifica��o do arquivo
            $data_modificacao = filemtime($arquivo);

            // Se a data de modifica��o for anterior � data limite, excluir o arquivo
            if ($data_modificacao <= $data_limite) {
                unlink($arquivo);
                echo "Arquivo $arquivo exclu�do.\n";
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
    $logMessage .= "Trace: " . $ex->getTraceAsString() . PHP_EOL; // Inclui o rastreamento da exce��o
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

?>