<?php
// ajax_processar_envio.php
set_time_limit(0); // Importante: Processar vários arquivos pode demorar muito

require_once '/www/includes/constantes.php';
require_once __DIR__ . "/functions_e_financeira.php";
require_once __DIR__ . "/../../includes/load_dotenv.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit("Método não permitido");
}

ob_start();

try {
    // 1. Lógica de Upload (Mantida igual)
    if (!isset($_FILES['arquivo']) || $_FILES['arquivo']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Erro no upload. Código: " . ($_FILES['arquivo']['error'] ?? 'N/A'));
    }

    $caminho_temp = $_FILES['arquivo']['tmp_name'];
    $nome_original = $_FILES['arquivo']['name'];
    $mime_type = mime_content_type($caminho_temp);
    $lotes_xml = [];

    // Extrai arquivos do ZIP ou pega o XML único
    if ($mime_type === 'application/zip' || $mime_type === 'application/x-zip-compressed' || pathinfo($nome_original, PATHINFO_EXTENSION) === 'zip') {
        $lotes_xml = obterXmlFromZip('arquivo');
    } elseif ($mime_type === 'text/xml' || $mime_type === 'application/xml' || pathinfo($nome_original, PATHINFO_EXTENSION) === 'xml') {
        $xml_conteudo = file_get_contents($caminho_temp);
        $lotes_xml[] = [
            'nome' => basename($nome_original),
            'conteudo' => $xml_conteudo
        ];
    } else {
        throw new Exception("O arquivo deve ser ZIP ou XML.");
    }

    // TRAVA DE SEGURANÇA: Verifica a quantidade de arquivos extraídos
    $quantidade_arquivos = count($lotes_xml);
    if ($quantidade_arquivos > 15) {
        throw new Exception("O arquivo contém {$quantidade_arquivos} XMLs. O limite máximo permitido é de 15 arquivos por envio.");
    }

    if (empty($lotes_xml)) {
        throw new Exception("Nenhum arquivo XML válido encontrado.");
    }

    // -------------------------------------------------------------------------
    // LOOP PRINCIPAL: Processa arquivo por arquivo
    // -------------------------------------------------------------------------

    // Defina se é produção ou homologação (se tiver input no form, pegue aqui)
    // Ex: $producao = ($_POST['ambiente'] === 'producao');
    $producao = getenv('AMBIENTE') == "HOMOLOGACAO" ? false : true;

    echo "<div class='container-resultados'>";

    foreach ($lotes_xml as $index => $lote) {
        $nomeArquivo = $lote['nome'];
        $conteudoXml = $lote['conteudo'];

        echo "<div class='card mb-3'>";
        echo "<div class='card-header bg-light'><strong>Arquivo " . ($index + 1) . ": $nomeArquivo</strong></div>";
        echo "<div class='card-body'>";

        try {
            // --- ETAPA 1: ENVIO ---
            // Passamos $nomeArquivo para que, se der erro, a exceção saiba qual arquivo falhou
            $dadosEnvio = etapa1_enviarLote($conteudoXml, $nomeArquivo, $producao);

            $protocolo = $dadosEnvio['protocolo'];
            echo "<div class='alert alert-info py-1'>Lote enviado. Protocolo: <strong>$protocolo</strong>. Aguardando processamento...</div>";

            // Flush para o usuário ver que está andando (se o output buffering permitir)
            ob_flush();
            flush();

            // --- ETAPA 2: MONITORAMENTO ---
            $xmlFinal = etapa2_monitorarProcessamento($protocolo, $producao);

            // --- ETAPA 3: PROCESSAMENTO E SALVAMENTO ---
            $resumo = etapa3_processarResultados(
                $xmlFinal,
                $dadosEnvio['xml_envio_assinado'],
                $protocolo,
                $nomeArquivo
            );

            // --- ETAPA 4: VISUALIZAÇÃO ---
            echo etapa4_renderizarVisualizacao($resumo, $xmlFinal, $nomeArquivo);
        } catch (Exception $e) {
            // Erro específico deste arquivo (não para o loop dos próximos)
            echo "<div class='alert alert-danger'><strong>Falha no processamento deste arquivo:</strong> " . $e->getMessage() . "</div>";

            // Se falhou no envio mas temos o XML original, mostra para debug
            echo "<button class='btn btn-sm btn-link' type='button' data-toggle='collapse' data-target='#err_xml_$index'>Ver XML Original</button>";
            echo "<div class='collapse' id='err_xml_$index'>";
            echo xmlViewer($conteudoXml, "erro_" . md5($nomeArquivo));
            echo "</div>";
        }

        echo "</div></div>"; // Fim do Card
    }

    echo "</div>"; // Fim container

} catch (Exception $e) {
    // Erro global (upload, zip inválido, etc)
    echo "<div class='alert alert-danger'><strong>Erro Geral:</strong> " . $e->getMessage() . "</div>";
}

$html = ob_get_clean();
echo $html;

/**
 * Etapa 1: Assina, Criptografa e Envia o Lote. Retorna o Protocolo.
 */
function etapa1_enviarLote($conteudoXmlOriginal, $nomeArquivo, $producao = false)
{
    // Passa o ambiente para o construtor, se sua classe suportar
    // Se sua classe define ambiente por constante ou setter, ajuste aqui.
    $efinanceira = new GerarEFinanceira();

    // Opcional: Configurar ambiente na classe
    // $efinanceira->setAmbiente($producao ? 1 : 2); 

    if (empty($conteudoXmlOriginal)) {
        throw new Exception("Conteúdo XML vazio para o arquivo: $nomeArquivo");
    }

    try {
        // 1. Assinar
        $lote_assinado = $efinanceira->assinarLoteEventos($conteudoXmlOriginal);

        // 2. Criptografar
        $lote_criptografado = $efinanceira->criptografarLoteEF($lote_assinado, $producao);

        // 3. Enviar
        $xmlResposta = $efinanceira->enviarLoteEFinanceira($lote_criptografado, false, $producao);
    } catch (Exception $e) {
        throw new Exception("Erro durante preparação/envio do arquivo $nomeArquivo: " . $e->getMessage());
    }

    // 4. Extrair Protocolo
    $xmlLimpo = preg_replace('/xmlns[^=]*="[^"]*"/i', '', $xmlResposta);
    $xmlObj = simplexml_load_string($xmlLimpo);

    if ($xmlObj === false) {
        throw new Exception("Receita retornou um XML inválido ou vazio para o arquivo $nomeArquivo.");
    }

    $protocolo = (string)($xmlObj->xpath("//protocoloEnvio")[0] ?? '');
    $cdResposta = (int)($xmlObj->xpath("//cdResposta")[0] ?? 0);
    $descResposta = (string)($xmlObj->xpath("//descResposta")[0] ?? 'Sem descrição');

    // Validação: Status 1 = Sucesso na Recepção
    if ($cdResposta !== 1 || empty($protocolo)) {
        // Se já existe (Status 7 - Duplicidade), lançamos erro com o protocolo antigo se possível?
        // No envio, Status 7 é erro fatal de envio. O usuário deve consultar o protocolo antigo manualmente ou via outra rotina.
        throw new Exception("Lote rejeitado pela Receita. Cód: $cdResposta - Msg: $descResposta");
    }

    return [
        'protocolo' => $protocolo,
        'xml_envio_assinado' => $lote_assinado,
        'xml_resposta_envio' => $xmlResposta
    ];
}
function etapa2_monitorarProcessamento($protocolo, $producao = false)
{
    $efinanceira = new GerarEFinanceira();

    $tentativa = 0;
    $maxTentativas = 24; // 2 minutos (24 * 5s)
    $xmlFinal = null;
    $statusLote = 1; // Começa assumindo '1 - Em Processamento'

    do {
        // Espera 5 segundos antes de consultar
        sleep(5);
        $tentativa++;

        $xmlFinal = $efinanceira->consultarLoteEFinanceira($protocolo, $producao);

        // Remove namespaces para leitura rápida do status
        $xmlLimpo = preg_replace('/xmlns[^=]*="[^"]*"/i', '', $xmlFinal);
        $obj = simplexml_load_string($xmlLimpo);

        if ($obj === false) {
            // Se o XML vier quebrado, tenta de novo na próxima iteração
            continue;
        }

        // Pega o status do lote
        // Caminho: eFinanceira -> retornoLoteEventosAssincrono -> status -> cdResposta
        $statusLote = (int)($obj->xpath("//status/cdResposta")[0] ?? 0);

        // A LÓGICA CORRETA AGORA:
        // Enquanto for 1 (Processando), continua o loop.
        // Se for 2, 3, 4, 5 ou 9, sai do loop.
    } while ($statusLote === 1 && $tentativa < $maxTentativas);

    // Se saiu do loop porque estourou o tempo e ainda é 1
    if ($statusLote === 1) {
        throw new Exception("Tempo limite excedido. O lote ainda está em processamento (Status 1). Tente consultar mais tarde.");
    }

    return $xmlFinal;
}

function etapa3_processarResultados($xmlProcessamento, $xmlEnvioAssinado, $protocolo, $nomeArquivoOriginal)
{
    $efinanceira = new GerarEFinanceira();

    // 1. IO (Salvar Arquivos)
    $pathEnviados = '/www/arquivos_gerados/efinanceira/lotes_enviados';
    $pathRespostas = '/www/arquivos_gerados/efinanceira/respostas_envio';

    if (!is_dir($pathEnviados)) mkdir($pathEnviados, 0755, true);
    if (!is_dir($pathRespostas)) mkdir($pathRespostas, 0755, true);

    file_put_contents($pathEnviados . '/' . $nomeArquivoOriginal, $xmlEnvioAssinado);

    $nomeResp = pathinfo($nomeArquivoOriginal, PATHINFO_FILENAME) . "_retorno.xml";
    file_put_contents($pathRespostas . '/' . $nomeResp, $xmlProcessamento);

    // 2. Parser
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

    // 3. Decisão baseada no Status do Lote

    // Status 2 (Sucesso Total) ou 3 (Com Ocorrências) -> Vamos ler os eventos
    if ($statusGeralLote === 2 || $statusGeralLote === 3) {

        $eventosRetorno = $xmlObj->xpath("//retornoEventos/evento");

        if (!empty($eventosRetorno)) {
            foreach ($eventosRetorno as $evt) {
                // ID do Wrapper (ID100...)
                $idEventoWrapper = (string)$evt['id'];

                // Busca o retornoEvento interno
                $retornoEvento = $evt->xpath(".//retornoEvento"); // Simplificado pois removemos namespace

                if (!empty($retornoEvento)) {
                    $nodeRetorno = $retornoEvento[0];

                    $idEventoReal = (string)$nodeRetorno->attributes()->id;
                    $idBanco = (int)substr($idEventoReal, 3);

                    $descRetornoEvt = (string)($nodeRetorno->xpath("status/descRetorno")[0] ?? '');

                    // Verificação Definitiva de Sucesso: EXISTÊNCIA DE RECIBO
                    $recibo = (string)($nodeRetorno->xpath("dadosReciboEntrega/numeroRecibo")[0] ?? '');

                    // Coleta Erros/Avisos do Evento
                    $errosMsg = [];
                    $ocorrencias = $nodeRetorno->xpath("status/dadosRegistroOcorrenciaEvento/ocorrencias");
                    if (!empty($ocorrencias)) {
                        foreach ($ocorrencias as $oc) {
                            $tipo = (string)$oc->tipo; // 1=Erro, 2=Aviso
                            $prefixo = ($tipo == '2') ? '[AVISO]' : '[ERRO]';
                            $errosMsg[] = "$prefixo " . $oc->descricao;
                        }
                    }

                    // Lógica para Banco de Dados
                    // Se tem Recibo = Sucesso (ENVIADO)
                    // Se não tem Recibo = Erro (ERRO)
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
    }
    // Status 4, 5, 9 -> Erros Globais (Não há eventos para processar)
    else {
        // Pega as ocorrências globais do lote, se houver
        $ocorrenciasLote = $xmlObj->xpath("//status/ocorrencias/ocorrencia");
        $errosGlobais = [];
        foreach ($ocorrenciasLote as $oc) {
            $errosGlobais[] = "[LOTE] " . $oc->descricao;
        }

        // Adiciona um item "falso" no detalhe para mostrar o erro global na tabela
        $detalhesEventos[] = [
            'id' => 'LOTE',
            'status_db' => 'ERRO',
            'mensagem' => $msgGeralLote,
            'recibo' => '',
            'erros' => $errosGlobais
        ];
    }

    // 4. Atualização no Banco
    if (!empty($idsSucesso)) {
        $efinanceira->atualizarLoteStatus($idsSucesso, $protocolo, $nomeArquivoOriginal, 'ENVIADO');
    }

    if (!empty($idsErro)) {
        $efinanceira->atualizarLoteStatus($idsErro, $protocolo, $nomeArquivoOriginal, 'ERRO');
    }

    return [
        'status_lote' => $statusGeralLote,
        'mensagem_lote' => $msgGeralLote,
        'detalhes' => $detalhesEventos,
        'caminho_resposta' => $nomeResp,
        'qtd_sucesso' => count($idsSucesso),
        'qtd_erro' => count($idsErro)
    ];
}

function etapa4_renderizarVisualizacao($dadosProcessamento, $xmlProcessamento, $nomeArquivo)
{
    $html = "";
    $statusLote = $dadosProcessamento['status_lote'];

    $html .= "<div class='card mb-3'>";
    $html .= "<div class='card-header'><strong>Resultado: $nomeArquivo</strong></div>";
    $html .= "<div class='card-body'>";

    // Cores baseadas nos status novos
    // 2 = Sucesso Total (Verde)
    // 3 = Processado com Ocorrências (Amarelo/Laranja)
    // 4, 5, 9 = Erro (Vermelho)
    // 1 = Processando (Azul) - Teimou em ficar processando

    $alertClass = 'danger';
    if ($statusLote === 2) $alertClass = 'success';
    elseif ($statusLote === 3) $alertClass = 'warning';
    elseif ($statusLote === 1) $alertClass = 'info';

    $html .= "<div class='alert alert-$alertClass'>";
    $html .= "<strong>Status do Lote ($statusLote):</strong> " . $dadosProcessamento['mensagem_lote'];
    $html .= "</div>";

    // Resumo Quantitativo
    if ($statusLote === 2 || $statusLote === 3) {
        $html .= "<div class='row mb-2'>";
        $html .= "<div class='col-md-6'><span class='badge badge-success'>Sucesso: {$dadosProcessamento['qtd_sucesso']}</span></div>";
        $html .= "<div class='col-md-6'><span class='badge badge-danger'>Erros: {$dadosProcessamento['qtd_erro']}</span></div>";
        $html .= "</div>";
    }

    // Tabela de Detalhes
    if (!empty($dadosProcessamento['detalhes'])) {
        $html .= "<table class='table table-bordered table-sm'>";
        $html .= "<thead><tr class='active'><th>ID Evento</th><th>Status</th><th>Detalhes / Recibo</th></tr></thead>";
        $html .= "<tbody>";

        foreach ($dadosProcessamento['detalhes'] as $det) {
            $label = ($det['status_db'] == 'ENVIADO') ? 'success' : 'danger';

            // Se for um erro global de lote, destaca a linha
            $rowClass = ($det['id'] === 'LOTE') ? 'class="danger"' : '';

            $html .= "<tr $rowClass>";
            $html .= "<td>{$det['id']}</td>";
            $html .= "<td><span class='label label-$label'>{$det['status_db']}</span></td>";

            $html .= "<td>";
            if (!empty($det['recibo'])) {
                $html .= "<div><strong>Recibo:</strong> " . $det['recibo'] . "</div>";
            }
            // Mensagem principal do evento
            if (!empty($det['mensagem']) && $det['mensagem'] !== 'SUCESSO') {
                $html .= "<div><em>" . $det['mensagem'] . "</em></div>";
            }
            // Lista de Ocorrências
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

    $html .= "</div></div>"; // Fim Card

    // XML Viewer
    $html .= xmlViewer($xmlProcessamento, "xml_" . md5($nomeArquivo), true, true);

    return $html;
}
