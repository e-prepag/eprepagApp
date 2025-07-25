<?php

class MigrationRunner {
    private $pdo;

    /**
     * MigrationRunner constructor.
     * @param PDO $pdo Inst�ncia de conex�o com o banco de dados
     * Inicializa a tabela migrations_db se n�o existir
     */
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS migrations_db (
            nome TEXT PRIMARY KEY,
            executado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
    }

    /**
     * Executa todas as migrations na pasta especificada
     * @param string $dir Diret�rio onde est�o as migrations
     * @return void
     * Percorre todos os arquivos PHP no diret�rio e executa as migrations
     */
    public function run($dir) {
        $executadas = $this->getExecutadas();

        foreach (glob("$dir/*.php") as $arquivo) {
            $nome = basename($arquivo);

            if (in_array($nome, $executadas)) {
                echo "J� executada: $nome\n";
                continue;
            }

            echo "Executando: $nome\n";
            $func = require $arquivo;
            $func($this->pdo);

            $this->registrar($nome);
        }
    }

    /**
        * Summary of getExecutadas
        * @return array
        * Retorna uma lista de migrations j� executadas
        * N�o alterar a tabela migrations_db diretamente, pode causar inconsist�ncias
     */
    private function getExecutadas() {
        $res = $this->pdo->query("SELECT nome FROM migrations_db");
        return $res ? $res->fetchAll(PDO::FETCH_COLUMN) : [];
    }

    /**
        * Registra uma migration como executada
        * @param string $nome Nome da migration
        * @return void
        * Registra a migration no banco de dados para evitar execu��o duplicada
        */
    private function registrar($nome) {
        $stmt = $this->pdo->prepare("INSERT INTO migrations_db (nome) VALUES (?)");
        $stmt->execute([$nome]);
    }
}
