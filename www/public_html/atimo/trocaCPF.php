<?php

require "/www/includes/constantes.php";
require "/www/db/connect.php";
require "/www/db/ConnectionPDO.php";	
require "/www/consulta_cpf/config.inc.cpf.php";
require "/www/consulta_cpf/trocaAutomatica.php";

// 2 - omnidata
// 4 - hub do desenvolvedor
$retorno = apagaTroca();
if($retorno > 0 && is_numeric($retorno)){
     echo "exclusão realizada com sucesso";
}else{
     echo "Não foi possivel fazer a exclusão";  
}

?>