<?php
require_once "../../../../includes/constantes.php";
require_once $raiz_do_projeto . "public_html/sys/includes/topo_sys.php";
require_once $raiz_do_projeto . "public_html/sys/includes/gamer/inc_pub_access.php";

include_once $raiz_do_projeto . "db/ConnectionPDO.php";
include_once $raiz_do_projeto . "class/util/Validate.class.php";
require_once $raiz_do_projeto . "includes/gamer/chave.php";
require_once $raiz_do_projeto . "includes/gamer/AES.class.php";
require_once $raiz_do_projeto . "class/util/Login.class.php";
//Instanciando Objetos para Descriptografia
$chave256bits = new Chave();
$aes = new AES($chave256bits->retornaChavePub());

$minCaracPass = 6;
$maxCaracPass = 12;

$con = ConnectionPDO::getConnection();
if ( !$con->isConnected() ) {
    // retornar os erros: $con->getErrors();
    die('Erro#2');
}

if(isset($_POST['pass_old']) && $_POST['pass_old'] != "")
{
    $erros = 0;
    
    if($_POST['nova_senha'] !== $_POST['pass_confirm'])
    {
        $erros++;
        $msg = LANG_WRONG_CONFIRM_PASS;
    }else
    {
        $clsLogin = new Login($_POST['nova_senha']);
        $clsLogin->setLimiteCaracteres($minCaracPass, $maxCaracPass);

        if($clsLogin->valida() > 0){
            $erros++;
            $msg = LANG_PASSW_NOT_SECURITY;
        }
    }

    if($erros > 0)
    {
        if(!isset($msg)) 
            $msg = LANG_WRONG_DATA;
        $color = "txt-vermelho";
        
    }else{

        $passw = base64_encode($aes->encrypt(addslashes($_POST['pass_old'])));

        $pdo = $con->getLink();
        $sql = "SELECT * FROM usuarios WHERE id = ? AND shn_password = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($_SESSION["iduser_bko_pub"], $passw));

        $fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($fetch) == 1) 
        {
            $passwNew = base64_encode($aes->encrypt(addslashes($_POST['nova_senha'])));

            $update = "UPDATE usuarios set shn_password = ? where id = ? and shn_password = ?";
            $stmt = $pdo->prepare($update);
            $stmt->execute(array($passwNew,$_SESSION["iduser_bko_pub"],$passw));
            if($stmt->rowCount() == 1){
                $msg = LANG_SUCCESS_CHANGE_PASS;
                $color = "txt-verde";
                session_start();
                session_destroy();
            }else{
                $msg = LANG_ERROR_CHANGE_PASS.LANG_CONTACT_SUPPORT;
                $color = "txt-vermelho";
            }        
        }else{
            $msg = LANG_WRONG_PASSWORD;
            $color = "txt-vermelho";
        }
    }
    
}

?>
<link href="/includes/bootstrap/css/bootstrap.min_new.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/js/jquery.js"></script>
<script type="text/javascript" src="/includes/bootstrap/js/bootstrap.min.js"></script>
<style>
td { 
    padding: 5px;
}

.top5{
    margin-top: 5px;
}
</style>
<div class="container-fluid">
    <div class="container bg-branco">
            
                <div class="row">
                    <div class="col-md-12 txt-azul-claro top20">                            
                        <strong><?php echo LANG_CHANGE_PASSWORD;?></strong>
                    </div>
                </div>    
       
    <div class="row" style="margin-top:20px; margin: 0 auto; width: 891px;">
        <form method="post" name="alterar_senha" id="alterar_senha">
            <div class="col-md-12 top20">
<?php                
                if(isset($msg) && $color != "txt-verde")
                {
?>
                <div class="col-md-12 text-center" style="margin: 15px;">
                    
                        <div class="<?php echo $color;?>"><?php echo $msg;?></div>
                    
                </div>
<?php
                }
                
                if(isset($msg) && $color == "txt-verde")
                {
?>
                <div class="col-md-12 text-center" style="margin: 15px;">
                    <div class = "row">
                        <div class="<?php echo $color;?>"><?php echo $msg;?></div>
                    </div>
                    <div class = "row top20">
                        <div><a href="/sys/admin/logout.php" class="btn btn-success"><?php echo LANG_CLICK_TO_ACCES_NEW_PASS;?>.</a></div>
                    </div>
                </div>
<?php    
                }else
                {
?>                
                <div class="row to10 txt-cinza">
                    <div class="col-md-4 text-right">
                        <label for="pass_old" ><?php echo LANG_CURRENT_PASS;?>
                    </div>
                    <div class="col-md-5  txt-preto">
                        <input type="password" name="pass_old" label="senha atual " class="form-control" id="pass_old">
                    </div>                    
                </div>
                <div class="row top10 txt-cinza">
                    <div class="col-md-4 text-right">
                        <label for="nova_senha" ><?php echo LANG_NEW_PASS;?>:</label>
                    </div>
                    <div class="col-md-5 txt-preto">
                        <input type="password" name="nova_senha" char="6" label="nova senha " class="form-control novaSenha"  autocomplete="off" id="nova_senha"> 
                    </div>
                </div>
                <div class="row top10 txt-cinza">
                    <div class="col-md-offset-4 col-md-5">
                        <div class="progress">
                            <div class="progress-bar hidden progress-bar-danger" style="width: 33.33%">
                                <span class="sr-only">33.33% Complete (danger)</span>
                            </div>
                            <div class="progress-bar hidden progress-bar-warning" style="width: 33.33%">
                                <span class="sr-only">33.33% Complete (warning)</span>
                            </div>
                            <div class="progress-bar hidden progress-bar-success" style="width: 33.33%">
                                <span class="sr-only">33.33% Complete (success)</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-offset-4 col-md-5 txt-vermelho fontsize-p" style="margin-bottom: 10px">
                    <?php echo vsprintf(LANG_MIN_MAX_PASS,array($minCaracPass,$maxCaracPass));?>
                </div>
                <div class="row top20 txt-cinza">
                    <div class="col-md-4">
                        <span class="pull-right"><label for="pass_confirm" ><?php echo LANG_CONFIRM_NEW_PASS;?>:</label></span>
                    </div>
                    <div class="col-md-5 txt-preto">
                        <input type="password" name="pass_confirm" char="6" label="confirmação de senha " class="form-control confirmacaoSenha" id="pass_confirm">
                    </div>
                </div>
                <div class="row top10">
                    <div class="col-md-offset-4 col-md-5 top10">
                        <button type="button" id="editar" value="Buscar" class="btn pull-right btn-success"><?php echo LANG_PINS_BUTTON_ALTER;?></button>
                    </div>
                </div>
                <div class="col-md-12 top10">
                </div>
            </div>
        </form>  
<?php
                }
?>
    </div>
       
</div>
</div>    
<script>
$(function(){
    $("#editar").click(function(){
        var erro = [];

        $(".form-control").each(function(){
            if($(this).val().length < $(this).attr("char")){
                erro++;
                $("label[for='"+$(this).attr("id")+"']").css("color","red");
            }else{
                $("label[for='"+$(this).attr("id")+"']").css("color","#337ab7");
            }
        });

        if(validaFormSenha().length > 0)
        {
            $("label[for='nova_senha']").css("color","red");
            erro++;
        }
        else{
            $("label[for='nova_senha']").css("color","#337ab7");
        }

         if(erro > 0)
         {
             var msgErro = "<?php echo LANG_INCORRECT_DATA_WRITED;?>";
             alert(msgErro);
         }
         else
         {
            $("#alterar_senha").submit();

         }
    });
});
</script>
<script src= "/js/validaSenha.js"></script>
<div class="col-md-12">
   <?php 
        require_once $raiz_do_projeto . "public_html/sys/includes/rodape_sys.php";
    ?>
</div>
</html>
