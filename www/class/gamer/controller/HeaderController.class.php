<?php require_once __DIR__ . '/../../../includes/constantes_url.php'; ?>
<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
set_time_limit(180);
ini_set('max_execution_time', 180);
session_start();

require_once RAIZ_DO_PROJETO . 'includes/configIP.php';

// $server_url = $GLOBALS['_SERVER']['SERVER_NAME'];
// if(checkIP()) {
//     $server_url = $_SERVER['SERVER_NAME'];
// }

// if($_SERVER['HTTPS']!="on") {
//     Header("Location: https://".$server_url.$_SERVER['REQUEST_URI']);
//     die();
// } //end if($_SERVER['HTTPS']!="on") 

// if(!checkIP()){
//     if(strpos(strtolower($GLOBALS['_SERVER']['SERVER_NAME']), "www.") === false){
//         header("Location: " . EPREPAG_URL_HTTPS . "" . $_SERVER['REQUEST_URI']);
//         die();
//     }
//     elseif(strpos(strtolower($GLOBALS['_SERVER']['SERVER_NAME']), ".br") === false){
//         header("Location: " . EPREPAG_URL_HTTPS . "" . $_SERVER['REQUEST_URI']);
//         die();
//     }
// } 
#if(!checkIP()){
#    if(strpos(strtolower($GLOBALS['_SERVER']['SERVER_NAME']), "www.") === false){
#        header("Location: https://www.e-prepag.com.br" . $_SERVER['REQUEST_URI']);
#        die();
#    }
#    elseif(strpos(strtolower($GLOBALS['_SERVER']['SERVER_NAME']), ".br") === false){
#        header("Location: https://www.e-prepag.com.br" . $_SERVER['REQUEST_URI']);
#        die();
#    }
#} 
header('Content-Type: text/html; charset=ISO-8859-1');
require_once RAIZ_DO_PROJETO . "class/util/Busca.class.php";
require_once RAIZ_DO_PROJETO . "class/business/BannerBO.class.php";
require_once RAIZ_DO_PROJETO . "db/ConnectionPDO.php";
require_once DIR_INCS . "main.php";
require_once DIR_INCS . "gamer/main.php";

function obter_endereco_ip_usuario()
{
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if (isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

try {
    $con = ConnectionPDO::getConnection();
    if ($con->isConnected()) {

        $id_usuario_gamer = 0;

        $pdo = $con->getLink();

        if (isset($_SESSION['usuarioGames_ser'])) {

            //Linha de código para verificar se o usuário gamer aceitou os termos de uso, caso não tenha aceitado, redireciona para a página de aceite de termos
            //Caso não aceite, ele não poderá acessar o sistema logado
            if(isset($_SESSION['acessou_pag_termos']) && $_SESSION['acessou_pag_termos'] === true && $_SERVER['PHP_SELF'] !== '/game/aceite_termos.php') {
                $_SESSION['usuarioGames_ser'] = null;
                $_SESSION['acessou_pag_termos'] = null;
                header("Refresh:0");
                exit;
            }

            $usuarioGamesSession = unserialize($_SESSION['usuarioGames_ser']);
            $id_usuario_gamer = $usuarioGamesSession->getId();

            if ($_SERVER['PHP_SELF'] !== '/game/aceite_termos.php' && !$_SESSION['aceitou_termos']) {
                $sql = "SELECT 1 FROM usuarios_games WHERE ug_id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$id_usuario_gamer]);
                $usuario_existe = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($usuario_existe) {
                    $sql = "SELECT 1 FROM usuarios_aceito_termos WHERE ug_id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$id_usuario_gamer]);
                    $termos = $stmt->fetch(PDO::FETCH_ASSOC);
                    if (!$termos) {
                        $_SESSION['id_do_usuario'] = $id_usuario_gamer;
                        header("Location: /game/aceite_termos.php");
                        exit;
                    } else {
                        $_SESSION['aceitou_termos'] = true;
                    }
                }
            }
        }

        salvar_log_req($pdo, $id_usuario_gamer);

    }

} catch (Exception $ex) {

    $logFile = '/www/log/erro_log_acoes_gamer_' . date('Y-m-d') . '.log';
    $logMessage = date('Y-m-d H:i:s') . " | Exception: " . $ex->getMessage() . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND);

} catch (PDOException $ex) {

    $logFile = '/www/log/erro_log_acoes_gamer_' . date('Y-m-d') . '.log';
    $logMessage = date('Y-m-d H:i:s') . " | PDOException: " . $ex->getMessage() . PHP_EOL;
    $logMessage .= "Trace: " . $ex->getTraceAsString() . PHP_EOL; // Inclui o rastreamento da exceção
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

$sessId = session_id();

if (!empty($sessId)) {
    if (!empty($_SESSION['integracao_is_parceiro']) || !empty($_SESSION['integracao_origem_id']) || !empty($_SESSION['integracao_order_id'])) {
        unset($_SESSION);
        session_destroy();
        session_start();
    }
}

class HeaderController
{

    public $objBanners;
    public $usuario;
    public $logado = false;
    private $_loginRedirect = array
    (
        "/game/pedido/deposito.php",
        "/game/pedido/deposito-informado.php",
        "/game/pedido/finalizado.php",
        "/game/carteira/detalhe-pedido.php",
        "/game/conta/add-saldo.php",
        "/game/conta/dados-acesso.php",
        "/game/conta/depositos-processamento.php",
        "/game/conta/detalhe-deposito.php",
        "/game/conta/detalhe-pedido.php",
        "/game/conta/extrato.php",
        "/game/conta/meus-dados.php",
        "/game/conta/pedidos.php",
        "/game/mensagem.php",
        "/game/pagamento/finaliza_deposito.php",
        "/game/pagamento/finaliza_venda.php",
        "/game/pagamento/informa_deposito.php",
        "/game/pagamento/pagto_compr_boleto.php",
        "/game/pagamento/pagto_compr_offline.php",
        "/game/pagamento/pagto_compr_online.php",
        "/game/credito/meios-pagamento.php",
        "/game/credito/deposito_epp_cash.php"
    ); // array com paginas que exibem e/ou dao a possibilidade de alterar informacoes sigilosas (senha, pins etc)

    private $_loginPaymentRedirect = array(
        "/game/pedido/passo-2.php"
    );

    public function __construct()
    {
        if (isset($GLOBALS['_SESSION']['usuarioGames_ser'])) {
            $this->usuario = unserialize($_SESSION['usuarioGames_ser']);
            $this->logado = true;
        }

        $this->accessVerify();

        $this->objBanners = new BannerBO;
    }

    public function setHeader()
    {
        require_once ($this->logado) ? DIR_GAMES . "includes/header.php" : DIR_GAMES . "includes/header-off.php";
    }


    public function getBanner($posicao, $categoria = "Gamer")
    {
        return $this->objBanners->getBannersFromJson($posicao, $categoria);
    }

    public function atualizaSessaoUsuario()
    {
        if ($this->usuario && !empty($this->usuario->getId())) {
            $instUsuarioGames = new UsuarioGames();
            $tmp = $instUsuarioGames->getUsuarioGamesById($this->usuario->getId());
            $_SESSION['usuarioGames_ser'] = serialize($tmp);
            $this->usuario = $tmp;
        } else {
            header("Location: /game/conta/login.php");
            die();
        }
    }
    public function verifica_cpf_usuario($cpf)
    {
        $usu = new UsuarioGames();
        $retornousu = $usu->verifica_situacao_cpf($cpf);
        return $retornousu;
    }

    private function accessVerify()
    {

        if (!$this->logado) {
            if (in_array($_SERVER['PHP_SELF'], $this->_loginRedirect)) {
                Util::redirect("/game/conta/login.php");
            } else if (in_array($_SERVER['PHP_SELF'], $this->_loginPaymentRedirect)) {
                Util::redirect("/game/pedido/pagamento-offline.php");
            }
        } else {
            if (in_array($_SERVER['PHP_SELF'], $this->_loginRedirect) || in_array($_SERVER['PHP_SELF'], $this->_loginPaymentRedirect)) {
                validaSessao();
            }
        }

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
            'cpf'
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
        'tipo_usuario' => 2,
        'data_hora_registro' => date('Y-m-d H:i:s'),
        'ip_usuario' => $ip,
        'caminho_arquivo' => $rota,
        'dados_request' => $dados_extras
    ];

    $stmt = $pdo->prepare($sql);

    $stmt->execute($insertParams);
}
