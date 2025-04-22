<?php
require_once "../../includes/constantes.php";
require_once DIR_CLASS."gamer/controller/HeaderController.class.php";

$controller = new HeaderController;
$controller->setHeader();

require_once "includes/termos-de-uso.php";
?>
<div class="container txt-azul-claro bg-branco p-bottom40">
    <div class="col-md-12 txt-azul-claro top10">
        <span class="glyphicon glyphicon-triangle-right graphycon-big pull-left" aria-hidden="true"></span><strong class="pull-left"><h4 class="top20">Termos de Uso</h4></strong>
    </div>
    <div class="col-md-12 top50 txt-cinza">
        <?php echo $termosDeUso;?>
    </div>
</div>
</div>
<?php
require_once RAIZ_DO_PROJETO . "public_html/game/includes/footer.php";