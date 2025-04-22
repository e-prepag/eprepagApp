<?php
	require_once "/www/db/connect.php";
	require_once "/www/db/ConnectionPDO.php";
	
	$conexao = ConnectionPDO::getConnection()->getLink();
	
	// var_dump($conexao);
	
	$query = $conexao->prepare("SELECT * FROM usuarios_games LIMIT 10");
	
	$query->execute();
	
	$result = $query->fetchAll(PDO::FETCH_ASSOC);
	
	echo "<pre>";
	
	print_r($result);
	
	echo "</pre>";