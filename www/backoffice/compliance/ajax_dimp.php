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
        
        $parametros = json_encode([
            'estado'       => $_POST['estado'] ?? '',
            'data_inicial' => $_POST['data_inicial'] ?? '',
            'cod_fin'      => $_POST['cod_fin'] ?? '',
            'cpfcnpj'      => $_POST['cpfcnpj'] ?? ''
        ]);

        // VERIFICAÇÃO DE CACHE/FILA
        $sqlBusca = "SELECT id FROM fila_tarefas_background 
                 WHERE tipo_tarefa = 'gerar_dimp' 
                   AND parametros = :params 
                   AND status IN ('PENDENTE', 'PROCESSANDO', 'CONCLUIDO', 'ERRO')
                   AND data_solicitacao >= NOW() - INTERVAL '15 minutes'
                 ORDER BY id DESC LIMIT 1";

        $stmtBusca = $pdo->prepare($sqlBusca);
        $stmtBusca->execute([':params' => $parametros]);
        $ticket_existente = $stmtBusca->fetchColumn();

        if ($ticket_existente) {
            echo json_encode(['sucesso' => true, 'ticket_id' => $ticket_existente]);
            exit;
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