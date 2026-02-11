<?php
require_once '/www/includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo_teste.php";

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
					<li class="active"><a href="#">Enviar Lotes</a></li>
					<li><a href="consultar.php">Consulta e-Fin</a></li>
					<li><a href="lotes_enviados.php">Enviados</a></li>
				</ul>
			</div>
		</div>
	</nav>
	<h2 class="titulo-vencimento">Enviar Lotes - E-Financeira</h2>
	<form id="form1" action="#" method="post" class="form-solicitacoes" enctype="multipart/form-data">
		<div class="container-cancel-pins">
			<div class="col-cancel-pins">
				<label for="arquivo">Envie um XML ou um ZIP: <span class="help-icon">?
						<span class="tooltiptext">
							O ZIP contém os XMLs dos lotes, o lote será criptografado e assinado automaticamente com o envio, após enviar, será possível fazer o download dos retornos.
						</span>
					</span>
				</label>
				<input id="arquivo" name="arquivo"
					type="file">
			</div>

		</div>

		<div class="d-flex top10 custom-justify">
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
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['arquivo'])) {

		$caminho_temp = $_FILES['arquivo']['tmp_name'];

		// Descobre o tipo real do arquivo
		$mime_type = mime_content_type($caminho_temp);

		$lotes_xml = [];

		if ($mime_type === 'application/zip' || $mime_type === 'application/x-zip-compressed') {
			$lotes_xml = obterXmlFromZip('arquivo');
		}
		elseif ($mime_type === 'text/xml' || $mime_type === 'application/xml') {
			$xml_conteudo = file_get_contents($caminho_temp);
			$lotes_xml[] = [
                    'nome' => basename($_FILES['arquivo']['name']),
                    'conteudo' => $xml_conteudo
                ];
		} else {
			$erro = "O arquivo deve ser ZIP ou XML.";
		}
		enviarLotesEfinanceira($lotes_xml);
	} else {
		echo "pindamonhagaba";
	}
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