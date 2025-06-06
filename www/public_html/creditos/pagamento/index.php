<?php
require_once "../../../includes/constantes.php";
require_once DIR_INCS . "main.php";
require_once DIR_INCS . "pdv/main.php";
require_once DIR_CLASS . "pdv/classOperadorGamesUsuario.php";

$_PaginaOperador2Permitido = 54; 
validaSessao();

if(!isset($_SESSION["seg_ip"]) || $_SESSION["seg_ip"] === false){
	header("location: /creditos/chave.php");
	exit;
}

$msg = "";

//Bloco Prepag Money Distribuidor
$usuarioGames = unserialize($_SESSION['dist_usuarioGames_ser']);
//var_dump($usuarioGames);
$pagto = $usuarioGames->getPerfilFormaPagto();
//echo "<hr><pre>".print_r($FORMAS_PAGAMENTO,true)."</pre><hr>";
//die($pagto);
if(!$pagto || trim($pagto) == "" || !is_numeric($pagto)) 	$msg = "Forma de Pagamento não definida.\n";
else if(!in_array($pagto, $FORMAS_PAGAMENTO))				$msg = "Forma de Pagamento definida é inválida.\n";

if($msg != ""){
        $msg .= $ENTRE_CONTATO_CENTRAL; 		
//        $strRedirect = "/prepag2/dist_commerce/mensagem.php?msg=" . urlencode($msg) . "&pt=" . urlencode("Forma de Pagamento") . "&link=" . urlencode("/prepag2/dist_commerce/carrinho.php");
        $strRedirect = "/creditos/erro.php?msg=" . urlencode($msg) . "&pt=" . urlencode("Forma de Pagamento") . "&link=" . urlencode("/prepag2/dist_commerce/carrinho.php");
} else {
        $_SESSION['dist_pagamento.pagto'] = $pagto;
        //$strRedirect = "/prepag2/dist_commerce/finaliza_venda_preview.php";
        $strRedirect = "/creditos/carrinho/";
}

//Fechando Conex�o
pg_close($connid);


redirect($strRedirect);
