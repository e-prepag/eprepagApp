<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."class/util/Util.class.php";
require_once $raiz_do_projeto."class/business/BannerBO.class.php";
require_once $raiz_do_projeto."class/classEncryption.php";

if(Util::isAjaxRequest() && $_POST['cript']){
    
    unset($_POST['cript']);
    $str = serialize($_POST);
    $objEncryption = new Encryption();
    echo $objEncryption->encrypt($str);
}
