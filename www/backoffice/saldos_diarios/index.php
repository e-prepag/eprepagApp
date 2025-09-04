<?php
require_once '/www/includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL); 

$data_inicial = isset($_GET['dt_inicial']) ? $_GET['dt_inicial'] : "";
$data_final = isset($_GET['dt_final']) ? $_GET['dt_final'] . " 23:59:59" : "";
$data_final_sem_hora = isset($_GET['dt_final']) ? $_GET['dt_final'] : "";
$tipo_cliente = isset($_GET['tipo_cliente']) ? $_GET['tipo_cliente'] : 4;
$data_atual = date('Y-m-d', strtotime('-1 Day'));
$horario_str = isset($_GET['horario_str']) ? $_GET['horario_str'] : 1;

$tipo_cliente_texto = $tipo_cliente == 4 ? 'Todos' : ($tipo_cliente == 3 ? 'PDVs' : ($tipo_cliente == 2 ? 'Gamers' : 'Desconhecido'));
?>
<link href="https://cdn.datatables.net/v/dt/dt-1.13.4/datatables.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/v/dt/dt-1.13.5/datatables.min.js"></script>

<style>
	#data-help-icon {
		left: 50px;

	}
	#data-help-icon::after {
		left: 20px;
	}

	.help-icon {
		position: relative;
		margin-left: 5px;
		cursor: pointer;
		background: rgb(0, 0, 0, 0.1);
		color: #606060;
		border-radius: 50%;
		width: 18px;
		height: 18px;
		text-align: center;
		line-height: 18px;
		font-size: 12px;
		user-select: none;
		display: inline-block;
	}

	.help-icon .tooltiptext {
		visibility: hidden;
		width: 135px;
		bottom: 100%;
		left: 50%;
		margin-left: -60px;
		background-color: rgba(0, 0, 0, 0.9);
		color: #fff;
		text-align: center;
		border-radius: 6px;
		padding: 5px;
		font-weight: bold;
		font-size: 11px;

		/* Position the tooltip */
		position: absolute;
		z-index: 1;
	}

	.help-icon .tooltiptext::after {
		content: " ";
		position: absolute;
		top: 100%;
		/* At the bottom of the tooltip */
		left: 50%;
		margin-left: -5px;
		border-width: 5px;
		border-style: solid;
		border-color: black transparent transparent transparent;
	}

	.help-icon:hover .tooltiptext,
	.tooltiptext.show {
		visibility: visible;
		pointer-events: auto;
	}

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
		justify-content: left;
		flex-wrap: wrap;
		gap: 20px;
		/* Adiciona uma margem entre as colunas */
	}

	/* Colunas (ajuste para uma largura proporcional) */
	.col-cancel-pins {
		flex: 1;
		min-width: 100px;
		margin: 0;
		max-width: 180px;
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
					<option <?php if ($tipo_cliente == 4) echo "selected"; ?> value="4">Todos</option>
					<option <?php if ($tipo_cliente == 3) echo "selected"; ?> value="3">PDVs</option>
					<option <?php if ($tipo_cliente == 2) echo "selected"; ?> value="2">Gamers</option>
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
			<?php if (!empty($data_inicial) && !empty($data_final)) { ?>
				<a class="btn btn-success btn-info"
					href="gerar_csv.php?
					data_inicial=<?= urlencode($data_inicial) ?>
					&data_final=<?= urlencode($data_final_sem_hora) ?>
					&tipo_cliente=<?= urlencode($tipo_cliente) ?>"
					target="_blank">Download</a>
			<?php } ?>
			<button type="submit" class="btn btn-success btn-busca">Buscar</button>
		</div>

	</form>

</div>
<div style="overflow-x: auto; padding-top: 20px;">
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
<script>
	$(document).ready(function() {
		document.querySelectorAll('.help-icon').forEach(icon => {
			icon.addEventListener('click', () => {
				const tooltip = icon.querySelector('.tooltiptext');

				// Remove outros tooltips visíveis
				document.querySelectorAll('.tooltiptext.show').forEach(other => {
					if (other !== tooltip) other.classList.remove('show');
				});

				tooltip.classList.add('show');

				// Remove após 3 segundos
				setTimeout(() => {
					tooltip.classList.remove('show');
				}, 3000);
			});
		});
		$('.container').removeClass('container');
		$('#tabela-clientes').DataTable({
			"ordering": true,
			"paging": false,
			"searching": false,
			"info": false
		});
	});
</script>
<?php
require_once $raiz_do_projeto . "backoffice/includes/rodape_bko.php";
?>