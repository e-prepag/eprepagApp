<?php
require_once "../includes/main.php";
require_once $raiz_do_projeto . "includes/pdv/main.php";
require_once $raiz_do_projeto . "includes/pdv/corte_constantes.php";
require_once $raiz_do_projeto . "includes/pdv/corte_functions.php";
require_once $raiz_do_projeto . "banco/bradesco/funcoes_bradesco.php";

//processaCorte
if(in_array("processaCorte", $argv)) echo processaCorte(); 

//processaLimiteSugerido
if(in_array("processaLimiteSugerido", $argv)) echo processaLimiteSugerido(); 

//processaZeraLimiteBoletoVencido
if(in_array("processaZeraLimiteBoletoVencido", $argv)) echo processaZeraLimiteBoletoVencido(); 

//Fechando Conexão
pg_close($connid);

?>