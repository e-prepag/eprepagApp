<?php

return function (PDO $pdo) {
    $pdo->exec("CREATE TABLE configuracao_pagamentos (
                    id  SERIAL PRIMARY KEY,
                    chave VARCHAR(100) NOT NULL,
                    valor TEXT NOT NULL,
                    descricao VARCHAR(255),
                    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    CONSTRAINT uk_configuracao_chave UNIQUE (chave)
                );

                -- Criar índice na coluna chave
                CREATE INDEX idx_configuracao_chave ON configuracao_pagamentos(chave);

                -- Criar função para atualizar data_atualizacao automaticamente
                CREATE OR REPLACE FUNCTION atualizar_data_atualizacao()
                RETURNS TRIGGER AS $$
                BEGIN
                    NEW.data_atualizacao = CURRENT_TIMESTAMP;
                    RETURN NEW;
                END;
                $$ LANGUAGE plpgsql;

                -- Criar trigger para atualizar automaticamente a data
                CREATE TRIGGER trg_atualizar_data_atualizacao
                    BEFORE UPDATE ON configuracao_pagamentos
                    FOR EACH ROW
                    EXECUTE PROCEDURE atualizar_data_atualizacao();

                -- Inserir configurações padrão
                INSERT INTO configuracao_pagamentos (chave, valor, descricao) 
                SELECT 'PAGAMENTO_BRADESCO', '0', 'Habilita/Desabilita pagamento Bradesco'
                WHERE NOT EXISTS (SELECT 1 FROM configuracao_pagamentos WHERE chave = 'PAGAMENTO_BRADESCO');

                INSERT INTO configuracao_pagamentos (chave, valor, descricao) 
                SELECT 'PAGAMENTO_BANCO_BRASIL', '0', 'Habilita/Desabilita pagamento Banco do Brasil'
                WHERE NOT EXISTS (SELECT 1 FROM configuracao_pagamentos WHERE chave = 'PAGAMENTO_BANCO_BRASIL');

                INSERT INTO configuracao_pagamentos (chave, valor, descricao) 
                SELECT 'PAGAMENTO_ITAU', '0', 'Habilita/Desabilita pagamento Itaú'
                WHERE NOT EXISTS (SELECT 1 FROM configuracao_pagamentos WHERE chave = 'PAGAMENTO_ITAU');

                INSERT INTO configuracao_pagamentos (chave, valor, descricao) 
                SELECT 'PAGAMENTO_BOLETO', '0', 'Habilita/Desabilita pagamento Boleto'
                WHERE NOT EXISTS (SELECT 1 FROM configuracao_pagamentos WHERE chave = 'PAGAMENTO_BOLETO');

                INSERT INTO configuracao_pagamentos (chave, valor, descricao) 
                SELECT 'BANCO_BOLETO', 'bradesco', 'Banco utilizado para boleto'
                WHERE NOT EXISTS (SELECT 1 FROM configuracao_pagamentos WHERE chave = 'BANCO_BOLETO');

                INSERT INTO configuracao_pagamentos (chave, valor, descricao) 
                SELECT 'PAGAMENTO_EPREPAG_CASH', '0', 'Habilita/Desabilita Eprepag Cash'
                WHERE NOT EXISTS (SELECT 1 FROM configuracao_pagamentos WHERE chave = 'PAGAMENTO_EPREPAG_CASH');

                INSERT INTO configuracao_pagamentos (chave, valor, descricao) 
                SELECT 'PAGAMENTO_CIELO', '0', 'Habilita/Desabilita pagamento Cielo'
                WHERE NOT EXISTS (SELECT 1 FROM configuracao_pagamentos WHERE chave = 'PAGAMENTO_CIELO');

                INSERT INTO configuracao_pagamentos (chave, valor, descricao) 
                SELECT 'PAGAMENTO_PIX', '0', 'Habilita/Desabilita pagamento PIX'
                WHERE NOT EXISTS (SELECT 1 FROM configuracao_pagamentos WHERE chave = 'PAGAMENTO_PIX');

                INSERT INTO configuracao_pagamentos (chave, valor, descricao) 
                SELECT 'PAGAMENTO_PIX_PROVEDOR', 'asaas', 'Provedor principal do PIX'
                WHERE NOT EXISTS (SELECT 1 FROM configuracao_pagamentos WHERE chave = 'PAGAMENTO_PIX_PROVEDOR');

                INSERT INTO configuracao_pagamentos (chave, valor, descricao) 
                SELECT 'PAGAMENTO_PIX_PROVEDOR2', 'mercadopago', 'Provedor secundário do PIX'
                WHERE NOT EXISTS (SELECT 1 FROM configuracao_pagamentos WHERE chave = 'PAGAMENTO_PIX_PROVEDOR2');

                INSERT INTO configuracao_pagamentos (chave, valor, descricao) 
                SELECT 'PAGAMENTO_PIX_CHAVEAMENTO', 'i', 'Status do chaveamento (a=ativa, i=inativa)'
                WHERE NOT EXISTS (SELECT 1 FROM configuracao_pagamentos WHERE chave = 'PAGAMENTO_PIX_CHAVEAMENTO');

                INSERT INTO configuracao_pagamentos (chave, valor, descricao) 
                SELECT 'VALOR_TROCA', '0', 'Valor para troca de provedor'
                WHERE NOT EXISTS (SELECT 1 FROM configuracao_pagamentos WHERE chave = 'VALOR_TROCA');");
};
