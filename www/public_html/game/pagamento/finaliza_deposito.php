<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);

require_once "../../../includes/constantes.php";
require_once DIR_CLASS . 'gamer/controller/HeaderController.class.php';
require_once DIR_INCS . "config.MeiosPagamentos.php";
$controller = new HeaderController;

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
                $b_libera_BancodoBrasil = $b_LimiteDiarioOK && $b_TentativasDiariasOK; // && $controller->usuario->b_IsLogin_pagamento_bancodobrasil();

                // Libera Bradesco apenas se limite diario não ultrapassado e tem até 10 compras nas últimas 24 horas	//produtos (Habbo e GPotato) 
                $b_libera_Bradesco = $b_LimiteDiarioOK && $b_TentativasDiariasOK;        //$b_IsProdutoOK && 

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

                $sql = "
                    UPDATE tb_pag_compras
                    SET
                        cliente_nome = $1,
                        idcliente = $2,
                        status = 1,
                        cesta = $3,
                        total = $4,
                        tipo_deposito = 2
                    WHERE numcompra = $5
                ";

                $params = array(
                        $snome, // $1
                        $controller->usuario->getId(), // $2
                        $cesta_boleto_pagto_online, // $3
                        100 * ($total_carrinho + $taxas), // $4
                        $numOrder // $5
                );

                $ret = SQLexecuteQueryParams($sql, $params);
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

                        // preparar valores com fallback (evita notices se índice de sessão não existir)
                        $param_vg_id   = $venda_id;
                        $param_vg_ug_id = $usuarioId;
                        $param_vg_pagto_tipo = $pagto_venda;
                        $param_vg_ultimo_status = $GLOBALS['STATUS_VENDA']['PEDIDO_EFETUADO'];
                        $param_vg_ultimo_status_obs = ''; // era SQLaddFields("", "s")
                        $param_vg_http_referer_origem = isset($_SESSION['epp_origem']) ? $_SESSION['epp_origem'] : null;
                        $param_vg_http_referer = isset($_SESSION['epp_origem_referer']) ? $_SESSION['epp_origem_referer'] : null;
                        $param_vg_http_referer_ip = isset($_SESSION['epp_remote_addr']) ? $_SESSION['epp_remote_addr'] : null;

                        // valores numéricos: normalizar para string com ponto decimal (PostgreSQL aceita)
                        $param_vg_deposito_em_saldo_valor = number_format((float)$total_carrinho, 2, '.', '');
                        $param_vg_valor_eppcash = number_format((float)$total_carrinho_eppcash, 2, '.', '');
                        $param_vg_deposito_em_saldo = 1;

                        // montar SQL com CURRENT_TIMESTAMP inlined (não como parâmetro)
                        $sql = "
                                    INSERT INTO tb_venda_games (
                                        vg_id,
                                        vg_ug_id,
                                        vg_data_inclusao,
                                        vg_pagto_data_inclusao,
                                        vg_pagto_tipo,
                                        vg_ultimo_status,
                                        vg_ultimo_status_obs,
                                        vg_http_referer_origem,
                                        vg_http_referer,
                                        vg_http_referer_ip,
                                        vg_deposito_em_saldo_valor,
                                        vg_valor_eppcash,
                                        vg_deposito_em_saldo
                                    )
                                    VALUES (
                                        $1, $2, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, $3, $4, $5, $6, $7, $8, $9, $10, $11
                                    )
                                ";
                        // executar de forma segura
                        $ret = SQLexecuteQueryParams($sql, array(
                                $param_vg_id,                       // $1
                                $param_vg_ug_id,                    // $2
                                $param_vg_pagto_tipo,               // $3
                                $param_vg_ultimo_status,            // $4
                                $param_vg_ultimo_status_obs,        // $5
                                $param_vg_http_referer_origem,      // $6
                                $param_vg_http_referer,             // $7
                                $param_vg_http_referer_ip,          // $8
                                $param_vg_deposito_em_saldo_valor,  // $9
                                $param_vg_valor_eppcash,            // $10
                                $param_vg_deposito_em_saldo         // $11
                        ));

                        if (!$ret) {
                                $msg = "Erro ao inserir venda. Por favor, tente novamente atualizando a página. Obrigado *.\n";
                                gravaLog_BoletoExpressMoney($msg . "\n" . $sql);
                                
                        }
                } //end if(!$msg)

                if (!$msg) {
                        // Salva venda_id em tb_pag_compras
                        $sql = "UPDATE tb_pag_compras SET idvenda = $1 WHERE numcompra = $2";

                        $ret = SQLexecuteQueryParams($sql, array(
                                $venda_id,   // $1
                                $numOrder    // $2
                        ));
                        if (!$ret) {
                                $msg = "Erro ao atualizar transação de pagamento (2a, id_venda=$venda_id, numcompra='" . $numOrder . "').\n";
                                gravaLog_BoletoExpressMoney($msg . "\n" . $sql);
                        }
                } //end if(!$msg)
        } //end if($msg == "")

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
        } //end if($msg == "")

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
        } //end else do if($msg != "")
} //end if(isset($controller->logado) && $controller->logado) 

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

                $sql = "
                            INSERT INTO tb_venda_games (
                                vg_id,
                                vg_ug_id,
                                vg_data_inclusao,
                                vg_pagto_tipo,
                                vg_ultimo_status,
                                vg_ultimo_status_obs,
                                vg_http_referer_origem,
                                vg_http_referer,
                                vg_deposito_em_saldo_valor,
                                vg_valor_eppcash,
                                vg_deposito_em_saldo
                            ) VALUES (
                                $1, $2, CURRENT_TIMESTAMP, $3, $4, $5, $6, $7, $8, $9, $10
                            )
                        ";

                $ret = SQLexecuteQueryParams($sql, array(
                        $venda_id,                              // $1
                        $usuarioId,                             // $2
                        $GLOBALS['FORMAS_PAGAMENTO']['BOLETO_BANCARIO'], // $3
                        $GLOBALS['STATUS_VENDA']['PEDIDO_EFETUADO'],     // $4
                        '',                                     // $5 (obs)
                        $GLOBALS['_SESSION']['epp_origem'],     // $6
                        $GLOBALS['_SESSION']['epp_origem_referer'], // $7
                        number_format($total_geral, 2, '.', ''), // $8
                        $total_geral_epp,                       // $9
                        1                                       // $10
                ));
                if (!$ret) {
                        $msg = "Erro ao inserir venda. Por favor, tente novamente atualizando a página. Obrigado.\n";
                        gravaLog_BoletoExpressLH($msg . "\n" . $sql);
                }
        } //end if($msg == "")

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
                        } //end if($controller->usuario->b_Is_Boleto_Bradesco())
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
                $sql = "
                            INSERT INTO boleto_bancario_games (
    bbg_ug_id,
    bbg_vg_id,
    bbg_data_inclusao,
    bbg_valor,
    bbg_valor_taxa,
    bbg_bco_codigo,
    bbg_documento,
    bbg_data_venc
) VALUES (
    $1, 
    $2, 
    CURRENT_TIMESTAMP, 
    $3, 
    $4, 
    $5, 
    $6, 
    CURRENT_DATE + ($qtde_dias_venc * interval '1 day')
)
                        ";

                $ret = SQLexecuteQueryParams($sql, array(
                        $usuarioId,                              // $1 bbg_ug_id
                        $venda_id,                               // $2 bbg_vg_id
                        $total_geral + $taxa_adicional,          // $3 bbg_valor
                        $taxa_adicional,                         // $4 bbg_valor_taxa
                        $bco_codigo,                             // $5 bbg_bco_codigo
                        $num_doc,                                // $6 bbg_documento
                ));

                //atualiza dados do pagamento e status da venda
                if ($ret) {
                        $sql = "
                                    UPDATE tb_venda_games SET
                                        vg_pagto_data_inclusao = CURRENT_TIMESTAMP,
                                        vg_pagto_banco = $1,
                                        vg_pagto_num_docto = $2,
                                        vg_ultimo_status = $3
                                    WHERE vg_id = $4
                                ";

                        // executar de forma segura
                        $ret = SQLexecuteQueryParams($sql, array(
                                $bco_codigo,                                      // $1 vg_pagto_banco
                                $num_doc,                                         // $2 vg_pagto_num_docto
                                $GLOBALS['STATUS_VENDA']['PEDIDO_EFETUADO'],      // $3 vg_ultimo_status
                                $venda_id                                         // $4 vg_id
                        ));

                        $pagto = $_SESSION['pagamento.pagto'];
                        $_SESSION['pagamento.pagto.deposito.em.saldo'] = $pagto;
                        $_SESSION['pagamento.pagto.deposito.em.saldo.num.docto'] = $num_doc;
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
} //end function depositoBoleto 
