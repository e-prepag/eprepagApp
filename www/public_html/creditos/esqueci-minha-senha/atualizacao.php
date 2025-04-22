<?php

	require_once "/www/db/connect.php";
	require_once "/www/db/ConnectionPDO.php";

	require_once 'functions-esqueci-minha-senha.php';

	if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['redirected'] === 'true' && $_GET['origemUsuario'] === 'pdv') {
		
		$codigoValidacao = $_GET['codigoValidacao'];
		$origemUsuario = $_GET['origemUsuario'];
		
		if (strlen($codigoValidacao) === 12 && $origemUsuario === 'pdv') {
			
			$resposta = capturaDadosSolicitacao($codigoValidacao, $origemUsuario);

			if (empty($resposta)) {
				
				$resposta = 'Acesso negado! O c�digo n�o foi informado';
				
				$arquivoLog = 'logEsqueciMinhaSenha.log';
		
				$mensagemLog = $resposta;

				geraLogNovaSolicitacao($arquivoLog, $mensagemLog);
				
				redirecionaAcessoNaoAutorizado();
				
			} elseif ($resposta[0]['status'] === 'Mudando Senha') {
				
				redirecionaAcessoNaoAutorizado();
			
			} elseif ($resposta[0]['status'] === 'Validado') {
				
				$dadosUsuario = $resposta;
				
				$dataHoraAtual = capturaDataHoraAtual();
				
				$arquivoLog = 'logEsqueciMinhaSenha.log';
				
				$mensagemLog = "O usu�rio acessou a p�gina de atualiza��o de senha\nDATA / HORA: $dataHoraAtual\nID: {$resposta[0]['ug_id']}\nNOME: {$resposta[0]['ug_nome_completo']}\nC�DIGO: {$codigoValidacao}";
		
				geraLogAtualizaSolicitacao($arquivoLog, $mensagemLog);
				
				defineStatusAlterandoSenha($codigoValidacao);
				
				require_once 'form-nova-senha.php';
				
			} else {
				
				$resposta = "Acesso negado! O c�digo informado n�o existe no banco: {$codigoValidacao}";
				
				$arquivoLog = 'logEsqueciMinhaSenha.log';
		
				$mensagemLog = $resposta;

				geraLogAtualizaSolicitacao($arquivoLog, $mensagemLog);
				
				redirecionaAcessoNaoAutorizado();
				
			}
			
		} else {
			
			$resposta = "Acesso negado! C�digo fora do padr�o: {$codigoValidacao}";
			
			$arquivoLog = 'logEsqueciMinhaSenha.log';
		
			$mensagemLog = $resposta;

			geraLogAtualizaSolicitacao($arquivoLog, $mensagemLog);
			
			redirecionaAcessoNaoAutorizado();
			
		}
		
	}