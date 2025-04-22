<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>  
<div class="col-md-12">
        <a href="banners_posicoes.php?acao=novo" class="btn btn-sm btn-info">Nova Posição</a>
</div>
<div class="col-md-12 txt-preto">
    <table class="table top10 table-bordered bordaTabela" >
        <thead class="">
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Tamanho</th>
                <th>Data Cadastro</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody title="Clique para editar">
<?php 
            if(is_array($posicoes)){
                foreach($posicoes as $posicao){
?>
            <tr class="bannersOpt c-pointer trListagem" id="<?php echo $posicao->getId(); ?>">
                <td><?php echo $posicao->getId(); ?></td>
                <td><?php echo $posicao->getDescricao(); ?></td>
                <td><?php echo $posicao->getTamanho(); ?></td>
                <td><?php echo $posicao->getDataCadastro(); ?></td>
                <td><?php echo ($posicao->getStatus() == 1) ? "Ativo" : "Inativo"; ?></td>
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
            window.location = "banners_posicoes.php?acao=edita&id="+$(this).attr("id");
        });
    });
</script>