<?php
    if($_GET["acao"] == "novo")
        $method = "novaPosicao";
    elseif($_GET["acao"] == "edita")
    {
        if(isset($_GET["id"]) && $_GET["id"] != ""){
            $filtro = "bsp_id = ".$_GET["id"]; //["="]["bsp_id"] = $_GET["id"];
            $posicao = $objPosicao->pegaPosicao($filtro);

        }else
            echo "<script>alert('Posicao para edição, não especificada'); location.href = 'banners_posicoes.php';</script>";
        
        $method = "editaPosicao";
    }else
        header("Location: banners_posicoes.php");
    
?>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>  
<div class="col-md-12 txt-preto">
    <form id="form" enctype="multipart/form-data" name="form" method="post" action="banners_posicoes.php">
        <div class="azul-claro p10 negrito">
            <div class="input-group">
                <label for="bsp_descricao" class="w100 left">Nome:</label>
                <input type="text" class="form-control w150" name="bsp_descricao" char="4" id="bsp_descricao" label="Nome " value="<?php if(isset($posicao)) echo $posicao[0]->getDescricao(); ?>" >
                <span class="TextoVermelho font12"> *Não deve conter caracteres especiais (|,!,?,*,$,%, etc).</span>
            </div>
            <div class="input-group top10">
                <label for="bsp_tamanho" class="w100 left">Tamanho:</label>
                <input type="text" class="form-control w150" name="bsp_tamanho" char="1" id="bsp_tamanho" label="Tamanho " value="<?php if(isset($posicao)) echo $posicao[0]->getTamanho(); ?>" >
            </div>
            <div class="input-group top10">
                <label for="bsp_status" class="w100 left">Status:</label>
                <select class="form-control w150" name="bsp_status" char="1" id="bsp_status" label="Status">
                    <option value="">--</option>
                    <option value="1" <?php if(isset($posicao) && $posicao[0]->getStatus() == 1) echo "selected"; ?>>Ativo</option>
                    <option value="0" <?php if(isset($posicao) && $posicao[0]->getStatus() == 0) echo "selected"; ?>>Inativo</option>
                </select>
            </div>
            <div class="input-group top10">
                <input type="hidden" name="idbp" value="<?php if(isset($posicao)) echo $posicao[0]->getId(); ?>">
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