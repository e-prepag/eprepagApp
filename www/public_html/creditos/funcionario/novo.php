<?php

require_once "../../../includes/constantes.php";
require_once DIR_CLASS ."pdv/controller/FuncionarioController.class.php";

$controller = new FuncionarioController;

if($_POST['btSubmit']){
    $controller->salva();
}

$banner = $controller->getBanner();

require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/header.php";

?>
<div class="container txt-azul-claro bg-branco">
    <div class="row">
        <div class="col-md-10">
            <div class="row">
                <div class="col-md-12 espacamento">
                    <strong>GERENCIAR FUNCIONÁRIO</strong>
                </div>
            </div>
<?php
            if($controller->msg != "")
            {
?>
            <div class="row">
                <div class="col-md-12">
                    <p class="txt-vermelho"><?php echo $controller->msg; ?></p>
                </div>
            </div>
<?php
            }
?>            
            <div class="row txt-cinza">
                <div class="col-md-12 col-lg-12 col-sm-12 col-xs-12 top10 espacamento txt-azul-claro">
                    <strong>Novo Funcionário</strong>
                </div>
                <form method="post">
                    <div class="col-md-2 col-lg-2 col-sm-12 col-xs-12 top10 text-right-lg">Nome:</div>
                    <div class="col-md-10 col-lg-10 col-sm-12 col-xs-12 top10"><input class="input-sm form-control input-medium" <?php if(isset($_POST['cad_nome'])) echo "value='".$_POST['cad_nome']."'";?> type="text" name="cad_nome" id="cad_nome"></div>
                    <div class="col-md-2 col-lg-2 col-sm-12 col-xs-12 top10 text-right-lg">Login:</div>
                    <div class="col-md-10 col-lg-10 col-sm-12 col-xs-12 top10"><input class="input-sm form-control input-medium" <?php if(isset($_POST['cad_login'])) echo "value='".$_POST['cad_login']."'";?> type="text" name="cad_login" id="cad_login"></div>
                    <div class="col-md-2 col-lg-2 col-sm-12 col-xs-12 top10 text-right-lg">E-mail:</div>
                    <div class="col-md-10 col-lg-10 col-sm-12 col-xs-12 top10"><input class="input-sm form-control input-medium" <?php if(isset($_POST['cad_email'])) echo "value='".$_POST['cad_email']."'";?> type="text" name="cad_email" id="cad_email"></div>
                    <div class="col-md-2 col-lg-2 col-sm-12 col-xs-12 top10 text-right-lg">Senha:</div>
                    <div class="col-md-10 col-lg-10 col-sm-12 col-xs-12 top10"><input class="input-sm novaSenha form-control input-medium" maxlength="12" autocomplete="off" <?php if(isset($_POST['cad_senha'])) echo "value='".$_POST['cad_senha']."'";?> type="password" name="cad_senha" id="cad_senha"></div>
                    <div class="col-md-offset-2 col-md-10">
                        <div class="progress input-medium">
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
                    <div class="col-md-offset-2 col-md-10 txt-vermelho fontsize-p">
                        *Sua senha deve ter: de 6 a 12 caracteres, letras, números, caracteres especiais (|,!,?,*,$,%, etc)
                    </div>
                    <div class="col-md-2 col-lg-2 col-sm-12 col-xs-12 top10 text-right-lg">Confirmar senha:</div>
                    <div class="col-md-10 col-lg-10 col-sm-12 col-xs-12 top10"><input class="input-sm form-control confirmacaoSenha input-medium" maxlength="12" <?php if(isset($_POST['cad_senhaConf'])) echo "value='".$_POST['cad_senhaConf']."'";?> type="password" name="cad_senhaConf" id="cad_senhaConf"></div>
                    <div class="col-md-2 col-lg-2 col-sm-12 col-xs-12 top10 text-right-lg">Acesso:</div>
                    <div class="col-md-10 col-lg-10 col-sm-12 col-xs-12 top10">
                        <select name="cad_tipo" id="cad_tipo" class="form-control w-auto">
                            <option value="1" <?php if(isset($_POST['cad_tipo']) && $_POST['cad_tipo'] == 1) echo "selected"; ?> >Comprar e emitir</option>
                            <option value="0" <?php if(isset($_POST['cad_tipo']) && $_POST['cad_tipo'] == 0) echo "selected"; ?>>Emitir</option>
                        </select>
                    </div>
                    <div class="col-md-12 col-md-offset-2 col-lg-12 col-lg-offset-2 col-sm-12 col-xs-12  espacamento">
                        <input type="submit" name="btSubmit" id="btSubmit" class="btn btn-info top10" value='Adicionar'>
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
            <div class="row pull-right top10 facebook">
            </div>
        </div>
    </div>
</div>
<link href="/js/jqueryui/css/custom-theme/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="/js/facebook.js"></script>
<script src="/js/jqueryui/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="/js/validaSenha.js"></script>
<?php
require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/footer.php";
?>