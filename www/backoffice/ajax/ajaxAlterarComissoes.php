<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);

$tipos_comissoes = array(
    '0' => "N&atilde;o utiliza comiss&atilde;o vari&aacute;vel",
    '1' => "Utiliza comiss&atilde;o vari&aacute;vel",
);

$tipos_vendas = array(
    '0' => "Não Utiliza Divis&atilde;o de Vendas Indiretas e Diretas",
    '1' => "Utiliza Divis&atilde;o de Vendas Indiretas e Diretas",
);

$divisao_vendas = array(
    'D' => "Venda Direta",
    'I' => "Venda Indireta",
);

header("Content-Type: text/html; charset=ISO-8859-1",true);
function isAjax() {return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));}
function block_direct_calling() {
    if(!isAjax()) {
           echo "Chamada não permitida<br>";
           die("Stop");
    }
}
block_direct_calling();

require_once '../../includes/constantes.php';

function gravaLog_Comissoes($mensagem){
    global $raiz_do_projeto;
    //Arquivo
    $file = $raiz_do_projeto . "log/log_Comissoes_Alteracao.txt";

    //Usuário
    $user_backoffice = strtoupper($GLOBALS['user_backoffice']);
    //Mensagem
    $mensagem = str_repeat("-", 80) . "\n" . date('Y-m-d H:i:s') . " " . $GLOBALS['_SERVER']['SCRIPT_FILENAME'] . "\nUsuário do BackOffice: " . $user_backoffice . "\n" . $mensagem . "\n";
    //Grava mensagem no arquivo
    if ($handle = fopen($file, 'a+')) {
        fwrite($handle, $mensagem);
        fclose($handle);
    }

}

require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";

//echo "<pre>".print_r($_REQUEST,true)."</pre>";

$opr_codigo = isset($_REQUEST['opr_codigo']) ? $_REQUEST['opr_codigo'] : NULL;
$user_backoffice = isset($_REQUEST['user_backoffice']) ? $_REQUEST['user_backoffice'] : NULL;
$opr_comissao_por_volume = isset($_REQUEST['opr_comissao_por_volume']) ? $_REQUEST['opr_comissao_por_volume'] : NULL;
$co_volume_tipo = isset($_REQUEST['co_volume_tipo']) ? $_REQUEST['co_volume_tipo'] : NULL;
$atualizar = isset($_REQUEST['atualizar']) ? $_REQUEST['atualizar'] : NULL;
$co_volume_tipo_dado = isset($_REQUEST['co_volume_tipo_dado']) ? $_REQUEST['co_volume_tipo_dado'] : NULL;
$co_comissao_dado = isset($_REQUEST['co_comissao_dado']) ? $_REQUEST['co_comissao_dado'] : NULL;
$co_volume_min_dado = isset($_REQUEST['co_volume_min_dado']) ? $_REQUEST['co_volume_min_dado'] : NULL;
$co_volume_max_dado = isset($_REQUEST['co_volume_max_dado']) ? $_REQUEST['co_volume_max_dado'] : NULL;
$addlinha = isset($_REQUEST['addlinha']) ? $_REQUEST['addlinha'] : NULL;

//Adicionado as novas Comissões
if ($atualizar == "OK") {
    //Inicia transacao
    $msg = "";
    $sql = "BEGIN TRANSACTION ";
    $logSQL = $sql . "\n";
    $ret = SQLexecuteQuery($sql);
    if (!$ret) $msg = "Erro ao iniciar transação.\n";

    //COMISSÃO VARIAVEL
    if ($opr_comissao_por_volume == 1) {
        $logSQL .= "Possui comissão variavel\n";

        //fazer Update para comissão variavel
        $sql = "update operadoras set opr_comissao_por_volume=" . $opr_comissao_por_volume . " where opr_codigo=" . $opr_codigo;
        $logSQL .= $sql . "\n";
        $rs_comissao = SQLexecuteQuery($sql);
        if (!$rs_comissao) {
            $msg .= "Erro ao salvar informações da comissão. ($sql)\n";
        } else {

            $sql = "insert into tb_comissoes (co_opr_codigo,co_canal,co_data_inclusao,co_comissao,co_tipo) values ($opr_codigo, 'C',NOW()," . $_POST['comiss_c'] . ",'F');";
            $logSQL .= $sql . "\n";
            $rs_comissao = SQLexecuteQuery($sql);
            if (!$rs_comissao) {
                $msg .= "Erro ao salvar informações da comissão. ($sql)\n";
            } else {
                //POSSUI DIFERENCIAÇÃO ENTRE VENDAS DIRETAS E INDIRETAS
                if ($co_volume_tipo == 1) {

                    $logSQL .= "Possui diferenciação de volumes por vendas diretas e indiretas\n";

                    $i = 0;
                    $array_aux = array();
                    while ($i < count($co_volume_tipo_dado)) {
                        $array_aux[$co_volume_tipo_dado[$i]][$i]['COM'] = $co_comissao_dado[$i];
                        $array_aux[$co_volume_tipo_dado[$i]][$i]['MIN'] = $co_volume_min_dado[$i];
                        $array_aux[$co_volume_tipo_dado[$i]][$i]['MAX'] = $co_volume_max_dado[$i];
                        $i++;
                    }//end while
                    ksort($array_aux);

                    //echo "<pre>";var_dump($array_aux);echo "</pre>";
                    //exit;

                    SQLexecuteQuery("UPDATE tb_comissoes SET co_tipo = 'F' WHERE co_opr_codigo = {$opr_codigo} AND co_canal NOT IN ('M','E', 'L', 'P','C');");

                    foreach ($array_aux as $key => $value) {
                        //sort($value);
                        //echo "<pre>".var_dump($value)."</pre>";exit;
                        $i = 0;
                        foreach ($value as $key_interno => $value_interno) {
                            /*var_dump('oi');
                            var_dump(($i + 1 >= count($value)) );
                            var_dump(count($value));

                            var_dump(($i + 1 >= count($value)) && (($value_interno['MIN']!='') && ($value_interno['MAX']!='') && ($value_interno['COM']!='')));*/
                            //exit;
                            //Teste de validação
                            //echo $value[$i+1]['MIN']." = ".$value[$i]['MAX']."<br>";
                            if ((($value_interno['MIN']!='') && ($value_interno['MAX']!='') && ($value_interno['COM']!=''))) { //

                                $sql = "insert into tb_comissoes (co_opr_codigo,co_canal,co_data_inclusao,co_comissao,co_tipo,co_volume_min,co_volume_max,co_volume_tipo)
                                          values ($opr_codigo, '',NOW()," . $value_interno['COM'] . ",'V'," . str_replace(",", ".", $value_interno['MIN']) . "," . str_replace(",", ".", $value_interno['MAX']) . ",'" . $key . "');";
                                $logSQL .= $sql . "\n";
                                //echo $sql."<br>";
                                $rs_comissao = SQLexecuteQuery($sql);
                                if (!$rs_comissao) {
                                    $msg .= "Erro ao salvar informações da comissão. ($sql)\n";
                                }

                            }//end if
                            else {
                                $logSQL .= "Valores de faixa errado:\nFinal [" . $value[$i]['MAX'] . "] e Início do Próximo [" . $value[$i + 1]['MIN'] . "]\n";
                            }
                            $i++;
                        }//end foreach
                    }//end foreach

                }//end if($co_volume_tipo == 1)
                //FAIXA POR VOLUME SEM DIFERENCIAÇÂO DE TIPOS DE VENDAS
                else {

                    $logSQL .= "Não possui diferenciação de volumes por vendas diretas e indiretas\n";

                    SQLexecuteQuery("UPDATE tb_comissoes SET co_tipo = 'F' WHERE co_opr_codigo = {$opr_codigo} AND co_canal NOT IN ('M','E', 'L', 'P','C');");

                    $i = 0;
                    $array_aux = array();
                    while ($i < count($co_volume_min_dado)) {
                        $array_aux[$co_volume_min_dado[$i]][$co_volume_min_dado[$i]]['COM'] = $co_comissao_dado[$i];
                        $array_aux[$co_volume_min_dado[$i]][$co_volume_min_dado[$i]]['MIN'] = $co_volume_min_dado[$i];
                        $array_aux[$co_volume_min_dado[$i]][$co_volume_min_dado[$i]]['MAX'] = $co_volume_max_dado[$i];
                        $i++;
                    }//end while
                    foreach ($array_aux as $key => $value) {
                        //sort($value);
                        //echo "<pre>".print_r($value,true)."</pre>";
                        $i = 0;
                        foreach ($value as $key_interno => $value_interno) {

                            //sort($array_aux);
                            //echo "<pre>".print_r($array_aux,true)."</pre>";
                            //$i = 0;
                            //foreach($array_aux as $key => $value) {
                            //Teste de validação
                            if (($value_interno['MIN']!='' && $value_interno['MAX']!='' && $value_interno['COM']!='')) {
                                $sql = "insert into tb_comissoes (co_opr_codigo,co_canal,co_data_inclusao,co_comissao,co_tipo,co_volume_min,co_volume_max)
                                          values ($opr_codigo, '',NOW()," . $value_interno['COM'] . ",'V'," . str_replace(",", ".", $value_interno['MIN']) . "," . str_replace(",", ".", $value_interno['MAX']) . ");";
                                //$sql = "insert into tb_comissoes (co_opr_codigo,co_canal,co_data_inclusao,co_comissao,co_tipo,co_volume_min,co_volume_max) values ($opr_codigo, '',NOW(),".$value['COM'].",'V',".str_replace(",",".",$value['MIN']).",".str_replace(",",".",$value['MAX']).");";
                                $logSQL .= $sql . "\n";
                                //echo $sql."<br>";
                                $rs_comissao = SQLexecuteQuery($sql);
                                if (!$rs_comissao) {
                                    $msg .= "Erro ao salvar informações da comissão. ($sql)\n";
                                }

                            }//end if
                            else {
                                $logSQL .= "Valores de faixa errado:\nFinal [" . $array_aux[$i]['MAX'] . "] e Início do Próximo [" . $value[$i + 1]['MIN'] . "]\n";
                            }
                            $i++;
                        }//end foreach

                    }//end foreach

                }//end else do if($co_volume_tipo == 1)
            }//end else do if(!$rs_comissao)
        }//end else do if(!$rs_comissao)
    }//end if($opr_comissao_por_volume == 1)
    //COMISSÃO FIXA
    else {
        $logSQL .= "Possui comissão Fixa por Canal\n";

        //fazer Update para comissão fixa
        $sql = "update operadoras set opr_comissao_por_volume=" . $opr_comissao_por_volume . " where opr_codigo=" . $opr_codigo;
        $logSQL .= $sql . "\n";
        $rs_comissao = SQLexecuteQuery($sql);
        if (!$rs_comissao) {
            $msg .= "Erro ao salvar informações da comissão. ($sql)\n";
        } else {
            $sql = "insert into tb_comissoes (co_opr_codigo,co_canal,co_data_inclusao,co_comissao,co_tipo) values ($opr_codigo, 'M',NOW()," . $_POST['comiss_m'] . ",'F');
                    insert into tb_comissoes (co_opr_codigo,co_canal,co_data_inclusao,co_comissao,co_tipo) values ($opr_codigo, 'E',NOW()," . $_POST['comiss_e'] . ",'F');
                    insert into tb_comissoes (co_opr_codigo,co_canal,co_data_inclusao,co_comissao,co_tipo) values ($opr_codigo, 'L',NOW()," . $_POST['comiss_l'] . ",'F');
                    insert into tb_comissoes (co_opr_codigo,co_canal,co_data_inclusao,co_comissao,co_tipo) values ($opr_codigo, 'P',NOW()," . $_POST['comiss_p'] . ",'F');
                    insert into tb_comissoes (co_opr_codigo,co_canal,co_data_inclusao,co_comissao,co_tipo) values ($opr_codigo, 'C',NOW()," . $_POST['comiss_c'] . ",'F');";
            $logSQL .= $sql . "\n";
            //echo $sql."<br>";
            $rs_comissao = SQLexecuteQuery($sql);
            if (!$rs_comissao) {
                $msg .= "Erro ao salvar informações da comissão. ($sql)\n";
            }
        }//end else do if(!$rs_comissao)

    }//end else do if($opr_comissao_por_volume == 1)
    //$msg .= "Interrompido para testes.\n";
    if ($msg == "") {
        $sql = "COMMIT TRANSACTION ";
        $ret = SQLexecuteQuery($sql);
        if (!$ret) $msg .= "Erro ao comitar transação do rollback.\n";
        else return true;
    } else {
        $sql = "ROLLBACK TRANSACTION ";
        $ret = SQLexecuteQuery($sql);
        if (!$ret) $msg .= "Erro ao dar rollback no rollback na transação.\n";
    }
    $logSQL .= $sql . "\n";

    gravaLog_Comissoes("MSG:\n" . $msg . "\nlogSQL:\n" . $logSQL);
    echo $msg;
    die();

}//end if($atualizar=="OK")

?>
<script type="text/javascript">

    function showValuesAlterar() {
        return $("form").serialize();
    }

    function normalizarComissao(v){
        return v.replace(",",".");
    }


    //funcao que adiciona novos registros de comissoes
    function MM_reload_alterar() {
        var errosMsg = '';
        // Verificando se é venda variável:
        var venda_variavel = $('#opr_comissao_por_volume');
        if (venda_variavel.val() == '0') {
            console.log('Validar somente os 5 campos');
            // Validar somente os 5 campos (Money / Express / Lan / Pos / Cartões)
            var money = $('#comiss_m');
            var express = $('#comiss_e');
            var lan = $('#comiss_l');
            var pos = $('#comiss_p');
            var cartoes = $('#comiss_c');

            if (parseInt(money.val()) <= 0) {
                money.val('0');
            }
            if (parseInt(express.val()) <= 0) {
                express.val('0');
            }
            if (parseInt(lan.val()) <= 0) {
                lan.val('0');
            }
            if (parseInt(pos.val()) <= 0) {
                pos.val('0');
            }
            if (parseInt(cartoes.val()) <= 0) {
                cartoes.val('0');
            }

            money.val(normalizarComissao(money.val()));
            express.val(normalizarComissao(express.val()));
            lan.val(normalizarComissao(lan.val()));
            pos.val(normalizarComissao(pos.val()));
            cartoes.val(normalizarComissao(cartoes.val()));

        } else {
            var tipo_comissao = $('#co_volume_tipo');
            if ( tipo_comissao.val() == '1' ) {
                var combos = $('select[name="co_volume_tipo_dado[]"]');
                if ( combos.length ) {
                    for (var i = 0; i < combos.length; i++ ) {
                        if ( combos[i].value == '' ) {
                            alert('Não é permitido deixar nenhuma divisão de venda não selecionada.');
                            $('#' + combos[i].id).css('border', '1px solid red');
                            return false;
                        }
                    }
                }
            }
        }

        $('#submit_comissao_span').html('Aguarde...');
        $(document).ready(function () {
            document.frmPreCadastro.atualizar.value = 'OK';
            $.ajax({
                type: "POST",
                url: "/ajax/ajaxAlterarComissoes.php",
                data: showValuesAlterar(),
                beforeSend: function () {
                    //$('#box-comisao').html("<center><table><tr><td><img src='http://www.e-prepag.com.br/prepag2/dist_commerce/images/loading1.gif' border='0' title='Aguardando pagamento...'/></td></tr><tr><td><font size='1'><b>Aguarde... Verificando.</b></font></td></tr></table></center>");
                },
                success: function (html) {
                    //$('#box-comisao').html(html);
                    //alert(html);
                    location.reload(true);
                },
                error: function () {
                    alert('Erro Valor');
                }
            });
        });
    }

    // Edita comissões
    function change() {
        $(document).ready(function () {
            $.ajax({
                type: "POST",
                url: "/ajax/ajaxAlterarComissoes.php",
                data: showValuesAlterar(),
                beforeSend: function () {
                    $('#box-comisao').html("<center><table><tr><td><img src='/images/pdv/loading1.gif' border='0' title='Aguardando pagamento...'/></td></tr><tr><td><font size='1'><b>Aguarde... Verificando.</b></font></td></tr></table></center>");
                },
                success: function (html) {
                    $('#box-comisao').html(html);
                },
                error: function () {
                    alert('erro valor');
                }
            });
        });
    }

    function AddLinha(isComissaoDI) {
        var teste_comissao_var = $('input[name="co_comissao_dado[]"]');
        var qtd = 0;
        if (teste_comissao_var.length > 0) {
            qtd = teste_comissao_var.length;
        }

        var newLine = "<tr align='center' id='comissao_linha_"+qtd+"'>";
        if (isComissaoDI) {
            newLine += "<td>"
            + "<select name='co_volume_tipo_dado[]' id='co_volume_tipo_dado_"+qtd+"' class='combo_normal'>"
            + "<option value='' >Selecione</option>"
            <?php foreach ($divisao_vendas as $key => $value) { ?>
                + "<option value='<?php echo $key ?>' ><?php echo $value; ?></option>"
            <?php } ?>
            + "</select>"
             }
        newLine += "</td>"
            + "<td>"
                + "<input name='co_comissao_dado[]' type='text' id='co_comissao_dado_"+qtd+"' size='"+((isComissaoDI)?'6':'12')+"' maxlength='6'"
                + "value='' onBlur='isTipo2(this.value);'>"
            + "</td>"
            + "<td>"
            + "<input name='co_volume_min_dado[]' type='text' id='co_volume_min_dado_"+qtd+"' size='15' maxlength='13'"
            + "value='' onBlur='isTipo2(this.value);'>"
            + "</td>"
            + "<td>"
            + "<input name='co_volume_max_dado[]' type='text' id='co_volume_max_dado_"+qtd+"' size='15' maxlength='13'"
            + "value='' onBlur='isTipo2(this.value);'>"
            + "</td>"
            + "<td>"
            + "<img src='/images/excluir.gif' width='16' height='16' border='0' alt='Adicionar Comissão Variavel' title='Adicionar Comissão Variavel'"
            + "onclick='$(\"#comissao_linha_"+qtd+"\").remove();' style='cursor:pointer;cursor:hand;'>"
        + "</td>"
        + "</tr>";


        $('#body_valores_comissoes').append(newLine);
    }

    // Adiciona Linha
    function adicionaLinha() {
        document.frmPreCadastro.addlinha.value = 1;
        var teste_comissao_var = $('input[name="co_comissao_dado[]"]');
        var teste_comissao = teste_comissao_var.length;

        if (teste_comissao_var.length > 0) {
            teste_comissao = teste_comissao_var.length - 1;
        }

        var var_comissao_aux = $("input[name=\"co_comissao_dado[]\"]");
        var comissao_aux = "";
        if (var_comissao_aux.length > 0) {
            comissao_aux = var_comissao_aux[teste_comissao].value;
        }

        var var_volume_min_aux = $("input[name=\"co_volume_min_dado[]\"]");
        var volume_min_aux = "";
        if (var_volume_min_aux.length > 0) {
            volume_min_aux = var_volume_min_aux[teste_comissao].value;
        }

        var var_volume_max_aux = $("input[name=\"co_volume_max_dado[]\"]");
        var volume_max_aux = "";
        if (var_volume_max_aux.length > 0) {
            volume_max_aux = var_volume_max_aux[teste_comissao].value;
        }

        // Verificando se existe o combo de vendas diretas e indiretas
        var teste_volume_tipo_dado = $("select[name=\"co_volume_tipo_dado[]\"]");
        var teste_volume_validar = true; // Para nao validar caso nao existe
        if (teste_volume_tipo_dado.length > 0) {
            // Sim, existe
            var t_v;
            if (teste_volume_tipo_dado.length > 1) {
                t_v = teste_volume_tipo_dado[teste_comissao];
            } else {
                t_v = teste_volume_tipo_dado;
            }

            if (t_v.value == "") {
                // nao foi selecionado nada
                teste_volume_validar = false
            }
        }

        if (teste_comissao_var.length > 0 && (comissao_aux == "" || volume_min_aux == "" || volume_max_aux == "" || !teste_volume_validar)) {
            alert("Para adicionar uma nova linha,\nprimeiro deve ser informado os dados completos da linha existente.");
            if (teste_volume_tipo_dado.length) {
                teste_volume_tipo_dado[teste_comissao].focus();
            } else {
                teste_comissao_var[teste_comissao].focus();
            }
        }//end if
        else {
            $(document).ready(function () {
                $.ajax({
                    type: "POST",
                    url: "/ajax/ajaxAlterarComissoes.php",
                    data: showValuesAlterar(),
                    beforeSend: function () {
                        $('#box-comisao').html("<center><table><tr><td><img src='/images/pdv/loading1.gif' border='0' title='Aguardando pagamento...'/></td></tr><tr><td><font size='1'><b>Aguarde... Verificando.</b></font></td></tr></table></center>");
                    },
                    success: function (html) {
                        $('#box-comisao').html(html);
                    },
                    error: function () {
                        alert('erro valor');
                    }
                });
            });
        }//end else
    }
</script>
<table width="90%" border="0" align="center" cellpadding="1" cellspacing="0" style="font-size:10px">
    <?php

    //buscar pelo id da comissão
/*    $sql = "select o.opr_codigo, o.opr_nome, o.opr_comissao_por_volume , c.*,
                    (100*obtem_comissao(opr_codigo, 'M', null, 0)) as comiss_m, 
                    (100*obtem_comissao(opr_codigo, 'E', null, 0)) as comiss_e, 
                    (100*obtem_comissao(opr_codigo, 'L', null, 0)) as comiss_l, 
                    (100*obtem_comissao(opr_codigo, 'C', null, 0)) as comiss_c, 
                    (100*obtem_comissao(opr_codigo, 'P', null, 0)) as comiss_p 
            from operadoras o 
                left outer join tb_comissoes c on o.opr_codigo=c.co_opr_codigo 
            where to_char(co_data_inclusao,'YYYYDDMMHH24MISS') = (select to_char(max(co_data_inclusao),'YYYYDDMMHH24MISS') from tb_comissoes where co_opr_codigo=$opr_codigo) 
                    and opr_codigo = $opr_codigo
                    and co_canal != 'C'
                    -- and opr_comissao_por_volume = 1
            order by co_opr_codigo, co_canal, co_data_inclusao desc,co_tipo, co_volume_tipo, co_volume_min ";

*/
    $sqlComissoesInvariaveis = "SELECT (100*obtem_comissao({$opr_codigo}, 'M', null, 0)) as comiss_m,
(100*obtem_comissao({$opr_codigo}, 'E', null, 0)) as comiss_e,
(100*obtem_comissao({$opr_codigo}, 'L', null, 0)) as comiss_l,
(100*obtem_comissao({$opr_codigo}, 'C', null, 0)) as comiss_c,
(100*obtem_comissao({$opr_codigo}, 'P', null, 0)) as comiss_p";

    $rsComiss = SQLexecuteQuery($sqlComissoesInvariaveis);
    $comissRows = pg_fetch_array($rsComiss);



    $sql = "select o.opr_codigo, o.opr_nome, o.opr_comissao_por_volume , c.*
from operadoras o
left outer join tb_comissoes c on o.opr_codigo=c.co_opr_codigo
where  opr_codigo = {$opr_codigo}
AND to_char(co_data_inclusao,'YYYYDDMMHH24MISS') = (select to_char(max(co_data_inclusao),'YYYYDDMMHH24MISS') from tb_comissoes where co_opr_codigo=$opr_codigo)
    and co_canal != 'C'
    AND co_tipo != 'F'
order by co_opr_codigo, co_canal, co_data_inclusao ASC,co_tipo, co_volume_tipo, co_volume_min;";
/*
    $sqlAllComissoes = 'SELECT c.*, o.opr_comissao_por_volume
 FROM tb_comissoes c
INNER JOIN operadoras o ON o.opr_codigo=c.co_opr_codigo
WHERE c.co_opr_codigo=99
AND c.co_canal != \'C\'
AND c.co_tipo != \'F\'
ORDER BY co_canal, co_data_inclusao DESC,co_tipo, co_volume_tipo, co_volume_min';
    */


    //echo $sql;
    $rs_comissao = SQLexecuteQuery($sql);
    $rs_comissao_row = pg_fetch_array($rs_comissao);

    //Auxiliar para teste se tem selecionado ou no BD o volume_tipo
    $auxiliar_volume_tipo = trim($rs_comissao_row["co_volume_tipo"]);
    if (empty($auxiliar_volume_tipo) && is_null($co_volume_tipo)) {
        $auxiliar_volume_tipo = 0;
    } elseif ($co_volume_tipo === '0') {
        $auxiliar_volume_tipo = 0;
    } else {
        $auxiliar_volume_tipo = 1;
    }

    //Auxiliar para teste se tem selecionado ou no BD o opr_comissao_por_volume
    $auxiliar_comissao_por_volume = trim($rs_comissao_row["opr_comissao_por_volume"]);
    if (empty($auxiliar_comissao_por_volume) && is_null($opr_comissao_por_volume)) {
        $auxiliar_comissao_por_volume = 0;
    } elseif ($opr_comissao_por_volume === '0') {
        $auxiliar_comissao_por_volume = 0;
    } else {
        $auxiliar_comissao_por_volume = 1;
    }
    ?>
    <tr>
        <td colspan="10">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="3">&nbsp;Tipo de Comiss&atilde;o:
        </td>
        <td colspan="7">
            <select name="opr_comissao_por_volume" id="opr_comissao_por_volume" class="combo_normal"
                    onchange="change();">
                <?php foreach ($tipos_comissoes as $key => $value) { ?>
                    <option
                        value="<?php echo $key ?>"
                        <?php echo (
                                ($key == $rs_comissao_row["opr_comissao_por_volume"] && is_null($opr_comissao_por_volume) ||
                                $key == $opr_comissao_por_volume) ? 'selected':'' )?>>
                        <?php echo "({$key}) {$value}"; ?></option>
                <?php } ?>
            </select>
        </td>
    </tr>
    <?php if ($auxiliar_comissao_por_volume) { ?>
        <tr>
            <td colspan="3">&nbsp;Vendas:
            </td>
            <td colspan="7">
                <select name="co_volume_tipo" id="co_volume_tipo" class="combo_normal" onchange="change();">
                    <?php foreach ($tipos_vendas as $key => $value) { ?>
                        <option
                            value="<?php echo $key ?>" <?php if ($key == $auxiliar_volume_tipo || $key == $co_volume_tipo) echo "selected" ?>><?php echo "(" . $key . ") " . $value; ?></option>
                    <?php } ?>
                </select>
            </td>
        </tr>
        <tr>
            <td colspan="3">&nbsp;</td>
            <td colspan="7">
                <?php /* th da tabela */
                $tam_colspan = 3;
                ?>
                <table border='1' cellpadding='0' cellspacing='1' bordercolor='#cccccc' style='border-collapse:collapse;font-family:Arial, Helvetica, sans-serif; font-size:10px;'>
                    <thead>
                        <tr style='text-align:center;background-color:#ffffcc;'>
                            <?php if ( $auxiliar_volume_tipo == 1 ) {$tam_colspan = 4;?><th style="padding: 5px;">Divisão de vendas</th><?php } ?>
                            <th style="padding: 13px;">Comissão</th>
                            <th style="padding: 13px;">Volume Min</th>
                            <th style="padding: 13px;">Volume max</th>
                            <th style="padding: 13px;">Excluir linha</th>
                        </tr>
                    </thead>

                    <tbody id="body_valores_comissoes">
                    <?php
                    $rs_comissao = SQLexecuteQuery($sql);
                    $i = 0;
                    while ($rs_row = pg_fetch_array($rs_comissao)){
                        $trStyle = ($rs_row['co_comissao'] == 0) ? ' style=\'background-color:yellow; color:red\'' : '';
                        ?>
                        <tr align="center" <?php echo $trStyle;?> id="comissao_linha_<?php echo $i;?>">
                            <?php if ($auxiliar_volume_tipo == 1) {?>
                            <td>
                                <select name='co_volume_tipo_dado[]' id='co_volume_tipo_dado_<?php echo $i;?>' class='combo_normal'>
                                    <option value='' >Selecione</option>
                                    <?php foreach ($divisao_vendas as $key => $value) { ?>
                                    <option
                                        value='<?php echo $key ?>' <?php if ($key == $rs_row["co_volume_tipo"] || $key == $co_volume_tipo_dado[$i]) echo "selected"; ?>><?php echo $value; ?></option>
                                    <?php } ?>
                                </select>
                            <?php } ?>
                            </td>
                            <td>
                                <input name='co_comissao_dado[]' type='text' id='co_comissao_dado_<?php echo $i;?>' size='<?php echo ($auxiliar_volume_tipo==0)?'12':'6';?>' maxlength='16'
                                       value='<?php echo ($co_comissao_dado[$i]) ? $co_comissao_dado[$i] : $rs_row['co_comissao'];?>' onBlur='isTipo2(this.value);'>
                            </td>
                            <td>
                                <input name='co_volume_min_dado[]' type='text' id='co_volume_min_dado_<?php echo $i;?>' size='15' maxlength='13'
                                       value='<?php echo ($co_volume_min_dado[$i]) ? $co_volume_min_dado[$i] : number_format($rs_row['co_volume_min'], 2, ',', '');?>' onBlur='isTipo2(this.value);'>
                            </td>
                            <td>
                                <input name='co_volume_max_dado[]' type='text' id='co_volume_max_dado_<?php echo $i;?>' size='15' maxlength='13'
                                       value='<?php echo ($co_volume_max_dado[$i]) ? $co_volume_max_dado[$i] : number_format($rs_row['co_volume_max'], 2, ',', '');?>' onBlur='isTipo2(this.value);'>
                            </td>
                            <td>
                                <img src='../images/excluir.gif' width='16' height='16' border='0' alt='Adicionar Comissão Variavel' title='Adicionar Comissão Variavel'
                                     onclick="$('#comissao_linha_<?php echo $i;?>').remove();" style='cursor:pointer;cursor:hand;'>
                            </td>
                        </tr>
                    <?php
                        $i++;
                    }
                    ?>
                    </tbody>
                    <tbody>
                    <tr>
                        <td colspan='<?php echo $tam_colspan;?>' style="border: 0;">&nbsp;</td>
                        <td align='center' style="border: 0;">
                            <img src='../images/add.gif' width='16' height='16' border='0' alt='Adicionar Comissão Variavel' title='Adicionar Comissão Variavel' onclick='AddLinha(<?php echo $auxiliar_volume_tipo == 1?>);' style='cursor:pointer;cursor:hand;'>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    <?php } ?>
    <?php // Bloco abaixo: 651-770 ?>
    <?php /*
    <?php if ($auxiliar_comissao_por_volume) { ?>

    <tr>
        <td colspan="3">&nbsp;
        </td>
        <td colspan="7">
            <?php
            echo "<table border='1' cellpadding='0' cellspacing='1' bordercolor='#cccccc' style='border-collapse:collapse;font-family:Arial, Helvetica, sans-serif; font-size:10px;'>\n
            <tr style='text-align:center;font-weight:bold;background-color:#ffffcc'>
            ";
            if ($auxiliar_volume_tipo == 1) {
                $tam_colspan = 4;
                echo "    <td>Divisão de Venda</td>";
            } else $tam_colspan = 3;
            echo "        <td>Comissao</td>
                <td>Volume Min</td>
                <td>Volume Max</td>
            </tr>\n";
            //echo $sql;exit;
            $rs_comissao = SQLexecuteQuery($sql);
            $i = 0;
            while ($rs_row = pg_fetch_array($rs_comissao)){
            echo "<tr align='center'" . (($rs_row['co_comissao'] == 0) ? " style='background-color:yellow; color:red'" : "") . ">";
            if ($auxiliar_volume_tipo == 1) {
            echo "    <td>
                        <select name='co_volume_tipo_dado[]' id='co_volume_tipo_dado[]' class='combo_normal'>
                            <option value='' >Selecione</option>
                        ";
            foreach ($divisao_vendas as $key => $value) {
                ?>
                <option
                    value='<?php echo $key ?>' <?php if ($key == $rs_row["co_volume_tipo"] || $key == $co_volume_tipo_dado[$i]) echo "selected"; ?>><?php echo $value; ?></option>
            <?php
            } //end foreach
            ?>
            </select>
        </td>
        <?php
        }
        echo "
                <td><input name='co_comissao_dado[]' type='text' id='co_comissao_dado[]' size='6' maxlength='6' value='";
        if ($co_comissao_dado[$i])
            echo $co_comissao_dado[$i];
        else echo $rs_row['co_comissao'];
        echo "' onBlur='isTipo2(this.value);'/></td>";
        echo "
                <td><input name='co_volume_min_dado[]' type='text' id='co_volume_min_dado[]' size='15' maxlength='13' value='";
        if ($co_volume_min_dado[$i])
            echo $co_volume_min_dado[$i];
        else echo number_format($rs_row['co_volume_min'], 2, ',', '');
        echo "' onBlur='isTipo2(this.value);'/></td>";
        echo "
                <td><input name='co_volume_max_dado[]' type='text' id='co_volume_max_dado[]' size='15' maxlength='13' value='";
        if ($co_volume_max_dado[$i])
            echo $co_volume_max_dado[$i];
        else echo number_format($rs_row['co_volume_max'], 2, ',', '');
        echo "' onBlur='isTipo2(this.value);'/></td>";

        echo "
            </tr>\n";
        $i++;
        }
        for ($j = $i; $j < count($co_comissao_dado); $j++) {
            echo "<tr align='center'>"; //<td>i[$i] j[$j] count [".count($co_comissao_dado)."]</td>
            if ($auxiliar_volume_tipo == 1) {
                echo "    <td>
                        <select name='co_volume_tipo_dado[]' id='co_volume_tipo_dado[]' class='combo_normal'>
                            <option value='' >Selecione</option>
                        ";
                foreach ($divisao_vendas as $key => $value) {
                    ?>
                    <option
                        value='<?php echo $key ?>' <?php if ($key == $co_volume_tipo_dado[$j]) echo "selected"; ?>><?php echo $value; ?></option>
                <?php
                } //end foreach
                ?>
                        </select>
                    </td>
            <?php
            }
            echo "
                <td><input name='co_comissao_dado[]' type='text' id='co_comissao_dado[]' size='6' maxlength='6' value='" . $co_comissao_dado[$j] . "' onBlur='isTipo2(this.value);'/></td>
                <td><input name='co_volume_min_dado[]' type='text' id='co_volume_min_dado[]' size='15' maxlength='13' value='" . $co_volume_min_dado[$j] . "' onBlur='isTipo2(this.value);'/></td>
                <td><input name='co_volume_max_dado[]' type='text' id='co_volume_max_dado[]' size='15' maxlength='13' value='" . $co_volume_max_dado[$j] . "' onBlur='isTipo2(this.value);'/></td>
            </tr>\n";
        }
        if (!empty($addlinha)) {
            echo "<tr align='center'>";
            if ($auxiliar_volume_tipo == 1) {
                echo "    <td>
                        <select name='co_volume_tipo_dado[]' id='co_volume_tipo_dado[]' class='combo_normal'>
                            <option value='' >Selecione</option>
                        ";
                foreach ($divisao_vendas as $key => $value) {
                    ?>
                    <option value='<?php echo $key ?>'><?php echo $value; ?></option>
                <?php
                } //end foreach
                ?>
                        </select>
                    </td>
            <?php
            }
            echo "    <td><input name='co_comissao_dado[]' type='text' id='co_comissao_dado[]' size='6' maxlength='6' value='' onBlur='isTipo2(this.value);'/></td>
                <td><input name='co_volume_min_dado[]' type='text' id='co_volume_min_dado[]' size='15' maxlength='13' value='' onBlur='isTipo2(this.value);'/></td>
                <td><input name='co_volume_max_dado[]' type='text' id='co_volume_max_dado[]' size='15' maxlength='13' value='' onBlur='isTipo2(this.value);'/></td>
            </tr>\n";
        }//end if(!empty($addlinha))
        echo "<tr>
<td colspan='$tam_colspan' align='right'>
                <img src='../images/excluir.gif' width='16' height='16' border='0' alt='Adicionar Comissão Variavel' title='Adicionar Comissão Variavel' onclick='javascript:adicionaLinha();' style='cursor:pointer;cursor:hand;'>
            <img src='../images/add.gif' width='16' height='16' border='0' alt='Adicionar Comissão Variavel' title='Adicionar Comissão Variavel' onclick='javascript:adicionaLinha();' style='cursor:pointer;cursor:hand;'>
            </td>
        </tr>\n
    </table>\n";
        ?>
        </td>
        <?php
        }//end if($auxiliar_comissao_por_volume)
*/
        ?>
    </tr>
    <tr>
        <td colspan="10">&nbsp;</td>
    </tr>
    <tr>
        <?php
        if (!$auxiliar_comissao_por_volume) {
            ?>
            <td align="right">Money:</td>
            <td><input name="comiss_m" type="text" id="comiss_m" size="10" maxlength="16"
                       value="<?php echo(!empty($comissRows["comiss_m"]) ? $comissRows["comiss_m"] : "0"); ?>"
                       onBlur="isTipo2(this.value);"/></td>
            <td align="right">Express:</td>
            <td><input name="comiss_e" type="text" id="comiss_e" size="10" maxlength="16"
                       value="<?php echo(!empty($comissRows["comiss_e"]) ? $comissRows["comiss_e"] : "0"); ?>"
                       onBlur="isTipo2(this.value);"/></td>
            <td align="right">LAN:</td>
            <td><input name="comiss_l" type="text" id="comiss_l" size="10" maxlength="16"
                       value="<?php echo(!empty($comissRows["comiss_l"]) ? $comissRows["comiss_l"] : "0"); ?>"
                       onBlur="isTipo2(this.value);"/></td>
            <td align="right">POS:</td>
            <td><input name="comiss_p" type="text" id="comiss_p" size="10" maxlength="16"
                       value="<?php echo(!empty($comissRows["comiss_p"]) ? $comissRows["comiss_p"] : "0"); ?>"
                       onBlur="isTipo2(this.value);"/></td>
        <?php
        }//end if(!$auxiliar_comissao_por_volume)
        ?>
        <td align="right">Cartões:</td>
        <td><input name="comiss_c" type="text" id="comiss_c" size="10" maxlength="16"
                   value="<?php echo(!empty($comissRows["comiss_c"]) ? $comissRows["comiss_c"] : "0"); ?>"
                   onBlur="isTipo2(this.value);"/></td>
    </tr>
    <tr>
        <td colspan="10">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="10" align="right">
            <input type="hidden" name="opr_codigo" id="opr_codigo" value="<?php echo $opr_codigo; ?>"/>
            <input type="hidden" name="atualizar" id="atualizar" value=""/>
            <input type="hidden" name="addlinha" id="addlinha" value=""/>
            <input type="hidden" name="user_backoffice" id="user_backoffice" value="<?php echo $user_backoffice; ?>"/>
            <span id="submit_comissao_span"><img id="submit_comissao" src="/images/finalizar_edicao.gif" width="67" height="22" border="0" alt="Alterar Comissoes"
                 title="Alterar Comissoes" onclick="MM_reload_alterar();"
                 style="cursor:pointer;cursor:hand;"></span>
        </td>
    </tr>
</table>
