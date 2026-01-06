<?php

// ---------------------------------------------------
// CONFIGURAÇÃO
// ---------------------------------------------------

$BASE = "/www/arquivos_gerados/imagens";

$ALLOWED_PATHS = array(
    realpath("$BASE/banners"),
    realpath("$BASE/gamer/produtos"),
    realpath("$BASE/pdv/produtos"),
);

$ALLOWED_EXT = array("jpg", "jpeg", "png", "gif");
$ALLOWED_MIMES = array(
    "image/jpeg",
    "image/png", 
    "image/gif"
);

// ---------------------------------------------------
// PROCESSAR REQUEST
// ---------------------------------------------------

// Decodifica URL (previne bypass via encoding)
$path = urldecode($_SERVER["REQUEST_URI"]);

// Remove prefixo
$path = str_replace("/imagens/", "", $path);
$path = strtok($path, "?");
$path = trim($path, "/");

// CRÍTICO: Remove caracteres nulos
$path = str_replace("\0", "", $path);
$path = str_replace("%00", "", $path);

// Validação rigorosa contra path traversal
if (preg_match('/\.\./', $path) || 
    preg_match('/\/\./', $path) ||
    preg_match('/\.\//', $path) ||
    strpos($path, chr(0)) !== false) {
    http_response_code(400);
    exit("Caminho inválido");
}

// Remove múltiplas barras
$path = preg_replace('#/+#', '/', $path);

// ---------------------------------------------------
// VALIDAR EXTENSÃO
// ---------------------------------------------------

$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

if (!in_array($ext, $ALLOWED_EXT, true)) {
    http_response_code(415);
    exit("Tipo de arquivo não permitido");
}

// ---------------------------------------------------
// VALIDAR NOME DO ARQUIVO
// ---------------------------------------------------

// Apenas caracteres alfanuméricos, underscore, hífen e ponto
if (!preg_match('/^[\w\-\/\.\s]+$/', $path)) {
    http_response_code(400);
    exit("Nome de arquivo contém caracteres inválidos");
}

// ---------------------------------------------------
// MONTAR E VALIDAR CAMINHO
// ---------------------------------------------------

$full_path = realpath("$BASE/$path");

if ($full_path === false || !file_exists($full_path) || !is_file($full_path)) {
    http_response_code(404);
    exit("Arquivo não encontrado");
}

// ---------------------------------------------------
// VERIFICAR DIRETÓRIO PERMITIDO
// ---------------------------------------------------

$allowed = false;

foreach ($ALLOWED_PATHS as $allowed_dir) {
    if ($allowed_dir !== false && strpos($full_path, $allowed_dir . '/') === 0) {
        $allowed = true;
        break;
    }
}

if (!$allowed) {
    http_response_code(403);
    exit("Acesso negado");
}

// ---------------------------------------------------
// VALIDAR MIME TYPE
// ---------------------------------------------------

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $full_path);
finfo_close($finfo);

if (!in_array($mime, $ALLOWED_MIMES, true)) {
    http_response_code(415);
    exit("Tipo MIME não permitido");
}

// ---------------------------------------------------
// VALIDAR CONTEÚDO DA IMAGEM
// ---------------------------------------------------

// Valida se é realmente uma imagem válida
$image_info = @getimagesize($full_path);
if ($image_info === false) {
    http_response_code(415);
    exit("Arquivo corrompido ou inválido");
}

// ---------------------------------------------------
// HEADERS DE SEGURANÇA
// ---------------------------------------------------

header("Content-Type: $mime");
header("Content-Length: " . filesize($full_path));
header("X-Content-Type-Options: nosniff");
header("Content-Security-Policy: default-src 'none'");
header("X-Frame-Options: DENY");

// Previne cache de arquivos sensíveis (opcional)
// header("Cache-Control: no-store, no-cache, must-revalidate");

// ---------------------------------------------------
// RETORNAR ARQUIVO
// ---------------------------------------------------

readfile($full_path);
exit;