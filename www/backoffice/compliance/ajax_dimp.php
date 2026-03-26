<?php require_once __DIR__ . '/../../includes/constantes_url.php'; ?>
<?php

if (isset($_GET['directory']) && isset($_GET['name'])) {
    $baseDir = '/www/arquivos_gerados/dimp';

    $directory = $_GET['directory'] ?? '';
    $filename  = $_GET['name'] ?? '';

    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $directory)) {
        http_response_code(400);
        exit('Diretório inválido.');
    }

    if (!preg_match('/^[a-zA-Z0-9_-]+\.txt$/i', $filename)) {
        http_response_code(400);
        exit('Nome de arquivo inválido.');
    }

    $fullPath = $baseDir . '/' . $directory . '/' . $filename;

    $realBase = realpath($baseDir);
    $realFile = realpath($fullPath);

    if ($realFile === false || strpos($realFile, $realBase) !== 0) {
        http_response_code(403);
        exit('Acesso negado.');
    }

    if (!is_file($realFile) || !is_readable($realFile)) {
        http_response_code(404);
        exit('Arquivo não encontrado.');
    }

    header('Content-Type: text/plain; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . basename($realFile) . '"');
    header('Content-Length: ' . filesize($realFile));
    header('X-Content-Type-Options: nosniff');

    readfile($realFile);
    exit;
}

require_once "../../includes/constantes.php";
require_once "../../db/connect.php";
require_once "../../db/ConnectionPDO.php";

try {
    $pdo = ConnectionPDO::getConnection()->getLink();
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'solicitar_download') {
        header('Content-Type: application/json');

        $estado = strtoupper(trim((string)($_POST['estado'] ?? '')));
        $dataInicialRaw = trim((string)($_POST['data_inicial'] ?? ''));
        $codFin = trim((string)($_POST['cod_fin'] ?? ''));
        $cpfCnpj = preg_replace('/\D+/', '', (string)($_POST['cpfcnpj'] ?? ''));

        // Normaliza "MM/YYYY" (aceita "M/YYYY", "MM/YY", "M/YY")
        $dataInicial = $dataInicialRaw;
        if ($dataInicialRaw !== '' && preg_match('/^\s*(\d{1,2})\s*\/\s*(\d{2,4})\s*$/', $dataInicialRaw, $m)) {
            $mes = (int)$m[1];
            $ano = (int)$m[2];
            if ($ano < 100) {
                $ano += 2000;
            }
            if ($mes >= 1 && $mes <= 12 && $ano >= 2000 && $ano <= 2100) {
                $dataInicial = str_pad((string)$mes, 2, '0', STR_PAD_LEFT) . '/' . (string)$ano;
            }
        }

        $parametros = json_encode([
            'estado'       => $estado,
            'data_inicial' => $dataInicial,
            'cod_fin'      => $codFin,
            'cpfcnpj'      => $cpfCnpj
        ]);

        // VERIFICAÇÃO DE CACHE/FILA (últimos 15 minutos, mesmos parâmetros)
        // Fluxo:
        // - Se existir PENDENTE/PROCESSANDO: reaproveita (espera terminar)
        // - Se existir CONCLUIDO sem erro: reaproveita (pode baixar)
        // - Se existir ERRO (ou CONCLUIDO com erro): cria uma nova tarefa
        $sqlBusca = "SELECT id, status, caminho_arquivo, mensagem_erro
                       FROM fila_tarefas_background
                      WHERE tipo_tarefa = 'gerar_dimp'
                        AND parametros = :params
                        AND data_solicitacao >= NOW() - INTERVAL '90 minutes'
                   ORDER BY id DESC
                      LIMIT 1";

        $stmtBusca = $pdo->prepare($sqlBusca);
        $stmtBusca->execute([':params' => $parametros]);
        $existente = $stmtBusca->fetch(PDO::FETCH_ASSOC);

        if ($existente) {
            $status = (string)($existente['status'] ?? '');
            $msgErro = isset($existente['mensagem_erro']) ? trim((string)$existente['mensagem_erro']) : '';
            $caminhoArquivo = isset($existente['caminho_arquivo']) ? trim((string)$existente['caminho_arquivo']) : '';

            // Se já existe uma execução em andamento, reaproveita o ticket
            if ($status === 'PENDENTE' || $status === 'PROCESSANDO') {
                echo json_encode(['sucesso' => true, 'ticket_id' => (int)$existente['id']]);
                exit;
            }

            // Se concluiu e não há erro e tem caminho de download, reaproveita e já devolve o link
            if ($status === 'CONCLUIDO' && $msgErro === '' && $caminhoArquivo !== '') {
                echo json_encode([
                    'sucesso' => true,
                    'ticket_id' => (int)$existente['id'],
                    'status' => 'CONCLUIDO',
                    'caminho_arquivo' => $caminhoArquivo
                ]);
                exit;
            }

            // Se houve erro, cria uma nova tarefa (não reaproveita)
            // (inclui CONCLUIDO com mensagem_erro preenchida)
        }

        $sql = "INSERT INTO fila_tarefas_background (tipo_tarefa, parametros, status) 
        VALUES ('gerar_dimp', :params, 'PENDENTE') 
        RETURNING id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':params' => $parametros]);
        $ticket_id = $stmt->fetchColumn();

        if ($ticket_id) {
            echo json_encode(['sucesso' => true, 'ticket_id' => $ticket_id]);
        } else {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Falha ao registrar tarefa no banco.']);
        }
        exit;
    }

    if ($acao === 'checar_status') {
        header('Content-Type: application/json');
        $ticket_id = isset($_POST['ticket_id']) ? (int)$_POST['ticket_id'] : 0;

        if ($ticket_id <= 0) {
            echo json_encode(['status' => 'ERRO', 'mensagem_erro' => 'Ticket inválido.']);
            exit;
        }

        $sql = "SELECT status, caminho_arquivo, mensagem_erro 
        FROM fila_tarefas_background 
        WHERE id = :id LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $ticket_id]);
        $tarefa = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$tarefa) {
            echo json_encode(['status' => 'ERRO', 'mensagem_erro' => 'Tarefa não encontrada.']);
            exit;
        }

        echo json_encode([
            'status' => $tarefa['status'],
            'caminho_arquivo' => $tarefa['caminho_arquivo'],
            'mensagem_erro' => $tarefa['mensagem_erro']
        ]);
        exit;
    }
} catch (Exception $e) {
    if (isset($_POST['acao']) && $_POST['acao'] === 'solicitar_download') {
        header('Content-Type: application/json');
        echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'ERRO', 'mensagem_erro' => $e->getMessage()]);
    }
    exit;
}