<?php
$pos_pagina = false; //apenas para nao exibir erro/ resolver depois

require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
/* 
    CONTROLLER
 */
    require_once $raiz_do_projeto."class/business/CategoriaBannerBO.class.php";
    require_once $raiz_do_projeto."class/business/BannerBO.class.php";
    
    $objBanner = new BannerBO;
    $objCategoria = new CategoriaBannerBO();
    
    if(!empty($_POST)){
        if(isset($_POST['busca'])){
            $categorias = $objCategoria->pegaCategoria();
            
        }elseif(isset($_POST['novaCategoria'])){
            if($objCategoria->insereCategoria($_POST))
                $objBanner->jsonBanners();
            
        }elseif(isset($_POST['editaCategoria'])){
            if(isset($_POST["idbc"])){
                if($objCategoria->editaCategoria($_POST))
                    $objBanner->jsonBanners();
                
            }
            else
                echo "<script>alert('Problema ao obter categoria.'); location.href = 'banners_categorias.php';</script>";
        }

        $categorias = $objCategoria->pegaCategoria();
    }elseif(!isset($_GET["acao"]) || $_GET["acao"] == ""){
        $categorias = $objCategoria->pegaCategoria();
    }
/*
    FIM CONTROLLER
 */

include_once (isset($_GET["acao"]) && ($_GET["acao"] == "novo" || $_GET["acao"] == "edita")) ? 'banner_categoria_novo_edita.php' : 'banner_categoria_lista.php';


require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</body>
</html>