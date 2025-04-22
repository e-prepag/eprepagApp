<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);

require_once "../../../includes/constantes.php";
require_once DIR_CLASS . 'gamer/controller/HeaderController.class.php';

$controller = new HeaderController;
$controller->setHeader();
$controller->atualizaSessaoUsuario();

require_once DIR_INCS . "inc_register_globals.php";    
require_once DIR_INCS . "gamer/functions_endereco.php";
require_once DIR_INCS . "config.MeiosPagamentos.php";

//Definindo valor Default no caso do include estar conrrompido
if(!defined('PAGAMENTO_BRADESCO')) {
    //Definindo como ativado
    define('PAGAMENTO_BRADESCO',1);
}// end if
if(!defined('PAGAMENTO_BANCO_BRASIL')) {
    //Definindo como ativado
    define('PAGAMENTO_BANCO_BRASIL',1);
}// end if
if(!defined('PAGAMENTO_ITAU')) {
    //Definindo como ativado
    define('PAGAMENTO_ITAU',1);
}// end if
if(!defined('PAGAMENTO_BOLETO')) {
    //Definindo como ativado
    define('PAGAMENTO_BOLETO',1);
}// end if
if(!defined('PAGAMENTO_PIX')) {
    //Definindo como ativado
    define('PAGAMENTO_PIX',1);
}// end if

// Para fins de teste, algumas lans com minimo de R$1,00
$valor_minimo = (($controller->usuario->b_IsLogin_pagamento_minimo_1_real())?1:$GLOBALS['RISCO_GAMERS_VALOR_MIN']);
$valor_indicado = (($controller->usuario->b_IsLogin_pagamento_minimo_1_real())?1:$GLOBALS['RISCO_GAMERS_VALOR_MIN']);

//Definindo valor máximo
if($controller->usuario->b_IsLogin_pagamento_free()) {
        $total_diario_const = $RISCO_GAMERS_FREE_TOTAL_DIARIO;
        $pagamentos_diario_const = $RISCO_GAMERS_FREE_PAGAMENTOS_DIARIO;
//	Gamers VIP- Pagamento Online = no max R$1000,00 por día por usuário (ver getVendasMoneyTotalDiarioOnline()) em até 20 vezes
} elseif($controller->usuario->b_IsLogin_pagamento_vip()) {
        $total_diario_const = $RISCO_GAMERS_VIP_TOTAL_DIARIO;
        $pagamentos_diario_const = $RISCO_GAMERS_VIP_PAGAMENTOS_DIARIO;
//	Gamers - Pagamento Online = no max R$450,00 por día por usuário (ver getVendasMoneyTotalDiarioOnline()) em até 10 vezes
} else {
        $total_diario_const = $RISCO_GAMERS_TOTAL_DIARIO;
        $pagamentos_diario_const = $RISCO_GAMERS_PAGAMENTOS_DIARIO;
}

//Validando valor informado
if($produtos_valor< $valor_minimo || $produtos_valor> $total_diario_const ) { 
?>
<script src="/js/valida.js"></script>
<script>
        manipulaModal(1,"Error: Valor digitado fora dos limítes (R$<?php echo number_format($valor_minimo, 2, ',', '.'); ?>, R$<?php echo number_format($total_diario_const, 2, ',', '.'); ?>), tente novamente","Erro"); 
        $('#modal-load').on('hidden.bs.modal', function () { location.href='/game/conta/add-saldo.php' });
</script>
<?php
        die();
}//end if($produtos_valor< $valor_minimo || $produtos_valor> $total_diario_const )

if($btSubmit || $iforma){
    
        if(empty($pagto)) $pagto = $iforma;
            
        //Validacao
        $msg = "";

        //Valida opcao de pagamento
        if(!$pagto || $pagto == "" || (strlen($pagto)!=1)) $msg = "Selecione a forma de pagamento.";

        //Validacao formas de pagamento		
        if($msg == ""){
                if(!in_array($pagto, $FORMAS_PAGAMENTO)) $msg = "Forma de pagamento inválida.";
        }

        //Adiciona dados no session
        if($msg == ""){

                $_SESSION['pagamento.pagto'] = $pagto;
                $_SESSION['pagamento.total'] = $produtos_valor;
                $instConversionPINsEPP = new ConversionPINsEPP;
                $_SESSION['pagamento.total_eppcash'] = $instConversionPINsEPP->get_ValorEPPCash('E', $produtos_valor);
                $_SESSION['pagamento.taxa'] = getTaxaPagtoOnline($iforma, $produtos_valor);

                unset($_SESSION['pagamento.numorder']);
                unset($_SESSION['pagamento.pagto_ja_fiz']);
                unset($_SESSION['pagamento.parcelas.REDECARD_MASTERCARD']);
                unset($_SESSION['pagamento.parcelas.REDECARD_DINERS']);
                
                if(($pagto == $FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) && 
                   (trim($controller->usuario->getCEP()) == "" || 
                    trim($controller->usuario->getEndereco()) == "" ||  
                    trim($controller->usuario->getNumero()) == "" || 
                    trim($controller->usuario->getBairro()) == "" || 
                    trim($controller->usuario->getCidade()) == "" || 
                    trim($controller->usuario->getEstado()) == "") )
                {
                    $completar_endereco = true;
                }
                
                if($completar_endereco){
?>
                    <div class="container txt-azul-claro bg-branco">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-12 txt-azul-claro top10">
                                        <span class="glyphicon glyphicon-triangle-right graphycon-big" aria-hidden="true"></span><strong>Atualização Dados de Endereço</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 espacamento">
<?php
                            endereco_page_transf($completar_endereco);
                }
                else{
                    //redireciona
                    $strRedirect = "/game/pagamento/finaliza_deposito.php";
                    redirect($strRedirect);
                }

        } //end if($msg == "")
        else {
?>
<script src="/js/valida.js"></script>
<script>
        manipulaModal(1,"<?php echo $msg; ?>","Erro"); 
        $('#modal-load').on('hidden.bs.modal', function () { location.href='/game/conta/add-saldo.php' });
</script>
<?php
            die();
        }//end else do if($msg == "")

}//end if($btSubmit || $iforma)

if($controller->usuario->b_IsLogin_pagamento()) {

        // Obtem o valor total deste pedido
        $total_carrinho = $produtos_valor;

        // ==========================================================================================
        // Faz validação de vendas totais, repetir em finaliza_vendas.php, antes de aceitar o pedido

        // Testa que usuário comprou no máximo 10 vezes nas últimas 24 horas
        $qtde_last_dayOK = getNVendasMOney($controller->usuario->getId());

        // Calcula o total nas últimas 24 horas para pagamentos Online 
        $total_diario = getVendasMoneyTotalDiarioOnline($controller->usuario->getId());

        $b_TentativasDiariasOK = ($qtde_last_dayOK<=$RISCO_GAMERS_SALDO_PAGAMENTOS_DIARIO);
        $b_LimiteDiarioOK = (($total_carrinho+$total_diario)<=$RISCO_GAMERS_SALDO_TOTAL_DIARIO);
        $b_ValorBoletoOK = ($total_carrinho<=$RISCO_GAMERS_BOLETOS_TOTAL_DIARIO);
        $b_ValorDepositoOK = ($total_carrinho<=$RISCO_GAMERS_DEPOSITOS_TOTAL_DIARIO);

        // Libera pagamento Online Banco do Brasil
        $b_libera_BancodoBrasil = $b_LimiteDiarioOK && $b_TentativasDiariasOK;// && $controller->usuario->b_IsLogin_pagamento_bancodobrasil();

        // Libera pagamento Online Banco Itaú
        $b_libera_BancoItau = $b_LimiteDiarioOK && $b_TentativasDiariasOK;// && $controller->usuario->b_IsLogin_pagamento_bancoitau();

        // Libera Bradesco apenas se limite diario não ultrapassado //produtos (Habbo e GPotato) e tem até 5 compras nas últimas 24 horas
        $b_libera_Bradesco = $b_LimiteDiarioOK && $b_TentativasDiariasOK;	//$b_IsProdutoOK && 

        // Libera pagamento pix
        $b_libera_Pix = $b_LimiteDiarioOK && $b_TentativasDiariasOK;
        
        // Libera pagamento Online Hipay
        $b_libera_Hipay = false;	//$b_LimiteDiarioOK && $b_TentativasDiariasOK && $controller->usuario->b_IsLogin_pagamento_hipay();

        // Libera pagamento Online Paypal
        $b_libera_Paypal = false;	//$b_LimiteDiarioOK && $b_TentativasDiariasOK && $controller->usuario->b_IsLogin_pagamento_paypal();

        // Libera Boleto apenas se o valor da venda não ultrapassa o limite por venda
        $b_libera_Boleto = $b_ValorBoletoOK;	

        // Libera Depósito apenas se o valor da venda não ultrapassa o limite por venda
        $b_libera_Deposito = $b_ValorDepositoOK;	

        $msg_bloqueia_Bradesco = (!$b_libera_Bradesco)?((!$b_LimiteDiarioOK)?"<p class='txt-azul fontsize-pp'>Sua compra de ".number_format($total_carrinho, 2, ',', '.')." ultrapassa o limite diário de compras on-line.</p>":((!$b_TentativasDiariasOK)?"<p class='txt-azul fontsize-pp'>Número de pagamentos online (".$qtde_last_dayOK.") ultrapassa o limite diário.</p>":"")):"";

        $msg_bloqueia_BancodoBrasil = (!$b_libera_BancodoBrasil)?((!$b_LimiteDiarioOK)?"<p class='txt-azul fontsize-pp'>Sua compra de ".number_format($total_carrinho, 2, ',', '.')." ultrapassa o limite diário de compras on-line.</p>":((!$b_TentativasDiariasOK)?"<p class='txt-azul fontsize-pp'>Número de pagamentos online (".$qtde_last_dayOK.") ultrapassa o limite diário.</p>":"")):"";

        $msg_bloqueia_BancoItau = (!$b_libera_BancoItau)?((!$b_LimiteDiarioOK)?"<p class='txt-azul fontsize-pp'>Sua compra de ".number_format($total_carrinho, 2, ',', '.')." ultrapassa o limite diário de compras on-line.</p>":((!$b_TentativasDiariasOK)?"<p class='txt-azul fontsize-pp'>Número de pagamentos online (".$qtde_last_dayOK.") ultrapassa o limite diário.</p>":"")):"";

        $msg_bloqueia_Pix= (!$b_libera_Pix)?((!$b_LimiteDiarioOK)?"<p class='txt-azul fontsize-pp'>Sua compra de ".number_format($total_carrinho, 2, ',', '.')." ultrapassa o limite diário de compras on-line.</p>":((!$b_TentativasDiariasOK)?"<p class='txt-azul fontsize-pp'>Número de pagamentos online (".$qtde_last_dayOK.") ultrapassa o limite diário.</p>":"")):"";
        
        $msg_bloqueia_Boleto = (!$b_libera_Boleto)? "<p class='txt-azul fontsize-pp'>Sua compra de ".number_format($total_carrinho, 2, ',', '.')." ultrapassa o limite de compras por boleto.</p>":"";

        $msg_bloqueia_Deposito = (!$b_libera_Deposito)? "<p class='txt-azul fontsize-pp'>Sua compra de ".number_format($total_carrinho, 2, ',', '.')." ultrapassa o limite de compras por depósito.</p>":"";
        
        if(!$b_TentativasDiariasOK || !$b_LimiteDiarioOK) {
                $msg_block = "Pagamento Online BLOQUEADO (Gamer) ******  ";
        } else {
                $msg_block = "Pagamento Online (Gamer) PERMITIDO ++++++  ";
        }
        $mensagem= "=====================================================================================\n".
                "$msg_block (".date("Y-m-d H:i:s").")\n".
                "  Usuário: ID: ".$controller->usuario->getId().", Nome: ".$controller->usuario->getNome().", Email: ".$controller->usuario->getEmail().",\n".
                "  qtde_last_dayOK: ".$qtde_last_dayOK."\n".
                "  total_diario: ".number_format($total_diario, 2, ',', '.')."\n".
                "  total_carrinho+total_diario: ".number_format(($total_carrinho+$total_diario), 2, ',', '.')."\n".
                "  b_TentativasDiariasOK: ".($b_TentativasDiariasOK?"OK":"nope")."\n".
                "  b_LimiteDiarioOK: ".($b_LimiteDiarioOK?"OK":"nope")."\n".
                "  \n".
                "  b_libera_BancodoBrasil: ".($b_libera_BancodoBrasil?"OK":"nope")."\n".
                "  b_libera_Bradesco: ".($b_libera_Bradesco?"OK":"nope")."\n".
                "  RISCO_GAMERS_SALDO_PAGAMENTOS_DIARIO: ".$RISCO_GAMERS_SALDO_PAGAMENTOS_DIARIO."\n".
                "  RISCO_GAMERS_SALDO_TOTAL_DIARIO: ".number_format($RISCO_GAMERS_SALDO_TOTAL_DIARIO, 2, ',', '.')."\n".
                "\n";
        gravaLog_BloqueioPagtoOnline($mensagem);

        // finaliza validações
        // ==========================================================================================
?>
<script language="Javascript">
	function save_shipping(iforma, id, sno) {
		document.form1.iforma.value = iforma;
		document.form1.idu.value = id;
		document.form1.sno.value = sno;
		document.form1.btSubmit.value = "Continuar";

		document.form1.submit();
	}
</script>
<?php
} //end if($controller->usuario->b_IsLogin_pagamento())

?>
<div class="container txt-azul-claro bg-branco">
    <div class="row">
        <div class="col-md-12 col-xs-12 col-sm-12 col-lg-12">
            <div class="row">
                <div class="col-md-12  col-xs-12 col-sm-12 col-lg-12 txt-azul-claro top20">
                    <span class="glyphicon glyphicon-triangle-right graphycon-big pull-left" aria-hidden="true"></span><strong class="pull-left"><h4 class="top20">adicionar saldo</h4></strong>
                </div>
                <div class="clearfix"></div>
                <div class="col-md-8 col-xs-12 col-sm-12 col-lg-8 txt-cinza top20">
                    <strong>Valor selecionado: <span class="txt-verde">R$ <?php echo number_format($produtos_valor, 2, ',', '.');?></span></strong>
                </div>
            </div>
            <div class="row  espacamento top20 borda-top-azul">
                <div class="col-md-12 txt-azul-claro ">
                    <strong>Escolha o meio de pagamento</strong>
                </div>
            </div>
            <form id="form1" name="form1" method="post">
                <input type="hidden" name="produtos_valor" id="produtos_valor" value="<?php echo $produtos_valor; ?>" />
		<input type="hidden" name="pagto" id="pagto" value="<?php echo $pagto ?>" />
                <input type="hidden" name="iforma" id="iforma" value="0">
                <input type="hidden" name="idu" id="idu" value="0">
                <input type="hidden" name="sno" id="sno" value="0">
                <input type="hidden" name="tipo" id="tipo" value="adicao_gamer">
                <input type="hidden" name="btSubmit" id="btSubmit" value="Continuar">
                <div class="row espacamento">
                    <div class="col-md-2 borda-colunas-formas-pagamento text-center">
                        <p class=" borda-linhas-formas-pagamento">
                            <img src="/imagens/pag/pagto_forma_pix.gif">
                        </p>
<?php                        
                //Constante do configurador de meios de pagamentos
                if($b_libera_Pix && PAGAMENTO_PIX) {
?>
                        <p>
                            <img src="/imagens/pag/pagto_forma_transferencia_1.gif" 
                                 name="btn_24" 
                                 onmouseover="document.btn_24.src='/imagens/pag/pagto_forma_transferencia_2.gif'" 
                                 onmouseout="document.btn_24.src='/imagens/pag/pagto_forma_transferencia_1.gif'" 
                                 width="110" height="35" border="0" 
                                 title="<?php echo $FORMAS_PAGAMENTO_DESCRICAO[$FORMAS_PAGAMENTO['PAGAMENTO_PIX']] ?>"
                                 class="c-pointer btnPgto"
                                 onClick="save_shipping('<?php echo $FORMAS_PAGAMENTO['PAGAMENTO_PIX'] ?>', <?php echo (($controller->usuario->getId()>0)?$controller->usuario->getId():"0") ?>, '<?php echo $controller->usuario->getNome() ?>');">
                        </p>
<?php
                    if($produtos_valor < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA && $PAGAMENTO_PIX_TAXA != 0) {
                            echo "<p class='txt-cinza fontsize-pp bottom0'>Taxa de serviço: R$ ".number_format($PAGAMENTO_PIX_TAXA, 2, ',', '.')."</p>";
                    }//end if($produtos_valor < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
                    else {
                        echo "<p class='txt-vermelho fontsize-pp bottom0'>Sem taxa de serviço</p>";
                    }//end else do if($produtos_valor < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
?>
                        <p class="txt-verde fontsize-pp">Entrega em até 30 minutos.</p>
                        <span style='display:none'><input type="radio" name="pagto" value="<?php echo $FORMAS_PAGAMENTO['PAGAMENTO_PIX']?>" <?php if($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_PIX']) echo " checked"; ?>></span>
<?php
                }//end if($b_libera_Boleto && PAGAMENTO_BOLETO)
                else { 
                        if(!PAGAMENTO_BOLETO) {
                                echo "<p class='txt-azul fontsize-pp'>Serviço indisponível no momento</p>";
                        }
                        echo $msg_bloqueia_Boleto;
                }
?>
                </div>
                <!--<div class="row espacamento">-->
                    <div class="col-md-2 borda-colunas-formas-pagamento text-center">
                        <p class=" borda-linhas-formas-pagamento">
                            <img src="/imagens/pag/pagto_boleto.gif">
                        </p>
<?php                        
                //Constante do configurador de meios de pagamentos
                if($b_libera_Boleto && PAGAMENTO_BOLETO) {
?>
                        <p>
                            <img src="/imagens/pag/pagto_forma_boleto_1.gif" 
                                 name="btn_2" 
                                 onmouseover="document.btn_2.src='/imagens/pag/pagto_forma_boleto_2.gif'" 
                                 onmouseout="document.btn_2.src='/imagens/pag/pagto_forma_boleto_1.gif'" 
                                 width="110" height="35" border="0" 
                                 title="Boleto Bancário" 
                                 class="c-pointer btnPgto"
                                 onClick="save_shipping(<?php echo $FORMAS_PAGAMENTO['BOLETO_BANCARIO'] ?>, <?php echo (($controller->usuario->getId()>0)?$controller->usuario->getId():"0") ?>, '<?php echo $controller->usuario->getNome() ?>');">
                        </p>
<?php
                    if($produtos_valor < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA && $BOLETO_TAXA_ADICIONAL != 0) {
                            echo "<p class='txt-cinza fontsize-pp bottom0'>Taxa de serviço: R$ ".number_format($BOLETO_TAXA_ADICIONAL, 2, ',', '.')."</p>";
                    }//end if($produtos_valor < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
                    else {
                        echo "<p class='txt-vermelho fontsize-pp bottom0'>Sem taxa de serviço</p>";
                    }//end else do if($produtos_valor < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
?>
                        <p class="txt-verde fontsize-pp">Entrega em até 2 dias úteis.</p>
                        <span style='display:none'><input type="radio" name="pagto" value="<?php echo $FORMAS_PAGAMENTO['BOLETO_BANCARIO']?>" <?php if($pagto == $FORMAS_PAGAMENTO['BOLETO_BANCARIO']) echo " checked"; ?>></span>
<?php
                }//end if($b_libera_Boleto && PAGAMENTO_BOLETO)
                else { 
                        if(!PAGAMENTO_BOLETO) {
                                echo "<p class='txt-azul fontsize-pp'>Serviço indisponível no momento</p>";
                        }
                        echo $msg_bloqueia_Boleto;
                }
?>
                    </div>
                    <div class="col-md-2 borda-colunas-formas-pagamento hidden-xs hidden-sm text-center">
                        <p class=" borda-linhas-formas-pagamento">
                            <img src="/imagens/pag/pagto_bradesco.gif">
                        </p>
<?php
                if($b_libera_Bradesco && PAGAMENTO_BRADESCO) { 
?>
                        <p>
                            <img class="c-pointer btnPgto" 
                                 src="/imagens/pag/pagto_forma_transferencia_1.gif" 
                                 name="btn_5"
                                 onMouseOver="document.btn_5.src='/imagens/pag/pagto_forma_transferencia_2.gif'" 
                                 onMouseOut="document.btn_5.src='/imagens/pag/pagto_forma_transferencia_1.gif'" 
                                 title="Bradesco pagamento (Transferência entre contas)"
                                 onClick="save_shipping(<?php echo $FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO'] ?>, <?php echo (($controller->usuario->getId()>0)?$controller->usuario->getId():"0") ?>, '<?php echo $controller->usuario->getNome() ?>');">
                        </p>
<?php
                    if($produtos_valor < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA && $BRADESCO_TRANSFERENCIA_ENTRE_CONTAS_TAXA_ADICIONAL != 0) {
                            echo "<p class='txt-cinza fontsize-pp bottom0'>Taxa de serviço: R$ ".number_format($BRADESCO_TRANSFERENCIA_ENTRE_CONTAS_TAXA_ADICIONAL, 2, ',', '.')."</p>";
                    }//end if($produtos_valor < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
                    else {
                        echo "<p class='txt-vermelho fontsize-pp bottom0'>Sem taxa de serviço</p>";
                    }//end else do if($produtos_valor < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
?>
                        <p class="txt-verde fontsize-pp">Entrega em até 90 minutos</p>
                        <span style='display:none'><input type="radio" name="pagto" value="<?php echo $FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']?>" <?php if($pagto == $FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) echo " checked"; ?>></span>
<?php
                } //end if($b_libera_Bradesco && PAGAMENTO_BRADESCO)
                else { 
                        if(!PAGAMENTO_BRADESCO) {
                                echo "<p class='txt-azul fontsize-pp'>Serviço indisponível no momento</p>";
                        }
                        echo $msg_bloqueia_Bradesco;
                }
?>
                    </div>
                    <div class="col-md-2 borda-colunas-formas-pagamento hidden-xs hidden-sm text-center">
                        <p class=" borda-linhas-formas-pagamento">
                            <img src="/imagens/pag/pagto_bancodobrasil.gif">
                        </p>
<?php                
                if($b_libera_BancodoBrasil && PAGAMENTO_BANCO_BRASIL) {	
?>
                         <p>
                            <img name="btn_9" class="c-pointer btnPgto" 
                            onMouseOver="document.btn_9.src='/imagens/pag/pagto_forma_debito_2.gif'" 
                            onMouseOut="document.btn_9.src='/imagens/pag/pagto_forma_debito_1.gif'" 
                            src="/imagens/pag/pagto_forma_debito_1.gif"  
                            title="<?php echo $FORMAS_PAGAMENTO_DESCRICAO[$FORMAS_PAGAMENTO['PAGAMENTO_BB_DEBITO_SUA_CONTA']] ?>"
                            onClick="save_shipping(<?php echo $FORMAS_PAGAMENTO['PAGAMENTO_BB_DEBITO_SUA_CONTA'] ?>, <?php echo (($controller->usuario->getId()>0)?$controller->usuario->getId():"0") ?>, '<?php echo $controller->usuario->getNome() ?>');">
                        </p>
<?php
                    if($produtos_valor < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA && $BANCO_DO_BRASIL_TAXA_DE_SERVICO != 0) {
                            echo "<p class='txt-cinza fontsize-pp bottom0'>Taxa de serviço: R$ ".number_format($BANCO_DO_BRASIL_TAXA_DE_SERVICO, 2, ',', '.')."</p>";
                    }//end if($produtos_valor < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
                    else {
                        echo "<p class='txt-vermelho fontsize-pp bottom0'>Sem taxa de serviço</p>";
                    }//end else do if($produtos_valor < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
?>
                        <p class="txt-verde fontsize-pp">Entrega em até 30 minutos</p>
                        <span style='display:none'><input type="radio" name="pagto" value="<?php echo $FORMAS_PAGAMENTO['PAGAMENTO_BB_DEBITO_SUA_CONTA']?>" <?php if($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_BB_DEBITO_SUA_CONTA']) echo " checked"; ?>></span>
<?php	
                } //end if($b_libera_BancodoBrasil && PAGAMENTO_BANCO_BRASIL)
                else {
                        if(!PAGAMENTO_BANCO_BRASIL) {
                                echo "<p class='txt-azul fontsize-pp'>Serviço indisponível no momento</p>";
                        }
                        echo $msg_bloqueia_BancodoBrasil;
                } 
?>
                    </div>
                    <div class="col-md-2 borda-colunas-formas-pagamento hidden-xs hidden-sm text-center">
                        <p class=" borda-linhas-formas-pagamento">
                            <img src="/imagens/pag/pagto_itau_shopline.gif">
                        </p>
<?php                        
                if($b_libera_BancoItau && PAGAMENTO_ITAU) {
?>
                        <p>
                            <img name="btn_10" src="/imagens/pag/pagto_forma_transferencia_1.gif" class="c-pointer btnPgto"
                            onMouseOver="document.btn_10.src='/imagens/pag/pagto_forma_transferencia_2.gif'" 
                            onMouseOut="document.btn_10.src='/imagens/pag/pagto_forma_transferencia_1.gif'"
                            title="<?php echo $FORMAS_PAGAMENTO_DESCRICAO[$FORMAS_PAGAMENTO['PAGAMENTO_BANCO_ITAU_ONLINE']] ?>" 
                            onClick="save_shipping('<?php echo $FORMAS_PAGAMENTO['PAGAMENTO_BANCO_ITAU_ONLINE'] ?>', <?php echo (($controller->usuario->getId()>0)?$controller->usuario->getId():"0") ?>, '<?php echo $controller->usuario->getNome() ?>');">
                        </p>
<?php
                    if($produtos_valor < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA && $BANCO_ITAU_TAXA_DE_SERVICO != 0) {
                            echo "<p class='txt-cinza fontsize-pp bottom0'>Taxa de serviço: R$ ".number_format($BANCO_ITAU_TAXA_DE_SERVICO, 2, ',', '.')."</p>";
                    }//end if($produtos_valor < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
                    else {
                        echo "<p class='txt-vermelho fontsize-pp bottom0'>Sem taxa de serviço</p>";
                    }//end else do if($produtos_valor < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
?>
                        <p class="txt-verde fontsize-pp">Entrega em até 30 minutos</p>
                        <span style='display:none'><input type="radio" name="pagto" value="<?php echo $FORMAS_PAGAMENTO['PAGAMENTO_BANCO_ITAU_ONLINE']?>" <?php if($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_BANCO_ITAU_ONLINE']) echo " checked"; ?>></span>
<?php
                } //end if($b_libera_BancoItau && PAGAMENTO_ITAU)
                else {
                        if(!PAGAMENTO_ITAU) {
                                echo "<p class='txt-azul fontsize-pp'>Serviço indisponível no momento</p>";
                        }
                        echo $msg_bloqueia_BancoItau;
                } 
?>
                    </div>
                    <div class="col-xs-12 col-sm-12 visible-xs visible-sm text-center top50">
                    <span class="txt-azul text-center"><b>Obs.:</b> Outros meios de pagamentos podem estar disponíveis acessando por desktop/notebook</span>
                </div>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
<?php
require_once RAIZ_DO_PROJETO . "public_html/game/includes/footer.php";
?>