<?php

require_once '/www/includes/constantes.php';
require_once $raiz_do_projeto."includes/gamer/chave.php";
require_once $raiz_do_projeto."includes/gamer/AES.class.php";

//Instanciando Objetos para Descriptografia
$chave256bits = new Chave();
$aes = new AES($chave256bits->retornaChavePub());
//$passw = base64_encode($aes->encrypt(addslashes("gokei@2023")));
$passw = $aes->decrypt(base64_decode("Ygnz7bfmxO0o7HvP8OL/NQ=="));

var_dump($passw);
?>