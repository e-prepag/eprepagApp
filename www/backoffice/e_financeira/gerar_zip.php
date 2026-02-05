<?php
require __DIR__ . '/functions_e_financeira.php';

if ($_GET['acao'] == "movimentacoes") {

    $data_inicial = isset($_GET['data_inicial']) ? urldecode($_GET['data_inicial']) : date('Y-m', strtotime('-1 Month'));
    $data_final = isset($_GET['data_final']) ? urldecode($_GET['data_final']) : date('Y-m');

    $efinanceira = new GerarEFinanceira();
    $lotes = $efinanceira->gerarXmlMovimentacao($data_inicial, $data_final);

    if ($lotes) {
        $caminho = gerarZipLotes($lotes, "lotes_{$data_final}_{$data_inicial}_" . date('Ymd_Hi') . ".zip");

        // Validação básica
        if (!$caminho || !file_exists($caminho)) {
            http_response_code(404);
            exit('Arquivo não encontrado');
        }

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $arquivo . '"');
        header('Content-Length: ' . filesize($caminho));
        header('Cache-Control: no-cache');

        readfile($caminho);
        unlink($caminho);
    } else {
        echo "Erro ao gerar o arquivo";
    }
}else if ($_GET['acao'] == "abertura") {

}else if ($_GET['acao'] == "fechamento") {

}
