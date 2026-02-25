<?php
// ajax_processar_consulta.php
//header("Content-Type: text/html; charset=ISO-8859-1",true);
set_time_limit(0);

require_once '/www/includes/constantes.php';
require_once '/www/db/connect.php';
require_once '/www/db/ConnectionPDO.php';
require_once __DIR__ . "/functions_e_financeira.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit("Método inválido");
}

ob_start();

try {
    $efinanceira = new GerarEFinanceira();

    $producao = getenv('AMBIENTE') == "HOMOLOGACAO" ? false : true;
    $tipoConsulta = $_POST['sel_consulta'];

    // Variáveis de controle
    $xmlSolicitacao = null;    // O XML retornado no pedido (Status 1)
    $xmlFinal = null;          // O XML retornado na consulta do protocolo (Status 2 ou 3)
    $protocolo = null;
    $tipoConsultaTexto = $tipoConsulta; // Para passar para a função de visualização

    // -------------------------------------------------------------------------
    // 1. FASE DE SOLICITAÇÃO (Switch apenas define a chamada inicial)
    // -------------------------------------------------------------------------

    if ($tipoConsulta === 'lote') {
        // Lote é síncrono/diferente, tratamos separado
        $lote = $_POST['numero_lote'];
        if (empty($lote)) throw new Exception("Número do lote obrigatório.");

        $xmlFinal = $efinanceira->consultarLoteEFinanceira($lote, $producao);
        $protocolo = $lote; // Para lote, o protocolo é o número do lote

    } else {
        // Consultas Assíncronas (Cadastro, Lista, Movimento)

        $cnpj = preg_replace('/[^0-9]/', '', $efinanceira->cnpjEPP);
        if (empty($cnpj)) throw new Exception("CNPJ do Declarante é obrigatório.");

        switch ($tipoConsulta) {
            case 'cadastro':
                $xmlSolicitacao = $efinanceira->consultarInformacoesCadastrais($cnpj, $producao);
                $tipoConsultaTexto = 'cadastro';
                break;

            case 'lista':
                $sit = $_POST['situacao_informacao'];
                $dtIni = implode('/', array_reverse(explode('-', $_POST['dt_inicial'])));
                $dtFim = implode('/', array_reverse(explode('-', $_POST['dt_final'])));

                $xmlSolicitacao = $efinanceira->consultarListaEFinanceira($cnpj, $sit, $dtIni, $dtFim, $producao);
                $tipoConsultaTexto = 'lista';
                break;

            case 'mov_fin':
            case 'mov_fin_anual':
                $sit = $_POST['situacao_informacao'];
                $mesIni = str_replace('-', '', $_POST['anomes_inicio']);
                $mesFim = str_replace('-', '', $_POST['anomes_termino']);
                $tipoId = $_POST['tipo_identificacao'];
                $ident = preg_replace('/[^0-9]/', '', $_POST['identificacao']);

                // Adicione lógica para 'mov_fin_anual' se tiver o método na classe
                $xmlSolicitacao = $efinanceira->consultarMovimentoOpFin($cnpj, $sit, $mesIni, $mesFim, $tipoId, $ident, $producao);
                $tipoConsultaTexto = ($tipoConsulta === 'mov_fin') ? 'mov_fin' : 'mov_fin_anual';
                break;
        }

        // -------------------------------------------------------------------------
        // 2. FASE DE EXTRAÇÃO DO PROTOCOLO
        // -------------------------------------------------------------------------

        // Tenta extrair o protocolo do XML de solicitação
        $protocolo = extrairProtocoloEFinanceira($xmlSolicitacao);

        if (!$protocolo) {
            echo "<h4 style='color:red'>Falha na Solicitação Inicial</h4>";
            echo "<p>Não foi possível obter um número de protocolo.</p>";
            // Mostra o XML de erro imediatamente e para
            echo xmlViewer($xmlSolicitacao, "Erro na Solicitação", true, true);
            throw new Exception("Protocolo não retornado pela Receita.");
        }

        // -------------------------------------------------------------------------
        // 3. FASE DE POLLING (LOOP DE VERIFICAÇÃO)
        // -------------------------------------------------------------------------

        $tentativa = 0;
        $maxTentativas = 12; // 12 * 5 segundos = 60 segundos limite máximo
        $statusAtual = 0;
        $esperaSegundos = 5;

        // Flush para tentar enviar o cabeçalho (embora em AJAX puro isso fique no buffer até o fim)
        ob_flush();
        flush();

        do {
            // Espera antes de consultar (inclusive na primeira vez, para dar tempo ao servidor)
            sleep($esperaSegundos);
            $tentativa++;

            // Consulta o protocolo
            $xmlFinal = $efinanceira->consultarDetalhesPorProtocolo($tipoConsultaTexto, $protocolo, $producao);

            // Verifica o status sem renderizar tudo (Função auxiliar abaixo)
            $statusAtual = obterStatusConsultaRapido($xmlFinal);

            // Se for status 1, continua o loop. Se for 2 (Erro) ou 3 (Sucesso), sai.
        } while ($statusAtual == 1 && $tentativa < $maxTentativas);

        if ($statusAtual == 1) {
            echo "<div class='alert alert-warning'>Tempo limite excedido. A consulta ainda está em processamento na Receita. Tente consultar este protocolo novamente mais tarde.</div>";
        }
    }

    // -------------------------------------------------------------------------
    // 4. FASE DE EXIBIÇÃO FINAL
    // -------------------------------------------------------------------------

    // Processa o resultado final (seja sucesso, erro ou timeout com o último XML obtido)
    if ($xmlFinal) {
        // Função visual que cria os boxes coloridos
        processarRetornoConsultaAssincrona($xmlFinal, $tipoConsultaTexto, $protocolo, $efinanceira);

        // Exibição do código XML
        echo xmlViewer($xmlFinal, $protocolo ?? "XML de Retorno", true, true);
    }
} catch (Exception $e) {
    echo "<div class='alert alert-danger'><strong>Erro:</strong> " . $e->getMessage() . "</div>";
    // Se falhou mas temos um XML de solicitação (erro na solicitação), mostramos ele para debug
    if (isset($xmlSolicitacao)) {
        echo xmlViewer($xmlSolicitacao, "XML da Solicitação (Com Erro)", true, true);
    }
}

$html = ob_get_clean();
echo $html;

// -------------------------------------------------------------------------
// FUNÇÃO AUXILIAR PARA O LOOP (Coloque aqui ou no functions_e_financeira.php)
// -------------------------------------------------------------------------
function obterStatusConsultaRapido($xmlString)
{
    if (!$xmlString) return 0;

    // Load simples apenas para pegar o cdResposta
    $xml = simplexml_load_string($xmlString, "SimpleXMLElement", LIBXML_NOCDATA);
    if ($xml === false) return 0;

    // Detecta namespace
    $namespaces = $xml->getNamespaces(true);
    $nsUrl = reset($namespaces);
    if ($nsUrl) $xml->registerXPathNamespace('ns', $nsUrl);

    // Busca cdResposta
    $res = $xml->xpath('//ns:status/ns:cdResposta');

    if (!empty($res)) {
        return (int)$res[0];
    }
    return 0; // Não conseguiu ler
}

function processarRetornoConsultaAssincrona($xmlString, $visualizadorConsulta, $protocolo = null, $efinanceira = null)
{
    if ($visualizadorConsulta == 'lote') {
        processarRetornoConsultaLote($xmlString, $protocolo, $efinanceira);
        return;
    }
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

function processarRetornoConsultaLote($xmlProcessamento, $protocolo = null, $efinanceira = null)
{
    // Extrai os dados, salva arquivos e atualiza o banco
    $dadosProcessamento = extrairDadosEAtualizarLote($xmlProcessamento, $protocolo, $efinanceira);

    // Gera o HTML com base nos dados e imprime na tela
    echo gerarHtmlConsultaLote($dadosProcessamento);
}

function extrairDadosEAtualizarLote($xmlProcessamento, $protocolo = null, $efinanceira = null)
{
    $xmlLimpo = preg_replace('/xmlns[^=]*="[^"]*"/i', '', $xmlProcessamento);
    $xmlObj = simplexml_load_string($xmlLimpo);

    if (!$xmlObj) {
        return [
            'status_lote' => 9,
            'mensagem_lote' => 'XML de retorno inválido ou corrompido.',
            'detalhes' => [],
            'qtd_sucesso' => 0,
            'qtd_erro' => 0
        ];
    }

    $statusGeralLote = (int)($xmlObj->xpath("//status/cdResposta")[0] ?? 0);
    $msgGeralLote = (string)($xmlObj->xpath("//status/descResposta")[0] ?? '');

    $idsSucesso = [];
    $idsErro = [];
    $detalhesEventos = [];

    if ($statusGeralLote === 2 || $statusGeralLote === 3) {
        $eventosRetorno = $xmlObj->xpath("//retornoEventos/evento");

        if (!empty($eventosRetorno)) {
            foreach ($eventosRetorno as $evt) {
                $retornoEvento = $evt->xpath(".//retornoEvento");

                if (!empty($retornoEvento)) {
                    $nodeRetorno = $retornoEvento[0];

                    $idEventoReal = (string)$nodeRetorno->attributes()->id;
                    $idBanco = (int)substr($idEventoReal, 3);

                    $descRetornoEvt = (string)($nodeRetorno->xpath("status/descRetorno")[0] ?? '');
                    $recibo = (string)($nodeRetorno->xpath("dadosReciboEntrega/numeroRecibo")[0] ?? '');

                    $errosMsg = [];
                    $ocorrencias = $nodeRetorno->xpath("status/dadosRegistroOcorrenciaEvento/ocorrencias");
                    if (!empty($ocorrencias)) {
                        foreach ($ocorrencias as $oc) {
                            $tipo = (string)$oc->tipo; 
                            $prefixo = ($tipo == '2') ? '[AVISO]' : '[ERRO]';
                            $errosMsg[] = "$prefixo " . $oc->descricao;
                        }
                    }

                    if (!empty($recibo)) {
                        if ($idBanco) $idsSucesso[] = $idBanco;
                        $statusDb = 'ENVIADO';
                    } else {
                        if ($idBanco) $idsErro[] = $idBanco;
                        $statusDb = 'ERRO';
                    }

                    $detalhesEventos[] = [
                        'id' => $idEventoReal,
                        'status_db' => $statusDb,
                        'mensagem' => $descRetornoEvt,
                        'recibo' => $recibo,
                        'erros' => $errosMsg
                    ];
                }
            }
        }
    } else {
        $ocorrenciasLote = $xmlObj->xpath("//status/ocorrencias/ocorrencia");
        $errosGlobais = [];
        foreach ($ocorrenciasLote as $oc) {
            $errosGlobais[] = "[LOTE] " . $oc->descricao;
        }

        $detalhesEventos[] = [
            'id' => 'LOTE',
            'status_db' => 'ERRO',
            'mensagem' => $msgGeralLote,
            'recibo' => '',
            'erros' => $errosGlobais
        ];
    }

    // LÓGICA DE CONTINGÊNCIA (SALVAR ARQUIVO FALTANTE E ATUALIZAR BD)
    if (!empty($protocolo) && $efinanceira) {
        $pdo = ConnectionPDO::getConnection()->getLink();
        $sqlBusca = "SELECT nome_arquivo, status_envio FROM envios_e_financeira WHERE num_protocolo = :protocolo LIMIT 1";
        $stmtBusca = $pdo->prepare($sqlBusca); 
        $stmtBusca->execute([':protocolo' => $protocolo]);
        
        if ($row = $stmtBusca->fetch(PDO::FETCH_ASSOC)) {
            $nomeArquivoOriginal = $row['nome_arquivo'];
            $statusAtualDb = strtoupper($row['status_envio']);
            
            if (!empty($nomeArquivoOriginal)) {
                $pathRespostas = '/www/arquivos_gerados/efinanceira/respostas_envio';
                if (!is_dir($pathRespostas)) mkdir($pathRespostas, 0755, true);
                
                $info_arquivo = pathinfo($nomeArquivoOriginal);
                $extensao = isset($info_arquivo['extension']) ? '.' . $info_arquivo['extension'] : '.xml';
                $nomeResp = $info_arquivo['filename'] . "_retorno" . $extensao;
                $caminhoCompletoResposta = $pathRespostas . '/' . $nomeResp;
                
                if (!file_exists($caminhoCompletoResposta)) {
                    file_put_contents($caminhoCompletoResposta, $xmlProcessamento);
                }
                
                if ($statusAtualDb === 'PENDENTE') {
                    if (!empty($idsSucesso)) {
                        $efinanceira->atualizarLoteStatus($idsSucesso, $protocolo, $nomeArquivoOriginal, 'ENVIADO');
                    }
                    if (!empty($idsErro)) {
                        $efinanceira->atualizarLoteStatus($idsErro, $protocolo, $nomeArquivoOriginal, 'ERRO');
                    }
                }
            }
        }
    }

    return [
        'status_lote' => $statusGeralLote,
        'mensagem_lote' => $msgGeralLote,
        'detalhes' => $detalhesEventos,
        'qtd_sucesso' => count($idsSucesso),
        'qtd_erro' => count($idsErro)
    ];
}

function gerarHtmlConsultaLote($dadosProcessamento)
{
    $html = "";
    $statusLote = $dadosProcessamento['status_lote'];

    $html .= "<div class='card mb-3'>";
    $html .= "<div class='card-header'><strong>Resultado: </strong></div>";
    $html .= "<div class='card-body'>";

    $alertClass = 'danger';
    if ($statusLote === 2) $alertClass = 'success';
    elseif ($statusLote === 3) $alertClass = 'warning';
    elseif ($statusLote === 1) $alertClass = 'info';

    $html .= "<div class='alert alert-$alertClass'>";
    $html .= "<strong>Status do Lote ($statusLote):</strong> " . $dadosProcessamento['mensagem_lote'];
    $html .= "</div>";

    if ($statusLote === 2 || $statusLote === 3) {
        $html .= "<div class='row mb-2'>";
        $html .= "<div class='col-md-6'><span class='badge badge-success'>Sucesso: {$dadosProcessamento['qtd_sucesso']}</span></div>";
        $html .= "<div class='col-md-6'><span class='badge badge-danger'>Erros: {$dadosProcessamento['qtd_erro']}</span></div>";
        $html .= "</div>";
    }

    if (!empty($dadosProcessamento['detalhes'])) {
        $html .= "<table class='table table-bordered table-sm'>";
        $html .= "<thead><tr class='active'><th>ID Evento</th><th>Status</th><th>Detalhes / Recibo</th></tr></thead>";
        $html .= "<tbody>";

        foreach ($dadosProcessamento['detalhes'] as $det) {
            $label = ($det['status_db'] == 'ENVIADO') ? 'success' : 'danger';
            $rowClass = ($det['id'] === 'LOTE') ? 'class="danger"' : '';

            $html .= "<tr $rowClass>";
            $html .= "<td>{$det['id']}</td>";
            $html .= "<td><span class='label label-$label'>{$det['status_db']}</span></td>";
            $html .= "<td>";
            
            if (!empty($det['recibo'])) {
                $html .= "<div><strong>Recibo:</strong> " . $det['recibo'] . "</div>";
            }
            if (!empty($det['mensagem']) && $det['mensagem'] !== 'SUCESSO') {
                $html .= "<div><em>" . $det['mensagem'] . "</em></div>";
            }
            if (!empty($det['erros'])) {
                $html .= "<div class='text-danger mt-1' style='font-size:0.9em; background:#fff0f0; padding:5px; border-radius:3px;'>";
                foreach ($det['erros'] as $err) {
                    $html .= "<div>• $err</div>";
                }
                $html .= "</div>";
            }
            $html .= "</td>";
            $html .= "</tr>";
        }
        $html .= "</tbody></table>";
    }

    $html .= "</div></div>";

    return $html;
}