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
    // Conex�o com o banco de dados usando PDO
   
    // Consulta para buscar altera��es recentes de email
    $query = "SELECT ug_id, email_anterior, email_novo, data_update 
              FROM log_alteracao_email 
              WHERE data_update >= NOW() - INTERVAL '40 minutes'";

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $alteracoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    

    if ($alteracoes) {
        // Configura��es do e-mail
        $to = 'felipe.farias@easygroupit.com';
        $cc = '';
        $subject = 'Notifica��o de Altera��o de E-mail no PDV';
        $bcc = "";
        // Monta o corpo do e-mail com as altera��es
        $message = "<h1>Notifica��o de Altera��o de E-mail no PDV</h1>";
        $message .= "<p>Os seguintes PDVs tiveram altera��o no e-mail:</p>";
        $message .= "<ul>";
        foreach ($alteracoes as $alteracao) {
            $message .= "<li><strong>PDV ID:</strong> " . $alteracao['ug_id'] . "<br>";
            $message .= "<strong>Email Anterior:</strong> " . $alteracao['email_anterior'] . "<br>";
            $message .= "<strong>Email Novo:</strong> " . $alteracao['email_novo'] . "<br>";
            $message .= "<strong>Data da Altera��o:</strong> " . $alteracao['data_update'] . "</li><br>";
        }
        $message .= "</ul>";

        // Envia o e-mail
        if (function_exists('enviaEmail3')) {
            var_dump(enviaEmail3($to, $cc, $bcc, $subject, $message, ""));
        } else {
            echo "Falha ao enviar o e-mail.";
        }
    } else {
        echo "Nenhuma altera��o recente encontrada.";
    }
} catch (PDOException $e) {
    echo "Erro na conex�o com o banco de dados: " . $e->getMessage();
}
?>
