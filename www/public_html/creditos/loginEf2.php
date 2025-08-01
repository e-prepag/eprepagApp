<?php require_once __DIR__ . '/../../includes/constantes_url.php'; ?>
<?php
require_once "../../includes/constantes.php";
require_once DIR_INCS . "main.php";
require_once DIR_INCS . "pdv/main.php";
require_once DIR_CLASS . "pdv/classOperadorGamesUsuario.php";
require '../libs/PHPGangsta/GoogleAuthenticator.php';

// include do arquivo contendo IPs DEV
require_once DIR_INCS . "configIP.php";
require_once DIR_CLASS . "util/Login.class.php";

require_once "/www/db/connect.php";
require_once "/www/db/ConnectionPDO.php";

$connection = ConnectionPDO::getConnection()->getLink();

$sqlVerificaBloqueio = "select * from bloqueios_login_pdv where login = :LOGIN and tentativas >= 5;";

$queryVerificaBloqueio = $connection->prepare($sqlVerificaBloqueio);
$queryVerificaBloqueio->bindValue(":LOGIN", $_SESSION['login_usuario']);
$queryVerificaBloqueio->execute();

$resultadoDaVerificacao = $queryVerificaBloqueio->fetch(PDO::FETCH_ASSOC);

if ($resultadoDaVerificacao !== false) {
    header("Location: pagina_bloqueio.php");
    exit();
}

$salva_dispositivo = false;


$login_id = $_SESSION['id_do_usuario'];
$login_usuario = $_SESSION['login_usuario'];
if (!$login_id || !is_numeric($login_id)) {
    $login_id = 0;
}
$login_autenticado = true;
if($_SESSION['pode_logar'] == 1){
    $login_autenticado = false;
}
elseif ($_SESSION['usuario_operador']) {
    
    $sql = "SELECT ugo_acesso_sem_aut, ugo_chave_autenticador FROM dist_usuarios_games_operador WHERE ugo_id = ?";

    $stmt = $connection->prepare($sql);
    $stmt->execute([$login_id]);
    $auth = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$auth) {
        $msgAuth = "Login ou senha inv�lidos.\n";

        $linha = "2[" . date('Y-m-d H:i:s') . "] [$login_usuario] $msgAuth" . PHP_EOL;
        file_put_contents('/www/log/log_login.txt', $linha, FILE_APPEND);

        $strRedirect = "login.php?msg=" .
            urlencode($msgAuth) .
            "&login=" .
            urlencode($_SESSION['login_usuario']);

        header("Location: $strRedirect");
        exit;
    } elseif (empty($auth['ugo_chave_autenticador'])) {
        $dataUltimoAcesso = new DateTime($auth['ugo_acesso_sem_aut']);
        $dataHoje = new DateTime();

        // Defina o prazo m�ximo permitido (por exemplo, 7 dias)
        $prazoMaximo = 28;

        // Calcula a diferen�a de dias
        $diasPassados = $dataUltimoAcesso->diff($dataHoje)->days;
        $diasRestantes = $prazoMaximo - $diasPassados;

        if ($_REQUEST['tem_auth'] == "false") {
            if ($diasRestantes <= 0 ) {
                $msgAuth = "Voc� precisa adicionar um autenticador para poder realizar seu login.\n";

                $linha = "2[" . date('Y-m-d H:i:s') . "] [$login_usuario] $msgAuth" . PHP_EOL;
                file_put_contents('/www/log/log_login.txt', $linha, FILE_APPEND);

                $strRedirect = "login.php?msg=" .
                    urlencode($msgAuth) .
                    "&login=" .
                    urlencode($_SESSION['login_usuario']);

                header("Location: $strRedirect");
                exit;
            }
            $login_autenticado = false;
        }else{
            header("Location: alterar_token.php");
            exit;
        }
    } else {

        if (!checkDevice($login_id, $connection, true)) {
            $ga = new PHPGangsta_GoogleAuthenticator();
            if (!$ga->verifyCode($auth['ugo_chave_autenticador'], $_REQUEST['token'], 2)) {
                $msgAuth = "Token inv�lido.\n";

                $linha = "2[" . date('Y-m-d H:i:s') . "] [$login_usuario] $msgAuth" . PHP_EOL;
                file_put_contents('/www/log/log_login.txt', $linha, FILE_APPEND);

                $strRedirect = "login.php?msg=" .
                    urlencode($msgAuth) .
                    "&login=" .
                    urlencode($_SESSION['login_usuario']);

                header("Location: $strRedirect");
                exit;
            }
            if ($_POST['salvarDispositivo'] == "sim") {
                $salva_dispositivo = true;
            }
        }
    }
    unset($_SESSION['usuario_operador']);
} else {
    $sql = "SELECT ug_acesso_sem_aut, ug_chave_autenticador FROM dist_usuarios_games WHERE ug_id = ?";

    $stmt = $connection->prepare($sql);
    $stmt->execute([$login_id]);
    $auth = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$auth) {
        $msgAuth = "Login ou senha inv�lidos.\n";

        $linha = "2[" . date('Y-m-d H:i:s') . "] [$login_usuario] $msgAuth" . PHP_EOL;
        file_put_contents('/www/log/log_login.txt', $linha, FILE_APPEND);

        $strRedirect = "login.php?msg=" .
            urlencode($msgAuth) .
            "&login=" .
            urlencode($_SESSION['login_usuario']);

        header("Location: $strRedirect");
        exit;
    } elseif (empty($auth['ug_chave_autenticador'])) {
        $dataUltimoAcesso = new DateTime($auth['ug_acesso_sem_aut']);
        $dataHoje = new DateTime();

        // Defina o prazo m�ximo permitido (por exemplo, 7 dias)
        $prazoMaximo = 28;

        // Calcula a diferen�a de dias
        $diasPassados = $dataUltimoAcesso->diff($dataHoje)->days;
        $diasRestantes = $prazoMaximo - $diasPassados;

        if ($_REQUEST['tem_auth'] == "false") {
            if ($diasRestantes <= 0 ) {
                $msgAuth = "Voc� precisa adicionar um autenticador para poder realizar seu login.\n";

                $linha = "2[" . date('Y-m-d H:i:s') . "] [$login_usuario] $msgAuth" . PHP_EOL;
                file_put_contents('/www/log/log_login.txt', $linha, FILE_APPEND);

                $strRedirect = "login.php?msg=" .
                    urlencode($msgAuth) .
                    "&login=" .
                    urlencode($_SESSION['login_usuario']);

                header("Location: $strRedirect");
                exit;
            }
            $login_autenticado = false;
        }else{
            header("Location: alterar_token.php");
            exit;
        }
    } else {

        if (!checkDevice($login_id, $connection, false)) {
            $ga = new PHPGangsta_GoogleAuthenticator();
            if (!$ga->verifyCode($auth['ug_chave_autenticador'], $_REQUEST['token'], 2)) {
                $msgAuth = "Token inv�lido.\n";

                $linha = "2[" . date('Y-m-d H:i:s') . "] [$login_usuario] $msgAuth" . PHP_EOL;
                file_put_contents('/www/log/log_login.txt', $linha, FILE_APPEND);

                $strRedirect = "login.php?msg=" .
                    urlencode($msgAuth) .
                    "&login=" .
                    urlencode($_SESSION['login_usuario']);

                header("Location: $strRedirect");
                exit;
            }
            if ($_POST['salvarDispositivo'] == "sim") {
                $salva_dispositivo = true;
            }
        }
    }
}

$pag = $_SESSION['pag'];
$senha = $_SESSION['senha_usuario'];
$g_recaptcha_response = $_SESSION['g-recaptcha-response'];

/*
       NOTA:::
       
       Se o usu�rio est� na tabela de bloqueio e atingiu o limite de tentativas, ele ser� direcionado � pagina_bloquieio.php e n�o conseguir� logar.
       Se n�o, a sess�o seguir� com o restante do fluxo.
       
   */

if (checkIP()) {
    $server_url = $_SERVER["SERVER_NAME"];
}

if ($_SERVER["HTTPS"] != "on") {
    redirect("https://" . $server_url . "/creditos/login.php");
    die();
} // NOTA::: Faz o redirecionamento adicionando SSL na URL.

session_destroy();

/*
       NOTA:::
       
       Esse session_destroy() est� comentado porque limpa os dados da sess�o.
       
       � bom descomentar e execut�-lo para limpar os dados de testes.
   */

session_start(); // Inicia a sess�o

$msg = ""; // Define a vari�vel mensagem




if (!isset($_SESSION["tentativas_login"])) {

    $_SESSION["tentativas_login"] = 1;

} else {

    if ($_SESSION["tentativas_login"] >= 5) {

        bloquearAcesso();

    }

    $_SESSION["tentativas_login"]++;
}
/*
       NOTA:::
       
       Se "tentativas_login" estiver definido e n�o for nulo, adiciona o valor 1.
       Caso a quatidade de tentativas for igual ou maior que 5, chama a fun��o bloquearAcesso() .
       Se n�o, incrementa "tentativas_login" .
   */

if (isset($_SESSION["bloqueado"]) && $_SESSION["bloqueado"] == true) {

    global $connection;

    $sql = "select * from bloqueios_login_pdv where ip = :IP;";

    $query = $connection->prepare($sql);
    $query->bindValue(":IP", $_SERVER["REMOTE_ADDR"]);
    $query->execute();

    $result = $query->fetch(PDO::FETCH_ASSOC);

    if ($result['tentativas'] >= 5) {

        header("Location: pagina_bloqueio.php");
        exit();
    } else {

        unset($_SESSION["bloqueado"]);
        $_SESSION["tentativas_login"] = 1;

    }
}
/*
       NOTA:::
       
       O c�digo acima verifica se a sess�o foi bloqueada e se o IP est�o no banco de dados.
   
       Caso esteja bloqueado e no banco de dados, envia para pagina_bloqueio.php .
       
       Se n�o, limpa a condi��o "bloqueado" da sess�o e adiciona "tentativas_login" igual a 1.
   */
function generateDeviceId()
{
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ? $_SERVER['HTTP_USER_AGENT'] : 'unknown';
    $randomToken = bin2hex(openssl_random_pseudo_bytes(32));
    return hash('sha256', $userAgent . $randomToken);
}

function saveDevice($userId, $deviceId, $pdo, $operador)
{
    $tabela = $operador ? 'dist_usuarios_games_operador_dispositivos' : 'dist_usuarios_games_dispositivos';
    $expiry = date('Y-m-d H:i:s', strtotime('+30 days')); // Expira em 30 dias
    $stmt = $pdo->prepare("INSERT INTO $tabela (user_id, device_token, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$userId, $deviceId, $expiry]);
}

function setDeviceCookie($deviceId)
{
    setcookie(
        'device_token',   // Nome do cookie
        $deviceId,        // Valor do cookie
        time() + (31 * 24 * 60 * 60), // Expira��o (timestamp)
        '/',              // Caminho
        '',               // Dom�nio (vazio = padr�o)
        isset($_SERVER['HTTPS']), // Secure: apenas HTTPS
        true              // HttpOnly: bloqueia acesso via JS
    );
}

function checkDevice($userId, $pdo, $operador)
{
    if (!isset($_COOKIE['device_token'])) {
        return false; // Sem cookie, exige login
    }

    $tabela = $operador ? 'dist_usuarios_games_operador_dispositivos' : 'dist_usuarios_games_dispositivos';

    $deviceId = $_COOKIE['device_token'];
    $stmt = $pdo->prepare("SELECT * FROM $tabela WHERE user_id = ? AND device_token = ? AND expires_at > NOW()");
    $stmt->execute([$userId, $deviceId]);

    if ($stmt->fetch()) {
        return true; // Dispositivo v�lido
    } else {
        return false; // Dispositivo inv�lido ou expirado
    }
}


function bloquearAcesso()
{

    global $connection;

    $_SESSION["bloqueado"] = true;

    $sql = "select * from bloqueios_login_pdv where ip = :IP;";

    $query = $connection->prepare($sql);
    $query->bindValue(":IP", $_SERVER["REMOTE_ADDR"]);
    $query->execute();

    $result = $query->fetch(PDO::FETCH_ASSOC);

    if ($result !== false) {

        $sqlUpdate = "update bloqueios_login_pdv set created = :DATE_TIME, tentativas = :TENTATIVAS where ip = :IP;";

        $query = $connection->prepare($sqlUpdate);

        $query->bindValue(":DATE_TIME", date("m-d-Y H:i:s"));
        $query->bindValue(":TENTATIVAS", $_SESSION["tentativas_login"]);
        $query->bindValue(":IP", $_SERVER["REMOTE_ADDR"]);
        $query->execute();

    } else {

        $sqlInsert = "insert into bloqueios_login_pdv(id, ug_id, created, ip, login, tentativas, visualizacao) values (default, NULL, :DATE_TIME, :IP, :LOGIN, :TENTATIVAS, 'S');";

        $query = $connection->prepare($sqlInsert);

        $query->bindValue(":DATE_TIME", date("m-d-Y H:i:s"));
        $query->bindValue(":IP", $_SERVER["REMOTE_ADDR"]);
        $query->bindValue(":LOGIN", $GLOBALS['login_usuario']);
        $query->bindValue(":TENTATIVAS", $_SESSION["tentativas_login"]);
        $query->execute();

    }

    header("Location: pagina_bloqueio.php");
    exit;

} // NOTA::: Atualiza ou insere no banco de dados as informa��es da sess�o / login bloqueado.

if ($g_recaptcha_response != "valido") {
    $msg .= "Recaptcha Errado. \n";
}

/*
       NOTA:::
       
       Verifica se a resposta do recaptcha est� OK.
       
       Se n�o estiver OK ou se estiver vazia, adiciona a mensagem de erro na vari�vel $msg .
   */

if (substr($pag, 0, 23) == "/creditos/") {
    $pag = "http" . ($_SERVER["HTTPS"] == "on" ? "s" : "") . "://" . $server_url . $pag;
    //	echo "new pag: '".$pag."'<br>";
}

/*
       NOTA:::
       
       Parece que o c�digo acima � uma failsafe que corrige a URL.
   */


/*
       $msg = "Usuario bloqueado. Por favor, tente novamente em %s.";
       $msg = "Usuario bloqueado. Para desbloquear seu acesso, entre em contato <a href='EPREPAG_URL_HTTPS/game/suporte.php' title='Desbloquear Acesso'>aqui<a>.";
       
       NOTA:::
       
       Inicialmente, a delacara��o de $msg acima estava ativa, por�m, aparentemente, estava interferindo na contagem de tentativas.
   */


$strRedirect = "https://" . $server_url . "/creditos/login.php?login=" . urlencode($login_usuario) . "&msg=";

$clsLogin = new Login();

if (file_exists(DIR_INCS . "attrLogin.php")) {

    require_once DIR_INCS . "attrLogin.php";
    $clsLogin->setTempoDesbloqueio($cfgLoginLan->tempoMaxBloqueio);
    $clsLogin->setMaxTentativas($cfgLoginLan->maxTentativas);

}

$clsLogin->setUrlRedirect($strRedirect);
$clsLogin->setMsgErro($msg);
$clsLogin->autentica();

/*
       NOTA:::
       
       Faz a inst�ncia para validar os dados, usando como refer�ncia os limites de tentativas + tempo de bloqueio.
   
   */


if (!$login_usuario || $login_usuario == "") {
    $msg .= "O login deve ser preenchido.\n";
}

if (!$senha || $senha == "") {
    $msg .= "A senha deve ser preenchida.\n";
}

if ($msg != "") {
    $linha = "2[" . date('Y-m-d H:i:s') . "] [$login_usuario] $msg" . PHP_EOL;
    file_put_contents('/www/log/log_login.txt', $linha, FILE_APPEND);
    $strRedirect = "https://" . $server_url . "/creditos/login.php?pag=" . urlencode($pag) . "&msg=" . urlencode($msg) . "&login=" . urlencode($login_usuario . "&tentativas=" . urlencode($_SESSION["tentativas_login"]));
}

if ($msg == "") {
    if (!isset($_SESSION["tentativas_login"]) || $_SESSION["tentativas_login"] <= 0) {
        $_SESSION = [];
        @session_destroy();
        session_start();
        session_regenerate_id();
    }

    function verificaPOST($referer, $POST)
    {
        //if (strpos($referer,"bronzato.com.br")===false && strpos($referer,"contause.digital")===false){return false;}
        $flag = true;
        foreach ($_POST as $xa => $xb) {
            $xb = serialize($xb);
            if (
                strpos($xb, "dbms_pipe.receive_message") !== false ||
                strpos($xb, "DBMS_PIPE.RECEIVE_MESSAGE") !== false ||
                strpos($xb, "delete") !== false ||
                strpos($xb, "delete") !== false ||
                strpos($xb, "update") !== false ||
                strpos($xb, "select") !== false
            ) {
                return false;
            }
            if (
                strpos($xb, "dbms_pipe.receive_message") !== false ||
                strpos($xb, "DBMS_PIPE.RECEIVE_MESSAGE") !== false ||
                strpos(hexToStr($xb), "delete") !== false ||
                strpos(hexToStr($xb), "update") !== false ||
                strpos(hexToStr($xb), "select") !== false
            ) {
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
        $hex = "";
        for ($i = 0; $i < strlen($string); $i++) {
            $ord = ord($string[$i]);
            $hexCode = dechex($ord);
            $hex .= substr("0" . $hexCode, -2);
        }
        return strToUpper($hex);
    }

    function hexToStr($hex)
    {
        $string = "";
        for ($i = 0; $i < strlen($hex) - 1; $i += 2) {
            $string .= chr(hexdec($hex[$i] . $hex[$i + 1]));
        }
        return $string;
    }

    if (!verificaPOST("", $_POST)) {
        $ret = false;
    } else {
        //validaLogin
        $instUsuarioGames = new UsuarioGames();
        $ret = $instUsuarioGames->autenticarLogin($login_usuario, $senha, $login_autenticado);
        if (!$ret) {
            $instUsuarioGames = new UsuarioGamesOperador();
            $ret = $instUsuarioGames->autenticarLogin($login_usuario, $senha, $login_autenticado);
            if ($ret) {
                $op = unserialize($_SESSION["dist_usuarioGamesOperador_ser"]);
                $ugo_ug_id = $op->getUgId();
                if ($ugo_ug_id) {
                    $sqlDataLoginOp = "UPDATE dist_usuarios_games SET ug_data_ultimo_acesso = NOW() WHERE ug_id = :ug_id";
                    $stmt = $connection->prepare($sqlDataLoginOp);
                    $stmt->bindParam(':ug_id', $ugo_ug_id, PDO::PARAM_INT);
                    $stmt->execute();

                    if ($salva_dispositivo) {
                        $deviceId = generateDeviceId();
                        saveDevice($login_id, $deviceId, $connection, true);
                        setDeviceCookie($deviceId);
                    }
                }
            }
        } else {
            if ($salva_dispositivo) {
                $deviceId = generateDeviceId();
                saveDevice($login_id, $deviceId, $connection, false);
                setDeviceCookie($deviceId);
            }
        }
    }

    if (!$ret) {
        $clsLogin->falhaAutenticacao();

        $ug_login = "SELECT ug_substatus FROM dist_usuarios_games WHERE ug_login = :ug_login";
        $stmt = $connection->prepare($ug_login);
        $loginUpper = strtoupper($login_usuario); // Converte o login para mai�sculas
        $stmt->bindParam(':ug_login', $loginUpper, PDO::PARAM_STR);
        $stmt->execute();

        $infoRow = $stmt->fetch(PDO::FETCH_ASSOC);

        if (
            isset($infoRow["ug_substatus"]) &&
            $infoRow["ug_substatus"] == "12"
        ) {
            $msg = "Para sua seguran�a, seu PDV est� inativo. Para reativ�-lo, entre em contato com o suporte E-Prepag.\n";
            gravaLog_Login("Para sua seguran�a, seu PDV est� inativo. Para reativ�-lo, entre em contato com o suporte E-Prepag: '$login_usuario', '$senha'.\n");
        } else {
            $msg = "Login ou senha inv�lidos.\n";
            gravaLog_Login("Login ou senha inv�lidos: '$login_usuario', '$senha'.\n");
        }
        $strRedirect =
            "http" .
            ($_SERVER["HTTPS"] == "on" ? "s" : "") .
            "://" .
            $server_url .
            "/creditos/login.php?pag=" .
            urlencode($pag) .
            "&msg=" .
            urlencode($msg) .
            "&login=" .
            urlencode($login_usuario);
        // $strRedirect = "https://" . $server_url . "/creditos/login.php?pag=" . urlencode($pag) . "&msg=" . urlencode($msg) . "&login=" . urlencode($login . "&tentativas=" . urlencode($_SESSION["tentativas_login"]));
    } else {
        gravaLog_Login("Login com sucesso: '$login_usuario', '$senha'.\n");
        //'Pagina default de redirecionamento apos login
        $strRedirect = "https://" . $server_url . "/creditos/";
        //'Se foi passado pagina de redirecionamento
        if ($pag) {
            //verifica se a pagina atual nao eh a pagina do redirect, senao entra em loop
            //if instr(1, Request.ServerVariables("URL"), mid(strRedirect, 1, instr(1, strRedirect, "?", 1)-1), 1) = 0 then
            if (strpos($pag, "/creditos/login.php")) {
                $pag = $strRedirect;
            }
            //'Se nao eh popup, redireciona a janela atual
            if (!$pop) {
                // Se login vem da p�gina de cadastro de campeonatos -> vai para index.php
                if (strpos($pag, "cadastroIn2.php")) {
                    $pag = $strRedirect;
                } else {
                    $strRedirect = $pag;
                }
            } else {
                //Fechando Conex�o
                pg_close($connid);
                //'Se eh popup, redireciona a janela atual e abre o popup
                ?>
                <html>

                <body
                    OnLoad="window.location.href='<?= $strRedirect ?>';window.open('<?= $pag ?>','','scrollbars=yes,width=467,height=500');">
                    <html>
                    <?php exit;
            }
        }
        //inicio do bloco de redirecionamento do questionario
        $ug_id = 0;
        $ug_alterar_senha = 0;
        if (
            isset($_SESSION["dist_usuarioGames_ser"]) &&
            !is_null($_SESSION["dist_usuarioGames_ser"])
        ) {
            $usuarioGames = unserialize($_SESSION["dist_usuarioGames_ser"]);
            $ug_id = $usuarioGames->getId();
            // vari�vel abaixo necess�ria para verifica��o se � obrigat�rio a altera��o de senha no pr�ximo login
            $ug_alterar_senha = $usuarioGames->getAlteraSenha();
        }
        $questionario = new Questionarios($ug_id, "L");
        $aux_vetor = $questionario->CapturarProximoQuestionario();
        if ($questionario->getRedireciona()) {
            //'Pagina questionario de redirecionamento apos login
            $strRedirect =
                "http" .
                ($_SERVER["HTTPS"] == "on" ? "s" : "") .
                "://" .
                $server_url .
                "/creditos/questionario.php?ug_id=" .
                $ug_id .
                "&ql_tipo_usuario=L";
        }
        //fim do bloco de redirecionamento do questionario
        //inicio do bloco de redirecionamento para altera��o de senha
        if ($ug_alterar_senha == 1) {
            //'Pagina altera��o de senha no redirecionamento apos login
            $strRedirect =
                "http" .
                ($_SERVER["HTTPS"] == "on" ? "s" : "") .
                "://" .
                $server_url .
                "/creditos/alterar_senha.php";
        }
        //fim do bloco de redirecionamento para altera��o de senha
    }
}

/*
       NOTA:::
       
       Esse bloco dentro do if($msg == ""){} parece ser uma maneira de validar e evitar vazamentos de dados caso ocorra um acesso de forma
       n�o convecional.
   */


//Fecha a Conex�o
pg_close($connid);

//Redirect
redirect($strRedirect);
?>