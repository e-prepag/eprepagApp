<?php 
    require_once "/www/class/classIntegracaoGarena.php";
	$dataRequisicao = date("Y-m-d");
	$verificaPinIpLote = Garena::verificaLotePin($_SERVER["REMOTE_ADDR"], $dataRequisicao);
	
	if($_SERVER["REQUEST_SCHEME"] == "https"){
		if(strpos($_SERVER["HTTP_ORIGIN"], "http:") !== false){
			header("location: https://www.e-prepag.com.br/resgate/garena/creditos.php"); //https://www.e-prepag.com.br/
			exit;
		}
	}else{
		header("location: https://www.e-prepag.com.br/resgate/garena/creditos.php"); //https://www.e-prepag.com.br/
		exit;
	} 
?>
<!DOCTYPE html>
<html lang="pt-br">
	<head>
	      <title>Resgate</title>
	      <meta charset="UTF-8">
		  <meta name="viewport" content="width=device-width, initial-scale=1">
		  <meta name="description" content="Resgate seus diamantes em sua conta de maneira fácil e rápida">
          <meta name="keywords" content="Free Fire, Resgate de créditos free fire, E-Prepag free fire, Diamantes, Dimas, Regaste Free Fire">
		  <link rel="stylesheet" href="../../css/resgateGarena.css">
		  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css"></link>
		  <script src="https://kit.fontawesome.com/8909184996.js" crossorigin="anonymous"></script>
		  <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
		  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
		  <script src="https://www.google.com/recaptcha/api.js?render=6Ldb9pMgAAAAAJkJHkv2etbfzGvJu1gKTFeW3Osn"></script>
	</head>
	<body>
	      <div class="container-fluid">
		       <div class="row justify-content-center align-items-center">
			        <div class="text-center">
					     <img title="E-Prepag" class="image-eprepag" src="https://www.e-prepag.com.br/sys/imagens/epp_logo.png">
					</div>
			        <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-6 col-xxl-4 p-0">
							<div class="alert mx-2 text-center d-none" role="alert"></div>
							<div class="form-resgate text-center">
								<h3 class="text-center text-white pt-2 fs-3">Resgate seus créditos aqui!</h3>
								<hr class="rounded-1 border-hr">
								<?php if($verificaPinIpLote === true){ ?>
									<div id="container-data" class="d-flex flex-column align-items-center justify-content-center height-container">
										<div class="input-group mb-3">
											<span class="input-group-text" id="iconecodigo"><i class="fa-regular fa-gem diamond fs-3"></i></span>
											<input type="number" class="form-control p-3" id="codigo" placeholder="Digite o código do seu pin" aria-describedby="iconecodigo">
										</div>
										<div class="input-group mb-3">
											<span class="input-group-text" id="iconeconta"><i class="fa-solid icone-conta fa-user conta fs-3"></i></span>
											<input type="number" class="form-control p-3" id="conta" placeholder="Digite a sua conta garena" aria-describedby="iconeconta">
										</div>
										<div class="g-recaptcha" data-sitekey="6Ldb9pMgAAAAAJkJHkv2etbfzGvJu1gKTFeW3Osn" data-callback='token' data-action='submit' data-size="invisible"></div>
										<button type="button" id="confirma" class="btn btn-success py-3 px-5 mt-2">Resgatar</button>
										<p class="mt-4 mb-0 text-white text-inform">*  Após o <span style="color:#a3e6ff;">PIN</span> ser resgatado os diamantes cairão automaticamente na sua conta.</p>
									</div>
							    <?php }else{ ?>
                                    <div class="p-3">
									    <div class="g-recaptcha" data-sitekey="6Ldb9pMgAAAAAJkJHkv2etbfzGvJu1gKTFeW3Osn" data-callback='token' data-action='submit' data-size="invisible"></div>
										<p class="text-white">Resgate indisponivel, entre em contanto com o suporte E-Prepag para obter mais informações.</p>
									</div>
								<?php } ?>
						    </div>
				    </div>
					<p class="align-self-end text-center border-top border-2 pt-2">E-Prepag Copyright 2023. Todos os direitos reservados.</p>
			   </div>
		  </div>
		  <script src="./js/processo.js"></script>
	</body>
</html> 