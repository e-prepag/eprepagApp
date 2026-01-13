<?php

return function (PDO $pdo) {
    $pdo->exec("ALTER TABLE usuarios_games
                    ADD COLUMN ug_data_encerramento_conta TIMESTAMP;
                ALTER TABLE dist_usuarios_games
                    ADD COLUMN ug_data_encerramento_conta TIMESTAMP;
                    
                CREATE OR REPLACE FUNCTION fn_set_data_encerramento_conta_gamer()
                    RETURNS TRIGGER AS $$
                    BEGIN
                        -- Se o status mudou
                        IF NEW.ug_ativo IS DISTINCT FROM OLD.ug_ativo THEN

                            -- Se deixou de ser ativo (diferente de 1)
                            IF NEW.ug_ativo <> 1 THEN

                                -- Só grava a data se ainda estiver NULL
                                IF NEW.ug_data_encerramento_conta IS NULL THEN
                                    NEW.ug_data_encerramento_conta := NOW();
                                END IF;
                            END IF;

                        END IF;

                        RETURN NEW;
                    END;
                    $$ LANGUAGE plpgsql;

                CREATE OR REPLACE FUNCTION fn_set_data_encerramento_conta_pdv()
                    RETURNS TRIGGER AS $$
                    BEGIN
                        -- Se o status mudou
                        IF NEW.ug_ativo IS DISTINCT FROM OLD.ug_ativo THEN

                            -- Se deixou de ser ativo (diferente de 1)
                            IF NEW.ug_ativo <> 1 THEN

                                -- Só grava a data se ainda estiver NULL
                                IF NEW.ug_data_encerramento_conta IS NULL THEN
                                    NEW.ug_data_encerramento_conta := NOW();
                                END IF;
                            END IF;

                        END IF;

                        RETURN NEW;
                    END;
                    $$ LANGUAGE plpgsql;

                CREATE TRIGGER trg_set_data_encerramento_conta_gamer
                BEFORE UPDATE ON usuarios_games
                FOR EACH ROW
                EXECUTE PROCEDURE fn_set_data_encerramento_conta_gamer();

                CREATE TRIGGER trg_set_data_encerramento_conta_pdv
                BEFORE UPDATE ON dist_usuarios_games
                FOR EACH ROW
                EXECUTE PROCEDURE fn_set_data_encerramento_conta_pdv();
                    ");
};
