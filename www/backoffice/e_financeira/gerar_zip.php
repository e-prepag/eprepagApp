<?php
require __DIR__ . '/functions_e_financeira.php';

if ($_REQUEST['acao'] == "movimentacoes") {

    $data_inicial = isset($_GET['data_inicial']) ? urldecode($_GET['data_inicial']) : date('Y-m', strtotime('-1 Month'));
    $data_final = isset($_GET['data_final']) ? urldecode($_GET['data_final']) : date('Y-m');

    $tipo_doc = isset($_GET['tipo_doc']) ? urldecode($_GET['tipo_doc']) : '';
    $cpfcnpj = isset($_GET['cpfcnpj']) ? urldecode($_GET['cpfcnpj']) : '';

    $param_tipo_doc = ($tipo_doc === 'todos' || empty($tipo_doc)) ? null : $tipo_doc;
    $param_cpfcnpj = empty($cpfcnpj) ? null : $cpfcnpj;

    $efinanceira = new GerarEFinanceira();

    // Passa os dados. null, null são para o limit e offset (trazendo tudo)
    $lotes = $efinanceira->gerarXmlMovimentacao($data_inicial, $data_final, null, null, $param_tipo_doc, $param_cpfcnpj);

    if ($lotes && !empty($lotes['xmls'])) {
        $nome_arquivo = "lotes_{$data_final}_{$data_inicial}_" . date('Ymd_Hi') . "_{$lotes['total_eventos']}.zip";
        $caminho = gerarZipLotes($lotes['xmls'], $nome_arquivo);

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
        echo "Nenhum evento encontrado para este período ou filtro.";
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
