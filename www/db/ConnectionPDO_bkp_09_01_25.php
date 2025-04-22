<?php

class LoggingPDOStatement extends PDOStatement
{
    public $queryString;

    protected function __construct()
    {
        // O construtor � protegido para evitar inst�ncias diretas.
    }

    public function execute($bound_input_params = null)
    {
        // Verifica se estamos na pasta public_html
        $backtrace = debug_backtrace();
        $callerFile = $backtrace[0]['file'];  // Pega o caminho do arquivo que chamou
        $callerDir = dirname($callerFile);   // Obt�m o diret�rio do arquivo que chamou

        if (strpos($callerDir, 'public_html') === false) {
            // Se n�o est� na pasta public_html, n�o faz log
            return parent::execute($bound_input_params);
        }
        // Verifica se a consulta � do tipo INSERT ou UPDATE
        if (preg_match('/^\s*(INSERT|UPDATE|DELETE)/i', $this->queryString)) {
            // Log da consulta SQL e par�metros
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

            // Linha de separa��o para melhorar a legibilidade
            $separator = str_repeat('*', 50);  // Cria uma linha de 50 asteriscos

            // Caminho do arquivo de log
            $logFile = '/www/log/sql_logs/logs_' . date('d_m_y') . '.log';

            // Adiciona o log ao arquivo, com uma linha de separa��o antes de cada nova consulta
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
     * N�o � necess�rio instanciar, basta chamar ConnectionPDO::getConnection();
     * Caso queria acessar o PDO diretamente: ConnectionPDO::getConnection()->getLink();
     */
    private function __construct()
    {
    }

    /**
     * Conecta a base de dados
     * Os erros s�o objetos Exceptions
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
     * Retorna a lista de erros onde cada posi��o � uma exception
     * Ou seja, voc� pode recuperar toda a informa��o ($error[0]->getMessage(), etc)
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Retorna a conex�o com o banco
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
     * Verifica se esta conectado ou n�o ao banco de dados
     *
     * @return bool
     */
    public function isConnected()
    {
        return $this->connected;
    }

    /**
     * Ao imprimir o objeto, ele mostra se est� ou n�o conectado
     * (para debug)
     *
     * @return string
     */
    public function __toString()
    {
        return 'Connection is ' . ($this->isConnected() ? 'ON' : 'OFF');
    }

    /**
     * N�o � poss�vel clonar este objeto
     * (Singleton-like pattern)
     *
     * @return bool
     */
    public function __clone()
    {
        return false;
    }

}