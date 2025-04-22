<?php
	
	if (isset($resposta)) {
		
		include "includes/header.php";

?>
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
		
		<div class="container txt-cinza bg-branco">
			<h4 class="top20 txt-azul-claro text-center"><strong>Informe A Sua Nova Senha</strong></h4>
			
			<div class="top20 p-bottom40">
				<form method="POST" action="atualiza-senha.php" id="formularioNovaSenha">
					<div class="row form-group">
						<div class="col-md-2"></div>
							<div class="col-md-3">
								<label for="novaSenha">Nova Senha</label>
							</div>
							<div class="col-md-4">
								<div class="campo-form">
									<input type="password" minlength="12" maxlength="12" autocomplete="off" name="novaSenha" id="novaSenha" placeholder="************" class="form-control input-sm campo-senha" required>
									<i class="bi bi-eye-fill btn-exibicao"></i>
								</div>
							</div>
							<div class="col-md-3"></div>
					</div>
					
					<div class="row form-group">
						<div class="col-md-2"></div>
						<div class="col-md-3">
							<label for="confirmacaoNovaSenha">Confirme Sua Nova Senha</label>
						</div>
						<div class="col-md-4">
							<input type="password" minlength="12" maxlength="12" autocomplete="off" name="confirmacaoNovaSenha" id="confirmacaoNovaSenha" autocomplete="off" onpaste="return false" placeholder="************" class="form-control input-sm campo-senha" required>
						</div>
						<div class="col-md-3"></div>
					</div>
					
					<div class="row">
						<div class="col-md-2"></div>
						<div class="col-md-10">
						
							<p>*Sua senha deve ter 12 caracteres, incluindo letras, números e caracteres especiais, ex: |, !, ?, *, $ e % </p>
					
							<input type='hidden' name='checked' id='checked' value='true'>
							<input type='hidden' name='codigoValidacao' id='codigoValidacao' value='<?php echo htmlspecialchars($codigoValidacao); ?>'>
							<input type="hidden" name="origemUsuario" id="origemUsuario" value="<?php echo htmlspecialchars($origemUsuario); ?>">
								
						</div>
						
					</div>
					
					
					<div class="row">
						<div class="col-md-3"></div>
						<div class="col-md-6">
							
							<div id="recaptcha">
								<div class="g-recaptcha" data-sitekey="6Lc4XtkkAAAAAJrfsAKc99enqDlxXz4uq86FI9_T" data-callback="onCaptchaResolved"></div>
							</div>
							
							<div class="top20 p-bottom40 text-center" id="feedback"></div>
							
							<button type="submit" id="btnSubmit" class="btn-block btn btn-success" disabled>Enviar</button>
						</div>
						<div class="col-md-3"></div>
					</div>
				</form>
			</div>
		</div>

		<script src="https://www.google.com/recaptcha/api.js" async="" defer=""></script>

		<script>

			$(document).ready( () => {
				
				let formularioNovaSenha = $('#formularioNovaSenha');
				let btnSubmit = $('#btnSubmit');
				let respostaRecaptcha = '';
				
				let btnExibicao = $('.btn-exibicao');
				let campoSenha = $('.campo-senha');
				
				btnExibicao.on('click', (event) => {
					
					if (campoSenha.attr('type') === 'password') {
						
						campoSenha.attr('type', 'text');
						btnExibicao.removeClass('bi-eye-fill');
						btnExibicao.addClass('bi-eye-slash-fill');
						
					} else {
						
						campoSenha.attr('type', 'password');
						btnExibicao.removeClass('bi-eye-slash-fill');
						btnExibicao.addClass('bi-eye-fill');
						
					}
					
				});
				
				// Função de callback para o reCAPTCHA
				function onCaptchaResolved(token) {
					
					respostaRecaptcha = token;
					checkFormValidity(); // Verificar a validade do formulário sempre que o captcha for resolvido
					
				}
				
				// Adicionar o callback do reCAPTCHA à janela global
				window.onCaptchaResolved = onCaptchaResolved;

				// Função para verificar a validade do formulário
				function checkFormValidity() {
					
					let novaSenha = $('#novaSenha').val().trim();
					let confirmacaoNovaSenha = $('#confirmacaoNovaSenha').val().trim();
					

					if (respostaRecaptcha !== '' && novaSenha !== '' && confirmacaoNovaSenha !== '') {
						
						// Verifica se as senhas coincidem
						if (novaSenha === confirmacaoNovaSenha) {
							
							// Faz a requisição AJAX para validar a senha
							$.ajax({
								type: 'POST',
								url: 'validador-padrao-senha.php',
								data: { novaSenha: novaSenha, confirmacaoNovaSenha: confirmacaoNovaSenha },
								success: (response) => {

									if (response == 'true') {
										$('#feedback').html('');
										btnSubmit.prop('disabled', false);
										
									} else {
										$('#feedback').html("<span style='color: red;'>A senha não segue o padrão</span>");
										btnSubmit.prop('disabled', true);
									}
									
								},
								error: () => {
									
									$('#feedback').html('<span style="color: red;">Erro ao validar a senha.</span>');
									btnSubmit.prop('disabled', true);
									
								}
							});
						} else {
							
							$('#feedback').html('<span style="color: red;">As senhas não coincidem!</span>');
							btnSubmit.prop('disabled', true);
							
						}
					} else {
						
						$('#feedback').html('');
						btnSubmit.prop('disabled', true);
						
					}
					
				}

				// Adicionar evento ao formulário para verificar a validade
				$('#formularioNovaSenha').on('input', checkFormValidity());

				// Adicionar evento ao botão de envio para verificar a validade
				$('#formularioNovaSenha').on('click', (event) => {
					
					checkFormValidity();
					
				});
				
			});
			
		</script>
<?php

		include "includes/footer.php";

	} else {
		
		redirecionaAcessoNaoAutorizado();
		
	}
?>