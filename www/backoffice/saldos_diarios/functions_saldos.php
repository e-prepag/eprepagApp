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
				  SUM(saldo_final) AS saldo_final,
				  SUM(entradas) AS entradas,
				  SUM(saidas) AS saidas
				FROM (";
	}
	if ($tipo_cliente == 3 || $tipo_cliente == 4) {
		$sql .= "(WITH logs_filtrados AS (
				    SELECT 
				        dugsl_ug_id,
				        dugsl_data_inclusao::date AS dia,
				        dugsl_data_inclusao,
				        dugsl_ug_perfil_saldo,
				        dugsl_ug_perfil_saldo_antes
				    FROM dist_usuarios_games_saldo_log
				    WHERE dugsl_data_inclusao >= :data_inicial
				      AND dugsl_data_inclusao <= :data_final
				),
				ordenados AS (
				    SELECT *,
				           ROW_NUMBER() OVER (PARTITION BY dugsl_ug_id, dia ORDER BY dugsl_data_inclusao ASC) AS rn_asc,
				           ROW_NUMBER() OVER (PARTITION BY dugsl_ug_id, dia ORDER BY dugsl_data_inclusao DESC) AS rn_desc
				    FROM logs_filtrados
				),
				por_usuario_dia AS (
				    SELECT 
				        dugsl_ug_id,
				        dia,
				        MAX(CASE WHEN rn_desc = 1 THEN dugsl_ug_perfil_saldo END) AS saldo_final,
				        MAX(CASE WHEN rn_asc = 1 THEN dugsl_ug_perfil_saldo_antes END) AS saldo_inicial,
				        SUM(CASE WHEN dugsl_ug_perfil_saldo > dugsl_ug_perfil_saldo_antes 
				                 THEN dugsl_ug_perfil_saldo - dugsl_ug_perfil_saldo_antes ELSE 0 END) AS entradas,
				        SUM(CASE WHEN dugsl_ug_perfil_saldo < dugsl_ug_perfil_saldo_antes 
				                 THEN dugsl_ug_perfil_saldo_antes - dugsl_ug_perfil_saldo ELSE 0 END) AS saidas
				    FROM ordenados
				    GROUP BY dugsl_ug_id, dia
				)
				SELECT 
				    dia AS data,
				    SUM(saldo_inicial) AS saldo_inicial,
				    SUM(saldo_final) AS saldo_final,
				    SUM(entradas) AS entradas,
				    SUM(saidas) AS saidas
				FROM por_usuario_dia
				GROUP BY dia
				ORDER BY dia DESC)
				";
	}
	if ($tipo_cliente == 4) {

		$sql .= "UNION ALL";

	}
	if ($tipo_cliente == 2 || $tipo_cliente == 4) {
		$sql .= "(WITH logs_filtrados AS (
				    SELECT 
				        ugsl_ug_id,
				        ugsl_data_inclusao::date AS dia,
				        ugsl_data_inclusao,
				        ugsl_ug_perfil_saldo,
				        ugsl_ug_perfil_saldo_antes
				    FROM usuarios_games_saldo_log
				    WHERE ugsl_data_inclusao >= :data_inicial
				      AND ugsl_data_inclusao <= :data_final
				),
				ordenados AS (
				    SELECT *,
				           ROW_NUMBER() OVER (PARTITION BY ugsl_ug_id, dia ORDER BY ugsl_data_inclusao ASC) AS rn_asc,
				           ROW_NUMBER() OVER (PARTITION BY ugsl_ug_id, dia ORDER BY ugsl_data_inclusao DESC) AS rn_desc
				    FROM logs_filtrados
				),
				por_usuario_dia AS (
				    SELECT 
				        ugsl_ug_id,
				        dia,
				        MAX(CASE WHEN rn_desc = 1 THEN ugsl_ug_perfil_saldo END) AS saldo_final,
				        MAX(CASE WHEN rn_asc = 1 THEN ugsl_ug_perfil_saldo_antes END) AS saldo_inicial,
				        SUM(CASE WHEN ugsl_ug_perfil_saldo > ugsl_ug_perfil_saldo_antes 
				                 THEN ugsl_ug_perfil_saldo - ugsl_ug_perfil_saldo_antes ELSE 0 END) AS entradas,
				        SUM(CASE WHEN ugsl_ug_perfil_saldo < ugsl_ug_perfil_saldo_antes 
				                 THEN ugsl_ug_perfil_saldo_antes - ugsl_ug_perfil_saldo ELSE 0 END) AS saidas
				    FROM ordenados
				    GROUP BY ugsl_ug_id, dia
				)
				SELECT 
				    dia AS data,
				    SUM(saldo_inicial) AS saldo_inicial,
				    SUM(saldo_final) AS saldo_final,
				    SUM(entradas) AS entradas,
				    SUM(saidas) AS saidas
				FROM por_usuario_dia
				GROUP BY dia
				ORDER BY dia DESC)
				";
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
{
	$html = '
    <table class="tabela-clientes">
        <thead>
            <tr>
				<th>Data</th>
                <th>Tipo Cliente</th>
                <th>Saldo Inicial</th>
                <th>Entradas</th>
                <th>Saídas</th>
                <th>Saldo Final</th>
            </tr>
        </thead>
        <tbody>
    ';

	$total_inicial = 0;
	$total_entradas = 0;
	$total_saidas = 0;
	$total_final = 0;

	foreach ($dados as $linha) {
		$saldo_inicial = (float) $linha['saldo_inicial'];
		$entradas = (float) $linha['entradas'];
		$saidas = (float) $linha['saidas'];
		$saldo_final = (float) $linha['saldo_final'];

		$total_inicial += $saldo_inicial;
		$total_entradas += $entradas;
		$total_saidas += $saidas;
		$total_final += $saldo_final;

		$tipo_cliente_texto = $tipo_cliente == 4 ? 'Todos' : ($tipo_cliente == 3 ? 'PDVs' : ($tipo_cliente == 2 ? 'Gamers' : 'Desconhecido'));

		$html .= '
            <tr>
				<td>' . $linha['data'] . '</td>
                <td>' . $tipo_cliente_texto . '</td>
                <td>' . formatarReais($saldo_inicial) . '</td>
                <td>' . formatarReais($entradas) . '</td>
                <td>' . formatarReais($saidas) . '</td>
                <td>' . formatarReais($saldo_final) . '</td>
            </tr>
        ';
	}

	$html .= '
        <tr class="total">
            <td>Total</td>
			 <td></td>
            <td>' . formatarReais($total_inicial) . '</td>
            <td>' . formatarReais($total_entradas) . '</td>
            <td>' . formatarReais($total_saidas) . '</td>
            <td>' . formatarReais($total_final) . '</td>
        </tr>
    ';

	$html .= '</tbody></table>';

	return $html;
}

function formatarReais($valor)
{
	return 'R$ ' . number_format($valor, 2, ',', '.');
}

