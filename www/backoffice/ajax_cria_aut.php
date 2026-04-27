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
require_once __DIR__ . "/../class/GoogleAutenticator.php";
require_once "/www/class/classSecureEncryption.php";

session_start();

$ga = new classGoogleAutenticator();

try {
    $pdo = ConnectionPDO::getConnection()->getLink();

    if (Util::isAjaxRequest()) {

        $login = strtoupper(trim((string)($_POST['user'] ?? "")));

        $sql = "SELECT id, chave_autenticador, shn_password FROM usuarios WHERE shn_login = ?
        AND ((tipo_acesso='AD') OR (tipo_acesso='DT') OR (tipo_acesso='SV') OR (tipo_acesso='AT') OR (tipo_acesso='US'))";

        $con = ConnectionPDO::getConnection();
        $pdo = $con->getLink();

        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($login));
        $fetch = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($stmt && $stmt->rowCount() > 0) {

            $bcrypt = new SecureEncryption();

            if (!$bcrypt->verifyPassword((string)($_POST['passw'] ?? ""), (string)($fetch['shn_password'] ?? ""))) {
                $msg = 'Senha incorreta!';
            } else if (empty($fetch['chave_autenticador'])) {
                if (!isset($_SESSION['secret']) || !$_SESSION['secret']) {
                    $secret = $ga->createSecret();
                    $_SESSION['secret'] = $secret;
                } else {
                    $secret = $_SESSION['secret'];

                    if (isset($_SESSION['id_do_usuario']) && $_SESSION['id_do_usuario'] == $fetch['id']) {
                        if ($ga->verifyCode($secret, (string)($_POST['token'] ?? ""), 2)) {
                            $sql = "UPDATE usuarios SET chave_autenticador = ? WHERE id = ?";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute([$secret, $fetch['id']]);

                            // Verifica se alguma linha foi afetada
                            if ($stmt->rowCount() > 0) {
                                $_SESSION['secret'] = "";
?>
                                <form id="redir" method="POST" action="/index2.php">
                                    <input type="hidden" name="user" value="<?= htmlspecialchars((string)($_POST['user'] ?? ""), ENT_QUOTES | ENT_SUBSTITUTE, 'ISO-8859-1') ?>">
                                    <input type="hidden" name="passw" value="<?= htmlspecialchars((string)($_POST['passw'] ?? ""), ENT_QUOTES | ENT_SUBSTITUTE, 'ISO-8859-1') ?>">
                                    <input type="hidden" name="token" value="<?= htmlspecialchars((string)($_POST['token'] ?? ""), ENT_QUOTES | ENT_SUBSTITUTE, 'ISO-8859-1') ?>">
                                </form>

                                <script>
                                    document.getElementById("redir").submit();
                                </script>
                <?php
                                exit;
                            } else {
                                $msg = 'Erro ao salvar o autenticador';
                            }
                        } else if (!$_POST['token']) {
                            $msg = 'Preencha o campo com um token!';
                        } else {
                            $msg = "Token inv�lido!";
                        }
                    }
                }
                $qrCodeUrl = $ga->getQRCodeImageUrl('E-Prepag bko', $secret);

                $_SESSION['id_do_usuario'] = $fetch['id'];

                ?>
                <style>
                    .aut-footer {
                        display: flex;
                        justify-content: end;
                    }

                    .msg-error {
                        color: red;
                        font-size: 16px;
                        font-weight: bold;
                        width: 100%;
                        text-align: start;
                    }

                    .instrucoes {
                        font-family: system-ui, sans-serif;
                        color: #333;
                    }

                    ol.lista-instrucoes li {
                        line-height: 1.7;
                        margin-bottom: 3px;
                    }

                    .div-principal {
                        display: flex;
                        flex-direction: row;
                        justify-content: stretch;
                    }

                    .botao-expandir {
                        background: none;
                        border: none;
                        color: #555;
                        font-size: 16px;
                        font-family: system-ui, sans-serif;
                        cursor: pointer;
                        padding: 8px 0;
                        margin-bottom: 10px;
                    }

                    .botao-expandir:hover {
                        color: #333;
                    }


                    @media (min-width: 601px) {
                        .botao-expandir {
                            display: none;
                        }

                        .instrucoes {
                            display: block !important;
                        }
                    }

                    @media (max-width: 600px) {
                        .div-principal {
                            flex-direction: column;
                            /* empilha os itens */
                        }

                        .instrucoes {
                            display: none;
                        }

                        .instrucoes.expandida {
                            display: block;
                        }

                        .botao-expandir {
                            display: block;
                            margin-top: 15px;
                        }
                    }
                </style>
                <div id="modal-autenticador" class="modal fade txt-preto" role="dialog">
                    <div class="modal-dialog modal-md">
                        <!-- Modal content-->
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title">Cadastro de autenticador</h4>
                            </div>
                            <div class="modal-body espacamento div-principal">
                                <form id="formAutenticador" action="" method="post">
                                    <div class="text-left" style="margin: 15px;">
                                        <label class="">QR Code:</label>
                                        <div>
                                            <img src="<?= $qrCodeUrl ?>" style="width: 170px; height: auto;" />
                                        </div>
                                    </div>

                                    <div class="text-left" style="margin: 15px;">
                                        <label class="">Chave de seguran�a:</label>
                                        <div id="div-copiar" onclick="copyAuthCode()" style="cursor: pointer;">
                                            <p id="authCode" style="font-size: 15px; letter-spacing: 0.5px; margin-bottom: 0px;">
                                                <?= $secret ?>
                                            </p>
                                            <small style="color: #333;" id="copyMessage">Clique para copiar</small>
                                        </div>
                                    </div>

                                    <div>
                                        <label style="margin-top: 15px;" for="token">
                                            Insira o Token gerado:
                                        </label>
                                        <input type="text" name="token" id="token" class="form-control" style="max-width: 180px;">
                                    </div>

                                    <div style="margin-top: 15px; max-width: 180px;">
                                        <button type="submit" class="btn btn-success btn-block" id="alteraToken">Salvar</button>
                                    </div>
                                </form>
                                <button class="botao-expandir btn"
                                    onclick="document.querySelector('.instrucoes').classList.toggle('expandida')">
                                    Como configurar o autenticador? &#11206;
                                </button>
                                <div class="instrucoes">

                                    <h3>Instru��es:</h3>
                                    <ol class="lista-instrucoes">
                                        <li>Abra o aplicativo autenticador instalado no seu celular. Caso n�o tenha um autenticador,
                                            voc� deve instalar um. O Microsoft Authenticator e o Google Authenticator s�o os mais
                                            populares.</li>

                                        <li>Com o aplicativo aberto, leia o QR code gerado pelo nosso site.
                                            Se estiver usando celular, copie a chave de seguran�a gerada e cole no
                                            aplicativo autenticador.</li>

                                        <li>Aparecer� um c�digo de 6 d�gitos no seu aplicativo.</li>

                                        <li>Digite esse c�digo no site da E-prepag para confirmar e pronto! O autenticador est�
                                            associado a seu PDV.</li>

                                    </ol>
                                    <div style="width: 100%; display: flex; justify-content: center;">
                                        <iframe width="300" height="170" src="https://www.youtube.com/embed/H_19Cv6jSDU" title="Tutorial autenticador" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer aut-footer">
                                <?php
                                if (isset($msg)) {
                                    echo "<p class='msg-error'>$msg</p>";
                                }
                                ?>
                                <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
                            </div>
                        </div>
                    </div>
                </div>
                <script type="text/javascript">
                    $("#formAutenticador").submit(function(e) {
                        e.preventDefault();
                        $("#modal-autenticador").modal('hide');
                        setTimeout(function() {
                            $.ajax({
                                url: 'ajax_cria_aut.php',
                                type: 'POST',
                                data: $("#formLog").serialize() + "&token=" + $("#token").val(),
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
                    });

                    function copyAuthCode() {
                        const authCode = document.getElementById("authCode").innerText;
                        navigator.clipboard.writeText(authCode).then(() => {
                            const message = document.getElementById("copyMessage");
                            message.innerText = "Copiado!";
                            setTimeout(() => {
                                message.innerText = "Clique para copiar";
                            }, 2000);
                        }).catch(err => {
                            console.error("Erro ao copiar:", err);
                        });
                    }
                </script>
<?php
                exit;
            }
        }
    }
} catch (PDOException $e) {
    echo "Erro de conexao";
}

?>