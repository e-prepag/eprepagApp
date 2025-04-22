<?php 
/*
 * 
 * Página para tratamento de erros e mensagens
 * parâmetros enviados via post: "msg", "link" e "titulo"
 * msg: mensagem a ser exibida no modal e no fundo
 * link: link de redirecionamento apos fechar o modal - não obrigatório
 * titulo: titulo a ser exibido no modal
 * 
 */
require_once "../../includes/constantes.php";
require_once DIR_CLASS . "util/Util.class.php";
require_once DIR_CLASS ."gamer/controller/HeaderController.class.php";

$controller = new HeaderController;
$controller->setHeader();

$msg = (isset($_POST['msg'])) ? $_POST['msg'] : "";
$link = (isset($_POST['link'])) ? $_POST['link'] : "";
$titulo = (isset($_POST['titulo'])) ? $_POST['titulo'] : "";

?>
<div class="container txt-azul-claro bg-branco p-bottom40">
    <div class="col-md-12 top20 txt-vermelho">
        <?php echo str_replace("\n", "<br>", $msg);?>
    </div>
    <div class="col-md-12 top20">
        <a href="/game/index.php"><button type="button" class="btn btn-info">Voltar para Página Inicial</button></a>
    </div>
</div>
</div>
<script src="/js/valida.js"></script>
<script>
    manipulaModal(1,'<?php echo str_replace("\n","<br>",$msg);?>','<?php echo $titulo;?>');
<?php 
    if(!empty($link)){ 
?>
    $('#modal-load').on('hidden.bs.modal', function () { location.href='<?php echo $link;?>' });
<?php
    } 
?>
</script>
<?php
require_once RAIZ_DO_PROJETO . "public_html/game/includes/footer.php";
?>