<?php 
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

set_time_limit(6000);
ini_set('max_execution_time', 6000); 

require_once "../includes/main.php";
require_once $raiz_do_projeto . "includes/gamer/main.php";

//Processa Email de Aviso	
echo str_repeat("=", 80).PHP_EOL."Processando Saldo de Gamer - Lembrete de Saldo ".date("Y-m-d H:i:s").PHP_EOL.str_repeat("=", 80).PHP_EOL;

$objEnvioEmailAutomatico = new EnvioEmailAutomatico('G','SaldoGamer',24,0.99,25);
echo $objEnvioEmailAutomatico->MontaEmail();

echo str_repeat("=", 80).PHP_EOL." Fim - ".date('Y-m-d H:i:s').PHP_EOL.str_repeat("=", 80).PHP_EOL;

//Fechando Conexão
pg_close($connid);

?>