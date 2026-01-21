<?php

return function (PDO $pdo) {
    $pdo->exec("ALTER TABLE envios_e_financeira
                    DROP COLUMN usuario_id,
                    ADD COLUMN cpfcnpj_declarado text NULL,
                    ADD COLUMN data_anomes text NULL,
                    ADD COLUMN id_retificacao bigint NULL,
                    ADD COLUMN num_protocolo text NULL;");
};
