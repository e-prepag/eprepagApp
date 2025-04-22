<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once $raiz_do_projeto . "class/util/Validate.class.php";
require_once $raiz_do_projeto . "class/util/Util.class.php";

$pos_pagina = false; //apenas para nao exibir erro/ resolver depois

require_once $raiz_do_projeto . "public_html/sys/includes/language/eprepag_lang_pt.inc.php";

if (!b_IsBKOUsuarioAdminBKO()) {
    Util::redirect("/");
}

$menus = array();
$abas = array();
$totalRegistros = 0;

if (!empty($_POST['dataIni']) && !empty($_POST['dataFim'])) {

    $dataI = $_POST['dataIni'];
    $dataF = $_POST['dataFim'];
} else {
    $_POST['dataIni'] = date("Y-m-d H:i:s");
    $_POST['dataFim'] = date("Y-m-d H:i:s");

    $dataI = $_POST['dataIni'];
    $dataF = $_POST['dataFim'];
}

if (!isset($_POST['funcao']))
    $_POST['funcao'] = "CPF";

if (isset($_POST['submit'])) {
    try {
        $con = ConnectionPDO::getConnection();
        if ($con->isConnected()) {
            $pdo = $con->getLink();

            $sqlPaginas = "select distinct(sistema) from benchmark";
            $stmt = $pdo->prepare($sqlPaginas);
            $stmt->execute();
            $paginas = $stmt->fetchAll(PDO::FETCH_OBJ);

            $totalPaginas = count($paginas);

            if (!isset($_POST['funcao'])) {
                throw new Exception("Função é um campo obrigatório.");
            }

            $sql = "select avg(tempo) as media, sistema from benchmark where funcao = :funcao and data >= :dataIni and data <= :dataFim group by sistema";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(":funcao", $_POST['funcao'], PDO::PARAM_STR);
            $stmt->bindParam(":dataIni", $dataI, PDO::PARAM_STR);
            $stmt->bindParam(":dataFim", $dataF, PDO::PARAM_STR);


            $stmt->execute();

            $objBenchmark = $stmt->fetchAll(PDO::FETCH_OBJ);

            $totalRegistros = count($objBenchmark);


        }

    } catch (Exception $ex) {
        print "<div class=\"alert alert-danger top10\" role=\"alert\">{$ex->getMessage()}</div>";
    } catch (PDOException $ex) {
        echo '<pre>';
        print_r($ex);
        echo '</pre>';
        exit;
    }
}
?>
<style>
    .opt {
        cursor: pointer;
    }

    #infoButton {
        border-radius: 50%;
        background-color: rgba(200, 200, 200, 0.45);
        font-weight: bold;
        font-size: 18px;
        border: none;
        color: black;
        align-items: center;
        justify-content: center;
        margin-left: 5px;
        width: 25px;
        height: 25px;
        transition: background-color 0.3s ease;
    }

    #infoButton:hover {
        background-color: rgba(150, 150, 150, 0.6);
    }
    .data{
        width: auto;
    }
</style>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice -
                <?php echo $currentAba->getDescricao(); ?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><?php echo $sistema->item->getDescricao(); ?></li>
    </ol>
</div>
<div class="col-md-12 ">
    <div class="col-md-12 bottom10 txt-preto">
        <h3>Filtrar:<button id="infoButton">
                ?
            </button></h3>
    </div>
    <form method="post" id="filtro" class="fontsize-p txt-preto">
        <div class="form-group has-feedback col-md-3">
            <label class="control-label" for="dataIni">
                Sistema
            </label>
            <select id="funcao" class="form-control" name="funcao">
                <option>--</option>
                <option value="CPF" <?php if (isset($_POST['funcao']))
                    echo "selected" ?>>CPF</option>
                </select>
            </div>
            <div class="form-group has-feedback col-md-3 data">
                <label class="control-label" for="dataIni">
                    Período inicial
                </label>
                <input type="datetime-local" required="" name="dataIni" id="dataIni" value="<?php if (isset($_POST['dataIni']))
                    echo $_POST['dataIni']; ?>" class="form-control">
        </div>
        <div class="form-group has-feedback col-md-3 data">
            <label class="control-label" for="dataFim">
                Período final
            </label>
            <div class="">
                <input type="datetime-local" required="" name="dataFim" id="dataFim" value="<?php if (isset($_POST['dataFim']))
                    echo $_POST['dataFim']; ?>" class="form-control">
            </div>
        </div>
        <div class="col-md-2 form-group">
            <button type="submit" value="enviar" name="submit" class="btn top20 btn-sm btn-info">Buscar</button>
        </div>
    </form>
</div>
<div class="col-md-12">
    <table class="text-left txt-preto table table-bordered table-hover">
        <thead class="">
            <tr>
                <th>Tempo médio</th>
                <th>Página (sistema)</th>
            </tr>
        </thead>
        <tbody title="Clique para editar">
            <?php
            if ($totalRegistros == 0) {
                ?>
                <tr>
                    <td colspan="7">Nenhum resultado foi encontrado.</td>
                </tr>
                <?php
            } else {
                foreach ($objBenchmark as $benchmark) {
                    ?>
                    <tr class="opt trListagem">
                        <td><?php echo $benchmark->media; ?> s.</td>
                        <td><?php echo $benchmark->sistema; ?></td>
                    </tr>
                    <?php
                }//end while
                ?>
                <tr>
                    <td colspan="7">Total de registros encontrados: <?php echo $totalRegistros; ?></td>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>
</div>
<!--trecho necessário para o calendario com data hora-->
<link rel="stylesheet" type="text/css" href="/css/anytime512.css" />
<script language="JavaScript" src="/js/anytime512.js"></script>
<script language="JavaScript" src="/js/anytimetz.js"></script>
<script language="JavaScript" src="/js/anytimeBR.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.getElementById('infoButton').addEventListener('click', function () {
        Swal.fire({
            title: 'Benchmark',
            text: 'O tempo de cada execução da consulta de CPF é armazenado em banco de dados e é calculado a média de tempo de execução em cada página onde a consulta é chamada pelo sistema. (O ajax é chamado em vários pontos do sistema, ele não é uma página específica)',
            icon: 'info',
            confirmButtonText: 'OK'
        });
    });
    // $(function () {
    //     $("#dataIni").AnyTime_noPicker().AnyTime_picker({
    //         baseYear: 2008,
    //         earliest: rangeDemoConv.format(new Date(2008, 1, 1, 0, 0, 0)),
    //         format: rangeDemoFormat,
    //         latest: rangeDemoConv.format(new Date(2022, 11, 31, 23, 59, 59)),
    //         dayAbbreviations: ['DOM', 'SEG', 'TER', 'QUA', 'QUI', 'SEX', 'SAB'],
    //         labelDayOfMonth: 'Dia do Mês',
    //         labelHour: 'Hora',
    //         labelMinute: 'Minuto',
    //         labelMonth: 'Mês',
    //         labelTitle: 'Selecione a Data e Hora',
    //         labelYear: 'Ano',
    //         monthAbbreviations: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez']
    //     });

    //     $("#dataFim").AnyTime_noPicker().AnyTime_picker({
    //         baseYear: 2008,
    //         earliest: rangeDemoConv.format(new Date(2008, 1, 1, 0, 0, 0)),
    //         format: rangeDemoFormat,
    //         latest: rangeDemoConv.format(new Date(2022, 11, 31, 23, 59, 59)),
    //         dayAbbreviations: ['DOM', 'SEG', 'TER', 'QUA', 'QUI', 'SEX', 'SAB'],
    //         labelDayOfMonth: 'Dia do Mês',
    //         labelHour: 'Hora',
    //         labelMinute: 'Minuto',
    //         labelMonth: 'Mês',
    //         labelTitle: 'Selecione a Data e Hora',
    //         labelYear: 'Ano',
    //         monthAbbreviations: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez']
    //     });
    // });
</script>
<?php
require_once $raiz_do_projeto . "backoffice/includes/rodape_bko.php";
?>
</body>

</html>