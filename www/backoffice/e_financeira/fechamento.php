<?php
require_once '/www/includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";

$erro = '';
$data_inicio = '';
$data_fim = '';
$tipo = $_POST['tipo'] ?? '';

$tem_movimentacoes = $_POST['tem_movimentacoes'] ?? 1;
$versao_layout = $_POST['versao_layout'] ?? 'v1_4_0';
$nada_a_declarar = ($_POST['nada_a_declarar'] ?? '0') === '1' ? 1 : 0;

if (!in_array($versao_layout, ['v1_4_0', 'v1_3_0'], true)) {
    $versao_layout = 'v1_4_0';
}

if ($versao_layout === 'v1_3_0') {
    $nada_a_declarar = 0;
}

$hoje = new DateTime('today');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {

    if ($tipo === 'semestre') {

        $ano = (int)($_POST['ano'] ?? 0);
        $semestre = $_POST['semestre'] ?? '';

        if (!$ano || !in_array($semestre, ['1', '2'])) {
            $erro = 'Ano ou semestre inválido.';
        } else {
            if ($semestre == '1') {
                $data_inicio = "$ano-01-01";
                $data_fim    = "$ano-06-30";
            } else {
                $data_inicio = "$ano-07-01";
                $data_fim    = "$ano-12-31";
            }

            if (new DateTime($data_fim) > $hoje) {
                $erro = 'O período não pode ser maior que a data atual.';
            }
        }
    } elseif ($tipo === 'periodo') {

        $data_inicio = $_POST['data_inicio'] ?? '';
        $data_fim    = $_POST['data_fim'] ?? '';

        if (!$data_inicio || !$data_fim) {
            $erro = 'Informe a data inicial e final.';
        } else {
            $inicio = new DateTime($data_inicio);
            $fim    = new DateTime($data_fim);

            if ($inicio > $fim) {
                $erro = 'A data inicial não pode ser maior que a final.';
            } elseif ($fim > $hoje) {
                $erro = 'A data final não pode ser maior que hoje.';
            } else {

                $ano = $inicio->format('Y');

                $sem1_ini = new DateTime("$ano-01-01");
                $sem1_fim = new DateTime("$ano-06-30");

                $sem2_ini = new DateTime("$ano-07-01");
                $sem2_fim = new DateTime("$ano-12-31");

                $valido_sem1 = ($inicio >= $sem1_ini && $fim <= $sem1_fim);
                $valido_sem2 = ($inicio >= $sem2_ini && $fim <= $sem2_fim);

                if (!$valido_sem1 && !$valido_sem2) {
                    $erro = 'O período deve estar inteiramente dentro de um único semestre.';
                }
            }
        }
    } else {
        $erro = 'Tipo de filtro inválido.';
    }
}

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
                    <li class="active"><a href="#">Gerar Fech.</a></li>
                    <li><a href="enviar_lotes.php">Enviar Lotes</a></li>
                    <li><a href="consultar.php">Consulta e-Fin</a></li>
                    <li><a href="lotes_enviados.php">Enviados</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <h2 class="titulo-vencimento">Gerar Fechamento - E-Financeira</h2>
    <?php if ($erro): ?>
        <div class="erro"><?= $erro ?></div>
    <?php endif; ?>
    <div class="alert alert-info">Enviar o fechamento apenas após enviar todas as movimentações.</div>
    <form id="form1" action="#" method="post" class="form-solicitacoes">
        <div class="container-cancel-pins">
            <div class="col-cancel-pins">
                <label for="tipo">Tipo Criação
                </label>
                <select class="form-control" id="tipoFiltro" name="tipo" onchange="alterarFiltro();">
                    <option value="">Selecione...</option>
                    <option value="periodo" <?= $tipo === 'periodo' ? 'selected' : '' ?>>Data inicial e final</option>
                    <option value="semestre" <?= $tipo === 'semestre' ? 'selected' : '' ?>>Por semestre</option>
                </select>
            </div>
            <div id="filtroSemestre" class="<?= $tipo === 'semestre' ? '' : 'd-none' ?>">
                <div class="col-cancel-pins">
                    <label for="ano">Ano
                    </label>
                    <input id="ano" name="ano" min="2000" max="2100" class="form-control" value="<?= $_POST['ano'] ?? date('Y') ?>"
                        type="number">
                </div>
                <div class="col-cancel-pins">
                    <label for="semestre">Semestre
                    </label>
                    <select id="semestre" name="semestre" class="form-control">
                        <option value="1" <?= ($_POST['semestre'] ?? '') == '1' ? 'selected' : '' ?>>1º Semestre</option>
                        <option value="2" <?= ($_POST['semestre'] ?? '') == '2' ? 'selected' : '' ?>>2º Semestre</option>
                    </select>
                </div>
            </div>
            <div id="filtroPeriodo" class="<?= $tipo === 'periodo' ? '' : 'd-none' ?>">
                <div class="col-cancel-pins">
                    <label for="data_inicio">Início período
                    </label>
                    <input id="data_inicio" name="data_inicio" value="<?php echo $data_inicio; ?>" class="form-control"
                        type="date">
                </div>
                <div class="col-cancel-pins">
                    <label for="data_fim">Final período
                    </label>
                    <input id="data_fim" name="data_fim" value="<?php echo $data_fim; ?>" class="form-control"
                        type="date">
                </div>
            </div>
            <div class="col-cancel-pins">
                <label for="tem_movimentacoes">Teve movimentações
                </label>
                <select id="tem_movimentacoes" name="tem_movimentacoes" class="form-control" onchange="alterarMovimentacoes();">
                        <option value="1" <?= ($tem_movimentacoes) == 1 ? 'selected' : '' ?>>Sim</option>
                        <option value="0" <?= ($tem_movimentacoes) == 0 ? 'selected' : '' ?>>Não</option>
                    </select>
            </div>
            <div class="col-cancel-pins">
                <label for="versao_layout">Versão do layout</label>
                <select id="versao_layout" name="versao_layout" class="form-control" onchange="alterarVersaoLayout();">
                    <option value="v1_4_0" <?= $versao_layout === 'v1_4_0' ? 'selected' : '' ?>>Atual - v1.4.0</option>
                    <option value="v1_3_0" <?= $versao_layout === 'v1_3_0' ? 'selected' : '' ?>>Anterior - v1.3.0</option>
                </select>
            </div>
            <div class="col-cancel-pins">
                <label for="nada_a_declarar">Nada a declarar no período</label>
                <select id="nada_a_declarar" name="nada_a_declarar" class="form-control" onchange="alterarNadaDeclarar();">
                    <option value="0" <?= $nada_a_declarar == 0 ? 'selected' : '' ?>>Não</option>
                    <option value="1" <?= $nada_a_declarar == 1 ? 'selected' : '' ?>>Sim</option>
                </select>
            </div>
        </div>
    </form>
    <div class="d-flex top10 custom-justify">
        <?php if (!empty($data_inicio) && !empty($data_fim)) { ?>
            <form id="form2" action="gerar_zip.php" method="POST" target="_blank">
                <?php

                echo "<input type='hidden' form='form2' name='data_inicio' value='{$data_inicio}'>\n";
                echo "<input type='hidden' form='form2' name='data_fim' value='{$data_fim}'>\n";
                echo "<input type='hidden' form='form2' name='tem_movimentacoes' value='{$tem_movimentacoes}'>\n";
                echo "<input type='hidden' form='form2' name='versao_layout' value='{$versao_layout}'>\n";
                echo "<input type='hidden' form='form2' name='nada_a_declarar' value='{$nada_a_declarar}'>\n";

                ?>
                <input name="acao" type="hidden" value="fechamento" form="form2">
                <button type="submit" form="form2" class="btn btn-primary">
                    <i class="fa fa-download"></i> Baixar XML
                </button>
            </form>
        <?php } ?>
        <button type="submit" form="form1" class="btn btn-success btn-busca">Gerar</button>
    </div>
</div>
<div style="overflow-x: auto; padding-top: 20px;">
    <div class="relatorio-info">
        <div><strong>Data:</strong> <?php echo date('d/m/Y H:m:i'); ?></div>
    </div>

    <?php
    require_once __DIR__ . "/functions_e_financeira.php";
    $efinanceira = new GerarEFinanceira();
    if (!$erro && $data_inicio && $data_fim) {
        $dados = $efinanceira->gerarFechamento(
            $data_inicio,
            $data_fim,
            $tem_movimentacoes == 1,
            $versao_layout,
            $nada_a_declarar == 1
        );
        echo xmlViewer($dados['xml']->saveXML(), $dados['id']);
    }
    ?>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
<script>
    function alterarFiltro() {
        const tipo = document.getElementById('tipoFiltro').value;

        const periodo = document.getElementById('filtroPeriodo');
        const semestre = document.getElementById('filtroSemestre');

        // Esconde tudo primeiro
        periodo.classList.add('d-none');
        semestre.classList.add('d-none');

        // Mostra conforme seleção
        if (tipo === 'periodo') {
            periodo.classList.remove('d-none');
        } else if (tipo === 'semestre') {
            semestre.classList.remove('d-none');
        }
    }

    function alterarVersaoLayout() {
        const versao = document.getElementById('versao_layout');
        const nadaADeclarar = document.getElementById('nada_a_declarar');
        const layoutAnterior = versao.value === 'v1_3_0';

        if (layoutAnterior) {
            nadaADeclarar.value = '0';
        }
        nadaADeclarar.disabled = layoutAnterior;
    }

    function alterarNadaDeclarar() {
        const nadaADeclarar = document.getElementById('nada_a_declarar');
        if (nadaADeclarar.value === '1') {
            document.getElementById('tem_movimentacoes').value = '0';
        }
    }

    function alterarMovimentacoes() {
        const temMovimentacoes = document.getElementById('tem_movimentacoes');
        if (temMovimentacoes.value === '1') {
            document.getElementById('nada_a_declarar').value = '0';
        }
    }

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
        alterarVersaoLayout();
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
