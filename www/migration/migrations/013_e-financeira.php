<?php

return function (PDO $pdo) {
	$pdo->exec("CREATE TABLE envios_e_financeira (
				    id BIGSERIAL PRIMARY KEY,
				    tipo TEXT NOT NULL,
				    status_envio TEXT NOT NULL,
				    versao_efin TEXT NOT NULL,
					versao_epp TEXT NOT NULL,
				    nome_arquivo TEXT NOT NULL,
				    descricao TEXT
				);");
};
