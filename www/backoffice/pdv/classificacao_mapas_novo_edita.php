<?php
    if($_GET["acao"] == "novo")
        $method = "novaPosicao";
    elseif($_GET["acao"] == "edita") {
        if(isset($_GET["id"]) && $_GET["id"] != ""){
            $select = "select * from classificacao_mapas where cm_id = '".$_GET["id"]."'";
            if($rsPublishers = SQLexecuteQuery($select)){
                if(pg_num_rows($rsPublishers) > 0){
                    while($publishers = pg_fetch_array($rsPublishers)) 
                    {
                        $publisher = new stdClass;
                        $publisher->id = $publishers['cm_id'];
                        $publisher->nome = $publishers['cm_nome'];
                        $publisher->status = $publishers['cm_status'];
                        $publisher->dataCadastro = $publishers['cm_data_cadastro'];
                        $publisher->oprCodigo = $publishers['opr_codigo'];
                    }
                }else {
                    echo "<script>alert('Publisher não encontrado.'); location.href = '/dist_commerce/classificacao_mapas.php';</script>";
                }
            }

        }else
            echo "<script>alert('Posicao para edição, não especificada'); location.href = '/dist_commerce/classificacao_mapas.php';</script>";
        
        $method = "editaPosicao";
    }else
        header("Location: /dist_commerce/classificacao_mapas.php");
    
    $sql  = "select * from operadoras ope order by opr_nome";
    $rs_operadoras = SQLexecuteQuery($sql);
    
?>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="0">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li><a href="classificacao_mapas.php">Classificações de Mapa</a></li>
        <li class="active"><?php echo $_GET["acao"] == "novo" ? "Nova" : "Edição de"?> classificação de Mapa</li>
    </ol>
</div>
<div class="col-md-12 txt-preto">
    <form id="form" enctype="multipart/form-data" name="form" method="post" action="classificacao_mapas.php">
        <div class="azul-claro p10 negrito">
            <div class="top10 ">
                <label for="cm_nome" class="w200 left">Nome do cartão:</label>
                <input type="text" class="w-auto form-control" name="cm_nome" char="3" id="cm_nome" label="Publisher " <?php if(isset($publisher->oprCodigo) && $publisher->oprCodigo) echo "readonly";?> value="<?php if(isset($publisher)) echo $publisher->nome; ?>" >
            </div>
            <div class="top10 ">
                <label for="opr_codigo" class="w200 left">Existe operadora referente?</label>
                <select class="w-auto form-control" name="opr_codigo" id="opr_codigo" label="Operadora">
                    <option value="" <?php if(isset($publisher->oprCodigo) && !$publisher->oprCodigo) echo "selected" ?>>Não</option>
                    <?php if(isset($rs_operadoras) && $rs_operadoras) while($rs_operadoras_row = pg_fetch_array($rs_operadoras)){ ?>
                    <option operadora="<?php echo $rs_operadoras_row['opr_nome'];?>" value="<?php echo $rs_operadoras_row['opr_codigo']; ?>" <?php if (isset($publisher->oprCodigo) && $publisher->oprCodigo == $rs_operadoras_row['opr_codigo']) echo "selected";?>><?php echo $rs_operadoras_row['opr_nome']." (".$rs_operadoras_row['opr_codigo'].")"; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="top10 ">
                <label for="cm_status" class="w200 left">Status:</label>
                <select class="w-auto form-control" name="cm_status" char="1" id="cm_status" label="Status">
                    <option value="">--</option>
                    <option value="1" <?php if(isset($publisher) && $publisher->status == 1) echo "selected"; ?>>Ativo</option>
                    <option value="0" <?php if(isset($publisher) && $publisher->status == 0) echo "selected"; ?>>Inativo</option>
                </select>
            </div>
            <div class="p5 top20 left">
                <input type="hidden" name="cmid" value="<?php if(isset($publisher)) echo $publisher->id; ?>">
                <input type="hidden" name="<?php echo $method; ?>" value="1">
                <input type="button" value="Salvar" id="salvar" class="btn btn-sm btn-info">
            </div>
        </div>
    </form>
</div>

<script>
    $(function(){
        $("#opr_codigo").change(function(){
            if($(this).val() != ""){
                var opr = $("#opr_codigo option:selected").text().split(" (");
                $("#cm_nome").val(opr[0]).attr("readonly","readonly");
            }else{
                $("#cm_nome").val("").removeAttr("readonly");
            }
        });
        
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