<?php
require_once '/www/includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo_teste.php";

$data_atual = date('Y-m');

?>
<link rel="stylesheet"
	href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github.min.css">
<link href="https://cdn.datatables.net/v/dt/dt-1.13.4/datatables.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/v/dt/dt-1.13.5/datatables.min.js"></script>

<style>
	#data-help-icon {
		left: 50px;

	}

	#data-help-icon::after {
		left: 20px;
	}

	.help-icon {
		position: relative;
		margin-left: 5px;
		cursor: pointer;
		background: rgb(0, 0, 0, 0.1);
		color: #606060;
		border-radius: 50%;
		width: 18px;
		height: 18px;
		text-align: center;
		line-height: 18px;
		font-size: 12px;
		user-select: none;
		display: inline-block;
	}

	.help-icon .tooltiptext {
		visibility: hidden;
		width: 135px;
		bottom: 100%;
		left: 50%;
		margin-left: -60px;
		background-color: rgba(0, 0, 0, 0.9);
		color: #fff;
		text-align: center;
		border-radius: 6px;
		padding: 5px;
		font-weight: bold;
		font-size: 11px;

		/* Position the tooltip */
		position: absolute;
		z-index: 1;
	}

	.help-icon .tooltiptext::after {
		content: " ";
		position: absolute;
		top: 100%;
		/* At the bottom of the tooltip */
		left: 50%;
		margin-left: -5px;
		border-width: 5px;
		border-style: solid;
		border-color: black transparent transparent transparent;
	}

	.help-icon:hover .tooltiptext,
	.tooltiptext.show {
		visibility: visible;
		pointer-events: auto;
	}

	.relatorio-info {
		display: flex;
		justify-content: space-between;
		margin-bottom: 20px;
		font-size: 16px;
	}

	.tabela-clientes {
		width: 100%;
		border-collapse: collapse;
		background: #fff;
	}

	.tabela-clientes th,
	.tabela-clientes td {
		border: 1px solid #ccc;
		padding: 10px;
		text-align: center;
	}

	.tabela-clientes th {
		background-color: #e0e0e0;
	}

	.tabela-clientes tr:nth-child(even) {
		background-color: #f9f9f9;
	}

	.total {
		font-weight: bold;
		background: #dfe6e9;
	}

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
		justify-content: left;
		flex-wrap: wrap;
		gap: 20px;
		/* Adiciona uma margem entre as colunas */
	}

	/* Colunas (ajuste para uma largura proporcional) */
	.col-cancel-pins {
		flex: 1;
		min-width: 100px;
		margin: 0;
		max-width: 180px;
		/* Remove margens laterais desnecessárias */
	}

	.data-input {
		min-width: 200px;
	}

	.titulo-vencimento {
		font-weight: bold;
		color: #333333;
		font-family: system-ui, -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
		text-align: left;
		margin-left: 25px;
		padding-bottom: 15px;
		font-size: 21px;
	}

	.xml-box {
		font-size: 14px;
		max-height: 500px;
		overflow: auto;
		background: #f8f9fa;
		border-radius: 6px;
		padding: 15px;
	}

	.xml-colapsado {
		max-height: 120px !important;
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

<div>
	<h2 class="titulo-vencimento">E-Financeira - Consulta</h2>
	<form id="form1" action="#" method="get" class="form-solicitacoes">
		<div class="container-cancel-pins">
			<div class="col-cancel-pins">
				<label for="tipo_busca">Tipo de busca
				</label>
				<select id="tipo_busca" name="tipo_busca" class="form-control">
					<option value="lotexml" selected>Lote por XML</option>
					<option value="loteprotocol">Lote por num. protocolo</option>
				</select>
			</div>
			<div class="col-cancel-pins">
				<label for="protocolo">Núm Protocolo<span class="help-icon">?
						<span class="tooltiptext">
							Número de protocolo recebido na resposta.
						</span>
					</span>
				</label>
				<input id="protocolo" name="protocolo" class="form-control"
					type="text">
			</div>
			<div class="col-cancel-pins">
				<label for="arquivo">XML Resposta: <span class="help-icon">?
						<span class="tooltiptext">
							O XML tem que ser o recebido quando for enviado.
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

	$efinanceira = new GerarEFinanceira();
	if($_POST['tipo_busca'] == 'lotexml'){

		//LER XML
		$protocolo = ""; //Pega do xml

	}else if($_POST['tipo_busca'] == 'loteprotocol'){

		$retorno_consulta = $efinanceira->consultarLoteEFinanceira($_POST['protocolo']);

		xmlViewer($retorno_consulta, $_POST['protocolo']);
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
		$('.container').removeClass('container');
		$('#tabela-clientes').DataTable({
			"ordering": true,
			"paging": false,
			"searching": false,
			"info": false
		});
	});
</script>
<?php
require_once $raiz_do_projeto . "backoffice/includes/rodape_bko.php";
?>