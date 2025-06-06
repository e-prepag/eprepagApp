<?php require_once __DIR__ . '/../../includes/constantes_url.php'; ?>
<?php
     	 
	 //ini_set('display_errors', 1);
	 //ini_set('display_startup_errors', 1);
	 //error_reporting(E_ALL);
	
	 session_start();
	 require_once '/www/class/pdv/classLink.php';
	 	 
     $errorCampo = '';
     $linkLimite = new LinkAcesso(null);
	 if(!isset($_GET['code']) || strtotime(date("Y-m-d H:i:s")) > $linkLimite->recuparaLink($_GET['code'])){
		 require '/www/public_html/link/message.php';
		 exit;
	 }
	 
	 $verify = !isset($_SESSION['access_token']) || strtotime(date("H:i")) > $_SESSION['access_token']['time'];
	 if($verify){
		 unset($_SESSION['access_token']);
	 }
	 
     if(!empty($_POST['login'])){
		 $link = new LinkAcesso($_POST['login']);
		 if($verify){
			 $token = $link->registra($_GET['code']);
			 if($token != false){
				 $_SESSION['access_token'] = ['time' => strtotime(date("H:i")) + 300, 'token' => $token];
			 }else{
				 $errorCampo = "Não conseguimos gerar o token de acesso";
			 }
		 }
	 }elseif(isset($_POST['login'])){
		 $errorCampo = "* O campo login está vazio";
	 }

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
					 <div class="alert alert-dark d-none" role="alert">
						  Token Copiado.
					 </div>
				     <img title="E-Prepag" src="<?= EPREPAG_URL_HTTPS ?>/sys/imagens/epp_logo.png">
				     <form class="pt-4" method="post" action="">
					    <h3 class="mb-4">Criar Token de acesso</h3>
						<?php if(isset($_SESSION['access_token'])){ ?>
							<div class="m-2 fw-bold fs-6">
							    Token gerado:
								<div class="border border-success text-success rounded p-2 mt-2">
								    <?php echo $_SESSION['access_token']['token']; ?>
									<input id="token" value="<?php echo $_SESSION['access_token']['token']; ?>" type="hidden">
								</div>
								<button id="execCopy" type="button" class="btn btn-primary mt-2">Copiar</button>
							<div>
						<?php }else{ ?>
                            <div class="mb-3">
								  <div class="input-group flex-nowrap">
									  <span class="input-group-text" id="addon-wrapping">&#128272;</span> 
									  <input type="text" class="form-control" id="login" name="login" placeholder="Digite seu login" aria-label="login" aria-describedby="addon-wrapping">
								  </div>
								  <span class="text-danger d-block mt-2"><?php echo isset($errorCampo)? $errorCampo: ""; ?></span>
							</div>					
							<button type="submit" class="btn btn-outline-primary mt-2">Verificar &#128270;</button>							
						<?php } ?>
					 </form>
				</div>
			 </div>
		</div>
    </body>
	<script>
	    window.onload = function(e){			
			document.getElementById('execCopy').addEventListener('click', clipboardCopy);
			async function clipboardCopy() {
			  let text = document.querySelector("#token").value;
			  document.querySelector(".alert").classList.remove("d-none");
			  setTimeout(function() {
				  document.querySelector(".alert").classList.add("d-none");
			  }, 3000);
			  await navigator.clipboard.writeText(text);
			}
		}
	</script>
</html>

