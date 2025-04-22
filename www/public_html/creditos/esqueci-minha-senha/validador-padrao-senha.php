<?php

	if ($_SERVER["REQUEST_METHOD"] == "POST") {
		
		require_once 'functions-esqueci-minha-senha.php';
		
		$novaSenha = $_POST['novaSenha'];
		$confirmacaoNovaSenha = $_POST['confirmacaoNovaSenha'];

		// Verifica se as senhas coincidem
		if ($novaSenha === $confirmacaoNovaSenha && validarSenha($confirmacaoNovaSenha)) {
			
			echo 'true';
			
		} else {
			
			echo 'false';
			
		}
	} else {
		
		redirecionaAcessoNaoAutorizado();
		
	}
