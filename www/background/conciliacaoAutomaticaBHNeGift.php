<?php
// foi descontiuado
// ob_start(); 
// set_time_limit(1200);
// ini_set('max_execution_time', 1200); 

// require_once "../includes/main.php";
// require_once $raiz_do_projeto . "bhn/egift/config.inc.bhn.egift.php";
// require_once $raiz_do_projeto . "class/classManipulacaoArquivosLog.php";
// require_once $raiz_do_projeto . "includes/gamer/main.php";
// $time_start_stats = getmicrotime();

// $arquivoLog = new ManipulacaoArquivosLog($argv);

// if(!$arquivoLog->haveFile()) {
    // $arquivoLog->createLockedFile();
    // $nome_arquivo = $arquivoLog->getNomeArquivo();

    // ob_start('callbackLog');
    // echo PHP_EOL."Data execução : ".date('Y-m-d H:i:s').PHP_EOL.PHP_EOL;

    // //Bloco para transation do pedido na BHN eGift
    // /*
     // * $jsonAux = '{
         // "giftFrom": "giftFrom",
         // "giftTo": "giftTo",
         // "giftMessage": "giftMessage",
         // "giftAmount": 15,
         // "purchaserId": "6T1XSTHG5C5643DZZ42230WX24",
         // "recipientId": "F7HP2Q7CPLPV8QZ88N9FN95ZCW",
         // "retrievalReferenceNumber": "111122223333",
         // "productConfigurationId": "AQKNLF4MKRAA5RBPJD00QB9P4R",
         // "notes": "Creating my first egift"
    // }';
    // */

    // //Buscando no DB pedidos para processar
    // $sql = "SELECT bhn_id, bhn_valor, bhn_product_id, bhn_valor, opr_codigo, vgm_id, vg_id FROM pedidos_egift_bhn WHERE bhn_status_generate IS NULL ORDER BY vg_id;";
    // echo $sql.PHP_EOL;
    // $vg_id = NULL;
    // $contadorPINsPedido = 0;
    // $contadorPINsLidoComSucesso = 0;
    // $rs_pedidos = SQLexecuteQuery($sql); 
    // while ($rs_pedidos_row = pg_fetch_array($rs_pedidos)){
        
            // if(!is_null($vg_id)&& $vg_id != $rs_pedidos_row['vg_id']&& $contadorPINsPedido == $contadorPINsLidoComSucesso) {
                // processaEmailVendaGames($vg_id, NULL);
                // $contadorPINsPedido = 0;
                // $contadorPINsLidoComSucesso = 0;
            // }//end if(!is_null($vg_id)&& $vg_id != $rs_pedidos_row['vg_id']&& $contadorPINsPedido == $contadorPINsLidoComSucesso)
            // $vg_id = $rs_pedidos_row['vg_id'];
                    
            // $jsonAux = '{
                 // "giftAmount": '.($rs_pedidos_row['bhn_valor']*1).',
                 // "retrievalReferenceNumber": "'.str_pad($rs_pedidos_row['bhn_id'], 12, "0", STR_PAD_LEFT).'",
                 // "productConfigurationId": "'.$rs_pedidos_row['bhn_product_id'].'",
                 // "recipientId": '.$rs_pedidos_row['bhn_id'].'
            // }';
            // echo PHP_EOL.$jsonAux.PHP_EOL;
            // $jsonAux = json_decode($jsonAux, TRUE);

            // $eGift = new classGenerateeGift($jsonAux);

            // if($eGift -> getService()) {
                
                // $pinParams = array(
                                    // 'bhn_valor'     => $rs_pedidos_row['bhn_valor'],
                                    // 'opr_codigo'    => $rs_pedidos_row['opr_codigo'],
                                    // 'vgm_id'        => $rs_pedidos_row['vgm_id']
                                // );

                // //Gerando o eGift
                // $eGift -> Req_EfetuaConsultaRegistro($lista_resposta_egift, NULL,json_encode($eGift));
                // echo "Resposta:".print_r($lista_resposta_egift, true).PHP_EOL;
                // if($eGift -> saveReturn($lista_resposta_egift, $jsonAux, $vg_id)){
                        // $eGiftAccount = $eGift -> readVarsRestful($lista_resposta_egift->accountId);
                        // echo "eGiftAccount:".print_r($eGiftAccount, true).PHP_EOL;
                        // foreach ($eGiftAccount as $id => $conteudo) {
                            // if(str_replace(BHN_EGIFT_GET_ACCOUNT,"",$id) != $id) {

                                // //Lendo o eGift
                                // $pin = new classReadeGift($lista_resposta_egift->retrievalReferenceNumber);
                                // $accountId = str_replace(BHN_EGIFT_GET_ACCOUNT,"",$id);
                                // if($pin -> saveAccountId($accountId)) {
                                        // $pin -> Req_EfetuaConsultaRegistro($lista_resposta_pin, $accountId);
                                        // if($pin -> saveReturn($lista_resposta_pin)){
                                                // echo "Reposta PIN:".print_r($lista_resposta_pin, true).PHP_EOL;
                                                // if(in_array($BHN_EGIFT_CODE_STATUS[trim($lista_resposta_pin->status)],$BHN_EGIFT_CODE_SUCESS)){
                                                        // $pinParams['pin_codigo'] = (empty($lista_resposta_pin->securityCode)?$lista_resposta_pin->accountNumber:$lista_resposta_pin->securityCode);
                                                        // $pinParams['pin_serial'] = $lista_resposta_pin->activationAccountNumber;
                                                        // $objectAux = new classRegistroPinRequest;
                                                        // if(classRegistroPinRequest::insereEstoque($pinParams, $objectAux)){
                                                                // $contadorPINsLidoComSucesso++;
                                                                // echo "Maravilha Tudo Funcionou!".PHP_EOL;
                                                        // }//if(classRegistroPinRequest::insereEstoque($pinParams))
                                                        // else echo "Erro ao inserrir o PIN no estoque.".PHP_EOL;
                                                // }//end if(in_array($BHN_EGIFT_CODE_STATUS[trim($lista_resposta_pin->status)],$BHN_EGIFT_CODE_SUCESS))
                                                // else echo "O Status de Resposta do Read eGift foi [".trim($lista_resposta_pin->status)."]".PHP_EOL;
                                        // }//end if($pin -> saveReturn($lista_resposta_pin))
                                        // else echo "Erro no Read Account.".PHP_EOL;
                                // }//end if($pin -> saveAccountId($accountId))
                                // else echo "Erro ao atualizar o Account ID.".PHP_EOL;

                            // }//end if(str_replace(BHN_EGIFT_GET_ACCOUNT,"",$id) != $id)
                        // }//end foreach
                // }//end if($eGift -> saveReturn($lista_resposta_egift))
            // }//end if($eGift -> getService())
            // else {
                // echo "Erro na operação.".PHP_EOL;
            // }
            // $contadorPINsPedido++;
    // }//end while
    // if(!is_null($vg_id) && isset($lista_resposta_egift->retrievalReferenceNumber) && $contadorPINsPedido == $contadorPINsLidoComSucesso) {
        // processaEmailVendaGames($vg_id, NULL);
    // }//end if(!is_null($vg_id)&& $vg_id != $rs_pedidos_row['bhn_valor'])
    
    // //FIM Bloco para transation do pedido na BHN eGift

    // echo PHP_EOL.str_repeat("_", 80) . PHP_EOL. "Final de execução em: ". date('Y-m-d H:i:s'). PHP_EOL. "Elapsed time : ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.').PHP_EOL.str_repeat("=", 80) . PHP_EOL;

    // $arquivoLog->deleteLockedFile();
// }
// else {
    // $arquivoLog->showBusy();
// }

// //Fechando Conexão
// pg_close($connid);

?>
