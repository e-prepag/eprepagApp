<?php
ob_start(); 
set_time_limit(3600);
ini_set('max_execution_time', 3600); 

// include do arquivo contendo IPs DEV
$raiz_do_projeto = "/www/";
require_once "/www/includes/main.php";
require_once $raiz_do_projeto . "bhn/config.inc.bhn.php";
require_once $raiz_do_projeto . "class/classManipulacaoArquivosLog.php";
require_once $raiz_do_projeto . "class/class_bank_sonda.php";
require_once $raiz_do_projeto . "includes/inc_Pagamentos.php";
require_once $raiz_do_projeto . "includes/pdv/functions_vendaGames_pag_online.php";
require_once $raiz_do_projeto . "includes/pdv/main.php";
$time_start_stats = getmicrotime();

///$arquivoLog = new ManipulacaoArquivosLog($argv);

//if(!$arquivoLog->haveFile()) {
    ///$arquivoLog->createLockedFile();
    ///$nome_arquivo = $arquivoLog->getNomeArquivo();

    //ob_start('callbackLog');
    echo PHP_EOL."Data execuчуo : ".date('Y-m-d H:i:s').PHP_EOL.PHP_EOL;

    echo conciliacaoAutomaticaPagtoOnlineExpressMoneyLH();

    echo PHP_EOL.str_repeat("_", 80) . PHP_EOL. "Final de execuчуo em: ". date('Y-m-d H:i:s'). PHP_EOL. "Elapsed time : ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.').PHP_EOL.str_repeat("=", 80) . PHP_EOL;

  ///  $arquivoLog->deleteLockedFile();
//}
//else {
  //  $arquivoLog->showBusy();
//}

//Fechando Conexуo
pg_close($connid);

?>