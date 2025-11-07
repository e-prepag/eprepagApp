<?php
//Status dos PINs E-PREPAG
$PINS_PUBLISHERS_STATUS_VALUES = array(
	'D'	=> '1',
	'V'	=> '3',
	'L'	=> '6',
	'P'	=> '7',
	'U'	=> '8',
);

function retorna_id_pin($pin, $id)
{
	$sql = "SELECT pin_codinterno 
            FROM pins 
            WHERE pin_codigo = $1 AND opr_codigo = $2";

	$rs_log = SQLexecuteQueryParams($sql, [$pin, $id]);

	if ($rs_log && pg_num_rows($rs_log) > 0) {
		$rs_log_row = pg_fetch_array($rs_log);
		if (!empty($rs_log_row['pin_codinterno'])) {
			return $rs_log_row['pin_codinterno'];
		}
	}

	// Informa zero (0) quando não foi encontrado
	return '0';
}

function retorna_status($pin, $id)
{
	$sql = "SELECT p.pin_status
				FROM pins p
				WHERE p.pin_codigo = $1
				  AND p.opr_codigo = $2
				  AND NOT EXISTS (
				      SELECT 1
				      FROM pins_integracao_historico pih
				      WHERE pih.pih_pin_id = p.pin_codinterno AND (pih.pin_status = 8 AND pih.pih_codretepp = '2' OR pih.pin_status = 9 AND pih.pih_codretepp = 'F')
				  );";

	$rs_log = SQLexecuteQueryParams($sql, [$pin, $id]);

	if ($rs_log && pg_num_rows($rs_log) > 0) {
		$rs_log_row = pg_fetch_array($rs_log);
		if (!empty($rs_log_row['pin_status'])) {
			return $rs_log_row['pin_status'];
		}
	}

	// Informa zero (0) quando não foi encontrado
	return '0';
}


function verifica_venda($pin, $opr)
{
	$sql_pdv = "SELECT 1 
                FROM pins p
                JOIN tb_dist_venda_games_modelo_pins vp 
                    ON vp.vgmp_pin_codinterno = p.pin_codinterno 
                WHERE p.pin_codigo = $1 AND p.opr_codigo = $2 
                LIMIT 1";

	$res_pdv = SQLexecuteQueryParams($sql_pdv, [$pin, $opr]);

	if ($res_pdv && pg_num_rows($res_pdv) > 0) {
		return true; // Venda encontrada no PDV
	}

	$sql_gamer = "SELECT 1 
                  FROM pins p
                  JOIN tb_venda_games_modelo_pins vp 
                      ON vp.vgmp_pin_codinterno = p.pin_codinterno 
                  WHERE p.pin_codigo = $1 AND p.opr_codigo = $2 
                  LIMIT 1";

	$res_gamer = SQLexecuteQueryParams($sql_gamer, [$pin, $opr]);

	if ($res_gamer && pg_num_rows($res_gamer) > 0) {
		return true; // Venda encontrada no gamer
	}

	return false; // Nenhuma venda encontrada
}


function log_pin($codretepp, $pin, $id)
{
	// 1. Chame as funções refatoradas (sem addslashes) e armazene os valores
	$aux_id_pin = retorna_id_pin($pin, $id);
	$aux_status = retorna_status($pin, $id);
	$aux_ip = retorna_ip_acesso();

	// 2. A query SQL agora usa placeholders ($1, $2, ...)
	$sql = "INSERT INTO pins_integracao_historico 
            VALUES (NOW(), $1, $2, $3, $4, $5)";

	// 3. Crie o array de parâmetros na ordem correta
	$params = [
		$aux_ip,
		$aux_id_pin,
		$id,
		$codretepp,
		$aux_status
	];

	// 4. Execute a query parametrizada
	$rs_log = SQLexecuteQueryParams($sql, $params);

	if (!$rs_log) {
		echo "<font color='#FF0000'><b>Erro na gera&ccedil;&atilde;o de LOG.\n</b></font><br>";
	}
}

function verifica_valor_pin($cod_pin, $valor, $id)
{
	global $PINS_PUBLISHERS_STATUS_VALUES;

	$sql = "SELECT pin_valor 
            FROM pins 
            WHERE pin_codigo = $1 
              AND (pin_status = $2 OR pin_status = $3 OR pin_status = $4) 
              AND opr_codigo = $5
            LIMIT 1"; // Adicionado LIMIT 1 para otimização

	$params = [
		$cod_pin,
		intval($PINS_PUBLISHERS_STATUS_VALUES['V']),
		intval($PINS_PUBLISHERS_STATUS_VALUES['L']),
		intval($PINS_PUBLISHERS_STATUS_VALUES['P']),
		$id
	];

	$rs_oper = SQLexecuteQueryParams($sql, $params);

	sleep(1); // Mantendo o sleep da lógica original

	if ($rs_oper && pg_num_rows($rs_oper) > 0) {
		$rs_oper_row = pg_fetch_array($rs_oper);
		// Retorna diretamente o resultado da comparação (true ou false)
		return $rs_oper_row['pin_valor'] == $valor;
	}

	return false; // Se a query falhou ou não retornou linhas
}

function retorna_pin_valor($pin, $id)
{
	$sql = "SELECT pin_valor FROM pins WHERE pin_codigo = $1 AND opr_codigo = $2";

	$rs_log = SQLexecuteQueryParams($sql, [$pin, $id]);

	if ($rs_log && pg_num_rows($rs_log) > 0) {
		$rs_log_row = pg_fetch_array($rs_log);

		// Mantendo a lógica original: se o valor for uma string vazia, retorna '0'
		if ($rs_log_row['pin_valor'] != '') {
			return $rs_log_row['pin_valor'];
		} else {
			return '0';
		}
	} else {
		// informa zero (0) quando nao foi encontrado -- ATENCAUN
		return '0';
	}
}

function verifica_validade($pin, $id)
{
	// 1. Calcule o período em PHP. É seguro, pois é baseado em lógica interna.
	$periodo = (intval($id) == 166) ? 60 : 180;

	// 2. A query usa placeholders para $pin ($1), $id ($2), e $periodo ($3)
	// Usamos ($3 || ' day')::interval para construir o intervalo dinâmico
	// de forma segura dentro do PostgreSQL.
	$sql = "SELECT 1
        FROM pins
        WHERE pin_codigo = $1
          AND opr_codigo = $2
          AND (
              (pin_validade >= CURRENT_DATE AND opr_codigo <> 166)
              OR
              ((CURRENT_DATE - INTERVAL '$periodo day') <= pin_datavenda)
          )
        LIMIT 1;
    ";

	// 3. Passe os valores originais no array de parâmetros
	$params = [$pin, $id];

	$rs = SQLexecuteQueryParams($sql, $params);

	// 4. A lógica de retorno original já estava correta
	return $rs && pg_fetch_row($rs) !== false;
}
