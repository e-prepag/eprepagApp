<?php require_once __DIR__ . '/../../../includes/constantes_url.php'; ?>
<?php

require_once DIR_INCS . "main.php";
require_once DIR_INCS . "pdv/main.php";

$https = 'http' . (($_SERVER['HTTPS']=='on') ? 's' : '');


require_once DIR_INCS . "configIP.php";

$server_url = $https . '://' . (checkIP() ? $_SERVER['SERVER_NAME'] : '' . EPREPAG_URL . '');


define('SITE_URL', $server_url.'/');

define('CSS_DIR', SITE_URL.'css/');
define('JS_DIR', SITE_URL.'js/');
define('BOOTSTRAP_DIR', SITE_URL.'includes/bootstrap/');

define('IMG_EPREPAG_URL', SITE_URL . 'imagens/');
define('IMG_LAN_URL', SITE_URL . 'imagens/pdv/');

define('LAN_DIR', SITE_URL . 'creditos/');
define('GAMER_DIR', SITE_URL . 'game/');
define('BO_DIR', SITE_URL . 'game/');
