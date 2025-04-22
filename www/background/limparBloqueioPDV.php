<?php

    require "/www/db/connect.php";
	require "/www/db/ConnectionPDO.php";
	
	$conexao = ConnectionPDO::getConnection()->getLink();
	
	$sql = "delete from bloqueios_login_pdv where TO_CHAR((created + INTERVAL '1 day'), 'YYYY-MM-DD') = :EXP and TO_CHAR((created + INTERVAL '1 day'), 'HH24:MI') >= :HR;";
	$deleteRows = $conexao->prepare($sql);
	$deleteRows->bindValue(":EXP", date("Y-m-d"));
	$deleteRows->bindValue(":HR", date("H:i"));
	$deleteRows->execute();

?>