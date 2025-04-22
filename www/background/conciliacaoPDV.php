<?php 
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

require_once "../includes/main.php";
require_once $raiz_do_projeto . "includes/pdv/main.php";
require_once $raiz_do_projeto . "includes/functions.php";
require_once $raiz_do_projeto . "class/classManipulacaoArquivosLog.php";

$arquivoLog = new ManipulacaoArquivosLog($argv);

if(!$arquivoLog->haveFile()) {
    $arquivoLog->createLockedFile();
    $nome_arquivo = $arquivoLog->getNomeArquivo();

    ob_start('callbackLog');
    
    //Printando hora de execução
    echo str_repeat("=", 80)."\n".date("Y-m-d H:i:s").PHP_EOL;
    
    //Capturando argumento da lista de finais de VG_IDs
    $shellcommand = implode(" ", $argv);
    $lista_finais = NULL;
    $pattern = '/--lista=([^ ]+)/';
    $match = array();
    if( preg_match($pattern, $shellcommand, $match) ){
        $lista_finais = $match[1];
    }
    if(!is_null($lista_finais)) {
        echo "Lista de finais de UG_IDs [".$lista_finais."]".PHP_EOL;
    }
    $inicioTempo = microtime(true);
    //processaAgendamentos
    if(in_array("processaAgendamentos", $argv)) echo processaAgendamentos($lista_finais); 
    $fimTempo = microtime(true);

    echo "Tempo decorrido: " . ($fimTempo - $inicioTempo) . " segundos";
    $arquivoLog->deleteLockedFile();
}
else {
    $arquivoLog->showBusy();
}


//Fechando Conexão
pg_close($connid);

?>
