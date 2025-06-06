<?php require_once __DIR__ . '/../../includes/constantes_url.php'; ?>
<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

require_once "/www/includes/constantes.php";
require_once DIR_CLASS . "pdv/controller/CarrinhoController.class.php";
require_once DIR_INCS . "gamer/constantes.php";
require_once "/www/class/pdv/classChaveMestra.php";
require_once "/www/class/pdv/classProvedor.php";
require_once "/www/includes/load_dotenv.php";

//Recupera carrinho do session
//$carrinho = $_SESSION['dist_carrinho'];
$controller = new CarrinhoController;
//$pdvInfo = unserialize($_SESSION["dist_usuarioGames_ser"]);
if(!isset($_SESSION['dist_usuarioGamesOperador_ser'])){
	$pdvInfo = unserialize($_SESSION["dist_usuarioGames_ser"]);
	$usuario = $pdvInfo->ug_id;
}else{
	$pdvInfo = unserialize($_SESSION['dist_usuarioGamesOperador_ser']);
	$usuario = $pdvInfo->ugo_ug_id; //    ugo_id
}

if(isset($_POST) && $_POST["passMestra"] != ""){
	
	if(!empty($_POST["g-recaptcha-response"])){
				
	   $tokenInfo = ["secret" => getenv("RECAPTCHA_SECRET_KEY"), "response" => $_POST["g-recaptcha-response"], "remoteip" => $_SERVER["REMOTE_ADDR"]];             

		$recaptcha = curl_init();
		curl_setopt_array($recaptcha, [
			CURLOPT_URL => getenv("RECAPTCHA_URL"),
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POSTFIELDS => http_build_query($tokenInfo)

		]);
		$retorno = json_decode(curl_exec($recaptcha), true);
		curl_close($recaptcha);

		if($retorno["success"] != true || (isset($retorno["error-codes"]) && !empty($retorno["error-codes"]))){
			$erro = true;
			$mensagemErro = "Recaptcha invalidado";
		}else{
			
			$classChave = new ChaveMestra();
			$provedor = new Provedor();
			$retornoVereficacao = $classChave->verificaSenha($usuario, $_POST["passMestra"]);
			if($retornoVereficacao > 0){
				$classChave->inserirSeguro("S", $usuario);
			    $provedor->coletaProvedor($usuario, 'chave_mestra', 'principal');
				$_SESSION["seg_ip"] = true; 
				header("location: /creditos/pagamento/");
				exit;
			}else{
				$erro = true;
		        $mensagemErro = "Chave não reconhecida";
			}
		}
	}else{
		$erro = true;
		$mensagemErro = "Recaptcha não encontrado";
	}
		
}else{
	
	if(isset($_POST["passMestra"]) && $_POST["passMestra"] == ""){
		$erro = true;
	    $mensagemErro = "Senha está vazia";
	}

}

if(isset($_POST['acao']) && $_POST['acao'] != "" && isset($_POST["mod"]) && $_POST["mod"] != ""){
    $controller->actions($_POST);
}

if(empty($_SESSION['dist_carrinho'])){
    header("Location: /creditos");
    die();
}

require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/header.php";

?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
   
   <?php if(isset($erro) && $erro == true){ ?>
		Swal.fire(
		  '<?php echo $mensagemErro; ?>',
		  'processo interrompido',
		  'error'
		);
		grecaptcha.reset();
   <?php } ?>
   
  function verificaRecaptcha(){
	  if(grecaptcha.getResponse() == "" || grecaptcha.getResponse().length == 0){
			Swal.fire(
			  'Você deve selecionar o Não sou um robô',
			  'processo interrompido',
			  'error'
			);
			return false;
	  }
	  
	  if($("#passMestra").val() == ""){
		  Swal.fire(
			  'Você deve digitar sua senha principal',
			  'processo interrompido',
			  'error'
		  );
		  return false;
	  }

  }
</script>
<div class="container txt-azul-claro bg-branco" style="">
    <div>
	    <h1 class="titulo-verificacao">Verificação de identidade</h1>
		<h2 class="subtitulo-verificacao">Digite sua Chave Mestra para confirmar sua identidade</h2>
		<p style="text-align: center">A Chave Mestra foi enviada para seu e-mail de cadastro, não esqueça de verificar o spam e lixo eletrônico.</p>
		<p style="text-align: center">Dúvidas? Fale com o <a style="text-decoration: none; color: #337ab7;" href="<?= EPREPAG_URL_HTTPS ?>/game/suporte.php" title="Clique para falar com o suporte">suporte</a>.<p>
		<form method="POST" class="container-form-verificacao" onSubmit="return verificaRecaptcha()">
		     <div class="container-input-verificacao">
				 <input type="text" id="passMestra" value="<?php echo (isset($_POST["passMestra"]))? $_POST["passMestra"]: "";?>" name="passMestra" class="form-control">
				 <button type="submit" class="btn btn-success top10">Verificar</button>
			 </div>
		     <div class="g-recaptcha" data-sitekey="6Lc4XtkkAAAAAJrfsAKc99enqDlxXz4uq86FI9_T"></div>
		</form>
	</div>
</div>
<script src="https://www.google.com/recaptcha/api.js" async="" defer=""></script>
<?php
require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/footer.php";
?>