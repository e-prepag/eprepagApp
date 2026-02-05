<?php
require_once '/www/includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo_teste.php";

$erro = '';
$data_inicio = '';
$data_fim = '';
$tipo = $_POST['tipo'] ?? '';
$arquivosPorMes = [];

$hoje = new DateTime('today');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    foreach ($_POST['qtd_mes'] as $anoMes => $quantidade) {

        // Convertendo para garantir que é inteiro
        $quantidade = (int)$quantidade;

        // Armazenando na sua variável final
        $arquivosPorMes[$anoMes] = $quantidade;
    }

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
    <h2 class="titulo-vencimento">Saldos diários - Lista</h2>
    <?php if ($erro): ?>
        <div class="erro"><?= $erro ?></div>
    <?php endif; ?>
    <div>Enviar o fechamento apenas após enviar todas as movimentações</div>
    <form id="form1" action="#" method="post" class="form-solicitacoes">
        <div class="container-cancel-pins">
            <div class="col-cancel-pins">
                <label for="tipo">Tipo Criação
                </label>
                <select class="form-control" id="tipoFiltro" name="tipo" onchange="alterarFiltro(); gerarCamposMeses();">
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
                        type="number" onchange="gerarCamposMeses()">
                </div>
                <div class="col-cancel-pins">
                    <label for="semestre">Semestre
                    </label>
                    <select id="semestre" name="semestre" class="form-control" onchange="gerarCamposMeses()">
                        <option value="1" <?= ($_POST['semestre'] ?? '') == '1' ? 'selected' : '' ?>>1º Semestre</option>
                        <option value="2" <?= ($_POST['semestre'] ?? '') == '2' ? 'selected' : '' ?>>2º Semestre</option>
                    </select>
                </div>
            </div>
            <div id="filtroPeriodo" class="<?= $tipo === 'periodo' ? '' : 'd-none' ?>">
                <div class="col-cancel-pins">
                    <label for="data_inicio">Início período
                    </label>
                    <input id="data_inicio" name="data_inicio" value="<?php echo $data_inicio; ?>" onchange="gerarCamposMeses()" class="form-control"
                        type="date">
                </div>
                <div class="col-cancel-pins">
                    <label for="data_fim">Final período
                    </label>
                    <input id="data_fim" name="data_fim" value="<?php echo $data_fim; ?>" onchange="gerarCamposMeses()" class="form-control"
                        type="date">
                </div>
            </div>
            <div id="col-cancel-pins">
                <label>Quantidade de Arquivos por Mês</label>
                <div id="container-campos-dinamicos">
                </div>
            </div>
        </div>
    </form>
    <div class="d-flex top10 custom-justify">
        <?php if (!empty($data_inicio) && !empty($data_fim)) { ?>
            <form id="form2" action="gerar_zip.php" method="POST" target="_blank">
                <?php
                $datasParaXml = ['data_inicio', 'data_fim'];

                foreach ($datasParaXml as $campo) {
                    if (isset($_POST[$campo])) {
                        $valor = htmlspecialchars($_POST[$campo]);
                        echo "<input type='hidden' form='form2' name='{$campo}' value='{$valor}'>\n";
                    }
                }

                if (isset($_POST['qtd_mes']) && is_array($_POST['qtd_mes'])) {
                    foreach ($_POST['qtd_mes'] as $anoMes => $quantidade) {
                        $chave = htmlspecialchars($anoMes);
                        $valor = htmlspecialchars($quantidade);
                        // Isso recria a estrutura qtd_mes[202501] etc.
                        echo "<input type='hidden' form='form2' name='qtd_mes[{$chave}]' value='{$valor}'>\n";
                    }
                }
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
        $dados = $efinanceira->gerarFechamento($data_inicio, $data_fim, $arquivosPorMes);
        echo xmlViewer($dados['xml']->saveXML(), $dados['id']);
    }
    ?>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
<script>
    const valoresRecarregados = <?php echo json_encode($_POST['qtd_mes'] ?? []); ?>;

    function gerarCamposMeses() {
        const tipo = document.getElementById('tipoFiltro').value;
        const container = document.getElementById('container-campos-dinamicos');
        container.innerHTML = '';

        let mesesParaGerar = [];

        // --- Lógica de Semestre ---
        if (tipo === 'semestre') {
            const ano = document.getElementById('ano').value;
            const semestre = document.getElementById('semestre').value;
            if (!ano) return;

            const mesInicio = (semestre == '1') ? 1 : 7;
            for (let i = 0; i < 6; i++) {
                mesesParaGerar.push({
                    ano: ano,
                    mes: mesInicio + i
                });
            }
        }
        // --- Lógica de Período ---
        else if (tipo === 'periodo') {
            let dataInicStr = document.getElementById('data_inicio').value;
            let dataFimStr = document.getElementById('data_fim').value;

            if (dataInicStr && dataFimStr) {
                let dataInic = new Date(dataInicStr + 'T00:00:00');
                let dataFim = new Date(dataFimStr + 'T00:00:00');

                if (dataInic <= dataFim) {
                    let atual = new Date(dataInic.getFullYear(), dataInic.getMonth(), 1);
                    while (atual <= dataFim) {
                        mesesParaGerar.push({
                            ano: atual.getFullYear(),
                            mes: atual.getMonth() + 1
                        });
                        atual.setMonth(atual.getMonth() + 1);
                    }
                }
            }
        }

        const nomesMeses = ["", "Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro"];

        mesesParaGerar.forEach(item => {
            const mesStr = item.mes.toString().padStart(2, '0');
            const chaveAnoMes = `${item.ano}${mesStr}`; // Ex: 202601

            // VERIFICAÇÃO: Se existir o valor no objeto recarregado, usa ele, senão usa 0
            const valorExistente = valoresRecarregados[chaveAnoMes] !== undefined ? valoresRecarregados[chaveAnoMes] : 0;

            const label = `${nomesMeses[item.mes]} / ${item.ano}`;
            const inputName = `qtd_mes[${chaveAnoMes}]`;

            const div = document.createElement('div');
            div.className = 'form-group d-flex align-items-center mb-2';
            div.innerHTML = `
            <label style="width: 150px; margin-bottom:0;">${label}:</label>
            <input type="number" name="${inputName}" class="form-control" 
                   style="width: 100px;" value="${valorExistente}" min="0" required>
        `;
            container.appendChild(div);
        });
    }

    // Garante que ao carregar a página (com ou sem POST), os campos apareçam
    document.addEventListener('DOMContentLoaded', function() {
        gerarCamposMeses();
    });

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