<?php
$pos_pagina = false; //apenas para nao exibir erro/ resolver depois

require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";

/* 
    CONTROLLER
 */
    require_once $raiz_do_projeto."class/business/PosicaoBannerBO.class.php";
    require_once $raiz_do_projeto."class/business/BannerBO.class.php";

    $objPosicao = new PosicaoBannerBO();
    $objBanner = new BannerBO;
    
    if(!empty($_POST)){
        if(isset($_POST['busca'])){
            $posicoes = $objPosicao->pegaPosicao();
        }elseif(isset($_POST['novaPosicao'])){
            if($objPosicao->inserePosicao($_POST)){
                $objBanner->jsonBanners();
            }
        }elseif(isset($_POST['editaPosicao'])){
            if(isset($_POST["idbp"])){
                if($objPosicao->editaPosicao($_POST)){
                    $objBanner->jsonBanners();
                }
                
            }
            else
                echo "<script>alert('Problema ao obter posicao.'); location.href = 'banners_posicoes.php';</script>";
        }

        $posicoes = $objPosicao->pegaPosicao();
    }elseif(!isset($_GET["acao"]) || $_GET["acao"] == ""){
        $posicoes = $objPosicao->pegaPosicao();
    }
/*
    FIM CONTROLLER
 */

include_once (isset($_GET["acao"]) && ($_GET["acao"] == "novo" || $_GET["acao"] == "edita")) ? 'banner_posicao_novo_edita.php' : 'banner_posicao_lista.php';


require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</body>
</html>