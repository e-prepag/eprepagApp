<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."class/util/Validate.class.php";
require_once $raiz_do_projeto."class/util/Util.class.php";
require_once $raiz_do_projeto."class/util/Login.class.php";
require_once $raiz_do_projeto."public_html/sys/includes/language/eprepag_lang_pt.inc.php";

$con = ConnectionPDO::getConnection();
if ( !$con->isConnected() ) {
    // retornar os erros: $con->getErrors();
    die('Erro#2');
}else if(!b_IsBKOUsuarioAdminBKO()) {
    Util::redirect("/");
}elseif(isset($_POST['id']) && is_numeric($_POST['id']) && empty($_POST['nome'])) {
        
        $pdo = $con->getLink();
        $sql = "SELECT * FROM bo_menu where menu_id = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($_POST['id']));
        $fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if(isset($fetch[0]['menu_id']) && isset($fetch[0]['menu_descricao'])) {
            $id = $fetch[0]['menu_id'];
            $nome = $fetch[0]['menu_descricao'];
            $idAba = $fetch[0]['aba_id'];
            $fixo = $fetch[0]['menu_fixo'];
        }
}
else {
    $id = "";
    $nome = "";
    $idAba = "";
}

$pdo = $con->getLink();

$sql = "SELECT * FROM bo_aba order by aba_descricao";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$abas = $stmt->fetchAll(PDO::FETCH_OBJ);


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
            $sql = "INSERT INTO bo_menu (menu_id,menu_descricao, aba_id) VALUES ((select max(menu_id)+1 from bo_menu),?, ?);";
            $params = array(
                            $_POST['nome'],
                            $_POST['idAba']
                        );
        }
        elseif(isset($_POST['id']) && is_numeric($_POST['id']) ){
            $texto = "atualizados";
            $sql = "UPDATE bo_menu set menu_descricao = ?, aba_id = ? where menu_id = ? AND menu_fixo != 1;";
            $params = array(
                            $_POST['nome'],
                            $_POST['idAba'],
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
<div class="col-md-12">
    <form method="post" action="lista.php">
        <input type="hidden" name="aba" value="<?php echo (isset($_POST['aba'])) ? $_POST['aba'] : "" ;?>">
        <input type="submit" class="btn btn-info" value="Voltar">
    </form>
</div>
<?php

if(!empty($msg))
{
?>

<div class="col-md-12">
    <a href="edita.php" class="btn btn-success">Nova</a>
    <a href="reordena.php" class="btn btn-success">Reordenar</a>
</div>    
<?php
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
    <div class='row'>
        <div class='col-md-7 text-center top10'>
            <?php if(isset($fixo) && $fixo) { ?><span style="color: red;">* Este menu é fixo e não pode ter sua aba alterada</span><?php } ?>
        </div>
    </div>
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
            <label class="control-label col-md-6 text-right" for="idAba">
                Aba
            </label>
            <div class="col-md-6">
                <select name="idAba" id="idAba" class="form-control" <?php if(isset($fixo) && $fixo) echo 'disabled'; ?>>
<?php 
                foreach($abas as $aba){ 
?>
                    <option value="<?php echo $aba->aba_id; ?>" <?php if($idAba != "" && $aba->aba_id == $idAba) echo "selected"; ?>><?php echo $aba->aba_descricao; ?></option>
<?php 
                } 
?>
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