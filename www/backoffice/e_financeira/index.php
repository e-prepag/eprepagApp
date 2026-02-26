<?php
require_once '/www/includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$data_inicial = isset($_GET['dt_inicial']) ? $_GET['dt_inicial'] : "";
$data_final = isset($_GET['dt_final']) ? $_GET['dt_final'] : "";
$sel_tipo = $_GET['sel_tipo'] ?? "pretty";

// --- NOVAS VARIÁVEIS DE FILTRO DE DOCUMENTO ---
$tipo_doc = $_GET['tipo_doc'] ?? ""; // Pode ser 'todos', 'cpf' ou 'cnpj'
$cpfcnpj = $_GET['cpfcnpj'] ?? "";

// --- VARIÁVEIS DE PAGINAÇÃO ---
$limite_registros = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
$pagina_atual = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$offset = ($pagina_atual - 1) * $limite_registros;

$data_atual = date('Y-m');

?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github.min.css">
<link href="https://cdn.datatables.net/v/dt/dt-1.13.4/datatables.min.css" rel="stylesheet" />
<link href="styles.css" rel="stylesheet" />

<style>
	/* Oculta a div do input por padrão */
	#container_cpfcnpj {
		display: none;
	}
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/v/dt/dt-1.13.5/datatables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

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
					<li class="active"><a href="#">Gerar mov.</a></li>
					<li><a href="abertura.php">Gerar abert.</a></li>
					<li><a href="fechamento.php">Gerar Fech.</a></li>
					<li><a href="enviar_lotes.php">Enviar Lotes</a></li>
					<li><a href="consultar.php">Consulta e-Fin</a></li>
					<li><a href="lotes_enviados.php">Enviados</a></li>
				</ul>
			</div>
		</div>
	</nav>
	<h2 class="titulo-vencimento">Gerar lotes de movimentações - E-Financeira</h2>
	<div class="alert alert-warning">
		<strong>Importante:</strong> O mês de dezembro gera movimentações para todos os usuários, então é normal possuir muitos arquivos.
	</div>
	<form id="form1" action="#" method="get" class="form-solicitacoes">
		<input type="hidden" name="limit" value="<?php echo $limite_registros; ?>">

		<div class="container-cancel-pins">
			<div class="col-cancel-pins">
				<label for="dt_inicial">Início período</label>
				<input id="dt_inicial" name="dt_inicial" max="<?php echo $data_atual; ?>" value="<?php echo $data_inicial; ?>" class="form-control" type="month">
			</div>

			<div class="col-cancel-pins">
				<label for="dt_final">Final período</label>
				<input id="dt_final" name="dt_final" max="<?php echo $data_atual; ?>" value="<?php echo $data_final; ?>" class="form-control" type="month">
			</div>

			<div class="col-cancel-pins">
				<label for="tipo_doc">Filtrar por</label>
				<select id="tipo_doc" name="tipo_doc" class="form-control">
					<option value="todos" <?php echo ($tipo_doc == "todos" || $tipo_doc == "" ? "selected" : ""); ?>>Todos</option>
					<option value="cpf" <?php echo ($tipo_doc == "cpf" ? "selected" : ""); ?>>CPF</option>
					<option value="cnpj" <?php echo ($tipo_doc == "cnpj" ? "selected" : ""); ?>>CNPJ</option>
				</select>
			</div>

			<div class="col-cancel-pins" id="container_cpfcnpj">
				<label for="cpfcnpj" id="label_cpfcnpj">Documento</label>
				<input id="cpfcnpj" name="cpfcnpj" value="<?php echo htmlspecialchars($cpfcnpj); ?>" class="form-control" type="text" placeholder="Digite apenas números">
			</div>

			<div class="col-cancel-pins">
				<label for="sel_tipo">Visualização</label>
				<select id="sel_tipo" name="sel_tipo" class="form-control">
					<option value="pretty" <?php echo ($sel_tipo == "pretty" ? "selected" : ""); ?>>Simplificada</option>
					<option value="xml" <?php echo ($sel_tipo == "xml" ? "selected" : ""); ?>>XML</option>
				</select>
			</div>
		</div>

		<div class="d-flex top10 custom-justify">
			<?php if (!empty($data_inicial) && !empty($data_final)) { ?>
                <a class="btn btn-success btn-info"
                    href="gerar_zip.php?data_inicial=<?= urlencode($data_inicial) ?>&data_final=<?= urlencode($data_final) ?>&acao=movimentacoes&tipo_doc=<?= urlencode($tipo_doc) ?>&cpfcnpj=<?= urlencode($cpfcnpj) ?>"
                    target="_blank">Baixar Todos os Lotes</a><span class="help-icon">?
                    <span class="tooltiptext">
                        Baixar um ZIP de todos os eventos no período separados em lotes XMLs com 50 eventos no máximo
                    </span>
                </span>
            <?php } ?>
			<button type="submit" class="btn btn-success btn-busca">Buscar Lotes</button>
		</div>
	</form>

</div>
<div style="overflow-x: auto; padding-top: 20px;">
	<div class="relatorio-info">
		<div><strong>Data:</strong> <?php echo date('d/m/Y H:i:s'); ?></div>
	</div>

	<?php
	require_once __DIR__ . "/functions_e_financeira.php";
	$efinanceira = new GerarEFinanceira();

	$dados = [];
	$quantidade_registros_reais = 0;
	$tem_proxima_pagina = false;

	// Converte 'todos' para null, assim a função busca ambas as categorias sem restrições
	$param_tipo_doc = ($tipo_doc === 'todos') ? null : $tipo_doc;
	$param_cpfcnpj = empty($cpfcnpj) ? null : $cpfcnpj;

	try {
		if (!empty($data_inicial) && !empty($data_final)) {

			if ($sel_tipo == 'xml') {
				$resultado = $efinanceira->gerarXmlMovimentacao($data_inicial, $data_final, $limite_registros, $offset, $param_tipo_doc, $param_cpfcnpj);

				// Valida se houve retorno e atribui as variáveis
				if ($resultado && isset($resultado['xmls'])) {
					$dados = $resultado['xmls'];
					$quantidade_registros_reais = $resultado['total_eventos'];
				}

				if (empty($dados)) {
					echo '<div class="alert alert-info">Nenhum registro encontrado nesta página para os filtros aplicados.</div>';
				} else {
					foreach ($dados as $dado) {
						echo xmlViewer($dado['xml'], "{$dado['ano_mes']}_{$dado['lote_numero']}");
					}
				}
			} else if ($sel_tipo == 'pretty') {
				$dados = $efinanceira->gerarMovimentacaoFinanceiraCompletaDados($data_inicial, $data_final, $limite_registros, $offset, $param_tipo_doc, $param_cpfcnpj);

				if (empty($dados)) {
					echo '<div class="alert alert-info">Nenhum registro encontrado nesta página para os filtros aplicados.</div>';
				} else {
					foreach ($dados as $mes => $eventos) {
						$quantidade_registros_reais += count($eventos);
					}
					echo gerarRelatorioPorCompetencia($dados);
				}
			}

			if ($quantidade_registros_reais > 0) {

				$tem_proxima_pagina = true;
			}
		}
	} catch (Exception $e) {
		echo "<div class='alert alert-danger'><strong>Erro:</strong> " . $e->getMessage() . "</div>";
	}
	?>

	<?php if (!empty($data_inicial) && !empty($data_final)): ?>
		<?php
		// Constrói a URL base com todos os filtros mantidos para a paginação
		$query_params = [
			'dt_inicial' => $data_inicial,
			'dt_final' => $data_final,
			'sel_tipo' => $sel_tipo,
			'limit' => $limite_registros,
			'tipo_doc' => $tipo_doc,
			'cpfcnpj' => $cpfcnpj
		];
		$url_base_paginacao = '?' . http_build_query($query_params);
		?>
		<div class="paginacao" style="margin-top: 30px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; background: #f9f9f9; padding: 15px; border-radius: 5px; border: 1px solid #ddd;">
			<div>
				<?php if ($pagina_atual > 1): ?>
					<a href="<?= $url_base_paginacao ?>&pagina=<?= $pagina_atual - 1 ?>" class="btn btn-primary">&laquo; Anterior</a>
				<?php else: ?>
					<button class="btn btn-default" disabled>&laquo; Anterior</button>
				<?php endif; ?>
			</div>
			<div style="text-align: center;">
				<strong>Página <?= $pagina_atual ?></strong><br>
				<span style="color: #666; font-size: 0.9em;">
					<?php if ($pagina_atual == 1 && !$tem_proxima_pagina): ?>
						(Exibindo todos os resultados)
					<?php elseif (!$tem_proxima_pagina): ?>
						(Última página - Fim dos resultados)
					<?php else: ?>
						(Mostrando até <?= $limite_registros ?> registros da base por arquivo gerado)
					<?php endif; ?>
				</span>
			</div>
			<div>
				<?php if ($tem_proxima_pagina): ?>
					<a href="<?= $url_base_paginacao ?>&pagina=<?= $pagina_atual + 1 ?>" class="btn btn-primary">Próximo &raquo;</a>
				<?php else: ?>
					<button class="btn btn-default" disabled>Próximo &raquo;</button>
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>

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

	// --- LÓGICA DO FILTRO DE DOCUMENTO (jQuery) ---
	function aplicarMascaraDocumento() {
		var tipo = $('#tipo_doc').val();
		var $inputCpfCnpj = $('#cpfcnpj');
		var $container = $('#container_cpfcnpj');
		var $label = $('#label_cpfcnpj');

		// Remove máscaras antigas antes de aplicar a nova
		$inputCpfCnpj.unmask();

		if (tipo === 'cpf') {
			$container.show();
			$label.text('CPF');
			$inputCpfCnpj.attr('placeholder', '000.000.000-00');
			$inputCpfCnpj.mask('000.000.000-00');
		} else if (tipo === 'cnpj') {
			$container.show();
			$label.text('CNPJ');
			$inputCpfCnpj.attr('placeholder', '00.000.000/0000-00');
			$inputCpfCnpj.mask('00.000.000/0000-00');
		} else {
			// Se for "todos", esconde o campo e limpa o valor para não interferir na busca
			$container.hide();
			$inputCpfCnpj.val('');
		}
	}

	$(document).ready(function() {
		hljs.highlightAll();

		// Inicializa a lógica de exibição baseada na seleção atual (Útil após a página recarregar com GET)
		aplicarMascaraDocumento();

		// Escuta as mudanças no select para trocar a máscara e o placeholder em tempo real
		$('#tipo_doc').change(function() {
			// Se o usuário trocar o tipo, limpamos o campo para ele não tentar buscar um CPF com máscara de CNPJ
			$('#cpfcnpj').val('');
			aplicarMascaraDocumento();
		});

		document.querySelectorAll('.help-icon').forEach(icon => {
			icon.addEventListener('click', () => {
				const tooltip = icon.querySelector('.tooltiptext');

				document.querySelectorAll('.tooltiptext.show').forEach(other => {
					if (other !== tooltip) other.classList.remove('show');
				});

				tooltip.classList.add('show');

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