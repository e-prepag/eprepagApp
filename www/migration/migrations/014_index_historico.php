<?php

return function (PDO $pdo) {
	$pdo->exec("ALTER TABLE pins_integracao_historico
					ADD COLUMN id INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY;");

	$pdo->exec("CREATE INDEX idx_pins_hist_data ON pins_integracao_historico (pih_data);");

	$pdo->exec("CREATE INDEX idx_pins_hist_pin_id ON pins_integracao_historico (pih_pin_id);");

	$pdo->exec("CREATE OR REPLACE FUNCTION trg_pins_status_9()
					RETURNS trigger AS $$
					BEGIN
					    IF NEW.pin_status = '9' AND (OLD.pin_status IS DISTINCT FROM NEW.pin_status) THEN
					        INSERT INTO pins_integracao_historico (
					            pih_data,
					            pih_ip_id,
					            pih_pin_id,
					            pih_id,
					            pih_codretepp,
					            pin_status
					        )
					        VALUES (
					            now(),                 -- Data e hora atual
					            '0.0.0.0',             -- IP fixo
					            NEW.pin_codinterno,    -- ID do PIN
					            NEW.opr_codigo,        -- Código da operação
					            'F',                   -- Código fixo
					            9                      -- Status
					        );
					    END IF;
					
					    RETURN NEW;
					END;
					$$ LANGUAGE plpgsql;
					
					CREATE TRIGGER pins_status_9_trigger
					AFTER UPDATE ON pins
					FOR EACH ROW
					EXECUTE PROCEDURE trg_pins_status_9();");
};
