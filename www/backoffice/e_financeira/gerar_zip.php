<?php
require __DIR__ . '/functions_e_financeira.php';

if (($_REQUEST['acao'] ?? "") == "abertura") {

    $data_inicio = $_GET['data_inicio'] ?? '';
    $data_fim    = $_GET['data_fim'] ?? '';

    $efinanceira = new GerarEFinanceira();
    if ($data_inicio && $data_fim) {
        $lotes = $efinanceira->gerarAbertura($data_inicio, $data_fim);

        if ($lotes) {

            header('Content-Type: application/xml; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . "abertura_{$data_inicio}_{$data_fim}_" . date('Ymd_Hi') . ".xml" . '"');
            header('Cache-Control: no-cache, no-store, must-revalidate'); // Limpa cache
            header('Pragma: no-cache');

            echo $efinanceira->gerarLoteAssincrono([$lotes]);
        } else {
            echo "Erro ao gerar o arquivo";
        }
    } else {
        echo "Parametros inválidos";
    }
} else if (($_REQUEST['acao'] ?? "") == "fechamento") {

    $data_inicio = $_POST['data_inicio'] ?? '';
    $data_fim    = $_POST['data_fim'] ?? '';
    $tem_movimentacoes = $_POST['tem_movimentacoes'] ?? 0;

    $efinanceira = new GerarEFinanceira();
    if ($data_inicio && $data_fim) {
        $lotes = $efinanceira->gerarFechamento($data_inicio, $data_fim, $tem_movimentacoes);

        if ($lotes) {

            header('Content-Type: application/xml; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . "fechamento_{$data_inicio}_{$data_fim}_" . date('Ymd_Hi') . ".xml" . '"');
            header('Cache-Control: no-cache, no-store, must-revalidate'); // Limpa cache
            header('Pragma: no-cache');

            echo $efinanceira->gerarLoteAssincrono([$lotes]);
        } else {
            echo "Erro ao gerar o arquivo";
        }
    } else {
        echo "Parametros inválidos";
    }
} else {

    $acao = $_REQUEST['acao'] ?? '';

    try {
        $pdo = ConnectionPDO::getConnection()->getLink();

        // =========================================================================
        // AÇÃO 1: Insere a requisição na fila (Com Verificação de Duplicidade)
        // =========================================================================
        if ($acao === 'solicitar_download') {
            header('Content-Type: application/json'); // Define JSON apenas para esta rota

            $parametros = json_encode([
                'data_inicial' => $_POST['data_inicial'] ?? '',
                'data_final'   => $_POST['data_final'] ?? '',
                'tipo_doc'     => $_POST['tipo_doc'] ?? '',
                'cpfcnpj'      => $_POST['cpfcnpj'] ?? ''
            ]);

            // VERIFICAÇÃO DE CACHE/FILA:
            // Checa se já pediram exatamente a mesma coisa na última 1 hora
            $sqlBusca = "SELECT id FROM fila_tarefas_background 
                     WHERE tipo_tarefa = 'gerar_zip_efinanceira' 
                       AND parametros = :params 
                       AND status IN ('PENDENTE', 'PROCESSANDO', 'CONCLUIDO')
                       AND data_solicitacao >= NOW() - INTERVAL '1 hour'
                     ORDER BY id DESC LIMIT 1";

            $stmtBusca = $pdo->prepare($sqlBusca);
            $stmtBusca->execute([':params' => $parametros]);
            $ticket_existente = $stmtBusca->fetchColumn();

            if ($ticket_existente) {
                // Reaproveita a tarefa em andamento/concluída!
                echo json_encode(['sucesso' => true, 'ticket_id' => $ticket_existente]);
                exit;
            }

            // Se não existir, insere uma nova normalmente
            $sql = "INSERT INTO fila_tarefas_background (tipo_tarefa, parametros, status) 
            VALUES ('gerar_zip_efinanceira', :params, 'PENDENTE') 
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

        // =========================================================================
        // AÇÃO 2: Consulta o status da requisição
        // =========================================================================
        if ($acao === 'checar_status') {
            header('Content-Type: application/json'); // Define JSON apenas para esta rota

            $ticket_id = isset($_GET['ticket_id']) ? (int)$_GET['ticket_id'] : 0;

            if ($ticket_id <= 0) {
                echo json_encode(['status' => 'ERRO', 'mensagem' => 'ID de ticket inválido.']);
                exit;
            }

            $sql = "SELECT status, mensagem_erro 
            FROM fila_tarefas_background 
            WHERE id = :id LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $ticket_id]);
            $tarefa = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($tarefa) {
                echo json_encode([
                    'status' => $tarefa['status'],
                    // Aponta explicitamente para o arquivo PHP correto
                    'url_download' => "gerar_zip.php?acao=baixar_arquivo&ticket_id=" . $ticket_id,
                    'mensagem' => $tarefa['mensagem_erro']
                ]);
            } else {
                echo json_encode(['status' => 'ERRO', 'mensagem' => 'Tarefa não encontrada.']);
            }
            exit;
        }

        // =========================================================================
        // AÇÃO 3: Download Seguro (Lê da pasta privada e entrega ao usuário)
        // =========================================================================
        if ($acao === 'baixar_arquivo') {
            $ticket_id = isset($_GET['ticket_id']) ? (int)$_GET['ticket_id'] : 0;

            // Pega o caminho físico real salvo pelo Worker
            $sql = "SELECT status, caminho_arquivo FROM fila_tarefas_background WHERE id = :id LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $ticket_id]);
            $tarefa = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($tarefa && $tarefa['status'] === 'CONCLUIDO' && !empty($tarefa['caminho_arquivo'])) {
                $caminho_fisico = "/www" . $tarefa['caminho_arquivo'];

                // Valida se o arquivo realmente existe na pasta restrita do servidor Linux
                if (file_exists($caminho_fisico)) {
                    $nome_arquivo = basename($caminho_fisico);

                    // Força o download binário sem exibir a URL real
                    header('Content-Description: File Transfer');
                    header('Content-Type: application/zip');
                    header('Content-Disposition: attachment; filename="' . $nome_arquivo . '"');
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate');
                    header('Pragma: public');
                    header('Content-Length: ' . filesize($caminho_fisico));

                    // Limpa o buffer de saída (previne arquivos corrompidos)
                    if (ob_get_level() > 0) {
                        ob_end_clean();
                    }

                    // Lê o arquivo e cospe para o navegador
                    readfile($caminho_fisico);
                    exit;
                } else {
                    http_response_code(404);
                    exit('Arquivo físico não encontrado no servidor. O prazo de armazenamento pode ter expirado.');
                }
            } else {
                http_response_code(403);
                exit('Acesso negado ou arquivo ainda não está pronto.');
            }
        }
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode(['sucesso' => false, 'status' => 'ERRO', 'mensagem' => $e->getMessage()]);
        exit;
    }

    header('Content-Type: application/json');
    echo json_encode(['sucesso' => false, 'mensagem' => 'Ação inválida.']);
    exit;
}
