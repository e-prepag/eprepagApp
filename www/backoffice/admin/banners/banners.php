<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
$pos_pagina = false; //apenas para nao exibir erro/ resolver depois

require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
/* 
    CONTROLLER
 */
    
    require_once RAIZ_DO_PROJETO."class/business/BannerBO.class.php";
    require_once RAIZ_DO_PROJETO."class/business/CategoriaBannerBO.class.php";
    require_once RAIZ_DO_PROJETO."class/business/PosicaoBannerBO.class.php";

    
    $objBanner = new BannerBO();
    $objCategoria = new CategoriaBannerBO();
    $objPosicao = new PosicaoBannerBO;
    
    if(isset($_POST)){
        if(isset($_POST['novoBanner'])){
            if($objBanner->insereBanner($_POST, $_FILES)){
                $url = "/admin/banners/banners_reordenacao.php?ctg=".$_POST['bsc_id']."&psc=".$_POST['bsp_id'];
                echo "<script>location.href='$url';</script>"; 
                
            }else{
                if(!empty($objBanner->erros)){
                    $msg = implode("<br>",$objBanner->erros);
                }
            }
        }elseif(isset($_POST['editaBanner'])){
            if(isset($_POST["idb"])){
                if(!$objBanner->editaBanner($_POST, $_FILES)) {
                    $msg = implode("<br>",$objBanner->erros);
                }
                unset($_POST);
            }
            else
                echo "<script>alert('Problema ao obter banner.'); location.href = 'banners.php';</script>";
        }
    }
/*
    FIM CONTROLLER
 */
?>
<div class="col-md-12 fontsize-pp">
<?php

include_once (isset($_GET["acao"]) && ($_GET["acao"] == "novo" || $_GET["acao"] == "edita")) ? 'banner_novo_edita.php' : 'banner_lista.php';
?>
</div>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</body>
</html>