<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

require_once "../includes/constantes.php";
require_once DIR_CLASS . 'gamer/controller/HeaderController.class.php';
require_once "/www/class/classEmailAutomatico.php";

$controller = new HeaderController;
$controller->setHeader();
?>
<!-- Facebook Pixel Code -->
<script>
	! function(f, b, e, v, n, t, s) {
		if (f.fbq) return;
		n = f.fbq = function() {
			n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments)
		};
		if (!f._fbq) f._fbq = n;
		n.push = n;
		n.loaded = !0;
		n.version = '2.0';
		n.queue = [];
		t = b.createElement(e);
		t.async = !0;
		t.src = v;
		s = b.getElementsByTagName(e)[0];
		s.parentNode.insertBefore(t, s)
	}(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');
	fbq('init', '228069144336893'); // Insert your pixel ID here.
	fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=228069144336893&ev=PageView&noscript=1" /></noscript>
<!-- End Facebook Pixel Code -->
<div class="container txt-azul-claro bg-branco p-bottom40">
	<div class="col-md-12 conteudo_principal">
		<h1 style="margin: 20px 0 30px 0" class="txt-azul-claro">
			<strong>Falta pouco para você começar a vender!</strong>
		</h1>
		<h2 style="font-size: 20px; margin: 10px 0;">
			<strong>Assista ao vídeo</strong> - em menos de 1 minuto, veja como concluir seu
			cadastro sem complicação.
		</h2>

		<div style="display: flex; justify-content: center; align-items: center; margin: auto;">
			<iframe width="560" height="315"
				src="https://www.youtube.com/embed/pt45qYvdv08"
				title="YouTube video player"
				frameborder="0"
				allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
				allowfullscreen>
			</iframe>
		</div>

		<br><br>
		<h3 style="font-size: 17px; margin: 10px 0;">
			Tenha em mãos: o número do seu CNPJ, imagem do seu canal de vendas (física/online) e um documento de identidade com foto.
			<br><br>
			<strong>Dica:</strong> você pode concluir seu cadastro pelo celular.
		</h3>

		<div style="display: flex; justify-content: center; align-items: center; margin: auto;">
			<a href="https://cadastro.io/?token=92af1f1040a6f03c42f4082ee30d92b1">
				<!-- /home/marcos/eprepagApp/www/backoffice/images/botao_completar_cadastro.png -->
				<!-- <img src="https://www.e-prepag.com.br/sys/imagens/botao_completar_cadastro.png" width="400" style="display:block; border:0;"> -->
				<img src="/imagens/botao_completar_cadastro.png" width="200" style="display:block; border:0;">

			</a>
		</div>
		<h3 style="font-size: 17px; margin: 10px 0;">
			Assim que finalizar, nossa equipe realiza a análise e, em até 1 dia útil, seu <strong>PDV estará pronto para operar, vender produtos digitais e
				aproveitar todos os benefícios da parceria E-prepag</strong>
			<br><br>
			<!-- Em caso de dúvidas, entre em contato com o nosso suporte através do e-mail:
			<a href="mailto:suporte@e-prepag.com.br">suporte@e-prepag.com.br</a> -->
		</h3>

		<div class="txt-cinza">
			<p>Esse processo é essencial para garantir a segurança do seu ponto de venda, e a conformidade regulatória.</p>
			<p>Se tiver alguma dúvida, entre em contato com o nosso suporte, pelo e-mail <a href="mailto:suporte@eprepag.com.br">suporte@eprepag.com.br</a> ou pelo chat disponível em nosso site que responderemos em até um dia útil.
			</p>
			<p>Equipe E-prepag</p>
		</div>
		<!--
        <div class="container">
            <div class="row">
                <div class="titulo top50">
                    <div class="col-md-2 col-lg-2 col-xs-12 col-sm-12">
                        <img src="/imagens/pdv/confirmation.png" style="  width: 100px;margin-top: 5px;">
                    </div>
                    <div class="col-md-10 col-lg-10 col-xs-12 col-sm-12" style="padding-bottom: 40px">
                        <p class="text18"><strong>Formulário de cadastro completo</strong></p>
                        <span class="txt-cinza">
                            Agora nossa equipe de negócios irá verificar seus dados.
                            Este processo de análise leva até 2 dias úteis.<br>
                            Se for aprovado você receberá um e-mail com as instruções do serviço.
                            <br>
                            Caso tenha alguma dúvida, por favor contate nosso <a href="/game/suporte.php" target="_blank">suporte</a>.
                        </span>
                    </div>
                </div>
            </div>
        </div>
		-->
	</div>
</div>

<script>
	$(document).ready(function() {
		var cookies = document.cookie.split(';');
		var cookieValue;

		for (var i = 0; i < cookies.length; i++) {
			var cookie = cookies[i].trim();
			if (cookie.indexOf('meuCookie=') === 0) {
				cookieValue = cookie.substring('meuCookie='.length, cookie.length);
				break;
			}
		}

		// Converte o valor de volta para um objeto JSON
		var objetoCookie = JSON.parse(cookieValue);
		console.log(objetoCookie);
	});
</script>
</div>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-WHJ6N33"
		height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
<?php
require_once "game/includes/footer.php";
require_once "/www/db/connect.php";
require_once "/www/db/ConnectionPDO.php";
require_once "/www/class/pdv/classChaveMestra.php";

$connection = ConnectionPDO::getConnection()->getLink();

$data = json_decode($_COOKIE["meuCookie"]);

// Query com placeholder ao invés de concatenar diretamente o valor do email
$sql = "SELECT ug_id, ug_nome_fantasia, ug_email FROM dist_usuarios_games WHERE ug_email = :email";

// Preparando a query
$query = $connection->prepare($sql);

// Bind do valor de email ao placeholder
$query->bindValue(':email', $data->email, PDO::PARAM_STR);

// Executando a query
$query->execute();


$result = $query->fetch(PDO::FETCH_ASSOC);

$objClassEmail = new EnvioEmailAutomatico('L', 'Onboarding');
$objClassEmail->setUgID($result["ug_id"]);
$objClassEmail->setUgEmail($data->email);
$objClassEmail->setOnboardingNome($data->nome);
$objClassEmail->setOnboardingCodigo($result["ug_id"]);
echo $objClassEmail->MontaEmailEspecifico();

/*$chave_mestra = new ChaveMestra();
$chave_mestra->inserirChaveMestra($result["ug_id"]);

$sql = "select chave from dist_usuarios_games_chave where usuario = ". $result["ug_id"];
$query = $connection->prepare($sql);
$query->execute();

if($query->rowCount() > 0) {
	$ret = $query->fetch(PDO::FETCH_ASSOC);
	
	
	$envia_email = new EnvioEmailAutomatico('L', 'ChaveMestra');
	$envia_email->setUgNome(ucwords(strtolower($result["ug_nome_fantasia"])));
	$envia_email->setChaveMestra($ret["chave"]);
	$to = ''; //ipojucan_net@hotmail.com
	$cc = "";
	$bcc = "";
	$subject = "E-prepag - Chave Mestra";
	$msg = $envia_email->getCorpoEmail();
	
	enviaEmail3($to, $cc, $bcc, $subject, $msg, "");
}*/