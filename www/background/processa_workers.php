<?php
require "/www/db/connect.php";
require "/www/db/ConnectionPDO.php";

try {
    $inicioTempo = microtime(true);
    set_time_limit(0);
    $pdo = ConnectionPDO::getConnection()->getLink();

    // 1. Busca e "trava" a próxima tarefa pendente em um único comando atômico
    $sqlLock = "UPDATE fila_tarefas_background 
                SET status = 'PROCESSANDO', data_inicio_processamento = NOW() 
                WHERE id = (
                    SELECT id FROM fila_tarefas_background 
                    WHERE status = 'PENDENTE' 
                    ORDER BY id ASC LIMIT 1
                ) 
                RETURNING *";

    $stmt = $pdo->query($sqlLock);
    $tarefa = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($tarefa) {
        $id_tarefa = $tarefa['id'];
        $tipo_tarefa = $tarefa['tipo_tarefa'];
        $parametros = json_decode($tarefa['parametros'], true);

        echo "Iniciando tarefa #{$id_tarefa} - Tipo: {$tipo_tarefa}\n";

        $url_download_final = null;

        // 2. Roteador de Tarefas
        switch ($tipo_tarefa) {

            case 'gerar_zip_efinanceira':
                require __DIR__ . "/worker_efinanceira.php";
                $url_download_final = workerGerarZipEfinanceira($parametros);
                break;

            // case 'outra_rotina_pdf':
            //     $url_download_final = workerGerarRelatorioPdf($parametros);
            //     break;

            default:
                throw new Exception("Tipo de tarefa desconhecido: {$tipo_tarefa}");
        }

        // 3. Atualiza o banco indicando o SUCESSO e o caminho do arquivo de forma global
        $sqlUpd = "UPDATE fila_tarefas_background SET status = 'CONCLUIDO', caminho_arquivo = :caminho, data_conclusao = NOW() WHERE id = :id";
        $stmtUpd = $pdo->prepare($sqlUpd);
        $stmtUpd->execute([':caminho' => $url_download_final, ':id' => $id_tarefa]);

        echo "Tarefa #{$id_tarefa} finalizada com sucesso.\n";
    }
} catch (Exception $e) {
    // 4. Captura qualquer erro de qualquer worker e atualiza o banco de dados
    if (isset($id_tarefa) && isset($pdo)) {
        $msgErro = $e->getMessage();
        $sqlErr = "UPDATE fila_tarefas_background SET status = 'ERRO', mensagem_erro = :msg, data_conclusao = NOW() WHERE id = :id";
        $stmtErr = $pdo->prepare($sqlErr);
        $stmtErr->execute([':msg' => $msgErro, ':id' => $id_tarefa]);
    }
    echo "ERRO CRÍTICO na Tarefa: " . $e->getMessage() . "\n";
}

$fimTempo = microtime(true);
echo "Tempo decorrido: " . ($fimTempo - $inicioTempo) . " segundos";