<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require "/www/class/pdv/classChaveMestra.php";

$classChave = new ChaveMestra();
$senha = "CG*Bs6sckJ30DQN";

$retornoVereficacao = $classChave->verificaSenha(17371, $senha);

var_dump($retornoVereficacao);
//19162


?>