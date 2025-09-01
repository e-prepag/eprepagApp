<?php
session_start();
date_default_timezone_set('America/Fortaleza');
require_once '../includes/constantes.php';

require_once $raiz_do_projeto . "includes/inc_register_globals.php";

$user = strtoupper(filter_input(INPUT_POST, 'user'));
$passw = filter_input(INPUT_POST, 'passw');

if (empty($user) || empty($passw)) {
        header("Location: login.php?Empty=1");
        exit;
}

require_once $raiz_do_projeto . "includes/gamer/chave.php";
require_once $raiz_do_projeto . "includes/gamer/AES.class.php";
require_once $raiz_do_projeto . "class/util/Log.class.php";

//Instanciando Objetos para Descriptografia
$chave256bits = new Chave();
$aes = new AES($chave256bits->retornaChavePub());
$passw = base64_encode($aes->encrypt(addslashes($passw)));

gravaLog_LoginBKO("Login BKO: '" . $user . "', '" . $passw . "'");
if ($Enviar) {
        require_once $raiz_do_projeto . 'db/connect.php';
        require_once $raiz_do_projeto . "db/ConnectionPDO.php";

        $con = ConnectionPDO::getConnection();

        if (!$con->isConnected()) {
                die('Erro#2');
        }
        $pdo = $con->getLink();

        if ($_SESSION["id_do_usuario"]) {
                $sql = "SELECT * FROM usuarios WHERE id = ? AND ((tipo_acesso='AD') OR (tipo_acesso='DT') OR (tipo_acesso='SV') OR (tipo_acesso='AT') OR (tipo_acesso='US'))";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$_SESSION["id_do_usuario"]]);
        } else {
                $sql = "SELECT * FROM usuarios WHERE shn_login = ? AND shn_password = ? AND ((tipo_acesso='AD') OR (tipo_acesso='DT') OR (tipo_acesso='SV') OR (tipo_acesso='AT') OR (tipo_acesso='US'))";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(array($user, $passw));
        }

        $fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($fetch) == 1) {
                if (!isset($pgrow)) $pgrow = array('bko_autoriza' => false);
                gravaLog_LoginBKO("Login BKO - Autoriza: '" . $pgrow['bko_autoriza'] . "'");
                $pgrow = $fetch[0];

                if ($pgrow['bko_autoriza'] == 'S') {
                        $iduser_var         = $pgrow['id'];
                        $user_login  = $pgrow['shn_login'];
                        $datalog_var = $pgrow['bko_datalogin'];
                        $horalog_var = $pgrow['bko_horalogin'];
                        $email_bo = $pgrow['shn_mail'];
                        $tipo_acesso = $pgrow['tipo_acesso'];
                        $visualiza_dados = $pgrow['visualiza_dados'];

                        gravaLog_LoginBKO("Login BKO - dados usuário: " . print_r($pgrow, true) . "");
                        if (!empty($iduser_var)) {

                                if (isset($_SESSION['iduser_bko_pub']) && $_SESSION['iduser_bko_pub'] != $iduser_var) {

                                        if (!$_SESSION['logar'] && !empty($pgrow['chave_autenticador'])) {
                                                if (!checkDevice($iduser_var, $pdo) || verifyCode($_POST['token'], $pgrow['chave_autenticador'])) {
                                                        header("Location: login.php?erro=2");
                                                        exit;
                                                }
                                                if ($_POST['salvarDispositivo'] == "sim") {
                                                        $deviceId = generateDeviceId();
                                                        saveDevice($iduser_var, $deviceId, $pdo);
                                                        setDeviceCookie($deviceId);
                                                }
                                        }else{
                                                header("Location: login.php?erro=2");
                                                exit; 
                                        }

                                        $_SESSION = array();
                                        session_destroy();
                                        session_start();
                                }

                                $_SESSION['iduser_bko'] = $iduser_var;
                                $_SESSION['userlogin_bko'] = $user_login;
                                $_SESSION['user_email'] = $email_bo;
                                $_SESSION['datalog_bko'] = $datalog_var;
                                $_SESSION['horalog_bko'] = $horalog_var;
                                $_SESSION['tipo_acesso'] = $tipo_acesso;
                                $_SESSION['visualiza_dados'] = $visualiza_dados;
                        } else {
                                header("Location: login.php?erro=2");
                                exit;
                        }

                        $acesso_atual = $pgrow['shn_qtde_acesso'] + 1;

                        try {
                                $sql = "update usuarios set bko_datalogin= :bko_datalogin, bko_horalogin= :bko_horalogin, shn_qtde_acesso= :shn_qtde_acesso where id= :id";
                                $stmt2 = $pdo->prepare($sql);
                                $stmt2->execute(array(
                                        ':bko_datalogin' => date('Y-m-d'),
                                        ':bko_horalogin' => date('H:i:s'),
                                        ':shn_qtde_acesso' => $acesso_atual,
                                        ':id' => $pgrow['id']
                                ));
                        } catch (PDOException $e) {
                                $geraLog = new Log("LOGINBACKOFFICE", array(
                                        "ERROR: " . $e->getMessage(),
                                        "FILE: " . $e->getFile(),
                                        "LINE " . $e->getLine()
                                ));
                        }

                        try {
                                $sql = "insert into bko_access_log (log_data, log_hora, log_ip, log_user_id) values ('" . date('Y-m-d') . "', '" . date('H:i:s') . "', '" . retorna_ip_acesso_BO() . "', '" . $pgrow['id'] . "') ";
                                $stmt22 = $pdo->prepare($sql);
                                $stmt22->execute();
                        } catch (PDOException $e) {
                                $geraLog = new Log("LOGINBACKOFFICE", array(
                                        "ERROR: " . $e->getMessage(),
                                        "FILE: " . $e->getFile(),
                                        "LINE " . $e->getLine()
                                ));
                        }

                        header("Location: /");
                        exit;
                } else {
                        header("Location: login.php?UserBlocked=1");
                        exit;
                }
        } else {
                header("Location: login.php?Invalido=1");
                exit;
        }
} //end if($Enviar)
else {
        header("Location: login.php?erro=3");
        exit;
}

function gravaLog_LoginBKO($mensagem)
{

        //Arquivo
        $file = $GLOBALS['raiz_do_projeto'] . "log/log_LoginBKO.txt";

        //Mensagem
        $mensagem = date('Y-m-d H:i:s') . " " . $_SERVER["SCRIPT_FILENAME"] . PHP_EOL . $mensagem . PHP_EOL;

        //Grava mensagem no arquivo
        if ($handle = fopen($file, 'a+')) {
                fwrite($handle, $mensagem);
                fclose($handle);
        }
}

function retorna_ip_acesso_BO()
{
        $realip = "";
        if (isset($_SERVER)) {
                if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                        $ip = $_SERVER['HTTP_CLIENT_IP'];
                } else {
                        $ip = $_SERVER['REMOTE_ADDR'];
                }
        } else {
                if (getenv('HTTP_X_FORWARDED_FOR')) {
                        $ip = getenv('HTTP_X_FORWARDED_FOR');
                } elseif (getenv('HTTP_CLIENT_IP')) {
                        $ip = getenv('HTTP_CLIENT_IP');
                } else {
                        $ip = getenv('REMOTE_ADDR');
                }
        }
        return $ip;
}

function generateDeviceId()
{
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ? $_SERVER['HTTP_USER_AGENT'] : 'unknown';
        $randomToken = bin2hex(openssl_random_pseudo_bytes(32));
        return hash('sha256', $userAgent . $randomToken);
}

function saveDevice($userId, $deviceId, $pdo)
{
        $expiry = date('Y-m-d H:i:s', strtotime('+30 days')); // Expira em 30 dias
        $stmt = $pdo->prepare("INSERT INTO usuarios_bo_dispositivos (user_id, device_token, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $deviceId, $expiry]);
}

function setDeviceCookie($deviceId)
{
        setcookie(
                'device_token_bko',   // Nome do cookie
                $deviceId,        // Valor do cookie
                time() + (31 * 24 * 60 * 60), // Expiração (timestamp)
                '/',              // Caminho
                '',               // Domínio (vazio = padrão)
                isset($_SERVER['HTTPS']), // Secure: apenas HTTPS
                true              // HttpOnly: bloqueia acesso via JS
        );
}

function checkDevice($userId, $pdo)
{
        if (!isset($_COOKIE['device_token_bko'])) {
                return false; // Sem cookie, exige login
        }

        $deviceId = $_COOKIE['device_token_bko'];
        $stmt = $pdo->prepare("SELECT * FROM usuarios_bo_dispositivos WHERE user_id = ? AND device_token = ? AND expires_at > NOW()");
        $stmt->execute([$userId, $deviceId]);

        if ($stmt->fetch()) {
                return true; // Dispositivo válido
        } else {
                return false; // Dispositivo inválido ou expirado
        }
}

function verifyCode($code, $secret)
{
        $ga = new PHPGangsta_GoogleAuthenticator();
        return $ga->verifyCode($secret, $code, 2);
}
