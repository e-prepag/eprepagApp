<?php
	
	require_once "/www/db/connect.php";
	require_once "/www/db/ConnectionPDO.php";

	require_once 'functions-esqueci-minha-senha.php';
	
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['redirected'] === 'true' && $_POST['origemUsuario'] === 'pdv') {
	
		$origemUsuario = $_POST['origemUsuario'];
		$loginUsuario = trim($_POST['loginUsuario']);
		$emailUsuario = trim($_POST['emailUsuario']);
		
		$idUsuario = encontraIdUsuario($loginUsuario, $emailUsuario);
		
		if ($idUsuario === 'Usuario não encontrado') {
			
			include "includes/header.php";
			
?>

			<div style="padding: 50px 0" class="container txt-cinza bg-branco">
				<h1 class="top20 text-center">Usuário não encontrado!</h1>
				<h4 class="top20 txt-azul-claro text-center"><strong>Tente novamente mais tarde</strong></h4>
				<p class='top20 text-center'>Você será redirecionado em <span id="countdown"></span> segundos...</p>
			</div>
			
			<script src='script/countdownRedirect.js'></script>
			
<?php

			include "includes/footer.php";
			
			die();
			
		}
		
		$ipUsuario = capturaIp();
		
		$timestamp = capturaTimeStamp();
		
		$nomeCompletoUsuario = encontraNomeUsuario($loginUsuario, $emailUsuario);
		
		$codigoValidacao = geraCodigoValidacao($idUsuario, $emailUsuario, $timestamp);
					
		$divideNome = explode(' ', $nomeCompletoUsuario);
		
		$primeiroNomeUsuario = $divideNome[0];
					
					
		$dadosUsuario = [
			'origemUsuario' => $origemUsuario,
			'idUsuario' => $idUsuario,
			'ipUsuario' => $ipUsuario,
			'loginUsuario' => $loginUsuario,
			'emailUsuario' => $emailUsuario,
			'nomeCompletoUsuario' => $nomeCompletoUsuario,
			'primeiroNomeUsuario' => $primeiroNomeUsuario	
		];
		
		include "includes/header.php";
					
?>

			<div style="padding: 50px 0" class="container txt-cinza bg-branco">
				<h1 class="top20 text-center">Foi enviado um e-mail para você</h1>
				<h4 class="top20 txt-azul-claro text-center"><strong>Não se esqueça de conferir a sua <span style="border-bottom: 2px solid #555; padding-bottom: 2px;">caixa de spam</span>.</strong></h4>
				<p class='top20 text-center'>Você será redirecionado em <span id="countdown"></span> segundos...</p>
			</div>
			
			<script src='script/countdownRedirect.js'></script>

<?php
		
		registraNovaSolicitacao($dadosUsuario, $codigoValidacao, $timestamp);
					
		enviaEmailParaValidarSolicitacao($dadosUsuario, $codigoValidacao);
		
		include "includes/footer.php";
	
	} else {
		
		redirecionaAcessoNaoAutorizado();
		
	}