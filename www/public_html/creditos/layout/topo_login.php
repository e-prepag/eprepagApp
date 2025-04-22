<?php
$msg = '';
if ( array_key_exists('msg', $_GET) ) {
    if ( !empty($_GET['msg']) ) {
        $msg = nl2br(htmlentities($_GET['msg']));
    }
}
?><!DOCTYPE html>
<html class="no-js" lang="pt-br">
<head>
    <title>E-Prepag - Créditos para games online</title>
    <meta charset="ISO-8859-1">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Venda de créditos para os games Point Blank, PB, League of Legends, Lol, Crossfire, Xbox, cards e muitos outros">
    <meta name="keywords" content="pb, point blank, lol, league of legends, crossfire, venda, games, cards, e-prepag, estabelecimento">

    <!-- CSS's -->
    <link href="<?php echo BOOTSTRAP_DIR;?>css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo CSS_DIR;?>main.css" rel="stylesheet" type="text/css" />

    <link href="<?php echo CSS_DIR;?>jquery-ui.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo JS_DIR;?>fancybox/jquery.fancybox.css" rel="stylesheet" type="text/css" />

    <?php
    if ( isset($_css_add) ) {
        if ( is_array($_css_add) && count($_css_add) > 0 ) {
            foreach ($_css_add as $css) {
                echo "<link href=\"$css\" rel=\"stylesheet\" type=\"text/css\" />".PHP_EOL;
            }
        }
    }
    ?>

    <!-- Javascripts -->
    <!--[if lt IE 9]>
    <script src="<?php echo JS_DIR;?>js/html5shiv-respond.js"></script>
    <![endif]-->
    <script type="text/javascript" src="<?php echo JS_DIR;?>Modernizr.js"></script>
    <script type="text/javascript" src="<?php echo JS_DIR;?>jquery.js"></script>
    <script type="text/javascript" src="<?php echo BOOTSTRAP_DIR;?>js/bootstrap.min.js"></script>
    <script type="text/javascript" src="<?php echo JS_DIR;?>fancybox/jquery.fancybox.js"></script>

    <?php
    if ( isset($_js_add) ) {
        if ( is_array($_js_add) && count($_js_add) > 0 ) {
            foreach ($_js_add as $js) {
                    echo "<script type=\"text/javascript\" src=\"$js\"></script>".PHP_EOL;
            }
        }
    }
    ?>
    <link href="<?php echo CSS_DIR;?>reset.css" rel="stylesheet" type="text/css" />
    <style>.btnLogin{width: 70px !important; color: #fff;    background-color: #009b4a;    border-color: #4cae4c;    font-weight: bold;    font-style: italic;    display: inline-block;    margin-bottom: 0;    font-size: 16px;    text-align: center;    white-space: nowrap;    vertical-align: middle;    -ms-touch-action: manipulation;    touch-action: manipulation;    cursor: pointer;    -webkit-user-select: none;    -moz-user-select: none;    -ms-user-select: none;    user-select: none;    background-image: none;    border: 1px solid transparent;    border-radius: 4px;}</style>
    <?php if ( !empty($msg) ) {?>
        <style type="text/css">
            .fancybox-wrap {
                top: 200px !important;
            }
            .fancybox-inner {
                overflow: hidden !important;
            }
        </style>
       <style type="text/css">
            .msgbox{ width: 400px; height: auto !important; text-align: left; line-height: 19px; color: rgba(0, 0, 0, 0.8) }
            .msgbox h2{ font-size: 22px; color: #094E78; display: block; }
            
        </style>

        <script>
            $(document).ready(function(){
                $('.msgbox').fancybox().trigger('click');
            });
        </script>
    <?php } ?>
</head>
<body>
<div class="topo">
    <div class="faixa">
        <div class="links">
            <span><a href="https://www.e-prepag.com/" target="_blank" class="corlink">E-Prepag</a></span>
            <span><a href="https://www.e-prepag.com/support" target="_blank" class="corlink">Suporte</a></span>
            <span><a href="http://blog.e-prepag.com/seja-um-ponto-de-venda/" target="_blank" class="corlink">Seja um ponto autorizado</a></span>
        </div>
    </div>

    <div class="login">
        <div class="control_flow">
            <div class="banner_login">
                <img src="<?php echo IMG_EPREPAG_URL;?>logo_frase.png" class="imglogo" />
            </div>
            <div class="form_login">
                <div class="form_text"><strong>Acesso - ponto de venda</strong></div>
                <div class="form_inputs">
                    <form action="/creditos/loginEf.php" method="post">
                        <?php
                        if (array_key_exists('pag', $_GET) ) {
                            if (!empty($_GET['pag'])) {
                                ?><input type="hidden" name="pag" value="<?php echo $_GET['pag'];?>" /><?php
                            }
                        }//end if (array_key_exists('pag', $_REQUEST) )
                        ?>
                        <input type="text" name="login" placeholder="Login" autocomplete="false" /><br />
                        <input type="password" name="senha" placeholder="Senha" />
                        <input id="" type="submit" class="btnLogin" value="Login" /><br />
                        <span><a href="/creditos/conta/esqueci_senha.php">Esqueceu sua senha?</a></span>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>