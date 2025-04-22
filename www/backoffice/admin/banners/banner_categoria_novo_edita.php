<?php
    if($_GET["acao"] == "novo")
        $method = "novaCategoria";
    elseif($_GET["acao"] == "edita")
    {
        if(isset($_GET["id"]) && $_GET["id"] != ""){
            $filtro = "bsc_id =  ".$_GET["id"];
            $categoria = $objCategoria->pegaCategoria($filtro);

        }else
            echo "<script>alert('Categoria para edição, não especificada'); location.href = 'banners_categorias.php';</script>";
        
        $method = "editaCategoria";
    }else
        header("Location: banners_categorias.php");
    
?>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><a href="banners_categorias.php"><?php echo $sistema->menu[0]->getDescricao(); ?></a></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<div class="col-md-12 txt-preto">
    <form id="form" enctype="multipart/form-data" name="form" method="post" action="banners_categorias.php">
        <div class="azul-claro col-md-5 p10 negrito">
            <div class="input-group">
                <label for="bsc_descricao" class="w100 left">Nome:</label>
                <input type="text" class="form-control w150" name="bsc_descricao" char="4" id="bsc_descricao" label="Titulo " value="<?php if(isset($categoria)) echo $categoria[0]->getDescricao(); ?>" >
                <span class="TextoVermelho font12"> *Não deve conter caracteres especiais (|,!,?,*,$,%, etc).</span>
            </div>
            <div class="input-group top20">
                <label for="bs_status" class="w100 left">Status:</label>
                <select class="form-control w150" name="bsc_status" char="1" id="bsc_status" label="Status">
                    <option value="">--</option>
                    <option value="1" <?php if(isset($categoria) && $categoria[0]->getStatus() == 1) echo "selected"; ?>>Ativo</option>
                    <option value="0" <?php if(isset($categoria) && $categoria[0]->getStatus() == 0) echo "selected"; ?>>Inativo</option>
                </select>
            </div>
            <div class="input-group top10">
                <input type="hidden" name="idbc" value="<?php if(isset($categoria)) echo $categoria[0]->getId(); ?>">
                <input type="hidden" name="<?php echo $method; ?>" value="1">
                <input type="button" value="Salvar" id="salvar" class="btn btn-sm btn-info">
            </div>
        </div>
    </form>
</div>

<script>
    $(function(){
       $("#salvar").click(function(){
            var erro = [];
            
            $(".form-control").each(function(){
                 if($(this).val().length < $(this).attr("char")){
                     erro.push($(this).attr("label"));
                     $("label[for='"+$(this).attr("id")+"']").css("color","red");
                 }else{
                     $("label[for='"+$(this).attr("id")+"']").css("color","#337ab7");
                 }
            });
            
            if(erro.length > 0)
            {
                var msgErro = erro.join()+" não estão preenchidos, ou estão preenchidos de forma incorreta.";
                alert(msgErro);
            }
            else
            {
               $("#"+$(this).get(0).form.id).submit();
               
            }
       });
    });
</script>