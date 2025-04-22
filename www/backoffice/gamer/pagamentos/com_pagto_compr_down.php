<?php

require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/gamer/main.php";

?>

<?php
$file = $FOLDER_COMMERCE_UPLOAD . $arquivo;

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
header("Content-Type: " . obtemContentType($extensao));
header("Content-Length: " . (string) filesize($file));
header("Content-Disposition: inline; filename=" . $arquivo);

$handle = fopen($file, "rb");
print(fread($handle, filesize($file)));
fclose($handle);
ob_end_flush(); 
exit;
?>
