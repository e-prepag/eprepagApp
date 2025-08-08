<?php

return function(PDO $pdo) {
    $pdo->exec("CREATE TABLE dist_usuarios_aceito_termos (
                    id SERIAL PRIMARY KEY,
                    ug_id INTEGER NOT NULL,
                    versao_termo VARCHAR(50) NOT NULL,
                    aceitou BOOLEAN NOT NULL DEFAULT FALSE,
                    data_aceite TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
                    ip VARCHAR(45),
                    dispositivo TEXT,
                    localizacao TEXT,

                    CONSTRAINT fk_usuario
                      FOREIGN KEY (ug_id)
                      REFERENCES dist_usuarios_games (ug_id)
                      ON DELETE CASCADE
                );");
};