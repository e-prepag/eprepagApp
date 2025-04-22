<?php
ob_start(); 
set_time_limit(1200);
ini_set('max_execution_time', 1200); 

require_once "../includes/main.php";
require_once $raiz_do_projeto . "bhn/egift/config.inc.bhn.egift.php";
require_once $raiz_do_projeto . "class/classManipulacaoArquivosLog.php";
require_once $raiz_do_projeto . "includes/gamer/main.php";
$time_start_stats = getmicrotime();


$arquivoLog = new ManipulacaoArquivosLog($argv);

if(!$arquivoLog->haveFile()) {
    $arquivoLog->createLockedFile();
    $nome_arquivo = $arquivoLog->getNomeArquivo();

    ob_start('callbackLog');
    echo PHP_EOL."Data execução : ".date('Y-m-d H:i:s').PHP_EOL.PHP_EOL;


    //Buscando no DB pedidos para processar o ReLoad
    $vg_id = NULL;
    $auxBHNiD = NULL;
    $contadorPINsPedido = 0;
    $contadorPINsLidoComSucesso = 0;
    $rs_pedidos = classReadeGift::buscaReLoad(); 
    if($rs_pedidos) {
        while ($rs_pedidos_row = pg_fetch_array($rs_pedidos)){
    
            
            //Verificando se deve enviar email
            if(!is_null($vg_id)&& $vg_id != $rs_pedidos_row['vg_id']&& $contadorPINsPedido == $contadorPINsLidoComSucesso) {
                processaEmailVendaGames($vg_id, NULL);
                $contadorPINsPedido = 0;
                $contadorPINsLidoComSucesso = 0;
            }//end if(!is_null($vg_id)&& $vg_id != $rs_pedidos_row['vg_id']&& $contadorPINsPedido == $contadorPINsLidoComSucesso)
            $vg_id = $rs_pedidos_row['vg_id'];
            $auxBHNiD = $rs_pedidos_row['bhn_id'];

            //Lendo o eGift
            $pin = new classReadeGift($rs_pedidos_row['bhn_id']);
            $pin -> Req_EfetuaConsultaRegistro($lista_resposta_pin, $rs_pedidos_row['bhn_account_id']);
            if($pin -> saveReturn($lista_resposta_pin)){
                    echo "Reposta PIN:".print_r($lista_resposta_pin, true).PHP_EOL;
                    if(in_array($BHN_EGIFT_CODE_STATUS[trim($lista_resposta_pin->status)],$BHN_EGIFT_CODE_SUCESS)){
                            //Preparando parametros para gerar os estoque
                            $pinParams = array(
                                                'bhn_valor'     => $rs_pedidos_row['bhn_valor'],
                                                'opr_codigo'    => $rs_pedidos_row['opr_codigo'],
                                                'vgm_id'        => $rs_pedidos_row['vgm_id'],
                                                'pin_codigo'    => (empty($lista_resposta_pin->securityCode)?$lista_resposta_pin->accountNumber:$lista_resposta_pin->securityCode),
                                                'pin_serial'    => $lista_resposta_pin->activationAccountNumber
                                            );        
                            $objectAux = new classRegistroPinRequest;
                            if(classRegistroPinRequest::insereEstoque($pinParams, $objectAux)){
                                    $contadorPINsLidoComSucesso++;
                                    echo "Maravilha Tudo Funcionou o RELOAD!".PHP_EOL;
                            }//if(classRegistroPinRequest::insereEstoque($pinParams))
                            else echo "Erro ao inserrir o PIN no estoque.".PHP_EOL;
                    }//end if(in_array($BHN_EGIFT_CODE_STATUS[trim($lista_resposta_pin->status)],$BHN_EGIFT_CODE_SUCESS))
                    else echo "O Status de Resposta do Read eGift foi [".trim($lista_resposta_pin->status)."]".PHP_EOL;
            }//end if($pin -> saveReturn($lista_resposta_pin))
            else echo "Erro no Read Account.".PHP_EOL;

            $contadorPINsPedido++;
        }//end while
        if(!is_null($vg_id) && !is_null($auxBHNiD) && $contadorPINsPedido == $contadorPINsLidoComSucesso) {
            processaEmailVendaGames($vg_id, NULL);
        }//end if(!is_null($vg_id)&& $vg_id != $rs_pedidos_row['bhn_valor'])
    }//end if($rs_pedidos)
    //FIM Bloco para ReLoad de PINs BHN eGift

    echo PHP_EOL.str_repeat("_", 80) . PHP_EOL. "Final de execução em: ". date('Y-m-d H:i:s'). PHP_EOL. "Elapsed time : ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.').PHP_EOL.str_repeat("=", 80) . PHP_EOL;

    $arquivoLog->deleteLockedFile();
}
else {
    $arquivoLog->showBusy();
}

//Fechando Conexão
pg_close($connid);

?>
