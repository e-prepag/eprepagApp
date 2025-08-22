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

    #btn-adicionar-carrinho {
        gap: 6px;
        display: flex;
        align-items: center;
        background-color: #5cb85c10;
        color: #4E9C4E;
        border-color: #4cae4c;
    }

    #btn-adicionar-carrinho:hover {
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

    .btn-custom {
        display: inline-block;
        margin-bottom: 0;
        line-height: 1.42857143;
        text-align: center;
        white-space: nowrap;
        vertical-align: middle;
        -ms-touch-action: manipulation;
        touch-action: manipulation;
        cursor: pointer;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
        background-image: none;
        border: 1px solid transparent;
        border-radius: 0;
        font-style: normal;
        padding: 10px 20px;
        font-size: 15px;
        box-shadow: 3px 3px 5px rgb(0, 0, 0, 0.2);
        margin-top: 10px;
    }

    .btn-custom.disabled,
    .btn-custom[disabled],
    fieldset[disabled] .btn-custom {
        pointer-events: none;
        cursor: not-allowed;
        filter: alpha(opacity=65);
        -webkit-box-shadow: none;
        box-shadow: none;
        opacity: .65
    }


    #confirmModal .modal-content {
        border-radius: 18px;
        overflow: hidden;

    }

    #confirmModal .modal-footer {
        border: 0;
        display: flex;
        align-items: center;
        justify-content: end;
        flex-wrap: wrap;
        row-gap: 10px;
    }

    #confirmModal .modal-header {
        /* border: 0; */
        background: linear-gradient(rgb(28, 95, 124) 0%, rgb(47, 121, 153) 100%);
    }

    .product-info {
        display: flex;
        align-items: center;
        gap: 20px;
        padding: 20px;
        background: #f8fafc;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
    }

    .product-details h3 {
        font-size: 18px;
        font-weight: 600;
        color: #1a202c;
        margin: 0 0 5px;
    }

    .product-details p {
        color: #64748b;
        margin: 0 0 8px;
        font-size: 14px;
    }

    .product-price {
        font-size: 20px;
        font-weight: 700;
        color: #478ee6;
        margin: 0;
    }

    .success-icon {
        width: 60px;
        height: 60px;
        background: #4cae4c;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        position: relative;
        z-index: 1;
    }

    .checkmark {
        width: 24px;
        height: 24px;
        stroke: white;
        stroke-width: 3;
        fill: none;
        stroke-linecap: round;
        stroke-linejoin: round;
        animation: checkmark 0.6s ease-in-out 0.3s both;
    }

    @keyframes checkmark {
        0% {
            stroke-dasharray: 0 50;
            stroke-dashoffset: 0;
        }

        100% {
            stroke-dasharray: 50 0;
            stroke-dashoffset: 0;
        }
    }

    #confirmModal .modal-title {
        color: white;
        font-size: 24px;
        font-weight: 700;
        margin: 0;
        position: relative;
        z-index: 1;
    }

    #confirmModal .modal-subtitle {
        color: white;
        font-size: 14px;
        opacity: 0.9;
        margin: 5px 0 0;
        font-weight: 400;
        position: relative;
        z-index: 1;
    }

    #confirmModal .btn {
        height: 55px;
        flex: 0 0 auto;
        min-width: 232px;
        padding: 15px 20px;
        border: none;
        border-radius: 0;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    #confirmModal .btn-primary {
        background: #009b4a;
        color: white;
        box-shadow: 0 4px 15px rgba(0, 155, 74, 0.4);
    }

    #confirmModal .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 155, 74, 0.6);
    }

    #confirmModal .btn-secondary {
        background: white;
        color: #64748b;
        border: 2px solid #e2e8f0;
    }

    #confirmModal .btn-secondary:hover {
        background: #f8fafc;
        border-color: #cbd5e0;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(100, 116, 139, 0.2);
    }

    .quantity-selector {
        display: inline-flex;
        align-items: center;
        background: #f8fafc;
        border: 2px solid #e2e8f0;
        border-radius: 0;
        padding: 4px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .quantity-selector:hover {
        border-color: rgb(190, 196, 202);
        box-shadow: 0 0 0 4px rgba(190, 196, 202, 0.1);
    }

    .quantity-selector:focus-within {
        border-color: rgb(190, 196, 202);
        box-shadow: 0 0 0 4px rgba(190, 196, 202, 0.15);
    }

    .quantity-btn {
        width: 44px;
        height: 44px;
        border: none;
        background: white;
        border-radius: 0;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
        color: #64748b;
        font-size: 18px;
        font-weight: 600;
    }

    .quantity-btn:hover {
        background: #e2e8f0;
        /* color: white; */
        transform: scale(1.05);
    }

    .quantity-btn:active {
        transform: scale(0.95);
    }

    .quantity-btn:disabled {
        opacity: 0.4;
        cursor: not-allowed;
        transform: none;
    }

    .quantity-btn:disabled:hover {
        background: white;
        color: #64748b;
    }

    .quantity-input {
        border-top-width: 0;
        border-right-width: 1px;
        border-bottom-width: 0;
        border-left-width: 1px;
        border-style: solid;
        border-color: #e2e8f0e0;
        background: transparent;
        width: 60px;
        text-align: center;
        font-size: 18px;
        font-weight: 600;
        color: #478ee6;
        padding: 12px 8px;
        outline: none;
        -moz-appearance: textfield;
    }

    .quantity-input::-webkit-outer-spin-button,
    .quantity-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .quantity-selector.compact {
        border-radius: 0;
        padding: 1px;
    }

    .quantity-selector.compact .quantity-btn {
        width: 28px;
        height: 28px;
        border-radius: 0;
        font-size: 14px;
        margin: 1px;
    }

    .quantity-selector.compact .quantity-input {
        width: 36px;
        font-size: 14px;
        padding: 4px 2px;
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.1);
        }

        100% {
            transform: scale(1);
        }
    }

    .pulse-animation {
        animation: pulse 0.3s ease;
    }
</style>
<div class="container txt-azul-claro bg-branco">
    <div class="row">
        <div class="col-md-10">
            <div class="row">
                <div class="col-md-12 espacamento">
                    <strong>Selecione o valor. carrinho: <?php print_r($_SESSION['dist_carrinho']) ?></strong>
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
                    ?>
                                <div class="row top10">
                                    <div class="col-md-5">
                                        <p class="txt-cinza p-top10"><strong><?php echo $modelo->getNome(); ?></strong></p>
                                    </div>

                                    <?php
                                    if ($modelo->contar($produto->getOprCodigo(), $modelo->getPinValor()) > 0 || $produto->getPinRequest() > 0) {
                                    ?>
                                        <div class="col-md-7 bg-comprar c-pointer p-top10 nome-produto">
                                            <span id="<?php echo $modelo->getId(); ?>">
                                                <div class="col-md-6 txt-azul-claro2">
                                                    <p class="pull-left "><strong>R$ <?php echo number_format($modelo->getValor(), 2, ',', '.') ?></strong></p>
                                                </div>
                                                <div class="col-md-6 txt-verde">
                                                    <p class="pull-right">
                                                        <strong><em>Selecionar</em></strong>
                                                    </p>
                                                </div>
                                            </span>
                                        </div>
                                    <?php
                                    } else {
                                    ?>
                                        <div class="col-md-7 bg-comprar p-top10 nome-produto">
                                            <p class="pull-right txt-vermelho"><strong><em>Fora de Estoque</em></strong></p>
                                        </div>
                                    <?php
                                    }
                                    ?>

                                </div>


                            <?php
                            }


                            //$urlTest = ($_SERVER["REMOTE_ADDR"] == "201.93.162.169")? "sms.php":"produtos_selecionados.php";
                            $urlTest = "produtos_selecionados.php";

                            ?>
                            <form id="seleciona" method="post" action="<?php echo $urlTest; ?>" style="display: flex; align-items:end; flex-wrap: wrap;">
                                <input type="hidden" name="acao" id="acao" value="u">
                                <input type="hidden" name="mod" id="mod" value="">
                                <input type="hidden" name="valor" id="valor_hidden" value="">
                                <input type="hidden" name="codeProd" id="codeProd" value="<?php echo $produto->getId()  ?>">
                                <div class="quantity-selector compact" data-min="1" data-max="999">
                                    <button type="button" class="quantity-btn" onclick="changeQuantity(this, -1)">-</button>
                                    <input type="text" name='qtde' id='qtde' class="quantity-input" onchange="validateQuantity(this)" value='1'>
                                    <button type="button" class="quantity-btn" onclick="changeQuantity(this, 1)">+</button>
                                </div>
                                <button type='button' id='btn-adicionar-carrinho' class='btn-custom' disabled><i style="font-size: 17px; position: inherit;" class="glyphicon glyphicon-shopping-cart"></i> Adicionar ao carrinho</button>
                                <button type='submit' id='btn-finalizar-selecao' class='btn-custom btn-success' disabled>Comprar agora</button>
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
                                            <input type="number" class="form-control align-right" id="valor" min="<?php echo $produto->getValorMinimo(); ?>" max="<?php echo $produto->getValorMaximo(); ?>" value="<?php echo number_format($produto->getValorMinimo(), 0); ?>" onchange="document.getElementById('valor_hidden').value = this.value;">
                                            <div class="input-group-addon">.00</div>
                                        </div>

                                    </div>
                                </div>
                                <div class="form-inline modelo-produto" estoque="1" id="<?php echo $NO_HAVE; ?>">

                                    <div class="row p-left10 p-right10 bg-comprar">
                                        <div>
                                            <div class="col-sm-12 col-md-6 col-lg-6 mt-md-15 pb-sm-15">
                                                <div style="margin-top: 3px;" estoque="1" id="<?php echo $NO_HAVE ?>">
                                                    <strong class="txt-verde-escuro"><em>Digite o valor</em></strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row top10">
                                        <div class="col-lg-12 align-center">
                                            <span>Valor mínimo: <?php echo $produto->getValorMinimo(); ?> | Valor máximo: <?php echo $produto->getValorMaximo(); ?></span>
                                        </div>
                                    </div>
                                </div>
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
                        <form id='seleciona' method='post' action='<?php echo $urlTest; ?>' style="display: flex; align-items:end; flex-wrap: wrap;">
                            <input type='hidden' name='acao' id='acao' value='u'>
                            <input type='hidden' name='mod' id='mod' value='NO HAVE'>
                            <input type='hidden' name='valor' id='valor_hidden' value=''>
                            <input type='hidden' name='codeProd' id='codeProd' value='<?php echo $produto->getId(); ?>'>
                            <div class="quantity-selector compact" data-min="1" data-max="999">
                                <button type="button" class="quantity-btn" onclick="changeQuantity(this, -1)">-</button>
                                <input type="text" name='qtde' id='qtde' class="quantity-input" onchange="validateQuantity(this)" value='1'>
                                <button type="button" class="quantity-btn" onclick="changeQuantity(this, 1)">+</button>
                            </div>
                            <button type='button' id='btn-adicionar-carrinho' class='btn-custom'><i style="font-size: 17px; position: inherit;" class="glyphicon glyphicon-shopping-cart"></i> Adicionar ao carrinho</button>
                            <button type='submit' id='btn-finalizar-selecao' class='btn-custom btn-success'>Comprar agora</button>
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

<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true" style="color: white;">&times;</span>
                </button>
                <div class="success-icon">
                    <svg class="checkmark" viewBox="0 0 24 24">
                        <path d="M20 6L9 17l-5-5" />
                    </svg>
                </div>
                <h2 class="modal-title">Produto Adicionado!</h2>
                <p class="modal-subtitle">Item adicionado com sucesso ao seu carrinho</p>
            </div>

            <div class="modal-body">
                <div class="product-info">
                    <div class="product-details">
                        <h3 id="productName"></h3>
                        <p id="productDesc"></p>
                        <p>Quantidade: <span id="productQtd"></span></p>
                        <div class="product-price"><span id="productPrice"></span></div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Continuar comprando</button>
                <a href="/creditos/produtos.php" class="btn btn-primary">Comprar outros produtos</a>
            </div>

        </div>
    </div>
</div>
<script src="/js/jquery.mask.min.js"></script>
<script>
    function validateQuantity(input) {
        const selector = input.closest('.quantity-selector');
        const min = parseInt(selector.dataset.min) || 0;
        const max = parseInt(selector.dataset.max) || Infinity;
        let value = parseInt(input.value);

        if (isNaN(value) || value < min) {
            value = min;
        } else if (value > max) {
            value = max;
        }

        input.value = value;
        updateButtonStates(selector);
    }

    function changeQuantity(button, change) {
        const selector = button.closest('.quantity-selector');
        const input = selector.querySelector('.quantity-input');
        const minusBtn = selector.querySelector('.quantity-btn:first-child');
        const plusBtn = selector.querySelector('.quantity-btn:last-child');

        const min = parseInt(selector.dataset.min) || 0;
        const max = parseInt(selector.dataset.max) || Infinity;
        const currentValue = parseInt(input.value) || min;
        const newValue = Math.max(min, Math.min(max, currentValue + change));

        if (newValue !== currentValue) {
            input.value = newValue;

            // Animação de pulso
            selector.classList.add('pulse-animation');
            setTimeout(() => selector.classList.remove('pulse-animation'), 300);

            // Atualizar estado dos botões
            updateButtonStates(selector);
        }
    }

    function updateButtonStates(selector) {
        const input = selector.querySelector('.quantity-input');
        const minusBtn = selector.querySelector('.quantity-btn:first-child');
        const plusBtn = selector.querySelector('.quantity-btn:last-child');

        const min = parseInt(selector.dataset.min) || 0;
        const max = parseInt(selector.dataset.max) || Infinity;
        const value = parseInt(input.value);

        minusBtn.disabled = value <= min;
        plusBtn.disabled = value >= max;
    }

    $(function() {

        $('#qtde').mask('000', {
            reverse: true
        });

        $(".bg-comprar").click(function() {
            // Remove a borda de seleção e ícone de todos os produtos
            if (!$(this).hasClass("c-pointer")) return;
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
            // Ao selecionar um produto, habilita o botão "Adicionar ao carrinho"
            $("#btn-adicionar-carrinho").prop("disabled", false);
        });

        $("#btn-adicionar-carrinho").click(function() {
            // Envia os dados do formulário via AJAX para adiciona-carrinho.php
            var data = {
                acao: "u",
                mod: $("#mod").val(),
                valor: $("#valor_hidden").val(),
                codeProd: $("#codeProd").val(),
                qtde: ($("#qtde").length ? $("#qtde").val() : 1)
            };

            $.ajax({
                url: "/creditos/ajax/adiciona-carrinho.php",
                type: "POST",
                data: data,
                success: function(response) {
                    if (response.trim() === "sucesso") {
                        // Exibe modal de sucesso com nome do produto, modelo e valor
                        var nomeProduto = "<?php echo addslashes($produto->getNome()); ?>";
                        var nomeModelo = "";
                        var valorModelo = "";
                        var qtdProdutos = ($("#qtde").length ? $("#qtde").val() : 1);

                        // Tenta obter o nome e valor do modelo selecionado
                        var modeloId = $("#mod").val();
                        var spanSelecionado = $("span#" + modeloId);
                        if (spanSelecionado.length > 0) {
                            nomeModelo = spanSelecionado.closest(".row").find("strong").first().text().trim();
                            valorModelo = spanSelecionado.find(".pull-left strong").text().trim();
                        } else {
                            // fallback para valor digitado manualmente
                            nomeModelo = "Personalizado";
                            valorModelo = "R$ " + ($("#valor").val() ? $("#valor").val() : "");
                        }

                        $("#productName").html(nomeProduto);
                        $("#productDesc").html(nomeModelo);
                        $("#productPrice").html(valorModelo);
                        $("#productQtd").html(qtdProdutos);
                        $(".carrinho-compras").each(function() {
                            var atual = parseInt($(this).text()) || 0;
                            var novoTotal = atual + parseInt(qtdProdutos);
                            $(this).text(novoTotal);
                        });

                        $("#confirmModal").modal("show");
                    } else {
                        alert("Falha ao adicionar produto ao carrinho. Tente novamente.");
                    }
                },
                error: function() {
                    alert("Erro ao processar a requisição. Por favor, tente novamente.");
                }
            });
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
        ) // ).click(function() {

        //     if ($(this).attr("estoque") == "1") {
        //         console.log("redir");
        //         $("#mod").val($(this).attr("id"));
        //         if ($("#valor").length && $("#valor").val() === "") {
        //             var html = "<p class='txt-vermelho'>Por favor, informe um valor no campo!</p>";
        //             $(".error-list").html(html);
        //             return;
        //         }
        //         $('#valor_hidden').val($("#valor").val());
        //         $("#seleciona").submit();
        //     }
        // });

        // $(".prod").click(function() {
        //     var id = $(this).attr("id");
        //     $("#prod").val(id);
        //     $("#detalhe").submit();
        // });

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