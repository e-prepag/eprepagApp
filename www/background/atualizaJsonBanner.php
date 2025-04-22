<?php
ob_start(); 
set_time_limit(5000);
ini_set('max_execution_time', 5000);

require_once "../includes/main.php";
require_once $raiz_do_projeto . "class/classManipulacaoArquivosLog.php";
require_once $raiz_do_projeto . "includes/gamer/main.php";
$time_start_stats = getmicrotime();

$arquivoLog = new ManipulacaoArquivosLog($argv);

if(!$arquivoLog->haveFile()) {
    $arquivoLog->createLockedFile();
    $nome_arquivo = $arquivoLog->getNomeArquivo();

    ob_start('callbackLog');
    echo PHP_EOL."Data execuчуo : ".date('Y-m-d H:i:s').PHP_EOL.PHP_EOL;

    //INICIO DO BLOCO
    include_once($raiz_do_projeto . "class/business/BannerBO.class.php");  
    $objBanner = new BannerBO();
    $objBanner->jsonBanners();

    //FIM DO BLOCO

    echo PHP_EOL.str_repeat("_", 80) . PHP_EOL. "Final de execuчуo em: ". date('Y-m-d H:i:s'). PHP_EOL. "Elapsed time : ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.').PHP_EOL.str_repeat("=", 80) . PHP_EOL;

    $arquivoLog->deleteLockedFile();
}
else {
    $arquivoLog->showBusy();
}

//Fechando Conexуo
pg_close($connid);

?>