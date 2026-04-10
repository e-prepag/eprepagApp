<?php require_once __DIR__ . '/../../../includes/constantes_url.php'; ?>
<?php
/**
 * Classe para as regras de negocio das vendas
 *
 * @author Diego
 * @email diego.gomes@e-prepag.com.br
 * @date 16-07-2015
 */
require_once RAIZ_DO_PROJETO . "includes/main.php";
require_once RAIZ_DO_PROJETO . "includes/pdv/main.php";
require_once RAIZ_DO_PROJETO . "includes/pdv/corte_classPrincipal.php"; //corte_constantes
require_once RAIZ_DO_PROJETO . "includes/pdv/captura_inc.php";
require_once RAIZ_DO_PROJETO . "class/util/Log.class.php";
require_once RAIZ_DO_PROJETO . "class/pdv/classOperadorGamesUsuario.php";
require_once RAIZ_DO_PROJETO . 'includes/functions.php';
require_once RAIZ_DO_PROJETO . "includes/configuracao.inc";
require_once RAIZ_DO_PROJETO . "class/classBannerDrawShadow.php";
require_once RAIZ_DO_PROJETO . 'class/business/BannerBO.class.php';
require_once RAIZ_DO_PROJETO . 'includes/configIP.php';
require_once RAIZ_DO_PROJETO . 'includes/constantes.php';
require_once RAIZ_DO_PROJETO . "class/util/Busca.class.php";

function obter_endereco_ip_usuario() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}


try {
    $con = ConnectionPDO::getConnection();
    if ($con->isConnected()) {

        $id_usuario_gamer = 0;
        if(isset($_SESSION['dist_usuarioGames_ser'])){
            $usuarioGamesSession = unserialize($_SESSION['dist_usuarioGames_ser']);
            $id_usuario_gamer = $usuarioGamesSession->getId();
        }
        $pdo = $con->getLink();

        salvar_log_req($pdo, $id_usuario_gamer);

    }

} catch (Exception $ex) {

    $logFile = '/www/arquivos_gerados/logs/erro_log_acoes_pdv_' . date('Y-m-d') . '.log';
    $logMessage = date('Y-m-d H:i:s') . " | Exception: " . $ex->getMessage() . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND);

} catch (PDOException $ex) {

    $logFile = '/www/arquivos_gerados/logs/erro_log_acoes_pdv_' . date('Y-m-d') . '.log';
    $logMessage = date('Y-m-d H:i:s') . " | PDOException: " . $ex->getMessage() . PHP_EOL;
    $logMessage .= "Trace: " . $ex->getTraceAsString() . PHP_EOL; // Inclui o rastreamento da exceção
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

$server_url = "" . EPREPAG_URL . "";
if (checkIP()) {
    $server_url = $_SERVER['SERVER_NAME'];
}

class HeaderController
{

    private $_errors = array(); // controle de erros
    public $usuarios = false; // session usuarios
    public $usuariosOperador = false; // session usuarios operador
    public $operadorTipo = null; // int tipo operador
    public $lanHouse = false; // bool se eh lan house
    public $jQuery;
    public $saldoLimite = 0;
    public $objBanner;

    private $_paginasRestritas = array
    (
        "/creditos/funcionario/edita.php",
        "/creditos/funcionario/novo.php",
        "/creditos/funcionario/novo.php",
        "/creditos/alterar_senha.php",
        "/creditos/meu_cadastro.php",
        "/creditos/esqueci_senha.php"
    ); // array com paginas que exibem e/ou dao a possibilidade de alterar informacoes sigilosas (senha, pins etc)

    public function __construct()
    {
        if (validaSessao()) {
            $this->usuarios = unserialize($_SESSION["dist_usuarioGames_ser"]);
            $instUsuarioGames = new UsuarioGames;
            $this->usuarios = $instUsuarioGames->getUsuarioGamesById($this->usuarios->getId());

            if (
                isset($_SESSION['dist_usuarioGamesOperador_ser']) &&
                !is_null($_SESSION['dist_usuarioGamesOperador_ser'])
            ) {
                $this->usuariosOperador = unserialize($_SESSION['dist_usuarioGamesOperador_ser']);

                if (
                    isset($_SESSION['dist_usuarioGamesOperadorTipo_ser']) &&
                    $_SESSION['dist_usuarioGamesOperadorTipo_ser'] == $GLOBALS['USUARIO_GAMES_OPERADOR_TIPOS'][FUNCIONARIO_1]
                ) {
                    $this->operadorTipo = $GLOBALS['USUARIO_GAMES_OPERADOR_TIPOS'][FUNCIONARIO_1];
                } else if (
                    isset($_SESSION['dist_usuarioGamesOperadorTipo_ser']) &&
                    $_SESSION['dist_usuarioGamesOperadorTipo_ser'] == $GLOBALS['USUARIO_GAMES_OPERADOR_TIPOS'][FUNCIONARIO_2]
                ) {
                    $this->operadorTipo = $GLOBALS['USUARIO_GAMES_OPERADOR_TIPOS'][FUNCIONARIO_2];
                }
            } elseif (
                isset($_SESSION['dist_usuarioGames_ser']) &&
                !is_null($_SESSION['dist_usuarioGames_ser'])
            ) {
                $this->lanHouse = true;
            }

            if ($this->usuarios->getDataExpiraSenha()) {
                if ($this->validaSenhaExpirada() && $_SERVER['SCRIPT_NAME'] !== "/creditos/alterar_senha.php") {
                    if ($this->lanHouse) {
                        Util::redirect("/creditos/alterar_senha.php");

                    } else {
                        Util::redirect("/creditos/erro.php?ERRO=2499");

                    }
                }
            }

            $this->saldoLimite = ($this->usuarios->getRiscoClassif() == 1) ?
                number_format($this->usuarios->getPerfilLimite() + $this->usuarios->getPerfilSaldo(), 2, ",", ".") : number_format($this->usuarios->getPerfilSaldo(), 2, ",", ".");

            $GLOBALS['_SESSION']['dist_usuarioGames_ser'] = serialize($this->usuarios);
            $GLOBALS['_SESSION']['usuarioGames.horarioLogin'] = date("U");
            $GLOBALS['_SESSION']['usuarioGames.horarioInatividade'] = date("U");
            $this->jQuery = "/js/jquery.js";
        } else {
            $this->accessDenied();
        }
    }

    public function validaSenhaExpirada()
    {
        $data = explode(" ", $this->usuarios->getDataExpiraSenha());
        $paramDate = Util::getData($data[0], true);
        return (Util::timeSub($paramDate, date("Y-m-d")) <= 0) ? true : false;

    }

    public function setError($fileError, Exception $ex)
    {
        $geraLog = new Log($fileError, array(
            "ERROR: " . $ex->getMessage(),
            "FILE: " . $_SERVER["REQUEST_URI"] . " / " . $ex->getFile(),
            "LINE " . $ex->getLine()
        ));
        return true;
    }

    public function emailReport($page, $error = "")
    {

        if (!checkIP()) //producao
        {
            $to = "wagner@e-prepag.com.br,suporte@e-prepag.com.br";
            $subject = "[PRODUÇÃO] ERROR REPORT";
        } else {
            $to = "wagner@e-prepag.com.br";
            $subject = "[DEV - HOMOLOGAÇÃO] - " . $_SERVER['SERVER_NAME'] . " - ERROR REPORT";
        }

        $body_html = "<strong>Data</strong>: " . date("d/m/Y H:i:s") . ". <br> "
            . "<strong>Tivemos um erro na página:</strong> " . $page;
        if ($error != "") {
            $body_html .= "<br> <strong>Erro</strong>: " . $error;
        }

        return (enviaEmail($to, null, null, $subject, $body_html, null)) ? true : false;

    }

    public function logout($className, $msg = "", $sendMail = false)
    {
        try {
            if ($sendMail)
                $this->emailReport($_SERVER["REQUEST_URI"], $msg);
            throw new Exception($msg);
        } catch (Exception $ex) {
            $this->setError($className, $ex);
        }
        unset($_SESSION);
        header("Location: /creditos/login.php");
    }

    public function accessDenied()
    {
        print "<script>"
            . "alert('Você não tem permissão para acessar essa página.');"
            . "location.href = '/creditos/';"
            . "</script>";
        die;
    }

    public function getBanner($posicao = "Side Bar", $categoria = "Lan House")
    {
        return $this->objBanner->getBannersFromJson($posicao, $categoria);
    }

    /*
        Método que valida se a pagina tem a permissao para abrir o chat externo da empresa mktzap
     */
    public function mktzap()
    {
        return (in_array($GLOBALS['_SERVER']['PHP_SELF'], $this->_paginasRestritas)) ? false : true;
    }

    private function destruct()
    {
        $GLOBALS['_SESSION']['dist_usuarioGames_ser'] = serialize($this->usuarios);
    }
}

function salvar_log_req($pdo, $usuario_id, $blacklist = null)
{
    if ($blacklist === null) {
        $blacklist = array(
            'password',
            'passw',
            'senha',
            'key',
            'pwd',
            'token',
            'authorization',
            'auth',
            'access_token',
            'secret',
            'credit_card',
            'cc',
            'card_number',
            'cvv',
            'ssn',
            'cpf',
            'passMestra',
            'g-recaptcha-response'
        );
    }

    function utf8ize($mixed)
    {
        if (is_array($mixed)) {
            $res = array();
            foreach ($mixed as $k => $v) {
                $res[$k] = utf8ize($v);
            }
            return $res;
        } elseif (is_object($mixed)) {
            $obj = new stdClass();
            foreach ($mixed as $k => $v) {
                $obj->$k = utf8ize($v);
            }
            return $obj;
        } elseif (is_string($mixed)) {
            // remove caracteres inválidos e converte para UTF-8
            return mb_convert_encoding($mixed, 'UTF-8', 'UTF-8');
        } else {
            return $mixed;
        }
    }
    // --- Função auxiliar para mascarar valores recursivamente ---
    function mask_value_recursive($key, $value, $blacklist_lower, $mask = '[FILTERED]', $maxValueLength = 200)
    {
        if (in_array(strtolower($key), $blacklist_lower, true)) {
            return $mask;
        }
        if (is_array($value)) {
            $out = array();
            foreach ($value as $k => $v) {
                $out[$k] = mask_value_recursive($k, $v, $blacklist_lower, $mask, $maxValueLength);
            }
            return $out;
        }
        if (is_object($value)) {
            $value = json_decode(json_encode($value), true);
            if (is_array($value)) {
                return mask_value_recursive('', $value, $blacklist_lower, $mask, $maxValueLength);
            }
        }
        if (is_string($value)) {
            if ($maxValueLength > 0 && mb_strlen($value, 'UTF-8') > $maxValueLength) {
                return mb_substr($value, 0, $maxValueLength, 'UTF-8') . '...';
            }
            return $value;
        }
        return $value;
    }

    // --- Preparar blacklist lower ---
    $blacklist_lower = array_map('strtolower', $blacklist);

    // --- Capturar informações ---
    $rota = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    $method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'CLI';
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
    $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

    // IP real
    $ipKeys = array(
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR'
    );
    $ip = '0.0.0.0';
    foreach ($ipKeys as $k) {
        if (!empty($_SERVER[$k])) {
            $ipList = explode(',', $_SERVER[$k]);
            $ip = trim($ipList[0]);
            break;
        }
    }

    // POST / JSON
    $postData = array();
    $contentType = '';
    if (isset($_SERVER['CONTENT_TYPE'])) $contentType = strtolower($_SERVER['CONTENT_TYPE']);
    elseif (isset($_SERVER['HTTP_CONTENT_TYPE'])) $contentType = strtolower($_SERVER['HTTP_CONTENT_TYPE']);

    if ($method === 'POST' || $method === 'PUT' || $method === 'PATCH') {
        if (strpos($contentType, 'application/json') !== false) {
            $raw = file_get_contents('php://input');
            $json = json_decode($raw, true);
            if (is_array($json)) {
                foreach ($json as $k => $v) {
                    $postData[$k] = mask_value_recursive($k, $v, $blacklist_lower);
                }
            }
        } else {
            if (!empty($_POST)) {
                foreach ($_POST as $k => $v) {
                    $postData[$k] = mask_value_recursive($k, $v, $blacklist_lower);
                }
            } else {
                $raw = file_get_contents('php://input');
                $parsed = array();
                parse_str($raw, $parsed);
                foreach ($parsed as $k => $v) {
                    $postData[$k] = mask_value_recursive($k, $v, $blacklist_lower);
                }
            }
        }
    }

    $dados_extras_array = array(
        'post' => $postData,
        'method' => $method,
        'host' => $host,
        'referer' => $referer
    );

    // Garante UTF-8
    $dados_extras_array = utf8ize($dados_extras_array);

    $dados_extras = json_encode($dados_extras_array, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    $sql = "INSERT INTO usuario_logs_acoes (
                    usuario_id, tipo_usuario, data_hora_registro, ip_usuario, caminho_arquivo, dados_request
                ) VALUES (
                    :usuario_id, :tipo_usuario, :data_hora_registro, :ip_usuario, :caminho_arquivo, :dados_request
                )";

    $insertParams = [
        'usuario_id' => $usuario_id,
        'tipo_usuario' => 1,
        'data_hora_registro' => date('Y-m-d H:i:s'),
        'ip_usuario' => $ip,
        'caminho_arquivo' => $rota,
        'dados_request' => $dados_extras
    ];

    $stmt = $pdo->prepare($sql);

    $stmt->execute($insertParams);
}
