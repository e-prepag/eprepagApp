<?php ob_start();

require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";

if($opr_codigo==0) 
	$opr_codigo = $_GET["opr_codigo"];
//echo "opr_codigo: ".$opr_codigo."<br>";
//echo "arquivo: ".$arquivo."<br>";
//echo "<pre>";
//print_r($_POST);
//print_r($_GET);
//echo "</pre>";
//echo "<hr>";

//Diretorio repositorio
//$folder = "/home/sites/backoffice/offweb/dist_commerce/lotes/";
$folder = $raiz_do_projeto . "log/";
$file = $folder . $opr_codigo . "/" . $arquivo;
if(isset($tipo)) $file = $folder . $opr_codigo . "/" . $tipo . "/" . $arquivo;

$msg = "";

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
//header("Content-Length: " . (string) filesize($file));
header("Content-Disposition: inline; filename=" . $arquivo);
//echo "file: $file<br>";

$sret = file_get_contents($file);
print(str_replace("\n","<br>\n",$sret));

ob_end_flush(); 
exit;

//$handle = fopen($file, "rb");
////print(str_replace("\n","<br>",fread($handle, filesize($file))));
//print(fread($handle, filesize($file)));
//fclose($handle);


//ob_end_flush(); 
//exit;
?>
