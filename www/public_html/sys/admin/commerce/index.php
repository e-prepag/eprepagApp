<?php 
require_once "../../../../includes/constantes.php";
require_once $raiz_do_projeto . "public_html/sys/includes/topo_sys.php";
require_once $raiz_do_projeto . "public_html/sys/includes/gamer/inc_pub_access.php";
?>
<link rel="stylesheet" href="/css/creditos.css" type="text/css">
<link rel="stylesheet" href="/css/game.css" type="text/css">
    <style>
        body{
            font-size: 14px !important;
            background-color: #fff !important;
        }

        .nav>li>a:focus, .nav>li>a:hover {
            text-decoration: none;
            background-color: #268fbd !important;
            color: #fff;
        }

        .lista{
            margin-bottom: 2px;
            padding: 10px;
        }
    </style>
<div class="container">    
<?php
$abaMenusItensMenu = $sistema->getIndex();
if(!$abaMenusItensMenu instanceof SistemaVO || $sistema->getErro()){
    
    $erro = $sistema->getErro();
    
    if(!empty($erro)){
        echo '<div class="alert alert-danger top20 txt-" role="alert">';
        
        foreach($erro as $er){
            echo '<span class="glyphicon glyphicon-exclamation-sign t0" aria-hidden="true"></span>
                  <span class="sr-only">Erro:</span>'.$er;
            
        }
        
        echo '</div>';
    }
    
}else{
    //echo var_dump(unserialize($_SESSION[SISTEMA]["arrAbasVo"])[$abaAtual]);
    $aba = unserialize($_SESSION[SISTEMA]["arrAbasVo"])[$abaAtual];
	
    $menus = $sistema->getMenusAba($aba->getId(), true);
    $dividorColuna = round(($sistema->qtdItens/2));
    $itens = 0;
    $quebraColuna = false;
    ?>
    <div class="col-md-6 top20 text-left">
    <?php

    foreach ($menus as $ind => $menu){
        $itensMenus = $menu->getItens();
        if(count($itensMenus) <= 0)
            continue;
        
        if($quebraColuna){

            echo '</div><div class="col-md-6 top20 text-left">';
            $quebraColuna = false;
        }
    ?>
            <div class="top10 lista bg-azul-claro txt-branco">
                <strong><?php echo constant(trim($menu->getDescricao())); ?></strong>
            </div>
            <ul class="nav nav-pills nav-stacked nav-pills-stacked-example"> 
    <?php 
            foreach($itensMenus as $item){
                if($item->getChaveMonitor() === ""){
    ?>
                <li role="presentation"><a href="<?php echo $item->getLink(); ?>" class="menu"><?php echo constant(trim($item->getDescricao())); ?></a></li> 
    <?php
                }else{
                    echo "<li role=\"presentation\">";
                    require_once $raiz_do_projeto.$item->getLink();
                    echo "</li>";
                }


                if($itens == $dividorColuna)
                    $quebraColuna = true;

                $itens++;
            }
    ?>
            </ul>
    <?php
    }
    ?>
    </div>
<?php 
}
?>
</div>    
<?php
    require_once $raiz_do_projeto . "public_html/sys/includes/rodape_sys.php"; 
?>
</body></html>