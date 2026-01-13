<?php require_once __DIR__ . '/../../../includes/constantes_url.php'; ?>
<?php
require_once "../../../includes/constantes.php";
require_once DIR_INCS . "main.php";
require_once DIR_INCS . "gamer/main.php";
require_once DIR_INCS . "config.MeiosPagamentos.php";
require_once DIR_CLASS . "gamer/classIntegracao.php";
@session_start();
validaSessao();

$https = "https";

$usuarioGames = unserialize($_SESSION['usuarioGames_ser']);
$pagto = $_SESSION['pagamento.pagto'];
$b_BancoEPP = false;
$b_ProdutosIntegracao = 0;

if (get_Integracao_is_sessao_logged()) {
        $b_BancoEPP = true;
        $b_ProdutosIntegracao = 1;
        // Se publisher usa valores livres (ou seja, não aqueles dos PINs cadastrados) então grava em carrinho_val
        $b_amount_free = "0";
        $carrinho_val = null;
        if (isset($_SESSION['integracao_origem_id'])) {
                if (function_exists('getPartner_amount_free_By_ID')) {
                        $b_amount_free = getPartner_amount_free_By_ID($_SESSION['integracao_origem_id']);
                        $s_operadora_nome = getPartner_amount_free_By_ID($_SESSION['integracao_origem_id']);
                        if ($b_amount_free == "1") {
                                $carrinho_val = $_SESSION['carrinho_val'];
                        }
                }
        }
}


if ($usuarioGames->b_IsLogin_pagamento()) {

        // atualiza cesta e total
        $total_carrinho = mostraCarrinho_pag(false, 1);


        // Taxa do Banco Itau é acrescentada em inc_urls_bancoitau.php, aqui não está fucnionando
        if ($total_carrinho < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA) {
                $taxas = (
                        ($pagto == $FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) ? $BRADESCO_TRANSFERENCIA_ENTRE_CONTAS_TAXA_ADICIONAL : (
                                ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_BB_DEBITO_SUA_CONTA']) ? $BANCO_DO_BRASIL_TAXA_DE_SERVICO : (
                                        ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_BANCO_ITAU_ONLINE']) ? $BANCO_ITAU_TAXA_DE_SERVICO : (
                                                ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_VISA_CREDITO']) ? $PAGAMENTO_VISA_CREDITO_TAXA : (
                                                        ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_MASTER_CREDITO']) ? $PAGAMENTO_MASTER_CREDITO_TAXA : (
                                                                ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_VISA_DEBITO']) ? $PAGAMENTO_VISA_DEBITO_TAXA : (
                                                                        ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_MASTER_DEBITO']) ? $PAGAMENTO_MASTER_DEBITO_TAXA : (
                                                                                ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_ELO_DEBITO']) ? $PAGAMENTO_ELO_DEBITO_TAXA : (
                                                                                        ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_ELO_CREDITO']) ? $PAGAMENTO_ELO_CREDITO_TAXA : (
                                                                                                ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_DINERS_CREDITO']) ? $PAGAMENTO_DINERS_CREDITO_TAXA : (
                                                                                                        ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_DISCOVER_CREDITO']) ? $PAGAMENTO_DISCOVER_CREDITO_TAXA : (
                                                                                                                ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_PIX']) ? $PAGAMENTO_PIX_TAXA : 0
                                                                                                        )
                                                                                                )
                                                                                        )
                                                                                )
                                                                        )
                                                                )
                                                        )
                                                )
                                        )
                                )
                        )
                );
                $_SESSION['pagamento.taxa'] = getTaxaPagtoOnline($iforma, $total_carrinho);
        } //end if ($total_carrinho > $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
        else {
                $taxas = 0;
                $_SESSION['pagamento.taxa'] = 0;
        } //end else do if ($total_carrinho > $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)

        $_SESSION['pagamento.pagto'] = $pagto;
        $_SESSION['pagamento.total'] = $total_carrinho;

        $smsg = "LOG Integração finaliza_venda_int.php - " . date("Y-m-d H:i:s") . "\n  total_carrinho: $total_carrinho\n  cesta_brd: '$cesta_brd'\n  OrderId: '$OrderId'\n";
        gravaLog_TMP($smsg);

        // ==========================================================================================
        // Faz validação de vendas totais, copia de pagamento.php

        // Testa que usuário comprou no máximo 10 vezes nas últimas 24 horas
        $qtde_last_dayOK = getNVendasMoney($usuarioGames->getId());

        // Calcula o total diario para pagamentos Online Bradesco
        $total_diario = getVendasMoneyTotalDiarioOnline($usuarioGames->getId());


        if ($usuarioGames->b_IsLogin_pagamento_free()) {
                $total_diario_const = $RISCO_GAMERS_FREE_TOTAL_DIARIO;
                $pagamentos_diario_const = $RISCO_GAMERS_FREE_PAGAMENTOS_DIARIO;
        } elseif ($usuarioGames->b_IsLogin_pagamento_vip()) {
                $total_diario_const = $RISCO_GAMERS_VIP_TOTAL_DIARIO;
                $pagamentos_diario_const = $RISCO_GAMERS_VIP_PAGAMENTOS_DIARIO;
        } else {
                $total_diario_const = $RISCO_GAMERS_TOTAL_DIARIO;
                $pagamentos_diario_const = $RISCO_GAMERS_PAGAMENTOS_DIARIO;
        }

        $b_TentativasDiariasOK = ($qtde_last_dayOK <= $pagamentos_diario_const);
        $b_LimiteDiarioOK = (($total_carrinho + $total_diario) <= $total_diario_const);

        // Libera pagamento Online Banco do Brasil
        $b_libera_BancodoBrasil = $b_LimiteDiarioOK && $b_TentativasDiariasOK; // && $usuarioGames->b_IsLogin_pagamento_bancodobrasil();

        // Libera Bradesco apenas se limite diario não ultrapassado e tem até 10 compras nas últimas 24 horas	//produtos (Habbo e GPotato) 
        $b_libera_Bradesco = $b_LimiteDiarioOK && $b_TentativasDiariasOK;        //$b_IsProdutoOK && 

        // Libera pagamento Online Banco Itaú
        $b_libera_BancoItau = $b_LimiteDiarioOK && $b_TentativasDiariasOK && $usuarioGames->b_IsLogin_pagamento_bancoitau();

        // Libera Banco EPP apenas para integração
        $b_libera_BancoEPP = $b_BancoEPP;

        // Libera PIX
        $b_libera_Pix = $b_LimiteDiarioOK && $b_TentativasDiariasOK;

        $msg_bloqueia_Bradesco = (!$b_libera_Bradesco) ? ((!$b_LimiteDiarioOK) ? "&nbsp;<br>&nbsp;<br><span class='style23'>Sua compra de " . number_format($total_carrinho, 2, ',', '.') . " ultrapassa o limite diário de compras on-line.</span>" : ((!$b_TentativasDiariasOK) ? "&nbsp;<br>&nbsp;<br><span class='style23'>Número de pagamentos online (" . $qtde_last_dayOK . ") ultrapassa o limite diário.</span>" : "")) : "";

        $msg_bloqueia_BancodoBrasil = (!$b_libera_BancodoBrasil) ? ((!$b_LimiteDiarioOK) ? "&nbsp;<br>&nbsp;<br><span class='style23'>Sua compra de " . number_format($total_carrinho, 2, ',', '.') . " ultrapassa o limite diário de compras on-line.</span>" : ((!$b_TentativasDiariasOK) ? "&nbsp;<br>&nbsp;<br><span class='style23'>Número de pagamentos online (" . $qtde_last_dayOK . ") ultrapassa o limite diário.</span>" : "")) : "";

        $msg_bloqueia_BancoItau = (!$b_libera_BancoItau) ? ((!$b_LimiteDiarioOK) ? "&nbsp;<br>&nbsp;<br><span class='style23'>Sua compra de " . number_format($total_carrinho, 2, ',', '.') . " ultrapassa o limite diário de compras on-line.</span>" : ((!$b_TentativasDiariasOK) ? "&nbsp;<br>&nbsp;<br><span class='style23'>Número de pagamentos online (" . $qtde_last_dayOK . ") ultrapassa o limite diário.</span>" : "")) : "";

        $msg_bloqueia_Pix = (!$b_libera_Pix) ? ((!$b_LimiteDiarioOK) ? "&nbsp;<br>&nbsp;<br><span class='style23'>Sua compra de " . number_format($total_carrinho, 2, ',', '.') . " ultrapassa o limite diário de compras on-line.</span>" : ((!$b_TentativasDiariasOK) ? "&nbsp;<br>&nbsp;<br><span class='style23'>Número de pagamentos online (" . $qtde_last_dayOK . ") ultrapassa o limite diário.</span>" : "")) : "";

        $msg_bloqueia_BancoEPrepag = "";

        // Pega o id_usuario do login para salvar ao inserir em tb_pag_compras, deposi de ter a venda cadastrada o ID será atualizado novamente.
        $id_usuario_prev = $usuarioGames->getId();
        $cliente_nome_prev = $usuarioGames->getNome();

        // finaliza validações
        // ==========================================================================================

        $pagto_venda = $pagto;
        // tipo_cliente   character varying(2),	-- 'M' - Money, 'E' - Money Express, 'LR' - Lanhouse Pré, 'LO' - Lanhouse Pos, 
        $tipo_cliente = "M";

        if (($pagto == $FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) || ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_DEBITO'])) {
                // gera nova ordem em tb_pag_compras
                include RAIZ_DO_PROJETO . "banco/bradesco/inc_gen_order.php"; // 

                $numOrder = $OrderId;
                $cesta_descricao = montaCesta_pag();
        } elseif ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_BB_DEBITO_SUA_CONTA']) {
                // gera nova ordem em tb_pag_compras
                include RAIZ_DO_PROJETO . "banco/bancodobrasil/inc_gen_order_bbr.php"; // 
                $numOrder = $orderId;
                $cesta_descricao = montaCesta_pag();
        } elseif ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_BANCO_ITAU_ONLINE']) {

                // gera nova ordem em tb_pag_compras
                require_once RAIZ_DO_PROJETO . "banco/itau/inc_config.php";
                require_once RAIZ_DO_PROJETO . "banco/itau/inc_gen_order_bit.php"; // 
                $numOrder = $orderId;
                $pagto_venda = $PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC;        // convert to numeric value to allow storing in tb_dist_venda_games

                $cesta_descricao = montaCesta_pag();
        } elseif ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_PIN_EPREPAG']) {
                // gera nova ordem em tb_pag_compras
                include RAIZ_DO_PROJETO . "banco/epp/inc_config.php";
                $numOrder = $orderId;
                $pagto_venda = $PAGAMENTO_PIN_EPREPAG_NUMERIC;

                $cesta_descricao = montaCesta_pag();
        } elseif ($pagto == $PAGAMENTO_BANCO_EPP_ONLINE) {

                // gera nova ordem em tb_pag_compras
                include "../pag/bep/inc_config.php";
                include "../pag/bep/inc_gen_order_bep.php"; // 
                $numOrder = $orderId;
                $pagto_venda = $PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC;        // convert to numeric value to allow storing in tb_dist_venda_games

                $cesta_descricao = montaCesta_pag_bep();
        } elseif (b_IsPagtoCieloAlpha($pagto)) {

                $cesta_descricao = montaCesta_pag();

                include_once DIR_CLASS . "gamer/classLimite.php";

                // Começa Gestão de Risco CIELO
                $carrinho_tmp = $GLOBALS['_SESSION']['carrinho'];
                $params = array();
                $limite = new Limite($pagto, $usuarioGames->getId(), $total_carrinho, $carrinho_tmp, "week");
                $mensagem = "";
                if ($limite->aplicaRegrasCielo($mensagem, $params)) {
                        $b_libera_Cielo = true;
                        // gera nova ordem em tb_pag_compras
                        include RAIZ_DO_PROJETO . "banco/cielo/inc_config.php";
                        $numOrder = $orderId;
                } else {
                        $b_libera_Cielo = false;
                        gravaLog_BloqueioPagtoOnline("Pagamento Cielo Bloqueado\n    pagto: $pagto, usuarioGames->getId(): " . $usuarioGames->getId() . ", total_carrinho: $total_carrinho, qtde_last_dayOK: " . $qtde_last_dayOK . ", total_diario: " . $total_diario . "\n    " . $mensagem);
                        redirect("/prepag2/commerce/pagamento.php");
                }

                $msg_bloqueia_Cielo = (!$b_libera_Cielo) ? "&nbsp;<br>&nbsp;<br><span class='style23'>Pagamento Cielo não disponível: $mensagem.</span>" : "";

                // Termina Gestão de Risco CIELO

        } elseif ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_PIX']) {
                // gera nova ordem em tb_pag_compras
                require_once RAIZ_DO_PROJETO . "banco/pix/inc_config.php";
                $numOrder = $orderId;
                $pagto_venda = $PAGAMENTO_PIX_NUMERIC;
                $cesta_descricao = montaCesta_pag();
        }

        // Pagamento existe?
        $sql = "SELECT * FROM tb_pag_compras WHERE numCompra = $1";
        $params = array($numOrder);
        $ret = SQLexecuteQueryParams($sql, $params);

        if (!$ret) {
                echo "Erro ao recuperar transação de pagamento (1).\n";
                die("Stop");
        }

        $sql = "UPDATE tb_pag_compras SET 
            cliente_nome = $1, 
            idcliente = $2, 
            status = 1, 
            cesta = $3, 
            total = $4 
        WHERE numcompra = $5";

        $params = array(
                $usuarioGames->getNome(),             // $1
                $usuarioGames->getId(),               // $2
                $cesta_descricao ?: '',             // $3
                100 * ($total_carrinho + $taxas),     // $4
                $numOrder                             // $5
        );

        $ret = SQLexecuteQueryParams($sql, $params);

        if (!$ret) {
                echo "Erro ao atualizar transação de pagamento (2).\n";
                die("Stop");
        }
}

//Junta objetos de uma venda
//----------------------------------------------------------------------------------------------------------------------------
$strRedirect = "";

// Carrinho
//----------------------------------------------------
//Recupera carrinho do session
$carrinho = $_SESSION['carrinho'];

//Valida se existe carrinho
if ($strRedirect == "") {
        if (!$carrinho || count($carrinho) == 0) {
                $strRedirect = "/prepag2/commerce/carrinho.php";
        }
}

//Valida produtos
if ($strRedirect == "") {

        //Remove produtos invalidos
        foreach ($carrinho as $modeloId => $qtde) {

                $qtde = intval($qtde);
                //Se qtde do modelo invalida, remove modelo
                //if($qtde <= 0) $carrinho[$modeloId] = null;
                if ($qtde <= 0)
                        unset($carrinho[$modeloId]);
        }

        //Se nao restou produto, retorna para o carrinho
        if (!$carrinho || count($carrinho) == 0) {
                $strRedirect = "/prepag2/commerce/carrinho.php";
        }
}

//Pagamento
//----------------------------------------------------
//Recupera tipo do pagamento do session
$pagto = $_SESSION['pagamento.pagto'];
$pagto_ja_fiz = $_SESSION['pagamento.pagto_ja_fiz'];
//		$parcelas_REDECARD_MASTERCARD = $_SESSION['pagamento.parcelas.REDECARD_MASTERCARD'];
//		$parcelas_REDECARD_DINERS = $_SESSION['pagamento.parcelas.REDECARD_DINERS'];
$parcelas_REDECARD_MASTERCARD = 1;
$parcelas_REDECARD_DINERS = 1;

//Valida se existe pagamento
if ($strRedirect == "") {
        if (
                !$pagto ||
                !(
                        is_numeric($pagto)
                        || ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_BANCO_ITAU_ONLINE'])
                        || ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_PIN_EPREPAG'])
                        || ($pagto == $PAGAMENTO_BANCO_EPP_ONLINE)

                        || ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_VISA_DEBITO'])
                        || ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_VISA_CREDITO'])
                        || ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_MASTER_DEBITO'])
                        || ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_MASTER_CREDITO'])
                        || ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_ELO_DEBITO'])
                        || ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_ELO_CREDITO'])
                        || ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_DINERS_CREDITO'])
                        || ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_DISCOVER_CREDITO'])

                        || ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_PIX'])
                )
        ) {
                $strRedirect = "/prepag2/commerce/pagamento_int.php";
        }
}

//Valida formas de pagamento
if ($strRedirect == "") {

        if ($b_BancoEPP) {
                if (!in_array($pagto, $FORMAS_PAGAMENTO) && ($pagto != $PAGAMENTO_BANCO_EPP_ONLINE)) {
                        $strRedirect = "/prepag2/commerce/pagamento_int.php";
                }
        } else {
                if (!in_array($pagto, $FORMAS_PAGAMENTO)) {
                        $strRedirect = "/prepag2/commerce/pagamento_int.php";
                }
        }
}

//Valida identificação de parceiro
if ($strRedirect == "") {
        if (strlen($_SESSION['integracao_origem_id']) == 0) {
                $strRedirect = "/prepag2/commerce/pagamento_int.php";
        }
}

if (!$b_libera_Bradesco && (($pagto == $FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) || ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_DEBITO']))) {
?>
        <script language="Javascript">
                alert("Erro: <?php echo $msg_bloqueia_Bradesco; ?>");
        </script>
        <?php

        $strRedirect = "/prepag2/commerce/carrinho.php";
} else if (!$b_libera_BancodoBrasil && ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_BB_DEBITO_SUA_CONTA'])) {
        if ($usuarioGames->b_IsLogin_pagamento_bancodobrasil()) {
        ?>
                <script language="Javascript">
                        alert("Erro: <?php echo $msg_bloqueia_BancodoBrasil; ?>");
                </script>
        <?php
                $strRedirect = "/prepag2/commerce/carrinho.php";
        }
} else if (!$b_libera_BancoItau && ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_BANCO_ITAU_ONLINE'])) {
        if ($usuarioGames->b_IsLogin_pagamento_bancoitau()) {
        ?>
                <script language="Javascript">
                        alert("Erro: <?php echo $msg_bloqueia_BancoItau; ?>");
                </script>
        <?php
                $strRedirect = "/prepag2/commerce/carrinho.php";
        }
} else if (!$b_libera_BancoEPP && ($pagto == $PAGAMENTO_BANCO_EPP_ONLINE)) {
        if ($usuarioGames->b_IsLogin_pagamento_bancoepp()) {
        ?>
                <script language="Javascript">
                        alert("Erro: <?php echo $msg_bloqueia_BancoEPrepag; ?>");
                </script>
        <?php
                $strRedirect = "/prepag2/commerce/carrinho.php";
        }
} else if (!$b_libera_Pix && ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_PIX'])) {
        ?>
        <script language="Javascript">
                alert("Erro: <?php echo $msg_bloqueia_Pix; ?>");
        </script>
<?php
        $strRedirect = "/prepag2/commerce/carrinho.php";
}


//Redireciona se ha algum dado invalido
//----------------------------------------------------
if ($strRedirect != "") {
        redirect($strRedirect);
}


//Cria venda
//----------------------------------------------------------------------------------------------------------------------------
$ret = "";

//Recupera o usuario do session
$usuarioGames = unserialize($_SESSION['usuarioGames_ser']);

//Insere na tabela de venda
if ($ret == "") {

        $venda_id = obterIdVendaValido();
        $_SESSION['integracao_transaction_id'] = $venda_id;

        $sql = "INSERT INTO tb_venda_games (
            vg_id, vg_ug_id, vg_data_inclusao, vg_pagto_tipo,
            vg_ultimo_status, vg_ultimo_status_obs, vg_integracao_parceiro_origem_id,
            vg_http_referer_origem, vg_http_referer
        ) VALUES (
            $1, $2, CURRENT_TIMESTAMP, $3,
            $4, $5, $6,
            $7, $8
        )";

        $params = array(
                $venda_id,                                    // $1
                $usuarioGames->getId(),                        // $2
                $pagto_venda,                                  // $3
                $STATUS_VENDA['PEDIDO_EFETUADO'],             // $4
                "",                                            // $5, vg_ultimo_status_obs
                $_SESSION['integracao_origem_id'] ?: '',    // $6
                $_SESSION['epp_origem'] ?: '',             // $7
                $_SESSION['epp_origem_referer'] ?: ''      // $8
        );

        // Grava log antes de executar
        grava_log_integracao("Venda Integração - Cadastra venda " . date("Y-m-d H:i:s") . "\nvg_id: '$venda_id' - integracao_origem_id: '" . $_SESSION['integracao_origem_id'] . "' - integracao_order_id: '" . $_SESSION['integracao_order_id'] . "'\n" . $sql . "\n");        //"s_session :'".$s_session."'\n"

        $ret = SQLexecuteQueryParams($sql, $params);

        if (!$ret)
                $ret = "Erro ao inserir venda.\n";
        else {
                $ret = ""; //limpa resourceId
        }

        if (strlen($numOrder) == 0)
                $numOrder = $OrderId;


        if ($usuarioGames->b_IsLogin_pagamento()) {
                // atualiza $venda_id
                $sql = "UPDATE tb_pag_compras 
                                SET idvenda = $1 
                                WHERE numcompra = $2";

                $params = array(
                        $venda_id,  // $1
                        $numOrder   // $2
                );

                $ret1 = SQLexecuteQueryParams($sql, $params);

                if (!$ret1) {
                        echo "Erro ao atualizar transação de pagamento (3).\n";
                        gravaLog_TMP("Erro ao atualizar transação de pagamento (3).\n" . $sql . "\n");
                }
                if (b_IsPagtoCielo($pagto_venda)) {
                        //Inicio do novo trecho para cadastramento do token no BD
                        $softDescriptor = $GLOBALS['_SESSION']['pagamento.token'];
                        $sql = "INSERT INTO codigo_confirmacao (
                                    cc_tipo_usuario,
                                    cc_ug_id,
                                    cc_vg_id,
                                    cc_tipo_pagamento,
                                    cc_data_inclusao,
                                    cc_codigo,
                                    cc_status
                                ) VALUES (
                                    $1, $2, $3, $4, NOW(), $5, $6
                                )";

                        $params = array(
                                'M',                                         // $1 cc_tipo_usuario
                                $usuarioGames->getId(),                      // $2 cc_ug_id
                                $venda_id,                                   // $3 cc_vg_id
                                getCodigoCaracterParaPagto($pagto_venda),   // $4 cc_tipo_pagamento
                                $softDescriptor,                             // $5 cc_codigo
                                '0'                                          // $6 cc_status
                        );

                        $rs_token = SQLexecuteQueryParams($sql, $params);

                        if (!$rs_token) {
                                die("Problema ao salvar o registro do Token.");
                        }
                } //end if(b_IsPagtoCielo($pagto_tipo))
        }

        if (get_Integracao_is_sessao_logged()) {
                // Obtem o ip_order_id do SESSION
                $ip_order_id = get_Integracao_order_id_is_sessao_logged();

                if ($ip_order_id != "") {
                        // atualiza $venda_id
                        $pdo = ConnectionPDO::getConnection()->getLink();
                        $sql = "UPDATE tb_integracao_pedido 
                                        SET ip_vg_id = :vg_id, 
                                            ip_transaction_id = :transaction_id
                                        WHERE ip_order_id = :order_id
                                          AND ip_store_id = :store_id
                                          AND ip_amount = :amount";

                        $stmt = $pdo->prepare($sql);

                        grava_log_integracao("Venda - Integração: UPDATE tb_integracao_pedido SET ip_vg_id=$venda_id, ip_transaction_id=$venda_id WHERE ip_order_id=$ip_order_id AND ip_store_id=" . $_SESSION['integracao_origem_id'] . " AND ip_amount=" . ($total_carrinho * 100) . "\n");

                        $ret1 = $stmt->execute([
                                ':vg_id' => $venda_id,
                                ':transaction_id' => $venda_id,
                                ':order_id' => $ip_order_id,
                                ':store_id' => $_SESSION['integracao_origem_id'],
                                ':amount' => $total_carrinho * 100
                        ]);

                        if (!$ret1) {
                                echo "Erro ao atualizar transação de integração (3).\n";
                                gravaLog_TMP("Erro ao atualizar transação de integração (3).\n" . $sql . "\n");
                        }
                } else {
                        gravaLog_TMP("Erro ao atualizar transação de integração (4): sem ip_order_id no SESSION.\n");
                }
        }
}

//Insere os modelos na tabela de venda modelos
//Este eh para guardar dados do modelo, valor e qtde do momento da venda
if ($ret == "") {
        // Se publisher usa valores livres (ouy seja, não aqueles dos PINs cadastrados) então grava em carrinho_val
        $b_amount_free = getPartner_amount_free_By_ID($_SESSION['integracao_origem_id']);
        if ($b_amount_free == "1") {
                $carrinho_val = $_SESSION["carrinho_val"];
        }

        foreach ($carrinho as $modeloId => $qtde) {

                $svalor = null;
                $svalorEPPCASH = null;
                if ($b_amount_free == "1") {
                        // o valor está em carrinho aparte
                        $svalor = $carrinho_val[$modeloId] / 100.;
                        /******************** Resolver problema de valores EPP CASH para compras com valores livre *****************************/
                        // o valor copiado para resolver ´problema de integração com publishers de valores livres
                        $svalorEPPCASH = $carrinho_val[$modeloId];
                }

                $ret = $ret1;

                // Buscar dados do modelo e produto
                $sql_select = "
                                    SELECT 
                                        ogp.ogp_id,
                                        ogp.ogp_nome,
                                        ogpm.ogpm_id,
                                        ogpm.ogpm_nome,
                                        ogp.ogp_opr_codigo,
                                        ogpm.ogpm_pin_valor,
                                        ogpm.ogpm_valor_eppcash
                                    FROM tb_operadora_games_produto_modelo ogpm
                                    INNER JOIN tb_operadora_games_produto ogp ON ogp.ogp_id = ogpm.ogpm_ogp_id
                                    WHERE ogpm.ogpm_id = $1
                                ";
                $params_select = [$modeloId];

                $rs_modelo = SQLexecuteQueryParams($sql_select, $params_select);
                $modelo = pg_fetch_assoc($rs_modelo);

                if ($modelo) {
                        // Agora insere usando apenas parâmetros ? sem misturar colunas SQL
                        $sql_insert = "
                                    INSERT INTO tb_venda_games_modelo (
                                        vgm_vg_id, vgm_ogp_id, vgm_nome_produto, vgm_ogpm_id, vgm_nome_modelo,
                                        vgm_valor, vgm_qtde, vgm_opr_codigo, vgm_pin_valor,
                                        vgm_game_id, vgm_valor_eppcash
                                    )
                                    VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11)
                                ";

                        $params_insert = [
                                $venda_id,                            // $1
                                $modelo['ogp_id'],                    // $2
                                $modelo['ogp_nome'],                  // $3
                                $modelo['ogpm_id'],                   // $4
                                $modelo['ogpm_nome'],                 // $5
                                (isset($svalor) ? $svalor : $modelo['ogpm_pin_valor']),                              // $6
                                $qtde,                                // $7
                                $modelo['ogp_opr_codigo'],            // $8
                                $modelo['ogpm_pin_valor'],            // $9
                                ($modelo['ogp_id'] == 5 ? $usuarioGames->getHabboId() : null), // $10
                                (isset($svalorEPPCASH) ? $svalorEPPCASH : $modelo['ogpm_valor_eppcash'])                       // $11
                        ];

                        $ret = SQLexecuteQueryParams($sql_insert, $params_insert);
                }

                if ($ret)
                        $ret = ""; //limpa resourceId
                else {

                        //Se deu erro ao inserir um modelo, deleta toda a venda
                        // Deleta modelos da venda
                        $sql = "DELETE FROM tb_venda_games_modelo WHERE vgm_vg_id = $1";
                        SQLexecuteQueryParams($sql, [$venda_id]);

                        // Deleta a venda
                        $sql = "DELETE FROM tb_venda_games WHERE vg_id = $1";
                        SQLexecuteQueryParams($sql, [$venda_id]);

                        // Deleta histórico da venda
                        $sql = "DELETE FROM tb_venda_games_historico WHERE vgh_vg_id = $1";
                        SQLexecuteQueryParams($sql, [$venda_id]);


                        $ret = "Erro ao inserir modelo(s) na venda.\n";
                        if ($_SERVER['REMOTE_ADDR'] == '177.37.138.175') {
                                exit($sql_insert . "\n" . print_r($params_insert, true) . "\n");
                        }
                        break;
                }
        }
}

//Redireciona se ha algum dado invalido
//----------------------------------------------------
if ($ret != "") {
        $strRedirect = "/prepag2/commerce/mensagem.php?msg=" . urlencode($ret) . "&pt=" . urlencode("Erro") . "&link=" . urlencode("/prepag2/commerce/carrinho.php");
        redirect($strRedirect);
}


//Registra venda
//----------------------------------------------------------------------------------------------------------------------------
$ret = "";

//Limpa objetos de uma venda
//----------------------------------------------------
unset($_SESSION['carrinho']);
unset($_SESSION['pagamento.pagto']);
unset($_SESSION['pagamento.pagto_ja_fiz']);
unset($_SESSION['pagamento.parcelas.REDECARD_MASTERCARD']);
unset($_SESSION['pagamento.parcelas.REDECARD_DINERS']);

//Log na base
//----------------------------------------------------
usuarios_games_log($GLOBALS['USUARIO_GAMES_LOG_TIPOS']['VENDA'], null, $venda_id);


if (true) {        // Começa aqui o email antigo 
        //Envia email
        //--------------------------------------------------------------------------------
        $sql = "SELECT * 
                    FROM tb_venda_games vg
                    INNER JOIN tb_venda_games_modelo vgm ON vgm.vgm_vg_id = vg.vg_id
                    WHERE vg.vg_id = $1 AND vg.vg_ug_id = $2
                ";
        $rs_venda_modelos = SQLexecuteQueryParams($sql, [$venda_id, $usuarioGames->getId()]);


        $parametros['prepag_dominio'] = $https . "://" . EPREPAG_URL . "";

        /* ---Wagner - variavel $aux_lista */
        $aux_lista = "<table cellspacing='0' cellpadding='5' width='100%' style='font: normal 13px arial, sans-serif;'>
                        <tr bgcolor='#CCCCCC'>
                                <td width='3'>&nbsp;</td>
                                <td align='left'><b>Jogo</b></td>
                                <td align='center'><b>Produto</b></td>
                                <td align='center'><b>Unit.&nbsp;(R$)</b></td>
                                <td align='center'><b>Qtde</b></td>
                                <td align='right'><b>Total&nbsp;(R$)</b></td>
                                <td width='5'>&nbsp;</td>
                        </tr>";
        /* ---Wagner - variavel $aux_lista */

        $qtde_total = 0;
        $total_geral = 0;
        while ($rs_venda_modelos_row = pg_fetch_array($rs_venda_modelos)) {

                $pagto_tipo = $rs_venda_modelos_row['vg_pagto_tipo'];

                $codigo = $rs_venda_modelos_row['vgm_id'];
                $qtde = $rs_venda_modelos_row['vgm_qtde'];
                $valor = $rs_venda_modelos_row['vgm_valor'];
                $qtde_total += $qtde;
                $total_geral += $valor * $qtde;

                /* ---Wagner - variavel $aux_lista */
                $aux_lista .= "<tr bgcolor='#E6E6E6'>
                                <td width='3'>&nbsp;</td>
                                <td align='left'><nobr>" . str_replace(" ", "&nbsp;", $rs_venda_modelos_row['vgm_nome_produto']) . "</nobr></td>
                                <td align='center'>" . $rs_venda_modelos_row['vgm_nome_modelo'] . "</td>
                                <td align='center'>" . number_format($valor, 2, ',', '.') . "</td>
                                <td align='center'><b>" . $qtde . "</b></td>
                                <td align='right'><b>" . number_format($valor * $qtde, 2, ',', '.') . "</b></td>
                                <td width='5'>&nbsp;</td>
                        </tr>";
                /* ---Fim Wagner - variavel $aux_lista */
        }
        /* ---Wagner - variavel $aux_lista */
        $aux_lista .= "<tr  bgcolor='#CCCCCC'>
                        <td colspan='3'>&nbsp;</td>
                        <td align='right' colspan='2'><b><nobr>Total&nbsp;Geral&nbsp;(R$)</nobr></b></td>
                        <td align='right'><b>" . number_format($total_geral, 2, ',', '.') . "</b></td>
                        <td width='5'>&nbsp;</td>
                </tr>
                </table>";
        /* ---Fim Wagner - variavel $aux_lista */
} // Até aqui o email antigo 


/* ---Wagner Email Template */
if (is_object($usuarioGames)) {
        if ($pagto == $FORMAS_PAGAMENTO['BOLETO_BANCARIO']) {
                $GLOBALS['_SESSION']['boleto_imagem'] = 'PedidoRegistradoInt';
                $GLOBALS['_SESSION']['aux_lista_oferta'] = $aux_lista;
        } else {
                $stipoEmail = 'PedidoRegistradoInt';
                $objEnvioEmailAutomatico = new EnvioEmailAutomatico(TIPO_USUARIO_GAMER, $stipoEmail);

                $ug_id = $usuarioGames->getId();        // 9093;	//
                $ug_email = $usuarioGames->getEmail();        // "reynaldo@e-prepag.com.br";// 
                $objEnvioEmailAutomatico->setPedido($venda_id);
                $objEnvioEmailAutomatico->setListaCreditoOferta($aux_lista);
                $objEnvioEmailAutomatico->setUgID($ug_id);
                $objEnvioEmailAutomatico->setUgEmail($ug_email);
                echo $objEnvioEmailAutomatico->MontaEmailEspecifico();
        }
}
/* -- Fim Wagner */


//Boleto
//Se forma de pagamento foi boleto, cria o boleto da venda
//----------------------------------------------------------------------------------------------------------------------------
if ($pagto == $FORMAS_PAGAMENTO['BOLETO_BANCARIO']) {

        //obtem o valor total da venda
        //----------------------------------------------------
        $sql = "
                    SELECT *
                    FROM tb_venda_games vg
                    INNER JOIN tb_venda_games_modelo vgm ON vgm.vgm_vg_id = vg.vg_id
                    WHERE vg.vg_id = $1
                      AND vg.vg_ug_id = $2
                ";

        $rs_venda_modelos = SQLexecuteQueryParams($sql, [$venda_id, $usuarioGames->getId()]);

        if ($rs_venda_modelos && pg_num_rows($rs_venda_modelos) > 0) {
                $total_geral = 0;
                while ($rs_venda_modelos_row = pg_fetch_array($rs_venda_modelos)) {
                        $qtde = $rs_venda_modelos_row['vgm_qtde'];
                        $valor = $rs_venda_modelos_row['vgm_valor'];
                        $total_geral += $valor * $qtde;
                }
        }

        $num_doc = "2" . "00" . str_pad($venda_id, 8, "0", STR_PAD_LEFT);
        $qtde_dias_venc = $GLOBALS['BOLETO_MONEY_BRADESCO_QTDE_DIAS_VENCIMENTO'];

        if ($total_carrinho < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
                $taxa_adicional = $GLOBALS['BOLETO_MONEY_BRADESCO_TAXA_ADICIONAL'];
        else
                $taxa_adicional = 0;

        if (BANCO_BOLETO == "asaas") {
                $bco_codigo = $GLOBALS['BOLETO_MONEY_ASAAS_COD_BANCO'];
        } elseif (BANCO_BOLETO == "bradesco") {
                $bco_codigo = $GLOBALS['BOLETO_MONEY_BRADESCO_COD_BANCO'];
        }
        // Usa Boleto Itau para alguns usuários
        if ($usuarioGames) {
                if (BANCO_BOLETO == "asaas") {
                        if ($total_carrinho < $GLOBALS['RISCO_GAMERS_VALOR_MIN_PARA_TAXA'])
                                $taxa_adicional = $GLOBALS['BOLETO_MONEY_BRADESCO_TAXA_ADICIONAL'];
                        else
                                $taxa_adicional = 0;
                        $qtde_dias_venc = $GLOBALS['BOLETO_MONEY_BRADESCO_QTDE_DIAS_VENCIMENTO'];
                        $bco_codigo = $GLOBALS['BOLETO_MONEY_ASAAS_COD_BANCO'];
                        $num_doc = "2" . "00" . str_pad($venda_id, 8, "0", STR_PAD_LEFT);
                } elseif (BANCO_BOLETO == "bradesco") {
                        if ($total_carrinho < $GLOBALS['RISCO_GAMERS_VALOR_MIN_PARA_TAXA'])
                                $taxa_adicional = $GLOBALS['BOLETO_MONEY_BRADESCO_TAXA_ADICIONAL'];
                        else
                                $taxa_adicional = 0;
                        $qtde_dias_venc = $GLOBALS['BOLETO_MONEY_BRADESCO_QTDE_DIAS_VENCIMENTO'];
                        $bco_codigo = $GLOBALS['BOLETO_MONEY_BRADESCO_COD_BANCO'];
                        $num_doc = "2" . "00" . str_pad($venda_id, 8, "0", STR_PAD_LEFT);
                } //end if($usuarioGames->b_Is_Boleto_Bradesco())
                elseif ($usuarioGames->b_Is_Boleto_Banespa()) {
                        $qtde_dias_venc = $GLOBALS['BOLETO_MONEY_BANESPA_QTDE_DIAS_VENCIMENTO'];
                        $bco_codigo = $GLOBALS['BOLETO_MONEY_BANCO_BANESPA_COD_BANCO'];
                        if ($total_carrinho < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
                                $taxa_adicional = $GLOBALS['BOLETO_MONEY_BANESPA_TAXA_ADICIONAL'];
                        else
                                $taxa_adicional = 0;
                        $num_doc = "2" . "000" . str_pad($venda_id, 8, "0", STR_PAD_LEFT);
                } elseif ($usuarioGames->b_Is_Boleto_Itau()) {
                        $qtde_dias_venc = $GLOBALS['BOLETO_MONEY_ITAU_QTDE_DIAS_VENCIMENTO'];
                        $bco_codigo = $GLOBALS['BOLETO_MONEY_ITAU_COD_BANCO'];
                        if ($total_carrinho < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
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
                        $1, $2, CURRENT_TIMESTAMP, $3, $4, $5, $6, CURRENT_DATE + interval '$qtde_dias_venc day'
                    )
                ";

        $params = [
                $usuarioGames->getId(),       // $1
                $venda_id,                    // $2
                $total_geral + $taxa_adicional, // $3
                $taxa_adicional,              // $4
                $bco_codigo,                  // $5
                $num_doc                     // $6
        ];

        $ret = SQLexecuteQueryParams($sql, $params);


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

                $params = [
                        $bco_codigo,                                    // $1
                        $num_doc,                                       // $2
                        $GLOBALS['STATUS_VENDA']['DADOS_PAGTO_RECEBIDO'], // $3
                        $venda_id                                       // $4
                ];

                $ret = SQLexecuteQueryParams($sql, $params);

                if (!$ret)
                        $ret = "Erro ao atualizar status da venda.\n";
                else
                        $ret = ""; //limpa resourceId
        }
}

//Redecard
//Se forma de pagamento foi cartoes Redecard, cria o item Redecard
//----------------------------------------------------------------------------------------------------------------------------
if ($pagto === $FORMAS_PAGAMENTO['REDECARD_MASTERCARD'] || $pagto === $FORMAS_PAGAMENTO['REDECARD_DINERS']) {

        // Define o número de parcelas conforme o tipo de pagamento
        $param_parcelas = ($pagto === $FORMAS_PAGAMENTO['REDECARD_MASTERCARD'])
                ? $parcelas_REDECARD_MASTERCARD
                : $parcelas_REDECARD_DINERS;

        $sql = "
                    INSERT INTO tb_venda_games_redecard (
                        vgrc_ug_id,
                        vgrc_vg_id,
                        vgrc_data_inclusao,
                        vgrc_parcelas
                    ) VALUES (
                        $1, $2, CURRENT_TIMESTAMP, $3
                    )
                ";

        $params = [
                $usuarioGames->getId(), // $1
                $venda_id,              // $2
                $param_parcelas         // $3
        ];

        $ret = SQLexecuteQueryParams($sql, $params);
}




//Comprovante
//----------------------------------------------------------------------------------------------------------------------------

//Se pagto_ja_fiz redireciona para a pagina de entrada dos dados de pagamento
if ($pagto == $FORMAS_PAGAMENTO['DEP_DOC_TRANSF'] && $pagto_ja_fiz && $pagto_ja_fiz == "1") {
        $strRedirect = "/prepag2/commerce/conta/pagto_informa_dep_doc_transf.php?venda=" . $venda_id;

        //Redecard - inicia processo de pagamento
} elseif ($pagto == $FORMAS_PAGAMENTO['REDECARD_MASTERCARD'] || $pagto == $FORMAS_PAGAMENTO['REDECARD_DINERS']) {
        $strRedirect = "/prepag2/commerce/redecard/rc_envio.php?venda=" . $venda_id;

        //Comprovante
} else {
        //redireciona para o redirecionador de comprovante
        $strRedirect = "/prepag2/commerce/conta/pagto_compr_redirect.php?venda=" . $venda_id;
}

// Desmarca a Origem de Integração
//	set_Integracao_marca_sessao_logout();

//Redireciona
//----------------------------------------------------

//Fechando Conexão
pg_close($connid);

redirect($strRedirect);
?>
