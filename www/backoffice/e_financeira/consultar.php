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
	<h2 class="titulo-vencimento">Consulta e-Financeira</h2>

	<form id="formConsulta" action="#" method="post" class="form-solicitacoes">

		<div class="container-cancel-pins">

			<div class="col-cancel-pins">
				<label for="sel_consulta">Tipo de Consulta</label>
				<select id="sel_consulta" name="sel_consulta" class="form-control" onchange="atualizarCampos()">
					<option value="lote" <?php echo ($sel_consulta == 'lote' ? 'selected' : ''); ?>>Consultar por Protocolo (Retornado na resposta)</option>
					<option value="cadastro" <?php echo ($sel_consulta == 'cadastro' ? 'selected' : ''); ?>>Informações Cadastrais</option>
					<option value="lista" <?php echo ($sel_consulta == 'lista' ? 'selected' : ''); ?>>Lista e-Financeira (Movimento)</option>
					<option value="mov_fin" <?php echo ($sel_consulta == 'mov_fin' ? 'selected' : ''); ?>>Mov. Operação Financeira (Mensal)</option>
					<option value="mov_fin_anual" <?php echo ($sel_consulta == 'mov_fin_anual' ? 'selected' : ''); ?>>Mov. Operação Financeira (Anual)</option>
				</select>
			</div>

			<div class="col-cancel-pins">
				<label for="ambiente">Ambiente</label>
				<select id="ambiente" name="ambiente" class="form-control">
					<option value="homologacao" <?php echo ($ambiente == 'homologacao' ? 'selected' : ''); ?>>Pre-Produção (Homologação)</option>
					<option value="producao" <?php echo ($ambiente == 'producao' ? 'selected' : ''); ?>>Produção</option>
				</select>
			</div>

			<div class="col-cancel-pins">
				<label for="sel_tipo_visualizacao">Visualização</label>
				<select id="sel_tipo_visualizacao" name="sel_tipo_visualizacao" class="form-control">
					<option value="pretty" <?php echo ($sel_tipo_visualizacao == "pretty" ? "selected" : ""); ?>>Simplificada</option>
					<option value="xml" <?php echo ($sel_tipo_visualizacao == "xml" ? "selected" : ""); ?>>XML Puro</option>
				</select>
			</div>
		</div>

		<div class="container-cancel-pins">

			<div class="col-cancel-pins group-dynamic" id="grp_lote">
				<label for="numero_lote">Número do Protocolo</label>
				<input type="text" id="numero_lote" name="numero_lote" class="form-control" placeholder="Apenas números">
			</div>

			<div class="col-cancel-pins group-dynamic" id="grp_cnpj" style="display:none;">
				<label for="cnpj">CNPJ do Declarante</label>
				<input type="text" id="cnpj" name="cnpj" class="form-control" placeholder="00.000.000/0000-00">
			</div>

			<div class="col-cancel-pins group-dynamic" id="grp_situacao" style="display:none;">
				<label for="situacao_informacao">Situação</label>
				<select id="situacao_informacao" name="situacao_informacao" class="form-control">
					<option value="1">1 - Ativa</option>
					<option value="2">2 - Retificadora</option>
					<option value="3">3 - Cancelada</option>
				</select>
			</div>

		</div>

		<div class="container-cancel-pins">

			<div class="col-cancel-pins group-dynamic" id="grp_data_ini" style="display:none;">
				<label for="dt_inicial">Data Início</label>
				<input id="dt_inicial" name="dt_inicial" type="date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
			</div>

			<div class="col-cancel-pins group-dynamic" id="grp_data_fim" style="display:none;">
				<label for="dt_final">Data Fim</label>
				<input id="dt_final" name="dt_final" type="date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
			</div>

			<div class="col-cancel-pins group-dynamic" id="grp_mes_ini" style="display:none;">
				<label for="anomes_inicio">Mês Início (Vigência)</label>
				<input id="anomes_inicio" name="anomes_inicio" type="month" class="form-control" value="<?php echo $data_atual; ?>">
			</div>

			<div class="col-cancel-pins group-dynamic" id="grp_mes_fim" style="display:none;">
				<label for="anomes_termino">Mês Término (Vigência)</label>
				<input id="anomes_termino" name="anomes_termino" type="month" class="form-control" value="<?php echo $data_atual; ?>">
			</div>

		</div>

		<div class="container-cancel-pins">

			<div class="col-cancel-pins group-dynamic" id="grp_tipo_id" style="display:none;">
				<label for="tipo_identificacao">Tipo Identificação</label>
				<select id="tipo_identificacao" name="tipo_identificacao" class="form-control">
					<option value="1">1 - CPF</option>
					<option value="2">2 - CNPJ</option>
				</select>
			</div>

			<div class="col-cancel-pins group-dynamic" id="grp_identificacao" style="display:none;">
				<label for="identificacao">Nº Identificação (CPF/CNPJ)</label>
				<input type="text" id="identificacao" name="identificacao" class="form-control">
			</div>

		</div>

		<div class="d-flex top10 custom-justify">
			<button type="submit" class="btn btn-success btn-busca">Consultar</button>
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
	if ($_POST) {
		try {
			// Instancia sua classe (Exemplo)
			// $api = new EFinanceiraClient(...); 

			$producao = false;
			$tipoConsulta = $_POST['sel_consulta'];
			$resultado = null;

			switch ($tipoConsulta) {
				case 'lote':
					$lote = $_POST['numero_lote'];
					$resultado = $efinanceira->consultarLoteEFinanceira($lote, $producao);
					break;

				case 'cadastro':
					$cnpj = preg_replace('/[^0-9]/', '', $_POST['cnpj']);
					$resultado = $efinanceira->consultarInformacoesCadastrais($cnpj, $producao);
					break;

				case 'lista':
					$cnpj = preg_replace('/[^0-9]/', '', $_POST['cnpj']);
					$sit = $_POST['situacao_informacao'];
					$dtIni = $_POST['dt_inicial'];
					$dtFim = $_POST['dt_final'];
					$resultado = $efinanceira->consultarListaEFinanceira($cnpj, $sit, $dtIni, $dtFim, $producao);
					break;

				case 'mov_fin':
				case 'mov_fin_anual':
					$cnpj = preg_replace('/[^0-9]/', '', $_POST['cnpj']);
					$sit = $_POST['situacao_informacao'];

					// Transforma '2025-01' (input type month) para '202501' (Formato API)
					$mesIni = str_replace('-', '', $_POST['anomes_inicio']);
					$mesFim = str_replace('-', '', $_POST['anomes_termino']);

					$tipoId = $_POST['tipo_identificacao'];
					$ident = preg_replace('/[^0-9]/', '', $_POST['identificacao']);

					if ($tipoConsulta === 'mov_fin') {
						$resultado = $efinanceira->consultarMovimentoOpFin($cnpj, $sit, $mesIni, $mesFim, $tipoId, $ident, $producao);
					} else {
						$resultado = $efinanceira->consultarMovimentoOpFinAnual($cnpj, $sit, $mesIni, $mesFim, $tipoId, $ident, $producao);
					}
					break;
			}

			// Exibição do Resultado
			echo xmlViewer($resultado, $lote ?? "1234");
		} catch (Exception $e) {
			echo "<div class='alert alert-danger'>Erro: " . $e->getMessage() . "</div>";
		}
	}

	?>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
<script>
	function atualizarCampos() {
		const tipo = document.getElementById('sel_consulta').value;

		// Função auxiliar para mostrar/esconder
		const setDisplay = (id, show) => {
			document.getElementById(id).style.display = show ? 'block' : 'none';
		};

		// Reseta tudo primeiro (esconde todos os grupos dinâmicos)
		const grupos = document.querySelectorAll('.group-dynamic');
		grupos.forEach(el => el.style.display = 'none');

		// Lógica de exibição baseada no PHP
		if (tipo === 'lote') {
			setDisplay('grp_lote', true);
		} else if (tipo === 'cadastro') {
			setDisplay('grp_cnpj', true);
		} else if (tipo === 'lista') {
			setDisplay('grp_cnpj', true);
			setDisplay('grp_situacao', true);
			setDisplay('grp_data_ini', true);
			setDisplay('grp_data_fim', true);
		} else if (tipo === 'mov_fin' || tipo === 'mov_fin_anual') {
			setDisplay('grp_cnpj', true);
			setDisplay('grp_situacao', true);
			setDisplay('grp_mes_ini', true);
			setDisplay('grp_mes_fim', true);
			setDisplay('grp_tipo_id', true);
			setDisplay('grp_identificacao', true);
		}
	}

	// Roda ao carregar a página para garantir estado correto (caso venha de um submit)
	document.addEventListener("DOMContentLoaded", function() {
		atualizarCampos();
	});

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