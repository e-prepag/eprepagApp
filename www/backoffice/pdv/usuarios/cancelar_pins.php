<?php
require_once '/www/includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";

$nome_operador = $_SESSION["userlogin_bko"];
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
	input[type="number"]::-webkit-outer-spin-button,
	input[type="number"]::-webkit-inner-spin-button {
		-webkit-appearance: none;
		margin: 0;
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

	.data-input {
		min-width: 200px;
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
	<h1 class="titulo-solicitacoes">Cancelar pins</h1>
	<div id="form" class="form-solicitacoes">
		<div class="container-cancel-pins">
			<div class="col-cancel-pins">
				<label for="pin_cod">Cód. pin</label>
				<input type="number" id="pin_cod" class="form-control" />
			</div>
			<div class="col-cancel-pins">
				<label for="id_pedido">Num. Pedido</label>
				<input type="number" id="id_pedido" class="form-control" />
			</div>
			<div class="col-cancel-pins">
				<label for="id_pdv">ID do PDV</label>
				<input type="number" id="id_pdv" class="form-control" />
			</div>
			<div class="col-cancel-pins data-input">
				<label for="tp_solicitacao">Data inicial</label>
				<input value="<?php echo date('Y-m-d', strtotime('-1 day')) . 'T00:00'; ?>" id="dt_inicial"
					max="<?php echo date("Y-m-d"); ?>" class="form-control" type="datetime-local">
			</div>
			<div class="col-cancel-pins data-input">
				<label for="tp_solicitacao">Data final</label>
				<input value="<?php echo date('Y-m-d') . 'T23:59'; ?>" id="dt_final" max="<?php echo date("Y-m-d"); ?>"
					class="form-control" type="datetime-local">
			</div>
		</div>
		<div class="d-flex top10 custom-justify">
			<button type="button" class="btn btn-success btn-busca">Buscar</button>
			<button type="button" class="btn btn-danger btn-todos align-right d-none">Cancelar Todos Pins</button>
		</div>
	</div>

</div>
<div style="overflow-x: auto;">
	<table id="table" class="display compact hover stripe cell-border"
		style="width:100%;text-align: center;visibility: hidden;">
		<thead>
			<tr>
				<th>Ações</th>
				<th>Id</th>
				<th>Valor</th>
				<th>PIN</th>
				<th>Operadora</th>
				<th>Data Venda</th>
				<th>Status</th>
				<th>Nome PDV</th>
			</tr>
		</thead>
	</table>
</div>

<script>
	$(document).ready(function () {


		$(".btn-busca").on("click", function () {

			let formulario = $("#form");
			$("#table").css("visibility", "visible");
			let idPDV = formulario.find("#id_pdv");
			let dt_inicial = formulario.find("#dt_inicial");
			let dt_final = formulario.find("#dt_final");
			let id_pedido = formulario.find("#id_pedido");
			let pin_cod = formulario.find("#pin_cod");

			let msgError = "";

			console.log(`${pin_cod.val()} ${id_pedido.val()}`)

			if (id_pedido.val() == "" && pin_cod.val() == "" && idPDV.val() == "") {

				msgError += "Você deve escolher um PDV, número do pedido ou código do pin<br>";

			}
			else if(id_pedido.val() && pin_cod.val())
			{
				msgError += "Você só pode escolher número do pedido ou código do pin<br>";
			}
			else {

				if (dt_inicial.val() == "") {
					msgError += "Você deve escolher uma data inicial<br>";
				}
				if (dt_final.val() == "") {
					msgError += "Você deve escolher uma data final<br>";
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
			} else {

				let table = $('#table').DataTable({
					ajax: './ajax_cancela_pins.php?acao=listar&dt_inicial=' + dt_inicial.val() + '&pin_cod=' + pin_cod.val() + '&dt_final=' + dt_final.val() + '&id_pdv=' + idPDV.val() + '&id_pedido=' + id_pedido.val() + '&reload=' + new Date().getTime(),
					cache: false,
					dataSrc: '',
					columns: [
						{ data: 'acoes' },
						{ data: 'pin_codinterno' },
						{ data: 'pin_valor' },
						{ data: 'pin_codigo' },
						{ data: 'opr_nome' },
						{ data: 'vg_data_inclusao' },
						{ data: 'stat_descricao' },
						{ data: 'ug_login' }
					],
					destroy: true,
					language: {
						"zeroRecords": "Não foram encontrados registros",
						"lengthMenu": "Mostrar _MENU_ linhas",
						"info": "Mostrando a página _PAGE_ de _PAGES_",
						"infoEmpty": "Dados inexistentes",
						"infoFiltered": "(filtro aplicado em _MAX_ registros)",
						"sSearch": "Pesquisar",
						"paginate": {
							"previous": "Anterior",
							"next": "Próximo"
						}
					}
				});

				table.on('xhr', function () {
					let data = table.ajax.json().data; // Acessa os dados retornados
					if (data && data.length > 0) {
						$('.btn-todos').removeClass("d-none"); // Mostra o botão se houver registros
					} else {
						$('.btn-todos').addClass("d-none"); // Esconde o botão se não houver registros
					}
				});

			}

		});

		$(document).on("click", ".btn-negar", function (e) {

			let button = $(e.currentTarget);
			$.ajax({
				url: "./ajax_cancela_pins.php",
				method: "POST",
				data: { acao: "unico", pin: button.data().codigo, idpdv: button.data().idpdv, login: '<?php echo $nome_operador; ?>', nome: '<?php echo $nome_operador; ?>' },
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
					let icone = "";
					if (data.includes("Ação realizada com sucesso")) {
						icone = "success";
					} else {
						icone = "error";
					}

					Swal.fire({
						position: 'center',
						icon: icone,
						title: data,
						showConfirmButton: false,
						timer: 3000
					});

				}
			});

			$('#table').DataTable().ajax.reload();
		});

		$(document).on("click", ".btn-todos", function (e) {
			Swal.fire({
				title: 'Tem certeza?',
				text: "Essa ação não poderá ser desfeita!",
				icon: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				confirmButtonText: 'Sim, apagar',
				cancelButtonText: 'Cancelar'
			}).then((result) => {
				if (result.isConfirmed) {
					let formulario = $("#form");
					let idPDV = formulario.find("#id_pdv");
					let dt_inicial = formulario.find("#dt_inicial");
					let dt_final = formulario.find("#dt_final");
					let msgError = "";

					if (idPDV.val() == "") {
						msgError += "Você deve escolher um PDV<br>";
					}

					if (dt_inicial.val() == "") {
						msgError += "Você deve escolher uma data inicial<br>";
					}

					if (dt_final.val() == "") {
						msgError += "Você deve escolher uma data final<br>";
					}

					if (msgError != "") {
						Swal.fire({
							position: 'center',
							icon: 'error',
							title: "Erros encotrados",
							html: msgError,
							showConfirmButton: false,
							timer: 3500
						});
					}
					else {
						$.ajax({
							url: "./ajax_cancela_pins.php",
							method: "POST",
							data: { acao: "todos", dt_inicial: dt_inicial.val(), dt_final: dt_final.val(), id_pdv: idPDV.val(), login: '<?php echo $nome_operador; ?>', nome: '<?php echo $nome_operador; ?>' },
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
								let icone = "";
								if (data.includes("Ação realizada com sucesso")) {
									icone = "success";
								} else {
									icone = "error";
								}

								Swal.fire({
									position: 'center',
									icon: icone,
									title: data,
									showConfirmButton: false,
									timer: 3000
								});

							}
						});

						$('#table').DataTable().ajax.reload();
					}
				}
			});

		});
	});

</script>
<?php
require_once $raiz_do_projeto . "backoffice/includes/rodape_bko.php";
?>