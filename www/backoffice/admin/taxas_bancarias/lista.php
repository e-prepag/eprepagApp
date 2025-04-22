<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."class/util/Validate.class.php";
require_once $raiz_do_projeto."class/util/Util.class.php";
require_once $raiz_do_projeto."includes/gamer/constantes.php";

$menus = array();
$abas = array();
$totalRegistros = 0;

$dataI = (isset($_POST['dataInicial'])) ? $_POST['dataInicial'] : date("d/m/Y");
$dataF = (isset($_POST['dataFinal'])) ? $_POST['dataFinal'] : date("d/m/Y");
$banco = (isset($_POST['banco'])) ? $_POST['banco'] : "";
$forma = (isset($_POST['formaPagamento'])) ? $_POST['formaPagamento'] : "";
$where = "";
$con = ConnectionPDO::getConnection();
if ($con->isConnected()){
    $pdo = $con->getLink();
    
    if(!empty($_POST)){
        if(!empty($_POST['formaPagamento']))
            $arrWhere[] = "id_forma = '".$_POST['formaPagamento']."'";
        
        if(!empty($_POST['banco']))
            $arrWhere[] = "banco = '".$_POST['banco']."'";
        
        if(!empty($_POST['dataInicial']) && !empty($_POST['dataFinal'])){
            $arrWhere[] = "data >= '".Util::getData($_POST['dataInicial'], true)."' and data <= '".Util::getData($_POST['dataFinal'], true)."'";
        }
        
        if(!empty($arrWhere)){
            $where = " and ".implode(" and ",$arrWhere);
        }
        
        $sql = "select 
                id, taxa, data, id_forma, LPAD(banco::text, 3, '0') as banco 
            from 
                taxas_transacoes_cobradas_da_epp 
            where 
                data >= '".Util::getData($dataI, true)."' and data <= '".Util::getData($dataF, true)."'
                $where
            order by data desc";
    
        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        $taxas = $stmt->fetchAll(PDO::FETCH_OBJ);
        $totalRegistros = count($taxas);
    }
}

?>
<style>
    .opt{cursor:pointer;}
</style>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><?php echo $sistema->item->getDescricao() ; ?></li>
    </ol>
</div>
<div class="col-md-12">
    <a href="edita.php" class="btn btn-success">Nova</a>
</div>
<div class="col-md-12 top20 txt-preto">
    <div class="col-md-12 bottom10 txt-preto">
        <h3>Filtrar:</h3>
    </div>
    <form method="post" id="filtro" class="fontsize-p">
        <div class="form-group col-md-4">
            <label class="control-label text-right" for="formaPagamento">
                Forma de Pagamento:
            </label>
            <select name="formaPagamento" id="formaPagamento" class="form-control pull-right input-sm">
                <option value="">Selecione a Forma de Pagamento</option>
<?php 
                foreach($FORMAS_PAGAMENTO_DESCRICAO as $idForma => $icone){
                    
                    $selected = ($forma == $idForma) ? "selected" : "";
                    echo "<option value='".$idForma."' $selected>".$icone."</option>";
                }
?>
            </select>
        </div>
        <div class="form-group col-md-3">
            <label class="control-label text-right" for="banco">
                Banco:
            </label>
            <select name="banco" id="banco" class="form-control pull-right input-sm">
                <option value="">Selecione o Banco</option>
<?php 
                foreach($PAGTO_BANCOS as $codBanco => $aBanco){
                    $selected = ($banco == $codBanco) ? "selected" : "";
                    $arrBanco = explode(" / ",$aBanco);
                    echo "<option value='".$codBanco."' $selected>".$arrBanco[0]."</option>";
                }
?>
            </select>
        </div>
        <div class="form-group col-md-4">
            <label class="control-label text-left pull-left col-md-12" for="banco">
                Período:
            </label>
            <input type="text" class="form-control w100p pull-left" value="<?=$dataI?>" name="dataInicial" id="dataInicial">
            <span class="pull-left left5 top5"> à </span>
            <input type="text" class="left5 form-control w100p pull-left" value="<?=$dataF?>" name="dataFinal" id="dataFinal">
        </div>
        <div class="col-md-1">
            <input type="submit" value="Buscar" class="btn top20 btn-info btn-sm">
        </div>
    </form>
</div>
<?php
    if(!empty($_POST)){
?>
    <div class="col-md-12 top10">
        <div class="alert alert-info" role="alert">Para alterar algum item, clique sobre ele.</div>
    </div>
    <div class="col-md-12">
        <table class="text-center txt-preto table table-bordered table-hover" >
            <thead class="">
                <tr>
                    <th>ID</th>
                    <th>Taxa (R$)</th>
                    <th>Data de Alteração</th>
                    <th>Forma Pagto</th>
                    <th>Banco</th>
                </tr>
            </thead>
            <tbody title="Clique para editar">
    <?php 
                if ($totalRegistros == 0) {
    ?>
                <tr>
                    <td colspan="5">Nenhum resultado.</td>
                </tr>
    <?php
                }else{
                    foreach($taxas as $taxa){
                        if($taxa->banco == 1)
                            $taxa->banco = "001";
    ?>
                <tr class="opt trListagem" id="<?php echo $taxa->id; ?>">
                    <td><?php echo $taxa->id; ?></td>
                    <td><?php echo Util::getNumero($taxa->taxa); ?></td>
                    <td><?php echo Util::getData($taxa->data, true); ?></td>
                    <td><?php echo $FORMAS_PAGAMENTO_DESCRICAO[$taxa->id_forma]; ?></td>
                    <td><?php echo $PAGTO_BANCOS[$taxa->banco]; ?></td>
                </tr>
    <?php
                    }//end while
    ?>
                <tr>
                    <td colspan="5">Total de registros encontrados: <?php echo $totalRegistros; ?></td>
                </tr>
    <?php
                }
    ?>
            </tbody>
        </table>
    </div>
<?php
    }else{
?>
    <div class="col-md-12 top10">
        <div class="alert alert-info" role="alert">Para exibir resultados, faça uma busca.</div>
    </div>
<?php 
    }
?>
<form method="post" action="edita.php" id="frmItensAba">
    <input type="hidden" id="id" name="id" value="">
</form>
<script src="/js/jquery.mask.min.js"></script>
<link href="<?php echo $server_url_ep; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $server_url_ep; ?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $server_url_ep; ?>/js/global.js"></script>
<script>
    $(function(){
        
        $("#dataInicial").datepicker();
        $("#dataFinal").datepicker();
        
        $(".opt").click(function(){
            $("#id").val($(this).attr("id"));
            $("#frmItensAba").submit();
        });
        
        $("#aba").change(function(){
            $("#menu").val(false);
            $("#filtro").submit();
        });
        
    });
</script>  
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</body>
</html>