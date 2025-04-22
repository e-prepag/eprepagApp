<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
   
    // Consulta para buscar alterações recentes de email
    $query = "SELECT e.ug_id, g.ug_login ,e.email_anterior, e.email_novo, e.data_update 
              FROM log_alteracao_email e
              left join dist_usuarios_games g on g.ug_id = e.ug_id  WHERE e.data_update >= NOW() - INTERVAL '6 minutes'";

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $alteracoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    

    if ($alteracoes) {
        // Configurações do e-mail
        $to = 'felipe.farias@easygroupit.com, jose.carlos@easygroupit.com, rc@e-prepag.com.br, glaucia@e-prepag.com.br';
        $cc     = "";
        $subject = 'Notificação de Alteração de E-mail no PDV';
        $bcc = "";
        // Monta o corpo do e-mail com as alterações
        $message = "<h1>Notificação de Alteração de E-mail no PDV</h1>";
        $message .= "<p>Os seguintes PDVs tiveram alteração no e-mail:</p>";
        $message .= "<ul>";
        foreach ($alteracoes as $alteracao) {
            $message .= "<li><strong>PDV ID:</strong> " . $alteracao['ug_id'] . "<br>";
            $message .= "<strong>Loginr:</strong> " . $alteracao['ug_login'] . "<br>";
            $message .= "<strong>Email Anterior:</strong> " . $alteracao['email_anterior'] . "<br>";
            $message .= "<strong>Email Novo:</strong> " . $alteracao['email_novo'] . "<br>";
            $message .= "<strong>Data da Alteração:</strong> " . $alteracao['data_update'] . "</li><br>";
        }
        $message .= "</ul>";

        // Envia o e-mail
        if (function_exists('enviaEmail3')) {
            var_dump(enviaEmail3($to, $cc, $bcc, $subject, $message, ""));
        } else {
            echo "Falha ao enviar o e-mail.";
        }
    } else {
        echo "Nenhuma alteração recente encontrada.";
    }
} catch (PDOException $e) {
    echo "Erro na conexão com o banco de dados: " . $e->getMessage();
}





try {
    // Consulta para buscar os registros recentes
    $query = "SELECT s.ug_id, g.ug_login , s.ug_perfil_saldo_anterior, s.ug_perfil_saldo_atual, s.data_bloqueio 
              FROM log_alteracao_saldo s
              left join dist_usuarios_games g on g.ug_id = s.ug_id  
              WHERE s.data_bloqueio >= NOW() - INTERVAL '6 minutes'";

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $suspeitos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($suspeitos) {
        // Configurações do e-mail
        $to = 'felipe.farias@easygroupit.com, jose.carlos@easygroupit.com, rc@e-prepag.com.br, glaucia@e-prepag.com.br';
        $subject = 'Notificação de Bloqueio por Saldo Suspeito';
        $message = "<h1>Notificação de Bloqueio por Saldo Suspeito</h1>";
        $message .= "<p>Os seguintes usuários foram bloqueados por saldo suspeito:</p>";
        $message .= "<ul>";
        
        foreach ($suspeitos as $suspeito) {
            $message .= "<li><strong>UG ID:</strong> " . $suspeito['ug_id'] . "<br>";
            $message .= "<strong>PDV:</strong> " . $suspeito['ug_login'] . "<br>";
            
            $message .= "<strong>Saldo atual:</strong> " . $suspeito['ug_perfil_saldo_anterior'] . "<br>";
            $message .= "<strong>Tentativa de saldo:</strong> " . $suspeito['ug_perfil_saldo_atual'] . "<br>";
            $message .= "<strong>Data do Bloqueio:</strong> " . $suspeito['data_bloqueio'] . "</li><br>";
        }
        $message .= "</ul>";

        // Envia o e-mail
        if (function_exists('enviaEmail3')) {
            var_dump(enviaEmail3($to, "", "", $subject, $message, ""));
        } else {
            echo "Função de envio de e-mail não encontrada.";
        }
    } else {
        echo "Nenhum bloqueio suspeito recente encontrado.";
    }
} catch (PDOException $e) {
    echo "Erro na conexão com o banco de dados: " . $e->getMessage();
}
?>
