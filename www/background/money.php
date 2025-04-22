<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
require_once "../includes/main.php";
require_once $raiz_do_projeto . "includes/gamer/main.php"; 

//cancelaVendasBoletoVencido
if(in_array("cancelaVendasBoletoVencido", $argv)) {
	echo "cancelaVendasBoletoVencido ======================================\r\n";
	echo cancelaVendasBoletoVencido(); 
}

//conciliacaoAutomaticaBoleto
if(in_array("conciliacaoAutomaticaBoleto", $argv)) {
	echo "conciliacaoAutomaticaBoleto ======================================\r\n";
	echo conciliacaoAutomaticaBoleto(); 
}

//conciliacaoAutomaticaBoletoExpressMoneyLH
if(in_array("conciliacaoAutomaticaBoletoExpressMoneyLH", $argv)) {
	echo "conciliacaoAutomaticaBoletoExpressMoneyLH ======================================\r\n";
	echo conciliacaoAutomaticaBoletoExpressMoneyLH();
}

//cancelaVendasEmPedidoEfetuado
if(in_array("cancelaVendasEmPedidoEfetuado", $argv)) {
	echo "cancelaVendasEmPedidoEfetuado ======================================\r\n";
	echo cancelaVendasEmPedidoEfetuado(); 
}

//Fechando Conexão
pg_close($connid);

?>