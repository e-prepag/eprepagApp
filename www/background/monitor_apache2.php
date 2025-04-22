<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

require_once "../includes/main.php";
require_once $raiz_do_projeto . "class/classManipulacaoArquivosLog.php";
require_once $raiz_do_projeto . "includes/gamer/main.php";

$time_start_stats = getmicrotime();
 
$arquivoLog = new ManipulacaoArquivosLog($argv);
  
if(!$arquivoLog->haveFile()) {
    $arquivoLog->createLockedFile();
    $nome_arquivo = $arquivoLog->getNomeArquivo();

    ob_start('callbackLog');
    
    //Geração de LOG
    echo PHP_EOL."Data execução : ".date('Y-m-d H:i:s').PHP_EOL.$subject.PHP_EOL.str_repeat("_", 80).PHP_EOL.PHP_EOL;
    $texto = file_get_contents('http://127.0.0.1:81/server-status');

    $textoAux = substr($texto, (strpos($texto,"requests currently being processed")-10) , 40);
    
    $sql = "INSERT INTO apache_linux (quantidade_acessos) VALUES(".(str_replace("<DT>","",strtoupper(substr($textoAux, (strpos(strtoupper($textoAux),"<DT>")), 6)))*1)."); ";
    
    echo $sql.PHP_EOL.PHP_EOL;
    $rs_dados_levantamento = SQLexecuteQuery($sql);
    
    $cmdtuples = pg_affected_rows($rs_dados_levantamento);
    if($cmdtuples===1) {
        echo " Dado inserido com sucesso!".PHP_EOL;
    } //end if($cmdtuples===1)
    else {
        
        echo " Problemas ao inserir os dados!".PHP_EOL;
    } //end else do if($cmdtuples===1)

    //Geração de LOG
    echo PHP_EOL.str_repeat("_", 80).PHP_EOL."Elapsed time : ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.').PHP_EOL.str_repeat("=", 80) . PHP_EOL;

    $arquivoLog->deleteLockedFile();
}
else {
    $arquivoLog->showBusy();
}

//Fechando Conexão
pg_close($connid);

?>
