<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
require_once "../../includes/constantes.php";
require_once DIR_CLASS ."pdv/controller/OffLineController.class.php";

$controller = new OfflineController;
session_destroy();
$pagina_titulo = "ERRO";

if($_GET['ERRO'] == 2499){
    $msg = "Desculpe. A senha de administrador do Ponto de Venda expirou. Para sua segurança é necessário que o Administrador cadastre uma nova senha antes do acesso ser liberado. Qualquer dúvida entre em contato com o suporte E-Prepag.";
}else{
    
    $cod = isset($_GET['err']) ? $_GET['err'] : null;
    
    $msg = "Erro desconhecido. [$cod]";
}

require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/header-offline.php";
?>
<div class="row  div-leia-mais hide">
    <div class="col-md-11  p-left25 p-top10 div-saldo-limite-interna">
        <p class="txt-vermelho"><strong><?php echo $msg;?></strong></p>
    </div>
    <div class="col-md-1 txt-preto">
        <div class="veja-mais glyphicon glyphicon-remove c-pointer"></div>
    </div>
</div>
<div class="container bg-branco">
    <div class="row">
        <div class="col-md-10 erro txt-preto">
                <div class="col-md-12 espacamento txt-vermelho">
                    <?php echo $msg;?>
                </div>

                <div class="col-md-12 espacamento txt-cinza-2">
                    <strong><a href="/creditos/">Clique aqui</a> </strong>para ir para a página de login.
                </div>
        </div>
    </div>
</div>
<link href="/js/jqueryui/css/custom-theme/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="/js/facebook.js"></script>
<script src="/js/jqueryui/js/jquery-ui-1.9.2.custom.min.js"></script>
<?php
require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/footer.php";
