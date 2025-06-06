<?php require_once __DIR__ . '/../../../includes/constantes_url.php'; ?>
<?php
//header("Access-Control-Allow-Origin: *");
require_once "/www/class/classIntegracaoGarena.php";
require_once "../../../includes/functions.php";
require_once "/www/includes/load_dotenv.php";

$ipsBloqueados = ["65.108.44.39", "201.46.218.115", "187.65.200.122"];
if (in_array($_SERVER["REMOTE_ADDR"], $ipsBloqueados)) {
	http_response_code(400);
	exit;
}

/*if($_SERVER["REMOTE_ADDR"] == "191.181.57.158"){
	   //ini_set('display_errors', 1);
	   //ini_set('display_startup_errors', 1);
	   //error_reporting(E_ALL);
   }*/

$idVenda = $_POST["vde"];

//$validJson = is_string($_POST) && is_array(json_decode($_POST, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;

$logEntry = sprintf(
	"DATA: %s\rIP REQUISICAO: %s\rORIGEM: %s\r%s\r%s\r",
	date("d-m-Y H:i:s"),
	$_SERVER["REMOTE_ADDR"],
	$_SERVER["HTTP_REFERER"],
	json_encode($_POST),
	str_repeat("*", 50)
);

file_put_contents('/www/log/parametros_GARENA.txt', $logEntry, FILE_APPEND);

$_POST["codigo"] = preg_replace("/['\"\s]/", "", $_POST["codigo"]);

// utilizado para verificar userName EPP
if (isset($_POST["user"])) {

	$semCalculo = $idVenda;
	$pinCode = [$_POST["codigo"]];
	$dist = true;
	$produto = (int) $_POST["prod"];

	$classGarena = new Garena($pinCode, $_POST["garena"], $_POST["type"], $semCalculo, $dist, $produto);
	/// verifica se a primeira chamada possui algum erro
	$auth = $classGarena->chamaGarena("GET", "producao"); // Para produção passar o segundo parametro 'producao' 
	if ($auth !== true) {
		echo $auth;
		exit;
	}

	echo $classGarena->getRoles();
	exit;
}

if (!isset($_POST["dist"])) {

	// if($_SERVER["REMOTE_ADDR"] == "191.181.57.158"){
	//	echo json_encode($_SERVER);
	//	exit;
	//}

	# BLOQUEIO HTTPS DESATIVO
	/*if($_SERVER["REQUEST_SCHEME"] == "https"){
		if(strpos($_SERVER["HTTP_ORIGIN"], "http:") !== false){
			header("Access-Control-Allow-Origin: EPREPAG_URL_HTTP"); //EPREPAG_URL_HTTPS/
		}else{
			header("Access-Control-Allow-Origin: EPREPAG_URL_HTTPS"); //EPREPAG_URL_HTTPS/
		}
		
	}else{
		header("Access-Control-Allow-Origin: EPREPAG_URL_HTTP"); //EPREPAG_URL_HTTPS/
	}*/

	if (isset($_POST["verifica"])) {

		if (strlen($_POST["codigo"]) != 20) {
			$mensagemRetono = json_encode(["Erro" => "Não é possivel fazer o resgate do pin enviado."]);
			Garena::salvaRetorno($_POST["codigo"], $_SERVER["REMOTE_ADDR"], $mensagemRetono);
			echo $mensagemRetono;
			exit;
		}

		$respostaToken = Garena::verificaTokenRe($_POST["token"]);

		//if($_SERVER["REMOTE_ADDR"] == "201.93.162.169"){
		//$respostaToken = ["retorno" => true, "code" => 0];
		//}

		$dataRequisicao = date("Y-m-d");
		if ($respostaToken["retorno"] === true) {

			$verificaPinIp = Garena::verificaQtdePin($_POST["codigo"], $_SERVER["REMOTE_ADDR"], $dataRequisicao);
			$verificaPinIpLote = Garena::verificaLotePin($_SERVER["REMOTE_ADDR"], $dataRequisicao);
			if ($verificaPinIp != true || $verificaPinIpLote != true) {
				$mensagemRetono = json_encode(["Erro" => "Seu resgate foi invalidado (EPP0044)."]);
				Garena::salvaRetorno($_POST["codigo"], $_SERVER["REMOTE_ADDR"], $mensagemRetono);
				echo $mensagemRetono;
				exit;
			}


			$dadosPin = Garena::BuscaIdPin($_POST["codigo"]);
			if ($dadosPin != false) {
				$pinCode = [$dadosPin["pin_codinterno"]];
				$semCalculo = $dadosPin["vgm_vg_id"];
				$nome_produto = $dadosPin["vgm_nome_produto"];
				$nome_modelo = $dadosPin["vgm_nome_modelo"];
				$valor_pin = $dadosPin["pin_valor"];
				$_POST["type"] = ($dadosPin["pin_status"] == "6") ? "pdv" : "usuario";
			} else {

				$mensagemRetono = json_encode(["Erro" => "Não é possivel fazer o resgate do pin enviado"]);
				Garena::salvaRetorno($_POST["codigo"], $_SERVER["REMOTE_ADDR"], $mensagemRetono);
				echo $mensagemRetono;
				exit;
			}
		} else {

			if ($respostaToken["code"] == 1) {
				$mensagemRetono = json_encode(["Erro" => "Seu resgate foi invalidado (EPP0045)."]);
				Garena::verificaQtdePin($_POST["codigo"], $_SERVER["REMOTE_ADDR"], $dataRequisicao);
				Garena::salvaRetorno($_POST["codigo"], $_SERVER["REMOTE_ADDR"], $mensagemRetono);
				echo $mensagemRetono;
				exit;
			}

			$mensagemRetono = json_encode(["Erro" => "Tempo de verificação foi excedido por favor clique no botão resgate novamente"]);
			Garena::verificaQtdePin($_POST["codigo"], $_SERVER["REMOTE_ADDR"], $dataRequisicao);
			Garena::salvaRetorno($_POST["codigo"], $_SERVER["REMOTE_ADDR"], $mensagemRetono);
			echo $mensagemRetono;
			exit;
		}

	} else {
		$pinCode = $_POST["codigo"];
		$key = getenv('ENCRYPT_KEY');
		$c = base64_decode($idVenda);
		$ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
		$iv = substr($c, 0, $ivlen);
		$hmac = substr($c, $ivlen, $sha2len = 32);
		$ciphertext_raw = substr($c, $ivlen + $sha2len);
		$semCalculo = openssl_decrypt($ciphertext_raw, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv);
		$calcmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary = true);
	}

	$dist = false;
	$produto = false;

} else {
	$semCalculo = $idVenda;
	if (isset($_POST["verifica"])) {
		$pinCode = [$_POST["codigo"]];
		$dist = true;
		$produto = (int) $_POST["produto"];
	} else {

		$pinCode = [$_POST["codigo"]];
		$dist = false;
		$produto = false;

	}

}

if (isset($_POST["valid"])) {

	// faz a verificação se o pedido já em PROCESSO
	$classGarena = new Garena($pinCode, $_POST["garena"], $_POST["type"], $semCalculo, $dist, $produto);
	/// verifica se a primeira chamada possui algum erro
	$auth = $classGarena->chamaGarena("GET", "producao"); // Para produção passar o segundo parametro 'producao' 
	if ($auth !== true) {
		echo $auth;
		Garena::salvaRetorno($_POST["codigo"], $_SERVER["REMOTE_ADDR"], $auth);
		exit;
	}

	if (isset($_POST["verifica"]) && !isset($_POST["dist"])) {
		$papel = json_decode($classGarena->getRoles(), true);
		$mensagemRetono = json_encode(["usuario" => $papel[0], "nome" => $nome_produto, "modelo" => utf8_encode($nome_modelo), "pin" => $_POST["codigo"], "valor" => $valor_pin]);
		Garena::salvaRetorno($_POST["codigo"], $_SERVER["REMOTE_ADDR"], $mensagemRetono);
		echo $mensagemRetono;
		exit;
	} else {
		$mensagemRetono = $classGarena->getRoles();
		Garena::salvaRetorno($_POST["codigo"], $_SERVER["REMOTE_ADDR"], $mensagemRetono);
		echo $mensagemRetono;
		exit;
	}

} else {

	if (gettype($_POST["codigo"]) == 'array') {
		$codPinFile = Garena::BuscaIdPin($_POST["codigo"][0], "id");
	} else {
		$codPinFile = $_POST["codigo"];
	}

	sleep(1);
	if (file_exists('/www/log/' . $codPinFile)) {
		sleep(10);
		$mensagemRetono = json_encode(["Erro" => "Não foi possivel realizar seu resgate, entre em contato com o suporte E-Prepag (EPP0345)."]);
		Garena::salvaRetorno($_POST["codigo"], $_SERVER["REMOTE_ADDR"], $mensagemRetono);
		echo $mensagemRetono;
		exit;
	} else {
		$file = fopen('/www/log/' . $codPinFile, 'a+');
	}
	$classGarena = new Garena($pinCode, $_POST["garena"], $_POST["type"], $semCalculo, $dist, $produto);
	/// CONTA GARENA DE TESTE: 10000335
	/// verifica se a primeira chamada possui algum erro
	$authConfirmado = true;

	if (isset($_POST["roles"])) {
		$classGarena->setRoles($_POST["roles"]);
	} else {
		$authConfirmado = $classGarena->chamaGarena("GET", "producao"); // Para produção passar o segundo parametro 'producao' 
		if ($authConfirmado !== true) {
			$mensagemRetono = $authConfirmado;
			Garena::salvaRetorno($_POST["codigo"], $_SERVER["REMOTE_ADDR"], $mensagemRetono);
			echo $mensagemRetono;
			exit;
		}
	}

	/// verifica se a segunda chamada possui algum erro
	$resgate = $classGarena->chamaGarena("POST", "producao"); // Para produção passar o segundo parametro 'producao' 
	if ($resgate !== true) {
		unlink('/www/log/' . $codPinFile);
		$mensagemRetono = $resgate;
		Garena::salvaRetorno($_POST["codigo"], $_SERVER["REMOTE_ADDR"], $mensagemRetono);
		echo $mensagemRetono;
		exit;
	}

	if ($authConfirmado === true && $resgate === true) {

		unlink('/www/log/' . $codPinFile);
		if (isset($_POST["verifica"]) && !isset($_POST["dist"])) {
			$data = substr($classGarena->getDataUtilizacao(), 8, 2) . "/" . substr($classGarena->getDataUtilizacao(), 5, 2) . "/" . substr($classGarena->getDataUtilizacao(), 0, 4) . "-" . substr($classGarena->getDataUtilizacao(), 11, 8);
			$mensagemRetono = json_encode(["Sucesso" => "Resgate realizado com sucesso, creditos encaminhados para conta", "txn_id" => $classGarena->getTxn_id(), "nome" => $nome_produto, "modelo" => utf8_encode($nome_modelo), "pin" => $_POST["codigo"], "valor" => $valor_pin, "data" => $data]);
			Garena::salvaRetorno($_POST["codigo"], $_SERVER["REMOTE_ADDR"], $mensagemRetono);
			echo $mensagemRetono;
		} else {
			$data = substr($classGarena->getDataUtilizacao(), 8, 2) . "/" . substr($classGarena->getDataUtilizacao(), 5, 2) . "/" . substr($classGarena->getDataUtilizacao(), 0, 4) . "-" . substr($classGarena->getDataUtilizacao(), 11, 8);
			$mensagemRetono = json_encode(["Sucesso" => "Resgate realizado com sucesso, creditos encaminhados para conta", "dataUtilizacao" => $data]);
			Garena::salvaRetorno($_POST["codigo"], $_SERVER["REMOTE_ADDR"], $mensagemRetono);
			echo $mensagemRetono;
		}
	}
	//echo $classGarena;
	exit;
}

?>