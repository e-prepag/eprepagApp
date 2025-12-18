<?php

// 1. Verifica se o primeiro argumento (o caminho) foi realmente fornecido
if (isset($argv[1])) {

    // 2. Se sim, armazena o argumento (o caminho)
    $caminho_do_arquivo = $argv[1];

    $state_file = "/root/logs_arquivos/verificador.log";


    if (!file_exists($state_file)) {
        touch($state_file);
    }

    $notificados = file($state_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if (in_array($caminho_do_arquivo, $notificados)) {
        echo "Arquivo já notificado. Ignorando: $caminho_do_arquivo" . PHP_EOL;
        exit(0); // Termina com sucesso (sem ação)
    }

    require_once "/www/includes/constantes.php";
    require_once "/www/includes/gamer/functions.php";
    require_once "/www/db/connect.php";
    require_once "/www/db/ConnectionPDO.php";
    require_once "/www/class/classEmailAutomatico.php";
    require_once "/www/class/phpmailer/class.phpmailer.php";
    require_once "/www/includes/configIP.php";
    require_once "/www/class/phpmailer/class.smtp.php";
    require_once "/www/class/pdv/classChaveMestra.php";
    $pdo = ConnectionPDO::getConnection()->getLink();
    try {
        // Conexão com o banco de dados usando PDO

        if ($caminho_do_arquivo) {
            // Configurações do e-mail
            $to = 'fclebio@gmail.com, wesley.pereira@easygroupit.com, jose.carlos@easygroupit.com, rc@e-prepag.com.br, glaucia@e-prepag.com.br';
            $cc     = "";
            $subject = 'Notificação de Alteração de arquivos';
            $bcc = "";
            // Monta o corpo do e-mail com as alterações
            $message = "<h1>Notificação de Alteração de arquivos</h1>";
            $message .= "<p>O seguinte Arquivo teve alteração:</p>";
            $message .= "<ul>";
            $message .= "<li><strong>Caminho arquivo:</strong> $caminho_do_arquivo</li><br>";
            $message .= "</ul>";

            // Envia o e-mail
            if (function_exists('enviaEmail3')) {
                var_dump(enviaEmail3($to, $cc, $bcc, $subject, $message, ""));

                // Sanitiza tentativas de PHP injection no conteúdo
                $conteudo = preg_replace('/<\?(php)?/i', '[BLOCKED]', $caminho_do_arquivo) . PHP_EOL;

                // Verifica se a extensão do arquivo é .log
                if (pathinfo($state_file, PATHINFO_EXTENSION) !== 'log') {
                    throw new Exception("Extensão inválida para arquivo de estado.");
                }

                // Impede path traversal
                if (strpos($state_file, '..') !== false) {
                    throw new Exception("Caminho inválido para state_file.");
                }

                if (file_put_contents($state_file, $conteudo, FILE_APPEND | LOCK_EX) === false) {
                    throw new Exception("Falha ao escrever no arquivo de estado: $state_file");
                }
            } else {
                echo "Falha ao enviar o e-mail.";
            }
        } else {
            echo "Nenhuma alteração recente encontrada.";
        }
    } catch (PDOException $e) {
        echo "Erro na conexão com o banco de dados: " . $e->getMessage();
    }
} else {
    // Caso nenhum argumento seja passado, informa o erro
    echo "Erro: Nenhum argumento de caminho foi fornecido." . PHP_EOL;
}
