<?php
ob_start(); 
set_time_limit(1200);
ini_set('max_execution_time', 1200); 

require_once "../includes/main.php";
require_once $raiz_do_projeto . "class/classManipulacaoArquivosLog.php";
require_once $raiz_do_projeto . "class/class_bank_sonda.php";
require_once $raiz_do_projeto . "includes/inc_Pagamentos.php";
require_once $raiz_do_projeto . "includes/gamer/main.php";

$time_start_stats = getmicrotime();

$arquivoLog = new ManipulacaoArquivosLog($argv);

if(!$arquivoLog->haveFile()) {
    $arquivoLog->createLockedFile();
    $nome_arquivo = $arquivoLog->getNomeArquivo();

    ob_start('callbackLog');


    //conciliacaoAutomaticaPagamentoOnline
    echo conciliacaoAutomaticaPagamentoOnline(); 

    echo PHP_EOL.str_repeat("_", 80) . PHP_EOL."Elapsed time : ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.').PHP_EOL.str_repeat("=", 80) . PHP_EOL;

    $arquivoLog->deleteLockedFile();
}
else {
    $arquivoLog->showBusy();
}

//Fechando Conexão
pg_close($connid);

?>
