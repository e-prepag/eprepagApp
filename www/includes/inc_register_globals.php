<?php
register_globals();

function register_globals($order = 'gp')
{
    // Log de entradas
    $$logDir = '/www/log';
    $logFile = $logDir . '/php_register_globals.log';

    // Garante que o diretório exista
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
        throw new Exception("Caminho inválido detectado.");
    }

    // Captura o arquivo chamador
    $caller = $_SERVER['SCRIPT_FILENAME'] ?? 'UNKNOWN';

    // Monta a entrada do log
    $entry = date('[Y-m-d H:i:s]') .
        " $caller GET=" . json_encode($_GET, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) .
        " POST=" . json_encode($_POST, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) .
        PHP_EOL;

    // Escreve com segurança
    if (file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX) === false) {
        throw new Exception("Falha ao escrever no arquivo de log: $logFile");
    }


    // Subfunção
    if (!function_exists('register_global_array')) {
        function register_global_array(array $superglobal)
        {
            foreach ($superglobal as $varname => $value) {
                // Não sobrescreve variáveis já existentes
                if (!array_key_exists($varname, $GLOBALS)) {
                    $GLOBALS[$varname] = $value;
                }
            }
        }
    }

    // Ordem g p
    $order = explode("\r\n", trim(chunk_split($order, 1)));
    foreach ($order as $k) {
        switch (strtolower($k)) {
            case 'g':
                register_global_array($_GET);
                break;
            case 'p':
                register_global_array($_POST);
                break;
        }
    }
}


/**
 * Undo register_globals
 * @author Ruquay K Calloway
 * @link hxxp://www.php.net/manual/en/security.globals.php#82213
 */
function unregister_globals()
{
    if (ini_get(register_globals)) {
        $array = array('_REQUEST', '_SESSION', '_SERVER', '_ENV', '_FILES');
        foreach ($array as $value) {
            foreach ($GLOBALS[$value] as $key => $var) {
                if ($var === $GLOBALS[$key]) {
                    unset($GLOBALS[$key]);
                }
            }
        }
    }
}
