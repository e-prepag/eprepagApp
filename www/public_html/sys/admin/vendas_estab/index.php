<?php
session_start();
if (empty($_SESSION["iduser_bko_pub"])) {
    echo '<script>alert("Faça login novamente");</script>';
    exit;
}

// ---------------------------------------------------
// CONFIGURAÇÃO
// ---------------------------------------------------

$BASE = "/www/arquivos_gerados";

$ALLOWED_PATHS = array(
    realpath("$BASE/vendas_estab")
);

$ALLOWED_EXT = array("txt");
$ALLOWED_MIMES = array(
    "text/plain"
);

// ---------------------------------------------------
// PROCESSAR REQUEST
// ---------------------------------------------------

// Decodifica URL (previne bypass via encoding)
$path = urldecode($_SERVER["REQUEST_URI"]);

// Remove prefixo
$path = str_replace("/sys/admin/", "", $path);
$path = strtok($path, "?");
$path = trim($path, "/");

// CRÍTICO: Remove caracteres nulos
$path = str_replace("\0", "", $path);
$path = str_replace("%00", "", $path);

// Validação rigorosa contra path traversal
if (
    preg_match('/\.\./', $path) ||
    preg_match('/\/\./', $path) ||
    preg_match('/\.\//', $path) ||
    strpos($path, chr(0)) !== false
) {
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
if (!preg_match('/^[a-zA-Z0-9_\-\/\.]+$/', $path)) {
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

$extension = pathinfo($full_path, PATHINFO_EXTENSION);
$extension = strtolower($extension); // Garante que a checagem não seja sensível a maiúsculas/minúsculas

if (!in_array($extension, $ALLOWED_EXT, true)) {
    http_response_code(415);
    exit("Extensão de arquivo não permitida.");
}

// ---------------------------------------------------
// HEADERS DE SEGURANÇA
// ---------------------------------------------------

header("Content-Type: text/plain");
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
