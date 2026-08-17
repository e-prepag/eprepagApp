<?php
// ajax_processar_envio.php

const EFIN_ENVIO_WEB_TIPO_TAREFA = 'enviar_lotes_efin_web';
const EFIN_ENVIO_WEB_STATUS_PENDENTE = 'WEB_PENDENTE';
const EFIN_ENVIO_WEB_BASE = '/www/arquivos_gerados/efinanceira/lotes_enviados';
const EFIN_ENVIO_WEB_MAX_XML_POR_FILA = 500;
const EFIN_ENVIO_WEB_MAX_BYTES_XML = 31457280; // 30 MiB por XML
const EFIN_ENVIO_WEB_MAX_BYTES_FILA = 524288000; // 500 MiB descompactados
const EFIN_ENVIO_WEB_MAX_CONSULTAS = 24;

$acaoFila = isset($_POST['acao']) ? (string) $_POST['acao'] : '';
$acoesFila = ['preparar_fila', 'avancar_fila', 'cancelar_fila'];

if (in_array($acaoFila, $acoesFila, true)) {
    set_time_limit(0);
    ignore_user_abort(false);

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $usuarioIdFila = isset($_SESSION['iduser_bko']) ? (int) $_SESSION['iduser_bko'] : 0;
    $csrfSessaoFila = isset($_SESSION['csrf_efinanceira']) ? (string) $_SESSION['csrf_efinanceira'] : '';
    $csrfRecebidoFila = isset($_POST['csrf_token']) ? (string) $_POST['csrf_token'] : '';

    if ($usuarioIdFila <= 0) {
        responderFilaJson(['sucesso' => false, 'mensagem' => 'Sessao expirada. Entre novamente no backoffice.'], 401);
    }
    if ($csrfSessaoFila === '' || !hash_equals($csrfSessaoFila, $csrfRecebidoFila)) {
        responderFilaJson(['sucesso' => false, 'mensagem' => 'Token de seguranca invalido. Atualize a pagina.'], 403);
    }

    // Evita bloquear outras paginas do backoffice durante chamadas externas.
    session_write_close();

    require_once '/www/includes/constantes.php';
    require_once __DIR__ . '/functions_e_financeira.php';
    require_once __DIR__ . '/../../includes/load_dotenv.php';

    try {
        $pdoFila = ConnectionPDO::getConnection()->getLink();
        if ($acaoFila === 'preparar_fila') {
            prepararFilaEnvio($pdoFila, $usuarioIdFila);
        } elseif ($acaoFila === 'avancar_fila') {
            avancarFilaEnvio($pdoFila, $usuarioIdFila);
        } else {
            cancelarFilaEnvio($pdoFila, $usuarioIdFila);
        }
    } catch (Throwable $e) {
        $statusHttp = $e instanceof InvalidArgumentException ? 400 : 500;
        responderFilaJson(['sucesso' => false, 'mensagem' => $e->getMessage()], $statusHttp);
    }
}

set_time_limit(0); // Importante: Processar vários arquivos pode demorar muito

require_once '/www/includes/constantes.php';
require_once __DIR__ . "/functions_e_financeira.php";
require_once __DIR__ . "/../../includes/load_dotenv.php";

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    exit("Método não permitido");
}

ob_start();

try {
    // Verifica se os arquivos foram enviados
    if (!isset($_FILES['arquivo'])) {
        throw new Exception("Nenhum arquivo enviado.");
    }

    $arquivos = $_FILES['arquivo'] ?? array();
    $lotes_xml = [];

    // Garante que a estrutura seja um array (mesmo se enviar apenas 1 arquivo)
    $nomes_arquivos = is_array($arquivos['name']) ? $arquivos['name'] : [$arquivos['name']];
    $erros = is_array($arquivos['error']) ? $arquivos['error'] : [$arquivos['error']];
    $caminhos_temp = is_array($arquivos['tmp_name']) ? $arquivos['tmp_name'] : [$arquivos['tmp_name']];

    $total_enviados = (is_countable($nomes_arquivos) ? count($nomes_arquivos) : 0);

    // Loop passando por cada arquivo anexado
    for ($i = 0; $i < $total_enviados; $i++) {

        if ($erros[$i] === UPLOAD_ERR_NO_FILE) {
            continue; // Pula se o slot estiver vazio
        }

        if ($erros[$i] !== UPLOAD_ERR_OK) {
            throw new Exception("Erro no upload do arquivo {$nomes_arquivos[$i]}. Código: " . $erros[$i]);
        }

        $caminho_temp = $caminhos_temp[$i];
        $nome_original = $nomes_arquivos[$i];
        $mime_type = mime_content_type($caminho_temp);
        $extensao = strtolower(pathinfo($nome_original, PATHINFO_EXTENSION));

        // Extrai arquivos do ZIP ou pega o XML único
        if ($mime_type === 'application/zip' || $mime_type === 'application/x-zip-compressed' || $extensao === 'zip') {

            // ATENÇÃO AQUI: Como agora processamos múltiplos arquivos, a sua função obterXmlFromZip 
            // não pode mais buscar o $_FILES global pelo nome 'arquivo'. 
            // Ela precisa receber o caminho temporário exato ($caminho_temp).
            $xmls_extraidos = obterXmlFromZip($caminho_temp);

            // Junta os XMLs extraídos no array principal
            $lotes_xml = array_merge($lotes_xml, $xmls_extraidos);
        } elseif ($mime_type === 'text/xml' || $mime_type === 'application/xml' || $extensao === 'xml') {

            $xml_conteudo = file_get_contents($caminho_temp);
            $lotes_xml[] = [
                'nome' => basename($nome_original),
                'conteudo' => $xml_conteudo
            ];
        } else {
            throw new Exception("O arquivo '{$nome_original}' não é um ZIP ou XML válido.");
        }
    }

    // TRAVA DE SEGURANÇA MANTIDA: Verifica a quantidade total de arquivos extraídos
    $quantidade_arquivos = (is_countable($lotes_xml) ? count($lotes_xml) : 0);
    if ($quantidade_arquivos > 15) {
        throw new Exception("Você tentou enviar {$quantidade_arquivos} XMLs (contando os que estavam dentro de ZIPs). O limite máximo permitido é de 15 arquivos por envio.");
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
    $xmlLimpo = preg_replace('/xmlns[^=]*="[^"]*"/i', '', (string)($xmlResposta ?? ''));
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

    // Faz um pré carregamento dos dados no servidor antes de enviar
    $idsExtraidos = $efinanceira->extrairIdsDoXml($conteudoXmlOriginal);

    if (!empty($idsExtraidos)) {
        $efinanceira->atualizarLoteStatus($idsExtraidos, $protocolo, $nomeArquivo, 'PENDENTE');
    }

    $pathEnviados = '/www/arquivos_gerados/efinanceira/lotes_enviados';
    if (!is_dir($pathEnviados)) mkdir($pathEnviados, 0755, true);

    if (!file_exists($pathEnviados . '/' . $nomeArquivo)) {
        file_put_contents($pathEnviados . '/' . $nomeArquivo, $lote_assinado);
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
        $xmlLimpo = preg_replace('/xmlns[^=]*="[^"]*"/i', '', (string)($xmlFinal ?? ''));
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
    $xmlLimpo = preg_replace('/xmlns[^=]*="[^"]*"/i', '', (string)($xmlProcessamento ?? ''));
    $xmlObj = simplexml_load_string($xmlLimpo);

    if (!isset($xmlObj) || !$xmlObj) {
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
                    $idBanco = (int)substr((string)($idEventoReal ?? ""), 3);

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
        'qtd_sucesso' => (is_countable($idsSucesso) ? count($idsSucesso) : 0),
        'qtd_erro' => (is_countable($idsErro) ? count($idsErro) : 0)
    ];
}

function etapa4_renderizarVisualizacao($dadosProcessamento, $xmlProcessamento, $nomeArquivo, $sufixoId = '')
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
    $html .= xmlViewer($xmlProcessamento, "xml_" . md5($nomeArquivo . $sufixoId), true, true);

    return $html;
}

/**
 * Prepara uma fila web sem executar trabalho em background. Os XMLs ficam
 * temporariamente no diretorio ja existente e cada chamada seguinte processa
 * somente uma etapa curta.
 */
function prepararFilaEnvio(PDO $pdo, $usuarioId)
{
    if (!isset($_FILES['arquivo'])) {
        throw new InvalidArgumentException('Selecione ao menos um arquivo XML ou ZIP.');
    }

    validarDiretorioFilaEnvio();
    limparFilasEnvioExpiradas($pdo);

    $arquivos = $_FILES['arquivo'];
    $nomes = is_array($arquivos['name']) ? $arquivos['name'] : [$arquivos['name']];
    $erros = is_array($arquivos['error']) ? $arquivos['error'] : [$arquivos['error']];
    $temporarios = is_array($arquivos['tmp_name']) ? $arquivos['tmp_name'] : [$arquivos['tmp_name']];
    $token = bin2hex(random_bytes(32));
    $tempId = bin2hex(random_bytes(12));
    $itens = [];
    $nomesUsados = [];
    $caminhosCriados = [];
    $totalBytes = 0;

    try {
        foreach ($nomes as $indice => $nomeOriginal) {
            $erroUpload = (int) ($erros[$indice] ?? UPLOAD_ERR_NO_FILE);
            if ($erroUpload === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            if ($erroUpload !== UPLOAD_ERR_OK) {
                throw new RuntimeException('Falha no upload de ' . basename((string) $nomeOriginal) . '. Codigo: ' . $erroUpload);
            }

            $temporario = (string) ($temporarios[$indice] ?? '');
            if ($temporario === '' || !is_uploaded_file($temporario)) {
                throw new RuntimeException('O upload recebido nao e valido.');
            }

            $extensao = strtolower(pathinfo((string) $nomeOriginal, PATHINFO_EXTENSION));
            if ($extensao === 'xml') {
                adicionarXmlNaFila(
                    $temporario,
                    (string) $nomeOriginal,
                    $tempId,
                    $itens,
                    $nomesUsados,
                    $caminhosCriados,
                    $totalBytes
                );
            } elseif ($extensao === 'zip') {
                adicionarZipNaFila(
                    $temporario,
                    $tempId,
                    $itens,
                    $nomesUsados,
                    $caminhosCriados,
                    $totalBytes
                );
            } else {
                throw new InvalidArgumentException("O arquivo '{$nomeOriginal}' nao e XML nem ZIP.");
            }
        }

        if (empty($itens)) {
            throw new RuntimeException('Nenhum XML valido foi encontrado nos arquivos enviados.');
        }

        $estado = [
            'versao' => 1,
            'usuario_id' => (int) $usuarioId,
            'token_hash' => hash('sha256', $token),
            'temp_id' => $tempId,
            'itens' => $itens,
            'indice_atual' => 0,
            'total' => count($itens),
            'concluidos' => 0,
            'falhas' => 0,
            'fase' => 'pronto',
            'atualizado_em' => date('c'),
        ];

        $stmt = $pdo->prepare("INSERT INTO fila_tarefas_background (tipo_tarefa, parametros, status)
                              VALUES (:tipo, :parametros, :status)
                              RETURNING id");
        $stmt->execute([
            ':tipo' => EFIN_ENVIO_WEB_TIPO_TAREFA,
            ':parametros' => codificarEstadoFilaEnvio($estado),
            ':status' => EFIN_ENVIO_WEB_STATUS_PENDENTE,
        ]);
        $ticketId = (int) $stmt->fetchColumn();
        if ($ticketId <= 0) {
            throw new RuntimeException('Nao foi possivel criar a fila de envio.');
        }

        responderFilaJson([
            'sucesso' => true,
            'ticket_id' => $ticketId,
            'token' => $token,
            'estado' => estadoPublicoFilaEnvio($estado),
        ]);
    } catch (Throwable $e) {
        foreach ($caminhosCriados as $caminho) {
            if (is_file($caminho)) {
                unlink($caminho);
            }
        }
        throw $e;
    }
}

function limparFilasEnvioExpiradas(PDO $pdo)
{
    $stmt = $pdo->prepare("SELECT id, parametros
                          FROM fila_tarefas_background
                          WHERE tipo_tarefa = :tipo
                            AND status = :status
                            AND data_solicitacao < NOW() - INTERVAL '24 hours'
                          ORDER BY id
                          LIMIT 100");
    $stmt->execute([
        ':tipo' => EFIN_ENVIO_WEB_TIPO_TAREFA,
        ':status' => EFIN_ENVIO_WEB_STATUS_PENDENTE,
    ]);

    $ids = [];
    while ($tarefa = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $estado = json_decode((string) $tarefa['parametros'], true);
        if (is_array($estado)) {
            limparTemporariosFilaEnvio($estado);
        }
        $ids[] = (int) $tarefa['id'];
    }

    if (!empty($ids)) {
        $marcadores = implode(',', array_fill(0, count($ids), '?'));
        $update = $pdo->prepare("UPDATE fila_tarefas_background
                                SET status = 'EXPIRADO', data_conclusao = NOW(),
                                    mensagem_erro = 'Fila web abandonada por mais de 24 horas.'
                                WHERE id IN ({$marcadores})");
        $update->execute($ids);
    }
}

function adicionarZipNaFila(
    $caminhoZip,
    $tempId,
    array &$itens,
    array &$nomesUsados,
    array &$caminhosCriados,
    &$totalBytes
) {
    $zip = new ZipArchive();
    $aberto = $zip->open($caminhoZip);
    if ($aberto !== true) {
        throw new RuntimeException('Nao foi possivel abrir o arquivo ZIP. Codigo: ' . $aberto);
    }

    try {
        for ($indice = 0; $indice < $zip->numFiles; $indice++) {
            $estatistica = $zip->statIndex($indice);
            if (!is_array($estatistica)) {
                continue;
            }

            $nomeInterno = (string) ($estatistica['name'] ?? '');
            if ($nomeInterno === '' || substr($nomeInterno, -1) === '/') {
                continue;
            }
            if (strtolower(pathinfo($nomeInterno, PATHINFO_EXTENSION)) !== 'xml') {
                continue;
            }
            if ((int) ($estatistica['size'] ?? 0) > EFIN_ENVIO_WEB_MAX_BYTES_XML) {
                throw new RuntimeException('O XML ' . basename($nomeInterno) . ' excede 30 MiB descompactado.');
            }
            if (count($itens) >= EFIN_ENVIO_WEB_MAX_XML_POR_FILA) {
                throw new RuntimeException('O limite de seguranca e de ' . EFIN_ENVIO_WEB_MAX_XML_POR_FILA . ' XMLs por arquivo selecionado.');
            }

            $stream = $zip->getStream($nomeInterno);
            if (!is_resource($stream)) {
                throw new RuntimeException('Falha ao ler ' . basename($nomeInterno) . ' dentro do ZIP.');
            }

            $sequencia = count($itens) + 1;
            $nomeTemporario = nomeTemporarioFilaEnvio($tempId, $sequencia, 'original');
            $caminhoDestino = EFIN_ENVIO_WEB_BASE . '/' . $nomeTemporario;
            $destino = fopen($caminhoDestino, 'xb');
            if (!is_resource($destino)) {
                fclose($stream);
                throw new RuntimeException('Nao foi possivel criar um XML temporario.');
            }
            $caminhosCriados[] = $caminhoDestino;

            $copiados = stream_copy_to_stream($stream, $destino, EFIN_ENVIO_WEB_MAX_BYTES_XML + 1);
            fclose($destino);
            fclose($stream);

            if ($copiados === false || $copiados > EFIN_ENVIO_WEB_MAX_BYTES_XML) {
                throw new RuntimeException('O XML ' . basename($nomeInterno) . ' excede o tamanho permitido.');
            }

            registrarXmlPreparado(
                $caminhoDestino,
                $nomeTemporario,
                $nomeInterno,
                $itens,
                $nomesUsados,
                $totalBytes
            );
        }
    } finally {
        $zip->close();
    }
}

function adicionarXmlNaFila(
    $caminhoOrigem,
    $nomeOriginal,
    $tempId,
    array &$itens,
    array &$nomesUsados,
    array &$caminhosCriados,
    &$totalBytes
) {
    if (count($itens) >= EFIN_ENVIO_WEB_MAX_XML_POR_FILA) {
        throw new RuntimeException('O limite de seguranca e de ' . EFIN_ENVIO_WEB_MAX_XML_POR_FILA . ' XMLs por arquivo selecionado.');
    }

    $tamanho = filesize($caminhoOrigem);
    if ($tamanho === false || $tamanho > EFIN_ENVIO_WEB_MAX_BYTES_XML) {
        throw new RuntimeException('O XML ' . basename($nomeOriginal) . ' excede 30 MiB.');
    }

    $sequencia = count($itens) + 1;
    $nomeTemporario = nomeTemporarioFilaEnvio($tempId, $sequencia, 'original');
    $caminhoDestino = EFIN_ENVIO_WEB_BASE . '/' . $nomeTemporario;
    if (!copy($caminhoOrigem, $caminhoDestino)) {
        throw new RuntimeException('Nao foi possivel preparar o XML ' . basename($nomeOriginal) . '.');
    }
    $caminhosCriados[] = $caminhoDestino;

    registrarXmlPreparado(
        $caminhoDestino,
        $nomeTemporario,
        $nomeOriginal,
        $itens,
        $nomesUsados,
        $totalBytes
    );
}

function registrarXmlPreparado(
    $caminho,
    $nomeTemporario,
    $nomeOriginal,
    array &$itens,
    array &$nomesUsados,
    &$totalBytes
) {
    validarXmlDaFila($caminho, $nomeOriginal);

    $tamanho = filesize($caminho);
    if ($tamanho === false) {
        throw new RuntimeException('Nao foi possivel determinar o tamanho do XML preparado.');
    }
    $totalBytes += (int) $tamanho;
    if ($totalBytes > EFIN_ENVIO_WEB_MAX_BYTES_FILA) {
        throw new RuntimeException('Os XMLs descompactados excedem o limite total de 500 MiB.');
    }

    $nomeSeguro = nomeUnicoFilaEnvio($nomeOriginal, $nomesUsados);
    $itens[] = [
        'nome' => $nomeSeguro,
        'arquivo_original' => $nomeTemporario,
        'arquivo_assinado' => null,
        'status' => 'pendente',
        'protocolo' => null,
        'tentativas' => 0,
    ];
}

function validarXmlDaFila($caminho, $nomeOriginal)
{
    $anterior = libxml_use_internal_errors(true);
    libxml_clear_errors();
    $dom = new DOMDocument();
    $carregado = $dom->load($caminho, LIBXML_NONET);
    libxml_clear_errors();
    libxml_use_internal_errors($anterior);

    if (!$carregado || !$dom->documentElement || $dom->documentElement->localName !== 'eFinanceira') {
        throw new InvalidArgumentException('O arquivo ' . basename((string) $nomeOriginal) . ' nao contem um XML e-Financeira valido.');
    }
}

function avancarFilaEnvio(PDO $pdo, $usuarioId)
{
    list($ticketId, $token) = dadosTicketFilaEnvio();
    $tarefa = carregarTicketFilaEnvio($pdo, $ticketId);
    $estado = validarAcessoFilaEnvio($tarefa, $usuarioId, $token);

    if (in_array((string) $tarefa['status'], ['CANCELADO', 'EXPIRADO', 'ERRO'], true)) {
        throw new RuntimeException('Esta fila nao esta mais disponivel para processamento.');
    }

    if (($estado['fase'] ?? '') === 'concluido' || (int) ($estado['indice_atual'] ?? 0) >= (int) ($estado['total'] ?? 0)) {
        concluirFilaEnvio($pdo, $ticketId, $estado);
        responderFilaJson([
            'sucesso' => true,
            'concluido' => true,
            'aguardar_ms' => 0,
            'estado' => estadoPublicoFilaEnvio($estado),
        ]);
    }

    $indice = (int) $estado['indice_atual'];
    $item = $estado['itens'][$indice];

    try {
        if (($item['status'] ?? '') === 'pendente') {
            $estado['itens'][$indice]['status'] = 'enviando';
            $estado['fase'] = 'enviando';
            salvarEstadoFilaEnvio($pdo, $ticketId, $estado, EFIN_ENVIO_WEB_STATUS_PENDENTE);

            $caminhoOriginal = caminhoTemporarioFilaEnvio($estado, (string) $item['arquivo_original']);
            $conteudoXml = file_get_contents($caminhoOriginal);
            if ($conteudoXml === false) {
                throw new RuntimeException('Nao foi possivel ler o XML temporario.');
            }

            $producao = getenv('AMBIENTE') == 'HOMOLOGACAO' ? false : true;
            $dadosEnvio = etapa1_enviarLote($conteudoXml, (string) $item['nome'], $producao);

            $nomeAssinado = nomeTemporarioFilaEnvio((string) $estado['temp_id'], $indice + 1, 'assinado');
            $caminhoAssinado = EFIN_ENVIO_WEB_BASE . '/' . $nomeAssinado;
            if (file_put_contents($caminhoAssinado, $dadosEnvio['xml_envio_assinado'], LOCK_EX) === false) {
                throw new RuntimeException('O lote foi recebido, mas nao foi possivel guardar o XML assinado para a consulta. Protocolo: ' . $dadosEnvio['protocolo']);
            }

            if (is_file($caminhoOriginal)) {
                unlink($caminhoOriginal);
            }

            $estado['itens'][$indice]['arquivo_assinado'] = $nomeAssinado;
            $estado['itens'][$indice]['protocolo'] = (string) $dadosEnvio['protocolo'];
            $estado['itens'][$indice]['status'] = 'aguardando';
            $estado['itens'][$indice]['tentativas'] = 0;
            $estado['fase'] = 'aguardando_receita';
            salvarEstadoFilaEnvio($pdo, $ticketId, $estado, EFIN_ENVIO_WEB_STATUS_PENDENTE);

            responderFilaJson([
                'sucesso' => true,
                'concluido' => false,
                'aguardar_ms' => 5000,
                'mensagem' => 'Lote enviado. Aguardando processamento da Receita.',
                'protocolo' => (string) $dadosEnvio['protocolo'],
                'estado' => estadoPublicoFilaEnvio($estado),
            ]);
        }

        if (($item['status'] ?? '') === 'enviando') {
            throw new RuntimeException('A requisicao anterior foi interrompida durante o envio. O lote nao sera reenviado automaticamente para evitar duplicidade.');
        }

        if (($item['status'] ?? '') !== 'aguardando') {
            throw new RuntimeException('Estado inesperado do lote na fila.');
        }

        $producao = getenv('AMBIENTE') == 'HOMOLOGACAO' ? false : true;
        $consulta = consultarProcessamentoUmaVez((string) $item['protocolo'], $producao);
        $estado['itens'][$indice]['tentativas'] = (int) ($item['tentativas'] ?? 0) + 1;

        if ($consulta['status'] === 1 && $estado['itens'][$indice]['tentativas'] < EFIN_ENVIO_WEB_MAX_CONSULTAS) {
            $estado['fase'] = 'aguardando_receita';
            salvarEstadoFilaEnvio($pdo, $ticketId, $estado, EFIN_ENVIO_WEB_STATUS_PENDENTE);
            responderFilaJson([
                'sucesso' => true,
                'concluido' => false,
                'aguardar_ms' => 5000,
                'mensagem' => 'A Receita ainda esta processando o lote.',
                'protocolo' => (string) $item['protocolo'],
                'estado' => estadoPublicoFilaEnvio($estado),
            ]);
        }

        if ($consulta['status'] === 1) {
            throw new RuntimeException('Tempo de consulta excedido. O lote continua em processamento na Receita. Consulte depois pelo protocolo ' . $item['protocolo'] . '.');
        }

        $caminhoAssinado = caminhoTemporarioFilaEnvio($estado, (string) $item['arquivo_assinado']);
        $xmlAssinado = file_get_contents($caminhoAssinado);
        if ($xmlAssinado === false) {
            throw new RuntimeException('Nao foi possivel recuperar o XML assinado.');
        }

        $resumo = etapa3_processarResultados(
            $consulta['xml'],
            $xmlAssinado,
            (string) $item['protocolo'],
            (string) $item['nome']
        );
        $html = etapa4_renderizarVisualizacao(
            $resumo,
            $consulta['xml'],
            (string) $item['nome'],
            $ticketId . '_' . $indice
        );
        $falhou = !in_array((int) $resumo['status_lote'], [2, 3], true) || (int) $resumo['qtd_erro'] > 0;

        finalizarItemFilaEnvio($estado, $indice, $falhou, $caminhoAssinado);
        $filaConcluida = (int) $estado['indice_atual'] >= (int) $estado['total'];
        if ($filaConcluida) {
            concluirFilaEnvio($pdo, $ticketId, $estado);
        } else {
            salvarEstadoFilaEnvio($pdo, $ticketId, $estado, EFIN_ENVIO_WEB_STATUS_PENDENTE);
        }

        responderFilaJson([
            'sucesso' => true,
            'concluido' => $filaConcluida,
            'aguardar_ms' => $filaConcluida ? 0 : 100,
            'html' => $html,
            'estado' => estadoPublicoFilaEnvio($estado),
        ]);
    } catch (Throwable $e) {
        $html = renderizarErroItemFilaEnvio((string) ($item['nome'] ?? 'arquivo'), $e->getMessage());
        limparArquivosItemFilaEnvio($estado, $item);
        finalizarItemFilaEnvio($estado, $indice, true);
        $filaConcluida = (int) $estado['indice_atual'] >= (int) $estado['total'];
        if ($filaConcluida) {
            concluirFilaEnvio($pdo, $ticketId, $estado);
        } else {
            salvarEstadoFilaEnvio($pdo, $ticketId, $estado, EFIN_ENVIO_WEB_STATUS_PENDENTE);
        }

        responderFilaJson([
            'sucesso' => true,
            'concluido' => $filaConcluida,
            'aguardar_ms' => $filaConcluida ? 0 : 100,
            'html' => $html,
            'mensagem' => $e->getMessage(),
            'estado' => estadoPublicoFilaEnvio($estado),
        ]);
    }
}

function consultarProcessamentoUmaVez($protocolo, $producao)
{
    $efinanceira = new GerarEFinanceira();
    $xml = $efinanceira->consultarLoteEFinanceira($protocolo, $producao);
    $xmlLimpo = preg_replace('/xmlns[^=]*="[^"]*"/i', '', (string) ($xml ?? ''));
    $obj = simplexml_load_string($xmlLimpo);
    if ($obj === false) {
        throw new RuntimeException('A Receita retornou um XML de consulta invalido.');
    }

    return [
        'status' => (int) ($obj->xpath('//status/cdResposta')[0] ?? 0),
        'xml' => $xml,
    ];
}

function finalizarItemFilaEnvio(array &$estado, $indice, $falhou, $caminhoAssinado = null)
{
    if ($caminhoAssinado && is_file($caminhoAssinado)) {
        unlink($caminhoAssinado);
    }
    $estado['itens'][$indice]['status'] = $falhou ? 'erro' : 'concluido';
    $estado['indice_atual'] = $indice + 1;
    $estado['concluidos'] = (int) ($estado['concluidos'] ?? 0) + 1;
    if ($falhou) {
        $estado['falhas'] = (int) ($estado['falhas'] ?? 0) + 1;
    }
    $estado['fase'] = $estado['indice_atual'] >= $estado['total'] ? 'concluido' : 'pronto';
}

function concluirFilaEnvio(PDO $pdo, $ticketId, array &$estado)
{
    $estado['fase'] = 'concluido';
    $estado['atualizado_em'] = date('c');
    $stmt = $pdo->prepare("UPDATE fila_tarefas_background
                          SET status = 'CONCLUIDO', parametros = :parametros,
                              data_conclusao = NOW(), mensagem_erro = NULL
                          WHERE id = :id AND tipo_tarefa = :tipo");
    $stmt->execute([
        ':parametros' => codificarEstadoFilaEnvio($estado),
        ':id' => $ticketId,
        ':tipo' => EFIN_ENVIO_WEB_TIPO_TAREFA,
    ]);
}

function cancelarFilaEnvio(PDO $pdo, $usuarioId)
{
    list($ticketId, $token) = dadosTicketFilaEnvio();
    $tarefa = carregarTicketFilaEnvio($pdo, $ticketId);
    $estado = validarAcessoFilaEnvio($tarefa, $usuarioId, $token);
    limparTemporariosFilaEnvio($estado);
    $estado['fase'] = 'cancelado';
    $stmt = $pdo->prepare("UPDATE fila_tarefas_background
                          SET status = 'CANCELADO', parametros = :parametros, data_conclusao = NOW()
                          WHERE id = :id AND tipo_tarefa = :tipo");
    $stmt->execute([
        ':parametros' => codificarEstadoFilaEnvio($estado),
        ':id' => $ticketId,
        ':tipo' => EFIN_ENVIO_WEB_TIPO_TAREFA,
    ]);
    responderFilaJson(['sucesso' => true]);
}

function salvarEstadoFilaEnvio(PDO $pdo, $ticketId, array &$estado, $status)
{
    $estado['atualizado_em'] = date('c');
    $stmt = $pdo->prepare("UPDATE fila_tarefas_background
                          SET parametros = :parametros, status = :status
                          WHERE id = :id AND tipo_tarefa = :tipo");
    $stmt->execute([
        ':parametros' => codificarEstadoFilaEnvio($estado),
        ':status' => $status,
        ':id' => $ticketId,
        ':tipo' => EFIN_ENVIO_WEB_TIPO_TAREFA,
    ]);
}

function carregarTicketFilaEnvio(PDO $pdo, $ticketId)
{
    $stmt = $pdo->prepare("SELECT id, parametros, status
                          FROM fila_tarefas_background
                          WHERE id = :id AND tipo_tarefa = :tipo
                          LIMIT 1");
    $stmt->execute([':id' => $ticketId, ':tipo' => EFIN_ENVIO_WEB_TIPO_TAREFA]);
    $tarefa = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$tarefa) {
        throw new RuntimeException('Fila de envio nao encontrada.');
    }
    return $tarefa;
}

function validarAcessoFilaEnvio(array $tarefa, $usuarioId, $token)
{
    $estado = json_decode((string) $tarefa['parametros'], true);
    if (!is_array($estado)) {
        throw new RuntimeException('Estado da fila de envio invalido.');
    }
    $mesmoUsuario = (int) ($estado['usuario_id'] ?? 0) === (int) $usuarioId;
    $tokenHash = (string) ($estado['token_hash'] ?? '');
    $tokenValido = $token !== '' && $tokenHash !== '' && hash_equals($tokenHash, hash('sha256', $token));
    if (!$mesmoUsuario || !$tokenValido) {
        throw new RuntimeException('Acesso negado a fila de envio.');
    }
    return $estado;
}

function dadosTicketFilaEnvio()
{
    $ticketId = isset($_POST['ticket_id']) ? (int) $_POST['ticket_id'] : 0;
    $token = isset($_POST['token']) ? (string) $_POST['token'] : '';
    if ($ticketId <= 0 || $token === '') {
        throw new InvalidArgumentException('Ticket ou token da fila invalido.');
    }
    return [$ticketId, $token];
}

function estadoPublicoFilaEnvio(array $estado)
{
    $indice = (int) ($estado['indice_atual'] ?? 0);
    $item = $estado['itens'][$indice] ?? null;
    return [
        'fase' => (string) ($estado['fase'] ?? 'pronto'),
        'total' => (int) ($estado['total'] ?? 0),
        'concluidos' => (int) ($estado['concluidos'] ?? 0),
        'falhas' => (int) ($estado['falhas'] ?? 0),
        'arquivo_atual' => is_array($item) ? (string) ($item['nome'] ?? '') : '',
        'protocolo_atual' => is_array($item) ? (string) ($item['protocolo'] ?? '') : '',
        'tentativas' => is_array($item) ? (int) ($item['tentativas'] ?? 0) : 0,
    ];
}

function codificarEstadoFilaEnvio(array $estado)
{
    $json = json_encode($estado, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    if ($json === false) {
        throw new RuntimeException('Nao foi possivel salvar o estado da fila.');
    }
    return $json;
}

function validarDiretorioFilaEnvio()
{
    if (!is_dir(EFIN_ENVIO_WEB_BASE) || !is_writable(EFIN_ENVIO_WEB_BASE)) {
        throw new RuntimeException('A pasta de lotes enviados nao existe ou nao permite gravacao.');
    }
}

function nomeTemporarioFilaEnvio($tempId, $sequencia, $tipo)
{
    $tempId = preg_replace('/[^a-f0-9]/', '', (string) $tempId);
    $tipo = $tipo === 'assinado' ? 'assinado' : 'original';
    return sprintf('.web_envio_%s_%04d_%s.xml', $tempId, (int) $sequencia, $tipo);
}

function caminhoTemporarioFilaEnvio(array $estado, $nomeArquivo)
{
    $tempId = preg_replace('/[^a-f0-9]/', '', (string) ($estado['temp_id'] ?? ''));
    $nomeArquivo = basename((string) $nomeArquivo);
    $prefixo = '.web_envio_' . $tempId . '_';
    if ($tempId === '' || strpos($nomeArquivo, $prefixo) !== 0) {
        throw new RuntimeException('Caminho temporario da fila invalido.');
    }
    $caminho = EFIN_ENVIO_WEB_BASE . '/' . $nomeArquivo;
    if (!is_file($caminho)) {
        throw new RuntimeException('Arquivo temporario da fila nao encontrado.');
    }
    return $caminho;
}

function nomeUnicoFilaEnvio($nomeOriginal, array &$nomesUsados)
{
    $nome = basename(str_replace('\\', '/', (string) $nomeOriginal));
    $nome = preg_replace('/[^A-Za-z0-9._-]/', '_', $nome);
    if ($nome === '' || strtolower(pathinfo($nome, PATHINFO_EXTENSION)) !== 'xml') {
        $nome = 'lote.xml';
    }
    $base = substr(pathinfo($nome, PATHINFO_FILENAME), 0, 170);
    if ($base === '') {
        $base = 'lote';
    }
    $extensao = '.xml';
    $candidato = $base . $extensao;
    $sufixo = 2;
    while (isset($nomesUsados[strtolower($candidato)])) {
        $candidato = substr($base, 0, 165) . '_' . $sufixo . $extensao;
        $sufixo++;
    }
    $nomesUsados[strtolower($candidato)] = true;
    return $candidato;
}

function limparArquivosItemFilaEnvio(array $estado, array $item)
{
    foreach (['arquivo_original', 'arquivo_assinado'] as $campo) {
        if (empty($item[$campo])) {
            continue;
        }
        try {
            $caminho = caminhoTemporarioFilaEnvio($estado, (string) $item[$campo]);
            if (is_file($caminho)) {
                unlink($caminho);
            }
        } catch (Throwable $ignorado) {
        }
    }
}

function limparTemporariosFilaEnvio(array $estado)
{
    foreach (($estado['itens'] ?? []) as $item) {
        if (is_array($item)) {
            limparArquivosItemFilaEnvio($estado, $item);
        }
    }
}

function renderizarErroItemFilaEnvio($nomeArquivo, $mensagem)
{
    $nome = htmlspecialchars($nomeArquivo, ENT_QUOTES, 'UTF-8');
    $erro = htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8');
    return "<div class='card mb-3'><div class='card-header'><strong>Resultado: {$nome}</strong></div>"
        . "<div class='card-body'><div class='alert alert-danger'><strong>Falha no processamento:</strong> {$erro}</div></div></div>";
}

function responderFilaJson(array $dados, $statusHttp = 200)
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    if (!headers_sent()) {
        http_response_code($statusHttp);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('X-Content-Type-Options: nosniff');
    }
    echo json_encode($dados, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}
