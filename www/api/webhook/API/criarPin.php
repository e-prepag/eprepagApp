<?php

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

require_once "/www/includes/constantes.php";
require_once "/www/db/connect.php";
require_once "/www/db/ConnectionPDO.php";
require_once "/www/includes/gamer/chave.php";
require_once "/www/includes/gamer/AES.class.php";
require_once "/www/includes/gamer/inc_sanitize.php";
require_once "/www/class/classGeraPin.php";

$valor = isset($_POST["valor"]) ? str_replace(",", ".", $_POST["valor"]) : null;

if (is_numeric($valor)) {
    // Aceita int ou float
    $valor = $valor + 0; // força a conversão numérica mantendo int se possível, float se necessário
} else {
    $valor = null; // valor inválido
}

if(isset($_POST["atimo"])){
	$operadora = 49;
	$distruidora = 2;
}else{
	$operadora = 53;
	$distruidora = 3;
}

$geraPinEpp = new GeraPinVariavel($valor, $operadora, $distruidora, 1);
$pin_codinterno = $geraPinEpp->gerar();
echo htmlspecialchars($pin_codinterno, ENT_QUOTES, 'UTF-8');

					
?>