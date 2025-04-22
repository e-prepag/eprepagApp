<?php
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/gamer/chave.php";
require_once $raiz_do_projeto."includes/gamer/AES.class.php";
require_once $raiz_do_projeto."class/util/Login.class.php";


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

if(isset($_POST['pass_old'])) {
    $erros = 0;
    $msg = "";
    
    if($_POST['nova_senha'] !== $_POST['pass_confirm']) {
        $erros++;
        $msg = "Confirmação de senha está diferente.";
        $color = "txt-vermelho";
    }
    else {
        
        $clsLogin = new Login($_POST['nova_senha']);
        $clsLogin->setLimiteCaracteres($minCaracPass, $maxCaracPass);

        if($clsLogin->valida() > 0){
            $erros++;
            $msg = "Senha não atinge os níveis de segurança desejados.";
            $color = "txt-vermelho";
        }
    }
    
    if($erros === 0){
        $passw = $_POST['pass_old'];
        
        $passw = base64_encode($aes->encrypt(addslashes($passw)));

        $pdo = $con->getLink();
        $sql = "SELECT * FROM usuarios WHERE id = ? AND shn_password = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($_SESSION["iduser_bko"], $passw));

        $fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($fetch) == 1) {
            $passw = base64_encode($aes->encrypt(addslashes($_POST['nova_senha'])));

            $update = "UPDATE usuarios set shn_password = ? where id = ?";
            $stmt = $pdo->prepare($update);
            $stmt->execute(array($passw,$_SESSION["iduser_bko"]));
            if($stmt->rowCount() == 1){
                $msg = "Senha alterada com sucesso.";
                $color = "txt-verde";
                @session_start();
                session_destroy();
                session_unset();
            }else{
                $msg = "Erro ao alterar senha. Entre em contato com o suporte.";
                $color = "txt-vermelho";
            }        
        }
        else {
            $msg = "Senha atual incorreta.";
            $color = "txt-vermelho";
        }
    }
    
}
?>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><?php echo $sistema->item->getDescricao() ; ?></li>
    </ol>
</div>    
<?php
if(isset($msg)) {
?>
    <div class="col-md-12 espacamento <?php echo $color;?>">
        <strong><?php echo $msg?></strong>
    </div>
<?php
}
if(isset($msg) && $color == "txt-verde") {
?>
    <table class="pull-left" style="margin: 15px;">
        <tr>
            <td colspan="2" class="<?php echo $color;?>"><?php echo $msg;?><td>
        </tr>
        <tr>
            <td colspan="2"><a href="/logout.php" class="btn pull-left btn-success">Clique aqui para acessar com sua nova senha.</a><td>
        </tr>
    </table>
<?php    
}
else {
?>
<form method="POST" id="form">
    <div class="col-md-7 espacamento txt-preto">
        <div class="form-group col-md-12 has-feedback">
            <label class="control-label col-md-7 text-right" for="pass_old">
                Senha atual
            </label>
            <div class="col-md-5">
                <input type="password" name="pass_old" label="senha atual " char="4" class="form-control" id="pass_old">
            </div>
        </div>
        <div class="form-group col-md-12 has-feedback">
            <label class="control-label  text-right col-md-7" for="nova_senha">
                Nova senha
            </label>
            <div class="col-md-5">
                <input type="password" name="nova_senha" label="senha atual " char="6" class="form-control novaSenha" id="nova_senha">
            </div>
            <div class="col-md-offset-7 col-md-5 top5">
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
            <div class="col-md-offset-7 col-md-5 txt-vermelho fontsize-p">
                <span class="text-right"><?php echo vsprintf('*Sua senha deve ter: de %s a %s caracteres, letras, números, caracteres especiais (|,!,?,*,$, etc)',array($minCaracPass,$maxCaracPass));?></span>
            </div>
        </div>
        <div class="form-group col-md-12 has-feedback">
            <label class="control-label  text-right col-md-7" for="pass_confirm">
                Confirme a nova senha
            </label>
            <div class="col-md-5">
                <input type="password" name="pass_confirm" label="senha atual "  char="6" class="form-control confirmacaoSenha" id="pass_confirm">
            </div>
        </div>
    </div>
    <div class="col-md-5 espacamento">
        <button type="submit" class="btn btn-success">Salvar</button>
    </div>
</form>
<script src="<?php echo $server_url_ep;?>/js/validaSenha.js"></script>
<?php
}
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</body>
</html>