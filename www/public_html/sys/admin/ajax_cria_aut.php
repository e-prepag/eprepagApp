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
require_once __DIR__ . "/../../../class/GoogleAutenticator.php";
require_once $raiz_do_projeto . "public_html/sys/includes/configuracao.php";
require_once $raiz_do_projeto . "public_html/sys/includes/languages.php";
require_once "../includes/funcoes_login.php";
require_once "/www/class/classSecureEncryption.php";

session_start();

if ($_SESSION['RECAPTCHA_TRUE'] != true) {
    echo '<script>window.location.href = "index.php?Invalido=1";</script>';
    exit;
}

$_SESSION['RECAPTCHA_TRUE'] = null;

$ga = new PHPGangsta_GoogleAuthenticator();

try {
    $pdo = ConnectionPDO::getConnection()->getLink();

    if (Util::isAjaxRequest()) {

        $senha_decript = null;
        $user_decript = null;

        $okDecript = descript_login($_POST['user'], $_POST['passw'], $senha_decript, $user_decript);
        if ($okDecript != 1) {
            echo "<script>alert('Error');</script>";
            exit;
        }
        
        $login = strtoupper(trim($user_decript));

        $sql = "SELECT id, chave_autenticador, shn_password FROM usuarios WHERE shn_login = ?
        AND ((tipo_acesso='AD') OR (tipo_acesso='DT') OR (tipo_acesso='SV') OR (tipo_acesso='AT') OR (tipo_acesso='PU') OR (tipo_acesso='US'))";

        $con = ConnectionPDO::getConnection();
        $pdo = $con->getLink();

        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($login));
        $fetch = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($stmt->rowCount() > 0) {
            if (empty($fetch['chave_autenticador'])) {

                $bcrypt = new SecureEncryption();

                if (!$bcrypt->verifyPassword($senha_decript, $fetch['shn_password'])) {
                    echo "<script>alert('" . LANG_USER_PASS_INVALID . "');</script>";
                    exit;
                }

                $senha_base64 = null;
                $user_base64 = null;
                cript_login($user_decript, $senha_decript, $user_base64, $senha_base64);

                $user_html = htmlspecialchars($user_base64, ENT_QUOTES | ENT_SUBSTITUTE, 'ISO-8859-1');
                $senha_html = htmlspecialchars($senha_base64, ENT_QUOTES | ENT_SUBSTITUTE, 'ISO-8859-1');

                $_SESSION['RECAPTCHA_TRUE'] = true;
                if (!$_SESSION['secret']) {
                    $secret = $ga->createSecret();
                    $_SESSION['secret'] = $secret;
                } else {
                    $secret = $_SESSION['secret'];

                    if ($_SESSION['id_do_usuario'] == $fetch['id']) {
                        if ($ga->verifyCode($secret, $_POST['token'], 2)) {
                            $sql = "UPDATE usuarios SET chave_autenticador = ? WHERE id = ?";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute([$secret, $fetch['id']]);

                            // Verifica se alguma linha foi afetada
                            if ($stmt->rowCount() > 0) {
                                $_SESSION['secret'] = "";
?>
                                <form id="redir" method="POST" action="index2.php">
                                    <input type="hidden" name="user" value="<?= $user_html ?>">
                                    <input type="hidden" name="passw" value="<?= $senha_html ?>">
                                    <input type="hidden" name="token" value="<?= htmlspecialchars($_POST['token'], ENT_QUOTES | ENT_SUBSTITUTE, 'ISO-8859-1') ?>">
                                </form>

                                <script>
                                    document.getElementById("redir").submit();
                                </script>
                <?php
                                exit;
                            } else {
                                $msg = LANG_ERROR_SAVE_AUT;
                            }
                        } else if (!$_POST['token']) {
                            $msg = LANG_FILL_TOKEN_FIELD;
                        } else {
                            $msg = LANG_INVALID_TOKEN;
                        }
                    }
                }
                $qrCodeUrl = $ga->getQRCodeGoogleUrl('E-Prepag bko', $secret);

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
                                <h4 class="modal-title"><?= LANG_TWO_FA_REGISTER ?></h4>
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
                                        <label class=""><?= LANG_TWO_FA_KEY ?></label>
                                        <div id="div-copiar" onclick="copyAuthCode()" style="cursor: pointer;">
                                            <p id="authCode" style="font-size: 15px; letter-spacing: 0.5px; margin-bottom: 0px;">
                                                <?= $secret ?>
                                            </p>
                                            <small style="color: #333;" id="copyMessage"><?= LANG_CLICK_TO_COPY ?></small>
                                        </div>
                                    </div>

                                    <div>
                                        <label style="margin-top: 15px;" for="token">
                                            <?= LANG_ENTER_TOKEN ?>
                                        </label>
                                        <input type="text" name="token" id="token" class="form-control" style="max-width: 180px;">
                                    </div>

                                    <div style="margin-top: 15px; max-width: 180px;">
                                        <button type="submit" class="btn btn-success btn-block" id="alteraToken"><?= LANG_SAVE ?></button>
                                    </div>
                                </form>
                                <button class="botao-expandir btn"
                                    onclick="document.querySelector('.instrucoes').classList.toggle('expandida')">
                                    <?= LANG_HOW_TO_AUT ?> &#11206;
                                </button>
                                <div class="instrucoes">
                                    <?= $LANG_INSTRUCOES_CONFIG_AUTENTICADOR ?>
                                    <div style="width: 100%; display: flex; justify-content: center;">
                                        <iframe width="300" height="170px" src="https://www.youtube.com/embed/H_19Cv6jSDU"
                                            frameborder="0"
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                            allowfullscreen>
                                        </iframe>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer aut-footer">
                                <?php
                                if (isset($msg)) {
                                    echo "<p class='msg-error'>$msg</p>";
                                }
                                ?>
                                <button type="button" class="btn btn-default" data-dismiss="modal"><?= LANG_CLOSE_BTN ?></button>
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
                                data: {
                                    user: '<?= $user_base64 ?>',
                                    passw: '<?= $senha_base64 ?>',
                                    token: $("#token").val()
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
                    });

                    function copyAuthCode() {
                        const authCode = document.getElementById("authCode").innerText;
                        navigator.clipboard.writeText(authCode).then(() => {
                            const message = document.getElementById("copyMessage");
                            message.innerText = "<?= LANG_COPIED ?>";
                            setTimeout(() => {
                                message.innerText = "<?= LANG_CLICK_TO_COPY ?>";
                            }, 2000);
                        }).catch(err => {
                            console.error("Error to copy:", err);
                        });
                    }
                </script>
<?php
                exit;
            }
        }
    }
} catch (PDOException $e) {
    echo "Connection Error";
}

?>