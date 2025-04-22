<?php
require_once "../../../includes/constantes.php";
require_once DIR_CLASS . "gamer/controller/HeaderController.class.php";
require_once DIR_INCS . "functions.php";

$posicao = "Inferior Internas";
$controller = new HeaderController;
$banners = $controller->getBanner($posicao);

if (!$controller || !$controller->usuario) {
    header("Location: /game/conta/login.php");
}

$controller->setHeader();

if (isset($_POST['envio']) && $_POST['envio'] == 1) {
    $arrCel = explode(" ", $_POST['cel']);
    $celDDD = str_replace(array("(", ")"), "", $arrCel[0]);
    $cel = str_replace("-", "", $arrCel[1]);

    // Define a lista de caracteres permitidos como expressão regular
    $pattern = "/^[a-zA-Z0-9\s\(\)\-\.\/,]+$/";

    if (trim($_POST['nome']) == "") {
        $msgErrors .= "Problema com o campo Nome.<br>";
    } elseif (!preg_match($pattern, $_POST['nome'])) {
        $msgErrors .= "O campo Nome contém caracteres inválidos.<br>";
    }

    if (trim($_POST['endereco']) == "") {
        $msgErrors .= "Problema com o campo Logradouro.<br>";
    } elseif (!preg_match($pattern, $_POST['endereco'])) {
        $msgErrors .= "O campo Logradouro contém caracteres inválidos.<br>";
    }

    if (trim($_POST['numero']) == "") {
        $msgErrors .= "Problema com o campo Numero.<br>";
    } elseif (!preg_match($pattern, $_POST['numero'])) {
        $msgErrors .= "O campo Numero contém caracteres inválidos.<br>";
    }

    if (trim($_POST['bairro']) == "") {
        $msgErrors .= "Problema com o campo Bairro.<br>";
    } elseif (!preg_match($pattern, $_POST['bairro'])) {
        $msgErrors .= "O campo Bairro contém caracteres inválidos.<br>";
    }

    if (trim($_POST['cidade']) == "") {
        $msgErrors .= "Problema com o campo Cidade.<br>";
    } elseif (!preg_match($pattern, $_POST['cidade'])) {
        $msgErrors .= "O campo Cidade contém caracteres inválidos.<br>";
    }

    if (trim($_POST['estado']) == "") {
        $msgErrors .= "Problema com o campo Estado.<br>";
    } elseif (!preg_match($pattern, $_POST['estado'])) {
        $msgErrors .= "O campo Estado contém caracteres inválidos.<br>";
    }

    if (trim($_POST['sexo']) == "") {
        $msgErrors .= "Problema com o campo Sexo.<br>";
    } elseif (!preg_match($pattern, $_POST['sexo'])) {
        $msgErrors .= "O campo Sexo contém caracteres inválidos.<br>";
    }


    if ($msgErrors == "") {
        $controller->usuario->setNome(fix_name($_POST['nome']));
        $controller->usuario->setSexo(trim($_POST['sexo']));
        $controller->usuario->setCep(str_replace("-", "", $_POST['cep']));
        $controller->usuario->setEstado(trim($_POST['estado']));
        $controller->usuario->setTelDDI('55');
        $controller->usuario->setTelDDD('');
        $controller->usuario->setTel('');
        $controller->usuario->setCelDDI('55');
        $controller->usuario->setCelDDD(trim($celDDD));
        $controller->usuario->setCel(trim($cel));
        $controller->usuario->setCidade(trim($_POST['cidade']));
        $controller->usuario->setBairro(trim($_POST['bairro']));
        $controller->usuario->setEndereco(trim($_POST['endereco']));
        $controller->usuario->setNumero(trim($_POST['numero']));
        $controller->usuario->setComplemento(trim($_POST['complemento']));

        $retorno = $controller->usuario->atualizarMelhorado(true);
    } else {
        $retorno = false;
    }

    if ($retorno === true) {
        $controller->usuario = unserialize($_SESSION['usuarioGames_ser']);
        $modalRetorno = "manipulaModal(2,'Seus dados foram alterados com sucesso.','Operação concluída.');";
    } else {
        $modalRetorno = "manipulaModal(1,'" . str_replace("\n", "<br>", $msgErrors) . "','Erro.');";
    }

    unset($_POST);
}

//$readonly_cidade = (trim($controller->usuario->getCidade()) == "")?"":"readonly='readonly'";
//$readonly_estado = (trim($controller->usuario->getEstado()) == "")?"":"readonly='readonly'";
//$readonly_bairro = (trim($controller->usuario->getBairro()) == "")?"":"readonly='readonly'";
//$readonly_logradouro = (trim($controller->usuario->getEndereco()) == "")?"":"readonly='readonly'";

?>
<script type="text/javascript" src="/js/jquery.mask.min.js"></script>
<script src="/js/valida.js"></script>
<script src="/js/validaSenha.js"></script>
<script>
    <?php
    if (isset($modalRetorno)) {
        print $modalRetorno;
    }
    ?>
    $(function() {
        var searching = false;

        $("#cpf")
            .focus(function() {
                $("#cpf").mask("999.999.999-99");
            })
            .blur(function() {
                if (!validaCpf($(this).val())) {
                    manipulaModal(1, "Cpf inválido, por favor, digite novamente.", "Erro");
                    $(this).val("");
                }
            });

        $("#cel").mask('(00) 90000-0000');

        $("#dataNascimento").mask("99/99/9999");

        $("#cep")
            .mask("99999-999")
            .blur(function() {
                var cep = $(this).val();

                if (cep.length == 9 && !searching) {
                    $.ajax({
                        type: "POST",
                        url: "/includes/cep.php",
                        data: "cep=" + cep,
                        beforeSend: function() {
                            searching = true;
                            waitingDialog.show('Por favor, aguarde...', {
                                dialogSize: 'sm'
                            });
                        },
                        success: function(txt) {
                            searching = false;
                            waitingDialog.hide();

                            if (txt.search("NO_ACCESS") == -1) {

                                if (txt.search("ERRO") == -1) {
                                    txt = txt.split("&");

                                    document.getElementById("endereco").value = txt[0].trim() + ' ' + txt[1].trim();
                                    document.getElementById("bairro").value = txt[2].trim();
                                    document.getElementById("cidade").value = txt[3].trim();
                                    document.getElementById("estado").value = txt[4].trim();

                                    if (txt[1].trim() != "" || txt[2].trim() != "") {
                                        if (txt[1].trim() != "")
                                            $("#endereco").attr("readonly", "readonly");

                                        if (txt[2].trim() != "")
                                            $("#bairro").attr("readonly", "readonly");

                                        document.getElementById("numero").focus();
                                    } else {
                                        $("#bairro").removeAttr("readonly");
                                        $("#endereco").removeAttr("readonly");
                                        document.getElementById("bairro").focus();
                                    }
                                } else {
                                    document.getElementById("endereco").value = "";
                                    document.getElementById("bairro").value = "";
                                    document.getElementById("cidade").value = "";
                                    document.getElementById("estado").value = "";
                                    manipulaModal(1, "CEP Inexistente!", "Atenção");
                                }
                            } else {
                                document.getElementById("endereco").value = "";
                                document.getElementById("bairro").value = "";
                                document.getElementById("cidade").value = "";
                                document.getElementById("estado").value = "";
                                manipulaModal(1, "<strong>[ERRO 404]</strong> - Não foi possível consultar o CEP, tente novamente mais tarde.<br>Por favor, relate o problema ao <a href='/support' alt='Suporte' title='Suporte'  target='_blank'>Suporte</a><br>Obrigado!", "Consulta de CEP indisponível no momento");
                            }

                        },
                        error: function(jqXHR, textStatus) {
                            if (textStatus === 'timeout') {
                                waitingDialog.hide();
                                manipulaModal(1, "<strong>[ERRO 404]</strong> - Não foi possível consultar o CEP, tente novamente mais tarde.<br>Por favor, relate o problema ao <a href='/support' alt='Suporte' title='Suporte'  target='_blank'>Suporte</a><br>Obrigado!", "Consulta de CEP indisponível no momento");
                            } else {
                                waitingDialog.hide();
                                manipulaModal(1, "<strong>[ERRO 400]</strong> - Erro no servidor.<br>Por favor, relate o problema ao <a href='/support' alt='Suporte' title='Suporte'  target='_blank'>Suporte</a><br>Obrigado!", "Consulta de CEP indisponível no momento");
                            }
                        },
                        timeout: 30000
                    });

                } else {
                    manipulaModal(1, "CEP Inválido!", "Atenção");
                    document.getElementById("endereco").value = "";
                    document.getElementById("bairro").value = "";
                    document.getElementById("cidade").value = "";
                    document.getElementById("estado").value = "";
                }
            });

        $("#editar").click(function() {
            waitingDialog.show('Por favor, aguarde...', {
                dialogSize: 'sm'
            });

            setTimeout(function() {
                if (valida()) {
                    if (typeof $("#cpf").val() != "undefined") {
                        $.ajax({
                            type: "POST",
                            dataType: "JSON",
                            url: "/ajax/ajaxCpf.php",
                            data: {
                                cpf: $("#cpf").val(),
                                dataNascimento: $("#dataNascimento").val()
                            },
                            success: function(txt) {

                                if (txt.erros.length > 0) {
                                    manipulaModal(1, txt.erros, "Erro");
                                    waitingDialog.hide();
                                    return false;
                                } else {
                                    $("#nome_cpf").val(txt.nome);
                                    $("#edita-cadastro").submit();
                                }
                            },
                            error: function() {
                                waitingDialog.hide();
                                manipulaModal(1, "Erro desconhecido, favor entrar em contato com o nosso suporte.", "Erro");
                                return false;
                            }
                        });
                    } else {
                        $("#edita-cadastro").submit();
                    }

                } else {

                    waitingDialog.hide();
                }
            }, 300);

        });
    });
</script>
<div class="container txt-cinza bg-branco  p-bottom40">
    <div class="row top20">
        <div class="col-md-3 txt-azul-claro">
            <div class="row">
                <span class="glyphicon glyphicon-triangle-right graphycon-big pull-left" aria-hidden="true"></span><strong class="pull-left">
                    <h4 class="top20">Minha Conta</h4>
                </strong>
            </div>
            <div class="row">
                <?php require_once RAIZ_DO_PROJETO . "public_html/game/includes/menu-carteira.php" ?>
            </div>
        </div>
        <div class="col-md-9">
            <div class="row">
                <strong class="pull-left txt-azul-claro p-left15 top20">MEU CADASTRO</strong>
            </div>
            <form method="post" id="edita-cadastro" class="text-right-lg text-right-md text-left-sm text-left-xs top20">
                <input type="hidden" name="nome_cpf" id="nome_cpf">
                <input type="hidden" name="envio" id="envio" value="1">
                <?php
                $login = $controller->usuario->getLogin();

                if (strlen($login) > 4) {
                ?>
                    <div class="row">
                        <div class="col-md-3 ">
                            <label for="login">Login:</label>
                        </div>
                        <div class="col-md-4 text-left-md text-left-lg">
                            <?php echo $login; ?>
                        </div>
                    </div>
                <?php

                }
                ?>
                <div class="row top10">
                    <div class="col-md-3 ">
                        <label for="email">E-mail:</label>
                    </div>
                    <div class="col-md-6 text-left-md text-left-lg">
                        <?php echo $controller->usuario->getEmail(); ?>
                    </div>
                </div>
                <div class="row top10">
                    <div class="col-md-3 ">
                        <label for="nome">Nome completo:</label>
                    </div>
                    <div class="col-md-4 text-left-md">
                        <input type="text" id="nome" name="nome" char="2" class="form-control" value="<?php echo htmlspecialchars($controller->usuario->getNome(), ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                </div>
                <div class="row top10">
                    <div class="col-md-3 ">
                        <label for="dataNascimento">Data de nascimento:</label>
                    </div>
                    <div class="col-md-2 text-left">
                        <?php
                        $dataNascimento = substr(trim($controller->usuario->getDataNascimento()), 0, 10);

                        $cpf = trim($controller->usuario->getCpf());

                        $editaCpf = (strlen($cpf) == 14 && strlen($dataNascimento) == 10) ? false : true;

                        if (!$editaCpf) {
                            echo htmlspecialchars($dataNascimento, ENT_QUOTES, 'UTF-8');
                        } else {
                        ?>
                            <input type="text" id="dataNascimento" name="dataNascimento" char="10" class="form-control" value="">
                        <?php
                        }
                        ?>
                    </div>
                </div>
                <div class="row top10">
                    <div class="col-md-3 ">
                        <label for="cpf">CPF:</label>
                    </div>
                    <div class="col-md-4 text-left">
                        <?php


                        if (!$editaCpf) {
                            echo htmlspecialchars($cpf, ENT_QUOTES, 'UTF-8');
                        } else {
                        ?>
                            <input type="text" class="form-control" maxlength="14" char="14" id="cpf" name="cpf" value="">
                        <?php
                        }
                        ?>

                    </div>
                </div>
                <div class="row top10">
                    <div class="col-md-3 ">
                        <label for="sexo">Sexo: </label>
                    </div>
                    <div class="col-md-4">
                        <select class="form-control" name="sexo" id="sexo" char="1">
                            <option value="">--</option>
                            <option value="M" <?php if ($controller->usuario->getSexo() == "M") print "selected"; ?>>Masculino</option>
                            <option value="F" <?php if ($controller->usuario->getSexo() == "F") print "selected"; ?>>Feminino</option>
                        </select>
                    </div>
                </div>
                <div class="row top10">
                    <div class="col-md-3 ">
                        <label for="cel">Celular:</label>
                    </div>
                    <div class="col-md-3">
                        <input type="text" id="cel" name="cel" char="14" maxlength="15" class="form-control" value="<?php echo htmlspecialchars("(" . $controller->usuario->getCelDDD() . ") " . $controller->usuario->getCel(), ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                </div>
                <div class="row top10">
                    <div class="col-md-3 ">
                        <label for="cep">CEP:</label>
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control" id="cep" name="cep" maxlength="9" char="9" value="<?php echo htmlspecialchars($controller->usuario->getCep(), ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                </div>
                <div class="row top10">
                    <div class="col-md-3 ">
                        <label for="estado">Estado:</label>
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control" id="estado" readonly="readonly" char="2" name="estado" maxlength="2" value="<?php echo htmlspecialchars($controller->usuario->getEstado(), ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                </div>
                <div class="row top10">
                    <div class="col-md-3 ">
                        <label for="cidade">Cidade:</label>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" id="cidade" readonly="readonly" char="2" name="cidade" value="<?php echo htmlspecialchars($controller->usuario->getCidade(), ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                </div>
                <div class="row top10">
                    <div class="col-md-3 ">
                        <label for="bairro">Bairro:</label>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" readonly="readonly" id="bairro" char="2" name="bairro" value="<?php echo htmlspecialchars($controller->usuario->getBairro(), ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                </div>
                <div class="row top10">
                    <div class="col-md-3 ">
                        <label for="endereco">Logradouro:</label>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" id="endereco" readonly="readonly" char="2" name="endereco" value="<?php echo htmlspecialchars($controller->usuario->getEndereco(), ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                </div>
                <div class="row top10">
                    <div class="col-md-3 ">
                        <label for="numero">Número:</label>
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control" id="numero" char="1" name="numero" value="<?php echo htmlspecialchars($controller->usuario->getNumero(), ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                </div>
                <div class="row top10">
                    <div class="col-md-3 ">
                        <label for="complemento">Complemento:</label>
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control" id="complemento" char="0" name="complemento" value="<?php echo htmlspecialchars($controller->usuario->getComplemento(), ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                </div>
                <div class="row top10">
                    <div class="col-md-4 col-md-offset-3 text-left">
                        <a href="javascript:void(0);" id="editar" class="btn btn-success">Salvar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php
    if (!empty($banners)) {
    ?>
        <div class="col-md-12 top10">
            <a href='<?php echo $banners[0]->link; ?>' target="_blank">
                <img title="<?php echo $banners[0]->titulo; ?>" alt="<?php echo $banners[0]->titulo; ?>" class="img-responsive" src="<?php echo $controller->objBanners->urlLink . $banners[0]->imagem; ?>">
            </a>
        </div>
    <?php
    }
    ?>
</div>
</div>
<?php
require_once RAIZ_DO_PROJETO . "public_html/game/includes/footer.php";
