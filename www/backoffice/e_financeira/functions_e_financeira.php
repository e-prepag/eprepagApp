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

function xmlViewer($xmlString, $id = 'xmlViewer')
{
    if (empty($xmlString)) {
        return '<div class="alert alert-warning">XML vazio</div>';
    }

    $xml_formatado = utf8_decode(htmlspecialchars(formatarXml($xmlString)));

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
        <pre class="xml-box" id="$id"><code class="language-xml">$xml_formatado</code></pre>
    </div>
</div>
HTML;
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

                        foreach ($eventos as $evento) {
                            $idAttribute = (string)$evento['id'];

                            if ($idAttribute) {
                                // SUA LOGICA DE UPDATE
                                $linhasAfetadas = $efinanceira->atualizarEnvioParaEnviado(
                                    $idAttribute,
                                    $protocolo,
                                    $nomeArquivoOriginal
                                );

                                if ($linhasAfetadas > 0) {
                                    echo "<span style='color:green'>Evento $idAttribute atualizado.</span><br>";
                                } else {
                                    echo "<span style='color:orange'>Evento $idAttribute não atualizado (ID não encontrado?).</span><br>";
                                }
                            }
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
