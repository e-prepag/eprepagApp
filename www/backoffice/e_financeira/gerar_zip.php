<?php
require __DIR__ . '/functions_e_financeira.php';

if ($_REQUEST['acao'] == "movimentacoes") {

    $data_inicial = isset($_GET['data_inicial']) ? urldecode($_GET['data_inicial']) : date('Y-m', strtotime('-1 Month'));
    $data_final = isset($_GET['data_final']) ? urldecode($_GET['data_final']) : date('Y-m');

    $efinanceira = new GerarEFinanceira();
    $lotes = $efinanceira->gerarXmlMovimentacao($data_inicial, $data_final);

    if ($lotes) {
        $nome_arquivo = "lotes_{$data_final}_{$data_inicial}_" . date('Ymd_Hi') . ".zip";
        $caminho = gerarZipLotes($lotes, $nome_arquivo);

        // Validação básica
        if (!$caminho || !file_exists($caminho)) {
            http_response_code(404);
            exit('Arquivo não encontrado');
        }

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $nome_arquivo . '"');
        header('Content-Length: ' . filesize($caminho));
        header('Cache-Control: no-cache');

        readfile($caminho);
        unlink($caminho);
    } else {
        echo "Erro ao gerar o arquivo";
    }
} else if ($_REQUEST['acao'] == "abertura") {

    $data_inicio = $_GET['data_inicio'] ?? '';
    $data_fim    = $_GET['data_fim'] ?? '';

    $efinanceira = new GerarEFinanceira();
    if ($data_inicio && $data_fim) {
        $lotes = $efinanceira->gerarAbertura($data_inicio, $data_fim);

        if ($lotes) {

            header('Content-Type: application/xml; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . "abertura_{$data_inicio}_{$data_fim}_" . date('Ymd_Hi') . ".xml" . '"');
            header('Cache-Control: no-cache, no-store, must-revalidate'); // Limpa cache
            header('Pragma: no-cache');

            echo $efinanceira->gerarLoteAssincrono([$lotes]);
        } else {
            echo "Erro ao gerar o arquivo";
        }
    } else {
        echo "Parametros inválidos";
    }

} else if ($_REQUEST['acao'] == "fechamento") {

    $data_inicio = $_POST['data_inicio'] ?? '';
    $data_fim    = $_POST['data_fim'] ?? '';
    $tem_movimentacoes = $_POST['tem_movimentacoes'] ?? 0;

    $efinanceira = new GerarEFinanceira();
    if ($data_inicio && $data_fim) {
        $lotes = $efinanceira->gerarFechamento($data_inicio, $data_fim, $tem_movimentacoes);

        if ($lotes) {

            header('Content-Type: application/xml; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . "fechamento_{$data_inicio}_{$data_fim}_" . date('Ymd_Hi') . ".xml" . '"');
            header('Cache-Control: no-cache, no-store, must-revalidate'); // Limpa cache
            header('Pragma: no-cache');

            echo $efinanceira->gerarLoteAssincrono([$lotes]);
        } else {
            echo "Erro ao gerar o arquivo";
        }
    } else {
        echo "Parametros inválidos";
    }
}
