<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

require_once "../includes/main.php";
require_once $raiz_do_projeto . "includes/pdv/main.php";
require_once $raiz_do_projeto . "includes/functions.php";
require_once $raiz_do_projeto . "class/classManipulacaoArquivosLog.php";

$arquivoLog = new ManipulacaoArquivosLog($argv);

if (!$arquivoLog->haveFile()) {
    $arquivoLog->createLockedFile();
    $nome_arquivo = $arquivoLog->getNomeArquivo();

    //Capturando argumento da lista de finais de VG_IDs
    $shellcommand = implode(" ", $argv);
    $lista_finais = NULL;
    $pattern = '/--lista=([^ ]+)/';
    $match = array();
    if (preg_match($pattern, $shellcommand, $match)) {
        $lista_finais = $match[1];
    }
    if (!is_null($lista_finais)) {
        echo "Lista de finais de UG_IDs [" . $lista_finais . "]" . PHP_EOL;
    }

    set_time_limit(0);
    if ($lista_finais == '9,0') {
        try {
            // Pega a conexão PDO do seu sistema
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

            // Se não tiver nada pendente, o worker morre silenciosamente
            if (!$tarefa) {
                exit;
            }

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
    }

    ob_start('callbackLog');

    //Printando hora de execução
    echo str_repeat("=", 80) . "\n" . date("Y-m-d H:i:s") . PHP_EOL;


    $inicioTempo = microtime(true);
    //processaAgendamentos
    if (in_array("processaAgendamentos", $argv)) echo processaAgendamentos($lista_finais);
    $fimTempo = microtime(true);

    echo "Tempo decorrido: " . ($fimTempo - $inicioTempo) . " segundos";

    $arquivoLog->deleteLockedFile();
} else {
    $arquivoLog->showBusy();
}


//Fechando Conexão
pg_close($connid);
