<?php
require_once '/www/includes/constantes.php';
// Adicione aqui sua validação de sessão/login se necessário, para evitar downloads anônimos

$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';
// A função basename garante que pegaremos APENAS o nome do arquivo, removendo qualquer barra ou diretório injetado
$arquivo_original = isset($_GET['arquivo']) ? basename($_GET['arquivo']) : '';

if (empty($arquivo_original) || empty($tipo)) {
    die("Parâmetros inválidos.");
}

$caminho_base_lotes = '/www/arquivos_gerados/efinanceira/lotes_enviados/';
$caminho_base_respostas = '/www/arquivos_gerados/efinanceira/respostas_envio/';

$caminho_final = '';
$nome_download = '';

if ($tipo === 'lote') {
    $caminho_final = $caminho_base_lotes . $arquivo_original;
    $nome_download = $arquivo_original;

} elseif ($tipo === 'resposta') {
    // Separa o nome e a extensão para injetar o "_retorno"
    $info_arquivo = pathinfo($arquivo_original);
    $nome_base = $info_arquivo['filename']; // ex: lote_202302_1
    $extensao = isset($info_arquivo['extension']) ? '.' . $info_arquivo['extension'] : '.xml';
    
    $nome_arquivo_resposta = $nome_base . '_retorno' . $extensao;
    
    $caminho_final = $caminho_base_respostas . $nome_arquivo_resposta;
    $nome_download = $nome_arquivo_resposta;
} else {
    die("Tipo de arquivo não reconhecido.");
}

// Verifica se o arquivo físico realmente existe no servidor
if (!file_exists($caminho_final)) {
    die("Erro: O arquivo solicitado não foi encontrado no servidor.");
}

// Cabeçalhos para forçar o download do arquivo
header('Content-Description: File Transfer');
header('Content-Type: application/xml'); // Como são XMLs, este é o mime-type correto
header('Content-Disposition: attachment; filename="' . $nome_download . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($caminho_final));

// Lê o arquivo e joga para o navegador
readfile($caminho_final);
exit;