<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
echo ini_get('open_basedir');
// Verifica se o par�metro 'numOrder' foi passado na URL
if (isset($_GET['numOrder'])) {
    // Obt�m o valor do par�metro 'numOrder'
    $numOrder = $_GET['numOrder'];
    $id_usuario_prev = $numOrder; // Usando o valor de numOrder como ID do usu�rio

    // Define o caminho do arquivo de log com base no ID do usu�rio
    $log_directory = __DIR__ . "/log/";  
    $log_filename = "finaliza_venda_user_" . $id_usuario_prev . ".txt";
    $log_filepath = $log_directory . $log_filename;

    // Verifica se o diret�rio de log existe, caso contr�rio cria
    if (!is_dir($log_directory)) {
        if (!mkdir($log_directory, 0777, true)) {
            die('Erro ao criar o diret�rio de log: ' . $log_directory);
            echo"felipe";
            exit;
        }
    }

    // Tenta abrir o arquivo de log
    $ff = fopen($log_filepath, "a+");
    if (!$ff) {
        die('Erro ao abrir o arquivo de log: ' . $log_filepath);
        echo"felipe2";
        exit;
    }

    // Escreve no log
    fwrite($ff, "Log de Finaliza��o de Venda - Data: " . date("Y-m-d H:i:s") . "\r\n");

    // Fecha o arquivo de log
    fclose($ff);
    
    echo "Log criado com sucesso: " . $log_filepath;
} else {
    echo "Erro: Par�metro 'numOrder' n�o foi passado na URL.";
}
?>
