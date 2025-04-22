<?php

header("location: https://www.e-prepag.com.br/game/conta/nova.php");
require_once "../../../includes/constantes.php";
require_once DIR_CLASS . "gamer/controller/HeaderController.class.php";
require_once RAIZ_DO_PROJETO . 'consulta_cpf/config.inc.cpf.php';
$usuarios = new UsuarioGames;

if(isset($_POST['login'])){
    
    require_once DIR_CLASS . "util/Validate.class.php";
    require_once DIR_CLASS . "util/Login.class.php";
    
    $erros = array();
    $validate = new Validate;
    $clsLogin = new Login($_POST['senha']);
    
    if($validate->qtdCaracteres($_POST['login'], 5, 100) > 0)
        $erros[] = "<p>O Login deve ter mais de 5 caracteres.</p>";
    
    if($validate->email($_POST['email']) > 0 || $_POST['email'] != $_POST['conf_mail'])
        $errros[] = "<p>A confirmação de e-mail está incorreta. Verifique os dados inseridos.</p>";
    
    if($clsLogin->valida() > 0 || $_POST['senha'] != $_POST['conf_senha']){
        $erros[] = "<p>Senha não atinge os níveis de segurança desejados.</p>";
    }
    
    if(empty($erros)){
        $usuarios = new UsuarioGames;
        $usuarios->setLogin($_POST['login']);
        $usuarios->setEmail($_POST['email']);
        $usuarios->setSenha($_POST['senha']);
        $insere = $usuarios->inserirMelhorado();

        if(is_array($insere)){
            $erros = $insere;
        }else{
            Util::redirect("/game/");
        }
    }
}

$controller = new HeaderController;
$controller->setHeader();

require_once RAIZ_DO_PROJETO . "public_html/game/includes/termos-de-uso.php";
$termosDeUso = strip_tags($termosDeUso);
?>
<script src="/js/valida.js"></script>
<script src="/js/validaSenha.js"></script>
<script>
<?php
    if(!empty($erros)){
        print "manipulaModal(1,\"".implode($erros)."\",'Atenção');";
    }
?>
$(function(){
    $("#cadastro").submit(function(){
        
        if($("#termos_uso").is(":checked")){
            if(!valida()){
                return false;
            }

            var erro = validaFormSenha();
            if(erro.length > 0)
            {
                manipulaModal(1,erro.join("<br>"),'Erro');
                return false;
            }
        }else{
            manipulaModal(1,"Você deve concordar com os termos de uso.",'Erro');
            return false;
        }
        
    });
});
</script>
<div class="container txt-cinza bg-branco  p-bottom40">
    <div class="row top10">
        <div class="col-md-12 txt-verde top10">
            <span class="glyphicon glyphicon-triangle-right graphycon-big pull-left" aria-hidden="true"></span><strong class="pull-left"><h4 class="top20">novo usuário?<span class="hidden-md hidden-lg"><br></span> faça aqui um rápido cadastro!</h4></strong>
        </div>
        <div class="clearfix"></div>
        <div class="col-md-6 col-lg-6 col-sm-12 col-xs-12 borda-colunas-formas-pagamento">
            <form id="cadastro" method="post" class="text-right-lg text-rightmd text-left-sm text-left-xs">
                <div class="row top10">
                    <div class="col-md-6">
                        <label for="login">Login:</label>
                    </div>
                    <div class="col-md-6">
                        <input class="form-control" id="login" char="5" name="login" type="text" value="<?php if(isset($_POST['login'])) print $_POST['login'];?>">
                    </div>
                </div>
                <div class="row top20">
                    <div class="col-md-6">
                        <label for="email">Digite seu e-mail:</label>
                    </div>
                    <div class="col-md-6">
                        <input class="form-control" id="email" char="5" maxlength="100" name="email" type="text" value="<?php if(isset($_POST['email'])) print $_POST['email'];?>">
                    </div>
                </div>
                <div class="row top10">
                    <div class="col-md-6">
                        <label for="conf_mail">Confirmação de e-mail:</label>
                    </div>
                    <div class="col-md-6">
                        <input class="form-control" id="conf_mail" onpaste="return false;" char="5" maxlength="100" name="conf_mail" type="text" value="<?php if(isset($_POST['conf_mail'])) print $_POST['conf_mail'];?>">
                    </div>
                </div>
                <div class="col-md-6 col-md-offset-6 col-sm-12 col-xs-12 txt-preto text-left">
                    <span>Sua senha deve ter:</span>
                    <ul>
                        <li>De 6 a 12 caracteres</li>
                        <li>Letras</li>
                        <li>Números</li>
                        <li>Caracteres especiais (!,?,*,$,%)</li>
                    </ul>
                </div>
                <div class="row top10">
                    <div class="col-md-6">
                        <label for="senha">Senha:</label>
                    </div>
                    <div class="col-md-6">
                        <input class="form-control novaSenha" maxlength="12" autocomplete="new-password" onpaste="return false;" char="6" id="senha"  name="senha" char="3" type="password" value="">
                    </div>
                </div>
                <div class="row top10">
                    <div class="col-md-6">
                        <label for="conf_senha">Confirmação de senha:</label>
                    </div>
                    <div class="col-md-6">
                        <input class="form-control confirmacaoSenha" maxlength="12" onpaste="return false;" autocomplete="new-password" onpaste="return false;" char="6" id="conf_senha" char="3" name="conf_senha" type="password" value="">
                    </div>
                </div>
                <div class="row top10">
                    <div class="col-md-6 col-md-offset-6">
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
                <div class="row top10">
                    <div class="col-md-6">
                        <label for="comment" >Termos de uso:</label>
                    </div>
                    <div class="col-md-6">
                        <textarea class="form-control" rows="5" readonly="readonly"><?php echo $termosDeUso;?></textarea>
                    </div>
                </div>
                <div class="row top10 ">
                    <div class="col-md-6 col-lg-6 col-sm-10 col-xs-10">
                        <label for="termos_uso" >Li e aceito os termos de uso:</label>
                    </div>
                    <div class="col-md-6 col-lg-6 col-sm-2 col-xs-2 text-left">
                        <input id="termos_uso" type="checkbox" char="1" class="" name="termos_uso" value="">
                    </div>
                </div>
                <div class="row top10 ">
<!--                    <div class="col-md-6 col-lg-6 col-sm-10 col-xs-10">
                    </div>-->
                    <div class="col-md-12 col-lg-12 col-sm-12 col-xs-12 text-left">
                        <ul>
                            <li>Esta conta deve ser utilizada para compras de créditos para uso pessoal.</li>
                            <li>Limite de compras diário de R$<?php echo number_format($GLOBALS['RISCO_GAMERS_TOTAL_DIARIO'], 2) ?>, condicionado ao máximo de <?php echo CPF_QUANTIDADE_LIMITE ?> compras em 30 dias.</li>
                            <li>Não é permitida a comercialização dos créditos adquiridos. Quer ser um ponto de venda? Acesse: <a href="https://e-prepagpdv.com.br/" target="_blank">https://e-prepagpdv.com.br/</a></li>
                        </ul>
                    </div>
                </div>
                <div class="row top10">
                    <div class="col-md-6 col-md-offset-6">
                        <input type="submit" class="pull-right btn btn-success" value="Prosseguir">
                    </div>
                </div>
            </form>
        </div>
        <div class="col-md-6 col-lg-6 col-sm-12 col-xs-12">
            <div class="col-md-12 col-lg-12 col-sm-12 col-xs-12 txt-azul-claro">
                <h2>Seja um ponto de venda</h2>
            </div>
            <div class="col-md-12 col-lg-12 col-sm-12 col-xs-12">
                <h4>Deseja cadastrar seu estabelecimento para vender créditos de games e outros serviços?</h4>
            </div>
            <div class="col-md-1 col-lg-1 col-sm-2 col-xs-2">
                <span class="glyphicon glyphicon-check txt-verde t0"></span>
            </div>
            <div class="col-md-11 col-lg-11 col-sm-10 col-xs-10">
                Mais de 1.000 games
            </div>
            <div class="col-md-1 col-lg-1 col-sm-2 col-xs-2">
                <span class="glyphicon glyphicon-check txt-verde t0"></span>
            </div>
            <div class="col-md-11 col-lg-11 col-sm-10 col-xs-10">
                Sistema 100% online
            </div>
            <div class="col-md-1 col-lg-1 col-sm-2 col-xs-2">
                <span class="glyphicon glyphicon-check txt-verde t0"></span>
            </div>
            <div class="col-md-11 col-lg-11 col-sm-10 col-xs-10">
                Sem custo de cadastro
            </div>
            <div class="col-md-6 col-lg-6 col-sm-12 col-xs-12 top10 align-center">
                <a href="/cadastro-de-ponto-de-venda.php" class="btn btn-info"><em>Faça agora seu cadastro</em></a>
            </div>
            <div class="col-md-6 col-lg-6 col-sm-12 col-xs-12 top10 bottom10 align-center">
                <a link="e-prepagpdv.com.br/" href="#" class="btn redirecionamento btn-info"><em>Veja aqui como funciona</em></a>
            </div>
        </div>
    </div>
</div>
</div>
<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 bg-info align-center borda-direita-basica p-bottom10">
        <h3>
            Quer ser um ponto de venda ?  
            <a href="/cadastro-de-ponto-de-venda.php" class="txt-branco" target="_blank"><b><span class="link-destaque">Cadastre-se</span></b></a>
            ou 
            <a href="https://e-prepagpdv.com.br/" class="txt-branco" target="_blank"><b><span class="link-destaque">saiba mais</span></b></a>.            
        </h3>
    </div>
</div>
<?php
require_once RAIZ_DO_PROJETO . "public_html/game/includes/footer.php";