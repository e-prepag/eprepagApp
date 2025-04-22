<?php

require_once '../../../includes/constantes.php';;
require_once $raiz_do_projeto . 'backoffice/includes/topo_bko_inc.php';
require_once $raiz_do_projeto."class/util/Util.class.php";

if(!Util::isAjaxRequest())
    die("Chamada não permitida!");


if(isset($_POST["ug_id"])){
    $ug_id = $_POST["ug_id"];
    $sql = "SELECT ug_login, ug_nome_fantasia, ug_razao_social FROM dist_usuarios_games WHERE ug_id = " . $ug_id;
    $ret = SQLexecuteQuery($sql);
    if(pg_num_rows($ret) == 0){
        echo "erro";
    }else{
        $row = pg_fetch_assoc($ret);
        $ug_login = $row["ug_login"];
        $ug_nome_fantasia = $row["ug_nome_fantasia"];
        $ug_razao_social = $row["ug_razao_social"];
        echo "<p>Login: " . $ug_login;
        echo "<p>Nome Fantasia: " . $ug_nome_fantasia;
        echo "<p>Razão Social: " . $ug_razao_social;
    }
}

