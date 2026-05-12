<?php
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";

set_time_limit(3600);

$data_inicial = $_POST['data_inicial'] ?? null;
$data_final = $_POST['data_final'] ?? null;
$BtnSearch = $_POST['BtnSearch'] ?? null;
$opr_codigo = $_POST['opr_codigo'] ?? null;

?>
<link href="/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="/js/global.js"></script>
<link rel="stylesheet" type="text/css" href="/js/jqueryui/css/custom-theme/jquery-ui-1.9.2.custom.min.css" />
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao(); ?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink(); ?>"><?php echo $sistema->item->getDescricao(); ?></a></li>
    </ol>
</div>
<div class="col-md-12 txt-preto">
    <h4 class="txt-azul-claro bottom50">Congelamento Cotação do Dólar e Período Apurado</h4>
    <form id="congelamento" name="congelamento" method="post" action="congelamento_cotacao_dolar_periodo_apurado.php">
        <div class="col-md-12 col-sm-12 col-xs-12 text-right">
            <div class="col-md-2 col-sm-12 col-xs-12 text-right">Data inicial:</div>
            <div class="col-md-2 col-sm-12 col-xs-12">
                <input type="text" value="<?php if (isset($_POST["data_inicial"]))  echo $_POST["data_inicial"];
                                            else echo date('d/m/Y'); ?>" id="data_inicial" name="data_inicial" char="10" maxlength="10" class="form form-control data w150">
            </div>
            <div class="col-md-2 col-sm-12 col-xs-12 text-right">Data final:</div>
            <div class="col-md-2 col-sm-12 col-xs-12">
                <input type="text" value="<?php if (isset($_POST["data_final"])) echo $_POST["data_final"];
                                            else echo date('d/m/Y'); ?>" id="data_final" name="data_final" char="10" maxlength="10" class="form-control data w150">
            </div>

            <div class="col-md-2 col-sm-12 col-xs-12 pull-right">
                <button type="submit" name="BtnSearch" id="BtnSearch" value="Consultar" class="btn pull-right btn-success">Congelar</button>
            </div>
        </div>
        <div class="col-md-12 col-sm-12 col-xs-12 text-right"><br></div>
        <div class="col-md-12 col-sm-12 col-xs-12 text-right">
            <div class="col-md-2 col-sm-12 col-xs-12 text-right">Publisher:</div>
            <div class="col-md-2 col-sm-12 col-xs-12">
                <select name="opr_codigo" id="opr_codigo">
                    <option value="">Selecione um Publisher</option>
                    <?php
                    $sql_opr = "SELECT opr_codigo, opr_nome, opr_razao, opr_facilitadora FROM operadoras WHERE opr_internacional_alicota > 0 AND opr_facilitadora > 0 order by opr_nome;";
                    $rs_opr = SQLexecuteQuery($sql_opr);

                    while ($rs_opr_row = pg_fetch_array($rs_opr)) {
                    ?>
                        <option value="<?php echo $rs_opr_row['opr_codigo'] ?>" <?php if (isset($_POST['opr_codigo']) && $rs_opr_row['opr_codigo'] == $_POST['opr_codigo']) echo "selected" ?>><?php echo $rs_opr_row['opr_nome'] . " (" . $rs_opr_row['opr_codigo'] . ")" ?></option>
                    <?php
                    } //end while($rs_opr_row = pg_fetch_array($rs_opr))
                    ?>
                </select>
            </div>
        </div>
    </form>
</div>
</div> <!--fecha a div de class="txt-azul-claro col-md-12 bg-branco p-bottom40"> -->

</div> <!--fecha a div de class="container"> -->

<?php

if (isset($BtnSearch) && $BtnSearch) {

    $msg = "";
    $class = "alert ";

    $cod_opr = $GLOBALS['_POST']['opr_codigo'];
    $data_inicio = formata_data($GLOBALS['_POST']['data_inicial'], 1);
    $data_fim = formata_data($GLOBALS['_POST']['data_final'], 1);

    if (empty($cod_opr) || empty($data_inicio) || empty($data_fim)) {
        $msg .= "<strong>ERRO 0101</strong>: É preciso definir um publisher e o período apurado para efetuar o congelamento<br>";
        $class = "alert-danger txt-vermelho";
    } else {

        $update = false;

        // Calcula a diferença em segundos entre as datas
        $diferenca = strtotime($data_fim) - strtotime($data_inicio);

        //Calcula a diferença em dias
        $diferencaDias = floor($diferenca / (60 * 60 * 24)) + 1;

        //Calcular total de dias dos mês
        $primeiroDia = date("Y-m-d", strtotime(substr($data_inicio, 0, 4) . "-" .  substr($data_inicio, 5, 2) . "-01"));
        $ultimoDia = date("Y-m-t", strtotime(substr($data_fim, 0, 4) . "-" .  substr($data_fim, 5, 2)));

        $totalDias = strtotime($ultimoDia) - strtotime($primeiroDia);
        $diferencaTotal = floor($totalDias / (60 * 60 * 24)) + 1;

        //Verificando se a diferença de dias bate com a soma total dos dias dos meses selecionados
        //Caso sejam iguais, o update é feito sem maiores verificações
        if ($diferencaDias == $diferencaTotal) {
            $update = true;
        } else {
            //Verificando se no período informado existe mais de uma cotação de dólar
            $sql = "SELECT COUNT(cd_cotacao), cd_cotacao FROM cotacao_dolar WHERE opr_codigo = $1 AND cd_data >= $2 AND cd_data <= $3 GROUP BY cd_cotacao";
            $rs_count_cotacao = SQLexecuteQueryParams($sql, array($cod_opr, date('Y-m-d', strtotime($data_inicio)) . " 00:00:00", date('Y-m-d', strtotime($data_fim)) . " 00:00:00"));
            if ((($rs_count_cotacao) ? pg_num_rows($rs_count_cotacao) : 0) > 1) {
                //Caso exista, não será permitido o update
                $msg .= "O período selecionado engloba mais de uma cotação. Favor congelar um fragmento de cotação por vez, ou selecione um período (mês) completo.";
            } else {
                //Verificando se o último dia selecionado é o último dia do mês      
                $rs_row = pg_fetch_array($rs_count_cotacao);
                $cotacao = $rs_row["cd_cotacao"];
                if ($ultimoDia != $data_fim) {
                    //Caso não seja, será feito uma verificação para garantir que o período informado corresponde a todo o período de uma determinada cotação
                    //Caso o dia após o último dia informado possua a mesma cotação, significa que o intervalo não abrangeu todo o fragmento de cotação               
                    $sql = "SELECT cd_cotacao FROM cotacao_dolar WHERE opr_codigo = $1 AND cd_data = $2";
                    $rs_verifica_proximo_dia = SQLexecuteQueryParams($sql, array($cod_opr, date('Y-m-d', strtotime($data_fim . " +1 day"))));
                    $rs_row = pg_fetch_array($rs_verifica_proximo_dia);
                    if ($cotacao == $rs_row["cd_cotacao"] && $primeiroDia != $data_inicio) {
                        $msg .= "O período selecionado não engloba todo o fragmento de cotação presente no cadastro.<br> Consulte o cadastro no seguinte link: <a href='/compliance/cadastro_cotacao_dolar.php' target='_blank' > https://" . $_SERVER['SERVER_NAME'] . "/compliance/cadastro_cotacao_dolar.php </a>";
                    }
                }
                //Passando pela validação do próximo dia, iremos fazer a verificação do dia anterior
                if ($primeiroDia != $data_inicio) {
                    $sql = "SELECT cd_cotacao FROM cotacao_dolar WHERE opr_codigo = $1 AND cd_data = $2";
                    $rs_verifica_dia_anterior = SQLexecuteQueryParams($sql, array($cod_opr, date('Y-m-d', strtotime($data_inicio . " -1 day"))));
                    $rs_row = pg_fetch_array($rs_verifica_dia_anterior);
                    if ($cotacao == $rs_row["cd_cotacao"] && $ultimoDia != $data_fim) {
                        $msg .= "O período selecionado não engloba todo o fragmento de cotação presente no cadastro.<br> Consulte o cadastro no seguinte link: <a href='/compliance/cadastro_cotacao_dolar.php' target='_blank' > https://" . $_SERVER['SERVER_NAME'] . "/compliance/cadastro_cotacao_dolar.php </a>";
                    }
                }
            }
        }
        if (empty($msg)) {
            $update = true;
        } else {
            $class = "alert-danger txt-vermelho";
        }

        if ($update) {
            $currentmonthVerify = mktime(0, 0, 0, intval(substr($data_fim, 5, 2)), 1, intval(substr($data_fim, 0, 4)));

            $sql = "UPDATE cotacao_dolar SET cd_freeze = 1 WHERE opr_codigo = $1 AND cd_data >= $2 AND cd_data <= $3;";

            $rs_freeze = SQLexecuteQueryParams($sql, array($cod_opr, date('Y-m-d', strtotime($data_inicio)) . " 00:00:00", date('Y-m-d', strtotime($data_fim)) . " 00:00:00"));
            if (!$rs_freeze || pg_affected_rows($rs_freeze) < 1) {
                $msg .= "<strong>ERRO 0104</strong>: Problema ao congelar a COTAÇÃO DO DÓLAR para o publisher selecionado!<br>";
                $class = "alert-danger txt-vermelho";
            } else {
                //Congelamento dos dados financeiros
                $dataInicioPeriodo = date('Y-m-d', strtotime($data_inicio)) . " 00:00:00";
                $dataFimPeriodo = date('Y-m-d', strtotime($data_fim)) . " 23:59:59";
                $sql_calculo = "update financial_processing
                                set fp_freeze = 1,
                                    fp_start_date = $2::timestamp,
                                    fp_end_date = $3::timestamp
                                where fp_publisher = $1
                                    and (
                                        (date(fp_start_date) = $2::date and date(fp_end_date) = $3::date)
                                        or date(fp_date) between $2::date and $3::date
                                    )";
                //echo $sql_calculo." <br>";

                $dados_calculo = SQLexecuteQueryParams($sql_calculo, array($cod_opr, $dataInicioPeriodo, $dataFimPeriodo));

                if (!$dados_calculo || pg_affected_rows($dados_calculo) < 1) {
                    $sql_verifica_financeiro = "select
                                                    count(*) as total_registros,
                                                    count(*) filter (where fp_freeze = 1) as total_congelados
                                                from financial_processing
                                                where fp_publisher = $1
                                                    and (
                                                        (date(fp_start_date) = $2::date and date(fp_end_date) = $3::date)
                                                        or date(fp_date) between $2::date and $3::date
                                                    )";
                    $rs_verifica_financeiro = SQLexecuteQueryParams($sql_verifica_financeiro, array($cod_opr, $dataInicioPeriodo, $dataFimPeriodo));
                    $row_verifica_financeiro = $rs_verifica_financeiro ? pg_fetch_assoc($rs_verifica_financeiro) : array();
                    $totalRegistrosFinanceiros = (int)($row_verifica_financeiro['total_registros'] ?? 0);
                    $totalRegistrosCongelados = (int)($row_verifica_financeiro['total_congelados'] ?? 0);

                    $msg .= "<strong>ERRO 0105</strong>: Problema ao congelar os TOTAIS FINANCEIROS para o publisher selecionado<br>Período considerado: " . substr($data_inicio, 0, 19) . " até " . substr($data_fim, 0, 19) . "!<br>";
                    if ($totalRegistrosFinanceiros === 0) {
                        $msg .= "Não há registros em financial_processing para esse publisher/período. Rode ou confira o processamento financeiro antes de congelar.<br>";
                    } elseif ($totalRegistrosFinanceiros === $totalRegistrosCongelados) {
                        $msg .= "Os registros financeiros desse publisher/período já estavam congelados.<br>";
                    }
                    $class = "alert-danger txt-vermelho";
                } //end if(!$dados_calculo && pg_affected_rows($dados_calculo) < 1)
                else {
                    $msg .= "<strong>SUCESSO</strong>: A cotação do dólar e os totais financeiros foram congelados com sucesso!";
                    $class = "alert-success txt-verde";
                }
            }
        }
    }
?>
    <div class="container espacamento">
        <div class="col-md-12 <?php echo $class; ?>"><?php echo $msg; ?></div>
    </div>
<?php
} // end if($BtnSearch)
?>
<script>
    jQuery(function(e) {

        $.datepicker.regional['pt-BR'] = {
            closeText: 'Fechar',
            prevText: '&#x3c;Anterior',
            nextText: 'Pr&oacute;ximo&#x3e;',
            currentText: 'Hoje',
            monthNames: ['Janeiro', 'Fevereiro', 'Mar&ccedil;o', 'Abril', 'Maio', 'Junho',
                'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
            ],
            monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun',
                'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'
            ],
            dayNames: ['Domingo', 'Segunda-feira', 'Ter&ccedil;a-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sabado'],
            dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab'],
            dayNamesMin: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab'],
            weekHeader: 'Sm',
            dateFormat: 'dd/mm/yy',
            firstDay: 0,
            isRTL: false,
            showMonthAfterYear: false,
            yearSuffix: ''
        };
        $.datepicker.setDefaults($.datepicker.regional['pt-BR']);

        $(".data").datepicker({
            dateFormat: "dd/mm/yy",
            maxDate: "dateToday"
        });

    });
</script>
<script>
    $(function() {
        $('#BtnSearch').click(function() {
            var msg = "";
            if ($("#opr_codigo").val().trim() == "") {
                msg += "- É preciso definir um publisher para o período apurado!\n";
            }

            if ($("#data_inicial").val().trim() == "") {
                msg += "- É preciso definir uma data inicial para o período apurado!\n";
            }

            if ($("#data_final").val().trim() == "") {
                msg += "- É preciso definir uma data final para o período apurado!\n";
            }

            if (msg !== "") {
                alert("Erros encontrados:\n" + msg);
                return false;
            }
        });
    });
</script>

<?php
require_once $raiz_do_projeto . "backoffice/includes/rodape_bko.php";
?>
</body>

</html>
