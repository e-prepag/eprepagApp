<?php
session_start();

if(!isset($GLOBALS['_SESSION']['iduser_bko'])) {
    header("Location: login.php");
    die();
}

require_once '../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";


//Recebe um objeto AbaVo na posio requisitada
$arrAbasVo = isset($_SESSION[SISTEMA]["arrAbasVo"]) ? unserialize($_SESSION[SISTEMA]["arrAbasVo"]) : [];
$aba = isset($arrAbasVo[$abaAtual]) ? $arrAbasVo[$abaAtual] : null;

if (!$aba) {
    echo "Aba no encontrada";
    exit;
}

//Recupera os itens e menus da aba atual que podem ser acessados pelo usurio
$menus = $sistema->getMenusAba($aba->getId(), true);

//Caso no tenha itens, ou a funo tenha retornado algum erro, uma mensagem  exibida
if(!$menus || $sistema->getErro()){

    $erro = $sistema->getErro();

    if(!empty($erro) && is_array($erro)){
        echo '<div class="alert alert-danger top20 txt-" role="alert">';

        foreach($erro as $er){
            echo '<span class="glyphicon glyphicon-exclamation-sign t0" aria-hidden="true"></span>
                  <span class="sr-only">Erro:</span>'.$er;

        }

        echo '</div>';
    }

}elseif(!isset($_SESSION[SISTEMA]["arrMenu"][$aba->getId()]["qtdItens"]) || $_SESSION[SISTEMA]["arrMenu"][$aba->getId()]["qtdItens"] == 0){
    echo '<div class="alert alert-warning top20 txt-" role="alert">';
    echo '<span class="glyphicon glyphicon-exclamation-sign t0" aria-hidden="true"></span>
                  <span class="">Alerta: No existem itens disponveis para este usurio nessa aba</span>';
    echo '</div>';
}else{
    $dividorColuna = round(($_SESSION[SISTEMA]["arrMenu"][$aba->getId()]["qtdItens"]/2));
    $itens = 0;
    $quebraColuna = false;
    session_write_close();
    echo '<div class="col-md-6 top10">';

    foreach ((array)$menus as $ind => $menu){

        $itensMenus = $menu->getItens();

        if(!is_array($itensMenus) || count($itensMenus) <= 0)
            continue;


        if($quebraColuna){

            echo '</div><div class="col-md-6 top10">';
            $quebraColuna = false;
        }
    ?>
            <div class="top10 lista bg-azul-claro txt-branco">
                <strong><?php echo $menu->getDescricao(); ?></strong>
            </div>
            <ul class="nav nav-pills nav-stacked nav-pills-stacked-example"> 
    <?php 
            foreach($itensMenus as $item){

                if($item->getChaveMonitor() === ""){
    ?>
                <li role="presentation"><a href="<?php echo $item->getLink(); ?>" class="menu"><?php echo $item->getDescricao(); ?></a></li> 
    <?php
                }else{
                    echo "<li role=\"presentation\" class='item-menu'>";
                    if(file_exists($raiz_do_projeto.$item->getLink())) {
                        require_once $raiz_do_projeto.$item->getLink();
                    }
                    else echo "Arquivo N�O encontrado:<br>".$raiz_do_projeto.$item->getLink();
                    echo "</li>";
                }

                $itens++;

                if($itens == $dividorColuna)
                    $quebraColuna = true;

            }
    ?>
            </ul>
    <?php
    }
    ?>
    </div>
<?php
}

if($_SERVER["REMOTE_ADDR"] == "201.93.162.169"){
	//var_dump($_SESSION);
}

require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
