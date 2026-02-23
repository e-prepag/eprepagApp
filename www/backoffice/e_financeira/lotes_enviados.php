<?php
require_once '/www/includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$data_inicial = isset($_GET['dt_inicial']) ? $_GET['dt_inicial'] : "";
$data_final = isset($_GET['dt_final']) ? $_GET['dt_final'] : "";
$data_atual = date('Y-m');

?>
<link rel="stylesheet"
	href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github.min.css">
<link href="https://cdn.datatables.net/v/dt/dt-1.13.4/datatables.min.css" rel="stylesheet" />
<link href="styles.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/v/dt/dt-1.13.5/datatables.min.js"></script>
<div>
	<div style="height: 15px;"></div>
	<nav class="navbar navbar-outline">
		<div class="container-fluid">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#menu-outline">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
			</div>

			<div class="collapse navbar-collapse" id="menu-outline">
				<ul class="nav navbar-nav">
					<li><a href="index.php">Gerar mov.</a></li>
					<li><a href="abertura.php">Gerar abert.</a></li>
					<li><a href="fechamento.php">Gerar Fech.</a></li>
					<li><a href="enviar_lotes.php">Enviar Lotes</a></li>
					<li><a href="consultar.php">Consulta e-Fin</a></li>
					<li class="active"><a href="#">Enviados</a></li>
				</ul>
			</div>
		</div>
	</nav>
	<h2 class="titulo-vencimento">Lotes Enviados - E-Financeira</h2>
	<form id="form1" action="#" method="get" class="form-solicitacoes">
		<div class="container-cancel-pins">
			<div class="col-cancel-pins">
				<label for="dt_inicial">Início período
				</label>
				<input id="dt_inicial" name="dt_inicial" max="<?php echo $data_atual; ?>" value="<?php echo $data_inicial; ?>" class="form-control"
					type="month">
			</div>
			<div class="col-cancel-pins">
				<label for="dt_final">Final período
				</label>
				<input id="dt_final" name="dt_final" max="<?php echo $data_atual; ?>" value="<?php echo $data_final; ?>" class="form-control"
					type="month">
			</div>
		</div>

		<div class="d-flex top10 custom-justify">
			<?php if (!empty($data_inicial) && !empty($data_final)) { ?>
				<a class="btn btn-success btn-info"
					href="gerar_zip.php?
					data_inicial=<?= urlencode($data_inicial) ?>
					&data_final=<?= urlencode($data_final) ?>
					&acao=baixar"
					target="_blank">Baixar Lotes</a>
				<a class="btn btn-success btn-info"
					href="gerar_zip.php?
					data_inicial=<?= urlencode($data_inicial) ?>
					&data_final=<?= urlencode($data_final) ?>
					&acao=criptografar"
					target="_blank">Baixar Cript. e Assin.</a>
			<?php } ?>
			<button type="submit" class="btn btn-success btn-busca">Buscar</button>
		</div>

	</form>

</div>
<div style="overflow-x: auto; padding-top: 20px;">
	<div class="relatorio-info">
		<div><strong>Data:</strong> <?php echo date('d/m/Y H:m:i'); ?></div>
	</div>

	<?php
	require_once __DIR__ . "/functions_e_financeira.php";
	$dados = gerarXmlMovimentacao($data_inicial, $data_final);
	foreach ($dados as $dado) {
		echo xmlViewer(utf8_decode(htmlspecialchars($dado['xml'])), "{$dado['ano_mes']}_{$dado['lote_numero']}");
	}
	//echo json_encode($dados);
	?>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
<script>
	function copiarXml(id) {
		const text = document.getElementById(id).innerText;
		navigator.clipboard.writeText(text).then(() => {
			alert('XML copiado com sucesso!');
		});
	}

	function toggleXml(id) {
		$('#' + id).toggleClass('xml-colapsado');
	}
	$(document).ready(function() {
		hljs.highlightAll();

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
		
	});
</script>
<?php
require_once $raiz_do_projeto . "backoffice/includes/rodape_bko.php";
?>