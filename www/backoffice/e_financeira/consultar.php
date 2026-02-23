<?php
require_once '/www/includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";

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
					<li class="active"><a href="#">Consulta e-Fin</a></li>
					<li><a href="lotes_enviados.php">Enviados</a></li>
				</ul>
			</div>
		</div>
	</nav>
	<h2 class="titulo-vencimento">Consulta - E-Financeira</h2>

	<form id="formConsulta" action="#" method="post" class="form-solicitacoes">

		<div class="container-cancel-pins">
			<div class="col-cancel-pins">
				<label for="sel_consulta">Tipo de Consulta</label>
				<select id="sel_consulta" name="sel_consulta" class="form-control" onchange="atualizarCampos()">
					<option value="lote" <?php echo ($sel_consulta == 'lote' ? 'selected' : ''); ?>>Protocolo do lote (Retornado no envio)</option>
					<option value="cadastro" <?php echo ($sel_consulta == 'cadastro' ? 'selected' : ''); ?>>Informações Cadastrais</option>
					<option value="lista" <?php echo ($sel_consulta == 'lista' ? 'selected' : ''); ?>>Lista e-Financeira (Abertura e fechamento)</option>
					<option value="mov_fin" <?php echo ($sel_consulta == 'mov_fin' ? 'selected' : ''); ?>>Mov. Operação Financeira (Mensal)</option>
				</select>
			</div>
		</div>

		<div class="container-cancel-pins">

			<div class="col-cancel-pins group-dynamic" id="grp_lote">
				<label for="numero_lote">Número do Protocolo</label>
				<input type="text" id="numero_lote" name="numero_lote" class="form-control" placeholder="Apenas números">
			</div>

			<div class="col-cancel-pins group-dynamic" id="grp_situacao" style="display:none;">
				<label for="situacao_informacao">Situação</label>
				<select id="situacao_informacao" name="situacao_informacao" class="form-control">
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
	<div id="resultadoConsultaAjax"></div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
<script>
	function atualizarCampos() {
		const selConsulta = document.getElementById('sel_consulta');
		const tipo = selConsulta.value;
		const selectSituacao = document.getElementById('situacao_informacao');

		// Função auxiliar para mostrar/esconder
		const setDisplay = (id, show) => {
			const el = document.getElementById(id);
			if (el) el.style.display = show ? 'block' : 'none';
		};

		// 1. Reseta a visibilidade dos grupos
		const grupos = document.querySelectorAll('.group-dynamic');
		grupos.forEach(el => el.style.display = 'none');

		// 2. Lógica de Opções Dinâmicas para o Select de Situação
		let novasOpcoes = [];

		// CASO A: Lista e-Financeira (0 a 4, onde 1 é Em Andamento e 2 é Ativa)
		if (tipo === 'lista') {
			novasOpcoes = [{
					val: '0',
					text: '0 - Todas'
				},
				{
					val: '1',
					text: '1 - Em Andamento'
				},
				{
					val: '2',
					text: '2 - Ativa'
				},
				{
					val: '3',
					text: '3 - Retificada'
				},
				{
					val: '4',
					text: '4 - Excluída'
				}
			];
		}
		// CASO B: Movimento Financeiro e Anual (0 a 3, onde 1 já é Ativa)
		else if (tipo === 'mov_fin' || tipo === 'mov_fin_anual') {
			novasOpcoes = [{
					val: '0',
					text: '0 - Todas'
				},
				{
					val: '1',
					text: '1 - Ativa'
				},
				{
					val: '2',
					text: '2 - Retificada'
				},
				{
					val: '3',
					text: '3 - Excluída'
				}
			];
		}

		// 3. Renderiza as opções no Select (se houver mudança de contexto)
		// Limpa as opções atuais
		if (novasOpcoes.length > 0) {
			selectSituacao.innerHTML = '';
			novasOpcoes.forEach(opt => {
				let option = document.createElement('option');
				option.value = opt.val;
				option.text = opt.text;
				selectSituacao.appendChild(option);
			});
		}

		// 4. Lógica de Exibição dos Campos (Display)
		if (tipo === 'lote') {
			setDisplay('grp_lote', true);
		} else if (tipo === 'cadastro') {
		} else if (tipo === 'lista') {
			setDisplay('grp_situacao', true); // Mostra o select populado com Caso A
			setDisplay('grp_data_ini', true);
			setDisplay('grp_data_fim', true);
		} else if (tipo === 'mov_fin' || tipo === 'mov_fin_anual') {
			setDisplay('grp_situacao', true); // Mostra o select populado com Caso B
			setDisplay('grp_mes_ini', true);
			setDisplay('grp_mes_fim', true);
			setDisplay('grp_tipo_id', true);
			setDisplay('grp_identificacao', true);
		}
	}

	// Inicializa ao carregar a página
	document.addEventListener("DOMContentLoaded", function() {
		atualizarCampos();
	});

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

		$('#formConsulta').on('submit', function(e) {
			e.preventDefault(); // Impede o reload da página

			// Limpa resultado anterior
			$('#resultadoConsultaAjax').html('');

			// Pega os dados do form
			var formData = new FormData(this);

			// Exibe SweetAlert de Carregamento
			Swal.fire({
				title: 'Consultando e-Financeira',
				html: 'Aguardando resposta da Receita Federal...<br>Isso pode levar alguns segundos (ou 30s se for assíncrono).',
				allowOutsideClick: false,
				allowEscapeKey: false,
				didOpen: () => {
					Swal.showLoading();
				}
			});

			// Requisição AJAX
			$.ajax({
				url: 'ajax_processar_consulta.php', // Nome do arquivo backend criado no Passo 1
				type: 'POST',
				data: formData,
				processData: false, // Importante para FormData
				contentType: false, // Importante para FormData
				success: function(response) {
					// Fecha o SweetAlert
					Swal.close();

					// Insere o HTML retornado na div de resultado
					$('#resultadoConsultaAjax').html(response);

					// Reativa o highlight.js nos novos elementos carregados
					document.querySelectorAll('pre code').forEach((el) => {
						hljs.highlightElement(el);
					});

					// Toast de sucesso (opcional)
					const Toast = Swal.mixin({
						toast: true,
						position: 'top-end',
						showConfirmButton: false,
						timer: 3000
					});
					Toast.fire({
						icon: 'success',
						title: 'Consulta realizada!'
					});
				},
				error: function(xhr, status, error) {
					Swal.fire({
						icon: 'error',
						title: 'Erro na Consulta',
						text: 'Ocorreu um erro ao processar a solicitação: ' + error
					});
				}
			});
		});

		hljs.highlightAll();

	});
</script>
<?php
require_once $raiz_do_projeto . "backoffice/includes/rodape_bko.php";
?>