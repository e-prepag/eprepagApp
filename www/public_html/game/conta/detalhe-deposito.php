<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);

require_once "../../../includes/constantes.php";
require_once DIR_CLASS . "gamer/controller/HeaderController.class.php";

$posicao = "Inferior Internas";
$controller = new HeaderController;
$banners = $controller->getBanner($posicao);

$controller->setHeader();

require_once DIR_INCS . "inc_register_globals.php";

// Marca esta venda como deposito.em.saldo, para uso em venda_e_modelos_logica.php
$_SESSION['pagamento.pagto.deposito.em.saldo'] = 3;
$_SESSION['pagamento.pagto.deposito.em.saldo.num.docto'] = true;

require_once DIR_INCS."inc_register_globals.php";

require_once RAIZ_DO_PROJETO . "public_html/game/pagamento/venda_e_modelos_logica.php";

while ($rs_venda_modelos_row = pg_fetch_array($rs_venda_modelos)){
    $icone = getIconeParaPagtoGamer($rs_venda_modelos_row['vg_pagto_tipo']);
    $msg_icone = getDescricaoPagtoOnline($rs_venda_modelos_row['vg_pagto_tipo']);
}

?>
<script>
$(function(){
    $(".detalheDeposito").click(function(){
        $("#DEPOSITOS_EM_PROCESSAMENTO").submit();
    });
});
</script>
<div class="container txt-cinza bg-branco  p-bottom40">
    <div class="row top20">
        <div class="row">
            <div class="col-md-3 txt-azul-claro">
                <span class="glyphicon glyphicon-triangle-right graphycon-big pull-left" aria-hidden="true"></span><strong class="pull-left"><h4 class="top20">Cartão E-Prepag</h4></strong>
            </div>
        </div>
        <?php //include "../includes/menu-carteira.php"?>
        <div class="col-md-12 espacamento">
            <?php require_once RAIZ_DO_PROJETO . "public_html/game/includes/menu-extrato.php"?>
            <div class="row txt-cinza">
                <div class="col-md-12 espacamento">
                    <h4 class="margin004"><strong>Detalhe de depósito</strong></h4>
                </div>
            </div>
<?php
require_once RAIZ_DO_PROJETO . "public_html/game/pagamento/venda_e_modelos_view.php"; 
?>
            
            <div class="row">
                <div class="col-md-12 txt-azul-claro ">
                    <h5><strong>Forma de pagamento: <span class="txt-cinza"><img title="<?php echo $msg_icone;?>" src="<?php echo $icone;?>"></span></strong> </h5>
                </div>
            </div>
<?php
$sql = " 
SELECT total,
	idvenda,
        idvenda_origem
FROM tb_pag_compras 
WHERE tipo_cliente = 'M' AND 
	idvenda=".$venda_id.";";

//Conectando com PDO para execução da QUERY
$con = ConnectionPDO::getConnection();
$pdo = $con->getLink();
$stmt = $pdo->prepare($sql);
$stmt->execute();
$fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);

if(count($fetch) > 0 && $fetch[0]['idvenda_origem'] > 0) {
?>            
            <form id="DEPOSITOS_EM_PROCESSAMENTO" name="DEPOSITOS_EM_PROCESSAMENTO" method="post" action="/game/conta/detalhe-pedido.php">
                <input type="hidden" id="venda_id" name="venda_id" value="<?php echo $fetch[0]['idvenda_origem']; ?>">
                <div class="row txt-cinza espacamento">
                    <div class="col-md-12 bg-cinza-claro">
                        <div class="bg-branco top20 bottom20 espacamento">
                            <p class=""><strong>Depósito: <?php echo number_format($fetch[0]['total'], 0,",", "."); ?> E-Prepag Cash</strong></p>
                            <p class="p10">Depósito automático referente ao pedido <a href="javascript:void(0);" class="detalheDeposito"><?php echo $fetch[0]['idvenda_origem']; ?></a> pago com PIN de E-Prepag Cash.</p>
                            <p class="p10"><a href="javascript:void(0);" class="detalheDeposito">Veja aqui</a> mais detalhes sobre o pedido.</p>
                        </div>
                    </div>
                </div>
            </form>
<?php 
} //end if(count($fetch) > 0) 
?>            
        </div>
    </div>
<?php
    unset($GLOBALS['_SESSION']['pagamento.pagto.deposito.em.saldo']);
    unset($GLOBALS['_SESSION']['pagamento.pagto.deposito.em.saldo.num.docto']);
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