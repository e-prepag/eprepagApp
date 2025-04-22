<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

    require "/www/db/connect.php";
	require "/www/db/ConnectionPDO.php";
    require "/www/consulta_cpf/Onminidata.php";

    $onminidata = new Onminidata();
    $onminidata->query("04673625137", "01/08/1993");
/*
	71919422153	
	12/04/1982
	
	08910564911
	15/11/1997
*/
	$result = $onminidata->collects_data();
	$id_search = $onminidata->take_property($result, "id_search");
	sleep(20);
    print_r($onminidata->result_status_search($id_search));

?>