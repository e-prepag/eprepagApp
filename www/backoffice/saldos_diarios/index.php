<?php
require_once '/www/includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";

?>
<link href="https://cdn.datatables.net/v/dt/dt-1.13.4/datatables.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/v/dt/dt-1.13.5/datatables.min.js"></script>

<style>
	.help-icon {
		position: relative;
		margin-left: 5px;
		cursor: pointer;
		background: #007BFF;
		color: white;
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
		width: 120px;
		bottom: 100%;
		left: 50%;
		margin-left: -60px;
		background-color: rgba(0, 0, 0, 0.9);
		color: #fff;
		text-align: center;
		border-radius: 6px;
		padding: 5px;
		font-weight: bold;

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

	.align-right {
		margin-left: auto;
	}

	.custom-justify {
		display: flex;
		width: 100%;
		flex-wrap: wrap;
		gap: 15px;
	}

	/* Para esconder os botões de incremento e decremento em navegadores baseados em WebKit (Chrome, Safari) */
	input.sem-spin[type="number"]::-webkit-outer-spin-button,
	input.sem-spin[type="number"]::-webkit-inner-spin-button {
		-webkit-appearance: none;
		margin: 0;
	}

	/* Para Firefox também (opcional) */
	input.sem-spin[type="number"] {
		-moz-appearance: textfield;
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
	<h2 class="titulo-vencimento">Risco Merchants - Lista</h2>
	<form id="form1" class="form-solicitacoes">
		<div class="container-cancel-pins">

			<div class="col-cancel-pins">
				<label for="opr_codigo">Código</label>
				<input type="number" id="opr_codigo" name="opr_codigo" class="form-control sem-spin" />
			</div>

			<div class="col-cancel-pins">
				<label for="opr_status">Status</label>
				<select id="opr_status" name="opr_status" class="form-control">
					<option value="2" selected>Todos</option>
					<option value="1">Ativo</option>
					<option value="0">Inativo</option>
				</select>
			</div>

			<div class="col-cancel-pins">
				<label for="opr_risco">Risco</label>
				<select id="opr_risco" name="opr_risco" class="form-control">
					<option value="4" selected>Todos</option>
					<option value="3">Alto</option>
					<option value="2">Médio</option>
					<option value="1">Baixo</option>
					<option value="0">Não possui</option>
				</select>
			</div>

			<div class="col-cancel-pins">
				<label for="dt_inicial">Data inicial
					<span class="help-icon">?
						<span class="tooltiptext">
							Ao preencher, merchants sem análise serão ocultados, pois não possuem data de análise.
							Se não preencher a data final, a busca será da data inicial até hoje.
						</span>
					</span>
				</label>
				<input id="dt_inicial" name="dt_inicial" max="<?php echo date("Y-m-d"); ?>" class="form-control"
					type="date">
			</div>
			<div class="col-cancel-pins">
				<label for="dt_final">Data final
					<span class="help-icon">?
						<span class="tooltiptext">
							Ao preencher, merchants sem análise serão ocultados, pois não possuem data de análise.
							Se não preencher a data inicial, a busca será desde o início até a data final.
						</span>
					</span>
				</label>
				<input id="dt_final" name="dt_final" max="<?php echo date("Y-m-d"); ?>" class="form-control"
					type="date">
			</div>
		</div>
		<div class="d-flex top10 custom-justify">
			<button type="button" class="btn btn-success btn-busca">Buscar</button>
		</div>
	</form>

</div>
<div style="overflow-x: auto;">
	<table id="table" class="display compact hover stripe cell-border"
		style="width:100%;text-align: center;visibility: hidden;">
		<thead>
			<tr>
				<th>Código</th>
				<th>Nome</th>
				<th>CNPJ</th>
				<th>Internacional</th>
				<th>Status</th>
				<th>Risco</th>
				<th>Data Análise</th>
				<th>Observação</th>
				<th>Ação</th>
			</tr>
		</thead>
	</table>
</div>
<script>
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

	var tipo = "";
	var vg_id = "";
	$(document).ready(function () {

		$(".btn-busca").on("click", function () {
			const formulario = $("#form1");

			// Mostra a tabela e inicializa DataTable
			$("#table").css("visibility", "visible");

			const table = $('#table').DataTable({
				ajax: {
					url: './ajax_risco_merchants.php',
					type: 'POST',
					data: function (d) {
						// Convertemos os campos do form para objeto
						const formData = $("#form1").serializeArray();
						formData.forEach(item => {
							d[item.name] = item.value;
						});

						// Adiciona manualmente a ação
						d.acao = 'listar';
						return d;
					},
					dataSrc: function (json) {
						if (json.erro) {
							Swal.fire({
								icon: 'error',
								title: 'Erro ao carregar',
								text: json.erro
							});
							return []; // não popula a tabela
						}
						return json.data || [];
					},
					error: function (xhr) {
						let msg = "Erro inesperado.";
						try {
							const response = JSON.parse(xhr.responseText);
							msg = response.erro || msg;
						} catch (e) {
							msg = xhr.responseText;
						}
						Swal.fire({
							icon: 'error',
							title: 'Erro ao carregar os dados',
							text: msg
						});
					},
					cache: false
				},
				columns: [
					{ data: 'opr_codigo' },
					{ data: 'opr_nome' },
					{ data: 'opr_cnpj' },
					{ data: 'opr_internacional' },
					{ data: 'opr_status' },
					{ data: 'tipo_risco' },
					{ data: 'ultima_data' },
					{ data: 'observacao' },
					{ data: 'acao' },
				],
				destroy: true,
				searching: false,
				language: {
					"zeroRecords": "Não foram encontrados registros",
					"lengthMenu": "Mostrar _MENU_ linhas",
					"info": "Mostrando a página _PAGE_ de _PAGES_",
					"infoEmpty": "Dados inexistentes",
					"infoFiltered": "(filtro aplicado em _MAX_ registros)",
					"paginate": {
						"previous": "Anterior",
						"next": "Próximo"
					}
				}
			});
		});

	});

</script>
<?php
require_once $raiz_do_projeto . "backoffice/includes/rodape_bko.php";
?>