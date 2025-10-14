<?php

return function (PDO $pdo) {
    $pdo->exec("ALTER table usuario_logs_acoes add column dados_request text not null default '';");
    
    $pdo->exec("CREATE TABLE usuario_logs_acoes_admin (
	                id serial4 NOT NULL,
	                usuario_id varchar(20) NOT NULL,
	                tipo_usuario varchar(10) NOT NULL,
	                data_hora_registro timestamp DEFAULT now() NOT NULL,
	                ip_usuario varchar(45) NOT NULL,
	                rota_acessada text NULL,
	                dados_extras text NULL,
	                CONSTRAINT usuario_logs_acoes_admin_pkey PRIMARY KEY (id)
                );");
};
