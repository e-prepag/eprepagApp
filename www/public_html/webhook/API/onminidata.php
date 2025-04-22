<?php

	//ini_set('display_errors', 1);
	//ini_set('display_startup_errors', 1);
	//error_reporting(E_ALL);

    require "/www/db/connect.php";
	require "/www/db/ConnectionPDO.php";
    require "/www/consulta_cpf/Onminidata.php";

    $onminidata = new Onminidata();
    $onminidata->query($_POST['cpf'], $_POST['data_nascimento']);
	$result = $onminidata->collects_data();
	$id_search = $onminidata->take_property($result, "id_search");

    var_dump($id_search);

?>