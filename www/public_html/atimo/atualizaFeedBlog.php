<?php

ob_start(); 

set_time_limit(5000);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('max_execution_time', 5000);


require_once "/www/includes/main.php";

$raiz_do_projeto = '/www/';
require_once $raiz_do_projeto . "class/classManipulacaoArquivosLog.php";

require_once $raiz_do_projeto . "includes/gamer/main.php";

$time_start_stats = getmicrotime();


    echo PHP_EOL."Data execução : ".date('Y-m-d H:i:s').PHP_EOL.PHP_EOL;
    $argv[1] = "lanhouse";


    //INICIO DO BLOCO

    require_once DIR_CLASS."util/FeedWP.class.php";

    

    if($argv[1] == "gamer"){

        $arrJsonFiles = unserialize(ARR_JSON_FEED_GAMER);

        $url = URL_BLOG_GAMER;

    }else if($argv[1] == "lanhouse"){

        $arrJsonFiles = unserialize(ARR_JSON_FEED_CREDITOS);

        $url = URL_BLOG_CREDITOS;

    }

    

    $feed = new FeedWP();

    $feed->setFullPath(DIR_JSON);

    $feed->setArrJsonFiles($arrJsonFiles);

    

    if($feed->generate($url)){

        echo "Sucesso ao atualizar JSON ".$arrJsonFiles[0].". (".date("Y-m-d H:i:s").")".PHP_EOL;

    }else{

        echo "Erro ao atualizar JSON ".$arrJsonFiles[0].". (".date("Y-m-d H:i:s").")".PHP_EOL;

    }

    

    $totalJson = array_merge(unserialize(ARR_JSON_FEED_CREDITOS),unserialize(ARR_JSON_FEED_GAMER));

    

    if($feed->cleanDir($totalJson)){

        echo "Diretório de imagens foi varrido com sucesso.".PHP_EOL;

    }else{

        echo "Erro ao limpar diretório de imagens.".PHP_EOL;

    }

    

    //FIM DO BLOCO



    echo PHP_EOL.str_repeat("_", 80) . PHP_EOL. "Final de execução em: ". date('Y-m-d H:i:s'). PHP_EOL. "Elapsed time : ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.').PHP_EOL.str_repeat("=", 80) . PHP_EOL;


//Fechando Conexão

pg_close($connid);


?>