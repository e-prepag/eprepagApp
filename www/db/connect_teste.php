<?php

// Fun��o para carregar o arquivo .env e definir as vari�veis de ambiente
if (file_exists('/www/.env')) {
    $lines = file('/www/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        // Ignora coment�rios
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Divide a linha em nome e valor
        list($name, $val) = explode('=', $line, 2);

        // Remove espa�os e aspas
        $name = trim($name);
        $val = trim($val, " \t\n\r\0\x0B\"");

        // Define a vari�vel de ambiente no processo atual
        putenv("$name=$val");
    }
}

// Verifica se as vari�veis de ambiente j� est�o definidas, caso contr�rio as carrega do .env
$db_host = getenv('DB_HOST') ?: null;
$db_port = getenv('DB_PORT') ?: null;
$db_banco = getenv('DB_BANCO') ?: null;
$db_user = getenv('DB_USER') ?: null;
$db_pass = getenv('DB_PASS') ?: null;

// Definir constantes
define('DB_HOST', $db_host);
define('DB_PORT', $db_port);
define('DB_BANCO', $db_banco);
define('DB_USER', $db_user);
define('DB_PASS', $db_pass);

// Conectando ao Banco de dados
$connid = pg_connect("host=" . DB_HOST . " port=" . DB_PORT . " dbname=" . DB_BANCO . " user=" . DB_USER . " password=" . DB_PASS);

?>
