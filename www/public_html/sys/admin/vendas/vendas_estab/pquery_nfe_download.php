<?php

// Validaчуo bсsica
if (empty($_GET['file'])) {
    http_response_code(400);
    exit('Parтmetro invсlido.');
}

// Seguranчa: evita path traversal
$filename = basename($_GET['file']);

// Caminho REAL do arquivo no servidor/container
$baseDir  = '/www/arquivos_gerados/lotes/';
$filePath = $baseDir . $filename;

// Verifica existъncia
if (!file_exists($filePath) || !is_file($filePath)) {
    http_response_code(404);
    exit('Arquivo nуo encontrado.');
}

// Headers para forчar download
header('Content-Description: File Transfer');
header('Content-Type: text/plain; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: public');

// Limpa buffers (evita corrupчуo do arquivo)
if (ob_get_length()) {
    ob_clean();
}
flush();

readfile($filePath);
exit;
