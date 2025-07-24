<?php
require_once "../../../includes/constantes.php";
require_once DIR_CLASS . "gamer/controller/HeaderController.class.php";
require_once "../../libs/PHPGangsta/GoogleAuthenticator.php";
require_once "../../../includes/constantes_url.php";

$posicao = "Inferior Internas";
$controller = new HeaderController;
$banners = $controller->getBanner($posicao);

$usuario = unserialize($_SESSION['usuarioGames_ser']);
if (!$usuario) {
    header("Location: " . EPREPAG_URL_HTTPS);
    exit;
}

$controller->setHeader();

$ga = new PHPGangsta_GoogleAuthenticator();

$secret = $ga->createSecret();
$qrCodeUrl = $ga->getQRCodeGoogleUrl('E-Prepag Gamer', $secret);
$_SESSION['secret'] = $secret;
?>
<script src="/js/valida.js"></script>
<script src="/js/validaSenha.js"></script>
<script>
    $(function () {
        $("#alteraSenha").click(function () {

            waitingDialog.show('Por favor, aguarde...', { dialogSize: 'sm' });

            var erro = validaFormSenha();
            if (erro.length > 0) {
                waitingDialog.hide();
                manipulaModal(1, erro.join("<br>"), "Atenção");
                return false;
            }

            $.ajax({
                type: "POST",
                dataType: "JSON",
                url: "/game/ajax/dados-acesso.php",
                data: { type: "pass", novaSenha: $("#novaSenha").val(), confirmaSenha: $("#confirmaSenha").val(), senha: $("#senha").val() },
                success: function (obj) {

                    waitingDialog.hide();

                    if (obj.erro.length > 0) {
                        $("#modal-senha").modal('hide');
                        manipulaModal(1, obj.erro, "Erro");
                        $("#senha").val('');
                        return false;
                    } else {
                        $("#modal-senha").modal('hide');
                        manipulaModal(2, "Senha alterada.", "Operação concluída.");
                        return false;
                    }
                },
                error: function () {
                    waitingDialog.hide();
                    manipulaModal(1, "Erro desconhecido, favor entrar em contato com o nosso suporte.", "Erro");
                    return false;
                }
            });

        });

        $("#alteraLogin").click(function () {

            waitingDialog.show('Por favor, aguarde...', { dialogSize: 'sm' });

            if ($("#novoLogin").val().lenth < 5 || $("#novoLogin").val().lenth > 100) {
                waitingDialog.hide();
                manipulaModal(1, "O Login deve ter mais de 5 caracteres.", "Atenção");
                return false;
            }

            if ($("#novoLogin").val() != $("#confirmaLogin").val()) {
                waitingDialog.hide();
                manipulaModal(1, "A confirmação de login está incorreta.", "Erro");
                return false;
            }

            $.ajax({
                type: "POST",
                dataType: "JSON",
                url: "/game/ajax/dados-acesso.php",
                data: { type: "novoLogin", login: $("#novoLogin").val(), confirmaLogin: $("#confirmaLogin").val(), senha: $("#l-senha").val() },
                success: function (obj) {

                    waitingDialog.hide();

                    if (obj.erro.length > 0) {
                        $("#modal-login").modal('hide');
                        manipulaModal(1, obj.erro, "Erro");
                        $("#l-senha").val('');
                        return false;
                    } else {
                        $("#modal-login").modal('hide');
                        manipulaModal(2, "Login alterado.", "Operação concluída");
                        return false;
                    }
                },
                error: function () {
                    waitingDialog.hide();
                    manipulaModal(1, "Erro desconhecido, favor entrar em contato com o nosso suporte.", "Erro");
                    return false;
                }
            });
        });

        $("#alteraEmail").click(function () {

            waitingDialog.show('Por favor, aguarde...', { dialogSize: 'sm' });

            if (!checkEmail($("#novoEmail").val())) {
                waitingDialog.hide();
                manipulaModal(1, "E-mail inválido", "Atenção");
                return false;
            }

            if ($("#novoEmail").val() != $("#confirmaEmail").val()) {
                waitingDialog.hide();
                manipulaModal(1, "A confirmação de e-mail está incorreta. Verifique os dados inseridos.", "Atenção");
                return false;
            }

            $.ajax({
                type: "POST",
                dataType: "JSON",
                url: "/game/ajax/dados-acesso.php",
                data: { type: "solicitaNovoEmail", email: $("#novoEmail").val(), confirmaEmail: $("#confirmaEmail").val(), senha: true },
                success: function (obj) {

                    waitingDialog.hide();

                    if (obj.erro.length > 0) {
                        $("#modal-email").modal('hide');
                        manipulaModal(1, obj.erro, "Erro");
                        return false;
                    } else {
                        $("#modal-email").modal('hide');
                        manipulaModal(2, "Um e-mail foi enviado para " + $("#novoEmail").val() + ", por favor, acesse e siga passo a passo para finalizar a edição.", "Alteração solicitada.");
                        return false;
                    }
                },
                error: function () {
                    waitingDialog.hide();
                    manipulaModal(1, "Erro desconhecido, favor entrar em contato com o nosso suporte.", "Erro");
                    return false;
                }
            });
        });

        $("#alteraToken").click(function () {

            waitingDialog.show('Por favor, aguarde...', { dialogSize: 'sm' });

            if ($("#token").val().trim() === "") {
                waitingDialog.hide();
                manipulaModal(1, "O token novo não pode ser vazio.", "Atenção");
                return false;
            }

            if ($("#cad_senhaAtual").val().trim() === "") {
                waitingDialog.hide();
                manipulaModal(1, "A senha não pode ser vazia.", "Atenção");
                return false;
            }

            $.ajax({
                type: "POST",
                dataType: "JSON",
                url: "/game/ajax/alterar_autenticador.php",
                data: { token: $("#token").val(), cad_senhaAtual: $("#cad_senhaAtual").val(), token_old: $("#token_old").val() },
                success: function (obj) {

                    waitingDialog.hide();

                    if (obj.erro.length > 0) {
                        $("#modal-login").modal('hide');
                        manipulaModal(1, obj.erro, "Erro");
                        $("#l-senha").val('');
                        return false;
                    } else {
                        $("#modal-login").modal('hide');
                        manipulaModal(2, "Autenticador alterado.", "Operação concluída");
                        return false;
                    }
                },
                error: function () {
                    waitingDialog.hide();
                    manipulaModal(1, "Erro desconhecido, favor entrar em contato com o nosso suporte.", "Erro");
                    return false;
                }
            });
        });

    });
</script>
<div class="container txt-cinza bg-branco  p-bottom40">
    <div class="row top20">
        <div class="col-md-3 txt-azul-claro">
            <div class="row">
                <span class="glyphicon glyphicon-triangle-right graphycon-big pull-left"
                    aria-hidden="true"></span><strong class="pull-left">
                    <h4 class="top20">MEU CARTÃO</h4>
                </strong>
            </div>
            <div class="row">
                <?php require_once RAIZ_DO_PROJETO . "public_html/game/includes/menu-carteira.php" ?>
            </div>
        </div>
        <div class="col-md-9 txt-azul-claro">
            <div class="col-md-12 col-lg-12 col-sm-12 col-xs-12">
                <strong class="pull-left top20">ALTERAR DADOS DE SEGURANÇA</strong>
            </div>
            <div class="col-md-12 col-lg-12 col-sm-12 col-xs-12 txt-cinza">
                <a href="#" class="btn btn-info" data-toggle="modal" data-target="#modal-senha">Senha</a>
                <a href="#" class="btn btn-info" data-toggle="modal" data-target="#modal-email">E-mail</a>
                <a href="#" class="btn btn-info" data-toggle="modal" data-target="#modal-login">Login</a>
                <a href="#" class="btn btn-info" data-toggle="modal" data-target="#modal-autenticador">Autenticador</a>
            </div>
        </div>
        <div id="modal-senha" class="modal fade" role="dialog">
            <div class="modal-dialog modal-sm">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Alteração senha</h4>
                    </div>
                    <div class="modal-body">
                        <form role="form" id="formSenha" name="formSenha" method="POST">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="novaSenha">Nova senha:</label>
                                    <input type="password" class="form-control novaSenha" onpaste="return false;"
                                        autocomplete="new-password" maxlength="12" char="6" id="novaSenha"
                                        name="novaSenha" placeholder="Nova senha">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="confirmaSenha">Confirme a nova senha:</label>
                                    <input type="password" class="form-control confirmacaoSenha" onpaste="return false;"
                                        autocomplete="new-password" maxlength="12" id="confirmaSenha" char="6"
                                        name="confirmaSenha" placeholder="Confirme sua nova senha">
                                    <div class="progress w-auto top10">
                                        <div class="progress-bar hidden progress-bar-danger" style="width: 33.33%">
                                            <span class="sr-only">33.33% Complete (danger)</span>
                                        </div>
                                        <div class="progress-bar hidden progress-bar-warning" style="width: 33.33%">
                                            <span class="sr-only">33.33% Complete (warning)</span>
                                        </div>
                                        <div class="progress-bar hidden progress-bar-success" style="width: 33.33%">
                                            <span class="sr-only">33.33% Complete (success)</span>
                                        </div>
                                    </div>
                                    <div class="col-md-12 txt-vermelho fontsize-pp">
                                        *Sua senha deve ter: de 6 a 12 caracteres, letras, números, caracteres especiais
                                        (|,!,?,*,$,%, etc)
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group top10">
                                    <label for="senha">Senha atual:</label>
                                    <input type="password" class="form-control" id="senha" autocomplete="new-password"
                                        name="senha" placeholder="Senha atual">
                                </div>
                            </div>
                            <div class="col-md-12 txt-vermelho bottom10 hide" id="erroSenha">
                            </div>
                            <div class="col-md-12 bottom20">
                                <a href="#" class="btn btn-success btn-block" id="alteraSenha">Alterar</a>
                            </div>
                        </form>
                        <div class="clearfix"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
                    </div>
                </div>
            </div>
        </div>
        <div id="modal-email" class="modal fade" role="dialog">
            <div class="modal-dialog modal-sm">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Alteração e-mail</h4>
                    </div>
                    <div class="modal-body espacamento">
                        <form role="form" id="formEmail" name="formEmail" method="POST">
                            <div class="form-group">
                                <label for="novoEmail">Novo e-mail:</label>
                                <input type="text" class="form-control" id="novoEmail" name="novoEmail"
                                    onpaste="return false;" autocomplete="new-password" placeholder="Novo e-mail">
                            </div>
                            <div class="form-group">
                                <label for="confirmaEmail">Confirme o novo e-mail:</label>
                                <input type="text" class="form-control" id="confirmaEmail" name="confirmaEmail"
                                    onpaste="return false;" autocomplete="new-password"
                                    placeholder="Confirme seu novo e-mail">
                            </div>
                            <a href="#" class="btn btn-success btn-block" id="alteraEmail">Alterar</a>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
                    </div>
                </div>
            </div>
        </div>
        <div id="modal-login" class="modal fade" role="dialog">
            <div class="modal-dialog modal-sm">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Alteração de login</h4>
                    </div>
                    <div class="modal-body espacamento">
                        <form role="form" id="formLogin" name="formLogin" method="POST">
                            <div class="form-group">
                                <label for="novoLogin">Novo login:</label>
                                <input type="text" class="form-control" id="novoLogin" autocomplete="new-password"
                                    onpaste="return false;" name="novoLogin" placeholder="Novo login">
                            </div>
                            <div class="form-group">
                                <label for="confirmaLogin">Confirme o novo login:</label>
                                <input type="text" class="form-control" id="confirmaLogin" autocomplete="new-password"
                                    onpaste="return false;" name="confirmaLogin" placeholder="Confirme seu novo login">
                            </div>
                            <div class="form-group">
                                <label for="l-senha">Senha:</label>
                                <input type="password" class="form-control" id="l-senha" name="l-senha"
                                    autocomplete="new-password" placeholder="Digite sua senha">
                            </div>
                            <a href="#" class="btn btn-success btn-block" id="alteraLogin">Alterar</a>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
                    </div>
                </div>
            </div>
        </div>
        <div id="modal-autenticador" class="modal fade" role="dialog">
            <div class="modal-dialog modal-sm">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Alteração de autenticador</h4>
                    </div>
                    <div class="modal-body espacamento">
                        <form name="form1" action="" method="post">
                            <div class="mb-3">
                                <label style="margin-top: 15px;" for="token_old" class="<?php if (isset($txtVermelho))
                                    echo $txtVermelho; ?>">
                                    Insira o Token gerado pelo autenticador atual:
                                </label>
                                <input type="text" name="token_old" id="token_old" class="form-control"
                                    placeholder="Preencha apenas se possuir">
                            </div>
                            <div class="mb-3">
                                <label style="margin-top: 15px;" for="cad_senhaAtual" class="<?php if (isset($txtVermelho))
                                    echo $txtVermelho; ?>">
                                    Insira sua senha atual:
                                </label>
                                <input type="password" name="cad_senhaAtual" id="cad_senhaAtual" class="form-control">
                            </div>
                            <div class="text-left" style="margin: 15px;">
                                <label class="">QR Code:</label>
                                <div>
                                    <img src="<?= $qrCodeUrl ?>" style="width: 170px; height: auto;" />
                                </div>
                            </div>

                            <div class="text-left mt-3" style="margin: 15px;">
                                <label class="">Chave de segurança:</label>
                                <div id="div-copiar" style="cursor: pointer;">
                                    <p id="authCode"
                                        style="font-size: 15px; letter-spacing: 0.5px; margin-bottom: 0px;">
                                        <?= $secret ?>
                                    </p>
                                    <small style="color: #333;" id="copyMessage">Clique para copiar</small>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label style="margin-top: 15px;" for="token" class="<?php if (isset($txtVermelho))
                                    echo $txtVermelho; ?>">
                                    Insira o novo Token gerado:
                                </label>
                                <input type="text" name="token" id="token" class="form-control">
                            </div>

                            <div class="d-grid gap-2 mt-3" style="margin-top: 15px;">
                                <a href="#" class="btn btn-success btn-block" id="alteraToken">Salvar</a>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    if (!empty($banners)) {
        ?>
        <div class="col-md-12 top10">
            <a href='<?php echo $banners[0]->link; ?>' target="_blank">
                <img title="<?php echo $banners[0]->titulo; ?>" alt="<?php echo $banners[0]->titulo; ?>"
                    class="img-responsive" src="<?php echo $controller->objBanners->urlLink . $banners[0]->imagem; ?>">
            </a>
        </div>
        <?php
    }
    ?>
</div>
</div>
<?php
require_once RAIZ_DO_PROJETO . "public_html/game/includes/footer.php";