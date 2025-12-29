<?php

return function (PDO $pdo) {
    $pdo->exec("ALTER TABLE dist_usuarios_games
                    ALTER COLUMN ug_senha TYPE VARCHAR(255);

                ALTER TABLE dist_usuarios_games
                    ADD COLUMN ug_senha_migrated SMALLINT NOT NULL DEFAULT 0;

                ALTER TABLE usuarios_games
                    ALTER COLUMN ug_senha TYPE VARCHAR(255);

                ALTER TABLE usuarios_games
                    ADD COLUMN ug_senha_migrated SMALLINT NOT NULL DEFAULT 0;

                ALTER TABLE dist_usuarios_games_operador
                    ALTER COLUMN ugo_senha TYPE VARCHAR(255);

                ALTER TABLE dist_usuarios_games_operador
                    ADD COLUMN ugo_senha_migrated SMALLINT NOT NULL DEFAULT 0;
                    
                ALTER TABLE usuarios
                    ALTER COLUMN shn_password TYPE VARCHAR(255);

                ALTER TABLE usuarios
                    ADD COLUMN senha_migrated BOOLEAN NOT NULL DEFAULT false;
                    
                ALTER TABLE dist_usuarios_games_chave
                    ALTER COLUMN chave TYPE VARCHAR(255);

                ALTER TABLE dist_usuarios_games_chave
                    ADD COLUMN chave_migrated SMALLINT NOT NULL DEFAULT 0;");
};
