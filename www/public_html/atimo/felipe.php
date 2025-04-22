<?php

$raiz_do_projeto = "/www/";
require_once $raiz_do_projeto."includes/gamer/chave.php";
require_once $raiz_do_projeto."includes/gamer/AES.class.php";
require_once $raiz_do_projeto."class/classEncryption.php";
require_once $raiz_do_projeto."db/connect.php";
require_once $raiz_do_projeto."db/ConnectionPDO.php";

$chave256bits = new Chave();
$aes = new AES($chave256bits->retornaChave());
$conexao = ConnectionPDO::getConnection()->getLink();

$pinH = '2ynTz/WT3wu9Q/KtrYcuyQ==';

 $ret = $aes->decrypt(base64_decode(trim($pinH)));
//$ret = base64_encode($aes->encrypt($pinH));
// $pin = $value["pin_codigo"];
	// $hashPin = base64_encode($aes->encrypt($pin));

	//encripta senha
	$objEncryption = new Encryption();
	$senha = 'AzgAAwlPPQBdEH9R';
	$senha = $objEncryption->decrypt(trim($senha));
var_dump($senha);