<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."class/util/Validate.class.php";
require_once $raiz_do_projeto."class/util/Util.class.php";

$con = ConnectionPDO::getConnection();
if ( !$con->isConnected() ) {
    // retornar os erros: $con->getErrors();
    die('Erro#2');
}

if(!b_IsBKOUsuarioAdminBKO()) {
    Util::redirect("/");
}
elseif(isset($_POST['id']) && is_numeric($_POST['id']) && empty($_POST['nome'])) {
        
        $pdo = $con->getLink();
        $sql = "SELECT * FROM bo_aba where aba_id = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($_POST['id']));
        $fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if(isset($fetch[0]['aba_id']) && isset($fetch[0]['aba_descricao'])) {
            $id = $fetch[0]['aba_id'];
            $nome = $fetch[0]['aba_descricao'];
            $sistema = $fetch[0]['aba_sistema'];
            $link = $fetch[0]['aba_link'];
        }
}
else {
    $id = "";
    $nome = "";
    $sistema = "";
    $link = "";
}

$msg = array();

if(isset($_POST['nome']) && isset($_POST['id'])) {

    $validate = new Validate();
    
    if($validate->qtdCaracteres($_POST['nome'],2,50))
        $msg[] = "Nome inválido.";
    
    if(empty($msg)) {
        
        $pdo = $con->getLink();
        
        $texto = null;
        if(empty($_POST['id'])) {
            $texto = "inseridos";
            $sql = "INSERT INTO bo_aba (aba_descricao, aba_sistema, aba_link) VALUES (?, ?, ?);";
            $params = array(
                            $_POST['nome'],
                            $_POST['aba_sistema'],
                            $_POST['aba_link']
                        );
        }
        elseif(isset($_POST['id']) && is_numeric($_POST['id']) ){
            $texto = "atualizados";
            $sql = "UPDATE bo_aba set aba_descricao = ?, aba_sistema = ?, aba_link = ? where aba_id = ?;";
            $params = array(
                            $_POST['nome'],
                            $_POST['aba_sistema'],
                            $_POST['aba_link'],
                            $_POST['id']
                        );
        }
            
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        if($stmt->rowCount() == 1){
            $msg[] = "Dados ".$texto." com sucesso. Clique <a href='lista.php'>aqui</a> para voltar.";
            $color = "txt-verde";
        }else{
            var_dump($stmt->rowCount());
            $msg[] = "Erro ao executar a query. Entre em contato com o Administrador do Sistema.";
            $color = "txt-vermelho";
        }        
        
    }else{
        $color = "txt-vermelho";
    }
    
}

?>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="0">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><a href="lista.php">Listagem</a></li>
        <li class="active">Edição</li>
    </ol>
</div>
<?php

if(!empty($msg))
{
    foreach($msg as $txt)
    {
?>
    <div class="col-md-12 top10 <?php echo $color;?>">
        <strong><?php echo $txt?></strong>
    </div>
<?php
    }
    die();
}
?>
<form method="POST" id="form">
    <input type="hidden" name="id" id="id" value="<?php echo $id;?>">
    <div class="col-md-7 top20 txt-preto">
        <div class="form-group col-md-12 has-feedback">
            <label class="control-label col-md-6 text-right" for="nome">
                Nome
            </label>
            <div class="col-md-6">
                <input type="text" class="form-control" name="nome" maxlength="50" id="nome" value="<?php echo $nome;?>">
            </div>
        </div>
        <div class="form-group col-md-12 has-feedback">
            <label class="control-label col-md-6 text-right" for="aba_link">
                Link
            </label>
            <div class="col-md-6">
                <input type="text" class="form-control" name="aba_link" maxlength="50" id="aba_link" value="<?php echo $link;?>">
            </div>
        </div>
        <div class="form-group col-md-12 has-feedback">
            <label class="control-label col-md-6 text-right" for="aba_sistema">
                Sistema
            </label>
            <div class="col-md-6">
                <select name="aba_sistema" class="form-control" id="aba_sistema">
                    <option value="backoffice" <?php if($sistema == "backoffice") echo "selected";?>>backoffice</option>
                    <option value="sysadmin" <?php if($sistema == "sysadmin") echo "selected";?>>sysadmin</option>
                </select>
            </div>
        </div>
    </div>
    <div class="col-md-5 espacamento">
        <button type="submit" class="btn btn-success"><?php echo(empty($id)?"Criar":"Alterar");?></button>
    </div>
</form>
<?php 
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</body>
</html>