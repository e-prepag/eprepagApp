<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);

require_once "../../../includes/constantes.php";
require_once DIR_CLASS . 'gamer/controller/HeaderController.class.php';
require_once DIR_INCS . "config.MeiosPagamentos.php";
$controller = new HeaderController;

require_once DIR_INCS . "inc_register_globals.php";

//Permitindo somente usuários logado a executar este programa
if (isset($controller->logado) && $controller->logado) {

        //validacao
        $msg = "";

        $pagto = $_SESSION['pagamento.pagto'];
        $produtos = $_SESSION['pagamento.total'];
        $iforma = $pagto;

        if ($controller->usuario->b_IsLogin_pagamento()) {

                $total_carrinho = $_SESSION['pagamento.total'];
                $total_carrinho_eppcash = $_SESSION['pagamento.total_eppcash'];
                $taxas = $_SESSION['pagamento.taxa'];

                // ==========================================================================================
                // Faz validação de vendas totais, copia de pagamento.php

                // Testa que usuário comprou no máximo 10 vezes nas últimas 24 horas
                $qtde_last_dayOK = getNVendasMOney($controller->usuario->getId());

                // Calcula o total diario para pagamentos Online Bradesco
                $total_diario = getVendasMoneyTotalDiarioOnline($controller->usuario->getId());

                $b_TentativasDiariasOK = ($qtde_last_dayOK <= $RISCO_GAMERS_SALDO_PAGAMENTOS_DIARIO);
                $b_LimiteDiarioOK = ((($total_carrinho + $total_diario) <= $RISCO_GAMERS_SALDO_TOTAL_DIARIO) && ($qtde_last_dayOK <= $RISCO_GAMERS_SALDO_PAGAMENTOS_DIARIO));

                // Libera pagamento Online Banco do Brasil
                $b_libera_BancodoBrasil = $b_LimiteDiarioOK && $b_TentativasDiariasOK;// && $controller->usuario->b_IsLogin_pagamento_bancodobrasil();

                // Libera Bradesco apenas se limite diario não ultrapassado e tem até 10 compras nas últimas 24 horas	//produtos (Habbo e GPotato) 
                $b_libera_Bradesco = $b_LimiteDiarioOK && $b_TentativasDiariasOK;	//$b_IsProdutoOK && 

                // Libera pagamento pix
                $b_libera_Pix = $b_LimiteDiarioOK && $b_TentativasDiariasOK;

                // Libera pagamento Online Banco Itaú
                $b_libera_BancoItau = $b_LimiteDiarioOK && $b_TentativasDiariasOK && $controller->usuario->b_IsLogin_pagamento_bancoitau();

                $msg_bloqueia_Bradesco = (!$b_libera_Bradesco) ? ((!$b_LimiteDiarioOK) ? " \\n \\n Sua compra de " . number_format($total_carrinho, 2, ',', '.') . " ultrapassa o limite diário de compras on-line." : ((!$b_TentativasDiariasOK) ? " \\n \\n Número de pagamentos online (" . $qtde_last_dayOK . ") ultrapassa o limite diário." : "")) : "";

                $msg_bloqueia_BancodoBrasil = (!$b_libera_BancodoBrasil) ? ((!$b_LimiteDiarioOK) ? " \\n \\n Sua compra de " . number_format($total_carrinho, 2, ',', '.') . " ultrapassa o limite diário de compras on-line." : ((!$b_TentativasDiariasOK) ? " \\n \\n Número de pagamentos online (" . $qtde_last_dayOK . ") ultrapassa o limite diário." : "")) : "";

                $msg_bloqueia_BancoItau = (!$b_libera_BancoItau) ? ((!$b_LimiteDiarioOK) ? " \\n \\n Sua compra de " . number_format($total_carrinho, 2, ',', '.') . " ultrapassa o limite diário de compras on-line." : ((!$b_TentativasDiariasOK) ? " \\n \\n Número de pagamentos online (" . $qtde_last_dayOK . ") ultrapassa o limite diário." : "")) : "";

                $msg_bloqueia_Pix = (!$b_libera_Pix) ? ((!$b_LimiteDiarioOK) ? " \\n \\n Sua compra de " . number_format($total_carrinho, 2, ',', '.') . " ultrapassa o limite diário de compras on-line." : ((!$b_TentativasDiariasOK) ? " \\n \\n Número de pagamentos online (" . $qtde_last_dayOK . ") ultrapassa o limite diário." : "")) : "";

                // finaliza validações
                // ==========================================================================================

                $pagto_venda = $pagto;
                // tipo_cliente   character varying(2),	-- 'M' - Money, 'E' - Money Express, 'LR' - Lanhouse Pré, 'LO' - Lanhouse Pos, 
                $tipo_cliente = "M";
                $numOrder = "00000000000000000";
                $id_usuario_prev = $controller->usuario->getId();
                $cliente_nome_prev = $controller->usuario->getNome();

                unset($_SESSION['sql_pagto_online_insert']);

                if (($pagto == $FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) || ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_DEBITO'])) {
                        require_once DIR_CLASS . "gamer/classIntegracao.php";
                        // gera nova ordem em tb_pag_compras
                        include RAIZ_DO_PROJETO . "banco/bradesco/inc_gen_order.php"; // 
                        $numOrder = $orderId;
                } elseif ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_BB_DEBITO_SUA_CONTA']) {
                        // gera nova ordem em tb_pag_compras
                        include RAIZ_DO_PROJETO . "banco/bancodobrasil/inc_gen_order_bbr.php"; // 
                        $numOrder = $orderId;
                } elseif ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_BANCO_ITAU_ONLINE']) {
                        $pagto_venda = $PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC;
                        // gera nova ordem em tb_pag_compras
                        require_once RAIZ_DO_PROJETO . "banco/itau/inc_config.php";
                        require_once RAIZ_DO_PROJETO . "banco/itau/inc_gen_order_bit.php"; // 
                        $numOrder = $orderId;
                } elseif ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_PIN_EPREPAG']) {
                        // gera nova ordem em tb_pag_compras
                        include RAIZ_DO_PROJETO . "banco/epp/inc_config.php";
                        $numOrder = $orderId;
                        $pagto_venda = $PAGAMENTO_PIN_EPREPAG_NUMERIC;
                } elseif ($pagto == $FORMAS_PAGAMENTO['BOLETO_BANCARIO']) {
                        depositoBoleto($produtos, $controller->usuario->getId());
                        redirect("/game/pagamento/pagto_compr_boleto.php");
                        die("");
                } elseif ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_PIX']) {
                        // gera nova ordem em tb_pag_compras
                        require_once RAIZ_DO_PROJETO . "banco/pix/inc_config.php";
                        $numOrder = $orderId;
                        $pagto_venda = $PAGAMENTO_PIX_NUMERIC;
                } else {
                        die("Erro: forma de pagamento desconhecida. (pagto=$pagto)<br>\n");
                }

                $snome = $controller->usuario->getNome();

                // ver montaCesta_pag() para cesta Money
                $cesta_boleto_pagto_online = "item:Boleto Online Gamers (Saldo)\n1\ncrédito\n" . (100 * $total_carrinho) . "\n";

                // tipo_deposito = 2 -> 'Depósito direto no Saldo'
                $sql = "UPDATE tb_pag_compras SET cliente_nome='" . str_replace("'", "''", $snome) . "', idcliente=" . $controller->usuario->getId() . ", status=1, cesta='" . $cesta_boleto_pagto_online . "', total=" . (100 * ($total_carrinho + $taxas)) . ", tipo_deposito = 2 WHERE numcompra='" . $numOrder . "'";		// "pagto='".$_SESSION['pagamento.pagto']."', "

                $ret = SQLexecuteQuery($sql);
                if (!$ret) {
                        echo "Erro ao atualizar transação de pagamento (2).\n";
                        die("Stop");
                }
        } //end if($controller->usuario->b_IsLogin_pagamento()) 


        //Produtos
        if ($msg == "") {
                if (!$produtos)
                        $msg = "Nenhum produto selecionado.\n";
        }

        $usuarioId = $controller->usuario->getId();
        $vg_ex_email = $controller->usuario->getEmail();


        if ($msg != "") {
                die($msg);
        }

        // processa só se:
        //		- PagtoOnline estiver autorizado para o usuário
        //		- forma de pagto for de fato online
        //		- lan cadastrada como Pre
        if (
                (!$controller->usuario->b_IsLogin_pagamento()) ||
                (!b_IsPagtoOnline($pagto))
        ) {
                $msg = "Pagamento Online para Gamers (Saldo) não processado (pagto: '$pagto', Pagto_online: " . (($controller->usuario->b_IsLogin_pagamento()) ? "OK" : "Não") . ", É pagto. online?: " . ((b_IsPagtoOnline($pagto)) ? "Sim" : "Não") . ")";
                die($msg);
        }


        //Inicia transacao
        if ($msg == "") {
                $sql = "BEGIN TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao iniciar transação.\n";
        }

        //Gera a venda
        if ($msg == "") {
                $venda_id = obterIdVendaValido();
                // Tentar 10 vezes
                $iloop = 1;
                $nloops = 10;
                while (existeIdVenda($venda_id) && ($iloop < $nloops)) {
                        gravaLog_BoletoExpressMoney("" . ($iloop++) . " - venda_id repetido($iloop): " . $venda_id . "\n");
                        $venda_id = obterIdVendaValido();
                }
                // Se ainda não foi encontrado um $venda_id livre vai aparecer um erro e terá que tentar novamente atualizando a página
                if ($iloop >= $nloops) {
                        $msg = "Erro: Desculpe, não foi possível encontrar um IDVenda disponível. Tente novamente ou contate o administrador do site.\n";
                }

                if (!$msg) {
                        //Guarda id da venda no session
                        $_SESSION['venda'] = $venda_id;
                        $_SESSION['pagamento.numorder'] = $orderId;
                        $pagto_venda = getCodigoNumericoParaPagto($pagto);

                        // Salva registro de vendas
                        $sql = "insert into tb_venda_games (" .
                                "vg_id, vg_ug_id, vg_data_inclusao,vg_pagto_data_inclusao, vg_pagto_tipo, " .
                                "vg_ultimo_status, vg_ultimo_status_obs, vg_http_referer_origem, vg_http_referer, vg_http_referer_ip, vg_deposito_em_saldo_valor, vg_valor_eppcash, vg_deposito_em_saldo) values (";
                        $sql .= SQLaddFields($venda_id, "") . ",";
                        $sql .= SQLaddFields($usuarioId, "") . ",";
                        $sql .= SQLaddFields("CURRENT_TIMESTAMP", "") . ",";
                        $sql .= SQLaddFields("CURRENT_TIMESTAMP", "") . ",";
                        $sql .= SQLaddFields($pagto_venda, "") . ",";
                        $sql .= SQLaddFields($GLOBALS['STATUS_VENDA']['PEDIDO_EFETUADO'], "") . ",";
                        $sql .= SQLaddFields("", "s") . ", ";
                        $sql .= SQLaddFields($_SESSION['epp_origem'], "s") . ", ";
                        $sql .= SQLaddFields($_SESSION['epp_origem_referer'], "s") . ", ";
                        $sql .= SQLaddFields($_SESSION['epp_remote_addr'], "s") . ", ";
                        $sql .= SQLaddFields(number_format($total_carrinho, 2, '.', '.'), "") . ", ";
                        $sql .= SQLaddFields($total_carrinho_eppcash, "") . ", ";
                        $sql .= SQLaddFields("1", "") . ")";
                        //gravaLog_DebugTMP(" TESTE 433443 a: (total_carrinho: '$total_carrinho') ".$sql."\n");
                        $ret = SQLexecuteQuery($sql);
                        if (!$ret) {
                                $msg = "Erro ao inserir venda. Por favor, tente novamente atualizando a página. Obrigado *.\n";
                                gravaLog_BoletoExpressMoney($msg . "\n" . $sql);
                        }
                }//end if(!$msg)

                if (!$msg) {
                        // Salva venda_id em tb_pag_compras
                        $sql = "UPDATE tb_pag_compras SET idvenda=" . $venda_id . " WHERE numcompra='" . $numOrder . "'";
                        $ret = SQLexecuteQuery($sql);
                        if (!$ret) {
                                $msg = "Erro ao atualizar transação de pagamento (2a, id_venda=$id_venda, numcompra='" . $numOrder . "').\n";
                                gravaLog_BoletoExpressMoney($msg . "\n" . $sql);
                        }
                }//end if(!$msg)
        }//end if($msg == "")

        if ($msg == "") {
                //Log na base
                usuarios_games_log($GLOBALS['USUARIO_GAMES_LOG_TIPOS']['VENDA'], $usuarioId, $venda_id);

                //obtem o valor total da venda
                //----------------------------------------------------
                // $produtos
                $total_geral = $produtos;
                $taxa_adicional = 0;

                // Marca esta venda como deposito.em.saldo, para uso em venda_e_modelos_logica.php
                $_SESSION['pagamento.pagto.deposito.em.saldo'] = $pagto;
                $_SESSION['pagamento.pagto.deposito.em.saldo.num.docto'] = true;
        }

        //Finaliza transacao
        if ($msg == "") {
                $sql = "COMMIT TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao comitar transação.\n";
        } else {
                $sql = "ROLLBACK TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if (!$ret)
                        $msg = "Erro ao dar rollback na transação.\n";
        }

        //token
        if ($msg == "") {
                //$token = date('YmdHis') . "," . $venda_id . "," . $usuarioId;
                $token = date('YmdHis', strtotime("+20 day")) . "," . $venda_id . "," . $usuarioId;
                $objEncryption = new Encryption();
                $token = $objEncryption->encrypt($token);
        }

        $msgEmail = "";
        $str_token = "ABCDEFGHIJ";

        //Envia email
        //--------------------------------------------------------------------------------
        if ($msg == "") {

                $pagto_tipo_email = $pagto;
                $valor = number_format($total_carrinho, 2, ",", ".");
                $objEnvioEmailAutomatico = new EnvioEmailAutomatico(TIPO_USUARIO_GAMER, 'AdicaoSaldoGamer');
                $objEnvioEmailAutomatico->setUgID($controller->usuario->getId());
                $objEnvioEmailAutomatico->setSaldoAdicionado($valor);
                $formaPagamento = (array_key_exists($pagto_tipo_email, $FORMAS_PAGAMENTO_DESCRICAO)) ? $FORMAS_PAGAMENTO_DESCRICAO[$pagto_tipo_email] : 'Online';
                $objEnvioEmailAutomatico->setFormaPagamento($formaPagamento);
                $objEnvioEmailAutomatico->setPedido(formata_codigo_venda($venda_id));
                $objEnvioEmailAutomatico->MontaEmailEspecifico();

        }//end if($msg == "")

        //Retorno
        if ($msg != "") {
                $msg = "<script>alert('" . str_replace("\n", "\\n", $msg) . "');</script>";
                echo $msg;
                exit;
        } else {
                if (b_IsPagtoOnline($pagto)) {
                        $msg = "<font color='red'><strong><span class='style3'>";
                        $msg .= "Sua compra está completa e o boleto foi cadastrado com sucesso: <br>";
                        $msg .= "<a href='index.php'>clique aqui</a> para continuar comprando!!";
                        $msg .= "</span></strong></font>";
                        $msg = str_replace($str_token, $msg, $msgEmail);

                        $strRedirect = "/game/pagamento/pagto_compr_online.php";
                        //Redireciona
                        redirect($strRedirect);

                        exit;
                } else {
                        echo "ERRO 64532.";
                }
        }//end else do if($msg != "")
}//end if(isset($controller->logado) && $controller->logado) 

function depositoBoleto($total_geral, $usuarioId)
{

        global $controller;

        //Variavel de controle
        $msg = "";

        //Inicio da transação
        $sql = "BEGIN TRANSACTION ";
        $ret = SQLexecuteQuery($sql);
        if (!$ret)
                $msg = "Erro ao iniciar transação.\n";

        //Gera a venda
        if ($msg == "") {

                $venda_id = obterIdVendaValido();
                $GLOBALS['_SESSION']['venda'] = $venda_id;

                $instConversionPINsEPP = new ConversionPINsEPP;
                $total_geral_epp = $instConversionPINsEPP->get_ValorEPPCash('E', $total_geral);

                $sql = "insert into tb_venda_games (" .
                        "vg_id, vg_ug_id, vg_data_inclusao, vg_pagto_tipo, " .
                        "vg_ultimo_status, vg_ultimo_status_obs, vg_http_referer_origem, vg_http_referer, vg_deposito_em_saldo_valor, vg_valor_eppcash, vg_deposito_em_saldo) values (";
                $sql .= SQLaddFields($venda_id, "") . ",";
                $sql .= SQLaddFields($usuarioId, "") . ",";
                $sql .= SQLaddFields("CURRENT_TIMESTAMP", "") . ",";
                $sql .= SQLaddFields($GLOBALS['FORMAS_PAGAMENTO']['BOLETO_BANCARIO'], "") . ",";
                $sql .= SQLaddFields($GLOBALS['STATUS_VENDA']['PEDIDO_EFETUADO'], "") . ",";
                $sql .= SQLaddFields("", "s") . ", ";
                $sql .= SQLaddFields($GLOBALS['_SESSION']['epp_origem'], "s") . ",";
                $sql .= SQLaddFields($GLOBALS['_SESSION']['epp_origem_referer'], "s") . ", ";
                $sql .= SQLaddFields(number_format($total_geral, 2, '.', '.'), "") . ", ";
                $sql .= SQLaddFields($total_geral_epp, "") . ", ";
                $sql .= SQLaddFields("1", "") . ")";
                //gravaLog_DebugTMP(" TESTE 433443 b (total_geral: '$total_geral') : ".$sql."\n");
                $ret = SQLexecuteQuery($sql);
                if (!$ret) {
                        $msg = "Erro ao inserir venda. Por favor, tente novamente atualizando a página. Obrigado.\n";
                        gravaLog_BoletoExpressLH($msg . "\n" . $sql);
                }
        }//end if($msg == "")

        //Log na base
        if ($msg == "") {
                usuarios_games_log($GLOBALS['USUARIO_GAMES_LOG_TIPOS']['VENDA'], $usuarioId, $venda_id);
        }

        //Boleto
        if ($msg == "") {

                //Formato do Nosso Numero e Numero do documento
                //----------------------------------------------------
                //6EEEEECCCCC Onde: 
                //6 – identifica Gamer - Depósito em Saldo
                //CCCCC – código do cliente MONEY (composto com zeros a esquerda)
                //VVVVV – codigo da venda (composto com zeros a esquerda)
                //$num_doc = "6" . substr("00000" . $usuarioId, -5) . substr("00000" . $venda_id, -5);
                $num_doc = "6" . "00" . str_pad($venda_id, 8, "0", STR_PAD_LEFT);
                $qtde_dias_venc = $GLOBALS['BOLETO_MONEY_BRADESCO_QTDE_DIAS_VENCIMENTO'];

                if ($total_geral < $GLOBALS['RISCO_GAMERS_VALOR_MIN_PARA_TAXA'])
                        $taxa_adicional = $GLOBALS['BOLETO_MONEY_BRADESCO_TAXA_ADICIONAL'];
                else
                        $taxa_adicional = 0;

                if (BANCO_BOLETO == "asaas" || $controller->usuario->getId() == 1354068) {
                        $bco_codigo = $GLOBALS['BOLETO_MONEY_ASAAS_COD_BANCO'];
                } elseif (BANCO_BOLETO == "bradesco") {
                        $bco_codigo = $GLOBALS['BOLETO_MONEY_BRADESCO_COD_BANCO'];
                }
                $url_boleto = "BoletoWebBradescoCommerce.php";

                // Usa Boleto Itau para alguns usuários
                if ($controller->logado) {
                        if (BANCO_BOLETO == "asaas" || $controller->usuario->getId() == 1354068) {
                                if ($total_geral < $GLOBALS['RISCO_GAMERS_VALOR_MIN_PARA_TAXA'])
                                        $taxa_adicional = $GLOBALS['BOLETO_MONEY_BRADESCO_TAXA_ADICIONAL'];
                                else
                                        $taxa_adicional = 0;
                                $qtde_dias_venc = $GLOBALS['BOLETO_MONEY_BRADESCO_QTDE_DIAS_VENCIMENTO'];
                                $bco_codigo = $GLOBALS['BOLETO_MONEY_ASAAS_COD_BANCO'];
                                $num_doc = "6" . "00" . str_pad($venda_id, 8, "0", STR_PAD_LEFT);
                        } elseif (BANCO_BOLETO == "bradesco") {
                                if ($total_geral < $GLOBALS['RISCO_GAMERS_VALOR_MIN_PARA_TAXA'])
                                        $taxa_adicional = $GLOBALS['BOLETO_MONEY_BRADESCO_TAXA_ADICIONAL'];
                                else
                                        $taxa_adicional = 0;
                                $qtde_dias_venc = $GLOBALS['BOLETO_MONEY_BRADESCO_QTDE_DIAS_VENCIMENTO'];
                                $bco_codigo = $GLOBALS['BOLETO_MONEY_BRADESCO_COD_BANCO'];
                                $num_doc = "6" . "00" . str_pad($venda_id, 8, "0", STR_PAD_LEFT);
                        }//end if($controller->usuario->b_Is_Boleto_Bradesco())
                        elseif ($controller->usuario->b_Is_Boleto_Banespa()) {
                                $qtde_dias_venc = $GLOBALS['BOLETO_MONEY_BANESPA_QTDE_DIAS_VENCIMENTO'];
                                $bco_codigo = $GLOBALS['BOLETO_MONEY_BANCO_BANESPA_COD_BANCO'];
                                if ($total_geral < $GLOBALS['RISCO_GAMERS_VALOR_MIN_PARA_TAXA'])
                                        $taxa_adicional = $GLOBALS['BOLETO_MONEY_BANESPA_TAXA_ADICIONAL'];
                                else
                                        $taxa_adicional = 0;
                                $num_doc = "6" . "000" . str_pad($venda_id, 8, "0", STR_PAD_LEFT);
                        } elseif ($controller->usuario->b_Is_Boleto_Itau()) {
                                $qtde_dias_venc = $GLOBALS['BOLETO_MONEY_ITAU_QTDE_DIAS_VENCIMENTO'];
                                $bco_codigo = $GLOBALS['BOLETO_MONEY_ITAU_COD_BANCO'];
                                if ($total_geral < $GLOBALS['RISCO_GAMERS_VALOR_MIN_PARA_TAXA'])
                                        $taxa_adicional = $GLOBALS['BOLETO_MONEY_ITAU_TAXA_ADICIONAL'];
                                else
                                        $taxa_adicional = 0;
                        }
                }

                //Insere boleto na base
                //----------------------------------------------------
                $sql = "insert into boleto_bancario_games (" .
                        "bbg_ug_id, bbg_vg_id, bbg_data_inclusao, bbg_valor, bbg_valor_taxa, " .
                        "bbg_bco_codigo, bbg_documento, bbg_data_venc" .
                        ") values (";
                $sql .= SQLaddFields($usuarioId, "") . ",";
                $sql .= SQLaddFields($venda_id, "") . ",";
                $sql .= SQLaddFields("CURRENT_TIMESTAMP", "") . ",";
                $sql .= SQLaddFields($total_geral + $taxa_adicional, "") . ",";
                $sql .= SQLaddFields($taxa_adicional, "") . ",";
                $sql .= SQLaddFields($bco_codigo, "") . ",";
                $sql .= SQLaddFields($num_doc, "s") . ","; //documento
                $sql .= SQLaddFields("CURRENT_DATE + interval '$qtde_dias_venc day'", "") . ")"; //vencimento
                $ret = SQLexecuteQuery($sql);

                //atualiza dados do pagamento e status da venda
                if ($ret) {
                        $sql = "update tb_venda_games set 
                                            vg_pagto_data_inclusao = " . SQLaddFields("CURRENT_TIMESTAMP", "") . ",
                                            vg_pagto_banco = '" . $bco_codigo . "',
                                            vg_pagto_num_docto = '" . $num_doc . "',
                                            vg_ultimo_status = " . SQLaddFields($GLOBALS['STATUS_VENDA']['PEDIDO_EFETUADO'], "") . "
                                    where vg_id = " . $venda_id;
                        // Marca esta venda como deposito.em.saldo, para uso em venda_e_modelos_logica.php
                        $pagto = $_SESSION['pagamento.pagto'];
                        $_SESSION['pagamento.pagto.deposito.em.saldo'] = $pagto;
                        $_SESSION['pagamento.pagto.deposito.em.saldo.num.docto'] = $num_doc;

                        $ret = SQLexecuteQuery($sql);
                        if (!$ret)
                                $msg = "Erro ao atualizar status da venda (3223).\n";
                }
        } else {
                gravaLog_DebugTMP(" TESTE 43343232: " . $msg . "\n");
        }

        //Finaliza transacao
        if ($msg == "") {
                $sql = "COMMIT TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                //if(!$ret) $msg = "Erro ao comitar transação.\n";
        } else {
                $sql = "ROLLBACK TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                //if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
        }

        //Envia email
        //--------------------------------------------------------------------------------
        if ($msg == "") {
                $GLOBALS['_SESSION']['boleto_imagem'] = 'AdicaoSaldoGamer';
                $GLOBALS['_SESSION']['valor_pedido_gamer'] = number_format($total_geral, 2, ',', '.');
        }

        //Retorno
        if ($msg != "") {
                $msg = "<script>alert('" . str_replace("\n", "\\n", $msg) . "');</script>";
                echo $msg;
                exit;
        }

}//end function depositoBoleto 
?>