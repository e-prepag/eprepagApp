<?php
require_once '/www/includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";

$conexao = ConnectionPDO::getConnection()->getLink();

$sql = "SELECT uat.versao_termo, uat.aceitou, uat.data_aceite, uat.ip, uat.dispositivo, uat.localizacao, ug.ug_nome_fantasia, ug.ug_id from dist_usuarios_games ug
				left join dist_usuarios_aceito_termos uat on ug.ug_id = uat.ug_id 
				where ug.ug_id = :usuario_id";

$usuario_id = isset($_REQUEST['usuario_id']) ? $_REQUEST['usuario_id'] : 0;

if ($usuario_id <= 0) {
	die("Id do usuário inválido.");
}

$stmt = $conexao->prepare($sql);
$stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
	die("Usuário não encontrada.");
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
	$(document).ready(function () {
		$('#remover').on('submit', function (e) {
			e.preventDefault();

			$.ajax({
				url: "./ajax_risco_merchants.php",
				method: "POST",
				data: "user_id=<?= $usuario_id ?>",
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
						msg = "Registro de aceite dos termos removido com sucesso";
					} else {
						msg = "Erro ao remover: " + data;
						icone = "error";
					}

					Swal.fire({
						position: 'center',
						icon: icone,
						title: (+data == 1) ? "Sucesso" : "Erro",
						html: msg,
						showConfirmButton: false,
						timer: 3000
					}).then(() => {
						window.location.href = "/index.php";
					});

				}
			});

		});
	});
</script>
<div class="col-md-12">
	<ol class="breadcrumb top10">
		<li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice -
				<?php echo $currentAba->getDescricao(); ?></a></li>
		<li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
		<li class="active"><a
				href="<?php echo $sistema->item->getLink(); ?>"><?php echo $sistema->item->getDescricao(); ?> -
				<?= utf8_decode(htmlspecialchars(utf8_encode($usuario['opr_nome']))) ?></a>
		</li>
	</ol>
</div>
<div class="col-md-12">
	<div>
		<fieldset>
			<h4 class="titulo-vencimento">Usuário PDV</h4>
			<table class="table txt-preto fontsize-pp">
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
						<?= utf8_decode(htmlspecialchars(utf8_encode(($value["ug_nome_fantasia"] ? $value["ug_nome_fantasia"] : "Não encontrado")))) ?>
					</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td>Aceitou:</td>
					<td>
						<?php
						echo ($value["aceitou"] ? "Sim" : "Não");
						?>

					</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td>Versão:</td>
					<td>
						<?= ($value["versao_termo"] ? $value["versao_termo"] : "N/A") ?>
					</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td>Data Aceitou:</td>
					<td>
						<?= ($value["data_aceite"] ? $value["data_aceite"] : "N/A") ?>
					</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td>IP:</td>
					<td>
						<?php
						echo ($value["ip"] ? $value["ip"] : "Não possui");
						?>
					</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td>Dispositivo/Navegador:</td>
					<td>
						<?php
						echo ($value["dispositivo"] ? $value["dispositivo"] : "N/A");
						?>
					</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td>Localização:</td>
					<td>
						<?php
						echo ($value["localizacao"] ? $value["localizacao"] : "N/A");
						?>
					</td>
				</tr>
				<tr>
					<td>Remover termos:</td>
					<td>
						<button class="btn btn-danger btn-sm" id="remover">Remover</button>
					</td>
				</tr>
			</table>
		</fieldset>
	</div>
</div>
<?php
require_once $raiz_do_projeto . "backoffice/includes/rodape_bko.php";
?>