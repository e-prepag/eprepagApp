<?php
require_once __DIR__ . "/../../../db/connect.php";
require_once __DIR__ . "/../../../db/ConnectionPDO.php";
function calcularTempoBloqueio($tentativas)
{
    switch (true) {
        case ($tentativas <= 2):
            return 0;
        case ($tentativas == 3):
            return 30;
        case ($tentativas == 4):
            return 60;
        case ($tentativas == 5):
            return 3 * 60;
        case ($tentativas == 6):
            return 10 * 60;
        case ($tentativas == 7):
            return 60 * 60;
        case ($tentativas >= 8 && $tentativas < 10):
            return 2 * 60 * 60;
        default:
            return 24 * 60 * 60;
    }
}

function bloquearAcesso($tempo)
{
    ?>
    <div id="modal-token" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content text-left">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><?=LANG_BLOCKED?></h4>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger" role="alert">
                        <h5><?=LANG_PLEASE_WAIT?> <span id="timer">00m 00s</span> <?=LANG_TRY_AGAIN?>
                            <br>
                            <?=LANG_FORGOT_PASSWORD?>
                        </h5>
                    </div>
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?=LANG_CLOSE_BTN?></button>
                </div>
                <div class="modal-footer" style="font-size: 10.5px;">

                    <span class="decoration-none txt-cinza"><em><?=LANG_LOGIN_PROBLEM?></em></span>
                    <a class="decoration-none txt-cinza" id="faca-cadastro" target="_blank" href="/game/suporte.php"><em><?=LANG_CONTACT_SUPPORT?>.</em></a>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        var tempo = <?php echo $tempo ?: 0; ?>;

        var timerElement = document.getElementById('timer');

        function atualizarTimer() {
            var dias = Math.floor(tempo / (24 * 60 * 60));
            var horas = Math.floor((tempo % (24 * 60 * 60)) / (60 * 60));
            var minutos = Math.floor((tempo % (60 * 60)) / 60);
            var segundos = tempo % 60;

            // Formatação com zeros à esquerda
            dias = dias < 10 ? '0' + dias : dias;
            horas = horas < 10 ? '0' + horas : horas;
            minutos = minutos < 10 ? '0' + minutos : minutos;
            segundos = segundos < 10 ? '0' + segundos : segundos;

            var textDia =  dias > 0 ? dias + ' dias ' : '';
            var textHora = horas > 0 ? horas + 'h ' : '';
            timerElement.textContent = textDia + textHora + minutos + 'm ' + segundos + 's';

            if (tempo > 0) {
                tempo--;
            } else {
                clearInterval(intervalo);
                document.querySelector('[data-dismiss="modal"]').click();
            }
        }

        atualizarTimer();
        var intervalo = setInterval(atualizarTimer, 1000);
    </script>
    <?php
    exit();
}

function retornaQtde()
{
    $conexao = ConnectionPDO::getConnection()->getLink();
    $sql = "SELECT * FROM bloqueia_login_usuario WHERE ip = :IP;";
    $query = $conexao->prepare($sql);
    $query->bindValue(":IP", $_SERVER["REMOTE_ADDR"]);
    $query->execute();
    $resultRow = $query->fetch(PDO::FETCH_ASSOC);
    return $resultRow ?: null;
}

function verificarBloqueio()
{
    $dados = retornaQtde();
    if (!$dados) {
        return false;
    }

    $agora = time();
    if (!empty($dados['expiracao']) && strtotime($dados['expiracao']) > $agora) {
        return strtotime($dados['expiracao']) - $agora;
    }

    return false;
}

function registrarTentativaFalha($login_verificacao)
{
    $ip = $_SERVER["REMOTE_ADDR"];
    $conexao = ConnectionPDO::getConnection()->getLink();
    $dados = retornaQtde();
    $agora = date("Y-m-d H:i:s");
    $limite_tempo = calcularTempoBloqueio($dados['qtde']) + 5 * 60;

    if ($dados) {
        $data_requisicao = strtotime($dados['data_requisicao']);
        if ((time() - $data_requisicao) > $limite_tempo) {
            $qtde = 1;
        } else {
            $qtde = $dados['qtde'] + 1;
        }

        $tempo_bloqueio = calcularTempoBloqueio($qtde);
        $expiracao = $tempo_bloqueio ? date("Y-m-d H:i:s", time() + $tempo_bloqueio) : null;

        $stmt = $conexao->prepare("
         		UPDATE bloqueia_login_usuario 
            	SET qtde = :QTDE, data_requisicao = :ULT, expiracao = :BLOQ 
            	WHERE ip = :IP;
        	");
        $stmt->bindValue(":QTDE", $qtde);
        $stmt->bindValue(":ULT", $agora);
        $stmt->bindValue(":BLOQ", $expiracao);
        $stmt->bindValue(":IP", $ip);
        $stmt->execute();
    } else {
        $stmt = $conexao->prepare("
            	INSERT INTO bloqueia_login_usuario (ip, data_requisicao, qtde, login, senha, visualizacao, expiracao)
            	VALUES (:IP, CURRENT_TIMESTAMP, 1, :LOGIN, :SENHA, 'S', NULL);
        	");
        $stmt->bindValue(":IP", $ip);
        $stmt->bindValue(":LOGIN", strip_tags(htmlentities($login_verificacao)));
        $stmt->bindValue(":SENHA", "");
        $stmt->execute();
    }
}

function descript_login($login_cript, $senha_cript, &$senha, &$login)
{
    $privateKeyPem = file_get_contents('/www/ssl/private.pem');

    $privateKey = openssl_pkey_get_private($privateKeyPem);

    if (!$privateKey) {
        return 0;
    }

    $okSenha = openssl_private_decrypt(base64_decode($senha_cript), $senha, $privateKey, OPENSSL_PKCS1_OAEP_PADDING);
    $okUsuario = openssl_private_decrypt(base64_decode($login_cript), $login, $privateKey, OPENSSL_PKCS1_OAEP_PADDING);
    if (!$okSenha || !$okUsuario) {
        return -1;
    }
    return 1;
}

function cript_login($login, $senha, &$login_cript, &$senha_cript)
{
    $publicKey = file_get_contents("/www/ssl/public.pem");

    $senhaCriptografada = null;
    openssl_public_encrypt(
        $senha,
        $senhaCriptografada,
        $publicKey,
        OPENSSL_PKCS1_OAEP_PADDING
    );

    $userCriptografado = null;
    openssl_public_encrypt(
        $login,
        $userCriptografado,
        $publicKey,
        OPENSSL_PKCS1_OAEP_PADDING
    );

    $senha_cript = base64_encode($senhaCriptografada);
    $login_cript = base64_encode($userCriptografado);
}