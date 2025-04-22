<?php 
header("Content-Security-Policy: default-src *; script-src * 'unsafe-inline' 'unsafe-eval'; style-src * 'unsafe-inline';");


    $arrCarteira = array("/game/conta/depositos-processamento.php","/game/conta/extrato.php","/game/carteira/detalhe-pedido.php","/game/conta/detalhe-deposito.php","/game/conta/add-saldo.php");
    $arrMinhaConta = array("/game/conta/pedidos.php", "/game/conta/meus-dados.php","/game/conta/dados-acesso.php");
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
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="Content-Language" content="pt-br" />
        <meta name="description" content="Créditos para Point Blank, League of Legends, CrossFire, Google Play, Xbox, Steam e muito mais. Seja um ponto de venda de games e outros serviços." />
        <meta name="keywords" content="game, online, créditos, vender, pin, point blank, pb, league, lol, crossfire, lan house, pdv" />
        <meta name="robots" content="index, follow" />
        <!-- includes css -->
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
		<script type="text/javascript" src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        
        <script>
            $(function(){
                $("#sair").click(function(){

                    $.ajax({
                            url: '/game/ajax/logoff.php',
                            beforeSend: function () {
                                waitingDialog.show('Por favor, aguarde...',{dialogSize: 'sm'});
                            },
                            success: function(ret){
                                if(ret)
                                    window.location = "/";
                            }
                        });
                });
            });
        </script>
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
                        <div class="col-md-12 fontsize-p">
                            <span class="txt-cinza">
                                <a href="#" link="solucoes.e-prepag.com" class="decoration-none redirecionamento txt-cinza">Business Page</a> | 
                                <a href="<?php echo PROTOCOL;?>://e-prepagpdv.com.br/" class="decoration-none redirecionamento txt-cinza">Seja um Parceiro</a> | 
                                <a href="https://www.blog.e-prepag.com" target="_blank" class="decoration-none nowrap txt-cinza">Sobre a E-prepag</a></span>
                        </div>
                        <div class="col-md-12">
                            <span class="txt-azul-claro"><?=$this->usuario->getNome();?> <span id="sair" class="c-pointer">(sair)</span></span>
                        </div>
                    </div>
                </div> 
                <div class="row">
                    <div class="col-md-3">
                        <span class="txt-azul-claro p-left8 hidden-xs"><i>Créditos para games</i></span>
                    </div>
                </div>
            </div>    
        </div>
        <div class="top50 hidden-md hidden-lg"></div>
        <div class=" borda-top-verde bg-info">
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
                            <li <?php echo ($_SERVER['SCRIPT_NAME'] == "/game/index.php") ? 'class="active decoration-none"><a href="#"' : '"><a href="/game/" class="decoration-none"';?>><strong>JOGOS</strong></a></li>
                            <li <?php echo (in_array($_SERVER['SCRIPT_NAME'],$arrCarteira)) ? 'class="active decoration-none"><a href="#"' : '><a href="/game/conta/extrato.php" class="decoration-none"';?>><strong>CARTÃO E-PREPAG</strong></a></li>
                            <li><a class="decoration-none" href="https://www.blog.e-prepag.com" target="_blank"><strong>NOVIDADES</strong></a></li>
                            <li>
                                <ul class="hidden-xs hidden-sm nav navbar-nav" style="margin:0;">
                                    <li class="dropdown">
                                        <a href="<?php echo (in_array($_SERVER['SCRIPT_NAME'],$arrMinhaConta)) ? "#\" class=\"bg-branco txt-azul decoration-none\"" : "/game/conta/pedidos.php\" class=\"decoration-none"; ?>"><strong>MINHA CONTA</strong></a>
                                        <div class="dropdown-content dropdown-menu-left text-left" style="z-index: 999;">
                                            <a href="/game/conta/pedidos.php" class="nowrap">Meus pedidos</a>
                                            <a href="/game/conta/extrato.php" class="nowrap">Cartão E-Prepag</a>
                                            <a href="/game/conta/meus-dados.php" class="nowrap">Meu Cadastro</a>
                                            <a href="/game/conta/dados-acesso.php" class="nowrap">Editar dados de acesso</a>
                                        </div>
                                    </li>
                                    <li>

                                    </li>
                                </ul>
                                <a href="<?php echo (in_array($_SERVER['SCRIPT_NAME'],$arrMinhaConta)) ? "#" : "/game/conta/pedidos.php"; ?>" class="hidden-md hidden-lg decoration-none  <?php if(in_array($_SERVER['SCRIPT_NAME'],$arrMinhaConta)) echo 'bg-branco txt-azul';?>"><strong>MINHA CONTA</strong></a>
                            </li>
                            <li <?php echo ($_SERVER['SCRIPT_NAME'] == "/game/suporte.php") ? 'class="active decoration-none"><a href="#"' : '><a href="/game/suporte.php" class="decoration-none"';?>><strong>SUPORTE</strong></a></li>
                        </ul>
                        <?php if(count($_SESSION["carrinho"])) {?>
                            <ul class="nav navbar-nav pull-right">
                                <li><a href="/game/pedido/passo-1.php"><span class="glyphicon glyphicon-shopping-cart txt-branco font20" style="top: 0px !important;"></span></a></li>
                            </ul>
                        <?php } ?>
                    </div><!-- /.navbar-collapse -->
                </div>
            </nav>
        </div>
        <div class="container-fluid bg-cinza-claro">
    