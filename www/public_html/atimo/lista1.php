<?php 
error_reporting(E_ALL); 
ini_set("display_errors", 1); 

$raiz_do_projeto = "/www/";
require_once "/www/includes/main.php";
require_once $raiz_do_projeto . "includes/pdv/main.php";
require_once $raiz_do_projeto . "includes/functions.php";
require_once $raiz_do_projeto . "class/classManipulacaoArquivosLog.php";

//$arquivoLog = new ManipulacaoArquivosLog($argv);

//if(!$arquivoLog->haveFile()) {
    //$arquivoLog->createLockedFile();
    //$nome_arquivo = $arquivoLog->getNomeArquivo();

    //ob_start('callbackLog');
    
    //Printando hora de execuчуo
    echo str_repeat("=", 80)."\n".date("Y-m-d H:i:s").PHP_EOL;
    
	$argv = ["processaAgendamentos","--log=conciliacaoPDV_12","--lista=1,2"];
    //Capturando argumento da lista de finais de VG_IDs
    $shellcommand = implode(" ", $argv);
    $lista_finais = NULL;
    $pattern = '/--lista=([^ ]+)/';
    $match = array();
    if( preg_match($pattern, $shellcommand, $match) ){
	    echo "ok";
        $lista_finais = $match[1];
    }
    if(!is_null($lista_finais)) {
        echo "Lista de finais de UG_IDs [".$lista_finais."]".PHP_EOL;
    }

    //processaAgendamentos
    if(in_array("processaAgendamentos", $argv)) echo processaAgendamentos($lista_finais); 

  //  $arquivoLog->deleteLockedFile();
//}
//else {
  //  $arquivoLog->showBusy();
//}


//Fechando Conexуo
pg_close($connid);

?>