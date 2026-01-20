<?php

// Validação básica
if (
    empty($_GET['file']) ||
    empty($_GET['date'])
) {
    http_response_code(400);
    exit('Parâmetros inválidos.');
}

$filename = basename($_GET['file']);
$dateDir  = preg_replace('/[^0-9]/', '', $_GET['date']);

// Caminho REAL no container
$baseDir = '/www/arquivos_gerados/bacen/' . $dateDir . '/';
$filePath = $baseDir . $filename;

// Verifica existência
if (!file_exists($filePath)) {
    http_response_code(404);
    exit('Arquivo não encontrado.');
}

// Headers
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: public');

ob_clean();
flush();

readfile($filePath);
exit;
