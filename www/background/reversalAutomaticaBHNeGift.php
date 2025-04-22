<?php
ob_start(); 
set_time_limit(1200);
ini_set('max_execution_time', 1200); 

require_once "../includes/main.php";
require_once $raiz_do_projeto . "bhn/egift/config.inc.bhn.egift.php";
require_once $raiz_do_projeto . "class/classManipulacaoArquivosLog.php";
$time_start_stats = getmicrotime();

$arquivoLog = new ManipulacaoArquivosLog($argv);

if(!$arquivoLog->haveFile()) {
    $arquivoLog->createLockedFile();
    $nome_arquivo = $arquivoLog->getNomeArquivo();

    ob_start('callbackLog');
    echo PHP_EOL."Data execução : ".date('Y-m-d H:i:s').PHP_EOL.PHP_EOL;

    //Bloco para transation do pedido na BHN eGift
    /*
     * $jsonAux = '{
            "reversalEGiftRequestId": "26547"
        }';
    */

    //Buscando no pedidos que necessitam de Reversal
    $rs_pedidos = classReverseeGift::buscaReversal(); 
    if($rs_pedidos && pg_num_rows($rs_pedidos) > 0) {
        while ($rs_pedidos_row = pg_fetch_array($rs_pedidos)){

                $jsonAux = '{
                     "reversalEGiftRequestId": '.$rs_pedidos_row['bhn_id'].'
                }';
                echo PHP_EOL.$jsonAux.PHP_EOL;
                $jsonAux = json_decode($jsonAux, TRUE);

                $eGift = new classReverseeGift($jsonAux);

                if($eGift -> getService()) {
                    //Reverse an eGift Card
                    $eGift -> Req_EfetuaConsultaRegistro($lista_resposta_egift, NULL,json_encode($eGift));
                    echo "Resposta:".print_r($lista_resposta_egift, true).PHP_EOL;
                    if($eGift -> saveReturn($lista_resposta_egift)){
                            echo "Reversal ID [".$rs_pedidos_row['bhn_id']."] processado!".PHP_EOL;
                    }//end if($eGift -> saveReturn($lista_resposta_egift))
                }//end if($eGift -> getService())
                else {
                    echo "Erro na operação.".PHP_EOL;
                }

        }//end while
    }//end do if($rs_pedidos && pg_num_rows($rs_pedidos) > 0)
    else echo "Nenhuma transação requer REVERSAL no momento!".PHP_EOL;
    //FIM Bloco para transation do pedido na BHN eGift

    echo PHP_EOL.str_repeat("_", 80) . PHP_EOL. "Final de execução em: ". date('Y-m-d H:i:s'). PHP_EOL. "Elapsed time : ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.').PHP_EOL.str_repeat("=", 80) . PHP_EOL;

    $arquivoLog->deleteLockedFile();
}
else {
    $arquivoLog->showBusy();
}

//Fechando Conexão
pg_close($connid);

?>
