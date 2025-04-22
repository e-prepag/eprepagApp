<?php

require "/www/db/connect.php";
require "/www/db/ConnectionPDO.php";

    if($_SERVER["REMOTE_ADDR"] == "201.93.162.169"){
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);
	}	

	require "/www/banco/pix/cielo/config.inc.pix.php";
	 	  
	//$pix = new Pix("cpf", "06743611131", "Andre Silva do Nascimento", "1020231030091448569", "010");
	//echo $pix->callService(); 
   
?>