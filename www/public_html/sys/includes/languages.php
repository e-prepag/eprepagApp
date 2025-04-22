<?php
header("Content-Type: text/html; charset=ISO-8859-1",true);

if (!defined('IN_eprepag')) {
    define('IN_eprepag', true);
}

if (!defined('S9Y_INCLUDE_PATH')) {
    define('S9Y_INCLUDE_PATH', dirname(__FILE__) . '/');
}

if (!isset($eprepag['eprepagPath'])) {
    $eprepag['eprepagPath'] = (defined('S9Y_INCLUDE_PATH') ? S9Y_INCLUDE_PATH : './');
}

// Sequencia de IF que mantem o language mesmo se outra opção de relatório é selecionada.
// Se xistir valor vindo do formsubmit
if (isset($_REQUEST['nome']) && strlen($_REQUEST['nome']) > 0){
	$_SESSION['langNome'] = $_REQUEST['nome'];
}

// Se a session ja existir mas não vem vamor do formsubmit
if (isset($_SESSION['langNome']) && (strlen($_SESSION['langNome']) > 0)and(!isset($_REQUEST['nome']) || !strlen($_REQUEST['nome']) > 0)){
	$_SESSION['langNome'] = $_SESSION['langNome'];
}

// valor default
if (!isset($_SESSION['langNome']) || ((!strlen($_SESSION['langNome']) > 0)and(!strlen($_REQUEST['nome']) > 0))){
	$_SESSION['langNome'] = 'pt';
}

require_once $raiz_do_projeto.'public_html/sys/includes/language/eprepag_lang_'.$_SESSION['langNome'].'.inc.php';
//echo "LANG_DIR: ".$_SERVER['DOCUMENT_ROOT'].'\incs\lang\eprepag_lang_'.$_SESSION['langNome'].'.inc.php\n';

@$eprepag['charsets'] = array(
    'UTF-8/' => 'UTF-8',
    ''        => CHARSET_NATIVE
);

//echo "defined('IN_installer'): " . defined('IN_installer') . "<br>";

if (defined('IN_installer') && IS_installed === false) {
    $eprepag['lang'] = $eprepag['autolang'];
//echo "eprepag['lang']: " . $eprepag['lang'] . "<br>";
    return 1;
}

?>