<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
require_once "../../../includes/constantes.php";
require_once DIR_CLASS . "pdv/controller/ProdutosController.class.php";
require_once DIR_CLASS . "gamer/classConversionPINsEPP.php";
header("Content-Type: text/html; charset=ISO-8859-1", true);

$qtdFeedsIndex = 5;
$controller = new ProdutosController;

if (isset($_GET['token'])) {

    $objEncryption = new Encryption();
    $token = unserialize($objEncryption->decrypt($_GET['token']));
    $_POST["prod"] = $token['produto'];
}

if (!isset($_POST["prod"]) || $_POST["prod"] == "")
    $controller->accessDenied();

if ($GLOBALS['_SESSION']["dist_usuarioGames_ser"]) {
    $sistema = "pdv";
    $ug_id = $controller->usuarios->getId();
} else {
    $sistema = "gamer";
    $ug_id = ($controller->usuario) ? $controller->usuario->getId() : "7909";
}

$ogp_id = $_POST["prod"];

$sqlClickProduto = "insert into clicks (sistema, ug_id, ogp_id) values (:sistema, :ug_id, :ogp_id)";
//Conectando com PDO para execução da QUERY
$con = ConnectionPDO::getConnection();
$pdo = $con->getLink();
$stmt = $pdo->prepare($sqlClickProduto);
$stmt->bindParam(':sistema', $sistema, PDO::PARAM_STR);
$stmt->bindParam(':ug_id', $ug_id, PDO::PARAM_INT);
$stmt->bindParam(':ogp_id', $ogp_id, PDO::PARAM_INT);
$stmt->execute();
//insert

$produto = $controller->getProdutoValor($_POST["prod"]);
$modelos = $produto->getModelo();

///prepag2/dist_commerce/images/produtos/
require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/header.php";
?>
<style>
    .produto-selecionado {
        border: 1px solid rgb(100, 100, 100, 0.5) !important;
        position: relative;
        transition: border 0.2s, box-shadow 0.2s;
    }

    .triangulo-produto-selecionado {
        position: absolute;
        bottom: 0;
        right: 0;
        z-index: 10;
        border-left: 11px solid transparent;
        border-top: 11px solid transparent;
        border-bottom: 11px solid green;
        border-right: 11px solid green;
    }

    .icone-produto-selecionado {
        position: absolute;
        bottom: 3px;
        right: 3px;
        top: auto !important;
        font-size: 9px;
        color: #F0F0F0;
        z-index: 10;
    }

    .bg-comprar:hover .txt-azul-claro2,
    .bg-comprar:hover .txt-verde {
        color: #fff;
    }

    .bg-comprar .txt-azul-claro2 {
        color: #478ee6;
        transition: color 0.1s ease;
    }

    .bg-comprar .txt-verde {
        color: #009b4a;
        transition: color 0.1s ease;
    }

    .btn-carrinho {
        gap: 6px;
        display: flex;
        align-items: center;
        background-color: #5cb85c10;
        color: #4E9C4E;
        border-color: #4cae4c;
    }

    .btn-carrinho:hover {
        color: #408040 !important;
        background-color: #4E9C4E15;
    }

    #seleciona {
        margin-top: 15px;
        margin-right: -15px;
        display: flex;
        justify-content: end;
        gap: 20px;
    }

    #btn-finalizar-selecao,
    #btn-adicionar-carrinho {
        border-radius: 0;
        font-style: normal;
        padding: 10px 20px;
        font-size: 15px;
        box-shadow: 3px 3px 5px rgb(0, 0, 0, 0.2);
        margin-top: 10px;
    }
</style>
<div class="container txt-azul-claro bg-branco">
    <div class="row">
        <div class="col-md-10">
            <div class="row">
                <div class="col-md-12 espacamento">
                    <strong>Selecione o valor. carrinho: <php? print_r()?></strong>
                </div>
            </div>
            <div class="row txt-cinza espacamento right10 borda-fina">
                <div class="col-md-3">

                    <?php
                    if ($produto->getNomeImagem() && $produto->getNomeImagem() != "" && file_exists($GLOBALS['FIS_DIR_IMAGES_PRODUTO'] . $produto->getNomeImagem())) {
                    ?>
                        <p class="bottom0"><img border="0" style="max-width: 100%;" src="<?php echo $GLOBALS['URL_DIR_IMAGES_PRODUTO'] . $produto->getNomeImagem() ?>"></p>
                    <?php
                    }
                    ?>
                    <p class="txt-azul-claro bottom0 top20"><strong><?php echo $produto->getNome(); ?> </strong></p>
                    <p class="txt-azul-claro bottom0 p-top10">Publisher: <span class="txt-cinza"><?php echo $produto->getNomeOperadora(); ?></span></p>

                    <?php
                    if (!$produto->getMostraIntegracao()) {
                    ?>
                        <p class="p-top10"><?php echo $produto->getDescricao(); ?></p>
                    <?php
                    }
                    ?>
                </div>
                <div class="col-md-9">
                    <?php
                    if (is_null($produto->getValorMinimo()) && is_null($produto->getValorMaximo())) {
                        if (is_array($modelos)) {
                            foreach ($modelos as $modelo) {
                                if ($_SERVER["REMOTE_ADDR"] == "187.18.199.172" && $modelo->getId() == 1424) {
                    ?>
                                    <div class="row top10">
                                        <div class="col-md-5">
                                            <p class="txt-cinza p-top10"><strong><?php echo $modelo->getNome(); ?></strong></p>
                                        </div>
                                        <div class="col-md-7 bg-comprar p-top10 nome-produto">
                                            <?php
                                            if ($modelo->contar($produto->getOprCodigo(), $modelo->getPinValor()) > 0 || $produto->getPinRequest() > 0) {
                                            ?>
                                                <span class="c-pointer" id="<?php echo $modelo->getId(); ?>">
                                                    <div class="col-md-6 txt-azul-claro2">
                                                        <p class="pull-left "><strong>R$ <?php echo number_format($modelo->getValor(), 2, ',', '.') ?></strong></p>
                                                    </div>
                                                    <div class="col-md-6 txt-verde">
                                                        <p class="pull-right">
                                                            <strong><em>Comprar</em></strong>
                                                        </p>
                                                    </div>
                                                </span>
                                            <?php
                                            } else {
                                            ?>
                                                <p class="pull-right txt-vermelho"><strong><em>Fora de Estoque</em></strong></p>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                    </div>
                                <?php
                                } else if ($modelo->getId() != 1424) {
                                ?>

                                    <div class="row top10">
                                        <div class="col-md-5">
                                            <p class="txt-cinza p-top10"><strong><?php echo $modelo->getNome(); ?></strong></p>
                                        </div>
                                        <div class="col-md-7 bg-comprar c-pointer p-top10 nome-produto">
                                            <?php
                                            if ($modelo->contar($produto->getOprCodigo(), $modelo->getPinValor()) > 0 || $produto->getPinRequest() > 0) {
                                            ?>
                                                <span id="<?php echo $modelo->getId(); ?>">
                                                    <div class="col-md-6 txt-azul-claro2">
                                                        <p class="pull-left "><strong>R$ <?php echo number_format($modelo->getValor(), 2, ',', '.') ?></strong></p>
                                                    </div>
                                                    <div class="col-md-6 txt-verde">
                                                        <p class="pull-right">
                                                            <strong><em>Comprar</em></strong>
                                                        </p>
                                                    </div>
                                                </span>
                                            <?php
                                            } else {
                                            ?>
                                                <p class="pull-right txt-vermelho"><strong><em>Fora de Estoque</em></strong></p>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                    </div>


                            <?php
                                }
                            }


                            //$urlTest = ($_SERVER["REMOTE_ADDR"] == "201.93.162.169")? "sms.php":"produtos_selecionados.php";
                            $urlTest = "produtos_selecionados.php";

                            ?>
                            <form id="seleciona" method="post" action="<?php echo $urlTest; ?>">
                                <input type="hidden" name="acao" id="acao" value="a">
                                <input type="hidden" name="mod" id="mod" value="">
                                <input type="hidden" name="valor" id="valor_hidden" value="">
                                <input type="hidden" name="codeProd" id="codeProd" value="<?php echo $produto->getId()  ?>">
                                <button type='button' id='btn-adicionar-carrinho' class='btn btn-carrinho'><i style="font-size: 17px; position: inherit;" class="glyphicon glyphicon-shopping-cart"></i> Adicionar ao carrinho</button>
                                <button type='submit' id='btn-finalizar-selecao' class='btn btn-success' disabled>Comprar agora</button>
                            </form>
                        <?php
                        } elseif ($produto->getMostraIntegracao() == 1) {
                        ?>
                            <div class="row top10">
                                <?php echo $produto->getDescricao(); ?>
                            </div>
                        <?php
                        } else {
                        ?>
                            <div class="row top10">
                                <p class="pull-right txt-vermelho"><strong><em>Não existem modelos cadastrados para este produto.</em></strong></p>
                            </div>
                        <?php
                        }
                    } else {

                        ?>

                        <div class="row top10 align-center">
                            <p class=""><strong>Informe o valor desejado de acordo com os valores máximo e mínimo informados</strong></p>
                            <div class="error-list">

                            </div>
                        </div>
                        <div class="row top10">
                            <div class="col-xs-12 col-sm-offset-2 col-sm-8 col-md-offset-2 col-md-8 col-lg-offset-2 col-lg-8 align-center">
                                <div class="col-sm-12 col-md-6 col-lg-6 top10" id="input-valor">
                                    <div class="form-group align-center">
                                        <div class="input-group align-center">
                                            <div class="input-group-addon">R$</div>
                                            <input type="number" class="form-control align-right" id="valor" name="valor" min="<?php echo $produto->getValorMinimo(); ?>" max="<?php echo $produto->getValorMaximo(); ?>" value="<?php echo number_format($produto->getValorMinimo(), 0); ?>" onkeypress="return event.charCode >= 48 && event.charCode <= 57" required>
                                            <div class="input-group-addon">.00</div>
                                        </div>

                                    </div>
                                </div>
                                <form class="form-inline c-pointer modelo-produto" estoque="1" id="<?php echo $NO_HAVE; ?>">

                                    <div class="row p-left10 p-right10 bg-comprar">
                                        <div>
                                            <div class="col-sm-12 col-md-3 col-lg-3 top15 align-center">
                                            </div>
                                            <div class="col-sm-12 col-md-3 col-lg-3 mt-md-15 pb-sm-15">
                                                <div class="" estoque="1" id="<?php echo $NO_HAVE ?>">
                                                    <strong class="txt-verde-escuro"><em>Comprar</em></strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row top10">
                                        <div class="col-lg-12 align-center">
                                            <span>Valor mínimo: <?php echo $produto->getValorMinimo(); ?> | Valor máximo: <?php echo $produto->getValorMaximo(); ?></span>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <?php
                        //$urlTest = ($_SERVER["REMOTE_ADDR"] == "201.93.162.169")? "sms.php":"produtos_selecionados.php";
                        $urlTest = "produtos_selecionados.php";

                        /* $se_for_get = "<form id='seleciona' method='get' action='". $urlTest . "'>
                    <input type='hidden' name='acao' id='acao' value='a'>
                    <input type='hidden' name='mod' id='mod' value=''>
                    <input type='hidden' name='valor' id='valor_hidden' value=''>
                    <input type='hidden' name='codeProd' id='codeProd' value='" . $produto->getId() . "'>
                </form>";
					$se_for_post = "<form id='seleciona' method='post' action='". $urlTest . "'>
                    <input type='hidden' name='acao' id='acao' value='a'>
                    <input type='hidden' name='mod' id='mod' value=''>
                    <input type='hidden' name='valor' id='valor_hidden' value=''>
                    <input type='hidden' name='codeProd' id='codeProd' value='" . $produto->getId() . "'>
                </form>";
				if ($_SERVER["REMOTE_ADDR"] == "201.93.162.169"){
					echo $se_for_get;
				} else {
					echo $se_for_post;
				}*/
                        ?>
                        <form id='seleciona' method='post' action='<?php echo $urlTest; ?>'>
                            <input type='hidden' name='acao' id='acao' value='a'>
                            <input type='hidden' name='mod' id='mod' value=''>
                            <input type='hidden' name='valor' id='valor_hidden' value=''>
                            <input type='hidden' name='codeProd' id='codeProd' value='<?php echo $produto->getId(); ?>'>
                        </form>
                    <?php
                    }
                    ?>
                </div>
            </div>
        </div>
        <div class="col-md-2 p-top10">
            <div class="row pull-right p-8">
                <p class="txt-azul-claro"><strong>Dúvidas ou problemas para concluir a venda?</strong></p>
                <p class="txt-cinza">Por favor, avise-nos entrando em contato com o nosso <a href="/game/suporte.php" target="_blank">suporte</a>.</p>
            </div>
        </div>
    </div>
</div>
<script>
    $(function() {

        $(".bg-comprar").click(function() {
            // Remove a borda de seleção e ícone de todos os produtos
            $(".bg-comprar").removeClass("produto-selecionado");
            $(".bg-comprar .icone-produto-selecionado").remove();
            $(".bg-comprar .triangulo-produto-selecionado").remove();

            // Adiciona a borda de seleção ao produto clicado
            $(this).addClass("produto-selecionado");

            // Adiciona um ícone visual de seleção (check)
            if ($(this).find(".icone-produto-selecionado").length === 0) {
                $(this).append('<div class="triangulo-produto-selecionado"></div><i class="icone-produto-selecionado glyphicon glyphicon-ok"></i>');
            }

            // Pega o produto selecionado
            var produtoSelecionado = $(this).find("span").attr("id");

            // Atualiza o campo hidden do form, mas NÃO faz submit ainda
            $("#mod").val(produtoSelecionado);

            //Mostrar o botão "Comprar"
            $("#btn-finalizar-selecao").prop("disabled", false);
        });

        $(".modelo-produto").hover(
            function() {
                $(this).children(".txt-azul-claro2").css("color", "#fff");
                $(this).children(".txt-verde").css("color", "#fff");
            },
            function() {
                $(this).children(".txt-azul-claro2").css("color", "#478ee6");
                $(this).children(".txt-verde").css("color", "##009b4a");
            }
        ).click(function() {

            if ($(this).attr("estoque") == "1") {
                console.log("redir");
                $("#mod").val($(this).attr("id"));
                if ($("#valor").length && $("#valor").val() === "") {
                    var html = "<p class='txt-vermelho'>Por favor, informe um valor no campo!</p>";
                    $(".error-list").html(html);
                    return;
                }
                $('#valor_hidden').val($("#valor").val());
                $("#seleciona").submit();
            }
        });

        $(".prod").click(function() {
            var id = $(this).attr("id");
            $("#prod").val(id);
            $("#detalhe").submit();
        });

        $("#valor").change(function() {
            var min = parseInt($("#valor").attr("min"));
            var max = parseInt($("#valor").attr("max"));
            var valor = parseInt($("#valor").val());
            if (valor < min) {

                var html = "<p class='txt-vermelho'>O valor " + valor + " não esta dentro do mínimo e máximo específicado. Por favor, insira um valor entre " + min + " e " + max + "!</p>";
                $("#valor").val(min);
                $(".error-list").html(html);
            } else if (valor > max) {
                var html = "<p class='txt-vermelho'>O valor " + valor + " não esta dentro do mínimo e máximo específicado. Por favor, insira um valor entre " + min + " e " + max + "!</p>";
                console.log(valor);
                $("#valor").val(max);
                $(".error-list").html(html);
            } else {
                var html = "R$" + valor + ",00";
                $.post("/game/ajax/epp_info.php", {
                        valor: valor,
                    },
                    function(data) {
                        var html = data;
                        $(".span-valor").html(html);
                    });
                $(".error-list").html("");
            }
        });
    });
</script>
<?php
require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/footer.php";
?>