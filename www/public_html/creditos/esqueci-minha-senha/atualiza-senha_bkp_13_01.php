<?php

	if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST['checked'] === 'true') {
		
		require_once "/www/db/connect.php";
		require_once "/www/db/ConnectionPDO.php";
		
		require_once 'functions-esqueci-minha-senha.php';
		
		$novaSenha = $_POST['novaSenha'];
		$confirmacaoNovaSenha = $_POST['confirmacaoNovaSenha'];
		$codigoValidacao = $_POST['codigoValidacao'];
		$origemUsuario = $_POST['origemUsuario'];

		// Verifica se as senhas coincidem
		if ($novaSenha === $confirmacaoNovaSenha) {
			
			$validacaoSenha = validarSenha($confirmacaoNovaSenha);
			
			if ($validacaoSenha == 'true') {
				
				$novaSenhaCriptografada = criptografaSenha($confirmacaoNovaSenha);
				
				$dadosUsuario = capturaDadosSolicitacao($codigoValidacao, $origemUsuario);
				
				$idUsuario = $dadosUsuario[0]['ug_id'];
				$idIp = $dadosUsuario[0]['ip'];
				$idDataTrocaSenha = capturaDataHoraAtual();
				
				registraNovaSenha($novaSenhaCriptografada, $idUsuario);
				
				defineStatusSenhaAtualizada($codigoValidacao);
	
				atualizaHistoricoCliente($idUsuario, $idIp, $idDataTrocaSenha);
				
				$arquivoLog = 'logEsqueciMinhaSenha.log';
				
				$mensagemLog = "CÓDIGO: {$codigoValidacao} -- A senha foi atualizada";
				
				geraLogAtualizaSolicitacao($arquivoLog, $mensagemLog);
				
				redirecionaSucesso();
				
			} else {
				
				capturaDadosSolicitacao($codigoValidacao, $origemUsuario);
				
				defineStatusErro($codigoValidacao);
				
				$arquivoLog = 'logEsqueciMinhaSenha.log'; 
				
				$mensagemLog = 'Forçou entrada de uma senha fora do padrão';
				
				geraLogAtualizaSolicitacao($arquivoLog, $mensagemLog);
				
				redirecionaAcessoNaoAutorizado();
				
			}
		} else {
			
			defineStatusErro($codigoValidacao);
			
			$arquivoLog = 'logEsqueciMinhaSenha.log'; 
			
			$mensagemLog = 'Forçou a entrada de duas senhas que não coincidem';
				
			geraLogAtualizaSolicitacao($arquivoLog, $mensagemLog);
			
			redirecionaAcessoNaoAutorizado();
			
		}
	} else {
		
		redirecionaAcessoNaoAutorizado();
		
	}