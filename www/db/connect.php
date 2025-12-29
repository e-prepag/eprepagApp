<?php
require_once "/www/includes/load_dotenv.php";

// Verifica se as variáveis de ambiente já estão definidas, caso contrário as carrega do .env
$db_host = getenv('DB_HOST_EPREPAG') ?: "null";
$db_port = getenv('DB_PORT_EPREPAG') ?: "null";
$db_banco = getenv('DB_BANCO_EPREPAG') ?: "null";
$db_user = getenv('DB_USER_EPREPAG') ?: "null";
$db_pass = getenv('DB_PASS_EPREPAG') ?: "null";

// Definir constantes
define('DB_HOST', $db_host);
define('DB_PORT', $db_port);
define('DB_BANCO', $db_banco);
define('DB_USER', $db_user);
define('DB_PASS', $db_pass);

// Conectando ao Banco de dados
$str_conn = "host=" . DB_HOST . 
            " port=" . DB_PORT . 
            " dbname=" . DB_BANCO . 
            " user=" . DB_USER . 
            " password=" . DB_PASS . 
            " connect_timeout=10"; 

// Tenta conectar usando @ para suprimir o warning nativo do PHP na tela
$connid = @pg_connect($str_conn);

// Se falhar, mata o script com a mensagem
if (!$connid) {
    die("Erro de conexão");
}

?>
