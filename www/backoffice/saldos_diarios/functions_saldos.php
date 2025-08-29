<?php

require_once "/www/includes/constantes.php";
require_once "/www/includes/gamer/functions.php";
require_once "/www/db/connect.php";
require_once "/www/db/ConnectionPDO.php";

function buscarSaldosDiarios($data_inicial, $data_final, $tipo_cliente)
{
	$pdo = ConnectionPDO::getConnection()->getLink();

	$sql = "";
	if ($tipo_cliente == 4) {
		$sql .= "SELECT
				  data,
				  SUM(saldo_inicial) AS saldo_inicial,
				  SUM(entradas_ate_corte) AS entradas_ate_corte,
				  SUM(saidas_ate_corte) AS saidas_ate_corte,
				  SUM(saldo_corte) AS saldo_corte,
				  SUM(entradas_completas) AS entradas_completas,
				  SUM(saidas_completas) AS saidas_completas,
				  SUM(saldo_final) AS saldo_final
				FROM (";
	}
	if ($tipo_cliente == 3 || $tipo_cliente == 4) {
		$sql .= "(";
		$sql .= "WITH parametros AS (
				    SELECT
				        :data_inicial::date AS data_inicio,
				        :data_final::date AS data_fim,
				        '18:30:00'::time AS horario_corte
				),
					
				-- 1. Último saldo de cada usuário antes do período
				ultimo_saldo_anterior AS (
				    SELECT DISTINCT ON (sl.dugsl_ug_id)
				        sl.dugsl_ug_id,
				        sl.dugsl_ug_perfil_saldo AS saldo
				    FROM dist_usuarios_games_saldo_log sl
				    WHERE sl.dugsl_data_inclusao < (SELECT data_inicio FROM parametros)
				    ORDER BY sl.dugsl_ug_id, sl.dugsl_data_inclusao DESC
				),
					
				-- 2. Saldo inicial total (apenas soma dos anteriores)
				saldo_inicial_sistema AS (
				    SELECT COALESCE(SUM(saldo), 0) AS valor
				    FROM ultimo_saldo_anterior
				),
					
				-- 3. Movimentações até 18:30 (para saldo corte)
				movimentacoes_ate_corte AS (
				    SELECT
				        sl.dugsl_ug_id,
				        sl.dugsl_data_inclusao::date AS dia,
				        CASE
				            WHEN ROW_NUMBER() OVER (PARTITION BY sl.dugsl_ug_id ORDER BY sl.dugsl_data_inclusao) = 1
				            THEN sl.dugsl_ug_perfil_saldo - COALESCE(usa.saldo, 0)
				            ELSE sl.dugsl_ug_perfil_saldo - LAG(sl.dugsl_ug_perfil_saldo)
				                 OVER (PARTITION BY sl.dugsl_ug_id ORDER BY sl.dugsl_data_inclusao)
				        END AS delta
				    FROM dist_usuarios_games_saldo_log sl
				    LEFT JOIN ultimo_saldo_anterior usa ON usa.dugsl_ug_id = sl.dugsl_ug_id
				    WHERE sl.dugsl_data_inclusao::date >= (SELECT data_inicio FROM parametros)
				      AND sl.dugsl_data_inclusao::date <= (SELECT data_fim FROM parametros)
				      AND sl.dugsl_data_inclusao::time <= (SELECT horario_corte FROM parametros)
				),
					
				-- 4. Movimentações completas do dia (para saldo final)
				movimentacoes_completas AS (
				    SELECT
				        sl.dugsl_ug_id,
				        sl.dugsl_data_inclusao::date AS dia,
				        CASE
				            WHEN ROW_NUMBER() OVER (PARTITION BY sl.dugsl_ug_id ORDER BY sl.dugsl_data_inclusao) = 1
				            THEN sl.dugsl_ug_perfil_saldo - COALESCE(usa.saldo, 0)
				            ELSE sl.dugsl_ug_perfil_saldo - LAG(sl.dugsl_ug_perfil_saldo)
				                 OVER (PARTITION BY sl.dugsl_ug_id ORDER BY sl.dugsl_data_inclusao)
				        END AS delta
				    FROM dist_usuarios_games_saldo_log sl
				    LEFT JOIN ultimo_saldo_anterior usa ON usa.dugsl_ug_id = sl.dugsl_ug_id
				    WHERE sl.dugsl_data_inclusao::date >= (SELECT data_inicio FROM parametros)
				      AND sl.dugsl_data_inclusao::date <= (SELECT data_fim FROM parametros)
				),
					
				-- 5. Entradas e saídas até 18:30
				entradas_saidas_ate_corte AS (
				    SELECT
				        dugsl_data_inclusao::date AS dia,
				        dugsl_ug_id,
				        -- Calcula entrada (quando saldo novo > saldo antes)
				        CASE
				            WHEN dugsl_ug_perfil_saldo > dugsl_ug_perfil_saldo_antes
				            THEN dugsl_ug_perfil_saldo - dugsl_ug_perfil_saldo_antes
				            ELSE 0
				        END AS entrada,
				        -- Calcula saída (quando saldo novo < saldo antes)
				        CASE
				            WHEN dugsl_ug_perfil_saldo < dugsl_ug_perfil_saldo_antes
				            THEN dugsl_ug_perfil_saldo_antes - dugsl_ug_perfil_saldo
				            ELSE 0
				        END AS saida
				    FROM dist_usuarios_games_saldo_log
				    WHERE dugsl_data_inclusao >= (SELECT data_inicio FROM parametros)
				      AND dugsl_data_inclusao <= (SELECT (data_fim + INTERVAL '1 day')::date FROM parametros)
				      AND dugsl_data_inclusao::time <= (SELECT horario_corte FROM parametros)
				),
					
				-- 6. Entradas e saídas completas (dia todo)
				entradas_saidas_completas AS (
				    SELECT
				        dugsl_data_inclusao::date AS dia,
				        dugsl_ug_id,
				        -- Calcula entrada (quando saldo novo > saldo antes)
				        CASE
				            WHEN dugsl_ug_perfil_saldo > dugsl_ug_perfil_saldo_antes
				            THEN dugsl_ug_perfil_saldo - dugsl_ug_perfil_saldo_antes
				            ELSE 0
				        END AS entrada,
				        -- Calcula saída (quando saldo novo < saldo antes)
				        CASE
				            WHEN dugsl_ug_perfil_saldo < dugsl_ug_perfil_saldo_antes
				            THEN dugsl_ug_perfil_saldo_antes - dugsl_ug_perfil_saldo
				            ELSE 0
				        END AS saida
				    FROM dist_usuarios_games_saldo_log
				    WHERE dugsl_data_inclusao >= (SELECT data_inicio FROM parametros)
				      AND dugsl_data_inclusao <= (SELECT (data_fim + INTERVAL '1 day')::date FROM parametros)
				),
					
				-- 7. Totais de entradas e saídas até 18:30
				totais_ate_corte AS (
				    SELECT
				        dia,
				        COALESCE(SUM(entrada), 0) AS total_entradas_corte,
				        COALESCE(SUM(saida), 0) AS total_saidas_corte
				    FROM entradas_saidas_ate_corte
				    GROUP BY dia
				),
					
				-- 8. Totais de entradas e saídas completas
				totais_completos AS (
				    SELECT
				        dia,
				        COALESCE(SUM(entrada), 0) AS total_entradas,
				        COALESCE(SUM(saida), 0) AS total_saidas
				    FROM entradas_saidas_completas
				    GROUP BY dia
				),
					
				-- 9. Variação diária até o horário de corte (18:30)
				variacao_ate_corte AS (
				    SELECT
				        dia,
				        SUM(delta) AS variacao
				    FROM movimentacoes_ate_corte
				    WHERE delta IS NOT NULL
				    GROUP BY dia
				),
					
				-- 10. Variação diária completa (dia todo)
				variacao_completa AS (
				    SELECT
				        dia,
				        SUM(delta) AS variacao
				    FROM movimentacoes_completas
				    WHERE delta IS NOT NULL
				    GROUP BY dia
				),
					
				-- 11. Todas as datas do período
				datas_periodo AS (
				    SELECT generate_series(
				        (SELECT data_inicio FROM parametros),
				        (SELECT data_fim FROM parametros),
				        '1 day'::interval
				    )::date AS data
				),
					
				-- 12. Saldo até 18:30 (para coluna saldo_corte)
				saldos_ate_corte AS (
				    SELECT
				        d.data,
				        sis.valor + SUM(COALESCE(v.variacao, 0)) OVER (
				            ORDER BY d.data
				            ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW
				        ) AS saldo_ate_1830
				    FROM datas_periodo d
				    LEFT JOIN variacao_ate_corte v ON v.dia = d.data
				    CROSS JOIN saldo_inicial_sistema sis
				),
					
				-- 13. Saldo completo acumulado (para saldo_inicial do próximo dia)
				saldos_completos AS (
				    SELECT
				        d.data,
				        sis.valor + SUM(COALESCE(v.variacao, 0)) OVER (
				            ORDER BY d.data
				            ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW
				        ) AS saldo_completo
				    FROM datas_periodo d
				    LEFT JOIN variacao_completa v ON v.dia = d.data
				    CROSS JOIN saldo_inicial_sistema sis
				)
					
				-- 14. Resultado final
				SELECT
				    sc.data,
				    -- Saldo inicial: saldo completo do dia anterior (ou saldo inicial do sistema para o primeiro dia)
				    LAG(sc.saldo_completo, 1, sis.valor) OVER (ORDER BY sc.data) AS saldo_inicial,
				    -- Entradas e saídas até 18:30
				    COALESCE(tac.total_entradas_corte, 0) AS entradas_ate_corte,
				    COALESCE(tac.total_saidas_corte, 0) AS saidas_ate_corte,
				    -- Saldo até 18:30 
				    sac.saldo_ate_1830 AS saldo_corte,
				    -- Entradas e saídas completas (dia todo)
				    COALESCE(tc.total_entradas, 0) AS entradas_completas,
				    COALESCE(tc.total_saidas, 0) AS saidas_completas,
				    -- Saldo final: saldo completo do dia (até 23:59:59)
				    sc.saldo_completo AS saldo_final
				FROM saldos_completos sc
				JOIN saldos_ate_corte sac ON sac.data = sc.data
				LEFT JOIN totais_ate_corte tac ON tac.dia = sc.data
				LEFT JOIN totais_completos tc ON tc.dia = sc.data
				CROSS JOIN saldo_inicial_sistema sis
				ORDER BY sc.data
				)";
	}
	if ($tipo_cliente == 4) {

		$sql .= "UNION ALL";
	}
	if ($tipo_cliente == 2 || $tipo_cliente == 4) {
		$sql .= "(";
		$sql .= "WITH parametros AS (
				    SELECT
				        :data_inicial::date AS data_inicio,
				        :data_final::date AS data_fim,
				        '18:30:00'::time AS horario_corte
				),
					
				-- 1. Último saldo de cada usuário antes do período
				ultimo_saldo_anterior AS (
				    SELECT DISTINCT ON (sl.ugsl_ug_id)
				        sl.ugsl_ug_id,
				        sl.ugsl_ug_perfil_saldo AS saldo
				    FROM usuarios_games_saldo_log sl
				    WHERE sl.ugsl_data_inclusao < (SELECT data_inicio FROM parametros)
				    ORDER BY sl.ugsl_ug_id, sl.ugsl_data_inclusao DESC
				),
					
				-- 2. Saldo inicial total (apenas soma dos anteriores)
				saldo_inicial_sistema AS (
				    SELECT COALESCE(SUM(saldo), 0) AS valor
				    FROM ultimo_saldo_anterior
				),
					
				-- 3. Movimentações até 18:30 (para saldo corte)
				movimentacoes_ate_corte AS (
				    SELECT
				        sl.ugsl_ug_id,
				        sl.ugsl_data_inclusao::date AS dia,
				        CASE
				            WHEN ROW_NUMBER() OVER (PARTITION BY sl.ugsl_ug_id ORDER BY sl.ugsl_data_inclusao) = 1
				            THEN sl.ugsl_ug_perfil_saldo - COALESCE(usa.saldo, 0)
				            ELSE sl.ugsl_ug_perfil_saldo - LAG(sl.ugsl_ug_perfil_saldo)
				                 OVER (PARTITION BY sl.ugsl_ug_id ORDER BY sl.ugsl_data_inclusao)
				        END AS delta
				    FROM usuarios_games_saldo_log sl
				    LEFT JOIN ultimo_saldo_anterior usa ON usa.ugsl_ug_id = sl.ugsl_ug_id
				    WHERE sl.ugsl_data_inclusao::date >= (SELECT data_inicio FROM parametros)
				      AND sl.ugsl_data_inclusao::date <= (SELECT data_fim FROM parametros)
				      AND sl.ugsl_data_inclusao::time <= (SELECT horario_corte FROM parametros)
				),
					
				-- 4. Movimentações completas do dia (para saldo final)
				movimentacoes_completas AS (
				    SELECT
				        sl.ugsl_ug_id,
				        sl.ugsl_data_inclusao::date AS dia,
				        CASE
				            WHEN ROW_NUMBER() OVER (PARTITION BY sl.ugsl_ug_id ORDER BY sl.ugsl_data_inclusao) = 1
				            THEN sl.ugsl_ug_perfil_saldo - COALESCE(usa.saldo, 0)
				            ELSE sl.ugsl_ug_perfil_saldo - LAG(sl.ugsl_ug_perfil_saldo)
				                 OVER (PARTITION BY sl.ugsl_ug_id ORDER BY sl.ugsl_data_inclusao)
				        END AS delta
				    FROM usuarios_games_saldo_log sl
				    LEFT JOIN ultimo_saldo_anterior usa ON usa.ugsl_ug_id = sl.ugsl_ug_id
				    WHERE sl.ugsl_data_inclusao::date >= (SELECT data_inicio FROM parametros)
				      AND sl.ugsl_data_inclusao::date <= (SELECT data_fim FROM parametros)
				),
					
				-- 5. Entradas e saídas até 18:30
				entradas_saidas_ate_corte AS (
				    SELECT
				        ugsl_data_inclusao::date AS dia,
				        ugsl_ug_id,
				        -- Calcula entrada (quando saldo novo > saldo antes)
				        CASE
				            WHEN ugsl_ug_perfil_saldo > ugsl_ug_perfil_saldo_antes
				            THEN ugsl_ug_perfil_saldo - ugsl_ug_perfil_saldo_antes
				            ELSE 0
				        END AS entrada,
				        -- Calcula saída (quando saldo novo < saldo antes)
				        CASE
				            WHEN ugsl_ug_perfil_saldo < ugsl_ug_perfil_saldo_antes
				            THEN ugsl_ug_perfil_saldo_antes - ugsl_ug_perfil_saldo
				            ELSE 0
				        END AS saida
				    FROM usuarios_games_saldo_log
				    WHERE ugsl_data_inclusao >= (SELECT data_inicio FROM parametros)
				      AND ugsl_data_inclusao <= (SELECT data_fim FROM parametros)
				      AND ugsl_data_inclusao::time <= (SELECT horario_corte FROM parametros)
				),
					
				-- 6. Entradas e saídas completas (dia todo)
				entradas_saidas_completas AS (
				    SELECT
				        ugsl_data_inclusao::date AS dia,
				        ugsl_ug_id,
				        -- Calcula entrada (quando saldo novo > saldo antes)
				        CASE
				            WHEN ugsl_ug_perfil_saldo > ugsl_ug_perfil_saldo_antes
				            THEN ugsl_ug_perfil_saldo - ugsl_ug_perfil_saldo_antes
				            ELSE 0
				        END AS entrada,
				        -- Calcula saída (quando saldo novo < saldo antes)
				        CASE
				            WHEN ugsl_ug_perfil_saldo < ugsl_ug_perfil_saldo_antes
				            THEN ugsl_ug_perfil_saldo_antes - ugsl_ug_perfil_saldo
				            ELSE 0
				        END AS saida
				    FROM usuarios_games_saldo_log
				    WHERE ugsl_data_inclusao >= (SELECT data_inicio FROM parametros)
				      AND ugsl_data_inclusao <= (SELECT data_fim FROM parametros)
				),
					
				-- 7. Totais de entradas e saídas até 18:30
				totais_ate_corte AS (
				    SELECT
				        dia,
				        COALESCE(SUM(entrada), 0) AS total_entradas_corte,
				        COALESCE(SUM(saida), 0) AS total_saidas_corte
				    FROM entradas_saidas_ate_corte
				    GROUP BY dia
				),
					
				-- 8. Totais de entradas e saídas completas
				totais_completos AS (
				    SELECT
				        dia,
				        COALESCE(SUM(entrada), 0) AS total_entradas,
				        COALESCE(SUM(saida), 0) AS total_saidas
				    FROM entradas_saidas_completas
				    GROUP BY dia
				),
					
				-- 9. Variação diária até o horário de corte (18:30)
				variacao_ate_corte AS (
				    SELECT
				        dia,
				        SUM(delta) AS variacao
				    FROM movimentacoes_ate_corte
				    WHERE delta IS NOT NULL
				    GROUP BY dia
				),
					
				-- 10. Variação diária completa (dia todo)
				variacao_completa AS (
				    SELECT
				        dia,
				        SUM(delta) AS variacao
				    FROM movimentacoes_completas
				    WHERE delta IS NOT NULL
				    GROUP BY dia
				),
					
				-- 11. Todas as datas do período
				datas_periodo AS (
				    SELECT generate_series(
				        (SELECT data_inicio FROM parametros),
				        (SELECT data_fim FROM parametros),
				        '1 day'::interval
				    )::date AS data
				),
					
				-- 12. Saldo até 18:30 (para coluna saldo_corte)
				saldos_ate_corte AS (
				    SELECT
				        d.data,
				        sis.valor + SUM(COALESCE(v.variacao, 0)) OVER (
				            ORDER BY d.data
				            ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW
				        ) AS saldo_ate_1830
				    FROM datas_periodo d
				    LEFT JOIN variacao_ate_corte v ON v.dia = d.data
				    CROSS JOIN saldo_inicial_sistema sis
				),
					
				-- 13. Saldo completo acumulado (para saldo_inicial do próximo dia)
				saldos_completos AS (
				    SELECT
				        d.data,
				        sis.valor + SUM(COALESCE(v.variacao, 0)) OVER (
				            ORDER BY d.data
				            ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW
				        ) AS saldo_completo
				    FROM datas_periodo d
				    LEFT JOIN variacao_completa v ON v.dia = d.data
				    CROSS JOIN saldo_inicial_sistema sis
				)
					
				-- 14. Resultado final
				SELECT
				    sc.data,
				    -- Saldo inicial: saldo completo do dia anterior (ou saldo inicial do sistema para o primeiro dia)
				    LAG(sc.saldo_completo, 1, sis.valor) OVER (ORDER BY sc.data) AS saldo_inicial,
				    -- Entradas e saídas até 18:30
				    COALESCE(tac.total_entradas_corte, 0) AS entradas_ate_corte,
				    COALESCE(tac.total_saidas_corte, 0) AS saidas_ate_corte,
				    -- Saldo até 18:30 
				    sac.saldo_ate_1830 AS saldo_corte,
				    -- Entradas e saídas completas (dia todo)
				    COALESCE(tc.total_entradas, 0) AS entradas_completas,
				    COALESCE(tc.total_saidas, 0) AS saidas_completas,
				    -- Saldo final: saldo completo do dia (até 23:59:59)
				    sc.saldo_completo AS saldo_final
				FROM saldos_completos sc
				JOIN saldos_ate_corte sac ON sac.data = sc.data
				LEFT JOIN totais_ate_corte tac ON tac.dia = sc.data
				LEFT JOIN totais_completos tc ON tc.dia = sc.data
				CROSS JOIN saldo_inicial_sistema sis
				ORDER BY sc.data
				)";
	}
	if ($tipo_cliente == 4) {
		$sql .= ") AS combinados
					GROUP BY data
					ORDER BY data DESC";
	}

	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':data_inicial', $data_inicial);
	$stmt->bindParam(':data_final', $data_final);
	//$stmt->bindParam(':tipo_cliente', $tipo_cliente);
	$stmt->execute();
	$saldos_agrupados = $stmt->fetchAll(PDO::FETCH_ASSOC);

	//echo $sql;
	//print_r($saldos_agrupados);

	if ($saldos_agrupados) {
		return $saldos_agrupados;
	} else {
		return [];
	}
}

function gerarTabelaClientes(array $dados, $tipo_cliente)
{ //class="tabela-clientes"
	$html = '
    <table id="tabela-clientes" class="table table-striped">
        <thead>
            <tr>
				<th>Data</th>
                <th>Tipo Cliente</th>
                <th>Saldo Inicial</th>
                <th>Entradas STR</th>
                <th>Saídas STR</th>
                <th>Saldo Final STR</th>
				<th>Entradas Dia</th>
                <th>Saídas Dia</th>
                <th>Saldo Final</th>
            </tr>
        </thead>
        <tbody>
    ';

	// Inicialização das variáveis de soma total no início
	$soma_saldo_inicial = 0;
	$soma_entradas_str = 0;
	$soma_saidas_str = 0;
	$soma_saldo_final_str = 0;
	$soma_entradas_dia = 0;
	$soma_saidas_dia = 0;
	$soma_saldo_final = 0;

	foreach ($dados as $linha) {
		$saldo_inicial = isset($linha['saldo_inicial']) ? (float) $linha['saldo_inicial'] : 0;
		$entradas_ate_corte = isset($linha['entradas_ate_corte']) ? (float) $linha['entradas_ate_corte'] : 0;
		$saidas_ate_corte = isset($linha['saidas_ate_corte']) ? (float) $linha['saidas_ate_corte'] : 0;
		$saldo_corte = isset($linha['saldo_corte']) ? (float) $linha['saldo_corte'] : 0;
		$entradas_completas = isset($linha['entradas_completas']) ? (float) $linha['entradas_completas'] : 0;
		$saidas_completas = isset($linha['saidas_completas']) ? (float) $linha['saidas_completas'] : 0;
		$saldo_final = isset($linha['saldo_final']) ? (float) $linha['saldo_final'] : 0;

		$soma_saldo_inicial += $saldo_inicial;
		$soma_entradas_str += $entradas_ate_corte;
		$soma_saidas_str += $saidas_ate_corte;
		$soma_saldo_final_str += $saldo_corte;
		$soma_entradas_dia += $entradas_completas;
		$soma_saidas_dia += $saidas_completas;
		$soma_saldo_final += $saldo_final;

		$tipo_cliente_texto = $tipo_cliente == 4 ? 'Todos' : ($tipo_cliente == 3 ? 'PDVs' : ($tipo_cliente == 2 ? 'Gamers' : 'Desconhecido'));

		$html .= '
            <tr>
				<td>' . (isset($linha['data']) ? $linha['data'] : '') . '</td>
                <td>' . $tipo_cliente_texto . '</td>
                <td>' . formatarReais($saldo_inicial) . '</td>
                <td>' . formatarReais($entradas_ate_corte) . '</td>
                <td>' . formatarReais($saidas_ate_corte) . '</td>
                <td>' . formatarReais($saldo_corte) . '</td>
				<td>' . formatarReais($entradas_completas) . '</td>
                <td>' . formatarReais($saidas_completas) . '</td>
                <td>' . formatarReais($saldo_final) . '</td>
            </tr>
        ';
	}

	$html .= '
		<tfoot>
        	<tr class="total">
        	    <td>Total</td>
				<td>' . $tipo_cliente_texto . '</td>
        	    <td>' . formatarReais($soma_saldo_inicial) . '</td>
        	    <td>' . formatarReais($soma_entradas_str) . '</td>
        	    <td>' . formatarReais($soma_saidas_str) . '</td>
        	    <td>' . formatarReais($soma_saldo_final_str) . '</td>
				<td>' . formatarReais($soma_entradas_dia) . '</td>
				<td>' . formatarReais($soma_saidas_dia) . '</td>
        	    <td>' . formatarReais($soma_saldo_final) . '</td>
        	</tr>
		</tfoot>
		<tfoot>
        	<tr class="total">
        	    <td>Média</td>
				<td>' . $tipo_cliente_texto . '</td>
        	    <td>' . formatarReais($soma_saldo_inicial/count($dados)) . '</td>
        	    <td>' . formatarReais($soma_entradas_str/count($dados)) . '</td>
        	    <td>' . formatarReais($soma_saidas_str/count($dados)) . '</td>
        	    <td>' . formatarReais($soma_saldo_final_str/count($dados)) . '</td>
				<td>' . formatarReais($soma_entradas_dia/count($dados)) . '</td>
				<td>' . formatarReais($soma_saidas_dia/count($dados)) . '</td>
        	    <td>' . formatarReais($soma_saldo_final/count($dados)) . '</td>
        	</tr>
		</tfoot>
    ';

	$html .= '</tbody></table>';

	return $html;
}

function formatarReais($valor)
{
	return 'R$ ' . number_format($valor, 2, ',', '.');
}
