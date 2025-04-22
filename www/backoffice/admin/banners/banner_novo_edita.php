<?php
    if($_GET["acao"] == "novo")
        $method = "novoBanner";
    elseif($_GET["acao"] == "edita")
    {
        if(isset($_GET["id"]) && $_GET["id"] != ""){
            
            $filtro[] = "bs_id = ".$_GET["id"]; //["="]["bs_id"] = $_GET["id"];
            $banner = $objBanner->pegaBanner($filtro);
        }else
            echo "<script>alert('Banner para edição, não especificado'); location.href = 'banners.php';</script>";
        
        $method = "editaBanner";
    }else
        header("Location: banners.php");
    
    require_once RAIZ_DO_PROJETO."class/business/CategoriaBannerBO.class.php";
    require_once RAIZ_DO_PROJETO."class/business/PosicaoBannerBO.class.php";
    require_once "/www/includes/bourls.php";

    $where_c = "bsc_status = 1";
    $where_p = "bsp_status = 1";
    $categorias = $objCategoria->pegaCategoria($where_c);
    $posicoes = $objPosicao->pegaPosicao($where_p);
?>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><a href="banners.php"><?php echo $sistema->menu[0]->getDescricao(); ?></a></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo ((!empty($filtro))?"Editar":"Novo"); ?> banner</a></li>
    </ol>
</div>
<div class="txt-preto col-md-7 col-lg-7 col-xs-12 col-sm-12">
    <form id="form" enctype="multipart/form-data" name="form" method="post" action="banners.php">
            <div class="col-md-3 col-lg-3 col-xs-12 col-sm-12">
                <label for="bs_titulo" class="w100 left">Titulo:</label>
            </div>
            <div class="col-md-9 col-lg-9 col-xs-12 col-sm-12">
                <input type="text" class="form-control w150" name="bs_titulo" char="4" id="bs_titulo" label="Titulo " value="<?php if(isset($banner)) echo $banner[0]->getTitulo(); ?>" >
            </div>
            <div class="col-md-3 col-lg-3 col-xs-12 col-sm-12 top10">
                <label for="bs_imagem" class="w100 left">Imagem:</label>
            </div>
            <div class="col-md-9 col-lg-9 col-xs-12 col-sm-12 top10">
                <input type="file" class="custom-file-input" name="bs_imagem" id="bs_imagem" label="Imagem " value="<?php if(isset($banner)) echo $banner[0]->getImagem();?>">
                <span class="font10">Tipos válidos de imagem: jpg, jpge e png.</span>
            </div>
            <div class="col-md-3 col-lg-3 col-xs-12 col-sm-12 top10">
                <label for="bs_link" class="w100 left">Link:</label>
            </div>
            <div class="col-md-9 col-lg-9 col-xs-12 col-sm-12 top10">
                <input type="text" class="form-control w150" name="bs_link" char="8" id="bs_link" label="Link " value="<?php if(isset($banner)) echo $banner[0]->getLink();?>">
            </div>
            <div class="col-md-3 col-lg-3 col-xs-12 col-sm-12 top10">
                <label for="bs_data_inicio" class="w100 left">Data inicial:</label>
            </div>
            <div class="col-md-9 col-lg-9 col-xs-12 col-sm-12 top10">
                <input type="text" id="bs_data_inicio" name="bs_data_inicio" char="10" class="form-control w150"  label="Data inicial " value="<?php if(isset($banner)) echo $banner[0]->getDataInicio();?>">
            </div>
            <div class="col-md-3 col-lg-3 col-xs-12 col-sm-12 top10">
                <label for="bs_data_fim" class="w100 left">Data final:</label>
            </div>
            <div class="col-md-9 col-lg-9 col-xs-12 col-sm-12 top10">
                <input type="text" id="bs_data_fim" name="bs_data_fim" char="10" class="form-control w150" label="Data final " value="<?php if(isset($banner)) echo $banner[0]->getDataFim();?>">
            </div>
            <div class="col-md-3 col-lg-3 col-xs-12 col-sm-12 top10">
                <label for="bsc_id" class="w100 left">Categoria:</label>
            </div>
            <div class="col-md-9 col-lg-9 col-xs-12 col-sm-12 top10">
<?php
    if(!empty($categorias)){
?>
                <select class="form-control w150" name="bsc_id" id="bsc_id" char="1" label="Categoria ">
                    <option value="">--</option>
<?php
                            foreach ($categorias as $categoria){
?>
                            <option value="<?php echo $categoria->getId(); ?>" <?php if(isset($banner)) if($banner[0]->getCategoria()->getId() == $categoria->getId()) echo "selected"; ?>><?php echo $categoria->getDescricao(); ?></option>
<?php
                            }

?>
                </select>
<?php
    }else{
?>
                <span>Não temos categorias cadastradas</span>
<?php
    }
?>
            </div>
            <div class="col-md-3 col-lg-3 col-xs-12 col-sm-12 top10">
                <label for="bsp_id" class="w100 left">Posição:</label>
            </div>
            <div class="col-md-9 col-lg-9 col-xs-12 col-sm-12 top10">
<?php
    if(!empty($posicoes)){
?>
                <select class="form-control w150" name="bsp_id" char="1" id="bsp_id" label="Posição ">
                    <option value="">--</option>
<?php
                            foreach ($posicoes as $posicao){
?>
                            <option value="<?php echo $posicao->getId(); ?>" <?php if(isset($banner)) if($banner[0]->getPosicao()->getId() == $posicao->getId()) echo "selected"; ?>><?php echo $posicao->getDescricao();?></option>
<?php
                            }
?>                    
                </select>
<?php
    }else{
?>
                        <span>Não temos categorias cadastradas</span>
<?php
    }
?>
            </div>
            <div class="col-md-3 col-lg-3 col-xs-12 col-sm-12 top10">
                <label for="bs_status" class="w100 left">Status:</label>
            </div>
            <div class="col-md-9 col-lg-9 col-xs-12 col-sm-12 top10">
                <select class="form-control w150" name="bs_status" char="1" id="bs_status" label="Status ">
                    <option value="">--</option>
                    <option value="1" <?php if(isset($banner)) if($banner[0]->getStatus() == 1) echo "selected"; ?>>Ativo</option>
                    <option value="0" <?php if(isset($banner)) if($banner[0]->getStatus() == 0) echo "selected"; ?>>Inativo</option>
                </select>
            </div>
            <div class="col-md-9 col-lg-9 col-md-offset-3 col-lg-offset-3 col-xs-12 col-sm-12 top10">
                <input type="hidden" name="idb" value="<?php if(isset($banner)) echo $banner[0]->getId(); ?>">
                <input type="hidden" name="bs_ordenacao" value="<?php if(isset($banner)) echo $banner[0]->getOrdenacao(); ?>">
                <input type="hidden" name="<?php echo $method; ?>" value="1">
                <input type="button" value="Salvar" id="salvar" class="btn btn-sm btn-info">
            </div>
    </form>
</div>
<link href="<?php echo $server_url_ep; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $server_url_ep; ?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $server_url_ep; ?>/js/global.js"></script>
<script>
    var imagensPermitidas = "jpge, jpg e png";
    
    function validaImagem(id){
        return ($("#"+id).val() != "" && 
               (imagensPermitidas.indexOf($("#"+id).val().split('.').pop().toLowerCase()) < 0)) ? false : true;
    }
    
    function validateUrl(value){
        return ((value.indexOf("http://") >= 0  ||
                value.indexOf("https://") >= 0) &&
                value.indexOf(".") >= 0) ? true : false;
      
    }
    
    $(function(){
        
        var objDatePicker = new Object();

        objDatePicker.interval = 1000;
        objDatePicker.maxDate = null;
        objDatePicker.dateFormat = "dd/mm/yy";
        objDatePicker.minDate = null;
        objDatePicker.changeMonth = true;

        objDatePicker.onClose = function(selectedDate, instance)
        {
            if (selectedDate != '') {
                    $("#bs_data_fim").datepicker("option", "minDate", selectedDate);
                    var date = $.datepicker.parseDate(instance.settings.dateFormat, selectedDate, instance.settings);
                    date.setMonth(date.getMonth() + objDatePicker.interval);
                    $("#bs_data_fim").datepicker("option", "minDate", selectedDate);
                }
        };

        $("#bs_data_inicio").datepicker(objDatePicker);

        var data = $("#bs_data_inicio").datepicker("getDate");
        if(data){
            var tmpData = data;
            tmpData.setMonth(tmpData.getMonth()+objDatePicker.interval);

            if(tmpData <= currentDate)
                data.setMonth(tmpData.getMonth());
            else
                data = currentDate;
        }else
            data = currentDate;

        $("#bs_data_fim").datepicker({
            maxDate: null,
            changeMonth: true,
            dateFormat: objDatePicker.dateFormat,
            minDate: $("#bs_data_inicio").datepicker("getDate")
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
                
            }else if(!validaImagem("bs_imagem"))
            {
                $("label[for='bs_imagem'").css("color","red");
                alert("Imagem já existe ou extensão inválida.");
                
            }else 
            if(!validateUrl($("#bs_link").val()))
            {
                $("label[for='bs_link'").css("color","red");
                alert("Link inválido (verifique se contém http:// ou https://).");
            }
            else
            {
               $("#"+$(this).get(0).form.id).submit();
               
            }
       });
    });
</script>