<?php require_once __DIR__ . '/../../../includes/constantes_url.php'; ?>
<?php 

header("Content-Security-Policy: default-src *; script-src * 'unsafe-inline' 'unsafe-eval'; style-src * 'unsafe-inline';");


    $arrCarteira = array("/game/conta/depositos-processamento.php","/game/conta/extrato.php","/game/carteira/detalhe-pedido.php","/game/conta/detalhe-deposito.php","/game/conta/add-saldo.php");
    $arrMinhaConta = array("/game/conta/pedidos.php", "/game/conta/extrato.php", "/game/conta/meus-dados.php","/game/conta/dados-acesso.php");
?>
<!DOCTYPE html>
<html>
    <head>
        <title>E-Prepag - Créditos para games online<?php echo ((isset($pagina_titulo))?" - ".$pagina_titulo:""); ?></title>
		<!-- Google Tag Manager -->
		<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
		new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
		j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
		'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
		})(window,document,'script','dataLayer','GTM-WHJ6N33');</script>
		<!-- End Google Tag Manager -->
        <link rel="icon" href="/favicon.ico" type="image/x-icon">
        <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
        <meta charset="ISO-8859-1">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="Content-Language" content="pt-br" />
        <meta name="description" content="Créditos para Point Blank, League of Legends, CrossFire, Google Play, Xbox, Free Fire e muito mais. Seja um ponto de venda de games e outros serviços." />
        <meta name="keywords" content="Free Fire, League of Legends, Point Blank, Crossfire, Google Play, revendedor, créditos, cash, games, vender ." />
        <meta name="robots" content="index, follow" />
        <link href="/includes/bootstrap/css/bootstrap.min_new.css" rel="stylesheet" type="text/css" />        
        <link href="/css/creditos.css" rel="stylesheet" type="text/css" />
        <link href="/css/game.css" rel="stylesheet" type="text/css" />
        <!-- includes js -->
        <script type="text/javascript" src="/js/jquery.js"></script>
        <script type="text/javascript" src="/includes/bootstrap/js/bootstrap.min.js"></script>
        <link href="/css/digicert.css" rel="stylesheet">
        <link href="/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
        <script src="/js/jquery-ui-1.9.2.custom.min.js"></script>
        <script type="text/javascript" src="/js/autocomplete.js"></script>
        <script type="text/javascript" src="/js/modalwaitingfor.js"></script>
		<!-- RDstation -->
		<script type="text/javascript" async src="https://d335luupugsy2.cloudfront.net/js/loader-scripts/a16eb379-4718-4567-8bfa-b86c5fd5ce3a-loader.js"></script>
        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=UA-1903237-3"></script>
        <script>

            window.dataLayer = window.dataLayer || [];

            function gtag(){dataLayer.push(arguments);}

            gtag('js', new Date());

            gtag('config', 'UA-1903237-3');

        </script>
        <!-- End Global site tag (gtag.js) - Google Analytics -->
<?php
    if($_SERVER['SCRIPT_NAME'] == "/cadastro-finalizado.php") {
?>        
        <!-- Google Code for Convers&atilde;o - Cadastro novo Conversion Page -->
        <script type="text/javascript">
        /* <![CDATA[ */
        var google_conversion_id = 1052651518;
        var google_conversion_language = "en";
        var google_conversion_format = "3";
        var google_conversion_color = "ffffff";
        var google_conversion_label = "Rqj2CIzB3FwQ_t_49QM";
        var google_remarketing_only = false;
        /* ]]> */
        </script>
        <script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">
        </script>
        <noscript>
        <div style="display:inline;">
        <img height="1" width="1" style="border-style:none;" alt="" src="//www.googleadservices.com/pagead/conversion/1052651518/?label=Rqj2CIzB3FwQ_t_49QM&amp;guid=ON&amp;script=0"/>
        </div>
        </noscript>
<?php
    }//end if($_SERVER['SCRIPT_NAME'] == "cadastro-finalizado.php") 
?>        
    </head>
    <body>
        <div id="modal-load" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                      <button type="button" class="close" data-dismiss="modal">&times;</button>
                      <h4 class="modal-title txt-vermelho" id="modal-title">Erro de preenchimento</h4>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger" id="tipo-modal" role="alert"> 
                          <h5><span id="error-text">PINs E-Prepag: São milhares de Lan Houses, lojas de games, de informáticas e vários outros tipos de comércio em todo o Brasil.</span></h5>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid bg-cinza-claro topo-h">
            <div class="container">
                <div class="row top20">
                    <div class="col-md-3">
                        <a href="/" class="">
                            <img src="/sys/imagens/epp_logo.png" alt="E-Prepag" title="E-Prepag" name="LogoRPP" border="0" id="LogoRPP">
                        </a>
                    </div>
                    <div class="col-md-5 col-md-offset-4 text-right">
                        <span class="txt-cinza fontsize-p">
                            <a href="#" class="decoration-none txt-cinza redirecionamento" link="solucoes.e-prepag.com">Business Page</a> | 
                            <a href="https://e-prepagpdv.com.br/" class="decoration-none txt-cinza">Seja um Parceiro</a> | 
                            <a href="https://www.blog.e-prepag.com" target="_blank" class="decoration-none nowrap txt-cinza">Sobre a E-prepag</a></span>
                    </div>
                </div> 
                <div class="row">
                    <div class="col-md-5 ">
                        <span class="txt-azul-claro p-left8 hidden-xs"><i>Créditos para games</i></span>
                    </div>
                    <div class="col-md-7 text-right">
                        <span class="txt-azul-claro"><a href="/game/conta/login.php">Login</a> | <a href="/game/conta/nova.php">Novo usuário?</a></span>
                    </div>
                </div>
            </div>    
        </div>
        <div class="top50 hidden-md hidden-lg"></div>
        <div class=" borda-top-verde bg-info">
            <nav class="navbar navbar-default bottom0">
                <div class="container">
                    <div class="navbar-header">
                        <button type="button" class="txt-branco navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                            <span class="sr-only">Toggle navigation</span>
                            <span class="icon-bar bg-branco"></span>
                            <span class="icon-bar bg-branco"></span>
                            <span class="icon-bar bg-branco"></span>
                        </button>
                    </div>
                    <!-- Collect the nav links, forms, and other content for toggling -->
                    <div id="navbar" class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                        <ul class="nav navbar-nav">
                            <li <?php echo ($_SERVER['SCRIPT_NAME'] == "/game/index.php") ? 'class="active"><a href="#"' : '"><a href="/game/"';?>"><strong>JOGOS</strong></a></li>
                            <li><a href="/game/conta/extrato.php"><strong>CARTÃO E-PREPAG</strong></a></li>
                            <li><a href="https://www.blog.e-prepag.com" target="_blank"><strong>NOVIDADES</strong></a></li><!-- href="#" link="blog.e-prepag.com" class="redirecionamento" -->
                            <li><a href="/game/conta/pedidos.php"><strong>MINHA CONTA</strong></a></li>
                            <li <?php echo ($_SERVER['SCRIPT_NAME'] == "/game/suporte.php") ? 'class="active"><a href="#"' : '"><a href="/game/suporte.php"';?>><strong>SUPORTE</strong></a></li>
                        </ul>
                        <?php if(isset($_SESSION["carrinho"]) && count($_SESSION["carrinho"])) {?>
                            <ul class="nav navbar-nav pull-right">
                                <li><a href="/game/pedido/passo-1.php"><span class="glyphicon glyphicon-shopping-cart txt-branco font20" style="top: 0px !important;"></span></a></li>
                            </ul>
                        <?php } ?>
                    </div><!-- /.navbar-collapse -->
                </div>
            </nav>
        </div>
        <div class="container-fluid bg-cinza-claro">
    