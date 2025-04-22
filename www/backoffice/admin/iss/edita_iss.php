<?php
$pagina_titulo = "E-prepag - Créditos para Games";
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."/class/util/Validate.class.php";
require_once "/www/includes/bourls.php";
$con = ConnectionPDO::getConnection();
if ( !$con->isConnected() ) {
    // retornar os erros: $con->getErrors();
    die('Erro#2');
}
$pdo = $con->getLink();

$msg = array();
$color = "txt-verde";

if(isset($_POST['nome']) && isset($_POST['id'])) { //novo e edita

    $validate = new Validate();
    
    if($validate->numeros($_POST['item_aliquota']))
        $msg[] = "É necessário informar alíquota do ISS.";
    
    if(empty($msg)) {
        
        $texto = null;
        $_POST['item_aliquota'] = str_replace(",", ".", $_POST['item_aliquota']);
        if(empty($_POST['id'])) { //novo
            $texto = "inseridos";
            $sql = "INSERT INTO iss_cidade (iss_cidade, iss_estado, iss_aliquota) VALUES (?, ?, ?);";
            
            $params = array(
                            filter_var($_POST['nome'],FILTER_SANITIZE_STRING),
                            filter_var($_POST['ug_estado'],FILTER_SANITIZE_STRING),
                            filter_var($_POST['item_aliquota'],FILTER_SANITIZE_STRING),
                        );
            
        }
        elseif(isset($_POST['id']) && is_numeric($_POST['id']) ){ //edita
            $texto = "atualizados";
            $sql = "UPDATE iss_cidade set iss_aliquota = ? where iss_id = ?;";
            $params = array(
                            filter_var($_POST['item_aliquota'],FILTER_SANITIZE_STRING),
                            filter_var($_POST['id'],FILTER_SANITIZE_NUMBER_INT),
                        );
        }
            
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        if($stmt->rowCount() == 1){
            $msg[] = "<span onclick='$(\"#BtnSearch\").click();' style='cursor:pointer;cursor:hand;'>Dados ".$texto." com sucesso. Clique aqui para voltar.</span>";
            $color = "txt-verde";
        }else{
            $msg[] = "Erro ao executar a query. Entre em contato com o Administrador do Sistema.";
            $color = "txt-vermelho";
        }
        
    }else{
        $color = "txt-vermelho";
    }
    
}

//Buscando dados no Banco de Dados
$sql = "SELECT * FROM iss_cidade WHERE iss_cidade = ? and iss_estado = ?;";
$stmt = $pdo->prepare($sql);
$params = array(
                filter_var($_POST['nome'],FILTER_SANITIZE_STRING),
                filter_var($_POST['ug_estado'],FILTER_SANITIZE_STRING),
            );
$stmt->execute($params);
$fetch = $stmt->fetch(PDO::FETCH_ASSOC);
$id = (isset($fetch['iss_id'])?$fetch['iss_id']:"");
$aliquota = (isset($fetch['iss_aliquota'])?$fetch['iss_aliquota']:"");

?>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<div class="col-md-12">
    <form method="post" id="voltar" name="voltar" action="cadastro_iss.php">
        <input type="hidden" id="ug_estado" name="ug_estado" value="<?php echo $_POST['ug_estado']; ?>">
        <input type="submit" id="BtnSearch" name="BtnSearch" class="btn btn-info" value="Voltar">
    </form>
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
<div class="col-md-12 top20">
    <div class="alert alert-danger" role="alert"><b>ATENÇÃO:</b> O Nome da Cidade exibido nesta tela foi capturado na tela anterior.<br>Portanto, se você cadastrar uma alíquota nesta tela e depois alterar o cadastro do usuário para uma gráfia de nome difernte desta cidade, isto exige um novo cadastro de alíquota. Pois este se relaciona pela grafia do nome.</div>
</div>
<form method="POST" id="form" onsubmit="return valida();">
    <input type="hidden" name="id" id="id" value="<?php echo $id;?>">
    <input type="hidden" id="ug_estado" name="ug_estado" value="<?php echo $_POST['ug_estado']; ?>">
    <div class="col-md-7 top10 txt-preto">
        <div class="col-md-12">
            <label class="top10 col-md-6 text-right">
                Cidade
            </label>
            <div class="col-md-6 top10">
                <?php echo $_POST['nome'];?>
                <input type="hidden" name="nome" id="nome" value="<?php echo $_POST['nome'];?>">
            </div>
        </div>
        <div class="col-md-12">
            <label class="top10 col-md-6 text-right">
                Estado
            </label>
            <div class="col-md-6 top10">
                <?php echo $_POST['ug_estado'];?>
            </div>
        </div>
        <div class="form-group has-feedback col-md-12">
            <label class="control-label top10 col-md-6 text-right" for="item_aliquota">
                Alíquota
            </label>
            <div class="col-md-6 top10">
                <input type="text" class="form-control" name="item_aliquota" char='1' maxlength="6" id="item_aliquota" value="<?php echo number_format($aliquota, 2, ",", ".");?>">
            </div>
        </div>
    </div>
    <div class="col-md-5 espacamento">
        <button type="submit" id="teste" name="teste" class="btn btn-success">Salvar</button>
    </div>
</form>
<link href="<?php echo $url; ?>:<?php echo $server_port; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>:<?php echo $server_port; ?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script>
    function valida(){
        if((typeof $("#item_aliquota").attr("char") !== undefined) && ($("#item_aliquota").attr("char") != false)){
            var valor = $("#item_aliquota").val();
            var aux = valor.replace(",",".");
            aux = (aux*1);
            var msg = "";
            if($("#item_aliquota").val().trim().length < $("#item_aliquota").attr("char") || isNaN(aux) || aux == 0){
                if(isNaN(aux)){
                    msg = "Preencha o campo Alíquota com números!";
                } else{
                    msg = "Preencha o campo Alíquota!";
                }
                $("#item_aliquota").css("border-color","#a94442");
                alert(msg);
                $("#item_aliquota").focus();
                $("#item_aliquota").select();
                return false;
            }else{
                $("#item_aliquota").css("border-color","");
                return true;
            }
        }
        else return false;
}
</script>
<div class="bloco row">
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</div>    
</body>
</html>