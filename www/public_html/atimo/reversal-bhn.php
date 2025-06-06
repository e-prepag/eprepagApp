<?php

//ob_start(); 
set_time_limit(1200);
ini_set('max_execution_time', 1200); 

$raiz_do_projeto = '/www/';
// include do arquivo contendo IPs DEV
require_once "/www/includes/main.php";
require_once $raiz_do_projeto . "bhn/config.inc.bhn.php";
require_once $raiz_do_projeto . "class/classManipulacaoArquivosLog.php";
require_once $raiz_do_projeto . "includes/pdv/main.php";
$time_start_stats = getmicrotime();

//$arquivoLog = new ManipulacaoArquivosLog($argv);

//if(!$arquivoLog->haveFile()) {
  //  $arquivoLog->createLockedFile();
    //$nome_arquivo = $arquivoLog->getNomeArquivo();

    //ob_start('callbackLog');
    echo PHP_EOL."Data execução : ".date('Y-m-d H:i:s').PHP_EOL.PHP_EOL;

    //Bloco para Reversal do pedido na BHN
    $rs_api = new classBHN();
    $rs = $rs_api->buscaRversal();

    if($rs && pg_num_rows($rs) != 0){
            while ($rs_row = pg_fetch_array($rs)){

                    $reversal = new classBHN("REVERSE");
                    $resposta = null;
                    $parametros = json_decode($rs_row['bhn_vetor']);
                    $testeBHN = $reversal->Req_EfetuaConsulta($reversal->object_to_array($parametros),$resposta);

                    echo "Reversal Return: ".$testeBHN.PHP_EOL;
                    echo print_r($resposta,true);

            }
    }
    //FIM Bloco para Reversal do pedido na BHN

    echo PHP_EOL.str_repeat("_", 80) . PHP_EOL."Elapsed time : ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.').PHP_EOL.str_repeat("=", 80) . PHP_EOL;

   // $arquivoLog->deleteLockedFile();
//}
//else {
    $arquivoLog->showBusy();
//}

//Fechando Conexão
pg_close($connid);


?>