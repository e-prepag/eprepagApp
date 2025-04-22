<?php
// CRON foi cancelado, descontinuado
// ob_start(); 
// set_time_limit(3600);
// ini_set('max_execution_time', 3600); 

// // include do arquivo contendo IPs DEV
// require_once "../includes/main.php";
// require_once $raiz_do_projeto . "bhn/config.inc.bhn.php";
// require_once $raiz_do_projeto . "class/classManipulacaoArquivosLog.php";
// require_once $raiz_do_projeto . "includes/pdv/main.php";
// $time_start_stats = getmicrotime();

// $arquivoLog = new ManipulacaoArquivosLog($argv);

// if(!$arquivoLog->haveFile()) {
    // $arquivoLog->createLockedFile();
    // $nome_arquivo = $arquivoLog->getNomeArquivo();

    // ob_start('callbackLog');
    // echo PHP_EOL."Data execução : ".date('Y-m-d H:i:s').PHP_EOL.PHP_EOL;

    // //Bloco para transation do pedido na BHN
    // $rs_api = new classBHN();
    // $rs = $rs_api->buscaTransaction();

    // if($rs && pg_num_rows($rs) != 0){
            // while ($rs_row = pg_fetch_array($rs)){

                    // $resposta = null;
					
					// // atualiza status para 'processando' para que na proxima chamada não seja buscada novamente 
					// $valid = $rs_api->updateStatusTemporario($rs_row['bhn_id']);
						
                    // $parametros = $rs_api->object_to_array(json_decode($rs_row['bhn_vetor']));
                    // $testeBHN = $rs_api->Req_EfetuaConsulta($parametros,$resposta);

                    // if(in_array($testeBHN, $BHN_CODE_SUCESS)) {
                        // $rs_api->disponibilizaPIN($parametros,$resposta);
                    // }//end if(in_array($testeBHN, $BHN_CODE_SUCESS))

                    // echo "Transaction Return: ".$testeBHN.PHP_EOL;
                    // echo print_r($resposta,true);

            // }
    // }
    // //FIM Bloco para transation do pedido na BHN

    // echo PHP_EOL.str_repeat("_", 80) . PHP_EOL. "Final de execução em: ". date('Y-m-d H:i:s'). PHP_EOL. "Elapsed time : ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.').PHP_EOL.str_repeat("=", 80) . PHP_EOL;

    // $arquivoLog->deleteLockedFile();
// }
// else {
    // $arquivoLog->showBusy();
// }

// //Fechando Conexão
// pg_close($connid);

?>
