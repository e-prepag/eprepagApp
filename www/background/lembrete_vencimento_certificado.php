<?php
ob_start(); 
set_time_limit(3600);
ini_set('max_execution_time', 3600);

require_once "../includes/main.php";
require_once $raiz_do_projeto . "includes/pdv/main.php";
require_once $raiz_do_projeto . "class/classManipulacaoArquivosLog.php";


$arquivoLog = new ManipulacaoArquivosLog($argv);

if(!$arquivoLog->haveFile()) {
    
    $arquivoLog->createLockedFile();
    $nome_arquivo = $arquivoLog->getNomeArquivo();
    ob_start('callbackLog');

    //Quantidade de dias para começar a avisar sobre o vencimento
    define("QTDE_DIAS", 60);
    //Data em que a validade do certificado digital do site irá expirar
    define("DATA_VENCIMENTO", '2022-06-20');

    $data_vencimento = date("Y-m-d",strtotime(date(DATA_VENCIMENTO)));

    $diferenca_dias = (strtotime($data_vencimento) - strtotime(date("Y-m-d")));
    $dias = floor(($diferenca_dias)/(60*60*24));
    
    echo str_repeat("-", 40).PHP_EOL."Data Execução: ".date("d/m/Y H:i").PHP_EOL.str_repeat("=", 31).PHP_EOL.PHP_EOL;

    if($dias < QTDE_DIAS && $dias > 1){
        $destino = "wagner@e-prepag.com.br, glaucia@e-prepag.com.br";
        $assunto = "E-Prepag - Aviso Vencimento do Certificado Digital";
        $mensagem = "<strong>AVISO IMPORTANTE</strong><br><br>A validade do Certificado Digital da E-Prepag irá expirar em <strong>".$dias. " dias</strong><br>Data do vencimento: <strong>".DATA_VENCIMENTO."</strong>";
        enviaEmail($destino, NULL, NULL, $assunto, $mensagem);

        echo "Aviso de vencimento do certificado enviado nos e-mails ".$destino.". Vence em ".$dias." dias".PHP_EOL.PHP_EOL;
    } else{
        if($dias >= 1){
            echo "Vencimento em ".$dias." dias".PHP_EOL.PHP_EOL;
        }

    }
    
    $arquivoLog->deleteLockedFile();
} else{
    $arquivoLog->showBusy();
}
