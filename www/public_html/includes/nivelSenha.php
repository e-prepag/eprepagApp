<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);

require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."class/util/Login.class.php";

if(Util::isAjaxRequest())
{
    if($_POST['str'] != ""){
        $minCarac = 10;
        $maxCarac = 35;
        $login = new Login($_POST['str']);
        $login->setLimiteCaracteres($minCarac, $maxCarac);

        print $login->valida();
    }else{
        print "ERRO";
        print 4;
    }
    
}
 
?>