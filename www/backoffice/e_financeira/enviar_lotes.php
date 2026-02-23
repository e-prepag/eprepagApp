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
	<div class="alert alert-info">
		Você pode enviar um arquivo <strong>XML</strong> ou um arquivo <strong>ZIP contendo vários XMLs (lotes)</strong>.
		Os arquivos enviados serão <strong>assinados digitalmente e criptografados automaticamente</strong>.
	</div>

	<div class="alert alert-warning">
		<strong>Importante:</strong> Se enviar um arquivo ZIP, inclua <strong>no máximo 5 XMLs</strong>.
		No mês de <strong>dezembro</strong>, são gerados muitos lotes, e quantidade de arquivos grande podem causar falhas no processamento.
	</div>
	<form id="formEnvio" action="#" method="post" class="form-solicitacoes" enctype="multipart/form-data">
		<div class="container-cancel-pins">
			<div class="col-cancel-pins">
				<label for="arquivo">Envie um XML ou um ZIP:
				</label>
				<input id="arquivo" name="arquivo"
					type="file">
			</div>

		</div>

		<div class="d-flex top10 custom-justify">
			<button type="submit" class="btn btn-success btn-busca">Enviar</button>
		</div>

	</form>

</div>
<div style="overflow-x: auto; padding-top: 20px;">
	<div class="relatorio-info">
		<div><strong>Data:</strong> <?php echo date('d/m/Y H:m:i'); ?></div>
	</div>
	<div id="resultadoEnvioAjax"></div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
<script>
	function copiarXml(id) {
		const text = document.getElementById(id).innerText;
		navigator.clipboard.writeText(text).then(() => {
			alert('XML copiado com sucesso!');
		});
	}

	function baixarXml(elementId, filename) {
		// Pega o texto do elemento. 
		// .innerText é CRUCIAL aqui: ele pega o XML puro (<tag>), revertendo o &lt;tag&gt;
		var content = document.getElementById(elementId).innerText;

		// Cria um Blob (arquivo em memória)
		var blob = new Blob([content], {
			type: "text/xml;charset=utf-8"
		});

		// Cria um link invisível para download
		var link = document.createElement("a");
		var url = URL.createObjectURL(blob);

		link.setAttribute("href", url);
		link.setAttribute("download", filename);
		link.style.visibility = 'hidden';

		document.body.appendChild(link);
		link.click();
		document.body.removeChild(link);

		URL.revokeObjectURL(url);
	}

	function toggleXml(id) {
		$('#' + id).toggleClass('xml-colapsado');
	}
	$(document).ready(function() {
		$('#formEnvio').on('submit', function(e) {
			e.preventDefault();

			// Limpa resultado anterior
			$('#resultadoEnvioAjax').html('');

			// Cria o FormData com o arquivo selecionado
			var formData = new FormData(this);

			Swal.fire({
				title: 'Enviando Lote',
				html: 'Processando arquivo, assinando e transmitindo...<br>Aguarde o retorno da Receita.',
				allowOutsideClick: false,
				didOpen: () => {
					Swal.showLoading();
				}
			});

			$.ajax({
				url: 'ajax_processar_envio.php', // Arquivo backend criado no Passo 1
				type: 'POST',
				data: formData,
				// AS DUAS LINHAS ABAIXO SÃO OBRIGATÓRIAS PARA UPLOAD
				processData: false,
				contentType: false,
				success: function(response) {
					Swal.close();

					$('#resultadoEnvioAjax').html(response);

					// Reativa o highlight.js nos novos XMLs retornados
					document.querySelectorAll('pre code').forEach((el) => {
						hljs.highlightElement(el);
					});

					// Toast de sucesso
					const Toast = Swal.mixin({
						toast: true,
						position: 'top-end',
						showConfirmButton: false,
						timer: 3000
					});
					Toast.fire({
						icon: 'success',
						title: 'Processamento concluído!'
					});
				},
				error: function(xhr, status, error) {
					Swal.fire({
						icon: 'error',
						title: 'Erro no Envio',
						text: 'Falha na comunicação com o servidor: ' + error
					});
				}
			});
		});

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