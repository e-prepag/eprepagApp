<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."class/util/Validate.class.php";
require_once $raiz_do_projeto."class/util/Util.class.php";
require_once $raiz_do_projeto."includes/gamer/constantes.php";

$_POST['nivel_id'] = 1;
$con = ConnectionPDO::getConnection();
if ( !$con->isConnected() ) {
    // retornar os erros: $con->getErrors();
    die('Erro#2');
}
$pdo = $con->getLink();

$msg = array();
$color = "txt-verde";

if(isset($_POST['taxa']) && isset($_POST['id'])) { //novo e edita
   
    if(empty($msg)) {
        $validaData = "";
        $texto = null;
        if(empty($_POST['id'])) { //novo
            $texto = "inseridos";
            $sql = "INSERT INTO taxas_transacoes_cobradas_da_epp (taxa, data, id_forma, banco) VALUES (?, ?, ?, ?);";
            $sqlValida = "select count(id) from taxas_transacoes_cobradas_da_epp where data = ? and id_forma = ? and banco = ?";
            
            $params = array(
                            filter_var(str_replace(",",".",$_POST['taxa']),FILTER_SANITIZE_STRING),
                            filter_var(Util::getData($_POST['data'], true),FILTER_SANITIZE_STRING),
                            filter_var($_POST['formaPagamento'],FILTER_SANITIZE_STRING),
                            filter_var($_POST['banco'],FILTER_SANITIZE_NUMBER_INT)
                        );
            
            $paramsValidate = array(
                           filter_var(Util::getData($_POST['data'], true),FILTER_SANITIZE_STRING),
                           filter_var($_POST['formaPagamento'],FILTER_SANITIZE_STRING),
                           filter_var($_POST['banco'],FILTER_SANITIZE_NUMBER_INT)
                       );
            
        }
        elseif(isset($_POST['id']) && $_POST['taxa'] ){ //edita
            $texto = "atualizados";
            $sql = "UPDATE taxas_transacoes_cobradas_da_epp set taxa = ?, data  = ?, id_forma = ?, banco = ? where id = ?;";
            $sqlValida = "select count(id) from taxas_transacoes_cobradas_da_epp where data = ? and id_forma = ? and banco = ? and id != ?";
            $params = array(
                            filter_var(str_replace(",",".",$_POST['taxa']),FILTER_SANITIZE_STRING),
                            filter_var(Util::getData($_POST['data'], true),FILTER_SANITIZE_STRING),
                            filter_var($_POST['formaPagamento'],FILTER_SANITIZE_STRING),
                            filter_var($_POST['banco'],FILTER_SANITIZE_NUMBER_INT),
                            filter_var($_POST['id'],FILTER_SANITIZE_NUMBER_INT)
                        );
            
            $paramsValidate = array(
                            filter_var(Util::getData($_POST['data'], true),FILTER_SANITIZE_STRING),
                            filter_var($_POST['formaPagamento'],FILTER_SANITIZE_STRING),
                            filter_var($_POST['banco'],FILTER_SANITIZE_NUMBER_INT),
                            filter_var($_POST['id'],FILTER_SANITIZE_NUMBER_INT)
                        );
        }
        
        $stmtValidate = $pdo->prepare($sqlValida);
        $stmtValidate->execute($paramsValidate);
        
        $validaRepetida = $stmtValidate->fetchAll(PDO::FETCH_OBJ);
        
        if($validaRepetida[0]->count > 0){
            
            $msg[] = "Já existe taxa cadastrada para a data, forma de pagamento e banco selecionados.";
            $color = "txt-vermelho";
        }else{
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            if($stmt->rowCount() == 1){
                $msg[] = "Dados ".$texto." com sucesso. Clique <a href='lista.php'>aqui</a> para voltar.";
                $color = "txt-verde";
            }else{
                $msg[] = "Erro ao executar a query. Entre em contato com o Administrador do Sistema.";
                $color = "txt-vermelho";
            }
        }

    }else{
        $color = "txt-vermelho";
    }
    
}
else if(isset($_POST['id']) && is_numeric($_POST['id']) && empty($_POST['nome'])) { //editar
        
        $sql = "SELECT * FROM taxas_transacoes_cobradas_da_epp where id = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($_POST['id']));
        $fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if(count($fetch) > 0){
            $id = $fetch[0]['id'];
            $taxa = $fetch[0]['taxa'];
            $data = Util::getData($fetch[0]['data']);
            $formaPagto = $fetch[0]['id_forma'];
            $codBanco = $fetch[0]['banco'];
        }
}
else {
    $id = "";
    $taxa = "";
    $data = date("d/m/Y");
    $formaPagto = "";
    $codBanco = "";
}
?>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="0">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li><a href="lista.php">Listagem</a></li>
        <li class="active">Gerenciamento de Taxas Bancárias</li>
    </ol>
</div>
<div class="col-md-12">
    <a href="lista.php" class="btn btn-info">Voltar</a>
</div>
<?php
if(!empty($msg))
{
?>
    
<div class="col-md-12 top10">
    <a href="edita.php" class="btn btn-success">Nova</a>
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
    <div class="col-md-9 top10 txt-preto">
        <div class="form-group has-feedback">
            <label class="control-label top10 col-md-3 text-right" for="taxa">
                Taxa:
            </label>
            <div class="col-md-9">
                <input type="text" class="form-control w100p top10" name="taxa" maxlength="250" id="taxa" value="<?php echo $taxa;?>">
            </div>
        </div>
        <div class="form-group has-feedback">
            <label class="control-label top10 col-md-3 text-right" for="dataCadastro">
                Válido a partir de:
            </label>
            <div class="col-md-9">
                <input type="text" class="form-control w150 top10" name="data" maxlength="10" id="data" value="<?php echo $data;?>">
            </div>
        </div>
        <div class="form-group has-feedback">
            <label class="control-label top10 col-md-3 text-right nowrap" for="formaPagamento">Forma de Pagamento:</label>
            <div class="col-md-9 top10">
                <select name="formaPagamento" id="formaPagamento" class="form-control">
                    <option value="">Selecione</option>
<?php 
                    foreach($FORMAS_PAGAMENTO_DESCRICAO as $idForma => $icone){
                        $selected = ($idForma == $formaPagto) ? "selected" : "" ;
                        echo "<option value='".$idForma."' $selected>".$icone."</option>";
                    }
?>
                </select>
            </div>
        </div>
        <div class="form-group has-feedback">
            <label class="control-label top10 col-md-3 text-right" for="banco">Banco:</label>
            <div class="col-md-7 top10">
                <select name="banco" id="banco" class="form-control">
                    <option value="">Selecione</option>
<?php 
                    foreach($PAGTO_BANCOS as $codArrBanco => $banco){
                        $selected = ($codArrBanco == $codBanco) ? "selected" : "" ;
                        
                        echo "<option value='".$codArrBanco."' $selected>".$banco."</option>";
                    }
?>
                </select>
            </div>
        </div>
    </div>
    <div class="col-md-3 espacamento">
        <a href="#" class="btn btn-sm btn-success"><?php echo(empty($id)?"Salvar":"Editar");?></a>
    </div>
</form>
<div class="col-md-12" id="divvincula"></div>
<script src="/js/jquery.mask.min.js"></script>
<link href="<?php echo $server_url_ep; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $server_url_ep; ?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $server_url_ep; ?>/js/global.js"></script>
<script>
    $(function(){
        $("#data").datepicker();
        $("#taxa").mask("###,##", {reverse: true});
        
        $(".btn-success").click(function(){
            var erro = false;

            $(".form-control").each(function(){
                if(!$(this).val())
                {
                    erro  = true;
                    $("label[for='"+$(this).attr("id")+"']").addClass("txt-vermelho");
                }else{
                    $("label[for='"+$(this).attr("id")+"']").removeClass("txt-vermelho");
                }
                
           });
           
            if(!erro)
                $("#form").submit();
        });
    });
</script> 
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</body>
</html>