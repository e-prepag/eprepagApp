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

const NOVIDADES_URL = "https://solucoes.e-prepag.com/category/business-blog/";
const SOBRE_URL = "https://solucoes.e-prepag.com/a-e-prepag/";
const QUEMSOMOS_URL = "https://solucoes.e-prepag.com/quem-somos/";
const CARTAO_URL = "https://solucoes.e-prepag.com/cartao-e-prepag-2/";
const COMPRASEG_URL = "https://solucoes.e-prepag.com/compra-segura/";
const FORMASPAG_URL = "https://solucoes.e-prepag.com/formas-de-pagamento/";
const SOLUCOES_URL = "//solucoes.e-prepag.com";
const EPPDV_URL = "https://e-prepagpdv.com.br/";
?>