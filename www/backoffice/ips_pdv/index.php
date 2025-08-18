<?php
require_once '/www/includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo_teste.php";

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
			<li><a href="#" class="muda-aba" ordem="<?php //echo $currentAba->getOrdem(); ?>">BackOffice -
					<?php //echo $currentAba->getDescricao(); ?></a></li>
			<li class="active"><?php //echo $sistema->menu[0]->getDescricao(); ?></li>
			<li class="active"><a
					href="<?php //echo $sistema->item->getLink(); ?>"><?php //echo $sistema->item->getDescricao(); ?></a>
			</li>
		</ol>
	</div>
	<h2 class="titulo-vencimento">Risco Merchants - Lista</h2>
	<form id="form1" class="form-solicitacoes">
		<div class="container-cancel-pins">

			<div class="col-cancel-pins">
				<label for="opr_codigo">ID ou nome PDV<span class="help-icon">?
						<span class="tooltiptext">
							O nome não precisa ser completo, ele buscará nomes que contenham o texto informado.
							Por exemplo, se você digitar "jo", ele encontrará "josé" e "joão".
						</span>
					</span></label>
				<input type="text" id="usuario_id" name="usuario_id" class="form-control" />
			</div>
			<div class="col-cancel-pins">
				<label for="ip_pdv">IP do PDV<span class="help-icon">?
						<span class="tooltiptext">
							Só buscam PDVs que possuam IP. Caso o PDV possua um range de IPs, qualquer IP inserido, se tiver no range, será encontrado.
						</span>
					</span></label>
				<input type="text" id="ip_pdv" name="ip_pdv" class="form-control" />
			</div>
		</div>
		<div class="d-flex top10 custom-justify">
			<button type="submit" class="btn btn-success btn-busca">Buscar</button>
		</div>
		<div class="d-flex top10 custom-justify">
			<a class="btn btn-success btn-busca">Novo</a>
		</div>
	</form>

</div>
<div style="overflow-x: auto;">
	<table id="table" class="display compact hover stripe cell-border"
		style="width:100%;text-align: center;visibility: hidden;">
		<thead>
			<tr>
				<th>Id PDV</th>
				<th>Nome</th>
				<th>Endereço IP</th>
				<th>Ação</th>
			</tr>
		</thead>
	</table>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
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

		$('#ip_pdv').mask('099.099.099.099');

		$("#form1").on("submit", function (e) {
			e.preventDefault();
			const formulario = $("#form1");

			// Mostra a tabela e inicializa DataTable
			$("#table").css("visibility", "visible");

			const table = $('#table').DataTable({
				ajax: {
					url: './ajax_ip_pdv.php',
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
					{ data: 'ug_id' },
					{ data: 'ug_nome' },
					{ data: 'ip_pdv' },
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