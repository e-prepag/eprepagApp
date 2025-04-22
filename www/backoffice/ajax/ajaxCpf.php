<?php

require_once "../../includes/constantes.php";
require_once $raiz_do_projeto . 'backoffice/includes/topo_bko_inc.php';
require_once $raiz_do_projeto."class/util/Util.class.php";

if(!Util::isAjaxRequest())
    die("Chamada n�o permitida!");
    
if(isset($_POST["cpf"]) && isset($_POST["dataNascimento"])){
    
    
    require_once $raiz_do_projeto."class/util/CPF.php";
    require_once $raiz_do_projeto."includes/funcoes_cpf.php";
    require_once $raiz_do_projeto."includes/functions.php";
    
    if(session_id() == '') {
        session_start();
    }
    
    if(is_array($_SESSION['cpf_boleto_ex']))
        unset($_SESSION['cpf_boleto_ex']);
    
    $objCpf = new CPF();
    $ret = $objCpf->verificaCpfRF($_POST["cpf"],$_POST["dataNascimento"]);

    if($ret["erros"] == ""){
        $cpf = preg_replace('/[^0-9]/', '', $_POST["cpf"]);
        $_SESSION['cpf_boleto_ex']['cpf'] = mask($cpf,'###.###.###-##');
        $_SESSION['cpf_boleto_ex']['data_nascimento'] = $ret["data_nascimento"];
        $_SESSION['cpf_boleto_ex']['nome'] = fix_name($ret["nome"]);
    }
    
    print json_encode($ret);
   
}else{
    print "CPF ou Data de Nascimento inv�lidos.";
}