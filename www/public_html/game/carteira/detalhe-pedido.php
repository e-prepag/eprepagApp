<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
require_once "../../../includes/constantes.php";
require_once DIR_CLASS . "gamer/controller/HeaderController.class.php";

$posicao = "Inferior Internas";
$controller = new HeaderController;
$banners = $controller->getBanner($posicao);

$controller->setHeader();

require_once DIR_INCS."inc_register_globals.php";

require_once RAIZ_DO_PROJETO . "public_html/game/pagamento/venda_e_modelos_logica.php";
?>
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
                <div class="col-md-12 top20">
                    <h4 class="margin004"><strong>Detalhe de pedido</strong></h4>
                </div>
            </div>
<?php
require_once RAIZ_DO_PROJETO . "public_html/game/pagamento/venda_e_modelos_view.php"; 

$sql = " 
SELECT valorpagtosaldo,
	valorpagtopin,
	valorpagtogocash
FROM tb_pag_compras 
WHERE tipo_cliente = 'M' AND 
	idvenda=".$venda_id.";";

//Conectando com PDO para execução da QUERY
$con = ConnectionPDO::getConnection();
$pdo = $con->getLink();
$stmt = $pdo->prepare($sql);
$stmt->execute();
$fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);

//Verificando total de registros
$qtde_registros = count($fetch);

$sql = "
SELECT total
FROM tb_pag_compras
WHERE tipo_cliente = 'M' AND 
	idvenda_origem=".$venda_id.";";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$fetch_deposito = $stmt->fetchAll(PDO::FETCH_ASSOC);

if($qtde_registros > 0) {
?>
            <div class="bg-cinza-claro top15-negativo espacamento">
                <div class="row">
                    <div class="col-md-11 text-right txt-preto">
                        <strong>Composição do Pagamento</strong>
                    </div>
                    <div class="col-md-1">

                    </div>
                </div>
<?php
        if($fetch[0]['valorpagtosaldo'] > 0) {
?>                
                <div class="row">
                    <div class="col-md-10 text-right">
                        <strong>Uso do saldo:</strong>
                    </div>
                    <div class="col-md-2 text-right">
                        <strong><?php echo number_format(getEPPCash_from_Currency($fetch[0]['valorpagtosaldo']), 0,",", "."); ?></strong>
                    </div>
                </div>
<?php
        }//end if($fetch[0]['valorpagtosaldo'] > 0) 
        
        if($fetch[0]['valorpagtopin'] > 0) {
?>                
                <div class="row">
                    <div class="col-md-10 text-right">
                        <strong>Pin E-Prepag Cash:</strong>
                    </div>
                    <div class="col-md-2 text-right">
                        <strong><?php echo number_format(getEPPCash_from_Currency($fetch[0]['valorpagtopin']), 0,",", "."); ?></strong>
                    </div>
                </div>
<?php 
        }//end if($fetch[0]['valorpagtopin'] > 0) valorpagtogocash
        if($fetch[0]['valorpagtogocash'] > 0) {
?>                
                <div class="row">
                    <div class="col-md-10 text-right">
                        <strong>Pin E-Prepag Gift:</strong>
                    </div>
                    <div class="col-md-2 text-right">
                        <strong><?php echo number_format(getEPPCash_from_Currency($fetch[0]['valorpagtogocash']), 0,",", "."); ?></strong>
                    </div>
                </div>
<?php 
        }//end if($fetch[0]['valorpagtogocash'] > 0) 
        
        if(count($fetch_deposito)> 0 && $fetch_deposito[0]['total'] > 0 ) {
?>                
                <div class="row">
                    <div class="col-md-10 text-right">
                        <strong>Depósito automático:</strong>
                    </div>
                    <div class="col-md-2 text-right">
                        <strong><?php echo number_format($fetch_deposito[0]['total'], 0,",", "."); ?></strong>
                    </div>
                </div>
<?php 
        } //end if(count($fetch_deposito)> 0 && $fetch_deposito[0]['total'] > 0 ) 
?>
            </div>
<?php
} //end if($qtde_registros > 0)
?>            
            <div class="col-md-12 top20">
                <div class="espacamento text-center">
                    <form method="POST" action="/game/conta/detalhe-pedido.php">
                        <input type="hidden" id="venda_id" name="venda_id" value="<?php echo $venda_id; ?>">
                        <input type="submit" name="processar" id="processar" class="btn btn-info" value="Visualizar Pin">
                    </form> 
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
<?php
require_once RAIZ_DO_PROJETO . "public_html/game/includes/footer.php";