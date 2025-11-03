<?php

return function (PDO $pdo) {
	$pdo->exec("ALTER TABLE pins_integracao_historico
					ADD COLUMN id INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY;");

	$pdo->exec("CREATE INDEX idx_pins_hist_data ON pins_integracao_historico (pih_data);");

	$pdo->exec("CREATE INDEX idx_pins_hist_pin_id ON pins_integracao_historico (pih_pin_id);");
};
