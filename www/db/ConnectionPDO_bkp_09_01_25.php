<?php

class LoggingPDOStatement extends PDOStatement
{
    public $queryString;

    protected function __construct()
    {
        // O construtor é protegido para evitar instâncias diretas.
    }

    public function execute($bound_input_params = null)
    {
        // Verifica se estamos na pasta public_html
        $backtrace = debug_backtrace();
        $callerFile = $backtrace[0]['file'];  // Pega o caminho do arquivo que chamou
        $callerDir = dirname($callerFile);   // Obtém o diretório do arquivo que chamou

        if (strpos($callerDir, 'public_html') === false) {
            // Se não está na pasta public_html, não faz log
            return parent::execute($bound_input_params);
        }
        // Verifica se a consulta é do tipo INSERT ou UPDATE
        if (preg_match('/^\s*(INSERT|UPDATE|DELETE)/i', $this->queryString)) {
            // Log da consulta SQL e parâmetros
            $log = date('Y-m-d H:i:s') . " | Query: " . $this->queryString . PHP_EOL ;

            if ($bound_input_params) {
                $log .= " | Params: " . json_encode($bound_input_params) . PHP_EOL;
            }

            $log .= " | Called from: " . $callerFile . PHP_EOL;

            if(isset($_SESSION['dist_usuarioGames_ser'])){
                $usuarioGames = unserialize($_SESSION['dist_usuarioGames_ser']);
                $log .= " | User Id Pdv: " . $usuarioGames->getId() . PHP_EOL;
            }else if(isset($_SESSION['usuarioGames_ser'])){
                $usuarioGames = unserialize($_SESSION['usuarioGames_ser']);
                $log .= " | User Id Gamer: " . $usuarioGames->getId() . PHP_EOL;
            }else{
                $log .= " | No users in the session" . PHP_EOL;
            }

            // Linha de separação para melhorar a legibilidade
            $separator = str_repeat('*', 50);  // Cria uma linha de 50 asteriscos

            // Caminho do arquivo de log
            $logFile = '/www/log/sql_logs/logs_' . date('d_m_y') . '.log';

            // Adiciona o log ao arquivo, com uma linha de separação antes de cada nova consulta
            file_put_contents($logFile, PHP_EOL . $separator . PHP_EOL . $log . PHP_EOL . PHP_EOL, FILE_APPEND);

        }

        // Executa a consulta normalmente
        return parent::execute($bound_input_params);
    }
}

/**
 * Porque usar Prepared Statment?
 * https://websec.wordpress.com/2010/03/19/exploiting-hard-filtered-sql-injections/
 *
 * Class ConnectionPDO
 */
class ConnectionPDO
{

    private $link;
    private $connected = false;

    private $errors = array();

    /**
     * Não é necessário instanciar, basta chamar ConnectionPDO::getConnection();
     * Caso queria acessar o PDO diretamente: ConnectionPDO::getConnection()->getLink();
     */
    private function __construct()
    {
    }

    /**
     * Conecta a base de dados
     * Os erros são objetos Exceptions
     *
     * @return bool|PDO
     */
    public function connect()
    {
        try {
            $this->link = new PDO(
                'pgsql:dbname=' . DB_BANCO . ';host=' . DB_HOST . ';port=' . DB_PORT,
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_PERSISTENT => false,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_STATEMENT_CLASS => [LoggingPDOStatement::class],
                ]
            );
            $this->connected = true;
            return $this->link;
        } catch (Exception $e) {
            $this->connected = false;
            $this->errors[] = $e;
            return false;
        }
    }

    /**
     * Retorna a lista de erros onde cada posição é uma exception
     * Ou seja, você pode recuperar toda a informação ($error[0]->getMessage(), etc)
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Retorna a conexão com o banco
     *
     * @return ConnectionPDO
     */
    public static function getConnection()
    {
        $_ = new self;
        $_->connect();
        return $_;
    }

    /**
     * Retorna o link com PDO
     *
     * @return PDO
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Verifica se esta conectado ou não ao banco de dados
     *
     * @return bool
     */
    public function isConnected()
    {
        return $this->connected;
    }

    /**
     * Ao imprimir o objeto, ele mostra se está ou não conectado
     * (para debug)
     *
     * @return string
     */
    public function __toString()
    {
        return 'Connection is ' . ($this->isConnected() ? 'ON' : 'OFF');
    }

    /**
     * Não é possível clonar este objeto
     * (Singleton-like pattern)
     *
     * @return bool
     */
    public function __clone()
    {
        return false;
    }

}