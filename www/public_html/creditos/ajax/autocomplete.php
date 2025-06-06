<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
require_once "../../../includes/constantes.php";
require_once DIR_INCS . 'constantes.php';
require_once DIR_CLASS . "util/Busca.class.php";

if(Util::isAjaxRequest())
{
    
    if(CURRENT_SYSTEM == "creditos"){
        require_once DIR_CLASS . "pdv/controller/ProdutosController.class.php";
        $controller = new ProdutosController;
        $arrJsonFiles = unserialize(ARR_PRODUTOS_CREDITOS);
        $filtro['id_user'] = $controller->usuarios->getId();
        $filtro['_ug_possui_restricao_produtos'] = $controller->usuarios->getPossuiRestricaoProdutos();
        $categoria = "Lan House";
        
    }else{
        $categoria = "Gamer";
        $arrJsonFiles = unserialize(ARR_PRODUTOS_GAMER);
    }
    
    /*if((($controller->lanHouse && $controller->usuarios->b_VendasB2C()) || $controller->operadorTipo == $GLOBALS['USUARIO_GAMES_OPERADOR_TIPOS'][FUNCIONARIO_1]) && 
        $controller->usuarios->getRiscoClassif() == 2)
    {
        $filtro['b2c'] = true;
    }else{ 
        $filtro['b2c'] = false;
    }*/
    
    $filtro['b2c'] = false; // para voltar com os produtos b2c, descomente o bloco acima e remova essa linha
    $filtro['origem'] = "autocomplete";
    
    $busca = new Busca();
    $busca->setFullPath(DIR_JSON);
    $busca->setArrJsonFiles($arrJsonFiles);
    $busca->setFiltro($filtro);
    $busca->setCategoria($categoria);
    print $busca->getJson(Util::cleanStr2(utf8_decode($_GET['term'])));
    
}else
{
    die("Chamada não permitida.");
}

