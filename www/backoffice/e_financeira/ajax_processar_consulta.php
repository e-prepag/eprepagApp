<?php
// ajax_processar_consulta.php

// Define tempo limite infinito (ou alto) pois o loop pode demorar
set_time_limit(0);

require_once '/www/includes/constantes.php';
require_once __DIR__ . "/functions_e_financeira.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit("Método inválido");
}

ob_start();

try {
    $efinanceira = new GerarEFinanceira();

    $producao = ($_POST['ambiente'] === 'producao');
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

        $cnpj = preg_replace('/[^0-9]/', '', $_POST['cnpj']);
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
            echo xmlViewer($xmlSolicitacao, "Erro na Solicitação");
            throw new Exception("Protocolo não retornado pela Receita.");
        }

        // -------------------------------------------------------------------------
        // 3. FASE DE POLLING (LOOP DE VERIFICAÇÃO)
        // -------------------------------------------------------------------------

        $tentativa = 0;
        $maxTentativas = 12; // 12 * 5 segundos = 60 segundos limite máximo
        $statusAtual = 0;
        $esperaSegundos = 5;

        echo "<div class='alert alert-info' style='padding: 10px; margin-bottom: 15px;'>";
        echo "<strong>Protocolo Gerado:</strong> $protocolo <br>";
        echo "<small>Iniciando monitoramento automático...</small>";
        echo "</div>";

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
        processarRetornoConsultaAssincrona($xmlFinal, $tipoConsultaTexto);

        // Exibição do código XML
        echo xmlViewer($xmlFinal, $protocolo ?? "XML de Retorno");
    }
} catch (Exception $e) {
    echo "<div class='alert alert-danger'><strong>Erro:</strong> " . $e->getMessage() . "</div>";
    // Se falhou mas temos um XML de solicitação (erro na solicitação), mostramos ele para debug
    if (isset($xmlSolicitacao)) {
        echo xmlViewer($xmlSolicitacao, "XML da Solicitação (Com Erro)");
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
