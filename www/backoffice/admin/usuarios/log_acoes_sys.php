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

	.ip-toggle-wrap {
		display: flex;
		align-items: center;
		gap: 8px;
		margin-top: 8px;
	}

	.switch-pill {
		position: relative;
		display: inline-block;
		width: 46px;
		height: 24px;
	}

	.switch-pill input {
		opacity: 0;
		width: 0;
		height: 0;
	}

	.switch-pill-slider {
		position: absolute;
		cursor: pointer;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;
		background-color: #d3d3d3;
		transition: .25s;
		border-radius: 24px;
	}

	.switch-pill-slider:before {
		position: absolute;
		content: "";
		height: 18px;
		width: 18px;
		left: 3px;
		bottom: 3px;
		background-color: #fff;
		transition: .25s;
		border-radius: 50%;
	}

	.switch-pill input:checked + .switch-pill-slider {
		background-color: #428bca;
	}

	.switch-pill input:checked + .switch-pill-slider:before {
		transform: translateX(22px);
	}

	.ip-mode-label {
		font-size: 12px;
		color: #444;
		font-weight: 600;
		user-select: none;
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
	<h1 class="titulo-solicitacoes">Ações de usuários do Sys Admin</h1>
	<div id="form" class="form-solicitacoes">
		<div class="container-cancel-pins">
			<div class="col-cancel-pins">
				<label for="usuario_id">Id usuário</label>
				<input type="text" id="usuario_id" class="form-control" inputmode="numeric" pattern="[0-9]*"
					placeholder="Ex: 12345" />
			</div>
			<div class="col-cancel-pins">
				<label for="ip_usuario">IP usuário</label>
				<input type="text" id="ip_usuario" class="form-control" placeholder="Ex: 192.168.0.1" />
				<div class="ip-toggle-wrap">
					<label class="switch-pill" for="ip_mode_switch">
						<input type="checkbox" id="ip_mode_switch">
						<span class="switch-pill-slider"></span>
					</label>
					<span class="ip-mode-label" id="ip_mode_label">IPv4</span>
				</div>
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
				<th>Dados Req</th>
			</tr>
		</thead>
	</table>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script>
	$(document).ready(function () {

		let jsonData = [];
		let ipMode = "ipv4";
		let isShowingDataTableError = false;
		const dtErrorMessage = "Houve um problema ao carregar os dados. Reinicie a página e, caso o erro persista, entre em contato com o suporte.";
		const IPV4_MASK = '099.099.099.099';
		const IPV6_MASK = 'HHHH:HHHH:HHHH:HHHH:HHHH:HHHH:HHHH:HHHH';

		$.fn.dataTable.ext.errMode = 'none';

		function applyIpMask(mode) {
			const ipInput = $('#ip_usuario');
			ipInput.unmask();

			if (mode === "ipv6") {
				ipInput.mask(IPV6_MASK, {
					translation: {
						'H': { pattern: /[0-9a-fA-F]/ }
					}
				});
				ipInput.attr('placeholder', '2001:0db8:85a3:0000:0000:8a2e:0370:7334');
				ipInput.attr('maxlength', 39);
				$('#ip_mode_label').text('IPv6');
			} else {
				ipInput.mask(IPV4_MASK);
				ipInput.attr('placeholder', '192.168.0.1');
				ipInput.attr('maxlength', 15);
				$('#ip_mode_label').text('IPv4');
			}
		}

		function showDataTableError() {
			if (isShowingDataTableError) {
				return;
			}

			isShowingDataTableError = true;
			Swal.fire({
				icon: 'error',
				title: 'Erro ao carregar dados',
				text: dtErrorMessage
			}).finally(function () {
				isShowingDataTableError = false;
			});
		}

		applyIpMask(ipMode);

		$('#ip_mode_switch').on('change', function () {
			ipMode = this.checked ? "ipv6" : "ipv4";
			$('#ip_usuario').val('');
			applyIpMask(ipMode);
		});

		$('#usuario_id').on('input', function () {
			this.value = this.value.replace(/\D/g, '');
		});

		$(".btn-busca").on("click", function () {

			let formulario = $("#form");
			$("#table").css("visibility", "visible");
			let dt_inicial = formulario.find("#dt_inicial");
			let dt_final = formulario.find("#dt_final");
			let usuario_id = formulario.find("#usuario_id");
			let ip_usuario = formulario.find("#ip_usuario");

			let msgError = "";

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

				let ajax = './ajax_log_acoes_sys.php?acao=listar&dt_inicial=' + dt_inicial.val() + '&dt_final=' + dt_final.val() + '&usuario_id=' + usuario_id.val() + '&ip_usuario=' + ip_usuario.val() + '&reload=' + new Date().getTime();

				let table = $('#table').DataTable({
					ajax: {
						url: ajax,
						dataSrc: 'data',
						complete: function () {
							Swal.close();
							let ajaxJson = table.ajax.json();
							jsonData = (ajaxJson && Array.isArray(ajaxJson.data)) ? ajaxJson.data : [];
						},
						error: function (xhr, error, code) {
							Swal.close();
							jsonData = [];
							showDataTableError();
							console.log("Erro na requisição AJAX:");
							console.log("Status: " + xhr.status);
							console.log("Erro: " + error);
							console.log("Código: " + code);
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
						{ data: 'rota_acessada' },
						{ data: 'dados_extras' },
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

				$('#table').off('error.dt').on('error.dt', function () {
					Swal.close();
					showDataTableError();
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
