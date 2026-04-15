<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

// [SFTP DESATIVADO] require_once '../sftp/connect.php';
// [SFTP DESATIVADO] require_once '../sftp/classSFTPconnection.php';

$json = file_get_contents("../json/file_info.json");
$arrFiles = json_decode($json);

//varrendo json de arquivos para enviar via sftp
foreach($arrFiles->files as $file){

    echo "Origem: ".$file->origem.PHP_EOL ;
    echo "=> Destino: ".$file->destino.PHP_EOL;

    try
    {
        if(file_exists($file->origem)) {
            // [SFTP DESATIVADO] $sftp = new SFTPConnection($server, $port);
            // [SFTP DESATIVADO] $sftp->login($user, $pass);
            // [SFTP DESATIVADO] $sftp->uploadFile($file->origem, $file->destino);
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