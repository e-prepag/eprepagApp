<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
require_once "../../includes/constantes.php";
require_once DIR_CLASS."pdv/controller/MeuCadastroController.class.php";

$controller = new MeuCadastroController;

$banner = $controller->getBanner();

if($_POST['cad_senha']){
    
    require DIR_CLASS."util/Login.class.php";
    
    $cad_login = $controller->usuarios->getLogin();
    //Variaveis do Formulario
    $cad_senha = $_POST['cad_senha'];
    $cad_senhaConf = $_POST['cad_senhaConf'];
    $cad_senhaAtual = $_POST['cad_senhaAtual'];

    $clsLogin = new Login($cad_senha);
   
    //Validacoes
    $msg = "";	
    $cor = "txt-vermelho";

    if($clsLogin->valida() > 0){
        $msg = "Senha não atinge os níveis de segurança desejados.";
    }
    
    if($cad_senha == $cad_senhaAtual){
        $msg = "Nova senha deve ser diferente da atual.";
    }

    //Valida dados do login
    if($msg == ""){
            $instUsuarioGames = new UsuarioGames();  
            $msg = $instUsuarioGames->validarCamposLogin($cad_senha, $cad_senhaConf, $cad_login);
    }

    //Altera senha
    if($msg == ""){
        $instUsuarioGames = new UsuarioGames(); 
        $msg = $instUsuarioGames->alterarSenha($cad_senha, $cad_senhaAtual, $cad_login);
        $controller->usuarios = unserialize($_SESSION['dist_usuarioGames_ser']);
        
        if(!$msg){
            $msg = "Senha atual inválida.";
            $txtVermelho = "txt-vermelho";
        }else{
            $msg = "";
        }

    }

    if($msg == ""){
        $cor = "txt-verde";
        $msg = "Senha alterada com sucesso!";
    }
}

if($controller->usuarios->getDataExpiraSenha() != "" && $controller->validaSenhaExpirada() && $msg == ""){
    $cor = "txt-vermelho";
    $msg = "Sua senha expirou. Para sua segurança é necessário que você cadastre uma nova senha antes de acessar o sistema. <br>Siga as instruções abaixo ou qualquer dúvida entre em contato com o suporte.";
}
    
require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/header.php";
?>
<div class="container bg-branco">
    <div class="row">
        <div class="col-md-10 txt-preto">
            <div class="row">
                <div class="col-md-12 espacamento txt-azul-claro">
                    <strong>Alterar senha</strong>
                </div>
            </div>
<?php
            if(isset($msg))
            {
?>
            <div class="row">
                <div class="col-md-12 espacamento <?php echo $cor; ?>">
                    <p><?php echo $msg; ?></p>
                </div>
            </div>
<?php                
            }
    
?>          
            <div class="row top5">
                <div class="col-md-6">
                    <form name="form1" action="" method="post">
                            <span class="col-md-offset-2 col-md-4 col-xs-12 col-sm-12"><label class="<?php if(isset($txtVermelho)) echo $txtVermelho;?>" for="cad_senhaAtual">Senha atual: </label></span>
                            <span class="col-md-6 col-xs-12 col-sm-12"><input type="password" label="Senha atual " name="cad_senhaAtual" id="cad_senhaAtual" class="form-control"></span>
                            <span class="col-md-offset-2 col-md-4 col-xs-12 top5  col-sm-12"><label for="cad_senha">Nova senha: </label></span>
                            <span class="col-md-6 top5 col-xs-12 col-sm-12"><input type="password" maxlength="12" char="6"  label="Nova senha " id="cad_senha" name="cad_senha" class="form-control novaSenha"></span>
                            <div class="col-xs-12 col-sm-12 hidden-md hidden-lg">
                                <div class=" top10 txt-vermelho">
                                    *Sua senha deve ter<br>
                                    - de 6 a 12 caracteres<br>
                                    - letras<br>
                                    - números<br>
                                    - caracteres especiais (|,!,?,*,$,%, etc)"
                                </div>
                            </div>
                            <div class="col-md-6 col-xs-12 col-sm-12 top5 col-md-offset-6">
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
                            <span class="col-md-offset-2 col-md-4 col-xs-12 col-sm-12"><label for="cad_senhaConf">Confirmar senha: </label></span>
                            <span class="col-md-6 col-xs-12 col-sm-12"><input type="password" name="cad_senhaConf" label="Confirmação de senha " id="cad_senhaConf" char="6" maxlength="12" class="form-control confirmacaoSenha"></span>
                            <span class="col-md-offset-6  col-md-6 fontsize-pp">
                                <input type="button" class="top10 btn btn-info salvar bottom20" value="Salvar">
                            </span>
                        </div>
                    </form>
                    <div class="col-md-4 hidden-sm hidden-xs">
                        <div class=" top10 txt-vermelho">
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