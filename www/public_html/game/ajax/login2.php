<?php

//error_reporting(E_ALL);
//ini_set("display_errors", 1);

require_once "../../../includes/constantes.php";
require_once DIR_CLASS . "util/Util.class.php";
require_once DIR_CLASS . "util/Validate.class.php";
require_once "/www/class/class2FA.php";
require_once "../../libs/PHPGangsta/GoogleAuthenticator.php";
/*
 * Programa em AJAX para efetuar o login de gamer
 * 
 * @paramns $_POST['senha'], $_POST['login']
 * 
 * @return RETURN_SUCCESS = sucesso
 * @return RETURN_EMPTY = usuario ou senha em branco
 * @return RETURN_WRONG = usuario ou senha invalidos
 * @return RETURN_CAPTCHA = captcha incorreto
 */
session_start();

if ($_SESSION['captcha_passed'] == 1) {

} else {
	session_destroy();
	echo "Você deve fazer a verificação do RECAPTCHA para fazer o login.";
	registrarTentativaFalha($_POST['login']);
	exit;
}

if (Util::isAjaxRequest()) {
	// Exibe todos os dados enviados via POST

	require_once DIR_CLASS . "util/Log.class.php";
	require_once DIR_INCS . "main.php";
	require_once DIR_INCS . "gamer/main.php";
	require_once "funcoes_login.php";
	$validate = new Validate;

	if (isset($_POST['login']) && !empty($_POST['login']) && isset($_POST['senha']) && !empty($_POST['senha'])) {

		function saveDevice($userId)
		{
			$userAgent = $_SERVER['HTTP_USER_AGENT'] ? $_SERVER['HTTP_USER_AGENT'] : 'unknown';
			$randomToken = bin2hex(openssl_random_pseudo_bytes(32));
			$deviceId = hash('sha256', $userAgent . $randomToken);

			$pdo = ConnectionPDO::getConnection()->getLink();
			$expiry = date('Y-m-d H:i:s', strtotime('+30 days')); // Expira em 30 dias
			$stmt = $pdo->prepare("INSERT INTO usuarios_games_dispositivos (user_id, device_token, expires_at) VALUES (?, ?, ?)");
			$stmt->execute([$userId, $deviceId, $expiry]);

			setcookie(
				'device_token',   // Nome do cookie
				$deviceId,        // Valor do cookie
				time() + (31 * 24 * 60 * 60), // Expiracao (timestamp)
				'/',              // Caminho
				'',               // Domonio (vazio = padrao)
				isset($_SERVER['HTTPS']), // Secure: apenas HTTPS
				true              // HttpOnly: bloqueia acesso via JS
			);
		}
		function checkDevice($userId, $pdo)
		{
			if (!isset($_COOKIE['device_token'])) {
				return false; // Sem cookie, exige login
			}

			$deviceId = $_COOKIE['device_token'];
			$stmt = $pdo->prepare("SELECT * FROM usuarios_games_dispositivos WHERE user_id = ? AND device_token = ? AND expires_at > NOW()");
			$stmt->execute([$userId, $deviceId]);

			if ($stmt->fetch()) {
				return true; // Dispositivo valido
			} else {
				return false; // Dispositivo invalido ou expirado
			}
		}
		function verifica_autenticador()
		{
			$validate = new Validate;

			$connection = ConnectionPDO::getConnection()->getLink();

			$objEncryption = new Encryption();
			$senha = $objEncryption->encrypt(trim($_POST['senha']));
			$login = strtoupper(trim($_POST['login']));

			if ($validate->email($_POST['login']) == 0) {
				$sql = "SELECT ug_acesso_sem_aut, ug_chave_autenticador, ug_id FROM usuarios_games WHERE ug_email = ? AND ug_ativo = 1 AND ug_senha = ?";
			} else if ($validate->qtdCaracteres($_POST['login'], 2, 255) == 0) {
				$sql = "SELECT ug_acesso_sem_aut, ug_chave_autenticador, ug_id FROM usuarios_games WHERE ug_login = ? AND ug_ativo = 1 AND ug_senha = ?";
			} else {
				echo RETURN_WRONG;
				exit;
			}

			$stmt = $connection->prepare($sql);
			$stmt->execute([$login, $senha]);
			$auth = $stmt->fetch(PDO::FETCH_ASSOC);

			if (!$auth) {
				$msgAuth = "Login ou senha inválidos.\n";

				$linha = "2g[" . date('Y-m-d H:i:s') . "] [$login] $msgAuth" . PHP_EOL;
				file_put_contents('/www/log/log_login.txt', $linha, FILE_APPEND);

				echo RETURN_WRONG;
				exit;
			} elseif (empty($auth['ug_chave_autenticador'])) {
				$dataUltimoAcesso = new DateTime($auth['ug_acesso_sem_aut']);
				$dataHoje = new DateTime();

				// Defina o prazo maximo permitido (por exemplo, 7 dias)
				$prazoMaximo = 28;

				// Calcula a diferenca de dias
				$diasPassados = $dataUltimoAcesso->diff($dataHoje)->days;
				$diasRestantes = $prazoMaximo - $diasPassados;

				if ($diasRestantes <= 0) {
					$msgAuth = "Você precisa adicionar um autenticador para poder realizar seu login.\n";

					$linha = "2g[" . date('Y-m-d H:i:s') . "] [$login] $msgAuth" . PHP_EOL;
					file_put_contents('/www/log/log_login.txt', $linha, FILE_APPEND);

					echo $msgAuth;
					exit;
				}
			} else {
				if (!checkDevice($auth['ug_id'], $connection, false)) {
					$ga = new PHPGangsta_GoogleAuthenticator();
					if (!$ga->verifyCode($auth['ug_chave_autenticador'], $_POST['token'], 2)) {
						$msgAuth = "Token inválido.\n";

						$linha = "2g[" . date('Y-m-d H:i:s') . "] [$login] $msgAuth" . PHP_EOL;
						file_put_contents('/www/log/log_login.txt', $linha, FILE_APPEND);

						echo $msgAuth;
						exit;
					}
					if ($_POST['salvarDispositivo'] == "sim") {
						saveDevice($auth['ug_id']);
					}
				}
			}
		}

		$tempoBloqueio = verificarBloqueio();
		if ($tempoBloqueio) {
			session_destroy();
			bloquearAcesso($tempoBloqueio);
		}

		if ($validate->email($_POST['login']) == 0) {

			if (!empty($_SESSION['carrinho']))
				$carrinho['carrinho'] = $_SESSION['carrinho'];

			$_SESSION = array();
			session_destroy();
			session_start();
			session_regenerate_id();

			function verificaPOST($referer, $POST)
			{

				//if (strpos($referer,"bronzato.com.br")===false && strpos($referer,"contause.digital")===false){return false;}
				$flag = true;
				foreach ($_POST as $xa => $xb) {
					$xb = serialize($xb);
					if (strpos($xb, "dbms_pipe.receive_message") !== false || strpos($xb, "DBMS_PIPE.RECEIVE_MESSAGE") !== false || strpos($xb, "delete") !== false || strpos($xb, "delete") !== false || strpos($xb, "update") !== false || strpos($xb, "select") !== false) {
						return false;
					}

					if (strpos($xb, "dbms_pipe.receive_message") !== false || strpos($xb, "DBMS_PIPE.RECEIVE_MESSAGE") !== false || strpos(hexToStr($xb), "delete") !== false || strpos(hexToStr($xb), "update") !== false || strpos(hexToStr($xb), "select") !== false) {
						return false;
					}
				}

				if ($flag) {
					return true;
				} else {
					return false;
				}
			}

			function strToHex($string)
			{
				$hex = '';
				for ($i = 0; $i < strlen($string); $i++) {
					$ord = ord($string[$i]);
					$hexCode = dechex($ord);
					$hex .= substr('0' . $hexCode, -2);
				}
				return strToUpper($hex);
			}

			function hexToStr($hex)
			{
				$string = '';
				for ($i = 0; $i < strlen($hex) - 1; $i += 2) {
					$string .= chr(hexdec($hex[$i] . $hex[$i + 1]));
				}
				return $string;
			}

			if (!verificaPOST("", $_POST)) {
				$erro = true;
			} else {
				if (isset($carrinho))
					$_SESSION = $carrinho;
				verifica_autenticador();

				$instUsuarioGames = new UsuarioGames();
				$ret = $instUsuarioGames->autenticarLogin($_POST['login'], $_POST['senha']);
				$erro = false;

				if (!$ret) {
					$erro = true;
					$geraLog = new Log("log_login", array("Login ou senha inválidos: '" . $_POST['login'] . "', '" . $_POST['senha']));
					registrarTentativaFalha($_POST['login']);
				} else {
					$geraLog = new Log("log_login", array("Login com sucesso: '" . $_POST['login'] . "', '" . $_POST['senha']));
				}
			}


			if ($erro) {
				echo RETURN_WRONG;
			} else {
				if ($instUsuarioGames->existe_session()) {
					echo RETURN_SUCCESS;
				} else {
					echo RETURN_TWO_FACTOR;
				}
			}

		} else if ($validate->qtdCaracteres($_POST['login'], 2, 255) == 0) {
			//validar minimo de 3 caracteres e verificar maximo permitido para o capmo ug_login na tabela
			//metodo autenticarUgLogin($_POST['login'],$_POST['senha']);

			$_SESSION = array();
			session_destroy();
			session_start();
			session_regenerate_id();

			verifica_autenticador();

			$instUsuarioGames = new UsuarioGames();
			$ret = $instUsuarioGames->autenticarUgLogin(utf8_decode($_POST['login']), $_POST['senha']);
			$erro = false;

			if (!$ret) {
				$erro = true;
				$geraLog = new Log("log_login", array("Login ou senha inválidos: '" . $_POST['login'] . "', '" . $_POST['senha'] . "'"));
				registrarTentativaFalha($_POST['login']);

			} else {
				$geraLog = new Log("log_login", array("Login com sucesso: '" . $_POST['login'] . "', '" . $_POST['senha'] . "'"));
			}

			if ($erro) {
				echo RETURN_WRONG;
			} else {
				if ($instUsuarioGames->existe_session()) {
					echo RETURN_SUCCESS;
				} else {
					echo RETURN_TWO_FACTOR;
				}
			}
		} else {
			echo RETURN_WRONG;
		}
	} else {
		echo RETURN_EMPTY;
	}
}
