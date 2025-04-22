<?php
/**
 * Porque usar Prepared Statment?
 * https://websec.wordpress.com/2010/03/19/exploiting-hard-filtered-sql-injections/
 *
 * Class ConnectionPDO
 */
class ConnectionPDO {

    private $link;
    private $connected = false;

    private $errors = array();

    /**
     * Não é necessário instanciar, basta chamar ConnectionPDO::getConnection();
     * Caso queria acessar o PDO diretamente: ConnectionPDO::getConnection()->getLink();
     */
    private function __construct()
    {}

    /**
     * Conecta a base de dados
     * Os erros são objetos Exceptions
     *
     * @return bool|PDO
     */
    public function connect()
    {
        try {
            $this->link = new PDO('pgsql:dbname='.DB_BANCO.';host='.DB_HOST.';port='.DB_PORT, DB_USER, DB_PASS, array(PDO::ATTR_PERSISTENT => false));
            $this->link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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