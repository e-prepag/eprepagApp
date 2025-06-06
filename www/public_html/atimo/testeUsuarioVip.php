<?php
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	
	$raiz_do_projeto = '/www/';
	
	
	require_once $raiz_do_projeto.'db/connect.php';
	require_once $raiz_do_projeto.'db/ConnectionPDO.php';
	require_once $raiz_do_projeto.'includes/functions.php';
	require_once $raiz_do_projeto.'class/gamer/classUsuarioVip.php';
	
	require_once $raiz_do_projeto.'class/gamer/classGamesUsuario.php';
	
	$ug_id = 1341779;
	
	
	$testeUsuario = new UsuarioGames();
	
	echo $testeUsuario->b_IsLogin_pagamento_vip(1, $usuarios_pagamento_online_vip_id);
	
?>

	