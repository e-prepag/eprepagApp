<?php
    require_once "/www/db/connect.php";
    require_once "/www/db/ConnectionPDO.php";
	
	if(isset($_GET["partner"])){
		
		$id = base_convert(base_convert(base64_decode(urldecode($_GET["partner"])), 16, 8), 8, 10);		
		$con = ConnectionPDO::getConnection();
		$sql = "SELECT ug_repr_legal_msn FROM dist_usuarios_games WHERE ug_id = :id";
		$find = $con->getLink()->prepare($sql);

		// Usar bindValue para garantir que $id seja tratado de forma segura
		$find->bindValue(':id', $id, PDO::PARAM_INT); // Presumindo que $id seja um n�mero inteiro

		$find->execute();
		$result = $find->fetch(PDO::FETCH_ASSOC);

		if($result["ug_repr_legal_msn"] != false && $result["ug_repr_legal_msn"] != ""){
			$style = json_decode($result["ug_repr_legal_msn"], true);
			$corCaixa = "background-color:".$style["CAIXA"].";";
			$corBotao = "background-color:".$style["BOTAO"].";border: none;";
			$corFundo = "background-color:".$style["FUNDO"].";";
			$corTexto = "color:".$style["TEXTO"]." !important;";
			$logo = "https://www.e-prepag.com.br/imagens/pdv/logos/".strtolower($style["LOGO"])."?".date("YmdHis");
		}
	}else{
		$_GET["partner"] = 0; 
	}
	
?>
<!DOCTYPE html>
<html lang="pt-br">
	<head>
	      <title>Resgate</title>
	      <meta charset="UTF-8">
		  <meta name="viewport" content="width=device-width, initial-scale=1">
		  <link rel="stylesheet" href="../../css/resgateGarena.css">
		  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css"></link>
		  <script src="https://kit.fontawesome.com/8909184996.js" crossorigin="anonymous"></script>
		  <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
		  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
		  <script src="https://www.google.com/recaptcha/api.js?render=6Ldb9pMgAAAAAJkJHkv2etbfzGvJu1gKTFeW3Osn"></script>
	</head>
	<body>
	      <div class="container-fluid">
		       <div style="<?php echo isset($style["FUNDO"])?$corFundo:"";?>" class="row justify-content-center align-items-center">
			        <div class="text-center">
					     <img title="E-Prepag" class="<?php echo (isset($style["LOGO"]) && $style["LOGO"] != "")?"image-ale":"image-eprepag";?>" src="<?php echo (isset($style["LOGO"]) && $style["LOGO"] != "")?$logo:"https://www.e-prepag.com.br/sys/imagens/epp_logo.png";?>">
					</div>
			        <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-6 col-xxl-4 p-0">
						  <div class="alert mx-2 text-center d-none" role="alert"></div>
				          <div style="<?php echo isset($style["CAIXA"])?$corCaixa:"";?>" class="form-resgate text-center">
						      <h3 style="<?php echo isset($style["TEXTO"])?$corTexto:"";?>" class="text-center text-white pt-2 fs-3">Resgate seus créditos aqui!</h3>
							  <hr class="rounded-1 border-hr">
							  <div id="container-data" class="d-flex flex-column align-items-center justify-content-center height-container">
								  <div class="input-group mb-3">
									  <span class="input-group-text" id="iconecodigo"><i class="fa-regular fa-gem diamond fs-3"></i></span>
									  <input type="number" class="form-control p-3" id="codigo" placeholder="Digite o código do seu pin" aria-describedby="iconecodigo">
								  </div>
								  <div class="input-group mb-3">
									  <span class="input-group-text" id="iconeconta"><i class="fa-solid icone-conta fa-user conta fs-3"></i></span>
									  <input type="text" class="form-control p-3" id="conta" placeholder="Digite a sua conta garena" aria-describedby="iconeconta">
								  </div>
								  <div class="g-recaptcha" data-sitekey="6Ldb9pMgAAAAAJkJHkv2etbfzGvJu1gKTFeW3Osn" data-callback='token' data-action='ecommerce' data-size="invisible"></div>
							      <button type="button" id="confirma" style="<?php echo isset($style["BOTAO"])?$corBotao.$corTexto:"";?>" class="btn btn-success py-3 px-5 mt-2">Resgatar</button>
								  <p style="<?php echo isset($style["TEXTO"])?$corTexto:"";?>" class="mt-4 mb-0 text-white text-inform">*  Após o <span style="color:#a3e6ff;">PIN</span> ser resgatado os diamantes cairão automaticamente na sua conta.</p>
							  </div>
						  </div>
				    </div>
					<p style="<?php echo isset($style["TEXTO"])?$corTexto:"";?>" class="align-self-end text-center border-top border-2 pt-2">E-Prepag Copyright 2023. Todos os direitos reservados. </p>
			   </div>
				<div style="display: none;" id="part"><?php echo htmlspecialchars($_GET["partner"], ENT_QUOTES, 'UTF-8'); ?></div>
			  
		  </div>
		  <script src="./js/processo.js"></script>
	</body>
</html> 