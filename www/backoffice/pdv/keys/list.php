<?php

session_start();
require_once '../../../includes/constantes.php';
require_once "/www/includes/main.php";
require_once $raiz_do_projeto . "backoffice/includes/topo.php";

$conexao_new_epp = function () {
	//Conectando ao Banco de dados
	try {
		$username = 'eprepaga_pagorama';
		$password = '3yARhv6HcJN';
		$pdo = new PDO(
			'mysql:host=10.204.168.21;port=3306;dbname=eprepaga_pag',
			$username,
			$password,
			[
				PDO::ATTR_TIMEOUT => 5, // segundos
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			]
		);
	} catch (PDOEXCEPTION $e) { //5433 
		echo "Error: " . $e->getMessage();
		return false;
	}
	return $pdo;
};


$query = $conexao_new_epp()->prepare("select preferredName,cod_situacao,id_eprepag,u.createdAt from user u 
	 inner join oauth_clients c on c.user_id = u.id_new 
	 inner join situacao_chave_api ch on ch.cod_usuario = u.id_new group by preferredName;");
$query->execute();
$resultadoSelecao = $query->fetchAll(PDO::FETCH_ASSOC);

//var_dump($resultadoSelecao);

$conexao = ConnectionPDO::getConnection()->getLink();
$idsEprepag = array_filter(array_column($resultadoSelecao, 'id_eprepag'));
$vendasPorId = [];

if (!empty($idsEprepag)) {
	$inQuery = implode(',', array_fill(0, (is_countable($idsEprepag) ? count($idsEprepag) : 0), '?'));
	$sqlVendas = "SELECT vg.vg_ug_id, COUNT(vg.vg_id) as qtd
				  FROM tb_dist_venda_games vg
				  INNER JOIN pedidos_api_pdv p ON p.id_pedido_eprepag = vg.vg_id
				  WHERE vg.vg_ug_id IN ($inQuery)
				  AND vg.vg_data_inclusao >= NOW() - INTERVAL '1 month'
				  GROUP BY vg.vg_ug_id";
	$stmtVendas = $conexao->prepare($sqlVendas);
	$stmtVendas->execute(array_values($idsEprepag));
	$resultadosVendas = $stmtVendas->fetchAll(PDO::FETCH_ASSOC);

	foreach ($resultadosVendas as $v) {
		$vendasPorId[$v['ug_id']] = $v['qtd'];
	}
}

foreach ($resultadoSelecao as &$row) {
	$row['qtd_vendas_mes'] = isset($vendasPorId[$row['id_eprepag']]) ? $vendasPorId[$row['id_eprepag']] : 0;
}
unset($row);

?>

<link href="https://cdn.datatables.net/v/dt/jszip-3.10.1/dt-1.13.8/b-2.4.2/b-html5-2.4.2/datatables.min.css"
	rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/v/dt/jszip-3.10.1/dt-1.13.8/b-2.4.2/b-html5-2.4.2/datatables.min.js"></script>

<style>
	.title {
		text-align: center;
		font-size: 24px;
		margin: 30px 0;
	}

	label {
		color: black;
		font-weight: normal;
	}

	.linha {
		width: 80%;
	}

	table.dataTable tbody td {
		padding: 12px 0;
	}

	#table,
	table.dataTable>thead>tr>th,
	table.dataTable>thead>tr>td {
		color: black;
		padding: 10px 0;
		text-align: center;
	}

	.dataTables_wrapper .dataTables_info {
		color: black;
	}

	.active {
		color: green;
		font-weight: bold;
	}

	.inactive {
		color: red;
		font-weight: bold;
	}

	.icone {
		margin-right: 5px;
	}
</style>

<div>
	<h1 class="title">Listagem de chaves API</h1>
	<hr class="linha">
	<table class="stripe hover row-border order-column" id="table">
		<thead>
			<tr>
				<th>ID</th>
				<th>PDV</th>
				<th>Data Criação</th>
				<th>Vendas (30 dias)</th>
				<th>Situação chave</th>
			</tr>
		</thead>
		<tbody>
			<?php
			if ((is_countable($resultadoSelecao) ? count($resultadoSelecao) : 0) > 0) {
				foreach ($resultadoSelecao as $key => $value) {
			?>
					<tr>
						<td><?php echo $value["id_eprepag"]; ?></td>
						<td><?php echo $value["preferredName"]; ?></td>
						<td><?php echo !empty($value["createdAt"]) ? date("d/m/Y H:i:s", strtotime($value["createdAt"])) : '-'; ?></td>
						<td><?php echo $value["qtd_vendas_mes"]; ?></td>
						<td class="<?php echo ($value["cod_situacao"] == 1) ? 'active' : 'inactive'; ?>">
							<?php echo ($value["cod_situacao"] == 1) ? 'Ativo' : 'Inativo'; ?></td>
					</tr>
			<?php
				}
			}
			?>
		</tbody>
	</table>
</div>

<script>
	$(document).ready(function() {
		let table = new DataTable('#table', {
			language: {
				lengthMenu: "Mostrar _MENU_ resultados por página",
				zeroRecords: "Não foram encontrados PDVs Bloqueados",
				info: "Mostrando a página _PAGE_ de _PAGES_",
				infoEmpty: "Dados inexistentes",
				infoFiltered: "(filtro aplicado em _MAX_ registros)",
				sSearch: "Pesquisar:",
				paginate: {
					previous: "Anterior",
					next: "Próximo",
				}
			}
		});
	});
</script>