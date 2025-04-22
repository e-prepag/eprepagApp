<?php 
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

// require_once "../includes/main.php";
// require_once $raiz_do_projeto . "class/classManipulacaoArquivosLog.php";
// require_once $raiz_do_projeto . "includes/gamer/main.php";

// $arquivoLog = new ManipulacaoArquivosLog($argv);

// if(!$arquivoLog->haveFile()) {
    // $arquivoLog->createLockedFile();
    // $nome_arquivo = $arquivoLog->getNomeArquivo();

    // ob_start('callbackLog');
    
    // echo str_repeat("=", 80).PHP_EOL.date('Y-m-d H:i:s').PHP_EOL.str_repeat("=", 80).PHP_EOL;

    // // Monitor SQL Connections 
    // $time_start_stats = getmicrotime();
    // $sql  = "INSERT INTO tb_stats_current_connections (scc_nconns, scc_data_inclusao) VALUES ((SELECT COUNT(*) FROM pg_stat_activity), CURRENT_TIMESTAMP);";
    // $rs_monitor = SQLexecuteQuery($sql);
    // echo "Número de registros afetados [".pg_affected_rows($rs_monitor)."]".PHP_EOL;
    // if(!$rs_monitor || pg_affected_rows($rs_monitor) == 0) echo "ERRO no Cadastra Monitor nConns.".PHP_EOL."SQL: ".$sql.PHP_EOL;
    // else echo "Sucesso Cadastra Monitor nConns.".PHP_EOL;
    // echo "Elapsed time: ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.').PHP_EOL.str_repeat("-", 80).PHP_EOL;

    // $arquivoLog->deleteLockedFile();
// }
// else {
    // $arquivoLog->showBusy();
// }

// //Fechando Conexão
// pg_close($connid);
?>
