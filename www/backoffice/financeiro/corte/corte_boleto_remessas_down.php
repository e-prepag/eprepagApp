<?php ob_start(); ?>
<?php 
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php"; 
?>
<?php
//Diretorio repositorio
$folder = $raiz_do_projeto . "arquivos_gerados/corte/";
if(isset($tipo) && $tipo == 1) $dir = "remessaBradesco/";
//else $dir = ""; 
//$file = $folder . $dir . $arquivo;
//echo "dirA: ".$file."<br>";

//echo "arquivo: ".$arquivo."<br>";
//echo "strpos(arquivo,' ('): ".strpos($arquivo," (")."<br>";

$arquivo = substr($arquivo,0,strpos($arquivo," ("));
$file = $folder . $dir . $arquivo;

//echo "dir: ".$file."<br>";
$msg = "";
//die("Stop");

//Validacao
if(!is_file($file)) $msg = "Nenhum arquivo encontrado.\n";

//Redireciona se ha algum dado invalido
//----------------------------------------------------
if($msg != ""){
	echo $msg;
	exit;
}

ob_clean(); 

$extensao = substr(strrchr($arquivo, "."), 1);
//header("Content-Type: " . obtemContentType($extensao));
header("Content-Type: application/x-octet-stream");
header("Content-Length: " . (string) filesize($file));
header("Content-Disposition: inline; filename=" . $arquivo);

$handle = fopen($file, "rb");
print(fread($handle, filesize($file)));
fclose($handle);
ob_end_flush(); 
exit;
?>
