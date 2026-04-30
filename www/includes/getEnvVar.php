<?php
/**
 * @param string $varName
 * @param bool $ignoreComments
 * @return string|null
 */
function getEnvVariable(string $varName, bool $ignoreComments = false): ?string {
    // Verifica se a variável de ambiente já está definida
    $value = getenv($varName);

    if ($value === false) {
        // Carrega o arquivo .env
        $envPath = '/www/.env';
        if (file_exists($envPath)) {
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            if (is_array($lines)) {
                foreach ($lines as $line) {
                    // Ignora comentários
                    if (strpos(trim($line), '#') === 0 && $ignoreComments === false) {
                        continue;
                    }

                    // Divide a linha em nome e valor
                    $parts = explode('=', $line, 2);
                    if (count($parts) !== 2) {
                        continue;
                    }

                    list($name, $val) = $parts;

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
        }

        // Se não encontrar no .env, retorna null ou algum valor padrão
        return null;
    }

    // Retorna o valor da variável já existente
    return (string)$value;
}
?>