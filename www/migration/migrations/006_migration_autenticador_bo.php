<?php

return function (PDO $pdo) {
    $pdo->exec("ALTER TABLE usuarios
                    ADD COLUMN chave_autenticador TEXT NOT NULL DEFAULT '',
                    ADD COLUMN sem_aut_data DATE NOT NULL DEFAULT CURRENT_DATE;");
                    
    $pdo->exec("CREATE TABLE usuarios_bo_dispositivos (
                	id varchar(20) NOT NULL,
                	user_id int4 NOT NULL,
                	device_token varchar(100) NOT NULL,
                	expires_at timestamp DEFAULT (now() + '30 days'::interval) NULL,
                	created_at timestamp DEFAULT now() NULL,
                	CONSTRAINT usuarios_bo_dispositivos_pkey PRIMARY KEY (id)
                );");
};