<?php
require_once '/www/includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL); 

$data_inicial = isset($_GET['dt_inicial']) ? $_GET['dt_inicial'] : date('Y-m-d', strtotime('-30 days'));
$data_final = isset($_GET['dt_final']) ? $_GET['dt_final'] . " 23:59:59" : date('Y-m-d') . " 23:59:59";
$data_final_sem_hora = isset($_GET['dt_final']) ? $_GET['dt_final'] : date('Y-m-d');
$tipo_cliente = isset($_GET['tipo_cliente']) ? $_GET['tipo_cliente'] : 4;
$data_atual = date('Y-m-d');

$tipo_cliente_texto = $tipo_cliente == 4 ? 'Todos' : ($tipo_cliente == 3 ? 'PDVs' : ($tipo_cliente == 2 ? 'Gamers' : 'Desconhecido'));
?>
<link href="https://cdn.datatables.net/v/dt/dt-1.13.4/datatables.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/v/dt/dt-1.13.5/datatables.min.js"></script>

<style>
	.relatorio-info {
		display: flex;
		justify-content: space-between;
		margin-bottom: 20px;
		font-size: 16px;
	}

	.tabela-clientes {
		width: 100%;
		border-collapse: collapse;
		background: #fff;
	}

	.tabela-clientes th,
	.tabela-clientes td {
		border: 1px solid #ccc;
		padding: 10px;
		text-align: center;
	}

	.tabela-clientes th {
		background-color: #e0e0e0;
	}

	.tabela-clientes tr:nth-child(even) {
		background-color: #f9f9f9;
	}

	.total {
		font-weight: bold;
		background: #dfe6e9;
	}

	.align-right {
		margin-left: auto;
	}

	.custom-justify {
		display: flex;
		width: 100%;
		flex-wrap: wrap;
		gap: 15px;
	}
	.container-cancel-pins {
		display: flex;
		justify-content: space-between;
		flex-wrap: wrap;
		gap: 20px;
		/* Adiciona uma margem entre as colunas */
	}

	/* Colunas (ajuste para uma largura proporcional) */
	.col-cancel-pins {
		flex: 1;
		min-width: 100px;
		margin: 0;
		/* Remove margens laterais desnecessárias */
	}

	.data-input {
		min-width: 200px;
	}

	.titulo-vencimento {
		font-weight: bold;
		color: #333333;
		font-family: system-ui, -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
		text-align: left;
		margin-left: 25px;
		padding-bottom: 15px;
		font-size: 21px;
	}


	@media (max-width: 480px) {

		input,
		label {
			font-size: 11px;
			/* Diminuir ainda mais o tamanho da fonte */
		}

		button {
			font-size: 10px;
			/* Diminuir o tamanho da fonte do botão */
			padding: 6px 10px;
			/* Diminuir o padding do botão */
		}
	}
</style>

<div>
	<div class="col-md-12">
		<ol class="breadcrumb top10">
			<li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice -
					<?php echo $currentAba->getDescricao(); ?></a></li>
			<li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
			<li class="active"><a
					href="<?php echo $sistema->item->getLink(); ?>"><?php echo $sistema->item->getDescricao(); ?></a>
			</li>
		</ol>
	</div>
	<h2 class="titulo-vencimento">Saldos diários - Lista</h2>
	<form id="form1" action="#" method="get" class="form-solicitacoes">
		<div class="container-cancel-pins">

			<div class="col-cancel-pins">
				<label for="tipo_cliente">Usuários</label>
				<select id="tipo_cliente" name="tipo_cliente" class="form-control">
					<option <?php if($tipo_cliente == 4) echo "selected"; ?> value="4" selected>Todos</option>
					<option <?php if($tipo_cliente == 3) echo "selected"; ?> value="3">PDVs</option>
					<option <?php if($tipo_cliente == 2) echo "selected"; ?> value="2">Gamers</option>
				</select>
			</div>

			<div class="col-cancel-pins">
				<label for="dt_inicial">Início período
				</label>
				<input id="dt_inicial" name="dt_inicial" max="<?php echo $data_atual; ?>" value="<?php echo $data_inicial; ?>" class="form-control"
					type="date">
			</div>
			<div class="col-cancel-pins">
				<label for="dt_final">Final período
				</label>
				<input id="dt_final" name="dt_final" max="<?php echo $data_atual; ?>" value="<?php echo $data_final_sem_hora; ?>" class="form-control"
					type="date">
			</div>
		</div>
		<div class="d-flex top10 custom-justify">
			<a class="btn btn-success btn-info" 
			href="gerar_csv.php?
			data_inicial=<?= urlencode($data_inicial) ?>
			&data_final=<?= urlencode($data_final_sem_hora) ?>
			&tipo_cliente=<?= urlencode($tipo_cliente) ?>" 
			target="_blank">Download</a>
			<button type="submit" class="btn btn-success btn-busca">Buscar</button>
		</div>
	</form>

</div>
<div style="overflow-x: auto;">
	<div class="relatorio-info">
		<div><strong>Data:</strong> <?php echo date('d/m/Y H:m:i'); ?></div>
		<div><strong>Tipo de Cliente:</strong><?php echo $tipo_cliente_texto ?></div>
	</div>

	<?php
	require_once __DIR__ . "/functions_saldos.php";
	$dados = buscarSaldosDiarios($data_inicial, $data_final, $tipo_cliente);
	echo gerarTabelaClientes($dados, $tipo_cliente);
	//echo json_encode($dados);
	?>
</div>
<?php
require_once $raiz_do_projeto . "backoffice/includes/rodape_bko.php";
?>