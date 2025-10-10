<?php

	require_once "/www/db/connect.php";
	require_once "/www/db/ConnectionPDO.php";

	require_once 'functions-esqueci-minha-senha.php';

	if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['redirected'] === 'true' && $_GET['origemUsuario'] === 'gamer') {
		
		$codigoValidacao = $_GET['codigoValidacao'];
		$origemUsuario = $_GET['origemUsuario'];
		
		if (strlen($codigoValidacao) === 12 && $origemUsuario === 'gamer') {
			
			$resposta = capturaDadosSolicitacao($codigoValidacao, $origemUsuario);

			if (empty($resposta)) {
				
				$resposta = 'Acesso negado! O código não foi informado';
				
				$arquivoLog = '/www/log/logEsqueciMinhaSenha.log';
		
				$mensagemLog = $resposta;

				geraLogNovaSolicitacao($arquivoLog, $mensagemLog);
				
				redirecionaAcessoNaoAutorizado();
				
			} elseif ($resposta[0]['status'] === 'Mudando Senha') {
				
				redirecionaAcessoNaoAutorizado();
			
			} elseif ($resposta[0]['status'] === 'Validado') {
				
				$dadosUsuario = $resposta;
				
				$dataHoraAtual = capturaDataHoraAtual();
				
				$arquivoLog = '/www/log/logEsqueciMinhaSenha.log';
				
				$mensagemLog = "O usuário acessou a página de atualização de senha\nDATA / HORA: $dataHoraAtual\nID: {$resposta[0]['ug_id']}\nNOME: {$resposta[0]['ug_nome_completo']}\nCÓDIGO: {$codigoValidacao}";
		
				geraLogAtualizaSolicitacao($arquivoLog, $mensagemLog);
				
				defineStatusAlterandoSenha($codigoValidacao);
				
				require_once 'form-nova-senha.php';
				
			} else {
				
				$resposta = "Acesso negado! O código informado não existe no banco: {$codigoValidacao}";
				
				$arquivoLog = '/www/log/logEsqueciMinhaSenha.log';
		
				$mensagemLog = $resposta;

				geraLogAtualizaSolicitacao($arquivoLog, $mensagemLog);
				
				redirecionaAcessoNaoAutorizado();
				
			}
			
		} else {
			
			$resposta = "Acesso negado! Código fora do padrão: {$codigoValidacao}";
			
			$arquivoLog = '/www/log/logEsqueciMinhaSenha.log';
		
			$mensagemLog = $resposta;

			geraLogAtualizaSolicitacao($arquivoLog, $mensagemLog);
			
			redirecionaAcessoNaoAutorizado();
			
		}
		
	}