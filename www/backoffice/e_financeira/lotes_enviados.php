<?php
require_once '/www/includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github.min.css">
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
	<h2 class="titulo-vencimento">Lotes Gerados - E-Financeira</h2>

	<div class="panel panel-default">
		<div class="panel-body">
			<form id="formFiltros" onsubmit="return false;">
				<div class="filtros-grid">
					<div class="campo-duplo">
						<label>Período de Envio</label>
						<div style="display: flex; gap: 5px;">
							<input type="date" id="envio_inicio" class="form-control" style="flex: 1;">
							<input type="date" id="envio_fim" class="form-control" style="flex: 1;">
						</div>
					</div>
					<div>
						<label>CPF/CNPJ (Apenas Mov)</label>
						<input type="text" id="filtro_cpfcnpj" class="form-control" placeholder="Apenas números">
					</div>
					<div>
						<label>Tipo de Evento</label>
						<select id="filtro_tipo" class="form-control">
							<option value="">TODOS</option>
							<option value="MOVIMENTACAO">MOVIMENTAÇÃO</option>
							<option value="ABERTURA">ABERTURA</option>
							<option value="FECHAMENTO">FECHAMENTO</option>
						</select>
					</div>
					<div>
						<label>Status Envio</label>
						<select id="filtro_status" class="form-control">
							<option value="">TODOS</option>
							<option value="ENVIADO">ENVIADO</option>
							<option value="PENDENTE">PENDENTE</option>
							<option value="ERRO">ERRO</option>
						</select>
					</div>
					<div>
						<label>Semestre / Ano</label>
						<div style="display: flex; gap: 5px;">
							<input type="number" id="filtro_ano" class="form-control" placeholder="Ano" min="2015" max="2099">
							<select id="filtro_semestre" class="form-control">
								<option value="">Sem.</option>
								<option value="1">1º Sem</option>
								<option value="2">2º Sem</option>
							</select>
						</div>
					</div>
					<div>
						<label>Competência Informação</label>
						<input type="text" id="filtro_competencia" class="form-control" placeholder="Ex: 2024-03 ou 2024-07...">
					</div>
				</div>
				<div class="text-right">
					<button type="button" id="btnBuscar" class="btn btn-success">Pesquisar Lotes</button>
					<button type="reset" id="btnLimpar" class="btn btn-default">Limpar</button>
				</div>
			</form>
		</div>
	</div>

</div>

<div style="overflow-x: auto; padding-top: 20px;">
	<div class="tabela">
		<table id="tabelaEnviados" class="table table-striped table-bordered" style="width:100%">
			<thead>
				<tr>
					<th>ID Formatado</th>
					<th>Tipo</th>
					<th>Status</th>
					<th>Nome do Arquivo</th>
					<th>Data do Envio</th>
					<th>Competência</th>
					<th>Retificado?</th>
					<th>CPF/CNPJ</th>
					<th>Protocolo</th>
					<th class="text-center" style="min-width: 120px;">Ações (Baixar)</th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
	</div>
</div>

<script>
	$(document).ready(function() {
		var tabela = $('#tabelaEnviados').DataTable({
			"processing": true,
			"serverSide": true,
			"ajax": {
				"url": "ajax_enviados.php",
				"type": "POST",
				"data": function(d) {
					// Passa os valores dos filtros para o PHP
					d.envio_inicio = $('#envio_inicio').val();
					d.envio_fim = $('#envio_fim').val();
					d.cpfcnpj = $('#filtro_cpfcnpj').val();
					d.tipo = $('#filtro_tipo').val();
					d.status = $('#filtro_status').val();
					d.ano = $('#filtro_ano').val();
					d.semestre = $('#filtro_semestre').val();
					d.competencia = $('#filtro_competencia').val();
				}
			},
			"columns": [{
					"data": "id_formatado"
				},
				{
					"data": "tipo"
				},
				{
					"data": "status_badge",
					"orderable": false
				},
				{
					"data": "nome_arquivo"
				},
				{
					"data": "data_envio"
				},
				{
					"data": "data_anomes_formatado",
					"orderable": false
				},
				{
					"data": "retificado"
				},
				{
					"data": "cpfcnpj_declarado"
				},
				{
					"data": "num_protocolo",
					"orderable": false
				},
				{
					"data": "acoes",
					"orderable": false,
					"searchable": false,
					"className": "text-center"
				}
			],
			"order": [
				[4, "desc"]
			], // Ordena por data_envio por padrão
			"language": {
				"url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/pt-BR.json"
			}
		});

		// Ação do botão buscar
		$('#btnBuscar').click(function() {
			tabela.draw();
		});

		// Limpa e recarrega
		$('#btnLimpar').click(function() {
			$('#formFiltros')[0].reset();
			tabela.draw();
		});
	});

	// FUNÇÃO REAL DE DOWNLOAD
	function realizarDownload(tipoArquivo, nomeArquivo) {
		// Codifica o nome do arquivo para garantir que espaços e caracteres especiais não quebrem a URL
		const url = `download_efinanceira.php?tipo=${tipoArquivo}&arquivo=${encodeURIComponent(nomeArquivo)}`;

		// Cria um link temporário e simula o clique para iniciar o download sem recarregar a página da tabela
		const link = document.createElement('a');
		link.href = url;
		document.body.appendChild(link);
		link.click();
		document.body.removeChild(link);
	}
</script>
<?php
require_once $raiz_do_projeto . "backoffice/includes/rodape_bko.php";
?>