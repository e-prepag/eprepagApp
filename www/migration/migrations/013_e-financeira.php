<?php

return function (PDO $pdo) {
	$pdo->exec("CREATE TABLE envios_e_financeira (
				    id BIGSERIAL PRIMARY KEY,
				    tipo TEXT NOT NULL,
				    status_envio TEXT NOT NULL,
				    versao_efin TEXT NOT NULL,
					versao_epp TEXT NOT NULL,
				    nome_arquivo TEXT NOT NULL,
					data_criacao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
					data_envio TIMESTAMP,
					semestre_ano TEXT,
					usuario_id INT,
					retificado BOOLEAN DEFAULT FALSE,
				    descricao TEXT
				);");
};
