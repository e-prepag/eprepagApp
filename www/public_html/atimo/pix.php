<?php

ob_start(); 
set_time_limit(1200);
ini_set('max_execution_time', 1200); 

echo "pix";

error_reporting(E_ALL); 
ini_set("display_errors", 1); 

require_once "/www/includes/main.php";
require_once "/www/class/classManipulacaoArquivosLog.php";
require_once "/www/class/class_bank_sonda.php";
require_once "/www/includes/inc_Pagamentos.php";
require_once "/www/includes/gamer/main.php";

$time_start_stats = getmicrotime();

//$arquivoLog = new ManipulacaoArquivosLog($argv);

//if(!$arquivoLog->haveFile()) {
  //  $arquivoLog->createLockedFile();
    //$nome_arquivo = $arquivoLog->getNomeArquivo();

    ob_start('callbackLog');

    echo conciliacaoAutomaticaPagtoPIXemGAMER(); 

    echo PHP_EOL.str_repeat("_", 80) . PHP_EOL."Elapsed time : ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.').PHP_EOL.str_repeat("=", 80) . PHP_EOL;

    //$arquivoLog->deleteLockedFile();
//}
//else {
    //$arquivoLog->showBusy();
//}

ob_clean();
//Fechando Conexão
pg_close($connid);


?>