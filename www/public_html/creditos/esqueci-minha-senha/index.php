<?php

	require_once 'functions-esqueci-minha-senha.php';

	if ($_SERVER['REQUEST_METHOD'] == 'GET') {
		
		if ($_GET['redirected'] === 'true' && $_GET['origemUsuario'] === 'pdv') {
			
			include "includes/header.php";
			
		
?>
			<div class="container txt-cinza bg-branco">
			
				<h4 class="top20 txt-azul-claro text-center"><strong>Informe aqui o seu Login e E-mail</strong></h4>
				
				<div class="top20 p-bottom40">
					<form id="form" method="post" action="registra-solicitacao.php">
						<div class="row form-group">
							<div class="col-md-2"></div>
							<div class="col-md-3 text-center">
								<label for="loginUsuario">Login</label>
							</div>
							<div class="col-md-4">
								<input type="text" name="loginUsuario" id="loginUsuario" class="form-control input-sm">
							</div>
							<div class="col-md-3"></div>
						</div>
						
						<div class="row form-group">
							<div class="col-md-2"></div>
							<div class="col-md-3 text-center">
								<label for="emailUsuario">E-mail</label>
							</div>
							<div class="col-md-4">
								<input type="email" name="emailUsuario" id="emailUsuario" class="form-control input-sm">
							</div>
							<div class="col-md-3"></div>
						</div>
						<input type="hidden" name="redirected" id="redirected" value="<?php echo htmlspecialchars($_GET['redirected'], ENT_QUOTES, 'UTF-8'); ?>">
						<input type="hidden" name="origemUsuario" id="origemUsuario" value="<?php echo htmlspecialchars($_GET['origemUsuario'], ENT_QUOTES, 'UTF-8'); ?>">
						<div class="row">
							<div class="col-md-3"></div>
							<div class="col-md-6">
							
								<div id="recaptcha">
									<div class="g-recaptcha" data-sitekey="6Lc4XtkkAAAAAJrfsAKc99enqDlxXz4uq86FI9_T" data-callback="onCaptchaResolved"></div>
								</div>
								
								<button type="submit" id="btnSubmit" class="btn-block btn btn-success" disabled>Enviar</button>
							</div>
							<div class="col-md-3"></div>
						</div>
					</form>
				</div>
			</div>
			
			<script src="https://www.google.com/recaptcha/api.js" async="" defer=""></script>
			
			<script>
			
				document.addEventListener('DOMContentLoaded', () => {
				
					const form = document.getElementById('form');
					const inputLoginUsuario = document.getElementById('loginUsuario');
					const inputEmailUsuario = document.getElementById('emailUsuario');
					const btnSubmit = document.getElementById('btnSubmit');
					let respostaRecaptcha = '';
					
					// Função para verificar a validade do formulário
					function checkFormValidity() {
						
						let valorLogin = inputLoginUsuario.value.trim();
						let valorEmail = inputEmailUsuario.value.trim();

						if (respostaRecaptcha !== '' && valorLogin !== '' && valorEmail !== '') {
							btnSubmit.removeAttribute('disabled');
						} else {
							btnSubmit.setAttribute('disabled', 'disabled');
						}
						
					}
					
					// Função de callback para o reCAPTCHA
					function onCaptchaResolved(token) {
						
						respostaRecaptcha = token;
						checkFormValidity(); // Verificar a validade do formulário sempre que o captcha for resolvido
						
					}

					// Adicionar o callback do reCAPTCHA à janela global
					window.onCaptchaResolved = onCaptchaResolved;

					// Adicionar evento ao formulário para verificar a validade
					form.addEventListener('input', checkFormValidity);

					// Bloqueia o botão de envio apósser acionado
					form.addEventListener('submit', () => {
								
						btnSubmit.setAttribute('disabled', 'disabled');

					});
				});
				
			</script>
<?php

			include "includes/footer.php";
			
		} else {
			
			redirecionaAcessoNaoAutorizado();
			
		}
		
	} else {
			
		redirecionaAcessoNaoAutorizado();
			
	}
