<?php


if($_SERVER["REMOTE_ADDR"] == "201.93.162.169"){
	//error_reporting(E_ALL); 
	//ini_set("display_errors", 1); 
}

$https = 'http' . (($_SERVER['HTTPS']=='on') ? 's' : '');

if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') )
{
        $teste = substr($_SERVER['HTTP_USER_AGENT'],strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE')+4,4)*1;
        echo "<meta http-equiv=\"X-UA-Compatible\" content=\"IE=".htmlspecialchars($teste)."\" />";
}
require_once "../../../includes/constantes.php";
require_once DIR_CLASS . 'gamer/controller/HeaderController.class.php';
$controller = new HeaderController;
$controller->setHeader();
$controller->atualizaSessaoUsuario();
//flag para controle de include do jquery
$GLOBALS["jquery"] = true;

?>
<style>
    h2{font-size: 22px;}
</style>
<script src="/js/valida.js"></script>
<div class="container txt-azul-claro bg-branco">
    <div class="row top20">
        <div class="row">
            <div class="col-md-12">
                <span class="glyphicon glyphicon-triangle-right graphycon-big pull-left" aria-hidden="true"></span><strong class="pull-left"><h4 class="top20">pagamento</h4></strong>
            </div>
        </div>
        <div class="col-md-12">
<?php  
require_once DIR_CLASS . "gamer/classIntegracao.php";
require_once DIR_INCS . "inc_register_globals.php";

//Recupera usuario
if(isset($_SESSION['usuarioGames_ser']) && !is_null($_SESSION['usuarioGames_ser'])){
        $usuarioGames = unserialize($_SESSION['usuarioGames_ser']);
        $usuarioId = $usuarioGames->getId();
}

require_once DIR_INCS . "gamer/venda_e_modelos_logica.php";

$rs_venda_row = pg_fetch_array($rs_venda);
$pagto_tipo	 = $rs_venda_row['vg_pagto_tipo'];
$iforma = $pagto_tipo; 
$ultimo_status	= $rs_venda_row['vg_ultimo_status'];

if(!isset($total_carrinho)) $total_carrinho = 0;

if($ultimo_status == $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']) {
        $sql  = "
                select * 
                from tb_pag_compras 
                where 
                    idvenda = " . $venda_id . " 
                    and idcliente=" . $usuarioId.";";
        $rs_pagto = SQLexecuteQuery($sql);
        if(!$rs_pagto || pg_num_rows($rs_pagto) == 0) {
                $msg = "Não foi encontrado o pagamento para a venda ".$venda_id.".\n";
        } else {
                $rs_pagto_row = pg_fetch_array($rs_pagto);
                $banco = $rs_pagto_row['banco'];
                $assinatura = $rs_pagto_row['assinatura'];
                if($total_carrinho==0) {
                        $total_carrinho = $rs_pagto_row['total']/100;
                }
        }
}//end if($ultimo_status == $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'])

if($total_carrinho == 0) {
        $sql  = " 
                select * from tb_pag_compras 
                where 
                    idvenda = " . $venda_id . " 
                    and idcliente=" . $usuarioId."; ";
        $rs_pagto = SQLexecuteQuery($sql);
        if(!$rs_pagto || pg_num_rows($rs_pagto) == 0) {
                $msg = "Não foi encontrado o pagamento para a venda ".$venda_id.".\n";
        } else {
                $rs_pagto_row = pg_fetch_array($rs_pagto);
                if($total_carrinho==0) {
                        $total_carrinho = $rs_pagto_row['total']/100;
                }
        }
} //end if($total_carrinho == 0) 

if(! (b_IsPagtoBoletoDeposito($pagto_tipo) || b_IsPagtoOnline($pagto_tipo )) ) {
        $strRedirect = "/game/conta/pedidos.php";
        //Fechando Conexão
        pg_close($connid);
        redirect($strRedirect);
}

// recupera numorder
$OrderId = $_SESSION['pagamento.numorder'];
$orderId = $OrderId;
$numOrder = $OrderId;

// Obtem o valor total deste pedido
$libera_pagamento = array(
    'BancodoBrasil' => true,
    'BancoItau'     => true,
    'Bradesco'      => true,
    'Hipay'         => true,
    'Paypal'        => true,
    'Boleto'        => true,
    'Deposito'      => true,
    'EppCash'       => true,
    'Cielo'         => true,
    'Pix'           => true,
);

$pagtoInvalido = false;

pg_result_seek($rs_venda_modelos, 0);
$arr_venda_modelos = pg_fetch_all($rs_venda_modelos);

$produto_idade_minima = "";
foreach($arr_venda_modelos as $modelo){
    if(isset($modelo["vgm_ogp_id"])){
        $sql = "SELECT ogp_idade_minima FROM tb_operadora_games_produto WHERE ogp_id = " . $modelo["vgm_ogp_id"];
        $rs_operadora = SQLexecuteQuery($sql);
        $rs_idade_minima = pg_fetch_all($rs_operadora)[0]["ogp_idade_minima"];
        if($rs_idade_minima > $GLOBALS["IDADE_MINIMA"]){
            $GLOBALS["IDADE_MINIMA"] = $rs_idade_minima;
            $produto_idade_minima = $modelo["vgm_nome_produto"];
        }
    }
}

foreach($arr_venda_modelos as $ind => $rs_venda_modelos_row){
    
    $tipoId['operadora'] = $rs_venda_modelos_row['vgm_opr_codigo'];
    $arrPagtosBloqueados[] = getMeiosPagamentosBloqueados($tipoId, $libera_pagamento);
}


// Insere os arquivos de URL de cada banco
if(($pagto_tipo == $FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) || ($pagto_tipo == $FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_DEBITO']) || ($pagto_tipo == $FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_CREDITO'])  ) {
    
        foreach($arrPagtosBloqueados as $ind => $val){
            if(!$arrPagtosBloqueados[$ind]['Bradesco']){
                $pagtoInvalido = true;
                break;
            }
        }
    
        include RAIZ_DO_PROJETO."banco/bradesco/inc_functions.php";
        include RAIZ_DO_PROJETO."banco/bradesco/inc_urls_bradesco.php";
        include RAIZ_DO_PROJETO."banco/bradesco/config.inc.bradesco_transf.php";
        
} 
else if($pagto_tipo == $FORMAS_PAGAMENTO['PAGAMENTO_BB_DEBITO_SUA_CONTA']) {
    
        foreach($arrPagtosBloqueados as $ind => $val){
            if(!$arrPagtosBloqueados[$ind]['BancodoBrasil']){
                $pagtoInvalido = true;
                break;
            }
        }
    
        include DIR_INCS . "gamer/venda_e_modelos_calculate.php";
        include RAIZ_DO_PROJETO . "banco/bancodobrasil/inc_urls_bancodobrasil.php";
        
} 
else if($pagto_tipo == $PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC) {
    
        foreach($arrPagtosBloqueados as $ind => $val){
            if(!$arrPagtosBloqueados[$ind]['BancoItau']){
                $pagtoInvalido = true;
                break;
            }
        }
        
        // Recupera Itau ID
        $sql  = "select * from tb_pag_compras where idvenda = " . $venda_id . " and idcliente=" . $usuarioId;
        $rs_pagto_id = SQLexecuteQuery($sql);
        if(!$rs_pagto_id || pg_num_rows($rs_pagto_id) == 0) {
                $msg = "Não foi encontrado o pagamento para a venda ".$venda_id.".\n";
        } else {
                $rs_pagto_id_row = pg_fetch_array($rs_pagto_id);
                $id_transacao_itau = $rs_pagto_id_row['id_transacao_itau'];
        }
        include DIR_INCS . "gamer/venda_e_modelos_calculate.php";
        require_once RAIZ_DO_PROJETO . "banco/itau/inc_config.php";
        require_once RAIZ_DO_PROJETO . "banco/itau/inc_urls_bancoitau.php";
        
} 
else if($pagto_tipo == $PAGAMENTO_HIPAY_ONLINE_NUMERIC) {
    
        foreach($arrPagtosBloqueados as $ind => $val){
            if(!$arrPagtosBloqueados[$ind]['Hipay']){
                $pagtoInvalido = true;
                break;
            }
        }
        
        include DIR_INCS . "gamer/venda_e_modelos_calculate.php";
//		include DIR_WEB."prepag2/pag/bep/inc_urls_bancoeprepag.php";
        
} 
else if($pagto_tipo == $PAGAMENTO_PAYPAL_ONLINE_NUMERIC) {
    
        foreach($arrPagtosBloqueados as $ind => $val){
            if(!$arrPagtosBloqueados[$ind]['Paypal']){
                $pagtoInvalido = true;
                break;
            }
        }
        
        include DIR_INCS . "gamer/venda_e_modelos_calculate.php";
        
//		include DIR_WEB."prepag2/pag/bep/inc_urls_bancoeprepag.php";
} 
else if($pagto_tipo == $PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC) {
    
        foreach($arrPagtosBloqueados as $ind => $val){
            if(!$arrPagtosBloqueados[$ind]['EppCash']){
                $pagtoInvalido = true;
                break;
            }
        }
        
        include DIR_INCS . "gamer/venda_e_modelos_calculate.php";
        include DIR_WEB."prepag2/pag/bep/inc_urls_bancoeprepag.php";
} 
else if($pagto_tipo == $PAGAMENTO_PIN_EPREPAG_NUMERIC) {

        
        foreach($arrPagtosBloqueados as $ind => $val){
            if(!$arrPagtosBloqueados[$ind]['EppCash']){
                $pagtoInvalido = true;
                break;
            }
        }
        
        $taxa = $PAGAMENTO_PIN_EPP_TAXA;
        
}
else if($pagto_tipo == $PAGAMENTO_PIX_NUMERIC) {

        
        foreach($arrPagtosBloqueados as $ind => $val){
            if(!$arrPagtosBloqueados[$ind]['Pix']){
                $pagtoInvalido = true;
                break;
            }
        }
        
        $taxa = $PAGAMENTO_PIX_TAXA;
        
}

$numOrder = $OrderId;

// Redireciona para a página final no site do Banco
if($iforma==$FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) {
    $img_bank_logo = "bradesco_logo_dr.gif";
    $simg_bank = "<img src='/imagens/pag/$img_bank_logo' border='0' title='Bradesco'>";
        
} else if($iforma==$FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_DEBITO']) {
        $location = $link_PagtoFacil;
        $img_bank_logo = "bradesco_logo_dr.gif";
        $simg_bank = "<img src='/imagens/pag/$img_bank_logo' border='0' title='Bradesco'>";
} else if($iforma==$FORMAS_PAGAMENTO['PAGAMENTO_BB_DEBITO_SUA_CONTA']) {
        $location = $link_BBDebito;
        $img_bank_logo = "bb_logo_dr.gif";
        $simg_bank = "<img src='/imagens/pag/$img_bank_logo' border='0' title='Banco do Brasil'>";
} else if($iforma==$PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC) {
        $location = $link_BItauShopline;
        $img_bank_logo = "itau_logo_dr.gif";
        $img_bank_logo_size = array('84', '68');
        $simg_bank = "<img src='/imagens/pag/$img_bank_logo' width='$img_bank_logo_size[0]' height='$img_bank_logo_size[1]' border='0' title='Banco Itaú Shopline'>";
} else if($iforma==$PAGAMENTO_HIPAY_ONLINE_NUMERIC) {
        $simg_bank = "<img src='/imagens/pag/Logo-hipay.png' width='142' height='49' border='0' title='Banco Hipay'>";
} else if($iforma==$PAGAMENTO_PAYPAL_ONLINE_NUMERIC) {
        $simg_bank = "<img src='/imagens/pag/Logo-paypal.jpg' width='159' height='35' border='0' title='PayPal - Pagamento Online'>";
} else if($iforma==$PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC) {
        $location = $link_EPP;
        $simg_bank = "<img src='/imagens/pag/epp_logo_loja.gif' width='116' height='43' border='0' title='Banco E-Prepag'>";
} else if(b_IsPagtoCielo($pagto_tipo)) {
        $simg_bank = "<img src='/imagens/pag/pagto_cielo.gif' width='116' height='43' border='0' title='Pagamento Cielo'>";
} else if($iforma==$PAGAMENTO_PIX_NUMERIC) {
        $simg_bank = "<img src='/imagens/pag/iconePIX.png' width='116' border='0' title='Pagamento PIX' class='top40'>";
} else {
        $location = $link_error;
        $simg_bank = "";
}

if($pagtoInvalido){
?>
    <div class="col-md-12">
        <div class="alert alert-danger" id="erro" role="alert">
            <span class="glyphicon t0 glyphicon-exclamation-sign" aria-hidden="true"></span>
            <span class="sr-only">Error:</span>
            Erro: forma de pagamento inválida no momento.
        </div>
    </div>
<?php
}else{
    
    if($ultimo_status == $GLOBALS['STATUS_VENDA']['PEDIDO_EFETUADO']) { 
?>

        <script language="JavaScript" type="text/JavaScript">
        <!--

            function abrePaginaBanco() {
                $("#vaipagamento").click();
            }

            function GP_popupConfirmMsg(msg) { //v1.0
              document.MM_returnValue = confirm(msg);
            }

            function IrAoBanco() {
                $("#btnIrAoBanco").click();
            }
            function ShowPopupWindowXY(fileName) {
                myFloater = window.open(fileName,'myWindow','scrollbars=yes,status=no,width=' +(0.8*screen.width) +',height='+(0.8*screen.height) +',top='+(0.1*screen.height) +',left='+(0.1*screen.width)+'');
            }

            refresh_snipet = 1;
            <?php
                if($pagto_tipo != $PAGAMENTO_PIN_EPREPAG_NUMERIC) {
            ?>

            // Mostra status da compra
            function refresh_status(){
                $(document).ready(function(){
                    $.ajax({
                        type: "POST",
                        url: "/ajax/gamer/ajax_info_pagamento.php",
                        data: "numcompra=<?php echo $numOrder; ?>",
                        beforeSend: function(){
                        },
                        success: function(txt){
                            if (txt != "ERRO") {
                                if(txt.indexOf("Pagamento completo em ")>0) {
                                    $(".hide-pix-success").hide();
                                    if($("#info_pagamento").attr("pix") == "1"){
                                        $("#pagamento_ok").show("slow");                
                                    }else{
                                        $("#info_pagamento").html(txt);
                                    }
                                } else {
                                    if(refresh_snipet==0) {
                                        clearInterval(refreshIntervalId);
                                    }
                                }
                            } else {
                            }
                        },
                        error: function(){
                            $("#info_pagamento").html("");
                        }
                    });
                });
            }

            var refreshIntervalId = setInterval(refresh_status, 5000);

            <?php
                } //end if($pagto_tipo != $PAGAMENTO_PIN_EPREPAG_NUMERIC)
            ?>

        //-->
        </script>

<?php
    } //end if($ultimo_status == $GLOBALS['STATUS_VENDA']['PEDIDO_EFETUADO'])

    include DIR_INCS . "gamer/venda_e_modelos_view.php"; 
    
    //Testando a necessidade de solicitação de CPF para Gamer
    if($test_opr_need_cpf || $iforma==$FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) {
        cpf_page_gamer();
    }//end if($test_opr_need_cpf)

    include DIR_INCS . "gamer/pagto_compr_usuario_dados.php";

    if(($ultimo_status == $GLOBALS['STATUS_VENDA']['DADOS_PAGTO_RECEBIDO']) || ($ultimo_status == $GLOBALS['STATUS_VENDA']['PAGTO_CONFIRMADO']) || ($ultimo_status == $GLOBALS['STATUS_VENDA']['PROCESSAMENTO_REALIZADO'])){ 
?>
        <div class="col-md-12 text-center">
            <p>Obrigado!</p>
            <p>Em poucos instantes a transação será concluida. Em caso de dúvida, favor entrar em contato com o suporte.</p>
        </div>
<?php 
    }//end if(($ultimo_status == $GLOBALS['STATUS_VENDA']['DADOS_PAGTO_RECEBIDO']) || ($ultimo_status == $GLOBALS['STATUS_VENDA']['PAGTO_CONFIRMADO']) || ($ultimo_status == $GLOBALS['STATUS_VENDA']['PROCESSAMENTO_REALIZADO']))
    else if($ultimo_status == $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']){
?>
        <div class="col-md-12 text-center">
            <p>Obrigado!</p>
            <p>Sua transação foi processada com sucesso.</p>
			<a class="btn btn-success bottom10" href="https://www.e-prepag.com.br/game/conta/pedidos.php">Meus pedidos</a>
        </div>
<?php 
        if (($banco=="237") && ($assinatura)) { 
?>
            <div class="col-md-12 text-center txt-cinza-claro">
                <p><strong>Autenticação</strong></p>
                <p><?php formataAssinatura($assinatura) ?></p>
            </div>
<?php 
        } 

    } 
    else if($ultimo_status == $GLOBALS['STATUS_VENDA']['VENDA_CANCELADA']) {
?>
        <div class="col-md-12 txt-vermelho espacamento text-center bottom20">
            Sua compra foi cancelada pelo sistema. Tente novamente.
        </div>
<?php  
    } 
    else {
        
        if(!empty($simg_bank)){
?>
            <div class="clearfix top20"></div>
            <div class="int-pagamento-compr-online-logo col-md-5 text-right-lg text-right-md text-center-sm text-center-xs col-lg-5 col-sm-12 col-xs-12 hide-pix-success">
                <?php echo $simg_bank; ?>
            </div>
<?php
        }
    
        if($pagto_tipo != $PAGAMENTO_PIN_EPREPAG_NUMERIC && $pagto_tipo != $PAGAMENTO_PIX_NUMERIC) {
?>
            <div id="info_pagamento" class="col-md-7">
                <div class="col-md-1 col-lg-1 col-sm-12 col-xs-12 text-right-lg text-right-md text-center-sm text-center-xs top10">
                    <img src='/imagens/loading1.gif' width='42' height='42' border='0' title='Aguardando pagamento...' class="int-pagamento-compr-online-loading">
                </div>
                <div class="col-md-11 col-lg-11 col-sm-12 col-xs-12 text-left top10">
                    <span class="int-pagamento-compr-online-message1 text-left fontsize-pp txt-verde">Aguardando pagamento.<br>Clique abaixo para efetuar a transação.</span>    
                </div>
            </div>
<?php
        }//end if($pagto_tipo != $PAGAMENTO_PIN_EPREPAG_NUMERIC) 
        
        if($pagto_tipo == $PAGAMENTO_PIX_NUMERIC){
?>
            <div id="info_pagamento" pix="1" style="display: none;" class="col-md-12">

            </div>
<?php
        }
        
        //inicio do bloco de pagamento com PINS EPREPAG
        if($pagto_tipo == $PAGAMENTO_PIN_EPREPAG_NUMERIC) {

            unset($_SESSION['PINEPP']);
            unset($_SESSION['PIN_NOMINAL']);

            echo "<div id='box-principal' name='box-principal'>";

            if(($total_geral_epp_cash/100+$taxa) > 0) {

                $aux_saldo = unserialize($_SESSION['usuarioGames_ser']);
                require_once DIR_CLASS . "classAtivacaoPinTemplate.class.php";
                /*
                Na confecção do vetor abaixo onde está sendo mencionado:
                [ $total_geral_epp_cash/100 ]
                não se trata de conversão e sim uma divisão simples para que o ajax receba o valor dividido por 100 com a finalidade
                de facilitar os calculos
                */

                if (b_isIntegracao() && b_isIntegracao_with_nonvalidated_email() && (!b_isIntegracao_logged_in())) {
                    $user_logado_aux	= false;
                    $saldo_aux			= 0;
                    $saldo_final_aux	= number_format((0-($total_geral_epp_cash/100+$taxa)),2,'.','');
                }
                else {
                    $user_logado_aux	= true;
                    $saldo_aux			= $aux_saldo->ug_fPerfilSaldo;
                    $saldo_final_aux	= number_format(($aux_saldo->ug_fPerfilSaldo-($total_geral_epp_cash/100+$taxa)),2,'.','');
                }

                $paramList	= array(
                                        'jquery_core_include'	=>	false,
                                        'url_resources'		=>	'/ativacao_pin/',
                                        'usuarioLogado'		=>	$user_logado_aux,
                                        'saldo'			=>	$saldo_aux,
                                        'valor_pedido'		=>	($total_geral_epp_cash/100+$taxa),
                                        'saldo_final'		=>	$saldo_final_aux,
                                        'email'			=>	$_SESSION['integracao_client_email'],
                                        );

                $ativacaoPinTemplate2 = new AtivacaoPinTemplate($paramList);
                echo $ativacaoPinTemplate2->boxAtivacaoPin();

            }//end if(($total_geral_epp_cash/100+$taxa) > 0)
            else { 
                echo "Dados da compra recebido sem valores.";
            }

            echo "</div>";

        }//end if($pagto_tipo == $PAGAMENTO_PIN_EPREPAG_NUMERIC)  
        //fim do bloco de pagamento com PINS EPREPAG

        //inicio do bloco de pagamentos CIELO
        if(b_IsPagtoCielo($pagto_tipo)) {

            //Aplicando nova regra
            require_once DIR_CLASS . "gamer/classLimite.php";
            $limite = new Limite(getCodigoCaracterParaPagto($pagto_tipo), intval($usuarioId));

            //Verificando Token digitado
            if(!empty($token) && !empty($cielo_pan)) {
                $limite->setStatusTokenUtilizado($cielo_pan,$token);
            }

            if($limite->getPrimeiraVendaGamers($cielo_pan,$data_exibicao)) {
                echo "
                <style>
                        .divToken { font: 11px bolder arial, sans-serif; color: #000000; margin: 20px 20px 0px 20px; text-align: left; }
                        .titulo {font: 13px bolder arial, sans-serif; font-weight: bold;}
                </style>
                <div class='divToken'><span class='titulo'>Validação do cartão</span><br><br>
                        Para a segurança desta transação, pedimos que digite abaixo o código de 6 dígitos que aparece na fatura do seu cartão. ( Este código aparece após o *, na descrição de sua Última compra pela E-prepag realizada em <nobr>".htmlspecialchars($data_exibicao)."</nobr><br><br>
                        Esta operação será solicitada somente uma vez para este cartão.<br><br>
                        <form action='".htmlspecialchars($_SERVER['PHP_SELF'])."' method='POST'>
                                <input type='hidden' name='cielo_pan' id='cielo_pan' value='".htmlspecialchars($cielo_pan)."'/>
                                <input type='text' name='token' id='token' maxlength='6' size='5'/>
                                <input type='submit' name='btnToken' id='btnToken' value='Enviar'>
                        </form>
                </div>"; //$cielo_pan contem o hash do cartão
            }//end if($limite->getPrimeiraVendaGamers())
            else {
                
                // controle primeiro pedido
                $dadosPedidoDescricao = "EPPPEDIDODESCRICAO";
                //Nova linha abaixo colocada por Wagner de Miranda
                $softDescriptor = $_SESSION['pagamento.token'];

                switch ($pagto_tipo) {
                        case $PAGAMENTO_VISA_DEBITO_NUMERIC:
                                $codigoBandeira = "visa";
                                $formaPagamento = "A";
                                $indicadorAutorizacao = "2";
                                break;
                        case $PAGAMENTO_VISA_CREDITO_NUMERIC:
                                $codigoBandeira = "visa";
                                $formaPagamento = "1";
                                $indicadorAutorizacao = "2";
                                break;
                        case $PAGAMENTO_MASTER_DEBITO_NUMERIC:
                                $codigoBandeira = "mastercard";
                                $formaPagamento = "A";
                                $indicadorAutorizacao = "2";
                                break;
                        case $PAGAMENTO_MASTER_CREDITO_NUMERIC:
                                $codigoBandeira = "mastercard";
                                $formaPagamento = "1";
                                $indicadorAutorizacao = "2";
                                break;
                        case $PAGAMENTO_ELO_DEBITO_NUMERIC:
                                $codigoBandeira = "elo";
                                $formaPagamento = "A";
                                $indicadorAutorizacao = "2";
                                break;
                        case $PAGAMENTO_ELO_CREDITO_NUMERIC:
                                $codigoBandeira = "elo";
                                $formaPagamento = "1";
                                $indicadorAutorizacao = "3";
                                break;
                        case $PAGAMENTO_DINERS_CREDITO_NUMERIC:
                                $codigoBandeira = "diners";
                                $formaPagamento = "1";
                                $indicadorAutorizacao = "3";
                                break;
                        case $PAGAMENTO_DISCOVER_CREDITO_NUMERIC:
                                $codigoBandeira = "discover";
                                $formaPagamento = "1";
                                $indicadorAutorizacao = "3";
                                break;
                }
?>
            <div class="col-md-12 col-lg-12 col-sm-12 col-xs-12 text-center">
                <form action="/cielo/pages/novoPedidoAguarde.php" method="POST" target="_blank">
                    <input type="hidden" name="produto" id="produto" value="<?php echo ($total_geral+$taxa)*100;?>"/>
                    <input type="hidden" name="codigoBandeira" id="codigoBandeira" value="<?php echo $codigoBandeira;?>"/>
                    <input type="hidden" name="formaPagamento" id="formaPagamento" value="<?php echo $formaPagamento;?>"/>
                    <input type="hidden" name="capturarAutomaticamente" id="capturarAutomaticamente" value="true"/>
                    <input type="hidden" name="indicadorAutorizacao" id="indicadorAutorizacao" value="<?php echo $indicadorAutorizacao;?>"/>
                    <input type="hidden" name="dadosPedidoDescricao" id="dadosPedidoDescricao" value="<?php echo $dadosPedidoDescricao;?>"/>
                    <input type="hidden" name="softDescriptor" id="softDescriptor" value="<?php echo $softDescriptor;?>"/>
                    <input type="hidden" name="campolivre" id="campolivre" value="<?php echo md5(uniqid(rand(), true));?>"/>
<?php
                    $sql  = "select * from tb_pag_compras " .
                                    "where idvenda = " . $venda_id . " and idcliente=" . $usuarioId;
                    $rs_pagto = SQLexecuteQuery($sql);
                    if(!$rs_pagto || pg_num_rows($rs_pagto) == 0) {
                        $msg = "Não foi encontrado o pagamento para a venda ".$venda_id.".\n";
                    } else {
                        $rs_pagto_row = pg_fetch_array($rs_pagto);
                        $numcompra = $rs_pagto_row['numcompra'];
?>
                        <input type="hidden" name="numcompra" id="numcompra" value="<?php echo $numcompra;?>"/>
                        <input class="btn btn-success top50" type="submit" name="btnIrCielo" value="Clique aqui para pagar">
<?php
                    }
?>
                </form>
            </div>
<?php
            }//end else do if($limite->getPrimeiraVendaGamers())
        }//fim do bloco de pagamentos CIELO
                        
        if(($pagto_tipo == $FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) || ($pagto_tipo == $FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_DEBITO']) || ($pagto_tipo == $FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_CREDITO']) ) {
            
            $cesta = $rs_pagto_row['cesta'];
            $numcompra = $rs_pagto_row['numcompra'];

            if(strpos($cesta, "\n")){
                $cesta_desc = explode("\n", $cesta);

                $cesta_descricao = "";
                $pattern = "/item:/";
                foreach ($cesta_desc as $i => $prod){
                    if(preg_match($pattern, $prod)){
                        $cesta_descricao .= $prod;
                    }
                }
            } else{
                $cesta_descricao = $cesta;
            }
            
            $obj_pagamento = new classBradescoTransferencia();
            
            $array_infos_ws = $obj_pagamento->montaVetorInformacoes($usuarioGames, $total_carrinho, $numcompra, trim($cesta_descricao));

            if(is_null($array_infos_ws)){
                $msg_problem = "Sua sessão expirou!";
                $titulo = "Sessão expirada";
                $redireciona = "/game/conta/login.php";
            } else{
                $comunica = $obj_pagamento->Req_EfetuaConsultaURL($array_infos_ws, $lista_resposta);

                if(is_null($comunica)){
                    $titulo = "ERRO - Problema na validação de seus dados";
                    $msg_problem = "Problema ao validar seus dados cadastrados! Por favor, relate o problema ao Suporte";
                    $redireciona = "/game/suporte.php";
                } else{
                    if(is_array($comunica)){
                        $titulo = "ERRO - Problema de comunicação com o Bradesco";
                        $msg_problem = "Houve um problema de comunicação com o Bradesco! Tente novamente mais tarde. Obrigado!";
                        $redireciona = "/game/index.php";
                    } else{
                        $location = $comunica;
                    }
                }
            }

            if(isset($msg_problem)){
?>
                <form name="pagamento" id="pagamento" method="POST" action="/game/mensagem.php">
                    <input type='hidden' name='msg' id='msg' value='<?php echo $msg_problem; ?>'>
                    <input type='hidden' name='titulo' id='titulo' value='<?php echo $titulo; ?>'>
                    <input type='hidden' name='link' id='link' value='<?php echo $redireciona; ?>'>
                </form>
                <script language='javascript'>
                    $("#info_pagamento").hide();
                    document.getElementById("pagamento").submit();
                </script>
<?php 
                die();
            } else{
?>
                <form action="" method="post" target="_blank">
                    <input class="btn btn-success top50" type="button" name="btnIrAoBanco" value="Clique aqui para pagar" onclick="window.open('<?php echo $location; ?>')" class="int-btn1 grad1 int-pagamento-compr-online-btn1">
                </form>
<?php
            }
        } 
        else if($pagto_tipo == $FORMAS_PAGAMENTO['PAGAMENTO_BB_DEBITO_SUA_CONTA']) {
?>
            <form action="<?php echo $location;?>" method="post" target="_blank">
                <input class="btn btn-success top50" type="submit" name="btnIrAoBanco" value="Clique aqui para pagar" class="int-btn1 grad1 int-pagamento-compr-online-btn1">
                <input type="hidden" name="idConv" value="<?php echo $bbr_idConv ; ?>">
                <input type="hidden" name="refTran" value="<?php echo $bbr_refTran ; ?>">
                <input type="hidden" name="valor" value="<?php echo $bbr_valor ; ?>">
                <input type="hidden" name="qtdPontos" value="<?php echo $bbr_qtdPontos ; ?>">
                <input type="hidden" name="dtVenc" value="<?php echo $bbr_dtVenc ; ?>">
                <input type="hidden" name="tpPagamento" value="<?php echo $bbr_tpPagamento ; ?>">
                <input type="hidden" name="urlRetorno" value="<?php echo $bbr_urlRetorno ; ?>">
                <input type="hidden" name="urlInforma" value="<?php echo $bbr_urlInforma ; ?>">
                <input type="hidden" name="nome" value="<?php echo $bbr_nome ; ?>">
                <input type="hidden" name="endereco" value="<?php echo $bbr_endereco ; ?>">
                <input type="hidden" name="cidade" value="<?php echo $bbr_cidade ; ?>">
                <input type="hidden" name="uf" value="<?php echo $bbr_uf ; ?>">
                <input type="hidden" name="cep" value="<?php echo $bbr_cep ; ?>">
                <input type="hidden" name="msgLoja" value="<?php echo $bbr_msgLoja ; ?>">
            </form>
<?php
        } else if($pagto_tipo == $PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC){ 

            $cripto = new Itaucripto();
            
            $dados_cripto = $cripto->geraDados($codEmp,$pedido,$valorAux,$observacao,$chave,$nomeSacado,$codigoInscricao,$numeroInscricao,$enderecoSacado,$bairroSacado,$cepSacado,$cidadeSacado,$estadoSacado,$dataVencimento,$urlRetorna,$ObsAdicional1,$ObsAdicional2,$ObsAdicional3);
//-----------------------------------------------------------------------------------------------------------------------------------------------------
//MODO UTILIZANDO ASP - DESCOMENTAR CASO UTILIZAR ASP            
            //$dados = getItauCrypto($form_fields, "pagto");
            
//            $aretorno = explode("\n", $dados);
            
//            $auxCripto = trim($aretorno[9]);
//            $dados_cripto = (empty($auxCripto)?$aretorno[10]:$aretorno[9]);
//-----------------------------------------------------------------------------------------------------------------------------------------------------            
?>
            <form action="<?php echo $location; ?>" method="post" target="_blank">
                <input type="hidden" name="DC" value="<?php echo $dados_cripto ?>">
                <input type="submit" name="btnIrAoBanco" value="Clique aqui para pagar" class="btn btn-success top50">
            </form>
<?php
        } 
        else if($pagto_tipo == $PAGAMENTO_BANCO_EPP_ONLINE_NUMERIC){ 

            $_SESSION['Banco_EPP_usuarioId'] = $usuarioId;
            $_SESSION['Banco_EPP_venda_id'] = $venda_id;
?>
            <form action="" method="post" target="_blank" name="formEPP">
                <input type="button" name="btnIrAoBanco" value="Clique aqui para pagar" onclick="window.open('<?php echo $location; ?>')">
            </form>
<?php
        } 
        elseif($pagto_tipo == $PAGAMENTO_PAYPAL_ONLINE_NUMERIC){ 
            
            // aqui exibimos o botao paypal que e gerado no inc_gen_order_pay.php
            include DIR_WEB."prepag2/pag/pay/inc_paypal_botao.php";
            // Para uso em testes PayPal/Hipay
            $total_carrinho_nominal = $GLOBALS['_SESSION']['carrinho_total_geral_treinamento'];
            $amount = $total_carrinho_nominal;
            $item_number = $OrderId;
            $item_name = montaCesta_pag_paypal($_SESSION['venda']);	//"Pagamento Online EPP";
?>
            <form action="/prepag2/pag/pay/paypal_process.php" target="_blank">
                <input type="hidden" name="cmd" value="_xclick">
                <input type="hidden" name="business" value="<?php echo $business ?>">
                <input type="hidden" name="item_name" value="<?php echo $item_name ?>">
                <input type="hidden" name="item_number" value="<?php echo $item_number ?>">
                <input type="hidden" name="INVNUM" value="<?php echo $item_number ?>">
                <input type="hidden" name="invoice" value="<?php echo $item_number ?>">
                <input type="hidden" name="amount" value="<?php echo number_format($amount,2) ?>">
                <input type="hidden" name="mc_gross" value="<?php echo number_format($amount,2) ?>">
                <input type="hidden" name="tax" value="<?php echo $taxa ?>">
                <input type="hidden" name="quantity" value="1">
                <input type="hidden" name="currency_code" value="<?php echo $currencyValue ?>">
                <input type="hidden" name="button_subtype" value="services">
                <input type="hidden" name="no_note" value="1">
                <input type="hidden" name="no_shipping" value="1">
                <input type="hidden" name="rm" value="1">
                <input type="hidden" name="return" value="<?php echo $retornosucesso ?>">
                <input type="hidden" name="cancel_return" value="<?php echo $retornocancela ?>">
                <input type="hidden" name="bn" value="PP-BuyNowBF:btn_buynowCC_LG.gif:NonHostedGuest">
                <input type="hidden" name="cbt" value="Continue">
                <input type="image" src="<?php echo $https; ?>://www.sandbox.paypal.com/pt_BR/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" title="Pague com PayPal!">
                <img alt="" border="0" src="<?php echo $https; ?>://www.sandbox.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
            </form>

<?php
        } 
        elseif($pagto_tipo == $PAGAMENTO_HIPAY_ONLINE_NUMERIC){ 

            // Para uso em testes PayPal/Hipay
            $total_carrinho_nominal = $GLOBALS['_SESSION']['carrinho_total_geral_treinamento'];
            $amount = $total_carrinho_nominal;	
?>
            <form action="/prepag2/pag/hpy/hipay_single_payment.php" target="_blank">
                <input type="hidden" name="numcompra" id="numcompra" value="<?php echo $_SESSION['pagamento.numorder'] ?>">
                <input type="hidden" name="amount" id="amount" value="<?php echo number_format($amount,2) ?>">
                <input type="image" src="/prepag2/commerce/images/botao_hipay.gif" border="0" name="submit" title="Hipay">
            </form>
<?php
        }
        else if($pagto_tipo == $PAGAMENTO_PIX_NUMERIC){ 
		        $sql  = "select nome from provedor_pix where ativo = 'A';";
				$ativo = SQLexecuteQuery($sql);
				$ativoNome = pg_fetch_assoc($ativo);
				
				if(!defined("PAGAMENTO_PIX_CHAVEAMENTO")) {
					include "/www/includes/config.MeiosPagamentos.php"; 
				}
				
				if(PAGAMENTO_PIX_CHAVEAMENTO == "a" || $_SERVER['REMOTE_ADDR'] == "187.18.199.57" || $usuarioGames->getId() == "8972") {
					if(number_format(($total_carrinho+$taxas),2,'.','') > 0.00){
                        $ativoNome["nome"] = "asaas";
                    }else{	
                        $ativoNome["nome"] = "mercadopago";
                    }
                    require_once RAIZ_DO_PROJETO.'banco/pix/'.$ativoNome["nome"].'/config.inc.pix.php'; 
                    //classe para pagamento em pix usada atualmente
                    if($ativoNome["nome"] == "asaas"){
                        $pix = new classPIX(); 
                        $params = array (
                            'metodo'    => PIX_REGISTER,
                            'cpf_cnpj'  => str_replace('-', '', str_replace('.', '', $usuarioGames->ug_sCPF)),
                            'nome'      => $usuarioGames->ug_nome_cpf,
                            'valor'     => number_format(($total_carrinho+$taxas),2,'.',''),
                            'descricao' => "E-PREPAG",
                            'idpedido'  => $ARRAY_CONCATENA_ID_VENDA['gamer'].$_SESSION['pagamento.numorder'],
                            'venda_id' => $venda_id,
                            'email'    => $usuarioGames->ug_sEmail
                        ); 
                        echo $pix->callService($params);
                        echo "asaas teste";
                    }else if($ativoNome["nome"] == "mercadopago")
                    {
                        $pix = new classPIX(); 
                        $params = array (
                            'metodo'    => PIX_REGISTER,
                            'cpf_cnpj'  => str_replace('-', '', str_replace('.', '', $usuarioGames->ug_sCPF)),
                            'nome'      => $usuarioGames->ug_nome_cpf,
                            'valor'     => number_format(($total_carrinho+$taxas),2,'.',''),
                            'descricao' => "E-PREPAG",
                            'idpedido'  => $ARRAY_CONCATENA_ID_VENDA['gamer'].$_SESSION['pagamento.numorder'],
                            'venda_id' => $venda_id,
                            'email'    => $usuarioGames->ug_sEmail
                        ); 
                        echo $pix->callService($params);
                        echo "mercado livre teste";
                    }
                    else{
                        echo "Pix não disponível no momento.";
                    }
				}else{
					require_once RAIZ_DO_PROJETO.'banco/pix/'.$ativoNome["nome"].'/config.inc.pix.php'; 
				
					if($ativoNome["nome"] == "blupay"){
						$pix = new classPIX();
				  
						//Bloco que vai para página de checkout
						$params = array (
							'metodo'    => PIX_REGISTER,
							'cpf_cnpj'  => str_replace('-', '', str_replace('.', '', $usuarioGames->ug_sCPF)),
							'nome'      => $usuarioGames->ug_nome_cpf,
							'valor'     => number_format(($total_carrinho+$taxa),2,'.',''),
							'descricao' => "E-PREPAG",
							'idpedido'  => $ARRAY_CONCATENA_ID_VENDA['gamer'].$_SESSION['pagamento.numorder']
						);
						
						$arquivo = '/www/log/requisicao-casa-credito.txt';

						$abre_arquivo = fopen($arquivo, 'a+');

						fwrite($abre_arquivo, json_encode($params) . "\n");

						fclose($abre_arquivo);
						
						usleep(800000);
						echo $pix->callService($params);
					}else if($ativoNome["nome"] == "mercadopago")
                    {
                        $pix = new classPIX(); 
                        $params = array (
                            'metodo'    => PIX_REGISTER,
                            'cpf_cnpj'  => str_replace('-', '', str_replace('.', '', $usuarioGames->ug_sCPF)),
                            'nome'      => $usuarioGames->ug_nome_cpf,
                            'valor'     => number_format(($total_carrinho+$taxa),2,'.',''),
                            'descricao' => "E-PREPAG",
                            'idpedido'  =>  $ARRAY_CONCATENA_ID_VENDA['gamer'].$_SESSION['pagamento.numorder'],
                            'email'    => $usuarioGames->ug_sEmail
                        ); 
                        $arquivo = '/www/log/requisicao-mercadopago.txt';

						$abre_arquivo = fopen($arquivo, 'a+');

						fwrite($abre_arquivo, json_encode($params) . "\n");

						fclose($abre_arquivo);
						
						usleep(800000);
                        echo $pix->callService($params);
                    }
                    else{
						$pix = new Pix(
							"CPF", 
							str_replace('-', '', str_replace('.', '', $usuarioGames->ug_sCPF)),
							!empty($usuarioGames->ug_nome_cpf)? $usuarioGames->ug_nome_cpf: "Nao possui nome",
							$ARRAY_CONCATENA_ID_VENDA['gamer'].$_SESSION['pagamento.numorder'],
							number_format(($total_carrinho+$taxas),2,'','')
						);
						usleep(800000);
						echo $pix->callService();
					}
				}
										
                //var_dump($params); die();

        } //end else if($pagto_tipo == $PAGAMENTO_PIX_NUMERIC)

        //inicio do bloco de pagamento com PINS EPREPAG
        if($pagto_tipo != $PAGAMENTO_PIN_EPREPAG_NUMERIC && $pagto_tipo != $PAGAMENTO_PIX_NUMERIC) {
?>
            <div class="col-md-12 text-center espacamento">
                Seus dados bancários ficarão restritos à interface do banco.
            </div>
<?php
        }
        elseif($pagto_tipo == $PAGAMENTO_PIX_NUMERIC) {
?>
            <div class="col-md-12 text-center espacamento">
                <b>ATENÇÃO: Não efetue o Pix fora do nosso site. Para cada pagamento será necessário gerar um novo pedido.</b>
            </div>
<?php
            
        }
?>
        </div>	
<?php 
// bloco pagamento fim  
?>
        <div id="pagamento_ok">	
<?php 
// bloco pagamento efetuado inicio 
?>
            <script language="JavaScript" type="text/javascript">
            <!--
            function send_to_trans()
            {
              document.forms['last_trans'].submit() ;
            }
            -->
            </script>
            <div class="col-md-11 text-center txt-verde bottom20 top50">
                <p>Pagamento realizado com sucesso.</p>
                <p>O crédito foi enviado para seu Email cadastrado.</p>
				<a class="btn btn-success bottom10" href="https://www.e-prepag.com.br/game/conta/pedidos.php">Meus pedidos</a>
            </div>
            <div class="col-md-1 bottom20 top-50">
                <input type="button" name="btVoltar" value="Voltar" OnClick="window.location='/game/conta/pedidos.php';" class="btn btn-info">
            </div>
        </div>
<?php 
// bloco pagamento efetuado fim  
?>
        <div id="pagamento_cancela">
<?php 
// bloco pagamento cancelado inicio 
?>
            <div class="col-md-11 text-center bottom20">
                <p>Compra <span class="txt-vermelho">cancelada</span> por falta de pagamento.<br>
                    Se ainda quiser realizar a compra, tente novamente e complete o pagamento sem demorar muito.<br>
                Obrigado.</p>
            </div>
            <div class="col-md-1 bottom20">
                <input type="button" name="btVoltar" value="Voltar" OnClick="window.location='/game/';" class="btn btn-info">
            </div>
        </div>
<?php 
// bloco pagamento cancelado fim  
?>
<script language="JavaScript" type="text/JavaScript">
    $("#pagamento_ok").hide();
    $("#pagamento_cancela").hide();
</script>
<?php
    } //end do else do else if($ultimo_status == $GLOBALS['STATUS_VENDA']['VENDA_CANCELADA'])
}
?>
    </div>
</div>
</div>
<?php
require_once DIR_WEB . "game/includes/footer.php";
?>
