<?php

//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 


require_once "../includes/main.php";
require_once $raiz_do_projeto . "includes/gamer/main.php";
require_once $raiz_do_projeto . "banco/gocash/config.inc.php";
//include_once("C:/Sites/E-Prepag/www/web/prepag2/pag/gca/includes/function.files.inc.php");

$sdate_now = date("Y-m-d H:i:s");

// Lê monitor
gocash_monitor_load($params_in);
echo "".print_r($params_in, true)."\n\n";

// Prepara o array de saida
$params_out = array();
$params_out['Titulo'] = $params_in['Titulo'];
$params_out['DateCreated'] = $sdate_now;

// Contata o WebService
$gc = @new GoCashAPI();
if($gc->get_service_status()) {
	echo "gocash_process_monitor.php: Is ONLINE\n";
	$params_out['LastStatus'] = "Running";
	if($params_in['LastStatus'] == "Running") {
		// Continua
		$params_out['DateLastStatus'] = $params_in['DateLastStatus'];
	} else {
		// Estava parado, voltou -> muda o status
		$params_out['DateLastStatus'] = $sdate_now;
	}
} else {
	echo "gocash_process_monitor.php: Is OFFLINE\n";
	$params_out['LastStatus'] = "Stopped";
	if($params_in['LastStatus'] == "Stopped") {
		// Continua
		$params_out['DateLastStatus'] = $params_in['DateLastStatus'];
	} else {
		// Estava parado, voltou -> muda o status
		$params_out['DateLastStatus'] = $sdate_now;
	}
        enviaEmail("glaucia@e-prepag.com.br", null, "wagner@e-prepag.com.br", "GoCASH OFFLINE", "GoCASH está OFFLINE");
}

/*
echo "data_last_status: '".$params['DateLastStatus']."'<br>";
echo "last_status: '".$params['LastStatus']."'<br>";
echo "data_created: '".$params['DateCreated']."'<br>";
*/
// Salva monitor
gocash_monitor_save($params_out);

/*
// Monitor File Structure
Titulo:GoCash monitor File
DateCreated:2012-08-16 13:32:34
LastStatus:Running
DateLastStatus:2012-08-16 13:32:34

*/

?>
