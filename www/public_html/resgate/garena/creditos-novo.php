<?php require_once __DIR__ . '/../../../includes/constantes_url.php'; ?>
<?php
require_once "/www/db/connect.php";
require_once "/www/db/ConnectionPDO.php";

if (isset($_GET["partner"])) {

	/*

						 $decode = base64_decode($_GET["partner"]);
						 $ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
						 $iv = substr($decode, 0, $ivlen);
						 $hmac = substr($decode, $ivlen, $sha2len=32);
						 $ciphertext_raw = substr($decode, $ivlen+$sha2len);
						 $id = openssl_decrypt($ciphertext_raw, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
						 */

	// numero maximo para conversão são 19 caracteres
	$id = base_convert(base_convert(base64_decode(urldecode($_GET["partner"])), 16, 8), 8, 10);
	$con = ConnectionPDO::getConnection();
	$sql = "select ug_repr_legal_msn from dist_usuarios_games where ug_id = " . $id . ";";
	$find = $con->getLink()->prepare($sql);
	$find->execute();
	$result = $find->fetch(PDO::FETCH_ASSOC);
	if ($result["ug_repr_legal_msn"] != false && $result["ug_repr_legal_msn"] != "") {
		$style = json_decode($result["ug_repr_legal_msn"], true);
		$corCaixa = "background-color:" . $style["CAIXA"] . ";";
		$corBotao = "background-color:" . $style["BOTAO"] . ";border: none;";
		$corFundo = "background-color:" . $style["FUNDO"] . ";";
		$corTexto = "color:" . $style["TEXTO"] . " !important;";
		$logo = "" . EPREPAG_URL_HTTPS . "/imagens/pdv/logos/" . strtolower($style["LOGO"]) . "?" . date("YmdHis");
	}
} else {
	$_GET["partner"] = 0;
}

if ($_GET["game"] == "free_fire") {
	$logo_game = "" . EPREPAG_URL_HTTPS . "/sys/imagens/Free_Fire.png";
} else if ($_GET["game"] == "delta_force") {
	$logo_game = "" . EPREPAG_URL_HTTPS . "/sys/imagens/Delta_Force.png";
} else {
	$logo_game = "" . EPREPAG_URL_HTTPS . "/sys/imagens/garena.svg";
}

$logo = (isset($style["LOGO"]) && $style["LOGO"] != "") ? $logo : $logo_game;

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
	<title>Resgate</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="../../css/resgateGarena.css?v=2">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css">
	</link>
	<script src="https://kit.fontawesome.com/8909184996.js" crossorigin="anonymous"></script>
	<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
	<script src="https://www.google.com/recaptcha/api.js?render=6Ldb9pMgAAAAAJkJHkv2etbfzGvJu1gKTFeW3Osn"></script>
</head>

<body>
	<div style="position: fixed;
	  top: 0;
	  left: 0;
	  width: 110%; /* um pouco maior para compensar o blur */
		  height: 110%;
		  top: -5%;
		  left: -5%;
	  background: linear-gradient(rgba(150, 150, 200, 0.5), rgba(150, 150, 200, 0.5)), url('./js/resgate_background.webp');
	  background-size: cover;
	  background-position: center;
	  filter: blur(3px);
	  z-index: -1;"></div>
	<div class="container-fluid container-principal" style="background-color: transparent;">
		<div style="<?php echo isset($style["FUNDO"]) ? $corFundo : ""; ?>"
			class="row justify-content-center align-items-center">
			<div class="text-center d-flex justify-content-center align-items-center" style="position: relative;">
				<img title="Free Fire" id="logo-jogo" style="filter: contrast(1.2) brightness(1.1) drop-shadow(4px 4px 5px rgb(0, 0, 0, 1));"
					class="<?php echo (isset($style["LOGO"]) && $style["LOGO"] != "") ? "image-ale" : "image-eprepag"; ?>"
					src="<?= $logo ?>">
			</div>
			<div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-6 col-xxl-4 p-0">
				<div class="alert mx-2 text-center d-none" role="alert"></div>
				<div style="<?php echo isset($style["CAIXA"]) ? $corCaixa : ""; ?>" class="form-resgate text-center">
					<h3 style="<?php echo isset($style["TEXTO"]) ? $corTexto : ""; ?>"
						class="text-center text-freefire pt-2 mt-2 fs-3" id="text-freefire">Resgate seus créditos aqui!
					</h3>
					<hr class="rounded-1 border-hr" style="border-color: #d81a0d;">
					<div id="container-data"
						class="d-flex flex-column align-items-center justify-content-center height-container mx-2">
						<div class="input-group mb-3">
							<span class="input-group-text" id="iconecodigo"><i
									class="fa-regular fa-gem diamond fs-3"></i></span>
							<input type="number" class="form-control p-3" id="codigo"
								placeholder="Digite o código do seu pin" aria-describedby="iconecodigo">
						</div>
						<div class="input-group mb-3">
							<span class="input-group-text" id="iconeconta"><i
									class="fa-solid icone-conta fa-user conta fs-3"></i></span>
							<input type="text" class="form-control p-3" id="conta"
								placeholder="Digite a sua conta garena" aria-describedby="iconeconta">
						</div>
						<div class="g-recaptcha" data-sitekey="6Ldb9pMgAAAAAJkJHkv2etbfzGvJu1gKTFeW3Osn"
							data-callback='token' data-action='ecommerce' data-size="invisible"></div>
						<button type="button" id="confirma"
							style="<?php echo isset($style["BOTAO"]) ? $corBotao . $corTexto : ""; ?>"
							class="btn btn-outline-light py-3 px-5 mt-2">RESGATAR</button>
						<p style="<?php echo isset($style["TEXTO"]) ? $corTexto : ""; ?>" class="mt-4 mb-0 text-inform">
							*
							Após o PIN ser resgatado os diamantes cairão automaticamente na sua conta.</p>
					</div>
				</div>
			</div>
			<div
				class="align-self-end d-flex w-fill justify-content-between align-items-center footer">
				<img src="<?= EPREPAG_URL_HTTPS ?>/imagens/logo_epp_escala_cinza_high.png" alt="logo epp"
					class="logo-epp" style="filter: drop-shadow(2px 2px 4px rgb(0, 0, 0, 0.5));">
				<p style="color: #F0F0F0; text-shadow: 2px 2px 5px rgb(0, 0, 0, 0.4);" class="text-center mt-2 mx-auto">
					E-Prepag
					Copyright 2025. Todos os direitos reservados. </p>
			</div>
		</div>
		<div style="display: none;" id="part"><?php echo htmlspecialchars($_GET["partner"], ENT_QUOTES, 'UTF-8'); ?>
			<div class="d-none" id="img-padrao"><?= $logo ?></div>
		</div>

	</div>
	<script src="./js/processo.js?v=3"></script>
	<script src="./js/verifica.js"></script>
</body>

</html>