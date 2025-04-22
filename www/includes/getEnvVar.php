<?php
function getEnvVariable($varName, $ignoreComments = false) {
    // Verifica se a variável de ambiente já está definida
    $value = getenv($varName);

    if ($value === false) {
        // Carrega o arquivo .env
        if (file_exists('/www/.env')) {
            $lines = file('/www/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            
            foreach ($lines as $line) {
                // Ignora comentários
                if (strpos(trim($line), '#') === 0 && $ignoreComments == false) {
                    continue;
                }

                // Divide a linha em nome e valor
                list($name, $val) = explode('=', $line, 2);
                
                // Remove espaços e aspas
                $name = trim($name);
                $val = trim($val, " \t\n\r\0\x0B\"");

                // Se o nome da variável do .env for o mesmo, define ela
                if ($name === $varName) {
                    // Definindo a variável de ambiente no processo atual
                    putenv("$name=$val");
                    return $val;
                }
            }
        }

        // Se não encontrar no .env, retorna null ou algum valor padrão
        return null;
    }

    // Retorna o valor da variável já existente
    return $value;
}
?>