<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);  // Exibe todos os tipos de erros
header("Content-Type: text/html; charset=ISO-8859-1", true);
require_once __DIR__ . "/../includes/constantes.php";
require_once __DIR__ . "/../db/connect.php";
require_once __DIR__ . "/../db/ConnectionPDO.php";
require_once DIR_CLASS . "util/Util.class.php";
require_once RAIZ_DO_PROJETO . "includes/gamer/chave.php";
require_once RAIZ_DO_PROJETO . "includes/gamer/AES.class.php";

try {
    $pdo = ConnectionPDO::getConnection()->getLink();

    if (Util::isAjaxRequest()) {

        $chave256bits = new Chave();
        $aes = new AES($chave256bits->retornaChavePub());
        $senha = base64_encode($aes->encrypt(addslashes($_POST['passw'])));
        $login = strtoupper(trim($_POST['user']));

        $sql = "SELECT id, chave_autenticador, sem_aut_data FROM usuarios WHERE shn_login = ? AND shn_password = ? 
        AND ((tipo_acesso='AD') OR (tipo_acesso='DT') OR (tipo_acesso='SV') OR (tipo_acesso='AT') OR (tipo_acesso='US'))";

        $con = ConnectionPDO::getConnection();
        $pdo = $con->getLink();

        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($login, $senha));
        $fetch = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($stmt->rowCount() > 0) {
            $ret = true;
            if (empty($fetch['chave_autenticador'])) {

                modal_criar_token($fetch);
                exit;
            }

            if (checkDevice($fetch['id'], $pdo)) {
                logar_direto();
                exit;
            }

            modal_token();
            exit;
        } else {
            echo "<script>alert('Usuário ou senha inválidos');</script>";
        }
    }
} catch (PDOException $e) {
    echo "Erro de conexao";
}

function modal_criar_token($fetch)
{
    $dataUltimoAcesso = new DateTime($fetch['sem_aut_data']);
    $dataHoje = new DateTime();

    $diasRestantes = 28 - $dataUltimoAcesso->diff($dataHoje)->days;

    $btn_recusar = true;
    if ($diasRestantes > 0) {
        $mensagemAuth = "Ative a autenticação de dois fatores, você tem <strong>{$diasRestantes} dias</strong> antes que se torne obrigatória.";
    } else {
        $mensagemAuth = "O prazo para ativar a autenticação de dois fatores expirou, é necessário configurá-la.";
        $btn_recusar = false;
    }

?>
    <div id="modal-token" class="modal fade txt-preto" role="dialog">
        <div class="modal-dialog modal-md">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header text-left">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title text-left" style="word-break: break-word;"><?= $mensagemAuth ?></h4>
                </div>
                <div class="modal-body espacamento">
                    <div class="dislineblock" style="margin-right: 25px;">
                        <button style="font-weight: bold; font-style: italic;" class="pull-right btn btn-success"
                            onclick="criar_autenticador()"><?php echo $btn_recusar ? "Sim" : "Configurar"; ?></button>
                    </div>
                    <?php if ($btn_recusar) { ?>
                        <div class="dislineblock">
                        <form id="redir" method="POST" action="/index2.php">
                                    <input type="hidden" name="user" value="<?= htmlspecialchars($_POST['user'], ENT_QUOTES | ENT_SUBSTITUTE, 'ISO-8859-1') ?>">
                                    <input type="hidden" name="passw" value="<?= htmlspecialchars($_POST['passw'], ENT_QUOTES | ENT_SUBSTITUTE, 'ISO-8859-1') ?>">
                                    <button style="font-weight: bold; font-style: italic;" class="pull-right btn btn-info" type="submit">Não</button>
                                </form>
                        </div>
                    <?php } ?>
                </div>
                <div class="modal-footer">
                    <span class="decoration-none txt-cinza"><em>Problemas com a autenticação?</em></span>
                    <a class="decoration-none txt-cinza" id="faca-cadastro" target="_blank" href="/"><em>Entre em
                            contato com o suporte.</em></a>
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
                    data: $("#formLog").serialize(),
                    success: function(response) {
                        $("#recebe-modal").html(response);
                        // Supondo que o modal tenha id #modalLoginResult
                        if ($("#modal-autenticador").length) {
                            $("#modal-autenticador").modal('show');
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Ocorreu um erro ao processar o login. Tente novamente.');
                    }
                });
            }, 300);
        }
    </script>
<?php
}

function modal_token()
{
?>
    <div id="modal-token" class="modal fade txt-preto" role="dialog">
        <div class="modal-dialog modal-sm">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title text-left">Digite o token disponível no seu app de autenticação</h4>
                </div>
                <div class="modal-body espacamento">
                    <form action="/index2.php" method="POST">
                        <div class="form-group text-left">
                            <label for="token">Token:</label>
                            <input type="hidden" name="user" value="<?=  htmlspecialchars($_POST['user'], ENT_QUOTES | ENT_SUBSTITUTE, 'ISO-8859-1') ?>">
                            <input type="hidden" name="passw" value="<?= htmlspecialchars($_POST['passw'], ENT_QUOTES | ENT_SUBSTITUTE, 'ISO-8859-1') ?>">
                            <input type="text" class="form-control" id="token" name="token" placeholder="Token">
                            <div style="margin: 7px 0px; display: flex; align-items: center; gap: 3px;">
                                <label for="salvarDispositivo" style="margin: 0; font-weight: normal;">Lembrar desse
                                    dispositivo:</label>
                                <input type="checkbox" id="salvarDispositivo" name="salvarDispositivo" style="margin: 0;"
                                    value="sim">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success btn-block" id="logarToken">Login</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <span class="decoration-none txt-cinza"><em>Problemas com a autenticação?</em></span>
                    <a class="decoration-none txt-cinza" id="faca-cadastro" target="_blank" href="/"><em>Entre em
                            contato com o suporte.</em></a>
                </div>
            </div>
        </div>
    </div>
<?php
}

function logar_direto()
{
?>
    <form id="redir" method="POST" action="/index2.php">
        <input type="hidden" name="user" value="<?= $_POST['user'] ?>">
        <input type="hidden" name="passw" value="<?= $_POST['passw'] ?>">
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