<?php
function temLogInconsistente($ug_id, $pdo)
{
    // 1. Pega todos os logs de altera��o de e-mail na �ltima semana
    $stmt = $pdo->prepare("
        SELECT ug_id, data_log
        FROM log_alteracao_email
        WHERE data_log >= NOW() - INTERVAL '7 days'
          AND ug_id = :ug_id
    ");
    $stmt->execute([':ug_id' => $ug_id]);
    $logsEmail = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($logsEmail as $log) {
        $dataLog = $log['data_log'];

        // 2. Verifica se existe um log do tipo 3 no intervalo de 5 segundos
        $stmtCheck = $pdo->prepare("
            SELECT 1
            FROM dist_usuarios_games_log
            WHERE ugl_ug_id = :ug_id
              AND ugl_uglt_id = 3
              AND ABS(EXTRACT(EPOCH FROM (ugl_data_inclusao - :data_log))) <= 5
            LIMIT 1
        ");
        $stmtCheck->execute([
            ':ug_id' => $ug_id,
            ':data_log' => $dataLog,
        ]);

        if (!$stmtCheck->fetch()) {
            // Log de e-mail sem log correspondente dentro de 5s
            return true;
        }
    }

    // Todos os logs t�m correspond�ncia v�lida
    return false;
}

function enviaEmailReport($motivo, $user)
{
    // Configura��es do e-mail
    $to = 'jose.carlos@easygroupit.com, rc@e-prepag.com.br, glaucia@e-prepag.com.br';
    $cc = "";
    $subject = 'Notifica��o de bloqueio no PDV';
    $bcc = "";
    // Monta o corpo do e-mail com as altera��es
    $message = "<h1>Notifica��o de bloqueio no PDV</h1>";
    $message .= "<p>O seguinte PDV foi bloqueado por suspeita de fraude:</p>";
    $message .= "<ul>";
    $message .= "<li><strong>PDV ID: </strong> " . $user['ug_id'] . "<br>";
    $message .= "<strong>Login: </strong> " . $user['ug_login'] . "<br>";
    $message .= "<strong>Email: </strong> " . $user['ug_email'] . "<br>";
    $message .= "<strong>Data da Altera��o: </strong> " . date('Y/m/d H:i') . "</li><br>";
    $message .= "</ul>";
    $message .= "<p>Motivo: $motivo</p>";
    $message .= "<p>Caso o PDV tenha sido bloqueado indevidamente, utilize o backoffice para desbloque�-lo.</p>";

    enviaEmail4($to, $cc, $bcc, $subject, $message, "");
}

function buscarUsuariosSemLog($pdo, $ug_id)
{
    $sql = "
        SELECT 1
        FROM dist_usuarios_games ug
        WHERE NOT EXISTS (
            SELECT 1
            FROM dist_usuarios_games_log ugl
            WHERE ugl.ugl_ug_id = ug.ug_id
              AND (ugl.ugl_uglt_id = 3 OR ugl.ugl_uglt_id = 38)
        )
        AND ug.ug_data_inclusao > '2025-01-01'
        AND ug.ug_id = ?
        LIMIT 1
    ";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$ug_id]);

        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    } catch (PDOException $e) {
        //echo "Erro ao verificar dados: " . $e->getMessage();
        file_put_contents('/www/log/log_login.txt', "Erro ao verificar dados: " . $e->getMessage(), FILE_APPEND);
        return false;
    }
}

function lerUsuariosBloqueados()
{
    $arquivo = __DIR__ . '/../../../db/usuarios_bloqueados.json';
    if (!file_exists($arquivo)) {
        // Tenta criar o arquivo com conte�do JSON vazio
        $inicial = json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $criado = file_put_contents($arquivo, $inicial);
        if ($criado === false) {
            throw new Exception("N�o foi criar o arquivo: $arquivo");
        }
        throw new Exception("N�o foi poss�vel abrir o arquivo para leitura: $arquivo");
    }
    $json = file_get_contents($arquivo);
    $dados = json_decode($json, true);
    return is_array($dados) ? $dados : [];
}

function adicionarUsuarioBloqueado($ug_id, $motivo)
{
    $arquivo = __DIR__ . '/../../../db/usuarios_bloqueados.json';

    // L� os usu�rios bloqueados atuais
    $usuarios = lerUsuariosBloqueados();

    // Cria um novo registro de usu�rio bloqueado
    $novoUsuario = [
        'ug_id' => $ug_id,
        'motivo' => utf8_encode($motivo),
        'data_bloqueio' => date('Y-m-d H:i:s'),
    ];

    // Adiciona o novo usu�rio na lista
    $usuarios[] = $novoUsuario;

    // Salva a lista atualizada no arquivo JSON, com bloqueio seguro
    salvarUsuariosBloqueados($arquivo, $usuarios);
}

function removerUsuarioBloqueado($ug_id)
{
    $arquivo = __DIR__ . '/../../../db/usuarios_bloqueados.json';

    // L� os usu�rios bloqueados atuais
    $usuarios = lerUsuariosBloqueados();

    // Filtra os usu�rios removendo o que possui o ID informado
    $usuariosAtualizados = array_filter($usuarios, function ($usuario) use ($ug_id) {
        return !isset($usuario['ug_id']) || $usuario['ug_id'] !== $ug_id;
    });

    // Reindexa o array para n�o deixar 'buracos' (opcional)
    $usuariosAtualizados = array_values($usuariosAtualizados);

    // Salva a lista atualizada no arquivo JSON, com bloqueio seguro
    salvarUsuariosBloqueados($arquivo, $usuariosAtualizados);
}

function salvarUsuariosBloqueados($arquivo, array $usuarios)
{
    // Abre o arquivo para leitura e escrita, cria se n�o existir
    $fp = fopen($arquivo, 'c+');
    if (!$fp) {
        throw new Exception("N�o foi poss�vel abrir o arquivo para escrita: $arquivo");
    }

    // Solicita bloqueio exclusivo para escrita
    if (!flock($fp, LOCK_EX)) {
        fclose($fp);
        throw new Exception("N�o foi poss�vel obter o bloqueio no arquivo: $arquivo");
    }

    // Limpa o conte�do atual do arquivo
    ftruncate($fp, 0);

    // Retorna o ponteiro para o in�cio do arquivo
    rewind($fp);

    // Codifica os dados em JSON formatado
    $json = json_encode($usuarios, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        // Log de erro ou avisar que houve problema na codifica��o
        throw new Exception("Erro ao codificar os dados em JSON: " . json_last_error_msg());
    }

    // Escreve os dados no arquivo
    fwrite($fp, $json);
    fflush($fp);

    // Libera o bloqueio
    flock($fp, LOCK_UN);

    // Fecha o arquivo
    fclose($fp);
}

function obterUsuarioBloqueado($ug_id)
{
    $arquivo = __DIR__ . '/../../../db/usuarios_bloqueados.json';

    if (!file_exists($arquivo)) {
        // Se o arquivo n�o existir, n�o h� usu�rio para retornar
        return null;
    }

    // Abre o arquivo em modo somente leitura
    $fp = fopen($arquivo, 'r');
    if (!$fp) {
        throw new Exception("N�o foi poss�vel abrir o arquivo para leitura: $arquivo");
    }

    // Solicita um bloqueio compartilhado para leitura
    if (!flock($fp, LOCK_SH)) {
        fclose($fp);
        throw new Exception("N�o foi poss�vel obter o bloqueio compartilhado no arquivo: $arquivo");
    }

    // L� o conte�do do arquivo
    $json = stream_get_contents($fp);

    // Libera o bloqueio e fecha o arquivo
    flock($fp, LOCK_UN);
    fclose($fp);

    // Decodifica o conte�do JSON em array
    $usuarios = json_decode($json, true);
    if (!is_array($usuarios)) {
        return null;
    }

    // Percorre o array para encontrar o usu�rio com o ID desejado
    foreach ($usuarios as $usuario) {
        if (isset($usuario['ug_id']) && $usuario['ug_id'] === $ug_id) {
            return $usuario;
        }
    }

    // Caso o usu�rio n�o seja encontrado, retorna null
    return null;
}
?>