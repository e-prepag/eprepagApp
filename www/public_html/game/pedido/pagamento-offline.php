<?php
require_once "../../../includes/constantes.php";
require_once DIR_CLASS . 'gamer/controller/PagamentoOfflineController.class.php';

$controller = new PagamentoOfflineController();

require_once DIR_WEB . "game/includes/header-off.php";
require_once DIR_INCS . "config.MeiosPagamentos.php";

//Recupra carrinho do session
$carrinho = $_SESSION['carrinho'];

//Variavel de teste para permitir compar utilizando BOLETO EXPRESS
$permitidoBoletoExpress = defined('PAGAMENTO_BOLETO') ? PAGAMENTO_BOLETO : TRUE;
if (is_array($carrinho) && count($carrinho) > 0) {
    foreach ($carrinho as $modeloId => $qtde) {

        if ($modeloId !== $NO_HAVE) {
            $rs = null;
            $filtro['ogpm_ativo'] = 1;
            $filtro['ogpm_id'] = $modeloId;
            $filtro['com_produto'] = true;
            $instProdutoModelo = new ProdutoModelo;
            $ret = $instProdutoModelo->obter($filtro, null, $rs);

            if ($rs && pg_num_rows($rs) != 0) {
                $rs_row = pg_fetch_array($rs);
                if ($rs_row['ogp_pin_request'] > 0) {
                    $permitidoBoletoExpress = FALSE;
                }//end if($rs_row['ogp_pin_request'] > 0) 
            }//end if($rs && pg_num_rows($rs) != 0)
        }//end if($modeloId !== $NO_HAVE)
        else {
            $rs = null;
            $filtro['ogp_ativo'] = 1;
            $filtro['ogp_id'] = key($qtde);
            $filtro['ogp_mostra_integracao_com_loja'] = '1';
            $filtro['opr'] = 1;
            $ret = (new Produto)->obtermelhorado($filtro, null, $rs);
            if ($rs && pg_num_rows($rs) != 0) {
                $rs_row = pg_fetch_array($rs);
                if ($rs_row['ogp_pin_request'] > 0) {
                    $permitidoBoletoExpress = FALSE;
                }//end if($rs_row['ogp_pin_request'] > 0) 
            }//end if($rs && pg_num_rows($rs) != 0)
        }//end else do if($modeloId !== $NO_HAVE)
    }//end foreach
} else {
    $msg = "Sua sessão expirou e os produtos de seu carrinho foram perdidos. Por favor, selecione o(s) produto(s) desejado(s) novamente. Obrigado!";
    $titulo = "Erro - Carrinho Vazio";
    $link = "/game/index.php";
    ?>
    <div class="container txt-azul-claro bg-branco p-bottom40">
        <div class="col-md-12 top20 txt-vermelho">
            <?php echo str_replace("\n", "<br>", $msg); ?>
        </div>
    </div>
    <script src="/js/valida.js"></script>
    <script>
        manipulaModal(1, '<?php echo str_replace("\n", "<br>", $msg); ?>', '<?php echo $titulo; ?>');
    </script>

    <?php
    if (!empty($link)) {
        ?>
        <script>
            $('#modal-load').on('hidden.bs.modal', function () {
                location.href = '<?php echo $link; ?>'
            });
        </script>
        <?php
    }
    echo "</div></div>";
    require_once DIR_WEB . "game/includes/footer.php";
    die();
}
?>
<div class="container txt-cinza bg-branco  p-bottom40">
    <div class="row top10">
        <div class="col-md-12 txt-verde top10">
            <span class="glyphicon glyphicon-triangle-right graphycon-big pull-left" aria-hidden="true"></span><strong
                class="pull-left">
                <h4 class="top20">acesse sua conta</h4>
            </strong>
        </div>
        <div class="clearfix"></div>
        <div class="col-md-6 borda-colunas-formas-pagamento">
            <form id="formLogin" method="post">
                <div class="row top20 form-group">
                    <div class="col-md-6 text-right-lg text-right-md text-left-sm text-left-xs">
                        <label for="login">Login:</label>
                    </div>
                    <div class="col-md-6">
                        <input class="form-control" id="login" name="login" placeholder="E-mail" type="text" value="">
                    </div>
                </div>
                <div class="row top10  form-group">
                    <div class="col-md-6  text-right-lg text-right-md text-left-sm text-left-xs">
                        <label for="senha">Senha:</label>
                    </div>
                    <div class="col-md-6">
                        <input class="form-control" id="senha" name="senha" type="password" value="">
                    </div>
                    <div class="col-md-6 col-md-offset-6 fontsize-p">
                        <div style="padding: 0 0 15px 10px; display: flex; justify-content: end;">
                            <div class="g-recaptcha" data-sitekey="6Lc4XtkkAAAAAJrfsAKc99enqDlxXz4uq86FI9_T"></div>
                        </div>
                        <a class="decoration-none txt-cinza"
                            href="/game/conta/esqueci-minha-senha/index.php?redirected=true&origemUsuario=gamer"><em>Esqueci
                                minha senha</em></a>
                    </div>
                    <!-- Modal -->
                    <div id="modal-autenticador">
                    </div>
                    <!-- Modal -->
                    <div id="myModal" class="modal fade" role="dialog">
                        <div class="modal-dialog">
                            <!-- Modal content-->
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    <h4 class="modal-title">Erro de preenchimento.</h4>
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
                </div>
                <div class="row top10 form-group">
                    <div class="col-md-12 dislineblock">
                        <button id="prosseguir" type="button" class="pull-right btn btn-success">Prosseguir</button>
                    </div>
                    <div class="top10 col-md-12 dislineblock">
                        <a href="/game/conta/nova.php" class="pull-right btn btn-info"><strong>Não tem
                                cadastro?</strong></a>
                    </div>
                </div>
            </form>
        </div>
        <div class="clearfix hidden-md hidden-lg"></div>
        <div class="hidden-md hidden-lg">
            <hr>
        </div>
    </div>
</div>
</div>
</div>
<script src="/js/valida.js"></script>
<script src="https://www.google.com/recaptcha/api.js" async="" defer=""></script>
<script>
    $(function () {
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

        <?php
        if ($permitidoBoletoExpress) {
            ?>
            $("#btnBoletoExpress").click(function () {
                var erro = false;

                if ($("#email").val().trim() == "") {
                    $(".modal-body").children().children().html("E-mail precisa ser preenchido.");
                    erro = true;
                } else if (!checkEmail($("#email").val())) {
                    $(".modal-body").children().children().html("E-mail inválido.");
                    erro = true;
                } else {
                    $(".modal-body").children().children().html("");
                }

                if ($("#confirma_email").val() !== $("#email").val()) {
                    $(".modal-body").children().children().append("<br>Confirmação de e-mail precisa ser igual a e-mail.");
                    erro = true;
                }

                if (erro) {
                    $('#myModal').modal('show');
                    return false;
                } else {
                    return true;
                }

            });

            <?php
        }//end if($permitidoBoletoExpress) 
        ?>
    });
</script>
<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 bg-info align-center borda-direita-basica p-bottom10">
        <h3>
            Quer ser um ponto de venda ?
            <a href="/creditos/cadastro.php" class="txt-branco" target="_blank"><b><span
                        class="link-destaque">Cadastre-se</span></b></a>
            ou
            <a href="https://e-prepagpdv.com.br/" class="txt-branco" target="_blank"><b><span
                        class="link-destaque">saiba mais</span></b></a>.
        </h3>
    </div>
</div>
</div>
<?php
require_once DIR_WEB . "game/includes/footer.php";
