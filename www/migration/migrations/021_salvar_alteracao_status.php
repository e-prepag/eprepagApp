<?php

return function (PDO $pdo) {
    $pdo->exec("ALTER TABLE usuarios_games_obs 
                ADD COLUMN ugo_status_user INT2 NULL;

                ALTER TABLE dist_usuarios_games_obs 
                ADD COLUMN ugo_status_user INT2 NULL;");

    $pdo->exec("CREATE OR REPLACE FUNCTION trg_func_usuarios_games_atualiza_status_ativo()
                    RETURNS TRIGGER AS $$
                    BEGIN
                        -- Verifica se o valor de ug_ativo realmente mudou
                        IF NEW.ug_ativo IS DISTINCT FROM OLD.ug_ativo THEN
                            INSERT INTO usuarios_games_obs (
                                ug_id, 
                                ug_obs, 
                                ugo_user_insert, 
                                ugo_data,
                                ugo_status_user
                            ) VALUES (
                                NEW.ug_id,
                                'Status de atividade do usuario mudado para ' || NEW.ug_ativo,
                                'Sistema',
                                NOW(),
                                NEW.ug_ativo
                            );
                        END IF;

                        RETURN NEW;
                    END;
                    $$ LANGUAGE plpgsql;

                -- Criação do gatilho associado à tabela
                CREATE TRIGGER trg_usuarios_games_ativo_update
                AFTER UPDATE ON usuarios_games
                FOR EACH ROW
                EXECUTE PROCEDURE trg_func_usuarios_games_atualiza_status_ativo();");


    $pdo->exec("CREATE OR REPLACE FUNCTION trg_func_dist_usuarios_games_atualiza_status_ativo()
                    RETURNS TRIGGER AS $$
                    BEGIN
                        -- Verifica se o valor de ug_ativo realmente mudou
                        IF NEW.ug_ativo IS DISTINCT FROM OLD.ug_ativo THEN
                            INSERT INTO dist_usuarios_games_obs (
                                ug_id, 
                                ug_obs, 
                                ugo_user_insert, 
                                ugo_data,
                                ugo_status_user
                            ) VALUES (
                                NEW.ug_id,
                                'Status de atividade do usuario mudado para ' || NEW.ug_ativo,
                                'Sistema',
                                NOW(),
                                NEW.ug_ativo
                            );
                        END IF;
                        
                        RETURN NEW;
                    END;
                    $$ LANGUAGE plpgsql;
                    
                -- Criação do gatilho associado à tabela
                CREATE TRIGGER trg_dist_usuarios_games_ativo_update
                AFTER UPDATE ON dist_usuarios_games
                FOR EACH ROW
                EXECUTE PROCEDURE trg_func_dist_usuarios_games_atualiza_status_ativo();");
};
