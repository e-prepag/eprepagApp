<?php

require_once __DIR__ . '/load_dotenv.php';
require_once __DIR__ . '/bourls.php';

require_once RAIZ_DO_PROJETO . 'includes/bourls.php';

if (!function_exists('random_bytes')) {
    function random_bytes($length)
    {

        // Se tiver OpenSSL, usa bytes seguros
        if (function_exists('openssl_random_pseudo_bytes')) {
            return openssl_random_pseudo_bytes($length);
        }
        // Fallback manual (nгo criptograficamente seguro)
        $bytes = '';
        for ($i = 0; $i < $length; $i++) {
            // chr(rand(0, 255)) gera um byte ?aleatуrio?
            $bytes .= chr(mt_rand(0, 255));
        }
        return $bytes;
    }
}

$protocol = "https";

// Nome do host
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';

// Construindo a URL base
$baseUrl = $protocol . "://" . $host;

if ($host == $server_url_bo) {
    define("SISTEMA", "backoffice");
} else {
    define("SISTEMA", "sysadmin");
} //end if (isset($GLOBALS['_SERVER']['SERVER_PORT']))

define("DIR_WEB", RAIZ_DO_PROJETO . "public_html/");
define("DIR_IMG", RAIZ_DO_PROJETO . "arquivos_gerados/");

define("DIR_BACKOFFICE", RAIZ_DO_PROJETO . "backoffice/");
define("DIR_BACKOFFICE_ADMIN", DIR_BACKOFFICE . "admin/");
define("DIR_BACKOFFICE_DIST_COMMERCE", DIR_BACKOFFICE . "dist_commerce/");
define("DIR_BACKOFFICE_COMMERCE", DIR_BACKOFFICE . "commerce/");

define("DIR_COMMERCE", DIR_WEB . "game/");
define("DIR_DIST_COMMERCE", DIR_WEB . "creditos/");

define("DIR_SYS_ADMIN", DIR_WEB . "sys/admin/");

define("DIR_CREDITOS", DIR_WEB . "creditos/");
define("DIR_GAMES", DIR_WEB . "game/");

define("DIR_CLASS", RAIZ_DO_PROJETO . "class/");

define("DIR_JSON", RAIZ_DO_PROJETO . "json/");

define("DIR_CSV", RAIZ_DO_PROJETO . "arquivos_gerados/csv/");

define("DIR_LOG", RAIZ_DO_PROJETO . "arquivos_gerados/logs/");

define("DIR_DB", RAIZ_DO_PROJETO . "db/");

define("DIR_INCS", RAIZ_DO_PROJETO . "includes/");

define("DIR_W_IMG_PRODUTOS", "/imagens/pdv/produtos/");

define("DIR_G_IMG_PRODUTOS", "/imagens/gamer/produtos/");

define("DIR_EPREPAG", "/");

define('IOF', 3.5);

define("PROTOCOL", "HTTPS");

$origem = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') ? $_SERVER['HTTP_REFERER'] : $_SERVER['SCRIPT_NAME'];

if (strpos($origem, 'creditos')) {
    define("CURRENT_SYSTEM", "creditos");
} elseif (strpos($origem, 'game')) {
    define("CURRENT_SYSTEM", "gamer");
} else {
    define("CURRENT_SYSTEM", "gamer");
}

/* 
 *  Constantes de retorno
 */

define("RETURN_SUCCESS", 1);
define("RETURN_EMPTY", 2);
define("RETURN_WRONG", 3);
define("RETURN_CAPTCHA", 4);
define("RETURN_MAX_COUNT", 5);
define("RETURN_TWO_FACTOR", 6);
/*
 * Constantes relacionadas ao blog
 */

define("ARR_JSON_FEED_CREDITOS", serialize(array("lh-blog-json.json")));
define("ARR_JSON_FEED_GAMER", serialize(array("gamer-blog.json")));

define("URL_BLOG_CREDITOS", 'http://e-prepagpdv.com.br/category/blog-pdv/feed/');
define("URL_BLOG_GAMER", 'http://blog.e-prepag.com/categorias/noticias-e-prepag/feed/');

define("MAX_FEEDS_JSON", 6);

/*
 * Constantes de produtos
 */
define("ARR_PRODUTOS_CREDITOS", serialize(array("lh-produtos.json")));
define("ARR_PRODUTOS_GAMER", serialize(array("gamer-produtos.json")));

/*
 * Constantes de modelos x produtos x operadoras
 */
define("ARR_JSON_PRODUTOS_MEIOS_DE_PAGAMENTOS_BLOQUEADOS_GAMER", serialize(array("produtos-meios-de-pagamentos-bloqueados-gamer.json")));

$ARRAY_INIBI_VENDA_HARDCODE = array(4708);

$ARRAY_INIBI_PRODUTOS_VENDA_TO_ID_HARDCODE = array(281);


//Constante de idade mнnima para cadastro
$IDADE_MINIMA = 16;

//Constante de idade mбxima sem validaзгo adicional de RC
$IDADE_MAXIMA = 60;

//Constante que define se haverб transferкncia SFTP dos arquivos para o Windows
define("SFTP_TRANSFER", false);
