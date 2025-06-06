<?php require_once __DIR__ . '/../../includes/constantes_url.php'; ?>
<!DOCTYPE html>
<html>
    <head>
          <title>E-Prepag - Alerta</title>
	      <meta charset="UTF-8">
		  <meta name="viewport" content="width=device-width, initial-scale=1">
		  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
		  <script src="https://kit.fontawesome.com/8909184996.js" crossorigin="anonymous"></script>
		  <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
		  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    </head>
    <body>
        <div class="container-fluid vh-100">
		     <div class="row h-100 bg-secondary-subtle justify-content-center align-items-center">
				<div class="col-10 col-sm-8 col-md-7 col-lg-6 col-xl-4 bg-white rounded px-3 py-5 shadow text-center">
				     <img title="E-Prepag" src="<?= EPREPAG_URL_HTTPS ?>/sys/imagens/epp_logo.png">
				     <div class="alert alert-warning mt-4 border-top-0 border-end-0 border-bottom-0 border-warning-subtle border-5" role="alert">
						<span class="fs-3">&#128680;</span> O tempo acabou, por favor gere outro link.
					 </div>
					 <a class="btn btn-primary" href="<?= EPREPAG_URL_HTTPS ?>/creditos/login.php">Voltar</a>
				</div>
			 </div>
		</div>
    </body>
</html>
