<?php
ob_start();

//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

require_once "../../../../../includes/constantes.php";
require_once $raiz_do_projeto . "public_html/sys/includes/topo_sys.php";
require_once $raiz_do_projeto . "public_html/sys/includes/gamer/inc_pub_access.php";
require_once $raiz_do_projeto . "includes/permissao_pin.php";

$tf_data_inicial   = $_POST['tf_data_inicial'] ?? null;
$tf_data_final     = $_POST['tf_data_final'] ?? null;

$dd_opr_codigo     = $_POST['dd_opr_codigo'] ?? null;
$fcanal          = $_POST['fcanal'] ?? null;

// Botão
$BtnSearch     = $_POST['BtnSearch'] ?? null;

$dd_opr_codigo = ($dd_opr_codigo !== null && $dd_opr_codigo !== '') ? (int)$dd_opr_codigo : 0;
$fcanal = preg_replace('/[^A-Za-z]/', '', (string)$fcanal);
$BtnSearch = !empty($BtnSearch) ? 1 : 0;
$tf_data_inicial = preg_replace('/[^0-9\\/]/', '', (string)$tf_data_inicial);
$tf_data_final = preg_replace('/[^0-9\\/]/', '', (string)$tf_data_final);


//Disponibilizando acesso restrito
set_time_limit(60000);

//Declarando valor IOF 6.38 ou 0.38
$iof = array(6.38, 0.38, IOF);

$time_start = getmicrotime();

$b_debug = false;
if ($_SESSION["tipo_acesso_pub"] == 'AT') {
    if (!$flist_vg_id) {
        $flist_vg_id = false;
    } //end if(!$flist_vg_id)
}

if (!$dd_opr_codigo) $dd_opr_codigo = 0;
if (!$tf_data_final)    $tf_data_final   = date('d/m/Y');
if (!$tf_data_inicial)  $tf_data_inicial = date('d/m/Y');

if (b_is_Publisher()) {
    $dd_opr_codigo = (int)$_SESSION["opr_codigo_pub"];
}

// Levanta lista de operadoras
$sql  = "select opr_codigo, opr_nome from operadoras where opr_status='1' and opr_importa=1 order by opr_nome";
$resopr = SQLexecuteQueryParams($sql, array());

if ($BtnSearch) {

    // Levantando as comissões por canal
    $sql = "
                select co_opr_codigo, co_canal, co_data_inclusao, co_volume_tipo, co_volume_min, co_comissao, opr_internacional_alicota
                from operadoras o 
                    left outer join tb_comissoes c on o.opr_codigo=c.co_opr_codigo 
                where to_char(co_data_inclusao,'YYYYDDMMHH24MISS') = (select to_char(max(co_data_inclusao),'YYYYDDMMHH24MISS') from tb_comissoes where co_opr_codigo=opr_codigo) 
                group by co_opr_codigo, co_canal, co_data_inclusao, co_volume_tipo, co_volume_min, co_comissao, opr_internacional_alicota 
                order by co_opr_codigo, co_canal, co_data_inclusao desc, co_volume_tipo, co_volume_min  
               ";
    $rescommiss = SQLexecuteQueryParams($sql, array());

    $vetorComissao = array();

    while ($rescommiss_row = pg_fetch_array($rescommiss)) {
        // Atalho para o código da operadora
        $codOpr = $rescommiss_row['co_opr_codigo'];

        // 1. Proteção da Raiz: Garante que esta operadora seja um array
        if (!isset($vetorComissao[$codOpr]) || !is_array($vetorComissao[$codOpr])) {
            $vetorComissao[$codOpr] = array();
        }

        $rescommiss_row['co_canal'] = trim($rescommiss_row['co_canal']);

        if (empty($rescommiss_row['co_canal'])) {
            $rescommiss_row['co_volume_tipo'] = trim($rescommiss_row['co_volume_tipo']);

            if (empty($rescommiss_row['co_volume_tipo'])) {
                // Estrutura: [CODIGO][MIN] = VALOR
                $vetorComissao[$codOpr][$rescommiss_row['co_volume_min']] = $rescommiss_row['co_comissao'];
            } else {
                // Estrutura: [CODIGO][TIPO][MIN] = VALOR
                $tipoVolume = $rescommiss_row['co_volume_tipo'];

                // 2. Proteção de Nível 2 (Tipo): Evita colisão se 'TIPO' já for um valor numérico
                if (isset($vetorComissao[$codOpr][$tipoVolume]) && !is_array($vetorComissao[$codOpr][$tipoVolume])) {
                    $vetorComissao[$codOpr][$tipoVolume] = array();
                }

                $vetorComissao[$codOpr][$tipoVolume][$rescommiss_row['co_volume_min']] = $rescommiss_row['co_comissao'];
            }
        } else {
            // Cálculo do valor final
            $valorComissao = $rescommiss_row['co_comissao'];

            if (in_array($rescommiss_row['opr_internacional_alicota'], $iof) && !empty($rescommiss_row['co_comissao'])) {
                $valorComissao = $rescommiss_row['co_comissao'] + $rescommiss_row['opr_internacional_alicota'];
            }

            // Estrutura: [CODIGO][CANAL] = VALOR
            // O erro acontecia aqui porque $vetorComissao[$codOpr] não estava se comportando como array
            $vetorComissao[$codOpr][$rescommiss_row['co_canal']] = $valorComissao;
        }
    } //end while
    //echo "<pre>".print_r($vetorComissao,true)."</pre>";

    $por_utilizacao = true;
    if ($dd_opr_codigo) {
        $sql = "select 
                opr_codigo                
                from operadoras
                where 
                        opr_contabiliza_utilizacao != 0 and opr_codigo = $1
                ";

	        $rs_publisher = SQLexecuteQueryParams($sql, array((int)$dd_opr_codigo));

        $por_utilizacao = (($rs_publisher) ? pg_num_rows($rs_publisher) : 0) > 0;
    }

    //Buscando PINs na banco de dados

	$sql_params = array();
	$param_dd_opr_codigo = '';
	if ($dd_opr_codigo) {
		$sql_params[] = (int)$dd_opr_codigo;
		$param_dd_opr_codigo = "$" . count($sql_params);
	}
    $sql = "
                select 
                    pin_codinterno, 
                    case_codigo, 
                    opr_nome, 
                    pin_valor, 
                    opr_codigo,
                    pin_datavenda,
                    pin_horavenda,
                    vg_canal
                FROM (
                ";
    if (strtoupper($fcanal) == 'L' || empty($fcanal)) {
        if ($por_utilizacao) {

            $sql .= "(SELECT DISTINCT
                            t0.pin_codinterno,
                            t0.pin_codigo AS case_codigo,
                            t1.opr_nome,
                            t0.pin_valor,
                            t0.opr_codigo,
                            t0.pin_datavenda,
                            t0.pin_horavenda,
                            CASE 
                                WHEN t0.pin_status = '3' THEN 'G'
                                WHEN t0.pin_status IN ('6','8') THEN 'L'
                            END AS vg_canal
                        FROM pins t0
                        JOIN operadoras t1 ON t0.opr_codigo = t1.opr_codigo
                        JOIN pins_status t3 ON t0.pin_status = t3.stat_codigo
                        JOIN pins_integracao_historico pih 
                               ON pih.pih_pin_id = t0.pin_codinterno 
                        LEFT JOIN tb_venda_games_modelo_pins vp 
                               ON vp.vgmp_pin_codinterno = t0.pin_codinterno
                        WHERE pih.pin_status = 8 AND pih.pih_codretepp = '2' AND vp.vgmp_pin_codinterno IS NULL";

            if ($tf_data_inicial && $tf_data_final) {
                $data_inic = formata_data(trim($tf_data_inicial), 1);
                $data_fim = formata_data(trim($tf_data_final), 1);
                $sql .= " and (pih.pih_data between '" . trim($data_inic) . "' and  '" . trim($data_fim) . "  23:59:59')  " . PHP_EOL;
            }
            if ($dd_opr_codigo) {
                $sql .= " and t0.opr_codigo=" . $param_dd_opr_codigo . " and t0.pin_status <> '9'" . PHP_EOL;
            }

            $sql .= "UNION ALL

                        SELECT DISTINCT
                            t0.pin_codinterno,
                            t0.pin_codigo AS case_codigo,
                            t1.opr_nome,
                            t0.pin_valor,
                            t0.opr_codigo,
                            t0.pin_datavenda,
                            t0.pin_horavenda,
                            CASE 
                                WHEN t0.pin_status = '3' THEN 'G'
                                WHEN t0.pin_status IN ('6','8') THEN 'L'
                            END AS vg_canal
                        FROM pins t0
                        JOIN operadoras t1 ON t0.opr_codigo = t1.opr_codigo
                        JOIN pins_status t3 ON t0.pin_status = t3.stat_codigo
                        JOIN tb_venda_games_modelo_pins vp 
                             ON vp.vgmp_pin_codinterno = t0.pin_codinterno
                        WHERE 1=1 ";
        } else {
            $sql .= "
                   (select 
                        distinct t0.pin_codinterno, 
                        pin_codigo as case_codigo, 
                        t1.opr_nome, 
                        t0.pin_valor, 
                        t0.opr_codigo,
                        t0.pin_datavenda,
                        t0.pin_horavenda,
                        case 
                            when t0.pin_status = '3' then 'G'
                            when t0.pin_status = '6' then 'L'
                            when t0.pin_status = '8' then 'L'
                        end as vg_canal
                    from pins t0
                    JOIN operadoras t1 ON t0.opr_codigo = t1.opr_codigo
					JOIN pins_status t3 ON t0.pin_status = t3.stat_codigo
					LEFT JOIN pins_integracao_historico pih ON pih.pih_pin_id = t0.pin_codinterno
                    where 1=1" . PHP_EOL;
        }
        if ($tf_data_inicial && $tf_data_final) {
            $data_inic = formata_data(trim($tf_data_inicial), 1);
            $data_fim = formata_data(trim($tf_data_final), 1);
            $sql .= " and (pin_datavenda between '" . trim($data_inic) . "' and  '" . trim($data_fim) . "')  " . PHP_EOL;
        }
        if ($dd_opr_codigo) {
            $sql .= " and (t0.opr_codigo=" . $param_dd_opr_codigo . ") and t0.pin_status <> '9'" . PHP_EOL;
        }
        $sql .= " ) " . PHP_EOL;
    } //end if($fcanal == 'L') 

    if (strtoupper($fcanal) == 'C' || empty($fcanal)) {
        //Ativando a descriptografia do PIN
        require_once $raiz_do_projeto . "includes/gamer/chave.php";
        require_once $raiz_do_projeto . "includes/gamer/AES.class.php";
        //Instanciando Objetos para Descriptografia
        $chave256bits = new Chave();
        $pc = new AES($chave256bits->retornaChave());

        if (empty($fcanal)) {
            $sql .= "
                    UNION ALL
                        ";
        } //end if(empty($fcanal))
        $sql .= "
                   ( SELECT 
                        pih_pin_id as pin_codinterno, 
                        pin_codigo as case_codigo, 
                        o.opr_nome, 
                        pin_valor, 
                        pih_id as opr_codigo,
                        to_char(pih_data,'YYYY-MM-DD')::timestamp as pin_datavenda,
                        to_char(pih_data,'HH24:MI:SS') as pin_horavenda,
                        'C' as vg_canal
                    FROM pins_integracao_card_historico pich
                        inner join pins_card pc ON pin_codinterno=pih_pin_id
                        inner join operadoras o ON pc.opr_codigo=o.opr_codigo
                    WHERE pih_codretepp='2'
                        and pich.pin_status =4
                        and pin_lote_codigo > 6 -- Codigo de lotes menor e igual a 6 foram utilizados para testes 
                        ";
        if ($dd_opr_codigo) {
            $sql .= " 
                        and pih_id = " . $param_dd_opr_codigo . PHP_EOL;
        } //end if($dd_opr_codigo) 
        if ($tf_data_inicial && $tf_data_final) {
            $data_inic = formata_data(trim($tf_data_inicial), 1);
            $data_fim = formata_data(trim($tf_data_final), 1);
            $sql .= " 
                        and (pih_data between '" . trim($data_inic) . " 00:00:00' and  '" . trim($data_fim) . " 23:59:59')  " . PHP_EOL;
        }
        $sql .= " 
                    )
                    ";
    } //end if(strtoupper($fcanal) == 'C' || empty($fcanal)) 

    $sql .= " 
                ) as selection
                order by pin_datavenda desc, pin_horavenda desc";
    //echo $sql;
    if ($_SERVER["REMOTE_ADDR"] == "187.18.252.183") {
        echo $sql;
    }
    $resid = SQLexecuteQueryParams($sql, $sql_params);
    $total_table = (($resid) ? pg_num_rows($resid) : 0);
} //end if($BtnSearch)
?>
<link href="/includes/bootstrap/css/bootstrap.min_new.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/js/jquery.js"></script>
<script type="text/javascript" src="/includes/bootstrap/js/bootstrap.min.js"></script>
<link href="/sys/css/css.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="/js/table2CSV.js"></script>
<script language="JavaScript">
    <!--
    function GP_popupConfirmMsg(msg) { //v1.0
        document.MM_returnValue = confirm(msg);
    }

    function validade() {
        if (document.form1.tf_data_inicial.value.trim() == "") {
            window.alert("Por favor selecione a Datal Inicial.");
            document.form1.tf_data_inicial.focus();
            return false;
        } else if (document.form1.tf_data_final.value.trim() == "") {
            window.alert("Por favor selecione a Datal Final.");
            document.form1.tf_data_final.focus();
            return false;
        } else if (DiferencaDatas(document.form1.tf_data_inicial.value, document.form1.tf_data_final.value) > 30) {
            window.alert("Por favor selecione uma diferença entre Datas de no máximo 30/31 dias.");
            document.form1.tf_data_inicial.focus();
            return false;
        } else return true;
    }

    function DiferencaDatas(data1, data2) {
        //Diferença entre datas com resultado em dias

        //Splitando
        var vetorData1 = data1.split("/");
        var vetorData2 = data2.split("/");
        // new Date(year, month, day, hours, minutes, seconds, milliseconds)
        var a = new Date(vetorData1[2], vetorData1[1], vetorData1[0], 0, 0, 0, 0); // data1
        var b = new Date(vetorData2[2], vetorData2[1], vetorData2[0], 0, 0, 0, 0); // data1
        var d = (b - a); // Diferença em millisegundos

        var days = Math.round((b - a) / 1000 / 60 / 60 / 24);

        return days;

    } //end function DiferencaDatas
    //
    -->
</script>
<!-- INICIO CODIGO NOVO -->
<div class="container-fluid">
    <div class="container txt-azul-claro bg-branco">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12 ">
                        <strong><?php echo LANG_PINS_PAGE_TITLE_REPORT; ?></strong>
                    </div>
                </div>
                <div class="row txt-cinza">
                    <div class="col-md-6">
                        <span class="pull-left"><strong><?php echo LANG_PINS_SEARCH_1; ?></strong></span>
                    </div>
                    <div class="col-md-6">
                        <span class="pull-right"><a href="../../commerce/index.php" class="btn btn-primary"><strong><i><?php echo LANG_BACK; ?></i></strong></a></span>
                    </div>
                </div>
                <div class="row txt-cinza espacamento">
                    <div class="col-md-2">
                        <?php echo LANG_PINS_START_DATE; ?>
                    </div>
                    <div class="col-md-2">
                        <?php echo LANG_PINS_END_DATE; ?>
                    </div>
                    <div class="col-md-3">
                        <?php echo LANG_PINS_OPERATOR; ?>
                    </div>
                    <div class="col-md-3">
                        <?php echo LANG_PINS_CHANNEL; ?>
                    </div>
                </div>
                <div class="row txt-cinza">
                    <form name="form1" method="post" action="" onSubmit="return validade()">
                        <div class="col-md-2">
                            <input alt="Calendário" name="tf_data_inicial" type="text" class="form-control data" id="tf_data_inicial" value="<?php echo $tf_data_inicial ?>" size="9" maxlength="10">
                        </div>
                        <div class="col-md-2">
                            <input alt="Calendário" name="tf_data_final" type="text" class="form-control data" id="tf_data_final" value="<?php echo $tf_data_final ?>" size="9" maxlength="10">
                        </div>
                        <div class="col-md-3">
                            <?php
                            if (b_is_Publisher()) {
                                echo $_SESSION["opr_nome"];
                            ?>
                                <input type="hidden" name="dd_opr_codigo" id="dd_opr_codigo" value="<?php echo $dd_opr_codigo ?>">
                            <?php
                            } else {
                            ?>
                                <select name="dd_opr_codigo" id="dd_opr_codigo" class="form-control">
                                    <option value=""><?php echo LANG_PINS_SELECT_OPERATOR; ?></option>
                                    <?php
                                    while ($pgopr = pg_fetch_array($resopr)) {
                                    ?>
                                        <option value="<?php echo $pgopr['opr_codigo'] ?>" <?php if ($pgopr['opr_codigo'] == $dd_opr_codigo) echo "selected" ?>><?php echo $pgopr['opr_nome'] ?></option>
                                    <?php
                                    }
                                    ?>
                                </select>
                            <?php
                            }
                            ?>
                        </div>
                        <div class="col-md-3">
                            <select name="fcanal" id="fcanal" class="form-control">
                                <option value="" <?php if (trim($fcanal) == '') echo "selected" ?>><?php echo LANG_PINS_ALL_CHANNELS; ?></option>
                                <option value="L" <?php if (trim($fcanal) == 'L') echo "selected" ?>>PIN Virtual</option>
                                <option value="C" <?php if (trim($fcanal) == 'C') echo "selected" ?>>PIN Card</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" name="BtnSearch" value="<?php echo LANG_PINS_SEARCH_2; ?>" class="btn pull-right btn-success"><?php echo LANG_INTEGRATION_SEARCH; ?></button>
                        </div>
                    </form>
                </div>
                <div class="row txt-cinza espacamento">
                    <div class="col-md-12 bg-cinza-claro">
                        <?php
                        if ($total_table > 0) {
                            $cabecalho = "'" . LANG_PINS_ID . "','Publisher','" . LANG_PINS_DATE . "','PIN','" . LANG_PINS_VALUE . "','E-Prepag + Tax'";
                            $cabecalho .= empty($_SESSION["opr_nome"]) ? ",'Liquido'" : ",'" . $_SESSION["opr_nome"] . "'";
                        ?>
                            <table id="table" class="table bg-branco txt-preto fontsize-p">
                                <thead>
                                    <tr class="bg-cinza-claro">
                                        <th class="text-center"><?php echo LANG_PINS_ID; ?></th>
                                        <th class="text-center">Publisher</th>
                                        <th class="text-center"><?php echo LANG_PINS_DATE; ?></th>
                                        <th class="text-center">PIN</th>
                                        <th class="text-center"><?php echo LANG_PINS_VALUE; ?></th>
                                        <?php
                                        if (!b_is_Publisher()) {
                                        ?>
                                            <th class="text-center">E-Prepag + Tax</th>
                                            <th class="text-center"><?php echo (empty($_SESSION["opr_nome"]) ? "Liquido" : $_SESSION["opr_nome"]); ?></th>
                                        <?php
                                        } //end if(!b_is_Publisher()) 
                                        ?>
                                    </tr>
                                    <tr>
                                        <th colspan="7">
                                            <?php echo ' ' . LANG_SHOW_DATA . ' '; ?> <strong><?php echo $inicial + 1 ?></strong><?php echo ' ' . LANG_TO . ' '; ?><strong><?php echo $reg_ate ?></strong><?php echo ' ' . LANG_FROM . ' '; ?><strong><?php echo $total_table ?> <span id="txt_totais" class="txt-azul-claro"></strong>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    while ($pgrow = pg_fetch_array($resid)) {
                                        $valor = 1;

                                        //Calculando a Comissão
                                        $aux_comiss = ($pgrow['pin_valor'] * $vetorComissao[$pgrow['opr_codigo']][$pgrow['vg_canal']] / 100);

                                        $valor_geral += $pgrow['pin_valor'];
                                        $valor_comiss += $aux_comiss;
                                        $valor_liquido += ($pgrow['pin_valor'] - $aux_comiss);

                                        if (strtoupper($pgrow['vg_canal']) == 'C') {
                                            $pin_serial    = $pc->decrypt(base64_decode($pgrow['case_codigo']));
                                        } //end if(strtoupper($pgrow['vg_canal']) == 'C')
                                        else {
                                            $pin_serial    = $pgrow['case_codigo'];
                                        } //end else do if(strtoupper($pgrow['vg_canal']) == 'C') 

                                        $opr_codigo    = $pgrow['opr_codigo'];
                                    ?>
                                        <tr class="trListagem">
                                            <td align="right"><?php echo mascarar_pin_id($pgrow['pin_codinterno']) ?></td>
                                            <td><?php echo $pgrow['opr_nome'] ?></td>
                                            <td align="center"><?php if ($pgrow['pin_datavenda']) { ?><?php echo monta_data($pgrow['pin_datavenda']); ?> - <?php echo $pgrow['pin_horavenda'];
                                                                                                                                                        } else echo "--"; ?></td>
                                            <td><?php echo mascarar_pin_codigo($pin_serial); ?></td>
                                            <td align="right"><?php echo "R$ " . number_format($pgrow['pin_valor'], 2, ',', '.'); ?></td>
                                            <?php
                                            if (!b_is_Publisher()) {
                                            ?>
                                                <td align="right"><?php echo "R$ " . number_format($aux_comiss, 2, ',', '.'); ?></td>
                                                <td align="right"><?php echo "R$ " . number_format(($pgrow['pin_valor'] - $aux_comiss), 2, ',', '.'); ?></td>
                                            <?php
                                            } //end if(!b_is_Publisher()) 
                                            ?>
                                        </tr>
                                    <?php
                                    }

                                    if (!$valor) {
                                    ?>
                                        <tr bgcolor="#f5f5fb">
                                            <td colspan="7"><?php echo LANG_NO_DATA; ?>.</td>
                                        </tr>
                                    <?php
                                    } else {
                                        $time_end = getmicrotime();
                                        $time = $time_end - $time_start;
                                    ?>
                                        <tr>
                                            <td colspan="3">&nbsp;</td>
                                            <td>TOTAL</td>
                                            <td class="text-right"><strong>R$ <?php echo number_format($valor_geral, 2, ',', '.') ?></strong></td>
                                            <?php
                                            if (!b_is_Publisher()) {
                                            ?>
                                                <td class="text-right"><strong>R$ <?php echo number_format($valor_comiss, 2, ',', '.') ?></strong></td>
                                                <td class="text-right"><strong>R$ <?php echo number_format($valor_liquido, 2, ',', '.') ?></strong></td>
                                            <?php
                                            } //end if(!b_is_Publisher()) 
                                            ?>
                                        </tr>
                                        <tr class="bg-cinza-claro">
                                            <td colspan="7" class="fontsize-pp"><?php echo LANG_PINS_SEARCH_MSG . ' ' . number_format($time, 2, '.', '.') . ' ' . LANG_PINS_SEARCH_MSG_UNIT; ?></td>
                                        </tr>
                                        <tr class="bg-cinza-claro">
                                            <td colspan="7" class="fontsize-pp"><?php echo date('Y-m-d H:i:s'); ?></td>
                                        </tr>
                                    <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                            <div class="row text-center" style="margin-bottom: 15px;">
                                <a href="#" class="btn downloadCsv btn-info ">Download CSV</a>
                            </div>
                            <script language="JavaScript">
                                document.getElementById('txt_totais').innerHTML = '( <?php echo number_format($valor_geral, 2, ',', '.') ?>)';
                            </script>
                        <?php
                        } elseif ($BtnSearch) {
                            echo LANG_NO_DATA . ".";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<link href="/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="/js/global.js"></script>
<script>
    $(function() {
        $('#table').table2CSV({
            header: [<?php echo $cabecalho; ?>],
            toStr: [3]
        });

        var optDate = new Object();
        optDate.interval = 1;

        setDateInterval('tf_data_inicial', 'tf_data_final', optDate);
    });
</script>
<!-- FIM CODIGO NOVO -->
<?php
pg_close($connid);
require_once $raiz_do_projeto . "public_html/sys/includes/rodape_sys.php";
?>
