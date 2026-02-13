<?php
require_once __DIR__ . "/../../class/classGerarEFinanceira.php";

function formatarXml(string $xmlBruto): string
{
    // Cria o objeto DOM
    $dom = new DOMDocument('1.0', 'UTF-8');

    // Preserva espaços em branco originais? Não, queremos formatar do zero.
    $dom->preserveWhiteSpace = false;

    // Ativa a formatação automática (identação)
    $dom->formatOutput = true;

    // Carrega a string XML
    // O @ serve para suprimir avisos caso o XML esteja malformado
    if (!@$dom->loadXML($xmlBruto)) {
        throw new Exception("XML inválido fornecido para formatação.");
    }

    return $dom->saveXML();
}

function xmlViewer($xmlString, $id = 'xmlViewer', $utf8 = false, $download = false)
{
    if (empty($xmlString)) {
        return '<div class="alert alert-warning">XML vazio</div>';
    }

    $xml_formatado = formatarXml($xmlString);

    if ($utf8) {
        $xml_formatado = htmlspecialchars($xml_formatado);
    } else {
        $xml_formatado = utf8_decode(htmlspecialchars($xml_formatado));
    }

    // O htmlspecialchars transformou os acentos (ex: &#xE1;) em &amp;#xE1;
    // Esse comando reverte APENAS as entidades numéricas (acentos) para que o navegador as renderize,
    $xml_formatado = preg_replace('/&amp;(#\d+|#[xX][0-9a-fA-F]+);/', '&$1;', $xml_formatado);

    $botaoDownloadHtml = '';

    if ($download) {
        // Gera um nome seguro para o arquivo baseado no ID
        $nomeArquivo = 'xml_download_' . preg_replace('/[^a-zA-Z0-9_-]/', '', $id) . '.xml';

        // Cria o HTML do botão
        $botaoDownloadHtml = <<<HTML
        <button type="button" class="btn btn-sm btn-outline-primary me-2" onclick="baixarXml('$id', '$nomeArquivo')" title="Baixar arquivo XML">
            <i class="fa fa-download"></i> Baixar
        </button>
HTML;
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
            $botaoDownloadHtml
        </div>
    </div>
    <div class="card-body">
        <pre class="xml-box" id="$id"><code class="language-xml">$xml_formatado</code></pre>
    </div>
</div>
HTML;
}

function extrairProtocoloEFinanceira($xmlString)
{
    // 1. Carrega o XML
    // LIBXML_NOCDATA é opcional, mas ajuda se houver blocos CDATA futuramente
    $xml = simplexml_load_string($xmlString, "SimpleXMLElement", LIBXML_NOCDATA);

    if ($xml === false) {
        return "Erro: XML inválido ou corrompido.";
    }

    // 2. Registra o Namespace
    // O link deve ser EXATAMENTE igual ao que está no xmlns do cabeçalho do XML
    $namespaceUrl = "http://www.eFinanceira.gov.br/schemas/retornoSolicitacaoConsultaAssincrona/v1_0_0";
    $xml->registerXPathNamespace('ns', $namespaceUrl);

    // 3. Busca o valor usando XPath
    // //ns: significa "busque em qualquer profundidade dentro desse namespace"
    $resultado = $xml->xpath('//ns:protocoloConsulta');

    // 4. Retorna o valor string ou null se não achar
    if (!empty($resultado)) {
        return (string)$resultado[0];
    } else {
        return null;
    }
}

function gerarRelatorioPorCompetencia(array $dados)
{
    if (empty($dados)) {
        return '<div class="alert alert-warning">Nenhum dado para exibir.</div>';
    }

    // CSS para deixar o relatório visualmente hierárquico
    $html = '<style>
        .rel-container { font-family: "Segoe UI", Arial, sans-serif; max-width: 1000px; margin: 0 auto; color: #333; }
        
        /* Estilo do Bloco do Mês */
        .mes-section { margin-bottom: 40px; border: 1px solid #268fbd; border-radius: 8px; overflow: hidden; }
        .mes-header { background-color: #268fbd; color: #fff; padding: 15px; font-size: 1.3em; font-weight: bold; text-align: center; text-transform: uppercase; letter-spacing: 1px; }
        
        /* Estilo do Card do Declarado */
        .declarado-card { background-color: #fff; border-bottom: 5px solid #f0f2f5; padding: 20px; }
        .declarado-card:last-child { border-bottom: none; }
        
        .declarado-info { margin-bottom: 15px; border-left: 4px solid #28a745; padding-left: 15px; }
        .declarado-nome { font-size: 1.2em; font-weight: 700; color: #2c3e50; margin: 0 0 5px 0; }
        .declarado-doc { font-size: 0.9em; color: #555; font-weight: 600; background: #e9ecef; padding: 2px 6px; border-radius: 4px; }
        .declarado-end { font-size: 0.85em; color: #666; margin-top: 5px; font-style: italic; }

        /* Tabela de Contas */
        .table-contas { width: 100%; border-collapse: collapse; font-size: 0.9em; margin-top: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .table-contas th { background-color: #f8f9fa; text-align: left; padding: 10px; border-bottom: 2px solid #dee2e6; color: #495057; }
        .table-contas td { padding: 10px; border-bottom: 1px solid #eee; vertical-align: middle; }
        .col-money { text-align: right; font-family: Consolas, monospace; }
        
        .val-entrada { color: #28a745; }
        .val-saida { color: #dc3545; }
        .val-saldo { font-weight: bold; }
        .badge-relacao { background: #17a2b8; color: white; font-size: 0.75em; padding: 2px 5px; border-radius: 3px; }
    </style>';

    $html .= '<div class="rel-container">';

    // 1. Loop Principal: Itera sobre os MESES (Chaves principais do array)
    foreach ($dados as $anoMes => $listaDeclarados) {

        // Formata 202402 para Fevereiro/2024 (ou 02/2024)
        $ano = substr($anoMes, 0, 4);
        $mes = substr($anoMes, 4, 2);
        $dataLabel = "$mes/$ano";

        $html .= '<div class="mes-section">';
        $html .= '  <div class="mes-header">Mês e ano: ' . $dataLabel . '</div>';

        // 2. Loop Secundário: Itera sobre os DECLARADOS dentro daquele mês
        foreach ($listaDeclarados as $registro) {

            $dd = $registro['dadosDeclarado'];

            // Monta Endereço
            $endereco = implode(', ', array_filter([
                $dd['ug_endereco'],
                $dd['ug_numero'],
                $dd['ug_complemento'],
                $dd['ug_bairro'],
                $dd['ug_cidade'] . '-' . $dd['ug_estado'],
                $dd['ug_cep'] ? 'CEP: ' . $dd['ug_cep'] : null
            ]));

            $html .= '<div class="declarado-card">';

            // Dados do Declarado
            $cpf_cnpj = $dd['tipo_declarado'] == 1 ? "CPF" : "CNPJ";
            $html .= '  <div class="declarado-info">';
            $html .= '      <h3 class="declarado-nome">' . htmlspecialchars($dd['nome_declarado']) . '</h3>';
            $html .= '      <span class="declarado-doc">' . $cpf_cnpj . ': ' . htmlspecialchars($dd['ni_declarado']) . '</span>';
            $html .= '      <div class="declarado-end">Endereço: ' . htmlspecialchars($endereco) . '</div>';
            $html .= '  </div>';

            // 3. Tabela de Contas
            if (!empty($registro['contas'])) {
                $html .= '<table class="table-contas">';
                $html .= '<thead>
                            <tr>
                                <th>Conta (ID)</th>
                                <th>Relação</th>
                                <th class="col-money">Entradas</th>
                                <th class="col-money">Saídas</th>
                            </tr>
                          </thead><tbody>';

                foreach ($registro['contas'] as $conta) {
                    $entradas = (float)$conta['entradas'];
                    $saidas   = (float)$conta['saidas'];
                    $saldo    = $entradas - $saidas;

                    $classeSaldo = $saldo >= 0 ? 'val-entrada' : 'val-saida';

                    $html .= '<tr>';
                    $html .= '  <td><strong>' . htmlspecialchars($conta['ug_id']) . '</strong></td>';

                    $tipos = [
                        1 => 'Titular',
                        3 => 'Representante Legal',
                    ];

                    $tipoRelacao = $tipos[$conta['tipo_relacao']] ?? 'Desconhecido';
                    $html .= '  <td><span class="badge-relacao">Tipo: ' . htmlspecialchars($tipoRelacao) . '</span></td>';

                    $html .= '  <td class="col-money val-entrada">R$ ' . number_format($entradas, 2, ',', '.') . '</td>';
                    $html .= '  <td class="col-money val-saida">R$ ' . number_format($saidas, 2, ',', '.') . '</td>';
                    $html .= '</tr>';
                }
                $html .= '</tbody></table>';
            } else {
                $html .= '<p style="color:#999; margin-left:15px;">Sem movimentação de contas.</p>';
            }

            $html .= '</div>'; // Fim declarado-card
        }

        $html .= '</div>'; // Fim mes-section
    }

    $html .= '</div>'; // Fim container

    return $html;
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

function enviarLotesEfinanceira(array $lotes)
{
    $efinanceira = new GerarEFinanceira();

    // Caminhos das pastas
    $pathLotesEnviados = '/www/arquivos_gerados/efinanceira/lotes_enviados';
    $pathRespostas     = '/www/arquivos_gerados/efinanceira/respostas_envio';

    // Garante criação das pastas
    if (!is_dir($pathLotesEnviados)) mkdir($pathLotesEnviados, 0755, true);
    if (!is_dir($pathRespostas))     mkdir($pathRespostas, 0755, true);

    foreach ($lotes as $lote) {

        $nomeArquivoOriginal = $lote['nome'];
        $conteudoXmlOriginal = $lote['conteudo'];

        if (empty($nomeArquivoOriginal) || empty($conteudoXmlOriginal)) {
            echo "Arquivo ou conteúdo vazio, pulando";
            continue;
        }

        try {
            echo "<h4>Processando: $nomeArquivoOriginal</h4>";

            $lote_assinado      = $efinanceira->assinarLoteEventos($conteudoXmlOriginal);
            $lote_criptografado = $efinanceira->criptografarLoteEF($lote_assinado);

            $xmlRespostaString = $efinanceira->enviarLoteEFinanceira($lote_criptografado);

            if ($xmlRespostaString) {

                // Limpa namespaces para facilitar leitura
                $xmlRespLimpo = str_replace('xmlns=', 'ns=', $xmlRespostaString);
                $xmlRespObj   = simplexml_load_string($xmlRespLimpo);

                // --- VERIFICAÇÃO DE STATUS (cdResposta) ---
                $nodeCdResposta = $xmlRespObj->xpath("//*[local-name()='cdResposta']");
                $cdResposta = $nodeCdResposta ? (int)$nodeCdResposta[0] : 0;

                // Pegamos a descrição da resposta principal
                $nodeDescResposta = $xmlRespObj->xpath("//*[local-name()='descResposta']");
                $descResposta = $nodeDescResposta ? (string)$nodeDescResposta[0] : 'Sem descrição';

                // SE FOR SUCESSO (1)
                if ($cdResposta === 1) {

                    // Extrair Protocolo
                    $protocolo = null;
                    $nodeProtocolo = $xmlRespObj->xpath("//*[local-name()='protocoloEnvio']");

                    if (!empty($nodeProtocolo)) {
                        $protocolo = (string)$nodeProtocolo[0];
                    }

                    echo "<div class='alert alert-success'>";
                    echo "Status: <strong>Sucesso</strong><br>";
                    echo "Protocolo: <strong>" . ($protocolo ? $protocolo : "NÃO GERADO") . "</strong>";
                    echo "</div>";

                    if ($protocolo) {
                        // 1. Salvar Arquivos
                        file_put_contents(
                            $pathLotesEnviados . DIRECTORY_SEPARATOR . $nomeArquivoOriginal,
                            $lote_assinado
                        );

                        $nomeArquivoResposta = pathinfo($nomeArquivoOriginal, PATHINFO_FILENAME) . "_resposta.xml";
                        file_put_contents(
                            $pathRespostas . DIRECTORY_SEPARATOR . $nomeArquivoResposta,
                            $xmlRespostaString
                        );

                        // 2. Atualizar Banco de Dados
                        $xmlLoteLimpo = str_replace(['xmlns=', 'eFinanceira:'], ['ns=', ''], $conteudoXmlOriginal);
                        $xmlLoteObj   = simplexml_load_string($xmlLoteLimpo);

                        $eventos = $xmlLoteObj->xpath("//evento");

                        $idsParaAtualizar = [];
                        foreach ($eventos as $evento) {
                            $idString = (string)$evento['id'];

                            // Remove o prefixo
                            $idLimpo = (int) substr($idString, 3);

                            if ($idLimpo) {
                                $idsParaAtualizar[] = $idLimpo;
                            }
                        }
                        if (!empty($idsParaAtualizar)) {
                            try {
                                $totalAtualizados = $efinanceira->atualizarLoteParaEnviado(
                                    $idsParaAtualizar,
                                    $protocolo,
                                    $nomeArquivoOriginal
                                );

                                echo "<span style='color:green'>Sucesso! Total de eventos atualizados: <strong>$totalAtualizados</strong></span><br>";
                            } catch (Exception $e) {
                                echo "<span style='color:red'>Erro ao atualizar lote: " . $e->getMessage() . "</span><br>";
                            }
                        } else {
                            echo "<span style='color:orange'>Nenhum ID válido encontrado para atualizar.</span><br>";
                        }
                    } else {
                        echo "<div class='alert alert-warning'>Atenção: Protocolo não encontrado apesar do sucesso. Nada salvo.</div>";
                    }
                } else {
                    // --- SE FOR ERRO (DIFERENTE DE 1) ---
                    echo "<div class='alert alert-danger'>";
                    echo "<h4>Erro no Envio (Cód: " . utf8_decode($cdResposta) . ")</h4>";
                    echo "<p><strong>Mensagem:</strong> " . utf8_decode($descResposta) . "</p>";

                    // Busca ocorrências de erro
                    $ocorrencias = $xmlRespObj->xpath("//*[local-name()='ocorrencia']");

                    if (!empty($ocorrencias)) {
                        echo "<hr><strong>Detalhes das Ocorrências:</strong><ul>";
                        foreach ($ocorrencias as $oco) {
                            echo "<li>";
                            echo "<strong>Código:</strong> " . utf8_decode($oco->codigo) . "<br>";
                            echo "<strong>Tipo:</strong> " . utf8_decode($oco->tipo) . "<br>";
                            echo "<strong>Descrição:</strong> " . utf8_decode($oco->descricao);
                            echo "</li><br>";
                        }
                        echo "</ul>";
                    }
                    echo "</div>";
                    echo "<strong>Atenção:</strong> O banco de dados NÃO foi atualizado e os arquivos NÃO foram salvos devido ao erro.";
                }

                // Visualização do XML de Resposta (Sempre útil, mesmo com erro)
                echo xmlViewer($xmlRespostaString, "Resposta Recebida ($nomeArquivoOriginal)");
            } else {
                echo "<div class='alert alert-warning'>Sem resposta do servidor para o arquivo $nomeArquivoOriginal</div>";
            }
        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>Erro crítico ao processar $nomeArquivoOriginal: " . $e->getMessage() . "</div>";
            echo "<pre>" . htmlentities($conteudoXmlOriginal) . "</pre>";
        }

        echo "<hr>";
    }
}

function extrairZip(string $caminhoZip, string $destino): array
{
    // Verifica se a extensão Zip está habilitada no PHP
    if (!class_exists('ZipArchive')) {
        throw new Exception('A extensão ZipArchive não está habilitada no PHP.');
    }

    $zip = new ZipArchive;
    $res = $zip->open($caminhoZip);

    if ($res !== TRUE) {
        throw new Exception("Não foi possível abrir o arquivo ZIP. Código de erro: $res");
    }

    // Cria o diretório de destino se não existir
    if (!is_dir($destino)) {
        if (!mkdir($destino, 0755, true)) {
            $zip->close();
            throw new Exception("Falha ao criar o diretório de destino: $destino");
        }
    }

    // Garante que o caminho de destino é absoluto e normalizado para segurança
    $destinoReal = realpath($destino);
    $arquivosExtraidos = [];

    // Itera sobre cada arquivo dentro do ZIP
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $nomeArquivo = $zip->getNameIndex($i);

        // Verifica se é um diretório (termina com /) e ignora,
        if (substr($nomeArquivo, -1) == '/') {
            continue;
        }

        // Previne que arquivos sejam extraídos fora da pasta de destino 
        $caminhoCompleto = $destinoReal . DIRECTORY_SEPARATOR . $nomeArquivo;

        // Normaliza o caminho para verificação
        $diretorioPai = dirname($caminhoCompleto);

        // Nota: Esta é uma verificação básica. O método extractTo do PHP moderno já possui proteções,
        // mas validar manualmente é uma boa prática.
        if (strpos(realpath($diretorioPai), $destinoReal) !== 0) {
            continue; // Pula arquivo suspeito
        }

        // Extrai apenas este arquivo específico
        if ($zip->extractTo($destino, $nomeArquivo)) {
            $arquivosExtraidos[] = $nomeArquivo;
        }
    }

    $zip->close();

    return $arquivosExtraidos;
}

function obterXmlFromZip($arquivo)
{
    if (isset($_FILES[$arquivo])) {

        $arquivoUpload = $_FILES[$arquivo];

        if ($arquivoUpload['error'] !== UPLOAD_ERR_OK) {
            die("Erro no upload do arquivo.");
        }

        // Cria uma pasta temporária única
        $pastaDestino = '/tmp/temp_' . uniqid();
        $caminhoTemporarioZip = $arquivoUpload['tmp_name'];

        try {
            $listaArquivos = extrairZip($caminhoTemporarioZip, $pastaDestino);

            $xmls = [];
            foreach ($listaArquivos as $nomeArquivo) {

                $extensao = strtolower(pathinfo($nomeArquivo, PATHINFO_EXTENSION));

                if ($extensao !== 'xml') {
                    continue;
                }

                $caminhoCompletoXml = $pastaDestino . DIRECTORY_SEPARATOR . $nomeArquivo;
                $conteudoXml = file_get_contents($caminhoCompletoXml);

                if ($conteudoXml === false) {
                    echo "Erro ao ler o arquivo: $nomeArquivo <br>";
                    continue;
                }

                $xmls[] = [
                    'nome' => $nomeArquivo,
                    'conteudo' => $conteudoXml
                ];

                unlink($caminhoCompletoXml);
            }
            return $xmls;
        } catch (Exception $e) {
            echo "Erro Crítico: " . $e->getMessage();
            return []; // Retorna array vazio em caso de erro para não quebrar o foreach seguinte
        }
    }
    return [];
}

// Função auxiliar para deletar a pasta temp depois (opcional)
function removerDiretorioRecursivo($dir)
{
    if (!is_dir($dir)) return;
    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file) {
        (is_dir("$dir/$file")) ? removerDiretorioRecursivo("$dir/$file") : unlink("$dir/$file");
    }
    return rmdir($dir);
}
