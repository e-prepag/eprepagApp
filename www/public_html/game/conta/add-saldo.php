<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
require_once "../../../includes/constantes.php";
require_once DIR_CLASS . 'gamer/controller/HeaderController.class.php';

$posicao = "Inferior Internas";
$controller = new HeaderController;
$banners = $controller->getBanner($posicao);
$controller->atualizaSessaoUsuario();
$controller->setHeader();

require_once DIR_INCS . "inc_register_globals.php";    

require_once DIR_INCS . "config.MeiosPagamentos.php";

//Definindo valor Default no caso do include estar conrrompido
if(!defined('PAGAMENTO_EPREPAG_CASH')) {
    //Definindo como ativado
    define('PAGAMENTO_EPREPAG_CASH',1);
}// end if

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

$usuarioId = $controller->usuario->getId();

// Para fins de teste, algumas lans com minimo de R$1,00
$valor_minimo = (($controller->usuario->b_IsLogin_pagamento_minimo_1_real())?1:$GLOBALS['RISCO_GAMERS_VALOR_MIN']);
$valor_indicado = (($controller->usuario->b_IsLogin_pagamento_minimo_1_real())?1:$GLOBALS['RISCO_GAMERS_VALOR_MIN']);

?>
<script src="/js/valida.js"></script>
<script type="text/javascript" src="/js/jquery.mask.min.js"></script>
<script language="javascript">

// Mostra status da compra
$(document).ready(function(){
    
    $("#produtos_valor").mask("####", {reverse: true});
    
        $("#produtos_valor").focus();

        $("#btnPagamento").click(function() { 
                var val1;
                val1 = $("#produtos_valor").val();
                if((val1< <?php echo $valor_minimo; ?>) || (val1> <?php echo $total_diario_const; ?>) ) {
                        manipulaModal(1,"Valor digitado fora dos limites (R$<?php echo number_format($valor_minimo, 2, ',', '.'); ?>, R$<?php echo number_format($total_diario_const, 2, ',', '.'); ?>), tente novamente","Erro de preenchimento"); 
                        return false;
                } else {
                        fcnBlockButtons();
                        fcnPagamento();
                }
                return true;
        });

        $(".modal-veja-mais").click(function(){
            manipulaModal(2,"mensagem do veja mais vem aqui","Veja mais");
        });
});
<?php 
if($controller->usuario->b_IsLogin_pagamento()) { 
?>
function fcnBlockButtons(){
		$("#btnPagamento").unbind('click'); 
		$("#btnPagamento").attr('title', 'Aguarde...');
}
function fcnPagamento(){
	document.deposito.submit();
}
<?php
} 
?>
</script>
<div class="container txt-cinza bg-branco  p-bottom40">
    <div class="row top20">
        <div class="col-md-12">
            <span class="glyphicon glyphicon-triangle-right graphycon-big color-blue pull-left"></span>
            <strong class="pull-left top15 color-blue font20">Cartão E-prepag</strong>  
        </div>
    </div>
    <div class="row">
        <div class="col-md-8 ">
            <hr class="border-blue">
        </div>
    </div>
<?php 
//clude "../includes/menu-carteira.php";
?>
    <div class="row">
        <div class="col-md-12 espacamento">
            <?php include RAIZ_DO_PROJETO . "public_html/game/includes/menu-extrato.php"?>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 borda-colunas-formas-pagamento">
<?php
        if($controller->usuario->b_IsLogin_pagamento()) {
?>
            <form id="deposito" classe="form-horizontal" name="deposito" action="/game/credito/meios-pagamento.php" method="post">
                <input type="hidden" name="email" id="email" value="<?php echo $controller->usuario->getEmail()?>">
                <input type="hidden" name="produtos" id="produtos" value="">
                <div class="col-md-12 col-sm-12 h310 top20">
                    <div class="row txt-azul-claro top20">
                        <h4 class="top20">Adicionar saldo</h4></strong>
                    </div>
                    <div class="row top20">
                        <div class="col-xs-12 col-sm-7 col-md-7 col-lg-7 txt-cinza align-left">
                             <strong><label for="produtos_valor" class="control-label top5">Informe o valor que será adicionado: </label></strong>
                        </div>
                        <div class="col-xs-12 col-sm-5 col-md-5 col-lg-5 align-left">
                            <div class="input-group col-xs-10 col-sm-7 col-md-11 col-lg-10">
                                <div class="input-group-addon">R$</div>
                                <input class="form-control" style="text-align: right;"  id="produtos_valor" name="produtos_valor" value="<?php echo $valor_indicado; ?>" maxlength="5" onfocus="this.select();">
                                <div class="input-group-addon">,00</div>
                            </div>
                        </div>
                    </div>
                    <div class="row align-left top10">
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <span class="txt-vermelho p-right15">Adicione R$<?php echo $RISCO_GAMERS_VALOR_MIN_PARA_TAXA;?>,00 ou mais para ter isenção das taxas</span>
                        </div>
                    </div>
                    
                    <input type="submit"  name="btnPagamento" id="btnPagamento" class="btn btn-success btn-block top20" value="Escolha a forma de pagamento"></p>
                </div>
            </form>
<?php 
        }//end if($controller->usuario->b_IsLogin_pagamento())
?>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
            <div class="row  p-left25 txt-azul-claro">
                <h4 class="top20">Utilizar PIN E-PREPAG CASH</h4>
            </div>

            <div class="row top20">
                <div class="col-md-4">
                    <p><img class="" src="/imagens/logo_epp_cash.png" width="100%"></p>
                </div>
                <div class="col-md-7">
                    <p class="txt-azul-claro bottom0"><strong>PIN E-Prepag Cash</strong></p>
                    <p class="txt-laranja t0">Adicione saldo em seu cartão utilizando o pin E-Prepag Cash. <b><a  class="txt-laranja" href="http://blog.e-prepag.com/e-prepag-cash-carteira-virtual/" target="_blank">Consulte aqui</a></b> os jogos que aceitam esta forma de pagamento.</p>
<?php
// Linha PIN E-PREPAG - inicio
if (PAGAMENTO_EPREPAG_CASH) {
?>                          <form id="deposito" name="eppcash" id="eppcash" action="/game/credito/deposito_epp_cash.php" method="post">  
                        <input type="submit" name="btnPagamento" id="btnPagamento" value="Utilizar o PIN ou card" class="btn btn-success">
                    </form>
<?php
}
?>
                </div>
            </div>
        </div>
    </div>
<?php
    if(!empty($banners)){
?>
    <div class="col-md-12 top10">
        <a href='<?php echo $banners[0]->link; ?>' target="_blank">
            <img title="<?php echo $banners[0]->titulo; ?>" alt="<?php echo $banners[0]->titulo; ?>" class="img-responsive" src="<?php echo $controller->objBanners->urlLink.$banners[0]->imagem; ?>">
        </a>
    </div>
<?php 
    } 
?>
</div>
</div>
<?php
require_once RAIZ_DO_PROJETO . "public_html/game/includes/footer.php";
