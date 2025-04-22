<?php
$pos_pagina = false; //apenas para nao exibir erro/ resolver depois
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
/* 
    CONTROLLER
 */

    require_once $raiz_do_projeto."class/business/BannerBO.class.php";
    require_once $raiz_do_projeto."class/business/ClickBannerBO.class.php";

    $objBanner = new BannerBO();
    
    $banners = $objBanner->pegaBanner();

    if(isset($_POST["busca"])){

        $objClickBanner = new ClickBannerBO;
        $bannersBusca = $objClickBanner->pegaClicksBannerBusca($_POST, $objBanner);
    }else{
        $bannersBusca = $banners;
    }
    
/*
    FIM CONTROLLER
 */
?>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>  
<div class="col-md-12 txt-preto">
    <form id="buscaClicksBanner" name="buscaClicksBanner" class="form-inline" method="post">
        <h4>Filtrar:</h4>
        <div class="text-left col-md-12">
            <div class="form-group">
                <label for="dataClickIni">Data inicial:</label>
                <input type="text" id="dataClickIni" label="data inicial " char="10" name="dataClickIni" <?php if(isset($_POST["dataClickIni"])) echo "value='".$_POST["dataClickIni"]."'"; ?> class="form-control w150">
            </div>
            <div class="form-group">
                <label for="dataClickFim">Data final:</label>
                <input type="text" id="dataClickFim"  label="data final " char="10" name="dataClickFim" <?php if(isset($_POST["dataClickFim"])) echo "value='".$_POST["dataClickFim"]."'"; ?> class="form-control w150">
            </div>
            <div class="form-group">
                <label for="bs_id">Banner:</label>
<?php
    if(!empty($banners)){
?>
                <select class="form-control w150" name="bs_id" char="1" id="bs_id" label="banner ">
                    <option value="">Todos</option>
<?php
                            foreach ($banners as $banner){
?>
                            <option value="<?php echo $banner->getId(); ?>" <?php if(isset($_POST["bs_id"]) && $_POST["bs_id"] == $banner->getId()) echo "selected"; ?>><?php echo $banner->getTitulo();?></option>
<?php
                            }
?>                    
                </select>
<?php
    }else{
?>
                        <span>Não temos banners cadastrados</span>
<?php
    }
?>
            </div>
            <div class="form-group">
                <span class="p5">
                    <input type="hidden" name="busca" value="1">
                    <input type="submit" value="Buscar" id="buscar" name="buscar" class="btn btn-sm btn-info">
                </span>
            </div>
        </div>
    </form>
</div>
<div class="col-md-12 txt-preto ">
    <table class="table table-bordered top20" >
        <thead class="">
            <tr>
                <th>Titulo</th>
                <th>Total clicks</th>
            </tr>
        </thead>
        <tbody title="Clique para editar">
<?php 
            if($bannersBusca){
                foreach($bannersBusca as $banner){
?>
            <tr class="trListagem">
                <td><?php echo $banner->getTitulo(); ?></td>
                <td><?php echo $banner->getClicks(); ?></td>
            </tr>
<?php
                }
            }else{
?>
            <tr>
                <td colspan="3">Nenhum resultado foi encontrado.</td>
            </tr>
<?php
            }
?>
        </tbody>
    </table>
</div>
<link href="<?php echo $server_url_ep; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $server_url_ep; ?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $server_url_ep; ?>/js/global.js"></script>
<!--<script type="text/javascript" src="js/jquery.ui.nestedSortable.js"></script>-->
<script>
    $(function(){

        var optDate = new Object();
            optDate.interval = 6;
            optDate.minDate = "01/01/2016";

        setDateInterval('dataClickIni','dataClickFim',optDate);
    });
</script>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</body>
</html>