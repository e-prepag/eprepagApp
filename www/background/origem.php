<?php

require '/www/includes/constantes.php';
require "/www/db/connect.php";
require "/www/db/ConnectionPDO.php";
require "/www/consulta_cpf/config.inc.cpf.php";
require "/www/consulta_cpf/trocaAutomatica.php";

$quantidade = verificaContagem();
if(isset($quantidade) && $quantidade > 5){
    trocaOrigemAutomatica(2);
}


?>