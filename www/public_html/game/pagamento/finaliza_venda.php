<link href="/includes/bootstrap/css/bootstrap.min_new.css" rel="stylesheet" type="text/css" />
<link href="/css/creditos.css" rel="stylesheet" type="text/css" />

<script type="text/javascript" src="/js/jquery.js"></script>
<script type="text/javascript" src="/includes/bootstrap/js/bootstrap.min.js"></script>
<script type="text/javascript" src="/js/modalwaitingfor.js"></script>
<script>
        $(function () {
                waitingDialog.show('Por favor aguarde, estamos validando seus dados...', { dialogSize: 'md' });
        });
</script>
<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
require_once "../../../includes/constantes.php";
require_once DIR_CLASS . 'gamer/controller/HeaderController.class.php';
$controller = new HeaderController;
require_once DIR_INCS . "config.MeiosPagamentos.php";
require_once DIR_CLASS . "gamer/classIntegracao.php";
require_once DIR_CLASS . "gamer/classLimite.php";

$https = 'http' . (($_SERVER['HTTPS'] == 'on') ? 's' : '');

$pagto = $_SESSION['pagamento.pagto'];
$pagto_ja_fiz = $_SESSION['pagamento.pagto_ja_fiz'];

if ($pagto == $FORMAS_PAGAMENTO['DEP_DOC_TRANSF'] && empty($pagto_ja_fiz)) {
        redirect("/game/pedido/deposito.php");
        unset($_SESSION['pagamento.pagto_ja_fiz']);
        die();
}//end if($pagto == $FORMAS_PAGAMENTO['DEP_DOC_TRANSF'] && empty($pagto_ja_fiz))

if (!$prod_camp) {
        $prod_camp = $_SESSION['campeonato.prod_id'];

}
$carrinho_backup = "";
if (isset($prod_camp) && $prod_camp > 0) {
        $carrinho_backup = $carrinho;
        $carrinho = array();
        $carrinho[$prod_camp] = 1;
}

if ($controller->usuario->b_IsLogin_pagamento()) {

        // atualiza cesta e total
        if (isset($prod_camp) && $prod_camp > 0) {
                $total_carrinho = mostraCarrinho_pag(false, 0);
                $total_carrinho = ($total_carrinho < 0) ? $total_carrinho * (-1) : $total_carrinho;
                gravaLog_EPPCASH_PINs("Vai para mostraCarrinho_pag(false, 0)\n  total_carrinho: '$total_carrinho'");
        } else {
                $total_carrinho = mostraCarrinho_pag(false, 1);
                $total_carrinho = ($total_carrinho < 0) ? $total_carrinho * (-1) : $total_carrinho;
                gravaLog_EPPCASH_PINs("Vai para mostraCarrinho_pag(false, 1)\n  total_carrinho: '$total_carrinho'");
        }


        // Para uso em testes PayPal/Hipay
        $total_carrinho_nominal = $GLOBALS['_SESSION']['carrinho_total_geral_treinamento'];

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
        }//end if ($total_carrinho > $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
        else {
                $taxas = 0;
                $_SESSION['pagamento.taxa'] = 0;
        }//end else do if ($total_carrinho > $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)

        // ==========================================================================================
        // Faz valida��o de vendas totais, copia de pagamento.php

        // Testa que usu�rio comprou no m�ximo 10 vezes nas �ltimas 24 horas
        $qtde_last_dayOK = getNVendasMoney($controller->usuario->getId());

        // Calcula o total diario para pagamentos Online Bradesco
        $total_diario = getVendasMoneyTotalDiarioOnline($controller->usuario->getId());

        //	Gamers FREE-Integra��o Pagamento Online = no max R$450,00 por d�a por usu�rio (ver getVendasMoneyTotalDiarioOnline()) em at� 100 vezes
        if ($controller->usuario->b_IsLogin_pagamento_free()) {
                $total_diario_const = $RISCO_GAMERS_FREE_TOTAL_DIARIO;
                $pagamentos_diario_const = $RISCO_GAMERS_FREE_PAGAMENTOS_DIARIO;
                //	Gamers VIP- Pagamento Online = no max R$1000,00 por d�a por usu�rio (ver getVendasMoneyTotalDiarioOnline()) em at� 20 vezes
        } elseif ($controller->usuario->b_IsLogin_pagamento_vip()) {
                $total_diario_const = $RISCO_GAMERS_VIP_TOTAL_DIARIO;
                $pagamentos_diario_const = $RISCO_GAMERS_VIP_PAGAMENTOS_DIARIO;
                //	Gamers - Pagamento Online = no max R$450,00 por d�a por usu�rio (ver getVendasMoneyTotalDiarioOnline()) em at� 10 vezes
        } else {
                $total_diario_const = $RISCO_GAMERS_TOTAL_DIARIO;
                $pagamentos_diario_const = $RISCO_GAMERS_PAGAMENTOS_DIARIO;
        }


        $b_TentativasDiariasOK = ($qtde_last_dayOK <= $pagamentos_diario_const);
        $b_LimiteDiarioOK = (($total_carrinho + $total_diario) <= $total_diario_const);
        $b_ValorBoletoOK = ($total_carrinho <= $RISCO_GAMERS_BOLETOS_TOTAL_DIARIO);
        $b_ValorDepositoOK = ($total_carrinho <= $RISCO_GAMERS_DEPOSITOS_TOTAL_DIARIO);

        // Libera pagamento Online Banco do Brasil
        $b_libera_BancodoBrasil = $b_LimiteDiarioOK && $b_TentativasDiariasOK;// && $controller->usuario->b_IsLogin_pagamento_bancodobrasil();

        // Libera Bradesco apenas se limite diario n�o ultrapassado e tem at� 10 compras nas �ltimas 24 horas	//produtos (Habbo e GPotato) 
        $b_libera_Bradesco = $b_LimiteDiarioOK && $b_TentativasDiariasOK;	//$b_IsProdutoOK && 

        // Libera pagamento Online Banco Ita�
        $b_libera_BancoItau = $b_LimiteDiarioOK && $b_TentativasDiariasOK && $controller->usuario->b_IsLogin_pagamento_bancoitau();

        // Libera pagamento Online Hipay
        $b_libera_Hipay = $b_LimiteDiarioOK && $b_TentativasDiariasOK && $controller->usuario->b_IsLogin_pagamento_hipay();

        // Libera pagamento Online Paypal
        $b_libera_Paypal = $b_LimiteDiarioOK && $b_TentativasDiariasOK && $controller->usuario->b_IsLogin_pagamento_paypal();

        // Libera Boleto apenas se o valor da venda n�o ultrapassa o limite por venda
        $b_libera_Boleto = $b_ValorBoletoOK;

        // Libera Dep�sito apenas se o valor da venda n�o ultrapassa o limite por venda
        $b_libera_Deposito = $b_ValorDepositoOK;

        // Libera PIX
        $b_libera_Pix = $b_LimiteDiarioOK && $b_TentativasDiariasOK;

        $msg_bloqueia_Bradesco = (!$b_libera_Bradesco) ? ((!$b_LimiteDiarioOK) ? "<p class='txt-azul fontsize-pp'>" . number_format($total_carrinho, 2, ',', '.') . " ultrapassa o limite di�rio de compras on-line.</p>" : ((!$b_TentativasDiariasOK) ? "<p class='txt-azul fontsize-pp'>N�mero de pagamentos online (" . $qtde_last_dayOK . ") ultrapassa o limite di�rio.</p>" : "")) : "";

        $msg_bloqueia_BancodoBrasil = (!$b_libera_BancodoBrasil) ? ((!$b_LimiteDiarioOK) ? "<p class='txt-azul fontsize-pp'>" . number_format($total_carrinho, 2, ',', '.') . " ultrapassa o limite di�rio de compras on-line.</p>" : ((!$b_TentativasDiariasOK) ? "<p class='txt-azul fontsize-pp'>N�mero de pagamentos online (" . $qtde_last_dayOK . ") ultrapassa o limite di�rio.</p>" : "")) : "";

        $msg_bloqueia_BancoItau = (!$b_libera_BancoItau) ? ((!$b_LimiteDiarioOK) ? "<p class='txt-azul fontsize-pp'>" . number_format($total_carrinho, 2, ',', '.') . " ultrapassa o limite di�rio de compras on-line.</p>" : ((!$b_TentativasDiariasOK) ? "<p class='txt-azul fontsize-pp'>N�mero de pagamentos online (" . $qtde_last_dayOK . ") ultrapassa o limite di�rio.</p>" : "")) : "";

        $msg_bloqueia_Hipay = (!$b_libera_Hipay) ? ((!$b_LimiteDiarioOK) ? "<p class='txt-azul fontsize-pp'>" . number_format($total_carrinho, 2, ',', '.') . " ultrapassa o limite di�rio de compras on-line.</p>" : ((!$b_TentativasDiariasOK) ? "<p class='txt-azul fontsize-pp'>N�mero de pagamentos online (" . $qtde_last_dayOK . ") ultrapassa o limite di�rio.</p>" : "")) : "";

        $msg_bloqueia_Paypal = (!$b_libera_Paypal) ? ((!$b_LimiteDiarioOK) ? "<p class='txt-azul fontsize-pp'>" . number_format($total_carrinho, 2, ',', '.') . " ultrapassa o limite di�rio de compras on-line.</p>" : ((!$b_TentativasDiariasOK) ? "<p class='txt-azul fontsize-pp'>N�mero de pagamentos online (" . $qtde_last_dayOK . ") ultrapassa o limite di�rio.</p>" : "")) : "";

        $msg_bloqueia_Boleto = (!$b_libera_Boleto) ? "<p class='txt-azul fontsize-pp'>" . number_format($total_carrinho, 2, ',', '.') . " ultrapassa o limite de compras por boleto.</p>" : "";

        $msg_bloqueia_Deposito = (!$b_libera_Deposito) ? "<p class='txt-azul fontsize-pp'>" . number_format($total_carrinho, 2, ',', '.') . " ultrapassa o limite de compras por dep�sito.</p>" : "";

        $msg_bloqueia_Pix = (!$b_libera_Pix) ? "<p class='txt-azul fontsize-pp'>Sua compra de " . number_format($total_carrinho, 2, ',', '.') . " ultrapassa o limite de compras por pix.</p>" : "";

        // Pega o id_usuario do login para salvar ao inserir em tb_pag_compras, deposi de ter a venda cadastrada o ID ser� atualizado novamente.
        $id_usuario_prev = $controller->usuario->getId();
        $cliente_nome_prev = $controller->usuario->getNome();

        // finaliza valida��es
        // ==========================================================================================

        $pagto_venda = $pagto;
        // tipo_cliente   character varying(2),	-- 'M' - Money, 'E' - Money Express, 'LR' - Lanhouse Pr�, 'LO' - Lanhouse Pos, 
        $tipo_cliente = "M";

        if (($pagto == $FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) || ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_DEBITO'])) {
                // gera nova ordem em tb_pag_compras
                include RAIZ_DO_PROJETO . "banco/bradesco/inc_gen_order.php"; // 
                $numOrder = $orderId;
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
                $pagto_venda = $PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC;	// convert to numeric value to allow storing in tb_dist_venda_games

        } elseif ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_PIN_EPREPAG']) {
                gravaLog_EPPCASH_PINs("Vai para include -> inc_config.php\n  total_carrinho: '$total_carrinho'");

                // gera nova ordem em tb_pag_compras
                include RAIZ_DO_PROJETO . "banco/epp/inc_config.php";
                $numOrder = $orderId;
                $pagto_venda = $PAGAMENTO_PIN_EPREPAG_NUMERIC;

        } elseif (b_IsPagtoCieloAlpha($pagto)) {

                include_once DIR_CLASS . "gamer/classLimite.php";

                // Come�a Gest�o de Risco CIELO
                $carrinho_tmp = $GLOBALS['_SESSION']['carrinho'];
                $params = array();
                $limite = new Limite($pagto, $controller->usuario->getId(), $total_carrinho, $carrinho_tmp, "week");
                $mensagem = "";
                if ($limite->aplicaRegrasCielo($mensagem, $params)) {
                        $b_libera_Cielo = true;
                        // gera nova ordem em tb_pag_compras
                        include RAIZ_DO_PROJETO . "banco/cielo/inc_config.php";
                        $numOrder = $orderId;

                } else {
                        $b_libera_Cielo = false;
                        gravaLog_BloqueioPagtoOnline("Pagamento Cielo Bloqueado\n    pagto: $pagto, usuarioGames->getId(): " . $controller->usuario->getId() . ", total_carrinho: $total_carrinho, qtde_last_dayOK: " . $qtde_last_dayOK . ", total_diario: " . $total_diario . "\n    " . $mensagem);
                        redirect("/game/pedido/passo-2.php");
                }

                $msg_bloqueia_Cielo = (!$b_libera_Cielo) ? "<p class='txt-azul fontsize-pp'>Pagamento Cielo n�o dispon�vel: $mensagem.</p>" : "";

                // Termina Gest�o de Risco CIELO

        } elseif ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_PAYPAL_ONLINE']) {

                // gera nova ordem em tb_pag_compras
                include RAIZ_DO_PROJETO . "banco/paypal/inc_gen_order_pay.php";
                $numOrder = $_SESSION['pagamento.numorder'];
                $pagto_venda = $PAGAMENTO_PAYPAL_ONLINE_NUMERIC;
        } elseif ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_HIPAY_ONLINE']) {
                // gera nova ordem em tb_pag_compras
                //include DIR_WEB."prepag2/pag/hpy/hipay_single_payment.php"; 
                include RAIZ_DO_PROJETO . "banco/hipay/inc_gen_order_hip.php";
                $numOrder = $_SESSION['pagamento.numorder'];
                $pagto_venda = $PAGAMENTO_HIPAY_ONLINE_NUMERIC;
        } elseif ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_PIX']) {
                // gera nova ordem em tb_pag_compras
                require_once RAIZ_DO_PROJETO . "banco/pix/inc_config.php";
                $numOrder = $orderId;
                $pagto_venda = $PAGAMENTO_PIX_NUMERIC;

        } else {
                // Aqui chega o pagamento por boleto, por exemplo, que n�o tem include e apenas � transferido
        }

        // Recupera cesta
        $sql = "SELECT * FROM tb_pag_compras WHERE numCompra='" . $numOrder . "'";
        $ret = SQLexecuteQuery($sql);
        if (!$ret) {
                echo "Erro ao recuperar transa��o de pagamento (1).\n";
                die("Stop");
        }

        $sql = "UPDATE tb_pag_compras SET cliente_nome='" . $controller->usuario->getNome() . "', idcliente=" . $controller->usuario->getId() . ", status=1, cesta='" . montaCesta_pag() . "', total=" . (100 * ($total_carrinho + $taxas)) . " WHERE numcompra='" . $numOrder . "'";		// "iforma='".$_SESSION['pagamento.pagto']."', "

        $ret = SQLexecuteQuery($sql);
        if (!$ret) {
                echo "Erro ao atualizar transa��o de pagamento (2).\n";
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

//Adiciona modelo Campeonato no carrinho
if (isset($prod_camp) && $prod_camp > 0) {
        $carrinho_backup = $carrinho;
        $carrinho = array();
        $carrinho[$prod_camp] = 1;
}


//Valida se existe carrinho
if ($strRedirect == "") {
        if (!$carrinho || count($carrinho) == 0) {
                $strRedirect = "/game/pedido/passo-1.php";
        }
}

//Valida produtos
if ($strRedirect == "") {

        //Remove produtos invalidos
        foreach ($carrinho as $modeloId => $qtde) {

                if ($modeloId !== $GLOBALS['NO_HAVE']) {

                        $qtde = intval($qtde);
                        //Se qtde do modelo invalida, remove modelo
                        //if($qtde <= 0) $carrinho[$modeloId] = null;
                        if ($qtde <= 0)
                                unset($carrinho[$modeloId]);
                }//end if($modeloId !== $GLOBALS['NO_HAVE'])
                else {
                        foreach ($qtde as $codeProd => $vetor_valor) {
                                foreach ($vetor_valor as $valor => $quantidade) {
                                        //Se qtde do modelo invalida, remove modelo
                                        if ($quantidade <= 0)
                                                unset($carrinho[$modeloId][$codeProd][$valor]);
                                        if (count($carrinho[$modeloId][$codeProd]) == 0)
                                                unset($carrinho[$mod][$codeProd]);
                                        if (count($carrinho[$modeloId]) == 0)
                                                unset($carrinho[$mod]);
                                }//end foreach 
                        }//end foreach
                }//end else do if($modeloId !== $GLOBALS['NO_HAVE'])
        }

        //Se nao restou produto, retorna para o carrinho
        if (!$carrinho || count($carrinho) == 0) {
                $strRedirect = "/game/pedido/passo-1.php";
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
                !$pagto
                || !(
                        is_numeric($pagto)
                        || ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_BANCO_ITAU_ONLINE'])
                        || ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_PAYPAL_ONLINE'])
                        || ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_HIPAY_ONLINE'])
                        || ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_PIN_EPREPAG'])

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
                $strRedirect = "/game/pedido/passo-2.php";
        }
}

//Valida formas de pagamento
if ($strRedirect == "") {

        if (!in_array($pagto, $FORMAS_PAGAMENTO)) {
                $strRedirect = "/game/pedido/passo-2.php";
        }
}

if (!$b_libera_Bradesco && (($pagto == $FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) || ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_DEBITO']))) {
        ?>
        <script language="Javascript">
                alert("Erro: <?php echo $msg_bloqueia_Bradesco; ?>");
        </script>
        <?php

        $strRedirect = "/game/pedido/passo-1.php";
} else if (!$b_libera_BancodoBrasil && ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_BB_DEBITO_SUA_CONTA'])) {
        if ($controller->usuario->b_IsLogin_pagamento_bancodobrasil()) {
                ?>
                        <script language="Javascript">
                                alert("Erro: <?php echo $msg_bloqueia_BancodoBrasil; ?>");
                        </script>
                        <?php
                        $strRedirect = "/game/pedido/passo-1.php";
        }
} else if (!$b_libera_BancoItau && ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_BANCO_ITAU_ONLINE'])) {
        if ($controller->usuario->b_IsLogin_pagamento_bancoitau()) {
                ?>
                                <script language="Javascript">
                                        alert("Erro: <?php echo $msg_bloqueia_BancoItau; ?>");
                                </script>
                        <?php
                        $strRedirect = "/game/pedido/passo-1.php";
        }
} else if (!$b_libera_Paypal && ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_PAYPAL_ONLINE'])) {
        if ($controller->usuario->b_IsLogin_pagamento_paypal()) {
                ?>
                                        <script language="Javascript">
                                                alert("Erro: <?php echo $msg_bloqueia_Paypal; ?>");
                                        </script>
                        <?php
                        $strRedirect = "/game/pedido/passo-1.php";
        }
} else if (!$b_libera_Hipay && ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_HIPAY_ONLINE'])) {
        if ($controller->usuario->b_IsLogin_pagamento_hipay()) {
                ?>
                                                <script language="Javascript">
                                                        alert("Erro: <?php echo $msg_bloqueia_Hipay; ?>");
                                                </script>
                        <?php
                        $strRedirect = "/game/pedido/passo-1.php";
        }
} else if (!$b_libera_Deposito && ($pagto == $FORMAS_PAGAMENTO['DEP_DOC_TRANSF'])) {
        ?>
                                                <script language="Javascript">
                                                        alert("Erro: <?php echo $msg_bloqueia_Deposito; ?>");
                                                </script>
                <?php
                $strRedirect = "/game/pedido/passo-1.php";
} else if (!$b_libera_Boleto && ($pagto == $FORMAS_PAGAMENTO['BOLETO_BANCARIO'])) {
        ?>
                                                        <script language="Javascript">
                                                                alert("Erro: <?php echo $msg_bloqueia_Boleto; ?>");
                                                        </script>
                <?php
                $strRedirect = "/game/pedido/passo-1.php";
} else if (!$b_libera_Pix && ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_PIX'])) {
        ?>
                                                                <script language="Javascript">
                                                                        alert("Erro: <?php echo $msg_bloqueia_Pix; ?>");
                                                                </script>
                <?php
                $strRedirect = "/game/pedido/passo-1.php";
}

//Redireciona se ha algum dado invalido
$ret = "";

//Insere na tabela de venda
if (empty($strRedirect)) {

        $venda_id = obterIdVendaValido();

        $sql = "insert into tb_venda_games (" .
                "vg_id, vg_ug_id, vg_data_inclusao, vg_pagto_tipo, " .
                "vg_ultimo_status, vg_ultimo_status_obs, vg_http_referer_origem, vg_http_referer, vg_http_referer_ip) values (";
        $sql .= SQLaddFields($venda_id, "") . ",";
        $sql .= SQLaddFields($controller->usuario->getId(), "") . ",";
        $sql .= SQLaddFields("CURRENT_TIMESTAMP", "") . ",";
        $sql .= SQLaddFields($pagto_venda, "") . ",";
        $sql .= SQLaddFields($STATUS_VENDA['PEDIDO_EFETUADO'], "") . ",";
        $sql .= SQLaddFields("", "s") . ", ";
        $sql .= SQLaddFields($_SESSION['epp_origem'], "s") . ",";
        $sql .= SQLaddFields($_SESSION['epp_origem_referer'], "s") . ",";
        $sql .= SQLaddFields($_SESSION['epp_remote_addr'], "s") . ")";

        $ret = SQLexecuteQuery($sql);
        if (!$ret)
                $ret = "Erro ao inserir venda.";
        else {
                $ret = ""; //limpa resourceId
        }

        if ($controller->usuario->b_IsLogin_pagamento()) {
                // atualiza $venda_id
                $sql = "UPDATE tb_pag_compras SET idvenda=" . $venda_id . " WHERE numcompra='" . $numOrder . "'";
                $ret1 = SQLexecuteQuery($sql);
                if (!$ret1) {
                        echo "Erro ao atualizar transa��o de pagamento (3).";
                        gravaLog_TMP("Erro ao atualizar transa��o de pagamento (3).\n" . $sql . "");
                }
                if (b_IsPagtoCielo($pagto_venda)) {
                        //Inicio do novo trecho para cadastramento do token no BD
                        $softDescriptor = $GLOBALS['_SESSION']['pagamento.token'];
                        $sql = "insert into codigo_confirmacao (cc_tipo_usuario,cc_ug_id,cc_vg_id,cc_tipo_pagamento, cc_data_inclusao, cc_codigo, cc_status) values ('M'," . intval($controller->usuario->getId()) . "," . intval($venda_id) . ",'" . getCodigoCaracterParaPagto($pagto_venda) . "',NOW(),'" . $softDescriptor . "','0');";
                        $rs_token = SQLexecuteQuery($sql);
                        if (!$rs_token) {
                                die("Problema ao salvar o registro do Token.");
                        }
                }//end if(b_IsPagtoCielo($pagto_tipo))
        }

}

//Insere os modelos na tabela de venda modelos
//Este eh para guardar dados do modelo, valor e qtde do momento da venda
if ($ret == "" && $carrinho && count($carrinho) > 0) {
        foreach ($carrinho as $modeloId => $qtde) {
                if ($modeloId !== $GLOBALS['NO_HAVE']) {
                        $sql = "insert into tb_venda_games_modelo( ";
                        $sql .= "		vgm_vg_id, vgm_ogp_id, vgm_nome_produto, vgm_ogpm_id, vgm_nome_modelo, ";
                        $sql .= "		vgm_valor, vgm_qtde, vgm_opr_codigo, vgm_pin_valor, ";
                        $sql .= " 		vgm_game_id, vgm_valor_eppcash, vgm_pin_request ";
                        if ($modeloId == $GLOBALS['prod_mod_Alawar'] && isset($GLOBALS['_SESSION']['carrinho_alawar_prod_id'])) {
                                $sql .= ", vgm_game_id_alawar";
                        }
                        $sql .= ") ";
                        $sql .= "select " . $venda_id . ", ogp.ogp_id, ogp.ogp_nome, ogpm.ogpm_id, ogpm.ogpm_nome, ";
                        $sql .= "		ogpm.ogpm_valor, " . $qtde . ", ogp.ogp_opr_codigo, ogpm.ogpm_pin_valor, ";
                        $sql .= "		case ogp.ogp_id ";
                        $sql .= "			when 5 then " . SQLaddFields($controller->usuario->getHabboId(), "s");
                        $sql .= "			else NULL ";
                        $sql .= "		end, ogpm.ogpm_valor_eppcash, ogp_pin_request ";
                        if ($modeloId == $GLOBALS['prod_mod_Alawar'] && isset($GLOBALS['_SESSION']['carrinho_alawar_prod_id'])) {
                                $sql .= ", " . ((isset($GLOBALS['_SESSION']['carrinho_alawar_prod_id'])) ? $GLOBALS['_SESSION']['carrinho_alawar_prod_id'] : "0");
                        }
                        $sql .= "from tb_operadora_games_produto_modelo ogpm ";
                        $sql .= "inner join tb_operadora_games_produto ogp on ogp.ogp_id = ogpm.ogpm_ogp_id ";
                        $sql .= "where ogpm.ogpm_id = " . $modeloId;

                        $ret = SQLexecuteQuery($sql);
                        if ($ret)
                                $ret = ""; //limpa resourceId
                        else {

                                //Se deu erro ao inserir um modelo, deleta toda a venda
                                $sql = "delete from tb_venda_games_modelo where vgm_vg_id=" . $venda_id;
                                SQLexecuteQuery($sql);
                                $sql = "delete from tb_venda_games where vg_id=" . $venda_id;
                                SQLexecuteQuery($sql);
                                $sql = "delete from tb_venda_games_historico where vgh_vg_id=" . $venda_id;
                                SQLexecuteQuery($sql);

                                $ret = "Erro ao inserir modelo(s) na venda.\n";
                                break;
                        }
                }//end if($modeloId !== $GLOBALS['NO_HAVE']) 
                else {
                        foreach ($qtde as $codeProd => $vetor_valor) {
                                foreach ($vetor_valor as $valor => $quantidade) {
                                        $rs = null;
                                        $filtro['ogp_ativo'] = 1;
                                        $filtro['ogp_id'] = $codeProd;
                                        $filtro['ogp_mostra_integracao_com_loja'] = '1';
                                        $filtro['opr'] = 1;
                                        $ret = (new Produto)->obtermelhorado($filtro, null, $rs);
                                        if (!$rs || pg_num_rows($rs) == 0)
                                                $msg = "Nenhum produto dispon�vel no momento.";
                                        else
                                                $rs_row = pg_fetch_array($rs);

                                        $valor = ($valor < 0) ? $valor * (-1) : $valor;
                                        $sql = "insert into tb_venda_games_modelo( ";
                                        $sql .= "		vgm_vg_id, vgm_ogp_id, vgm_nome_produto, vgm_ogpm_id, vgm_nome_modelo, ";
                                        $sql .= "		vgm_valor, vgm_qtde, vgm_opr_codigo, vgm_pin_valor, ";
                                        $sql .= " 		vgm_game_id, vgm_valor_eppcash, vgm_pin_request ";
                                        $sql .= ") VALUES (" . $venda_id . ", " . $codeProd . ", '" . $rs_row['ogp_nome'] . "', (SELECT ogpm_id FROM tb_operadora_games_produto_modelo WHERE ogpm_ogp_id = " . $codeProd . "), (SELECT ogpm_nome FROM tb_operadora_games_produto_modelo WHERE ogpm_ogp_id = " . $codeProd . "), ";
                                        $sql .= "		" . $valor . ", " . $quantidade . ", " . $rs_row['ogp_opr_codigo'] . ", " . $valor . ", ";
                                        $sql .= "		NULL , " . ((new ConversionPINsEPP)->get_ValorEPPCash('E', $valor)) . ", " . $rs_row['ogp_pin_request'] . ");";
                                        $ret = SQLexecuteQuery($sql);
                                        if ($ret)
                                                $ret = ""; //limpa resourceId
                                        else {
                                                //Se deu erro ao inserir um modelo, deleta toda a venda
                                                $sql = "delete from tb_venda_games_modelo where vgm_vg_id=" . $venda_id;
                                                SQLexecuteQuery($sql);
                                                $sql = "delete from tb_venda_games where vg_id=" . $venda_id;
                                                SQLexecuteQuery($sql);
                                                $sql = "delete from tb_venda_games_historico where vgh_vg_id=" . $venda_id;
                                                SQLexecuteQuery($sql);
                                                $ret = "Erro ao inserir modelo(s) na venda.\n";
                                                break;
                                        }
                                }//end foreach 
                        }//end foreach
                }//end else do if($modeloId !== $GLOBALS['NO_HAVE'])
        }//end foreach
}

//Restaura carrinho
if (isset($prod_camp) && $prod_camp > 0 && $carrinho_backup != "") {
        $carrinho = $carrinho_backup;
}

//Redireciona se ha algum dado invalido
if ($ret != "") {
        $strRedirect = "/game/mensagem.php";
        $hiddden = "<input type='hidden' name='msg' id='msg' value='" . urlencode($ret) . "'>";
        $hiddden .= "<input type='hidden' name='titulo' id='titulo' value='" . urlencode("Erro") . "'>";
        $hiddden .= "<input type='hidden' name='link' id='link' value='/game/pedido/passo-1.php'>";
}


if (empty($strRedirect)) {

        //Registra venda
        $ret = "";

        //Limpa objetos de uma venda
        unset($_SESSION['carrinho']);
        unset($_SESSION['carrinho_alawar_prod_id']);
        unset($_SESSION['pagamento.pagto']);
        unset($_SESSION['pagamento.pagto_ja_fiz']);
        unset($_SESSION['pagamento.parcelas.REDECARD_MASTERCARD']);
        unset($_SESSION['pagamento.parcelas.REDECARD_DINERS']);

        //Log na base
        usuarios_games_log($GLOBALS['USUARIO_GAMES_LOG_TIPOS']['VENDA'], null, $venda_id);

        //Envia email
        $sql = "select * from tb_venda_games vg " .
                "inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id " .
                "where vg.vg_id = " . $venda_id . " and vg.vg_ug_id=" . $controller->usuario->getId() . " " .
                "order by vgm_opr_codigo, vgm_valor ";
        $rs_venda_modelos = SQLexecuteQuery($sql);
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
        $qtde_total = 0;
        $total_geral = 0;
        while ($rs_venda_modelos_row = pg_fetch_array($rs_venda_modelos)) {

                $pagto_tipo = $rs_venda_modelos_row['vg_pagto_tipo'];

                $codigo = $rs_venda_modelos_row['vgm_id'];
                $qtde = $rs_venda_modelos_row['vgm_qtde'];
                $valor = $rs_venda_modelos_row['vgm_valor'];
                $qtde_total += $qtde;
                $total_geral += $valor * $qtde;
                $aux_lista .= "<tr bgcolor='#E6E6E6'>
                            <td width='3'>&nbsp;</td>
                            <td align='left'><nobr>" . str_replace(" ", "&nbsp;", $rs_venda_modelos_row['vgm_nome_produto']) . "</nobr></td>
                            <td align='center'>" . $rs_venda_modelos_row['vgm_nome_modelo'] . "</td>
                            <td align='center'>" . number_format($valor, 2, ',', '.') . "</td>
                            <td align='center'><b>" . $qtde . "</b></td>
                            <td align='right'><b>" . number_format($valor * $qtde, 2, ',', '.') . "</b></td>
                            <td width='5'>&nbsp;</td>
                    </tr>";
        }
        $aux_lista .= "<tr  bgcolor='#CCCCCC'>
                    <td colspan='3'>&nbsp;</td>
        <td align='right' colspan='2'><b><nobr>Total&nbsp;Geral&nbsp;(R$)</nobr></b></td>
                    <td align='right'><b>" . number_format($total_geral, 2, ',', '.') . "</b></td>
                    <td width='5'>&nbsp;</td>
            </tr>
            </table>";
        if ($controller->logado) {
                if ($pagto == $FORMAS_PAGAMENTO['BOLETO_BANCARIO']) {
                        $GLOBALS['_SESSION']['boleto_imagem'] = 'PedidoRegistrado';
                        $GLOBALS['_SESSION']['aux_lista_oferta'] = $aux_lista;
                } else {
                        $objEnvioEmailAutomatico = new EnvioEmailAutomatico(TIPO_USUARIO_GAMER, 'PedidoRegistrado');
                        $objEnvioEmailAutomatico->setUgID($controller->usuario->getId());
                        $objEnvioEmailAutomatico->setListaCreditoOferta($aux_lista);
                        $objEnvioEmailAutomatico->setPedido(formata_codigo_venda($venda_id));
                        $objEnvioEmailAutomatico->MontaEmailEspecifico();
                }

        }

        //Boleto
        //Se forma de pagamento foi boleto, cria o boleto da venda
        if ($pagto == $FORMAS_PAGAMENTO['BOLETO_BANCARIO']) {

                //obtem o valor total da venda
                $sql = "select * from tb_venda_games vg " .
                        "inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id " .
                        "where vg.vg_id = " . $venda_id . " and vg.vg_ug_id=" . $controller->usuario->getId();
                $rs_venda_modelos = SQLexecuteQuery($sql);
                if ($rs_venda_modelos && pg_num_rows($rs_venda_modelos) > 0) {
                        $total_geral = 0;
                        while ($rs_venda_modelos_row = pg_fetch_array($rs_venda_modelos)) {
                                $qtde = $rs_venda_modelos_row['vgm_qtde'];
                                $valor = $rs_venda_modelos_row['vgm_valor'];
                                $valor = ($valor < 0) ? $valor * (-1) : $valor;
                                $total_geral += $valor * $qtde;
                        }
                }
                /*
                        //Qtde de dias para o vencimento
                        //----------------------------------------------------
                        $qtde_dias_venc = 2;

                        //Codigo do banco
                        //----------------------------------------------------
                        $bco_codigo = 104;
                        $taxa_adicional = $BOLETO_TAXA_ADICIONAL;

                        //Formato do Nosso Numero e Numero do documento
                        //----------------------------------------------------
                        //MMMMCCCCCVVVVVV
                        //Onde: 
                        //0211 � identifica cliente MONEY
                        //CCCCC � c�digo do cliente MONEY (composto com zeros a esquerda)
                        //VVVVVV � codigo da venda (composto com zeros a esquerda)
                        //$num_doc = "8211" . sprintf("%05d%06d", $controller->usuario->getId(), $venda_id);
                        $num_doc = "8211" . sprintf("%05d%06d", substr($controller->usuario->getId(), 0, 5), substr($venda_id, 0, 6));
                */
                //Boleto Bradesco
                //Formato do Nosso Numero e Numero do documento
                //----------------------------------------------------
                //2EEEEECCCCC Onde: 
                //2 � identifica MONEY
                //CCCCC � c�digo do cliente MONEY (composto com zeros a esquerda)
                //VVVVV � codigo da venda (composto com zeros a esquerda)
                $num_doc = "2" . "00" . str_pad($venda_id, 8, "0", STR_PAD_LEFT);
                $qtde_dias_venc = $GLOBALS['BOLETO_MONEY_BRADESCO_QTDE_DIAS_VENCIMENTO'];

                if ($total_carrinho < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
                        $taxa_adicional = $GLOBALS['BOLETO_MONEY_BRADESCO_TAXA_ADICIONAL'];
                else
                        $taxa_adicional = 0;

                if (BANCO_BOLETO == "asaas" || $controller->usuario->getId() == 1354068) {
                        $bco_codigo = $GLOBALS['BOLETO_MONEY_ASAAS_COD_BANCO'];
                } elseif (BANCO_BOLETO == "bradesco") {
                        $bco_codigo = $GLOBALS['BOLETO_MONEY_BRADESCO_COD_BANCO'];
                }
                // Usa Boleto Itau para alguns usu�rios
                if ($controller->logado) {
                        if (BANCO_BOLETO == "asaas" || $controller->usuario->getId() == 1354068) {
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
                        }//end if($controller->usuario->b_Is_Boleto_Bradesco())
                        elseif ($controller->usuario->b_Is_Boleto_Banespa()) {
                                $qtde_dias_venc = $GLOBALS['BOLETO_MONEY_BANESPA_QTDE_DIAS_VENCIMENTO'];
                                $bco_codigo = $GLOBALS['BOLETO_MONEY_BANCO_BANESPA_COD_BANCO'];
                                if ($total_carrinho < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
                                        $taxa_adicional = $GLOBALS['BOLETO_MONEY_BANESPA_TAXA_ADICIONAL'];
                                else
                                        $taxa_adicional = 0;
                                $num_doc = "2" . "000" . str_pad($venda_id, 8, "0", STR_PAD_LEFT);
                        } elseif ($controller->usuario->b_Is_Boleto_Itau()) {
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
                $sql = "insert into boleto_bancario_games (" .
                        "bbg_ug_id, bbg_vg_id, bbg_data_inclusao, bbg_valor, bbg_valor_taxa, " .
                        "bbg_bco_codigo, bbg_documento, bbg_data_venc" .
                        ") values (";
                $sql .= SQLaddFields($controller->usuario->getId(), "") . ",";
                $sql .= SQLaddFields($venda_id, "") . ",";
                $sql .= SQLaddFields("CURRENT_TIMESTAMP", "") . ",";
                $sql .= SQLaddFields($total_geral + $taxa_adicional, "") . ",";
                $sql .= SQLaddFields($taxa_adicional, "") . ",";
                $sql .= "$bco_codigo,";
                $sql .= SQLaddFields($num_doc, "s") . ","; //documento
                $sql .= SQLaddFields("CURRENT_DATE + interval '$qtde_dias_venc day'", "") . ")"; //vencimento
                $ret = SQLexecuteQuery($sql);

                //atualiza dados do pagamento e status da venda
                if ($ret) {
                        $sql = "update tb_venda_games set 
                                            vg_pagto_data_inclusao = " . SQLaddFields("CURRENT_TIMESTAMP", "") . ",
                                            vg_pagto_banco = '" . $bco_codigo . "',
                                            vg_pagto_num_docto = '" . $num_doc . "',
                                            vg_ultimo_status = " . SQLaddFields($GLOBALS['STATUS_VENDA']['DADOS_PAGTO_RECEBIDO'], "") . "
                                    where vg_id = " . $venda_id;
                        $ret = SQLexecuteQuery($sql);
                        if (!$ret)
                                $ret = "Erro ao atualizar status da venda.\n";
                        else
                                $ret = ""; //limpa resourceId
                }

        }

        //Redecard
        //Se forma de pagamento foi cartoes Redecard, cria o item Redecard
        if ($pagto == $FORMAS_PAGAMENTO['REDECARD_MASTERCARD']) {

                //Insere mastercard
                $sql = "insert into tb_venda_games_redecard (" .
                        "vgrc_ug_id, vgrc_vg_id, vgrc_data_inclusao, vgrc_parcelas" .
                        ") values (";
                $sql .= SQLaddFields($controller->usuario->getId(), "") . ",";
                $sql .= SQLaddFields($venda_id, "") . ",";
                $sql .= SQLaddFields("CURRENT_TIMESTAMP", "") . ",";
                $sql .= SQLaddFields($parcelas_REDECARD_MASTERCARD, "") . ")";
                $ret = SQLexecuteQuery($sql);


        } elseif ($pagto == $FORMAS_PAGAMENTO['REDECARD_DINERS']) {

                //Insere diners
                $sql = "insert into tb_venda_games_redecard (" .
                        "vgrc_ug_id, vgrc_vg_id, vgrc_data_inclusao, vgrc_parcelas" .
                        ") values (";
                $sql .= SQLaddFields($controller->usuario->getId(), "") . ",";
                $sql .= SQLaddFields($venda_id, "") . ",";
                $sql .= SQLaddFields("CURRENT_TIMESTAMP", "") . ",";
                $sql .= SQLaddFields($parcelas_REDECARD_DINERS, "") . ")";
                $ret = SQLexecuteQuery($sql);
        }
} //end if(empty($strRedirect))

//Redirecionando se deu algum problema
if (!empty($strRedirect)) {
        ?>
        <form name="pagamento" id="pagamento" method="POST" action="<?php echo $strRedirect; ?>">
                <?php
                echo $hiddden;
                ?>
        </form>
        <script language='javascript'>
                document.getElementById("pagamento").submit();
        </script>
        <?php
}//end if(!empty($strRedirect)) 
else {
        //Comprovante

        //Se pagto_ja_fiz redireciona para a pagina de entrada dos dados de pagamento
        $hiddden = "<input type='hidden' name='venda' id='venda' value='" . $venda_id . "'>";
        //Pagamento OFF-LINE
        if ($pagto == $FORMAS_PAGAMENTO['DEP_DOC_TRANSF'] && isset($pagto_ja_fiz) && $pagto_ja_fiz == "1") {
                $strRedirect = "/game/pagamento/informa_deposito.php";
                $proximo_passo = "PagtoOffLine";
                //Redecard - inicia processo de pagamento
        } elseif ($pagto == $FORMAS_PAGAMENTO['REDECARD_MASTERCARD'] || $pagto == $FORMAS_PAGAMENTO['REDECARD_DINERS']) {
                $strRedirect = "/prepag2/commerce/redecard/rc_envio.php";
                redirect($strRedirect);
                //Boleto e Pagamento Online
        } else {
                //redireciona para o redirecionador de comprovante
                $proximo_passo = "PagtoOnLine";
        }

        if ($proximo_passo == "PagtoOnLine") {

                //Buscando Dados
                require_once DIR_INCS . 'gamer/venda_e_modelos_logica.php';

                $rs_venda_row = pg_fetch_array($rs_venda);
                $pagto_tipo = $rs_venda_row['vg_pagto_tipo'];
                $venda_status = $rs_venda_row['vg_ultimo_status'];

                //Verifica se venda cancelada
                //Variavel $msg alterada dentro do require_once venda_e_modelos_logica.php
                if ($msg == "") {
                        if ($venda_status == $STATUS_VENDA['VENDA_CANCELADA']) {
                                ?>
                                <form name="pagamento" id="pagamento" method="POST" action="/game/mensagem.php">
                                        <input type='hidden' name='msg' id='msg' value='Esta venda se encontra cancelada no momento'>
                                        <input type='hidden' name='titulo' id='titulo' value='Informa Pagamento'>
                                        <input type='hidden' name='link' id='link' value='/game/conta/pedidos.php'>
                                </form>
                                <script language='javascript'>
                                        document.getElementById("pagamento").submit();
                                </script>
                                <?php
                                die();
                        }
                }//end if($msg == "")

                //Comprovantes
                if ($pagto_tipo == $FORMAS_PAGAMENTO['DEP_DOC_TRANSF']) {
                        $strRedirect = "/game/pagamento/pagto_compr_offline.php";

                } elseif ($pagto_tipo == $FORMAS_PAGAMENTO['BOLETO_BANCARIO']) {
                        $strRedirect = "/game/pagamento/pagto_compr_boleto.php";

                } elseif ($pagto_tipo == $FORMAS_PAGAMENTO['REDECARD_MASTERCARD'] || $pagto_tipo == $FORMAS_PAGAMENTO['REDECARD_DINERS']) {
                        $strRedirect = "/prepag2/commerce/redecard/rc_comprovante.php";
                        redirect($strRedirect);
                        die();

                } elseif ($pagto_tipo == $FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) {
                        $strRedirect = "/game/pagamento/pagto_compr_online.php";

                } elseif ($pagto_tipo == $FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_DEBITO']) {
                        $strRedirect = "/game/pagamento/pagto_compr_online.php";

                } elseif ($pagto_tipo == $FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_CREDITO']) {
                        $strRedirect = "/game/pagamento/pagto_compr_online.php";

                } elseif ($pagto_tipo == $FORMAS_PAGAMENTO['PAGAMENTO_BB_DEBITO_SUA_CONTA']) {
                        $strRedirect = "/game/pagamento/pagto_compr_online.php";

                } elseif ($pagto_tipo == $PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC) {
                        $strRedirect = "/game/pagamento/pagto_compr_online.php";

                } elseif ($pagto_tipo == $PAGAMENTO_PIN_EPREPAG_NUMERIC) {
                        $strRedirect = "/game/pagamento/pagto_compr_online.php";

                } elseif ($pagto_tipo == $PAGAMENTO_HIPAY_ONLINE_NUMERIC) {
                        $strRedirect = "/game/pagamento/pagto_compr_online.php";

                } elseif ($pagto_tipo == $PAGAMENTO_PAYPAL_ONLINE_NUMERIC) {
                        $strRedirect = "/game/pagamento/pagto_compr_online.php";

                } elseif ($pagto_tipo == $PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC) {
                        $strRedirect = "/game/pagamento/pagto_compr_online.php";

                } elseif ($pagto_tipo == $PAGAMENTO_PIX_NUMERIC) {
                        $strRedirect = "/game/pagamento/pagto_compr_online.php";

                        //----Wagner 
                } elseif (b_IsPagtoCielo($pagto_tipo)) {
                        $strRedirect = "/game/pagamento/pagto_compr_online.php";

                        //----Wagner AT�
                } else {
                        $strRedirect = "/game/conta/pedidos.php";
                }

        }//end if($proximo_passo == "PagtoOnLine") 
        elseif ($proximo_passo == "PagtoOffLine") {
        }//end elseif($proximo_passo == "PagtoOffLine")
        else {
                echo "Problema no Tipo de Pagamento. Por favor, entre em contato com o suporte da E-Prepag e informe o ERRO: WM5156. Obrigado.";
        }//end else do elseif($proximo_passo == "PagtoOffLine") 

        if (($proximo_passo == "PagtoOffLine") || ($proximo_passo == "PagtoOnLine")) {
                //Redireciona
                ?>
                <form name="pagamento" id="pagamento" method="POST" action="<?php echo $strRedirect; ?>">
                        <?php
                        echo $hiddden;
                        ?>
                </form>
                <script language='javascript'>
                        document.getElementById("pagamento").submit();
                </script>
                <?php
        }//end if(($proximo_passo == "PagtoOffLine") || ($proximo_passo == "PagtoOnLine"))

}//end else do if(!empty($strRedirect))

//Fechando Conex�o
pg_close($connid);
?>