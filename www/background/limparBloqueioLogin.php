<?php

    require "/www/db/connect.php";
	require "/www/db/ConnectionPDO.php";
	
	$conexao = ConnectionPDO::getConnection()->getLink();
	
	$sql = "delete from bloqueia_login_usuario where TO_CHAR(expiracao, 'YYYY-MM-DD') = :EXP and TO_CHAR(expiracao, 'HH24:MI') >= :HR;";
	$deleteRows = $conexao->prepare($sql);
	$deleteRows->bindValue(":EXP", date("Y-m-d"));
	$deleteRows->bindValue(":HR", date("H:i"));
	$deleteRows->execute();

?>