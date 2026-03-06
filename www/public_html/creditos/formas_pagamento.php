<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
require_once "../../includes/constantes.php";
require_once DIR_CLASS . "pdv/controller/FormasPagtoController.class.php";

$controller = new FormasPagtoController;

$banner = $controller->getBanner();

require_once "includes/header.php";

require_once DIR_INCS . "config.MeiosPagamentos.php";

$token_csrf = $_POST['token_csrf'] ?? null;
$iforma = $_POST['iforma'] ?? null;
$idu = $_POST['idu'] ?? null;
$sno = $_POST['sno'] ?? null;
$btSubmit = $_POST['btSubmit'] ?? null;
$produtos_valor = $_POST['produtos_valor'] ?? null;
$email = $_POST['email'] ?? null;
$produtos = $_POST['produtos'] ?? null;


if ($_SESSION["token_csrf"] != $_POST["token_csrf"]) {
    header("location: /creditos/index.php");
}

$_SESSION["token_csrf"] = bin2hex(random_bytes(32));

//Definindo valor Default no caso do include estar conrrompido
if (!defined('PAGAMENTO_BRADESCO')) {
    //Definindo como ativado
    define('PAGAMENTO_BRADESCO', 1);
} // end if
if (!defined('PAGAMENTO_BANCO_BRASIL')) {
    //Definindo como ativado
    define('PAGAMENTO_BANCO_BRASIL', 1);
} // end if
if (!defined('PAGAMENTO_ITAU')) {
    //Definindo como ativado
    define('PAGAMENTO_ITAU', 1);
} // end if
if (!defined('PAGAMENTO_BOLETO')) {
    //Definindo como ativado
    define('PAGAMENTO_BOLETO', 1);
} // end if
if (!defined('PAGAMENTO_EPREPAG_CASH')) {
    //Definindo como ativado
    define('PAGAMENTO_EPREPAG_CASH', 1);
} // end if
if (!defined('PAGAMENTO_CIELO')) {
    //Definindo como ativado
    define('PAGAMENTO_CIELO', 1);
} // end if
if (!defined('PAGAMENTO_PIX')) {
    //Definindo como ativado
    define('PAGAMENTO_PIX', 1);
} // end if

if (isset($iforma)) {
    $pagto = $iforma;
    $_SESSION['dist_pagamento.pagto'] = $pagto;
} else {
    $iforma = false;
}

if (!isset($controller->usuarios) || !($controller->usuarios instanceof UsuarioGames)) {
    die("Erro crítico: O objeto de usuário não foi instanciado corretamente no controller.");
}

$usr = $controller->usuarios;

$valor_minimo = $usr->b_IsLogin_pagamento_minimo_1_real() ? 1 : $GLOBALS['RISCO_LANS_PRE_VALOR_MIN'];

$nivel = $usr->getNivelPagamento();

switch ($nivel) {
    case 5:
        $prefixo = 'RISCO_LANS_PRE_PLATINUM_';
        break;
    case 4:
        $prefixo = 'RISCO_LANS_PRE_GOLD_';
        break;
    case 3:
        $prefixo = 'RISCO_LANS_PRE_BLACK_';
        break;
    case 2:
        $prefixo = 'RISCO_LANS_PRE_MASTER_';
        break;
    case 1:
        $prefixo = 'RISCO_LANS_PRE_VIP_';
        break;
    case 0:
    default:
        $prefixo = 'RISCO_LANS_PRE_';
        break; 
}

$valor_maximo = $GLOBALS[$prefixo . 'VALOR_MAX'];

if ($GLOBALS['TIPO_LIMITE'] == 0) {
    $valor_total_diario = $GLOBALS[$prefixo . 'TOTAL_DIARIO'];
    $n_total_diario     = $GLOBALS[$prefixo . 'PAGAMENTOS_DIARIO'];
} else {
    $valor_total_semanal = $GLOBALS[$prefixo . 'TOTAL_SEMANAL'];
    $n_total_semanal     = $GLOBALS[$prefixo . 'PAGAMENTOS_SEMANAL'];
}

if (!isset($produtos_valor))
    $produtos_valor = $produtos;

$produtos_valor = preg_replace('/[^0-9]/', '', $produtos_valor) * 1;

//Produtos
$msg = "";
if ($produtos_valor < $valor_minimo || $produtos_valor > $valor_maximo) {
    $msg = "Error: Valor mínimo permitido é R$" . number_format($valor_minimo, 2, ',', '.') . ", tente novamente";
}

$cobraTaxa = false;

if ($produtos_valor < $RISCO_LANS_PRE_VALOR_MIN_PARA_TAXA)
    $cobraTaxa = true;

$pagto = isset($_SESSION['dist_pagamento.pagto']) ? $_SESSION['dist_pagamento.pagto'] : false;

$btSubmit = isset($_REQUEST['btSubmit']) ? $_REQUEST['btSubmit'] : false;

if ($btSubmit || $iforma) {

    $msg = "";

    //Valida opcao de pagamento
    if ($msg == "") {
        if (!$pagto || $pagto == "" || (strlen($pagto) != 1))
            $msg = "Selecione a forma de pagamento.";
    }

    //Validacao formas de pagamento		
    if ($msg == "") {
        if (!in_array($pagto, $FORMAS_PAGAMENTO))
            $msg = "Forma de pagamento inválida.";
    }

    //Adiciona dados no session
    if ($msg == "") {
        $_SESSION['dist_pagamento.pagto'] = $pagto;
        $_SESSION['dist_pagamento.total'] = $produtos_valor;
        $_SESSION['dist_pagamento.taxa'] = getTaxaPagtoOnline($iforma, $produtos_valor);

        unset($_SESSION['pagamento.numorder']);
        unset($_SESSION['dist_pagamento.pagto_ja_fiz']);
        unset($_SESSION['dist_pagamento.parcelas.REDECARD_MASTERCARD']);
        unset($_SESSION['dist_pagamento.parcelas.REDECARD_DINERS']);

        //redireciona
        $strRedirect = "/creditos/pagamento/finaliza_vendaExLH_pgtoonline.php";
        Util::redirect($strRedirect);
        //redirect($strRedirect);

    } else {
        echo "<hr>??? " . $msg . " ???<hr>";
        die;
    }
}

$b_nova_forma_pagamento = false;
if ($controller->usuarios->b_IsLogin_pagamento()) {
    $b_nova_forma_pagamento = true;
    // Obtem o valor total deste pedido
    $total_carrinho = $produtos_valor;
    // ==========================================================================================
    // Faz validação de vendas totais, repetir em finaliza_vendas.php, antes de aceitar o pedido

    // Testa que só tem produtos Habbo e GPotato no carrinho
    //$b_IsProdutoOK = bCarrinho_ApenasProdutosOK();	// não usa mais

    // Testa que usuário comprou no máximo 10 vezes nas últimas 24 horas


    // Calcula o total nas últimas 24 horas para pagamentos Online 

    if ($GLOBALS['TIPO_LIMITE'] == 0) {
        $qtde_last_dayOK = getNVendasLH($controller->usuarios->getId());

        $total_diario = getVendasLHTotalDiarioOnline($controller->usuarios->getId());

        // Calcula o total nas últimas 24 horas de valores de boletos gerados
        $total_diario_boletos = getVendasLHTotalDiarioBoletos($controller->usuarios->getId());

        $platinum = $controller->usuarios->b_IsLogin_pagamento_platinum();

        if ($platinum) {
            $b_TentativasDiariasOK = true;
            $b_LimiteDiarioOK = true;
            $b_LimiteDiarioOKBoleto = true;

            $b_libera_BancodoBrasil = $b_LimiteDiarioOK && $b_TentativasDiariasOK;

            // Libera pagamento Online Banco Itaú
            $b_libera_BancoItau = $b_LimiteDiarioOK && $b_TentativasDiariasOK;

            // Libera Bradesco apenas se limite diario não ultrapassado //produtos (Habbo e GPotato) e tem até 5 compras nas últimas 24 horas
            $b_libera_Bradesco = $b_LimiteDiarioOK && $b_TentativasDiariasOK;

            // Libera Boleto apenas se o valor da venda não ultrapassa o limite por venda
            $b_libera_Boleto = $b_LimiteDiarioOKBoleto && $b_TentativasDiariasOK;

            $b_libera_Pix = $b_LimiteDiarioOK && $b_TentativasDiariasOK;
        } else {
            $b_TentativasDiariasOK = ($qtde_last_dayOK <= $n_total_diario);
            $b_LimiteDiarioOK = (($total_carrinho + $total_diario) <= $valor_total_diario);

            $b_LimiteDiarioOKBoleto = (($total_carrinho + $total_diario_boletos) <= $valor_total_diario);

            // Libera pagamento Online Banco do Brasil
            $b_libera_BancodoBrasil = $b_LimiteDiarioOK && $b_TentativasDiariasOK; // && $controller->usuarios->b_IsLogin_pagamento_bancodobrasil();

            // Libera pagamento Online Banco Itaú
            $b_libera_BancoItau = $b_LimiteDiarioOK && $b_TentativasDiariasOK;

            // Libera Bradesco apenas se limite diario não ultrapassado //produtos (Habbo e GPotato) e tem até 5 compras nas últimas 24 horas
            $b_libera_Bradesco = $b_LimiteDiarioOK && $b_TentativasDiariasOK;    //$b_IsProdutoOK && 

            // Libera Boleto apenas se o valor da venda não ultrapassa o limite por venda
            $b_libera_Boleto = $b_LimiteDiarioOKBoleto && $b_TentativasDiariasOK;

            $b_libera_Pix = $b_LimiteDiarioOK && $b_TentativasDiariasOK;
        }



        $msg_bloqueia_Bradesco = (!$b_libera_Bradesco) ? ((!$b_LimiteDiarioOK) ? "<div class='alert alert-danger' role='alert'>Sua compra de <b>R$" . number_format($total_carrinho, 2, ',', '.') . "</b> ultrapassa o limite para compras on-line. <br> Você ainda tem disponível o valor de <b>R$" . number_format(($valor_total_diario - $total_diario), 2, ',', '.') . "</b> para hoje</div>." : ((!$b_TentativasDiariasOK) ? "<div class='alert alert-danger' role='alert'>Número de pagamentos online (" . $qtde_last_dayOK . ") ultrapassa o limite nas ultimas 24 horas.</div>" : "")) : "";

        $msg_bloqueia_BancodoBrasil = (!$b_libera_BancodoBrasil) ? ((!$b_LimiteDiarioOK) ? "<div class='alert alert-danger' role='alert'>Sua compra de <b>R$" . number_format($total_carrinho, 2, ',', '.') . "</b> ultrapassa o limite para compras on-line. <br> Você ainda tem disponível o valor de <b>R$" . number_format(($valor_total_diario - $total_diario), 2, ',', '.') . "</b> para hoje.</div>" : ((!$b_TentativasDiariasOK) ? "<div class='alert alert-danger' role='alert'>Número de pagamentos online (" . $qtde_last_dayOK . ") ultrapassa o limite nas ultimas 24 horas.</div>" : "")) : "";

        $msg_bloqueia_BancoItau = (!$b_libera_BancoItau) ? ((!$b_LimiteDiarioOK) ? "<div class='alert alert-danger' role='alert'>Sua compra de <b>R$" . number_format($total_carrinho, 2, ',', '.') . "</b> ultrapassa o limite para compras on-line. <br> Você ainda tem disponível o valor de <b>R$" . number_format(($valor_total_diario - $total_diario), 2, ',', '.') . "</b> para hoje.</div>" : ((!$b_TentativasDiariasOK) ? "<div class='alert alert-danger' role='alert'>Número de pagamentos online (" . $qtde_last_dayOK . ") ultrapassa o limite nas ultimas 24 horas.</div>" : "")) : "";

        $msg_bloqueia_Boleto = (!$b_libera_Boleto) ? ((!$b_LimiteDiarioOKBoleto) ? "<div class='alert alert-danger' role='alert'>Sua compra de <b>R$" . number_format($total_carrinho, 2, ',', '.') . "</b> ultrapassa o limite diário de compras com boleto. <br> Você ainda tem disponível o valor de <b>R$" . number_format(($valor_total_diario - $total_diario_boletos), 2, ",", ".") . "</b> para hoje.</div>" : ((!$b_TentativasDiariasOK) ? "<div class='alert alert-danger' role='alert'>Número de pagamentos online (" . $qtde_last_dayOK . ") ultrapassa o limite nas ultimas 24 horas.</div>" : "")) : "";

        $msg_bloqueia_Pix = (!$b_libera_Pix) ? ((!$b_LimiteDiarioOK) ? "<div class='alert alert-danger' role='alert'>Sua compra de <b>R$" . number_format($total_carrinho, 2, ',', '.') . "</b> ultrapassa o limite para compras on-line. <br> Você ainda tem disponível o valor de <b>R$" . number_format(($valor_total_diario - $total_diario), 2, ',', '.') . "</b> para hoje.</div>" : ((!$b_TentativasDiariasOK) ? "<div class='alert alert-danger' role='alert'>Número de pagamentos online (" . $qtde_last_dayOK . ") ultrapassa o limite nas ultimas 24 horas.</div>" : "")) : "";

        if (!$b_TentativasDiariasOK || !$b_LimiteDiarioOK) {
            $msg_block = "Pagamento Online BLOQUEADO (LH Pré) ******  ";

            $smsg_bloqueio =
                "	Usuário: ID: " . $controller->usuarios->getId() . ",\n" .
                "	solicitado: " . number_format(($total_carrinho + $total_diario), 2, ',', '.') . " de " . number_format($valor_maximo, 2, ',', '.') . "\n";
            if (($total_carrinho + $total_diario) <= (2 * $valor_maximo)) {
                $smsg_bloqueio .= "	Safe (<=2*LIMITE_MAX)\n";
            } else {
                $smsg_bloqueio .= "	NotSafe (>2*LIMITE_MAX)\n";
            }
            gravaLog_PagtoOnlineUsuariosBloqueadosParaVIP($smsg_bloqueio);
        } else {
            $msg_block = "Pagamento Online (LH Pré) PERMITIDO ++++++  ";
        }
        $mensagem = "=====================================================================================\n" .
            "$msg_block (" . date("Y-m-d H:i:s") . ")\n" .
            "  Usuário: ID: " . $controller->usuarios->getId() . ", Nome: " . $controller->usuarios->getNome() . ", Email: " . $controller->usuarios->getEmail() . ",\n" .
            "  qtde_last_dayOK: " . $qtde_last_dayOK . "\n" .
            "  total_diario: " . number_format($total_diario, 2, ',', '.') . "\n" .
            "  total_carrinho+total_diario: " . number_format(($total_carrinho + $total_diario), 2, ',', '.') . "\n" .
            "  b_TentativasDiariasOK: " . ($b_TentativasDiariasOK ? "OK" : "nope") . "\n" .
            "  b_LimiteDiarioOK: " . ($b_LimiteDiarioOK ? "OK" : "nope") . "\n" .
            "  \n" .
            "  b_libera_BancodoBrasil: " . ($b_libera_BancodoBrasil ? "OK" : "nope") . "\n" .
            "  b_libera_Bradesco: " . ($b_libera_Bradesco ? "OK" : "nope") . "\n" .
            "  RISCO_LANS_PRE_PAGAMENTOS_DIARIO: " . $n_total_diario . "\n" .
            "  RISCO_LANS_PRE_TOTAL_DIARIO: " . number_format($valor_total_diario, 2, ',', '.') . "\n" .
            "\n";
        gravaLog_BloqueioPagtoOnline($mensagem);
    } else {
        $qtde_last_weekOK = getNVendasSemanaisLH($controller->usuarios->getId());

        $total_semanal = getVendasLHTotalSemanalOnline($controller->usuarios->getId());
        // Calcula o total nas últimas 24 horas de valores de boletos gerados
        $total_semanal_boletos = getVendasLHTotalSemanalBoletos($controller->usuarios->getId());

        $b_TentativasSemanaisOK = ($qtde_last_weekOK <= $n_total_semanal);
        $b_LimiteSemanalOK = (($total_carrinho + $total_semanal) <= $valor_total_semanal);

        $b_LimiteSemanalOKBoleto = (($total_carrinho + $total_semanal_boletos) <= $valor_total_semanal);

        // Libera pagamento Online Banco do Brasil
        $b_libera_BancodoBrasil = $b_LimiteSemanalOK && $b_TentativasSemanaisOK; // && $controller->usuarios->b_IsLogin_pagamento_bancodobrasil();

        // Libera pagamento Online Banco Itaú
        $b_libera_BancoItau = $b_LimiteSemanalOK && $b_TentativasSemanaisOK;

        // Libera Bradesco apenas se limite diario não ultrapassado //produtos (Habbo e GPotato) e tem até 5 compras nas últimas 24 horas
        $b_libera_Bradesco = $b_LimiteSemanalOK && $b_TentativasSemanaisOK;    //$b_IsProdutoOK && 

        // Libera Boleto apenas se o valor da venda não ultrapassa o limite por venda
        $b_libera_Boleto = $b_LimiteSemanalOKBoleto && $b_TentativasSemanaisOK;

        $b_libera_Pix = $b_LimiteSemanalOK && $b_TentativasSemanaisOK;

        $msg_bloqueia_Bradesco = (!$b_libera_Bradesco) ? ((!$b_LimiteSemanalOK) ? "<div class='alert alert-danger' role='alert'>Sua compra de <b>R$" . number_format($total_carrinho, 2, ',', '.') . "</b> ultrapassa o limite diário de compras on-line. <br> Você ainda tem disponível o valor de <b>R$" . number_format(($valor_total_semanal - $total_semanal), 2, ',', '.') . "</b> para hoje</div>." : ((!$b_TentativasSemanaisOK) ? "<div class='alert alert-danger' role='alert'>Número de pagamentos online (" . $qtde_last_weekOK . ") ultrapassa o limite diário.</div>" : "")) : "";

        $msg_bloqueia_BancodoBrasil = (!$b_libera_BancodoBrasil) ? ((!$b_LimiteSemanalOK) ? "<div class='alert alert-danger' role='alert'>Sua compra de <b>R$" . number_format($total_carrinho, 2, ',', '.') . "</b> ultrapassa o limite diário de compras on-line. <br> Você ainda tem disponível o valor de <b>R$" . number_format(($valor_total_semanal - $total_semanal), 2, ',', '.') . "</b> para hoje.</div>" : ((!$b_TentativasSemanaisOK) ? "<div class='alert alert-danger' role='alert'>Número de pagamentos online (" . $qtde_last_weekOK . ") ultrapassa o limite diário.</div>" : "")) : "";

        $msg_bloqueia_BancoItau = (!$b_libera_BancoItau) ? ((!$b_LimiteSemanalOK) ? "<div class='alert alert-danger' role='alert'>Sua compra de <b>R$" . number_format($total_carrinho, 2, ',', '.') . "</b> ultrapassa o limite diário de compras on-line. <br> Você ainda tem disponível o valor de <b>R$" . number_format(($valor_total_semanal - $total_semanal), 2, ',', '.') . "</b> para hoje.</div>" : ((!$b_TentativasSemanaisOK) ? "<div class='alert alert-danger' role='alert'>Número de pagamentos online (" . $qtde_last_weekOK . ") ultrapassa o limite diário.</div>" : "")) : "";

        $msg_bloqueia_Boleto = (!$b_libera_Boleto) ? ((!$b_LimiteSemanalOKBoleto) ? "<div class='alert alert-danger' role='alert'>Sua compra de <b>R$" . number_format($total_carrinho, 2, ',', '.') . "</b> ultrapassa o limite diário de compras com boleto. <br> Você ainda tem disponível o valor de <b>R$" . number_format(($valor_total_semanal - $total_semanal_boletos), 2, ",", ".") . "</b> para hoje.</div>" : ((!$b_TentativasSemanaisOK) ? "<div class='alert alert-danger' role='alert'>Número de pagamentos online (" . $qtde_last_weekOK . ") ultrapassa o limite diário.</div>" : "")) : "";

        $msg_bloqueia_Pix = (!$b_libera_Pix) ? ((!$b_LimiteSemanalOK) ? "<div class='alert alert-danger' role='alert'>Sua compra de <b>R$" . number_format($total_carrinho, 2, ',', '.') . "</b> ultrapassa o limite diário de compras on-line. <br> Você ainda tem disponível o valor de <b>R$" . number_format(($valor_total_semanal - $total_semanal), 2, ',', '.') . "</b> para hoje.</div>" : ((!$b_TentativasSemanaisOK) ? "<div class='alert alert-danger' role='alert'>Número de pagamentos online (" . $qtde_last_weekOK . ") ultrapassa o limite diário.</div>" : "")) : "";

        if (!$b_TentativasSemanaisOK || !$b_LimiteSemanalOK) {
            $msg_block = "Pagamento Online BLOQUEADO (LH Pré) ******  ";

            $smsg_bloqueio =
                "	Usuário: ID: " . $controller->usuarios->getId() . ",\n" .
                "	solicitado: " . number_format(($total_carrinho + $total_semanal), 2, ',', '.') . " de " . number_format($valor_maximo, 2, ',', '.') . "\n";
            if (($total_carrinho + $total_semanal) <= (2 * $valor_maximo)) {
                $smsg_bloqueio .= "	Safe (<=2*LIMITE_MAX)\n";
            } else {
                $smsg_bloqueio .= "	NotSafe (>2*LIMITE_MAX)\n";
            }
            gravaLog_PagtoOnlineUsuariosBloqueadosParaVIP($smsg_bloqueio);
        } else {
            $msg_block = "Pagamento Online (LH Pré) PERMITIDO ++++++  ";
        }

        $mensagem = "=====================================================================================\n" .
            "$msg_block (" . date("Y-m-d H:i:s") . ")\n" .
            "  Usuário: ID: " . $controller->usuarios->getId() . ",\n" .
            "  qtde_last_weekOK: " . $qtde_last_weekOK . "\n" .
            "  total_semanal: " . number_format($total_semanal, 2, ',', '.') . "\n" .
            "  total_carrinho+total_semanal: " . number_format(($total_carrinho + $total_semanal), 2, ',', '.') . "\n" .
            "  b_TentativasSemanaisOK: " . ($b_TentativasSemanaisOK ? "OK" : "nope") . "\n" .
            "  b_LimiteSemanalOK: " . ($b_LimiteSemanalOK ? "OK" : "nope") . "\n" .
            "  \n" .
            "  b_libera_BancodoBrasil: " . ($b_libera_BancodoBrasil ? "OK" : "nope") . "\n" .
            "  b_libera_Bradesco: " . ($b_libera_Bradesco ? "OK" : "nope") . "\n" .
            "  b_libera_BancoItau: " . ($b_libera_BancoItau ? "OK" : "nope") . "\n" .
            "  b_libera_Pix: " . ($b_libera_Pix ? "OK" : "nope") . "\n" .
            "  RISCO_LANS_PRE_PAGAMENTOS_SEMANAL: " . $n_total_semanal . "\n" .
            "  RISCO_LANS_PRE_TOTAL_SEMANAL: " . number_format($valor_total_semanal, 2, ',', '.') . "\n" .
            "\n";
        gravaLog_BloqueioPagtoOnline($mensagem);
    }



    // finaliza validações
    // ==========================================================================================
}


if ($b_nova_forma_pagamento) {


?>

    <script type="text/javascript" src="/js/ajax.js"></script>
    <script language="Javascript">
        <?php
        if (is_object($controller->usuarios)) {
            if ($controller->usuarios->b_IsLogin_pagamento()) {

        ?>

                function save_shipping(iforma, id, sno) {

                    document.form1.iforma.value = iforma;
                    document.form1.idu.value = id;
                    document.form1.sno.value = sno;
                    document.form1.btSubmit.value = "Continuar";
                    document.form1.submit();
                }
        <?php
            }
        }
        ?>

        function finalizaVenda() {
            document.getElementsByClassName("imgSubmit")[0].style.pointerEvents = "none";
            var produtos_valor = document.getElementById("produtos_valor").value;
            document.getElementById("produtos").value = produtos_valor;

            //        disableElementId('btnSubmit', true);
            document.getElementById('tab_content').innerHTML = "<font color='blue'>Aguarde alguns instantes, estamos processando seu pedido...</font>\n";
            var params = GetFormFields('form1');
            AJAXRequest('/ajax/pdv/finaliza_vendaExLH.php', params, 'FillHTML', 'tab_content', false);
        }

        function fcnJanelaBoleto(token) {

            $(".btnPgto").each(function() {
                $(this).attr("onclick", "");
            });


            <?php
            if ($controller->usuarios->b_Is_Boleto_Itau()) {
            ?>
                window.open('/SICOB/BoletoWebItauCommerceLH.php?token=' + token, '', '');
            <?php
            } elseif ($controller->usuarios->b_Is_Boleto_Banespa()) {
            ?>
                window.open('/SICOB/BoletoWebBanespaCommerceLH.php?token=' + token, '', '');
            <?php
            } elseif (BANCO_BOLETO == "asaas" || $usuarioGames->getId() == 17371) {
            ?>
                window.open(token, '', '');
            <?php
            } elseif (BANCO_BOLETO == "bradesco") {
            ?>
                window.open('/boletos/pdv/boleto_bradesco.php?token=' + token, '', '');
            <?php
            }
            ?>
            $(".formaspagamento").fadeOut();
        }
    </script>

    <form id="form1" name="form1" method="POST">
        <input type="hidden" name="token_csrf" value="<?= $_SESSION["token_csrf"] ?>">
        <input type="hidden" name="iforma" value="0">
        <input type="hidden" name="idu" value="0">
        <input type="hidden" name="sno" value="0">
        <input type="hidden" name="btSubmit" value="Continuar">
        <input name="produtos_valor" id="produtos_valor" type="hidden" value="<?php echo $produtos_valor; ?>" />
        <div class="container txt-azul-claro bg-branco">
            <div class="row">
                <div class="col-md-10">
                    <div class="row">
                        <div class="col-md-12 espacamento">
                            <strong>ADICIONAR SALDO</strong>
                        </div>
                    </div>
                    <div class="row txt-cinza">
                        <div class="col-md-12 espacamento">
                            <strong>Valor selecionado: <span class="txt-verde">R$
                                    <?php echo number_format($produtos_valor, 2, ',', '.'); ?></span></strong>
                        </div>
                    </div>
                    <div class="row txt-cinza formaspagamento borda-top-azul right20">
                        <div class="col-md-12 top10">
                            <p class="txt-azul-claro"><strong>Escolha o meio de pagamento</strong></p>
                        </div>
                    </div>
                    <div class="row formaspagamento">
                        <?php

                        if (PAGAMENTO_PIX || in_array($controller->usuarios->getId(), [17371, 19430, 17201, 12667, 13715])) {
                            if ($b_libera_Pix) {
                        ?>
                                <div class="col-md-2 espacamento-colunas-formas-pagamento borda-colunas-formas-pagamento">
                                    <div class="col-md-12 text-center">
                                        <div class="row borda-linhas-formas-pagamento">
                                            <img src="/imagens/pag/pagto_forma_pix.gif">
                                        </div>
                                        <div class="row top10">
                                            <!--<p class="txt-vermelho fontsize-p">ATENÇAO pix itaú indisponível<br> <strong>faça o pagamento pix por outro banco</strong></p>-->
                                            <img class="c-pointer btnPgto" src="/imagens/pag/pagto_forma_transferencia_1.gif"
                                                name="btn_5" class="c-pointer btnPgto"
                                                onMouseOver="document.btn_5.src='/imagens/pag/pagto_forma_transferencia_2.gif'"
                                                onMouseOut="document.btn_5.src='/imagens/pag/pagto_forma_transferencia_1.gif'"
                                                title="Pagamento Pix" onclick="save_shipping(
                                            '<?php echo $FORMAS_PAGAMENTO['PAGAMENTO_PIX'] ?>', 
                                            <?php echo (($controller->usuarios->getId() > 0) ? $controller->usuarios->getId() : "0") ?>, 
                                            '<?php echo $controller->usuarios->getNome() ?>')">
                                        </div>
                                        <div class="row">
                                            <?php
                                            if ($cobraTaxa) {
                                            ?>
                                                <p class="txt-cinza fontsize-pp bottom0"><strong>Taxa de serviço:
                                                        R$<?php echo number_format($PAGAMENTO_PIX_TAXA, 2, ',', '.') ?></strong></p>
                                            <?php
                                            }
                                            ?>
                                            <p class="txt-verde fontsize-p">Entrega Imediata</p>
                                            <span style='visibility:hidden'><input type="radio" name="pagto"
                                                    value="<?php echo $FORMAS_PAGAMENTO['PAGAMENTO_PIX'] ?>" <?php if ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_PIX'])
                                                                                                                    echo " checked"; ?>></span>
                                        </div>
                                    </div>
                                </div>
                            <?php
                            } else {
                            ?>
                                <div class="col-md-2 espacamento-colunas-formas-pagamento borda-colunas-formas-pagamento">
                                    <div class="col-md-12 text-center">
                                        <div class="row borda-linhas-formas-pagamento">
                                            <?php echo $msg_bloqueia_Pix; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php

                            }
                        }

                        if ($controller->usuarios->b_IsLogin_pagamento_boleto() && PAGAMENTO_BOLETO) {
                            if ($b_libera_Boleto) {
                            ?>
                                <div class="col-md-2 espacamento-colunas-formas-pagamento borda-colunas-formas-pagamento">
                                    <div class="col-md-12 text-center">
                                        <div class="row borda-linhas-formas-pagamento">
                                            <img src="/imagens/pag/pagto_boleto.gif">
                                        </div>
                                        <div class="row top10">
                                            <img name="btn_12" src="/imagens/pag/pagto_forma_boleto_1.gif"
                                                class="c-pointer btnPgto imgSubmit"
                                                onMouseOver="document.btn_12.src='/imagens/pag/pagto_forma_boleto_2.gif'"
                                                onMouseOut="document.btn_12.src='/imagens/pag/pagto_forma_boleto_1.gif'"
                                                title="Clique aqui para emitir o Boleto Bancário" onclick="finalizaVenda();">
                                            <input type="hidden" name="email" id="email"
                                                value="<?php echo $controller->usuarios->getEmail() ?>">
                                            <input type="hidden" name="produtos" id="produtos" value="">
                                        </div>
                                        <div class="row">
                                            <?php
                                            if ($cobraTaxa) {
                                            ?>
                                                <p class="txt-cinza fontsize-pp bottom0"><strong>Taxa de serviço: R$<?php
                                                                                                                    if ($controller->usuarios->b_Is_Boleto_Itau()) {
                                                                                                                        echo number_format($GLOBALS['BOLETO_MONEY_ITAU_TAXA_CUSTO_BANCO_NOVA'], 2, ',', '.');
                                                                                                                    } elseif ($controller->usuarios->b_Is_Boleto_Banespa()) {
                                                                                                                        echo number_format($GLOBALS['BOLETO_BANESPA_TAXA_ADICIONAL'], 2, ',', '.');
                                                                                                                    } else {
                                                                                                                        echo number_format($GLOBALS['BOLETO_TAXA_ADICIONAL_BRADESCO'], 2, ',', '.');
                                                                                                                    }
                                                                                                                    ?></strong></p>
                                            <?php
                                            }
                                            ?>
                                            <p class="txt-verde fontsize-p">Entrega em até 2 dias úteis.</p>
                                        </div>
                                    </div>
                                </div>
                            <?php
                            } else {
                            ?>
                                <div class="col-md-2 espacamento-colunas-formas-pagamento borda-colunas-formas-pagamento">
                                    <div class="col-md-12 text-center">
                                        <div class="row borda-linhas-formas-pagamento">
                                            <?php echo $msg_bloqueia_Boleto; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php
                            }
                        }
                        /*
                         *  ##############################################
                         *  ########FORMAS DE PAGAMENTO DO BRADESCO######
                         * ##############################################
                         * 
                         */


                        if ($controller->usuarios->b_IsLogin_pagamento_bancodobradesco() && PAGAMENTO_BRADESCO && false) {
                            if ($b_libera_Bradesco) {
                            ?>
                                <div class="col-md-2 espacamento-colunas-formas-pagamento">
                                    <div class="col-md-12 text-center">
                                        <div class="row borda-linhas-formas-pagamento">
                                            <img src="/imagens/pag/pagto_forma_debito_visa1.gif">
                                        </div>
                                        <div class="row top10">
                                            <img src="/imagens/pag/pagto_forma_debito_visa1.gif" onclick="save_shipping(
                                        <?php echo $FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_DEBITO'] ?>, 
                                        <?php echo (($controller->usuarios->getId() > 0) ? $controller->usuarios->getId() : "0") ?>, 
                                        '<?php echo $controller->usuarios->getNome() ?>'
                                    )">>
                                        </div>
                                        <div class="row">
                                            <p class="txt-cinza fontsize-pp bottom0"><strong>Sem taxa de serviço</strong></p>
                                            <p class=" fontsize-p">Entrega em até 90 minutos</p>
                                            <span style='visibility:hidden'><input type="radio" name="pagto"
                                                    value="<?php echo $FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_DEBITO'] ?>" <?php if ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_DEBITO'])
                                                                                                                                    echo " checked"; ?>></span>
                                        </div>
                                    </div>
                                </div>
                            <?php
                            } else {
                            ?>
                                <div class="col-md-2 espacamento-colunas-formas-pagamento borda-colunas-formas-pagamento">
                                    <div class="col-md-12 text-center">
                                        <div class="row borda-linhas-formas-pagamento">
                                            <?php echo $msg_bloqueia_Bradesco; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php
                            }
                        }

                        if ($controller->usuarios->b_IsLogin_pagamento_bancodobradesco() && PAGAMENTO_BRADESCO) {
                            if ($b_libera_Bradesco) {
                            ?>
                                <div class="col-md-2 espacamento-colunas-formas-pagamento borda-colunas-formas-pagamento">
                                    <div class="col-md-12 text-center">
                                        <div class="row borda-linhas-formas-pagamento">
                                            <img src="/imagens/pag/pagto_bradesco.gif">
                                        </div>
                                        <div class="row top10">
                                            <img class="c-pointer btnPgto" src="/imagens/pag/pagto_forma_transferencia_1.gif"
                                                name="btn_5" class="c-pointer btnPgto"
                                                onMouseOver="document.btn_5.src='/imagens/pag/pagto_forma_transferencia_2.gif'"
                                                onMouseOut="document.btn_5.src='/imagens/pag/pagto_forma_transferencia_1.gif'"
                                                title="Bradesco pagamento (Transferência entre contas)" onclick="save_shipping(
                                            <?php echo $FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO'] ?>, 
                                            <?php echo (($controller->usuarios->getId() > 0) ? $controller->usuarios->getId() : "0") ?>, 
                                            '<?php echo $controller->usuarios->getNome() ?>')">
                                        </div>
                                        <div class="row">
                                            <?php
                                            if ($cobraTaxa) {
                                            ?>
                                                <p class="txt-cinza fontsize-pp bottom0"><strong>Taxa de serviço:
                                                        R$<?php echo number_format($BRADESCO_TRANSFERENCIA_ENTRE_CONTAS_TAXA_ADICIONAL, 2, ',', '.') ?></strong>
                                                </p>
                                            <?php
                                            }
                                            ?>
                                            <p class="txt-verde fontsize-p">Entrega em até 90 minutos</p>
                                            <span style='visibility:hidden'><input type="radio" name="pagto"
                                                    value="<?php echo $FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO'] ?>"
                                                    <?php if ($pagto == $FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO'])
                                                        echo " checked"; ?>></span>
                                        </div>
                                    </div>
                                </div>
                            <?php
                            } else {
                            ?>
                                <div class="col-md-2 espacamento-colunas-formas-pagamento borda-colunas-formas-pagamento">
                                    <div class="col-md-12 text-center">
                                        <div class="row borda-linhas-formas-pagamento">
                                            <?php echo $msg_bloqueia_Bradesco; ?>
                                        </div>
                                    </div>
                                </div>
                        <?php

                            }
                        }
                        ?>
                        <!-- -->
                        <?php /*
       if(isset($b_libera_Deposito))
       { 
?>                
               <div class="col-md-2 borda-colunas-formas-pagamento espacamento-colunas-formas-pagamento">
                   <div class="col-md-12 text-center">
                       <div class="row borda-linhas-formas-pagamento">
                           <img src="/prepag2/pag/images/pagto_bradesco.gif">
                       </div>
                       <div class="row top10">
                           <img src="/prepag2/pag/images/pagto_forma_deposito1.gif"  title="Bradesco pagamento (Transferência entre contas)"
                               onclick="save_shipping(<?php echo $FORMAS_PAGAMENTO['DEP_DOC_TRANSF']; ?>, 
                                                      <?php echo (($controller->usuarios->getId()>0)?$controller->usuarios->getId():"0") ?>, 
                                                     '<?php echo $controller->usuarios->getNome() ?>')">
                       </div>
                       <div class="row top10">
                           <p class="txt-cinza fontsize-pp bottom0"><strong>Depósito, DOC, Transferência offline</strong></p>
                           <p class="txt-cinza fontsize-pp bottom0"><strong>Ag. 2062-1</strong></p>
                           <p class="txt-cinza fontsize-pp bottom0"><strong>Cc. 4707-4</strong></p>
                           <p class="txt-cinza fontsize-pp"><input type="checkbox" name="pagto_ja_fiz" value="1" <?php if(isset($pagto_ja_fiz) && $pagto_ja_fiz == "1") echo "checked"; ?>> Já fiz meu pagamento e quero informar os dados</p>
                           <span style='visibility:hidden'><input type="radio" name="pagto" value="<?php echo $FORMAS_PAGAMENTO['DEP_DOC_TRANSF']?>" <?php if($pagto == $FORMAS_PAGAMENTO['DEP_DOC_TRANSF']) echo " checked"; ?>></span>
                       </div>
                   </div>
               </div>
<?php 
       } else 
       { 
           if(isset($msg_bloqueia_Deposito)) echo $msg_bloqueia_Deposito;
       }
       */
                        /*
                         *  ##############################################
                         *  ########FORMAS DE PAGAMENTO DO BB ###########
                         * ##############################################
                         * 
                         */

                        if ($controller->usuarios->b_IsLogin_pagamento_bancodobrasil() && PAGAMENTO_BANCO_BRASIL) {
                            if ($b_libera_BancodoBrasil) {
                        ?>
                                <div class="col-md-2 espacamento-colunas-formas-pagamento borda-colunas-formas-pagamento">
                                    <div class="col-md-12 text-center">
                                        <div class="row borda-linhas-formas-pagamento">
                                            <img src="/imagens/pag/pagto_bancodobrasil.gif">
                                        </div>
                                        <div class="row top10">
                                            <img name="btn_9" class="c-pointer btnPgto"
                                                onMouseOver="document.btn_9.src='/imagens/pag/pagto_forma_debito_2.gif'"
                                                onMouseOut="document.btn_9.src='/imagens/pag/pagto_forma_debito_1.gif'"
                                                src="/imagens/pag/pagto_forma_debito_1.gif"
                                                title="<?php echo $FORMAS_PAGAMENTO_DESCRICAO[$FORMAS_PAGAMENTO['PAGAMENTO_BB_DEBITO_SUA_CONTA']] ?>"
                                                onclick="save_shipping(<?php echo $FORMAS_PAGAMENTO['PAGAMENTO_BB_DEBITO_SUA_CONTA'] ?>, 
                                                        <?php echo (($controller->usuarios->getId() > 0) ? $controller->usuarios->getId() : "0") ?>, 
                                                       '<?php echo $controller->usuarios->getNome() ?>')">
                                        </div>
                                        <div class="row">
                                            <?php
                                            if ($cobraTaxa) {
                                            ?>
                                                <p class="txt-cinza fontsize-pp bottom0"><strong>Taxa de serviço:
                                                        R$<?php echo number_format($BANCO_DO_BRASIL_TAXA_DE_SERVICO, 2, ',', '.') ?></strong>
                                                </p>
                                            <?php
                                            }
                                            ?>
                                            <p class="txt-verde fontsize-p">Entrega em até 90 minutos</p>
                                            <span style='visibility:hidden'><input type="radio" name="pagto"
                                                    value="<?php echo $FORMAS_PAGAMENTO['PAGAMENTO_BB_DEBITO_SUA_CONTA'] ?>" <?php if ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_BB_DEBITO_SUA_CONTA'])
                                                                                                                                    echo " checked"; ?>></span>
                                        </div>
                                    </div>
                                </div>
                            <?php
                            } else {
                            ?>
                                <div class="col-md-2 espacamento-colunas-formas-pagamento borda-colunas-formas-pagamento">
                                    <div class="col-md-12 text-center">
                                        <div class="row borda-linhas-formas-pagamento">
                                            <?php echo $msg_bloqueia_BancodoBrasil; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php
                            }
                        } else {
                            echo "&nbsp;";
                        }
                        /*
                        if(isset($b_libera_Deposito))
                        {
                ?>
                                <div class="col-md-2 espacamento-colunas-formas-pagamento borda-colunas-formas-pagamento">
                                    <div class="col-md-12 text-center">
                                        <div class="row borda-linhas-formas-pagamento">
                                            <img src="/prepag2/pag/images/pagto_bancodobrasil.gif">
                                        </div>
                                        <div class="row">
                                            <img src="/prepag2/pag/images/pagto_forma_deposito1.gif" 
                                                name="btn_2bb" class="c-pointer btnPgto"
                                                onMouseOver="document.btn_2bb.src='/prepag2/pag/images/pagto_forma_deposito2.gif'" 
                                                onMouseOut="document.btn_2bb.src='/prepag2/pag/images/pagto_forma_deposito1.gif'" 
                                                width="110" height="35" border="0" title="BB pagamento depósito" onclick="save_shipping(<?php echo $FORMAS_PAGAMENTO['DEP_DOC_TRANSF'] ?>, <?php echo (($controller->usuarios->getId()>0)?$controller->usuarios->getId():"0") ?>, '<?php echo $controller->usuarios->getNome() ?>')"><br>
                                                <span class="style20">Depósito, DOC, Transferência offline</span><br>
                                                <span class="style20"><?php echo "Ag. 4328-1<br />Cc. 14.498-3" ?></span><br>
                                                <span class="style21"><input type="checkbox" name="pagto_ja_fiz" value="1" <?php if($pagto_ja_fiz == "1") echo "checked"; ?>> Já fiz meu pagamento e quero informar os dados<br>&nbsp;</span><br>
                                                <span style='visibility:hidden'><input type="radio" name="pagto" value="<?php echo $FORMAS_PAGAMENTO['DEP_DOC_TRANSF']?>" <?php if($pagto == $FORMAS_PAGAMENTO['DEP_DOC_TRANSF']) echo " checked"; ?>></span>
                                        </div>
                                    </div>
                                </div>
                                <br>
                                
                <?php 
                        } else 
                        {
                            echo isset($msg_bloqueia_Deposito) ? $msg_bloqueia_Deposito : "";
                        }*/

                        /*
                         *  ##############################################
                         *  ########FORMAS DE PAGAMENTO ITAU  ###########
                         * ##############################################
                         * 
                         */

                        /* ###########################################
                         * 
                         *  Bloqueando o tipo de pagamento Itau
                         *          
                         #############################################*/
                        if (false) {
                            $b_libera_BancoItau = false;
                            $msg_bloqueia_BancoItau = "<br><b><p class='txt-vermelho fontsize-p'>Indisponível por Problemas no Itaú</p></b>";
                        } //end if

                        if ($controller->usuarios->b_IsLogin_pagamento_bancoitau() && PAGAMENTO_ITAU) {
                            ?>
                            <div class="col-md-2 espacamento-colunas-formas-pagamento">
                                <div class="col-md-12 text-center">
                                    <div class="row borda-linhas-formas-pagamento">
                                        <img src="/imagens/pag/pagto_itau_shopline.gif">
                                    </div>
                                    <?php
                                    if ($b_libera_BancoItau) {
                                    ?>
                                        <div class="row top10">
                                            <img name="btn_10" src="/imagens/pag/pagto_forma_transferencia_1.gif"
                                                class="c-pointer btnPgto"
                                                onMouseOver="document.btn_10.src='/imagens/pag/pagto_forma_transferencia_2.gif'"
                                                onMouseOut="document.btn_10.src='/imagens/pag/pagto_forma_transferencia_1.gif'"
                                                title="<?php echo $FORMAS_PAGAMENTO_DESCRICAO[$FORMAS_PAGAMENTO['PAGAMENTO_BANCO_ITAU_ONLINE']] ?>"
                                                onclick="save_shipping('<?php echo $FORMAS_PAGAMENTO['PAGAMENTO_BANCO_ITAU_ONLINE'] ?>', <?php echo (($controller->usuarios->getId() > 0) ? $controller->usuarios->getId() : "0") ?>, '<?php echo $controller->usuarios->getNome() ?>')">
                                        </div>
                                        <div class="row">
                                            <?php
                                            if ($cobraTaxa) {
                                            ?>
                                                <p class="txt-cinza fontsize-pp bottom0"><strong>Taxa de serviço:
                                                        R$<?php echo number_format($BANCO_ITAU_TAXA_DE_SERVICO, 2, ',', '.') ?></strong>
                                                </p>
                                            <?php
                                            }
                                            ?>
                                            <p class="txt-verde fontsize-p">Entrega em até 90 minutos</p>
                                            <span style='visibility:hidden'><input type="radio" name="pagto"
                                                    value="<?php echo $FORMAS_PAGAMENTO['PAGAMENTO_BANCO_ITAU_ONLINE'] ?>" <?php if ($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_BANCO_ITAU_ONLINE'])
                                                                                                                                echo " checked"; ?>></span>
                                        </div>
                                    <?php
                                    } else {
                                    ?>
                                        <div class="row borda-linhas-formas-pagamento">
                                            <?php echo $msg_bloqueia_BancoItau; ?>
                                        </div>
                                    <?php
                                    }
                                    ?>
                                </div>
                            </div>
                        <?php
                        } else {
                            echo "&nbsp;";
                        }
                        ?>
                    </div>
                    <div id="tab_content"></div>
                </div>
                <div class="col-md-2 hidden-sm hidden-xs p-top10">
                    <?php
                    if ($banner) {
                        foreach ($banner as $b) {
                    ?>
                            <div class="row pull-right">
                                <a href="<?php echo $b->link; ?>" class="banner" id="<?php echo $b->id; ?>" target="_blank"><img
                                        src="<?php echo $controller->objBanner->urlLink . $b->imagem; ?>" width="186" class="p-3"
                                        title="<?php echo $b->titulo; ?>"></a>
                            </div>
                    <?php
                        }
                    }
                    ?>
                    <div class="row pull-right facebook"></div>
                </div>
            </div>

        </div>
    </form>
<?php
}
?>

<script src="/js/facebook.js"></script>
<?php
require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/footer.php";
