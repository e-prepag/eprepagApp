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

<?php
require_once $raiz_do_projeto."/class/classDescriptionReport.php";
$descricao = new DescriptionReport();
echo $descricao->MontaAreaDescricaoTodos();
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</body>
</html>