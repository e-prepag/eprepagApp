<?php require_once __DIR__ . '/constantes_url.php'; ?>
<?php

$server_url = EPREPAG_URL;
$server_url_ep = EPREPAG_URL_HTTPS;
$server_url_bo = getenv("BACKOFFICE_URL");
$server_port = '';
$server_url_complete = getenv("BACKOFFICE_URL");

?>