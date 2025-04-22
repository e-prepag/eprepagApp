<?php
require_once '/www/includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";

$nome_operador = $_SESSION["userlogin_bko"];
?>
<link href="https://cdn.datatables.net/v/dt/dt-1.13.4/datatables.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/v/dt/dt-1.13.5/datatables.min.js"></script>

<style type="text/css">
	td {
		word-wrap: break-word;
		/* Quebra o texto quando necessário */
		white-space: normal;
		/* Permite múltiplas linhas */
		max-width: 150px;
		/* Define o tamanho máximo */
		line-height: 1.8; /* Aumenta o espaço entre as linhas */
		max-height: 180px; /* Define altura máxima */
    	overflow: hidden; /* Oculta o conteúdo excedente */
    	text-overflow: ellipsis; /* Adiciona reticências ao texto excedente */
	}
</style>

<div class="bottom10">
	<h1 class="titulo-solicitacoes">Solicitações</h1>
	<form id="form" class="form-solicitacoes">
		<div class="container-solicitacoes">
			<div class="col-solicitacoes">
				<label for="tp_solicitacao">Tipo solicitação</label>
				<select id="tp_solicitacao" class="form-control">
					<option value="">Selecione um tipo</option>
					<option value="0">Adicionar saldo</option>
					<option value="2">Subtrair saldo</option>
					<option value="1">Zerar saldo</option>
				</select>
			</div>
			<div class="col-solicitacoes">
				<label for="tp_solicitacao">Data inicial</label>
				<input value="" id="dt_inicial" max="<?php echo date("Y-m-d"); ?>" class="form-control" type="date">
			</div>
			<div class="col-solicitacoes">
				<label for="tp_solicitacao">Data final</label>
				<input value="" id="dt_final" max="<?php echo date("Y-m-d"); ?>" class="form-control" type="date">
			</div>
		</div>
		<button type="button" class="btn btn-success top10 btn-busca">Buscar</button>
	</form>
</div>

<table id="table" class="display compact hover stripe cell-border"
	style="width:100%;text-align: center;visibility: hidden;">
	<thead>
		<tr>
			<th>Data da requisição</th>
			<th>Valor</th>
			<th>Tipo</th>
			<th>Operador</th>
			<th>Pdv</th>
			<th>Justificativa</th>
			<th>Ações</th>
		</tr>
	</thead>
</table>

<script>
	$(document).ready(function () {

		$(".btn-busca").on("click", function () {

			let formulario = $("#form");
			$("#table").css("visibility", "visible");
			let tipo = formulario.find("#tp_solicitacao");
			let dt_inicial = formulario.find("#dt_inicial");
			let dt_final = formulario.find("#dt_final");
			let msgError = "";

			if (tipo.val() == "") {
				msgError += "Você deve escolher um tipo de solicitação<br>";
			}

			if (dt_inicial.val() == "") {
				msgError += "Você deve escolher uma data inicial<br>";
			}

			if (dt_final.val() == "") {
				msgError += "Você deve escolher uma data final<br>";
			}

			if (msgError != "") {
				Swal.fire({
					position: 'top-end',
					icon: 'error',
					title: "Erros encotrados",
					html: msgError,
					showConfirmButton: false,
					timer: 3500
				});
			} else {

				let table = $('#table').DataTable({
					ajax: './ajax_solicitacoes.php?acao=listar&dt_inicial=' + dt_inicial.val() + '&dt_final=' + dt_final.val() + '&tipo=' + tipo.val(),
					columns: [
						{ data: 'data_operacao' },
						{ data: 'est_valor' },
						{ data: 'est_tipo' },
						{ data: 'shn_login' },
						{ data: 'ug_login' },
						{ data: 'ug_descricao' },
						{ data: 'acoes' }
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
							"next": "Próximo",
						}
					},
					createdRow: function (row, data, dataIndex) {
						$('td', row).eq(5).attr('title', data.ug_descricao); // Define o tooltip para a última coluna
					}
				});

			}

		});

		$(document).on("click", ".btn-aprovar", function (e) {

			let button = $(e.currentTarget);
			$.ajax({
				url: "./ajax_solicitacoes.php",
				method: "POST",
				data: { acao: "aprovar", codigo: button.data().codigo, login: button.data().login, nome: '<?php echo $nome_operador; ?>' },
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
					if (data.trim() == "Ação realizada com sucesso") {
						icone = "success";
					} else {
						icone = "error";
					}

					Swal.fire({
						position: 'top-end',
						icon: icone,
						title: data,
						showConfirmButton: false,
						timer: 3000
					});

				}
			});

			$('#table').DataTable().ajax.reload();
		});

		$(document).on("click", ".btn-negar", function (e) {

			let button = $(e.currentTarget);
			$.ajax({
				url: "./ajax_solicitacoes.php",
				method: "POST",
				data: { acao: "negar", codigo: button.data().codigo, login: button.data().login, nome: '<?php echo $nome_operador; ?>' },
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
					if (data.trim() == "Ação realizada com sucesso") {
						icone = "success";
					} else {
						icone = "error";
					}

					Swal.fire({
						position: 'top-end',
						icon: icone,
						title: data,
						showConfirmButton: false,
						timer: 3000
					});
				}
			});

			$('#table').DataTable().ajax.reload();
		});


	});
</script>
<?php
require_once $raiz_do_projeto . "backoffice/includes/rodape_bko.php";
?>