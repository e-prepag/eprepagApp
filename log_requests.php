<?php
/**
 * AUTO PREPEND SECURITY LAYER
 * Impede execução de arquivos suspeitos ou localizados em diretórios perigosos
 */

$script = $_SERVER['SCRIPT_FILENAME'] ?: '';
$basename = basename($script);
$dirname = dirname($script);

// ------------------------------------------------------------
// 1. BLACKLIST: nomes exatos muito suspeitos
// ------------------------------------------------------------

$exactBlacklist = [
    '.php',
    'shell.php',
    'sh.php',
    'cmd.php',
    'backdoor.php',
    'evil.php',
    'hack.php',
    'wso.php',
    'r57.php',
    'c99.php',
    'gif.php',
    'mailer.php',
    'upload.php',
    'uploader.php',
];

if (in_array(strtolower($basename), $exactBlacklist, true)) {
    denyExecution("Exact blacklist match: $basename");
}

// ------------------------------------------------------------
// 2. BLACKLIST: nomes de 1 ou 2 caracteres
// Ex: a.php, 1.php, xy.php
// ------------------------------------------------------------

if (preg_match('/^(?!(dld|cep|C03)\.php$)[a-zA-Z0-9]{1,3}\.php$/i', $basename)) {
    denyExecution("Short filename: $basename");
}

// ------------------------------------------------------------
// 3. BLACKLIST: nomes suspeitos por padrão
// ------------------------------------------------------------

if (preg_match('/(shell|cmd|exec|upload|uploader|backdoor|mailer|wso|r57|c99|bypass|inject)\.php$/i', $basename)) {
    denyExecution("Suspicious pattern: $basename");
}

// ------------------------------------------------------------
// 4. DIRECTORY BLACKLIST: locais proibidos
// ------------------------------------------------------------

$forbiddenDirs = [
    '/tmp/',
    '/temp/',
    '/cache/',
    '/log/',
    '/logs/',
    '/upload/',
    '/uploads/',
    '/img/',
    '/image/',
    '/images/',
    '/imagens/',
];

// Detecta diretórios proibidos
foreach ($forbiddenDirs as $dir) {
    if (stripos($dirname . '/', $dir) !== false) {
        denyExecution("Execution in forbidden directory: $dirname");
    }
}

// ------------------------------------------------------------
// 5. Diretório includes/ — permitido apenas por include!
// Se for executado diretamente: bloqueia
// ------------------------------------------------------------

if (stripos($dirname . '/', '/includes/') !== false) {

    $scriptName = realpath($_SERVER['SCRIPT_NAME'] ?: '');
    $scriptFile = realpath($script);

    // Se o arquivo acessado diretamente, não via include()
    if ($scriptName === $scriptFile) {
        denyExecution("Direct execution in /includes/: $script");
    }
}

// ------------------------------------------------------------
// Função de bloqueio
// ------------------------------------------------------------

function denyExecution($reason)
{
    // Caminho do log (modifique se quiser)
    $logFile = '/www/log/security_block.log';

    $log  = "[" . date('Y-m-d H:i:s') . "] BLOCKED: " . $reason . "\n";
    $log .= "IP: " . ($_SERVER['REMOTE_ADDR'] ?: 'CLI') . "\n";
    $log .= "SCRIPT: " . ($_SERVER['SCRIPT_FILENAME'] ?: '') . "\n";
    $log .= "URI: " . ($_SERVER['REQUEST_URI'] ?: 'CLI') . "\n";
    $log .= "--------------------------------------------------\n";

    @file_put_contents($logFile, $log, FILE_APPEND);

    http_response_code(403);
    exit('Access denied.');
}


// Colocar no /usr/share/php e no php ini na opção auto_prepend_file
// --- Configurações ---
if (!defined('SCRIPT_START_TIME')) {
    define('SCRIPT_START_TIME', microtime(true));
}
// Obtém a hora atual no formato 24h (de 0 a 23)
$hora_atual = (int)date('G');

// Divide a hora por 6 para descobrir o bloco (0, 1, 2, ou 3)
// (PHP trata a divisão de inteiros e arredonda para baixo)
$bloco_do_dia = (int)($hora_atual / 6); 

// Monta o nome do arquivo usando a data E o bloco
$LOG_FILE_PATH = '/www/log/requests-' . date('Y-m-d') . '-bloco-' . $bloco_do_dia . '.log';
$LOG_OWNER_USER = 'www-data'; // Usuário que DEVE ser o dono (usuário do FPM)
$LOG_OWNER_GROUP = 'www-data'; // Grupo que DEVE ser o dono

$SENSITIVE_KEYS = [
    // Lista de chaves a serem filtradas
    'password', 'passw', 'senha', 'key', 'pwd', 'token',
    'authorization', 'auth', 'access_token', 'secret',
    'credit_card', 'cc', 'card_number', 'cvv', 'ssn',
    'cpf', 'passmestra', 'g-recaptcha-response'
];
// ---------------------

/**
 * Função recursiva para filtrar dados sensíveis em arrays.
 * (Seu código original)
 */
function filter_sensitive_data(array $data, array $keysToFilter)
{
    $filteredData = [];
    foreach ($data as $key => $value) {
        $lowerKey = strtolower((string) $key);
        if (in_array($lowerKey, $keysToFilter)) {
            $filteredData[$key] = '***REMOVIDO***';
        } elseif (is_array($value)) {
            $filteredData[$key] = filter_sensitive_data($value, $keysToFilter);
        } else {
            $filteredData[$key] = $value;
        }
    }
    return $filteredData;
}

/**
 * Obtém todos os cabeçalhos da requisição.
 * (Seu código original)
 */
function get_request_headers()
{
    if (function_exists('getallheaders')) {
        return getallheaders();
    }
    $headers = [];
    foreach ($_SERVER as $name => $value) {
        if (substr($name, 0, 5) == 'HTTP_') {
            $headerKey = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
            $headers[$headerKey] = $value;
        }
    }
    return $headers;
}

// --- Início do Processo de Log ---

try {
    // Importante: Verifica se o log do dia existe ANTES de escrever
    $fileExists = file_exists($LOG_FILE_PATH);

    // 1. Coleta de dados básicos
    $logData = [
        'timestamp' => date('Y-m-d H:i:s'),
    ];

    // 2. Diferencia a coleta de dados (CLI vs. WEB)
    if (php_sapi_name() === 'cli') {
        // --- É UM CRON OU EXECUÇÃO CLI ---
        global $argv; // Garante que $argv esteja no escopo
        
        $logData['type'] = 'CLI';
        $logData['user'] = $_SERVER['USER'] ?: (function_exists('posix_getpwuid') ? posix_getpwuid(posix_geteuid())['name'] : 'unknown');
        $logData['script'] = $_SERVER['SCRIPT_FILENAME'] ?: 'N/A';
        $logData['arguments'] = $argv ?: []; // Salva os argumentos do CLI

    } else {
        // --- É UMA REQUISIÇÃO WEB (FPM) ---
        $logData['type'] = 'WEB';
        $logData['ip'] = $_SERVER['REMOTE_ADDR'] ?: 'N/A';
        $logData['method'] = $_SERVER['REQUEST_METHOD'] ?: 'N/A';
        $logData['uri'] = $_SERVER['REQUEST_URI'] ?: 'N/A';
        $logData['script'] = $_SERVER['SCRIPT_FILENAME'] ?: 'N/A';

        // Filtra dados da requisição (GET, POST, COOKIE)
        $logData['get_params'] = filter_sensitive_data($_GET, $SENSITIVE_KEYS);
        $logData['post_params'] = filter_sensitive_data($_POST, $SENSITIVE_KEYS);
        
        // Captura o "corpo" da requisição (ex: JSON)
        $rawBody = file_get_contents('php://input');
        $jsonBody = json_decode($rawBody, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($jsonBody)) {
            $logData['json_body'] = filter_sensitive_data($jsonBody, $SENSITIVE_KEYS);
        } else {
            $logData['raw_body_preview'] = substr($rawBody, 0, 256) . (strlen($rawBody) > 256 ? '...' : '');
        }

        // Captura metadados de arquivos
        $fileData = [];
        if (!empty($_FILES)) {
            foreach ($_FILES as $inputName => $fileInfo) {
                if (is_array($fileInfo['name'])) {
                    foreach ($fileInfo['name'] as $index => $name) {
                        $fileData[$inputName][] = [
                            'name' => $name,
                            'type' => $fileInfo['type'][$index] ?: 'N/A',
                            'tmp_name' => $fileInfo['tmp_name'][$index] ?: 'N/A',
                            'error' => $fileInfo['error'][$index] ?: 'N/A',
                            'size' => $fileInfo['size'][$index] ?: 0,
                        ];
                    }
                } else {
                    $fileData[$inputName] = $fileInfo;
                }
            }
        }
        $logData['files'] = $fileData;

        // Captura cabeçalhos
        $headers = get_request_headers();
        $logData['headers'] = filter_sensitive_data($headers, $SENSITIVE_KEYS);
    }

    // 6. Formata a entrada do log
    $logEntry = json_encode($logData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL;

    // 7. Escreve no arquivo de log
    if (file_put_contents($LOG_FILE_PATH, $logEntry, FILE_APPEND | LOCK_EX) === false) {
        // Se a escrita falhar, registra e para
        throw new Exception("Falha ao escrever no log: $LOG_FILE_PATH");
    }

    // 8. --- CORREÇÃO DE PERMISSÃO NA CRIAÇÃO ---
    // Se o arquivo NÃO existia antes (foi criado nesta execução)...
    if (!$fileExists) {
        // Passo A: Define as permissões para 664 (rw-rw-r--)
        @chmod($LOG_FILE_PATH, 0664);

        // Passo B: Se fomos NÓS (root) que criamos...
        if (function_exists('posix_getuid') && posix_getuid() === 0) {
            // Muda o dono para o usuário do FPM
            @chown($LOG_FILE_PATH, $LOG_OWNER_USER);
            @chgrp($LOG_FILE_PATH, $LOG_OWNER_GROUP);
        }
    }

} catch (Exception $e) {
    // Se algo falhar (ex: permissão de escrita), registra no log de erros do PHP
    error_log('Falha no Prepend Logger: ' . $e->getMessage());
}
?>