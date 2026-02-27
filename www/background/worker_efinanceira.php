<?php
require_once '/www/includes/constantes.php';
require_once "/www/backoffice/e_financeira/functions_e_financeira.php";
// Extrai as variáveis do JSON de parâmetros

function workerGerarZipEfinanceira($parametros) {
    $data_inicial = $parametros['data_inicial'] ?? '';
    $data_final = $parametros['data_final'] ?? '';
    $tipo_doc = $parametros['tipo_doc'] ?? '';
    $cpfcnpj = $parametros['cpfcnpj'] ?? '';

    $param_tipo_doc = ($tipo_doc === 'todos' || empty($tipo_doc)) ? null : $tipo_doc;
    $param_cpfcnpj = empty($cpfcnpj) ? null : $cpfcnpj;

    $efinanceira = new GerarEFinanceira();

    $session_id = uniqid();
    $temp_dir = "/www/arquivos_gerados/efinanceira/temp_zip_{$session_id}/";
    if (!is_dir($temp_dir)) mkdir($temp_dir, 0755, true);

    $limit = 5000;
    $offset = 0;
    $total_processado = 0;
    $arquivos_temporarios = [];

    while (true) {
        $lotes = $efinanceira->gerarXmlMovimentacao($data_inicial, $data_final, $limit, $offset, $param_tipo_doc, $param_cpfcnpj);

        if (!$lotes || empty($lotes['xmls'])) {
            break;
        }

        foreach ($lotes['xmls'] as $item) {
            $nome_xml = "{$item['ano_mes']}_lote_{$item['lote_numero']}_" . uniqid() . ".xml";
            $caminho_xml = $temp_dir . $nome_xml;

            file_put_contents($caminho_xml, $item['xml']);
            $arquivos_temporarios[] = $caminho_xml;
            unset($item['xml']); 
        }

        $total_processado += count($lotes['xmls']);
        $offset += $limit;
        unset($lotes);
        
        echo "Lote processado. Total atual: $total_processado \n";
    }

    if ($total_processado > 0) {
        $nome_zip = "lotes_{$data_final}_{$data_inicial}_" . date('Ymd_Hi') . "_{$total_processado}.zip";
        
        // Pasta acessível publicamente ou mapeada pelo seu servidor web
        $caminho_fisico_zip = "/www/arquivos_gerados/efinanceira/lotes_enviados/" . $nome_zip;
        // A URL que será devolvida ao usuário para ele clicar e baixar
        $url_download = "/arquivos_gerados/efinanceira/lotes_enviados/" . $nome_zip;

        $zip = new ZipArchive();
        if ($zip->open($caminho_fisico_zip, ZipArchive::CREATE) === TRUE) {
            foreach ($arquivos_temporarios as $arquivo) {
                $zip->addFile($arquivo, basename($arquivo));
            }
            $zip->close();

            // Limpeza
            foreach ($arquivos_temporarios as $arquivo) {
                unlink($arquivo);
            }
            rmdir($temp_dir);

            return $url_download; // Retorna o caminho para a função principal atualizar o BD
        } else {
            throw new Exception("Erro ao criar o arquivo ZIP no disco.");
        }
    } else {
        if (is_dir($temp_dir)) rmdir($temp_dir);
        throw new Exception("Nenhum evento encontrado para gerar o ZIP.");
    }
}
