<?php
require_once '/www/includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";

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
	<h2 class="titulo-vencimento">Aumentar validade de pins</h2>
	<div id="form" class="form-solicitacoes">
		<div class="container-cancel-pins">
			<div class="col-cancel-pins">
				<label for="tipo_pesquisa">Tipo</label>
				<select id="tipo_pesquisa" name="tipo_pesquisa" class="form-control">
					<option value="0" selected>Código do Pin</option>
					<option value="1">Número da Venda (PDV)</option>
					<option value="2">Número da Venda (Gamer)</option>
				</select>
			</div>
			<div class="col-cancel-pins">
				<label for="campo_pesquisa">Código do Pin</label>
				<input type="number" id="campo_pesquisa" name="campo_pesquisa" class="form-control sem-spin" />
			</div>

			<div class="form-group">
				<label for="nova_validade">Nova Validade (Dias)</label>
				<input type="number" id="nova_validade" disabled name="nova_validade" class="form-control" value="60"
					min="1">
			</div>
		</div>
		<div class="d-flex top10 custom-justify">
			<button type="button" class="btn btn-success btn-busca">Buscar</button>
			<button style="font-weight: bold;" type="button" class="btn btn-info btn-todos align-right d-none">Aumentar
				p/ Todos</button>
		</div>
	</div>

</div>
<div style="overflow-x: auto;">
	<table id="table" class="display compact hover stripe cell-border"
		style="width:100%;text-align: center;visibility: hidden;">
		<thead>
			<tr>
				<th>Ação</th>
				<th>Id</th>
				<th>Valor</th>
				<th>PIN</th>
				<th>Operadora</th>
				<th>Data Validade</th>
				<th>Status</th>
				<th>Já Alterada?</th>
			</tr>
		</thead>
	</table>
</div>
<script>
	document.addEventListener("DOMContentLoaded", function () {
		const select = document.getElementById("tipo_pesquisa");
		const label = document.querySelector("label[for='campo_pesquisa']");

		const titulos = {
			0: "Código do Pin",
			1: "Número da Venda (PDV)",
			2: "Número da Venda (Gamer)"
		};

		select.addEventListener("change", function () {
			const selectedValue = select.value;
			label.textContent = titulos[selectedValue] || "Valor";
		});
	});
</script>
<script>
	var tipo = "";
	var vg_id = "";
	$(document).ready(function () {

		$(".btn-busca").on("click", function () {

			let formulario = $("#form");
			$("#table").css("visibility", "visible");
			let campo_pesquisa = formulario.find("#campo_pesquisa").val();
			let tipo_pesquisa = +formulario.find("#tipo_pesquisa").val();

			let msgError = "";

			if (tipo_pesquisa > 2 || tipo_pesquisa < 0) {
				msgError += "Tipo de pesquisa inválido<br>";
			}
			if (campo_pesquisa.trim() == "") {
				msgError += "Digite o código<br>";
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
			} else {
				if (tipo_pesquisa > 0) {
					tipo = tipo_pesquisa == 1 ? "pdv" : "gamer";
					vg_id = campo_pesquisa;
				}

				let table = $('#table').DataTable({
					ajax: './ajax_validade_pins.php?acao=listar&tipo_pesquisa=' + tipo_pesquisa +
						'&campo_pesquisa=' + campo_pesquisa +
						'&reload=' + new Date().getTime(),
					cache: false,
					dataSrc: '',
					searching: false,
					columns: [
						{ data: 'acoes' },
						{ data: 'pin_codinterno' },
						{ data: 'pin_valor' },
						{ data: 'pin_codigo' },
						{ data: 'opr_nome' },
						{ data: 'pin_validade' },
						{ data: 'stat_descricao' },
						{ data: 'pin_vencimento' }
					],
					destroy: true,
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

				table.on('xhr', function () {
					let data = table.ajax.json().data; // Acessa os dados retornados
					$("#nova_validade").prop("disabled", !(data && data.length > 0));
					$(".btn-todos").toggleClass("d-none", !(data && data.length > 1));
				});

			}

		});

		$(document).on("click", ".btn-negar", function (e) {

			let button = $(e.currentTarget);

			const nova_validade = $("#nova_validade").val();
			let mensagemErro = "";

			if (!nova_validade) {
				mensagemErro = "Nova validade não informada";
			} else {
				if (isNaN(nova_validade)) {
					mensagemErro = "Data de validade inválida";
				} else if (nova_validade <= 0) {
					mensagemErro = "A nova validade deve ser maior do que 0";
				}
			}

			if (mensagemErro) {
				Swal.fire({
					position: 'center',
					icon: 'error',
					title: mensagemErro,
					showConfirmButton: false,
					timer: 3500
				});
			} else {

				$.ajax({
					url: "./ajax_validade_pins.php",
					method: "POST",
					data: { acao: "unico", pin: button.data().codigo, nova_validade: nova_validade },
					beforeSend: function () {
						Swal.fire({
							title: 'Processo em andamento!',
							html: 'Processando a solicitação',
							timerProgressBar: true,
							didOpen: () => {
								Swal.showLoading()
							}
						});
					},
					success: function (data) {
						Swal.close();
						let mensagem = "";
						let icone = "";
						if (+data == 1) {
							icone = "success";
							mensagem = "Validade alterada com sucesso!";
						} else {
							icone = "error";
							mensagem = data;
						}

						Swal.fire({
							position: 'center',
							icon: icone,
							title: "Finalizado",
							html: mensagem,
							showConfirmButton: false,
							timer: 3000
						});

					},
					error: function (xhr, status, error) {
						Swal.close();
						Swal.fire({
							icon: 'error',
							title: 'Erro ao processar requisição',
						});
					}
				});
				setTimeout(function () {
				}, 100);
				$('#table').DataTable().ajax.reload();
			}
		});

		$(document).on("click", ".btn-todos", function (e) {
			const nova_validade = $("#nova_validade").val();
			let mensagemErro = "";

			if (!nova_validade) {
				mensagemErro = "Nova validade não informada";
			} else {
				if (isNaN(nova_validade)) {
					mensagemErro = "Data de validade inválida";
				} else if (nova_validade <= 0) {
					mensagemErro = "A nova validade deve ser maior do que 0";
				}
			}

			if (tipo == "" || vg_id == "") {
				mensagemErro = "Selecione um tipo de pesquisa, informe o código e clique em buscar";
			}

			if (mensagemErro) {
				Swal.fire({
					position: 'center',
					icon: 'error',
					title: mensagemErro,
					showConfirmButton: false,
					timer: 3500
				});
			} else {

				Swal.fire({
					title: 'Confirmação',
					text: "Deseja alterar para todoas?",
					icon: 'warning',
					showCancelButton: true,
					confirmButtonColor: '#3085d6',
					cancelButtonColor: '#d33',
					confirmButtonText: 'Sim, alterar todos',
					cancelButtonText: 'Cancelar ação'
				}).then((result) => {
					if (result.isConfirmed) {

						$.ajax({
							url: "./ajax_validade_pins.php",
							method: "POST",
							data: { acao: "todos", vg_id: vg_id, tipo: tipo, nova_validade: nova_validade },
							beforeSend: function () {
								Swal.fire({
									title: 'Processo em andamento!',
									html: 'Processando a solicitação',
									timerProgressBar: true,
									didOpen: () => {
										Swal.showLoading()
									}
								});
							},
							success: function (data) {
								Swal.close();
								let mensagem = "";
								let icone = "";
								if (+data == "1") {
									icone = "success";
									mensagem = "Validades alteradas com sucesso!";
								} else {
									icone = "error";
									mensagem = data;
								}

								Swal.fire({
									position: 'center',
									icon: icone,
									title: "Finalizado",
									html: mensagem,
									showConfirmButton: false,
									timer: 3000
								});

							},
							error: function (xhr, status, error) {
								Swal.close();
								Swal.fire({
									icon: 'error',
									title: 'Erro ao processar requisição',
								});
							}
						});
						setTimeout(function () {
						}, 2000);
						$('#table').DataTable().ajax.reload();

					}
				});
			}

		});
	});

</script>
<?php
require_once $raiz_do_projeto . "backoffice/includes/rodape_bko.php";
?>