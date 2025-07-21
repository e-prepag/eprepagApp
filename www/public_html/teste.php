<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);  // Exibe todos os tipos de erros
require "/www/db/connect.php";
require "/www/db/ConnectionPDO.php";

$pdo = ConnectionPDO::getConnection()->getLink();
try {
    $sql = "
        ALTER TABLE usuarios_games
        ADD COLUMN ug_chave_autenticador TEXT NULL,
        ADD COLUMN ug_acesso_sem_aut DATE NULL DEFAULT CURRENT_DATE;

        CREATE TABLE usuarios_games_dispositivos (
            id serial4 NOT NULL,
            user_id int4 NOT NULL,
            device_token text NOT NULL,
            expires_at timestamp DEFAULT (now() + '30 days'::interval) NULL,
            created_at timestamp DEFAULT now() NULL,
            CONSTRAINT usuarios_games_dispositivos_pkey PRIMARY KEY (id)
        );
    ";

    $pdo->exec($sql);
    echo "Alterações aplicadas com sucesso.";
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}

?>
