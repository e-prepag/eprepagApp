<?php

return function (PDO $pdo) {
    $pdo->exec("CREATE TABLE IF NOT EXISTS jsons_epp (
                    id SERIAL PRIMARY KEY,
                    nome VARCHAR(255) NOT NULL,
                    conteudo TEXT NOT NULL,
                    versao SMALLINT DEFAULT 1,
                    data_atualizacao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE (nome, versao)
                );
                
                CREATE INDEX idx_nome ON jsons_epp (nome);
                
                -- Função de trigger
                CREATE OR REPLACE FUNCTION atualiza_timestamp()
                RETURNS TRIGGER AS $$
                BEGIN
                    NEW.data_atualizacao = CURRENT_TIMESTAMP;
                    RETURN NEW;
                END;
                $$ LANGUAGE plpgsql;
                
                -- Trigger
                CREATE TRIGGER trg_atualiza_jsons_epp
                BEFORE UPDATE ON jsons_epp
                FOR EACH ROW
                EXECUTE FUNCTION atualiza_timestamp();

                ");
};
