<?php 
header("Content-Type: text/html; charset=ISO-8859-1",true);
require_once "../../../includes/constantes.php";
require_once DIR_CLASS . "util/Util.class.php";
require_once DIR_INCS . "main.php";
require_once DIR_INCS . "gamer/main.php";
require_once DIR_CLASS . "gamer/classIntegracao.php";
@session_start();

if(!$pt) $pt = $_REQUEST['pt'];
$msg = (isset($_POST['msg'])) ? $_POST['msg'] : "";
$msg .= (isset($_GET['msg'])) ? $_GET['msg'] : "";
$link = (isset($_POST['link'])) ? $_POST['link'] : "";
$titulo = (isset($_POST['titulo'])) ? $_POST['titulo'] : "";
$pagina_titulo = $pt; 

require_once RAIZ_DO_PROJETO . "public_html/prepag2/commerce/includes/cabecalho_int.php"; 

?>
<div class="container txt-azul-claro bg-branco p-bottom40">
    <div class="col-md-12 top20 txt-vermelho">
        <?php echo str_replace("\n", "<br>", $msg);?>
    </div>
    <div class="col-md-12 top20">
        <a href="/prepag2/commerce/integracao_sd.php"><button type="button" class="btn btn-info">Voltar para Página Inicial</button></a>
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
require_once RAIZ_DO_PROJETO . "public_html/prepag2/commerce/includes/rodape.php"; ?>
