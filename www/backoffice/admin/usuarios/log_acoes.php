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
	<h1 class="titulo-solicitacoes">Log de ações de usuários</h1>
	<div id="form" class="form-solicitacoes">
		<div class="container-cancel-pins">
			<div class="col-cancel-pins">
				<label for="usuario_id">Id usuário</label>
				<input type="number" id="usuario_id" class="form-control" />
			</div>
			<div class="col-cancel-pins">
				<label for="tipo_usuario">Tipo usuário</label>
				<select id="tipo_usuario" class="form-control">
					<option value="">Todos</option>
					<option value="1">Usuário PDV</option>
					<option value="2">Usuário Gamer</option>
					<option value="3">Sem login</option>
				</select>
			</div>
			<div class="col-cancel-pins">
				<label for="ip_usuario">IP usuário</label>
				<input type="text" id="ip_usuario" class="form-control" />
			</div>
			<div class="col-cancel-pins data-input">
				<label for="dt_inicial">Data inicial</label>
				<input value="<?php echo date('Y-m-d', strtotime('-1 day')) . 'T00:00'; ?>" id="dt_inicial"
					max="<?php echo date("Y-m-d"); ?>" class="form-control" type="datetime-local">
			</div>
			<div class="col-cancel-pins data-input">
				<label for="dt_final">Data final</label>
				<input value="<?php echo date('Y-m-d') . 'T23:59'; ?>" id="dt_final" max="<?php echo date("Y-m-d"); ?>"
					class="form-control" type="datetime-local">
			</div>
		</div>
		<div class="d-flex top10 custom-justify">
			<button type="button" class="btn btn-success btn-busca">Buscar</button>
			<button id="exportCSV" type="button" class="btn btn-info">Baixar CSV</button>
		</div>
	</div>

</div>
<div style="overflow-x: auto;">
	<table id="table" class="display compact hover stripe cell-border"
		style="width:100%;text-align: center;visibility: hidden;">
		<thead>
			<tr>
				<th>Usuário ID</th>
				<th>Tipo usuário</th>
				<th>Data Registro</th>
				<th>IP usuário</th>
				<th>URL página</th>
			</tr>
		</thead>
	</table>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script>
	$(document).ready(function () {

		let jsonData = [];

		$('#ip_usuario').mask('099.099.099.099');

		$(".btn-busca").on("click", function () {

			let formulario = $("#form");
			$("#table").css("visibility", "visible");
			let tipo_usuario = formulario.find("#tipo_usuario");
			let dt_inicial = formulario.find("#dt_inicial");
			let dt_final = formulario.find("#dt_final");
			let usuario_id = formulario.find("#usuario_id");
			let ip_usuario = formulario.find("#ip_usuario");

			let msgError = "";

			if (dt_inicial.val() == "" && id_pedido.val() == "") {
				msgError += "Você deve escolher uma data inicial<br>";
			}
			if (dt_final.val() == "" && id_pedido.val() == "") {
				msgError += "Você deve escolher uma data final<br>";
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

				Swal.fire({
					title: 'Carregando...',
					html: 'Aguarde enquanto os dados estão sendo carregados.', // Mensagem de carregamento
					allowOutsideClick: false,  // Impede o fechamento do alerta clicando fora
					showConfirmButton: false,
					willOpen: () => {
						Swal.showLoading(); // Exibe o ícone de carregamento
					}
				});

				let ajax = './ajax_log_acoes.php?acao=listar&dt_inicial=' + dt_inicial.val() + '&dt_final=' + dt_final.val() + '&usuario_id=' + usuario_id.val() + '&ip_usuario=' + ip_usuario.val() + '&reload=' + new Date().getTime() + '&tipo_usuario=' + tipo_usuario.val();

				let table = $('#table').DataTable({
					ajax: {
						url: ajax,
						dataSrc: 'data',
						complete: function () {
							Swal.close();
							jsonData = table.ajax.json().data;
						}
					},
					cache: false,
					dataSrc: 'data',
					order: [[2, 'desc']],
					columns: [
						{ data: 'usuario_id' },
						{ data: 'tipo_usuario' },
						{ data: 'data_hora_registro' },
						{ data: 'ip_usuario' },
						{ data: 'caminho_arquivo' },
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
					},
					error: function (xhr, error, code) {
						console.log("Erro na requisição AJAX:");
						console.log("Status: " + xhr.status);
						console.log("Erro: " + error);
						console.log("Código: " + code);
					}
				});

			}

		});

		// Função para converter JSON em CSV
		function convertToCSV(data) {
			if (data.length === 0) return '';

			const headers = Object.keys(data[0]).join(',') + '\n';
			const rows = data.map(row => Object.values(row).map(value => `"${value}"`).join(',')).join('\n');

			return headers + rows;
		}

		function getCurrentTimestamp() {
			let now = new Date();
			let dd = String(now.getDate()).padStart(2, '0');
			let mm = String(now.getMonth() + 1).padStart(2, '0');
			let yy = String(now.getFullYear()).slice(-2);
			let hh = String(now.getHours()).padStart(2, '0');
			let min = String(now.getMinutes()).padStart(2, '0');

			return `${dd}${mm}${yy}_${hh}${min}`;
		}

		// Evento para exportar CSV
		$("#exportCSV").on("click", function () {
			if (jsonData.length === 0) {
				Swal.fire("Aviso!", "Nenhum dado para exportar.", "warning");
				return;
			}

			let csv = convertToCSV(jsonData);
			let blob = new Blob([csv], { type: "text/csv;charset=utf-8;" });
			let url = URL.createObjectURL(blob);
			let link = document.createElement("a");
			let fileName = `log_acoes_${getCurrentTimestamp()}.csv`;

			link.setAttribute("href", url);
			link.setAttribute("download", fileName);
			document.body.appendChild(link);
			link.click();
			document.body.removeChild(link);
		});

	});

</script>
<?php
require_once $raiz_do_projeto . "backoffice/includes/rodape_bko.php";
?>