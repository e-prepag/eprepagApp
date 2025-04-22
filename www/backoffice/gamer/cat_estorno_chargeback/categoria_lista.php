<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<div class="col-md-12">
    <a href="/gamer/cat_estorno_chargeback/categorias.php?acao=novo" class="btn btn-sm btn-info">Nova Categoria de Estorno e ChargeBack</a>
</div>
<div class="col-md-12 top10">
    <table class="text-center table table-bordered txt-preto">
        <thead class="">
            <tr>
                <th>ID</th>
                <th>Titulo</th>
                <th>Data Cadastro</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody title="Clique para editar">
<?php 
            if(is_array($categorias)){
                foreach($categorias as $categoria){
?>
            <tr class="bannersOpt trListagem c-pointer" id="<?php echo $categoria->getId(); ?>">
                <td><?php echo $categoria->getId(); ?></td>
                <td><?php echo $categoria->getDescricao(); ?></td>
                <td><?php echo $categoria->getDataCadastro(); ?></td>
                <td><?php echo ($categoria->getStatus() == 1) ? "Ativo" : "Inativo"; ?></td>
            </tr>
<?php
                }
            }else{
?>
            <tr>
                <td colspan="5">Nenhum resultado foi encontrado.</td>
            </tr>
<?php
            }
?>
        </tbody>
    </table>
</div>
<script>
    $(function(){
        $(".bannersOpt").click(function(){
            window.location = "/gamer/cat_estorno_chargeback/categorias.php?acao=edita&id="+$(this).attr("id");
        });
    });
</script>