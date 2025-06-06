<?php 
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
require_once "../../includes/constantes.php";
require_once DIR_CLASS . "util/Util.class.php";
require_once DIR_CLASS ."pdv/controller/HeaderController.class.php";
//Recupera carrinho do session
$_PaginaOperador2Permitido = 54; 

$controller = new HeaderController;

require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/header.php";

$msg = (isset($_POST['msg'])) ? $_POST['msg'] : "";
$link = (isset($_POST['link'])) ? $_POST['link'] : "";
$titulo = (isset($_POST['titulo'])) ? $_POST['titulo'] : "";

?>
<div class="container txt-azul-claro bg-branco p-bottom40">
    <div class="col-md-12 top20 txt-vermelho">
        <?php echo str_replace("\n", "<br>", $msg);?>
    </div>
    <div class="col-md-12 top20">
        <a href="/creditos/index.php"><button type="button" class="btn btn-info">Voltar para Página Inicial</button></a>
    </div>
</div>

<div id="modal-load" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal">&times;</button>
              <h4 class="modal-title txt-vermelho" id="modal-title"><?php echo $titulo; ?></h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger" id="tipo-modal" role="alert"> 
                    <h5><span id="error-text"><?php echo $msg; ?></span></h5>
              </div>             
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-info" data-dismiss="modal" action="/creditos/index.php">Voltar para Página Inicial</button>
                <button type="button" class="btn btn-default" data-dismiss="modal" action="">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script>
        $("#modal-load").modal();
<?php 
    if(!empty($link)){ 
?>
        $('#modal-load').on('hidden.bs.modal', function () { location.href='<?php echo $link;?>' });
<?php
    } 
?>
</script>
<?php
require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/footer.php";
?>