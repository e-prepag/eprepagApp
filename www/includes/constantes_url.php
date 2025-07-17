<?php
// Constantes para URLs usadas no sistema
require_once "/www/includes/load_dotenv.php";

$tipo_http = getenv("HAS_CERTIFICATE") == "true" ? "https://" : "http://";

define('EPREPAG_URL_HTTPS', $tipo_http . getenv("EPREPAG_URL"));
define('EPREPAG_URL_HTTP', $tipo_http . getenv("EPREPAG_URL"));
define('EPREPAG_URL', getenv("EPREPAG_URL"));

define('EPREPAG_URL_HTTPS_COM', $tipo_http . getenv("EPREPAG_URL"));
define('EPREPAG_URL_HTTP_COM', $tipo_http . getenv("EPREPAG_URL"));
define('EPREPAG_URL_COM', getenv("EPREPAG_URL"));

?>