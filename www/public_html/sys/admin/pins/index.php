<?php
require_once "../../../../includes/constantes.php";
require_once $raiz_do_projeto . "public_html/sys/includes/topo_sys.php";
require_once $raiz_do_projeto . "public_html/sys/includes/gamer/inc_pub_access.php";
?>
<link rel="stylesheet" href="/css/creditos.css" type="text/css">
 <style>
        body{
            font-size: 14px !important;
            background-color: #fff !important;
        }

        .nav>li>a:focus, .nav>li>a:hover {
            text-decoration: none;
            background-color: #268fbd !important;
            color: #fff;
        }

        .lista{
            margin-bottom: 2px;
            padding: 10px;
        }
    </style>
<div class="container">
    <div class="col-md-12 txt-preto">
        <h4><?php echo LANG_STOCK_PAGE_TITLE; ?></h4>
    </div>
    <div class="col-md-6 text-left">
        <div class="top10 lista bg-azul-claro txt-branco">
            <strong>Pins</strong>
        </div>
        <ul class="nav nav-pills nav-stacked nav-pills-stacked-example">
<?php
        if (!b_is_Publisher() || $sistema->validaAcessoItem("/sys/admin/pins/pins_qtde_resta.php"))  {
?>
            <li role="presentation"><a href="pins_qtde_resta.php" class="menu"><?php echo LANG_STOCK_LINK_TITLE_1; ?></a></li>
<?php
        }
        
        if ( (!b_is_Publisher() ||  b_is_PublisherMostraEstoquePINs() ) || $sistema->validaAcessoItem("/sys/admin/pins/situacao_query.php") ) {
?>
						<li role="presentation"><a href="situacao_query.php" class="menu"><?php echo LANG_STOCK_LINK_TITLE_2; ?></a></li>
<?php
        }
        if (!b_is_Publisher() || $sistema->validaAcessoItem("/sys/admin/pins/lote_carga/pendentes.php"))  {
?>
						<li role="presentation"><a href="lote_carga/pendentes.php" class="menu"><?php echo LANG_STOCK_LINK_TITLE_3; ?></a></li>
						<!--<li role="presentation"><a href="pins_transfer_channel.php" class="menu"><?php echo LANG_PINS_PAGE_TITLE_TRANSFER; ?></a></li>-->
<?php
        }
?>
        </ul>
    </div>
</div>
<?php require_once $raiz_do_projeto . "public_html/sys/includes/rodape_sys.php"; ?>
</body></html>
