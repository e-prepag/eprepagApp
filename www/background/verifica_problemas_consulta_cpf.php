<?php
ob_start(); 
set_time_limit(3600);
ini_set('max_execution_time', 3600);

require_once "../includes/main.php";
require_once $raiz_do_projeto . "includes/pdv/main.php";
require_once $raiz_do_projeto . "class/classManipulacaoArquivosLog.php";

//Número de problemas que dispara um e-mail de alerta
define('ALERT_NUMBER', 5);
//Tempo a considerar em minutos a cada execução
define('VERIFY_TIME', 5);

define('EMAIL_DEV', 'wagner@e-prepag.com.br');
define('EMAILS_PROD', 'glaucia@e-prepag.com.br, suporte@e-prepag.com.br, help@e-prepag.com.br');

$destino = (checkIP()) ? EMAIL_DEV : EMAILS_PROD;
$assunto = (checkIP()?"[DEV]":"[PROD]") . " Possível problema com sistema consulta CPF";

$arquivoLog = new ManipulacaoArquivosLog($argv);

if(!$arquivoLog->haveFile()) {
    
    $arquivoLog->createLockedFile();
    $nome_arquivo = $arquivoLog->getNomeArquivo();

    ob_start('callbackLog');
    $sql = "SELECT erro_sistema, data FROM benchmark WHERE funcao = 'CPF' AND data > (NOW() - '".VERIFY_TIME." minutes'::interval) AND erro_sistema IS NOT NULL;";
    $rs = SQLexecuteQuery($sql);
    
    echo str_repeat("=", 60).PHP_EOL."Data execução : ".date('Y-m-d H:i:s').PHP_EOL.PHP_EOL;
    if($rs){
        $num_rows = pg_num_rows($rs);
        if($num_rows > 0){
            $msg_log = "";
            
            $list_errors = "";
            $msg_log .= "Foram detectados ".$num_rows." problema(s) com o sistema de consulta de CPF nos últimos ".VERIFY_TIME." minutos:".PHP_EOL;
            while($rs_row = pg_fetch_array($rs)){
                $list_errors .= $rs_row['data']. " - ".$rs_row['erro_sistema'].PHP_EOL;
            }
            $msg_log .= $list_errors.PHP_EOL;
            
            echo utf8_encode($msg_log);

            if($num_rows >= ALERT_NUMBER){
                $msg_prob = "Foram detectados ".$num_rows." problema(s) com o sistema de consulta de CPF nos últimos ".VERIFY_TIME." minutos!".PHP_EOL."<br>Por favor, verifique se há algo de errado! Mais detalhes abaixo:<br><br>".str_replace(PHP_EOL, "<br><br>", $list_errors);
                enviaEmail($destino, null, null, $assunto, $msg_prob);
            }
        }
    }
    else{
        echo "Problema ao executar a query: ".$sql.PHP_EOL.PHP_EOL;
    }
    
    $arquivoLog->deleteLockedFile();
    
} else {
    $arquivoLog->showBusy();
}

//Fechando Conexão
pg_close($connid);
?>