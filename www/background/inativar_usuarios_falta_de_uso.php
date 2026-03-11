<?php
require_once "/www/includes/constantes.php";
require_once "/www/includes/gamer/functions.php";
require_once "/www/db/connect.php";
require_once "/www/db/ConnectionPDO.php";
require_once "/www/class/classEmailAutomatico.php";
require_once "/www/class/phpmailer/class.phpmailer.php";
require_once "/www/includes/configIP.php";
require_once "/www/class/phpmailer/class.smtp.php";

$logFile = __DIR__ . '../arquivos_gerados/logs/inativar_usuarios_falta_de_uso.log';

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

function inativarUsuarios($pdo, $tabelaUsuario, $tabelaObs)
{
    logMessage("Iniciando inativação para a tabela: $tabelaUsuario");
    $usuariosInativados = [];

    try {
        $pdo->beginTransaction();

        // Busca usuários sem acesso há mais de 1 ano e que ainda não estão inativados
        $sqlBusca = "SELECT ug_id, ug_login, ug_email, ug_data_ultimo_acesso FROM {$tabelaUsuario} 
                     WHERE ug_data_ultimo_acesso <= NOW() - INTERVAL '1 year' 
                     AND ug_ativo = 1
                     LIMIT 500";

        $stmtBusca = $pdo->prepare($sqlBusca);
        $stmtBusca->execute();
        $usuarios = $stmtBusca->fetchAll(PDO::FETCH_ASSOC);

        $total = count($usuarios);
        logMessage("Encontrados $total usuários inativos na tabela $tabelaUsuario.");

        if ($total > 0) {
            // Prepara a query de update
            $sqlUpdate = "UPDATE {$tabelaUsuario} 
                          SET ug_ativo = 6 
                          WHERE ug_id = :ug_id";
            $stmtUpdate = $pdo->prepare($sqlUpdate);

            // Prepara a query de histórico/observação
            $sqlInsertObs = "INSERT INTO {$tabelaObs} (ug_id, ug_obs, ugo_user_insert, ugo_data) 
                             VALUES (:ug_id, :ug_obs, :ugo_user_insert, NOW())";
            $stmtInsert = $pdo->prepare($sqlInsertObs);

            $countInativados = 0;
            foreach ($usuarios as $usuario) {
                // Atualiza código do usuário
                $stmtUpdate->execute([':ug_id' => $usuario['ug_id']]);

                // Adiciona log na tabela de obs
                $stmtInsert->execute([
                    ':ug_id' => $usuario['ug_id'],
                    ':ug_obs' => 'mensagem usuário inativado por falta de uso',
                    ':ugo_user_insert' => 'Sistema'
                ]);
                $countInativados++;
                $usuariosInativados[] = $usuario;
            }

            logMessage("Sucesso: $countInativados usuários inativados com sucesso.");
        }

        $pdo->commit();
        logMessage("Processo finalizado com sucesso para $tabelaUsuario.");
    } catch (Exception $e) {
        $pdo->rollBack();
        logMessage("Erro ao processar a tabela $tabelaUsuario: " . $e->getMessage());
    }
    return $usuariosInativados;
}

$todosInativados = [];

// Executar para dist_usuarios_games
$inativadosDist = inativarUsuarios($pdo, 'dist_usuarios_games', 'dist_usuarios_games_obs');
$todosInativados = array_merge($todosInativados, $inativadosDist);

// Executar para usuarios_games
$inativadosGames = inativarUsuarios($pdo, 'usuarios_games', 'usuarios_games_obs');
$todosInativados = array_merge($todosInativados, $inativadosGames);

if (count($todosInativados) > 0) {
    logMessage("Montando e-mail de notificação para compliance. Total de inativados: " . count($todosInativados));

    $to = 'wesley.pereira@easygroupit.com, jose.carlos@easygroupit.com, rc@e-prepag.com.br, glaucia@e-prepag.com.br';
    $subject = 'Notificação de Inativação de Usuários por Falta de Uso';

    $message = "<h1>Notificação de Inativação de Usuários</h1>";
    $message .= "<p>Os seguintes usuários foram inativados por estarem há mais de 1 ano sem acessar o sistema:</p>";
    $message .= "<ul>";

    foreach ($todosInativados as $user) {
        $message .= "<li><strong>UG ID:</strong> " . $user['ug_id'] . "<br>";
        $message .= "<strong>Login:</strong> " . $user['ug_login'] . "<br>";
        $message .= "<strong>Email:</strong> " . $user['ug_email'] . "<br>";
        $message .= "<strong>Último Acesso:</strong> " . $user['ug_data_ultimo_acesso'] . "</li><br>";
    }

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
