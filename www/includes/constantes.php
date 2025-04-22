<?php
$raiz_do_projeto = "/www/";
require_once "/www/includes/bourls.php";
define("RAIZ_DO_PROJETO",$raiz_do_projeto);

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";

// Nome do host
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';

// Construindo a URL base
$baseUrl = $protocol . "://" . $host;

if (isset($baseUrl)) {
    if($host == $server_url_bo){
        define("SISTEMA", "backoffice");
    }else{
        define("SISTEMA", "sysadmin");
    }
}//end if (isset($GLOBALS['_SERVER']['SERVER_PORT']))

define("DIR_WEB",RAIZ_DO_PROJETO."public_html/");

define("DIR_BACKOFFICE",RAIZ_DO_PROJETO."backoffice/");
define("DIR_BACKOFFICE_ADMIN",DIR_BACKOFFICE."admin/");
define("DIR_BACKOFFICE_DIST_COMMERCE",DIR_BACKOFFICE."dist_commerce/");
define("DIR_BACKOFFICE_COMMERCE",DIR_BACKOFFICE."commerce/");

define("DIR_COMMERCE",DIR_WEB."game/");
define("DIR_DIST_COMMERCE",DIR_WEB."creditos/");

define("DIR_SYS_ADMIN",DIR_WEB."sys/admin/");

define("DIR_CREDITOS",DIR_WEB."creditos/");
define("DIR_GAMES",DIR_WEB."game/");

define("DIR_CLASS",RAIZ_DO_PROJETO."class/");

define("DIR_JSON",RAIZ_DO_PROJETO."json/");

define("DIR_CACHE",DIR_WEB."cache/");

define("DIR_LOG",RAIZ_DO_PROJETO."log/");

define("DIR_DB",RAIZ_DO_PROJETO."db/");

define("DIR_INCS",RAIZ_DO_PROJETO."includes/");

define("DIR_W_IMG_PRODUTOS","/imagens/pdv/produtos/");

define("DIR_G_IMG_PRODUTOS","/imagens/gamer/produtos/");

define("DIR_EPREPAG","/");

(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=="on" ? define("PROTOCOL","HTTPS"):define("PROTOCOL","HTTP"));

$origem = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') ? $_SERVER['HTTP_REFERER'] : $_SERVER['SCRIPT_NAME'];

if(strpos($origem,'creditos')){
    define("CURRENT_SYSTEM","creditos");
}elseif(strpos($origem,'game')){
    define("CURRENT_SYSTEM","gamer");
}else{
    define("CURRENT_SYSTEM","gamer");
}

/* 
 *  Constantes de retorno
 */

define("RETURN_SUCCESS",1);
define("RETURN_EMPTY",2);
define("RETURN_WRONG",3);
define("RETURN_CAPTCHA",4);
define("RETURN_MAX_COUNT",5);
define("RETURN_TWO_FACTOR",6);
/*
 * Constantes relacionadas ao blog
 */

define("ARR_JSON_FEED_CREDITOS",  serialize(array("lh-blog-json.json", "lh-blog-json-2.json", "lh-blog-json-3.json")));
define("ARR_JSON_FEED_GAMER",  serialize(array("gamer-blog.json", "gamer-blog-2.json", "gamer-blog-3.json")));

define("URL_BLOG_CREDITOS",'http://e-prepagpdv.com.br/category/blog-pdv/feed/'); //"http://blog.e-prepag.com/categorias/blogpdv/feed/");
define("URL_BLOG_GAMER",'http://blog.e-prepag.com/categorias/noticias-e-prepag/feed/'); //'http://blog.e-prepag.com/categorias/blog/feed/');

define("MAX_FEEDS_JSON",6);
/*
 * Constantes de produtos
 */

define("ARR_PRODUTOS_CREDITOS",  serialize(array("lh-produtos.json", "lh-produtos-2.json", "lh-produtos-3.json")));
define("ARR_PRODUTOS_GAMER",  serialize(array("gamer-produtos.json", "gamer-produtos-2.json", "gamer-produtos-3.json")));

/*
 * Constantes de modelos x produtos x operadoras
 */

define("ARR_JSON_PRODUTOS_MEIOS_DE_PAGAMENTOS_BLOQUEADOS_GAMER", serialize(array("produtos-meios-de-pagamentos-bloqueados-gamer.json", "produtos-meios-de-pagamentos-bloqueados-gamer-2.json", "produtos-meios-de-pagamentos-bloqueados-gamer-3.json")));

$ARRAY_INIBI_VENDA_HARDCODE = array(4708);

$ARRAY_INIBI_PRODUTOS_VENDA_TO_ID_HARDCODE = array(281);


//Constante de idade mínima para cadastro
$IDADE_MINIMA = 0;

//Constante que define se haverá transferência SFTP dos arquivos para o Windows
define("SFTP_TRANSFER", false);

?>
