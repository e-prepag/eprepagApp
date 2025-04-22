<?php
ob_start(); 
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
require_once "../includes/main.php";
require_once $raiz_do_projeto . "includes/pdv/main.php"; 
//Foi necess�rio sobrescrever as constantes para as de Gamer em fun��o do metodo em Linux esta usando equivocamente o include incorreto
require_once $raiz_do_projeto . "includes/gamer/constantes.php";
require_once $raiz_do_projeto . "class/classManipulacaoArquivosLog.php";

$arquivoLog = new ManipulacaoArquivosLog($argv);

if(!$arquivoLog->haveFile()) {
    $arquivoLog->createLockedFile();
    $nome_arquivo = $arquivoLog->getNomeArquivo();

    ob_start('callbackLog');

    //conciliacaoAutomaticaBoletoExpressMoneyLH
    if(in_array("conciliacaoAutomaticaBoletoExpressMoneyLH", $argv)) echo conciliacaoAutomaticaBoletoExpressMoneyLH();
    
    $arquivoLog->deleteLockedFile();
}
else {
    $arquivoLog->showBusy();
}

//Fechando Conex�o
pg_close($connid);
?>