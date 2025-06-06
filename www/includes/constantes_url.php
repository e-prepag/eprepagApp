<?php
// Constantes para URLs usadas no sistema
require_once "/www/includes/load_dotenv.php";

const EPREPAG_URL_HTTPS = getenv("EPREPAG_URL_HTTPS");
const EPREPAG_URL_HTTP = getenv("EPREPAG_URL_HTTP");
const EPREPAG_URL = getenv("EPREPAG_URL");

const EPREPAG_URL_HTTPS_COM = getenv("EPREPAG_URL_HTTPS_COM");
const EPREPAG_URL_HTTP_COM = getenv("EPREPAG_URL_HTTP_COM");
const EPREPAG_URL_COM = getenv("EPREPAG_URL_COM");

?>