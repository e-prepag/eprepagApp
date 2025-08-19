<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
require_once '/www/includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";

$conexao = ConnectionPDO::getConnection()->getLink();

$sql = "SELECT ug_nome_fantasia, ug_id
				FROM dist_usuarios_games
				WHERE ug_id = :user_id
		";

$user_id = isset($_REQUEST['ug_id']) ? $_REQUEST['ug_id'] : 0;

if ($user_id <= 0) {
	die("Id do usuário inválido.");
}

$stmt = $conexao->prepare($sql);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
	die("Usuário não encontrado.");
}

$sqlIp = "SELECT id, ip_address,
				    ip_range,
				    ip_range_ini,
				    ip_range_end
					FROM pdv_api_ip
					WHERE ug_id = :user_id";
$stmtIp = $conexao->prepare($sqlIp);
$stmtIp->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmtIp->execute();
$Ip = $stmtIp->fetchAll(PDO::FETCH_ASSOC);
if (!$Ip) {
	$Ip = [];
}

?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/js/jquery.mask.min.js"></script>
<style>
	.titulo-vencimento {
		font-weight: bold;
		color: #333333;
		font-family: system-ui, -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
		text-align: left;
		margin-left: 10px;
		padding-bottom: 5px;
	}

	.custom-justify {
		display: flex;
		width: 100%;
		flex-wrap: wrap;
		gap: 15px;
	}

	.align-right {
		margin-left: auto;
		height: 34px;
	}

	.card {
		background: rgba(255, 255, 255, 0.95);
		border-radius: 20px;
		padding: 30px;
		box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
		backdrop-filter: blur(10px);
	}

	.modal-title-alt {
		color: #555;
		margin-bottom: 20px;
		font-size: 34px;
	}

	.form-group {
		margin-bottom: 20px;
	}

	label {
		display: block;
		margin-bottom: 8px;
		font-weight: 600;
		color: #555;
	}

	input,
	select {
		width: 100%;
		padding: 12px 16px;
		border: 2px solid #e1e5e9;
		border-radius: 10px;
		font-size: 16px;
		transition: all 0.3s ease;
		background: white;
	}

	input:focus,
	select:focus {
		outline: none;
		border-color: #667eea;
		box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
	}

	.ip-type-selector {
		display: flex;
		gap: 15px;
		margin-bottom: 20px;
	}

	.radio-group {
		display: flex;
		align-items: center;
		gap: 8px;
		padding: 10px 15px;
		border: 2px solid #e1e5e9;
		border-radius: 10px;
		cursor: pointer;
		transition: all 0.3s ease;
	}

	.radio-group:hover {
		border-color: #667eea;
		background: rgba(102, 126, 234, 0.05);
	}

	.radio-group input[type="radio"] {
		width: auto;
		margin: 0;
	}

	.radio-group.active {
		border-color: #667eea;
		background: rgba(102, 126, 234, 0.1);
	}

	.ip-input-group {
		display: flex;
		gap: 10px;
		align-items: end;
	}

	.ip-input-group input {
		flex: 1;
	}

	.range-inputs {
		display: none;
		gap: 10px;
	}

	.range-inputs.active {
		display: flex;
	}

	.range-separator {
		padding: 12px 0;
		font-weight: bold;
		color: #667eea;
		text-align: center;
		min-width: 30px;
	}

	.button-modal {
		color: white;
		border: none;
		padding: 10px 16px;
		border-radius: 10px;
		font-size: 16px;
		font-weight: 600;
		cursor: pointer;
		transition: all 0.3s ease;
		min-width: 120px;
	}

	.btn-modal-submit {
		background: linear-gradient(135deg, #28a745, #218838);
	}

	.btn-modal-close {
		background: linear-gradient(135deg, #dc3545, #c82333);
	}

	.button-modal:hover {
		transform: translateY(-2px);
		box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
	}

	.btn-modal-group {
		display: flex;
		gap: 20px;
		flex-wrap: wrap;
	}

	@media (max-width: 768px) {

		.ip-type-selector {
			flex-direction: column;
		}

		.range-inputs {
			flex-direction: column;
		}
	}

	.error {
		color: #dc3545;
		font-size: 14px;
		margin-top: 5px;
	}

	.success {
		color: #28a745;
		font-size: 14px;
		margin-top: 5px;
	}
</style>
<script>
	document.addEventListener("DOMContentLoaded", function() {
		const modal = document.getElementById("modal-novo");
		if (modal && modal.parentNode !== document.body) {
			document.body.appendChild(modal);
		}
	});

	function validateIP(ip) {
		const regex = /^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;
		return regex.test(ip);
	}

	function clearMessages() {
		document.getElementById('ipError').textContent = '';
	}

	function selectIpType(type) {
		const radioGroups = document.querySelectorAll('.radio-group');
		const rangeInputs = document.getElementById('rangeInputs');
		const singleIp = document.getElementById('singleIp');
		const ipLabel = document.getElementById('ipLabel');

		radioGroups.forEach(group => group.classList.remove('active'));

		if (type === 'single') {
			radioGroups[0].classList.add('active');
			rangeInputs.classList.remove('active');
			singleIp.style.display = 'block';
			ipLabel.textContent = 'Endereço IP';
			document.querySelector('input[name="ipType"][value="single"]').checked = true;
		} else {
			radioGroups[1].classList.add('active');
			rangeInputs.classList.add('active');
			singleIp.style.display = 'none';
			ipLabel.textContent = 'Range de IPs';
			document.querySelector('input[name="ipType"][value="range"]').checked = true;
		}
		clearMessages();
	}

	function validaForm(ipType) {
		clearMessages();
		if (ipType === 'single') {
			const ip = document.getElementById('singleIp').value.trim();
			if (!ip) {
				document.getElementById('ipError').textContent = 'Digite um endereço IP válido.';
				return false;
			}
			if (!validateIP(ip)) {
				document.getElementById('ipError').textContent = 'Formato de IP inválido.';
				return false;
			}

		} else {
			const startIp = document.getElementById('startIp').value.trim();
			const endIp = document.getElementById('endIp').value.trim();

			if (!startIp || !endIp) {
				document.getElementById('ipError').textContent = 'Digite ambos os IPs do range.';
				return false;
			}
			if (!validateIP(startIp) || !validateIP(endIp)) {
				document.getElementById('ipError').textContent = 'Formato de IP inválido.';
				return false;
			}

		}
		return true;
	}

	function removerIp(id) {
		Swal.fire({
			title: 'Tem certeza?',
			text: "Deseja realmente remover este endereço IP?",
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#d33',
			cancelButtonColor: '#3085d6',
			confirmButtonText: 'Sim, remover',
			cancelButtonText: 'Cancelar'
		}).then((result) => {
			if (result.isConfirmed) {
				Swal.fire({
					title: 'Removendo...',
					html: 'Aguarde enquanto removemos o IP.',
					allowOutsideClick: false,
					didOpen: () => {
						Swal.showLoading();
					}
				});
				$.ajax({
					url: "ajax_ip_pdv.php",
					method: "POST",
					data: {
						acao: "remover",
						id: id
					},
					success: function(data) {
						let resposta;
						try {
							resposta = typeof data === "string" ? JSON.parse(data) : data;
						} catch (e) {
							resposta = {};
						}
						if (resposta.status === "success") {
							Swal.fire({
								icon: "success",
								title: "Removido!",
								text: resposta.msg
							});
							// Remove a linha da tabela
							$("#remover" + id).closest("tr").remove();
						} else {
							Swal.fire({
								icon: "error",
								title: "Erro",
								text: resposta.msg || "Erro ao remover o endereço IP."
							});
						}
					},
					error: function() {
						Swal.fire({
							icon: "error",
							title: "Erro",
							text: "Erro ao remover o endereço IP."
						});
					}
				});
			}
		});
	}

	// Delegação de evento para os botões de remover (caso sejam adicionados dinamicamente)
	$(document).on('click', 'button[id^="remover"]', function() {
		const id = $(this).attr('id').replace('remover', '');
		removerIp(id);
	});

	$(document).ready(function() {

		$('#singleIp').mask('099.099.099.099');
		$('#startIp').mask('099.099.099.099');
		$('#endIp').mask('099.099.099.099');

		$('#formNovo').on('submit', function(e) {
			e.preventDefault();

			const ipType = document.querySelector('input[name="ipType"]:checked').value;

			const formValidado = validaForm(ipType);

			if (!formValidado) return;

			$.ajax({
				url: "ajax_ip_pdv.php",
				method: "POST",
				data: $("#formNovo").serialize() + "&acao=novo&ug_id=<?= $user_id ?>",
				beforeSend: function() {
					Swal.fire({
						title: 'Aguarde!',
						html: 'Processando a solicitação',
						timerProgressBar: true,
						didOpen: () => {
							Swal.showLoading()
						}
					});
				},
				success: function(data) {
					Swal.close();
					let icone = "";
					let msg = "";
					let resposta;
					try {
						resposta = typeof data === "string" ? JSON.parse(data) : data;
					} catch (e) {
						resposta = {};
					}
					if (resposta.status === "success") {
						icone = "success";
						//msg = "Endereço IP cadastrado com sucesso!";

						// Atualiza a tabela de histórico
						const newRow = `<tr>
							<td style="font-size: 16px;font-weight: bold;color: #444;">${(ipType == 'single' ? 'Único' : 'Range')}</td>
							<td style="font-size: 16px;font-weight: bold;color: #444;">${resposta.msg}</td>
							<td><button type="button" class="btn btn-danger" id="remover${resposta.id}" style="font-weight: bold;" title="Remover">Remover</button></td>
						</tr>`;
						$("#col-Ip").prepend(newRow);
						// Atualiza os campos de risco e data
						document.getElementById('startIp').value = '';
						document.getElementById('endIp').value = '';
						// Limpa o formulário
						//$("#modal-novo").modal('hide');
					} else {
						msg = "Erro ao cadastrar endereço IP: " + resposta.msg;
						icone = "error";
					}

					Swal.fire({
						position: 'center',
						icon: icone,
						title: (resposta?.status == "success") ? "Sucesso" : "Erro",
						html: msg,
						showConfirmButton: false,
						timer: 3000
					});

				},
				error: function(xhr, status, error) {
					Swal.close();
					let msg = "Erro inesperado ao cadastrar o endereço IP.";
					if (xhr && xhr.responseText) {
						try {
							const resposta = JSON.parse(xhr.responseText);
							if (resposta.msg) {
								msg = resposta.msg;
							}
						} catch (e) {
							msg = xhr.responseText;
						}
					}
					Swal.fire({
						position: 'center',
						icon: 'error',
						title: "Erro",
						html: msg,
						showConfirmButton: false,
						timer: 3000
					});
				}
			});

		});
	});
</script>
<div id="modal-novo" class="modal fade txt-azul-claro" role="dialog">
	<div class="modal-dialog modal-md">
		<!-- Modal content-->
		<div class="modal-content" style="z-index: 1001;">
			<div class="card">
				<h2 class="modal-title-alt">Cadastrar endereços IPs</h2>
				<form id="formNovo">
					<div class="form-group">
						<label>Tipo de IP</label>
						<div class="ip-type-selector">
							<div class="radio-group active" onclick="selectIpType('single')">
								<input type="radio" name="ipType" value="single" checked>
								<span>IP Individual</span>
							</div>
							<div class="radio-group" onclick="selectIpType('range')">
								<input type="radio" name="ipType" value="range">
								<span>Range de IPs</span>
							</div>
						</div>
					</div>

					<div class="form-group">
						<label id="ipLabel">Endereço IP</label>
						<div class="ip-input-group">
							<input type="text" id="singleIp" name="ip_address" placeholder="ex: 192.168.1.100" pattern="^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$">
							<div class="range-inputs" id="rangeInputs">
								<input type="text" id="startIp" name="ip_range_ini" placeholder="IP inicial" pattern="^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$">
								<div class="range-separator">até</div>
								<input type="text" id="endIp" name="ip_range_end" placeholder="IP final" pattern="^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$">
							</div>
						</div>
						<div id="ipError" class="error"></div>
					</div>

					<div class="form-group btn-modal-group">
						<button class="button-modal btn-modal-submit" type="submit">Salvar IP</button>
						<button class="button-modal btn-modal-close" type="button" data-dismiss="modal">Fechar</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<div class="col-md-12">
	<ol class="breadcrumb top10">
		<li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem();
												?>">BackOffice -
				<?php echo $currentAba->getDescricao();
				?></a></li>
		<li class="active"><?php echo $sistema->menu[0]->getDescricao();
							?></li>
		<li class="active"><a
				href="<?php echo $sistema->item->getLink();
						?>"><?php echo $sistema->item->getDescricao();
							?> -
				<?= utf8_decode(htmlspecialchars(utf8_encode($usuario['ug_id']))) ?></a>
		</li>
	</ol>
</div>
<div class="col-md-12">
	<div>
		<fieldset>
			<h4 class="titulo-vencimento">PDV</h4>
			<table class="table txt-preto">
				<tr>
					<td>Id:</td>
					<td id="ug_id">
						<?= $usuario['ug_id'] ?>
					</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td>Nome:</td>
					<td>
						<?= utf8_decode(htmlspecialchars(utf8_encode(($usuario["ug_nome_fantasia"] ? $usuario["ug_nome_fantasia"] : "Não encontrado")))) ?>
					</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td colspan="2">
						<fieldset>
							<div class="d-flex custom-justify bottom-10">
								<h5 class="titulo-vencimento">Endereços IP cadastrados</h5>
								<a href="#" style="font-weight: bold;" class="btn btn-success btn-todos align-right"
									data-toggle="modal" data-target="#modal-novo">Novo</a>
							</div>
							<div style="overflow-x: auto;">
								<div class="table-responsive">
									<table
										class="table table-bordered table-striped table-hover text-center align-middle">
										<thead class="thead-dark">
											<tr>
												<th style="font-size: 14px;font-weight: bold;color: #444;">Tipo</th>
												<th style="font-size: 14px;font-weight: bold;color: #444;">Endereço IP</th>
												<th style="font-size: 14px;font-weight: bold;color: #444;">Ação</th>
											</tr>
										</thead>
										<tbody id="col-Ip">
											<?php
											/*  ip_address,
				    							ip_range,
				    							ip_range_ini,
				    							ip_range_end */
											foreach ($Ip as $row) {
												$range = $row['ip_range'] == false;

												if ($range) {
													$msgRange = "Único";
													$ipAdress = $row['ip_address'];
												} else {
													$msgRange = "Range";
													$ipAdress = "{$row['ip_range_ini']} - {$row['ip_range_end']}";
												}

												echo '<tr>
              									  		<td style="font-size: 16px;font-weight: bold;color: #444;">' . $msgRange . '</td>
              									  		<td style="font-size: 16px;font-weight: bold;color: #444;">' . $ipAdress . '</td>
              											<td>
															<button id="remover' . $row['id'] . '" type="button" class="btn btn-danger" style="font-weight: bold;" title="Remover">Remover</button>
														</td>
              										</tr>';
											}
											?>
										</tbody>
									</table>
								</div>
							</div>
						</fieldset>
					</td>
				</tr>
			</table>
		</fieldset>
	</div>
</div>
<?php
require_once $raiz_do_projeto . "backoffice/includes/rodape_bko.php";
?>