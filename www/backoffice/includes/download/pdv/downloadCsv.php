<?php
require_once '../../../../includes/constantes.php';

if (isset($_GET["csv"])) {
    
    // CORREÇÃO 1: Usa basename() para isolar o nome do arquivo
    // Isso remove qualquer tentativa de Path Traversal (ex: '../')
    $safe_csv_filename = basename($_GET['csv']);
    $full_path = $raiz_do_projeto . "arquivos_gerados/csv/" . $safe_csv_filename;

    // A verificação agora usa o caminho seguro
    if (file_exists($full_path)) {
        header('Content-Type: application/csv; charset=iso-8859-1');
        header('Content-Disposition: attachment; filename=relatorio.csv');
        header('Pragma: no-cache');
        
        // A leitura também usa o caminho seguro
        $str = file_get_contents($full_path);
        
        echo $str;
    } else {
        echo "Erro: Arquivo não encontrado.";
    }

} elseif (isset($_REQUEST['filename'])) {
    
    // CORREÇÃO 2: Usa basename() também no nome do arquivo do header
    // Isso previne Path Traversal no nome do arquivo salvo e
    // mitiga ataques de injeção de header.
    $safe_filename = basename($_REQUEST['filename']);
    
    header("Content-Description: File Transfer");
    header('Content-Type: application/octet-stream;');
    // Usa o nome seguro no header
    header('Content-Disposition: attachment; filename="' . $safe_filename . '"');
    
    echo base64_decode($_REQUEST['content']);
    
} else {
    echo "Erro na geração do CSV";
}
?>