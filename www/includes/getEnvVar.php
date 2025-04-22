<?php
function getEnvVariable($varName, $ignoreComments = false) {
    // Verifica se a vari�vel de ambiente j� est� definida
    $value = getenv($varName);

    if ($value === false) {
        // Carrega o arquivo .env
        if (file_exists('/www/.env')) {
            $lines = file('/www/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            
            foreach ($lines as $line) {
                // Ignora coment�rios
                if (strpos(trim($line), '#') === 0 && $ignoreComments == false) {
                    continue;
                }

                // Divide a linha em nome e valor
                list($name, $val) = explode('=', $line, 2);
                
                // Remove espa�os e aspas
                $name = trim($name);
                $val = trim($val, " \t\n\r\0\x0B\"");

                // Se o nome da vari�vel do .env for o mesmo, define ela
                if ($name === $varName) {
                    // Definindo a vari�vel de ambiente no processo atual
                    putenv("$name=$val");
                    return $val;
                }
            }
        }

        // Se n�o encontrar no .env, retorna null ou algum valor padr�o
        return null;
    }

    // Retorna o valor da vari�vel j� existente
    return $value;
}
?>