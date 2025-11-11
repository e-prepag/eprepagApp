<?php
// Colocar no /usr/share/php e no php ini na opção auto_append_file
// --- Configurações do Log de Performance ---
$PERF_LOG_FILE_PATH = '/www/log/performance-' . date('Y-m-d') . '.log';
$PERF_LOG_OWNER_USER = 'www-data';
$PERF_LOG_OWNER_GROUP = 'www-data';
// ------------------------------------------

try {
    // Obter Nome do Script (antes, para usar em caso de falha)
    $scriptName = 'N/A';
    if (php_sapi_name() === 'cli') {
        global $argv;
        $scriptName = $argv[0] ?: $_SERVER['SCRIPT_FILENAME'] ?: 'CLI Unknown';
    } else {
        $scriptName = $_SERVER['SCRIPT_FILENAME'] ?: $_SERVER['REQUEST_URI'] ?: 'Web Unknown';
    }

    $executionTimeMs = null; // Inicia como nulo

    // Verifica se o prepend definiu o tempo inicial
    if (defined('SCRIPT_START_TIME')) {
        $startTime = SCRIPT_START_TIME;
        $endTime = microtime(true);
        // Calcula em milissegundos
        $executionTimeMs = round(($endTime - $startTime) * 1000, 2);
    } else {
        // Se a constante não foi definida, registra a falha
        $executionTimeMs = 'FALHA AO OBTER TEMPO INICIAL';
    }

    $logData = [
        'timestamp' => date('Y-m-d H:i:s'),
        'script' => $scriptName,
        'duration_ms' => $executionTimeMs
    ];
    
    $logEntry = json_encode($logData) . PHP_EOL;

    $fileExists = file_exists($PERF_LOG_FILE_PATH);

    if (file_put_contents($PERF_LOG_FILE_PATH, $logEntry, FILE_APPEND | LOCK_EX) === false) {
        throw new Exception("Falha ao escrever no log de performance: $PERF_LOG_FILE_PATH");
    }

    // Corrigir Permissão se o arquivo foi criado por root
    if (!$fileExists) {
        @chmod($PERF_LOG_FILE_PATH, 0664); 
        if (function_exists('posix_getuid') && posix_getuid() === 0) {
            @chown($PERF_LOG_FILE_PATH, $PERF_LOG_OWNER_USER);
            @chgrp($PERF_LOG_FILE_PATH, $PERF_LOG_OWNER_GROUP);
        }
    }

} catch (Exception $e) {
    // Registra falhas no log de erro principal do PHP
    error_log('Falha no Performance Logger (Append): ' . $e->getMessage());
}
?>