<?php
if($_GET["acao"] == "novo") $method = "novaCategoria";
elseif($_GET["acao"] == "edita") {
    if(isset($_GET["id"]) && $_GET["id"] != ""){
        $filtro = "cec_id =  ".$_GET["id"];
        $categoria = $objCategoria->pegaCategoria($filtro);

    }else
        echo "<script>alert('Categoria para edição, não especificada'); location.href = '/gamer/cat_estorno_chargeback/categorias.php';</script>";

    $method = "editaCategoria";
}
else header("Location: /gamer/cat_estorno_chargeback/categorias.php");
?>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $_GET["acao"] == "novo" ? "Nova" : "Edição de"?> Categoria de Estorno e ChargeBack</a></li>
    </ol>
</div>
<div class="col-md-12 txt-preto">
    <form id="formCategorias" enctype="multipart/form-data" name="formCategorias" method="post" action="/gamer/cat_estorno_chargeback/categorias.php">
            <div class=" p5">
                <label for="cec_descricao" class="w100 left">Nome:</label>
                <input type="text" class="form-control w150" name="cec_descricao" char="4" id="cec_descricao" label="Titulo " value="<?php if(isset($categoria)) echo $categoria[0]->getDescricao(); ?>" >
            </div>
            <div class="top10">
                <label for="cec_status" class="w100 left">Status:</label>
                <select class="form-control w150" name="cec_status" char="1" id="cec_status" label="Status">
                    <option value="">--</option>
                    <option value="1" <?php if(isset($categoria) && $categoria[0]->getStatus() == 1) echo "selected"; ?>>Ativo</option>
                    <option value="0" <?php if(isset($categoria) && $categoria[0]->getStatus() == 0) echo "selected"; ?>>Inativo</option>
                </select>
            </div>
            <div class="p5 top20 left">
                <input type="hidden" name="id" value="<?php if(isset($categoria)) echo $categoria[0]->getId(); ?>">
                <input type="hidden" name="<?php echo $method; ?>" value="1">
                <input type="button" value="Salvar" id="salvar" class="btn btn-sm btn-info">
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
                //console.log($(this).get(0).form.id);
               $("#formCategorias").submit();
               
            }
       });
    });
</script>