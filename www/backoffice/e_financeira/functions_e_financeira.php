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

    // 1. Formata (indenta) o XML
    $xml_formatado = formatarXml($xmlString);

    // 2. Protege as tags HTML (transforma < em &lt;)
    // Removemos o utf8_decode pois ele costuma quebrar caracteres em ambientes UTF-8 modernos
    $xml_formatado = utf8_decode(htmlspecialchars($xml_formatado));

    // 3. O PULO DO GATO:
    // O htmlspecialchars transformou os acentos (ex: &#xE1;) em &amp;#xE1;
    // Esse comando reverte APENAS as entidades numéricas (acentos) para que o navegador as renderize,
    // mantendo as tags (< >) protegidas.
    $xml_formatado = preg_replace('/&amp;(#\d+|#[xX][0-9a-fA-F]+);/', '&$1;', $xml_formatado);

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

function processarRetornoConsultaAssincrona($xmlString, $visualizadorConsulta)
{
    // 1. Carregar o XML removendo a "casca" do CDATA automaticamente (LIBXML_NOCDATA)
    $xml = simplexml_load_string($xmlString, "SimpleXMLElement", LIBXML_NOCDATA);

    if ($xml === false) {
        echo "<div style='color:red; border:1px solid red; padding:10px;'>Erro crítico: XML inválido ou corrompido.</div>";
        return null;
    }

    // 2. Registrar Namespace (Essencial para encontrar as tags)
    $ns = "http://www.eFinanceira.gov.br/schemas/retornoSolicitacaoConsultaAssincrona/v1_0_0";
    $xml->registerXPathNamespace('ns', $ns);

    // 3. Extração de Dados Comuns
    // Helper para pegar string limpa via XPath
    $getXPathString = function ($path) use ($xml) {
        $res = $xml->xpath($path);
        return (!empty($res)) ? (string)$res[0] : null;
    };

    $cdResposta   = (int) $getXPathString('//ns:status/ns:cdResposta');
    $descResposta = $getXPathString('//ns:status/ns:descResposta');
    $tipoConsulta = $getXPathString('//ns:dadosSolicitacaoConsulta/ns:consultaSolicitada');
    $protocolo    = $getXPathString('//ns:dadosSolicitacaoConsulta/ns:protocoloConsulta');
    $dataHora     = $getXPathString('//ns:dadosSolicitacaoConsulta/ns:dhSolicitacaoConsulta');

    // Se o tipo estiver vazio (comum em erros de validação inicial), define um padrão
    if (empty($tipoConsulta)) {
        $tipoConsulta = "Não Identificado / Validação Estrutural";
    }

    // 4. Estrutura de Retorno
    $resultado = [
        'sucesso' => false,
        'codigo_resposta' => $cdResposta,
        'mensagem' => $descResposta,
        'tipo_consulta' => $tipoConsulta,
        'protocolo' => $protocolo,
        'xml_interno' => null, // Onde ficará o CDATA
        'erros' => []
    ];

    echo "<div style='font-family: sans-serif; border: 1px solid #ccc; padding: 15px; margin-bottom: 10px; background: #f9f9f9;'>";
    echo "<strong>Tipo de Consulta:</strong> " . htmlspecialchars($tipoConsulta) . "<br>";
    echo "<strong>Protocolo:</strong> " . ($protocolo ? $protocolo : '<em>Não gerado</em>') . "<br>";
    echo "<strong>Data/Hora:</strong> " . $dataHora . "<br>";
    echo "<hr>";

    // --- LÓGICA DE DECISÃO ---

    // CASO 1: Aguardando Processamento
    if ($cdResposta === 1) {
        echo "<strong style='color: orange;'>[STATUS 1] Aguardando Processamento</strong><br>";
        echo "Mensagem: $descResposta<br>";
        $resultado['sucesso'] = true; // Consideramos sucesso pois foi enfileirado
    }

    // CASO 2: Erro / Ocorrências
    elseif ($cdResposta === 2) {
        echo "<strong style='color: red;'>[STATUS 2] Erro / Ocorrências Encontradas</strong><br>";
        echo "Mensagem Global: $descResposta<br><br>";
        echo "<strong>Lista de Erros:</strong><ul>";

        // Busca as ocorrências
        $ocorrencias = $xml->xpath('//ns:status/ns:ocorrencias/ns:ocorrencia');

        foreach ($ocorrencias as $oc) {
            $codErro  = (string)$oc->children($ns)->codigo;
            $descErro = (string)$oc->children($ns)->descricao;

            // Adiciona ao array de retorno
            $resultado['erros'][] = ['codigo' => $codErro, 'descricao' => $descErro];

            echo "<li style='color: darkred;'><strong>($codErro)</strong> $descErro</li>";
        }
        echo "</ul>";
    }

    // CASO 3: Processado com Sucesso (Tem CDATA)
    elseif ($cdResposta === 3) {
        echo "<strong style='color: green;'>[STATUS 3] Sucesso - Consulta Processada!</strong><br>";

        // Extrai o conteúdo do CDATA (Graças ao LIBXML_NOCDATA, ele já vem como string limpa)
        $xmlResultado = $getXPathString('//ns:xmlResultadoConsulta');

        if ($xmlResultado) {
            $resultado['sucesso'] = true;
            $resultado['xml_interno'] = $xmlResultado; // Salva o XML interno para uso futuro

            switch ($visualizadorConsulta) {
                case 'cadastro':
                    processarRetornoInformacoesCadastrais($xmlResultado);
                    break;
                case 'lista':
                    processarRetornoListaEFinanceira($xmlResultado);
                    break;
                case 'mov_fin':
                    processarRetornoMovOpFin($xmlResultado);
                    break;
            }
        } else {
            echo "<span style='color:red'>Aviso: Status 3 recebido mas tag xmlResultadoConsulta está vazia.</span>";
        }
    }

    // Outros casos não mapeados
    else {
        echo "<strong>Status Desconhecido ($cdResposta):</strong> $descResposta";
    }

    echo "</div>";

    return $resultado;
}

/**
 * Processa o XML específico de Retorno de Informações Cadastrais (v1.3.0)
 * Este XML geralmente vem dentro do CDATA da consulta assíncrona.
 */
function processarRetornoInformacoesCadastrais($xmlString)
{
    // 1. Carregar o XML (Remove CDATA automaticamente)
    $xml = simplexml_load_string($xmlString, "SimpleXMLElement", LIBXML_NOCDATA);

    if ($xml === false) {
        return ['sucesso' => false, 'erro_msg' => 'XML inválido'];
    }

    // =========================================================================
    // SEGURANÇA CONTRA MUDANÇA DE VERSÃO (1.2.0 vs 1.3.0)
    // =========================================================================
    // Pega todos os namespaces declarados no XML recebido.
    // Se a Receita mandar v1.2, pega v1.2. Se mandar v1.3, pega v1.3.
    $namespaces = $xml->getNamespaces(true);

    // Pega o primeiro namespace encontrado (que é o principal do eFinanceira)
    $nsUrl = reset($namespaces);

    // Se por acaso vier sem namespace (improvável), usa um fallback ou string vazia
    if (!$nsUrl) {
        $nsUrl = ""; // XPath funcionará sem prefixo
        $xml->registerXPathNamespace('ns', '');
    } else {
        // Registra o namespace detectado com o apelido 'ns'
        $xml->registerXPathNamespace('ns', $nsUrl);
    }
    // =========================================================================

    // Helpers de Extração
    $getXPathString = function ($path) use ($xml) {
        $res = $xml->xpath($path);
        return (!empty($res)) ? (string)$res[0] : null;
    };

    // Extração (Funciona em v1.2 e v1.3 pois os nomes das tags são iguais)
    $dataProcessamento = $getXPathString('//ns:retornoConsultaInformacoesCadastrais/ns:dataHoraProcessamento');
    $numeroRecibo      = $getXPathString('//ns:retornoConsultaInformacoesCadastrais/ns:numeroRecibo');
    $idEvento          = $getXPathString('//ns:retornoConsultaInformacoesCadastrais/ns:id');

    // Status
    $cdRetorno   = $getXPathString('//ns:status/ns:cdRetorno');
    $descRetorno = $getXPathString('//ns:status/ns:descRetorno');

    // Identificação Declarante
    $cnpjDeclarante = $getXPathString('//ns:identificacaoEmpresaDeclarante/ns:cnpjEmpresaDeclarante');

    // Informações Cadastrais
    $cadNome      = $getXPathString('//ns:informacoesCadastrais/ns:nome');
    $cadCnpj      = $getXPathString('//ns:informacoesCadastrais/ns:cnpj');
    $cadEndereco  = $getXPathString('//ns:informacoesCadastrais/ns:endereco');
    $cadMunicipio = $getXPathString('//ns:informacoesCadastrais/ns:municipio');
    $cadUf        = $getXPathString('//ns:informacoesCadastrais/ns:uf');
    $cadGiin      = $getXPathString('//ns:informacoesCadastrais/ns:giin');

    // Montagem do Retorno
    $dadosRetorno = [
        'sucesso' => ($cdRetorno == '0'),
        'status' => [
            'codigo' => $cdRetorno,
            'descricao' => $descRetorno
        ],
        'versao_detectada' => $nsUrl, // Útil para você debugar qual versão está vindo
        'recibo' => $numeroRecibo,
        'cadastro' => [
            'nome' => $cadNome,
            'cnpj' => $cadCnpj,
            'endereco' => $cadEndereco,
            'municipio' => $cadMunicipio,
            'uf' => $cadUf,
            'giin' => $cadGiin
        ],
        'ocorrencias' => []
    ];

    // Layout de Exibição
    echo "<div style='background-color: #fff; border: 1px solid #ddd; padding: 15px; margin-top: 10px; border-radius: 4px;'>";
    echo "<h4 style='margin-top:0; border-bottom: 2px solid #268fbd; padding-bottom: 5px; color: #268fbd;'>Detalhes do Cadastro</h4>";

    $corStatus = ($cdRetorno == '0') ? 'green' : 'red';
    echo "<p><strong>Status do Processamento:</strong> <span style='color:$corStatus; font-weight:bold'>[$cdRetorno] $descRetorno</span></p>";

    if ($numeroRecibo) {
        echo "<p><strong>Recibo:</strong> $numeroRecibo</p>";
    }

    // Busca Ocorrências usando o namespace detectado
    $ocorrencias = $xml->xpath('//ns:status/ns:dadosRegistroOcorrenciaEvento/ns:ocorrencias');

    if (!empty($ocorrencias)) {
        echo "<div style='background-color: #fff0f0; padding: 10px; border-left: 4px solid red; margin-bottom: 10px;'>";
        echo "<strong style='color:red'>Ocorrências Encontradas:</strong><br><ul style='margin-bottom:0;'>";

        foreach ($ocorrencias as $oc) {
            // Usa children($nsUrl) para garantir que pegamos os filhos dentro do namespace correto
            $tipo = (string)$oc->children($nsUrl)->tipo;
            $cod  = (string)$oc->children($nsUrl)->codigo;
            $desc = (string)$oc->children($nsUrl)->descricao;

            $labelTipo = ($tipo == '2') ? 'ERRO' : 'AVISO';
            echo "<li><strong>[$labelTipo $cod]</strong> $desc</li>";

            $dadosRetorno['ocorrencias'][] = ['tipo' => $tipo, 'codigo' => $cod, 'descricao' => $desc];
        }
        echo "</ul></div>";
    }

    if ($cadNome) {
        echo "<table class='table table-bordered table-condensed' style='background-color: #f9f9f9; margin-top: 10px;'>";
        echo "<tr><th width='150'>Razão Social</th><td>$cadNome</td></tr>";
        echo "<tr><th>CNPJ</th><td>$cadCnpj</td></tr>";
        if ($cadGiin) echo "<tr><th>GIIN</th><td>$cadGiin</td></tr>";
        echo "<tr><th>Endereço</th><td>$cadEndereco, $cadMunicipio - $cadUf</td></tr>";
        echo "</table>";
    }

    echo "</div>";

    return $dadosRetorno;
}


/**
 * Processa o XML específico de Retorno da Lista e-Financeira (v1.2.0)
 * Este XML vem dentro do CDATA da consulta assíncrona.
 */
function processarRetornoListaEFinanceira($xmlString)
{
    // 1. Carregar XML
    $xml = simplexml_load_string($xmlString, "SimpleXMLElement", LIBXML_NOCDATA);

    if ($xml === false) {
        echo "<div style='color:red'>Erro: XML interno de lista inválido.</div>";
        return null;
    }

    // 2. Registrar Namespace (Baseado no XSD: v1_2_0)
    $ns = "http://www.eFinanceira.gov.br/schemas/retornoConsultaListaEFinanceira/v1_2_0";
    $xml->registerXPathNamespace('ns', $ns);

    // 3. Helpers
    $getXPathString = function ($context, $path) use ($xml) {
        // Se passar um nó específico, busca relativo a ele
        if ($context instanceof SimpleXMLElement) {
            $res = $context->xpath($path);
        } else {
            // Senão busca na raiz
            $res = $xml->xpath($path);
        }
        return (!empty($res)) ? (string)$res[0] : null;
    };

    // 4. Dados Principais
    $dhProcessamento = $getXPathString($xml, '//ns:retornoConsultaListaEFinanceira/ns:dhProcessamento');
    $cnpjDeclarante  = $getXPathString($xml, '//ns:identificacaoEmpresaDeclarante/ns:cnpjEmpresaDeclarante');

    // Status
    $cdRetorno   = $getXPathString($xml, '//ns:status/ns:cdRetorno');
    $descRetorno = $getXPathString($xml, '//ns:status/ns:descRetorno');

    // 5. Lista de Informações e-Financeira (Pode ter vários blocos)
    $listaEFin = [];
    $nosEFinanceira = $xml->xpath('//ns:retornoConsultaListaEFinanceira/ns:informacoesEFinanceira');

    if (!empty($nosEFinanceira)) {
        foreach ($nosEFinanceira as $node) {
            // Registra namespace no nó filho para garantir que xpath relativo funcione
            $node->registerXPathNamespace('ns', $ns);

            $listaEFin[] = [
                'dhInicial' => (string)$node->xpath('ns:dhInicial')[0],
                'dhFinal'   => (string)$node->xpath('ns:dhFinal')[0],
                'situacao'  => (string)$node->xpath('ns:situacaoEFinanceira')[0], // 0-Todas, 1-Em Andamento, 2-Ativa...
                'reciboAbertura'   => (string)$node->xpath('ns:numeroReciboAbertura')[0],
                'idAbertura'       => (string)$node->xpath('ns:idAbertura')[0],
                'reciboFechamento' => (string)$node->xpath('ns:numeroReciboFechamento')[0],
                'idFechamento'     => (string)$node->xpath('ns:idFechamento')[0]
            ];
        }
    }

    // 6. Estrutura de Retorno
    $dadosRetorno = [
        'sucesso' => ($cdRetorno == '0'),
        'dhProcessamento' => $dhProcessamento,
        'status' => [
            'codigo' => $cdRetorno,
            'descricao' => $descRetorno
        ],
        'cnpjDeclarante' => $cnpjDeclarante,
        'lista' => $listaEFin,
        'ocorrencias' => []
    ];

    // 7. Exibição HTML
    echo "<div style='background-color: #fff; border: 1px solid #ddd; padding: 15px; margin-top: 10px; border-radius: 4px;'>";
    echo "<h4 style='margin-top:0; border-bottom: 2px solid #268fbd; padding-bottom: 5px; color: #268fbd;'>Lista de e-Financeiras</h4>";

    // Status
    $corStatus = ($cdRetorno == '0') ? 'green' : 'red';
    echo "<p><strong>Status:</strong> <span style='color:$corStatus; font-weight:bold'>[$cdRetorno] $descRetorno</span></p>";

    // Ocorrências
    $ocorrencias = $xml->xpath('//ns:status/ns:dadosRegistroOcorrenciaEvento/ns:ocorrencias');
    if (!empty($ocorrencias)) {
        echo "<div style='background-color: #fff0f0; padding: 10px; border-left: 4px solid red; margin-bottom: 10px;'>";
        echo "<strong style='color:red'>Ocorrências:</strong><ul>";
        foreach ($ocorrencias as $oc) {
            $tipo = (string)$oc->children($ns)->tipo;
            $cod  = (string)$oc->children($ns)->codigo;
            $desc = (string)$oc->children($ns)->descricao;
            echo "<li><strong>[" . ($tipo == '2' ? 'ERRO' : 'AVISO') . " $cod]</strong> $desc</li>";
            $dadosRetorno['ocorrencias'][] = ['tipo' => $tipo, 'codigo' => $cod, 'descricao' => $desc];
        }
        echo "</ul></div>";
    }

    // Tabela da Lista
    if (!empty($listaEFin)) {
        echo "<div class='table-responsive'>";
        echo "<table class='table table-striped table-bordered table-condensed' style='font-size: 0.9em;'>";
        echo "<thead><tr style='background-color: #f5f5f5;'>";
        echo "<th>Período</th>";
        echo "<th>Situação</th>";
        echo "<th>Abertura (Recibo/ID)</th>";
        echo "<th>Fechamento (Recibo/ID)</th>";
        echo "</tr></thead><tbody>";

        // Mapa de Situação
        $situacoes = ['0' => 'Todas', '1' => 'Em Andamento', '2' => 'Ativa', '3' => 'Retificada', '4' => 'Excluída'];

        foreach ($listaEFin as $item) {
            $dtIni = date('d/m/Y', strtotime($item['dhInicial']));
            $dtFim = date('d/m/Y', strtotime($item['dhFinal']));
            $sitDesc = isset($situacoes[$item['situacao']]) ? $situacoes[$item['situacao']] : $item['situacao'];

            // Estiliza a situação
            $lblClass = 'label-default';
            if ($item['situacao'] == '2') $lblClass = 'label-success'; // Ativa
            if ($item['situacao'] == '1') $lblClass = 'label-warning'; // Em Andamento

            echo "<tr>";
            echo "<td>$dtIni a $dtFim</td>";
            echo "<td><span class='label $lblClass'>$sitDesc</span></td>";
            echo "<td><small><strong>Rec:</strong> {$item['reciboAbertura']}<br><strong>ID:</strong> {$item['idAbertura']}</small></td>";
            echo "<td><small><strong>Rec:</strong> {$item['reciboFechamento']}<br><strong>ID:</strong> {$item['idFechamento']}</small></td>";
            echo "</tr>";
        }
        echo "</tbody></table></div>";
    } else if ($cdRetorno == '0') {
        echo "<p><em>Nenhuma e-Financeira encontrada para os critérios informados.</em></p>";
    }

    echo "</div>";

    return $dadosRetorno;
}

/**
 * Processa o XML específico de Retorno de Consulta de Informações de Movimento (v1.0.0)
 * Este XML vem dentro do CDATA da consulta assíncrona.
 */
function processarRetornoMovOpFin($xmlString)
{
    // 1. Carregar XML
    $xml = simplexml_load_string($xmlString, "SimpleXMLElement", LIBXML_NOCDATA);

    if ($xml === false) {
        return ['sucesso' => false, 'erro_msg' => 'XML interno de movimento inválido'];
    }

    // 2. DETECÇÃO DE NAMESPACE (Compatibilidade)
    $namespaces = $xml->getNamespaces(true);
    $nsUrl = reset($namespaces);

    if (!$nsUrl) {
        $nsUrl = "http://www.eFinanceira.gov.br/schemas/retornoConsultaInformacoesMovOpFin/v1_0_0"; // Fallback para o schema padrão
        $xml->registerXPathNamespace('ns', $nsUrl);
    } else {
        $xml->registerXPathNamespace('ns', $nsUrl);
    }

    // 3. Helpers
    $getXPathString = function ($context, $path) use ($xml) {
        if ($context instanceof SimpleXMLElement) {
            $res = $context->xpath($path);
        } else {
            $res = $xml->xpath($path);
        }
        return (!empty($res)) ? (string)$res[0] : null;
    };

    // 4. Extração de Dados Gerais
    $dataProcessamento = $getXPathString($xml, '//ns:retornoConsultaInformacoesMovOpFin/ns:dataHoraProcessamento');
    $cnpjDeclarante    = $getXPathString($xml, '//ns:identificacaoEmpresaDeclarante/ns:cnpjEmpresaDeclarante');

    // Status
    $cdRetorno   = $getXPathString($xml, '//ns:status/ns:cdRetorno');
    $descRetorno = $getXPathString($xml, '//ns:status/ns:descRetorno');

    // 5. Lista de Movimentos Encontrados
    $listaMovimentos = [];
    $nosMovimento = $xml->xpath('//ns:retornoConsultaInformacoesMovOpFin/ns:informacoesMovimento');

    if (!empty($nosMovimento)) {
        foreach ($nosMovimento as $node) {
            // Garante namespace relativo correto
            $node->registerXPathNamespace('ns', $nsUrl);

            $listaMovimentos[] = [
                'tipoNI'      => (string)$node->xpath('ns:tipoNI')[0],
                'NI'          => (string)$node->xpath('ns:NI')[0],
                'anoMesCaixa' => (string)$node->xpath('ns:anoMesCaixa')[0],
                'situacao'    => (string)$node->xpath('ns:situacao')[0],
                'recibo'      => (string)$node->xpath('ns:numeroRecibo')[0],
                'id'          => (string)$node->xpath('ns:id')[0]
            ];
        }
    }

    // 6. Estrutura de Retorno
    $dadosRetorno = [
        'sucesso' => ($cdRetorno == '0'),
        'dhProcessamento' => $dataProcessamento,
        'status' => [
            'codigo' => $cdRetorno,
            'descricao' => $descRetorno
        ],
        'cnpjDeclarante' => $cnpjDeclarante,
        'movimentos' => $listaMovimentos,
        'ocorrencias' => []
    ];

    // 7. Exibição HTML
    echo "<div style='background-color: #fff; border: 1px solid #ddd; padding: 15px; margin-top: 10px; border-radius: 4px;'>";
    echo "<h4 style='margin-top:0; border-bottom: 2px solid #268fbd; padding-bottom: 5px; color: #268fbd;'>Consulta de Movimento Financeiro</h4>";

    // Status
    $corStatus = ($cdRetorno == '0') ? 'green' : 'red';
    echo "<p><strong>Status:</strong> <span style='color:$corStatus; font-weight:bold'>[$cdRetorno] $descRetorno</span></p>";

    // Ocorrências
    $ocorrencias = $xml->xpath('//ns:status/ns:dadosRegistroOcorrenciaEvento/ns:ocorrencias');
    if (!empty($ocorrencias)) {
        echo "<div style='background-color: #fff0f0; padding: 10px; border-left: 4px solid red; margin-bottom: 10px;'>";
        echo "<strong style='color:red'>Ocorrências:</strong><ul>";
        foreach ($ocorrencias as $oc) {
            $tipo = (string)$oc->children($nsUrl)->tipo;
            $cod  = (string)$oc->children($nsUrl)->codigo;
            $desc = (string)$oc->children($nsUrl)->descricao;
            $local = (string)$oc->children($nsUrl)->localizacaoErroAviso;

            echo "<li><strong>[" . ($tipo == '2' ? 'ERRO' : 'AVISO') . " $cod]</strong> $desc " . ($local ? "<em>($local)</em>" : "") . "</li>";
            $dadosRetorno['ocorrencias'][] = ['tipo' => $tipo, 'codigo' => $cod, 'descricao' => $desc];
        }
        echo "</ul></div>";
    }

    // Tabela de Movimentos
    if (!empty($listaMovimentos)) {
        echo "<div class='table-responsive'>";
        echo "<table class='table table-striped table-bordered table-condensed' style='font-size: 0.9em; margin-top:10px;'>";
        echo "<thead><tr style='background-color: #f5f5f5;'>";
        echo "<th>Período</th>";
        echo "<th>Declarado (NI)</th>";
        echo "<th>Situação</th>";
        echo "<th>Recibo</th>";
        echo "<th>ID Evento</th>";
        echo "</tr></thead><tbody>";

        foreach ($listaMovimentos as $mov) {
            // Formata Ano/Mês (202501 -> 01/2025)
            $anoMes = $mov['anoMesCaixa'];
            if (strlen($anoMes) == 6) {
                $anoMes = substr($anoMes, 4, 2) . '/' . substr($anoMes, 0, 4);
            }

            echo "<tr>";
            echo "<td><strong>$anoMes</strong></td>";
            echo "<td>" . $mov['NI'] . " <br><small class='text-muted'>(" . $mov['tipoNI'] . ")</small></td>";
            echo "<td>" . $mov['situacao'] . "</td>";
            echo "<td><small>" . $mov['recibo'] . "</small></td>";
            echo "<td><small>" . $mov['id'] . "</small></td>";
            echo "</tr>";
        }
        echo "</tbody></table></div>";
        echo "<p class='text-right'>Total de registros encontrados: <strong>" . count($listaMovimentos) . "</strong></p>";
    } else if ($cdRetorno == '0') {
        echo "<p><em>Nenhum movimento financeiro encontrado para os critérios informados.</em></p>";
    }

    echo "<div style='font-size: 0.85em; color: #777; margin-top: 5px;'>Processado em: " . str_replace('T', ' ', $dataProcessamento) . "</div>";
    echo "</div>";

    return $dadosRetorno;
}
