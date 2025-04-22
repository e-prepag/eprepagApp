<?php 
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
require_once "../../includes/constantes.php";
require_once DIR_CLASS . "pdv/controller/SaldoController.class.php";

$controller = new SaldoController;

$banner = $controller->getBanner();

require_once "includes/header.php";

// Para fins de teste, algumas lans com minimo de R$1,00
$valor_minimo_0 = (($controller->usuarios->b_IsLogin_pagamento_minimo_1_real())?1:$GLOBALS['RISCO_LANS_PRE_VALOR_MIN']);
$valor_maximo_0 = (($controller->usuarios->b_IsLogin_pagamento_vip()) ? $GLOBALS['RISCO_LANS_PRE_VIP_VALOR_MAX'] : (($controller->usuarios->b_IsLogin_pagamento_master()) ? $GLOBALS['RISCO_LANS_PRE_MASTER_VALOR_MAX'] : (($controller->usuarios->b_IsLogin_pagamento_black()) ? $GLOBALS['RISCO_LANS_PRE_BLACK_VALOR_MAX'] : (($controller->usuarios->b_IsLogin_pagamento_gold()) ? $GLOBALS['RISCO_LANS_PRE_GOLD_VALOR_MAX'] : $GLOBALS['RISCO_LANS_PRE_VALOR_MAX']))));


	if($controller->usuarios->b_IsLogin_pagamento_platinum()) {
		$valor_maximo_0 = 120000;
	}

?>
<div id="modal-veja-mais" class="modal fade" role="dialog">
        <div class="modal-dialog" role="document">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                  <h5 class="modal-title txt-azul-claro"><strong>Informações sobre limites de adição de saldo</strong></h5>
                </div>
                <div class="modal-body txt-verde">
                    <p class="txt-cinza">O limite estabelecido para adição de saldo é de no mínimo R$ <?php echo number_format($valor_minimo_0, 2, ',', '.');?>  por dia. Se você precisa de um limite maior, entre em contato com nosso <a href="https://<?php echo $_SERVER["SERVER_NAME"] ?>/game/suporte.php">suporte</a>.</p>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
</div>
<div class="container txt-azul-claro bg-branco">
    <div class="row">
        <div class="col-md-7">
            <div class="row">
                <div class="col-md-12 espacamento">
                    <strong>ADICIONAR SALDO</strong>
                </div>
            </div>
            <div class="row espacamento borda-fina">
                <form action="/creditos/formas_pagamento.php" id="form1" method="post">
                    <div class="col-md-12">
                        <p class="txt-cinza"><strong>Selecione o valor que deseja adicionar</strong></p>
                        <p><strong>R$ <input class="widthInputMoeda" id="produtos_valor" value="<?php echo $valor_minimo_0; ?>" name="produtos_valor">,00</strong></p>
                        <p class="txt-cinza">Mínimo de R$ <?php echo number_format($valor_minimo_0, 2, ',', '.');?> - <a data-toggle="modal" data-target="#modal-veja-mais" href="#" class="veja-mais">Veja mais</a></p>
                        <p><input type="button" id="btnPagamento" class="btn btn-success" value="Escolha a forma de pagamento"></p>
                        <p class="txt-cinza bottom0">Após confirmação de pagamento o saldo será</p>
                        <p class="txt-cinza">creditado diretamente em sua conta</p>
                    </div>
                </form>
            </div>

        </div>
        <div class="col-md-5 p-top10">
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-12 espacamento">
                        <strong>SUPORTE PARA PDVs</strong>
                    </div>
                    <div class="col-md-12 espacamento">
                        <div class="row p-3"><img src="/imagens/skype_icon.png"/><strong> eprepagpdv</strong></div>
                        <div class="row p-3"><a href="http<?php if($_SERVER['HTTPS']=="on") { echo "s"; } ?>://<?php echo $_SERVER["SERVER_NAME"] ?>/game/suporte.php" target="_blank"><img src="/imagens/lh-support.png"/><strong> Área de suporte</strong></a></div>
                        <div class="row p-3"><a href="mailto:suporte@e-prepag.com.br" target="_blank"><img src="/imagens/lh-email.png"/><strong> Escrever e-mail</strong></a></div>
                        <div class="row p-3"><a href="http<?php if($_SERVER['HTTPS']=="on") { echo "s"; } ?>://www.facebook.com/eprepagcash/" target="_blank"><img src="/imagens/lh-fb.png"/><strong> Acessar página de PDV</strong></a></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 hidden-sm hidden-xs">
<?php 
            if($banner){
                foreach($banner as $b){
?>
                <div class="row pull-right">
                    <a href="<?php echo $b->link; ?>" class="banner" id="<?php echo $b->id; ?>" target="_blank"><img src="<?php echo $controller->objBanner->urlLink.$b->imagem; ?>" width="186" class="p-3" title="<?php echo $b->titulo; ?>"></a>
                </div>
<?php 
                }
            }
?>
                <div class="row pull-right facebook"></div>
            </div>
        </div>
    </div>
</div>
<script language="javascript">
    // Mostra status da compra
    $(document).ready(function(){
        $("#produtos_valor").focus()
                            .keydown(function(e)
        {
            var tecla = e.keyCode;
            if((tecla >= 96 && tecla <= 105) || (tecla >= 48 && tecla <= 57) || tecla == 8 || tecla == 46 || tecla == 13){
                return true;
            }else{
                return false;
            }
        });
        
        $("#btnPagamento").click(function() {
            $("#form1").trigger("submit");
        });
        
        $("#form1").submit(function(){
            var val1 = $("#produtos_valor").val();
            if((val1< <?php echo $valor_minimo_0 ?>) || (val1> <?php echo $valor_maximo_0 ?>) ) {
                alert("Valor digitado fora dos limítes (R$<?php echo number_format($valor_minimo_0, 2, ',', '.');?>, R$<?php echo number_format($valor_maximo_0, 2, ',', '.')?>), tente novamente");
                $("#produtos_valor").val(<?php echo $valor_minimo_0 ?>);
                return false;
            } else {
                return true;
            }
        });
    });
</script>
<script src="/js/facebook.js"></script>
<?php
require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/footer.php";
?>