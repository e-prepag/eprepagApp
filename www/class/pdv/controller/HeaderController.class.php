<?php require_once __DIR__ . '/../../../includes/constantes_url.php'; ?>
<?php
/**
 * Classe para as regras de negocio das vendas
 *
 * @author Diego
 * @email diego.gomes@e-prepag.com.br
 * @date 16-07-2015
 */
@require_once RAIZ_DO_PROJETO . "includes/main.php";
@require_once RAIZ_DO_PROJETO . "includes/pdv/main.php";
@require_once RAIZ_DO_PROJETO . "includes/pdv/corte_classPrincipal.php"; //corte_constantes
@require_once RAIZ_DO_PROJETO . "includes/inc_register_globals.php";
@require_once RAIZ_DO_PROJETO . "includes/pdv/captura_inc.php";
require_once RAIZ_DO_PROJETO . "class/util/Log.class.php";
@require_once RAIZ_DO_PROJETO . "class/pdv/classOperadorGamesUsuario.php";
@require_once RAIZ_DO_PROJETO . 'includes/functions.php';
@require_once RAIZ_DO_PROJETO . "includes/configuracao.inc";
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

        $sql = "INSERT INTO usuario_logs_acoes (
                    usuario_id, tipo_usuario, data_hora_registro, ip_usuario, caminho_arquivo
                ) VALUES (
                    :usuario_id, :tipo_usuario, :data_hora_registro, :ip_usuario, :caminho_arquivo
                )";

        $insertParams = [
            'usuario_id' => $id_usuario_gamer,
            'tipo_usuario' => 1,
            'data_hora_registro' => date('Y-m-d H:i:s'),
            'ip_usuario' => obter_endereco_ip_usuario(),
            'caminho_arquivo' => $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']
        ];

        $stmt = $pdo->prepare($sql);

        $stmt->execute($insertParams);

    }

} catch (Exception $ex) {

    $logFile = '/www/log/erro_log_acoes_pdv_' . date('Y-m-d') . '.log';
    $logMessage = date('Y-m-d H:i:s') . " | Exception: " . $ex->getMessage() . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND);

} catch (PDOException $ex) {

    $logFile = '/www/log/erro_log_acoes_pdv_' . date('Y-m-d') . '.log';
    $logMessage = date('Y-m-d H:i:s') . " | PDOException: " . $ex->getMessage() . PHP_EOL;
    $logMessage .= "Trace: " . $ex->getTraceAsString() . PHP_EOL; // Inclui o rastreamento da exceção
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

$server_url = "" . EPREPAG_URL . "";
if (checkIP()) {
    $server_url = $_SERVER['SERVER_NAME'];
}

if ($_SERVER['HTTPS'] != "on") {
    redirect("https://" . $server_url . $_SERVER['REQUEST_URI']);
    die();
} //end if($_SERVER['HTTPS']!="on") 

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