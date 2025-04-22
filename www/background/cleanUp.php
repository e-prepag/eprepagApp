<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

require_once '../includes/constantes.php';
require_once $raiz_do_projeto . "class/classManipulacaoArquivosLog.php";

$arquivoLog = new ManipulacaoArquivosLog($argv);

$nome_arquivo = $arquivoLog->getNomeArquivo();

ob_start('callbackLog');

$shellcomm = implode(' ', $argv);
$minutes = 60;
$debug = false;

$matches = array();
if( preg_match('/--minutes=([0-9]{1,})/', $shellcomm, $matches) )
        $minutes = $matches[1];

$matches = array();
if( preg_match('/--debug/', $shellcomm, $matches) )
        $debug=true;

if($debug)  echo "Escaneando arquivos .locked ... ".PHP_EOL;

$path = $raiz_do_projeto."log/";
$files = glob($path . "*.locked");

if($debug && count($files)==0)  echo "Nao foi encontrado nenhum arquivo .locked ... ".PHP_EOL;

foreach( $files as $file ){
    echo PHP_EOL."Data execuчуo : ".date('Y-m-d H:i:s').PHP_EOL.PHP_EOL;

    if($debug)  echo "Encontrou arquivo ".str_replace($path, '', $file)." ... ".PHP_EOL; 

    if(file_exists($file)) {
        $modtime = filemtime($file);
        $diff = (int) round(abs(time() - $modtime) / 60,2);

        if($debug)  echo "Modificado a $diff minutos atras... ".PHP_EOL;

        //Testando se o tempo de execuчуo щ maior que o limite desejado
        if( $diff > $minutes ){
            $content =  (file_exists($file)?trim(file_get_contents($file)):NULL);
            if(!empty($content)) {
                echo "Em [".date("Y-m-d H:i:s")."] o".PHP_EOL."Arquivo [".str_replace($path, '', $file)."] contendo o".PHP_EOL."Processo [".$content."] que estava executando a [".$diff."] minutos foi forчado a terminar.".PHP_EOL.str_repeat("=", 80).PHP_EOL.PHP_EOL;
                $arquivoLog->killProcess($content);
                unlink($file);
            }//end if(!empty($content))
            else echo "Conteudo do arquivo vazio ou Arquivo nуo existe mais!".PHP_EOL.str_repeat("=", 80).PHP_EOL.PHP_EOL;
        }//end if( $diff > $minutes )
        else echo "Arquivo: $file com apenas $diff minutos.".PHP_EOL.str_repeat("=", 80).PHP_EOL.PHP_EOL;
    }//end if(file_exists($file))
    else echo "Ao tentar acessar o arquivo ".$file." este nуo existia mais!".PHP_EOL.str_repeat("=", 80).PHP_EOL.PHP_EOL;
}//end foreach

?>