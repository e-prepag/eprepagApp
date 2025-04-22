<?php
require_once "../../../includes/constantes.php";
require_once DIR_CLASS ."pdv/controller/FuncionarioController.class.php";

$controller = new FuncionarioController;

$banner = $controller->getBanner();

if($_POST['sel_id'] && $_POST['sel_id'] > 0)
{    
    if($_POST["btSubmit"])
    {
        $controller->alteraSenha($_POST['sel_id']);
    }
    
    $funcionario = $controller->pega($_POST['sel_id']);
}
else
{
    die("ERRO");
}

if($controller->usuarios->getDataExpiraSenha() != "" && $controller->validaSenhaExpirada() && $msg == ""){
    $cor = "txt-vermelho";
    $msg = "Sua senha expirou. Para sua segurança é necessário que você cadastre uma nova senha antes de acessar o sistema. <br>Siga as instruções abaixo ou qualquer dúvida entre em contato com o suporte.";
}
    
require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/header.php";
?>
<div class="container bg-branco">
    <div class="row">
        <div class="col-md-10 col-lg-10 col-sm-12 col-xs-12 txt-preto">
            <div class="row">
                <div class="col-md-12 espacamento txt-azul-claro">
                    <strong>Alterar senha</strong>
                </div>
            </div>
<?php
            if(isset($controller->msg))
            {
?>
            <div class="row">
                <div class="col-md-12 col-lg-12 col-sm-12 col-xs-12 espacamento">
                    <p><?php echo $controller->msg; ?></p>
                </div>
            </div>
<?php                
            }
    
?>          
            <div class="row top5">
                <div class="col-md-6 col-lg-6 col-sm-12 col-xs-12">
                    <form name="form1" action="" method="post">
                        <div class="row">
                            <span class="col-md-6 col-lg-6 col-sm-4 col-xs-4 text-right">Nome: </span>
                            <span class="col-md-6 text-left col-lg-6 col-sm-8 col-xs-8">
                                <input type="text" name="ugo_nome" class="form-control" readonly="readonly" value="<?php echo $funcionario['ugo_nome'];?>">
                            </span>
                        </div>
                        <div class="row top5">
                            <span class="col-md-6 col-lg-6 col-sm-4 col-xs-4 text-right">Login:</span>
                            <span class="col-md-6 text-left col-lg-6 col-sm-8 col-xs-8">
                                <input type="text" name="ugo_login" class="form-control" readonly="readonly" value="<?php echo $funcionario['ugo_login'];?>">
                            </span>
                        </div>
                        <div class="row top5">
                            <span class="col-md-6 col-lg-6 col-sm-4 col-xs-4 text-right"><label for="cad_senha">Nova senha: </label></span>
                            <span class="col-md-6 col-lg-6 col-sm-8 col-xs-8">
                                <input type="password" maxlength="12" char="6"  label="Nova senha " id="novaSenha" name="novaSenha" class="form-control novaSenha">
                            <div class="hidden-md hidden-lg">
                                <div class=" top10 txt-vermelho">
                                    *Sua senha deve ter<br>
                                    - de 6 a 12 caracteres<br>
                                    - letras<br>
                                    - números<br>
                                    - caracteres especiais (|,!,?,*,$,%, etc)"
                                </div>
                            </div>
                            </span>
                        </div>
                        <div class="row top5">
                            <div class="col-md-6 col-md-offset-6 col-lg-6 col-lg-offset-6 col-sm-8 col-sm-offset-4 col-xs-8 col-xs-offset-4">
                                <div class="progress w-auto">
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
                        <div class="row top5">
                            <span class="col-md-6 text-right col-lg-6 col-sm-4 col-xs-4">
                                <label for="cad_senhaConf">Confirmar senha: </label>
                            </span>
                            <span class="col-md-6 col-lg-6 col-sm-8 col-xs-8">
                                <input type="password" name="novaSenhaConf" label="Confirmação de senha " id="novaSenhaConf" char="6" maxlength="12" class="form-control confirmacaoSenha">
                            </span>
                        </div>
                        <div class="row top10 bottom10">
                            <span class="col-md-offset-6 col-md-6 col-lg-6 col-lg-offset-6 col-sm-8 col-sm-offset-4 col-xs-8  col-xs-offset-4 fontsize-pp">
                                <input type="hidden" name="sel_id" id="sel_id" class="btn btn-info" value='<?php echo $_POST['sel_id']; ?>'>
                                <input type="submit" name="btSubmit" id="btSubmit" class="btn btn-info" value='Editar'>
                            </span>
                        </div>
                    </form>
                </div>
                <div class="col-md-4 hidden-xs hidden-sm">
                    <div class="row top10 txt-vermelho">
                        *Sua senha deve ter<br>
- de 6 a 12 caracteres<br>
- letras<br>
- números<br>
- caracteres especiais (|,!,?,*,$,%, etc)"
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2 p-top10 hidden-sm hidden-xs">
<?php 
            if($banner){
                foreach($banner as $b){
?>
                <div class="row pull-right">
                    <a href="<?php echo $b->link; ?>" class="banner" id="<?php echo $b->id; ?>" target="_blank"><img src="<?php echo $controller->objBanner->urlLink.$b->imagem; ?>" width="186" class="p-3" title="<?php echo $b->titulo; ?>"></a>
                </div>
<?php 
                }
            }
?>
            <div class="row pull-right facebook">
            </div>
        </div>
    </div>
</div>
<link href="/js/jqueryui/css/custom-theme/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="/js/facebook.js"></script>
<script src="/js/jqueryui/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="/js/validaSenha.js"></script>
<script>
$(function(){
    $(".salvar").click(function(){
        var form = $(this).closest("form");
        var erro = [];
        
        $(".form-control").each(function(){
            if($(this).val().length < $(this).attr("char")){
                erro.push($(this).attr("label")+" deve ter "+$(this).attr("char")+" caracteres.");
                $("label[for='"+$(this).attr("id")+"']").addClass("txt-vermelho");
            }else{
                $("label[for='"+$(this).attr("id")+"']").removeClass("txt-vermelho");
            }
        });
        
        if(erro.length == 0){
            erro = validaFormSenha(); //funcao esta em /js/validaSenha.js
        }
        
        if(erro.length > 0)
        {
            alert(erro.join("\n"));

        }else{
            $(form).submit();    
        }
    });
});
</script>
<?php
require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/footer.php";
