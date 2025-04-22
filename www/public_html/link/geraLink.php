<?php 

	//ini_set('display_errors', 1);
	//ini_set('display_startup_errors', 1);
	//error_reporting(E_ALL);

	 //require_once '/www/class/pdv/classLink.php';
	 //$link = LinkAcesso::geraLink();

?>
<!DOCTYPE html>
<html>
    <head>
          <title>E-Prepag - Verificação</title>
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
				     <img title="E-Prepag" src="https://www.e-prepag.com.br/sys/imagens/epp_logo.png">
				     <div class="mt-2">
					     <?php// echo $link; ?>
					 </div>
				</div>
			 </div>
		</div>
    </body>
</html>

