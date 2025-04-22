<?php
$pos_pagina = false; //apenas para nao exibir erro/ resolver depois
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
/* 
    CONTROLLER
 */
require_once $raiz_do_projeto."class/business/CategoriaEstornoChargebackBO.class.php";

$objCategoria = new CategoriaEstornoChargebackBO();

if(!empty($_POST)){
    if(isset($_POST['busca'])){
        $categorias = $objCategoria->pegaCategoria();    
    }elseif(isset($_POST['novaCategoria'])){
        $objCategoria->insereCategoria($_POST);
    }elseif(isset($_POST['editaCategoria'])){
        if(isset($_POST["id"])){
            $objCategoria->editaCategoria($_POST);
        }
        else
            echo "<script>alert('Problema ao obter categoria.'); location.href = '/gamer/cat_estorno_chargeback/categorias.php';</script>";
    }

    $categorias = $objCategoria->pegaCategoria();
}elseif(!isset($_GET["acao"]) || $_GET["acao"] == ""){
    $categorias = $objCategoria->pegaCategoria();
}
/*
    FIM CONTROLLER
 */

include_once (isset($_GET["acao"]) && ($_GET["acao"] == "novo" || $_GET["acao"] == "edita")) ? 'categoria_novo_edita.php' : 'categoria_lista.php';


require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</body>
</html>