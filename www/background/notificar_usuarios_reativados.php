<?php

require_once "/www/includes/constantes.php";
require_once "/www/includes/gamer/functions.php";
require_once "/www/db/connect.php";
require_once "/www/db/ConnectionPDO.php";
require_once "/www/class/classEmailAutomatico.php";
require_once "/www/class/phpmailer/class.phpmailer.php";
require_once "/www/includes/configIP.php";
require_once "/www/class/phpmailer/class.smtp.php";



$logFile = '/www/arquivos_gerados/logs/notificar_usuarios_reativados.log';

// Teste
$to = 'jose.carlos@easygroupit.com';
$subject = 'TESTE CRON';

$message = "<h1>TESTE CRON</h1>";
$message .= "<p>TESTE CRON</p>";

// Dispara o e-mail
if (function_exists('enviaEmail3')) {
    $emailResult = enviaEmail3($to, "", "", $subject, $message, "");
    if ($emailResult) {
        logMessage("TESTE CRON");
    } else {
        logMessage("TESTE CRON");
    }
}
// Fim do Teste

function logMessage($message)
{
    global $logFile;
    $date = date('Y-m-d H:i:s');
    $formattedMessage = "[$date] $message" . PHP_EOL;
    file_put_contents($logFile, $formattedMessage, FILE_APPEND);
    echo $formattedMessage;
}

$pdo = ConnectionPDO::getConnection()->getLink();
$intervalo = '120 seconds';

logMessage("Buscando usuários reativados (status = 1) no último(a) $intervalo...");
$usuariosReativados = [];

// Query para buscar os PDVs (dist_usuarios_games) reativados
// Fazemos um JOIN da tabela _obs com a principal para capturar email e login
$sqlDist = "SELECT u.ug_id, u.ug_login, u.ug_email, o.ugo_user_insert, o.ugo_data AS data_reativacao, 'PDV' AS tipo_usuario
                FROM dist_usuarios_games u
                INNER JOIN dist_usuarios_games_obs o ON u.ug_id = o.ug_id
                WHERE o.ugo_status_user = 1 
                  AND o.ugo_data >= NOW() - INTERVAL '$intervalo'
                ORDER BY o.ugo_data DESC";

// Query para buscar os Gamers (usuarios_games) reativados
$sqlGamer = "SELECT u.ug_id, u.ug_login, u.ug_email, o.ugo_user_insert, o.ugo_data AS data_reativacao, 'Gamer' AS tipo_usuario
                 FROM usuarios_games u
                 INNER JOIN usuarios_games_obs o ON u.ug_id = o.ug_id
                 WHERE o.ugo_status_user = 1 
                   AND o.ugo_data >= NOW() - INTERVAL '$intervalo'
                 ORDER BY o.ugo_data DESC";

try {
    // Executa busca de PDVs
    $stmtDist = $pdo->query($sqlDist);
    $pdvs = $stmtDist->fetchAll(PDO::FETCH_ASSOC);
    $usuariosReativados = array_merge($usuariosReativados, $pdvs);

    // Executa busca de Gamers
    $stmtGamer = $pdo->query($sqlGamer);
    $gamers = $stmtGamer->fetchAll(PDO::FETCH_ASSOC);
    $usuariosReativados = array_merge($usuariosReativados, $gamers);

    $totalReativados = count($usuariosReativados);
    logMessage("Encontrados $totalReativados usuários reativados. Montando e-mail...");

    if ($totalReativados > 0) {
        // Reutilizando as configurações de e-mail do seu script original
        $to = 'wesley.pereira@easygroupit.com, jose.carlos@easygroupit.com, rc@e-prepag.com.br, glaucia@e-prepag.com.br';
        $subject = 'Notificação de Reativação de Usuários';

        $message = "<h1>Notificação de Reativação de Usuários</h1>";
        $message .= "<p>Os seguintes usuários foram reativados (status alterado para 1 - Ativo) recentemente:</p>";
        $message .= "<ul>";

        foreach ($usuariosReativados as $user) {
            // Formata a data para o padrão brasileiro visualmente
            $dataFormatada = date('d/m/Y H:i:s', strtotime($user['data_reativacao']));

            $message .= "<li><strong>UG ID:</strong> " . $user['ug_id'] . "<br>";
            $message .= "<strong>Tipo:</strong> <span style='color: blue;'><b>" . $user['tipo_usuario'] . "</b></span><br>";
            $message .= "<strong>Login:</strong> " . $user['ug_login'] . "<br>";
            $message .= "<strong>Email:</strong> " . $user['ug_email'] . "<br>";
            $message .= "<strong>Responsável:</strong> " . $user['ugo_user_insert'] . "<br>";
            $message .= "<strong>Data da Reativação:</strong> " . $dataFormatada . "</li><br>";
        }

        $message .= "</ul>";

        // Dispara o e-mail
        if (function_exists('enviaEmail3')) {
            $emailResult = enviaEmail3($to, "", "", $subject, $message, "");
            if ($emailResult) {
                logMessage("E-mail de reativações enviado com sucesso para a equipe de compliance.");
            } else {
                logMessage("Falha ao enviar o e-mail de reativações: enviaEmail3 retornou false.");
            }
        } else {
            logMessage("Falha ao enviar e-mail: Função enviaEmail3 não encontrada.");
        }
    } else {
        logMessage("Nenhum usuário foi reativado no período estipulado.");
    }
} catch (Exception $e) {
    logMessage("Erro ao consultar usuários reativados: " . $e->getMessage());
}
