<?php require_once __DIR__ . '/../includes/constantes_url.php'; ?>
<?php
require_once __DIR__ . "/connect.php";

class LoggingPDOStatement extends PDOStatement
{
    protected function __construct()
    {
        // O construtor é protegido para evitar instâncias diretas.
    }

    public function execute(?array $bound_input_params = null): bool
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $callerFile = $backtrace[1]['file'] ?? __FILE__;
        $callerDir = dirname($callerFile);

        if (
            strpos($callerDir, 'public_html') === false
            && (!defined('EPREPAG_URL') || strpos($_SERVER['HTTP_HOST'] ?? '', EPREPAG_URL) === false)
            && stripos($this->queryString, 'usuarios') === false
        ) {
            return parent::execute($bound_input_params);
        }

        if (preg_match('/^\s*(INSERT|UPDATE|DELETE)/i', $this->queryString)) {
            $log = date('Y-m-d H:i:s') . " | Query: " . $this->queryString . PHP_EOL;

            if ($bound_input_params !== null) {
                $log .= " | Params: " . json_encode($bound_input_params) . PHP_EOL;
            }

            $log .= " | Called from: " . $callerFile . PHP_EOL;

            if (isset($_SESSION['dist_usuarioGames_ser'])) {
                $usuarioGames = unserialize($_SESSION['dist_usuarioGames_ser']);
                $log .= " | User Id Pdv: " . $usuarioGames->getId() . PHP_EOL;
            } elseif (isset($_SESSION['usuarioGames_ser'])) {
                $usuarioGames = unserialize($_SESSION['usuarioGames_ser']);
                $log .= " | User Id Gamer: " . $usuarioGames->getId() . PHP_EOL;
            } else {
                $log .= " | No users in the session" . PHP_EOL;
            }

            if (!empty($_SERVER['REMOTE_ADDR'])) {
                $log .= " | IP: " . $_SERVER['REMOTE_ADDR'] . PHP_EOL;
            }

            $separator = str_repeat('*', 50);
            $logFile = '/www/arquivos_gerados/logs/sql_logs/logs_' . date('d_m_y') . '.log';

            file_put_contents(
                $logFile,
                PHP_EOL . $separator . PHP_EOL . $log . PHP_EOL . PHP_EOL,
                FILE_APPEND
            );
        }

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
            $dbHost = defined("DB_HOST") ? DB_HOST : (getenv("DB_HOST_EPREPAG") ?: "null");
            $dbPort = defined("DB_PORT") ? DB_PORT : (getenv("DB_PORT_EPREPAG") ?: "null");
            $dbBanco = defined("DB_BANCO") ? DB_BANCO : (getenv("DB_BANCO_EPREPAG") ?: "null");
            $dbUser = defined("DB_USER") ? DB_USER : (getenv("DB_USER_EPREPAG") ?: "null");
            $dbPass = defined("DB_PASS") ? DB_PASS : (getenv("DB_PASS_EPREPAG") ?: "null");

            $this->link = new PDO(
                "pgsql:dbname=" . $dbBanco . ";host=" . $dbHost . ";port=" . $dbPort,
                $dbUser,
                $dbPass,
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
    }

}
