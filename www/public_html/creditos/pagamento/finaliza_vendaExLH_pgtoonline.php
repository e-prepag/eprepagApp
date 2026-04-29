<?php

require_once "../../../includes/constantes.php";
require_once DIR_INCS . "configIP.php";
require_once DIR_INCS . "main.php";
require_once DIR_INCS . "pdv/main.php";
require_once DIR_CLASS . "pdv/classOperadorGamesUsuario.php";
require_once DIR_INCS . "pdv/corte_constantes.php";

function finalizaVendaExLHPgtoOnlineLog($message, array $context = array()) {
        $context["script"] = "finaliza_vendaExLH_pgtoonline.php";
        error_log("[finaliza_vendaExLH_pgtoonline] " . $message . " " . json_encode($context));
}

////Recupera o usuario do session
$usuarioGames = unserialize($_SESSION['dist_usuarioGames_ser']);

//validacao
$msg = "";

$pagto = $_SESSION['dist_pagamento.pagto'];
$produtos = $_SESSION['dist_pagamento.total'];
$iforma = $pagto;
if(!$usuarioGames){
    header("Location: /creditos/login.php");
}
if($usuarioGames->b_IsLogin_pagamento())  {

        // atualiza cesta e total (em LH-Pre pagamos online apenas boletos, não tem lista de produtos)
        $total_carrinho = $_SESSION['dist_pagamento.total'];
        $taxas = $_SESSION['dist_pagamento.taxa'];

        // ==========================================================================================
        // Faz validação de vendas totais, copia de pagamento.php

        // Testa que usuário comprou no máximo 10 vezes nas últimas 24 horas
        $qtde_last_dayOK = getNVendasLH($usuarioGames->getId());

        // Calcula o total diario para pagamentos Online Bradesco
        $total_diario = getVendasLHTotalDiarioOnline($usuarioGames->getId()); 

        $b_TentativasDiariasOK = true;//($qtde_last_dayOK<=$RISCO_LANS_PRE_PAGAMENTOS_DIARIO);
        $b_LimiteDiarioOK = (($total_carrinho+$total_diario)<=$RISCO_LANS_PRE_TOTAL_DIARIO); // ((($total_carrinho+$total_diario)<=$RISCO_LANS_PRE_TOTAL_DIARIO) && ($qtde_last_dayOK<=$RISCO_LANS_PRE_PAGAMENTOS_DIARIO))

        // Libera pagamento Online Banco do Brasil
        $b_libera_BancodoBrasil = $b_LimiteDiarioOK && $b_TentativasDiariasOK;// && $usuarioGames->b_IsLogin_pagamento_bancodobrasil();

        // Libera Bradesco apenas se limite diario não ultrapassado e tem até 10 compras nas últimas 24 horas	//produtos (Habbo e GPotato) 
        $b_libera_Bradesco = $b_LimiteDiarioOK && $b_TentativasDiariasOK;	//$b_IsProdutoOK && 

        // Libera pagamento Online Banco Itaú
        $b_libera_BancoItau = $b_LimiteDiarioOK && $b_TentativasDiariasOK && $usuarioGames->b_IsLogin_pagamento_bancoitau();

        $msg_bloqueia_Bradesco = (!$b_libera_Bradesco)?((!$b_LimiteDiarioOK)?" \\n \\n Sua compra de ".number_format($total_carrinho, 2, ',', '.')." ultrapassa o limite diário de compras on-line.":((!$b_TentativasDiariasOK)?" \\n \\n Número de pagamentos online (".$qtde_last_dayOK.") ultrapassa o limite diário.":"")):"";

        $msg_bloqueia_BancodoBrasil = (!$b_libera_BancodoBrasil)?((!$b_LimiteDiarioOK)?" \\n \\n Sua compra de ".number_format($total_carrinho, 2, ',', '.')." ultrapassa o limite diário de compras on-line.":((!$b_TentativasDiariasOK)?" \\n \\n Número de pagamentos online (".$qtde_last_dayOK.") ultrapassa o limite diário.":"")):"";

        $msg_bloqueia_BancoItau = (!$b_libera_BancoItau)?((!$b_LimiteDiarioOK)?" \\n \\n Sua compra de ".number_format($total_carrinho, 2, ',', '.')." ultrapassa o limite diário de compras on-line.":((!$b_TentativasDiariasOK)?" \\n \\n Número de pagamentos online (".$qtde_last_dayOK.") ultrapassa o limite diário.":"")):"";

        // finaliza validações
        // ==========================================================================================

        $pagto_venda = $pagto;
        // tipo_cliente   character varying(2),	-- 'M' - Money, 'E' - Money Express, 'LR' - Lanhouse Pré, 'LO' - Lanhouse Pos, 
        $tipo_cliente = "LR";
        $numOrder = "00000000000000000";
        $id_usuario_prev = $usuarioGames->getId();
        $cliente_nome_prev = $usuarioGames->getNome();

        unset($_SESSION['sql_pagto_online_insert']);

        if(($pagto==$FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) || ($pagto==$FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_DEBITO'])) {
                // gera nova ordem em tb_pag_compras
                require_once RAIZ_DO_PROJETO . "banco/bradesco/inc_gen_order.php"; // 
                $numOrder = $orderId;
        } elseif($pagto==$FORMAS_PAGAMENTO['PAGAMENTO_BB_DEBITO_SUA_CONTA']) {
                // gera nova ordem em tb_pag_compras
                require_once RAIZ_DO_PROJETO . "banco/bancodobrasil/inc_gen_order_bbr.php"; // 
                $numOrder = $orderId;
        } elseif($pagto==$FORMAS_PAGAMENTO['PAGAMENTO_BANCO_ITAU_ONLINE']) {
                $pagto_venda = $PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC;

                $numOrder = $orderId;
        } elseif($pagto==$FORMAS_PAGAMENTO['PAGAMENTO_PIX']) {
                // gera nova ordem em tb_pag_compras
                require_once RAIZ_DO_PROJETO . "banco/pix/inc_config.php";
                $numOrder = $orderId; 
                $pagto_venda = $PAGAMENTO_PIX_NUMERIC;
        } else {
                die("Erro: forma de pagamento desconhecida. (pagto=$pagto)<br>\n");
        }

        $snome = (($usuarioGames->getTipoCadastro()=="PJ")?$usuarioGames->getNomeFantasia():$usuarioGames->getNome())." (".$usuarioGames->getTipoCadastro().")";

        // ver montaCesta_pag() para cesta Money
        $cesta_boleto_pagto_online = "item:Crédito OnLine\n1\ncrédito\n".(100*$total_carrinho)."\n";

        $sql = "UPDATE tb_pag_compras SET cliente_nome=$1, idcliente=$2, status=1, cesta=$3, total=$4 WHERE numcompra=$5";
        $params = array($snome, $usuarioGames->getId(), $cesta_boleto_pagto_online, (100 * ($total_carrinho + $taxas)), $numOrder);
        $ret = SQLexecuteQueryParams($sql, $params);
        if(!$ret) {
                echo "Erro ao atualizar transacao de pagamento (2).\n";
                die("Stop");
        }
}


//Produtos
if($msg == ""){
	if(!$produtos) $msg = "Nenhum produto selecionado.\n";
}

$usuarioId = $usuarioGames->getId();
$vg_ex_email = $usuarioGames->getEmail();


if($msg != ""){
        $strRedirect = "/creditos/mensagem.php?msg=" . urlencode($msg) . "&pt=" . urlencode("Erro") . "&link=" . urlencode("/creditos/erro.php?err=51a");
        redirect($strRedirect);
        die("Stop");
}

// processa so se:
//		- PagtoOnline estiver autorizado para o usuario
//		- forma de pagto for de fato online
//		- lan cadastrada como Pre
if((!$usuarioGames->b_IsLogin_pagamento()) ||
        (!b_IsPagtoOnline($pagto)) ||
        (!$usuarioGames->bIsLanPre()) ) {
                $msg = "Pagamento Online para LHs Pre nao processado (pagto: " . $pagto . ", Pre: " . (($usuarioGames->bIsLanPre())?"Sim":"Nao") . ", Pagto_online: " . (($usuarioGames->b_IsLogin_pagamento())?"OK":"Nao") . ", e pagto. online?: " . ((b_IsPagtoOnline($pagto))?"Sim":"Nao") . ")";
                $strRedirect = "/creditos/mensagem.php?msg=" . urlencode($msg) . "&pt=" . urlencode("Erro") . "&link=" . urlencode("/creditos/erro.php?err=51");
                redirect($strRedirect);
                die("Stop sdf sd");
}

//Inicia transacao
if($msg == ""){
        $sql = "BEGIN TRANSACTION ";
        $ret = SQLexecuteQuery($sql);
        if(!$ret) $msg = "Erro ao iniciar transacao.\n";
}

//Gera a venda
if($msg == ""){
        $venda_id = obterIdVendaValido();

        if(!$msg) {
                //Guarda id da venda no session
                $_SESSION['venda'] = $venda_id;
                $_SESSION['pagamento.numorder'] = $orderId;

                // Salva registro de vendas
                $pagto_venda_insert = (($pagto==$FORMAS_PAGAMENTO["PAGAMENTO_BANCO_ITAU_ONLINE"])?$PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC:(($pagto==$FORMAS_PAGAMENTO["PAGAMENTO_PIX"])?$PAGAMENTO_PIX_NUMERIC:$pagto));
                $sql = "insert into tb_dist_venda_games (vg_id, vg_ug_id, vg_data_inclusao, vg_pagto_tipo, vg_ultimo_status, vg_ultimo_status_obs, vg_deposito_em_saldo) values ($1, $2, CURRENT_TIMESTAMP, $3, $4, $5, $6)";
                $params = array($venda_id, $usuarioId, $pagto_venda_insert, $GLOBALS["STATUS_VENDA"]["PEDIDO_EFETUADO"], "", "1");
                $ret = SQLexecuteQueryParams($sql, $params);
                if(!$ret) {
                        $msg = "Erro ao inserir venda. Por favor, tente novamente atualizando a página. Obrigado *.\n";
                        gravaLog_BoletoExpressLH($msg."\n".$sql);
                }
        }

        if(!$msg) {
                // Salva venda_id em tb_pag_compras
                $sql = "UPDATE tb_pag_compras SET idvenda=$1 WHERE numcompra=$2";
                $params = array($venda_id, $numOrder);
                $ret = SQLexecuteQueryParams($sql, $params);
                if(!$ret) {
                        $msg = "Erro ao atualizar transação de pagamento (2a, id_venda=$venda_id, numcompra='".$numOrder."').\n";
                        gravaLog_BoletoExpressLH($msg."\n".$sql);
                }
        }
}

//Log na base
if($msg == ""){
        usuarios_games_log($GLOBALS['USUARIO_GAMES_LOG_TIPOS']['VENDA'], $usuarioId, $venda_id);
}

//Boleto
if($msg == ""){

        //obtem o valor total da venda
        //----------------------------------------------------
        // $produtos
        $total_geral = $produtos;
        $taxa_adicional = 0;

        if($pagto==$GLOBALS['FORMAS_PAGAMENTO']['BOLETO_BANCARIO']) {
                //Boleto Bradesco
                //Formato do Nosso Numero e Numero do documento
                //----------------------------------------------------
                //5EEEEECCCCC Onde: 
                //4 – identifica MONEY EXPRESS LH
                //CCCCC – código do cliente MONEY (composto com zeros a esquerda)
                //VVVVV – codigo da venda (composto com zeros a esquerda)
//		$num_doc = "4" . substr("00000" . $usuarioId, -5) . substr("00000" . $venda_id, -5);
                $num_doc = "4" . "00" . str_pad($venda_id, 8, "0", STR_PAD_LEFT);

                if($total_geral>=$BOLETO_LIMITE_PARA_TAXA_ADICIONAL_BRADESCO) $taxa_adicional = 0;
                else $taxa_adicional = $GLOBALS['BOLETO_TAXA_ADICIONAL_BRADESCO'];

                //		$taxa_adicional = $GLOBALS['BOLETO_MONEY_BRADESCO_TAXA_ADICIONAL'];
                $qtde_dias_venc = $GLOBALS['BOLETO_MONEY_BRADESCO_QTDE_DIAS_VENCIMENTO'];
                $bco_codigo = $GLOBALS['BOLETO_MONEY_BRADESCO_COD_BANCO'];


        } elseif(b_IsPagtoOnline($pagto)) {
                //5EEEEECCCCC Onde: 
                //5 – identifica MONEY EXPRESS LH PAGTO ONLINE
                //CCCCC – código do cliente MONEY (composto com zeros a esquerda)
                //VVVVV – codigo da venda (composto com zeros a esquerda)
                $num_doc = "5" . "00" . str_pad($venda_id, 8, "0", STR_PAD_LEFT);

                $taxa_adicional = getTaxaPagtoOnline($pagto, $total_geral);
                $qtde_dias_venc = 1;
                $bco_codigo = getBcoCodigo($pagto); //$GLOBALS['BOLETO_MONEY_BRADESCO_COD_BANCO'];

        } else {
                die("Forma de pagmto desconhecida em finaliza_vendaExLH (pgtoonline: '$pagto')");
        }

        //Insere boleto na base
        //----------------------------------------------------
        $sql = "insert into dist_boleto_bancario_games (bbg_ug_id, bbg_vg_id, bbg_data_inclusao, bbg_valor, bbg_valor_taxa, bbg_bco_codigo, bbg_documento, bbg_data_venc) values ($1, $2, CURRENT_TIMESTAMP, $3, $4, $5, $6, CURRENT_DATE + $7::integer)";
        $params = array($usuarioId, $venda_id, $total_geral + $taxa_adicional, $taxa_adicional, $bco_codigo, $num_doc, $qtde_dias_venc);
        $ret = SQLexecuteQueryParams($sql, $params);

        //atualiza dados do pagamento e status da venda
        if($ret){
                $sql = "update tb_dist_venda_games set vg_cor_codigo = 0, vg_pagto_data_inclusao = CURRENT_TIMESTAMP, vg_pagto_banco = $1, vg_pagto_num_docto = $2 where vg_id = $3";
                $params = array($bco_codigo, $num_doc, $venda_id);
                $ret = SQLexecuteQueryParams($sql, $params);
                if(!$ret) {
                        $msg = "Erro ao atualizar status da venda.\n";
                        finalizaVendaExLHPgtoOnlineLog("erro_update_tb_dist_venda_games", array("usuario_id" => $usuarioId, "venda_id" => $venda_id, "banco" => $bco_codigo, "documento" => $num_doc, "erro" => function_exists("pg_last_error") ? pg_last_error() : ""));
                }
        } else {
                $msg = "Erro ao inserir boleto.\n";
                finalizaVendaExLHPgtoOnlineLog("erro_insert_dist_boleto_bancario_games", array("usuario_id" => $usuarioId, "venda_id" => $venda_id, "valor" => $total_geral + $taxa_adicional, "taxa" => $taxa_adicional, "banco" => $bco_codigo, "documento" => $num_doc, "dias_vencimento" => $qtde_dias_venc, "erro" => function_exists("pg_last_error") ? pg_last_error() : ""));
        }
} 

//Finaliza transacao
if($msg == ""){
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
if($msg == ""){

    $envioEmail = new EnvioEmailAutomatico(TIPO_USUARIO_LAN,'AdicaoSaldoLan');
    $saldoAdicionado = number_format($total_geral, 2, ',', '.');

    $formaPagamento = (array_key_exists($pagto, $FORMAS_PAGAMENTO_DESCRICAO)) ? $FORMAS_PAGAMENTO_DESCRICAO[$pagto] : '';

    $envioEmail->setUgID($usuarioId);
    $envioEmail->setPedido(formata_codigo_venda($venda_id));
    $envioEmail->setSaldoAdicionado($saldoAdicionado);
    $envioEmail->setFormaPagamento($formaPagamento);
    $envioEmail->MontaEmailEspecifico();
}

//Retorno
if($msg != ""){
        $msg = "<script>alert('" . str_replace("\n", "\\n", $msg) . "');</script>";
        echo $msg;
        exit;
} else {
        if(b_IsPagtoOnline($pagto)) {
                $msg  = "<font color='red'><strong><span class='style3'>";
                $msg .= "Sua compra está completa e o boleto foi cadastrado com sucesso: <br>";
                $msg .= "<a href='index.php'>clique aqui</a> para continuar comprando!!";
                $msg .= "</span></strong></font>";

                $strRedirect = "/creditos/pagamento/pagto_compr_online.php";
                //Redireciona
                require_once DIR_CLASS . "util/Util.class.php";
                Util::redirect($strRedirect);

                exit;
        } else {
                //token
                if($msg == ""){
                        //$token = date('YmdHis') . "," . $venda_id . "," . $usuarioId;
                        $token = date('YmdHis', strtotime("+20 day")) . "," . $venda_id . "," . $usuarioId;
                        $objEncryption = new Encryption();
                        $token = $objEncryption->encrypt($token);
                }

                $msgEmail = "";
                $str_token = "ABCDEFGHIJ";
                $msg = str_replace($str_token, $msg, $msgEmail);

                $msg  = "<font color='red'><strong><span class='style3'>";
                $msg .= "Se a janela do boleto nao abrir automaticamente, ou se tiver algum bloqueador de popup, <br> desabilite-o e ";
                $msg .= "<a href='#' onclick=\"fcnJanelaBoleto('".$token."'); return false;\">clique aqui</a> para abrir o boleto novamente!!";
                $msg .= "<script>fcnJanelaBoleto('".$token."');</script>";
                $msg .= "</span></strong></font>";
                echo $msg;
                exit;
        }
}

?>
        