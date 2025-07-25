<?php
require_once '/www/includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";

$conexao = ConnectionPDO::getConnection()->getLink();

$sql = "SELECT *
			FROM (
			  SELECT DISTINCT ON (o.opr_codigo)
			    o.opr_codigo,
			    o.opr_nome,
			    o.opr_cnpj,
			    o.opr_internacional,
			    o.opr_status,
			    COALESCE(oo.tipo_risco, 0) AS tipo_risco,
			    COALESCE(oo.data_observacao, NULL) AS data_observacao,
			    COALESCE(oo.observacao, '') AS observacao
			  FROM operadoras o
			  LEFT JOIN operadoras_obs oo ON o.opr_codigo = oo.opr_codigo
			  WHERE o.opr_codigo = :opr_codigo
			  ORDER BY o.opr_codigo, oo.data_observacao DESC NULLS LAST
			) ultimos;
		";

$oprCodigo = isset($_REQUEST['opr_codigo']) ? $_REQUEST['opr_codigo'] : 0;

if ($oprCodigo <= 0) {
	die("Código da operadora inválido.");
}

$stmt = $conexao->prepare($sql);
$stmt->bindParam(':opr_codigo', $oprCodigo, PDO::PARAM_INT);
$stmt->execute();
$operadora = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$operadora) {
	die("Operadora não encontrada.");
}

$sqlHisorico = "SELECT obs.*, u.shn_nome FROM operadoras_obs obs
					JOIN usuarios u ON u.id = obs.user_id
					WHERE obs.opr_codigo = :opr_codigo ORDER BY data_observacao DESC";
$stmtHistorico = $conexao->prepare($sqlHisorico);
$stmtHistorico->bindParam(':opr_codigo', $oprCodigo, PDO::PARAM_INT);
$stmtHistorico->execute();
$historico = $stmtHistorico->fetchAll(PDO::FETCH_ASSOC);
if (!$historico) {
	$historico = [];
}

$sqlUserNome = "SELECT shn_nome FROM usuarios WHERE id = :user_id";
$stmtUserNome = $conexao->prepare($sqlUserNome);
$stmtUserNome->bindParam(':user_id', $_SESSION["iduser_bko"], PDO::PARAM_INT);
$stmtUserNome->execute();
$userNome = $stmtUserNome->fetchColumn();
if (!$userNome) {
	$userNome = "Desconhecido";
}

?>
<link href="https://cdn.datatables.net/v/dt/dt-1.13.4/datatables.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/v/dt/dt-1.13.5/datatables.min.js"></script>

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
</style>
<script>
	document.addEventListener("DOMContentLoaded", function () {
		const modal = document.getElementById("modal-novo");
		if (modal && modal.parentNode !== document.body) {
			document.body.appendChild(modal);
		}
	});
	$(document).ready(function () {
		$('#formNovo').on('submit', function (e) {
			e.preventDefault();

			$.ajax({
				url: "./ajax_risco_merchants.php",
				method: "POST",
				data: $("#formNovo").serialize() + "&acao=novo&opr_codigo=<?= $oprCodigo ?>&user_id=<?= $_SESSION["iduser_bko"] ?>",
				beforeSend: function () {
					Swal.fire({
						title: 'Aguarde!',
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
					let msg = "";
					if ((+data == 1)) {
						icone = "success";
						msg = "Análise de risco cadastrada com sucesso";

						// Atualiza a tabela de histórico
						const newRow = `<tr>
							<td>${$("#tipo_risco option:selected").text()}</td>
							<td>${new Date().toLocaleString()}</td>
							<td><?= utf8_decode(htmlspecialchars(utf8_encode($userNome))) ?></td>
							<td style='max-width: 300px; word-break: break-word;'>${$("#observacao").val()}</td>
						</tr>`;
						$("#col-historico").prepend(newRow);
						// Atualiza os campos de risco e data
						$("#ultimo-tipo-risco").text($("#tipo_risco option:selected").text());
						$("#ultima-analise").text(new Date().toLocaleString());
						$("#ultima-obs").text($("#observacao").val());
						// Limpa o formulário
						$("#modal-novo").modal('hide');
					} else {
						msg = "Erro ao cadastrar análise de risco: " + data;
						icone = "error";
					}

					Swal.fire({
						position: 'center',
						icon: icone,
						title: (+data == 1) ? "Sucesso" : "Erro",
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
	<div class="modal-dialog modal-sm">
		<!-- Modal content-->
		<div class="modal-content" style="z-index: 1001;">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">Nova Análise de Risco</h4>
			</div>
			<div class="modal-body espacamento">
				<form id="formNovo">
					<div class="bottom-10 form-group">
						<label style="margin-top: 15px;" for="tipo_risco">
							Nível do Risco:
						</label>
						<select class="form-control" name="tipo_risco" id="tipo_risco">
							<option value="1">Baixo</option>
							<option value="2">Médio</option>
							<option value="3">Alto</option>
						</select>
					</div>
					<div class="top-10 form-group">
						<label style="margin-top: 15px;" for="observacao">
							Observação:
						</label>
						<textarea class="form-control" name="observacao" id="observacao" cols="18" rows="5"></textarea>
					</div>

					<div class="d-grid gap-2 mt-3" style="margin-top: 15px;">
						<button type="submit" href="#" class="btn btn-success btn-block"
							id="alteraToken">Salvar</button>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
			</div>
		</div>
	</div>
</div>
<div class="col-md-12">
	<ol class="breadcrumb top10">
		<li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice -
				<?php echo $currentAba->getDescricao(); ?></a></li>
		<li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
		<li class="active"><a
				href="<?php echo $sistema->item->getLink(); ?>"><?php echo $sistema->item->getDescricao(); ?> -
				<?= utf8_decode(htmlspecialchars(utf8_encode($operadora['opr_nome']))) ?></a>
		</li>
	</ol>
</div>
<div class="col-md-12">
	<div>
		<fieldset>
			<h4 class="titulo-vencimento">Merchant</h4>
			<table class="table txt-preto fontsize-pp">
				<tr>
					<td>Nome:</td>
					<td>
						<?= utf8_decode(htmlspecialchars(utf8_encode($operadora['opr_nome']))) ?>
					</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td>CNPJ:</td>
					<td>
						<?php
						$cnpj = isset($operadora['opr_cnpj']) ? trim($operadora['opr_cnpj']) : "";

						echo $cnpj != "" ? utf8_decode(htmlspecialchars(utf8_encode($cnpj))) : "N&#227;o possui";
						?>

					</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td>Status:</td>
					<td>
						<?= $operadora['opr_status'] == 1 ? "Ativo" : "Inativo" ?>
					</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td>Localidade:</td>
					<td>
						<?= $operadora['opr_internacional'] == 1 ? "Internacional" : "Nacional" ?>
					</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td>Risco:</td>
					<td id="ultimo-tipo-risco">
						<?php
						$riscos = [
							0 => "Não possui",
							1 => "Baixo",
							2 => "Médio",
							3 => "Alto"
						];
						echo isset($operadora['tipo_risco']) ? $riscos[$operadora['tipo_risco']] : "Não encontrado";
						?>
					</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td>Data Última Análise:</td>
					<td id="ultima-analise">
						<?php
						echo isset($operadora['data_observacao']) ? $operadora['data_observacao'] : "Não encontrado";
						?>
					</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td>Última Observação:</td>
					<td id="ultima-obs">
						<?php
						echo isset($operadora['observacao']) ? utf8_decode(htmlspecialchars(utf8_encode($operadora['observacao']))) : "Não encontrado";
						?>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<fieldset>
							<div class="d-flex custom-justify bottom-10">
								<h5 class="titulo-vencimento">Histórico de Análises de Risco</h5>
								<a href="#" style="font-weight: bold;" class="btn btn-success btn-todos align-right"
									data-toggle="modal" data-target="#modal-novo">Novo</a>
							</div>
							<div style="overflow-x: auto;">
								<div class="table-responsive">
									<table
										class="table table-bordered table-striped table-hover text-center align-middle">
										<thead class="thead-dark">
											<tr>
												<th>Risco</th>
												<th>Data Análise</th>
												<th>Usuário</th>
												<th>Observação</th>
											</tr>
										</thead>
										<tbody id="col-historico">
											<?php
											$riscos = [
												0 => "Não possui",
												1 => "Baixo",
												2 => "Médio",
												3 => "Alto"
											];

											foreach ($historico as $row) {
												$tipoRisco = isset($row['tipo_risco']) ? (int) $row['tipo_risco'] : null;
												$risco = isset($riscos[$tipoRisco]) ? $riscos[$tipoRisco] : "Não encontrado";

												$data = isset($row['data_observacao']) ? $row['data_observacao'] : "Não encontrado";

												$observacao = isset($row['observacao']) ? utf8_decode(htmlspecialchars(utf8_encode($row['observacao']))) : "?";

												$usuario = isset($row['shn_nome']) ? utf8_decode(htmlspecialchars(utf8_encode($row['shn_nome']))) : "Desconhecido";

												echo "<tr>
              									  <td>{$risco}</td>
              									  <td>{$data}</td>
												  <td>$usuario</td>
              									  <td style='max-width: 300px; word-break: break-word;'>{$observacao}</td>
              									</tr>";
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