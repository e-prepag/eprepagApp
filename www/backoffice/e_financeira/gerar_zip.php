<?php
require __DIR__ . '/functions_e_financeira.php';

if ($_REQUEST['acao'] == "movimentacoes") {
    set_time_limit(0); // Impede que o script pare por tempo em processos longos

    $data_inicial = isset($_GET['data_inicial']) ? urldecode($_GET['data_inicial']) : date('Y-m', strtotime('-1 Month'));
    $data_final = isset($_GET['data_final']) ? urldecode($_GET['data_final']) : date('Y-m');
    $tipo_doc = $_GET['tipo_doc'] ?? '';
    $cpfcnpj = $_GET['cpfcnpj'] ?? '';

    $param_tipo_doc = ($tipo_doc === 'todos' || empty($tipo_doc)) ? null : $tipo_doc;
    $param_cpfcnpj = empty($cpfcnpj) ? null : $cpfcnpj;

    $efinanceira = new GerarEFinanceira();

    // 1. Criar pasta temporária exclusiva para este processamento
    $session_id = uniqid();
    $temp_dir = "/www/arquivos_gerados/efinanceira/temp_zip_{$session_id}/";
    if (!is_dir($temp_dir)) mkdir($temp_dir, 0755, true);

    $limit = 8000;
    $offset = 0;
    $total_processado = 0;
    $arquivos_temporarios = [];

    // 2. Loop de processamento em lotes (Batch Processing)
    while (true) {
        // Busca apenas 50 registros por vez
        $lotes = $efinanceira->gerarXmlMovimentacao($data_inicial, $data_final, $limit, $offset, $param_tipo_doc, $param_cpfcnpj);

        // Se não voltou nada, encerra o loop
        if (!$lotes || empty($lotes['xmls'])) {
            break;
        }

        foreach ($lotes['xmls'] as $item) {
            $nome_xml = "{$item['ano_mes']}_lote_{$item['lote_numero']}_" . uniqid() . ".xml";
            $caminho_xml = $temp_dir . $nome_xml;

            // Salva o XML direto no disco e remove da memória
            file_put_contents($caminho_xml, $item['xml']);
            $arquivos_temporarios[] = $caminho_xml;

            unset($item['xml']); // Limpa a string pesada da memória
        }

        $total_processado += count($lotes['xmls']);
        $offset += $limit;

        // Limpa as variáveis do lote para a próxima rodada
        unset($lotes);
        error_log("Foi $total_processado \n");
        // Se a quantidade retornada for menor que o limite, significa que acabou a base
        // (Isso depende de como sua função gerarXmlMovimentacao responde, se ela não trouxer o total_eventos total)
    }

    // 3. Compactar os arquivos gerados
    if ($total_processado > 0) {
        $nome_zip = "lotes_{$data_final}_{$data_inicial}_" . date('Ymd_Hi') . "_{$total_processado}.zip";
        $caminho_zip = "/www/arquivos_gerados/efinanceira/lotes_enviados/" . $nome_zip;

        $zip = new ZipArchive();
        if ($zip->open($caminho_zip, ZipArchive::CREATE) === TRUE) {
            foreach ($arquivos_temporarios as $arquivo) {
                // Adiciona ao zip usando apenas o nome do arquivo (sem o caminho da pasta temp)
                $zip->addFile($arquivo, basename($arquivo));
            }
            $zip->close();

            // 4. Limpar arquivos temporários XML (já estão no ZIP)
            foreach ($arquivos_temporarios as $arquivo) {
                unlink($arquivo);
            }
            rmdir($temp_dir);

            // 5. Enviar para download
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $nome_zip . '"');
            header('Content-Length: ' . filesize($caminho_zip));
            header('Cache-Control: no-cache');

            readfile($caminho_zip);
            unlink($caminho_zip); // Apaga o ZIP do servidor após o download
            error_log("Uso maximo: " . round(memory_get_peak_usage(true) / 1024 / 1024, 2) . "mb");
            exit;
        } else {
            echo "Erro ao criar o arquivo ZIP.";
        }
    } else {
        // Limpa a pasta caso não tenha gerado nada
        if (is_dir($temp_dir)) rmdir($temp_dir);
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
