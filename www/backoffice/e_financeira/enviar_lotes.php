<?php
require_once '/www/includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";

$producao = getenv('AMBIENTE') == "HOMOLOGACAO" ? 'Homologação' : '';

if (empty($_SESSION['csrf_efinanceira'])) {
	$_SESSION['csrf_efinanceira'] = bin2hex(random_bytes(32));
}
$csrf_efinanceira = $_SESSION['csrf_efinanceira'];

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
	<h2 class="titulo-vencimento">Enviar Lotes - E-Financeira <?= $producao ?></h2>
	<div class="alert alert-info">
		Você pode enviar um arquivo <strong>XML</strong> ou um arquivo <strong>ZIP contendo vários XMLs (lotes)</strong>.
		Os arquivos enviados serão <strong>assinados digitalmente e criptografados automaticamente</strong>.
	</div>

	<div class="alert alert-warning">
			<strong>Importante:</strong> os lotes serão enviados um de cada vez.
			Você pode selecionar vários arquivos XML ou ZIP de uma só vez.
			<strong>Não feche nem atualize esta página durante o envio.</strong>
			Aguarde até aparecer a mensagem <strong>"Envio concluído"</strong>; caso contrário, os lotes restantes não serão enviados.
		</div>
	<form id="formEnvio" action="#" method="post" class="form-solicitacoes" enctype="multipart/form-data">
		<div class="container-cancel-pins">
			<div class="col-cancel-pins">
				<label for="arquivo">Selecione os arquivos XML ou ZIP que deseja enviar:</label>
					<input id="arquivo" name="arquivo[]" type="file" multiple accept=".xml,.zip" required>
			</div>
		</div>
		<div class="d-flex top10 custom-justify">
				<button id="btnEnviarLotes" type="submit" class="btn btn-success btn-busca">Enviar</button>
		</div>
	</form>
</div>
<div style="overflow-x: auto; padding-top: 20px;">
	<div class="relatorio-info">
		<div><strong>Data:</strong> <?php echo date('d/m/Y H:m:i'); ?></div>
	</div>
		<div id="progressoEnvio" class="alert alert-info" style="display:none; margin-top:15px;">
			<div><strong id="tituloProgressoEnvio">Preparando arquivos...</strong></div>
			<div id="detalheProgressoEnvio" style="margin:6px 0;"></div>
			<div class="progress" style="margin-bottom:0;">
				<div id="barraProgressoEnvio" class="progress-bar progress-bar-striped active" role="progressbar" style="width:0%">0%</div>
			</div>
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

	const csrfEFinanceira = <?= json_encode($csrf_efinanceira) ?>;
	let envioEmAndamento = false;

	function esperarEnvio(ms) {
		return new Promise(resolve => setTimeout(resolve, ms));
	}

	function atualizarProgressoEnvio(titulo, detalhe, concluidos, total) {
		const percentual = total > 0 ? Math.min(100, Math.round((concluidos / total) * 100)) : 0;
		$('#progressoEnvio').show();
		$('#tituloProgressoEnvio').text(titulo);
		$('#detalheProgressoEnvio').text(detalhe || '');
		$('#barraProgressoEnvio').css('width', percentual + '%').text(percentual + '%');
	}

	async function chamarFilaEnvio(formData) {
		const resposta = await fetch('ajax_processar_envio.php', {
			method: 'POST',
			body: formData,
			credentials: 'same-origin',
			cache: 'no-store'
		});
		const texto = await resposta.text();
		let dados;
		try {
			dados = JSON.parse(texto);
		} catch (e) {
			throw new Error('O servidor retornou uma resposta inválida.');
		}
		if (!resposta.ok || !dados.sucesso) {
			throw new Error(dados.mensagem || 'Falha no processamento da fila.');
		}
		return dados;
	}

	async function prepararArquivoEnvio(arquivo, atual, totalArquivos) {
		atualizarProgressoEnvio(
			'Preparando arquivos',
			'Enviando arquivo de entrada ' + atual + ' de ' + totalArquivos + ': ' + arquivo.name,
			atual - 1,
			totalArquivos
		);
		const formData = new FormData();
		formData.append('acao', 'preparar_fila');
		formData.append('csrf_token', csrfEFinanceira);
		formData.append('arquivo[]', arquivo, arquivo.name);
		return chamarFilaEnvio(formData);
	}

	async function avancarTicketEnvio(ticket) {
		const formData = new FormData();
		formData.append('acao', 'avancar_fila');
		formData.append('csrf_token', csrfEFinanceira);
		formData.append('ticket_id', ticket.ticket_id);
		formData.append('token', ticket.token);

		while (true) {
			try {
				return await chamarFilaEnvio(formData);
			} catch (erro) {
				const decisao = await Swal.fire({
					icon: 'warning',
					title: 'Comunicação interrompida',
					text: erro.message,
					confirmButtonText: 'Tentar continuar',
					showCancelButton: true,
					cancelButtonText: 'Interromper'
				});
				if (!decisao.isConfirmed) {
					throw erro;
				}
				await esperarEnvio(1500);
			}
		}
	}

	function adicionarResultadoEnvio(html) {
		if (!html) return;
		$('#resultadoEnvioAjax').append(html);
		document.querySelectorAll('#resultadoEnvioAjax pre code:not([data-highlighted])').forEach(el => {
			hljs.highlightElement(el);
		});
	}

	$(document).ready(function() {
		$('#formEnvio').on('submit', async function(e) {
			e.preventDefault();
			if (envioEmAndamento) return;

			const arquivos = Array.from(document.getElementById('arquivo').files || []);
			if (arquivos.length === 0) {
				Swal.fire({ icon: 'warning', title: 'Selecione um arquivo' });
				return;
			}

			envioEmAndamento = true;
			$('#btnEnviarLotes, #arquivo').prop('disabled', true);
			$('#resultadoEnvioAjax').html('');

			try {
				let totalLotes = 0;
				let falhasTotais = 0;
				let lotesConcluidos = 0;

				// Cada arquivo e preparado e consumido antes do proximo, limitando disco e memoria.
				for (let i = 0; i < arquivos.length; i++) {
					const ticket = await prepararArquivoEnvio(arquivos[i], i + 1, arquivos.length);
					totalLotes += ticket.estado.total;
					while (true) {
						const resposta = await avancarTicketEnvio(ticket);
						adicionarResultadoEnvio(resposta.html);

						const estado = resposta.estado;
						const protocolo = estado.protocolo_atual ? ' Protocolo: ' + estado.protocolo_atual + '.' : '';
						atualizarProgressoEnvio(
							'Enviando lotes para a Receita',
							'Arquivo de entrada ' + (i + 1) + ' de ' + arquivos.length + '; lote ' + Math.min(estado.concluidos + 1, estado.total) + ' de ' + estado.total + ': ' + (estado.arquivo_atual || 'finalizando') + '.' + protocolo,
							i + (estado.total > 0 ? estado.concluidos / estado.total : 0),
							arquivos.length
						);

						if (resposta.concluido) {
							lotesConcluidos += estado.total;
							falhasTotais += estado.falhas;
							break;
						}
						await esperarEnvio(Number(resposta.aguardar_ms) || 100);
					}
				}

				atualizarProgressoEnvio(
					'Envio concluído',
					lotesConcluidos + ' lote(s) processado(s); ' + falhasTotais + ' com falha ou ocorrência.',
					arquivos.length,
					arquivos.length
				);
				$('#barraProgressoEnvio').removeClass('active progress-bar-striped');

				Swal.fire({
					icon: falhasTotais > 0 ? 'warning' : 'success',
					title: 'Envio concluído',
					text: totalLotes + ' lote(s) processado(s), com ' + falhasTotais + ' falha(s) ou ocorrência(s).'
				});
			} catch (erro) {
				Swal.fire({
					icon: 'error',
					title: 'Envio interrompido',
					text: erro.message + ' Não reenvie lotes já aceitos sem antes conferir os resultados e protocolos exibidos.'
				});
			} finally {
				envioEmAndamento = false;
				$('#btnEnviarLotes, #arquivo').prop('disabled', false);
			}
		});

		window.addEventListener('beforeunload', function(e) {
			if (!envioEmAndamento) return;
			e.preventDefault();
			e.returnValue = '';
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
