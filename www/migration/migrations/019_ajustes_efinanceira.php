<?php

return function (PDO $pdo) {
    $pdo->exec("ALTER TABLE envios_e_financeira
                    DROP COLUMN usuario_id,
                    ADD COLUMN cpfcnpj_declarado text NULL,
                    ADD COLUMN data_anomes text NULL,
                    ADD COLUMN id_retificacao bigint NULL,
                    ADD COLUMN num_protocolo text NULL;
                    
                    CREATE INDEX idx_envios_data_anomes 
                        ON public.envios_e_financeira (data_anomes);

                    CREATE INDEX idx_envios_cpfcnpj 
                        ON public.envios_e_financeira (cpfcnpj_declarado);

                    CREATE INDEX idx_envios_protocolo 
                        ON public.envios_e_financeira (num_protocolo);

                    CREATE INDEX idx_envios_busca_combinada 
                        ON public.envios_e_financeira (cpfcnpj_declarado, data_anomes);");
};
