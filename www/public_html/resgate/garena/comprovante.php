<?php require_once __DIR__ . '/../../../includes/constantes_url.php'; ?>
<?php
if (isset($_GET["partner"])) {
	$href = "" . EPREPAG_URL_HTTPS . "/resgate/garena/creditos-novo.php?partner=" . $_GET["partner"];
} else {
	$href = "" . EPREPAG_URL_HTTPS . "/resgate/garena/creditos.php";
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
	<title>Resgate</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css">
	</link>
	<script src="https://kit.fontawesome.com/8909184996.js" crossorigin="anonymous"></script>
	<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
	<style>
		.box {
			box-shadow: 0 3px 28px black;
			border-radius: 5px;
		}

		.total-height {
			min-height: 100vh;
		}

		.image-eprepag {
			margin-top: 30px;
			margin-bottom: 30px;
		}

		.bg-color {
			background-color: white;
			padding: 15px;
			border-radius: 50%;
			font-size: 3.5em;
		}

		.border-color {
			border-color: #d81a0db0 !important;
		}

		.clearfix {
			clear: both;
		}

		.btn {
			font-weight: bold;
			border-color: transparent;
			background-color: #d81a0d;
			color: white;
			box-shadow: 2px 2px 5px rgba(255, 185, 0, 0.4);
		}

		.btn:hover {
			/* background-color: transparent; */
			color: lightgray;
			border-color: transparent;
			background-color: #ad150a;
		}

		.logo-epp {
			width: auto;
			height: 30px;
		}
	</style>
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
	<div class="container-fluid" style="background-color: transparent;">
		<div class="row justify-content-center total-height">
			<div class="text-center">
				<img title="E-Prepag" class="image-eprepag logo-epp" src="<?= EPREPAG_URL_HTTPS ?>/imagens/logo_epp_escala_cinza_high.png" style="filter: drop-shadow(2px 2px 4px rgb(0, 0, 0, 0.5));">
			</div>
			<div style="background-color: rgba(0, 0, 0, 0.95);"
				class="col-10 col-sm-9 col-md-9 col-lg-8 col-xl-6 col-xxl-4 text-center p-3 box align-self-start">
				<!--<a href="" class="btn btn-success float-end">Gerar PDF</a>-->
				<div class="d-block clearfix">
					<i class="fa-solid fa-thumbs-up text-success bg-color"></i>
					<h3 class="text-center text-white">Transação concluida</h3>
				</div>
				<hr>
				<div class="m-4">
					<div class="border border-color p-2">
						<div class="d-flex justify-content-between text-white">
							Produto selecionado<span id="produto"></span>
						</div>
						<div class="d-flex justify-content-between py-1 text-white">
							Preço<span id="preco"></span>
						</div>
					</div>
					<div class="my-2 border border-color p-2">
						<div class="d-flex justify-content-between py-1 text-white">
							Jogo<span id="jogo"></span>
						</div>
						<div class="d-flex justify-content-between py-1 text-white">
							Meio de pagamento<span> E-Prepag </span>
						</div>
						<div class="d-flex justify-content-between py-1 text-white">
							Data<span id="data"></span>
						</div>
						<div class="d-flex justify-content-between py-1 text-white">
							Pin resgatado<span id="code"></span>
						</div>
						<div class="d-flex justify-content-between py-1 text-white">
							Txn id<span id="txn_id"></span>
						</div>
					</div>
				</div>
				<hr>
				<div>
					<a href="<?php echo htmlspecialchars($href, ENT_QUOTES, 'UTF-8'); ?>"
						class="btn btn-outline-light py-3 px-5">NOVO RESGATE</a>
				</div>
			</div>
		</div>
	</div>
	<script>
		if (localStorage.getItem('info') != "" && localStorage.getItem('info') != null) {
			let info = JSON.parse(localStorage.getItem('info'));
			$("#produto").html(info.nome + " - " + info.modelo);
			$("#preco").html(info.valor.replace('.', ','));
			$("#jogo").html(info.nome);
			$("#data").html(info.data);
			$("#code").html(info.pin);
			$("#txn_id").html(info.txn_id);
		}
	</script>
</body>

</html>