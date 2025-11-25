<?php

if (!isset($raiz_do_projeto)) {
    require_once '../../includes/constantes.php';
}

// Lista branca de diretórios permitidos
$dir = array(
    "cache" => $raiz_do_projeto . "arquivos_gerados/csv/",
    "bkov"  => $raiz_do_projeto . "arquivos_gerados/csv/"
);

if (isset($_GET["downloadCsv"]) && $_GET["downloadCsv"] == 1) {
    ob_end_clean();
    ob_clean();
    header("Content-type: text/csv");
    header("Content-Disposition: attachment; filename=report_e-prepag.csv");
    header("Pragma: no-cache");
    header("Expires: 0");
    echo $objCsv->getCsv();
    die;
    
} elseif (isset($_GET["csv"]) && isset($_GET["dir"])) {
    
    // 1. VALIDAR que o diretório está na lista branca
    if (!array_key_exists($_GET["dir"], $dir)) {
        die("Erro: Diretório inválido.");
    }
    
    $baseDir = $_GET["dir"];
    $csvFile = $_GET["csv"];
    
    // 2. SANITIZAR o nome do arquivo (remover path traversal)
    $csvFile = basename($csvFile);
    
    // 3. REMOVER caracteres perigosos
    $csvFile = preg_replace('/[^a-zA-Z0-9._-]/', '', $csvFile);
    
    // 4. VALIDAR extensão .csv
    if (substr($csvFile, -4) !== '.csv') {
        die("Erro: Formato de arquivo inválido. Deve ser CSV.");
    }
    
    // 5. RESOLVER caminho real do diretório base
    $basePath = realpath($dir[$baseDir]);
    
    if ($basePath === false) {
        die("Erro: Diretório base não existe.");
    }
    
    // 6. CONSTRUIR caminho completo do arquivo
    $fullPath = $basePath . DIRECTORY_SEPARATOR . $csvFile;
    
    // 7. RESOLVER caminho real do arquivo
    $resolvedPath = realpath($fullPath);
    
    // 8. VERIFICAR que o arquivo existe E está dentro do diretório permitido
    if ($resolvedPath === false || strpos($resolvedPath, $basePath) !== 0) {
        die("Erro: Arquivo não encontrado ou acesso negado.");
    }
    
    // 9. VERIFICAR novamente a extensão após realpath
    if (pathinfo($resolvedPath, PATHINFO_EXTENSION) !== 'csv') {
        die("Erro: Formato de arquivo inválido.");
    }
    
    // 10. Arquivo validado - proceder com download
    header('Content-Type: application/csv; charset=iso-8859-1');
    header('Content-Disposition: attachment; filename=report_e-prepag.csv');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    $str = file_get_contents($resolvedPath);
    
    if ($str === false) {
        die("Erro ao ler o arquivo.");
    }
    
    echo $str;
    die;
    
} else {
    die("Erro na geração do CSV");
}