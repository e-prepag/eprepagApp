<?php

header("Content-Type: text/html; charset=ISO-8859-1",true);

?>

<!DOCTYPE html>
<html>
    <head>
        <title>E-Prepag - Venda de créditos para games<?php echo ((isset($pagina_titulo))?" - ".$pagina_titulo:""); ?></title>
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
        <meta name="description" content="Venda de créditos para Point Blank, PB, League of Legends, Lol, Crossfire, Xbox, Clash Royale, Free Fire, Google Play e muitos outros. Cadastre sua loja." />
        <meta name="keywords" content="Free Fire, League of Legends, Point Blank, Crossfire, Google Play, revendedor, créditos, cash, games, vender ." />
        <meta name="robots" content="index, follow" />
        <!-- includes css -->
        <link href="/includes/bootstrap/css/bootstrap.min_new.css" rel="stylesheet" type="text/css" />
        <link href="/css/creditos.css" rel="stylesheet" type="text/css" />
        <link href="/css/game.css" rel="stylesheet" type="text/css" />
        <!-- includes js -->
        <script type="text/javascript" src="/js/jquery.js"></script>
        <script type="text/javascript" src="/includes/bootstrap/js/bootstrap.min.js"></script>
        <link href="/css/digicert.css" rel="stylesheet">
        <link href="/js/jqueryui/css/custom-theme/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
        <script src="/js/jqueryui/js/jquery-ui-1.9.2.custom.min.js"></script>
        <script type="text/javascript" src="/js/autocomplete.js"></script>
        <script type="text/javascript" src="/js/modalwaitingfor.js"></script>
        <script type="text/javascript" src="/js/valida.js"></script>
		<!-- RDstation -->
		<script type="text/javascript" async src="https://d335luupugsy2.cloudfront.net/js/loader-scripts/a16eb379-4718-4567-8bfa-b86c5fd5ce3a-loader.js"></script>
        <!-- Mautic Tag Manager -->
        <script>
            (function(w,d,t,u,n,a,m){w['MauticTrackingObject']=n;
                w[n]=w[n]||function(){(w[n].q=w[n].q||[]).push(arguments)},a=d.createElement(t),
                m=d.getElementsByTagName(t)[0];a.async=1;a.src=u;m.parentNode.insertBefore(a,m)
            })(window,document,'script','https://mautic.e-prepag.net.br/mtc.js','mt');

            mt('send', 'pageview');
        </script>
        <!-- End Mautic Tag Manager -->
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
        <div class="hide fundo-neutro"></div>
        <div class="container-fluid bg-primary topo-h">
            <div class="container">
                <div class="col-md-3">
                    <a href="/" class="">
                        <div class="col-md-12 top20">
                            <img src="/sys/imagens/epp_logo.png" alt="E-Prepag" title="E-Prepag" name="LogoRPP" border="0" id="LogoRPP">
                        </div>
                        <div class="col-md-12">
                            <span class="txt-branco p-left8"><strong>Créditos para games</strong></span>
                        </div>
                    </a>
                </div>
            </div>    
        </div>
        <div class="bg-info">
            <nav class="navbar navbar-default bottom0">
                <div class="container">
                    <div class="navbar-header">
                        <button type="button" class="btn-info navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                            <span class="sr-only">Toggle navigation</span>
                            <span class="icon-bar bg-branco"></span>
                            <span class="icon-bar bg-branco"></span>
                            <span class="icon-bar bg-branco"></span>
                        </button>
                    </div>
                    <!-- Collect the nav links, forms, and other content for toggling -->
                    <div id="navbar" class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                        <ul class="nav navbar-nav">
                            <li class="decoration-none"><a href="/"><strong>E-PREPAG</strong></a></li>
                            <li <?php echo (($_SERVER['SCRIPT_NAME'] == "/game/suporte.php")) ? 'class="active decoration-none"><a href="#"' : '><a href="/game/suporte.php" class="decoration-none"';?>><strong>SUPORTE</strong></a></li>
                        </ul>
                    </div><!-- /.navbar-collapse -->
                </div>
            </nav>
        </div>
        <div class="container-fluid bg-cinza-claro">
    