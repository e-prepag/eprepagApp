<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

require_once '../sftp/connect.php';
require_once '../sftp/classSFTPconnection.php';

$json = file_get_contents("../json/file_info.json");
$arrFiles = json_decode($json);

//varrendo json de arquivos para enviar via sftp
foreach($arrFiles->files as $file){

    echo "Origem: ".$file->origem.PHP_EOL ;
    echo "=> Destino: ".$file->destino.PHP_EOL;

    try
    {
        if(file_exists($file->origem)) {
            $sftp = new SFTPConnection($server, $port);
            $sftp->login($user, $pass);
            $sftp->uploadFile($file->origem, $file->destino);
            echo PHP_EOL;
        }
        else echo "Arquivo de origem não existe!".PHP_EOL.PHP_EOL;
    }
    catch (Exception $e)
    {
        echo $e->getMessage().PHP_EOL;
    }
    echo PHP_EOL;

} //end foreach

echo PHP_EOL."Fim de processamento.".PHP_EOL;

?>