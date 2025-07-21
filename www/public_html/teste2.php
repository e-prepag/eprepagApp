<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);  // Exibe todos os tipos de erros

require_once "../includes/load_dotenv.php";

echo json_encode($_ENV);