<?php
	
	$raiz_do_projeto = '/www/';
	
	require_once $raiz_do_projeto."includes/constantes.php";
	//require_once $raiz_do_projeto."backoffice/includes/topo.php";
	require_once $raiz_do_projeto."includes/main.php";
	require_once $raiz_do_projeto."includes/gamer/main.php";
	
	require_once "./UsuarioVip.php";
	
	if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST)) {
		
		$ug_id = $_POST['ug_id'];
		$op_id = $_POST['op_id'];
		$op_nome = $_POST['op_nome'];
		
		$usuario = new UsuarioVip();
    
		$resultado = $usuario->setGamerVip($ug_id, $op_id, $op_nome);
    
		echo $resultado;
		
	} else {
		$resultado = 'Erro na requisição - #111';
		
		echo $resultado;
	}