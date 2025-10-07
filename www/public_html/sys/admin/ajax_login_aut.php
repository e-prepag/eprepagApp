<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);  // Exibe todos os tipos de erros
header("Content-Type: text/html; charset=ISO-8859-1", true);
require_once __DIR__ . "/../../../includes/constantes.php";
require_once __DIR__ . "/../../../db/connect.php";
require_once __DIR__ . "/../../../db/ConnectionPDO.php";
require_once DIR_CLASS . "util/Util.class.php";
require_once RAIZ_DO_PROJETO . "includes/gamer/chave.php";
require_once RAIZ_DO_PROJETO . "includes/gamer/AES.class.php";
require_once __DIR__ . "/../../../libs/PHPGangsta/GoogleAuthenticator.php";
require_once $raiz_do_projeto . "public_html/sys/includes/configuracao.php";
require_once $raiz_do_projeto . "public_html/sys/includes/languages.php";
require_once "../../../includes/load_dotenv.php";
require_once "../includes/funcoes_login.php";

session_start();

try {
    $pdo = ConnectionPDO::getConnection()->getLink();

    if (Util::isAjaxRequest()) {

        $recaptcha = $_REQUEST["g-recaptcha-response"];
        if ($recaptcha != "") {

            $tokenInfo = [
                "secret" => getenv("RECAPTCHA_SECRET_KEY"),
                "response" => $recaptcha,
                "remoteip" => $_SERVER["REMOTE_ADDR"],
            ];

            $recaptcha_curl = curl_init();

            curl_setopt_array($recaptcha_curl, [
                CURLOPT_URL => getenv("RECAPTCHA_URL"),
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POSTFIELDS => http_build_query($tokenInfo),
            ]);

            $dadosT = curl_exec($recaptcha_curl);

            $inforCurl = curl_getinfo($recaptcha_curl);

            $retorno = json_decode($dadosT, true);

            curl_close($recaptcha_curl);

            if ($retorno["success"] != true || (isset($retorno["error-codes"]) && !empty($retorno["error-codes"]))) {
                echo '<script>window.location.href = "index.php?Invalido=1";</script>';
                exit;
            }
        } else {
            echo '<script>window.location.href = "index.php?Invalido=1";</script>';
            exit;
        }

        $tempoBloqueio = verificarBloqueio();
        if ($tempoBloqueio) {
            session_destroy();
            bloquearAcesso($tempoBloqueio);
        }

        $_SESSION['RECAPTCHA_TRUE'] = true;

        $privateKeyPem = file_get_contents('/www/ssl/private.pem');

        $privateKey = openssl_pkey_get_private($privateKeyPem);

        if (!$privateKey) {
            echo "<script>alert('Error');</script>";
            exit;
        }

        $senha_decript = null;
        $user_decript = null;
        $okDecript = descript_login($_POST['user'], $_POST['passw'], $senha_decript, $user_decript);
        if ($okDecript != 1) {
            echo "<script>alert('Error');</script>";
            exit;
        }

        $chave256bits = new Chave();
        $aes = new AES($chave256bits->retornaChavePub());
        $senha = base64_encode($aes->encrypt($senha_decript));
        $login = strtoupper(trim($user_decript));

        $sql = "SELECT id, chave_autenticador, sem_aut_data FROM usuarios WHERE shn_login = ? AND shn_password = ? 
        AND ((tipo_acesso='AD') OR (tipo_acesso='DT') OR (tipo_acesso='SV') OR (tipo_acesso='AT') OR (tipo_acesso='PU') OR (tipo_acesso='US'))";

        $con = ConnectionPDO::getConnection();
        $pdo = $con->getLink();

        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($login, $senha));
        $fetch = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($stmt->rowCount() > 0) {

            $senha_base64 = null;
            $user_base64 = null;
            cript_login($user_decript, $senha_decript, $user_base64, $senha_base64);

            $ret = true;
            if (empty($fetch['chave_autenticador'])) {

                modal_criar_token($fetch, $senha_base64, $user_base64);
                exit;
            }

            if (checkDevice($fetch['id'], $pdo)) {
                logar_direto($senha_base64, $user_base64);
                exit;
            }

            modal_token($senha_base64, $user_base64);
            exit;
        } else {
            registrarTentativaFalha($login);
            echo "<script>alert('" . LANG_USER_PASS_INVALID . "');</script>";
        }
    }
} catch (PDOException $e) {
    echo "Connection error";
}

function modal_criar_token($fetch, $senha, $login)
{
    $dataUltimoAcesso = new DateTime($fetch['sem_aut_data']);
    $dataHoje = new DateTime();

    $diasRestantes = 28 - $dataUltimoAcesso->diff($dataHoje)->days;

    $btn_recusar = true;
    if ($diasRestantes > 0) {
        $mensagemAuth = sprintf(LANG_AUTHENTICATOR_REGISTER_NOT_REQUIRED_YET, $diasRestantes);
    } else {
        $mensagemAuth = LANG_AUTHENTICATOR_REGISTER_EXPIRED;
        $btn_recusar = false;
    }

    $user_html = htmlspecialchars($login, ENT_QUOTES | ENT_SUBSTITUTE, 'ISO-8859-1');
    $senha_html = htmlspecialchars($senha, ENT_QUOTES | ENT_SUBSTITUTE, 'ISO-8859-1');

?>
    <div id="modal-token" class="modal fade txt-preto" role="dialog">
        <div class="modal-dialog modal-md">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header text-left">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title text-left" style="word-break: break-word;"><?= $mensagemAuth ?></h4>
                </div>
                <div class="modal-body espacamento" style="display: flex;">
                    <div class="dislineblock" style="margin-right: 25px;">
                        <button style="font-weight: bold; font-style: italic; min-width: 55px;" class="pull-right btn btn-success"
                            onclick="criar_autenticador()"><?php echo $btn_recusar ? LANG_INTEGRATION_YES : LANG_CONFIGURE; ?></button>
                    </div>
                    <?php if ($btn_recusar) { ?>
                        <div class="dislineblock">
                            <form id="redir" method="POST" action="index2.php">
                                <input type="hidden" name="user" value="<?= $user_html ?>">
                                <input type="hidden" name="passw" value="<?= $senha_html ?>">
                                <button style="font-weight: bold; font-style: italic; width: 55px;" class="pull-right btn btn-info" type="submit"><?= LANG_NO ?></button>
                            </form>
                        </div>
                    <?php } ?>
                </div>
                <div class="modal-footer">
                    <span class="decoration-none txt-cinza"><em><?= LANG_PROBLEMS_WITH_AUTHENTICATOR ?></em></span>
                    <a class="decoration-none txt-cinza" id="faca-cadastro" target="_blank" href="/"><em><?= LANG_CONTACT_SUPPORT ?>.</em></a>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        function criar_autenticador() {
            $("#modal-token").modal('hide');
            setTimeout(function() {
                $.ajax({
                    url: 'ajax_cria_aut.php',
                    type: 'POST',
                    data: {
                        user: '<?= $login ?>',
                        passw: '<?= $senha ?>'
                    },
                    success: function(response) {
                        $("#recebe-modal").html(response);
                        // Supondo que o modal tenha id #modalLoginResult
                        if ($("#modal-autenticador").length) {
                            $("#modal-autenticador").modal('show');
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('<?= LANG_ERROR_PROCESSING_LOGIN ?>');
                    }
                });
            }, 300);
        }
    </script>
<?php
}

function modal_token($senha, $login)
{
?>
    <div id="modal-token" class="modal fade txt-preto" role="dialog">
        <div class="modal-dialog modal-sm">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title text-left"><?= LANG_ENTER_AUT_TOKEN ?></h4>
                </div>
                <div class="modal-body espacamento">
                    <form action="index2.php" method="POST">
                        <div class="form-group text-left">
                            <label for="token">Token:</label>
                            <input type="hidden" name="user" value="<?= htmlspecialchars($login, ENT_QUOTES | ENT_SUBSTITUTE, 'ISO-8859-1') ?>">
                            <input type="hidden" name="passw" value="<?= htmlspecialchars($senha, ENT_QUOTES | ENT_SUBSTITUTE, 'ISO-8859-1') ?>">
                            <input type="text" class="form-control" id="token" name="token" placeholder="Token">
                            <div style="margin: 7px 0px; display: flex; align-items: center; gap: 3px;">
                                <label for="salvarDispositivo" style="margin: 0; font-weight: normal;"><?= LANG_REMEMBER_DEVICE ?>:</label>
                                <input type="checkbox" id="salvarDispositivo" name="salvarDispositivo" style="margin: 0;"
                                    value="sim">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success btn-block" id="logarToken">Login</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <span class="decoration-none txt-cinza"><em><?= LANG_PROBLEMS_WITH_AUTHENTICATOR ?></em></span>
                    <a class="decoration-none txt-cinza" id="faca-cadastro" target="_blank" href="/"><em><?= LANG_CONTACT_SUPPORT ?>.</em></a>
                </div>
            </div>
        </div>
    </div>
<?php
}

function logar_direto($senha, $login)
{
?>
    <form id="redir" method="POST" action="index2.php">
        <input type="hidden" name="user" value="<?= htmlspecialchars($login, ENT_QUOTES | ENT_SUBSTITUTE, 'ISO-8859-1') ?>">
        <input type="hidden" name="passw" value="<?= htmlspecialchars($senha, ENT_QUOTES | ENT_SUBSTITUTE, 'ISO-8859-1') ?>">
    </form>

    <script>
        document.getElementById("redir").submit();
    </script>
<?php
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
?>