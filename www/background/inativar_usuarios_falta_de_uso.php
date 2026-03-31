<?php
require_once "/www/includes/constantes.php";
require_once "/www/includes/gamer/functions.php";
require_once "/www/db/connect.php";
require_once "/www/db/ConnectionPDO.php";
require_once "/www/class/classEmailAutomatico.php";
require_once "/www/class/phpmailer/class.phpmailer.php";
require_once "/www/includes/configIP.php";
require_once "/www/class/phpmailer/class.smtp.php";

$logFile = __DIR__ . '/../arquivos_gerados/logs/inativar_usuarios_falta_de_uso.log';

function logMessage($message)
{
    global $logFile;
    $date = date('Y-m-d H:i:s');
    $formattedMessage = "[$date] $message" . PHP_EOL;
    file_put_contents($logFile, $formattedMessage, FILE_APPEND);
    echo $formattedMessage;
}

logMessage("--- INÍCIO DO SCRIPT DE INATIVAÇÃO ---");

try {
    $pdo = ConnectionPDO::getConnection()->getLink();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    logMessage("Erro ao conectar ao banco de dados: " . $e->getMessage());
    exit(1);
}

function inativarUsuarios($pdo, $tabelaUsuario)
{
    logMessage("Iniciando inativação para a tabela: $tabelaUsuario");
    $countInativados = 0;

    try {
        // Update em bulk sem limite
        $sqlUpdate = "UPDATE {$tabelaUsuario} 
                      SET ug_ativo = 6, ug_data_encerramento_conta = NOW()
                      WHERE ug_data_ultimo_acesso <= NOW() - INTERVAL '1 year' 
                      AND ug_ativo = 1";
        
        $stmtUpdate = $pdo->prepare($sqlUpdate);
        $stmtUpdate->execute();
        
        $countInativados = $stmtUpdate->rowCount();
        
        logMessage("Sucesso: $countInativados usuários inativados na tabela $tabelaUsuario.");
    } catch (Exception $e) {
        logMessage("Erro ao processar a tabela $tabelaUsuario: " . $e->getMessage());
    }
    
    return $countInativados;
}

// Executar para dist_usuarios_games
$inativadosDist = inativarUsuarios($pdo, 'dist_usuarios_games');

// Executar para usuarios_games
$inativadosGames = inativarUsuarios($pdo, 'usuarios_games');

$totalGeral = $inativadosDist + $inativadosGames;

if ($totalGeral > 0) {
    logMessage("Montando e-mail de notificação para compliance. Total de inativados: $totalGeral");

    $to = 'wesley.pereira@easygroupit.com, jose.carlos@easygroupit.com, rc@e-prepag.com.br, glaucia@e-prepag.com.br';
    $subject = 'Notificação de Inativação de Usuários por Falta de Uso';

    $message = "<h1>Notificação de Inativação de Usuários</h1>";
    $message .= "<p>Foram inativados <strong>$totalGeral</strong> usuários por estarem há mais de 1 ano sem acessar o sistema.</p>";
    $message .= "<ul>";
    $message .= "<li><strong>PDVs:</strong> $inativadosDist usuários inativados</li>";
    $message .= "<li><strong>Gamers:</strong> $inativadosGames usuários inativados</li>";
    $message .= "</ul>";

    if (function_exists('enviaEmail3')) {
        $emailResult = enviaEmail3($to, "", "", $subject, $message, "");
        if ($emailResult) {
            logMessage("E-mail enviado com sucesso para a equipe de compliance.");
        } else {
            logMessage("Falha ao enviar o e-mail: enviaEmail3 retornou false.");
        }
    } else {
        logMessage("Falha ao enviar e-mail: Função enviaEmail3 não encontrada.");
    }
} else {
    logMessage("Nenhum usuário inativado. E-mail não será enviado.");
}

logMessage("--- FIM DO SCRIPT DE INATIVAÇÃO ---");
