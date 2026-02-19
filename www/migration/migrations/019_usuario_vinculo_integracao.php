<?php

return function (PDO $pdo) {
    $pdo->exec("CREATE TABLE usuarios_games_vinculo (
                    id SERIAL NOT NULL,
                    ug_id BIGINT NOT NULL,
                    email TEXT NOT NULL,
                    -- Definição da Chave Primária
                    CONSTRAINT pk_usuarios_games_vinculo PRIMARY KEY (id),
                    -- Definição da Unique Constraint para o email
                    CONSTRAINT unq_usuario_email UNIQUE (email),
                    -- Definição da Chave Estrangeira
                    CONSTRAINT fk_usuarios_games 
                        FOREIGN KEY (ug_id) 
                        REFERENCES usuarios_games (ug_id)
                        ON DELETE CASCADE
        );");
};
