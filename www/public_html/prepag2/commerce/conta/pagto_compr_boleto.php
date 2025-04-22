<?php

$https = 'http' . (($_SERVER['HTTPS'] == 'on') ? 's' : '');
header("Content-Type: text/html; charset=ISO-8859-1; P3P: CP='CAO PSA OUR'", true);
require_once "../../../../includes/constantes.php";
require_once DIR_INCS . "main.php";
require_once DIR_INCS . "gamer/main.php";
require_once DIR_CLASS . "gamer/classIntegracao.php";
require_once DIR_INCS . "config.MeiosPagamentos.php";
validaSessao();

require_once DIR_INCS . "gamer/venda_e_modelos_logica_epp.php";


$rs_venda_row = pg_fetch_array($rs_venda);
$pagto_tipo = $rs_venda_row['vg_pagto_tipo'];
$ultimo_status = $rs_venda_row['vg_ultimo_status'];
$vg_integracao_parceiro_origem_id = $rs_venda_row['vg_integracao_parceiro_origem_id'];

if ($pagto_tipo != $FORMAS_PAGAMENTO['BOLETO_BANCARIO']) {
    $strRedirect = "/prepag2/commerce/conta/lista_vendas.php";

    //Fechando Conexão
    pg_close($connid);

    redirect($strRedirect);
}

$pagina_titulo = "Comprovante";
$cabecalho_file = isset($GLOBALS['_SESSION']['is_integration']) && $GLOBALS['_SESSION']['is_integration'] == true ? RAIZ_DO_PROJETO . "/public_html/prepag2/commerce/includes/cabecalho_int.php" : RAIZ_DO_PROJETO . "public_html/game/includes/cabecalho.php";
include $cabecalho_file;

//Recupera usuario
$completar_endereco = false;
if (isset($_SESSION['usuarioGames_ser']) && !is_null($_SESSION['usuarioGames_ser'])) {
    $usuarioGames = unserialize($_SESSION['usuarioGames_ser']);
    $usuarioId = $usuarioGames->getId();
    if (
        trim($usuarioGames->getEndereco()) == '' ||
        trim($usuarioGames->getNumero()) == '' ||
        trim($usuarioGames->getBairro()) == '' ||
        trim($usuarioGames->getCidade()) == '' ||
        trim($usuarioGames->getEstado()) == '' ||
        trim($usuarioGames->getCEP()) == ''
    ) {
        $completar_endereco = true;
    }
} else {
    echo "<div class='txt-vermelho text-center top50'><p>Sua sessão expirou.</p><p>Volte no jogo e tente novamente.</p> Obrigado! </div>";
    require_once RAIZ_DO_PROJETO . "public_html/prepag2/commerce/includes/rodape.php";
    die();
}

//Verifica se é preciso informar os dados de endereço, dado o email do GAMER
if ($completar_endereco) {
    endereco_page($completar_endereco);
}


?>

<script>
    function fcnJanelaBoleto() {
        <?php
        if ($usuarioGames) {
            //Codigo do usuario
            if (BANCO_BOLETO == "asaas") {
                require_once "../../../../banco/asaas/classBoletoAsaas.php";
                $classBoleto = new classBoleto();

                $buscarBoleto = "SELECT bbg_valor FROM boleto_bancario_games WHERE bbg_vg_id = " . addslashes($venda_id) . ";";

                $boletoEncontrado = pg_fetch_array(SQLexecuteQuery($buscarBoleto));
                $boleto_valor = $boletoEncontrado["bbg_valor"];

                $params = array(
                    'cpf_cnpj' => str_replace('-', '', str_replace('.', '', $usuarioGames->ug_sCPF)),
                    'nome' => $usuarioGames->ug_nome_cpf,
                    'valor' => number_format(($boleto_valor), 2, '.', ''),
                    'descricao' => "E-PREPAG",
                    'idpedido' => "GM" . $venda_id,
                    'email' => $usuarioGames->ug_sEmail
                );
                $link = $classBoleto->callService($params);
                if ($link) {
                    ?>
                    window.open('<?php echo $link ?>', 'boleto', '');
                    <?php
                } else {
                    ?>
                    alert('Erro ao gerar boleto <?= $boleto_valor ?>.');
                    console.log('<?= ($buscarBoleto . "\n" . $boletoEncontrado) ?>');
                    <?php
                }
            } elseif ($usuarioGames->b_Is_Boleto_Bradesco()) {
                ?>
                window.open('/boletos/gamer/boleto_bradesco.php?venda=<?php echo $venda_id ?>', 'boleto', '');
                <?php
            } else {
                die("Caixa");
                ?>
                window.open('/SICOB/BoletoWebCaixaCommerce.php?venda=<?php echo $venda_id ?>', 'boleto', '');
                <?php
            }
        }
        ?>
    }
</script>
<div class="wrapper" style="border-top: 0px; margin: 0 auto;">
    <?php if (empty($GLOBALS['_SESSION']['is_integration']) || $GLOBALS['_SESSION']['is_integration'] == "false"): ?>
        <table border="0" cellspacing="0" width="100%">
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr valign="top" align="center">
                <td>
                    <?php
                    require_once DIR_INCS . "gamer/venda_e_modelos_view_epp.php";

                    //Teste de integração
                    if ($vg_integracao_parceiro_origem_id) {
                        //Novo modelo de captura de CPF
                        cpf_page($partner_list);
                    } else {
                        //Testando a necessidade de solicitação de CPF para Gamer
                        if ($test_opr_need_cpf) {
                            cpf_page_gamer();
                        }

                        require_once DIR_INCS . "gamer/pagto_compr_usuario_dados.php";
                    }
                    ?>
                </td>
            </tr>
        </table>
    <?php endif; ?>

    <?php
    if ($ultimo_status == $GLOBALS['STATUS_VENDA']['DADOS_PAGTO_RECEBIDO']) { ?>
        <table border="0" cellspacing="0" class="wrapper">
            <tr>
                <td class="text13" align="center" height="25">
                    <br>
                    <?php if (b_isIntegracao()) { ?>
                        Após o pagamento aguarde até 2 dias úteis para confirmação bancária. Seu crédito ocorrerá diretamente em
                        sua conta no jogo.
                        <?php
                    } else { ?>
                        Obrigado por comprar conosco!<br><br>
                        Após o pagamento aguarde até dois dias úteis<br />
                        para o processamento do boleto pelo banco, quando a <br />
                        senha será automaticamente enviada para o seu email.<br>
                        <?php
                    } //end else do if(b_isIntegracao())
                    ?>
                </td>
            </tr>
        </table>

        <table border="0" cellspacing="0" class="wrapper">
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td align="center" class="texto">
                    <input type="button" name="btOK" value="Clique aqui para emitir o Boleto Bancário"
                        OnClick="fcnJanelaBoleto();"
                        class="btn btn-sm btn-success int-btn1 grad1 int-pagamento-compr-online-btn1 int-pagamento-compr-online-btn1 int-pagamento-compr-online-btn-boleto">
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td align="center" class="texto">
                </td>
            </tr>
        </table>
    <?php } ?>
    <table border="0" cellspacing="0" class="wrapper">
        <tr>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td align="center" class="texto">
                &nbsp;
            </td>
        </tr>
    </table>
</div>
<br>&nbsp;
<br>&nbsp;
<!-- Google Code for Analytics Page -->
<script src="<?php echo $https; ?>://www.google-analytics.com/urchin.js" type="text/javascript">
</script>
<!-- Facebook Pixel Code -->
<script>
    !function (f, b, e, v, n, t, s) {
        if (f.fbq) return; n = f.fbq = function () { n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments) }; if (!f._fbq) f._fbq = n;
        n.push = n; n.loaded = !0; n.version = '2.0'; n.queue = []; t = b.createElement(e); t.async = !0;
        t.src = v; s = b.getElementsByTagName(e)[0]; s.parentNode.insertBefore(t, s)
    }(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '228069144336893'); // Insert your pixel ID here.
    fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none"
        src="https://www.facebook.com/tr?id=228069144336893&ev=PageView&noscript=1" /></noscript>
<!-- End Facebook Pixel Code -->
<script type="text/javascript">
    _uacct = "UA-1903237-3";
    urchinTracker();
</script>
<?php
require_once RAIZ_DO_PROJETO . "public_html/prepag2/commerce/includes/rodape.php";

if (getPartner_Integracao_Transparente_By_ID($GLOBALS['_SESSION']['integracao_origem_id'])) {
    ob_clean();
    if ($usuarioGames) {
        if ($usuarioGames->b_Is_Boleto_Banespa()) {
            die("Banespa");
            redirect('/SICOB/BoletoWebBanespaCommerce.php?venda=' . $venda_id);
        } elseif ($usuarioGames->b_Is_Boleto_Itau()) {
            redirect('/boletos/gamer/boleto_itau.php?venda=' . $venda_id);
        } else {
            die("Caixa");
            redirect('/SICOB/BoletoWebCaixaCommerce.php?venda=' . $venda_id);
        }
    }
}//end if(getPartner_Integracao_Transparente_By_ID)
?>