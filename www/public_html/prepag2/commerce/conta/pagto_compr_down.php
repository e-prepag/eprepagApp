<?php 

header("Content-Type: text/html; charset=ISO-8859-1; P3P: CP='CAO PSA OUR'",true);
require_once "../../../../includes/constantes.php";
require_once DIR_INCS . "main.php";
require_once DIR_INCS . "gamer/main.php";
validaSessao(); 

require_once DIR_INCS . "gamer/venda_e_modelos_logica_epp.php";

$file = $FOLDER_COMMERCE_UPLOAD . $arquivo;

$msg = "";

//Validacao
if(!is_file($file)) $msg = "Nenhum arquivo encontrado.\n";

//Redireciona se ha algum dado invalido
//----------------------------------------------------
if($msg != ""){
	$strRedirect = "/prepag2/commerce/mensagem.php?msg=" . urlencode($msg) . "&pt=" . urlencode("Comprovante") . "&link=" . urlencode("/prepag2/commerce/conta/lista_vendas.php");
	redirect($strRedirect);
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
