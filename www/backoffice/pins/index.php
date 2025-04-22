<?php
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
?>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<div class="col-md-6 top10">
    <div class="top10 lista bg-azul-claro txt-branco">
        <strong>Pins</strong>
    </div>
    <ul class="nav nav-pills nav-stacked nav-pills-stacked-example">
        <li role="presentation"><a href="pins_qtde_resta.php" class="menu">Consulta Estoque de Pins</a></li>
        <li role="presentation"><a href="situacao_query.php" class="menu">Consultar Situação do Pin</a></li>
        <li role="presentation"><a href="lote_carga/lotes_pendentes_carga.php" class="menu">Inserir novo lote</a></li>
        <li role="presentation"><a href="pins_transfer_channel.php" class="menu">Alteração do Canal do PIN</a></li>
    </ul>
</div>
<?php require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php"; ?>
</body></html>
