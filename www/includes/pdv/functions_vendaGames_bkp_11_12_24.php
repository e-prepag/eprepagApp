<?php
$bHTML = null;
$cReturn = PHP_EOL;
$cSpaces = "    ";

if ($_SERVER["REMOTE_ADDR"] == "201.93.162.169") {
    //ini_set('display_errors', 1);
    //ini_set('display_startup_errors', 1);
    //error_reporting(E_ALL);
}
require_once "/www/db/connect.php";
require_once "/www/db/ConnectionPDO.php";
require_once "/www/includes/gamer/chave.php";
require_once "/www/includes/gamer/AES.class.php";
require_once "/www/class/classGeraPin.php";

function processaVendaGamesValidacao($venda_id, $usuario_id)
{
    $msg = "";

    //obtem usuario
    if ($msg == "") {
        $objUsuarioGames = (new UsuarioGames())->getUsuarioGamesById(
            $usuario_id
        );
        if ($objUsuarioGames == null) {
            $msg = "Nenhum usuário encontrado." . PHP_EOL;
        } else {
            $perfilLimite = $objUsuarioGames->getPerfilLimite();
            $perfilSaldo = $objUsuarioGames->getPerfilSaldo();
        }
    }

    //Recupera modelos
    if ($msg == "") {
        $sql =
            "select * from tb_dist_venda_games vg " .
            "inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id " .
            "where vg.vg_id = " .
            $venda_id;
        $rs_venda_modelos = SQLexecuteQuery($sql);
        if (!$rs_venda_modelos || pg_num_rows($rs_venda_modelos) == 0) {
            $msg = "Nenhum produto encontrado.(4335a)" . PHP_EOL;
        }
    }

    //obtem total repasse
    if ($msg == "") {
        while ($rs_venda_modelos_row = pg_fetch_array($rs_venda_modelos)) {
            $valor = $rs_venda_modelos_row["vgm_valor"];
            $qtde = $rs_venda_modelos_row["vgm_qtde"];
            $perc_desconto = $rs_venda_modelos_row["vgm_perc_desconto"];

            $geral = $valor * $qtde;
            $desconto = ($geral * $perc_desconto) / 100;
            $repasse = $geral - $desconto;

            $qtde_total += $qtde;
            $total_geral += $geral;
            $total_desconto += $desconto;
            $total_repasse += $repasse;
        }
    }

    //Valida saldo
    if ($msg == "") {
        if ($total_repasse > $perfilSaldo + $perfilLimite) {
            $msg = "Saldo insuficiente." . PHP_EOL;
        }
    }

    //Valida pagamentos em aberto
    return $msg;
}

function verificaEstoque($venda_id)
{
    $ff = fopen("/www/log/livrodjx.txt", "a+");

    fwrite($ff, "Chegou no Verifica Estoque " . $venda_id . "\n");

    $msg = "";

    //Recupera modelos
    if ($msg == "") {
        $sql =
            "select * from tb_dist_venda_games vg 
                                inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                                inner join operadoras ope on ope.opr_codigo = vgm.vgm_opr_codigo
                                where vg.vg_id = " . $venda_id;
        $rs_venda_modelos = SQLexecuteQuery($sql);
        if (!$rs_venda_modelos || pg_num_rows($rs_venda_modelos) == 0) {
            $msg = "Nenhum produto encontrado.(4335b)" . PHP_EOL;
        }
    }

    if ($msg == "") {
        //Verifica cada item de cada produro
        while ($rs_venda_modelos_row = pg_fetch_array($rs_venda_modelos)) {
            $vgm_qtde = $rs_venda_modelos_row["vgm_qtde"];
            $vgm_opr_codigo = $rs_venda_modelos_row["vgm_opr_codigo"];
            $vgm_pin_valor = $rs_venda_modelos_row["vgm_pin_valor"];
            $opr_nome = $rs_venda_modelos_row["opr_nome"];
            $vgm_pin_request = $rs_venda_modelos_row["vgm_pin_request"];

            //PINS
            //---------------------------------------------------------------------------------------------------
            if ($vgm_pin_request == 0) {
                $sql =
                    "select count(*) as pins_qtde from pins
                                                where opr_codigo = " .
                    $vgm_opr_codigo .
                    "
                                                        and pin_status = '1'
                                                        and pin_valor = " .
                    $vgm_pin_valor;
                $rs_pins = SQLexecuteQuery($sql);
                if (!$rs_pins || pg_num_rows($rs_pins) == 0) {
                    $msg .=
                        "Não há pin de " .
                        number_format($vgm_pin_valor, 2, ",", ".") .
                        " da operadora " .
                        $opr_nome .
                        " em estoque." .
                        PHP_EOL;
                } else {
                    $rs_pins_row = pg_fetch_array($rs_pins);
                    $pins_qtde = $rs_pins_row["pins_qtde"];
                    if ($pins_qtde < $vgm_qtde) {
                        $msg .=
                            "Não há pin de " .
                            number_format($vgm_pin_valor, 2, ",", ".") .
                            " da operadora " .
                            $opr_nome .
                            " em estoque." .
                            PHP_EOL;
                    }
                }
            } //end if($vgm_pin_request == 0)
        }
    }

    fwrite($ff, "mensagem: " . $msg . "\n");
    fclose($ff);

    return $msg;
}

function processaVendaGames($venda_id, $EstabCod, $parametros)
{
    set_time_limit(0);
    global $raiz_do_projeto;

    $bDebug = false;

    if ($bDebug) {
        $time_start_stats = getmicrotime();
        //	echo "Elapsed time (): ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.').PHP_EOL;
    }

    if ($bDebug) {
        echo PHP_EOL;
        echo "=====================================================================" .
            PHP_EOL;
        echo "Em processaVendaGames() " . date("d/m/Y - H:i:s") . PHP_EOL;
    }

    $msg = "";

    //Recupera a venda
    if ($msg == "") {
        $sql =
            "select * from tb_dist_venda_games vg inner join tb_dist_venda_games_modelo on vgm_vg_id = vg.vg_id where vg.vg_id = " .
            $venda_id;
        if ($bDebug) {
            echo $sql . PHP_EOL;
        }

        $rs_venda = SQLexecuteQuery($sql);
        if (!$rs_venda || pg_num_rows($rs_venda) == 0) {
            $msg = "Nenhuma venda encontrada." . PHP_EOL;
        } else {
            $rs_venda_row = pg_fetch_array($rs_venda);
            $vg_ug_id = $rs_venda_row["vg_ug_id"];
            $vg_ultimo_status = $rs_venda_row["vg_ultimo_status"];
            $vg_pagto_tipo = $rs_venda_row["vg_pagto_tipo"];
            $vg_somente_debito = $rs_venda_row["vg_somente_debito"];
            $produto_selecionado = $rs_venda_row["vgm_ogp_id"];

            //valida status
            if (
                $vg_ultimo_status !=
                $GLOBALS["STATUS_VENDA"]["AGUARDANDO_PROCESSAMENTO"]
            ) {
                $msg =
                    "Venda não esta no status de " .
                    $GLOBALS["STATUS_VENDA_DESCRICAO"][
                        $GLOBALS["STATUS_VENDA"]["AGUARDANDO_PROCESSAMENTO"]
                    ] .
                    PHP_EOL;

                //Agendamento
                if (
                    $vg_ultimo_status ==
                    $GLOBALS["STATUS_VENDA"]["PROCESSAMENTO_REALIZADO"] ||
                    $vg_ultimo_status ==
                    $GLOBALS["STATUS_VENDA"]["VENDA_REALIZADA"]
                ) {
                    $parametros["agendamento_acao"] = "cancelar";
                    $parametros["agendamento_acao_obs"] =
                        "Venda já foi processada." . PHP_EOL;
                } elseif (
                    $vg_ultimo_status ==
                    $GLOBALS["STATUS_VENDA"]["VENDA_CANCELADA"]
                ) {
                    $parametros["agendamento_acao"] = "cancelar";
                    $parametros["agendamento_acao_obs"] =
                        "Venda esta cancelada." . PHP_EOL;
                }
            }
        }
    }

    //Verifica validadacao do usuario
    if ($msg == "") {
        if ($bDebug) {
            echo "processaVendaGamesValidacao($venda_id, $vg_ug_id) - " .
                date("d/m/Y - H:i:s") .
                PHP_EOL;
        }
        $msg = processaVendaGamesValidacao($venda_id, $vg_ug_id);

        //Agendamento
        $parametros["agendamento_acao"] = "repetir_em";
        $parametros["agendamento_acao_repetir_em"] = "01";
    }

    //Verifica estoque
    if ($msg == "" && $vg_somente_debito == 0 && $produto_selecionado != 488) {
        if ($bDebug) {
            echo "verificaEstoque($venda_id) - " .
                date("d/m/Y - H:i:s") .
                PHP_EOL;
        }
        $msg = verificaEstoque($venda_id);

        //Agendamento
        $parametros["agendamento_acao"] = "repetir_em";
        $parametros["agendamento_acao_repetir_em"] = "01";
    }

    //Recupera modelos
    if ($msg == "") {
        $sql =
            "select * from tb_dist_venda_games vg " .
            "inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id " .
            "where vg.vg_id = " .
            $venda_id;
        if ($bDebug) {
            echo $sql . PHP_EOL;
        }
        if ($bDebug) {
            echo "Recupera modelos($venda_id) - " .
                date("d/m/Y - H:i:s") .
                PHP_EOL;
        }
        $rs_venda_modelos = SQLexecuteQuery($sql);
        if (!$rs_venda_modelos || pg_num_rows($rs_venda_modelos) == 0) {
            $msg = "Nenhum produto encontrado.(4335c)" . PHP_EOL;
        }
    }

    //Recupera dados do usuario
    if ($msg == "") {
        if ($bDebug) {
            echo "Recupera dados de usuario($venda_id) - " .
                date("d/m/Y - H:i:s") .
                PHP_EOL;
        }
        $objUsuarioGames = (new UsuarioGames())->getUsuarioGamesById($vg_ug_id);
        if ($objUsuarioGames == null) {
            $msg = "Nenhum usuário encontrado." . PHP_EOL;
        } else {
            $ug_cel_ddd = $objUsuarioGames->getCelDDD();
            $ug_cel = $objUsuarioGames->getCel();
            if (!is_numeric($ug_cel_ddd)) {
                $ug_cel_ddd = null;
            }
            if (!is_numeric(str_replace("-", "", $ug_cel))) {
                $ug_cel = null;
            }
        }
    }

    //Enquanto nao tem deposito e boleto

    $data_corrente = date("Y/m/d");
    $hora_corrente = date("H:i:s");

    //Inicia transacao
    if ($msg == "") {
        $sql = "BEGIN TRANSACTION ";
        if ($bDebug) {
            echo " AAA1 - Begin transacation (" .
                number_format(getmicrotime() - $time_start_stats, 2, ".", ".") .
                ")" .
                PHP_EOL;
        }
        $ret = SQLexecuteQuery($sql);
        if (!$ret) {
            $msg = "Erro ao iniciar transação." . PHP_EOL;
        }
    }

    if ($msg == "") {
        $total_venda = 0;
        $total_repasse = 0;
        $s_info_venda = "";
        //Realiza uma venda para cada item de cada produto
        while ($rs_venda_modelos_row = pg_fetch_array($rs_venda_modelos)) {
            $vgm_id = $rs_venda_modelos_row["vgm_id"];
            $vgm_valor = $rs_venda_modelos_row["vgm_valor"];
            $vgm_qtde = $rs_venda_modelos_row["vgm_qtde"];
            $vgm_opr_codigo = $rs_venda_modelos_row["vgm_opr_codigo"];
            $vgm_pin_valor = $rs_venda_modelos_row["vgm_pin_valor"];
            $vgm_game_id = $rs_venda_modelos_row["vgm_game_id"];
            $vgm_perc_desconto = $rs_venda_modelos_row["vgm_perc_desconto"];

            $vgm_nome_produto = $rs_venda_modelos_row["vgm_nome_produto"];

            $vgm_pin_request = $rs_venda_modelos_row["vgm_pin_request"];
            $vgm_ogp_id = $rs_venda_modelos_row["vgm_ogp_id"];
            $vgm_valor_pin = $rs_venda_modelos_row["vgm_valor"];

            $s_info_venda .=
                " ('$vgm_nome_produto' opr:$vgm_opr_codigo, $vgm_qtde x R$" .
                number_format($vgm_valor, 2, ",", ".") .
                ")";

            $total_venda += $vgm_valor * $vgm_qtde;

            //valor liquido de repasse
            $vgm_valor = $vgm_valor - ($vgm_valor * $vgm_perc_desconto) / 100;
            $total_repasse += $vgm_valor * $vgm_qtde;

            if ($bDebug) {
                echo " AAA+++ - Uma venda para cada item de cada produto (valor: $vgm_valor) (" .
                    number_format(
                        getmicrotime() - $time_start_stats,
                        2,
                        ".",
                        "."
                    ) .
                    ")" .
                    PHP_EOL;
            }

            
            $verificaExist = SQLexecuteQuery("SELECT COUNT(*) AS qtde FROM tb_dist_venda_games_modelo_pins WHERE vgmp_vgm_id = $vgm_id;");

            // Verifique se a consulta foi bem-sucedida e obtemos um recurso de resultado
            if ($verificaExist) {
                    // Extrai o valor da contagem usando pg_fetch_assoc
                    $row = pg_fetch_assoc($verificaExist);
                    $qtde = $row['qtde'];
                    if ($qtde >= $vgm_qtde) {
                            continue;
                    }
            }

            //Realiza n qtde de venda de pins
            for ($i = 0; $i < $vgm_qtde; $i++) {


                //Sai se houve algum erro
                if ($msg != "") {
                    break;
                }

                if ($vgm_ogp_id == 488) {
                    $ff = fopen("/www/log/livrodjx.txt", "a+");

                    fwrite($ff, "Chegou no if" . $vgm_ogp_id . " venda: " . $venda_id . "\n");

                    fclose($ff);
                    try {
                        $geraPinEpp = new GeraPinVariavel($vgm_pin_valor, 53, 3, 1);

                        $pin_codinterno = $geraPinEpp->gerar();

                        $ff = fopen("/www/log/livrodjx.txt", "a+");

                        fwrite($ff, "PIN GERADO NESSE CARAI: " . $pin_codinterno . "\n");
                        fwrite($ff, "VGM_ID: " . $vgm_id . "\n");


                        $sql =
                            "update tb_dist_venda_games_modelo set 
									vgm_pin_codinterno = coalesce(vgm_pin_codinterno,'') || '" .
                            $pin_codinterno .
                            ",' 
									where vgm_id = '" .
                            $vgm_id .
                            "'";
                        $ret = SQLexecuteQuery($sql);
                        if (!$ret) {
                            $msg =
                                "Erro ao atualizar pin no modelo vendido." .
                                PHP_EOL;
                        }

                        $sql =
                            "insert into tb_dist_venda_games_modelo_pins (vgmp_vgm_id, vgmp_pin_codinterno) values (" .
                            $vgm_id .
                            "," .
                            $pin_codinterno .
                            ")";
                        $ret = SQLexecuteQuery($sql);
                        if (!$ret) {
                            $msg =
                                "Erro ao associar pin no modelo vendido." . PHP_EOL;
                        }

                        $sql = "update pins set pin_status = '6' where pin_codinterno = " . $pin_codinterno;
                        $ret = SQLexecuteQuery($sql);

                        if (!$ret)
                            $msg = "Erro ao atualizar tabela de pins." . PHP_EOL;
                        elseif (pg_affected_rows($ret) > 1)
                            $msg = "Erro ao atualizar mais de um pin na tabela de pins." . PHP_EOL;
                        elseif (pg_affected_rows($ret) == 0) {
                            $i--;
                            continue;
                        }
                    } catch (Exception $err) {
                        $ff = fopen("/www/log/livrodjx.txt", "a+");

                        fwrite($ff, "Gerar PIN deu erro: " . $err->getMessage() . "\n");

                        fclose($ff);
                    }

                } else {
                    //Sleep para nao sobrecarregar o servidor
                    //sleep(1);
                    //FALA SÉRIO GENTE: Eu NÃO Tive que comentar isso!!!
                    //usleep(0.1*1000000);

                    if ($bDebug) {
                        echo " AAA+++ - usleep (iter: $i) (" .
                            number_format(
                                getmicrotime() - $time_start_stats,
                                2,
                                ".",
                                "."
                            ) .
                            ")" .
                            PHP_EOL;
                    }

                    //PINS
                    //---------------------------------------------------------------------------------------------------
                    // Captura o primeiro pin válido
                    if (
                        $msg == "" &&
                        $vg_somente_debito == 0 &&
                        $vgm_pin_request == 0
                    ) {
                        $ff = fopen("/www/log/livrodjx.txt", "a+");
                        fwrite($ff, "Entra aqui ??? " . $vgm_ogp_id);
                        fclose($ff);
                        // Executa uma verificação se o a senha do pin é zerada, se for exibe o campo pin_caracter
                        $sql =
                            "select * from pins
                                                        where opr_codigo = '" .
                            $vgm_opr_codigo .
                            "'
                                                                and pin_status = '1'
                                                                and pin_canal = 's'
                                                                and pin_valor = " .
                            $vgm_pin_valor .
                            "
                                                        limit 1";
                        if ($bDebug) {
                            echo "SQL pin: " . $sql . PHP_EOL;
                        }
                        $rs_pins = SQLexecuteQuery($sql);
                        if (!$rs_pins || pg_num_rows($rs_pins) == 0) {
                            $msg =
                                "Nenhum pin encontrado ou estoque insuficiente para atender este pedido." .
                                PHP_EOL;
                        } else {
                            $pgpins = pg_fetch_array($rs_pins);
                            $pin_codinterno = $pgpins["pin_codinterno"];
                            $pin_valor = $pgpins["pin_valor"];
                            $pin_serial = $pgpins["pin_serial"];
                        }
                    }

                    if ($bDebug) {
                        echo " AAA+++ - Captura pin válido (iter: $i) (" .
                            number_format(
                                getmicrotime() - $time_start_stats,
                                2,
                                ".",
                                "."
                            ) .
                            ")" .
                            PHP_EOL;
                    }

                    //
                    if (
                        $msg == "" &&
                        $vg_somente_debito == 0 &&
                        $vgm_pin_request == 0
                    ) {
                        $sql =
                            "update pins set 
                                                                pin_status = '6', 
                                                                pin_celular = '" .
                            str_replace("-", "", $ug_cel) .
                            "',
                                                                pin_ddd = " .
                            SQLaddFields($ug_cel_ddd, "") .
                            ",
                                                                pin_datavenda = '" .
                            $data_corrente .
                            "', 
                                                                pin_datapedido = '" .
                            $data_corrente .
                            "', 
                                                                pin_horavenda = '" .
                            $hora_corrente .
                            "',
                                                                pin_horapedido = '" .
                            $hora_corrente .
                            "', 
                                                                pin_est_codigo = '" .
                            $EstabCod .
                            "'
                                                        where pin_codinterno = '" .
                            $pin_codinterno .
                            "' and pin_status = '1'";
                        $ret = SQLexecuteQuery($sql);
                        if (!$ret) {
                            $msg =
                                "Erro ao atualizar tabela de pins." . PHP_EOL;
                        } elseif (pg_affected_rows($ret) > 1) {
                            $msg =
                                "Erro ao atualizar mais de um pin na tabela de pins." .
                                PHP_EOL;
                        } elseif (pg_affected_rows($ret) == 0) {
                            $i--;
                            continue;
                        }
                    }

                    if ($bDebug) {
                        echo " AAA+++ - Atualiza a tabela de pins (iter: $i) (" .
                            number_format(
                                getmicrotime() - $time_start_stats,
                                2,
                                ".",
                                "."
                            ) .
                            ")" .
                            PHP_EOL;
                    }

                    // Atualiza o serial do pin no modelo vendido
                    if (
                        $msg == "" &&
                        $vg_somente_debito == 0 &&
                        $vgm_pin_request == 0
                    ) {
                        $sql =
                            "insert into tb_dist_venda_games_modelo_pins (vgmp_vgm_id, vgmp_pin_codinterno) values (" .
                            $vgm_id .
                            "," .
                            $pin_codinterno .
                            ")";
                        if ($bDebug) {
                            echo "PDV - Atualiza a tabela de pins: " .
                                $sql .
                                PHP_EOL;
                        }
                        $ret = SQLexecuteQuery($sql);
                        if (!$ret) {
                            $msg =
                                "Erro ao associar pin no modelo vendido." .
                                PHP_EOL;
                        }
                    }

                    if ($bDebug) {
                        echo " AAA+++ - Atualiza o serial do pin no modelo vendido (iter: $i) (" .
                            number_format(
                                getmicrotime() - $time_start_stats,
                                2,
                                ".",
                                "."
                            ) .
                            ")" .
                            PHP_EOL;
                    }

                    //ESTABELECIMENTO
                    //---------------------------------------------------------------------------------------------------
                    if ($msg == "") {
                        if ($bDebug) {
                            echo " AAA+++ - Debita valor da venda no estabelecimento (iter: $i) (" .
                                number_format(
                                    getmicrotime() - $time_start_stats,
                                    2,
                                    ".",
                                    "."
                                ) .
                                ")" .
                                PHP_EOL;
                        }
                        if ($bDebug) {
                            echo " AAA+++ - atualiza estabelecimento (iter: $i) (" .
                                number_format(
                                    getmicrotime() - $time_start_stats,
                                    2,
                                    ".",
                                    "."
                                ) .
                                ")" .
                                PHP_EOL;
                        }
                    }

                    //Gerando o registro de requisição de PIN por Webservice
                    if ($vgm_pin_request > 0) {
                        //Para produtos BHN
                        if ($vgm_pin_request == 1) {
                            //Bloco de verificação de produto de valro variável
                            $sql =
                                "select ogp_valor_minimo,ogp_valor_maximo,ogp_nome from tb_dist_operadora_games_produto where ogp_id=" .
                                $vgm_ogp_id .
                                ";";
                            $rs_bhn_variavel = SQLexecuteQuery($sql);
                            $rs_bhn_variavel_row = pg_fetch_array(
                                $rs_bhn_variavel
                            );

                            //Bloco para registro do pedido
                            $sql = "select ogpm_pin_resquest_id,ogpm_provider_id,ogpm_cod_epay
													from tb_dist_operadora_games_produto_modelo 
													where ogpm_ogp_id = $vgm_ogp_id";
                            if (
                                is_null(
                                    $rs_bhn_variavel_row["ogp_valor_minimo"]
                                ) &&
                                is_null(
                                    $rs_bhn_variavel_row["ogp_valor_maximo"]
                                )
                            ) {
                                $sql .= " AND ogpm_valor = " . $vgm_valor_pin;
                            }
                            $sql .= " AND ogpm_ativo = 1 LIMIT 1;";
                            $rs_bhn = SQLexecuteQuery($sql);

                            if ($vgm_opr_codigo == 159) {
                                $rs_bhn_row = pg_fetch_array($rs_bhn);
                                include_once "/www/e-pay/Epay.php";
                                $epay = new Epay();
                                $dados_pedido = [
                                    "code" =>
                                        $rs_bhn_row["ogpm_pin_resquest_id"],
                                    "shopid" => $rs_bhn_row["ogpm_provider_id"],
                                    "model" => $vgm_id,
                                    "operator" => $vgm_opr_codigo,
                                    "retailerid" =>
                                        $rs_bhn_row["ogpm_cod_epay"],
                                    "type_sale" => "PDV",
                                    "value" => $vgm_valor_pin,
                                    "sale" => $venda_id,
                                    "name_prod" =>
                                        $rs_bhn_variavel_row["ogp_nome"],
                                ];
                                $msg = $epay->sale("DIRECT", $dados_pedido);
                            } else {
                                require_once $raiz_do_projeto .
                                    "bhn/config.inc.bhn.php";

                                if ($rs_bhn) {
                                    $rs_bhn_row = pg_fetch_array($rs_bhn);
                                    $rs_api = new classBHN();
                                    $parametros = [
                                        "productId" =>
                                            $rs_bhn_row["ogpm_pin_resquest_id"],
                                        "localTransactionDate" => date("ymd"),
                                        "localTransactionTime" => date("His"),
                                        "retrievalReferenceNumber" => str_pad(
                                            $rs_api->getIdBHN(),
                                            12,
                                            "0",
                                            STR_PAD_LEFT
                                        ),
                                        "systemTraceAuditNumber" => str_pad(
                                            0,
                                            6,
                                            "0",
                                            STR_PAD_LEFT
                                        ),
                                        "transactionAmount" => str_pad(
                                            $vgm_valor_pin * 100,
                                            12,
                                            "0",
                                            STR_PAD_LEFT
                                        ),
                                        "merchantTerminalId" => str_pad(
                                            $vg_ug_id,
                                            5,
                                            "0",
                                            STR_PAD_LEFT
                                        ),
                                        //'transmissionDateTime'	=> date('ymdHms'),
                                        "vg_id" => $venda_id,
                                        "vgm_id" => $vgm_id,
                                        "opr_codigo" => $vgm_opr_codigo,
                                    ];
                                    $rs_api->registroPedido($parametros);
                                } //end if($rs_bhn)
                            }
                        } //end if($vgm_pin_request == 1)
                        //FIM Para produtos BHN
                    } //end if($vgm_pin_request > 0)
                }
            } //end for($i=0; $i < $vgm_qtde; $i++)

            // Insere pins na tabela auxiliar
            if (
                $msg == "" &&
                $vg_somente_debito == 0 &&
                $vgm_pin_request == 0
            ) {
                $sql = "insert into pins_dist 
                                                select * from pins where pin_codinterno in 
                                                        (select vgmp_pin_codinterno from tb_dist_venda_games_modelo_pins where vgmp_vgm_id = $vgm_id)";
                $ret = SQLexecuteQuery($sql);
                if (!$ret) {
                    $msg =
                        "Erro ao inserir pins na tabela auxiliar (S)." .
                        PHP_EOL .
                        "$sql" .
                        PHP_EOL;
                }
            }

            if ($bDebug) {
                echo " AAA+++ - Insere pins na tabela auxiliar (iter: $i) (" .
                    number_format(
                        getmicrotime() - $time_start_stats,
                        2,
                        ".",
                        "."
                    ) .
                    ")" .
                    PHP_EOL;
            }
        }
    }

    //Usuario

    //---------------------------------------------------------------------------------------------------
    //debita valor de repasse do saldo do usuario
    if ($msg == "") {
        $sql =
            "update dist_usuarios_games set 
                                        ug_perfil_saldo = case when ug_perfil_saldo is null then 0 else ug_perfil_saldo end - (" .
            SQLaddFields(ROUND($total_repasse, 2), "") .
            ")
                                where ug_id = " .
            $vg_ug_id;
        $ret = SQLexecuteQuery($sql);
        if (!$ret) {
            $msg = "Erro ao atualizar saldo do usuário." . PHP_EOL;
        } elseif (
            $objUsuarioGames->ug_Substatus == "9" &&
            $total_repasse * 1 != 0
        ) {
            $sql =
                "update dist_usuarios_games set ug_substatus = 11, ug_data_aprovacao = NOW()
                                    where ug_id = " .
                $vg_ug_id .
                ";";
            $retSubStatus = SQLexecuteQuery($sql);
            if (!$retSubStatus) {
                $msg =
                    "Erro ao atualizar atualizar automaticamente o Substatus para 11 (PDV -Aprovado)." .
                    PHP_EOL;
            } else {
                enviaEmail(
                    "rc1@e-prepag.com.br,help@e-prepag.com.br",
                    "rc@e-prepag.com.br,relacionamento@e-prepag.com.br",
                    null,
                    (!checkIP() ? "[PROD]" : "[DEV-HOMOLOG]") .
                    " PDV - Promovido Automáticamente para PDV Aprovado",
                    "PDV de ID [" .
                    $vg_ug_id .
                    "] foi promovido automáticamente para SubStatus PDV Aprovado após a conciliação automática de sua primeira compra. ID do Pedido [" .
                    $venda_id .
                    "]."
                );
            } //end else do if(!$retSubStatus)
        } //end else do if(!$ret)

        if ($bDebug) {
            echo " AAA+++ - debita valor de repasse do saldo do usuario (" .
                number_format(getmicrotime() - $time_start_stats, 2, ".", ".") .
                ")" .
                PHP_EOL;
        }
    }

    //VENDA GAMES
    //---------------------------------------------------------------------------------------------------
    //atualiza status
    if ($msg == "") {
        $sql =
            "update tb_dist_venda_games set 
                                        vg_ultimo_status_obs = " .
            SQLaddFields($parametros["ultimo_status_obs"], "s") .
            ",
                                        vg_ultimo_status = " .
            SQLaddFields(
                $GLOBALS["STATUS_VENDA"]["PROCESSAMENTO_REALIZADO"],
                ""
            ) .
            "
                                where vg_id = " .
            $venda_id;
        $ret = SQLexecuteQuery($sql);
        if (!$ret) {
            $msg = "Erro ao conciliar venda." . PHP_EOL;
        }
        if ($bDebug) {
            echo " AAA+++ - atualiza status (" .
                number_format(getmicrotime() - $time_start_stats, 2, ".", ".") .
                ")" .
                PHP_EOL;
        }
    }

    //Finaliza transacao
    if ($msg == "") {
        $sql = "COMMIT TRANSACTION ";
        $ret = SQLexecuteQuery($sql);
        if (!$ret) {
            $msg = "Erro ao comitar transação." . PHP_EOL;
        }
    } else {
        $sql = "ROLLBACK TRANSACTION ";
        $ret = SQLexecuteQuery($sql);
        if (!$ret) {
            $msg = "Erro ao dar rollback na transação." . PHP_EOL;
        }
    }

    // retorna valor total
    $parametros["total_venda"] = $total_venda;
    $parametros["s_info_venda"] = $s_info_venda;

    if ($bDebug) {
        echo " AAA+++ - Finaliza transacao (" .
            number_format(getmicrotime() - $time_start_stats, 2, ".", ".") .
            ")" .
            PHP_EOL;
    }

    if ($bDebug) {
        echo " AAA2 - " . $sql . " (" . date("d/m/Y - H:i:s") . ")" . PHP_EOL;
    }
    return $msg;
}

function processaEmailVendaGames($venda_id, $parametros)
{
    $msg = "";

    //Recupera a venda
    if ($msg == "") {
        $sql =
            "select * from tb_dist_venda_games vg " .
            "where vg.vg_id = " .
            $venda_id;
        $rs_venda = SQLexecuteQuery($sql);
        if (!$rs_venda || pg_num_rows($rs_venda) == 0) {
            $msg = "Nenhuma venda encontrada." . PHP_EOL;
        } else {
            $rs_venda_row = pg_fetch_array($rs_venda);
            $vg_ug_id = $rs_venda_row["vg_ug_id"];
            $vg_ultimo_status = $rs_venda_row["vg_ultimo_status"];

            //valida status
            if (
                $vg_ultimo_status !=
                $GLOBALS["STATUS_VENDA"]["PROCESSAMENTO_REALIZADO"] &&
                $vg_ultimo_status != $GLOBALS["STATUS_VENDA"]["VENDA_REALIZADA"]
            ) {
                $msg = "Processamento ainda não realizado." . PHP_EOL;
            }
        }
    }

    //Recupera modelos
    if ($msg == "") {
        $sql =
            "select * from tb_dist_venda_games vg " .
            "inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id " .
            "where vg.vg_id = " .
            $venda_id;
        $rs_venda_modelos = SQLexecuteQuery($sql);
        if (!$rs_venda_modelos || pg_num_rows($rs_venda_modelos) == 0) {
            $msg = "Nenhum produto encontrado.(4335d)" . PHP_EOL;
        }
    }

    //Recupera dados do usuario
    if ($msg == "") {
        $sql =
            "select * from dist_usuarios_games ug " .
            "where ug.ug_id = " .
            $vg_ug_id;
        $rs_usuario = SQLexecuteQuery($sql);
        if (!$rs_usuario || pg_num_rows($rs_usuario) == 0) {
            $msg = "Nenhum cliente encontrado." . PHP_EOL;
        } else {
            $rs_usuario_row = pg_fetch_array($rs_usuario);
            $ug_email = $rs_usuario_row["ug_email"];
            $ug_tipo_cadastro = $rs_usuario_row["ug_tipo_cadastro"];
            $ug_sexo = $rs_usuario_row["ug_sexo"];
            $ug_nome = $rs_usuario_row["ug_nome"];
            $ug_cpf = $rs_usuario_row["ug_cpf"];
            $ug_rg = $rs_usuario_row["ug_rg"];
            $ug_nome_fantasia = $rs_usuario_row["ug_nome_fantasia"];
            $ug_cnpj = $rs_usuario_row["ug_cnpj"];
            $ug_endereco = $rs_usuario_row["ug_endereco"];
            $ug_numero = $rs_usuario_row["ug_numero"];
            $ug_complemento = $rs_usuario_row["ug_complemento"];
            $ug_bairro = $rs_usuario_row["ug_bairro"];
            $ug_cidade = $rs_usuario_row["ug_cidade"];
            $ug_estado = $rs_usuario_row["ug_estado"];
            $ug_cep = $rs_usuario_row["ug_cep"];
        }
    }

    //USUARIO
    //---------------------------------------------------------------------------------------------------
    //envia email
    if ($msg == "") {
        $parametros["prepag_dominio"] = "http://www.e-prepag.com.br";
        $parametros["nome_fantasia"] = $ug_nome_fantasia;
        $parametros["tipo_cadastro"] = $ug_tipo_cadastro;
        $parametros["sexo"] = $ug_sexo;
        $parametros["nome"] = $ug_nome;
        //$msgEmail = email_cabecalho($parametros);
        $msgEmailLista = "";

        //Informacoes do pedido
        $sql =
            "select * from tb_dist_venda_games vg " .
            "inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id " .
            "where vg.vg_id = " .
            $venda_id;
        $rs_venda_modelos = SQLexecuteQuery($sql);
        $msgEmailLista .= "
                                <table cellspacing='0' cellpadding='5' width='100%' style='font: normal 13px arial, sans-serif;'>
                                        <tr bgcolor='#CCCCCC'>
                                                <td width='3'>&nbsp;</td>
                                                <td align='left'><b>Jogo</b></td>
                                                <td align='center'><b>Produto</b></td>
                                                <td align='center'><b>Unit.&nbsp;(R$)</b></td>
                                                <td align='center'><b>Qtde</b></td>
                                                <td align='right'><b>Total&nbsp;(R$)</b></td>
                                                <td align='right'><b>Comissão</b></td>
                                                <td width='5'>&nbsp;</td>
                                        </tr>";

        $qtde_total = 0;
        $total_geral = 0;
        $total_desconto = 0;
        $total_repasse = 0;
        $vg_ug_id = "";
        while ($rs_venda_modelos_row = pg_fetch_array($rs_venda_modelos)) {
            // Define operadora
            //if($rs_venda_modelos_row['vgm_ogp_id'] == 5) $blEmailHabbo = true;
            $blEmailHabbo =
                $rs_venda_modelos_row["vgm_opr_codigo"] == 16 ? true : false;
            $blEmailVostu =
                $rs_venda_modelos_row["vgm_opr_codigo"] == 35 ? true : false;

            $blEmailStardoll =
                $rs_venda_modelos_row["vgm_opr_codigo"] == 38 ? true : false;
            $blEmailSoftnyx =
                $rs_venda_modelos_row["vgm_opr_codigo"] == 37 ? true : false;

            $pagto_tipo = $rs_venda_modelos_row["vg_pagto_tipo"];
            $vg_ug_id = $rs_venda_modelos_row["vg_ug_id"];

            $codigo = $rs_venda_modelos_row["vgm_id"];
            $qtde = $rs_venda_modelos_row["vgm_qtde"];
            $valor = $rs_venda_modelos_row["vgm_valor"];
            $perc_desconto = $rs_venda_modelos_row["vgm_perc_desconto"];
            $geral = $valor * $qtde;
            $desconto = ($geral * $perc_desconto) / 100;
            $repasse = $geral - $desconto;

            $qtde_total += $qtde;
            $total_geral += $geral;
            $total_desconto += $desconto;
            $total_repasse += $repasse;
            $msgEmailLista .=
                "
                                                                        <tr bgcolor='#E6E6E6'>
                                                                                <td width='3'>&nbsp;</td>
                                                                                <td align='left'><nobr>" .
                $rs_venda_modelos_row["vgm_nome_produto"] .
                "</nobr></td>
                                                                                <td align='center'>" .
                $rs_venda_modelos_row["vgm_nome_modelo"] .
                "</td>
                                                                                <td align='center'>" .
                number_format($valor, 2, ",", ".") .
                "</td>
                                                                                <td align='center'><b>" .
                $qtde .
                "</b></td>
                                                                                <td align='right'><b>" .
                number_format($geral, 2, ",", ".") .
                "</b></td>
                                                                                <td align='right'><b>" .
                number_format($desconto, 2, ",", ".") .
                "</b></td>
                                                                                <td width='5'>&nbsp;</td>
                                                                        </tr>";
        }

        //Mensagem
        $msgEmailLista .=
            "
                                        <tr bgcolor='F0F0F0'>
                                          <td colspan='3'>&nbsp;</td>
                                          <td align='right' colspan='2'><b>Total</b></td>
                                          <td align='right'><b>" .
            number_format($total_geral, 2, ",", ".") .
            "</b></td>
                                          <td align='right'><b>" .
            number_format($total_desconto, 2, ",", ".") .
            "</b></td>
                                          <td width='5'>&nbsp;</td>
                                        </tr>
                                        <tr  bgcolor='#CCCCCC'>
                                                <td colspan='5'>&nbsp;</td>
                                <td align='right' colspan='2'></td>							
                                                <td width='5'>&nbsp;</td>
                                        </tr>
                                </table>";

        if (b_IsNew_template($vg_ug_id)) {
            $objEnvioEmailAutomatico = new EnvioEmailAutomatico(
                TIPO_USUARIO_LAN,
                "VendaProcessadaLH"
            );
            $objEnvioEmailAutomatico->setUgID($vg_ug_id);
            $objEnvioEmailAutomatico->setPedido(
                formata_codigo_venda($venda_id)
            );
            $objEnvioEmailAutomatico->setListaCreditoOferta($msgEmailLista);
            $objEnvioEmailAutomatico->MontaEmailEspecifico();
        }
    }

    //VENDA GAMES
    //---------------------------------------------------------------------------------------------------
    //atualiza status
    if ($msg == "") {
        $sql =
            "update tb_dist_venda_games set 
                                        vg_ultimo_status_obs = " .
            SQLaddFields($parametros["ultimo_status_obs"], "s") .
            ",
                                        vg_ultimo_status = " .
            SQLaddFields($GLOBALS["STATUS_VENDA"]["VENDA_REALIZADA"], "") .
            "
                                where vg_id = " .
            $venda_id;
        $ret = SQLexecuteQuery($sql);
        if (!$ret) {
            $msg = "Erro ao atualizar venda." . PHP_EOL;
        }
    }

    return $msg;
}

function enviaEmailFormatadoComProdutos(
    $venda_id,
    $parametros,
    $cc,
    $bcc,
    $subjectEmail,
    $mensagem
) {
    $msg = "";

    //Recupera a venda
    if ($msg == "") {
        $sql =
            "select * from tb_dist_venda_games vg " .
            "where vg.vg_id = " .
            $venda_id;
        $rs_venda = SQLexecuteQuery($sql);
        if (!$rs_venda || pg_num_rows($rs_venda) == 0) {
            $msg = "Nenhuma venda encontrada." . PHP_EOL;
        } else {
            $rs_venda_row = pg_fetch_array($rs_venda);
            $vg_ug_id = $rs_venda_row["vg_ug_id"];
            $vg_ultimo_status = $rs_venda_row["vg_ultimo_status"];
        }
    }

    //Recupera dados do usuario
    if ($msg == "") {
        $sql =
            "select * from dist_usuarios_games ug " .
            "where ug.ug_id = " .
            $vg_ug_id;
        $rs_usuario = SQLexecuteQuery($sql);
        if (!$rs_usuario || pg_num_rows($rs_usuario) == 0) {
            $msg = "Nenhum cliente encontrado." . PHP_EOL;
        } else {
            $rs_usuario_row = pg_fetch_array($rs_usuario);
            $ug_email = $rs_usuario_row["ug_email"];
            $ug_tipo_cadastro = $rs_usuario_row["ug_tipo_cadastro"];
            $ug_sexo = $rs_usuario_row["ug_sexo"];
            $ug_nome = $rs_usuario_row["ug_nome"];
            $ug_cpf = $rs_usuario_row["ug_cpf"];
            $ug_rg = $rs_usuario_row["ug_rg"];
            $ug_nome_fantasia = $rs_usuario_row["ug_nome_fantasia"];
            $ug_cnpj = $rs_usuario_row["ug_cnpj"];
            $ug_endereco = $rs_usuario_row["ug_endereco"];
            $ug_numero = $rs_usuario_row["ug_numero"];
            $ug_complemento = $rs_usuario_row["ug_complemento"];
            $ug_bairro = $rs_usuario_row["ug_bairro"];
            $ug_cidade = $rs_usuario_row["ug_cidade"];
            $ug_estado = $rs_usuario_row["ug_estado"];
            $ug_cep = $rs_usuario_row["ug_cep"];
        }
    }

    //USUARIO
    //---------------------------------------------------------------------------------------------------
    //envia email
    if ($msg == "") {
        $sql =
            "select * from tb_dist_venda_games vg " .
            "inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id " .
            "where vg.vg_id = " .
            $venda_id;
        $rs_venda_modelos = SQLexecuteQuery($sql);
        $msgEmail .= "	<br>
                                                <table border='0' cellspacing='0' width='90%'>
                                                        
                                                        <tr bgcolor='C0C0C0'>
                                                          <td class='texto' align='center'><b>CODIGO</b></td>
                                                          <td class='texto' align='center'><b>PRODUTO</b></td>
                                                          <td class='texto' align='center'><b>QTDE</b></td>
                                                          <td class='texto' align='right'><b>PRC UNIT</b></td>
                                                          <td class='texto' align='right'><b>PRC TOTAL</b></td>
                                                          <!--td class='texto' align='right'><b>DESCONTO</b></td-->
                                                          <td class='texto' align='right'><b>REPASSE</b></td>
                                                        </tr>";

        $qtde_total = 0;
        $total_geral = 0;
        $total_desconto = 0;
        $total_repasse = 0;
        while ($rs_venda_modelos_row = pg_fetch_array($rs_venda_modelos)) {
            // Define operadora
            //if($rs_venda_modelos_row['vgm_ogp_id'] == 5) $blEmailHabbo = true;
            $blEmailHabbo =
                $rs_venda_modelos_row["vgm_opr_codigo"] == 16 ? true : false;
            $blEmailVostu =
                $rs_venda_modelos_row["vgm_opr_codigo"] == 35 ? true : false;

            $blEmailStardoll =
                $rs_venda_modelos_row["vgm_opr_codigo"] == 38 ? true : false;
            $blEmailSoftnyx =
                $rs_venda_modelos_row["vgm_opr_codigo"] == 37 ? true : false;

            $pagto_tipo = $rs_venda_modelos_row["vg_pagto_tipo"];

            $codigo = $rs_venda_modelos_row["vgm_id"];
            $qtde = $rs_venda_modelos_row["vgm_qtde"];
            $valor = $rs_venda_modelos_row["vgm_valor"];
            $perc_desconto = $rs_venda_modelos_row["vgm_perc_desconto"];
            $geral = $valor * $qtde;
            $desconto = ($geral * $perc_desconto) / 100;
            $repasse = $geral - $desconto;

            $qtde_total += $qtde;
            $total_geral += $geral;
            $total_desconto += $desconto;
            $total_repasse += $repasse;

            $msgEmail .=
                "  <tr bgcolor='FEFEFE'>
                                                          <td class='texto' align='center'>" .
                $codigo .
                "</td>
                                                          <td class='texto' width='200'>
                                                                &nbsp;&nbsp;
                                                                " .
                $rs_venda_modelos_row["vgm_nome_produto"];
            if ($rs_venda_modelos_row["vgm_nome_modelo"] != "") {
                $msgEmail .= " - " . $rs_venda_modelos_row["vgm_nome_modelo"];
            }
            $msgEmail .=
                "    </td>
                                                          <td class='texto' align='center'>" .
                $qtde .
                "</td>
                                                          <td class='texto' align='right'>" .
                number_format($valor, 2, ",", ".") .
                "</td>
                                                          <td class='texto' align='right'>" .
                number_format($geral, 2, ",", ".") .
                "</td>
                                                          <!--td class='texto' align='right'>" .
                number_format($desconto, 2, ",", ".") .
                "</td-->
                                                          <td class='texto' align='right'>" .
                number_format($repasse, 2, ",", ".") .
                "</td>
                                                        </tr>";
        }
        $msgEmail .=
            "  <tr bgcolor='F4F4F4'>
                                                          <td colspan='3'>&nbsp;</td>
                                                          <td class='texto' align='right'><b>Total</b></td>
                                                          <td class='texto' align='right'><b>" .
            number_format($total_geral, 2, ",", ".") .
            "</b></td>
                                                          <!--td class='texto' align='right'><b>" .
            number_format($total_desconto, 2, ",", ".") .
            "</b></td-->
                                                          <td class='texto' align='right'><b>" .
            number_format($total_repasse, 2, ",", ".") .
            "</b></td>
                                                        </tr>
                                                </table>";

        //Mensagem
        //$msgEmail .= $mensagem;
        $envioEmail = new EnvioEmailAutomatico(
            TIPO_USUARIO_LAN,
            "PedidoCancelado"
        );

        $envioEmail->setUgID($vg_ug_id);
        $envioEmail->setPedido($venda_id);
        $envioEmail->setInfo1($msgEmail);
        $envioEmail->setInfo2(" - Tempo de processamento expirado");

        $envioEmail->MontaEmailEspecifico();
    }

    return $msg;
}

function cancelaVendaGames($venda_id, $parametros)
{
    $msg = "";

    //atualiza status
    if ($msg == "") {
        $sql =
            "update tb_dist_venda_games set 
                                        vg_usuario_obs = " .
            SQLaddFields($parametros["usuario_obs"], "s") .
            ",
                                        vg_ultimo_status_obs = " .
            SQLaddFields($parametros["ultimo_status_obs"], "s") .
            ",
                                        vg_ultimo_status = " .
            SQLaddFields($GLOBALS["STATUS_VENDA"]["VENDA_CANCELADA"], "") .
            "
                                where vg_id = " .
            $venda_id;
        $ret = SQLexecuteQuery($sql);
        if (!$ret) {
            $msg = "Erro ao cancelar venda." . PHP_EOL;
        } else {
            $msg .= "Venda cancelada." . PHP_EOL;
            //Mensagem
            $msgEmail = "";
            $ret = enviaEmailFormatadoComProdutos(
                $venda_id,
                null,
                null,
                null,
                "E-Prepag - " .
                formata_codigo_venda($venda_id) .
                " - Cancelado",
                $msgEmail
            );
            if ($ret == "") {
                $msg .= "Envio de email: Enviado com sucesso." . PHP_EOL;
            } else {
                $msg .= "Envio de email: $ret " . PHP_EOL;
            }

            // Cancela pagamento desta venda, se existir
            // e se o pagamento não tiver sido feito e a venda ainda não tiver sido processada
            $sql =
                "update tb_pag_compras set 
                                                status_processed = 1,
                                                status = -1
                                        where idvenda = " .
                $venda_id .
                " and status_processed = 0 and status = 1 and tipo_cliente='LR' ";

            // Ver cancelaVendasEmPedidoEfetuado()
            //	" where idvenda = 0 and status_processed = 0 and status = 1 and ((datainicio + interval '" . $GLOBALS['PROCESS_AUTOM_PEDIDO_ONLINE_EFETUADO_CANCELAMENTO_MINUTOS_VENCIDO'] . " minutes' < CURRENT_TIMESTAMP))";

            $ret = SQLexecuteQuery($sql);
            if (!$ret) {
                $msg =
                    "Erro ao cancelar pagamentos de venda (venda foi cancelada)." .
                    PHP_EOL;
            }
        }
    }

    return $msg;
}

// Está usando este em processaAgendamentos.bat
function processaAgendamentos($lista = null)
{
    /*	status: 1 - Agendado
                                2 - Processado
                                3 - Cancelado
                                4 - Em execucao
                tipo:
                                1 - Distribuidor - Processamento e Envio de Email
        */
    global $cReturn,
    $cSpaces,
    $sFontRedOpen,
    $sFontRedClose,
    $bHTML,
    $raiz_do_projeto;

    $bDebug = false;
    $time_start_stats = getmicrotime();

    //header
    $header0 =
        "<b>" .
        (!is_null($lista) ? "UG_IDs Finais [" . $lista . "]" : "") .
        " (B1) - " .
        date("d/m/Y - H:i:s") .
        "</b>" .
        PHP_EOL;
    $smonitor = $header0 . "Incompleto" . PHP_EOL; // tem que ser substituido depoi spelo processamento que for
    $smonitorprocessamentos = "";
    $header =
        PHP_EOL .
        "------------------------------------------------------------------------" .
        PHP_EOL;
    $header .= $header0;
    if ($bDebug) {
        echo $header . $cReturn;
    }
    $msg = "";
    if ($bDebug) {
        echo "Elapsed time A0(agendamento): " .
            number_format(getmicrotime() - $time_start_stats, 2, ".", ".") .
            $cReturn;
    }

    //Recupera agendamentos
    if ($msg == "") {
        $sql = "select * from tb_dist_agendamento_execucao ae 
                                    inner join tb_dist_venda_games vg ON ae.ae_vg_id = vg.vg_id
                                where ae.ae_tipo = 1 and ae.ae_status = 1  
                                ";
        if (!is_null($lista)) {
            $sql .=
                " AND substring(vg_ug_id::varchar,length(vg_ug_id::varchar),1)::integer IN (" .
                $lista .
                ")
                                 ";
        } //end if(!is_null($lista))
        $sql .= "order by ae.ae_id ";
        echo PHP_EOL .
            "------------------------------------------------------------------------" .
            PHP_EOL .
            "(" .
            date("d/m/Y - H:i:s") .
            ")" .
            PHP_EOL .
            "  SQL Agendamento: $sql " .
            PHP_EOL;

        $rs_agendamentos = SQLexecuteQuery($sql);
        if (!$rs_agendamentos || pg_num_rows($rs_agendamentos) == 0) {
            $msg = "Nenhuma venda encontrada (0)." . PHP_EOL;
            $smonitor = $header0 . $msg;
        }
    }
    if ($bDebug) {
        echo "Elapsed time A1(agendamento): " .
            number_format(getmicrotime() - $time_start_stats, 2, ".", ".") .
            " " .
            $cReturn;
    }

    //Executa agendamentos
    if ($msg == "") {
        // É errado fazer um loop pelos registros da tabela tb_dist_agendamento_execucao e fazer update em tb_dist_agendamento_execucao
        //	dentro do loop -> termina em deadlock
        //		passar a usar pg_fetch_all()

        while ($rs_agendamentos_row = pg_fetch_array($rs_agendamentos)) {
            $ae_id = $rs_agendamentos_row["ae_id"];
            $ae_tipo = $rs_agendamentos_row["ae_tipo"];
            $ae_status = $rs_agendamentos_row["ae_status"];
            $ae_repetir_em = $rs_agendamentos_row["ae_repetir_em"];
            $ae_vg_id = $rs_agendamentos_row["ae_vg_id"];
            $ae_vg_ultimo_status_obs =
                $rs_agendamentos_row["ae_vg_ultimo_status_obs"];

            echo "  ID: " .
                $ae_id .
                ", Tipo: " .
                $ae_tipo .
                ", repetir em: " .
                $ae_repetir_em .
                ", vg_id: " .
                $ae_vg_id .
                ", ultimo status: " .
                $ae_vg_ultimo_status_obs .
                " (" .
                date("d/m/Y - H:i:s") .
                ")" .
                PHP_EOL;

            //Verifica horarios
            if ($ae_repetir_em && trim($ae_repetir_em) != "") {
                $ae_repetir_em_Ar = explode(";", $ae_repetir_em);
                if (count($ae_repetir_em_Ar) > 0) {
                    if (
                        !in_array(date("i"), $ae_repetir_em_Ar) &&
                        !in_array(date("H:i"), $ae_repetir_em_Ar)
                    ) {
                        if ($bDebug) {
                            echo "  count(ae_repetir_em_Ar): " .
                                count($ae_repetir_em_Ar) .
                                " (" .
                                date("d/m/Y - H:i:s") .
                                ")" .
                                PHP_EOL .
                                print_r($ae_repetir_em_Ar, true) .
                                PHP_EOL;
                        }
                        continue;
                    }
                }
            }
            if ($bDebug) {
                echo "  Horário OK" . PHP_EOL;
            }

            $msg = "";

            //atualiza agendamento
            if ($msg == "") {
                $sql =
                    "update tb_dist_agendamento_execucao set 
                                                        ae_status = 4,
                                                        ae_data_execucao_inicio = CURRENT_TIMESTAMP,
                                                        ae_data_execucao_fim = NULL
                                                where ae_id = " . $ae_id;

                if ($bDebug) {
                    echo "  SQL Agendamento: $sql (" .
                        date("d/m/Y - H:i:s") .
                        ")" .
                        PHP_EOL;
                }
                $ret = SQLexecuteQuery($sql);
                if (!$ret) {
                    $msg = "Erro ao atualizar agendamento (Inicio)." . PHP_EOL;
                }
            }

            if ($bDebug) {
                echo "  Agendamento OK (" .
                    date("d/m/Y - H:i:s") .
                    ")" .
                    PHP_EOL;
            }
            if ($msg == "") {
                $ae_status = 1;

                if ($ae_tipo == 1) {
                    $msgConcilia = "";
                    $msgConciliaUsuario = "";
                    $BtnProcessa = 1;
                    $venda_id = $ae_vg_id;
                    $ultimo_status_obs = $ae_vg_ultimo_status_obs;

                    if ($BtnProcessa) {
                        //Associa pins, gera venda e credita saldo
                        if ($msgConcilia == "") {
                            $parametros[
                                "ultimo_status_obs"
                            ] = $ultimo_status_obs;
                            //if($bDebug)
                            echo "  Último status $ultimo_status_obs (" .
                                date("d/m/Y - H:i:s") .
                                ")" .
                                PHP_EOL;

                            $msgConcilia = processaVendaGames(
                                $venda_id,
                                1,
                                $parametros
                            );
                            if ($msgConcilia == "") {
                                $msgConciliaUsuario .=
                                    "Processamento: Processado com sucesso. (ABCD - venda_id=" .
                                    $venda_id .
                                    ", R$" .
                                    number_format(
                                        $parametros["total_venda"],
                                        2,
                                        ",",
                                        "."
                                    ) .
                                    ")" .
                                    PHP_EOL;
                                $smonitorprocessamentos .=
                                    "vg: <a href='/pdv/vendas/com_venda_detalhe.php?venda_id=" .
                                    $venda_id .
                                    "'>" .
                                    $venda_id .
                                    " (R$" .
                                    number_format(
                                        $parametros["total_venda"],
                                        2,
                                        ",",
                                        "."
                                    ) .
                                    ")</a> " .
                                    $parametros["s_info_venda"] .
                                    PHP_EOL; //colocar no inicio da string (!empty($smonitorprocessamentos)?", ":"").
                            } else {
                                $msgConciliaUsuario .=
                                    "Processamento: " . $msgConcilia . "";
                            }

                            //if($bDebug)
                            echo "  msgConcilia: $msgConcilia (" .
                                date("d/m/Y - H:i:s") .
                                ")" .
                                PHP_EOL .
                                "msgConciliaUsuario: $msgConciliaUsuario" .
                                PHP_EOL;
                            //Ativa o processamento de envio de email da venda
                            if ($msgConcilia == "") {
                                if (verificaSomenteDebito($venda_id)) {
                                    $sql =
                                        "update tb_dist_venda_games set 
                                                                                        vg_ultimo_status_obs = " .
                                        SQLaddFields($ultimo_status_obs, "s") .
                                        ",
                                                                                        vg_ultimo_status = " .
                                        SQLaddFields(
                                            $GLOBALS["STATUS_VENDA"][
                                                "VENDA_REALIZADA"
                                            ],
                                            ""
                                        ) .
                                        "
                                                                                where vg_id = " .
                                        $venda_id;
                                    $ret = SQLexecuteQuery($sql);
                                    if (!$ret) {
                                        $msg =
                                            "Erro ao atualizar venda." .
                                            PHP_EOL;
                                    }
                                } else {
                                    $BtnProcessaEmail = 1;
                                }
                            } else {
                                //reagendamento
                                $agendamento_acao =
                                    $parametros["agendamento_acao"];
                                $agendamento_acao_obs =
                                    $parametros["agendamento_acao_obs"];
                                $agendamento_acao_repetir_em =
                                    $parametros["agendamento_acao_repetir_em"];
                                if ($agendamento_acao) {
                                    if ($agendamento_acao == "cancelar") {
                                        $ae_status = 3;
                                    }
                                    if ($agendamento_acao == "repetir_em") {
                                        $repetir_em = $agendamento_acao_repetir_em;
                                    }
                                }
                            }
                        }
                    }

                    if ($BtnProcessaEmail) {
                        //envia email para o cliente
                        if ($msgConcilia == "") {
                            $parametros[
                                "ultimo_status_obs"
                            ] = $ultimo_status_obs;
                            $msgConcilia = processaEmailVendaGames(
                                $venda_id,
                                $parametros
                            );
                            if ($msgConcilia == "") {
                                $msgConciliaUsuario .=
                                    "Envio de email: Enviado com sucesso." .
                                    PHP_EOL;
                            } else {
                                $msgConciliaUsuario .=
                                    "Envio de email: " . $msgConcilia;
                            }

                            //Sucesso na execucao do tipo 1
                            if ($msgConcilia == "") {
                                $ae_status = 2;
                            }
                        }
                    }
                    $ae_mensagem = $msgConciliaUsuario . $agendamento_acao_obs;
                    $msgOut .= PHP_EOL . "Venda " . $ae_vg_id . ":" . PHP_EOL;
                    $msgOut .= $ae_mensagem;
                }
            }

            if ($bDebug) {
                echo "  atualiza agendamentos (" .
                    date("d/m/Y - H:i:s") .
                    ")" .
                    PHP_EOL;
            }

            //atualiza agendamento
            if ($msg == "") {
                $sql =
                    "update tb_dist_agendamento_execucao set 
                                                        ae_status = $ae_status,
                                                        ae_data_execucao_fim = CURRENT_TIMESTAMP,
                                                        ae_mensagem = " .
                    SQLaddFields($ae_mensagem, "s");
                if ($repetir_em) {
                    $sql .= " ,ae_repetir_em = '$repetir_em'";
                }
                $sql .= " where ae_id = " . $ae_id;
                $ret = SQLexecuteQuery($sql);
                if (!$ret) {
                    $msg = "Erro ao atualizar agendamento (Fim)." . PHP_EOL;
                }
            }

            echo "Elapsed time A2(agendamento): " .
                number_format(getmicrotime() - $time_start_stats, 2, ".", ".") .
                " (ae_id: $ae_id, ae_vg_id: $ae_vg_id, ae_tipo: $ae_tipo, ae_status: $ae_status)" .
                $cReturn;
        }
        // Salva texto de monitor
        $smonitor = $header0 . $smonitorprocessamentos;
    }

    if ($bDebug) {
        echo "  SALVA FILE MONITOR (" . date("d/m/Y - H:i:s") . ")" . PHP_EOL;
    }
    // Salva o file monitor para mostrar no Backoffice
    try {
        if (
            $handle = fopen(
                $raiz_do_projeto .
                "log/monitoragendamentos" .
                str_replace(",", "", $lista) .
                ".txt",
                "w"
            )
        ) {
            fwrite($handle, $smonitor);

            fclose($handle);
        } else {
            echo PHP_EOL .
                "Error: Couldn't open Monitor File for writing" .
                PHP_EOL;
        }
    } catch (Exception $e) {
        echo "Error(6) writing monitor file [" .
            date("Y-m-d H:i:s") .
            "]: " .
            $e->getMessage() .
            PHP_EOL;
    }

    $msg = $header . $msg . $msgOut;

    return $msg;
}

// ====================  Pagamento Online LH Pre

function conciliacaoAutomaticaPagtoOnlineExpressMoneyLH($id_venda = null)
{
    global $FORMAS_PAGAMENTO,
    $FORMAS_PAGAMENTO_DESCRICAO,
    $PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC,
    $PAGAMENTO_PIX_NUMERIC;
    global $cReturn, $cSpaces, $sFontRedOpen, $sFontRedClose, $bHTML;

    $bDebug = true;
    if ($bDebug) {
        $time_start_stats = getmicrotime();
        $time_start_stats_prev = $time_start_stats;
        echo $cReturn .
            $cReturn .
            "Entering  conciliacaoAutomaticaPagamentoOnline() PDV - " .
            date("Y-m-d - H:i:s") .
            $cReturn;
        echo "Elapsed time : " .
            number_format(getmicrotime() - $time_start_stats, 2, ".", ".") .
            $cReturn;
    }
    $nminutes = 1440; // Por agora 1 dia, depois apenas 90 min
    echo $cReturn .
        "========================================================================" .
        $cReturn;
    echo "Prepara conciliação de pagamentos online PDV pré (registros com idvenda>0 e não processados nos últimos " .
        $nminutes .
        " minutos, desde " .
        date("Y-m-d H:i:s", strtotime("-" . $nminutes . " minutes")) .
        ")" .
        $cReturn;
    // Prepara conciliação de pagamentos online
    //		$date_ini = date('Y-m-d', strtotime("-5 days"));	//"2009-01-01"; //date("Y-m-d");
    // echo "-90 minutes: ".date('Y-m-d H:i:s', strtotime("-90 minutes"))."<br>";
    $date_ini = date("Y-m-d H:i:s", strtotime("-" . $nminutes . " minutes"));
    $date_end = date("Y-m-d H:i:s");

    $sql =
        "select * from tb_pag_compras pgt where idvenda>0 and status_processed=0 and tipo_cliente='LR' and iforma!='6' and iforma!='" .
        $FORMAS_PAGAMENTO["PAGAMENTO_PIX"] .
        "' ";
    // Quando chamado desde inc_mod_st.php processa apenas um registro, quando chamado na rotina automatica (id_venda=null) -> processa todos
    if ($id_venda) {
        $sql .= " and idvenda = " . $id_venda . " ";
    }

    // Opção 1 - não precissa limitar por data - apenas os pagtos com status_processed=0 serão retornados, após 90mins eles são cancelados.
    //	se houver um descancelamento de venda o pagto correspondnete vai aparecer aqui

    // Opção 1 - Para processar normalmente
    //		$sql .= " and (pgt.datainicio between '".$date_ini."' and '".$date_end."') ";
    // Opção 2 - Para incluir algum pagamento antigo descancelado
    //		$sql .= " and ((pgt.datainicio between '".$date_ini."' and '".$date_end."') or (pgt.datainicio between '2010-01-26 00:00:00' and '2010-01-26 23:59:59'))";

    // Levanta apenas pagamentos recentes para completar testes
    $sql .= " and (pgt.datainicio > (now() -'3 months'::interval)) ";

    $rs_total = SQLexecuteQuery($sql);
    if ($rs_total) {
        $registros_total = pg_num_rows($rs_total);
    }
    $sql .= " order by pgt.datainicio desc ";

    $rs_transacoes = SQLexecuteQuery($sql);
    if (!$rs_transacoes || pg_num_rows($rs_transacoes) == 0) {
        $msg = "Nenhuma transação encontrada (132 PDV)." . $cReturn;
    }

    $irows = 0;
    if ($rs_transacoes) {
        echo "NRegs: " . pg_num_rows($rs_transacoes) . $cReturn;
        while ($rs_transacoes_row = pg_fetch_array($rs_transacoes)) {
            $irows++;

            $msgregister =
                $rs_transacoes_row["numcompra"] .
                " - " .
                $rs_transacoes_row["datainicio"] .
                " - " .
                $rs_transacoes_row["datacompra"] .
                " - " .
                $rs_transacoes_row["iforma"] .
                " - " .
                $rs_transacoes_row["idvenda"] .
                " - Proc: " .
                $rs_transacoes_row["status_processed"] .
                " - " .
                get_tipo_cliente_descricao($rs_transacoes_row["tipo_cliente"]) .
                " -: R\$" .
                number_format($rs_transacoes_row["total"] / 100, 2, ",", ".") .
                " - '" .
                $rs_transacoes_row["cliente_nome"] .
                "'" .
                $cReturn;

            $msg = "";
            // Venda cadastrada
            if ($rs_transacoes_row["idvenda"] > 0) {
                // Pagamento concluido com sucesso -  status=3 em \prepag2\pag\*.php (arquivo de retorno do banco)
                if ($rs_transacoes_row["status"] == 3) {
                    echo "msgregister: " . $msgregister;
                    $prefix = getDocPrefix($rs_transacoes_row["iforma"]);

                    echo "rs_transacoes_row['iforma']: " .
                        $rs_transacoes_row["iforma"] .
                        " (prefix: '$prefix')" .
                        $cReturn;
                    $iforma_tmp =
                        $rs_transacoes_row["iforma"] ==
                        $FORMAS_PAGAMENTO["PAGAMENTO_BANCO_ITAU_ONLINE"]
                        ? $PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC
                        : $rs_transacoes_row["iforma"];

                    // Atualiza dados para tabela vendas
                    $sql_update =
                        "update tb_dist_venda_games set 
                                                                        vg_pagto_valor_pago		= " .
                        $rs_transacoes_row["total"] / 100 .
                        ",
                                                                        vg_pagto_tipo			= " .
                        $iforma_tmp .
                        ",
                                                                        vg_pagto_data_inclusao	= '" .
                        $rs_transacoes_row["datainicio"] .
                        "',
                                                                        vg_ultimo_status		= " .
                        $GLOBALS["STATUS_VENDA"]["DADOS_PAGTO_RECEBIDO"] .
                        "
                                                                where vg_id=" .
                        $rs_transacoes_row["idvenda"] .
                        ";";
                    // vg_pagto_num_docto		= '".$prefix.$rs_transacoes_row['iforma']."_".$rs_transacoes_row['numcompra']."',

                    // vg_usuario_obs			= 'Pagamento Online Bradesco [".$rs_transacoes_row['iforma']."] em ".date("Y-m-d H:i:s")."',
                    //	vg_bol_codigo			= ".$rs_transacoes_row['idpagto'].",

                    // foi transferido para confirmaBradesco.php
                    //	vg_pagto_banco			= 237,

                    $rs_update = SQLexecuteQuery($sql_update);
                    $sout =
                        $rs_transacoes_row["datainicio"] .
                        $cReturn .
                        "   " .
                        $rs_transacoes_row["numcompra"] .
                        $cReturn .
                        "   tipo: (" .
                        $rs_transacoes_row["iforma"] .
                        ") " .
                        $FORMAS_PAGAMENTO_DESCRICAO[
                            $rs_transacoes_row["iforma"]
                        ] .
                        "," .
                        $cReturn .
                        "   idvenda: " .
                        $rs_transacoes_row["idvenda"] .
                        "." .
                        $cReturn;
                    if (!$rs_update) {
                        $msg =
                            "Erro atualizando registro (61 PDV)." .
                            $sout .
                            $cReturn;
                        echo $msg;
                        gravaLog_TMP(
                            "Erro atualizando registro em processamento (41 PDV)." .
                            $cReturn .
                            $sout .
                            $sql_update .
                            $cReturn
                        );
                    } else {
                        echo "Pagamento atualizado com sucesso (PDV)." .
                            $cReturn;
                        gravaLog_TMP(
                            "Pagamento processado com sucesso (PDV):" .
                            $cReturn .
                            "   " .
                            $sout
                        );
                    }

                    // Pagamento ainda não foi feito ou não tem confirmação bancaria -  status=1 -> Sonda o banco, se estiver completo atualiza aqui
                } elseif ($rs_transacoes_row["status"] == 1) {
                    // bloqueio para evitar consulta ao MUP
                    //if(false)
                    // começa aqui nova função getSondaBanco()
                    $areturn = [];
                    $iret = getSondaBanco(
                        $rs_transacoes_row["iforma"],
                        $rs_transacoes_row["numcompra"],
                        $rs_transacoes_row["id_transacao_itau"],
                        $areturn
                    );
                    // get return values
                    $s_sonda = $areturn["s_sonda"];
                    $sBanco = $areturn["sBanco"];
                    $dataconfirma = $areturn["dataconfirma"];
                    if ($rs_transacoes_row["iforma"] == 9) {
                        echo "===> Data do BB :" . $dataconfirma . PHP_EOL;
                    }
                    $prefix_1 = $areturn["prefix_1"];
                    $s_sync = $areturn["s_sync"];
                    $vg_pagto_tipo = $areturn["vg_pagto_tipo"];
                    // até aqui nova função getSondaBanco()

                    // Se (!$s_sync), ou seja (status=1 & sonda) => completa a venda POR SONDA
                    if ($s_sync == "NO SYNC") {
                        /////   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

                        //Inicia transacao
                        if ($msg == "") {
                            $sql = "BEGIN TRANSACTION ";
                            $ret = SQLexecuteQuery($sql);
                            $ret = true; /////////////////////
                            if (!$ret) {
                                $msg =
                                    "Erro ao iniciar transação (PDV)." .
                                    $cReturn;
                            }
                        }
                        //Arames Monster
                        if (strlen(str_replace("'", "", $dataconfirma)) == 17) {
                            $dataconfirma =
                                "'20" .
                                str_replace("'", "", $dataconfirma) .
                                "'";
                            gravaLog_TMP(
                                "Arrumar teste OK: [" .
                                $dataconfirma .
                                "]" .
                                PHP_EOL
                            );
                        } //end if(strlen(str_replace("'", "", $dataconfirma)) == 17)

                        // Marca registro como status=3, já que se chegou aqui quer dizer que não passou por confirmaBanco.php
                        $sql =
                            "update tb_pag_compras set datacompra=CURRENT_TIMESTAMP, dataconfirma=" .
                            $dataconfirma .
                            ", status=3 where numcompra='" .
                            $rs_transacoes_row["numcompra"] .
                            "'";
                        echo $cReturn . " NO SYNC => [" . $sql . "]" . $cReturn;
                        //gravaLog_TMP("Marca registro como processado.".$cReturn.$sql.$cReturn);
                        $rs_update2 = SQLexecuteQuery($sql);
                        if (!$rs_update2) {
                            $msg =
                                "Erro atualizando status de registro (62aa PDV)." .
                                $cReturn .
                                "$sql" .
                                $cReturn;
                            echo $msg;
                        }
                        if (!$msg) {
                            // Atualiza dados para tabela vendas
                            $sql_update =
                                "update tb_dist_venda_games set 
                                                                                        vg_pagto_valor_pago		= " .
                                ($rs_transacoes_row["total"] / 100 +
                                    $rs_transacoes_row["frete"] +
                                    $rs_transacoes_row["manuseio"]) .
                                ",
                                                                                        vg_pagto_tipo			= " .
                                $vg_pagto_tipo .
                                ",
                                                                                        vg_pagto_data_inclusao	= '" .
                                $rs_transacoes_row["datainicio"] .
                                "',
                                                                                        vg_usuario_obs			= 'Pagamento Online " .
                                $sBanco .
                                " POR SONDA [" .
                                $rs_transacoes_row["iforma"] .
                                "] em " .
                                date("Y-m-d H:i:s") .
                                "',
                                                                                        vg_ultimo_status		= " .
                                $GLOBALS["STATUS_VENDA"][
                                    "DADOS_PAGTO_RECEBIDO"
                                ] .
                                "
                                                                                where vg_id=" .
                                $rs_transacoes_row["idvenda"] .
                                ";";
                            // 												vg_pagto_num_docto		= '".$prefix_1 . $rs_transacoes_row['iforma'] . "_" .$rs_transacoes_row['numcompra']."',

                            // vg_usuario_obs			= 'Pagamento Online Bradesco [".$rs_transacoes_row['iforma']."] em ".date("Y-m-d H:i:s")."',
                            //	vg_bol_codigo			= ".$rs_transacoes_row['idpagto'].",

                            // foi transferido para confirmaBradesco.php
                            //	vg_pagto_banco			= 237,

                            echo $cSpaces .
                                " " .
                                ($bHTML
                                    ? str_replace(
                                        PHP_EOL,
                                        "<br>" . PHP_EOL,
                                        $sql_update
                                    )
                                    : $sql_update) .
                                $cReturn;

                            $rs_update = SQLexecuteQuery($sql_update);
                            $sout =
                                $rs_transacoes_row["datainicio"] .
                                $cReturn .
                                "   " .
                                $rs_transacoes_row["numcompra"] .
                                $cReturn .
                                "   tipo: (" .
                                $rs_transacoes_row["iforma"] .
                                ") " .
                                $FORMAS_PAGAMENTO_DESCRICAO[
                                    $rs_transacoes_row["iforma"]
                                ] .
                                "," .
                                $cReturn .
                                "   idvenda: " .
                                $rs_transacoes_row["idvenda"] .
                                "." .
                                $cReturn;
                            if (!$rs_update) {
                                $msg =
                                    "Erro atualizando registro POR SONDA (61a PDV)." .
                                    $sout .
                                    $cReturn;
                                echo $msg;
                                gravaLog_TMP(
                                    "Erro atualizando registro POR SONDA em processamento (41a PDV)." .
                                    $cReturn .
                                    $sout .
                                    $sql_update .
                                    $cReturn
                                );
                            } else {
                                echo "Pagamento atualizado com sucesso." .
                                    $cReturn;
                                gravaLog_TMP(
                                    "Pagamento processado POR SONDA com sucesso (PDV):" .
                                    $cReturn .
                                    "   " .
                                    $sout
                                );
                            }
                        }

                        //Finaliza transacao
                        if ($msg == "") {
                            $sql = "COMMIT TRANSACTION ";
                            $ret = SQLexecuteQuery($sql);
                            if (!$ret) {
                                $msg = "Erro ao comitar transação." . $cReturn;
                            }

                            $msg_sonda = "PROCESSADO POR SONDA";
                        } else {
                            $sql = "ROLLBACK TRANSACTION ";
                            $ret = SQLexecuteQuery($sql);
                            //////			$ret  =true;												/////////////////////
                            if (!$ret) {
                                $msg =
                                    "Erro ao dar rollback na transação." .
                                    $cReturn;
                            }

                            $msg_sonda =
                                "PROCESSAMENTO POR SONDA FALHOU (ROLLBACK TRANSACTION)";
                        }

                        echo $msg_sonda .
                            ": Sonda='$s_sonda' forma:" .
                            $rs_transacoes_row["iforma"] .
                            ", numcompra: " .
                            $rs_transacoes_row["numcompra"] .
                            " - IDVenda: " .
                            $rs_transacoes_row["idvenda"] .
                            "- " .
                            $rs_transacoes_row["datainicio"] .
                            " - R\$" .
                            number_format(
                                $rs_transacoes_row["total"] / 100 -
                                $rs_transacoes_row["taxas"],
                                2,
                                ".",
                                "."
                            ) .
                            " (SYNC)" .
                            $cReturn;
                    } else {
                        // number_format(($rs_transacoes_row['total']/100 - $rs_transacoes_row['taxas']), 2, '.', '.')
                        echo "Não Processado por sonda: forma:" .
                            $rs_transacoes_row["iforma"] .
                            ", numcompra: " .
                            $rs_transacoes_row["numcompra"] .
                            " - IDVenda: " .
                            $rs_transacoes_row["idvenda"] .
                            "- " .
                            $rs_transacoes_row["datainicio"] .
                            " - R\$" .
                            number_format(
                                $rs_transacoes_row["total"] / 100 -
                                $rs_transacoes_row["taxas"],
                                2,
                                ".",
                                "."
                            ) .
                            " (NO SYNC)" .
                            $cReturn;
                    } // bloqueio para evitar consulta ao MUP
                } else {
                    echo "Não processado: status!=3 e Sonda=false." .
                        $cReturn .
                        "numcompra: " .
                        $rs_transacoes_row["numcompra"] .
                        " - IDVenda: " .
                        $rs_transacoes_row["idvenda"] .
                        "- " .
                        $rs_transacoes_row["datainicio"] .
                        $cReturn;
                }
            } else {
                echo "Não processado: idvenda=0." .
                    $cReturn .
                    "numcompra: " .
                    $rs_transacoes_row["numcompra"] .
                    "- " .
                    $rs_transacoes_row["datainicio"] .
                    $cReturn;
            }
        }
    }
    // ===================================================

    //header
    $header =
        $cReturn .
        "------------------------------------------------------------------------" .
        $cReturn;
    $header .=
        "Conciliacao Automatica de Pagto Online ExpressMoney PDV (f)" .
        $cReturn;
    $header .= date("d/m/Y - H:i:s") . $cReturn . $cReturn;
    $msg = "";
    echo $header;

    // Recupera as vendas pendentes de Pagto online ExpressMoney LH
    if ($msg == "") {
        $sql =
            "select * from tb_dist_venda_games vg 
                                where 1=1
                                                and ((vg.vg_ultimo_status = " .
            $GLOBALS["STATUS_VENDA"]["DADOS_PAGTO_RECEBIDO"] .
            ") or 
                                                    (vg.vg_ultimo_status = " .
            $GLOBALS["STATUS_VENDA"]["PAGTO_CONFIRMADO"] .
            ")) 
                                                and ((vg_pagto_tipo = " .
            $GLOBALS["FORMAS_PAGAMENTO"][
                "TRANSFERENCIA_ENTRE_CONTAS_BRADESCO"
            ] .
            ") or 
                                                                (vg_pagto_tipo = " .
            $GLOBALS["FORMAS_PAGAMENTO"]["PAGAMENTO_FACIL_BRADESCO_DEBITO"] .
            ") or 
                                                                (vg_pagto_tipo = " .
            $GLOBALS["FORMAS_PAGAMENTO"]["PAGAMENTO_BB_DEBITO_SUA_CONTA"] .
            ") or 
                                                                (vg_pagto_tipo = " .
            $GLOBALS["PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC"] .
            "))
                                                 and vg_pagto_tipo!=6
                                                 and vg_concilia=0 ";
        // Quando chamado desde inc_mod_st.php processa apenas um registro, quando chamado na rotina automatiaca (id_venda=null) -> processa todos
        if ($id_venda) {
            $sql .= " and vg_id = " . $id_venda . " ";
        }

        $rs_venda = SQLexecuteQuery($sql);
        if (!$rs_venda || pg_num_rows($rs_venda) == 0) {
            $msg = "Nenhuma venda encontrada." . $cReturn;
        }
        echo "Encontrados registros - pg_num_rows(rs_venda): " .
            pg_num_rows($rs_venda) .
            $cReturn;
    }

    // Busca Pagto online ExpressMoney LH
    echo " = Busca Pagto online ExpressMoney PDV" . $cReturn;
    if ($msg == "") {
        while ($rs_venda_row = pg_fetch_array($rs_venda)) {
            $vg_id = $rs_venda_row["vg_id"];
            $vg_pagto_banco = $rs_venda_row["vg_pagto_banco"];
            $vg_pagto_tipo = $rs_venda_row["vg_pagto_tipo"];
            $vg_pagto_num_docto = $rs_venda_row["vg_pagto_num_docto"];
            $vg_ultimo_status_obs = $rs_venda_row["vg_ultimo_status_obs"];
            $vg_ug_id = $rs_venda_row["vg_ug_id"];

            // obtem o valor total da venda
            $sql =
                "select * from dist_boleto_bancario_games 
                                        where bbg_ug_id=" .
                $vg_ug_id .
                " and bbg_pago=0 and  bbg_vg_id=" .
                $vg_id .
                "
                                        order by bbg_data_inclusao desc";
            $rs_venda_dist = SQLexecuteQuery($sql);
            if ($rs_venda_dist && pg_num_rows($rs_venda_dist) > 0) {
                while ($rs_venda_dist_row = pg_fetch_array($rs_venda_dist)) {
                    $valor = $rs_venda_dist_row["bbg_valor"];
                    $valor_taxa = $rs_venda_dist_row["bbg_valor_taxa"];
                    $bco_codigo = $rs_venda_dist_row["bco_codigo"];
                    $iforma = get_iforma($vg_pagto_tipo);
                    echo date("Y-m-d H:i:s") .
                        " - idvenda = " .
                        $vg_id .
                        ", valor: $valor, taxa: $valor_taxa (doc:'" .
                        $rs_venda_dist_row["bbg_documento"] .
                        "', banco: '$vg_pagto_banco') iforma: '$iforma'" .
                        PHP_EOL;

                    //Procura pagamento completo
                    $sql = "select * ";
                    $sql .= "from tb_pag_compras ";
                    $sql .=
                        "where idvenda = " .
                        $vg_id .
                        " and banco = '" .
                        $vg_pagto_banco .
                        "' and status = 3 and status_processed = 0 and (total/100) = " .
                        $valor .
                        " ";
                    $sql .= " and iforma = '" . $iforma . "'";

                    $rs_bol = SQLexecuteQuery($sql);
                    if ($rs_bol && pg_num_rows($rs_bol) > 0) {
                        $rs_bol_row = pg_fetch_array($rs_bol);
                        $bol_codigo = $rs_bol_row["bol_codigo"];

                        $msg .=
                            PHP_EOL .
                            "Venda " .
                            $vg_id .
                            ": Boleto " .
                            $bol_codigo .
                            ":" .
                            $cReturn;
                        $parametros["ultimo_status_obs"] =
                            "Conciliação automática em " .
                            date("d/m/Y - H:i:s") .
                            $cReturn;
                        if (trim($vg_ultimo_status_obs) != "") {
                            $parametros["ultimo_status_obs"] =
                                $vg_ultimo_status_obs .
                                $cReturn .
                                $parametros["ultimo_status_obs"];
                        }
                        $parametros["PROCESS_AUTOM"] = "1";
                        $parametros["valor_total"] = $valor;
                        $parametros["valor"] = $valor - $valor_taxa;
                        $parametros["valor_taxa"] = $valor_taxa;

                        //Concilia
                        $msgConcilia = "";
                        if ($msgConcilia == "") {
                            echo "msgConcilia = conciliaExpressMoneyLH_PagtoOnline(" .
                                $vg_id .
                                ", " .
                                $vg_ug_id .
                                ", parametros) [" .
                                getmicrotime() .
                                "]" .
                                $cReturn;
                            $msgConcilia = conciliaExpressMoneyLH_PagtoOnline(
                                $vg_id,
                                $vg_ug_id,
                                $parametros
                            );
                            if ($msgConcilia == "") {
                                $msg .=
                                    "Conciliacao: Conciliado com sucesso." .
                                    $cReturn;
                            } else {
                                $msg .= "Conciliacao: " . $msgConcilia;
                            }
                        }

                        //Gera venda
                        if ($msgConcilia == "") {
                            echo "msgConcilia = processaExpressMoneyLH_pag_online(" .
                                $vg_id .
                                ", 1, parametros) [" .
                                getmicrotime() .
                                "]" .
                                $cReturn;
                            $msgConcilia = processaExpressMoneyLH_pag_online(
                                $vg_id,
                                1,
                                $parametros
                            );
                            if ($msgConcilia == "") {
                                $msg .=
                                    "Processamento: Processado com sucesso." .
                                    $cReturn;
                            } else {
                                $msg .= "Processamento: " . $msgConcilia;
                            }
                        }

                        //envia email para o cliente
                        if ($msgConcilia == "") {
                            echo "msgConcilia = processaEmailExpressMoneyLH_pag_online(" .
                                $vg_id .
                                ", parametros) [" .
                                getmicrotime() .
                                "]" .
                                $cReturn;
                            $msgConcilia = processaEmailExpressMoneyLH_pag_online(
                                $vg_id,
                                $parametros
                            );
                            if ($msgConcilia == "") {
                                $msg .=
                                    "Envio de email: Enviado com sucesso." .
                                    $cReturn;
                            } else {
                                $msg .= "Envio de email: " . $msgConcilia;
                            }
                        }

                        // Marca registro como processado só aqui, depois de conciliar a venda
                        $sql = "update tb_pag_compras set status_processed=1 ";
                        $sql .=
                            "where idvenda = " .
                            $vg_id .
                            " and banco = '" .
                            $vg_pagto_banco .
                            "' and status = 3 and status_processed = 0 and (total/100) = " .
                            $valor .
                            " ";
                        $sql .= " and iforma = '" . $iforma . "'";
                        echo $cReturn .
                            " Marca registro de pagamento como processado => " .
                            $sql .
                            $cReturn;
                        $rs_update3 = SQLexecuteQuery($sql);
                        if (!$rs_update3) {
                            $msg =
                                "Erro atualizando status de registro (62aabb PDV)." .
                                $cReturn .
                                "$sql" .
                                $cReturn;
                            echo $msg;
                        }
                    } else {
                        echo "SEM PAGAMENTOS PENDENTES (num_rows: " .
                            pg_num_rows($rs_bol) .
                            ")" .
                            $cReturn;
                    }
                }
            } else {
                echo "SEM BOLETOS BANCARIOS (DIST)" . $cReturn;
            }
        }
    } else {
        echo "SEM BOLETOS PDV PRE PENDENTES: '$msg'" . $cReturn;
    }

    // Em certos casos o status da venda fica em vg_ultimo_status=8 (PAGTO_CONFIRMADO) com o pagamento completo e os PINs entregues
    //	-> tem que passar para vg_ultimo_status=5 (VENDA_REALIZADA)
    $sql =
        "select vg.vg_id, pag.status, vg.vg_ultimo_status  
                                from tb_dist_venda_games vg 
                                        inner join tb_pag_compras pag on pag.idvenda = vg.vg_id 
                                where (vg.vg_ultimo_status = " .
        $GLOBALS["STATUS_VENDA"]["PAGTO_CONFIRMADO"] .
        "	
                                        and (vg_pagto_tipo = " .
        $GLOBALS["FORMAS_PAGAMENTO"]["TRANSFERENCIA_ENTRE_CONTAS_BRADESCO"] .
        " 
                                        or vg_pagto_tipo =  " .
        $GLOBALS["FORMAS_PAGAMENTO"]["PAGAMENTO_FACIL_BRADESCO_DEBITO"] .
        "  
                                        or vg_pagto_tipo = " .
        $GLOBALS["FORMAS_PAGAMENTO"]["PAGAMENTO_BB_DEBITO_SUA_CONTA"] .
        " 
                                        or vg_pagto_tipo = " .
        $GLOBALS["PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC"] .
        "
                                        or vg_pagto_tipo = " .
        $GLOBALS["PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC"] .
        ")) 
                                        and vg_pagto_data_inclusao > '2009-01-01' 
                                order by vg_data_inclusao desc";
    // and not vgm_pin_codinterno=''
    //--and vg_data_inclusao >= '2010-04-18 00:00:00'
    // just not null
    if ($bDebug) {
        //	echo "DEBUG (AN1000): ".$sql.PHP_EOL;
        echo $cReturn .
            $cReturn .
            $cReturn .
            "ELAPSED TOTAL TIME (1abc): " .
            number_format(getmicrotime() - $time_start_stats, 2, ".", ".") .
            $cReturn;
    }

    $rs_venda_pendentes = SQLexecuteQuery($sql);
    if ($rs_venda_pendentes && pg_num_rows($rs_venda_pendentes) > 0) {
        while ($rs_venda_pendentes_row = pg_fetch_array($rs_venda_pendentes)) {
            $vg_id_pendente = $rs_venda_pendentes_row["vg_id"];
            $pag_status = $rs_venda_pendentes_row["status"];
            $vg_ultimo_status = $rs_venda_pendentes_row["vg_ultimo_status"];

            if (
                $vg_ultimo_status ==
                $GLOBALS["STATUS_VENDA"]["PAGTO_CONFIRMADO"]
            ) {
                // 8
                $sql =
                    "update tb_dist_venda_games
                                                set vg_ultimo_status = " .
                    $GLOBALS["STATUS_VENDA"]["VENDA_REALIZADA"] .
                    "
                                                where vg_id = " .
                    $vg_id_pendente;

                echo "==>> Atualiza status de venda PDV de '" .
                    $GLOBALS["STATUS_VENDA"]["PAGTO_CONFIRMADO"] .
                    "' para '" .
                    $GLOBALS["STATUS_VENDA"]["VENDA_REALIZADA"] .
                    "' (vg_id pendente = " .
                    $vg_id_pendente .
                    ")" .
                    PHP_EOL;

                $ret = SQLexecuteQuery($sql);
                if (!$ret) {
                    $msg =
                        "Erro ao atualizar venda PDV com status pendente (pagamento online PDV)." .
                        PHP_EOL;
                } else {
                    echo "Venda PDV status pendente vg_id:$vg_id_pendente, status ajustado de PAGTO_CONFIRMADO -> VENDA_REALIZADA (" .
                        date("d/m/Y - H:i:s") .
                        ")" .
                        PHP_EOL;
                }
            } else {
                echo "Venda PDV status pendente vg_id:$vg_id_pendente, status do pagamento != " .
                    $GLOBALS["STATUS_VENDA"]["PAGTO_CONFIRMADO"] .
                    ", nada feito (" .
                    date("d/m/Y - H:i:s") .
                    ")" .
                    PHP_EOL;
            }
        }
    }

    $msg =
        $header .
        $msg .
        "------------------------------------------------------------------------" .
        PHP_EOL;
    //echo $msg;
    if ($bDebug) {
        echo $cReturn .
            $cReturn .
            $cReturn .
            "ELAPSED TOTAL TIME: " .
            number_format(getmicrotime() - $time_start_stats, 2, ".", ".") .
            $cReturn;
    }

    $msg =
        $header .
        $msg .
        "------------------------------------------------------------------------" .
        $cReturn;

    //Conciliação PIX
    $msg .= conciliacaoAutomaticaPagtoPIXemPDV($id_venda);
    return $msg;
}

function conciliacaoAutomaticaPagtoPIXemPDV($id_venda = null)
{
    global $FORMAS_PAGAMENTO,
    $FORMAS_PAGAMENTO_DESCRICAO,
    $PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC,
    $PAGAMENTO_PIX_NUMERIC;
    global $cReturn, $cSpaces, $sFontRedOpen, $sFontRedClose, $bHTML;

    $bDebug = true;
    $minutes = 172800; //2500

    if ($bDebug) {
        $time_start_stats = getmicrotime();
        $time_start_stats_prev = $time_start_stats;
        echo $cReturn .
            $cReturn .
            "Entering  conciliacaoAutomaticaPagtoPIXemPDV() PDV - " .
            date("Y-m-d - H:i:s") .
            $cReturn;
        echo "Elapsed time : " .
            number_format(getmicrotime() - $time_start_stats, 2, ".", ".") .
            $cReturn;
    }
    echo $cReturn .
        "========================================================================" .
        $cReturn;
    echo "Prepara conciliação de pagamentos PIX para PDV pré (não processados nos últimos " .
        $minutes .
        " minutos, desde " .
        date("Y-m-d H:i:s", strtotime("-" . $minutes . " minutes")) .
        ")" .
        $cReturn;
    // Prepara conciliação de pagamentos online
    $date_ini = date("Y-m-d H:i:s", strtotime("-" . $minutes . " minutes"));
    $date_end = date("Y-m-d H:i:s");

    $sql =
        "select * from tb_pag_compras pgt where idvenda>0 and status_processed=0 and tipo_cliente='LR' AND status = 1 AND iforma='" .
        $FORMAS_PAGAMENTO["PAGAMENTO_PIX"] .
        "' ";
    // Quando chamado desde inc_mod_st.php processa apenas um registro, quando chamado na rotina automatica (id_venda=null) -> processa todos
    if ($id_venda) {
        $sql .= " and idvenda = " . $id_venda . " ";
    }
    // Levanta apenas pagamentos recentes para completar testes
    $sql .=
        " and ((pgt.datainicio > (now() -'" .
        $minutes .
        " minutes'::interval)) OR (pgt.datainicio >= '2022-06-22 20:00:00' AND pgt.datainicio <= '2022-06-22 22:00:00')) ";
    $sql .= " order by pgt.datainicio desc ";

    $rs_transacoes = SQLexecuteQuery($sql);
    $registros_total = pg_num_rows($rs_transacoes);
    if (!$rs_transacoes || $registros_total == 0) {
        $msg = "Nenhuma transação encontrada (132 PDV)." . $cReturn;
    }

    $irows = 0;
    if ($rs_transacoes) {
        echo "NRegs: " . $registros_total . $cReturn;
        while ($rs_transacoes_row = pg_fetch_array($rs_transacoes)) {
            $irows++;

            echo $rs_transacoes_row["numcompra"] .
                " - " .
                $rs_transacoes_row["datainicio"] .
                " - " .
                $rs_transacoes_row["datacompra"] .
                " - " .
                $rs_transacoes_row["iforma"] .
                " - " .
                $rs_transacoes_row["idvenda"] .
                " - Proc: " .
                $rs_transacoes_row["status_processed"] .
                " - " .
                get_tipo_cliente_descricao($rs_transacoes_row["tipo_cliente"]) .
                " -: R\$" .
                number_format($rs_transacoes_row["total"] / 100, 2, ",", ".") .
                " - '" .
                $rs_transacoes_row["cliente_nome"] .
                "'" .
                $cReturn;
            $msg = "";
            // Venda cadastrada
            if ($rs_transacoes_row["idvenda"] > 0) {
                // começa aqui nova função getSondaBanco()
                $areturn = [];
                $iret = getSondaBanco(
                    $rs_transacoes_row["iforma"],
                    $rs_transacoes_row["numcompra"],
                    $rs_transacoes_row["id_transacao_itau"],
                    $areturn
                );

                // get return values
                $s_sonda = $areturn["s_sonda"];
                $sBanco = $areturn["sBanco"];
                $dataconfirma = $areturn["dataconfirma"];
                $prefix_1 = $areturn["prefix_1"];
                $s_sync = $areturn["s_sync"];
                $vg_pagto_tipo = $areturn["vg_pagto_tipo"];
                // até aqui nova função getSondaBanco()

                //echo "<<<<<".json_encode($areturn).">>>>>";

                // Se (!$s_sync), ou seja (status=1 & sonda) => completa a venda POR SONDA
                if ($s_sync == "NO SYNC") {
                    //Inicia transacao
                    if ($msg == "") {
                        $sql = "BEGIN TRANSACTION ";
                        $ret = SQLexecuteQuery($sql);
                        $ret = true; /////////////////////
                        if (!$ret) {
                            $msg =
                                "Erro ao iniciar transação (PDV)." . $cReturn;
                        }
                    }
                    //Arames Monster
                    if (strlen(str_replace("'", "", $dataconfirma)) == 17) {
                        $dataconfirma =
                            "'20" . str_replace("'", "", $dataconfirma) . "'";
                        gravaLog_TMP(
                            "Arrumar teste OK: [" .
                            $dataconfirma .
                            "]" .
                            PHP_EOL
                        );
                    } //end if(strlen(str_replace("'", "", $dataconfirma)) == 17)

                    // Marca registro como status=3, já que se chegou aqui quer dizer que não passou por confirmaBanco.php
                    $sql =
                        "update tb_pag_compras set protocolo='Teste', datacompra=CURRENT_TIMESTAMP, dataconfirma=" .
                        $dataconfirma .
                        ", status=3 where numcompra='" .
                        $rs_transacoes_row["numcompra"] .
                        "'";
                    echo $cReturn . " NO SYNC => [" . $sql . "]" . $cReturn;
                    //gravaLog_TMP("Marca registro como processado.".$cReturn.$sql.$cReturn);
                    $rs_update2 = SQLexecuteQuery($sql);
                    if (!$rs_update2) {
                        $msg =
                            "Erro atualizando status de registro (62aa PDV)." .
                            $cReturn .
                            "$sql" .
                            $cReturn;
                        echo $msg;
                    }
                    if (!$msg) {
                        // Atualiza dados para tabela vendas
                        $sql_update =
                            "update tb_dist_venda_games set 
                                                                                vg_pagto_valor_pago		= " .
                            ($rs_transacoes_row["total"] / 100 +
                                $rs_transacoes_row["frete"] +
                                $rs_transacoes_row["manuseio"]) .
                            ",
                                                                                vg_pagto_tipo			= " .
                            $vg_pagto_tipo .
                            ",
                                                                                vg_pagto_data_inclusao	= '" .
                            $rs_transacoes_row["datainicio"] .
                            "',
                                                                                vg_usuario_obs			= 'Pagamento Online " .
                            $sBanco .
                            " POR SONDA [" .
                            $rs_transacoes_row["iforma"] .
                            "] em " .
                            date("Y-m-d H:i:s") .
                            "',
                                                                                vg_ultimo_status		= " .
                            $GLOBALS["STATUS_VENDA"]["DADOS_PAGTO_RECEBIDO"] .
                            "
                                                                        where vg_id=" .
                            $rs_transacoes_row["idvenda"] .
                            ";";
                        // 												vg_pagto_num_docto		= '".$prefix_1 . $rs_transacoes_row['iforma'] . "_" .$rs_transacoes_row['numcompra']."',

                        // vg_usuario_obs			= 'Pagamento Online Bradesco [".$rs_transacoes_row['iforma']."] em ".date("Y-m-d H:i:s")."',
                        //	vg_bol_codigo			= ".$rs_transacoes_row['idpagto'].",

                        // foi transferido para confirmaBradesco.php
                        //	vg_pagto_banco			= 237,

                        echo $cSpaces .
                            " " .
                            ($bHTML
                                ? str_replace(
                                    PHP_EOL,
                                    "<br>" . PHP_EOL,
                                    $sql_update
                                )
                                : $sql_update) .
                            $cReturn;

                        $rs_update = SQLexecuteQuery($sql_update);
                        $sout =
                            $rs_transacoes_row["datainicio"] .
                            $cReturn .
                            "   " .
                            $rs_transacoes_row["numcompra"] .
                            $cReturn .
                            "   tipo: (" .
                            $rs_transacoes_row["iforma"] .
                            ") " .
                            $FORMAS_PAGAMENTO_DESCRICAO[
                                $rs_transacoes_row["iforma"]
                            ] .
                            "," .
                            $cReturn .
                            "   idvenda: " .
                            $rs_transacoes_row["idvenda"] .
                            "." .
                            $cReturn;
                        if (!$rs_update) {
                            $msg =
                                "Erro atualizando registro POR SONDA (61a PDV)." .
                                $sout .
                                $cReturn;
                            echo $msg;
                            gravaLog_TMP(
                                "Erro atualizando registro POR SONDA em processamento (41a PDV)." .
                                $cReturn .
                                $sout .
                                $sql_update .
                                $cReturn
                            );
                        } else {
                            echo "Pagamento atualizado com sucesso." . $cReturn;
                            gravaLog_TMP(
                                "Pagamento processado POR SONDA com sucesso (PDV):" .
                                $cReturn .
                                "   " .
                                $sout
                            );
                        }
                    }

                    //Finaliza transacao
                    if ($msg == "") {
                        $sql = "COMMIT TRANSACTION ";
                        $ret = SQLexecuteQuery($sql);
                        if (!$ret) {
                            $msg = "Erro ao comitar transação." . $cReturn;
                        }

                        $msg_sonda = "PROCESSADO POR SONDA";
                    } else {
                        $sql = "ROLLBACK TRANSACTION ";
                        $ret = SQLexecuteQuery($sql);
                        if (!$ret) {
                            $msg =
                                "Erro ao dar rollback na transação." . $cReturn;
                        }

                        $msg_sonda =
                            "PROCESSAMENTO POR SONDA FALHOU (ROLLBACK TRANSACTION)";
                    }

                    echo $msg_sonda .
                        ": Sonda='$s_sonda' forma:" .
                        $rs_transacoes_row["iforma"] .
                        ", numcompra: " .
                        $rs_transacoes_row["numcompra"] .
                        " - IDVenda: " .
                        $rs_transacoes_row["idvenda"] .
                        "- " .
                        $rs_transacoes_row["datainicio"] .
                        " - R\$" .
                        number_format(
                            $rs_transacoes_row["total"] / 100 -
                            $rs_transacoes_row["taxas"],
                            2,
                            ".",
                            "."
                        ) .
                        " (SYNC)" .
                        $cReturn;
                }
                //end if($s_sync=="NO SYNC")
                else {
                    // number_format(($rs_transacoes_row['total']/100 - $rs_transacoes_row['taxas']), 2, '.', '.')
                    echo "Não Processado por sonda: forma:" .
                        $rs_transacoes_row["iforma"] .
                        ", numcompra: " .
                        $rs_transacoes_row["numcompra"] .
                        " - IDVenda: " .
                        $rs_transacoes_row["idvenda"] .
                        "- " .
                        $rs_transacoes_row["datainicio"] .
                        " - R\$" .
                        number_format(
                            $rs_transacoes_row["total"] / 100 -
                            $rs_transacoes_row["taxas"],
                            2,
                            ".",
                            "."
                        ) .
                        " (NO SYNC)" .
                        $cReturn;
                }
            }
            //end if($rs_transacoes_row['idvenda']>0)
            else {
                echo "Não processado: idvenda=0." .
                    $cReturn .
                    "numcompra: " .
                    $rs_transacoes_row["numcompra"] .
                    "- " .
                    $rs_transacoes_row["datainicio"] .
                    $cReturn;
            }
        } //end while
    } //end if($rs_transacoes)
    // ===================================================

    //header
    $header =
        $cReturn .
        "------------------------------------------------------------------------" .
        $cReturn;
    $header .= "Conciliacao Automatica de Pagto Online PIX PDV (f)" . $cReturn;
    $header .= date("d/m/Y - H:i:s") . $cReturn . $cReturn;
    $msg = "";
    echo $header;

    // Recupera as vendas pendentes de Pagto online ExpressMoney LH
    if ($msg == "") {
        $sql =
            "select * from tb_dist_venda_games vg 
                                where 1=1
                                                and ((vg.vg_ultimo_status = " .
            $GLOBALS["STATUS_VENDA"]["DADOS_PAGTO_RECEBIDO"] .
            ") or 
                                                    (vg.vg_ultimo_status = " .
            $GLOBALS["STATUS_VENDA"]["PAGTO_CONFIRMADO"] .
            ")) 
                                                and vg_pagto_tipo = " .
            $GLOBALS["PAGAMENTO_PIX_NUMERIC"] .
            "
                                                and vg_pagto_tipo!=6
                                                and vg_concilia=0 ";
        // Quando chamado desde inc_mod_st.php processa apenas um registro, quando chamado na rotina automatiaca (id_venda=null) -> processa todos
        if ($id_venda) {
            $sql .= " and vg_id = " . $id_venda . " ";
        }
        $rs_venda = SQLexecuteQuery($sql);
        if (!$rs_venda || pg_num_rows($rs_venda) == 0) {
            $msg = "Nenhuma venda encontrada." . $cReturn;
        }
        echo "Encontrados registros - pg_num_rows(rs_venda): " .
            pg_num_rows($rs_venda) .
            $cReturn;
    }

    // Busca Pagto online ExpressMoney LH
    echo " = Busca Pagto online PIX PDV" . $cReturn;
    if ($msg == "") {
        while ($rs_venda_row = pg_fetch_array($rs_venda)) {
            $vg_id = $rs_venda_row["vg_id"];
            $vg_pagto_banco = $rs_venda_row["vg_pagto_banco"];
            $vg_pagto_tipo = $rs_venda_row["vg_pagto_tipo"];
            $vg_pagto_num_docto = $rs_venda_row["vg_pagto_num_docto"];
            $vg_ultimo_status_obs = $rs_venda_row["vg_ultimo_status_obs"];
            $vg_ug_id = $rs_venda_row["vg_ug_id"];

            // obtem o valor total da venda
            $sql =
                "select * from dist_boleto_bancario_games 
                                        where bbg_ug_id=" .
                $vg_ug_id .
                " and bbg_pago=0 and  bbg_vg_id=" .
                $vg_id .
                "
                                        order by bbg_data_inclusao desc";
            $rs_venda_dist = SQLexecuteQuery($sql);
            if ($rs_venda_dist && pg_num_rows($rs_venda_dist) > 0) {
                while ($rs_venda_dist_row = pg_fetch_array($rs_venda_dist)) {
                    $valor = $rs_venda_dist_row["bbg_valor"];
                    $valor_taxa = $rs_venda_dist_row["bbg_valor_taxa"];
                    $bco_codigo = $rs_venda_dist_row["bco_codigo"];
                    $iforma = get_iforma($vg_pagto_tipo);
                    echo date("Y-m-d H:i:s") .
                        " - idvenda = " .
                        $vg_id .
                        ", valor: $valor, taxa: $valor_taxa (doc:'" .
                        $rs_venda_dist_row["bbg_documento"] .
                        "', banco: '$vg_pagto_banco') iforma: '$iforma'" .
                        PHP_EOL;

                    //Procura pagamento completo
                    $sql = "select * ";
                    $sql .= "from tb_pag_compras ";
                    $sql .=
                        "where idvenda = " .
                        $vg_id .
                        " and banco = '" .
                        $vg_pagto_banco .
                        "' and status = 3 and status_processed = 0 and (total/100) = " .
                        $valor .
                        " ";
                    $sql .= " and iforma = '" . $iforma . "'";

                    $rs_bol = SQLexecuteQuery($sql);
                    if ($rs_bol && pg_num_rows($rs_bol) > 0) {
                        $rs_bol_row = pg_fetch_array($rs_bol);
                        $bol_codigo = $rs_bol_row["bol_codigo"];

                        $msg .=
                            PHP_EOL .
                            "Venda " .
                            $vg_id .
                            ": Boleto " .
                            $bol_codigo .
                            ":" .
                            $cReturn;
                        $parametros["ultimo_status_obs"] =
                            "Conciliação automática em " .
                            date("d/m/Y - H:i:s") .
                            $cReturn;
                        if (trim($vg_ultimo_status_obs) != "") {
                            $parametros["ultimo_status_obs"] =
                                $vg_ultimo_status_obs .
                                $cReturn .
                                $parametros["ultimo_status_obs"];
                        }
                        $parametros["PROCESS_AUTOM"] = "1";
                        $parametros["valor_total"] = $valor;
                        $parametros["valor"] = $valor - $valor_taxa;
                        $parametros["valor_taxa"] = $valor_taxa;

                        //Concilia
                        $msgConcilia = "";
                        if ($msgConcilia == "") {
                            echo "msgConcilia = conciliaExpressMoneyLH_PagtoOnline(" .
                                $vg_id .
                                ", " .
                                $vg_ug_id .
                                ", parametros) [" .
                                getmicrotime() .
                                "]" .
                                $cReturn;
                            $msgConcilia = conciliaExpressMoneyLH_PagtoOnline(
                                $vg_id,
                                $vg_ug_id,
                                $parametros
                            );
                            if ($msgConcilia == "") {
                                $msg .=
                                    "Conciliacao: Conciliado com sucesso." .
                                    $cReturn;
                            } else {
                                $msg .= "Conciliacao: " . $msgConcilia;
                            }
                        }

                        //Gera venda
                        if ($msgConcilia == "") {
                            echo "msgConcilia = processaExpressMoneyLH_pag_online(" .
                                $vg_id .
                                ", 1, parametros) [" .
                                getmicrotime() .
                                "]" .
                                $cReturn;
                            $msgConcilia = processaExpressMoneyLH_pag_online(
                                $vg_id,
                                1,
                                $parametros
                            );
                            if ($msgConcilia == "") {
                                $msg .=
                                    "Processamento: Processado com sucesso." .
                                    $cReturn;
                            } else {
                                $msg .= "Processamento: " . $msgConcilia;
                            }
                        }

                        //envia email para o cliente
                        if ($msgConcilia == "") {
                            echo "msgConcilia = processaEmailExpressMoneyLH_pag_online(" .
                                $vg_id .
                                ", parametros) [" .
                                getmicrotime() .
                                "]" .
                                $cReturn;
                            $msgConcilia = processaEmailExpressMoneyLH_pag_online(
                                $vg_id,
                                $parametros
                            );
                            if ($msgConcilia == "") {
                                $msg .=
                                    "Envio de email: Enviado com sucesso." .
                                    $cReturn;
                            } else {
                                $msg .= "Envio de email: " . $msgConcilia;
                            }
                        }

                        // Marca registro como processado só aqui, depois de conciliar a venda
                        $sql = "update tb_pag_compras set status_processed=1 ";
                        $sql .=
                            "where idvenda = " .
                            $vg_id .
                            " and banco = '" .
                            $vg_pagto_banco .
                            "' and status = 3 and status_processed = 0 and (total/100) = " .
                            $valor .
                            " ";
                        $sql .= " and iforma = '" . $iforma . "'";
                        echo $cReturn .
                            " Marca registro de pagamento como processado => " .
                            $sql .
                            $cReturn;
                        $rs_update3 = SQLexecuteQuery($sql);
                        if (!$rs_update3) {
                            $msg =
                                "Erro atualizando status de registro (62aabb PDV)." .
                                $cReturn .
                                "$sql" .
                                $cReturn;
                            echo $msg;
                        }
                    } else {
                        echo "SEM PAGAMENTOS PENDENTES (num_rows: " .
                            pg_num_rows($rs_bol) .
                            ")" .
                            $cReturn;
                    }
                }
            } else {
                echo "SEM BOLETOS BANCARIOS (DIST)" . $cReturn;
            }
        }
    } else {
        echo "SEM PIX PDV PRE com (vg.vg_ultimo_status = " .
            $GLOBALS["STATUS_VENDA"]["DADOS_PAGTO_RECEBIDO"] .
            ") ou (vg.vg_ultimo_status = " .
            $GLOBALS["STATUS_VENDA"]["PAGTO_CONFIRMADO"] .
            "): $msg" .
            $cReturn;
    }

    // Em certos casos o status da venda fica em vg_ultimo_status=8 (PAGTO_CONFIRMADO) com o pagamento completo e os PINs entregues
    //	-> tem que passar para vg_ultimo_status=5 (VENDA_REALIZADA)
    $sql =
        "select vg.vg_id, pag.status, vg.vg_ultimo_status  
                                from tb_dist_venda_games vg 
                                        inner join tb_pag_compras pag on pag.idvenda = vg.vg_id 
                                where (vg.vg_ultimo_status = " .
        $GLOBALS["STATUS_VENDA"]["PAGTO_CONFIRMADO"] .
        "	
                                        and vg_pagto_tipo = " .
        $GLOBALS["PAGAMENTO_PIX_NUMERIC"] .
        ") 
                                        and vg_pagto_data_inclusao > '2021-01-01' 
                                order by vg_data_inclusao desc";
    if ($bDebug) {
        //	echo "DEBUG (AN1000): ".$sql.PHP_EOL;
        echo $cReturn .
            $cReturn .
            $cReturn .
            "ELAPSED TOTAL TIME (1abc): " .
            number_format(getmicrotime() - $time_start_stats, 2, ".", ".") .
            $cReturn;
    }

    $rs_venda_pendentes = SQLexecuteQuery($sql);
    if ($rs_venda_pendentes && pg_num_rows($rs_venda_pendentes) > 0) {
        while ($rs_venda_pendentes_row = pg_fetch_array($rs_venda_pendentes)) {
            $vg_id_pendente = $rs_venda_pendentes_row["vg_id"];
            $pag_status = $rs_venda_pendentes_row["status"];
            $vg_ultimo_status = $rs_venda_pendentes_row["vg_ultimo_status"];

            if (
                $vg_ultimo_status ==
                $GLOBALS["STATUS_VENDA"]["PAGTO_CONFIRMADO"]
            ) {
                // 8
                $sql =
                    "update tb_dist_venda_games
                                                set vg_ultimo_status = " .
                    $GLOBALS["STATUS_VENDA"]["VENDA_REALIZADA"] .
                    "
                                                where vg_id = " .
                    $vg_id_pendente;

                echo "==>> Atualiza status de venda PDV de '" .
                    $GLOBALS["STATUS_VENDA"]["PAGTO_CONFIRMADO"] .
                    "' para '" .
                    $GLOBALS["STATUS_VENDA"]["VENDA_REALIZADA"] .
                    "' (vg_id pendente = " .
                    $vg_id_pendente .
                    ")" .
                    PHP_EOL;

                $ret = SQLexecuteQuery($sql);
                if (!$ret) {
                    $msg =
                        "Erro ao atualizar venda PDV com status pendente (pagamento PIX PDV)." .
                        PHP_EOL;
                } else {
                    echo "Venda PDV status pendente vg_id:$vg_id_pendente, status ajustado de PAGTO_CONFIRMADO -> VENDA_REALIZADA (" .
                        date("d/m/Y - H:i:s") .
                        ")" .
                        PHP_EOL;
                }
            } else {
                echo "Venda PDV status pendente vg_id:$vg_id_pendente, status do pagamento != " .
                    $GLOBALS["STATUS_VENDA"]["PAGTO_CONFIRMADO"] .
                    ", nada feito (" .
                    date("d/m/Y - H:i:s") .
                    ")" .
                    PHP_EOL;
            }
        }
    }

    $msg =
        $header .
        $msg .
        "------------------------------------------------------------------------" .
        PHP_EOL;
    //echo $msg;
    if ($bDebug) {
        echo $cReturn .
            $cReturn .
            $cReturn .
            "ELAPSED TOTAL TIME: " .
            number_format(getmicrotime() - $time_start_stats, 2, ".", ".") .
            $cReturn;
    }

    $msg =
        $header .
        $msg .
        "------------------------------------------------------------------------" .
        $cReturn;

    return $msg;
} // end conciliacaoAutomaticaPagtoPIXemPDV

/*
    // => o correto é conciliar a partir de tb_pag_compras com status=3
*/
function conciliaExpressMoneyLH_PagtoOnline($venda_id, $usuario_id, $parametros)
{
    //Validacoes
    $msg = "";

    //Valida usuario_id
    if (!$usuario_id) {
        $msg = "Código do usuário PDV não fornecido." . PHP_EOL;
    } elseif (!is_numeric($usuario_id)) {
        $msg = "Código do usuário PDV inválido." . PHP_EOL;
    }

    //Recupera o boleto pendente
    if ($msg == "") {
        $sql =
            "select * from tb_pag_compras pag
                                where pag.idvenda = " . $venda_id;
        $rs_pagto = SQLexecuteQuery($sql);
        if (!$rs_pagto || pg_num_rows($rs_pagto) == 0) {
            $msg = "Nenhum pagamento encontrado." . PHP_EOL;
        } else {
            $rs_pagto_row = pg_fetch_array($rs_pagto);
            $pag_data = $rs_pagto_row["datainicio"];
            $pag_valor = $rs_boleto_row["total"] / 100;
            $pag_banco = $rs_boleto_row["banco"];
            //				$pag_documento = $rs_boleto_row['???'];
            echo "pag_data: '$pag_data', pag_valor: $pag_valor, pag_banco: $pag_banco, pag_documento: '$pag_documento', <br>";
        }
    }

    //Inicia transacao
    if ($msg == "") {
        $sql = "BEGIN TRANSACTION ";
        $ret = SQLexecuteQuery($sql);
        if (!$ret) {
            $msg = "Erro ao iniciar transação." . PHP_EOL;
        }
    }

    //Concilia boleto
    if ($msg == "") {
        // a esta altura já deve estar status=3
        $sql =
            "update tb_pag_compras set status_processed = 1, dataconfirma = CURRENT_TIMESTAMP where idvenda = " .
            $venda_id .
            "";
        echo "sqlAZ2: $sql [" . getmicrotime() . "]" . PHP_EOL;
        $ret = SQLexecuteQuery($sql);
        if (!$ret) {
            $msg = "Erro ao conciliar boleto." . PHP_EOL;
        }
    }

    //Credita valor do boleto no usuário LH
    if ($msg == "") {
        $sql =
            "update dist_usuarios_games set ug_perfil_saldo = coalesce(ug_perfil_saldo,0) + " .
            $parametros["valor"] .
            "
                                where ug_id =" .
            $usuario_id;
        echo "sqlAZ3: $sql [" . getmicrotime() . "]" . PHP_EOL;
        $ret = SQLexecuteQuery($sql);
        if (!$ret) {
            $msg = "Erro ao creditar valor do boleto no usuário PDV." . PHP_EOL;
        }
    }

    //Completa venda LH
    if ($msg == "") {
        $sql =
            "update tb_dist_venda_games set vg_ultimo_status = " .
            $GLOBALS["STATUS_VENDA"]["VENDA_REALIZADA"] .
            ", vg_concilia = 1, vg_data_concilia = CURRENT_TIMESTAMP
                                where vg_id = " .
            $venda_id .
            " and vg_ug_id = " .
            $usuario_id .
            ";";
        echo "sqlA4: <span style='background-color:#CCCC66'>$sql</span><br>";
        $ret = SQLexecuteQuery($sql);
        if (!$ret) {
            $msg = "Erro ao creditar valor do boleto no usuário PDV." . PHP_EOL;
        }
    }

    //Finaliza transacao
    if ($msg == "") {
        $sql = "COMMIT TRANSACTION ";
        $ret = SQLexecuteQuery($sql);
        //echo "Comentários em COMMIT TRANSACTION  ============================================<br>";
        if (!$ret) {
            $msg = "Erro ao comitar transação." . PHP_EOL;
        }
    } else {
        $sql = "ROLLBACK TRANSACTION ";
        //echo "Comentários em ROLLBACK TRANSACTION   ============================================<br>";
        $ret = SQLexecuteQuery($sql);
        if (!$ret) {
            $msg = "Erro ao dar rollback na transação." . PHP_EOL;
        }
    }

    return $msg;
}

function verificaSomenteDebito($venda_id)
{
    $msg = "";
    $retorno = 0;

    //Recupera modelos
    if ($msg == "") {
        $sql =
            "select * from tb_dist_venda_games vg where vg.vg_id = " .
            $venda_id;
        $rs_venda = SQLexecuteQuery($sql);
        if (!$rs_venda || pg_num_rows($rs_venda) == 0) {
            $msg = "Nenhum produto encontrado.(WM-435)" . PHP_EOL;
        }
    }

    if ($msg == "") {
        //Verifica cada item de cada produro
        if ($rs_venda_row = pg_fetch_array($rs_venda)) {
            $retorno = $rs_venda_row["vg_somente_debito"];
        }
    }

    return $retorno;
} //end function verificaSomenteDebito

function conciliacaoAutomaticaBoletoExpressMoneyLH($vg_id = null)
{
    $time_start_stats = getmicrotime();
    $i = 1;
    $n = 0;
    $custoBoleto = $GLOBALS["BOLETO_MONEY_ITAU_TAXA_CUSTO_BANCO_2"]; //1.10; /// Utilizado somente para o Itau

    //header
    $header =
        PHP_EOL .
        "------------------------------------------------------------------------" .
        PHP_EOL;
    $header .=
        "Conciliacao Automatica de Boleto ExpressMoney PDV (functions_vendaGames.php)" .
        PHP_EOL;
    $header .= date("d/m/Y - H:i:s") . "" . PHP_EOL . PHP_EOL;
    $msg = "";

    // Recupera as vendas pendentes de boleto ExpressMoney LH
    if ($msg == "") {
        $sql =
            "select * from tb_dist_venda_games vg 
                                where ((vg.vg_ultimo_status = " .
            $GLOBALS["STATUS_VENDA"]["PEDIDO_EFETUADO"] .
            ") or 
                                          (vg.vg_ultimo_status = " .
            $GLOBALS["STATUS_VENDA"]["DADOS_PAGTO_RECEBIDO"] .
            ") or 
                                          (vg.vg_ultimo_status = " .
            $GLOBALS["STATUS_VENDA"]["PAGTO_CONFIRMADO"] .
            ")) 
                                                and vg_pagto_tipo = " .
            $GLOBALS["FORMAS_PAGAMENTO"]["BOLETO_BANCARIO"] .
            "
                                                 and vg_concilia=0 and substr(vg_pagto_num_docto,1,1) = '4'
                                                 and vg_data_inclusao > (CURRENT_DATE - INTERVAL '15 days')
                                ";
        if ($vg_id && $vg_id > 0) {
            $sql .= "and vg.vg_id = $vg_id	";
        }
        echo "sqlA0: $sql" . PHP_EOL;
        $rs_venda = SQLexecuteQuery($sql);
        if (!$rs_venda || pg_num_rows($rs_venda) == 0) {
            $msg = "Nenhuma venda encontrada." . PHP_EOL;
        }
        echo "Encontrados " . pg_num_rows($rs_venda) . " registros" . PHP_EOL;
        $n = pg_num_rows($rs_venda);
    }

    // Busca boletos ExpressMoney LH
    if ($msg == "") {
        while ($rs_venda_row = pg_fetch_array($rs_venda)) {
            $vg_id = $rs_venda_row["vg_id"];
            $vg_pagto_banco = $rs_venda_row["vg_pagto_banco"];
            $vg_pagto_num_docto = $rs_venda_row["vg_pagto_num_docto"];
            $vg_ultimo_status_obs = $rs_venda_row["vg_ultimo_status_obs"];
            $vg_ug_id = $rs_venda_row["vg_ug_id"];

            // obtem o valor total da venda
            $sql =
                "select * from dist_boleto_bancario_games 
                                        where bbg_ug_id=" .
                $vg_ug_id .
                " and bbg_pago=0 and  bbg_vg_id=" .
                $vg_id .
                "
                                        order by bbg_data_inclusao desc";

            echo " (vg_id: $vg_id) elapsed total time (" .
                $i++ .
                " de " .
                $n .
                "): " .
                number_format(getmicrotime() - $time_start_stats, 2, ".", ".") .
                "" .
                PHP_EOL;

            $rs_venda_dist = SQLexecuteQuery($sql);
            if ($rs_venda_dist && pg_num_rows($rs_venda_dist) > 0) {
                //echo "  Encontrados (2) ".pg_num_rows($rs_venda_dist)." registros dist".PHP_EOL;

                while ($rs_venda_dist_row = pg_fetch_array($rs_venda_dist)) {
                    $valor = $rs_venda_dist_row["bbg_valor"];
                    $valor_taxa = $rs_venda_dist_row["bbg_valor_taxa"];

                    //Procura boleto
                    $sql = "select bol_codigo ";
                    $sql .= "from boletos_pendentes, bancos_financeiros ";
                    $sql .=
                        "where (bol_banco = bco_codigo) and (bco_rpp = 1) and bol_aprovado = 0 and bol_banco = '" .
                        $vg_pagto_banco .
                        "' ";
                    if (
                        $vg_pagto_banco == "237" ||
                        $vg_pagto_banco == "341" ||
                        $vg_pagto_banco == "033"
                    ) {
                        if ($vg_pagto_banco == "341") {
                            $sql .=
                                " and bol_documento = '4" .
                                str_pad($vg_id, 8, "0", STR_PAD_LEFT) .
                                "'";
                            $sql .=
                                " and bol_valor = " . ($valor - $custoBoleto); //($total_geral + $GLOBALS['BOLETO_MONEY_BRADESCO_TAXA_ADICIONAL']);
                        }
                        //end if($vg_pagto_banco == "341")
                        else {
                            $sql .=
                                " and bol_documento like '4" .
                                substr($vg_pagto_num_docto, 1) .
                                "%'";
                            $sql .= " and bol_valor = " . $valor; //($total_geral + $GLOBALS['BOLETO_MONEY_BRADESCO_TAXA_ADICIONAL']);
                        } //end else if($vg_pagto_banco == "341")
                    } else {
                        $msg .=
                            PHP_EOL .
                            "Venda " .
                            $vg_id .
                            ": Venda por boleto sem banco definido (vg_pagto_banco: '$vg_pagto_banco')." .
                            PHP_EOL;
                        continue;
                    }

                    $rs_bol = SQLexecuteQuery($sql);
                    if ($rs_bol && pg_num_rows($rs_bol) > 0) {
                        $rs_bol_row = pg_fetch_array($rs_bol);
                        $bol_codigo = $rs_bol_row["bol_codigo"];

                        $msg .=
                            PHP_EOL .
                            "Venda " .
                            $vg_id .
                            ": Boleto " .
                            $bol_codigo .
                            ":" .
                            PHP_EOL;
                        $parametros["ultimo_status_obs"] =
                            "Conciliação automática em " .
                            date("d/m/Y - H:i:s") .
                            "" .
                            PHP_EOL;
                        if (trim($vg_ultimo_status_obs) != "") {
                            $parametros["ultimo_status_obs"] =
                                $vg_ultimo_status_obs .
                                "" .
                                PHP_EOL .
                                $parametros["ultimo_status_obs"];
                        }
                        $parametros["PROCESS_AUTOM"] = "1";
                        $parametros["valor_total"] = $valor;
                        $parametros["valor"] = $valor - $valor_taxa;
                        $parametros["valor_taxa"] = $valor_taxa;

                        //Concilia
                        $msgConcilia = "";
                        if ($msgConcilia == "") {
                            $msgConcilia = conciliaExpressMoneyLH_boleto(
                                $bol_codigo,
                                $vg_id,
                                $vg_ug_id,
                                $parametros
                            );
                            if ($msgConcilia == "") {
                                $msg .=
                                    "Conciliacao: Conciliado com sucesso." .
                                    PHP_EOL;
                            } else {
                                $msg .= "Conciliacao: " . $msgConcilia;
                            }
                        }

                        //Gera venda
                        if ($msgConcilia == "") {
                            $msgConcilia = processaExpressMoneyLH(
                                $vg_id,
                                1,
                                $parametros
                            );
                            if ($msgConcilia == "") {
                                $msg .=
                                    "Processamento: Processado com sucesso." .
                                    PHP_EOL;
                            } else {
                                $msg .= "Processamento: " . $msgConcilia;
                            }
                        }

                        //envia email para o cliente
                        if ($msgConcilia == "") {
                            $msgConcilia = processaEmailExpressMoneyLH(
                                $vg_id,
                                $parametros
                            );
                            if ($msgConcilia == "") {
                                $msg .=
                                    "Envio de email: Enviado com sucesso." .
                                    PHP_EOL;
                            } else {
                                $msg .= "Envio de email: " . $msgConcilia;
                            }
                        }
                    }
                }
            }
        }
    }

    echo "  FINAL elapsed total time (total de $n registros): " .
        number_format(getmicrotime() - $time_start_stats, 2, ".", ".") .
        "s, média de " .
        number_format(
            (getmicrotime() - $time_start_stats) / ($n == 0 ? 1 : $n),
            2,
            ".",
            "."
        ) .
        "s/reg" .
        PHP_EOL;

    $msg =
        $header .
        $msg .
        "------------------------------------------------------------------------" .
        PHP_EOL;

    return $msg;
}

function conciliaExpressMoneyLH_boleto(
    $boleto_id,
    $venda_id,
    $usuario_id,
    $parametros
) {
    //Validacoes
    $msg = "";

    //Valida boleto id
    if (!$boleto_id) {
        $msg = "Código do boleto não fornecido." . PHP_EOL;
    } elseif (!is_numeric($boleto_id)) {
        $msg = "Código do boleto inválido." . PHP_EOL;
    }

    //Valida usuario_id
    if (!$usuario_id) {
        $msg = "Código do usuário PDV não fornecido." . PHP_EOL;
    } elseif (!is_numeric($usuario_id)) {
        $msg = "Código do usuário PDV inválido." . PHP_EOL;
    }

    //Recupera o boleto pendente
    if ($msg == "") {
        $sql =
            "select * from boletos_pendentes bol
                                where bol.bol_codigo = " . $boleto_id;
        $rs_boleto = SQLexecuteQuery($sql);
        if (!$rs_boleto || pg_num_rows($rs_boleto) == 0) {
            $msg = "Nenhum boleto encontrado." . PHP_EOL;
        } else {
            $rs_boleto_row = pg_fetch_array($rs_boleto);
            $bol_data = $rs_boleto_row["bol_data"];
            $bol_valor = $rs_boleto_row["bol_valor"];
            $bol_banco = $rs_boleto_row["bol_banco"];
            $bol_documento = $rs_boleto_row["bol_documento"];
        }
    }

    //Inicia transacao
    if ($msg == "") {
        $sql = "BEGIN TRANSACTION ";
        $ret = SQLexecuteQuery($sql);
        $ret = true; /////////////////////
        if (!$ret) {
            $msg = "Erro ao iniciar transação." . PHP_EOL;
        }
    }

    //Concilia boleto
    if ($msg == "") {
        $sql =
            "update boletos_pendentes set bol_aprovado = 1, bol_aprovado_data = CURRENT_TIMESTAMP, bol_venda_games_id = " .
            $venda_id .
            "
                                where bol_codigo = " .
            $boleto_id;
        echo "sqlA2: $sql" . PHP_EOL;
        $ret = SQLexecuteQuery($sql);
        if (!$ret) {
            $msg = "Erro ao conciliar boleto." . PHP_EOL;
        }
    }

    //Credita valor do boleto no usuário LH
    if ($msg == "") {
        $sql =
            "update dist_usuarios_games set ug_perfil_saldo = coalesce(ug_perfil_saldo,0) + " .
            $parametros["valor"] .
            "
                                where ug_id =" .
            $usuario_id;
        echo "sqlA3: $sql" . PHP_EOL;
        $ret = SQLexecuteQuery($sql);
        if (!$ret) {
            $msg = "Erro ao creditar valor do boleto no usuário PDV." . PHP_EOL;
        }
    }

    //Completa venda LH
    if ($msg == "") {
        $sql =
            "update tb_dist_venda_games set vg_ultimo_status = " .
            $GLOBALS["STATUS_VENDA"]["VENDA_REALIZADA"] .
            ", vg_concilia = 1, vg_data_concilia = CURRENT_TIMESTAMP
                                where vg_id = " .
            $venda_id .
            " and vg_ug_id = " .
            $usuario_id .
            ";";
        echo "sqlA4: $sql" . PHP_EOL;
        $ret = SQLexecuteQuery($sql);
        if (!$ret) {
            $msg = "Erro ao creditar valor do boleto no usuário PDV." . PHP_EOL;
        }
    }

    //Finaliza transacao
    if ($msg == "") {
        $sql = "COMMIT TRANSACTION ";
        $ret = SQLexecuteQuery($sql);
        if (!$ret) {
            $msg = "Erro ao comitar transação." . PHP_EOL;
        }
    } else {
        $sql = "ROLLBACK TRANSACTION ";
        $ret = SQLexecuteQuery($sql);
        if (!$ret) {
            $msg = "Erro ao dar rollback na transação." . PHP_EOL;
        }
    }

    return $msg;
}

function processaExpressMoneyLH($venda_id, $usuario_id, $parametros)
{
    $blDebugMT = false;
    $blShowProgress = false;
    if ($parametros["showProgress"]) {
        $blShowProgress = true;
    }

    set_time_limit(0);
    //ob_end_flush();

    if ($blShowProgress) {
        echo "</td></tr></table>";
    }

    $msg = "";
    if ($blDebugMT) {
        echo "Ponto B1;" . getmicrotime() . "" . PHP_EOL;
    }
    //Recupera a venda
    if ($msg == "") {
        $sql =
            "select * from tb_dist_venda_games vg where vg.vg_id = " .
            $venda_id;

        $rs_venda = SQLexecuteQuery($sql);
        if (!$rs_venda || pg_num_rows($rs_venda) == 0) {
            $msg = "Nenhuma venda encontrada." . PHP_EOL;
        } else {
            $rs_venda_row = pg_fetch_array($rs_venda);
            $vg_ug_id = $rs_venda_row["vg_ug_id"];
            $vg_ultimo_status = $rs_venda_row["vg_ultimo_status"];
            $vg_pagto_tipo = $rs_venda_row["vg_pagto_tipo"];

            //valida status
            if (
                $vg_ultimo_status !=
                $GLOBALS["STATUS_VENDA"]["PEDIDO_EFETUADO"] &&
                $vg_ultimo_status !=
                $GLOBALS["STATUS_VENDA"]["DADOS_PAGTO_RECEBIDO"] &&
                $vg_ultimo_status !=
                $GLOBALS["STATUS_VENDA"]["PAGTO_CONFIRMADO"]
            ) {
                $msg = "Venda não esta no seu status inicial." . PHP_EOL;
            }
        }
    }
    if ($blDebugMT) {
        echo "Ponto B4;" . getmicrotime() . "" . PHP_EOL;
    }
    //Recupera dados do usuario
    if ($msg == "") {
        $sql =
            "select * from dist_usuarios_games ug where ug.ug_id = " .
            $vg_ug_id;
        $rs_usuario = SQLexecuteQuery($sql);
        if (!$rs_usuario || pg_num_rows($rs_usuario) == 0) {
            $msg = "Nenhum cliente encontrado." . PHP_EOL;
        } else {
            $rs_usuario_row = pg_fetch_array($rs_usuario);
            $ug_cel_ddd = $rs_usuario_row["ug_cel_ddd"];
            $ug_cel = $rs_usuario_row["ug_cel"];
            if (!is_numeric($ug_cel_ddd)) {
                $ug_cel_ddd = null;
            }
            if (!is_numeric(str_replace("-", "", $ug_cel))) {
                $ug_cel = null;
            }
        }
    }
    if ($blDebugMT) {
        echo "Ponto B5;" . getmicrotime() . "" . PHP_EOL;
    }

    //Enquanto nao tem deposito e boleto
    $vg_pagto_banco = $rs_venda_row["vg_pagto_banco"];
    $vg_pagto_num_docto = $rs_venda_row["vg_pagto_num_docto"];
    $ped_cod_doc_equiv = "";
    $ped_dep_codigo = null;
    $ped_bol_codigo = null;

    $data_corrente = date("Y/m/d");
    $hora_corrente = date("H:i:s");

    //Inicia transacao
    if ($msg == "") {
        $sql = "BEGIN TRANSACTION ";
        $ret = SQLexecuteQuery($sql);
        if (!$ret) {
            $msg = "Erro ao iniciar transação." . PHP_EOL;
        }
    }
    if ($blDebugMT) {
        echo "Ponto B6;" . getmicrotime() . "" . PHP_EOL;
    }

    //VENDA GAMES
    //---------------------------------------------------------------------------------------------------
    //atualiza status
    if ($msg == "") {
        $sql =
            "update tb_dist_venda_games set 
                                        vg_ultimo_status_obs = " .
            SQLaddFields($parametros["ultimo_status_obs"], "s") .
            ",
                                        vg_ultimo_status = " .
            SQLaddFields(
                $GLOBALS["STATUS_VENDA"]["PROCESSAMENTO_REALIZADO"],
                ""
            ) .
            "
                                where vg_id = " .
            $venda_id;
        $ret = SQLexecuteQuery($sql);
        if (!$ret) {
            $msg = "Erro ao conciliar venda." . PHP_EOL;
        }
    }
    if ($blDebugMT) {
        echo "Ponto B20;" . getmicrotime() . "" . PHP_EOL;
    }

    //Finaliza transacao
    if ($msg == "") {
        $sql = "COMMIT TRANSACTION ";
        $ret = SQLexecuteQuery($sql);
        if (!$ret) {
            $msg = "Erro ao comitar transação." . PHP_EOL;
        }
    } else {
        $sql = "ROLLBACK TRANSACTION ";
        $ret = SQLexecuteQuery($sql);
        if (!$ret) {
            $msg = "Erro ao dar rollback na transação." . PHP_EOL;
        }
    }
    if ($blDebugMT) {
        echo "Ponto B21;" . getmicrotime() . "" . PHP_EOL;
    }

    if ($blShowProgress) {
        echo "<table width='900'><tr><td>";
    }

    return $msg;
}

function processaEmailExpressMoneyLH($venda_id, $parametros)
{
    $blDebugMT = false;

    $msg = "";
    if ($blDebugMT) {
        echo "Ponto C1;" . getmicrotime() . "" . PHP_EOL;
    }

    //Recupera a venda
    if ($msg == "") {
        $sql =
            "select * from tb_dist_venda_games vg " .
            "where vg.vg_id = " .
            $venda_id;
        $rs_venda = SQLexecuteQuery($sql);
        if (!$rs_venda || pg_num_rows($rs_venda) == 0) {
            $msg = "Nenhuma venda encontrada." . PHP_EOL;
        } else {
            $rs_venda_row = pg_fetch_array($rs_venda);
            $vg_ug_id = $rs_venda_row["vg_ug_id"];
            $vg_ultimo_status = $rs_venda_row["vg_ultimo_status"];

            //valida status
            if (
                $vg_ultimo_status !=
                $GLOBALS["STATUS_VENDA"]["PROCESSAMENTO_REALIZADO"] &&
                $vg_ultimo_status !=
                $GLOBALS["STATUS_VENDA"]["VENDA_REALIZADA"] &&
                $vg_ultimo_status !=
                $GLOBALS["STATUS_VENDA"]["PAGTO_CONFIRMADO"]
            ) {
                $msg = "Processamento ainda não realizado." . PHP_EOL;
            }
        }
    }
    if ($blDebugMT) {
        echo "Ponto C2;" . getmicrotime() . "" . PHP_EOL;
    }

    //Recupera dados do usuario
    if ($msg == "") {
        $sql =
            "select * from dist_usuarios_games ug " .
            "where ug.ug_id = " .
            $vg_ug_id;
        $rs_usuario = SQLexecuteQuery($sql);
        if (!$rs_usuario || pg_num_rows($rs_usuario) == 0) {
            $msg = "Nenhum cliente encontrado." . PHP_EOL;
        } else {
            $rs_usuario_row = pg_fetch_array($rs_usuario);
            $ug_email = $rs_usuario_row["ug_email"];
            $ug_tipo_cadastro = $rs_usuario_row["ug_tipo_cadastro"];
            $ug_sexo = $rs_usuario_row["ug_sexo"];
            $ug_nome = $rs_usuario_row["ug_nome"];
            $ug_cpf = $rs_usuario_row["ug_cpf"];
            $ug_rg = $rs_usuario_row["ug_rg"];
            $ug_nome_fantasia = $rs_usuario_row["ug_nome_fantasia"];
            $ug_cnpj = $rs_usuario_row["ug_cnpj"];
            $ug_endereco = $rs_usuario_row["ug_endereco"];
            $ug_numero = $rs_usuario_row["ug_numero"];
            $ug_complemento = $rs_usuario_row["ug_complemento"];
            $ug_bairro = $rs_usuario_row["ug_bairro"];
            $ug_cidade = $rs_usuario_row["ug_cidade"];
            $ug_estado = $rs_usuario_row["ug_estado"];
            $ug_cep = $rs_usuario_row["ug_cep"];
        }
    }
    if ($blDebugMT) {
        echo "Ponto C4;" . getmicrotime() . "" . PHP_EOL;
    }

    //USUARIO
    //---------------------------------------------------------------------------------------------------
    //envia email
    if ($msg == "") {
        $parametros["prepag_dominio"] = "http://www.e-prepag.com.br";
        $parametros["nome_fantasia"] = $ug_nome_fantasia;
        $parametros["tipo_cadastro"] = $ug_tipo_cadastro;
        $parametros["sexo"] = $ug_sexo;
        $parametros["nome"] = $ug_nome;
        $msgEmail = email_cabecalho($parametros);

        //Dados do comprador
        $msgEmail .=
            "	<br>
                                                <table border='0' cellspacing='0' width='90%'>
                                                <tr>
                                                        <td class='texto' colspan='2'><b>DADOS DE CADASTRO</b></td>
                                                </tr>
                                                <tr>
                                                        <td class='texto'> " .
            ($ug_tipo_cadastro == "PF"
                ? $ug_nome .
                "<br>CPF: " .
                $ug_cpf .
                "<br>RG: " .
                $ug_rg .
                "<br>"
                : $ug_nome_fantasia . "<br>CNPJ: " . $ug_cnpj . "<br>") .
            "	
                                                                " .
            $ug_endereco .
            (trim($ug_complemento) == "" ? "" : " - " . $ug_complemento) .
            "<br>
                                                                " .
            $ug_bairro .
            ", " .
            $ug_cidade .
            " - " .
            $ug_estado .
            "<br>
                                                                " .
            $ug_cep .
            "<br>
                                                        </td>
                                                </tr>
                                                </table>";

        //Mensagem
        $msgEmail .=
            "	<br>
                                                <table border='0' cellspacing='0' width='90%'>
                                                <tr>
                                                        <td class='texto'> 
                                                                Seu pagamento de boleto Express Money PDV número <b>" .
            formata_codigo_venda($venda_id) .
            "</b> foi processado com sucesso!<br>
                                                                Na sua conta com a E-Prepag foi creditado o valor de <b>R$" .
            number_format($parametros["valor"], 2, ",", ".") .
            "</b> que já pode ser usado para comprar os produtos disponíveis. 
                                                        </td>
                                                </tr>
                                                </table>";
        if ($blDebugMT) {
            echo "Ponto C5;" . getmicrotime() . "" . PHP_EOL;
        }

        $msgEmail .= "	<br>";

        $msgEmail .= email_rodape($parametros);
        if ($blDebugMT) {
            echo "Ponto C6;" . getmicrotime() . "" . PHP_EOL;
        }

        $subjectEmail = "E-Prepag - Boleto Processado";
        if ($vg_ultimo_status == $GLOBALS["STATUS_VENDA"]["VENDA_REALIZADA"]) {
            $subjectEmail .= " (Reenvio)";
        }
        //Descomentar a linha abaixo caso os PDVs reclamem
        //enviaEmail($ug_email, null, null, $subjectEmail, $msgEmail);
        if ($blDebugMT) {
            echo "Ponto C7;" . getmicrotime() . "" . PHP_EOL;
        }
    }

    //VENDA GAMES
    //---------------------------------------------------------------------------------------------------
    //atualiza status
    if ($msg == "") {
        $sql =
            "update tb_dist_venda_games set 
                                        vg_ultimo_status_obs = " .
            SQLaddFields($parametros["ultimo_status_obs"], "s") .
            ",
                                        vg_ultimo_status = " .
            SQLaddFields($GLOBALS["STATUS_VENDA"]["VENDA_REALIZADA"], "") .
            ",
                                        vg_concilia = 1, vg_data_concilia = CURRENT_TIMESTAMP
                                where vg_id = " .
            $venda_id;
        $ret = SQLexecuteQuery($sql);
        if (!$ret) {
            $msg = "Erro ao atualizar venda." . PHP_EOL;
        }
    }
    if ($blDebugMT) {
        echo "Ponto C8;" . getmicrotime() . "" . PHP_EOL;
    }

    return $msg;
}

function gravaLog_TMP_conciliacao($mensagem)
{
    global $raiz_do_projeto;

    //Arquivo
    $file = $raiz_do_projeto . "log/log_pagamento_TMP_conciliacao.txt";

    //Mensagem
    $mensagem =
        date("Y-m-d H:i:s") .
        " " .
        $_SERVER["SCRIPT_FILENAME"] .
        PHP_EOL .
        $mensagem .
        PHP_EOL;

    //Grava mensagem no arquivo
    if ($handle = fopen($file, "a+")) {
        fwrite($handle, $mensagem);
        fclose($handle);
    }
}

function isVendaDeposito($venda_id)
{
    $msg = "";

    $sql =
        "select vg_deposito_em_saldo from tb_dist_venda_games vg where vg.vg_id = " .
        $venda_id;
    $rs_venda = SQLexecuteQuery($sql);
    if (!$rs_venda || pg_num_rows($rs_venda) == 0) {
        $msg =
            "Nenhuma venda encontrada (em isvendaDeposito($venda_id))." .
            PHP_EOL;
    }

    if ($msg == "") {
        $rs_venda_row = pg_fetch_array($rs_venda);
        $vg_deposito_em_saldo = $rs_venda_row["vg_deposito_em_saldo"];
    }
    $vg_deposito_em_saldo = $vg_deposito_em_saldo == 1 ? 1 : 0;
    return $vg_deposito_em_saldo;
}

?>