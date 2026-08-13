<?php

/**
 * Processamento interativo dos lotes da e-Financeira.
 *
 * Este fluxo nao substitui o worker/cron existente. Os tickets criados aqui
 * usam estados WEB_* e, portanto, nao sao consumidos por processa_workers.php.
 */

const EFIN_WEB_TIPO_TAREFA = 'gerar_zip_efinanceira_web';
const EFIN_WEB_STATUS_PENDENTE = 'WEB_PENDENTE';
const EFIN_WEB_STATUS_PROCESSANDO = 'WEB_PROCESSANDO';
const EFIN_WEB_LIMITE_CONSULTA = 1000;
const EFIN_WEB_LIMITE_SEGUNDOS_REQUISICAO = 210;
const EFIN_WEB_LOTE_COMPACTACAO = 100;
const EFIN_WEB_BASE_ZIP = '/www/arquivos_gerados/efinanceira/lotes_enviados';
const EFIN_WEB_URL_ZIP = '/arquivos_gerados/efinanceira/lotes_enviados/';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$usuarioId = isset($_SESSION['iduser_bko']) ? (int) $_SESSION['iduser_bko'] : 0;
$csrfSessao = isset($_SESSION['csrf_efinanceira']) ? (string) $_SESSION['csrf_efinanceira'] : '';
$acao = isset($_REQUEST['acao']) ? (string) $_REQUEST['acao'] : '';

if ($usuarioId <= 0) {
    responderJson(['sucesso' => false, 'mensagem' => 'Sessao expirada. Entre novamente no backoffice.'], 401);
}

$csrfRecebido = isset($_POST['csrf_token']) ? (string) $_POST['csrf_token'] : '';
if ($acao !== 'baixar' && ($csrfSessao === '' || !hash_equals($csrfSessao, $csrfRecebido))) {
    responderJson(['sucesso' => false, 'mensagem' => 'Token de seguranca invalido. Atualize a pagina.'], 403);
}

if ($acao === 'baixar' && ($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    responderJson(['sucesso' => false, 'mensagem' => 'Metodo nao permitido.'], 405);
}

// Libera a sessao antes das consultas demoradas para nao bloquear o backoffice.
session_write_close();

require_once '/www/includes/constantes.php';
require_once '/www/db/connect.php';
require_once '/www/db/ConnectionPDO.php';
require_once __DIR__ . '/functions_e_financeira.php';

try {
    $pdo = ConnectionPDO::getConnection()->getLink();

    switch ($acao) {
        case 'solicitar':
            exigirPost();
            solicitarProcessamento($pdo, $usuarioId);
            break;

        case 'status':
            exigirPost();
            consultarStatus($pdo, $usuarioId);
            break;

        case 'processar':
            exigirPost();
            processarTicket($pdo, $usuarioId);
            break;

        case 'baixar':
            baixarArquivo($pdo, $usuarioId, $csrfSessao);
            break;

        default:
            responderJson(['sucesso' => false, 'mensagem' => 'Acao invalida.'], 400);
    }
} catch (Throwable $e) {
    responderJson(['sucesso' => false, 'mensagem' => $e->getMessage()], 500);
}

function exigirPost()
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        responderJson(['sucesso' => false, 'mensagem' => 'Metodo nao permitido.'], 405);
    }
}

function responderJson(array $dados, $statusHttp = 200)
{
    if (!headers_sent()) {
        http_response_code($statusHttp);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate');
    }

    echo json_encode($dados, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function solicitarProcessamento(PDO $pdo, $usuarioId)
{
    $dataInicial = trim((string) ($_POST['data_inicial'] ?? ''));
    $dataFinal = trim((string) ($_POST['data_final'] ?? ''));
    $tipoDoc = trim((string) ($_POST['tipo_doc'] ?? 'todos'));
    $cpfCnpj = preg_replace('/[^0-9]/', '', (string) ($_POST['cpfcnpj'] ?? ''));

    validarParametros($dataInicial, $dataFinal, $tipoDoc, $cpfCnpj);

    $token = bin2hex(random_bytes(32));
    $tempId = bin2hex(random_bytes(12));
    $estado = [
        'versao' => 1,
        'modo' => 'interativo',
        'usuario_id' => (int) $usuarioId,
        'token_hash' => hash('sha256', $token),
        'data_inicial' => $dataInicial,
        'data_final' => $dataFinal,
        'tipo_doc' => $tipoDoc,
        'cpfcnpj' => $cpfCnpj,
        'limite' => EFIN_WEB_LIMITE_CONSULTA,
        'offset' => 0,
        'eventos_processados' => 0,
        'arquivos_gerados' => 0,
        'arquivos_compactados' => 0,
        'fase' => 'preparando',
        'temp_id' => $tempId,
        'atualizado_em' => date('c'),
    ];

    $sql = "INSERT INTO fila_tarefas_background (tipo_tarefa, parametros, status)
            VALUES (:tipo, :parametros, :status)
            RETURNING id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':tipo' => EFIN_WEB_TIPO_TAREFA,
        ':parametros' => codificarEstado($estado),
        ':status' => EFIN_WEB_STATUS_PENDENTE,
    ]);

    $ticketId = (int) $stmt->fetchColumn();
    if ($ticketId <= 0) {
        throw new RuntimeException('Nao foi possivel criar o ticket de processamento.');
    }

    responderJson([
        'sucesso' => true,
        'ticket_id' => $ticketId,
        'token' => $token,
        'estado' => estadoPublico($estado),
    ]);
}

function validarParametros($dataInicial, $dataFinal, $tipoDoc, $cpfCnpj)
{
    $inicio = DateTime::createFromFormat('!Y-m', $dataInicial);
    $fim = DateTime::createFromFormat('!Y-m', $dataFinal);
    $inicioValido = $inicio && $inicio->format('Y-m') === $dataInicial;
    $fimValido = $fim && $fim->format('Y-m') === $dataFinal;

    if (!$inicioValido || !$fimValido || $inicio > $fim) {
        throw new InvalidArgumentException('Informe um periodo valido para a geracao.');
    }

    if (!in_array($tipoDoc, ['todos', 'cpf', 'cnpj'], true)) {
        throw new InvalidArgumentException('Tipo de documento invalido.');
    }

    if ($cpfCnpj !== '') {
        if ($tipoDoc === 'cpf' && strlen($cpfCnpj) !== 11) {
            throw new InvalidArgumentException('Informe um CPF com 11 digitos.');
        }
        if ($tipoDoc === 'cnpj' && strlen($cpfCnpj) !== 14) {
            throw new InvalidArgumentException('Informe um CNPJ com 14 digitos.');
        }
    }
}

function consultarStatus(PDO $pdo, $usuarioId)
{
    list($ticketId, $token) = dadosTicketRecebidos();
    $tarefa = carregarTicket($pdo, $ticketId);
    $estado = validarAcessoTicket($tarefa, $usuarioId, $token);

    responderJson([
        'sucesso' => true,
        'ticket_id' => $ticketId,
        'status' => $tarefa['status'],
        'mensagem' => $tarefa['mensagem_erro'],
        'estado' => estadoPublico($estado),
        'concluido' => $tarefa['status'] === 'CONCLUIDO',
    ]);
}

function processarTicket(PDO $pdo, $usuarioId)
{
    list($ticketId, $token) = dadosTicketRecebidos();
    $tarefa = carregarTicket($pdo, $ticketId);
    $estado = validarAcessoTicket($tarefa, $usuarioId, $token);

    iniciarStream();

    if ($tarefa['status'] === 'CONCLUIDO') {
        emitirEvento([
            'tipo' => 'concluido',
            'ticket_id' => $ticketId,
            'estado' => estadoPublico($estado),
        ]);
        exit;
    }

    if ($tarefa['status'] === 'ERRO') {
        emitirEvento(['tipo' => 'erro', 'mensagem' => (string) $tarefa['mensagem_erro']]);
        exit;
    }

    $sqlClaim = "UPDATE fila_tarefas_background
                 SET status = :processando, data_inicio_processamento = NOW(), mensagem_erro = NULL
                 WHERE id = :id
                   AND tipo_tarefa = :tipo
                   AND (
                       status = :pendente
                       OR (status = :processando_atual AND data_inicio_processamento < NOW() - INTERVAL '30 minutes')
                   )
                 RETURNING id";
    $stmtClaim = $pdo->prepare($sqlClaim);
    $stmtClaim->execute([
        ':processando' => EFIN_WEB_STATUS_PROCESSANDO,
        ':processando_atual' => EFIN_WEB_STATUS_PROCESSANDO,
        ':pendente' => EFIN_WEB_STATUS_PENDENTE,
        ':id' => $ticketId,
        ':tipo' => EFIN_WEB_TIPO_TAREFA,
    ]);

    if (!$stmtClaim->fetchColumn()) {
        emitirEvento([
            'tipo' => 'ocupado',
            'mensagem' => 'O processamento deste ticket ainda esta ativo. Uma nova tentativa sera feita automaticamente.',
        ]);
        exit;
    }

    $liberado = false;
    register_shutdown_function(function () use ($pdo, $ticketId, &$liberado) {
        if ($liberado) {
            return;
        }

        try {
            $stmt = $pdo->prepare("UPDATE fila_tarefas_background
                                  SET status = :pendente
                                  WHERE id = :id AND status = :processando");
            $stmt->execute([
                ':pendente' => EFIN_WEB_STATUS_PENDENTE,
                ':processando' => EFIN_WEB_STATUS_PROCESSANDO,
                ':id' => $ticketId,
            ]);
        } catch (Throwable $ignorado) {
            // O proximo acesso tambem pode recuperar claims antigos apos 30 minutos.
        }
    });

    emitirEvento([
        'tipo' => 'inicio',
        'ticket_id' => $ticketId,
        'estado' => estadoPublico($estado),
    ]);

    try {
        executarEtapas($pdo, $ticketId, $estado);

        if (($estado['fase'] ?? '') === 'concluido') {
            $liberado = true;
            emitirEvento([
                'tipo' => 'concluido',
                'ticket_id' => $ticketId,
                'estado' => estadoPublico($estado),
            ]);
            exit;
        }

        salvarEstado($pdo, $ticketId, $estado, EFIN_WEB_STATUS_PENDENTE);
        $liberado = true;
        emitirEvento([
            'tipo' => 'continuar',
            'ticket_id' => $ticketId,
            'estado' => estadoPublico($estado),
        ]);
    } catch (Throwable $e) {
        limparTemporariosDoTicket($ticketId, $estado);
        $stmtErro = $pdo->prepare("UPDATE fila_tarefas_background
                                  SET status = 'ERRO', mensagem_erro = :mensagem, data_conclusao = NOW()
                                  WHERE id = :id");
        $stmtErro->execute([':mensagem' => $e->getMessage(), ':id' => $ticketId]);
        $liberado = true;
        emitirEvento(['tipo' => 'erro', 'mensagem' => $e->getMessage()]);
    }
}

function executarEtapas(PDO $pdo, $ticketId, array &$estado)
{
    $inicioRequisicao = microtime(true);
    validarDiretorioExistente(EFIN_WEB_BASE_ZIP);

    if (($estado['fase'] ?? '') === 'preparando') {
        $estado['fase'] = 'gerando_xml';
        salvarEstado($pdo, $ticketId, $estado, EFIN_WEB_STATUS_PROCESSANDO);
    }

    while ((microtime(true) - $inicioRequisicao) < EFIN_WEB_LIMITE_SEGUNDOS_REQUISICAO) {
        if (($estado['fase'] ?? '') === 'gerando_xml') {
            processarPaginaXml($pdo, $ticketId, $estado);
        } elseif (($estado['fase'] ?? '') === 'compactando') {
            compactarPagina($pdo, $ticketId, $estado);
        } else {
            throw new RuntimeException('Fase de processamento desconhecida.');
        }

        if (($estado['fase'] ?? '') === 'concluido' || connection_aborted()) {
            return;
        }
    }
}

function processarPaginaXml(PDO $pdo, $ticketId, array &$estado)
{
    emitirEvento([
        'tipo' => 'heartbeat',
        'fase' => 'gerando_xml',
        'mensagem' => 'Consultando o proximo bloco de registros...',
        'estado' => estadoPublico($estado),
    ]);

    $tipoDoc = ($estado['tipo_doc'] ?? 'todos');
    $paramTipoDoc = ($tipoDoc === 'todos' || $tipoDoc === '') ? null : $tipoDoc;
    $cpfCnpj = (string) ($estado['cpfcnpj'] ?? '');
    $paramCpfCnpj = $cpfCnpj === '' ? null : $cpfCnpj;

    $gerador = new GerarEFinanceira();
    $resultado = $gerador->gerarXmlMovimentacao(
        $estado['data_inicial'],
        $estado['data_final'],
        (int) $estado['limite'],
        (int) $estado['offset'],
        $paramTipoDoc,
        $paramCpfCnpj
    );

    if (!$resultado || empty($resultado['xmls'])) {
        if ((int) ($estado['arquivos_gerados'] ?? 0) === 0) {
            throw new RuntimeException('Nenhum evento foi encontrado para gerar o arquivo ZIP.');
        }

        $estado['fase'] = 'compactando';
        $estado['atualizado_em'] = date('c');
        salvarEstado($pdo, $ticketId, $estado, EFIN_WEB_STATUS_PROCESSANDO);
        emitirEvento([
            'tipo' => 'fase',
            'fase' => 'compactando',
            'mensagem' => 'XMLs gerados. Iniciando a compactacao...',
            'estado' => estadoPublico($estado),
        ]);
        return;
    }

    foreach ($resultado['xmls'] as $item) {
        $numeroArquivo = (int) $estado['arquivos_gerados'] + 1;
        $anoMes = preg_replace('/[^0-9]/', '', (string) ($item['ano_mes'] ?? 'sem_data'));
        $nomeXml = sprintf('%s%s_lote_%06d.xml', prefixoTemporario($ticketId, $estado), $anoMes ?: 'sem_data', $numeroArquivo);
        $caminhoXml = EFIN_WEB_BASE_ZIP . '/' . $nomeXml;
        $gravados = file_put_contents($caminhoXml, (string) ($item['xml'] ?? ''));

        if ($gravados === false) {
            throw new RuntimeException('Falha ao gravar um arquivo XML temporario.');
        }

        $estado['arquivos_gerados'] = $numeroArquivo;
    }

    $estado['eventos_processados'] = (int) $estado['eventos_processados'] + (int) ($resultado['total_eventos'] ?? 0);
    $estado['offset'] = (int) $estado['offset'] + (int) $estado['limite'];
    $estado['atualizado_em'] = date('c');
    salvarEstado($pdo, $ticketId, $estado, EFIN_WEB_STATUS_PROCESSANDO);

    emitirEvento([
        'tipo' => 'progresso',
        'fase' => 'gerando_xml',
        'mensagem' => 'Bloco concluido e salvo com seguranca.',
        'estado' => estadoPublico($estado),
    ]);
}

function compactarPagina(PDO $pdo, $ticketId, array &$estado)
{
    $prefixo = prefixoTemporario($ticketId, $estado);
    $arquivos = glob(EFIN_WEB_BASE_ZIP . '/' . $prefixo . '*.xml');
    if ($arquivos === false) {
        throw new RuntimeException('Nao foi possivel listar os XMLs temporarios.');
    }
    sort($arquivos, SORT_STRING);

    $zipTemporario = caminhoZipTemporario($ticketId, $estado);

    if (empty($arquivos)) {
        finalizarZip($pdo, $ticketId, $estado, $zipTemporario);
        return;
    }

    $grupo = array_slice($arquivos, 0, EFIN_WEB_LOTE_COMPACTACAO);
    $zip = new ZipArchive();
    $aberto = $zip->open($zipTemporario, ZipArchive::CREATE);
    if ($aberto !== true) {
        throw new RuntimeException('Nao foi possivel abrir o ZIP temporario. Codigo: ' . $aberto);
    }

    foreach ($grupo as $arquivo) {
        $nomeInterno = substr(basename($arquivo), strlen($prefixo));
        if (!$zip->addFile($arquivo, $nomeInterno)) {
            $zip->close();
            throw new RuntimeException('Falha ao adicionar um XML ao arquivo ZIP.');
        }
    }

    $quantidadeNoZip = $zip->numFiles;
    if (!$zip->close()) {
        throw new RuntimeException('Falha ao salvar uma etapa do arquivo ZIP.');
    }

    foreach ($grupo as $arquivo) {
        if (is_file($arquivo)) {
            unlink($arquivo);
        }
    }

    $estado['arquivos_compactados'] = (int) $quantidadeNoZip;
    $estado['atualizado_em'] = date('c');
    salvarEstado($pdo, $ticketId, $estado, EFIN_WEB_STATUS_PROCESSANDO);

    emitirEvento([
        'tipo' => 'progresso',
        'fase' => 'compactando',
        'mensagem' => 'Arquivos adicionados ao ZIP.',
        'estado' => estadoPublico($estado),
    ]);
}

function finalizarZip(PDO $pdo, $ticketId, array &$estado, $zipTemporario)
{
    if (!is_file($zipTemporario)) {
        throw new RuntimeException('O ZIP temporario nao foi encontrado.');
    }

    $inicio = preg_replace('/[^0-9-]/', '', (string) $estado['data_inicial']);
    $fim = preg_replace('/[^0-9-]/', '', (string) $estado['data_final']);
    $nomeFinal = sprintf(
        'lotes_%s_%s_%s_%d_ticket%d.zip',
        $inicio,
        $fim,
        date('Ymd_Hi'),
        (int) $estado['arquivos_gerados'],
        $ticketId
    );
    $caminhoFinal = EFIN_WEB_BASE_ZIP . '/' . $nomeFinal;

    if (!rename($zipTemporario, $caminhoFinal)) {
        throw new RuntimeException('Nao foi possivel concluir o arquivo ZIP.');
    }

    $urlDownload = EFIN_WEB_URL_ZIP . $nomeFinal;
    $estado['fase'] = 'concluido';
    $estado['url_download'] = $urlDownload;
    $estado['atualizado_em'] = date('c');

    $sql = "UPDATE fila_tarefas_background
            SET status = 'CONCLUIDO', parametros = :parametros, caminho_arquivo = :caminho,
                data_conclusao = NOW(), mensagem_erro = NULL
            WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':parametros' => codificarEstado($estado),
        ':caminho' => $urlDownload,
        ':id' => $ticketId,
    ]);
}

function salvarEstado(PDO $pdo, $ticketId, array &$estado, $status)
{
    $estado['atualizado_em'] = date('c');
    $stmt = $pdo->prepare("UPDATE fila_tarefas_background
                          SET parametros = :parametros, status = :status
                          WHERE id = :id AND tipo_tarefa = :tipo");
    $stmt->execute([
        ':parametros' => codificarEstado($estado),
        ':status' => $status,
        ':id' => $ticketId,
        ':tipo' => EFIN_WEB_TIPO_TAREFA,
    ]);
}

function carregarTicket(PDO $pdo, $ticketId)
{
    $stmt = $pdo->prepare("SELECT id, tipo_tarefa, parametros, status, caminho_arquivo, mensagem_erro
                          FROM fila_tarefas_background
                          WHERE id = :id AND tipo_tarefa = :tipo
                          LIMIT 1");
    $stmt->execute([':id' => $ticketId, ':tipo' => EFIN_WEB_TIPO_TAREFA]);
    $tarefa = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tarefa) {
        throw new RuntimeException('Ticket de processamento nao encontrado.');
    }

    return $tarefa;
}

function validarAcessoTicket(array $tarefa, $usuarioId, $token)
{
    $estado = json_decode((string) $tarefa['parametros'], true);
    if (!is_array($estado)) {
        throw new RuntimeException('Estado do ticket invalido.');
    }

    $mesmoUsuario = (int) ($estado['usuario_id'] ?? 0) === (int) $usuarioId;
    $tokenHash = (string) ($estado['token_hash'] ?? '');
    $tokenValido = $token !== '' && $tokenHash !== '' && hash_equals($tokenHash, hash('sha256', $token));

    if (!$mesmoUsuario || !$tokenValido) {
        throw new RuntimeException('Acesso negado ao ticket de processamento.');
    }

    return $estado;
}

function dadosTicketRecebidos()
{
    $ticketId = isset($_POST['ticket_id']) ? (int) $_POST['ticket_id'] : 0;
    $token = isset($_POST['token']) ? (string) $_POST['token'] : '';

    if ($ticketId <= 0 || $token === '') {
        throw new InvalidArgumentException('Ticket ou token invalido.');
    }

    return [$ticketId, $token];
}

function codificarEstado(array $estado)
{
    $json = json_encode($estado, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    if ($json === false) {
        throw new RuntimeException('Falha ao salvar o estado do processamento.');
    }
    return $json;
}

function estadoPublico(array $estado)
{
    return [
        'fase' => (string) ($estado['fase'] ?? 'preparando'),
        'offset' => (int) ($estado['offset'] ?? 0),
        'eventos_processados' => (int) ($estado['eventos_processados'] ?? 0),
        'arquivos_gerados' => (int) ($estado['arquivos_gerados'] ?? 0),
        'arquivos_compactados' => (int) ($estado['arquivos_compactados'] ?? 0),
        'atualizado_em' => (string) ($estado['atualizado_em'] ?? ''),
    ];
}

function prefixoTemporario($ticketId, array $estado)
{
    $tempId = preg_replace('/[^a-f0-9]/', '', (string) ($estado['temp_id'] ?? ''));
    if ($tempId === '') {
        throw new RuntimeException('Identificador temporario invalido.');
    }
    return '.web_' . (int) $ticketId . '_' . $tempId . '_';
}

function caminhoZipTemporario($ticketId, array $estado)
{
    $tempId = preg_replace('/[^a-f0-9]/', '', (string) ($estado['temp_id'] ?? ''));
    return EFIN_WEB_BASE_ZIP . '/.web_' . (int) $ticketId . '_' . $tempId . '.part.zip';
}

function validarDiretorioExistente($diretorio)
{
    if (!is_dir($diretorio) || !is_writable($diretorio)) {
        throw new RuntimeException('A pasta de lotes enviados nao existe ou nao possui permissao de escrita.');
    }
}

function limparTemporariosDoTicket($ticketId, array $estado)
{
    try {
        $arquivos = glob(EFIN_WEB_BASE_ZIP . '/' . prefixoTemporario($ticketId, $estado) . '*.xml');
        if (is_array($arquivos)) {
            foreach ($arquivos as $arquivo) {
                if (is_file($arquivo)) {
                    unlink($arquivo);
                }
            }
        }
        $zipTemporario = caminhoZipTemporario($ticketId, $estado);
        if (is_file($zipTemporario)) {
            unlink($zipTemporario);
        }
    } catch (Throwable $ignorado) {
        // A mensagem original do processamento deve ser preservada.
    }
}

function iniciarStream()
{
    set_time_limit(0);
    ignore_user_abort(false);

    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    header('Content-Type: application/x-ndjson; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, no-transform');
    header('X-Accel-Buffering: no');
    header('X-Content-Type-Options: nosniff');
}

function emitirEvento(array $evento)
{
    echo json_encode($evento, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE) . "\n";
    flush();
}

function baixarArquivo(PDO $pdo, $usuarioId, $csrfSessao)
{
    $csrfRecebido = isset($_POST['csrf_token']) ? (string) $_POST['csrf_token'] : '';
    if ($csrfSessao === '' || !hash_equals($csrfSessao, $csrfRecebido)) {
        responderJson(['sucesso' => false, 'mensagem' => 'Token de seguranca invalido.'], 403);
    }

    list($ticketId, $token) = dadosTicketRecebidos();
    $tarefa = carregarTicket($pdo, $ticketId);
    validarAcessoTicket($tarefa, $usuarioId, $token);

    if ($tarefa['status'] !== 'CONCLUIDO' || empty($tarefa['caminho_arquivo'])) {
        responderJson(['sucesso' => false, 'mensagem' => 'O arquivo ainda nao esta disponivel.'], 409);
    }

    $caminhoRelativo = (string) $tarefa['caminho_arquivo'];
    if (strpos($caminhoRelativo, EFIN_WEB_URL_ZIP) !== 0) {
        responderJson(['sucesso' => false, 'mensagem' => 'Caminho de download invalido.'], 403);
    }

    $baseReal = realpath(EFIN_WEB_BASE_ZIP);
    $arquivoReal = realpath('/www' . $caminhoRelativo);
    if ($baseReal === false || $arquivoReal === false || strpos($arquivoReal, $baseReal . DIRECTORY_SEPARATOR) !== 0 || !is_file($arquivoReal)) {
        responderJson(['sucesso' => false, 'mensagem' => 'Arquivo nao encontrado.'], 404);
    }

    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    header('Content-Description: File Transfer');
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . basename($arquivoReal) . '"');
    header('Content-Length: ' . filesize($arquivoReal));
    header('Cache-Control: private, no-store, must-revalidate');
    header('Pragma: public');
    readfile($arquivoReal);
    exit;
}
