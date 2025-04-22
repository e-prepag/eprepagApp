<?php
//  Dados do Banco de Dados

define('DB_HOST', '10.204.134.61');
//define('DB_HOST', '192.168.4.1');
//define('DB_HOST', '11.0.0.1');
//define('DB_HOST', '187.45.202.15');
//define('DB_HOST', '186.202.139.58');
define('DB_PORT', '5433');

//Dados de Produção
define('DB_BANCO', 'db_epp_prod');
define('DB_USER', 'epp_prod');
define('DB_PASS', 'db@eprepag2013');

//Dados de Homologação
//define('DB_BANCO', 'epp_test');
//define('DB_USER', 'epp_prod');
//define('DB_PASS', 'db@eprepag2013');

//Conectando ao Banco de dados
$connid = pg_connect("host=".DB_HOST." port=".DB_PORT." dbname=".DB_BANCO." user=".DB_USER." password=".DB_PASS);

?>
