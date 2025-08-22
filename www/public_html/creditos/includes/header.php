<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<?php
header("Content-Type: text/html; charset=ISO-8859-1", true);

$strNome = ($controller->usuariosOperador) ?
    $controller->usuariosOperador->getLogin()
    : ($controller->usuarios->getTipoCadastro() == 'PF') ? fix_name($controller->usuarios->getNome()) : fix_name($controller->usuarios->getNomeFantasia());


$usuarioGames = unserialize($GLOBALS['_SESSION']['dist_usuarioGames_ser']);

// Verifica se a desserialização foi bem-sucedida e acessa a propriedade
$showModal = false;
if ($usuarioGames && isset($usuarioGames->ug_blAtivo)) {
    if ($usuarioGames->ug_blAtivo == 2) {
        $showModal = true;
    } else {
        $showModal = false;
    }
}
$quantidadeCarrinho = 0;
if (isset($_SESSION['dist_carrinho']) && is_array($_SESSION['dist_carrinho'])) {
    // Conta a quantidade total de itens no carrinho, incluindo o NO HAVE
    foreach ($_SESSION['dist_carrinho'] as $key => $qtd) {
        // Ignora o NO HAVE, pois será tratado separadamente
        if ($key === 'NO HAVE') {
            continue;
        }
        if (is_numeric($qtd)) {
            $quantidadeCarrinho += $qtd;
        }
    }
    // Se existir o NO HAVE, soma as quantidades internas
    if (isset($_SESSION['dist_carrinho']['NO HAVE']) && is_array($_SESSION['dist_carrinho']['NO HAVE'])) {
        foreach ($_SESSION['dist_carrinho']['NO HAVE'] as $subArray) {
            if (is_array($subArray)) {
                foreach ($subArray as $valor) {
                    if (is_numeric($valor)) {
                        $quantidadeCarrinho += $valor;
                    }
                }
            }
        }
    }
}
?>
<html>

<head>
    <title>E-Prepag - Créditos para games online <?php echo ((isset($pagina_titulo)) ? " - " . $pagina_titulo : ""); ?></title>
    <!-- Google Tag Manager -->
    <script>
        (function(w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({
                'gtm.start': new Date().getTime(),
                event: 'gtm.js'
            });
            var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s),
                dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src =
                'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-WHJ6N33');
    </script>
    <!-- End Google Tag Manager -->
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <meta charset="ISO-8859-1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Language" content="pt-br" />
    <meta name="description" content="Créditos para Point Blank, League of Legends, CrossFire, Google Play, Free Fire, Xbox e muito mais. Seja um ponto de venda de games e outros serviços." />
    <meta name="keywords" content="Free Fire, League of Legends, Point Blank, Crossfire, Google Play, revendedor, créditos, cash, games, vender ." />
    <meta name="robots" content="index, follow" />
    <!-- includes css -->
    <link href="/includes/bootstrap/css/bootstrap.min_new.css" rel="stylesheet" type="text/css" />
    <link href="/css/creditos.css" rel="stylesheet" type="text/css" />
    <link href="/css/game.css" rel="stylesheet" type="text/css" />
    <!-- includes js -->
    <script type="text/javascript" src="<?php echo $controller->jQuery; ?>"></script>
    <script type="text/javascript" src="/includes/bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- RDstation -->
    <script type="text/javascript" async src="https://d335luupugsy2.cloudfront.net/js/loader-scripts/a16eb379-4718-4567-8bfa-b86c5fd5ce3a-loader.js"></script>
</head>

<body>

    <div id="modal-bloqueiopdv" class="modal fade" role="dialog">
        <div class="modal-dialog modal-md" role="document">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title txt-vermelho"><strong>Atenção!</strong></h4>
                </div>
                <div class="modal-body alert alert-danger txt-vermelho">
                    <p>Seu PDV está inativo, por gentileza, entre em contato com o suporte@e-prepag.com.br.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>
    <div class="hide fundo-neutro"></div>
    <div class="container-fluid bg-primary topo-h">
        <div class="container">
            <div class="col-md-12 col-sm-12 col-xs-12 col-lg-12 top20">
                <a href="/creditos/index.php" class="">
                    <img src="/sys/imagens/epp_logo.png" alt="Painel de Administração da E-Prepag" title="Painel de Administração da E-Prepag" name="LogoRPP" border="0" id="LogoRPP">
                </a>
                <a class=" hidden-xs p-left8 pull-right decoration-none fontsize-p txt-branco" href="/game/suporte.php" alt="Veja como funciona" title="Suporte" target="_blank"><strong>SUPORTE</strong></a>
                <span class=" hidden-xs p-left8 pull-right decoration-none fontsize-p txt-branco"> | </span>
                <a class=" hidden-xs pull-right decoration-none fontsize-p txt-branco" href="http://blog.e-prepag.com/como-funciona-o-sistema-de-vendas/" alt="Veja como funciona" title="Veja como funciona" target="_blank"><strong>VEJA COMO FUNCIONA</strong></a>
            </div>
            <div class="col-md-3 col-sm-3 col-lg-3">
                <span class=" p-left8"><strong>Créditos para games</strong></span>
            </div>
            <div class="col-md-3 col-lg-3 col-sm-3 hidden-xs">
                <strong class=" texto-topo div-texto-topo nowrap">Olá <?= $strNome; ?></strong>
            </div>
            <div class="col-md-3 col-lg-3 text-right col-sm-2 hidden-xs">
                <?php
                if (($controller->lanHouse && $controller->usuarios->getRiscoClassif() == 2) || ($controller->usuarios->getRiscoClassif() == 2 && $controller->operadorTipo == $GLOBALS['USUARIO_GAMES_OPERADOR_TIPOS'][FUNCIONARIO_1])) {
                ?>
                    <a href="/creditos/add_saldo.php" alt="Adicionar saldo" title="Adicionar saldo" class="link_azul nowrap hidden-xs">
                        <span class="texto-topo hidden-sm"><strong>ADICIONAR SALDO</strong></span>
                        <span class="texto-topo hidden-lg hidden-md"><strong>ADD SALDO</strong></span>
                        <img src="/imagens/t_icons2.png" alt="Painel de Administração da E-Prepag" name="" border="0" id="">
                    </a>
                <?php
                }
                ?>
            </div>
            <div class="col-sm-4 col-md-3 col-lg-3 text-right hidden-xs nowrap" style="max-height: 34px;">
                <p class="text-success text22">
                    <?php
                    if ($controller->operadorTipo !== $GLOBALS['USUARIO_GAMES_OPERADOR_TIPOS'][FUNCIONARIO_2]) {
                        echo ($controller->usuarios->getRiscoClassif() == 1) ? "Limite" : "Saldo";
                    ?>
                        <strong>R$ <?php echo $controller->saldoLimite; ?></strong>
                    <?php
                    }
                    ?>
                </p>
            </div>
        </div>
    </div>
    <!-- Static navbar -->
    <nav class="navbar navbar-default bottom0 bg-info">
        <div class="container">
            <div class="navbar-header" style="display: flex; justify-content: end; align-items: center; gap: 20px;">
                <a class="hidden-sm hidden-md hidden-lg" style="padding-top: 15px; height:50px;  position: relative; <?= ($quantidadeCarrinho > 0 ? "" : "display: none !important;") ?>" href="/creditos/produto/produtos_selecionados.php" alt="Meu Carrinho" title="Meu Carrinho">
                    <span class="glyphicon glyphicon-shopping-cart txt-branco" style="top: -5px !important; font-size: 28px;"></span>
                    <span class="carrinho-compras" style="
                                            position: absolute;
                                            top: 9px;
                                            right: -4px;
                                            color: #268fbd;
                                            padding: 5px 7px;
                                            font-size: 11px;
                                            font-weight: bold;
                                            min-width: 32px;
                                            text-align: center;
                                            text-shadow: 1px 1px 5px rgba(38, 143, 189, 0.5);
                                            text-align: center;
                                        "><?php echo $quantidadeCarrinho; ?></span></a>
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar bg-branco"></span>
                    <span class="icon-bar bg-branco"></span>
                    <span class="icon-bar bg-branco"></span>
                </button>
            </div>
            <div id="navbar" class="navbar-collapse collapse">
                <ul class="nav navbar-nav navbar-left hidden-lg hidden-md hidden-sm">
                    <li class="p-left8">Olá <?= $strNome; ?>.</li>
                    <?php
                    if ($controller->operadorTipo !== $GLOBALS['USUARIO_GAMES_OPERADOR_TIPOS'][FUNCIONARIO_2]) {
                        echo " <li class=\"p-left8\"><strong>";
                        echo ($controller->usuarios->getRiscoClassif() == 1) ? "Limite" : "Saldo";
                        echo " R$ " . $controller->saldoLimite . "</strong></li>";
                    }
                    ?>
                </ul>
                <?php
                if ($controller->operadorTipo !== $GLOBALS['USUARIO_GAMES_OPERADOR_TIPOS'][FUNCIONARIO_2]) {
                ?>
                    <ul class="nav navbar-nav navbar-left bg-verde-claro">
                        <li class=""><a href="/creditos/produtos.php" alt="Clique para Comprar Games" title="Clique para Comprar Games" class="p-top10 bg-verde-claro hover-verde"><strong>GAMES</strong></a></li>
                    </ul>
                    <div class=" hidden-xs w35 pull-left right10">&nbsp</div>
                <?php
                }

                if ($controller->operadorTipo !== $GLOBALS['USUARIO_GAMES_OPERADOR_TIPOS'][FUNCIONARIO_2]) {
                ?>
                    <form class=" p0 navbar-form navbar-left  has-success has-feedback" action="/creditos/busca.php" name="formBusca" id="formBusca" method="post">
                        <div class="input-group has-success has-feedback">
                            <input type="text" class="form-control  txt-cinza borda-cinza" placeholder="Encontrar Produto" id="busca" name="busca" aria-describedby="basic-addon2">
                            <span class="input-group-addon glyphicon t0 glyphicon-search bg-branco c-pointer borda-cinza" id="basic-addon2"></span>
                        </div>
                        <input type="submit" class="hidden">
                    </form>
                    <form id="detalhe" name="detalhe" action="/creditos/produto/produto_detalhe.php" method="post">
                        <input type="hidden" name="prod" id="prod">
                    </form>
                <?php
                }
                ?>
                <ul class="nav navbar-nav navbar-right hidden-md hidden-sm hidden-lg">
                    <?php
                    if (($controller->usuarios->getRiscoClassif() == 1 && $controller->operadorTipo !== $GLOBALS['USUARIO_GAMES_OPERADOR_TIPOS'][FUNCIONARIO_2]) && checaBoletoEmAberto() != 0) {
                    ?>
                        <li><a href="/creditos/boletos.php?nao-emitido=1" class="botao-laranja txt-branco" title="Boleto Pendente" alt="Boleto Pendente" id="boleto-pendente"><strong>BOLETO PENDENTE</strong></a></li>
                    <?php
                    }
                    ?>
                    <li role="presentation"><a href="/creditos/pedidos.php?nao_emitidos=1" title="Pins não emitidos" alt="Pins não emitidos" class=" txt-branco"><strong>PINS NÃO EMITIDOS</strong></a></li>
                    <?php
                    if (($controller->usuarios->getRiscoClassif() == 2 && $controller->operadorTipo !== $GLOBALS['USUARIO_GAMES_OPERADOR_TIPOS'][FUNCIONARIO_2])) {
                    ?>
                        <li role="presentation"><a href="/creditos/add_saldo.php" alt="Adicionar Saldo" title="Adicionar Saldo" class=" txt-branco"><strong>ADICIONAR SALDO</strong></a></li>
                        <li role="presentation"><a href="https://e-prepagpdv.com.br/midia-kits/" alt="Divulgação" title="Divulgação"><strong>DIVULGAÇÃO</strong></a></li>
                    <?php
                    }
                    ?>
                    <li role="presentation"><a href="/creditos/pedidos.php" title="Pedidos" alt="Pedidos" class=" txt-branco"><strong>PEDIDOS</strong></a></li>
                    <?php
                    if ($controller->lanHouse && $controller->usuarios->b_IsLogin_lista_extrato()) {
                    ?>
                        <li role="presentation"><a href="/creditos/extrato.php" alt="Extrato" title="Extrato" class=" txt-branco"><strong>EXTRATO</strong></a></li>
                    <?php
                    }
                    if (($controller->lanHouse && $controller->usuarios->getRiscoClassif() == 2) || ($controller->usuarios->getRiscoClassif() == 2 && $controller->operadorTipo == $GLOBALS['USUARIO_GAMES_OPERADOR_TIPOS'][FUNCIONARIO_1])) {
                    ?>
                        <li role="presentation"><a href="/creditos/depositos.php" alt="Depósitos" title="Depósitos" class=" txt-branco"><strong>DEPÓSITOS</strong></a></li>
                    <?php
                    }
                    if (($controller->usuarios->getRiscoClassif() == 1 && $controller->operadorTipo !== $GLOBALS['USUARIO_GAMES_OPERADOR_TIPOS'][FUNCIONARIO_2])) {
                    ?>
                        <li role="presentation"><a href="/creditos/boletos.php" alt="Boletos - Histórico" title="Boletos - Histórico" class=" txt-branco"><strong>BOLETOS - HISTÓRICO</strong></a></li>
                    <?php
                    }

                    if ($controller->lanHouse) {
                    ?>
                        <li role="presentation"><a href="/creditos/meu_cadastro.php" alt="Meu Cadastro" title="Meu Cadastro" class=" txt-branco"><strong>MEU CADASTRO</strong></a></li>
                        <li role="presentation"><a href="/creditos/funcionarios.php" alt="Gerenciar funcionários" title="Gerenciar funcionários" class=" txt-branco"><strong>GERENCIAR FUNCIONÁRIOS</strong></a></li>
                        <li role="presentation"><a href="/creditos/pesquisa.php" alt="Pesquisa - Pins Disponiveis" title="Pesquisa - Pins Disponiveis"><strong>PINS DISPONÍVEIS</strong></a></li>
                    <?php
                    }
                    ?>
                    <li role="presentation"><a href="http://blog.e-prepag.com/como-funciona-o-sistema-de-vendas/" alt="Veja como funciona" title="Veja como funciona" class="txt-branco" target="_blank"><strong>VEJA COMO FUNCIONA</strong></a></li>
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <li><a href="/creditos/logout.php" alt="Sair" title="Sair" class="decoration-none texto-topo">(sair)</a></li>
                </ul>
                <ul class="nav navbar-nav navbar-right hidden-xs">
                    <li style="position: relative; <?= ($quantidadeCarrinho > 0 ? "" : "display: none !important;") ?>">
                        <a style="padding-top: 15px; height:50px; position: relative;" href="/creditos/produto/produtos_selecionados.php" alt="Meu Carrinho" title="Meu Carrinho">
                            <span class="glyphicon glyphicon-shopping-cart txt-branco" style="top: -5px !important; font-size: 28px;"></span>
                            <span class="carrinho-compras" style="
                                            position: absolute;
                                            top: 6px;
                                            right: 10px;
                                            color: #268fbd;
                                            padding: 5px 7px;
                                            font-size: 11px;
                                            font-weight: bold;
                                            min-width: 32px;
                                            text-align: center;
                                            text-shadow: 1px 1px 5px rgba(38, 143, 189, 0.5);
                                            text-align: center;
                                        "><?php echo $quantidadeCarrinho; ?></span>
                        </a>
                    </li>
                    <li class=""><a href="/creditos/pedidos.php" alt="Meus Pedidos" title="Meus Pedidos"><strong>PEDIDOS</strong></a></li>
                    <li class=""><a href="/creditos/pedidos.php?nao_emitidos=1" alt="Pins Não Emitidos" title="Pins Não Emitidos"><strong>PINS NÃO EMITIDOS</strong></a></li>
                    <li class="active"><a href="/creditos/index.php" class="" alt="Minha Loja" title="Minha Loja"><strong>MINHA LOJA</strong></a></li>
                </ul>

            </div><!--/.nav-collapse -->
        </div>
    </nav>
    <?php
    if (
        $controller->usuarios->getLogin() == "WAGNER" ||
        $controller->usuarios->getLogin() == "GLAUCIAPJ" ||
        $controller->usuarios->getLogin() == "ODECIO" ||
        $controller->usuarios->getLogin() == "FABIO###"
    ) {

    ?>
        <div class="container-fluid bg-cinza-claro text-center">
            <span class="txt-cinza"><?php echo getLastOrders(); ?></span>
        </div>
    <?php
    }
    ?>
    <div class="container-fluid bg-cinza-claro">

        <script>
            var showModal = <?php echo json_encode($showModal); ?>;
            $(document).ready(function() {

                // Função para ajustar o tamanho da fonte dos elementos .carrinho-compras conforme o valor
                function ajustarFonteCarrinho() {
                    $(".carrinho-compras").each(function() {
                        var valor = parseInt($(this).text());
                        if (!isNaN(valor) && valor > 999) {
                            $(this).css("font-size", "9px");
                        } else {
                            $(this).css("font-size", "11px");
                        }
                    });
                }

                // Chama a função ao carregar a página
                ajustarFonteCarrinho();

                // Observa mudanças no texto dos elementos .carrinho-compras
                $(".carrinho-compras").each(function() {
                    var observer = new MutationObserver(function(mutations) {
                        ajustarFonteCarrinho();
                    });
                    observer.observe(this, { childList: true, characterData: true, subtree: true });
                });

                // Caso o valor seja alterado via JS (ex: ao adicionar produto), chame também após atualizar o valor

                if (showModal) {
                    // Exibe o modal
                    $("#modal-bloqueiopdv").modal('show');
                    // Redireciona após alguns segundos
                    setTimeout(function() {
                        window.location.href = "/creditos/logout.php";
                    }, 5000); // 5000 milissegundos (5 segundos)
                }
            });
        </script>