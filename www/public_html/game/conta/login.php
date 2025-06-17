<?php require_once __DIR__ . '/../../../includes/constantes_url.php'; ?>
<?php
header("Content-Type: text/html; charset=ISO-8859-1; P3P: CP='CAO PSA OUR'", true);

//header("location: EPREPAG_URL_HTTPS/");
//exit;

if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], "/prepag2")) {
    session_start();


    if (isset($GLOBALS['_SESSION']['integracao_error_msg']) && $GLOBALS['_SESSION']['integracao_error_msg'] != "") {
        echo $GLOBALS['_SESSION']['integracao_error_msg'] . "<br>";
    }//end if
    else {
        echo "Ops, parece que algo deu errado!<br>
         Por favor, faça um novo pedido para concluir a compra.";
    }//end else
    session_destroy();
    die();
}//end if 

require_once "../../../includes/constantes.php";
require_once DIR_CLASS . "gamer/controller/HeaderController.class.php";

$controller = new HeaderController();

if ($controller->logado)
    Util::redirect("/game/");
else
    require_once RAIZ_DO_PROJETO . "public_html/game/includes/header-off.php";

$msg = htmlspecialchars($msg, ENT_QUOTES);
    
if(isset($GLOBALS['_SESSION']['integracao_error_msg']) && $GLOBALS['_SESSION']['integracao_error_msg'] != ""){
    $msg = $GLOBALS['_SESSION']['integracao_error_msg'];
}
?>
<div class="container txt-cinza bg-branco " style="padding-bottom: 60px;margin-top: 29px;">
    <div class="row top40">
        <div class="col-md-12">
            <span class="glyphicon glyphicon-triangle-right graphycon-big color-green pull-left"></span>
            <strong class="pull-left top15 color-green font20">Acesse sua conta</strong>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8 ">
            <hr class="border-green">
        </div>
    </div>
    <div class="row top10">
        <?php
        if (isset($msg) && $msg != "") {
            ?>
            <div class="col-md-12 espacamento">
                <div class="alert alert-danger" role="alert">
                    <span class="glyphicon glyphicon-exclamation-sign t0" aria-hidden="true"></span>
                    <span class="sr-only">Erro:</span>
                    <?php echo $msg; ?>
                </div>
            </div>
            <?php
            unset($GLOBALS['_SESSION']['integracao_error_msg']);
        }
        ?>
    </div>
    <div class="row">
        <div class="clearfix"></div>
        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 borda-colunas-formas-pagamento box-servico text-right">

            <div class="row mleft5 txt-azul-claro">
                <div class="col-md-12 text-left">
                    <h4>Acesso para gamers</h4>
                </div>
            </div>
            <form id="formLogin" class="top20" method="post">
                <div class="row top10 form-group">
                    <div class="col-md-4">
                        <label for="login">Login ou E-mail:</label>
                    </div>
                    <div class="col-md-6">
                        <input class="form-control" id="login" onpaste="return false;" name="login" autocomplete="off"
                            type="text" value="">
                    </div>
                </div>
                <div class="row top10  form-group">
                    <div class="col-md-4">
                        <label for="senha">Senha:</label>
                    </div>
                    <div class="col-md-6">
                        <input class="form-control" id="senha" name="senha" type="password" value="">
                    </div>
                    <div class="col-md-10 top10 fontsize-p">
                        <div style="padding: 0 0 15px 10px; display: flex; justify-content: end;">
                            <div class="g-recaptcha" data-sitekey="6Lc4XtkkAAAAAJrfsAKc99enqDlxXz4uq86FI9_T"></div>
                        </div>

						<a class="decoration-none txt-cinza" href="<?= EPREPAG_URL_HTTPS ?>/game/conta/esqueci-minha-senha/index.php?redirected=true&origemUsuario=gamer"><em>Esqueci minha senha</em></a>

                    </div>
                    <!-- Modal -->
                    <div id="modal-autenticador">
                    </div>
                    <!-- Modal -->
                    <div id="myModal" class="modal fade" role="dialog">
                        <div class="modal-dialog">
                            <!-- Modal content-->
                            <div class="modal-content text-left">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    <h4 class="modal-title">Erro de preenchimento</h4>
                                </div>
                                <div class="modal-body">
                                    <div class="alert alert-danger" role="alert">
                                        <h5><span id="msg-modal">Dados em destaque estão incorretos.</span> Por favor,
                                            confira e tente novamente.</h5>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="myModal2FA" class="modal fade" role="dialog">
                        <div class="modal-dialog">
                            <!-- Modal content-->
                            <div class="modal-content text-left">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    <h4 class="modal-title">Para confirmar sua identidade</h4>
                                </div>
                                <div class="modal-body">
                                    <div class="alert">
                                        <h5><span>Foi enviada uma mensagem em seu endereço de e-mail para confirmar sua
                                                identidade.</span></h5>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row top10 form-group">
                    <div class="col-md-5  dislineblock">
                        <a href="/game/conta/nova.php" class="btn btn-info btn-block"><strong>Não tem
                                cadastro?</strong></a>
                    </div>
                    <div class="col-md-5 dislineblock">
                        <button id="prosseguir" class=" btn btn-success btn-block">Prosseguir</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-sm-12 col-xs-12 col-md-6 col-lg-6 align-center" style="margin-top: 50px">
            <div class="row">
                <div class="col-md-12 txt-azul-claro">
                    <h2>É um ponto de venda?</h2>
                    <h4> Temos uma área especial para você</h4>
                </div>
            </div>
            <div class="row top40">
                <div class="col-md-offset-3 col-md-6">
                    <a href="/creditos/login.php" class="btn btn-success btn-block"><strong>Acesso - Ponto de
                            Venda</strong></a>
                </div>
            </div>
            <div class="row top20">
                <div class="col-md-offset-3 col-md-6">
                    <a href="/cadastro-de-ponto-de-venda.php" class="btn btn-info btn-block"><strong>Cadastro - Ponto de
                            Venda</strong></a>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row" style="margin-top: 29px">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 bg-info align-center borda-direita-basica p-bottom10">
        <h3>
            Quer ser um ponto de venda ?
            <a href="/cadastro-de-ponto-de-venda.php" class="txt-branco" target="_blank"><b><span
                        class="link-destaque">Cadastre-se</span></b></a>
            ou
            <a href="#" link="e-prepagpdv.com.br/" class="txt-branco redirecionamento"><b><span
                        class="link-destaque">saiba mais</span></b></a>.
        </h3>
    </div>
</div>
</div>
<script src="https://www.google.com/recaptcha/api.js" async="" defer=""></script>
<script src="/js/valida.js"></script>
<script>
    $(function () {
        <?php
        if (isset($msg) && $msg != "") {
            echo "manipulaModal(1,'$msg','Erro');";
        }


        ?>
        $("#prosseguir").click(function () {

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

                var dados = $("#formLogin").serialize();

                $.ajax({
                    type: 'POST',
                    url: '/game/ajax/autenticador.php',
                    data: dados,
                    beforeSend: function () {
                        waitingDialog.show('Por favor, aguarde...', { dialogSize: 'sm' });
                    },
                    success: function (ret) {
                        waitingDialog.hide();
                        if (ret == <?php echo RETURN_WRONG; ?> || ret == <?php echo RETURN_EMPTY; ?>) {
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
                        } else if (ret == <?php echo RETURN_CAPTCHA; ?>) {
                            erro = true;
                            $("#msg-modal").text("Você deve fazer a verificação do RECAPTCHA novamente para fazer o login.");
                            grecaptcha.reset();
                            $('#myModal').modal('show');
                            return false;
                        } else if (ret.trim() == "") {
                            erro = true;
                            $("#msg-modal").text("Erro, tente novamente. Entre em contato com o suporte caso persista.");
                            grecaptcha.reset();
                            $('#myModal').modal('show');
                            return false;
                        } else {

                            $("#modal-autenticador").html(ret);
                            $("#modal-token").modal('show');
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
<?php

require_once RAIZ_DO_PROJETO . "public_html/game/includes/footer.php";