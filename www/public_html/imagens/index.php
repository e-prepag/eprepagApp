<?php

// ---------------------------------------------------
// CONFIGURAÇÃO
// ---------------------------------------------------

// Pasta raiz onde ficam as imagens
$BASE = "/www/arquivos_gerados/imagens";

// Lista de pastas permitidas (whitelist absoluta)
$ALLOWED_PATHS = array(
    realpath("$BASE/banners"),
    realpath("$BASE/gamer/produtos"),
    realpath("$BASE/pdv/produtos"),
);

// Extensões permitidas
$ALLOWED_EXT = array("jpg", "jpeg", "png", "gif");

// ---------------------------------------------------
// PEGAR A URL LIMPA
// ---------------------------------------------------

// Remove /api/imagem/ da URL
$path = str_replace("/imagens/", "", $_SERVER["REQUEST_URI"]);
$path = strtok($path, "?"); // remove query
$path = trim($path, "/");
// Segurança extra contra path traversal
$path = str_replace(array("../", "./"), "", $path);

// ---------------------------------------------------
// VALIDAR EXTENSÃO
// ---------------------------------------------------

$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

if (!in_array($ext, $ALLOWED_EXT)) {
    http_response_code(415);
    exit("Tipo de arquivo não permitido");
}

// ---------------------------------------------------
// MONTAR CAMINHO REAL DO ARQUIVO
// ---------------------------------------------------
$full_path = realpath("$BASE/$path");

if (!$full_path || !file_exists($full_path)) {
    http_response_code(404);
    exit("Arquivo não encontrado");
}

// ---------------------------------------------------
// VERIFICAR SE O ARQUIVO ESTÁ DENTRO DE UM DIRETÓRIO PERMITIDO
// ---------------------------------------------------

$allowed = false;

foreach ($ALLOWED_PATHS as $allowed_dir) {
    if ($allowed_dir !== false && strpos($full_path, $allowed_dir) === 0) {
        $allowed = true;
        break;
    }
}

if (!$allowed) {
    http_response_code(403);
    exit("Acesso negado");
}

// ---------------------------------------------------
// VALIDAR MIME REAL DA IMAGEM
// ---------------------------------------------------

$mime = mime_content_type($full_path);

if (strpos($mime, "image/") !== 0) {
    http_response_code(415);
    exit("Arquivo não é uma imagem válida");
}

// ---------------------------------------------------
// RETORNAR IMAGEM COM CABEÇALHO CORRETO
// ---------------------------------------------------

header("Content-Type: $mime");
header("Content-Length: " . filesize($full_path));

readfile($full_path);
exit;
