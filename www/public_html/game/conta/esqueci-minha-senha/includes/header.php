<?php require_once __DIR__ . '/../../../../../includes/constantes_url.php'; ?>
<?php

	header("Content-Type: text/html; charset=ISO-8859-1",true);

	ini_set('display_errors', 0);
	ini_set('display_startup_errors', 0);
	error_reporting(0);

?>

<!DOCTYPE html>
<html>
    <head>
        <title>E-Prepag - Esqueci Minha Senha<?php echo ((isset($pagina_titulo))?" - ".$pagina_titulo:""); ?></title>
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
        <meta name="robots" content="noindex, nofollow" />
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
		
		<link rel="stylesheet" href="<?= EPREPAG_URL_HTTPS ?>/creditos/esqueci-minha-senha/style/style.css" />
		
    </head>
    <body>
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
    