<?php 

header("Content-Type: text/html; charset=ISO-8859-1; P3P: CP='CAO PSA OUR'",true);
require_once "../../../../includes/constantes.php";
require_once DIR_INCS . "main.php";
require_once DIR_INCS . "gamer/main.php";
validaSessao(); 

require_once DIR_INCS . "gamer/venda_e_modelos_logica_epp.php";

// Usa basename() para remover qualquer informaзгo de diretуrio (como ../)
// da variбvel $arquivo. Isso neutraliza o Path Traversal.
$safe_filename = basename($arquivo);
$file = $FOLDER_COMMERCE_UPLOAD . $safe_filename; // Agora $file estб seguro

$msg = "";

//Validacao (agora usa o $file seguro)
if(!is_file($file)) $msg = "Nenhum arquivo encontrado.\n";

//Redireciona se ha algum dado invalido
//----------------------------------------------------
if($msg != ""){
    $strRedirect = "/prepag2/commerce/mensagem.php?msg=" . urlencode($msg) . "&pt=" . urlencode("Comprovante") . "&link=" . urlencode("/prepag2/commerce/conta/lista_vendas.php");
    redirect($strRedirect);
}

ob_clean(); 

// Usa a variбvel segura ($safe_filename) aqui tambйm
$extensao = substr(strrchr($safe_filename, "."), 1);
header("Content-Type: " . obtemContentType($extensao));
header("Content-Length: " . (string) filesize($file));

// Usa a variбvel segura ($safe_filename) no header
header("Content-Disposition: inline; filename=" . $safe_filename);

// O restante do cуdigo de leitura agora й seguro
$handle = fopen($file, "rb");
print(fread($handle, filesize($file)));
fclose($handle);
ob_end_flush(); 
exit;

?>