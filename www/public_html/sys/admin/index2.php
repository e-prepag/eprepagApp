<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);  // Exibe todos os tipos de erros
@session_start();
date_default_timezone_set('America/Fortaleza');
require_once "../../../includes/constantes.php";
require_once "../../../class/pdv/classGamesUsuario.php";
require_once $raiz_do_projeto . "includes/inc_register_globals.php";
require_once $raiz_do_projeto . "public_html/sys/includes/functions.php";
require_once $raiz_do_projeto . "class/util/Log.class.php";
require_once "../../../includes/load_dotenv.php";
require_once __DIR__ . "/../../../libs/PHPGangsta/GoogleAuthenticator.php";
require_once "../includes/funcoes_login.php";
require_once "/www/class/classSecureEncryption.php";

if ($_SESSION['RECAPTCHA_TRUE'] != true) {
    header("Location: index.php?Invalido=1");
    exit;
}

$_SESSION['RECAPTCHA_TRUE'] = null;

$varBlDebug = true;

if (!$_POST['passw'] || !$_POST['user']) header("Location: index.php?Empty=1");

require_once $raiz_do_projeto . "includes/gamer/chave.php";
require_once $raiz_do_projeto . "includes/gamer/AES.class.php";
//Instanciando Objetos para Descriptografia

$senha_decript = null;
$user_decript = null;

$okDecript = descript_login($_POST['user'], $_POST['passw'], $senha_decript, $user_decript);
if ($okDecript != 1) {
    header("Location: index.php?Erro=13");
    exit;
}

$user = strtoupper($user_decript);

$Enviar = true;

if ($Enviar) {

    gravaLog_LoginSys("Login: '" . $user, true);

    $_SESSION["iduser_bko_pub"] = "";
    $_SESSION["tipo_acesso_pub"] = "";
    $_SESSION["opr_codigo_pub"] = "";
    $_SESSION["nome_bko"] = "";
    $_SESSION["userlogin_bko"] = "";
    $_SESSION["opr_nome"] = "";
    $_SESSION["datalog_bko"] = "";
    $_SESSION["horalog_bko"] = "";

    require_once $raiz_do_projeto . "db/connect.php";
    require_once $raiz_do_projeto . "db/ConnectionPDO.php";

    $con = ConnectionPDO::getConnection();

    if (!$con->isConnected()) {
        // retornar os erros: $con->getErrors();
        die('Erro#2');
    }

    $pdo = $con->getLink();

    if ($_SESSION["id_do_usuario"]) {
        $sql = "SELECT * FROM usuarios WHERE id = ? AND ((tipo_acesso='AD') OR (tipo_acesso='DT') OR (tipo_acesso='SV') OR (tipo_acesso='AT') OR (tipo_acesso='PU') OR (tipo_acesso='US'))";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_SESSION["id_do_usuario"]]);
    } else {
        $sql = "SELECT * FROM usuarios WHERE shn_login = ? AND ((tipo_acesso='AD') OR (tipo_acesso='DT') OR (tipo_acesso='SV') OR (tipo_acesso='AT') OR (tipo_acesso='PU') OR (tipo_acesso='US'))";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($user));
    }

    $fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($fetch) == 1) {
        $pgrow = $fetch[0];


        if (!$_SESSION["id_do_usuario"]) {
            $bcrypt = new SecureEncryption();
            if (!$bcrypt->verifyPassword($senha_decript, $pgrow['shn_password'])) {
                header("Location: login.php?erro=2");
                exit;
            }
        }

        if ($pgrow['bko_autoriza'] == 'S') {
            $iduser_var   = $pgrow['id'];
            $nome_var   = $pgrow['shn_nome'];
            $login_var   = $pgrow['shn_login'];
            $opr_codigo_var   = $pgrow['opr_codigo'];
            $opr_nome_var = '';

            if ($opr_codigo_var > 0) {
                $sql = "select opr_nome from operadoras where opr_codigo= ?";

                try {
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute(array($opr_codigo_var));
                    $operadoras = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $opr_nome_var = $operadoras[0]["opr_nome"];
                } catch (PDOException $e) {
                    $geraLog = new Log("LOGINSYSADMIN", array(
                        "ERROR: " . $ex->getMessage(),
                        "FILE: " . $ex->getFile(),
                        "LINE " . $ex->getLine()
                    ));
                }
            }

            $tipo_acesso_var = $pgrow['tipo_acesso'];
            $datalog_var = $pgrow['bko_datalogin'];
            $horalog_var = $pgrow['bko_horalogin'];
            $opr_banco = $pgrow['opr_codigo'];

            if (!empty($iduser_var)) {

                $dataUltimoAcesso = new DateTime($pgrow['sem_aut_data']);
                $dataHoje = new DateTime();
                $diasRestantes = 28 - $dataUltimoAcesso->diff($dataHoje)->days;

                if ($diasRestantes <= 0 || !empty($pgrow['chave_autenticador'])) {

                    if (empty($pgrow['chave_autenticador'])) {
                        header("Location: index.php?erro=1");
                        exit;
                    }

                    $ga = new PHPGangsta_GoogleAuthenticator();
                    $tokenValido = $ga->verifyCode($pgrow['chave_autenticador'], $_POST['token'], 2);
                    $deviceValido = checkDevice($iduser_var, $pdo);

                    if (!$tokenValido && !$deviceValido) {
                        header("Location: index.php?erro=4");
                        exit;
                    }

                    // Salva dispositivo se selecionado
                    if (!empty($_POST['salvarDispositivo']) && $_POST['salvarDispositivo'] === "sim") {
                        $deviceId = generateDeviceId();
                        saveDevice($iduser_var, $deviceId, $pdo);
                        setDeviceCookie($deviceId);
                    }
                }

                if (isset($_SESSION['iduser_bko']) && $_SESSION['iduser_bko'] != $iduser_var) {
                    $_SESSION = array();
                    session_destroy();
                    session_start();
                }

                $_SESSION["iduser_bko_pub"] = $iduser_var;
                $_SESSION["tipo_acesso_pub"] = $tipo_acesso_var;
                $_SESSION["opr_codigo_pub"] = $opr_codigo_var;
                $_SESSION["nome_bko"] = $nome_var;
                $_SESSION["userlogin_bko"] = $login_var;
                $_SESSION["opr_nome"] = $opr_nome_var;
                $_SESSION["datalog_bko"] = $datalog_var;
                $_SESSION["horalog_bko"] = $horalog_var;
                $_SESSION["opr_vinculo"] = $opr_banco;

                gravaLog_LoginSys("Login Success: $login_var", true);
            } else {
                gravaLog_LoginSys("Login Error (1): $login_var", true);

                header("Location: index.php");
                exit;
            }

            $acesso_atual = $pgrow['shn_qtde_acesso'] + 1;

            /*
                             $sql = "update usuarios set bko_datalogin='".date('Y-m-d')."', bko_horalogin='".date('H:i:s')."', shn_qtde_acesso=".$acesso_atual." where id='".$pgrow['id']."'";
                             pg_exec($connid,$sql);

                             $sql = "insert into bko_access_log (log_data, log_hora, log_ip, log_user_id) values ('".date('Y-m-d')."', '".date('H:i:s')."', '".retorna_ip_acesso_sys_admin()."', '".$pgrow['id']."') ";
                             
                             echo $sql;
                             pg_exec($connid,$sql);
                             */

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
                $geraLog = new Log("LOGINSYSADMIN", array(
                    "ERROR: " . $e->getMessage(),
                    "FILE: " . $e->getFile(),
                    "LINE " . $e->getLine()
                ));
            }

            try {
                $sql = "insert into bko_access_log (log_data, log_hora, log_ip, log_user_id) values ('" . date('Y-m-d') . "', '" . date('H:i:s') . "', '" . retorna_ip_acesso_sys_admin() . "', '" . $pgrow['id'] . "') ";
                $stmt22 = $pdo->prepare($sql);
                $stmt22->execute();
            } catch (PDOException $e) {
                $geraLog = new Log("LOGINSYSADMIN", array(
                    "ERROR: " . $e->getMessage(),
                    "FILE: " . $e->getFile(),
                    "LINE " . $e->getLine()
                ));
            }

            header("Location: frameset.php");
            exit;
        } else {
            gravaLog_LoginSys("Login Error (2 Blocked): $login_var", true);
            header("Location: index.php?UserBlocked=1");
            exit;
        }
    } else {
        gravaLog_LoginSys("Login Error (2 Invalido): $login_var", true);
        header("Location: index.php?Invalido=1");
        exit;
    }
    pg_close($connid);
} else {
    header("Location: index.php");
    exit;
}
function gravaLog_LoginSys($mensagem, $forced_save = false)
{

    // Desativa o registro de Sucesso/Erro de logins
    global $raiz_do_projeto;
    if (!$forced_save) return;

    //Arquivo
    $file = $raiz_do_projeto . "arquivos_gerados/logs/log_login_sys.txt";

    //Mensagem
    $mensagem = date('Y-m-d H:i:s') . " " . $_SERVER["SCRIPT_FILENAME"] . " (" . $_SERVER['REMOTE_ADDR'] . ")\n" . $mensagem . "\n";

    //Grava mensagem no arquivo
    if ($handle = fopen($file, 'a+')) {
        fwrite($handle, $mensagem);
        fclose($handle);
    }
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
