<?php

	if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST['checked'] === 'true') {
		 // Log dos dados recebidos no POST
		 $arquivoLogPost = 'logPostData.log';
		 salvarLog($arquivoLogPost, $_POST);

		 $dadosPost = print_r($_POST, true);
   		 error_log("Dados recebidos no POST:\n" . $dadosPost);
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
				
				$mensagemLog = "C�DIGO: {$codigoValidacao} -- A senha foi atualizada";
				
				geraLogAtualizaSolicitacao($arquivoLog, $mensagemLog);
				
				redirecionaSucesso();
				
			} else {
				
				capturaDadosSolicitacao($codigoValidacao, $origemUsuario);
				
				defineStatusErro($codigoValidacao);
				
				$arquivoLog = 'logEsqueciMinhaSenha.log'; 
				
				$mensagemLog = 'For�ou entrada de uma senha fora do padr�o';
				
				geraLogAtualizaSolicitacao($arquivoLog, $mensagemLog);
				
				redirecionaAcessoNaoAutorizado();
				
			}
		} else {
			
			defineStatusErro($codigoValidacao);
			
			$arquivoLog = 'logEsqueciMinhaSenha.log'; 
			
			$mensagemLog = 'For�ou a entrada de duas senhas que n�o coincidem';
				
			geraLogAtualizaSolicitacao($arquivoLog, $mensagemLog);
			
			redirecionaAcessoNaoAutorizado();
			
		}
	} else {
		
		redirecionaAcessoNaoAutorizado();
		
	}

	function salvarLog($arquivoLog, $dados) {
		$dataAtual = date("Y-m-d H:i:s");
		$mensagem = "[{$dataAtual}] " . print_r($dados, true) . PHP_EOL;
		file_put_contents($arquivoLog, $mensagem, FILE_APPEND);
	}