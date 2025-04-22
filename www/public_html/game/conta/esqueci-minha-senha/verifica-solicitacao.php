<?php
	
	require_once "/www/db/connect.php";
	require_once "/www/db/ConnectionPDO.php";

	require_once 'functions-esqueci-minha-senha.php';
	
	if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['redirected'] === 'true' && $_GET['origemUsuario'] === 'gamer') {
		
		$codigoValidacao = $_GET['codigoValidacao'];
		
		$resultado = verificaCodigoValidacao($codigoValidacao);
		
		if ($resultado === 'Expirado') {
		
			expiraSolicitacao($codigoValidacao);
			
		} elseif ($resultado === 'Validado') {
			
			validaSolicitacao($codigoValidacao);
			
			header("Location: atualizacao.php?redirected=true&codigoValidacao={$codigoValidacao}&origemUsuario=gamer");
			die();
			
		}
		
	} else {
		redirecionaAcessoNaoAutorizado();
	}