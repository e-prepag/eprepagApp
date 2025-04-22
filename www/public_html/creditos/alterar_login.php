<?php

	header('Location: https://www.e-prepag.com.br/creditos/index.php');
	die();

/*
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
require_once "../../includes/constantes.php";
require_once DIR_CLASS."pdv/controller/MeuCadastroController.class.php";
$controller = new MeuCadastroController;

$banner = $controller->getBanner();

if($_POST['senha'] && $_POST['login']){
    
    require DIR_CLASS."util/Validate.class.php";
    
    $cad_login = $controller->usuarios->getLogin();
    //Variaveis do Formulario
    $login = $_POST['login'];
    $senha = $_POST['senha'];

    //Validacoes
    $msg = "";	
    $cor = "txt-vermelho";

    $valida = new Validate();
    
    if($valida->qtdCaracteres($login,6,100)){
        $msg = "O login deve ter mais que 6 e menos que 100 caracteres de tamanho";
    }
    
    if(!$valida->caracteresEspeciais($login)){
        $msg = "O login não deve ter caracteres especiais (|,!,?,*,$,%, etc).";
    }

    //Altera login
    if($msg === ""){
        
        $attr['campo'] = "login";
        $attr['loginAntigo'] = $controller->usuarios->getLogin();
        $attr['email'] = $controller->usuarios->getEmail();
        $attr['nome'] = $controller->usuarios->getNome();
        $attr['value'] = $login;        
        $instUsuarioGames = new UsuarioGames();
        $retorno = $instUsuarioGames->alterarAcesso($attr, $senha, $controller->usuarios->getId());
        
        $msg = $retorno['msg'];
        if($retorno["sucesso"]){
            $cor = "txt-verde";
            $_SESSION = array();
            session_destroy();
        }else{
            $cor = "txt-vermelho";
        }

    }
}
    
require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/header.php";
?>
<div class="container bg-branco">
    <div class="row">
        <div class="col-md-10 txt-preto">
            <div class="row">
                <div class="col-md-12 espacamento txt-azul-claro">
                    <strong>Alterar Login</strong>
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
                        <input type="hidden" id="loginAtual" value="<?= $controller->usuarios->getLogin();?>">
                        <span class="col-md-6 col-xs-12 col-sm-12 text-right">
                            <label class="<?php if(isset($txtVermelho)) echo $txtVermelho;?>" for="login">Novo Login: </label>
                        </span>
                        <span class="col-md-6 col-xs-12 col-sm-12">
                            <input type="text" label="Login " name="login" char="6" maxlength="100" id="login" class="form-control">
                        </span>
                        <span class="col-md-6 col-xs-12 col-sm-12 text-right top5">
                            <label class="<?php if(isset($txtVermelho)) echo $txtVermelho;?>" for="confLogin">Confirmar Login: </label>
                        </span>
                        <span class="col-md-6 col-xs-12 col-sm-12 top5">
                            <input type="text" label="Login " name="confLogin" onpaste="return false;" char="6" maxlength="100" id="confLogin" class="form-control">
                        </span>
                        <span class="col-md-6 col-xs-12 col-sm-12 top5 text-right">
                            <label for="cad_senhaConf">Senha: </label>
                        </span>
                        <span class="col-md-6 col-xs-12 col-sm-12 top5">
                            <input type="password" name="senha" label="Confirmação de senha " id="senha" char="6" maxlength="12" class="form-control">
                        </span>
                        <span class="col-md-offset-6  col-md-6 fontsize-pp">
                            <input type="button" class="top10 btn btn-info salvar bottom20" value="Alterar">
                        </span>
                        </div>
                    </form>
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

        if($("#login").val().toUpperCase() === $("#loginAtual").val().toUpperCase()){
            erro.push("Este é o seu login atual.");
        }
        
        $(".form-control").each(function(){
            if($(this).val().length < $(this).attr("char")){
                erro.push($(this).attr("label")+" deve ter "+$(this).attr("char")+" caracteres.");
                $("label[for='"+$(this).attr("id")+"']").addClass("txt-vermelho");
            }else{
                $("label[for='"+$(this).attr("id")+"']").removeClass("txt-vermelho");
            }
        });
        
        if($("#login").val() !== $("#confLogin").val()){
            erro.push("Login de confirmação não confere com login digitado.");
            $("label[for='login']").addClass("txt-vermelho");
            $("label[for='confLogin']").addClass("txt-vermelho");
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

*/