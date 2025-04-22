<?php
$pagina_titulo = "E-prepag - Créditos para Games";
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";

require_once RAIZ_DO_PROJETO."class/business/BannerBO.class.php";
require_once RAIZ_DO_PROJETO."class/business/CategoriaBannerBO.class.php";
require_once RAIZ_DO_PROJETO."class/business/PosicaoBannerBO.class.php";

$objBanner = new BannerBO();
$where[] = "bs_status = 1";
$where[] = "bs_data_inicio <= '". date('Y-m-d 00:00:00') ."'";
$where[] = "bs_data_fim >= '". date('Y-m-d 00:00:00') ."'";

$banners = $objBanner->pegaBanner($where);

$categorias = array();
$posicoes = array();
if(isset($banners)){
    foreach($banners as $banner){
        $idCategoria                = $banner->getCategoria()->getId();
        $categorias[$idCategoria]   = $banner->getCategoria()->getDescricao();
        $posicoes[$idCategoria][$banner->getPosicao()->getId()] = $banner->getPosicao()->getDescricao();
    }
}

if(!empty($_POST['banners'])){
    
    foreach($_POST['banners'] as $ind => $ordem){
        
        $filtro[] = "bs_id = ".$ind;
        $arrBanner = $objBanner->pegaBanner($filtro);
        unset($filtro);
        $bannerEdt = $arrBanner[0];
        
        if($bannerEdt instanceof BannerVO){
            $idc = $bannerEdt->getCategoria()->getId();
            $idp = $bannerEdt->getPosicao()->getId();
            
            $bannerEdt->setOrdenacao($ordem);
            $bannerEdt->setCategoria($idc);
            $bannerEdt->setPosicao($idp);
            
            $_GET['ctg'] = $idc;
            $_GET['psc'] = $idp;

            if($objBanner->update($bannerEdt)){
                $msg = "Reordenado com sucesso.";
                $color = "txt-verde"; 
            }else{
                $msg = "Erro na reordenação dos banners.";
                $color = "txt-vermelho"; 
            }
        }
    }
    
    $objBanner->jsonBanners();
}
?>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><a href="banners.php"><?php echo $sistema->menu[0]->getDescricao(); ?></a></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>    
<?php
if(isset($msg))
{
?>
    <div class="col-md-12 espacamento <?php echo $color;?>">
        <strong><?php echo $msg?></strong>
    </div>
<?php
}
?>
<div class="col-md-12 espacamento">
    <strong>Ordenação de banners</strong>
</div>
<form method="POST" id="editaBanner">
    <div class="col-md-1 top20">
        <strong>Categoria: </strong>
    </div>
    <div class="col-md-3 top20">
    <?php
        if(!empty($categorias)){
    ?>
                    <select class="form-control w100" name="bsc_id" char="1" id="bsc_id" label="Categoria ">
                        <option value="">--</option>
    <?php
                                foreach ($categorias as $id => $categoria){
    ?>
                                <option value="<?php echo $id; ?>" <?php if(isset($_GET['ctg']) && $_GET['ctg'] == $id) echo "selected";?>><?php echo $categoria;?></option>
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
    <div class="col-md-1 top20">
        <strong>Posição: </strong>
    </div>
    <div class="col-md-3 top20" id="divPosicoes">
    <?php
        if(isset($_GET['psc']) && isset($_GET['ctg'])){
    ?>
                    <select class="form-control w100" onchange="getTabelaBanners(this.value)" name="bsp_id" char="1" id="bsp_id" label="Posição ">
                        <option value="">--</option>
    <?php
                                foreach ($posicoes[$_GET['ctg']] as $id => $posicao){
    ?>
                                <option value="<?php echo $id; ?>" <?php if(isset($_GET['psc']) && $_GET['psc'] == $id) echo "selected";?>><?php echo $posicao;?></option>
    <?php
                                }
    ?>                    
                    </select>
    <?php
        }else{
    ?>
                            <span>Selecione uma categoria.</span>
    <?php
        }
    ?>            
    </div>
    <div class="col-md-4 txt-vermelho font14">
            Para ordenar os banners, basta clicar no banner desejado e arrastá-lo.
    </div>
    <div class="col-md-12">
        
            <div id="tabelaBanners" class="top10"></div>
        
    </div>
</form>
<link href="<?php echo $server_url_ep; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $server_url_ep; ?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script>
$(function(){
    
<?php 
    if(isset($_GET['ctg'])) echo '$("#bsc_id").trigger("change");';
    if(isset($_GET['psc'])) echo 'getTabelaBanners("'.$_GET['psc'].'");';
?>
    
    $("#bsc_id").change(function(){
        
        $.ajax("/ajax/ordenacao_banners.php", { 
            data: { 
                    idc: $("#bsc_id").val(), 
                    metodo: "posicoes" 
                },
            type: "POST",
            beforeSend: function() {
                $("#divPosicoes").html("Carregando posições, por favor aguarde...");
                $("#tabelaBanners").html("");
            },
            error: function() {
                alert("error"); 
            },
            success: function(data) {
                $("#divPosicoes").html(data);
            }
        });
    });
});

function getTabelaBanners(bsp){
    $.ajax("/ajax/ordenacao_banners.php", { 
            data: { 
                    idc: $("#bsc_id").val(),
                    idp: bsp,
                    metodo: "banners" 
                },
            type: "POST",
            beforeSend: function() {
                $("#tabelaBanners").html("Carregando banners, por favor aguarde...");
            },
            error: function() {
                alert("error"); 
            },
            success: function(data) {
                $("#tabelaBanners").html(data);
            }
    });
}

function reordena(){
    $(".table-bordered tr td").each(function(i){
       $(this).children().val(i);
    });
    
    $("#editaBanner").submit();
}
</script>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
