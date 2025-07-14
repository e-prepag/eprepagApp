<?php

session_start();
session_regenerate_id();

require_once "../../../includes/constantes.php";
require_once DIR_CLASS . "util/Util.class.php";
require_once DIR_CLASS . "util/Validate.class.php";
require_once "/www/class/class2FA.php";
/*
 * Programa em AJAX para efetuar o login de gamer
 * 
 * @paramns $_POST['senha'], $_POST['login']
 * 
 * @return RETURN_SUCCESS = sucesso
 * @return RETURN_EMPTY = usuario ou senha em branco
 * @return RETURN_WRONG = usuario ou senha invalidos
 * @return RETURN_CAPTCHA = captcha incorreto
 */

if (Util::isAjaxRequest()) {

    require_once DIR_CLASS . "util/Log.class.php";
    require_once DIR_INCS . "main.php";
    require_once DIR_INCS . "gamer/main.php";
    require_once "funcoes_login.php";
    $validate = new Validate;

    function checkDevice($userId, $pdo)
    {
        if (!isset($_COOKIE['device_token'])) {
            return false; // Sem cookie, exige login
        }

        $deviceId = $_COOKIE['device_token'];
        $stmt = $pdo->prepare("SELECT * FROM usuarios_games_dispositivos WHERE user_id = ? AND device_token = ? AND expires_at > NOW()");
        $stmt->execute([$userId, $deviceId]);

        if ($stmt->fetch()) {
            return true; // Dispositivo válido
        } else {
            return false; // Dispositivo inválido ou expirado
        }
    }

    if (!empty($_POST["g-recaptcha-response"])) {

        $tokenInfo = ["secret" => "6Lc4XtkkAAAAAJYRV2wnZk_PrI7FFNaNR24h7koQ", "response" => $_POST["g-recaptcha-response"], "remoteip" => $_SERVER["REMOTE_ADDR"]];

        $recaptcha = curl_init();
        curl_setopt_array($recaptcha, [
            CURLOPT_URL => "https://www.google.com/recaptcha/api/siteverify",
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => http_build_query($tokenInfo)

        ]);
        $retorno = json_decode(curl_exec($recaptcha), true);
        curl_close($recaptcha);

        if ($retorno["success"] != true || (isset($retorno["error-codes"]) && !empty($retorno["error-codes"]))) {
            $erro = true;
            registrarTentativaFalha($_POST['login']);
            echo RETURN_CAPTCHA;
            exit;
        }

    } else {
        registrarTentativaFalha($_POST['login']);
        $erro = true;
        echo RETURN_CAPTCHA;
        exit;
    }

    $tempoBloqueio = verificarBloqueio();
    if ($tempoBloqueio) {
        session_destroy();
        bloquearAcesso($tempoBloqueio);
    }

    if (isset($_POST['login']) && !empty($_POST['login']) && isset($_POST['senha']) && !empty($_POST['senha'])) {

        if ($validate->email($_POST['login']) == 0) {

            function verificaPOST($referer, $POST)
            {

                //if (strpos($referer,"bronzato.com.br")===false && strpos($referer,"contause.digital")===false){return false;}
                $flag = true;
                foreach ($_POST as $xa => $xb) {
                    $xb = serialize($xb);
                    if (strpos($xb, "dbms_pipe.receive_message") !== false || strpos($xb, "DBMS_PIPE.RECEIVE_MESSAGE") !== false || strpos($xb, "delete") !== false || strpos($xb, "delete") !== false || strpos($xb, "update") !== false || strpos($xb, "select") !== false) {
                        return false;
                    }

                    if (strpos($xb, "dbms_pipe.receive_message") !== false || strpos($xb, "DBMS_PIPE.RECEIVE_MESSAGE") !== false || strpos(hexToStr($xb), "delete") !== false || strpos(hexToStr($xb), "update") !== false || strpos(hexToStr($xb), "select") !== false) {
                        return false;
                    }
                }

                if ($flag) {
                    return true;
                } else {
                    return false;
                }
            }

            function hexToStr($hex)
            {
                $string = '';
                for ($i = 0; $i < strlen($hex) - 1; $i += 2) {
                    $string .= chr(hexdec($hex[$i] . $hex[$i + 1]));
                }
                return $string;
            }

            if (!verificaPOST("", $_POST)) {
                $erro = true;
            } else {

                $objEncryption = new Encryption();
                $senha = $objEncryption->encrypt(trim($_POST['senha']));
                $login = strtoupper(trim($_POST['login']));

                $sql = "SELECT ug_chave_autenticador, ug_id, ug_acesso_sem_aut FROM usuarios_games WHERE ug_ativo = 1 AND ug_email = ? AND ug_senha = ? ";

                $con = ConnectionPDO::getConnection();
                $pdo = $con->getLink();

                $stmt = $pdo->prepare($sql);
                $stmt->execute(array($login, $senha));
                $fetch = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($stmt->rowCount() > 0) {
                    $ret = true;
                    $_SESSION['captcha_passed'] = 1;
                    if (empty($fetch['ug_chave_autenticador'])) {
                        $_SESSION['id_do_usuario'] = $fetch['ug_id'];
                        modal_criar_token($fetch['ug_acesso_sem_aut']);
                        exit;
                    }

                    if (checkDevice($fetch['ug_id'], $pdo)) {
                        //$msg = "Dispositivo já autenticado.";
                        logar_direto();
                        exit;
                    }
                } else {
                    $ret = false;
                }

                $erro = false;

                if (!$ret) {
                    $erro = true;
                    $geraLog = new Log("log_login", array("Login ou senha inválidos gamer: '" . $_POST['login'] . "', '" . $_POST['senha']));
                    registrarTentativaFalha($_POST['login']);
                }
            }

            if ($erro) {
                echo RETURN_WRONG;
            } else {
                modal_token();
            }

        } else if ($validate->qtdCaracteres($_POST['login'], 2, 255) == 0) {
            //validar minimo de 3 caracteres e verificar maximo permitido para o capmo ug_login na tabela
            //metodo autenticarUgLogin($_POST['login'],$_POST['senha']);
            if (!filter_var($_POST['login'], FILTER_VALIDATE_EMAIL)) {
                $objEncryption = new Encryption();
                $senha = $objEncryption->encrypt(trim($_POST['senha']));
                $login = strtoupper(trim($_POST['login']));

                $sql = "SELECT ug_chave_autenticador, ug_id, ug_acesso_sem_aut FROM usuarios_games WHERE ug_ativo = 1 AND ug_login = ? AND ug_senha = ? ";

                $con = ConnectionPDO::getConnection();
                $pdo = $con->getLink();

                $stmt = $pdo->prepare($sql);
                $stmt->execute(array($login, $senha));
                $fetch = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($stmt->rowCount() > 0) {
                    $ret = true;
                    $_SESSION['captcha_passed'] = 1;
                    if (empty($fetch['ug_chave_autenticador'])) {
                        $_SESSION['id_do_usuario'] = $fetch['ug_id'];
                        modal_criar_token($fetch['ug_acesso_sem_aut']);
                        exit;
                    }

                    if (checkDevice($fetch['ug_id'], $pdo)) {
                        //$msg = "Dispositivo já autenticado.";
                        logar_direto();
                        exit;
                    }
                } else {
                    $ret = false;
                }
            }

            $erro = false;

            if (!$ret) {
                $erro = true;
                $geraLog = new Log("log_login", array("Login ou senha inválidos gamer: '" . $_POST['login'] . "', '" . $_POST['senha'] . "'"));
                registrarTentativaFalha($_POST['login']);
            }

            if ($erro) {
                echo RETURN_WRONG;
            } else {
                modal_token();
            }
        } else {
            echo RETURN_WRONG;
        }
    } else {
        echo RETURN_EMPTY;
    }
}

function modal_token()
{
    ?>
    <div id="modal-token" class="modal fade" role="dialog">
        <div class="modal-dialog modal-sm">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title text-left">Digite o token disponível no seu app de autenticação</h4>
                </div>
                <div class="modal-body espacamento">
                    <form role="form" id="formLogar" name="formLogar" method="POST">
                        <div class="form-group text-left">
                            <label for="token">Token:</label>
                            <input type="text" class="form-control" id="token" name="token" placeholder="Token">
                            <div style="margin: 7px 0px; display: flex; align-items: center; gap: 3px;">
                                <label for="salvarDispositivo" style="margin: 0; font-weight: normal;">Lembrar desse
                                    dispositivo:</label>
                                <input type="checkbox" id="salvarDispositivo" name="salvarDispositivo" style="margin: 0;"
                                    value="sim">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success btn-block" id="logarToken">Logar</button>
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
    <script>
        $(function () {
            $("#formLogar").submit(function (e) {
                e.preventDefault();

                var erro = false;
                if (grecaptcha.getResponse() == "" || grecaptcha.getResponse().length == 0) {
                    $("#msg-modal").text("Você deve fazer a verificação do RECAPTCHA para fazer o login.");
                    erro = true;
                }
                else {
                    $("#msg-modal").text("Dados em destaque estão incorretos. Por favor, confira e tente novamente.");
                }
                if ($("#login").val().trim() == "") {
                    $("label[for='login']").addClass("txt-vermelho");

                    $("#login").css("border-color", "#e04847");
                    erro = true;
                } else {
                    $("label[for='login']").removeClass("txt-vermelho");
                    $("#login").css("border-color", "");
                }

                if ($("#senha").val().trim() == "") {
                    $("label[for='senha']").addClass("txt-vermelho");
                    $("#senha").css("border-color", "#e04847");
                    erro = true;
                } else {
                    $("label[for='senha']").removeClass("txt-vermelho");
                    $("#senha").css("border-color", "");
                }

                if (!erro) {

                    var dados = {
                        login: $("#login").val(),
                        senha: $("#senha").val(),
                        token: $("#token").val(),
                        salvarDispositivo: $("#salvarDispositivo").val(),
                    }

                    $.ajax({
                        type: 'POST',
                        url: '/game/ajax/login2.php',
                        data: dados,
                        beforeSend: function () {
                            waitingDialog.show('Por favor, aguarde...', { dialogSize: 'sm' });
                        },
                        success: function (ret) {
                            waitingDialog.hide();
                            console.log(ret);
                            if (ret == <?php echo RETURN_SUCCESS; ?>) {
                                window.location.href = "/game/";

                            } else if (ret == <?php echo RETURN_WRONG; ?> || ret == <?php echo RETURN_EMPTY; ?>) {
                                erro = true;
                                $("#msg-modal").text("Usuário ou senha inválidos ou não encontrados.");
                                $("label[for='login']").addClass("txt-vermelho");
                                $("label[for='senha']").addClass("txt-vermelho");
                                $("#login").css("border-color", "#e04847");
                                $("#senha").css("border-color", "#e04847");
                                grecaptcha.reset();
                                $('#myModal').modal('show');
                                return false;
                            } else if (ret == <?php echo RETURN_MAX_COUNT; ?>) {
                                erro = true;
                                window.location.href = "/game/conta/pagina_bloqueio.php";
                            }
                            else if (ret == <?php echo RETURN_TWO_FACTOR; ?>) {
                                grecaptcha.reset();
                                $('#myModal2FA').modal('show');
                            } else {
                                erro = true;
                                $("#msg-modal").text(ret);
                                grecaptcha.reset();
                                $('#myModal').modal('show');
                                return false;
                            }
                        },
                        error: function () {
                            grecaptcha.reset();
                            waitingDialog.hide();
                            manipulaModal(1, "Erro desconhecido, favor entrar em contato com o nosso suporte.", "Erro");
                            return false;
                        }
                    });
                }

                if (erro) {
                    grecaptcha.reset();
                    $('#myModal').modal('show');
                    return false;
                } else {
                    return false;
                }

            });
        });
    </script>
    <?php
}

function modal_criar_token($dia_faltam)
{
    $https = 'http' . (($_SERVER['HTTPS'] == 'on') ? 's' : '');
    $server_url = $https . '://' . (checkIP() ? $_SERVER['SERVER_NAME'] : 'www.e-prepag.com.br');

    $dataUltimoAcesso = new DateTime($dia_faltam);
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
    <div id="modal-token" class="modal fade" role="dialog">
        <div class="modal-dialog modal-md">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header text-left">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title text-left" style="word-break: break-word;"><?= $mensagemAuth ?></h4>
                </div>
                <div class="modal-body espacamento">
                    <div class="dislineblock" style="margin-right: 25px;">
                        <a style="font-weight: bold; font-style: italic;" class="pull-right btn btn-success"
                            href="<?= $server_url . '/game/conta/criar-autenticador.php' ?>"><?php echo $btn_recusar ? "Sim" : "Configurar"; ?></a>
                    </div>
                    <?php if ($btn_recusar) { ?>
                        <div class="dislineblock">
                            <button style="font-weight: bold; font-style: italic;" class="pull-right btn btn-info"
                                id="logar_sem_token">Não</button>
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
    <?php if ($btn_recusar) { ?>
        <script>
            $(function () {
                $("#logar_sem_token").click(function (e) {

                    var erro = false;
                    if (grecaptcha.getResponse() == "" || grecaptcha.getResponse().length == 0) {
                        $("#msg-modal").text("Você deve fazer a verificação do RECAPTCHA para fazer o login.");
                        erro = true;
                    }
                    else {
                        $("#msg-modal").text("Dados em destaque estão incorretos. Por favor, confira e tente novamente.");
                    }
                    if ($("#login").val().trim() == "") {
                        $("label[for='login']").addClass("txt-vermelho");

                        $("#login").css("border-color", "#e04847");
                        erro = true;
                    } else {
                        $("label[for='login']").removeClass("txt-vermelho");
                        $("#login").css("border-color", "");
                    }

                    if ($("#senha").val().trim() == "") {
                        $("label[for='senha']").addClass("txt-vermelho");
                        $("#senha").css("border-color", "#e04847");
                        erro = true;
                    } else {
                        $("label[for='senha']").removeClass("txt-vermelho");
                        $("#senha").css("border-color", "");
                    }

                    if (!erro) {

                        var dados = {
                            login: $("#login").val(),
                            senha: $("#senha").val(),
                        }

                        $.ajax({
                            type: 'POST',
                            url: '/game/ajax/login2.php',
                            data: dados,
                            beforeSend: function () {
                                waitingDialog.show('Por favor, aguarde...', { dialogSize: 'sm' });
                            },
                            success: function (ret) {
                                waitingDialog.hide();
                                console.log(ret);
                                if (ret == <?php echo RETURN_SUCCESS; ?>) {
                                    window.location.href = "/game/";

                                } else if (ret == <?php echo RETURN_WRONG; ?> || ret == <?php echo RETURN_EMPTY; ?>) {
                                    erro = true;
                                    $("#msg-modal").text("Usuário ou senha inválidos ou não encontrados.");
                                    $("label[for='login']").addClass("txt-vermelho");
                                    $("label[for='senha']").addClass("txt-vermelho");
                                    $("#login").css("border-color", "#e04847");
                                    $("#senha").css("border-color", "#e04847");
                                    grecaptcha.reset();
                                    $('#myModal').modal('show');
                                    return false;
                                } else if (ret == <?php echo RETURN_MAX_COUNT; ?>) {
                                    erro = true;
                                    window.location.href = "/game/conta/pagina_bloqueio.php";
                                }
                                else if (ret == <?php echo RETURN_TWO_FACTOR; ?>) {
                                    grecaptcha.reset();
                                    $('#myModal2FA').modal('show');
                                } else if (ret.startsWith('<')) {
                                    $("#modal-autenticador").html(ret);
                                    $("#modal-token").modal('show');
                                } else {
                                    erro = true;
                                    $("#msg-modal").text(ret);
                                    grecaptcha.reset();
                                    $('#myModal').modal('show');
                                    return false;
                                }
                            },
                            error: function () {
                                waitingDialog.hide();
                                grecaptcha.reset();
                                manipulaModal(1, "Erro desconhecido, favor entrar em contato com o nosso suporte.", "Erro");
                                return false;
                            }
                        });
                    }

                    if (erro) {
                        $('#myModal').modal('show');
                        return false;
                    } else {
                        return false;
                    }

                });
            });
        </script>
    <?php } ?>
<?php
}

function logar_direto()
{
    ?>
    <script>
        $(function () {
            var erro = false;
            if (grecaptcha.getResponse() == "" || grecaptcha.getResponse().length == 0) {
                $("#msg-modal").text("Você deve fazer a verificação do RECAPTCHA para fazer o login.");
                erro = true;
            }
            else {
                $("#msg-modal").text("Dados em destaque estão incorretos. Por favor, confira e tente novamente.");
            }
            if ($("#login").val().trim() == "") {
                $("label[for='login']").addClass("txt-vermelho");

                $("#login").css("border-color", "#e04847");
                erro = true;
            } else {
                $("label[for='login']").removeClass("txt-vermelho");
                $("#login").css("border-color", "");
            }

            if ($("#senha").val().trim() == "") {
                $("label[for='senha']").addClass("txt-vermelho");
                $("#senha").css("border-color", "#e04847");
                erro = true;
            } else {
                $("label[for='senha']").removeClass("txt-vermelho");
                $("#senha").css("border-color", "");
            }

            if (!erro) {

                var dados = {
                    login: $("#login").val(),
                    senha: $("#senha").val(),
                }

                $.ajax({
                    type: 'POST',
                    url: '/game/ajax/login2.php',
                    data: dados,
                    success: function (ret) {
                        waitingDialog.hide();
                        console.log(ret);
                        if (ret == <?php echo RETURN_SUCCESS; ?>) {
                            window.location.href = "/game/";

                        } else if (ret == <?php echo RETURN_WRONG; ?> || ret == <?php echo RETURN_EMPTY; ?>) {
                            erro = true;
                            $("#msg-modal").text("Usuário ou senha inválidos ou não encontrados.");
                            $("label[for='login']").addClass("txt-vermelho");
                            $("label[for='senha']").addClass("txt-vermelho");
                            $("#login").css("border-color", "#e04847");
                            $("#senha").css("border-color", "#e04847");
                            grecaptcha.reset();
                            $('#myModal').modal('show');
                            return false;
                        } else if (ret == <?php echo RETURN_MAX_COUNT; ?>) {
                            erro = true;
                            window.location.href = "/game/conta/pagina_bloqueio.php";
                        }
                        else if (ret == <?php echo RETURN_TWO_FACTOR; ?>) {
                            grecaptcha.reset();
                            $('#myModal2FA').modal('show');
                        } else {
                            erro = true;
                            $("#msg-modal").text(ret);
                            grecaptcha.reset();
                            $('#myModal').modal('show');
                            return false;
                        }
                    },
                    error: function () {
                        grecaptcha.reset();
                        waitingDialog.hide();
                        manipulaModal(1, "Erro desconhecido, favor entrar em contato com o nosso suporte.", "Erro");
                        return false;
                    }
                });
            }

            if (erro) {
                grecaptcha.reset();
                $('#myModal').modal('show');
                return false;
            } else {
                return false;
            }
        });
    </script>
    <?php
}