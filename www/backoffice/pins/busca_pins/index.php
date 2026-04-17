<?php
require_once '/www/includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";

$nome_operador = $_SESSION["userlogin_bko"] ?? "";
?>
<link href="https://cdn.datatables.net/v/dt/dt-1.13.4/datatables.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/v/dt/dt-1.13.5/datatables.min.js"></script>

<style>
	.align-right {
		margin-left: auto;
	}

	.custom-justify {
		display: flex;
		width: 100%;
		flex-wrap: wrap;
		gap: 15px;
	}

	/* Para esconder os botões de incremento e decremento no Firefox */
	input[type="number"] {
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

<div class="bottom10">
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
	<h1 class="titulo-solicitacoes" style='font-family: system-ui, -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; font-weight: bold; color: #004E84;'>BUSCAR PINS</h1>
	<p class="txt-cinza"><em>Coloque os pins separados por vírgula, ou coloque-os separados por linha</em></p>
	<form id="form1" class="form-solicitacoes">
		<div class="container-cancel-pins">
			<div class="col-cancel-pins">
				<label for="pin_cod">Cód. pin</label>
				<textarea name="pin_cod" id="pin_cod" class="form-control" rows="5" placeholder="1234, 1234&#10;2345, 34556"></textarea>
			</div>
		</div>
		<div class="d-flex top10 custom-justify">
			<button type="submit" class="btn btn-success btn-busca">Buscar</button>
		</div>
	</form>

</div>
<div style="overflow-x: auto;">
	<table id="table" class="display compact hover stripe cell-border"
		style="width:100%;text-align: center;visibility: hidden;">
		<thead>
			<tr>
				<th>ID Pin</th>
				<th>Código PIN</th>
				<th>Valor (R$)</th>
				<th>Validade</th>
				<th>Status Geral</th>
				<th>Status EPP</th>
				<th>Operadora</th>
				<th>Dt Utilização</th>
				<th>Tipo Usuário</th>
				<th>Nome Usuário</th>
				<th>ID da Venda</th>
				<th>Data Venda</th>
				<th>Produto</th>

			</tr>
		</thead>
	</table>
</div>

<script>
	$(document).ready(function() {

		$("#form1").on("submit", function(e) {
			e.preventDefault();
			const formulario = $("#form1");

			// Mostra a tabela e inicializa DataTable
			$("#table").css("visibility", "visible");

			let pin_cod = formulario.find("#pin_cod");
			let msgError = "";

			// Validação do campo pin_cod
			if (pin_cod.val().trim() === "") {
				msgError += "O campo de códigos PIN não pode estar vazio.<br>";
			} else {
				// Processa a lista (remove quebras de linha e substitui por vírgulas)
				let valores = pin_cod.val()
					.trim()
					.replace(/\r\n|\r|\n/g, ',')
					.split(',')
					.map(item => item.trim())
					.filter(item => item !== '');

				if (valores.length === 0) {
					msgError += "Nenhum código PIN válido foi informado.<br>";
				}
			}

			if (msgError != "") {
				Swal.fire({
					position: 'center',
					icon: 'error',
					title: "Erros encontrados",
					html: msgError,
					showConfirmButton: false,
					timer: 3500
				});
				return;
			}

			const table = $('#table').DataTable({
				//order: [[4, 'asc']],
				ajax: {
					url: './ajax_busca_pins.php',
					type: 'POST',
					data: function(d) {
						// Convertemos os campos do form para objeto
						const formData = $("#form1").serializeArray();
						console.log(formData);
						formData.forEach(item => {
							d[item.name] = item.value;
						});

						// Adiciona manualmente a ação
						d.acao = 'listar';
						return d;
					},
					dataSrc: function(json) {
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
					error: function(xhr) {
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
				columns: [{
						data: 'pin_codinterno'
					},
					{
						data: 'pin_codigo'
					},
					{
						data: 'pin_valor'
					},
					{
						data: 'pin_validade'
					},
					{
						data: 'stat_descricao'
					},
					{
						data: 'pin_epp_status'
					},
					{
						data: 'opr_nome'
					},
					{
						data: 'pin_data_uti'
					},
					{
						data: 'tipo_usuario'
					},
					{
						data: 'nome_usuario'
					},
					{
						data: 'venda_id'
					},
					{
						data: 'data_venda'
					},
					{
						data: 'nome_produto'
					}
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

			// table.on('xhr', function () {
			// });
		});
	});
</script>
<?php
require_once $raiz_do_projeto . "backoffice/includes/rodape_bko.php";
?>