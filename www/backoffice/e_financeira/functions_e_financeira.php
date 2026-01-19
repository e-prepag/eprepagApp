<?php
require_once __DIR__ . "/../../class/classGerarEFinanceira.php";

function compararDatas($data_inicial, $data_final)
{
    if ($data_inicial == "" || $data_final == "") {
        return 0;
    }
    try {
        $d1 = new DateTime($data_inicial);
        $d2 = new DateTime($data_final);

        if ($d1 < $d2) {
            return 1;   // data inicial menor (normal)
        } else {
            return -1;  // data inicial maior ou igual
        }
    } catch (Exception $e) {
        return 0; // erro na data
    }
}
function gerarXmlMovimentacao($data_inicial, $data_final)
{
    if (compararDatas($data_inicial, $data_final) < 1) {
        return [];
    }

    $efinanceira = new GerarEFinanceira();

    $movimentacoes = $efinanceira->gerarMovimentacaoFinanceiraCompleta($data_inicial, $data_final);

    $xmls = $efinanceira->gerarLotesMovsFinanceira($movimentacoes);



    return $xmls;
}

function xmlViewer($xmlString, $id = 'xmlViewer')
{
    if (empty($xmlString)) {
        return '<div class="alert alert-warning">XML vazio</div>';
    }

    return <<<HTML
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>Visualizador de XML id: $id</strong>
        <div>
            <button class="btn btn-sm btn-outline-secondary me-2" onclick="copiarXml('$id')">
                Copiar
            </button>
            <button class="btn btn-sm btn-outline-secondary" onclick="toggleXml('$id')">
                Expandir / Colapsar
            </button>
        </div>
    </div>
    <div class="card-body">
        <pre class="xml-box" id="$id"><code class="language-xml">$xmlString</code></pre>
    </div>
</div>
HTML;
}

function gerarZipCripto(array $lotes, string $nomeZip): string
{
    $dirTemp = sys_get_temp_dir();
    $zipPath = $dirTemp . '/' . $nomeZip;

    $zip = new ZipArchive();

    $efinanceira = new GerarEFinanceira();

    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        throw new Exception('Não foi possível criar o ZIP');
    }

    foreach ($lotes as $lote) {

        $nomeArquivo = "lote_{$lote['ano_mes']}_{$lote['lote_numero']}_criptografado_assinado";

        if ($lote['xml'] instanceof DOMDocument) {
            $conteudo = $lote['xml']->saveXML();
        } elseif (is_string($lote['xml'])) {
            $conteudo = $lote['xml'];
        } else {
            throw new Exception('XML inválido para o arquivo ' . $nomeArquivo);
        }

        $lote_assinado = $efinanceira->assinarLoteEventos($conteudo);
        $lote_criptografado = $efinanceira->criptografarLoteEF($lote_assinado);

        $zip->addFromString($nomeArquivo . '.xml', $lote_criptografado);
    }

    $zip->close();

    return $zipPath;
}

function gerarZipLotes(array $lotes, string $nomeZip): string
{
    $dirTemp = sys_get_temp_dir();
    $zipPath = $dirTemp . '/' . $nomeZip;

    $zip = new ZipArchive();

    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        throw new Exception('Não foi possível criar o ZIP');
    }

    foreach ($lotes as $lote) {

        $nomeArquivo = "lote_{$lote['ano_mes']}_{$lote['lote_numero']}";

        if ($lote['xml'] instanceof DOMDocument) {
            $conteudo = $lote['xml']->saveXML();
        } elseif (is_string($lote['xml'])) {
            $conteudo = $lote['xml'];
        } else {
            throw new Exception('XML inválido para o arquivo ' . $nomeArquivo);
        }

        $zip->addFromString($nomeArquivo . '.xml', $conteudo);
    }

    $zip->close();

    return $zipPath;
}

function enviarLotesEfinanceira(array $lotes, string $nomeZip): string
{
    $dirTemp = sys_get_temp_dir();
    $zipPath = $dirTemp . '/' . $nomeZip;

    $zip = new ZipArchive();

    $efinanceira = new GerarEFinanceira();

    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        throw new Exception('Não foi possível criar o ZIP');
    }

    foreach ($lotes as $lote) {

        $nomeArquivo = "lote_{$lote['ano_mes']}_{$lote['lote_numero']}_resposta_efinanceira";

        if ($lote['xml'] instanceof DOMDocument) {
            $conteudo = $lote['xml']->saveXML();
        } elseif (is_string($lote['xml'])) {
            $conteudo = $lote['xml'];
        } else {
            throw new Exception('XML inválido para o arquivo ' . $nomeArquivo);
        }

        $lote_assinado = $efinanceira->assinarLoteEventos($conteudo);
        $lote_criptografado = $efinanceira->criptografarLoteEF($lote_assinado);

        $resposta_efin = $efinanceira->enviarLoteEFinanceira($lote_criptografado);

        $zip->addFromString($nomeArquivo . '.xml', $resposta_efin);
    }

    $zip->close();

    return $zipPath;
}
