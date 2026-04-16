<?php

require_once '/www/includes/constantes.php';;
require_once $raiz_do_projeto . 'backoffice/includes/topo_bko_inc.php';
require_once $raiz_do_projeto."class/util/Util.class.php";

if(!Util::isAjaxRequest())
    die("Chamada n�o permitida!");


if(isset($_POST["ug_id"])){
    $ug_id = (int)$_POST["ug_id"];
    $sql = "SELECT ug_login, ug_nome, ug_email, ug_perfil_saldo FROM usuarios_games WHERE ug_id = " . $ug_id;
    $ret = SQLexecuteQuery($sql);
    if(!$ret || !$ret || pg_num_rows($ret) == 0){
        echo "erro";
    }else{
        $row = pg_fetch_assoc($ret);
        if ($row) {
            $ug_login = (string)($row["ug_login"] ?? "");
            $ug_nome = (string)($row["ug_nome"] ?? "");
            $ug_email = (string)($row["ug_email"] ?? "");
            $ug_perfil_saldo = (string)($row["ug_perfil_saldo"] ?? "0");
            echo "<p>Login: " . strtolower($ug_login);
            echo "<p>Nome: " . $ug_nome;
            echo "<p>Email: " . strtolower($ug_email);
            echo "<p>Saldo: R$".$ug_perfil_saldo;
        } else {
            echo "erro";
        }
    }
}
