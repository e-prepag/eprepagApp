<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<?php
/*
// Strict-Transport-Security (HSTS)
header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");

// Remove Exibição da Versão
header_remove("X-Powered-By");

// X-Frame-Options
header("X-Frame-Options: SAMEORIGIN");

// X-Content-Type-Options
header("X-Content-Type-Options: nosniff");

// Referrer-Policy
header("Referrer-Policy: same-origin");

// Permissions-Policy
header("Permissions-Policy: geolocation=(self)");

*/
?>

<body>
<script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <h1>Hello, E-prepag</h1>
	
	<form method="post">
		<label for="nome">Nome</label>
        <input type="text" name="nome" id="nome">
		<button type="submit" id="manda">Manda</button>
    </form>
	
	<script>
		$('#manda').on('click', ()=> {
			var nome = $('#nome').val();
			$.ajax({
				type: 'POST',
				url: 'teste.php',
				data: {
					nome: nome
				},
				success: (nome) => {
					alert(nome);
				}
			});
		});
	</script>
</body>

</html>