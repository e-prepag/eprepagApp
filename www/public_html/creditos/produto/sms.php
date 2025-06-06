<?php require_once __DIR__ . '/../../../includes/constantes_url.php'; ?>
<?php

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

require_once "../../../includes/constantes.php";
require_once DIR_CLASS . "pdv/controller/CarrinhoController.class.php";
require_once DIR_INCS . "gamer/constantes.php";

$controller = new CarrinhoController;
require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/header.php";
require_once "/www/class/pdv/classSMS.php";

$sms = new SMS();
$pdvInfo = unserialize($_SESSION["dist_usuarioGames_ser"]);
$dd = $pdvInfo->ug_sCelDDD;
$tel = $pdvInfo->ug_sCel;
$num = $dd.$tel;

if(!isset($_SESSION['prodData'])){
	$_SESSION['prodData'] = [
		"acao" => $_POST['acao'],
		"mod" => $_POST['mod'],
		"valor" => $_POST['valor'],
		"codeProd" => $_POST['codeProd']
	];
}


if(!isset($_POST["but"])){
	$retCode = $sms->sendSMS($num);
	
	if($_SERVER["REMOTE_ADDR"] == '201.93.162.170') {
		var_dump("dev");
		echo "<script>console.log(".json_encode($_SESSION['SMS_CODE']).")</script>";
	}
}

if(isset($_POST) && !empty($_POST["code"])){		
	$receive = $_POST["code"];
	
	if ($receive === $_SESSION["SMS_CODE"]) {
		$url = '' . EPREPAG_URL_HTTPS . '/creditos/produto/produtos_selecionados.php?'. http_build_query($_SESSION['prodData']);
        unset($_SESSION['prodData']);
		header('location: '. $url);
		exit;
		
	} else {
		include '/www/public_html/includes/modal-error.php';
	}
} elseif(isset($_POST["code"]) && empty($_POST["code"])) {
	include '/www/public_html/includes/modal-error.php';
}

?>

<div class="container txt-azul-claro bg-branco">
    <div class="row">
        <div style="padding-top: 20px;" class="col-md-10 col-lg-10 col-xs-12 col-sm-12">
		     <h1 style="font-size: 25px;font-weight: bold; margin-left: 15px;">Verificação de SMS</h1>
		     <form action="" method="post" style="height: 290px;padding:20px;">
				 <div>
					<label style="display:block;">Código de verificação</label>
					<input type="text" id="code" style="padding:5px;width: 230px;" name="code" placeholder="Digite o seu código">
					<input type="submit" class="btn btn-success" name="but" value="Enviar">
				 </div>
                 <div style="margin-top: 20px;">
				    <p style="color: green;">
					    <span style="font-size: 20px;">&#128274;</span> Foi enviado para o seu número de telefone um código de verificação que será necessário<br>
						fazer a utilização no campo acima.
					</p>
				 </div>
			 </form>
		</div>
	</div>
</div>

<?php
require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/footer.php";
?>