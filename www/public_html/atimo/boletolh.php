<?php
ob_start(); 
error_reporting(E_ALL); 
ini_set("display_errors", 1);
 $raiz_do_projeto = "/www/";
require_once "/www/includes/main.php";
require_once $raiz_do_projeto . "includes/pdv/main.php"; 
//Foi necessсrio sobrescrever as constantes para as de Gamer em funчуo do metodo em Linux esta usando equivocamente o include incorreto
require_once $raiz_do_projeto . "includes/gamer/constantes.php";
require_once $raiz_do_projeto . "class/classManipulacaoArquivosLog.php";

//$arquivoLog = new ManipulacaoArquivosLog($argv);

//if(!$arquivoLog->haveFile()) {
    ///$arquivoLog->createLockedFile();
    //$nome_arquivo = $arquivoLog->getNomeArquivo();

    //ob_start('callbackLog');

	$argv = ["conciliacaoAutomaticaBoletoExpressMoneyLH"];
    //conciliacaoAutomaticaBoletoExpressMoneyLH
    if(in_array("conciliacaoAutomaticaBoletoExpressMoneyLH", $argv)) echo conciliacaoAutomaticaBoletoExpressMoneyLH();
    
  //  $arquivoLog->deleteLockedFile();
//}
//else {
  //  $arquivoLog->showBusy();
//}

//Fechando Conexуo
pg_close($connid);
?>